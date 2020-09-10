/**
 * Форма для редактирования списка полей регистра
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
 * @since        10.12.2018
 * @version      19.04.2019
 */
Ext6.define('common.DSS.Editor.Register.DSSEditorRegisterFeatureForm', {


    /**
     * Создать поле регистра
     *
     * @param loadMask: Ext6.Component - индикатор ожидания для всего окна
     * @param moduleData: object - модуль
     * @param registerName: string - название создаваемого регистра
     * @return Promise
     */
    _createRegisterFeature: function(loadMask, diagModule, register, registerFeatureName, onSuccess, onFailure) {
        loadMask.show();

        Ext6.Ajax.request({
            url: '/?c=DSSEditorRegisterFeature&m=postRegisterFeature',
            params: {
                moduleId: diagModule.moduleId,
                moduleName: diagModule.moduleName,
                registerId: register.registerId,
                registerName: register.registerName,
                registerFeatureName: registerFeatureName
            },
            success: function(response, opts) {
                var data;
                loadMask.hide();

                try {
                    data = JSON.parse(response.responseText);
                } catch(e) {
                    onFailure(e);
                    return;
                }
                if (!Number.isInteger(data[0].featureId)) {
                    onFailure(data[0]);
                    return;
                }

                onSuccess(data);
            },
            failure: function(response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Отобразить форму списка полей регистра
     *
     *  @param form: Ext6.form.FormPanel - панель для отображения формы
     *  @param function onRegisterDataAccessRightEditButton - callback-функция,
     *      которую нужно выполнить при нажатии на кнопку перехода к работе с правом создания модулей
     *  @param function onSelect - callback-функция, которую нужно выполнить,
     *      когда можуль будет выбран (и успешно заблокирован)
     *  @param function onFailure - callback-функция, которую нужно выполнить
     *      в случае непредвиденной ошибки
     */
    show: function (loadMask, form, diagModule, register, onReturn, onRegisterFeatureSelected, onRegisterDataAccessRightEditButton, onFailure) {
        var self = this;

        var registerFeaturePanel = self._makeRegisterFeaturePanel(
                loadMask,
                diagModule,
                register,
                onReturn,
                onRegisterFeatureSelected,
                onRegisterDataAccessRightEditButton,
                onFailure);
        form.add(registerFeaturePanel);
    },


    /**
     * Преобразовать данные модуля, полученные от АПИ сервера в строку таблицы
     *
     * @param data: RegisterFeature[]
     * @return row[]
     */
    _transformRegisterFeatureData: function(data) {
        if ((typeof data[0]) === 'string') {
            if (data[0] === 'empty') {
                // трюк для передачи пустого массива
                return [];
            }
            // вернулась ошибка от сервера АПИ
            onFailure(data[0]);
            throw new TypeError('В ходе обработки запросе сервером возникла ошибка');
        }

        return data.map(function(row) {
            row.moveRegisterFeatureUp = 'Вверх';
            row.renameRegisterFeature = 'Переименовать';
            row.deleteRegisterFeature = 'Удалить';

            return row;
        });
    },


    /**
     * Создать форму списка полей регистров
     *
     * @param data: {moduleData, registerData}
     * @param onGridUpdated: function
     * @param onReturn: function
     * @param onRegisterFeatureSelected: function
     * @param onRegisterDataAccessRightEditButton: function
     * @param onFailure: function
     */
    _makeRegisterFeaturePanel: function(
        loadMask,
        diagModule,
        register,
        onReturn,
        onRegisterFeatureSelected,
        onRegisterDataAccessRightEditButton,
        onFailure
    ) {
        var self = this;

        var featuresStore = new Ext6.data.Store({
            autoLoad: true,
            fields: [
                'featureId',
                'featureName',
                'moveRegisterFeatureUp',
                'renameRegisterFeature',
                'deleteRegisterFeature'
            ],
            proxy: {
                type: 'ajax',
                actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                reader: {
                    type: 'json',
                    transform: {
                        fn: self._transformRegisterFeatureData
                    }
                },
                extraParams: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName
                },
                url: '/?c=DSSEditorRegisterFeature&m=getRegisterFeatures'
            }
        });

        return new Ext6.form.FormPanel({
            id: 'genRegisterFeaturesPanel',
            bodyPadding: 32,
            border: false,
            width: '100%',
            items: [

                new Ext6.form.FormPanel({
                    border: false,
                    html: ''
                        + '<h1>Модуль <i>"' + diagModule.moduleName + '"</i></h1>'
                        + '<h2>Регистр <i>"' + register.registerName + '"</i></h2>',
                    width: '100%',
                    style: 'margin-bottom: 32px;'
                }),

                new Ext6.Button({
                    text: 'Вернуться к списку регистров',
                    style: 'margin: 32px 24px;',
                    handler: onReturn
                }),

                new Ext6.Button({
                    text: 'Дать/отозвать право просмотра данных регистра',
                    style: 'margin: 32px 24px;',
                    handler: onRegisterDataAccessRightEditButton
                }),

                new Ext6.Button({
                    text: 'Создать новое поле регистра',
                    style: 'margin: 32px 24px;',
                    handler: self._onCreateRegisterFeatureButton.bind(
                            self,
                            loadMask,
                            diagModule,
                            register,
                            featuresStore,
                            onFailure)
                }),

                new Ext6.grid.GridPanel({
                    store: featuresStore,
                    id: 'registerFeaturesGrid',
                    autoHeight: true,
                    width: '100%',
                    title: 'Список полей регистра',
                    selModel: 'cellmodel',
                    viewConfig: {
                        deferEmptyText: false,
                        emptyText: '<div style="text-align: center;">'
                            + 'Список пуст'
                            + '</div>'
                    },
                    columns: [{
                        dataIndex: 'featureId',
                        name: 'registerFeatureId',
                        tooltip: 'Идентификатор поля регистра',
                        tdCls: 'nameTdCls',
                        header: 'Идентификатор',
                        flex: 1,
                        align: 'center',
                        renderer: self._addTip
                    }, {
                        dataIndex: 'featureName',
                        tdCls: 'nameTdCls',
                        name: 'registerFeatureName',
                        header: 'Название',
                        tooltip: 'Название поля регистра',
                        flex: 5,
                        align: 'center',
                        renderer: self._addTip
                    }, {
                        dataIndex: 'moveRegisterFeatureUp',
                        name: 'moveRegisterFeatureUp',
                        tdCls: 'nameTdCls',
                        header: 'Изменить позицию поля регистра',
                        tooltip: 'Вверх',
                        flex: 1,
                        align: 'center',
                        renderer: self._addTip
                    }, {
                        dataIndex: 'renameRegisterFeature',
                        name: 'renameRegister',
                        tdCls: 'nameTdCls',
                        header: 'Переименовать',
                        tooltip: 'Переименовать поле регистра',
                        flex: 1,
                        align: 'center',
                        renderer: self._addTip
                    }, {
                        dataIndex: 'deleteRegisterFeature',
                        name: 'deleteRegister',
                        tdCls: 'nameTdCls',
                        header: 'Удалить',
                        tooltip: 'Удалить поле регистра',
                        flex: 1,
                        align: 'center',
                        renderer: self._addTip
                    }],
                    listeners: {
                        cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                            self._featureActionHandlers.onCellClick(
                                    loadMask,
                                    diagModule,
                                    register,
                                    featuresStore,
                                    onRegisterFeatureSelected,
                                    onFailure,
                                    cellIndex,
                                    record);
                        }
                    }

                })
            ]
        });
    },


    /**
     * Рендерер строки грида: добавить всплывающую подсказку и отформатировать
     *
     */
    _addTip: function(val, metaData, record) {
        if (val) {
            metaData.tdAttr = 'data-qtip="' + val + '"';
        }
        metaData.tdStyle = 'vertical-align: middle;cursor:pointer;'
        return '<div style="white-space: normal;">' + val + '</div>';
    },


    /**
     * Удалить форму списка регистров с родительского компонента
     *
     * @param form: Ext6.Component
     */
    remove: function(form) {
        var registerFeaturesPanel = form.getComponent('genRegisterFeaturesPanel');
        if (registerFeaturesPanel) {
            form.remove(registerFeaturesPanel);
        }
    },


    /**
     * Обработчик нажатия на кнопку создания нового поля регистра
     *
     * @param data: {moduleData, registerData}
     * @param onFeatureCreated: function
     * @param onFailure: function
     */
    _onCreateRegisterFeatureButton: function(loadMask, diagModule, register, featuresStore, onFailure) {
        var self = this;

        Ext6.Msg.prompt(
            'Создание нового поля регистра',
            'Введите название: ',
            function(btn, text) {
                if ((btn == 'ok') && (text)) {
                    self._createRegisterFeature(
                            loadMask,
                            diagModule,
                            register,
                            text,
                            function onSuccess() {
                                featuresStore.reload();
                            },
                            onFailure);
                }
            }
        );
    },


    /**
     * Обработчики нажания на ячейку таблицы
     */
    _featureActionHandlers: {


        /**
         * Нажатие на ячейку
         *
         */
        onCellClick: function(
            loadMask,
            diagModule,
            register,
            featuresStore,
            onRegisterFeatureSelected,
            onFailure,
            cellIndex,
            record
        ) {
            var self = this;
            var clickedColumnName = record.getFields()[cellIndex].getName();
            var registerFeature = {
                featureId: record.get('featureId'),
                featureName: record.get('featureName').trim()
            };

            switch (clickedColumnName) {

                case 'featureId': {
                    onRegisterFeatureSelected(registerFeature);
                    break;
                }

                case 'featureName': {
                    onRegisterFeatureSelected(registerFeature);
                    break;
                }

                case 'moveRegisterFeatureUp': {
                    self._onMoveRegisterFeature(loadMask, diagModule, register, registerFeature, featuresStore, onFailure);
                    break;
                }

                case 'renameRegisterFeature': {
                    self._onRenameRegisterFeature(loadMask, diagModule, register, registerFeature, featuresStore, onFailure);
                    break;
                }

                case 'deleteRegisterFeature': {
                    self._onDeleteRegisterFeature(loadMask, diagModule, register, registerFeature, featuresStore, onFailure);
                    break;
                }

                default: {
                    // no action
                }
            }
        },


        /**
         * Обработчик нажатия на ячейку Вверх или на ячейку Вниз
         *
         * @param registerFeatureForm: {} - форма поля регистра
         * @param data: {moduleData, registerData}
         * @param direction: string ['up', 'down']
         * @param registerFeature: {}
         * @param onFeatureMoved: function
         * @param onFailure: function
         */
        _onMoveRegisterFeature: function(loadMask, diagModule, register, registerFeature, featuresStore, onFailure) {
            this._moveRegisterFeature(
                loadMask,
                diagModule,
                register,
                registerFeature,
                function onSuccess() {
                    featuresStore.reload();
                },
                onFailure);
        },


        /**
         * Переименовать поле регистра
         *
         * @param registerFeatureForm: {} - форма поля регистра
         * @param data: {moduleData, registerData}
         * @param registerFeature: {} - данные поля регистра
         * @param onFeatureRenamed: function
         * @param onFailure: function
         */
        _onRenameRegisterFeature: function(loadMask, diagModule, register, registerFeature, featuresStore, onFailure) {
            var self = this;
            Ext6.Msg.prompt(
                'Переименовать поле регистра',
                'Введите новое название: ',
                function(btn, text) {
                    if ((btn == 'ok') && (text)) {
                        var newRegisterFeature = {
                            featureId: registerFeature.featureId,
                            featureName: text
                        };
                        self._updateRegisterFeature(
                            loadMask,
                            diagModule,
                            register,
                            newRegisterFeature,
                            function onSuccess() {
                                featuresStore.reload();
                            },
                            onFailure);
                    }
                },
                null, //scope
                false, //multiline
                registerFeature.featureName //default text
            );
        },


        /**
         * Удалить поле регистра
         *
         * @param registerFeatureForm: {} - форма поля регистра
         * @param data: {moduleData, registerData}
         * @param registerFeature: {} - данные поля регистра
         * @param onFeatureRenamed: function
         * @param onFailure: function
         */
        _onDeleteRegisterFeature: function(loadMask, diagModule, register, registerFeature, featuresStore, onFailure) {
            var self = this;
            Ext6.Msg.confirm(
                'Удалить поле регистра',
                'Удалить поле регистра <i>' + registerFeature.featureName + '</i>?',
                function(btn) {
                    if (btn === 'yes') {
                        self._deleteRegisterFeature(
                            loadMask,
                            diagModule,
                            register,
                            registerFeature,
                            function onSuccess() {
                                featuresStore.reload();
                            },
                            onFailure);
                    }
                }
            );
        },


        /**
         * Изменить название поля регистра
         *
         * @param loadMask: Ext6.Component - индикатор ожидания для всего окна
         * @param moduleData: object - модуль
         * @param registerData: object - регистр
         * @return Promise
         */
        _updateRegisterFeature: function(loadMask, diagModule, register, registerFeature, onSuccess, onFailure) {
            loadMask.show();

            Ext6.Ajax.request({
                url: '/?c=DSSEditorRegisterFeature&m=patchRegisterFeature',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName,
                    registerFeatureId: registerFeature.featureId,
                    registerFeatureName: registerFeature.featureName
                },
                success: function (response, opts) {
                    var data;
                    loadMask.hide();
                    try {
                        data = JSON.parse(response.responseText);
                    } catch(e) {
                        onFailure('msg ' + e);
                        return;
                    }
                    if (data[0].featureId !== registerFeature.featureId) {
                        onFailure(data[0]);
                        return;
                    }
                    onSuccess(data);
                },
                failure: function (response, opts) {
                    loadMask.hide();
                    onFailure('Request error');
                }
            });
        },


        /**
         * Удалить поле регистра
         *
         * @param loadMask: Ext6.Component - индикатор ожидания для всего окна
         * @param moduleData: object - модуль
         * @param registerData: object - регистр
         * @return Promise
         */
        _deleteRegisterFeature: function(loadMask, diagModule, register, registerFeature, onSuccess, onFailure) {
            loadMask.show();

            Ext6.Ajax.request({
                url: '/?c=DSSEditorRegisterFeature&m=deleteRegisterFeature',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName,
                    registerFeatureId: registerFeature.featureId,
                    registerFeatureName: registerFeature.featureName
                },
                success: function (response, opts) {
                    var data;
                    loadMask.hide();
                    try {
                        data = JSON.parse(response.responseText);
                    } catch(e) {
                        onFailure(e);
                        return;
                    }
                    if (data[0] !== 'removed') {
                        onFailure();
                        return;
                    }
                    onSuccess();
                },
                failure: function (response, opts) {
                    loadMask.hide();
                    onFailure('Network error');
                }
            });
        },


        /**
         * Поднять или опустить поле регистра
         *
         * @param loadMask: Ext6.Component - индикатор ожидания для всего окна
         * @param moduleData: object - модуль
         * @param registerData: object - регистр
         * @return Promise
         */
        _moveRegisterFeature: function(loadMask, diagModule, register, registerFeature, onSuccess, onFailure) {
            loadMask.show();

            Ext6.Ajax.request({
                url: '/?c=DSSEditorRegisterFeature&m=moveRegisterFeature',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName,
                    registerFeatureId: registerFeature.featureId,
                    registerFeatureName: registerFeature.featureName,
                    direction: 'up'
                },
                success: function (response, opts) {
                    var data;
                    loadMask.hide();

                    try {
                        data = JSON.parse(response.responseText);
                    } catch(e) {
                        onFailure(e);
                        return;
                    }
                    if ((data[0] !== 'up') && (data[0] !== 'First')) {
                        onFailure(data[0]);
                    }

                    onSuccess();
                },
                failure: function (response, opts) {
                    loadMask.hide();
                    onFailure();
                }
            });
        }
    }
});
