/**
 * Форма для редактирования списка регистров
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
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Yaroslav Mishlanov <ya.mishlanov@swan-it.ru>
 * @since        10.12.2018
 * @version      18.04.2019
 */
Ext6.define('common.DSS.Editor.Register.DSSEditorRegisterForm', {


    /**
     * Создать регистр
     *
     * @param loadMask: Ext6.Component - индикатор ожидания для всего окна
     * @param moduleData: object - модуль
     * @param registerName: string - название создаваемого регистра
     */
    _createRegister: function(loadMask, diagModule, registerName, onSuccess, onFailure) {
        loadMask.show();

        Ext6.Ajax.request({
            url: '/?c=DSSEditorRegister&m=postRegister',
            params: {
                moduleId: diagModule.moduleId,
                moduleName: diagModule.moduleName,
                registerName: registerName
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
                if ((typeof data[0]) === 'string') {
                    onFailure(data[0]);
                    return;
                }
                onSuccess(data);
            },
            failure: function(response, opts) {
                loadMask.hide();
                onFailure('Network error');
            }
        });
    },


    /**
     * Отобразить форму списка регистров
     *
     *  @param form: Ext6.form.FormPanel - панель для отображения формы
     *  @param function onRegisterDataAccessRightEditButton - callback-функция,
     *      которую нужно выполнить при нажатии на кнопку перехода к работе с правом создания модулей
     *  @param function onSelect - callback-функция, которую нужно выполнить,
     *      когда можуль будет выбран (и успешно заблокирован)
     *  @param function onFailure - callback-функция, которую нужно выполнить
     *      в случае непредвиденной ошибки
     */
    show: function (loadMask, form, diagModule, onReturn, onRegisterSelected, onFailure) {
        var self = this;

        form.add(self._makeRegisterPanel(
                loadMask,
                form,
                diagModule,
                onReturn,
                onRegisterSelected,
                onFailure));
    },


    /**
     * Преобразовать данные регистра, полученные от АПИ сервера, в строку таблицы регистров
     *
     */
    _transformRegisterData: function(onFailure, data) {
        // особые ответы сервера
        if ((typeof data[0]) === 'string') {
            if (data[0] === 'empty') {
                return []; // трюк для передачи пустого списка
            } else {
                onFailure(data[0]); // вернулась ошибка
                return [];
            }
        }

        return data.map(function(row) {
            row.renameRegister = 'Переименовать';
            row.deleteRegister = 'Удалить';

            return row;
        });
    },


    /**
     * Создать форму списка регистров
     *
     */
    _makeRegisterPanel: function(loadMask, form, diagModule, onReturn, onRegisterSelected, onFailure) {
        var self = this;
        var registersStore = self._makeRegistersStore(diagModule, onFailure);

        return new Ext6.form.FormPanel({
            id: 'genRegistersPanel',
            bodyPadding: 32,
            border: false,
            width: '100%',
            items: [

                new Ext6.form.FormPanel({
                    border: false,
                    html: '<h1>Модуль <i>' + diagModule.moduleName + '</i></h1>',
                    width: '100%',
                    style: 'margin-bottom: 32px;'
                }),

                new Ext6.Button({
                    text: 'Вернуться к опроснику',
                    style: 'margin: 32px 24px;',
                    handler: onReturn
                }),

                new Ext6.Button({
                    text: 'Создать новый регистр',
                    id: 'createModuleButton',
                    style: 'margin: 32px 24px;',
                    handler: function() {
                        self._onCreateRegisterButton(
                                loadMask,
                                diagModule,
                                registersStore,
                                onFailure);
                    }
                }),

                new Ext6.grid.GridPanel({
                    store: registersStore,
                        autoHeight: true,
                        width: '100%',
                        title: 'Список регистров',
                        selModel: 'cellmodel',
                        viewConfig: {
                            deferEmptyText: false,
                            emptyText: '<div style="text-align: center;">'
                                + 'Список пуст'
                                + '</div>'
                        },
                        columns: [{
                            dataIndex: 'registerId',
                            name: 'registerId',
                            tdCls: 'nameTdCls',
                            header: 'Идентификатор',
                            flex: 1,
                            align: 'center'
                        }, {
                            dataIndex: 'registerName',
                            tdCls: 'nameTdCls',
                            name: 'registerName',
                            header: 'Название',
                            flex: 5,
                            align: 'center'
                        }, {
                            dataIndex: 'renameRegister',
                            name: 'renameRegister',
                            tdCls: 'nameTdCls',
                            header: 'Переименовать регистр',
                            flex: 1,
                            align: 'center'
                        }, {
                            dataIndex: 'deleteRegister',
                            name: 'deleteRegister',
                            tdCls: 'nameTdCls',
                            header: 'Удалить регистр',
                            flex: 1,
                            align: 'center'
                        }
                    ],
                    listeners: {
                        cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                            self._moduleActionHandlers.onCellClick(
                                    loadMask,
                                    diagModule,
                                    onRegisterSelected,
                                    registersStore,
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
     * Удалить форму списка регистров с родительского компонента
     *
     * @param form: Ext6.Component
     */
    remove: function(form) {
        const registersPanel = form.getComponent('genRegistersPanel');
        if (registersPanel) {
            form.remove(registersPanel);
        }
    },


    /**
     * Сформирвоать стор для списка регистров
     *
     */
    _makeRegistersStore: function(diagModule, onFailure) {
        var self = this;

        return new Ext6.data.Store({
            autoLoad: true,
            fields: [
                'registerId',
                'registerName',
                'renameRegister',
                'deleteRegister'
            ],
            proxy: {
                type: 'ajax',
                actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                reader: {
                    type: 'json',
                    transform: {
                        fn: self._transformRegisterData.bind(self, onFailure)
                    }
                },
                extraParams: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName
                },
                url: '/?c=DSSEditorRegister&m=getRegisters'
            }
        });
    },


    /**
     * Обработчик нажатия на кнопку создания нового модуля
     *
     */
    _onCreateRegisterButton: function(loadMask,diagModule, registersStore, onFailure) {
        var self = this;

        Ext6.Msg.prompt(
            'Создание нового регистра',
            'Введите название: ',
            function(btn, text) {
                if ((btn == 'ok') && (text)) {
                    self._createRegister(
                            loadMask,
                            diagModule,
                            text,
                            function onSuccess() {
                                registersStore.reload();
                            },
                            onFailure);
                }
            }
        );
    },


    /**
     * Обработчики нажания на ячейку таблицы модулей
     */
    _moduleActionHandlers: {

        /**
         * Нажатие на ячейку
         *
         */
        onCellClick: function(loadMask, diagModule, onRegisterSelected, registersStore, onFailure, cellIndex, record) {
            var self = this;
            var clickedColumnName = record.getFields()[cellIndex].getName();
            var register = {
                registerId: record.get('registerId'),
                registerName: record.get('registerName').trim()
            };

            switch (clickedColumnName) {

                case 'registerId': {
                    onRegisterSelected(register);
                    break;
                }

                case 'registerName': {
                    onRegisterSelected(register);
                    break;
                }

                case 'renameRegister': {
                    self._onRenameRegister(loadMask, diagModule, register, registersStore, onFailure);
                    break;
                }

                case 'deleteRegister': {
                    self._onDeleteRegister(loadMask, diagModule, register, registersStore, onFailure);
                    break;
                }

                default: {
                    // no action
                }
            }
        },


        /**
         * Обрабочик нажатия на кнопку --Переименовать регистр--
         *
         */
        _onRenameRegister: function(loadMask, diagModule, register, registersStore, onFailure) {
            var self = this;

            Ext6.Msg.prompt(
                'Переименовать регистр',
                'Введите новое название: ',
                function(btn, text) {
                    if ((btn == 'ok') && (text)) {
                        var newRegister = {
                            registerId: register.registerId,
                            registerName: text
                        };
                        self._updateRegister(
                                loadMask,
                                diagModule,
                                newRegister,
                                function onSuccess() {
                                    registersStore.reload();
                                },
                                onFailure);
                    }
                },
                null,//scope
                false,//multiline
                register.registerName//default text
            );
        },


        /**
         * Обрабочик нажатия на кнопку --Удалить регистр--
         *
         */
        _onDeleteRegister: function(loadMask, diagModule, register, registersStore, onFailure) {
            var self = this;

            Ext6.Msg.confirm(
                'Удалить регистр',
                'Удалить регистр <i>' + register.registerName + '</i>?',
                function(btn) {
                    if (btn == 'yes') {
                        self._deleteRegister(
                                loadMask,
                                diagModule,
                                register,
                                function onSuccess() {
                                    registersStore.reload();
                                },
                                onFailure);
                    }
                }
            );
        },

        /**
         * Изменить название регистра
         *
         * @param loadMaskЖ Ext6.Component - индикатор ожидания для всего окна
         * @param moduleData: object - модуль
         * @param registerData: object - регистр
         */
        _updateRegister: function(loadMask, diagModule, register, onSuccess, onFailure) {
            loadMask.show();

            Ext6.Ajax.request({
                url: '/?c=DSSEditorRegister&m=patchRegister',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName
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
                    if (data[0].registerId !== register.registerId) {
                        onFailure(data[0]);
                        return;
                    }
                    onSuccess(data);
                },
                failure: function(response, opts) {
                    loadMask.hide();
                    onFailure('Network error');
                }
            });
        },


        /**
         * Удалить регистр
         *
         * @param loadMask: Ext6.Component - индикатор ожидания для всего окна
         * @param moduleData: object - модуль
         * @param registerData: object - регистр
         */
        _deleteRegister: function(loadMask, diagModule, register, onSuccess, onFailure) {
            loadMask.show();

            Ext6.Ajax.request({
                url: '/?c=DSSEditorRegister&m=deleteRegister',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName
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
                    if (data[0] !== 'removed') {
                        onFailure();
                        return;
                    }
                    onSuccess(data);
                },
                failure: function(response, opts) {
                    loadMask.hide();
                    onFailure();
                }
            });
        }
    }
});
