/**
* swReceptStatusFLKMEKSelectWindow - окно выбора статуса рецепта в реестре
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
sw.Promed.swReceptStatusFLKMEKSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Выбор статуса рецепта в реестре',
	layout: 'border',
	id: 'ReceptStatusFLKMEKSelectWindow',
	modal: true,
	shim: false,
	width: 800,
	height: 114,
	resizable: false,
	maximizable: false,
	maximized: false,
	show: function() {
		sw.Promed.swReceptStatusFLKMEKSelectWindow.superclass.show.apply(this, arguments);

        this.onSelect = Ext.emptyFn;

        if (arguments[0].onSelect && typeof arguments[0].onSelect == 'function') {
            this.onSelect = arguments[0].onSelect;
        }

        this.status_combo.reset();
	},
	initComponent: function() {
        var wnd = this;

        this.status_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: 'Статус рецепта',
            hiddenName: 'ReceptStatusFLKMEK_id',
            displayField: 'ReceptStatusFLKMEK_Name',
            valueField: 'ReceptStatusFLKMEK_id',
            editable: false,
            triggerAction: 'all',
            trigger2Class: 'hideTrigger',
            width: 577,
            listWidth: 560,
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<font color="red">{ReceptStatusFLKMEK_Code}</font>&nbsp;{ReceptStatusFLKMEK_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'ReceptStatusFLKMEK_id', mapping: 'ReceptStatusFLKMEK_id' },
                    { name: 'ReceptStatusFLKMEK_Code', mapping: 'ReceptStatusFLKMEK_Code' },
                    { name: 'ReceptStatusFLKMEK_Name', mapping: 'ReceptStatusFLKMEK_Name' }
                ],
                key: 'ReceptStatusFLKMEK_id',
                sortInfo: { field: 'ReceptStatusFLKMEK_Code' },
                url:'/?c=RegistryLLO&m=loadReceptStatusFLKMEKCombo'
            })
        });

        var form = new Ext.form.FormPanel({
            bodyStyle: 'padding: 5px',
            border: false,
            region: 'center',
            autoHeight: true,
            frame: true,
            id: 'rretsFormPanel',
            labelWidth: 105,
            items: [this.status_combo]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
                    var id = wnd.status_combo.getValue();
                    if (id > 0) {
                        var idx = wnd.status_combo.getStore().findBy(function(rec) { return rec.get(wnd.status_combo.valueField) == id; });
                        if (idx >= 0) {
                            var record = wnd.status_combo.getStore().getAt(idx);
                            wnd.onSelect(record.data);
                        }
                    }
                    wnd.hide();
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
		sw.Promed.swReceptStatusFLKMEKSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});