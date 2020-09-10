Ext6.define('DrugViewModel', {
	extend: 'Ext6.app.ViewModel',
	alias: 'viewmodel.DrugViewModel',

	data: {
		EvnDrug_id: null,
		EvnDrug_setDate: new Date(),
		EvnDrug_setTime: new Date().format('H:i'),
		Person_id: null,
		EvnCourse_id: null,
		EvnPrescr_id: null,
		EvnCourseTreatDrug_id: null,
		EvnPrescrTreatDrug_id: null,
		EvnPrescrTreat_Fact: null,
		PrescrFactCountDiff: null,
		EvnDrug_pid: null,
		EvnDrug_rid: null,
		Server_id: null,
		PersonEvn_id: null,
		Drug_id: null,
		DrugPrepFas_id: null,
		LpuSection_id: null,
		EvnDrug_Price: null,
		//EvnDrug_Sum: null,
		DocumentUc_id: null,
		DocumentUcStr_id: null,
		DocumentUcStr_oid: null,
		Storage_id: null,
		Mol_id: null,
		EvnDrug_Kolvo: null,
		EvnDrug_KolvoEd: null,
		GoodsUnit_id: null,
		Diag_id: null,
		loading: false,

		DrugRecord: {}
	},
	formulas: {
		DrugRecord: function (get)
		{
			var Drug_id = get('Drug_id'),
				formPanel = this.getView(),
				form = formPanel.getForm(),
				Drug = form.findField('Drug_id');

			return Drug.getStore().getById(Drug_id) || {
				Drug_Fas: '', // 1
				DrugForm_Name: '',
				GoodsUnit_bid: '',
				GoodsUnit_bName: '',
				GoodsPackCount_bCount: ''
			};
		},

		DocumentUcStrRecord: function (get)
		{
			var DocumentUcStr_oid = get('DocumentUcStr_oid'),
				formPanel = this.getView(),
				form = formPanel.getForm(),
				DocumentUcStr = form.findField('DocumentUcStr_oid');

			return DocumentUcStr.getStore().getById(DocumentUcStr_oid) || {
				DocumentUcStr_Count: '',
				DocumentUc_id: null,
				GoodsUnit_id: null,
				DocumentUcStr_Price: null

				// EvnDrug_Kolvo: '',
				// DocumentUcStr_Count: '',
				// DocumentUcStr_EdCount: ''
			};
		},

		getTitle: function (get)
		{
			var formPanel = this.getView(),
				wnd = formPanel.up('window'),
				vm = formPanel.getViewModel(),
				wndVm = vm.getParent(),
				action = get('action'),
				title,

				titles = {
					add: langs('Добавить медикамент'),
					edit: langs('Редактировать медикамент'),
					view: langs('Просмотр медикамента')
				};


			title = action ? titles[action] : langs('Медикамент');

			wndVm.set('title', title);

			return;
		},

		// EvnDrug_KolvoEd: function (get)
		// {
		// 	var DocumentUcStrRecord = get('DocumentUcStrRecord'),
		// 		GoodsPackCount_Count = get('GoodsPackCount_Count') || null,
		// 		GoodsPackCount_bCount = DocumentUcStrRecord.isModel ? DocumentUcStrRecord.get('GoodsPackCount_bCount') : null,
		//
		// 		formPanel = this.getView(),
		// 		vm = formPanel.getViewModel(),
		// 		form = formPanel.getForm(),
		//
		// 		EvnDrug_Kolvo_Show = vm.get('EvnDrug_Kolvo_Show');
		//
		//
		// 	if ( ! EvnDrug_Kolvo_Show )
		// 	{
		// 		return '';
		// 	}
		//
		//
		//
		// 	var fas = GoodsPackCount_Count || 1,
		// 		b_fas = GoodsPackCount_bCount || 1,
		// 		EvnDrug_KolvoEd = ((fas * EvnDrug_Kolvo_Show)/b_fas).toFixed(6);
		//
		// 	form.findField('EvnDrug_Kolvo').setValue(((EvnDrug_KolvoEd/fas)*b_fas).toFixed(6));
		//
		// 	return EvnDrug_KolvoEd;
		// },

		Drug: function (get)
		{
			var Drug_id = get('Drug_id'),
				formPanel = this.getView(),
				form = formPanel.getForm(),
				Drug = form.findField('Drug_id'),
				GoodsPackCount_Count = form.findField('GoodsPackCount_Count'),
				rec = Drug.getStore().getById(Drug_id);



			if ( ! rec )
			{
				GoodsPackCount_Count.setValue('');
			}
		},

		GoodsUnit: function (get)
		{
			var GoodsUnit_id = get('GoodsUnit_id'),
				formPanel = this.getView(),
				form = formPanel.getForm(),
				GoodsUnit = form.findField('GoodsUnit_id'),
				rec = GoodsUnit.getStore().getById(GoodsUnit_id);

			return rec ? GoodsUnit_id : null;
		},

		LoadMask: function (get)
		{
			var loading = get('loading'),
				formPanel = this.getView(),
				wnd = formPanel.up('window'),
				loadMask = wnd.loadMask;

			if (loading && loadMask)
			{
				loadMask.show();
			} else if (loadMask){
				loadMask.hide();
			}
		}
	}
});


Ext6.define('DrugEditForm', {
	extend: 'GeneralFormPanel',
	alias: 'widget.DrugEditForm',
	requires: ['common.Drug.models.DrugFormModel', 'common.Drug.controllers.DrugFormBindingsController'],
	bodyPadding: '15 27 20 20',
	controller: 'DrugFormBindingsController',
	viewModel: 'DrugViewModel',
	border: false,
	reader: {
		type: 'json',
		model: 'common.Drug.models.DrugFormModel'
	},
	url: '/?c=EvnDrug&m=saveEvnDrug',

	defaults: {
		maxWidth: 735,
		labelWidth: 175 // всего - 735, поля - 560
	},

	items: [
		{
			name: 'EvnDrug_id',
			value: null,
			xtype: 'hidden'
		},
		{
			name: 'EvnDrug_rid',
			value: null,
			xtype: 'hidden'
		},
		{
			name: 'Person_id',
			value: null,
			xtype: 'hidden'
		},
		{
			name: 'DocumentUc_id',
			value: null,
			bind: '{DocumentUcStrRecord.DocumentUc_id}',
			xtype: 'hidden'
		},
		{
			name: 'DocumentUcStr_id',
			value: null,
			xtype: 'hidden'
		},
		{
			name: 'PersonEvn_id',
			value: null,
			xtype: 'hidden'
		},
		{
			name: 'Server_id',
			value: -1,
			xtype: 'hidden'
		},
		{
			// bind: {
			// 	fieldLabel: '{ (parentClass == "EvnVizit" || parentClass == "EvnPL" || parentClass == "EvnPLStom") ? "Посещение" : "Движение"}',
			// 	value: '{EvnUslugaOper_pid}',
			// 	disabled: '{(editable  === false) || EvnCount == 1 || parentClass == "EvnPLStom" || parentClass == "EvnVizit" || parentClass == "EvnSection"}',
			// 	hidden: '{useCase === "OperBlock"}'
			// },
			bind: {
				value: '{EvnDrug_pid}'
			},
			xtype: 'EventCombo',
			//allowBlank: false,
			fieldLabel: langs('Отделение'),
			name: 'EvnDrug_pid'
		},
		{
			layout: 'column',
			border: false,
			defaults: {
				labelWidth: 175,
				width: '100%'
			},
			margin: '0 0 5 0',
			items: [
				{
					xtype: 'datefield',
					plugins: [new Ext6.ux.InputTextMask('99.99.9999', true)],
					maxWidth: 295,
					fieldLabel: langs('Дата/время'),
					allowBlank: false,
					margin: '0 40 0 0',
					bind: {
						value: '{EvnDrug_setDate}',
						disabled: '{editable === false}'
					},
					name: 'EvnDrug_setDate',
					listeners: {
						change: function (c, v)
						{
							try {
								if (this.up('form') && this.up('form').getViewModel())
								{
									this.up('form').getViewModel().set(this.name, v);
								}

								//typeof this.up === 'function' ? (this.up('form') ? this.up('form').isValid() : null) : null;
							} catch (e) {
								if (IS_DEBUG)
								{
									console.log(e)
								}
							}


							return;
						}
					}
				},
				{
					xtype: 'swTimeField',
					allowBlank: false,
					width: '15%',
					hideLabel: true,
					name: 'EvnDrug_setTime',
					bind: {
						value: '{EvnDrug_setTime}',
						disabled: '{editable === false}'
					},
					listeners: {
						change: function (c, v)
						{
							try {
								if (this.up('form') && this.up('form').getViewModel())
								{
									this.up('form').getViewModel().set(this.name, v);
								}

								//typeof this.up === 'function' ? (this.up('form') ? this.up('form').isValid() : null) : null;
							} catch (e) {
								if (IS_DEBUG)
								{
									console.log(e)
								}
							}


							return;
						}
					}
				}
			]
		},
		{
			fieldLabel: langs('Исполнение назначения'),
			xtype: 'SwLpuSectionGlobalCombo',
			bind: {
				value: '{LpuSection_id}'
			},
			allowBlank: false,
			queryMode: 'local',
			name: 'LpuSection_id'
		},
		{
			fieldLabel: langs('Склад'),
			allowBlank: false,
			name: 'Storage_id',
			queryMode: 'local',
			xtype: 'StorageCombo',
			listeners: {
				'change': function(combo, newValue) {
					var bf = combo.up('form').getForm();
					var mol_combo = bf.findField('Mol_id');

					mol_combo.getStore().removeAll();

					if (!Ext6.isEmpty(newValue)) {
						mol_combo.getStore().load({
							params: {
								Storage_id: newValue
							}
						});
					}
				}
			}
		},
		{
			fieldLabel: langs('Назначение'),
			name: 'EvnPrescrTreatDrug_id',
			minChars: 1,
			minLength: 1,
			hidden: true,
			mode: 'remote',
			xtype: 'EvnPrescrTreatDrugCombo',
			listeners: {
				'change': function(combo, newValue) {
					return; // не переводилось на новую форму
					var bf = this.findById('NEDEW_FormPanel').getForm();
					var record = combo.getStore().getById(newValue);

					var lpu_section_combo = bf.findField('LpuSection_id');
					var mol_combo = bf.findField('Mol_id');
					var storage_combo = bf.findField('Storage_id');
					var dpf_combo = bf.findField('DrugPrepFas_id');
					var drug_combo = bf.findField('Drug_id');
					var dus_combo = bf.findField('DocumentUcStr_oid');

					if ( record )
					{
						bf.findField('EvnCourse_id').setValue(record.get('EvnCourse_id'));
						bf.findField('EvnCourseTreatDrug_id').setValue(record.get('EvnCourseTreatDrug_id'));
						bf.findField('EvnPrescr_id').setValue(record.get('EvnPrescrTreat_id'));
						bf.findField('GoodsUnit_id').setValue(record.get('GoodsUnit_id'));
						bf.findField('GoodsPackCount_Count').setValue(record.get('GoodsPackCount_Count'));

						bf.findField('PrescrFactCountDiff').setValue(record.get('PrescrFactCountDiff'));
						bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
						if (!bf.findField('EvnPrescrTreat_Fact').getValue()) {
							bf.findField('EvnPrescrTreat_Fact').setValue(record.get('EvnPrescrTreat_Fact'))
						}

						var LpuSection = lpu_section_combo.getStore().getById(record.get('LpuSection_id'));
						if (LpuSection) {
							lpu_section_combo.setValue(record.get('LpuSection_id'));
						}

						mol_combo.getStore().removeAll();
						if (record.get('Storage_id')) {
							mol_combo.getStore().baseParams = {Storage_id: record.get('Storage_id')};
							mol_combo.getStore().load({callback: function() {
									if (mol_combo.getStore().getById(record.get('Mol_id'))) {
										mol_combo.setValue(record.get('Mol_id'));
									} else if (mol_combo.getStore().getCount() == 1) {
										mol_combo.setValue(mol_combo.getStore().getAt(0).id);
									}
								}.createDelegate(this)});
						} else {
							mol_combo.setValue(null);
						}

						this.refreshStorageParams();
						storage_combo.getStore().load({
							callback: function() {
								if (storage_combo.getStore().getById(record.get('Storage_id'))) {
									storage_combo.setValue(record.get('Storage_id'));
								} else {
									storage_combo.setValue(null);
								}

								this.refreshDrugPrepFasParams();
								dpf_combo.getStore().load({
									callback: function() {
										if (dpf_combo.getStore().getById(record.get('DrugPrepFas_id'))) {
											dpf_combo.setValue(record.get('DrugPrepFas_id'));
										} else {
											dpf_combo.setValue(null);
										}

										this.refreshDrugParams();
										drug_combo.getStore().load({
											callback: function() {
												var drug = drug_combo.getStore().getById(record.get('Drug_id'));
												if (drug) {
													drug_combo.setValue(drug.get('Drug_id'));

													bf.findField('Drug_Fas').setRawValue(drug.get('Drug_Fas') ? drug.get('Drug_Fas') : 1);
													bf.findField('DrugForm_Name').setRawValue(drug.get('DrugForm_Name'));
													bf.findField('GoodsUnit_bid').setValue(drug.get('GoodsUnit_bid'));
													bf.findField('GoodsUnit_bName').setValue(drug.get('GoodsUnit_bName'));
													bf.findField('GoodsPackCount_bCount').setValue(drug.get('GoodsPackCount_bCount'));
												} else {
													drug_combo.setValue(null);

													bf.findField('Drug_Fas').setRawValue('');
													bf.findField('DrugForm_Name').setRawValue('');
													bf.findField('GoodsUnit_bid').setValue('');
													bf.findField('GoodsUnit_bName').setValue('');
													bf.findField('GoodsPackCount_bCount').setValue('');
												}

												this.refreshDocumentUcStrParams();
												dus_combo.getStore().removeAll();
												dus_combo.getStore().load({
													callback: function() {
														var DocumentUcStr = dus_combo.getStore().getById(record.get('DocumentUcStr_oid'));
														if (DocumentUcStr) {
															dus_combo.setValue(record.get('DocumentUcStr_oid'));
														} else {
															dus_combo.setValue(null);
														}
														bf.findField('EvnDrug_Kolvo').setValue(record.get('EvnDrug_Kolvo'));
														this.calculateEvnDrug();
													}.createDelegate(this)
												});
											}.createDelegate(this)
										});
									}.createDelegate(this)
								});
							}.createDelegate(this)
						});
					}
					else
					{
						combo.setValue(null);
						bf.findField('EvnCourse_id').setValue(null);
						bf.findField('EvnCourseTreatDrug_id').setValue(null);
						bf.findField('EvnPrescr_id').setValue(null);
						bf.findField('PrescrFactCountDiff').setValue(0);
						bf.findField('EvnPrescrTreat_Fact').setValue(0);

						this.refreshStorageParams();
						storage_combo.lastQuery = 'This query sample that is not will never appear';

						this.refreshDrugPrepFasParams();
						dpf_combo.lastQuery = 'This query sample that is not will never appear';
					}
					return true;
				}
			}
		},
		// {
		// 	fieldLabel: langs('Списать приемов'),
		// 	name: 'EvnPrescrTreat_Fact',
		// 	hidden: true,
		// 	width: 70,
		// 	value: 1,
		// 	minValue: 0,
		// 	maxValue: 1,
		// 	allowNegative: false,
		// 	allowDecimals: false,
		// 	listeners: {
		// 		'change': function(field, newValue, oldValue) {
		// 			return;
		// 			var bf = this.findById('NEDEW_FormPanel').getForm();
		//
		// 			var ep_combo = bf.findField('EvnPrescrTreatDrug_id');
		// 			var ep = ep_combo.getStore().getById(ep_combo.getValue());
		//
		// 			if (newValue >= 0) {
		// 				var fact = (newValue>field.maxValue)?field.maxValue:newValue;
		// 				var kolvo = ep?ep.get('EvnDrug_Kolvo'):0;
		// 				bf.findField('EvnDrug_Kolvo_Show').setValue(fact*kolvo);
		// 				bf.findField('EvnDrug_Kolvo_Show').fireEvent('change', bf.findField('EvnDrug_Kolvo_Show'), bf.findField('EvnDrug_Kolvo_Show').getValue());
		// 			}
		// 		}.createDelegate(this)
		// 	},
		// 	xtype: 'numberfield'
		// },
		// {
		// 	name: 'PrescrFactCountDiff',//Количество невыполненных приемов
		// 	value: 1,
		// 	xtype: 'hidden'
		// },
		// {
		// 	name: 'EvnPrescr_id',
		// 	value: null,
		// 	xtype: 'hidden'
		// },
		// {
		// 	name: 'EvnCourseTreatDrug_id',
		// 	value: null,
		// 	xtype: 'hidden'
		// },
		// {
		// 	name: 'EvnCourse_id',
		// 	value: null,
		// 	xtype: 'hidden'
		// },
		// {
		// 	border: false,
		// 	layout: 'column',
		// 	hidden: true,
		// 	items:
		// 		[{
		// 			border: false,
		// 			labelWidth: 100,
		// 			layout: 'form',
		// 			items: [{
		// 				disabled: true,
		// 				fieldLabel: langs('Лек. форма'),
		// 				name: 'DrugForm_Name',
		// 				tabIndex: TABINDEX_EDEW + 7,
		// 				width: 70,
		// 				xtype: 'textfield'
		// 			}]
		// 		},
		// 			{
		// 				border: false,
		// 				labelWidth: 100,
		// 				layout: 'form',
		// 				items: [{
		// 					disabled: true,
		// 					fieldLabel: langs('Кол-во в упак.'),
		// 					name: 'Drug_Fas',
		// 					tabIndex: TABINDEX_EDEW + 8,
		// 					width: 70,
		// 					xtype: 'numberfield'
		// 				}]
		// 			}]
		// },
		{
			fieldLabel: langs('МОЛ'),
			name: 'Mol_id',
			queryMode: 'local',
			xtype: 'MolCombo'
		},
		{
			fieldLabel: langs('Медикамент'),
			name: 'DrugPrepFas_id',
			xtype: 'DrugPrepCombo',
			queryMode: 'local',
			forceSelection: false,
			editable: true,
			allowBlank: false
		},
		{
			fieldLabel: langs('Упаковка'),
			allowBlank: false,
			queryMode: 'local',
			forceSelection: false,
			editable: true,
			name: 'Drug_id',
			xtype: 'DrugPackCombo'
		},
		{
			fieldLabel: langs('Партия'),
			allowBlank: false,
			queryMode: 'local',
			forceSelection: false,
			editable: true,
			name: 'DocumentUcStr_oid',
			xtype: 'DocumentUcStrCombo'
		},
		{
			border: false,
			layout: {
				type: 'hbox'
			},
			margin: '0 0 5 0',
			items: [
				{
					name: 'GoodsUnit_bid',
					bind: '{DrugRecord.GoodsUnit_bid}',
					xtype: 'hidden'
				}, {
					name: 'GoodsPackCount_bCount',
					bind: '{DrugRecord.GoodsPackCount_bCount}',
					xtype: 'hidden'
				},
				// остановился на том что привязал goodsunit_id к записи
				{
					fieldLabel: langs('Упаковка'),
					disabled: true,
					name: 'GoodsPackCount_Count',
					bind: '{GoodsPackCount_Count}',
					labelWidth: 175,
					maxWidth: 175 + 75,
					minWidth: 175 + 35,
					xtype: 'SimpleNumField'
				},
				{xtype: 'tbspacer', width: 10},

				{
					//fieldLabel: langs('Ед. списания'),
					maxWidth: 112,
					minWidth: 100,
					bind: '{DocumentUcStrRecord.GoodsUnit_id}',
					//name: 'GoodsUnit_id',
					displayCode: false,
					disabled: true,
					comboSubject: 'GoodsUnit',
					xtype: 'commonSprCombo'

				},
				{xtype: 'tbspacer', flex: 1, winWidth: 10},

				{
					allowBlank: false,
					allowDecimals: true,
					disabled: true,
					bind: '{DocumentUcStrRecord.DocumentUcStr_Price}',
					fieldLabel: langs('Цена упаковки'),
					listeners: {
						change: function(field, newValue, oldValue) {

							//bf.findField('EvnDrug_Kolvo').fireEvent('change', bf.findField('EvnDrug_Kolvo'), bf.findField('EvnDrug_Kolvo').getValue());
						}
					},
					name: 'EvnDrug_Price',
					labelWidth: 140,
					maxWidth: 140 + 75,
					minWidth: 140 + 35,
					xtype: 'SimpleNumField'
				},
				{xtype: 'tbspacer', width: 10},
				{
					value: 'руб.',
					disabled: true,
					maxWidth: 112,
					minWidth: 100,
					xtype: 'SimpleTextField'
				}

				// {
				// 	fieldLabel: langs('Упаковка'),
				// 	labelWidth: 175,
				// 	maxWidth: 175 + 75,
				// 	minWidth: 175 + 35,
				// 	disabled: true,
				// 	//name: 'GoodsUnit_bName', Ед. учета
				// 	xtype: 'SimpleTextField'
				// },
			]
		},
		{
			border: false,
			layout: {
				type: 'hbox'
			},
			margin: '0 0 5 0',
			items: [
				{
					fieldLabel: langs('Остаток ед. учета'),
					disabled: true,
					name: 'DocumentUcStr_Count',
					bind: '{DocumentUcStrRecord.DocumentUcStr_Count}',
					labelWidth: 175,
					maxWidth: 175 + 75,
					minWidth: 175 + 35,
					allowDecimals: true,
					decimalPrecision: 6,
					xtype: 'SimpleNumField'
				},
				{xtype: 'tbspacer', width: 10},
				{
					//fieldLabel: langs('Упаковка'),
					//labelWidth: 175,
					bind: '{DrugRecord.GoodsUnit_bName}',
					maxWidth: 112,
					minWidth: 100,
					disabled: true,
					name: 'GoodsUnit_bName', //Ед. учета
					xtype: 'SimpleTextField'
				},

				{xtype: 'tbspacer', flex: 1, winWidth: 10},

				{
					fieldLabel: langs('Остаток ед. списания'),
					labelWidth: 140,
					maxWidth: 140 + 75,
					minWidth: 140 + 35,
					disabled: true,
					name: 'DocumentUcStr_EdCount',
					bind: '{ ( GoodsPackCount_Count * DocumentUcStrRecord.DocumentUcStr_Count ) || ""}',
					allowDecimals: true,
					decimalPrecision: 6,
					xtype: 'SimpleNumField'
				},
				{xtype: 'tbspacer', width: 10},
				{
					//fieldLabel: langs('Ед. списания'),
					maxWidth: 112,
					minWidth: 100,
					disabled: true,
					bind: '{DocumentUcStrRecord.GoodsUnit_id}',
					//name: 'GoodsUnit_id',
					displayCode: false,
					comboSubject: 'GoodsUnit',
					xtype: 'commonSprCombo'
				}
			]
		},
		{
			border: false,
			layout: {
				type: 'hbox'
			},
			margin: '0 0 5 0',
			items: [
				{
					allowBlank: false,
					allowDecimals: true,
					decimalPrecision: 6,
					fieldLabel: langs('Количество ед. учета'),
					minValue: 0.001,
					name: 'EvnDrug_Kolvo_Show', //
					bind: '{EvnDrug_Kolvo_Show}',
					labelWidth: 175,
					maxWidth: 175 + 75,
					minWidth: 175 + 35,
					xtype: 'SimpleNumField'
				},
				{xtype: 'tbspacer', width: 10},
				{
					//fieldLabel: langs('Упаковка'),
					//labelWidth: 175,
					bind: '{DrugRecord.GoodsUnit_bName}',
					maxWidth: 112,
					minWidth: 100,
					disabled: true,
					//name: 'GoodsUnit_bName', Ед. учета
					xtype: 'SimpleTextField'
				},
				{xtype: 'tbspacer', flex: 1, winWidth: 10},
				{
					hidden: true,
					allowDecimals: true,
					decimalPrecision: 6,
					minValue: 0.000001,
					name: 'EvnDrug_Kolvo',
					xtype: 'SimpleNumField'
				},
				{
					labelWidth: 140,
					maxWidth: 140 + 75,
					minWidth: 140 + 35,
					allowBlank: false,
					minValue: 0.0001,
					allowDecimals: true,
					decimalPrecision: 4,
					fieldLabel: 'Кол-во ед. списания',
					bind: '{EvnDrug_KolvoEd}',
					name: 'EvnDrug_KolvoEd',
					xtype: 'SimpleNumField'
				},
				{xtype: 'tbspacer', width: 10},
				{
					//fieldLabel: langs('Ед. списания'),
					maxWidth: 112,
					minWidth: 100,
					bind: '{DocumentUcStrRecord.GoodsUnit_id}',
					name: 'GoodsUnit_id',
					displayCode: false,
					comboSubject: 'GoodsUnit',
					xtype: 'commonSprCombo'
				}
			]
		},
		{
			border: false,
			layout: {
				type: 'hbox'
			},
			margin: '0 0 5 0',
			items: [

				{
					labelWidth: 175,
					maxWidth: 175 + 75,
					minWidth: 175 + 35,
					allowBlank: false,
					disabled: true,
					fieldLabel: langs('Сумма'),
					bind: '{EvnDrug_Kolvo_Show && DocumentUcStrRecord.DocumentUcStr_Price ? DocumentUcStrRecord.DocumentUcStr_Price * EvnDrug_Kolvo_Show : "" }',
					name: 'EvnDrug_Sum',
					xtype: 'SimpleNumField'
				},
				{xtype: 'tbspacer', width: 10},
				{
					value: 'руб.',
					disabled: true,
					maxWidth: 112,
					minWidth: 100,
					xtype: 'SimpleTextField'
				}
			]
		}
	]
});




Ext6.define('common.Drug.EvnDrugEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.EvnDrugEditWindow',
	requires: ['common.Drug.controllers.DrugWindowController'],
	controller: 'DrugWindowController',
	cls: 'general-window',
	noTaskBarButton: true,
	closeToolText: 'Закрыть окно медикамента',

	documentUcStrMode:'expenditure',
	openMode: '',

	resizable: true,
	minWidth: 800,
	width: 800,
	height: 500,
	modal: true,
	closeAction: 'destroy',
	bind: {
		title: '{title}'
	},

	viewModel: {

		data: {

		}
	},

	layout: {
		type: 'vbox',
		align: 'stretch'
	},

	items: [
		{xtype:'DrugEditForm'}
	],
	buttons: [
		'->',
		{xtype: 'SimpleButton'},
		{
			text: 'Применить',
			xtype: 'SubmitButton',
			bind: {
				disabled: '{editable === false}'
			}
		}
	],

	initComponent: function ()
	{
		this.callParent(arguments);

		this.loadMask = new Ext6.LoadMask(this, {msg: "Загрузка..."});
	},

	doSave: function ()
	{
		this.getController().doSave();
	},

	show: function(params)
	{
		var wnd = this;

		if ( ! params || ! params.formParams )
		{
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function()
			{
				wnd.close();
			});
			return false;
		}


		var wndVm = this.getViewModel(),
			formPanel = wnd.down('form'),
			form = formPanel.getForm(),
			vm = formPanel.getViewModel(),
			EvnCombo = form.findField('EvnDrug_pid'),
			EvnDrug_id = (params.formParams.EvnDrug_id && ! isNaN(params.formParams.EvnDrug_id) && params.formParams.EvnDrug_id > 0) ? params.formParams.EvnDrug_id : null;

			params.formParams.EvnDrug_id = EvnDrug_id;

		if ( ! params.action )
		{
			params.action = EvnDrug_id ? 'view' : 'add';
		}

		vm.set('action', params.action);
		vm.set('parentAction', params.parentAction);

		wnd.callback = params.callback || Ext6.emptyFn;
		wnd.onHideFn = params.onHide || Ext6.emptyFn;

		wndVm.set('type', params.type || null);
		wndVm.set('owner', params.owner || null);
		wndVm.set('documentUcStrMode', params.mode || null);
		wndVm.set('parentEvnClass_SysNick', params.parentEvnClass_SysNick || null);
		wndVm.set('openMode', params.openMode || null);
		wndVm.set('show_diff_gu', getDrugControlOptions().doc_uc_different_goods_unit_control && getGlobalOptions().orgtype == 'lpu'); //отображение поле списания в альтернативных ед. измерения


		if (params.parentEvnComboData)
		{
			EvnCombo.getStore().loadData(params.parentEvnComboData);
		} else if (params.formParams.EvnDrug_pid)
		{
			EvnCombo.getStore().loadData([{Evn_id: params.formParams.EvnDrug_pid, Evn_Name: `Идентификатор родительского события - ${params.formParams.EvnDrug_pid}`}]);
			EvnCombo.disable();
		}


		//настройка видимости некоторых элементов
		// if (this.show_diff_gu)
		// {
		// 	bf.findField('EvnDrug_KolvoEd').allowBlank = false;
		// 	bf.findField('GoodsUnit_id').ownerCt.show();
		// 	bf.findField('DocumentUcStr_EdCount').ownerCt.show();
		// 	bf.findField('EvnDrug_KolvoEd').ownerCt.show();
		// } else
		// {
		// 	bf.findField('EvnDrug_KolvoEd').allowBlank = true;
		// 	bf.findField('GoodsUnit_id').ownerCt.hide();
		// 	bf.findField('DocumentUcStr_EdCount').ownerCt.hide();
		// 	bf.findField('EvnDrug_KolvoEd').ownerCt.hide();
		// }

		// if ( EvnCombo.getStore().getCount() > 0)
		// {
		// 	EvnCombo.setValue( getRegionNick() === 'perm' ? EvnCombo.getStore().data.last() : EvnCombo.getStore().data.first());
		//
		// 	if (EvnCombo.getFieldValue('Evn_disDate'))
		// 	{
		// 		form.findField('EvnDrug_setDate').setValue(EvnCombo.getFieldValue('Evn_disDate'));
		// 	}
		// 	if (EvnCombo.getFieldValue('LpuSection_id'))
		// 	{
		// 		form.findField('LpuSection_id').setValue(EvnCombo.getFieldValue('LpuSection_id'));
		// 	}
		// }

		this.callParent(arguments);

		return;
	},

	onSprLoad: function(args)
	{
		var params = args[0],
			formPanel = this.down('form'),
			form = formPanel.getForm(),
			vm = formPanel.getViewModel(),
			EvnDrug_Kolvo_Show = form.findField('EvnDrug_Kolvo_Show'),
			DocumentUcStr = form.findField('DocumentUcStr_oid'),
			Storage = form.findField('Storage_id'),
			DrugPrepFas = form.findField('DrugPrepFas_id'),
			Drug = form.findField('Drug_id'),

			EvnDrug_id = params.formParams.EvnDrug_id,
			action = vm.get('action'),
			parentAction = vm.get('parentAction'),
			data = params.formParams,
			EvnDrug_setDate = Ext6.util.Format.date(data.EvnDrug_setDate, 'd.m.Y');


		if (action === 'add')
		{
			form.setValues(params.formParams);

			form.isValid();
		} else
		{

			if (EvnDrug_id)
			{
				vm.set('loading', true); // отключаем обработчиик событий, потому что некоторые поля зависят друг от друга
				//action == 'edit' && getDrugControlOptions().drugcontrol_module == "2"
				Ext6.Ajax.request({
					params:{
						EvnDrug_id: EvnDrug_id
					},
					url: '/?c=EvnDrug&m=getExecutedDocumentUcStrForEvnDrug',

				})
					.then(({responseText}) => {
					var result = Ext6.util.JSON.decode(responseText);

				if (result.DocumentUcStr_id) {throw Error()}
			})
			.then(
				() =>  Ext6.Ajax.request({params: {EvnDrug_id: EvnDrug_id, archiveRecord: false}, url: '/?c=EvnDrug&m=loadEvnDrugEditForm'}),
				(e) => Ext6.Msg.show({ buttons: Ext6.Msg.OK, fn: () => wnd.close(), icon: Ext6.Msg.ERROR,
				msg: 'Редактирование использования медикамента невозможно, т.к. медикамент уже списан со склада', title: 'Ошибка'})
			)
			.then( ({ responseText }) => Ext6.util.JSON.decode(responseText)[0] )
			.then(data => {form.setValues(data); return data;})
			.then(data => { // грузим вручную взаимозависимые комбики, чтобы обработчики событий не работали в разнобой
				DrugPrepFas.getStore().load({params:{Storage_id: data.Storage_id, date: data.EvnDrug_setDate}, callback: () => DrugPrepFas.setValue(data.DrugPrepFas_id)});
				Drug.getStore().load({params:{Storage_id: data.Storage_id, date: data.EvnDrug_setDate, DrugPrepFas_id: data.DrugPrepFas_id}, callback: () => Drug.setValue(data.Drug_id)});
				DocumentUcStr.getStore().load({params:{Storage_id: data.Storage_id, date: data.EvnDrug_setDate, Drug_id: data.Drug_id}, callback: () => DocumentUcStr.setValue(data.DocumentUcStr_oid)});
				return data;
			})
				// контрольно проставляем значения, на случай если где-то слетело и ставим количество
			.then( data => setTimeout( () => { setTimeout(() => {EvnDrug_Kolvo_Show.setValue(data.EvnDrug_Kolvo); vm.set('loading', false);},1500); Storage.setValue(data.Storage_id); DocumentUcStr.setValue(data.DocumentUcStr_oid); Drug.setValue(data.Drug_id); DrugPrepFas.setValue(data.DrugPrepFas_id); }, 1500));

			} else
			{
				if (parentAction === 'add')
				{
					vm.set('loading', true); // отключаем обработчиик событий, потому что некоторые поля зависят друг от друга
					form.setValues(data);

					DrugPrepFas.getStore().load({params:{Storage_id: data.Storage_id, date: EvnDrug_setDate}, callback: () => DrugPrepFas.setValue(data.DrugPrepFas_id)});
					Drug.getStore().load({params:{Storage_id: data.Storage_id, date: EvnDrug_setDate, DrugPrepFas_id: data.DrugPrepFas_id}, callback: () => Drug.setValue(data.Drug_id)});
					DocumentUcStr.getStore().load({params:{Storage_id: data.Storage_id, date: EvnDrug_setDate, Drug_id: data.Drug_id}, callback: () => DocumentUcStr.setValue(data.DocumentUcStr_oid)});

					setTimeout( () => {
						setTimeout(() => {
						EvnDrug_Kolvo_Show.setValue(data.EvnDrug_Kolvo);
						vm.set('loading', false);
					}, 1500);

						Storage.setValue(data.Storage_id);
						DocumentUcStr.setValue(data.DocumentUcStr_oid);
						Drug.setValue(data.Drug_id);
						DrugPrepFas.setValue(data.DrugPrepFas_id);
					}, 1500);
				}
			}
		}


		return;
	},

	// show_window: function() {
	//
	//
	// 	bf.isFirst = 1;
	//
	// 	var evn_combo = bf.findField('EvnDrug_pid');
	// 	var evn_drug_pid = null;
	// 	var set_date = true;
	//
	//
	// 	// this.findById('nedewEvnPrescrPanel').setVisible(this.openMode == 'prescription');
	// 	// if (openMode == 'prescription')
	// 	// {
	// 	// 	bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
	// 	// 	bf.clearInvalid();
	// 	// }
	//
	// },

	// loadSpr: function()
	// {
	// 	return;
	//
	// 	var ep_combo = bf.findField('EvnPrescrTreatDrug_id');
	// 	ep_combo.getStore().removeAll();
	// 	ep_combo.getStore().baseParams = {};
	// 	if (bf.findField('EvnPrescr_id').getValue()) {
	// 		ep_combo.getStore().baseParams.EvnPrescrTreat_id = bf.findField('EvnPrescr_id').getValue();
	// 	} else if (bf.findField('EvnDrug_pid').getValue()) {
	// 		ep_combo.getStore().baseParams.EvnPrescrTreat_pid = bf.findField('EvnDrug_pid').getValue();
	// 	}
	// },

	// initFormData: function()
	// {
		// var form = this;
		//
		// var date = bf.findField('EvnDrug_setDate').getValue();
		//
		// form.init = true;
		// form.loadSpr();
		//
		// var evn_drug_id = bf.findField('EvnDrug_id').getValue(),
		// 	lpu_id = bf.findField('EvnDrug_pid').getFieldValue('Lpu_id'),
		// 	document_uc_str_oid = bf.findField('DocumentUcStr_oid').getValue(),
		// 	drug_prep_fas_id = bf.findField('DrugPrepFas_id').getValue(),
		// 	mol_id = bf.findField('Mol_id').getValue(),
		// 	drug_id = bf.findField('Drug_id').getValue();

		// грузим или назначение или



		// var ep_combo = bf.findField('EvnPrescrTreatDrug_id');
		// if (ep_combo.getValue())
		// {
		// 	ep_combo.getStore().load({
		// 		callback: function() {
		// 			ep_combo.getStore().each(function(rec){
		// 				if (rec.get('EvnPrescrTreatDrug_id')==ep_combo.getValue()) {
		// 					ep_combo.setValue(ep_combo.getValue());
		// 					if (Ext.isEmpty(rec.get('Drug_id')) || form.action != 'add') {
		// 						bf.findField('EvnCourse_id').setValue(rec.get('EvnCourse_id'));
		// 						bf.findField('EvnCourseTreatDrug_id').setValue(rec.get('EvnCourseTreatDrug_id'));
		// 						bf.findField('EvnPrescr_id').setValue(rec.get('EvnPrescrTreat_id'));
		// 						bf.findField('PrescrFactCountDiff').setValue(rec.get('PrescrFactCountDiff'));
		// 						//bf.findField('EvnPrescrTreat_Fact').setValue((form.action == 'add')?1:null);
		// 						bf.findField('EvnPrescrTreat_Fact').maxValue = bf.findField('PrescrFactCountDiff').getValue();
		// 						bf.findField('GoodsUnit_id').setValue(rec.get('GoodsUnit_id'));
		// 						bf.findField('GoodsPackCount_Count').setValue(rec.get('GoodsPackCount_Count'));
		// 					}
		// 					ep_combo.fireEvent('change', ep_combo, ep_combo.getValue());
		// 					form.init = false;
		// 					return false;
		// 				}
		// 				form.init = false;
		// 				return true;
		// 			});
		// 		}
		// 	});
		// }

	//}
});