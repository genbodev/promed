/**
 * swPolkaEvnPrescrRegimeEditWindow - окно добавления/редактирования назначения c типом Режим.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Prescription
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @version      11.2013
 */
/*NO PARSE JSON*/

sw.Promed.swPolkaEvnPrescrRegimeEditWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh: true,
    objectName: 'swPolkaEvnPrescrRegimeEditWindow',
    objectSrc: '/jscore/Forms/Prescription/swPolkaEvnPrescrRegimeEditWindow.js',

    PrescriptionType_id: 1,
    action: null,
    onHide: Ext.emptyFn,
    callback: Ext.emptyFn,
    autoHeight: true,
    width: 550,
    buttonAlign: 'left',
    closable: true,
    closeAction: 'hide',
    collapsible: false,
    draggable: true,
    formStatus: 'edit',
    id: 'PolkaEvnPrescrRegimeEditWindow',
    layout: 'form',
    listeners: {
        hide: function(win) {
            win.onHide();
        }
    },
    maximizable: false,
    maximized: false,
    modal: true,
    plain: true,
    resizable: false,
    keys: [{
        alt: true,
        fn: function(inp, e) {
            switch ( e.getKey() ) {
                case Ext.EventObject.C:
                    this.doSave();
                    break;

                case Ext.EventObject.J:
                    this.hide();
                    break;
            }
        }.createDelegate(this),
        key: [
            Ext.EventObject.C,
            Ext.EventObject.J
        ],
        scope: this,
        stopEvent: false
    }],
    doSave: function(options) {
        var thas = this;
        if ( this.formStatus == 'save' ) {
            return false;
        }

        if ( typeof options != 'object' ) {
            options = {};
        }

        var base_form = this.FormPanel.getForm();
		base_form.findField('PrescriptionRegimeType_id').enable();
        if ( !base_form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    thas.formStatus = 'edit';
                    thas.FormPanel.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        var params = {};
        params.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
        params.PrescriptionType_id = this.PrescriptionType_id;

        params.signature = (options.signature)?1:0;

        this.formStatus = 'save';
        this.getLoadMask(LOAD_WAIT_SAVE).show();
        base_form.submit({
            failure: function(result_form, action) {
                thas.formStatus = 'edit';
                thas.getLoadMask().hide();

                if ( action.result ) {
                    if ( action.result.Error_Msg ) {
                        sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                    }
                    else {
                        sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
                    }
                }
            },
            params: params,
            success: function(result_form, action) {
                thas.formStatus = 'edit';
                thas.getLoadMask().hide();

                if ( action.result ) {
                    var data = {};
                    data.evnPrescrData = base_form.getValues();
                    data.evnPrescrData.EvnPrescr_id = action.result.EvnPrescr_id;
                    thas.callback(data);
                    thas.hide();
                }
                else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                }
            }
        });
        return true;
    },
    setFieldsDisabled: function(d,obj)
    {
		var win = this;
		obj=obj||this.FormPanel
		if(obj.items){
			obj.items.each(function(f)
			{
				if (f && (f.xtype!='hidden') &&  (f.xtype!='fieldset')  && (f.changeDisabled!==false))
				{
					if((typeof f.getLayout=='function')){
						win.setFieldsDisabled(d,f);
					}else{
						f.setDisabled(d);
					}
				}
			});
		}
        this.buttons[0].setDisabled(d);
		this.buttons[2].setText((d)?lang['zakryit']:lang['otmena']);
    },
    initComponent: function() {
        var thas = this;

        this.FormPanel = new Ext.form.FormPanel({
            autoHeight: true,
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0',
            border: false,
            frame: true,
            id: 'PolkaEvnPrescrRegimeEditForm',
            labelAlign: 'right',
            labelWidth: 160,
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            },  [
                { name: 'accessType' },
                { name: 'EvnPrescr_id' },
                { name: 'EvnPrescr_pid' },
                { name: 'PrescriptionRegimeType_id' },
                { name: 'EvnPrescr_setDate' },
                { name: 'EvnPrescr_dayNum' },
                { name: 'EvnPrescr_Descr' },
                { name: 'PersonEvn_id' },
                { name: 'Server_id' }
            ]),
            region: 'center',
            url: '/?c=EvnPrescr&m=saveEvnPrescrRegime',
            layout: 'form',
            items: [{
                name: 'accessType', // Режим доступа
                value: null,
                xtype: 'hidden'
            }, {
                name: 'EvnPrescr_id', // Идентификатор назначения
                value: null,
                xtype: 'hidden'
            }, {
                name: 'EvnPrescr_pid', // Идентификатор события
                value: null,
                xtype: 'hidden'
            }, {
                name: 'PersonEvn_id', // Идентификатор состояния человека
                value: null,
                xtype: 'hidden'
            }, {
                name: 'Server_id', // Идентификатор сервера
                value: null,
                xtype: 'hidden'
            }, {
                allowBlank: false,
                comboSubject: 'PrescriptionRegimeType',
                fieldLabel: lang['tip_rejima'],
                typeCode: 'int',
                hiddenName: 'PrescriptionRegimeType_id',
				tabIndex:TABINDEX_PEREW+1,
                listeners: {
                    render: function(combo) {
                        combo.getStore().load();
                    }
                },
                listWidth: 600,
                width: 340,
                xtype: 'swcommonsprcombo'
            }, {
                layout:'column',
                border:false,
                items:[{
                    layout:'form',
                    border:false,
                    items:[{
                        allowBlank: false,
                        fieldLabel: lang['nachat'],
                        name: 'EvnPrescr_setDate',
                        format: 'd.m.Y',
						tabIndex:TABINDEX_PEREW+2,
                        plugins: [
                            new Ext.ux.InputTextMask('99.99.9999', false)
                        ],
                        width: 100,
                        xtype: 'swdatefield'
                    }]
                }, {
                    layout:'form',
                    border:false,
                    labelWidth: 100,
                    items:[{
                        allowDecimals: false,
                        allowNegative: false,
                        fieldLabel: lang['prodoljat'],
                        minValue: 1,
                        maxValue: 999,
						tabIndex:TABINDEX_PEREW+3,
                        name: 'EvnPrescr_dayNum',
	                    maxLength:3,
                        width: 50,
                        xtype: 'numberfield'
                    }]
                },{
                    border: false,
                    layout: 'form',
                    items: [{
                        style: 'padding: 0; margin: 0; padding-left: 5px; padding-top: 3px; font-size: 9pt;',
                        width: 35,
                        html: lang['dney']
                    }]
                }]
            }, {
                fieldLabel: lang['kommentariy'],
                height: 70,
				tabIndex:TABINDEX_PEREW+4,
                name: 'EvnPrescr_Descr',
                width: 340,
                xtype: 'textarea'
            }]
        });

        Ext.apply(this, {
            buttons: [{
                    handler: function() {
                        thas.doSave();
                    },
					tabIndex:TABINDEX_PEREW+5,
                    iconCls: 'save16',
                    text: lang['naznachit']
                   
                }, {
                text: '-'
            },{
                handler: function() {
                    thas.hide();
                },
				tabIndex:TABINDEX_PEREW+6,
                iconCls: 'cancel16',
                text: BTN_FRMCANCEL,
				onTabAction: function () {
					thas.FormPanel.getForm().findField('PrescriptionRegimeType_id').focus(true, 250);
				}
                /*}, {
                 handler: function() {
                 thas.doSave({signature: true});
                 },
                 iconCls: 'signature16',
                 text: BTN_FRMSIGN*/
            }
                //HelpButton(this, -1),
                ],
            items: [
                this.FormPanel
            ],
            layout: 'form'
        });

        sw.Promed.swPolkaEvnPrescrRegimeEditWindow.superclass.initComponent.apply(this, arguments);
    },
    show: function() {
        sw.Promed.swPolkaEvnPrescrRegimeEditWindow.superclass.show.apply(this, arguments);
        var thas = this;
        this.center();

        var base_form = this.FormPanel.getForm();
        base_form.reset();

        this.parentEvnClass_SysNick = null;
        this.action = 'add';
        this.callback = Ext.emptyFn;
        this.formStatus = 'edit';
        this.onHide = Ext.emptyFn;

        if ( !arguments[0] || !arguments[0].formParams ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { thas.hide(); } );
            return false;
        }

        base_form.setValues(arguments[0].formParams);
		if(typeof arguments[0].begDate == 'string'){
			
		}
		else{
			base_form.findField('EvnPrescr_setDate').setValue(getGlobalOptions().date)
		}
        if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
            this.action = arguments[0].action;
        }

        if ( arguments[0].parentEvnClass_SysNick && typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
            this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
        }

        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }

        if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
            this.onHide = arguments[0].onHide;
        }

        this.getLoadMask(LOAD_WAIT).show();

        switch ( this.action ) {
            case 'add':
                this.getLoadMask().hide();
                this.setTitle(lang['naznachenie_rejima_dobavlenie']);
                this.setFieldsDisabled(false);
				if(arguments[0].formParams.PrescriptionRegimeType_id){
					 base_form.findField('PrescriptionRegimeType_id').disable();
				}
                base_form.clearInvalid();
                base_form.findField('PrescriptionRegimeType_id').focus(true, 250);
                break;

            case 'edit':
            case 'view':
                base_form.load({
                    failure: function() {
                        thas.getLoadMask().hide();
                        sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { thas.hide(); } );
                    },
                    params: {
                        'EvnPrescr_id': base_form.findField('EvnPrescr_id').getValue()
                        ,'parentEvnClass_SysNick': this.parentEvnClass_SysNick
                    },
                    success: function() {
                        thas.getLoadMask().hide();
                        base_form.clearInvalid();
                        if ( base_form.findField('accessType').getValue() == 'view' ) {
                            thas.action = 'view';
                        }

                        if ( thas.action == 'edit' ) {
                            thas.setTitle(lang['naznachenie_rejima_redaktirovanie']);
                            thas.setFieldsDisabled(false);
                        }
                        else {
                            thas.setTitle(lang['naznachenie_rejima_prosmotr']);
                            thas.setFieldsDisabled(true);
                        }
                        base_form.findField('PrescriptionRegimeType_id').focus(true, 250);
                    },
                    url: '/?c=EvnPrescr&m=loadEvnPrescrRegimeEditForm'
                });
                break;

            default:
                this.getLoadMask().hide();
                this.hide();
                break;
        }
        return true;
    }
});