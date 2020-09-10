/**
* swLpuSectionMedProductTypeLinkEditWindow - редактирование мед. оборудования отделения
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      LpuStructure
* @access       public
* @copyright    Copyright (c) 2020 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      04.2020
* @comment      Префикс для id компонентов LSMPTLEW (LpuSectionMedProductTypeLinkEditWindow)
*/
sw.Promed.swLpuSectionMedProductTypeLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		// Собираем данные с формы
		var data = new Object();
		
		data.lpuSectionMedProductTypeLinkData = {
			'LpuSectionMedProductTypeLink_id': base_form.findField('LpuSectionMedProductTypeLink_id').getValue(),
			'MedProductType_id': base_form.findField('MedProductType_id').getValue(),
			'MedProductType_Name': base_form.findField('MedProductType_id').getFieldValue('MedProductType_Name'),
			'LpuSectionMedProductTypeLink_TotalAmount': base_form.findField('LpuSectionMedProductTypeLink_TotalAmount').getValue(),
			'LpuSectionMedProductTypeLink_IncludePatientKVI': base_form.findField('LpuSectionMedProductTypeLink_IncludePatientKVI').getValue(),
			'LpuSectionMedProductTypeLink_IncludeReanimation': base_form.findField('LpuSectionMedProductTypeLink_IncludeReanimation').getValue(),
			'LpuSectionMedProductTypeLink_begDT': base_form.findField('LpuSectionMedProductTypeLink_begDT').getValue(),
			'LpuSectionMedProductTypeLink_endDT': base_form.findField('LpuSectionMedProductTypeLink_endDT').getValue(),
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue()
		};

		this.formStatus = 'edit';
		loadMask.hide();

		var success = true;

		if ( typeof this.callback == 'function' ) {
			success = this.callback(data);
		}

		if ( success == true ) {
			this.hide();
		}
	},
	draggable: true,
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'LpuSectionMedProductTypeLinkEditWindow',
	initComponent: function() {
		var win = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'LpuSectionMedProductTypeLinkEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'LpuSectionMedProductTypeLink_id' },
				{ name: 'MedProductType_id' },
				{ name: 'LpuSectionMedProductTypeLink_IncludePatient' },
				{ name: 'LpuSectionMedProductTypeLink_TotalAmount' },
				{ name: 'LpuSectionMedProductTypeLink_begDT' },
				{ name: 'LpuSectionMedProductTypeLink_endDT' },
				{ name: 'RecordStatus_Code' }
			]),
			url: '/?c=LpuStructure&m=saveLpuSectionMedProductTypeLink',
			items: [{
				name: 'LpuSectionMedProductTypeLink_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				comboSubject: 'MedProductType',
				editable: true,
				fieldLabel: langs('Тип оборудования'),
				hiddenName: 'MedProductType_id',
				loadParams: {params: {where: ' where MedProductType_id in (1612, 46, 81, 87, 1147, 5306, 982, 6228, 6394, 4187, 4482, 4657, 6207, 6495, 2011, 3262, 4423, 5155, 6254, 4221, 6975, 6976)'}},
				prefix: 'passport_',
				width: 380,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: 'Общее количество',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = win.FormPanel.getForm();
						base_form.findField('LpuSectionMedProductTypeLink_IncludePatientKVI').maxValue = newValue;
						base_form.findField('LpuSectionMedProductTypeLink_IncludePatientKVI').validate();
						win.setIncludeReanimationMaxValue();
					}
				},
				minValue: 0,
				maxValue: 1000000,
				name: 'LpuSectionMedProductTypeLink_TotalAmount',
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: 'В т.ч. для пациентов с КВИ',
				listeners: {
					'change': function(field, newValue, oldValue) {
						win.setIncludeReanimationMaxValue();
					}
				},
				minValue: 0,
				maxValue: 1000000,
				name: 'LpuSectionMedProductTypeLink_IncludePatientKVI',
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: 'В т.ч. для реанимации',
				minValue: 0,
				maxValue: 1000000,
				name: 'LpuSectionMedProductTypeLink_IncludeReanimation',
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: langs('Дата начала'),
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = win.FormPanel.getForm();
						base_form.findField('LpuSectionMedProductTypeLink_endDT').setMinValue(newValue);
					}
				},
				name: 'LpuSectionMedProductTypeLink_begDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				xtype: 'swdatefield'
			}, {
				fieldLabel: langs('Дата окончания'),
				format: 'd.m.Y',
				name: 'LpuSectionMedProductTypeLink_endDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				xtype: 'swdatefield'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('LpuSectionMedProductTypeLink_endDT').disabled ) {
						base_form.findField('LpuSectionMedProductTypeLink_endDT').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				//tabIndex: TABINDEX_LSMPTLEW + 8,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this/*, TABINDEX_LSMPTLEW + 9*/),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('MedProductType_id').disabled ) {
						base_form.findField('MedProductType_id').focus(true);
					}
				}.createDelegate(this),
				//tabIndex: TABINDEX_LSMPTLEW + 10,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swLpuSectionMedProductTypeLinkEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('LpuSectionMedProductTypeLinkEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	setIncludeReanimationMaxValue: function() {
		var win = this;
		var base_form = win.FormPanel.getForm();
		
		var maxValue = base_form.findField('LpuSectionMedProductTypeLink_TotalAmount').getValue() - base_form.findField('LpuSectionMedProductTypeLink_IncludePatientKVI').getValue();
		if (Ext.isEmpty(maxValue) || maxValue < 0) {
			maxValue = 0;
		}
		
		base_form.findField('LpuSectionMedProductTypeLink_IncludeReanimation').maxValue = maxValue;
		base_form.findField('LpuSectionMedProductTypeLink_IncludeReanimation').validate();
	},
	show: function() {
		sw.Promed.swLpuSectionMedProductTypeLinkEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'local';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		this.getLoadMask().show();

		base_form.findField('LpuSectionMedProductTypeLink_begDT').setMaxValue(undefined);
		base_form.findField('LpuSectionMedProductTypeLink_begDT').setMinValue(undefined);
		base_form.findField('LpuSectionMedProductTypeLink_endDT').setMaxValue(undefined);
		base_form.findField('LpuSectionMedProductTypeLink_endDT').setMinValue(undefined);

		if ( !Ext.isEmpty(arguments[0].LpuSection_setDate) ) {
			base_form.findField('LpuSectionMedProductTypeLink_begDT').setMinValue(typeof arguments[0].LpuSection_setDate == 'object' ? Ext.util.Format.date(arguments[0].LpuSection_setDate, 'd.m.Y') : arguments[0].LpuSection_setDate);
			base_form.findField('LpuSectionMedProductTypeLink_endDT').setMinValue(typeof arguments[0].LpuSection_setDate == 'object' ? Ext.util.Format.date(arguments[0].LpuSection_setDate, 'd.m.Y') : arguments[0].LpuSection_setDate);
		}

		if ( !Ext.isEmpty(arguments[0].LpuSection_disDate) ) {
			base_form.findField('LpuSectionMedProductTypeLink_begDT').setMaxValue(typeof arguments[0].LpuSection_disDate == 'object' ? Ext.util.Format.date(arguments[0].LpuSection_disDate, 'd.m.Y') : arguments[0].LpuSection_disDate);
			base_form.findField('LpuSectionMedProductTypeLink_endDT').setMaxValue(typeof arguments[0].LpuSection_disDate == 'object' ? Ext.util.Format.date(arguments[0].LpuSection_disDate, 'd.m.Y') : arguments[0].LpuSection_disDate);
		}

		base_form.findField('LpuSectionMedProductTypeLink_TotalAmount').fireEvent('change', base_form.findField('LpuSectionMedProductTypeLink_TotalAmount'), base_form.findField('LpuSectionMedProductTypeLink_TotalAmount').getValue());
		base_form.findField('LpuSectionMedProductTypeLink_begDT').fireEvent('change', base_form.findField('LpuSectionMedProductTypeLink_begDT'), base_form.findField('LpuSectionMedProductTypeLink_begDT').getValue());

		switch ( this.action ) {
			case 'add':
				this.setTitle(langs('Медицинское оборудование в отделении: Добавление'));
				this.enableEdit(true);
			break;

			case 'edit':
				this.setTitle(langs('Медицинское оборудование в отделении: Редактирование'));
				this.enableEdit(true);
			break;

			case 'view':
				this.setTitle(langs('Медицинское оборудование в отделении: Просмотр'));
				this.enableEdit(false);
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}

		this.getLoadMask().hide();

		if ( !base_form.findField('MedProductType_id').disabled ) {
			base_form.findField('MedProductType_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 600
});