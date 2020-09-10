/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      08.2013
*/

/**
 * swSelectUslugaComplexWindow - окно выбора услуги
 *
 * @class sw.Promed.swSelectUslugaComplexWindow
 * @extends sw.Promed.BaseForm
 */
sw.Promed.swSelectUslugaComplexWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	width : 700,
	height : 140,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['vyibor_uslugi'],
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
		sw.Promed.swSelectUslugaComplexWindow.superclass.show.apply(this, arguments);

        if (arguments[0] && typeof arguments[0].onSelect == 'function') {
            this.onSelect = arguments[0].onSelect;
        } else {
            this.onSelect = Ext.emptyFn;
        }
        if (arguments[0] && typeof arguments[0].onHide == 'function') {
            this.onHide = arguments[0].onHide;
        } else {
            this.onHide = Ext.emptyFn;
        }
        if (arguments[0] && typeof arguments[0].mode == 'string' && arguments[0].mode.inlist(['all','MedService'])) {
            this.mode = arguments[0].mode;
        } else {
            this.mode = 'all';
        }
        this.setVisibleUslugaComplexCombo();
        var uc_field = this.getUslugaComplexCombo();
        uc_field.getStore().removeAll();
        uc_field.clearValue();
        uc_field.lastQuery = 'This query sample that is will never appear';
        if (arguments[0] && arguments[0].baseParams && typeof arguments[0].baseParams == 'object') {
            uc_field.getStore().baseParams = arguments[0].baseParams;
        } else {
            uc_field.getStore().baseParams = {
                UslugaComplex_Date: getGlobalOptions().date
            };
        }
        uc_field.focus(true, 100);
        this.buttons[0].enable();
    }, //end show()

    getUslugaComplexCombo: function() {
        var bf = this.findById('SelectUslugaComplexForm').getForm();
        if (this.mode == 'all') {
            return bf.findField('UslugaComplexAll_id');
        } else {
            return bf.findField('UslugaComplexMedService_id');
        }
    },
    setVisibleUslugaComplexCombo: function() {
        var bf = this.findById('SelectUslugaComplexForm').getForm();
        if (this.mode == 'all') {
            bf.findField('UslugaComplexAll_id').setContainerVisible(true);
            bf.findField('UslugaComplexMedService_id').setContainerVisible(false);
        } else {
            bf.findField('UslugaComplexMedService_id').setContainerVisible(true);
            bf.findField('UslugaComplexAll_id').setContainerVisible(false);
        }
    },

    doSelect: function() {
		this.buttons[0].disable();
        var uc_field = this.getUslugaComplexCombo();
        var record = uc_field.getStore().getById(uc_field.getValue());
        if (!record) {
            Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_vyibora_uslugi']);
            this.buttons[0].enable();
            return false;
        }
        this.onSelect(record);
        return true;
	}, //end doSelect()

	/**
	 * Конструктор
	 */
	initComponent: function() {
		var form = this;

    	Ext.apply(this, {
			items : [new Ext.form.FormPanel({
				id : 'SelectUslugaComplexForm',
				height : 75,
				layout : 'form',
				border : false,
				frame : true,
				labelWidth : 120,
				items : [{
					xtype: 'fieldset',
					style : 'padding: 10px;',
					autoHeight: true,
					items : [{
						anchor : "95%",
                        value: null,
                        fieldLabel: lang['usluga'],
                        hiddenName: 'UslugaComplexAll_id',
                        listWidth: 600,
                        width: 500,
                        xtype: 'swuslugacomplexnewcombo'
					},{
                        anchor : "95%",
                        value: null,
                        fieldLabel: lang['usluga'],
                        hiddenName: 'UslugaComplexMedService_id',
                        listWidth: 600,
                        width: 500,
                        xtype: 'swuslugacomplexmedservicecomdo'
                    }]
			    }]
			})],
			buttons : [{
				text : lang['vyibrat'],
				iconCls : 'ok16',
				handler : function() {
                    form.doSelect();
				}
			}],
			buttonAlign : "right"
		});
		sw.Promed.swSelectUslugaComplexWindow.superclass.initComponent.apply(this, arguments);
	} //end initComponent()
});