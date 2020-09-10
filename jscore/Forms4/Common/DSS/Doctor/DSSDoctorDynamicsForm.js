/**
 * Панель для визуализации динамики жалоб пациента
 *
 * swDSSWindow - окно для работы врача с опросником
 *   для сбора структурированной медицинской информации и поддержки принятия решений
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
 * @since        22.01.2019
 * @version      11.04.2019
 */
Ext6.define('common.DSS.Doctor.DSSDoctorDynamicsForm', {


    /**
     * Получить список всех модулей
     *
     * @return Promise
     */
    _getAllModules: function(patientId, loadMask, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSS&m=getAllModules',
            params: {
                Person_id: patientId
            },

            success: function (response, opts) {
                var data;
                var modules;

                loadMask.hide();
                try {
                    data = JSON.parse(response.responseText);
                } catch(e) {
                    onFailure(e);
                }

                if (data.success === false) {
                    onFailure(data.Error_Msg);
                    return;
                }
                modules = data[0];
                if (modules.error) {
                    onFailure(modules.error);
                    return;
                }
                onSuccess(modules);
            },

            failure: function (response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Получить структуру мини-регистра - регистр по одному пациенту
     *
     * @param moduleId: int
     * @return Promise
     */
    _getMiniRegStructure: function(patientId, moduleData, register, loadMask, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSS&m=getRegisterStructure',
            params: {
                Person_id: patientId,
                moduleId: moduleData.moduleId,
                moduleName: moduleData.moduleName,
                registerId: register.registerId
            },

            success: function(response, opts) {
                var data;
                var miniregStructure;

                loadMask.hide();
                data = JSON.parse(response.responseText);
                if (data.success === false) {
                    onFailure(data.Error_Msg);
                    return;
                }
                miniregStructure = data[0];// хук для нормального отображения имён полей
                if (miniregStructure.error) {
                    onFailure(miniregStructure.error);
                    return;
                }
                if (miniregStructure.result === 'empty') {
                    // хук для передачи пустого списка
                    onSuccess([]);
                    return;
                }
                onSuccess(miniregStructure);
            },

            failure: function(response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Отобразить страницу панели анкет
     *
     * @param patientId: bigint - идкетификатор пациента
     * @param form: ExtComponent - родительский компонент
     * @param loadMask: LoadMask - индикатор ожидания
     * @param onSessionDetails: function - переход к отображению выбранной анкеты
     * @param onFailure: function - обработчик ошибки
     */
    show: function(data, form, loadMask, onShowActualState, onShowSessionDetails, onFailure) {
        var self = this;
        var genDynamicsForm;

        // использовать регистр с ид 1
        var register = {
            registerId: 1
        };

        genDynamicsForm = new Ext6.form.FormPanel({
            id: 'genDynamicsForm',
            border: false,
            items: [
                new Ext6.form.FormPanel({
                    border: false,
                    autoHeight: true,
                    html: data.patient.patientFullName + ', ' + data.patient.patientAge + ' лет',
                    bodyPadding: 16
                }),
                new Ext6.button.Segmented({
                    allowMultiple: false,
                    allowDepress: false,
                    items: [{
                        name: 'actual',
                        text: 'Текущие данные жалоб пациента'
                    }, {
                        name: 'dynamics',
                        text: 'Динамика жалоб пациента',
                        pressed: true
                    }, {
                        name: 'questionnaire',
                        text: 'Анкета',
                        disabled: true
                    }],
                    listeners: {
                        toggle: function(container, button, pressed) {
                            if (button.name === 'actual') {
                                onShowActualState();
                            }
                        }
                    }
                })
            ]
        });
        form.add(genDynamicsForm);

        self._getAllModules(
            data.patient.patientId,
            loadMask,
            function onSuccess(modules) {
                if (!modules.length) {
                    genDynamicsForm.add(
                        new Ext6.form.FormPanel({
                            border: false,
                            html: 'Список пуст'
                    }));
                    return;
                }

                modules.forEach(function(moduleData) {
                    var modulePanel;
                    var miniregStructure;

                    // зарезервировать место
                    modulePanel = self._createModulePanel(moduleData, genDynamicsForm);
                    // по готовности отобразить данные
                    miniregStructure = self._getMiniRegStructure(
                        data.patient.patientId,
                        moduleData,
                        register,
                        loadMask,
                        function onSuccess(miniregStructure) {
                            self._showModuleData(
                                    modulePanel,
                                    data.patient.patientId,
                                    moduleData,
                                    register,
                                    miniregStructure,
                                    onShowSessionDetails);
                        },
                        function onFailure(msg) {
                            // ошибка при загрузке данных отдельного модуля отобржается в его панели
                            modulePanel.update(
                                '<div style="padding: 24px; color: red;">' + msg + '</div>'
                            );
                        }
                    );
                });
            },
            function onFailure() {
                genDynamicsForm.add(
                    new Ext6.form.FormPanel({
                        border: false,
                        html: ''
                        + '<div style="color: red;">'
                        +     'Не удалось получить список модулей'
                        + '</div>'
                }));
            })
        ;
    },


    /**
     * Удалить со страницы все элементы панели анкет
     *
     * @param parentComponent: ExtComponent
     */
    remove: function(form) {
        const genDynamicsForm = form.getComponent('genDynamicsForm');
        if (genDynamicsForm) {
            form.remove(genDynamicsForm);
        }
    },


    /**
     * Отдельно создать (обновить) панель для модуля,
     * чтобы зарезервировать метсто, а потом асинхронно подгружать туда данные
     *
     * @param moduleData: {} - данные модуля, для которого создаётся панель
     * @param parentComponent: ExtComponent - родительский компонент для создаваемой панели
     * @return modulePanel: ExtComponent
     */
    _createModulePanel: function(moduleData, parentComponent) {
        const modulePanelId = `module_${moduleData.moduleId}`;

        const oldModulePanel = parentComponent.getComponent(modulePanelId);
        if (oldModulePanel) {
            oldModulePanel.update('');
            return oldModulePanel;
        }

        const modulePanel = new Ext6.form.FormPanel({
            id: modulePanelId,
            style: 'margin-top: 32px;',
            title: moduleData.moduleName
        });
        parentComponent.add(modulePanel);
        return modulePanel;
    },


    /**
     * Сформировать стор для одного модуля
     *
     * @param moduleData
     * @param miniregStructure
     * @return Ext6.Store
     * @uses patientId
     */
    _makeModuleStore: function(patientId, moduleData, register, miniregStructure) {
        var self = this;
        var itemsPerPage = 5;
        var moduleStoreFields;
        var transformContent;

        moduleStoreFields = ['sessionId', 'sessionStartDT', 'sessionCloseDTLF']
        .concat(miniregStructure.map(function(question) {
            return 'answerText_' + question.featureId;
        }));

        /**
         * Функиция трансформации данных грида
         *
         */
        transformContent = function(data) {
            var minireg = data[0]; // хук для корректного отображения названий полей
            var finalSessions = minireg.data.map(function(row) {
                var sessionData;
                var registerData;

                sessionData = {
                    sessionId: row.session.sessionId,
                    sessionStartDT: row.session.sessionStartDT,
                    sessionCloseDTLF: self._timeTransform(row.session.sessionCloseDT)
                };
                registerData = {};
                row.data.forEach((feature) => {
                    registerData['answerText_' + feature.featureId] = feature.text + ' ';
                });

                return Object.assign({}, sessionData, registerData);
            });
            return {
                data: finalSessions,
                total: minireg.total
            }
        };

        return new Ext6.data.Store({
            autoLoad: true,
            fields: moduleStoreFields,
            pageSize: itemsPerPage,
            proxy: {
                type: 'ajax',
                url: '/?c=DSS&m=getRegister',
                reader: new Ext6.data.JsonReader({
                    rootProperty: 'data',
                    totalProperty: 'total',
                    transform: transformContent
                }),
                extraParams: {
                    Person_id: patientId,
                    moduleId: moduleData.moduleId,
                    moduleName: moduleData.moduleName,
                    registerId: register.registerId
                }
            }
        });
    },


    /**
     * Отобразить данные по одному модулю
     *
     * @param modulePanel: ExtComponent - где отобразить, родительский компонент
     * @param moduleData - данные модуля
     * @param miniregStructure - структура минирегистра
     */
    _showModuleData: function(
        modulePanel,
        patientId,
        moduleData,
        register,
        miniregStructure,
        onShowSessionDetails
    ) {
        var self = this;

        var checkSessionsAvailable;
        var addTip;

        var moduleColumns;

        /**
         * Проверить, нужно ли отображать анкеты в модуле
         *
         * @param moduleData
         * @return message - сообщение о причинах отсутствия анкет в модуле
         * '' - если есть доступные анкеты
         */
        checkSessionsAvailable = function(moduleData) {
            if (moduleData.moduleStatus === 'P') {
                return '<div style="padding: 32px;">Модуль находится в разработке</div>';
            }
            if (moduleData.moduleStatus !== 'Y') {
                return '<div style="padding: 32px;">Модуль не доступен</div>';
            }
        };

        if (checkSessionsAvailable(moduleData)) {
            modulePanel.update(checkSessionsAvailable(moduleData));
            return;
        }

        /**
         * Добавить всплывающую подсказку
         */
        addTip = function(val, metaData, record) {
            if (val) {
                if (!val.trim().length) {
                    val = 'н/д';
                }
                metaData.tdAttr = 'data-qtip="' + val + '"';
                metaData.style = 'white-space: normal;cursor:pointer;';
            }
            return val;
        };

        moduleColumns =  [{
            dataIndex: 'sessionId',
            header: '<div style="white-space:normal;">Идентификатор анкеты</div>',
            flex: 1,
            minWidth: 150,
            align: 'center'
        }, {
            dataIndex: 'sessionCloseDTLF',
            header: '<div style="white-space:normal;">Время заполнения</div>',
            flex: 1,
            align: 'center',
            minWidth: 150,
            renderer: addTip
        }, {
            dataIndex: 'sessionStartDT',
            hidden: true
        }].concat(miniregStructure.map(function(question) {
            return {
                dataIndex: 'answerText_' + question.featureId,
                header: '<div style="white-space:normal;">' + question.featureName + '</div>',
                flex: 1,
                align: 'center',
                minWidth: 150,
                renderer: addTip
            }
        }));

        modulePanel.add(new Ext6.grid.GridPanel({
            store: self._makeModuleStore(patientId, moduleData, register, miniregStructure),
            autoHeight: true,
            width: '100%',
            columns: moduleColumns,
            bbar: {
                xtype: 'pagingtoolbar',
                displayInfo: true
            },
            viewConfig: {
                deferEmptyText: false,
                emptyText: '<div style="text-align: center;">Список анкет пуст</div>'
            },
            listeners: {
                rowclick: function(grid, record, tr, rowIndex, e, eOpts) {
                    var sessionData = {
                        sessionId: parseInt(record.get('sessionId')),
                        sessionStartDT: record.get('sessionStartDT'),
                        sessionCloseDTLF: record.get('sessionCloseDTLF')
                    };
                    onShowSessionDetails(moduleData, sessionData);
                }
            }
        }));
    },


    /**
     * Преобразовать дату из формата, как она хранится в БД (по utc)
     * в человеческий формат (в местном часовом поясе)
     *
     * @param dateFromDB: string
     * @return string
     */
    _timeTransform: function(dateFromDB) {
        var myDate = new Date();
        var year = parseInt(dateFromDB.substring(0, 4));
        var month = parseInt(dateFromDB.substring(5, 7));
        var day = parseInt(dateFromDB.substring(8, 10));
        myDate.setUTCFullYear(year, month-1, day);
        var hours = parseInt(dateFromDB.substring(11, 13));
        var minutes = parseInt(dateFromDB.substring(14, 16));
        myDate.setUTCHours(hours, minutes);
        var myTime = myDate.toLocaleString().substring(0, 17);
        if (myTime.substring(16, 17) == ':') {
            return myTime.substring(0, 16);
        }
        return myTime;
    }
});
