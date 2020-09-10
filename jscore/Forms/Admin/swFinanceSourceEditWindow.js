/**
 * swFinanceSourceEditWindow - окно редактирования финансирования контрактов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.02.2016
 */

/*NO PARSE JSON*/

sw.Promed.swFinanceSourceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swFinanceSourceEditWindow',
	maximizable: false,
	maximized: false,
	width: 780,
	minWidth: 780,
	autoHeight: true,

	generateCode: function() {
		var params = {};
		var base_form = this.FormPanel.getForm();

		var Mask = new Ext.LoadMask(Ext.get(this.id), { msg: "Получение кода..." });
		Mask.show();

		Ext.Ajax.request({
			params: params,
			callback: function(opt, success, resp) {
				Mask.hide();

				var response_obj = Ext.util.JSON.decode(resp.responseText);

				if (!Ext.isEmpty(response_obj.FinanceSource_Code)) {
					base_form.findField('FinanceSource_Code').setValue(response_obj.FinanceSource_Code);
				}
			}.createDelegate(this),
			url: '/?c=Farmacy&m=generateFinanceSourceCode'
		});
	},

	doSave: function() {
		var wnd = this;

		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var begDate = base_form.findField('FinanceSource_begDate').getValue()
		var endDate = base_form.findField('FinanceSource_endDate').getValue()
		if (!Ext.isEmpty(endDate) && begDate > endDate) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					base_form.findField('FinanceSource_begDate').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Дата окончания не может быть меньше даты начала',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action)
			{
				loadMask.hide();

			}.createDelegate(this),
			success: function(result_form, action)
			{
				loadMask.hide();
				if (action.result){
					if (action.result.FinanceSource_id){
						base_form.findField('FinanceSource_id').setValue(action.result.FinanceSource_id);

						this.callback();
						this.hide();
					}
				}
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swFinanceSourceEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		base_form.findField('Region_id').setDisabled(!isDebug());
		base_form.findField('Region_id').setValue(getRegionNumber());

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		base_form.items.each(function(f){f.validate()});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle('Финансирование контрактов: Добавление');
				this.enableEdit(true);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Финансирование контрактов: Редактирование');
					this.enableEdit(true);
				} else {
					this.setTitle('Финансирование контрактов: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					params: {
						FinanceSource_id: base_form.findField('FinanceSource_id').getValue()
					},
					url: '/?c=Farmacy&m=loadFinanceSourceForm',
					success: function() {
						loadMask.hide();

						var drug_finance_combo = base_form.findField('DrugFinance_id');
						var cost_item_type_combo = base_form.findField('WhsDocumentCostItemType_id');
						var budget_form_type_combo = base_form.findField('BudgetFormType_id');

						drug_finance_combo.getStore().load({
							callback: function() {
								drug_finance_combo.setValue(drug_finance_combo.getValue());
							}
						});
						cost_item_type_combo.getStore().load({
							callback: function() {
								cost_item_type_combo.setValue(cost_item_type_combo.getValue());
							}
						});
						budget_form_type_combo.getStore().load({
							callback: function() {
								budget_form_type_combo.setValue(budget_form_type_combo.getValue());
							}
						});

					}.createDelegate(this),
					failure: function() {
						loadMask.hide();
					}
				});

				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			autoHeight: true,
			id: 'FSEW_FormPanel',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 150,
			url: '/?c=Farmacy&m=saveFinanceSource',
			defaults: {
				width: 580
			},
			items: [{
				xtype: 'hidden',
				name: 'FinanceSource_id'
			}, {
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: 'Код',
				width: 120,
				name: 'FinanceSource_Code',
				onTriggerClick: function() {
					this.generateCode();
				}.createDelegate(this),
				triggerClass: 'x-form-plus-trigger',
				validateOnBlur: false,
				xtype: 'trigger'
			}, {
				allowBlank: false,
				xtype: 'swdrugfinanceremotecombo',
				hiddenName: 'DrugFinance_id',
				fieldLabel: 'Бюджет',
			}, {
				allowBlank: false,
				xtype: 'swwhsdocumentcostitemtyperemotecombo',
				hiddenName: 'WhsDocumentCostItemType_id',
				fieldLabel: 'Статья расхода'
			}, {
				allowBlank: false,
				xtype: 'swbudgetformtypecombo',
				hiddenName: 'BudgetFormType_id',
				fieldLabel: 'Целевая статья'
			}, {
				allowBlank: false,
				xtype: 'textarea',
				name: 'FinanceSource_Name',
				fieldLabel: 'Полное официальное наименование источника финансирования',
				height: 80
			}, {
				allowBlank: false,
				xtype: 'textarea',
				name: 'FinanceSource_SuppName',
				fieldLabel: 'Наименование контракта',
				height: 80
			}, {
				layout: 'column',
				width: 580,
				items: [{
					layout: 'form',
					items: [{
						allowBlank: false,
						xtype: 'swdatefield',
						name: 'FinanceSource_begDate',
						fieldLabel: 'Начало'
					}]
				}, {
					layout: 'form',
					labelWidth: 100,
					items: [{
						xtype: 'swdatefield',
						name: 'FinanceSource_endDate',
						fieldLabel: 'Окончание'
					}]
				}]
			}, {
				xtype: 'swpromedregioncombo',
				hiddenName: 'Region_id',
				fieldLabel: 'Регион',
				disabled: true
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'FinanceSource_id'},
				{name: 'FinanceSource_Code'},
				{name: 'FinanceSource_Name'},
				{name: 'FinanceSource_SuppName'},
				{name: 'DrugFinance_id'},
				{name: 'WhsDocumentCostItemType_id'},
				{name: 'BudgetFormType_id'},
				{name: 'FinanceSource_begDate'},
				{name: 'FinanceSource_endDate'},
				{name: 'Region_id'}
			])
		});

		Ext.apply(this,
			{
				buttons: [
					{
						handler: function () {
							this.doSave();
						}.createDelegate(this),
						iconCls: 'save16',
						id: 'FSEW_SaveButton',
						text: BTN_FRMSAVE
					},
					{
						text: '-'
					},
					HelpButton(this),
					{
						handler: function()
						{
							this.hide();
						}.createDelegate(this),
						iconCls: 'cancel16',
						text: BTN_FRMCLOSE
					}
				],
				items: [this.FormPanel]
			});

		sw.Promed.swFinanceSourceEditWindow.superclass.initComponent.apply(this, arguments);
	}
});