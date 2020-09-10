/**
 * Форма удалённых вопросов для восстановления
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
 * @since        18.07.2018
 * @version      10.12.2018
 */
Ext6.define('common.DSS.Editor.DSSEditorRestoreQuestionForm', {


    putQuestion2restore: function(loadMask, moduleId, moduleName, questionId, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorQuestion&m=putQuestion2restore',
            params: {
                moduleId: moduleId,
                moduleName: moduleName,
                questionId: questionId
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


    show: function(genQuestionPanel, questionForm, moduleId, moduleName, pagination, onSuccess, onFailure) {
        const restoreQuestionForm = this;
        genQuestionPanel.add(new Ext6.form.FormPanel({
            id: 'restoreQuestionPanel',
            border: false,
            bodyPadding: 30,
            width: '100%',
            items: [

                new Ext6.Button({
                    text: 'Вернуться к опроснику',
                    id: 'restoreQuestionCancelButton',
                    handler: function() {
                        restoreQuestionForm.remove(genQuestionPanel);
                        onSuccess();
                    }
                }),

                new Ext6.grid.GridPanel({
                    store: new Ext6.data.Store({
                        id: 'deletedQuestionsStore',
                        autoLoad: false,
                        fields: ['questionText', 'action', 'questionId'],
                        proxy: {
                            type: 'ajax',
                            actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                            reader: {
                                type: 'json',
                                transform: {
                                    fn: function(data) {
                                        for (let i=0; i<data.length; i++) {
                                            data[i].questionId = data[i].SelfQuestion_id;
                                            data[i].questionText = data[i].SelfQuestion_text;
                                            data[i].action = 'Восстановить';
                                        }
                                        return data;
                                    }
                                }
                            },
                            url: '/?c=DSSEditorQuestion&m=getQuestions2restore'
                        }
                    }),
                    id: 'deletedQuestionsGrid',
                    autoHeight: true,
                    width: '100%',
                    title: 'Удалённые вопросы',
                    selModel: 'cellmodel',
                    columns: [
                        {
                            dataIndex: 'questionText',
                            tdCls: 'nameTdCls',
                            name: 'questionText',
                            header: 'Текст вопроса',
                            flex: 3,
                            align: 'center'
                        },
                        {
                            dataIndex: 'action',
                            tdCls: 'nameTdCls',
                            header: 'Восстановить',
                            name: 'action',
                            flex: 1,
                            align: 'center'
                        },
                        {
                            dataIndex: 'questionId',
                            name: 'questionId',
                            header: 'Идентификатор',
                            xtype: 'hidden'
                        }
                    ],
                    listeners: {
                        cellclick : function(view, cell, cellIndex, record, row, rowIndex, e) {
                            const clickedColumnName = record.getFields()[cellIndex].getName();
                            if (clickedColumnName === 'action') {
                                restoreQuestionForm.putQuestion2restore(
                                    questionForm.loadMask,
                                    moduleId,
                                    moduleName,
                                    record.get('questionId'),
                                    onSuccess,
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
                    id: 'emptyLabel2restore',
                    border: false,
                    html: 'Список удалённых вопросов пуст'
                }),

                new Ext6.form.FormPanel({
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
                                    restoreQuestionForm.remove(genQuestionPanel);
                                    restoreQuestionForm.show(genQuestionPanel, questionForm, moduleId, moduleName, pagination, onSuccess, onFailure);
                                }
                            }
                        }),

                        new Ext6.Button({
                            text: 'Следующая страница',
                            flex: 1,
                            handler: function() {
                                if (restoreQuestionForm.count === pagination.limit) {
                                    // поскольку общее количество модулей на передаётся от АПИ,
                                    // определение последней страницы выполняется по косвенным признакам (неточно):
                                    // количество модулей на странице меньше limit -
                                    // значит страница последняя
                                    pagination.offset += pagination.limit;
                                    restoreQuestionForm.remove(genQuestionPanel);
                                    restoreQuestionForm.show(genQuestionPanel, questionForm, moduleId, moduleName, pagination, onSuccess, onFailure);
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

        questionForm.loadMask.show();
        const restoreQuestionPanel = genQuestionPanel.getComponent('restoreQuestionPanel');
        const deletedQuestionsGrid = restoreQuestionPanel.getComponent('deletedQuestionsGrid');
        const emptyLabel = restoreQuestionPanel.getComponent('emptyLabel2restore');
        deletedQuestionsGrid.getStore().load({
            params: {
                moduleId: moduleId,
                moduleName: moduleName,
                offset: pagination.offset,
                limit: pagination.limit
            },
            callback: function(records, operation, success) {
                questionForm.loadMask.hide();
                emptyLabel.setHidden(true);
                restoreQuestionForm.count = records.length;
                if (!success) {
                    onFailure();
                }
                else if (records[0].data === 'empty') {
                    deletedQuestionsGrid.setHidden(true);
                    emptyLabel.setHidden(false);
                }
                else if (!Number.isInteger(records[0].get('SelfQuestion_id'))) {
                    onFailure();
                }
            }
        });
    },


    remove: function(genQuestionPanel) {
        const restoreQuestionPanel = genQuestionPanel.getComponent('restoreQuestionPanel');
        genQuestionPanel.remove(restoreQuestionPanel);
    }
});
