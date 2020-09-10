/**
* swMarkingBiopsyEditWindow - Форма добавления/редактирования маркировки материала
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		EvnDirectionHistologic
* @access		public
* @copyright	Copyright (c) 2018 Swan Ltd.
* @author		Stanislav Bykov
* @version		12.09.2018
*/

sw.Promed.swMarkingBiopsyEditWindow = Ext.extend(sw.Promed.BaseForm, {
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
	id: 'MarkingBiopsyEditWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('MarkingBiopsyEditWindow');

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

		data.MarkingBiopsyData = {
			'EvnDirectionHistologic_id': base_form.findField('EvnDirectionHistologic_id').getValue(),
			'MarkingBiopsy_id': base_form.findField('MarkingBiopsy_id').getValue(),
			'MarkingBiopsy_NumBot': base_form.findField('MarkingBiopsy_NumBot').getValue(),
			'MarkingBiopsy_LocalProcess': base_form.findField('MarkingBiopsy_LocalProcess').getValue(),
			'MarkingBiopsy_NatureProcess': base_form.findField('MarkingBiopsy_NatureProcess').getValue(),
			'MarkingBiopsy_ObjKolvo': base_form.findField('MarkingBiopsy_ObjKolvo').getValue(),
			'AnatomicLocal_id': base_form.findField('AnatomicLocal_id').getValue(),
			'MaterialChange_id': base_form.findField('MaterialChange_id').getValue(),
			'MarkingBiopsy_Size': base_form.findField('MarkingBiopsy_Size').getValue(),
			'MarkingBiopsy_Shape': base_form.findField('MarkingBiopsy_Shape').getValue(),
			'MarkingBiopsy_Border': base_form.findField('MarkingBiopsy_Border').getValue(),
			'MarkingBiopsy_Consistence': base_form.findField('MarkingBiopsy_Consistence').getValue(),
			'MarkingBiopsy_ColorSkin': base_form.findField('MarkingBiopsy_ColorSkin').getValue()
		};
		
		if(data.MarkingBiopsyData.AnatomicLocal_id){
			data.MarkingBiopsyData.AnatomicLocal_Text = base_form.findField('AnatomicLocal_id').getFieldValue('AnatomicLocal_Name');
		}else if(data.MarkingBiopsyData.MarkingBiopsy_LocalProcess){
			data.MarkingBiopsyData.AnatomicLocal_Text = data.MarkingBiopsyData.MarkingBiopsy_LocalProcess;
		}

		win.formStatus = 'edit';
		loadMask.hide();

		win.callback(data);
		win.hide();

		return true;
	},
	onHide: Ext.emptyFn,
	show: function(params) {
		sw.Promed.swMarkingBiopsyEditWindow.superclass.show.apply(this, arguments);
		
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

		if(arguments[0].formParams && arguments[0].formParams.MarkingBiopsy_LocalProcess){
			base_form.findField('MarkingBiopsy_LocalProcess').showContainer();
		}else{
			base_form.findField('MarkingBiopsy_LocalProcess').hideContainer();
		}
		this.onResize();

		switch ( this.action ) {
			case 'add':
				this.setTitle(langs('Маркировка материала') + ': ' + langs('Добавление'));
				this.enableEdit(true);
				this.formPanel.getForm().findField('MarkingBiopsy_ObjKolvo').setValue(1);
				this.formPanel.getForm().findField('MarkingBiopsy_NumBot').focus(true);
				break;

			case 'edit':
				this.setTitle(langs('Маркировка материала') + ': ' + langs('Редактирование'));
				this.enableEdit(true);
				this.formPanel.getForm().findField('MarkingBiopsy_NumBot').focus(true);
				break;

			case 'view':
				this.setTitle(langs('Маркировка материала') + ': ' + langs('Просмотр'));
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
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'MarkingBiopsyEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'MarkingBiopsy_id' },
				{ name: 'EvnDirectionHistologic_id' },
				{ name: 'RecordStatus_Code' },
				{ name: 'MarkingBiopsy_NumBot' },
				{ name: 'MarkingBiopsy_LocalProcess' },
				{ name: 'MarkingBiopsy_NatureProcess' },
				{ name: 'MarkingBiopsy_ObjKolvo' },
				{ name: 'AnatomicLocal_id' },
				{ name: 'MaterialChange_id' },
				{ name: 'MarkingBiopsy_Size' },
				{ name: 'MarkingBiopsy_Shape' },
				{ name: 'MarkingBiopsy_Border' },
				{ name: 'MarkingBiopsy_Consistence' },
				{ name: 'MarkingBiopsy_ColorSkin' }
			]),
			url: '/?c=MarkingBiopsy&m=saveMarkingBiopsy',
			items: [{
				name: 'MarkingBiopsy_id',
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionHistologic_id',
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				enableKeyEvents: true,
				fieldLabel: langs('Номер флакона'),
				listeners: {
					'keydown':function (inp, e) {
						if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
							e.stopEvent();
							form.buttons[form.buttons.length - 1].focus();
						}
					}
				},
				maxValue: 10000,
				minValue: 1,
				name: 'MarkingBiopsy_NumBot',
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: true,
				fieldLabel: langs('Локализация патологического процесса (орган, топография)'),
				height: 150,
				name: 'MarkingBiopsy_LocalProcess',
				width: 600,
				xtype: 'textarea',
				disabled: true
			},{
				xtype: 'swanatomiclocalcombo',
				mode: 'local',
				allowBlank: false,
				anchor: '',
				width: 250,
				listWidth: 250,
				hiddenName: 'AnatomicLocal_id',
				fieldLabel: langs('Локализация патологического процесса'),
			},{
				xtype: 'swmaterialchangecombo',
				mode: 'local',
				width: 250,
				anchor: '',
				listWidth: 250,
				hiddenName: 'MaterialChange_id',
				fieldLabel: langs('Характер изменений тканей')
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: langs('Количество объектов'),
				maxValue: 10000,
				minValue: 1,
				name: 'MarkingBiopsy_ObjKolvo',
				width: 100,
				xtype: 'numberfield'
			}, {
				autoHeight: true,
				title: langs('Качественная характеристика биопсируемого образования'),
				bodyStyle:'padding: 10px;',
				xtype: 'fieldset',
				columnWidth: 1,
				labelWidth: 180,
				items: [
					{
						fieldLabel: 'Размер',
						name: 'MarkingBiopsy_Size',
						width: 300,
						xtype: 'textfield'
					},
					{
						fieldLabel: 'Форма',
						name: 'MarkingBiopsy_Shape',
						width: 300,
						xtype: 'textfield'
					},
					{
						fieldLabel: 'Характер границы',
						name: 'MarkingBiopsy_Border',
						width: 300,
						xtype: 'textfield'
					},
					{
						fieldLabel: 'Консистенция',
						name: 'MarkingBiopsy_Consistence',
						width: 300,
						xtype: 'textfield'
					},
					{
						fieldLabel: 'Цвет кожи над образованием',
						name: 'MarkingBiopsy_ColorSkin',
						width: 300,
						xtype: 'textfield'
					},
					{
						allowBlank: true,
						fieldLabel: langs('Иные характеристики'),
						height: 150,
						name: 'MarkingBiopsy_NatureProcess',
						width: 500,
						xtype: 'textarea'
					}
				]
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
					else {
						form.formPanel.getForm().findField('MarkingBiopsy_ObjKolvo').focus(true);
					}
				},
				onTabAction: function () {
					form.buttons[form.buttons.length - 2].focus(true);
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
					form.buttons[form.buttons.length - 2].focus(true);
				},
				onTabAction: function () {
					if ( form.action == 'view' ) {
						form.buttons[form.buttons.length - 2].focus(true);
					}
					else {
						form.formPanel.getForm().findField('MarkingBiopsy_NumBot').focus(true);
					}
				},
				text: BTN_FRMCANCEL
			}],
			items: [
				 form.formPanel
			],
			layout: 'form'
		});

		sw.Promed.swMarkingBiopsyEditWindow.superclass.initComponent.apply(this, arguments);
	}
});