/**
 * swECORegistryViewWindow - окно регистра эко 
 */
sw.Promed.swECORegistryViewWindow = Ext.extend(sw.Promed.BaseForm, {
    title: 'Регистр по ВРТ',
    width: 800,
    codeRefresh: true,
    objectName: 'swECORegistryViewWindow',
    id: 'swECORegistryViewWindow',
    objectSrc: '/jscore/Forms/Admin/Ufa/swECORegistryViewWindow.js',
    buttonAlign: 'left',
    closable: true,
    closeAction: 'hide',
    collapsible: true,
    height: 550,
    layout: 'border',
    maximizable: true,
    minHeight: 550,
    minWidth: 800,
    modal: false,
    plain: true,
    resizable: true,    
    //размеры едитов комбобоксов и дат, значение большое, значение маленькое и значения ввода даты
    m_width_big: 320,
    m_width_min: 175,
    m_width_date: 95,
    
            
    getButtonSearch: function () {
        return Ext.getCmp('ORW_SearchButton');
    },
    
    inArray: function (needle, array) {
        for (var k in array) {
            if (array[k] == needle)
                return true;
        }

        return false;
    },
    
    doReset: function () {

        var base_form = this.findById('ECORegistryFilterForm').getForm();
        base_form.reset();
        this.ECORegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
        this.ECORegistrySearchFrame.ViewActions.action_view.setDisabled(true);
        this.ECORegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
        this.ECORegistrySearchFrame.getGrid().getStore().removeAll();

    },
    
    doSearch: function (params) {

        if (typeof params != 'object') {
            params = {};
        }

        var base_form = this.findById('ECORegistryFilterForm').getForm();

        var grid = this.ECORegistrySearchFrame.getGrid();

        if (!base_form.isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function () {
                    //
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
        loadMask.show();

        var post = getAllFormFieldValues(this.findById('ECORegistryFilterForm'));
        
        var noRez = Ext.getCmp('ecoRegistry_noRes').getValue();
        
        if (noRez){
            post.EcoRegistryData_noRes = 1;
        } else{
            post.EcoRegistryData_noRes = 0;
        }
            var ds_from = Ext.getCmp('ecoRegistry_ds1_from').getRawValue();
            var ds_to = Ext.getCmp('ecoRegistry_ds1_to').getRawValue();
            ds_from = ds_from.substr(0,ds_from.indexOf(' '));
            ds_to = ds_to.substr(0,ds_to.indexOf(' '));
            post.EcoRegistryData_ds1_from = ds_from ;
            post.EcoRegistryData_ds1_to = ds_to;
        
        if (String(getGlobalOptions().groups).indexOf('EcoRegistryRegion', 0) > 0) {
            post.isRegion = 1;
        }else if (String(getGlobalOptions().groups).indexOf('EcoRegistry', 0) > 0 && String(getGlobalOptions().groups).indexOf('OuzSpec', 0) < 0) 
        {
            post.isRegion = 0;
        }else{
			post.isRegion = 1;
		}
        
       
        post.limit = 100;
        post.start = 0;

        if (base_form.isValid()) {
            this.ECORegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
            grid.getStore().removeAll();
            grid.getStore().load({
                callback: function (records, options, success) {
                    loadMask.hide();
                },
                params: post
            });
  
        }

    },
    
    getRecordsCount: function () {

        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
        loadMask.show();

        var count = Ext.getCmp('ECORegistry').getGrid().store.getCount();
        sw.swMsg.alert('Подсчет записей', 'Найдено записей: ' + count);
        
        loadMask.hide();
        
    },
    
    openViewWindow: function (action) {
        if (getWnd('swECORegistryEditWindow').isVisible()) {
            sw.swMsg.alert('Сообщение', 'Окно просмотра уже открыто');
            return false;
        }

        var grid = this.ECORegistrySearchFrame.getGrid();
        if (!grid.getSelectionModel().getSelected()) {
            return false;
        }
        var selected_record = grid.getSelectionModel().getSelected();
        var params = {
            editType: this.editType,
            Person_id: selected_record.data.Person_id            
        };
        
            params.action = action;
        
        
        
        getWnd('swECORegistryEditWindow').show(params);
    },
    
    openWindow: function (action) {
        
        var form = this.findById('ECORegistryFilterForm').getForm();
        var grid = this.ECORegistrySearchFrame.getGrid();

        var cur_win = this;



        if (action == 'include') {
            getWnd('swPersonSearchWindow').show({
				viewOnly: (cur_win.editType=='onlyRegister')?true:false,
				onClose: function ()
				{
				},
				onSelect: function (params)
				{
					params.editErrors = false;
					params.action = 'edit';					
					if (params.Sex_id==1){
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
							},
							icon: Ext.Msg.WARNING, //icon: Ext.Msg.INFO,//
							msg: 'Невозможно добавление пациента мужского пола',
							title: 'Добавление пациента мужского пола'		
						});	
					}
					else{
						//03092018 проверка на наличие открытого случая в другой МО        
						Ext.Ajax.request({ 
							url: '/?c=Eco&m=checkOpenEco', 
							params: { 
								Person_id:params.Person_id, lpu_id:getGlobalOptions().lpu_id
							},

							success: function(result){
								var resp_obj = Ext.util.JSON.decode(result.responseText); 
								if (resp_obj.length >0){
									sw.swMsg.show(
									{
										buttons: Ext.Msg.OK,
										fn: function() 
										{
										},
										icon: Ext.Msg.WARNING, //icon: Ext.Msg.INFO,//
										msg: 'Невозможно добавление пациента с открытым случаем в другой МО',
										title: 'Ошибка'		
									});	
								}else{									
									getWnd('swPersonSearchWindow').hide();  
									getWnd('swECORegistryEditWindow').show(params);
								}
							}
						});
        
					}
				}
			});
        } 

    },    
    
    listeners: {
        'hide': function (win) {
            win.doReset();
        },
        'maximize': function (win) {
            win.findById('ECORegistryFilterForm').doLayout();
        },
        'restore': function (win) {
            win.findById('ECORegistryFilterForm').doLayout();
        },
        'resize': function (win, nW, nH, oW, oH) {
            win.findById('ORW_SearchFilterTabbar').setWidth(nW - 5);
            win.findById('ECORegistryFilterForm').setWidth(nW - 5);
        }
    },
    
    
    
    show: function () {
        sw.Promed.swECORegistryViewWindow.superclass.show.apply(this, arguments);

        var base_form = this.findById('ECORegistryFilterForm').getForm();
        
        //Убираем лишние поисковые поля
        Ext.getCmp('swECORegistryViewWindow').findById('ECORegistryFilterForm').items.items[0].items.items[0].hide();
        Ext.getCmp('swECORegistryViewWindow').findById('ECORegistryFilterForm').getForm().findField('PersonCard_IsDms').hide();
        Ext.getCmp('swECORegistryViewWindow').findById('ECORegistryFilterForm').getForm().findField('PersonCard_IsDms').hideLabel = true;

        base_form.findField('IsMeasuresComplete').setContainerVisible(getRegionNick() != 'ufa');
        
        
                
        this.ECORegistrySearchFrame.addActions({
            name: 'open_emk',
            text: 'Открыть ЭМК',
            tooltip: 'Открыть электронную медицинскую карту пациента',
            iconCls: 'open16',
            handler: function () {
                this.emkOpen();
            }.createDelegate(this)
        });

        var base_form = this.findById('ECORegistryFilterForm').getForm();
		
		var obPayType = base_form.findField('PayType_id');
		var item_0 = obPayType.store.getAt(5);
		obPayType.store.remove(item_0);		

        this.restore();
        this.center();
        this.maximize();
        this.doReset();

        if (arguments[0].userMedStaffFact)
        {
            this.userMedStaffFact = arguments[0].userMedStaffFact;
        } else {
            if (sw.Promed.MedStaffFactByUser.last)
            {
                this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
            } else
            {
                sw.Promed.MedStaffFactByUser.selectARM({
                    ARMType: arguments[0].ARMType,
                    onSelect: function (data) {
                        this.userMedStaffFact = data;
                    }.createDelegate(this)
                });
            }
        }
        this.editType = 'all';
        if(arguments[0] && arguments[0].editType)
        {
            this.editType = arguments[0].editType;
        }
       
        this.doLayout();

        base_form.findField('PersonRegisterType_id').setValue(1);
		
		var ipraTab = Ext.getCmp('ORW_SearchFilterTabbar');
		ipraTab.setActiveTab(5);
		ipraTab.setActiveTab(0);
                
        if (String(getGlobalOptions().groups).indexOf('EcoRegistryRegion', 0) < 0) {
            Ext.getCmp('LPU_idid').setValue(getGlobalOptions().lpu_id); 
            Ext.getCmp('LPU_idid').setDisabled(true);
        }

		base_form.findField('MedPersonal_iid').getStore().load({
			params: {Lpu_id: getGlobalOptions().lpu_id}
		});		
		
    },
    
    emkOpen: function () {
        var grid = this.ECORegistrySearchFrame.getGrid();

        if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id'))
        {
            Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
            return false;
        }
        var record = grid.getSelectionModel().getSelected();

        getWnd('swPersonEmkWindow').show({
            Person_id: record.get('Person_id'),
            Server_id: record.get('Server_id'),
            PersonEvn_id: record.get('PersonEvn_id'),
            userMedStaffFact: this.userMedStaffFact,
            MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
            LpuSection_id: this.userMedStaffFact.LpuSection_id,
            ARMType: 'common',
            readOnly: (this.editType == 'onlyRegister')?true:false,
            callback: function ()
            {
                //
            }.createDelegate(this)
        });
    },    
    delete_eco: function () {
        var grid = this.ECORegistrySearchFrame.getGrid();
		var gridPanel = this.ECORegistrySearchFrame;

        if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id'))
        {
            Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
            return false;
        }
		
		var params = {};		
		params.record = grid.getSelectionModel().getSelected();		
		params.callback = function() {
			gridPanel.getAction('action_refresh').execute();
		};		
		
		getWnd('swSelectDeleteEco').show(params);    
    },    
    initComponent: function () {
        var win = this;

        this.ECORegistrySearchFrame = new sw.Promed.ViewFrame({
            actions: [
                {name: 'action_add', disabled: false, handler: function () {
                        this.openWindow('include');
                    }.createDelegate(this)},
                {name: 'action_edit', disabled: false, handler: function () {
                        this.openViewWindow('edit');
                    }.createDelegate(this)},
                {name: 'action_view', handler: function () {
                        this.openViewWindow('view');
                    }.createDelegate(this)},
                {name: 'action_delete', disable: false, 
					hidden: (String(getGlobalOptions().groups).indexOf('SuperAdmin', 0) < 0),
					handler: function () {
                        this.delete_eco();
                    }.createDelegate(this)},
                {name: 'action_refresh'},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            dataUrl: C_SEARCH, 
            id: 'ECORegistry',
            object: 'ECORegistry',
            pageSize: 100,
            paging: true,
            region: 'center',
            root: 'data',
            stringfields: [
                {name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
		{name: 'ECORegistry_id', type: 'int', hidden: true},
		{name: 'ECORegistry_issueDate', type: 'date', format: 'd.m.Y', hidden: true},
                {name: 'Person_id', type: 'int', hidden: true},
                {name: 'Server_id', type: 'int', hidden: true},
                {name: 'PersonEvn_id', type: 'int', hidden: true},
                {name: 'Lpu_iid', type: 'int', hidden: true},
                {name: 'MedPersonal_iid', type: 'int', hidden: true},
                {name: 'MorbusType_id', type: 'int', hidden: true},
                {name: 'EvnNotifyBase_id', type: 'int', hidden: true},
                {name: 'PersonRegisterOutCause_id', type: 'int', hidden: true},
                {name: 'Person_Surname', type: 'string', header: 'Фамилия', width: 120}, 
                {name: 'Person_Firname', type: 'string', header: 'Имя', width: 120}, 
                {name: 'Person_Secname', type: 'string', header: 'Отчество', width: 120}, 
                {name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: 'Д/р', width: 90}, 
                {name: 'Lpu_Nick', type: 'string', header: 'МО прикрепления', width: 100}, 
                {name: 'Lpu_NickUch', type: 'string', header: 'МО учета', width: 100}, 
                {name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: 'Дата включения в регистр', width: 150}, //
				{name: 'MedPersonal_name', type: 'string', header: 'Врач', width: 200},
                {name: 'ResEco', type: 'string', header: 'Результат ВРТ', width: 150},
                {name: 'opl_name', type: 'string', header: 'Вид ВРТ', width: 150},
                {name: 'ds_name', type: 'string', header: 'Основной диагноз', width: 150},
                {name: 'Person_PAddress', type: 'string', header: 'Адрес регистрации', width: 400},
                {name: 'IsxBer', type: 'string', header: 'Исход беременности', width: 120},
                {name: 'Count_plod', type: 'string', header: 'Количество плодов', width: 130},
                {name: 'Diag_int', type: 'int',hidden: true }
            ],
            toolbar: true,
            totalProperty: 'totalCount',
            onBeforeLoadData: function () {
                this.getButtonSearch().disable();
            }.createDelegate(this),
            onLoadData: function () {
                this.getButtonSearch().enable();              
            }.createDelegate(this),
            onRowSelect: function (sm, index, record) {
                this.getAction('open_emk').setDisabled(false);

                var enableEdit = (!Ext.isEmpty(record.get('PersonRegister_id')) && record.get('IPRARegistry_Confirm') != 2);
                if (getRegionNick() == 'ufa') {
                    enableEdit = true;
                }
                this.getAction('action_edit').setDisabled(!enableEdit);
				this.getAction('action_delete').setDisabled(!enableEdit);
               
                this.getAction('action_view').setDisabled(Ext.isEmpty(record.get('PersonRegister_id')));
            },
            onDblClick: function (x, c, v) {
            }
        });


        var ECOStore = this.ECORegistrySearchFrame.getGrid().on(
                'rowdblclick',
                function () {
                    if (getWnd('swECORegistryEditWindow').isVisible()) {
                        sw.swMsg.alert('Сообщение', 'Окно просмотра уже открыто');
                        return false;
                    }

                    //var grid = this.IPRARegistrySearchFrame.getGrid();
                    if (!this.getSelectionModel().getSelected()) {
                        return false;
                    }
                    
                    Ext.getCmp('swECORegistryViewWindow').openViewWindow('edit');
                }
        );

         ECOStore = this.ECORegistrySearchFrame.getGrid().getStore().on(
                'load',
                function () {

                    var unicRecs = [];
                    var Person_ids = [];

                    var recs = this.data.items;

                    for (var k in recs) {
                        if (typeof recs[k] == 'object') {

                            if (recs[k].data.Person_id) {
                                if (Ext.getCmp('swECORegistryViewWindow').inArray(recs[k].get('Person_id'), Person_ids) === false) {
                                    Person_ids.push(recs[k].data.Person_id);
                                    unicRecs.push(recs[k]);
                                }
                            }
                        }
                    }


                    this.removeAll();


                    for (var k in unicRecs) {
                        if (typeof unicRecs[k] == 'object') {
                            this.add(unicRecs[k]);
                        }
                    }

                }
        );

        Ext.apply(this, {
            buttons: [
                {
                    handler: function () {
                        this.doSearch();
                    }.createDelegate(this),
                    iconCls: 'search16',
                    tabIndex: TABINDEX_ORW + 120,
                    id: 'ORW_SearchButton',
                    text: BTN_FRMSEARCH
                }, {
                    handler: function () {
                        this.doReset();
                    }.createDelegate(this),
                    iconCls: 'resetsearch16',
                    tabIndex: TABINDEX_ORW + 121,
                    text: BTN_FRMRESET
                }, {
                    handler: function () {
                        this.getRecordsCount();
                    }.createDelegate(this),
                    // iconCls: 'resetsearch16',
                    tabIndex: TABINDEX_ORW + 123,
                    text: BTN_FRMCOUNT
                },
                {
                    text: '-'
                },
                HelpButton(this, -1),
                {
                    handler: function () {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    onShiftTabAction: function () {
                        this.buttons[this.buttons.length - 2].focus();
                    }.createDelegate(this),
                    onTabAction: function () {
                        this.findById('ORW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ORW_SearchFilterTabbar').getActiveTab());
                    }.createDelegate(this),
                    tabIndex: TABINDEX_ORW + 124,
                    text: BTN_FRMCLOSE
                }],
            getFilterForm: function () {
                if (this.filterForm == undefined) {
                    this.filterForm = this.findById('ECORegistryFilterForm');
                    
                }
                return this.filterForm;
            },
            items: [getBaseSearchFiltersFrame({
                    isDisplayPersonRegisterRecordTypeField: false,
                    allowPersonPeriodicSelect: false,
                    id: 'ECORegistryFilterForm',
                    labelWidth: 130,
                    ownerWindow: this,
                    searchFormType: 'ECORegistry',
                    tabIndexBase: TABINDEX_ORW,
                    tabPanelHeight: 225,
                    tabPanelId: 'ORW_SearchFilterTabbar',
                    tabs: [{
                            autoHeight: true,
                            bodyStyle: 'margin-top: 5px;',
                            border: false,
                            labelWidth: 220, 
                            layout: 'form', 
                            listeners: {
                                'activate': function () {
                                    this.getFilterForm().getForm().findField('PersonRegisterType_id').focus(250, true);
                                }.createDelegate(this)
                            },
                            title: '<u>6</u>. Регистр',
                            items: [
                                {
                                    layout: 'column',
                                    border: false,
                                    items: [
                                        {
                                            layout: 'form',
                                            border: false,
                                            items: [
                                                {
                                                    xtype: 'swpersonregistertypecombo',
                                                    hiddenName: 'PersonRegisterType_id',
                                                    width: 200,
                                                    hidden: true,
						    hideLabel: true
                                                },
                                                {
                                                    fieldLabel: 'Дата включения в регистр',
                                                    name: 'EcoRegistryData_dateRange',
                                                    plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                                                    width: this.m_width_min,
                                                    xtype: 'daterangefield'
                                                },{
                                                    fieldLabel: 'Вид ВРТ',
                                                    xtype: 'swbaselocalcombo',
                                                    id : 'ecoRegistry_vidOplod',
                                                    name: 'EcoRegistry_vidOplod',
                                                    hiddenName:'EcoRegistryData_vidOplod',
                                                    editable: false,
                                                    mode: 'local',
                                                    displayField: 'name',
                                                    width: this.m_width_min,
                                                    valueField: 'id',
                                                    codeField:'selection_code',
                                                    triggerAction: 'all',
                                                    tpl: new Ext.XTemplate(
                                                        '<tpl for="."><div class="x-combo-list-item">',
                                                        '<font color="red">{selection_code}</font>&nbsp;{name}',
                                                        '</div></tpl>'
                                                    ),
                                                    store: new Ext.data.SimpleStore({
                                                        autoLoad: true,
                                                        fields: [{name: 'id', type: 'int'}, 
                                                                {name:'selection_code', type: 'int'},
                                                                {name: 'name', type: 'string'}],
                                                        data: 
                                                        [
                                                            [1, 1, 'ЭКО'], 
                                                            [2, 2,'ЭКО/ICSI'],
															[3, 3,'Перенос крио размороженных эмбрионов']
                                                        ],
                                                        key: 'id'
                                                    })
                                                }, {
                                                        fieldLabel: 'Количество перенесенных эмбрионов',
                                                        xtype: 'swbaselocalcombo',
                                                        id : 'ecoRegistry_countMoveEmbroin',
                                                        name: 'EcoRegistry_countMoveEmbroin',
                                                        hiddenName: 'EcoRegistryData_countMoveEmbroin',
                                                        allowBlank: true,
                                                        editable: false,
                                                        mode: 'local',
                                                        displayField: 'name',
                                                        valueField: 'id',
                                                        codeField:'selection_code',
                                                        triggerAction: 'all',
                                                        width: this.m_width_min,
                                                        tpl: new Ext.XTemplate(
                                                            '<tpl for="."><div class="x-combo-list-item">',
                                                            '<font color="red">{selection_code}</font>&nbsp;{name}',
                                                            '</div></tpl>'
                                                        ),
                                                        store: new Ext.data.SimpleStore({
                                                            autoLoad: true,
                                                            fields: [{name: 'id', type: 'int'}, 
                                                                    {name:'selection_code', type: 'int'},
                                                                    {name: 'name', type: 'string'}],
                                                            key: 'id',
                                                            data: [[1, 1,'1'], 
                                                                [2, 2, '2'], 
                                                                [3, 3, '3']]
                                                        })
                                                    },{
                                                        fieldLabel: 'Код основного диагноза с',
                                                        name: 'EcoRegistry_ds1_from',
                                                        id : 'ecoRegistry_ds1_from',  
                                                        hiddenName:'EcoRegistryData_ds1_from_int',
                                                        width: this.m_width_big,
                                                        xtype: 'swdiagcombo'
                                                    },{
                                                        fieldLabel: 'по',
                                                        name: 'EcoRegistry_ds1_to',
                                                        id : 'ecoRegistry_ds1_to',  
                                                        hiddenName:'EcoRegistryData_ds1_to_int',
                                                        width: this.m_width_big,
                                                        xtype: 'swdiagcombo'
                                                    },{
														id : 'ecoRegistry_VidBer',
														name: 'EcoRegistry_VidBer',
														xtype: 'swcommonsprcombo',
														comboSubject: 'EcoPregnancyType',
														hiddenName: 'EcoPregnancyType_id',
														fieldLabel: 'Вид беременности',
														width: this.m_width_min	
                                                    }
                                            ]
                                        },
                                        {
                                            layout: 'form',
                                            border: false,
                                            items: [
                                                {
													id : 'ecoRegistry_VidOpl',
													name: 'ECORegistry_VidOpl',
													xtype: 'swcommonsprcombo',
													comboSubject: 'PayType',
													hiddenName: 'PayType_id',
													fieldLabel: 'Вид оплаты',
													width: this.m_width_min
                                                },{
                                                    fieldLabel: 'Преимплантационная генетическая диагностика',
                                                    xtype: 'swbaselocalcombo',
                                                    id : 'ecoRegistry_genDiad',
                                                    name: 'EcoRegistry_genDiad',
                                                    hiddenName: 'EcoRegistryData_genDiag',
                                                    editable: false,
                                                    matchFieldWidth: false,
                                                    allowBlank: true,
                                                    mode: 'local',
                                                    displayField: 'name',
                                                    valueField: 'id',
                                                    codeField:'selection_code',
                                                    width: this.m_width_min,
                                                    triggerAction: 'all',
                                                    tpl: new Ext.XTemplate(
                                                        '<tpl for="."><div class="x-combo-list-item">',
                                                        '<font color="red">{selection_code}</font>&nbsp;{name}',
                                                        '</div></tpl>'
                                                    ),
                                                    store: new Ext.data.SimpleStore({
                                                        autoLoad: true,
                                                        fields: [{name: 'id', type: 'int'}, 
                                                                {name:'selection_code', type: 'int'},
                                                                {name: 'name', type: 'string'}],
                                                        key: 'id',
                                                        data: [
                                                            [1, 1, 'Да'], 
                                                            [2, 2, 'Нет']]
                                                    })
                                                },{
                                                    fieldLabel: 'Результат ВРТ',
                                                    name: 'EcoRegistry_resEco',
                                                    id : 'ecoRegistry_resEco',
                                                    xtype: 'swbaselocalcombo',
                                                    listWidth: this.m_width_big,
                                                    width: this.m_width_big,
                                                    allowBlank: true,
                                                    editable: false,
                                                    mode: 'local',
                                                    hiddenName: 'EcoRegistryData_resEco',
                                                    displayField: 'name',
                                                    valueField: 'id',
                                                    codeField:'selection_code',
                                                    triggerAction: 'all',
                                                    tpl: new Ext.XTemplate(
                                                            '<tpl for="."><div class="x-combo-list-item">',
                                                            '<font color="red">{selection_code}</font>&nbsp;{name}',
                                                            '</div></tpl>'
                                                    ),
                                                    store: new Ext.data.SimpleStore({
                                                        autoLoad: true,
                                                        fields: [{name: 'id', type: 'int'}, 
                                                                {name:'selection_code', type: 'int'},
                                                                {name: 'name', type: 'string'}],
                                                        key: 'id',
                                                        data: [
                                                            [1,1, 'Беременность наступила'], 
                                                            [2,2, 'Беременность не наступила'], 
                                                            [3,3, 'Отсутствие ответа на стимуляцию'], 
                                                            [4,4, 'Отсутствие половых клеток для оплодотворения'],
                                                            [5,5, 'Отсутствие оплодотворения'],
                                                            [6,6, 'Эмбрионы остановились в развитии'],
                                                            [7,7, 'Неизвестен'],
															[8,8, 'Отмена переноса']
                                                        ]
                                                    })
                                                },{
                                                    fieldLabel: 'Результат ВРТ не указан',
                                                    id: 'ecoRegistry_noRes',
                                                    name: 'EcoRegistryData_noRes',
                                                    xtype: 'checkbox'
                                                },{
                                                    fieldLabel: 'МО Учета',
                                                    xtype: 'swlpucombo',
                                                    autoload: true,
                                                    id: 'LPU_idid',
                                                    mode: 'local',
                                                    name:'EcoRegistry_lpu_id',
                                                    width: this.m_width_big,
                                                    hiddenName: 'EcoRegistryData_lpu_id',																										
													listeners: {
														'change': function(combo, newValue, oldValue) {
															var base_form = this.getFilterForm().getForm();

															var med_personal_combo = base_form.findField('MedPersonal_iid');

															if (Ext.isEmpty(newValue) || newValue == -1) {
																med_personal_combo.setValue(null);
																med_personal_combo.getStore().removeAll();
														} else {
																med_personal_combo.getStore().load({
																	params: {Lpu_id: newValue}
																});
														}
														}.createDelegate(this)
													}
												}, {
													allowBlank: true,
													xtype: 'swmedpersonalcombo',
													hiddenName: 'MedPersonal_iid',
													fieldLabel: 'Врач',
													width: 210,
													listWidth: 300													
                                                },{													
													
                                                    fieldLabel: 'Мероприятия выполнены',
                                                    hiddenName: 'IsMeasuresComplete',
                                                    xtype: 'swyesnocombo',
                                                    width: 170,
													hidden: true,
													hideLabel: true
                                                }
                                            ]
										},
                                        {
                                            layout: 'column',
                                            id: 'text_number_IPRA',
                                            border: false
                                        }
                                    ]
                                }
                            ]
                        }

                    ]
                }),
                this.ECORegistrySearchFrame]
        });

        sw.Promed.swECORegistryViewWindow.superclass.initComponent.apply(this, arguments);

    }
    
});