/**
 * swNumeratorLinkEditWindow - окно редактирования связанного документа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 */

/*NO PARSE JSON*/

sw.Promed.swNumeratorLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swNumeratorLinkEditWindow',
	width: 700,
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

		var win = this;
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

		if (this.formMode == 'remote') {
			win.getLoadMask(LOAD_WAIT_SAVE).show();

			base_form.submit({
				failure: function (result_form, action) {
					this.formStatus = 'edit';
					win.getLoadMask().hide();
				}.createDelegate(this),
				success: function (result_form, action) {
					win.getLoadMask().hide();
					if (typeof this.callback == 'function') {
						this.callback();
					}
					this.formStatus = 'edit';
					this.hide();
				}.createDelegate(this)
			});
		} else {
			if (typeof this.callback == 'function') {
				if (base_form.findField('Record_Status').getValue() == 1) {
					base_form.findField('Record_Status').setValue(2);
				}
				var data = [{
					'NumeratorLink_id': base_form.findField('NumeratorLink_id').getValue(),
					'NumeratorObject_id': base_form.findField('NumeratorObject_id').getValue(),
					'NumeratorObject_TableName': base_form.findField('NumeratorObject_id').getFieldValue('NumeratorObject_TableName'),
					'Record_Status': base_form.findField('Record_Status').getValue()
				}];
				this.callback(data);
			}
			this.formStatus = 'edit';
			this.hide();
		}
	},
	show: function() {
		sw.Promed.swNumeratorLinkEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = win.FormPanel.getForm();

		base_form.reset();

		this.formMode = 'remote';
		if (arguments[0].formMode) {
			this.formMode = arguments[0].formMode;
		}

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		switch (this.action) {
			case 'add':
				win.getLoadMask().hide();
				win.enableEdit(true);
				win.setTitle(lang['svyazannyiy_dokument_dobavlenie']);
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					win.enableEdit(true);
					win.setTitle(lang['svyazannyiy_dokument_redaktirovanie']);
				} else {
					win.enableEdit(false);
					win.setTitle(lang['svyazannyiy_dokument_prosmotr']);
				}

				if (this.formMode == 'remote') {
					win.getLoadMask(LOAD_WAIT).show();
					base_form.load({
						failure: function () {
							win.getLoadMask().hide();
							win.hide();
						},
						url: '/?c=Numerator&m=loadNumeratorLinkEditForm',
						params: {NumeratorLink_id: base_form.findField('NumeratorLink_id').getValue()},
						success: function () {
							win.getLoadMask().hide();
						}
					});
				}

				break;
		}

		if (base_form.findField('NumeratorObject_id').disabled) {
			win.buttons[0].focus();
		} else {
			base_form.findField('NumeratorObject_id').focus();
		}
	},

	initComponent: function() {
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			url: '/?c=Numerator&m=saveNumeratorLink',
			labelWidth: 80,
			labelAlign: 'right',

			items: [{
				name: 'NumeratorLink_id',
				xtype: 'hidden'
			}, {
				name: 'Numerator_id',
				xtype: 'hidden'
			}, {
				name: 'Record_Status',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{NumeratorObject_Code}</font>&nbsp;{NumeratorObject_TableName}',
					'</div></tpl>'
				),
				store: new Ext.db.AdapterStore({
					autoLoad: false,
					dbFile: 'Promed.db',
					fields: [
						{name: 'NumeratorObject_id', mapping: 'NumeratorObject_id'},
						{name: 'NumeratorObject_Code', mapping: 'NumeratorObject_id'},
						{name: 'NumeratorObject_SchemaNam', mapping: 'NumeratorObject_SchemaName'},
						{name: 'NumeratorObject_SysName', mapping: 'NumeratorObject_SysName'},
						{name: 'NumeratorObject_TableName', mapping: 'NumeratorObject_TableName'},
					],
					key: 'NumeratorObject_id',
					sortInfo: {
						field: 'NumeratorObject_Code'
					},
					tableName: 'NumeratorObject'
				}),
				valueField: 'NumeratorObject_id',
				displayField: 'NumeratorObject_TableName',
				codeField: 'NumeratorObject_Code',
				hiddenName: 'NumeratorObject_id',
				fieldLabel: lang['dokument'],
				loadParams: 
					getRegionNick() == 'ekb'
						?{params: {where: "where NumeratorObject_SysName in ('DeathSvid', 'BirthSvid', 'PntDeathSvid', 'EvnCytologicProto', 'EvnDirectionCytologic', 'EvnHistologicProto', 'HomeVisit', 'EvnVK', 'EvnRecept', 'EvnReceptGeneral')"}} // Екб
						:{params: {where: "where NumeratorObject_SysName in ('DeathSvid', 'BirthSvid', 'PntDeathSvid', 'EvnCytologicProto', 'EvnDirectionCytologic', 'EvnDirectionMorfoHistologic', 'EvnDirectionHistologic', 'EvnHistologicProto', 'HomeVisit', 'EvnVK', 'EvnRecept', 'EvnReceptGeneral')"}}, // Остальные
				xtype: 'swbaselocalcombo',
				anchor: '-10'
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'NumeratorLink_id'},
				{name: 'Numerator_id'},
				{name: 'NumeratorObject_id'}
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
					text: lang['otmenit']
				}]
		});

		sw.Promed.swNumeratorLinkEditWindow.superclass.initComponent.apply(this, arguments);
	}
});