/**
 * swElectronicInfomatEditWindow - инфомат добавление\редактирование\просмотр
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 * @author       Sysolin Maksim
 * @version      08.2017
 */
/*NO PARSE JSON*/
sw.Promed.swElectronicInfomatEditWindow = Ext.extend(sw.Promed.BaseForm,
    {
        maximizable: false,
        maximized: true,
        height: 600,
        width: 900,
        id: 'swElectronicInfomatEditWindow',
        title: 'Инфомат',
        layout: 'border',
        resizable: true,
        formAction: null,
		ElectronicInfomatButtons: [
			{ ElectronicInfomatButton_id: 1, ElectronicInfomatButton_Code: 1, ElectronicInfomatButton_Name: 'Зарегистрироваться' },
			{ ElectronicInfomatButton_id: 2, ElectronicInfomatButton_Code: 2, ElectronicInfomatButton_Name: 'Записаться' },
			{ ElectronicInfomatButton_id: 3, ElectronicInfomatButton_Code: 3, ElectronicInfomatButton_Name: 'Без записи' },
                        { ElectronicInfomatButton_id: 5, ElectronicInfomatButton_Code: 4, ElectronicInfomatButton_Name: 'Вызов врача' }
		],
        listeners: {

            'hide': function(wnd) {
                wnd.hide();
            }
        },
        onGridRowSelect: function(grid) {

            var wnd = this;

            if (wnd.formAction && wnd.formAction != 'view') {
                grid.ViewActions.action_delete.setDisabled(false);
            }
        },
        clearGridFilter: function(grid) { //очищаем фильтры (необходимо делать всегда перед редактированием store)

            grid.getGrid().getStore().clearFilter();
        },
        setGridFilter: function(grid) { //скрывает удаленные записи
            grid.getGrid().getStore().filterBy(function(record){
                return (record.get('state') != 'delete');
            });
        },
        deleteLpuOfficeAssign: function(){

            var wnd = this,
                grid = wnd.LpuBuildingOfficeInfomatGrid.getGrid(),
                record = grid.getSelectionModel().getSelected();

            if (record) {
                sw.swMsg.show({
                    icon: Ext.MessageBox.QUESTION,
                    msg: langs('Вы хотите удалить запись?'),
                    title: langs('Подтверждение'),
                    buttons: Ext.Msg.YESNO,
                    fn: function(buttonId, text, obj) {

                        if ('yes' == buttonId) {
                            Ext.Ajax.request({
                                url: '/?c=LpuBuildingOffice&m=deleteLpuBuildingOfficeInfomat',
                                params: {
                                    LpuBuildingOfficeInfomat_id: record.get('LpuBuildingOfficeInfomat_id')
                                },
                                success: function (resp) {
                                    wnd.LpuBuildingOfficeInfomatGrid.refreshRecords(null,0)
                                },
                                error: function (elem, resp) {
                                    if (!resp.result.success) {
                                        Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
                                    }
                                }
                            });
                        } else {
                            return false;
                        }
                    }
                });
            }
        },
        deleteGridRecord: function(){

            var wnd = this,
                view_frame = this.ElectronicInfomatLinkGrid,
                grid = view_frame.getGrid(),
                selected_record = grid.getSelectionModel().getSelected();

            sw.swMsg.show({
                icon: Ext.MessageBox.QUESTION,
                msg: lang['vyi_hotite_udalit_zapis'],
                title: lang['podtverjdenie'],
                buttons: Ext.Msg.YESNO,
                fn: function(buttonId, text, obj) {

                    if ('yes' == buttonId) {
                        if (selected_record.get('state') == 'add') {
                            grid.getStore().remove(selected_record);
                        } else {
                            selected_record.set('state', 'delete');
                            selected_record.commit();
                            wnd.setGridFilter(view_frame);
                        }
                    } else {
                        if (grid.getStore().getCount()>0) {
                            grid.getView().focusRow(0);
                        }
                    }
                }
            });
        },
        openElectronicInfomatProfileEditWindow: function(action) {

            var wnd = this,
                form = this.formEditPanel.getForm(),
                grid = this.ProfilesLinkGrid.ViewGridPanel,
                profiles = {};

            var profilesGridStore = grid.getStore();
            profilesGridStore.each(function(r) {
                profiles[r.get('ElectronicInfomatProfile_id')] = {
                    profile_id: r.get('LpuSectionProfile_id'),
                    position: r.get('ElectronicInfomatProfile_Position'),
                    profileName: r.get('ProfileSpec_Name'),
                    specName: r.get('MedSpecOms_id')
                };
            })

            var params = {
                action: action,
                ElectronicInfomat_id: form.findField('ElectronicInfomat_id').getValue(),
                profiles: profiles,
                callback: function(){ wnd.ProfilesLinkGrid.refreshRecords(null, 0);}
            }

            var selected_record =  grid.getSelectionModel().getSelected();
            if ((action == 'view' || action == 'edit') &&  selected_record) {
                params.ElectronicInfomatProfile_id = selected_record.get('ElectronicInfomatProfile_id');
            }

            getWnd('swElectronicInfomatProfileEditWindow').show(params);
        },
        openElectronicInfomatLinkEditWindow: function(action) {

            var wnd = this,
                form = this.formEditPanel.getForm();

            var params = {};

            params.Lpu_id = form.findField('Lpu_id').getValue();
            params.LpuBuilding_id = form.findField('LpuBuilding_id').getValue();

            if (!params.Lpu_id) { log('Не указан lpu_id'); return false; }

            params.action = action;
            var view_frame = wnd.ElectronicInfomatLinkGrid,
                store = view_frame.getGrid().getStore();

            if (action === 'add') {

                params.onSave = function(data) {

                    var record_count = store.getCount();
                    if ( record_count === 1 && !store.getAt(0).get('ElectronicInfomatLink_id') ) {
                        view_frame.removeAll({addEmptyRecord: false});
                    }

                    var record = new Ext.data.Record.create(view_frame.jsonData['store']);
                    wnd.clearGridFilter(view_frame);

                    data.ElectronicInfomatLink_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
                    data.state = 'add';

                    store.insert(record_count, new record(data));
                    wnd.setGridFilter(view_frame);
                };

                getWnd('swElectronicInfomatLinkEditWindow').show(params);
            }
        },
        openLpuBuildingOfficeAssignWindow: function(action) {

            var wnd = this,
                grid = this.LpuBuildingOfficeInfomatGrid.getGrid(),
                form = this.formEditPanel.getForm();

            var params = {};
            params.Lpu_id = form.findField('Lpu_id').getValue();
            params.LpuBuilding_id = form.findField('LpuBuilding_id').getValue();
            params.ElectronicInfomat_id = form.findField('ElectronicInfomat_id').getValue();

            params.onSave = function() {
                wnd.LpuBuildingOfficeInfomatGrid.refreshRecords(null,0)
            };

            if (action === 'edit') {
                var record = grid.getSelectionModel().getSelected();
                params.LpuBuildingOfficeInfomat_id = record.get('LpuBuildingOfficeInfomat_id');
            }

            if (!params.Lpu_id) {
                log('Не указан идентификатор МО');
                return false;
            }

            if (!params.ElectronicInfomat_id) {
                log('Не указан идентификатор табло');
                return false;
            }

            params.action = action;
            getWnd('swLpuBuildingOfficeAssign').show(params);
        },
        doSave: function(options) {

            if (typeof options != 'object') {
                options = new Object();
            }

            var wnd = this,
                form = this.formEditPanel.getForm(),
                loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});

            if (!form.isValid()) {

                sw.swMsg.show( {

                    buttons: Ext.Msg.OK,
                    fn: function() {
                        wnd.formEditPanel.getFirstInvalidEl().focus(false);
                    },
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
                return false;
            }

            var params = {};

            if (form.findField('Lpu_id').disabled) {
                params.Lpu_id = form.findField('Lpu_id').getValue();
            }

            params.queueData = wnd.ElectronicInfomatLinkGrid.getJSONChangedData();

			var ElectronicInfomatButtons = {};

			wnd.findById(wnd.id + 'ElectronicInfomatButtons').items.each(function(item) {
				if ( item.xtype == 'checkbox' ) {
					ElectronicInfomatButtons[item.name] = (item.getValue() == true ? 2 : 1);
				}
			});

			params.ElectronicInfomatButtons = Ext.util.JSON.encode(ElectronicInfomatButtons);

            loadMask.show();

            form.submit({

                failure: function(result_form, action) {
                    loadMask.hide();
                },
                params: params,
                success: function(result_form, action) {

                    loadMask.hide();

                    if (action.result) {

                        wnd.hide();
                        wnd.returnFunc();

                    } else {
                        Ext.Msg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
                    }
                }.createDelegate(this)
            });
        },
        initComponent: function() {

            var wnd = this;

            this.printTypeCombo = new Ext.form.ComboBox({			
                fieldLabel: 'Тип печати',
                id: wnd.id + 'ElectronicInfomat_IsPrintService',
                width: 250,
                triggerAction: 'all',
                store: [
                        [1, langs('Через браузер')],
                        [2, langs('Из службы')]				
                ],
                hiddenName: 'ElectronicInfomat_IsPrintService',
                name: 'ElectronicInfomat_IsPrintService_Name'
            });
                
            this.formEditPanel = new Ext.FormPanel({
                region: 'north',
                labelAlign: 'right',
                layout: 'form',
                autoHeight: true,
                labelWidth: 100,
                frame: true,
                border: false,
                items: [{
                    border: false,
                    layout: 'column',
                    anchor: '10',
                    items: [{
                        layout: 'form',
                        columnWidth: .50,
                        border: false,
                        items: [
                            {
                                name: 'ElectronicInfomat_id',
                                xtype: 'hidden'
                            },{
                                xtype: 'fieldset',
                                autoHeight: true,
                                collapsible: false,
                                title: 'Конфигурация',
                                style: 'margin-top: 5px; margin-right: 10px; height: 295px;',
                                labelWidth: 130,
                                items: [
                                    {
                                        fieldLabel: 'МО',
                                        hiddenName: 'Lpu_id',
                                        xtype: 'swlpusearchcombo',
                                        allowBlank: false,
                                        listWidth: 400,
                                        width: 350,
                                        listeners: {
                                            'change': function (combo, newValue, oldValue) {
                                                var buildingCombo = wnd.formEditPanel.getForm().findField('LpuBuilding_id');
                                                buildingCombo.clearValue();
                                                buildingCombo.getStore().baseParams.Lpu_id = newValue;
                                                buildingCombo.getStore().load();
                                            }
                                        }
                                    },{
                                        fieldLabel: 'Подразделение',
                                        hiddenName: 'LpuBuilding_id',
                                        xtype: 'swlpubuildingcombo',
                                        allowBlank: true,
                                        listWidth: 400,
                                        width: 350,
                                    },{
                                        name: 'ElectronicInfomat_Code',
                                        fieldLabel: 'Код',
                                        xtype: 'textfield',
                                        allowBlank: false,
                                        width: 150
                                    },{
                                        name: 'ElectronicInfomat_Name',
                                        fieldLabel: 'Наименование',
                                        xtype: 'textfield',
                                        allowBlank: false,
                                        width: 350
                                    },{
                                        name: 'ElectronicInfomat_begDate',
                                        fieldLabel: 'Дата начала',
                                        xtype: 'swdatefield',
                                        allowBlank: false,
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
                                        width: 100
                                    },{
                                        name: 'ElectronicInfomat_endDate',
                                        fieldLabel: 'Дата окончания',
                                        xtype: 'swdatefield',
                                        allowBlank: true,
                                        plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                                        width: 100
                                    },
                                    wnd.printTypeCombo
                                ]
                            }
                        ]
                    },{
                        layout: 'form',
                        columnWidth: .50,
                        border: false,
                        items: [{
                            xtype: 'fieldset',
                            autoHeight: true,
                            collapsible: false,
                            title: 'Опции',
                            style: 'margin-top: 5px; margin-right: 5px; height: 295px;',
                            items: [
                                {
                                    name: 'ElectronicInfomat_isPrintOut',
                                    xtype: 'checkbox',
                                    hideLabel: true,
                                    boxLabel: 'Печать талона записи',
                                },{
                                    name: 'ElectronicInfomat_IsAllSpec',
                                    xtype: 'checkbox',
                                    hideLabel: true,
                                    boxLabel: 'Отображать сгруппированные специальности',
                                },{
                                    xtype: 'fieldset',
                                    autoHeight: true,
                                    collapsible: false,
                                    title: 'Кнопки стартового экрана',
                                    id: wnd.id + 'ElectronicInfomatButtons',
                                    style: 'margin-top: 15px; margin-right: 5px; height: 95px;',
                                    labelWidth: 1,
                                    items: []
                                },{
                                    name: 'ElectronicInfomat_StartPage',
                                    fieldLabel: 'Стартовая страница',
                                    labelStyle: 'width: 120px !important;',
                                    xtype: 'textfield',
                                    allowBlank: true,
                                    width: 300
                                }
                            ]
                        }]
                    }]
				}],
                reader: new Ext.data.JsonReader({}, [
                    { name: 'ElectronicInfomat_id' },
                    { name: 'Lpu_id' },
                    { name: 'LpuBuilding_id' },
                    { name: 'ElectronicInfomat_Code' },
                    { name: 'ElectronicInfomat_Name' },
                    { name: 'ElectronicInfomat_StartPage' },
                    { name: 'ElectronicInfomat_begDate' },
                    { name: 'ElectronicInfomat_endDate' },
                    { name: 'ElectronicInfomat_isPrintOut' },
                    { name: 'ElectronicInfomat_IsAllSpec' }
                ]),
                url: '/?c=ElectronicInfomat&m=save'
            });

            this.ElectronicInfomatLinkGrid = new sw.Promed.ViewFrame({
                id: 'ElectronicInfomatLinkGrid',
                title: 'Электронные очереди',
                object: 'ElectronicInfomatLink',
                dataUrl: '/?c=ElectronicInfomat&m=loadElectronicInfomatQueues',
                autoLoadData: false,
                paging: true,
                totalProperty: 'totalCount',
                region: 'center',
                height: 200,
                toolbar: true,
                useEmptyRecord: false,
                stringfields: [
                    {name: 'ElectronicInfomatLink_id', type: 'int', header: 'ID', key: true, hidden: true},
                    {name: 'ElectronicQueueInfo_Code', header: 'Код', width: 100},
                    {name: 'ElectronicQueueInfo_Name', header: 'Наименование', width: 150, id: 'autoexpand'},
                    {name: 'ElectronicQueueInfo_id', header: 'Идентификатор очереди', hidden: true},
                    {name: 'LpuBuilding_Name', header: 'Подразделение', width: 200, },
                    {name: 'LpuSection_Name', header: 'Отделение', width: 200,},
                    {name: 'MedService_Name', header: 'Служба', width: 200}
                ],
                actions: [
                    {name:'action_add', handler: function() { wnd.openElectronicInfomatLinkEditWindow('add'); }},
                    {name:'action_edit',  disabled: true, hidden: true, handler: function() { wnd.openElectronicInfomatLinkEditWindow('edit'); }},
                    {name:'action_view',  disabled: true, hidden: true, handler: function() { wnd.openElectronicInfomatLinkEditWindow('view'); }},
                    {name:'action_delete', handler: function() { wnd.deleteGridRecord() }},
                    {name:'action_print', disabled: true, hidden: true},
                    {name:'action_refresh', hidden: true}
                ],
                onRowSelect: function(sm, rowIdx, record) {
                    wnd.onGridRowSelect(this);
                },
                getChangedData: function(){ //возвращает новые и измненные показатели
                    var data = new Array();
                    wnd.clearGridFilter(this);
                    this.getGrid().getStore().each(function(record) {
                        if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete') {
                            data.push(record.data);
                        }
                    });
                    wnd.setGridFilter(this);
                    return data;
                },
                getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
                    var dataObj = this.getChangedData();
                    return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
                }
            });

            this.ProfilesLinkGrid = new sw.Promed.ViewFrame({
                id: 'ProfilesLinkGrid',
                title: 'Основные специальности',
                object: 'ElectronicInfomatProfile',
                dataUrl: '/?c=ElectronicInfomat&m=loadElectronicInfomatProfiles',
                autoLoadData: false,
                paging: true,
                totalProperty: 'totalCount',
                autoExpandColumn: 'autoexpand',
                region: 'center',
                toolbar: true,
                useEmptyRecord: false,
                stringfields: [
                    {name: 'ElectronicInfomatProfile_id', type: 'int', header: 'ID', key: true, hidden: true},
                    {name: 'LpuSectionProfile_id', hidden: true },
                    {name: 'ElectronicInfomatProfile_Position', header: 'Позиция отображения', width: 200 },
                    {name: 'ProfileSpec_Name', header: 'Профиль', width: 400, id: 'autoexpand'},
                    {name: 'MedSpecOms_id', hidden: true },
                    {name: 'MedSpecOms_Name', header: 'Специальность', width: 400, id: 'autoexpand'},
                    {name: 'LpuSectionProfile_Name', hidden: true, header: 'Наименование', width: 400}
                ],
                actions: [
                    {name:'action_add', handler: function() { wnd.openElectronicInfomatProfileEditWindow('add'); }},
                    {name:'action_edit', handler: function() { wnd.openElectronicInfomatProfileEditWindow('edit'); }},
                    {name:'action_view', handler: function() { wnd.openElectronicInfomatProfileEditWindow('view'); }},
                    {name:'action_print', disabled: true, hidden: true}
                ]
            });

            this.LpuBuildingOfficeInfomatGrid = new sw.Promed.ViewFrame({
                id: 'LpuBuildingOfficeInfomatGrid',
                object: 'LpuBuildingOfficeInfomatGrid',
                dataUrl: '/?c=LpuBuildingOffice&m=loadLpuBuildingOfficeInfomat',
                autoLoadData: false,
                paging: true,
                totalProperty: 'totalCount',
                region: 'center',
                toolbar: true,
                useEmptyRecord: false,
                stringfields: [
                    {name: 'LpuBuildingOfficeInfomat_id', type: 'int', header: 'ID', key: true, hidden: true},
                    {name: 'LpuBuildingOffice_id', type: 'int', hidden: true},
                    {name: 'ElectronicInfomat_id', type: 'int', hidden: true},
                    {name: 'LpuBuildingOffice_Number', header: 'Номер кабинета', width: 100},
                    {name: 'LpuBuildingOffice_Name', header: 'Наименование кабинета', width: 300},
                    {name: 'LpuBuildingOffice_Comment', header: 'Комментарий', width: 150, id: 'autoexpand'},
                    {name: 'LpuBuildingOfficeInfomat_begDT', header: 'Дата начала', width: 100},
                    {name: 'LpuBuildingOfficeInfomat_endDT', header: 'Дата окончания', width: 100}
                ],
                actions: [
                    {name:'action_add', handler: function() { wnd.openLpuBuildingOfficeAssignWindow('add'); }},
                    {name:'action_edit',  handler: function() { wnd.openLpuBuildingOfficeAssignWindow('edit'); }},
                    {name:'action_view',  disabled: true, hidden: true, handler: function() { wnd.openLpuBuildingOfficeAssignWindow('view'); }},
                    {name:'action_delete', handler: function() { wnd.deleteLpuOfficeAssign() }},
                    {name:'action_print', disabled: true, hidden: true},
                    {name:'action_refresh'}
                ],
                onRowSelect: function(sm, rowIdx, record) {
                    wnd.onGridRowSelect(this);
                },
                getChangedData: function(){ //возвращает новые и измненные показатели
                    var data = new Array();
                    wnd.clearGridFilter(this);
                    this.getGrid().getStore().each(function(record) {
                        if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete') {
                            data.push(record.data);
                        }
                    });
                    wnd.setGridFilter(this);
                    return data;
                },
                getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
                    var dataObj = this.getChangedData();
                    return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
                }
            });

            this.tabPanel = new Ext.TabPanel({
                activeTab: 0,
                id: wnd.id + 'TabPanel',
                layoutOnTabChange: true,
                region: 'center',
                height: 530,
                border: false,
                items:
                    [{
                        title: "Список ЭО",
                        layout: 'border',
                        id: "tab_eqlink",
                        items: [
                            this.ElectronicInfomatLinkGrid
                        ]
                    },
                        {
                            title: "Основные специальности",
                            layout: 'border',
                            id: 'tab_profilelink',
                            items: [
                                this.ProfilesLinkGrid
                            ]
                        },
                        {
                            title: "Cписок кабинетов",
                            layout: 'border',
                            id: 'tab_roomlink',
                            items: [
                                this.LpuBuildingOfficeInfomatGrid
                            ]
                        },
                    ],
                listeners:
                    {
                        tabchange: function(tab, panel)
                        {
                            // будет...
                        }
                    }
            });

            this.formPanel = new Ext.Panel({
                region: 'center',
                labelAlign: 'right',
                layout: 'border',
                labelWidth: 50,
                border: false,
                items: [
                    this.formEditPanel,
                    this.tabPanel
                ]
            });

            Ext.apply(this, {
                items: [
                    wnd.formPanel
                ],
                buttons: [{
                    text: BTN_FRMSAVE,
                    iconCls: 'save16',
                    handler: function() {
                        wnd.doSave();
                    }
                }, {
                    text: '-'
                },
				HelpButton(this, TABINDEX_RRLW + 13),
				{
					iconCls: 'close16',
					tabIndex: TABINDEX_RRLW + 14,
					handler: function() {
						wnd.hide();
					},
					text: BTN_FRMCLOSE
				}]
            });

            sw.Promed.swElectronicInfomatEditWindow.superclass.initComponent.apply(this, arguments);
        },
        setFieldsDisabled: function(d) {
            var form = this.formEditPanel.getForm();
            form.items.each(function(f)
            {
                if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
                {
                    f.setDisabled(d);
                }
            });
            this.buttons[0].show();
            this.buttons[0].setDisabled(d);
            this.ElectronicInfomatLinkGrid.setReadOnly(d);
        },
        disableGridActions: function(params) {

            if ( typeof params != 'object' ) { params = new Object(); }
            if (!params.grid) return false;

            var standardActions = ['action_add', 'action_edit', 'action_view', 'action_delete', 'action_refresh'];
            standardActions.forEach(function(action) {
                params.grid.setActionDisabled(action, params.isDisable);
            });
        },
        show: function() {

            sw.Promed.swElectronicInfomatEditWindow.superclass.show.apply(this, arguments);

            var wnd = this,
                form = this.formEditPanel.getForm(),
                grid = this.ElectronicInfomatLinkGrid,
                grid_rooms = this.LpuBuildingOfficeInfomatGrid;

            wnd.formAction = null;

            form.reset();
            grid.getGrid().getStore().baseParams = {};
            grid.getGrid().getStore().removeAll();

            grid_rooms.getGrid().getStore().baseParams = {};
            grid_rooms.getGrid().getStore().removeAll();

            if (arguments[0]['action']) {
                this.action = arguments[0]['action'];
                wnd.formAction = this.action;
            }

            if (arguments[0]['callback']) {
                this.returnFunc = arguments[0]['callback'];
            }

            var profilesGridStore = this.ProfilesLinkGrid.ViewGridPanel.getStore();
            profilesGridStore.removeAll();

            if (arguments[0]['ElectronicInfomat_id']) {

                this.ElectronicInfomat_id = arguments[0]['ElectronicInfomat_id'];
                profilesGridStore.baseParams.ElectronicInfomat_id = this.ElectronicInfomat_id;
                profilesGridStore.load();

            } else { this.ElectronicInfomat_id = null;}

            wnd.disableGridActions({
                grid: this.ProfilesLinkGrid,
                isDisable: (wnd.action == 'edit') ? false : true
            })

            this.setFieldsDisabled(this.action == 'view');
            if (isLpuAdmin() && !isSuperAdmin()) { form.findField('Lpu_id').disable(); }

			if ( wnd.findById(wnd.id + 'ElectronicInfomatButtons').items.items.length == 0 ) {
				for ( var i = 0; i < wnd.ElectronicInfomatButtons.length; i++ ) {
					wnd.findById(wnd.id + 'ElectronicInfomatButtons').add({
						boxLabel: wnd.ElectronicInfomatButtons[i].ElectronicInfomatButton_Name,
						fieldLabel: '',
						labelSeparator: '',
						name: 'ElectronicInfomatButton' + wnd.ElectronicInfomatButtons[i].ElectronicInfomatButton_id,
						xtype: 'checkbox'
					});
				}

				wnd.findById(this.id + 'ElectronicInfomatButtons').doLayout();
				wnd.formEditPanel.doLayout();
				wnd.doLayout();
			}

            switch (this.action){

                case 'add':
                    this.setTitle('Инфомат: Добавление');

					this.findById(this.id + 'ElectronicInfomatButtons').items.each(function(item) {
						if ( item.xtype == 'checkbox' ) {
							item.setValue(true);
						}
					});

                    form.findField('ElectronicInfomat_isPrintOut').setValue(true);

                    if (isLpuAdmin() && !isSuperAdmin()) {
                        form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
                        form.findField('Lpu_id').fireEvent('change', form.findField('Lpu_id'), form.findField('Lpu_id').getValue());
                        form.findField('ElectronicInfomat_Code').focus(true, 100);
                    } else {
                        form.findField('Lpu_id').focus(true, 100);
                    }

                    break;

                case 'edit':
                    this.setTitle('Инфомат: Редактирование');
                    //form.findField('Lpu_id').setDisabled(true);
                    break;

                case 'view':
                    this.setTitle('Инфомат: Просмотр');
                    break;
            }

            if (this.action != 'add') {

                var loadMask = new Ext.LoadMask(this.getEl(), {
                    msg: "Подождите, идет загрузка..."
                });

                loadMask.show();

                form.load({
                    url: '/?c=ElectronicInfomat&m=load',
                    params: {
                        ElectronicInfomat_id: wnd.ElectronicInfomat_id
                    },
                    success: function (form, action) {

						var ElectronicInfomatButtons = new Array();
                                                
                        if (action.reader.jsonData[0].ElectronicInfomat_IsPrintService > 0) {
                            var ps_item = wnd.findById(wnd.id + 'ElectronicInfomat_IsPrintService');
                            ps_item.setValue(action.reader.jsonData[0].ElectronicInfomat_IsPrintService);
                        }

						if ( !Ext.isEmpty(action.reader.jsonData[0].ElectronicInfomatButton_List) ) {
							ElectronicInfomatButtons = action.reader.jsonData[0].ElectronicInfomatButton_List.split(', ');
						}

						wnd.findById(wnd.id + 'ElectronicInfomatButtons').items.each(function(item) {
							var id;

							if ( item.xtype == 'checkbox' ) {
								id = item.name.replace('ElectronicInfomatButton', '');

								if ( typeof id == 'string' && id.inlist(ElectronicInfomatButtons) ) {
									item.setValue(true);
								}
								else {
									item.setValue(false);
								}
							}
						});

                        loadMask.hide();

                        grid.getGrid().getStore().baseParams = {
                            ElectronicInfomat_id: wnd.ElectronicInfomat_id,
                            start: 0,
                            limit: 100
                        };
                        grid.loadData();

                        form.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = action.reader.jsonData[0].Lpu_id;
                        form.findField('LpuBuilding_id').getStore().load(
                            {
                                callback: function(){
                                    if (action.reader.jsonData[0].LpuBuilding_id) {
                                        form.findField('LpuBuilding_id').setValue(action.reader.jsonData[0].LpuBuilding_id);
                                    }
                                }
                            }
                        );
                        grid_rooms.getGrid().getStore().baseParams = {
                            ElectronicInfomat_id: wnd.ElectronicInfomat_id,
                            start: 0,
                            limit: 100
                        };
                        grid_rooms.loadData();

                    },
                    failure: function (form, action) {

                        loadMask.hide();

                        if (!action.result.success) {
                            sw.swMsg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
                            this.hide();
                        }
                    },
                    scope: this
                });
            }
        }
    });