/**
 * swPersonPrivilegeWOWStreamInputWindow - окно Регистр ВОВ: Поточный ввод"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Abakhri
 * @version      21.05.2013
 */

sw.Promed.swPersonPrivilegeWOWStreamInputWindow = Ext.extend(sw.Promed.BaseForm, {
    addPersonPrivilegeWOW: function()
    {
        var frm = this;
        if (getWnd('swPersonSearchWindow').isVisible())
        {
            sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
            return false;
        }

        if (getWnd('swPersonPrivilegeWOWEditWindow').isVisible())
        {
            Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_lgotyi_uchastnika_vov_uje_otkryito']);
            return false;
        }

        getWnd('swPersonSearchWindow').show(
            {
                onClose: function()
                {
                    frm.doStreamInputSearch();
                },
                onSelect: function(person_data)
                {
                    getWnd('swPersonPrivilegeWOWEditWindow').show({
                        action: 'add',
                        callback: function() {
                            frm.findById('PersonPrivilegeWOWViewGrid').getGrid().getStore().reload();

                        },
                        formParams: {
                            Person_id: person_data.Person_id,
                            Server_id: person_data.Server_id
                        }
                    });
                }
            });
    },
    buttonAlign: 'left',
    viewPersonPrivilegeWOW: function() {
        var grid = this.findById('PersonPrivilegeWOWViewGrid').ViewGridPanel;
        var current_row = grid.getSelectionModel().getSelected();
        if (!current_row)
            return;
        if ((!grid.getSelectionModel().getSelected())||(grid.getStore().getCount()==0))
            return;
        var person_id = grid.getSelectionModel().getSelected().data.Person_id;
        var server_id = grid.getSelectionModel().getSelected().data.Server_id;
        var PersonPrivilegeWOW_id = grid.getSelectionModel().getSelected().data.PersonPrivilegeWOW_id;
        getWnd('swPersonPrivilegeWOWEditWindow').show({
            action: 'view',
            formParams: {
                Person_id: person_id,
                PersonPrivilegeWOW_id: PersonPrivilegeWOW_id,
                Server_id: server_id
            },
            callback: function(callback_data) {
                grid.getStore().reload();
            },
            onClose: function() {
            }
        });

    },
    editPersonPrivilegeWOW: function() {
        var grid = this.findById('PersonPrivilegeWOWViewGrid').ViewGridPanel;
        var current_row = grid.getSelectionModel().getSelected();
        if (!current_row)
            return;
        if ((!grid.getSelectionModel().getSelected())||(grid.getStore().getCount()==0))
            return;
        var person_id = grid.getSelectionModel().getSelected().data.Person_id;
        var server_id = grid.getSelectionModel().getSelected().data.Server_id;
        var PersonPrivilegeWOW_id = grid.getSelectionModel().getSelected().data.PersonPrivilegeWOW_id;
        getWnd('swPersonPrivilegeWOWEditWindow').show({
            action: 'edit',
            formParams: {
                Person_id: person_id,
                PersonPrivilegeWOW_id: PersonPrivilegeWOW_id,
                Server_id: server_id
            },
            callback: function(callback_data) {
                grid.getStore().reload();
            },
            onClose: function() {
            }
        });
    },
    closable: true,
    closeAction: 'hide',
    collapsible: true,
    monitorResize: true,
    draggable: true,
    deletePersonPrivilegeWOW: function() {
        var current_window = this;
        var grid = current_window.findById('PersonPrivilegeWOWViewGrid').ViewGridPanel;
        var current_row = grid.getSelectionModel().getSelected();
        if (!current_row)
            return;
        if ( !current_row.get('PersonPrivilegeWOW_id') || current_row.get('PersonPrivilegeWOW_id') == '' )
            return;
       sw.swMsg.show({
            title: lang['podtverjdenie_udaleniya'],
            msg: lang['vyi_deystvitelno_jelaete_udalit_etu_zapis'],
            buttons: Ext.Msg.YESNO,
            fn: function ( buttonId ) {
                if ( buttonId == 'yes' )
                {
                    Ext.Ajax.request({
                        url: '?c=PersonPrivilegeWOW&m=deletePersonPrivilegeWOW',
                        params: {PersonPrivilegeWOW_id: current_row.data.PersonPrivilegeWOW_id},
                        callback: function() {
                            current_window.doStreamInputSearch();
                        }
                    });
                }
            }
        });
    },
    doStreamInputSearch: function() {
        var grid = this.findById('PersonPrivilegeWOWViewGrid').ViewGridPanel;
        var params = {
        begDate: this.begDate,
        begTime: this.begTime,
        start: 0,
        limit: 100
        };
        params.SearchFormType = 'PersonPrivilegeWOW';
        grid.getStore().removeAll();
        grid.getStore().baseParams = params;
        grid.getStore().load({
        params: params
        });
    },
    getBegDateTime: function() {
        var current_window = this;
        Ext.Ajax.request({
            url: C_LOAD_CURTIME,
            callback: function(opt, success, response) {
                if (success && response.responseText != '')
                {
                    var response_obj = Ext.util.JSON.decode(response.responseText);

                    current_window.begDate = response_obj.begDate;
                    current_window.begTime = response_obj.begTime;
                    current_window.doStreamInputSearch();
                    current_window.findById('Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
                    current_window.findById('pmUser_Name').setValue(response_obj.pmUser_Name);
                }
            }
        });
    },
    height: 550,
    id: 'PersonPrivilegeWOWStreamInputWindow',
    getButtonSearch: function() {
        // TODO: правильно юзать scope кнопки
        return Ext.getCmp('PDDSW_SearchButton');
    },
    initComponent: function() {
        Ext.apply(this, {
            buttons: [
               HelpButton(this, -1),
                {
                    handler: function() {
                        this.ownerCt.hide();
                    },
                    iconCls: 'cancel16',
                    tabIndex: TABINDEX_DDREG + 55,
                    text: BTN_FRMCANCEL
                }
            ],
            items: [
                new Ext.Panel({
                    autoHeight: true,
                    region: 'north',
                    bodyStyle:'padding:3px',
                    layout: 'form',
                    labelWidth: 120,
                    items: [{
                        disabled: true,
                        fieldLabel: lang['polzovatel'],
                        id: 'pmUser_Name',
                        width: 380,
                        xtype: 'textfield'
                    }, {
                        disabled: true,
                        fieldLabel: lang['data_nachala_vvoda'],
                        id: 'Stream_begDateTime',
                        width: 165,
                        xtype: 'textfield'
                    }]
                }),
                new sw.Promed.ViewFrame({
                    actions:
                        [
                            {name:'action_add', disabled: !(isSuperAdmin() || getRegionNick().inlist(['ufa','ekb','penza','astra'])), handler: function() {this.addPersonPrivilegeWOW();}.createDelegate(this)},
                            {name:'action_edit', disabled: !(isSuperAdmin() || getRegionNick().inlist(['ufa','ekb','penza','astra'])), handler: function() {this.editPersonPrivilegeWOW();}.createDelegate(this)},
                            {name:'action_view', disabled: !(isSuperAdmin() || getRegionNick().inlist(['ufa','ekb','penza','astra'])), handler: function() {this.viewPersonPrivilegeWOW();}.createDelegate(this)},
                            {name:'action_delete', disabled: !(isSuperAdmin() || getRegionNick().inlist(['ufa','ekb','penza','astra'])), handler: function() {this.deletePersonPrivilegeWOW();}.createDelegate(this)},
                            {name: 'action_refresh', disabled: false, handler: function() {this.doStreamInputSearch();}.createDelegate(this)}
                        ],
                    autoLoadData: false,
                    //dataUrl: C_SEARCH,
                    dataUrl: '/?c=PersonPrivilegeWOW&m=loadStreamPersonPrivilegeWOW',
                    id: 'PersonPrivilegeWOWViewGrid',
                    //object: 'PersonPrivilegeWOW',
                    pageSize: 100,
                    paging: true,
                    region: 'center',
                    root: '',
                    store: new Ext.data.Store({
                        autoLoad: false,
                        listeners: {
                            'load': function(store, records, options) {
                                var evn_recept_grid = Ext.getCmp('PersonPrivilegeWOWViewGrid');
                                if ( store.getCount() == 0 ) {
                                    LoadEmptyRow(evn_recept_grid);
                                }
                                evn_recept_grid.getTopToolbar().items.items[12].el.innerHTML = '0 / ' + store.getCount();
                            }
                        },
                        reader: new Ext.data.JsonReader({
                            id: 'PersonEvn_id'
                        }, [{
                            mapping: 'Person_id',
                            name: 'Person_id',
                            type: 'int'
                        }, {
                            mapping: 'Server_id',
                            name: 'Server_id',
                            type: 'int'
                        }, {
                            mapping: 'PersonEvn_id',
                            name: 'PersonEvn_id',
                            type: 'int'
                        }, {
                            dateFormat: 'd.m.Y',
                            mapping: 'Person_Birthday',
                            name: 'Person_Birthday',
                            type: 'date'
                        }, {
                            mapping: 'Person_Surname',
                            name: 'Person_Surname',
                            type: 'string'
                        }, {
                            mapping: 'Person_Firname',
                            name: 'Person_Firname',
                            type: 'string'
                        }, {
                            mapping: 'Person_Secname',
                            name: 'Person_Secname',
                            type: 'string'
                        }, {
                            mapping: 'PersonPrivilegeWOW_id',
                            name: 'PersonPrivilegeWOW_id',
                            type: 'int'
                        }, {
                            mapping: 'ua_name',
                            name: 'ua_name',
                            type: 'string'
                        }, {
                            mapping: 'pa_name',
                            name: 'pa_name',
                            type: 'string'
                        }, {
                            mapping: 'PrivilegeTypeWOW_Name',
                            name: 'PrivilegeTypeWOW_Name',
                            type: 'string'
                        }, {
                            mapping: 'PrivilegeTypeWOW_id',
                            name: 'PrivilegeTypeWOW_id',
                            type: 'int'
                        }]),
                        url: '/?c=PersonPrivilegeWOW&m=loadStreamPersonPrivilegeWOW'
                    }),
                    stringfields:
                        [
                            { name: 'PersonPrivilegeWOW_id', type: 'int', header: 'ID', key: true },
                            { name: 'Person_id', type: 'int', hidden: true },
                            { name: 'PersonEvn_id', type: 'int', hidden: true },
                            { name: 'Server_id', type: 'int', hidden: true },
                            { name: 'PrivilegeTypeWOW_id',  type: 'int', hidden: true},
                            { name: 'Person_Surname', type: 'string', header: lang['familiya'],  width: 150, id: 'autoexpand' },
                            { name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150 },
                            { name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150 },
                            { name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'] },
                            { name: 'ua_name', type: 'string', header: lang['adres_registratsii']},
                            { name: 'pa_name', type: 'string', header: lang['adres_projivaniya']},
                            { name: 'PrivilegeTypeWOW_Name', header: lang['kategoriya'], width: 350 }
                        ],
                    toolbar: true
                })
            ]
        });

        sw.Promed.swPersonPrivilegeWOWStreamInputWindow.superclass.initComponent.apply(this, arguments);
    },
    keys: [{
        key: Ext.EventObject.INSERT,
        fn: function(e) {Ext.getCmp("swPersonPrivilegeWOWStreamInputWindow").addPersonPrivilegeWOW();},
        stopEvent: true
    }, {
        alt: true,
        fn: function(inp, e) {
            var current_window = Ext.getCmp('swPersonPrivilegeWOWStreamInputWindow');
            switch (e.getKey())
            {
                case Ext.EventObject.J:
                    current_window.hide();
                    break;
                case Ext.EventObject.C:
                    current_window.doResetAll();
                    break;
            }
        },
        key: [ Ext.EventObject.J, Ext.EventObject.C, Ext.EventObject.N ],
        stopEvent: true
    }],
    layout: 'border',
    maximizable: true,
    minHeight: 550,
    minWidth: 900,
    modal: false,
    plain: true,
    resizable: true,
    show: function() {
        sw.Promed.swPersonPrivilegeWOWStreamInputWindow.superclass.show.apply(this, arguments);
        this.restore();
        this.center();
        this.maximize();
        this.doLayout();
        this.getBegDateTime();
    },
    title: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? "Регистр инвалидов, подлежащих ДВН: Поточный ввод" :WND_POL_EPLWOWSTREAM,
    width: 900
});