/**
 * swDSSEditorWindow - окно для редактирования опросников
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
 * @since        27.08.2018
 * @version      11.06.2019 - изменение форм восстановления удалённых вопросов и вариантов ответов
 */
Ext6.define('common.DSS.Editor.swDSSEditorWindow', {
    addCodeRefresh: Ext.emptyFn,
    addHelpButton: Ext.emptyFn,
    closeToolText: 'Закрыть',

    alias: 'widget.swDSSEditorWindow',
    title: 'Сбор структурированной медицинской информации и поддержка принятия решений. Редактор опросников',
    extend: 'base.BaseForm',
    maximized: false,//true,
    width: '75%',//width: 800,
    height: '100%',//height: 600,
    modal: true,

    findWindow: false,
    closable: true,
    cls: 'arm-window-new',
    renderTo: main_center_panel.body.dom,
    layout: 'border',

    plain: true,
    resizable: true,

    /**
     * Стандартный индикатор ожидания для окна прилоения редактора опросников
     *
     */
    loadMask: {
        init: function(form) {
            if (!this.mask) {
                this.mask = new Ext6.LoadMask({
                    msg: 'Подождите...',
                    target: form
                });
            }
        },
        show: function() {
            if (this.mask) {
                this.mask.show();
            }
        },
        hide: function() {
            if (this.mask) {
                this.mask.hide();
            }
        }
    },


    /**
     * Стандартная обработка ошибок
     *
     */
    showErrorMsg: function(msg) {
        Ext6.Msg.alert('Ошибка', (msg || 'Ошибка приложения'));
    },


    /**
     * Удалить все компоненты
     *
     * @param {Ext.Component} form - основное окно
     */
    _clean: function(form) {
        var win = this;

        win.moduleForm.selectModuleForm.remove(form);
        win.questionForm.genQuestionnaireForm.remove(form);
        win.moduleAccessRightForm.remove(form);
        win.moduleCreateRightForm.remove(form);

        win.expertSystemForm.remove(form);
        win.resultForm.remove(form);
        win.recommendationForm.remove(form);
        win.recommendation2ResultForm.remove(form);

        win.registersForm.remove(form);
        win.registerAccessRightForm.remove(form);
        win.registerFeaturesForm.remove(form);
        win.registerVariablesForm.remove(form);
    },


    /**
     * Отобразить окно редактора опросников
     *
     */
    show: function() {
        this.callParent(arguments);
        var current_window = this;
        var win = this;

        var form = this.MainPanel;
        win.form = form;

        Ext6.require([
            'common.DSS.Editor.DSSEditorModuleForm',
            'common.DSS.Editor.DSSEditorModuleCreateRightForm',
            'common.DSS.Editor.DSSEditorQuestionForm',
            'common.DSS.Editor.DSSEditorModuleAccessRightForm',

            'common.DSS.Editor.DSSEditorExpertSystemForm',
            'common.DSS.Editor.DSSEditorResultForm',
            'common.DSS.Editor.DSSEditorRecommendationForm',
            'common.DSS.Editor.DSSEditorRecommendation2ResultForm',

            'common.DSS.Editor.Register.DSSEditorRegisterForm',
            'common.DSS.Editor.Register.DSSEditorRegisterAccessRightForm',
            'common.DSS.Editor.Register.DSSEditorRegisterFeatureForm',
            'common.DSS.Editor.Register.DSSEditorRegisterVariableForm'
        ]);

        Ext6.onReady(function() {
            win.moduleForm = new common.DSS.Editor.DSSEditorModuleForm();
            win.moduleCreateRightForm = new common.DSS.Editor.DSSEditorModuleCreateRightForm();
            win.questionForm = new common.DSS.Editor.DSSEditorQuestionForm();
            win.moduleAccessRightForm = new common.DSS.Editor.DSSEditorModuleAccessRightForm();

            win.expertSystemForm = new common.DSS.Editor.DSSEditorExpertSystemForm();
            win.resultForm = new common.DSS.Editor.DSSEditorResultForm();
            win.recommendationForm = new common.DSS.Editor.DSSEditorRecommendationForm();
            win.recommendation2ResultForm = new common.DSS.Editor.DSSEditorRecommendation2ResultForm();

            win.registersForm = new common.DSS.Editor.Register.DSSEditorRegisterForm();
            win.registerAccessRightForm = new common.DSS.Editor.Register.DSSEditorRegisterAccessRightForm();
            win.registerFeaturesForm = new common.DSS.Editor.Register.DSSEditorRegisterFeatureForm();
            win.registerVariablesForm = new common.DSS.Editor.Register.DSSEditorRegisterVariableForm();

            win._clean(form);

            // начало работы - форма диагностических модулей
            win.moduleForm.show(
                win.loadMask,
                form,
                function on2ModuleCreateRightForm() {
                    win.showModuleCreateRightForm(form, win.loadMask);
                },
                function onModuleSelected(diagModule) {
                    win.showQuestionForm(form, win.loadMask, diagModule);
                },
                function onFailure(msg) {
                    //win.show();
                    win.showErrorMsg(msg);
                }
            );
        });
    },


    showModuleCreateRightForm: function(form, loadMask) {
        var win = this;
        win.moduleCreateRightForm.show(
            loadMask,
            form,
            function callback() {
                win.show();
            },
            win.showErrorMsg
        );
    },


    showQuestionForm: function(form, loadMask, diagModule) {
        var win = this;
        win.questionForm.show(
            loadMask,
            form,
            diagModule,
            function onReturn2ModulesForm() {
                win.unlockModule(loadMask, diagModule);
            },
            function onESEdit() {
                win.showExpertSystemForm(
                        form,
                        loadMask,
                        diagModule);
            },
            function onEditorsGroupButton() {
                win.showModuleAccessRightForm(form, loadMask, diagModule);
            },
            function () {
                win.showRegistersForm(form, loadMask, diagModule);
            },
            win.showErrorMsg
        );
    },


    unlockModule: function(loadMask, diagModule) {
        var win = this;
        win.moduleForm.unlockModule(
            loadMask,
            diagModule,
            function () {
                win.show();
            },
            function (msg) {
                //win.show();
                win.showErrorMsg(msg);
            }
        );
    },


    showModuleAccessRightForm: function(form, loadMask, diagModule) {
        var win = this;
        win.moduleAccessRightForm.show(
            loadMask,
            form,
            diagModule,
            function () {
                win.showQuestionForm(form, loadMask, diagModule);
            },
            function (msg) {
                //win.show();
                win.showErrorMsg(msg);
            }
        );
    },


    showExpertSystemForm: function(form, loadMask, diagModule) {
        var win = this;
        win.expertSystemForm.show(
            loadMask,
            form,
            diagModule,
            win.questionForm.questionnaire,
            function onReturn2Questionnaire() {
                win.showQuestionForm(form, loadMask, diagModule);
            },
            function () {
                win.showResultForm(form, loadMask, diagModule);
            },
            function () {
                win.showRecommendationForm(form, loadMask, diagModule);
            },
            function (msg) {
                //win.show();
                win.showErrorMsg(msg);
            }
        );
    },


    showRecommendationForm: function(form, loadMask, diagModule) {
        var win = this;
        win.recommendationForm.show(
            loadMask,
            form,
            diagModule,
            function onReturn() {
                win.showExpertSystemForm(form, loadMask, diagModule);
            },
            function onFailure(msg) {
                //win.show();
                win.showErrorMsg(msg);
            }
        );
    },


    showRegistersForm: function(form, loadMask, diagModule) {
        var win = this;
        win.registersForm.show(
            loadMask,
            form,
            diagModule,
            function onReturn() {
                win.registersForm.remove(form);
                win.showQuestionForm(form, loadMask, diagModule);
            },
            function onRegisterSelected(register) {
                win.registersForm.remove(form);
                win.showRegisterFeaturesForm(form, loadMask, diagModule, register);
            },
            function onFailure(msg) {
                //win.show();
                win.showErrorMsg(msg);
            }
        );
    },


    showRegisterFeaturesForm: function(form, loadMask, diagModule, register) {
        var win = this;
        win.registerFeaturesForm.show(
            loadMask,
            form,
            diagModule,
            register,
            function onReturn() {
                win.registerFeaturesForm.remove(form);
                win.showRegistersForm(form, loadMask, diagModule);
            },
            function onRegisterFeatureSelected(registerFeature) {
                win.registerFeaturesForm.remove(form);
                win.showRegisterVariablesForm(form, loadMask, diagModule, register, registerFeature);
            },
            function onRegisterDataAccessRightEditButton() {
                win.registerFeaturesForm.remove(form);
                win.showRegisterAccessRightForm(form, loadMask, diagModule, register);
            },
            function onFailure(msg) {
                //win.show();
                win.showErrorMsg(msg);
            }
        );
    },


    showRegisterVariablesForm: function(form, loadMask, diagModule, register, feature) {
        var win = this;
        win.registerVariablesForm.show(
            loadMask,
            form,
            diagModule,
            register,
            feature,
            function onReturn() {
                win.registerVariablesForm.remove(form);
                win.showRegisterFeaturesForm(form, loadMask, diagModule, register);
            },
            function onFailure(msg) {
                //win.show();
                win.showErrorMsg(msg);
            }
        );
    },


    showRegisterAccessRightForm: function(form, loadMask, diagModule, register) {
        var win = this;
        win.registerAccessRightForm.show(
            loadMask,
            form,
            diagModule,
            register,
            function onReturn() {
                win.registerAccessRightForm.remove(form);
                win.showRegistersForm(form, loadMask, diagModule);
            },
            function onFailure(msg) {
                //win.show();
                win.showErrorMsg(msg);
            }
        );
    },


    showResultForm: function(form, loadMask, diagModule) {
        var win = this;
        win.resultForm.show(
            loadMask,
            form,
            diagModule,
            function onReturn() {
                win.showExpertSystemForm(form, loadMask, diagModule);
            },
            function on2Recommendations(result) {
                win.resultForm.remove(form);
                win.showRecommendation2ResultForm(
                        form,
                        loadMask,
                        diagModule,
                        result);
            },
            function onFailure(msg) {
                //win.show();
                win.showErrorMsg(msg);
            }
        );
    },


    showRecommendation2ResultForm: function(form, loadMask, diagModule, result) {
        var win = this;
        win.recommendation2ResultForm.show(
            loadMask,
            form,
            diagModule,
            result,
            function onReturn() {
                win.showResultForm(form, loadMask, diagModule);
            },
            function onFailure(msg) {
                //win.show();
                win.showErrorMsg(msg);
            }
        );
    },


    initComponent: function() {
        var win = this;

        win.MainPanel = new Ext6.form.FormPanel({
            autoHeight: true,
            bodyPadding: 30,
            border: false,
            frame: false,
            name: 'DSSEditorForm',
            id: 'DSSEditorForm',
            labelAlign: 'right',
            region: 'center',
            scrollable: true
        });

        Ext6.apply(win, {
            items: [
                win.MainPanel
            ],
            layout: 'border',
            border: false,
            buttons:
            [ '->'
            , {
                text: 'Отмена',
                handler: function() {
                    win._clean(win.MainPanel);
                    win.hide();
                }
            }
            ]
        });

        this.callParent(arguments);
    }
});
