/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      08.2014
*/

/**
 * swUslugaComplexSearchWindow - окно поиска услуги
 *
 * @class sw.Promed.swUslugaComplexSearchWindow
 * @extends sw.Promed.BaseForm
 */
sw.Promed.swUslugaComplexSearchWindow = Ext.extend(sw.Promed.BaseForm, {
    closable: true,
    width : 800,
    height : 500,
    modal: true,
    resizable: false,
    autoHeight: false,
    closeAction :'hide',
    border : false,
    plain : false,
    id: 'UslugaComplexSearchWindow',
    title: lang['usluga_poisk'],
    mode: 'all',
    onSelect: Ext.emptyFn,
    onHide: Ext.emptyFn,
    listeners: {
        hide: function(win) {
            win.onHide();
        }
    },
    
    /**
     * Отображение окна
     */
    show: function() {
        sw.Promed.swUslugaComplexSearchWindow.superclass.show.apply(this, arguments);

        if ( !arguments[0] || !arguments[0].store || typeof arguments[0].onSelect != 'function' ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {
                this.hide();
            }.createDelegate(this));
            return false;
        }

        this.onSelect = arguments[0].onSelect;
        this._store = arguments[0].store;
        this._query = arguments[0].query || '';

        if (typeof arguments[0].onHide == 'function') {
            this.onHide = arguments[0].onHide;
        } else {
            this.onHide = Ext.emptyFn;
        }

        if (arguments[0].UslugaCategory_id) {
            this.UslugaCategory_id = arguments[0].UslugaCategory_id;
        }

        this.doReset();
    }, //end show()

    doSelect: function() {
        this.getSelectBtn().setDisabled(true);
        var record = this.ViewFrame.getGrid().getSelectionModel().getSelected();
        if (!record) {
            Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_vyibora']);
            this.getSelectBtn().setDisabled(false);
            return false;
        }
        this.onSelect(record.data);
        return true;
    },
    doReset: function() {
        var bf = this.FormPanel.getForm(),
            queryField = bf.findField('query'),
            moreMsg = this.findById(this.getId() + '_moreMsg');
        bf.reset();
        queryField.setValue(this._query);
        queryField.focus(true, 250);
        this.ViewFrame.removeAll({clearAll:true});
        this.getSelectBtn().setDisabled(true);
        moreMsg.hide();
    },
    doSearch: function() {
        var params = this._store.baseParams,
            queryField = this.FormPanel.getForm().findField('query'),
            moreMsg = this.findById(this.getId() + '_moreMsg');
        params.query = queryField.getValue();
        if ( !params.query ) {
            sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { queryField.focus(true, 250); });
            return false;
        }
        if ( params.query.length < 2 ) {
            sw.swMsg.alert(lang['oshibka'], lang['vvedite_ne_menee_dvuh_simvolov'], function() { queryField.focus(true, 250); });
            return false;
        }

        if (!Ext.isEmpty(this.UslugaCategory_id)) {
            params.UslugaCategory_id = this.UslugaCategory_id;
        }

        this.ViewFrame.removeAll({clearAll:true});
        this.getSelectBtn().setDisabled(true);
        moreMsg.hide();
        this.ViewFrame.loadData({
            globalFilters: params,
            callback: function(records) {
                if (records.length > 99) {
                    moreMsg.show();
                }
            }
        });
        return true;
    },
    getSelectBtn: function() {
        return this.buttons[2];
    },

    /**
     * Конструктор
     */
    initComponent: function() {
        var me = this;

        me.ViewFrame = new sw.Promed.ViewFrame(
        {
            id: me.getId() + '_List',
            region: 'center',
            object: 'UslugaComplex',
            dataUrl: '/?c=Usluga&m=loadNewUslugaComplexList',
            //paging: me.paging,
            //root: (me.paging)?'data':null,
            //totalProperty: (me.paging)?'totalCount':null,
            focusOn: {name: me.getId() + '_searchBtn',type:'button'},
            focusPrev: {name: me.getId() + '_queryField',type:'field'},
            autoLoadData: false,
            autoExpandColumn: 'autoexpand',
            stringfields:[
                { name: 'UslugaComplex_id', type: 'string', header: 'ID', key: true },
                { name: 'UslugaComplex_2011id', type: 'string', hidden: true },
                { name: 'UslugaComplex_AttributeList', type: 'string', hidden: true },
                { name: 'UslugaCategory_id', type: 'string', hidden: true },
                { name: 'UslugaCategory_SysNick', type: 'string', hidden: true },
                { name: 'UslugaComplex_pid', type: 'string', hidden: true },
                { name: 'UslugaComplexLevel_id', type: 'string', hidden: true },
                {
                    name: 'UslugaComplex_Code',
                    header: lang['kod'], headerAlign: 'right',
                    align: 'right', width: 70,  type: 'string'
                },
                {
                    name: 'UslugaComplex_Name', id: 'autoexpand',
                    header: lang['naimenovanie'], headerAlign: 'center',
                    align: 'left', width: 250, 
                    renderer: function(value, cellEl, rec, row, origId, store) {
                        if (rec.get('UslugaComplexLevel_id') != 9) {
                            return value;
                        }
                        value = '<b>' + value + '</b>';
                        return value;
                    }
                },
                {
                    name: 'UslugaCategory_Name',
                    header: lang['kategoriya'], headerAlign: 'center',
                    align: 'center', type: 'string', width: 120
                },
                {
                    name: 'LpuSection_Name',
                    header: lang['otdelenie'], headerAlign: 'center',
                    align: 'center', type: 'string', width: 120
                },
                { name: 'UslugaComplex_begDT', type: 'date', dateFormat: 'd.m.Y', hidden: true },
                {
                    name: 'UslugaComplex_endDT', 
                    header: lang['data_zakryitiya'], headerAlign: 'center',
                    align: 'center', type: 'date', dateFormat: 'd.m.Y', width: 120
                },
                { name: 'UslugaComplex_UET', type: 'string', hidden: true },
                { name: 'FedUslugaComplex_id', type: 'string', hidden: true },
				{ name: 'UslugaComplex_hasComposition', hidden: true, type: 'int' },
                { name: 'LpuSectionProfile_id', hidden: true, type: 'string' },
                { name: 'MedSpecOms_id', hidden: true, type: 'string' }
            ],
            toolbar: false,
            disableActions: true,
            actions: [
                {name:'action_add', hidden: true, disabled: true},
                {name:'action_edit', hidden: true, disabled: true},
                {name:'action_view', hidden: true, disabled: true},
                {name:'action_delete', hidden: true, disabled: true},
                {name:'action_refresh', hidden: true, disabled: true},
                {name:'action_save', hidden: true, disabled: true},
                {name:'action_print', hidden: true, disabled: true}
            ],
            onLoadData: function (result)
            {
                me.getSelectBtn().setDisabled(!result);
            },
            onDblClick: function()
            {
                me.doSelect();
            },
            onEnter: function()
            {
                me.doSelect();
            }
        });
        
        me.FormPanel = new Ext.form.FormPanel( {
            autoHeight: true,
            border: false,
            buttonAlign: 'left',
            frame: true,
            id: me.getId() + '_ListForm',
            items: [{
                anchor: '100%',
                enableKeyEvents: true,
                fieldLabel: lang['naimenovanie'],
                id: me.getId() + '_queryField',
                listeners: {
                    'keydown': function(inp, e) {
                        if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true)
                        {
                            e.stopEvent();
                            me.buttons[5].focus();
                        }
                    }
                },
                name: 'query',
                xtype: 'textfield'
            },
            {
                id: me.getId() + '_moreMsg',
                height: 20,
                xtype:'label',
                html: lang['naydeno_bolee_99_zapisey_neobhodimo_utochnit_kriterii_poiska']
            }],
            keys: [{
                fn: function(e) {
                    me.doSearch();
                },
                key: Ext.EventObject.ENTER,
                stopEvent: true
            }],
            labelAlign: 'left',
            region: 'north'
        });

        Ext.apply(me, {
            layout: 'border',
            items: [
                me.FormPanel,
                me.ViewFrame
            ],
            buttons : [{
                handler: function() {
                    me.doSearch();
                },
                id: me.getId() + '_searchBtn',
                iconCls: 'search16',
                text: BTN_FRMSEARCH
            }, {
                handler: function() {
                    me.doReset();
                },
                iconCls: 'resetsearch16',
                text: BTN_FRMRESET
            }, {
                handler: function() {
                    me.doSelect();
                },
                iconCls: 'ok16',
                text: lang['vyibrat']
            }, {
                text: '-'
            },
            HelpButton(me),
            {
                handler: function() {
                    me.hide();
                },
                iconCls: 'cancel16',
                onTabElement: me.getId() + '_queryField',
                text: BTN_FRMCANCEL
            }],
            buttonAlign : "left"
        });
        sw.Promed.swUslugaComplexSearchWindow.superclass.initComponent.apply(me, arguments);
    } //end initComponent()
});