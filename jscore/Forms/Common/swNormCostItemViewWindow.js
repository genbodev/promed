/**
 * swNormCostItemViewWindow - окно редактирования норм
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

sw.Promed.swNormCostItemViewWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh:true,
    objectName:'swNormCostItemViewWindow',
    objectSrc:'/jscore/Forms/Common/swNormCostItemViewWindow.js',
    buttonAlign:'left',
    closeAction:'hide',
    layout:'border',
    listeners:{
        'hide':function () {
            this.onHide();
        }
    },
    title:lang['normativyi_rashoda_reaktivov'],
    id:'swNormCostItemViewWindow',
    maximized:true,
	onTreeSelect: function (sm, node) {
		if (!node) 
			return false;
		
		let that = this;
		let AnalyzerTest_id = node.attributes.object_value;
		let UslugaComplex_id = node.attributes.UslugaComplex_id;

		if (Ext.isEmpty(AnalyzerTest_id)) 
			return false;
		
		that.compUslDetailGrid.loadData({
			globalFilters:{
				MedService_id: that.MedService_id,
				AnalyzerTest_id: AnalyzerTest_id
			},
			params: {
				MedService_id: that.MedService_id,
				AnalyzerTest_id: AnalyzerTest_id,
				UslugaComplex_id: UslugaComplex_id
			},
			noFocusOnLoad:true
		});
	},
    initComponent:function () {
        var that = this;

		this.AnalyzerTestTree = new Ext.tree.TreePanel({
			animate: false,
			autoLoad: false,
			autoScroll: true,
			border: true,
			enableDD: false,
			getLoadTreeMask: function(MSG) {
				if ( MSG )  {
					delete(this.loadMask);
				}

				if ( !this.loadMask ) {
					this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
				}

				return this.loadMask;
			},
			loader: new Ext.tree.TreeLoader( {
				listeners: {
					'beforeload': function (tl, node) {
						if (!that.MedService_id)
							return false;
						
						that.AnalyzerTestTree.getLoadTreeMask(lang['zagruzka_dereva_issledovanii']).show();
						
						tl.baseParams.MedService_id = that.MedService_id;
						tl.baseParams.IsActive = 2;
						if ( node.getDepth() > 0 ) {
							tl.baseParams.AnalyzerTest_pid = node.attributes.object_value;
						} else {
							tl.baseParams.AnalyzerTest_pid = '';
						}
					},
					'load': function(node) {
						callback: {
							that.AnalyzerTestTree.getLoadTreeMask().hide();
						}
					}
				},
				dataUrl:'/?c=AnalyzerTest&m=loadAnalyzerTestTree'
			}),
			region: 'west',
			root: {
				nodeType: 'async',
				text: lang['issledovaniya'],
				id: 'root',
				expanded: true
			},
			rootVisible: false,
			split: true,
			title: lang['issledovaniya'],
			width: 300
		});

		this.AnalyzerTestTree.getSelectionModel().on('selectionchange', function(sm, node) {
			that.onTreeSelect(sm, node);
		});
		
		
        this.compUslDetailGrid = new sw.Promed.ViewFrame({
            title:lang['rashod_reaktivov'],
            dataUrl:'/?c=NormCostItem&m=loadNormCostItemGrid',
			editformclassname: 'swNormCostItemEditWindow',
			obj_isEvn: false,
			object: 'NormCostItem',
            actions: [
				{name: 'action_add' },
				{name: 'action_edit' },
				{name: 'action_view' },
				{name: 'action_delete', url: '/?c=NormCostItem&m=deleteNormCostItem' },
				{name: 'action_refresh' },
				{name: 'action_print' }
            ],
            autoLoadData:false,
            focusOnFirstLoad:false,
            region:'center',
            stringfields:[
                {name:'NormCostItem_id', type:'int', header:'ID', key:true},
                {name:'AnalyzerTest_id', type:'int', hidden: true},
				{name:'DrugNomen_Code', header:lang['kod'], width:80},
                {name:'DrugNomen_Name', header:lang['naimenovanie'], type:'string', id:'autoexpand'},
                {name:'NormCostItem_Kolvo', header:lang['normativ_rashoda'], type:'float', width:120},
                {name:'Unit_Name', header:lang['ed_izmereniya'], type:'string', width:100},
                {name:'Analyzer_Name', header:lang['analizator'], type:'string', width:100},
                {name:'NormCostItem_updDT', header:lang['data_obnovleniya'], type:'date', width:100}
            ]
        });
        
        Ext.apply(this, {
            buttons:[
                {
                    text:'-'
                },
                HelpButton(this, 0),
                {
                    iconCls:'cancel16',
                    handler:function () {
                        this.ownerCt.hide();
                    },
                    text:BTN_FRMCLOSE
                }
            ],
            items:[
            	this.AnalyzerTestTree,
                {
                    xtype:'panel',
                    region:'center',
                    layout:'border',
                    items:[
                        this.compUslDetailGrid
                    ]
                }

            ]
        });
        sw.Promed.swNormCostItemViewWindow.superclass.initComponent.apply(this, arguments);
    },
    show:function () {
		if (!arguments[0] || !arguments[0].MedService_id) {
            alert(lang['ne_ukazanyi_obyazatelnyie_vhodnyie_parametryi_medservice_id']);
            return false;
        }

		this.MedService_id = arguments[0].MedService_id;
        
        sw.Promed.swNormCostItemViewWindow.superclass.show.apply(this, arguments);

		this.AnalyzerTestTree.getRootNode().select();
		this.AnalyzerTestTree.getRootNode().reload();
    }
});
