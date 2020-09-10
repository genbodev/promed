/**
 * swReportEngineWindow.js - окно репозитория отчетов
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

sw.Promed.swReportEngineWindow = Ext.extend(sw.Promed.BaseForm, {
    title      : lang['repozitoriy_otchetov'],
    id         : 'ReportEngineWindow',
    maximized  : true,
    maximizable: false,
    iconCls    : 'rpt-report',
    closable   : true,
    closeAction: 'hide',
    collapsible: true,
    layout     : 'border',
    buttons    : [
    {
        text: BTN_FRMHELP,
        iconCls: 'help16',
        handler: function(button, event)
        {
            ShowHelp(this.ownerCt.title);
        }
    },
    {
        text      : BTN_FRMCLOSE,
        tabIndex  : -1,
        tooltip   : lang['zakryit'],
        iconCls   : 'cancel16',
        handler   : function()
        {
            this.ownerCt.hide();
        }
    }
    ],
    initComponent : function(){
        sw.reports.designer.ui.RepositoryPanel.registerHandler(function(server,viewClass,id,ownerId){
            sw.reports.designer.ui.ContentManager.selectView(server,viewClass,id,ownerId);
        });
        Ext.apply(this,{
        items  : [
            sw.reports.designer.ui.TopMenu.getComponent(),
            sw.reports.designer.ui.RepositoryPanel.getComponent(),
            sw.reports.designer.ui.ContentManager.getComponent()
        ]
        });
        sw.Promed.swReportEngineWindow.superclass.initComponent.apply(this,arguments);
        sw.reports.designer.ui.RepositoryPanel.reloadServers();
    }

});