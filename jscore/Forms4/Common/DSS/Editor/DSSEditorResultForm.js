/**
 * Форма редактирования заключений
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
 * @since        4.06.2018
 * @version      25.04.2019
 */
Ext6.define('common.DSS.Editor.DSSEditorResultForm', {


    /**
     * Отобразить общую форму редактирования заключений
     *
     */
    show: function(loadMask, form, diagModule, onReturn2Balls, on2ResultRecommendations, onFailure) {
        var self = this;
        var genResultPanel;

        genResultPanel = new Ext6.form.FormPanel({
            id: 'genResultPanel',
            bodyPadding: 32,
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
                })
            ]
        });

        form.add(genResultPanel);

        self._resultPanel.show(
            loadMask,
            genResultPanel,
            diagModule,
            on2ResultRecommendations,
            onFailure
        );
    },


    /**
     * Удалить компоненты формы редактирования заключений с родителького компонента
     *
     * @param {Ext.Component} form - родительский компонент
     */
    remove: function(form) {
        var genResultPanel = form.getComponent('genResultPanel');
        if (genResultPanel) {
            form.remove(genResultPanel);
        }
    },


    /**
     * Форма редактирования заключений
     */
    _resultPanel: {

        /**
         * Создать заключение (запрос к серверу)
         *
         */
        _postResult: function(loadMask, diagModule, resultName, onSuccess, onFailure) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorES&m=postResult',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    resultName: resultName
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
         * Удалить заключение (выполнить ajax-запрос к серверу)
         *
         */
        _deleteResult: function(loadMask, diagModule, result, onSuccess, onFailure) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorES&m=deleteResult',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    resultId: result.resultId
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
         * Изменить заключение (выполнить ajax-запрос к серверу)
         *
         */
        _patchResult: function(loadMask, diagModule, result, onSuccess, onFailure) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorES&m=patchResult',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    resultId: result.resultId,
                    resultName: result.resultName,
                    // editor API определяет пороговое значение как число с двумя знаками после запятой
                    // и требует передавать как целое число value = threshold*100
                    resultThreshold: result.resultThreshold
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
         * Восстановить удалённое заключение (выполнить запрос к серверу)
         *
         */
        _restoreResult: function(loadMask, diagModule, resultId, onSuccess, onFailure) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorES&m=putResult2restore',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    resultId: resultId
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
         * Отобразить форму редактирования заключений
         *
         */
        show: function(loadMask, genResultPanel, diagModule, on2ResultRecommendations, onFailure) {
            var self = this;
            var resultsStore;

            resultsStore = self._makeResultsStore(diagModule);

            genResultPanel.add(new Ext6.form.FormPanel({
                id: 'resultPanel',
                border: false,
                bodyPadding: 0,
                width: '100%',
                items: [

                    new Ext6.Button({
                        text: 'Добавить заключение',
                        handler: function() {
                            self._addResultHandler(loadMask, diagModule, resultsStore, onFailure);
                        }
                    }),

                    new Ext6.Button({
                        text: 'Восстановить удалённое заключение',
                        handler: function() {
                            self._restoreResultHandler(
                                    loadMask,
                                    genResultPanel,
                                    diagModule,
                                    function() {
                                        resultsStore.reload();
                                    },
                                    onFailure);
                        }
                    }),

                    new Ext6.grid.GridPanel({
                        title: 'Заключения',
                        autoHeight: true,
                        id: 'resultGrid',
                        store: resultsStore,
                        viewConfig: {
                            deferEmptyText: false,
                            emptyText: '<div style="text-align: center;">'
                                + 'Список пуст'
                                + '</div>'
                        },
                        selModel: 'cellmodel',
                        columns: [{
                                dataIndex: 'resultId',
                                tdCls: 'nameTdCls',
                                header: 'Идентификатор',
                                flex: 1,
                                align: 'center'
                            }, {
                                dataIndex: 'resultName',
                                tdCls: 'nameTdCls',
                                header: 'Название',
                                flex: 3,
                                align: 'center'
                            }, {
                                dataIndex: 'resultThreshold',
                                tdCls: 'nameTdCls',
                                header: 'Пороговое значение',
                                flex: 1,
                                align: 'center',
                                tooltip: 'Пороговое значение'
                            }, {
                                text: 'Действия',
                                flex: 2,
                                columns: [{
                                        dataIndex: 'on2ResultRecommendations',
                                        tdCls: 'nameTdCls',
                                        header: 'Рекомендации',
                                        flex: 1,
                                        align: 'center',
                                        tooltip: 'Рекомендации'
                                    }, {
                                        dataIndex: 'rename',
                                        tdCls: 'nameTdCls',
                                        header: 'Переименовать',
                                        flex: 1,
                                        align: 'center',
                                        tooltip: 'Переименовать'
                                    }, {
                                        dataIndex: 'updateThreshold',
                                        tdCls: 'nameTdCls',
                                        header: 'Изменить пороговое значение',
                                        flex: 1,
                                        align: 'center',
                                        tooltip: 'Изменить пороговое значение'
                                    }, {
                                        dataIndex: 'delete',
                                        tdCls: 'nameTdCls',
                                        header: 'Удалить',
                                        flex: 1,
                                        align: 'center',
                                        tooltip: 'Удалить'
                                }]
                        }],
                        listeners: {
                            cellclick : function(view, cell, cellIndex, record, row, rowIndex, e) {
                                var clickedColumnName = record.getFields()[cellIndex].getName();
                                var resultThresholdStr = record.get('resultThreshold');
                                var result = {
                                    resultId: record.get('resultId'),
                                    resultName: record.get('resultName'),
                                    resultThreshold: Math.round(parseFloat(
                                            resultThresholdStr
                                            .substr( // в гриде пороговое значение указывается со знаком %, отрезать
                                                    0,
                                                    resultThresholdStr.length-7)
                                            .replace(',', '.'))
                                        *100)
                                };
                                self._onCellClick(
                                        loadMask,
                                        diagModule,
                                        resultsStore,
                                        onFailure,
                                        on2ResultRecommendations,
                                        clickedColumnName,
                                        result);
                            }
                        }
                    })
                ]
            }));
        },


        /**
         * Очистить основную форму редактирования заключений
         * от панели редактирования заключений (чтобы отобразить панель удалённых заключений)
         *
         */
        remove: function(genResultPanel) {
            var resultPanel = genResultPanel.getComponent('resultPanel');
            if (genResultPanel) {
                genResultPanel.remove(resultPanel);
            }
        },


        /**
         * Сформировать стор для ссписка заключений
         *
         */
        _makeResultsStore: function(diagModule) {
            return new Ext6.data.Store({
                fields: [
                    'resultId',
                    'resultName',
                    'resultThreshold',
                    'on2ResultRecommendations',
                    'rename',
                    'updateThreshold',
                    'delete'
                ],
                autoLoad: true,
                proxy: {
                    type: 'ajax',
                    actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                    reader: {
                        type: 'json',
                        transform: {
                            fn: function(data) {
                                if (data[0] === 'empty') {
                                    // трюк с передачей пустого массива
                                    data = [];
                                }

                                return data.map(function(row) {
                                    row.resultThreshold = row.resultThreshold/100 + '&nbsp;%';
                                    row.rename = 'Переименовать';
                                    row.updateThreshold = 'Изменить пороговое значение';
                                    row.on2ResultRecommendations = 'Рекомендации';
                                    row.delete = 'Удалить';

                                    return row;
                                });
                            }
                        }
                    },
                    extraParams: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName
                    },
                    url: '/?c=DSSEditorES&m=getResults'
                }
            });
        },


        /**
         * Обработать нажание на ячейку строки грида
         *
         */
        _onCellClick: function(
            loadMask,
            diagModule,
            resultsStore,
            onFailure,
            on2ResultRecommendations,
            clickedColumnName,
            result
        ) {
            var self = this;

            switch (clickedColumnName) {

                case 'rename': {
                    Ext6.Msg.prompt(
                        'Переименовать заключение',
                        'Введите новое название заключения: ',
                        function(btn, text) {
                            if ((btn == 'ok') && (text) && (text !== result.resultName)) {
                                result.resultName = text;
                                self._patchResult(
                                    loadMask,
                                    diagModule,
                                    result,
                                    function onResultUpdated() {
                                        resultsStore.reload();
                                    },
                                    onFailure
                                );
                            }
                        },
                        null,
                        false,
                        result.resultName
                    );
                    break;
                }

                case 'on2ResultRecommendations': {
                    on2ResultRecommendations(result);
                    break;
                }

                case 'updateThreshold': {
                    Ext6.Msg.prompt(
                        'Изменить пороговое значение заключения',
                        'Введите новое пороговое значение: ',
                        function(btn, text) {
                            if ((btn == 'ok') && (text)) {
                                result.resultThreshold = Math.round(parseFloat(text.replace(',', '.'))*100);
                                if (result.resultThreshold > 10000) {
                                    return; // смысл проценты не должны быть больше 100
                                }
                                self._patchResult(
                                    loadMask,
                                    diagModule,
                                    result,
                                    function onResultUpdated() {
                                        resultsStore.reload();
                                    },
                                    onFailure
                                );
                            }
                        },
                        null,
                        false,
                        result.resultThreshold/100.0
                    );
                    break;
                }


                case 'delete': {
                    Ext6.Msg.confirm(
                        'Удалить заключение',
                        'Удалить заключение <i>"' + result.resultName + '"</i>?',
                        function(btn) {
                            if (btn == 'yes')  {
                                self._deleteResult(
                                    loadMask,
                                    diagModule,
                                    result,
                                    function onResultUpdated() {
                                        resultsStore.reload();
                                    },
                                    onFailure
                                );
                            }
                        }
                    );
                    break;
                }

                default: return;
            };
        },


        /**
         * Обработчик нажатия на кнопку перехода к восстановлению заключений
         *
         */
        _restoreResultHandler: function(loadMask, resultPanel, diagModule, onReload, onFailure) {
            var self = this;
            var modalWindow;

            modalWindow = new Ext6.window.Window({
                title: 'Восстановить заключение',
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
                                'resultId',
                                'resultName'
                            ],
                            pageSize: 2,
                            proxy: {
                                    type: 'ajax',
                                    actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                    reader: new Ext6.data.JsonReader({
                                        rootProperty: 'results',
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
                                                    results: data[0],
                                                    total: data[1]
                                                };
                                            }
                                        }
                                    }),
                                    extraParams: {
                                        moduleId: diagModule.moduleId,
                                        moduleName: diagModule.moduleName
                                    },
                                    url: '/?c=DSSEditorES&m=getResults2restore',
                                }
                        }),
                        autoHeight: true,
                        width: '100%',
                        title: 'Удалённые заключения для восстановления',
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
                            dataIndex: 'resultId',
                            name: 'resultId',
                            header: 'Идентификатор заключения',
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
                            dataIndex: 'resultName',
                            name: 'resultName',
                            header: 'Название заключения',
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
                                var resultId = parseInt(record.get('resultId'));
                                self._restoreResult(
                                        loadMask,
                                        diagModule,
                                        resultId,
                                        onReload,
                                        onFailure);
                                resultPanel.remove(modalWindow);
                            }
                        }
                    })
                ]
            });

            resultPanel.add(modalWindow);
            modalWindow.show();
        },


        /**
         * Обработчик нажания на кнопку создания нового заключения
         *
         */
        _addResultHandler: function(loadMask, diagModule, resultsStore, onFailure) {
            var self = this;

            Ext6.Msg.prompt(
                'Создание нового заключения',
                'Введите текст заключения: ',
                function(btn, text) {
                    if ((btn == 'ok') && (text)) {
                        self._postResult(
                            loadMask,
                            diagModule,
                            text,
                            function onResultAdded() {
                                resultsStore.reload();
                            },
                            onFailure
                        );
                    }
                }
            );
        }
    }
});
