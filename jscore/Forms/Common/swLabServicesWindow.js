/**
 * swLabServicesWindow - окно редактирования услуг лаборатории
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

sw.Promed.swLabServicesWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh:true,
    objectName:'swLabServicesWindow',
    objectSrc:'/jscore/Forms/Common/swLabServicesWindow.js',
    buttonAlign:'left',
    closeAction:'hide',
    layout:'border',
    listeners:{
        'hide':function () {
            this.onHide();
        }
    },
    title:lang['sample_and_container_customization'],
    id:'swLabServicesWindow',
    maximized:true,
    initComponent:function () {
        var that = this;
        var refreshCompUslDetailGrid = function ()  {
            var selectedUsluga = that.compUslGrid.getGrid().getSelectionModel().getSelected();
            that.compUslDetailGrid.loadData({
                globalFilters:{
                    MedService_id:that.MedService_id,
                    UslugaComplex_pid:selectedUsluga.get('UslugaComplex_id'),
                    UslugaComplexMedService_id:selectedUsluga.get('UslugaComplexMedService_id'),
					armMode:'Lis'
                }, /*
                 params: {
                 MedService_id: that.MedService_id,
                 UslugaComplex_pid: selectedUsluga.get('UslugaComplex_id'),
                 UslugaComplexMedService_id: selectedUsluga.get('UslugaComplexMedService_id')
                 }, */
                noFocusOnLoad:true
            });

        }
        this.compUslGrid = new sw.Promed.ViewFrame({
            title:lang['issledovaniya'],
			id: 'swUslugaComplexMedServiceGrid',
            toolbar:true,
            dataUrl:'/?c=MedService&m=loadUslugaComplexMedServiceGrid',
            autoLoadData:false,
            refreshCompUslDetailGrid: refreshCompUslDetailGrid,
            /*focusOnFirstLoad:false,*/
            region:'center',
            contextmenu: true,
            actions: [
                {
                    name:'action_edit',
                    handler:function () {
                        that.showProbeDialog('compUslGrid');
                    },
                    text: lang['edit_sample'],
                    hidden:true
                },
                {
                    name:'action_view',
                    text: lang['odna_proba'],
                    handler:function () {
                    },
                    hidden:true
                },
                {
                    name:'action_add',
                    handler:function () {
                    },
                    hidden:true
                },
                {
                    name:'action_delete',
                    handler:function () {
                    },
                    hidden:true
                },
                {
                    name:'action_print',
                    handler: function (){},
                    hidden:true
                },
                {
                    name:'action_refresh',
                    handler: function (){},
                    hidden:true
                },
            ],
            stringfields:[
                { name: 'rownumberer', type: 'rownumberer', header: '№', width: 30 },
                {name:'UslugaComplex_id', type:'int', header:'ID', key:true},
				{name:'UslugaComplex_Code', header:lang['kod'], width:80},
				{name:'UslugaComplexMedService_id', type:'int', hidden:true},
                {name:'UslugaComplex_Name', header:lang['naimenovanie'], id:'autoexpand'},
                {name:'RefSample_id',type:'int', hidden:true},
                {name:'ContainerType_id', type:'int', hidden:true},
                {name:'RefMaterial_id', type:'int', hidden:true},
				{name:'UslugaComplex_closed', type:'int', hidden:true},
                // {name:'RefSample_Name', header:lang['proba'], type:'string'},
                // {name:'RefMaterial_Name', header:lang['biomaterial'], type:'string'},
                // {name:'ContainerType_Name', header:lang['container_type'], type:'string'},
                // {name:'UslugaComplexMedService_IsSeparateSample', header: lang['isSeparateSample'], type: 'checkbox', width: 80},
            ],
            onRowSelect: function (){
                refreshCompUslDetailGrid();
            }
			
        });
		// todo: Нужно обработать закрытые (UslugaComplex_closed=2) услуги и не давать их редактировать
        this.compUslDetailGrid = new sw.Promed.ViewFrame({
            id: 'swLabServicesWindow_compUslDetailGrid',
            title:lang['composition_research'],
            dataUrl:'/?c=MedService&m=loadUslugaComplexMedServiceGridChild',
            toolbar:true,
            useEmptyRecord: false,
            selectionModel: 'multiselect',
            contextmenu: true,
            actions:[
                {
                    name:'action_add',
                    handler:function () {},
                    hidden:true
                },
                {
                    name:'action_delete',
                    handler:function () {},
                    hidden:true
                },
                {
                    name:'action_view',
                    handler:function () {},
                    hidden:true
                },
                {
                    name:'action_edit',
                    handler:function () {
                        that.showProbeDialog('compUslDetailGrid');
                    },
                    text: lang['redaktirovat']
                },
                // {
                //     name:'action_save',
                //     url: '/?c=MedService&m=saveUslugaComplexMedServiceIsSeparateSample',
                //     hidden:true
                // },
                {
                    name:'action_refresh',
                    handler:function () {
                        refreshCompUslDetailGrid();
                    },
                    hidden:false
                },
                {
                    name:'action_print',
                    hidden:true
                }
            ],
            autoLoadData:false,
            focusOnFirstLoad:false,
            region:'center',
			onRowSelectionChange: function(sm) {
				if (sm.getCount() >= 1) {
					this.getAction('action_edit').setDisabled(false);
				} else {
					this.getAction('action_edit').setDisabled(true);
				}
				
				this.getAction('action_view').setDisabled(false);
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
            saveAllParams: true,
            saveAtOnce: true,
            stringfields:[
                {name:'UslugaComplex_id', type:'int', header:'ID', hidden: true},
                {name:'UslugaComplexMedService_id', type:'int', hidden: true, key:true},
				{name:'UslugaComplex_Code', header:lang['kod'], width:80},
                {name:'UslugaComplex_Name', header:lang['naimenovanie'], type:'string', id:'autoexpand'},
                {name:'RefSample_Name', header:lang['proba'], type:'string'},
                {name:'RefMaterial_Name', header:lang['biomaterial'], type:'string'},
                {name:'ContainerType_id', type:'int', hidden:true},
                {name:'ContainerType_Name', header:lang['container_type'], type:'string'},
                {name:'RefMaterial_id', type:'int', hidden:true},
                {name:'UslugaComplexMedService_IsSeparateSample', header: lang['isSeparateSample'], type: 'checkbox', width: 80},
                // {
                //     name:'UslugaComplexMedService_IsSeparateSample',
                //     header: lang['isSeparateSample'],
                //     sortable: false,
                //     type: 'checkcolumnedit',
                //     isparams: true,
                // },
                //{name:'UslugaComplex_Name', header:'Наименование', type:'string'}
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
                    width:'20%',
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
        sw.Promed.swLabServicesWindow.superclass.initComponent.apply(this, arguments);
    },
    showProbeDialog: function (grid){
        var that = this;
        var selected_grid = that[grid];
		var sel = new Array();
        var row = selected_grid.getGrid().getSelectionModel().getSelected();
        selected_grid.getGrid().getSelectionModel().getSelections().forEach(function (el) {
            sel.push(el.data.UslugaComplexMedService_id);
        });

        if(row.data.RefMaterial_id == null){
            sw.swMsg.alert(langs('Ошибка'), langs('Не выбран тест'));
            return false;
        }

        var params = {
            Usluga_ids: Ext.util.JSON.encode(sel),
            MedService_id: that.MedService_id
        };

        // если выделена одна строка - подставляем значения в форму
        if( selected_grid.getGrid().getSelectionModel().getSelections().length == 1 ){
            params.RefMaterial_id = row.data.RefMaterial_id;
            params.RefSample_Name = row.data.RefSample_Name;
            params.ContainerType_id = row.data.ContainerType_id;
            params.UslugaComplexMedService_IsSeparateSample = row.data.UslugaComplexMedService_IsSeparateSample;
            params.UslugaComplex_Code = row.data.UslugaComplex_Code;
            params.UslugaComplex_Name = row.data.UslugaComplex_Name;
        }

		getWnd('swLisSelectBioMaterialWindow').show({
			formParams: params,
			callback: function(RefMaterial_id, RefSample_Name) {
				selected_grid.getGrid().getStore().reload();
				selected_grid.getGrid().getStore().on('load', function () {
					if(selected_grid.getGrid().getSelectionModel().getSelections().length > 1){
						that['compUslDetailGrid'].getAction('action_edit').setDisabled(false);
					}
					}
				)
			}
		});
    },
    show:function () {
        var that = this;
		
		if (!arguments[0] || !arguments[0].MedService_id) {
            alert(lang['ne_ukazanyi_obyazatelnyie_vhodnyie_parametryi_medservice_id']);
            return false;
        }

		this.MedService_id = arguments[0].MedService_id;
        
        sw.Promed.swLabServicesWindow.superclass.show.apply(this, arguments);
        this.compUslGrid.loadData({
            globalFilters:{
                MedService_id:that.MedService_id,
				armMode:'Lis'
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
