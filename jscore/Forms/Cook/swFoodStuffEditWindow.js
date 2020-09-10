/**
 * swFoodStuffEditWindow - окно редактирования продукта питания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */

sw.Promed.swFoodStuffEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        id: 'swFoodStuffEditWindow',
        autoHeight: true,
        width: 450,
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        maximizable: false,
        modal: true,
        objectSrc: '/jscore/Forms/Cook/swFoodStuffEditWindow.js',
        title: lang['produktyi_pitaniya'],

        doSave: function()
        {
            var wnd = this;
            var form = wnd.FoodStuffEditForm;

            var base_form = form.getForm();

            if ( !base_form.isValid() )
            {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    fn: function() {
                        form.getFirstInvalidEl().focus(false);
                    },
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
                return false;
            }

            var params = new Object();

            wnd.getLoadMask("Подождите, идет сохранение...").show();

            base_form.submit({
                failure: function(result_form, action) {
                    wnd.getLoadMask().hide()
                },
                params: params,
                success: function(result_form, action) {
                    wnd.getLoadMask().hide();
                    if (action.result)
                    {
                        action.result.FoodStuffData = base_form.getValues();
                        wnd.callback(action.result);
                        wnd.hide();
                    }
                    else
                    {
                        Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                    }
                }
            });
        },

        show: function()
        {
            sw.Promed.swFoodStuffEditWindow.superclass.show.apply(this, arguments);

            this.restore();
            this.center();

            var base_form = this.FoodStuffEditForm.getForm();
            base_form.reset();

            this.action = null;
            this.callback = Ext.emptyFn;
            this.onCancelActionFlag = true;
            this.formStatus = 'edit';
            this.onHide = Ext.emptyFn;

            if ( !arguments[0] || !arguments[0].formParams ) {
                sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
                return false;
            }

            base_form.setValues(arguments[0].formParams);

            if ( arguments[0].action ) {
                this.action = arguments[0].action;
            }

            if ( arguments[0].callback ) {
                this.callback = arguments[0].callback;
            }

            if ( arguments[0].onHide ) {
                this.onHide = arguments[0].onHide;
            }

            var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
            loadMask.show();

            switch ( this.action ) {
                case 'add':
                    this.setTitle(lang['produktyi_pitaniya_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();

                    base_form.clearInvalid();

                    base_form.findField('FoodStuff_Code').focus(true, 250);
                    break;

                case 'edit':
                case 'view':
                    var food_stuff_id = base_form.findField('FoodStuff_id').getValue();

                    if ( !food_stuff_id ) {
                        loadMask.hide();
                        this.hide();
                        return false;
                    }

                    base_form.load({
                        failure: function() {
                            loadMask.hide();
                            sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
                        }.createDelegate(this),
                        params: {
                            'FoodStuff_id': food_stuff_id
                        },
                        success: function() {


                            if ( base_form.findField('accessType').getValue() == 'view' ) {
                                this.action = 'view';
                            } else {
                                this.action = 'edit';
                            }

                            if ( this.action == 'edit' ) {
                                this.setTitle(lang['produktyi_pitaniya_redaktirovanie']);
                                this.enableEdit(true);
                            }
                            else {
                                this.setTitle(lang['produktyi_pitaniya_prosmotr']);
                                this.enableEdit(false);
                            }

                            loadMask.hide();

                            base_form.clearInvalid();

                            if ( this.action == 'edit' ) {
                                base_form.findField('FoodStuff_Code').focus(true, 250);
                            }
                            else {
                                this.buttons[this.buttons.length - 1].focus();
                            }
                        }.createDelegate(this),
                        url: '/?c=FoodStuff&m=loadFoodStuffEditForm'
                    });
                    break;

                default:
                    loadMask.hide();
                    this.hide();
                    break;
            }
        },

        initComponent: function()
        {
            this.FoodStuffEditForm = new Ext.form.FormPanel({
                autoScroll: true,
                bodyBorder: false,
                bodyStyle: 'padding: 5px 20px 0',
                border: false,
                frame: true,
                id: 'FoodStuffEditWindow',
                labelAlign: 'right',
                labelWidth: 150,
                region: 'center',
                url: '/?c=FoodStuff&m=saveFoodStuff',

                reader: new Ext.data.JsonReader({
                    success: Ext.emptyFn
                },  [
                    { name: 'accessType' },
                    { name: 'FoodStuff_id' },
                    { name: 'FoodStuffType_id' },
                    { name: 'Okei_id' },
                    { name: 'FoodStuff_Code' },
                    { name: 'FoodStuff_Name' },
                    { name: 'FoodStuff_Descr' },
                    { name: 'FoodStuff_StorCond' },
                    { name: 'FoodStuff_Protein' },
                    { name: 'FoodStuff_Fat' },
                    { name: 'FoodStuff_Carbohyd' },
                    { name: 'FoodStuff_Caloric' },
                    { name: 'pmUser_Name' },
                    { name: 'Server_id' }
                ]),

                items: [{
                    name: 'accessType',
                    value: '',
                    xtype: 'hidden'
                }, {
                    name: 'FoodStuff_id',
                    value: -1,
                    xtype: 'hidden'
                }, {
                    name: 'FoodStuff_Code',
                    fieldLabel: lang['kod'],
                    xtype: 'textfield',
                    allowBlank: false,
                    width: 200
                }, {
                    name: 'FoodStuff_Name',
                    fieldLabel: lang['naimenovanie'],
                    xtype: 'textfield',
                    allowBlank: false,
                    width: 200
                }, {
                    name: 'FoodStuff_Descr',
                    fieldLabel: lang['opisanie'],
                    xtype: 'textfield',
                    width: 200
                }, {
                    name: 'FoodStuff_StorCond',
                    fieldLabel: lang['usloviya_hraneniya'],
                    xtype: 'textfield',
                    width: 200
                }, {
                    hiddenName: 'Okei_id',
                    fieldLabel: lang['bazovaya_edinitsa_izmereniya'],
                    xtype: 'swokeicombo',
                    allowBlank: false,
                    width: 200
                }, {
                    name: 'FoodStuff_Protein',
                    fieldLabel: lang['soderjanie_belkov_v_100_gr_produkta'],
                    xtype: 'numberfield',
                    width: 200
                }, {
                    name: 'FoodStuff_Fat',
                    fieldLabel: lang['soderjanie_jirov_v_100_gr_produkta'],
                    xtype: 'numberfield',
                    width: 200
                }, {
                    name: 'FoodStuff_Carbohyd',
                    fieldLabel: lang['soderjanie_uglevodov_v_100_gr_produkta'],
                    xtype: 'numberfield',
                    width: 200
                }, {
                    name: 'FoodStuff_Caloric',
                    fieldLabel: lang['energeticheskaya_tsennost_kkal_100g'],
                    xtype: 'numberfield',
                    width: 200
                }
                ]
            });

            Ext.apply(this,
                {
                    items:
                        [
                            this.FoodStuffEditForm
                        ],
                    buttons: [{
                        handler: function() {
                            this.doSave(false);
                        }.createDelegate(this),
                        iconCls: 'save16',
                        id: 'FSEW_SaveButton',
                        text: BTN_FRMSAVE
                    },
                    '-',
                    HelpButton(this, -1),
                    {
                        handler: function() {
                            this.hide();
                        }.createDelegate(this),
                        iconCls: 'cancel16',
                        id: 'FSEW_CancelButton',
                        tabIndex: 2409,
                        text: BTN_FRMCANCEL
                    }]
                });
            sw.Promed.swFoodStuffEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });
