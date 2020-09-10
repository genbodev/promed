/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 24.10.14
 * Time: 13:23
 * To change this template use File | Settings | File Templates.
 */

sw.Promed.swScetSelectSMOWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: true,
    buttonAlign: 'left',
    modal: true,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    id: 'ScetSelectSMOWindow',
    title: 'Параметры печати счета',
    width: 400,
    layout: 'form',
    resizable: false,
    onHide: Ext.emptyFn,
    plain: true,
    initComponent: function()
    {
        this.Panel = new Ext.form.FormPanel(
            {
                autoHeight: true,
                bodyBorder: false,
                bodyStyle: 'padding: 5px 5px 0',
                border: false,
                frame: true,
                id: 'SMOSelectPanel',
                labelAlign: 'right',
                labelWidth: 50,
                items: [
                    {
                        anchor: '100%',
                        allowBlank: false,
                        fieldLabel: 'СМО',
                        xtype:'swbaselocalcombo',
                        hiddenName: 'OrgSMO_id',
                        store: new Ext.data.JsonStore({
                            url: '/?c=Registry&m=getOrgSMOListForExportRegistry',
                            editable: false,
                            key: 'OrgSMO_id',
                            autoLoad: false,
                            fields: [
                                {name: 'OrgSMO_id',    type:'int'},
                                {name: 'OrgSMO_Nick',  type:'string'},
                                {name: 'OrgSMO_Name',  type:'string'}
                            ],
                            sortInfo: {
                                field: 'OrgSMO_Nick'
                            }
                        }),
                        triggerAction: 'all',
                        displayField:'OrgSMO_Nick',
                        tpl: '<tpl for="."><div class="x-combo-list-item">'+
                            '{OrgSMO_Nick}'+
                            '</div></tpl>',
                        valueField: 'OrgSMO_id'
                    }
                ]
            });

        Ext.apply(this,
            {
                autoHeight: true,
                buttons: [
                    {
                        id: 'prntOk',
                        handler: function()
                        {
                            this.ownerCt.printScet();
                        },
                        iconCls: 'refresh16',
                        text: 'Печать счета'
                    },
                    {
                        text: '-'
                    },
                    HelpButton(this),
                    {
                        handler: function()
                        {
                            this.ownerCt.hide();
                        },
                        iconCls: 'cancel16',
                        onTabElement: 'prntOk',
                        text: BTN_FRMCANCEL
                    }],
                items: [this.Panel]
            });
        sw.Promed.swScetSelectSMOWindow.superclass.initComponent.apply(this, arguments);
    },

    listeners:
    {
        'hide': function()
        {
            if (this.refresh)
                this.onHide();
        }
    },
    printScet: function(addParams)
    {
        var paramRegistry_id = this.Registry_id;
        var form = this;
        var base_form = form.Panel.getForm();
        var paramOrgSMO_id = base_form.findField('OrgSMO_id').getValue();
        if ( !paramOrgSMO_id ){
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.ERROR,
                    msg: 'Не указана СМО.',
                    title: 'Ошибка'
                });
            return false;
        }

        Ext.Msg.show({
            title: 'Вопрос',
            buttons: {yes: "PDF", no: "XLS", cancel: "Отмена"},
            icon: Ext.MessageBox.QUESTION,
			msg: 'Выберите формат печати',
            fn: function(btn) {
                if( btn == 'cancel') {
                    return;
                }
                if (btn == 'yes'){
                    printBirt({
                        'Report_FileName': 'ScetPrint.rptdesign',
                        'Report_Params': '&paramRegistry_id=' + paramRegistry_id + '&paramOrgSMO_id=' + paramOrgSMO_id,
                        'Report_Format': 'pdf'
                    });
                }

                if (btn == 'no'){
                    printBirt({
                        'Report_FileName': 'ScetPrint.rptdesign',
                        'Report_Params': '&paramRegistry_id=' + paramRegistry_id + '&paramOrgSMO_id=' + paramOrgSMO_id,
                        'Report_Format': 'xls'
                    });
                }
            }
        });
        this.hide();
    },

    show: function()
    {
        sw.Promed.swScetSelectSMOWindow.superclass.show.apply(this, arguments);
        var form = this;

        form.Registry_id = null;
        form.onHide = Ext.emptyFn;
        Ext.getCmp('prntOk').enable();
        form.refresh = true;

        if (!arguments[0] || !arguments[0].Registry_id)
        {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.ERROR,
                    msg: 'Ошибка открытия формы ' + form.id + '.<br/>Не указаны необходимые входные параметры.',
                    title: 'Ошибка'
                });
            this.hide();
        }

        if (arguments[0].Registry_id)
        {
            form.Registry_id = arguments[0].Registry_id;
        }

        var base_form = form.Panel.getForm();
        base_form.findField('OrgSMO_id').getStore().removeAll();
        base_form.findField('OrgSMO_id').getStore().load({
            params: {
                Registry_id: form.Registry_id
            }
        });
        this.restore();
    }
});