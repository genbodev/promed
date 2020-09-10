//yl:основной грид Подозрений и Диагнозов
Ext6.define("common.EMK.EvnPLDispDop.store.DeseaseStore", {
	extend: "Ext6.data.Store",
	alias: "store.EvnPLDispDop13DeseaseStore",
	autoLoad: false,
	proxy: {
		type: "ajax",
		url: "/?c=EvnPLDispDop13&m=loadEvnPLDispDop13Desease",
		actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
		reader: {
			type: "json",
			rootProperty: "data"
		},
	}
});

//yl:комбо редактора грида с Типами Диагнозов
Ext6.define("common.EMK.EvnPLDispDop.store.DiagSetClassStore", {//yl:
	extend: "Ext6.data.Store",
	alias: "store.EvnPLDispDop13DiagSetClassStore",
	autoLoad: false,
	proxy: {
		type: "ajax",
		actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
		url : "/?c=EvnPLDispDop13&m=loadEvnPLDispDop13DiagSetClass",
		reader: {
			type: "json",
			rootProperty: "data"
		}
	}
});

