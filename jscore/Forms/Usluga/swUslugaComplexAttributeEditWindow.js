/**
* swUslugaComplexAttributeEditWindow - редактирование атрибута услуги
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      16.07.2012
* @comment      Префикс для id компонентов UCAEW (UslugaComplexAttributeEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swUslugaComplexAttributeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaComplexAttributeEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swUslugaComplexAttributeEditWindow.js',

	action: null,
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
		
		// this.getLoadMask(LOAD_WAIT_SAVE).show();

		var data = new Object();
		
		var AttributeValueType_id = base_form.findField('UslugaComplexAttributeType_id').getFieldValue('AttributeValueType_id');
		var UslugaComplexAttribute_Value = "";
		
		switch (AttributeValueType_id) {
			case 1:
				UslugaComplexAttribute_Value = base_form.findField('UslugaComplexAttribute_Int').getValue();
				break;
			case 2:
				UslugaComplexAttribute_Value = base_form.findField('UslugaComplexAttribute_Float').getValue();
				break;
			case 6:
				UslugaComplexAttribute_Value = this.sprCmp.getRawValue();
				break;
			default:
				UslugaComplexAttribute_Value = base_form.findField('UslugaComplexAttribute_Text').getValue();
				break;
		}
		
		base_form.findField('UslugaComplexAttribute_Value').setValue(UslugaComplexAttribute_Value);
						
		data.uslugaComplexAttributeData = {
			'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
			'UslugaComplexAttributeType_id': base_form.findField('UslugaComplexAttributeType_id').getValue(),
			'UslugaComplexAttributeType_Name': base_form.findField('UslugaComplexAttributeType_id').getFieldValue('UslugaComplexAttributeType_Name'),
			'UslugaComplexAttribute_id': base_form.findField('UslugaComplexAttribute_id').getValue(),
			'UslugaComplexAttribute_Value': base_form.findField('UslugaComplexAttribute_Value').getValue(),
			'UslugaComplexAttribute_Int': base_form.findField('UslugaComplexAttribute_Int').getValue(),
			'UslugaComplexAttribute_Float': base_form.findField('UslugaComplexAttribute_Float').getValue(),
			'UslugaComplexAttribute_Text': base_form.findField('UslugaComplexAttribute_Text').getValue(),
			'UslugaComplexAttribute_DBTableID': this.sprCmp?this.sprCmp.getValue():null,
			'AttributeValueType_Name': base_form.findField('AttributeValueType_id').getFieldValue('AttributeValueType_Name'),
			'UslugaComplexAttribute_begDate': base_form.findField('UslugaComplexAttribute_begDate').getValue(),
			'UslugaComplexAttribute_endDate': base_form.findField('UslugaComplexAttribute_endDate').getValue(),
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue(),
			'pmUser_Name': getGlobalOptions().pmuser_name
		};
		
		log(data);
		
		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				this.getLoadMask().hide();
				
				this.callback(data);
				this.hide();
			break;

			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						this.getLoadMask().hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					success: function(result_form, action) {
						this.formStatus = 'edit';
						this.getLoadMask().hide();

						if ( action.result && action.result.UslugaComplexAttribute_id > 0 ) {
							base_form.findField('UslugaComplexAttribute_id').setValue(action.result.UslugaComplexAttribute_id);
							data.uslugaComplexAttributeData.UslugaComplexAttribute_id = base_form.findField('UslugaComplexAttribute_id').getValue();

							this.callback(data);
							this.hide();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	draggable: true,
	formStatus: 'edit',
	id: 'UslugaComplexAttributeEditWindow',
	sprCmp: null,
	onChangeAttributeValueType: function(AttributeValueType_id, UslugaComplexAttributeType_DBTable) {
		var win = this;
		var base_form = this.FormPanel.getForm();

		this.sprCmp = null;

		win.sprPanel.items.each(function(item) {
			var itemParentNode = false;
			if (item.el.dom.parentNode && item.el.dom.parentNode.parentNode && item.el.dom.parentNode.parentNode.parentNode) {
				itemParentNode = item.el.dom.parentNode.parentNode.parentNode;
			}
			win.sprPanel.remove(item); // auto destroy child item
			if (itemParentNode) {
				Ext.fly(itemParentNode).remove(); // remove container element
			}
		});
		// win.sprPanel.removeAll();

		base_form.findField('AttributeValueType_id').setValue(AttributeValueType_id);

		base_form.findField('UslugaComplexAttribute_Int').hideContainer();
		base_form.findField('UslugaComplexAttribute_Float').hideContainer();
		base_form.findField('UslugaComplexAttribute_Text').hideContainer();

		switch (AttributeValueType_id) {
			case 1:
				base_form.findField('UslugaComplexAttribute_Int').showContainer();
				break;
			case 2:
				base_form.findField('UslugaComplexAttribute_Float').showContainer();
				break;
			case 6:
				// надо создать комбо со справочником из UslugaComplexAttributeType_DBTable
				this.sprCmp = new sw.Promed.SwCommonSprCombo({
					fieldLabel: lang['znachenie'],
					width: 300,
					lastQuery: '',
					comboSubject: UslugaComplexAttributeType_DBTable,
					value: base_form.findField('UslugaComplexAttribute_DBTableID').getValue(),
					tabIndex: TABINDEX_UCAEW + 1
				});
				this.sprPanel.add(this.sprCmp);
				this.sprCmp.getStore().load({callback: function(){
					if (UslugaComplexAttributeType_DBTable == 'MesAgeGroup') {
						win.sprCmp.getStore().filterBy(function(rec){
							return rec.get('MesAgeGroup_Code').inlist(['1', '2']);
						});
					}
				}});
				this.FormPanel.doLayout();

				break;
			default:
				base_form.findField('UslugaComplexAttribute_Text').showContainer();
				break;
		}
	},
	initComponent: function() {
		var win = this;

		this.sprPanel = new sw.Promed.Panel({
			border: false,
			layout: 'form',
			frame: false,
			labelWidth: 160
		});

		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'UslugaComplexAttributeEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'UslugaComplex_id' },
				{ name: 'UslugaComplexAttribute_id' },
				{ name: 'UslugaComplexAttributeType_id' },
				{ name: 'UslugaComplexAttribute_Int' },
				{ name: 'UslugaComplexAttribute_Float' },
				{ name: 'UslugaComplexAttribute_Text' },
				{ name: 'UslugaComplexAttribute_DBTableID' },
				{ name: 'UslugaComplexAttribute_Value' },
				{ name: 'UslugaComplexAttribute_begDate' },
				{ name: 'UslugaComplexAttribute_endDate' }
			]),
			url: '/?c=UslugaComplex&m=saveUslugaComplexAttribute',
			items: [{
				name: 'UslugaComplexAttribute_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaComplex_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaComplexAttribute_DBTableID',
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				hiddenName: 'UslugaComplexAttributeType_id',
                comboSubject: 'UslugaComplexAttributeType',
                fieldLabel: lang['tip_atributa'],
				typeCode: 'int',
				moreFields: [
					{name: 'AttributeValueType_id', mapping: 'AttributeValueType_id'},
					{name: 'UslugaComplexAttributeType_DBTable', mapping: 'UslugaComplexAttributeType_DBTable'},
					{name: 'UslugaComplexAttributeType_IsSet', mapping: 'UslugaComplexAttributeType_IsSet'}
				],
				listeners: {
					'beforeselect': function(combo, record) {
						win.onChangeAttributeValueType(record.get('AttributeValueType_id'), record.get('UslugaComplexAttributeType_DBTable'));
					}.createDelegate(this)
				},
				width: 300,
				tabIndex: TABINDEX_UCAEW + 0,
				xtype: 'swcommonsprcombo',
				allowBlank:false
			}, {
				name: 'UslugaComplexAttribute_Value',
				xtype: 'hidden',
				value: ''
			},
			{
				name: 'AttributeValueType_id',
                comboSubject: 'AttributeValueType',
				xtype: 'swcommonsprcombo'
			}, this.sprPanel,
			{
				fieldLabel: lang['znachenie'],
				allowDecimals:false,
				name: 'UslugaComplexAttribute_Int',
				tabIndex: TABINDEX_UCAEW + 1,
				xtype:'numberfield'
			},
			{
				fieldLabel: lang['znachenie'],
				allowDecimals:true,
				name: 'UslugaComplexAttribute_Float',
				tabIndex: TABINDEX_UCAEW + 1,
				xtype:'numberfield'
			},
			{
				fieldLabel: lang['znachenie'],
				name: 'UslugaComplexAttribute_Text',
				tabIndex: TABINDEX_UCAEW + 1,
				xtype: 'textfield'
			},
			{
				fieldLabel: lang['data_nachala'],
				name: 'UslugaComplexAttribute_begDate',
				allowBlank: false,
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UCAEW + 1,
				xtype: 'swdatefield'
			},
			{
				fieldLabel: lang['data_okonchaniya'],
				name: 'UslugaComplexAttribute_endDate',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UCAEW + 1,
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

					if ( !base_form.findField('UslugaComplexAttribute_Value').disabled ) {
						base_form.findField('UslugaComplexAttribute_Value').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_UCAEW + 2,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
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
					if ( !base_form.findField('UslugaComplexAttributeType_id').disabled ) {
						base_form.findField('UslugaComplexAttributeType_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_UCAEW + 3,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swUslugaComplexAttributeEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaComplexAttributeEditWindow');

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
	parentClass: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swUslugaComplexAttributeEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		base_form.findField('UslugaComplexAttribute_Int').hideContainer();
		base_form.findField('UslugaComplexAttribute_Float').hideContainer();
		base_form.findField('UslugaComplexAttribute_Text').hideContainer();
		base_form.findField('AttributeValueType_id').hideContainer();
		this.onChangeAttributeValueType(null, null);
				
		this.doLayout();
		this.center();
		
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'local';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		var deniedAttributeTypeList = [];
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		if ( arguments[0].deniedAttributeTypeList ) {
			deniedAttributeTypeList = arguments[0].deniedAttributeTypeList;
		}
		
		base_form.findField('UslugaComplexAttributeType_id').getStore().clearFilter();
		base_form.findField('UslugaComplexAttributeType_id').lastQuery = '';
		
		base_form.findField('UslugaComplexAttributeType_id').getStore().filterBy(function(record) {
			if (!record.get('UslugaComplexAttributeType_id').inlist(deniedAttributeTypeList) || record.get('UslugaComplexAttributeType_IsSet') == 2) {
				return true;
			} else {
				return false;
			}
		});

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		//this.getLoadMask().show();
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_USLUGA_ATTRIBUTE_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				var index = base_form.findField('UslugaComplexAttributeType_id').getStore().findBy(function (rec) {
					return (rec.get(base_form.findField('UslugaComplexAttributeType_id').valueField) == base_form.findField('UslugaComplexAttributeType_id').getValue());
				});
				
				base_form.findField('UslugaComplexAttributeType_id').fireEvent('select', base_form.findField('UslugaComplexAttributeType_id'), base_form.findField('UslugaComplexAttributeType_id').getStore().getAt(index), index);
				
				var AttributeValueType_id = base_form.findField('UslugaComplexAttributeType_id').getFieldValue('AttributeValueType_id');
				var UslugaComplexAttributeType_DBTable = base_form.findField('UslugaComplexAttributeType_id').getFieldValue('UslugaComplexAttributeType_DBTable');
				win.onChangeAttributeValueType(AttributeValueType_id, UslugaComplexAttributeType_DBTable);
				
				if ( this.action == 'edit' ) {
					this.setTitle(WND_USLUGA_ATTRIBUTE_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_USLUGA_ATTRIBUTE_VIEW);
					this.enableEdit(false);
				}

				this.getLoadMask().hide();
				base_form.clearInvalid();
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('UslugaComplexAttributeType_id').disabled ) {
			base_form.findField('UslugaComplexAttributeType_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 600
});
