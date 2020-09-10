Ext6.define('usluga.components.models.EvnDrugModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.EvnDrugModel',
	idProperty: 'EvnDrug_id',
	fields: [
		{ name: 'EvnDrug_id' },
		{ name: 'EvnDrug_setDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'EvnDrug_setTime' },
		{ name: 'Person_id' },
		{ name: 'EvnCourse_id' },
		{ name: 'EvnPrescr_id' },
		{ name: 'EvnCourseTreatDrug_id' },
		{ name: 'EvnPrescrTreatDrug_id' },
		{ name: 'EvnPrescrTreat_Fact' },
		{ name: 'PrescrFactCountDiff' },
		{ name: 'EvnDrug_pid' },
		{ name: 'EvnDrug_rid'},
		{ name: 'Server_id' },
		{ name: 'PersonEvn_id' },
		{ name: 'Drug_id' },
		{ name: 'DrugPrepFas_id' },
		{ name: 'LpuSection_id' },
		{ name: 'EvnDrug_Price' },
		{ name: 'EvnDrug_Sum' },
		{ name: 'DocumentUc_id' },
		{ name: 'DocumentUcStr_id' },
		{ name: 'DocumentUcStr_oid' },
		{ name: 'Storage_id' },
		{ name: 'Mol_id' },
		{ name: 'EvnDrug_Kolvo' },
		{ name: 'EvnDrug_KolvoEd' },
		{ name: 'EvnDrug_RealKolvo' },
		{ name: 'GoodsUnit_id' },

		{ name: 'MSF_LpuSection_id' },
		{ name: 'MSF_MedPersonal_id' },
		{ name: 'MSF_MedService_id' },

		// визуальная часть
		{
			name: 'Drug_Code',
			type: 'string'
		}, {
			name: 'Drug_Name',
			type: 'string'
		}
	],

	proxy: {
		type: 'ajax',
		api: {
			destroy: '/?c=EvnDrug&m=deleteEvnDrug',
			create: '/?c=EvnDrug&m=saveEvnDrug',
			update: '/?c=EvnDrug&m=saveEvnDrug'
		},
		actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
		url: '/?c=EvnDrug&m=loadEvnDrugGrid',
		reader: {
			type: 'json'
		},
		writer: 'QueryStringWriter'
	}
});
