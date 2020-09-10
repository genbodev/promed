/**
 * swDChamberComfortEditWindow - окно редактирования/добавления объекта комфортности.
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

sw.Promed.swDChamberComfortEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'DChamberComfortEditWindow',
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
        formStatus: 'edit',
        doSave: function()
        {
            if ( this.formStatus == 'save' || this.action == 'view' ) {
                return false;
            }

            //this.formStatus = 'save';

            var base_form = this.DChamberComfortEditForm.getForm();
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

            data.DChamberComfortData = {
                'LpuSectionWardComfortLink_id': base_form.findField('LpuSectionWardComfortLink_id').getValue(),
                'LpuSectionWard_id': base_form.findField('LpuSectionWard_id').getValue(),
                'DChamberComfort_Name': base_form.findField('DChamberComfort_id').getFieldValue('DChamberComfort_Name'),
                'LpuSectionWardComfortLink_Count': base_form.findField('LpuSectionWardComfortLink_Count').getValue(),
                'DChamberComfort_id': base_form.findField('DChamberComfort_id').getValue()
            };

            //log(data);

            this.formStatus = 'edit';
            loadMask.hide();

            this.callback(data);
            this.hide();

            /* switch ( this.formMode ) {
                case 'local':
                    this.formStatus = 'edit';
                    loadMask.hide();

                    this.callback(data);
                    this.hide();
                    break;

                case 'remote':
                    base_form.submit({
                        failure: function(result_form, action) {
                            this.formStatus = 'edit';
                            loadMask.hide();

                            if ( action.result ) {
                                if ( action.result.Error_Msg ) {
                                    sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                                }
                                else {
                                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
                                }
                            }
                        }.createDelegate(this),
                        success: function(result_form, action) {
                            this.formStatus = 'edit';
                            loadMask.hide();

                            if ( action.result && action.result.LpuSectionWardComfortLink_id > 0 ) {
                                base_form.findField('LpuSectionWardComfortLink_id').setValue(action.result.LpuSectionWardComfortLink_id);
                                data.DChamberComfortData.LpuSectionWardComfortLink_id = base_form.findField('LpuSectionWardComfortLink_id').getValue();

                                this.callback(data);
                                this.hide();
                            }
                            else {
                                sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                            }
                        }.createDelegate(this)
                    });
                break;
            }*/
            return true;
        },
        enableEdit: function(enable)
        {
            var form = this.findById('DChamberComfortEditForm').getForm();

            if (enable)
            {
                form.findField('DChamberComfort_id').enable();
                form.findField('LpuSectionWardComfortLink_Count').enable();
                this.buttons[0].enable();
            }
            else
            {
                form.findField('DChamberComfort_id').disable();
                form.findField('LpuSectionWardComfortLink_Count').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swDChamberComfortEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('DChamberComfortEditForm').getForm().reset();
            this.action = null;
            this.callback = Ext.emptyFn;
            this.formMode = 'local';
            this.formStatus = 'edit';
            this.onHide = Ext.emptyFn;
            var deniedComfortTypeList = [];
            var isEmpty = 0;

            if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
                this.formMode = 'remote';
            }

            if ( arguments[0].deniedComfortTypeList ) {
                deniedComfortTypeList = arguments[0].deniedComfortTypeList;
            }

            if (arguments[0].LpuSectionWard_id)
                this.LpuSectionWard_id = arguments[0].LpuSectionWard_id;
            else
                this.LpuSectionWard_id = null;

            if (arguments[0].DChamberComfort_id)
                this.DChamberComfort_id = arguments[0].DChamberComfort_id;
            else
                this.DChamberComfort_id = null;

            if (arguments[0].LpuSectionWardComfortLink_Count)
                this.LpuSectionWardComfortLink_Count = arguments[0].LpuSectionWardComfortLink_Count;
            else
                this.LpuSectionWardComfortLink_Count = null;

            if (arguments[0].LpuSectionWardComfortLink_id)
                this.LpuSectionWardComfortLink_id = arguments[0].LpuSectionWardComfortLink_id;
            else
                this.LpuSectionWardComfortLink_id = null;

            if ( arguments[0].callback ) {
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
                if ( ( this.LpuSectionWardComfortLink_id ) && ( this.LpuSectionWardComfortLink_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.DChamberComfortEditForm.getForm();
            form.reset();
            form.setValues(arguments[0]);
            form.findField('DChamberComfort_id').getStore().clearFilter();
            form.findField('DChamberComfort_id').lastQuery = '';

            form.findField('DChamberComfort_id').getStore().filterBy(function(record) {
                if (!record.get('DChamberComfort_id').inlist(deniedComfortTypeList)) {
                    return true;
                } else {
                    return false;
                }
            });

            form.findField('DChamberComfort_id').getStore().findBy(function(rec) {
                if (!Ext.isEmpty(rec.get('DChamberComfort_id'))) {
                    isEmpty = 1
                }
            });

            if ( isEmpty == 0) {
                sw.swMsg.alert(lang['soobschenie'], lang['ne_ostalos_unikalnyih_naimenovaniy_obyektov_komfortnosti'], function() { this.hide(); }.createDelegate(this) );
                return false;
            }

            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['obyekt_komfortnosti_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();
                    form.clearInvalid();
                    break;
                case 'edit':
                    this.setTitle(lang['obyekt_komfortnosti_redaktirovanie']);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['obyekt_komfortnosti_prosmotr']);
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                if (!(this.LpuSectionWardComfortLink_id < 0)) {
                    form.load({
                        params:
                        {
                            LpuSectionWard_id: current_window.LpuSectionWard_id,
                            DChamberComfort_id: current_window.DChamberComfort_id,
                            LpuSectionWardComfortLink_Count: current_window.LpuSectionWardComfortLink_Count,
                            LpuSectionWardComfortLink_id: ((current_window.LpuSectionWardComfortLink_id > 0)?(current_window.LpuSectionWardComfortLink_id) : (null))
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
                        },
                        url: '/?c=LpuStructure&m=loadLpuSectionWardComfortLink'
                    });
                } else {
                    loadMask.hide();
                }
            }
            if ( this.action != 'view' )
                Ext.getCmp('LPEW_DChamberComfort_Name').focus(true, 100);
            else
                this.buttons[3].focus();
        },
        initComponent: function()
        {
            this.DChamberComfortEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'DChamberComfortEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            name: 'LpuSectionWard_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            id: 'LpuSectionWardComfortLink_id',
                            name: 'LpuSectionWardComfortLink_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            anchor: '100%',
                            allowBlank: false,
                            comboSubject: 'DChamberComfort',
                            fieldLabel: lang['naimenovanie_obyekta'],
                            hiddenName: 'DChamberComfort_id',
                            id: 'LPEW_DChamberComfort_Name',
                            tabIndex: TABINDEX_LPEEW + 2,
                            xtype: 'swcommonsprcombo'
                        },{
                            fieldLabel: lang['kolichestvo'],
                            allowBlank: false,
                            xtype: 'textfield',
                            anchor: '100%',
                            name: 'LpuSectionWardComfortLink_Count',
                            tabIndex: TABINDEX_LPEEW + 3,
                            maskRe: /[0-9]/
                        }],
                    reader: new Ext.data.JsonReader(
                        {
                            success: function()
                            {
                                //
                            }
                        },
                        [
                            {name: 'LpuSectionWard_id'},//ИД палаты
                            {name: 'LpuSectionWardComfortLink_id'},//ИД объекта комфортности
                            {name: 'DChamberComfort_id'},
                            {name: 'LpuSectionWardComfortLink_Count'}//Количество объектов комфортности
                        ]),
                    url: '/?c=LpuStructure&m=saveLpuSectionWardComfortLink'
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
                    items: [this.DChamberComfortEditForm]
                });
            sw.Promed.swDChamberComfortEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });