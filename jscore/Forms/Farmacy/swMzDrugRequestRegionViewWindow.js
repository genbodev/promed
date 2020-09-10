/**
* swMzDrugRequestRegionViewWindow - окно просмотра заявок региона
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov Rustam
* @version      10.2012
* @comment      
*/
sw.Promed.swMzDrugRequestRegionViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: langs('Заявочные кампании'),
	layout: 'border',
	id: 'MzDrugRequestRegionViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	changeYear: function(value) {
		var val = Ext.getCmp('mdrrvYear').getValue();
		if (!val || value == 0)
			val = (new Date()).getFullYear();
		Ext.getCmp('mdrrvYear').setValue(val+value);
	},
	doSearch: function(clear, default_values) {
		var wnd = this;
		
		if (clear) {
			Ext.getCmp('mdrrvYear').setValue(null);
		}
		if (default_values) {
			wnd.changeYear(0);			
		}
			
		var params = new Object();
		params.Year = Ext.getCmp('mdrrvYear').getValue();
		params.limit = 100;
		params.start =  0;

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
			globalFilters: params
		});
	},
    setDisabledAction: function(actions_id, action, disable) {
        var actions = this.SearchGrid.getAction(actions_id).items[0].menu.items;
        var menu_actions = this.SearchGrid.ViewContextMenu.items.get('id_'+actions_id).menu.items;
        actions.each(function(a) {
            if (a.name == action) {
                if (disable) {
                    a.disable();
                } else {
                    a.enable();
                }
            }
        });
        menu_actions.each(function(a) {
            if (a.name == action) {
                if (disable) {
                    a.disable();
                } else {
                    a.enable();
                }
            }
        });
    },
    hideAction: function(actions_id, action, hide) {
        var actions = this.SearchGrid.getAction(actions_id).items[0].menu.items;
        var menu_actions = this.SearchGrid.ViewContextMenu.items.get('id_'+actions_id).menu.items;
        actions.each(function(a) {
            if (a.name == action) {
                if (hide) {
                    a.hide();
                } else {
                    a.show();
                }
            }
        });
        menu_actions.each(function(a) {
            if (a.name == action) {
                if (hide) {
                    a.hide();
                } else {
                    a.show();
                }
            }
        });
    },
    createCopy: function(type) {
        var record = this.SearchGrid.getGrid().getSelectionModel().getSelected();
        var params = new Object();
        var url = "";
        var msg = new Object();
        var wnd = this;

        switch(type) {
            case 'archive_copy':
                url = "/?c=MzDrugRequest&m=createDrugRequestArchiveCopy";
                msg.success = langs('Создана архивная копия заявки');
                msg.failure = langs('Не удалось создать архивную копию заявки');
                msg.question = langs('Будет создана архивная копия заявки. Продолжить?');
                break;
            case 'first_copy':
                params.check_consolidated_request = 1;
                params.check_status = 1;
                url = "/?c=MzDrugRequest&m=createDrugRequestRegionFirstCopy";
                msg.success = langs('Создана копия заявочной кампании');
                msg.failure = langs('Не удалось создать копию заявочной кампании');
                msg.question = langs('Будет создана копия заявочной кампании. Продолжить?');
                break;
            case 'delete_first_copy':
                url = "/?c=MzDrugRequest&m=deleteDrugRequestRegionFirstCopy";
                msg.success = langs('Удалена копия заявочной кампании');
                msg.failure = langs('Не удалось удалить копию заявочной кампании');
                msg.question = langs('Будет удалена копия заявочной кампании. Продолжить?');
                break;
        }

        if (record.get('DrugRequest_id') > 0) {
            params.DrugRequest_id = record.get('DrugRequest_id');

            sw.swMsg.show({
                buttons: Ext.Msg.YESNO,
                fn: function(buttonId) {
                    if (buttonId == 'yes') {
                        Ext.Ajax.request({
                            params: params,
                            success: function (response) {
                                var result = Ext.util.JSON.decode(response.responseText);
                                if (Ext.isEmpty(result.Error_Msg)) {
                                    sw.swMsg.alert(langs('Сообщение'), msg.success);
                                    wnd.SearchGrid.refreshRecords(null,0);
                                }
                            },
                            failure: function () {
                                sw.swMsg.alert(msg.failure);
                            },
                            url: url
                        });
                    }
                },
                icon: Ext.Msg.QUESTION,
                msg: msg.question,
                title: langs('Внимание')
            });
        }
    },
	show: function() {        
		var wnd = this;
        var region_nick = getRegionNick();

		this.OrgServiceTerr_Org_id = null;
		this.ARMType = null;

		if (arguments[0]) {
            if(arguments[0].OrgServiceTerr_Org_id) {
                this.OrgServiceTerr_Org_id = arguments[0].OrgServiceTerr_Org_id;
            }
            if(arguments[0].ARMType) {
                this.ARMType = arguments[0].ARMType;
            }
        }

        //установка валюты в названиях столбцов
        var currency_str = ' ('+getCurrencyName()+')';
        this.SearchGrid.setColumnHeader('DrugRequest_SummaFed', langs('Сумма (фед.)')+currency_str);
        this.SearchGrid.setColumnHeader('DrugRequest_SummaReg', langs('Сумма (рег.)')+currency_str);
        this.SearchGrid.setColumnHeader('DrugRequest_Summa', langs('Сумма')+currency_str);

		sw.Promed.swMzDrugRequestRegionViewWindow.superclass.show.apply(this, arguments);
		wnd.SearchGrid.addActions({
			name:'action_actions',
			text:langs('Действия'),
			menu: [{
				name: 'action_get_access',
				text: langs('Предоставить доступ к заявке врачам МО'),
				tooltip: langs('Предоставить доступ к заявке врачам МО'),
				handler: function() {
					var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') == 4) { //4 - Нулевая
						Ext.Ajax.request({
							failure:function () {
								sw.swMsg.alert(langs('Ошибка'), langs('Не удалось сменить статус заявки'));
								loadMask.hide();
								wnd.hide();
							},
							params:{
								DrugRequest_id: record.get('DrugRequest_id'),
								DrugRequestStatus_Code: 1 //Начальная
							},
							success: function (response) {
								wnd.SearchGrid.refreshRecords(null,0);
								Ext.Ajax.request({
									params:{
										DrugRequest_id: record.get('DrugRequest_id'),
										event: 'request_set_edit'
									},
									success: function (response) {
										var result = Ext.util.JSON.decode(response.responseText);
										if (result[0] && result[0].Message_id > 0) {
											getWnd('swMessagesViewWindow').show({
												mode: 'openMessage',
												message_data: result[0]
											});
										}
									},
									url:'/?c=MzDrugRequest&m=getNotice'
								});
							},
							url:'/?c=MzDrugRequest&m=changeDrugRequestStatus'
						});	
					}
				},
				iconCls: 'edit16'
			}, {
				name: 'action_mo_view',
				text: langs('Просмотр заявок МО'),
				tooltip: langs('Просмотр заявок МО'),
				handler: function() {
					var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequest_id') > 0) {
						getWnd('swMzDrugRequestMoViewWindow').show({
                            ARMType: wnd.ARMType,
							DrugRequest_id: record.get('DrugRequest_id'),
							DrugRequest_Name: record.get('DrugRequest_Name'),
							DrugRequestPeriod_id: record.get('DrugRequestPeriod_id'),
							PersonRegisterType_id: record.get('PersonRegisterType_id'),
							DrugRequestKind_id: record.get('DrugRequestKind_id'),
							DrugGroup_id: record.get('DrugGroup_id'),
                            DrugRequest_Version: record.get('DrugRequest_Version'),
							OrgServiceTerr_Org_id: wnd.OrgServiceTerr_Org_id,
                            SvodDrugRequest_Name: record.get('SvodDrugRequest_Name'),
                            FirstCopy_Inf: record.get('FirstCopy_Inf'),
							onHide: function() {
								wnd.SearchGrid.refreshRecords(null, 0);
							}
						});
					}
				},
				iconCls: 'view16'
			}, {
                name: 'action_approve_all',
                text: langs('Утвердить заявочную кампанию'),
                tooltip: langs('Утвердить заявочную кампанию'),
                handler: function() {
                    var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
                    if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') != 3) { //3 - Утвержденная
                        Ext.Ajax.request({
                            params:{
                                RegionDrugRequest_id: record.get('DrugRequest_id'),
                                check_status: 0,
                                set_auto_status: 0
                            },
                            failure:function () {
                                sw.swMsg.alert(langs('Ошибка'), langs('Не удалось сменить статус заявки'));
                                loadMask.hide();
                                wnd.hide();
                            },
                            success: function (response) {
                                wnd.SearchGrid.refreshRecords(null, 0);
                            },
                            url:'/?c=MzDrugRequest&m=approveAllDrugRequestMo'
                        });
                    }
                },
                iconCls: 'edit16'
            }, {
                name: 'action_unapprove_all',
                text: langs('Редактировать заявки МО'),
                tooltip: langs('Редактировать заявки МО'),
                handler: function() {
                    var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
                    if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') == 3) { //3 - Утвержденная
                        Ext.Ajax.request({
                            failure:function () {
                                sw.swMsg.alert(langs('Ошибка'), langs('Не удалось сменить статус заявки'));
                                loadMask.hide();
                                wnd.hide();
                            },
                            params:{
                                RegionDrugRequest_id: record.get('DrugRequest_id'),
                                check_consolidated_request: 1
                            },
                            success: function (response) {
                                var result = Ext.util.JSON.decode(response.responseText);
                                if (result.success) {
                                    wnd.SearchGrid.refreshRecords(null,0);
                                    Ext.Ajax.request({
                                        params:{
                                            DrugRequest_id: record.get('DrugRequest_id'),
                                            event: 'request_set_edit'
                                        },
                                        success: function (response) {
                                            var result = Ext.util.JSON.decode(response.responseText);
                                            if (result[0] && result[0].Message_id > 0) {
                                                getWnd('swMessagesViewWindow').show({
                                                    mode: 'openMessage',
                                                    message_data: result[0]
                                                });
                                            }
                                        },
                                        url:'/?c=MzDrugRequest&m=getNotice'
                                    });
                                }
                            },
                            url:'/?c=MzDrugRequest&m=unapproveAllDrugRequestMo'
                        });
                    }
                },
                iconCls: 'edit16'
            }, {
                name: 'action_recalculate_by_fin',
                text: langs('Выполнить расчет лимитированной заявки'),
                tooltip: langs('Выполнить расчет лимитированной заявки'),
                handler: function() {
                    var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
                    if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') == 1) { //1 - Начальная
                        sw.swMsg.show({
                            icon: Ext.MessageBox.QUESTION,
                            msg: 'Будет выполнен перерасчет количества ЛП в заявке. После перерасчета количество ЛП в заявках будет равно количеству ЛП, указанному в заявках с реальной потребностью, уменьшенному пропорционально доле объема финансирования заявки  к сумме реальной потребности. Вы действительно желаете выполнить такой расчет? ',
                            title: 'Вопрос',
                            buttons: Ext.Msg.YESNO,
                            fn: function(buttonId, text, obj) {
                                if ('yes' == buttonId) {
                                    Ext.Ajax.request({
                                        failure:function () {
                                            sw.swMsg.alert(langs('Ошибка'), langs('Не удалось выполнить расчет'));
                                            loadMask.hide();
                                            wnd.hide();
                                        },
                                        params:{
                                            RegionDrugRequest_id: record.get('DrugRequest_id')
                                        },
                                        success: function (response) {
                                            var result = Ext.util.JSON.decode(response.responseText);
                                            if (result.success) {
                                                wnd.SearchGrid.refreshRecords(null,0);
                                            }
                                        },
                                        url:'/?c=MzDrugRequest&m=recalculateDrugRequestByFin'
                                    });
                                }
                            }
                        });
                    }
                },
                iconCls: 'edit16'
            }],
			iconCls: 'actions16'
		});
		
		wnd.SearchGrid.addActions({
			name:'action_copy',
			text:langs('Копировать'),
            iconCls: 'add16',
            menu: [{
                name:'action_create_archive_copy',
                text:langs('Создать рабочую копию'),
                iconCls: 'add16',
                handler: function() {
                    wnd.createCopy('archive_copy');
                }
            }, {
                name:'action_create_first_copy',
                text:langs('Создать заявку на закуп по реальной потребности'),
                iconCls: 'add16',
                handler: function() {
                    wnd.createCopy('first_copy');
                }
            }, { //техническая функция уделения  "первой копии", только для разработчика
                name:'action_delete_first_copy',
                text:langs('Удалить заявку на закуп по реальной потребности'),
                iconCls: 'delete16',
                handler: function() {
                    wnd.createCopy('delete_first_copy');
                }
            }]

		});

        //принудительное утверждение/отмена утверждения доступны только администратору ЦОД
        wnd.hideAction('action_actions', 'action_approve_all', !isSuperAdmin());
        wnd.hideAction('action_actions', 'action_unapprove_all', !isSuperAdmin());
        wnd.hideAction('action_copy', 'action_create_archive_copy', (region_nick != 'ufa'));

        wnd.hideAction('action_copy', 'action_create_archive_copy', (region_nick == 'ufa'));
        wnd.hideAction('action_copy', 'action_delete_first_copy', true);

		wnd.doSearch(true, true);
	},
	initComponent: function() {
		var wnd = this;
		
		this.WindowToolbar = new Ext.Toolbar({
			items: [{					
					xtype: 'button',
					disabled: true,
					text: langs('Год')
				}, {
					text: null,
					xtype: 'button',
					iconCls: 'arrow-previous16',
					handler: function() {						
						wnd.changeYear(-1);
						wnd.doSearch();
					}.createDelegate(this)
				}, {
					xtype : "tbseparator"
				}, {
					xtype : 'numberfield',
					id: 'mdrrvYear',
					allowDecimal: false,
					allowNegtiv: false,
					width: 35,
					enableKeyEvents: true,
					listeners: {
						'keydown': function (inp, e) {
							if (e.getKey() == Ext.EventObject.ENTER) {
								e.stopEvent();
								wnd.doSearch();
							}
						}
					}
				}, {
					xtype : "tbseparator"
				}, {
					text: null,
					xtype: 'button',
					iconCls: 'arrow-next16',
					handler: function() {						
						wnd.changeYear(1);
						wnd.doSearch();
					}.createDelegate(this)
				}, {
					xtype: 'tbfill'
				}
			]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			id: this.id + 'ViewFrame',
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=MzDrugRequest&m=delete'},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadRegionList',
			height: 180,
			object: 'DrugRequest',
			editformclassname: 'swMzDrugRequestRegionEditWindow',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugRequest_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugRequestPeriod_id', type: 'int', hidden: true},
				{name: 'PersonRegisterType_id', type: 'int', hidden: true},
				{name: 'DrugRequestKind_id', type: 'int', hidden: true},
				{name: 'DrugGroup_id', type: 'int', hidden: true},
				{name: 'SvodDrugRequest_id', type: 'int', hidden: true},
				{name: 'DrugRequest_Version', type: 'int', hidden: true},
				{name: 'DrugRequestRegionFirstCopy_id', type: 'int', header: langs('Копия'), hidden: true},
				{name: 'DrugRequestProperty_OrgName', type: 'string', header: langs('Координатор'), width: 90},
				{name: 'PersonRegisterType_Name', type: 'string', header: langs('Тип'), width: 90},
				{name: 'DrugRequestKind_Name', type: 'string', header: langs('Вид'), width: 90},
				{name: 'DrugGroup_Name', type: 'string', header: langs('Группа медикаментов'), width: 90},
				{name: 'DrugRequest_Name', type: 'string', header: langs('Наименование'), width: 120, id: 'autoexpand'},
				{name: 'MoDrugRequest_Count', type: 'string', header: langs('Кол-во МО'), width: 90},
				{name: 'DrugRequestStatus_id', type: 'int', hidden: true},
				{name: 'DrugRequestStatus_Code', type: 'int', hidden: true},
				{name: 'DrugRequestStatus_Name', type: 'string', header: langs('Статус'), width: 120},
				{name: 'DrugRequest_SummaFed', header: langs('Сумма (фед.)'), hidden: (getRegionNick() != 'perm'), width: 120},
				{name: 'DrugRequest_SummaReg', header: langs('Сумма (рег.)'), hidden: (getRegionNick() != 'perm'), width: 120},
				{name: 'DrugRequest_Summa', header: langs('Сумма'), width: 120,
					renderer: function(value,p,rec){
						if (rec.get('PersonRegisterType_id') == 1 && getRegionNick() == 'perm'){
							return null;
						}
						var total = rec.get('DrugRequestQuota_Total');
						if (value > 0 && total > 0 && value*1 > total*1 ) {
							return '<span style="color: red">' + value + '</span>';
						}
						return value;
					}
				},
				{name: 'DrugRequestQuota_Total', type: 'string', header: langs('Лимит фед.заявки'), width: 120, hidden: true},
				{name: 'SvodDrugRequest_Name', type: 'string', header: langs('Сводная заявка'), width: 120},
				{name: 'FirstCopy_Inf', type: 'string', header: langs('Примечание'), width: 120},
				{name: 'DrugRequest_isActual', header: langs('Актуальность'), renderer: sw.Promed.Format.checkColumn, width: 90}
			],
			title: null,
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugRequest_id') > 0 && !this.readOnly && Ext.isEmpty(record.get('DrugRequest_Version'))) {
					this.ViewActions.action_edit.setDisabled(record.get('DrugRequestStatus_Code') == 8);
					this.ViewActions.action_view.setDisabled(record.get('DrugRequestStatus_Code') == 8);
    				this.ViewActions.action_delete.setDisabled(record.get('DrugRequestStatus_Code') != 4 && record.get('DrugRequestStatus_Code') != 1);
					this.ViewActions.action_copy.setDisabled(record.get('DrugRequestStatus_Code') == 8);
                    wnd.setDisabledAction('action_copy', 'action_create_first_copy', !(record.get('DrugRequestStatus_Code') == 3 && Ext.isEmpty(record.get('DrugRequestRegionFirstCopy_id')) && Ext.isEmpty(record.get('SvodDrugRequest_id'))));
                    this.ViewActions.action_actions.setDisabled(false);
                    wnd.setDisabledAction('action_actions', 'action_get_access', record.get('DrugRequestStatus_Code') != 4); //4 - Нулевая);
                    wnd.setDisabledAction('action_actions', 'action_approve_all', record.get('DrugRequestStatus_Code') == 3 || record.get('DrugRequestStatus_Code') == 8); //3 - Утвержденная; 8 - Выполняется операция обработки
                    wnd.setDisabledAction('action_actions', 'action_unapprove_all', !(record.get('DrugRequestStatus_Code') == 3 && Ext.isEmpty(record.get('SvodDrugRequest_id'))) || record.get('DrugRequestStatus_Code') == 8);
                    wnd.setDisabledAction('action_actions', 'action_recalculate_by_fin', !(record.get('DrugRequestStatus_Code') == 1 && !Ext.isEmpty(record.get('DrugRequestRegionFirstCopy_id')))); //1 - Начальная
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
					this.ViewActions.action_copy.setDisabled(true);
                    this.ViewActions.action_actions.setDisabled(Ext.isEmpty(record.get('DrugRequest_id')) || this.readOnly);
                    wnd.setDisabledAction('action_actions', 'action_get_access', true);
                    wnd.setDisabledAction('action_actions', 'action_approve_all', true);
                    wnd.setDisabledAction('action_actions', 'action_unapprove_all', true);
                    wnd.setDisabledAction('action_actions', 'action_recalculate_by_fin', true);
				}
                wnd.setDisabledAction('action_actions', 'action_mo_view', Ext.isEmpty(record.get('DrugRequest_id')) || record.get('DrugRequestStatus_Code') == 8);
			},
			onDblClick: function(grid, number, object) {
				var viewframe = grid.ownerCt.ownerCt;
				if (!viewframe.ViewActions.action_actions.isDisabled()) {
					viewframe.getAction('action_actions').initialConfig.menu[1].handler();
				}
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
				handler: function()  {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			tbar: this.WindowToolbar,
			items:[{
				border: false,
				xtype: 'panel',
				region: 'center',
				layout: 'border',
				id: 'mdrrvGridPanel',
				items: [wnd.SearchGrid]
			}]
		});
		sw.Promed.swMzDrugRequestRegionViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});