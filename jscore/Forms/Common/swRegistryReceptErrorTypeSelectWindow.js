/**
* swRegistryReceptErrorTypeSelectWindow - окно выбора типа ошибки рецепта в реестре
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
sw.Promed.swRegistryReceptErrorTypeSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Выбор типа ошибки рецепта в реестре',
	layout: 'border',
	id: 'RegistryReceptErrorTypeSelectWindow',
	modal: true,
	shim: false,
	width: 800,
	height: 114,
	resizable: false,
	maximizable: false,
	maximized: false,
	show: function() {
		sw.Promed.swRegistryReceptErrorTypeSelectWindow.superclass.show.apply(this, arguments);

        this.onSelect = Ext.emptyFn;

        if (arguments[0].onSelect && typeof arguments[0].onSelect == 'function') {
            this.onSelect = arguments[0].onSelect;
        }

        this.error_combo.reset();
	},
	initComponent: function() {
        var wnd = this;

        this.error_combo = new sw.Promed.SwBaseRemoteCombo ({
            fieldLabel: 'Тип ошибки',
            hiddenName: 'RegistryReceptErrorType_id',
            displayField: 'RegistryReceptErrorType_Name',
            valueField: 'RegistryReceptErrorType_id',
            editable: false,
            triggerAction: 'all',
            trigger2Class: 'hideTrigger',
            width: 577,
            listWidth: 560,
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{RegistryReceptErrorType_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'RegistryReceptErrorType_id', mapping: 'RegistryReceptErrorType_id' },
                    { name: 'RegistryReceptErrorType_Name', mapping: 'RegistryReceptErrorType_Name' }
                ],
                key: 'RegistryReceptErrorType_id',
                sortInfo: { field: 'RegistryReceptErrorType_Name' },
                url:'/?c=RegistryLLO&m=loadRegistryReceptErrorTypeCombo'
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
            items: [this.error_combo]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
					wnd.onSelect({
                        RegistryReceptErrorType_id: wnd.error_combo.getValue()
                    });
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
		sw.Promed.swRegistryReceptErrorTypeSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});