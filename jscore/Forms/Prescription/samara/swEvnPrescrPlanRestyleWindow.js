/**
* swEvnPrescrPlanRestyleWindow - лист назначений.
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

sw.Promed.swEvnPrescrPlanRestyleWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPrescrPlanRestyleWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrPlanRestyleWindow.js',

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
	id: 'EvnPrescrPlanRestyleWindow',
    listeners: {
        hide: function(win) {
			this.PrescrPlanPanel.getEl().update('');
			this.ViewContextMenu.hide();
            (win.isChange)?win.callback():win.onHide();
        }
    },
    show: function() {
        sw.Promed.swEvnPrescrPlanRestyleWindow.superclass.show.apply(this, arguments);
        this.restore();
        this.center();
        this.maximize();

        this.isChange = false;
        if ( !arguments[0] || !arguments[0].formParams ) {
            sw.swMsg.alert('Сообщение', 'Неверные параметры', function() {this.hide();}.createDelegate(this) );
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

        this.action = arguments[0].action || 'edit';

        this.PersonInfo.load({
            Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
            Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
            Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
            Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
            Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
        });
        this.ViewActions['action_openstandart'].setDisabled(!arguments[0].formParams.CureStandart_Count);
        this.loadPrescrPlanView();
        this.createPrescriptionTypeMenu();
    },
    /**
     * Создание назначения клику по кнопке "Добавить" в строке типа назначения
     * или по двойному клику в ячейке пустой строки
     * @param data
     */
    addPrescrByType: function(data) {
		
		if (!data.EvnPrescr_key){ 
			return false;
		}
		var rec = this.EvnPrescrStore.getById(data.EvnPrescr_key);
		
		if (!rec) {
			return false
		}
		
		
        var thas = this, personFrame = this.PersonInfo;
        var base_form = this.FormPanel.getForm();
		var set_date = data.set_date;
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
                thas.loadPrescrPlanView();
            }
			
        };
		
		if(data.EvnPrescr_id){
			option.EvnPrescr_id=data.EvnPrescr_id;
			option.Prescr_type= data.PrescriptionType_id;
		}
		if(data.newEvnPrescr_id)
		{
			option.newEvnPrescr_id=data.newEvnPrescr_id;
		}
        sw.Promed.EvnPrescr.openEditWindow(option);
        return true;
    },
    onCellClick: function(e, clickData) {
		
		if (!clickData.EvnPrescr_key||!clickData.cellType) {
			return false;
		}

        var thas = this,
            base_form = this.FormPanel.getForm(),
            coords = [e.clientX,e.clientY],
            day_key = '',
            rec, data, key, index_cell;
		
		key = clickData.EvnPrescr_key;
//		console.log(key);
//		console.log(clickData.date);
		rec = this.EvnPrescrStore.getById(key);
		
		if (clickData.cellType == 'EvnPrescrDayCell') {
			if (!clickData.date ) {
				return false;
			}
            day_key = rec.get('EvnPrescr_id') || rec.get('EvnCourse_id');
            day_key += '-'+clickData.date;
			if (!rec.get('days') || !rec.get('days').hasOwnProperty(day_key)) {
				data = {set_date:clickData.date, empty: true}
			} else {
				data = rec.get('days')[day_key];
				data.set_date = data.date;
				data.empty = false;
			}
		}

        if (clickData.cellType == 'EvnPrescrDayRow') {
            if (!clickData.EvnPrescrDayRow_key || !clickData.EvnPrescr_id) {
                return false;
            }
            data = rec.get('days')[clickData.EvnPrescrDayRow_key].EvnPrescrDataList[clickData.EvnPrescr_id];
            data.EvnPrescr_id = clickData.EvnPrescr_id;
            data.date = data.EvnPrescr_setDate;
            data.set_date = data.EvnPrescr_setDate;
            data.Day_IsExec = (data.EvnPrescr_IsExec == 2);
            data.Day_IsSign = (data.PrescriptionStatusType_id == 2);
            data.empty = false;
        }
		
        this.ViewActions['action_edit'].setHidden(false);
        this.ViewActions['action_exec'].setHidden(false);
        this.ViewActions['action_execWithUseDrug'].setHidden(false);
        this.ViewActions['action_unexec'].setHidden(false);
        this.ViewActions['action_view'].setHidden(false);
        this.ViewActions['action_delete'].setHidden(false);
        this.ViewActions['action_deleteCourse'].setHidden(true);        
        
        this.ViewActions['action_edit'].setDisabled(false);
        this.ViewActions['action_exec'].setDisabled(false);
        this.ViewActions['action_execWithUseDrug'].setDisabled(false);
        this.ViewActions['action_unexec'].setDisabled(false);
        this.ViewActions['action_view'].setDisabled(false);
        this.ViewActions['action_delete'].setDisabled(false);
        this.ViewActions['action_deleteCourse'].setDisabled(true);
        
        if (this.action == 'view'){ // ipavelpetrov
        	this.ViewActions['action_edit'].setHidden(true);
	        this.ViewActions['action_delete'].setHidden(true);
	        this.ViewActions['action_deleteCourse'].setHidden(true);
        }        
        
        this.ViewContextMenu.params = {
            EvnPrescrListRecord: rec,
            DayData: data
        };
        this.ViewContextMenu.showAt([coords[0], coords[1]]);
		if (typeof clickData.callback == 'function') {
			clickData.callback();
		}
        return true;
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
                thas.loadPrescrPlanView();
            },
            onCreate: function(menu){
                thas.menuPrescriptionType = menu;
                thas.ViewActions['action_add'].items[0].menu = thas.menuPrescriptionType;
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
        var data = this.ViewContextMenu.params.DayData || {};
        var EvnPrescr_id = rec.get('EvnPrescr_id') || data.EvnPrescr_id;
        if ( !EvnPrescr_id || rec.get('EvnPrescr_IsExec')==2) {
            return false;
        }
            sw.Promed.EvnPrescr.cancel({
                ownerWindow: thas
                ,getParams: function(){
                    return {
                        parentEvnClass_SysNick: 'EvnSection'
                        ,PrescriptionType_id: rec.get('PrescriptionType_id')
                    ,EvnPrescr_id: EvnPrescr_id
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
     * Отмена назначения-направления
     * @return {Boolean}
     */
    cancelEvnCourse: function() {
        var thas = this;
        if ( typeof this.ViewContextMenu.params != 'object' || !this.ViewContextMenu.params.EvnPrescrListRecord) {
            return false;
                    }
        var rec = this.ViewContextMenu.params.EvnPrescrListRecord;
        if ( !rec.get('EvnCourse_id')) {
            return false;
        }
        sw.Promed.EvnPrescr.cancelEvnCourse({
            ownerWindow: thas
            ,getParams: function(){
                return {
                    parentEvnClass_SysNick: 'EvnSection'
                    ,PrescriptionType_id: rec.get('PrescriptionType_id')
                    ,EvnCourse_id: rec.get('EvnCourse_id')
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
     * Загрузить план из шаблона
     */
    getEvnPrescrList: function()
    {
        var hasEvnPrescr = false;
        var store = this.EvnPrescrStore;
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
            lm = this.getLoadMask('Загрузка...'),
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
                            msg: 'Текущий план будет удален! Продолжить?',
                            title: 'Подтверждение'
                        });
                    } else {
                        thas.isChange = true;
                        thas.loadPrescrPlanView();
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
        var EvnPrescr_id = rec.get('EvnPrescr_id') || dayData.EvnPrescr_id;
        if ( !rec || !EvnPrescr_id ) {
            return false;
        }
        var EvnPrescrDay_id = EvnPrescr_id;
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
		var scrollTop = $('.EvnPrescrList_right').scrollTop();
		var scrollLeft = $('.EvnPrescrList_right').scrollLeft();
        var conf = {
            ownerWindow: this
            ,EvnPrescrDay_id: EvnPrescrDay_id
            ,PrescriptionType_id: rec.get('PrescriptionType_id')
            ,EvnDirection_id: rec.get('EvnDirection_id') || dayData.EvnDirection_id
            ,hasExec: ((dayData && dayData.Day_IsExec) || 2==rec.get('EvnPrescr_IsExec'))
            ,EvnPrescr_IsHasEvn: ((dayData && dayData.EvnPrescr_IsHasEvn) || 2==rec.get('EvnPrescr_IsHasEvn'))
            ,PrescriptionStatusType_id: rec.get('PrescriptionStatusType_id') || dayData.PrescriptionStatusType_id
            ,onSuccess: function() {
                thas.isChange = true;
                thas.loadPrescrPlanView(function(){
                    $('.EvnPrescrList_right').scrollTop(scrollTop);
                    $('.EvnPrescrList_right').scrollLeft(scrollLeft);
                });
            }
            ,onCancel: Ext.emptyFn
        };
        sw.Promed.EvnPrescr.unExec(conf);
        return true;
    },
    /**
     * Выполнение назначения
     * @return {Boolean}
     */
    execEvnPrescr: function(mode) {
        var thas = this,
            base_form = this.FormPanel.getForm();
        if ( typeof this.ViewContextMenu.params != 'object' || !this.ViewContextMenu.params.EvnPrescrListRecord) {
            return false;
        }
        var rec = this.ViewContextMenu.params.EvnPrescrListRecord;
        var dayData = this.ViewContextMenu.params.DayData;
        var EvnPrescr_id = rec.get('EvnPrescr_id') || dayData.EvnPrescr_id;
        if ( !EvnPrescr_id ) {
            return false;
        }
		var scrollTop = $('.EvnPrescrList_right').scrollTop();
		var scrollLeft = $('.EvnPrescrList_right').scrollLeft();
        var conf = {
            ownerWindow: this
            //,btnId: 'EvnPrescrPlanWindowExecEvnPrescr'
            ,btnId: (dayData?('EvnPrescrDayCell_'+dayData.index):('EvnPrescrCell_'+rec.get('EvnPrescr_key')))
            ,allowChangeTime: !dayData
            ,EvnPrescr_setDate: (dayData&&dayData.set_date)||null
            ,Person_id: base_form.findField('Person_id').getValue()
            ,PersonEvn_id: base_form.findField('PersonEvn_id').getValue()
            ,Server_id: base_form.findField('Server_id').getValue()
            ,mode: mode
        };
        conf.EvnPrescr_id = EvnPrescr_id;
        conf.PrescriptionType_id = rec.get('PrescriptionType_id');
        conf.EvnPrescr_IsExec = (dayData)?(dayData.Day_IsExec?2:1):rec.get('EvnPrescr_IsExec');
        conf.PrescriptionStatusType_id = rec.get('PrescriptionStatusType_id') || dayData.PrescriptionStatusType_id;
        conf.onExecSuccess = function(){
            thas.isChange = true;
            thas.loadPrescrPlanView(function(){
				$('.EvnPrescrList_right').scrollTop(scrollTop);
				$('.EvnPrescrList_right').scrollLeft(scrollLeft);
			});
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
        //for 6,7,11,12,13
        conf.EvnPrescr_rid  = base_form.findField('EvnPrescr_rid').getValue();
        conf.EvnPrescr_pid = base_form.findField('EvnPrescr_pid').getValue();
        conf.Diag_id = base_form.findField('Diag_id').getValue();
        conf.UslugaId_List = rec.get('UslugaId_List') || dayData.UslugaComplex_id;
        conf.TableUsluga_id = rec.get('TableUsluga_id') || null;
        conf.PrescriptionType_Code = rec.get('PrescriptionType_Code');
        conf.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
        conf.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');
        conf.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
        conf.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
        conf.EvnDirection_id = rec.get('EvnDirection_id') || dayData.EvnDirection_id;
        if (mode && 'withUseDrug' == mode) {
            //@todo
        }
        //log([rec, dayData]);
        sw.Promed.EvnPrescr.exec(conf);
        return true;
    },
    /**
     * Вызов формы для копирования назначения-направления  на услугу манип. и процедуры
     * @return {Boolean}
     */
    addEvnPrescrProc: function(EvnPrescr_key, day_key, set_date) {
        //log(['addEvnPrescrProc', EvnPrescr_key, day_key, set_date]);
        var rec = this.EvnPrescrStore.getById(EvnPrescr_key);
        if (!rec) {
            return false
        }
        if (!rec.get('days') || !rec.get('days').hasOwnProperty(day_key) || !rec.get('days')[day_key].hasOwnProperty('EvnPrescrDataList')) {
            return false
        } else {
            var data = {};
            var dataList = rec.get('days')[day_key].EvnPrescrDataList;
            for (var EvnPrescr_id in dataList) {
                if (dataList.hasOwnProperty(EvnPrescr_id)) {
                    data = dataList[EvnPrescr_id];
                    data.EvnPrescr_id = EvnPrescr_id;
                    break;
                }
            }
            data.set_date = set_date||rec.get('days')[day_key].date;
        }
        // Вызов формы для добавления назначения в курс
        this.ViewContextMenu.params = {
            EvnPrescrListRecord: rec,
            DayData: data
        };
        return this.openEvnPrescrEditWindow('copy');
    },
    /**
     * Отмена назначения-направления на услугу манип. и процедуры
     * @return {Boolean}
     */
    cancelEvnPrescrProc: function(EvnPrescr_key, day_key, EvnPrescr_id) {
        var rec = this.EvnPrescrStore.getById(EvnPrescr_key);
        if (!rec) {
            return false
        }
        if (!rec.get('days') || !rec.get('days').hasOwnProperty(day_key)) {
            return false
        } else {
            var data = rec.get('days')[day_key].EvnPrescrDataList[EvnPrescr_id];
            data.EvnPrescr_id = EvnPrescr_id;
            data.Day_IsExec = (2 == data.EvnPrescr_IsExec);
            this.ViewContextMenu.params = {
                EvnPrescrListRecord: rec,
                DayData: data
            };
        }
        return this.cancelEvnPrescr();
    },
    /**
     * Отмена выполнения назначения-направления на услугу манип. и процедуры
     * @return {Boolean}
     */
    unExecEvnPrescrProc: function(EvnPrescr_key, day_key, EvnPrescr_id) {
        var rec = this.EvnPrescrStore.getById(EvnPrescr_key);
        if (!rec) {
            return false
        }
        if (!rec.get('days') || !rec.get('days').hasOwnProperty(day_key)) {
            return false
        } else {
            var data = rec.get('days')[day_key].EvnPrescrDataList[EvnPrescr_id];
            data.EvnPrescr_id = EvnPrescr_id;
            data.Day_IsExec = (2 == data.EvnPrescr_IsExec);
            data.EvnPrescr_IsHasEvn = (2 == data.EvnPrescr_IsHasEvn);
            this.ViewContextMenu.params = {
                EvnPrescrListRecord: rec,
                DayData: data
            };
        }
        return this.unExecEvnPrescr();
    },
    /**
     * Редактирование назначения-направления на услугу манип. и процедуры
     * @return {Boolean}
     */
    editEvnPrescrProc: function(EvnPrescr_key, day_key, EvnPrescr_id) {
        var rec = this.EvnPrescrStore.getById(EvnPrescr_key);
        if (!rec) {
            return false
        }
        if (!rec.get('days') || !rec.get('days').hasOwnProperty(day_key)) {
            return false
        } else {
            var data = rec.get('days')[day_key].EvnPrescrDataList[EvnPrescr_id];
            data.EvnPrescr_id = EvnPrescr_id;
            this.ViewContextMenu.params = {
                EvnPrescrListRecord: rec,
                DayData: data
            };
        }
        return this.openEvnPrescrEditWindow('edit');
    },
    mouseoutEvnPrescrRows: function(id) {
        var el = Ext.get(id);
        if (el && el.isDisplayed()) {
            el.setStyle({display: "none"});
        }
    },
    mouseoverEvnPrescrRows: function(id) {
        var el=Ext.get(id);
        if (el && !el.isDisplayed()) {
            el.setStyle({display: "block"});
        }
    },
    /**
     * Вызов формы для копирования ближайшего слева назначения-направления на новую дату-время
     * @return {Boolean}
     */
    copyEvnPrescrUsluga: function(rec, data) {
        // открывается расписание услуги-службы (а если отдельного расписания нет, то службы) из строки на дату из ячейки.
        // Результатом должно явиться аналогичное созданное назначение-направления на новую дату-время.
        /*
         case '13':
         case '12':
         case '11':
         case '7':
         */
        //log(['copyEvnPrescrUsluga',rec,data]);
        if (rec.get('days') && data.set_date) {
            data.EvnPrescr_id = null;
            var day_dt = Date.parseDate(data.set_date, 'd.m.Y');
            var cur_dt;
            for (var day_key in rec.get('days')) {
                if ( !rec.get('days').hasOwnProperty(day_key) ) continue;
                cur_dt = Date.parseDate(day_key.split('-')[1], 'd.m.Y');
                if (day_dt > cur_dt) {
                    data.EvnPrescr_id = rec.get('days')[day_key].EvnPrescr_id;
                }
            }
            // Вызов формы для добавления назначения в курс
            this.ViewContextMenu.params = {
                EvnPrescrListRecord: rec,
                DayData: data
            };
            return this.openEvnPrescrEditWindow('copy');
        }
        return false;
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
        var data = this.ViewContextMenu.params.DayData || {};
        var EvnPrescr_id = rec.get('EvnPrescr_id') || data.EvnPrescr_id;
        if ( !EvnPrescr_id || !action.inlist(['edit', 'view', 'copy'])) {
            return false;
        }
        var set_date = base_form.findField('EvnPrescr_begDate').getValue();
        if (data.set_date) {
            set_date = data.set_date;
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
                EvnCourse_id: rec.get('EvnCourse_id'),
                EvnPrescr_id: EvnPrescr_id,
                begDate: set_date
            }
            ,onHideEditWindow: function() {
                //
            }
            ,callbackEditWindow: function() {
                thas.isChange = true;
                thas.loadPrescrPlanView();
            }
        });
        return true;
    },
    loadPrescrPlanView: function(callback) {
		
		//Проверяем данные

		var base_form = this.FormPanel.getForm();
        var thas = this;
		var params = {
			Evn_rid: base_form.findField('EvnPrescr_rid').getValue(),
			Evn_pid: base_form.findField('EvnPrescr_pid').getValue()
		};
		
		
		
		//Весим прелоадер, загружаем
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=EvnPrescr&m=getPrescrPlanView',
			params:params,
			success: function(response, opts) {
				var resp = JSON.parse(response.responseText);

				if (resp) {
					if (resp.html) {
						this.PrescrPlanPanel.getEl().update(resp.html);
					}
					if (resp.response) {
						this.EvnPrescrStore.loadData(resp.response);
					}
				}

                if (thas.action === 'view'){
                    $('.EvnPrescrList_group_name a').css('display','none')                  
                }				

				this.PrescrPlanPanel.doLayout();
				loadMask.hide();
				var setLayersHeight = this.setLayersHeight;
				$(window).resize(function(e){
					this.setLineWidth(function(){
						this.updateScalebarWidth(function(){
							this.updateToday();
						}.createDelegate(this));
					}.createDelegate(this));
					this.setLayersHeight();
				}.createDelegate(this));
				this.setLayersHeight();
				this.setLineWidth(function(){
					this.updateScalebarWidth(function(){
						this.updateToday();
					}.createDelegate(this));
				}.createDelegate(this));


				$('.EvnPrescrList_middle .EvnPrescrList_right').scroll(function(e){
					$('.EvnPrescrList_middle .EvnPrescrList_left').css({top:'-'+$(this).scrollTop()+'px',position:'absolute'});
					$('.EvnPrescrList_header .EvnPrescrList_calendar').css({left:'-'+$(this).scrollLeft()+'px',position:'absolute'});
				});
				
				if (typeof callback == 'function') {
					callback();
				}
				
			}.createDelegate(this),
			failure: function(response, opts) {
				loadMask.hide();
			}.createDelegate(this)
		});
		
//		this.PrescrPlanPanel.load({
//			url: '/?c=EvnPrescr&m=getPrescrPlanView',
//			params:params
//		});
		
//		this.PrescrPlanPanel.getEl().up('div').addClass('EvnUslugaparFunctRequest_position');
	},

	setLayersHeight: function(){
		var middleH = parseInt( Ext.getCmp('EvnPrescrPlanRestyleWindow').body.getHeight() ) - ( Ext.getCmp('EvnPrescrPlanRestyleWindow').TopToolbar.getBox().height ) - 54;	
		$('.EvnPrescrList_middle').height( middleH+'px' );
		$('.EvnPrescrList_middle .EvnPrescrList_right').height( middleH+'px' )
	},

	updateScalebarWidth: function(callback){
		var layoutW = $('#EvnPrescrList_scroll-layout').outerWidth();
		var lineW = $('#EvnPrescrList_scroll-layout div[class*="EvnPrescrList_no"]:first').outerWidth();
		var totalScalebars = $('#EvnPrescrList_scroll-layout div[class*="EvnPrescrList_no"]:first .EvnPrescrList_scale_bar').length;
		totalScalebars = (totalScalebars>0)?totalScalebars:10;
		var borderW = 1;
		var setLineWidthCalback = null;
		if ( layoutW >= lineW ) {
			var w = Math.ceil( layoutW / totalScalebars ) + borderW;
			$('#EvnPrescrList_scroll-layout div[class*="EvnPrescrList_no"] .EvnPrescrList_scale_bar').css('width',w+'px');
			$('.EvnPrescrList_calendar table td:not(:first-child)').css('width',(w+1)+'px');
			$('.EvnPrescrList_calendar table td:first-child').css('width',w+'px');
			setLineWidthCalback = function(){
				var diff = $('#EvnPrescrList_scroll-layout div[class*="EvnPrescrList_no"]:first').outerWidth() - $('#EvnPrescrList_scroll-layout').outerWidth()
				$('.EvnPrescrList_header .EvnPrescrList_calendar').css({left: '0px',right:'-'+diff+'px',position:'absolute'});
			}
		}
		this.setLineWidth(setLineWidthCalback);
		if ( typeof callback == 'function' ) {
			callback();
		}
	},

	setLineWidth: function(callback){
		var parentWidth = 0;
		var totalScalebars = $('#EvnPrescrList_scroll-layout div[class*="EvnPrescrList_no"]:first .EvnPrescrList_scale_bar').length;
		$('#EvnPrescrList_scroll-layout div[class*="EvnPrescrList_no"]:first .EvnPrescrList_scale_bar').each(function(index,element){
			parentWidth += (parseInt( $(this).outerWidth() )+1);
			if ( index == ( totalScalebars - 1 ) ) {
				parentWidth += 2;
				$('#EvnPrescrList_scroll-layout div[class*="EvnPrescrList_no"]').css('width',parentWidth+'px');
			}
		});
		if ( typeof callback == 'function' ) {
			callback();
		}
	},

	updateToday: function(){
		var leftSideW = 350;
		var $obj = $('.EvnPrescrList_isToday').first();
		var objW = $obj.outerWidth();
		if (objW == null) {
			Ext.get('EvnPrescrList_today').hide();
		} else {
			var offset = $obj.offset();
			Ext.get('EvnPrescrList_today').setLeft(offset.left-leftSideW+parseInt(objW/2));
		}

	},
	
	

    initComponent: function() {
        var thas = this;
		
		this.EvnPrescrStore = new Ext.data.Store({
			reader:new Ext.data.JsonReader({
				id:'EvnPrescr_key'
			}, [
				{name: 'EvnPrescr_key', mapping: 'EvnPrescr_key',key:true},
                {name: 'EvnCourse_id', mapping: 'EvnCourse_id'},
				{name: 'EvnPrescr_id', mapping: 'EvnPrescr_id'},
				{name: 'EvnPrescr_IsExec', mapping: 'EvnPrescr_IsExec'},
                {name: 'EvnPrescr_IsHasEvn', mapping: 'EvnPrescr_IsHasEvn'},
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
				{name: 'EvnPrescrTreatDrug_MaxDoseDay', mapping:'EvnPrescrTreatDrug_MaxDoseDay'},
				{name: 'EvnPrescrTreatDrug_PrescrDoseCource', mapping:'EvnPrescrTreatDrug_PrescrDoseCource'},
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
			])
		});
		
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
                text: 'Выполнить',
                tooltip: 'Отметить назначение как выполненое',
                iconCls: 'exec16',
                handler: function() {
                    thas.execEvnPrescr('simple');
                },
                id: 'EvnPrescrPlanRestyleWindowExecEvnPrescr'
            },
            {
                name:'action_execWithUseDrug',
                text: 'Выполнить с использованием медикаментов',
                tooltip: 'Открыть форму списания медикаментов ',
                iconCls: 'exec16',
                handler: function() {
                    thas.execEvnPrescr('withUseDrug');
                }
            },
			{
                name:'action_unexec',
                text: 'Отменить выполнение',
                tooltip: 'Снять отметку об исполнении услуги',
                iconCls: 'unexec16',
                handler: function() {
                    thas.unExecEvnPrescr();
                },
                id: 'EvnPrescrPlanWindowUnExecEvnPrescr'
            },
            {name:'action_edit', text:BTN_GRIDEDIT, tooltip: BTN_GRIDEDIT_TIP, icon: 'img/icons/edit16.png', handler: function() {thas.openEvnPrescrEditWindow('edit');}},
            {name:'action_view', text:BTN_GRIDVIEW, tooltip: BTN_GRIDVIEW_TIP, icon: 'img/icons/view16.png', handler: function() {thas.openEvnPrescrEditWindow('view');}},
            {name:'action_delete', text:BTN_GRIDDEL, tooltip: BTN_GRIDDEL_TIP, icon: 'img/icons/delete16.png', handler: this.cancelEvnPrescr},
            {name:'action_deleteCourse', text:BTN_GRIDDEL, tooltip: BTN_GRIDDEL_TIP, icon: 'img/icons/delete16.png', handler: this.cancelEvnCourse},
            {name:'action_refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR_TIP, icon: 'img/icons/refresh16.png', handler: this.loadPrescrPlanView},
            {
                name: 'action_openstandart',
                text: 'Федеральный стандарт',
                tooltip: 'Открыть форму назначений по федеральному стандарту',
				iconCls:'template16',
                handler: this.openStandartWindow
            },
            {
                name: 'action_savetemplate',
                text: 'Сохранить как шаблон',
                tooltip: 'Сохранить план назначений как шаблон',
				iconCls: 'save16',
                disabled: true,//до восстановления работоспособности
                handler: this.saveEvnPrescrListAsXTemplate
            },
            {
                name: 'action_gettemplate',
                text: 'Загрузить план',
                tooltip: 'Загрузить план из шаблона',
				iconCls:'adddrugs-icon16',
                disabled: true,//до восстановления работоспособности
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
                {xtype : "tbseparator"}
            ]
        });
        this.ViewContextMenu = new Ext.menu.Menu();
		this.ViewContextMenu.on('hide',function(){$('.EvnPrescrList_click').removeClass('EvnPrescrList_click');});
        this.ViewContextMenu.add(this.ViewActions['action_exec']);
        this.ViewContextMenu.add(this.ViewActions['action_execWithUseDrug']);
		this.ViewContextMenu.add(this.ViewActions['action_unexec']);
        this.ViewContextMenu.add(this.ViewActions['action_edit']);
        this.ViewContextMenu.add(this.ViewActions['action_view']);
        this.ViewContextMenu.add(this.ViewActions['action_delete']);
        this.ViewContextMenu.add(this.ViewActions['action_deleteCourse']);

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
			hidden: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnPrescrPlanForm',
			labelAlign: 'right',
			labelWidth: 130,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
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
                name: 'EvnPrescr_begDate', // Диагноз
                xtype: 'hidden'
            }]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EPRPLF_PersonInformationFrame'
		});
		
		this.PrescrPlanPanel = new Ext.Panel({
			id: 'EPRPLF_R_PrescrPlanPanel_Panel',
			width: '100%',
			height: '100%'
		});
		this.TopToolbar = new Ext.Panel({
				autoHeight: true,
				xtype: 'panel',
				width: '100%',
				tbar: this.ViewToolbar,
				items: [
					this.PersonInfo,
					this.FormPanel
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
			items: [this.TopToolbar, 
				{
				id:'EPRPLF_R_PrescrPlanPanel',
				heigth: '100%',
				width: '100%',
//				margins:'0 5 0 0',
				items:
				[
					this.PrescrPlanPanel
				]
			}
            ],
			layout: 'anchor'
		});

		sw.Promed.swEvnPrescrPlanRestyleWindow.superclass.initComponent.apply(this, arguments);
	}
});