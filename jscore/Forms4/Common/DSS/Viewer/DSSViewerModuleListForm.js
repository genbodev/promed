/**
 * Страница выбора модуля
 *
 * swDSSViewerWindow - окно для просмотра данных регистров
 *
 * DSS - сбор структурированной медицинской информации и поддержка принятия решений
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
 * @since        12.12.2018
 * @version      28.06.2019
 */
Ext6.define('common.DSS.Viewer.DSSViewerModuleListForm', {


    /**
     * Отобразить страницу выбора модуля
     *
     * @param form: ExtComponent - родительский компонент
     * @param loadMask: LoadMask - индикатор ожидания
     * @param questionnaire - объект для преобразований опросника
     * @param onSessionDetails: function - переход к отображению выбранной анкеты
     * @param onFailure: function - обработчик ошибки
     */
    show: function(form, loadMask, onModuleSelected, onFailure) {
        var self = this;

        // общая форма для страницы. которая содержит все компоненты
        //     и удаляется при смене страницы
        var genModulesForm;

        genModulesForm = new Ext6.form.FormPanel({
            id: 'genModulesForm',
            border: false
        });
        form.add(genModulesForm);

        genModulesForm.add(self._makeGrid(
                self._onModuleRowClick.bind(self, onModuleSelected)));
    },


    /**
     * Обработчик нажатия на строку модуля
     *
     */
    _onModuleRowClick: function(onModuleSelected, view, cell, cellIndex, record, row, rowIndex, e) {
        if (record.get('moduleStatusCode') !== 't') {
            // если клик на недоступный модуль
            return;
        }

        onModuleSelected({
            moduleId: record.get('moduleId'),
            moduleName: record.get('moduleName')
        });
    },

    /**
     * Преобразовать данные, полученные от сервера, в форма для вставки в таблицу
     *
     */
    _transformData: function(data) {
        if (typeof data[0] === 'string') {
            // особые случаи ответа сервера
            if (data[0] === 'empty') {
                // хук для передачи пустого списка - список модулей пуст
                return [];
            } else {
                // ошибка
                Ext6.Msg.alert('Ошибка', data[0]);
                return [];
            }
        }

        return data.map(function(row) {
            var moduleStatus;
            var moduleStatusCode;

            moduleStatus = function(moduleData) {
                if (moduleData.moduleStatus === 'P') {
                    return 'Модуль находится в разработке';
                }
                if (moduleData.moduleStatus !== 'Y') {
                    return 'Модуль не доступен';
                }
                /*if (moduleData.isAvailable !== 't') {
                    return 'В данный момент модуль редактируется. Попробуйте позже';
                }*/
                return 'Доступен';
            };

            moduleStatusCode = function(moduleData) {
                if (moduleData.moduleStatus !== 'Y') {
                    return 'f';
                }
                /*if (moduleData.isAvailable !== 't') {
                    return 'f';
                }*/
                return 't';
            };

            row.moduleStatusCode = moduleStatusCode(row);
            row.moduleStatus = moduleStatus(row);

            return(row);
        });
    },


    /**
     * Сформировать грид модулей
     *
     * @param {function} onModuleRowClick - обработчик выбора модуля
     * @return {Ext6.grid.GridPanel}
     */
    _makeGrid: function(onModuleRowClick) {
        var self = this;
        var addTip;
        var store;

        addTip = function(val, metaData, record) {
            if (val) {
                metaData.tdAttr = 'data-qtip="' + val + '"';
            }
            return '<div style="white-space: normal;">' + val + '</div>';
        };

        store = new Ext6.data.Store({
            autoLoad: true,
            fields: [
                'moduleId',
                'moduleName'
            ],
            proxy: {
                type: 'ajax',
                reader: {
                    type: 'json',
                    transform: {
                        fn: self._transformData
                    }
                },
                url: '/?c=DSSViewer&m=getAllModules'
            }
        });

        return new Ext6.grid.GridPanel({
            store: store,
            autoHeight: true,
            width: '100%',
            title: 'Выбор модуля',
            viewConfig: {
                deferEmptyText: false,
                emptyText: '<div style="text-align: center;">Список пуст</div>'
            },
            columns: [{
                dataIndex: 'moduleId',
                name: 'moduleId',
                tdCls: 'nameTdCls',
                header: 'Идентификатор',
                flex: 1,
                align: 'center'
            }, {
                dataIndex: 'moduleName',
                name: 'moduleName',
                header: 'Название',
                flex: 5,
                align: 'center',
                renderer: addTip
            }, {
                dataIndex: 'moduleStatus',
                name: 'moduleStatus',
                header: 'Статус',
                flex: 5,
                align: 'center',
                renderer: addTip
            }, {
                dataIndex: 'moduleStatusCode',
                tdCls: 'nameTdCls',
                name: 'moduleStatus',
                hidden: true
            }],
            listeners: {
                cellclick: onModuleRowClick
            }
        });
    },


    /**
     * Удалить со страницы все элементы панели анкет
     *
     * @param {ExtComponent} parentComponent
     */
    remove: function(form) {
        var genModulesForm = form.getComponent('genModulesForm');
        if (genModulesForm) {
            form.remove(genModulesForm);
        }
    }
});
