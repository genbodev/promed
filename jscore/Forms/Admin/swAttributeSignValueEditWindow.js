/**
 * swAttributeSignValueEditWindow - окно редактирования значений признака атрибутов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.07.2015
 */

/*NO PARSE JSON*/

sw.Promed.swAttributeSignValueEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAttributeSignValueEditWindow',
	width: 620,
	autoHeight: true,
	modal: true,
	listeners: {
		'hide': function() {
			this.findById('ASVEW_AttributesPanel').clearAttributes();
		}
	},

	doSave: function(options) {
		options = options || {};

		var base_form = this.FormPanel.getForm();
		var attributes_panel = this.findById('ASVEW_AttributesPanel');

		if ( !base_form.isValid() ){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.FormPanel.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		var saveParams = attributes_panel.getSaveParams(this.requireValueText);

		var EKOBegDT = null;
		var EKOEndDT = null;
		for(var k in saveParams) {
			if (saveParams[k].Attribute_SysNick && saveParams[k].Attribute_SysNick == 'EKOBegDT') {
				EKOBegDT = saveParams[k].AttributeValue_Value;
			} else if (saveParams[k].Attribute_SysNick && saveParams[k].Attribute_SysNick == 'EKOEndDT') {
				EKOEndDT = saveParams[k].AttributeValue_Value;
			}
		}

		if (EKOBegDT && EKOEndDT && EKOBegDT > EKOEndDT) {
			loadMask.hide();
			Ext.Msg.alert(langs('Ошибка'), langs('Дата окончания не может быть ранее, чем дата начала.'));
			return false;
		}

		var params = {};
		if (this.formMode == 'remote') {
			if (base_form.findField('AttributeSign_id').disabled) {
				params.AttributeSign_id = base_form.findField('AttributeSign_id').getValue();
			}

			params.AttributeSign_TableName = base_form.findField('AttributeSign_id').getFieldValue('AttributeSign_TableName');
			params.AttributeValueSaveParams = Ext.util.JSON.encode(saveParams);

			base_form.submit({
				params: params,
				failure: function(result_form, action)
				{
					loadMask.hide();
				}.createDelegate(this),
				success: function(result_form, action)
				{
					loadMask.hide();
					this.callback();
					this.hide();
				}.createDelegate(this)
			});
		} else {
			var lp = {};
			var key = '';

			var AttributeValueLoadParams = [];
			var AttributeValueSaveParams = saveParams;
			for(var i=0; i<AttributeValueSaveParams.length; i++) {
				key = AttributeValueSaveParams[i].Attribute_SysNick;
				lp[key] = {
					AttributeValue_id: AttributeValueSaveParams[i].AttributeValue_id || null,
					AttributeValue_Value: AttributeValueSaveParams[i].AttributeValue_Value,
					Attribute_SysNick: AttributeValueSaveParams[i].Attribute_SysNick
				};
			}
			for(key in lp) {
				AttributeValueLoadParams.push(lp[key]);
			}

			params.AttributeSignValueData = {
				AttributeSignValue_id: base_form.findField('AttributeSignValue_id').getValue(),
				AttributeSignValue_TablePKey: base_form.findField('AttributeSignValue_TablePKey').getValue(),
				AttributeSign_id: base_form.findField('AttributeSign_id').getValue(),
				AttributeSign_Code: base_form.findField('AttributeSign_id').getFieldValue('AttributeSign_Code'),
				AttributeSign_Name: base_form.findField('AttributeSign_id').getFieldValue('AttributeSign_Name'),
				AttributeSign_TableName: base_form.findField('AttributeSign_id').getFieldValue('AttributeSign_TableName'),
				RecordStatus_Code: base_form.findField('RecordStatus_Code').getValue(),
				AttributeSignValue_begDate: base_form.findField('AttributeSignValue_begDate').getValue(),
				AttributeSignValue_endDate: base_form.findField('AttributeSignValue_endDate').getValue(),
				AttributeValueLoadParams: Ext.util.JSON.encode(AttributeValueLoadParams),
				AttributeValueSaveParams: Ext.util.JSON.encode(AttributeValueSaveParams)
			};

			loadMask.hide();
			this.callback(params);
			this.hide();
		}
	},

	loadAttributeSignCombo: function(options) {
		options = Ext.applyIf(options || {}, {callback: Ext.emptyFn});

		var base_form = this.FormPanel.getForm();
		var attribute_sign_combo = base_form.findField('AttributeSign_id');
		var attribute_sign_id = attribute_sign_combo.getValue();

		attribute_sign_combo.getStore().baseParams.AttributeSign_TableName = this.tableName;
		attribute_sign_combo.getStore().baseParams.AttributeSignValue_TablePKey = base_form.findField('AttributeSignValue_TablePKey').getValue();

		if (this.UslugaComplex_Code) {
			attribute_sign_combo.getStore().baseParams.UslugaComplex_Code = this.UslugaComplex_Code;
		} else {
			attribute_sign_combo.getStore().baseParams.UslugaComplex_Code = null;
		}

		attribute_sign_combo.getStore().load({
			callback: function() {
				if (attribute_sign_combo.getStore().getCount() == 1) {
					attribute_sign_combo.disable();
					attribute_sign_combo.setValue(attribute_sign_combo.getStore().getAt(0).get('AttributeSign_id'));
				} else {
					attribute_sign_combo.enable();

					if (!Ext.isEmpty(attribute_sign_id)) {
						var index = attribute_sign_combo.getStore().findBy(function(rec) { return rec.get('AttributeSign_id') == attribute_sign_id; });
						if (index >= 0) {
							attribute_sign_combo.setValue(attribute_sign_id);
						} else {
							attribute_sign_combo.setValue(null);
						}
					}
				}
				options.callback();
			}
		});
	},

	show: function() {
		sw.Promed.swAttributeSignValueEditWindow.superclass.show.apply(this, arguments);

		this.formMode = 'remote';
		this.callback = Ext.emptyFn;
		this.tableName = null;
		this.AttributeValueLoadParams = [];
		this.hideDates = false;

		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var attributes_panel = this.findById('ASVEW_AttributesPanel');

		base_form.reset();

		if (!arguments[0] || !arguments[0].action || !arguments[0].formParams) {
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}

		if (arguments[0].formMode && arguments[0].formMode == 'local') {
			this.formMode = 'local';
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0].UslugaComplex_Code) {
			this.UslugaComplex_Code = arguments[0].UslugaComplex_Code;
		} else {
			this.UslugaComplex_Code = null;
		}

		if (arguments[0].AttributeSign_TableName) {
			this.tableName = arguments[0].AttributeSign_TableName;
		}

		if (arguments[0].AttributeValueLoadParams) {
			this.AttributeValueLoadParams = Ext.util.JSON.decode(arguments[0].AttributeValueLoadParams);
		}

		if (arguments[0].hideDates) {
			this.hideDates = arguments[0].hideDates;
		}

		if (this.hideDates) {
			base_form.findField('AttributeSignValue_begDate').hideContainer();
			base_form.findField('AttributeSignValue_begDate').setAllowBlank(true);
			base_form.findField('AttributeSignValue_endDate').hideContainer();
		} else {
			base_form.findField('AttributeSignValue_begDate').showContainer();
			base_form.findField('AttributeSignValue_begDate').setAllowBlank(false);
			base_form.findField('AttributeSignValue_endDate').showContainer();
		}

		this.action = arguments[0].action;

		base_form.setValues(arguments[0].formParams);

		base_form.items.each(function(f){f.validate();});

		this.requireValueText = arguments[0].requireValueText;

		var attribute_sign_combo = base_form.findField('AttributeSign_id');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				wnd.setTitle(lang['znachenie_priznaka_atributa_dobavlenie']);
				wnd.enableEdit(true);
				attributes_panel.readOnly = false;
				loadMask.hide();

				if (getRegionNick() == 'ufa' &&
					(this.UslugaComplex_Code == 'A06.10.006' || this.UslugaComplex_Code == 'A06.10.006.002'))
					attribute_sign_combo.setValue(15);  // Отказ от ЧКВ

				wnd.loadAttributeSignCombo({
					callback: function() {
						if (!Ext.isEmpty(attribute_sign_combo.getValue())) {
							attributes_panel.AttributeSign_id = base_form.findField('AttributeSign_id').getValue();
							attributes_panel.loadAttributes({
								callback: function() {
									base_form.items.each(function(f){f.validate()});
								}
							});
						}
					}
				});
				break;

			case 'edit':
			case 'view':
				if (this.action=='edit') {
					this.setTitle(lang['znachenie_priznaka_atributa_redaktirovanie']);
					this.enableEdit(true);
					attributes_panel.readOnly = false;
				} else {
					this.setTitle(lang['znachenie_priznaka_atributa_prosmotr']);
					this.enableEdit(false);
					attributes_panel.readOnly = true;
				}

				if (wnd.formMode == 'remote') {
					base_form.load({
						url: '/?c=Attribute&m=loadAttributeSignValueForm',
						params: {AttributeSignValue_id: base_form.findField('AttributeSignValue_id').getValue()},
						success: function (bf, action)
						{
							loadMask.hide();
							wnd.AttributeValueLoadParams = Ext.util.JSON.decode(action.result.data.AttributeValueData);

							wnd.loadAttributeSignCombo({
								callback: function() {
									if (!Ext.isEmpty(attribute_sign_combo.getValue())) {
										attributes_panel.AttributeSign_id = base_form.findField('AttributeSign_id').getValue();
										attributes_panel.loadAttributes({callback: function() {
											var field;
											for (var i=0; i<wnd.AttributeValueLoadParams.length; i++) {
												field = wnd.AttributeValueLoadParams[i].Attribute_SysNick;

												if (base_form.findField(field)) {
													base_form.findField(field).attribute.AttributeValue_id = wnd.AttributeValueLoadParams[i].AttributeValue_id;
													base_form.findField(field).attribute.RecordStatus_Code = wnd.AttributeValueLoadParams[i].RecordStatus_Code;
													base_form.findField(field).attribute.AttributeValue_Value = wnd.AttributeValueLoadParams[i].AttributeValue_Value;
													base_form.findField(field).setValue(wnd.AttributeValueLoadParams[i].AttributeValue_Value);
												}
											}
											base_form.items.each(function(f){f.validate()});
										}});
									}
								}
							});
						}.createDelegate(this),
						failure: function (form,action)
						{
							loadMask.hide();
							Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
						}.createDelegate(this)
					});
				} else {
					loadMask.hide();
					wnd.loadAttributeSignCombo({
						callback: function() {
							if (!Ext.isEmpty(attribute_sign_combo.getValue())) {
								attributes_panel.AttributeSign_id = base_form.findField('AttributeSign_id').getValue();
								attributes_panel.loadAttributes({callback: function() {
									var field;
									for (var i=0; i<wnd.AttributeValueLoadParams.length; i++) {
										field = wnd.AttributeValueLoadParams[i].Attribute_SysNick;

										if (base_form.findField(field)) {
											base_form.findField(field).attribute.AttributeValue_id = wnd.AttributeValueLoadParams[i].AttributeValue_id;
											if (field.includes("DT") && !Ext.isDate(wnd.AttributeValueLoadParams[i].AttributeValue_Value)) {
												wnd.AttributeValueLoadParams[i].AttributeValue_Value = wnd.AttributeValueLoadParams[i].AttributeValue_Value.split("T")[0];
											}
											base_form.findField(field).attribute.AttributeValue_Value = wnd.AttributeValueLoadParams[i].AttributeValue_Value;
											base_form.findField(field).attribute.RecordStatus_Code = wnd.AttributeValueLoadParams[i].RecordStatus_Code;
											base_form.findField(field).setValue(wnd.AttributeValueLoadParams[i].AttributeValue_Value);
										}
									}
									base_form.items.each(function(f){f.validate()});
								}});
							}
						}
					});
				}
				break;
		}
	},

	initComponent: function() {
		var wnd = this;

		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'ASVEW_FormPanel',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 180,
			url: '/?c=Attribute&m=saveAttributeSignValue',
			items: [{
				xtype: 'hidden',
				name: 'AttributeSignValue_id'
			}, {
				xtype: 'hidden',
				name: 'RecordStatus_Code'
			}, {
				xtype: 'hidden',
				name: 'AttributeSignValue_TablePKey'
			}, {
				allowBlank: false,
				xtype: 'swattributesigncombo',
				name: 'AttributeSign_id',
				listeners: {
					'select': function(cmb, record)
					{
						cmb._loadAttributes(record.get('AttributeSign_id'));
					},
					'change': function(cmb, newValue)
					{
						if (!newValue)
							cmb._loadAttributes(newValue);
					}

				},
				width: 360,
				_loadAttributes: function(AttributeSign_id)
				{
					var base_form = this.FormPanel.getForm();
					var attributes_panel = this.findById('ASVEW_AttributesPanel');

					attributes_panel.AttributeSign_id = AttributeSign_id;
					attributes_panel.loadAttributes({callback: function() {
						base_form.items.each(function(f){
							if (f.name != 'AttributeSignValue_begDate')
								f.validate()
						});
						wnd.syncSize();
						wnd.syncShadow();
					}});
				}.createDelegate(this)
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						allowBlank: false,
						xtype: 'swdatefield',
						name: 'AttributeSignValue_begDate',
						fieldLabel: lang['nachalo'],
						width: 100
					}]
				}, {
					layout: 'form',
					labelWidth: 80,
					items: [{
						xtype: 'swdatefield',
						name: 'AttributeSignValue_endDate',
						fieldLabel: lang['okonchanie'],
						width: 100
					}]
				}]
			}, {
				id: 'ASVEW_AttributesPanel',
				xtype: 'swattributesbysignpanel',
				formPanel: this.FormPanel,
				denyAutoSubmit: true
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'AttributeSignValue_id'},
				{name: 'RecordStatus_Code'},
				{name: 'AttributeSignValue_TablePKey'},
				{name: 'AttributeSign_id'},
				{name: 'AttributeSignValue_begDate'},
				{name: 'AttributeSignValue_endDate'},
				{name: 'AttributeValueData'}
			])
		});

		Ext.apply(this,
		{
			buttons: [
			{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'ASVEW_SaveButton',
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [this.FormPanel]
		});

		sw.Promed.swAttributeSignValueEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
