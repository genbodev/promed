Ext6.define("common.EMK.EvnPLDispDop.store.FactorRiskStore", {//yl:
	extend: "Ext6.data.Store",
	alias: "store.EvnPLDispDop13FactorRiskStore",
	autoLoad: false,
	proxy: {
		type: "ajax",
		url: "/?c=EvnPLDispDop13&m=loadEvnPLDispDop13FactorRisk",
		actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
		reader: {
			type: "json",
			rootProperty: "data"
		},
	}
});

