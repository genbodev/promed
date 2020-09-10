/**
* swPersonDispFactRateEditWindow - Форма добавления/редактирования Фактических значений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2016 Swan Ltd.
* @author		Aleksandr Chebukin
* @version		24.02.2016
*/

sw.Promed.swPersonDispFactRateEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPersonDispFactRateEditWindow',
	objectSrc: '/jscore/Forms/Polka/swPersonDispFactRateEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {		
		var _this = this;
		var form = this.findById('PersonDispFactRateEditForm');
		var base_form = form.getForm();

		if ( !form.getForm().isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();		
		data.PersonDispFactRateData = {
			'PersonDispFactRate_id': base_form.findField('PersonDispFactRate_id').getValue(),
			'Rate_id': base_form.findField('Rate_id').getValue(),
			'PersonDispFactRate_setDT': base_form.findField('PersonDispFactRate_setDT').getValue(),
			'PersonDispFactRate_Value': base_form.findField('PersonDispFactRate_Value').getValue()
		};

		this.formStatus = 'edit';
		loadMask.hide();

		this.callback(data);
		this.hide();
		return true;
	},
	draggable: true,
	formStatus: 'edit',
	height: 150,
	id: 'PersonDispFactRateEditWindow',
	initComponent: function() {
		var form = this;

		form.formPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PersonDispFactRateEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'PersonDispFactRate_id' },
				{ name: 'Rate_id' },
				{ name: 'PersonDispFactRate_setDT' },
				{ name: 'PersonDispFactRate_Value' }
			]),
			region: 'center',
			url: '/?c=UslugaComplex&m=saveUslugaComplexGroup',
			items: [{
				name: 'PersonDispFactRate_id',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'Rate_id',
				value: '',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_rezultata'],
				format: 'd.m.Y',
				name: 'PersonDispFactRate_setDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: lang['fakticheskoe_znachenie'],
				format: 'd.m.Y',
				name: 'PersonDispFactRate_Value',
				width: 100,
				xtype: 'numberfield'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = form.formPanel.getForm();

					if ( form.action == 'view' ) {
						form.buttons[form.buttons.length - 1].focus(true);
					}
				},
				onTabAction: function () {
					form.buttons[1].focus(true);
				},
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(form, -1),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					form.buttons[1].focus();
				},
				onTabAction: function () {
					if ( form.action == 'edit' ) {
						form.formPanel.getForm().findField('MedProductCard_id').focus(true);
					}
					else {
						form.buttons[1].focus(true);
					}
				},
				text: BTN_FRMCANCEL
			}],
			items: [
				 form.formPanel
			],
			layout: 'border'
		});

		sw.Promed.swPersonDispFactRateEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonDispFactRateEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function(params) {
		sw.Promed.swPersonDispFactRateEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.formPanel.getForm();
		base_form.reset();

		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

        this.action = arguments[0].action || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
		this.formMode = 'local';
        this.onHide = arguments[0].onHide || Ext.emptyFn;

        base_form.setValues(arguments[0].formParams);
		base_form.clearInvalid();

		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['fakticheskie_znacheniya_dobavlenie']);
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle(lang['fakticheskie_znacheniya_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['fakticheskie_znacheniya_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
	},
	width: 400
});