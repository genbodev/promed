/**
 * swIPRARegistryConfirmWindow - Редактирование регистра ИПРА.
 */
sw.Promed.swIPRARegistryConfirmWindow = Ext.extend(sw.Promed.BaseForm, {
    //alwaysOnTop: true,
    id: 'swIPRARegistryConfirmWindow',
    objectName: 'swIPRARegistryConfirmWindow',
    objectSrc: '/jscore/Forms/Admin/ufa/swIPRARegistryConfirmWindow.js',
    layout: 'form',
    title: 'Редактирование регистра ИПРА',
    modal: true,
    width: 1100,
    fieldWidth: 40,
    autoHeight: true,
    closable: true,
    Ministry_of_Health: function() {
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
    undfunc: function (id, value) {
       //console.log(id, typeof Ext.getCmp(id)); 
       return (typeof Ext.getCmp(id) == 'undefined'? value :Ext.getCmp(id).getValue()); 
    },
    forms_setDisabled(value){
        Ext.getCmp('form_RepPerson').setDisabled(value);
        Ext.getCmp('form_profession').setDisabled(value);
        Ext.getCmp('form_rehability').setDisabled(value);
        Ext.getCmp('form_disability').setDisabled(value);
        Ext.getCmp('form_prognozResult').setDisabled(value);
        //var base_form = Ext.getCmp('formIPRA').getForm();
        //base_form.findField('IPRARegistryData_RepPerson_LastName').allowBlank = value;
        //base_form.findField('IPRARegistryData_RepPerson_FirstName').allowBlank = value;
        //base_form.findField('IPRARegistryData_RepPerson_SecondName').allowBlank = value;
        //base_form.findField('IPRARegistryData_RepPerson_SNILS').allowBlank = value;
        //base_form.findField('IPRARegistryData_DisabilityGroup').allowBlank = value;
        //base_form.findField('IPRARegistryData_DisabilityCause').allowBlank = value;
        //if(value)
        //    base_form.findField('IPRARegistryData_DisabilityCauseOther').allowBlank = value;
    },
    resizable: false,
    closeAction: 'hide',
    draggable: true,
    //Confirm: true,
    PersonSearch: function () {
        getWnd('swPersonSearchWindow').show(
                {
                    onClose: function ()
                    {
                    },
                    onSelect: function (params)
                    {
                        //console.log('+++>', params);
                        Ext.getCmp('Person_FirName').setValue(params.Person_Firname);
                        Ext.getCmp('Person_SurName').setValue(params.Person_Surname);
                        Ext.getCmp('Person_SecName').setValue(params.Person_Secname);
                        Ext.getCmp('Lpu_id').setValue(params.CmpLpu_id);
                        Ext.getCmp('IPRARegistryData_BirthDate').setValue(params.Person_Birthday);
                        Ext.getCmp('Person_id').setValue(params.Person_id);
                        this.hide();
                    }
                });
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
                    autoHeight: true,
                    buttonAlign: 'left',
                    buttons: [
                        {
                            id: 'IRCW_MeasuresRehabButton',
                            hidden: getRegionNick() == 'ufa',
                            disabled: !isUserGroup(['IPRARegistry','IPRARegistryEdit']),
                            handler: function() {
                                var base_form = Ext.getCmp('formIPRA').getForm();
                                var grid = Ext.getCmp('IPRA_Number').getGrid();
                                var record = grid.getSelectionModel().getSelected();

                                if (!record || Ext.isEmpty(record.get('IPRARegistry_id'))) {
                                    return false;
                                }

                                var params = {
                                    IPRARegistry_id: record.get('IPRARegistry_id'),
                                    needMedRehab: (base_form.findField('IPRARegistryData_MedRehab_id').getValue() == 2),
                                    needReconstructSurg: (base_form.findField('IPRARegistryData_ReconstructSurg_id').getValue() == 2),
                                    needOrthotics: (base_form.findField('IPRARegistryData_Orthotics_id').getValue() == 2),
                                    begDate: base_form.findField('IPRARegistry_issueDate').getValue(),
                                    endDate: Date.parseDate(getGlobalOptions().date, 'd.m.Y')
                                };

                                var endDate = base_form.findField('IPRARegistry_EndDate').getValue();

                                if (!Ext.isEmpty(endDate)) {
                                    endDate.setDate(endDate.getDate() - 30);

                                    if (endDate < params.endDate) {
                                        params.endDate = endDate;
                                    }
                                }

                                getWnd('swMeasuresRehabViewWindow').show(params);
                            },
                            text: 'Мероприятия реабилитации или абилитации'
                        },
                        '-',
                        {
                            hidden: false,
                          
                            handler: function ()
                            {
                                //console.log('wwwwwwwwwwwwwwwwww', getGlobalOptions()); return;
                                for (var i = 0; i < 34; i++) {
                                    Ext.getCmp('formIPRA').getForm().findField(i).setDisabled(false); //Разлочивание всех полей формы
                                }
                                if (Ext.getCmp('formIPRA').getForm().isValid()) {                                 
    
                                    
                                    //console.log('formIPRA', Ext.getCmp('formIPRA').getForm().getValues()); return;
                                    var data_params = Ext.getCmp('formIPRA').getForm().getValues();
                                    //console.log('data_params',data_params); return;
                                    //Костыли без которых перечисленные ниже свойства записываются неверно
                                    delete data_params.Lpu_id;
                                    data_params.Lpu_id = Ext.getCmp('formIPRA').getForm().findField('Lpu_id').getValue();
                                    data_params.IPRARegistry_DirectionLPU_id = Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_DirectionLPU_id').getValue();
                                    data_params.IPRARegistryData_DisabilityCause = Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_DisabilityCause').getValue();
                                    data_params.IPRARegistry_FGUMCEnumber = Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_FGUMCEnumber').getValue();
                                    data_params.IPRARegistry_Confirm = Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_Confirm').getValue() ? 2 : 1;
                                    
                                    //Форматирование времени
                                    data_params.IPRARegistryData_BirthDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_BirthDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistry_EndDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_EndDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistry_issueDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_issueDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistry_ProtocolDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_ProtocolDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistry_DevelopDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_DevelopDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistryData_MedRehab_begDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_MedRehab_begDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistryData_MedRehab_endDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_MedRehab_endDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistryData_ReconstructSurg_begDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_ReconstructSurg_begDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistryData_ReconstructSurg_endDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_ReconstructSurg_endDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistryData_Orthotics_begDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_Orthotics_begDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistryData_Orthotics_endDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_Orthotics_endDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistryData_DisabilityGroupDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_DisabilityGroupDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistryData_DisabilityEndDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_DisabilityEndDate').getValue(), 'Y.m.d H:i');    
                                    data_params.IPRARegistryData_RepPerson_IdentifyDocDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_RepPerson_IdentifyDocDate').getValue(), 'Y.m.d H:i');
                                    data_params.IPRARegistryData_RepPerson_AuthorityDocDate = Ext.util.Format.date(Ext.getCmp('formIPRA').getForm().findField('IPRARegistryData_RepPerson_AuthorityDocDate').getValue(), 'Y.m.d H:i');

                                    if (data_params.IPRARegistry_FGUMCEnumber.inlist([11, 12, 13, 14, 16])) {
                                        //console.log('Псих');
                                        data_params.Lpu_id = data_params.IPRARegistry_DirectionLPU_id
                                    } else {
                                        //console.log('не Псих');
                                        if (getRegionNick() == 'ufa' && Ext.isEmpty(data_params.Lpu_id)) {
                                            if (Ext.isEmpty(data_params.IPRARegistry_DirectionLPU_id)) {
                                                Ext.getCmp('IPRARegistry_DirectionLPU_id').allowBlank = false;
                                                Ext.Msg.alert('Ошибка ввода', 'Поле "МО направившая на МСЭ" должно быть заполнено');
                                                return false;
                                            } else {
                                                data_params.Lpu_id = data_params.IPRARegistry_DirectionLPU_id;
                                            }
                                        }
                                    }
                                    //console.log('data_params',data_params); return;
                                    data_params.IPRARegistryEditError_id = data_params.IPRARegistry_id;
                                    
                                    var form = Ext.getCmp('swIPRARegistryConfirmWindow');
                                    
                                    //Проверка полей на undefined и задание им в этом случае соответствующих значений
                                    data_params.IPRARegistryData_Behavior = form.undfunc('IPRARegistryData_Behavior', 0);
                                    data_params.IPRARegistryData_Communicate = form.undfunc('IPRARegistryData_Communicate', 0);
                                    data_params.IPRARegistryData_Learn = form.undfunc('IPRARegistryData_Learn', 0);
                                    data_params.IPRARegistryData_Move = form.undfunc('IPRARegistryData_Move', 0);
                                    data_params.IPRARegistryData_Orientation = form.undfunc('IPRARegistryData_Orientation', 0);
                                    data_params.IPRARegistryData_SelfService = form.undfunc('IPRARegistryData_SelfService', 0);
                                    data_params.IPRARegistryData_Work = form.undfunc('IPRARegistryData_Work', 0);
                                    data_params.IPRARegistryData_MedRehab = form.undfunc('IPRARegistryData_MedRehab_id', 1);
                                    data_params.IPRARegistryData_Orthotics = form.undfunc('IPRARegistryData_Orthotics_id', 1);
                                    data_params.IPRARegistryData_ReconstructSurg = form.undfunc('IPRARegistryData_ReconstructSurg_id', 1);
                                    data_params.IPRARegistryData_Restoration = form.undfunc('IPRARegistryData_Restoration_id', 2);
                                    data_params.IPRARegistryData_Compensate = form.undfunc('IPRARegistryData_Compensate_id', 2);
                                    
                                    //console.log('data_params',data_params); return;
                                    
                                    var base_form = Ext.getCmp('formIPRA').getForm();
                                    data_params.IPRARegistryData_RepPerson_SNILS = data_params.IPRARegistryData_RepPerson_SNILS.replace(/[- ]/g, "");
                                    
                                    if (Ext.getCmp('swIPRARegistryConfirmWindow').editErrors) {//Сохранение формы с ошибками
                                        var params = {
                                            jsondata: Ext.util.JSON.encode({data: [data_params]})
                                        };
										
										data_params.idx = 0;
										Ext.Ajax.request(
										{
											params: data_params,
											url: '/?c=IPRARegister&m=checkIPRAdataIsValid',
											callback: function(options, success, response)
											{
												//console.log('checkIPRAdataIsValid-callback!');
												//console.log(response);
												if (success)
												{
													//console.log('checkIPRAdataIsValid-success');
													var res = Ext.util.JSON.decode(response.responseText);
													//var idx = res.idx;
													//grid.getSelectionModel().selectRow(idx);
													//var selected = grid.getSelectionModel().getSelected();
													if (res.isValid == 2) { //если данные ИПРы валидны, то сохраняем
														//selected.set('IPRAData_isValid', res.isValid);
														//selected.commit();
														
														
														//console.log('data_params', data_params);
														Ext.Ajax.request({
															url: '/?c=IPRARegister&m=saveInRegisterIPRA',
															params: params,
															callback: function (options, success, response) {
																if (success === true) {
																	//console.log('Отправка данных в регистр ИПРА прошла успешно');
																	Ext.getCmp('IPRA_ErrorsGrid').getStore().load({limit: 50});
																	Ext.getCmp('swIPRARegistryViewWindow').IPRARegistrySearchFrame.getGrid().getStore().baseParams = {};
																	Ext.getCmp('swIPRARegistryViewWindow').IPRARegistrySearchFrame.getGrid().getStore().baseParams.SearchFormType = 'IPRARegistry'; 
																	Ext.getCmp('swIPRARegistryViewWindow').IPRARegistrySearchFrame.getGrid().getStore().load();                                                    
																	//form.close();
																} else {
																	//console.log('Отправка данных в регистр ИПРА не успешна');
																}
																form.close();
															}
														});
														
														
													} else {
														Ext.Msg.alert('Ошибка ввода', 'Не все поля заполнены корректно');
														//return false;
													}
												}
											}
										});
										

                                    } else {//Сохранение отредактированной формы без ошибок
                                        Ext.Ajax.request({
                                            url: '/?c=IPRARegister&m=IPRARegistry_upd',
                                            params: data_params,
                                            callback: function (options, success, response) {
                                                if (success === true) {
                                                    //console.log('Отправка данных в регистр ИПРА прошла успешно');
                                                } else {
                                                    //console.log('Отправка данных в регистр ИПРА не успешна');
                                                }
                                            }
                                        })
										form.close();
                                    }
                                } else {
                                    Ext.Msg.alert('Ошибка ввода', 'Все выделенные поля формы должны быть заполнены');
                                    return false;
                                }
								//form.close();
                            },
                            iconCls: 'save16',
                            id: 'save',
                            text: 'Сохранить'
                        },
                        {
                            hidden: false,
                            handler: function ()
                            {
                                form.close();
                            },
                            id: 'close',
                            iconCls: 'close16',
                            text: 'Отмена'
                        }
                    ],
                    items: [
                        new Ext.FormPanel({
                            layout: 'column',
                            id: 'formIPRA',
                            height: 700,
                            items: [
                                {
                                    layout: 'form',
                                    width: 170,
                                    border: false,
                                    id: 'choiceIPRA',
                                    style: "padding-left: 10px",
                                    items: [
                                        new sw.Promed.ViewFrame({
                                            /*actions: [
                                             {name: 'action_add', handler: function() { this.openWindow('include'); }.createDelegate(this)},
                                             {name: 'action_edit', handler: function() { this.openViewWindow('edit'); }.createDelegate(this)},
                                             {name: 'action_view',  handler: function() { this.openViewWindow('view'); }.createDelegate(this)},
                                             {name: 'action_delete',  hidden: true, handler: this.deletePersonRegister.createDelegate(this)  },
                                             {name: 'action_refresh'},
                                             {name: 'action_print' }
                                             ], */
                                            autoExpandColumn: 'autoexpand',
                                            autoExpandMin: 110,
                                            autoLoadData: false,
                                            dataUrl: '/?c=IPRARegister&m=getAllIPRARegistry',
                                            id: 'IPRA_Number',
											height: 400,
                                            object: 'IPRA_Number',
                                            pageSize: 100,
											contextmenu: false,
                                            paging: false,
                                            //root: 'data',
                                            stringfields: [
                                                {name: 'IPRARegistry_id', hidden: true, width: 100},
                                                {name: 'IPRARegistry_Number', header: '№ ИПРА', width: 155}
                                            ],
                                            toolbar: false,
                                            border: false,
                                            listeners: {
                                                'render': function () {
                                                    if (!Ext.getCmp('swIPRARegistryConfirmWindow').editErrors) {
                                                       Ext.getCmp('IPRA_Number').getGrid().on(
                                                            'rowclick',function(){
                                                                var main_form = Ext.getCmp('swIPRARegistryConfirmWindow');
                                                                var loadMask = new Ext.LoadMask(main_form.getEl(), {msg: "Загрузка"});
                                                                loadMask.show();
                                                                var IPRARegistry_id =  Ext.getCmp('IPRA_Number').getGrid().getSelectionModel().getSelected().get('IPRARegistry_id');
                                                                main_form.getIPRARegistry(IPRARegistry_id);
                                                            });
                                                    }
                                                }

                                            }
                                        })
                                    ]
                                },
                                new Ext.TabPanel({
                                        deferredRender: false,
					activeTab: 0,
					id: 'pacient_tab_panel',
                                        border: false,
					layoutOnTabChange: true,
					plain: true,
                                        width: 900,
					items: [{
						id: 'pacient_tab',
						layout:'column',
						title: 'Пациент',
                                                items: [{
                                                    layout: 'form',
                                                    width: 430,
                                                    border: false,
                                                    style: "padding-left: 10px",
                                                    items: [
                                                    {
                                                        title: 'Пациент',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 150,
                                                        items: [
                                                            {
                                                                hideLabel: true,
                                                                hidden: true,
                                                                name: 'IPRARegistry_Version',
                                                                id: 'IPRARegistry_Version',
                                                                width: 180,
                                                                xtype: 'numberfield',
                                                                readOnly: true
                                                            },
                                                            {
                                                                fieldLabel: 'Person_id',
                                                                hideLabel: true,
                                                                hidden: true,
                                                                name: 'Person_id',
                                                                id: 'Person_id',
                                                                width: 180,
                                                                xtype: 'numberfield',
                                                                readOnly: true
                                                            },
                                                            {
                                                                fieldLabel: 'IPRARegistry_id',
                                                                hideLabel: true,
                                                                hidden: true,
                                                                name: 'IPRARegistry_id',
                                                                id: 'IPRARegistry_id',
                                                                width: 180,
                                                                xtype: 'numberfield',
                                                                readOnly: true
                                                            },
                                                            {
                                                                name: 'IPRARegistry_IPRAident',
                                                                xtype: 'hidden'
                                                            },
                                                            {
                                                                name: 'IPRARegistry_RecepientType',
                                                                xtype: 'hidden'
                                                            },
                                                            {
                                                                fieldLabel: 'Lpu_id',
                                                                hideLabel: true,
                                                                hidden: true,
                                                                name: 'Lpu_id',
                                                                id: 'Lpu_id',
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                readOnly: true
                                                            },
                                                            {
                                                                fieldLabel: 'Фамилия',
                                                                name: 'Person_SurName',
                                                                id: 'Person_SurName',
                                                                toUpperCase: true,
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                //disabled: true,
                                                                //readOnly: true,
                                                                listeners: {
                                                                    focus: function () {
                                                                        Ext.getCmp('swIPRARegistryConfirmWindow').PersonSearch();
                                                                    }
                                                                }
                                                            }, {
                                                                fieldLabel: 'Имя',
                                                                name: 'Person_FirName',
                                                                id: 'Person_FirName',
                                                                toUpperCase: true,
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                //disabled: true
                                                                listeners: {
                                                                    focus: function () {
                                                                        Ext.getCmp('swIPRARegistryConfirmWindow').PersonSearch();
                                                                    }
                                                                }
                                                            }, {
                                                                fieldLabel: 'Отчество',
                                                                name: 'Person_SecName',
                                                                id: 'Person_SecName',
                                                                toUpperCase: true,
                                                                width: 180,
                                                                xtype: 'textfield',													
                                                                //disabled: true
                                                                listeners: {
                                                                    focus: function () {
                                                                        Ext.getCmp('swIPRARegistryConfirmWindow').PersonSearch();
                                                                    }
                                                                }
                                                            },
                                                            {
                                                                xtype: 'datefield',
                                                                name: 'IPRARegistryData_BirthDate',
                                                                id: 'IPRARegistryData_BirthDate',
                                                                fieldLabel: 'Дата рождения пациента',
                                                                width: 100,
                                                                //disabled: true,
                                                                                                                    hideTrigger: true,
                                                                                                                    readOnly: true,
                                                                listeners: {
                                                                    focus: function () {
                                                                        Ext.getCmp('swIPRARegistryConfirmWindow').PersonSearch();
                                                                    }
                                                                }												
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
                                                        id: 'form_RepPerson',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 150,
                                                        items: [
                                                            {
                                                                fieldLabel: 'Фамилия',
                                                                name: 'IPRARegistryData_RepPerson_LastName',
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                maxLength: 30,
                                                                //allowBlank: false
                                                            }, {
                                                                fieldLabel: 'Имя',
                                                                name: 'IPRARegistryData_RepPerson_FirstName',
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                maxLength: 30,
                                                               // allowBlank: false
                                                            }, {
                                                                fieldLabel: 'Отчество',
                                                                name: 'IPRARegistryData_RepPerson_SecondName',
                                                                width: 180,
                                                                xtype: 'textfield',
                                                                maxLength: 30,
                                                                //allowBlank: false
                                                            }, {
                                                                fieldLabel: 'СНИЛС',
                                                                name: 'IPRARegistryData_RepPerson_SNILS',
                                                                xtype: 'ocsnilsfield',
                                                                maxLength: 11,
                                                                //allowBlank: false
                                                            }, {
                                                                autoHeight: true,
                                                                style: 'padding: 0; padding-top: 5px; margin: 0;',
                                                                title: 'Документ, удостоверяющий полномочия',
                                                                xtype: 'fieldset',
                                                                items: [
                                                                    {
                                                                        width: 240,
                                                                        xtype: 'textfield',
                                                                        id:'IPRARegistryData_RepPerson_AuthorityDocType',
                                                                        fieldLabel: lang['tip'],
                                                                        maxLength: 75
                                                                    },{
                                                                        fieldLabel: lang['vyidan'],
                                                                        id: 'IPRARegistryData_RepPerson_AuthorityDocDep',
                                                                        width: 240,
                                                                        xtype: 'textfield',
                                                                        listWidth: 400,
                                                                        maxLength: 200
                                                                    },{
                                                                        fieldLabel: lang['seriya'],
                                                                        maxLength: 10,
                                                                        width: 130,
                                                                        xtype: 'textfield',
                                                                        id:'IPRARegistryData_RepPerson_AuthorityDocSeries'
                                                                    },{
                                                                        fieldLabel: lang['nomer'],
                                                                        maxLength: 30,
                                                                        width: 130,
                                                                        xtype: 'textfield',
                                                                        id:'IPRARegistryData_RepPerson_AuthorityDocNum',
                                                                    },{
                                                                        xtype: 'swdatefield',
                                                                        fieldLabel: lang['data_vyidachi'],
                                                                        width: 94,
                                                                        id:'IPRARegistryData_RepPerson_AuthorityDocDate'
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
                                                                        id:'IPRARegistryData_RepPerson_IdentifyDocType',
                                                                        fieldLabel: lang['tip']
                                                                    },{
                                                                        fieldLabel: lang['vyidan'],
                                                                        id: 'IPRARegistryData_RepPerson_IdentifyDocDep',
                                                                        maxLength: 200,
                                                                        width: 240,
                                                                        xtype: 'textfield',
                                                                        listWidth: 400
                                                                    },{
                                                                        fieldLabel: lang['seriya'],
                                                                        maxLength: 10,
                                                                        width: 130,
                                                                        xtype: 'textfield',
                                                                        id:'IPRARegistryData_RepPerson_IdentifyDocSeries'
                                                                    },{
                                                                        fieldLabel: lang['nomer'],
                                                                        maxLength: 30,
                                                                        width: 130,
                                                                        xtype: 'textfield',
                                                                        id:'IPRARegistryData_RepPerson_IdentifyDocNum'
                                                                    },{
                                                                        xtype: 'swdatefield',
                                                                        fieldLabel: lang['data_vyidachi'],
                                                                        width: 94,
                                                                        id:'IPRARegistryData_RepPerson_IdentifyDocDate'
                                                                    }
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
                                                    listeners: {
                                                        render: function () {
                                                            Ext.getCmp('IPRARegistryData_ReconstructSurg_id').store = Ext.getCmp('IPRARegistryData_MedRehab_id').store;
                                                            Ext.getCmp('IPRARegistryData_Orthotics_id').store = Ext.getCmp('IPRARegistryData_MedRehab_id').store;
                                                        }
                                                    },
                                                    items: [
                                                    {
                                                        title: 'Данные о трудовой деятельности',
                                                        id: 'form_profession',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 250,
                                                        items:[
                                                            {
                                                                name: 'IPRARegistryData_PrimaryProfession',
                                                                width: 180,
                                                                xtype: 'textfield',    
                                                                fieldLabel: 'Основная профессия',
                                                            },{
                                                                name: 'IPRARegistryData_PrimaryProfessionExperience',
                                                                width: 180,
                                                                xtype: 'textfield',    
                                                                fieldLabel: 'Стаж работы, лет'
                                                            },{
                                                                name: 'IPRARegistryData_Qualification',
                                                                width: 180,
                                                                xtype: 'textfield',    
                                                                fieldLabel: 'Квалификация'
                                                            },{
                                                                name: 'IPRARegistryData_CurrentJob',
                                                                width: 180,
                                                                xtype: 'textfield',    
                                                                fieldLabel: 'Выполняемая работа на момент МСЭ'
                                                            },{
                                                                name: 'IPRARegistryData_NotWorkYears',
                                                                width: 30,
                                                                xtype: 'numberfield',    
                                                                fieldLabel: 'Не работал лет'
                                                            },{ 
                                                                xtype: 'combo',
                                                                fieldLabel: 'Трудовая направленность',
                                                                hiddenName: 'IPRARegistryData_ExistEmploymentOrientation',
                                                                name: 'IPRARegistryData_ExistEmploymentOrientation',
                                                                width: 50,
                                                                listWidth: 50,
                                                                editable: false,
                                                                triggerAction: 'all',
                                                                mode: 'local',
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                store: this.YesNo_store
                                                            },{ 
                                                                xtype: 'combo',
                                                                fieldLabel: 'Состоит на учёте в службе занятости',
                                                                hiddenName: 'IPRARegistryData_isRegInEmplService',
                                                                name: 'IPRARegistryData_isRegInEmplService',
                                                                listWidth: 50,
                                                                width: 50,
                                                                editable: false,
                                                                triggerAction: 'all',
                                                                mode: 'local',
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                store: this.YesNo_store
                                                            }
                                                        ]

                                                        },
                                                        {
                                                            title: 'Показания к проведению реабилитационных или абилитационных мероприятий',
                                                            xtype: 'fieldset',
                                                            autoHeight: true,
                                                            id: 'data',
                                                            labelWidth: 250,
                                                            items: [
                                                                {
                                                                    id: 'form_rehability',
                                                                    layout: 'form',
                                                                    border: false,
                                                                    labelWidth: 250,
                                                                    items: [
                                                                    {
                                                                        xtype: 'combo',
                                                                        name: 'IPRARegistryData_RehabPotential',
                                                                        hiddenName: 'IPRARegistryData_RehabPotential',
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
                                                                        name: 'IPRARegistryData_RehabPrognoz',
                                                                        hiddenName: 'IPRARegistryData_RehabPrognoz',
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
                                                                    name: 'IPRARegistryData_SelfService',
                                                                    id: 'IPRARegistryData_SelfService',
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
                                                                    name: 'IPRARegistryData_Move',
                                                                    id: 'IPRARegistryData_Move',
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
                                                                    name: 'IPRARegistryData_Orientation',
                                                                    id: 'IPRARegistryData_Orientation',
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
                                                                    name: 'IPRARegistryData_Communicate',
                                                                    id: 'IPRARegistryData_Communicate',
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
                                                                    name: 'IPRARegistryData_Learn',
                                                                    id: 'IPRARegistryData_Learn',
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
                                                                    name: 'IPRARegistryData_Work',
                                                                    id: 'IPRARegistryData_Work',
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
                                                                    name: 'IPRARegistryData_Behavior',
                                                                    id: 'IPRARegistryData_Behavior',
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
                                                                            name: 'IPRARegistry_isFirst',
                                                                            hiddenName: 'IPRARegistry_isFirst',
                                                                            id: 'IPRARegistry_isFirst_id',
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
                                                                            name: 'IPRARegistry_EndDate',
                                                                            id: 'IPRARegistry_EndDate',
                                                                            width: 100,
                                                                            listWidth: 100
                                                                        },
                                                                        {
                                                                            xtype: 'swdatefield',
                                                                            fieldLabel: 'Дата выдачи ИПРА инвалида',
                                                                            name: 'IPRARegistry_issueDate',
                                                                            id: 'IPRARegistry_issueDate',
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
						id: 'disability_tab',
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
                                                        id: 'form_disability',
                                                        xtype: 'fieldset',
                                                        autoHeight: true,
                                                        labelWidth: 150,
                                                        items: [
                                                            {
                                                                xtype: 'combo',
                                                                name: 'IPRARegistryData_DisabilityGroup',
                                                                hiddenName: 'IPRARegistryData_DisabilityGroup',
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
                                                                id: 'IPRARegistryData_DisabilityCause_id',
                                                                name: 'IPRARegistryData_DisabilityCause',
                                                                hiddenName: 'IPRARegistryData_DisabilityCause',
                                                                fieldLabel: 'Причина инвалидности',
                                                                width: 225,
                                                                listWidth: 725,
                                                                editable: false,
                                                                displayField: 'name',
                                                                mode: 'local',
                                                                valueField: 'id',
                                                                triggerAction: 'all',
                                                                store: this.disabilityCause_store,
                                                                resizable: true,
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
                                                                        var textform = Ext.getCmp('textform_DisabilityCause');
                                                                        var textarea = Ext.getCmp('IPRARegistryData_DisabilityCauseOther');
                                                                        if(record.get('id') == 16){
                                                                            //textarea.allowBlank=false;
                                                                            textform.show();
                                                                        } else {
                                                                            if(textform.hidden == false){
                                                                                textform.hide();
                                                                                //textarea.allowBlank=true;
                                                                                textarea.setValue('');
                                                                            }
                                                                        }
                                                                    }
                                                                },
                                                            },{
                                                                id: 'textform_DisabilityCause',
                                                                layout: 'form',
                                                                hidden:true,
                                                                border: false,
                                                                labelSeparator: '&nbsp',
                                                                items: [
                                                                {
                                                                    id: 'IPRARegistryData_DisabilityCauseOther',
                                                                    maxLength : 512,
                                                                    width: 225,
                                                                    xtype: 'textarea'
                                                                }]
                                                            }, 
                                                             {
                                                                xtype: 'combo',
                                                                name: 'IPRARegistryData_IsDisabilityGroupPrimary',
                                                                hiddenName: 'IPRARegistryData_IsDisabilityGroupPrimary',
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
                                                                name: 'IPRARegistryData_DisabilityGroupDate',
                                                                fieldLabel: 'Дата установления инвалидности',
                                                                width: 74
                                                            },
                                                            {
                                                                layout: 'column',
                                                                border: false,
                                                                id: 'PeriodOfDisability',
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
                                                                                id: 'IPRARegistryData_DisabilityEndDate',
                                                                                width: 74,
                                                                                msgTarget: 'under',
                                                                                invalidText: 'Дата окончания инвалидности должна быть после даты установления инвалидности',

                                                                                validator: function () {
                                                                                    var base_form = Ext.getCmp('formIPRA').getForm();
                                                                                    var begDate = base_form.findField('IPRARegistryData_DisabilityGroupDate').getValue();
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
                                                                                id: 'checkbox_DisEndDate',
                                                                                triggerAction: 'all',
                                                                                handler: function(){
                                                                                        var endDate = Ext.getCmp('IPRARegistryData_DisabilityEndDate');
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
                                                                name: 'IPRARegistryData_IsIntramural',
                                                                hiddenName: 'IPRARegistryData_IsIntramural',
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
                                                                name: 'IPRARegistry_DirectionLPU_id',
                                                                fieldLabel: 'МО направившая на МСЭ',
                                                                width: 225,
                                                                listWidth: 225,
                                                                xtype: 'swlpucombo', //Lpu_id
                                                                allowBlank: true,
                                                                id: 'IPRARegistry_DirectionLPU_id',
                                                                getMOAddressOgrn: function() {
                                                                    Ext.Ajax.request({
                                                                        url: '/?c=IPRARegister&m=getMOAddressOgrn',
                                                                        params:{
                                                                            DirectionLpu_id: this.getValue(),  
                                                                        },
                                                                        callback: function(options, success, response)
                                                                        {
                                                                            if (success && Ext.getCmp('formIPRA')) {
                                                                                var result_json = Ext.util.JSON.decode(response.responseText);
                                                                                var result = result_json[0];
                                                                                Ext.getCmp('UAddress_Address').setValue(result.UAddress_Address);
                                                                                Ext.getCmp('Lpu_OGRN').setValue(result.Lpu_OGRN)
                                                                            }
                                                                        }
                                                                    })
                                                                },
                                                                listeners: {
                                                                    select: function(combo, record, index){
                                                                        var base_form = Ext.getCmp('formIPRA').getForm();
                                                                        var address = base_form.findField('UAddress_Address');
                                                                        var ogrn = base_form.findField('Lpu_OGRN');
                                                                        if(index != 0)
                                                                        {
                                                                            address.setValue(null);
                                                                            ogrn.setValue(null);
                                                                            this.getMOAddressOgrn();
                                                                        }
                                                                        else
                                                                        {
                                                                            address.setValue(null);
                                                                            ogrn.setValue(null)
                                                                        }
                                                                    }
                                                                }
                                                            }, {
                                                                id: 'UAddress_Address',
                                                                fieldLabel: 'Адрес МО',
                                                                toUpperCase: true,
                                                                width: 240,
                                                                xtype: 'textfield',
                                                                readOnly: 'true'
                                                            } , {
                                                                id:'Lpu_OGRN',
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
                                                                name: 'IPRARegistry_FGUMCEnumber',
                                                                id: 'IPRARegistry_FGUMCEnumber',
                                                                triggerAction: 'all',
                                                                displayField: 'LpuBuilding_Name',
                                                                valueField: 'LpuBuilding_Code',
                                                                editable: false,
                                                                allowBlank: false,
                                                                mode: 'local',
                                                                width: 225,
                                                                listWidth: 225,
                                                                store: new Ext.data.JsonStore({
                                                                    autoLoad: true,
                                                                    url: '/?c=IPRARegister&m=getAllBureau',
                                                                    fields: [{name: 'LpuBuilding_Code', type: 'int'}, {name: 'LpuBuilding_Name', type: 'string'}]
                                                                }),
                                                                listeners: {
                                                                    valid: function () {
                                                                        if (this.getValue().inlist([11, 12, 13, 14, 16])) {
                                                                            Ext.getCmp('IPRARegistry_DirectionLPU_id').allowBlank = false;
                                                                        } else {
                                                                            Ext.getCmp('IPRARegistry_DirectionLPU_id').allowBlank = true;
                                                                        }
                                                                    }
                                                                }
                                                            },
                                                            {
                                                                xtype: 'textfield',
                                                                fieldLabel: '№ ИПРА',
                                                                name: 'IPRARegistry_Number',
                                                                id: 'IPRRegistry_Number',
                                                                allowBlank: false,
                                                                width: 180
                                                            },
                                                            {
                                                                xtype: 'textfield',
                                                                fieldLabel: '№ протокола МСЭ',
                                                                name: 'IPRARegistry_Protocol',
                                                                id: 'IPRARegistry_Protocol',
                                                                allowBlank: false,
                                                                width: 180
                                                            },
                                                            {
                                                                xtype: 'swdatefield',
                                                                fieldLabel: 'Дата протокола проведения МСЭ',
                                                                name: 'IPRARegistry_ProtocolDate',
                                                                id: 'IPRARegistry_ProtocolDate',
                                                                allowBlank: false,
                                                                width: 74
                                                            },
                                                            {
                                                                xtype: 'swdatefield',
                                                                fieldLabel: 'Дата вынесения решений по ИПРА инвалида',
                                                                name: 'IPRARegistry_DevelopDate',
                                                                id: 'IPRARegistry_DevelopDate',
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
                                                                name: 'IPRARegistryData_MedRehab',
                                                                hiddenName: 'IPRARegistryData_MedRehab',
                                                                id: 'IPRARegistryData_MedRehab_id',
                                                                listWidth:110,
                                                                width: 110,
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
                                                                            Ext.getCmp('IPRARegistryData_MedRehabDate').show();
                                                                            //Ext.getCmp('IPRARegistryData_MedRehab_begDate').allowBlank = false;
                                                                        } else {
                                                                            Ext.getCmp('IPRARegistryData_MedRehab_begDate').setValue('');
                                                                            Ext.getCmp('IPRARegistryData_MedRehab_endDate').setValue('');
                                                                            Ext.getCmp('IPRARegistryData_MedRehabDate').hide();
                                                                            //Ext.getCmp('IPRARegistryData_MedRehab_begDate').allowBlank = true;
                                                                        }
                                                                    }
                                                                }
                                                            },
                                                            {
                                                                layout: 'column',
                                                                border: false,
                                                                id: 'IPRARegistryData_MedRehabDate',
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
                                                                                name: 'IPRARegistryData_MedRehab_begDate',
                                                                                id: 'IPRARegistryData_MedRehab_begDate',
                                                                                width: 74,
                                                                                listeners: {
                                                                                        change: function() {
                                                                                                Ext.getCmp('IPRARegistryData_MedRehab_endDate').validate();
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
                                                                                name: 'IPRARegistryData_MedRehab_endDate',
                                                                                id: 'IPRARegistryData_MedRehab_endDate',
                                                                                width: 74,
                                                                                msgTarget: 'under',
                                                                                validator: function () {
                                                                                    var base_form = Ext.getCmp('formIPRA').getForm();
                                                                                    var needMedRehab = (base_form.findField('IPRARegistryData_MedRehab').getValue() == 2);
                                                                                    var begDate = base_form.findField('IPRARegistryData_MedRehab_begDate').getValue();
                                                                                    var endDate = base_form.findField('IPRARegistryData_MedRehab_endDate').getValue();

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
                                                                name: 'IPRARegistryData_ReconstructSurg',
                                                                hiddenName: 'IPRARegistryData_ReconstructSurg',
                                                                id: 'IPRARegistryData_ReconstructSurg_id',
                                                                width: 110,
                                                                listWidth:110,
                                                                editable: false,
                                                                mode: 'local',
                                                                displayField: 'sp',
                                                                valueField: 'data',
                                                                triggerAction: 'all',
                                                                listeners: {
                                                                    select: function (combo, record, index) {
                                                                        if (record.get('data') == 2) {
                                                                            Ext.getCmp('IPRARegistryData_ReconstructSurgDate').show();
                                                                            //Ext.getCmp('IPRARegistryData_ReconstructSurg_begDate').allowBlank = false;
                                                                        } else {
                                                                            Ext.getCmp('IPRARegistryData_ReconstructSurg_begDate').setValue('');
                                                                            Ext.getCmp('IPRARegistryData_ReconstructSurg_endDate').setValue('');
                                                                            Ext.getCmp('IPRARegistryData_ReconstructSurgDate').hide();
                                                                            //Ext.getCmp('IPRARegistryData_ReconstructSurg_begDate').allowBlank = true;
                                                                        }

                                                                    }
                                                                }
                                                            },
                                                            {
                                                                layout: 'column',
                                                                border: false,
                                                                hidden: true,
                                                                id: 'IPRARegistryData_ReconstructSurgDate',
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
                                                                                name: 'IPRARegistryData_ReconstructSurg_begDate',
                                                                                id: 'IPRARegistryData_ReconstructSurg_begDate',
                                                                                width: 74,
                                                                                listeners: {
                                                                                        change: function() {
                                                                                                Ext.getCmp('IPRARegistryData_ReconstructSurg_endDate').validate();
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
                                                                                name: 'IPRARegistryData_ReconstructSurg_endDate',
                                                                                id: 'IPRARegistryData_ReconstructSurg_endDate',
                                                                                width: 74,
                                                                                msgTarget: 'under',
                                                                                validator: function () {
                                                                                    var base_form = Ext.getCmp('formIPRA').getForm();
                                                                                    var needReconstructSurg = (base_form.findField('IPRARegistryData_ReconstructSurg').getValue() == 2);
                                                                                    var begDate = base_form.findField('IPRARegistryData_ReconstructSurg_begDate').getValue();
                                                                                    var endDate = base_form.findField('IPRARegistryData_ReconstructSurg_endDate').getValue();

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
                                                                        //console.log('IPRARegistryData_ReconstructSurg_id', Ext.getCmp('IPRARegistryData_ReconstructSurg_id').getValue());
                                                                        if (!(Ext.getCmp('IPRARegistryData_ReconstructSurg_id').getValue() == 2)) {
                                                                            this.hide();
                                                                        } else {
                                                                            this.show();
                                                                        }
                                                                    }
                                                                }
                                                            },{
                                                                xtype: 'combo',
                                                                fieldLabel: 'Протезирование и ортезирование',
                                                                id: 'IPRARegistryData_Orthotics_id',
                                                                name: 'IPRARegistryData_Orthotics',
                                                                hiddenName: 'IPRARegistryData_Orthotics',
                                                                width: 110,
                                                                listWidth:110,
                                                                editable: false,
                                                                mode: 'local',
                                                                displayField: 'sp',
                                                                valueField: 'data',
                                                                triggerAction: 'all',
                                                                listeners: {
                                                                    select: function (combo, record, index) {
                                                                        if (record.get('data') == 2) {
                                                                            Ext.getCmp('IPRARegistryData_OrthoticsDate').show();
                                                                            //Ext.getCmp('IPRARegistryData_Orthotics_begDate').allowBlank = false;
                                                                        } else {
                                                                            Ext.getCmp('IPRARegistryData_Orthotics_begDate').setValue('');
                                                                            Ext.getCmp('IPRARegistryData_Orthotics_endDate').setValue('');
                                                                            Ext.getCmp('IPRARegistryData_OrthoticsDate').hide();
                                                                            //Ext.getCmp('IPRARegistryData_Orthotics_begDate').allowBlank = true;
                                                                        }
                                                                    }
                                                                }
                                                            },{
                                                                layout: 'column',
                                                                border: false,
                                                                id: 'IPRARegistryData_OrthoticsDate',
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
                                                                                name: 'IPRARegistryData_Orthotics_begDate',
                                                                                id: 'IPRARegistryData_Orthotics_begDate',
                                                                                width: 74,
                                                                                listeners: {
                                                                                        change: function() {
                                                                                                Ext.getCmp('IPRARegistryData_Orthotics_endDate').validate();
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
                                                                                name: 'IPRARegistryData_Orthotics_endDate',
                                                                                id: 'IPRARegistryData_Orthotics_endDate',
                                                                                width: 74,
                                                                                msgTarget: 'under',
                                                                                validator: function () {
                                                                                    var base_form = Ext.getCmp('formIPRA').getForm();
                                                                                    var needOrthotics = (base_form.findField('IPRARegistryData_Orthotics').getValue() == 2);
                                                                                    var begDate = base_form.findField('IPRARegistryData_Orthotics_begDate').getValue();
                                                                                    var endDate = base_form.findField('IPRARegistryData_Orthotics_endDate').getValue();

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
                                                                name: 'IPRARegistryData_Restoration',
                                                                hiddenName: 'IPRARegistryData_Restoration',
                                                                id: 'IPRARegistryData_Restoration_id',
                                                                listWidth: 110,
                                                                width: 110,
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
                                                                name: 'IPRARegistryData_Compensate',
                                                                hiddenName: 'IPRARegistryData_Compensate',
                                                                id: 'IPRARegistryData_Compensate_id',
                                                                listWidth: 110,
                                                                width: 110,
                                                                editable: false,
                                                                mode: 'local',
                                                                triggerAction: 'all',
                                                                displayField: 'name',
                                                                valueField: 'id',
                                                                store: this.prognoz_store
                                                            },{
                                                                id: 'form_prognozResult',
                                                                layout:'form',
                                                                autoHeight: true,
                                                                labelWidth: 300,
                                                                border:false,
                                                                items:[
                                                                {
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Восстановление (формирование) способности осуществлять самообслуживание',
                                                                    name: 'IPRARegistryData_PrognozResult_SelfService',
                                                                    hiddenName: 'IPRARegistryData_PrognozResult_SelfService',
                                                                    id: 'IPRARegistryData_PrognozResult_SelfService_id',
                                                                    listWidth: 110,
                                                                    width: 110,
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
                                                                    name: 'IPRARegistryData_PrognozResult_Independently',
                                                                    hiddenName: 'IPRARegistryData_PrognozResult_Independently',
                                                                    id: 'IPRARegistryData_PrognozResult_Independently_id',
                                                                    listWidth: 110,
                                                                    width: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    valueField: 'id',
                                                                    displayField: 'name',
                                                                    store: this.prognoz_store
                                                                },{
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Ориентрироваться',
                                                                    name: 'IPRARegistryData_PrognozResult_Orientate',
                                                                    hiddenName: 'IPRARegistryData_PrognozResult_Orientate',
                                                                    listWidth: 110,
                                                                    width: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    displayField: 'name',
                                                                    valueField: 'id',
                                                                    store: this.prognoz_store
                                                                },{
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Общаться',
                                                                    name: 'IPRARegistryData_PrognozResult_Communicate',
                                                                    hiddenName: 'IPRARegistryData_PrognozResult_Communicate',
                                                                    listWidth: 110,
                                                                    width: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    displayField: 'name',
                                                                    valueField: 'id',
                                                                    store: this.prognoz_store
                                                                },{
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Контролировать свое поведение',
                                                                    name: 'IPRARegistryData_PrognozResult_BehaviorControl',
                                                                    hiddenName: 'IPRARegistryData_PrognozResult_BehaviorControl',
                                                                    listWidth: 110,
                                                                    width: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    displayField: 'name',
                                                                    valueField: 'id',
                                                                    store: this.prognoz_store
                                                                },{
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Обучаться',
                                                                    name: 'IPRARegistryData_PrognozResult_Learning',
                                                                    hiddenName: 'IPRARegistryData_PrognozResult_Learning',
                                                                    listWidth: 110,
                                                                    width: 110,
                                                                    editable: false,
                                                                    triggerAction: 'all',
                                                                    mode: 'local',
                                                                    displayField: 'name',
                                                                    valueField: 'id',
                                                                    store: this.prognoz_store
                                                                },{
                                                                    xtype: 'combo',
                                                                    fieldLabel: 'Заниматься трудовой деятельностью',
                                                                    hiddenName: 'IPRARegistryData_PrognozResult_Work',
                                                                    name: 'IPRARegistryData_PrognozResult_Work',
                                                                    listWidth: 110,
                                                                    width: 110,
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
                                                        name: 'IPRARegistry_Confirm',
                                                        id: 'IPRARegistry_Confirm'
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
        sw.Promed.swIPRARegistryConfirmWindow.superclass.initComponent.apply(this, arguments);
    },
    close: function () {
        this.hide();
        /*this.destroy();
        window[this.objectName] = null;
        delete sw.Promed[this.objectName];*/
    },
    show: function (params)
    {
        //Ext.getCmp('swIPRARegistryConfirmWindow').setDisabled(true);
		/*if (document.getElementById("swIPRARegistryEditWindow") != null) { //Удаляем форму, если она есть (устраняем глюк с появлением окна)
			//console.log('удаляем');
			document.getElementById("swIPRARegistryEditWindow").remove();
		}*/
                
		var base_form = Ext.getCmp('formIPRA').getForm();

		base_form.findField('IPRARegistry_DirectionLPU_id').enable();
		base_form.findField('IPRRegistry_Number').enable();

        //console.log('params', params);
        //Заполнение данной формы в зависимости от того, какая форма ее открыла: swIPRARegistryErrors, либо swIPRARegistryViewWindow
        if (params.editErrors) {
            this.editErrors = true;
            Ext.getCmp('choiceIPRA').hide();
            Ext.getCmp('IRCW_MeasuresRehabButton').hide();
            this.setWidth(930);
            this.doLayout();
            var row = params.row;
            console.log('-------->', params.row);
            base_form.setValues(
                    {
                        IPRARegistry_IPRAident: row.get('IPRARegistry_IPRAident'),
                        IPRARegistry_RecepientType: row.get('IPRARegistry_RecepientType'),
                        IPRARegistryData_Behavior: row.get('IPRARegistryError_Behavior'),
                        IPRARegistryData_Communicate: row.get('IPRARegistryError_Communicate'),
                        IPRARegistryData_Compensate: row.get('IPRARegistryError_Compensate'),
                        IPRARegistryData_Learn: row.get('IPRARegistryError_Learn'),
                        IPRARegistryData_MedRehab: row.get('IPRARegistryError_MedRehab'),
                        IPRARegistryData_MedRehab_begDate: row.get('IPRARegistryError_MedRehab_begDate'),
                        IPRARegistryData_MedRehab_endDate: row.get('IPRARegistryError_MedRehab_endDate'),
                        IPRARegistryData_Move: row.get('IPRARegistryError_Move'),
                        IPRARegistryData_Orientation: row.get('IPRARegistryError_Orientation'),
                        IPRARegistryData_Orthotics: row.get('IPRARegistryError_Orthotics'),
                        IPRARegistryData_Orthotics_begDate: row.get('IPRARegistryError_Orthotics_begDate'),
                        IPRARegistryData_Orthotics_endDate: row.get('IPRARegistryError_Orthotics_endDate'),
                        IPRARegistryData_ReconstructSurg: row.get('IPRARegistryError_ReconstructSurg'),
                        IPRARegistryData_ReconstructSurg_begDate: row.get('IPRARegistryError_ReconstructSurg_begDate'),
                        IPRARegistryData_ReconstructSurg_endDate: row.get('IPRARegistryError_ReconstructSurg_endDate'),
                        IPRARegistryData_Restoration: row.get('IPRARegistryError_Restoration'),
                        IPRARegistryData_SelfService: row.get('IPRARegistryError_SelfService'),
                        IPRARegistryData_Work: row.get('IPRARegistryError_Work'),
                        IPRARegistry_DevelopDate: row.get('IPRARegistry_DevelopDate'),
                        IPRARegistry_EndDate: row.get('IPRARegistry_EndDate'),
                        //IPRARegistry_FGUMCEnumber: row.get('IPRARegistry_FGUMCE'),
                        IPRARegistry_Number: row.get('IPRARegistry_Number'),
                        IPRARegistry_Protocol: row.get('IPRARegistry_Protocol'),
                        IPRARegistry_ProtocolDate: row.get('IPRARegistry_ProtocolDate'),
                        IPRARegistry_id: row.get('IPRARegistry_id'),
                        IPRARegistry_isFirst: row.get('IPRARegistry_isFirst'),
                        IPRARegistry_issueDate: row.get('IPRARegistry_issueDate'),
                        IPRARegistry_DirectionLPU_id: row.get('IPRARegistry_DirectionLPU_id'),
                        Person_FirName: row.get('Person_FirName'),
                        Person_SurName: row.get('Person_SurName'),
                        Person_SecName: row.get('Person_SecName'),
                        IPRARegistryData_BirthDate: row.get('Person_BirthDay'),
                        Lpu_id: row.get('LpuAttach_id'),
                        Person_id: row.get('Person_id'),
                        Person_Snils: row.get('Person_Snils'),
                        IPRARegistryData_PrimaryProfession              : row.get('IPRARegistryError_PrimaryProfession'),
                        IPRARegistryData_PrimaryProfessionExperience    : row.get('IPRARegistryError_PrimaryProfessionExperience'),
                        IPRARegistryData_Qualification                  : row.get('IPRARegistryError_Qualification'),
                        IPRARegistryData_CurrentJob                     : row.get('IPRARegistryError_CurrentJob'),
                        IPRARegistryData_NotWorkYears                   : row.get('IPRARegistryError_NotWorkYears'),
                        IPRARegistryData_ExistEmploymentOrientation     : row.get('IPRARegistryError_ExistEmploymentOrientation'),
                        IPRARegistryData_isRegInEmplService             : row.get('IPRARegistryError_isRegInEmplService'),
                        IPRARegistryData_IsDisabilityGroupPrimary       : row.get('IPRARegistryError_IsDisabilityGroupPrimary'),
                        IPRARegistryData_IsIntramural                   : row.get('IPRARegistryError_IsIntramural'),
                        IPRARegistryData_DisabilityGroupDate            : row.get('IPRARegistryError_DisabilityGroupDate'),
                        IPRARegistryData_DisabilityEndDate              : row.get('IPRARegistryError_DisabilityEndDate'),
                        IPRARegistryData_DisabilityGroup                : row.get('IPRARegistryError_DisabilityGroup'),
                        IPRARegistryData_DisabilityCause                : row.get('IPRARegistryError_DisabilityCause'),
                        IPRARegistryData_RehabPotential                 : row.get('IPRARegistryError_RehabPotential'),
                        IPRARegistryData_RehabPrognoz                   : row.get('IPRARegistryError_RehabPrognoz'),
                        IPRARegistryData_PrognozResult_SelfService      : row.get('IPRARegistryError_PrognozResult_SelfService'),
                        IPRARegistryData_PrognozResult_Orientate        : row.get('IPRARegistryError_PrognozResult_Orientate'),
                        IPRARegistryData_PrognozResult_Communicate      : row.get('IPRARegistryError_PrognozResult_Communicate'),
                        IPRARegistryData_PrognozResult_BehaviorControl  : row.get('IPRARegistryError_PrognozResult_BehaviorControl'),
                        IPRARegistryData_PrognozResult_Learning         : row.get('IPRARegistryError_PrognozResult_Learning'),
                        IPRARegistryData_PrognozResult_Work             : row.get('IPRARegistryError_PrognozResult_Work'),
                        IPRARegistryData_RepPerson_LastName              : row.get('IPRARegistryError_RepPerson_LastName'),
                        IPRARegistryData_RepPerson_FirstName             : row.get('IPRARegistryError_RepPerson_FirstName'),
                        IPRARegistryData_RepPerson_SecondName            : row.get('IPRARegistryError_RepPerson_SecondName'),
                        IPRARegistryData_RepPerson_SNILS                : row.get('IPRARegistryError_RepPerson_SNILS'),
                        IPRARegistryData_RepPerson_IdentifyDocType      : row.get('IPRARegistryError_RepPerson_IdentifyDocType'),
                        IPRARegistryData_RepPerson_IdentifyDocDep       : row.get('IPRARegistryError_RepPerson_IdentifyDocDep'),
                        IPRARegistryData_RepPerson_IdentifyDocSeries    : row.get('IPRARegistryError_RepPerson_IdentifyDocSeries'),
                        IPRARegistryData_RepPerson_IdentifyDocNum       : row.get('IPRARegistryError_RepPerson_IdentifyDocNum'),
                        IPRARegistryData_RepPerson_IdentifyDocDate      : row.get('IPRARegistryError_RepPerson_IdentifyDocDate'),
                        IPRARegistryData_RepPerson_AuthorityDocType     : row.get('IPRARegistryError_RepPerson_AuthorityDocType'),
                        IPRARegistryData_RepPerson_AuthorityDocDep      : row.get('IPRARegistryError_RepPerson_AuthorityDocDep'),
                        IPRARegistryData_RepPerson_AuthorityDocSeries   : row.get('IPRARegistryError_RepPerson_AuthorityDocSeries'),
                        IPRARegistryData_RepPerson_AuthorityDocNum      : row.get('IPRARegistryError_RepPerson_AuthorityDocNum'),
                        IPRARegistryData_RepPerson_AuthorityDocDate     : row.get('IPRARegistryError_RepPerson_AuthorityDocDate'),
                        IPRARegistryData_DisabilityCauseOther           : row.get('IPRARegistryError_DisabilityCauseOther'),
                        IPRARegistryData_PrognozResult_Independently    : row.get('IPRARegistryError_PrognozResult_Independently'),
                        IPRARegistry_Version                            : row.get('IPRARegistry_Version')
                    }
            );
            var main_form = Ext.getCmp('swIPRARegistryConfirmWindow')
            var ipra_version = row.get('IPRARegistry_Version');
            if(ipra_version < 2.0 || ipra_version === null){
                main_form.forms_setDisabled(true);                                                                                    
            } else {
                main_form.forms_setDisabled(false); 
            }
            base_form.findField('Person_SurName').allowBlank = false;
            
			//При открытии формы отображение содержимого филдсета мероприятий медицинской реабилитации или абилитации
			if (row.get('IPRARegistryError_DisabilityEndDate') == '9999-12-31'){
				Ext.getCmp('IPRARegistryData_DisabilityEndDate').hide();
				Ext.getCmp('checkbox_DisEndDate').setValue(true);
			}
            if (row.get('IPRARegistryError_MedRehab') == 2)
                Ext.getCmp('IPRARegistryData_MedRehabDate').show();
            else
                Ext.getCmp('IPRARegistryData_MedRehabDate').hide();

            if (row.get('IPRARegistryError_ReconstructSurg') == 2)
                Ext.getCmp('IPRARegistryData_ReconstructSurgDate').show();
            else
                Ext.getCmp('IPRARegistryData_ReconstructSurgDate').hide();

            if (row.get('IPRARegistryError_Orthotics') == 2)
                Ext.getCmp('IPRARegistryData_OrthoticsDate').show();
            else
                Ext.getCmp('IPRARegistryData_OrthoticsDate').hide();            

			if (getRegionNick() == 'perm' && !isUserGroup('IPRARegistryEdit')) {
				base_form.findField('IPRARegistry_DirectionLPU_id').disable();
				base_form.findField('IPRRegistry_Number').disable();
			}
    
            var FGUMCENumber = base_form.findField('IPRARegistry_FGUMCEnumber');
            //FGUMCENumber.getStore().load();
            FGUMCENumber.getStore().on(
                    'load', function () {
                        if(Ext.getCmp('formIPRA'))
                            FGUMCENumber.setValue(row.get('IPRARegistry_FGUMCEnumber'));
                    }
            );
    
            if(row.get('IPRARegistryError_DisabilityCause')==16){
                Ext.getCmp('textform_DisabilityCause').show();
            }

            Ext.getCmp('IPRARegistryData_DisabilityCause_id').setValue(row.get('IPRARegistryError_DisabilityCause'));
            
            if(row.get('IPRARegistry_DirectionLPU_id') != "" && row.get('IPRARegistry_DirectionLPU_id') != null)
                base_form.findField('IPRARegistry_DirectionLPU_id').getMOAddressOgrn();
        } 
        
        else 
        
        {
            if(!Ext.getCmp('choiceIPRA').isVisible()){
                this.setWidth(1100);
                Ext.getCmp('choiceIPRA').show();
            }
            Ext.getCmp('IRCW_MeasuresRehabButton').hide();
            var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Загрузка"});
            loadMask.show();
            //this.action = params.action;
            //console.log('this.action',this.action);
            Ext.getCmp('IRCW_MeasuresRehabButton').setVisible(getRegionNick() != 'ufa');
            
            var grid_IPRA = Ext.getCmp('IPRA_Number').getGrid();
            grid_IPRA.getStore().load({params: {Person_id: params.Person_id}});
            grid_IPRA.getStore().on(
                    'load', function () {
                        var rowIndex = this.find('IPRARegistry_id', params.IPRARegistry_id);  //where 'id': the id field of your model, record.getId() is the method automatically created by Extjs. You can replace 'id' with your unique field.. And 'this' is your store.
                        grid_IPRA.getSelectionModel().selectRow(rowIndex);
                        //loadMask.hide();
                    }
            );
            base_form.findField('Person_SurName').setValue(params.Person_SurName);
            base_form.findField('Person_FirName').setValue(params.Person_FirName);
            base_form.findField('Person_SecName').setValue(params.Person_SecName);
            base_form.findField('IPRARegistryData_BirthDate').setValue(params.Person_Birthday);
            base_form.findField('Person_Snils').setValue(params.Person_Snils);
            this.action = params.action;
            console.log('action1',params.action, this);
            
            base_form.findField('IPRARegistry_Confirm').setDisabled(this.Ministry_of_Health());
            Ext.getCmp('swIPRARegistryConfirmWindow').getIPRARegistry(params.IPRARegistry_id);
        }

        sw.Promed.swIPRARegistryConfirmWindow.superclass.show.apply(this, arguments);
    },
    
    getIPRARegistry: function (IPRARegistry_id){
                Ext.Ajax.request({
                url: '/?c=IPRARegister&m=getIPRARegistry',
                params: {
                    IPRARegistry_id: IPRARegistry_id
                },
                callback: function (options, success, response) {
                    if (success) {
                        var main_form = Ext.getCmp('swIPRARegistryConfirmWindow');
                        main_form.setDisabled(false);
                        var obj = Ext.util.JSON.decode(response.responseText);
                        var formobj = obj[0];
                        delete formobj.IPRARegistry_insDT;
                        delete formobj.IPRARegistry_updDT;
                        delete formobj.pmUser_insID;
                        delete formobj.pmUser_updID;
                        delete formobj.IPRARegistry_deleted;
                        delete formobj.IPRARegistryData_id;
                        delete formobj.IPRARegistryData_insDT;
                        delete formobj.IPRARegistryData_updDT;
                        delete formobj.IPRARegistryData_deleted;
                        formobj.IPRARegistry_Confirm = (formobj.IPRARegistry_Confirm == 2) ? true : false;
                        main_form.Confirm = formobj.IPRARegistry_Confirm;                                                                                        

                        //Ext.getCmp('IPRARegistry_FGUMCEnumber').getStore().load();
                        Ext.getCmp('IPRARegistry_FGUMCEnumber').getStore().on(
                                'load', function () {
                                    if(Ext.getCmp('formIPRA'))
                                        Ext.getCmp('IPRARegistry_FGUMCEnumber').setValue(formobj.IPRARegistry_FGUMCEnumber);
                                }
                        );
                
                        var base_form = Ext.getCmp('formIPRA').getForm();
                        base_form.setValues(formobj); //Заполнение полей формы

                        var dir_lpu = base_form.findField('IPRARegistry_DirectionLPU_id');
                        if(formobj.IPRARegistry_DirectionLPU_id != null){
                            dir_lpu.getMOAddressOgrn();
                            if(dir_lpu.getStore().indexOfId(formobj.IPRARegistry_DirectionLPU_id) == -1){
                                dir_lpu.getMONick();
                            }
                        }
                        else{
                            //base_form.findField('UAddress_Address').setValue(null);
                            base_form.findField('Lpu_OGRN').setValue(null);
                        }
                        
                        if(formobj.IPRARegistryData_DisabilityCause==16){
                            Ext.getCmp('textform_DisabilityCause').show();
                            //Ext.getCmp('IPRARegistryData_DisabilityCauseOther').allowBlank = false;
                        }else{
                            Ext.getCmp('textform_DisabilityCause').hide();
                        }                                                
                        
                        //При открытии формы отображение содержимого филдсета мероприятий медицинской реабилитации или абилитации

                        if (formobj.IPRARegistryData_MedRehab == 2)
                            Ext.getCmp('IPRARegistryData_MedRehabDate').show();
                        else
                            Ext.getCmp('IPRARegistryData_MedRehabDate').hide();

                        if (formobj.IPRARegistryData_ReconstructSurg == 2)
                            Ext.getCmp('IPRARegistryData_ReconstructSurgDate').show();
                        else
                            Ext.getCmp('IPRARegistryData_ReconstructSurgDate').hide();

                        if (formobj.IPRARegistryData_Orthotics == 2)
                            Ext.getCmp('IPRARegistryData_OrthoticsDate').show();
                        else
                            Ext.getCmp('IPRARegistryData_OrthoticsDate').hide();

                        if (formobj.IPRARegistryData_DisabilityEndDate == '9999-12-31'){
                            Ext.getCmp('IPRARegistryData_DisabilityEndDate').hide();
                            Ext.getCmp('checkbox_DisEndDate').setValue(true);
                        }

                        
                        if(formobj.IPRARegistry_Version < 2.0 || formobj.IPRARegistry_Version === null){
                            main_form.forms_setDisabled(true);                                                                                    
                        } else {
                            main_form.forms_setDisabled(false); 
                        }
                        
                        /*this.disabilityCause_store.load();
                        this.disabilityGroup_store.load();
                        this.rehabPotential_store.load();
                        this.rehabPrognoz_store.load();
                        this.prognoz_store.load();*/
                        //Залочивание всех полей формы, если она подтверждена или открыта для просмотра
                        //console.log('action',Ext.getCmp('swIPRARegistryConfirmWindow').action);
                        //console.log('Ministry_of_Health',Ext.getCmp('swIPRARegistryConfirmWindow').Ministry_of_Health());
                                                                                                        Ext.getCmp('close').setText('Отмена');
                                                                                                        //if (formobj.IPRARegistry_Confirm == 2 || Ext.getCmp('swIPRARegistryConfirmWindow').action == 'view') {
                                                                                                        if (Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_Confirm').getValue()
                                                                                                                || main_form.action == 'view') {
                                                                                                                main_form.enableEdit(false);
                                                                                                                Ext.getCmp('save').hide();
                                                                                                                Ext.getCmp('close').setText('Закрыть');
                                                                                                                Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_Confirm').setDisabled(true);
                                                                                                        }
                                                                                                        else {
                                                                                                                if (!main_form.Ministry_of_Health()) { //Если это не минздрав - Залочиваем все поля кроме поля подтверждения МО
                                                                                                                        main_form.enableEdit(false);
                                                                                                                        Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_Confirm').setDisabled(false); //активация поля подтверждения МО
                                                                                                                }
                                                                                                                else {//Если это минздрав - Разлочиваем все поля кроме поля подтверждения МО
                                                                                                                        main_form.enableEdit(true);
                                                                                                                        Ext.getCmp('formIPRA').getForm().findField('IPRARegistry_Confirm').setDisabled(true);
                                                                                                                }
                                                                                                                Ext.getCmp('save').show();
                                                                                                        }
                                                                                                        Ext.getCmp('IRCW_MeasuresRehabButton').setVisible(getRegionNick() != 'ufa');
                    } else {
                        sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера!');
                    }
                }
            }); 
    }
    
});