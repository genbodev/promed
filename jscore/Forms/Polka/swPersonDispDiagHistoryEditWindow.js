/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 02.09.14
 * Time: 13:12
 * To change this template use File | Settings | File Templates.
 */

sw.Promed.swPersonDispDiagHistoryEditWindow = Ext.extend(sw.Promed.BaseForm,{
    buttonAlign: 'left',
    closable: true,
    closeAction: 'hide',
    draggable: true,
    height: 150,
    title: lang['diagnoz_v_karte_du'],
    width: 600,
    callback: Ext.emptyFn,
    doSave: function(options){
        var form = this.FormPanel;
        var base_form = form.getForm();

        if ( !base_form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    form.getFirstInvalidEl().focus(false);
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }
        var DiagDispCard_id = this.DiagDispCard_id;
        var DiagDispCard_setDate = base_form.findField('DiagDispCard_Date').getValue();
        var Diag_id = base_form.findField('Diag_id').getValue();
        var PersonDisp_id = base_form.findField('PersonDisp_id').getValue();
        var params = new Object();
        base_form.submit({
            failure: function(result_form, action) {
                if ( action.result ) {
                    if ( action.result.Error_Msg ) {
                        sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                    }
                    else {
                        sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
                    }
                }
            }.createDelegate(this),
            params: params,
            success: function(result_form, action) {
                if ( action.result ) {
                    if ( action.result.DiagDispCard_id > 0 ) {
                        var DiagHistoryGrid = Ext.getCmp('PersonDispDiagHistory').findById('PDDH_DiagHistoryGrid').ViewGridPanel;
                        DiagHistoryGrid.getStore().load({
                            params: {
                                PersonDisp_id: PersonDisp_id
                            },
                            callback: function() {
                                if ( DiagHistoryGrid.getStore().getCount() > 0 )
                                {
                                    DiagHistoryGrid.getSelectionModel().selectFirstRow();
                                    DiagHistoryGrid.getView().focusRow(0);
                                }
                            }
                        });
                        this.hide();
                    }
                    else {
                        if ( action.result.Error_Msg ) {
                            sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                        }
                        else {
                            sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
                        }
                    }
                }
                else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                }
            }.createDelegate(this)
        });
    },
    formMode: 'remote',
    formStatus: 'edit',
    id: 'PersonDispDiagHistoryEditWindow',
    initComponent: function(){
        this.sicknessDiagStore = new Ext.db.AdapterStore({
            autoLoad: false,
            dbFile: 'Promed.db',
            fields: [
				{ name: 'SicknessDiag_id', type: 'int' },
				{ name: 'Sickness_id', type: 'int' },
				{ name: 'Sickness_Code', type: 'int' },
				{ name: 'PrivilegeType_id', type: 'int' },
				{ name: 'Sickness_Name', type: 'string' },
				{ name: 'Diag_id', type: 'int' },
				{ name: 'SicknessDiag_begDT', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'SicknessDiag_endDT', type: 'date', dateFormat: 'd.m.Y' }
            ],
            key: 'SicknessDiag_id',
            sortInfo: {
                field: 'Diag_id'
            },
            tableName: 'SicknessDiag'
        });
        var parentWindow = this;
        this.FormPanel = new Ext.form.FormPanel({
            //autoHeight: true,
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 5px 5px',
            border: false,
            frame: true,
            id: 'PersonDispDiagHistoryEditForm',
            //labelAlign: 'right',
            labelWidth: 100 ,
            height: 200,
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            },  [
                { name: 'DiagDispCard_id' },
                { name: 'DiagDispCard_Date' },
                { name: 'Diag_id' },
                { name: 'PersonDisp_id' }
            ]),
            url: '/?c=PersonDisp&m=saveDiagDispCard',

            items: [{
                name: 'PersonDisp_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'DiagDispCard_id',
                value: 0,
                xtype: 'hidden'
            }, {
                allowBlank: false,
                fieldLabel: lang['data_ustanovki'],
                format: 'd.m.Y',
                name: 'DiagDispCard_Date',
                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                selectOnFocus: true,
                tabIndex: 0,
                width: 100,
                xtype: 'swdatefield'
            }, {
                allowBlank: false,
                beforeBlur: function() {
                    return true;
                },
                hiddenName: 'Diag_id',
                tabIndex: 1,
                listWidth: 500,
                width: 400,
                xtype: 'swdiagcombo',
                listeners: {
                    'change': function(combo, newValue, oldValue) {
                        var sickness_diag_store = parentWindow.sicknessDiagStore;
                        var sickness_id = null;
                        var idx = -1;
                        if (newValue != '') {
                            idx = sickness_diag_store.findBy(function(record) {
                                if (record.get('Diag_id') == newValue) {
                                    sickness_id = record.get('Sickness_id');
                                    return true;
                                }
                            });
                            if ((idx>=0) && (sickness_id != null)) {
                                if(sickness_id.toString() != '9'){
                                    alert(lang['vyibrannyiy_diagnoz_ne_otgositsya_k_gruppe_zabolevaniy_po_beremennosti_i_rodam']);
                                    combo.setValue('');
                                }
                            }
                            else{
                                alert(lang['vyibrannyiy_diagnoz_ne_otgositsya_k_gruppe_zabolevaniy_po_beremennosti_i_rodam']);
                                combo.setValue('');
                            }
                        }
                    }.createDelegate(this),
                    'select': function(combo, record, index) {
                        //alert('1');
                        //log(record);
                        //alert('2');
                        combo.setRawValue(record.get('Diag_Code') + " " + record.get('Diag_Name'));
                        // combo.focus(true);
                    }
                }
            }]
        });
        Ext.apply(this, {
            buttons: [{
                handler: function() {
                    this.doSave();
                }.createDelegate(this),
                iconCls: 'save16',
                tabIndex: 2,
                text: BTN_FRMSAVE
            }, {
                text: '-'
            },
                HelpButton(this, -1),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    tabIndex: 3,
                    text: BTN_FRMCANCEL
                }],
            items: [
                this.FormPanel
            ],
            layout: 'form'
        });

        sw.Promed.swPersonDispDiagHistoryEditWindow.superclass.initComponent.apply(this, arguments);
    },
    maximizable: false,
    maximized: false,
    modal: true,
    onHide: Ext.emptyFn,
    plain: true,
    resizable: false,
    show: function(){
        sw.Promed.swPersonDispDiagHistoryEditWindow.superclass.show.apply(this, arguments);

        this.center();
        var base_form = this.FormPanel.getForm();
        base_form.reset();

        this.action = null;
        this.DiagDispCard_id = null;
        this.callback = Ext.emptyFn;
        if (!arguments[0]) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
            return false;
        }

        if(arguments[0].action)
            this.action = arguments[0].action;
        this.PersonDisp_id = arguments[0].PersonDisp_id;
        this.sicknessDiagStore.load();

        switch ( this.action ) {
            case 'add':
                this.setTitle(lang['diagnoz_v_karte_du_dobavlenie']);
                base_form.findField('Diag_id').enable();
                base_form.findField('DiagDispCard_Date').enable();
                this.buttons[0].show();
                setCurrentDateTime({
                    callback: function() {
                        base_form.clearInvalid();
                        base_form.findField('DiagDispCard_Date').focus(true, 250);
                    }.createDelegate(this),
                    dateField: base_form.findField('DiagDispCard_Date'),
                    setDate: true,
                    windowId: this.id
                });
                base_form.findField('PersonDisp_id').setValue(this.PersonDisp_id);
                break;
            case 'edit':
            case 'view':
                if(this.action=='edit'){
                    this.setTitle(lang['diagnoz_v_karte_du_redaktirovanie']);
                    base_form.findField('Diag_id').enable();
                    base_form.findField('DiagDispCard_Date').enable();
                    this.buttons[0].show();
                }
                else{
                    base_form.findField('Diag_id').disable();
                    base_form.findField('DiagDispCard_Date').disable();
                    this.buttons[0].hide();
                    this.setTitle(lang['diagnoz_v_karte_du_prosmotr']);
                }
                var DiagDispCard_id = arguments[0].DiagDispCard_id;
                this.DiagDispCard_id = DiagDispCard_id;
                base_form.load({
                    failure: function() {
                        sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
                    }.createDelegate(this),
                    params: {
                        'DiagDispCard_id': DiagDispCard_id
                    },
                    success: function() {
                        base_form.findField('Diag_id').getStore().load({
                            callback: function() {
                                base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
                            },
                            params: {
                                where:'where Diag_id = '+base_form.findField('Diag_id').getValue()
                            }

                        });

                        //base_form.findField('Diag_id').setValue('7623');
                        base_form.clearInvalid();
                    }.createDelegate(this),
                    url: '/?c=PersonDisp&m=loadDiagDispCardEditForm'
                });
                break;
            default:
                this.hide();
                break;
        }

    }

});
