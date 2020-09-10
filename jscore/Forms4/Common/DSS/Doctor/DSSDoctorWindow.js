/**
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
 * @since        24.05.2018
 * @version      11.04.2019
 */
Ext6.define('common.DSS.Doctor.DSSDoctorWindow', {
    addCodeRefresh: Ext.emptyFn,
    addHelpButton: Ext.emptyFn,
    closeToolText: 'Закрыть',

    title: 'Результаты интерактивного анкетирования',
    extend: /*'Ext6.window.Window',*/'base.BaseForm',
    maximized: false,//true,
    width: '75%',//width: 800,
    height: '100%',//height: 600,
    modal: true,

    findWindow: false,
    closable: true,
    cls: 'arm-window-new',
    renderTo: /*Ext6.getBody(),*/main_center_panel.body.dom,
    layout: 'border',

    plain: true,
    resizable: true,


    // общая маска
    genLoadMask: {
        init: function(form) {
            this.mask = new Ext6.LoadMask({
                msg: 'Подождите...',
                target: form
            });
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


    // общее сообщение об ошибке
    showErrorMsg: function(msg) {
        Ext6.Msg.alert('Ошибка', msg || 'Ошибка приложения');
    },


    /**
     * Получить информацию для диагностики проблемы получения адреса апи сервера из конфига на бете
     *
     */
    _debugProblem: function(loadMask, onSuccess) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSS&m=debugProblem',
            params: {
                key: 366
            },

            success: function (response, opts) {
                //Ext6.Msg.alert('Информация', response);
                onSuccess();
            },

            failure: function (response, opts) {
                loadMask.hide();
                Ext6.Msg.alert('Ошибка', 'Ошибка приложения');
            }
        });
    },


    /**
     * Убедиться, что пациент есть в БД и получить данные пациента
     *
     * @param patientId: string (bigint)
     * @return Promise
     */
    _putPatient: function(patientId, loadMask, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSS&m=putPatient',
            params: {
                Person_id: patientId
            },

            success: function (response, opts) {
                var data;
                var patientData;

                loadMask.hide();
                data = JSON.parse(response.responseText);
                if (data.success === false) {
                    onFailure(data.Error_Msg);
                    return;
                }
                patientData = data[0];
                if (patientData.error) {
                    onFailure(patientData.error);
                    return;
                }
                onSuccess(patientData);
            },

            failure: function (response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Обработчик события файлы окна загружены
     *
     * @param form: ExtComponent - родительский компонент
     * @param patientData: {}
     */
    _onModulesLoaded: function(form, patientData) {
        var win = this;
        var data;

        win.questionnaireForm = new common.DSS.Doctor.DSSDoctorQuestionnaireForm();
        win.conclusionForm = new common.DSS.Doctor.DSSDoctorConclusionForm();
        win.actualStateForm = new common.DSS.Doctor.DSSDoctorActualStateForm();
        win.dynamicsForm = new common.DSS.Doctor.DSSDoctorDynamicsForm();

        win.actualStateForm.remove(form);
        win.dynamicsForm.remove(form);
        win.questionnaireForm.remove(form);

        data = {
            patient: patientData
        };

        win._showActualState(form, data);
    },


    /**
     * Отображение окна приложения
     *
     * @param bigint patientId - идентификатор пациента
     */
    show: function(patientId) {
        var win = this;

        var form = this.MainPanel;
        this.callParent();

        win._debugProblem(
            win.genLoadMask,
            function onSuccess(patientData) {
                win._putPatient(
                    win._bigInt('' + patientId),
                    win.genLoadMask,
                    function onSuccess(patientData) {
                        Ext6.require([
                            'common.DSS.Doctor.DSSDoctorActualStateForm',
                            'common.DSS.Doctor.DSSDoctorDynamicsForm',
                            'common.DSS.Doctor.DSSDoctorQuestionnaireForm',
                            'common.DSS.Doctor.DSSDoctorConclusionForm'
                        ]);
                        Ext6.onReady(
                            function() {
                                win._onModulesLoaded(form, patientData);
                            }
                        );
                    },
                    function onFailure(e) {
                        win.showErrorMsg(e);
                    }
                );
            }
        );
    },


    /**
     * Отобразить страницу опросника
     *
     * @param parentComponent
     * @param data: {patient}
     */
    _showQuestionForm: function(form, data) {
        var win = this;
        var newData;

        newData = {
            patient: data.patient
        };

        win.questionnaireForm.show(
            data,
            form,
            win.genLoadMask,
            function onShowActualState() {
                win.questionnaireForm.remove(form);
                win._showActualState(form, newData);
            },
            function onShowDynamics() {
                win.questionnaireForm.remove(form);
                win._showDynamics(form, newData);
            },
            win._showConclusionPanel.bind(win, data),
            function onFailure(msg) {
                win.questionnaireForm.remove(form);
                win._showPanel(form, newData);
                win.showErrorMsg(msg);
            }
        );
    },


    /**
     * Раздел отображения результатов формируется отдельно от страницы опросника
     *
     * @param data: {patient, module, session}
     * @param textPanel: Ext6.Panel
     * @param resultPanel: Ext6.Panel
     * @param quesions: {}
     * @param answers: {}
     */
    _showConclusionPanel: function(data, textPanel, resultPanel, questions, answers, onShowExplanationCellClick) {
        var win = this;

        data.questions = questions;
        data.answers = answers;

        win.conclusionForm.show(
            data,
            textPanel,
            resultPanel,
            win.genLoadMask,
            win.questionnaireForm.questionnaire,
            onShowExplanationCellClick,
            function onFailure(msg) {
                win.showErrorMsg(msg);
            }
        );
    },


    /**
     * Отобразить панель для визуализации текущего состояния пациента
     *
     * @param parentComponent
     * @param data: {patient}
     */
    _showActualState: function(form, data) {
        var win = this;

        win.actualStateForm.show(
            data,
            form,
            win.genLoadMask,
            function onShowDynamics() {
                win.actualStateForm.remove(form);
                win._showDynamics(form, data);
            },
            function onFailure(msg) {
                win.showErrorMsg(msg);
            }
        );
    },

    /**
     * Отобразить панель для визуализации динамики жалоб пациента
     *
     * @param parentComponent
     * @param data: {patient}
     */
    _showDynamics: function(form, data) {
        var win = this;

        win.dynamicsForm.show(
            data,
            form,
            win.genLoadMask,
            function onShowActualState() {
                win.dynamicsForm.remove(form);
                win._showActualState(form, data);
            },
            function onShowSessionDetails(module, session) {
                data.module = module;
                data.session = session;
                win.dynamicsForm.remove(form);
                win._showQuestionForm(form, data);
            },
            function onFailure(msg) {
                win.showErrorMsg(msg);
            }
        );
    },


    /**
     * Валидация идентификатора типа bigint (аналог intval)
     *
     * В связи с тем, что bigint может быть до 2^63 - 1,
     * а javascript может только до 2^53 - 1,
     * для работы с идентификаторами испольуются строки
     * @param id: string - значение параметра типа bigint
     * @param name: string - название параметра для корректного вывода сообщения об ошибке
     * @return id: string
     */
    _bigInt: function(id, name) {
        var re = /[^0-9]/;

        if ((!id) && (id !== '0')) {
            throw new Error('id is not int: empty. ' + name);
        }

        if (id.match(re)) {
            throw new Error('id is not int: symbols. ' + name);
        }

        if (id.length > 19) {
            throw new Error('id is not int: too big. '+ name);
        }

        return id;
    },



    /**
     * загрузка окна
     */
    initComponent: function() {
        var win = this;

        win.MainPanel = new Ext6.form.FormPanel({
            autoHeight: true,
            bodyPadding: 32,
            border: false,
            frame: false,
            name: 'DSSForm',
            id: 'DSSForm',
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
                text: langs('Отмена'),
                handler: function() {
                    //win.close();
                    win.hide();
                }
            }]
        });

        win.genLoadMask.init(win.MainPanel);

        this.callParent(arguments);
    },


    listeners: {
        close: function() {
            //const win = this;
            //win.MainPanel.ownerCt.remove(win.MainPanel);
        }
    }
});
