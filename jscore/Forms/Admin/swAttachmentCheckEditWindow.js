/**
 * swAttachmentCheckEditWindow - окно редактирования параметров проверки прикрепления
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Max Sysolin (max.sysolin@gmail.com)
 * @version			20.04.2017
 */

sw.Promed.swAttachmentCheckEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'swAttachmentCheckEditWindow',
    action: null,
    callback: Ext.emptyFn,
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

    // сама сущность
    entity: 'AttachmentCheck',
    // имя основной формы, чтобы не задавать внутри
    formName: 'AttachmentCheckEditForm',
    // поле id главной сущности, чтобы не задавать внутри
    mainIdField: 'AttachmentCheck_id',
    // флаг, если хранилище профилей загружено первый раз
    profileStoreInit: true,
    // флаг, если хранилище специальностей загружено первый раз
    specStoreInit: true,
    // флаг, определяющий загрузили данные в форму через функцию loadData или нет
    isLoadData: false,

    getMainForm: function()
    {
        return this[this.formName].getForm();
    },

    setDefaultDateRange: function () {
        var wnd = this,
            dateNow = new Date(),
            datePlusYear = new Date(),
            minDate = Ext.util.Format.date(dateNow, 'd.m.Y'),
            maxDate = '',
            dateRangeField = wnd.getMainForm().findField('activityRange');

        datePlusYear.setFullYear(datePlusYear.getFullYear() + 1);
        maxDate = Ext.util.Format.date(datePlusYear, 'd.m.Y');

        dateRangeField.setValue(minDate + ' - ' + maxDate);
    },

    doSave: function() {

        var wnd = this,
            form = wnd.getMainForm(),
            loadMask = new Ext.LoadMask(

            wnd.getEl(), {
                msg: lang['podojdite_idet_sohranenie']
            }
        );

        if (!form.isValid()) {

            sw.swMsg.show({

                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT,
                icon: Ext.Msg.WARNING,
                buttons: Ext.Msg.OK,

                fn: function() {
                    wnd[wnd.formName].getFirstInvalidEl().focus(true);
                }
            });

            return false;
        }

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

                if ( action.result ) {
                    wnd.callback();
                    wnd.hide();
                } else
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki']);
            }
        });
    },

    loadForm : function(loadMask)
    {
        var wnd = this,
            form = wnd.getMainForm(),
            main_id_field = wnd.mainIdField,
            input_params = {};

        input_params[main_id_field]= wnd[main_id_field];

        form.load(
            {
                params: input_params,
                failure: function() {

                    loadMask.hide();
                    sw.swMsg.show({

                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.ERROR,
                        title: lang['oshibka'],
                        msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],

                        fn: function() { wnd.hide(); }
                    });
                },
                success: function(elem, resp) {

                    wnd.isLoadData = true;

                    if (resp.result.data) {

                        var radioGroup = form.findField('AttachementCheckOn').items;

                        if (resp.result.data.LpuSectionProfile_id) {

                            // включаем радиокнопку "Профиль"
                            radioGroup.each(function(radioBtn){

                                if (radioBtn.inputValue == 'profile')
                                    radioBtn.setValue(true);
                            });

                        } else {

                            if (resp.result.data.MedSpecOms_id) {

                                // включаем радиокнопку "Специальность"
                                radioGroup.each(function(radioBtn){

                                    if (radioBtn.inputValue == 'spec')
                                        radioBtn.setValue(true);
                                });

                            } else {

                                // включаем радиокнопку "Не указано"
                                radioGroup.each(function(radioBtn){


                                    if (radioBtn.inputValue == 'default')
                                        radioBtn.setValue(true);
                                });
                            }
                        }

                    }

                    loadMask.hide();
                },
                url: '/?c=AttachmentCheck&m=getAttachmentCheckRecord'
            });
    },

    onAttachTypeStoreLoaded: function(evt_object) {

        var excludedTypes = [1,4,5], //эти типы прикрепления убираем
            combo_store = evt_object;

        excludedTypes.forEach(function (code) {
           combo_store.removeAt(combo_store.find('LpuAttachType_Code', code));
        });
    },

    toggleProfileSpecVisibility: function(activeType) {

        var wnd = this,
            form = wnd.getMainForm(),
            profileField = form.findField('LpuSectionProfile_id'),
            specField = form.findField('MedSpecOms_id'),
            lpuField = form.findField('ACEW_Lpu_id');

        switch (activeType) {

            case 'profile':

                if (wnd.action != 'view') {

                    form.clearInvalid();

                    profileField.setDisabled(false);
                    specField.setDisabled(true);
                    lpuField.allowBlank = true;
                }

                profileField.showContainer();
                specField.hideContainer();

                break;

            case 'spec':

                if (wnd.action != 'view') {

                    form.clearInvalid();

                    specField.setDisabled(false);
                    profileField.setDisabled(true);
                    lpuField.allowBlank = true;
                }


                specField.showContainer();
                profileField.hideContainer();

                break;

            case 'default':

                if (wnd.action != 'view') {

                    specField.setDisabled(true);
                    profileField.setDisabled(true);
                    lpuField.allowBlank = false;
                }

                specField.hideContainer();
                profileField.hideContainer();

                break;
        }
    },

    show: function() {

        sw.Promed.swAttachmentCheckEditWindow.superclass.show.apply(this, arguments);

        var wnd = this,
            form = wnd.getMainForm(),
            main_id_field = wnd.mainIdField,

            loadMask = new Ext.LoadMask(

                wnd.getEl(),{
                    msg: LOAD_WAIT
                }
            );

        wnd.profileStoreInit = true;
        wnd.specStoreInit = true;
        wnd.isLoadData = false;

        if (!arguments[0]){

            sw.swMsg.show({

                buttons: Ext.Msg.OK,
                icon: Ext.Msg.ERROR,
                title: lang['oshibka'],
                msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],

                fn: function() { wnd.hide(); }
            });
        }

        // обновляем грид после сохранения
        if (arguments[0].callback)
            wnd.callback = arguments[0].callback;

        var args = arguments[0].formParams;
        wnd.action = arguments[0].action ? arguments[0].action : null;

        loadMask.show();
        wnd.focus();

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

        loadMask.show();

        var attachTypeCombo = form.findField('LpuAttachType_id');
        attachTypeCombo.getStore().load();

        var profilesCombo = form.findField('LpuSectionProfile_id'),
            specsCombo = form.findField('MedSpecOms_id');

        switch (wnd.action) {

            case 'add':

                // чистим сторы
                profilesCombo.store.removeAll();
                specsCombo.store.removeAll();

                // включаем радиокнопку "Не указано" по умолчанию
                var radioBtn = form.findField('AttachementCheckOn').items.items[0];
                radioBtn.setValue(true);

                // ставим дату по умолчанию
                wnd.setDefaultDateRange();

                wnd.setTitle(
                    lang['proverka_prikreplenija']
                    + ': '
                    + lang['dobavlenie']
                );

                wnd.enableEdit(true);

                form.clearInvalid();
                loadMask.hide();
                break;

            case 'edit':

                wnd.setTitle(
                    lang['proverka_prikreplenija']
                    + ': '
                    + lang['redaktirovanie']
                );

                profilesCombo.store.load();
                specsCombo.store.load();

                wnd.enableEdit(true);
                wnd.loadForm(loadMask);

                break;

            case 'view':

                wnd.setTitle(
                    lang['proverka_prikreplenija']
                    + ': '
                    + lang['prosmotr']
                );

                profilesCombo.store.load();
                specsCombo.store.load();

                wnd.enableEdit(false);
                wnd.loadForm(loadMask);

                break;

            default:
                wnd.hide();
                break;
        }
    },

    sendAjaxRequestPromise: function(ajax_params, url)
    {
        return new Promise(function(resolve, reject) {

            Ext.Ajax.request({

                params: ajax_params,
                url: url,
                success: function(response) {resolve({response: response.responseText})},
                failure: function(response) {reject(response)}
            })
        })
    },

    filterProfilesRequest: function(params)
    {
        var url = '/?c=AttachmentCheck&m=getLpuSectionProfiles';
        this.filterRequest(url, params);
    },

    filterMedSpecsRequest: function(params)
    {
        var url = '/?c=AttachmentCheck&m=getMedSpecs';
        this.filterRequest(url, params);
    },

    filterRequest: function(url, input_params)
    {
        var wnd = this,
            params = {
                LpuAttachType_id: input_params.AttachType_id
            };

        if (input_params.Lpu_id)
            params.lpu_id_filter = input_params.Lpu_id;

        wnd.sendAjaxRequestPromise(params, url).then(function(res) {
            //log('data: ' + JSON.stringify(res.response));
            wnd.filterCombo(JSON.parse(res.response));
        })
    },

    getCheckOnValue: function(){

        var radioGroup = this.getMainForm().findField('AttachementCheckOn').items,
            ret = 'default';

        radioGroup.each(function(radioBtn){

            if (radioBtn.getValue() == true)
                ret = radioBtn.inputValue;
        });

        return ret;
    },

    filterCombo: function(list)
    {
        var wnd = this,
            checkOn = wnd.getCheckOnValue(),
            targetCombo = '';

        var i = 0;

        if (checkOn == 'profile')
            targetCombo = wnd.getMainForm().findField('LpuSectionProfile_id');
        else if (checkOn == 'spec')
            targetCombo = wnd.getMainForm().findField('MedSpecOms_id');

        if (targetCombo != '') {

            log('targetCombo: ' + checkOn);

            targetCombo.store.removeAll();
            targetCombo.clearValue();

            targetCombo.getStore().load({
                callback: function (data) {

                    log('combo.store before filter: ' + targetCombo.store.getCount());

                    targetCombo.store.filterBy(function (record, store_id) {

                        var ret = false;

                        list.forEach(function (item_id) {

                            if (item_id == store_id) {

                                i++;
                                //результат фильтра
                                ret = true;

                                //выходим из форича, если нашли
                                return false;
                            }
                        });

                        //фильтруем
                        return ret;
                    });

                    log('-------------------');
                    log('total equals: ' + i);
                    log('id_list: ' + list.length);
                    log('combo.store after filter: ' + targetCombo.store.getCount());
                }
            });
        } else {

            log('Not filtered. Radio button "default" is checked.');
        }
    },

    initComponent: function() {

        var wnd = this,
            formName = wnd.formName;

        wnd[formName] = new Ext.form.FormPanel(
            {

            bodyStyle: '{padding-top: 0.5em;}',
            border: false,
            frame: true,
            labelAlign: 'right',
            labelWidth: 200,
            layout: 'form',
            id: formName,
            url: '/?c=AttachmentCheck&m=saveAttachmentCheckRecord',
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                { name: 'AttachmentCheck_id' },
                { name: 'LpuAttachType_id' },
                { name: 'ACEW_Lpu_id' },
                { name: 'LpuSectionProfile_id' },
                { name: 'MedSpecOms_id' },
                { name: 'AttachmentCheck_CheckOn' },
                { name: 'AttachmentCheck_Period' },
            ]),
            items: [
                {
                    name: 'AttachmentCheck_id',
                    xtype: 'hidden'
                }, {
                    allowBlank: false,
                    name: 'LpuAttachType_id',
                    id: 'ComboLpuAttachType_id',
                    xtype: 'swlpuattachtypecombo',
                    width: 350,
                    autoload: false,
                    listeners: {
                        'render': function (combo) {
                            combo.store.addListener('load', this.onAttachTypeStoreLoaded, this);
                        }.createDelegate(this),
                        'select': function (combo) {

                            var attachType_id = combo.getValue(),
                                lpu_id = wnd.getMainForm().findField('ACEW_Lpu_id').getValue();

                            if (attachType_id) {

                                var params = {
                                        AttachType_id : attachType_id,
                                        Lpu_id: lpu_id
                                    },
                                    checkOn = wnd.getCheckOnValue();

                                log('checkOn: ' + checkOn);

                                if (checkOn == 'profile')
                                    wnd.filterProfilesRequest(params);
                                else if (checkOn == 'spec')
                                    wnd.filterMedSpecsRequest(params);
                            }
                        }
                    }
                }, {
                    allowBlank: true,
                    name: 'ACEW_Lpu_id',
                    hiddenName: 'ACEW_Lpu_id',
                    xtype: 'swlpulocalcombo',
                    width: 350,
                    listeners: {
                        'select': function (combo) {

                            var attachCombo = wnd.getMainForm().findField('LpuAttachType_id');
                            attachCombo.fireEvent('select', attachCombo);
                        }
                    }
                },
                {
                    fieldLabel: lang['proverka_po'],
                    xtype: 'radiogroup',
                    width: 350,
                    columns: 1,
                    name: 'AttachementCheckOn',
                    id: 'AttachementCheckOn',
                    items: [
                        {
                            name: 'AttachementCheckOn',
                            id: 'AttachementCheckOnDefault',
                            boxLabel  : lang['ne_ukazano_'],
                            inputValue: 'default'
                        },
                        {
                            name: 'AttachementCheckOn',
                            id: 'AttachementCheckOnProfile',
                            boxLabel  : lang['profilyu'],
                            inputValue: 'profile'
                        }, {
                            name: 'AttachementCheckOn',
                            id: 'AttachementCheckOnSpec',
                            boxLabel  : lang['specialnosti'],
                            inputValue: 'spec'
                        }
                    ],
                    listeners: {
                        'change': function (radioGroup, radioBtn) {

                                if (radioBtn) {
                                    wnd.toggleProfileSpecVisibility(radioBtn.inputValue);

                                    if (!wnd.isLoadData) {

                                        var attachCombo = wnd.getMainForm().findField('LpuAttachType_id');
                                        attachCombo.fireEvent('select', attachCombo);

                                    } else
                                        wnd.isLoadData = false;
                                }
                        }
                    }

                    //bind: {value: '{currentRecordData}'},
                },
                {
                    name: 'LpuSectionProfile_id',
                    xtype: 'swlpusectionprofilecombo',
                    width: 350,
                    fieldLabel: lang['profil'],
                    editable: false,
                    allowBlank: false,
                    autoload: false,
                    listeners: {
                        'expand': function (combo) {

                            //если форма только загружена
                            if (wnd.profileStoreInit) {

                                var attachCombo = wnd.getMainForm().findField('LpuAttachType_id');
                                attachCombo.fireEvent('select', attachCombo);

                                wnd.profileStoreInit = false;
                            }
                        },
                    }
                }, {
                    editable: false,
                    allowBlank: false,
                    autoload: false,
                    name: 'MedSpecOms_id',
                    xtype: 'swmedspecomscombo',
                    fieldLabel: lang['spetsialnost'],
                    width: 350,
                    listeners: {
                        'expand': function (combo) {

                            //если форма только загружена
                            if (wnd.specStoreInit) {

                                var attachCombo = wnd.getMainForm().findField('LpuAttachType_id');
                                attachCombo.fireEvent('select', attachCombo);

                                wnd.specStoreInit = false;
                            }
                        }
                    }
                } ,

                new Ext.form.DateRangeField({
                    allowBlank: false,
                    width: 350,
                    showApply: false,
                    name:'AttachmentCheck_Period',
                    id:'activityRange',
                    fieldLabel: lang['period_deystviya'],
                    plugins:
                        [
                            new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
                        ]
                })
            ]
        });

        Ext.apply(this, {
            items: [
                this[this.formName]
            ],
            buttons: [{
                handler: function() {
                    this.doSave();
                }.createDelegate(this),
                iconCls: 'save16',
                id: 'CIEW_SaveButton',
                text: BTN_FRMSAVE
            },
                '-',
                HelpButton(this, -1),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    id: 'CIEW_CancelButton',
                    text: BTN_FRMCANCEL
                }]
        });

        Ext.getCmp('ComboLpuAttachType_id').store.addListener('load',this.onAttachTypeStoreLoaded, this);
        sw.Promed.swAttachmentCheckEditWindow.superclass.initComponent.apply(this, arguments);
    }
});
