/**
 * swEvnDtpWoundWindow - окно просмотра извещений о раненых в ДТП.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Alexander "Alf" Arefyev (avaref@gmail.com)
 * @version      30.03.2010
 * @comment      Префикс для id компонентов EDWW (swEvnDtpWoundWindow)
 *
 */
sw.Promed.swEvnDtpWoundWindow = Ext.extend(sw.Promed.BaseForm, {
    title: lang['izvescheniya_o_ranenyih_v_dtp'],
    iconCls: 'stac-accident-injured16',
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
            sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
            return false;
        }
		
        if (getWnd('swEvnDtpWoundEditWindow').isVisible()) {
            sw.swMsg.alert(lang['soobschenie'], lang['okno_izvescheniya_o_ranenom_v_dtp_uje_otkryito']);
            return false;
        }
		
        var params = new Object();
        var grid = this.frameGrid.getGrid();
		
        params.action = action;
		
        params.callback = function(data) {
            //swalert(data);
            if (!data || !data.evnDtpWoundData) {
                return false;
            }
			
            // Обновить запись в grid
            var record = grid.getStore().getById(data.evnDtpWoundData.EvnDtpWound_id);
			
            if (record) {
                record.set('EvnDtpWound_id', data.evnDtpWoundData.EvnPS_id);
                record.set('Person_Birthday', data.evnDtpWoundData.Person_Birthday);
                record.set('Person_Surname', data.evnDtpWoundData.Person_Surname);
                record.set('Person_Firname', data.evnDtpWoundData.Person_Firname);
                record.set('Person_Secname', data.evnDtpWoundData.Person_Secname);
                record.set('Person_id', data.evnDtpWoundData.Person_id);
                record.set('PersonEvn_id', data.evnDtpWoundData.PersonEvn_id);
                record.set('Server_id', data.evnDtpWoundData.Server_id);
				
                record.commit();
            } else {
                if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDtpWound_id')) {
                    grid.getStore().removeAll();
                }
				
                grid.getStore().loadData({
                    'data': [data.evnDtpWoundData]
                }, true);
            }
			
            grid.getStore().each(function(record) {
                if (record.get('Person_id') == data.evnDtpWoundData.Person_id && record.get('Server_id') == data.evnDtpWoundData.Server_id) {
                    record.set('Person_Birthday', data.evnDtpWoundData.Person_Birthday);
                    record.set('Person_Surname', data.evnDtpWoundData.Person_Surname);
                    record.set('Person_Firname', data.evnDtpWoundData.Person_Firname);
                    record.set('Person_Secname', data.evnDtpWoundData.Person_Secname);
					
                    record.commit();
                }
            });

            return true;
        };
		
		
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
                    getWnd('swEvnDtpWoundEditWindow').show(params);
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
			
            var evn_dtp_wound_id = selected_record.get('EvnDtpWound_id');
            var person_id = selected_record.get('Person_id');
            var server_id = selected_record.get('Server_id');
			
            if (evn_dtp_wound_id > 0 && person_id > 0 && server_id >= 0) {
                params.EvnDtpWound_id = evn_dtp_wound_id;
                params.onHide = function() {
                    grid.getView().focusRow(grid.getStore().indexOf(selected_record));
                };
                params.Person_id = person_id;
                params.Server_id = server_id;
				
                getWnd('swEvnDtpWoundEditWindow').show(params);
            }
        }

        return true;
    },
	
    /**
	 * Удаление извещения о раненом в ДТП
	 */
    deleteEvnDtpWound: function() {
        var grid = this.frameGrid.getGrid();
		
        if (!grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDtpWound_id')) {
            return false;
        }
		
        var record = grid.getSelectionModel().getSelected();
        var evn_dtp_wound_id = record.get('EvnDtpWound_id');
		
        sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if (buttonId == 'yes') {
                    Ext.Ajax.request({
                        callback: function(options, success, response) {
                            if (success) {
                                var response_obj = Ext.util.JSON.decode(response.responseText);

                                if ( response_obj.success == false ) {
                                    sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii']);
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
                                    sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                                } else {
                                    sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_proizoshli_oshibki_[tip_oshibki_1]']);
                                }
								
                            }
                        },
                        params: {
                            EvnDtpWound_id: evn_dtp_wound_id
                        },
                        url: '/?c=EvnDtp&m=deleteEvnDtpWound'
                    });
                }
            },
            icon: Ext.MessageBox.QUESTION,
            msg: lang['udalit_izveschenie'],
            title: lang['izvescheniya_o_ranenom_v_dtp']
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
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
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
            grid = this.findById('DtpWoundGrid').getGrid();
		
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
            sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
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
                        sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
                    } else {
                        sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
                    }
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
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
            tabIndex: TABINDEX_EDWW + 1,
            handler: function() {
                wnd.doSearch();
            }
        }, {
            iconCls: 'resetsearch16',
            text: BTN_FRMRESET,
            tabIndex: TABINDEX_EDWW + 2,
            handler: function() {
                wnd.doReset();
            }
        }, {
            iconCls: 'print16',
            text: lang['pechat_spiska'],
            tabIndex: TABINDEX_EDWW + 3,
            handler: function() {
                wnd.printGrid();
            }
        }, {
            iconCls: 'count16',
            text: BTN_FRMCOUNT,
            tabIndex: TABINDEX_EDWW + 4,
            handler: function() {
                wnd.getRecordsCount();
            }
        }, '-', HelpButton(this, -1), {
            iconCls: 'cancel16',
            text: BTN_FRMCANCEL,
            tabIndex: TABINDEX_EDWW + 6,
            handler: function() {
                wnd.hide();
            },
            onShiftTabAction: function() {
                wnd.buttons[wnd.buttons.length - 2].focus();
            },
            onTabAction: function() {
                wnd.findById('EDWW_SearchFilterTabbar').getActiveTab().fireEvent('activate', wnd.findById('EDWW_SearchFilterTabbar').getActiveTab());
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
                wnd.deleteEvnDtpWound();
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
                var search_filter_tabbar = wnd.findById('EDWW_SearchFilterTabbar');
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
		
        this.tabDtpWoundSearch = {
            autoHeight: true,
            bodyStyle: 'margin-top: 15px;',
            border: false,
            layout: 'form',
            labelWidth: 220,
            title: '<u>6</u>. Извещение',
            listeners: {
                'activate': function(panel) {
                    wnd.frameHeader.getForm().findField('EvnDtpWound_setDate_Range').focus(true, 250);
                }
            },
            items: [{
                fieldLabel: lang['data_zapolneniya_izvescheniya'],
                name: 'EvnDtpWound_setDate_Range',
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
            tabPanelId: 'EDWW_SearchFilterTabbar',
            ownerWindow: wnd,
            searchFormType: 'EvnDtpWound',
            tabs: [this.tabDtpWoundSearch]
        });
		
        this.fields = [{
            name: 'EvnDtpWound_id',
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
            name: 'EvnDtpWound_setDate',
            type: 'date',
            format: 'd.m.Y',
            header: lang['data_zapolneniya'],
            width: 90
        }, {
            name: 'Person_Surname',
            type: 'string',
            header: lang['familiya'],
            id: 'autoexpand'
        }, {
            name: 'Person_Firname',
            type: 'string',
            header: lang['imya'],
            width: 200
        }, {
            name: 'Person_Secname',
            type: 'string',
            header: lang['otchestvo'],
            width: 200
        }, {
            name: 'Person_Birthday',
            type: 'date',
            format: 'd.m.Y',
            header: lang['d_r'],
            width: 90
        }];
		
        this.frameGrid = new sw.Promed.ViewFrame({
            actions: this.actionsOnGrid,
            toolbar: true,
            id: 'DtpWoundGrid',
            autoExpandColumn: 'autoexpand',
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
		
        sw.Promed.swEvnDtpWoundWindow.superclass.initComponent.apply(this, arguments);
    },
	
    /**
	 *  Функция открытия окна
	 */
    show: function() {
        sw.Promed.swEvnDtpWoundWindow.superclass.show.apply(this, arguments);
        this.doReset();
		
        var formSearch = this.frameHeader.getForm();
		
        formSearch.getEl().dom.action = "/?c=Search&m=printSearchResults";
        formSearch.getEl().dom.method = "post";
        formSearch.getEl().dom.target = "_blank";
        formSearch.standardSubmit = true;
    }
});
