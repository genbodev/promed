
Ext6.define('common.EMK.EvnPLDispDop.model.MainModel', {
	extend: 'Ext6.app.ViewModel',
	requires: [
		'common.EMK.EvnPLDispDop.store.ConsentStore',
		'common.EMK.EvnPLDispDop.store.DeseaseStore'
	],
	alias: 'viewmodel.EvnPLDispDop13MainModel',
	data: {
		//вспомогательные переменные:
		enableEdit: true,
		saveDopDispInfoConsentAfterLoad: false,
		blockSaveDopDispInfoConsent: false,
		inWowRegister: false,
		params: {}, //храним то что получили в setParams
		action: 'add',
		SurveyType_Codes: [],
		count85Percent: 0,
		//значения на форме:
		Server_id: null,
		Person_id: null,
		DispClass_id: 1,
		EvnPLDispDop13_consDate: new Date(),
		EvnPLDispDop13_id: null,
		EvnPLDispDop13_fid: null,
		EvnPLDispDop13Sec_id: null,
		EvnPLDispDop13_IsPaid: null,
		HealthKind_id: null,
		PayType_id: 1, //по умолчанию: ОМС
		EvnPLDispDop13_IsMobile: false, //checkbox обслужен мобильной бригадой
		PersonAgree: true,//по умолчанию: согласие получено  //==PersonFirstStageAgree
		EvnPLDispDop13_IsOutLpu: false,//checkbox Проведен вне МО
		EvnPLDispDop13_SumRick: null,
		accessType: null,
		PersonEvn_id: null,
		Lpu_mid: null,
		person_weight: 0,
		person_height: 0,
		waist_circumference: 0,
		therapistEditorText: '',
		anketa_edit_disabled: true,
		systolic_blood_pressure: null,
		diastolic_blood_pressure: null,
		total_cholesterol: null,
		EvnPLDispDop13_IsDisp: null,
		PersonDisp_id: null,
		TherapistViewData: null,
		Terapevt_OnkoDiag_Code: null
	},
	reset: function() {
		var vm = this;
		vm.setData({
			DispClass_id: 1,
			Server_id: null,
			Person_id: null,
			PersonEvn_id: null,
			anketa_edit_disabled: true,
			Person_Age: 0,
			Sex_Name: '',
			EvnPLDispDop13_IsMobile: false,
			EvnPLDispDop13_SumRick : null,
			EvnPLDispDop13_IsStenocard: 1,
			EvnPLDispDop13_IsDoubleScan: 1,
			EvnPLDispDop13_IsTIA: 1,
			EvnPLDispDop13_IsSpirometry: 1,
			EvnPLDispDop13_IsLungs: 1,
			EvnPLDispDop13_IsHeartFailure: 1,
			EvnPLDispDop13_IsIrrational: 1,
			EvnPLDispDop13_IsUseNarko: 1,
			EvnPLDispDop13_IsBrain: 1,
			EvnPLDispDop13_IsTub: 1,
			EvnPLDispDop13_IsEsophag: 1,
			EvnPLDispDop13_IsSmoking: null,
			EvnPLDispDop13_IsRiskAlco: 1,
			EvnPLDispDop13_IsLowActiv: 1,
			EvnPLDispDop13_IsAlcoDepend: 1,
			EvnPLDispDop13_IsTopGastro: 1,
			EvnPLDispDop13_IsBotGastro: 1,
			EvnPLDispDop13_IsOncology: 1,
			systolic_blood_pressure: null,
			diastolic_blood_pressure: null,
			total_cholesterol: null,
			HealthKind_id: null,
			EvnPLDispDop13_IsEndStage: null,
			EvnPLDispDop13_IsDisp: null,
			PersonDisp_id: null,
			Terapevt_OnkoDiag_Code: null
		});
		
		/*
		
		[2,3,5,6,7,8,31,127,19].forEach(function(SurveyType_Code) {
			vm.set('MedPersonal_SurveyType_Code'+SurveyType_Code,'');
			vm.set('LpuNick_SurveyType_Code'+SurveyType_Code,'');
			vm.set('Date_SurveyType_Code'+SurveyType_Code,'');
		});*/
		
		//var accordion = null;
		//if(vm.getView().AccordionPanel) 
		
		var accordion = !Ext6.isEmpty(vm.getView().ownerPanel.AccordionPanel) ? 
			vm.getView().ownerPanel.AccordionPanel :
			!Ext6.isEmpty(vm.getView().AccordionPanel) ? vm.getView().AccordionPanel : null;
		if(accordion)
		accordion.items.getRange().forEach(function(panel) {
			if(panel.SurveyType_Code) {
				vm.set('MedPersonal_SurveyType_Code'+panel.SurveyType_Code,'');
				vm.set('LpuNick_SurveyType_Code'+panel.SurveyType_Code,'');
				vm.set('Date_SurveyType_Code'+panel.SurveyType_Code,'');
			}
		});
	},
	setParams: function (params) {
		var vm = this;
		if (params) {
			vm.set('params', params);
		} else {
			//чтобы лишний раз не переприсваивать
			var p = vm.get('params');
			['Person_id', 'Server_id', 'EvnPLDispDop13_id'].forEach(function (name) {
				//if (p[name]) {
					vm.set(name, p[name]);
				//}
			});
		}
	},
	bindings: {
		onEvnPLDispDop13_IsDisp: '{EvnPLDispDop13_IsDisp}',
	},
	formulas: {
		body_mass_index: function (get) {
			var h = get('person_height');
			var w = get('person_weight');
			return (h > 0) ? w / (h/100 * h/100) : '';
		},
		isEvnPLDispDop13_id: function (get) {
			return Number(get('EvnPLDispDop13_id')) > 0;
		},
		consentExtraParams: function (get) {
			//параметры для таблицы согласий/услуг
			var dt = get('EvnPLDispDop13_consDate');
			return {
				Person_id: get('Person_id'),
				DispClass_id: get('DispClass_id'),
				EvnPLDispDop13_id: get('EvnPLDispDop13_id'),
				EvnPLDispDop13_fid: get('EvnPLDispDop13_fid'),
				EvnPLDispDop13_IsNewOrder: null,
				EvnPLDispDop13_consDate: (typeof dt == 'object' ? Ext6.util.Format.date(dt, 'd.m.Y') : dt)
			};
		},
		EvnPLDispDop13_consDateString: function(get) {
			if(Ext6.isDate(get('EvnPLDispDop13_consDate')))
				return get('EvnPLDispDop13_consDate').dateFormat('d.m.Y');
			else return '';
		},
		isHiddenSurveyType_Code1: function (get) {
			return !get('SurveyType_Codes').in_array(1);
		},
		isHiddenSurveyType_Code2: function (get) {
			//опрос
			return !get('SurveyType_Codes').in_array(2);
		},
		isHiddenSurveyType_Code3: function (get) {
			return !get('SurveyType_Codes').in_array(3);
		},
		isHiddenSurveyType_Code4: function (get) {
			//антропометрия
			return !get('SurveyType_Codes').in_array(4);
		},
		isHiddenSurveyType_Code5: function (get) {
			return !get('SurveyType_Codes').in_array(5);
		},
		isHiddenSurveyType_Code6: function (get) {
			return !get('SurveyType_Codes').in_array(6);
		},
		isHiddenSurveyType_Code7: function (get) {
			return !get('SurveyType_Codes').in_array(7);
		},
		isHiddenSurveyType_Code19: function (get) {
			return !get('SurveyType_Codes').in_array(19);
		},
		isHiddenSurveyType_Code8: function(get) {
			return !get('SurveyType_Codes').in_array(8);
		},
		isHiddenSurveyType_Code96: function(get) {
			return !get('SurveyType_Codes').in_array(96);
		},
		isHiddenSurveyType_Code97: function(get) {
			return !get('SurveyType_Codes').in_array(97);
		},
		isHiddenSurveyType_Code163: function (get) {
			return !get('SurveyType_Codes').in_array(163);
		},
		isHiddenSurveyType_Code139: function (get) {
			return !get('SurveyType_Codes').in_array(139);
		},
		isHiddenSurveyType_Code31: function (get) {
			return !get('SurveyType_Codes').in_array(31);
		},
		body_mass_index_over_max: function(get) {
			return Number(get('body_mass_index')) > 25;
		},
		body_mass_index_over_min: function(get) {
			return Number(get('body_mass_index')) <18;
		},
		getRiskTypeName: function(get) {
			switch(Number(get('RiskType_id'))) {
				case 1: return 'низкий';
				case 2: return 'умеренный';
				case 3: return 'высокий';
				case 4: return 'очень высокий';
				default: return '';
			}
		},
		isSmokingLabel: function(get) {
			switch (Number(get('EvnPLDispDop13_IsSmoking'))) {
				case 2: return 'Да';
				case 1: return 'Нет';
				default: return '';
			}
		},
		prescrExtraParams: function (get) {
			//параметры для таблицы согласий/услуг
			var dt = get('EvnPLDispDop13_consDate');
			return {
				userLpuSection_id: getGlobalOptions().CurLpuSection_id,
				UslugaComplexList: get('UslugaComplexList'),
				EvnPLDispDop13_id: get('EvnPLDispDop13_id')
			};
		}
	},
	stores: {
		ConsentStore: {
			type: 'EvnPLDispDop13ConsentStore',
			proxy: {
				extraParams: '{consentExtraParams}'
			},
			listeners: {
				load: 'onLoadConsentGrid'
			}
		},
		/*DeseaseStore: {
			type: 'EvnPLDispDop13DeseaseStore',
			proxy: {
				extraParams: '{consentExtraParams}'
			}
		},*/
		/*PrescrStore: {
			type: 'EvnPLDispDop13PrescrStore',
			proxy: {
				extraParams: '{prescrExtraParams}'
			},
			listeners: {
				load: 'onLoadPrescrGrid'
			}
		}*/
	}
});