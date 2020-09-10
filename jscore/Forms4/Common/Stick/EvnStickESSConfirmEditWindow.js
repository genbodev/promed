/**
 * swEvnStickESSConfirmEditWindow - Форма согласия на получение ЭЛН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2017 Swan Ltd.
 */
Ext6.define('common.Stick.EvnStickESSConfirmEditWindow', {
	alias: 'widget.EvnStickESSConfirmEditWindow',
	extend: 'base.BaseForm',
	
	addCodeRefresh: Ext6.emptyFn,
	closeToolText: 'Закрыть',
	maximized: false,
	width: 400,
	modal: true,
	cls: 'arm-window-new emk-forms-window person-disp-diag-edit-window lln-add-argee-window',
	renderTo: Ext6.getBody(),
	
	modal: true,
	resizable: false,
	title: 'Согласие на получение ЭЛН',
	show: function() {
		var win = this;
		this.callParent(arguments);

		this.callback = Ext6.emptyFn;
		if (typeof arguments[0].callback == 'function') {
			this.callback = arguments[0].callback;
		}

		this.EvnStickBase_consentDT = getGlobalOptions().date;
		this.EvnStick_setDate = null;
		this.EvnStick_disDate = null;
		
		if( arguments[0].allowPrint ) {
			this.allowPrint = arguments[0].allowPrint;
		} else {
			this.allowPrint = false;
		}
		if (arguments[0].EvnStickBase_consentDT) {
			this.EvnStickBase_consentDT = arguments[0].EvnStickBase_consentDT;
		}
		if (arguments[0].EvnStick_setDate) {
			this.EvnStick_setDate = arguments[0].EvnStick_setDate;
		}
		if (arguments[0].EvnStick_disDate) {
			this.EvnStick_disDate = arguments[0].EvnStick_disDate;
		}

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		base_form.findField('EvnStickBase_consentDT').setValue(this.EvnStickBase_consentDT);

		if(this.allowPrint) {
			win.queryById('SaveAndPrint').setText('Сохранить и напечатать');
		} else {
			win.queryById('SaveAndPrint').setText('Сохранить');
		}
		return true;
	},
	doSave: function(){
		var base_form = this.FormPanel.getForm();
		var evnstickbase_consentdt = base_form.findField('EvnStickBase_consentDT').getValue();

		if ( !base_form.isValid() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if (
			! Ext6.isEmpty(this.EvnStick_setDate)
			&& this.EvnStick_setDate >= new Date('2019-2-22') // отключаем контроль если выдан раньше 22.02.2019
			&& ! Ext6.isEmpty(base_form.findField('EvnStickBase_consentDT').getValue())
			&& this.EvnStick_setDate < base_form.findField('EvnStickBase_consentDT').getValue()
		) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Согласие не может быть получено после выдачи ЛВН.'));
			return false;
		}
		/*
		if(evnstickbase_consentdt < this.EvnStick_setDate || evnstickbase_consentdt > parseDate(getGlobalOptions().date, 'd.m.Y').add(Date.DAY, 2)) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING,
				msg: 'Дата согласия на получение электронного листа нетрудоспособности должна быть не меньше даты выдачи ЛВН и не более 2 дней от текущей даты',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}*/

		this.callback(evnstickbase_consentdt);
		this.hide();
		return true;
	},
	initComponent: function() {
		var thas = this;
		
		this.FormPanel = new Ext6.form.FormPanel({
			padding: '20 20 20 32',
			border: false,
			items: [{
				allowBlank: false,
				//~ enableKeyEvents: true,
				fieldLabel: 'Дата согласия',
				labelWidth: 115,
				format: 'd.m.Y',
				name: 'EvnStickBase_consentDT',
				plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
				width: 115+117,
				xtype: 'datefield'
			}]
		});

		Ext6.apply(this, {
			//~ buttonAlign: "right",
			
			buttons: [{
				xtype: 'SimpleButton'
			}, {
				text: 'Сохранить и напечатать',
				itemId: 'SaveAndPrint',
				xtype: 'SubmitButton'
			}],
			border: false,
			//~ layout: 'border',
			items: [
				this.FormPanel
			]
		});
		this.callParent(arguments);
	}
});