/**
 * Форма редактирования рекомендаций
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
 * @since        27.08.2018
 * @version      24.04.2019
 */
Ext6.define('common.DSS.Editor.DSSEditorRecommendationForm', {


    /**
     * Выполнить ajax-запрос восстановления типа рекомендаций
     *
     */
    _restoreRecommendationType: function(loadMask, diagModule, recommendationTypeId, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorESRecommendation&m=putRecommendationType2restore',
            params: {
                moduleId: diagModule.moduleId,
                moduleName: diagModule.moduleName,
                recommendationTypeId: recommendationTypeId
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
     * Выполнить ajax-запрос восстановления рекомендации
     *
     */
    _restoreRecommendation: function(loadMask, diagModule, recommendationId, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorESRecommendation&m=putRecommendation2restore',
            params: {
                moduleId: diagModule.moduleId,
                moduleName: diagModule.moduleName,
                recommendationId: recommendationId
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
     * Отобразить форму редактирвоания рекомендаций
     *
     */
    show: function(loadMask, form, diagModule, onReturn2Balls, onFailure) {
        var self = this;
        var genRecommendationPanel; // общая панель
        var anchorRecommendationPanel; // панель дял отображения формы редактирования рекомендаций

        self.remove(form);

        anchorRecommendationPanel = new Ext6.form.FormPanel({
            border: false,
            bodyPadding: 0,
            width: '100%'
        });

        genRecommendationPanel = new Ext6.form.FormPanel({
            id: 'genRecommendationPanel',
            bodyPadding: 8,
            border: false,
            width: '100%',
            items: [
                new Ext6.form.FormPanel({
                    border: false,
                    html: '<h1>Модуль <i>"' + diagModule.moduleName + '"</i></h1>',
                    width: '100%',
                    style: 'margin-bottom: 32px;'
                }),

                new Ext6.Button({
                    text: 'Вернуться к таблице баллов',
                    handler: function() {
                        self.remove(form);
                        onReturn2Balls();
                    }
                }),

                anchorRecommendationPanel,

                new Ext6.form.FormPanel({
                    id: 'anchorRestoreRecommendationPanel',
                    border: false,
                    bodyPadding: 32,
                    width: '100%'
                })
            ]
        });
        form.add(genRecommendationPanel);

        self._recommendationPanel.show(
            loadMask,
            anchorRecommendationPanel,
            diagModule,
            self._go2RestoreRecommendationForm.bind(
                    self,
                    loadMask,
                    anchorRecommendationPanel,
                    diagModule,
                    self.show.bind(self, loadMask, form, diagModule, onReturn2Balls, onFailure),
                    onFailure),
            self._go2RestoreRecommendationTypeForm.bind(
                    self,
                    loadMask,
                    anchorRecommendationPanel,
                    diagModule,
                    self.show.bind(self, loadMask, form, diagModule, onReturn2Balls, onFailure),
                    onFailure),
            onFailure
        );
    },


    /**
     * Удалить компоненты форму рекомендаций с родительской формы
     *
     * @param {Ext.Component} form - родительский компонент
     */
    remove: function(form) {
        var genRecommendationPanel = form.getComponent('genRecommendationPanel');
        if (genRecommendationPanel) {
            form.remove(genRecommendationPanel);
        }
    },


    /**
     * панель рекомендаций
     */
    _recommendationPanel: {


        recommendationTypeActions: {

            getRecommendationTypes: function(loadMask, diagModule, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorESRecommendation&m=getRecommendationTypes',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName
                    },
                    success: function(response, opts) {
                        var data;
                        loadMask.hide();
                        data = JSON.parse(response.responseText);
                        if (data[0] === 'empty') {
                            data = [];
                        }
                        onSuccess(data);
                    },
                    failure: function (response, opts) {
                        loadMask.hide();
                        onFailure();
                    }
                });
            },


            postRecommendationType: function(loadMask, diagModule, recommendationTypeName, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorESRecommendation&m=postRecommendationType',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        recommendationTypeName: recommendationTypeName
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


            deleteRecommendationType: function(loadMask, diagModule, recommendationType, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorESRecommendation&m=deleteRecommendationType',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        recommendationTypeId: recommendationType.recommendationTypeId
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


            updateRecommendationTypeName: function(loadMask, diagModule, recommendationType, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorESRecommendation&m=updateRecommendationTypeName',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        recommendationTypeId: recommendationType.recommendationTypeId,
                        recommendationTypeName: recommendationType.recommendationTypeName
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
            }
        },


        recommendationActions: {


            postRecommendation: function(loadMask, diagModule, recommendationType, recommendationText, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorESRecommendation&m=postRecommendation',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        recommendationText: recommendationText,
                        recommendationTypeId: recommendationType.recommendationTypeId
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


            deleteRecommendation: function(loadMask, diagModule, recommendation, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorESRecommendation&m=deleteRecommendation',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        recommendationId: recommendation.recommendationId
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


            updateRecommendationText: function(loadMask, diagModule, recommendation, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorESRecommendation&m=updateRecommendationText',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule. moduleName,
                        recommendationId: recommendation.recommendationId,
                        recommendationText: recommendation.recommendationText,
                        recommendationTypeId: recommendation.recommendationTypeId
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
            }
        },


        /**
         * Обработчик нажатия на кнопку -Добавить тип рекомендаций-
         *
         */
        _addRecommendationTypeButtonHandler: function(
            loadMask,
            diagModule,
            onSuccess,
            onFailure
        ) {
            var self = this;
            Ext6.Msg.prompt(
                'Создание типа рекомендаций',
                'Введите название типа рекомендаций: ',
                function(btn, text) {
                    if ((btn == 'ok') && (text)) {
                        self.recommendationTypeActions.postRecommendationType(
                                loadMask,
                                diagModule,
                                text,
                                onSuccess,
                                onFailure);
                    }
                }
            );
        },


        /**
         * Обработчик нажатия на кнопку -Добавить рекомендацию-
         *
         */
        _addRecommendationButtonHandler: function(
            loadMask,
            diagModule,
            recommendationType,
            onSuccess,
            onFailure
        ) {
            var self = this;
            Ext6.Msg.prompt(
                'Создание рекомендации',
                'Введите текст рекомендации: ',
                function(btn, text) {
                    if ((btn == 'ok') && (text)) {
                        self.recommendationActions.postRecommendation(
                                loadMask,
                                diagModule,
                                recommendationType,
                                text,
                                onSuccess,
                                onFailure);
                    }
                }
            );
        },


        /**
         * Обработчик нажатия на кнопку --Изменить название типа рекомендаций--
         *
         */
        _renameRecommendationTypeButtonHandler: function(
            loadMask,
            diagModule,
            recommendationType,
            onSuccess,
            onFailure
        ) {
            var self = this;
            Ext6.Msg.prompt(
                'Изменить название типа рекомендаций',
                'Введите название типа рекомендаций: ',
                function(btn, text) {
                    if ((btn == 'ok') && (text)) {
                        recommendationType.recommendationTypeName = text;
                        self.recommendationTypeActions.updateRecommendationTypeName(
                                loadMask,
                                diagModule,
                                recommendationType,
                                onSuccess,
                                onFailure);
                    }
                },
                null,
                false,
                recommendationType.recommendationTypeName
            );
        },


        /**
         * Обработчик нажатия на кнопку --Удалить тип рекомендаций--
         *
         */
        _removeRecommendationTypeButtonHandler: function(
            loadMask,
            diagModule,
            recommendationType,
            onSuccess,
            onFailure
        ) {
            var self = this;
            Ext6.Msg.confirm(
                'Удаление типа рекомендации',
                'Удалить тип рекомендаций?',
                function(btn, text) {
                    if (btn == 'yes') {
                        self.recommendationTypeActions.deleteRecommendationType(
                                loadMask,
                                diagModule,
                                recommendationType,
                                onSuccess,
                                onFailure);
                    }
                }
            );
        },


        /**
         * Отобразить панель рекомендаций
         *
         */
        show: function(
            loadMask,
            anchorRecommendationPanel,
            diagModule,
            go2RestoreRecommendationForm,
            go2RestoreRecommendationTypeForm,
            onFailure
        ) {
            var self = this;

            var recommendationPanel = new Ext6.form.FormPanel({
                id: 'recommendationPanel',
                title: 'Список рекомендаций',
                border: true,
                bodyPadding: 8,
                width: '100%',
                items: [
                    //recommendationPanelsContainer
                ],
                dockedItems: [{
                    xtype: 'toolbar',
                    scrollable: true,
                    dock: 'top',
                    items: [
                        new Ext6.Button({
                            text: 'Добавить тип рекомендаций',
                            handler: self._addRecommendationTypeButtonHandler.bind(
                                    self,
                                    loadMask,
                                    diagModule,
                                    function onSuccess() {
                                        self.remove(anchorRecommendationPanel);
                                        self.show(
                                                loadMask,
                                                anchorRecommendationPanel,
                                                diagModule,
                                                go2RestoreRecommendationForm,
                                                go2RestoreRecommendationTypeForm,
                                                onFailure);
                                    },
                                    onFailure)
                        }),

                        new Ext6.Button({
                            text: 'Восстановить удалённый тип рекомендаций',
                            handler: function() {
                                go2RestoreRecommendationTypeForm();
                            }
                        }),

                        new Ext6.Button({
                            text: 'Восстановить удалённую рекомендацию',
                            handler: function() {
                                go2RestoreRecommendationForm();
                            }
                        })
                    ]
                }]
            });

            anchorRecommendationPanel.add(recommendationPanel);

            self.recommendationTypeActions.getRecommendationTypes(
                    loadMask,
                    diagModule,
                    function onSuccess(recommendationTypes) {
                        recommendationTypes.forEach(function(recommendationType) {
                            recommendationPanel.add(
                                self._makeRecommendations4TypeGrid(
                                        loadMask,
                                        recommendationPanel,
                                        diagModule,
                                        recommendationType,
                                        self._makeRecommendationStore(diagModule, recommendationType),
                                        function onReload() {
                                            self.remove(anchorRecommendationPanel);
                                            self.show(
                                                    loadMask,
                                                    anchorRecommendationPanel,
                                                    diagModule,
                                                    go2RestoreRecommendationForm,
                                                    go2RestoreRecommendationTypeForm,
                                                    onFailure);
                                        },
                                        onFailure)
                            );
                        });
                    },
                    onFailure);
        },


        /**
         * Сформировать грид для рекомендаций одного типа
         *
         */
        _makeRecommendations4TypeGrid: function(loadMask, recommendationPanel, diagModule, recommendationType, recommendations4TypeStore, onReload, onFailure) {
            var self = this;

            return new Ext6.grid.GridPanel({
                title: recommendationType.recommendationTypeName,
                //bodyPadding: 8,
                autoHeight: true,
                store: recommendations4TypeStore,
                hideHeaders: true,
                selModel: 'cellmodel',
                viewConfig: {
                    deferEmptyText: false,
                    emptyText: '<div style="text-align: center;">'
                        + 'Список пуст'
                        + '</div>'
                },
                columns: [{
                        dataIndex: 'text',
                        tdCls: 'nameTdCls',
                        //header: 'Текст рекомендации',
                        flex: 3,
                        align: 'center',
                        renderer: self._addTip.bind(self, false)
                    }, {
                        //text: 'Действия',
                        flex: 2,
                        columns: [{
                                dataIndex: 'action1',
                                tdCls: 'nameTdCls',
                                //header: 'Изменить текст',
                                //tooltip: 'Изменить текст',
                                flex: 1,
                                align: 'center',
                                renderer: self._addTip.bind(self, true)
                            }, {
                                dataIndex: 'action2',
                                tdCls: 'nameTdCls',
                                //header: 'Изменить текст',
                                //tooltip: 'Изменить тип',
                                flex: 1,
                                align: 'center',
                                renderer: self._addTip.bind(self, true)
                            }, {
                                dataIndex: 'action3',
                                tdCls: 'nameTdCls',
                                //header: 'Удалить',
                                //tooltip: 'Удалить',
                                flex: 1,
                                align: 'center',
                                renderer: self._addTip.bind(self, true)
                        }]
                }],
                listeners: {
                    cellclick : function(view, cell, cellIndex, record, row, rowIndex, e) {
                        var clickedColumnName = record.getFields()[cellIndex].getName();
                        var selectedRecommendation;

                        selectedRecommendation = {
                            recommendationId: record.get('recommendationId'),
                            recommendationText: record.get('recommendationText'),
                            recommendationTypeId: record.get('recommendationTypeId')
                        };
                        self._onRecommendationRowCellClick(
                                loadMask,
                                recommendationPanel,
                                diagModule,
                                function onRecommendationUpdated() {
                                    recommendations4TypeStore.reload();
                                },
                                onFailure,
                                onReload,
                                selectedRecommendation,
                                clickedColumnName);
                    }
                },
                dockedItems: [{
                    xtype: 'toolbar',
                    scrollable: true,
                    dock: 'top',
                    items: [
                        new Ext6.Button({
                            text: 'Добавить рекомендацию',
                            handler: self._addRecommendationButtonHandler.bind(
                                    self,
                                    loadMask,
                                    diagModule,
                                    recommendationType,
                                    function onSuccess() {
                                        recommendations4TypeStore.reload();
                                    },
                                    onFailure)
                    }),
                    new Ext6.Button({
                        text: 'Изменить название типа рекомендаций',
                        handler: self._renameRecommendationTypeButtonHandler.bind(
                                self,
                                loadMask,
                                diagModule,
                                recommendationType,
                                onReload.bind(self),
                                onFailure)
                    }),
                    new Ext6.Button({
                        text: 'Удалить тип рекомендаций',
                        handler: self._removeRecommendationTypeButtonHandler.bind(
                                self,
                                loadMask,
                                diagModule,
                                recommendationType,
                                onReload.bind(self),
                                onFailure)
                    })]
                }]
            });
        },


        /**
         * Сформировать стор для рекомендаций
         *
         */
        _makeRecommendationStore: function(diagModule, recommendationType) {
            var self = this;
            return new Ext6.data.Store({
                autoLoad: true,
                fields: [
                    'text',
                    'action1',
                    'action2',
                    'action3'
                ],
                proxy: {
                    type: 'ajax',
                    actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                    reader: {
                        type: 'json',
                        transform: {
                            fn: self._transformRecommendations.bind(self, recommendationType)
                        }
                    },
                    extraParams: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName
                    },
                    url: '/?c=DSSEditorESRecommendation&m=getRecommendations'
                }
            });
        },


        /**
         * Преобразовать данные, полученные от сервера в строки грида
         *
         * @param {Array} data
         * @return {Array}
         */
        _transformRecommendations: function(recommendationType, data) {
            var newData = [];

            // особые случаи ответа сервера
            if ((typeof data[0]) === 'string') {
                if (data[0] === 'empty') {
                    data = []; // трюк с передачей пустого массива
                } else {
                    onFailure(data[0]); // вернулась ошибка
                    return;
                }
            }

            // строки рекомендаций этого типа
            data.filter(function(row) {
                return (row.recommendationType.recommendationTypeId === recommendationType.recommendationTypeId);
            }).forEach(function(row) {
                newData.push({
                    text: row.recommendationText,
                    action1: 'Изменить текст',
                    action2: 'Изменить тип',
                    action3: 'Удалить',
                    isTypeRow:  false,
                    recommendationId: row.recommendationId,
                    recommendationText: row.recommendationText,
                    recommendationTypeId: recommendationType.recommendationTypeId
                });
            });

            return newData;
        },


        /**
         * Рендер для столбца грида
         *
         */
        _addTip: function(isClickable, value, metaData, record, rowIndex, colIndex, view) {
            metaData.tdAttr = (value)
                ? 'data-qtip="' + value + '"'
                : null;
            if (isClickable) {
                metaData.tdStyle = 'cursor:pointer;'
            }
            return value;
        },


        /**
         * Обработчик нажатия на ячейку таблицы строки рекомендации
         *
         */
        _onRecommendationRowCellClick: function(
            loadMask,
            recommendationPanel,
            diagModule,
            onRecommendationUpdated,
            onFailure,
            onReload,
            selectedRecommendation,
            clickedColumnName
        ) {
            var self = this;

            if (clickedColumnName === 'action1')  {
                Ext6.Msg.prompt(
                    'Изменить текст рекомендации',
                    'Введите новый текст рекомендации: ',
                    function(btn, text) {
                        if ((btn === 'ok') && (text)) {
                            selectedRecommendation.recommendationText = text;
                            self.recommendationActions.updateRecommendationText(
                                    loadMask,
                                    diagModule,
                                    selectedRecommendation,
                                    onRecommendationUpdated,
                                    onFailure);
                        }
                    },
                    null,
                    false,
                    selectedRecommendation.recommendationText
                );
            } else if (clickedColumnName === 'action2') {
                var self = this;
                var modalWindow;

                modalWindow = new Ext6.window.Window({
                    title: 'Изменить тип рекомендации',
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
                                    'recommendationTypeId',
                                    'recommendationTypeName'
                                ],
                                proxy: {
                                        type: 'ajax',
                                        actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                        reader: {
                                            type: 'json',
                                            transform: {
                                                fn: function(data) {
                                                    if (data[0] === 'empty') {
                                                        return [];
                                                    }
                                                    if ((typeof data[0]) === 'string') {
                                                        onFailure(data[0]);
                                                        return;
                                                    }

                                                    return data;
                                                }
                                            }
                                        },
                                        extraParams: {
                                            moduleId: diagModule.moduleId,
                                            moduleName: diagModule.moduleName
                                        },
                                        url: '/?c=DSSEditorESRecommendation&m=getRecommendationTypes',
                                    }
                            }),
                            autoHeight: true,
                            width: '100%',
                            title: 'Типы рекомендаций',
                            viewConfig: {
                                deferEmptyText: false,
                                emptyText: '<div style="text-align: center;">'
                                    + 'Список пуст'
                                    + '</div>'
                            },
                            columns: [{
                                dataIndex: 'recommendationTypeId',
                                name: 'recommendationTypeId',
                                header: 'Идентификатор типа рекомендаций',
                                flex: 1,
                                align: 'center',
                                renderer: function(value, metaData, record, rowIndex, colIndex, view) {
                                    metaData.tdAttr = (value)
                                        ? 'data-qtip="' + value + '"'
                                        : null;
                                    metaData.tdStyle = 'vertical-align: middle;cursor:pointer;'
                                    return value;
                                }
                            }, {
                                dataIndex: 'recommendationTypeName',
                                name: 'recommendationTypeName',
                                header: 'Название типа рекомендаций',
                                flex: 5,
                                align: 'center',
                                renderer: function(value, metaData, record, rowIndex, colIndex, view) {
                                    metaData.tdAttr = (value)
                                        ? 'data-qtip="' + value + '"'
                                        : null;
                                    metaData.tdStyle = 'vertical-align: middle;cursor:pointer;'
                                    return value;
                                }
                            }],
                            listeners: {
                                cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                                    selectedRecommendation.recommendationTypeId = parseInt(record.get('recommendationTypeId'));
                                    self.recommendationActions.updateRecommendationText(
                                            loadMask,
                                            diagModule,
                                            selectedRecommendation,
                                            onReload,
                                            onFailure);
                                    recommendationPanel.remove(modalWindow);
                                }
                            }
                        })
                    ]
                });

                recommendationPanel.add(modalWindow);
                modalWindow.show();
            } else if (clickedColumnName === 'action3') {
                Ext6.Msg.confirm(
                    'Удалить рекомендацию',
                    'Удалить рекомендацию <i>"' + selectedRecommendation.recommendationText + '"</i>?',
                    function(btn, text) {
                        if (btn === 'yes') {
                            self.recommendationActions.deleteRecommendation(
                                    loadMask,
                                    diagModule,
                                    selectedRecommendation,
                                    onRecommendationUpdated,
                                    onFailure);
                        }
                    }
                );
            }
        },


        /**
         * Удалить форму редактирования рекомендаций с общей формы рекомендаций
         *
         * @param {Ext.Component} anchorRecommendationPanel -
         *     родительская панель для панели рекомендаций с которой она будет удалена
         */
        remove: function(anchorRecommendationPanel) {
            var recommendationPanel = anchorRecommendationPanel.getComponent('recommendationPanel');
            if (recommendationPanel) {
                anchorRecommendationPanel.remove(recommendationPanel);
            }
        }
    },




    /**
     * Отобразить панель восстановления удалённых рекомендаций
     *
     */
    _go2RestoreRecommendationForm: function(loadMask, recommendationPanel, diagModule, onReload, onFailure) {
        var self = this;
        var modalWindow;

        modalWindow = new Ext6.window.Window({
            title: 'Восстановить рекомендацию',
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
                            'recommendationTypeId',
                            'recommendationTypeName'
                        ],
                        pageSize: 2,
                        proxy: {
                                type: 'ajax',
                                actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                reader: new Ext6.data.JsonReader({
                                    rootProperty: 'recommendations',
                                    totalProperty: 'total',
                                    transform: {
                                        fn: function(data) {
                                            var recommendationTypes;
                                            var recommendations = [];

                                            if ((typeof data[0]) === 'string') {
                                                onFailure(data[0]);
                                                return;
                                            }

                                            // список типов рекомендаций
                                            recommendationTypes = (function() {
                                                var recommendationTypes = [];

                                                data[0].forEach(function(row) {
                                                    var found = recommendationTypes.reduce(function(acc, type) {
                                                        return (type.recommendationTypeId === row.recommendationType.recommendationTypeId)
                                                            ? true
                                                            : acc;
                                                    }, false);

                                                    if (found === false) {
                                                        recommendationTypes.push(row.recommendationType);
                                                    }
                                                });

                                                return recommendationTypes;
                                            }());

                                            // рекомендации должны быть сгруппированы по типам
                                            recommendationTypes.forEach(function(recommendationType) {
                                                // строка типа рекомендаций
                                               recommendations.push({
                                                   text: '<b>' + recommendationType.recommendationTypeName + '</b>',
                                                   isTypeRow: true,
                                                   recommendationTypeId: recommendationType.recommendationTypeId,
                                                   recommendationTypeName: recommendationType.recommendationTypeName
                                               });
                                                // строки рекомендаций этого типа
                                                data[0].filter(function(row) {
                                                    return (row.recommendationType.recommendationTypeId === recommendationType.recommendationTypeId);
                                                }).forEach(function(row) {
                                                    recommendations.push({
                                                        text: row.recommendationText,
                                                        isTypeRow:  false,
                                                        recommendationId: row.recommendationId,
                                                        recommendationText: row.recommendationText,
                                                        recommendationTypeId: recommendationType.recommendationTypeId
                                                    });
                                                });
                                            });

                                            return {
                                                recommendations: recommendations,
                                                total: data[1]
                                            };
                                        }
                                    }
                                }),
                                extraParams: {
                                    moduleId: diagModule.moduleId,
                                    moduleName: diagModule.moduleName
                                },
                                url: '/?c=DSSEditorESRecommendation&m=getRecommendations2restore',
                            }
                    }),
                    autoHeight: true,
                    width: '100%',
                    title: 'Удалённые рекомендации для восстановления',
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
                        dataIndex: 'recommendationId',
                        name: 'recommendationId',
                        //header: 'Идентификатор рекомендации',
                        hidden: true
                    }, {
                        dataIndex: 'text',
                        name: 'text',
                        //header: 'Название типа рекомендаций',
                        flex: 5,
                        align: 'center',
                        renderer: function(value, metaData, record, rowIndex, colIndex, view) {
                            metaData.tdAttr = (value)
                                ? 'data-qtip="' + value + '"'
                                : null;
                            metaData.tdStyle = 'vertical-align: middle;cursor:pointer;'
                            return value;
                        }
                    }],
                    listeners: {
                        cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                            var recommendationId;
                            if (record.get('isTypeRow')) {
                                return;
                            }
                            recommendationId = parseInt(record.get('recommendationId'));
                            self._restoreRecommendation(
                                    loadMask,
                                    diagModule,
                                    recommendationId,
                                    onReload,
                                    onFailure);
                            recommendationPanel.remove(modalWindow);
                        }
                    }
                })
            ]
        });

        recommendationPanel.add(modalWindow);
        modalWindow.show();
    },


    /**
     * Отобразитьпанель восстановления удалённых типов рекомендаций
     *
     */
    _go2RestoreRecommendationTypeForm: function(loadMask, recommendationPanel, diagModule, onReload, onFailure) {
        var self = this;
        var modalWindow;

        modalWindow = new Ext6.window.Window({
            title: 'Восстановить тип рекомендации',
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
                            'recommendationTypeId',
                            'recommendationTypeName'
                        ],
                        pageSize: 2,
                        proxy: {
                                type: 'ajax',
                                actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                reader: new Ext6.data.JsonReader({
                                    rootProperty: 'recommendationTypes',
                                    totalProperty: 'total',
                                    transform: {
                                        fn: function(data) {
                                            if (data[0] === 'empty') {
                                                return [];
                                            }
                                            if ((typeof data[0]) === 'string') {
                                                onFailure(data[0]);
                                                return;
                                            }

                                            return {
                                                recommendationTypes: data[0],
                                                total: data[1]
                                            };
                                        }
                                    }
                                }),
                                extraParams: {
                                    moduleId: diagModule.moduleId,
                                    moduleName: diagModule.moduleName
                                },
                                url: '/?c=DSSEditorESRecommendation&m=getRecommendationTypes2restore',
                            }
                    }),
                    autoHeight: true,
                    width: '100%',
                    title: 'Удалённые типы рекомендаций для восстановления',
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
                        dataIndex: 'recommendationTypeId',
                        name: 'recommendationTypeId',
                        header: 'Идентификатор типа рекомендаций',
                        flex: 1,
                        align: 'center',
                        renderer: function(value, metaData, record, rowIndex, colIndex, view) {
                            metaData.tdAttr = (value)
                                ? 'data-qtip="' + value + '"'
                                : null;
                            metaData.tdStyle = 'vertical-align: middle;cursor:pointer;'
                            return value;
                        }
                    }, {
                        dataIndex: 'recommendationTypeName',
                        name: 'recommendationTypeName',
                        header: 'Название типа рекомендаций',
                        flex: 5,
                        align: 'center',
                        renderer: function(value, metaData, record, rowIndex, colIndex, view) {
                            metaData.tdAttr = (value)
                                ? 'data-qtip="' + value + '"'
                                : null;
                            metaData.tdStyle = 'vertical-align: middle;cursor:pointer;'
                            return value;
                        }
                    }],
                    listeners: {
                        cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                            var recommendationTypeId = parseInt(record.get('recommendationTypeId'));
                            self._restoreRecommendationType(
                                    loadMask,
                                    diagModule,
                                    recommendationTypeId,
                                    onReload,
                                    onFailure);
                            recommendationPanel.remove(modalWindow);
                        }
                    }
                })
            ]
        });

        recommendationPanel.add(modalWindow);
        modalWindow.show();
    }
});
