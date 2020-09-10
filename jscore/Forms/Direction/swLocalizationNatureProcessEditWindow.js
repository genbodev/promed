/**
* swLocalizationNatureProcessEditWindow - Форма Локализация, характер процесса
* copy swMarkingBiopsyEditWindow
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*/

sw.Promed.swLocalizationNatureProcessEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	formStatus: 'edit',
	height: 200,
	id: 'LocalizationNatureProcessEditWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('LocalizationNatureProcessEditWindow');

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
	plain: true,
	resizable: false,
	width: 800,

	/* методы */
	callback: Ext.emptyFn,
	doSave: function() {
		var
			win = this,
			form = win.formPanel,
			base_form = form.getForm();

		if ( !form.getForm().isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(win.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();

		data.Data = {
			'EvnDirectionCytologic_id': base_form.findField('EvnDirectionCytologic_id').getValue(),
			'LocalProcessCytologic_id': base_form.findField('LocalProcessCytologic_id').getValue(),
			'PathologicProcessType_id': base_form.findField('PathologicProcessType_id').getValue(),
			'PathologicProcessType_Name': base_form.findField('PathologicProcessType_id').getFieldValue('PathologicProcessType_Name'),
			'LocalProcessCytologic_FeatureForm': base_form.findField('LocalProcessCytologic_FeatureForm').getValue(),
			'LocalProcessCytologic_Localization': base_form.findField('LocalProcessCytologic_Localization').getValue(),
			'BiopsyReceive_id': base_form.findField('BiopsyReceive_id').getValue(),
			'BiopsyReceive_Name': base_form.findField('BiopsyReceive_id').getFieldValue('BiopsyReceive_Name')
		};

		win.formStatus = 'edit';
		loadMask.hide();

		win.callback(data);
		win.hide();

		return true;
	},
	onHide: Ext.emptyFn,
	show: function(params) {
		sw.Promed.swLocalizationNatureProcessEditWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		var base_form = this.formPanel.getForm();
		base_form.reset();

        this.action = arguments[0].action || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
		this.formMode = 'local';
		this.formStatus = 'edit';
        this.onHide = arguments[0].onHide || Ext.emptyFn;
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

        base_form.setValues(arguments[0].formParams);
        var title = 'Локализация, характер процесса';
		switch ( this.action ) {
			case 'add':
				this.setTitle(langs(title) + ': ' + langs('Добавление'));
				this.enableEdit(true);
				this.formPanel.getForm().findField('PathologicProcessType_id').focus(true);
				break;

			case 'edit':
				this.setTitle(langs(title) + ': ' + langs('Редактирование'));
				this.enableEdit(true);
				this.formPanel.getForm().findField('PathologicProcessType_id').focus(true);
				break;

			case 'view':
				this.setTitle(langs(title) + ': ' + langs('Просмотр'));
				this.enableEdit(false);
				this.buttons[this.buttons.length - 1].focus();
				break;
		}
		
		loadMask.hide();
	},

	/* конструктор */
	initComponent: function() {
		var form = this;

		form.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			// bodyStyle: 'padding: 5px 5px 0',
			bodyStyle:'background-color:inherit;padding: 10px;',
			border: false,
			frame: false,
			id: 'LocalProcessCytologicEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'LocalProcessCytologic_id' },
				{ name: 'EvnDirectionCytologic_id' },
				{ name: 'RecordStatus_Code' },
				{ name: 'PathologicProcessType_id' },
				{ name: 'LocalProcessCytologic_FeatureForm' },
				{ name: 'LocalProcessCytologic_Localization' },
				{ name: 'BiopsyReceive_id' }
			]),
			url: '/?c=MarkingBiopsy&m=saveMarkingBiopsy',
			items: [{
				name: 'LocalProcessCytologic_id',
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionCytologic_id',
				xtype: 'hidden'
			},{
				xtype: 'swpathologicprocesstypecombo',
				hiddenName: 'PathologicProcessType_id',
				fieldLabel: 'Характер патологического процесса',
				allowBlank: false,
				width: 180
			}, {
				allowBlank: true,
				fieldLabel: langs('Характеристики образования; прилежащие ткани'),
				height: 150,
				name: 'LocalProcessCytologic_FeatureForm',
				width: 600,
				xtype: 'textarea',
				maxLength: 500
			}, {
				allowBlank: false,
				fieldLabel: langs('Локализация патологического процесса'),
				height: 150,
				name: 'LocalProcessCytologic_Localization',
				width: 600,
				xtype: 'textarea',
				maxLength: 500
			}, {
				comboSubject: 'BiopsyReceive',
				allowBlank: true,
				fieldLabel: langs('Способ получения материала'),
				hiddenName: 'BiopsyReceive_id',
				width: 200,
				xtype: 'swcommonsprcombo'
			},
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				/*onShiftTabAction: function () {
					var base_form = form.formPanel.getForm();

					if ( form.action == 'view' ) {
						form.buttons[form.buttons.length - 1].focus(true);
					}
					else {
						form.formPanel.getForm().findField('MarkingBiopsy_ObjKolvo').focus(true);
					}
				},
				onTabAction: function () {
					form.buttons[form.buttons.length - 2].focus(true);
				},*/
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
				/*onShiftTabAction: function () {
					form.buttons[form.buttons.length - 2].focus(true);
				},
				onTabAction: function () {
					if ( form.action == 'view' ) {
						form.buttons[form.buttons.length - 2].focus(true);
					}
					else {
						form.formPanel.getForm().findField('MarkingBiopsy_NumBot').focus(true);
					}
				},*/
				text: BTN_FRMCANCEL
			}],
			items: [
				 form.formPanel
			],
			layout: 'form'
		});

		sw.Promed.swLocalizationNatureProcessEditWindow.superclass.initComponent.apply(this, arguments);
	}
});