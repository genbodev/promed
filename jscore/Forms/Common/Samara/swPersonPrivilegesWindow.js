/**  
* swPersonPrivilegesWindow - окно для добавления льгот
* 
*/
sw.Promed.swPersonPrivilegesWindow = Ext.extend(sw.Promed.BaseForm, {
    layout: 'fit',
    resizable: false,
    draggable: false,
    height: 'auto',
    width: 535,
    closeAction: 'hide',
    id: 'PersonPrivilegesWindow',
    onClose: Ext.emptyFn,
    objectName: 'swPersonPrivilegesWindow',
    objectSrc: '/jscore/Forms/Commons/swPersonPrivilegesWindow.js',
    listeners: {
        'hide': function () { this.onClose() }
    },

    title: 'Добавить льготу',
    
    closeAction: 'hide',
    modal: true,
    show: function (config) {
        var win = this;
        sw.Promed.swPersonPrivilegesWindow.superclass.show.apply(this, arguments);
        this.config = config;

        privilegeForm = this.findById('PersonPrivilegesForm').getForm();

        if (config.action === 'add') {
            privilegeForm.items.each(function (field) {
                field.setValue(null);
            });
        }

        privilegeForm.findField('Person_id').setValue(config.Person_id);
        if (config.action === 'edit') {

            privilegeForm.findField('PersonPrivilege_id').setValue(config.PersonPrivilege_id);
            win.getLoadMask('Выполняется загрузка...').show();
            Ext.Ajax.request({
                url: '/?c=Privilege&m=loadPrivilegeSamara',
                params: { PersonPrivilege_id: config.PersonPrivilege_id },
                callback: function (options, success, response) {
                    win.getLoadMask().hide();
                    if (success) {
                        var privilege = Ext.util.JSON.decode(response.responseText)[0];

                        privilegeForm.findField('PrivilegeType_id').setValue(privilege.PrivilegeType_id);
                        var index = privilegeForm.findField('PrivilegeType_id').getStore().findBy(function(rec) { return rec.get('PrivilegeType_id') == privilege.PrivilegeType_id; });
                        if (index !== -1) {
                            privilegeForm.findField('ReceptFinance_id').setValue(privilegeForm.findField('PrivilegeType_id').getStore().getAt(index).data.ReceptFinance_id);
                        }

                        privilegeForm.findField('Diag_id').setValue(privilege.Diag_id);
                        privilegeForm.findField('Diag_id').getStore().load({
                            params: {
                                where: 'where Diag_id = ' + privilege.Diag_id
                            },
                            callback: function () {
                                privilegeForm.findField('Diag_id').setValue(privilege.Diag_id);;
                            }
                        });

                        privilegeForm.findField('PersonPrivilege_Serie').setValue(privilege.PersonPrivilege_Serie);
                        privilegeForm.findField('PersonPrivilege_Number').setValue(privilege.PersonPrivilege_Number);
                        privilegeForm.findField('PersonPrivilege_begDate').setValue(privilege.PersonPrivilege_begDate);
                        privilegeForm.findField('PersonPrivilege_endDate').setValue(privilege.PersonPrivilege_endDate);
                        privilegeForm.findField('PersonPrivilege_IssuedBy').setValue(privilege.PersonPrivilege_IssuedBy);
                        privilegeForm.findField('PersonPrivilege_Group').setValue(privilege.PersonPrivilege_Group);
                    }
                }
            });
        }
    },
    initComponent: function () {
        var win = this;

        Ext.apply(this, {
            buttons: [{
                text: BTN_FRMSAVE,
                tabIndex: TABINDEX_PEF + 62,
                iconCls: 'save16',
                id: 'PEW_SaveButton',
                handler: function () {
                    //win.getLoadMask('Подождите, сохраняется льгота...').show();
                    var frm = Ext.getCmp('PersonPrivilegesForm');
                    myfrm = frm.getForm();
                    frm.getForm().submit({
                        url: '/?c=Privilege&m=savePrivilegeSamara',
                        success: function () {
                            win.getLoadMask().hide();
                            win.hide();
                            if (this.config.callback) {
                                this.config.callback();
                            }
                        }.createDelegate(this)
                    });
                }.createDelegate(this)
            },{
                text: '-'
            }, {
                text: BTN_FRMCANCEL,
                tabIndex: TABINDEX_PEF + 65,
                iconCls: 'cancel16',
                handler: this.hide.createDelegate(this, [])
            }],
            items: [
				new Ext.form.FormPanel({
				    id: 'PersonPrivilegesForm',
				    autoHeight: true,
				    frame: true,
				    labelAlign: 'right',
				    items: [{
				        allowBlank: false,
				        codeField: 'ReceptFinance_Code',
				        displayField: 'ReceptFinance_Name',
				        editable: false,
				        fieldLabel: 'Финансирование',
				        labelWidth: 160,
				        hiddenName: 'ReceptFinance_id',
				        id: 'EUEF_ReceptFinanceCombo',
				        loadingText: 'Идет загрузка...',
				        store: new Ext.data.Store({
				            autoLoad: true,
				            reader: new Ext.data.JsonReader({
				                id: 'ReceptFinance_id'
				            }, [
                                { name: 'ReceptFinance_Code', mapping: 'ReceptFinance_Code' },
                                { name: 'ReceptFinance_id', mapping: 'ReceptFinance_id' },
                                { name: 'ReceptFinance_Name', mapping: 'ReceptFinance_Name' }
				            ]),
				            url: '/?c=Privilege&m=loadReceptFinances'
				        }),
				        tabIndex: TABINDEX_PEF + 29,
				        tpl: new Ext.XTemplate(
                            '<tpl for="."><div class="x-combo-list-item">',
                            '<table style="border: 0;"><tr><td>{ReceptFinance_Name}</td></tr></table>',
                            '</div></tpl>'
                        ),
				        validateOnBlur: true,
				        valueField: 'ReceptFinance_id',
				        width: 400,
				        listeners: {
				            'change': function (combo, newValue, oldValue) {
				                var EUEF_PrivilegeTypeCombo = Ext.getCmp('EUEF_PrivilegeTypeCombo');
				                var store = EUEF_PrivilegeTypeCombo.getStore();
				                if (!store.isFiltered())
				                    setTimeout(function () { store.filter('ReceptFinance_id', newValue); }, 100); // таймаут поставил потому что первый раз фильтр не применяется
				                else
				                    store.filter('ReceptFinance_id', newValue);
				            }
				        },
				        xtype: 'swbaselocalcombo'
				    }, {
				        allowBlank: false,
				        codeField: 'PrivilegeType_Code',
				        displayField: 'PrivilegeType_Name',
				        editable: false,
				        fieldLabel: 'Льгота',
				        labelWidth: 160,
				        hiddenName: 'PrivilegeType_id',
				        id: 'EUEF_PrivilegeTypeCombo',
				        loadingText: 'Идет загрузка...',
						listeners: {
							'change': function (combo, newValue, oldValue){
								var record = combo.getStore().getById(newValue);
								switch (record.get('PrivilegeType_Code'))
								{
									case 0:
									case 631670:
										var base_form = this.findById('PersonPrivilegesForm').getForm();
										base_form.findField('PersonPrivilege_Serie').setAllowBlank(true);
										base_form.findField('PersonPrivilege_Number').setAllowBlank(true);
										break;
									default:
										var base_form = this.findById('PersonPrivilegesForm').getForm();
										base_form.findField('PersonPrivilege_Serie').setAllowBlank(false);
										base_form.findField('PersonPrivilege_Number').setAllowBlank(false);
										break;
								}								
							}.createDelegate(this)
						},
				        store: new Ext.data.Store({
				            autoLoad: true,
				            reader: new Ext.data.JsonReader({
				                id: 'PrivilegeType_id'
				            }, [
                                { name: 'PrivilegeType_Code', mapping: 'PrivilegeType_Code' },
                                { name: 'PrivilegeType_id', mapping: 'PrivilegeType_id' },
                                { name: 'PrivilegeType_Name', mapping: 'PrivilegeType_Name' },
                                { name: 'ReceptDiscount_id', mapping: 'ReceptDiscount_id' },
                                { name: 'ReceptFinance_id', mapping: 'ReceptFinance_id' }
				            ]),
				            url: '/?c=Privilege&m=loadPrivilegeTypes'
				        }),
				        tabIndex: TABINDEX_PEF + 30,
				        tpl: new Ext.XTemplate(
                            '<tpl for="."><div class="x-combo-list-item">',
                            '<table style="border: 0;"><tr><td style="width: 25px;">{PrivilegeType_Code}</td><td>{PrivilegeType_Name}</td></tr></table>',
                            '</div></tpl>'
                        ),
				        validateOnBlur: true,
				        valueField: 'PrivilegeType_id',
				        width: 400,
				        xtype: 'swbaselocalcombo'
				    }, {
				        allowBlank: true,
				        fieldLabel: 'Льгота по заболеванию',
				        labelWidth: 160,
				        hiddenName: 'Diag_id',
				        tabIndex: TABINDEX_PEF + 31,
				        width: 400,
				        xtype: 'swdiagcombo'
				    }, {
                        layout: 'column',
				        items: [{
				            layout: 'form',
				            items: [{
				                allowBlank: false,
				                fieldLabel: 'Серия',
				                name: 'PersonPrivilege_Serie',
				                tabIndex: TABINDEX_PEF + 32,
				                width: 145,
				                xtype: 'textfield'
				            }]
				        }, {
				            layout: 'form',
				            labelWidth: 105,
				            items: [{
				                allowBlank: false,
				                xtype: 'textfield',
				                width: 145,
				                fieldLabel: 'Номер',
				                name: 'PersonPrivilege_Number',
				                tabIndex: TABINDEX_PEF + 33
				            }]
				        }]
				    }, {
				        layout: 'column',
				        items: [
				            {
				                layout: 'form',
				                items: [{
				                    allowBlank: true,
				                    xtype: 'swdatefield',
				                    width: 145,
				                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				                    format: 'd.m.Y',
				                    fieldLabel: 'Дата начала',
				                    name: 'PersonPrivilege_begDate',
				                    tabIndex: TABINDEX_PEF + 34
				                }]
				            }, {
				                layout: 'form',
				                labelWidth: 105,
				                items: [{
				                    allowBlank: true,
				                    xtype: 'swdatefield',
				                    width: 145,
				                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				                    format: 'd.m.Y',
				                    fieldLabel: 'Дата окончания',
				                    name: 'PersonPrivilege_endDate',
				                    tabIndex: TABINDEX_PEF + 35
				                }]
				            }
				        ]
				    }, {
				        allowBlank: true,
				        xtype: 'textfield',
				        width: 400,
				        fieldLabel: 'Кем выдан',
				        name: 'PersonPrivilege_IssuedBy',
				        tabIndex: TABINDEX_PEF + 36
				    }, {
				        allowBlank: true,
				        xtype: 'textfield',
				        width: 400,
				        fieldLabel: 'Группа',
				        name: 'PersonPrivilege_Group',
				        tabIndex: TABINDEX_PEF + 36
				    }, {
				        xtype: 'hidden',
				        name: 'Person_id'
				    }, {
				        xtype: 'hidden',
				        name: 'PersonPrivilege_id',
				        hiddenName: 'PersonPrivilege_id',
				        id: 'PersonPrivilege_id'
				    }
				    ]
				})
            ]
        });

        sw.Promed.swPersonPrivilegesWindow.superclass.initComponent.apply(this, arguments);
    }
});
