/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 01.08.14
 * Time: 13:23
 * To change this template use File | Settings | File Templates.
 */

sw.Promed.swUserSessionsViewForm = Ext.extend(sw.Promed.BaseForm,{
    title:lang['spravochnik_organizatsiy'],
    layout: 'border',
    id: 'UserSessionsViewForm',
    maximized: true,
    maximizable: false,
    shim: false,
    buttonAlign : "right",
    buttons:[
        {
            text: BTN_FRMHELP,
            tabIndex: TABINDEX_USVF + 45,
            iconCls: 'help16',
            handler: function(button, event)
            {
                ShowHelp(this.ownerCt.title);
            }
        },
        {
            text      : BTN_FRMCLOSE,
            tabIndex: TABINDEX_USVF + 50,
            tooltip   : lang['zakryit'],
            iconCls   : 'cancel16',
            onTabElement: 'USVF_Login_Range',
            handler   : function()
            {
                this.ownerCt.hide();
            }
        }
    ],
    returnFunc: function(owner) {},
    listeners:
    {
        hide: function()
        {
            this.returnFunc(this.owner, -1);
        }
    },

	interruptSession: function()
	{
		var grid = this.SessionGrid.getGrid();
		if (!grid.getSelectionModel().hasSelection()) {
			return false;
		}

		var records = grid.getSelectionModel().getSelections();
		var session_ids = [];

		for (var i=0; i<records.length; i++) {
			session_ids.push(records[i].get('Session_id'));
		}

		getWnd('swUserSessionsInterruptWindow').show({
			Session_ids: session_ids,
			callback: function(data) {
				Ext.Msg.alert('прерывание сессий', data);
			}
		});
	},

    show: function()
    {
        sw.Promed.swUserSessionsViewForm.superclass.show.apply(this, arguments);

		this.FilterPanel.getForm().reset();
		this.SessionGrid.getGrid().removeAll();

        this.mode = null;
		this.Org_id = null;
		if (arguments[0] && arguments[0].Org_id) {
			this.Org_id = arguments[0].Org_id;
		}

        this.setTitle(lang['jurnal_avtorizatsii_v_sisteme']);

        var loadMask = new Ext.LoadMask(Ext.get('UserSessionsViewForm'), {msg: LOAD_WAIT});
        loadMask.show();
        var form = this;

		form.SessionGrid.addActions({
			name:'action_isp',
			text: lang['deystviya'],
			iconCls: 'actions16',
			menu: new Ext.menu.Menu({
				items: [{
					id: 'USVF_InterruptSessions',
					text: lang['prervat_seans'],
					handler: function() {
						form.interruptSession();
					}
				}]
			})
		});

		if (form.findById('USVF_PMUserGroup').getStore().getCount() == 0) {
			form.findById('USVF_PMUserGroup').getStore().load();
		}

		if (form.Org_id) {
			form.findById('USVF_Org').getStore().load({
				params: {Org_id: form.Org_id},
				callback: function() {
					form.findById('USVF_Org').setValue(form.Org_id);
				}
			});
		}

        form.loadGridWithFilter(true);

        loadMask.hide();
    },
    loadGridWithFilter: function(clear)
    {
        var form = this;
        if (clear) {
			form.FilterPanel.getForm().reset();
			form.findById('USVF_Login_Range').setValue(getGlobalOptions().date + ' - ' + getGlobalOptions().date);
		}

		// Диапазон дат не должен превышать 7 дней
		if (Ext.isEmpty(form.findById('USVF_Login_Range').getRawValue())) {
			sw.swMsg.alert(langs('Ошибка'), langs('Поле "Дата входа" обязательно для заполнения'), function() {
				form.findById('USVF_Login_Range').focus(true, 250);
			});
			return false;
		}

		if (Ext.isEmpty(form.findById('USVF_Login_Range').getValue1()) || Ext.isEmpty(form.findById('USVF_Login_Range').getValue2())) {
			sw.swMsg.alert(langs('Ошибка'), langs('Неверно задан период в поле "Дата входа"'), function() {
				form.findById('USVF_Login_Range').focus(true, 250);
			});
			return false;
		}

		if (form.findById('USVF_Login_Range').getValue1().add(Date.DAY, 7) < form.findById('USVF_Login_Range').getValue2()) {
			sw.swMsg.alert(langs('Ошибка'), langs('Диапазон дат не должен превышать 7 дней'), function() {
				form.findById('USVF_Login_Range').focus(true, 250);
			});
			return false;
		}

        var Login_Range = this.findById('USVF_Login_Range').getRawValue(),
            Logout_Range = this.findById('USVF_Logout_Range').getRawValue(),
			IsMedPersonal = this.findById('USVF_IsMedPersonal').getValue(),
            PMUser_Name = this.findById('USVF_PMUser_Name').getValue(),
            PMUser_Login = this.findById('USVF_PMUser_Login').getValue(),
            IP = this.findById('USVF_IP').getValue(),
            AuthType_id = this.findById('USVF_AuthType_id').getValue(),
            Status = this.findById('USVF_Status').getValue(),
            onlyActive = this.findById('USVF_onlyActive').getValue(),
            Org_id = this.findById('USVF_Org').getValue(),
            PMUserGroup_Name = this.findById('USVF_PMUserGroup').getValue(),
            filters = {
                Login_Range: Login_Range,
                Logout_Range: Logout_Range,
				IsMedPersonal: IsMedPersonal,
                PMUser_Name: PMUser_Name,
				PMUser_Login: PMUser_Login,
                IP: IP,
                AuthType_id: AuthType_id,
                Status: Status,
                onlyActive: onlyActive,
				Org_id: Org_id,
				userOrg_id : this.Org_id,
				PMUserGroup_Name: PMUserGroup_Name,
                start: 0,
                limit: 100
            };
        if ( this.mode )
            filters.mode = this.mode;
        else
            filters.mode = null;
        form.SessionGrid.loadData({globalFilters: filters});
    },
    initComponent: function()
    {
        var form = this;
        this.FilterPanel = new Ext.FormPanel(
            {
                autoHeight: true,
                frame: true,
                region: 'north',
                border: false,
                items:  [
                    new Ext.form.FieldSet({
                        bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
                        border: true,
                        autoHeight: true,
                        colapsible: true,
                        colapsed: false,
                        layout: 'column',
                        title: lang['filtryi'],
                        labelWidth: 100,
                        labelAlign: 'right',
                        id: 'USVF_OrgFilterPanel',
                        items:[{
                            // Левая часть фильтров
                            layout: 'form',
                            border: false,
                            items:
                            [{
                            	allowBlank: false,
                                fieldLabel: lang['data_vhoda'],
                                id: 'USVF_Login_Range',
                                tabIndex: TABINDEX_USVF,
                                plugins: [
                                    new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
                                ],
                                width: 170,
                                xtype: 'daterangefield'
                            },{
                                fieldLabel: lang['data_vyihoda'],
                                id: 'USVF_Logout_Range',
                                tabIndex: TABINDEX_USVF + 5,
                                plugins: [
                                    new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
                                ],
                                width: 170,
                                xtype: 'daterangefield'
                            },{
                                fieldLabel: lang['vrach'],
                                id: 'USVF_IsMedPersonal',
                                tabIndex: TABINDEX_USVF + 5,
                                width: 170,
                                xtype: 'swyesnocombo'
                            }]
                        },{
                            // Средняя часть фильтров
                            layout: 'form',
                            border: false,
                            labelWidth: 120,
                            items:
                            [{
                                id: 'USVF_IP',
                                tabIndex: TABINDEX_USVF + 10,
                                width: 200,
                                disabled: false,
                                fieldLabel: lang['ip_polzovatelya'],
                                xtype: 'textfield'
                            },{
                                id: 'USVF_PMUser_Name',
                                tabIndex: TABINDEX_USVF + 15,
                                width: 200,
                                disabled: false,
                                fieldLabel: lang['polzovatel'],
                                xtype: 'textfield'
                            },{
                                id: 'USVF_PMUser_Login',
                                tabIndex: TABINDEX_USVF + 15,
                                width: 200,
                                disabled: false,
                                fieldLabel: lang['login'],
                                xtype: 'textfield'
                            }]
                        },{
                            layout: 'form',
                            border: false,
                            items:
                            [{
                                id: 'USVF_Org',
                                tabIndex: TABINDEX_USVF + 10,
                                width: 200,
                                disabled: (!getGlobalOptions().superadmin),
                                fieldLabel: lang['organizatsiya'],
								onTrigger1Click: function() {
									if(!this.disabled){
										var combo = this;
										getWnd('swOrgSearchWindow').show({
											object: 'org',
											onSelect: function(orgData) {
												if ( orgData.Org_id > 0 ) {
													combo.getStore().load({
														params: {
															Object:'Org',
															Org_id: orgData.Org_id,
															Org_Name:''
														},
														callback: function() {
															combo.setValue(orgData.Org_id);
															combo.focus(true, 500);
															combo.fireEvent('change', combo);
														}
													});
												}

												getWnd('swOrgSearchWindow').hide();
											},
											onClose: function() {combo.focus(true, 200)}
										});
									}
								},
                                xtype: 'sworgcombo'
                            },{
                                id: 'USVF_PMUserGroup',
                                tabIndex: TABINDEX_USVF + 15,
                                width: 200,
                                disabled: false,
                                fieldLabel: lang['gruppa'],
								valueField: 'Group_Name',
                                xtype: 'swusersgroupscombo'
                            }]
                        },{
                            layout: 'form',
                            border: false,
							labelWidth: 160,
                            items:
                            [{
                                border: false,
                                layout: 'form',
                                items:
                                [{
                                    xtype  : 'combo',
                                    store  : new Ext.data.SimpleStore({
                                        fields : ['id','value'],
                                        data   : [
                                            ['1' , lang['udachnyiy_vhod']],
                                            ['0' , lang['neudachnyiy_vhod']]
                                        ]
                                    }),
                                    id          : 'USVF_Status',
                                    tabIndex: TABINDEX_USVF + 20,
                                    fieldLabel  : lang['popyitka_podklyucheniya'],
                                    displayField: 'value',
                                    valueField  : 'id',
                                    triggerAction : 'all',
                                    mode        : 'local',
                                    editable    : false,
                                    width: 150
                                },{
                                    border: false,
                                    layout: 'form',
                                    items: [{
                                        xtype  : 'combo',
                                        store  : new Ext.data.SimpleStore({
                                            fields : ['id','value'],
                                            data   : [
                                                ['1' , langs('по логину/паролю')],
                                                ['2' , langs('по соцкарте')],
                                                ['3' , langs('через УЭК')],
                                                ['2' , langs('через ЭЦП')],
                                                ['5' , langs('через ЕСИА')]
                                            ]
                                        }),
                                        id          : 'USVF_AuthType_id',

                                        tabIndex: TABINDEX_USVF + 25,
                                        fieldLabel  : lang['tip_avtorizatsii'],
                                        displayField: 'value',
                                        valueField  : 'id',
                                        triggerAction : 'all',
                                        mode        : 'local',
                                        editable    : false,
                                        width: 150
                                    }]
                                },{
                                    layout: 'fit',
                                    border: false,
                                    labelWidth: 0,
                                    items: [{
                                        boxLabel: lang['otobrajat_tolko_aktiv_podklyucheniya'],
                                        tabIndex: TABINDEX_USVF + 30,
                                        labelSeparator: '',
                                        id: 'USVF_onlyActive',
                                        xtype: 'checkbox'
                                    }]
                                }]
                            }]
                        }],
                        buttons: [
                        {
                            text: BTN_FIND2,
                            tabIndex: TABINDEX_USVF + 35,
                            handler: function() {
                                form.loadGridWithFilter();
                            },
                            iconCls: 'search16'
                        },
                        {
                            text: BTN_RESETFILTER,
                            tabIndex: TABINDEX_USVF + 40,
                            handler: function() {
                                form.loadGridWithFilter(true);
                            },
                            iconCls: 'resetsearch16'
                        },
                        '-'
                        ]
                    })
                ],
                keys: [{
                    key: [
                        Ext.EventObject.ENTER
                    ],
                    fn: function(inp, e) {
                        e.stopEvent();

                        if ( e.browserEvent.stopPropagation )
                            e.browserEvent.stopPropagation();
                        else
                            e.browserEvent.cancelBubble = true;

                        if ( e.browserEvent.preventDefault )
                            e.browserEvent.preventDefault();
                        else
                            e.browserEvent.returnValue = false;

                        e.browserEvent.returnValue = false;
                        e.returnValue = false;

                        if (Ext.isIE)
                        {
                            e.browserEvent.keyCode = 0;
                            e.browserEvent.which = 0;
                        }

                        Ext.getCmp('UserSessionsViewForm').loadGridWithFilter();
                    },
                    stopEvent: true
                }]
            });

        // Организации
        this.SessionGrid = new sw.Promed.ViewFrame({
            id: 'SessionGridPanel',
            region: 'center',
            height: 303,
            paging: true,
            object: 'Session',
            //editformclassname: 'swOrgEditForm',
            dataUrl: '/?c=User&m=getUserSessions',
            toolbar: true,
            root: 'data',
            totalProperty: 'totalCount',
			selectionModel: 'multiselect',
            autoLoadData: false,
            stringfields: [
                // Поля для отображение в гриде
				{name: 'Status_id', type: 'int', hidden: true},
                {name: 'Unic_id', type: 'string', key: true, header: langs('ID сессии'),width: 100},
                {name: 'Session_id', type: 'string', header: langs('ID сессии'),width: 100},
                {name: 'PMUser_id', type: 'int', header: langs('ID пользователя'),width: 100},
                {name: 'IP', type:'string', header: langs('IP пользователя'), width: 120},
                {name: 'PMUser_Name', type:'string', header: langs('ФИО пользователя'), width: 120},
                {name: 'LoginTime', type: 'string', header: langs('Дата входа'), width: 120},
                {name: 'LogoutTime', type: 'string', header: langs('Дата выхода'), width: 120},
                {name: 'WorkTime', type: 'string', header: langs('Время в системе'), width: 120},
                {name: 'AuthType_id', type: 'string', header: langs('Тип авторизации'), width: 120},
                {name: 'Status', type: 'string', header: langs('Попытка подключения'), width: 120},
                {name: 'PMUser_Login', type: 'string', header: langs('Логин'), width: 120},
                {name: 'IsMedPersonal', type: 'checkbox', header: langs('Врач'), width: 60},
                {name: 'ParallelSessions', type: 'string', header: langs('Количество параллельных сеансов'), width: 60, hidden: getRegionNick() == 'kz'}
            ],
            actions:[
                {name:'action_add', hidden: true},
                {name:'action_edit', hidden: true},
                {name:'action_delete', hidden: true},
                {name:'action_view', hidden: true},
                {name:'action_refresh'},
                {name:'action_print'}
			],
			onMultiSelectionChangeAdvanced: function(sm) {
				var records = sm.getSelections();

				var disableInterruptSessions = true;

				for (var i=0; i<records.length; i++) {
					if (Ext.isEmpty(records[i].get('LogoutTime')) && records[i].get('Status_id') == 1) {
						disableInterruptSessions = false;
					}
				}

				Ext.getCmp('USVF_InterruptSessions').setDisabled(disableInterruptSessions);
			}.createDelegate(this)
        });

        Ext.apply(this,
            {
                xtype: 'panel',
                region: 'center',
                layout:'border',
                items:[
                    form.FilterPanel,
                    {
                        border: false,
                        region: 'center',
                        layout: 'border',
                        defaults: {split: true},
                        items:[
                        {
                            border: false,
                            region: 'center',
                            layout: 'fit',
                            items: [form.SessionGrid]
                        }]
                    }
                ]
            });
        sw.Promed.swUserSessionsViewForm.superclass.initComponent.apply(this, arguments);
    }
});
