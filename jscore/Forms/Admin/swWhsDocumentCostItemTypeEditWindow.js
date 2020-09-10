/**
 * swWhsDocumentCostItemTypeEditWindow - окно редактирования статьи расхода
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

sw.Promed.swWhsDocumentCostItemTypeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swWhsDocumentCostItemTypeEditWindow',
	maximizable: false,
	maximized: false,
	autoHeight: true,
	width: 720,
	minWidth: 720,

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

				if (!Ext.isEmpty(response_obj.WhsDocumentCostItemType_Code)) {
					base_form.findField('WhsDocumentCostItemType_Code').setValue(response_obj.WhsDocumentCostItemType_Code);
				}
			}.createDelegate(this),
			url: '/?c=Farmacy&m=generateWhsDocumentCostItemTypeCode'
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
					if (action.result.WhsDocumentCostItemType_id){
						base_form.findField('WhsDocumentCostItemType_id').setValue(action.result.WhsDocumentCostItemType_id);

						this.callback();
						this.hide();
					}
				}
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swWhsDocumentCostItemTypeEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		base_form.findField('WhsDocumentCostItemType_Nick').setContainerVisible(false);

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
				this.setTitle('Статья расхода: Добавление');
				this.enableEdit(true);
				base_form.findField('WhsDocumentCostItemType_Nick').setContainerVisible(true);

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle('Статья расхода: Редактирование');
					this.enableEdit(true);
					base_form.findField('WhsDocumentCostItemType_Nick').setContainerVisible(true);
				} else {
					this.setTitle('Статья расхода: Просмотр');
					this.enableEdit(false);
				}

				base_form.load({
					params: {
						WhsDocumentCostItemType_id: base_form.findField('WhsDocumentCostItemType_id').getValue()
					},
					url: '/?c=Farmacy&m=loadWhsDocumentCostItemTypeForm',
					success: function() {
						loadMask.hide();

						var DocNormative_id = base_form.findField('DocNormative_id').getValue();
						if (!Ext.isEmpty(DocNormative_id)) {
							base_form.findField('DocNormative_id').getStore().load({
								params: {DocNormative_id: DocNormative_id},
								callback: function() {
									base_form.findField('DocNormative_id').setValue(DocNormative_id);
								}
							});
						}

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
			id: 'WDCITEW_FormPanel',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 160,
			url: '/?c=Farmacy&m=saveWhsDocumentCostItemType',
			defaults: {
				width: 500
			},
			items: [{
				xtype: 'hidden',
				name: 'WhsDocumentCostItemType_id'
			}, {
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: 'Код',
				width: 120,
				name: 'WhsDocumentCostItemType_Code',
				onTriggerClick: function() {
					this.generateCode();
				}.createDelegate(this),
				triggerClass: 'x-form-plus-trigger',
				validateOnBlur: false,
				xtype: 'trigger'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'WhsDocumentCostItemType_Nick',
				fieldLabel: 'Ник'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'WhsDocumentCostItemType_Name',
				fieldLabel: 'Краткое наим.'
			}, {
				allowBlank: false,
				xtype: 'textarea',
				name: 'WhsDocumentCostItemType_FullName',
				fieldLabel: 'Полное наим.'
			},{
				xtype: 'swcommonsprcombo',
				comboSubject: 'PersonRegisterType',
				hiddenName: 'PersonRegisterType_id',
				fieldLabel: 'Регистр',
				allowSysNick: true,
				typeCode: 'int'
			}, {
				allowBlank: false,
				xtype: 'swdrugfinancecombo',
				hiddenName: 'DrugFinance_id',
				fieldLabel: 'Источник финансирования'
			}, {
				id: 'WDCITE_DocNormative_id',
				xtype: 'swdocnormativesearchcombo',
				hiddenName: 'DocNormative_id',
				fieldLabel: 'Нормативный акт'
			}, {
				layout: 'column',
				width: 580,
				items: [{
					layout: 'form',
					items: [{
						xtype: 'swcheckbox',
						name: 'WhsDocumentCostItemType_isDLO',
						fieldLabel: 'ЛЛО',
						listeners: {
							'check': function (comp,newvalue) {
								var win = this.ownerCt.ownerCt.ownerCt.ownerCt;
								if(newvalue == true && win.action != 'view'){
									this.ownerCt.ownerCt.findById('isPersonAllocation').enable();
									this.ownerCt.ownerCt.ownerCt.findById('isPrivilegeAllowed').enable();
									this.ownerCt.ownerCt.ownerCt.findById('WDCITE_DocNormative_id').setAllowBlank(false);
								} else {
									this.ownerCt.ownerCt.findById('isPersonAllocation').setValue(false);
									this.ownerCt.ownerCt.ownerCt.findById('isPrivilegeAllowed').setValue(false);
									this.ownerCt.ownerCt.findById('isPersonAllocation').disable();
									this.ownerCt.ownerCt.ownerCt.findById('isPrivilegeAllowed').disable();
									this.ownerCt.ownerCt.ownerCt.findById('WDCITE_DocNormative_id').setAllowBlank(true);
								}
							}
						}
					}]
				}, {
					layout: 'form',
					labelWidth: 265,
					items: [{
						xtype: 'swcheckbox',
						name: 'WhsDocumentCostItemType_isDrugRequest',
						fieldLabel: 'Проведение заявочных кампаний'
					}]
				}, {
					layout: 'form',
					items: [{
						id: 'isPersonAllocation',
						xtype: 'swcheckbox',
						name: 'WhsDocumentCostItemType_isPersonAllocation',
						fieldLabel: 'Персональная разнарядка',
						disabled: true
					}]
				}]
			}, {
                layout: 'column',
                width: 640,
                items: [{
                    layout: 'form',
                    labelWidth: 590,
                    items: [{
                        id: 'isPrivilegeAllowed',
                        xtype: 'swcheckbox',
                        name: 'WhsDocumentCostItemType_isPrivilegeAllowed',
                        fieldLabel: 'Разрешить всем МО выписку льготных рецептов из заявок главных внештатных специалистов при МЗ',
                        disabled: true
                    }]
                }]
            }, {
				layout: 'column',
				width: 580,
				items: [{
					layout: 'form',
					items: [{
						xtype: 'swdatefield',
						allowBlank: false,
						name: 'WhsDocumentCostItemType_begDate',
						fieldLabel: 'Начало'
					}]
				}, {
					layout: 'form',
					labelWidth: 100,
					items: [{
						xtype: 'swdatefield',
						name: 'WhsDocumentCostItemType_endDate',
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
				{name: 'WhsDocumentCostItemType_id'},
				{name: 'WhsDocumentCostItemType_Code'},
				{name: 'WhsDocumentCostItemType_Nick'},
				{name: 'WhsDocumentCostItemType_Name'},
				{name: 'WhsDocumentCostItemType_FullName'},
				{name: 'PersonRegisterType_id'},
				{name: 'DrugFinance_id'},
				{name: 'DocNormative_id'},
				{name: 'WhsDocumentCostItemType_isDLO'},
				{name: 'WhsDocumentCostItemType_isPersonAllocation'},
				{name: 'WhsDocumentCostItemType_isPrivilegeAllowed'},
				{name: 'WhsDocumentCostItemType_isDrugRequest'},
				{name: 'WhsDocumentCostItemType_begDate'},
				{name: 'WhsDocumentCostItemType_endDate'},
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
						id: 'WDCITEW_SaveButton',
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

		sw.Promed.swWhsDocumentCostItemTypeEditWindow.superclass.initComponent.apply(this, arguments);
	}
});