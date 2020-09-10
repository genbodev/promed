/**
* swMzDrugRequestMoViewWindow - окно просмотра заявок МО
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov Rustam
* @version      10.2012
* @comment      
*/
sw.Promed.swMzDrugRequestMoAddWindow = Ext.extend(sw.Promed.BaseForm, {
    title: 'Заявка МО: Добавление',
    modal: true,
    id:'MzDrugRequestMoAddWindow',
    width: 600,
    autoHeight: true,
    action:'edit',
    doSave:  function() {
        var wnd = this;
        if (!this.FormPanel.getForm().isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    wnd.FormPanel.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }
        this.submit();
        return true;
    },
    submit: function() {
        var wnd = this;
        var form = this.FormPanel.getForm();

        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
        //loadMask.show();

        var params = new Object();

        params.RegionDrugRequest_id = this.RegionDrugRequest_id; //1 - Начальная;
        params.Lpu_id = form.findField('Lpu_id').getValue();

        form.submit({
            params: params,
            failure: function(result_form, action) {
                //loadMask.hide();
                if (action.result)  {
                    if (action.result.Error_Code) {
                        Ext.Msg.alert('Ошибка #' + action.result.Error_Code, action.result.Error_Message);
                    }
                }
            },
            success: function(result_form, action) {
                //loadMask.hide();
                if (typeof wnd.onSave == 'function') {
                    wnd.onSave(wnd.owner, action.result.DrugRequest_id);
                }
                wnd.hide();
            }
        });
    },
    show: function() {
        sw.Promed.swMzDrugRequestMoAddWindow.superclass.show.apply(this, arguments);

        this.onSave = Ext.emptyFn;
        this.RegionDrugRequest_id = null;

        if (arguments[0]) {
            if (arguments[0].onSave) {
                this.onSave = arguments[0].onSave;
            }
            if (arguments[0].RegionDrugRequest_id) {
                this.RegionDrugRequest_id = arguments[0].RegionDrugRequest_id;
            }
        } else {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }

        this.FormPanel.getForm().reset();
        this.lpu_combo.getStore().baseParams.Date = (new Date()).format('d.m.Y');
    },
    initComponent: function() {
        var wnd = this;

        this.lpu_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: 'МО',
            hiddenName: 'Lpu_id',
            displayField: 'Lpu_Name',
            valueField: 'Lpu_id',
            allowBlank: false,
            editable: true,
            anchor: '100%',
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{Lpu_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'Lpu_id', mapping: 'Lpu_id' },
                    { name: 'Lpu_Name', mapping: 'Lpu_Name' }
                ],
                key: 'Lpu_id',
                sortInfo: { field: 'Lpu_Name' },
                url:'/?c=MzDrugRequest&m=loadLpuCombo'
            }),
            onTrigger2Click: function() {
                var combo = this;

                if (combo.disabled) {
                    return false;
                }

                combo.clearValue();
                combo.lastQuery = '';
                combo.getStore().removeAll();
                combo.getStore().baseParams.query = '';
                combo.fireEvent('change', combo, null);
            },
            setValueById: function(id) {
                var combo = this;
                combo.store.baseParams.Lpu_id = id;
                combo.store.load({
                    callback: function(){
                        combo.setValue(id);
                        combo.store.baseParams.Lpu_id = null;
                    }
                });
            }
        });

        this.FormPanel = new Ext.form.FormPanel({
            height:50,
            bodyStyle: 'padding: 5px',
            buttonAlign: 'left',
            frame: true,
            id: this.id+'FormPanel',
            labelAlign: 'right',
            labelWidth: 50,
            url:'/?c=MzDrugRequest&m=saveDrugRequestMo',
            items: [this.lpu_combo]
        });

        Ext.apply(this, {
            buttons: [{
                handler: function() {
                    wnd.doSave();
                },
                iconCls: 'save16',
                text: BTN_FRMSAVE
            }, {
                text: '-'
            }, {
                handler: function() {
                    wnd.hide();
                },
                iconCls: 'cancel16',
                text: BTN_FRMCANCEL
            }],
            items: [
                this.FormPanel
            ]
        });
        sw.Promed.swMzDrugRequestMoAddWindow.superclass.initComponent.apply(this, arguments);
    }
});