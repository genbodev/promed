/**
 * swFoodCookSpecEditWindow - окно редактирования ингредиента блюда
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Bykov Stanislav (savage@swan.perm.ru)
 * @version			01.10.2013
 */

sw.Promed.swFoodCookSpecEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		var base_form = this.FormPanel.getForm();
		var wnd = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		wnd.getLoadMask("Подождите, идет сохранение...").show();
		
		var data = new Object();

		var FoodStuff_Name, FoodStuff_Protein, FoodStuff_Fat, FoodStuff_Carbohyd, FoodStuff_Caloric, FoodStuff_id = base_form.findField('FoodStuff_id').getValue();

		var index = base_form.findField('FoodStuff_id').getStore().findBy(function(rec) {
			return (rec.get('FoodStuff_id') == FoodStuff_id);
		});

		if ( index >= 0 ) {
			FoodStuff_Name = base_form.findField('FoodStuff_id').getStore().getAt(index).get('FoodStuff_Name');
			FoodStuff_Protein = base_form.findField('FoodStuff_id').getStore().getAt(index).get('FoodStuff_Protein');
			FoodStuff_Fat = base_form.findField('FoodStuff_id').getStore().getAt(index).get('FoodStuff_Fat');
			FoodStuff_Carbohyd = base_form.findField('FoodStuff_id').getStore().getAt(index).get('FoodStuff_Carbohyd');
			FoodStuff_Caloric = base_form.findField('FoodStuff_id').getStore().getAt(index).get('FoodStuff_Caloric');
		}

		data.FoodCookSpecData = {
			'FoodCookSpec_id': base_form.findField('FoodCookSpec_id').getValue(),
			'FoodStuff_id': FoodStuff_id,
			'Okei_nid': base_form.findField('Okei_nid').getValue(),
			'Okei_bid': base_form.findField('Okei_bid').getValue(),
			'FoodCookSpec_Priority': base_form.findField('FoodCookSpec_Priority').getValue(),
			'FoodStuff_Name': FoodStuff_Name,
			'FoodStuff_Protein': FoodStuff_Protein,
			'FoodStuff_Fat': FoodStuff_Fat,
			'FoodStuff_Carbohyd': FoodStuff_Carbohyd,
			'FoodStuff_Caloric': FoodStuff_Caloric,
			'FoodCookSpec_MassN': base_form.findField('FoodCookSpec_MassN').getValue(),
			'FoodCookSpec_MassB': base_form.findField('FoodCookSpec_MassB').getValue(),
			'FoodCookSpec_Time': base_form.findField('FoodCookSpec_Time').getValue(),
			'FoodCookSpec_Descr': base_form.findField('FoodCookSpec_Descr').getValue(),
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue()
		};

		wnd.formStatus = 'edit';
		wnd.getLoadMask().hide()

		wnd.callback(data);
		wnd.hide();
	},
	draggable: true,
	id: 'swFoodCookSpecEditWindow',
	maximizable: false,
	modal: true,
	objectSrc: '/jscore/Forms/Cook/swFoodCookSpecEditWindow.js',
	resizable: false,
	show: function() {
		sw.Promed.swFoodCookSpecEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['ingredient_dobavlenie']);
				this.enableEdit(true);

				loadMask.hide();

				base_form.findField('FoodCookSpec_Priority').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(lang['ingredient_redaktirovanie']);
					this.enableEdit(true);
				}
				else {
					this.setTitle(lang['ingredient_prosmotr']);
					this.enableEdit(false);
				}

				var FoodStuff_id  = base_form.findField('FoodStuff_id').getValue();

				if ( !Ext.isEmpty(FoodStuff_id) ) {
					base_form.findField('FoodStuff_id').getStore().load({
						callback: function() {
							if ( base_form.findField('FoodStuff_id').getStore().getCount() > 0 ) {
								var index = base_form.findField('FoodStuff_id').getStore().findBy(function(rec) {
									return (rec.get('FoodStuff_id') == FoodStuff_id);
								});

								if ( index >= 0 ) {
									base_form.findField('FoodStuff_id').setValue(FoodStuff_id);
								}
								else {
									base_form.findField('FoodStuff_id').clearValue();
								}
							}
						},
						params: {
							FoodStuff_id: FoodStuff_id
						}
					});
				}

				loadMask.hide();

				if ( this.action == 'edit' ) {
					base_form.findField('FoodCookSpec_Priority').focus(true, 250);
				}
				else {
					this.buttons[this.buttons.length - 1].focus();
				}
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	title: lang['ingredient'],
	width: 600,

	initComponent: function() {
		var form = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0px;',
			border: false,
			frame: true,
			id: 'FoodCookSpecEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			region: 'center',
			style: 'margin-bottom: 0.5em;',
			url: '/?c=FoodCook&m=saveFoodCookSpec',

			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'FoodCookSpec_id' },
				{ name: 'RecordStatus_Code' },
				{ name: 'FoodStuff_id' },
				{ name: 'Okei_nid' },
				{ name: 'Okei_bid' },
				{ name: 'FoodCookSpec_Priority' },
				{ name: 'FoodCookSpec_MassN' },
				{ name: 'FoodCookSpec_MassB' },
				{ name: 'FoodCookSpec_Time' },
				{ name: 'FoodCookSpec_Descr' }
			]),

			items: [{
				name: 'FoodCookSpec_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: lang['ocherednost'],
				name: 'FoodCookSpec_Priority',
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['produkt'],
				hiddenName: 'FoodStuff_id',
				width: 350,
				xtype: 'swfoodstuffcombo'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowDecimails: true,
						allowNegative: false,
						fieldLabel: lang['massa_netto'],
						name: 'FoodCookSpec_MassN',
						width: 100,
						xtype: 'numberfield'
					}, {
						allowDecimails: true,
						allowNegative: false,
						fieldLabel: lang['massa_brutto'],
						name: 'FoodCookSpec_MassB',
						width: 100,
						xtype: 'numberfield'
					}]
				}, {
					border: false,
					labelWidth: 70,
					layout: 'form',
					items: [{
						fieldLabel: lang['ed_izm'],
						hiddenName: 'Okei_nid',
						width: 100,
						xtype: 'swokeicombo'
					}, {
						fieldLabel: lang['ed_izm'],
						hiddenName: 'Okei_bid',
						width: 100,
						xtype: 'swokeicombo'
					}]
				}]
			}, {
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: lang['vremya_prigotovleniya'],
				name: 'FoodCookSpec_Time',
				width: 100,
				xtype: 'numberfield'
			}, {
				fieldLabel: lang['opisanie'],
				name: 'FoodCookSpec_Descr',
				width: 350,
				xtype: 'textfield'
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'FCSEW_SaveButton',
				text: BTN_FRMSAVE
			},
			'-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'FCSEW_CancelButton',
				text: BTN_FRMCANCEL
			}]
		});

		sw.Promed.swFoodCookSpecEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
