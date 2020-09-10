/**
 * swEvnDtpDeathWindow - окно просмотра извещений о скончавшихся в ДТП.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2016 Swan Ltd.
 * @author       Alexander Kurakin (a.kurakin@swan.perm.ru)
 * @version      05.02.2016
 * @comment      Префикс для id компонентов EDDW (swEvnDtpDeathWindow)
 *
 */
sw.Promed.swEvnDtpDeathWindow = Ext.extend(sw.Promed.BaseForm, {
    title: 'Извещения о скончавшихся в ДТП',
    iconCls: 'stac-accident-dead16',
    layout: 'border',
    maximized: true,
    closable: true,
    closeAction: 'hide',
    buttonAlign: 'left',
	
    /**
	 *  Функция открывает форму добавления/редактирования/просмотра
	 */
    openEditWindow: function(action) {
        var wnd = this;
		
        if (action != 'add' && action != 'edit' && action != 'view') {
            return false;
        }
		
        if (action == 'add' && getWnd('swPersonSearchWindow').isVisible()) {
            sw.swMsg.alert('Сообщение', 'Окно поиска человека уже открыто');
            return false;
        }
		
        if (getWnd('swEvnDtpDeathEditWindow').isVisible()) {
            sw.swMsg.alert('Сообщение', 'Окно извещения о скончавшемся в ДТП уже открыто');
            return false;
        }
		
        var params = new Object();
        var grid = this.frameGrid.getGrid();
		
        params.action = action;
		
        params.callback = function(data) {
            //swalert(data);
            if (!data || !data.evnDtpDeathData) {
                return false;
            }
			
            // Обновить запись в grid
            var record = grid.getStore().getById(data.evnDtpDeathData.EvnDtpDeath_id);
			
            if (record) {
                record.set('EvnDtpDeath_id', data.evnDtpDeathData.EvnDtpDeath_id);
                record.set('Person_Birthday', data.evnDtpDeathData.Person_Birthday);
                record.set('Person_Surname', data.evnDtpDeathData.Person_Surname);
                record.set('Person_Firname', data.evnDtpDeathData.Person_Firname);
                record.set('Person_Secname', data.evnDtpDeathData.Person_Secname);
                record.set('Person_id', data.evnDtpDeathData.Person_id);
                record.set('PersonEvn_id', data.evnDtpDeathData.PersonEvn_id);
                record.set('Server_id', data.evnDtpDeathData.Server_id);
				
                record.commit();
            } else {
                if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDtpDeath_id')) {
                    grid.getStore().removeAll();
                }
				
                grid.getStore().loadData({
                    'data': [data.evnDtpDeathData]
                }, true);
            }
			
            grid.getStore().each(function(record) {
                if (record.get('Person_id') == data.evnDtpDeathData.Person_id && record.get('Server_id') == data.evnDtpDeathData.Server_id) {
                    record.set('Person_Birthday', data.evnDtpDeathData.Person_Birthday);
                    record.set('Person_Surname', data.evnDtpDeathData.Person_Surname);
                    record.set('Person_Firname', data.evnDtpDeathData.Person_Firname);
                    record.set('Person_Secname', data.evnDtpDeathData.Person_Secname);
					
                    record.commit();
                }
            });

            return true;
        };
		params.onHide = Ext.emptyFn;
		
        if (action == 'add') {
            getWnd('swPersonSearchWindow').show({
                onClose: Ext.emptyFn,
                onSelect: function(person_data) {
                    params.onHide = function() {
	                      // TODO: Продумать использование getWnd в таких случаях
                        getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
                    };
                    params.Person_id = person_data.Person_id;
                    params.PersonEvn_id = person_data.PersonEvn_id;
                    params.Server_id = person_data.Server_id;
					
                    getWnd('swPersonSearchWindow').hide();
                    getWnd('swEvnDtpDeathEditWindow').show(params);
                },
                personFirname: wnd.frameHeader.getForm().findField('Person_Firname').getValue(),
                personSecname: wnd.frameHeader.getForm().findField('Person_Secname').getValue(),
                personSurname: wnd.frameHeader.getForm().findField('Person_Surname').getValue(),
                searchMode: 'all'
            });
        } else {
            var selected_record = grid.getSelectionModel().getSelected();
            if (!selected_record) {
                return false;
            }
			
            var evn_dtp_death_id = selected_record.get('EvnDtpDeath_id');
            var person_id = selected_record.get('Person_id');
            var server_id = selected_record.get('Server_id');
			
            if (evn_dtp_death_id > 0 && person_id > 0 && server_id >= 0) {
                params.EvnDtpDeath_id = evn_dtp_death_id;
                params.onHide = function() {
                    grid.getView().focusRow(grid.getStore().indexOf(selected_record));
                };
                params.Person_id = person_id;
                params.Server_id = server_id;
				
                getWnd('swEvnDtpDeathEditWindow').show(params);
            }
        }

        return true;
    },
	
    /**
	 * Удаление извещения о раненом в ДТП
	 */
    deleteEvnDtpDeath: function() {
        var grid = this.frameGrid.getGrid();
		
        if (!grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDtpDeath_id')) {
            return false;
        }
		
        var record = grid.getSelectionModel().getSelected();
        var evn_dtp_death_id = record.get('EvnDtpDeath_id');
		
        sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if (buttonId == 'yes') {
                    Ext.Ajax.request({
                        callback: function(options, success, response) {
                            if (success) {
                                var response_obj = Ext.util.JSON.decode(response.responseText);

                                if ( response_obj.success == false ) {
                                    sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при удалении');
                                }
                                else {
                                    grid.getStore().remove(record);
									
                                    if (grid.getStore().getCount() == 0) {
                                        LoadEmptyRow(grid, 'data');
                                    }
                                }
								
                                grid.getView().focusRow(0);
                                grid.getSelectionModel().selectFirstRow();
                            } else {
                                if (response.Error_Msg) {
                                    sw.swMsg.alert('Ошибка', action.result.Error_Msg);
                                } else {
                                    sw.swMsg.alert('Ошибка', 'При удалении произошли ошибки [Тип ошибки: 1]');
                                }
								
                            }
                        },
                        params: {
                            EvnDtpDeath_id: evn_dtp_death_id
                        },
                        url: '/?c=EvnDtpDeath&m=deleteEvnDtpDeath'
                    });
                }
            },
            icon: Ext.MessageBox.QUESTION,
            msg: 'Удалить извещение?',
            title: 'Извещения о раненом в ДТП'
        });

        return true;
    },
	
    /**
	 *  Печать списка извещений
	 */
    printGrid: function() {
        var form = this.frameHeader.getForm();
        form.submit();
    },
	
    /**
	 *  Поиск и заполнение Grid'а
	 */
    doSearch: function(params) {
		
        if ( params && params['soc_card_id'] )
            var soc_card_id = params['soc_card_id'];
		
        var form = this.frameHeader;
		
		if ( form.isEmpty() ) {
			sw.swMsg.alert('Ошибка', 'Не заполнено ни одно поле', function() {
			});
			return false;
		}
		
        var grid = this.frameGrid.getGrid();
		
        var loadMask = new Ext.LoadMask(this.getEl(), {
            msg: "Подождите, идет поиск..."
        });
        loadMask.show();

        var post;

        if (soc_card_id) {
            post = {
                soc_card_id: soc_card_id,
                SearchFormType: post.SearchFormType
            };
        } else {
            post = getAllFormFieldValues(form);

            if (post.PersonCardStateType_id == null) {
                post.PersonCardStateType_id = 1;
            }
            if (post.PrivilegeStateType_id == null) {
                post.PrivilegeStateType_id = 1;
            }
            if (form.getForm().findField('MedStaffFactViz_id') != null) {
                var med_personal_viz_record = form.getForm().findField('MedStaffFactViz_id').getStore().getById(form.getForm().findField('MedStaffFactViz_id').getValue());

                if (med_personal_viz_record) {
                    post.MedPersonalViz_id = med_personal_viz_record.get('MedPersonal_id');
                }
            }
        }
		
        post.limit = 100;
        post.start = 0;
		
        grid.getStore().baseParams = post;
		
        if (form.getForm().isValid()) {
            this.frameGrid.ViewActions.action_refresh.setDisabled(false);
            grid.getStore().removeAll();
            grid.getStore().load({
                callback: function(records, options, success) {
                    loadMask.hide();
                },
                params: post
            });
        }
    },
	
    /**
	 *  Сброс параметров поиска
	 */
    doReset: function() {
        var form = this.frameHeader,
            grid = this.findById('DtpDeathGrid').getGrid();
		
        form.getForm().reset();
		
        if (form.getForm().findField('AttachLpu_id') != null) {
            form.getForm().findField('AttachLpu_id').fireEvent('change', form.getForm().findField('AttachLpu_id'), 0, 1);
        }
        if (form.getForm().findField('LpuRegion_id') != null) {
            form.getForm().findField('LpuRegion_id').lastQuery = '';
            form.getForm().findField('LpuRegion_id').getStore().clearFilter();
        }
        if (form.getForm().findField('PrivilegeType_id') != null) {
            form.getForm().findField('PrivilegeType_id').lastQuery = '';
            form.getForm().findField('PrivilegeType_id').getStore().filterBy(function(record) {
                if (record.get('PrivilegeType_Code') <= 500) {
                    return true;
                } else {
                    return false;
                }
            });
        }
        if (form.getForm().findField('LpuRegionType_id') != null) {
            form.getForm().findField('LpuRegionType_id').getStore().clearFilter();
        }
        if (form.getForm().findField('DirectClass_id') != null) {
            form.getForm().findField('DirectClass_id').fireEvent('change', form.getForm().findField('DirectClass_id'), null, 1);
        }
        if (form.getForm().findField('PersonCardStateType_id') != null) {
            form.getForm().findField('PersonCardStateType_id').fireEvent('change', form.getForm().findField('PersonCardStateType_id'), 1, 0);
        }
		
        if (form.getForm().findField('PrivilegeStateType_id') != null) {
            form.getForm().findField('PrivilegeStateType_id').fireEvent('change', form.getForm().findField('PrivilegeStateType_id'), 1, 0);
        }
		
        form.findByType('tabpanel')[0].setActiveTab(0);
        form.findByType('tabpanel')[0].getActiveTab().fireEvent('activate', form.findByType('tabpanel')[0].getActiveTab());


        grid.removeAll();
        grid.getStore().removeAll();
    },
	
    /**
	 *  Подсчет найденных записей
	 */
    getRecordsCount: function() {
        if (!this.frameHeader.getForm().isValid()) {
            sw.swMsg.alert('Поиск', 'Проверьте правильность заполнения полей на форме поиска');
            return false;
        }
		
        var loadMask = new Ext.LoadMask(this.getEl(), {
            msg: "Подождите, идет подсчет записей..."
        });
        loadMask.show();
		
        var post = getAllFormFieldValues(this.frameHeader);
		
        Ext.Ajax.request({
            callback: function(options, success, response) {
                loadMask.hide();
				
                if (success) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
					
                    if (response_obj.Records_Count != undefined) {
                        sw.swMsg.alert('Подсчет записей', 'Найдено записей: ' + response_obj.Records_Count);
                    } else {
                        sw.swMsg.alert('Подсчет записей', response_obj.Error_Msg);
                    }
                } else {
                    sw.swMsg.alert('Ошибка', 'При подсчете количества записей произошли ошибки');
                }
            },
            params: post,
            url: C_SEARCH_RECCNT
        });

        return true;
    },
	
    /**
	 * Инициализация компонента
	 */
    initComponent: function() {
        var wnd = this;
		
        // Кнопки для формы
        this.buttonsOnForm = [{
            text: BTN_FRMSEARCH,
            iconCls: 'search16',
            tabIndex: TABINDEX_EDDW + 1,
            handler: function() {
                wnd.doSearch();
            }
        }, {
            iconCls: 'resetsearch16',
            text: BTN_FRMRESET,
            tabIndex: TABINDEX_EDDW + 2,
            handler: function() {
                wnd.doReset();
            }
        }, {
            iconCls: 'print16',
            text: 'Печать списка',
            tabIndex: TABINDEX_EDDW + 3,
            handler: function() {
                wnd.printGrid();
            }
        }, {
            iconCls: 'count16',
            text: BTN_FRMCOUNT,
            tabIndex: TABINDEX_EDDW + 4,
            handler: function() {
                wnd.getRecordsCount();
            }
        }, '-', HelpButton(this, -1), {
            iconCls: 'cancel16',
            text: BTN_FRMCANCEL,
            tabIndex: TABINDEX_EDDW + 6,
            handler: function() {
                wnd.hide();
            },
            onShiftTabAction: function() {
                wnd.buttons[wnd.buttons.length - 2].focus();
            },
            onTabAction: function() {
                wnd.findById('EDDW_SearchFilterTabbar').getActiveTab().fireEvent('activate', wnd.findById('EDDW_SearchFilterTabbar').getActiveTab());
            }
        }];
		
        // Экшены для грида
        this.actionsOnGrid = [{
            name: 'action_add',
            disabled: false,
            handler: function() {
                wnd.openEditWindow('add');
            }
        }, {
            name: 'action_edit',
            disabled: false,
            handler: function() {
                wnd.openEditWindow('edit');
            }
        }, {
            name: 'action_view',
            disabled: false,
            handler: function() {
                wnd.openEditWindow('view');
            }
        }, {
            name: 'action_delete',
            disabled: false,
            handler: function() {
                wnd.deleteEvnDtpDeath();
            }
        }, {
            name: 'action_refresh',
            handler: function() {
                wnd.frameGrid.getGrid().getStore().reload();
            }
			
        }];
		
        this.keys = [{
            key: Ext.EventObject.INSERT,
            stopEvent: true,
            fn: function() {
                wnd.openEditWindow('add');
            }
        }, {
            alt: true,
            key: [Ext.EventObject.C, Ext.EventObject.J, Ext.EventObject.ONE, Ext.EventObject.NUM_ONE, Ext.EventObject.TWO, Ext.EventObject.NUM_TWO, Ext.EventObject.THREE, Ext.EventObject.NUM_THREE, Ext.EventObject.FOUR, Ext.EventObject.NUM_FOUR, Ext.EventObject.FIVE, Ext.EventObject.NUM_FIVE, Ext.EventObject.SIX, Ext.EventObject.NUM_SIX, Ext.EventObject.SEVEN, Ext.EventObject.NUM_SEVEN],
            stopEvent: true,
            fn: function(inp, e) {
                var search_filter_tabbar = wnd.findById('EDDW_SearchFilterTabbar');
                switch (e.getKey()) {
                    case Ext.EventObject.C:
                        wnd.doReset();
                        break;
                    case Ext.EventObject.J:
                        wnd.hide();
                        break;
                    case Ext.EventObject.NUM_ONE:
                    case Ext.EventObject.ONE:
                        search_filter_tabbar.setActiveTab(0);
                        break;
                    case Ext.EventObject.NUM_TWO:
                    case Ext.EventObject.TWO:
                        search_filter_tabbar.setActiveTab(1);
                        break;
                    case Ext.EventObject.NUM_THREE:
                    case Ext.EventObject.THREE:
                        search_filter_tabbar.setActiveTab(2);
                        break;
                    case Ext.EventObject.NUM_FOUR:
                    case Ext.EventObject.FOUR:
                        search_filter_tabbar.setActiveTab(3);
                        break;
                    case Ext.EventObject.NUM_FIVE:
                    case Ext.EventObject.FIVE:
                        search_filter_tabbar.setActiveTab(4);
                        break;
                    case Ext.EventObject.NUM_SIX:
                    case Ext.EventObject.SIX:
                        search_filter_tabbar.setActiveTab(5);
                        break;
                    case Ext.EventObject.NUM_SEVEN:
                    case Ext.EventObject.SEVEN:
                        search_filter_tabbar.setActiveTab(6);
                        break;
                }
            }
        }];
		
        this.tabDtpDeathSearch = {
            autoHeight: true,
            bodyStyle: 'margin-top: 15px;',
            border: false,
            layout: 'form',
            labelWidth: 220,
            title: '<u>6</u>. Извещение',
            listeners: {
                'activate': function(panel) {
                    wnd.frameHeader.getForm().findField('EvnDtpDeath_setDate_Range').focus(true, 250);
                }
            },
            items: [{
                fieldLabel: 'Дата заполнения извещения',
                name: 'EvnDtpDeath_setDate_Range',
                plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                width: 200,
                xtype: 'daterangefield',
                listeners: {
                    'keydown': function(inp, e) {
                        if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
                            e.stopEvent();
                            wnd.buttons[wnd.buttons.length - 1].focus();
                        }
                    }
                }
            }, {
                fieldLabel: 'Дата смерти',
                name: 'EvnDtpDeath_DeathDate_Range',
                plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                width: 200,
                xtype: 'daterangefield',
                listeners: {
                    'keydown': function(inp, e) {
                        if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
                            e.stopEvent();
                            wnd.buttons[wnd.buttons.length - 1].focus();
                        }
                    }
                }
			
            }]
        };
		
        this.frameHeader = getBaseSearchFiltersFrame({
            // allowPersonPeriodicSelect: true,
            tabPanelId: 'EDDW_SearchFilterTabbar',
            ownerWindow: wnd,
            searchFormType: 'EvnDtpDeath',
            tabs: [this.tabDtpDeathSearch]
        });
		
        this.fields = [{
            name: 'EvnDtpDeath_id',
            type: 'int',
            hidden: true
        }, {
            name: 'Person_id',
            type: 'int',
            hidden: true
        }, {
            name: 'Server_id',
            type: 'int',
            hidden: true
        }, {
            name: 'EvnDtpDeath_setDate',
            type: 'date',
            format: 'd.m.Y',
            header: 'Дата заполнения',
            width: 120
        }, {
            name: 'Person_Surname',
            type: 'string',
            header: 'Фамилия',
            hidden: true//,
            //id: 'autoexpand'
        }, {
            name: 'Person_Firname',
            type: 'string',
            header: 'Имя',
            hidden: true,
            width: 200
        }, {
            name: 'Person_Secname',
            type: 'string',
            header: 'Отчество',
            hidden: true,
            width: 200
        }, {
            name: 'Person_Fio',
            type: 'string',
            header: 'ФИО',
            id: 'autoexpand',
            width: 200
        }, {
            name: 'Person_Birthday',
            type: 'date',
            format: 'd.m.Y',
            header: 'Д/р',
            width: 120
        }, {
            name: 'Person_Sex',
            type: 'string',
            header: 'Пол',
            width: 90
        }, {
            name: 'EvnDtpDeath_DeathDate',
            type: 'date',
            format: 'd.m.Y',
            header: 'Дата смерти',
            width: 120
        }, {
            name: 'DiagDeath_Name',
            type: 'string',
            header: 'Непосредственная причина смерти',
            width: 250
        }];
		
        this.frameGrid = new sw.Promed.ViewFrame({
            actions: this.actionsOnGrid,
            toolbar: true,
            autoExpandColumn: 'autoexpand',
            id: 'DtpDeathGrid',
            region: 'center',
            autoExpandMin: 150,
            autoLoadData: false,
            dataUrl: C_SEARCH,
            pageSize: 100,
            paging: true,
            root: 'data',
            totalProperty: 'totalCount',
            stringfields: this.fields
        })
		
        Ext.apply(this, {
            buttons: this.buttonsOnForm,
            items: [this.frameHeader, this.frameGrid]
        });
		
        sw.Promed.swEvnDtpDeathWindow.superclass.initComponent.apply(this, arguments);
    },
	
    /**
	 *  Функция открытия окна
	 */
    show: function() {
        sw.Promed.swEvnDtpDeathWindow.superclass.show.apply(this, arguments);
        this.doReset();
		
        var formSearch = this.frameHeader.getForm();
		
        formSearch.getEl().dom.action = "/?c=Search&m=printSearchResults";
        formSearch.getEl().dom.method = "post";
        formSearch.getEl().dom.target = "_blank";
        formSearch.standardSubmit = true;
    }
});
