/**
 * swDocumentUcStrZayavEditWindow - окно редактирования позиции заявки на медикаменты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Farmacy
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			16.01.2014
 */

/*NO PARSE JSON*/

sw.Promed.swDocumentUcStrZayavEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDocumentUcStrZayavEditWindow',
	width: 640,
	autoHeight: true,
	//height: 600,
	layout: 'form',
	callback: Ext.emptyFn,
	modal: true,
	title: lang['pozitsiya_zayavki_na_medikamentyi'],

	action: 'view',
	DrugDocumentClass_Code: null,

	doSave: function(options)
	{
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

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

		data.DocumentUcStrData = base_form.getValues();
		data.DocumentUcStrData.Drug_Name = base_form.findField('Drug_id').getFieldValue('Drug_Name');
		data.DocumentUcStrData.DocumentUcStr_Sum = base_form.findField('DocumentUcStr_Sum').getValue();
		data.DocumentUcStrData.DocumentUcStr_PlanSum = base_form.findField('DocumentUcStr_Sum').getValue();

		wnd.getLoadMask().hide();

		wnd.callback(data);

		if (!options || !options.copy) {
			wnd.hide();
		}
	},

	doCopy: function()
	{
		if ( this.action == 'add' ) {
			this.doSave({copy: true});
		}
		else {
			this.action = 'add';

			var base_form = this.FormPanel.getForm();

			base_form.findField('RecordStatus_Code').setValue(0);
			base_form.findField('DocumentUcStr_id').setValue(null);

			this.enableEdit(true);
			this.setTitle(lang['pozitsiya_zayavki_na_medikamentyi_dobavlenie']);
		}
	},

	calcSumField: function(count_field, price_field, sum_field)
	{
		var base_form = this.FormPanel.getForm();
		var count = 0, price = 0, sum = 0, coeff = 1;

		var unit_convers = base_form.findField('Okei_id').getFieldValue('Okei_UnitConversion');
		if (unit_convers) {
			coeff = unit_convers;
		}

		count = base_form.findField(count_field).getValue();
		price = base_form.findField(price_field).getValue();
		count = (count == null) ? 0 : count;
		price = (price == null) ? 0 : price;
		sum = Number(count*coeff*price).toFixed(2);

		base_form.findField(sum_field).setValue(sum);
	},
	calcFactSum: function() {
		this.calcSumField('DocumentUcStr_Count','DocumentUcStr_Price','DocumentUcStr_Sum');
	},
	calcPlanSum: function() {
		this.calcSumField('DocumentUcStr_PlanKolvo','DocumentUcStr_PlanPrice','DocumentUcStr_PlanSum');
	},

	show: function()
	{
		sw.Promed.swDocumentUcStrZayavEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		base_form.reset();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].DrugDocumentClass_Code) {
			this.DrugDocumentClass_Code = arguments[0].DrugDocumentClass_Code;
		}

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		if (this.DrugDocumentClass_Code && this.DrugDocumentClass_Code == 2) {
			base_form.findField('Person_id').setContainerVisible(true);
			base_form.findField('Person_id').setAllowBlank(false);
			this.syncShadow();
		} else {
			base_form.findField('Person_id').setContainerVisible(false);
			base_form.findField('Person_id').setAllowBlank(true);
			this.syncShadow();
		}

		wnd.getLoadMask(lang['zagruzka_dannyih_formyi']).show();
		switch(wnd.action) {
			case 'add':
				wnd.calcFactSum();
				wnd.calcPlanSum();
				wnd.enableEdit(true);
				wnd.getLoadMask().hide();
				wnd.setTitle(lang['pozitsiya_zayavki_na_medikamentyi_dobavlenie']);
			break;

			case 'edit':
			case 'view':
				if (wnd.action == 'view') {
					wnd.setTitle(lang['pozitsiya_zayavki_na_medikamentyi_prosmotr']);
					wnd.enableEdit(false);
				} else {
					wnd.setTitle(lang['pozitsiya_zayavki_na_medikamentyi_redaktirovanie']);
					wnd.enableEdit(true);
				}

				var callback = function() {
					if ( base_form.findField('Drug_id').getValue() > 0 ) {
						base_form.findField('Drug_id').getStore().load({params: {Drug_id: base_form.findField('Drug_id').getValue()}});
					}
					var person_id = base_form.findField('Person_id').getValue();
					if ( person_id > 0 ) {
						base_form.findField('Person_id').getStore().loadData([{
							Person_id: person_id,
							Person_Fio: base_form.findField('Person_Fio').getValue()
						}]);
						base_form.findField('Person_id').setValue(person_id);
					} else {
						base_form.findField('Person_id').getStore().removeAll();
					}

					wnd.calcFactSum();
					wnd.calcPlanSum();
					wnd.getLoadMask().hide();
				};

				if ( base_form.findField('DocumentUcStr_id').getValue() > 0 ) {
					base_form.load({
						failure:function () {
							wnd.getLoadMask().hide();
							wnd.hide();
						},
						params:{
							DocumentUcStr_id: base_form.findField('DocumentUcStr_id').getValue()
						},
						success: function (response) {
							callback();
						},
						url:'/?c=Farmacy&m=loadDocumentUcStrView'
					});
				} else {
					callback();
				}
			break;
		}
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 20px 0',
			border: false,
			frame: true,
			//height: 200,
			labelAlign: 'right',
			labelWidth: 140,
			id: 'DUSZEW_FormPanel',
			region: 'center',

			items: [{
				name: 'DocumentUcStr_id',
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_Fio',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				name: 'Drug_id',
				fieldLabel: lang['tmts'],
				xtype: 'swdrugsimplecombo',
				width: 420
			}, {
				allowBlank:false,
				editable: true,
				fieldLabel: lang['edinitsa_izmereniy'],
				hiddenName: 'Okei_id',
				xtype: 'swokeicombo',
				width: 420,
				listeners: {
					'change': function() {
						wnd.calcPlanSum();
						wnd.calcFactSum();
					}
				}
			}, {
				allowBlank: false,
				allowNegative: false,
				fieldLabel: lang['kolichestvo_plan'],
				name: 'DocumentUcStr_PlanKolvo',
				xtype: 'numberfield',
				width: 200,
				listeners: {
					'change': function() {
						wnd.calcPlanSum();
					}
				}
			}, {
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: lang['tsena_plan'],
				name: 'DocumentUcStr_PlanPrice',
				xtype: 'numberfield',
				width: 200,
				value: 0,
				listeners: {
					'change': function() {
						wnd.calcPlanSum();
					}
				}
			}, {
				fieldLabel: lang['summa_plan'],
				name: 'DocumentUcStr_PlanSum',
				disabled: true,
				xtype: 'numberfield',
				width: 200
			}, {
				allowBlank: false,
				allowNegative: false,
				fieldLabel: lang['kolichestvo_fakt'],
				name: 'DocumentUcStr_Count',
				xtype: 'numberfield',
				width: 200,
				listeners: {
					'change': function() {
						wnd.calcFactSum();
					}
				}
			}, {
				allowNegative: false,
				decimalPrecision: 2,
				fieldLabel: lang['tsena_fakt'],
				name: 'DocumentUcStr_Price',
				xtype: 'numberfield',
				width: 200,
				value: 0,
				listeners: {
					'change': function() {
						wnd.calcFactSum();
					}
				}
			}, {
				decimalPrecision: 2,
				fieldLabel: lang['summa_fakt'],
				name: 'DocumentUcStr_Sum',
				disabled: true,
				xtype: 'numberfield',
				width: 200
			}, {
				editable: false,
				fieldLabel: lang['patsient'],
				hiddenName: 'Person_id',
				width: 420,
				xtype: 'swpersoncombo',
				onTrigger1Click: function() {
					if (this.disabled) return false;
					var ownerWindow = Ext.getCmp('PersonEditWindow');
					var combo = this;
					getWnd('swPersonSearchWindow').show({
						onSelect: function(personData) {
							if ( personData.Person_id > 0 )
							{
								combo.getStore().loadData([{
									Person_id: personData.Person_id,
									Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
								}]);
								combo.setValue(personData.Person_id);
								combo.collapse();
								combo.focus(true, 500);
								combo.fireEvent('change', combo);
							}
							getWnd('swPersonSearchWindow').hide();
						},
						onClose: function() {combo.focus(true, 500)}
					});
				},
				enableKeyEvents: true,
				listeners: {
					'change': function(combo) {
					},
					'keydown': function( inp, e ) {
						if ( e.F4 == e.getKey() )
						{
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;
							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;
							e.browserEvent.returnValue = false;
							e.returnValue = false;
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							inp.onTrigger1Click();
							return false;
						}
					},
					'keyup': function(inp, e) {
						if ( e.F4 == e.getKey() )
						{
							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;
							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;
							e.browserEvent.returnValue = false;
							e.returnValue = false;
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							return false;
						}
					}
				}
						}],
			reader: new Ext.data.JsonReader(
				{
					success: function()
					{
						//
					}
				},
				[
					{ name: 'DocumentUcStr_id' },
					{ name: 'Drug_id' },
					{ name: 'Okei_id' },
					{ name: 'Person_id' },
					{ name: 'Person_Fio' },
					{ name: 'DocumentUcStr_Sum' },
					{ name: 'DocumentUcStr_Price' },
					{ name: 'DocumentUcStr_Count' },
					{ name: 'DocumentUcStr_PlanSum' },
					{ name: 'DocumentUcStr_PlanPrice' },
					{ name: 'DocumentUcStr_PlanKolvo' }
				]
			)
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel
			],
			buttons: [
				{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'DUSZEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					handler: function() {
						this.doCopy();
					}.createDelegate(this),
					iconCls: 'copy16',
					id: 'DUSZEW_SaveButton',
					text: lang['kopiya']
				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'DUSZEW_CancelButton',
					text: lang['otmena']
				}]
		});

		sw.Promed.swDocumentUcStrZayavEditWindow.superclass.initComponent.apply(this, arguments);
	}
});