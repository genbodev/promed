/**
 * OneDrugFormPanel - форма добавления односоставного лек. назначения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.SpecificationDetail.OneDrugFormPanel', {
	/* свойства */
	alias: 'widget.OneDrugFormPanel',
	cls: 'OneDrugFormPanel',
	extend: 'Ext6.form.FormPanel',
	trackResetOnLoad: true,
	autoHeight: true,
	border: false,
	defaults: {
		labelAlign: 'right',
		border: false
	},
	bodyPadding: 16,
	showRp: true,
	url: '/?c=EvnPrescr&m=saveEvnCourseTreat',

	/**
	 * Задание текста способа применения вручную или событием с поля
	 * @param comp флаг события: 'force' - вручную из кода, либо тип component, если по событию с поля
	 * @returns {boolean}
	 */
	changeMethodText: function(comp){

		var me = this;
		if(me.parentPanel.setValuesMode)
			return false;
		var arrNames = [
			'PrescriptionIntroType_id', //Перорально
			'EvnPrescrTreat_Descr', //во время еды, запивая
			'EvnCourseTreat_Duration', // в течение 14
			'DurationType_id', //  дней
			'EvnCourseTreat_CountDay', // 2 раза в день
			'KolvoEd', // по 1
			'GoodsUnit_sid' // табл. за прием.
		];
		if((comp!='force' && comp.getName() && arrNames.indexOf(comp.getName()) != -1) || comp == 'force' ){
			var mpanel = me.MethodPanel,
				methodText = mpanel.down('#methodText'),
				base_form = me.getForm(),
				str_val = '';
			arrNames.forEach(function(e){
				if(e){
					var field = base_form.findField(e);
					if(!field) return false;
					var rec, v = field.getValue(), temp;
					if (v) {
						switch (e) {
							case 'PrescriptionIntroType_id':
								rec = field.getSelectedRecord();
								str_val += (rec?rec.get('PrescriptionIntroType_Name'):'');
								break;
							case 'EvnPrescrTreat_Descr':
								str_val += ', ' + v;
								break;
							case 'EvnCourseTreat_Duration':
								str_val += ', в течение ' + field.getRawValue();
								break;
							case 'DurationType_id':
								str_val += ' ' + field.getRawValue();
								break;
							case 'EvnCourseTreat_CountDay':
								temp = ' раз в день';
								if ([2, 3, 4].indexOf(v % 10) != -1)
									temp = ' раза в день';
								str_val += ', ' + v + temp;
								break;
							case 'KolvoEd':
								str_val += ' по ' + v;
								break;
							case 'GoodsUnit_sid':
								rec = field.getSelectedRecord();
								str_val += ' ' + (rec?rec.get('GoodsUnit_Nick'):v) + ' за приём';
								break;
						}
					}
				}
			});
			methodText.setHtml(str_val);
			if(me.parentPanel.modeReceipt && me.parentPanel.modeReceipt == 2)
				me.parentPanel.setSignaGeneralReceiptForm();
		}
		else{
			return false;
		}
	},

	setErrorPanelVisible: function(visible) {
		let me = this;
		let ErrorPanelIdObject = me.queryById(me.id + '-ErrorPanel');
		ErrorPanelIdObject.setHidden(visible);
	},

	getDrugFormData: function(forTemplate)
	{
		var base_form,
			win = this,
			data = {};
		base_form = win.getForm();
		var mnn = base_form.findField('DrugComplexMnn_id'),
			mnn_rec = mnn.getSelectedRecord(),
			KolvoEd = base_form.findField('KolvoEd'),
			GoodsUnit_sid = base_form.findField('GoodsUnit_sid');
		if(!mnn_rec || Ext6.isEmpty(KolvoEd.getValue()) || Ext6.isEmpty(GoodsUnit_sid.getValue())){
			return false;
		}

		var DrugForm_Name = '';
		DrugForm_Name = mnn_rec.get('RlsClsdrugforms_Name');


		data['Drug_Name'] = mnn.getRawValue();
		data['Drug_id'] = base_form.findField('Drug_id').getValue() ||null;
		data['DrugForm_Name'] = DrugForm_Name;
		data['DrugComplexMnn_id'] = mnn.getValue() || null;
		//data['DrugForm_Name'] = base_form.findField('DrugForm_Name').getValue();
		data['KolvoEd'] = base_form.findField('KolvoEd').getValue() || null;
		//data['DrugForm_Nick'] = thas.findById(thas.id +'_TreatDrugForm_Nick').text || null;
		data['Kolvo'] = base_form.findField('Kolvo').getValue() || null;
		//data['EdUnits_id'] = base_form.findField('EdUnits_id').getValue() || null;
		//data['EdUnits_Nick'] = base_form.findField('EdUnits_id').getRawValue() || null;
		data['GoodsUnit_id'] = base_form.findField('GoodsUnit_id').getValue() || null;
		data['GoodsUnit_Nick'] = base_form.findField('GoodsUnit_id').getRawValue() || null;
		//data['DrugComplexMnnDose_Mass'] = base_form.findField('DrugComplexMnnDose_Mass').getValue() || null;
		//data['DoseDay'] = base_form.findField('DoseDay').getValue() || null;
		//data['PrescrDose'] = base_form.findField('PrescrDose').getValue() || null;
		//data['GoodsUnit_id'] = base_form.findField('GoodsUnit_id').getValue() || null;
		data['GoodsUnit_sid'] = base_form.findField('GoodsUnit_sid').getValue() || null;
		data['GoodsUnit_SNick'] = base_form.findField('GoodsUnit_sid').getRawValue() || null;
		data['EvnCourseTreat_CountDay'] = base_form.findField('EvnCourseTreat_CountDay').getValue() || null;
		data['EvnCourseTreat_Duration'] = base_form.findField('EvnCourseTreat_Duration').getValue() || null;
		data['DurationType_id'] = base_form.findField('DurationType_id').getValue() || null;
		data['LatName'] = base_form.findField('LatName').getValue() || null;
		data['MethodInputDrug_id'] = data['Drug_id']?'2':'1';
		data['FactCount'] = 0;
		if(!forTemplate){
			var id = base_form.findField('id').getValue();
			if(!isNaN(parseInt(id)))
				data['id'] = id || '';
			log('id: '+data['id']);
			data['status'] = data['id']?'updated':'new';
		}
		data['EdUnits_id'] = null;
		data['EdUnits_Nick'] = null;
		data['DrugComplexMnnDose_Mass'] = null;
		data['DoseDay'] = (parseInt(data['Kolvo']) * parseInt(data['EvnCourseTreat_CountDay'])).toString()+data['GoodsUnit_Nick'];
		//data['PrescrDose'] = parseInt(data['Kolvo']) * parseInt(base_form.findField('EvnCourseTreat_CountDay').getValue())*(Продолжительность)*месяц(30дней)
		data['DrugForm_Nick'] =  base_form.findField('DrugComplexMnn_id').getSelectedRecord().get('RlsClsdrugforms_Name') || '';
		data = win.reCountData(data);

		return data
	},
	reCountData: function(data)
	{
		// Расчет суточной и курсовой доз
		var dd_text='', kd_text='', dd=0, kd=0, ed = '', multi = 1;

		//Дневная доза – Прием в ед. измерения (либо количество ед. дозировки*дозировку)*Приемов в сутки
		if ( data['Kolvo'] && data['GoodsUnit_id'] /*&& data['EdUnits_id']*/ ) {
			// в ед. измерения
			dd = data['EvnCourseTreat_CountDay']*data['Kolvo'];
			if (data['GoodsUnit_Nick']){
				dd_text = dd +' '+ data['GoodsUnit_Nick'];
				ed = data['GoodsUnit_Nick'];
			}
		}
		if (data['KolvoEd'] && !data['Kolvo']) {
			// в ед. дозировки только если не указано в ед.измерения
			dd = data['EvnCourseTreat_CountDay']*data['KolvoEd'];
			if (data['GoodsUnit_SNick']){
				dd_text = dd +' '+ data['GoodsUnit_SNick'];
				ed = data['GoodsUnit_SNick'];
			}
		}
		if (dd > 0 && data['EvnCourseTreat_Duration']>0) {
			switch (data['DurationType_id']) {
				case 1: // дней
					multi = 1;
					break;
				case 2: // недель
					multi = 7;
					break;
				case 3: // месяцев
					multi = 30;
					break;
			}
			kd = dd*data['EvnCourseTreat_Duration']*multi;
			kd_text=kd +' '+ ed;
		}
		data['DoseDay'] = dd_text;
		data['PrescrDose'] = kd_text;
		return data;
	},
	initComponent: function() {
		var me = this;
		Ext6.define(me.id + '_FormModel', {
			extend: 'Ext6.data.Model',
			fields: [
				{name: 'CourseType_id'},
				{name: 'DrugListData'},
				{name: 'DurationType_id'},
				{name: 'DurationType_intid'},
				{name: 'DurationType_recid'},
				{name: 'EvnCourseTreat_ContReception'},
				{name: 'EvnCourseTreat_CountDay'},
				{name: 'EvnCourseTreat_Duration'},
				{name: 'EvnCourseTreat_Interval'},
				{name: 'EvnCourseTreat_MaxCountDay'},
				{name: 'EvnCourseTreat_MinCountDay'},
				{name: 'EvnCourseTreat_PrescrCount'},
				{name: 'EvnCourseTreat_id'},
				{name: 'EvnCourseTreat_pid'},
				{name: 'EvnCourseTreat_setDate'},
				{name: 'EvnPrescrTreat_Descr'},
				{name: 'EvnPrescrTreat_IsCito'},
				{name: 'EvnReceptGeneralDrugLink_id'},
				{name: 'EvnReceptGeneral_id'},
				{name: 'LatName'},
				{name: 'LpuSection_id'},
				{name: 'Lpu_id'},
				{name: 'Morbus_id'},
				{name: 'PerformanceType_id'},
				{name: 'PersonEvn_id'},
				{name: 'PrescriptionIntroType_id'},
				{name: 'PrescriptionTreatType_id'},
				{name: 'ResultDesease_id'},
				{name: 'Server_id'},
				{name: 'accessType'}
			]
		});

		me.MethodPanel = Ext6.create('Ext6.panel.Panel', {
			title: 'СПОСОБ ПРИМЕНЕНИЯ',
			width: '100%',
			frame: true,
			userCls: 'mode-of-application',
			bodyPadding: '15 10',
			layout: {
				type: 'hbox'
			},
			defaults: {
				border: false
			},
			border: false,
			items: [
				{
					maxWidth: 450,
					defaults: {
						width: '100%',
						labelWidth: 140,
						padding: '5 0 0 0',
						margin: 0,
						listeners: {
							change: {
								fn: 'changeMethodText',
								scope: this
							}
						}
					},
					flex: 5,
					items: [{
						xtype: 'commonSprCombo',
						comboSubject: 'PrescriptionIntroType',
						sortField: 'PrescriptionIntroType_id',
						name: 'PrescriptionIntroType_id',
						fieldLabel: 'Способ применения',
						listConfig: {
							cls: 'choose-bound-list-menu update-scroller'
						},
						allowBlank: false,
						forceSelection: true
					}, {
						xtype: 'datefield',
						allowBlank: false,
						format: 'd.m.Y',
						value: new Date(),
						plugins: [new Ext6.ux.InputTextMask('99.99.9999', false)],
						fieldLabel: 'Начать',
						maxWidth: 251,
						name: 'EvnCourseTreat_setDate'
					}, {
						border: false,
						layout: {
							type: 'hbox'
						},
						defaults: {
							allowBlank: false,
							listeners: {
								change: {
									fn: 'changeMethodText',
									scope: this
								}
							}
						},
						items: [{
							xtype: 'numberfield',
							fieldLabel: 'Продолжительность',
							name: 'EvnCourseTreat_Duration',
							hideTrigger: true,
							//value: 1,
							minValue: 1,
							labelWidth: 140,
							width: 140+76
						}, {
							xtype: 'commonSprCombo',
							displayCode: false,
							comboSubject: 'DurationType',
							name: 'DurationType_id',
							moreFields: [{name: 'DurationType_Genitive', type: 'string'}],
							displayField: 'DurationType_Genitive',
							hiddenName: 'GoodsUnit_sid',
							padding: '0 16 0 9',
							width: 102
						}, {
							xtype: 'checkboxfield',
							boxLabel: 'До выписки',
							disabled: false,
							name: 'PayType_id',
							hidden: getCurArm().inlist(['polka', 'common'])
						}]
					},
						{
							xtype: 'numberfield',
							hideTrigger: true,
							name: 'EvnCourseTreat_CountDay',
							labelWidth: 140,
							fieldLabel: 'Приёмов в сутки',
							maxWidth: 328,
							allowBlank: false,
							listeners: {
								change: {
									fn: 'changeMethodText',
									scope: this
								}
							}
						}, {
							layout: {
								type: 'hbox'
							},
							defaults: {
								listeners: {
									change: {
										fn: 'changeMethodText',
										scope: this
									}
								}
							},
							border: false,
							items: [{
								name: 'KolvoEd',
								minValue: 0.0001,
								xtype: 'numberfield',
								hideTrigger: true,
								fieldLabel: 'Кол-во ЛС на прием',
								allowBlank: false,
								labelWidth: 140,
								flex: 1
							}, {
								xtype: 'commonSprCombo',
								displayCode: false,
								displayField: 'GoodsUnit_Nick',
								moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
								comboSubject: 'GoodsUnit',
								name: 'GoodsUnit_sid',
								hiddenName: 'GoodsUnit_sid',
								border: false,
								forceSelection: true,
								allowBlank: false,
								minHeight: 25,
								padding: '0 0 0 8',
								flex: 1,
								loadingText: 'Загрузка...',
								listConfig: {
									scrollable: 'y', height: 300, resizable: true, resizeHandles: "se",
									cls: 'choose-bound-list-menu update-scroller'
								}
							}]
						}, {
							bodyPadding: '0 0',
							layout: {
								type: 'hbox',
								align: 'stretch'
							},
							border: false,
							items: [{
								name: 'Kolvo',
								xtype: 'numberfield',
								hideTrigger: true,
								fieldLabel: 'Доза на прием',
								labelWidth: 140,
								width: 140+76,
								flex: 1
							}, {
								xtype: 'commonSprCombo',
								displayCode: false,
								comboSubject: 'GoodsUnit',
								displayField: 'GoodsUnit_Nick',
								moreFields: [{name: 'GoodsUnit_Nick', type: 'string'}],
								name: 'GoodsUnit_id',
								padding: '0 0 0 8',
								width: 102,
								forceSelection: true,
								loadingText: 'Загрузка...',
								listConfig: {
									scrollable: 'y', height: 300, resizable: true, resizeHandles: "se",
									cls: 'choose-bound-list-menu update-scroller'
								}
							}, {
								itemId: 'DoseDay_warnIcon',
								hidden: true,
								border: false,
								width: 25,
								html: '',
							}]
						}, {
							xtype: 'commonSprCombo',
							displayCode: false,
							comboSubject: 'PerformanceType',
							name: 'PerformanceType_id',
							forceSelection: true,
							fieldLabel: 'Исполнение'
						}, {
							xtype: 'textfield',
							fieldLabel: 'Комментарий',
							name: 'EvnPrescrTreat_Descr'
						}, {
							padding: '0 0 0 145',
							xtype: 'checkboxfield',
							boxLabel: 'Cito!',
							name: 'EvnPrescrTreat_IsCito'
						}]
				}, {
					layout: 'vbox',
					flex: 3,
					bodyPadding: '0 30 10 15',
					items: [
						{
							border: false,
							cls: 'method-description',
							itemId: 'methodText',
							minHeight: 67,
							maxWidth: 250,
							style:{
								'font':'400 12px/14px Roboto, Helvetica, Arial, Geneva, sans-serif',
								'color': '#333 !important'
							},
							html: '',
							getValue: function(){
								return this.html.toString();
							},
							reset: function () {
								this.setHtml('');
							}
						},
						{
							xtype: 'button',
							handler: function () {
								//win.setMode('one');
								var fields = me.MethodPanel.query('field');
								fields.forEach(function (e) {
									if (e && e.reset)
										e.reset();
								});
							},
							cls: 'button-secondary',
							text: 'СБРОС'
						}
					]
				}
			]
		});

		Ext6.apply(me, {
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: me.id + '_FormModel'
			}),
			items: [
				{
					xtype: 'hiddenfield',
					name: 'id'
				},
				{
					xtype: 'hiddenfield',
					name: 'EvnCourseTreat_id'
				},
				{
					xtype: 'hiddenfield',
					name: 'EvnReceptGeneral_id'
				},
				{
					xtype: 'hiddenfield',
					name: 'EvnReceptGeneralDrugLink_id'
				},
				{
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					defaults: {
						border: false,
						labelWidth: 75
					},
					items: [
						{
							layout: {
								type: 'hbox'
							},
							bodyPadding: '0 10 15 15',
							defaults: {
								border: false
							},
							items: [
								{
									layout: 'anchor',
									itemId: 'OneDrugAddFormPanel',
									maxWidth: 460,
									defaults: {
										anchor: '100%',
										padding: '5 0 0 0',
										margin: 0
									},
									flex: 5,
									items: [
										{
											xtype: 'swDrugComplexMnnCombo',
											readOnly: true,
											allowBlank: false,
											name: 'DrugComplexMnn_id'
										}, {
											xtype: 'textfield',
											fieldLabel: 'Rp',
											readOnly: true,
											name: 'LatName',
											hidden: !me.showRp
										}, {
											xtype: 'swDrugCombo',
											userCls: 'drugs-trade-name',
											name: 'Drug_id'
										}
									]
								}, {
									width: 260,
									height: 100,
									hidden: true,
									id: me.id + '-ErrorPanel',
									html: '<img src="img/icons/emk/panelicons/VarningAlertIcon.png"  style="float: left; margin-right: 13px">' + '<div style="overflow: auto">' + '<p>' + 'Внимание! У пациента выявлена аллергическая реакция на данный препарат!' + '</p>' + '</div>',
									cls: 'drug-allerg'
								}
							]
						},
						me.MethodPanel
					]
				}]
		});

		this.callParent(arguments);
	}
});

