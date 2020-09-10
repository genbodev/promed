/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 02.09.14
 * Time: 9:47
 * To change this template use File | Settings | File Templates.
 */
sw.Promed.swPersonDispDiagHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
    buttonAlign: 'left',
    closable: true,
    closeAction: 'hide',
    draggable: true,
    height: 400,
    id: 'PersonDispDiagHistory',
    title: lang['diagnozyi_kartyi_du'],
    width: 600,
    deleteItem: function(){
        var form = Ext.getCmp('PersonDispDiagHistory');
        var DiagHistoryGrid = form.findById('PDDH_DiagHistoryGrid').ViewGridPanel;
        var current_row = DiagHistoryGrid.getSelectionModel().getSelected();
        var PersonDisp_id = this.PersonDisp_id;
        if(DiagHistoryGrid.getStore().getCount()==1)
            alert(lang['udalenie_nevozmojno_doljen_ostatsya_hotya_byi_odin_diagnoz']);
        else{
            sw.swMsg.show({
                title: lang['podtverjdenie_udaleniya'],
                msg: lang['vyi_deystvitelno_jelaete_udalit_etu_zapis'],
                buttons: Ext.Msg.YESNO,
                fn: function ( buttonId ) {
                    if ( buttonId == 'yes' )
                    {
                        Ext.Ajax.request({
                            url: '/?c=PersonDisp&m=deleteDiagDispCard',
                            params: {DiagDispCard_id: current_row.get('DiagDispCard_id'), PersonDisp_id: PersonDisp_id},
                            callback: function() {
                                DiagHistoryGrid.getStore().load({
                                    params: {
                                        PersonDisp_id: PersonDisp_id
                                    },
                                    callback: function() {
                                        if ( DiagHistoryGrid.getStore().getCount() > 0 )
                                        {
                                            DiagHistoryGrid.getSelectionModel().selectFirstRow();
                                            DiagHistoryGrid.getView().focusRow(0);
                                        }
                                    }
                                });
                            }
                        });
                    }
                }
            });
        }
    },
    addItem: function(){
        var form = Ext.getCmp('PersonDispDiagHistory');
        var DiagHistoryGrid = form.findById('PDDH_DiagHistoryGrid').ViewGridPanel;
        var current_row = DiagHistoryGrid.getSelectionModel().getSelected();
        var PersonDisp_id = this.PersonDisp_id;
        getWnd('swPersonDispDiagHistoryEditWindow').show({
            action: 'add',
            PersonDisp_id: PersonDisp_id,
            callback: function() {
                DiagHistoryGrid.getStore().load({
                    params: {
                        PersonDisp_id: PersonDisp_id
                    },
                    callback: function() {
                        if ( DiagHistoryGrid.getStore().getCount() > 0 )
                        {
                            DiagHistoryGrid.getSelectionModel().selectFirstRow();
                            DiagHistoryGrid.getView().focusRow(0);
                        }
                    }
                });
            },
            onClose: function() {}
        });
    },
    openItem: function(mode){
        var form = Ext.getCmp('PersonDispDiagHistory');
        var DiagHistoryGrid = form.findById('PDDH_DiagHistoryGrid').ViewGridPanel;
        var current_row = DiagHistoryGrid.getSelectionModel().getSelected();
        var PersonDisp_id = this.PersonDisp_id;
        var action = mode;
        getWnd('swPersonDispDiagHistoryEditWindow').show({
            action: action,
            DiagDispCard_id: current_row.get('DiagDispCard_id'),
            PersonDisp_id: PersonDisp_id,
            callback: function() {
                DiagHistoryGrid.getStore().load({
                    params: {
                        PersonDisp_id: PersonDisp_id
                    },
                    callback: function() {
                        if ( DiagHistoryGrid.getStore().getCount() > 0 )
                        {
                            DiagHistoryGrid.getSelectionModel().selectFirstRow();
                            DiagHistoryGrid.getView().focusRow(0);
                        }
                    }
                });
            },
            onClose: function() {}
        });
    },
    initComponent: function() {
        Ext.apply(this, {
            buttons: [
                {
                    text: '-'
                },
                HelpButton(this),
                {
                    handler: function() {
                        var form = Ext.getCmp('PersonDispDiagHistory');
                        var DiagHistoryGrid = form.findById('PDDH_DiagHistoryGrid').ViewGridPanel.getStore();
                        var Diag_id = DiagHistoryGrid.data.items[DiagHistoryGrid.getCount()-1].data.Diag_id;
                        var PersonDispDiagField = Ext.getCmp('PersonDispEditWindow').FormPanel.getForm().findField('Diag_id');
                        PersonDispDiagField.setValue(Diag_id);
                        PersonDispDiagField.getStore().load({
                            callback: function() {
                                PersonDispDiagField.fireEvent('select', PersonDispDiagField, PersonDispDiagField.getStore().getAt(0), 0);
                            },
                            params: {
                                where:'where Diag_id = '+PersonDispDiagField.getValue()
                            }

                        });
                        Ext.getCmp('PersonDispDiagHistory').hide();
                    },
                    iconCls: 'cancel16',
                    text: BTN_FRMCLOSE
                }
            ],
            items: [
                    new Ext.Panel({
                    height: 400,
                    border: false,
                    items: [
                        new sw.Promed.ViewFrame(
                            {
                                actions:
                                    [
                                        {
                                            name: 'action_add',
                                            handler: function() {
                                                Ext.getCmp('PersonDispDiagHistory').addItem();
                                            },
                                            disabled: false
                                        },
                                        {
                                            name: 'action_edit',
                                            handler: function() {
                                                Ext.getCmp('PersonDispDiagHistory').openItem('edit');
                                            },
                                            disabled: false
                                        },
                                        {
                                            name: 'action_view', handler: function() {
                                            Ext.getCmp('PersonDispDiagHistory').openItem('view');
                                            },
                                            disabled: false
                                        },
                                        {
                                            name: 'action_delete',
                                            handler: function() {
                                                Ext.getCmp('PersonDispDiagHistory').deleteItem();
                                            },
                                            disabled: false
                                        },
                                        {
                                            name: 'action_refresh',
                                            disabled: false
                                        }
                                    ],
                                autoLoadData: false,
                                border: false,
                                //selectionModel: 'cell',
                                autoexpand: 'expand',
                                dataUrl: '?c=PersonDisp&m=loadDiagDispCardHistory',
                                id: 'PDDH_DiagHistoryGrid',
                                region: 'center',
                                sm: new Ext.grid.RowSelectionModel({
                                    listeners: {
                                        'rowselect': function(sm, rowIndex, record) {
                                            if(rowIndex == 0){
                                                this.findById('PDDH_DiagHistoryGrid').setActionDisabled('action_edit',true);
                                                this.findById('PDDH_DiagHistoryGrid').setActionDisabled('action_delete',true);
                                            }
                                            else {
                                                this.findById('PDDH_DiagHistoryGrid').setActionDisabled('action_edit',false);
                                                this.findById('PDDH_DiagHistoryGrid').setActionDisabled('action_delete',false);
                                            }
                                        }.createDelegate(this)
                                    }
                                }),
                                stringfields:
                                    [

                                        {name: 'DiagDispCard_id', type: 'int', hidden: true, key:true},
                                        {name: 'DiagDispCard_Date',  type: 'string', header: lang['data_ustanovki'], width: 150},
                                        {name: 'Diag_FullName',  type: 'string', header: lang['diagnoz'], width: 436},
                                        {name: 'Diag_id',  type: 'int', hidden: true, width: 436}
                                    ]
                            })
                    ]
                })]
        });
        sw.Promed.swPersonDispDiagHistoryWindow.superclass.initComponent.apply(this, arguments);
    },
    show: function(){
        sw.Promed.swPersonDispDiagHistoryWindow.superclass.show.apply(this, arguments);
        this.onHide = Ext.emptyFn;
        this.PersonDisp_id = arguments[0].PersonDisp_id;
        var form = Ext.getCmp('PersonDispDiagHistory');
        var DiagHistoryGrid = form.findById('PDDH_DiagHistoryGrid').ViewGridPanel;
        DiagHistoryGrid.getStore().load({
            params: {
                PersonDisp_id: this.PersonDisp_id
            },
            callback: function() {
                if ( DiagHistoryGrid.getStore().getCount() > 0 )
                {
                    DiagHistoryGrid.getSelectionModel().selectFirstRow();
                    DiagHistoryGrid.getView().focusRow(0);
                }
            }
        });
        this.restore();
        this.center();
    }
});