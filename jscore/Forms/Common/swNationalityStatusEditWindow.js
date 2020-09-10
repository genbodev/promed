/**
 * swNationalityStatusEditWindow - окно редактирования гражданства.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
 * @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
 * @version      06.10.2010
 */

sw.Promed.swNationalityStatusEditWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'fit',
	width: 480,
	modal: true,
	resizable: false,
	draggable: false,
	autoHeight: true,
	closeAction : 'hide',
	id: 'nationality_status_edit_window',
	plain: true,
	returnFunc: function() {},
	title: lang['grajdanstvo_redaktirovanie'],
	listeners: {
		'hide': function() {
			this.onWinClose();
		}
	},
	doSave: function() {
		var base_form = this.findById('nationality_status_edit_form').getForm();
		if ( !base_form.isValid() ) {
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('NationalityStatus_IsTwoNation').focus();
				}.createDelegate(this)
			});
			return false;
		}
		var values = base_form.getValues();
		values.NationalityStatus_String = base_form.findField('KLCountry_id').getFieldValue('KLCountry_Code')+'. '+base_form.findField('KLCountry_id').getFieldValue('KLCountry_Name');
		if (base_form.findField('NationalityStatus_IsTwoNation').checked) {
			values.NationalityStatus_String += ', Двойное гражданство (РФ и иностранное государство)';
		}
		if ( this.ignoreOnClose === true )
			this.onWinClose = function() {};
		Ext.callback(this.returnFunc, this, [values]);
		this.hide();
	},
	onShowActions: function() {
		var base_form = this.findById('nationality_status_edit_form').getForm();
		base_form.reset();
		base_form.setValues(this.fields);

		if (base_form.findField('KLCountry_id').getFieldValue('KLCountry_Code') == 643) {
			base_form.findField('NationalityStatus_IsTwoNation').enable();
			base_form.findField('LegalStatusVZN_id').hideContainer();
			base_form.findField('LegalStatusVZN_id').setValue(null);
		} else {
			base_form.findField('NationalityStatus_IsTwoNation').setValue(false);
			base_form.findField('NationalityStatus_IsTwoNation').disable();
			base_form.findField('LegalStatusVZN_id').showContainer();
		}
		this.syncShadow();

		base_form.findField('KLCountry_id').focus();
	},
	show: function() {
		sw.Promed.swNationalityStatusEditWindow.superclass.show.apply(this, arguments);

		if ( arguments[0] )
		{
			if ( arguments[0].callback )
				this.returnFunc = arguments[0].callback;
			if ( arguments[0].ignoreOnClose )
				this.ignoreOnClose = arguments[0].ignoreOnClose;
			else
				this.ignoreOnClose = false;
			if ( arguments[0].fields )
				this.fields = arguments[0].fields;
			if ( arguments[0].action )
				this.action = arguments[0].action;
			if ( arguments[0].onClose )
				this.onWinClose = arguments[0].onClose;
			else
				this.onWinClose = function() {};
		}
		// если это редактирование с загрузкой данных, то загружаем данные
		if ( this.action && this.action == 'edit_with_load' )
		{
			var loadMask = new Ext.LoadMask(
				Ext.get('nationality_status_edit_window'),
				{ msg: "Подождите, идет загрузка...", removeMask: true }
			);
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=Person&m=loadNationalityStatusData',
				params: {NationalityStatus_id: this.fields.NationalityStatus_id},
				callback: function(options, success, response) {
					loadMask.hide();
					if ( response && response.responseText )
					{
						var resp = Ext.util.JSON.decode(response.responseText);
						if ( resp && resp[0] )
						{
							this.fields = resp[0];
							this.onShowActions();
						}
					}
				}.createDelegate(this)
			});
		}
		else
			this.onShowActions();
	},
	initComponent: function() {
		Ext.apply(this, {
			items: [
				new Ext.form.FormPanel({
					frame: true,
					autoHeight: true,
					labelAlign: 'right',
					id: 'nationality_status_edit_form',
					labelWidth: 94,
					buttonAlign: 'left',
					bodyStyle:'padding: 5px',
					items: [{
						allowBlank: false,
						tabIndex: TABINDEX_DEW + 1,
						xtype: 'swklcountrycombo',
						fieldLabel: lang['grajdanstvo'],
						width: 340,
						hiddenName: 'KLCountry_id',
						listeners: {
							'select': function(combo, record, index) {
								var base_form = this.findById('nationality_status_edit_form').getForm();

								if (record && record.get('KLCountry_Code') == 643) {
									base_form.findField('NationalityStatus_IsTwoNation').enable();
									base_form.findField('LegalStatusVZN_id').hideContainer();
									base_form.findField('LegalStatusVZN_id').setValue(null);
								} else {
									base_form.findField('NationalityStatus_IsTwoNation').disable();
									base_form.findField('NationalityStatus_IsTwoNation').setValue(false);
									base_form.findField('LegalStatusVZN_id').showContainer();
								}
								this.syncShadow();
							}.createDelegate(this),
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().indexOfId(newValue);
								var record = combo.getStore().getAt(index);
								combo.fireEvent('select', combo, record, index);
							}
						}
					}, {
						layout: 'form',
						items: [{
							tabIndex: TABINDEX_DEW + 2,
							xtype: 'checkbox',
							style: 'overflow: hidden',
							boxLabel: lang['dvoynoe_grajdanstvo_rf_i_inostrannoe_gosudarstvo'],
							hideLabel: true,
							name: 'NationalityStatus_IsTwoNation'
						}]
					}, {
						layout: 'form',
						items: [{
							tabIndex: TABINDEX_DEW + 3,
							xtype: 'swcommonsprcombo',
							comboSubject: 'LegalStatusVZN',
							hiddenName: 'LegalStatusVZN_id',
							fieldLabel: langs('Правовой статус нерезидента'),
							width: 340
						}]
					}],
					enableKeyEvents: true,
					keys: [{
						alt: true,
						fn: function(inp, e) {
							Ext.getCmp('nationality_status_edit_form').ownerCt.hide();
						},
						key: [ Ext.EventObject.J ],
						stopEvent: true
					}, {
						alt: true,
						fn: function(inp, e) {
							Ext.getCmp('nationality_status_edit_form').buttons[0].handler();
						},
						key: [ Ext.EventObject.C ],
						stopEvent: true
					}]
				})
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_DEW + 3,
					iconCls: 'ok16',
					handler: function() {
						this.doSave();
					}.createDelegate(this)
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_DEW + 4,
					iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			]
		});
		sw.Promed.swNationalityStatusEditWindow.superclass.initComponent.apply(this, arguments);
	}
});