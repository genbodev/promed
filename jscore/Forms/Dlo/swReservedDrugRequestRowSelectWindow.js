/**
* swReservedDrugRequestRowSelectWindow - окно выбора строки заявки из резерва
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Salakhov R.
* @version      11.2015
* @comment      
*/
sw.Promed.swReservedDrugRequestRowSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Добавление из резерва',
	layout: 'border',
	id: 'ReservedDrugRequestRowSelectWindow',
	modal: true,
	shim: false,
	width: 800,
	height: 138,
	resizable: false,
	maximizable: false,
	maximized: false,
    doSelect: function() {
        var wnd = this;
        if (!this.form.isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    wnd.findById('ReservedDrugRequestRowSelectForm').getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        var id = wnd.row_combo.getValue();
        var kolvo = wnd.form.findField('DrugRequestRow_Kolvo').getValue();

        if (id > 0 && kolvo > 0) {
            var idx = wnd.row_combo.getStore().findBy(function(rec) { return rec.get(wnd.row_combo.valueField) == id; });
            if (idx >= 0) {
                var record = wnd.row_combo.getStore().getAt(idx);
                var data = new Object();
                Ext.apply(data, record.data);
                data.DrugRequestRow_Kolvo = kolvo;
                wnd.onSelect(data);
            }
            wnd.hide();
        }
    },
	show: function() {
		sw.Promed.swReservedDrugRequestRowSelectWindow.superclass.show.apply(this, arguments);

        this.onSelect = Ext.emptyFn;
        this.DrugRequest_id = null;
        this.DrugRequestType_id = null;

        if (arguments[0].onSelect && typeof arguments[0].onSelect == 'function') {
            this.onSelect = arguments[0].onSelect;
        }

        if (!Ext.isEmpty(arguments[0].DrugRequest_id)) {
            this.DrugRequest_id = arguments[0].DrugRequest_id;
        }

        if (!Ext.isEmpty(arguments[0].DrugRequestType_id)) {
            this.DrugRequestType_id = arguments[0].DrugRequestType_id;
        }

        this.form.reset();
        this.row_combo.getStore().removeAll();
        this.row_combo.getStore().baseParams.DrugRequest_id = this.DrugRequest_id;
        this.row_combo.getStore().baseParams.DrugRequestType_id = this.DrugRequestType_id;
        this.row_combo.getStore().load();
        this.form.findField('DrugRequestRow_Kolvo').maxValue = 9999999;
	},
	initComponent: function() {
        var wnd = this;

        this.row_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: 'Медикамент',
            hiddenName: 'DrugRequestRow_id',
            displayField: 'DrugRequestRow_Name',
            valueField: 'DrugRequestRow_id',
            editable: false,
            triggerAction: 'all',
            trigger2Class: 'hideTrigger',
            width: 577,
            listWidth: 560,
            allowBlank: false,
            tpl: new Ext.XTemplate(
                '<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
                '<td style="padding: 2px; width: 10%;">Кол-во</td>',
                '<td style="padding: 2px; width: 15%;">Тип</td>',
                '<td style="padding: 2px; width: 75%;">Медикамент</td>',
                '</tr><tpl for="."><tr class="x-combo-list-item">',
                '<td style="padding: 2px;">{DrugRequestRow_Kolvo}&nbsp;</td>',
                '<td style="padding: 2px;">{DrugRequestType_Name}&nbsp;</td>',
                '<td style="padding: 2px;">{DrugRequestRow_Name}&nbsp;</td>',
                '</tr></tpl>',
                '</table>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'DrugRequestRow_id', mapping: 'DrugRequestRow_id' },
                    { name: 'DrugRequestRow_Name', mapping: 'DrugRequestRow_Name' },
                    { name: 'DrugRequestRow_Kolvo', mapping: 'DrugRequestRow_Kolvo' },
                    { name: 'DrugRequestType_Name', mapping: 'DrugRequestType_Name' }
                ],
                key: 'DrugRequestRow_id',
                sortInfo: { field: 'DrugRequestRow_Name' },
                url:'/?c=DrugRequest&m=loadReservedDrugRequestRowCombo'
            }),
            listeners: {
                select: function (combo, record, index) {
                    var kolvo_field = wnd.form.findField('DrugRequestRow_Kolvo');
                    kolvo_field.setValue(record.get('DrugRequestRow_Kolvo'));
                    kolvo_field.maxValue = record.get('DrugRequestRow_Kolvo');
                }
            }
        });

        var form = new Ext.form.FormPanel({
            bodyStyle: 'padding: 5px',
            border: false,
            region: 'center',
            autoHeight: true,
            frame: true,
            id: 'ReservedDrugRequestRowSelectForm',
            labelWidth: 105,
            items: [
                this.row_combo,
                {
                    fieldLabel: 'Кол-во',
                    name: 'DrugRequestRow_Kolvo',
                    xtype: 'numberfield',
                    minValue: 0,
                    allowNegative: false,
                    allowDecimal: true,
                    allowBlank: false
                }
            ]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
                    wnd.doSelect();
				},
				iconCls: 'ok16',
				text: 'Выбрать'
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swReservedDrugRequestRowSelectWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.findById('ReservedDrugRequestRowSelectForm').getForm();
	}
});