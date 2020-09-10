/**
* swDateTimeSelectWindow - окно выбора даты/времени
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      08.09.2010
*
* @class        sw.Promed.swDateTimeSelectWindow
* @extends      Ext.Window
*/
sw.Promed.swDateTimeSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	doSelect: function() {
		var form = this.findById('DateTimeSelectForm');
		var base_form = form.getForm();
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.hide();
		this.onSelect(Ext.getCmp('DateTimeSelectForm').getForm().getValues(), Ext.getCmp('DateTimeSelectForm').getForm().findField('Person_Attribute').getValue());
		return true;
	},
	/**
	 * Конструктор
	 */
	initComponent: function() {
    	Ext.apply(this, {
			buttons: [{
				handler : function(button, event) {
					this.doSelect();
				}.createDelegate(this),
				iconCls : 'ok16',
				tabIndex: TABINDEX_TPSW + 3,
				text: lang['ok']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function(button, event) {
					this.hide();
				}.createDelegate(this),
				iconCls : 'cancel16',
				onShiftTabAction: function () {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.findById('DTSW_Date').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_TPSW + 3,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.FormPanel({
				autoHeight: true,
				border: false,
				frame: true,
				id: 'DateTimeSelectForm',
				labelWidth: 100,
				layout: 'form',
				style: 'padding: 3px',
				items: [{
					fieldLabel: lang['data'],
					allowBlank: false,
					name: 'Date',
					id: 'DTSW_Date',
					tabIndex: TABINDEX_TPSW + 5,
					width: 90,
					enableKeyEvents: true,
					format: 'd.m.Y',
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB && !e.shiftKey )
							{
								e.stopEvent();
								Ext.getCmp('DTSW_Time').focus(true, 100);
							}
						}
					},
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				}, {
					fieldLabel: lang['vremya'],
					allowBlank: false,
					name: 'Time',
					id: 'DTSW_Time',
					tabIndex: TABINDEX_TPSW + 1,
					width: 90,
					plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
					xtype: 'swtimefield',
					enableKeyEvents: true,
					onTriggerClick: function() {
						var base_form = this.findById('DateTimeSelectForm').getForm();
						var time_field = base_form.findField('Time');

						if ( time_field.disabled ) {
							return false;
						}

						setCurrentDateTime({
							loadMask: false,
							setDate: true,
							setDateMaxValue: true,
							setDateMinValue: false,
							setTime: true,
							timeField: time_field,
							windowId: this.id
						});
					}.createDelegate(this),	
					listeners: {
						'keypress': function(field, e) {
							if ( e.getKey() == e.TAB && e.shiftKey )
							{
								e.stopEvent();
								Ext.getCmp('DTSW_Date').focus(true, 100);
							}
						}
					}
				},
				{
					allowBlank: false,
					fieldLabel: lang['sohranyaemyiy_atribut'],
					hiddenName: 'Person_Attribute',
					width: 200,
					tabIndex: TABINDEX_TPSW + 2,
					xtype: 'combo',
					store: [
						['Person_SurName', lang['familiya']],
						['Person_SecName', lang['imya']],
						['Person_FirName', lang['otchestvo']],
						['Person_BirthDay', lang['data_rojdeniya']],
						['Person_SNILS', lang['snils']],
						['PersonSex_id', lang['pol']],
						['SocStatus_id', lang['sots_status']],
						['Federal_Num', lang['edinyiy_nomer']],
						['Polis', lang['polis']],
						['Document', lang['dokument']],
						['UAddress', lang['adres_registratsii']],
						['PAddress', lang['adres_projivaniya']],
						['Job', lang['mesto_rabotyi']]
					]
				}]
			})]
		});
		sw.Promed.swDateTimeSelectWindow.superclass.initComponent.apply(this, arguments);
	},
	modal: true,
	onSelect: Ext.emptyFn,
	plain: false,
	resizable: false,
	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swDateTimeSelectWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('DateTimeSelectForm').getForm();
		base_form.reset();

		this.onSelect = Ext.emptyFn;

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].onSelect ) {
			this.onSelect = arguments[0].onSelect;
		}				
		if ( arguments[0].selectAttribute && arguments[0].selectAttribute === true )
		{
			base_form.findField('Person_Attribute').enable();
		}
		else
		{
			base_form.findField('Person_Attribute').disable();
			if ( arguments[0].selectedAttribute )
				base_form.findField('Person_Attribute').setValue(arguments[0].selectedAttribute);
		}
		var time_field = base_form.findField('Time');
		var date_field = base_form.findField('Date');

		setCurrentDateTime({
			loadMask: false,
			setDate: true,
			setDateMaxValue: true,
			setDateMinValue: false,
			setTime: true,
			timeField: time_field,							
			dateField: date_field,
			windowId: this.id
		});
		base_form.findField('Date').focus(true, 100);
	}, //end show()
	title: lang['vyibor_datyi_i_vremeni'],
	autoWidth: true
});