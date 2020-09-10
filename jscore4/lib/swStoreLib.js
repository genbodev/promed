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
//sw.Promed.Store = Ext.extend(Ext.data.JsonStore,

Ext.define('swPromedStore',
{
	extend:'Ext.data.JsonStore',
	load : function(options)
	{
		options = options || {};
		if(this.fireEvent("beforeload", this, options) !== false)
		{
			this.storeOptions(options);
			var p = Ext.apply(this.baseParams || {}, options.params || {});
			
			if(this.sortInfo && this.remoteSort)
			{
				var pn = this.paramNames;
				p[pn["sort"]] = this.sortInfo.field;
				p[pn["dir"]] = this.sortInfo.direction;
			}
			this.proxy.load(p, this.reader, this.loadRecords, this, options);
			return true;
		}
		else
		{
			return false;
		}
		// sw.Promed.Toolbar.superclass.initComponent.apply(this, arguments);
  }
});

//sw.Promed.GroupingStore = Ext.extend(sw.Promed.Store, {
Ext.define('swPromedGroupingStore',{
    //inherit docs
	extend: 'swPromedStore',
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

    // private
    applySort : function(){
        Ext.data.GroupingStore.superclass.applySort.call(this);
        if(!this.groupOnSort && !this.remoteGroup){
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