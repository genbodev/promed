/**
 * swFoodStuffViewWindow - окно просмотра продуктов питания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */

sw.Promed.swFoodStuffViewWindow = Ext.extend(sw.Promed.BaseForm,
    {
        closable: true,
        closeAction: 'hide',
        draggable: true,
        maximized: true,
        id: 'swFoodStuffViewWindow',
        objectName: 'swFoodStuffViewWindow',
        objectSrc: '/jscore/Forms/Cook/swFoodStuffViewWindow.js',
        title: lang['produktyi_pitaniya'],
        readOnly: false,

        loadGridWithFilter: function(clear)
        {
            var wnd = this;
            var base_form = this.FiltersPanel.getForm();
            wnd.FoodStuffGrid.removeAll();
            if (clear){
                base_form.reset();
                wnd.FoodStuffGrid.getAction('action_refresh').setDisabled(true);
                wnd.FoodStuffGrid.gFilters = null;
                wnd.FoodStuffGrid.onRowSelect();

                wnd.SubstitPanel.getAction('action_refresh').setDisabled(true);
                wnd.SubstitPanel.getAction('action_add').setDisabled(true);
                wnd.PricePanel.getAction('action_refresh').setDisabled(true);
                wnd.PricePanel.getAction('action_add').setDisabled(true);
                wnd.MicronutrientPanel.getAction('action_refresh').setDisabled(true);
                wnd.MicronutrientPanel.getAction('action_add').setDisabled(true);
                wnd.CoeffPanel.getAction('action_refresh').setDisabled(true);
                wnd.CoeffPanel.getAction('action_add').setDisabled(true);
            } else {
                var params = base_form.getValues();
                params.limit = 100;
                params.start = 0;
                wnd.FoodStuffGrid.loadData({
                    globalFilters: params
                });
            }
        },

        openFoodStuffEditWindow: function(action) {
            if ( action != 'add' && action != 'edit' && action != 'view' ) {
                return false;
            }

            if ( getWnd('swFoodStuffEditWindow').isVisible() ) {
                sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_produkta_pitaniya_uje_otkryito']);
                return false;
            }

            var params = new Object();
            var grid = this.FoodStuffGrid.getGrid();

            params.action = action;
            params.callback = function(data) { log(data);
                if ( !data || !data.FoodStuffData ) {
                    return false;
                }

                data.FoodStuffData.FoodStuff_id = data.FoodStuff_id;
                var record = grid.getStore().getById(data.FoodStuffData.FoodStuff_id);

                if ( record ) {
                    var grid_fields = new Array();

                    grid.getStore().fields.eachKey(function(key, item) {
                        grid_fields.push(key);
                    });

                    for ( i = 0; i < grid_fields.length; i++ ) {
                        if ( data.FoodStuffData[grid_fields[i]] != undefined ) {
                            record.set(grid_fields[i], data.FoodStuffData[grid_fields[i]]);
                        }
                    }

                    record.commit();
                }
                else {
                    if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('FoodStuff_id') ) {
                        grid.getStore().removeAll();
                    }

                    grid.getStore().loadData({ data: [ data.FoodStuffData ], totalCount: 1 }, true);
                }
            };

            params.formParams = new Object();

            if ( action == 'add' ) {
                getWnd('swFoodStuffEditWindow').show(params);
            }
            else {
                if ( !grid.getSelectionModel().getSelected() ) {
                    return false;
                }

                var selected_record = grid.getSelectionModel().getSelected();

                if ( selected_record.get('accessType') != 'edit' ) {
                    params.action = 'view';
                }

                var food_stuff_id = selected_record.get('FoodStuff_id');
                params.formParams.FoodStuff_id = food_stuff_id;

                params.onHide = function() {
                    grid.getView().focusRow(grid.getStore().indexOf(selected_record));
                };

                getWnd('swFoodStuffEditWindow').show(params);
            }
        },

        openFoodStuffSubstitEditWindow: function(action) {
            if ( action != 'add' && action != 'edit' && action != 'view' ) {
                return false;
            }

            if ( getWnd('swFoodStuffSubstitEditWindow').isVisible() ) {
                sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_zamenitelya_produkta_pitaniya_uje_otkryito']);
                return false;
            }

            var params = new Object();
            var grid = this.SubstitPanel.getGrid();

            params.action = action;
            params.callback = function(data) {
                if ( !data || !data.FoodStuffSubstitData ) {
                    return false;
                }

                data.FoodStuffSubstitData.FoodStuffSubstit_id = data.FoodStuffSubstit_id;
                var record = grid.getStore().getById(data.FoodStuffSubstitData.FoodStuffSubstit_id);

                if ( record ) {
                    var grid_fields = new Array();

                    grid.getStore().fields.eachKey(function(key, item) {
                        grid_fields.push(key);
                    });

                    for ( i = 0; i < grid_fields.length; i++ ) {
                        if ( data.FoodStuffSubstitData[grid_fields[i]] != undefined ) {
                            record.set(grid_fields[i], data.FoodStuffSubstitData[grid_fields[i]]);
                        }
                    }

                    record.commit();
                }
                else {
                    grid.getStore().reload({
                        callback: function(){
                            if ( this.getStore().getCount() == 1 && !this.getStore().getAt(0).get('FoodStuffSubstit_id') ) {
                                this.getStore().removeAll();
                            }
                        }.createDelegate(grid)
                    })
                    /*
                    if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('FoodStuffSubstit_id') ) {
                        grid.getStore().removeAll();
                    }

                    grid.getStore().loadData({ data: [ data.FoodStuffSubstitData ], totalCount: 1 }, true);
                    */
                }
            };

            params.formParams = new Object();

            var selected_record = this.FoodStuffGrid.getGrid().getSelectionModel().getSelected();
            var food_stuff_id = selected_record.get('FoodStuff_id');
            params.formParams.FoodStuff_id = food_stuff_id;

            if ( action == 'add' ) {
                getWnd('swFoodStuffSubstitEditWindow').show(params);
            }
            else {
                if ( !grid.getSelectionModel().getSelected() ) {
                    return false;
                }

                var selected_record = grid.getSelectionModel().getSelected();

                if ( selected_record.get('accessType') != 'edit' ) {
                    params.action = 'view';
                }

                var food_stuff_substit_id = selected_record.get('FoodStuffSubstit_id');
                params.formParams.FoodStuffSubstit_id = food_stuff_substit_id;

                params.onHide = function() {
                    grid.getView().focusRow(grid.getStore().indexOf(selected_record));
                };

                getWnd('swFoodStuffSubstitEditWindow').show(params);
            }
        },

        openFoodStuffPriceEditWindow: function(action) {
            if ( action != 'add' && action != 'edit' && action != 'view' ) {
                return false;
            }

            if ( getWnd('swFoodStuffPriceEditWindow').isVisible() ) {
                sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_tsenyi_produkta_pitaniya_uje_otkryito']);
                return false;
            }

            var params = new Object();
            var grid = this.PricePanel.getGrid();

            params.action = action;
            params.callback = function(data) {
                if ( !data || !data.FoodStuffPriceData ) {
                    return false;
                }

                data.FoodStuffPriceData.FoodStuffPrice_id = data.FoodStuffPrice_id;
                var record = grid.getStore().getById(data.FoodStuffPriceData.FoodStuffPrice_id);

                if ( record ) {
                    var grid_fields = new Array();

                    grid.getStore().fields.eachKey(function(key, item) {
                        grid_fields.push(key);
                    });

                    for ( i = 0; i < grid_fields.length; i++ ) {
                        if ( data.FoodStuffPriceData[grid_fields[i]] != undefined ) {
                            record.set(grid_fields[i], data.FoodStuffPriceData[grid_fields[i]]);
                        }
                    }

                    record.commit();
                }
                else {
                    if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('FoodStuffPrice_id') ) {
                        grid.getStore().removeAll();
                    }

                    grid.getStore().loadData({ data: [ data.FoodStuffPriceData ], totalCount: 1 }, true);
                }
            };

            params.formParams = new Object();

            var selected_record = this.FoodStuffGrid.getGrid().getSelectionModel().getSelected();
            var food_stuff_id = selected_record.get('FoodStuff_id');

            params.formParams.FoodStuff_id = food_stuff_id;

            if ( action == 'add' ) {
                getWnd('swFoodStuffPriceEditWindow').show(params);
            }
            else {
                if ( !grid.getSelectionModel().getSelected() ) {
                    return false;
                }

                var selected_record = grid.getSelectionModel().getSelected();

                if ( selected_record.get('accessType') != 'edit' ) {
                    params.action = 'view';
                }

                var food_stuff_price_id = selected_record.get('FoodStuffPrice_id');
                params.formParams.FoodStuffPrice_id = food_stuff_price_id;

                params.onHide = function() {
                    grid.getView().focusRow(grid.getStore().indexOf(selected_record));
                };
                getWnd('swFoodStuffPriceEditWindow').show(params);
            }
        },

        openFoodStuffMicronutrientEditWindow: function(action) {
            if ( action != 'add' && action != 'edit' && action != 'view' ) {
                return false;
            }

            if ( getWnd('swFoodStuffMicronutrientEditWindow').isVisible() ) {
                sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_mikronutrientov_produkta_pitaniya_uje_otkryito']);
                return false;
            }

            var params = new Object();
            var grid = this.MicronutrientPanel.getGrid();

            params.action = action;
            params.callback = function(data) {
                if ( !data || !data.FoodStuffMicronutrientData ) {
                    return false;
                }

                data.FoodStuffMicronutrientData.FoodStuffMicronutrient_id = data.FoodStuffMicronutrient_id;
                var record = grid.getStore().getById(data.FoodStuffMicronutrientData.FoodStuffMicronutrient_id);

                if ( record ) {
                    var grid_fields = new Array();

                    grid.getStore().fields.eachKey(function(key, item) {
                        grid_fields.push(key);
                    });

                    for ( i = 0; i < grid_fields.length; i++ ) {
                        if ( data.FoodStuffMicronutrientData[grid_fields[i]] != undefined ) {
                            record.set(grid_fields[i], data.FoodStuffMicronutrientData[grid_fields[i]]);
                        }
                    }

                    record.commit();
                }
                else {
                    if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('FoodStuffMicronutrient_id') ) {
                        grid.getStore().removeAll();
                    }

                    grid.getStore().loadData({ data: [ data.FoodStuffMicronutrientData ], totalCount: 1 }, true);
                }
            };

            params.formParams = new Object();

            var selected_record = this.FoodStuffGrid.getGrid().getSelectionModel().getSelected();
            var food_stuff_id = selected_record.get('FoodStuff_id');

            params.formParams.FoodStuff_id = food_stuff_id;

            if ( action == 'add' ) {
                getWnd('swFoodStuffMicronutrientEditWindow').show(params);
            }
            else {
                if ( !grid.getSelectionModel().getSelected() ) {
                    return false;
                }

                var selected_record = grid.getSelectionModel().getSelected();

                if ( selected_record.get('accessType') != 'edit' ) {
                    params.action = 'view';
                }

                var food_stuff_micronutrient_id = selected_record.get('FoodStuffMicronutrient_id');
                params.formParams.FoodStuffMicronutrient_id = food_stuff_micronutrient_id;

                params.onHide = function() {
                    grid.getView().focusRow(grid.getStore().indexOf(selected_record));
                };
                getWnd('swFoodStuffMicronutrientEditWindow').show(params);
            }
        },

        openFoodStuffCoeffEditWindow: function(action) {
            if ( action != 'add' && action != 'edit' && action != 'view' ) {
                return false;
            }

            if ( getWnd('swFoodStuffCoeffEditWindow').isVisible() ) {
                sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_koeffitsienta_produkta_pitaniya_uje_otkryito']);
                return false;
            }

            var params = new Object();
            var grid = this.CoeffPanel.getGrid();

            params.action = action;
            params.callback = function(data) {
                if ( !data || !data.FoodStufCoeffData ) {
                    return false;
                }

                data.FoodStuffCoeffData.FoodStuffCoeff_id = data.FoodStuffCoeff_id;
                var record = grid.getStore().getById(data.FoodStuffCoeffData.FoodStuffCoeff_id);

                if ( record ) {
                    var grid_fields = new Array();

                    grid.getStore().fields.eachKey(function(key, item) {
                        grid_fields.push(key);
                    });

                    for ( i = 0; i < grid_fields.length; i++ ) {
                        if ( data.FoodStuffCoeffData[grid_fields[i]] != undefined ) {
                            record.set(grid_fields[i], data.FoodStuffCoeffData[grid_fields[i]]);
                        }
                    }

                    record.commit();
                }
                else {
                    if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('FoodStuffCoeff_id') ) {
                        grid.getStore().removeAll();
                    }

                    grid.getStore().loadData({ data: [ data.FoodStuffCoeffData ], totalCount: 1 }, true);
                }
            };

            params.formParams = new Object();

            var selected_record = this.FoodStuffGrid.getGrid().getSelectionModel().getSelected();
            var food_stuff_id = selected_record.get('FoodStuff_id');

            params.formParams.FoodStuff_id = food_stuff_id;

            if ( action == 'add' ) {
                getWnd('swFoodStuffCoeffEditWindow').show(params);
            }
            else {
                if ( !grid.getSelectionModel().getSelected() ) {
                    return false;
                }

                var selected_record = grid.getSelectionModel().getSelected();

                if ( selected_record.get('accessType') != 'edit' ) {
                    params.action = 'view';
                }

                var food_stuff_coeff_id = selected_record.get('FoodStuffCoeff_id');
                params.formParams.FoodStuffCoeff_id = food_stuff_coeff_id;

                params.onHide = function() {
                    grid.getView().focusRow(grid.getStore().indexOf(selected_record));
                };
                getWnd('swFoodStuffCoeffEditWindow').show(params);
            }
        },

        show: function()
        {
            sw.Promed.swFoodStuffViewWindow.superclass.show.apply(this, arguments);

            var loadMask = new Ext.LoadMask(Ext.get('swFoodStuffViewWindow'), {msg: LOAD_WAIT});
            loadMask.show();
            var wnd = this;

            wnd.loadGridWithFilter(true);
            loadMask.hide();
        },

        initComponent: function()
        {
            var wnd = this;

            this.FiltersPanel = new Ext.form.FormPanel({
                autoHeight: true,
                bodyStyle: 'padding: 5px',
                border: false,
                frame: true,
                labelAlign: 'right',
                labelWidth: 150,
                id: 'FSVW_FoodStuffFilterForm',
                region: 'north',
                xtype: 'form',
                layout: 'column',

                items: [{
                    border: false,
                    layout: 'form',
                    labelWidth: 45,
                    items: [{
                        fieldLabel: lang['kod'],
                        listeners: {
                            'keydown': function (f, e) {
                                if ( e.getKey() == e.ENTER ) {
                                    this.loadGridWithFilter();
                                }
                            }.createDelegate(this)
                        },
                        maxLength: 5,
                        name: 'FoodStuff_Code',
                        width: 175,
                        xtype: 'textfield'
                    }]
                }, {
                    border: false,
                    layout: 'form',
                    //labelWidth: 100,
                    items: [{
                        fieldLabel: lang['naimenovanie'],
                        listeners: {
                            'keydown': function (f, e) {
                                if ( e.getKey() == e.ENTER ) {
                                    this.loadGridWithFilter();
                                }
                            }.createDelegate(this)
                        },
                        maxLength: 100,
                        name: 'FoodStuff_Name',
                        width: 175,
                        xtype: 'textfield'
                    }]
                }, {
                    bodyStyle: 'padding-left: 5px;',
                    border: false,
                    layout: 'form',
                    items: [{
                        disabled: false,
                        handler: function () {
                            this.loadGridWithFilter();
                        }.createDelegate(this),
                        minWidth: 125,
                        text: lang['ustanovit_filtr'],
                        topLevel: true,
                        xtype: 'button'
                    }, {
                        disabled: false,
                        handler: function () {
                            this.loadGridWithFilter(true);
                        }.createDelegate(this),
                        minWidth: 125,
                        text: lang['snyat_filtr'],
                        topLevel: true,
                        xtype: 'button'
                    }]
                }]
            });

            this.FoodStuffGrid = new sw.Promed.ViewFrame(
                {
                    id: 'FSVW_FoodStuffGridPanel',
                    tbar: this.gridToolbar,
                    region: 'center',
                    paging: true,
                    dataUrl: '/?c=FoodStuff&m=loadFoodStuffGrid',
                    object: 'FoodStuff',
                    keys: [],
                    root: 'data',
                    totalProperty: 'totalCount',
                    autoLoadData: false,
                    stringfields:
                        [
                            {name: 'FoodStuff_id', type: 'int', header: 'ID', key: true},
                            {name: 'FoodStuffPrice_id', type: 'int', hidden: true},
                            {name: 'FoodStuff_Code', type: 'string', header: lang['kod'], width: 100},
                            {name: 'FoodStuff_Name', type: 'string', header: lang['naimenovanie'], width: 250},
                            {name: 'FoodStuff_Descr', type: 'string', header: lang['opisanie'], id: 'autoexpand'},
                            {name: 'FoodStuffPrice_Price', header: lang['tsena'], width: 150, renderer: twoDecimalsRenderer},

                        //Отображаются во вкладке "Пищевая ценность" (NutrientValuePanel)
                            {name: 'FoodStuff_Protein', type: 'int', hidden: true},
                            {name: 'FoodStuff_Fat', type: 'int', hidden: true},
                            {name: 'FoodStuff_Carbohyd', type: 'int', hidden: true},
                            {name: 'FoodStuff_Caloric', type: 'int', hidden: true}
                        ],
                    actions:
                        [
                            {
                                name:'action_add',
                                handler: function() {
                                    this.openFoodStuffEditWindow('add');
                                }.createDelegate(this)
                            },
                            {
                                name:'action_edit',
                                handler: function() {
                                    this.openFoodStuffEditWindow('edit');
                                }.createDelegate(this)
                            },
                            {
                                name:'action_view',
                                handler: function() {
                                    this.openFoodStuffEditWindow('view');
                                }.createDelegate(this)
                            }
                        ],
                    onRowSelect: function(sm,index,record)
                    {
                        wnd.SubstitPanel.getGrid().getStore().removeAll();
                        wnd.PricePanel.getGrid().getStore().removeAll();
                        wnd.MicronutrientPanel.getGrid().getStore().removeAll();
                        wnd.CoeffPanel.getGrid().getStore().removeAll();

                        wnd.findById('FSVW_FoodStuff_Protein').setValue('');
                        wnd.findById('FSVW_FoodStuff_Fat').setValue('');
                        wnd.findById('FSVW_FoodStuff_Carbohyd').setValue('');
                        wnd.findById('FSVW_FoodStuff_Caloric').setValue('');

                        if (typeof record != 'object' || Ext.isEmpty(record.get('FoodStuff_id'))){
                            return false;
                        }

                        var food_stuff_id = record.get('FoodStuff_id');
                        var grid;

                        wnd.findById('FSVW_FoodStuff_Protein').setValue(record.get('FoodStuff_Protein'));
                        wnd.findById('FSVW_FoodStuff_Fat').setValue(record.get('FoodStuff_Fat'));
                        wnd.findById('FSVW_FoodStuff_Carbohyd').setValue(record.get('FoodStuff_Carbohyd'));
                        wnd.findById('FSVW_FoodStuff_Caloric').setValue(record.get('FoodStuff_Caloric'));

                        grid = wnd.SubstitPanel.getGrid();
                        grid.getStore().load({
                            params: {
                                FoodStuff_id: food_stuff_id
                            }
                        });

                        grid = wnd.PricePanel.getGrid();
                        grid.getStore().load({
                            params: {
                                FoodStuff_id: food_stuff_id
                            }
                        });

                        grid = wnd.MicronutrientPanel.getGrid();
                        grid.getStore().load({
                            params: {
                                FoodStuff_id: food_stuff_id
                            }
                        });

                        grid = wnd.CoeffPanel.getGrid();
                        grid.getStore().load({
                            params: {
                                FoodStuff_id: food_stuff_id
                            }
                        });
                    }
                });

            /*this.FoodStuffTree = new Ext.tree.TreePanel({
                animate: true,
                autoScroll: true,
                border: false,
                enableDD: false,
                enableKeyEvents: true,
                id: 'FSVW_FoodStuffTreePanel',
                keys: [],
                split: true,
                title: lang['tovarno-materialnyie_tsennosti'],
                useArrows: true,
                width: 150,

                listeners: {
                    'click': function(node, e) {
                        if ( node.id == 'root' ) {
                            return false;
                        }

                    },
                    'expandnode': function(node) {
                        if ( node.id == 'root' ) {
                            this.getSelectionModel().select(node.firstChild);
                            this.fireEvent('click', node.firstChild);
                        }
                    }
                },
                loader: new Ext.tree.TreeLoader({
                    clearOnLoad: true
                    //dataUrl: ''
                }),
                region: 'west',
                root: {
                    draggable: false,
                    id: 'root',
                    text: ''
                }
            });*/

            this.SubstitPanel = new sw.Promed.ViewFrame(
                {
                    id: 'FSVW_FoodStuffSubstitPanel',
                    tbar: this.gridToolbar,
                    paging: false,
                    dataUrl: '/?c=FoodStuff&m=loadFoodStuffSubstitGrid',
                    object: 'FoodStuffSubstit',
                    keys: [],
                    autoLoadData: false,
                    focusOnFirstLoad: false,
                    noFocusOnLoad: true,
                    stringfields:
                        [
                            {name: 'FoodStuffSubstit_id', type: 'int',header: 'ID', key: true},
                            {name: 'FoodStuff_id', type: 'int',  hidden: true},
                            {name: 'FoodStuff_sid', type: 'int', hidden: true},
                            {name: 'FoodStuffSubstit_Priority', type: 'int', header: lang['prioritet'], width: 200},
                            {name: 'FoodStuff_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand'},
                            {name: 'FoodStuffSubstit_Coeff', type: 'float', header: lang['koeffitsient'], width: 300}
                        ],
                    actions:
                        [
                            {
                                name:'action_add',
                                handler: function() {
                                    this.openFoodStuffSubstitEditWindow('add');
                                }.createDelegate(this)
                            },
                            {
                                name:'action_edit',
                                handler: function() {
                                    this.openFoodStuffSubstitEditWindow('edit');
                                }.createDelegate(this)
                            },
                            {
                                name:'action_view',
                                handler: function() {
                                    this.openFoodStuffSubstitEditWindow('view');
                                }.createDelegate(this)
                            }
                        ]
                });

            this.PricePanel = new sw.Promed.ViewFrame(
                {
                    id: 'FSVW_FoodStuffPricePanel',
                    tbar: this.gridToolbar,
                    paging: false,
                    dataUrl: '/?c=FoodStuff&m=loadFoodStuffPriceGrid',
                    keys: [],
                    object: 'FoodStuffPrice',
                    autoLoadData: false,
                    focusOnFirstLoad: false,
                    noFocusOnLoad: true,
                    stringfields:
                        [
                            {name: 'FoodStuffPrice_id', type: 'int', header: 'ID', key: true},
                            {name: 'FoodStuff_id', type: 'int', hidden: true},
                            {name: 'FoodStuffPrice_begDate', type: 'date', header: lang['data'], width: 200,
                                format: 'd.m.Y'},
                            {name: 'FoodStuffPrice_Price', type: 'money', header: lang['tsena'], width: 300}
                        ],
                    actions:
                        [
                            {
                                name:'action_add',
                                handler: function() {
                                    this.openFoodStuffPriceEditWindow('add');
                                }.createDelegate(this)
                            },
                            {
                                name:'action_edit',
                                handler: function() {
                                    this.openFoodStuffPriceEditWindow('edit');
                                }.createDelegate(this)
                            },
                            {
                                name:'action_view',
                                handler: function() {
                                    this.openFoodStuffPriceEditWindow('view');
                                }.createDelegate(this)
                            }
                        ],
                    onRowSelect: function(sm,index,record)
                    {

                    }
                });

            this.NutrientValuePanel = new Ext.form.FormPanel({
                id: 'FSVW_FoodStuffNutrientValuePanel',
                autoScroll: true,
                bodyBorder: false,
                bodyStyle: 'padding: 10px 40px 0',
                border: false,
                frame: true,

                items: [{
                    name: 'FoodStuff_Protein',
                    id: 'FSVW_FoodStuff_Protein',
                    fieldLabel: lang['belki'],
                    disabled: true,
                    xtype: 'textfield'
                }, {
                    name: 'FoodStuff_Fat',
                    id: 'FSVW_FoodStuff_Fat',
                    fieldLabel: lang['jiryi'],
                    disabled: true,
                    xtype: 'textfield'
                }, {
                    name: 'FoodStuff_Carbohyd',
                    id: 'FSVW_FoodStuff_Carbohyd',
                    fieldLabel: lang['uglevodyi'],
                    disabled: true,
                    xtype: 'textfield'
                }, {
                    name: 'FoodStuff_Caloric',
                    id: 'FSVW_FoodStuff_Caloric',
                    fieldLabel: lang['kalorii'],
                    disabled: true,
                    xtype: 'textfield'
                }
                ]
            });

            this.MicronutrientPanel = new sw.Promed.ViewFrame(
                {
                    id: 'FSVW_FoodStuffMicronutrientPanel',
                    tbar: this.gridToolbar,
                    paging: false,
                    dataUrl: '/?c=FoodStuff&m=loadFoodStuffMicronutrientGrid',
                    object: 'FoodStuffMicronutrient',
                    keys: [],
                    autoLoadData: false,
                    focusOnFirstLoad: false,
                    noFocusOnLoad: true,
                    stringfields:
                        [
                            {name: 'Micronutrient_id', type: 'int', header: 'ID', key: true},
                            {name: 'FoodStuff_id', type: 'int', hidden: true},
                            {name: 'FoodStuffMicronutrient_id', type: 'int', hidden: true},
                            {name: 'Micronutrient_Name', type: 'string', header: lang['naimenovanie'], width: 350},
                            {name: 'FoodStuffMicronutrient_Content', type: 'float', header: lang['soderjanie'], width: 200}
                        ],
                    actions:
                        [
                            {
                                name:'action_add',
                                handler: function() {
                                    this.openFoodStuffMicronutrientEditWindow('add');
                                }.createDelegate(this)
                            },
                            {
                                name:'action_edit',
                                handler: function() {
                                    this.openFoodStuffMicronutrientEditWindow('edit');
                                }.createDelegate(this)
                            },
                            {
                                name:'action_view',
                                handler: function() {
                                    this.openFoodStuffMicronutrientEditWindow('view');
                                }.createDelegate(this)
                            }

                        ],
                    onRowSelect: function(sm,index,record)
                    {

                    }
                });

            this.CoeffPanel = new sw.Promed.ViewFrame(
                {
                    id: 'FSVW_FoodStuffCoeffPanel',
                    tbar: this.gridToolbar,
                    paging: false,
                    dataUrl: '/?c=FoodStuff&m=loadFoodStuffCoeffGrid',
                    object: 'FoodStuffCoeff',
                    keys: [],
                    autoLoadData: false,
                    focusOnFirstLoad: false,
                    noFocusOnLoad: true,
                    stringfields:
                        [
                            {name: 'FoodStuffCoeff_id', type: 'int', header: 'ID', key: true},
                            {name: 'FoodStuff_id', type: 'int', hidden: true},
                            {name: 'Okei_id', type: 'int', hidden: true},
                            {name: 'Okei_Name', type: 'string', header: lang['naimenovanie'], width: 300},
                            {name: 'FoodStuffCoeff_Coeff', type: 'float', header: lang['koeffitsient'], width: 200},
                            {name: 'FoodStuffCoeff_Descr', type: 'string', header: lang['opisanie'], id: 'autoexpand'}
                        ],
                    actions:
                        [
                            {
                                name:'action_add',
                                handler: function() {
                                    this.openFoodStuffCoeffEditWindow('add');
                                }.createDelegate(this)
                            },
                            {
                                name:'action_edit',
                                handler: function() {
                                    this.openFoodStuffCoeffEditWindow('edit');
                                }.createDelegate(this)
                            },
                            {
                                name:'action_view',
                                handler: function() {
                                    this.openFoodStuffCoeffEditWindow('view');
                                }.createDelegate(this)
                            }
                        ],
                    onRowSelect: function(sm,index,record)
                    {

                    }
                });

            this.FoodStuffTabs = new Ext.TabPanel(
                {
                    id: 'FSVW_FoodStuffTabsPanel',
                    autoScroll: true,
                    activeTab: 0,
                    resizeTabs: true,
                    region: 'south',
                    enableTabScroll: true,
                    height: 400,
                    minTabWidth: 120,
                    tabWidth: 'auto',
                    defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
                    layoutOnTabChange: true,
                    listeners:
                    {
                        tabchange: function(tab, panel)
                        {
                            //LoadOnChangeTab(n);
                        }
                    },
                    items:[
                        {
                            title: lang['zameniteli'],
                            layout: 'fit',
                            id: 'tab_substit',
                            border:false,
                            items: [this.SubstitPanel]
                        },
                        {
                            title: lang['tsena'],
                            layout: 'fit',
                            id: 'tab_price',
                            border:false,
                            items: [this.PricePanel]
                        },
                        {
                            title: lang['pischevaya_tsennost'],
                            id: 'tab_nutrient_value',
                            layout: 'fit',
                            border:false,
                            items: [this.NutrientValuePanel]
                        },
                        {
                            title: lang['mikronutrientyi'],
                            id: 'tab_micronutrient',
                            layout: 'fit',
                            border: false,
                            items: [this.MicronutrientPanel]
                        },
                        {
                            title: lang['pereschet_edinits_izmereniya'],
                            id: 'tab_coeff',
                            layout: 'fit',
                            border: false,
                            items: [this.CoeffPanel]
                        }
                    ]
                });

            Ext.apply(this,
                {
                    layout: 'border',
                    items:
                        [
                            this.FiltersPanel,
                            //this.FoodStuffTree,
                            this.FoodStuffGrid,
                            this.FoodStuffTabs
                        ],
                    buttons:
                    [{
                        text: '-'
                    },
                    //HelpButton(this, TABINDEX_MPSCHED + 98),
                    {
                        iconCls: 'cancel16',
                        text: BTN_FRMCLOSE,
                        handler: function() {this.hide();}.createDelegate(this)
                    }]
                });
            sw.Promed.swFoodStuffViewWindow.superclass.initComponent.apply(this, arguments);
        }
    });

