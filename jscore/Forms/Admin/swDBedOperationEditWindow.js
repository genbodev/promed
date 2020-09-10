/**
 * swDBedOperationEditWindow - окно редактирования/добавления операции.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @version      05.10.2011
 */

sw.Promed.swDBedOperationEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        action: null,
        autoHeight: true,
        buttonAlign: 'left',
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        split: true,
        width: 400,
        layout: 'form',
        id: 'DBedOperationEditWindow',
        listeners:
        {
            hide: function()
            {
                this.onHide();
            }
        },
        modal: true,
        onHide: Ext.emptyFn,
        plain: true,
        resizable: false,
        doSave: function()
        {
            if ( this.formStatus == 'save' || this.action == 'view' ) {
                return false;
            }

            var form = this.findById('DBedOperationEditForm');
            var base_form = this.DBedOperationEditForm.getForm();
            if ( !base_form.isValid() )
            {
                sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        fn: function()
                        {
                            form.getFirstInvalidEl().focus(true);
                        },
                        icon: Ext.Msg.WARNING,
                        msg: ERR_INVFIELDS_MSG,
                        title: ERR_INVFIELDS_TIT
                    });
                return false;
            }

            var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
            loadMask.show();

            var data = new Object();

            data.DBedOperationData = {
                'LpuSectionBedStateOper_id': base_form.findField('LpuSectionBedStateOper_id').getValue(),
                'LpuSectionBedState_id': base_form.findField('LpuSectionBedState_id').getValue(),
                'DBedOperation_Name': base_form.findField('DBedOperation_id').getFieldValue('DBedOperation_Name'),
                'LpuSectionBedStateOper_OperDT': base_form.findField('LpuSectionBedStateOper_OperDT').getValue(),
                'DBedOperation_id': base_form.findField('DBedOperation_id').getValue()
            };

            this.formStatus = 'edit';
            loadMask.hide();

            this.callback(data);
            this.hide();


            //this.submit();
            return true;
        },
        /*submit: function()
        {
            var form = this.findById('DBedOperationEditForm');
            var current_window = this;
            var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
            loadMask.show();
            form.getForm().submit(
                {
                    params:
                    {
                        action: current_window.action
                    },
                    failure: function(result_form, action)
                    {
                        loadMask.hide();
                        if (action.result)
                        {
                            if (action.result.Error_Code)
                            {
                                Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
                            }
                        }
                    },
                    success: function(result_form, action)
                    {
                        loadMask.hide();
                        if (action.result)
                        {
                            if (action.result.LpuSectionBedStateOper_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuSectionBedStateEditForm').findById('LPEW_DBedOperationGrid').loadData();
                            }
                            else
                            {
                                sw.swMsg.show(
                                    {
                                        buttons: Ext.Msg.OK,
                                        fn: function()
                                        {
                                            form.hide();
                                        },
                                        icon: Ext.Msg.ERROR,
                                        msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
                                        title: lang['oshibka']
                                    });
                            }
                        }
                    }
                });
        },*/
        enableEdit: function(enable)
        {
            var form = this.findById('DBedOperationEditForm');
            if (enable)
            {

                form.getForm().findField('LpuSectionBedStateOper_OperDT').enable();
                form.getForm().findField('DBedOperation_id').enable();
                this.buttons[0].enable();
            }
            else
            {
                form.getForm().findField('LpuSectionBedStateOper_OperDT').disable();
                form.getForm().findField('DBedOperation_id').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swDBedOperationEditWindow.superclass.show.apply(this, arguments);
            var current_window = this;
            if (!arguments[0])
            {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.ERROR,
                    msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
                    title: lang['oshibka'],
                    fn: function() {
                        this.hide();
                    }
                });
            }

            this.focus();
            this.findById('DBedOperationEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;
            this.formMode = 'local';
            this.formStatus = 'edit';
            //var deniedComfortTypeList = [];
            //var isEmpty = 0;

            if (arguments[0].LpuSectionBedState_id)
                this.LpuSectionBedState_id = arguments[0].LpuSectionBedState_id;
            else
                this.LpuSectionBedState_id = null;

            if (arguments[0].LpuSectionBedStateOper_id)
                this.LpuSectionBedStateOper_id = arguments[0].LpuSectionBedStateOper_id;
            else
                this.LpuSectionBedStateOper_id = null;

            if (arguments[0].DBedOperation_id)
                this.DBedOperation_id = arguments[0].DBedOperation_id;
            else
                this.DBedOperation_id = null;

            if (arguments[0].LpuSectionBedStateOper_OperDT)
                this.LpuSectionBedStateOper_OperDT = arguments[0].LpuSectionBedStateOper_OperDT;
            else
                this.LpuSectionBedStateOper_OperDT = null;

            if (arguments[0].callback)
            {
                this.callback = arguments[0].callback;
            }
            if (arguments[0].owner)
            {
                this.owner = arguments[0].owner;
            }
            if (arguments[0].onHide)
            {
                this.onHide = arguments[0].onHide;
            }
            if (arguments[0].action)
            {
                this.action = arguments[0].action;
            }
            else
            {
                if ( ( this.LpuSectionBedStateOper_id ) && ( this.LpuSectionBedStateOper_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('DBedOperationEditForm');
            form.getForm().setValues(arguments[0]);

            /*form.findField('DChamberComfort_id').getStore().clearFilter();
            form.findField('DChamberComfort_id').lastQuery = '';
            form.findField('DChamberComfort_id').getStore().filterBy(function(record) {
                if (!record.get('DChamberComfort_id').inlist(deniedComfortTypeList)) {
                    return true;
                } else {
                    return false;
                }
            });*/

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['operatsiya_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.getForm().clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['operatsiya_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['operatsiya_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                if (!(this.LpuSectionBedStateOper_id < 0)) {
                    form.getForm().load(
                    {
                        params:
                        {
                            LpuSectionBedState_id: current_window.LpuSectionBedState_id,
                            LpuSectionBedStateOper_id: current_window.LpuSectionBedStateOper_id,
                            DBedOperation_id: current_window.DBedOperation_id,
                            LpuSectionBedStateOper_OperDT: current_window.LpuSectionBedStateOper_OperDT
                        },
                        failure: function(f, o, a)
                        {
                            loadMask.hide();
                            sw.swMsg.show(
                                {
                                    buttons: Ext.Msg.OK,
                                    fn: function()
                                    {
                                        current_window.hide();
                                    },
                                    icon: Ext.Msg.ERROR,
                                    msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
                                    title: lang['oshibka']
                                });
                        },
                        success: function()
                        {
                            loadMask.hide();
                            //current_window.findById('Lpu_id').setValue(current_window.Lpu_id);
                        },
                        url: '/?c=LpuStructure&m=loadDBedOperation'
                    });
                } else {
                    loadMask.hide();
                }
            }
            if ( this.action != 'view' )
                Ext.getCmp('LpuSectionBedStateOper_OperName').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            // Форма с полями 
            this.DBedOperationEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'DBedOperationEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LpuSectionBedState_id',
                            name: 'LpuSectionBedState_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            id: 'LpuSectionBedStateOper_id',
                            name: 'LpuSectionBedStateOper_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'DBedOperation',
                            fieldLabel: lang['naimenovanie_operatsii'],
                            hiddenName: 'DBedOperation_id',
                            id: 'LpuSectionBedStateOper_OperName',
                            tabIndex: TABINDEX_LPEEW + 2,
                            xtype: 'swcommonsprcombo'
                        },{
                            fieldLabel: lang['data_operatsii'],
                            allowBlank: false,
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            name: 'LpuSectionBedStateOper_OperDT',
                            tabIndex: TABINDEX_LPEEW + 5
                        }],
                //},
                    reader: new Ext.data.JsonReader(
                        {
                            success: function()
                            {
                                //
                            }
                        },
                        [
                            {name: 'LpuSectionBedState_id'},
                            {name: 'LpuSectionBedStateOper_id'},
                            {name: 'DBedOperation_id'},
                            {name: 'LpuSectionBedStateOper_OperDT'}
                        ]),
                    url: '/?c=LpuStructure&m=saveDBedOperation'
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
                            tabIndex: TABINDEX_LPEEW + 16,
                            text: BTN_FRMSAVE
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
                            tabIndex: TABINDEX_LPEEW + 17,
                            text: BTN_FRMCANCEL
                        }],
                    items: [this.DBedOperationEditForm]
                });
            sw.Promed.swDBedOperationEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });