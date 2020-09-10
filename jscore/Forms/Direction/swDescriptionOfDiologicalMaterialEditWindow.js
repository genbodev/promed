/**
* swDescriptionOfDiologicalMaterialEditWindow - Описание биологического материала
* copy swMarkingBiopsyEditWindow
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*/

sw.Promed.swDescriptionOfDiologicalMaterialEditWindow = Ext.extend(sw.Promed.BaseForm, {
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
	id: 'DescriptionOfDiologicalMaterial',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('DescriptionOfDiologicalMaterial');

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
	width: 400,

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
			'MacroMaterialCytologic_id': base_form.findField('MacroMaterialCytologic_id').getValue(),
			'MacroMaterialCytologic_Mark': base_form.findField('MacroMaterialCytologic_Mark').getValue(),
			'MacroMaterialCytologic_Size': base_form.findField('MacroMaterialCytologic_Size').getValue(),
			'MacroMaterialCytologic_CountObject': base_form.findField('MacroMaterialCytologic_CountObject').getValue(),
			'BiologycalMaterialType_id': base_form.findField('BiologycalMaterialType_id').getValue(),
			'BiologycalMaterialType_Name': base_form.findField('BiologycalMaterialType_id').getFieldValue('BiologycalMaterialType_Name')
		};

		win.formStatus = 'edit';
		loadMask.hide();

		win.callback(data);
		win.hide();

		return true;
	},
	onHide: Ext.emptyFn,
	show: function(params) {
		sw.Promed.swDescriptionOfDiologicalMaterialEditWindow.superclass.show.apply(this, arguments);
		
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

		switch ( this.action ) {
			case 'add':
				this.setTitle(langs('Описание биологического материала') + ': ' + langs('Добавление'));
				this.enableEdit(true);
				this.formPanel.getForm().findField('MacroMaterialCytologic_Mark').focus(true);
				break;

			case 'edit':
				this.setTitle(langs('Описание биологического материала') + ': ' + langs('Редактирование'));
				this.enableEdit(true);
				this.formPanel.getForm().findField('MacroMaterialCytologic_Mark').focus(true);
				break;

			case 'view':
				this.setTitle(langs('Описание биологического материала') + ': ' + langs('Просмотр'));
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
			//bodyStyle: 'padding: 5px 5px 0',
			bodyStyle:'background-color:inherit;padding: 10px;', 
			border: true,
			frame: false,
			id: 'DescriptionOfDiologicalMaterialEditWindow',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'MacroMaterialCytologic_id' },
				{ name: 'EvnDirectionCytologic_id' },
				{ name: 'RecordStatus_Code' },
				{ name: 'MacroMaterialCytologic_Mark' },
				{ name: 'MacroMaterialCytologic_Size' },
				{ name: 'MacroMaterialCytologic_CountObject' },
				{ name: 'BiologycalMaterialType_id' }
			]),
			url: '/?c=EvnDirectionCytologic&m=saveVolumeAndMacroscopicDescription',
			items: [{
				name: 'MacroMaterialCytologic_id',
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionCytologic_id',
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				xtype: 'hidden'
			}, {
				fieldLabel: 'Маркировка препарата',
				name: 'MacroMaterialCytologic_Mark',
				allowBlank: false,
				width: 180,
				maxLength: 50,
				xtype: 'textfield'
			},{
				fieldLabel: 'Объем',
				name: 'MacroMaterialCytologic_Size',
				allowBlank: false,
				width: 180,
				allowDecimals: true,
				decimalPrecision: 1,
				xtype: 'numberfield'
			},{
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: langs('Кол-во объектов'),
				maxLength: 2,
				minText: 'Введите целое число больше нуля',
				//maxValue: 10000,
				minValue: 1,
				name: 'MacroMaterialCytologic_CountObject',
				allowBlank: false,
				width: 180,
				xtype: 'numberfield'
			},{
				xtype: 'swbiologycalmaterialtypecombo',
				hiddenName: 'BiologycalMaterialType_id',
				fieldLabel: 'Макро-описание',
				allowBlank: false,
				width: 180
			}]
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

		sw.Promed.swDescriptionOfDiologicalMaterialEditWindow.superclass.initComponent.apply(this, arguments);
	}
});