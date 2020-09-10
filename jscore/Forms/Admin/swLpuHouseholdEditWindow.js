/**
 * swLpuHouseholdEditWindow - окно формы домового хозяйства
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Max Sysolin (max.sysolin@gmail.com)
 * @version			11.05.2017
 */

sw.Promed.swLpuHouseholdEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'swLpuHouseholdEditWindow',
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
    entity: 'LpuHousehold',
    // имя основной формы
    formName: 'LpuHouseholdEditForm',
    // краткое имя основной формы
    formPrefix: 'LHHEW_',
    // поле id главной сущности
    mainIdField: 'LpuHousehold_id',
    // флаг, определяющий загрузили данные в форму через функцию loadData или нет
    isLoadData: false,

    getMainForm: function()
    {
        return this[this.formName].getForm();
    },

    doSave: function() {

        var wnd = this,
            form = wnd.getMainForm(),
            loadMask = new Ext.LoadMask(

                wnd.getEl(), {
                    msg: lang['podojdite_idet_sohranenie']
                }
            );
        var controlPersonName = wnd.controlFieldContactPerson();
        if(!controlPersonName) controlPersonName = wnd.controlOfTheFieldLpuHousehold_Name();
        if( controlPersonName ){
            sw.swMsg.show(
            {
                buttons: Ext.Msg.OK,
                icon: Ext.Msg.WARNING,
                msg: controlPersonName,
                title: 'Ошибка !!!'
            });
            return false;
        }

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
                formPrefix: (wnd.formPrefix) ? wnd.formPrefix : ''
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

                    Ext.getCmp('LpuPassportEditWindow').findById('LPEW_HouseholdGrid').loadData();
                    //wnd.callback();
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
        input_params.formPrefix = (wnd.formPrefix) ? wnd.formPrefix : '';

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

                    //if (resp.result.data) {}
                    loadMask.hide();
                },
                url: '/?c=LpuPassport&m=getLpuHouseholdRecord'
            });
    },

    show: function() {

        sw.Promed.swLpuHouseholdEditWindow.superclass.show.apply(this, arguments);

        var wnd = this,
            form = wnd.getMainForm(),
            main_id_field = wnd.mainIdField,

            loadMask = new Ext.LoadMask(

                wnd.getEl(),{
                    msg: LOAD_WAIT
                }
            );

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

        var args = arguments[0];
        wnd.action = arguments[0].action ? arguments[0].action : null;

        loadMask.show();
        wnd.focus();

        form.reset();
        form.setValues(args);

        // оптимизировал присвоение параметров arguments[0]
        for (var field_name in args) {

            //log(field_name +':'+ args[field_name]);
            wnd[field_name] = args[field_name];
        }

        if (!wnd.action) {

            if ((wnd[main_id_field]) && (wnd[main_id_field] > 0))
                wnd.action = "edit";
            else
                wnd.action = "add";
        }

        loadMask.show();

        switch (wnd.action) {

            case 'add':

                wnd.setTitle(
                    lang['domovoe_hozjajstvo']
                    + ': '
                    + lang['dobavlenie']
                );

                wnd.enableEdit(true);

                form.clearInvalid();
                loadMask.hide();
                break;

            case 'edit':

                wnd.setTitle(
                    lang['domovoe_hozjajstvo']
                    + ': '
                    + lang['redaktirovanie']
                );

                wnd.enableEdit(true);
                wnd.loadForm(loadMask);

                break;

            case 'view':

                wnd.setTitle(
                    lang['domovoe_hozjajstvo']
                    + ': '
                    + lang['prosmotr']
                );

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

    getPAddressFields: function()
    {
        var formPrefix = this.formPrefix,
            pAddressFields = {
                Address_ZipEdit: 'PAddress_Zip',
                KLCountry_idEdit: 'PKLCountry_id',
                KLRgn_idEdit: 'PKLRGN_id',
                KLSubRGN_idEdit: 'PKLSubRGN_id',
                KLCity_idEdit: 'PKLCity_id',
                KLTown_idEdit: 'PKLTown_id',
                KLStreet_idEdit: 'PKLStreet_id',
                Address_HouseEdit: 'PAddress_House',
                Address_CorpusEdit: 'PAddress_Corpus',
                Address_FlatEdit: 'PAddress_Flat',
                Address_AddressEdit: 'PAddress_Address'
            };

        for (var key in pAddressFields) {
            pAddressFields[key] = formPrefix + pAddressFields[key];
        }

        return pAddressFields;
    },

    getFormFieldValue: function(id)
    {
        var form = this.getMainForm();
        return form.findField(id).value;
    },

    setFormFieldValue: function(id,val)
    {
        var form = this.getMainForm();
        return form.findField(id).setValue(val);
    },

    controlFieldContactPerson: function(){
        debugger;
        var wnd = this,
            form = wnd.getMainForm();
        var LH_ContactPerson = form.findField('LpuHousehold_ContactPerson').getValue();
        var error_msg = 'В контактном лице домового хозяйства допустим ввод до 4-х слов на кириллице (первое и последнее слово могут состоять из одной буквы и более, слова посередине – из 2-х букв и более), разделенных пробелом или дефисом.';
        var errorFlag = false;
        if(!LH_ContactPerson) return false;

        if( /([A-Za-z])/.test(LH_ContactPerson) ){
            return error_msg;
        }
        var s = LH_ContactPerson.split(' ');
        var arrCP = [];
        for (i = 0; i < s.length; i++) {
            if( /-/.test(s[i]) ){
                arrCP = arrCP.concat( s[i].split('-') );
            }else{
                arrCP.push(s[i]);
            }
        }
        var n = arrCP.length;
        if(n > 4){
            errorFlag = error_msg;
        }else if(n == 4){
            if(arrCP[1].length < 2 || arrCP[2].length < 2){
                errorFlag = error_msg;
            }
        }else if(n == 3){
            if(arrCP[1].length < 2 ){
                errorFlag = error_msg;
            }
        }
        return errorFlag;
    },
    controlOfTheFieldLpuHousehold_Name: function(){
        var wnd = this,
            form = wnd.getMainForm();

        var LLBPass = form.findField('LpuHousehold_Name').getValue(); // поле "Наименование"
        if(!LLBPass) return false;
        var rxArr = [
            {
                rx: /([^А-Яа-яёЁ\d\s-,№()"«»\.])|(^\([^)(]*\))|(^"[^"]*")|(^«[^«]*»)|(№.*№)/,
                error_msg: 'В наименовании допустимо использование только следующих знаков: буквы (кириллица), цифры, круглые парные скобки "(" и ")", дефис, пробел, запятая, парные кавычки типов " " и « » и один знак "№"».',
                res: true
            },
            {
                rx: /^[А-Яа-я0-9]*[\s-][А-Яа-я\s-,№("«\.]{2,}/,
                error_msg: 'Наименование может начинаться только на букву или цифру, за которой должны следовать либо пробел и слово, либо дефис и слово. Словом считается любая последовательность кириллических букв более двух знаков',
                res: false
            },
            {
                rx: /(--)|(\s\s)/,
                error_msg: 'В наименовании не должно быть более одного пробела или дефиса подряд',
                res: true
            },
            {
                rx: /\s-/,
                error_msg: 'В наименовании не должны располагаться подряд пробел и дефис',
                res: true
            },
            {
                rx: /(№[^\s\d])|(№\s\D)/,
                error_msg: 'В наименовании после знака номера "№" допустимы либо цифра, либо один пробел и цифра',
                res: true
            },
            {
                rx: /\([\(-,:\.\s]/,
                error_msg: 'В наименовании, после открывающейся скобки "(", должны следовать цифра или слово. Не допускается использование после скобки "(" другой скобки, дефиса, запятой или пробела.',
                res: true
            },
            {
                rx: /[^\s]\(/,
                error_msg: 'В наименовании обязательно использование пробела перед открывающейся скобкой "(".',
                res: true
            },
            {
                rx: /\)[^\s]/,
                error_msg: 'В наименовании обязательно использование пробела после закрывающейся скобки ")", расположенной не в конце',
                res: true
            },
            {
                rx: /(\).)$/,
                error_msg: 'В конце наименования после закрывающейся скобки ")" недопустимы иные символы',
                res: true
            },
            {
                rx: /\s,/,
                error_msg: 'Перед запятой недопустим пробел',
                res: true
            },
            {
                rx: /,[^\s]/,
                error_msg: 'После запятой обязателен пробел',
                res: true
            },
            {
                rx: /(».)|(".)$/,
                error_msg: 'После закрывающейся кавычки в конце наименования недопустимы иные символы',
                res: true
            },
            {
                rx: /("[^А-Яа-я0-9].*")|(«[^А-Яа-я0-9].*»)/,
                error_msg: 'После открывающейся кавычки должны следовать цифра или слово и недопустимы: другая кавычка, дефис, запятая, скобка, пробел',
                res: true
            },
            {
                rx: /(".*["\s,\)\(-]")|(«.*[«\s,\)\(-]»)/,
                error_msg: 'Перед закрывающей кавычкой недопустимы кавычки, дефис, запятая, скобка, пробел',
                res: true
            },
            
        ];
    
        for (i = 0; i < rxArr.length; i++) {
            var elem = rxArr[i];
            if( elem.rx.test(LLBPass) == elem.res){
                return elem.error_msg;
            }
        }

        function quotation(LLBPass){
            //парные скобки, кавычки
            var opening_parenthesis = LLBPass.match(/\(/g);
            var closing_parenthesis = LLBPass.match(/\)/g);
            var quotation_mark = LLBPass.match(/\"/g);
            var opening_quotation = LLBPass.match(/\«/g);
            var closing_quotation = LLBPass.match(/\»/g);
            if( quotation_mark && quotation_mark.length%2 ){
                //не четные
                return false;
            }else if(
                (opening_parenthesis && closing_parenthesis && opening_parenthesis.length!=closing_parenthesis.length)
                || (opening_parenthesis && !closing_parenthesis)
                || (!opening_parenthesis && closing_parenthesis)
            ){
                return false;
            }else if(
                (opening_quotation && closing_quotation && opening_quotation.length!=closing_quotation.length)
                || (opening_quotation && !closing_quotation)
                || (!opening_quotation && closing_quotation)
            ){
                return false;
            }else{
                return true;
            }
        }

        if( !quotation(LLBPass) ){
            return 'В наименовании допустимо использование только следующих знаков: буквы (кириллица), цифры, круглые парные скобки "(" и ")", дефис, пробел, запятая, парные кавычки типов " " и « » и один знак "№"».';
        }else{
            return false;
        }
    },
    initComponent: function() {

        var wnd = this,
            formName = wnd.formName,
            formPrefix = wnd.formPrefix;

        wnd[formName] = new Ext.form.FormPanel(
            {
                bodyStyle: '{padding-top: 0.5em;}',
                border: false,
                frame: true,
                labelAlign: 'right',
                labelWidth: 200,
                layout: 'form',
                id: formName,
                url: '/?c=LpuPassport&m=saveLpuHouseholdRecord',
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    success: Ext.emptyFn
                }, [
                    { name: 'LPEW_Lpu_id' },
                    { name: 'LpuHousehold_id' },
                    { name: 'LpuHousehold_Name' },
                    { name: 'LpuHousehold_ContactPerson' },
                    { name: 'LpuHousehold_ContactPhone' },
                    { name: 'LpuHousehold_CadNumber' },
                    { name: 'LpuHousehold_CoordLat' },
                    { name: 'LpuHousehold_CoordLon' },
                    { name: 'LpuHousehold_Index' },
                    { name: 'LpuHousehold_Address' },
                    { name: formPrefix + 'PAddress_id'},
                    { name: formPrefix + 'PAddress_Zip'},
                    { name: formPrefix + 'PKLCountry_id'},
                    { name: formPrefix + 'PKLRGN_id'},
                    { name: formPrefix + 'PKLSubRGN_id'},
                    { name: formPrefix + 'PKLCity_id'},
                    { name: formPrefix + 'PKLTown_id'},
                    { name: formPrefix + 'PKLStreet_id'},
                    { name: formPrefix + 'PAddress_House'},
                    { name: formPrefix + 'PAddress_Corpus'},
                    { name: formPrefix + 'PAddress_Flat'},
                    { name: formPrefix + 'PAddress_Address'}
                ]),
                items: [
                    {
                        name: 'LPEW_Lpu_id',
                        xtype: 'hidden'
                    },
                    {
                        name: 'LpuHousehold_id',
                        xtype: 'hidden'
                    },
                    {
                        fieldLabel: lang['naimenovanie'],
                        allowBlank: false,
                        name: 'LpuHousehold_Name',
                        xtype: 'textfield',
                        autoCreate: {tag: "input", maxLength: "256", autocomplete: "off"},
                        width: 400,
                    }, {
                        fieldLabel: lang['kontaktnoe_lico'],
                        allowBlank: false,
                        name: 'LpuHousehold_ContactPerson',
                        xtype: 'textfield',
                        autoCreate: {tag: "input", maxLength: "256", autocomplete: "off"},
                        width: 400,
                    },
                    {
                        fieldLabel: lang['kontaktnyiy_telefon'],
                        allowBlank: false,
                        name: 'LpuHousehold_ContactPhone',
                        xtype: 'textfield',
                        maskRe: /[0-9]/,
                        autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
                        width: 400,
                    },
                    {
                        fieldLabel: lang['kadastrovyj_nomer'],
                        name: 'LpuHousehold_CadNumber',
                        xtype: 'textfield',
                        autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
                        width: 400,
                    },
                    {
                        fieldLabel: lang['koordinaty_shirota'],
                        allowBlank: false,
                        allowDecimals: true,
                        allowNegative: true,
                        decimalPrecision: 6,
                        name: 'LpuHousehold_CoordLat',
                        xtype: 'numberfield',
                        maskRe: /[0-9.]/,
                        autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
                        width: 400,
                    },
                    {
                        fieldLabel: lang['koordinaty_dolgota'],
                        allowBlank: false,
                        allowDecimals: true,
                        allowNegative: true,
                        decimalPrecision: 6,
                        name: 'LpuHousehold_CoordLon',
                        xtype: 'numberfield',
                        maskRe: /[0-9.]/,
                        autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
                        width: 400,
                    },
                    new sw.Promed.TripleTriggerField ({
                        enableKeyEvents: true,
                        allowBlank: false,
                        fieldLabel: lang['adres_hozjajstva'],
                        id: 'LpuHousehold_Address',
                        name: 'LpuHousehold_Address',
                        listeners: {
                            'keydown': function(inp, e) {

                            },
                            'keyup': function( inp, e ) {

                            }
                        },
                        onTrigger1Click: function() {

                            var showParams = wnd.getPAddressFields();

                            for (var key in showParams) {
                                showParams[key] = wnd.getFormFieldValue(showParams[key]);
                            }

                            getWnd('swAddressEditWindow').show({

                                fields: showParams,
                                callback: function(values) {

                                    var callbackParams = wnd.getPAddressFields(),
                                        addressField = wnd.getMainForm().findField('LpuHousehold_Address');

                                    for (var key in callbackParams) {
                                        wnd.setFormFieldValue(callbackParams[key],values[key]);
                                    }

                                    addressField.setValue(values.Address_AddressEdit);
                                    addressField.focus(true, 500);
                                },
                                onClose: function() {
                                    //wnd.getMainForm().findById('LPEW_PAddress_AddressText').focus(true, 500);
                                }
                            })
                        },
                        onTrigger3Click: function() {

                            var clearParams = wnd.getPAddressFields();

                            for (var key in clearParams) {
                                wnd.setFormFieldValue(clearParams[key],'');
                            }
                        },
                        readOnly: true,
                        trigger1Class: 'x-form-search-trigger',
                        trigger3Class: 'x-form-clear-trigger',
                        width: 400
                    }),
                    {
                        id: formPrefix + 'PAddress_id',
                        name: formPrefix + 'PAddress_id',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PAddress_Zip',
                        name: formPrefix +'PAddress_Zip',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PKLCountry_id',
                        name: formPrefix +'PKLCountry_id',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PKLRGN_id',
                        name: formPrefix +'PKLRGN_id',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PKLSubRGN_id',
                        name: formPrefix +'PKLSubRGN_id',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PKLCity_id',
                        name: formPrefix +'PKLCity_id',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PKLTown_id',
                        name: formPrefix +'PKLTown_id',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PKLStreet_id',
                        name: formPrefix +'PKLStreet_id',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PAddress_House',
                        name: formPrefix +'PAddress_House',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PAddress_Corpus',
                        name: formPrefix +'PAddress_Corpus',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PAddress_Flat',
                        name: formPrefix +'PAddress_Flat',
                        xtype: 'hidden'
                    }, {
                        id: formPrefix + 'PAddress_Address',
                        name: formPrefix +'PAddress_Address',
                        xtype: 'hidden'
                    },
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

        sw.Promed.swLpuHouseholdEditWindow.superclass.initComponent.apply(this, arguments);
    }
});
