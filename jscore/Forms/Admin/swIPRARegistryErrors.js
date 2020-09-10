/**
* swIPRARegistryErrors - Таблица c неправильными данными ИПРА.
*/

sw.Promed.swIPRARegistryErrors = Ext.extend(sw.Promed.BaseForm, {
    //alwaysOnTop: true,
	id    : 'swIPRARegistryErrors', 
	objectName    : 'swIPRARegistryErrors',
	objectSrc     : '/jscore/Forms/Admin/swIPRARegistryErrors.js',
	layout: 'form',
	buttonAlign: 'center',
	title : 'Неверные данные ИПРА',
	modal : true,
        //Псих. бюро
        crazyMCElist : {
                    ufa : [11,12,13,14,16]
        },
	width : 1250,
        height : 700,
        fieldWidth:40,
	autoHeight : true,
        closable : true,
        resizable: false,
	closeAction   : 'hide',
	draggable     : true,       
        /*saveInRegisterIPRA : function(jsondata){
        //console.log('jsondata', jsondata);
        Ext.Ajax.request({
	 		url: '/?c=IPRARegister&m=saveInRegisterIPRA',
			params: {
                            jsondata : Ext.util.JSON.encode(jsondata)                               
			},
			callback: function(options, success, response) {
			    //var res = Ext.util.JSON.decode(response.responseText);

                console.log('success', success);
                //console.log('res', res);
            }
        });            
        },*/
	initComponent: function() 
	{      
		var form = this;
                form.store = new Ext.data.JsonStore({     
                                autoLoad: true,
                                root: 'data',
                                totalProperty: 'totalCount',
                                remoteSort: true,
                                fields : [                                    
                                    {name:'IPRARegistry_id', type:'string'},
                                    {name:'IPRARegistryError_Behavior', type:'string'},
                                    {name:'IPRARegistryError_Communicate', type:'string'},
                                    {name:'IPRARegistryError_Compensate', type: 'string'},
                                    {name:'IPRARegistry_Confirm', type: 'string'},
                                    //Лпу направления, после сопряжения с РМИАС
                                    //{name:'IPRARegistry_DirectionLPU_Name', type: 'string'},
                                    {name:'IPRARegistry_DirectionLPU_id', type: 'string'},
                                    
                                    {name:'IPRARegistry_EndDate', type: 'string'},
                                    {name:'IPRARegistry_FGUMCE', type: 'string'},
                                    //{name:'IPRARegistry_FGUMCEshort', type: 'string'},
                                    {name:'IPRARegistry_FGUMCEnumber', type: 'string'},
                                    {name:'IPRARegistry_RecepientType', type: 'string'},
                                    {name:'IPRARegistryError_Learn', type: 'string'},
                                    {name:'IPRARegistryError_MedRehab', type: 'string'},
                                    {name:'IPRARegistryError_MedRehab_begDate', type: 'string'},
                                    {name:'IPRARegistryError_MedRehab_endDate', type: 'string'},
                                    {name:'IPRARegistryError_Move', type: 'string'},
                                    {name:'IPRARegistry_IPRAident', type: 'string'},
                                    {name:'IPRARegistry_Number', type: 'string'},
                                    {name:'IPRARegistryError_Orientation', type: 'string'},
                                    {name:'IPRARegistryError_Orthotics', type: 'string'},
                                    {name:'IPRARegistryError_Orthotics_begDate', type: 'string'},
                                    {name:'IPRARegistryError_Orthotics_endDate', type: 'string'},
                                    {name:'IPRARegistry_PersonFIO', type: 'string'},
                                    {name:'IPRARegistry_Protocol', type: 'string'},
                                    {name:'IPRARegistry_ProtocolDate', type: 'string'},
                                    {name:'IPRARegistryError_ReconstructSurg', type: 'string'},
                                    {name:'IPRARegistryError_ReconstructSurg_begDate', type: 'string'},
                                    {name:'IPRARegistryError_ReconstructSurg_endDate', type: 'string'},
                                    {name:'IPRARegistryError_Restoration', type: 'string'},
                                    {name:'IPRARegistryError_SelfService', type: 'string'},
                                    {name:'IPRARegistryError_Work', type: 'string'},
                                    {name:'IPRARegistry_isFirst', type: 'string'},
                                    {name:'IPRARegistry_issueDate', type: 'string'},
                                    {name:'Person_id', type: 'string'},
                                    {name:'Lpu_Nick', type: 'string'},
                                    {name:'Person_BirthDay', type: 'string'},                                     
                                    {name:'IPRARegistry_DevelopDate', type: 'string'},
                                    
                                    //Лпу прикрепления
                                    {name:'Lpu_id', type: 'string'},
                                    {name:'LpuAttach_id', type: 'string'},
                                    {name:'Person_FIO', type: 'string'},
                                    {name:'Person_Snils', type: 'string'},
                                    {name:'Person_FirName', type: 'string'},
                                    {name:'Person_SecName', type: 'string'},
                                    {name:'Person_SurName', type: 'string'},
                                    {name:'IPRARegistry_insDT', type: 'string'},
                                    {name:'IPRARegistry_FileName', type: 'string'},
                                    //{name:'IPRARegistry_Document_Num', type: 'string'}
                                    {name:'IPRARegistryError_PrimaryProfession',type:'string'},
                                    {name:'IPRARegistryError_PrimaryProfessionExperience',type:'string'},
                                    {name:'IPRARegistryError_Qualification',type:'string'},
                                    {name:'IPRARegistryError_CurrentJob',type:'string'},
                                    {name:'IPRARegistryError_NotWorkYears',type:'string'},
                                    {name:'IPRARegistryError_ExistEmploymentOrientation',type:'string'},
                                    {name:'IPRARegistryError_isRegInEmplService',type:'string'},
                                    {name:'IPRARegistryError_IsDisabilityGroupPrimary',type:'string'},
                                    {name:'IPRARegistryError_IsIntramural',type:'string'},
                                    {name:'IPRARegistryError_DisabilityGroupDate',type:'string'},
                                    {name:'IPRARegistryError_DisabilityEndDate',type:'string'},
                                    {name:'IPRARegistryError_DisabilityGroup',type:'string'},
                                    {name:'IPRARegistryError_DisabilityCause',type:'string'},
                                    {name:'IPRARegistryError_RehabPotential',type:'string'},
                                    {name:'IPRARegistryError_RehabPrognoz',type:'string'},
                                    {name:'IPRARegistryError_PrognozResult_SelfService',type:'string'},
                                    {name:'IPRARegistryError_PrognozResult_Independently',type:'string'},
                                    {name:'IPRARegistryError_PrognozResult_Orientate',type:'string'},
                                    {name:'IPRARegistryError_PrognozResult_Communicate',type:'string'},
                                    {name:'IPRARegistryError_PrognozResult_BehaviorControl',type:'string'},
                                    {name:'IPRARegistryError_PrognozResult_Learning',type:'string'},
                                    {name:'IPRARegistryError_PrognozResult_Work',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_LastName',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_FirstName',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_SecondName',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_IdentifyDocType',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_IdentifyDocNum',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_IdentifyDocSeries',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_IdentifyDocDep',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_IdentifyDocDate',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_AuthorityDocType',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_AuthorityDocNum',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_AuthorityDocSeries',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_AuthorityDocDep',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_AuthorityDocDate',type:'string'},
                                    {name:'IPRARegistryError_RepPerson_SNILS',type:'string'},
                                    {name:'IPRARegistry_Version',type:'string'},
                                    {name:'IPRARegistryError_DisabilityCauseOther',type:'string'}
                                
                                ]});
                form.pagingBar = new Ext.PagingToolbar({
                                store: this.store,
                                displayInfo: true,
				pageSize:50,
                                displayMsg: 'Записи {0} - {1} из {2}', // из {2} - серверная сторона странно считает - решить,
                                emptyMsg: "Записей нет",

                                items: [ ]

                            });
                            
                form.printObjectListFull = function()
                {
                        var viewframe = Ext.getCmp('IPRA_ErrorsGrid');
                        var store = viewframe.getStore();

                        if (store.getCount() < store.getTotalCount()) {
                                var loadMask = new Ext.LoadMask(viewframe.getEl(), {msg:'Получение данных для печати'});
                                loadMask.show();

                                var tmpGrid = viewframe.cloneConfig();

                                tmpGrid.store = new Ext.data.JsonStore({
                                    autoLoad: false,
                                    root:           store.root,
                                    totalProperty:  store.totalProperty,
                                    remoteSort:     store.remoteSort,
                                    fields :        store.fields.items
                                });

                                if ( typeof viewframe.sortInfo == 'object' && !Ext.isEmpty(viewframe.sortInfo.field) ) {
                                        tmpGrid.store.sortInfo = viewframe.sortInfo;
                                }

                                tmpGrid.getStore().proxy.conn.url = store.proxy.conn.url;
                                tmpGrid.getStore().baseParams.start = 0;
                                tmpGrid.getStore().baseParams.limit = 100000;
                                tmpGrid.getStore().load({callback: function(){
                                        loadMask.hide();

                                        if ( viewframe.showArchive == true ) {
                                                tmpGrid.getStore().baseParams.useArchive = 1;

                                                viewframe.getLoadMask('Загрузка архивных записей...').show();

                                                Ext.Ajax.request({
                                                        url: viewframe.dataUrl,
                                                        params: tmpGrid.getStore().baseParams,
                                                        callback: function (opt, success, response) {
                                                                viewframe.getLoadMask().hide();

                                                                if ( success ) {
                                                                        var response_obj = Ext.util.JSON.decode(response.responseText);
                                                                        tmpGrid.getStore().loadData(response_obj, true);
                                                                }

                                                                Ext.ux.GridPrinter.print(tmpGrid, {addNumberColumn: viewframe.printWithNumberColumn});
                                                        }
                                                });
                                        }
                                        else {
                                                Ext.ux.GridPrinter.print(tmpGrid, {addNumberColumn: viewframe.printWithNumberColumn});
                                        }
                                }});
                        } else {
                                Ext.ux.GridPrinter.print(viewframe, {addNumberColumn: viewframe.printWithNumberColumn});
                        }
                }       
                
		Ext.apply(this, 
		{   
			autoHeight: true,                        
                        buttonAlign: 'right', 
			buttons : [
                            {
                                hidden: false,
                                handler: function() 
                                {
                                    form.printObjectListFull();
                                },
                                iconCls: 'print16',
                                text: 'Печать всего списка'
                            },                                
                            {
                                hidden: false,
                                handler: function() 
                                {
                                    form.close();
                                },
                                iconCls: 'close16',
                                text: 'Закрыть'
                            }                    
			],
			items : [
                        new Ext.grid.GridPanel({
                            id: 'IPRA_ErrorsGrid',
                            count : 0,
                            title : '',
                            printWithNumberColumn: false,
                            disabled : false,
                            border: false,
                            height: 700,
                            bbar : form.pagingBar,
                            columns: [
                                //Псих
                                {dataIndex:'CRZ', header: 'crazy', width: 40, hidden:true, renderer: function(v,p,r){
                                    //console.log(v,p,r)
                                    return r.get('IPRARegistry_FGUMCEnumber').inlist(Ext.getCmp('swIPRARegistryErrors').crazyMCElist.ufa) 
                                           ? '<span class="x-grid3-check-col-non-border-on">&nbsp;&nbsp;&nbsp;</span>' 
                                           : '<span class="x-grid3-check-col-on-non-border-red">&nbsp;&nbsp;&nbsp;</span>';
                                       
                                    }
                                }, 
                                //Пациент прошёл идентификацию и определён по МО
                                {
                                    dataIndex: 'ipra',
                                    header: 'ИПРА',
                                    width: 40,
                                    renderer: function(v, p, r) {

                                        if (r.get('Lpu_id') > 0 &&
                                            r.get('Person_id') > 0 &&
											!Ext.isEmpty(r.get('IPRARegistry_isFirst')) &&
											!Ext.isEmpty(r.get('IPRARegistry_issueDate')) &&
											!Ext.isEmpty(r.get('IPRARegistry_FGUMCEnumber')) &&
											!Ext.isEmpty(r.get('IPRARegistry_Number')) &&
											!Ext.isEmpty(r.get('IPRARegistry_Protocol')) &&
											!Ext.isEmpty(r.get('IPRARegistry_ProtocolDate')) &&
											!Ext.isEmpty(r.get('IPRARegistry_DevelopDate'))
                                        ) {
                                            //return '<span class="x-grid3-check-col-non-border-on">&nbsp;&nbsp;&nbsp;</span>'
                                            return '<span style="color:green;; font-style:italic; font-size:14px">V</span>';
                                        } else {
                                            //return '<span class="x-grid3-check-col-on-non-border-red">&nbsp;&nbsp;&nbsp;</span>';
                                            return '<span style="color:red; font-style:italic; font-size:14px">X</span>';
                                        }
                                    }
                                },                               
                                //Идентификация пациента
                                {
                                    dataIndex: 'pacient',
                                    header: 'ИД',
                                    width: 40,
                                    renderer: function(v, p, r) {
                                        //console.log(v,p,r)
                                        return r.get('Person_id') > 0 ?
                                            //'<span class="x-grid3-check-col-non-border-on">&nbsp;&nbsp;&nbsp;</span>' :
                                            //'<span class="x-grid3-check-col-on-non-border-red">&nbsp;&nbsp;&nbsp;</span>';
                                            '<span style="color:green; font-style:italic; font-size:14px">V</span>' :
                                            '<span style="color:red; font-style:italic; font-size:14px">X</span>';


                                    }
                                },
                                //МО определения инвалида
                                {
                                    dataIndex: 'lpu',
                                    header: 'МО',
                                    width: 40,
                                    renderer: function(v, p, r) {


                                        return r.get('Lpu_id') > 0 ?
                                            //'<span class="x-grid3-check-col-non-border-on">&nbsp;&nbsp;&nbsp;</span>' :
                                            //'<span class="x-grid3-check-col-on-non-border-red">&nbsp;&nbsp;&nbsp;</span>';
                                            '<span style="color:green; font-style:italic; font-size:14px">V</span>' :
                                            '<span style="color:red; font-style:italic; font-size:14px">X</span>';                                    

                                    }
                                },
                                {
                                    dataIndex: 'IPRADataAllFields',
                                    header: 'Все данные',
                                    width: 40,
                                    renderer: function(v, p, r) {
                                        var IPRAdataIsValid = r.get('IPRARegistry_isFirst') && r.get('IPRARegistry_issueDate') && r.get('IPRARegistry_FGUMCEnumber')
                                                           && r.get('IPRARegistry_Number')&& r.get('IPRARegistry_Protocol')&& r.get('IPRARegistry_ProtocolDate')
                                                           && r.get('IPRARegistry_DevelopDate');
                                        return IPRAdataIsValid ?
                                            '<span style="color:green; font-style:italic; font-size:14px">V</span>' :
                                            '<span style="color:red; font-style:italic; font-size:14px">X</span>';
                                    }
                                },
                                                                                                              
                                {dataIndex:'IPRARegistry_DirectionLPU_id', header: 'IPRARegistry_DirectionLPU_id', width: 190, hidden: true},
                                {dataIndex:'IPRARegistry_id', header: 'IPRARegistry_id', width: 190, hidden: true},
                                //{dataIndex:'IPRARegistry_DirectionLPU_Name', header: 'МО Справочника', width: 190},
                                {dataIndex:'IPRARegistry_Number', header: '№ ИПРА', width: 90, sortable: true}, 
                                {dataIndex:'IPRARegistry_issueDate', header: 'Дата выдачи ИПРА', sortable: true},                                
                                {dataIndex:'Lpu_Nick', header: 'МО определения', width: 190},
                                {dataIndex:'Person_Snils', header: 'СНИЛС', width: 90},
                                {dataIndex:'Person_FIO', header: 'Фамилия Имя Отчество', width: 200, sortable: true}, 
                                {dataIndex:'Person_FirName', header: 'Имя', width: 200, hidden: true},
                                {dataIndex:'Person_SecName', header: 'Отчество', width: 200, hidden: true},
                                {dataIndex:'Person_SurName', header: 'Фамилия', width: 200, hidden: true},
                                {dataIndex:'Person_BirthDay', header: 'Дата рождения', width: 90, sortable: true,
                                renderer: function(v, p, r) {
                                    return (r.get('Person_BirthDay') != '' && r.get('Person_BirthDay') != null && r.get('Person_BirthDay').length <= 10)?
                                    Ext.util.Format.date(r.get('Person_BirthDay'),'d.m.Y'):
                                '';
                                }},
                                {dataIndex:'IPRARegistry_Protocol', header: '№ Протокола', width: 90, sortable: true},
                                {dataIndex:'IPRARegistry_ProtocolDate', header: 'Дата протокола', width: 90, sortable: true},
                                {dataIndex:'IPRARegistry_FGUMCE', header: 'Бюро МСЭ (полное название)'},
                                {dataIndex:'IPRARegistry_FGUMCEnumber', header: 'Номер бюро МСЭ', sortable: true},
                                {dataIndex:'IPRARegistryError_Behavior', header:'IPRARegistry_Behavior', hidden:true},
                                {dataIndex:'IPRARegistryError_Communicate', header:'IPRARegistry_Communicate', hidden:true},
                                {dataIndex:'IPRARegistryError_Compensate', header: 'IPRARegistry_Compensate', hidden:true},
                                {dataIndex:'IPRARegistry_Confirm', header: 'IPRARegistry_Confirm', hidden:true, renderer: function(v,p,r){
                                    return (r.get('IPRARegistry_Confirm') == 2) ? 'подтвержден' : 'не подтвержден';
                                }},
                                {dataIndex:'IPRARegistry_RequiredHelp', header: 'IPRARegistry_RequiredHelp', hidden:true},
                                {dataIndex:'IPRARegistry_EndDate', header: 'Срок ИПРА', hidden:true},
                                {dataIndex:'LpuAttach_id', header: 'Lpu_id прикрепления', hidden:true},
                                //{dataIndex:'Lpu_Name', header: 'Lpu прикрепления'},
                                {dataIndex:'IPRARegistryError_Learn', header: 'IPRARegistry_Learn', hidden:true},
                                {dataIndex:'IPRARegistryError_MedRehab', header: 'IPRARegistry_MedRehab', hidden:true},
                                {dataIndex:'IPRARegistryError_MedRehab_begDate', header: 'IPRARegistry_MedRehab_begDate', hidden:true},
                                {dataIndex:'IPRARegistryError_MedRehab_endDate', header: 'IPRARegistry_MedRehab_endDate', hidden:true},
                                {dataIndex:'IPRARegistryError_Move', header: 'IPRARegistry_Move', hidden:true},
                                {dataIndex:'IPRARegistryError_Orientation', header: 'IPRARegistry_Orientation', hidden:true},
                                {dataIndex:'IPRARegistryError_Orthotics', header: 'IPRARegistry_Orthotics', hidden:true},
                                {dataIndex:'IPRARegistryError_Orthotics_begDate', header: 'IPRARegistry_Orthotics_begDate', hidden:true},
                                {dataIndex:'IPRARegistryError_Orthotics_endDate', header: 'IPRARegistry_Orthotics_endDate', hidden:true},
                                {dataIndex:'IPRARegistryError_ReconstructSurg', header: 'IPRARegistry_ReconstructSurg', hidden:true},
                                {dataIndex:'IPRARegistryError_ReconstructSurg_begDate', header: 'IPRARegistry_ReconstructSurg_begDate', hidden:true},
                                {dataIndex:'IPRARegistryError_ReconstructSurg_endDate', header: 'IPRARegistry_ReconstructSurg_endDate', hidden:true},
                                {dataIndex:'IPRARegistryError_Restoration', header: 'IPRARegistry_Restoration', hidden:true},
                                {dataIndex:'IPRARegistryError_SelfService', header: 'IPRARegistry_SelfService', hidden:true},
                                {dataIndex:'IPRARegistryError_Work', header: 'IPRARegistry_Work', hidden:true},
                                {dataIndex:'IPRARegistry_isFirst', header: 'IPRARegistry_isFirst', hidden:true, renderer: function(v,p,r){
                                    return (r.get('IPRARegistry_isFirst') == 2) ? 'впервые' : 'повторно';
                                }},
                                {dataIndex:'IPRARegistry_DevelopDate', header: 'Дата разработки ИПРА', hidden:true}, 
                                {dataIndex:'Person_id', header: 'Person_id', hidden:true},  
                                {dataIndex:'Person_FirName', header: 'Person_FirName', hidden: true}, 
                                {dataIndex:'Person_SecName', header: 'Person_SecName', hidden: true},
                                {dataIndex:'Person_SurName', header: 'Person_surName', hidden: true},
                                {dataIndex:'IPRARegistryError_PrimaryProfession', header: 'IPRARegistryError_PrimaryProfession', hidden: true},
                                {dataIndex:'IPRARegistryError_PrimaryProfessionExperience', header: 'IPRARegistryError_PrimaryProfessionExperience', hidden: true},
                                {dataIndex:'IPRARegistryError_Qualification', header: 'IPRARegistryError_Qualification', hidden: true},
                                {dataIndex:'IPRARegistryError_CurrentJob', header: 'IPRARegistryError_CurrentJob', hidden: true},
                                {dataIndex:'IPRARegistryError_NotWorkYears', header: 'IPRARegistryError_NotWorkYears', hidden: true},
                                {dataIndex:'IPRARegistryError_ExistEmploymentOrientation', header: 'IPRARegistryError_ExistEmploymentOrientation', hidden: true},
                                {dataIndex:'IPRARegistryError_isRegInEmplService', header: 'IPRARegistryError_isRegInEmplService', hidden: true},
                                {dataIndex:'IPRARegistryError_IsDisabilityGroupPrimary', header: 'IPRARegistryError_IsDisabilityGroupPrimary', hidden: true},
                                {dataIndex:'IPRARegistryError_IsIntramural', header: 'IPRARegistryError_IsIntramural', hidden: true},
                                {dataIndex:'IPRARegistryError_DisabilityGroupDate', header: 'IPRARegistryError_DisabilityGroupDate', hidden: true},
                                {dataIndex:'IPRARegistryError_DisabilityEndDate', header: 'IPRARegistryError_DisabilityEndDate', hidden: true},
                                {dataIndex:'IPRARegistryError_DisabilityGroup', header: 'IPRARegistryError_DisabilityGroup', hidden: true},
                                {dataIndex:'IPRARegistryError_DisabilityCause', header: 'IPRARegistryError_DisabilityCause', hidden: true},
                                {dataIndex:'IPRARegistryError_RehabPotential', header: 'IPRARegistryError_RehabPotential', hidden: true},
                                {dataIndex:'IPRARegistryError_RehabPrognoz', header: 'IPRARegistryError_RehabPrognoz', hidden: true},
                                {dataIndex:'IPRARegistryError_PrognozResult_SelfService', header: 'IPRARegistryError_PrognozResult_SelfService', hidden: true},
                                {dataIndex:'IPRARegistryError_PrognozResult_Independently', header: 'IPRARegistryError_PrognozResult_Independently', hidden: true},
                                {dataIndex:'IPRARegistryError_PrognozResult_Orientate', header: 'IPRARegistryError_PrognozResult_Orientate', hidden: true},
                                {dataIndex:'IPRARegistryError_PrognozResult_Communicate', header: 'IPRARegistryError_PrognozResult_Communicate', hidden: true},
                                {dataIndex:'IPRARegistryError_PrognozResult_BehaviorControl', header: 'IPRARegistryError_PrognozResult_BehaviorControl', hidden: true},
                                {dataIndex:'IPRARegistryError_PrognozResult_Learning', header: 'IPRARegistryError_PrognozResult_Learning', hidden: true},
                                {dataIndex:'IPRARegistryError_PrognozResult_Work', header: 'IPRARegistryError_PrognozResult_Work', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_LastName', header: 'IPRARegistryError_RepPerson_LastName', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_FirstName', header: 'IPRARegistryError_RepPerson_FirstName', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_SecondName', header: 'IPRARegistryError_RepPerson_SecondName', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_IdentifyDocType', header: 'IPRARegistryError_RepPerson_IdentifyDocType', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_IdentifyDocNum', header: 'IPRARegistryError_RepPerson_IdentifyDocNum', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_IdentifyDocSeries', header: 'IPRARegistryError_RepPerson_IdentifyDocSeries', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_IdentifyDocDep', header: 'IPRARegistryError_RepPerson_IdentifyDocDep', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_IdentifyDocDate', header: 'IPRARegistryError_RepPerson_IdentifyDocDate', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_AuthorityDocType', header: 'IPRARegistryError_RepPerson_AuthorityDocType', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_AuthorityDocNum', header: 'IPRARegistryError_RepPerson_AuthorityDocNum', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_AuthorityDocSeries', header: 'IPRARegistryError_RepPerson_AuthorityDocSeries', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_AuthorityDocDep', header: 'IPRARegistryError_RepPerson_AuthorityDocDep', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_AuthorityDocDate', header: 'IPRARegistryError_RepPerson_AuthorityDocDate', hidden: true},
                                {dataIndex:'IPRARegistryError_RepPerson_SNILS', header: 'IPRARegistryError_RepPerson_SNILS', hidden: true},
                                {dataIndex:'IPRARegistry_Version', header: 'IPRARegistry_Version', hidden: true},
                                {dataIndex:'IPRARegistryError_DisabilityCauseOther', header: 'IPRARegistryError_DisabilityCauseOther', hidden: true},
                                {dataIndex:'IPRARegistry_insDT', header: 'Дата/время импорта', width: 120},
                                {dataIndex:'IPRARegistry_FileName', header: 'Файл', width: 120},
                                {dataIndex:'IPRARegistry_Errors', renderer: function(v, p, r) {
									var errors = '';
									// 1.	При отсутствии идентификации человека выдается ошибка «Пациент не идентифицирован»
									if (!(r.get('Person_id') > 0)) {
										if (errors.length > 0) {
											errors += '<br>';
										}
										errors += 'Пациент не идентифицирован';
									}
									// 2.	При отсутствии МО выдается ошибка «МО, направившая на МСЭ не определена»
									if (!(r.get('Lpu_id') > 0)) {
										if (errors.length > 0) {
											errors += '<br>';
										}
										errors += 'МО, направившая на МСЭ не определена';
									}

									// 3.	При наличии пустого обязательного поля выдается ошибка «Не заполнены следующие обязательные поля:». Поля перечисляются через запятую.
									var emptyFields = '';
									if (Ext.isEmpty(r.get('IPRARegistry_isFirst'))) {
										if (emptyFields.length > 0) {
											emptyFields += ',';
										}
										emptyFields += 'Версия ИПРА инвалида';
									}
									if (Ext.isEmpty(r.get('IPRARegistry_issueDate'))) {
										if (emptyFields.length > 0) {
											emptyFields += ',';
										}
										emptyFields += 'Дата выдачи ИПРА инвалида';
									}
									if (Ext.isEmpty(r.get('IPRARegistry_FGUMCEnumber'))) {
										if (emptyFields.length > 0) {
											emptyFields += ',';
										}
										emptyFields += 'Наименование ФГУ МСЭ';
									}
									if (Ext.isEmpty(r.get('IPRARegistry_Number'))) {
										if (emptyFields.length > 0) {
											emptyFields += ',';
										}
										emptyFields += '№ ИПРА';
									}
									if (Ext.isEmpty(r.get('IPRARegistry_Protocol'))) {
										if (emptyFields.length > 0) {
											emptyFields += ',';
										}
										emptyFields += '№ протокола МСЭ';
									}
									if (Ext.isEmpty(r.get('IPRARegistry_ProtocolDate'))) {
										if (emptyFields.length > 0) {
											emptyFields += ',';
										}
										emptyFields += 'Дата протокола проведения МСЭ';
									}
									if (Ext.isEmpty(r.get('IPRARegistry_DevelopDate'))) {
										if (emptyFields.length > 0) {
											emptyFields += ',';
										}
										emptyFields += 'Дата разработки ИПРА';
									}
									if (emptyFields.length > 0) {
										if (errors.length > 0) {
											errors += '<br>';
										}
										errors += 'Не заполнены следующие обязательные поля: ' + emptyFields;
									}

									return errors;
								}, header: 'Ошибки', width: 500}
                                //{dataIndex:'IPRARegistry_Document_Num', header: 'IPRARegistry_Document_Num', hidden: true}
                                              
                            ],                                 
                            store:form.store,
                            listeners : {
                                rowdblclick : function( grid, rowIndex, e ){
                                    var row = this.getStore().getAt(rowIndex);
                                    var params = {
                                        row: row,
                                        editErrors: true
                                    }
                                    getWnd('swIPRARegistryConfirmWindow').show(params);
                                }
                            }
                            
                        })                              
			]
		});
		sw.Promed.swIPRARegistryErrors.superclass.initComponent.apply(this, arguments);
	},
	close : function(){	
            this.hide();
            this.destroy();
            window[this.objectName] = null;
            delete sw.Promed[this.objectName];
    },    
	show: function(params) 
	{   

            
            var gridErrors = Ext.getCmp('IPRA_ErrorsGrid');
            var storeErors = gridErrors.getStore();                  
            
            storeErors.baseParams = {limit: 50};
            storeErors.proxy.conn.url = '/?c=IPRARegister&m=getIPRARegistryErrors';
            //storeErors.load();


            //console.log(params);
            /*this.IPRAdata_decode = params.IPRAdata_decode;
            Ext.getCmp('IPRA_ErrorsGrid').getStore().removeAll();
            Ext.getCmp('IPRA_ErrorsGrid').getStore().loadData(Ext.getCmp('swIPRARegistryErrors').IPRAdata_decode);*/
            
            sw.Promed.swIPRARegistryErrors.superclass.show.apply(this, arguments);
	}

});