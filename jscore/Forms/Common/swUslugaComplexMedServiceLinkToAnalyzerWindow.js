/**
 * swUslugaComplexMedServiceLinkToAnalyzerWindow - окно связи услуг лаборатории с анализаторами
 *
 * Promed - The Regional Medical Information System
 * http://swan.perm.ru/projects/promed
 *
 * @package      common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      11.12.2013
 */

sw.Promed.swUslugaComplexMedServiceLinkToAnalyzerWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh:true,
    objectName:'swUslugaComplexMedServiceLinkToAnalyzerWindow',
    objectSrc:'/jscore/Forms/Common/swUslugaComplexMedServiceLinkToAnalyzerWindow.js',
    buttonAlign:'left',
    closeAction:'hide',
    layout:'border',
    listeners:{
        'hide':function () {
            this.onHide();
        }
    },
    title:lang['svyaz_uslug_slujbyi_s_analizatorami'],
    id:'swUslugaComplexMedServiceLinkToAnalyzerWindow',
    maximized:true,
	refreshCompUslDetailGrid: function(record)  {
		var that = this;
		that.compUslDetailGrid.loadData({
			globalFilters:{
				MedService_id: that.MedService_id,
				UslugaComplexMedService_pid: record.get('UslugaComplexMedService_id')
			},
			noFocusOnLoad:true
		});
	},
    initComponent:function () {
        var that = this;
        this.compUslGrid = new sw.Promed.ViewFrame({
            title:lang['kompleksnyie_uslugi'],
			selectionModel: 'multiselect',
			id: 'swUslugaComplexMedServiceGrid',
            actions:[
                {
                    name:'action_edit',
                    handler:function () {
						var sel = new Array();
						that.compUslGrid.getGrid().getSelectionModel().getSelections().forEach(function (el) {
							if (!Ext.isEmpty(el.data.UslugaComplexMedService_id)) {
								sel.push(el.data.UslugaComplexMedService_id);
							}
						});
						
						getWnd('swUslugaComplexMedServiceSelectAnalyzerWindow').show({
							callback: function() {
								that.compUslGrid.getGrid().getStore().reload();
							},
							UslugaComplexMedService_ids: Ext.util.JSON.encode(sel),
							MedService_id: that.MedService_id
						});
                    },
                    text: lang['svyazat_s_analizatorom']
                },
                { name:'action_add', disabled: true, hidden:true },
                { name:'action_view', disabled: true, hidden:true },
                { name:'action_delete', disabled: true, hidden:true },
                { name:'action_print', disabled: true, hidden:true }
            ],
			onRowSelectionChange: function(sm) {
				if (sm.getCount() >= 1) {
					this.getAction('action_edit').setDisabled(false);
				} else {
					this.getAction('action_edit').setDisabled(true);
				}
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
				that.refreshCompUslDetailGrid(record);
            },
            dataUrl:'/?c=AnalyzerTest&m=getUnlinkedUslugaComplexMedServiceGrid',
            autoLoadData:false,
            /*focusOnFirstLoad:false,*/
            region:'center',
            stringfields:[
				{name:'UslugaComplexMedService_id', type:'int', key:true},
				{name:'UslugaComplex_Code', header:lang['kod'], width:80},
                {name:'UslugaComplex_Name', header:lang['naimenovanie'], id:'autoexpand'}
            ]			
        });
		// todo: Нужно обработать закрытые (UslugaComplex_closed=2) услуги и не давать их редактировать
        this.compUslDetailGrid = new sw.Promed.ViewFrame({
            id: 'swUslugaComplexMedServiceLinkToAnalyzerWindow_compUslDetailGrid',
            title:lang['sostav_kompleksnoy_uslugi'],
            dataUrl:'/?c=AnalyzerTest&m=getUnlinkedUslugaComplexMedServiceGrid',
            toolbar: false,
			actions:[
                { name:'action_add', disabled: true, hidden:true },
                { name:'action_edit', disabled: true, hidden:true },
                { name:'action_view', disabled: true, hidden:true },
                { name:'action_delete', disabled: true, hidden:true }
            ],
            autoLoadData:false,
            focusOnFirstLoad:false,
            region:'center',
            stringfields:[
                {name:'UslugaComplexMedService_id', type:'int', key:true},
				{name:'UslugaComplex_Code', header:lang['kod'], width:80},
                {name:'UslugaComplex_Name', header:lang['naimenovanie'], type:'string', id:'autoexpand'}
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
                {
                    xtype:'panel',
                    region:'west',
                    width:500,
                    split:true,
                    layout:'border',
                    items:[
                        this.compUslGrid
                    ]

                },
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
        sw.Promed.swUslugaComplexMedServiceLinkToAnalyzerWindow.superclass.initComponent.apply(this, arguments);
    },
    show:function () {
		sw.Promed.swUslugaComplexMedServiceLinkToAnalyzerWindow.superclass.show.apply(this, arguments);
		
        var that = this;
		
		if (!arguments[0] || !arguments[0].MedService_id) {
            alert(lang['ne_ukazanyi_obyazatelnyie_vhodnyie_parametryi_medservice_id']);
            return false;
        }
		
		sw.swMsg.alert(lang['vnimanie'], lang['na_laboratornoy_slujbe_imeyutsya_uslugi_ne_svyazannyie_ni_s_odnim_analizatorom_dlya_korrektnoy_rabotyi_s_dannyimi_uslugami_neobhodimo_svyazat_ih_s_odnim_iz_analizatorov_dannoe_soobschenie_budet_poyavlyatsya_kajdyiy_raz_pri_vhode_v_arm_laboranta_poka_vse_uslugi_ne_budut_svyazanyi_s_analizatorami']);

		this.MedService_id = arguments[0].MedService_id;
		
        this.compUslGrid.loadData({
            globalFilters:{
                MedService_id:that.MedService_id
            },
            params:{
                MedService_id:that.MedService_id
            }/*,
            noFocusOnLoad:true*/
        });
		this.compUslDetailGrid.getGrid().getStore().removeAll();
        this.center();
    }
});
