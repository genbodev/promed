/**
 * Форма редактирования рекомендаций для заключений
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
 * @since        04.06.2018
 * @version      24.04.2019
 */
Ext6.define('common.DSS.Editor.DSSEditorRecommendation2ResultForm', {


    /**
     * отображение основной страницы
     *
     */
    show: function(loadMask, form, diagModule, result, onReturn2Results, onFailure) {
        var self = this;
        var genPrescrPanel;

        genPrescrPanel = new Ext6.form.FormPanel({
            id: 'genPrescrPanel',
            bodyPadding: 32,
            border: false,
            width: '100%',
            items: [
                new Ext6.form.FormPanel({
                    border: false,
                    html: '<h1>Модуль <i>"' + diagModule.moduleName + '"</i></h1>'
                        + '<h2>Заключение <i>"' + result.resultName + '"</i></h2>',
                    width: '100%',
                    style: 'margin-bottom: 32px;'
                }),

                new Ext6.Button({
                    text: 'Вернуться к таблице заключений',
                    handler: function() {
                        self.remove(form);
                        onReturn2Results();
                    }
                })
            ]
        });
        form.add(genPrescrPanel);

        self._prescrPanel.show(
            loadMask,
            genPrescrPanel,
            diagModule,
            result,
            onFailure
        );
    },


    /**
     * Удалить форму списка рекомендаций для заключения с родительского компонента
     *
     */
    remove: function(form) {
        var genPrescrPanel = form.getComponent('genPrescrPanel');
        if (genPrescrPanel) {
            form.remove(genPrescrPanel);
        }
    },


    /**
     * панель рекомендаций
     */
    _prescrPanel: {

        /**
         * ajax-запрос добавления рекомендации в список рекомедаций для заключения
         */
        _postRecommendation2Result: function(loadMask, diagModule, resultId, recommendationId, onSuccess, onFailure) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorESRecommendation2Result&m=postRecommendation2Result',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    resultId: resultId,
                    recommendationId: recommendationId
                },
                success: function(response, opts) {
                    loadMask.hide();
                    onSuccess();
                },
                failure: function(response, opts) {
                   loadMask.hide();
                   onFailure();
                }
            });
        },

        /**
         * ajax-запрос удаления рекомендации из списка рекомендаций для заключения
         */
        _deleteRecommendation2Result: function(
            loadMask,
            diagModule,
            result,
            recommendation2ResultId,
            onSuccess,
            onFailure
        ) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorESRecommendation2Result&m=deleteRecommendation2Result',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    resultId: result.resultId,
                    recommendation2ResultId: recommendation2ResultId
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
         * ajax-запрос изменения рекомендации в списке рекомендаций для заключения -
         *  добавление или удаление флаг "по показаниям"
         *
         */
        _updateRecommendation2Result: function(
            loadMask,
            diagModule,
            result,
            recommendation2ResultId,
            isConditional,
            onSuccess,
            onFailure
        ) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorESRecommendation2Result&m=updateRecommendation2Result',
                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName,
                    resultId: result.resultId,
                    recommendation2ResultId: recommendation2ResultId,
                    isConditional: isConditional
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
         * Отобразить панель рекомендаций
         *
         */
        show: function(
            loadMask,
            genPrescrPanel,
            diagModule,
            result,
            onFailure
        ) {
            var self = this;
            var resultRecommendationStore;

            resultRecommendationStore = self._makeResultRecommendationsStore(diagModule, result);


            genPrescrPanel.add(new Ext6.form.FormPanel({
                id: 'prescrPanel',
                border: false,
                bodyPadding: 32,
                width: '100%',
                items: [

                    new Ext6.Button({
                        text: 'Добавить рекомендацию',
                        handler: self._onAddResultRecommendation.bind(
                                    self,
                                    loadMask,
                                    diagModule,
                                    result,
                                    genPrescrPanel,
                                    function onSuccess() {
                                        resultRecommendationStore.reload();
                                    },
                                    onFailure)
                    }),

                    new Ext6.grid.GridPanel({
                        title: 'Список рекомендаций',
                        autoHeight: true,
                        store: resultRecommendationStore,
                        selModel: 'cellmodel',
                        viewConfig: {
                            deferEmptyText: false,
                            emptyText: '<div style="text-align: center;">'
                                + 'Список пуст'
                                + '</div>'
                        },
                        columns: [{
                                dataIndex: 'resultRecommendationId',
                                tdCls: 'nameTdCls',
                                header: 'Идентификатор',
                                flex: 1,
                                align: 'center'
                            }, {
                                dataIndex: 'recommendationText',
                                tdCls: 'nameTdCls',
                                header: 'Текст рекомендации',
                                flex: 3,
                                align: 'center',
                                renderer: function(value, metaData, record, rowIndex, colIndex, view) {
                                    if (value) {
                                        metaData.tdAttr = 'data-qtip="' + value + '"';
                                    }
                                    return value;
                                }
                            }, {
                                text: 'Действия',
                                flex: 2,
                                columns: [{
                                        dataIndex: 'isConditional',
                                        tdCls: 'nameTdCls',
                                        header: 'Установить/снять флаг "по показаниям"',
                                        flex: 1,
                                        align: 'center',
                                        tooltip: 'Установить/снять флаг -по показаниям-'
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
                                if (record.get('isTypeRow') === true) {
                                    return;
                                }
                                self._onCellClick(
                                        loadMask,
                                        diagModule,
                                        result,
                                        function onResultRecommendationListChanged() {
                                            resultRecommendationStore.reload();
                                        },
                                        onFailure,
                                        clickedColumnName,
                                        record);
                            }
                        }
                    })
                ]
            }));
        },


        /**
         * Сформировать стор для списка рекомендаций для заключения
         *
         */
        _makeResultRecommendationsStore: function(diagModule, result) {
            return new Ext6.data.Store({
                fields: [
                    'resultRecommendationId',
                    'recommendationText',
                    'isConditional',
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
                                    row.recommendationText = row.recommendation.recommendationText;
                                    row.isConditional = (row.isConditional)
                                        ? 'по показаниям'
                                        : '-';
                                    row.delete = 'Удалить';

                                    return row;
                                });
                            }
                        }
                    },
                    extraParams: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        resultId: result.resultId
                    },
                    url: '/?c=DSSEditorESRecommendation2Result&m=getRecommendations2Result'
                }
            });
        },


        /**
         * Обработчик нажания на ячейку грида списка рекомендаций для заключения
         *
         */
        _onCellClick: function(
            loadMask,
            diagModule,
            result,
            onRecommendationUpdated,
            onFailure,
            clickedColumnName,
            record
        ) {
            var self = this;
            var isConditional = (record.get('isConditional') === '-') ? 't' : 'f';
            var recommendation2ResultId = record.get('resultRecommendationId');

            if (clickedColumnName === 'isConditional') {
                self._updateRecommendation2Result(
                        loadMask,
                        diagModule,
                        result,
                        recommendation2ResultId,
                        isConditional,
                        onRecommendationUpdated,
                        onFailure);
            } else if (clickedColumnName === 'delete') {
                Ext6.Msg.confirm(
                    'Удалить рекомендацию',
                    `Удалить рекомендацию <i>"${record.get('recommendationText')}"</i>?`,
                    function(btn) {
                        if (btn == 'yes') {
                            self._deleteRecommendation2Result(
                                    loadMask,
                                    diagModule,
                                    result,
                                    recommendation2ResultId,
                                    onRecommendationUpdated,
                                    onFailure);
                        }
                    }
                );
            }
        },


        /**
         * Обработать нажатие на кнопку --Добавить рекомендацию для заключения--
         *
         */
        _onAddResultRecommendation: function(loadMask, diagModule, result, genPrescrPanel, onSuccess, onFailure) {
            var self = this;
            var modalWindow;

            modalWindow = new Ext6.window.Window({
                title: 'Добавить рекомендацию для заключения',
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
                                'recommendationId',
                                'text',
                                'recomendationTypeId'
                            ],
                            proxy: {
                                    type: 'ajax',
                                    actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                    reader: {
                                        type: 'json',
                                        transform: {
                                            fn: self._transformRecommendations
                                        }
                                    },
                                    extraParams: {
                                        moduleId: diagModule.moduleId,
                                        moduleName: diagModule.moduleName
                                    },
                                    url: '/?c=DSSEditorESRecommendation&m=getRecommendations'
                                }
                        }),
                        autoHeight: true,
                        width: '100%',
                        title: 'Рекомендации',
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
                            //flex: 1,
                            //align: 'center',
                            hidden: true
                        }, {
                            dataIndex: 'text',
                            name: 'text',
                            //header: 'Текст рекомендации',
                            flex: 5,
                            align: 'center',
                            renderer: function(value, metaData, record, rowIndex, colIndex, view) {
                                metaData.tdAttr = (value)
                                    ? 'data-qtip="' + value + '"'
                                    : null;
                                metaData.tdStyle = 'vertical-align: middle;cursor:pointer;'
                                return value;
                            }
                        }, {
                            dataIndex: 'recomendationTypeId',
                            name: 'recomendationTypeId',
                            //header: 'Текст рекомендации',
                            //flex: 5,
                            //align: 'center',
                            hidden: true
                        }],
                        listeners: {
                            cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                                self._postRecommendation2Result(
                                        loadMask,
                                        diagModule,
                                        result.resultId,
                                        record.get('recommendationId'),
                                        onSuccess,
                                        onFailure);
                                genPrescrPanel.remove(modalWindow);
                            }
                        }
                    })
                ]
            });

            genPrescrPanel.add(modalWindow);
            modalWindow.show();
        },


        /**
         * Преобразовать данные, полученные от сервера в строки грида
         *
         * @param {Array} data
         * @return {Array}
         */
        _transformRecommendations: function(data) {
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

            // список типов рекомендаций
            recommendationTypes = (function() {
                var recommendationTypes = [];

                data.forEach(function(row) {
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
               newData.push({
                   text: '<b>' + recommendationType.recommendationTypeName + '</b>',
                   isTypeRow: true,
                   recommendationTypeId: recommendationType.recommendationTypeId,
                   recommendationTypeName: recommendationType.recommendationTypeName
               });
                // строки рекомендаций этого типа
                data.filter(function(row) {
                    return (row.recommendationType.recommendationTypeId === recommendationType.recommendationTypeId);
                }).forEach(function(row) {
                    newData.push({
                        text: row.recommendationText,
                        isTypeRow:  false,
                        recommendationId: row.recommendationId,
                        recommendationText: row.recommendationText,
                        recommendationTypeId: recommendationType.recommendationTypeId
                    });
                });
            });

            return newData;
        },


        /**
         * Удалить панель рекомендаций с общей панели рекомендаций
         *
         */
        remove: function(anchorPrescrPanel) {
            var prescrPanel = anchorPrescrPanel.getComponent('prescrPanel');
            if (prescrPanel) {
                anchorPrescrPanel.remove(prescrPanel);
            }
        }
    },


    /**
     * панель выбораудалённой рекомендации из списков рекомендаций для заключений
     *  для восстановления
     */
    selectRecommendation2RestorePanel: {
        show: function(
            prescrForm,
            anchorRestoreRecommendationPanel,
            module,
            resultId,
            pagination,
            onSuccess,
            onCancel,
            onFailure
        ) {

            anchorRestoreRecommendationPanel.add(new Ext6.form.FormPanel({
                id: 'selectRecomendation2restorePanel',
                border: false,
                bodyPadding: 32,
                width: '100%',
                items: [

                    new Ext6.Button({
                        style: 'margin-top: 32px;',
                        text: 'Вернуться к списку рекомендаций',
                        handler: function() {
                            onCancel();
                        }
                    }),

                    new Ext6.grid.GridPanel({
                        title: 'Выбрать рекомендацию для восстановления',
                        autoHeight: true,
                        id: 'selectRecomendationGrid',
                        store: new Ext6.data.Store({
                            fields: [
                                'recommendation2ResultId',
                                'recommendationText'
                            ],
                            proxy: {
                                type: 'ajax',
                                actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                reader: {
                                    type: 'json'
                                },
                                url: '/?c=DSSEditorESRecommendation2Result&m=getRecommendations2Result2restore'
                            }
                        }),
                        //hideHeaders: true,
                        selModel: 'cellmodel',
                        columns: [
                            {
                                dataIndex: 'recommendation2ResultId',
                                tdCls: 'nameTdCls',
                                header: 'Идентификатор',
                                flex: 1,
                                align: 'center'
                            },
                            {
                                dataIndex: 'recommendationText',
                                tdCls: 'nameTdCls',
                                header: 'Текст рекомендации',
                                flex: 3,
                                align: 'center'
                            }
                        ],
                        listeners: {
                            cellclick : function(view, cell, cellIndex, record, row, rowIndex, e) {
                                const clickedColumnName = record.getFields()[cellIndex].getName();
                                Ext6.Msg.confirm(
                                    'Выбрать рекомендацию',
                                    `Выбрать рекомендацию <i>"${record.get('recommendationText')}"</i>?`,
                                    function(btn) {
                                        if (btn === 'yes') {
                                            onSuccess(record.get('recommendation2ResultId'));
                                        }
                                    }
                                );
                            },

                            render: function(grid) {
                                // всплывающая подсказка
                                const view = grid.getView();
                                grid.tip = Ext6.create('Ext6.tip.ToolTip', {
                                    target: view.getId(),
                                    delegate: view.itemSelector + ' .nameTdCls',
                                    trackMouse: true,
                                    listeners: {
                                        beforeshow: function updateTipBody(tip) {
                                            const tipGridView = tip.target.component;
                                            if (tipGridView) {
                                                const record = tipGridView.getRecord(
                                                    tip.triggerElement
                                                );
                                                const colname = tipGridView
                                                    .getHeaderCt()
                                                    .getHeaderAtIndex(
                                                        tip.triggerElement.cellIndex
                                                    )
                                                    .dataIndex;
                                                tip.update(record.get(colname));
                                            }
                                        }
                                    }
                                });
                            },


                            destroy: function(view) {
                                view.tip.destroy();
                                delete view.tip; // Clean up this property on destroy.
                            }
                        }
                    }),

                    new Ext6.form.FormPanel({
                        id: 'deletedRecommendationsPag',
                        hidden: true,
                        border: false,
                        layout: 'hbox',
                        align: 'middle',
                        pack: 'center',
                        items: [
                            new Ext6.form.FormPanel({
                                border: false,
                                html: `
                                     <div style='padding: 7px;text-align: center;'>
                                         Страница ${(pagination.offset / pagination.limit) + 1}
                                     </div>
                                 `,
                                flex: 1,
                                height: 32
                            }),

                            new Ext6.Button({
                                text: 'Предыдущая страница',
                                flex: 1,
                                handler: function() {
                                    if (pagination.offset >= pagination.limit) {
                                        pagination.offset -= pagination.limit;
                                        prescrForm.selectRecommendation2RestorePanel.remove(anchorRestoreRecommendationPanel);
                                        prescrForm.selectRecommendation2RestorePanel.show(prescrForm, anchorRestoreRecommendationPanel, module, resultId, pagination, onSuccess, onCancel, onFailure);
                                    }
                                }
                            }),

                            new Ext6.Button({
                                text: 'Следующая страница',
                                flex: 1,
                                handler: function() {
                                    if (prescrForm.selectRecommendation2RestorePanel.count === pagination.limit) {
                                        // поскольку общее количество модулей на передаётся от АПИ,
                                        // определение последней страницы выполняется по косвенным признакам (неточно):
                                        // количество модулей на странице меньше limit -
                                        // значит страница последняя
                                        pagination.offset += pagination.limit;
                                        prescrForm.selectRecommendation2RestorePanel.remove(anchorRestoreRecommendationPanel);
                                        prescrForm.selectRecommendation2RestorePanel.show(prescrForm, anchorRestoreRecommendationPanel, module, resultId, pagination, onSuccess, onCancel, onFailure);
                                    }
                                }
                            }),

                            new Ext6.form.FormPanel({
                                // пустая панель чтобы пагинация отображалась слева
                                border: false,
                                html: ``,
                                flex: 4
                            })
                        ]
                    }),

                    new Ext6.form.FormPanel({
                        id: 'emptyRecommendation2restoreListLabel',
                        border: false,
                        html: 'Список удалённых рекомендаций пуст'
                    })
                ]
            }));

            const selectRecommendationPanel = anchorRestoreRecommendationPanel.getComponent('selectRecomendation2restorePanel');
            const testGrid = selectRecommendationPanel.getComponent('selectRecomendationGrid');
            const deletedRecommendationsPag = selectRecommendationPanel.getComponent('deletedRecommendationsPag');
            const emptyTestLabel = selectRecommendationPanel.getComponent('emptyRecommendation2restoreListLabel');
            prescrForm.loadMask.show();
            testGrid.getStore().load({
                params: {
                    moduleId: module.moduleId,
                    moduleName: module.moduleName,
                    resultId: resultId,
                    offset: pagination.offset,
                    limit: pagination.limit
                },
                callback: function(records, operation, success) {
                    prescrForm.loadMask.hide();
                    if (!success) {
                        onFailure();
                    } else if (records[0].data === 'empty') {
                        testGrid.setHidden(true);
                        emptyTestLabel.setHidden(false);
                    } else {
                        testGrid.setHidden(false);
                        emptyTestLabel.setHidden(true);
                        deletedRecommendationsPag.setHidden(false);
                        prescrForm.selectRecommendation2RestorePanel.count = records.length;
                    }
                }
            });
        },


        remove: function(anchorRestoreRecommendationPanel) {
            anchorRestoreRecommendationPanel.remove(
                anchorRestoreRecommendationPanel.getComponent('selectRecomendation2restorePanel')
            );
        }
    }
});
