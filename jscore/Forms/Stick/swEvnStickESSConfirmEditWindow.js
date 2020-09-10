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

sw.Promed.swEvnStickESSConfirmEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnStickESSConfirmEditWindow',
	objectSrc: '/jscore/Forms/Stick/swEvnStickESSConfirmEditWindow.js',
	collapsible: false,
	draggable: true,
	autoHeight: true,
	id: 'StickCauseDelSelectWindow',
    buttonAlign: 'left',
    closeAction: 'hide',
	modal: true,
	resizable: false,
	plain: true,
	width: 400,
    title: 'Согласие на получение ЭЛН',
    callback: Ext.emptyFn,
	show: function() {
        sw.Promed.swEvnStickESSConfirmEditWindow.superclass.show.apply(this, arguments);

        this.callback = Ext.emptyFn;
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
			Ext.getCmp(this.id + 'SaveAndPrint').setText('Сохранить и напечатать');
		} else {
			Ext.getCmp(this.id + 'SaveAndPrint').setText('Сохранить');
		}
        return true;
	},
    doSelect: function(){
		var base_form = this.FormPanel.getForm();
		var evnstickbase_consentdt = base_form.findField('EvnStickBase_consentDT').getValue();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if (
			! Ext.isEmpty(this.EvnStick_setDate)
			&& this.EvnStick_setDate >= new Date('2019-2-22') // отключаем контроль если выдан раньше 22.02.2019
			&& ! Ext.isEmpty(base_form.findField('EvnStickBase_consentDT').getValue())
			
			&& this.EvnStick_setDate < base_form.findField('EvnStickBase_consentDT').getValue()
		) {
			sw.swMsg.alert(langs('Ошибка'), 'Согласие не может быть получено после выдачи ЛВН.');
			return false;
		}

		// if(evnstickbase_consentdt < this.EvnStick_setDate || evnstickbase_consentdt > parseDate(getGlobalOptions().date, 'd.m.Y').add(Date.DAY, 2)) {
		// 	sw.swMsg.show({
		// 		buttons: Ext.Msg.OK,
		// 		icon: Ext.Msg.WARNING,
		// 		msg: 'Дата согласия на получение электронного листа нетрудоспособности должна быть не меньше даты выдачи ЛВН и не более 2 дней от текущей даты',
		// 		title: ERR_INVFIELDS_TIT
		// 	});
		// 	return false;
		// }

        this.callback(evnstickbase_consentdt);
        this.hide();
        return true;
    },
	initComponent: function() {
        var thas = this;
        var win = this;
        this.FormPanel = new sw.Promed.FormPanel({
			autoHeight: true,
			layout: 'form',
			frame: true,
			items: [{
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: 'Дата согласия',
				format: 'd.m.Y',
				name: 'EvnStickBase_consentDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}]
        });

    	Ext.apply(this, {
			buttonAlign: "right",
			buttons: [{
				handler: function() {
					thas.doSelect();
				},
				id: win.id + 'SaveAndPrint',
				iconCls: 'ok16',
				text: 'Сохранить и напечатать'
			}, {
				text: '-'
			},
			{
				handler: function() {
					thas.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
            border: false,
			layout: 'form',
			items: [
                this.FormPanel
            ]
		});
		sw.Promed.swEvnStickESSConfirmEditWindow.superclass.initComponent.apply(this, arguments);
	}
});