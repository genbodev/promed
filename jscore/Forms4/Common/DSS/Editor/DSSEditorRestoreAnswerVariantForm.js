/**
* swDSSEditorWindow - окно для редактирования опросников
*   для сбора структурированной медицинской информации и поддержки принятия решений
*
* Форма для работы с удалёнными варианта ответа
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
* @version      18.07.2018
*/

Ext6.define('common.DSS.Editor.DSSEditorRestoreAnswerVariantForm', {



    putAnswerVariant2restore: function(loadMask, moduleId, moduleName, questionId, answerVariantId, answerVariantText, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorAnswerVariant&m=putAnswerVariant2restore',
            params: {
                moduleId: moduleId,
                moduleName: moduleName,
                questionId: questionId,
                answerVariantId: answerVariantId,
                answerVariantText: answerVariantText
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


    show: function(genQuestionPanel, questionForm, moduleId, moduleName, questionId, questionText, pagination, onSuccess, onFailure) {
        const restoreAnswerVariantForm = this;
        genQuestionPanel.add(new Ext6.form.FormPanel({
            id: 'restoreAVPanel',
            title: `Восстановление удалённых вариантов ответа на вопрос <i>${questionText}</i>`,
            border: false,
            bodyPadding: 30,
            width: '100%',
            items: [

                new Ext6.Button({
                    text: 'Вернуться к опроснику',
                    id: 'restoreAVCancelButton',
                    handler: function() {
                        restoreAnswerVariantForm.remove(genQuestionPanel);
                        onSuccess();
                    }
                }),

                new Ext6.grid.GridPanel({
                    store: new Ext6.data.Store({
                        id: 'deletedAVStore',
                        autoLoad: false,
                        fields: ['answerVariantText', 'action', 'answerVariantId'],
                        proxy: {
                            type: 'ajax',
                            actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                            reader: {
                                type: 'json',
                                transform: {
                                    fn: function(data) {
                                        for (let i=0; i<data.length; i++) {
                                            data[i].answerVariantId = data[i].SelfAnswerVariant_id;
                                            data[i].answerVariantText = data[i].SelfAnswerVariant_text;
                                            data[i].action = 'Восстановить';
                                        }
                                        return data;
                                    }
                                }
                            },
                            url: '/?c=DSSEditorAnswerVariant&m=getAnswerVariants2restore'
                        }
                    }),
                    id: 'deletedAVGrid',
                    autoHeight: true,
                    width: '100%',
                    title: 'Удалённые варианты ответа',
                    selModel: 'cellmodel',
                    columns: [
                        {
                            dataIndex: 'answerVariantText',
                            tdCls: 'nameTdCls',
                            name: 'answerVariantText',
                            header: 'Текст варианта ответа',
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
                            dataIndex: 'answerVariantId',
                            name: 'questionId',
                            header: 'Идентификатор',
                            xtype: 'hidden'
                        }
                    ],
                    listeners: {
                        cellclick : function(view, cell, cellIndex, record, row, rowIndex, e) {
                            const clickedColumnName = record.getFields()[cellIndex].getName();
                            if (clickedColumnName == 'action') {
                                restoreAnswerVariantForm.putAnswerVariant2restore(
                                    questionForm.loadMask,
                                    moduleId,
                                    moduleName,
                                    questionId,
                                    record.get('answerVariantId'),
                                    record.get('answerVariantText'),
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
                    html: 'Список удалённых вариантов ответа пуст'
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
                                    restoreAnswerVariantForm.remove(genQuestionPanel);
                                    restoreAnswerVariantForm.show(genQuestionPanel, questionForm, moduleId, moduleName, questionId, questionText, pagination, onSuccess, onFailure);
                                }
                            }
                        }),

                        new Ext6.Button({
                            text: 'Следующая страница',
                            flex: 1,
                            handler: function() {
                                if (restoreAnswerVariantForm.count === pagination.limit) {
                                    // поскольку общее количество модулей на передаётся от АПИ,
                                    // определение последней страницы выполняется по косвенным признакам (неточно):
                                    // количество модулей на странице меньше limit -
                                    // значит страница последняя
                                    pagination.offset += pagination.limit;
                                    restoreAnswerVariantForm.remove(genQuestionPanel);
                                    restoreAnswerVariantForm.show(genQuestionPanel, questionForm, moduleId, moduleName, questionId, questionText, pagination, onSuccess, onFailure);
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
        const restoreAVPanel = genQuestionPanel.getComponent('restoreAVPanel');
        const deletedAVGrid = restoreAVPanel.getComponent('deletedAVGrid');
        const emptyLabel = restoreAVPanel.getComponent('emptyLabel2restore');
        deletedAVGrid.getStore().load({
            params: {
                moduleId: moduleId,
                moduleName: moduleName,
                questionId: questionId,
                offset: pagination.offset,
                limit: pagination.limit
            },
            callback: function(records, operation, success) {
                questionForm.loadMask.hide();
                emptyLabel.setHidden(true);
                restoreAnswerVariantForm.count = records.length;
                if (!success) {
                    onFailure();
                } else if (records[0].data === 'empty') {
                    deletedAVGrid.setHidden(true);
                    emptyLabel.setHidden(false);
                } else if (!Number.isInteger(records[0].get('SelfAnswerVariant_id'))) {
                    onFailure();
                }
            }
        });

    },


    remove: function(genQuestionPanel) {
        const restoreAVPanel = genQuestionPanel.getComponent('restoreAVPanel');
        genQuestionPanel.remove(restoreAVPanel);
    }
});
