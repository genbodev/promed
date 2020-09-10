/**
 * Форма редактирования анализов и исследований
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
 * @since        5.06.2018
 * @version      7.12.2018
 */
Ext6.define('common.DSS.Editor.DSSEditorTestForm', {

    loadMask: {
        init: (form) => {
            if (!this.mask) {
                this.mask = new Ext6.LoadMask({
                    msg: "Подождите...",
                    target: form
                });
            }
        },
        show: () => {
            if (this.mask) this.mask.show();
        },
        hide: () => {
            if (this.mask) this.mask.hide();
        }
    },


    postTest: function(loadMask, moduleId, moduleName, testName, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorTest&m=postTest',
            params: {
                moduleId: moduleId,
                moduleName: moduleName,
                testName: testName
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


    deleteTest: function(loadMask, moduleId, moduleName, testId, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorTest&m=deleteTest',
            params: {
                moduleId: moduleId,
                moduleName: moduleName,
                testId: testId
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


    patchTest: function(loadMask, moduleId, moduleName, testId, testName, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorTest&m=patchTest',
            params: {
                moduleId: moduleId,
                moduleName: moduleName,
                testId: testId,
                testName: testName
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


    putTest2restore: function(loadMask, moduleId, moduleName, testId, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorTest&m=putTest2restore',
            params: {
                moduleId: moduleId,
                moduleName: moduleName,
                testId: testId
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


    show: function(form, moduleId, moduleName, onSuccess, onFailure) {
        const testForm = this;
        testForm.loadMask.init(form);

        form.add(new Ext6.form.FormPanel({
            id: 'genTestPanel',
            bodyPadding: 32,
            border: false,
            width: '100%',
            items: [
                new Ext6.form.FormPanel({
                    border: false,
                    html: `<h1>Модуль <i>${moduleName}</i></h1>`,
                    width: '100%',
                    style: 'margin-bottom: 32px;'
                }),

                new Ext6.Button({
                    text: 'Вернуться к таблице баллов',
                    handler: function() {
                        testForm.remove(form);
                        onSuccess();
                    }
                })
            ]
        }));

        testForm.testPanel.show(
            form.getComponent('genTestPanel'),
            testForm,
            moduleId,
            moduleName,
            function() {
                form.remove(form.getComponent('genTestPanel'));
                onSuccess();
            },
            onFailure
        );

    },


    remove: function(form) {
        const genTestPanel = form.getComponent('genTestPanel');
        if (genTestPanel) form.remove(genTestPanel);
    },


    testPanel: {
        show: function(genTestPanel, testForm, moduleId, moduleName, on2ExpertSystem, onFailure) {
            genTestPanel.add(new Ext6.form.FormPanel({
                id: 'testPanel',
                border: false,
                bodyPadding: 32,
                width: '100%',
                items: [

                    new Ext6.Button({
                        text: 'Добавить анализ или исследование',
                        handler: function() {
                            testForm.testPanel.onCreateTest(genTestPanel, testForm, moduleId, moduleName, onFailure);
                        }
                    }),

                    new Ext6.Button({
                        text: 'Восстановить удалённое исследование',
                        handler: function() {
                            testForm.testPanel.on2restoreTest(
                                genTestPanel,
                                testForm,
                                moduleId,
                                moduleName,
                                on2ExpertSystem,
                                onFailure
                            );
                        }
                    }),

                    new Ext6.grid.GridPanel({
                        title: 'Анализы и исследования',
                        autoHeight: true,
                        id: 'testGrid',
                        store: new Ext6.data.Store({
                            fields: [
                                'testId',
                                'testName',
                                'rename',
                                'delete'
                            ],
                            proxy: {
                                type: 'ajax',
                                actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                reader: {
                                    type: 'json',
                                    transform: {
                                        fn: function(data) {
                                            for (let i=0; i<data.length; i++) {
                                                data[i].rename = 'Переименовать';
                                                data[i].delete = 'Удалить';
                                            }
                                            return data;
                                        }
                                    }
                                },
                                url: '/?c=DSSEditorTest&m=getTests'
                            }
                        }),
                        //hideHeaders: true,
                        selModel: 'cellmodel',
                        columns: [
                            {
                                dataIndex: 'testId',
                                tdCls: 'nameTdCls',
                                header: 'Идентификатор',
                                flex: 1,
                                align: 'center'
                            },
                            {
                                dataIndex: 'testName',
                                tdCls: 'nameTdCls',
                                header: 'Название',
                                flex: 3,
                                align: 'center',
                                renderer: function(value, metaData, record, rowIndex, colIndex, view) {
                                    if (value) metaData.tdAttr = 'data-qtip="' + value + '"';
                                    return value;
                                }
                            },
                            {
                                text: 'Действия',
                                flex: 2,
                                columns: [
                                    {
                                        dataIndex: 'rename',
                                        tdCls: 'nameTdCls',
                                        header: 'Переименовать',
                                        flex: 1,
                                        align: 'center',
                                        tooltip: 'Переименовать'
                                    },
                                    {
                                        dataIndex: 'delete',
                                        tdCls: 'nameTdCls',
                                        header: 'Удалить',
                                        flex: 1,
                                        align: 'center',
                                        tooltip: 'Удалить'
                                    }

                                ]

                            },
                        ],
                        listeners: {
                            cellclick : function(view, cell, cellIndex, record, row, rowIndex, e) {
                                testForm.testPanel.onCellClick.handler(
                                    genTestPanel,
                                    testForm,
                                    moduleId,
                                    moduleName,
                                    cellIndex,
                                    record,
                                    onFailure
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
                        id: 'emptyLabel',
                        border: false,
                        html: 'Список исследований пуст'
                    })
                ]
            }));

            const testPanel = genTestPanel.getComponent('testPanel');
            const testGrid = testPanel.getComponent('testGrid');
            const emptyLabel = testPanel.getComponent('emptyLabel');
            testForm.loadMask.show();
            testGrid.getStore().load({
                params: {
                    moduleId: moduleId,
                    moduleName: moduleName
                },
                callback: function(records, operation, success) {
                    testForm.loadMask.hide();
                    if (!success) {
                        onFailure();
                    }
                    else if (records[0].data === 'empty') {
                        testGrid.setHidden(true);
                        emptyLabel.setHidden(false);
                    }
                    else if (!Number.isInteger(records[0].get('testId'))) {
                        onFailure();
                    }
                    else {
                        testGrid.setHidden(false);
                        emptyLabel.setHidden(true);
                    }
                }
            });
        },


        onCreateTest: function(genTestPanel, testForm, moduleId, moduleName, onFailure) {
            Ext6.Msg.prompt(
                'Создание нового анализа или исследования',
                'Введите название: ',
                function(btn, text) {
                    if ((btn == 'ok') && (text)) {
                        testForm.postTest(
                            testForm.loadMask,
                            moduleId,
                            moduleName,
                            text,
                            function() {
                                genTestPanel
                                    .getComponent('testPanel')
                                    .getComponent('testGrid').getStore().reload();
                            },
                            onFailure
                        );
                    }
                }
            );
        },


        on2restoreTest: function(genTestPanel, testForm, moduleId, moduleName, on2ExpertSystem, onFailure) {
            testForm.testPanel.remove(genTestPanel);
            testForm.restoreTestPanel.show(
                genTestPanel,
                testForm,
                moduleId,
                moduleName,
                {
                    offset: 0,
                    limit: 10
                },
                function() {
                    testForm.testPanel.show(
                        genTestPanel,
                        testForm,
                        moduleId,
                        moduleName,
                        function() {
                            form.remove(genTestPanel);
                            onSuccess();
                        },
                        onFailure
                    )
                },
                onFailure
            );
        },


        onCellClick: {
            onRenameTest: function(testForm, testStore, moduleId, moduleName, testId, testName, onFailure) {
                Ext6.Msg.prompt(
                    'Переименовать исследование',
                    'Введите новое название: ',
                    function(btn, text) {
                        if ((btn == 'ok') && (text)) {
                            testForm.patchTest(
                                testForm.loadMask,
                                moduleId,
                                moduleName,
                                testId,
                                text,
                                function() {
                                    testStore.reload();
                                },
                                onFailure
                            );
                        }
                    },
                    null,
                    false,
                    testName
                );
            },


            onDeleteTest: function(testForm, testStore, moduleId, moduleName, testId, testName, onFailure) {
                Ext6.Msg.confirm(
                    'Удалить исследование',
                    `Удалить исследование <i>"${testName}"</i>?`,
                    function(btn) {
                        if (btn == 'yes')  {
                            testForm.deleteTest(
                                testForm.loadMask,
                                moduleId,
                                moduleName,
                                testId,
                                function() {
                                    testStore.reload();
                                },
                                onFailure
                            );
                        }
                    }
                );
            },


            handler: function(genTestPanel, testForm, moduleId, moduleName, cellIndex, record, onFailure) {
                const h = this;
                const clickedColumnName = record.getFields()[cellIndex].getName();
                const testId = record.get('testId');
                const testName = record.get('testName');
                const testStore = genTestPanel
                    .getComponent('testPanel')
                    .getComponent('testGrid').getStore();

                if (clickedColumnName === 'rename') {
                    h.onRenameTest(
                        testForm,
                        testStore,
                        moduleId,
                        moduleName,
                        testId,
                        testName,
                        onFailure
                    );
                } else if (clickedColumnName === 'delete') {
                    h.onDeleteTest(
                        testForm,
                        testStore,
                        moduleId,
                        moduleName,
                        testId,
                        testName,
                        onFailure
                    );
                }
            }
        },


        remove: function(genTestPanel) {
            genTestPanel.remove(genTestPanel.getComponent('testPanel'));
        }
    },


    restoreTestPanel: {
        show: function(genTestPanel, testForm, moduleId, moduleName, pagination, onSuccess, onFailure) {
            genTestPanel.add(new Ext6.form.FormPanel({
                id: 'restoreTestPanel',
                border: false,
                bodyPadding: 30,
                width: '100%',
                items: [

                    new Ext6.form.FormPanel({
                        id: 'emptyLabel2restore',
                        border: false,
                        html: 'Список удалённых исследований пуст'
                    }),

                    new Ext6.Button({
                        text: 'Вернуться к списку исследований',
                        handler: function() {
                            testForm.restoreTestPanel.remove(genTestPanel);
                            onSuccess();
                        }
                    }),

                    new Ext6.grid.GridPanel({
                        store: new Ext6.data.Store({
                            autoLoad: false,
                            fields: [ 'Test_id', 'Test_name', 'restore',],
                            proxy: {
                                type: 'ajax',
                                actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                reader: {
                                    type: 'json',
                                    transform: {
                                        fn: function(data) {
                                            for (let i=0; i<data.length; i++) {
                                                data[i].restore = 'Восстановить';
                                            }
                                            return data;
                                        }
                                    }
                                },
                                url: '/?c=DSSEditorTest&m=getTests2restore'
                            }
                        }),
                        id: 'deletedTestsGrid',
                        autoHeight: true,
                        width: '100%',
                        title: 'Удалённые исследования',
                        selModel: 'cellmodel',
                        columns: [
                            {
                                dataIndex: 'testId',
                                tdCls: 'nameTdCls',
                                header: 'Идентификатор',
                                flex: 1,
                                align: 'center'
                            },
                            {
                                dataIndex: 'testName',
                                tdCls: 'nameTdCls',
                                header: 'Название исследования',
                                flex: 3,
                                align: 'center'
                            },
                            {
                                dataIndex: 'restore',
                                tdCls: 'nameTdCls',
                                header: 'Восстановить',
                                flex: 1,
                                align: 'center'
                            }
                        ],
                        listeners: {
                            cellclick : function(view, cell, cellIndex, record, row, rowIndex, e) {
                                const clickedColumnName = record.getFields()[cellIndex].getName();
                                if (clickedColumnName == 'restore') {
                                    testForm.putTest2restore(
                                        testForm.loadMask,
                                        moduleId,
                                        moduleName,
                                        record.get('testId'),
                                        function() {
                                            genTestPanel
                                                .getComponent('restoreTestPanel')
                                                .getComponent('deletedTestsGrid')
                                                .getStore().reload();
                                        },
                                        onFailure
                                    );
                                }
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
                        id: 'deletedTestsPag',
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
                                        testForm.restoreTestPanel.remove(genTestPanel);
                                        testForm.restoreTestPanel.show(genTestPanel, testForm, moduleId, moduleName, pagination, onSuccess, onFailure);
                                    }
                                }
                            }),

                            new Ext6.Button({
                                text: 'Следующая страница',
                                flex: 1,
                                handler: function() {
                                    if (testForm.count === pagination.limit) {
                                        // поскольку общее количество модулей на передаётся от АПИ,
                                        // определение последней страницы выполняется по косвенным признакам (неточно):
                                        // количество модулей на странице меньше limit -
                                        // значит страница последняя
                                        pagination.offset += pagination.limit;
                                        testForm.restoreTestPanel.remove(genTestPanel);
                                        testForm.restoreTestPanel.show(genTestPanel, testForm, moduleId, moduleName, pagination, onSuccess, onFailure);
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
                    })
                ]
            }));

            const restoreTestPanel = genTestPanel.getComponent('restoreTestPanel');
            const deletedTestsGrid = restoreTestPanel.getComponent('deletedTestsGrid');
            const deletedTestsPag = restoreTestPanel.getComponent('deletedTestsPag');
            const emptyLabel = restoreTestPanel.getComponent('emptyLabel2restore');
            testForm.loadMask.show();
            deletedTestsGrid.getStore().load({
                params: {
                    moduleId: moduleId,
                    moduleName: moduleName,
                    offset: pagination.offset,
                    limit: pagination.limit
                },
                callback: function(records, operation, success) {
                    testForm.loadMask.hide();
                    emptyLabel.setHidden(true);
                    testForm.count = records.length;
                    if (!success) {
                        onFailure();
                    } else if (records[0].data === 'empty') {
                        deletedTestsGrid.setHidden(true);
                        emptyLabel.setHidden(false);
                    } else if (!Number.isInteger(records[0].get('testId'))) {
                        onFailure();
                    } else {
                        deletedTestsPag.setHidden(false);
                    }
                }
            });
        },


        remove: function(genTestPanel) {
            const restoreTestPanel = genTestPanel.getComponent('restoreTestPanel');
            genTestPanel.remove(restoreTestPanel);
        }
    }
});
