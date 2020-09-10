/**
 * swWorkGraphMiddleWindow
 *
 * График дежурств среднего медперсонала - основное окно.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access    public
 * @copyright 2019
 */
Ext6.define('common.WorkGraphMiddle.swWorkGraphMiddleWindow', {
    extend: 'base.BaseForm',

    requires: [
        'common.WorkGraphMiddle.Grid'
    ],

    title: lang['duty_schedule_middle'],

    maximized: true,
    constrain: true,

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    items: [
        // Фильтр:
        {
            xtype: 'form',
            itemId: 'frmFilter',
            border: false,
            layout: 'hbox',
            padding: 10,

            defaults: {
                labelWidth: 150
            },

            items: [{
                    xtype: 'swMedStaffFactCombo',
                    itemId: 'cmbMedStaffFact',
                    name: 'MedStaffFact_id',
                    fieldLabel: lang['sotrudnik'],
                    flex: 1
                },
                Ext6.create('Ext6.date.RangeField', {
                    fieldLabel: lang['duty_date'],
                    itemId: 'dtRange',
                    width: 400,
                    margin: '0 0 0 10'
                }),
                {
                    xtype: 'button',
                    itemId: 'btnFind',
                    text: lang['nayti'],
                    cls: 'button-primary',
                    iconCls: 'action_find_white',
                    margin: '0 10 0 20'
                },
                {
                    xtype: 'button',
                    itemId: 'btnClear',
                    text: lang['sbros'],
                    cls: 'button-secondary',
                    iconCls: 'action_clear'
                }
            ]
        },

        // Панель инструментов:
        {
            xtype: 'toolbar',
            border: false,
            cls: 'grid-toolbar',
            padding: 0,

            items: [{
                    xtype: 'button',
                    itemId: 'btnAdd',
                    text: lang['dobavit'],
                    iconCls: 'action_add'
                },
                {
                    xtype: 'button',
                    itemId: 'btnEdit',
                    text: lang['izmenit'],
                    iconCls: 'action_edit',
                    disabled: true
                },
                {
                    xtype: 'button',
                    itemId: 'btnView',
                    text: lang['prosmotret'],
                    iconCls: 'action_view',
                    disabled: true
                },
                {
                    xtype: 'button',
                    itemId: 'btnDel',
                    text: lang['udalit'],
                    iconCls: 'action_delete',
                    disabled: true
                },
                {
                    xtype: 'button',
                    itemId: 'btnRefresh',
                    text: lang['obnovit'],
                    iconCls: 'action_refresh'
                },
                {
                    xtype: 'button',
                    itemId: 'btnPrint',
                    text: lang['pechat'],
                    iconCls: 'action_print',
                    disabled: true
                }
            ]
        },

        // Таблица:
        {
            xtype: 'common.work_graph_middle.grid',
            border: false,
            flex: 1
        }
    ],

    _frmFilter: undefined,

    _cmbMedStaffFact: undefined,
    _dtRange: undefined,

    _btnFind: undefined,

    _btnEdit: undefined,
    _btnView: undefined,
    _btnDel: undefined,
    _btnPrint: undefined,

    _grid: undefined,

    _userMedStaffFact: undefined,
    _curDate: undefined,

    /******* initComponent ********************************************************
     *
     ******************************************************************************/
    initComponent: function() {
        var btnClear,
            btnAdd,
            btnRefresh;

        this.callParent(arguments);

        this._frmFilter = this.down('#frmFilter');

        this._cmbMedStaffFact = this.down('#cmbMedStaffFact');
        this._dtRange = this.down('#dtRange');

        this._btnFind = this.down('#btnFind');
        this._btnFind.handler = this._onClick_find;
        this._btnFind.scope = this;

        btnClear = this.down('#btnClear');
        btnClear.handler = this._onClick_clear;
        btnClear.scope = this;

        btnAdd = this.down('#btnAdd');
        btnAdd.handler = this._onClick_add;
        btnAdd.scope = this;

        this._btnEdit = this.down('#btnEdit');
        this._btnEdit.handler = this._onClick_edit;
        this._btnEdit.scope = this;

        this._btnView = this.down('#btnView');
        this._btnView.handler = this._onClick_view;
        this._btnView.scope = this;

        this._btnDel = this.down('#btnDel');
        this._btnDel.handler = this._onClick_del;
        this._btnDel.scope = this;

        btnRefresh = this.down('#btnRefresh');
        btnRefresh.handler = this._onClick_refresh;
        btnRefresh.scope = this;

        this._btnPrint = this.down('#btnPrint');
        this._btnPrint.handler = this._onClick_print;
        this._btnPrint.scope = this;

        this._grid = this.down('grid');
        this._grid.addListener({
            selectionchange: this._onSelectionChange,
            itemdblclick: this._onItemDblClick,
            scope: this
        });
    },

    /******* show *****************************************************************
     *
     ******************************************************************************/
    show: function(params) {
        var store,
            date;

        this.callParent(arguments);

        this._userMedStaffFact = params.userMedStaffFact;

        date = getGlobalOptions().date.match(/(\d+).(\d+).(\d+)/);
        this._curDate = new Date(date[3], date[2] - 1, date[1]);

        setMedStaffFactGlobalStoreFilter({
                LpuSection_id: this._userMedStaffFact.LpuSection_id,
                isMidMedPersonalOnly: true
            },
            sw4.swMedStaffFactGlobalStore);

        this._cmbMedStaffFact.setValue(null);
        store = this._cmbMedStaffFact.getStore();
        store.removeAll();
        store.loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
        this._loadData();
    },

    /******* _onClick_find ********************************************************
     *
     ******************************************************************************/
    _onClick_find: function() {
        this._loadData();
    },

    /******* _loadData ************************************************************
     *
     ******************************************************************************/
    _loadData: function() {
        var v,
            params = {};

        if (v = this._cmbMedStaffFact.getValue())
            params.MedStaffFact_id = v;

        this._dtRange._read();

        if ((v = this._dtRange.getDates()).length) {
            params.fromDate = v[0].dateFormat('Y-m-d');

            if (v.length > 1)
                params.toDate = v[1].dateFormat('Y-m-d');
        }

        this._grid.getStore().load({
            params: params
        });
    },

    /******* _onClick_clear *******************************************************
     *
     ******************************************************************************/
    _onClick_clear: function() {
        this._cmbMedStaffFact.setValue(null);
        this._dtRange.clear();
    },

    /******* _onClick_add *********************************************************
     *
     ******************************************************************************/
    _onClick_add: function() {
        getWnd('swWorkGraphMiddleEditWindow').show({
            action: 'add',
            userMedStaffFact: this._userMedStaffFact,
            callback: this._refreshData,
            scope: this
        });
    },

    /******* _onClick_edit ********************************************************
     *
     ******************************************************************************/
    _onClick_edit: function() {
        this._editRecord(this._grid.getSelection()[0]);
    },

    /******* _editRecord **********************************************************
     *
     ******************************************************************************/
    _editRecord: function(record) {
        getWnd('swWorkGraphMiddleEditWindow').show({
            action: 'edit',
            recordData: record.getData(),
            userMedStaffFact: this._userMedStaffFact,
            callback: this._refreshData,
            scope: this
        });
    },

    /******* _onClick_view ********************************************************
     *
     ******************************************************************************/
    _onClick_view: function() {
        this._viewRecord(this._grid.getSelection()[0]);
    },

    /******* _viewRecord **********************************************************
     *
     ******************************************************************************/
    _viewRecord: function(record) {
        getWnd('swWorkGraphMiddleEditWindow').show({
            action: 'view',
            recordData: record.getData(),
            userMedStaffFact: this._userMedStaffFact
        });
    },

    /******* _onClick_del *********************************************************
     *
     ******************************************************************************/
    _onClick_del: function() {
        var vals = this._grid.getSelection()[0].getData();

        Ext6.Msg.show({
            title: lang['vnimanie'],

            message: Ext6.String.format(lang['duty_middle_del_prompt'],
                vals.Person_SurName,
                vals.Person_FirName,
                vals.Person_SecName,
                vals.WorkGraphMiddle_begDate.dateFormat('d.m.Y G:i'),
                vals.WorkGraphMiddle_endDate.dateFormat('d.m.Y G:i')),

            icon: Ext6.MessageBox.QUESTION,
            buttons: Ext6.MessageBox.YESNO,
            fn: _deleteRecord,
            scope: this
        });

        /******* _deleteRecord ********************************************************
         *
         */
        function _deleteRecord(btnId) {
            if (btnId == 'yes') {
                this.setLoading(true);

                Ext6.Ajax.request({
                    url: '/?c=WorkGraphMiddle&m=delWorkGraphMiddle',

                    params: {
                        WorkGraphMiddle_id: vals.WorkGraphMiddle_id
                    },

                    callback: function(opts, success, response) {
                        this.setLoading(false);

                        if (success)
                            this._refreshData();
                    },

                    scope: this
                });
            }
        }
    },

    /******* _onClick_refresh *****************************************************
     *
     ******************************************************************************/
    _onClick_refresh: function() {
        this._refreshData();
    },

    /******* _refreshData *********************************************************
     *
     ******************************************************************************/
    _refreshData: function() {
        this._grid.getStore().reload();
    },

    /******* _onClick_print *******************************************************
     *
     ******************************************************************************/
    _onClick_print: function() {
        Ext6.ux.GridPrinter.print(this._grid, {
            selections: this._grid.getSelection()
        });
    },

    /******* _onSelectionChange ***************************************************
     *
     ******************************************************************************/
    _onSelectionChange: function(grid, records) {
        this._btnEdit.setDisabled(records.length != 1 ||
            records[0].get('WorkGraphMiddle_endDate') <= this._curDate);

        this._btnView.setDisabled(records.length != 1);

        this._btnDel.setDisabled(records.length != 1 ||
            records[0].get('WorkGraphMiddle_begDate') <= this._curDate);

        this._btnPrint.setDisabled(records.length != 1);
    },

    /******* _onItemDblClick ******************************************************
     *
     ******************************************************************************/
    _onItemDblClick: function(grid, record) {
        if (!this._btnEdit.isDisabled())
            this._editRecord(record);
        else if (!this._btnView.isDisabled())
            this._viewRecord(record);
    }
});
