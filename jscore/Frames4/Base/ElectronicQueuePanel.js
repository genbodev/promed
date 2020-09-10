/**
 * sw.Promed.ElectronicQueuePanel. Класс панели работы с ЭО
 *
 * @project  PromedWeb
 * @copyright  (c) Swan Ltd, 2017
 * @package frames
 * @author  Maksim Sysolin
 * @class sw4.Promed.ElectronicQueuePanel
 * @extends Ext.form.FormPanel
 * @version 09.12.2017
 */

sw4.Promed.ElectronicQueuePanel = function(config) {
    Ext6.apply(this, config);
    sw4.Promed.ElectronicQueuePanel.superclass.constructor.call(this);
};

Ext6.extend(sw4.Promed.ElectronicQueuePanel, Ext6.Panel, {

    getOwnerWindow: function () { return this.ownerWindow },

    // здесь обновляем грид,
    // точнее подтаскиваем функцию отвечающую за рефреш того или иного грида
    gridRefresh: function (params) {

        var panel = this;

        if ( typeof params != 'object' ) { params = new Object() }

        // если у панели есть связанная грид панель, отправим кэллбэк на рефреш
        if (panel.gridPanel) {
            panel.gridPanel.onRefresh = function(){

                if (params.callback && typeof params.callback == 'function') {
                    params.callback();
                }
                panel.gridPanel.onRefresh = Ext6.emptyFn; // подчистим после выполнения
            };

            panel.gridPanel.refreshRecords(null, 0);
        }

        // если у нас просто грид, без вьюфрэйма
        if (panel.gridRefreshFn && typeof panel.gridRefreshFn == 'function') {
            panel.gridRefreshFn(params); log('gridRefreshFn()', params)
        } else log('no gridRefreshFn()', params)
    },

    // запускаем функцию отвечающую за открытие окна при нажатии на принять
    openApplyCallActionWindow: function () {

        var panel = this;

        if (panel.applyCallActionFn && typeof panel.applyCallActionFn == 'function') {panel.applyCallActionFn(); log('openApplyCallActionWindow() calling')}
        else log('can`t call openApplyCallActionWindow()')
    },

    // обновляем комбо в панели с типом 3
    refreshElectronicTalonCombo: function() {

    	var panel = this,
            grid = panel.ownerGrid;

    	// дальше подгружаем в комбик все значения из грида
    	var electronicTalonCombo = panel.electronicQueuePanel.lookup('electronicTalonCombo');
    	var electronicTalonComboStore = electronicTalonCombo.store;

    	if (electronicTalonComboStore) {

    		// очищаем все что там есть
    		electronicTalonComboStore.removeAll();

            if (grid.getStore().getCount() > 0) {

                if (!electronicTalonCombo.isVisible())
                    electronicTalonCombo.show();

                grid.getStore().each(function(record){

                    if (record.get('ElectronicTalonStatus_id') < 4) {

                        var gridIndex = grid.getStore().findBy(function(rec) {
                            return (rec.get('ElectronicTalon_id') == record.get('ElectronicTalon_id'));
                        });

                        if (gridIndex >= 0) {
                            // добавляем талон в комбик
                            electronicTalonComboStore.add(new electronicTalonComboStore.recordType({
                                ElectronicTalon_id: record.get('ElectronicTalon_id'),
                                ElectronicTalon_Num: 'Талон ' + panel.digitsConverter(record.get('ElectronicTalon_Num')),
                                ElectronicTalonStatus_id: record.get('ElectronicTalonStatus_id'),
                                ElectronicQueueGrid_Index: gridIndex
                            }));
                        }
                    }
                });
            } else {
                if (electronicTalonCombo.isVisible())
                    electronicTalonCombo.hide();
            }
    	}
    },

    // обновление особого грида для панели с типом 3
    electronicQueueGridRefresh: function(options) {

    	var panel = this;
    	if (typeof options != 'object') {options = new Object();}

    	var params = new Object();

    	params.begDate = new Date().format('d.m.Y');
    	params.endDate = new Date().format('d.m.Y');

    	panel.ownerGrid.loadStore(params, function() {

    		if (options.callback && typeof options.callback == 'function') {
    			options.callback();
    		}
    	});
    },

    // ситуации когда не нужно обновлять грид, при включененном ноде
    checkNodeConnAndRefresh: function (params) {

        var panel = this,
            ownerWnd = panel.ownerWindow;

        if ( typeof params != 'object' ) { params = new Object() }

        if (ownerWnd.socket && ownerWnd.socket.connected) {

            // выполняем кэллбэк без рефреша
            if (params.callback && typeof params.callback == 'function') {
                params.callback();
            }

        } else panel.gridRefresh(params) // обновляем грид если нод не запущен
    },

    // подходит ли человек по возрасту в соответствии с типом отделения
    checkIsPersonSuitable: function(record) {

        if (!record) return false;

        var panel = this,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact,
            unsuitablePersonMsg = '';

        if (record.get('Person_Age')) {

            var age = record.get('Person_Age');
            if (userMedStaffFact.LpuSectionAge_id) {

                if (age >= 18 && userMedStaffFact.LpuSectionAge_id == 2) {
                    unsuitablePersonMsg = 'Пациент старше 18 лет, прием не возможен.';
                } else if (age < 18 && userMedStaffFact.LpuSectionAge_id == 1) {
                    unsuitablePersonMsg = 'Пациент младше 18 лет, прием не возможен.';
                }

                if (unsuitablePersonMsg) {

                    var buttons = {
                        yes: "ОК"
                    };

                    sw.swMsg.show({
                        icon: Ext.MessageBox.WARNING,
                        buttons: buttons,
                        title: langs('Предупреждение'),
                        msg: unsuitablePersonMsg,

                        fn: function (buttonId) {

                            if (buttonId == 'yes') {
                                if (!record.get('ElectronicTalon_id') ||
                                    record.get('ElectronicTalonStatus_id') > 4) return false;
                                panel.doCancelTalon(record.get('ElectronicTalon_id'));
                            }
                        }
                    });

                } else return true;

            } else {
                return true;
                //todo: потом доработать
                //unsuitablePersonMsg = 'Не указана возрастная группа на отделении, продолжить прием?';
            }
        } else {
            return true;
            //todo: потом доработать
            //unsuitablePersonMsg = 'Не указана дата рождения пациента, продолжить прием?';
        }
    },

    // подмена неизвестного человека
    fixPersonUnknown: function(params) {

        var panel = this,
            grid = panel.ownerGrid,
            ownerWnd = panel.ownerWindow;

        panel.personSelected = false;

        if ( typeof params != 'object' ) { params = new Object() }
        if (!params.Person_id) {log('FALSE | fixPersonUnknown() | Person_id is null'); return false;};

        // открывается форма «Человек: Поиск» для поиска, выбора или создания нового человека
        getWnd('swPersonSearchWindow').show({
            onClose: function() {

                if (!panel.personSelected) {

                    log('onFixPersonCloseGridRefresh');

                    panel.gridRefresh({
                        callback: function() {
                            // ищем запись с челвоеком в гриде
                            var index = grid.getStore().findBy(function(rec) {
                                return rec.get('Person_id') == params.Person_id;
                            });

                            if (index >= 0) {
                                // ставим фокус
                                grid.getView().focusRow(index);
                                grid.getSelectionModel().select(index);
                            }
                        }
                    });
                }

            },
            onSelect: function(person_data) {

                getWnd('swPersonSearchWindow').hide();
                if (person_data.Person_id) {

                    panel.personSelected = true;

                    // Обновляем данные в талоне и удаляем неизвестного
                    ownerWnd.getLoadMask('Обновление данных человека в талоне...').show();
                    Ext6.Ajax.request({
                        url: '/?c=PaidService&m=fixPersonUnknown',
                        params: {
                            Person_oldId: params.Person_id,
                            Person_newId: person_data.Person_id
                        },
                        callback: function() {

                            ownerWnd.getLoadMask().hide();

                            log('onFixPersonSelectGridRefresh');
                            panel.gridRefresh({

                                callback: function() {

                                    // ищем запись с челвоеком в гриде
                                    var index = grid.getStore().findBy(function(rec) {
                                        return rec.get('Person_id') == person_data.Person_id;
                                    });

                                    if (index >= 0) {

                                        // ставим фокус
                                        grid.getView().focusRow(index);
                                        grid.getSelectionModel().select(index);

                                        if (params.callback && typeof params.callback == 'function') {
                                            params.callback();
                                        }
                                    }
                                }
                            });
                        }
                    });
                }
            }
        });
    },

    // проверка на неизвестного
    checkIsUnknown: function (params) {

        if ( typeof params != 'object' ) { params = new Object() }
        if (!params.record) {log('FALSE | checkIsUnknown() | record is null'); return false}

        var panel = this,
            record = params.record;

        if (record.get('Person_IsUnknown') == 2) {

            panel.clearCancelCallTimer(); // если таймер "Вызова" запущен останалвиваем его
            showPopupWarningMsg('Человек неизвестный! Найдите человека в системе или создайте нового.');

            panel.fixPersonUnknown({
                Person_id: record.get('Person_id'),
                callback: (params.callback ? params.callback : null)
            });

            return false;
        }

        return true;
    },

    initNodeListeners: function () {

        var opts = getGlobalOptions(),
            grid = this.ownerGrid,
            ownerWnd = this.ownerWindow,
            panel = this;

        if (!opts || !opts.nodePortalConnectionHost ) {
            log('No socket connection host for ElectronicQueue node server');
            return false;
        }

        if (!ownerWnd.socket) {

            var userMedStaffFact = this.ownerWindow.userMedStaffFact;

            var socketData = {
                Lpu_id: userMedStaffFact.Lpu_id,
                MedService_id: userMedStaffFact.MedService_id,
                MedPersonal_id: userMedStaffFact.MedPersonal_id,
                MedPersonal_FIO: userMedStaffFact.MedPersonal_FIO,
                ElectronicService_id: userMedStaffFact.ElectronicService_id,
                ElectronicQueueInfo_id: userMedStaffFact.ElectronicQueueInfo_id,
                ElectronicTreatment_ids: userMedStaffFact.ElectronicTreatment_ids
            };

            ownerWnd.socket = io(opts.nodePortalConnectionHost, {query: socketData, forceNew: true});
            ownerWnd.socket.on('connect', function () {

                log('connect');

                ownerWnd.socket.on('error', function (nodeResponse) {
                    log('node-error', nodeResponse);
                });

                ownerWnd.socket.on('message', function (nodeResponse) {

                    log('message', nodeResponse);

                    if (nodeResponse.message) {
                        switch(nodeResponse.message) {

                            // ЭО отключена
                            case 'electronicQueueDisabled':
                                panel.gridRefresh();
                                break;

                            // талон перенаправлен
                            case 'electronicTalonRedirected':
                                panel.gridRefresh();
                                break;

                            //	новый талон
                            case 'electronicTalonCreated':
                                panel.gridRefresh();
                                break;

                            // статус талона изменен
                            case 'electronicTalonStatusHasChanged':

                                // просто ищем в гриде и обновляем статус
                                var index = grid.getStore().findBy(function(rec) {
                                    return (rec.get('ElectronicTalon_id') == parseFloat(nodeResponse.ElectronicTalon_id));
                                });

                                if (index >= 0) {
                                    var record = grid.getStore().getAt(index);
                                    record.set('ElectronicTalonStatus_id', parseFloat(nodeResponse.ElectronicTalonStatus_id));
                                    record.set('ElectronicTalonStatus_Name', nodeResponse.ElectronicTalonStatus_Name);
                                    record.set('ElectronicService_id', nodeResponse.ElectronicService_id);
                                    record.commit();

                                    if (panel.showOnlyActive) {
                                        panel.filterElectronicQueueRecords();
                                    } else {

                                        if (panel.panelType == 3) {
                                            panel.refreshElectronicTalonCombo();
                                        }

                                        panel.updateElectronicQueueState();
                                        panel.electronicQueueRowFocus();
                                    }
                                }

                                break;
                        }
                    }
                });
            });
        }
    },

    showDelayedTalonWarning: function(record, action, fn) {

        var panel = this,
            grid = panel.ownerGrid;

        // проверка на отсрочку
        var gridView = grid.getView();

        var idx = grid.getStore().indexOf(record);
        if (gridView && idx != undefined) {

            var row = gridView.getRow(idx);
            log('row', row.className);
            if (row && row.className &&  row.className.includes('eq-call-delay')) {

                var buttons = {
                    yes: "Да",
                    cancel: "Нет"
                };

                sw.swMsg.show({
                    icon: Ext.MessageBox.WARNING,
                    buttons: buttons,
                    title: langs('Предупреждение'),
                    msg: "Возможно пациент еще не дошел до кабинета, Вы действительно хотите его " + action + " ?",

                    fn: function (buttonId) {
                        if (buttonId == 'cancel') { return false; }
                        if (buttonId == 'yes') { fn(); }
                    }

                });
            } else  { fn(); }
        } else { fn(); }
    },

    // вызвать пациента
    doCall: function() {

        var panel = this,
            grid = panel.ownerGrid,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = ownerWnd.userMedStaffFact,
            record = grid.getSelectionModel().getSelection()[0];

        log('record', record);

        if (!record
            || !record.get('ElectronicTalon_id')
            || (record.get('ElectronicTalonStatus_id') != 1)
        ) {
            log('doCall.return false'); return false;
        }

        panel.showDelayedTalonWarning(record, 'вызвать',function () {

            // проверяем наше рабочее место и ПО на "на обслуживании" и вызываем если пусто
            panel.checkIsDigitalServiceBusy(record, 'doCall', function() {

                panel.clearTalonDelayTimer(record);
                ownerWnd.getLoadMask('Вызов пациента').show();

            Ext6.Ajax.request({
                url: '/?c=ElectronicTalon&m=setElectronicTalonStatus',
                params: {
                    ElectronicTalon_id: record.get('ElectronicTalon_id'),
                    ElectronicTalonStatus_id: 2, // Изменяется текущий статус на «Вызван»
                    ElectronicService_id: userMedStaffFact.ElectronicService_id,
                    MedStaffFact_id: userMedStaffFact.MedStaffFact_id,
                    Person_id: record.get('Person_id'),
                    pmUser_did: record.get('pmUser_did')
                },
                callback: function (options, success, response) {

                    // записываем в глоб. переменную чтобы потом отменилась именно эта запись
                    panel.calledRecord = record;
                    ownerWnd.getLoadMask().hide();

                    panel.sendBXPanelData({
                        text: record.get('ElectronicTalon_Num'),
                        mode: 7 // моргает
                    });

                    panel.checkNodeConnAndRefresh();
                }
            });

            });
        })
    },

    // отменить вызов (сменить статус с "вызван" на "ожидает")
    cancelCall: function(rec) {

        var panel = this,
            grid = panel.ownerGrid,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = ownerWnd.userMedStaffFact,
            record = null;

        // если мы получили строку грида через параметр
        if (rec) record = rec;
        else record = grid.getSelectionModel().getSelection()[0];

        if ( !record || !record.get('ElectronicTalon_id')) {
            log('cancelCall.return false');
            return false;
        }

        var ajaxParams = {
            ElectronicTalon_id: record.get('ElectronicTalon_id'),
            ElectronicTalonStatus_id: 1, // Изменяется текущий статус на «Ожидает»
            ElectronicService_id: userMedStaffFact.ElectronicService_id,
            MedStaffFact_id: userMedStaffFact.MedStaffFact_id
        };

        if (!Ext6.isEmpty(userMedStaffFact.ElectronicQueueInfo_CallCount)) {
            ajaxParams.cancelCallCount = userMedStaffFact.ElectronicQueueInfo_CallCount
        }

        ownerWnd.getLoadMask('Отмена вызова').show();

        Ext6.Ajax.request({
            url: '/?c=ElectronicTalon&m=setElectronicTalonStatus',
            params: ajaxParams,
            callback: function (opt, success, response) {

                // очищаем глобальную переменную, вызванной записи
                if (panel.calledRecord) panel.calledRecord = null;
                panel.clearCancelCallTimer();

                ownerWnd.getLoadMask().hide();
                panel.sendBXPanelData({clearPanel: true}); // очищаем панельку
                panel.checkNodeConnAndRefresh();
            }
        });
    },

    getLastSelectedRecord: function() { return this.lastSelectedRecord; },

    // принять пациента
    applyCall: function(options) {

        if ( typeof options != 'object' ) { options = new Object();}

        var panel = this,
            grid = panel.ownerGrid,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = ownerWnd.userMedStaffFact,
            record = grid.getSelectionModel().getSelection()[0];

        if (!record || !record.get('ElectronicTalon_id')) return false;

        panel.lastSelectedRecord = record;

        var applyFn = function() {

            panel.clearTalonDelayTimer(record);
            panel.clearCancelCallTimer(); // если таймер "Вызова" запущен останалвиваем его

            // перед принятием определяем пациента, если он неизвестный
            if (!panel.checkIsUnknown({ record: record, callback: function(){ panel.applyCall({disableDelayedTalonWarning: true}); }})) {
                log('person is inknown from apply'); return false;
            };

            // определяем подходит ли чел для этого отделения по возрасту
            if (!panel.checkIsPersonSuitable(record)) { log('person is not suitable'); return false; };

            // проверяем наше рабочее место и ПО на "на обслуживании" и вызываем если пусто
            panel.checkIsDigitalServiceBusy(record, 'applyCall', function(){

                //win.applyCallRequest(options, applyParams)
                ownerWnd.getLoadMask('Прием пациента').show();
                var selectedRecord = grid.getSelectionModel().getSelection()[0];

                log('selectedRecord', selectedRecord);

                var params = {
                    ElectronicTalon_id: selectedRecord.get('ElectronicTalon_id'),
                    ElectronicService_id: userMedStaffFact.ElectronicService_id,
                    MedStaffFact_id: userMedStaffFact.MedStaffFact_id
                };

                // если это какой-либо тип диспансеризации (профосмотры например)
                // шлем доп. параметры
                if (panel.DispClass_id) {
                    params.Person_id = selectedRecord.get('Person_id');
                    params.DispClass_id = panel.DispClass_id;
                    params.EvnDirection_id = selectedRecord.get('EvnDirection_id');

                    var EvnPLmodel = '';

                    switch (panel.DispClass_id) {
                        case 10:
                            EvnPLmodel_id = 'EvnPLDispTeenInspection_id';
                            break;
                    }

                    params[EvnPLmodel_id] = selectedRecord.get(EvnPLmodel_id);
                    log('disp_params', params);
                }

                Ext6.Ajax.request({
                    url: '/?c=ElectronicQueue&m=applyCall',
                    params: params,
                    callback: function (opt, success, response) {

                        ownerWnd.getLoadMask().hide();

                        if (success) {
                            if ( response.responseText.length > 0 ) {

                                var result = Ext.util.JSON.decode(response.responseText);
                                if (result.success) {

                                    panel.sendBXPanelData({
                                        text: selectedRecord.get('ElectronicTalon_Num'),
                                        mode: 1
                                    });

                                    var refreshParams = {
                                        callback: function(){

                                            panel.electronicQueueData = panel.getElectronicQueueData({
                                                electronicTalonStatus_id: 3,
                                                selectedRecord: selectedRecord
                                            });

                                            if (panel.DispClass_id) {

                                                var EvnPLmodel = '';
                                                switch (panel.DispClass_id) {
                                                    case 10:
                                                        EvnPLmodel_id = 'EvnPLDispTeenInspection_id';
                                                        break;
                                                }

                                                log(EvnPLmodel_id);

                                                if (result[EvnPLmodel_id]) {
                                                    panel.electronicQueueData.EvnPLObjectId = result[EvnPLmodel_id];
                                                    panel.electronicQueueData.DispClass_id = panel.DispClass_id;
                                                }
                                            }

                                            log('applyCall.electronicQueueData', panel.electronicQueueData);
                                            panel.openApplyCallActionWindow();
                                        }
                                    };

                                    panel.checkNodeConnAndRefresh(refreshParams);
                                }
                            }
                        }
                    }
                });
            });
        }

        if (!options.disableDelayedTalonWarning) {
            panel.showDelayedTalonWarning(record, 'принять', applyFn);
        } else applyFn();
    },

    // завершить прием
    finishReceive: function(options) {

        if ( typeof options != 'object' ) { options = new Object() }
        log('finishReceive is called');

        var panel = this,
            grid = panel.ownerGrid,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = ownerWnd.userMedStaffFact,
            record = grid.getSelectionModel().getSelection()[0];

        if (!record && !options.electronicTalon_id) return false;
        var electronicTalon_id = (options.electronicTalon_id ? options.electronicTalon_id : record.get('ElectronicTalon_id'))

        if (!electronicTalon_id) { panel.gridRefresh(); return false;};

        ownerWnd.getLoadMask('Завершение приема').show();
        Ext6.Ajax.request({
            url: '/?c=ElectronicQueue&m=finishCall',
            params: {
                ElectronicTalon_id: electronicTalon_id,
                ElectronicService_id: userMedStaffFact.ElectronicService_id,
                DispClass_id: ((panel.DispClass_id) ? panel.DispClass_id : null)
            },
            callback: function (opt, success, response) {

                if (success) {

                    if (response.responseText.length > 0) {
                        var result = Ext.util.JSON.decode(response.responseText);

                        if (result.success) {
                            if (result.nextCab) {
                                sw.swMsg.alert('Следующий кабинет',
                                    'Следующий кабинет для пациента: ' + result.nextCab);
                            }
                        }
                    }

                    ownerWnd.getLoadMask().hide();
                    panel.sendBXPanelData({clearPanel: true});

                    var callbackFn = null;

                    if (options.callNext) {callbackFn = function (){ panel.doCall(); }
                    } else if (options.callback && typeof options.callback === 'function') {
                        callbackFn = function () { options.callback(); }
                    }
                    panel.checkNodeConnAndRefresh({callback: callbackFn});
                    if (options.hideForm) options.hideForm.hide();
                }
            }
        });
    },

    // отменить талон
    doCancel: function() {

        var panel = this,
            grid = panel.ownerGrid,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = ownerWnd.userMedStaffFact,
            record = grid.getSelectionModel().getSelection()[0];

        if (!record || !record.get('ElectronicTalon_id')) return false;

        ownerWnd.getLoadMask('Отмена талона').show();
        Ext6.Ajax.request({
            url: '/?c=ElectronicTalon&m=setElectronicTalonStatus',
            params: {
                ElectronicTalon_id: record.get('ElectronicTalon_id'),
                ElectronicTalonStatus_id: 5, // статус «Отменен»
                ElectronicService_id: userMedStaffFact.ElectronicService_id
            },
            callback: function (options, success, response) {

                ownerWnd.getLoadMask().hide();
                panel.checkNodeConnAndRefresh();
            }
        });
    },

    // проверка не занят ли пункт обслуживания
    checkIsDigitalServiceBusy: function(record, serviceAction, callbackFn) {

        var panel = this,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = ownerWnd.userMedStaffFact;

        // проверяем кабинет на доступность
        Ext6.Ajax.request({
            url: '/?c=ElectronicQueue&m=checkIsDigitalServiceBusy',
            params: {
                ElectronicService_id: userMedStaffFact.ElectronicService_id,
                MedStaffFact_id: userMedStaffFact.MedStaffFact_id,
                ServiceAction: serviceAction
            },
            callback: function (opt, success, response) {

                ownerWnd.getLoadMask().hide();
                var serviceChecking = JSON.parse(response.responseText);

                if (serviceChecking.success) {

                    // если есть сообщение проверки
                    if (serviceChecking.Check_Msg) {

                        sw.swMsg.show({
                            icon: Ext.MessageBox.WARNING,
                            buttons: {
                                yes: "Завершить обслуживание",
                                cancel: "Отмена"
                            },
                            title: langs('Предупреждение'),
                            msg: serviceChecking.Check_Msg,

                            fn: function (buttonId) {

                                if (buttonId == 'yes') {

                                    if (serviceChecking.data.ElectronicTalon_id) {

                                        panel.finishReceive({
                                            electronicTalon_id: serviceChecking.data.ElectronicTalon_id,
                                            callback: function(){
                                                if (callbackFn && typeof callbackFn === 'function') { callbackFn(); }
                                            }
                                        });

                                    } else {showPopupWarningMsg('Невозможно завершить вызов, нет данных талона ЭО');}
                                }
                            }
                        });

                        // проверка прошла, продолжаем
                    } else {
                        if (callbackFn && typeof callbackFn === 'function') { callbackFn(); }
                    }
                }

            }
        });
    },

    // прамис для любого аякс запроса
    ajaxRequestPromise: function(url, ajax_params) {
        return new Promise(function(resolve, reject) {
            Ext6.Ajax.request({

                params: ajax_params,
                url: url,
                success: function(response) {resolve(JSON.parse(response.responseText))},
                failure: function(response) {reject(response)}
            })
        })
    },

    // отправить текст на связанное с этим Армом табло
    sendBXPanelData: function(params) {

        var panel = this,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = ownerWnd.userMedStaffFact;

        if ( typeof params != 'object' ) {params = new Object()}
        if (!params.clearPanel && !params.text) return false;

        if (userMedStaffFact.ElectronicScoreboard_id &&
            userMedStaffFact.ElectronicScoreboard_Port &&
            userMedStaffFact.ElectronicScoreboard_IPaddress
        ){

            var boardPort = userMedStaffFact.ElectronicScoreboard_Port,
                boardIp = userMedStaffFact.ElectronicScoreboard_IPaddress,
                boardIpAddress = [],
                text = params.text,
                mode = (params.mode ? params.mode : 2);

            if (params.clearPanel) {text = ' '}
            else {
                text = panel.digitsConverter(parseInt(text));
            }

            // обработаем нули в адресе
            boardIp = boardIp.split('.');
            boardIp.forEach(function(addr) {boardIpAddress.push(parseInt(addr))});
            boardIp = boardIpAddress.join('.');

            $.ajax({
                url: 'https://localhost:8088/AraService/BXPanel/Send?IP='+boardIp+'&text='+text+'&mode='+mode+'&port='+boardPort,
                dataType: 'jsonp',
                type: 'GET',
                jsonpCallback: "devicecallback",
                timeout: 3500,
                success: function (data) {

                    log('BXPanel SUCCESS', data);
                    if (data.errorMessage) {showPopupWarningMsg('Нет связи с электронным табло. Самостоятельно проинформируйте пациента.')}
                },
                error: function (data) {

                    log('BXPanel ERROR', data);
                    showPopupWarningMsg('Нет связи с электронным табло. Самостоятельно проинформируйте пациента.');
                }
            });

        } else return false;
    },

    // переключение вида кнопок когда выбрана строка
    onRowSelectElectronicQueue: function() {

        var panel = this,
            grid = panel.ownerGrid,
            form = panel.electronicQueuePanel,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = ownerWnd.userMedStaffFact,
            selModel = grid.getSelectionModel(),
            record = grid.getSelectionModel().getSelection()[0];

        //log('onRowSelectElectronicQueue', record);

        if (!Ext6.isEmpty(userMedStaffFact.ElectronicService_id) ) {

            var currDate = new Date().format('d.m.Y'),
                redirected = record.get('EvnDirection_uid') && (record.get('toElectronicService_id') != userMedStaffFact.ElectronicService_id);

            var timetableDataType = record.get('TimetableGraf_Date'); // при записи на бирку
            if (!timetableDataType) {timetableDataType = record.get('TimetableResource_Date')} // при записи на ресурс
            if (!timetableDataType) {timetableDataType = record.get('TimetableMedService_Date')} // при записи на службу
            if (!timetableDataType) {timetableDataType = record.get('ElectronicTalon_Date')} // без записи на бирку


            if (record && record.get('Person_id') && !redirected) {

                panel.toggleElectronicQueueButtons({ // состояние по умолчанию
                    buttons: ["electronicQueueCall", "electronicQueueReceive", "electronicQueueRedirect"],
                    disabled_state: [-1], // дизабленые все
                    record: record
                });

                if (selModel.getCount() == 1) {
                    var ttgDate = 0;
                    if (timetableDataType) ttgDate = timetableDataType.dateFormat('d.m.Y');

                    var dateCondition = (currDate == ttgDate);
                    if (panel.DispClass_id) dateCondition = record.get('IsCurrentDate');

                    log('talon_status',record.get('ElectronicTalonStatus_id'));
                    log('ttgDate', ttgDate);

                    if (record.get('IsCurrentDate')) log('IsCurrentDate', dateCondition);

                    // если статус талона "ОЖИДАЕТ"
                    if (record.get('ElectronicTalonStatus_id') == 1 && (dateCondition)) {
                        // если нет ни одного пациента с текущим статусом талона ЭО «Вызван»
                        var index = grid.getStore().findBy(function(rec) {
                            return (rec.get('ElectronicTalonStatus_id') == 2);
                        });

                        log('index',index);

                        if (index >= 0) {

                            log('index >= 0')
                            panel.toggleElectronicQueueButtons({
                                buttons: ["electronicQueueCall", "electronicQueueReceive", "electronicQueueRedirect"],
                                disabled_state: [1],  //дизаблим первую
                                record: record
                            });
                        } else {

                            log('index <= 0')

                            panel.toggleElectronicQueueButtons({
                                buttons: ["electronicQueueCall", "electronicQueueReceive", "electronicQueueRedirect"],
                                record: record
                            });
                        }
                    }

                    // если статус талона "ВЫЗВАН"
                    if (
                        record.get('ElectronicTalonStatus_id')
                        && record.get('ElectronicTalonStatus_id') == 2
                    ) {
                        panel.toggleElectronicQueueButtons({
                            buttons: ["electronicQueueCancelCall", "electronicQueueReceive", "electronicQueueRedirect"],
                            record: record,
                            disabled_state: [3] // третья дизабленная
                        });
                    } else if (
                        record.get('ElectronicTalonStatus_id')
                        && record.get('ElectronicTalonStatus_id') == 2
                    ) {

                        panel.toggleElectronicQueueButtons({
                            buttons: ["electronicQueueCancelCall", "electronicQueueReceive", "electronicQueueRedirect"],
                            disabled_state: [2,3], // вторая, третья дизабленные
                            record: record
                        });
                    }

                    // если статус талона "НА ОБСЛУЖИВАНИИ"
                    if (
                        record.get('ElectronicTalonStatus_id')
                        && record.get('ElectronicTalonStatus_id') == 3
                    ) {
                        panel.toggleElectronicQueueButtons({
                            buttons: ["electronicQueueFinishAndNext", "electronicQueueFinishReceive", "electronicQueueRedirect"],
                            record: record
                        });
                    }
                }
            }

            // для панельки с типом 3, меняем значение в комбике при выборе
            if (panel.panelType == 3) {

                var talonCombo = form.lookup('electronicTalonCombo'),
                    buttonsDiv = form.lookup('electronicQueue_buttons'),
                    comboStore = talonCombo.getStore(),
                    itemsCount = comboStore.getCount();

                if (record && record.get('ElectronicTalon_Num')) {
                    if (comboStore && itemsCount > 0) {
                        log('setValue', record.get('ElectronicTalon_Num'))
                        talonCombo.setValue('Талон ' + panel.digitsConverter(record.get('ElectronicTalon_Num')));
                    }
                }

                if (!itemsCount && buttonsDiv.isVisible()) buttonsDiv.hide();
                else if (itemsCount && !buttonsDiv.isVisible()) buttonsDiv.show();
            }
        }


    },

    // инициализация вида кнопок
    resetElectronicQueueButtons: function() {

        var panel = this,
            form = panel.electronicQueuePanel;

        var buttons = [
            'electronicQueueCall',
            'electronicQueueReceive',
            'electronicQueueCancelCall',
            'electronicQueueFinishReceive',
            'electronicQueueFinishAndNext',
            'electronicQueueRedirect'
        ];

        buttons.forEach(function(btnName){

            var btn = form.lookup(btnName);
            if (btn) {

                btn.hide(); // хайдим все предварительно все кнопки
                btn.enable(); // разрешаем предварительном все конпки

                // приводим кнопку "приема" в стандартный вид
                if (btnName == 'electronicQueueReceive') {

                    btn.removeCls('full-name');
                    btn.setIconCls('eq-chair eq-no-pos');
                    btn.setText(btn.initialConfig.text)
                }
            }

        })

    },

    // сброс таймера отмены вызова
    clearCancelCallTimer: function() {

        var panel = this,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        if (panel.cancelCallTimerRunner && panel.timerActivated) {
            panel.cancelCallTimerRunner.stop(panel.cancelCallTimerTask); log('stop timer')
            panel.timerActivated = false;
        }

        if (!Ext6.isEmpty(userMedStaffFact.ElectronicQueueInfo_CallTimeSec)) {
            panel.electronicQueueCancelCallTimer = userMedStaffFact.ElectronicQueueInfo_CallTimeSec
        } else { panel.electronicQueueCancelCallTimer = 30; }
    },

    // инициализация таймера отмены вызова
    initCancelCallTimerTask: function() {

        var panel = this,
            cancelCallButton = panel.electronicQueuePanel.lookup('electronicQueueCancelCall');

        if (!panel.cancelCallTimerTask) {

            panel.clearCancelCallTimer();
            panel.cancelCallTimerTask = {

                run: function () {

                    // берем глобальный таймер и уменьшаем его на 1
                    panel.electronicQueueCancelCallTimer--;

                    var record = panel.ownerGrid.getSelectionModel().getSelection()[0],
                        btnTextContent = '';


                    if (panel.panelType != 3) {

                        //подготовим ФИО если оно нужно на кнопке
                        if (record && record.get('Person_Surname')) {

                            btnTextContent = panel.ucfirst(record.get('Person_Surname').toLowerCase()) + ' ';

                            var firNameFirstCh = (record.get('Person_Firname') ? record.get('Person_Firname') : ''),
                                secNameFirstCh = (record.get('Person_Secname') ? record.get('Person_Secname') : '');

                            btnTextContent += firNameFirstCh.substring(0, 1) + '.' + secNameFirstCh.substring(0, 1) + '.';
                        }
                    } else {
                        // для типа панелей 3, на кнопке пишем номер талона
                        btnTextContent = panel.digitsConverter(record.get('ElectronicTalon_Num'));
                    }

                    // формируем текст кнопки
                    var btnTxt = cancelCallButton.initialConfig.text +
                        '<br><i class="full-name">' +
                        btnTextContent +
                        '</i>' +
                        '<span class="timer">' +
                        '00:' + ((panel.electronicQueueCancelCallTimer < 10) ? '0' + panel.electronicQueueCancelCallTimer : panel.electronicQueueCancelCallTimer) +
                        '</span>';

                    cancelCallButton.setText(btnTxt);  // обновляем текст

                    // если время таймера закончилось
                    if (panel.electronicQueueCancelCallTimer <= 0) {

                        panel.clearCancelCallTimer(); // останавливаем задачу

                        // отменяем вызов (вызванная запись хранится в глобальной переменной)
                        if (panel.calledRecord) panel.cancelCall(panel.calledRecord);
                    }
                },
                interval: 1000 // 1 second
            };

            panel.cancelCallTimerRunner = new Ext6.util.TaskRunner();
        }
    },

    // обработка строки, первая буква большая
    ucfirst: function(str) {
        var f = str.charAt(0).toUpperCase();
        return f + str.substr(1, str.length-1);
    },

    isEmpty: function(obj) {

        // null and undefined are "empty"
        if (obj == null) return true;

        // Assume if it has a length property with a non-zero value
        // that that property is correct.
        if (obj.length > 0)    return false;
        if (obj.length === 0)  return true;

        // If it isn't an object at this point
        // it is empty, but it can't be anything *but* empty
        // Is it empty?  Depends on your application.
        if (typeof obj !== "object") return true;

        // Otherwise, does it have any properties of its own?
        // Note that this doesn't handle
        // toString and valueOf enumeration bugs in IE < 9
        for (var key in obj) {
            if (this.hasOwnProperty.call(obj, key)) return false;
        }

        return true;
    },

    // переключение шаблона отображения кнопок на панели
    toggleElectronicQueueButtons: function(params) {

        var panel = this,
            form = panel.electronicQueuePanel;

        if ( typeof params != 'object' ) { params = new Object();}
        panel.resetElectronicQueueButtons(); // сбросим кнопки

        var person = '', btnContent = '';

        // для типа панелей 1 и 2 на кнопке пишем ФИО
        if (panel.panelType != 3) {
            if (!params.personData && params.record) {

                person = {
                    Person_Surname: params.record.get('Person_Surname'),
                    Person_Firname: params.record.get('Person_Firname'),
                    Person_Secname: params.record.get('Person_Secname')
                }

            } else person = params.personData;

            //подготовим ФИО если оно нужно на кнопке
            if (person && person.Person_Surname) {

                btnContent = panel.ucfirst(person.Person_Surname.toLowerCase()) + ' ',
                    firNameFirstCh = (person.Person_Firname ? person.Person_Firname : ''),
                    secNameFirstCh = (person.Person_Secname ? person.Person_Secname : '');

                btnContent += firNameFirstCh.substring(0, 1) + '.' + secNameFirstCh.substring(0, 1) + '.';
            }
        } else {
            // для типа панелей 3, на кнопке пишем номер талона
            btnContent = panel.digitsConverter(params.record.get('ElectronicTalon_Num'));
        }

        var storedBtnContent = btnContent;

        //log('buttins', params);

        params.buttons.forEach(function(btn, k) {

            //log('btn', btn);
            el = form.lookup(btn);
            if (el) {

                //log('btnEl', el);

                // если случилось так что затерли основного персона на кнопке
                if (!btnContent) btnContent = storedBtnContent;

                // смотрим нужно ли кнопку дизаблить
                if (!params.disabled_state) params.disabled_state = [0];

                params.disabled_state.forEach(function(disabledIndex){

                    if (disabledIndex == -1) el.disable();
                    else if (k+1 == disabledIndex) el.disable();
                });

                switch(btn) { // доп. манипуляции

                    case 'electronicQueueCancelCall': // имеется "отменить вызов"?

                        // если таймер не запущен запускаем
                        if (!panel.timerActivated) {
                            panel.clearCancelCallTimer();
                            panel.timerActivated = true;
                            panel.cancelCallTimerRunner.start(panel.cancelCallTimerTask); log('startCancelCallTimer');
                        }

                        break;

                    case 'electronicQueueReceive':

                        // если таймер запущен значит мы имеем первой кнопкой отмену вызова
                        if (panel.timerActivated) {
                            var btnTxt = el.initialConfig.text +'<br><i class="full-name">' + btnContent + '</i>';

                            el.setIconCls('eq-chair-full-name eq-no-pos');
                            el.setText(btnTxt);
                        }
                        break;

                    case 'electronicQueueFinishAndNext':

                        btnContent = (params.nextPersonFin ? params.nextPersonFin : '');

                        if (!btnContent && panel.panelType != 2) {

                            var nextRecord = panel.getNextElectronicQueueRecord();
                            if (nextRecord) {

                                if (panel.panelType == 1) {

                                    var	nfirNameFirstCh = (nextRecord.get('Person_Firname') ? nextRecord.get('Person_Firname') :''),
                                        nsecNameFirstCh = (nextRecord.get('Person_Secname') ? nextRecord.get('Person_Secname') :'');

                                    btnContent = panel.ucfirst(nextRecord.get('Person_Surname').toLowerCase()) + ' '
                                        + nfirNameFirstCh.substring(0, 1)+'.' + nsecNameFirstCh.substring(0, 1)+'.';

                                } else if (panel.panelType == 3) {
                                    btnContent = panel.digitsConverter(nextRecord.get('ElectronicTalon_Num'));
                                }
                            } else el.disable(); // дизаблим кнопку
                        }

                        el.setText(el.initialConfig.text +
                            '<i class="full-name">' + btnContent + '</i>'
                        );

                        btnContent = ''; // обнуляем, чтобы на след. конопке был основной персон
                        break;

                    case 'electronicQueueFinishReceive':

                        el.setText(el.initialConfig.text + '<br><i class="full-name">' + btnContent + '</i>');
                        if (panel.dontDisableCompleteBtn) el.enable(); // костыль для АРМА ФД
                        break;
                }
                el.show(); // показываем кнопку
            }
        });

        //var eq = [
        //    "eq-btn-cancel_call",
        //    "eq-btn-call",
        //    "eq-btn-redirect",
        //    "eq-btn-complete",
        //    "eq-btn-complete_next",
        //    "eq-btn-take",
        //    "eq-btn-redirect"
        //];
    },

    // обновление статуса панели ЭО
    updateElectronicQueueState: function() {

        var panel = this,
            form = panel.electronicQueuePanel,
            recorded = form.lookup('electronicQueue_recorded'),
            serviced = form.lookup('electronicQueue_serviced'),
            away = form.lookup('electronicQueue_away');

        recorded.setValue(panel.recountElectronicQueueRecordsStates()); // записаны
        serviced.setValue(panel.recountElectronicQueueRecordsStates(4) + panel.recountElectronicQueueRecordsStates(5)); // обслужены
        away.setValue(panel.recountElectronicQueueRecordsStates(1)); // ожидают
    },

    // пересчет статусных значений
    recountElectronicQueueRecordsStates: function(stateNum) {

        var panel = this,
            grid = panel.ownerGrid,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact,
            sum = 0;

        if (grid.store.getCount() > 0) {

            if (stateNum) {
                grid.store.each(function (rec) {

                    // переопределяем признак, если талон возращен обратно,
                    // но имеется связь в таблице перенаправлений, чтобы адекватно посчитать
                    if (!rec.get('ElectronicTalonStatus_id')
                        && (rec.get('toElectronicService_id') == userMedStaffFact.ElectronicService_id)
                        && stateNum != 1
                        && stateNum != 5
                    ) {
                        sum++
                    }

                    var redirected = rec.get('EvnDirection_uid') && (rec.get('toElectronicService_id') != userMedStaffFact.ElectronicService_id);
                    if ((rec.get('ElectronicTalonStatus_id') == stateNum) && !redirected) {
                        sum++;
                    }

                    if (redirected && stateNum == 4) { sum++; }
                });

            } else {
                grid.store.each(function (rec) {

                    if (rec.get('ElectronicTalon_Num')) { sum++; }
                    else if (!rec.get('ElectronicTalon_Num')
                        && (rec.get('toElectronicService_id') == userMedStaffFact.ElectronicService_id)
                        && stateNum != 1
                        && stateNum != 5
                    ) {
                        sum++
                    }
                });
            }
        }

        return sum;
    },

    // получить следующую по порядку запись ЭО (на основе номера талона)
    getNextElectronicQueueRecord: function() {

        var panel = this,
            grid = panel.ownerGrid,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        if (grid.getStore().getCount()>0) {
            // получаем из стора запись с талоном со статусом "В ОЖИДАНИИ"
            // и минимальным номером талона
            var minTalonNum = 0, // получаем стартовое значение
                record = 0;

            grid.getStore().each(function(rec)
            {
                var redirected = rec.get('EvnDirection_uid') && (rec.get('toElectronicService_id') != userMedStaffFact.ElectronicService_id);

                if (!minTalonNum
                    && rec.get('ElectronicTalonStatus_id') == 1
                    && rec.get('ElectronicTalon_Num') > 0
                    && !redirected
                ) {
                    minTalonNum = rec.get('ElectronicTalon_Num');
                }

                if (rec.get('ElectronicTalonStatus_id') == 1
                    && rec.get('ElectronicTalon_Num') > 0
                    && rec.get('ElectronicTalon_Num') <= minTalonNum
                    && !redirected
                ) {

                    record = rec;
                    minTalonNum = rec.get('ElectronicTalon_Num');
                }
            });

            if (record)
                return record;
            else return false

        } else {
            return false
        }
    },

    // фокус на строке грида связанного с ЭО
    electronicQueueRowFocus: function() {

        var panel = this,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        if (!Ext6.isEmpty(userMedStaffFact.ElectronicService_id)) {

            var grid = panel.ownerGrid;
            var store = grid.getStore();

            if (store.getCount()>0) {

                grid.getSelectionModel().clearSelections();

                // получаем из стора запись с талоном со статусом "НА ОБСЛУЖИВАНИИ"
                var index = store.findBy(function(rec) {

                    var redirected = rec.get('EvnDirection_uid') && (rec.get('toElectronicService_id') != userMedStaffFact.ElectronicService_id);

                    return (
                        rec.get('ElectronicTalonStatus_id') == 3
                        && !redirected
                    );
                });

                // если нет
                if (index == -1) {

                    // получаем из стора запись с талоном со статусом "ВЫЗВАН"
                    index = store.findBy(function(rec) {

                        var redirected = rec.get('EvnDirection_uid') && (rec.get('toElectronicService_id') != userMedStaffFact.ElectronicService_id);

                        return (
                            rec.get('ElectronicTalonStatus_id') == 2
                            && !redirected
                        );
                    })

                    // если нет
                    if (index == -1) {

                        // получаем из стора запись с талоном со статусом "В ОЖИДАНИИ"
                        // и минимальным номером талона
                        var minTalonNum = 0, // получаем стартовое значение
                            record = 0;

                        store.each(function(rec)
                        {
                            var redirected = rec.get('EvnDirection_uid') && (rec.get('toElectronicService_id') != userMedStaffFact.ElectronicService_id);

                            if (!minTalonNum
                                && rec.get('ElectronicTalonStatus_id') == 1
                                && rec.get('ElectronicTalon_Num') > 0
                                && !redirected
                            ) {
                                minTalonNum = rec.get('ElectronicTalon_Num');
                            }

                            if (rec.get('ElectronicTalonStatus_id') == 1
                                && rec.get('ElectronicTalon_Num') > 0
                                && rec.get('ElectronicTalon_Num') <= minTalonNum
                                && !redirected
                            ) {

                                record = rec;
                                minTalonNum = rec.get('ElectronicTalon_Num');
                            }
                        });

                        log(minTalonNum);

                        if (record) {

                            // фокус на талон "ожидает"
                            index = store.indexOf(record);
                            log('row focus, "ожидает" ', index);
                            log('row focus, "минимальный"',minTalonNum);

                            grid.getSelectionModel().select(index);

                        } else {

                            log('row focus, "select first row"')
                            grid.getSelectionModel().select(0);
                        }

                    } else {

                        // фокус на талон "вызван"
                        grid.getSelectionModel().select(index);
                    }

                } else {
                    // фокус на талон "на обслуживании"
                    grid.getSelectionModel().select(index);
                }
            } else {

                // сбросим кнопки если нет никого в гриде
                panel.resetElectronicQueueButtons()
            }
        }
    },

    // фильтр хранилища записей грида
    filterElectronicQueueRecords: function() {

        var panel = this,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        if (!userMedStaffFact || (userMedStaffFact && !userMedStaffFact.ElectronicService_id)) { return false; }

        log('filterElectronicQueueRecords');

        var TimetableGraf_id = null;
        var grid = panel.ownerGrid;

        var record = grid.getSelectionModel().getSelection()[0];
        if (record && record.get('TimetableGraf_id')) {
            TimetableGraf_id = record.get('TimetableGraf_id');
        }

        grid.getStore().clearFilter();
        panel.updateElectronicQueueState();

        log('panel.showOnlyActive', panel.showOnlyActive);
        if (panel.showOnlyActive) {

            grid.getStore().filterBy(function(record) {
               return panel.checkElectronicQueueRecordActive(record)
            });

        } else { grid.getStore().filterBy(function() {return true;})}

        panel.electronicQueueRowFocus();
    },

    // подгружаем параметры загрузки грида
    setElectronicQueueLoadStoreParams: function(){

        var panel = this,
            grid = panel.ownerGrid,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        if (grid.params) {
            grid.params.showLiveQueue = (panel.showLiveQueue ? 1 : null);
            grid.params.ElectronicService_id =(userMedStaffFact.ElectronicService_id ? userMedStaffFact.ElectronicService_id : null);
        }

        // бывают названы по другому
        if (grid.loadParams) {
            grid.loadParams.showLiveQueue = (panel.showLiveQueue ? 1 : null);
            grid.loadParams.ElectronicService_id =(userMedStaffFact.ElectronicService_id ? userMedStaffFact.ElectronicService_id : null);
        }
    },

    // фильтр по которому выбираются записи для галочки "только очередь"
    checkElectronicQueueRecordActive: function(row) {

        var userMedStaffFact = this.ownerWindow.userMedStaffFact;

        return (
            (row.get('ElectronicTalonStatus_id')
            && row.get('ElectronicTalonStatus_id').inlist([1]))
            ||
            (row.get('ElectronicService_id') == userMedStaffFact.ElectronicService_id
            && row.get('ElectronicTalonStatus_id')
            && row.get('ElectronicTalonStatus_id').inlist([2,3]))
        );
    },

    getElectronicQueueData: function(params){

        var panel = this,
            form = this.electronicQueuePanel,
            grid = panel.ownerGrid,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        if ( typeof params != 'object' ) { params = new Object();}

        // если это ЭО подготовим данные о состоянии очереди
        if (userMedStaffFact.ElectronicService_id) {

            var eqPanelState = {
                recorded: form.lookup('electronicQueue_recorded').getValue(),
                serviced: form.lookup('electronicQueue_serviced').getValue(),
                away: form.lookup('electronicQueue_away').getValue()
            };

            var nextPersonFin = null,
                electronicTalonStatus_id = 0;

            var nextRecord = panel.getNextElectronicQueueRecord();

            var record = params.selectedRecord
                ? params.selectedRecord
                : grid.getSelectionModel().getSelection()[0];

            if (nextRecord) {

                var	firNameFirstCh = (nextRecord.get('Person_Firname') ? nextRecord.get('Person_Firname') :''),
                    secNameFirstCh = (nextRecord.get('Person_Secname') ? nextRecord.get('Person_Secname') :'');

                nextPersonFin = panel.ucfirst(nextRecord.get('Person_Surname').toLowerCase()) + ' '
                    + firNameFirstCh.substring(0, 1)+'.' + secNameFirstCh.substring(0, 1)+'.';
            }

            if (params.electronicTalonStatus_id) {
                electronicTalonStatus_id = params.electronicTalonStatus_id;
            } else if (panel.isEmpty(params)) {

                electronicTalonStatus_id = (record.get('ElectronicTalonStatus_id')
                    ? record.get('ElectronicTalonStatus_id')
                    : null
                )
            };

            var electronicTalon_id = (record.get('ElectronicTalon_id') ? record.get('ElectronicTalon_id') : null);
            var evnDirection_id = (record.get('EvnDirection_id') ? record.get('EvnDirection_id') : null);
            var isRedirectedTo = record.get('EvnDirection_uid') && (userMedStaffFact.ElectronicService_id == record.get('toElectronicService_id'))
            var fromElectronicService_id = record.get('fromElectronicService_id');

            sender_object = {
                Person_id: record.get('Person_id'),
                ElectronicService_id: record.get('ElectronicService_id'),
                ElectronicTalon_IsBusy: (record.get('ElectronicTalon_IsBusy') != undefined && record.get('ElectronicTalon_IsBusy') > 0),
                eqPanelState: eqPanelState,
                electronicTalonStatus_id: electronicTalonStatus_id,
                electronicTalon_id: electronicTalon_id,
                evnDirection_id: evnDirection_id,
                electronicTalonNum: (record.get('ElectronicTalon_Num') ? record.get('ElectronicTalon_Num') : null),
                nextPersonFin: nextPersonFin,
                isRedirectedTo: isRedirectedTo,
                senderPanel: panel,
                fromElectronicService_id: fromElectronicService_id,
                personData: {
                    Person_Firname: record.get('Person_Firname'),
                    Person_Secname: record.get('Person_Secname'),
                    Person_Surname: record.get('Person_Surname')
                },
                electronicServiceNum: (userMedStaffFact.ElectronicService_Num ? parseInt(userMedStaffFact.ElectronicService_Num) : 0),
            };

            if (panel.onLoadEmkWindow && typeof panel.onLoadEmkWindow === 'function'){
                sender_object.onLoadEmkWindow = panel.onLoadEmkWindow;
                panel.onLoadEmkWindow = null;
            }

            if (panel.DispClass_id) {

                var EvnPLmodel_id = '';

                switch (panel.DispClass_id) {
                    case 10:
                        EvnPLmodel_id = 'EvnPLDispTeenInspection_id';
                        break;
                }

                if (EvnPLmodel_id) {
                    sender_object.EvnPLObjectId = record.get(EvnPLmodel_id);
                    sender_object.DispClass_id = panel.DispClass_id;
                }
            }

            return sender_object;

        } else return false;
    },

    // включение\отключение элементов связанных с ЭО
    toggleElectronicQueueElements: function(params)
    {
        if ( typeof params != 'object' ) { params = new Object() }
        var panel = this,
            grid  = panel.ownerGrid,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        var electronicQueueGridField = [
            'ElectronicTalonStatus',
            'ElectronicTalonStatus_Name',
            'ElectronicTalon_Num'
        ];

        // показать поле "повод обращения"
        if (userMedStaffFact.ElectronicService_isShownET) {
            electronicQueueGridField.push('ElectronicTreatment_Name');
        }

        log('toggleElectronicQueueElements');

        if (params.dontShow) {
            if (panel.isVisible()) {log('panel hide'); panel.hide()}
            if (panel.ElectronicQueueGrid) {log('eqGrid hide'); panel.ElectronicQueueGrid.hide()}
        } else {
            if (!panel.isVisible()) {log('panel show'); panel.show()}
        }

        //// перерисовываем
        //if (params.layoutPanelId) {log('syncSize', params.layoutPanelId); ownerWnd.findById(params.layoutPanelId).syncSize() }
        //else if (ownerWnd.CenterPanel) ownerWnd.CenterPanel.syncSize();
        //
        var hc = grid.getHeaderContainer();
        var cols = hc.getGridColumns();

        electronicQueueGridField.forEach(function (fName) {

            cols.forEach(function(col) {
                if (col.dataIndex == fName) {
                    if (params.dontShow) { if (!col.isHidden()) col.setHidden(true) }
                    else { if (col.isHidden()) col.setHidden(false) }
                }
            });
        })
    },

    redirectElectronicTalon: function(params){

        if ( typeof params != 'object' ) { params = {}; }

        var panel = this,
            grid  = panel.ownerGrid,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        var
            ElectronicTalonStatus_id = null,
            electronicTalon_id = null,
            evnDirection_id = null,
            fromElectronicService_id = null,
            Person_id = null;

        if (grid && grid.getSelectionModel().getSelection()[0]) {

            var row = grid.getSelectionModel().getSelection()[0];

            electronicTalon_id = row.get('ElectronicTalon_id');
            evnDirection_id = row.get('EvnDirection_id');
            fromElectronicService_id = row.get('fromElectronicService_id');
            Person_id = row.get('Person_id');
            ElectronicTalonStatus_id = row.get('ElectronicTalonStatus_id');

        } else if (ownerWnd.electronicQueueData) {


            electronicTalon_id = ownerWnd.electronicQueueData.electronicTalon_id;
            evnDirection_id = ownerWnd.electronicQueueData.evnDirection_id;
            fromElectronicService_id = ownerWnd.electronicQueueData.fromElectronicService_id;
            ElectronicTalonStatus_id = ownerWnd.electronicQueueData.electronicTalonStatus_id;

        } else { log('FALSE | checkForTalonRedirect()'); return false };

        if (electronicTalon_id) {

            // форма перенаправления талона
            getWnd('swElectronicTalonRedirectEditWindow').show({
                action: "edit",
                current_ElectronicService_id: userMedStaffFact.ElectronicService_id,
                ElectronicTalon_id: electronicTalon_id,
                EvnDirection_pid: evnDirection_id,
                Lpu_id: userMedStaffFact.Lpu_id,
                LpuBuilding_id: userMedStaffFact.LpuBuilding_id,
                LpuSectionProfile_id: userMedStaffFact.LpuSectionProfile_id,
                LpuSection_id: userMedStaffFact.LpuSection_id,
                MedPersonal_id: (userMedStaffFact.MedPersonal_id ? userMedStaffFact.MedPersonal_id : 0),
                From_MedStaffFact_id: (userMedStaffFact.MedStaffFact_id ? userMedStaffFact.MedStaffFact_id: 0),
                pmUser_id: getGlobalOptions().pmuser_id,
                fromElectronicService_id: fromElectronicService_id,
                callback: function(){
                    panel.checkNodeConnAndRefresh();
                    if (params.hideForm && typeof params.hideForm.hide === 'function') {
                        //log('you must see mee!');
                        params.hideForm.hide();
                    }
                },
                showEvnPL: function(params){

                    var emkForm = Ext6.ComponentQuery.query('swPersonEmkWindow')[0];
                    var isEmkVisible = false;

                    if (emkForm) {
                        isEmkVisible = emkForm.isVisible();
                    }

                    if (!params) params = {};

                    if (
                        userMedStaffFact.ARMType === 'stom6'
                        || userMedStaffFact.ARMType === 'stom'
                        || userMedStaffFact.ARMType === 'polka'
                        || userMedStaffFact.ARMType === 'common'
                    ){
                        // проверяем создан ли случай лечения
                        Ext6.Ajax.request({
                            params: {
                                Person_id: Person_id,
                                MedStaffFact_id: userMedStaffFact.MedStaffFact_id
                            },
                            url: '/?c=Common&m=checkIsEvnPLExist',
                            callback: function(options, success, response)
                            {
                                var result = Ext6.util.JSON.decode(response.responseText);
                                if (result && result[0] && result[0].Evn_id) {

                                    panel.onLoadEmkWindow = function(form) {

                                        var objectPrescribe = '';

                                        if (params.MedServiceType_SysNick === 'func') {
                                            objectPrescribe = 'EvnPrescrFuncDiag';
                                        }

                                        if (params.MedServiceType_SysNick === 'pzm') {
                                            objectPrescribe = 'EvnPrescrLabDiag';
                                        }

                                        if (objectPrescribe !== '') {

                                            // показываем окно назначений
                                            if (form.EvnPLStomForm) {
                                                var ctrl = form.EvnPLStomForm.EvnPrescrPanel.getController();
                                                ctrl.openAllUslugaInputWnd({objectPrescribe: objectPrescribe});
                                            }

                                        } else {
                                            log('Данный тип назначений не доступен для перенаправления"');
                                        }

                                        // так как в новой ЭМК случай открывается если создан сегодня
                                        // не будем здесь прописывать дополнительную логику открытия

                                        // form.loadEmkViewPanel({
                                        //     object: result[0].parentEvnClass_SysNick,
                                        //     object_value: result[0].Evn_pid
                                        // });

                                    }
                                } else {

                                    // иначе создаем новый случай
                                    panel.onLoadEmkWindow = function(form){
                                        form.addNewEvnPLAndEvnVizitPL({isStom: (form.userMedStaffFact.ARMType === 'stom6' || form.userMedStaffFact.ARMType === 'stom')});
                                    }
                                }

                                // если ЭМК закрыта
                                if (!isEmkVisible) {

                                    // проверяем принят или нет талон
                                    if (ElectronicTalonStatus_id < 3) {
                                        // примаем талон (ЭМК откроется при принятии)
                                        panel.applyCall();
                                    } else {
                                        // открываем ЭМК
                                        panel.openApplyCallActionWindow();
                                    }

                                } else {
                                    // если окно ЭМК открыто, то просто выполняем функцию
                                    panel.onLoadEmkWindow(emkForm);
                                }
                            }
                        });
                    } else {
                        sw.swMsg.show({

                            msg: "Перенаправления из текущего АРМа на службу в настоящий момент не поддерживаются",
                            title: ERR_INVFIELDS_TIT,
                            icon: Ext.Msg.WARNING,
                            buttons: Ext.Msg.OK,

                            fn: function() {}
                        });
                    }
                }
            });
        }
    },

    // deprecated
    checkForTalonRedirect: function(params){

        if ( typeof params != 'object' ) {params = new Object();}

        var panel = this,
            grid  = panel.ownerGrid,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        var electronicTalon_id = 0,
            isRedirectedTo = 0;

        if (grid && grid.getSelectionModel().getSelection()[0]) {

            var record = grid.getSelectionModel().getSelection()[0];

            electronicTalon_id = record.get('ElectronicTalon_id');
            isRedirectedTo = record.get('EvnDirection_uid') && (userMedStaffFact.ElectronicService_id == record.get('toElectronicService_id'))

        } else if (ownerWnd.electronicQueueData) {

            electronicTalon_id = ownerWnd.electronicQueueData.electronicTalon_id
            isRedirectedTo = ownerWnd.electronicQueueData.isRedirectedTo

        } else {log('FALSE | checkForTalonRedirect()'); return false};

        if (electronicTalon_id && isRedirectedTo){ // показываем только если редирект в этот ПО

        	var talonRedirectParams = {
        		ElectronicTalon_id: electronicTalon_id,
        		currentElectronicService_id: userMedStaffFact.ElectronicService_id,
        		action: 'edit',
        		pmUser_id: getGlobalOptions().pmuser_id,

        		// будет выполнено после закрытия окна "перенаправления талона"
        		callback: function () {
                    if (params.callback && typeof params.callback === 'function') { params.callback(); }
                }
            };

            // форма перенаправления талона
            getWnd('swElectronicTalonRedirectEditWindow').show(talonRedirectParams);

        } else {
            if (params.callback && typeof params.callback === 'function') { params.callback(); }
        }
    },

    // получаем количество символов в числе
    getDigits: function(number) {

        return Math.log(number) * Math.LOG10E + 1 | 0;
    },
    // получаем количество символов в числе
    digitsConverter: function(value) {

        var retValue = value;

        if (value) {

            var digits = this.getDigits(parseInt(value)),
                maxDigitsNum = 4;

            if (digits < maxDigitsNum) {

                for(var i=0; i < maxDigitsNum-digits; i++) {
                    retValue = '0' + retValue;
                }
            }
        }

        return retValue;
    },


    getElectronicQueueGridColumns: function(){

        var panel = this,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        var columns = [

            {
                header: "Повод обращения",
                width: 195,
                sortable: true,
                hidden: true, // регулируется настройкой на ПО
                dataIndex: 'ElectronicTreatment_Name'
            },
            {
                header: "Талон",
                width: 65,
                sortable: true,
                dataIndex: 'ElectronicTalon_Num',
                renderer: function(value, meta, record) {
                    if (record.get('ElectronicTalon_id')) {
                        return '<a title="Показать историю" href="javascript://" onClick="getWnd(\'swElectronicTalonHistoryWindow\').show({ElectronicTalon_id:' + record.get('ElectronicTalon_id') + '})">' + panel.digitsConverter(value) + '</a>';
                    } else return '';
                }
            },
            {
                header: "Статус ЭО",
                width: 100,
                sortable: true,
                dataIndex: 'ElectronicTalonStatus_Name',
                renderer: function(v, p, r) {

                    // если этот ПО = ПО откуда направлен талон
                    if (r.get('EvnDirection_uid') && (userMedStaffFact.ElectronicService_id == r.get('fromElectronicService_id'))) {
                        return '';
                    } else return r.get('ElectronicTalonStatus_Name')
                }
            },
            {
                header: "",
                width: 40,
                sortable: false,
                dataIndex: 'ElectronicTalonStatus',
                renderer: function(v, p, r) {

                    var icon = '';

                    if (r.get('ElectronicService_id') == userMedStaffFact.ElectronicService_id
                        && r.get('ElectronicTalonStatus_id')
                    ) {

                        switch(r.get('ElectronicTalonStatus_id')) {

                            case 2: // Вызван
                                icon = "<img class='electronicQueueGridIcon' src='/img/icons/electronicQueue/call16.png' />";
                                break;
                            case 3: // На обслуживании
                                icon = "<img class='electronicQueueGridIcon' src='/img/icons/electronicQueue/chair.png' />";
                                break;
                        }
                    }

                    // если этот ПО = ПО откуда направлен талон
                    if (r.get('EvnDirection_uid') && (userMedStaffFact.ElectronicService_id == r.get('fromElectronicService_id'))) {
                        icon = "<img class='electronicQueueGridIcon' src='/img/icons/electronicQueue/out.png' />";
                    }

                    // если этот ПО = ПО куда направлен талон
                    if (userMedStaffFact.ElectronicService_id == r.get('toElectronicService_id')) {
                        icon = "<img class='electronicQueueGridIcon' src='/img/icons/electronicQueue/in.png' />";
                    }

                    return icon;
                }
            }
        ];

        return columns;
    },

    getElectronicQueueGridMetadata: function() {
        return [
            { name: 'ElectronicTalon_Num', type: 'string', mapping: 'ElectronicTalon_Num' },
            { name: 'ElectronicTalonStatus_Name', type: 'string', mapping: 'ElectronicTalonStatus_Name' },
            { name: 'ElectronicService_id', type: 'int', mapping: 'ElectronicService_id' },
            { name: 'ElectronicTalonStatus_id', type: 'int', mapping: 'ElectronicTalonStatus_id' },
            { name: 'ElectronicTalon_id', type: 'int', mapping: 'ElectronicTalon_id' },
            { name: 'EvnDirection_uid', type: 'int', mapping: 'EvnDirection_uid' },
            { name: 'toElectronicService_id', type: 'int', mapping: 'toElectronicService_id' },
            { name: 'fromElectronicService_id', type: 'int', mapping: 'fromElectronicService_id' },
            { name: 'ElectronicTreatment_id', type: 'int', mapping: 'ElectronicTreatment_id' },
            { name: 'ElectronicTreatment_Name', type: 'string', mapping: 'ElectronicTreatment_Name' },
            { name: 'pmUser_did', type: 'int', mapping: 'pmUser_did' }, // тот кто записал на бирку
            { name: 'ElectronicTalon_TimeHasPassed', type: 'int', mapping: 'ElectronicTalon_TimeHasPassed' },
            { name: 'TimetableMedService_Date', type: 'date', dateFormat: 'd.m.Y'}
        ];
    },

    reconfigureGrid: function() {

        var panel = this,
            grid = panel.ownerGrid,
            gridColumns = panel.gridColumnConfig;

        if (!panel.gridAlreadyReconfigured) {

            grid.getStore().on('load', function(){panel.filterElectronicQueueRecords();});
            grid.getStore().on('load', function(){panel.initTalonDelayTimers();});

            if (gridColumns) {

                panel.getElectronicQueueGridColumns().forEach(function(col){gridColumns.unshift(col)});
                grid.reconfigure(null, gridColumns);

                if (panel.additionalGridPlugins) panel.initAdditionalGridPlugins(panel.additionalGridPlugins);
                log('grid reconfigured by a new columns');

                panel.gridAlreadyReconfigured = true;
            }
        }
    },

    // обновление состояние панели, с принудительной установкой статуса
    refreshPanel: function(params) {

        var panel = this,
            data = panel.ownerWindow.electronicQueueData;

        if ( typeof params != 'object' ) { params = new Object() }

        if (!params.disabled_state) params.disabled_state = [-1];
        if (!params.buttons) params.buttons = ["electronicQueueFinishAndNext", "electronicQueueFinishReceive", "electronicQueueRedirect"];

        panel.toggleElectronicQueueButtons({
            buttons: params.buttons,
            disabled_state: params.disabled_state,
            personData: data.personData ? data.personData : null,
            nextPersonFin: (data.nextPersonFin) ? data.nextPersonFin : null
        });
    },

    // инициализируем ЭО
    initMainFunctions: function () {

        var panel = this,
            ownerWnd = panel.ownerWindow;

        panel.initCancelCallTimerTask();
        panel.toggleElectronicQueueElements();
        panel.initNodeListeners();

        // автоматическое обновление грида если не доступен НОД
        if (ownerWnd.refreshInterval) {
            clearInterval(ownerWnd.refreshInterval);
            ownerWnd.refreshInterval = null;
        }

        if(!ownerWnd.refreshInterval){
            ownerWnd.refreshInterval = setInterval(function(){

                var activeWin = getActiveWin();

                if (ownerWnd.id == activeWin.id) {
                    if (ownerWnd.socket && !ownerWnd.socket.connected) panel.gridRefresh();
                    else if (!ownerWnd.socket) panel.gridRefresh();
                }

            }.bind(this),panel.refreshTimer);
        }

    },
    initAdditionalGridPlugins: function(plugins) {

        var grid = this.ownerGrid;

        plugins.forEach(function(plugin){
            grid.addPlugin(plugin);
            grid.getPlugin(plugin.pluginId).renderFilters();
        })
    },

    // менеджер инициализации панели (происходит в методе show родительской формы)
    initElectronicQueue: function() {

        var panel = this,
            form = this.electronicQueuePanel,
            ownerWnd = panel.ownerWindow,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;


        // режим работы ЭО по привязке к кабинетам\по ЭО
        this.officesMode = false;
        var eq_options = getElectronicQueueOptions();
        if (eq_options.electronic_queue_direct_link && userMedStaffFact && userMedStaffFact.ARMType) {

            var armtype = userMedStaffFact.ARMType;

            if (parseInt(eq_options.electronic_queue_direct_link) == 2
                && armtype && ['stom6','common','polka'].in_array(armtype)
            ) {
                this.officesMode = true;
            }
        }


        // Если врач связан с пунктом обслуживания электронной очереди, показываем панель работы с ЭО
        if (!Ext6.isEmpty(userMedStaffFact) && !Ext6.isEmpty(userMedStaffFact.ElectronicService_id)) {

            panel.electronicQueueEnable = true;
            var redirectButton = form.lookup('electronicQueueRedirect');
            var btnsWrapper = form.lookup('electronicQueue_buttons');

            var electronicPanelOfficesCombo =  form.lookup('electronicPanelOfficesCombo');
            if (!this.officesMode) {
                electronicPanelOfficesCombo.hide();
            }

            switch(panel.panelType) {

                case 1: // тип панели, стандартный (для АРМов со своим гридом)

                    // если работа ЭО осуществляется через кабинеты
                    // покажем диалог выбора кабинета
                    if (userMedStaffFact.ElectronicQueueInfo_id && this.officesMode) {

                        electronicPanelOfficesCombo.getStore().getProxy().extraParams = {
                            Lpu_id: userMedStaffFact.Lpu_id,
                            LpuBuilding_id: userMedStaffFact.LpuBuilding_id,
                            showOfficeNumPrefix: true
                        }

                        Ext.Ajax.request({
                            url: '/?c=LpuBuildingOfficeMedStaffLink&m=getCurrentOffice',
                            params: {
                                MedStaffFact_id: (userMedStaffFact.MedStaffFact_id) ? userMedStaffFact.MedStaffFact_id : null,
                                MedService_id: (userMedStaffFact.MedService_id) ? userMedStaffFact.MedService_id : null
                            },
                            callback: function (opt, success, response) {

                                changeOfficeParams = {
                                    onClose: function(params){
                                        if (params && params.selectedValue) {

                                            if (electronicPanelOfficesCombo.getStore().getCount() > 0) {
                                                electronicPanelOfficesCombo.setValue(params.selectedValue);
                                            } else {
                                                electronicPanelOfficesCombo.getStore().load({
                                                    callback: function() {
                                                        electronicPanelOfficesCombo.setValue(params.selectedValue);
                                                    }
                                                });
                                            }
                                        }
                                    }
                                };

                                if (success && response.responseText.length > 0) {

                                    var result = Ext6.util.JSON.decode(response.responseText);
                                    if (result && result[0] && result[0].LpuBuildingOffice_id) {

                                        electronicPanelOfficesCombo.getStore().load({
                                            callback: function() {
                                                electronicPanelOfficesCombo.setValue(result[0].LpuBuildingOffice_id);
                                            }
                                        });

                                        changeOfficeParams.askBeforeShow = true;
                                        panel.showChangeDoctorRoomDialog(changeOfficeParams);

                                    } else {
                                        panel.showChangeDoctorRoomDialog(changeOfficeParams);
                                    }
                                } else {
                                    panel.showChangeDoctorRoomDialog(changeOfficeParams);
                                }
                            }
                        });

                    }

                    // ставим все галочки включенными
                    panel.showOnlyActive = true;
                    panel.showLiveQueue = true;

                    // переконфигурируем грид для работы с ЭО
                    panel.reconfigureGrid();
                    panel.initMainFunctions();

                    if (panel.redirection != undefined && panel.redirection == false) {
                        redirectButton.ownerCt.hide();
                        btnsWrapper.addCls('condensed');
                    }

                    if (panel.floating)
                        panel.alignTo(ownerWnd, 'bl', [300, -70]); // позиция внизу

                    break;

                case 2: // тип панели, урезанный (для форм без грида, окон)

                    if (ownerWnd.electronicQueueData) {

                        var data = ownerWnd.electronicQueueData;
                        if  (data.eqPanelState) {

                            form.lookup('electronicQueue_recorded').setValue(data.eqPanelState.recorded),
                            form.lookup('electronicQueue_serviced').setValue(data.eqPanelState.serviced),
                            form.lookup('electronicQueue_away').setValue(data.eqPanelState.away)
                        }

                        if (data.senderPanel.redirection != undefined && data.senderPanel.redirection == false) {
                            redirectButton.ownerCt.hide();
                            btnsWrapper.addCls('condensed');
                        }

                        panel.toggleElectronicQueueButtons({
                            buttons: ["electronicQueueFinishAndNext", "electronicQueueFinishReceive", "electronicQueueRedirect"],
                            disabled_state: (data.electronicTalonStatus_id && data.electronicTalonStatus_id == 3) ? [0] : [-1],
                            personData: data.personData ? data.personData : null,
                            nextPersonFin: (data.nextPersonFin) ? data.nextPersonFin : null
                        });

                        panel.senderData = ownerWnd.electronicQueueData;
                         // позиция внизу
                    }
                    break;

                // тип панели "особый", использует свой грид без привязки к биркам
                // на кнопках номера талонов вместо фамилий
                // так же доп. элемент на панели (комбик с номерами талонов)
                case 3:

                    form.lookup('electronicQueue_recorded').hideContainer();

                    panel.initMainFunctions();
                    panel.gridRefresh();

                    panel.alignTo(ownerWnd, 'bl', [300, -70]); // позиция внизу
                    break;
            }

        } else {

            switch(panel.panelType) {

                case 1:
                case 3:

                    if (panel.additionalGridPlugins) panel.initAdditionalGridPlugins(panel.additionalGridPlugins);

                    panel.toggleElectronicQueueElements({
                        dontShow: true,
                        layoutPanelId: panel.layoutPanelId
                    });
                    break;

                case 2:
                    panel.hide();
                    //if (ownerWnd.btnsPanel) ownerWnd.btnsPanel.show();

                    //ownerWnd.syncSize();
                    //ownerWnd.doLayout();
                    break;
            }
        }

    },

    initStandardGrid: function() {

        var panel = this;

        // добавим доп класс
        panel.cls += ' eq-panelType3 ';

        // грид этот будет не видимый, но необходим для работы с ЭО когда нет бирок
        // в любой момент его можно раскрыть, если нужно
        panel.ElectronicQueueGrid = new Ext.grid.GridPanel({
            region: panel.gridRegion,
            height: 150,
            hidden: panel.hideDirectQueueGrid,
            title: 'Электронная очередь',
            tbar: new Ext.Toolbar({
                id: 'ElectronicQueueGridToolbar',
                items:
                    [
                        new Ext.Action({
                                name:'refresh',
                                text:BTN_GRIDREFR,
                                tooltip: BTN_GRIDREFR,
                                iconCls : 'x-btn-text',
                                icon: 'img/icons/refresh16.png',
                                handler: function() {panel.gridRefresh()}
                            }
                        )
                    ]
            }),
            store: new Ext.data.GroupingStore({
                reader: new Ext.data.JsonReader({
                    id: 'ElectronicTalon_id'
                }, [
                    {name: 'ElectronicTalon_id'},
                    {name: 'Person_id'},
                    {name: 'Person_FIO'},
                    {name: 'Server_id'},
                    {name: 'PersonEvn_id'},
                    {name: 'ElectronicTalon_Time'},
                    {name: 'ElectronicTalon_ProcessedTime'},
                    {
                        name: 'ElectronicTalon_Date',
                        type: 'date',
                        dateFormat: 'd.m.Y'
                    },
                    {name: 'pmUser_Name'},
                    {name: 'EvnDirection_id' },
                    {name: 'EvnDirection_uid' },
                    {name: 'EvnDirection_uid' },
                    {name: 'ElectronicTalon_Num' },
                    {name: 'ElectronicTalonStatus_Name' },
                    {name: 'ElectronicService_id' },
                    {name: 'ElectronicTalonStatus_id' },
                    {name: 'toElectronicService_id'},
                    {name: 'fromElectronicService_id'},
                    {name: 'ElectronicTreatment_id'},
                    {name: 'ElectronicTreatment_Name'}
                ]),
                autoLoad: false,
                url: C_EQ_TALONLIST,
                sortInfo:
                {
                    field: 'ElectronicTalon_Num',
                    direction: 'ASC'
                },
                groupField: 'ElectronicTalon_Date',
                listeners: {
                    load: function(){
                        panel.refreshElectronicTalonCombo();
                        panel.filterElectronicQueueRecords();
                    }
                }
            }),
            loadMask: true,
            collapsible: true,
            collapsed: true,
            stripeRows: true,
            columns: [
                {header: "id",hidden: true,hideable: false,dataIndex: 'ElectronicTalon_id'},
                {header: "Талон",width: 65,sortable: true, dataIndex: 'ElectronicTalon_Num',
                    renderer: function(value, meta, record) {
                        return panel.digitsConverter(value);
                    }
                },
                {header: "Повод обращения",width: 195,sortable: true,hidden: true, dataIndex: 'ElectronicTreatment_Name'},
                {header: "Клиент",width: 300,hideable: false,dataIndex: 'Person_FIO'},
                {header: "Статус ЭО",width: 150,sortable: true,dataIndex: 'ElectronicTalonStatus_Name'},
                {header: "Зарегистрирован",width: 100,sortable: true,dataIndex: 'ElectronicTalon_Time'},
                {header: "Обработан", width: 100,sortable: true, dataIndex: 'ElectronicTalon_ProcessedTime',
                    renderer: function(v, p, r) {

                        var time = '';

                        if (r.get('ElectronicTalonStatus_id') == 4 ||  r.get('ElectronicTalonStatus_id') == 5)
                            time = r.get('ElectronicTalon_ProcessedTime');

                        return time;
                    }
                },
                {header: "sid",hidden: true,hideable: false,dataIndex: 'Server_id'},
                {header: "Статус ЭО(id)",width: 100,sortable: true,hidden: true,dataIndex: 'ElectronicTalonStatus_id'},
                {header: langs('Дата'),width: 150,sortable: true,dataIndex: 'ElectronicTalon_Date',renderer: Ext.util.Format.dateRenderer('d.m.Y')},
                {header: 'Оператор',width: 200,sortable: true,dataIndex: 'pmUser_Name'},
                {header: "EvnDirection_id",hidden: true,hideable: false,dataIndex: 'EvnDirection_id'}
            ],

            loadStore: function(params, callback)
            {
                var grid = panel.ElectronicQueueGrid;
                if (!this.params) this.params = null;

                if (params){

                    this.params = params;
                    panel.setElectronicQueueLoadStoreParams();
                }

                if (!panel.ownerWindow.refreshInterval) {grid.loadMask.show();}
                grid.getStore().baseParams = this.params;

                Ext6.Ajax.request({
                    params: this.params,
                    url: C_EQ_TALONLIST,
                    callback: function(options, success, response)
                    {
                        grid.loadMask.hide();
                        if (success)
                        {
                            // запоминаем скролл
                            var scrollState = grid.getView().getScrollState();
                            var response_obj = Ext.util.JSON.decode(response.responseText);

                            grid.getStore().loadData(response_obj);

                            grid.getView().restoreScroll(scrollState);
                            if (callback && typeof callback == 'function'){callback()}
                        }
                    }
                });
            },
            clearStore: function()
            {
                if (this.getEl())
                {
                    if (this.getTopToolbar().items.last())
                        this.getTopToolbar().items.last().el.innerHTML = '0 / 0';
                    this.getStore().removeAll();
                }
            },
            sm: new Ext.grid.RowSelectionModel({ singleSelect: true }),
            view: new Ext.grid.GridView({})
        });

        // присвоим наш особый грид, в грид с которым будет работать панель
        panel.ownerGrid = panel.ElectronicQueueGrid;
        panel.gridRefreshFn = function(params){panel.electronicQueueGridRefresh(params)};

    },

    initTalonDelayTimers: function () {

        var panel = this;
        var store = panel.ownerGrid.getStore();

        if (store) {
            store.each(function(row) {
                // включаем таймер только если талон в ожидании
                if (row.get('ElectronicTalonStatus_id') == 1) {
                    panel.startTalonDelayTimer(row);
                } else panel.clearTalonDelayTimer(row);
            })
        }
    },

    startTalonDelayTimer: function (record) {

        var panel = this;

        var userMedStaffFact = panel.ownerWindow.userMedStaffFact;
        var passTimeMaxValue = userMedStaffFact.ElectronicQueueInfo_PersCallDelTimeMin;

        // проверяем время отсрочки вызова
        if (!Ext.isEmpty(passTimeMaxValue) && passTimeMaxValue > 0) {

            passTimeMaxValue = 60*passTimeMaxValue; // приведём к секундам
            var talonTimePassed = record.get('ElectronicTalon_TimeHasPassed');

            // если время отсрочки не пройдено, помечаем запись как отсроченная
            if (talonTimePassed <= passTimeMaxValue) {

                // время из БД до окончания отсрочки
                var timeToEnd = passTimeMaxValue-talonTimePassed;

                // всякий раз при обновлении грида,
                // обновим таймеры отсчета отсрочки для этой записи

                // включаем таймер для записи если не включен
                var timerEnable = false;
                panel.delayTimers.some(function(timer, i){

                    if (timer.etid == record.get('ElectronicTalon_id')) {
                        timerEnable = true;
                        return true; //break;
                    }
                })

                //log('timerEnable', timerEnable);

                if (!timerEnable) {

                    log('beginTimerFor ', record.get('ElectronicTalon_id'))

                    var timerId = setInterval(function() {

                        timeToEnd--;
                        //log('timeToEnd', timeToEnd);

                        if (timeToEnd >= 0) {

                            var store = panel.ownerGrid.getStore();
                            if (store) {

                                var r_idx = store.find('ElectronicTalon_id', record.get('ElectronicTalon_id'));

                                //log('timer, r_idx', r_idx)

                                if (r_idx !== undefined && r_idx != -1) {

                                    var storeRecord = store.getAt(r_idx);
                                    if (storeRecord) {
                                        //log('timer, record', record)

                                        var timeInSec = timeToEnd;

                                        var minutes = 0,
                                            seconds = '00',
                                            prefix = '';

                                        var ostatok = timeInSec % 60;
                                        if (ostatok) {

                                            minutes = Math.floor(timeInSec/60);
                                            seconds = timeInSec - minutes*60;

                                            if (seconds < 10) prefix = '0';

                                        } else minutes = timeInSec/60;

                                        storeRecord.set('ElectronicTalonStatus_Name', minutes+':'+prefix+seconds);
                                        //storeRecord.set('ElectronicTalonStatus_Name', record.get('ElectronicTalon_id'));
                                        storeRecord.commit(true); // true - не сообщаем стору об изменении
                                    } else {
                                        clearInterval(timerId);
                                        storeRecord.set('ElectronicTalonStatus_Name', "Ожидает");
                                        storeRecord.commit(true); // true - не сообщаем стору об изменении
                                    }
                                }
                            }
                        }
                        //log('timer tick');

                    }, 1000);

                    panel.delayTimers.push({
                        etid: record.get('ElectronicTalon_id'),
                        tid: timerId
                    })
                }

            } else { // если пройдено отключаем таймер если включен
                panel.clearTalonDelayTimer(record);
            }
        }
    },

    clearTalonDelayTimer: function (record){

        var panel = this, removeObject = null;

        // выключаем таймер для записи если включен
        panel.delayTimers.some(function(timer, i){

            if (timer.etid == record.get('ElectronicTalon_id')) {
                removeObject = {tid:timer.tid, idx: i, etid: timer.etid};
                log('talon for clear timer finded', removeObject);
                return true; // break;
            }
        })

        // удаляем из массива
        if (removeObject) {
            log('clearTimerFor ', removeObject)
            clearInterval(removeObject.tid);
            panel.delayTimers.splice(removeObject.idx, 1);
            log('timers after cleaning', panel.delayTimers);
        }

    },

    reconfigureGridViewRowClass :function() {

        var panel = this;

        if (panel.ownerGrid) {

            log('reconfigureGridViewRowClass');
            // связываем клик на строку с функционалом смены кнопок ЭО
            panel.ownerGrid.getSelectionModel().on('select', function (){panel.onRowSelectElectronicQueue()});

            var gridViewGetRowClass = '';

            // раширяем getRowClass
            if (panel.gridPanel) {

                gridViewGetRowClass = (panel.ownerGrid.getView().getRowClass && panel.ownerGrid.getView().getRowClass === 'function' ? panel.ownerGrid.getView().getRowClass : '')
                panel.ownerGrid.getView().getRowClass = function(row, index) {

                    var cls = '' + (typeof gridViewGetRowClass === 'function' ? gridViewGetRowClass(row, index) : '');

                    // меняем класс только если талон в ожидании (для отсрочки)
                    if (row.get('ElectronicTalonStatus_id') == 1) {

                        var userMedStaffFact = panel.ownerWindow.userMedStaffFact;
                        var passTimeMaxValue = userMedStaffFact.ElectronicQueueInfo_PersCallDelTimeMin;

                        // проверяем время отсрочки вызова
                        if (!Ext.isEmpty(passTimeMaxValue) && passTimeMaxValue > 0) {

                            passTimeMaxValue = 60*passTimeMaxValue; // приведём к секундам
                            var talonTimePassed = row.get('ElectronicTalon_TimeHasPassed');

                            log('passTimeMaxValue', passTimeMaxValue);
                            log('talonTimePassed', talonTimePassed);

                            // если время отсрочки не пройдено, помечаем запись как отсроченная
                            if (talonTimePassed <= passTimeMaxValue) {
                                cls = cls + ' x-grid-rowgray eq-call-delay';
                            }
                        }
                    }

                    // если есть направление из этого ПО, или принял другой врач, делаем серым
                    if (row.get('EvnDirection_uid') && (panel.ownerWindow.userMedStaffFact.ElectronicService_id == row.get('fromElectronicService_id'))) {
                        cls = cls + ' x-grid-rowgray ';
                    }

                    return cls;
                }

            }
        }
    },

    showChangeDoctorRoomDialog: function(params){

        var panel = this,
            userMedStaffFact = panel.ownerWindow.userMedStaffFact;

        if (!params) params = {};

        var wndParams = {
            Lpu_id: userMedStaffFact.Lpu_id,
            MedStaffFact_id: (userMedStaffFact.MedStaffFact_id) ? userMedStaffFact.MedStaffFact_id : null,
            MedService_id: (userMedStaffFact.MedService_id) ? userMedStaffFact.MedService_id : null,
            LpuBuilding_id: userMedStaffFact.LpuBuilding_id,
            onClose: params.onClose ? params.onClose : null,
            selected_LpuBuildingOffice_id: params.selected_LpuBuildingOffice_id ? params.selected_LpuBuildingOffice_id : null,
            lastSelected_LpuBuildingOffice_id:  params.lastSelected_LpuBuildingOffice_id ? params.lastSelected_LpuBuildingOffice_id : null,
            lastSelected_LpuBuildingOffice_Number: params.lastSelected_LpuBuildingOffice_Number ? params.lastSelected_LpuBuildingOffice_Number : null
        };

        if (params.askBeforeShow) {
            sw.swMsg.show({
                title: 'Кабинет приема',
                icon: Ext.Msg.WARNING,
                msg: "Изменить кабинет или время приема?",
                buttons: {yes: 'Да', no: 'Нет'},
                fn: function(btn){
                    if (btn == 'no') return false;
                    getWnd('swChangeDoctorRoomWindow').show(wndParams);
                }
            });
        } else {
            getWnd('swChangeDoctorRoomWindow').show(wndParams);
        }
    },

    initComponent: function() {

        var panel = this;

        this.electronicQueuePanel = Ext6.create('Ext.panel.Panel', {

                id: 'ElectronicQueuePanel_' + panel.ownerWindow.id,
                region: 'center',
                frame: false,
                border: false,
                height: panel.height,
                referenceHolder: true,
                items:[
                    {
                        cls: 'electronicQueue_container',
                        layout: 'column',
                        border: false,
                        // колонки
                        items:[
                            {
                                // заголовок
                                layout: 'form',
                                border: false,
                                items:
                                    [{
                                        xtype: 'label',
                                        text: 'ЭЛЕКТРОННАЯ ОЧЕРЕДЬ',
                                        width: 120,
                                        cls: 'electronicQueue_title',
                                    },
                                        {  // комбо бокс для выбора кабинета
                                            hidden: false,
                                            width: 110,
                                            margin: 0,
                                            maxHeight: 32,
                                            layout: 'column',
                                            cls: 'OfficesCombo_custome_height',
                                            border: false,
                                            items: [{
                                                width: 110,
                                                reference: 'electronicPanelOfficesCombo',
                                                ctCls: 'electronicPanelOfficesCombo',
                                                xtype: 'combo',
                                                height: 13,
                                                mode: 'local',
                                                hiddenName: 'LpuBuildingOffice_id',
                                                displayField: 'LpuBuildingOffice_Number',
                                                valueField: 'LpuBuildingOffice_id',
                                                allowBlank: true,
                                                store:  Ext6.create('Ext.data.Store', {
                                                    proxy: {
                                                        type: 'ajax',
                                                        url: '/?c=LpuBuildingOffice&m=loadLpuBuildingOfficeCombo',
                                                        reader: {
                                                            type: 'json'
                                                        }
                                                    },
                                                    key: 'LpuBuildingOffice_id',
                                                    autoLoad: false,
                                                    fields: [
                                                        {name: 'LpuBuildingOffice_id', type:'int'},
                                                        {name: 'LpuBuildingOffice_Number', type:'string'},
                                                        {name: 'LpuBuildingOffice_Comment', type:'string'},
                                                        {name: 'LpuBuildingOffice_Name', type:'string'}
                                                    ]
                                                }),
                                                triggerAction: 'all',
                                                editable: false,
                                                labelSeparator: '',
                                                labelWidth : 5,
                                                listeners: {
                                                    'select': function(combo, rec) {
                                                        panel.showChangeDoctorRoomDialog({
                                                            selected_LpuBuildingOffice_id: rec.get('LpuBuildingOffice_id'),
                                                            lastSelected_LpuBuildingOffice_id:  panel.lastSelected_LpuBuildingOffice_id ? panel.lastSelected_LpuBuildingOffice_id : null,
                                                            onClose: function(params) {
                                                                if (params && params.selectedValue) {
                                                                    combo.setValue(params.selectedValue);
                                                                }
                                                            }
                                                        });
                                                    },
                                                    'beforeselect': function(combo, rec) {
                                                    },
                                                    // хренатень чтобы не показывался верт. курсор при выборе значения
                                                    'expand': function(combo) {

                                                        panel.lastSelected_LpuBuildingOffice_id = combo.getValue();

                                                        var blurField = function(el) {
                                                            el.blur();
                                                        }
                                                        blurField.defer(10,this,[combo.el]);
                                                    },
                                                    // хренатень чтобы не показывался верт. курсор при выборе значения
                                                    'collapse': function(combo) {
                                                        var blurField = function(el) {
                                                            el.blur();
                                                        }
                                                        blurField.defer(10,this,[combo.el]);
                                                    }
                                                },
                                                tpl: new Ext6.XTemplate(
                                                    '<tpl for=".">',
                                                    '<div class="x6-boundlist-item">',
                                                    '{LpuBuildingOffice_Number}',
                                                    '</div></tpl>'
                                                )
                                            }]}
                                    ]
                            },
                            {
                                layout: 'column',
                                border: false,
                                cls: 'electronicQueue_buttons',
                                reference: 'electronicQueue_buttons',
                                items:
                                    [
                                        {  // комбо бокс для панели с типом 3
                                        hidden: (panel.panelType != 3),
                                        width: 110,
                                        layout: 'column',
                                        items: [{
                                            width: 110,
                                            reference: 'electronicTalonCombo',
                                            ctCls: 'electronicTalonCombo',
                                            xtype: 'combo',
                                            mode: 'local',
                                            hiddenName: 'ElectronicTalon_id',
                                            displayField: 'ElectronicTalon_Num',
                                            valueField: 'ElectronicTalon_id',
                                            allowBlank: false,
                                            store: Ext6.create('Ext.data.ArrayStore', {
                                                key: 'ElectronicTalon_id',
                                                autoLoad: true,
                                                fields: [
                                                    {name:'ElectronicTalon_id', type:'int'},
                                                    {name:'ElectronicTalon_Num', type:'string'},
                                                    {name:'ElectronicQueueGrid_Index', type:'int'},
                                                    {name:'ElectronicTalonStatus_id', type:'int'}
                                                ]
                                            }),
                                            triggerAction: 'all',
                                            editable: false,
                                            labelSeparator: '',
                                            labelWidth : 5,
                                            listeners: {
                                                'select': function(combo) {
                                                    if (panel.ElectronicQueueGrid.store.getCount() > 0) {
                                                        panel.ElectronicQueueGrid.getSelectionModel().select(combo.getFieldValue('ElectronicQueueGrid_Index'));
                                                    }
                                                },
                                                'render': function(combo) {
                                                    //combo.hide();
                                                    //if (combo.store && !combo.store.getCount()) {
                                                    //	combo.hide();
                                                    //}
                                                },
                                                // хренатень чтобы не показывался верт. курсор при выборе значения
                                                'expand': function(combo) {
                                                    var blurField = function(el) {
                                                        el.blur();
                                                    }
                                                    blurField.defer(10,this,[combo.el]);
                                                },
                                                // хренатень чтобы не показывался верт. курсор при выборе значения
                                                'collapse': function(combo) {
                                                    var blurField = function(el) {
                                                        el.blur();
                                                    }
                                                    blurField.defer(10,this,[combo.el]);
                                                }
                                            },
                                            tpl: new Ext.XTemplate(
                                                '<tpl for=".">',
                                                '<div class="x-combo-list-item custom_combo_width ' + '{[(values.ElectronicTalonStatus_id == 2 || values.ElectronicTalonStatus_id == 3) ? "wide-combo-text" : "" ]}' + '">',
                                                '<span class="eq-direct-combo-status ' + '{[((values.ElectronicTalonStatus_id == 2) ? "call" : ((values.ElectronicTalonStatus_id == 3) ? "apply" : "" ))]}' + '">',
                                                '</span>',
                                                '{ElectronicTalon_Num}',
                                                '</div></tpl>'
                                            )
                                        }]
                                    },
                                        {
                                            // первая кнопка
                                            layout: 'form',
                                            border: false,
                                            items:
                                                [
                                                    new Ext6.Button(
                                                        {
                                                            cls: 'electronicQueueCall',
                                                            reference: 'electronicQueueCall',
                                                            iconCls:'eq-call eq-no-pos',
                                                            disabled: false,
                                                            textAlign: 'left',
                                                            text: 'Вызвать',
                                                            hidden: true,
                                                            handler: function() { panel.doCall(); }
                                                        }),
                                                    new Ext6.Button(
                                                        {
                                                            cls: 'electronicQueueCancelCall',
                                                            reference: 'electronicQueueCancelCall',
                                                            iconCls:'eq-cancelCall eq-no-pos',
                                                            textAlign: 'left',
                                                            disabled: false,
                                                            text: 'Отменить вызов',
                                                            hidden: true,
                                                            handler: function(){ panel.cancelCall(); }
                                                        }),
                                                    new Ext6.Button(
                                                        {
                                                            cls: 'electronicQueueFinishAndNext',
                                                            reference: 'electronicQueueFinishAndNext',
                                                            iconCls:'eq-finishAndNext eq-no-pos',
                                                            textAlign: 'left',
                                                            disabled: false,
                                                            text: 'Завершить и вызвать<br> следующего',
                                                            hidden: true,
                                                            handler: function() {
                                                                // если панель второго типа подменяем основное действие по кнопке
                                                                if (panel.panelType == 2) {

                                                                    // определяем панель первого типа (ту что открыла окно)
                                                                    var senderPanel = (panel.ownerWindow.electronicQueueData)
                                                                        ? panel.ownerWindow.electronicQueueData.senderPanel
                                                                        : '' ;

                                                                    if (senderPanel && panel.completeServiceActionFn && typeof panel.completeServiceActionFn === 'function')
                                                                        panel.completeServiceActionFn({callback: function(){senderPanel.finishReceive({callNext: true});}})


                                                                } else panel.checkForTalonRedirect({callback: function(){panel.finishReceive({callNext: true})}});
                                                            }
                                                        }),
                                                ]
                                        },
                                        {
                                            // вторая кнопка
                                            layout: 'form',
                                            border: false,
                                            items:
                                                [
                                                    new Ext6.Button(
                                                        {
                                                            cls: 'electronicQueueReceive',
                                                            reference: 'electronicQueueReceive',
                                                            iconCls:'eq-chair eq-no-pos',
                                                            text: 'Принять',
                                                            disabled: false,
                                                            textAlign: 'left',
                                                            hidden: true,
                                                            handler: function() { panel.applyCall(); }
                                                        }),
                                                    new Ext6.Button(
                                                        {
                                                            cls: 'electronicQueueFinishReceive',
                                                            reference: 'electronicQueueFinishReceive',
                                                            iconCls:'eq-finishReceive eq-no-pos',
                                                            disabled: false,
                                                            hidden: true,
                                                            text: 'Завершить прием',
                                                            textAlign: 'left',
                                                            handler: function() {
                                                                // если панель второго типа подменяем основное действие по кнопке
                                                                if (panel.panelType == 2) {

                                                                    // определяем панель первого типа (ту что открыла окно)
                                                                    var senderPanel = (panel.ownerWindow.electronicQueueData)
                                                                        ? panel.ownerWindow.electronicQueueData.senderPanel
                                                                        : '' ;

                                                                    log('senderPanel', senderPanel);

                                                                    if (senderPanel
                                                                        && panel.completeServiceActionFn
                                                                        && typeof panel.completeServiceActionFn === 'function'
                                                                    ) {

                                                                        log('senderpanel call finishReceive')
                                                                        panel.completeServiceActionFn({
                                                                            callback: function (params) {
                                                                                senderPanel.finishReceive(params);
                                                                            }
                                                                        })
                                                                    }

                                                                } else panel.checkForTalonRedirect({callback: function(){panel.finishReceive()}});
                                                            }
                                                        })
                                                ]
                                        },
                                        {
                                            layout: 'form',
                                            border: false,
                                            items: [
                                                new Ext6.Button({  // третья кнопка "перенаправить"
                                                    cls: 'electronicQueueRedirect',
                                                    reference: 'electronicQueueRedirect',
                                                    iconCls:'eq-redirect eq-redirect6',
                                                    text: 'Перенаправить',
                                                    textAlign: 'left',
                                                    disabled: false,
                                                    hidden: true,
                                                    handler: function() {

                                                        // если панель второго типа подменяем основное действие по кнопке
                                                        if (panel.panelType == 2) {

                                                            // определяем панель первого типа (ту что открыла окно)
                                                            var senderPanel = (panel.ownerWindow.electronicQueueData)
                                                                ? panel.ownerWindow.electronicQueueData.senderPanel
                                                                : '' ;

                                                            if (senderPanel && panel.completeServiceActionFn
                                                                && typeof panel.completeServiceActionFn === 'function'
                                                            ) {
                                                                panel.completeServiceActionFn({
                                                                    callback: function(){
                                                                        senderPanel.redirectElectronicTalon(
                                                                            {
                                                                                hideForm: panel.ownerWindow
                                                                            }
                                                                        );
                                                                        log('completeServiceAction callback')
                                                                    }
                                                                })
                                                            } else log('no sender panel or callbackFn!')
                                                        } else panel.redirectElectronicTalon();

                                                    }
                                                })
                                            ]
                                        }
                                    ]
                            },
                            {
                                // состояние
                                layout: 'form',
                                border: false,
                                cls: 'electronicQueue_state_container',
                                labelWidth: 5,
                                items:
                                    [
                                        {
                                            hidden: panel.panelType == 3,
                                            xtype: 'textfield',
                                            fieldLabel: 'Записаны',
                                            disabled: true,
                                            reference: 'electronicQueue_recorded',
                                            width: 50
                                        },
                                        {
                                            xtype: 'textfield',
                                            fieldLabel: 'Обслужены',
                                            disabled: true,
                                            reference: 'electronicQueue_serviced',
                                            width: 50
                                        },
                                        {
                                            xtype: 'textfield',
                                            fieldLabel: 'Ожидают',
                                            disabled: true,
                                            reference: 'electronicQueue_away',
                                            width: 50
                                        }
                                    ]
                            },
                            {
                                // чекбокс (только очередь)
                                layout: 'form',
                                border: false,
                                labelWidth: 140,
                                cls: 'electronicQueue_checkColumn eq-x6',
                                hidden: panel.panelType.toString().inlist([2,3]),
                                items: [{
                                    xtype: 'checkboxfield',
                                    width: 160,
                                    labelSeparator: '',
                                    cls: 'electronicQueue_isOnlyElectronicQueue',
                                    //fieldLabel: 'Только очередь',
                                    boxLabel: 'Только очередь',
                                    checked: true,
                                    listeners: {
                                        'change': function(field, value) {
                                            panel.showOnlyActive = (value ? true : false);
                                            panel.gridRefresh();
                                        }
                                    }
                                }, {
                                    // чекбокс (живая очередь)
                                    xtype: 'checkbox',
                                    width: 160,
                                    labelSeparator: '',
                                    cls: 'electronicQueue_isOnlyLiveElectronicQueue',
                                    //fieldLabel: 'Все по поводам',
                                    boxLabel: 'Все по поводам',
                                    checked: true,
                                    listeners: {
                                        'change': function(field, value) {
                                            panel.showLiveQueue = (value ? true : false);
                                            panel.gridRefresh();
                                        }
                                    }
                                }]
                            }
                        ]
                    }
                ]
            }
        );

        // для панели с типом 3 (очередь без бирок), создадим собственный грид
        if (panel.panelType == 3) panel.initStandardGrid();

        // переопределим класс TR для строки грида
        panel.reconfigureGridViewRowClass();

        Ext6.apply(this, {layout: 'border', items: [panel.electronicQueuePanel]});
        this.callParent(arguments);
    },

    /*
        тип панели (отображение элементов):

        1 - полная панель, связанная с гридом АРМа, подстрочник на кнопках "ФИО" * по умолчанию
        2 - панель урезанная, связанная с текущей записью, без фильтров
        3 - особая панель, с особым гридом, комбо с талонами, подстрочник на кнопках "номер талона"

     */
    panelType: 1,

    ownerWindow:null, // окно к которому подключена панель ЭО
    ownerGrid:null, // грид с которым будет работать функционал ЭО
    storeInitObject: null,
    gridColumnConfig: null,
    gridPanel: null,
    applyCallActionFn: Ext.emptyFn, // функция отвечающая за открытие окна после нажатия "Принять" (эмка или окно результатов)
    completeServiceActionFn: null, // функция отвечающая за действия после нажатия завершить прием (в арме просмотра результатов например)

    refreshTimer: 15000, // таймер обновления грида, если НОД отключен
    cls: 'electronicQueuePanel',
    height: 56,
    width: 1280,
    border: false,
    frame: false,

    electronicQueueCancelCallTimer: 30, // настройка на очереди, по умолчанию 30
    timerActivated: false,
    cancelCallTimerRunner: null,
    cancelCallTimerTask: null,
    calledRecord: null,
    electronicQueueData: null,

    showOnlyActive: false, // флаг показать только активные (для электронной очереди)
    showLiveQueue: false,  // флаг показать все бирки без записи с типом "живая очередь"

    hasOwnProperty: Object.prototype.hasOwnProperty, // Speed up calls to hasOwnProperty
    layoutPanelId: null,
    gridRegion: 'north', // расположение грида для панели с типом 3 (по умолчанию)
    hideDirectQueueGrid: true, // скрывать по умолчанию этот грид для типа 3
    lastSelectedRecord: null,// признак того что мы не будем фильтровать по пункту обслуживания
    gridAlreadyReconfigured: false,
    electronicQueueEnable: false,
    delayTimers: [{etid: 0, tid: 0}],
    redirection: true
    //floating: true, // плавающая панель
    //draggable: true,
    //shadow: true
});