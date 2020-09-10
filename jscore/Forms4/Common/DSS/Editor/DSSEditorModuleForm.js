/**
 * Форма для редактирвания модулей
 *
 * swDSSEditorWindow - окно для редактирования опросников
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
 * @since        31.05.2018
 * @version      12.04.2019
 */
Ext6.define('common.DSS.Editor.DSSEditorModuleForm', {


    /**
     * Получить данные о медработнике, который сейчас
     *     работает в системе (выполнить ajax- запрос)
     *
     * @param {Ext6.LoadMask} loadMask - индикатор ожидания для всего окна
     * @param {function(Doctor)} onSuccess -
     *       callback-функция, выполняемая в случае успеха
     *       doctorHasRight2CreateModules - флаг наличия/отсутствия права создания модулей
     * @param {function(string)} onFailure
     */
    _putDoctor: function(loadMask, onSuccess, onFailure) {
        var self = this;

        loadMask.show();
        Ext6.Ajax.request({

            // если переместить контроллеры в отдельную папку, то адрес будет такой
            //url: '/?d=DSS&c=DSSEditorEditor&m=putDoctor',
            url: '/?c=DSSEditorEditor&m=putDoctor',

            success: function (response, opts) {
                var data;
                var user;

                loadMask.hide();

                data = JSON.parse(response.responseText);
                user = data[0]; // хук для корекного отображения названий полей
                if ((typeof user) === 'string') {
                    // вернуласть ошибка
                    onFailure(user);
                    return;
                }
                if ((typeof user) !== 'object') {
                    // вернулось неизветсно что
                    onFailure('Put doctor: got malformed api response');
                    return;
                }
                if ((typeof user.doctorHasRight2CreateModules) !== 'boolean') {
                    // флаг прива создания модулей должен иметь логический тип
                    onFailure('Put doctor: doctorHasRight2CreateModules must have boolean type');
                    return;
                }

                onSuccess({
                    doctorId: self._bigInt(user.doctorId),
                    doctorLogin: user.doctorLogin,
                    doctorHasRight2CreateModules: user.doctorHasRight2CreateModules
                });
            },

            failure: function (response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Заблокировать модуль (выполнить ajax- запрос)
     *
     * В процессе редактирования опросника опросник может находиться
     * в незавершенном состоянии.
     * Поэтому на время редактирования модуль блокируется
     * и другим пользователям доступ запрещается
     * @param {Ext6.LoadMask} loadMask - индикатор ожидания для всего окна
     * @param {DiagModule} diagModule - диагностический модуль
     * @param {function()} onSuccess -
     *       callback-функция, выполняемая в случае успеха
     * @param {function(string)} onFailure
     */
    _lockModule: function(loadMask, diagModule, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorModule&m=putModuleLock',
            params: {
                moduleId: diagModule.moduleId,
                moduleName: diagModule.moduleName
            },
            success: function (response, opts) {
                var data;
                var diagModule;

                loadMask.hide();
                data = JSON.parse(response.responseText);
                diagModule = data[0]; // хук для корректного отображения полей
                if (
                    (!diagModule.moduleLock)
                    || (!diagModule.moduleLock.isLocked)
                ) {
                    onFailure('Не удалось заблокировать модуль');
                    return;
                }
                Ext6.Msg.alert(
                    'Блокировка модуля',
                    'Модуль заблокирован',
                    function() {
                        onSuccess();
                    }
                );
            },

            failure: function (response, opts) {
                loadMask.hide();
                onFailure();
                return;
            }
        });
    },


    /**
     * Разблокировать модуль (выполнить ajax- запрос)
     *
     * @param {Ext6.LoadMask} loadMask - индикатор ожидания для всего окна
     * @param {DiagModule} diagModule - диагностический модуль
     * @param {function} onSuccess - callback-функция, выполняемая в случае успеха
     * @param {function(string)} onFailure
     */
    unlockModule: function(loadMask, diagModule, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorModule&m=deleteModuleLock',
            params: {
                moduleId: diagModule.moduleId,
                moduleName: diagModule.moduleName
            },
            success: function (response, opts) {
                var data;
                var diagModule;

                loadMask.hide();
                data = JSON.parse(response.responseText);
                diagModule = data[0]; // хук для корректного отображения полей
                if (
                    (!diagModule.moduleLock)
                    || (diagModule.moduleLock.isLocked !== false)
                ) {
                    onFailure('Не удалось разблокировать модуль');
                    return;
                }
                Ext6.Msg.alert(
                    'Блокировка модуля',
                    'Модуль разблокирован',
                    function() {
                        onSuccess();
                    }
                );
            },

            failure: function (response, opts) {
                loadMask.hide();
                onFailure(response.responseText);
                return;
            }
        });
    },


    /**
     * Отображение формы выбора модуля
     *
     * @param Ext6.form.FormPanel form - пустая панель для отображения формы
     * @param function onCreateModuleRightEditButton - callback-функция,
     *      которую нужно выполнить при нажатии на кнопку перехода к работе с правом создания модулей
     * @param function onSelect - callback-функция, которую нужно выполнить,
     *     когда можуль будет выбран (и успешно заблокирован)
     * @param function onFailure - callback-функция, которую нужно выполнить
     *      в случае непредвиденной ошибки
     */
    show: function(loadMask, form, onCreateModuleRightEditButton, onModuleSelected, onFailure) {
        var self = this;

        self._putDoctor(
            loadMask,
            function onSuccess(doctor) {
                self.selectModuleForm.show(
                    loadMask,
                    form,
                    doctor,
                    onCreateModuleRightEditButton,
                    function onModuleRowSelected(diagModule) {
                        self._lockModule(
                            loadMask,
                            diagModule,
                            function onModuleLocked() {
                                self.selectModuleForm.remove(form);
                                onModuleSelected(diagModule);
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
     * Валидация идентификатора
     *
     * В связи с тем, что bigint может быть до 2^63 - 1,
     * а javascript может только до 2^53 - 1,
     * для работы с идентификаторами испольуются строки
     * @param {string} id
     * @param {string} name - для формирования сообщения об ошибке
     * @return {string}
     */
    _bigInt: function(id, name) {
        if ((typeof id) !== 'string') {
            throw new TypeError('Type of ' + name + 'must be string');
        }

        if ((!id) && (id !== '0')) {
            throw new Error('id is not bigint: empty. ' + name);
        }

        if (id.match(/[^0-9]/)) {
            throw new Error('id is not bigint: symbols. ' + name);
        }

        if (id.length > 19) {
            throw new Error('id is not bigint: too big. '+ name);
        }

        return id;
    },


    selectModuleForm: {

        /**
         * Создать модуль (выполнить ajax-запрос)
         *
         * @param {Ext6.LoadMask} loadMask - индикатор ожидания
         * @param {string} moduleName - название нового модуля
         * @param {function} onSuccess() -
         *       callback-функция, выполняемая в случае успеха
         * @param {function} onFailure()
         */
        _createModule: function(loadMask, moduleName, onSuccess, onFailure) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorModule&m=postModule',
                params: {
                    moduleName: moduleName
                },
                success: function (response, opts) {
                    loadMask.hide();
                    onSuccess();
                },
                failure: function (response, opts) {
                    loadMask.hide();
                    onFailure();
                }
            });
        },



        /**
         * Преобразовать данные модулей, полученные от АПИ сервера, в строки таблицы модулей
         *
         * @param {Array} data
         * @return {Array}
         */
        _transformModuleData: function(data) {
            var newData = [];
            if (data[0] === 'empty') {
                return [];
            }

            data.forEach(function(row) {
                row.renameModule = 'Переименовать';
                row.deleteModule = 'Удалить';
                row.status = (row.moduleLock.isLocked === false)
                        ? 'Доступен'
                        : 'Редактируется экспертом <i>'
                            + row.moduleLock.lockingDoctor.doctorLogin
                            + '</i>';
                row.moduleStatusCode = row.moduleStatus;
                row.moduleStatus = (function(statusCode) {
                    switch (statusCode) {
                        case 'Y': return 'Доступен';
                        case 'P': return 'Резерв';
                        case 'N': return 'Невидимый';
                        default: throw new Error('Неопознанный статус модуля');
                    }
                })(row.moduleStatus);

                newData.push(row);
            });

            return newData;
        },


        /**
         * Сормировать грид модулей
         *
         * @return {Ext.Component}
         */
        _makeGrid: function(loadMask, doctor, onModuleSelected, onFailure) {
            var self = this;
            var grid = new Ext6.grid.GridPanel({
                viewConfig: {
                    deferEmptyText: false,
                    emptyText: '<div style="text-align: center;">Список пуст</div>'
                },
                store: new Ext6.data.Store({
                    autoLoad: true,
                    fields: [
                        'moduleId',
                        'moduleName',
                        'renameModule',
                        'deleteModule',
                        'status',
                        'moduleStatus'
                    ],
                    proxy: {
                            type: 'ajax',
                            actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                            reader: {
                                type: 'json',
                                transform: {
                                    fn: self._transformModuleData
                                }
                            },
                            url: '/?c=DSSEditorModule&m=getModules'
                        }
                    }),
                    autoHeight: true,
                    width: '100%',
                    title: 'Выбор модуля',
                    selModel: 'cellmodel',
                    columns: [{
                        dataIndex: 'moduleId',
                        name: 'moduleId',
                        tdCls: 'nameTdCls',
                        header: 'Идентификатор',
                        flex: 1,
                        align: 'center',
                        renderer: self._addTip
                    }, {
                        dataIndex: 'moduleName',
                        tdCls: 'nameTdCls',
                        name: 'moduleName',
                        header: 'Название',
                        flex: 5,
                        align: 'center',
                        renderer: self._addTip
                    }, {
                        dataIndex: 'renameModule',
                        name: 'renameModule',
                        tdCls: 'nameTdCls',
                        header: 'Переименовать модуль',
                        flex: 1,
                        align: 'center',
                        renderer: self._addTip
                    }, {
                        dataIndex: 'deleteModule',
                        name: 'deleteModule',
                        tdCls: 'nameTdCls',
                        header: 'Удалить модуль',
                        flex: 1,
                        align: 'center',
                        renderer: self._addTip
                    }, {
                        dataIndex: 'status',
                        name: 'status',
                        text: 'Состояние',
                        tdCls: 'nameTdCls',
                        flex: 3,
                        align: 'center',
                        renderer: self._addTip
                    }, {
                        dataIndex: 'moduleStatus',
                        name: 'moduleStatus',
                        text: 'Статус',
                        tdCls: 'nameTdCls',
                        flex: 3,
                        align: 'center',
                        renderer: self._addTip
                    }
                ],
                listeners: {
                    cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                        self.moduleActionHandlers.onCellClick(
                            loadMask,
                            grid,
                            doctor,
                            cellIndex,
                            record,
                            onModuleSelected,
                            onFailure
                        );
                    }
               }
           });

           return grid;
        },


        /**
         * Рендерер строки грида: добавить всплывающую подсказку и отформатировать
         *
         */
        _addTip: function(val, metaData, record) {
            if (val) {
                metaData.tdAttr = 'data-qtip="' + val + '"';
            }
            metaData.tdStyle = 'vertical-align: middle;'
            return '<div style="white-space: normal;">' + val + '</div>';
        },


        /**
         * Отобразить форму модулей
         *
         * @param {Ext.LoadMask} loadMask
         * @param {Ext.form.FormPanel} form - родительский компонент для формы модулей
         * @param {Doctor} doctor - медработник, который сейчас работает в системе
         * @param {function()} onCreateModuleRightEditButton -
         * @param {function(DiagModule)} onModuleSelected -
         * @param {function(string)} onFailure
         */
        show: function(loadMask, form, doctor, onCreateModuleRightEditButton, onModuleSelected, onFailure) {
            var self = this;
            var grid;

            grid = self._makeGrid(loadMask, doctor, onModuleSelected, onFailure);

            form.add(new Ext6.form.FormPanel({
                id: 'genModulePanel', // чтобы можно было надёжно удалять с родительской формы
                bodyPadding: 32,
                border: false,
                width: '100%',
                items: [

                    new Ext6.Button({
                        text: 'Создать новый модуль',
                        style: 'margin: 32px 24px;',
                        disabled: !doctor.doctorHasRight2CreateModules,
                        handler: function() {
                            self._onCreateModuleButton(
                                    loadMask,
                                    function onSuccess() {
                                        grid.getStore().reload();
                                    },
                                    onFailure);
                        }
                    }),

                    new Ext6.Button({
                        text: 'Дать/отозвать право создания модулей',
                        style: 'margin: 32px 24px;',
                        disabled: !doctor.doctorHasRight2CreateModules,
                        handler: function() {
                            self.remove(form);
                            onCreateModuleRightEditButton();
                        }
                    }),

                    grid
                ]
            }));
        },


        /**
         * Очистить родительский компонент от формы модулей
         *
         * @param {Ext.Component} parentElement
         */
        remove: function(form) {
            var genModulePanel = form.getComponent('genModulePanel');
            if (genModulePanel) {
                form.remove(genModulePanel);
            }
        },


        /**
         * Обработчик нажатия на кнопку создания нового модуля
         *
         * @param {Ext.LoadMask} loadMask
         * @param {function()} onSuccess
         * @param {function(string)} onFailure
         */
        _onCreateModuleButton: function(loadMask, onSuccess, onFailure) {
            var self = this;

            Ext6.Msg.prompt(
                'Создание нового модуля',
                'Введите название: ',
                function(btn, text) {
                    if ((btn == 'ok') && (text)) {
                        self._createModule(
                            loadMask,
                            text,
                            onSuccess,
                            onFailure
                        );
                    }
                }
            );
        },


        /**
         * Обработчики нажания на ячейку таблицы модулей
         *
         */
        moduleActionHandlers: {


            /**
             * Изменить название модуля (AJAX-запрос)
             *
             * @param {Ext6.LoadMask} loadMask - индикатор ожидания
             * @param {DiagModule} diagModule - модуль
             * @param {string} newModuleName - новое название модуля
             * @param {function()} onSuccess -
             *       callback-функция, выполняемая в случае успеха
             * @param {function(string)} onFailure
             */
            _updateModule: function(loadMask, diagModule, newModuleName, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorModule&m=patchModule',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        newModuleName: newModuleName,
                        moduleStatus: diagModule.moduleStatus
                    },
                    success: function (response, opts) {
                        loadMask.hide();
                        onSuccess();
                    },
                    failure: function (response, opts) {
                        loadMask.hide();
                        onFailure();
                    }
                });
            },


            /**
             * Изменить видимость модуля (запрос к серверу)
             *
             * @param {Ext6.LoadMask} loadMask - индикатор ожидания
             * @param {DiagModule} diagModule - модуль
             * @param {function()} onSuccess -
             *       callback-функция, выполняемая в случае успеха
             * @param {function(string)} onFailure
             */
            _updateModuleStatus: function(loadMask, diagModule, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorModule&m=patchModuleStatus',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        moduleStatus: diagModule.moduleStatus
                    },
                    success: function (response, opts) {
                        loadMask.hide();
                        onSuccess();
                    },
                    failure: function (response, opts) {
                        loadMask.hide();
                        onFailure();
                    }
                });
            },


            /**
             * Удалить модуль
             *
             * @param {Ext6.LoadMask} loadMask - индикатор ожидания
             * @param {DiagModule} diagModule - модуль
             * @param {function} onSuccess -
             *       callback-функция, выполняемая в случае успеха
             * @param {function(string)} onFailure
             */
            _deleteModule: function(loadMask, diagModule, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorModule&m=deleteModule',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName
                    },
                    success: function (response, opts) {
                        loadMask.hide();
                        onSuccess();
                    },
                    failure: function (response, opts) {
                        loadMask.hide();
                        onFailure();
                    }
                });
            },


            /**
             * Нажатие на ячейку
             *
             */
            onCellClick: function(loadMask, grid, doctor, cellIndex, record, onModuleSelected, onFailure) {
                var h = this;
                var clickedColumnName = record.getFields()[cellIndex].getName();
                var lockingDoctorName = record.get('status').trim();
                var diagModule = {
                    moduleId: record.get('moduleId'),
                    moduleName:record.get('moduleName').trim(),
                    moduleStatus: h._getModuleStatusCode(record.get('moduleStatus').trim())
                };

                switch (clickedColumnName) {

                    case 'moduleId': {
                        onModuleSelected(diagModule);
                        break;
                    }

                    case 'moduleName': {
                        onModuleSelected(diagModule);
                        break;
                    }

                    case 'renameModule': {
                        h._renameModule(loadMask, grid, diagModule, onFailure);
                        break;
                    }

                    case 'moduleStatus': {
                        h._changeModuleStatus(loadMask, grid, diagModule, onFailure);
                        break;
                    }

                    case 'deleteModule': {
                        h._deleteModuleHandler(loadMask, grid, diagModule, onFailure);
                        break;
                    }

                    default: {
                        // no action
                    }
                }
            },


            /**
             * Обработчик для нажатия на ячейку изменения названия модуля
             *
             */
            _renameModule: function(loadMask, grid, diagModule, onFailure) {
                var self = this;

                Ext6.Msg.prompt(
                    'Переименовать модуль',
                    'Введите новое название: ',
                    function(btn, text) {
                        if ((btn == 'ok') && (text) && (text.trim().length > 2)) {
                            self._updateModule(
                                loadMask,
                                diagModule,
                                text.trim(),
                                function onSuccess() {
                                    grid.getStore().reload();
                                },
                                onFailure
                            );
                        }
                    },
                    null,//scope
                    false,//multiline
                    diagModule.moduleName//default text
                );
            },


            /**
             * Обработчик нажания на ячейку таблицы изменения статуса модуля
             *
             */
            _changeModuleStatus: function(loadMask, grid, diagModule, onFailure) {
                var self = this;
                var updatedDiagModule;
                var newModuleStatus;

                newModuleStatus = ((function(oldModuleStatus){
                    switch (oldModuleStatus) {
                        case 'Y': return 'N';
                        case 'N': return 'P';
                        case 'P': return 'Y';
                        default: return 'N';
                    }
                })(diagModule.moduleStatus));

                updatedDiagModule = {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    moduleStatus: newModuleStatus
                };

                self._updateModuleStatus(
                    loadMask,
                    updatedDiagModule,
                    function onSuccess() {
                        grid.getStore().reload();
                    },
                    onFailure
                );
            },


            /**
             * Преобразовать статус модуля в текстовом виде в код статуса
             *
             * @param {string} moduleStatus - текстовый вид статуса модуля
             * @return {string} - код статуса модуля
             */
            _getModuleStatusCode: function(moduleStatus) {
                switch (moduleStatus) {
                    case 'Доступен': return 'Y';
                    case 'Резерв': return 'P';
                    case 'Невидимый': return 'N';
                    default: return 'N';
                };
            },


            /**
             * Обработчик нажатия на ячейку таблицы удаления модуля
             *
             */
            _deleteModuleHandler: function(loadMask, grid, diagModule, onFailure) {
                var self = this;

                Ext6.Msg.confirm(
                    'Удалить модуль',
                    'Удалить модуль <i>'
                    + diagModule.moduleName
                    + '</i> безвозвратно со всеми собранными данными?',
                    function(btn) {
                        if (btn == 'yes') {
                            self._deleteModule(
                                loadMask,
                                diagModule,
                                function onSuccess() {
                                    grid.getStore().reload();
                                },
                                onFailure
                            );
                        }
                    }
                );
            }
        }
    }
});
