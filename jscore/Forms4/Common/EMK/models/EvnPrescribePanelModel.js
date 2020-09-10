Ext6.define('common.EMK.models.EvnPrescribePanelModel', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	}, {
		name: 'UslugaComplex_Code',
		type: 'string'
	}, {
		name: 'UslugaComplex_id',
		type: 'string'
	}, {
		name: 'withResource',
		type: 'string'
	}, {
		name: 'group',
		type: 'string'
	}]
});
Ext6.define('common.EMK.models.EvnPrescrLabDiag', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	}, {
		name: 'UslugaComplex_Code',
		type: 'string'
	}, {
		name: 'UslugaComplex_id',
		type: 'string'
	}, {
		name: 'EvnStatus_id',
		type: 'int'
	}, {
		name: 'EvnDirection_id',
		type: 'int'
	}, {
		name: 'EvnPrescr_IsExec',
		type: 'int'
	}, {
		name: 'otherMO',
		type: 'int'
	}, {
		name: 'EvnStatus_SysNick',
		type: 'string'
	}, {
		name: 'TimetableMedService_id',
		type: 'int'
	}]
});
Ext6.define('common.EMK.models.EvnPrescrFuncDiag', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	}, {
		name: 'UslugaComplex_Code',
		type: 'string'
	}, {
		name: 'UslugaComplex_id',
		type: 'string'
	}, {
		name: 'EvnStatus_id',
		type: 'int'
	}, {
		name: 'EvnPrescr_IsExec',
		type: 'int'
	}, {
		name: 'EvnDirection_id',
		type: 'int'
	}, {
		name: 'otherMO',
		type: 'int'
	}, {
		name: 'EvnStatus_SysNick',
		type: 'string'
	}]
});
Ext6.define('common.EMK.models.EvnCourseTreat', {
	extend: 'Ext6.data.Model',
	fields: [{
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
		name: 'EvnPrescr_IsExec',
		type: 'int'
	},{
		name: 'haveRecept',
		type: 'int'
	},{
		name: 'isValid',
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
	}]
});
Ext6.define('common.EMK.models.EvnConsUsluga', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	}, {
		name: 'UslugaComplex_Code',
		type: 'string'
	}, {
		name: 'UslugaComplex_id',
		type: 'string'
	}, {
		name: 'withResource',
		type: 'string'
	}, {
		name: 'group',
		type: 'string'
	}]
});
Ext6.define('common.EMK.models.EvnCourseProc', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	}, {
		name: 'UslugaComplex_Code',
		type: 'string'
	}, {
		name: 'UslugaComplex_id',
		type: 'string'
	}, {
		name: 'EvnDirection_id',
		type: 'int'
	}, {
		name: 'otherMO',
		type: 'int'
	}, {
		name: 'withResource',
		type: 'string'
	}, {
		name: 'group',
		type: 'string'
	}, {
		name: 'EvnPrescr_IsExec',
		type: 'int'
	}]
});
Ext6.define('common.EMK.models.EvnPrescrDiet', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'PrescriptionDietType_Name',
		type: 'string'
	}, {
		name: 'UslugaComplex_Code',
		type: 'string'
	}, {
		name: 'UslugaComplex_id',
		type: 'string'
	}, {
		name: 'withResource',
		type: 'string'
	}, {
		name: 'group',
		type: 'string'
	}]
});
Ext6.define('common.EMK.models.EvnPrescrRegime', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'PrescriptionRegimeType_Name',
		type: 'string'
	}, {
		name: 'UslugaComplex_Code',
		type: 'string'
	}, {
		name: 'UslugaComplex_id',
		type: 'string'
	}, {
		name: 'withResource',
		type: 'string'
	}, {
		name: 'group',
		type: 'string'
	}]
});
Ext6.define('common.EMK.models.EvnPrescrOperBlock', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	}, {
		name: 'UslugaComplex_Code',
		type: 'string'
	}, {
		name: 'UslugaComplex_id',
		type: 'string'
	}, {
		name: 'withResource',
		type: 'string'
	}, {
		name: 'group',
		type: 'string'
	}]
});
Ext6.define('common.EMK.models.EvnPrescrUsluga', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'UslugaComplex_Name',
		type: 'string'
	}, {
		name: 'UslugaComplex_Code',
		type: 'string'
	}, {
		name: 'UslugaComplex_id',
		type: 'string'
	}, {
		name: 'EvnDirection_id',
		type: 'int'
	}, {
		name: 'otherMO',
		type: 'boolean',
		calculate: function(data){
			var otherMO = false;
			if(getGlobalOptions().lpu_id && data && !Ext6.isEmpty(data.Lpu_id)){
				otherMO = (getGlobalOptions().lpu_id != data.Lpu_id);
			}
			return otherMO;
		}
	}, {
		name: 'EvnStatus_SysNick',
		type: 'string'
	}, {
		name: 'object',
		type: 'string'
	}, {
		name: 'annotate',
		type: 'string'
	},{
		name: 'Lpu_id',
		type: 'int',
		defaultValue: null
	}]
});
Ext6.define('common.EMK.models.EvnDirection', {
	extend: 'Ext6.data.Model',
	fields: [
		{ name: 'EvnDirection_id', type: 'int' },
		{
			name: 'SignHidden',
			type: 'boolean',
			convert: function(val, row) {
				if (/*me.accessType == 'edit' &&*/ row.get('DirType_Code') && row.get('DirType_Code').inlist([1, 2, 3, 4, 5, 6, 8, 9, 12, 13, 23])) {
					return false;
				} else {
					return true;
				}
			}
		},
		{ name: 'EMDRegistry_ObjectName', type: 'string' },
		{ name: 'EMDRegistry_ObjectID', type: 'int' },
		{ name: 'IsSigned', type: 'int' },
		{ name: 'EvnDirection_SignCount', type: 'int' },
		{ name: 'EvnDirection_MinSignCount', type: 'int' },
		{ name: 'EvnStatus_id', type: 'int' },
		{ name: 'DirType_Name', type: 'string' },
		{ name: 'EvnStatus_epvkName', type: 'string' },
		{ name: 'LpuSection_Name', type: 'string' },
		{ name: 'Lpu_Name', type: 'string' },
		{ name: 'Org_Name', type: 'string' },
		{ name: 'Lpu_Nick', type: 'string' },
		{ name: 'Org_Nick', type: 'string' },
		{ name: 'EvnDirection_setDate', type: 'string' },
		{ name: 'EvnDirection_Num', type: 'string' },
		{ name: 'TimetableGraf_id', type: 'string' },
		{ name: 'TimetableMedService_id', type: 'string' },
		{ name: 'TimetableResource_id', type: 'string' },
		{ name: 'TimetableStac_id', type: 'string' },
		{ name: 'EvnQueue_id', type: 'string' },
		{ name: 'DirType_Code', type: 'int' },
		{ name: 'EvnStatus_Name', type: 'string' },
		{ name: 'EvnDirection_statusDate', type: 'string' },
		{ name: 'EvnStatusCause_Name', type: 'string' },
		{ name: 'EvnPrescrMse_id', type: 'int' },
		{ name: 'Lpu_gid', type: 'int' }
	]
});
Ext6.define('common.EMK.models.EvnVaccination', {
	extend: 'Ext6.data.Model',
	fields: [{
		name: 'VaccinType_Name',
		type: 'string'
	}, {
		name: 'VaccinType_Code',
		type: 'string'
	}, {
		name: 'VaccinType_id',
		type: 'string'
	}, {
		name: 'EvnDirection_id',
		type: 'int'
	}]
});