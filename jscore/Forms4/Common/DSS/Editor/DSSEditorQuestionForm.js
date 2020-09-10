/**
 * Форма для работы с вопросами
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
 * @since        31.05.2018
 * @version      16.04.2019
 */
Ext6.define('common.DSS.Editor.DSSEditorQuestionForm', {


    /**
     * Отобразить форму редактирования опросника
     *
     */
    show: function(
        loadMask,
        form,
        diagModule,
        onReturn2ModulesForm,
        onESEditButton,
        onEditorsGroupButton,
        on2Registers,
        onFailure
    ) {
        var self = this;

        self.genQuestionnaireForm._getQuestions(
            loadMask,
            diagModule,
            function onQuestionsFetched(questions) {
                form.add(self.genQuestionnaireForm.makeGenQuestionForm(
                        loadMask,
                        form,
                        self.questionPanel,
                        self.questionnaire,
                        diagModule,
                        questions,
                        onReturn2ModulesForm,
                        onESEditButton,
                        onEditorsGroupButton,
                        on2Registers,
                        onFailure));
            },
            onFailure
        );
    },


    /**
     * Общая форма для работы с опросником
     *
     *  Собержит общее меню, а также одну позицию из:
     *      : форма для работы с опросником
     *      : форма восстановления удалённого вопроса
     *      : форма восстановденяи удалённого варианта ответа
     */
    genQuestionnaireForm: {

        /**
         * Получить вопросы
         *
         */
        _getQuestions: function(loadMask, diagModule, onSuccess, onFailure) {
            loadMask.show();
            Ext6.Ajax.request({
                url: '/?c=DSSEditorQuestion&m=getModuleQuestions',

                params: {
                    moduleId: diagModule.moduleId,
                    moduleName: diagModule.moduleName
                },

                success: function (response, opts) {
                    var data;

                    loadMask.hide();
                    data = JSON.parse(response.responseText);
                    if (data[0] === 'empty') {
                        data = [];
                    } else if (!Number.isInteger(data[0].questionId)) {
                        onFailure();
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
         * Отобразить форму общую форму для работы с опросником
         *
         */
        makeGenQuestionForm: function(
            loadMask,
            form,
            questionPanel,
            questionnaire,
            diagModule,
            questions,
            onReturn2ModulesForm,
            onESEditButton,
            onEditorsGroupButton,
            on2Registers,
            onFailure
        ) {
            var self = this;
            var genQuestionForm;

            genQuestionForm = new Ext6.form.FormPanel({
                id: 'genQuestionPanel', // для корректного удаления
                bodyPadding: 0,
                border: false,
                width: '100%',
                items: [
                    new Ext6.form.FormPanel({
                        border: false,
                        html: '<h1>Модуль <i>' + diagModule.moduleName + '</i></h1>',
                        width: '100%',
                        style: 'margin-bottom: 32px;'
                    }),

                    new Ext6.Button({
                        text: 'Вернуться к выбору модуля',
                        handler: function() {
                            self.remove(form);
                            onReturn2ModulesForm();
                        }
                    }),

                    new Ext6.Button({
                        text: 'Параметры СППР',
                        handler: function() {
                            self.remove(form);
                            onESEditButton();
                        }
                    }),

                    new Ext6.Button({
                        text: 'Команда экспертов',
                        handler: function() {
                            self.remove(form);
                            onEditorsGroupButton();
                        }
                    }),

                    new Ext6.Button({
                        text: 'Клинические регистры',
                        handler: function() {
                            self.remove(form);
                            on2Registers();
                        }
                    })
                ]
            });

            questionPanel.show(
                loadMask,
                form,
                genQuestionForm,
                questionnaire,
                diagModule,
                questions,
                function onQuestionnaireChanged() {
                    self.remove(form);
                    self._getQuestions(
                        loadMask,
                        diagModule,
                        function onQuestionsFetched(questions) {
                            form.add(self.makeGenQuestionForm(
                                    loadMask,
                                    form,
                                    questionPanel,
                                    questionnaire,
                                    diagModule,
                                    questions,
                                    onReturn2ModulesForm,
                                    onESEditButton,
                                    onEditorsGroupButton,
                                    onFailure));
                        },
                        onFailure
                    )
                },
                onFailure
            );

            return genQuestionForm;
        },


        /**
         * Удалить общую форму для работы с опросником
         *
         * @param {Ext.Component} form - родительский компонент с которого нужно
         *     удалить общую форму для работы с опросником
         */
        remove: function(form) {
            var genQuestionPanel = form.getComponent('genQuestionPanel');
            if (genQuestionPanel) {
                form.remove(genQuestionPanel);
            }
        }
    },


    /**
     * Панель для работы с опросником
     *
     */
    questionPanel: {


        /**
         * Сформировать панель с инструкцией по редактированию опросника
         *
         * @return {Ext6.form.FormPanel}
         */
        _makeManualPanel: function() {
            return new Ext6.form.FormPanel({
                title: 'Зависимости вопросов',
                style: 'margin-bottom: 16px; padding: 0px',
                bodyPadding: 16,
                html: '<div>'
                + '<p>Для каждого вопроса предусмотрена возможность указать комбинацию ответов,'
                    + 'при реализации которой в ходе опроса вопрос будет задан.</p>'
                + '<p>В настоящее время реализована возможность указать для вопроса'
                    + 'только множество ответов, при появлении любого из которых в ответах в ходе опроса'
                    + 'вопрос будет задан.</p>'
                + '<p>Пример:<br>Вопрос "Сколько времени беспокоит кашель?"'
                    +  'зависит от ответа "Да" на вопрос "Беспокоит ли Вас кашель?"</p>'
                + '<table style="font-size: 12px;">'
                    + '<tr><th>Вопрос</th><th>Зависимости вопроса</th></tr>'
                    + '<tr><td><b>Беспокоит ли Вас кашель?</b></td><td style="text-align:center">Корневой вопрос (1)</td></tr>'
                    + '<tr><td><span style="margin-left: 30px;"><i>1</i> Да</span></td><td></td></tr>'
                    + '<tr><td><span style="margin-left: 30px;"><i>2</i> Нет</span></td><td></td></tr>'
                    + '<tr><td><span style="margin-left: 30px;"><b>Сколько времени беспокоит кашель?</b></span></td><td style="text-align:center">1 (1)</td></tr>'
                    + '<tr><td><span style="margin-left: 60px;"><i>3</i> Несколько дней</span></td><td></td></tr>'
                    + '<tr><td><span style="margin-left: 60px;"><i>4</i> 1-2 месяца</span></td><td></td></tr>'
                + '</table>'
                + '<p>Варианты ответов, от которых зависит вопрос, указываются в виде идентификаторов.'
                    + 'Идентификатор варианта ответа указан перед текстом варианта ответа.'
                    + 'Например, вариант ответа "Да" на вопрос "Беспокоит ли Вас Кашель?" имеет идентификатор 1.</p>'
                + '<p>Корневой вопрос (вопрос без зависимостей) не зависит от ответов в ходе опроса.</p>'
                + '<p>Корневые вопросы и вопросы, зависящие от вариантов ответа на один вопрос, упорядочены.'
                    + 'Позиция вопроса указывается рядом с идентификатором варианта ответа в скобках.'
                    + 'Например, вопрос "Беспокоит ли Вас кашель?" имеет позицию 1 в списке корневых вопросов.'
                    + 'Вопрос "Сколько времени беспокоит кашель?" имеет позицию 1 в списке вопросов,'
                    + 'зависящих от вариантов ответа на вопрос "Беспокоит ли Вас кашель?"</p>'
                + '</div>'
            });
        },


        /**
         * Сформировать грид для отображения опросника
         *
         */
        _makeQuestionnaireGrid: function(loadMask, form, diagModule, questions, questionnaire, onQuestionnaireChanged, onFailure) {
            var self = this;

            var questionnaireGrid = new Ext6.grid.GridPanel({
                title: 'Опросник',
                autoHeight: true,
                bodyPadding: 0,
                store: new Ext6.data.Store({
                    fields: [
                        'text',
                        'dependenciesStr',
                        'action1',
                        'action2',
                        'action3',
                        'action4',
                        'action5',
                        'action6',
                        'action7',
                        'questionId',
                        'answerVariantId',
                        'level',
                        'statement'
                    ]
                }),
                hideHeaders: true,
                selModel: 'cellmodel',
                columns: [{
                    dataIndex: 'text',
                    header: 'Опросник',
                    name: 'text',
                    tdCls: 'nameTdCls',
                    flex: 5,
                    align: 'left',
                    renderer: function(val, metaData, record) {
                        // уровень дерева вопросов
                        var level = parseInt(record.get('level'));
                        // отступ каждого уровня 50px
                        var levelMargin = level*50;
                        // отступ вариантов ответа от вопроса ещё 25px
                        var answerVariantMargin = levelMargin+25;
                        if (record.get('answerVariantId') === -1) { // вопрос
                            return '<span style="margin-left:' + levelMargin + 'px;">'
                                + '<b>' + record.get('text') + '</b>'
                            + '</span>';
                        }
                        return '<span style="margin-left: ' + answerVariantMargin + 'px;">'
                            + record.get('text')
                        + '</span>';
                    }
                }, {
                    dataIndex: 'dependenciesStr',
                    name: 'dependenciesStr',
                    header: 'Варианты ответов,<br>от которых зависит вопрос',
                    tdCls: 'nameTdCls',
                    flex: 2,
                    align: 'center',
                    renderer: function(value, metaData, record, rowIndex, colIndex, view) {
                        if (value) {
                            if (value[0] == 'К') {
                                // от Корневой вопрос
                                metaData.tdAttr = 'data-qtip="'
                                    + value
                                    + '<p>В скобках указана позиция вопроса в списке корневых вопросов</p> '
                                    + '"';
                            } else {
                                metaData.tdAttr = 'data-qtip="'
                                    + value
                                    + '<p>Варианты ответов, от которых зависит вопрос.</p>'
                                    + '<p>В скобках указана позиция вопроса в списке всех вопросов,'
                                        + 'зависящих от вариантов ответа на тот же вопрос, что и указанный вариант ответа.</p>'
                                    + '"';
                            }
                        }
                        return value;
                    }
                }, {
                    dataIndex: 'action1',
                    name: 'action1',
                    tdCls: 'nameTdCls',
                    flex: 1,
                    renderer: self._addTip
                }, {
                    dataIndex: 'action2',
                    name: 'action2',
                    tdCls: 'nameTdCls',
                    flex: 1,
                    renderer: self._addTip
                }, {
                    dataIndex: 'action3',
                    name: 'action3',
                    tdCls: 'nameTdCls',
                    flex: 1,
                    renderer: self._addTip
                }, {
                    dataIndex: 'action4',
                    name: 'action4',
                    tdCls: 'nameTdCls',
                    flex: 1,
                    renderer: self._addTip
                }, {
                    dataIndex: 'action5',
                    name: 'action5',
                    tdCls: 'nameTdCls',
                    flex: 1,
                    renderer: self._addTip
                }, {
                    dataIndex: 'action6',
                    name: 'action6',
                    tdCls: 'nameTdCls',
                    flex: 1,
                    renderer: self._addTip
                }, {
                    dataIndex: 'action7',
                    name: 'action7',
                    tdCls: 'nameTdCls',
                    flex: 1,
                    renderer: self._addTip
                }, {
                    dataIndex: 'questionId',
                    name: 'questionId',
                    xtype: 'hidden'
                }, {
                    dataIndex: 'answerVariantId',
                    name: 'answerVariantId',
                    xtype: 'hidden'
                }, {
                    dataIndex: 'level',
                    name: 'level',
                    xtype: 'hidden'
                }, {
                    dataIndex: 'statement',
                    name: 'statement',
                    xtype: 'hidden'
                }],
                listeners: {
                    cellclick: function(view, cell, cellIndex, record, row, rowIndex, e) {
                        if (record.get('answerVariantId') == -1) {
                            self._questionPanelActionHandlres.onQuestionRowCellClick(
                                    loadMask,
                                    diagModule,
                                    questions,
                                    questionnaire,
                                    function(questionId) {
                                        self.onRestoreAnswerVariant(
                                            loadMask,
                                            form,
                                            diagModule,
                                            questionId,
                                            onQuestionnaireChanged,
                                            onFailure);
                                    },
                                    onQuestionnaireChanged,
                                    onFailure,
                                    cellIndex,
                                    record);
                        } else {
                            self._questionPanelActionHandlres.onAVRowCellClick(
                                    loadMask,
                                    diagModule,
                                    questions,
                                    questionnaire,
                                    onQuestionnaireChanged,
                                    onFailure,
                                    cellIndex,
                                    record);
                        }
                    }
                }
            });

            return questionnaireGrid;
        },


        /**
         * Рендер для столбца грида
         *
         */
        _addTip: function(value, metaData, record, rowIndex, colIndex, view) {
            metaData.tdAttr = (value)
                ? 'data-qtip="' + value + '"'
                : null;
            return value;
        },


        /**
         * Отобразить панель для работы с опросником
         *
         */
        show: function(
            loadMask,
            form, // для корректного отображения модальных окон
            genQuestionForm,
            questionnaire,
            diagModule,
            questions,
            onQuestionnaireChanged,
            onFailure
        ) {
            var self = this;

            var data; // список вопросов, преобразованный в список строк для грида
            var questionPanel;
            var questionnaireGrid = self._makeQuestionnaireGrid(
                    loadMask,
                    form,
                    diagModule,
                    questions,
                    questionnaire,
                    onQuestionnaireChanged,
                    onFailure);
            var emptyLabel = new Ext6.form.FormPanel({
                id: 'emptyLabel',
                border: false,
                html: 'Список вопросов пуст'
            });
            var missedQuestionsWarningLabel = new Ext6.form.FormPanel({
                id: 'missedQuestionsWarningLabel',
                border: false,
                html: '<div><font color="red">'
                    + '<h1>Внимание, ошибки в зависимостях вопросов!</h1>'
                    + 'Вероятно, опросник содержит циклические зависимости вопросов'
                + '</font></div>',
                style: 'margin-bottom: 32px;',
                hidden: true
            });

            questionPanel = new Ext6.form.FormPanel({
                border: false,
                bodyPadding: 0,
                width: '100%',
                items: [
                    self._makeManualPanel(),

                    missedQuestionsWarningLabel,

                    new Ext6.Button({
                        text: 'Добавить вопрос',
                        handler: function() {
                            Ext6.Msg.prompt(
                                'Создание нового вопроса',
                                'Введите текст вопроса: ',
                                function(btn, text) {
                                    if ((btn == 'ok') && (text)) {
                                        self._questionPanelActionHandlres.postQuestion(
                                                loadMask,
                                                diagModule,
                                                text,
                                                onQuestionnaireChanged,
                                                onFailure);
                                    }
                                }
                            );
                        }
                    }),

                    new Ext6.Button({
                        text: 'Восстановить удалённый вопрос',
                        handler: function() {
                            self.onRestoreQuestion(
                                loadMask,
                                form,
                                diagModule,
                                onQuestionnaireChanged,
                                onFailure);
                        }
                    }),

                    questionnaireGrid,

                    emptyLabel
                ]
            });
            genQuestionForm.add(questionPanel);

            if (questions.length === 0) {
                questionnaireGrid.setHidden(true);
                emptyLabel.setHidden(false);
                return;
            }
            questionnaireGrid.setHidden(false);
            emptyLabel.setHidden(true);

            data = questionnaire.makeQuestionTree(questions, questionnaire.makeQuestion);

            if (data.missedQuestions.length > 0) {
                missedQuestionsWarningLabel.setHidden(false);
                data.missedQuestions.forEach(function(row) {
                    data.questions.push(row);
                });
            }

            questionnaireGrid.getStore().loadData(data.questions);
        },


        /**
         * Обработчики нажатий на ячейки опросника-таблицы
         */
        _questionPanelActionHandlres: {


            /**
             * Создать вопрос
             *
             */
            postQuestion: function(loadMask, diagModule, questionText, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorQuestion&m=postModuleQuestion',

                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        questionText: questionText
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
             * Создать вариант ответа
             *
             */
            _postAnswerVariant: function(loadMask, diagModule, questionId, answerVariantText, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorAnswerVariant&m=postAnswerVariant',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        questionId: questionId,
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


            /**
             * Изменить вопрос
             *
             */
            _patchQuestion: function(loadMask, diagModule, question, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorQuestion&m=patchModuleQuestion',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        questionId: question.questionId,
                        questionText: question.questionText
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
             * Удалить вопрос
             *
             */
            _deleteQuestion: function(loadMask, diagModule, questionId, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorQuestion&m=deleteModuleQuestion',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
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


            /**
             * Изменить вариант ответа (формулировку)
             *
             */
            _patchAnswerVariant: function(loadMask, diagModule, questionId, answerVariant, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorAnswerVariant&m=patchAnswerVariant',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        questionId: questionId,
                        answerVariantId: answerVariant.answerVariantId,
                        answerVariantText: answerVariant.answerVariantText,
                        answerVariantStatement: answerVariant.answerVariantStatement
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
             * Удалить вариант ответа
             *
             */
            _deleteAnswerVariant: function(loadMask, diagModule, questionId, answerVariant, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorAnswerVariant&m=deleteAnswerVariant',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        questionId: questionId,
                        answerVariantId: answerVariant.answerVariantId
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
             * Поднять вариант ответа на одну позицию вверх в списке варианто вотеа на вопрос
             *
             */
            _postAnswerVariantPositionUp: function(loadMask, diagModule, questionId, answerVariant, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorAnswerVariant&m=postAnswerVariantPositionUp',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        questionId: questionId,
                        answerVariantId: answerVariant.answerVariantId
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
             * Добавить зависимость вопроса
             *
             */
            _putQuestionDependency: function(loadMask, diagModule, questionId, parentQuestionId, answerVariantId, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorQuestion&m=putQuestionDependency',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        questionId: questionId,
                        answerVariantId: answerVariantId,
                        parentQuestionId: parentQuestionId
                    },
                    success: function (response, opts) {
                        var data;

                        loadMask.hide();
                        data = JSON.parse(response.responseText);

                        if (data[0] !== 'added') {
                            onFailure(data[0]);
                            return;
                        }
                        onSuccess();
                    },
                    failure: function (response, opts) {
                       loadMask.hide();
                       onFailure('Network error');
                    }
                });
            },


            /**
             * Удалить зависимость вопроса
             *
             */
            _deleteQuestionDependency: function(loadMask, diagModule, questionId, parentQuestionId, answerVariantId, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorQuestion&m=deleteQuestionDependency',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        questionId: questionId,
                        answerVariantId: answerVariantId,
                        parentQuestionId: parentQuestionId
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
             * Изменить (уменьшить) позицию вопроса в списке
             *
             * @param questionId id вопроса, позицию которого нужно изменить
             * @param parentQuestionId id вопроса, задающего список вопросов,
             *       в котором нужно изменить позицию вопроса
             */
            _postQuestionPositionUp: function(loadMask, diagModule, questionId, parentQuestionId, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorQuestion&m=postQuestionPositionUp',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        questionId: questionId,
                        parentQuestionId: parentQuestionId
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


            putQuestion2restore: function(loadMask, diagModule, questionId, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorQuestion&m=putQuestion2restore',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
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


            putAnswerVariant2restore: function(loadMask, diagModule, questionId, answerVariant, onSuccess, onFailure) {
                loadMask.show();
                Ext6.Ajax.request({
                    url: '/?c=DSSEditorAnswerVariant&m=putAnswerVariant2restore',
                    params: {
                        moduleId: diagModule.moduleId,
                        moduleName: diagModule.moduleName,
                        questionId: questionId,
                        answerVariantId: answerVariant.answerVariantId,
                        answerVariantText: answerVariant.answerVariantText
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
             * Обработчик нажатий на ячейки строки таблицы, в которой располагается вопрос
             *
             */
            onQuestionRowCellClick: function(loadMask, diagModule, questions, questionnaire, onRestoreAnswerVariant, onQuestionnaireChanged, onFailure, cellIndex, record) {
                var self = this;
                var clickedColumnName = record.getFields()[cellIndex].getName();
                var questionId = parseInt(record.get('questionId'));

                if (clickedColumnName === 'action1') {
                    Ext6.Msg.prompt(
                        'Добавить зависимость вопроса',
                        'Введите идентификатор варианта ответа,'
                        + 'зависимость от которого нужно добавить'
                        + '(идентификатор указан перед текстом варианта ответа):',
                        function(btn, text) {
                            var answerVariantId;

                            if ((btn === 'ok') && (text)) {
                                answerVariantId = parseInt(text);
                                if (isNaN(answerVariantId)) {
                                    return;
                                }
                                self._putQuestionDependency(
                                        loadMask,
                                        diagModule,
                                        questionId,
                                        questionnaire.getQuestionIdByAnswerVariantId(questions, answerVariantId),
                                        answerVariantId,
                                        onQuestionnaireChanged,
                                        onFailure);
                            }
                        }
                    );
                } else if (clickedColumnName === 'action2') {
                    Ext6.Msg.prompt(
                        'Удалить зависимость вопроса',
                        'Введите идентификатор варианта ответа: ',
                        function(btn, text) {
                            var answerVariantId;

                            if ((btn == 'ok') && (text)) {
                                answerVariantId = parseInt(text);
                                if (isNaN(answerVariantId)) {
                                    return;
                                }
                                self._deleteQuestionDependency(
                                        loadMask,
                                        diagModule,
                                        questionId,
                                        questionnaire.getQuestionIdByAnswerVariantId(questions, answerVariantId),
                                        answerVariantId,
                                        onQuestionnaireChanged,
                                        onFailure);
                            }
                        }
                    );
                } else if (clickedColumnName === 'action3') {
                    // поднять вопрос на одну позицию
                    self._onMoveUpQuestion(
                            loadMask,
                            diagModule,
                            questionId,
                            questions,
                            questionnaire,
                            onQuestionnaireChanged,
                            onFailure);
                } else if (clickedColumnName === 'action4') {
                    Ext6.Msg.prompt(
                        'Добавить вариант ответа',
                        'Введите текст варианта ответа: ',
                        function(btn, text) {
                            if ((btn == 'ok') && (text)) {
                                self._postAnswerVariant(
                                    loadMask,
                                    diagModule,
                                    questionId,
                                    text,
                                    onQuestionnaireChanged,
                                    onFailure);
                            }
                        }
                    );
                } else if (clickedColumnName == 'action5') { // восстановить удалённый вариант ответа
                    onRestoreAnswerVariant(questionId);
                } else if (clickedColumnName == 'action6') {
                    Ext6.Msg.prompt(
                        'Изменение формулировки вопроса',
                        'Введите новую формулировку вопроса: ',
                        function(btn, text) {
                            var question;

                            if ((btn == 'ok') && (text)) {
                                question = {
                                    questionId: questionId,
                                    questionText: text
                                };
                                self._patchQuestion(
                                        loadMask,
                                        diagModule,
                                        question,
                                        onQuestionnaireChanged,
                                        onFailure);
                            }
                        },
                        null,
                        false,
                        record.get('text')
                    );
                } else if (clickedColumnName == 'action7') {
                    Ext6.Msg.confirm(
                        'Удаление вопроса',
                        'Удалить вопрос "' + record.get('text') + '"?',
                        function(btn) {
                            if (btn == 'yes') {
                                self._deleteQuestion(
                                        loadMask,
                                        diagModule,
                                        questionId,
                                        onQuestionnaireChanged,
                                        onFailure);
                            }
                        }
                    );
                }
            },


            /**
             * Обработчик нажатий на ячейки строки варианта ответа
             *
             */
            onAVRowCellClick: function(loadMask, diagModule, questions, questionnaire, onQuestionnaireChanged, onFailure, cellIndex, record) {
                var self = this;
                var questionId = parseInt(record.get('questionId'));
                var answerVariant = questionnaire.getAnswerVariantById(
                        questions,
                        parseInt(record.get('answerVariantId')));
                var clickedColumnName = record.getFields()[cellIndex].getName();

                if (clickedColumnName === 'action4') {
                    self._postAnswerVariantPositionUp(
                            loadMask,
                            diagModule,
                            questionId,
                            answerVariant,
                            onQuestionnaireChanged,
                            onFailure);
                } else if (clickedColumnName === 'action5') {
                    Ext6.Msg.prompt(
                        'Изменение формулировки варианта ответа',
                        'Введите новую формулировку варианта ответа: ',
                        function(btn, text) {
                            if ((btn === 'ok') && (text)) {
                                answerVariant.answerVariantText = text;
                                self._patchAnswerVariant(
                                        loadMask,
                                        diagModule,
                                        questionId,
                                        answerVariant,
                                        onQuestionnaireChanged,
                                        onFailure);
                            }
                        },
                        null,
                        false,
                        record.get('text')
                        .substr( // обрезать идентификатор варианта ответа. Пример: '2&nbsp;Вариант ответа'
                            // количество цифр в идентификаторе варианта ответа плюс шесть
                            Math.floor(
                                Math.log(record.get('answerVariantId'))/Math.log(10)+1
                            ) + 6, // &nbsp;
                            record.get('text').length
                        )
                    );
                } else if (clickedColumnName === 'action6') {
                    Ext6.Msg.prompt(
                        'Изменение формулировки варианта ответа для генерации текстового представлния протокола опроса',
                        'Введите новую формулировку варианта ответа: ',
                        function(btn, text) {
                            if ((btn === 'ok') && (text)) {
                                if (text.trim() === record.get('statement')) {
                                    return;
                                }
                                answerVariant.answerVariantStatement = text;
                                self._patchAnswerVariant(
                                        loadMask,
                                        diagModule,
                                        questionId,
                                        answerVariant,
                                        onQuestionnaireChanged,
                                        onFailure);
                            }
                        },
                        null,
                        false,
                        record.get('statement')
                    );
                } else if (clickedColumnName === 'action7') {
                    Ext6.Msg.confirm(
                        'Удаление варианта ответа',
                        'Удалить вариант ответа "' + record.get('text') + '"?',
                        function(btn) {
                            if (btn === 'yes') {
                                self._deleteAnswerVariant(
                                        loadMask,
                                        diagModule,
                                        questionId,
                                        answerVariant,
                                        onQuestionnaireChanged,
                                        onFailure);
                            }
                        }
                    );
                }
            },


            /**
             * Обработчик нажатия на ячейку грида --Поднять вопрос--
             *
             * Вопрос имеет разные позиции в группах вопросов, зависящих от разных вопросов
             *     (если вопрос зависит от нескольких вопросов)
             */
            _onMoveUpQuestion: function(loadMask, diagModule, questionId, questions, questionnaire, onQuestionnaireChanged, onFailure) {
                var self = this;
                var parentQuestionId = undefined;

                // попытка определить родительский вопрос
                // по возможности без участия пользователя
                // (это возможно, если вопрос имеет ровно одну зависимость)
                try {
                    parentQuestionId = questionnaire.getParentQuestionId(
                            questions,
                            questionId);
                } catch (e) {
                    if (e === 'multiple') {
                        // не удалось определить родительский вопрос,
                        // поскольку вопрос зависит больше чет от одного вопроса.
                        // родительский вопрос необходимо уточнить у пользователя
                        Ext6.Msg.prompt(
                            'Поднять вопрос на одну позицию',
                            'Введите идентификатор варианта ответа,'
                            + 'задающий список вопросов,'
                            + 'в котором нужно изменить позицию вопроса: ',
                            function(btn, text) {
                                if ((btn == 'ok') && (text)) {
                                    try {
                                        parentQuestionId = questionnaire.getQuestionIdByAnswerVariantId(
                                                questions,
                                                parseInt(text));
                                    } catch(e) {
                                        return; // ошибка, которой быть не должно
                                    }
                                    self._postQuestionPositionUp(
                                            loadMask,
                                            diagModule,
                                            questionId,
                                            parentQuestionId,
                                            onQuestionnaireChanged,
                                            onFailure);
                                }
                            }
                        );
                    } else if (e == 'root') {
                        // не удалось определить родительский вопрос,
                        // поскольку вопрос корневой,
                        // не зависит ни от одного вопроса
                        // в этом случае необходимо
                        // передать 'root' в поле parentQuestionId
                        parentQuestionId = 'root';
                    } else {
                        onFailure('Непредвиденная ошибка ' + e);
                        return; // ошибка, которой быть не должно
                    }
                }

                // удалось определить родительский вопрос без участия пользователя
                self._postQuestionPositionUp(
                        loadMask,
                        diagModule,
                        questionId,
                        parentQuestionId,
                        onQuestionnaireChanged,
                        onFailure);

            }
        },


        /**
         * Обработать нажание на кнопку --Восстановить вопрос--
         *
         */
        onRestoreQuestion: function(loadMask, panel, diagModule, onReload, onFailure) {
            var self = this;
            var modalWindow;

            modalWindow = new Ext6.window.Window({
                title: 'Восстановить вопрос',
                height: '75%',
                width:  '50%',
                modal: true,
                scrollable: true,
                draggable: true,
                layout: 'fit',
                items: [
                    new Ext6.grid.GridPanel({
                        store: new Ext6.data.Store({
                            autoLoad: true,
                            fields: [
                                'questionId',
                                'questionText'
                            ],
                            pageSize: 2,
                            proxy: {
                                type: 'ajax',
                                actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                reader: new Ext6.data.JsonReader({
                                    rootProperty: 'questions',
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
                                                questions: data[0],
                                                total: data[1]
                                            };
                                        }
                                    }
                                }),
                                extraParams: {
                                    moduleId: diagModule.moduleId,
                                    moduleName: diagModule.moduleName
                                },
                                url: '/?c=DSSEditorQuestion&m=getQuestions2restore',
                            }
                        }),
                        autoHeight: true,
                        width: '100%',
                        title: 'Удалённые вопросы для восстановления',
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
                            dataIndex: 'questionId',
                            name: 'resultId',
                            header: 'Идентификатор вопроса',
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
                            dataIndex: 'questionText',
                            name: 'questionText',
                            header: 'Текст вопроса',
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
                                var questionId = parseInt(record.get('questionId'));
                                self._questionPanelActionHandlres.putQuestion2restore(
                                        loadMask,
                                        diagModule,
                                        questionId,
                                        onReload,
                                        onFailure);
                                panel.remove(modalWindow);
                            }
                        }
                    })
                ]
            });

            panel.add(modalWindow);
            modalWindow.show();
        },


        /**
         * Обработать нажание на кнопку --Восстановить вариант ответа--
         *
         */
        onRestoreAnswerVariant: function(loadMask, resultPanel, diagModule, questionId, onReload, onFailure) {
            var self = this;
            var modalWindow;

            modalWindow = new Ext6.window.Window({
                title: 'Восстановить вариант ответа',
                height: '75%',
                width: '50%',
                modal: true,
                scrollable: true,
                draggable: true,
                layout: 'fit',
                items: [
                    new Ext6.grid.GridPanel({
                        store: new Ext6.data.Store({
                            autoLoad: true,
                            fields: [
                                'answreVariantId',
                                'answerVariantText'
                            ],
                            //pageSize: 2,
                            proxy: {
                                type: 'ajax',
                                actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
                                reader: new Ext6.data.JsonReader({
                                    rootProperty: 'answerVariants',
                                    totalProperty: 'total',
                                    transform: {
                                        fn: function(data) {
                                            var answerVariants;
                                            if (data[0] === 'empty') {
                                                data = []; // трюк для передачи пустого списка
                                            }
                                            if ((typeof data[0]) === 'string') {
                                                onFailure(data[0]); // вернулась ошибка
                                                return;
                                            }

                                            return {
                                                answerVariants: data,
                                                total: data.length/*data[1]*/ // апи сервер возвращает без пагинации
                                            };
                                        }
                                    }
                                }),
                                extraParams: {
                                    moduleId: diagModule.moduleId,
                                    moduleName: diagModule.moduleName,
                                    questionId: questionId
                                },
                                url: '/?c=DSSEditorAnswerVariant&m=getAnswerVariants2restore',
                            }
                        }),
                        autoHeight: true,
                        width: '100%',
                        title: 'Удалённые варианты ответа для восстановления',
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
                            dataIndex: 'answerVariantId',
                            name: 'answerVariantId',
                            header: 'Идентификатор вариант ответа',
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
                            dataIndex: 'answerVariantText',
                            name: 'answerVariantText',
                            header: 'Текст варианта ответа',
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
                                var answerVariant = {
                                    answerVariantId: parseInt(record.get('answerVariantId')),
                                    answerVariantText: record.get('answerVariantText')
                                };
                                self._questionPanelActionHandlres.putAnswerVariant2restore(
                                        loadMask,
                                        diagModule,
                                        questionId,
                                        answerVariant,
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
        }
    },


    /**
     * Опросник
     */
    questionnaire: {


        /**
         * Получить вопрос, от которого зависит данный вопрос
         *
         * (если он зависит от одного вопроса)
         * Получить исключение 'root', если вопрос корневой (не зависит ни от одного вопроса)
         * Получить исключение 'multiple', если вопрос зависит от нескольких вопросов
         */
        getParentQuestionId: function(questionsList, questionId) {
            var self = this;
            var question = self._getQuestionById(questionId, questionsList);
            var answerVariantId;

            if  (question.questionDependencies.length > 1) {
                throw 'multiple';
            }

            // ровно одна зависимость
            answerVariantId = question.questionDependencies[0].answerVariantId;
            if (answerVariantId === -1) {
                // иметь зависимость 'null' может только корневой вопрос
                throw 'root';
            }
            return self.getQuestionIdByAnswerVariantId(questionsList, answerVariantId);
        },


        /**
         * Получить идентификатор вопроса по идентификатору варианта ответа
         *
         * @param {Question[]} - опросник (список вопросов)
         * @param {int} answerVariantId - идентификатор варианта ответа
         * @return {int}
         * @throws 'Вопрос не найден'
         */
        getQuestionIdByAnswerVariantId: function(questions, answerVariantId) {
            var foundQuestions = questions.filter(function(question) {
                return question.questionAnswerVariants.reduce(function(acc, answerVariant) {
                    if (answerVariant.answerVariantId === answerVariantId) {
                        return true;
                    }
                    return acc;
                }, false);
            });

            if (foundQuestions.length === 1) {
                return foundQuestions[0].questionId;
            }

            throw 'Вопрос не найден';
        },


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
                    if (question.questionDependencies.length > 1) {
                        return false;
                    }
                    // идентификатор варианта ответа единственной зависимости вопроса
                    answerVariantId = question.questionDependencies[0].answerVariantId;
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
                        var aPosition = a.questionDependencies[0].questionPosition;
                        var bPosition = b.questionDependencies[0].questionPosition;
                        return aPosition - bPosition;
                    }
                );
        },


        /**
         * Найти вариант ответа по идентификатору
         *
         */
        getAnswerVariantById: function(questions, answerVariantId) {
            var found = false;
            var foundAnswerVariant;

            questions.forEach(function(question) {
                question.questionAnswerVariants.forEach(function(answerVariant) {
                    if (answerVariant.answerVariantId === answerVariantId) {
                        found = true;
                        foundAnswerVariant = answerVariant;
                    }
                });
            });

            if (found) {
                return foundAnswerVariant;
            }

            throw 'answer variant is not found';
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
         * Сформировать строки таблицы опросника по заданному вопросу -
         * строка вопроса и ниже строки вариантов ответа
         *
         * @param question - вопрос (объект)
         * @param int level - уровень вопроса в иерархии
         *  (если вопрос А зависит от вопроса Б, то уровень вопроса Б на 1 больше, чем вопроса А)
         * @return row[] - список строк данных для таблицы
         */
        makeQuestion: function(question, level) {
            var rows = [];
            var dependenciesStr;

            dependenciesStr = question.questionDependencies.reduce(function(acc, dependency) {
                var answerVariantId = (dependency.answerVariantId === -1)
                    ? 'Корневой вопрос'
                    : dependency.answerVariantId;

                return acc + answerVariantId + '&nbsp;(' + dependency.questionPosition + '), ';
            }, '');

            if (dependenciesStr.length) {
                dependenciesStr = dependenciesStr.substr(0, dependenciesStr.length-2);
            }

            rows.push({
                questionId: question.questionId,
                answerVariantId: -1,
                text: question.questionText,
                level: level,
                dependenciesStr: dependenciesStr,
                action1: "Добавить зависимость",
                action2: "Удалить зависмость",
                action3: "Вверх",
                action4: "Добавить вариант ответа",
                action5: "Восстановить удалённый вариант ответа",
                action6: "Изменить текст вопроса",
                action7: "Удалить вопрос"
            });

            question.questionAnswerVariants.forEach(function(answerVariant) {
                rows.push({
                    questionId: question.questionId,
                    answerVariantId: answerVariant.answerVariantId,
                    text: answerVariant.answerVariantId + '&nbsp;' + answerVariant.answerVariantText,
                    statement: answerVariant.answerVariantStatement,
                    level: level,
                    action4: "Вверх",
                    action5: "Изменить текст",
                    action6: "Изменить формулировку для генерации текстового представления протокола",
                    action7: "Удалить"
                });
            });

            return rows;
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
            var successors = [];

            answerVariantIds = question.questionAnswerVariants.map(function(answerVariant) {
                return answerVariant.answerVariantId;
            });

            questionsList.forEach(function(currentQuestion) {
                currentQuestion.questionDependencies.forEach(function(variable) {
                    if (answerVariantIds.indexOf(variable.answerVariantId) > -1) {
                        successors.push({
                            questionId: currentQuestion.questionId,
                            questionPosition: variable.questionPosition
                        });
                    }
                });
            });

            return successors.sort(function(a, b) {
                return a.questionPosition - b.questionPosition;
            });
        },


        /**
         * Сформировать данные для таблицы опросника
         *
         * @param question[] - граф опросника
         *      Вершины графа - вопросы - связаны при помощи поля SelfQuestion_variables,
         *      в котором содержится список вариантов ответов, от которых зависит вопрос
         *      Дополнительно у вопроса есть поле SelfQuestion_successors,
         *      в котором содержится список вопросов, зависящих от вариантов ответов вопроса
         *  Каждый вопрос выводится только один раз (это позволяет не делать проверки на наличие циклов)
         * @return список строк таблицы для загрузки в store и отображения в grid
         */
        makeQuestionTree: function(questionsList, makeQuestion) {
            var self = this;

            var stack = self._getRootQuestions(questionsList);
            var askedQuestions = [];
            var result = [];
            var missedQuestions = [];

            var level = 0;
            var top;
            var topHasSuccessors;
            var nextQuestionParentId;
            var thisQuestion;

            while (stack.length) {
                top = stack.pop();	// ветка
                while (top) {			// внутри ветки
                    // вывести вопрос
                    makeQuestion(top, level).forEach(function(row) {
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

            // если в зависимостях вопросов образовались циклические связи,
            // то часть вопросов не будет отображаться,
            // что не позволит эксперту исправить ошибку в зависимостях.
            // Поэтому, в случае обнаружения ситуации,
            // в которой в дереве вопросов были отображены не все вопросы,
            // вывести предупреждение эксперту о (предположении) наличия циклических связей
            // и оставшиеся вопросы (списком, без учёта зависимостей)
            missedQuestions = (function() {
                var missedQuestions = [];
                if (questionsList.length > askedQuestions.length) {
                    questionsList.forEach(function(question) {
                        if (askedQuestions.indexOf(question.questionId) < 0) {
                            questionnaire.makeQuestion(question, 0).forEach(function(row) {
                                missedQuestions.push(row);
                            });
            			}
                    });
                }
                return missedQuestions;
            }());

            return {questions: result, missedQuestions: missedQuestions};
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
        }
    }
});
