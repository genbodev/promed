/**
 * swDrugListStrEditWindow - окно редактирования медикамента в перечне
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			23.10.2017
 */
/*NO PARSE JSON*/

sw.Promed.swDrugListStrEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugListStrEditWindow',
	//layout: 'form',
	modal: true,
	autoHeight: true,
	width: 580,

	doSave: function() {
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var requireOneOf = ['Drug_id','DrugComplexMnn_id','Actmatters_id','DrugNonpropNames_id','Tradenames_id'];
		var allRequiredIsEmpty = requireOneOf.every(function(fieldName) {
			return Ext.isEmpty(base_form.findField(fieldName).getValue());
		});
		if (allRequiredIsEmpty) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField(requireOneOf[0]).focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Не указаны данные о медикаменте. Сохранение невозможно. Должно быть заполнено хотя бы одно из полей ЛП, Комплексное МНН, МНН, НН, Торговое наименование.',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (Ext.isEmpty(base_form.findField('DrugListStr_Num').getValue()) || Ext.isEmpty(base_form.findField('GoodsUnit_nid').getValue())) {
			var normative_empty_msg = null;
			if (this.DrugListType_Code == 2) {
				normative_empty_msg = 'Не указан норматив для списания медикаментов. Сохранение невозможно.';
			}
			if (this.DrugListType_Code == 4) {
				normative_empty_msg = 'Не указан норматив неснижаемого остатка. Сохранение невозможно.';
			}
			if (normative_empty_msg) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						base_form.findField('DrugListStr_Num').focus(true);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: normative_empty_msg,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var params = {};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();
				base_form.findField('DrugListStr_id').setValue(action.result.DrugListStr_id);

				this.callback();
				this.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swDrugListStrEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		if (arguments && arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0] && arguments[0].DrugListType_Code) {
			this.DrugListType_Code = arguments[0].DrugListType_Code;
		}
		if (arguments && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		this.DrugTip = new Ext.ToolTip({
			target: 'DLSEW_Drug_id',
			html: 'Медикамент указывается в перечне как Лекарственный препарат, если формируется шаблон для работы со списком потребительских упаковок ЛС.',
			autoHide: true
		});

		this.DrugComplexMnnTip = new Ext.ToolTip({
			target: 'DLSEW_DrugComplexMnn_id',
			html: 'Медикамент указывается в перечне как комплексное МНН, если  формируется шаблон для работы со списком ЛС по основным потребительским свойствам: МНН, лекарственная форма, дозировка, фасовка.',
			autoHide: true
		});

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет загрузка..."});
		loadMask.show();

		var drug_combo = base_form.findField('Drug_id');
		var drug_complex_mnn_combo = base_form.findField('DrugComplexMnn_id');

		switch(this.action) {
			case 'add':
				this.setTitle('Медикамент в перечне: Добавление');
				this.enableEdit(true);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Медикамент в перечне: Изменение');
					this.enableEdit(true);
				} else {
					this.setTitle('Медикамент в перечне: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					url: '/?c=DrugList&m=loadDrugListStrForm',
					params: {
						DrugListStr_id: base_form.findField('DrugListStr_id').getValue()
					},
					success: function() {
						loadMask.hide();

						if (!Ext.isEmpty(drug_combo.getValue())) {
							drug_combo.getStore().load({
								params: {Drug_id: drug_combo.getValue()},
								callback: function() {
									drug_combo.setValue(drug_combo.getValue());
								}
							});
						}
						if (!Ext.isEmpty(drug_complex_mnn_combo.getValue())) {
							drug_complex_mnn_combo.getStore().load({
								params: {DrugComplexMnn_id: drug_complex_mnn_combo.getValue()},
								callback: function() {
									drug_complex_mnn_combo.setValue(drug_complex_mnn_combo.getValue());
								}
							});
						}
					}.createDelegate(this),
					failure: function() {
						loadMask.hide();
					}.createDelegate(this)
				});
				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'DLEW_FormPanel',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 195,
			url: '/?c=DrugList&m=saveDrugListStr',
			items: [{
				xtype: 'hidden',
				name: 'DrugListStr_id'
			}, {
				xtype: 'hidden',
				name: 'DrugList_id'
			}, {
				xtype: 'swcommonsprcombo',
				comboSubject: 'DrugListGroup',
				hiddenName: 'DrugListGroup_id',
				fieldLabel: 'Группа',
				width: 300
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'DrugListStr_Name',
				fieldLabel: 'Наименование',
				width: 300
			}, {
				id: 'DLSEW_Drug_id',
				allowBlank: true,
				xtype: 'swdrugsimplecombo',
				hiddenName: 'Drug_id',
				fieldLabel: 'ЛП',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var drug_complex_mnn_combo = base_form.findField('DrugComplexMnn_id');
						var tradenames_combo = base_form.findField('Tradenames_id');

						var DrugComplexMnn_id = combo.getFieldValue('DrugComplexMnn_id');
						var Tradenames_id = combo.getFieldValue('Tradenames_id');

						if (!Ext.isEmpty(DrugComplexMnn_id)) {
							drug_complex_mnn_combo.getStore().load({
								params: {DrugComplexMnn_id: DrugComplexMnn_id},
								callback: function() {
									drug_complex_mnn_combo.setValue(DrugComplexMnn_id);
									drug_complex_mnn_combo.fireEvent('change', drug_complex_mnn_combo, DrugComplexMnn_id);
								}
							});
						}
						if (!Ext.isEmpty(Tradenames_id)) {
							tradenames_combo.setValue(Tradenames_id);
							tradenames_combo.fireEvent('change', tradenames_combo, Tradenames_id);
						}
					}.createDelegate(this)
				},
				width: 300
			}, {
				id: 'DLSEW_DrugComplexMnn_id',
				xtype: 'swdrugcomplexmnncombo',
				hiddenName: 'DrugComplexMnn_id',
				fieldLabel: 'Комплексное МНН',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var actmatters_combo = base_form.findField('Actmatters_id');
						var clsdrugforms_combo = base_form.findField('Clsdrugforms_id');

						var Actmatters_id = combo.getFieldValue('RlsActmatters_id');
						var Clsdrugforms_id = combo.getFieldValue('RlsClsdrugforms_id');
						var DrugComplexMnn_Dose = combo.getFieldValue('DrugComplexMnn_Dose');

						if (!Ext.isEmpty(Actmatters_id)) {
							actmatters_combo.setValue(Actmatters_id);
							actmatters_combo.fireEvent('change', actmatters_combo, Actmatters_id);
						}
						if (!Ext.isEmpty(Clsdrugforms_id)) {
							clsdrugforms_combo.setValue(Clsdrugforms_id);
							clsdrugforms_combo.fireEvent('change', clsdrugforms_combo, Clsdrugforms_id);
						}
						if (!Ext.isEmpty(DrugComplexMnn_Dose)) {
							var match = /^([0-9\.]+)\s(\S+)$/.exec(DrugComplexMnn_Dose);
							if (match && match.length == 3) {
								var dose = match[1];
								var unit = match[2];

								base_form.findField('DrugListStr_Dose').setValue(dose);
								base_form.findField('GoodsUnit_did').setFieldValue('GoodsUnit_Nick', unit);
							}
						}
					}.createDelegate(this)
				},
				width: 300
			}, {
				xtype: 'swrlsactmatterscombo',
				hiddenName: 'Actmatters_id',
				fieldLabel: 'МНН',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var nonprop_names_combo = base_form.findField('DrugNonpropNames_id');

						nonprop_names_combo.getStore().baseParams.RlsActmatters_id = newValue;
						nonprop_names_combo.getStore().removeAll();
						nonprop_names_combo.lastQuest = null;

						if (!Ext.isEmpty(newValue)) {
							nonprop_names_combo.getStore().load({
								callback: function() {
									if (nonprop_names_combo.getStore().getCount() == 1) {
										var record = nonprop_names_combo.getStore().getAt(0);
										if (record && !Ext.isEmpty(record.get('DrugNonpropNames_id'))) {
											nonprop_names_combo.setValue(record.get('DrugNonpropNames_id'));
											nonprop_names_combo.setValue('change', nonprop_names_combo, record.get('DrugNonpropNames_id'));
										}
									}
								}
							});
						}
					}.createDelegate(this)
				},
				width: 300
			}, {
				xtype: 'swdrugnonpropnamescombo',
				hiddenName: 'DrugNonpropNames_id',
				fieldLabel: 'Непатентованное наименование',
				forceSelection: true,
				mode: 'remote',
				width: 300
			}, {
				xtype: 'swrlstradenamescombo',
				hiddenName: 'Tradenames_id',
				fieldLabel: 'Торговое наименование',
				width: 300
			}, {
				xtype: 'swrlsclsdrugformscombo',
				hiddenName: 'Clsdrugforms_id',
				fieldLabel: 'Лекарственная форма',
				width: 300
			}, {
				allowNegative: false,
				decimalPrecision: 3,
				xtype: 'numberfield',
				name: 'DrugListStr_Dose',
				fieldLabel: 'Дозировка',
				width: 300
			}, {
				xtype: 'swgoodsunitcombo',
				hiddenName: 'GoodsUnit_did',
				fieldLabel: 'Единица измерения',
				width: 300
			}, {
				xtype: 'textfield',
				name: 'DrugListStr_Comment',
				fieldLabel: 'Примечания',
				width: 300
			}, {
				xtype: 'fieldset',
				title: 'Норматив',
				autoHeight: true,
				style: 'padding: 0;',
				items: [{
					allowNegative: false,
					decimalPrecision: 3,
					xtype: 'numberfield',
					name: 'DrugListStr_Num',
					fieldLabel: 'Количество',
					width: 300
				}, {
					xtype: 'swgoodsunitcombo',
					hiddenName: 'GoodsUnit_nid',
					fieldLabel: 'Единица измерения',
					width: 300
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: function(){}
			}, [
				{name: 'DrugListStr_id'},
				{name: 'DrugList_id'},
				{name: 'DrugListStr_Name'},
				{name: 'DrugListGroup_id'},
				{name: 'Drug_id'},
				{name: 'DrugComplexMnn_id'},
				{name: 'Actmatters_id'},
				{name: 'DrugNonpropNames_id'},
				{name: 'Tradenames_id'},
				{name: 'Clsdrugforms_id'},
				{name: 'DrugListStr_Comment'},
				{name: 'DrugListStr_Dose'},
				{name: 'GoodsUnit_did'},
				{name: 'DrugListStr_Num'},
				{name: 'GoodsUnit_nid'}
			])
		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'DLEW_SaveButton',
					text: BTN_FRMSAVE
				}, {
					text: '-'
				},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swDrugListStrEditWindow.superclass.initComponent.apply(this, arguments);
	}
});