/**
* swEvnUslugaSetWindow - окно выбора типа услуги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-21.07.2009
* @comment      Префикс для id компонентов EUSF (EvnUslugaSetForm)
*/

sw.Promed.swEvnUslugaSetWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'EvnUslugaSetWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.openEvnUslugaWindow();
				}.createDelegate(this),
				iconCls: 'ok16',
				onShiftTabAction: function() {
					this.findById('EUSF_UslugaClassCombo').focus(true);
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: 3052,
				text: lang['ok']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('EUSF_UslugaClassCombo').focus(true);
				}.createDelegate(this),
				tabIndex: 3053,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.Panel({
				border: false,
				frame: true,
				id: 'EvnUslugaSetForm',
				items: [{
					allowBlank: false,
					autoLoad: false,
					comboSubject: 'UslugaClass',
					fieldLabel: lang['tip_uslugi'],
					hiddenName: 'UslugaClass_id',
					id: 'EUSF_UslugaClassCombo',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					tabIndex: 3051,
					typeCode: 'int',
					width: 350,
					xtype: 'swcommonsprcombo'
				}],
				labelAlign: 'right',
				labelWidth: 100,
				layout: 'form'
			})]
		});
		sw.Promed.swEvnUslugaSetWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	openEvnUslugaWindow: function() {
		var evn_usluga_edit_window = null;

		var usluga_class_combo = this.findById('EUSF_UslugaClassCombo');
		var usluga_class_id = usluga_class_combo.getValue();

		if ( !usluga_class_id ) {
			return false;
		}

		var record = usluga_class_combo.getStore().getById(usluga_class_id);

		if ( !record ) {
			return false;
		}

        sw.Promed.UslugaClass.onSelectCode(
            record.get('UslugaClass_Code'),
            this.params,
            this.EvnUsluga_rid,
            function() { this.hide(); }.createDelegate(this)
        );
		this.hide();
	},
	params: null,
	parentEvent: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnUslugaSetWindow.superclass.show.apply(this, arguments);

		this.EvnUsluga_rid = null;
		this.onHide = Ext.emptyFn;
		this.params = null;
		this.parentEvent = null;

		if ( !arguments[0] || !arguments[0].EvnUsluga_rid || !arguments[0].params || !arguments[0].parentEvent ) {
			sw.swMsg.alert(lang['soobschenie'], lang['neverno_zadanyi_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.EvnUsluga_rid = arguments[0].EvnUsluga_rid;
		this.params = arguments[0].params;
		this.parentEvent = arguments[0].parentEvent;
		this.MorbusType_SysNick = arguments[0].MorbusType_SysNick || null;

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		var usluga_class_combo = this.findById('EUSF_UslugaClassCombo');

		usluga_class_combo.clearValue();
		usluga_class_combo.getStore().removeAll();

        var where = sw.Promed.UslugaClass.getWhereClause(this.parentEvent, this.MorbusType_SysNick);
        if (!where) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernoe_znachenie_parametra_parentevent'], function() { this.hide(); }.createDelegate(this) );
        } else {
			usluga_class_combo.focus(true, 250);//
            usluga_class_combo.getStore().load({
                params: {
                    where: where
                },
                callback: function(r,o,s){
                    if ( usluga_class_combo.getStore().getCount() > 0 ) {
                        usluga_class_combo.setValue(usluga_class_combo.getStore().getAt(0).get('UslugaClass_id'));
                    }
                    //usluga_class_combo.focus(true, 250);
                }
            });
        }
	},
	title: WND_POL_EUSETTYPE,
	width: 500
});