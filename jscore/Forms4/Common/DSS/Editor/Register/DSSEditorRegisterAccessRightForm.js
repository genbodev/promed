/**
 * Форма редактирования состава группы, имеющей доступ к просмотру данных регистра
 *
 * Редактор структуры регистров
 *
 * swDSSEditorWindow - окно для редактирования опросников
 *   для сбора структурированной медицинской информации и поддержки принятия решений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.DSS
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Yaroslav Mishlanov <ya.mishlanov@swan-it.ru>
 * @since        11.12.2018
 * @version      11.12.2018
 */
Ext6.define('common.DSS.Editor.Register.DSSEditorRegisterAccessRightForm', {


    /**
     * Отобразить форму редактирования списка медработников, имеющих доступ
     *     к данным клинического регистра
     *
     */
    show: function(loadMask, form, diagModule, register, onReturn, onFailure) {
        var self = this;

        var viewersStore =  new Ext6.data.Store({
            autoLoad: true,
            pageSize: 2,
            fields: ['doctorId', 'doctorLogin'],
            proxy: {
                type: 'ajax',
                actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                reader: new Ext6.data.JsonReader({
                    rootProperty: 'doctors',
                    totalProperty: 'total',
                    transform: function(data) {
                        return data[0]; // хук для корректной передчи названий полей
                    }
                }),
                extraParams: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName
                },
                url: '/?c=DSSEditorRegister&m=getRegisterViewers'
            }
        });

        form.add(new Ext6.form.FormPanel({
            id: 'genARForm',
            border: false,
            bodyPadding: 32,
            width: '100%',
            items: [

                new Ext6.form.FormPanel({
                    border: false,
                    id: 'captionPanel',
                    html: ''
                        + '<h1>Модуль <i>"' + diagModule.moduleName + '"</i></h1>'
                        + '<h2>Клинический регистр <i>"' + register.registerName + '"</i></h2>',
                    width: '100%',
                    style: 'margin-bottom: 32px;'
                }),

                new Ext6.Button({
                    text: 'Вернуться к списку регистров',
                    handler: onReturn
                }),

                new Ext6.grid.GridPanel({
                    store: viewersStore,
                    bbar: {
                        xtype: 'pagingtoolbar',
                        displayInfo: true
                    },
                    viewConfig: {
                        deferEmptyText: false,
                        emptyText: '<div style="text-align: center;">Список пуст</div>'
                    },
                    id: 'registerViewersGrid',
                    autoHeight: true,
                    width: '100%',
                    title: 'Группа пользователей, имеющих право просмотра данных регистра',
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

                self._changeRegisterAccessRightForm.makeForm(loadMask, diagModule, register, viewersStore, onFailure)
            ]
        }));
    },


    remove: function(form) {
        const genARForm = form.getComponent('genARForm');
        if (genARForm) {
            form.remove(genARForm);
        }
    },

    /**
     * Форма изменения права редактирования модулей
     *
     */
    _changeRegisterAccessRightForm: {


        /**
         * Добавить (удалить) медработника в группу экспертов, ответственных за модуль
         *
         * @param method putModuleEditor | deleteModuleEditor
         */
        _changeRegisterDataAccessRight: function(loadMask, diagModule, register, grantee, method, onSuccess, onFailure) {
            if ((method !== 'putRegisterViewer') && (method !== 'deleteRegisterViewer')) {
                onFailure('wrong method');
                return;
            }
            loadMask.show();
            Ext6.Ajax.request({
                url: `/?c=DSSEditorRegister&m=${method}`,
                params: {
                    doctorId: grantee.doctorId,
                    doctorLogin: grantee.doctorLogin,
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    registerId: register.registerId,
                    registerName: register.registerName,
                },
                success: function (response, opts) {
                    loadMask.hide();
                    onSuccess();
                },
                failure: function (response, opts) {
                   loadMask.hide();
                   onFailure('Network error');
                }
            });
        },


        /**
         * Сформировать стор для списка найденных по логину медработников
         *
         * @return {Ext.data.Store}
         */
        _makeFoundDoctorsStore: function(creatorsStore) {
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

                            var editorRows = creatorsStore.getData().getRange();

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
         * Сформировать панель изменения прав медработников на создание модулей
         *
         * @param {Ext.LoadMask} loadMask
         * @param {Ext.data.Store} creatorsStore - стор для списка медработников,
         *     имеющих право создания модулей (чтобы обновлять его)
         * @param {function(string)} onFailure - страндартный обработчик ошибочной ситуации
         * @return {Ext.form.FormPanel}
         */
        makeForm: function(loadMask, diagModule, register, creatorsStore, onFailure) {
            var self = this;
            var lastNameInput;
            var foundDoctorsStore;

            lastNameInput = new Ext6.form.field.Text({
                emptyText: 'Логин медработника',
                height: 32,
                flex: 2
            });

            foundDoctorsStore = self._makeFoundDoctorsStore(creatorsStore);

            return new Ext6.form.FormPanel({
                title: 'Изменить право медработника на создание модулей',
                borders: false,
                layout: 'hbox',
                bodyPadding: 16,
                style: 'margin-top: 32px',
                items: [
                    lastNameInput,

                    new Ext6.Button({
                        text: 'Найти',
                        height: 32,
                        flex: 1,
                        handler: function() {
                            self._onSearchButton(
                                    onFailure,
                                    lastNameInput,
                                    foundDoctorsStore);
                        }
                    }),

                    new Ext6.form.FormPanel({
                        border: false,
                        style: 'margin-left: 16px;',
                        flex: 3,
                        items: [
                            new Ext6.grid.GridPanel({
                                title: 'Результаты поиска',
                                width: '100%',
                                hidden: false,
                                selModel: 'cellmodel',
                                store: foundDoctorsStore,
                                bbar: {
                                    xtype: 'pagingtoolbar',
                                    displayInfo: true
                                },
                                viewConfig: {
                                    deferEmptyText: false,
                                    emptyText: '<div style="text-align: center;">Список пуст</div>'
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
                                    header: 'Право создания модулей',
                                    flex: 2,
                                    align: 'center'
                                }, {
                                    dataIndex: 'action',
                                    tdCls: 'nameTdCls',
                                    header: 'Изменить право создания модулей',
                                    flex: 1,
                                    align: 'center'
                                }],
                                listeners: {
                                    cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                                        self._onCellClick(
                                                loadMask,
                                                diagModule,
                                                register,
                                                creatorsStore,
                                                onFailure,
                                                foundDoctorsStore,
                                                cellIndex,
                                                record);
                                    }
                                }
                            })
                        ]
                    })
                ]
            });
        },


        /**
         * Обработчик нажания на кнопку поиска экспертов
         *
         */
        _onSearchButton: function(onFailure, lastNameInput, foundDoctorsStore) {
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
         * Обработчик нажания на ячейку таблицы с результатами поиска экспертов -
         *   добавление или удаление права создания модулей,
         *   в зависимости от того, было оно у эксперта или нет
         *
         */
        _onCellClick: function(loadMask, diagModule, register,creatorsStore, onFailure, foundDoctorsStore, cellIndex, record) {
            var self = this;
            var clickedColumnName;
            var grantee;
            var method;

            clickedColumnName = record.getFields()[cellIndex].getName();
            if (clickedColumnName !== 'action') {
                return;
            }

            grantee = {
                doctorId: record.get('doctorId'),
                doctorLogin: record.get('doctorLogin'),
                doctorHasRight2CreateModules: (record.get('doctorHasRight2CreateModules') === 'Есть')
            };

            method = (record.get('doctorHasAR') === 'Есть')
                ? 'deleteRegisterViewer'
                : 'putRegisterViewer';

            self._changeRegisterDataAccessRight(
                loadMask,
                diagModule,
                register,
                grantee,
                method,
                function onSuccess() {
                    creatorsStore.reload();
                    foundDoctorsStore.reload();
                },
                onFailure
            );
        }
    }
});
