Ext6.define('EvnPLDispDop13ConsentStoreModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.EvnPLDispDop13ConsentStoreModel',
	idProperty: 'DopDispInfoConsent_id',
	fields: [
		{ name: 'DopDispInfoConsent_id', type: 'int'},
		{ name: 'SurveyTypeLink_id', type: 'int'},
		{ name: 'SurveyType_isVizit ', type: 'int' },
		{ name: 'SurveyTypeLink_IsNeedUsluga', type: 'int' },
		{ name: 'SurveyType_Code', type: 'int' },
		{ name: 'SurveyType_RecNotNeeded', type: 'bool' },
		{ name: 'SurveyTypeLink_IsDel', type: 'int' },
		{ name: 'SurveyTypeLink_IsUslPack', type: 'int' },
		{ name: 'DopDispInfoConsent_IsAgeCorrect', type: 'int' },
		{ name: 'SurveyType_Name', type: 'string' },
		{ name: 'DopDispInfoConsent_IsImpossible', type: 'string' },
		{ name: 'DopDispInfoConsent_IsEarlier', type: 'bool' },
		{ name: 'DopDispInfoConsent_IsAgree', type: 'bool' },
		{ name: 'EvnPLDispDop13_id', type: 'int'},
		{ name: 'Lpu_Nick', type: 'int'},
		{ name: 'EvnUsluga_Date', type: 'date', format: 'd.m.Y', dateFormat: 'd.m.Y'},
		{ name: 'Lpu_Nick', type: 'string' }
	]
});

Ext6.define('common.EMK.EvnPLDispDop.store.ConsentStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.EvnPLDispDop13ConsentStore',
	model: 'EvnPLDispDop13ConsentStoreModel',
	proxy: {
		type: 'ajax',
		actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
		url: '/?c=EvnPLDispDop13&m=loadDopDispInfoConsentWithUsluga',
		reader: {
			type: 'json',
			rootProperty: 'data'
		},
		extraParams: '{consentExtraParams}'
	},
	sorters: [
		'DopDispInfoConsent_id'
	]
});