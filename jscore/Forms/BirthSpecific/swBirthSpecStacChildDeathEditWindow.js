/**
 * swBirthSpecStacChildDeathEditWindow - форма редактирования сведений о мертворожденном
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   gabdushev
 * @version	  26.09.2011
 * @comment	  Префикс для id компонентов BSSCDEF (BirthSpecStacChildDeathEditForm)
 */

sw.Promed.swBirthSpecStacChildDeathEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		//log(options);
		// options @Object
		// options.ignoreWeightIsIncorrect @Boolean Признак игнорирования проверки правильности ввода массы
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';
		var form_panel = this.FormPanel;
		var base_form = this.FormPanel.getForm();
		switch (base_form.findField('Okei_wid').getValue()) {
			case 36://граммы
				base_form.findField('ChildDeath_Weight').maxValue=9999;
				break;
			case 37://килограммы
				base_form.findField('ChildDeath_Weight').maxValue=9.999;
				break;
			default:
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('Okei_wid').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Неправильное значение единицы измерения массы',
					title: 'Выбрано неправильное значение единицы измерения массы'
				});
				return false;
		}
		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form_panel.getFirstInvalidEl().focus(false);
					log(["sdfsd",form_panel.getFirstInvalidEl()])
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		//loadMask.show();
		var data = new Object();
		//todo: сделать в цикле getDisplayName
		var MedStaffFactField = base_form.findField('MedStaffFact_id');
		var MedStaffFactName = MedStaffFactField.getStore().getById((MedStaffFactField.getValue())).data['MedPersonal_Fio'];
		var DiagField = base_form.findField('Diag_id');
		var DiagName = DiagField.getStore().getById((DiagField.getValue())).data['Diag_Name'];
		var sexField = base_form.findField('Sex_id');
		var sexName = sexField.getStore().getById((sexField.getValue())).data['Sex_Name'];
		var OkeiField = base_form.findField('Okei_wid');
		data.birthSpecStacChildDeathData = {
			'ChildDeath_id': base_form.findField('ChildDeath_id').getValue(),
			'LpuSection_id': base_form.findField('LpuSection_id').getValue(),
			'MedStaffFact_id': base_form.findField('MedStaffFact_id').getValue(),
			'MedStaffFact_Name': MedStaffFactName,
			'Diag_id': base_form.findField('Diag_id').getValue(),
			'Diag_Name': DiagName,
			'Sex_id': base_form.findField('Sex_id').getValue(),
			'Sex_Name': sexName,
			'PntDeathTime_id': base_form.findField('PntDeathTime_id').getValue(),
			'ChildTermType_id': base_form.findField('ChildTermType_id').getValue(),
			'ChildDeath_Weight': base_form.findField('ChildDeath_Weight').getValue(),
			'ChildDeath_Weight_text': base_form.findField('ChildDeath_Weight').getValue() + ' ' + OkeiField.getStore().getById(OkeiField.getValue()).get('Okei_NationSymbol'),
			'Okei_wid': OkeiField.getValue(),
			'ChildDeath_Height': base_form.findField('ChildDeath_Height').getValue(),
			'ChildDeath_Count': base_form.findField('ChildDeath_Count').getValue(),
			'BirthSvid_id': base_form.findField('BirthSvid_id').getValue(),
			'BirthSvid_Num': base_form.findField('BirthSvid_Num').getValue()
		};
		loadMask.hide();
		this.callback(data);
		this.hide();
		return true;
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
				'ChildDeath_id',
				'LpuSection_id',
				'MedStaffFact_id',
				'Diag_id',
				'Sex_id',
				'PntDeathTime_id',
				'ChildTermType_id',
				'ChildDeath_Weight',
				'Okei_wid',
				'ChildDeath_Height',
				'ChildDeath_Count'
		);
		var i = 0;

		for (i = 0; i < form_fields.length; i++) {
			if (enable) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if (enable) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'BirthSpecStacChildDeathEditWindow',
	initComponent: function() {
		var that = this;
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'BirthSpecStacChildDeathEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			reader: new Ext.data.JsonReader(
					{
						success: Ext.emptyFn
					},
					[
						 { name:'ChildDeath_id'     },
						 { name:'LpuSection_id'     },
						 { name:'MedStaffFact_id'   },
						 { name:'Diag_id'           },
						 { name:'Sex_id'            },
						 { name:'PntDeathTime_id'   },
						 { name:'ChildTermType_id'  },
						 { name:'ChildDeath_Weight' },
						 { name:'Okei_wid' },
						 { name:'ChildDeath_Height' },
						 { name:'ChildDeath_Count'  }
					 ]
			),
			url: '/?c=PersonWeight&m=savePersonWeight',
			items: [
				{
					name: 'ChildDeath_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'BirthSvid_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'BirthSvid_Num',
					value: 0,
					xtype: 'hidden'
				},
				{
					title: 'Врач, установивший смерть',
					xtype: 'fieldset',
					autoHeight: true,
					style: 'padding: 5px 0;',
					items: [
						{
							allowBlank: false,
							hiddenName: 'LpuSection_id',
							id: 'BSSCDEF_LpuSectionCombo',
							linkedElements: [
								'BSSCDEF_MedStaffFactCombo'
							],
							tabIndex: TABINDEX_BSSCDEF + 1,
							width: 400,
							xtype: 'swlpusectionglobalcombo',
							listeners: {
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.TAB) {
										if (e.shiftKey) {
											e.stopEvent();
											if (!that.buttons[that.buttons.length - 1].hidden) {
												that.buttons[that.buttons.length - 1].focus(true);
											}
										}
									}
								}
							}
						},
						{
							allowBlank: false,
							fieldLabel: 'Врач',
							hiddenName: 'MedStaffFact_id',
							id: 'BSSCDEF_MedStaffFactCombo',
							listWidth: 650,
							parentElementId: 'BSSCDEF_LpuSectionCombo',
							tabIndex: TABINDEX_BSSCDEF + 2,
							width: 400,
							xtype: 'swmedstafffactglobalcombo'
						}
					]
				},
				{
					allowBlank: false,
					fieldLabel: 'Диагноз',
					hiddenName: 'Diag_id',
					id: 'BSSCDEF_DiagCombo',
					tabIndex: TABINDEX_BSSCDEF + 3,
					width: 400,
					xtype: 'swdiagcombo'
				},
				{
					hiddenName: 'Sex_id',
                    allowBlank: false,
					tabIndex: TABINDEX_BSSCDEF + 5,
					fieldLabel: 'Пол',
					width: 150,
					xtype: 'swpersonsexcombo'
				},
				{
                    allowBlank: false,
                    hiddenName: 'PntDeathTime_id',
					tabIndex: TABINDEX_BSSCDEF + 6,
					comboSubject: 'PntDeathTime',
					lastQuery: '',
					fieldLabel: 'Наступление смерти',
					//value:1,
					width: 250,
					xtype: 'swcommonsprcombo'
				},
				{
                    allowBlank: false,
                    comboSubject: 'ChildTermType',
					fieldLabel: 'Доношеный',
					hiddenName: 'ChildTermType_id',
					tabIndex: TABINDEX_BSSCDEF + 7,
					width: 250,
					xtype: 'swcustomsvidcombo'
				},
				{
					layout: 'column',
					items:[{
							layout:'form',
							labelWidth: 130,
							items: [
								{
									allowDecimals: true,
									allowNegative: false,
									maxValue: 9999,
									minValue: 0,
									fieldLabel: 'Масса',
									tabIndex: TABINDEX_BSSCDEF + 8,
									name: 'ChildDeath_Weight',
									width: 50,
									xtype: 'numberfield',
									style: 'text-align: right;'
								}
							]
						},{
							layout:'form',
							items: [
								{
									hideLabel: true,
									allowBlank: false,
									width: 60,
									value: 37,
									tabIndex: TABINDEX_BSSCDEF + 8,
									hiddenName: 'Okei_wid',
									loadParams: {params: {where: ' where Okei_id in (36,37)'}},
									xtype: 'swokeicombo',
									listeners: {
										'change': function (){
											switch (that.form.findField('Okei_wid').getValue()) {
												case 36://граммы
													that.form.findField('ChildDeath_Weight').maxValue = 9999;
													break;
												case 37://килограммы
													that.form.findField('ChildDeath_Weight').maxValue = 9.999;
													break;
												default:
													sw.swMsg.show({
														buttons: Ext.Msg.OK,
														fn: function() {
															that.formStatus = 'edit';
															that.form.findField('Okei_wid').focus(false);
														},
														icon: Ext.Msg.WARNING,
														msg: 'Неправильное значение единицы измерения массы',
														title: 'Выбрано неправильное значение единицы измерения массы'
													});
											}
										}
									}
								}
							]
						}
					]
				},
				{
					allowDecimals: false,
                    allowBlank: false,
                    allowNegative: false,
					maxValue: 99,
					minValue: 0,
					tabIndex: TABINDEX_BSSCDEF + 9,
					fieldLabel: 'Рост, см',
					name: 'ChildDeath_Height',
					width: 100,
					xtype: 'numberfield'
				},
				{
                    allowBlank: false,
                    allowDecimals: false,
					allowNegative: false,
					fieldLabel: 'Который по счету',
					tabIndex: TABINDEX_BSSCDEF + 10,
					name: 'ChildDeath_Count',
					width: 100,
					xtype: 'numberfield',
					minValue: 0
				}
			]
		});

		Ext.apply(this, {
			buttons: [
				{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					onShiftTabAction: function () {
						var base_form = this.FormPanel.getForm();

						if (this.action == 'view') {
							this.buttons[this.buttons.length - 1].focus(true);
						}
						else if (!base_form.findField('ChildDeath_Count').disabled) {
							base_form.findField('ChildDeath_Count').focus();
						}
					}.createDelegate(this),
					onTabAction: function () {
						this.buttons[this.buttons.length - 1].focus(true);
					}.createDelegate(this),
					tabIndex: TABINDEX_BSSCDEF + 11,
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						if (!this.buttons[0].hidden) {
							this.buttons[0].focus(true);
						}
					}.createDelegate(this),
					onTabAction: function () {
						if (!this.FormPanel.getForm().findField('LpuSection_id').disabled) {
							this.FormPanel.getForm().findField('LpuSection_id').focus(true);
						}
						else if (!this.buttons[0].hidden) {
							this.buttons[0].focus(true);
						}
					}.createDelegate(this),
					tabIndex: TABINDEX_BSSCDEF + 12,
					text: BTN_FRMCANCEL
				}
			],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});
		sw.Promed.swBirthSpecStacChildDeathEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('BirthSpecStacChildDeathEditForm').getForm();
	},
	keys: [
		{
			alt: true,
			fn: function(inp, e) {
				var current_window = Ext.getCmp('BirthSpecStacChildDeathEditWindow');

				switch (e.getKey()) {
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
			scope: this,
			stopEvent: true
		}
	],
	listeners: {
		'beforehide': function(win) {
			// 
		},
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
	show: function() {
		sw.Promed.swBirthSpecStacChildDeathEditWindow.superclass.show.apply(this, arguments);
		var base_form = this.FormPanel.getForm();
		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		//return;
		//todo: падает при первом открытии. Надо устанавливать фильтр на уже загруженном комбо. upd1: сделал на таймерах. решение временное, надо разобраться.
		/*setTimeout(function (){
			base_form.findField('PntDeathTime_id').setFilter([1,2]);
		}, 100);*/
			var PntDeathTime_id = 1;
		if(arguments[0].formParams['PntDeathTime_id']){
			 PntDeathTime_id = arguments[0].formParams['PntDeathTime_id'];
		}
		base_form.findField('PntDeathTime_id').lastQuery = "";
		base_form.findField('PntDeathTime_id').getStore().filterBy(function(rec){
			
				return rec.get('PntDeathTime_Code').inlist([1,2]);
				
			
		});
		base_form.findField('PntDeathTime_id').setValue(PntDeathTime_id);
		
			base_form.findField('LpuSection_id').focus(true,250);

		
		this.center();
		base_form.reset();
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.measureTypeExceptions = new Array();
		this.onHide = Ext.emptyFn;
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		switch (this.action) {
			case 'add':
				this.setTitle('Сведения о мертворожденном: Добавление');
				//this.enableEdit(true);
				if (arguments[0].formParams){
					//todo: надо избавиться от таймера, который используется для установки значения, когда удастся добиться нормальной загрузки этого комбо
					var ChildTermType_id = arguments[0].formParams['ChildTermType_id'];
					
						base_form.findField('ChildTermType_id').setValue(ChildTermType_id);
					
					//alert(arguments[0].formParams.ChildDeath_Count);
					if (arguments[0].formParams.ChildDeath_Count) {
						base_form.findField('ChildDeath_Count').setValue(arguments[0].formParams.ChildDeath_Count);
					}
				}
				break;
			case 'view':
			case 'edit':
				
				
				if(this.action == "edit"){
					this.setTitle('Сведения о мертворожденном: Редактирование');
					this.enableEdit(true);}
				else{
					this.setTitle('Сведения о мертворожденном: Просмотр');
					this.enableEdit(false);
					this.buttons[this.buttons.length - 1].focus();
				}
				if (arguments[0].formParams) {
					base_form.setValues(arguments[0].formParams);
					
					//todo: надо избавиться от таймера для установки значения, когда удастся добиться нормальной загрузки этого комбо
					
					var diag_id = arguments[0].formParams['Diag_id'];
					if (diag_id != null && diag_id.toString().length > 0) {
						base_form.findField('Diag_id').getStore().load({
							callback: function() {
								base_form.findField('Diag_id').getStore().each(function(record) {
									if (record.get('Diag_id') == diag_id) {
										base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
									}
								});
							},
							params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
						});
					}
					base_form.clearInvalid();
				}
				base_form.findField('LpuSection_id').focus(true, 250);
				break;
			default:
				this.hide();
				break;
		}
	},
	width: 600
});