/**
 * Форма для редактирования списка вариантов значений поля регистра
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
 * @since        11.12.2018
 * @version      23.04.2019
 */
Ext6.define('common.DSS.Editor.Register.DSSEditorRegisterVariableForm', {


    /**
     * Отобразить форму списка значений поля регистра
     *
     *  @param form: Ext6.form.FormPanel - панель для отображения формы
     *  @param function onRegisterDataAccessRightEditButton - callback-функция,
     *      которую нужно выполнить при нажатии на кнопку перехода к работе с правом создания модулей
     *  @param function onSelect - callback-функция, которую нужно выполнить,
     *      когда можуль будет выбран (и успешно заблокирован)
     *  @param function onFailure - callback-функция, которую нужно выполнить
     *      в случае непредвиденной ошибки
     */
    show: function(loadMask, form, diagModule, register, feature, onReturn, onFailure) {
        var self = this;

        var genVariablesPanel = self._variablesPanel.makeVariablesPanel(
                loadMask,
                diagModule,
                register,
                feature,
                onReturn,
                onFailure);

        form.add(genVariablesPanel);
    },


    /**
     * Удалить форму списка регистров с родительского компонента
     *
     * @param form: Ext6.Component - родительский компонент
     */
    remove: function(form) {
        var genVariablesPanel = form.getComponent('genVariablesPanel');
        if (genVariablesPanel) {
            form.remove(genVariablesPanel);
        }
    },


    /**
     * Панель вариантов значения поля регистра
     */
    _variablesPanel: {


        /**
         * Преобразовать данные, полученные от АПИ сервера, в строку таблицы
         *
         * @param data: variable[]
         * @param data: row[]
         */
        _transformVariableData: function(data) {
            if (data[0] === 'empty') {
                return [];
            }

            return data.map(function(row) {
                row.renameVariable = 'Переименовать';
                row.deleteVariable = 'Удалить';

                return row;
            });
        },


        /**
         * Обработчик нажатия на ячейку таблицы
         *
         * @param record
         * @param cellIndex
         */
        _onCellClick: function(
            loadMask,
            diagModule,
            register,
            feature,
            variable,
            onSuccess,
            onFailure,
            clickedColumnName
        ) {
            var self = this;

            switch (clickedColumnName) {

                case 'renameVariable': {
                    self._onRenameVariable(
                            loadMask,
                            diagModule,
                            register,
                            feature,
                            variable,
                            onSuccess,
                            onFailure);
                    break;
                }

                case 'deleteVariable': {
                    self._onDeleteVariable(
                            loadMask,
                            diagModule,
                            register,
                            feature,
                            variable,
                            onSuccess,
                            onFailure);
                    break;
                }

                default: {
                    // no action
                }

            }
        },


        /**
         * Создать форму списка вариантов значения поля регистра
         *
         */
        makeVariablesPanel: function(
            loadMask,
            diagModule,
            register,
            feature,
            onReturn,
            onFailure
        ) {
            var self = this;
            var genVariablesPanel;
            var variablesStore;

            variablesStore = new Ext6.data.Store({
                autoLoad: true,
                fields: [
                    'variableName',
                    'answerVariantId',
                    'renameVariable',
                    'deleteVariable'
                ],
                proxy: {
                        type: 'ajax',
                        actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                        reader: {
                            type: 'json',
                            transform: {
                                fn: self._transformVariableData
                            }
                        },
                        extraParams: {
                            moduleId: diagModule.moduleId,
                            moduleName: diagModule.moduleName,
                            registerId: register.registerId,
                            registerName: register.registerName,
                            registerFeatureId: feature.featureId,
                            registerFeatureName: feature.featureName
                        },
                        url: '/?c=DSSEditorRegisterVariable&m=getVariables'
                    }
            });

            genVariablesPanel = new Ext6.form.FormPanel({
                id: 'genVariablesPanel',
                bodyPadding: 32,
                border: false,
                width: '100%',
                items: [

                    new Ext6.form.FormPanel({
                        border: false,
                        html: ''
                            + '<h1>Модуль <i>"' + diagModule.moduleName + '"</i></h1>'
                            + '<h2>Клинический Регистр <i>"' + register.registerName + '"</i></h2>'
                            + '<h3>Поле <i>"' + feature.featureName + '"</i></h3>',
                        width: '100%',
                        style: 'margin-bottom: 32px;'
                    }),

                    new Ext6.Button({
                        text: 'Вернуться к списку полей регистра',
                        style: 'margin: 32px 24px;',
                        handler: onReturn
                    }),

                    new Ext6.Button({
                        text: 'Создать новый вариант значения поля регистра',
                        style: 'margin: 32px 24px;',
                        handler: function() {
                            self._onCreateVariable(
                                    loadMask,
                                    genVariablesPanel,
                                    diagModule,
                                    register,
                                    feature,
                                    function() {
                                        variablesStore.reload();
                                    },
                                    onFailure);
                        }
                    }),

                    new Ext6.grid.GridPanel({
                        store: variablesStore,
                        autoHeight: true,
                        width: '100%',
                        title: 'Список вариантов значений поля регистра',
                        selModel: 'cellmodel',
                        viewConfig: {
                            deferEmptyText: false,
                            emptyText: '<div style="text-align: center;">'
                                + 'Список пуст'
                                + '</div>'
                        },
                        columns: [{
                            dataIndex: 'variableName',
                            tdCls: 'nameTdCls',
                            name: 'variableName',
                            header: 'Название',
                            flex: 5,
                            align: 'center'
                        }, {
                            dataIndex: 'answerVariantId',
                            tdCls: 'nameTdCls',
                            name: 'answerVariantId',
                            header: 'Идентификатор варианта ответа',
                            flex: 1,
                            align: 'center'
                        }, {
                            dataIndex: 'renameVariable',
                            name: 'renameVariable',
                            tdCls: 'nameTdCls',
                            header: 'Переименовать',
                            flex: 1,
                            align: 'center'
                        }, {
                            dataIndex: 'deleteVariable',
                            name: 'deleteVariable',
                            tdCls: 'nameTdCls',
                            header: 'Удалить',
                            flex: 1,
                            align: 'center'
                        }],
                        listeners: {
                            cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                                var clickedColumnName = record.getFields()[cellIndex].getName();
                                var variable = {
                                    variableName: record.get('variableName').trim(),
                                    answerVariantId: record.get('answerVariantId')
                                };
                                self._onCellClick(
                                        loadMask,
                                        diagModule,
                                        register,
                                        feature,
                                        variable,
                                        function onSuccess() {
                                            variablesStore.reload();
                                        },
                                        onFailure,
                                        clickedColumnName);
                            }
                        }
                    })
                ]
            });

            return genVariablesPanel;
        },


        /**
         * Обработчик действия "Создать вариант значения поля регистра"
         *
         * @param data: {} - данные модуля, регистра и поля регистра
         * @param onVariableCreated: function - действие в случае успешного удаления
         * @param onFailure: function - действия при непредвиденной ошибке
         */
        _onCreateVariable: function(loadMask, genVariablesPanel, diagModule, register, feature, onSuccess, onFailure) {
            var self = this;
            var modalWindow;

            modalWindow = new Ext6.window.Window({
                title: 'Создать новый вариант значения поля регистра',
                height: '75%',
                width: '50%',
                scrollable: true,
                draggable: true,
                layout: 'fit',
                items: [
                    new Ext6.grid.GridPanel({
                        store: new Ext6.data.Store({
                            autoLoad: true,
                            fields: [
                                'answerVariantId',
                                'answerVariantStatement'
                            ],
                            proxy: {
                                    type: 'ajax',
                                    actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                    reader: {
                                        type: 'json',
                                        transform: {
                                            fn: function(data) {
                                                var newData = [];

                                                if (data[0] === 'empty') {
                                                    return [];
                                                }
                                                if ((typeof data[0]) === 'string') {
                                                    onFailure(data[0]);
                                                    return;
                                                }

                                                data.forEach(function(question) {
                                                    question.questionAnswerVariants.forEach(function(answerVariant) {
                                                        if (answerVariant.answerVariantStatement === '') {
                                                            answerVariant.answerVariantStatement = question.questionText
                                                                    + ' - ' + answerVariant.answerVariantText;
                                                        }
                                                        newData.push(answerVariant);
                                                    });
                                                });

                                                return newData;
                                            }
                                        }
                                    },
                                    extraParams: {
                                        moduleId: diagModule.moduleId,
                                        moduleName: diagModule.moduleName
                                    },
                                    url: '/?c=DSSEditorQuestion&m=getModuleQuestions'
                                }
                        }),
                        autoHeight: true,
                        width: '100%',
                        title: 'Варианты ответов',
                        viewConfig: {
                            deferEmptyText: false,
                            emptyText: '<div style="text-align: center;">'
                                + 'Список пуст'
                                + '</div>'
                        },
                        columns: [{
                            dataIndex: 'answerVariantId',
                            name: 'answerVariantId',
                            header: 'Идентификатор варианта ответа',
                            flex: 1,
                            align: 'center'
                        }, {
                            dataIndex: 'answerVariantStatement',
                            name: 'answerVariantStatement',
                            header: 'Вариант ответа',
                            flex: 5,
                            align: 'center'
                        }],
                        listeners: {
                            cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                                self._createVariable(
                                        loadMask,
                                        diagModule,
                                        register,
                                        feature,
                                        record.get('answerVariantId'),
                                        onSuccess,
                                        onFailure);
                                genVariablesPanel.remove(modalWindow);
                            }
                        }
                    })
                ]
            });

            genVariablesPanel.add(modalWindow);
            modalWindow.show();
        },


        /**
         * Создать вариант значения поля регистра
         *
         * @param loadMask: Ext6.Component - индикатор ожидания для всего окна
         * @param moduleData: object - модуль
         * @param registerName: string - название создаваемого регистра
         * @return Promise
         */
        _createVariable: function(loadMask, diagModule, register, feature, answerVariantId, onSuccess, onFailure) {
            loadMask.show();

            Ext6.Ajax.request({
                url: '/?c=DSSEditorRegisterVariable&m=postVariable',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName,
                    registerFeatureId: feature.featureId,
                    registerFeatureName: feature.featureName,
                    answerVariantId: answerVariantId
                },
                success: function(response, opts) {
                    loadMask.hide();
                    try {
                        data = JSON.parse(response.responseText);
                    } catch(e) {
                        onFailure(e);
                        return;
                    }
                    if ((typeof data[0]) === 'string') {
                        onFailure(data[0]);
                        return;
                    }
                    onSuccess();
                },
                failure: function(response, opts) {
                    loadMask.hide();
                    onFailure();
                }
            });
        },


        /**
         * Обработчик действия "Переименовать вариант значения поля регистра"
         *
         * @param data: {} - данные модуля, регистра и поля регистра
         * @param variableData: {} - данные варианта значения поля для удаления
         * @param onVariableUpdated: function - действие в случае успешного удаления
         * @param onFailure: function - действия при непредвиденной ошибке
         */
        _onRenameVariable: function(
            loadMask,
            diagModule,
            register,
            feature,
            variable,
            onSuccess,
            onFailure
        ) {
            var self = this;

            Ext6.Msg.prompt(
                'Переименовать вариант значения поля регистра',
                'Введите новое название: ',
                function(btn, text) {
                    if ((btn == 'ok') && (text)) {
                        if ((text.trim() === '') || (variable.variableName === text)) {
                            return;
                        }
                        variable.variableName = text;

                        self._updateVariable(
                            loadMask,
                            diagModule,
                            register,
                            feature,
                            variable,
                            onSuccess,
                            onFailure);
                    }
                },
                null, //scope
                false, //multiline
                variable.variableName //default text
            );
        },


        /**
         * Обработчик действия "Удалить вариант значения поля регистра"
         *
         * @param data: {} - данные модуля, регистра и поля регистра
         * @param variableData: {} - данные варианта значения поля для удаления
         * @param onVariableDeleted: function - действие в случае успешного удаления
         * @param onFailure: function - действия при непредвиденной ошибке
         */
        _onDeleteVariable: function(
            loadMask,
            diagModule,
            register,
            feature,
            variable,
            onSuccess,
            onFailure
        ) {
            var self = this;

            Ext6.Msg.confirm(
                'Удалить вариант значения поля регистра',
                'Удалить вариант значения <i>' + variable.variableName + '</i>?',
                function(btn) {
                    if (btn === 'yes') {
                        self._deleteVariable(
                            loadMask,
                            diagModule,
                            register,
                            feature,
                            variable,
                            onSuccess,
                            onFailure);
                    }
                }
            );
        },


        /**
         * Изменить название варианта значения поля регистра
         *
         * @param loadMaskЖ Ext6.Component - индикатор ожидания для всего окна
         * @param moduleData: object - модуль
         * @param registerData: object - регистр
         * @return Promise
         */
        _updateVariable: function(loadMask, diagModule, register, feature, variable, onSuccess, onFailure) {
            loadMask.show();

            Ext6.Ajax.request({
                url: '/?c=DSSEditorRegisterVariable&m=patchVariable',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName,
                    registerFeatureId: feature.featureId,
                    registerFeatureName: feature.featureName,
                    variableName: variable.variableName,
                    answerVariantId: variable.answerVariantId
                },
                success: function (response, opts) {
                    loadMask.hide();

                    try {
                        data = JSON.parse(response.responseText);
                    } catch(e) {
                        onFailure('msg ' + e);
                        return;
                    }
                    if ((typeof data[0]) === 'string') {
                        onFailure(data[0]);
                        return;
                    }

                    onSuccess();
                },
                failure: function (response, opts) {
                    loadMask.hide();
                    onFailure('Request error');
                }
            });
        },


        /**
         * Удалить вариант значения поля регистра
         *
         * @param loadMask: Ext6.Component - индикатор ожидания для всего окна
         * @param moduleData: object - модуль
         * @param registerData: object - регистр
         * @return Promise
         */
        _deleteVariable: function(loadMask, diagModule, register, feature, variable, onSuccess, onFailure) {
            loadMask.show();

            Ext6.Ajax.request({
                url: '/?c=DSSEditorRegisterVariable&m=deleteVariable',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName,
                    registerFeatureId: feature.featureId,
                    registerFeatureName: feature.featureName,
                    answerVariantId: variable.answerVariantId,
                    variableName: variable.variableName
                },
                success: function (response, opts) {
                    loadMask.hide();

                    try {
                        data = JSON.parse(response.responseText);
                    } catch(e) {
                        onFailure(e);
                        return;
                    }
                    if (data[0] !== 'removed') {
                        onFailure(data[0]); // ошибка
                        return;
                    }

                    onSuccess();
                },
                failure: function (response, opts) {
                    loadMask.hide();
                    onFailure('Network error');
                }
            });
        }
    }
});
