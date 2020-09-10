/**
* sw.Promed.Store - Store который не заменяет парамсы.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      24.06.2009
*/
sw.Promed.Store = Ext.extend(Ext.data.JsonStore, {
	load: function (options) {
		options = options || {};
		if (this.fireEvent("beforeload", this, options) !== false) {
			this.storeOptions(options);
			var p;
			if (this.notApplyStringFields)
				p = (options.params || {});
			else
				p = Ext.apply(this.baseParams || {}, options.params || {});

			if (this.sortInfo && this.remoteSort) {
				var pn = this.paramNames;
				p[pn["sort"]] = this.sortInfo.field;
				p[pn["dir"]] = this.sortInfo.direction;
			}
			this.proxy.load(p, this.reader, this.loadRecords, this, options);
			return true;
		}
		else {
			return false;
		}
		// sw.Promed.Toolbar.superclass.initComponent.apply(this, arguments);
	},
	notApplyStringFields: false,
	overLimit: false, // признак того, что нам не известно сколько всего записей в paging-гриде
	loadRecords: function (o, options, success) {
		if (!o || success === false) {
			if (success !== false) {
				this.fireEvent("load", this, [], options);
			}
			if (options.callback) {
				options.callback.call(options.scope || this, [], options, false);
			}
			return;
		}

		var r = o.records, t = o.totalRecords || r.length;

		this.overLimit = false;
		if (o.overLimit) {
			if (!options.saveTotalLength) {
				// устанавливаем признак, только если не знаем кол-во записей
				this.overLimit = true;
			}
		} else {
			// если с сервака получили кол-во записей, то обновляем totalLength в сторе в любом случае.
			options.saveTotalLength = false;
		}

		if (!options || options.add !== true) {
			if (this.pruneModifiedRecords) {
				this.modified = [];
			}
			for (var i = 0, len = r.length; i < len; i++) {
				r[i].join(this);
			}
			if (this.snapshot) {
				this.data = this.snapshot;
				delete this.snapshot;
			}
			this.data.clear();
			this.data.addAll(r);
			if (!options.saveTotalLength) {
				this.totalLength = t;
			}
			this.applySort();
			this.fireEvent("datachanged", this);
		} else {
			this.totalLength = Math.max(t, this.data.length + r.length);
			this.add(r);
		}
		this.fireEvent("load", this, r, options);
		if (options.callback) {
			options.callback.call(options.scope || this, r, options, true);
		}
	}
});

sw.Promed.GroupingStore = Ext.extend(sw.Promed.Store, {
    
    //inherit docs
    constructor: function(config){
        Ext.data.GroupingStore.superclass.constructor.call(this, config);
        this.applyGroupField();
    },
    
    
    
    remoteGroup : false,
    
    groupOnSort:false,

    
    clearGrouping : function(){
        this.groupField = false;
        if(this.remoteGroup){
            if(this.baseParams){
                delete this.baseParams.groupBy;
            }
            var lo = this.lastOptions;
            if(lo && lo.params){
                delete lo.params.groupBy;
            }
            this.reload();
        }else{
            this.applySort();
            this.fireEvent('datachanged', this);
        }
    },

    
    groupBy : function(field, forceRegroup){
        if(this.groupField == field && !forceRegroup){
            return; // already grouped by this field
        }
        this.groupField = field;
        this.applyGroupField();
        if(this.groupOnSort){
            this.sort(field);
            return;
        }
        if(this.remoteGroup){
            this.reload();
        }else{
            var si = this.sortInfo || {};
            if(si.field != field){
                this.applySort();
            }else{
                this.sortData(field);
            }
            this.fireEvent('datachanged', this);
        }
    },
    
    // private
    applyGroupField: function(){
        if(this.remoteGroup){
            if(!this.baseParams){
                this.baseParams = {};
            }
            this.baseParams.groupBy = this.groupField;
        }
    },

	groupSortInfo: null,

    // private
    applySort : function(){
        Ext.data.GroupingStore.superclass.applySort.call(this);

		if (this.groupSortInfo) {
			this.sortData(this.groupSortInfo.field, this.groupSortInfo.direction);
		} else if(!this.groupOnSort && !this.remoteGroup && !this.disableGroupSort) {
            var gs = this.getGroupState();
            if(gs && gs != this.sortInfo.field){
                this.sortData(this.groupField);
            }
        }
    },

    // private
    applyGrouping : function(alwaysFireChange){
        if(this.groupField !== false){
            this.groupBy(this.groupField, true);
            return true;
        }else{
            if(alwaysFireChange === true){
                this.fireEvent('datachanged', this);
            }
            return false;
        }
    },

    // private
    getGroupState : function(){
        return this.groupOnSort && this.groupField !== false ?
               (this.sortInfo ? this.sortInfo.field : undefined) : this.groupField;
    }
});