Ext6.define('common.Drug.controllers.DrugFormBindingsController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.DrugFormBindingsController',

	bindings: {
		onEvnDrug_pidChange: '{EvnDrug_pid}',
		filterLpuSection: {date: '{EvnDrug_setDate}'},
		reloadMol: '{Storage_id}',
		reloadDrugPrepFas: {date: '{EvnDrug_setDate}', Storage_id: '{Storage_id}', EvnPrescrTreatDrug_id: 'EvnPrescrTreatDrug_id', Diag_id: '{Diag_id}'},
		reloadDrug: {date: '{EvnDrug_setDate}', Storage_id: '{Storage_id}', DrugPrepFas_id: '{DrugPrepFas_id}', EvnPrescrTreatDrug_id: '{EvnPrescrTreatDrug_id}'},
		reloadDocumentUcStr: {date: '{EvnDrug_setDate}', //DocumentUcStr_oid: '{DocumentUcStr_oid}',
			Storage_id: '{Storage_id}', Drug_id: '{Drug_id}', EvnPrescrTreatDrug_id: 'EvnPrescrTreatDrug_id'},
		reloadStorage: {date: '{EvnDrug_setDate}', LpuSection_id: '{LpuSection_id}', EvnPrescrTreatDrug_id: '{EvnPrescrTreatDrug_id}',}, //  DrugPrepFas_id: '{DrugPrepFas_id}'

		calculateEvnDrug: '{DocumentUcStr_oid}',
		EvnDrug_Kolvo_ShowControl: '{EvnDrug_Kolvo_Show}', // {DocumentUcStrRecord: '{DocumentUcStrRecord}', EvnDrug_Kolvo_Show: '{EvnDrug_Kolvo_Show}'},
		EvnDrug_KolvoEdControl: '{EvnDrug_KolvoEd}'
	},

	onEvnDrug_pidChange: function (value)
	{
		var formPanel = this.getView(),
			vm = this.getViewModel(),
			form = formPanel.getForm(),
			field = form.findField('EvnDrug_pid'),
			LpuSection = form.findField('LpuSection_id'),

			rec = field.getStore().getById(value),
			data = rec ? rec.data : null,
			action = vm.get('action') || 'add';


		if ( data && action == 'add')
		{
			data.LpuSection_id ? LpuSection.setValue(data.LpuSection_id) : null;

		}

		return;
	},

	filterLpuSection: function (params, none, binding)
	{
		var date = Ext6.util.Format.date(params.date ? params.date : new Date(), 'd.m.Y'),
			formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),

			LpuSection_id = vm.get('LpuSection_id'),
			LpuSection = form.findField('LpuSection_id'),
			openMode = this.openMode || '',
			type = this.type || null,

			SectionFilterParams =
				{
					isStac: type ? undefined : openMode != 'prescription',
					onDate: date
				},

			rec;

		setLpuSectionGlobalStoreFilter(SectionFilterParams);
		form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		rec = LpuSection_id ? form.findField('LpuSection_id').getStore().getById(LpuSection_id) : null;
		LpuSection.setValue( rec ? LpuSection_id : null );

		return true;

	},

	reloadStorage: function(params, none, binding)
	{ // refreshStorageParams
		var date = Ext6.util.Format.date(params.date ? params.date : new Date(), 'd.m.Y'),
			formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),
			rec,

			EvnDrug = form.findField('EvnDrug_pid'),
			Storage = form.findField('Storage_id'),
			DrugPrepFas = form.findField('DrugPrepFas_id'),
			Storage_id = vm.get('Storage_id'),
			DPFStorage_id = DrugPrepFas.getFieldValue('Storage_id');

		// if ( ! DPFStorage_id )
		// {
		// 	return;
		// }


		//rec = Storage.getStore().getById(Storage_id);

		// if (rec && DPFStorage_id != Storage_id)
		// {
		// 	Storage.setValue(rec);
		// } else
		params.date = date;
		params.LpuSection_id = isNaN(Number(params.LpuSection_id)) ? null : Math.floor(Number(params.LpuSection_id));
		params.Lpu_oid = params.LpuSection_id ? null : (EvnDrug.getFieldValue('Lpu_id') || getGlobalOptions().lpu_id);


		if (params.LpuSection_id || params.Lpu_oid)
		{

			// EvnPrescrTreatDrug_id

			Storage.getStore().getProxy().setExtraParams(params);

			//if (Storage_id)
			//{
				Storage.getStore().load({
					callback: function ()
					{
						rec = Storage.getStore().getById(DPFStorage_id || Storage_id);

						Storage.setValue(rec ? rec : null);
					}
				})
			//}
		} else
		{
			Storage.getStore().removeAll();
			Storage.setValue(null);
		}

		return;
	},


	reloadMol: function(Storage_id, none, binding) 
	{
		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),
			Mol = form.findField('Mol_id'),
			Mol_id;

		if ( Storage_id )
		{
			if (Mol.getFieldValue('Storage_id') != Storage_id)
			{
				Mol.getStore().getProxy().setExtraParams({Storage_id: Storage_id});

				Mol.getStore().load({
					callback: function (recs, b, s)
					{
						Mol_id = recs.length == 1 ? recs[0].get('Mol_id') : null;
						Mol.setValue(Mol_id);
					}
				});
			}
		} else
		{
			Mol.getStore().removeAll();
			Mol.setValue(null);
		}

		return;
	},

	reloadDrugPrepFas: function(params, none, binding)
	{ // refreshDrugPrepFasParams
		var date = Ext6.util.Format.date(params.date ? params.date : new Date(), 'd.m.Y'),
			formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),
			isLoading = vm.get('loading'),

			DrugPrepFas_id = vm.get('DrugPrepFas_id'),
			DrugPrepFas = form.findField('DrugPrepFas_id'),
			Storage = form.findField('Storage_id'),
			//Diag = form.findField('Diag_id'),
			EvnPrescrTreatDrug = form.findField('EvnPrescrTreatDrug_id'),

			rec;

		if ( isLoading === true)
		{
			return;
		}

		params.date = date;
		params.Storage_id =  isNaN(Number(params.Storage_id)) ? null : Math.floor( Number(params.Storage_id) );
		params.DrugPrepFas_id = EvnPrescrTreatDrug.getFieldValue('DrugPrepFas_id');
		params.Diag_id = null;
		// EvnPrescrTreatDrug_id


		DrugPrepFas.getStore().getProxy().setExtraParams(params);

		if ( params.Storage_id )
		{
			DrugPrepFas.getStore().load({
				callback: function (recs, b, s)
				{
					//DrugPrepFas_id = DrugPrepFas_id || Diag.getFieldValue('DrugPrepFas_id');
					rec = DrugPrepFas.getStore().getById(DrugPrepFas_id);

					DrugPrepFas.setValue(rec ? rec : null);
				}
			});
		} else
		{
			DrugPrepFas.getStore().removeAll();
			DrugPrepFas.setValue(null);
		}

		return;
	},

	reloadDrug: function(params, none, binding)
	{ // refreshDrugParams
		var date = Ext6.util.Format.date(params.date ? params.date : new Date(), 'd.m.Y'),
			formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),
			rec,

			isLoading = vm.get('loading'),
			Drug_id = vm.get('Drug_id'),
			DrugPrepFas = form.findField('DrugPrepFas_id'),
			Drug = form.findField('Drug_id'),
			Storage = form.findField('Storage_id'),
			EvnPrescrTreatDrug = form.findField('EvnPrescrTreatDrug_id');

		if ( isLoading === true)
		{
			return;
		}

		params.date = date;
		params.Storage_id =  isNaN(Number(params.Storage_id)) ? null : Math.floor( Number(params.Storage_id) );
		params.DrugPrepFas_id = (isNaN(Number(params.DrugPrepFas_id)) ? null : Math.floor( Number(params.DrugPrepFas_id) )) || EvnPrescrTreatDrug.getFieldValue('DrugPrepFas_id');
		params.Drug_id = EvnPrescrTreatDrug.getFieldValue('Drug_id');
		//params.EvnPrescrTreatDrug_id = null;

		Drug.getStore().getProxy().setExtraParams(params);

		if ( params.Storage_id && params.DrugPrepFas_id) // && params.DrugPrepFas_id
		{
			Drug.getStore().load({
				callback: function(recs)
				{
					Drug_id = Drug_id || (recs.length == 1 ? recs[0].get('Drug_id') : null);
					rec = Drug.getStore().getById(Drug_id);

					Drug.setValue(rec ? Drug_id : null);

					// bf.findField('Drug_Fas').setRawValue(rec.get('Drug_Fas') ? rec.get('Drug_Fas') : 1);
					// bf.findField('DrugForm_Name').setRawValue(rec.get('DrugForm_Name'));
				}
			});
		} else
		{
			Drug.getStore().removeAll();
			Drug.setValue(null);
			//Drug.setValue(null);
			//Drug.getStore().removeAll();
		}


		return;
	},

	reloadDocumentUcStr: function(params, none, binding)
	{ // refreshDocumentUcStrParams
		var date = Ext6.util.Format.date(params.date ? params.date : new Date(), 'd.m.Y'),
			formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),
			isLoading = vm.get('loading'),

			EvnDrug_id = vm.get('EvnDrug_id'),
			DocumentUcStr_oid = vm.get('DocumentUcStr_oid'),
			DocumentUcStr = form.findField('DocumentUcStr_oid'),
			EvnDrug_Kolvo_Show = form.findField('EvnDrug_Kolvo_Show'),
			EvnPrescrTreatDrug = form.findField('EvnPrescrTreatDrug_id'),

			rec, rec2;

		if ( isLoading === true)
		{
			return;
		}

		params.EvnDrug_id = EvnDrug_id;
		params.date = date;
		params.Storage_id =  isNaN(Number(params.Storage_id)) ? null : Math.floor( Number(params.Storage_id) );
		params.Drug_id = (isNaN(Number(params.Drug_id)) ? null : Math.floor( Number(params.Drug_id) )) || EvnPrescrTreatDrug.getFieldValue('Drug_id');
		params.EvnPrescrTreatDrug_id = null;

		DocumentUcStr.getStore().getProxy().setExtraParams(params);

		if (params.Drug_id && params.Storage_id)
		{
			DocumentUcStr.getStore().load({
				callback: function(recs)
				{
					rec = DocumentUcStr.getStore().getById(DocumentUcStr_oid);
					rec2 = DocumentUcStr.getStore().getAt(0);

					if (recs.length > 0 && ! rec)
					{
						DocumentUcStr.getStore().each(function(rec) {

							var godnDate1 = Date.parseDate(rec.get('PrepSeries_GodnDate'), 'd.m.Y');
							var godnDate2 = Date.parseDate(rec2.get('PrepSeries_GodnDate'), 'd.m.Y');
							if (godnDate1 < godnDate2) rec2 = rec;
						});
					}

					DocumentUcStr.setValue( rec || rec2 || null);

					//DocumentUcStr.fireEvent('change', DocumentUcStr, DocumentUcStr.getValue());
				}
			});
		} else
		{
			//DocumentUcStr.setValue(null);
			DocumentUcStr.getStore().removeAll();
			DocumentUcStr.setValue(null);
			EvnDrug_Kolvo_Show.setValue('');
			//DocumentUcStr.fireEvent('change', DocumentUcStr, DocumentUcStr.getValue());
		}

		return;
	},

	calculateEvnDrug: function(DocumentUcStr_oid)
	{
		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),

			action = vm.get('action'),
			openMode = null, //this.openMode || '',
			init = null, //this.init || '',
			GoodsUnit_id = vm.get('GoodsUnit_id'),
			DocumentUcStr = form.findField('DocumentUcStr_oid'),
			rec = DocumentUcStr.getStore().getById(DocumentUcStr_oid);


		// bf.findField('EvnDrug_KolvoEd').setValue('');
		// bf.findField('EvnDrug_Price').setValue('');
		// bf.findField('DocumentUc_id').setValue(null);

		if ( rec )
		{

			if (openMode == 'prescription' || init)
			{
				//form.findField('EvnDrug_Kolvo_Show').fireEvent('change', form.findField('EvnDrug_Kolvo'), form.findField('EvnDrug_Kolvo').getValue());
				

				if ( openMode == 'prescription' && action == 'add' && ! Ext6.isEmpty(form.findField('EvnPrescrTreat_Fact').getValue()))
				{
					//form.findField('EvnPrescrTreat_Fact').fireEvent('change', form.findField('EvnPrescrTreat_Fact'), form.findField('EvnPrescrTreat_Fact').getValue(),null);
				}

			} else
			{
				//form.findField('GoodsUnit_id').getStore().clearFilter();
				//form.findField('GoodsUnit_id').setValue(rec.get('GoodsUnit_id'));
				
				this.getGoodsPackCount(function () {
					
					//form.findField('EvnDrug_Kolvo_Show').fireEvent('change', form.findField('EvnDrug_Kolvo'), form.findField('EvnDrug_Kolvo').getValue());
					
					if (openMode == 'prescription' && action == 'add' && ! Ext6.isEmpty(form.findField('EvnPrescrTreat_Fact').getValue()))
					{
						//form.findField('EvnPrescrTreat_Fact').fireEvent('change', form.findField('EvnPrescrTreat_Fact'), form.findField('EvnPrescrTreat_Fact').getValue(),null);
					}
				});
			}

		}
		else
		{
			//form.findField('DocumentUcStr_oid').setValue(null);
			// form.findField('EvnDrug_Kolvo').setValue('');
			// form.findField('DocumentUcStr_Count').setValue(null);
			// form.findField('DocumentUc_id').setValue(null);
			// form.findField('DocumentUcStr_EdCount').setValue(null);

			//form.findField('EvnDrug_Kolvo_Show').fireEvent('change', form.findField('EvnDrug_Kolvo'), '', 1);
		}

	},
	getGoodsPackCount: function(callback)
	{
		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),

			Drug_id = vm.get('Drug_id'),

			GoodsUnit_id = vm.get('GoodsUnit_id'),
			GoodsUnit = form.findField('GoodsUnit_id'),
			GoodsUnit_Name = '',
			rec = GoodsUnit.getStore().getById(GoodsUnit_id);

		if ( ! (GoodsUnit_id && Drug_id))
		{
			return;
		}


		Ext6.Ajax.request({
			params:{
				Drug_id: Drug_id,
				GoodsUnit_id: GoodsUnit_id
			},
			url: '/?c=Farmacy&m=getGoodsPackCount',
			callback: function(options, success, response)
			{
				if ( success )
				{
					var result = Ext6.util.JSON.decode(response.responseText);

					if ( result && result[0] && ! Ext6.isEmpty(result[0].GoodsPackCount_Count) )
					{
						form.findField('GoodsPackCount_Count').setValue(result[0].GoodsPackCount_Count);
					} else
					{
						if (rec)
						{
							GoodsUnit_Name = rec.get('GoodsUnit_Name');
						}

						if(GoodsUnit_id == 57)
						{
							form.findField('GoodsPackCount_Count').setValue(1);
						} else
						{
							form.findField('GoodsPackCount_Count').setValue('');
							Ext6.Msg.alert(langs('Внимание'), `Чтобы указать количество ${GoodsUnit_Name} в потребительской упаковке выбранного медикамента обратитесь к Старшей медсестре или Администратору системы для заполнения справочника «Количество товара в потребительской упаковке»`);
						}
					}

					typeof callback == 'function' ? callback() : null;
				}
			}
		});
	},

	/**
	 * Основная задача - синхронизировать значения поля EvnDrug_KolvoEd с EvnDrug_Kolvo_Show,
	 * EvnDrug_KolvoEd = EvnDrug_Kolvo_Show * GoodsPackCount_Count (число условных таблеток = кол-во упаковок * условные таблетки в упаковке)
	 */
	EvnDrug_Kolvo_ShowControl: function (EvnDrug_Kolvo_Show)
	{
		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),


			EvnDrug_Kolvo_Show = isNaN(Number(EvnDrug_Kolvo_Show)) ? 0 : EvnDrug_Kolvo_Show,

			GoodsPackCount_Count = vm.get('GoodsPackCount_Count') || 1, // кол-во в упаковке

			DocumentUcStrRecord = vm.get('DocumentUcStrRecord'),
			EvnDrug_KolvoEd = vm.get('EvnDrug_KolvoEd') || 0, // ед списания

			DocumentUcStr_Count = DocumentUcStrRecord.isModel ? DocumentUcStrRecord.get('DocumentUcStr_Count') : 0; // остаток


		// если кол-во больше остатка
		if (EvnDrug_Kolvo_Show > DocumentUcStr_Count)
		{
			form.findField('EvnDrug_Kolvo_Show').setValue(DocumentUcStr_Count);
			return;
		}


		if (EvnDrug_KolvoEd != EvnDrug_Kolvo_Show * GoodsPackCount_Count)
		{
			form.findField('EvnDrug_KolvoEd').setValue(EvnDrug_Kolvo_Show * GoodsPackCount_Count || '');
		}


		// if (getGlobalOptions().region.nick == 'ufa')
		// {
		// 	// Для Уфы берем значение без округления, чтобы подсчитать корректно количество (таблеток или пр.)
		// 	// #113297
		// 	var ep_combo = form.findField('EvnPrescrTreatDrug_id');
		// 	var ep = ep_combo.getStore().getById(ep_combo.getValue());
		// 	var kolvo = ep?ep.get('EvnDrug_Kolvo'):0;
		// 	var fact;
		// 	if (oldValue == undefined) {
		// 		fact = form.findField('EvnPrescrTreat_Fact').getValue()
		// 		EvnDrug_Kolvo_Show = fact*kolvo;
		// 	}
		// 	else {
		// 		var oldKolvo = ep?ep.get('EvnDrug_Kolvo'):0;
		// 		//console.log('oldKolvo = ', oldKolvo);
		// 		fact = (EvnDrug_Kolvo_Show/oldKolvo).toFixed();
		// 		form.findField('EvnPrescrTreat_Fact').setValue(fact);
		// 	}
		// }


		//EvnDrug_Kolvo_Show > DocumentUcStr_Count ? form.findField('EvnDrug_Kolvo_Show').setValue(DocumentUcStr_Count) : null;

		//EvnDrug_Kolvo_Show = EvnDrug_Kolvo_Show > DocumentUcStr_Count ? DocumentUcStr_Count : EvnDrug_Kolvo_Show;


		// Расчет суммы - цена берется из медикамента
		// if (EvnDrug_Kolvo_Show === -1)
		// {
		// 	form.findField('EvnDrug_Sum').setValue('');
		// 	form.findField('EvnDrug_KolvoEd').setValue('');
		// } else
		// {
		// 	var fas = GoodsPackCount_Count || 1,
		// 		b_fas = GoodsPackCount_bCount || 1,
		// 		EvnDrug_KolvoEd = ((fas * EvnDrug_Kolvo_Show)/b_fas).toFixed(6);
		//
		//
		// 	EvnDrug_KolvoEd = EvnDrug_KolvoEd > DocumentUcStr_EdCount ? DocumentUcStr_EdCount : EvnDrug_KolvoEd;
		//
		// 	form.findField('EvnDrug_Sum').setValue((EvnDrug_Price * EvnDrug_Kolvo_Show).toFixed(2));
		// 	form.findField('EvnDrug_KolvoEd').setValue(EvnDrug_KolvoEd);
		// 	form.findField('EvnDrug_Kolvo').setValue(((EvnDrug_KolvoEd/fas)*b_fas).toFixed(6));
		// 	form.findField('EvnDrug_Kolvo_Show').setValue(EvnDrug_Kolvo_Show.toFixed(6));
		// }
	},

	/**
	 * Основная задача - синхронизировать значения поля EvnDrug_KolvoEd с EvnDrug_Kolvo_Show,
	 * EvnDrug_KolvoEd = EvnDrug_Kolvo_Show * GoodsPackCount_Count (число условных таблеток = кол-во упаковок * условные таблетки в упаковке)
	 */
	EvnDrug_KolvoEdControl: function (EvnDrug_KolvoEd)
	{
		var formPanel = this.getView(),
			form = formPanel.getForm(),
			vm = this.getViewModel(),

			EvnDrug_KolvoEd = isNaN(Number(EvnDrug_KolvoEd)) ? 0 : EvnDrug_KolvoEd,
			GoodsPackCount_Count = vm.get('GoodsPackCount_Count') || 1, // кол-во в упаковке

			DocumentUcStr_EdCount = form.findField('DocumentUcStr_EdCount').getValue(), // остаток ед списания

			DocumentUcStrRecord = vm.get('DocumentUcStrRecord'),

			DocumentUcStr_Count = DocumentUcStrRecord.isModel ? DocumentUcStrRecord.get('DocumentUcStr_Count') : 0; // остаток



		// если кол-во ед списания больше чем остаток ед списания
		if (EvnDrug_KolvoEd > DocumentUcStr_EdCount)
		{
			form.findField('EvnDrug_KolvoEd').setValue(DocumentUcStr_EdCount);
			return;
		}


		// Расчет суммы - цена берется из медикамента
		if ( EvnDrug_KolvoEd == 0 )
		{
			//form.findField('EvnDrug_Sum').setValue('');
			//form.findField('EvnDrug_Kolvo').setValue('');
			form.findField('EvnDrug_Kolvo_Show').setValue('');
			
		} else
		{
			var EvnDrug_Kolvo_Show = EvnDrug_KolvoEd/GoodsPackCount_Count < DocumentUcStr_Count ? EvnDrug_KolvoEd/GoodsPackCount_Count : DocumentUcStr_Count;


			form.findField('EvnDrug_Kolvo').setValue(EvnDrug_Kolvo_Show || '');
			form.findField('EvnDrug_Kolvo_Show').setValue(EvnDrug_Kolvo_Show || '');
			//form.findField('EvnDrug_Sum').setValue((price * kolvo_show).toFixed(2));
			
			// if (getGlobalOptions().region.nick == 'ufa') 
			// {
			// 	// Для Уфы расчет приемов
			// 	if (oldKolvo == 0 ) oldKolvo = 1;
			// 	var kolvo_show_tmp = kolvo < form.findField('DocumentUcStr_Count').getValue() ? kolvo : form.findField('DocumentUcStr_Count').getValue();
			// 	var fact = (kolvo_show_tmp/oldKolvo).toFixed(4);
			// 	if (fact > 0 && fact < 1)
			// 		fact = 1; //fact.toFixed(); // Округляем
			// 	else
			// 		fact = Math.floor(fact); // Отбрасываем дробную часть
			//
			// 	form.findField('EvnPrescrTreat_Fact').setValue(fact);
			// }
		}
	},

	GoodsUnit_idChange: function (GoodsUnit_id)
	{
		// var record = bf.findField('DocumentUcStr_oid').getStore().getById(bf.findField('DocumentUcStr_oid').getValue());
		//
		// this.getGoodsPackCount(function() {
		//
		// 	if (record) {
		// 		bf.findField('DocumentUcStr_EdCount').setValue(this.calculateEdCount(
		// 			bf.findField('GoodsPackCount_Count').getValue(), record.get('DocumentUcStr_Count')
		// 		));
		// 		bf.findField('EvnDrug_Kolvo_Show').fireEvent('change', bf.findField('EvnDrug_Kolvo'), bf.findField('EvnDrug_Kolvo').getValue());
		// 	}
		// });
	}
});