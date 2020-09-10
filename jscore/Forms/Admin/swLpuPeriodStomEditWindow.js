/**
 * swLpuPeriodStomEditWindow - окно редактирования/добавления периода обслуживания стомат. вызовов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2016 Swan Ltd.
 * @version      04.2016
 */

sw.Promed.swLpuPeriodStomEditWindow = Ext.extend(sw.Promed.BaseForm,
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
        id: 'LpuPeriodStomEditWindow',
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
            var form = this.findById('LpuPeriodStomEditForm');
            if ( !form.getForm().isValid() )
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

            this.submit();
            return true;
        },
        submit: function()
        {
            var form = this.findById('LpuPeriodStomEditForm');
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
                            if (action.result.Error_Code && action.result.Error_Code!=5555)
                            {
                                Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Msg);
                            }
                        }
                    },
                    success: function(result_form, action)
                    {
                        loadMask.hide();
                        if (action.result)
                        {
                            if (action.result.LpuPeriodStom_id)
                            {
                                current_window.hide();
                                Ext.getCmp('LpuPassportEditWindow').findById('LPEW_LpuPeriodStomGrid').loadData();
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
        },
        enableEdit: function(enable)
        {
            var form = this;
            if (enable)
            {
                var form = this.findById('LpuPeriodStomEditForm');
                form.getForm().findField('LpuPeriodStom_begDate').enable();
                form.getForm().findField('LpuPeriodStom_endDate').enable();
                this.buttons[0].enable();
            }
            else
            {
                var form = this.findById('LpuPeriodStomEditForm');
                form.getForm().findField('LpuPeriodStom_begDate').disable();
                form.getForm().findField('LpuPeriodStom_endDate').disable();
                this.buttons[0].disable();
            }
        },
        show: function()
        {
            sw.Promed.swLpuPeriodStomEditWindow.superclass.show.apply(this, arguments);
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
            this.findById('LpuPeriodStomEditForm').getForm().reset();
            this.callback = Ext.emptyFn;
            this.onHide = Ext.emptyFn;


            if (arguments[0].LpuPeriodStom_id)
                this.LpuPeriodStom_id = arguments[0].LpuPeriodStom_id;
            else
                this.LpuPeriodStom_id = null;

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
                if ( ( this.LpuPeriodStom_id ) && ( this.LpuPeriodStom_id > 0 ) )
                    this.action = "edit";
                else
                    this.action = "add";
            }

            var form = this.findById('LpuPeriodStomEditForm');

            switch (this.action)
            {
                case 'add':
                    this.setTitle(lang['period_obslugivania_dobavlenie']);
                    form.getForm().setValues(arguments[0]);
                    this.enableEdit(true);
                    form.getForm().clearInvalid();
                    form.getForm().findField('LpuPeriodStom_begDate').setValue(new Date());
                    break;
                case 'edit':
                    this.setTitle(lang['period_obslugivania_redaktirovanie']);
                    form.getForm().setValues(arguments[0].owner.getGrid().getSelectionModel().getSelected().data);
                    this.enableEdit(true);
                    break;
                case 'view':
                    this.setTitle(lang['period_obslugivania_prosmotr']);
                    form.getForm().setValues(arguments[0].owner.getGrid().getSelectionModel().getSelected().data);
                    this.enableEdit(false);
                    break;
            }

            if ( this.action != 'view' ) {
				var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет проверка действующих лицензий МО..."});
				loadMask.show();
				Ext.Ajax.request({
					url: '/?c=LpuPassport&m=checkLpuStomLicenceDates',
					failure: function(){
						loadMask.hide();
						Ext.Msg.alert(lang['oshibka'],'Не удалось проверить действующие лицензии МО');
						this.hide();
					}.createDelegate(this),
					success: function(response){
						loadMask.hide();
						//log(response);
						var response_obj = Ext.util.JSON.decode(response.responseText);//log(response_obj);
						if(response_obj.length == 0){
							Ext.Msg.alert(lang['oshibka'],'Нет действующих лицензий по стоматологическим профилям. Добавление периода невозможно.');
							this.hide();
						}
					}.createDelegate(this)
				});

                form.getForm().findField('LpuPeriodStom_begDate').focus(true, 100);
			}
            else {
                this.buttons[3].focus();
			}
        },
        initComponent: function()
        {
            // Форма с полями 
            var current_window = this;

            this.LpuPeriodStomEditForm = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'LpuPeriodStomEditForm',
                    labelAlign: 'right',
                    labelWidth: 180,
                    items:
                        [{
                            id: 'LPEW_LpuPeriodStom_id',
                            name: 'LpuPeriodStom_id',
                            value: 0,
                            xtype: 'hidden'
                        },{
                            fieldLabel: lang['data_nachala_perioda'],
                            allowBlank: false,
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            name: 'LpuPeriodStom_begDate',
                            //tabIndex: TABINDEX_LPEEW + 5
                        },{
                            fieldLabel: lang['data_okonchaniya_perioda'],
                            xtype: 'swdatefield',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            format: 'd.m.Y',
                            name: 'LpuPeriodStom_endDate',
                            //tabIndex: TABINDEX_LPEEW + 5
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
                            {name: 'LpuPeriodStom_id'},
                            {name: 'LpuPeriodStom_begDate'},
                            {name: 'LpuPeriodStom_endDate'}
                        ]),
                    url: '/?c=LpuPassport&m=saveLpuPeriodStom'
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
                            //tabIndex: TABINDEX_LPEEW + 16,
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
                                //tabIndex: TABINDEX_LPEEW + 17,
                                text: BTN_FRMCANCEL
                            }],
                    items: [this.LpuPeriodStomEditForm]
                });
            sw.Promed.swLpuPeriodStomEditWindow.superclass.initComponent.apply(this, arguments);
        }
    });