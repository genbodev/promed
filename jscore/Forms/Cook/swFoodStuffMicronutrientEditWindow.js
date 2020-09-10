/**
 * swFoodStuffMicronutrientEditWindow - окно редактирования микронутриента продукта питания
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

sw.Promed.swFoodStuffMicronutrientEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        id: 'swFoodStuffMicronutrientEditWindow',
        autoHeight: true,
        width: 500,
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        modal: true,
        objectSrc: '/jscore/Forms/Cook/swFoodStuffMicronutrientEditWindow.js',

        doSave: function()
        {
            var wnd = this;
            var form = wnd.FoodStuffMicronutrientEditForm;

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
                    wnd.getLoadMask().hide()

                    if (action.result)
                    {
                        action.result.FoodStuffMicronutrientData = base_form.getValues();
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
            sw.Promed.swFoodStuffMicronutrientEditWindow.superclass.show.apply(this, arguments);

            this.restore();
            this.center();

            var base_form = this.FoodStuffMicronutrientEditForm.getForm();
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
                    this.setTitle(lang['mikronutrient_dobavlenie']);
                    this.enableEdit(true);
                    loadMask.hide();

                    base_form.clearInvalid();

                    //base_form.findField('Micronutrient_Name').focus(true, 250);

                    break;

                case 'edit':
                case 'view':
                    var food_stuff_micronutrient_id = base_form.findField('FoodStuffMicronutrient_id').getValue();

                    if ( !food_stuff_micronutrient_id ) {
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
                            'FoodStuffMicronutrient_id': food_stuff_micronutrient_id
                        },
                        success: function() {


                            if ( base_form.findField('accessType').getValue() == 'view' ) {
                                this.action = 'view';
                            } else {
                                this.action = 'edit';
                            }

                            if ( this.action == 'edit' ) {
                                this.setTitle(lang['mikronutrient_redaktirovanie']);
                                this.enableEdit(true);
                            }
                            else {
                                this.setTitle(lang['mikronutrient_prosmotr']);
                                this.enableEdit(false);
                            }

                            loadMask.hide();

                            base_form.clearInvalid();

                            if ( this.action == 'edit' ) {
                                //base_form.findField('Micronutrient_Name').focus(true, 250);
                            }
                            else {
                                this.buttons[this.buttons.length - 1].focus();
                            }
                        }.createDelegate(this),
                        url: '/?c=FoodStuff&m=loadFoodStuffMicronutrientEditForm'
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
            this.FoodStuffMicronutrientEditForm = new Ext.form.FormPanel({
                autoScroll: true,
                bodyBorder: false,
                bodyStyle: 'padding: 5px 5px 0',
                border: false,
                frame: true,
                id: 'FoodStuffEditWindow',
                labelAlign: 'right',
                labelWidth: 170,
                region: 'center',
                url: '/?c=FoodStuff&m=saveFoodStuffMicronutrient',

                reader: new Ext.data.JsonReader({
                    success: Ext.emptyFn
                },  [
                    { name: 'accessType' },
                    { name: 'FoodStuff_id' },
                    { name: 'FoodStuffMicronutrient_id' },
                    { name: 'Micronutrient_id' },
                    { name: 'Micronutrient_Name' },
                    { name: 'FoodStuffMicronutrient_Content' },
                    { name: 'pmUser_Name' }
                ]),

                items: [{
                    name: 'accessType',
                    value: '',
                    xtype: 'hidden'
                }, {
                    name: 'FoodStuff_id',
                    xtype: 'hidden'
                }, {
                    name: 'FoodStuffMicronutrient_id',
                    value: -1,
                    xtype: 'hidden'
                }, {
                    hiddenName: 'Micronutrient_id',
                    xtype: 'swmicronutrientcombo',
                    fieldLabel: lang['mikronutrient'],
                    allowBlank: false
                }, {
                    name: 'FoodStuffMicronutrient_Content',
                    xtype: 'textfield',
                    fieldLabel: lang['kolitsestvennoe_soderjanie_v_100_g'],
                    allowBlank: false
                }
                ]
            });

            Ext.apply(this,
                {
                    items:
                        [
                            this.FoodStuffMicronutrientEditForm
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
            sw.Promed.swFoodStuffMicronutrientEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });
