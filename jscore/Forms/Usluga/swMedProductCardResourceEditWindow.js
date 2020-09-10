/**
* swMedProductCardResourceEditWindow - Форма добавления/редактирования связи ресурса с медицинским изделием
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Usluga
* @access		public
* @copyright	Copyright (c) 2015 Swan Ltd.
* @author		Aleksandr Chebukin
* @version		05.11.2015
* @comment		Префикс для id компонентов MPCREF (MedProductCardResourceEditForm)
*/

sw.Promed.swMedProductCardResourceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedProductCardResourceEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swMedProductCardResourceEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {		
		var _this = this;
		var form = this.findById('MedProductCardResourceEditForm');
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

		base_form.findField('MedProductCard_id').getStore().clearFilter();
		
		data.MedProductCardResourceData = {
			'MedProductCardResource_id': base_form.findField('MedProductCardResource_id').getValue(),
			'MedProductCard_id': base_form.findField('MedProductCard_id').getValue(),
			'MedProductClass_Name': base_form.findField('MedProductCard_id').getFieldValue('MedProductClass_Name'),
			'MedProductCardResource_begDT': base_form.findField('MedProductCardResource_begDT').getValue(),
			'MedProductCardResource_endDT': base_form.findField('MedProductCardResource_endDT').getValue()
		};

		this.formStatus = 'edit';
		loadMask.hide();

		this.callback(data);
		this.hide();
		return true;
	},	
	filterMedProductCardCombo: function()
	{
		var 
			form = this.formPanel.getForm(),
			MedProductCardCombo = form.findField('MedProductCard_id'),
			CardType_id = form.findField('CardType_id').getValue();		
		
		MedProductCardCombo.getStore().clearFilter();
		MedProductCardCombo.lastQuery = '';
		if (!Ext.isEmpty(CardType_id)) {		
			MedProductCardCombo.getStore().filterBy(function(rec) {
				return ( rec.get('CardType_id') == CardType_id );
			});
			MedProductCardCombo.setBaseFilter(function(rec) {
				return ( rec.get('CardType_id') == CardType_id );
			});
		}
	},
	draggable: true,
	formStatus: 'edit',
	height: 200,
	id: 'MedProductCardResourceEditWindow',
	initComponent: function() {
		var form = this;

		form.formPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'MedProductCardResourceEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'MedProductCardResource_id' },
				{ name: 'MedProductCardResource_begDT' },
				{ name: 'MedProductCardResource_endDT' }
			]),
			region: 'center',
			url: '/?c=UslugaComplex&m=saveUslugaComplexGroup',
			items: [{
				name: 'MedProductCardResource_id',
				value: '',
				xtype: 'hidden'
			}, {			
				allowBlank: true,
				editable: true,
				comboSubject: 'CardType',
				fieldLabel: lang['tip_mi'],
				hiddenName: 'CardType_id',
				width: 400,
				prefix: 'passport_',
				xtype: 'swcommonsprcombo',
				listeners: {
					select: function(combo, record) {
						this.filterMedProductCardCombo();
						var MedProductCard = this.formPanel.getForm().findField('MedProductCard_id');
						if (record && MedProductCard.getFieldValue('CardType_id') != record.get('CardType_id')) {
							MedProductCard.clearValue();
						}
					}.createDelegate(this),
					change: function(combo, nv, ov) {						
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('CardType_id') == nv);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					}
				}
			}, {
				allowBlank: false,
				editable: true,
				codeField: 'AccountingData_InventNumber',
				displayField: 'MedProductClass_Name',
				fieldLabel: lang['naimenovanie_mi'],
				hiddenName: 'MedProductCard_id',
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
						id: 'MedProductCard_id'
					}, [
						{ name: 'MedProductCard_id', mapping: 'MedProductCard_id', type: 'int' },
						{ name: 'CardType_id', mapping: 'CardType_id', type: 'int' },
						{ name: 'AccountingData_InventNumber', mapping: 'AccountingData_InventNumber', type: 'string' },
						{ name: 'MedProductClass_Name', mapping: 'MedProductClass_Name', type: 'string' },
						{ name: 'MedProductClass_Model', mapping: 'MedProductClass_Model', type: 'string' }
					]),
					url: '/?c=LpuPassport&m=loadMedProductCard'
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<table style="border: 0;"><td style="width: 70px"><font color="red">{AccountingData_InventNumber}</font></td><td><h3>{MedProductClass_Name}</h3>{MedProductClass_Model}</td></tr></table>',
					'</div></tpl>'
				),
				listeners: {
					select: function(combo, record) {
						if (record) {
							var CardType = form.formPanel.getForm().findField('CardType_id');
							var index = CardType.getStore().findBy(function(rec) {
								return (rec.get('CardType_id') == record.get('CardType_id'));
							});
							CardType.setValue(record.get('CardType_id'));
							CardType.fireEvent('select', CardType, CardType.getStore().getAt(index), index);
						}
					}.createDelegate(this)
				},
				triggerAction: 'all',
				valueField: 'MedProductCard_id',
				width: 400,
				listWidth: 600,
				xtype: 'swbaselocalcombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_nachala'],
				format: 'd.m.Y',
				name: 'MedProductCardResource_begDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: true,
				fieldLabel: lang['data_okonchaniya'],
				format: 'd.m.Y',
				name: 'MedProductCardResource_endDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				xtype: 'swdatefield'
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

		sw.Promed.swMedProductCardResourceEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('MedProductCardResourceEditWindow');

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
		sw.Promed.swMedProductCardResourceEditWindow.superclass.show.apply(this, arguments);
		
		log(arguments);

		var base_form = this.formPanel.getForm();
		base_form.reset();

		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		var CardTypeCombo = base_form.findField('CardType_id');

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

        this.action = arguments[0].action || null;
        this.Lpu_id = arguments[0].Lpu_id || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
		this.formMode = 'local';
        this.onHide = arguments[0].onHide || Ext.emptyFn;
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

        base_form.setValues(arguments[0].formParams);		
		base_form.findField('MedProductCard_id').getStore().load({
			params: {Lpu_id: this.Lpu_id},
			callback: function() {
				if ( base_form.findField('MedProductCard_id').getStore().getById(base_form.findField('MedProductCard_id').getValue()) ) {	
					base_form.findField('MedProductCard_id').setValue(base_form.findField('MedProductCard_id').getValue());
				}
				if ( this.action != 'add' ) {
					CardTypeCombo.setValue(base_form.findField('MedProductCard_id').getFieldValue('CardType_id'));	
					var index = CardTypeCombo.getStore().findBy(function(rec) {
						return (rec.get('CardType_id') == CardTypeCombo.getValue());
					});
					CardTypeCombo.fireEvent('select', CardTypeCombo, CardTypeCombo.getStore().getAt(index), index);		
				}
				loadMask.hide();
			}
		});

		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['meditsinskoe_izdelie_dobavlenie']);
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle(lang['meditsinskoe_izdelie_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['meditsinskoe_izdelie_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
	},
	width: 600
});