/**
 * swIPRARegistryEditWindow - Запись в Регистр ИПРА.
 */
sw.Promed.swIPRARegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
    //alwaysOnTop: true,
    id: 'swIPRARegistryEditWindow',
    objectName: 'swIPRARegistryEditWindow',
    objectSrc: '/jscore/Forms/Admin/ufa/swIPRARegistryEditWindow.js',
    layout: 'form',
    title: 'Запись в Регистр ИПРА',
    modal: true,
    width: 1000,
    fieldWidth: 40,
    autoHeight: true,
    closable: true,
    resizable: false,
    closeAction: 'hide',
    draggable: true,
    listeners: {
        'hide': function() {
            Ext.getCmp('pacient_tab_panel_edit').setActiveTab(0);
            Ext.getCmp('formEditIPRA').getForm().reset();
        },
    },
    accessCheck: function() {
        var ggo = getGlobalOptions();       
        //рабочий и тестовый
        var listMZ = [150035,150031,9990000014,9990000015];
        //прогресс
        var listMZTest = [150035,150031,13026016,13026015];
        
        if(getRegionNick() == 'ufa'){
            if(location.hostname == '127.0.0.1'){
                return true;
            }
            else if(location.hostname == '192.168.200.175' || location.hostname == '192.168.200.16'){
                return ggo.lpu_id.inlist(listMZTest);
            }       
            else{
                return ggo.lpu_id.inlist(listMZ);
            }
        } else {
            return isUserGroup('IPRARegistryEdit');
        }
    },
    initComponent: function ()
    {
        var form = this;
        this.store = new Ext.data.SimpleStore({
            fields: [{name: 'sp', type: 'int'}],
            data: [[0], [1], [2], [3], [4]]
        });
        
        this.prognoz_store = new Ext.data.JsonStore({
            mode: 'local',
            fields: [
                {name: 'id', type: 'int'}, 
                {name: 'name', type: 'string'}
            ],
            data: [
                { 'id' : 1, 'name' : 'Полностью' },
                { 'id' : 2, 'name' : 'Частично' }
            ]
        });
        
        this.disabilityCause_store = new Ext.data.JsonStore({
            fields: [
                {name: 'id', type: 'int'},
                {name: 'name', type: 'string'}
            ],
            data: [
                { 'id' : 1, 'name': 'Общее заболевание' },
                { 'id' : 2, 'name': 'Инвалидность с детства' },
                { 'id' : 3, 'name': 'Профессиональное заболевание'},
                { 'id' : 4, 'name': 'Трудовое увечье'},
                { 'id' : 5, 'name': 'Военная травма'},
                { 'id' : 6, 'name': 'Заболевание получено в период военной службы'},
                { 'id' : 7, 'name': 'Заболевание получено при исполнении обязанностей военной службы (служебных обязанностей) в связи с аварией на Чернобыльской АЭС'},
                { 'id' : 8, 'name': 'Заболевание радиационно обусловленное, получено при исполнении обязанностей военной службы (служебных обязанностей) в связи с аварией на Чернобыльской АЭС'},
                { 'id' : 9, 'name': 'Заболевание связано с катастрофой на Чернобыльской АЭС'},
                { 'id' : 10, 'name': 'Заболевание получено при исполнении иных обязанностей военной службы (служебных обязанностей), связано с катастрофой на Чернобыльской АЭС '},
                { 'id' : 11, 'name': 'Заболевание связано с аварией на ПО «Маяк»'},
                { 'id' : 12, 'name': 'Заболевание, полученное при исполнении иных обязанностей военной службы (служебных обязанностей), связано с аварией на ПО «Маяк»'},
                { 'id' : 13, 'name': 'Заболевание связано с последствиями радиационных воздействий'},
                { 'id' : 14, 'name': 'Заболевание (травма, увечье, контузия, ранение), полученное при исполнении обязанностей военной службы (служебных обязанностей), связано с непосредственным участием в действиях подразделений особого риска'},
                { 'id' : 15, 'name': 'Инвалидность с детства вследствие ранения, контузии или увечья, связанных с боевыми действиями в период Великой Отечественной войны 1941-1945 годов'},
                { 'id' : 16, 'name': 'Формулировки причин инвалидности, установленные в соответствии с законодательством, действовавшим на момент установления инвалидности (указать)'},
            ]
        });
        
        this.disabilityGroup_store = new Ext.data.JsonStore({
            fields: [
                {name: 'id', type: 'int'},
                {name: 'name', type: 'string'}
            ],
            data:[
                { 'id':1, 'name':'Первая'},
                { 'id':2, 'name':'Вторая'},
                { 'id':3, 'name':'Третья'},
                { 'id':4, 'name':'Категория «ребенок–инвалид»'},
                { 'id':7, 'name':'Инвалидность не установлена'}
            ]
        });
        
        this.rehabPotential_store = new Ext.data.JsonStore({
            fields: [
                {name: 'id', type: 'int'},
                {name: 'name', type: 'string'}
            ],
            data:[
                { 'id': 1, 'name':'Высокий'},
                { 'id': 2, 'name':'Удовлетворительный'},
                { 'id': 3, 'name':'Низкий'},
            ]
        });
        
        this.rehabPrognoz_store = new Ext.data.JsonStore({
            fields: [
                {name: 'id', type: 'int'},
                {name: 'name', type: 'string'}
            ],
            data:[
                { 'id': 1, 'name':'Благоприятный'},
                { 'id': 2, 'name':'Относительно благоприятный'},
                { 'id': 3, 'name':'Сомнительный (неясный)'},
            ]
        });
        
        this.YesNo_store = new Ext.data.SimpleStore({
            fields: [
                {name: 'id', type: 'int'}, 
                {name: 'name', type: 'string'}
            ],
            data: [
                [1, 'Нет'],
                [2, 'Да']
            ]
        });
        //Функция проверки значения «№ ИПРА» и «№ протокола МСЭ»
        /*this.checkfunc = function() {
         var numipra = this.getValue();
         var arr_numipra = numipra.split('/');
         var arr_numipra0 = arr_numipra[0].split('.');
         var cdate = new Date();
         var cyear = cdate.getFullYear();   
         if (arr_numipra0[2] > 12 || arr_numipra[1] > cyear) {
         return false;
         }
         else {
         return true;
         }                
         };
         //Текст при неверном вволе в поля «№ ИПРА» и «№ протокола МСЭ»
         this.invtext = 'Вводимое значение должно иметь формат "AAAAA.BB.CC/DDDD" , где:<br/>\n\
         AAAAA - число, имеющее до 5 цифр;<br/>\n\
         BB - число, имеющее до 2 цифр;<br/>\n\
         CC - число, имеющее до 2 цифр, но не больше 12;<br/>\n\
         DDDD - четырехзначное число, которое не больше текущего года. ';*/
        Ext.apply(this,
                {
                    width:925,
                    autoHeight: true,
                    buttonAlign: 'right',
                    buttons: [
                        {
                            hidden: false,
                            handler: function ()
                            {
                                var base_form = Ext.getCmp('formEditIPRA').getForm();
                                //console.log('wwwwwwwwwwwwwwwwww', getGlobalOptions()); return;
                                if (base_form.isValid()) {
                                    
                                    var data_params = base_form.getValues();
                                    //Костыли без которых перечисленные ниже свойства записываются неверно
                                    delete data_params.Lpu_id;
                                    data_params.Lpu_id = base_form.findField('Lpu_id').getValue();
                                    data_params.IPRAData_DirectionLPU_id = base_form.findField('IPRAData_DirectionLPU_id').getValue();
                                    data_params.IPRAData_DisabilityCause = base_form.findField('IPRAData_DisabilityCause').getValue();
                                    data_params.IPRAData_FGUMCEnumber = base_form.findField('IPRAData_FGUMCEnumber').getValue();
                                    data_params.IPRAData_Confirm = base_form.findField('IPRAData_Confirm').getValue() ? 2 : 1;

                                    //Форматирование времени
                                    data_params.IPRAData_BirthDate = Ext.util.Format.date(base_form.findField('IPRAData_BirthDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_EndDate = Ext.util.Format.date(base_form.findField('IPRAData_EndDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_issueDate = Ext.util.Format.date(base_form.findField('IPRAData_issueDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_ProtocolDate = Ext.util.Format.date(base_form.findField('IPRAData_ProtocolDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_DevelopDate = Ext.util.Format.date(base_form.findField('IPRAData_DevelopDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_MedRehab_begDate = Ext.util.Format.date(base_form.findField('IPRAData_MedRehab_begDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_MedRehab_endDate = Ext.util.Format.date(base_form.findField('IPRAData_MedRehab_endDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_ReconstructSurg_begDate = Ext.util.Format.date(base_form.findField('IPRAData_ReconstructSurg_begDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_ReconstructSurg_endDate = Ext.util.Format.date(base_form.findField('IPRAData_ReconstructSurg_endDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_Orthotics_begDate = Ext.util.Format.date(base_form.findField('IPRAData_Orthotics_begDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_Orthotics_endDate = Ext.util.Format.date(base_form.findField('IPRAData_Orthotics_endDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_DisabilityGroupDate = Ext.util.Format.date(base_form.findField('IPRAData_DisabilityGroupDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_DisabilityEndDate = Ext.util.Format.date(base_form.findField('IPRAData_DisabilityEndDate').getValue(), 'Y.m.d H:i');    
                                    data_params.IPRAData_RepPersonID_IssueDate = Ext.util.Format.date(base_form.findField('IPRAData_RepPersonID_IssueDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_RepPersonAD_IssueDate = Ext.util.Format.date(base_form.findField('IPRAData_RepPersonAD_IssueDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRAData_Version = 2.0;
                                    
                                    data_params.IPRAData_RepPerson_SNILS = data_params.IPRAData_RepPerson_SNILS.replace(/[- ]/g, "");
                                    
                                    //Проверка по псих
                                    if (data_params.IPRAData_FGUMCEnumber.inlist([11, 12, 13, 14, 16])) {
                                        //console.log('Псих');
                                        data_params.Lpu_id = data_params.IPRAData_DirectionLPU_id;
                                    } else {
                                        //console.log('не Псих');
                                        if (getRegionNick() == 'ufa' && Ext.isEmpty(data_params.Lpu_id)) {
                                            if (Ext.isEmpty(data_params.IPRAData_DirectionLPU_id)) {
                                                Ext.getCmp('IPRAData_DirectionLPU_id').allowBlank = false;
                                                Ext.Msg.alert('Ошибка ввода', 'Поле "МО направившая на МСЭ" должно быть заполнено');
                                                return false;
                                            }
                                            else {
                                                data_params.Lpu_id = data_params.IPRAData_DirectionLPU_id; 
                                            }
                                        }
                                    }
                                    data_params.IPRARegistryEditError_id = null;
                                    var params = {
                                        jsondata: Ext.util.JSON.encode({data: [data_params]})
                                    };
                                    //console.log('data_params', data_params);
                                    Ext.Ajax.request({
                                        url: '/?c=IPRARegister&m=saveInRegisterIPRA',
                                        params: params,
                                        callback: function (options, success, response) {
                                            if (success === true) {
                                                //console.log('Отправка данных в регистр ИПРА прошла успешно');
                                                //Ext.getCmp('swIPRARegistryViewWindow').IPRARegistrySearchFrame.getGrid().getStore().baseParams = {};
                                                //Ext.getCmp('swIPRARegistryViewWindow').IPRARegistrySearchFrame.getGrid().getStore().baseParams.SearchFormType = 'IPRARegistry'; 
                                                //Ext.getCmp('swIPRARegistryViewWindow').IPRARegistrySearchFrame.getGrid().getStore().load();
                                                form.close();
                                            } else {
                                                //console.log('Отправка данных в регистр ИПРА не успешна');
                                            }
                                        }
                                    });
                                } else {
                                    Ext.Msg.alert('Ошибка ввода', 'Все выделенные поля формы должны быть заполенены');
                                }

                            },
                            iconCls: 'save16',
                            text: 'Сохранить'
                        },
                        {
                            hidden: false,
                            handler: function ()
                            {
                                form.close();
                            },
                            iconCls: 'close16',
                            text: 'Отмена'
                        }
                    ],
                    items: [
                        new Ext.FormPanel({
                            layout: 'column',
                            id: 'formEditIPRA',
                            height: 700,
                            items: [
                                new Ext.TabPanel({
                                        deferredRender: false,
					activeTab: 0,
					id: 'pacient_tab_panel_edit',
                                        border: false,
					layoutOnTabChange: true,
					plain: true,
                                        width: 900,
					items: [{
						id: 'pacient_tab_edit',
						layout:'column',
						title: 'Пациент',
                                                items: [{
                                                    layout: 'form',
                                                    width: 430,
                                                    border: false,
                                                    style: "padding-left: 10px",
                                                    items:[
                                                    {
                                                        title: 'Пациент',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 150,
                                                        items: [
                                                            {
                                                                fieldLabel: 'Person_id',
                                                                hideLabel: true,
                                                                hidden: true,
                                                                name: 'Person_id',
                                                                width: 180,
                                                                xtype: 'numberfield',
                                                                readOnly: true
                                                            },
                                                            {
                                                                fieldLabel: 'IPRARegistry_id',
                                                                hideLabel: true,
                                                                hidden: true,
                                                                name: 'IPRARegistry_id',
                                                                //id: 'IPRARegistry_id',
                                                                width: 180,
                                                                xtype: 'numberfield',
                                                                readOnly: true
                                                            },
                                                            {
                                                                fieldLabel: 'Lpu_id',
                                                                hideLabel: true,
                                                                hidden: true,
                                                                name: 'Lpu_id',
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                readOnly: true
                                                            },
                                                            {
                                                                fieldLabel: 'Фамилия',
                                                                name: 'Person_SurName',
                                                                //id: 'Person_SurName',
                                                                toUpperCase: true,
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                readOnly: true,
                                                            }, {
                                                                fieldLabel: 'Имя',
                                                                name: 'Person_FirName',
                                                                //id: 'Person_FirName',
                                                                toUpperCase: true,
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                readOnly: true,
                                                            }, {
                                                                fieldLabel: 'Отчество',
                                                                name: 'Person_SecName',
                                                                //id: 'Person_SecName',
                                                                toUpperCase: true,
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                readOnly: true,
                                                            },
                                                            {
                                                                xtype: 'datefield',
                                                                name: 'IPRAData_BirthDate',
                                                                id: 'IPRAData_BirthDate',
                                                                fieldLabel: 'Дата рождения пациента',
                                                                width: 100,
                                                                hideTrigger: true,
                                                                readOnly: true,												
                                                            }, {
                                                                fieldLabel: 'СНИЛС',
                                                                name: 'Person_Snils',
                                                                xtype: 'ocsnilsfield',
                                                                readOnly: true
                                                            }
                                                        ]
                                                    },
                                                    {
                                                        title: 'Законный представитель',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 150,
                                                        items: [
                                                            {
                                                                fieldLabel: 'Фамилия',
                                                                name: 'IPRAData_RepPerson_LastName',
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                maxLength : 30,
                                                                //allowBlank: false
                                                            }, {
                                                                fieldLabel: 'Имя',
                                                                name: 'IPRAData_RepPerson_FirstName',
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                maxLength : 30,
                                                                //allowBlank: false
                                                            }, {
                                                                fieldLabel: 'Отчество',
                                                                name: 'IPRAData_RepPerson_SecondName',
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                maxLength : 30,
                                                                //allowBlank: false
                                                            }, {
                                                                fieldLabel: 'СНИЛС',
                                                                name: 'IPRAData_RepPerson_SNILS',
                                                                xtype: 'ocsnilsfield',
                                                                maxLength : 11,
                                                                //allowBlank: false
                                                            }, {
                                                                autoHeight: true,
                                                                style: 'padding: 0; padding-top: 5px; margin: 0;',
                                                                title: 'Документ, удостоверяющий полномочия',
                                                                xtype: 'fieldset',
                                                                items: [
                                                                    {
                                                                        width: 240,
                                                                        maxLength: 75,
                                                                        xtype: 'textfield',
                                                                        name:'IPRAData_RepPersonAD_Title',
                                                                        fieldLabel: lang['tip']
                                                                    },{
                                                                        fieldLabel: lang['vyidan'],
                                                                        maxLength: 200,
                                                                        id: 'IPRAData_RepPersonAD_Issuer',
                                                                        name: 'IPRAData_RepPersonAD_Issuer',
                                                                        width: 240,
                                                                        xtype: 'textfield',
                                                                    },{
                                                                        fieldLabel: lang['seriya'],
                                                                        maxLength: 10,
                                                                        width: 130,
                                                                        xtype: 'textfield',
                                                                        id:'IPRAData_RepPersonAD_Series'
                                                                    },{
                                                                        fieldLabel: lang['nomer'],
                                                                        maxLength: 30,
                                                                        width: 130,
                                                                        xtype: 'textfield',
                                                                        id:'IPRAData_RepPersonAD_Number'
                                                                    },{
                                                                        xtype: 'swdatefield',
                                                                        fieldLabel: lang['data_vyidachi'],
                                                                        width: 94,
                                                                        name:'IPRAData_RepPersonAD_IssueDate'
                                                                    }

                                                                    ]
                                                            },{
                                                                autoHeight: true,
                                                                style: 'padding: 0; padding-top: 5px; margin: 0;',
                                                                title: 'Документ, удостоверяющий личность',
                                                                xtype: 'fieldset',
                                                                items: [
                                                                    {
                                                                        listWidth: 500,
                                                                        maxLength: 75,
                                                                        width: 240,
                                                                        xtype: 'textfield',
                                                                        id:'IPRAData_RepPersonID_Title',
                                                                        fieldLabel: lang['tip']
                                                                    },
                                                                    {
                                                                        fieldLabel: lang['vyidan'],
                                                                        id: 'IPRAData_RepPersonID_Issuer',
                                                                        maxLength: 200,
                                                                        width: 240,
                                                                        xtype: 'textfield',
                                                                        listWidth: 400
                                                                    },{
                                                                        fieldLabel: lang['seriya'],
                                                                        maxLength: 10,
                                                                        width: 130,
                                                                        xtype: 'textfield',
                                                                        id:'IPRAData_RepPersonID_Series'
                                                                    },{
                                                                        fieldLabel: lang['nomer'],
                                                                        maxLength: 30,
                                                                        width: 130,
                                                                        xtype: 'textfield',
                                                                        id:'IPRAData_RepPersonID_Number'
                                                                    },{
                                                                        xtype: 'swdatefield',
                                                                        fieldLabel: lang['data_vyidachi'],
                                                                        width: 94,
                                                                        id:'IPRAData_RepPersonID_IssueDate'
                                                                    },

                                                                    ]
                                                            }
                                                        ]
                                                    }
                                                    ]
                                                },
                                                {
                                                    layout: 'form',
                                                    width: 470,
                                                    border: false,
                                                    style: "padding-left: 10px",
                                                    labelWidth: 150,
                                                    listeners: {
                                                        render: function () {
                                                            Ext.getCmp('IPRAData_ReconstructSurg_id').store = Ext.getCmp('IPRAData_MedRehab_id').store;
                                                            Ext.getCmp('IPRAData_Orthotics_id').store = Ext.getCmp('IPRAData_MedRehab_id').store;
                                                        }
                                                    },
                                                    items: [
                                                    {
                                                        title: 'Данные о трудовой деятельности',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 250,
                                                        items:[
                                                            {
                                                                name: 'IPRAData_PrimaryProfession',
                                                                width: 180,
                                                                xtype: 'textfield',    
                                                                fieldLabel: 'Основная профессия',
                                                            },{
                                                                name: 'IPRAData_PrimaryProfessionExp',
                                                                width: 180,
                                                                xtype: 'textfield',    
                                                                fieldLabel: 'Стаж работы, лет'
                                                            },{
                                                                name: 'IPRAData_Qualification',
                                                                width: 180,
                                                                xtype: 'textfield',    
                                                                fieldLabel: 'Квалификация'
                                                            },{
                                                                name: 'IPRAData_CurrentJob',
                                                                width: 180,
                                                                xtype: 'textfield',    
                                                                fieldLabel: 'Выполняемая работа на момент МСЭ'
                                                            },{
                                                                name: 'IPRAData_NotWorkYears',
                                                                width: 30,
                                                                xtype: 'numberfield',    
                                                                fieldLabel: 'Не работал лет'
                                                            },{ 
                                                                width: 50,
                                                                listWidth:50,
                                                                xtype: 'combo',
                                                                name: 'IPRAData_EmploymentOrientationExists',
                                                                hiddenName: 'IPRAData_EmploymentOrientationExists',
                                                                fieldLabel: 'Трудовая направленность',
                                                                editable: false,
                                                                triggerAction: 'all',
                                                                mode: 'local',
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                store: this.YesNo_store
                                                            },{ 
                                                                listWidth:50,
                                                                width: 50,
                                                                xtype: 'combo',
                                                                name: 'IPRAData_IsRegisteredInEmploymentService',
                                                                hiddenName: 'IPRAData_IsRegisteredInEmploymentService',
                                                                fieldLabel: 'Состоит на учёте в службе занятости',
                                                                editable: false,
                                                                triggerAction: 'all',
                                                                mode: 'local',
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                store: new Ext.data.SimpleStore({
                                                                    fields: [
                                                                        {name: 'id', type: 'int'}, 
                                                                        {name: 'name', type: 'string'}
                                                                    ],
                                                                    data: [
                                                                        [1, 'Нет'],
                                                                        [2, 'Да']
                                                                    ]
                                                                }),
                                                            }
                                                        ]

                                                        },
                                                        {
                                                            title: 'Показания к проведению реабилитационных или абилитационных мероприятий',
                                                            xtype: 'fieldset',
                                                            autoHeight: true,
                                                            labelWidth: 250,
                                                            items: [
                                                                {
                                                                    layout: 'form',
                                                                    border: false,
                                                                    labelWidth: 250,
                                                                    items: [
                                                                    {
                                                                        listWidth: 180,
                                                                        xtype: 'combo',
                                                                        name: 'IPRAData_RehabPotential',
                                                                        hiddenName: 'IPRAData_RehabPotential',
                                                                        fieldLabel: 'Ребилитационный или абилитационный потенциал',
                                                                        width: 180,
                                                                        editable: false,
                                                                        triggerAction: 'all',
                                                                        mode: 'local',
                                                                        displayField: 'name',
                                                                        valueField: 'id',
                                                                        store: this.rehabPotential_store
                                                                    },
                                                                    {
                                                                        xtype: 'combo',
                                                                        name: 'IPRAData_RehabPrognoz',
                                                                        hiddenName: 'IPRAData_RehabPrognoz',
                                                                        fieldLabel: 'Ребилитационный или абилитационный прогноз',
                                                                        width: 180,
                                                                        editable: false,
                                                                        triggerAction: 'all',
                                                                        mode: 'local',
                                                                        displayField: 'name',
                                                                        valueField: 'id',
                                                                        store: this.rehabPrognoz_store
                                                                    }]
                                                                },
                                                                {
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Способности к самообслуживанию',
                                                                    name: 'IPRAData_SelfService',
                                                                    id: 'IPRAData_SelfService',
                                                                    width: 50,
                                                                    listWidth: 50,
                                                                    editable: false,
                                                                    mode: 'local',
                                                                    displayField: 'sp',
                                                                    triggerAction: 'all',
                                                                    store: form.store
                                                                },
                                                                {
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Способности к передвижению',
                                                                    name: 'IPRAData_Move',
                                                                    id: 'IPRAData_Move',
                                                                    width: 50,
                                                                    listWidth: 50,
                                                                    editable: false,
                                                                    mode: 'local',
                                                                    displayField: 'sp',
                                                                    triggerAction: 'all',
                                                                    store: form.store
                                                                },
                                                                {
                                                                    fieldStyle:     "text-align:right;",
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Способности к ориентации',
                                                                    name: 'IPRAData_Orientation',
                                                                    id: 'IPRAData_Orientation',
                                                                    width: 50,
                                                                    listWidth: 50,
                                                                    editable: false,
                                                                    mode: 'local',
                                                                    displayField: 'sp',
                                                                    triggerAction: 'all',
                                                                    store: form.store
                                                                },
                                                                {
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Способности к общению',
                                                                    name: 'IPRAData_Communicate',
                                                                    id: 'IPRAData_Communicate',
                                                                    width: 50,
                                                                    listWidth: 50,
                                                                    editable: false,
                                                                    mode: 'local',
                                                                    displayField: 'sp',
                                                                    triggerAction: 'all',
                                                                    store: form.store
                                                                },
                                                                {
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Способности к обучению',
                                                                    name: 'IPRAData_Learn',
                                                                    id: 'IPRAData_Learn',
                                                                    width: 50,
                                                                    listWidth: 50,
                                                                    editable: false,
                                                                    mode: 'local',
                                                                    displayField: 'sp',
                                                                    triggerAction: 'all',
                                                                    store: form.store
                                                                },
                                                                {
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Способности к трудовой деятельности',
                                                                    name: 'IPRAData_Work',
                                                                    id: 'IPRAData_Work',
                                                                    width: 50,
                                                                    listWidth: 50,
                                                                    editable: false,
                                                                    mode: 'local',
                                                                    displayField: 'sp',
                                                                    triggerAction: 'all',
                                                                    store: form.store
                                                                },
                                                                {
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Способности к контролю за своим поведением',
                                                                    name: 'IPRAData_Behavior',
                                                                    id: 'IPRAData_Behavior',
                                                                    width: 50,
                                                                    listWidth: 50,
                                                                    editable: false,
                                                                    mode: 'local',
                                                                    displayField: 'sp',
                                                                    triggerAction: 'all',
                                                                    store: form.store
                                                                },
                                                                {
                                                                    layout: 'form',
                                                                    border: false,
                                                                    labelWidth: 250,
                                                                    items: [
                                                                        {
                                                                            xtype: 'combo',
                                                                            fieldLabel: 'Версия ИПРА инвалида',
                                                                            name: 'IPRAData_isFirst',
                                                                            hiddenName: 'IPRAData_isFirst',
                                                                            id: 'IPRAData_isFirst_id',
                                                                            width: 100,
                                                                            listWidth: 100,
                                                                            editable: false,
                                                                            mode: 'local',
                                                                            displayField: 'IPRARegistry_isFirst',
                                                                            valueField: 'num',
                                                                            triggerAction: 'all',
                                                                            store: new Ext.data.SimpleStore({
                                                                                fields: [{name: 'num', type: 'int'}, {name: 'IPRARegistry_isFirst', type: 'string'}],
                                                                                data: [
                                                                                    [2, 'впервые'],
                                                                                    [1, 'повторно']
                                                                                ]
                                                                            }),
                                                                            listeners: {
                                                                                render: function () {
                                                                                    if (this.getValue() == '') {
                                                                                        this.setValue(2);
                                                                                    }
                                                                                }
                                                                            }
                                                                        },
                                                                        {
                                                                            xtype: 'swdatefield',
                                                                            fieldLabel: 'Срок ИПРА (до)',
                                                                            name: 'IPRAData_EndDate',
                                                                            id: 'IPRAData_EndDate',
                                                                            width: 100,
                                                                            listWidth: 100
                                                                        },
                                                                        {
                                                                            xtype: 'swdatefield',
                                                                            fieldLabel: 'Дата выдачи ИПРА инвалида',
                                                                            name: 'IPRAData_issueDate',
                                                                            id: 'IPRAData_issueDate',
                                                                            allowBlank: false,
                                                                            width: 100,
                                                                            listWidth: 100
                                                                        }
                                                                    ]
                                                                }
                                                            ]
                                                        }
                                                    ]
                                                }]
                                            },{
						id: 'disability_tab_edit',
						layout:'column',
						title: 'Данные об инвалидности',
                                                autoHeight: true,
                                                border:false,
                                                items: [
                                                {
                                                    layout: 'form',
                                                    width: 430,
                                                    border: false,
                                                    style: "padding-left: 10px",
                                                    labelWidth: 150,
                                                    items:[
                                                    {
                                                        title: 'Данные об инвалидности',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 150,
                                                        items: [
                                                            {
                                                                xtype: 'combo',
                                                                name: 'IPRAData_DisabilityGroup',
                                                                hiddenName: 'IPRAData_DisabilityGroup',
                                                                fieldLabel: 'Группа инвалидности',
                                                                width: 225,
                                                                listWidth: 225,
                                                                editable: false,
                                                                triggerAction: 'all',
                                                                mode: 'local',
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                store: this.disabilityGroup_store,
                                                                //allowBlank: false

                                                            },
                                                            {
                                                                xtype: 'combo',
                                                                name: 'IPRAData_DisabilityCause',
                                                                hiddenName: 'IPRAData_DisabilityCause',
                                                                fieldLabel: 'Причина инвалидности',
                                                                width: 225,
                                                                listWidth: 725,
                                                                editable: false,
                                                                displayField: 'name',
                                                                mode: 'local',
                                                                valueField: 'id',
                                                                triggerAction: 'all',
                                                                store: this.disabilityCause_store,
                                                                tpl: 	new Ext.XTemplate(
                                                                                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: left;">',
                                                                                '<td style="padding: 2px; width: 5%;">Код</td>',
                                                                                '<td style="padding: 2px; width: 65%;">Наименование</td>',
                                                                                '<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
                                                                                '<td style="padding: 2px;">{id}&nbsp;</td>',
                                                                                '<td style="padding: 2px;">{name}&nbsp;</td>',
                                                                                '</tr></tpl>',
                                                                                '</table>'
                                                                        ),
                                                                listeners:{
                                                                    select: function(combo, record, index){
                                                                        var textform = Ext.getCmp('textform_DisabilityCause_edit');
                                                                        var textarea = Ext.getCmp('IPRAData_DisabilityCauseOther');
                                                                        if(record.get('id')==16){
                                                                            //textarea.allowBlank = false;
                                                                            textform.show();
                                                                        } else {
                                                                            if(textform.hidden == false){
                                                                               // textarea.allowBlank = true;
                                                                                textform.hide();
                                                                                textarea.setValue('');
                                                                            }
                                                                        }
                                                                    }
                                                                },
                                                            },{
                                                                id: 'textform_DisabilityCause_edit',
                                                                layout: 'form',
                                                                hidden:true,
                                                                border: false,
                                                                labelSeparator: '&nbsp',
                                                                items: [
                                                                {
                                                                    id: 'IPRAData_DisabilityCauseOther',
                                                                    maxLength : 512,
                                                                    width: 240,
                                                                    xtype: 'textarea'
                                                                }]
                                                            }, 
                                                             {
                                                                xtype: 'combo',
                                                                name: 'IPRAData_IsDisabilityGroupPrimary',
                                                                hiddenName: 'IPRAData_IsDisabilityGroupPrimary',
                                                                fieldLabel: 'Инвалидность установлена',
                                                                width: 225,
                                                                listWidth: 225,
                                                                editable: false,
                                                                triggerAction: 'all',
                                                                mode: 'local',
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                store: new Ext.data.SimpleStore({
                                                                    fields: [
                                                                        {name: 'id', type: 'int'}, 
                                                                        {name: 'name', type: 'string'}
                                                                    ],
                                                                    data: [
                                                                        [1, 'Повторно'],
                                                                        [2, 'Первично']
                                                                    ]
                                                                })
                                                            },
                                                            {
                                                                xtype: 'swdatefield',
                                                                name: 'IPRAData_DisabilityGroupDate',
                                                                fieldLabel: 'Дата установления инвалидности',
                                                                width: 74
                                                            },
                                                            {
                                                                layout: 'column',
                                                                border: false,
                                                                items: [
                                                                    {
                                                                        layout: 'form',
                                                                        width: 260,
                                                                        border: false,
                                                                        items: [
                                                                            {
                                                                                xtype: 'swdatefield',
                                                                                fieldLabel: 'Срок инвалидности',
                                                                                name: 'Disability_endDate',
                                                                                format: 'd.m.Y',
                                                                                id: 'IPRAData_DisabilityEndDate',
                                                                                width: 74,
                                                                                msgTarget: 'under',
                                                                                invalidText: 'Дата окончания инвалидности должна быть после даты установления инвалидности',

                                                                                validator: function () {
                                                                                    var base_form = Ext.getCmp('formEditIPRA').getForm();
                                                                                    var begDate = base_form.findField('IPRAData_DisabilityGroupDate').getValue();
                                                                                    var endDate = this.getValue();
                                                                                    if ((Ext.isEmpty(begDate) && !Ext.isEmpty(endDate)) || begDate > endDate) {
                                                                                        return false;
                                                                                    } else {
                                                                                        return true;
                                                                                    }
                                                                                }
                                                                            }
                                                                        ]
                                                                    },
                                                                    {
                                                                        layout: 'form',
                                                                        border: false,
                                                                        labelWidth: 60,
                                                                        items: [
                                                                            {
                                                                                xtype: 'checkbox',
                                                                                fieldLabel: 'Бессрочно',
                                                                                id: 'checkbox_DisEndDate_edit',
                                                                                triggerAction: 'all',
                                                                                handler: function(){
                                                                                        var endDate = Ext.getCmp('IPRAData_DisabilityEndDate');
                                                                                        if(this.getValue() == true){
                                                                                            var indefinitely = new Date("9999-12-31");
                                                                                            endDate.setValue(indefinitely);
                                                                                            endDate.hide();
                                                                                        } else {
                                                                                            var together = new Date();
                                                                                            endDate.setValue(together);
                                                                                            endDate.show(false);
                                                                                        }

                                                                                }
                                                                            }
                                                                        ]
                                                                    }
                                                                ]
                                                            },
                                                            {
                                                                xtype: 'combo',
                                                                name: 'IPRAData_IsIntramural',
                                                                hiddenName: 'IPRAData_IsIntramural',
                                                                fieldLabel: 'Ипра разрабатывалась',
                                                                width: 225,
                                                                listWidth: 225,
                                                                editable: false,
                                                                triggerAction: 'all',
                                                                mode: 'local',
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                store: new Ext.data.SimpleStore({
                                                                    fields: [
                                                                        {name: 'id', type: 'int'}, 
                                                                        {name: 'name', type: 'string'}
                                                                    ],
                                                                    data: [
                                                                        [1, 'Заочно'],
                                                                        [2, 'Очно']
                                                                    ]
                                                                })
                                                            }
                                                        ]
                                                    },{
                                                        title: 'Данные МО направившей на МСЭ',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 150,
                                                        items: [
                                                            {
                                                                name: 'IPRAData_DirectionLPU_id',
                                                                fieldLabel: 'МО направившая на МСЭ',
                                                                width: 225,
                                                                listWidth: 225,
                                                                xtype: 'swlpucombo', //Lpu_id
                                                                allowBlank: true,
                                                                id: 'IPRAData_DirectionLPU_id',
                                                                getDataMO: function() {
                                                                    Ext.Ajax.request({
                                                                        url: '/?c=IPRARegister&m=getMOAddressOgrn',
                                                                        url: '/?c=IPRARegister&m=getMOAddressOgrn',
                                                                        params:{
                                                                            DirectionLpu_id: this.getValue(),  
                                                                        },
                                                                        callback: function(options, success, response)
                                                                        {
                                                                            if (success && Ext.getCmp('formEditIPRA')) {
                                                                                var result_json = Ext.util.JSON.decode(response.responseText);
                                                                                var result = result_json[0];
                                                                                Ext.getCmp('UAddress_Address_edit').setValue(result.UAddress_Address);
                                                                                Ext.getCmp('Lpu_OGRN_edit').setValue(result.Lpu_OGRN)
                                                                            }
                                                                        }
                                                                    })
                                                                },
                                                                listeners: {
                                                                    select: function(combo, record, index){
                                                                        var base_form = Ext.getCmp('formEditIPRA').getForm();
                                                                        var address = base_form.findField('UAddress_Address_edit');
                                                                        var ogrn = base_form.findField('Lpu_OGRN_edit');
                                                                        if(index != 0)
                                                                        {
                                                                            address.setValue(null);
                                                                            ogrn.setValue(null);
                                                                            this.getDataMO();
                                                                        }
                                                                        else
                                                                        {
                                                                            address.setValue(null);
                                                                            ogrn.setValue(null)
                                                                        }
                                                                    }
                                                                }
                                                            }, {
                                                                id: 'UAddress_Address_edit',
                                                                fieldLabel: 'Адрес МО',
                                                                toUpperCase: true,
                                                                width: 240,
                                                                xtype: 'textfield',
                                                                readOnly: 'true'
                                                            } , {
                                                                id:'Lpu_OGRN_edit',
                                                                fieldLabel: 'ОГРН МО',
                                                                toUpperCase: true,
                                                                width: 94,
                                                                xtype: 'textfield',
                                                                readOnly: 'true'
                                                            },
                                                            
                                                        ]
                                                    },{
                                                        title: 'Реквизиты ИПРА',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 150,
                                                        items: [
                                                            {
                                                                xtype: 'combo',
                                                                fieldLabel: 'Наименование ФГУ МСЭ',
                                                                name: 'IPRAData_FGUMCEnumber',
                                                                id: 'IPRAData_FGUMCEnumber',
                                                                triggerAction: 'all',
                                                                displayField: 'LpuBuilding_Name',
                                                                valueField: 'LpuBuilding_Code',
                                                                editable: false,
                                                                allowBlank: false,
                                                                width: 225,
                                                                listWidth: 225,
                                                                mode: 'local',
                                                                store: new Ext.data.JsonStore({
                                                                    autoLoad:true,
                                                                    url: '/?c=IPRARegister&m=getAllBureau',
                                                                    fields: [{name: 'LpuBuilding_Code', type: 'int'}, {name: 'LpuBuilding_Name', type: 'string'}]
                                                                }),
                                                                listeners: {
                                                                    valid: function () {
                                                                        if (this.getValue().inlist([11, 12, 13, 14, 16])) {
                                                                            Ext.getCmp('IPRAData_DirectionLPU_id').allowBlank = false;
                                                                        } else {
                                                                            Ext.getCmp('IPRAData_DirectionLPU_id').allowBlank = true;
                                                                        }
                                                                    }
                                                                }
                                                            },
                                                            {
                                                                xtype: 'textfield',
                                                                fieldLabel: '№ ИПРА',
                                                                name: 'IPRAData_Number',
                                                                id: 'IPRAData_Number',
                                                                allowBlank: false,
                                                                width: 180
                                                            },
                                                            {
                                                                xtype: 'textfield',
                                                                fieldLabel: '№ протокола МСЭ',
                                                                name: 'IPRAData_Protocol',
                                                                id: 'IPRAData_Protocol',
                                                                allowBlank: false,
                                                                width: 180
                                                            },
                                                            {
                                                                xtype: 'swdatefield',
                                                                fieldLabel: 'Дата протокола проведения МСЭ',
                                                                name: 'IPRAData_ProtocolDate',
                                                                id: 'IPRAData_ProtocolDate',
                                                                allowBlank: false,
                                                                width: 74
                                                            },
                                                            {
                                                                xtype: 'swdatefield',
                                                                fieldLabel: 'Дата вынесения решений по ИПРА инвалида',
                                                                name: 'IPRAData_DevelopDate',
                                                                id: 'IPRAData_DevelopDate',
                                                                allowBlank: false,
                                                                width: 74
                                                            }
                                                        ]
                                                    },
                                                    ]
                                                },
                                                {
                                                    layout: 'form',
                                                    width: 470,
                                                    border: false,
                                                    style: "padding-left: 10px",
                                                    items:[
                                                    {
                                                        title: 'Мероприятия медицинской реабилитации или абилитации',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 300,
                                                        items: [
                                                            {
                                                                xtype: 'combo',
                                                                fieldLabel: 'Медицинская реабилитация',
                                                                name: 'IPRAData_MedRehab',
                                                                hiddenName: 'IPRAData_MedRehab',
                                                                id: 'IPRAData_MedRehab_id',
                                                                width: 110,
                                                                listWidth: 110,
                                                                editable: false,
                                                                mode: 'local',
                                                                displayField: 'sp',
                                                                valueField: 'data',
                                                                triggerAction: 'all',
                                                                store: new Ext.data.SimpleStore({
                                                                    fields: [
                                                                        {name: 'data', type: 'int'},
                                                                        {name: 'sp', type: 'string'}
                                                                    ],
                                                                    data: [
                                                                        [2, 'Нуждается'], [1, 'Не нуждается']
                                                                    ]
                                                                }),
                                                                listeners: {
                                                                    select: function (combo, record, index) {
                                                                        if (record.get('data') == 2) {
                                                                            Ext.getCmp('IPRAData_MedRehabDate').show();
                                                                            //Ext.getCmp('IPRAData_MedRehab_begDate').allowBlank = false;
                                                                        } else {
                                                                            Ext.getCmp('IPRAData_MedRehab_begDate').setValue('');
                                                                            Ext.getCmp('IPRAData_MedRehab_endDate').setValue('');
                                                                            Ext.getCmp('IPRAData_MedRehabDate').hide();
                                                                            //Ext.getCmp('IPRAData_MedRehab_begDate').allowBlank = true;
                                                                        }
                                                                    }
                                                                }
                                                            },
                                                            {
                                                                layout: 'column',
                                                                border: false,
                                                                id: 'IPRAData_MedRehabDate',
                                                                hidden: true,
                                                                style: 'padding-bottom:10px',
                                                                items: [
                                                                    {
                                                                        layout: 'form',
                                                                        width: 260,
                                                                        labelWidth: 130,
                                                                        border: false,
                                                                        items: [
                                                                            {
                                                                                xtype: 'swdatefield',
                                                                                fieldLabel: 'Срок исполнения с',
                                                                                name: 'IPRAData_MedRehab_begDate',
                                                                                id: 'IPRAData_MedRehab_begDate',
                                                                                width: 74,
                                                                                listeners: {
                                                                                        change: function() {
                                                                                                Ext.getCmp('IPRAData_MedRehab_endDate').validate();
                                                                                        }
                                                                                }
                                                                            }
                                                                        ]
                                                                    },
                                                                    {
                                                                        layout: 'form',
                                                                        border: false,
                                                                        labelWidth: 30,
                                                                        width: 160,
                                                                        items: [
                                                                            {
                                                                                xtype: 'swdatefield',
                                                                                fieldLabel: 'до',
                                                                                name: 'IPRAData_MedRehab_endDate',
                                                                                id: 'IPRAData_MedRehab_endDate',
                                                                                width: 74,
                                                                                msgTarget: 'under',
                                                                                validator: function () {
                                                                                    var base_form = Ext.getCmp('formEditIPRA').getForm();
                                                                                    var needMedRehab = (base_form.findField('IPRAData_MedRehab').getValue() == 2);
                                                                                    var begDate = base_form.findField('IPRAData_MedRehab_begDate').getValue();
                                                                                    var endDate = base_form.findField('IPRAData_MedRehab_endDate').getValue();

                                                                                    if (needMedRehab && ((Ext.isEmpty(begDate) && !Ext.isEmpty(endDate)) || begDate > endDate)) {
                                                                                        return false;
                                                                                    } else {
                                                                                        return true;
                                                                                    }
                                                                                },
                                                                                invalidText: '"Срок исполнения с" должен быть меньше чем значение "до"'
                                                                            }
                                                                        ]
                                                                    }
                                                                ]
                                                            },{
                                                                xtype: 'combo',
                                                                fieldLabel: 'Реконструктивная хирургия',
                                                                name: 'IPRAData_ReconstructSurg',
                                                                hiddenName: 'IPRAData_ReconstructSurg',
                                                                id: 'IPRAData_ReconstructSurg_id',
                                                                width: 110,
                                                                listWidth: 110,
                                                                editable: false,
                                                                mode: 'local',
                                                                displayField: 'sp',
                                                                valueField: 'data',
                                                                triggerAction: 'all',
                                                                listeners: {
                                                                    select: function (combo, record, index) {
                                                                        if (record.get('data') == 2) {
                                                                            Ext.getCmp('IPRAData_ReconstructSurgDate').show();
                                                                            //Ext.getCmp('IPRAData_ReconstructSurg_begDate').allowBlank = false;
                                                                        } else {
                                                                            Ext.getCmp('IPRAData_ReconstructSurg_begDate').setValue('');
                                                                            Ext.getCmp('IPRAData_ReconstructSurg_endDate').setValue('');
                                                                            Ext.getCmp('IPRAData_ReconstructSurgDate').hide();
                                                                            //Ext.getCmp('IPRAData_ReconstructSurg_begDate').allowBlank = true;
                                                                        }

                                                                    }
                                                                }
                                                            },
                                                            {
                                                                layout: 'column',
                                                                border: false,
                                                                hidden: true,
                                                                id: 'IPRAData_ReconstructSurgDate',
                                                                style: 'padding-bottom:10px',
                                                                items: [
                                                                    {
                                                                        layout: 'form',
                                                                        width: 260,
                                                                        labelWidth: 130,
                                                                        border: false,
                                                                        items: [
                                                                            {
                                                                                xtype: 'swdatefield',
                                                                                fieldLabel: 'Срок исполнения с',
                                                                                name: 'IPRAData_ReconstructSurg_begDate',
                                                                                id: 'IPRAData_ReconstructSurg_begDate',
                                                                                width: 74,
                                                                                listeners: {
                                                                                        change: function() {
                                                                                                Ext.getCmp('IPRAData_ReconstructSurg_endDate').validate();
                                                                                        }
                                                                                }
                                                                            }
                                                                        ]
                                                                    },
                                                                    {
                                                                        layout: 'form',
                                                                        border: false,
                                                                        labelWidth: 30,
                                                                        width: 160,
                                                                        items: [
                                                                            {
                                                                                xtype: 'swdatefield',
                                                                                fieldLabel: 'до',
                                                                                name: 'IPRAData_ReconstructSurg_endDate',
                                                                                id: 'IPRAData_ReconstructSurg_endDate',
                                                                                width: 74,
                                                                                msgTarget: 'under',
                                                                                validator: function () {
                                                                                    var base_form = Ext.getCmp('formEditIPRA').getForm();
                                                                                    var needReconstructSurg = (base_form.findField('IPRAData_ReconstructSurg').getValue() == 2);
                                                                                    var begDate = base_form.findField('IPRAData_ReconstructSurg_begDate').getValue();
                                                                                    var endDate = base_form.findField('IPRAData_ReconstructSurg_endDate').getValue();

                                                                                    if (needReconstructSurg && ((Ext.isEmpty(begDate) && !Ext.isEmpty(endDate)) || begDate > endDate)) {
                                                                                        return false;
                                                                                    } else {
                                                                                        return true;
                                                                                    }
                                                                                },
                                                                                invalidText: '"Срок исполнения с" должен быть меньше чем значение "до"'
                                                                            }
                                                                        ]
                                                                    }
                                                                ],
                                                                listeners: {
                                                                    render: function () {
                                                                        //console.log('IPRAData_ReconstructSurg_id', Ext.getCmp('IPRAData_ReconstructSurg_id').getValue());
                                                                        if (!(Ext.getCmp('IPRAData_ReconstructSurg_id').getValue() == 2)) {
                                                                            this.hide();
                                                                        } else {
                                                                            this.show();
                                                                        }
                                                                    }
                                                                }
                                                            },{
                                                                xtype: 'combo',
                                                                fieldLabel: 'Протезирование и ортезирование',
                                                                id: 'IPRAData_Orthotics_id',
                                                                name: 'IPRAData_Orthotics',
                                                                hiddenName: 'IPRAData_Orthotics',
                                                                width: 110,
                                                                listWidth: 110,
                                                                editable: false,
                                                                mode: 'local',
                                                                displayField: 'sp',
                                                                valueField: 'data',
                                                                triggerAction: 'all',
                                                                listeners: {
                                                                    select: function (combo, record, index) {
                                                                        if (record.get('data') == 2) {
                                                                            Ext.getCmp('IPRAData_OrthoticsDate').show();
                                                                            //Ext.getCmp('IPRAData_Orthotics_begDate').allowBlank = false;
                                                                        } else {
                                                                            Ext.getCmp('IPRAData_Orthotics_begDate').setValue('');
                                                                            Ext.getCmp('IPRAData_Orthotics_endDate').setValue('');
                                                                            Ext.getCmp('IPRAData_OrthoticsDate').hide();
                                                                            //Ext.getCmp('IPRAData_Orthotics_begDate').allowBlank = true;
                                                                        }
                                                                    }
                                                                }
                                                            },{
                                                                layout: 'column',
                                                                border: false,
                                                                id: 'IPRAData_OrthoticsDate',
                                                                hidden: true,
                                                                items: [
                                                                    {
                                                                        layout: 'form',
                                                                        width: 260,
                                                                        labelWidth: 130,
                                                                        border: false,
                                                                        items: [
                                                                            {
                                                                                xtype: 'swdatefield',
                                                                                fieldLabel: 'Срок исполнения с',
                                                                                name: 'IPRAData_Orthotics_begDate',
                                                                                id: 'IPRAData_Orthotics_begDate',
                                                                                width: 74,
                                                                                listeners: {
                                                                                        change: function() {
                                                                                                Ext.getCmp('IPRAData_Orthotics_endDate').validate();
                                                                                        }
                                                                                }
                                                                            }
                                                                        ]
                                                                    },
                                                                    {
                                                                        layout: 'form',
                                                                        border: false,
                                                                        labelWidth: 30,
                                                                        width: 160,
                                                                        items: [
                                                                            {
                                                                                xtype: 'swdatefield',
                                                                                fieldLabel: 'до',
                                                                                name: 'IPRAData_Orthotics_endDate',
                                                                                id: 'IPRAData_Orthotics_endDate',
                                                                                width: 74,
                                                                                msgTarget: 'under',
                                                                                validator: function () {
                                                                                    var base_form = Ext.getCmp('formEditIPRA').getForm();
                                                                                    var needOrthotics = (base_form.findField('IPRAData_Orthotics').getValue() == 2);
                                                                                    var begDate = base_form.findField('IPRAData_Orthotics_begDate').getValue();
                                                                                    var endDate = base_form.findField('IPRAData_Orthotics_endDate').getValue();

                                                                                    if (needOrthotics && ((Ext.isEmpty(begDate) && !Ext.isEmpty(endDate)) || begDate > endDate)) {
                                                                                        return false;
                                                                                    } else {
                                                                                        return true;
                                                                                    }
                                                                                },
                                                                                invalidText: '"Срок исполнения с" должен быть меньше чем значение "до"'
                                                                            }
                                                                        ]
                                                                    }
                                                                ]
                                                            }
                                                        ]
                                                    },{
                                                        title: 'Прогнозируемый результат',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 300,
                                                        items: [
                                                            {
                                                                xtype: 'combo',
                                                                fieldLabel: 'Восстановление нарушенных функций',
                                                                name: 'IPRAData_Restoration',
                                                                hiddenName: 'IPRAData_Restoration',
                                                                id: 'IPRAData_Restoration_id',
                                                                width: 110,
                                                                listWidth: 110,
                                                                editable: false,
                                                                mode: 'local',
                                                                triggerAction: 'all',
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                store: this.prognoz_store
                                                            },
                                                            {
                                                                xtype: 'combo',
                                                                fieldLabel: 'Достижение компенсации утраченных либо формирование отсутствующих функций',
                                                                name: 'IPRAData_Compensate',
                                                                hiddenName: 'IPRAData_Compensate',
                                                                id: 'IPRAData_Compensate_id',
                                                                width: 110,
                                                                listWidth: 110,
                                                                editable: false,
                                                                mode: 'local',
                                                                triggerAction: 'all',
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                store: this.prognoz_store
                                                            },{
                                                                layout:'form',
                                                                autoHeight: true,
                                                                labelWidth: 300,
                                                                border:false,
                                                                items:[
                                                                {
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Восстановление (формирование) способности осуществлять самообслуживание',
                                                                    name: 'IPRAData_PrognozSelfService',
                                                                    hiddenName: 'IPRAData_PrognozSelfService',
                                                                    id: 'IPRAData_PrognozSelfService_id',
                                                                    width: 110,
                                                                listWidth: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    displayField: 'name',
                                                                    valueField: 'id',
                                                                    store: this.prognoz_store
                                                                },
                                                                {
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Самостоятельно передвигаться»',
                                                                    name: 'IPRAData_PrognozMoveIndependetly',
                                                                    hiddenName: 'IPRAData_PrognozMoveIndependetly',
                                                                    id: 'IPRAData_PrognozMoveIndependetly_id',
                                                                    width: 110,
                                                                    listWidth: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    valueField: 'id',
                                                                    displayField: 'name',
                                                                    store: this.prognoz_store
                                                                },{
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Ориентрироваться',
                                                                    name: 'IPRAData_PrognozOrientate',
                                                                    hiddenName: 'IPRAData_PrognozOrientate',
                                                                    width: 110,
                                                                    listWidth: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    displayField: 'name',
                                                                    valueField: 'id',
                                                                    store: this.prognoz_store
                                                                },{
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Общаться',
                                                                    name: 'IPRAData_PrognozCommunicate',
                                                                    hiddenName: 'IPRAData_PrognozCommunicate',
                                                                    width: 110,
                                                                    listWidth: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    displayField: 'name',
                                                                    valueField: 'id',
                                                                    store: this.prognoz_store
                                                                },{
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Контролировать свое поведение',
                                                                    name: 'IPRAData_PrognozBehaviorControl',
                                                                    hiddenName: 'IPRAData_PrognozBehaviorControl',
                                                                    width: 110,
                                                                    listWidth: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    displayField: 'name',
                                                                    valueField: 'id',
                                                                    store: this.prognoz_store
                                                                },{
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Обучаться',
                                                                    name: 'IPRAData_PrognozLearning',
                                                                    hiddenName: 'IPRAData_PrognozLearning',
                                                                    width: 110,
                                                                    listWidth: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    displayField: 'name',
                                                                    valueField: 'id',
                                                                    store: this.prognoz_store
                                                                },{
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Заниматься трудовой деятельностью',
                                                                    hiddenName: 'IPRAData_PrognozWork',
                                                                    name: 'IPRAData_PrognozWork',
                                                                    width: 110,
                                                                    listWidth: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    displayField: 'name',
                                                                    valueField: 'id',
                                                                    store: this.prognoz_store
                                                                }]
                                                            }

                                                        ]
                                                    },
                                                    {
                                                        xtype: 'checkbox',
                                                        fieldLabel: 'Подтверждение МО',
                                                        name: 'IPRAData_Confirm',
                                                        id: 'IPRAData_Confirm'
                                                    }
                                                    ]
                                                }
                                                ]
                                            }]
                                    })
                            ]
                        })
                    ]
                });
        sw.Promed.swIPRARegistryEditWindow.superclass.initComponent.apply(this, arguments);
    },
    close: function () {
        this.hide();
        /*this.destroy();
        window[this.objectName] = null;
        delete sw.Promed[this.objectName];*/
    },
    show: function (params)
    {
        Ext.getCmp('formEditIPRA').getForm().findField('Lpu_id').setValue(params.CmpLpu_id);
        Ext.getCmp('formEditIPRA').getForm().findField('Person_id').setValue(params.Person_id);
        Ext.getCmp('formEditIPRA').getForm().findField('Person_SurName').setValue(params.Person_Surname);
        Ext.getCmp('formEditIPRA').getForm().findField('Person_FirName').setValue(params.Person_Firname);
        Ext.getCmp('formEditIPRA').getForm().findField('Person_SecName').setValue(params.Person_Secname);
        Ext.getCmp('formEditIPRA').getForm().findField('IPRAData_BirthDate').setValue(params.Person_Birthday);
        Ext.getCmp('formEditIPRA').getForm().findField('Person_Snils').setValue(params.Person_Snils);
        //console.log('params_swIPRARegistryEditWindow', params);

        if(this.accessCheck()){
            Ext.getCmp('formEditIPRA').getForm().findField('IPRAData_Confirm').disable();
        } else {
            Ext.getCmp('formEditIPRA').getForm().findField('IPRAData_Confirm').enable(); 
        }

        sw.Promed.swIPRARegistryEditWindow.superclass.show.apply(this, arguments);
    }

});