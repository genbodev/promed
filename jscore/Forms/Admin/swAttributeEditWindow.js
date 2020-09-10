/**
 * swAttributeEditWindow - окно редактирования атрибута
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.07.2014
 */

/*NO PARSE JSON*/

sw.Promed.swAttributeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAttributeEditWindow',
	width: 500,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function() {
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
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

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();
				if (typeof this.callback == 'function') {
					this.callback();
				}
				this.formStatus = 'edit';
				this.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swAttributeEditWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = form.FormPanel.getForm();

		base_form.reset();

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(form.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var Attribute_TableName_combo = base_form.findField('Attribute_TablePKey');

		Attribute_TableName_combo.getStore().load({
			callback: function() {
				Attribute_TableName_combo.setValue(Attribute_TableName_combo.getValue());
			}
		});

		switch (this.action) {
			case 'add':
				form.enableEdit(true);
				form.setTitle(lang['atributyi_dobavlenie']);
				loadMask.hide();

				base_form.findField('AttributeValueType_id').fireEvent('change', base_form.findField('AttributeValueType_id'), base_form.findField('AttributeValueType_id').getValue());

				this.syncShadow();

				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					form.enableEdit(true);
					form.setTitle(lang['atributyi_redaktirovanie']);
				} else {
					form.enableEdit(false);
					form.setTitle(lang['atributyi_prosmotr']);
				}

				base_form.load({
					failure:function () {
						//sw.swMsg.alert('Ошибка', 'Не удалось получить данные');
						loadMask.hide();
						form.hide();
					},
					url: '/?c=Attribute&m=loadAttributeForm',
					params: {Attribute_id: base_form.findField('Attribute_id').getValue()},
					success: function() {
						loadMask.hide();

						base_form.findField('AttributeValueType_id').fireEvent('change', base_form.findField('AttributeValueType_id'), base_form.findField('AttributeValueType_id').getValue());

						this.syncShadow();
					}.createDelegate(this)
				});

				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'AEW_AttributeEditForm',
			url: '/?c=Attribute&m=saveAttribute',
			labelWidth: 160,
			labelAlign: 'right',

			items: [{
				name: 'Attribute_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				name: 'Attribute_Code',
				fieldLabel: lang['kod'],
				xtype: 'numberfield',
				width: 100
			}, {
				allowBlank: false,
				name: 'Attribute_Name',
				fieldLabel: lang['naimenovanie'],
				xtype: 'textfield',
				width: 180
			}, {
				allowBlank: false,
				name: 'Attribute_SysNick',
				fieldLabel: lang['sistemnoe_naimenovanie'],
				xtype: 'textfield',
				width: 180
			}, {
				allowBlank: false,
				hiddenName: 'AttributeValueType_id',
				comboSubject: 'AttributeValueType',
				fieldLabel: lang['tip'],
				xtype: 'swcommonsprcombo',
				width: 180,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						var type = combo.getFieldValue('AttributeValueType_Code');

						if (type == 6) {
							base_form.findField('Attribute_TableName').setContainerVisible(true);
							base_form.findField('Attribute_TableName').setAllowBlank(false);
						} else {
							base_form.findField('Attribute_TableName').setContainerVisible(false);
							base_form.findField('Attribute_TableName').setValue(null);
							base_form.findField('Attribute_TableName').setAllowBlank(true);
						}
						if (type == 8) {
							base_form.findField('Attribute_TablePKey').setContainerVisible(true);
						} else {
							base_form.findField('Attribute_TablePKey').setContainerVisible(false);
							base_form.findField('Attribute_TablePKey').setValue(null);
						}

						this.syncShadow();
					}.createDelegate(this)
				}
			}, {
				allowBlank: false,
				name: 'Attribute_begDate',
				fieldLabel: lang['nachalo_deystviya'],
				xtype: 'swdatefield'
			}, {
				name: 'Attribute_endDate',
				fieldLabel: lang['okonchanie_deystviya'],
				xtype: 'swdatefield'
			}, {
				name: 'Attribute_TableName',
				fieldLabel: lang['spravochnik'],
				xtype: 'textfield',
				width: 180
			}, {
				hiddenName: 'Attribute_TablePKey',
				fieldLabel: lang['spravochnik'],
				xtype: 'swtabledirectinfocombo',
				width: 180
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'Attribute_id'},
				{name: 'Attribute_Code'},
				{name: 'Attribute_Name'},
				{name: 'Attribute_SysNick'},
				{name: 'AttributeValueType_id'},
				{name: 'Attribute_begDate'},
				{name: 'Attribute_endDate'},
				{name: 'Attribute_TableName'},
				{name: 'Attribute_TablePKey'}
			]),
			keys: [{
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					id: 'AEW_ButtonSave',
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'AEW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swAttributeEditWindow.superclass.initComponent.apply(this, arguments);
	}
});