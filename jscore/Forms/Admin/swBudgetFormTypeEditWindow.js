/**
 * swBudgetFormTypeEditWindow - окно редактирования целевой статьи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.02.2016
 */

/*NO PARSE JSON*/

sw.Promed.swBudgetFormTypeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swBudgetFormTypeEditWindow',
	width: 540,
	minWidth: 540,
	autoHeight: true,
	modal: true,

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

				if (!Ext.isEmpty(response_obj.BudgetFormType_Code)) {
					base_form.findField('BudgetFormType_Code').setValue(response_obj.BudgetFormType_Code);
				}
			}.createDelegate(this),
			url: '/?c=Farmacy&m=generateBudgetFormTypeCode'
		});
	},

	doSave: function() {
		var wnd = this;

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
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
					if (action.result.BudgetFormType_id){
						base_form.findField('BudgetFormType_id').setValue(action.result.DrugFinance_id);

						this.callback();
						this.hide();
					}
				}
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swBudgetFormTypeEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		base_form.findField('Region_id').setDisabled(!isDebug());
		base_form.findField('Region_id').setValue(getRegionNumber());

		if (arguments[0] && arguments[0].action && isSuperAdmin()) {
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
				this.setTitle('Целевая статья: Добавление');
				this.enableEdit(true);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Целевая статья: Редактирование');
					this.enableEdit(true);
				} else {
					this.setTitle('Целевая статья: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					params: {
						BudgetFormType_id: base_form.findField('BudgetFormType_id').getValue()
					},
					url: '/?c=Farmacy&m=loadBudgetFormTypeForm',
					success: function() {
						loadMask.hide();


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
			id: 'BFTEW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 165,
			url: '/?c=Farmacy&m=saveBudgetFormType',
			defaults: {
				width: 275
			},
			items: [{
				xtype: 'hidden',
				name: 'BudgetFormType_id'
			}, {
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: 'Код',
				width: 120,
				name: 'BudgetFormType_Code',
				onTriggerClick: function() {
					this.generateCode();
				}.createDelegate(this),
				triggerClass: 'x-form-plus-trigger',
				validateOnBlur: false,
				xtype: 'trigger'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'BudgetFormType_Name',
				fieldLabel: 'Наименование (им.падеж)'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'BudgetFormType_NameGen',
				fieldLabel: 'Наименование (род.падеж)'
			}, {
				layout: 'column',
				width: 480,
				items: [{
					layout: 'form',
					items: [{
						xtype: 'swdatefield',
						name: 'BudgetFormType_begDate',
						fieldLabel: 'Начало'
					}]
				}, {
					layout: 'form',
					labelWidth: 78,
					items: [{
						xtype: 'swdatefield',
						name: 'BudgetFormType_endDate',
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
				{name: 'BudgetFormType_id'},
				{name: 'BudgetFormType_Code'},
				{name: 'BudgetFormType_Name'},
				{name: 'BudgetFormType_NameGen'},
				{name: 'BudgetFormType_begDate'},
				{name: 'BudgetFormType_endDate'},
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
						id: 'BFTEW_SaveButton',
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

		sw.Promed.swBudgetFormTypeEditWindow.superclass.initComponent.apply(this, arguments);
	}
});