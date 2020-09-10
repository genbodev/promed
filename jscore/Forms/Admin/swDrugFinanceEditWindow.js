/**
 * swDrugFinanceEditWindow - окно редактирования источника финансирование
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.02.2016
 */

/*NO PARSE JSON*/

sw.Promed.swDrugFinanceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugFinanceEditWindow',
	width: 450,
	minWidth: 450,
	autoHeight: true,
	modal: true,

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
					if (action.result.DrugFinance_id){
						base_form.findField('DrugFinance_id').setValue(action.result.DrugFinance_id);

						this.callback();
						this.hide();
					}
				}
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swDrugFinanceEditWindow.superclass.show.apply(this, arguments);

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

		base_form.findField('DrugFinance_SysNick').setContainerVisible(this.action.inlist(['add','edit']));
		this.syncShadow();

		base_form.items.each(function(f){f.validate()});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle('Источник финансирования: Добавление');
				this.enableEdit(true);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Источник финансирования: Редактирование');
					this.enableEdit(true);
				} else {
					this.setTitle('Источник финансирования: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					params: {
						DrugFinance_id: base_form.findField('DrugFinance_id').getValue()
					},
					url: '/?c=Farmacy&m=loadDrugFinanceForm',
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
			id: 'DFEW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 100,
			url: '/?c=Farmacy&m=saveDrugFinance',
			defaults: {
				width: 275
			},
			items: [{
				xtype: 'hidden',
				name: 'DrugFinance_id'
			}, {
				allowBlank: false,
				allowDecimal: false,
				allowNegative: false,
				xtype: 'numberfield',
				name: 'DrugFinance_Code',
				fieldLabel: 'Код',
				width: 120
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'DrugFinance_Name',
				fieldLabel: 'Наименование'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'DrugFinance_SysNick',
				fieldLabel: 'Ник'
			}, {
				layout: 'column',
				width: 400,
				items: [{
					layout: 'form',
					items: [{
						xtype: 'swdatefield',
						name: 'DrugFinance_begDate',
						fieldLabel: 'Начало'
					}]
				}, {
					layout: 'form',
					labelWidth: 78,
					items: [{
						xtype: 'swdatefield',
						name: 'DrugFinance_endDate',
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
				{name: 'DrugFinance_id'},
				{name: 'DrugFinance_Code'},
				{name: 'DrugFinance_Name'},
				{name: 'DrugFinance_SysNick'},
				{name: 'DrugFinance_begDate'},
				{name: 'DrugFinance_endDate'},
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
						id: 'DFEW_SaveButton',
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

		sw.Promed.swDrugFinanceEditWindow.superclass.initComponent.apply(this, arguments);
	}
});