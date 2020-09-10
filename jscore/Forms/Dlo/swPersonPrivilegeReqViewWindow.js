/**
* swPersonPrivilegeReqViewWindow - окно просмотра списка запросов на включение в PersonPrivilegeReq
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Salakhov R.
* @version      09.2019
* @comment      
*/
sw.Promed.swPersonPrivilegeReqViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Запросы на включение в льготные регистры',
	layout: 'border',
	id: 'PersonPrivilegeReqViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var date_menu = form.findField('Date_Range');
		var params = new Object();

		wnd.SearchGrid.removeAll();
		params = form.getValues();

		params.start = 0;
		params.limit = 100;
        params.begDate = !Ext.isEmpty(date_menu.getValue1()) ? date_menu.getValue1().format('d.m.Y') : null;
        params.endDate = !Ext.isEmpty(date_menu.getValue2()) ? date_menu.getValue2().format('d.m.Y') : null;
        params.Lpu_id = getGlobalOptions().lpu_id > 0 ? getGlobalOptions().lpu_id : null;

        wnd.SearchGrid.loadData({params: params, globalFilters: params});
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		form.reset();
		wnd.SearchGrid.removeAll();

		//установка значений по умолчанию
		form.findField('PersonPrivilegeReqStatus_id').setValue(1); //1 - Новый
	},
	doExpertise: function() {
		var wnd = this;
        var selected_record = this.SearchGrid.getGrid().getSelectionModel().getSelected();
        if (selected_record && selected_record.get('PersonPrivilegeReq_id')) {
            getWnd('swPersonPrivilegeReqExpertiseWindow').show({
                PersonPrivilegeReq_id: selected_record.get('PersonPrivilegeReq_id'),
                callback: function() {
                    wnd.SearchGrid.refreshRecords(null, 0);
				}
            });
        }
	},
	show: function() {
        var wnd = this;
		sw.Promed.swPersonPrivilegeReqViewWindow.superclass.show.apply(this, arguments);
        this.userMedStaffFact = new Object();

        if ( arguments[0] && arguments[0].userMedStaffFact ) {
            this.userMedStaffFact = arguments[0].userMedStaffFact;
        }

        if(!this.SearchGrid.getAction('action_expertise')){
            this.SearchGrid.addActions({
                handler: function() {
                    wnd.doExpertise();
                },
                name: 'action_expertise',
                text: 'Экспертиза запроса',
                iconCls: 'actions16'
            });
        }
        this.SearchGrid.initEnabledActions();

		this.doReset();
		this.doSearch();
	},
	openSignVersions: function (obj, id) {
		getWnd('swEMDVersionViewWindow').show({
			EMDRegistry_ObjectName: obj,
			EMDRegistry_ObjectID: id
		});
	},
	signDoc: function (obj, id) {
		getWnd('swEMDSignWindow').show({
			EMDRegistry_ObjectName: obj,
			EMDRegistry_ObjectID: id,
			callback: function(data) {
				if (data.preloader) {
					me.disable();
				}

				if (data.success || data.error) {
					me.enable();
				}

				if (data.success) {
					g.getStore().reload();
				}
			}
		});
	},
	initComponent: function() {
		var wnd = this;

		this.FilterFieldPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 140,
			border: false,
			frame: true,
			items: [{
                xtype: 'hidden',
                fieldLabel: 'begDate',
                name: 'begDate'
            }, {
                xtype: 'hidden',
                fieldLabel: 'endDate',
                name: 'endDate'
            }, {
				layout: 'column',
				items: [{
                    layout: 'form',
                    items: [{
                        xtype: 'daterangefield',
                        fieldLabel: langs('Период'),
                        name: 'Date_Range',
                        plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                        width: 200
                    }, {
                        xtype: 'textfield',
                        fieldLabel: langs('Отчество'),
                        name: 'Person_SecName',
                        width: 200
                    }, {
                        xtype: 'swcommonsprcombo',
                        comboSubject: 'PersonPrivilegeReqStatus',
                        fieldLabel: 'Статус запроса',
                        hiddenName: 'PersonPrivilegeReqStatus_id',
                        width: 200
                    }]
                }, {
					layout: 'form',
					items: [{
                        xtype: 'textfield',
                        fieldLabel: langs('Фамилия'),
                        name: 'Person_SurName',
                        width: 200
                    }, {
                        xtype: 'daterangefield',
                        fieldLabel: langs('Дата рождения'),
                        name: 'Person_BirthDay_Range',
                        plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                        width: 200
                    }, {
                        xtype: 'combo',
                        fieldLabel: langs('Результат'),
                        hiddenName: 'Result_Type',
                        width: 200,
                        displayField: 'name',
                        valueField: 'code',
                        editable: false,
                        mode: 'local',
                        forceSelection: true,
                        triggerAction: 'all',
                        store: new Ext.data.SimpleStore({
                            id: 0,
                            fields: [
                                'code',
                                'name'
                            ],
                            data: [
                                ['insert', langs('Включен')],
                                ['reject', langs('Отказано')]
                            ]
                        }),
                        tpl: new Ext.XTemplate(
                            '<tpl for="."><div class="x-combo-list-item">',
                            '{name}&nbsp;',
                            '</div></tpl>'
                        )
                    }]
				}, {
					layout: 'form',
					items: [{
                        xtype: 'textfield',
                        fieldLabel: langs('Имя'),
                        name: 'Person_FirName',
                        width: 200
                    }, {
                        xtype: 'swprivilegetypecombo',
                        fieldLabel: langs('Льготная категория'),
                        hiddenName: 'PrivilegeType_id',
                        width: 200
                    }]
				}]
			}]
		});

		this.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: langs('Найти'),
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						text: langs('Сброс'),
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doReset();
						}
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterFieldPanel,
				this.FilterButtonsPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			tbActions: true,
			actions: [
				{name: 'action_add', handler: function() { wnd.SearchGrid.addRecord(); } },
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=Privilege&m=deletePersonPrivilegeReq'},
				{name: 'action_print'},
				{
					name:'action_signPersonPrivilegeReq', key: 'sign_actions', text:langs('Подписать'),
					tooltip: langs('Подписать'), iconCls : 'x-btn-text', icon: 'img/icons/digital-sign16.png',
					position: 6,
					handler: function() {
						var me = this,
							grid = wnd.SearchGrid.getGrid(),
							record = grid.getSelectionModel().getSelected();

						if (record && record.get('PersonPrivilegeReq_id')) {
							getWnd('swEMDSignWindow').show({
								EMDRegistry_ObjectName: 'PersonPrivilegeReq',
								EMDRegistry_ObjectID: record.get('PersonPrivilegeReq_id'),
								callback: function(data) {
									if (data.preloader) {
										me.disable();
									}

									if (data.success || data.error) {
										me.enable();
									}

									if (data.success) {
										grid.getStore().reload();
									}
								}
							});
						}
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Privilege&m=loadPersonPrivilegeReqList',
			height: 180,
			object: 'PersonPrivilegeReq',
			editformclassname: 'swPersonPrivilegeReqEditWindow',
			id: 'PersonPrivilegeReqGrid',
            paging: true,
            pageSize: 50,
			root: 'data',
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'PersonPrivilegeReq_id', type: 'int', header: 'ID', key: true },
                { name: 'PersonPrivilegeReqStatus_id', hidden: true },
				{ name: 'PersonPrivilegeReq_setDT', type: 'string', header: langs('Дата и время'), width: 150 },
				{ name: 'Person_FullName', type: 'string', header: langs('ФИО, ДР'), width: 200  },
				{ name: 'PrivilegeType_Name', type: 'string', header: langs('Льготная категория'), width: 200 },
				{ name: 'MedStaffFact_FullName', type: 'string', header: langs('Заявитель'), id: 'autoexpand', width: 200 },
				{
					name: 'PersonPrivilegeReq_IsSigned', renderer: function (v, p, r) {
					if (Ext.isEmpty(r.get('PersonPrivilegeReq_id'))) {
						return '';
					}
					var wndId = wnd.getId(),
						id = r.get('PersonPrivilegeReq_id'),
						obj = 'PersonPrivilegeReq';
					var val = '<span data-qtip="Не подписан" class="doc-sign"></span>';
					if (!Ext.isEmpty(v)) {
						switch (parseInt(v)) {
							case 1:
								val = '<span data-qtip="Не актуален" class="doc-notactual"></span>';
								break;
							case 2:
								val = '<span onClick="Ext.getCmp(\''+wndId+'\').openSignVersions(\''+obj+'\','+id+');"';
								val += ' data-qtip="Подписан' + ' (' + r.get('PersonPrivilegeReq_signDT') + ' ' + r.get('PPRSignPmUser_Name') + ')" class="doc-signed"></span>';
								break;
						}
					}
					return val;
					}, header: 'ЭП запроса', width: 70
				},
				{ name: 'PersonPrivilegeReqStatus_Name', type: 'string', header: langs('Статус'), width: 150 },
				{
					name: 'Result_Data', renderer: function (v, p, r) {
						var sign_str = '';
						if (!Ext.isEmpty(r.get('PersonPrivilegeReqAns_id')) && !Ext.isEmpty(r.get('PersonPrivilegeReqAns_IsSigned'))) {
							var sign = r.get('PersonPrivilegeReqAns_IsSigned');
							var wndId = wnd.getId(),
								id = r.get('PersonPrivilegeReqAns_id'),
								obj = 'PersonPrivilegeReqAns';
							if (!Ext.isEmpty(sign)) {
								switch (parseInt(sign)) {
									case 1:
										sign_str = ' <span data-qtip="Не актуален" class="doc-notactual"></span>';
										break;
									case 2:
										sign_str = '<span onClick="Ext.getCmp(\''+wndId+'\').openSignVersions(\''+obj+'\','+id+');"';
										sign_str += ' data-qtip="Подписан' + ' (' + r.get('PersonPrivilegeReqAns_signDT') + ' ' + r.get('PPRASignPmUser_Name') + ')" class="doc-signed"></span>';
										break;
								}
							}
						}
						return v+sign_str;
					},
					header: 'Результат', width: 300
				},
				//{ name: 'Result_Data', type: 'string', header: langs('Результат'), width: 300 },
				{ name: 'Check_Snils', header: langs('Проверка СНИЛС'), width: 130, renderer: function (v, p, record) { return record.get('PersonPrivilegeReq_id') > 0 ? sw.Promed.Format.checkColumn('true', p, record) : ''; } },
				{ name: 'Check_Registration', header: langs('Проверка регистрации'), width: 130, renderer: function (v, p, record) { return record.get('PersonPrivilegeReq_id') > 0 ? sw.Promed.Format.checkColumn('true', p, record) : ''; } },
				{ name: 'Check_Polis', header: langs('Проверка полиса'), width: 130, renderer: function (v, p, record) { return record.get('PersonPrivilegeReq_id') > 0 ? sw.Promed.Format.checkColumn('true', p, record) : ''; } },
				{ name: 'PersonPrivilegeReq_signDT', hidden: true },
				{ name: 'PPRSignPmUser_Name', hidden: true },
				{ name: 'PersonPrivilegeReqAns_signDT', hidden: true },
				{ name: 'PPRASignPmUser_Name', hidden: true }
			],
			title: null,
			toolbar: true,
            enableAudit: true,
			addRecord: function() {
				var viewframe = this;
				var params = new Object();

				params.action = 'add';
				params.callback = function() {
					viewframe.refreshRecords(null, 0);
				};
				params.userMedStaffFact = wnd.userMedStaffFact;

                getWnd('swPersonSearchWindow').show({
                    onHide: function() {
                        viewframe.focus(false);
                    },
                    onSelect: function(person_data) {
                    	params.Person_id = person_data.Person_id;
                        getWnd(viewframe.editformclassname).show(params);
                        getWnd('swPersonSearchWindow').hide();
                    }
                });
			},
			initEnabledActions: function() { //настройка доступности и видимости элементов панели управления списком
				this.add_enabled = isUserGroup(['OperLLO', 'ChiefLLO', 'LpuUser', 'LpuAdmin']); //Оператор ЛЛО, Руководитель ЛЛО МО, Пользователь МО, Администратор МО

				if (isUserGroup(['OperLLO', 'ChiefLLO', 'LpuUser', 'LpuAdmin'])) { //Оператор ЛЛО, Руководитель ЛЛО МО, Пользователь МО, Администратор МО
                    this.getAction('action_add').show();
                    this.getAction('action_edit').show();
                    this.getAction('action_delete').show();
				} else {
                    this.getAction('action_add').hide();
                    this.getAction('action_edit').hide();
                    this.getAction('action_delete').hide();
				}

                /*if (haveArmType('adminllo')) { //АРМ Администратора ЛЛО (АРМ ситуационного центра ЛЛО)
                    this.getAction('action_expertise').show();
                } else {
                    this.getAction('action_expertise').hide();
				}*/
                //экспертиза производится в АРМ ситуационного центра, поэтому тут кнопку скрою
                this.getAction('action_expertise').hide();
			},
            onRowSelect: function(sm, rowIdx, record) {
                var status_id = record.get('PersonPrivilegeReqStatus_id');

                if (record.get('PersonPrivilegeReq_id') > 0 && !this.readOnly) {
                    this.getAction('action_edit').setDisabled(status_id != 1); //1 - Новый
                    this.getAction('action_delete').setDisabled(status_id != 1); //1 - Новый
                    this.getAction('action_expertise').setDisabled(status_id != 2); //2 - На рассмотрнии
                } else {
                    this.getAction('action_edit').setDisabled(true);
                    this.getAction('action_delete').setDisabled(true);
                    this.getAction('action_expertise').setDisabled(true);
                }
                this.getAction('action_add').setDisabled(!this.add_enabled || this.readOnly);
                this.getAction('action_view').setDisabled(Ext.isEmpty(record.get('PersonPrivilegeReq_id')));
            }
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				wnd.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchGrid]
					}]
				}
			]
		});
		sw.Promed.swPersonPrivilegeReqViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});