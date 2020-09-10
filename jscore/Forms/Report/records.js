/**
* records.js -
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

Ext.ns('sw.reports.records');

sw.reports.records.Server = Ext.data.Record.create([
    { name : 'id', type : 'int'},
    { name : 'username', type : 'string'},
    { name : 'hostname', type : 'string'},
    { name : 'database', type : 'string'},
    { name : 'title', type : 'string'},
    { name : 'status', type : 'string'}
]);

sw.reports.records.ReportTable = new Ext.data.Record.create([
    { name : 'Report_id', type : 'int' },
    { name : 'Region_ids', type: 'string' },
    { name : 'ReportCatalog_id', type : 'int' },
    { name : 'Report_Caption', type : 'string' },
    { name : 'Report_Title', type : 'string' },
    { name : 'Report_Description', type : 'string' },
    { name : 'Report_FileName', type : 'string' },
    { name : 'Report_Status', type : 'int' },
	{ name : 'ReportType_id', type : 'int' },
    { name : 'Report_Position', type : 'int' },
	{ name : 'DatabaseType', type : 'int' }
]);

sw.reports.records.ReportParameterTable = new Ext.data.Record.create([
    { name : 'ReportParameter_id', type : 'int' },
    { name : 'ReportParameterCatalog_id', type : 'int' },
    { name : 'ReportParameter_Name', type : 'string' },
    { name : 'ReportParameter_Type', type : 'string' },
    { name : 'ReportParameter_Label', type : 'string' },
    { name : 'ReportParameter_Mask', type : 'string' },
    { name : 'ReportParameter_RegionName', type : 'string'},
	{ name : 'Region_id', type : 'int' },
    { name : 'ReportParameter_Length', type : 'int' },
    { name : 'ReportParameter_MaxLength', type : 'int' },
    { name : 'ReportParameter_Default', type : 'string' },
    { name : 'ReportParameter_Align', type : 'string' },
    { name : 'ReportParameter_CustomStyle', type : 'string' },
    { name : 'ReportParameter_SQL', type : 'string' },
    { name : 'ReportParameter_SQL_IdField', type : 'string' },
    { name : 'ReportParameter_SQL_TextField', type : 'string' },
    { name : 'ReportParameter_SQL_XTemplate', type : 'string' }
]);

sw.reports.records.ReportParameterCatalogTable = new Ext.data.Record.create([
    { name : 'ReportParameterCatalog_id', type : 'int' },
    { name : 'ReportParameterCatalog_pid', type : 'int' },
    { name : 'ReportParameterCatalog_Name', type : 'string' }
]);


sw.reports.records.ReportContentTable = new Ext.data.Record.create([
    { name : 'ReportContent_id', type : 'int' },
    { name : 'Report_id', type : 'int' },
    { name : 'ReportContent_Name', type : 'string' },
    { name : 'ReportContent_Position', type : 'int' }
]);

sw.reports.records.ReportContentParameterTable = new Ext.data.Record.create([
    { name : 'ReportContentParameter_id', type : 'int' },
    { name : 'ReportContent_id', type : 'int' },
    { name : 'ReportParameter_id', type : 'int' },
    { name : 'ReportContentParameter_Default', type : 'string' },
    { name : 'ReportContentParameter_Required', type : 'int' },
    { name : 'ReportContentParameter_ReportId', type : 'string' },
    { name : 'ReportContentParameter_ReportLabel', type : 'string' },
    { name : 'ReportContentParameter_Position', type : 'int' },
    { name : 'ReportContentParameter_PrefixId', type : 'string' },
    { name : 'ReportContentParameter_PrefixText', type : 'string' },
    { name : 'ReportContentParameter_SQL', type: 'string'},
    { name : 'ReportContentParameter_SQLIdField', type: 'string'},
    { name : 'ReportContentParameter_SQLTextField', type: 'string'},
    { name : 'Region_ids', type: 'string'},
    // only for visualization
    { name : 'originalId', type : 'string' },
    { name : 'originalLabel', type : 'string' },
    { name : 'originalDefault', type : 'string' }
]);

sw.reports.records.ReportCatalogTable = new Ext.data.Record.create([
    { name : 'ReportCatalog_id', type : 'int' },
    { name : 'ReportCatalog_pid', type : 'int' },
    { name : 'ReportCatalog_Name', type : 'string' },
	{ name : 'Region_id', type: 'int'},
    { name : 'Region_ids', type: 'string'},
    { name : 'ReportCatalog_Status', type : 'int' },
    { name : 'ReportCatalog_Path', type : 'string' },
    { name : 'ReportCatalog_Position', type : 'int' }
]);

