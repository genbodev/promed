/**
 * swONMKRegistryEditWindow - форма просмотра случая регистра ОНМК
 */

sw.Promed.swONMKRegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    autoScroll: true,
    title: 'Регистр ОНМК: Добавление',
    layout: 'form',
    id: 'swONMKRegistryEditWindow',
    modal: true,
    onHide: Ext.emptyFn,
    onSelect:  Ext.emptyFn,
    shim: false,
    resizable: false,
    maximizable: false,
    maximized: true,
    region: 'center',
	
    m_width_big: 500,
    m_width_min: 150,
    m_width_date: 95,
    
    user_id:0,
    lpu_id:0,
    act:'q',
    treeLoaded:true,

    listeners:{
        hide:function () {
            this.onHide();
        }
    },	

    show: function(params) {

	
		this.GRID_ONMKRegistry_id=params.ONMKRegistry_id;
		
        sw.Promed.swONMKRegistryEditWindow.superclass.show.apply(this, arguments);
        
        this.PersonInfoPanel.personId = params.Person_id;
        this.PersonInfoPanel.serverId = params.Server_id;
        this.PersonInfoPanel.load({
            callback: function() {
                this.PersonInfoPanel.setPersonTitle();
            }.createDelegate(this),
            Person_id: this.PersonInfoPanel.personId,
            Server_id: this.PersonInfoPanel.serverId
        });                
        
        this.loadSluchList();
		
		
		
    },
    
	//установка статуса что случай открывался для просмотра
	setNotIsNewStatus: function(ONMKRegistry_id){

		Ext.Ajax.request({
            url: '/?c=ONMKRegister&m=saveONMKStatus', 
            params: { 
                ONMKRegistry_id:ONMKRegistry_id
            },
            success: function(result){
                var resp_obj = Ext.util.JSON.decode(result.responseText);
			}
		});
	},
			
    //проверка соответствия лпу текущего пользователя, лпу случая эко
    isThisUser: function(){        
        if (this.lpu_id == getGlobalOptions().lpu[0]){
            return true;
        }
        else return false;
    },    
    
    loadOslList: function(){		
		
        var form = this;
        //var data_form = form.svedPanel.getForm(); 

        //var loadMask = new Ext.LoadMask(Ext.getCmp('uslugaGrid').getGrid().getEl(), {msg: "Загрузка..."});
        //loadMask.show();        
		var _form = Ext.getCmp("swONMKRegistryEditWindow");						
        Ext.Ajax.request({
            url: '/?c=ONMKRegister&m=loadEvnUslugaGrid', 
            params: { 
                EvnPS_id:_form.EvnPS_id
            },
            success: function(result){
                var resp_obj = Ext.util.JSON.decode(result.responseText); 
                
				Ext.getCmp('uslugaConsultGrid').getGrid().store.removeAll();
				Ext.getCmp('uslugaOperGrid').getGrid().store.removeAll();
				Ext.getCmp('uslugaCommonGrid').getGrid().store.removeAll();				
				
                var ind = 0;
                var indEnd = resp_obj.length;
                while (ind<indEnd) {                    
                    
					
					if (resp_obj[ind].consultAttr == '1'){
						var nRecord = Ext.getCmp('uslugaConsultGrid').getGrid().store.getCount();
						Ext.getCmp('uslugaConsultGrid').getGrid().getStore().insert(nRecord, [
							new Ext.data.Record({
								EvnUsluga_id: resp_obj[ind].EvnUsluga_id,
								EvnUsluga_pid: resp_obj[ind].EvnUsluga_pid,
								Usluga_Code: resp_obj[ind].Usluga_Code,
								Usluga_Name: resp_obj[ind].Usluga_Name,                                     
								EvnUsluga_setDT: resp_obj[ind].EvnUsluga_setDT,
								EvnUsluga_insDT: resp_obj[ind].EvnUsluga_insDT 
							})
						]);
					}
					else if (resp_obj[ind].operAttr == '1'){
						var nRecord = Ext.getCmp('uslugaOperGrid').getGrid().store.getCount();
						Ext.getCmp('uslugaOperGrid').getGrid().getStore().insert(nRecord, [
							new Ext.data.Record({
								EvnUsluga_id: resp_obj[ind].EvnUsluga_id,
								EvnUsluga_pid: resp_obj[ind].EvnUsluga_pid,
								Usluga_Code: resp_obj[ind].Usluga_Code,
								Usluga_Name: resp_obj[ind].Usluga_Name,
								EvnUsluga_setDT: resp_obj[ind].EvnUsluga_setDT,
								EvnUsluga_insDT: resp_obj[ind].EvnUsluga_insDT 
							})
						]);
					}
					else if (resp_obj[ind].commonAttr == '1'){
						var nRecord = Ext.getCmp('uslugaCommonGrid').getGrid().store.getCount();
						Ext.getCmp('uslugaCommonGrid').getGrid().getStore().insert(nRecord, [
							new Ext.data.Record({
								EvnUsluga_id: resp_obj[ind].EvnUsluga_id,
								EvnUsluga_pid: resp_obj[ind].EvnUsluga_pid,
								Usluga_Code: resp_obj[ind].Usluga_Code,
								Usluga_Name: resp_obj[ind].Usluga_Name,                                     
								EvnUsluga_setDT: resp_obj[ind].EvnUsluga_setDT,
								EvnUsluga_insDT: resp_obj[ind].EvnUsluga_insDT 
							})
						]);
					}

                    ind++;
                }
                
            }
        });        
		
    },

   //загрузка список случаев ОНММК
    loadSluchList: function(){
        var form = this;
        var parametrs = {};        
		parametrs.Person_id = form.PersonInfoPanel.personId;        
		form.sluchFrameGrid.loadData({globalFilters: parametrs, callback:function () { 					
				var form = Ext.getCmp('swONMKRegistryEditWindow');
				if (form.GRID_ONMKRegistry_id != 0){
					var ind = form.sluchFrameGrid.getGrid().getStore().find('ONMKRegistry_id',form.GRID_ONMKRegistry_id)
					var gridmodel = form.sluchFrameGrid.getGrid().getSelectionModel();

					for (var i = 0; i < Ext.getCmp('swONMKRegistryEditWindow').sluchFrameGrid.getGrid().getStore().getCount(); i++) {
						gridmodel.deselectRow(i);
					}						
					Ext.getCmp('swONMKRegistryEditWindow').GRID_ONMKRegistry_id=0;
					form.sluchFrameGrid.getGrid().getSelectionModel().selectRow(ind);						
				}			
			}});
    },

    //Нажать на случай в гриде случаев
    onClickSl: function(){
		
        var form = this;
        var data_form = form.svedPanel.getForm();	
        if (form.sluchFrameGrid.getGrid().getStore().data.items[0].data.ONMKRegistry_id)
        {
            var record = form.sluchFrameGrid.getGrid().getSelectionModel().getSelections()[0].data; 
            if (record){
                var ONMKRegistry_id = record.ONMKRegistry_id;
				
				var passONMKRegistry_id = Ext.getCmp('swONMKRegistryEditWindow').GRID_ONMKRegistry_id;
				if (passONMKRegistry_id != 0){
					ONMKRegistry_id = passONMKRegistry_id;					
				}								

                Ext.Ajax.request({ 
                    url: '/?c=ONMKRegister&m=loadSluchData', 
                    params: { 
						ONMKRegistry_id: ONMKRegistry_id
                    },

                    success: function(result){
                        var resp_obj = Ext.util.JSON.decode(result.responseText);												
						
						var EvnPS_id = resp_obj[0].EvnPS_id;						
						var ONMKRegistry_id = resp_obj[0].ONMKRegistry_id;						
						
						var lpu_id = resp_obj[0].lpu_id;
						var Lpu_Phone = resp_obj[0].Lpu_Phone;
						var Diag_id = resp_obj[0].Diag_id;
						var Person_Year = resp_obj[0].Person_Year;
						var Person_BirthDay = resp_obj[0].Person_BirthDay;
						var ONMKRegistry_Evn_DTDesease = resp_obj[0].ONMKRegistry_Evn_DTDesease;
						var ONMKRegistry_Evn_setDT = resp_obj[0].ONMKRegistry_Evn_setDT;
						var ONMKRegistry_insDT = resp_obj[0].ONMKRegistry_insDT;
						var LpuSection_pid = resp_obj[0].LpuSection_pid;
						var MedStaffFact_pid = resp_obj[0].MedStaffFact_pid;
						var TimeBeforeStac = resp_obj[0].TimeBeforeStac;
						
						var ONMKRegistry_SetDate = resp_obj[0].ONMKRegistry_SetDate;
						var EvnPS_NumCard = resp_obj[0].EvnPS_NumCard;
						
						var RankinScale_Name = resp_obj[0].RankinScale_Name;
						var RankinScale_Name_s = resp_obj[0].RankinScale_Name_s;
						var ONMKRegistry_InsultScale = resp_obj[0].ONMKRegistry_InsultScale;
						
						var ONMKRegistry_NIHSSAfterTLT = resp_obj[0].ONMKRegistry_NIHSSAfterTLT;
						var ONMKRegistry_NIHSSLeave = resp_obj[0].ONMKRegistry_NIHSSLeave;
						
						var BreathingType = resp_obj[0].BreathingType;
						var ConsciousType = resp_obj[0].ConsciousType;
						var MO_OK = resp_obj[0].MO_OK;
						
						var _form = Ext.getCmp("swONMKRegistryEditWindow");
						_form.EvnPS_NumCard = resp_obj[0].EvnPS_NumCard;
						_form.ONMKRegistry_id = resp_obj[0].ONMKRegistry_id;
						_form.lpu_id = lpu_id;
						_form.EvnPS_id = EvnPS_id;
						
						_form.loadOslList(); 
						
						
						if (MO_OK == 0){
							_form.svedPanel.getForm().findField('lpu_id').addClass("x-grid-redborderrow");
							_form.svedPanel.getForm().findField('lpu_id').addClass("onmk_lpu_ok");
						}else{
							_form.svedPanel.getForm().findField('lpu_id').removeClass("x-grid-redborderrow");
							_form.svedPanel.getForm().findField('lpu_id').removeClass("onmk_lpu_ok");
						}
						
						_form.svedPanel.getForm().findField('lpu_id').setDisabled(true);
						
						data_form.findField('Lpu_Phone').setValue(Lpu_Phone);
						data_form.findField('Person_Year').setValue(Person_Year);
						data_form.findField('Person_BirthDay').setValue(Person_BirthDay);
						
						data_form.findField('Person_BirthDay').setDisabled(true);
						
						
						data_form.findField('ONMKRegistry_Evn_DTDesease').setValue(ONMKRegistry_Evn_DTDesease);
						if (typeof ONMKRegistry_Evn_DTDesease != 'undefined' &&  ONMKRegistry_Evn_DTDesease != null){
							data_form.findField('ONMKRegistry_Evn_DTDesease').setValue(ONMKRegistry_Evn_DTDesease.substr(0,10));
							data_form.findField('ONMKRegistry_Evn_DTDesease_Time').setValue(ONMKRegistry_Evn_DTDesease.substr(11,5));
						}else{
							data_form.findField('ONMKRegistry_Evn_DTDesease').reset();
							data_form.findField('ONMKRegistry_Evn_DTDesease_Time').reset();							
						}
						
						data_form.findField('ONMKRegistry_Evn_setDT').setValue(ONMKRegistry_Evn_setDT);
						if (typeof ONMKRegistry_Evn_setDT != 'undefined' && ONMKRegistry_Evn_setDT != null){
							data_form.findField('ONMKRegistry_Evn_setDT').setValue(ONMKRegistry_Evn_setDT.substr(0,10));
							data_form.findField('ONMKRegistry_Evn_setDT_Time').setValue(ONMKRegistry_Evn_setDT.substr(11,5));
						}
										
						data_form.findField('ONMKRegistry_insDT').setValue(ONMKRegistry_SetDate);
						if (typeof ONMKRegistry_SetDate != 'undefined' && ONMKRegistry_SetDate != null){
							data_form.findField('ONMKRegistry_insDT').setValue(ONMKRegistry_SetDate.substr(0,10));
							data_form.findField('ONMKRegistry_insDT_Time').setValue(ONMKRegistry_SetDate.substr(11,5));
 						}						
						
						data_form.findField('TimeBeforeStac').setValue(TimeBeforeStac);
						
						data_form.findField('lpu_id').setValue(lpu_id);
						data_form.findField('LpuSection_pid').setValue(LpuSection_pid);
						data_form.findField('LpuSection_pid').setDisabled(true);
						//data_form.findField('MedStaffFact_pid').setValue(MedStaffFact_pid);
						//data_form.findField('Diag_id').setValue(Diag_id);
						
						console.log(RankinScale_Name);
						console.log(ONMKRegistry_InsultScale);

                        Ext.getCmp("RankinScale").setValue(RankinScale_Name);
						Ext.getCmp("RankinScale_sid").setValue(RankinScale_Name_s);
						Ext.getCmp("NihssScale").setValue(ONMKRegistry_InsultScale);
						Ext.getCmp("BreathingType").setValue(BreathingType>0);
						Ext.getCmp("ConsciousType").setValue(ConsciousType);
						
						Ext.getCmp("ONMKRegistry_NIHSSAfterTLT").setValue(ONMKRegistry_NIHSSAfterTLT);
						Ext.getCmp("ONMKRegistry_NIHSSLeave").setValue(ONMKRegistry_NIHSSLeave);						
						
						var LpuSection = data_form.findField('LpuSection_pid')
						
						console.log('LpuSection');
						console.log(LpuSection);
						
						data_form.findField('LpuSection_pid').getStore().load({
							params: {
								//Lpu_id: form.Lpu_id,
								Lpu_id: 35,
								mode: 'combo'
							},
							callback: function(){
								//form.filterLpuSectionCombo();
								//loadMask.hide();
							}}
						);							

						setMedStaffFactGlobalStoreFilter({
							EvnClass_SysNick: 'EvnSection',
							isStac: true,
							isPriemMedPers: false 
						});

						data_form.findField('MedStaffFact_pid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

						data_form.findField('LpuSection_pid').setValue(LpuSection_pid);
						data_form.findField('MedStaffFact_pid').setValue(MedStaffFact_pid);
						data_form.findField('MedStaffFact_pid').setDisabled(true);
												
                        var DiagCombo1 = Ext.getCmp('ds1');
						DiagCombo1.setDisabled(true);
						
                        DiagCombo1.getStore().load({
                            params: { where: "where Diag_id = " + Diag_id },
                            callback: function () {
                                DiagCombo1.setValue(DiagCombo1.getValue());
                                DiagCombo1.getStore().each(function (record) {
                                    if (record.data.Diag_id == DiagCombo1.getValue()) {
                                        DiagCombo1.fireEvent('select', DiagCombo1, record, 0);
                                    }
                                });
                            }
                        });
                       DiagCombo1.setValue(Diag_id);						

                    }.createDelegate(this)
                });    
				this.setNotIsNewStatus(record.ONMKRegistry_id);
            } 							
        }				
    },	       

    initComponent: function() {
        var wnd = this;

        //Панель с перс данными
        this.PersonInfoPanel  =new sw.Promed.PersonInfoPanel({
            floatable: false,
            collapsed: true,
            region: 'north',
            title: lang['zagruzka'],
            plugins: [ Ext.ux.PanelCollapsedTitle ],
            titleCollapse: true,
            collapsible: true,
            id: 'ONMK_PersonInfoPanel'
        });   
      

        //Грид с датами случаев
        this.sluchFrameGrid = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 200,
            autoLoadData: false,
            dataUrl: '/?c=ONMKRegister&m=loadSluch',
			focusOnFirstLoad: false,
            id: 'ONMKSluch',
            //height: 400,   
            region: 'center',
            pageSize: 100,
            contextmenu: false,
            //useEmptyRecord: false,
            paging: false,
            toolbar: false,
            border: false, 
            stringfields: [
                {name: 'ONMKRegistry_id', width: 100, hidden:true, type: 'int'},
				{name: 'Empty_id0', header: '', width: 10},
                {name: 'ONMKRegistry_SetDate', header: '', width: 172, type:'string'},
				{name: 'Empty_id1', header: '', width: 10}
            ],
            onRowSelect: function() {
                Ext.getCmp('swONMKRegistryEditWindow').onClickSl();
            },
            //Если в гриде один случай, нажали кнопку +, то слушателем выше заново открыть случай не получается
            onCellDblClick: function(){
                //Ext.getCmp('swECORegistryEditWindow').onClickSl();
            }
        });

        
        //панель с Гридом с датами случаев ОНМК
        this.gridSluch = new Ext.Panel({
            collapsible: true,     
            title: 'Случаи ОНМК',
            width : 200,                             
            region: 'west',
            split: true,
            useSplitTips: true,
            minSize: 200,
            maxSize: 400,
            bodyBorder: true,
            layout: 'border',
            id: 'Sluch',
            border: true,
            style: 'padding:2px;margin:0px;',
            plugins: [Ext.ux.PanelCollapsedTitle],
            items : [this.sluchFrameGrid]
        });
        
        //панель сведений
        this.svedPanel = new Ext.FormPanel({
            title: null,
            //bodySyle:'background:#ffffff;',
            style: 'margin: 5px 10px 0px 0px;',            
            layout: 'form',    
            autoWidth: true,
            id: 'svedPanel',
            labelWidth: 165,
            labelAlign: 'right',
            items: [
				
				{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
                
							  xtype: 'textfield',
							  id : 'Pers_id',
							  name: 'pers_id',
							  labelSeparator : ':',
							  hidden: true,
							  hideLabel: true,
							  fieldLabel: 'перс ид'
							},
							{
								fieldLabel: 'МО госпитализации',
								xtype: 'swlpucombo',
								autoload: true,
								mode: 'local',
								width: 450,
								hiddenName: 'lpu_id'

							}, {				
								fieldLabel: 'Диагноз по МКБ-10',
								name: 'ds1',
								id : 'ds1',                    
								allowBlank : false,
								width: 450,
								xtype: 'swdiagcombo'					                    										
							},{
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										xtype: 'numberfield',
										name: 'Person_Year',
										fieldLabel: 'Возраст на начало случая',
										width: 124,
										readOnly: true
									}]
								}, {
									layout: 'form',
									labelWidth: 205,
									items: [{
										xtype: 'swdatefield',
										id : 'Person_BirthDay',                                          
										name: 'Person_BirthDay',
										width: 120,
										labelSeparator : ':',
										fieldLabel: 'Дата рождения'
									}]
								}]
							},{
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										xtype: 'textfield',
										id : 'ONMKRegistry_Evn_DTDesease',                                          
										name: 'ONMKRegistry_Evn_DTDesease',
										width: 75,
										labelSeparator : ':',
										fieldLabel: 'Дата начала заболевания',
										readOnly: true
									}]
								},
								{
									layout: 'form',
									labelWidth: 3,
									items: [{
										xtype: 'textfield',
										id : 'ONMKRegistry_Evn_DTDesease_Time',                                          
										name: 'ONMKRegistry_Evn_DTDesease_Time',
										labelSeparator : '',
										width: 40,
										readOnly: true
									}]
								},
								{
									layout: 'form',
									labelWidth: 205,
									items: [{
										fieldLabel: 'Время до госпитализации',
										id: 'TimeBeforeStac',
										name: 'TimeBeforeStac',
										width: 120,
										xtype: 'textfield',
										readOnly: true										
									}]
								}]
							},{
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										xtype: 'textfield',
										id : 'ONMKRegistry_Evn_setDT',                                          
										name: 'ONMKRegistry_Evn_setDT',
										width: 75,
										labelSeparator : ':',
										fieldLabel: 'Дата госпитализации',
										readOnly: true
									}]
								},
								{
									layout: 'form',
									labelWidth: 3,
									items: [{
										xtype: 'textfield',
										id : 'ONMKRegistry_Evn_setDT_Time',                                          
										name: 'ONMKRegistry_Evn_setDT_Time',
										labelSeparator : '',
										width: 40,
										readOnly: true
									}]
								}]					
							},{
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										xtype: 'textfield',
										id : 'ONMKRegistry_insDT',                                          
										name: 'ONMKRegistry_insDT',
										width: 75,
										labelSeparator : ':',
										fieldLabel: 'Дата создания КВС',
										readOnly: true										
									}]
								},
								{
									layout: 'form',
									labelWidth: 3,
									items: [{
										xtype: 'textfield',
										id : 'ONMKRegistry_insDT_Time',                                          
										name: 'ONMKRegistry_insDT_Time',
										labelSeparator : '',
										width: 40,
										readOnly: true
									}]
								}]
							}
						]
					}, {
						layout: 'form',
						labelWidth: 125,
						items: [{
							hiddenName: 'LpuSection_pid',
							id: this.id + 'LpuSection_pid',
							width: 350,
							xtype: 'swlpusectionglobalcombo'
						}, {
							xtype: 'swmedstafffactglobalcombo',
							hiddenName: 'MedStaffFact_pid',
							fieldLabel: 'Лечащий врач',
							width: 350								
						}, {
							fieldLabel: 'Телефон МО',
							id: 'Lpu_Phone',
							name: 'Lpu_Phone',
							width: 350,
							xtype: 'textfield',
							readOnly: true
						}]
					}]
				}
			] 
        });        
		


        //Грид услуг
        this.uslugaConsult = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 110,
            dataUrl: '/?c=ONMKRegister&m=loadEvnUslugaGrid',
            autoLoadData: false,
            //id: 'uslugaGrid',
			id: 'uslugaConsultGrid',
            height: 150, 
            style : 'border: 1px solid #666',
            autoWidth: true,
            pageSize: 100,
            contextmenu: false,
            paging: false,
            toolbar: false, 
            border: true, 
            stringfields: [ 
                {name: 'EvnUsluga_id', header: 'Идентификатор услуги', width: 100, type:'string', hidden: true},
                {name: 'EvnUsluga_pid', header: 'Идентификатор pid', width: 95, type:'string', hidden: true},
                {name: 'Usluga_Code', header: 'Код', width: 210, type:'string', align:'center'},
                {name: 'Usluga_Name', header: '<center>Наименование</center>', width: 400, type:'string', align:'left'},
                {name: 'EvnUsluga_setDT', header: 'Дата/Время проведения', width: 200, type:'string', align:'center'},
                {name: 'EvnUsluga_insDT', header: 'Дата/Время внесения', width: 200, type:'string', align:'center'}
            ]
        });
		
		Ext.getCmp('uslugaConsultGrid').getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {			 				
				if (row.get('Usluga_Code') == 'A16.23.034.011' || row.get('Usluga_Code') ==  'A16.23.034.012' || row.get('Usluga_Code') ==  'A25.30.036.002' || row.get('Usluga_Code') ==  'A25.30.036.003') {									
					return ' x-grid-rowlightpink x-grid-rowbold';
				}
				
				return '';
			}
		});			
		
        this.uslugaOper = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 110,
            dataUrl: '/?c=ONMKRegister&m=loadEvnUslugaGrid',
            autoLoadData: false,
            //id: 'uslugaGrid',
			id: 'uslugaOperGrid',
            height: 150, 
            style : 'border: 1px solid #666',
            autoWidth: true,
            pageSize: 100,
            contextmenu: false,
            paging: false,
            toolbar: false, 
            border: true, 
            stringfields: [ 
                {name: 'EvnUsluga_id', header: 'Идентификатор услуги', width: 100, type:'string', hidden: true},
                {name: 'EvnUsluga_pid', header: 'Идентификатор pid', width: 95, type:'string', hidden: true},
                {name: 'Usluga_Code', header: 'Код', width: 210, type:'string', align:'center'},
                {name: 'Usluga_Name', header: '<center>Наименование</center>', width: 400, type:'string', align:'left'},
                {name: 'EvnUsluga_setDT', header: 'Дата/Время проведения', width: 200, type:'string', align:'center'},
                {name: 'EvnUsluga_insDT', header: 'Дата/Время внесения', width: 200, type:'string', align:'center'}
            ]
        });
		
		Ext.getCmp('uslugaOperGrid').getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {			 				
				if (row.get('Usluga_Code') == 'A16.23.034.011' || row.get('Usluga_Code') ==  'A16.23.034.012' || row.get('Usluga_Code') ==  'A25.30.036.002' || row.get('Usluga_Code') ==  'A25.30.036.003') {									
					return ' x-grid-rowlightpink x-grid-rowbold';
				}
				
				return '';
			}
		});		
		
        this.uslugaCommon = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 110,
            dataUrl: '/?c=ONMKRegister&m=loadEvnUslugaGrid',
            autoLoadData: false,
            //id: 'uslugaGrid',
			id: 'uslugaCommonGrid',
            height: 150, 
            style : 'border: 1px solid #666',
            autoWidth: true,
            pageSize: 100,
            contextmenu: false,
            paging: false,
            toolbar: false, 
            border: true, 
            stringfields: [ 
                {name: 'EvnUsluga_id', header: 'Идентификатор услуги', width: 100, type:'string', hidden: true},
                {name: 'EvnUsluga_pid', header: 'Идентификатор pid', width: 95, type:'string', hidden: true},
                {name: 'Usluga_Code', header: 'Код', width: 210, type:'string', align:'center'},
                {name: 'Usluga_Name', header: '<center>Наименование</center>', width: 400, type:'string', align:'left'},
                {name: 'EvnUsluga_setDT', header: 'Дата/Время проведения', width: 200, type:'string', align:'center'},
                {name: 'EvnUsluga_insDT', header: 'Дата/Время внесения', width: 200, type:'string', align:'center'}
            ]
        });	
		
		Ext.getCmp('uslugaCommonGrid').getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {			 				
				if (row.get('Usluga_Code') == 'A16.23.034.011' || row.get('Usluga_Code') ==  'A16.23.034.012' || row.get('Usluga_Code') ==  'A25.30.036.002' || row.get('Usluga_Code') ==  'A25.30.036.003') {
					return ' x-grid-rowlightpink x-grid-rowbold';
				}
				
				return '';
			}
		});		

		
		this.paneluslConsult =  new Ext.FormPanel({
			title: null,
			autoWidth: true,
			id: 'paneluslConsult',
			layout: 'form',
			items: [ 
				this.uslugaConsult			
			]
		});
		
		this.paneluslOper =  new Ext.FormPanel({
			title: null,
			autoWidth: true,
			id: 'paneluslOper',
			layout: 'form',
			items: [ 
				this.uslugaOper		
			]
		});
		
		this.paneluslCommon =  new Ext.FormPanel({
			title: null,
			autoWidth: true,
			id: 'paneluslCommon',
			layout: 'form',
			items: [ 
				this.uslugaCommon
			]
		});

        //центральная таб панель
        this.centerPanel = new Ext.TabPanel({
            plain: false,
            border: true,      
            region: 'center',
            bodyBorder : false,
            style: 'padding:2px;margin:0px;',
            autoScroll : true,
            layoutOnTabChange: true,
            deferredRender: true,
            activeTab: 0,    
            items : [
                {
                    autoHeight: true,                                    
                    title: 'Сведения',
                    id: 'tabSved',
                    border: false,    
                    style: 'padding:10px;margin:0px;',
                    items : [
                        this.svedPanel,
						{
							xtype: 'fieldset',
							autoHeight: true,
				            labelWidth: 240,
					        labelAlign: 'right',
							width: 1100,
							cls: "onmk-header",
							title: 'Мониторинг состояния пациента',
							items:[{
								layout: 'column',									
								style: 'align:center',
								items: [{
									layout: 'form',
									labelWidth: 180,
									items: [{
										xtype: 'textfield',
										id : 'RankinScale',
										name: 'RankinScale',
										width: 95,
										labelSeparator : ':',
										fieldLabel: 'Рэнкин при поступлении',
										readOnly: true
									}]
								}, {
									layout: 'form',
									labelWidth: 180,
									items: [{
										xtype: 'textfield',
										id : 'NihssScale',                                          
										name: 'NihssScale',
										width: 190,
										labelSeparator : ':',
										fieldLabel: 'NIHSS при поступлении',
										readOnly: true
									}]									
								},
								{
									layout: 'form',
									labelWidth: 80,
									items: [{
										xtype: 'checkbox',
										id : 'BreathingType', 
										name: 'BreathingType',
										width: 270,
										labelSeparator : ':',
										fieldLabel: 'ИВЛ',
										readOnly: true
									}]
								}]
							},
							{
								layout: 'column',
								style: 'align:center',
								items: [{
									layout: 'form',
									labelWidth: 180,
									items: [{
										xtype: 'textfield',
										id : 'RankinScale_sid',
										name: 'RankinScale_sid',
										width: 95,
										labelSeparator : ':',
										fieldLabel: 'Рэнкин при выписке',
										readOnly: true
									}]
								},
								{
									layout: 'form',
									labelWidth: 180,
									items: [{
										xtype: 'textfield',
										id : 'ONMKRegistry_NIHSSAfterTLT',
										name: 'ONMKRegistry_NIHSSAfterTLT',
										width: 190,
										labelSeparator : ':',
										fieldLabel: 'NIHSS после ТЛТ',
										readOnly: true		
									}]
								},
								{
									layout: 'form',
									labelWidth: 155,
									items: [{
										xtype: 'textfield',
										id : 'ConsciousType',
										name: 'ConsciousType',
										width: 220,
										labelSeparator : ':',
										fieldLabel: 'Уровень сознания',
										readOnly: true										
									}]
								}								
							]
							},
							{
								layout: 'column',
								style: 'align:center',
								items: [
								{
									layout: 'form',
									labelWidth: 275,
									items: [{
										xtype: 'textfield',
										labelSeparator : '',
										hidden:true
									}]
								},																
								{
									layout: 'form',
									labelWidth: 180,
									items: [{
										xtype: 'textfield',
										id : 'ONMKRegistry_NIHSSLeave',
										name: 'ONMKRegistry_NIHSSLeave',
										width: 190,
										labelSeparator : ':',
										fieldLabel: 'NIHSS при выписке',
										readOnly: true		
									}]
								}
								]
							},
							{
								xtype: 'label',
								style: "font-size:11px;font-weight:bold;",
								text: 'Консультационные услуги',
								margin: '100',
								padding: '50'
							},
							this.paneluslConsult,
							{
								xtype: 'label',
								style: "font-size:11px;font-weight:bold;",
								text: 'Операционные услуги',
								margin: '0 0 0 10'
							},
							this.paneluslOper,
							{
								xtype: 'label',
								style: "font-size:11px;font-weight:bold;",
								text: 'Общие услуги',
								margin: '0 0 0 10'
							},
							this.paneluslCommon ]
						}
                    ]
                }
			]
        });

        //Главная панель, в которой собрано все выше описанное
        this.MainPanel = new Ext.Panel({
            //autoScroll: true,
            bodyBorder: false,
            bodyStyle: 'padding: 2px',
            border: true,
            layout: 'border',
            frame: true,
            region: 'center',
            labelAlign: 'right',
            items:[
                this.centerPanel,
                this.PersonInfoPanel,
                this.gridSluch
            ]
//			listeners:{
//				close:function(){
//					alert(1);
//				}
//			},
//			beforeDestroy: function(){
//				alert(2);
//			}
		});
		
        Ext.apply(this, {
           layout: 'border',
            buttons: [{
				text: '-'
			},
			{
				handler: function()  {
					Ext.getCmp('swONMKRegistryViewWindow').ONMKRegistrySearchFrame.refreshRecords(null,0);
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: 'Закрыть'
			}],
            items:[wnd.MainPanel]
        });

        sw.Promed.swONMKRegistryEditWindow.superclass.initComponent.apply(this, arguments);									
    }
});