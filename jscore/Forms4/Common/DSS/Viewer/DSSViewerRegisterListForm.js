/**
 * Страница выбора регистра
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
 * @version      12.04.2019
 */
Ext6.define('common.DSS.Viewer.DSSViewerRegisterListForm', {


    /**
     * Отобразить страницу выбора регистра
     *
     * @param form: ExtComponent - родительский компонент
     * @param loadMask: LoadMask - индикатор ожидания
     * @param moduleData: {} - данные выбранного модуля
     * @param onReturn: function - кнопка возврата назад, к выбору модуля
     * @param onRegisterSelected: function - переход к отображению выбранного регистра
     * @param onFailure: function - обработчик непредвиденной ошибки
     */
    show: function(form, loadMask, moduleData, onReturn, onRegisterSelected, onFailure) {
        var self = this;
        var onRegisterRowClick;
        var genRegistersForm;
        var grid;

        genRegistersForm = new Ext6.form.FormPanel({
            id: 'genRegistersForm',
            border: false,
            items: [

                new Ext6.form.FormPanel({
                    border: false,
                    html: '<h1>Модуль <i>' + moduleData.moduleName + '</i></h1>',
                    width: '100%',
                    style: 'margin-bottom: 32px;'
                }),

                new Ext6.Button({
                    text: 'Вернуться к выбору модуля',
                    style: 'margin: 32px 24px;',
                    handler: onReturn
                })
            ]
        });
        form.add(genRegistersForm);

        onRegisterRowClick = function(view, cell, cellIndex, record, row, rowIndex, e) {
            onRegisterSelected({
                registerId: record.get('registerId'),
                registerName: record.get('registerName')
            });
        };
        grid = self._makeGrid(onRegisterRowClick);
        genRegistersForm.add(grid);

        self._loadRegisters(loadMask, moduleData, grid, onFailure);
    },


    /**
     * Сформировать форму
     *
     * @param onRegisterRowClick: function - выбрать регистр по нажанию на строку таблицы
     * @return grid - созданные компоненты
     */
    _makeGrid: function(onRegisterRowClick) {
        return new Ext6.grid.GridPanel({
            store: new Ext6.data.Store({
                autoLoad: false,
                fields: [
                    'registerId',
                    'registerName'
                ],
                proxy: {
                    type: 'ajax',
                    reader: {
                        type: 'json',
                        transform: {
                            fn: function(data) {
                                return (
                                    (data)
                                    && (data.length === 1)
                                    && (data[0] === 'empty')
                                ) ? [] : data;
                            }
                        }
                    },
                    url: '/?c=DSSViewer&m=getRegisters'
                }
            }),
            autoHeight: true,
            width: '100%',
            title: 'Выбор регистра',
            viewConfig: {
                deferEmptyText: false,
                emptyText: '<div style="text-align: center;">Список пуст</div>'
            },
            columns: [{
                dataIndex: 'registerId',
                name: 'registerId',
                header: 'Идентификатор',
                flex: 1,
                align: 'center'
            }, {
                dataIndex: 'registerName',
                name: 'registerName',
                header: 'Название',
                flex: 5,
                align: 'center'
            }],
            listeners: {
                cellclick: onRegisterRowClick
            }
        });
    },


    /**
     * Загрузить данные регистров в грид
     *
     * @param loadMask: Ext6.Component - индикатор ожидания
     * @param moduleData: {} - данные модуля
     * @param grid: Ext6.Component - грид куда загружать данные
     * @param emptyLabel: Ext6.Component - панель для отображения вместо грида, если получен пустой список
     * @param onFailure: function - действие в случае непредвиденной ошибки
     */
    _loadRegisters: function(loadMask, moduleData, grid, onFailure) {
        loadMask.show();
        grid.getStore().load({
            params: {
                moduleId: moduleData.moduleId,
                moduleName: moduleData.moduleName
            },
            callback: function(records, operation, success) {
                loadMask.hide();
                if (!success) {
                    onFailure('Request was unsuccessful');
                }
                if (
                    (records.length)
                    && ((typeof records[0].data) === 'string')
                ) {
                    onFailure(records[0].data);
                }
            }
        });
    },


    /**
     * Удалить со страницы все элементы формы выбора регистра
     *
     * @param parentComponent: ExtComponent
     */
    remove: function(form) {
        var genRegistersForm = form.getComponent('genRegistersForm');
        if (genRegistersForm) {
            form.remove(genRegistersForm);
        }
    }
});
