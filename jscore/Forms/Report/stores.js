/**
* stores.js -
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       yunitsky
* @version      22.04.2010
 */

Ext.ns('sw.reports.stores');

sw.reports.stores.Servers = function(){

    var _store = null;

    return {
        getInstance : function(){
            if(_store === null){
                _store = new Ext.data.Store({
                   storeId  : Ext.id(),
                   autoLoad : false,
                   reader   : new Ext.data.JsonReader({
                       root     : 'items',
                       id       : 'id',
                       totalProperty : 'total'
                   }, sw.reports.records.Server),
                   proxy    : new Ext.data.HttpProxy({
                       url      : sw.consts.url(sw.consts.actions.GET_SERVERS_LIST)
                   })
                });
            }
            return _store;
        }
    }
}();

sw.reports.stores.ReportTable = function(){

    var _stores = [];

    return {
        getInstance : function(serverId){
            if(_stores[serverId] == null){
                _stores[serverId] = new Ext.data.Store({
                   storeId  : Ext.id(),
                   autoLoad : false,
                   baseParams : {serverId : serverId, ownerId : 'all' },
                   reader   : new Ext.data.JsonReader({
                       root     : 'items',
                       id       : 'Report_id',
                       totalProperty : 'total'
                   }, sw.reports.records.ReportTable),
                   proxy    : new Ext.data.HttpProxy({
                       url      : sw.consts.url(sw.consts.actions.GET_TABLE_REPORT)
                   })
                });
            }
            return _stores[serverId];
        }
    }
}();

sw.reports.stores.ReportParameterTable = function(){

    var _stores = [];

    return {
        getInstance : function(serverId){
            if(_stores[serverId] == null){
                _stores[serverId] = new Ext.data.Store({
                   storeId  : Ext.id(),
                   autoLoad : true,
                   baseParams : {serverId : serverId, ownerId : 'all' },
                   reader   : new Ext.data.JsonReader({
                       root     : 'items',
                       id       : 'ReportParameter_id',
                       totalProperty : 'total'
                   }, sw.reports.records.ReportParameterTable),
                   proxy    : new Ext.data.HttpProxy({
                       url      : sw.consts.url(sw.consts.actions.GET_TABLE_REPORTPARAMETER)
                   })
                });
            }
            return _stores[serverId];
        }
    }
}();


sw.reports.stores.ReportParameterCatalogTable = function(){

    var _stores = [];

    return {
        getInstance : function(serverId){
            if(_stores[serverId] == null){
                _stores[serverId] = new Ext.data.Store({
                   storeId  : Ext.id(),
                   autoLoad : false,
                   baseParams : {serverId : serverId, ownerId : 'all' },
                   reader   : new Ext.data.JsonReader({
                       root     : 'items',
                       id       : 'ReportParameterCatalog_id',
                       totalProperty : 'total'
                   }, sw.reports.records.ReportParameterCatalogTable),
                   proxy    : new Ext.data.HttpProxy({
                       url      : sw.consts.url(sw.consts.actions.GET_TABLE_REPORTPARAMETERCATALOG)
                   })
                });
            }
            return _stores[serverId];
        }
    }
}();


sw.reports.stores.ReportContentTable = function(){

    var _stores = [];

    return {
        getInstance : function(serverId){
            if(_stores[serverId] == null){
                _stores[serverId] = new Ext.data.Store({
                   storeId  : Ext.id(),
                   autoLoad : false,
                   baseParams : {serverId : serverId, ownerId : 'all' },
                   reader   : new Ext.data.JsonReader({
                       root     : 'items',
                       id       : 'ReportContent_id',
                       totalProperty : 'total'
                   }, sw.reports.records.ReportContentTable),
                   proxy    : new Ext.data.HttpProxy({
                       url      : sw.consts.url(sw.consts.actions.GET_TABLE_REPORTCONTENT)
                   })
                });
            }
            return _stores[serverId];
        }
    }
}();

sw.reports.stores.ReportContentParameterTable = function(){

    var _stores = [];

    return {
        getInstance : function(serverId){
            if(_stores[serverId] == null){
                _stores[serverId] = new Ext.data.Store({
                   storeId  : Ext.id(),
                   autoLoad : false,
                   baseParams : {serverId : serverId, ownerId : 'all', isFieldset : false },
                   reader   : new Ext.data.JsonReader({
                       root     : 'items',
                       id       : 'ReportContentParameter_id',
                       totalProperty : 'total'
                   }, sw.reports.records.ReportContentParameterTable),
                   proxy    : new Ext.data.HttpProxy({
                       url      : sw.consts.url(sw.consts.actions.GET_TABLE_REPORTCONTENTPARAMETER)
                   })
                });
            }
            return _stores[serverId];
        }
    }
}();


sw.reports.stores.ReportCatalogTable = function(){

    var _stores = [];

    return {
        getInstance : function(serverId){
            if(_stores[serverId] == null){
                _stores[serverId] = new Ext.data.Store({
                   storeId  : Ext.id(),
                   autoLoad : false,
                   baseParams : {serverId : serverId, ownerId : 'all' },
                   reader   : new Ext.data.JsonReader({
                       root     : 'items',
                       id       : 'ReportCatalog_id',
                       totalProperty : 'total'
                   }, sw.reports.records.ReportCatalogTable),
                   proxy    : new Ext.data.HttpProxy({
                       url      : sw.consts.url(sw.consts.actions.GET_TABLE_REPORTCATALOG)
                   })
                });
            }
            return _stores[serverId];
        }
    }
}();

