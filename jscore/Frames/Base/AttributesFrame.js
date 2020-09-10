sw.Promed.AttributeProcessing = {
	checkAttributeVision: function(attribute) {
		if (!attribute) {return true;}
		var global_options = getGlobalOptions();
		var ch1 = (Ext.isEmpty(attribute.Org_id) || attribute.Org_id == global_options.org_id);
		var ch2 = (Ext.isEmpty(attribute.Region_id) || attribute.Region_id == global_options.region.number);
		var ch3 = (attribute.AttributeVision_inDate ? true : false);

		return (ch1 && ch2 && ch3);
	},

	processFields: function(attributes, options) {
		options = Ext.applyIf(options || {}, {
			singleAttributeField: false
		});

		var fields = [];
		attributes.forEach(function(attribute){
			var obj = {};
			if (!Ext.isEmpty(attribute.AttributeVision_AppCode)) {
				eval('obj={'+attribute.AttributeVision_AppCode+'}');
			}
			var allowedFnList = ['afterProcess', 'afterRender', 'afterInit', 'afterLoad', 'afterSave'];

			allowedFnList.forEach(function(fnName){
				attribute[fnName] = (typeof obj[fnName] == 'function') ? obj[fnName] : Ext.emptyFn;
			});

			var field = {};
			switch (attribute.AttributeValueType_SysNick) {
				case 'string':
					field = {
						xtype: 'textfield',
						name: attribute.Attribute_SysNick,
						fieldLabel: attribute.Attribute_Name,
						attributeObject: attribute.AttributeVision_TableName,
						hidden: this.checkAttributeVision(attribute),
						attribute: attribute
					};
					break;
				case 'int':
				case 'float':
				case 'money':
					field = {
						xtype: 'numberfield',
						allowDecimal: (attribute.AttributeValueType_SysNick!='int'),
						name: attribute.Attribute_SysNick,
						fieldLabel: attribute.Attribute_Name,
						attributeObject: attribute.AttributeVision_TableName,
						hidden: this.checkAttributeVision(attribute),
						attribute: attribute,
						listeners: {
							'render': function(field) {
								field.attribute.afterRender(field, this);
							}.createDelegate(this)
						}
					};
					if (attribute.AttributeValueType_SysNick == 'money') {
						field['decimalPrecision'] = 2;
					}
					break;
				case 'date':
					field = {
						xtype: 'swdatefield',
						name: attribute.Attribute_SysNick,
						fieldLabel: attribute.Attribute_Name,
						attributeObject: attribute.AttributeVision_TableName,
						hidden: this.checkAttributeVision(attribute),
						attribute: attribute,
						listeners: {
							'render': function(field) {
								field.attribute.afterRender(field, this);
							}.createDelegate(this)
						}
					};
					break;
				case 'bool':
					field = {
						xtype: 'checkbox',
						name: attribute.Attribute_SysNick,
						fieldLabel: attribute.Attribute_Name,
						attributeObject: attribute.AttributeVision_TableName,
						hidden: this.checkAttributeVision(attribute),
						attribute: attribute,
						listeners: {
							'render': function(field) {
								field.attribute.afterRender(field, this);
							}.createDelegate(this)
						}
					};
					break;
				case 'ident':
					var comboSubject = '',
						prefix = '',
						tableNameArr;

					switch (true){
						case !Ext.isEmpty(attribute.Attribute_TableName):
							tableNameArr = attribute.Attribute_TableName.split('.');
							break;
						case !Ext.isEmpty(attribute.AttributeVision_TableName):
							tableNameArr = attribute.AttributeVision_TableName.split('.');
							break;
					}

					if (tableNameArr.length == 2) {
						comboSubject = tableNameArr[1];
						if (tableNameArr[0] != 'dbo') {
							prefix = tableNameArr[0]+'_';
						}
					} else if (tableNameArr.length == 1) {
						comboSubject = tableNameArr[0];
					}

					field = {
						xtype: 'swcommonsprcombo',
						prefix: prefix,
						comboSubject: comboSubject,
						hiddenName: attribute.Attribute_SysNick,
						fieldLabel: attribute.Attribute_Name,
						hidden: this.checkAttributeVision(attribute),
						attribute: attribute,
						width: 360,
						listeners: {
							'render': function(field) {
								if (field.hidden) {
									field.hideContainer();
								}
								field.attribute.afterRender(field, this);
							}.createDelegate(this)
						}
					};
					break;
				case 'baseident':
					field = {
						xtype: 'swtabledirectcombo',
						TableDirectInfo_id: attribute.Attribute_TablePKey,
						hiddenName: attribute.Attribute_SysNick,
						fieldLabel: attribute.Attribute_Name,
						hidden: this.checkAttributeVision(attribute),
						attribute: attribute,
						listeners: {
							'render': function(field) {
								if (field.hidden) {
									field.hideContainer();
								}
								field.attribute.afterRender(field, this);
							}.createDelegate(this)
						}
					};
					break;

				default: return;
			}
			if (options.singleAttributeField) {
				if (attribute.AttributeValueType_SysNick.inlist(['ident','baseident'])) {
					field.hiddenName = 'AttributeValue_Value';
				} else {
					field.name = 'AttributeValue_Value';
				}
				field.fieldLabel = lang['znachenie'];
			}
			if (!Ext.isEmpty(options.allowBlank)) {
				field.allowBlank = options.allowBlank;
			}
			if (!Ext.isEmpty(options.width)) {
				field.width = options.width;
			}
			if (!Ext.isEmpty(options.disabled)) {
				field.disabled = options.disabled;
			}

			switch(attribute.Attribute_SysNick) {
				case 'EKOBegDT':
				case 'EKOEndDT':
				case 'EKOKolEmbr':
				case 'EKOEmbrKriokons':
				case 'EKOConfPregn':
					field.allowBlank = getRegionNick() != 'perm';
					break;
			}

			switch(attribute.Attribute_SysNick) {
				case 'EKOKolEmbr':
					field.minValue = 1;
					field.maxValue = 10;
					break;
			}

			field.attribute.afterProcess(field, this);

			fields.push(field);
		}.createDelegate(this));
		return fields;
	}
};

sw.Promed.AttributesFrame = function(config)
{
	Ext.apply(this, config);
	sw.Promed.AttributesFrame.superclass.constructor.call(this);
};

Ext.extend(sw.Promed.AttributesFrame, Ext.Panel, {
	title: '',
	layout: 'form',
	formPanel: null,
	object: '',
	identField: '',
	fieldWidth: null,
	readOnly: false,
	denyAutoSubmit: false,
	attributes: [],

	setAttributes: function(attributes, initForm) {
		var opt = {};
		opt.initForm = (initForm == undefined)?true:initForm;

		this.attributes = attributes;
		this.initFields(opt);
	},

	getSaveParams: function() {
		var params = [];
		this.items.each(function(field){
			var attribute = field.attribute;
			var value = Ext.isEmpty(field.getValue()) ? null : field.getValue();
			if (value != attribute.AttributeValue_Value) {
				params.push({
					Attribute_id: attribute.Attribute_id,
					Attribute_SysNick: attribute.Attribute_SysNick,
					AttributeValue_id: attribute.AttributeValue_id,
					AttributeValue_Value: value,
					AttributeValueType_SysNick: attribute.AttributeValueType_SysNick,
					AttributeValue_TableName: attribute.AttributeVision_TableName,
					AttributeVision_id: attribute.AttributeVision_id,
					identField: attribute.Attribute_IdentField
				});
			}
		}.createDelegate(this));

		return params;
	},

	appendTableDirectData: function(tableDirectData) {
		this.items.each(function(f){
			if (f.attribute.AttributeValueType_SysNick == 'baseident' && f.TableDirectInfo_id) {
				f.getStore().loadData(tableDirectData[f.TableDirectInfo_id]);
			}
		});
	},

	clearAttributes: function() {
		this.attributes = [];
		this.items.each(function(item) {
			if (this.formPanel) {
				this.formPanel.getForm().items.removeKey(item.id);
			}
			item.getEl().up('.x-form-item').remove();
		}.createDelegate(this));
		this.removeAll(true);
	},

	checkAttributeVisionByName: function(name) {
		var attribute = null;
		this.attributes.forEach(function(item) {
			if (item.Attribute_SysNick == name) {
				attribute = item;
				return false;
			}
		});
		return this.checkAttributeVision(attribute);
	},

	initFields: function(opt) {
		var fields = this.processFields(this.attributes, {
			width: this.fieldWidth,
			disabled: this.readOnly
		});
		if (fields.length > 0) {
			Ext.apply(this, {items: fields});
			this.initComponent();

			if (opt && opt.initForm && this.formPanel) {
				this.formPanel.initFields();
			}
			//this.doLayout();

			this.onInitFields();
		}
	},

	onInitFields: function() {
		this.items.each(function(field){
			//Загружаем сторы у комбобоксов
			if (field.attribute.AttributeValueType_SysNick.inlist(['ident'])) {
				field.getStore().load();
			}

			field.attribute.afterInit(field, this);
		}.createDelegate(this));
	},

	afterLoad: function() {
		this.items.each(function(field){
			field.attribute.afterLoad(field, this);
		}.createDelegate(this));
	},

	afterSave: function(action) {
		this.items.each(function(field){
			field.attribute.afterSave(field, this);
		}.createDelegate(this));
	},

	initComponent: function() {
		Ext.applyIf(this, sw.Promed.AttributeProcessing);

		sw.Promed.AttributesFrame.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swattributespanel', sw.Promed.AttributesFrame);


sw.Promed.AttributesBySignFrame = function(config)
{
	Ext.apply(this, config);
	sw.Promed.AttributesBySignFrame.superclass.constructor.call(this);
};

Ext.extend(sw.Promed.AttributesBySignFrame, sw.Promed.AttributesFrame, {
	AttributeSign_id: null,

	getSaveParams: function(requireValueText) {
		var params = [];
		this.items.each(function(field){
			var attribute = field.attribute;
			var value = Ext.isEmpty(field.getValue()) ? null : field.getValue();
			var status = 1;
			switch(true) {
				case Ext.isEmpty(attribute.AttributeValue_id) && Ext.isEmpty(value):
					return;
				case Ext.isEmpty(attribute.AttributeValue_id):
					status = 0; break;
				case Ext.isEmpty(value):
					status = 3; break;
				case value!=attribute.AttributeValue_Value:
					status = 2; break;
			}
			
			var AttributeValue_ValueText = null;
			if (requireValueText && typeof field.getFieldValue == 'function') {
				AttributeValue_ValueText = field.getFieldValue(field.displayField);
			}
			
			params.push({
				Attribute_id: attribute.Attribute_id,
				Attribute_SysNick: attribute.Attribute_SysNick,
				AttributeValue_id: attribute.AttributeValue_id,
				AttributeValue_Value: value,
				AttributeValue_ValueText: AttributeValue_ValueText,
				AttributeValueType_SysNick: attribute.AttributeValueType_SysNick,
				AttributeValue_TableName: attribute.AttributeVision_TableName,
				AttributeVision_id: attribute.AttributeVision_id,
				identField: attribute.Attribute_IdentField,
				RecordStatus_Code: status
			});
		}.createDelegate(this));

		return params;
	},

	loadAttributes: function(options) {
		options = Ext.applyIf(options || {}, {
			callback: Ext.emptyFn
		});
		this.clearAttributes();

		if (Ext.isEmpty(this.AttributeSign_id)) {
			return false;
		}

		var params = {AttributeSign_id: this.AttributeSign_id};

		Ext.Ajax.request({
			url: '/?c=Attribute&m=getAttributesBySign',
			params: params,
			success: function(response) {
				var attributes = Ext.util.JSON.decode(response.responseText);

				if (Ext.isArray(attributes)) {
					this.setAttributes(attributes);

					/*if (data.tableDirectData) {
						frame.appendTableDirectData(data.tableDirectData);
					}*/
				}
				this.doLayout();
				this.syncShadow();
				options.callback();
				
				var oob = Ext.getCmp("swAttributeSignValueEditWindow").FormPanel.getForm().findField('LevelROD');
				if(oob){
					oob.store.addListener('load', function() {
						var oob = Ext.getCmp("swAttributeSignValueEditWindow").FormPanel.getForm().findField('LevelROD');

						var item_3 = oob.store.getAt(3);
						var item_4 = oob.store.getAt(4);

						oob.store.remove(item_3);
						oob.store.remove(item_4);
						item_3.data.MesLevel_Name = "МПЦ";
						oob.store.add(item_3);

					}, this);
				}

				
				
			}.createDelegate(this)
		});
		return true;
	}
});
Ext.reg('swattributesbysignpanel', sw.Promed.AttributesBySignFrame);
