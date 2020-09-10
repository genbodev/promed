/**
 * Форма просмотра и изменения экспертной группы, ответственной за модуль
 *
 * swDSSEditorWindow - окно для редактирования опросников
 *     для сбора структурированной медицинской информации
 *     и поддержки принятия решений
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
 * @since        04.06.2018
 * @version      16.04.2019
 */
Ext6.define('common.DSS.Editor.DSSEditorModuleAccessRightForm', {



    /**
     * Отобразить форму для работы с командой экспертов,
     *     ответственных за диагностический модуль
     *
     * @param {Ext.LoadMask} loadMask - страндартный индикатор ожидания
     * @param {ExtComponent} form - родительский компонент, где отобразить форму
     * @param {DiagModule} diagModule
     * @param {function()} onReturn2Questions - вернуться к форме вопросов
     * @param {function()} onFailure
     */
    show: function(loadMask, form, diagModule, onReturn2Questions, onFailure) {
        var self = this;
        var moduleARStore; // стор для списка медработников, входящих в команду, поддерживающую модуль
        var changeARForm; // форма для добавления/удаления медработников

        moduleARStore = new Ext6.data.Store({
            autoLoad: true,
            fields: ['doctorId', 'doctorLogin'],
            proxy: {
                type: 'ajax',
                actionMethods:  {
                    create: "POST",
                    read: "POST",
                    update: "POST",
                    destroy: "POST"
                },
                reader: {
                    type: 'json'
                },
                extraParams: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName
                },
                url: '/?c=DSSEditorModule&m=getModuleEditors'
            }
        });

        changeARForm = self._changeARForm.makeChangeARForm(
                loadMask,
                diagModule,
                moduleARStore,
                onFailure);

        form.add(new Ext6.form.FormPanel({
            id: 'moduleARForm',
            border: false,
            bodyPadding: 32,
            width: '100%',
            items: [

                new Ext6.form.FormPanel({
                    border: false,
                    html: '<h1>Модуль <i>' + diagModule.moduleName + '</i></h1>',
                    width: '100%',
                    style: 'margin-bottom: 32px;'
                }),

                new Ext6.Button({
                    text: 'Вернуться к редактированию опросника',
                    handler: function() {
                        self.remove(form);
                        onReturn2Questions();
                    }
                }),

                new Ext6.grid.GridPanel({
                    store: moduleARStore,
                    autoHeight: true,
                    width: '100%',
                    title: 'Группа экспертов, поддерживающих модуль',
                    columns: [{
                            dataIndex: 'doctorId',
                            header: 'Идентификатор',
                            hidden: true
                        }, {
                            dataIndex: 'doctorLogin',
                            header: 'Логин',
                            flex: 3,
                            align: 'center'
                    }]
                }),

                changeARForm

            ]
        }));
    },


    /**
     * Удалить компоненты формы с родительского компонента
     *
     * @param {Ext.Component} form - родительский компонент
     */
    remove: function(form) {
        var moduleARForm = form.getComponent('moduleARForm');
        if (moduleARForm) {
            form.remove(moduleARForm);
        }
    },


    /**
     * Форма изменения команды медработников, ответственных за модуль
     */
    _changeARForm: {


        /**
         * Добавить (удалить) медработника в группу экспертов, ответственных за модуль
         *
         * @param {Ext.LoadMask} loadMask
         * @param {DiagModule} diagModule
         * @param {Doctor} grantee
         * @param {string} method putModuleEditor | deleteModuleEditor
         * @param {function()} onSuccess
         * @param {function()} onFailure
         */
        _changeModuleAccessRight: function(loadMask, diagModule, grantee, method, onSuccess, onFailure) {
            if (
                (method !== 'putModuleEditor')
                && (method !== 'deleteModuleEditor')
            ) {
                onFailure();
                return;
            }

            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorModule&m=' + method,
                params: {
                    doctorId: grantee.doctorId,
                    doctorLogin: grantee.doctorLogin,
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


        _makeFoundDoctorsStore: function(moduleARStore) {
            return new Ext6.data.Store({
                autoLoad: false,
                fields: [
                    'doctorId',
                    'doctorLogin',
                    'doctorHasAR',
                    'action'
                ],
                pageSize: 2,
                proxy: {
                    type: 'ajax',
                    actionMethods:  {
                        create: "POST",
                        read: "POST",
                        update: "POST",
                        destroy: "POST"
                    },
                    url: '/?c=DSSEditorEditor&m=getDoctorsByLogin',
                    reader: new Ext6.data.JsonReader({
                        rootProperty: 'doctors',
                        totalProperty: 'total',
                        transform: function(data) {
                            var newDoctors = [];
                            var editorRows = moduleARStore.getData().getRange();

                            data[0].doctors.forEach(function(row) {
                                row.doctorHasAR = editorRows.reduce(function(acc, editorRow) {
                                    return (editorRow.data.doctorId === row.doctorId)
                                        ? 'Есть' : acc;
                                }, 'Нет');
                                row.action = 'Изменить';
                                newDoctors.push(row);
                            });

                            return {
                                doctors: newDoctors,
                                total: data[0].total
                            };
                        }
                    })
                }
            });
        },


        /**
         * Сформировать панель изменения команды экспертов
         *
         * @param {Ext.LoadMask} loadMask
         * @param {Ext.data.Store} moduleARStore - стор со списком медработников, входящихв команду
         * @param {function(string)} onFailure
         */
        makeChangeARForm: function(loadMask, diagModule, moduleARStore, onFailure) {
            var self = this;
            var lastNameInput;
            var foundDoctorsStore;

            lastNameInput = new Ext6.form.field.Text({
                //fieldLabel: 'Логин',
                emptyText: 'Логин медработника',
                height: 32,
                flex: 2
            });

            foundDoctorsStore = self._makeFoundDoctorsStore(moduleARStore);

            return new Ext6.form.FormPanel({
                title: 'Изменить право медработника на редактирование модуля',
                borders: false,
                layout: 'hbox',
                bodyPadding: 16,
                style: 'padding-top: 24px',
                items: [
                    lastNameInput,

                    new Ext6.Button({
                        text: 'Найти',
                        height: 32,
                        flex: 1,
                        handler: function() {
                            self._onSearchButton(
                                    lastNameInput,
                                    foundDoctorsStore,
                                    onFailure);
                        }
                    }),

                    new Ext6.grid.GridPanel({
                        title: 'Результаты поиска',
                        style: 'margin-left: 16px;',
                        width: '100%',
                        flex: 3,
                        hidden: false,
                        selModel: 'cellmodel',
                        store: foundDoctorsStore,
                        bbar: {
                            xtype: 'pagingtoolbar',
                            displayInfo: true
                        },
                        viewConfig: {
                            deferEmptyText: false,
                            emptyText: '<div style="text-align: center;">'
                                + 'Список пуст'
                                + '</div>'
                        },
                        columns: [{
                                dataIndex: 'doctorId',
                                header: 'Идентификатор',
                                hidden: true
                            }, {
                                dataIndex: 'doctorLogin',
                                tdCls: 'nameTdCls',
                                header: 'Логин',
                                flex: 3,
                                align: 'center'
                            }, {
                                dataIndex: 'doctorHasAR',
                                tdCls: 'nameTdCls',
                                header: 'Право редактирования модуля',
                                flex: 2,
                                align: 'center'
                            }, {
                                dataIndex: 'action',
                                tdCls: 'nameTdCls',
                                header: 'Изменить',
                                flex: 1,
                                align: 'center'
                        }],
                        listeners: {
                            cellclick : function(view, cell, cellIndex, record, row, rowIndex, e) {
                                self._onCellClick(
                                        loadMask,
                                        moduleARStore,
                                        foundDoctorsStore,
                                        diagModule,
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
         * Обработчик нажатия на кнопку поиска эксперта по логину
         *
         * По нажатию на кнопку поиска медработника по логину
         * нужно взять логин из поля ввода,
         * выполнить запрос к серверу и загрузить полученные данные в стор
         * @param {Ext.form.field.Text} lastNameInput
         * @param {Ext.data.Store} foundDoctorsStore
         * @param {function(string)} onFailure
         */
        _onSearchButton: function(lastNameInput, foundDoctorsStore, onFailure) {
            var doctorLogin = lastNameInput.getValue();
            if (!doctorLogin) {
                return;
            }
            if (doctorLogin.length < 3) {
                onFailure('Логин должен содержать не менее трёх символов');
            }

            foundDoctorsStore.getProxy().setExtraParams({
                filter: 'login',
                doctorLogin: doctorLogin
            });

            foundDoctorsStore.load({
                callback: function(records, operation, success) {
                    if (!success) {
                        onFailure();
                    }
                }
            });
        },


        /**
         * Обработчик нажатия на ячейку таблицы - изменение уровня доступа эксперта
         *
         */
        _onCellClick: function(
            loadMask,
            moduleARStore,
            foundDoctorsStore,
            diagModule,
            onFailure,
            cellIndex,
            record
        ) {
            var self = this;
            var clickedColumnName = record.getFields()[cellIndex].getName();
            var grantee = {
                doctorId: record.get('doctorId'),
                doctorLogin: record.get('doctorLogin')
            };
            var method = (record.get('doctorHasAR') === 'Есть')
                ? 'deleteModuleEditor' : 'putModuleEditor';

            if (clickedColumnName !== 'action') {
                return;
            }

            self._changeModuleAccessRight(
                loadMask,
                diagModule,
                grantee,
                method,
                function onSuccess() {
                    moduleARStore.reload();
                    foundDoctorsStore.reload();
                },
                onFailure
            );
        }
    }
});
