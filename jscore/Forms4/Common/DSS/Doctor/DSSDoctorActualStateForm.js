/**
 * Панель для визуализации актуального состояния пациента (жалобы)
 *
 * swDSSWindow - окно для работы врача с опросником
 *   для сбора структурированной медицинской информации и поддержки принятия решений
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.DSS
 * @access       public
 * @copyright    Copyright (c) 2018-2019 Swan Ltd.
 * @author       Yaroslav Mishlanov <ya.mishlanov@swan-it.ru>
 * @since        22.01.2019
 * @version      11.04.2019
 */
Ext6.define('common.DSS.Doctor.DSSDoctorActualStateForm', {


    /**
     * Получить данные последних заполненных анкет во всех модулях
     *
     * @return Promise
     */
    _getRecentData: function(patientId, loadMask, onSuccess, onFailure) {
        loadMask.show();
        Ext6.Ajax.request({
            url: '/?c=DSS&m=getRecentData',
            params: {
                Person_id: patientId
            },

            success: function (response, opts) {
                var data;
                var recentData;

                loadMask.hide();
                data = JSON.parse(response.responseText);
                if (data.success === false) {
                    onFailure(data.Error_Msg);
                    return;
                }
                recentData = data[0];
                if (recentData.error) {
                    onFailure(recentData.error);
                    return;
                }
                onSuccess(recentData);
            },

            failure: function (response, opts) {
                loadMask.hide();
                onFailure();
            }
        });
    },


    /**
     * Отобразить страницу панели анкет
     *
     * @param patientId: bigint - идкетификатор пациента
     * @param form: ExtComponent - родительский компонент
     * @param loadMask: LoadMask - индикатор ожидания
     * @param onShowDynamics: function - переход к отображению выбранной анкеты
     * @param onFailure: function - обработчик ошибки
     */
    show: function(data, form, loadMask, onShowDynamics, onFailure) {
        var self = this;

        var genActualStateForm = new Ext6.form.FormPanel({
            id: 'genActualStateForm',
            border: false,
            items: [
                new Ext6.form.FormPanel({
                    border: false,
                    autoHeight: true,
                    html: data.patient.patientFullName + ', ' +  data.patient.patientAge + ' лет',
                    bodyPadding: 16
                }),
                new Ext6.button.Segmented({
                    allowMultiple: false,
                    allowDepress: false,
                    style: 'margin-bottom: 32px;',
                    items: [{
                        name: 'actual',
                        text: 'Текущие данные жалоб пациента',
                        pressed: true
                    }, {
                        name: 'dynamics',
                        text: 'Динамика жалоб пациента'
                    }, {
                        name: 'questionnaire',
                        text: 'Анкета',
                        disabled: true
                    }],
                    listeners: {
                        toggle: function(container, button, pressed) {
                            if (button.name === 'dynamics') {
                                onShowDynamics();
                            }
                        }
                    }
                }
            )]
        });
        form.add(genActualStateForm);

        self._getRecentData(
            data.patient.patientId,
            loadMask,
            function onSuccess(recentData) {
                if ((typeof recentData !== 'object') || (!recentData.length)) {
                    genActualStateForm.add(
                        new Ext6.form.FormPanel({
                            border: false,
                            autoHeight: true,
                            html: 'Нет данных',
                            padding: 32
                        })
                    );
                    return;
                }
                genActualStateForm.add(self._makeRecentDataPanel(recentData));
            },
            function onFailure() {
                genActualStateForm.add(
                    new Ext6.form.FormPanel({
                        border: false,
                        autoHeight: true,
                        html: 'Не удалось получить данные',
                        padding: 32,
                        style: 'color: red;'
                    })
                );
                return;
            }
        );
    },


    /**
     * Удалить со страницы все элементы панели анкет
     *
     * @param parentComponent: ExtComponent
     */
    remove: function(form) {
        var genActualStateForm = form.getComponent('genActualStateForm');
        if (genActualStateForm) {
            form.remove(genActualStateForm);
        }
    },


    /**
     * Сформировать панель данных последних анкет
     *
     * @param recentData: RecentData
     * @return string
     */
    _makeRecentDataPanel: function(recentData) {
        var self = this;

        var recentDataForm = new Ext6.form.FormPanel({
            border: false,
            autoHeight: true
        });

        recentData.map(function(recentDataItem) {
            var diagModule = recentDataItem.module;
            var session = recentDataItem.session;
            var questions = recentDataItem.questions;
            var recentDataItemForm;
            var text;

            recentDataItemForm = new Ext6.form.FormPanel({
                border: true,
                autoHeight: true,
                title: diagModule.moduleName,
                bodyPadding: 16
            });
            recentDataForm.add(recentDataItemForm);
            /*if (module.isAvailable !== 't') {
                recentDataItemForm.update(
                    '<div>В данный момент модуль редактируется</div>'
                );
                return;
            }*/
            /*
            // отображать данные даже если модуль недоступен
            if (diagModule.moduleStatus !== 'Y') {
                recentDataItemForm.update('<div>Модуль не доступен</div>');
                return;
            }
            */
            text = questions.map(function(question) {
                // корневый вопросы - жалобы - жирно и с новой строки
                return (question.root)
                    ? '<li style="margin-top: 8px;"><b>' + question.text + '</b></li>'
                    : question.text + '; ';
            }).join('');

            recentDataItemForm.update(
                '<div style="font-size: 120%; font-variant: small-caps;">'
                +     self._timeTransform(session.sessionCloseDT)
                + '</div>'
                + '<div>' + text + '</div>'
            );
        });

        return recentDataForm;
    },



    /**
     * Преобразовать дату из формата, как она хранится в БД (по utc)
     * в человеческий формат (в местном часовом поясе)
     *
     * @param dateFromDB: string
     * @return string
     */
    _timeTransform: function(dateFromDB) {
        var myDate = new Date();
        var year = parseInt(dateFromDB.substring(0, 4));
        var month = parseInt(dateFromDB.substring(5, 7));
        var day = parseInt(dateFromDB.substring(8, 10));
        myDate.setUTCFullYear(year, month-1, day);
        var hours = parseInt(dateFromDB.substring(11, 13));
        var minutes = parseInt(dateFromDB.substring(14, 16));
        myDate.setUTCHours(hours, minutes);
        var myTime = myDate.toLocaleString().substring(0, 17);
        if (myTime.substring(16, 17) == ':') {
            return myTime.substring(0, 16);
        }
        return myTime;
    }
});
