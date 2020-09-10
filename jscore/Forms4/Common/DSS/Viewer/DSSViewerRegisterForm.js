/**
 * Страница клинического регистра
 *
 * swDSSViewerWindow - окно для просмотра данных регистров
 *
 * DSS - сбор структурированной медицинской информации и поддержка принятия решений
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.DSS
 * @access       public
 * @copyright    Copyright (c) 2018-2019 Swan Ltd.
 * @author       Yaroslav Mishlanov <ya.mishlanov@swan-it.ru>
 * @since        12.12.2018
 * @version      01.07.2019
 */
Ext6.define('common.DSS.Viewer.DSSViewerRegisterForm', {


    /**
     * Получить список всех участков МО
     *
     * @param {object} data: {diagModule, register}
     * @param {ExtComponent} loadMask
     * @param {function} onSuccess
     * @param {function} onFailure
     */
    _getAllLpuRegions: function(data, loadMask, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=Common&m=loadLpuRegionList',

            params: {
                add_without_region_line: false
            },

            success: function(response, opts) {
                var lpuRegions;
                var registerStructure;

                loadMask.hide();
                lpuRegions = JSON.parse(response.responseText);

                if (lpuRegions.success === false) {
                    onFailure(data.Error_Msg);
                    return;
                }

                onSuccess(lpuRegions);
            },

            failure: function(response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Получить доступные врачу участки МО
     *
     * @param {object} data: {diagModule, register}
     * @param {ExtComponent} loadMask
     * @param {function} onSuccess
     * @param {function} onFailure
     */
    _getLpuRegions: function(data, loadMask, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=LpuRegion&m=getMedPersLpuRegionList',

            params: {
                Lpu_id: data.Lpu_id,
                MedPersonal_id: data.MedPersonal_id,
                MedStaffFact_id: data.MedStaffFact_id
            },

            success: function(response, opts) {
                var lpuRegions;
                var registerStructure;

                loadMask.hide();
                lpuRegions = JSON.parse(response.responseText);

                if (lpuRegions.success === false) {
                    onFailure(data.Error_Msg);
                    return;
                }

                onSuccess(lpuRegions);
            },

            failure: function(response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Получить структуру регистра (шаблон)
     *
     * @param {object} data: {diagModule, register}
     * @param {ExtComponent} loadMask
     * @param {function} onSuccess
     * @param {function} onFailure
     */
    _getRegisterStructure: function(data, loadMask, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSViewer&m=getRegisterStructure',

            params: {
                moduleId: data.moduleData.moduleId,
                moduleName: data.moduleData.moduleName,
                registerId: data.registerData.registerId,
                registerName: data.registerData.registerName
            },

            success: function(response, opts) {
                var data;
                var registerStructure;

                loadMask.hide();
                data = JSON.parse(response.responseText);
                if (data.success === false) {
                    onFailure(data.Error_Msg);
                    return;
                }
                registerStructure = data;// хук для нормального отображения имён полей
                if (registerStructure.error) {
                    onFailure(registerStructure.error);
                    return;
                }
                if (registerStructure[0] === 'empty') {
                    // хук для передачи пустого списка
                    onSuccess([]);
                    return;
                }
                onSuccess(registerStructure);
            },

            failure: function(response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Получить файл регистра (экспорт)
     *
     * @param {object} data: {diagModule, register}
     * @param {ExtComponent} loadMask
     * @param {function} onSuccess
     * @param {function} onFailure
     */
    _getRegisterAsFile: function(data, loadMask, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({

            url: '/?c=DSSViewer&m=getRegisterAsFile',

            params: {
                moduleId: data.moduleData.moduleId,
                moduleName: data.moduleData.moduleName,
                registerId: data.registerData.registerId,
                registerName: data.registerData.registerName
            },

            success: function(response, opts) {
                loadMask.hide();
                response.blob()
                .then((file) => {
                    onSuccess(file);
                })
                .catch((err) => {
                    onFailure(err);
                });

            },

            failure: function(response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Отобразить страницу клинического регистра
     *
     * @param {ExtComponent} form  - родительский компонент
     * @param {Ext.LoadMask} loadMask  - индикатор ожидания
     * @param {Object} data - объект параметров для получения клинического регистра
     * @param {function} onReturn - переход на предыдущую страниццу
     * @param {function} onSessionSelected - просмотр анкеты при нажатии на строку регистра
     * @param {function} onFailure - обработчик ошибки
     */
    show: function(form, loadMask, data, onReturn, onSessionSelected, onFailure) {
        var self = this;
        var genRegisterForm;

        genRegisterForm = self._makeGenRegisterForm(data, onReturn);
        form.add(genRegisterForm);

        self._getRegisterStructure(
            data,
            loadMask,
            function onSuccess(registerStructure) {
                self._getLpuRegions(
                    data,
                    loadMask,
                    function onSuccess(lpuRegions) {
                        self._getAllLpuRegions(
                            data,
                            loadMask,
                            function onSuccess(allLpuRegions) {
                                self._onDataLoaded(
                                        genRegisterForm,
                                        data,
                                        registerStructure,
                                        allLpuRegions.filter(function(lpuRegion) {
                                            var found = false;
                                            lpuRegions.forEach(function(doctorLpuRegion) {
                                                if (doctorLpuRegion.LpuRegion_id === lpuRegion.LpuRegion_id) {
                                                    found = true;
                                                }
                                            });
                                            return found;
                                        }))
                            },
                            onFailure
                        );
                    },
                    onFailure
                );
            },
            onFailure
        );
    },


    /**
     * Обработчик события завершения предварительной загрузки данных -
     *     структуры регистра и списка участков врача
     *
     * После загрузки предварительных данных отобразить регистр
     * @param {ExtComponent} genRegisterForm
     * @param {Object} data
     * @param {Array} registerStructure
     * @param {Array} lpuRegions - список участков врача
     */
    _onDataLoaded: function(genRegisterForm, data, registerStructure, lpuRegions) {
        var self = this;
        var registerGrid;

        registerGrid = self._registerForm.makeGrid(
                data,
                lpuRegions,
                registerStructure);

        genRegisterForm.add(self._selectLpuRegionsForm.makeForm(
                lpuRegions,
                function onLpuRegionsSelected(lpuRegions) {
                    var registerStore = registerGrid.getStore();

                    registerStore.getProxy().extraParams.lpuRegions
                            = lpuRegions.join(',');

                    registerStore.reload();
                }));

        genRegisterForm.add(registerGrid);
    },


    /**
     * Сформировать основную форму страницы регистров,
     *     на которой будут размещаться остальные элементы
     *
     * @param {object} data - параметры страницы
     * @param {function} onReturn - кнопка возврата к предыдущей странице
     * @return {Ext6.form.FormPanel} - основная форма страницы регистров
     */
    _makeGenRegisterForm: function(data, onReturn) {
        return new Ext6.form.FormPanel({
            id: 'genRegisterForm',
            border: false,
            items: [

                new Ext6.form.FormPanel({
                    border: false,
                    html: ''
                        + '<h1>Модуль "<i>'
                        +     data.moduleData.moduleName
                        + '</i>"</h1>'
                        + '<h2>Регистр "<i>'
                        +     data.registerData.registerName
                        + '</i>"</h2>',
                    width: '100%',
                    style: 'margin-bottom: 32px;'
                }),

                new Ext6.Button({
                    text: 'Вернуться к выбору регистра',
                    style: 'margin: 32px 24px;',
                    handler: onReturn
                }),

                new Ext6.Button({
                    text: 'Экспорт регистра в файл',
                    style: 'margin: 32px 24px;',
                    handler: function() {
                        var form;

                        form = new Ext6.form.Panel({
                            standardSubmit: true,
                            url: '/?c=DSSViewer&m=getRegisterAsFile',
                            method: 'POST'
                        });

                        form.submit({
                            target: '_blank',
                            params: {
                                moduleId: data.moduleData.moduleId,
                                moduleName: data.moduleData.moduleName,
                                registerId: data.registerData.registerId,
                                registerName: data.registerData.registerName
                            }
                        });

                        Ext6.defer(function() {
                            form.close();
                        }, 100);
                    }
                })
            ]
        });
    },


    /**
     * Форма для выбора участков МО, пациенты которых должны войти в регистр
     */
    _selectLpuRegionsForm: {


        /**
         * Сформировать панель фильтра пациентов по участкам - выбора участков,
         *     пациенты которых должны войти в регистр
         *
         * @param {Array} lpuRegions - список участков врача
         * @param {function} onLpuRegionsSelected - колбэк
         */
        makeForm: function(lpuRegions, onLpuRegionsSelected) {
            var form;
            var items;
            var checkBoxGroup;

            items = lpuRegions.map(function(lpuRegion) {
                return {
                    boxLabel: lpuRegion.LpuRegionType_Name + ' ' + lpuRegion.LpuRegion_Name,// + ' '/* + lpuRegion*/,
                    name : lpuRegion.LpuRegion_id,
                    inputValue: lpuRegion.LpuRegion_id
                };
            });

            checkBoxGroup = new Ext6.form.CheckboxGroup({
                fieldLabel: 'Список участков',
                defaultType: 'checkboxfield',
                vertical: true,
                columns: 4,
                items: items
            });

            form = new Ext6.form.FormPanel({
                border: false,
                items: [
                    checkBoxGroup,
                    {
                        xtype: 'button',
                        text : 'Применить',
                        handler: function() {
                            var valuesObj = checkBoxGroup.getValue();
                            var valuesList = [];
                            for (var lpuRegion_id in valuesObj) {
                                valuesList.push(lpuRegion_id);
                            }

                            onLpuRegionsSelected(valuesList);
                        }
                    }
                ]
            });

            return form;
        }

    },


    /**
     * Форма для отображения клинического регистра
     */
    _registerForm: {


        /**
         * Преобразование данных, полученных от сервера, в формат для вставки в грид
         *
         * @param {Array} data - данные, полученные от сервера
         * @return {Object {Array} data, {int} total} } data - данные для вставки в грид
         */
        _transformGridRow: function(data) {
            var self = this;
            var isMarkedRow;

            var registerData = data[0]; // хук для корректного отображения названий полей
            var finalSessions = [];

            isMarkedRow = true;
            registerData.data.forEach(function(patient) {
                var item = []; // строки таблицы, описывающие одного пациента
                var patientData;

                patientData = {
                    patientId: patient.patient.patientId,
                    patientLogin: patient.patient.patientLogin
                };

                patient.data.forEach(function(session) {
                    var sessionData;
                    var registerRow;

                    sessionData = {
                        sessionId: session.session.sessionId,
                        sessionStartDT: session.session.sessionStartDT,
                        sessionCloseDTLF: self._timeTransform(session.session.sessionCloseDT)
                    };

                    registerRow = {};
                    session.data.forEach(function(feature) {
                        registerRow['answerText_' + feature.featureId] = feature.text + ' ';
                    });

                    item.push(Object.assign(
                            {},
                            patientData,
                            sessionData,
                            registerRow,
                            {
                                isMarkedRow: isMarkedRow
                            }));
                });

                finalSessions = finalSessions.concat(item);

                isMarkedRow = !isMarkedRow;
            });

            return {
                data: finalSessions,
                total: registerData.total
            }
        },


        /**
         * Обработчик событий пагинации
         *
         * @param {Ext6.toolbar.Paging} scope
         * @param {int} page - the page number that will be loaded on change
         * @param ...beforechange event args
         */
        _handleBeforeChangeEvent: function(scope, page, eOpts) {
            var nextPage;
            var pageDiff; // на сколько страниц переход
            var start;

            nextPage = scope.getStore().currentPage;
            pageDiff = page - scope.getStore().currentPage;
            start = (scope.getStore().currentPage - 1) * scope.getStore().pageSize;

            if ((pageDiff < 0) && ((scope.getStore().currentPage - pageDiff) < 1)) {
                return false;
            }

            nextPage += pageDiff;
            start += scope.getStore().pageSize * pageDiff;

            scope.getStore().getProxy().extraParams.start = start;
            scope.getStore().loadPage(nextPage);

            // отменить автоматический переход
            return false;
        },


        /**
         * Сформировать стор
         *
         * @param {Object: moduleData, registerData} data
         * @param {Array} lpuRegions - список участков, пациенты которых должны войти в регистр
         * @param {Array} registerStructure
         * @return {Ext6.data.Store}
         */
        _makeStore: function(data, lpuRegions, registerStructure) {
            var self = this;
            var params;
            var storeFields;
            var itemsPerPage;

            params = {
                moduleId: data.moduleData.moduleId,
                moduleName: data.moduleData.moduleName,
                registerId: data.registerData.registerId,
                registerName: data.registerData.registerName,
                lpuRegions: ''
            };

            storeFields = [
                'patientId',
                'patientLogin',
                'sessionId',
                'sessionStartDT',
                'sessionCloseDTLF'
            ]
            .concat(registerStructure.map(function(question) {
                return 'answerText_' + question.featureId;
            }));
            itemsPerPage = 3;

            return new Ext6.data.Store({
                autoLoad: true,
                fields: storeFields,
                pageSize: itemsPerPage,
                proxy: {
                    type: 'ajax',
                    url: '/?c=DSSViewer&m=getRegisterContentByRegions',
                    reader: new Ext6.data.JsonReader({
                        rootProperty: 'data',
                        totalProperty: 'total',
                        transform: self._transformGridRow.bind(self)
                    }),
                    extraParams:params
                }
            });
        },


        /**
         * Сформировать грид для отображения регистра
         *
         * @param {object} data - параметры получения клинического регистра
         * @param {Array} lpuRegions - список участков, пациенты которых должны войти в регистр
         * @param {Array} registerStructure
         * @return {Ext6.grid.GridPanel}
         */
        makeGrid: function(data, lpuRegions, registerStructure) {
            var self = this;
            var addTip;
            var registerStore;

            var gridColumns;

            addTip = function(val, metaData, record) {
                if (val) {
                    if (!('' + val).trim().length) {
                        val = 'н/д';
                    }
                    metaData.tdAttr = 'data-qtip="' + val + '"';
                    metaData.style = 'white-space: normal;';
                    if (record.data.isMarkedRow) {
                        metaData.style += 'color: #448;';
                    }
                }
                return val;
            };

            registerStore = self._makeStore(data, lpuRegions, registerStructure);

            gridColumns =  [{
                    dataIndex: 'patientId',
                    header: `Идентификатор пациента`,
                    flex: 1,
                    minWidth: 150,
                    align: 'center',
                    renderer: addTip
                }, {
                    dataIndex: 'patientLogin',
                    header: `Фамилия пациента`,
                    flex: 1,
                    minWidth: 150,
                    align: 'center',
                    renderer: addTip
                }, {
                    dataIndex: 'sessionId',
                    header: `Идентификатор анкеты`,
                    flex: 1,
                    minWidth: 150,
                    align: 'center',
                    renderer: addTip
                }, {
                    dataIndex: 'sessionCloseDTLF',
                    header: `Время заполнения`,
                    flex: 1,
                    align: 'center',
                    minWidth: 150,
                    renderer: addTip
                }, {
                    dataIndex: 'sessionStartDT',
                    hidden: true
            }].concat(registerStructure.map(function(question) {
                return {
                    dataIndex: 'answerText_' + question.featureId,
                    header: question.featureName,
                    flex: 1,
                    align: 'center',
                    minWidth: 150,
                    renderer: addTip
                }
            }));

            return new Ext6.grid.GridPanel({
                store: registerStore,
                bbar: {
                    xtype: 'pagingtoolbar',
                    displayInfo: true,
                    listeners: {
                        beforechange: self._handleBeforeChangeEvent
                    }
                },
                viewConfig: {
                    deferEmptyText: false,
                    emptyText: '<div style="text-align: center;">Список анкет пуст</div>',
                },
                autoHeight: true,
                width: '100%',
                title: data.registerData.registerName,
                columns: gridColumns
            });
        },


        /**
         * Преобразовать дату из формата, как она хранится в БД (по utc)
         * в человеческий формат (в местном часовом поясе)
         *
         * @param {string} dateFromDB
         * @return string
         */
        _timeTransform: function(dateFromDB) {
            var utcDate = new Date();
            var year = parseInt(dateFromDB.substring(0, 4));
            var month = parseInt(dateFromDB.substring(5, 7));
            var day = parseInt(dateFromDB.substring(8, 10));
            utcDate.setUTCFullYear(year, month-1, day);
            var hours = parseInt(dateFromDB.substring(11, 13));
            var minutes = parseInt(dateFromDB.substring(14, 16));
            utcDate.setUTCHours(hours, minutes);
            var localDate = utcDate.toLocaleString().substring(0, 17);
            if (localDate.substring(16, 17) == ':') {
                return localDate.substring(0, 16);
            }
            return localDate;
        }
    },


    /**
     * Удалить со страницы все элементы формы регистра
     *
     * @param {ExtComponent} parentComponent
     */
    remove: function(form) {
        var genRegisterForm = form.getComponent('genRegisterForm');

        if (genRegisterForm) {
            form.remove(genRegisterForm);
        }
    }
});
