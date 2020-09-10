
sw.Promed.swVolPeriodViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Периоды фактических объёмов',
	layout: 'border',
	id: 'swVolPeriodViewWindow',
	modal: false,
	onHide: Ext.emptyFn,
	onSelect:  Ext.emptyFn,
	shim: false,
	width: 400,
        region: 'center',
//	resizable: true,
	maximizable: true,
	maximized: true,
	listeners:{
            hide:function () {
                this.onHide();
            }
	},	
	show: function() {		
            sw.Promed.swVolPeriodViewWindow.superclass.show.apply(this, arguments);
            this.maximize();
            var wnd = this;
            wnd.onHide = Ext.emptyFn;
            if (arguments[0] && arguments[0].onHide) {
                wnd.onHide = arguments[0].onHide;
            }
            if (arguments[0] && arguments[0].onSelect) {
                wnd.onSelect = arguments[0].onSelect;
                wnd.buttons[0].show();
                wnd.mode = 'select';			
            } else {
                wnd.onSelect = Ext.emptyFn;
                wnd.buttons[0].hide();
                wnd.mode = 'view';
            }
            this.SearchGrid.loadData();
            this.SearchGrid.getGrid().getStore().sort('Period_Sort', 'DESC');

            if (arguments[0] && arguments[0].onlyView){
                wnd.setOnlyView(arguments[0].onlyView);
            } else {//button
                wnd.setOnlyView(false);
            }

	},
	setOnlyView: function(onlyView) {
            if (onlyView) {
                this.SearchGrid.getAction('action_add').hide();
                this.SearchGrid.getAction('action_edit').hide();
                this.SearchGrid.getAction('action_delete').hide();
            } else {
                this.SearchGrid.getAction('action_add').show();
                this.SearchGrid.getAction('action_edit').show();
                this.SearchGrid.getAction('action_delete').show();
            }
            this.SearchGrid.setReadOnly(onlyView);
	},
        deletePeriod: function() 
        {
            var grid = this.SearchGrid.ViewGridPanel,
                record = grid.getSelectionModel().getSelected();
            if( !record ) return false;

            sw.swMsg.show(
            {
                title: lang['podtverjdenie_udaleniya'],
                msg: 'Вы действительно хотите удалить выбранный период?',
                buttons: Ext.Msg.YESNO,
                fn: function(buttonId) {
                    if (buttonId == 'yes') 
                    {
                        // проверим наличие фактических объемов за выбранный период
                        Ext.Ajax.request(
                        {
                            url: '/?c=VolPeriods&m=checkFact',
                            params: 
                            {
                                VolPeriod_id: record.get('VolPeriod_id'),
                                VidMP_id: 0
                            },
                            callback: function(options, success, response) 
                            {
                                if (success) 
                                {
                                    var result = Ext.util.JSON.decode(response.responseText);
                                    // если они есть, то предупреждаем об их удалении
                                    if (result[0].Cnt > 0) 
                                    {
                                        sw.swMsg.show(
                                        {
                                            title: lang['podtverjdenie_udaleniya'],
                                            msg: 'Внимание! При удалении периода также будут удалены сводные данные о фактически выполненных объемах по данным реестров счетов за выбранный период. Вы подтверждаете удаление?',
                                            buttons: Ext.Msg.YESNO,
                                            fn: function(button) 
                                            {
                                                if (button == 'yes') 
                                                {
                                                    // сначала удаляем факты
                                                    Ext.Ajax.request(
                                                    {
                                                        url: '/?c=VolPeriods&m=deleteVolFact',
                                                        params: 
                                                        {
                                                            VolPeriod_id: record.get('VolPeriod_id'),
                                                            Table: 'none'
                                                        },
                                                        callback: function(o, ss, r) 
                                                        {
                                                            // если факты удалены, то удаляем период
                                                            if(ss) 
                                                            {
                                                                Ext.Ajax.request(
                                                                {
                                                                    url: '/?c=VolPeriods&m=deleteVolPeriod',
                                                                    params: 
                                                                    {
                                                                        VolPeriod_id: record.get('VolPeriod_id')
                                                                    },
                                                                    callback: function(o, s, r) 
                                                                    {
                                                                        if(s) 
                                                                        {
                                                                            grid.getStore().remove(record);
                                                                        }
                                                                    }
                                                                });
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    }
                                    // если фактов нет, то просто удаляем период
                                    else
                                    {
                                        Ext.Ajax.request(
                                        {
                                            url: '/?c=VolPeriods&m=deleteVolPeriod',
                                            params: 
                                            {
                                                VolPeriod_id: record.get('VolPeriod_id')
                                            },
                                            callback: function(o, s, r) 
                                            {
                                                if(s) 
                                                {
                                                    grid.getStore().remove(record);
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                        });
                    }
                }
            });
	},
        getGrid: function() {
            var grid = this.SearchGrid;
            return grid;
        },
        reloadGrid: function() {
            this.getGrid().loadData();
        },
	initComponent: function() {
		var wnd = this;
		this.SearchGrid = new sw.Promed.ViewFrame(
                {
                    actions: 
                    [
                        {name: 'action_add'},
                        {name: 'action_edit'},
                        {name: 'action_view', hidden: true},
                        {name: 'action_delete',  handler: this.deletePeriod.createDelegate(this) },
                        {name: 'action_print'}
                    ],
                    autoExpandColumn: 'autoexpand',
                    autoExpandMin: 150,
                    autoLoadData: false,
                    border: true,
                    dataUrl: '/?c=VolPeriods&m=loadVolPeriodList',
                    height: 180,
                    region: 'center',
                    object: 'VolPeriods',
                    editformclassname: 'swVolPeriodEditWindow',
                    id: 'idVolPeriodGrid',
                    paging: false,
                    style: 'margin-bottom: 10px',
                    stringfields: [
                        {name: 'VolPeriod_id', type: 'int', header: 'ID', key: true},
                        {name: 'Period_TimeRange', hidden: true},
                        {name: 'period_sort', header: lang['period'], width: 175, renderer: function(v, p, record) {return record.get('Period_TimeRange');}},
                        {name: 'VolPeriod_Name', type: 'string', header: lang['naimenovanie'], width: 250}
                    ],
                    title: null,
                    toolbar: true,
                    onDblClick: function() {
                        if (wnd.mode == 'select') {
                            wnd.onSelect();
                        } else if (wnd.mode == 'view') {
                            this.ViewActions.action_edit.execute();
                        }

                    }
		});
		
		Ext.apply(this, 
                    {
                        layout: 'border',
                        buttons:
                        [
                            {
                                handler: function() 
                                {
                                    this.ownerCt.onSelect();					
                                },
                            iconCls: 'ok16',
                            text: lang['vyibrat']
                            }, 
                            {
                                text: '-'
                            },
                            HelpButton(this, 0),//todo проставить табиндексы
                            {
                                handler: function()  {
                                    this.ownerCt.hide();
                                },
                                iconCls: 'cancel16',
                                text: BTN_FRMCLOSE
                            }
                        ],
                        tbar: this.WindowToolbar,
                        items:[
                            {
                                border: false,
                                xtype: 'panel',
                                region: 'center',
                                layout: 'border',
                                id: 'drpvGridPanel',
                                items: [wnd.SearchGrid]
                            }
                        ]
                    }
                );
		sw.Promed.swVolPeriodViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});