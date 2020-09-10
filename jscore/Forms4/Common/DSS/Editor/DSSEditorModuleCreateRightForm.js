/**
 * Форма редактирования прав медработников на создание модулей
 *
 * swDSSEditorWindow - окно для редактирования опросников
 *     для сбора структурированной медицинской информации
 *     и поддержки принятия решений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.DSS
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Yaroslav Mishlanov <ya.mishlanov@swan-it.ru>
 * @since        06.05.2018
 * @version      16.04.2019
 */
Ext6.define('common.DSS.Editor.DSSEditorModuleCreateRightForm', {


    /**
     * Отобразить форму редактирования прав на создание модулей
     *
     * @param {Ext.LoadMask} loadMask - стандартный индикатор ожидания
     * @param {Ext.form.FormPanel} form - родительский компонент для отобржения
     * @param {function()} onReturn2Modules - возврат к форме модулей
     * @param {function(string)} onFailure - стандартный обработчик ситуации,
     *     когда что-то пошло не так
     */
    show: function(loadMask, form, onReturn2Modules, onFailure) {
        var self = this;
        var creatorsStore; // стор для списка медработников, имеющих право создания модулей
        var changeCreateModuleRightForm; // форма редактирования прав на создание модулей

        creatorsStore = new Ext6.data.Store({
            autoLoad: true,
            pageSize: 2,
            fields: ['doctorId', 'doctorLogin'],
            proxy: {
                type: 'ajax',
                actionMethods:  {
                    create: "POST",
                    read: "POST",
                    update: "POST",
                    destroy: "POST"
                },
                reader: new Ext6.data.JsonReader({
                    rootProperty: 'doctors',
                    totalProperty: 'total',
                    transform: function(data) {
                        return data[0]; // хук для корректной передчи названий полей
                    }
                }),
                extraParams: {
                    filter: 'accessLevel'
                },
                url: '/?c=DSSEditorEditor&m=getEditors'
            }
        });

        changeCreateModuleRightForm = self._changeCreateModuleRightForm.makeChangeRightForm(
                loadMask,
                creatorsStore,
                onFailure);

        form.add(new Ext6.form.FormPanel({
            id: 'genModuleCreateRightPanel', // для корректного удаления
            bodyPadding: 32,
            border: false,
            width: '100%',
            items: [
                new Ext6.Button({
                    text: 'Вернуться списку модулей',
                    style: 'margin: 32px 24px;',
                    handler: function() {
                        self.remove(form);
                        onReturn2Modules();
                    }
                }),

                new Ext6.grid.GridPanel({
                    store: creatorsStore,
                    bbar: {
                        xtype: 'pagingtoolbar',
                        displayInfo: true
                    },
                    viewConfig: {
                        deferEmptyText: false,
                        emptyText: '<div style="text-align: center;">Список пуст</div>'
                    },
                    autoHeight: true,
                    title: 'Медработники, имеющие право создания модулей',
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

                changeCreateModuleRightForm
            ]
        }));
    },


    /**
     * Удалить форму редактирования прав на создание модулей с родительской формы
     *
     * @param {Ext.Component} form - родительский компонент
     */
    remove: function(form) {
        var genModuleCreateRightPanel = form.getComponent('genModuleCreateRightPanel');
        if (genModuleCreateRightPanel) {
            form.remove(genModuleCreateRightPanel);
        }
    },


    /**
     * Форма изменения права редактирования модулей
     *
     */
    _changeCreateModuleRightForm: {


        /**
         * Добавить/отозвать право эксперта на создание модулей (ajax-запрос)
         *
         * @param Ext6.LoadMask loadMask - индикатор ожидания для всего окна
         * @param int doctorId - идентификатор эксперта, которому изменяют право создания модулей
         * @param string doctorAccessLevel - текущее состояние права создания модулей у эксперта ('Есть' | 'Нет')
         *      Если у эксперта нет права создания модулей, оно будет добавлено,
         *      если есть - отозвано
         * @param function onSuccess() - callback-функция, выполняемая в случае успеха
         * @param function onFailure()
         */
        _changeCreateModuleRight: function(loadMask, grantee, onSuccess, onFailure) {
            var method = (grantee.doctorHasRight2CreateModules)
                ? 'deleteCreateModuleRight'
                : 'putCreateModuleRight';

            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorEditor&m=' + method,
                params: {
                    doctorId: grantee.doctorId,
                    doctorLogin: grantee.doctorLogin
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
         * Сформировать стор для списка найденных по логину медработников
         *
         * @return {Ext.data.Store}
         */
        _makeFoundDoctorsStore: function() {
            return new Ext6.data.Store({
                autoLoad: false,
                fields: [
                    'doctorId',
                    'doctorLogin',
                    'doctorHasRight2CreateModules',
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

                            data[0].doctors.forEach(function(row) {
                                row.doctorHasRight2CreateModules = (row.doctorHasRight2CreateModules)
                                    ? 'Есть' : 'Нет';
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
        makeChangeRightForm: function(loadMask, creatorsStore, onFailure) {
            var self = this;
            var lastNameInput;
            var foundDoctorsStore;

            lastNameInput = new Ext6.form.field.Text({
                emptyText: 'Логин медработника',
                height: 32,
                flex: 2
            });

            foundDoctorsStore = self._makeFoundDoctorsStore();

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
                                    dataIndex: 'doctorHasRight2CreateModules',
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
        _onCellClick: function(loadMask, creatorsStore, onFailure, foundDoctorsStore, cellIndex, record) {
            var self = this;
            var clickedColumnName;
            var grantee;

            clickedColumnName = record.getFields()[cellIndex].getName();
            if (clickedColumnName !== 'action') {
                return;
            }

            grantee = {
                doctorId: record.get('doctorId'),
                doctorLogin: record.get('doctorLogin'),
                doctorHasRight2CreateModules: (record.get('doctorHasRight2CreateModules') === 'Есть')
            };

            self._changeCreateModuleRight(
                loadMask,
                grantee,
                function onSuccess() {
                    creatorsStore.reload();
                    foundDoctorsStore.reload();
                },
                onFailure
            );
        }
    }
});
