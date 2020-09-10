/**
 * swMSJobsWindow.js - окно управления джобами MSSQL
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      sw.Promed
 * @access       public
 * @copyright    Copyright (c) 2010 Swan Ltd.
 * @author       yunitsky
 * @version      22.04.2010
 */

sw.Promed.swMSJobsWindow = Ext.extend(sw.Promed.BaseForm, {
    title      : lang['zadaniya_mssql_servera'],
    id         : 'MSJobsWindow',
    maximized  : true,
    maximizable: false,
    iconCls    : 'rpt-report',
    closable   : true,
    closeAction: 'hide',
    collapsible: true,
    layout     : 'border',
    stateToIcon: [
        'sqljobs-error','sqljobs-ok',
        'sqljobs-repeat','sqljobs-cancelled','sqljobs-running','sqljobs-unknown'
    ],
    stateToText: [
        lang['oshibka'],lang['ok'],lang['povtor'],lang['otmenena'],lang['vyipolnyaetsya'],lang['neizvestno']
    ],
    filters    : [0,1,2,3,4,5],
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
        this.jobsCombo = new Ext.form.ComboBox({
            allowBlank     : false,
            minChars       : 0,
            mode           : 'local',
            triggerAction  : 'all',
            emptyText      : lang['vyiberite_zadanie'],
            editable       : false,
            width          : 500,
            forceSelection : true,
            store          : new Ext.data.JsonStore({
                autoLoad   : true,
                url        : C_MSJOBS_GETJOBSLIST,
                root       : 'data',
                id         : 'name',
                fields     : [
                {
                    name : 'enabled',
                    type : 'boolean'
                },
                {
                    name : 'name',
                    type : 'string'
                },
                {
                    name : 'description',
                    type : 'string'
                }
                ]
                
            }),
            displayField : 'name',
            valueField   : 'name',
            tpl          : '<tpl for="."><div class="x-combo-list-item">{name}<span style="color:gray"> {description}</span></div></tpl>',
            listeners    : {
                scope  : this,
                select : this.onJobChange
            }
        });

        var temp = [];
        for(var i = 0; i < this.stateToText.length; i++){
            temp.push(new Ext.Toolbar.Button({
                stateId : i,
                text    : '',
                iconCls : this.stateToIcon[i],
                enableToggle : true,
                pressed      : true,
                handler      : this.onFilterChange,
                scope        : this,
                tooltip      : this.stateToText[i]
            }));
            if(i != this.stateToText.length-1) temp.push(' ');
        }
        
        this.filterToolbar = new Ext.Toolbar({
            items : temp
        });

        
        this.filterCombo = new Ext.form.ComboBox({
            allowBlank     : false,
            mode           : 'local',
            triggerAction  : 'all',
            emptyText      : lang['filtr'],
            editable       : false,
            width          : 200,
            forceSelection : true,
            store : [
                ['','Все'],
                ['1',lang['uspeshnyie']],
                ['0',lang['neudachnyie']],
                ['3',lang['otmenennyie']],
                ['4',lang['vyipolnyayuschiesya']]
            ],
            value : '',
            listeners    : {
                scope  : this,
                select : this.onFilterChange
            }
        });
        
        this.stepsCombo = new Ext.form.ComboBox({
            allowBlank     : false,
            minChars       : 0,
            mode           : 'local',
            triggerAction  : 'all',
            emptyText      : lang['vyiberite_shag'],
            width          : 200,
            forceSelection : true,
            disabled       : true,
            editable       : false,
            store          : new Ext.data.JsonStore({
                autoLoad   : false,
                url        : C_MSJOBS_GETSTEPSLIST,
                baseParams : {jobName : null},
                root       : 'data',
                id         : 'step_id',
                fields     : [
                {
                    name : 'step_id',
                    type : 'int'
                },
                {
                    name : 'step_name',
                    type : 'string'
                }
                ]

            }),
            tpl          : '<tpl for="."><div class="x-combo-list-item">{step_id} - <span style="color:gray"> {step_name}</span></div></tpl>',
            displayField : 'step_name',
            valueField   : 'step_id'
        });

        
        this.refreshButton = new Ext.Button({
            text : lang['obnovit'],
            iconCls  : 'refresh16',
            disabled : true,
            handler  : function(){
                this.refreshContent();
            },
            scope : this
        });

        this.startButton = new Ext.Button({
            text : lang['start'],
            iconCls  : 'start16',
            disabled : true,
            handler  : function(){
                this.run();
            },
            scope : this
        });
        
        this.stopButton = new Ext.Button({
            text : lang['stop'],
            iconCls  : 'stop16',
            disabled : true,
            handler  : function(){
                this.stop();
            },
            scope : this
        });

        this.jobsGrid = new Ext.grid.GridPanel({
            region  : 'center',
            columns : [
            {
                header : ' ',
                dataIndex : 'run_status',
                width : 20,
                sortable : false,
                renderer : (function(value,md){
                    md.css = this.stateToIcon[value];
                    return '';
                }).createDelegate(this)
            },
            {
                header : lang['zapuschena'],
                dataIndex : 'run_datetime',
                width : 100,
                sortable : true,
                renderer : Ext.util.Format.dateRenderer('d.m.Y H:i:s')
            },
            {
                header : lang['ostanovlena'],
                dataIndex : 'stop_datetime',
                width : 100,
                sortable : true,
                renderer : Ext.util.Format.dateRenderer('d.m.Y H:i:s')
            },
            {
                header : lang['status'],
                dataIndex : 'run_status_name',
                renderer  : function(value,md,rec){
                    var color = ['red','green','yellow','gray','blue','red'][rec.data.run_status];
                    return '<span style="color:' + color + '">' + value + '</span>';
                },
                width : 100,
                sortable : true
            }
            ],
            viewConfig : {
                forceFit : true
            },
            store  : new Ext.data.JsonStore({
                autoLoad   : false,
                url        : C_MSJOBS_GETJOBSRUNNING,
                root       : 'data',
                baseParams : {
                    jobName : null
                },
                fields     : [
                {
                    name : 'run_datetime',
                    type : 'date',
                    dateFormat : 'Y-m-d H:i:s'
                },
                {
                    name : 'stop_datetime',
                    type : 'date',
                    dateFormat : 'Y-m-d H:i:s'
                },
                {
                    name : 'run_status_name',
                    type : 'string'
                },
                {
                    name : 'run_status',
                    type : 'int'
                }
                ]
            }),
            sm : new Ext.grid.RowSelectionModel({
                singleSelect : true,
                listeners    : {
                    'selectionchange' : this.onJobSelect,
                    scope       : this
                }
            })
        });
        this.jobsGrid.getStore().on('load',this.onJobsLoaded,this);

        this.stepGrid = new Ext.grid.GridPanel({
            columns : [
            {
                header : ' ',
                dataIndex : 'run_status',
                width : 20,
                sortable : false,
                renderer : (function(value,md){
                    md.css = this.stateToIcon[value];
                    return '';
                }).createDelegate(this)
            },
            {
                header : 'ID',
                dataIndex : 'step_id',
                width : 40,
                sortable : false
            },
//            {
//                header : 'Имя шага',
//                dataIndex : 'step_name',
//                width : 200,
//                sortable : false
//            },
            {
                header : lang['zapuschen'],
                dataIndex : 'run_datetime',
                width : 200,
                sortable : false,
                renderer : Ext.util.Format.dateRenderer('d.m.Y H:i:s')
            },
            {
                header : lang['status'],
                dataIndex : 'run_status_name',
                renderer  : function(value,md,rec){
                    var color = ['red','green','yellow','gray','blue','red'][rec.data.run_status];
                    return '<span style="color:' + color + '">' + value + '</span>';
                },
                width : 200,
                sortable : false
            }
            ],
            store  : new Ext.data.GroupingStore({
                autoLoad   : false,
                url        : C_MSJOBS_GETHISTORY,
                sortInfo:{field: 'run_datetime', direction: "DESC"},
                groupField : 'step_id',
                remoteGroup: true,
                remoteSort : true,
                baseParams : {
                    jobName : null,
                    Start_DT : null,
                    Stop_DT : null
                },
                reader     : new Ext.data.JsonReader({
                    root       : 'data'
                },Ext.data.Record.create(
                    [
                    {
                        name : 'run_datetime',
                        type : 'date',
                        dateFormat : 'Y-m-d H:i:s'
                    },
                    {
                        name : 'step_id',
                        type : 'int'
                    },
                    {
                        name : 'step_name',
                        type : 'string'
                    },
                    {
                        name : 'run_status_name',
                        type : 'string'
                    },
                    {
                        name : 'message',
                        type : 'string'
                    },
                    {
                        name : 'run_status',
                        type : 'int'
                    }
                    ]
                ))
            }),
            view : new Ext.grid.GroupingView({
                forceFit : true,
                enableGroupingMenu : false,
                enableNoGroups : false,
                enableGrouping:false,
                hideGroupedColumn : true,
                groupTextTpl : '<span style="color:red">{group}</span> - {[values.rs[0].data["step_name"] ]}'
            }),
            sm : new Ext.grid.RowSelectionModel({
                singleSelect : true,
                listeners    : {
                    'selectionchange' : this.onStepSelect,
                    scope       : this
                }
            })
        });

        this.logPanel = new Ext.Panel({
            title  : lang['log'],
            region : 'south',
            autoScroll : true,
            split  : true,
            height : '30%'

        });

        this.titlePanel = new Ext.Panel({
            height : 60,
            region : 'north',
            split  : true,
            style  : 'padding:10px;background-color:#FFFFFF',
            border : false
        });

        this.runLabel = new Ext.Toolbar.TextItem({
            disabled : true,
            text : ' undefined '
        });

        Ext.apply(this,{
            tbar : new Ext.Toolbar({
                items : [
                    this.jobsCombo,'-',this.runLabel,'-',this.stepsCombo,'-',
                    this.refreshButton,'-',this.startButton,'-',this.stopButton
                ]
            }),
            items : [
            {
                region : 'west',
                width  : '30%',
                layout : 'border',
                split  : true,
                title  : lang['istoriya_zapuskov'],
                tbar   : this.filterToolbar,
                items  : [  
                    this.titlePanel,
                    this.jobsGrid
                ]
            },
            {
                region : 'center',
                layout : 'border',
                items  : [
                {
                    title  : lang['shagi_vyipolneniya'],
                    layout : 'fit',
                    region : 'center',
                    items  : [ this.stepGrid ]
                },
                this.logPanel
                ]
            }
            ]
        });
        sw.Promed.swMSJobsWindow.superclass.initComponent.apply(this,arguments);

    },
    setRunningText : function(isRunning){
        var color = isRunning ? 'red' : 'green';
        var text = isRunning ? 'running' : 'stopped';
        this.runLabel.el.innerHTML = '<span style="color:'+color+';font-weight:bold"> '+ text +' </span>';
    },
    refreshContent : function(){
        this.jobsCombo.fireEvent('select',this.jobsCombo,
            this.jobsCombo.getStore().getById(this.jobsCombo.getValue()));
    },
    onFilterChange : function(btn){
        if(btn){
            if(btn.pressed){
                this.filters.push(btn.stateId);
            } else {
                this.filters.splice(this.filters.indexOf(btn.stateId),1);
            }
        }
        var filter = this.filterCombo.getValue();
        this.jobsGrid.getStore().filterBy(function(record){
            if(this.filters.indexOf(record.data.run_status) != -1) return true;
            return false;
        },this);
    },
    onJobChange : function(combo,record,num){
        this.titlePanel.removeAll();
        this.titlePanel.add(new Ext.Panel({
            border : false,
            html   : record.data.description
        }));
        this.titlePanel.doLayout();
        this.jobsGrid.getStore().baseParams.jobName = record.data.name;
        this.jobsGrid.getStore().load({
            callback : function(){
                this.onFilterChange();
            },
            scope : this
        });
        this.stepsCombo.getStore().removeAll();
        this.stepsCombo.getStore().baseParams.jobName = record.data.name;
        this.stepsCombo.getStore().load({
            callback : function(){
                this.stepsCombo.setValue(1);
            },
            scope    : this
        });
        this.refreshButton.enable();
    },
    /**
     * При перегрузке джоба. Проверяем не запущен ли он
     * в данный момент.
     */
    onJobsLoaded : function(store,records){
        Ext.Ajax.request({
            url     : C_MSJOBS_ISJOBRUNNING,
            method  : 'POST',
            params  : {jobName : this.jobsCombo.getValue()},
            scope   : this,
            success : function(response){
                var data = Ext.util.JSON.decode(response.responseText);
                if(!data.data){
                    this.startButton.enable();
                    this.stepsCombo.enable();
                    this.stopButton.disable();
                    this.setRunningText(false);
                } else {
                    this.startButton.disable();
                    this.stepsCombo.disable();
                    this.stopButton.enable();
                    this.setRunningText(true);
                }
            }
        });
    },
    onJobSelect : function(sm){
        var rec = sm.getSelected();
        if(rec){
            this.stepGrid.getStore().baseParams.jobName = this.jobsCombo.getValue();
            this.stepGrid.getStore().baseParams.Start_DT = rec.data.run_datetime;
            this.stepGrid.getStore().baseParams.Stop_DT = rec.data.stop_datetime;
            this.stepGrid.getStore().reload();
        } else this.stepGrid.getStore().removeAll();
    },
    onStepSelect : function(sm){
        var rec = sm.getSelected();
        if(rec){
            this.logPanel.removeAll();
            this.logPanel.add(new Ext.Panel({
                style : 'padding:10px;line-height:14px',
                border: false,
                html  : rec.data.message
            }));
            this.logPanel.doLayout();
        } else this.logPanel.removeAll();
    },
    /**
     * Запускаем задачу.
     */
    run : function(){
        var job = this.jobsCombo.getValue();
        var step = this.stepsCombo.getRawValue();
        if(!job || !step) return;
        Ext.Ajax.request({
            url : C_MSJOBS_STARTJOB,
            params  : {jobName : job, stepName : step},
            success : this.refreshContent.defer(1000,this),
            scope   : this
        });
    },
    stop : function(){
        var job = this.jobsCombo.getValue();
        if(!job) return;
        Ext.Ajax.request({
            url : C_MSJOBS_STOPJOB,
            params  : {jobName : job},
            success : this.refreshContent.defer(1000,this),
            scope   : this
        });
    }


});