/**
 * swMedProductCardEditWindow - окно просмотра, добавления и редактирования медицинского изделия
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Abakhri Samir
 * @version      24.05.2014
 * @comment      Префикс для id компонентов MPC (MedProductCard)
 */

sw.Promed.swMedProductCardEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	//autoWidth: true,
    //height: 700,
	title: lang['kartochka_meditsinskogo_izdeliya_redaktirovanie'],
	width: 1200,
	bodyStyle: 'padding: 2px',
	buttonAlign: 'left',
    action: '',
	draggable: true,
    MedProductCard_id: null,
	callback: Ext.emptyFn,
	closeAction: 'hide',
	id: 'MedProductCardEditWindow',
    openConsumablesEditWindow: function(action) {
        if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
            return false;
        }

        if ( this.action == 'view' ) {
            if ( action == 'add' ) {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }

        if ( getWnd('swConsumablesEditWindow').isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_rashodnogo_materiaala_uje_otkryito']);
            return false;
        }

        var deniedConsumablesList = [],
            formParams = {},
            grid = this.findById('MPC_ConsumablesGrid').getGrid(),
            params = {},
            selectedRecord;

        params.MedProductCard_id = this.MedProductCard_id;

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('Consumables_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

        if ( action == 'add' ) {

            params.onHide = function() {
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                }
            };

            grid.getStore().each(function(rec) {
                if ( rec.get('Consumables_id') ) {
                    deniedConsumablesList.push(rec.get('Consumables_Name'));
                }
            });
        }
        else {
            if ( !selectedRecord ) {
                return false;
            }

            grid.getStore().each(function(rec) {
                if ( rec.get('Consumables_id') && selectedRecord.get('Consumables_id') != rec.get('Consumables_id') ) {
                    deniedConsumablesList.push(rec.get('Consumables_Name'));
                }
            });

            formParams = selectedRecord.data;
            params.Consumables_id = grid.getSelectionModel().getSelected().get('Consumables_id');
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
            };

            if (params.Consumables_id < 0) {
                params.Consumables_Name = grid.getSelectionModel().getSelected().get('Consumables_Name');
            }
        }

        params.action = action;
        params.callback = function(data) {
            if ( typeof data != 'object' || typeof data.deniedConsumablesData != 'object' ) {
                return false;
            }

            var record = grid.getStore().getById(data.deniedConsumablesData.Consumables_id);

            if ( record ) {

                var grid_fields = [];

                grid.getStore().fields.eachKey(function(key, item) {
                    grid_fields.push(key);
                });

                for ( var i = 0; i < grid_fields.length; i++ ) {
                    record.set(grid_fields[i], data.deniedConsumablesData[grid_fields[i]]);
                }

                record.commit();
            }
            else {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Consumables_id') ) {
                    grid.getStore().removeAll();
                }

                data.deniedConsumablesData.Consumables_id = -swGenTempId(grid.getStore());

                grid.getStore().loadData([ data.deniedConsumablesData ], true);
            }
        }.createDelegate(this);
        params.deniedConsumablesList = deniedConsumablesList;
        params.formMode = 'local';
        params.formParams = formParams;

        getWnd('swConsumablesEditWindow').show(params);
    },
    openMeasureFundCheckEditWindow: function(action) {
        if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
            return false;
        }

        if ( this.action == 'view' ) {
            if ( action == 'add' ) {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }

        if ( getWnd('swMeasureFundCheckEditWindow').isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_svidetelstva_o_proverke_uje_otkryito']);
            return false;
        }

        var deniedMeasureFundCheckList = [],
            formParams = {},
            grid = this.findById('MPC_MeasureFundCheckGrid').getGrid(),
            params = {},
            selectedRecord;

        params.MedProductCard_id = this.MedProductCard_id;

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('MeasureFundCheck_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

        if ( action == 'add' ) {

            params.onHide = function() {
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                    grid.getTopToolbar().enable();
                }
            };

            grid.getStore().each(function(rec) {
                if ( rec.get('MeasureFundCheck_id') ) {
                    deniedMeasureFundCheckList.push(rec.get('MeasureFundCheck_setDate'));
                }
            });
        }
        else {
            if ( !selectedRecord ) {
                return false;
            }

            grid.getStore().each(function(rec) {
                if ( rec.get('MeasureFundCheck_id') && selectedRecord.get('MeasureFundCheck_id') != rec.get('MeasureFundCheck_id') ) {
                    deniedMeasureFundCheckList.push(rec.get('MeasureFundCheck_setDate'));
                }
            });

            formParams = selectedRecord.data;
            params.MeasureFundCheck_id = grid.getSelectionModel().getSelected().get('MeasureFundCheck_id');
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
            };

            if (params.MeasureFundCheck_id < 0) {
                params.MeasureFundCheck_setDate = grid.getSelectionModel().getSelected().get('MeasureFundCheck_setDate');
            }
        }

        params.action = action;
        params.callback = function(data) {
            if ( typeof data != 'object' || typeof data.deniedMeasureFundCheckData != 'object' ) {
                return false;
            }

            var record = grid.getStore().getById(data.deniedMeasureFundCheckData.MeasureFundCheck_id);

            if ( record ) {

                var grid_fields = [];

                grid.getStore().fields.eachKey(function(key, item) {
                    grid_fields.push(key);
                });

                for ( var i = 0; i < grid_fields.length; i++ ) {
                    record.set(grid_fields[i], data.deniedMeasureFundCheckData[grid_fields[i]]);
                }

                record.commit();
            }
            else {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('MeasureFundCheck_id') ) {
                    grid.getStore().removeAll();
                }

                data.deniedMeasureFundCheckData.MeasureFundCheck_id = -swGenTempId(grid.getStore());

                grid.getStore().loadData([ data.deniedMeasureFundCheckData ], true);
            }
        }.createDelegate(this);
        params.deniedMeasureFundCheckList = deniedMeasureFundCheckList;
        params.formMode = 'local';
        params.formParams = formParams;

        getWnd('swMeasureFundCheckEditWindow').show(params);
    },
    MeasureFundCheckDelete: function() {
        var _this = this;

        if ( this.action == 'view' ) {
            return false;
        }

         sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ( buttonId == 'yes' ) {
                    var grid = this.findById('MPC_MeasureFundCheckGrid').getGrid(),
                        idField = 'MeasureFundCheck_id',
                        record = grid.getSelectionModel().getSelected(),
                        params = {},
                        url = "/?c=LpuPassport&m=deleteMeasureFundCheck",
                        index = 0;

                    if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
                        return false;
                    }

                    params.MeasureFundCheck_id = record.get('MeasureFundCheck_id');
                    if (record.get('MeasureFundCheck_id') > 0) {
                        index = 1;
                    }

                    switch (index) {
                        case 0:
                            grid.getStore().remove(record);
                            break;
                        case 1:
                            if (!Ext.isEmpty(url)) {
                                Ext.Ajax.request({
                                    callback: function(opt, scs, response) {
                                        if (scs) {
                                            grid.getStore().remove(record);
                                        }
                                    }.createDelegate(this),
                                    params: params,
                                    url: url
                                });
                            }
                            break;
                    }

                    if ( grid.getStore().getCount() > 0 ) {
                        grid.getView().focusRow(0);
                        grid.getSelectionModel().selectFirstRow();
                    } else {
			            _this.findById('MPC_MeasureFundCheckGrid').getGrid().getTopToolbar().items.items[1].disable();
                    }
                }
            }.createDelegate(this),
            icon: Ext.MessageBox.QUESTION,
            msg: lang['vyi_deystvitelno_hotite_udalit_dannoe_svidetelstvo_o_poverke'],
            title: lang['vopros']
        });
    },
    openAmortizationEditWindow: function(action) {
        if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
            return false;
        }

        if ( this.action == 'view' ) {
            if ( action == 'add' ) {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }

        if ( getWnd('swAmortizationEditWindow').isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_otsenki_iznosa_uje_otkryito']);
            return false;
        }

        var deniedAmortizationList = [],
            formParams = {},
            grid = this.findById('MPC_AmortizationGrid').getGrid(),
            params = {},
            selectedRecord;

        params.MedProductCard_id = this.MedProductCard_id;

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('Amortization_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

        if ( action == 'add' ) {

            params.onHide = function() {
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                }
            };

            grid.getStore().each(function(rec) {
                if ( rec.get('Amortization_id') ) {
                    deniedAmortizationList.push(rec.get('Amortization_setDate'));
                }
            });
        }
        else {
            if ( !selectedRecord ) {
                return false;
            }

            grid.getStore().each(function(rec) {
                if ( rec.get('Amortization_id') && selectedRecord.get('Amortization_id') != rec.get('Amortization_id') ) {
                    deniedAmortizationList.push(rec.get('Amortization_setDate'));
                }
            });


            formParams = selectedRecord.data;
            params.Amortization_id = grid.getSelectionModel().getSelected().get('Amortization_id');
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
            };

            if (params.Amortization_id < 0) {
                params.Amortization_setDate = grid.getSelectionModel().getSelected().get('Amortization_setDate');
            }
        }

        params.action = action;
        params.callback = function(data) {
            if ( typeof data != 'object' || typeof data.deniedAmortizationData != 'object' ) {
                return false;
            }

            var record = grid.getStore().getById(data.deniedAmortizationData.Amortization_id);

            if ( record ) {

                var grid_fields = [];

                grid.getStore().fields.eachKey(function(key, item) {
                    grid_fields.push(key);
                });

                for ( var i = 0; i < grid_fields.length; i++ ) {
                    record.set(grid_fields[i], data.deniedAmortizationData[grid_fields[i]]);
                }

                record.commit();
            }
            else {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Amortization_id') ) {
                    grid.getStore().removeAll();
                }

                data.deniedAmortizationData.Amortization_id = -swGenTempId(grid.getStore());

                grid.getStore().loadData([ data.deniedAmortizationData ], true);
            }
        }.createDelegate(this);
        params.deniedAmortizationList = deniedAmortizationList;
        params.formMode = 'local';
        params.formParams = formParams;

        getWnd('swAmortizationEditWindow').show(params);
    },
    openWorkDataEditWindow: function(action) {
        if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
            return false;
        }

        if ( this.action == 'view' ) {
            if ( action == 'add' ) {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }

        if ( getWnd('swWorkDataEditWindow').isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_otsenki_iznosa_uje_otkryito']);
            return false;
        }

        var deniedWorkDataList = [],
            formParams = {},
            grid = this.findById('MPC_WorkDataGrid').getGrid(),
            params = {},
            selectedRecord;

        params.MedProductCard_id = this.MedProductCard_id;

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('WorkData_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

        if ( action == 'add' ) {

            params.onHide = function() {
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                }
            };

            grid.getStore().each(function(rec) {
                if ( rec.get('WorkData_id') ) {
                    deniedWorkDataList.push(rec.get('WorkData_WorkPeriod'));
                }
            });
        }
        else {
            if ( !selectedRecord ) {
                return false;
            }

            grid.getStore().each(function(rec) {
                if ( rec.get('WorkData_id') && selectedRecord.get('WorkData_id') != rec.get('WorkData_id') ) {
                    deniedWorkDataList.push(rec.get('WorkData_WorkPeriod'));
                }
            });


            formParams = selectedRecord.data;
            params.WorkData_id = grid.getSelectionModel().getSelected().get('WorkData_id');
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
            };

            if (params.WorkData_id < 0) {
                params.WorkData_WorkPeriod = grid.getSelectionModel().getSelected().get('WorkData_WorkPeriod');
            }
        }

        params.action = action;
        params.callback = function(data) {
            if ( typeof data != 'object' || typeof data.deniedWorkDataData != 'object' ) {
                return false;
            }

            var record = grid.getStore().getById(data.deniedWorkDataData.WorkData_id);

            if ( record ) {

                var grid_fields = [];

                grid.getStore().fields.eachKey(function(key, item) {
                    grid_fields.push(key);
                });

                for ( var i = 0; i < grid_fields.length; i++ ) {
                    record.set(grid_fields[i], data.deniedWorkDataData[grid_fields[i]]);
                }

                record.commit();
            }
            else {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('WorkData_id') ) {
                    grid.getStore().removeAll();
                }

                data.deniedWorkDataData.WorkData_id = -swGenTempId(grid.getStore());

                grid.getStore().loadData([ data.deniedWorkDataData ], true);
            }
        }.createDelegate(this);
        params.deniedWorkDataList = deniedWorkDataList;
        params.formMode = 'local';
        params.formParams = formParams;

        getWnd('swWorkDataEditWindow').show(params);
    },
    openDowntimeEditWindow: function(action) {
        if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
            return false;
        }

        if ( this.action == 'view' ) {
            if ( action == 'add' ) {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }

        if ( getWnd('swDowntimeEditWindow').isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_otsenki_iznosa_uje_otkryito']);
            return false;
        }

        var deniedDowntimeList = [],
            formParams = {},
            grid = this.findById('MPC_DowntimeGrid').getGrid(),
            params = {},
            selectedRecord;

        params.MedProductCard_id = this.MedProductCard_id;

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('Downtime_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

        if ( action == 'add' ) {

            params.onHide = function() {
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                }
            };

            grid.getStore().each(function(rec) {
                if ( rec.get('Downtime_id') ) {
                    deniedDowntimeList.push([rec.get('Downtime_begDate'), rec.get('DowntimeCause_id')]);
                }
            });
        }
        else {
            if ( !selectedRecord ) {
                return false;
            }

            grid.getStore().each(function(rec) {
                if ( rec.get('Downtime_id') && selectedRecord.get('Downtime_id') != rec.get('Downtime_id') ) {
                    deniedDowntimeList.push([rec.get('Downtime_begDate'), rec.get('DowntimeCause_id')]);
                }
            });


            formParams = selectedRecord.data;
            params.Downtime_id = grid.getSelectionModel().getSelected().get('Downtime_id');
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
            };

            if (params.Downtime_id < 0) {
                params.Downtime_begDate = grid.getSelectionModel().getSelected().get('Downtime_begDate');
                params.DowntimeCause_id = grid.getSelectionModel().getSelected().get('DowntimeCause_id');
            }
        }

        params.action = action;
        params.callback = function(data) {
            if ( typeof data != 'object' || typeof data.deniedDowntimeData != 'object' ) {
                return false;
            }

            var record = grid.getStore().getById(data.deniedDowntimeData.Downtime_id);

            if ( record ) {

                var grid_fields = [];

                grid.getStore().fields.eachKey(function(key, item) {
                    grid_fields.push(key);
                });

                for ( var i = 0; i < grid_fields.length; i++ ) {
                    record.set(grid_fields[i], data.deniedDowntimeData[grid_fields[i]]);
                }

                record.commit();
            }
            else {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Downtime_id') ) {
                    grid.getStore().removeAll();
                }

                data.deniedDowntimeData.Downtime_id = -swGenTempId(grid.getStore());

                grid.getStore().loadData([ data.deniedDowntimeData ], true);
            }
        }.createDelegate(this);
        params.deniedDowntimeList = deniedDowntimeList;
        params.formMode = 'local';
        params.formParams = formParams;

        getWnd('swDowntimeEditWindow').show(params);
    },
    consumableDelete: function() {
        var _this = this;

        if ( this.action == 'view' ) {
            return false;
        }

         sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ( buttonId == 'yes' ) {
                    var grid = this.findById('MPC_ConsumablesGrid').getGrid(),
                        idField = 'Consumables_id',
                        record = grid.getSelectionModel().getSelected(),
                        params = {},
                        url = "/?c=LpuPassport&m=deleteConsumables",
                        index = 0;

                    if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
                        return false;
                    }

                    params.Consumables_id = record.get('Consumables_id');
                    if (record.get('Consumables_id') > 0) {
                        index = 1;
                    }

                    switch (index) {
                        case 0:
                            grid.getStore().remove(record);
                            break;
                        case 1:
                            if (!Ext.isEmpty(url)) {
                                Ext.Ajax.request({
                                    callback: function(opt, scs, response) {
                                        if (scs) {
                                            grid.getStore().remove(record);
                                        }
                                    }.createDelegate(this),
                                    params: params,
                                    url: url
                                });
                            }
                            break;
                    }

                    if ( grid.getStore().getCount() > 0 ) {
                        grid.getView().focusRow(0);
                        grid.getSelectionModel().selectFirstRow();
                    } else {
			            _this.findById('MPC_ConsumablesGrid').getGrid().getTopToolbar().items.items[1].disable();
                    }
                }
            }.createDelegate(this),
            icon: Ext.MessageBox.QUESTION,
            msg: lang['vyi_deystvitelno_hotite_udalit_dannyiy_rashodnyiy_material'],
            title: lang['vopros']
        });
    },
    downtimeDelete: function() {
        var _this = this;

        if ( this.action == 'view' ) {
            return false;
        }

         sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ( buttonId == 'yes' ) {
                    var grid = this.findById('MPC_DowntimeGrid').getGrid(),
                        idField = 'Downtime_id',
                        record = grid.getSelectionModel().getSelected(),
                        params = {},
                        url = "/?c=LpuPassport&m=deleteDowntime",
                        index = 0;

                    if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
                        return false;
                    }

                    params.Downtime_id = record.get('Downtime_id');
                    if (record.get('Downtime_id') > 0) {
                        index = 1;
                    }

                    switch (index) {
                        case 0:
                            grid.getStore().remove(record);
                            break;
                        case 1:
                            if (!Ext.isEmpty(url)) {
                                Ext.Ajax.request({
                                    callback: function(opt, scs, response) {
                                        if (scs) {
                                            grid.getStore().remove(record);
                                        }
                                    }.createDelegate(this),
                                    params: params,
                                    url: url
                                });
                            }
                            break;
                    }

                    if ( grid.getStore().getCount() > 0 ) {
                        grid.getView().focusRow(0);
                        grid.getSelectionModel().selectFirstRow();
                    } else {
			            _this.findById('MPC_DowntimeGrid').getGrid().getTopToolbar().items.items[1].disable();
                    }
                }
            }.createDelegate(this),
            icon: Ext.MessageBox.QUESTION,
            msg: lang['vyi_deystvitelno_hotite_udalit_dannyiy_rashodnyiy_material'],
            title: lang['vopros']
        });
    },
    workDataDelete: function() {
        var _this = this;

        if ( this.action == 'view' ) {
            return false;
        }

        sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ( buttonId == 'yes' ) {
                    var grid = this.findById('MPC_WorkDataGrid').getGrid(),
                        idField = 'WorkData_id',
                        record = grid.getSelectionModel().getSelected(),
                        params = {},
                        url = "/?c=LpuPassport&m=deleteWorkData",
                        index = 0;

                    if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
                        return false;
                    }

                    params.WorkData_id = record.get('WorkData_id');
                    if (record.get('WorkData_id') > 0) {
                        index = 1;
                    }

                    switch (index) {
                        case 0:
                            grid.getStore().remove(record);
                            break;
                        case 1:
                            if (!Ext.isEmpty(url)) {
                                Ext.Ajax.request({
                                    callback: function(opt, scs, response) {
                                        if (scs) {
                                            grid.getStore().remove(record);
                                        }
                                    }.createDelegate(this),
                                    params: params,
                                    url: url
                                });
                            }
                            break;
                    }

                    if ( grid.getStore().getCount() > 0 ) {
                        grid.getView().focusRow(0);
                        grid.getSelectionModel().selectFirstRow();
                    } else {
			            _this.findById('MPC_WorkDataGrid').getGrid().getTopToolbar().items.items[1].disable();
                    }
                }
            }.createDelegate(this),
            icon: Ext.MessageBox.QUESTION,
            msg: lang['vyi_deystvitelno_hotite_udalit_dannuyu_zapis'],
            title: lang['vopros']
        });
    },
    amortizationDelete: function() {
        var _this = this;

        if ( this.action == 'view' ) {
            return false;
        }

         sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ( buttonId == 'yes' ) {
                    var grid = this.findById('MPC_AmortizationGrid').getGrid(),
                        idField = 'Amortization_id',
                        record = grid.getSelectionModel().getSelected(),
                        params = {},
                        url = "/?c=LpuPassport&m=deleteAmortization",
                        index = 0;

                    if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
                        return false;
                    }

                    params.Amortization_id = record.get('Amortization_id');
                    if (record.get('Amortization_id') > 0) {
                        index = 1;
                    }

                    switch (index) {
                        case 0:
                            grid.getStore().remove(record);
                            break;
                        case 1:
                            if (!Ext.isEmpty(url)) {
                                Ext.Ajax.request({
                                    callback: function(opt, scs, response) {
                                        if (scs) {
                                            grid.getStore().remove(record);
                                        }
                                    }.createDelegate(this),
                                    params: params,
                                    url: url
                                });
                            }
                            break;
                    }

                    if ( grid.getStore().getCount() > 0 ) {
                        grid.getView().focusRow(0);
                        grid.getSelectionModel().selectFirstRow();
                    } else {
			            _this.findById('MPC_AmortizationGrid').getGrid().getTopToolbar().items.items[1].disable();
                    }
                }
            }.createDelegate(this),
            icon: Ext.MessageBox.QUESTION,
            msg: lang['vyi_deystvitelno_hotite_udalit_dannuyu_otsenku_iznosa'],
            title: lang['vopros']
        });
    },
	initComponent: function() {
		var _this = this;

        this.formPanel = new Ext.Panel({
            frame: true,
            layout: 'column',
            region: 'north',
            id: 'MPCEW_panel',
            bodyStyle: 'padding: 5px',
            autoHeight: true,
            items:[
                new Ext.form.FormPanel({
                    border: false,
                    id: 'MPCEW_panelForm',
                    labelAlign: 'right',
                    labelWidth: 150,
                    items:[
                        {
                            allowBlank: false,
                            fieldLabel: lang['klass_mi'],
                            id: 'MPC_MedProductClass_id',
                            name: 'MedProductClass_id',
                            tabIndex: TABINDEX_SPEF,
                            width: 800,
                            listeners: {
                                select:  function(combo, newValue, oldValue)
                                {
                                    var medProdForm = _this.MedProductCardEditForm.getForm();
                                    //Проставляем наименование и модель МИ
                                    _this.findById('MPC_MedProductCard_Name').setValue(combo.getFieldValue('MedProductClass_Name'));
                                    _this.findById('MPC_MedProductCard_Model').setValue(combo.getFieldValue('MedProductClass_Model'));

                                    //Проставляем поля вкладки "Классификационные данные"
                                    medProdForm.findField('MedProductType_id').setValue(combo.getFieldValue('MedProductType_Name'));
                                    medProdForm.findField('CardType_id').setValue(combo.getFieldValue('CardType_Name'));
                                    medProdForm.findField('ClassRiskType_id').setValue(combo.getFieldValue('ClassRiskType_Name'));
                                    medProdForm.findField('FuncPurpType_id').setValue(combo.getFieldValue('FuncPurpType_Name'));
                                    medProdForm.findField('FZ30Type_id').setValue(combo.getFieldValue('FZ30Type_Name'));
                                    medProdForm.findField('FRMOEquipment_id').setValue(combo.getFieldValue('FRMOEquipment_Name'));
                                    medProdForm.findField('GMDNType_id').setValue(combo.getFieldValue('GMDNType_Name'));
                                    medProdForm.findField('MT97Type_id').setValue(combo.getFieldValue('MT97Type_Name'));
                                    medProdForm.findField('OKOFType_id').setValue(combo.getFieldValue('OKOFType_Name'));
                                    medProdForm.findField('OKPType_id').setValue(combo.getFieldValue('OKPType_Name'));
                                    medProdForm.findField('OKPDType_id').setValue(combo.getFieldValue('OKPDType_Name'));
                                    medProdForm.findField('TNDEDType_id').setValue(combo.getFieldValue('TNDEDType_Name'));
                                    medProdForm.findField('UseAreaType_id').setValue(combo.getFieldValue('UseAreaType_Name'));
                                    medProdForm.findField('UseSphereType_id').setValue(combo.getFieldValue('UseSphereType_Name'));
					
									if(typeof(newValue) == 'object'){
										if ( newValue.get('MedProductType_Code') && newValue.get('MedProductType_Code').inlist([7,8,9,10] ) ) {
											medProdForm.findField('MedProductCard_IsOutsorc').showContainer();							   
											medProdForm.findField('MedProductCard_BoardNumber').showContainer();						   
											medProdForm.findField('MedProductCard_Phone').showContainer();
											medProdForm.findField('MedProductCard_Glonass').showContainer();
										}
										else{
											medProdForm.findField('MedProductCard_IsOutsorc').hideContainer();							   
											medProdForm.findField('MedProductCard_BoardNumber').hideContainer();						   
											medProdForm.findField('MedProductCard_Phone').hideContainer();
											medProdForm.findField('MedProductCard_Glonass').hideContainer();
										}
									}

									if(_this.action == 'edit') {//#PROMEDWEB-15685
										if (Ext.isEmpty(combo.getFieldValue('CardType_Name'))) {
											sw.swMsg.show({
												buttons: {yes: langs('Да'), no: langs('Отмена')},
												fn: function (buttonId) {
													if (buttonId == 'yes') {
														params = {};
														params.action = 'edit';
														params.Lpu_id = _this.Lpu_id;
														params.MedProductClass_id = combo.getValue();
														params.callback = function (data) {
															if (!Ext.isEmpty(data.MedProductClass_Name)) {
																var uper_form = _this.findById('MPCEW_panelForm').getForm();
																uper_form.findField('MedProductClass_id').getStore().load({
																	params: {
																		MedProductClass_id: data.MedProductClass_id,
																		Lpu_id: _this.Lpu_id
																	},
																	callback: function () {
																		uper_form.findField('MedProductClass_id').setValue(combo.getValue());
																		uper_form.findField('MedProductClass_id').fireEvent('select', uper_form.findField('MedProductClass_id'), uper_form.findField('MedProductClass_id').getValue());
																	}
																});
															}

															getWnd('swMedProductClassEditWindow').hide();
														};
														getWnd('swMedProductClassEditWindow').show(params);
													}
												},
												msg: langs('У выбранного класса МИ не указан тип медицинского оборудования. Редактировать?'),
												title: langs('Вопрос')
											});
										}
									}
								}
							},
							xtype: 'swmedprodclasscomboex'
						},{
							fieldLabel: lang['naimenovanie_mi'],
							id: 'MPC_MedProductCard_Name',
							name: 'MedProductCard_Name',
							tabIndex: TABINDEX_SPEF,
							width: 800,
							disabled: true,
							xtype: 'textfield'
						},{
							fieldLabel: lang['model_mi'],
							id: 'MPC_MedProductCard_Model',
							name: 'MedProductCard_Model',
							tabIndex: TABINDEX_SPEF + 1,
							width: 800,
							disabled: true,
							xtype: 'textfield'
						}
					]
				})
			]
		});

		this.MedProductCardEditForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			frame: true,
			labelAlign: 'right',
			labelWidth: 145,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'MedProductCard_id' },
				{ name: 'MedProductCard_Name' },
				{ name: 'MedProductClass_id' },
				{ name: 'MedProductCard_Model' },
				{ name: 'AccountingData_InventNumber' },
				{ name: 'MedProductCard_SerialNumber' },
				{ name: 'AccountingData_RegNumber' },
				{ name: 'MedProductCard_BoardNumber' },
				{ name: 'MedProductCard_Phone' },
                { name: 'MedProductCard_Glonass' },
				{ name: 'LpuBuilding_id' },
				{ name: 'LpuUnit_id' },
				{ name: 'LpuSection_id' },
				{ name: 'SubSection_Name' },
				{ name: 'MedProductCard_begDate' },
				{ name: 'MedProductCard_IsEducatAct' },
                { name: 'MedProductCard_IsNoAvailLpu' },
                { name: 'MedProductCard_IsNotFRMO' },
				{ name: 'MedProductCard_UsePeriod' },
				{ name: 'PrincipleWorkType_id' },
				{ name: 'RegCertificate_setDate' },
				{ name: 'RegCertificate_endDate' },
				{ name: 'RegCertificate_Number' },
				{ name: 'RegCertificate_OrderNumber' },
				{ name: 'RegCertificate_MedProductName' },
				{ name: 'MedProductCard_Options' },
				{ name: 'MedProductCard_OtherParam' },
				{ name: 'MeasureFund_Range' },
				{ name: 'MeasureFund_IsMeasure' },
				{ name: 'MeasureFund_RegNumber' },
				{ name: 'MeasureFund_AccuracyClass' },
				{ name: 'AccountingData_buyDate' },
				{ name: 'AccountingData_setDate' },
				{ name: 'AccountingData_begDate' },
				{ name: 'AccountingData_endDate' },
				{ name: 'GosContract_Number' },
				{ name: 'GosContract_setDate' },
				{ name: 'AccountingData_BuyCost' },
				{ name: 'AccountingData_ProductCost' },
				{ name: 'MedProductCard_IsRepair' },
				{ name: 'MedProductCard_IsSpisan' },
				{ name: 'Org_id' },
				{ name: 'Org_regid' },
				{ name: 'Org_prid' },
				{ name: 'Org_decid' },
				{ name: 'Org_toid' },
				{ name: 'OkeiLink_id' },
				{ name: 'FinancingType_id'},
				{ name: 'DeliveryType_id'},
				{ name: 'MedProductCard_SetResource'},
				{ name: 'MedProductCard_AvgProcTime'},
				{ name: 'MedProductCard_SpisanDate'},
				{ name: 'MedProductCard_RepairDate'},
				{ name: 'MedProductCard_IsContractTO'},
				{ name: 'MedProductCard_IsOrgLic'},
				{ name: 'MedProductCard_IsLpuLic'},
				{ name: 'MedProductCard_DocumentTO'},
				{ name: 'PropertyType_id'},
				{ name: 'MedProductCard_IsOutsorc'},
				{ name: 'MedProductCard_IsAvailibleSpecialists'},
				{ name: 'MedProductCard_IsClockMode'},
				{ name: 'MedProductClassForm_secid'},
				{ name: 'MedProductClassForm_strid'},
				{ name: 'MedProductClassForm_fsubid'},
				{ name: 'MedProductClassForm_ssubid'},
				{ name: 'MedProductCard_IsWorkList'},
				{ name: 'MedProductCard_AETitle'},
				{ name: 'LpuEquipmentPacs_id'},
				{ name: 'FRMOEquipment_id'},
				{ name: 'FRMOEquipment_Name'}
			]),
            layout: 'fit',
			url: '/?c=LpuPassport&m=saveMedProductCard',
			items: [
                new Ext.TabPanel({
                    region: 'center',
                    defaults: {bodyStyle:'width:100%;'},
                    activeTab: 0,
                    autoScroll: true,
                    enableTabScroll: true,
                    layoutOnTabChange: true,
                    border: false,
                    id: 'MedProductCardEdit',
                    items: [{
                        height: 400,
                        labelWidth: 250,
                        layout: 'form',
                        autoScroll: true,
                        border:false,
                        //layout: 'fit',
                        tabPosition:'top',
                        style: 'padding: 2px',
                        id: 'baseInfo',
                        title: lang['1_osnovnaya_informatsiya'],
                        items: [{
                                id: 'MPC_MedProductCard_id',
                                name: 'MedProductCard_id',
                                xtype: 'hidden'
                            },
							{
                                id: 'MPC_Lpu_id',
                                name: 'Lpu_id',
                                xtype: 'hidden'
                            },
							{
                                xtype:'checkbox',
                                tabIndex: TABINDEX_SPEF + 1,
                                fieldLabel:lang['po_dogovory_autsorsinga'],
                                name: 'MedProductCard_IsOutsorc',
                                id: 'MPC_MedProductCard_IsOutsorc',
								listeners: {
									check: function(cmp, checked){
										var base_form = _this.MedProductCardEditForm.getForm();
										
										//обязательные для заполнения при флаге "по договору аутсорсинга"
										base_form.findField('AccountingData_InventNumber').allowBlank = checked;
                                        base_form.findField('MedProductCard_SerialNumber').allowBlank = checked;

                                        _this.MedProductCardEditForm.findById('proizvoditel_id').allowBlank = checked;
										
										//вкладка бух учет
										base_form.findField('AccountingData_buyDate').allowBlank = checked;
										base_form.findField('FinancingType_id').allowBlank = checked;
										base_form.findField('AccountingData_ProductCost').allowBlank = checked;
										base_form.findField('PropertyType_id').allowBlank = checked;
										base_form.findField('AccountingData_BuyCost').allowBlank = checked;
										base_form.findField('DeliveryType_id').allowBlank = checked;
                                        var params = {};
                                        params.MedProductClass_id = _this.formPanel.findById('MPC_MedProductClass_id').getValue();
										base_form.submit({params:{MedProductClass_id: params.MedProductClass_id}});
									}
                                },
                                handler: function(value) {
                                    //убираем флаг с "Не передавать на ФРМО", задание 102811
                                    var IsNotFRMO = _this.MedProductCardEditForm.findById('MPC_MedProductCard_IsNotFRMO');

                                    if(value.checked) {
                                        IsNotFRMO.checked = false;
                                        IsNotFRMO.disable();

                                        _this.MedProductCardEditForm.findById('proizvoditel_id').allowBlank = true;
                                    } else {
                                        IsNotFRMO.checked = false;
                                        IsNotFRMO.enable();

                                        _this.MedProductCardEditForm.findById('proizvoditel_id').allowBlank = false;
                                    }
                                }
                            },
							{
                                allowBlank: false,
                                fieldLabel: lang['inventarnyiy_nomer'],
                                id: 'MPC_AccountingData_InventNumber',
                                name: 'AccountingData_InventNumber',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 2,
                                xtype: 'textfield'
                            },
							{
                                allowBlank: false,
                                fieldLabel: lang['seriynyiy_nomer'],
                                id: 'MPC_MedProductCard_SerialNumber',
                                name: 'MedProductCard_SerialNumber',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 3,
                                xtype: 'textfield'
                            },
							{
                                fieldLabel: lang['registratsionnyiy_znak_dlya_avtomobiley'],
                                id: 'MPC_AccountingData_RegNumber',
                                name: 'AccountingData_RegNumber',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 4,
                                xtype: 'textfield'
                            }, 
							{
                                fieldLabel: lang['bortovoy_nomer'],
                                id: 'MPC_MedProductCard_BoardNumber',
                                name: 'MedProductCard_BoardNumber',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 4,
                                xtype: 'textfield'
                            },
							{
                                fieldLabel: 'Телефон',
								plugins: [ new Ext.ux.InputTextMask('+7 (999)-999-99-99', true) ],
                                name: 'MedProductCard_Phone',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 4,
                                xtype: 'textfield'
                            },
							{
                                allowBlank: false,
                                anchor: '100%',
                                Lpu_id: _this.Lpu_id,
                                allowLowLevelRecordsOnly: false,
                                fieldLabel: lang['podrazdelenie'],
                                id: 'MPCE_SubSection_Name',
                                name: 'SubSection_Name',
                                object: 'SubDivision',
                                tabIndex: TABINDEX_SPEF + 5,
                                Sub_SysNick: '',
                                selectionWindowParams: {
                                    height: 500,
                                    title: lang['podrazdelenie'],
                                    width: 600
                                },
                                //при выборе подразделения, очистка поля глонасс если не скрыто
                                callback: function(data){

                                    log('data: ' + JSON.stringify(data));

                                    var thisCombo = this;
                                    thisCombo.loadTargetCombo(data);
                                },
                                valueFieldId: 'MPCE_SubSection_id',
                                xtype: 'swtreeselectionfield',
                                loadTargetCombo: function(data) {

                                    var base_form = _this.MedProductCardEditForm.getForm(),
                                        target = base_form.findField('MedProductCard_Glonass'),
                                        targetIsHidden = target.hidden;

                                    // если поле не скрыто на форме
                                    if (!targetIsHidden) {

                                        target.reset();
                                        target.loadGlonassCombo(data);
                                    }
                                }
                            },
                            {
                                    xtype: 'swEmergencyTeamWialonCombo',
                                    name: 'MedProductCard_Glonass',
                                    hiddenName: 'MedProductCard_Glonass',
                                    tabIndex: TABINDEX_SPEF + 4,
                                    loadGlonassCombo: function(data) {

                                        var LpuBuilding_id = null;

                                        if (data.parentNodeArray)
                                            if (data.parentNodeArray.length > 1)
                                                LpuBuilding_id = data.parentNodeArray[data.parentNodeArray.length - 1];

                                        var thisCombo = this,
                                            comboParams = {

                                                Lpu_id: _this.Lpu_id,
                                                LpuBuilding_id: LpuBuilding_id,
                                                Sub_SysNick: data.Sub_SysNick,
                                                LpuDepartment_id: data.id,

                                                // чтобы контроллер Виалона\ТНЦ знал, что авторизация
                                                // будет проходить через учетку выбранного подразделения
                                                // а не через учетку пользователя СМП
                                                GlonassAuthByDepartment: true
                                            };

                                        thisCombo.getStore().load({

                                                params: comboParams,
                                                callback: function (storeArray) {

                                                    if (!storeArray || storeArray && storeArray.length  == 0) {

                                                        log('no_glonass_data!');
                                                        thisCombo.reset();
                                                    }
                                                }
                                            }
                                        );
                                    }
                            },
                            //xtype: (getGlobalOptions().region.number == 2) ? 'swEmergencyTeamTNCCombo' : 'swEmergencyTeamWialonCombo',

                            {
                                id: 'MPCE_SubSection_id',
                                name: 'SubSection_id',
                                xtype: 'hidden'
                            }, {
                                id: 'MPCE_LpuBuilding_id',
                                name: 'LpuBuilding_id',
                                xtype: 'hidden'
                            }, {
                                id: 'MPCE_LpuUnit_id',
                                name: 'LpuUnit_id',
                                xtype: 'hidden'
                            }, {
                                id: 'MPCE_LpuSection_id',
                                name: 'LpuSection_id',
                                xtype: 'hidden'
                            }, {
                                xtype: 'sworgcomboex',
                                hiddenName: 'Org_id',
                                tabIndex: TABINDEX_SPEF + 6,
                                fieldLabel: lang['postavschik']
                            },{
                                allowBlank: false,
                                xtype: 'swdatefield',
                                fieldLabel: lang['data_vyipuska'],
                                format: 'd.m.Y',
                                tabIndex: TABINDEX_SPEF + 7,
                                name: 'MedProductCard_begDate',
                                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
                            },{
                                allowBlank: false,
                                editable: true,
                                fieldLabel: lang['srok_slujbi'],
                                id: 'MPC_MedProductCard_UsePeriod',
                                name: 'MedProductCard_UsePeriod',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 8,
                                autoCreate: {tag: "input", size: 14, autocomplete: "off"},
                                xtype: 'textfield',
                                minValue: 1,
                                maxValue: 100
                            },{
                                xtype:'checkbox',
                                tabIndex: TABINDEX_SPEF + 8,
                                fieldLabel: 'Бессрочно',
                                name: 'MedProductCard_UsePeriod_Check',
                                id: 'MPC_MedProductCard_UsePeriod_Check',
                                checked: '',
                                handler: function(value) {
                                    var form = _this.MedProductCardEditForm;
                                    var base_form = form.getForm();
                                    var UsePeriod = _this.MedProductCardEditForm.findById('MPC_MedProductCard_UsePeriod');

                                    if(value.checked) {
                                        UsePeriod.setValue('');
                                        UsePeriod.allowBlank = true;
                                        UsePeriod.editable = false;
                                        UsePeriod.disable();
                                    } else {
                                        UsePeriod.allowBlank = false;
                                        UsePeriod.setValue('');
                                        UsePeriod.editable = true;
                                        UsePeriod.enable();
                                    }
                                }
                            },{
                                fieldLabel: 'Принцип работы',
								xtype: 'swcommonsprcombo',
                                id: 'MPC_PrincipleWorkType_id',
								hiddenName: 'PrincipleWorkType_id',
								comboSubject: 'PrincipleWorkType',
                                tabIndex: TABINDEX_SPEF + 8,
								width: 200,
								listeners: {
									select: function(combo, record, idx) {
										var base_form = _this.MedProductCardEditForm.getForm();
										base_form.findField('MPC_MedProductCard_IsWorkList').setContainerVisible(typeof record == 'object' && 
										record.get('PrincipleWorkType_id') == 2 );
										base_form.findField('MPC_MedProductCard_AETitle').setContainerVisible(typeof record == 'object' && 
										record.get('PrincipleWorkType_id') == 2 );
										base_form.findField('MPC_LpuEquipmentPacs_id').setContainerVisible(typeof record == 'object' && 
										record.get('PrincipleWorkType_id') == 2 );
									}
								}
                            },{
                                fieldLabel: 'Работа с рабочим списком',
								xtype: 'checkbox',
                                id: 'MPC_MedProductCard_IsWorkList',
                                name: 'MedProductCard_IsWorkList',
                                tabIndex: TABINDEX_SPEF + 8,
                                listeners: {
                                    check: function(checkbox, checked) {
										var base_form = _this.MedProductCardEditForm.getForm()
                                        var aeTitleFild = base_form.findField('MPC_MedProductCard_AETitle');
                                        var pacsField = base_form.findField('MPC_LpuEquipmentPacs_id');
                                        if(checked === true) {
                                            aeTitleFild.setDisabled(false);
                                            aeTitleFild.allowBlank = false;
                                            pacsField.setDisabled(false);
                                            pacsField.allowBlank = false;
                                        } else {
                                            aeTitleFild.setValue('');
                                            aeTitleFild.setDisabled(true);
                                            aeTitleFild.allowBlank = true;
                                            pacsField.setValue('');
                                            pacsField.setDisabled(true);
                                            pacsField.allowBlank = true;
                                        }
                                    }
                                }
                            },{
                                fieldLabel: 'AE Title',
								xtype: 'textfield',
                                id: 'MPC_MedProductCard_AETitle',
                                name: 'MedProductCard_AETitle',
                                disabled: true,
                                tabIndex: TABINDEX_SPEF + 8,
                                width: 200
                            },{
                                fieldLabel: 'PACS',
								xtype: 'swcommonsprcombo',
                                id: 'MPC_LpuEquipmentPacs_id',
                                hiddenName: 'LpuEquipmentPacs_id',
                                disabled: true,
                                tabIndex: TABINDEX_SPEF + 8,
                                displayField: 'LpuEquipment_Name',
                                valueField: 'LpuEquipmentPacs_id',
                                store: new Ext.data.JsonStore({
                                    autoLoad: true,
                                    url: '/?c=LpuPassport&m=loadLpuEquipment',
                                    baseParams: {Lpu_id: getGlobalOptions().lpu_id? getGlobalOptions().lpu_id : null},
                                    fields: [
                                        {name: 'LpuEquipmentPacs_id', type: 'int'},
                                        {name: 'LpuEquipment_Name', type: 'string'},
                                    ]
                                }),
                                listeners: {
                                    enable: function(pacsCombo) {
                                        if(pacsCombo.store.getCount() === 1) {
                                            var valuePACS = pacsCombo.store.data.items[0].get('LpuEquipmentPacs_id');
                                            pacsCombo.setValue(valuePACS);
                                            pacsCombo.setDisabled(true);
                                        }
                                    }
                                },
                                width: 200
                            },{
                                xtype:'checkbox',
                                tabIndex: TABINDEX_SPEF + 46,
                                fieldLabel:lang['nalichie_akta_ob_obuchenii_med_personala_rabote_na_mi'],
                                name: 'MedProductCard_IsEducatAct',
                                id: 'MPC_MedProductCard_IsEducatAct'
                            },{
                                xtype:'checkbox',
                                tabIndex: TABINDEX_SPEF + 47,
                                fieldLabel: 'Недоступна для МО',
                                name: 'MedProductCard_IsNoAvailLpu',
                                id: 'MPC_MedProductCard_IsNoAvailLpu',
                                handler: function(value) {
                                    var IsNotFRMO = _this.MedProductCardEditForm.findById('MPC_MedProductCard_IsNotFRMO');

                                    if(value.checked) {
                                        IsNotFRMO.checked = false;
                                        IsNotFRMO.disable();
                                    } else {
                                        IsNotFRMO.enable();
                                    }
                                }
                            },{
                                xtype:'checkbox',
                                tabIndex: TABINDEX_SPEF + 49,
                                fieldLabel: lang['nedostypno_dlia_FRMO'],
                                name: 'MedProductCard_IsNotFRMO',
                                id: 'MPC_MedProductCard_IsNotFRMO',
                            }
                        ]
                    },{
                        height: 400,
                        labelWidth: 270,
                        layout: 'form',
                        style: 'padding: 2px',
                        id: 'class_data',
                        autoScroll: true,
                        border:false,
                        //layout: 'fit',
                        tabPosition:'top',
                        title: lang['2_klassifikatsionnyie_dannyie'],
                        items: [
                            {
                                fieldLabel: lang['vid_mi'],
                                name: 'MedProductType_id',
                                tabIndex: TABINDEX_SPEF + 10,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['klass_potentsialnogo_riska_primeneniya'],
                                name: 'ClassRiskType_id',
                                width: 400,
                                disabled: true,
                                tabIndex: TABINDEX_SPEF + 9,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['funktsionalnoe_naznachenie'],
                                name: 'FuncPurpType_id',
                                tabIndex: TABINDEX_SPEF + 10,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['tip_meditsinskogo_izdeliya'],
                                name: 'CardType_id',
                                tabIndex: TABINDEX_SPEF + 11,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['tip_meditsinskogo_oborudovania_frmo'],
                                name: 'FRMOEquipment_id',
                                tabIndex: TABINDEX_SPEF + 12,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['oblast_primeneniya'],
                                name: 'UseAreaType_id',
                                tabIndex: TABINDEX_SPEF + 13,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['sfera_primeneniya'],
                                name: 'UseSphereType_id',
                                tabIndex: TABINDEX_SPEF + 14,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['30y_fz'],
                                name: 'FZ30Type_id',
                                tabIndex: TABINDEX_SPEF + 15,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['tn_ved'],
                                name: 'TNDEDType_id',
                                tabIndex: TABINDEX_SPEF + 16,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: 'GMDN',
                                name: 'GMDNType_id',
                                tabIndex: TABINDEX_SPEF + 17,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['mt_po_97pr'],
                                name: 'MT97Type_id',
                                tabIndex: TABINDEX_SPEF + 18,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['okof'],
                                name: 'OKOFType_id',
                                tabIndex: TABINDEX_SPEF + 19,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['okp'],
                                name: 'OKPType_id',
                                tabIndex: TABINDEX_SPEF + 20,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['okpd'],
                                name: 'OKPDType_id',
                                tabIndex: TABINDEX_SPEF + 21,
                                width: 400,
                                disabled: true,
                                xtype: 'textfield'
                            }
                        ]
                    },{
                        height: 400,
                        //autoheight: true,
                        labelWidth: 350,
                        layout: 'form',
                        style: 'padding: 2px',
                        id: 'RegCertificate',
                        title: lang['3_registratsionnyie_dannyie'], //RegCertificate
                        items: [
                            {
                                xtype: 'swdatefield',
                                fieldLabel: lang['srok_deystviya_reg_udostovereniya'],
                                format: 'd.m.Y',
                                tabIndex: TABINDEX_SPEF + 21,
                                name: 'RegCertificate_endDate',
                                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
                            },{
                                xtype: 'swdatefield',
                                fieldLabel: lang['data_registratsionnogo_udostovereniya'],
                                format: 'd.m.Y',
                                tabIndex: TABINDEX_SPEF + 21,
                                name: 'RegCertificate_setDate',
                                id: 'RegCertificate_setDate_id',
                                //begDateField: 'Org_begDate',
                                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                listeners: {
                                    change: function(value) {
                                        var base_form = _this.MedProductCardEditForm;
                                        var RegCertificateNumber = base_form.findById('MPC_RegCertificate_Number');
    
                                        if(value.getValue()) {
                                            RegCertificateNumber.allowBlank = false;
                                        } else {
                                            RegCertificateNumber.allowBlank = true;
                                        }
                                    }
                                }
                            },{
                                allowBlank: true,
                                fieldLabel: lang['nomer_registratsionnogo_udostovereniya'],
                                id: 'MPC_RegCertificate_Number',
                                name: 'RegCertificate_Number',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 22,
                                xtype: 'textfield',
                                listeners: {
                                    change: function(value) {
                                        var base_form = _this.MedProductCardEditForm;
                                        var RegCertificateSetDate = base_form.findById('RegCertificate_setDate_id');
    
                                        if(value.getValue()) {
                                            RegCertificateSetDate.allowBlank = false;
                                        } else {
                                            RegCertificateSetDate.allowBlank = true;
                                        }
                                    }
                                }
                            },{
                                fieldLabel: lang['nomer_prikaza'],
                                id: 'MPC_RegCertificate_OrderNumber',
                                name: 'RegCertificate_OrderNumber',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 23,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['naimenovanie_mi_po_registratsionnyim_dokumentam'],
                                id: 'MPC_RegCertificate_MedProductName',
                                name: 'RegCertificate_MedProductName',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 24,
                                xtype: 'textfield'
                            },{
                                xtype: 'sworgcomboex',
                                //anchor: '100%',
                                tabIndex: TABINDEX_SPEF + 25,
                                hiddenName: 'Org_regid',
                                //disabled: true,
                                fieldLabel: lang['derjatel_udostovereniya']
                            },{
                                allowBlank: true,
                                xtype: 'sworgcomboex',
                                //anchor: '100%',
                                tabIndex: TABINDEX_SPEF + 26,
                                hiddenName: 'Org_prid',
                                id: 'proizvoditel_id',
                                //disabled: true,
                                allowEmptyUAddress: '0',
                                fieldLabel: lang['proizvoditel'],
                                listeners: {
                                    check: function(cmp, checked){
										var base_form = _this.MedProductCardEditForm.getForm();
										
										//обязательные для заполнения при флаге "по договору аутсорсинга"
										if (base_form.findField('MedProductCard_IsOutsorc').checked) {
                                            base_form.findById('proizvoditel_id').allowBlank = true;
                                        } else {
                                            base_form.findById('proizvoditel_id').allowBlank = false;
                                        }
                                    }
                                }
                            },{
                                xtype: 'sworgcomboex',
                                //anchor: '100%',
                                tabIndex: TABINDEX_SPEF + 27,
                                hiddenName: 'Org_decid',
                                //disabled: true,
                                fieldLabel: lang['deklarant']
                            }
                        ]
                    },{
                        height: 400,
                        //autoheight: true,
                        labelWidth: 143,
                        layout: 'form',
                        style: 'padding: 2px',
                        id: 'Consumables',
                        title: lang['4_komplektatsiya_rahodnyie_materialyi'], //MedProductCard_Options
                        items: [
                            {

                                fieldLabel: lang['komplektatsiya'],
                                id: 'MPC_MedProductCard_Options',
                                name: 'MedProductCard_Options',
                                tabIndex: TABINDEX_SPEF + 28,
                                width: 600,
                                xtype: 'textarea'
                            }, {

                                fieldLabel: lang['prochie_parametryi'],
                                id: 'MPC_MedProductCard_OtherParam',
                                name: 'MedProductCard_OtherParam',
                                width: 600,
                                tabIndex: TABINDEX_SPEF + 29,
                                xtype: 'textarea'
                            },
                            new sw.Promed.Panel({
                                autoHeight: true,
                                style:'margin-bottom: 0.5em;',
                                border: true,
                                collapsible: false,
                                collapsed: false,
                                id: 'MPC_Consumables',
                                layout: 'form',
                                title: lang['rashodnyie_materialyi'],
                                listeners: {
                                    collapse: function ()  {
                                        _this.syncShadow();
                                    }.createDelegate(this)
                                },
                                items: [
                                    new sw.Promed.ViewFrame({
                                        actions: [
                                            {name: 'action_add', handler: function() { _this.openConsumablesEditWindow('add'); }.createDelegate(this) },
                                            {name: 'action_edit', handler: function() { _this.openConsumablesEditWindow('edit'); }.createDelegate(this) },
                                            {name: 'action_view', hidden: true },
                                            {name: 'action_delete', handler: function() { _this.consumableDelete(); }.createDelegate(this) },
                                            {name: 'action_refresh', disabled: true, hidden: true},
                                            {name: 'action_print', hidden: true }
                                        ],
                                        object: 'Consumables',
                                        editformclassname: 'swConsumablesEditWindow',
                                        autoExpandColumn: 'autoexpand',
                                        autoExpandMin: 150,
                                        autoLoadData: false,
                                        border: false,
                                        scheme: 'passport',
                                        dataUrl: '/?c=LpuPassport&m=loadConsumables',
                                        id: 'MPC_ConsumablesGrid',
                                        paging: false,
                                        region: 'center',
                                        stringfields: [
                                            {name: 'Consumables_id', type: 'int', header: 'Consumables_id', key: true},
                                            {name: 'MedProductCard_id', type: 'int', header: 'MedProductCard_id', hidden: true, isparams: true },
                                            {name: 'Consumables_Name', type: 'string', header: lang['naimenovanie_rashodnogo_materiala'], isparams: true, width: 200}
                                        ],
                                        toolbar: true,
                                        onLoadData: function() {
                                            if (!Ext.isEmpty(_this.MedProductCard_id)) {
                                                this.setActionDisabled('action_refresh', false);
                                            }
                                        }
                                        //totalProperty: 'totalCount'
                                    })
                                ]
                            })
                        ]
                    },{
                        height: 400,
                        //autoheight: true,
                        labelWidth: 200,
                        layout: 'form',
                        style: 'padding: 2px',
                        id: 'MeasureFund',
                        title: lang['5_sredstva_izmereniya'], //MeasureFund
                        items: [
                            {
                                xtype:'checkbox',
                                checked: false,
                                tabIndex: TABINDEX_SPEF + 35,
                                fieldLabel:lang['yavlyaetsya_sredstvom_izmereniya'],
                                handler: function(value) {
                                    var MeasureFundCheckGrid = _this.MedProductCardEditForm.findById('MPC_MeasureFundCheckGrid').getGrid(),
                                        okeiLink = _this.MedProductCardEditForm.getForm().findField('OkeiLink_id');
                                    if (value.checked) {
                                        okeiLink.allowBlank = false;
                                        MeasureFundCheckGrid.getTopToolbar().enable();

                                        if (MeasureFundCheckGrid.getStore().getCount() == 0 || !MeasureFundCheckGrid.getSelectionModel().getSelected() || Ext.isEmpty(MeasureFundCheckGrid.getSelectionModel().getSelected().get('MeasureFundCheck_id'))) {
                                            MeasureFundCheckGrid.getTopToolbar().items.items[1].disable();
                                            MeasureFundCheckGrid.getTopToolbar().items.items[3].disable();
                                        }

                                    } else {
                                        okeiLink.allowBlank = true;
                                        MeasureFundCheckGrid.getTopToolbar().disable();
                                    }
                                },
                                name: 'MeasureFund_IsMeasure',
                                id: 'MPC_MeasureFund_IsMeasure'
                            }, {
                                fieldLabel: lang['diapazon_izmereniy'],
                                id: 'MPC_MeasureFund_Range',
                                name: 'MeasureFund_Range',
                                tabIndex: TABINDEX_SPEF + 40,
                                width: 200,
                                xtype: 'textfield'
                            }, {
                                width: 200,
                                hiddenName: 'OkeiLink_id',
                                tabIndex: TABINDEX_SPEF + 45,
                                xtype: 'swokeilinkcombo'
                            }, {
                                fieldLabel: lang['registratsionnyiy_nomer_sredstv_izmereniya'],
                                id: 'MPC_MeasureFund_RegNumber',
                                name: 'MeasureFund_RegNumber',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 50,
                                xtype: 'textfield'
                            },{
                                fieldLabel: lang['klass_tochnosti_sredstv_izmereniya'],
                                id: 'MPC_MeasureFund_AccuracyClass',
                                name: 'MeasureFund_AccuracyClass',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 55,
                                xtype: 'textfield'
                            },
                            new sw.Promed.Panel({
                                autoHeight: true,
                                style:'margin-bottom: 0.5em;',
                                border: true,
                                collapsible: false,
                                collapsed: false,
                                id: 'MPC_MeasureFundCheck',
                                layout: 'form', 
                                title: lang['svidetelstva_o_proverke_sredstv_izmereniy'],
                                listeners: {
                                    collapse: function ()  {
                                        _this.syncShadow();
                                    }.createDelegate(this)
                                },
                                items: [
                                    new sw.Promed.ViewFrame({
                                        actions: [
                                            {name: 'action_add', disabled: true, handler: function() { _this.openMeasureFundCheckEditWindow('add'); } },
                                            {name: 'action_edit', disabled: true, handler: function() { _this.openMeasureFundCheckEditWindow('edit'); } },
                                            {name: 'action_view', disabled: true, hidden: true},
                                            {name: 'action_delete', disabled: true, handler: function() { _this.MeasureFundCheckDelete(); } },
                                            {name: 'action_refresh', disabled: true, hidden: true},
                                            {name: 'action_print', hidden: true }
                                        ],
                                        object: 'MeasureFundCheck',
                                        editformclassname: 'swMeasureFundCheckEditWindow',
                                        autoExpandColumn: 'autoexpand',
                                        autoExpandMin: 150,
                                        autoLoadData: false,
                                        border: false,
                                        scheme: 'passport',
                                        dataUrl: '/?c=LpuPassport&m=loadMeasureFundCheck',
                                        id: 'MPC_MeasureFundCheckGrid',
                                        paging: false,
                                        region: 'center',
                                        stringfields: [
                                            {name: 'MeasureFundCheck_id', type: 'int', header: 'MeasureFundCheck_id', key: true},
                                            {name: 'MedProductCard_id', type: 'int', header: 'MedProductCard_id', hidden: true, isparams: true },
                                            {name: 'MeasureFundCheck_setDate', type: 'date', header: lang['data_svidetelstva'], isparams: true, width: 200},
                                            {name: 'MeasureFundCheck_Number', type: 'string', header: lang['nomer_svidetelstva'], isparams: true, width: 200},
                                            {name: 'MeasureFundCheck_endDate', type: 'date', header: lang['srok_deystviya_svidetelstva'], isparams: true, width: 200}
                                        ],
                                        toolbar: true,
                                        onLoadData: function() {
                                            if (!Ext.isEmpty(_this.MedProductCard_id)) {
                                                this.setActionDisabled('action_refresh', false);
                                            }
                                        },
                                        onDblClick: function(grid, number, object){
                                            if (_this.action != 'view') {
                                                _this.openMeasureFundCheckEditWindow('edit');
                                            } else {
                                                _this.openMeasureFundCheckEditWindow('view');
                                            }

                                        }
                                        //totalProperty: 'totalCount'
                                    })
                                ]
                            })
                        ]
                    },{
                        height: 400,
                        //autoheight: true,
                        labelWidth: 200,
                        layout: 'form',
                        style: 'padding: 2px',
                        id: 'BuhUchet',
                        title: '<u>6</u>. Бухгалтерский учёт', //AccountingData и GosContract
                        items: [
                            {
                                border: false,
                                layout: 'column',
                                //labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [{
                                        xtype: 'swdatefield',
                                        width: 100,
                                        tabIndex: TABINDEX_SPEF + 35,
                                        allowBlank: false,
                                        fieldLabel: lang['data_priobreteniya'],
                                        format: 'd.m.Y',
                                        name: 'AccountingData_buyDate',
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
                                    }]
                                },{
                                    border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [ {
                                        xtype: 'swdatefield',
                                        width: 100,
                                        tabIndex: TABINDEX_SPEF + 36,
                                        fieldLabel: lang['data_vvoda_v_ekspluatatsiyu'],
                                        format: 'd.m.Y',
                                        allowBlank: false,
                                        name: 'AccountingData_setDate',
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
                                    }]
                                }]
                            },{
                                border: false,
                                layout: 'column',
                                //labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [{
                                        xtype: 'swdatefield',
                                        width: 100,
                                        tabIndex: TABINDEX_SPEF + 37,
                                        fieldLabel: 'Дата принятия на учёт',
                                        format: 'd.m.Y',
                                        name: 'AccountingData_begDate',
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                    }]
                                },{	border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [ {
                                        allowBlank: true,
                                        xtype: 'swdatefield',
                                        fieldLabel: 'Дата снятия с учёта',
                                        format: 'd.m.Y',
                                        tabIndex: TABINDEX_SPEF + 38,
                                        name: 'AccountingData_endDate',
                                        id: 'MPC_AccountingData_endDate',
                                        width: 100,
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                        handler: function(value) {
                                            var base_form = _this.MedProductCardEditForm.getForm();
                                            var ProductCauseType = base_form.findField('MedProductCauseType');

                                            if(value.getValue()) {
                                                ProductCauseType.allowBlank = false;
                                            } else {
                                                ProductCauseType.allowBlank = true;
                                            }
                                        }
                                    }]
                                }]
                            },{
                                border: false,
                                layout: 'column',
                                //labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [{
                                        allowBlank: true,
                                        fieldLabel: 'Причина снятия с учета медицинского оборудования',
                                        tabIndex: TABINDEX_SPEF + 10,
                                        //width: 400,
                                        comboSubject: 'MedProductCauseType',
                                        xtype: 'swcommonsprcombo',
                                        prefix: 'passport_',
                                        name: 'MedProductCauseType',
                                        listeners: {
                                            select: function(value) {
                                                var base_form = _this.MedProductCardEditForm.getForm();
                                                var AccountingData_endDate = base_form.findField('AccountingData_endDate');
                                                var ProductCauseType_Cause = _this.MedProductCardEditForm.findById('MPC_MedProductCard_Cause');

                                                if(value.getValue()) {
                                                    AccountingData_endDate.allowBlank = false;
                                                } else {
                                                    AccountingData_endDate.allowBlank = true;
                                                }
    
                                                if(value.getValue() == 3) {
                                                    ProductCauseType_Cause.showContainer();
                                                    ProductCauseType_Cause.allowBlank = false;
                                                } else {
                                                    ProductCauseType_Cause.hideContainer();
                                                    ProductCauseType_Cause.allowBlank = true;
                                                }
                                            } 
                                        },
                                    }],
                                },{	border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    id: 'buh_uchet_layout',
                                    items: [{
                                        allowBlank: true,
                                        fieldLabel: 'Причина',
                                        name: 'MedProductCard_Cause',
                                        id: 'MPC_MedProductCard_Cause',
                                        //width: 400,
                                        tabIndex: TABINDEX_SPEF + 39,
                                        xtype: 'textfield',
                                        listeners: {
                                            render: function (value) {
                                                value.hideContainer();
                                            }
                                        },
                                    }],
                                }]
                            },{
                                border: false,
                                layout: 'column',
                                //labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [{
                                        allowBlank: true,
                                        fieldLabel: lang['nomer_gos_kontrakta'],
                                        id: 'MPC_GosContract_Number',
                                        name: 'GosContract_Number',
                                        width: 100,
                                        tabIndex: TABINDEX_SPEF + 39,
                                        xtype: 'textfield'
                                    }]
                                },{	border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [ {
                                        allowBlank: true,
                                        xtype: 'swdatefield',
                                        fieldLabel: lang['data_zaklyucheniya_kontrakta'],
                                        format: 'd.m.Y',
                                        name: 'GosContract_setDate',
                                        width: 100,
                                        tabIndex: TABINDEX_SPEF + 40,
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
                                    }]
                                }]
                            },{
                                border: false,
                                layout: 'column',
                                //labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [{
                                        allowBlank: false,
                                        typeCode: 'int',
                                        comboSubject: 'FinancingType',
                                        fieldLabel: lang['programma_zakupki'],
                                        hiddenName: 'FinancingType_id',
                                        id: 'MPC_FinancingType_id',
                                        width: 200,
                                        tabIndex: TABINDEX_SPEF + 41,
                                        prefix: 'passport_',
                                        xtype: 'swcommonsprcombo'
                                    }]
                                },{	border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [ {
                                        allowBlank: false,
                                        fieldLabel: lang['stoimost_priobreteniya'],
                                        id: 'MPC_AccountingData_BuyCost',
                                        name: 'AccountingData_BuyCost',
                                        width: 100,
                                        tabIndex: TABINDEX_SPEF + 42,
                                        allowDecimals:true,
                                        xtype: 'numberfield',
                                        autoCreate: {tag: "input", size: 14, autocomplete: "off"}
                                    }]
                                }]
                            },{
                                border: false,
                                layout: 'column',
                                //labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [{
                                        allowBlank: false,
                                        fieldLabel: lang['tsena_proizvoditelya'],
                                        id: 'MPC_AccountingData_ProductCost',
                                        name: 'AccountingData_ProductCost',
                                        width: 100,
                                        tabIndex: TABINDEX_SPEF + 43,
                                        allowDecimals:true,
                                        xtype: 'numberfield',
                                        autoCreate: {tag: "input", size: 14, autocomplete: "off"}
                                    }]
                                },{	border: false,
                                    columnWidth: .40,
                                    layout: 'form',
                                    items: [{
                                        allowBlank: false,
                                        //anchor: '100%',
                                        comboSubject: 'DeliveryType',
                                        fieldLabel: lang['tip_postavki'],
                                        hiddenName: 'DeliveryType_id',
                                        id: 'MPC_DeliveryType_id',
                                        width: 200,
                                        tabIndex: TABINDEX_SPEF + 44,
                                        prefix: 'passport_',
                                        xtype: 'swcommonsprcombo'
                                    }]
                                }]
                            },{
                                allowBlank: false,
                                //anchor: '100%',
                                comboSubject: 'PropertyType',
                                fieldLabel: lang['forma_vladeniya'],
                                hiddenName: 'PropertyType_id',
                                id: 'MPC_PropertyType_id',
                                width: 200,
                                tabIndex: TABINDEX_SPEF + 45,
                                //prefix: '',
                                xtype: 'swcommonsprcombo'
                            },
                            new sw.Promed.Panel({
                                autoHeight: true,
                                style:'margin-bottom: 0.5em;',
                                border: true,
                                collapsible: false,
                                //collapsed: true,
                                id: 'MPC_Amortization',
                                layout: 'form',
                                title: lang['nachislenie_iznosa'],
                                listeners: {
                                    collapse: function ()  {
                                        _this.syncShadow();
                                    }.createDelegate(this)
                                },
                                items: [
                                    new sw.Promed.ViewFrame({
                                        actions: [
                                            {name: 'action_add', handler: function() { _this.openAmortizationEditWindow('add'); } },
                                            {name: 'action_edit', handler: function() { _this.openAmortizationEditWindow('edit'); } },
                                            {name: 'action_view', hidden: true },
                                            {name: 'action_delete', handler: function() { _this.amortizationDelete(); } },
                                            {name: 'action_refresh', disabled: true, hidden: true},
                                            {name: 'action_print', hidden: true }
                                        ],
                                        object: 'Amortization',
                                        editformclassname: 'swAmortizationEditWindow',
                                        autoExpandColumn: 'autoexpand',
                                        autoExpandMin: 150,
                                        autoLoadData: false,
                                        border: false,
                                        scheme: 'passport',
                                        dataUrl: '/?c=LpuPassport&m=loadAmortization',
                                        id: 'MPC_AmortizationGrid',
                                        paging: false,
                                        region: 'center',
                                        stringfields: [
                                            {name: 'Amortization_id', type: 'int', header: 'Amortization_id', key: true},
                                            {name: 'MedProductCard_id', type: 'int', header: 'MedProductCard_id', hidden: true},
                                            {name: 'Amortization_setDate', type: 'date', header: lang['data_otsenki'], width: 200},
                                            {name: 'Amortization_WearPercent', header: lang['protsent_iznosa'], width: 200},
                                            {name: 'Amortization_FactCost', header: lang['fakticheskaya_stoimost'], width: 200},
                                            {name: 'Amortization_ResidCost', header: lang['ostatochnaya_stoimost'], width: 200}
                                        ],
                                        onLoadData: function() {
                                            if (!Ext.isEmpty(_this.MedProductCard_id)) {
                                                this.setActionDisabled('action_refresh', false);
                                            }
                                        },
                                        toolbar: true
                                        //totalProperty: 'totalCount'
                                    })
                                ]
                            })
                        ]
                    },{
                        height: 400,
                        //autoheight: true,
                        labelWidth: 400,
                        layout: 'form',
                        style: 'padding: 2px',
                        autoScroll: true,
                        tabPosition:'top',
                        border:false,
                        //layout: 'fit',
                        id: 'WorkData',
                        title: '<u>7</u>. Простои МИ/Эксплуатационные данные  ', // ЭД - WorkData,  простои - DownTime
                        items: [
                            {
                            autoHeight: true,
                            title: lang['ekspluatatsionnyie_dannyie'],
                            bodyStyle:'padding: 0;',
                            xtype: 'fieldset',
                            collapsible: true,
                            columnWidth: 1,
                            labelWidth: 250,
                            items:[
                                {
                                    fieldLabel: lang['ustanovlennyiy_naznachennyiy_resurs_ed'],
                                    id: 'MPC_MedProductCard_SetResource',
                                    name: 'MedProductCard_SetResource',
                                    tabIndex: TABINDEX_SPEF + 53,
                                    width: 300,
                                    xtype: 'numberfield'
                                }, {
                                    fieldLabel: lang['srednyaya_dlitelnost_protseduryi_ed'],
                                    id: 'MPC_MedProductCard_AvgProcTime',
                                    name: 'MedProductCard_AvgProcTime',
                                    tabIndex: TABINDEX_SPEF + 56,
                                    width: 300,
                                    xtype: 'numberfield'
                                }, {
                                    layout: 'form',
                                    hidden: getRegionNick() != 'ufa',
                                    autoHeight: true,
                                    items: [
                                        {
                                            xtype: 'swyesnocombo',
                                            fieldLabel: langs('Работа в круглосуточном режиме'),
                                            name: 'MedProductCard_IsClockMode',
                                            hiddenName: 'MedProductCard_IsClockMode',
                                            allowBlank: false,
                                            valueField: 'YesNo_id',
                                            disabled: getRegionNick() != 'ufa'
                                        }, {
                                            xtype: 'swyesnocombo',
                                            fieldLabel: langs('Наличие специалистов для работы на указанном оборудовании'),
                                            name: 'MedProductCard_IsAvailibleSpecialists',
                                            hiddenName: 'MedProductCard_IsAvailibleSpecialists',
                                            allowBlank: false,
                                            valueField: 'YesNo_id',
                                            disabled: getRegionNick() != 'ufa'
                                        }
                                    ]
                                },
                                new sw.Promed.ViewFrame({
                                    actions: [
                                        {name: 'action_add', handler: function() { _this.openWorkDataEditWindow('add'); }.createDelegate(this) },
                                        {name: 'action_edit', handler: function() { _this.openWorkDataEditWindow('edit'); }.createDelegate(this) },
                                        {name: 'action_view', hidden: true },
                                        {name: 'action_delete', handler: function() { _this.workDataDelete(); }.createDelegate(this) },
                                        {name: 'action_refresh', disabled: true, hidden: true},
                                        {name: 'action_print', hidden: true }
                                    ],
                                    object: 'WorkData',
                                    editformclassname: 'swWorkDataEditWindow',
                                    autoExpandColumn: 'autoexpand',
                                    autoExpandMin: 150,
                                    autoLoadData: false,
                                    border: false,
                                    //title: 'Эксплуатационные данные',
                                    scheme: 'passport',
                                    dataUrl: '/?c=LpuPassport&m=loadWorkData',
                                    id: 'MPC_WorkDataGrid',
                                    paging: false,
                                    region: 'center',
                                    stringfields: [
                                        {name: 'WorkData_id', type: 'int', header: 'WorkData_id', key: true},
                                        {name: 'MedProductCard_id', type: 'int', header: 'MedProductCard_id', hidden: true, isparams: true },
                                        {name: 'WorkData_WorkPeriod', type: 'date', header: lang['period_ekspluatatsii_pervoe_chislo_mesyatsa'], isparams: true, width: 300},
                                        {name: 'WorkData_DayChange', type: 'string', header: lang['kolichestvo_smen_v_sutki'], isparams: true, width: 200},
                                        {name: 'WorkData_CountUse', type: 'string', header: lang['obschee_kolichestvo_primeneniy_za_period'], id: 'autoexpand', isparams: true, width: 200},
                                        {name: 'WorkData_AvgUse', type: 'string', header: lang['srednee_kolichestvo_primeneniy_v_smenu'], isparams: true, width: 200},
                                        {name: 'WorkData_KolDay', type: 'string', header: lang['kolichestvo_rabochih_dney_v_periode'], width: 200}
                                    ],
                                    toolbar: true,
                                    onLoadData: function() {
                                        if (!Ext.isEmpty(_this.MedProductCard_id)) {
                                            this.setActionDisabled('action_refresh', false);
                                        }
                                    }
                                    //totalProperty: 'totalCount'
                                })
                            ]},
                        new sw.Promed.Panel({
                            autoHeight: true,
                            style:'margin-bottom: 0.5em;',
                            border: true,
                            collapsible: false,
                            collapsed: false,
                            id: 'MPC_Downtime',
                            layout: 'form',
                            title: lang['prostoi_mi'],
                            listeners: {
                                collapse: function () {
                                    _this.syncShadow();
                                }.createDelegate(this)
                            },
                            items: [
                                new sw.Promed.ViewFrame({
                                    actions: [
                                        {name: 'action_add', handler: function() { _this.openDowntimeEditWindow('add'); }.createDelegate(this) },
                                        {name: 'action_edit', handler: function() { _this.openDowntimeEditWindow('edit'); }.createDelegate(this) },
                                        {name: 'action_view', hidden: true },
                                        {name: 'action_delete', handler: function() { _this.downtimeDelete(); }.createDelegate(this) },
                                        {name: 'action_refresh', disabled: true, hidden: true},
                                        {name: 'action_print', hidden: true }
                                    ],
                                    object: 'Downtime',
                                    editformclassname: 'swDowntimeEditWindow',
                                    autoExpandColumn: 'autoexpand',
                                    autoExpandMin: 150,
                                    autoLoadData: false,
                                    border: false,
                                    scheme: 'passport',
                                    dataUrl: '/?c=LpuPassport&m=loadDowntime',
                                    id: 'MPC_DowntimeGrid',
                                    paging: false,
                                    region: 'center',
                                    stringfields: [
                                        {name: 'Downtime_id', type: 'int', header: 'WorkData_id', key: true},
                                        {name: 'DowntimeCause_id', type: 'string', header: lang['identifikator_prichinyi_prostoya'],  hidden: true, isparams: true, width: 200},
                                        {name: 'MedProductCard_id', type: 'int', header: 'MedProductCard_id', hidden: true, isparams: true },
                                        {name: 'Downtime_begDate', type: 'date', header: lang['data_nachala_prostoya'], isparams: true, width: 200},
                                        {name: 'DowntimeCause_Name', type: 'string', header: lang['prichina_prostoya'], isparams: true, id: 'autoexpand' },
                                        {name: 'Downtime_endDate', type: 'date', header: lang['data_vozobnovleniya_rabotyi'], isparams: true, width: 200}
                                    ],
                                    toolbar: true,
                                    onLoadData: function() {
                                        if (!Ext.isEmpty(_this.MedProductCard_id)) {
                                            this.setActionDisabled('action_refresh', false);
                                        }
                                    }
                                    //totalProperty: 'totalCount'
                                })
                            ]}
                        )]
                    },{
                        height: 400,
                        labelWidth: 400,
                        layout: 'form',
                        style: 'padding: 2px',
                        autoScroll: true,
                        tabPosition:'top',
                        border:false,
                        id: 'TechState',
                        title: lang['8_tehnicheskoe_sostoyanie'], //AccountingData +lang['i']+ GosContract
                        items: [{
                            autoHeight: true,
                            title: lang['osnovnaya_informatsiya_o_teh_sostoyanii'],
                            bodyStyle:'padding: 10;',
                            xtype: 'fieldset',
                            collapsible: true,
                            labelWidth: 280,
                            items:[
                                {
                                border: false,
                                layout: 'column',
                                //labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .35,
                                    layout: 'form',
                                    items: [{
                                        xtype:'checkbox',
                                        tabIndex: TABINDEX_SPEF + 60,
                                        checked: false,
                                        handler: function(value) {
                                            var repairDate = _this.MedProductCardEditForm.getForm().findField('MedProductCard_RepairDate');
                                            if (value.checked) {
                                                repairDate.enable();
                                                repairDate.allowBlank = false;
                                            } else {
                                                repairDate.allowBlank = true;
                                                repairDate.disable();
                                                repairDate.setValue('');
                                            }
                                        },
                                        fieldLabel:lang['trebuet_remonta'],
                                        name: 'MedProductCard_IsRepair',
                                        id: 'MPC_MedProductCard_IsRepair'
                                    }]
                                },{
                                    border: false,
                                    columnWidth: .55,
                                    labelWidth: 350,
                                    layout: 'form',
                                    items: [{
                                        xtype:'checkbox',
                                        checked: false,
                                        handler: function(value) {
                                            var spisanDate = _this.MedProductCardEditForm.getForm().findField('MedProductCard_SpisanDate');
                                            if (value.checked) {
                                                spisanDate.enable();
                                                spisanDate.allowBlank = false;
                                            } else {
                                                spisanDate.allowBlank = true;
                                                spisanDate.disable();
                                                spisanDate.setValue('');
                                            }
                                        },
                                        tabIndex: TABINDEX_SPEF + 65,
                                        fieldLabel:lang['trebuet_spisaniya'],
                                        name: 'MedProductCard_IsSpisan',
                                        id: 'MPC_MedProductCard_IsSpisan'
                                    }]
                                }]
                            },{
                                border: false,
                                layout: 'column',
                                //labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .35,
                                    layout: 'form',
                                    items: [{
                                        xtype: 'swdatefield',
                                        fieldLabel:lang['data_ustanovki_statusa'],
                                        format: 'd.m.Y',
                                        name: 'MedProductCard_RepairDate',
                                        width: 100,
                                        tabIndex: TABINDEX_SPEF + 70,
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
                                    }]
                                },{
                                    border: false,
                                    columnWidth: .55,
                                    labelWidth: 350,
                                    layout: 'form',
                                    items: [{
                                        xtype: 'swdatefield',
                                        fieldLabel:lang['data_ustanovki_statusa'],
                                        format: 'd.m.Y',
                                        name: 'MedProductCard_SpisanDate',
                                        width: 100,
                                        tabIndex: TABINDEX_SPEF + 75,
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
                                    }]
                                }]
                            }]
                        },{
                            autoHeight: true,
                            title: lang['tehnicheskoe_obslujivanie'],
                            bodyStyle:'padding: 10;',
                            xtype: 'fieldset',
                            collapsible: true,
                            columnWidth: 1,
                            labelWidth: 280,
                            items:[
                                {
                                border: false,
                                layout: 'column',
                                //labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .35,
                                    layout: 'form',
                                    items: [{
                                        xtype:'checkbox',
                                        tabIndex: TABINDEX_SPEF + 80,
                                        checked: false,
                                        handler: function(value) {
                                            var repairDate = _this.MedProductCardEditForm.getForm().findField('Org_toid');
                                            if (value.checked) {
                                                repairDate.enable();
                                                repairDate.allowBlank = false;
                                            } else {
                                                repairDate.allowBlank = true;
                                                repairDate.disable();
                                                repairDate.setValue('');
                                            }
                                        },
                                        fieldLabel:lang['nalichie_dogovora_na_teh_obslujivanie'],
                                        name: 'MedProductCard_IsContractTO',
                                        id: 'MPC_MedProductCard_IsContractTO'
                                    }]
                                },{
                                    border: false,
                                    columnWidth: .55,
                                    labelWidth: 350,
                                    layout: 'form',
                                    items: [{
                                        xtype: 'sworgcomboex',
                                        hiddenName: 'Org_toid',
                                        tabIndex: TABINDEX_SPEF + 85,
                                        fieldLabel: lang['organizatsiya_osuschestvlyayuschaya_teh_obslujivanie'],
                                        id: 'MPC_Org_toid',
                                        width: 200,
                                        disabled: true
                                    }]
                                }]
                            },{
                                border: false,
                                layout: 'column',
                                //labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .35,
                                    layout: 'form',
                                    items: [{
                                        xtype:'checkbox',
                                        tabIndex: TABINDEX_SPEF + 90,
                                        checked: false,
                                        fieldLabel:lang['nalichie_litsenzii_na_provedenie_teh_obslujivaniya'],
                                        name: 'MedProductCard_IsOrgLic',
                                        id: 'MPC_MedProductCard_IsOrgLic'
                                    }]
                                },{
                                    border: false,
                                    columnWidth: .55,
                                    labelWidth: 350,
                                    layout: 'form',
                                    items: [{
                                        xtype:'checkbox',
                                        tabIndex: TABINDEX_SPEF + 95,
                                        checked: false,
                                        fieldLabel:lang['nalichie_litsenzii_u_mo_na_provedenie_teh_obslujivaniya'],
                                        name: 'MedProductCard_IsLpuLic',
                                        id: 'MPC_MedProductCard_IsLpuLic'
                                    }]
                                }]
                            },{
                                autoCreate: {
                                    tag: "input",
                                    type: "text",
                                    maxLength: "64",
                                    autocomplete: "off"
                                },
                                fieldLabel: lang['dokument_podtverjdayuschiy_prohojdenie_to'],
                                id: 'MPC_MPC_MedProductCard_DocumentTO',
                                name: 'MedProductCard_DocumentTO',
                                tabIndex: TABINDEX_SPEF + 100,
                                width: 800,
                                xtype: 'textfield'
                            }]
                        }]
                    }
                ],
				listeners: {
					'tabchange': function(panel, tab) {
						var els = tab.findByType('textfield', false);

                        _this.syncShadow();

						if (els == undefined || els == null)
							els = tab.findByType('combo', false);

						var el = els[0];

						if (el != undefined && el.focus)
							el.focus(true, 200);
					}
				}
			})]
        });

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					_this.submit();
				},
				iconCls: 'save16',
				id: 'MPC_SaveButton',
				tabIndex: 60,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, 61),
			{
				iconCls: 'cancel16',
				id: 'MPC_CancelButton',
				handler: function() {

					_this.hide();
				},
				tabIndex: 62,
				text: BTN_FRMCANCEL
			}],
			items: [
                _this.formPanel,
                _this.MedProductCardEditForm
            ],
            layout: 'fit',
			keys: [{
				alt: true,
				fn: function(inp, e) {
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

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.submit();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swMedProductCardEditWindow.superclass.initComponent.apply(this, arguments);

		if(getRegionNick() != 'kz') this.addTabClassificationForTheForm_30();

		this.getFieldsLists(this.MedProductCardEditForm, {
			needConstructComboLists: true,
			needConstructEditFields: true
		});
	},
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swMedProductCardEditWindow.superclass.show.apply(this, arguments);

        if(getRegionNick() != 'kz') this.findById('MedProductCardEdit').setActiveTab(8);
		this.findById('MedProductCardEdit').setActiveTab(7);
		this.findById('MedProductCardEdit').setActiveTab(6);
		this.findById('MedProductCardEdit').setActiveTab(5);
		this.findById('MedProductCardEdit').setActiveTab(4);
		this.findById('MedProductCardEdit').setActiveTab(3);
		this.findById('MedProductCardEdit').setActiveTab(2);
		this.findById('MedProductCardEdit').setActiveTab(1);
		this.findById('MedProductCardEdit').setActiveTab(0);

		var _this = this,
		    form = _this.MedProductCardEditForm,
            uper_form = _this.findById('MPCEW_panelForm').getForm(),
		    base_form = form.getForm();

		base_form.reset();
        uper_form.reset();

		_this.action = null;
		_this.callback = Ext.emptyFn;
		_this.onHide = Ext.emptyFn;

		if ( arguments[0] ) {
			if ( arguments[0].action )
				_this.action = arguments[0].action;

			if ( arguments[0].callback )
				_this.callback = arguments[0].callback;

			if ( arguments[0].onHide )
				_this.onHide = arguments[0].onHide;

			if ( arguments[0].MedProductCard_id ) {
				_this.MedProductCard_id = arguments[0].MedProductCard_id;
			} else {
				_this.MedProductCard_id = null;
			}

			if ( arguments[0].Lpu_id ) {
				_this.Lpu_id = arguments[0].Lpu_id;
			} else {
				_this.Lpu_id = getGlobalOptions().lpu_id;
			}
		}

        base_form.findField('SubSection_Name').Lpu_id = _this.Lpu_id;

        if (_this.action != 'add' && Ext.isEmpty(arguments[0].MedProductCard_id)) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_peredan_identifikator_kartyi_meditsinskogo_izdeliya'], function() { _this.hide(); } );
			return false;
        }

		form.findById('MedProductCardEdit').setActiveTab('baseInfo');
		base_form.findField('OkeiLink_id').getStore().load();
		base_form.findField('Lpu_id').setValue(_this.Lpu_id);
        uper_form.findField('MedProductClass_id').action = _this.action;
        uper_form.findField('MedProductClass_id').Lpu_id = _this.Lpu_id;
		base_form.findField('SubSection_Name').parentNodeArray = [];
        
        if (!isSuperAdmin() || getRegionNick() != 'perm') {
            base_form.findField('MedProductCard_IsNoAvailLpu').setContainerVisible(false);
        }

		if ( _this.action ) {
			switch ( _this.action ) {
				case 'add':
                    base_form.reset();
                    this.loadComboMedProductClassForm();
                    form.findById('MPC_ConsumablesGrid').removeAll({clearAll: true});
                    form.findById('MPC_AmortizationGrid').removeAll({clearAll: true});
                    form.findById('MPC_MeasureFundCheckGrid').removeAll({clearAll: true});
                    form.findById('MPC_WorkDataGrid').removeAll({clearAll: true});
                    form.findById('MPC_DowntimeGrid').removeAll({clearAll: true});
					_this.setTitle(lang['kartochka_meditsinskogo_izdeliya_dobavlenie']);
					_this.enableEdit(true);
                    base_form.findField('MedProductCard_SpisanDate').disable();
                    base_form.findField('MedProductCard_RepairDate').disable();

					//Фокусируем на поле Наименование
					uper_form.findField('MedProductCard_Name').focus(100, true);
					
					base_form.findField('MedProductCard_IsOutsorc').hideContainer();							   
					base_form.findField('MedProductCard_BoardNumber').hideContainer();						   
					base_form.findField('MedProductCard_Phone').hideContainer();
					base_form.findField('MedProductCard_Glonass').hideContainer();
				break;

				case 'edit':
				case 'view':

                    base_form.setValues({
                        MedProductCard_id: _this.MedProductCard_id
                    });

					if (_this.action == 'edit') {
						_this.setTitle(lang['kartochka_meditsinskogo_izdeliya_redaktirovanie']);
						//Фокусируем на поле Код
						_this.enableEdit(true);
					    uper_form.findField('MedProductCard_Name').focus(100, true);
					} else {
						_this.setTitle(lang['kartochka_meditsinskogo_izdeliya_prosmotr']);
						_this.enableEdit(false);
						// todo: Временный костыль, снести как только, так сразу
						if (isUserGroup('OuzSpec') || isUserGroup('OuzSpecMPC')) {
							_this.enableEdit(true);
							this.buttons[0].disable();	
							form.findById('MPC_ConsumablesGrid').setReadOnly(true);
							form.findById('MPC_AmortizationGrid').setReadOnly(true);
							form.findById('MPC_WorkDataGrid').setReadOnly(true);
							form.findById('MPC_DowntimeGrid').setReadOnly(true);
						}
					}

                    form.findById('MPC_ConsumablesGrid').loadData({
                        globalFilters:{MedProductCard_id: _this.MedProductCard_id},
                        params:{MedProductCard_id: _this.MedProductCard_id}
                    });

                    form.findById('MPC_AmortizationGrid').loadData({
                        globalFilters:{MedProductCard_id: _this.MedProductCard_id},
                        params:{MedProductCard_id: _this.MedProductCard_id}
                    });
					//
                    form.findById('MPC_DowntimeGrid').loadData({
                        globalFilters:{MedProductCard_id: _this.MedProductCard_id},
                        params:{MedProductCard_id: _this.MedProductCard_id}
                    });

                    form.findById('MPC_MeasureFundCheckGrid').loadData({
                        globalFilters:{MedProductCard_id: _this.MedProductCard_id},
                        params:{MedProductCard_id: _this.MedProductCard_id}
                    });

                    form.findById('MPC_WorkDataGrid').loadData({
                        globalFilters:{MedProductCard_id: _this.MedProductCard_id},
                        params:{MedProductCard_id: _this.MedProductCard_id}
                    });

					var Mask = new Ext.LoadMask(Ext.get('MedProductCardEditWindow'), { msg: "Пожалуйста, подождите, Идет загрузка данных формы..."} );
					Mask.show();

					base_form.load({
						failure: function() {

                            Mask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { _this.hide(); } );
						},
						params: {
							MedProductCard_id: _this.MedProductCard_id
						},
						success: function(result_form, action) {

                            var responseText = Ext.util.JSON.decode(action.response.responseText);

							if (!Ext.isEmpty(responseText[0].MedProductClass_id)) {
								uper_form.findField('MedProductClass_id').getStore().load({
									params: {
										MedProductClass_id: responseText[0].MedProductClass_id,
										Lpu_id: _this.Lpu_id
									},
									callback: function () {
										uper_form.findField('MedProductClass_id').setValue(responseText[0].MedProductClass_id);
										uper_form.findField('MedProductClass_id').fireEvent('select', uper_form.findField('MedProductClass_id'), uper_form.findField('MedProductClass_id').getValue());
									}
								});
							}
							_this.setComboMedProductClassForm();

                            //сделано для TNC и Wialon по задаче https://redmine.swan.perm.ru/issues/106263
							//if( getGlobalOptions().region.number == 2 && !Ext.isEmpty(responseText[0].LpuBuilding_id) ){
							//	// необходимо загрузить список автомобилей с настройками логин/пароль TNC для данной станции
							//	// возможно и для Wialon понадобится, т.к. сейчас он так не работает
							//	var LpuBuilding_id = responseText[0].LpuBuilding_id;
							//	var fieldGlonass=base_form.findField('MedProductCard_Glonass');
							//	var glonass_id=responseText[0].MedProductCard_Glonass;
							//	if(fieldGlonass.hidden == false){
							//		fieldGlonass.getStore().load({
							//			params: {LpuBuilding_id: LpuBuilding_id},
							//			callback: function(){
							//				if(glonass_id) fieldGlonass.setValue(glonass_id)
							//			}
							//		});
							//	}
							//}

                            if (!Ext.isEmpty(responseText[0].Org_id)) {
                                base_form.findField('Org_id').getStore().loadData([{
                                    Org_id: responseText[0].Org_id,
                                    Org_Name: responseText[0].Org_Name,
                                    Org_ColoredName: ''
                                }]);

                                base_form.findField('Org_id').setValue(responseText[0].Org_id);
                            }

                            if (!Ext.isEmpty(responseText[0].Org_regid)) {
                                base_form.findField('Org_regid').getStore().loadData([{
                                    Org_id: responseText[0].Org_regid,
                                    Org_Name: responseText[0].Org_regid_Name,
                                    Org_ColoredName: ''
                                }]);

                                base_form.findField('Org_regid').setValue(responseText[0].Org_regid);
                            }

                            if (!Ext.isEmpty(responseText[0].Org_prid)) {
                                base_form.findField('Org_prid').getStore().loadData([{
                                    Org_id: responseText[0].Org_prid,
                                    Org_Name: responseText[0].Org_prid_Name,
                                    Org_ColoredName: ''
                                }]);

                                base_form.findField('Org_prid').setValue(responseText[0].Org_prid);
                            }

                            if (!Ext.isEmpty(responseText[0].Org_decid)) {
                                base_form.findField('Org_decid').getStore().loadData([{
                                    Org_id: responseText[0].Org_decid,
                                    Org_Name: responseText[0].Org_dec_Name,
                                    Org_ColoredName: ''
                                }]);

                                base_form.findField('Org_decid').setValue(responseText[0].Org_decid);
                            }

                            if (!Ext.isEmpty(responseText[0].Org_toid)) {
                                base_form.findField('Org_toid').getStore().loadData([{
                                    Org_id: responseText[0].Org_toid,
                                    Org_Name: responseText[0].Org_toid_Name,
                                    Org_ColoredName: ''
                                }]);

                                base_form.findField('Org_toid').setValue(responseText[0].Org_toid);
                            }

                            if (!Ext.isEmpty(responseText[0].LpuSection_id)) {
                                base_form.findField('SubSection_Name').Sub_SysNick = 'LpuSection';
                                base_form.findField('MPCE_SubSection_id').setValue(responseText[0].LpuSection_id);
                                base_form.findField('SubSection_Name').setNameWithPath();
                            } else if (!Ext.isEmpty(responseText[0].LpuUnit_id)) {
                                base_form.findField('SubSection_Name').Sub_SysNick = 'LpuUnit';
                                base_form.findField('MPCE_SubSection_id').setValue(responseText[0].LpuUnit_id);
                                base_form.findField('SubSection_Name').setNameWithPath();
                            } else if (!Ext.isEmpty(responseText[0].LpuBuilding_id)) {
                                base_form.findField('SubSection_Name').Sub_SysNick = 'LpuBuilding';
                                base_form.findField('MPCE_SubSection_id').setValue(responseText[0].LpuBuilding_id);
                                base_form.findField('SubSection_Name').setNameWithPath();
                            }

                            if (!Ext.isEmpty(responseText[0].MedProductType_Code) && responseText[0].MedProductType_Code.inlist([7, 8, 9, 10])) {
                                base_form.findField('MedProductCard_IsOutsorc').showContainer();
                                base_form.findField('MedProductCard_BoardNumber').showContainer();
                                base_form.findField('MedProductCard_Phone').showContainer();
                                base_form.findField('MedProductCard_Glonass').showContainer();
                            }
                            else {
                                base_form.findField('MedProductCard_IsOutsorc').hideContainer();
                                base_form.findField('MedProductCard_BoardNumber').hideContainer();
                                base_form.findField('MedProductCard_Phone').hideContainer();
                                base_form.findField('MedProductCard_Glonass').hideContainer();
                            }

                            var glonassCombo = base_form.findField('MedProductCard_Glonass'),
                                glonassComboIsHidden = glonassCombo.hidden;

                            // подгружаем комбо глонасс с параметрами, если поле не скрыто
                            if (!glonassComboIsHidden) {

                                var LpuBuilding_id = null;

                                if (!Ext.isEmpty(responseText[0].LpuBuilding_id))
                                    LpuBuilding_id = responseText[0].LpuBuilding_id;

                                var glonassComboData = {
                                    Lpu_id: _this.Lpu_id,
                                    LpuBuilding_id: LpuBuilding_id,
                                    Sub_SysNick: base_form.findField('SubSection_Name').Sub_SysNick,
                                    LpuDepartment_id: base_form.findField('MPCE_SubSection_id').getValue(),
                                    // чтобы контроллер виалона знал, что авторизация
                                    // будет проходить через учетку выбранного подразделения
                                    GlonassAuthByDepartment: true
                                };

                                glonassCombo.getStore().load({

                                        params: glonassComboData,
                                        callback: function (storeArray) {

                                            if (!storeArray || storeArray && storeArray.length  == 0) {

                                                log('no_wialon_data!');
                                                glonassCombo.reset();

                                            } else {

                                                if (!Ext.isEmpty(responseText[0].MedProductCard_Glonass))
                                                    glonassCombo.setValue(responseText[0].MedProductCard_Glonass);

                                            }
                                        }
                                    }
                                );



                            }

                            base_form.findField('MedProductCard_IsSpisan').getValue() ? base_form.findField('MedProductCard_SpisanDate').enable() : base_form.findField('MedProductCard_SpisanDate').disable();
                            base_form.findField('MedProductCard_IsRepair').getValue() ? base_form.findField('MedProductCard_RepairDate').enable() : base_form.findField('MedProductCard_RepairDate').disable();

							Mask.hide();
							uper_form.findField('MedProductCard_Name').focus(100, true);
							if(Ext.getCmp('MPC_PrincipleWorkType_id') && typeof Ext.getCmp('MPC_PrincipleWorkType_id').getValue() != "undefined" && Ext.getCmp('MPC_PrincipleWorkType_id').getValue() == 2) {
								Ext.getCmp('MPC_MedProductCard_IsWorkList').setContainerVisible(true);
								Ext.getCmp('MPC_MedProductCard_AETitle').setContainerVisible(true);
								Ext.getCmp('MPC_LpuEquipmentPacs_id').setContainerVisible(true);
							} else {
								Ext.getCmp('MPC_MedProductCard_IsWorkList').setContainerVisible(false);
								Ext.getCmp('MPC_MedProductCard_AETitle').setContainerVisible(false);
								Ext.getCmp('MPC_LpuEquipmentPacs_id').setContainerVisible(false);
							}
						},
						url: '/?c=LpuPassport&m=loadMedProductCardData'
					});
					break;
			}
		}

	},
	submit: function() {
		var _this = this,
		    form = this.MedProductCardEditForm.getForm(),
		    top_form = this.formPanel.findById('MPCEW_panelForm').getForm(),
		    params = {},
            ConsumablesGrid = this.findById('MPC_ConsumablesGrid').getGrid(),
            AmortizationGrid = this.findById('MPC_AmortizationGrid').getGrid(),
            MeasureFundCheckGrid = this.findById('MPC_MeasureFundCheckGrid').getGrid(),//Проверки
            WorkDataGrid = this.findById('MPC_WorkDataGrid').getGrid(),//Эксплуатационные данные
            DowntimeGrid = this.findById('MPC_DowntimeGrid').getGrid(),//Простои МИ
            parentNodeArrayFull = form.findField('SubSection_Name').parentNodeArray,
            parentNodeArray = [],
            loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение услуги..." });

        // Перебираем поля формы

        var formFields = form.formPanel.form.items.items; // общее кол-во полей

        if(!top_form.isValid()) {
            sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi_MI']);
			return false;
        }

        if ( !form.isValid() ) {

            formFields.forEach(function(item, i, arr) {

                if(item.allowBlank === false && item.xtype != 'hidden' && item.disabled != true ) {

                    if( (typeof (item.value) == "string") && (item.value.replace(/\s/g,"") == "") ) {
                     
                        item.tabTitle = item.ownerCt.ownerCt.ownerCt.title;

                        sw.swMsg.alert(`Ошибка на вкладке: ${item.tabTitle}`, `в поле: ${item.fieldLabel}` );

                        return false;
                    
                    }
                }
            })
     
			return false;
		}

		// if ( !form.isValid()) {
		// 	sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
		// 	return false;
		// }

        if (
			!(form.findField('MedProductCard_IsOutsorc').getValue()) && 
			Ext.isEmpty(form.findField('AccountingData_setDate').getValue()) && 
			Ext.isEmpty(form.findField('AccountingData_begDate').getValue()) && 
			Ext.isEmpty(form.findField('AccountingData_endDate').getValue())) {
            sw.swMsg.alert('Ошибка заполнения формы', 'Одно из следующих полей на вкладке "Бухгалтерский учёт" должно быть заполнено: <br/> - Дата ввода в эксплуатацию <br/> - Дата принятия на учёт <br/> - Дата снятия с учёта');
			return false;
        }

        parentNodeArrayFull.forEach(function(element){
           if  (typeof(element) != 'function') {
               parentNodeArray.push(element)
           }
        });

        if (!Ext.isEmpty(parentNodeArray) && parentNodeArray instanceof Array) {
            parentNodeArray.reverse();
        }

        //Проставляем данные подотделений
        if (!Ext.isEmpty(parentNodeArray[0])){
            form.findField('LpuBuilding_id').setValue(parentNodeArray[0]);
        }

        if (!Ext.isEmpty(parentNodeArray[1])){
            form.findField('LpuUnit_id').setValue(parentNodeArray[1]);
        }else{
            form.findField('LpuUnit_id').setValue();
        }

        if (!Ext.isEmpty(parentNodeArray[2])){
            form.findField('LpuSection_id').setValue(parentNodeArray[2]);
        }else{
            form.findField('LpuSection_id').setValue();
        }


        // Собираем данные из грида расходные материалы
        ConsumablesGrid.getStore().clearFilter();

        if ( ConsumablesGrid.getStore().getCount() > 0 ) {
            var ConsumablesGridData = getStoreRecords(ConsumablesGrid.getStore(), {
                convertDateFields: true,
                exceptionFields: [
                    'MedProductCard_id'
                ]
            });

            params.ConsumablesGridData = Ext.util.JSON.encode(ConsumablesGridData);
        }

        // Собираем данные из грида износа
        AmortizationGrid.getStore().clearFilter();

        if ( AmortizationGrid.getStore().getCount() > 0  ) {
            var AmortizationGridData = getStoreRecords(AmortizationGrid.getStore(), {
                convertDateFields: true,
                exceptionFields: [
                    'MedProductCard_id'
                ]
            });

            params.AmortizationGridData = Ext.util.JSON.encode(AmortizationGridData);
        }

        // Собираем данные из грида порверок
        MeasureFundCheckGrid.getStore().clearFilter();

        if ( MeasureFundCheckGrid.getStore().getCount() > 0  ) {
            var MeasureFundCheckGridData = getStoreRecords(MeasureFundCheckGrid.getStore(), {
                convertDateFields: true,
                exceptionFields: [
                    'MedProductCard_id'
                ]
            });

            params.MeasureFundCheckGridData = Ext.util.JSON.encode(MeasureFundCheckGridData);
        }

        // Собираем данные из грида эксплуатационных данных
        WorkDataGrid.getStore().clearFilter();

        if ( WorkDataGrid.getStore().getCount() > 0  ) {
            var WorkDataGridData = getStoreRecords(WorkDataGrid.getStore(), {
                convertDateFields: true,
                exceptionFields: [
                    'MedProductCard_id'
                ]
            });

            params.WorkDataGridData = Ext.util.JSON.encode(WorkDataGridData);
        }

        // Собираем данные из грида простоев МИ
        DowntimeGrid.getStore().clearFilter();

        if ( DowntimeGrid.getStore().getCount() > 0  ) {
            var DowntimeGridData = getStoreRecords(DowntimeGrid.getStore(), {
                convertDateFields: true,
                exceptionFields: [
                    'MedProductCard_id'
                ]
            });

            params.DowntimeGridData = Ext.util.JSON.encode(DowntimeGridData);
        }

        //Сохнаряем идентификатор класса МИ
        params.MedProductClass_id = _this.formPanel.findById('MPC_MedProductClass_id').getValue();

        loadMask.show();

        form.submit({
            failure: function(result_form, action) {
                this.formStatus = 'edit';

                if (!Ext.isEmpty(form.findField('SubSection_Name').parentNodeArray) && (form.findField('SubSection_Name').parentNodeArray instanceof Array)) {
                    form.findField('SubSection_Name').parentNodeArray.reverse();
                }

                loadMask.hide();

                if ( action.result ) {
                    if ( action.result.Error_Msg ) {
                        sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                    }
                    else {
                        sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
                    }
                }
            }.createDelegate(this),
            params: params,
            standardSubmit: true,
            success: function(result_form, action) {
                _this.formStatus = 'edit';
                loadMask.hide();
                showSysMsg(lang['sohranenie_meditsinskogo_izdeliya_proshlo_uspeshno'], lang['soobschenie']);
				_this.callback();
                _this.hide();
            }
	    });
	},
    addTabClassificationForTheForm_30: function(){
        if(getRegionNick() == 'kz') return false;
        var medProductCardEdit = this.findById('MedProductCardEdit');
        medProductCardEdit.add({
            height: 400,
            labelWidth: 270,
            layout: 'form',
            style: 'padding: 2px',
            id: 'сlassification_for_the_form_30',
            autoScroll: true,
            border:false,
            //layout: 'fit',
            tabPosition:'top',
            title: langs('<u>9</u>. Классификация для формы №30'),
            items: [
                {
                    fieldLabel: langs('Раздел'),
                    hiddenName: 'MedProductClassForm_secid',
                    name: 'MedProductClassForm_secid',
                    id: 'CFTF_MedProductClassForm_secid',
                    xtype: 'swMedProductClassFormcombo',
                    listeners:{
                        beforeselect: function(combo, data){
                            if(combo.getValue() != data.get('MedProductClassForm_id')){
                                this.loadComboMedProductClassForm('MedProductClassForm_strid', data);
                            }
                            var ss=1;
                        }.createDelegate(this)
                    }
                },
                {
                    fieldLabel: langs('Строка'),
                    hiddenName: 'MedProductClassForm_strid',
                    name: 'MedProductClassForm_strid',
                    id: 'CFTF_MedProductClassForm_strid',
                    xtype: 'swMedProductClassFormcombo',
                    listeners:{
                        beforeselect: function(combo, data){
                            if(combo.getValue() != data.get('MedProductClassForm_id')){
                                this.loadComboMedProductClassForm('MedProductClassForm_fsubid', data);
                            }
                            var ss=1;
                        }.createDelegate(this),
                    }
                },
                {
                    fieldLabel: langs('Подстрока 1'),
                    hiddenName: 'MedProductClassForm_fsubid',
                    name: 'MedProductClassForm_fsubid',
                    id: 'CFTF_MedProductClassForm_fsubid',
                    xtype: 'swMedProductClassFormcombo',
                    listeners:{
                        beforeselect: function(combo, data){
                            if(combo.getValue() != data.get('MedProductClassForm_id')){
                                this.loadComboMedProductClassForm('MedProductClassForm_ssubid', data);
                            }
                        }.createDelegate(this),
                    }
                },
                {
                    fieldLabel: langs('Подстрока 2'),
                    hiddenName: 'MedProductClassForm_ssubid',
                    name: 'MedProductClassForm_ssubid',
                    id: 'CFTF_MedProductClassForm_ssubid',
                    xtype: 'swMedProductClassFormcombo'
                }                            
            ]
        });
        
        var form = this.MedProductCardEditForm;
        var base_form = form.getForm();
        form.doLayout();
        return true;
    },
    setComboMedProductClassForm: function(){
        
        if(getRegionNick() == 'kz' || !Ext.getCmp('сlassification_for_the_form_30')) return false;
        var form = this.MedProductCardEditForm;
        var base_form = form.getForm();
        var idsArr = ['MedProductClassForm_secid', 'MedProductClassForm_strid', 'MedProductClassForm_fsubid', 'MedProductClassForm_ssubid'];
        idsArr.forEach(function(item, i, arr){
            var combo = base_form.findField(item);
            if(combo){
                if(item != 'MedProductClassForm_secid') {
                    combo.setContainerVisible(false);
                    combo.getStore().sortInfo = {field: 'MedProductClassForm_Code'}
                }
                combo.getStore().removeAll();
                var medProductClassForm_pid = null;
                if(i > 0)  medProductClassForm_pid = base_form.findField(arr[i-1]).getValue();
                if(medProductClassForm_pid || item == 'MedProductClassForm_secid'){
                    combo.getStore().load(
                    {
                        callback: function() 
                        {
                            var value = this.getValue();
                            if(this.findRecord('MedProductClassForm_id', value)){
                                this.setValue(this.getValue());
                            }else{
                                this.clearValue();
                            }
                            if(this.hiddenName != 'MedProductClassForm_secid') this.setContainerVisible(this.getStore().getCount()>0);
                        }.createDelegate(combo),
                        params: { MedProductClassForm_pid: medProductClassForm_pid }
                    });
                }
            }
        });
    },
    loadComboMedProductClassForm: function(id, data, callback){
        if(getRegionNick() == 'kz' || !Ext.getCmp('сlassification_for_the_form_30')) return false;
        var idCombo = id || 'MedProductClassForm_secid';
        var cb = (callback && typeof callback == 'function') ? callback : null;
        //if(data && data.get('MedProductClassForm_id'))
        var medProductClassForm_pid = (data && data.get('MedProductClassForm_id')) ? data.get('MedProductClassForm_id') : null;
        var form = this.MedProductCardEditForm;
        var base_form = form.getForm();
        var idsArr = ['MedProductClassForm_strid', 'MedProductClassForm_fsubid', 'MedProductClassForm_ssubid'];
        var clear = (idCombo == 'MedProductClassForm_secid') ? true : false;
        idsArr.forEach(function(item, i, arr){
            if(!clear && idCombo == item) clear = true;
            if(clear){
                var combo = base_form.findField(item);
                combo.clearValue();
                combo.getStore().removeAll();
                combo.setContainerVisible(false);
            }
        });

        var combo = base_form.findField(idCombo);
        combo.getStore().removeAll();
        if((medProductClassForm_pid && idCombo != 'MedProductClassForm_secid') || idCombo == 'MedProductClassForm_secid'){
            if(idCombo != 'MedProductClassForm_secid') combo.getStore().sortInfo = {field: 'MedProductClassForm_Code'}
            combo.getStore().load(
            {
                callback: function() 
                {
                    var value = this.getValue();
                    var sb = null;
                    this.setValue(value);
                    if(this.hiddenName != 'MedProductClassForm_secid'){
                        this.setContainerVisible(this.getStore().getCount()>0);
                    }
                    if(value) sb = this.findRecord('MedProductClassForm_id', value);
                    if(value && sb){
                        this.fireEvent('beforeselect', this, sb);
                    }else{
                        combo.clearValue();
                    }
                   
                    if(cb) cb();
                }.createDelegate(combo),
                params: { MedProductClassForm_pid: medProductClassForm_pid }
            });
        }
    }
});