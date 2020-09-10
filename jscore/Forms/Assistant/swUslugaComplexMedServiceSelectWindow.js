/**
 * swUslugaComplexMedServiceSelectWindow - форма выбора исследования
 *
 * Promed - The Regional Medical Information System
 * http://swan.perm.ru/projects/promed
 *
 * @package      common
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Alexander "Alf" Arefyev (avaref@gmail.com)
 * @version      15.03.2012
 */

sw.Promed.swUslugaComplexMedServiceSelectWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh:true,
    objectName:'swUslugaComplexMedServiceSelectWindow',
    objectSrc:'/jscore/Forms/Common/swUslugaComplexMedServiceSelectWindow.js',
    buttonAlign:'left',
    closeAction:'hide',
    layout:'border',
    width: 900,
    height: 500,
	cls: 'newStyle',
	onSelect: function() {
		var win = this;

		var selections = win.UslugaComplexGrid.getGrid().getSelectionModel().getSelections();
		var researches = [];
		var MedService_id = null;

		for	(var key in selections) {
			if (selections[key].data && !Ext.isEmpty(selections[key].data['UslugaComplexMedService_id'])) {
				MedService_id = selections[key].data['MedService_id'];
				researches.push(selections[key].data['UslugaComplexMedService_id']);
			}
		}

		if (researches.length > 0) {
			this.callback(researches, MedService_id);
			this.hide();
		}
	},
    title: lang['dobavlenie_issledovaniya'],
    id:'swUslugaComplexMedServiceSelectWindow',
	refreshUslugaComplexGrid: function() {
		var win = this;

		var record = win.MedServiceGrid.getGrid().getSelectionModel().getSelected();
		if (record && record.get('MedService_id')) {
			win.UslugaComplexGrid.loadData({
				globalFilters: {
					armMode: win.armMode,
					MedService_id: record.get('MedService_id')
				},
				noFocusOnLoad: true
			});
		}
	},
    initComponent:function () {
        var win = this;

		this.MedServiceGrid = new sw.Promed.ViewFrame({
            title:lang['laboratoriya'],
            cls: 'GridWithoutBorders',
            toolbar: false,
            dataUrl:'/?c=MedService&m=loadMedServiceGrid',
            autoLoadData:false,
            /*focusOnFirstLoad:false,*/
            region:'center',
			actions: [
				{ name:'action_add', hidden: true, disabled: true },
				{ name:'action_edit', hidden: true, disabled: true },
				{ name:'action_view', hidden: true, disabled: true },
				{ name:'action_delete', hidden: true, disabled: true }
			],
            stringfields:[
                {name:'MedService_id', type:'int', header:'ID', key:true},
                {name:'MedService_Name', header:lang['naimenovanie'], id:'autoexpand', renderer: function(v, p, row) {
                    return "<img src='img/icons/lab_icon.png' style='float:left; margin-right:5px;' /><div style='margin-top: 1px;'>" + v + "</div>";
                }}
            ],
            onRowSelect: function (){
				win.refreshUslugaComplexGrid();
            }
			
        });

        this.UslugaComplexGrid = new sw.Promed.ViewFrame({
            title: lang['issledovaniya'],
			toolbar: false,
			selectionModel: 'multiselect',
            dataUrl:'/?c=MedService&m=loadUslugaComplexMedServiceGrid',
            actions: [
                { name:'action_add', hidden: true, disabled: true },
                { name:'action_edit', hidden: true, disabled: true },
                { name:'action_view', hidden: true, disabled: true },
                { name:'action_delete', hidden: true, disabled: true }
            ],
            autoLoadData:false,
            focusOnFirstLoad:false,
            region:'center',
			onRowSelectionChange: function(sm) {
				var count = sm.getCount();
				if (count >= 1) {
					this.getAction('action_edit').setDisabled(false);
				} else {
					this.getAction('action_edit').setDisabled(true);
				}
				
				this.getAction('action_view').setDisabled(false);

				this.getGrid().setTitle('Исследования <div style="float: right; font-weight:normal; margin-right: 5px; color: #000;";>Выбрано: ' + count + '</div>')
			},
			onLoadData: function() {
				sm = this.getGrid().getSelectionModel();
				this.onRowSelectionChange(sm);
			},
			onRowDeSelect: function(sm,rowIdx,record) {
				this.onRowSelectionChange(sm);
            },
			onRowSelect: function(sm,rowIdx,record) {
				this.onRowSelectionChange(sm);
            },
            stringfields:[
                {name:'UslugaComplex_id', type:'int', header:'ID', key:true},
                {name:'UslugaComplexMedService_id', type:'int', hidden: true},
                {name:'MedService_id', type:'int', hidden: true},
				{name:'UslugaComplex_Code', header:lang['kod'], width: 100},
                {name:'UslugaComplex_Name', header:lang['naimenovanie'], type:'string', id:'autoexpand'}
            ]
        });
        
        Ext.apply(this, {
            buttons:[
				{
					cls: 'newInGridButton save',
					iconCls:'add16',
					handler:function () {
						win.onSelect();
					},
					text: lang['dobavit']
				},
                {
                    text:'-'
                },
				{
					text: BTN_FRMHELP,
					cls: 'newInGridButton help',
					iconCls: 'help16',
					handler: function(button, event) {
						ShowHelp(win.title);
					}.createDelegate(this)
				},
                {
					cls: 'newInGridButton close',
                    iconCls:'cancel16',
                    handler:function () {
                        win.hide();
                    },
                    text:BTN_FRMCLOSE
                }
            ],
            items:[
                {
                    xtype:'panel',
                    region:'west',
                    width:200,
                    split:true,
                    layout:'border',
                    items:[
                        this.MedServiceGrid
                    ]

                },
                {
                    xtype:'panel',
                    region:'center',
                    layout:'border',
                    items:[
                        this.UslugaComplexGrid
                    ]
                }

            ]
        });
        sw.Promed.swUslugaComplexMedServiceSelectWindow.superclass.initComponent.apply(this, arguments);
    },
    show:function () {
        var win = this;
		
		if (!arguments[0] || !arguments[0].MedService_sid) {
            alert(lang['ne_ukazanyi_obyazatelnyie_vhodnyie_parametryi_medservice_sid']);
            return false;
        }

        if (arguments[0].armMode) {
        	this.armMode = arguments[0].armMode;
		}

		this.MedService_id = arguments[0].MedService_id || null;
		this.MedService_sid = arguments[0].MedService_sid || null;

		this.callback = Ext.emptyFn();
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

        sw.Promed.swUslugaComplexMedServiceSelectWindow.superclass.show.apply(this, arguments);

        this.MedServiceGrid.loadData({
            globalFilters:{
				armMode: win.armMode,
                MedService_id: win.MedService_id,
                MedService_sid: win.MedService_sid
            },
            params:{
                MedService_id: win.MedService_id,
				MedService_sid: win.MedService_sid
            }/*,
            noFocusOnLoad:true*/
        });

		this.UslugaComplexGrid.getGrid().getStore().removeAll();

        this.center();
    }
});
