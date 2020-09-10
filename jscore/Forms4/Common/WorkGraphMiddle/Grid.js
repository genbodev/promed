/**
 * common.WorkGraphMiddle - Model, Store, Grid.
 *
 * График дежурств среднего медперсонала - модель, хранилище, таблица.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access    public
 * @copyright 2019
 */
Ext6.define('common.WorkGraphMiddle.Model', {
    extend: 'Ext6.data.Model',

    fields: [{
            name: 'WorkGraphMiddle_id',
            type: 'int'
        },
        {
            name: 'MedStaffFact_id',
            type: 'int'
        },
        {
            name: 'Person_SurName',
            type: 'string'
        },
        {
            name: 'Person_FirName',
            type: 'string'
        },
        {
            name: 'Person_SecName',
            type: 'string'
        },
        {
            name: 'WorkGraphMiddle_begDate',
            type: 'date'
        },
        {
            name: 'WorkGraphMiddle_endDate',
            type: 'date'
        }
    ]
});

Ext6.define('common.WorkGraphMiddle.Store', {
    extend: 'Ext6.data.Store',

    alias: 'store.common.work_graph_middle',

    model: 'common.WorkGraphMiddle.Model',

    proxy: {
        type: 'ajax',
        actionMethods: {
            create: "POST",
            read: "POST",
            update: "POST",
            destroy: "POST"
        },
        url: '/?c=WorkGraphMiddle&m=selWorkGraphMiddle',

        reader: {
            type: 'json',
            rootProperty: 'data'
        }
    }
});

Ext6.define('common.WorkGraphMiddle.Grid', {
    extend: 'Ext6.grid.Panel',

    requires: [
        'common.WorkGraphMiddle.Store'
    ],

    xtype: 'common.work_graph_middle.grid',

    cls: 'grid-common',

    columns: [{
            text: lang['familiya'],
            dataIndex: 'Person_SurName',
            flex: 1
        },
        {
            text: lang['imya'],
            dataIndex: 'Person_FirName',
            flex: 1
        },
        {
            text: lang['otchestvo'],
            dataIndex: 'Person_SecName',
            flex: 1
        },
        {
            xtype: 'datecolumn',
            text: lang['duty_begin_date'],
            dataIndex: 'WorkGraphMiddle_begDate',
            format: 'd.m.Y G:i',
            width: 150
        },
        {
            xtype: 'datecolumn',
            text: lang['duty_end_date'],
            dataIndex: 'WorkGraphMiddle_endDate',
            format: 'd.m.Y G:i',
            width: 150
        }
    ],

    store: {
        type: 'common.work_graph_middle'
    },

    bbar: {
        xtype: 'pagingtoolbar',
        displayInfo: true
    }
});
