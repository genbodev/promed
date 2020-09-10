Ext6.define('common.EMK.models.AddPrescrByCheckGridsModel', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	}]
});
Ext6.define('common.EMK.models.CureStandDrug', {
	extend: 'Ext6.data.Model',

	fields: [{
		name: 'ActMatters_Name',
		type: 'string'
	},{
		name: 'ActMatters_id',
		type: 'int'
	},{
		name: 'Drug_id',
		type: 'int'
	},{
		name: 'DrugComplexMnn_id',
		type: 'int'
	},{
		name: 'ClsAtc_Name',
		type: 'string'
	},{
		name: 'ClsAtc_id',
		type: 'int'
	},{
		name: 'FreqDelivery',
		type: 'auto',
		convert: function (value) {
			var resStr = false;
			if( !isNaN(parseInt(value)) && parseInt(value) == 1)
				resStr = true;
			return resStr;
		}
	},{
		name: 'Replaseability',
		type: 'string'
	}
		]
});
Ext6.define('common.EMK.models.CureStandLabDiag', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	},{
		name: 'UslugaComplex_id',
		type: 'int'
	},{
		name: 'MedService_id',
		type: 'int'
	},{
		name: 'MedService_Nick',
		type: 'string'
	},{
		name: 'Lpu_Nick',
		type: 'string'
	},{
		name: 'PacketPrescrUsluga_id',
		type: 'int'
	},{
		name: 'FreqDelivery',
		type: 'auto',
		convert: function (value) {
			var resStr = false;
			if( !isNaN(parseInt(value)) && parseInt(value) == 1)
				resStr = true;
				return resStr;
		}
	}, {
		name: 'active',
		type: 'bool',
		defaultValue: false
	}]
});
Ext6.define('common.EMK.models.CureStandFuncDiag', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	},{
		name: 'UslugaComplex_id',
		type: 'int'
	},{
		name: 'MedService_id',
		type: 'int'
	},{
		name: 'MedService_Nick',
		type: 'string'
	},{
		name: 'Lpu_Nick',
		type: 'string'
	},{
		name: 'PacketPrescrUsluga_id',
		type: 'int'
	},{
		name: 'FreqDelivery',
		type: 'auto',
		convert: function (value) {
			var resStr = '';
			if( !isNaN(parseInt(value)) && parseInt(value) == 1)
				resStr = '1';
			return resStr;
		}
	}, {
		name: 'active',
		type: 'bool',
		defaultValue: false
	}]
});
Ext6.define('common.EMK.models.CureStandConsUsl', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	},{
		name: 'UslugaComplex_id',
		type: 'int'
	},{
		name: 'MedService_id',
		type: 'int'
	},{
		name: 'MedService_Nick',
		type: 'string'
	},{
		name: 'Lpu_Nick',
		type: 'string'
	},{
		name: 'PacketPrescrUsluga_id',
		type: 'int'
	}, {
		name: 'active',
		type: 'bool',
		defaultValue: false
	}]
});
Ext6.define('common.EMK.models.CureStandProc', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	},{
		name: 'UslugaComplex_id',
		type: 'int'
	},{
		name: 'MedService_Nick',
		type: 'string'
	},{
		name: 'Lpu_Nick',
		type: 'string'
	},{
		name: 'PacketPrescrUsluga_id',
		type: 'int'
	}, {
		name: 'active',
		type: 'bool',
		defaultValue: false
	}]
});
Ext6.define('common.EMK.models.CureStandRegime', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'PrescriptionRegimeType_Name',
		type: 'string'
	}, {
		name: 'PrescriptionRegimeType_id',
		type: 'int'
	}, {
		name: 'PacketPrescrRegime_Duration',
		type: 'int'
	},{
		name: 'PacketPrescrRegime_id',
		type: 'int'
	}, {
		name: 'active',
		type: 'bool',
		defaultValue: false
	}]
});
Ext6.define('common.EMK.models.CureStandDiet', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'PrescriptionDietType_Name',
		type: 'string'
	}, {
		name: 'PrescriptionDietType_id',
		type: 'int'
	}, {
		name: 'PacketPrescrDiet_Duration',
		type: 'int'
	},{
		name: 'PacketPrescrDiet_id',
		type: 'int'
	}, {
		name: 'active',
		type: 'bool',
		defaultValue: false
	}]
});
Ext6.define('common.EMK.models.PacketPrescrDrug', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'ActMatters_Name',
		type: 'string'
	},{
		name: 'PacketPrescrTreatDrug_id',
		type: 'int'
	},{
		name: 'PacketPrescrTreat_id',
		type: 'int'
	},{
		name: 'ActMatters_id',
		type: 'int'
	},{
		name: 'Drug_id',
		type: 'int'
	},{
		name: 'DrugComplexMnn_id',
		type: 'int'
	},{
		name: 'ClsAtc_Name',
		type: 'string'
	},{
		name: 'ClsAtc_id',
		type: 'int'
	},{
		name: 'Periodic',
		type: 'string'
	},{
		name: 'Replaseability',
		type: 'string'
	},{
		name: 'EvnCourse_id',
		type: 'int'
	},{
		name: 'Duration',
		type: 'int' // бывает еще type: 'Date'
	},{
		name: 'DurationType_Nick',
		type: 'string'
	},{
		name: 'MinCountInDay',
		type: 'int'
	},{
		name: 'EvnPrescr_IsCito',
		type: 'int'
	},{
		name: 'DrugListData',
		type: 'auto'
		/*,convert: function (value) {
			var resStr = '<div>'; // Для назначения idшника всей строки в целом EXTJSом
				if(value){
					var manyDrug = (Object.keys(value).length > 1);
					for(var key in value) {
						if(manyDrug)
							resStr += '<div class="onePrescr" ><span class="manyEvnPrescr" >'+value[key].Drug_Name+'</span></div>';
						else
							resStr += '<div class="onePrescr" >' + value[key].Drug_Name + '</div>';
						console.log(value[key]);
					}
				}
				resStr += '</div>';
				return resStr;
		}*/
	}, {
		name: 'active',
		type: 'bool',
		defaultValue: false
	}
	]
});