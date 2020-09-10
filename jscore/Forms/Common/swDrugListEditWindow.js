/**
 * swDrugListEditWindow - окно редактирования перечня медикаментов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.10.2017
 */
/*NO PARSE JSON*/

sw.Promed.swDrugListEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugListEditWindow',
	layout: 'form',
	title: 'Перечень медикаментов',
	modal: true,
	autoHeight: true,
	width: 480,

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
				base_form.findField('DrugList_id').setValue(action.result.DrugList_id);

				this.callback();
				this.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swDrugListEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.ARMType = null;
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();

		base_form.reset();

		if (arguments && arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		if (arguments && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var publisher_combo = base_form.findField('DrugListObj_id');
		var country_combo = base_form.findField('KLCountry_id');
		var region_combo = base_form.findField('Region_id');

		country_combo.setContainerVisible(this.ARMType == 'superadmin');
		region_combo.setContainerVisible(this.ARMType == 'superadmin');
		this.syncShadow();

		publisher_combo.getStore().baseParams.isPublisher = 1;
		if (this.ARMType == 'orgadmin') {
			publisher_combo.getStore().baseParams.Org_id = getGlobalOptions().org_id;
		}
		if (this.ARMType == 'lpuadmin') {
			publisher_combo.getStore().baseParams.Lpu_oid = getGlobalOptions().lpu_id;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет загрузка..."});
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle('Перечень медикаментов: Добавление');
				this.enableEdit(true);

				base_form.findField('DrugList_begDate').setValue(new Date());

				var region = getRegionNumber();
				switch(region) {
					case 101:
						country_combo.setValue(398);
						break;
					case 201:
						country_combo.setValue(112);
						break;
					default:
						if (String(region).length == 2) {
							country_combo.setValue(643);
							region_combo.setValue(region);
						}
						break;
				}
				country_combo.fireEvent('change', country_combo, country_combo.getValue());
				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Перечень медикаментов: Изменение');
					this.enableEdit(true);
				} else {
					this.setTitle('Перечень медикаментов: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					url: '/?c=DrugList&m=loadDrugListForm',
					params: {
						DrugList_id: base_form.findField('DrugList_id').getValue()
					},
					success: function() {
						loadMask.hide();

						if (!Ext.isEmpty(publisher_combo.getValue())) {
							publisher_combo.getStore().load({
								params: {DrugListObj_id: publisher_combo.getValue()},
								callback: function() {
									publisher_combo.setValue(publisher_combo.getValue());
								}
							});
						}

						country_combo.fireEvent('change', country_combo, country_combo.getValue());
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
			url: '/?c=DrugList&m=saveDrugList',
			items: [{
				xtype: 'hidden',
				name: 'DrugList_id'
			}, {
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					items: [{
						allowBlank: false,
						xtype: 'swdatefield',
						name: 'DrugList_begDate',
						fieldLabel: 'Начало',
						width: 100
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 95,
					items: [{
						xtype: 'swdatefield',
						name: 'DrugList_endDate',
						fieldLabel: 'Окончание',
						width: 100
					}]
				}]
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'DrugList_Name',
				fieldLabel: 'Наименование',
				width: 300
			}, {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'DrugListType',
				hiddenName: 'DrugListType_id',
				fieldLabel: 'Тип',
				width: 300
			}, {
				xtype: 'swdocnormativesearchcombo',
				hiddenName: 'DocNormative_id',
				displayField: 'DocNormative_Num',
				fieldLabel: 'Документ',
				width: 300,
				listWidth: 400
			}, {
				xtype: 'swdruglistobjcombo',
				hiddenName: 'DrugListObj_id',
				fieldLabel: 'Издатель',
				withoutTrigger: false,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{DrugListObj_PublisherNick}&nbsp;',
					'</div></tpl>'
				),
				width: 300
			}, {
				allowBlank: false,
				xtype: 'swklcountrycombo',
				hiddenName: 'KLCountry_id',
				fieldLabel: 'Страна',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						var region_combo = base_form.findField('Region_id');

						if (Ext.isEmpty(newValue)) {
							region_combo.setValue(null);
							region_combo.getStore().removeAll();
						} else {
							region_combo.getStore().load({
								params: {country_id: newValue},
								callback: function() {
									region_combo.setValue(region_combo.getValue());
								}
							});
						}
					}.createDelegate(this)
				},
				width: 300
			}, {
				xtype: 'swregioncombo',
				hiddenName: 'Region_id',
				fieldLabel: 'Регион',
				width: 300
			}],
			reader: new Ext.data.JsonReader({
				success: function(){}
			}, [
				{name: 'DrugList_id'},
				{name: 'DrugList_begDate'},
				{name: 'DrugList_endDate'},
				{name: 'DrugList_Name'},
				{name: 'DrugListType_id'},
				{name: 'DocNormative_id'},
				{name: 'DrugListObj_id'},
				{name: 'KLCountry_id'},
				{name: 'Region_id'}
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

		sw.Promed.swDrugListEditWindow.superclass.initComponent.apply(this, arguments);
	}
});