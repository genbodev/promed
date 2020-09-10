/**
 * swDSSViewerWindow - окно для просмотра данных регистров
 *
 * DSS - сбор структурированной медицинской информации и поддержка принятия решений
 *
 * Приложение для работы с клиническими регистрами -
 * приложение для анализа и визуализации собранных данных
 * заполненных анкет с фильтрами по территориям, датам
 * и клиническим признакам
 *
 * @package      Common.DSS
 * @access       public
 * @copyright    Copyright (c) 2018-2019 Swan Ltd.
 * @author       Yaroslav Mishlanov <ya.mishlanov@swan-it.ru>
 * @since        12.12.2018
 * @version      12.04.2019
 * javascript ES5(2011)
 */
Ext6.define('common.DSS.Viewer.DSSViewerWindow', {
    addCodeRefresh: Ext.emptyFn,
    addHelpButton: Ext.emptyFn,
    closeToolText: 'Закрыть',

    title: 'Сбор структурированной медицинской информации и поддержка принятия решений. Клинические регистры',
    extend: /*'Ext6.window.Window',*/'base.BaseForm',
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
     * Удалить все компоненты
     *
     * @param form: Ext6.Component - родительский компонент
     */
    _clean: function(form) {
        var win = this;

        win.moduleListForm.remove(form);
        win.registerListForm.remove(form);
        win.registerForm.remove(form);
    },


    /**
     * Обработчик события файлы окна загружены
     *
     * @param form: ExtComponent - родительский компонент
     */
    _onModulesLoaded: function(form) {
        var win = this;

        win.moduleListForm = new common.DSS.Viewer.DSSViewerModuleListForm();
        win.registerListForm = new common.DSS.Viewer.DSSViewerRegisterListForm();
        win.registerForm = new common.DSS.Viewer.DSSViewerRegisterForm();

        win._clean(form);

        win._showModuleListForm(form);
    },


    /**
     * Отображение окна приложения
     *
     */
    show: function(Lpu_id, MedPersonal_id, MedStaffFact_id) {
        this.Lpu_id = Lpu_id;
        this.MedPersonal_id = MedPersonal_id;
        this.MedStaffFact_id = MedStaffFact_id;

        var win = this;

        var form = this.MainPanel;
        this.callParent();

        Ext6.require([
            'common.DSS.Viewer.DSSViewerModuleListForm',
            'common.DSS.Viewer.DSSViewerRegisterListForm',
            'common.DSS.Viewer.DSSViewerRegisterForm'
        ]);
        Ext6.onReady(
            function() {
                win._onModulesLoaded(form);
            }
        );
    },


    /**
     * Отобразить страницу модулей
     *
     * @param parentComponent
     */
    _showModuleListForm: function(form) {
        var win = this;

        win.moduleListForm.show(
            form,
            win.genLoadMask,
            function onModuleSelected(moduleData) {
                win.moduleListForm.remove(form);
                win._showRegisterListForm(form, moduleData);
            },
            function onFailure(msg) {
                win.showErrorMsg(msg);
            }
        );
    },


    /**
     * Отобразить форму списка регистров в модуле
     *
     * @param parentComponent
     * @param moduleData
     */
    _showRegisterListForm: function(form, moduleData) {
        var win = this;

        win.registerListForm.show(
            form,
            win.genLoadMask,
            moduleData,
            function onReturn() {
                win.registerListForm.remove(form);
                win._showModuleListForm(form);
            },
            function onRegisterSelected(registerData) {
                win.registerListForm.remove(form);
                win._showRegisterForm(form, moduleData, registerData);
            },
            function onFailure(msg) {
                win.showErrorMsg(msg);
            }
        );
    },


    /**
     * Отобразить форму просмотра регистра
     *
     * @param parentComponent: Ext6.Component - родительский компонент
     * @param moduleData: {} - данные выбранного модуля
     * @param registerData: {} - данные выбранного регистра
     */
    _showRegisterForm: function(form, moduleData, registerData) {
        var win = this;

        win.registerForm.show(
            form,
            win.genLoadMask,
            {
                moduleData: moduleData,
                registerData: registerData,
                Lpu_id: win.Lpu_id,
                MedPersonal_id: win.MedPersonal_id,
                MedStaffFact_id: win.MedStaffFact_id
            },
            function onReturn() {
                win.registerForm.remove(form);
                win._showRegisterListForm(form, moduleData);
            },
            function onSessionSelected() {

            },
            function onFailure(msg) {
                win.showErrorMsg(msg);
            }
        );
    },


    /**
     * загрузка окна
     */
    initComponent: function() {
        var win = this;

        win.MainPanel = new Ext6.form.FormPanel({
            autoHeight: true,
            bodyPadding: 30,
            border: false,
            frame: false,
            name: 'DSSViewerForm',
            id: 'DSSViewerForm',
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
                    win._clean(win.MainPanel);
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
            //win.MainPanel.ownerCt.remove(win.MainPanel);
        }
    }
});
