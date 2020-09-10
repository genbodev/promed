/**
 * Панель для отображения результата работы СППР для анкеты
 *
 * swDSSWindow - окно для работы с опросником
 *   для сбора структурированной медицинской информации и поддержки принятия решений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.DSS
 * @access       public
 * @copyright    Copyright (c) 2018-2019 Swan Ltd.
 * @author       Yaroslav Mishlanov <ya.mishlanov@swan-it.ru>
 * @since        12.09.2018
 * @version      10.04.2019
 */
Ext6.define('common.DSS.Doctor.DSSDoctorConclusionForm', {


    /**
     * Получить результат для анкеты от сервера АПИ
     *
     * @param {object} patient - пациент
     * @param {object} diagModule - модуль
     * @param {object} session - анкета
     * @param {function(object conclusions)} onSuccess - колбек для успешного завершения
     * @param {function(string errorMsg)} onFailure - колбек в случае ошибки
     * @param {object} loadMask - индикатор ожидания
     */
    _getConclusions: function(patient, diagModule, session, onSuccess, onFailure, loadMask) {
        loadMask.show();
        Ext6.Ajax.request({

            url: '/?c=DSS&m=getPatientConclusions',

            params: {
                Person_id: patient.patientId,
                moduleId: diagModule.moduleId,
                moduleName: diagModule.moduleName,
                sessionId: session.sessionId,
                sessionStartDT: session.sessionStartDT
            },

            success: function(response, opts) {
                var responseData;
                var conclusions;

                loadMask.hide();

                responseData = JSON.parse(response.responseText);
                conclusions = responseData[0]; // трюк чтобы передались имена полей

                onSuccess(conclusions);
            },

            failure: function(response, opts) {
                loadMask.hide();
                onFailure('_getConclusions failed');
            }
        });
    },


    /**
     * Сформировать html-представление одного вопроса для представления анкеты
     * в форме связного текста жалоб
     *
     * Ответы пациента входят в виде предложений-утверждений для получения
     * связного текста жалоб, либо, если для варианта ответа не задана форма
     * предложения-утверждения, в виде вопрос: ответ.
     * @param question - вопрос
     * @param {int[]} answers - ответы в анкете
     * @return {str[]}
     */
    _makeQuestion4TextView(question, answers) {
        var text;

        text = question.questionAnswerVariants.reduce(
            function(text, answerVariant) {
                var statement;
                var questionText;

                if (answers.indexOf(answerVariant.answerVariantId) < 0) {
                    // этот вариант ответа не выбран
                    return text;
                }

                if (answerVariant.answerVariantStatement) {
                    // если заполнено поле statement для данного вопроса - использовать его
                    // Удалить точку в конце (если есть)
                    statement = answerVariant.answerVariantStatement.trim();
                    if (statement.charAt(statement.length-1) === '.') {
                        statement = statement.slice(0, -1);
                    }
                    return text + statement;
                } else {
                    // если поле statement для варианта ответа не заполнено -
                    // сформировать текст в виде вопрос: ответ
                    // если в конце текста вопроса есть "?" - убрать
                    questionText = question.questionText.trim();
                    if (questionText.charAt(questionText.length-1) === '?') {
                        questionText = questionText.slice(0, -1);
                    }
                    return text + `${questionText}: ${answerVariant.answerVariantText}`;
                }
            },
            ''
        );

        if (text === '') {
            // нет выбранных вариантов ответа на этот вопрос
            return [''];
        } else if (
            (question.questionVariables.length === 1)
            && (question.questionVariables[0].answerVariantId === -1)
        ) {
            // один из корневых вопросов - вывести жирно и с новой строки
            return ['<li style="margin-top: 8px;"><b>' + text + '</b></li>'];
        } else {
            // конкретизирующий вопрос
            return [text + '; '];
        }
    },


    /**
     * Отобразить панель результатов
     *
     * Панель результатов содержит анкету в форме связного текста жалоб
     * и результаты и рекомендации, полученные СППР для этой анкеты.
     * То есть на панели результатов показаны исходные данные для СППР - жалобы -
     * и результат работы СППР - заключения и рекомендации.
     * @param data: {patient, module, session, questions, answers}
     * @param {Ext.Component} generatedTextPanel - панель для анкеты в форме
     *     связного текста жалоб
     * @param {Ext.Component} resultPanel - панель для заключений и рекомендаций
     * @param {Ext.Component} loadMask - индикатор ожидания
     * @param questionnaire - объект, предоставляющий методы для формирования
     *     дерева вопросов опросника
     * @param {function(result)} onExplainResult - колбек для запуска подсистемы
     *     объяснения оценки, полученной СППР для заключения
     * @param {function(errorMsg)} onFailure - колбек- в случае обнаружения ошибки
     */
    show: function(data, generatedTextPanel, resultPanel, loadMask, questionnaire, onExplainResult, onFailure) {
        // заполнить панель текста
        generatedTextPanel.update(
            questionnaire.makeFullQuestionTree(
                   data.questions,
                   data.answers,
                   this._makeQuestion4TextView)
           .join('')
           || 'Анкета пуста'
        );

        // заполнить панель результатов
        this._getConclusions(
                data.patient,
                data.module,
                data.session,
                this._showResults.bind(
                        this,
                        resultPanel,
                        this._onExportSession.bind(this, data.patient, data.module, data.session),
                        onExplainResult),
                function onFailure(err) {
                    // ничего не делать
                },
                loadMask);
    },


    /**
     * Заполнить панель результатов
     *
     * @param {Ext.Component} resultPanel - панель результатов
     * @param {function()} onExportSession - экспортировать анкету как файл
     * @param {function(result)} onExplainResult - запустить подсистему объяснения
     *     полученной оценки для заключения
     * @param {object} conclusions - результат работы СППР для анкеты -
     *     оценки для заключений и рекомендации
     */
    _showResults: function(resultPanel, onExportSession, onExplainResult, conclusions) {
        /**
         * Оцценки для заключений, обработанные для вставки в гриды
         */
        var transformedScores;

        /**
         * Гриды
         */
        var markedResultsGrid; // для заключений, для которых оценка превышает порог
        var allResultsGrid; // для оценок для всех заключений
        var recommendationsGrid; // для рекомендаций

        markedResultsGrid = this._makeMarkedResultsGrid();
        allResultsGrid = this._makeAllResultsGrid(onExplainResult);
        recommendationsGrid = this._makeRecommendationsGrid();

        resultPanel.add(
            this._makeResultPanel(
                    markedResultsGrid,
                    allResultsGrid,
                    recommendationsGrid,
                    onExportSession)
        );

        transformedScores = this._transformScores4Grids(conclusions.scores);
        markedResultsGrid.getStore().loadData(transformedScores.markedResults);
        allResultsGrid.getStore().loadData(transformedScores.allResults);

        recommendationsGrid.getStore().loadData(
            this._transformRecommendations4Grid(conclusions.recommendations)
        );
    },


    /**
     * Обработчик нажатия на кнопку Экспортировать анкету как файл
     *
     * @param patient
     * @param diagModule
     * @param session
     */
    _onExportSession: function(patient, diagModule, session) {
        var form = new Ext6.form.Panel({
            standardSubmit: true,
            url: '/?c=DSS&m=getSessionAsFile',
            method: 'POST'
        });

        form.submit({
            target: '_blank',
            params: {
                Person_id: patient.patientId,
                moduleId:  diagModule.moduleId,
                moduleName:  diagModule.moduleName,
                sessionId:  session.sessionId,
                sessionStartDT:  session.sessionStartDT
            }
        });

        Ext6.defer(function() {
            form.close();
        }, 100);
    },


    /**
     * Сформировать грид результатов - заключений, для которых превышен порог оценки
     *
     * @return {Ext.Component}
     */
    _makeMarkedResultsGrid: function() {
        var self = this;

        return new Ext6.grid.GridPanel({
            border: false,
            viewConfig: {
                deferEmptyText: false,
                emptyText: '<div style="text-align: center;">Результат не получен</div>'
            },
            store: new Ext6.data.Store({
                autoLoad: false,
                fields: [
                    'resultId',
                    'resultName',
                    'conclusionValue'
                ]
            }),
            columns: [{
                dataIndex: 'resultId',
                hidden: true
            }, {
                dataIndex: 'resultName',
                header: 'Заключение',
                flex: 3,
                align: 'center',
                renderer: self._addTip
            }, {
                dataIndex: 'conclusionValue',
                header: 'Оценка',
                flex: 1,
                align: 'center',
                renderer: self._addTip
            }]
        });
    },


    /**
     * Сформировать грид для оценок для всех заключений
     *
     * @param {function(result)} onExplainResult - функция для запуска
     *     подсистемы объяснений оценки для результата
     * @return {Ext.Component}
     */
    _makeAllResultsGrid: function(onExplainResult) {
        var self = this;

        return new Ext6.grid.GridPanel({
            title: 'Оценки для всех заключений',
            border: false,
            hidden: true,
            selModel: 'cellmodel',
            viewConfig: {
                deferEmptyText: false,
                emptyText: '<div style="text-align: center;">Список пуст</div>'
            },
            store: new Ext6.data.Store({
                autoLoad: false,
                fields: [
                    'resultName',
                    'conclusionValue'
                ]
            }),
            columns: [{
                dataIndex: 'resultName',
                header: 'Заключение',
                flex: 3,
                align: 'center',
                renderer: self._addTip
            }, {
                dataIndex: 'conclusionValue',
                header: 'Оценка',
                flex: 1,
                align: 'center',
                renderer: self._addTip
            }, {
                header: 'Расчёт',
                flex: 1,
                align: 'center',
                renderer: function(val, metaData, record) {
                    metaData.style = 'cursor:pointer;';
                    return self._addTip('Показать', metaData, record);
                }
            }],
            listeners: {
                cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                    if (cellIndex == 2) {
                        onExplainResult({
                            resultId: record.get('resultId'),
                            resultName: record.get('resultName')
                        });
                    }
                }
            }
        });
    },


    /**
     * Сформировать структуру таблицы рекомендацией
     *
     * @return {Ext.Component}
     */
    _makeRecommendationsGrid: function() {
        var self = this;

        return new Ext6.grid.GridPanel({
            border: false,
            viewConfig: {
                deferEmptyText: false,
                emptyText: '<div style="text-align: center; padding-top:24px;">Список пуст</div>'
            },
            store: new Ext6.data.Store({
                autoLoad: false,
                fields: [
                    'recommendationText'
                ]
            }),
            columns: [{
                dataIndex: 'recommendationText',
                flex: 1,
                align: 'center',
                style: 'white-space: normal;',
                renderer: self._addTip
            }]
        });
    },


    /**
     * Рендерер строки грида: добавить всплывающую подсказку и отформатировать
     *
     */
    _addTip: function(val, metaData, record) {
        if (val) {
            metaData.tdAttr = 'data-qtip="' + val + '"';
        }
        metaData.tdStyle = 'vertical-align: middle;'
        return '<div style="white-space: normal;">' + val + '</div>';
    },


    /**
     * Создать структуру панели результатов
     *
     * @param resultPanel: ExtComponent - родительский компонент
     * @return {clinicalTrialsWarning, resultGrid} - содержимое панели
     */
    _makeResultPanel: function(resultGrid, scoreGrid, recommendationGrid, onExportSession) {
        var showAllScoresButton;
        var hideAllScoresButton;

        showAllScoresButton = new Ext6.Button({
            text: 'Просмотреть оценки для всех заключений',
            style: 'padding: 16px;',
            width: '100%',
            handler: function() {
                scoreGrid.setHidden(false);
                showAllScoresButton.setHidden(true);
                hideAllScoresButton.setHidden(false);
            }
        });

        hideAllScoresButton = new Ext6.Button({
            text: 'Скрыть оценки для всех заключений',
            style: 'padding: 16px;',
            hidden: true,
            width: '100%',
            handler: function() {
                scoreGrid.setHidden(true);
                showAllScoresButton.setHidden(false);
                hideAllScoresButton.setHidden(true);
            }
        });

        return [
            new Ext6.Button({
                text: 'Экспорт анкеты в файл',
                style: 'margin: 32px 24px;',
                handler: onExportSession
            }),
            new Ext6.form.FormPanel({
                border: false,
                scrollable: true,
                width: '100%',
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                items: [
                    new Ext6.form.FormPanel({
                        title: 'Результат',
                        border: true,
                        flex: 1,
                        items: [
                            resultGrid,
                            scoreGrid,
                            showAllScoresButton,
                            hideAllScoresButton
                        ]
                    }),
                    new Ext6.form.FormPanel({
                       title: 'Рекомендации',
                       border: true,
                       flex: 1,
                       items: [
                            recommendationGrid
                       ]
                   })
                ]
            })
        ];
    },


    /**
     * Преобразование данных из scores (оценки для заключений, полученные из АПИ)
     * в формат для вставки в Grid
     *
     * @param scores - список оценок для заключений от сервета АПИ
     * @return {markedResults, allResults} - список оценок для заключений,
     *     для которых превышен порог оценки и список оценок для всех заключений
     *     в формате для вставки в гриды
     */
    _transformScores4Grids: function(scores) {
        var markedResults = [];
        var allResults = [];

        scores.forEach(function(score) {
            var item = {
                resultId: score.result.resultId,
                resultName: score.result.resultName,
                conclusionValue: score.value/100 + '&nbsp;%'
            };
            if (score.isMarked === true) {
                markedResults.push(item);
            }
            allResults.push(item);
        });

        return {markedResults: markedResults, allResults: allResults};
    },


    /**
     * Преобразование данных из resultRecommendations (рекомендации, полученные из АПИ)
     * в формат для вставки в Grid
     *
     * @param resultRecommendations - список рекомендаций от сервета АПИ
     * @return recommendationRow[] - список строк рекомендаций для recommendationGrid
     */
    _transformRecommendations4Grid: function(resultRecommendations) {
        var recommendationTypes;

        var recomendationRows = [];

        recommendationTypes = this._getRecommendationTypes(resultRecommendations);

        recommendationTypes.forEach(function(recommendationType) {
            var addedRecomendationIds = [];
            var resultRecommendations4Type; // список рекомендаций этого типа

            recomendationRows.push({
                recommendationText: '<b>' + recommendationType.recommendationTypeName + '</b>',
                isTypeRow: true
            });

            resultRecommendations4Type = resultRecommendations.filter(function(resultRecommendation) {
                return (
                    resultRecommendation.recommendation.recommendationType.recommendationTypeId
                    === recommendationType.recommendationTypeId
                );
            });

            resultRecommendations4Type.forEach(function(resultRecommendation) {
                if (
                    addedRecomendationIds.indexOf(resultRecommendation.recommendation.recommendationId) < 0
                ) {
                    // если такой рекомендации ещё нет - просто добавить
                    addedRecomendationIds.push(resultRecommendation.recommendation.recommendationId);
                    recomendationRows.push(resultRecommendation.recommendation);
                } else {
                    // если такая рекомендация уже добавлена - учесть
                    // приоритет рекомендации перед рекомендацией "по показаниям"
                    if (resultRecommendation.isConditional) {
                        recomendationRows = recomendationRows.map(function(recomendationRow) {
                            return (recomendationRow.recommendationId === resultRecommendation.recommendation.recommendationId)
                                ? resultRecommendation.recommendation
                                : recomendationRow;
                        });
                    }
                }
            });
        });

        return recomendationRows.map(function(recomendationRow) {
            if (recomendationRow.isTypeRow) {
                return recomendationRow;
            } else {
                return {
                    recommendationText: recomendationRow.recommendationText
                            + ((recomendationRow.isConditional)
                                    ? ' по показаниям'
                                    : '')
                };
            }
        });
    },


    /**
     * Получить из списка рекомендаций список типов рекомендацией
     *
     * @param resultRecommendation[]
     * @return recommendationType[]
     */
    _getRecommendationTypes(resultRecommendations) {
        var recommendationTypes = [];
        var recommendationTypeIds = [];

        resultRecommendations.forEach(function(resultRecommendation) {
            var typeId = resultRecommendation.recommendation.recommendationType.recommendationTypeId;
            if (recommendationTypeIds.indexOf(typeId) < 0) {
                recommendationTypeIds.push(typeId);
                recommendationTypes.push(
                    resultRecommendation.recommendation.recommendationType
                );
            }
        });

        return recommendationTypes;
    }
});
