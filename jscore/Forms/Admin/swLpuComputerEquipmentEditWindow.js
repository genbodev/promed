/**
 * swLpuComputerEquipmentEditwnd - окно редактирования/добавления компьютерного оснащения.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Maksim Sysolin aka Smay (max.sysolin@gmail.com)
 * @version      23.02.2017
 */

sw.Promed.swLpuComputerEquipmentEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        id: 'swLpuComputerEquipmentEditWindow',
        action: null,
        callback: Ext.emptyFn,
        onHide: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        split: true,
        autoHeight: true,
        width: 670,
        layout: 'form',
        modal: true,
        plain: true,
        resizable: false,

        // дальше переменные только внутри этого окна

        //родительское окно и грид, чтобы не задавать внутри
        parentWindow: 'LpuPassportEditWindow',
        parentGrid: 'ComputerEquipmentGrid',
        // имя основной формы, чтобы не задавать внутри
        formName: 'LpuComputerEquipmentEditForm',
        // поле id главной сущности, чтобы не задавать внутри
        mainIdField: 'ComputerEquip_id',
        // флаг уникальности поля
        uniqFieldChanged: false,
        // контейнеры для дизаблинга\енаблинга
        ownerCts: [],

        listeners:
        {
            hide: function() {
                this.onHide();
            },
            'beforeshow': function(){

                this.loadComboByField('Period_id');
                this.loadComboByField('Device_pid');
            }
        },
        loadComboByField: function(field_name, field_value, load_params) {

            // функция подгрузки комбо-бокса

            var wnd = this,
                form = wnd.getMainForm(),
                combo_field = field_name,
                combo_value = field_value,
                combo = form.findField(combo_field);

            combo.getStore().load({

                params: load_params,
                callback: function () {

                    if (combo_value) {

                        var cmb = form.findField(combo_field);
                        cmb.setValue(combo_value);

                        if (wnd.action == 'edit')
                            cmb.setFieldVisibility(cmb);
                    }



                }
            });
        },
        setComboData: function(data) {

            // при редактировании записи и просмотре
            // подгрузка комбо-бокса для подкатегории

            var wnd = this,
                form = wnd.getMainForm(),
                period_id = wnd.getFieldValue('Period_id'),
                device_id = wnd.getFieldValue('Device_id'),
                parent_id = wnd.getFieldValue('Device_pid') ? wnd.getFieldValue('Device_pid') : device_id,

                loadParams = {
                    parent_id: parent_id
                };

            var period_combo = form.findField('Period_id'),
                dpid_combo = form.findField('Device_pid'),
                device_combo = form.findField('Device_id');

            period_combo.setValue(period_id);
            dpid_combo.setValue(parent_id);

            //выбираем во втором комбо дочернюю категорию
            //и вызываем метод в комбике
            this.loadComboByField('Device_id', device_id, loadParams);
        },
        getMainForm: function()
        {
            return this[this.formName].getForm();
        },
        getFieldValue: function(field_name)
        {
            var form = this.getMainForm();
            return form.findField(field_name).getValue();
        },
        doSave: function()
        {
            var wnd = this,
                form = wnd.getMainForm();

            if (!form.isValid()) {

                sw.swMsg.show({

                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT,
                    icon: Ext.Msg.WARNING,
                    buttons: Ext.Msg.OK,

                    fn: function() {
                        form.getFirstInvalidEl().focus(true);
                    }
                });

                return false;
            }

            // сохранение \ редактирование(уникальные поля изменены)
            if (wnd.action == 'add' || (wnd.action == 'edit' && wnd.uniqFieldChanged))

                //проверили устройство на уникальность записи
                wnd.checkUniqRecord().then(function(firstCheck) {

                    if (isDebug())
                        console.log('firstCheck: '+ firstCheck);

                    // если первая проверка пройдена, проходим вторую
                    if (firstCheck) {

                        //проверили устройство на количество использований
                        wnd.checkDeviceUsage().then(function(secondCheck) {

                            if (isDebug())
                                console.log('secondCheck:' + secondCheck);

                            // отправили на сохранение если все ок
                            (secondCheck) ? wnd.submit() : null;
                        })
                    }
                })

            // редактирование (уникальные поля БЕЗ изменений)
            if (wnd.action == 'edit' && !wnd.uniqFieldChanged) {

                //проверили устройство на количество использований
                wnd.checkDeviceUsage().then(function(checked) {

                    // отправили на сохранение если все ок
                    (checked) ? wnd.submit() : null;
                })
            }

            //ставим флаг что уникальные поля не изменены (пока..)
            wnd.uniqFieldChanged = false;
            return true;
        },
        getDeviceUsageTotal: function()
        {
            var wnd = this,
                parent_id = wnd.getFieldValue('Device_pid'),
                device_id = wnd.getFieldValue('Device_id'),
                is_child = (device_id != parent_id) ? true : false, // true | false

                ajax_input_params = {

                    Lpu_id: wnd.getFieldValue('Lpu_id'),
                    Device_id: is_child ? parent_id : device_id,
                    ComputerEquip_Year: wnd.getFieldValue('ComputerEquip_Year'),
                    Period_id: wnd.getFieldValue('Period_id')
                };

            // если дочерняя категория
            if (is_child) {

                // подготовка прамиса:
                return new Promise(function(resolve, reject) {

                    Ext.Ajax.request({

                        params: ajax_input_params,
                        url: '/?c=LpuPassport&m=checkLpuComputerEquipmentParentDeviceUsage',
                        success: function(response) {resolve({response: response.responseText, is_child: is_child})},
                        failure: function(response) {reject(response)}
                    })
                })
            //если это родительская категория
            } else {

                // подготовка прамиса:
                return new Promise(function(resolve, reject) {

                    Ext.Ajax.request({

                        params: ajax_input_params,
                        url: '/?c=LpuPassport&m=checkLpuComputerEquipmentChildDeviceUsage',
                        success: function(response) {resolve({response: response.responseText, is_child: is_child})},
                        failure: function(response) {reject(response)}
                    })
                })
            }
        },
        showMsgPromise: function(type, btns, messageTxt)
        {
            var title ='',
                msg = lang['vnimanie'] + '! ' + messageTxt,
                is_error = false;

            if (type == Ext.MessageBox.ERROR) {

                title = lang['oshibka'];
                is_error  = true;
            }

            if (type == Ext.MessageBox.WARNING)
                title = lang['preduprejdenie'];

            return new Promise(function(resolve, reject) {

                if (isDebug())
                    console.log(
                        'type: ' + type,
                        'btns: ' + btns,
                        'title: ' + title,
                        'msg: ' + msg,
                        'is_error: ' + is_error
                    );

                sw.swMsg.show({

                    icon: type,
                    buttons: btns,
                    title: title,
                    msg: msg,

                    fn: function (buttonId) {

                        if (is_error)
                            resolve(false);
                        else
                            resolve((buttonId == 'ok') ? true : false);
                    }

                });
            })
        },
        checkDeviceUsage: function()
        {
            var wnd = this,
                form = wnd.getMainForm(),
                total_col = 'ComputerEquip_Total',
                cols = [
                    'ComputerEquip_MedPAmb',
                    'ComputerEquip_MedPStac',
                    'ComputerEquip_AHDAmb',
                    'ComputerEquip_AHDStac',
                    'ComputerEquip_other'
                ],
                this_device = {};

            this_device[total_col] = 0;

            cols.forEach(function(column) {

                this_device[total_col] += Number(wnd.getFieldValue(column));
                this_device[column] = Number(wnd.getFieldValue(column))
            });

            // подготовка этого прамиса:
            return new Promise(function(resolve, reject) {

                // ждем результат другого прамиса:
                wnd.getDeviceUsageTotal().then(function(res) {

                    var responseText = res.response,
                        is_child = res.is_child,
                        obj = JSON.parse(responseText);

                    if (isDebug())
                        console.log('responseText: ' + responseText);

                    // если родительской записи нет или она в другом годе-периоде
                    if (obj.length == 0)
                        resolve(true)
                    else {
                        // иначе сравниваем общее количество оборудования
                        var query = {},
                            is_valid_cols = true,
                            ivalid_column = '';

                        query[total_col] = 0;

                        obj.forEach(function(query_row) {

                            cols.forEach(function(column) {

                                if (typeof query[column] == 'undefined')
                                    query[column] = 0;

                                query[column]+=Number(query_row[column]);
                            });

                            query[total_col] += Number(query_row[total_col])
                        });

                        if (isDebug())
                            console.log('this_device: ' + JSON.stringify(this_device) + ' query: ' + JSON.stringify(query) + ' is_child: ' + is_child);

                        if (is_child) {

                            var warn_msg = lang['dlja_dannoj_kategorii_ustrojstv_ne_mozhet_byt_zadano_bolshee_kolichestvo_edinic_ustrojstv_chem_dlja kategorii']
                                + ' ' + form.findField('Device_pid').lastSelectionText;

                            if (isDebug())
                                console.log(this_device[total_col] + '<=' + query[total_col]);

                            // если по общему количеству значение меньше родительской
                            if (this_device[total_col] <= query[total_col]) {

                                // проверяем каждую колонку отдельно
                                cols.forEach(function(column) {

                                    if (isDebug())
                                        console.log(column + '(this): ' + this_device[column]);

                                    if (isDebug())
                                        console.log(column + '(query): ' + query[column]);

                                    // если дочерняя больше ошибка, если меньше либо равны ОК
                                    if (this_device[column] > query[column]) {

                                        //break;
                                        if (!is_valid_cols)
                                            return false;

                                        is_valid_cols = false;
                                        ivalid_column = column;
                                    }
                                });

                                if (is_valid_cols)
                                    resolve(true);
                                else {
                                    wnd.showMsgPromise(Ext.MessageBox.WARNING, Ext.Msg.OKCANCEL, warn_msg).then(

                                        function(btnOK) {

                                            if (isDebug())
                                                console.log('ivalid_column: ' + ivalid_column);

                                            if (!btnOK)
                                                form.findField(ivalid_column).markInvalid(lang['eto_znachenie_bolshe_chem_v_osnovnoj_kategorii']);
                                            else
                                                resolve(true);
                                        }
                                    );
                                }
                            // иначе предупреждаем
                            } else
                                wnd.showMsgPromise(Ext.MessageBox.WARNING, Ext.Msg.OKCANCEL, warn_msg)
                                    .then(
                                        function(btnOk) {
                                            //резолвим этот прамис
                                            resolve(btnOk);
                                        }
                                    );

                        } else {

                            warn_msg = lang['v_dannoj_kategorii_ustrojstv_ne_mozhet_byt_zadano_menshee_kolichestvo_edinic_ustrojstv_chem_summarno_v_sootvetstvujushhih_podkategorijah'];

                            if (isDebug())
                                console.log(this_device[total_col] + '>=' + query[total_col]);

                            // если по общему количеству значение больше чем в родительских
                            if (this_device[total_col] >= query[total_col]) {

                                // проверяем каждую колонку отдельно
                                cols.forEach(function(column) {
                                    // если родительская меньше ошибка, если больше либо равна ОК
                                    if (this_device[column] < query[column]) {

                                        //break;
                                        if (!is_valid_cols)
                                            return false;

                                        is_valid_cols = false;
                                        ivalid_column = column;

                                    }
                                });

                                if (is_valid_cols)
                                    resolve(true);
                                else {
                                    wnd.showMsgPromise(Ext.MessageBox.ERROR, Ext.Msg.OK, warn_msg).then(

                                        function(res) {

                                            if (isDebug())
                                                console.log('ivalid_column: ' + ivalid_column);

                                            form.findField(ivalid_column).markInvalid(lang['eto_znachenie_menshe_summy_jetogo_polja_v_dochernih_ustrojstvah']);
                                        }
                                    );
                                }

                            } else
                                wnd.showMsgPromise(Ext.MessageBox.ERROR, Ext.Msg.OK, warn_msg)
                                    .then(
                                        resolve(false)
                                    );

                        }
                    }
                })
            })
        },
        deleteDuplicateRow: function(id)
        {
            var ajax_input_params = {};
            ajax_input_params[this.mainIdField] = id;

            // подготовим этот прамис:
            return new Promise(function(resolve, reject) {

                Ext.Ajax.request({

                    params: ajax_input_params,
                    url: '/?c=LpuPassport&m=deleteLpuComputerEquipment',
                    success: function(response) {resolve(response.responseText)},
                    failure: function(response) {reject(response)}
                })

            })

        },
        checkUniqRecord: function()
        {
            // функция проверки уникальности записи
            var wnd = this,
                form = wnd.getMainForm();

            // подготовим этот прамис:
            return new Promise(function(resolve, reject) {

                // ждем результат выполнения другого прамиса:
                wnd.getUniqRecord().then(function(responseText) {

                    var obj = JSON.parse(responseText);

                    // если мы получили пустой массив, значит такой записи нет
                    if (obj.length == 0)
                        // поэтому мы можем добавить новую запись
                        resolve(true);

                    else {
                        // в противном случае выводим предупреждение, что запись будет обновлена
                        var entity_id = 0;

                        // если поле ComputerEquip_id существует
                        if (typeof obj[0].ComputerEquip_id !== 'undefined') {

                            // если значение поля больше нуля
                            if (obj[0].ComputerEquip_id > 0) {

                                entity_id = obj[0].ComputerEquip_id;

                                sw.swMsg.show({

                                    icon: Ext.MessageBox.WARNING,
                                    buttons: Ext.Msg.OKCANCEL,
                                    title:  lang['preduprejdenie'],
                                    msg:    lang['znachenija_vybrannoj_kategorii_za_ukazannyj_period_byli_dobavleny_ranee']
                                    + '. '
                                    + lang['pri_sohranenii_ranee_sozdannaja_zapis_budet_otredaktirovana']
                                    ,

                                    fn: function (buttonId) {

                                        // если согласен с предупреждением, сохраняем
                                        if (buttonId == 'ok') {

                                            var main_id = form.findField('ComputerEquip_id');

                                            if (wnd.action == 'add') {

                                                main_id.setValue(entity_id);
                                                //резолвим прамис
                                                resolve(true);

                                            } else {

                                                var duplicate_id = main_id.getValue();

                                                // ждем результат выполнения еще одного прамиса:
                                                wnd.deleteDuplicateRow(duplicate_id).then(

                                                    function() {

                                                        main_id.setValue(entity_id),
                                                        resolve(true);
                                                    }
                                                );
                                            }
                                        } else
                                            resolve(false);
                                    }
                                });
                            } else
                                sw.swMsg.alert(lang['oshibka'], '');
                        }  else
                            sw.swMsg.alert(lang['oshibka'], '');
                    }
                })
            })
        },
        getUniqRecord: function()
        {
            // функция получения запроса на уникальность записи

            var wnd = this,
                ajax_input_params = {

                Lpu_id: wnd.getFieldValue('Lpu_id'),
                Device_id: wnd.getFieldValue('Device_id'),
                ComputerEquip_Year: wnd.getFieldValue('ComputerEquip_Year'),
                Period_id: wnd.getFieldValue('Period_id')
            };

            return new Promise(function(resolve, reject) {

                Ext.Ajax.request({

                    params: ajax_input_params,
                    url: '/?c=LpuPassport&m=checkLpuComputerEquipmentUniqRecord',
                    success: function(response) {resolve(response.responseText)},
                    failure: function(response) {reject(response)}
                })
            })
        },
        submit: function()
        {
            var wnd = this,
                form = wnd.getMainForm(),

                loadMask = new Ext.LoadMask(

                    wnd.getEl(), {
                        msg: lang['podojdite_idet_sohranenie']
                    }
                );

            loadMask.show();
            form.submit({

                params: {
                    action: wnd.action,
                },
                failure: function(result_form, action) {

                    loadMask.hide();

                    if (action.result)
                        if (action.result.Error_Code)

                            Ext.Msg.alert(

                                lang['oshibka_#'] + action.result.Error_Code,
                                action.result.Error_Message
                            );
                },
                success: function(result_form, action) {

                    loadMask.hide();

                    if (action.result) {

                        if (action.result[wnd.mainIdField]) {

                            // обновляем диапазон годов для фильтра
                            var parentWndYearComboName = 'ComputerEquip_YearCombo',
                                parentWndYearCombo =  Ext.getCmp(wnd.parentWindow).findById(parentWndYearComboName);

                            // обновляем диапазон годов для фильтра
                            parentWndYearCombo.getStore().load(
                                {
                                    callback: function(store)
                                    {
                                        parentWndYearCombo.setValue(store.shift().data.ComputerEquip_Year);
                                    }
                                });


                            wnd.hide();

                            //перегружаем грид
                            Ext.getCmp(wnd.parentWindow).findById(wnd.parentGrid).loadData();

                    } else {

                            sw.swMsg.show({

                                title: lang['oshibka'],
                                msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
                                icon: Ext.Msg.ERROR,
                                buttons: Ext.Msg.OK,

                                fn: function() {
                                    wnd.hide();
                                }
                            });
                        }
                    }
                }
            });

        },
        enableEdit: function(enable)
        {
            var form = this.getMainForm();

            form.items.each(function(field){

                field.setDisabled(!enable);
            });

            this.buttons[0].setDisabled(!enable);
        },
        toggleFieldVisibility: function(fields_array, params) {

            var wnd = this,
                form = wnd.getMainForm(),
                field_names = fields_array,
                element = params.fromElement,
                code_field = params.codeField,

                toggle_codes = params.visibleCodes
                    ? params.visibleCodes
                    : params.invisibleCodes
                        ? params.invisibleCodes
                        : 0,

                store_id_field = params.storeIdField
                    ? params.storeIdField
                    : element.getName();

            var elem_store = element.getStore(),
                store_index = elem_store.find(store_id_field, element.getValue()),
                code_value = elem_store.getAt(Number(store_index)).get(code_field);

            // функция indexOf не будет работать в браузерах ниже IE9
            // если версия браузера < IE9 то необходимо сделать полифилл...
            var in_code_list = (toggle_codes.indexOf(code_value) > -1);

            var visibility = params.visibleCodes ? true : false,
                enabled = visibility ? in_code_list : !in_code_list,
                already_toggled_ct = [];

            if (isDebug())
                console.log(
                    'Имя поля: ' + store_id_field +
                    ' Значение ID: ' + element.getValue() +
                    ' Индекс в Store: ' + store_index +
                    ' Код устройства: ' + code_value +
                    ' Список кодов: ' + toggle_codes +
                    ' Поля: ' + field_names,
                    ' Есть в списке? ' + in_code_list,
                    ' Видим если есть ' + visibility
                );

            field_names.forEach(function(field_name) {

                if (form.findField(field_name)) {

                    var toggle_field = form.findField(field_name),
                        container = toggle_field.ownerCt,
                        cont_id = container.getId(),
                        ct_is_toggled = (already_toggled_ct.indexOf(cont_id) > -1);

                    //для поля\полей должен быть определен контейнер который мы будем дизаблить
                    //без этого конечно дизаблится, но стили остаются прежними :(
                    if (!ct_is_toggled) {

                        container.setDisabled(!enabled);
                        already_toggled_ct.push(cont_id);
                    }

                    toggle_field.setDisabled(!enabled);
                }
            });

            already_toggled_ct.forEach(function(ct_id) {

                var ct_exist = (wnd.ownerCts.indexOf(ct_id) > -1);

                    // чтобы потом легко найти дизабленные контейнеры
                    if (!ct_exist)
                        wnd.ownerCts.push(ct_id);
            });
        },
        loadForm : function(loadMask)
        {
            var wnd = this,
                form = wnd.getMainForm(),
                main_id_field = wnd.mainIdField,

                input_params = {
                    Lpu_id: wnd.Lpu_id
                };

            input_params[main_id_field]= wnd[main_id_field];

            form.load(
                {
                    params: input_params,
                    failure: function()
                    {
                        loadMask.hide();

                        sw.swMsg.show({

                            buttons: Ext.Msg.OK,
                            icon: Ext.Msg.ERROR,
                            title: lang['oshibka'],
                            msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],

                            fn: function() { wnd.hide(); }
                        });
                    },
                    success: function()
                    {
                        loadMask.hide();
                        wnd.setComboData();

                        wnd.findById('LPEW_Lpu_id').setValue(wnd.Lpu_id);

                        form.items.each(function(f) {

                            // сохраняем предыдущие значения
                            f.previous = (f.getValue() != null) ? f.getValue() : '';
                        });
                    },
                    url: '/?c=LpuPassport&m=loadLpuComputerEquipment'
                });
        },
        show: function()
        {
            sw.Promed.swLpuComputerEquipmentEditWindow.superclass.show.apply(this, arguments);

            var wnd = this,
                form = wnd.getMainForm(),
                args = arguments[0],
                main_id_field = wnd.mainIdField,

                loadMask = new Ext.LoadMask(

                    wnd.getEl(),{
                        msg: LOAD_WAIT
                    }
                );

            if (!args){

                sw.swMsg.show({

                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.ERROR,
                    title: lang['oshibka'],
                    msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],

                    fn: function() { wnd.hide(); }
                });
            }

            loadMask.show();

            //ставим флаг что уникальные поля не изменены (пока..)
            wnd.uniqFieldChanged = false;
            wnd.focus();

            wnd.callback = Ext.emptyFn;
            wnd.onHide = Ext.emptyFn;

            form.reset();
            form.setValues(args);

            // оптимизировал присвоение параметров arguments[0]
            for (var field_name in args)
                wnd[field_name] = args[field_name];

            if (!wnd.action) {

                if ((wnd[main_id_field]) && (wnd[main_id_field] > 0))
                    wnd.action = "edit";
                else
                    wnd.action = "add";
            }

            switch (wnd.action)  {

                case 'add':

                    wnd.setTitle(
                        lang['osnashhennost_kompjuternym_oborudovaniem']
                        + ': '
                        + lang['dobavlenie']
                    );

                    wnd.enableEdit(true);
                    form.clearInvalid();

                    // автозаполнение года
                    form.findField('ComputerEquip_Year').setValue(new Date().getFullYear());

                    // очищаем хранилище устройства
                    form.findField('Device_id').getStore().removeAll();

                    //раздизабливаем контейнеры
                    wnd.ownerCts.forEach(function(ct) {
                        wnd.findById(ct).enable();
                    });

                    //дизаблим кабинеты мед.статистики как по ТЗ
                    form.findField('ComputerEquip_MedStatCab').ownerCt.setDisabled(true);
                    form.findField('ComputerEquip_MedStatCab').setDisabled(true);

                    loadMask.hide();
                    break;

                case 'edit':

                    wnd.setTitle(
                        lang['osnashhennost_kompjuternym_oborudovaniem']
                        + ': '
                        + lang['redaktirovanie']
                    );

                    wnd.enableEdit(true);
                    wnd.loadForm(loadMask);
                    break;

                case 'view':

                    wnd.setTitle(
                        lang['osnashhennost_kompjuternym_oborudovaniem']
                        + ': '
                        + lang['prosmotr']
                    );

                    wnd.enableEdit(false);
                    wnd.loadForm(loadMask);

                    break;
            }
        },
        initComponent: function()
        {
            var wnd = this,
                formName = wnd.formName;

            //главная форма
            wnd[formName] = new Ext.form.FormPanel(
            {
                id: formName,
                frame: true,
                border: false,
                autoHeight: true,
                bodyStyle: 'padding: 5px',
                labelAlign: 'right',
                labelWidth: 180,
                items: [
                {
                    id: wnd.mainIdField,
                    name: wnd.mainIdField,
                    value: 0,
                    xtype: 'hidden'
                },{
                    id: 'LPEW_Lpu_id',
                    name: 'Lpu_id',
                    value: 0,
                    xtype: 'hidden'
                },{
                    autoLoad: false,
                    width: 100,
                    xtype:  'swyearscombo',
                    triggerAction: 'all',
                    id: 'ComputerEquip_Year',
                    //tabIndex: TABINDEX_LPUCMPEQ + 1,
                    listeners: {
                        'change': function (combo) {

                            // если поле изменено
                            if (combo.getValue != combo.previous)
                                wnd.uniqFieldChanged = true;
                        }
                    },
                },{
                    fieldLabel: lang['period'],
                    width: 100,
                    comboSubject: 'Period',
                    hiddenName: 'Period_id',
                    //tabIndex: TABINDEX_LPUCMPEQ + 2,
                    xtype: 'swcommonsprcombo',
                    listeners: {
                        'change': function (combo) {

                            // если поле изменено
                            if (combo.previous != combo.getValue())
                                wnd.uniqFieldChanged = true;
                        }
                    }
                },{
                    fieldLabel: lang['kategoriya'],
                    hiddenName: 'Device_pid',
                    //tabIndex: TABINDEX_LPUCMPEQ + 3,
                    allowBlank: false,
                    width: 450,
                    xtype: 'combo',
                    store:
                        new sw.Promed.Store({
                            autoLoad: false,
                            url: '/?c=LpuPassport&m=loadLpuComputerEquipmentDevicesCat',
                            fields: [,
                                {name: 'Device_id', type: 'int'},
                                {name: 'Device_Name', type: 'string'},
                                {name:'Device_Code', type:'string'},
                            ],
                            key: 'DeviceCat_id',
                        }),
                    valueField: 'Device_id',
                    displayField: 'Device_Name',
                    triggerAction: 'all',
                    editable: false,
                    tpl:
                    '<tpl for="."><div class="x-combo-list-item">'+
                    '<font color="red">{Device_Code}</font>&nbsp;{Device_Name}'+
                    '</div></tpl>',
                    listeners: {
                        'change': function(combo) {

                            //combo.fireEvent('select', combo);
                        },
                        'select': function(combo) {

                            var selected_value = combo.getValue();

                            wnd.getMainForm().findField('Device_id').onChangeDeviceCatField(selected_value);

                            if (selected_value)
                                combo.setFieldVisibility(combo);
                        }
                    },
                    setFieldVisibility: function(combo) {

                        wnd.toggleFieldVisibility(

                            [
                                'ComputerEquip_MedStatCab'
                            ],
                            {
                                storeIdField: 'Device_id',
                                codeField: 'Device_Code',
                                fromElement: combo,

                                visibleCodes: [

                                    11 // Высокоскоростные каналы передачи данных
                                ]
                            }
                        );

                        wnd.toggleFieldVisibility(

                            [
                                'ComputerEquip_AHDAmb',
                                'ComputerEquip_AHDStac',
                                'ComputerEquip_MedPAmb',
                                'ComputerEquip_MedPStac'
                            ],
                            {
                                storeIdField: 'Device_id',
                                codeField: 'Device_Code',
                                fromElement: combo,

                                invisibleCodes: [

                                    11 // Высокоскоростные каналы передачи данных
                                ]
                            }
                        );
                    }
                },{
                    fieldLabel: lang['oborudovanie'],
                    hiddenName: 'Device_id',
                    //tabIndex: TABINDEX_LPUCMPEQ + 4,
                    allowBlank: false,
                    width: 450,
                    xtype: 'combo',
                    store:
                        new sw.Promed.Store({
                            autoLoad: false,
                            url: '/?c=LpuPassport&m=loadLpuComputerEquipmentDevices',
                            fields: [,
                                {name: 'Device_id', type: 'int'},
                                {name: 'Device_Name', type: 'string'},
                                {name:'Device_Code', type:'string'}
                            ],
                            key: 'Device_id'
                        }),
                    valueField: 'Device_id',
                    displayField: 'Device_Name',
                    triggerAction: 'all',
                    editable: false,
                    tpl:
                    '<tpl for="."><div class="x-combo-list-item">'+
                    '<font color="red">{Device_Code}</font>&nbsp;{Device_Name}'+
                    '</div></tpl>',
                    listeners: {
                        'change': function (combo) {

                            // если поле изменено
                            if (combo.getValue != combo.previous)
                                wnd.uniqFieldChanged = true;

                            //combo.fireEvent('select', combo);
                        },
                        'select': function(combo) {

                            combo.setFieldVisibility(combo);
                        }
                    },
                    setFieldVisibility: function(combo) {

                        wnd.toggleFieldVisibility(

                            ['ComputerEquip_MedStatCab'],
                            {
                                codeField: 'Device_Code',
                                fromElement: combo,

                                // коды при которых не дизаблится "ComputerEquip_MedStatCab"
                                visibleCodes: [

                                    // Количество точек подключения
                                    // к ведомственной корпоративной сети
                                    // связи по типам подключения

                                    '8.1', // коммутируемый (модемный)
                                    '8.2', // широкополосный доступ по технологии xDSL
                                    '8.6', // VPN через сеть общего пользования

                                    // Количество точек подключения
                                    // к сети Интернет по типам подключения

                                    '9.1', // коммутируемый (модемный)
                                    '9.2', // широкополосный доступ по технологии xDSL
                                    '9.6', // VPN через сеть общего пользования
                                    '11'
                                ]
                            }
                        );

                        wnd.toggleFieldVisibility(

                            [
                                'ComputerEquip_AHDAmb',
                                'ComputerEquip_AHDStac',
                                'ComputerEquip_MedPAmb',
                                'ComputerEquip_MedPStac'
                            ],
                            {
                                codeField: 'Device_Code',
                                fromElement: combo,

                                // коды при которых не дизаблится "ComputerEquip_MedStatCab"
                                invisibleCodes: [

                                    11 // Высокоскоростные каналы передачи данных
                                ]
                            }
                        );
                    },
                    onChangeDeviceCatField: function(parent_selected_value) {

                        var	child_combo = this;
                        //child_combo.allowBlank = (parent_selected_value) ? false : true;
                        child_combo.getStore().removeAll();

                        if (parent_selected_value) {

                            child_combo.getStore().baseParams.parent_id = parent_selected_value || null;
                            child_combo.getStore().load({

                                callback: function () {

                                    if (child_combo.getStore().getById(parent_selected_value))
                                        child_combo.setValue(parent_selected_value);
                                    else
                                        child_combo.clearValue();
                                }
                            });
                        } else
                            child_combo.clearValue();
                    }
                },{
                                    autoHeight: true,
                                    title: lang['dlya_nuzhd_ahd'],
                                    bodyStyle:'padding: 10;',
                                    xtype: 'fieldset',
                                    collapsible: false,
                                    labelWidth: 170,
                                    items:[
                                        new Ext.Container({
                                            autoEl: {},
                                            items: [{
                                                xtype: 'container',
                                                layout: 'form',
                                                autoEl: {},
                                                items: [{
                                                    fieldLabel: lang['v_ambulatornyh_uslovijah'],
                                                    xtype: 'textfield',
                                                    maskRe:/[0-9]/,
                                                    autoCreate: {tag: "input", maxLength: "12", autocomplete: "off"},
                                                    name: 'ComputerEquip_AHDAmb',
                                                    //tabIndex: TABINDEX_LPUCMPEQ + 5,
                                                    width: 100
                                                }, {
                                                    fieldLabel: lang['v_stacionarnyh_uslovijah'],
                                                    xtype: 'textfield',
                                                    maskRe:/[0-9]/,
                                                    autoCreate: {tag: "input", maxLength: "12", autocomplete: "off"},
                                                    name: 'ComputerEquip_AHDStac',
                                                    //tabIndex: TABINDEX_LPUCMPEQ + 6,
                                                    width: 100
                                            }]
                                            }]
                                        })


                                    ]},
                                    {
                                        autoHeight: true,
                                        title: lang['dlja_medicinskogo_personala'],
                                        bodyStyle:'padding: 10;',
                                        xtype: 'fieldset',
                                        collapsible: false,
                                        labelWidth: 170,
                                        items:[
                                            new Ext.Container({
                                            autoEl: {},
                                            items: [{
                                                xtype: 'container',
                                                layout: 'form',
                                                autoEl: {},
                                                items: [{
                                                    fieldLabel: lang['v_ambulatornyh_uslovijah'],
                                                    xtype: 'textfield',
                                                    maskRe:/[0-9]/,
                                                    autoCreate: {tag: "input", maxLength: "12", autocomplete: "off"},
                                                    name: 'ComputerEquip_MedPAmb',
                                                    //tabIndex: TABINDEX_LPUCMPEQ + 7,
                                                    width: 100
                                                },
                                                {
                                                    fieldLabel: lang['v_stacionarnyh_uslovijah'],
                                                    xtype: 'textfield',
                                                    maskRe:/[0-9]/,
                                                    autoCreate: {tag: "input", maxLength: "12", autocomplete: "off"},
                                                    name: 'ComputerEquip_MedPStac',
                                                    //tabIndex: TABINDEX_LPUCMPEQ + 8,
                                                    width: 100
                                                }]
                                            }]
                                        })


                                        ]
                                    },
                        {
                            fieldLabel: lang['prochie'],
                            xtype: 'textfield',
                            maskRe:/[0-9]/,
                            autoCreate: {tag: "input", maxLength: "12", autocomplete: "off"},
                            name: 'ComputerEquip_other',
                            //tabIndex: TABINDEX_LPUCMPEQ + 9,
                            width: 100
                        },
                            new Ext.Container({
                            autoEl: {},
                            items: [{
                                xtype: 'container',
                                layout: 'form',
                                id: 'ComputerEquip_MedStatCabCont',
                                autoEl: {},
                                items: {
                                    fieldLabel: lang['kabinety_medicinskoj_statistiki'],
                                    style: 'margin-top: 7px',
                                    xtype: 'textfield',
                                    maskRe:/[0-9]/,
                                    autoCreate: {tag: "input", maxLength: "12", autocomplete: "off"},
                                    name: 'ComputerEquip_MedStatCab',
                                    //tabIndex: TABINDEX_LPUCMPEQ + 10,
                                    width: 100
                                    }
                                }]
                            })
                    ],
                reader: new Ext.data.JsonReader(
                    {
                        success: function()
                        {
                            //
                        }
                    },
                    [
                        {name: wnd.mainIdField},
                        {name: 'Lpu_id'},
                        {name: 'Device_id'},
                        {name: 'Device_pid'},
                        {name: 'Period_id'},
                        {name: 'ComputerEquip_Year'},
                        {name: 'ComputerEquip_MedPAmb'},
                        {name: 'ComputerEquip_MedPStac'},
                        {name: 'ComputerEquip_AHDAmb'},
                        {name: 'ComputerEquip_AHDStac'},
                        {name: 'ComputerEquip_MedStatCab'},
                        {name: 'ComputerEquip_other'}
                    ]),
                url: '/?c=LpuPassport&m=saveLpuComputerEquipment'
            });
            Ext.apply(this,
                {

                    buttons:
                        [{
                            handler: function()
                            {
                                this.ownerCt.doSave();
                            },
                            iconCls: 'save16',
                            text: BTN_FRMSAVE
                            //tabIndex: TABINDEX_LPUCMPEQ + 11,
                        },
                            {
                                text: '-'
                            },
                            HelpButton(this),
                            {
                                handler: function()
                                {
                                    this.ownerCt.hide();
                                },
                                iconCls: 'cancel16',
                                text: BTN_FRMCANCEL,
                                //tabIndex: TABINDEX_LPUCMPEQ + 12,
                                listeners: {
                                    'focus': function (btn) {

                                        // если поле изменено
                                        if (combo.getValue != combo.previous)
                                            wnd.uniqFieldChanged = true;

                                        //combo.fireEvent('select', combo);
                                    }
                                }
                            }],
                    items: [this[this.formName]]
                });
            sw.Promed.swLpuComputerEquipmentEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });