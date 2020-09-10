/**
* swPhpLogAndUserSessionViewWindow - окно просмотра логов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Admin
* @access			public
* @copyright		Copyright (c) 2012 Swan Ltd.
* @origamiauthor	Dmitry Storozhev
* @version			03.07.2012
*/

sw.Promed.swPhpLogAndUserSessionViewWindow = Ext.extend(sw.Promed.BaseForm, {
	modal: false,
	resizable: false,
	title: 'Журнал авторизаций и событий безопасности',
	maximized: true,
	buttonAlign: 'right',
	id: 'swPhpLogAndUserSessionViewWindow',

	listeners: {
		'beforehide': function () {
			//this.cancelQueries();
		},
		resize: function () {
            if (this.layout.layout) {
                this.doLayout();
            }
        }
	},

	show: function () {
        this.tabPanel.setActiveTab(0);
		sw.Promed.swPhpLogAndUserSessionViewWindow.superclass.show.apply(this, arguments);

		this.doResetGrid1();
		this.findById('PLAYSVW_Login_Range').setValue(getGlobalOptions().date + ' - ' + getGlobalOptions().date);
	},

    loadGrid1: function() {
        var wnd = this;
        var params = {start: 0};
        wnd.Grid1.loadData({params: params, globalFilters: params});
    },

    loadGrid2: function() {
        var wnd = this;
        var params = {start: 0};
        wnd.Grid2.loadData({params: params, globalFilters: params});
    },

    doSearchGrid1: function(cb) {
        // отменяем выполнение запросов в цикле
        //this.cancelQueries();
        var bf = this.Grid1FilterPanel.getForm(),
            store = this.Grid1.ViewGridPanel.getStore();

        var value = bf.findField('limit').getValue();
        if (Ext.isEmpty(value)) {
            bf.findField('limit').setValue(50);
            value = 50;
        }
        this.Grid1.ViewGridPanel.getStore().baseParams['limit'] = value;
        this.Grid1.ViewGridPanel.getBottomToolbar().pageSize = value;
        this.Grid1.pageSize = value;

        with(store) {
            baseParams = bf.getValues();
            baseParams.limit = this.Grid1.pageSize;
            removeAll();
            load({ callback: cb || Ext.emptyFn });
        }
    },

    doResetGrid1: function() {
        this.Grid1FilterPanel.getForm().reset();
        this.Grid1.ViewGridPanel.getStore().removeAll();
        this.Grid1.ViewGridPanel.getStore().baseParams['limit'] = 50;
        this.Grid1.ViewGridPanel.getBottomToolbar().pageSize = 50;
        this.Grid1.pageSize = 50;
    },

    doLoadNewLog: function() {
        Ext.Ajax.request({
            url: "/?c=PhpLog&m=loadOldLogToNewFormat"
        });
    },

    doSearchGrid2: function(clear) {
		var form = this;
		if (clear) {
			form.Grid2FilterPanel.getForm().reset();
			form.findById('PLAYSVW_Login_Range').setValue(getGlobalOptions().date + ' - ' + getGlobalOptions().date);
		}
		// Диапазон дат не должен превышать 7 дней
		if (Ext.isEmpty(form.findById('PLAYSVW_Login_Range').getRawValue())) {
			sw.swMsg.alert(langs('Ошибка'), langs('Поле "Дата входа" обязательно для заполнения'), function() {
				form.findById('PLAYSVW_Login_Range').focus(true, 250);
			});
			return false;
		}

		if (Ext.isEmpty(form.findById('PLAYSVW_Login_Range').getValue1()) || Ext.isEmpty(form.findById('PLAYSVW_Login_Range').getValue2())) {
			sw.swMsg.alert(langs('Ошибка'), langs('Неверно задан период в поле "Дата входа"'), function() {
				form.findById('PLAYSVW_Login_Range').focus(true, 250);
			});
			return false;
		}

		if (form.findById('PLAYSVW_Login_Range').getValue1().add(Date.DAY, 7) < form.findById('PLAYSVW_Login_Range').getValue2()) {
			sw.swMsg.alert(langs('Ошибка'), langs('Диапазон дат не должен превышать 7 дней'), function() {
				form.findById('PLAYSVW_Login_Range').focus(true, 250);
			});
			return false;
		}
        var filters = {
            Login_Range: this.findById('PLAYSVW_Login_Range').getRawValue(),
            Logout_Range: this.findById('PLAYSVW_Logout_Range').getRawValue(),
            IsMedPersonal: this.findById('PLAYSVW_IsMedPersonal').getValue(),
            PMUser_Name: this.findById('PLAYSVW_PMUser_Name').getValue(),
            PMUser_Login: this.findById('PLAYSVW_PMUser_Login').getValue(),
            IP: this.findById('PLAYSVW_IP').getValue(),
            AuthType_id: this.findById('PLAYSVW_AuthType_id').getValue(),
            Status: this.findById('PLAYSVW_Status').getValue(),
            onlyActive: this.findById('PLAYSVW_onlyActive').getValue(),
            Org_id: this.findById('PLAYSVW_Org').getValue(),
            userOrg_id: this.Org_id,
            PMUserGroup_Name: this.findById('PLAYSVW_PMUserGroup').getValue(),
            start: 0,
            limit: 50
        };
        filters.mode = (this.mode) ? this.mode : null;
        form.Grid2.loadData({globalFilters: filters});
    },

	initComponent: function () {
		let form = this;
        this.Grid1FilterPanel = new Ext.FormPanel({
            region: 'north',
            autoHeight: true,
            frame: true,
            items: [{
                xtype: 'fieldset',
                title: lang['filtr'],
                //autoHeight: true,
                height: 185,
                labelAlign: 'right',
                collapsible: false,
                layout: 'form',
                items: [{
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        defaults: {anchor: '100%'},
                        width: 400,
                        labelWidth: 150,
                        items: [{
                            fieldLabel: lang['period'],
                            plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                            name: 'PHPLog_insDT',
                            xtype: 'daterangefield'
                        }, {
                            fieldLabel: lang['kontroller'],
                            name: 'Controller',
                            xtype: 'textfield'
                        }, {
                            fieldLabel: lang['metod'],
                            name: 'Method',
                            xtype: 'textfield'
                        }, {
                            xtype: 'combo',
                            name: 'methodAdvanced',
                            id: 'methodAdvanced',
                            fieldLabel: 'Расшифровка метода',
                            displayField: 'Method_Name_Ru',
                            valueField: 'Method_ID',
                            triggerAction: 'all',
                            mode: 'local',
                            editable: false,
                            store: new Ext.data.JsonStore({
                                autoLoad: true,
                                url: '/?c=PhpLog&m=loadRuMethodsList',
                                fields: [
                                    {name: 'Method_ID', type: 'int'},
                                    {name: 'Method_Name_Ru', type: 'string'}]
                            })
                        }, {
                            fieldLabel: lang['interval_zaprosov_ms'],
                            allowDecimals: false,
                            allowNegative: false,
                            name: 'queriesInterval',
                            xtype: 'numberfield',
                            value: 3000
                        }]
                    }, {
                        layout: 'form',
                        width: 400,
                        defaults: {anchor: '100%'},
                        labelWidth: 200,
                        items: [{
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                defaults: {anchor: '100%'},
                                labelWidth: 200,
                                width: 285,
                                items: [{
                                    fieldLabel: lang['vremya_vyipolneniya_metoda_ot'],
                                    name: 'ET_from',
                                    maskRe: /[\d]/,
                                    xtype: 'textfield'
                                }, {
                                    fieldLabel: lang['vremya_vyipolneniya_zaprosa_ot'],
                                    name: 'ET_Query_from',
                                    maskRe: /[\d]/,
                                    xtype: 'textfield'
                                }]
                            }, {
                                layout: 'form',
                                defaults: {anchor: '100%'},
                                labelWidth: 30,
                                width: 115,
                                items: [{
                                    fieldLabel: lang['do'],
                                    name: 'ET_to',
                                    maskRe: /[\d]/,
                                    xtype: 'textfield'
                                }, {
                                    fieldLabel: lang['do'],
                                    name: 'ET_Query_to',
                                    maskRe: /[\d]/,
                                    xtype: 'textfield'
                                }]
                            }]
                        }, {
                            fieldLabel: lang['polzovatel'],
                            name: 'PMUser_Login',
                            xtype: 'textfield'
                        }, {
                            fieldLabel: lang['limit_zapisey'],
                            allowDecimals: false,
                            allowNegative: false,
                            name: 'limit',
                            xtype: 'numberfield',
                            value: 50
                        }]
                    }, {
                        layout: 'form',
                        defaults: {anchor: '100%'},
                        width: 400,
                        labelWidth: 120,
                        items: [{
                            fieldLabel: lang['ip_polzovatelya'],
                            name: 'IP',
                            xtype: 'textfield'
                        }, {
                            fieldLabel: lang['ip_servera'],
                            name: 'Server_IP',
                            xtype: 'textfield'
                        }, {
                            fieldLabel: lang['dannyie_zaprosa'],
                            name: 'POST',
                            xtype: 'textfield'
                        }, new sw.Promed.SwYesNoCombo({
                            fieldLabel: langs('Ошибка'),
                            hiddenName: 'AnswerError',
                            lastQuery: ''
                        }), {
                            xtype: 'combo',
                            name: 'ARMType',
                            id: 'ARMType',
                            fieldLabel: 'АРМ',
                            displayField: 'ARMType_Name',
                            valueField: 'ARMType_id',
                            triggerAction: 'all',
                            mode: 'local',
                            editable: false,
                            store: new Ext.data.JsonStore({
                                autoLoad: true,
                                url: '/?c=User&m=getARMTypeList',
                                fields: [
                                    {name: 'ARMType_id', type: 'int'},
                                    {name: 'ARMType_Name', type: 'string'}]
                            })
                        }]
                    }]
                }, {
                    layout: 'column',
                    bodyStyle: 'padding: 5px;',
                    items: [{
                        layout: 'form',
                        items: [{
                            xtype: 'button',
                            iconCls: 'search16',
                            text: lang['poisk'],
                            handler: this.doSearchGrid1.createDelegate(this, [])
                        }]
                    }, {
                        layout: 'form',
                        style: 'margin-left: 10px;',
                        items: [{
                            xtype: 'button',
                            iconCls: 'reset16',
                            text: lang['sbros'],
                            handler: this.doResetGrid1.createDelegate(this)
                        }]
                    }]
                }]
            }]
        });

        this.Grid1 = new sw.Promed.ViewFrame({
            height: 520,
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 100,
            autoLoadData: false,
            root: 'data',
            region: 'center',
            id: this.id + '_Grid1',
            autoScroll: true,
            paging: true,
            pageSize: 50,
            auditOptions: {
                key: false
            },
            actions: [
                {name: 'action_add', hidden: true},
                {name: 'action_edit', hidden: true},
                {name: 'action_view', hidden: true},
                {name: 'action_delete', hidden: true},
                {name: 'action_refresh'},
                {name: 'action_print'}
            ],
            stringfields: [
                {name: 'PHPLog_id', type: 'int', hidden: true, key: true},
                {
                    name: 'PHPLog_insDT',
                    renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'),
                    header: langs('Дата и время выполнения'),
                    width: 160
                },
                {name: 'ARMType_Name', type: 'string', header: langs('АРМ'), width: 300, hidden: !(getRegionNick() === 'vologda')},
                {name: 'Controller', type: 'string', header: langs('Контроллер'), width: 300},
                {name: 'Method', type: 'string', header: langs('Метод'), width: 300},
                {name: 'Method_Name_Ru', type: 'string', header: langs('Расшифровка метода'), width: 300, hidden: !(getRegionNick() === 'vologda')},
                {name: 'QueryString', type: 'string', header: langs('Строка запроса'), width: 250, hidden: true},
                {name: 'ET', type: 'string', header: langs('Время выполнения метода'), width: 160},
                {name: 'ET_Query', type: 'string', header: langs('Время выполнения запроса'), width: 170},
                {name: 'PMUser_Login', type: 'string', header: langs('Пользователь'), id: 'autoexpand'},
                {name: 'PMUser_id', type: 'int', header: langs('ID пользователя'), width: 100},
                {name: 'IP', type: 'string', header: langs('IP пользователя'), width: 100},
                {name: 'Server_IP', type: 'string', header: langs('IP сервера'), width: 100},
                {name: 'POST', hidden: true, type: 'string', header: langs('Данные запроса')},
                {name: 'AnswerError', hidden: true, type: 'string', header: langs('Ошибка')}
            ],
            dataUrl: '/?c=PhpLog&m=loadPhpLogGrid',
            totalProperty: 'totalCount'
        });
        this.Grid1.ViewGridPanel.getStore().baseParams['limit'] = this.Grid1.pageSize;
        this.Grid1.ViewGridPanel.getSelectionModel().on('rowselect', function (sm, rowIdx, rec) {
            var AnswerMsg = 'нет';
            if (rec.get('AnswerError') != null && rec.get('AnswerError') !== '') {
                AnswerError_obj = Ext.util.JSON.decode(rec.get('AnswerError'));
                if (AnswerError_obj.error_msg) { // если есть сообщение об ошибке выводим его
                    AnswerMsg = AnswerError_obj.error_msg;
                } else {
                    // возвращаем декодированный json (в нём декодированы символы utf8)
                    Ext.util.JSON.encode(AnswerError_obj);
                }
            }
            form.Grid1BottomTemplate.overwrite(form.Grid1BottomPanel.body, {
                QueryString: rec.get('QueryString') != null && rec.get('QueryString') !== '' ? rec.get('QueryString') : 'нет',
                POST: rec.get('POST') != null && rec.get('POST') !== '' ? rec.get('POST') : 'нет',
                AnswerError: AnswerMsg
            });
        }.createDelegate(this));

        this.Grid1BottomTemplate = new Ext.Template(
            'Строка запроса: <b>{QueryString}</b><br>',
            'POST-данные: <b>{POST}</b><br>',
            'Ошибка: <b>{AnswerError}</b>'
        );

        this.Grid1BottomPanel = new Ext.Panel({
            height: 90,
            bodyStyle: 'padding:2px',
            region: 'south',
            border: true,
            frame: true,
            html: ''
        });

        this.Grid2FilterPanel = new Ext.FormPanel({
            region: 'north',
            //autoHeight: true,
            height: 160,
            frame: true,
            items: [{
                xtype: 'fieldset',
                title: lang['filtr'],
                autoHeight: true,
                labelAlign: 'right',
                collapsible: false,
                layout: 'form',
                items: [{
                    bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
                    border: true,
                    autoHeight: true,
                    colapsible: true,
                    colapsed: false,
                    layout: 'column',
                    labelWidth: 100,
                    labelAlign: 'right',
                    id: 'PLAYSVW_OrgFilterPanel',
                    items: [{
                        layout: 'form',
                        border: false,
                        items: [{
                            allowBlank: false,
                            fieldLabel: lang['data_vhoda'],
                            id: 'PLAYSVW_Login_Range',
                            plugins: [
                                new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
                            ],
                            width: 170,
                            xtype: 'daterangefield'
                        }, {
                            fieldLabel: lang['data_vyihoda'],
                            id: 'PLAYSVW_Logout_Range',
                            plugins: [
                                new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
                            ],
                            width: 170,
                            xtype: 'daterangefield'
                        }, {
                            fieldLabel: lang['vrach'],
                            id: 'PLAYSVW_IsMedPersonal',
                            width: 170,
                            xtype: 'swyesnocombo'
                        }]
                    }, {
                        layout: 'form',
                        border: false,
                        labelWidth: 120,
                        items: [{
                            id: 'PLAYSVW_IP',
                            width: 200,
                            disabled: false,
                            fieldLabel: lang['ip_polzovatelya'],
                            xtype: 'textfield'
                        }, {
                            id: 'PLAYSVW_PMUser_Name',
                            width: 200,
                            disabled: false,
                            fieldLabel: lang['polzovatel'],
                            xtype: 'textfield'
                        }, {
                            id: 'PLAYSVW_PMUser_Login',
                            width: 200,
                            disabled: false,
                            fieldLabel: lang['login'],
                            xtype: 'textfield'
                        }]
                    }, {
                        layout: 'form',
                        border: false,
                        items: [{
                            id: 'PLAYSVW_Org',
                            width: 200,
                            disabled: (!getGlobalOptions().superadmin),
                            fieldLabel: lang['organizatsiya'],
                            onTrigger1Click: function () {
                                if (!this.disabled) {
                                    var combo = this;
                                    getWnd('swOrgSearchWindow').show({
                                        object: 'org',
                                        onSelect: function (orgData) {
                                            if (orgData.Org_id > 0) {
                                                combo.getStore().load({
                                                    params: {
                                                        Object: 'Org',
                                                        Org_id: orgData.Org_id,
                                                        Org_Name: ''
                                                    },
                                                    callback: function () {
                                                        combo.setValue(orgData.Org_id);
                                                        combo.focus(true, 500);
                                                        combo.fireEvent('change', combo);
                                                    }
                                                });
                                            }
                                            getWnd('swOrgSearchWindow').hide();
                                        },
                                        onClose: function () {
                                            combo.focus(true, 200)
                                        }
                                    });
                                }
                            },
                            xtype: 'sworgcombo'
                        }, {
							xtype: 'combo',
							name: 'USVF_PMUserGroup',
							id: 'USVF_PMUserGroup',
							fieldLabel: lang['gruppa'],
							displayField: 'Group_Desc',
							valueField: 'Group_Name',
							triggerAction: 'all',
							mode: 'local',
							editable: false,
							width: 200,
							store: new Ext.data.JsonStore({
								autoLoad: true,
								url: C_USER_GETGROUP_LIST,
								fields: [
									{name: 'Group_id', type: 'int'},
									{name: 'Group_Name', type: 'string'},
									{name: 'Group_Desc', type: 'string'}
								],
							})
                        }]
                    }, {
                        layout: 'form',
                        border: false,
                        labelWidth: 160,
                        items: [{
                            border: false,
                            layout: 'form',
                            items: [{
                                xtype: 'combo',
                                store: new Ext.data.SimpleStore({
                                    fields: ['id', 'value'],
                                    data: [
                                        ['1', lang['udachnyiy_vhod']],
                                        ['0', lang['neudachnyiy_vhod']],
                                        ['2', 'блокировка учетной записи'],
                                    ]
                                }),
                                id: 'PLAYSVW_Status',
                                fieldLabel: lang['popyitka_podklyucheniya'],
                                displayField: 'value',
                                valueField: 'id',
                                triggerAction: 'all',
                                mode: 'local',
                                editable: false,
                                width: 150
                            }, {
                                border: false,
                                layout: 'form',
                                items: [{
                                    xtype: 'combo',
                                    store: new Ext.data.SimpleStore({
                                        fields: ['id', 'value'],
                                        data: [
                                            ['1', langs('по логину/паролю')],
                                            ['2', langs('по соцкарте')],
                                            ['3', langs('через УЭК')],
                                            ['4', langs('через ЭЦП')],
                                            ['5', langs('через ЕСИА')]
                                        ]
                                    }),
                                    id: 'PLAYSVW_AuthType_id',
                                    fieldLabel: lang['tip_avtorizatsii'],
                                    displayField: 'value',
                                    valueField: 'id',
                                    triggerAction: 'all',
                                    mode: 'local',
                                    editable: false,
                                    width: 150
                                }]
                            }, {
                                layout: 'fit',
                                border: false,
                                labelWidth: 0,
                                items: [{
                                    boxLabel: lang['otobrajat_tolko_aktiv_podklyucheniya'],
                                    labelSeparator: '',
                                    id: 'PLAYSVW_onlyActive',
                                    xtype: 'checkbox'
                                }]
                            }]
                        }]
                    }],
                    buttons: [{
                        text: BTN_FIND2,
                        handler: function () {
                            form.doSearchGrid2();
                        },
                        iconCls: 'search16'
                    }, {
                        text: BTN_RESETFILTER,
                        handler: function () {
                            form.doSearchGrid2(true);
                        },
                        iconCls: 'resetsearch16'
                    }, '-']
                }]
            }]
        });

        this.Grid2 = new sw.Promed.ViewFrame({
            height: 660,
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 100,
            autoLoadData: false,
            root: 'data',
            region: 'center',
            id: this.id + '_Grid2',
            autoScroll: true,
            paging: true,
            pageSize: 50,
            auditOptions: {
                key: false
            },
            actions: [
                {name: 'action_add', hidden: true},
                {name: 'action_edit', hidden: true},
                {name: 'action_view', hidden: true},
                {name: 'action_delete', hidden: true},
                {name: 'action_refresh'},
                {name: 'action_print'}
            ],
            stringfields: [
                {name: 'Status_id', type: 'int', hidden: true},
                {name: 'Unic_id', type: 'string', key: true, header: langs('ID сессии'), width: 100},
                {name: 'Session_id', type: 'string', header: langs('ID сессии'), width: 100},
                {name: 'PMUser_id', type: 'int', header: langs('ID пользователя'), width: 100},
                {name: 'IP', type: 'string', header: langs('IP пользователя'), width: 120},
                {name: 'PMUser_Name', type: 'string', header: langs('ФИО пользователя'), width: 120},
                {name: 'LoginTime', type: 'string', header: langs('Дата входа'), width: 120},
                {name: 'LogoutTime', type: 'string', header: langs('Дата выхода'), width: 120},
                {name: 'WorkTime', type: 'string', header: langs('Время в системе'), width: 120},
                {name: 'AuthType_id', type: 'string', header: langs('Тип авторизации'), width: 120},
                {name: 'Status', type: 'string', header: langs('Попытка подключения'), width: 120},
                {name: 'PMUser_Login', type: 'string', header: langs('Логин'), width: 120},
                {name: 'IsMedPersonal', type: 'checkbox', header: langs('Врач'), width: 60},
                {
                    name: 'ParallelSessions',
                    type: 'string',
                    header: langs('Количество параллельных сеансов'),
                    width: 60,
                    hidden: getRegionNick() === 'kz'
                }
            ],
            dataUrl: '/?c=User&m=getUserSessions',
            totalProperty: 'totalCount',
        });
        this.Grid2.ViewGridPanel.getStore().baseParams['limit'] = this.Grid2.pageSize;
        this.Grid2.ViewGridPanel.getSelectionModel().on('rowselect', function (sm, rowIdx, rec) {

        }.createDelegate(this));

		this.tabPanel = new Ext.TabPanel({
            activeTab: 1,
            plain: true,
            region: 'center',
            border: false,
			items: [{
				title: "События безопасности системы",
                layout: 'border',
				id: "tab_log",
				items: [this.Grid1FilterPanel, this.Grid1, this.Grid1BottomPanel]
			}, {
				title: "Авторизации в системе",
                layout: 'border',
				id: 'tab_users',
				items: [this.Grid2FilterPanel, this.Grid2]
			}],
            listeners: {
                tabchange: function (tab, panel) {
                    switch (panel.id) {
                        case 'tab_log':
                            form.loadGrid1();
                            break;
                        case 'tab_users':
                            form.loadGrid2();
                            break;
                    }
                }
            }
		});

		Ext.apply(this, {
            layout: 'border',
			items: [this.tabPanel],
			buttons: [{
				text: '-'
			}, HelpButton(this, 20016), {
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: TABINDEX_PHPLOG + 17,
				handler: this.hide.createDelegate(this, []),
				onTabAction: function () {
				}.createDelegate(this)
			}]
		});
		sw.Promed.swPhpLogAndUserSessionViewWindow.superclass.initComponent.apply(this, arguments);
	}
});