/**
 * swIndividualPeriodEditWindow - окно редактирования индивидуальных периодов записи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Timofeev
 * @version			31.05.2019
 */

sw.Promed.swIndividualPeriodEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'swIndividualPeriodEditWindow',
    action: null,
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    split: true,
    title: 'Добавление МО',
    autoHeight: true,
    width: 550,
    layout: 'form',
    modal: true,
    plain: true,
    resizable: false,

    // имя основной формы, чтобы не задавать внутри
    formName: 'IndividualPeriodEditWindow',
    
    getMainForm: function()
    {
        return this[this.formName].getForm();
    },

    doSave: function() {

        var win = this,
            base_form = win.getMainForm(),
            loadMask = new Ext.LoadMask(

                win.getEl(), {
                    msg: langs('Подождите, идет сохранение...')
                }
            );

        if (!base_form.isValid()) {

            sw.swMsg.show({

                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT,
                icon: Ext.Msg.WARNING,
                buttons: Ext.Msg.OK,

                fn: function() {
                    win[win.formName].getFirstInvalidEl().focus(true);
                }
            });

            return false;
        }

        loadMask.show();

        base_form.submit({
            params: {
                action: win.action,
            },
            failure: function(result_form, action) {
                loadMask.hide();

                if(action.result && action.result.Error_Msg) {
                    switch(action.result.Error_Code) {
                        case 101:
                            sw.swMsg.show({
                                buttons: Ext.Msg.YESNO,
                                fn: function(buttonId, text, obj) {
                                    if ( buttonId == 'yes' ) {
                                        base_form.findField('IndividualPeriod_id').setValue(action.result.IndividualPeriod_id);
                                        win.doSave();
                                    }
                                },
                                icon: Ext.MessageBox.QUESTION,
                                msg: action.result.Alert_Msg,
                                title: langs('Продолжить сохранение?')
                            });
                            break;
                        default:
                            sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
                    }
                }
            },
            success: function(result_form, action) {

                loadMask.hide();

                if ( action.result ) {
                    win.callback();
                    win.hide();
                } else
                    sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
            }
        });
    },  
    setMode: function() {
        var win = this;
        var base_form = win[win.formName].getForm();

        var Lpu_id = getGlobalOptions().lpu_id;

        base_form.findField('MedStaffFact_id').hideContainer();
        base_form.findField('LpuSection_id').hideContainer();
        base_form.findField('MedService_id').hideContainer();

        base_form.findField('MedStaffFact_id').setAllowBlank(true);
        base_form.findField('LpuSection_id').setAllowBlank(true);
        base_form.findField('MedService_id').setAllowBlank(true);

        if(win.action != 'add') {
            win.mode = win.IndividualPeriodType_id;
        }

        switch(win.mode) {
            case 'MedStaffFact':
            case 1:
                var label = 'в поликлинике';
                win.setTitle('Место работы врача поликлиники');
                
                base_form.findField('MedStaffFact_id').showContainer();
                base_form.findField('IndividualPeriodType_id').setValue(1);
                base_form.findField('MedStaffFact_id').setAllowBlank(false);

                if(base_form.findField('MedStaffFact_id').getStore().getCount() == 0 ) {
                    base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
                    base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getValue()); 
                }
                

            break;
            case 'LpuSection':
            case 2:
                var label = 'в стационар';
                win.setTitle('Отделение стационара');
                base_form.findField('LpuSection_id').showContainer();
                
                base_form.findField('IndividualPeriodType_id').setValue(2);
                base_form.findField('LpuSection_id').setAllowBlank(false);

                if( base_form.findField('LpuSection_id').getStore().getCount() == 0 ) {
                    base_form.findField('LpuSection_id').getStore().load({
                        params: {
                            Lpu_id: Lpu_id,
                            isStac: true
                        },
                        callback: function() {
                            base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getValue());
                        }
                    });
                }
                
            break;
            case 'MedService':
            case 3:
                var label = 'на службу';
                win.setTitle('Служба');
                base_form.findField('MedService_id').showContainer();

                base_form.findField('IndividualPeriodType_id').setValue(3);
                base_form.findField('MedService_id').setAllowBlank(false);
                if( base_form.findField('MedService_id').getStore().getCount() == 0) {
                    base_form.findField('MedService_id').getStore().load({
                        params: {
                            Lpu_id: Lpu_id
                        },
                        callback: function() {
                            base_form.findField('MedService_id').setValue(base_form.findField('MedService_id').getValue());
                        }
                    });
                }
            break;
        }
        base_form.findField('IndividualPeriod_value').setFieldLabel('На сколько дней вперед разрешить запись ' + label + ' регистратору МО');
    },
    show: function() {

        sw.Promed.swIndividualPeriodEditWindow.superclass.show.apply(this, arguments);

        var win = this,
            base_form = win.getMainForm();
          

        // обновляем грид после сохранения
        if (arguments[0].callback) {
            win.callback = arguments[0].callback;
        }

        var args = arguments[0].formParams;
        win.action = arguments[0].action ? arguments[0].action : null;

        if (arguments[0].menu_action) {
            win.mode = arguments[0].menu_action
        }

        if (args.IndividualPeriod_id) {
            this.IndividualPeriod_id = args.IndividualPeriod_id;
        }

        if(args.IndividualPeriodType_id) {
            this.IndividualPeriodType_id = args.IndividualPeriodType_id;
        }

        win.focus();

        base_form.reset();
        

        win.setMode();
        switch(win.action) {
            case 'add':
                                
                
            break;
            case 'edit':
            case 'view':            
                win.loadForm();
            break;
        }
    },
   
    loadForm: function(callback) {
        var win = this;
        base_form = this.getMainForm();
        base_form.load({
            url: '/?c=LpuIndividualPeriod&m=loadIndividualPeriodEditForm',
            params: {
                IndividualPeriod_id: win.IndividualPeriod_id
            },
            success: function(form, action) {
                if(typeof(callback) == 'function') {
                    callback(form, action);
                }
            }
        });
    },

    initComponent: function() {

        var win = this,
            formName = win.formName;

        win[formName] = new Ext.form.FormPanel(
            {

            bodyStyle: '{padding-top: 0.5em;}',
            border: false,
            frame: true,
            labelAlign: 'right',
            labelWidth: 200,
            layout: 'form',
            id: formName,
            url: '/?c=LpuIndividualPeriod&m=saveIndividualPeriod',
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                { name: 'IndividualPeriod_id' },
                { name: 'IndividualPeriodType_id' },
                { name: 'MedStaffFact_id' },
                { name: 'LpuSection_id' },
                { name: 'MedService_id' },
                { name: 'IndividualPeriod_value' } 
                
            ]),
            items: [
                {
                    name: 'IndividualPeriod_id',
                    xtype: 'hidden'
                }, {
                    name: 'IndividualPeriodType_id',
                    xtype: 'hidden'
                }, {
                    allowBlank: false,
                    width: 300,
                    hiddenName: 'MedStaffFact_id',
                    xtype: 'swmedstafffactglobalcombo'
                }, {
                    allowBlank: false,
                    width: 300,
                    hiddenName: 'LpuSection_id',
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">'+
                        '<table style="border: 0;">'+
                        '<tr><td><font color="red">{LpuSection_Code}</font>&nbsp;{LpuSection_Name}</td></tr>'+
                        '<tr><td><div style="font-size: 10px;">{LpuUnit_Name}</div></td></tr>' +
                        '</table>'+
                        '</div></tpl>'
                    ),
                    xtype: 'swlpusectioncombo'
                }, {
                    allowBlank: false,
                    width: 300,
                    hiddenName: 'MedService_id',
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">'+
                        '<table style="border: 0;">'+
                        '<tr><td>{MedService_Name}</td></tr>'+
                        '<tr><td><div style=\"font-size: 10px;\">{[(values.LpuBuilding_Name) ? values.LpuBuilding_Name : "" ]}{[(values.LpuUnit_Name) ? " / " + values.LpuUnit_Name : ""]}</div></td></tr>' +
                        '<tr><td><div style=\"font-size: 10px;\">{[(values.LpuSection_Name) ? "" + values.LpuSection_Name + "" : ""]}</div></td></tr>' +
                        '</table>'+
                        '</div></tpl>'
                    ),
                    xtype: 'swmedservicecombo'
                }, {
                    fieldLabel: 'На сколько дней вперед разрешить запись в поликлинике регистратору МО',
                    allowBlank: false,
                    width: 50,
                    name: 'IndividualPeriod_value',
                    maxValue: 370,
                    minValue: 1,
                    xtype: 'numberfield'
                }


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
                id: 'IPEW_SaveButton',
                text: BTN_FRMSAVE
            },
            '-',
            HelpButton(this, -1),
            {
                handler: function() {
                    this.hide();
                }.createDelegate(this),
                iconCls: 'cancel16',
                id: 'IPEW_CancelButton',
                text: BTN_FRMCANCEL
            }]
        });

        sw.Promed.swIndividualPeriodEditWindow.superclass.initComponent.apply(this, arguments);
    }
});
