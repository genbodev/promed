/**
 * Форма редактирования параметров экспертной системы
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
 * @version      17.04.2019
 */
Ext6.define('common.DSS.Editor.DSSEditorExpertSystemForm', {


    /**
     * Запрос получения таблицы баллов
     *
     * Сразу вернёт опросник, список заключений и таблицу баллов
     */
    _getBalls: function(loadMask, diagModule, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorES&m=getBalls',
            params: {
                moduleId: diagModule.moduleId,
                moduleName: diagModule.moduleName
            },
            success: function(response, opts) {
                var data;

                loadMask.hide();
                try {
                    data = JSON.parse(response.responseText);
                } catch(e) {
                    onFailure();
                    return;
                }
                onSuccess(data[0].questions, data[0].results, data[0].balls);
            },
            failure: function(response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Запрос обновления балла по данному заключени. у данного варианта ответа
     *
     */
    _putBall: function(loadMask, diagModule, resultId, answerVariantId, value, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSSEditorES&m=putBall',
            params: {
                moduleId: diagModule.moduleId,
                moduleName: diagModule.moduleName,
                answerVariantId: answerVariantId,
                resultId: resultId,
                value: Math.round(parseFloat(value.replace(',', '.'))*100)
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
     * Сформировать пустую таблицу баллов
     *
     */
    _makeEmptyBallTable: function() {
        return new Ext6.form.FormPanel({
            border: false,
            html: 'Таблица баллов пуста'
        });
    },


    /**
     * Сформировать таблицу баллов
     *
     */
    _loadBallGrid: function(
        loadMask,
        genBallPanel,
        diagModule,
        questions,
        results,
        balls,
        questionnaire,
        onFailure
    ) {
        var self = this;
        var columns;
        var fields;
        var bGrid;
        var data;

        columns = [{
            dataIndex: 'text',
            tdCls: 'nameTdCls',
            header: 'Опросник',
            name: 'text',
            flex: 5,
            align: 'left',
            menuDisabled: true,
            sortable: false,
            renderer: function(val, metaData, record) {
                if (val) {
                    metaData.tdAttr = 'data-qtip="' + val + '"';
                }
                if (record.get('answerVariantId') == -1) { // вопрос
                    return ''
                        + '<span style="margin-left:' + (record.get('level'))*50 + 'px;">'
                        + '<b>' + record.get('text') + '</b>'
                        + '</span>';
                }
                // вариант ответа
                return ''
                    + '<span style="margin-left:' + (record.get('level'))*50+25 + 'px;">'
                    + record.get('text')
                    + '</span>';
            }
        }];

        fields = [
            'text'
        ];

        results.forEach(function(result) {
            fields.push(`result_${result.resultId}`);
            columns.push({
                header: result.resultName,
                tdCls: 'nameTdCls',
                dataIndex: `result_${result.resultId}`,
                flex: 1,
                align: 'center',
                tooltip: result.resultName,
                menuDisabled: true,
                sortable: false
            });
        });

        bGrid = new Ext6.grid.GridPanel({
            title: 'Таблица баллов',
            autoHeight: true,
            store: new Ext6.data.Store({
                fields: fields
            }),
            selModel: 'cellmodel',
            columns: columns,
            listeners: {
                cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                    if ((cellIndex < 1)  || (record.get('answerVariantId') < 0)) {
                        return;
                    }

                    genBallPanel.remove(bGrid);

                    self._onBallTableCellClick(
                            loadMask,
                            questionnaire,
                            genBallPanel,
                            diagModule,
                            results,
                            onFailure,
                            cellIndex,
                            record);
                }
            }
        });

        genBallPanel.add(bGrid);

        data = questionnaire.makeQuestionTree(
                questions,
                self._makeQuestion.bind(self, results, balls));

        bGrid.getStore().loadData(data.questions);
    },


    /**
     * Обработчик нажатия на ячейку таблицы - изменение баллов
     *
     */
    _onBallTableCellClick: function(loadMask, questionnaire, genBallPanel, diagModule, results, onFailure, cellIndex, record) {
        var self = this;
        var resultId;

        resultId = results[cellIndex-1].resultId;

        Ext6.Msg.prompt(
            'Изменить балл',
            'Введите новое значение: ',
            function(btn, text) {
                if ((btn === 'ok') && (text)) {
                    self._putBall(
                        loadMask,
                        diagModule,
                        resultId,
                        record.get('answerVariantId'),
                        text,
                        function onSuccess() {
                            self._onBallChanged(
                                    loadMask,
                                    genBallPanel,
                                    diagModule,
                                    questionnaire,
                                    onFailure);
                        },
                        onFailure
                    );
                } else {
                    self._onBallChanged(
                            loadMask,
                            genBallPanel,
                            diagModule,
                            questionnaire,
                            onFailure);
                }
            },
            null,
            false,
            record.get('result_' + resultId)
        );
    },


    /**
     * Обновить таблицу баллов после изменения балла
     *
     */
    _onBallChanged: function(
        loadMask,
        genBallPanel,
        diagModule,
        questionnaire,
        onFailure
    ) {
        var self = this;

        self._getBalls(
            loadMask,
            diagModule,
            function(questions, results, balls) {
                if (
                    (!questions)
                    || (!questions.length)
                    || (!results)
                    || (!results.length)
                ) {
                    genBallPanel.add(self._makeEmptyBallTable());
                    return;
                }

                self._loadBallGrid(
                        loadMask,
                        genBallPanel,
                        diagModule,
                        questions,
                        results,
                        balls,
                        questionnaire,
                        onFailure);
            },
            onFailure
        );
    },


    /**
     * Отобразить форму редактирования таблицы баллов
     *
     */
    show: function(
        loadMask,
        form,
        diagModule,
        questionnaire,
        onReturn2Questionnaire,
        on2Results,
        on2Recommendations,
        onFailure
    ) {
        var self = this;
        var genBallPanel;

        genBallPanel = new Ext6.form.FormPanel({
            id: 'genBallPanel',
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
                    text: 'Вернуться к опроснику',
                    handler: function() {
                        self.remove(form);
                        onReturn2Questionnaire();
                    }
                }),

                new Ext6.Button({
                    text: 'Заключения',
                    handler: function() {
                        self.remove(form);
                        on2Results();
                    }
                }),

                new Ext6.Button({
                    text: 'Рекомендации',
                    handler: function() {
                        self.remove(form);
                        on2Recommendations();
                    }
                })
            ]
        });
        form.add(genBallPanel);


        self._getBalls(
            loadMask,
            diagModule,
            function onSuccess(questions, results, balls) {
                if (
                    (!questions)
                    || (!questions.length)
                    || (!results)
                    || (!results.length)
                ) {
                    genBallPanel.add(self._makeEmptyBallTable());
                    return;
                }

                self._loadBallGrid(
                        loadMask,
                        genBallPanel,
                        diagModule,
                        questions,
                        results,
                        balls,
                        questionnaire,
                        onFailure);
            },
            onFailure
        );

    },


    /**
     * Удалить компоненты формы редактирования баллов с родителького компоненты
     *
     * @param {Ext.Component} form - родительский компонент
     */
    remove: function(form) {
        var genBallPanel = form.getComponent('genBallPanel');
        if (genBallPanel) {
            form.remove(genBallPanel);
        }
    },


    /**
     * Создать строки html-таблицы, соответствующие одному вопросу
     *
     */
    _makeQuestion: function(results, ballTable, question, level) {
        var rows = [];

        // вопрос
        rows.push({
            questionId: question.questionId,
            answerVariantId: -1,
            text: question.questionText,
            level: level
        });

        // варианты ответа
        question.questionAnswerVariants.forEach(function(answerVariant) {
            var row = {
                questionId: question.questionId,
                answerVariantId: answerVariant.answerVariantId,
                text: answerVariant.answerVariantId + '&nbsp;' + answerVariant.answerVariantText,
                level: level
            };

            results.forEach(function(result) {
                var column = 'result_' + result.resultId;
                var vector = ballTable[result.resultId];

                row[column] = ((vector) && (vector[answerVariant.answerVariantId]))
                    ? vector[answerVariant.answerVariantId] / 100.0
                    : 0;
            });

            rows.push(row);
        });

        return rows;
    }
});
