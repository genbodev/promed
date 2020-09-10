/**
 * swServiceContract - форма добавления, редактирования и просмотра услуг, действующих в рамках договора.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 */

sw.Promed.swServiceContract = Ext.extend(sw.Promed.BaseForm,
    {
        action: null,
        autoHeight: true,
        buttonAlign: 'left',
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        split: true,
        width: 600,
        layout: 'form',
        id: 'ServiceContract',
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
            var params = {};
            var flag = true;
            var base_form = this.ServiceContract.getForm();
            var uslugaComplexCombo = base_form.findField('UslugaComplex_id');
            var uslugaCategoryCombo = base_form.findField('UslugaCategory_id');
            var uslugaKolvo = base_form.findField('UslugaComplexLink_Kolvo');
            var uslugaComplexLink = base_form.findField('LpuDispContractUslugaComplexLink_id');

            params.UslugaComplex_id = uslugaComplexCombo.getValue();
            if(!params.UslugaComplex_id){
                sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        fn: function()
                        {
                            uslugaComplexCombo.focus(true);
                        },
                        icon: Ext.Msg.WARNING,
                        msg: 'Поле <b>Услуга </b> обязательное для заполнения.',
                        title: ERR_INVFIELDS_TIT
                    });
                return false;
            }
            params.UslugaComplex_Name = uslugaComplexCombo.getFieldValue('UslugaComplex_Name');

            for (var i = 0; i < this.arrServiceContract.length; i++) {
                var arrayElem = this.arrServiceContract[i];
                
                if(arrayElem.UslugaComplex_id && arrayElem.UslugaComplex_id == params.UslugaComplex_id){
                    if( this.action != 'edit' || (this.action == 'edit' && this.index != i)){
                        flag = false;
                        break;
                    }
                }
            }

            if(!flag){
                sw.swMsg.show(
                    {
                        buttons: Ext.Msg.OK,
                        fn: function()
                        {
                            uslugaComplexCombo.focus(true);
                        },
                        icon: Ext.Msg.WARNING,
                        msg: 'Услуга <b>'+params.UslugaComplex_Name+'</b> уже добавлена в договор.',
                        title: ERR_INVFIELDS_TIT
                    });
                return false;
            }
            params.UslugaCategory_id = uslugaCategoryCombo.getValue();
            params.UslugaCategory_Name = uslugaCategoryCombo.getFieldValue('UslugaCategory_Name');
            params.UslugaComplex_Code = uslugaComplexCombo.getFieldValue('UslugaComplex_Code');
            params.LpuDispContractUslugaComplexLink_Kolvo = uslugaKolvo.getValue();
            params.LpuDispContractUslugaComplexLink_id = uslugaComplexLink.getValue();
            params.index = this.index;

            this.callback(params);
            this.hide();
        },
        show: function()
        {
            sw.Promed.swServiceContract.superclass.show.apply(this, arguments);
            var current_window = this;
            var base_form = this.findById('ServiceContractForm').getForm();
            var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
            loadMask.show();
            if (!arguments[0])
            {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.ERROR,
                    msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
                    title: langs('Ошибка'),
                    fn: function() {
                        this.hide();
                    }
                });
            }

            this.focus();
            base_form.reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;
            this.index = null;
            
            this.arrServiceContract = [];

            if (arguments[0].callback)
            {
                this.callback = arguments[0].callback;
            }

            if (arguments[0].index >= 0)
            {
                this.index = arguments[0].index;
            }

            if (arguments[0].action)
            {
                this.action = arguments[0].action;
            }else{
                this.action = 'add';
            }

            if (arguments[0].arrServiceContract)
            {
                this.arrServiceContract = arguments[0].arrServiceContract;
            }

            this.LpuDispContract_id = arguments[0].LpuDispContract_id || null;

            var uslugaCombo = base_form.findField('UslugaComplex_id');
            uslugaCombo.getStore().clearFilter();
            delete uslugaCombo.getStore().baseParams.UslugaCategory_id;

            var uslugaCategoryCombo = base_form.findField('UslugaCategory_id');
            var complexLinkKolvo  = base_form.findField('UslugaComplexLink_Kolvo');
            var uslugaComplexLink = base_form.findField('LpuDispContractUslugaComplexLink_id');

            switch (this.action)
            {
                case 'add':
                    this.setTitle(langs('Услуга договора: Добавление'));
                    this.enableEdit(true);
                    base_form.clearInvalid();
                    loadMask.hide();
                    break;
                case 'edit':
                    this.setTitle(langs('Услуга договора: Редактирование'));
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(langs('Услуга договора: Просмотр'));
                    this.enableEdit(false);
                    break;
            }

            if (this.action != 'add')
            {
                var UslugaCategory_id = arguments[0].UslugaCategory_id || null;
                var UslugaComplex_id = arguments[0].UslugaComplex_id || null;
                var UslugaComplexLink_Kolvo = arguments[0].UslugaComplexLink_Kolvo || null;
                var LpuDispContractUslugaComplexLink_id = arguments[0].LpuDispContractUslugaComplexLink_id || 0;

                complexLinkKolvo.setValue(UslugaComplexLink_Kolvo);
                uslugaCombo.setValue(UslugaComplex_id);
                uslugaComplexLink.setValue(LpuDispContractUslugaComplexLink_id);

                if(UslugaCategory_id){
                    uslugaCategoryCombo.setValue(UslugaCategory_id);
                    uslugaCombo.getStore().baseParams.UslugaCategory_id = UslugaCategory_id;
                    uslugaCombo.getStore().reload({
                        callback: function(){
                            var base_form =Ext.getCmp('ServiceContractForm').getForm();
                            var combo = base_form.findField('UslugaComplex_id');
                            combo.setValue(combo.getValue());
                            loadMask.hide();
                        },
                        failure: function(){
                            loadMask.hide();
                        }
                    });
                }else{
                    loadMask.hide();
                }             
            }
        },
        initComponent: function()
        {
			var win = this;
            this.ServiceContract = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'ServiceContractForm',
                    labelAlign: 'right',
                    labelWidth: 80,
                    items:[
                        {
                            name: 'LpuDispContractUslugaComplexLink_id',
                            value: 0,
                            xtype: 'hidden'
                        },
                        {
                            name: 'UslugaCategory_id',
                            comboSubject: 'UslugaCategory',
                            fieldLabel: langs('Категория'),
                            anchor: '50%',
                            listeners: {
                                'change': function(combo, newValue, oldValue) {
                                    var base_form = this.ServiceContract.getForm();

                                    var uslugaCombo = base_form.findField('UslugaComplex_id');
                                    uslugaCombo.clearValue();

                                    if (!Ext.isEmpty(newValue)) {
                                        uslugaCombo.getStore().filterBy(function(record) {
                                            if (record.get('UslugaCategory_id') == newValue) {
                                                return true;
                                            } else {
                                                return false;
                                            }
                                        });
                                        uslugaCombo.getStore().baseParams.UslugaCategory_id = newValue;
                                        uslugaCombo.getStore().reload();
                                    }
                                }.createDelegate(this)
                            },
                            xtype: 'swuslugacategorycombo'
                        },{
                            fieldLabel: langs('Услуга'),
                            hiddenName: 'UslugaComplex_id',
                            //id: 'UslugaComplex_id',
                            allowBlank: false,
                            listWidth: 760,
                            listeners: {
                                'change': function(combo, newValue, oldValue) {
                                    //                                    
                                }.createDelegate(this),
                                'select': function(combo, record, index) {
                                    //
                                }.createDelegate(this)
                            },
                            //width: 400,
                            anchor: '90%',
                            xtype: 'swuslugacomplexallcombo'
                        },{
                            fieldLabel: langs('Количество'),
                            xtype: 'textfield',
                            anchor: '50%',
                            name: 'UslugaComplexLink_Kolvo',
                            maskRe: /[0-9]/
                        }                        
					],                
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
                    items: [this.ServiceContract]
                });
            sw.Promed.swServiceContract.superclass.initComponent.apply(this, arguments);
        }
    });