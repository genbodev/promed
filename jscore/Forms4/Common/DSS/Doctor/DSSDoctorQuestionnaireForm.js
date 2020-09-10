/**
 * Форма для работы с анкетой
 *
 * swDSSWindow - окно для работы с опросником
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
 * @since        12.09.2018
 * @version      11.04.2019
 */
Ext6.define('common.DSS.Doctor.DSSDoctorQuestionnaireForm', {



    /**
     * Отобразить форму опроса в окне
     *
     * @param state: object - состояние приложения (выбранный пациент, модуль, анкета)
     * @param form: ExtComponent - родительский компонент
     * @param loadMask: object - индикатор ожидания
     * @param onShowActualState: function - обработчик перехода на форму актуального состояния пациента
     * @param onShowDynamics: function - обработчик перехода на форму просмотра динамики состояния
     * @param showConclusionPanel: function - фукнкция, отображающая панель результатов
     * @param onFailure - функция-обработчик ошибок
     */
    show: function(state, form, loadMask, onShowActualState, onShowDynamics, showConclusionPanel, onFailure) {
        var self = this;
        var genQuestionPanel;
        var textPanel;
        var resultPanel;
        var onSoftFailure;
        var defaultResult;

        genQuestionPanel = new Ext6.form.FormPanel({
            id: 'genQuestionPanel',
            border: false,
            width: '100%',
            items: [
                new Ext6.form.FormPanel({
                    border: false,
                    autoHeight: true,
                    html: state.patient.patientFullName + ', ' + state.patient.patientAge + ' лет',
                    bodyPadding: 16
                }),
                new Ext6.button.Segmented({
                    allowMultiple: false,
                    allowDepress: false,
                    items: [{
                        name: 'actual',
                        text: 'Текущие данные жалоб пациента'
                    }, {
                        name: 'dynamics',
                        text: 'Динамика жалоб пациента',
                    }, {
                        name: 'one',
                        text: 'Анкета',
                        pressed: true
                    }],
                    listeners: {
                        toggle: function(container, button, pressed) {
                            if (button.name === 'actual') {
                                onShowActualState();
                            }
                            if (button.name === 'dynamics') {
                                onShowDynamics();
                            }
                        }
                    }
                }),
                new Ext6.form.FormPanel({
                    border: false,
                    style: 'margin-top: 24px;',
                    html: ''
                        + '<h1>'
                        +    'Модуль <i>"' + state.module.moduleName + '"</i>'
                        + '</h1>'
                        + '<h2>'
                        +    'Анкета ' + state.session.sessionId
                        +    ' от ' + state.session.sessionCloseDTLF
                        + '</h2>'
                })
            ]
        });
        form.add(genQuestionPanel);

        textPanel = new Ext6.form.FormPanel({
            border: false,
            bodyPadding: 16
        });
        genQuestionPanel.add(textPanel);

        resultPanel = new Ext6.form.FormPanel({
            border: false,
            style: 'margin-top: 24px;'
        });
        genQuestionPanel.add(resultPanel);

        onSoftFailure = function onFailure(errorMsg) {
            // параллельно загружается несколько секций страницы,
            // было бы неправильно при ошибке в одной секции перезагружать приложение
            Ext6.Msg.alert('Ошибка', errorMsg);
        };

        defaultResult = {
            resultId: 0
        };

        self.questionPanel.getQuestionnaire(
                state,
                loadMask,
                function onQuestionnaireFetched(questions, answers) {
                    showConclusionPanel(
                        textPanel,
                        resultPanel,
                        questions,
                        answers,
                        self._onQuestionnaireFetched.bind(
                                self,
                                state,
                                genQuestionPanel,
                                loadMask,
                                onSoftFailure,
                                questions,
                                answers)
                    );

                    self._onQuestionnaireFetched(
                            state,
                            genQuestionPanel,
                            loadMask,
                            onSoftFailure,
                            questions,
                            answers,
                            defaultResult);
                },
                onSoftFailure);
    },


    /**
     * Колбек для загрузки анкеты
     *
     */
    _onQuestionnaireFetched: function(
        state,
        genQuestionPanel,
        loadMask,
        onSoftFailure,
        questions,
        answers,
        result
    ) {
        var self = this;

        var onBallsFetched = function onBallsFetched(balls) {
            self.questionPanel.remove(genQuestionPanel);
            self.questionPanel.show(
                questions,
                answers,
                result,
                balls,
                genQuestionPanel,
                self.questionnaire,
                onSoftFailure
            );
        };

        self.questionPanel.getResultBalls(
            state.patient.patientId,
            state.module,
            result.resultId,
            onBallsFetched,
            function ignoreError() {
                onBallsFetched([]);
            },
            loadMask);
    },


    /**
     * Удалить форму опроса (очистить окно)
     *
     * @param form: Ext6.Component - родительский компонент
     */
    remove: function(form) {
        var genQuestionPanel = form.getComponent('genQuestionPanel');
        if (genQuestionPanel) {
            form.remove(genQuestionPanel);
        }
    },


    /**
     * Панель анкеты
     */
    questionPanel: {

        /**
         * Получить вопросы
         *
         */
        _getQuestions: function(patientId, moduleData, session, onSuccess, onFailure, loadMask) {
            loadMask.show();

            Ext6.Ajax.request({
                url: '/?c=DSS&m=getQuestions',

                params: {
                    Person_id: patientId,
                    moduleId: moduleData.moduleId,
                    moduleName: moduleData.moduleName,
                    sessionId: session.sessionId,
                    sessionStartDT: session.sessionStartDT
                },

                success: function (response, opts) {
                    var data;

                    loadMask.hide();
                    data = JSON.parse(response.responseText);
                    if ((typeof data[0]) != 'object') {
                        onFailure(data[0]);
                        return;
                    }
                    onSuccess(data);
                },

                failure: function (response, opts) {
                    loadMask.hide();
                    onFailure();
                }
            });
        },


        /**
         * Получить ответы в анкете
         *
         */
        _getAnswers: function(patientId, moduleData, session, onSuccess, onFailure, loadMask) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSS&m=getAnswers',

                params: {
                    Person_id: patientId,
                    moduleId: moduleData.moduleId,
                    moduleName: moduleData.moduleName,
                    sessionId: session.sessionId,
                    sessionStartDT: session.sessionStartDT
                },

                success: function (response, opts) {
                    var answers;

                    loadMask.hide();
                    answers = (function(response) {
                        var data;

                        data = JSON.parse(response.responseText);
                        if (data[0] === 'empty') {
                            return [];
                        }
                        return data;
                    })(response);

                    if ((answers[0]) && (isNaN(parseInt(answers[0])))) {
                        onFailure('Ill structured server response');
                        return;
                    }
                    onSuccess(answers);
                },

                failure: function (response, opts) {
                    loadMask.hide();
                    onFailure();
                }
            });
        },


        /**
         * Получить вектор баллов для заключения
         *
         */
        getResultBalls: function(patientId, moduleData, resultId, onSuccess, onFailure, loadMask) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSS&m=getResultBalls',
                params: {
                    Person_id: patientId,
                    moduleId: moduleData.moduleId,
                    moduleName: moduleData.moduleName,
                    resultId: resultId
                },
                success: function (response, opts) {
                    var resultBalls;
                    loadMask.hide();
                    resultBalls = ((response) => {
                        var data;
                        data = JSON.parse(response.responseText);
                        if (data[0] === 'empty') {
                            return [];
                        }
                        return data;
                    })(response);

                    if ((resultBalls[0]) && (!resultBalls[0].value)) {
                        onFailure('Ill structured server response');
                        return;
                    }
                    if ((resultBalls[0]) && (!resultBalls[0].answerVariantId)) {
                        onFailure('Ill structured server response');
                        return;
                    }

                    onSuccess(resultBalls);
                },
                failure: function (response, opts) {
                    loadMask.hide();
                    onFailure();
                }
            });
        },


        /**
         * Получить анкету - опросник + ответы
         *
         */
        getQuestionnaire: function(state, loadMask, onSuccess, onFailure) {
            var self = this;

            const questions = self._getQuestions(
                state.patient.patientId,
                state.module,
                state.session,
                function onQuestionsFetched(questions) {
                    self._getAnswers(
                        state.patient.patientId,
                        state.module,
                        state.session,
                        function onAnswersFetched(answers) {
                            onSuccess(questions, answers);
                        },
                        onFailure,
                        loadMask
                    );
                },
                onFailure,
                loadMask
            );
        },


        /**
         * Преобразование строки таблицы опросника
         *
         * @param record - данные строки таблицы
         * @return str - html-строка для отображения в строке таблицы
         */
        _renderQuestionnaireTableRow: function(record) {
            var level;
            var levelMargin;
            var answerVariantMargin;

            level = parseInt(record.get('level')); // номер уровня
            levelMargin = level*50; // отступ уровня
            answerVarianMargin = levelMargin + 25; // отступ вариантов ответа
            if (record.get('answerVariantId') === -1) { // вопрос
                return `
                    <div style="margin-left: ${levelMargin}px; white-space: normal;">
                        <b>${record.get('text')}</b>
                    </div>
                `;
            } else { // вариант ответа
                if (record.get('checked')) { // отмеченный вариант ответа
                    return `
                        <div style="margin-left: ${answerVarianMargin}px; white-space: normal;">
                            <span style="background-color: #faf0c1;">${record.get('text')}</span>
                        </div>
                    `;
                } else { // неотмеченный вариант ответа
                    return `
                        <div style="margin-left: ${answerVarianMargin}px; white-space: normal;">
                            ${record.get('text')}
                        </div>
                    `;
                }
            }
        },


        /**
         * Отобразить панель анкеты
         *
         * @param state - состояние приложения (данные о пациента, модуле, сессии)
         * @param parentComponent - родительский компонент, где отображать форму анкеты (страница поросника)
         * @param loadMask - общий индикатор загрузки
         * @param questionnaire - вспомогательные функции для отображения дерева вопросов
         * @param function onFailure - обработчик ошибки
         */
        show: function(
            questions,
            answers,
            result,
            balls,
            genQuestionPanel,
            questionnaire,
            onSoftFailure
        ) {
            var self = this;
            var questionPanel;
            var questionGrid;
            var data;

            // отдельная панель для анкеты чтобы её можно было удалить и создать заново
            questionPanel = new Ext6.form.FormPanel({
                id: 'questionPanel',
                border: false
            });
            genQuestionPanel.add(questionPanel);

            questionGrid = new Ext6.grid.GridPanel({
                title: 'Анкета',
                autoHeight: true,
                style: 'margin-top: 40px;',
                hideHeaders: (!result.resultId),
                store: new Ext6.data.Store({
                    fields: ['answerVariantId', 'text', 'level', 'checked', 'disabled']
                }),
                columns: [{
                    dataIndex: 'text',
                    header: 'Опросник',
                    flex: 5,
                    align: 'left',
                    renderer: function(val, metaData, record) {
                        // добавить всплывающую подсказку
                        if (val) {
                            metaData.tdAttr = 'data-qtip="' + val + '"';
                        }
                        // преобразовать
                        return self._renderQuestionnaireTableRow(record);
                    }
                }, {
                    dataIndex: 'resultBall',
                    header: `${result.resultName}: балл`,
                    flex: 1,
                    align: 'center',
                    renderer: function(val, metaData, record) {
                        if (val) {
                            metaData.tdAttr = 'data-qtip="' + val + '"';
                        }
                        if (record.get('checked')) {
                            return ''
                                + '<span style="background-color: #faf0c1;">'
                                +   val
                                + '</span>';
                        }
                        return val;
                    }
                }, {
                    dataIndex: 'answerVariantId',
                    xtype: 'hidden'
                }, {
                    dataIndex: 'level',
                    xtype: 'hidden'
                }, {
                    dataIndex: 'checked',
                    xtype: 'hidden'
                }, {
                    dataIndex: 'disabled',
                    xtype: 'hidden'
                }]
            });
            questionGrid.disableColumnHeaders(true);
            questionPanel.add(questionGrid);

            //if (balls.length > 0) {
                data = questionnaire.makeFullQuestionTree(
                    questions,
                    answers,
                    questionnaire.makeQuestion.bind(self, balls)
                );
            /*} else {
                data = questionnaire.makeQuestionTree(
                    questions,
                    answers,
                    questionnaire.makeQuestion.bind(self, balls)
                );
            }*/

            questionGrid.getStore().loadData(data);
        },


        /**
         * Удалить форму анкеты
         *
         */
        remove: function(parentComponent) {
            var questionPanel = parentComponent.getComponent('questionPanel');
            if (questionPanel) {
                parentComponent.remove(questionPanel);
            }
        }
    },



    /**
     * Вспомогательные методы для работы с опросником
     */
    questionnaire: {

        /**
         * Получить список корневых вопросов
         *
         *      (вопросов, которые не зависят ни от одного другого вопроса),
         *  отсортированный по позициям вопросов в списке корневых вопросов
         *
         * @param question[] - опросник
         * @return question[]
         */
        _getRootQuestions: function(questionsList) {
            return questionsList
                .filter(function(question) {
                    var answerVariantId;

                    // если зависимостей больше одной - вопрос точно не корневой
                    if (question.questionVariables.length > 1) {
                        return false;
                    }
                    // идентификатор варианта ответа единственной зависимости вопроса
                    answerVariantId = question.questionVariables[0].answerVariantId;
                    // если идентификатор не числовой (например, 'null')
                    // или -1 - зависимости фиктивная, вопрос корневой
                    if (
                        (Number.isInteger(answerVariantId))
                        && (parseInt(answerVariantId) > -1)
                    ) {
                        return false;
                    }
                    return true;
                })
                .sort(
                    function(a, b) {
                        var aPosition = a.questionVariables[0].questionPosition;
                        var bPosition = b.questionVariables[0].questionPosition;
                        return aPosition - bPosition;
                    }
                );
        },


        /**
         * Получить вопросы, напрямую зависящие от данного вопроса (потомки вопроса)
         * (имеющие в зависимостях варианты ответа на данный вопрос)
         *
         * @param question[] - список всех вопросов
         * @param question - вопрос, потомков которого нужно найти
         * @return question[] - список потомков вопроса, отсортированный по позиции
         */
        _getSuccessors: function(questionsList, question) {
            var answerVariantIds;
            var successors;

            answerVariantIds = question.questionAnswerVariants.map(function(answerVariant) {
                return answerVariant.answerVariantId;
            });
            successors = [];
            questionsList.forEach(function(currentQuestion) {
                currentQuestion.questionVariables.forEach(function(variable) {
                    if (answerVariantIds.indexOf(variable.answerVariantId) > -1) {
                        successors.push({
                            questionId: currentQuestion.questionId,
                            questionPosition: variable.questionPosition
                        });
                    }
                });
            });

            return successors.sort(
                function(a, b) {
                    return a.questionPosition - b.questionPosition;
                }
            );
        },


        /**
         * Найти объект вопроса в опроснике (списке объектов вопросов)
         * по идкетификатору вопроса
         *
         * @param question[] questionList - опросник (список объктов вопросов)
         * @param int questionId - идентификатор искомого вопроса
         * @return question - объект вопроса
         * @throws
         */
        _getQuestionById: function(questionId, questionList) {
            var foundQuestions;

            foundQuestions = questionList.filter(function(question) {
                return (question.questionId === questionId);
            });

            if (foundQuestions.length !== 1) {
                throw 'Question not found in the list';
            }

            return foundQuestions[0];
        },


        /**
         * Проверить, выполняется ли условие для отображения данного вопроса
         *
         * @param question - объект вопроса
         * @param int[] givenAnswers - список ответов
         */
        _checkCondition: function(question, givenAnswers) {
            var lineNumber = 0;	// номер строки в таблице истинности
            var variables = question.questionVariables;	// сокращение имени переменной
            var position;

            for (position = 0; position < variables.length; position++) {
                if (givenAnswers.indexOf(variables[position].answerVariantId) > -1) {
                    lineNumber += (1 << (variables.length-1)) >> (position);
                }
            }

            if ((question.questionCondition >> lineNumber) % 2 == 1) {
                return true;
            }
            return false ;
        },


        /**
         * Определить, есть ли среди ответов в анкете ответы на указанный вопрос
         *
         * @param int questionId - идентификатор вопроса
         * @param int[] answerVariants - список ответов
         * @param question[] questionList - опросник
         * @return bool
         */
        _hasAnswers: function(question, givenAnswers) {
            var answerVariant;

            for (answerVariant of question.questionAnswerVariants) {
                if (givenAnswers.indexOf(answerVariant.answerVariantId) > -1) {
                    return true;
                }
            }
            return false;
        },


        /**
         * Сформировать представление вопроса (с вариантами ответов)
         * так, чтобы потом список вопросов можно было загрузить в грид
         *
         * @param question - объект вопроса
         * @param int[] givenAnswers - ответы
         * @param int level - уровень вложенности для данного вопроса в дереве вопросов
         * @return row[] - список строк для грида (соответствующие этому вопросу и вариантам ответа)
         * Row: {
         *  answerVariantId: int,
         *  text: str,
         *  level: int,
         *  checked: 'checked' | '' - есть ответ или нет
         *  disabled: 'disabled' | '' - запрещение/разрешение изменения ответа
         * }
         * При этом первая строка соответствует вопросу (answerVariantId устанавливается -1)
         */
        makeQuestion: function(resultBalls, question, givenAnswers, level) {
            var rows = [{
                answerVariantId: -1,
                text: question.questionText,
                level: level,
                checked: false,
                disabled: 'disabled',
                resultBall: ''
            }];

            question.questionAnswerVariants.forEach(function(answerVariant) {
                var checked = (function() {
                    if (givenAnswers.indexOf(answerVariant.answerVariantId) < 0) {
                        return '';
                    }
                    return 'checked';
                })();

                var resultBall = resultBalls.reduce(function(acc, item) {
                    if (item.answerVariantId === answerVariant.answerVariantId) {
                        return item.value;
                    } else {
                        return acc;
                    }
                }, 0);

                rows.push({
                    answerVariantId: answerVariant.answerVariantId,
                    text: answerVariant.answerVariantText,
                    statement: answerVariant.answerVariantStatement,
                    level: level,
                    checked: checked,
                    disabled: 'disabled',
                    resultBall: resultBalls.length ? resultBall/100 : ''
                });
            });

            return rows;
        },


        /**
         * Сформировать список вопросов в порядке, соответствующем дереву вопросов
         * (с учётом зависимостей и позиций вопросов, а также данных ответов в анкете).
         * При этом форматирование каждого вопроса опредляется функцией makeQuestion,
         * которой передаётся параметр level - уровень вопроса в дереве
         *
         * @param questionsList: question[]
         * @param givenAnswers: int[]
         * @param makeQuestion: function(question, givenAnswers, level)
         * @return row[]
         */
        makeQuestionTree: function(questionsList, givenAnswers, makeQuestion) {
            var self = this;

            var stack = self._getRootQuestions(questionsList);
            var askedQuestions = [];

            var result = [];
            var level = 0;
            var top;
            var topHasSuccessors;
            var nextQuestionParentId;
            var thisQuestion;

            while (stack.length) {
                top = stack.pop();	// ветка
                while (top) {			// внутри ветки
                    // вывести вопрос
                    makeQuestion(top, givenAnswers, level).forEach(function(row) {
                        result.push(row);
                    });
                    askedQuestions.push(top.questionId);
                    // получить подвопросы этого вопроса
                    topHasSuccessors = false;
                    self._getSuccessors(questionsList, top).forEach(function(successor) {
                        var question = self._getQuestionById(
                            successor.questionId,
                            questionsList
                        );
                        if (
                            (self._checkCondition(question, givenAnswers))
                            && (self._hasAnswers(top, givenAnswers))
                            && (self._questionIsNotAsked(question, askedQuestions, stack))
                        ) {
                            question.parentId = top.questionId;
                            stack.push(question);
                            topHasSuccessors = true;
                        }
                    });

                    // переход
                    if (topHasSuccessors) {
                        // если у этого вопроса есть подвопросы - переход вниз по первой ветке
                        top = stack.pop();
                        level += 1;
                    } else {
                        // у этого вопроса нет подвопросов - вверх до уровня следующего вопроса
                        // подниматься вверх (переходить к родительскому вопросу)
                        // пока родительский вопрос не совпадёт с родительским вопросом следующего вопроса в стеке

                        // идентификатор родительского вопроса для следующего вопроса из стека
                        nextQuestionParentId = ((function() {
                            if (stack.length) {
                                return stack[stack.length-1].parentId;
                            }
                            return undefined;
                        })());

                        thisQuestion = top;
                        while (thisQuestion.parentId !== nextQuestionParentId) {
                            level -= 1;
                            thisQuestion = self._getQuestionById(
                                thisQuestion.parentId,
                                questionsList
                            );
                        }
                        top = undefined;
                    }
                }
            }

            return result;
        },


        /**
         * Проверить, можно ли добавить этот вопрос к дереву вопросов с учётом того,
         * что вопрос может быть добавлен к дереву вопросов только один раз
         *
         * Для этого проверить наличие вопроса в списке добавленных вопросов,
         * а также в стеке (вопросы из стека точно будут добавлены к дереву вопросов).
         * @param question - вопрос
         * @param askedQuestions: questionId - список идентификаторов добавленных вопросов
         * @param stack: question[] - стек
         * @return bool
         */
        _questionIsNotAsked: function(question, askedQuestions, stack) {
            var self = this;
            var foundInStack;
            var foundInAskedQuestions;

            // искать вопрос в стеке
            foundInStack = (function() {
                try {
                    self._getQuestionById(question.questionId, stack);
                } catch (e) {
                    // вопрос не найден
                    return false;
                }
                return true;
            }());

            // искать в списке заданных вопросов
            foundInAskedQuestions = (function() {
                if (askedQuestions.indexOf(question.questionId) > -1) {
                    return true;
                }
                return false;
            }());

            if ((!foundInStack) && (!foundInAskedQuestions)) {
                return true;
            }

            return false;
        },


        /**
         * Сформировать список вопросов в порядке, соответствующем дереву вопросов
         * (с учётом зависимостей и позиций вопросов, но без учёта ответов в анкете)
         *
         * @param questionsList: question[]
         * @param givenAnswers: int[]
         * @param makeQuestion: function(question, givenAnswers)
         * @return row[]
         */
        makeFullQuestionTree: function(questionsList, givenAnswers, makeQuestion) {
            var self = this;

            var stack = self._getRootQuestions(questionsList);
            var askedQuestions = [];
            var result = [];

            var level = 0;
            var top;
            var topHasSuccessors;
            var nextQuestionParentId;
            var thisQuestion;

            while (stack.length) {
                top = stack.pop();	// ветка
                while (top) {			// внутри ветки
                    // вывести вопрос
                    makeQuestion(top, givenAnswers, level).forEach(function(row) {
                        result.push(row);
                    });
                    askedQuestions.push(top.questionId);
                    // получить подвопросы
                    topHasSuccessors = false;
                    self._getSuccessors(questionsList, top).forEach(function(successor) {
                        var question = self._getQuestionById(
                                successor.questionId,
                                questionsList);
                        if (self._questionIsNotAsked(question, askedQuestions, stack)) {
                            question.parentId = top.questionId;
                            stack.push(question);
                            topHasSuccessors = true;
                        }
                    });
                    // переход
                    if (topHasSuccessors) {
                        // если у этого вопроса есть подвопросы - переход вниз по первой ветке
                        top = stack.pop();
                        level += 1;
                    } else {
                        // у этого вопроса нет подвопросов - вверх до уровня следующего вопроса
                        // подниматься вверх (переходить к родительскому вопросу)
                        // пока родительский вопрос не совпадёт с родительским вопросом следующего вопроса в стеке

                        // идентификатор родительского вопроса для следующего вопроса из стека
                        nextQuestionParentId = ((function() {
                            if (stack.length) {
                                return stack[stack.length-1].parentId;
                            }
                            return undefined;
                        })());

                        thisQuestion = top;
                        while (thisQuestion.parentId !== nextQuestionParentId) {
                            level -= 1;
                            thisQuestion = self._getQuestionById(
                                thisQuestion.parentId,
                                questionsList
                            );
                        }
                        top = undefined;
                    }
                }
            }
            return result;
        }
    }
});
