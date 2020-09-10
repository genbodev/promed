/**
 * swECORegistryEditWindow - Запись в Регистр ЭКО.
 */

sw.Promed.swECORegistryEditWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    autoScroll: true,
    title: 'Регистр по ВРТ: Добавление',
    layout: 'form',
    id: 'swECORegistryEditWindow',
    modal: true,
    onHide: Ext.emptyFn,
    onSelect:  Ext.emptyFn,
    shim: false,
    resizable: false,
    maximizable: false,
    maximized: true,
    region: 'center',
    //размеры едитов комбобоксов и дат, значение большое, значение маленькое и значения ввода даты
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
        sw.Promed.swECORegistryEditWindow.superclass.show.apply(this, arguments);
        
        var wnd = this;
        var data_form = wnd.svedPanel.getForm();		
		
		this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		if (arguments[0] && arguments[0].ARMType_id) {
			this.ARMType_id = arguments[0].ARMType_id;
		} else if (arguments[0] && arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.ARMType_id) {
			this.ARMType_id = arguments[0].userMedStaffFact.ARMType_id;
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			this.ARMType_id = null;
		}		
        
        data_form.reset();
        wnd.oslFrameGrid.getGrid().store.removeAll();
        wnd.uslFrameGrid.getGrid().store.removeAll();
        Ext.getCmp('Date_add').setDisabled(false);
		Ext.getCmp('Date_add').setValue((new Date()).format('d.m.Y'));
        data_form.findField('pers_id').setValue(params.Person_id);
        data_form.findField('eco_id').setValue(0);
		
        
        Ext.getCmp('vid_ber').getEl().up('.x-form-item').setDisplayed(false);
        Ext.getCmp('vid_ber').allowBlank = true;
        Ext.getCmp('vid_ber').validate();  
        
        Ext.getCmp('count_plod').getEl().up('.x-form-item').setDisplayed(false);
        Ext.getCmp('count_plod').allowBlank = true;
        Ext.getCmp('count_plod').validate();

        this.PersonInfoPanelEco.personId = params.Person_id;
        this.PersonInfoPanelEco.serverId = params.Server_id;

        this.PersonInfoPanelEco.setTitle('...');
        this.PersonInfoPanelEco.load({
            callback: function() {
                this.PersonInfoPanelEco.setPersonTitle();
            }.createDelegate(this),
            Person_id: this.PersonInfoPanelEco.personId,
            Server_id: this.PersonInfoPanelEco.serverId
        });
        wnd.onHide = Ext.emptyFn;   
        
        Ext.getCmp('Ds_eco').allowBlank = true;
        Ext.getCmp('Ds_eco').validate();

        Ext.getCmp('Res_date').allowBlank = true;
        Ext.getCmp('Res_date').validate(); 
        
        this.centerPanel.setActiveTab(0);
               			
		var base_form = this.svedPanel.getForm();
		var med_personal_combo = base_form.findField('MedPersonal_sid');
		var newValue = getGlobalOptions().lpu[0];


		if (Ext.isEmpty(newValue) || newValue == -1) {
			med_personal_combo.setValue(null);
			med_personal_combo.getStore().removeAll();
		} else {
			med_personal_combo.getStore().load({
				params: {Lpu_id: newValue},
				callback: function() {
					med_personal_combo.setValue(getGlobalOptions().medpersonal_id);
				}
			});
		}		
        
        this.loadSluchList();
        //this.onLoadDs(); 
        this.act = params.action;
        //this.onReadOnly(params.action,this.isThisUser());
    },
    
    //проверка соответствия лпу текущего пользователя, лпу случая эко
    isThisUser: function(){        
		console.log('isThisUser');	
		console.log(this.lpu_id);
		console.log(getGlobalOptions().lpu[0]);	
        if (this.lpu_id == getGlobalOptions().lpu[0]){
            return true;
        }
        else return false;
    },
    
    //Установка формы только для просмотра
    onReadOnly: function(action, isUser){
		
        var addBtnSl = Ext.getCmp('addObjectButton');
        var addBtnUsl = Ext.getCmp('addObjectButtonUsl');
        var delBtnUsl = Ext.getCmp('deleteObjectButtonUsl');
        var dateFld = Ext.getCmp('Date_add');
        var dsFld = Ext.getCmp('ds1');
        var oplodFld = Ext.getCmp('Vid_oplod');
        var oplatFld = Ext.getCmp('Vid_oplat');
        var pgdFld = Ext.getCmp('Gen_diag');
        var countFld = Ext.getCmp('Count_embrion');
        var addBtnOsl = Ext.getCmp('addObjectButtonOsl');
        var delBtnOsl = Ext.getCmp('deleteObjectButtonOsl');
        var resFld = Ext.getCmp('Res_eco');
        var resDateFld = Ext.getCmp('Res_date');
        var dsEcoFld = Ext.getCmp('Ds_eco');
        var vidBerFld = Ext.getCmp('vid_ber');
        var countPlodFld = Ext.getCmp('count_plod');
		
		var viewBtnUsl = Ext.getCmp('viewObjectButtonUsl');	
		var viewBtnOsl = Ext.getCmp('viewObjectButtonOsl');
		
		var base_form = this.findById('svedPanel').getForm();
		var obPayType = base_form.findField('PayType_id');
		var item_0 = obPayType.store.getAt(5);
		obPayType.store.remove(item_0);			
        
        if (action == 'edit'& isUser){
            this.setTitle('Регистр по ВРТ: Редактирование');
            addBtnUsl.setDisabled(false);
            delBtnUsl.setDisabled(false);
            dsFld.setDisabled(false);
            oplodFld.setDisabled(false);
            oplatFld.setDisabled(false);
            pgdFld.setDisabled(false);
            countFld.setDisabled(false);
            addBtnOsl.setDisabled(false);
            delBtnOsl.setDisabled(false);
            resFld.setDisabled(false);
            resDateFld.setDisabled(false);
            dsEcoFld.setDisabled(false);
            vidBerFld.setDisabled(false);
            countPlodFld.setDisabled(false);
			
			viewBtnUsl.setDisabled(false);
			viewBtnOsl.setDisabled(false);	
			Ext.getCmp("ecosave").setDisabled(false);			
            
        }
        else if (action == 'add'& isUser){
            this.setTitle('Регистр по ВРТ: Добавление');
            addBtnUsl.setDisabled(false);
            delBtnUsl.setDisabled(false);
            dsFld.setDisabled(false);
            oplodFld.setDisabled(false);
            oplatFld.setDisabled(false);
            pgdFld.setDisabled(false);
            countFld.setDisabled(false);
            addBtnOsl.setDisabled(false);
            delBtnOsl.setDisabled(false);
            resFld.setDisabled(false);
            resDateFld.setDisabled(false);
            dsEcoFld.setDisabled(false);
            vidBerFld.setDisabled(false);
            countPlodFld.setDisabled(false); 
			
			viewBtnUsl.setDisabled(false);
			viewBtnOsl.setDisabled(false);	
			Ext.getCmp("ecosave").setDisabled(false);			
        }
        else{
            this.setTitle('Регистр по ВРТ: Просмотр');
            addBtnUsl.setDisabled(true);
            delBtnUsl.setDisabled(true);
            dsFld.setDisabled(true);
            oplodFld.setDisabled(true);
            oplatFld.setDisabled(true);
            pgdFld.setDisabled(true);
            countFld.setDisabled(true);
            addBtnOsl.setDisabled(true);
            delBtnOsl.setDisabled(true);
            resFld.setDisabled(true);
            resDateFld.setDisabled(true);
            dsEcoFld.setDisabled(true);
            vidBerFld.setDisabled(true);
            countPlodFld.setDisabled(true);   

			viewBtnUsl.setDisabled(true);
			viewBtnOsl.setDisabled(true);	
			Ext.getCmp("ecosave").setDisabled(true);
        }
        
    },
    
    //Удаление услуги
    onDelUsl: function(id){
        if (id){
            Ext.Ajax.request({ 
            url: '/?c=Eco&m=delEcoUsl', 
            params: { 
                uslId: id
            } 
        });
        }        
        
        var grid =  Ext.getCmp('uslGrid').getGrid();  

        if (!grid.getSelectionModel().getSelected() )
        {
            Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
            return false;
        }
        var record = grid.getSelectionModel().getSelected();
        grid.store.remove(record);
    },

    //Удаление осложнения
    onDelOsl: function(){
        var grid =  Ext.getCmp('oslGrid').getGrid();  

        if (!grid.getSelectionModel().getSelected() )
        {
            Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
            return false;
        }
        var record = grid.getSelectionModel().getSelected();
        Ext.getCmp('oslGrid').getGrid().store.remove(record);

    },
    
    //загрузка уточненных диагнозов
    onLoadDs: function(){
        var parametrs = {}; 
        parametrs.Person_id = this.svedPanel.getForm().findField('pers_id').getValue()
        this.diagsPanel.loadData({globalFilters: parametrs}); 
    },
    
    /***Список осложнений 
     * загружаю через объект, а не через url чтоб избежать косяков с пустыми строками***/
    loadOslList: function(){		
		
        var form = this;
        var data_form = form.svedPanel.getForm(); 
        Ext.getCmp('oslGrid').getGrid().store.removeAll();
        
        var loadMask = new Ext.LoadMask(Ext.getCmp('oslGrid').getGrid().getEl(), {msg: "Загрузка..."});
        loadMask.show();
        
        Ext.Ajax.request({ 
            url: '/?c=Eco&m=loadEcoOsl', 
            params: { 
                Eco_id:data_form.findField('eco_id').getValue()
            },

            success: function(result){
                var resp_obj = Ext.util.JSON.decode(result.responseText); 
                
                var ind = 0;
                var indEnd = resp_obj.length;
                while (ind<indEnd) {                    
                    var nRecord = Ext.getCmp('oslGrid').getGrid().store.getCount();
                    Ext.getCmp('oslGrid').getGrid().getStore().insert(nRecord, [new Ext.data.Record({
								EcoOsl_id: resp_obj[ind].EcoOsl_id,
                                 Date_osl: resp_obj[ind].Date_osl,
                                 Osl_id: resp_obj[ind].Osl_id,
                                 Osl: resp_obj[ind].Osl,                                     
                                 Ds: resp_obj[ind].Ds,
                                 Ds_int: resp_obj[ind].Ds_int 
                                })]);
                            
                    ind++;
                }
                
            }
        });
        
        loadMask.hide();
    },

    //Список услуг случая
    //загружаю через объект, а не через url чтоб избежать косяков с пустыми строками
    loadUslList: function(){
        var form = this;
        var data_form = form.svedPanel.getForm();
        form.uslFrameGrid.getGrid().store.removeAll();
        var parametrs = {}; 
        parametrs.PersID = data_form.findField('pers_id').getValue(); 
        parametrs.DateUsl = Ext.util.Format.date(data_form.findField('date_add').getValue(), 'Y-m-d');
        var DateUslBeg = Ext.util.Format.date(data_form.findField('date_add').getValue(), 'Y-m-d');
        var DateUslEnd = Ext.util.Format.date(data_form.findField('res_date').getValue(), 'Y-m-d');
        if (DateUslEnd==''){
           DateUslEnd = '9999-01-01' ;
        }		
		
        Ext.Ajax.request({ 
            url: '/?c=Eco&m=loadEcoUsl', 
            params: { 
                PersID: data_form.findField('pers_id').getValue(),
                DateUslBeg: DateUslBeg,
                DateUslEnd: DateUslEnd
            },

            success: function(result){
                var resp_obj = Ext.util.JSON.decode(result.responseText); 
                
                var ind = 0;
                var indEnd = resp_obj.length;
                while (ind<indEnd) {                    
                    var nRecord = form.uslFrameGrid.getGrid().store.getCount();
                    form.uslFrameGrid.getGrid().getStore().insert(nRecord, [new Ext.data.Record({
                                usl_id: resp_obj[ind].usl_id,
                                DateUslStr: resp_obj[ind].DateUslStr,
                                CodeUsl: resp_obj[ind].Code_usl,
                                NameUsl: resp_obj[ind].Name_usl, 
                                Eco_usl_id: resp_obj[ind].Eco_usl_id,
                                MO:resp_obj[ind].MO,
                                DS:resp_obj[ind].DS,
								del:resp_obj[ind].del
                                })]);
                            
                    ind++;
                }
                
            }
        });
        
    },

   //загрузка список случаев эко
    loadSluchList: function(doselect){
        var form = this;
        var data_form = form.svedPanel.getForm();
        var parametrs = {}; 
        parametrs.PersID = data_form.findField('pers_id').getValue();           
		form.sluchFrameGrid.loadData({globalFilters: parametrs});
		
    },

    //нажатие кнопки "добавить"
    onAddPress: function(){
        var form = this;
        var data_form = form.svedPanel.getForm();
        var pers_id = data_form.findField('pers_id').getValue();       
        
        if (form.title=='Регистр по ВРТ: Добавление'){
           sw.swMsg.show(
		{
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.WARNING, //icon: Ext.Msg.INFO,//
                    msg: 'Окно добавления уже открыто',
                    title: 'Окно добавления уже открыто'		
		}
            );
            return false;
        }        
        
        Ext.Ajax.request({ 
            url: '/?c=Eco&m=checkLastRes', 
            params: { 
                Pers_id :data_form.findField('pers_id').getValue()
            },

            success: function(result){                
                var resp_obj = Ext.util.JSON.decode(result.responseText);  
                if (resp_obj[0].no_res>0){
                    sw.swMsg.show(
                        {
                            buttons: Ext.Msg.OK,			
                            icon: Ext.Msg.WARNING, //icon: Ext.Msg.INFO,//                            
							msg: 'Добавление случая с применением ВРТ невозможно. У данного пациента есть случай с применением ВРТ с незаполненным результом',
                            title: 'Ошибка',			
                        });	
                    return false;
                }
                else{
                    form.onReadOnly('add',true);                    
                    data_form.reset();
                    form.oslFrameGrid.getGrid().store.removeAll();
                    form.uslFrameGrid.getGrid().store.removeAll();
                    data_form.findField('pers_id').setValue(pers_id);
                    data_form.findField('eco_id').setValue(0);    
                    Ext.getCmp('Date_add').setDisabled(false);
                    Ext.getCmp('Ds_eco').allowBlank = true;
                    Ext.getCmp('Ds_eco').validate();

                    Ext.getCmp('Res_date').allowBlank = true;
                    Ext.getCmp('Res_date').validate(); 

                    var res_cbx = data_form.findField('EcoResultType_id');
                    var rec_res = {};
                    rec_res.id = 0;
                    rec_res.data = {id:0, selection_code:0};
                    res_cbx.fireEvent('select', res_cbx, rec_res, 0);                           
                    res_cbx.setValue();

                    var vidb_cbx = data_form.findField('EcoPregnancyType_id');                        
                    var rec_vid = {};
                    rec_vid.id = 0;
                    rec_vid.data = {id:0, selection_code:0};
                    vidb_cbx.fireEvent('select', vidb_cbx, rec_vid, 0);
                    vidb_cbx.setValue();                    
                }
            }
        });   
    },

    //Нажать на случай в гриде
    onClickSl: function(){  
		
        var form = this;
        var data_form = form.svedPanel.getForm();	
        if (form.sluchFrameGrid.getGrid().getStore().data.items[0].data.Eco_id)
        {
            Ext.getCmp('Date_add').setDisabled(true);
            var record = form.sluchFrameGrid.getGrid().getSelectionModel().getSelections()[0].data; 
            if (record){
                var id_eco = record.Eco_id;
                var dAdd = record.DateAdd; 

                data_form.findField('date_add').setValue(dAdd);
                data_form.findField('eco_id').setValue(id_eco);
                this.loadOslList();

                Ext.Ajax.request({ 
                    url: '/?c=Eco&m=loadEcoSluchData', 
                    params: { 
                    Eco_id: id_eco
                    },

                    success: function(result){
                        var resp_obj = Ext.util.JSON.decode(result.responseText);
                        var ds = resp_obj[0].DS;
                        var dateAdd = resp_obj[0].DateAdd;
                        var embrionCount = resp_obj[0].EmbrionCount;
                        var genetigDiag = resp_obj[0].GenetigDiag;
                        var result = resp_obj[0].Result;
                        var resultDate= resp_obj[0].ResultDate;
                        var vidOplat = resp_obj[0].VidOplat;
                        var vidOplod = resp_obj[0].VidOplod;
                        var ds1 = resp_obj[0].DS_osn;
                        var vidBer = resp_obj[0].VidBer;
                        var countPlod = resp_obj[0].CountPlod;
                        var pmUser = resp_obj[0].pmUser_id;
						
                        var lpu_id = resp_obj[0].lpu_id;
						var PersonRegister_id = resp_obj[0].PersonRegister_id;
						var Person_id = resp_obj[0].Person_id;
						
						var PersonRegisterOutCause_id = resp_obj[0].PersonRegisterOutCause_id;
						var BirthSpecStac_id = resp_obj[0].BirthSpecStac_id;
						var PersonRegisterOutCause_id_link = resp_obj[0].PersonRegisterOutCause_id_link;
						var BirthSpecStac_id_link = resp_obj[0].BirthSpecStac_id_link;	
						var PregnancyPersonRegister_id = resp_obj[0].PregnancyPersonRegister_id;
						var PersonRegister_id_link = resp_obj[0].PersonRegister_id_link;						
						var BirthSpecStac_id_Create_Eco = resp_obj[0].BirthSpecStac_id_Create_Eco;
						var MedPersonal_id = resp_obj[0].MedPersonal_id;						
						
						/*
						//причина выбывания из РБ привязанного к рассматриваемому случаю ЭКО
						console.log('PersonRegisterOutCause_id='+PersonRegisterOutCause_id);
						
						//идентификатор исход из РБ последний, не обязательно привязанный
						console.log('BirthSpecStac_id=' + BirthSpecStac_id);
						
						//причина выбывания из регистра беременных по привязанному случаю
						console.log('PersonRegisterOutCause_id_link='+PersonRegisterOutCause_id_link);
						
						//идентификатор исхода в РБ по привязанному случаю
						console.log('BirthSpecStac_id_link='+BirthSpecStac_id_link);
						
						//результат в ЭКО
						console.log('EcoResultType='+result);
						
						//случай из РБ последний, не обязательно привязанный
						console.log('PregnancyPersonRegister_id='+PregnancyPersonRegister_id);						
						
						
						//Идентификатор случая из РБ привязанного к рассматриваемому случаю ЭКО
						console.log('PregnancyPersonRegister_id='+PersonRegister_id_link);						

						//Идентификатор исхода созданного из регистра ЭКО
						console.log('BirthSpecStac_id_Create_Eco='+BirthSpecStac_id_Create_Eco);												
						*/
						var statustree = 1;
						if (PregnancyPersonRegister_id == null || PersonRegisterOutCause_id == 2){
							//исключен или никогда не был в регистре беременных														
							if (PersonRegister_id_link == null){
								//связи с регистром берем нет
								if (BirthSpecStac_id == null){
									//исхода в РБ нет
									if (result == null){
										//результата ЭКО нет
										statustree = 1;
									}else{										
										if (result == 1){
											statustree = 3;																													
										}else{
											statustree = 1;
										}											
									}
								}															
							}else{
								//связь с регистром берем есть
								if (BirthSpecStac_id_link == null){
									//исхода в РБ нет
									if (result == null){
										//результата ЭКО нет
										statustree = 1;
									}else{
										//результата ЭКО есть
										if (result == 1){
											//беременность наступила
											statustree = 3;
										}																			
									}
								}
							}
						}else if (PregnancyPersonRegister_id != null && PersonRegisterOutCause_id_link == null && BirthSpecStac_id == null){
							//стоит на учете в регистре беременных
							if (PersonRegister_id_link == null){
								//связиь с регистром берем нет
								if (BirthSpecStac_id == null){
									//исхода в РБ нет
									if (result == null){
										//результата ЭКО нет
										statustree = 1;
									}else{
										//результата ЭКО есть
										if (result == 1){
											//беременность наступила
											statustree = 3;
										}																			
									}
								}														
							}else{
								//связь с регистром берем есть
								if (BirthSpecStac_id_link == null){
									//исхода в РБ нет
									if (result == null){
										//результата ЭКО нет
										statustree = 1;
									}else{
										//результата ЭКО есть
										if (result == 1){
											//беременность наступила
											statustree = 2;
										}																			
									}
								}																							
							}							
							
						}else if (PregnancyPersonRegister_id != null && (PersonRegisterOutCause_id_link != 2)){
							//выбывший из регистра беременных
							if (PersonRegister_id_link == null){
								//связи с регистром берем нет
								if (BirthSpecStac_id != null){
									//исход в РБ есть
									if (result == null){
										//результата ЭКО нет
										statustree = 1;
									}else{
										//результата ЭКО есть
										if (result == 1){
											//беременность наступила
											statustree = 3;
										}																			
									}																								
								}															
							}else{
								//связь с регистром берем есть
								if (BirthSpecStac_id_link != null){
									//исход в РБ есть
									if (BirthSpecStac_id_Create_Eco != null){
										statustree = 2;
									}else{
										statustree = 4;
									}									
								}																							
							}
						}
						
						
						this.PersonRegister_id = PersonRegister_id;
						this.Person_id = Person_id;												

                        data_form.findField('date_add').setValue(dateAdd);
                        data_form.findField('vid_oplod').setValue(vidOplod);
                        data_form.findField('PayType_id').setValue(vidOplat);
                        data_form.findField('gen_diag').setValue(genetigDiag);
                        data_form.findField('EmbrionCount_id').setValue(embrionCount);
						
						if (MedPersonal_id != null){													
							data_form.findField('MedPersonal_sid').getStore().load({
								params: {Lpu_id: lpu_id},
								callback: function() {
									data_form.findField('MedPersonal_sid').setValue(MedPersonal_id);
								}
							});																					
						}
                        
                        this.user_id = pmUser;
                        this.lpu_id = lpu_id;
						//this.PersonRegister_id = PersonRegister_id;
						console.log(form.act);
						console.log(form.isThisUser());						
                        this.onReadOnly(form.act,form.isThisUser());
                        
                        data_form.findField('EcoChildCountType_id').setValue(countPlod);

                        var res_cbx = data_form.findField('EcoResultType_id');
                        var rec_res = {};
                        rec_res.id = result;
                        rec_res.data = {id:result, selection_code:result};
                        res_cbx.fireEvent('select', res_cbx, rec_res, 0);                           
                        res_cbx.setValue(result);
                        
                        var vidb_cbx = data_form.findField('EcoPregnancyType_id');                        
                        var rec_vid = {};
                        rec_vid.id = vidBer;
                        rec_vid.data = {id:vidBer, selection_code:vidBer};
                        vidb_cbx.fireEvent('select', vidb_cbx, rec_vid, 0);
                        vidb_cbx.setValue(vidBer);
                        
                        data_form.findField('res_date').setValue(resultDate);

                         var DiagCombo = Ext.getCmp('Ds_eco');
                        DiagCombo.getStore().load({
                            callback: function () {
                                var rec = DiagCombo.getStore().getById(ds);
                                if (rec){
                                    DiagCombo.setValue(ds);                                   
                                } else{
                                    DiagCombo.setValue(null);
                                }
                            },
                            params: { where: "where DiagLevel_id = 4 and Diag_id = " + ds }
                        });
                       
                        var DiagCombo1 = Ext.getCmp('ds1');
                        DiagCombo1.getStore().load({
                            params: { where: "where Diag_id = " + ds1 },
                            callback: function () {
                                DiagCombo1.setValue(DiagCombo1.getValue());
                                DiagCombo1.getStore().each(function (record) {
                                    if (record.data.Diag_id == DiagCombo1.getValue()) {
                                        DiagCombo1.fireEvent('select', DiagCombo1, record, 0);
                                    }
                                });
                            }
                        });
                       DiagCombo1.setValue(ds1);
                       this.loadUslList(); 					   					   					   
						
						//Обновляем параметры для дерева						
						var tree = this.specificsTree;						
						tree.getLoader().baseParams.PersonRegister_id = PersonRegister_id;
						tree.getLoader().baseParams.Status = statustree;
						console.log('STATUSTREE='+statustree);
						//Рисуем дерево только в случае наличи явзаимосвязи с регистром беременных							
						if (statustree != 1){
							
							this.specificsPanel.expand();
							tree.getLoader().baseParams.PersonRegisterEco_id = form.sluchFrameGrid.getGrid().getSelectionModel().getSelections()[0].data.Eco_id;
							tree.getLoader().baseParams.PersonRegister_id = PersonRegister_id;
							tree.getLoader().baseParams.Person_id = Person_id;
							tree.getLoader().load(tree.getRootNode());
							tree.fireEvent('click', tree.getRootNode());
							tree.setWidth(300);
							//this.specificsPanel.setHeight(1000);
						}else{
							tree.getLoader().baseParams.PersonRegisterEco_id = 0;
							var nn = tree.getRootNode().childNodes.length;
							for (j = 0; j < nn; j++)
							{
								tree.getRootNode().childNodes[0].remove(true);
							};
							if (typeof this.WizardPanel != 'undefined'){
								this.WizardPanel.hide();
								this.WizardPanel.resetCurrentCategory();
							}							
							this.specificsPanel.setHeight(220);
							this.specificsPanel.collapse();
							this.specificsFormsPanel.doLayout();														
						}						
                    }.createDelegate(this)
                });    
            } 			
			
        }else{
			//случаев ЭКО нет
			//Обновляем параметры для дерева						
			var tree = this.specificsTree;						
			tree.getLoader().baseParams.PersonRegister_id = '';
			tree.getLoader().baseParams.Status = 1;
			tree.getLoader().baseParams.PersonRegisterEco_id = 0;
			var nn = tree.getRootNode().childNodes.length;
			for (j = 0; j < nn; j++)
			{
				tree.getRootNode().childNodes[0].remove(true);
			};
			if (typeof this.WizardPanel != 'undefined'){
				this.WizardPanel.hide();
				this.WizardPanel.resetCurrentCategory();
			}							
			this.specificsPanel.setHeight(220);
			this.specificsPanel.collapse();
			this.specificsFormsPanel.doLayout();			
		}
        
        if (form.sluchFrameGrid.getGrid().getStore().data.items[0].data.Eco_id==null){
            this.onReadOnly('add',true);      
        }    

    },
	
    //Проверка на одинаковые даты при сохранении случая эко	
    checkEqualSluch: function(date, eco_id){
        var grid = Ext.getCmp('ecoNumber').getGrid();
        var nRecord = grid.store.getCount();
        var resp = false;
        
        if (eco_id==0){
            if (grid.getStore().data.items[0].data.Eco_id){
                for (var i =0; i < nRecord; i++){
                    if (grid.store.data.items[i].data.DateAdd <= new Date(date+'T00:00:00') && new Date(date+'T00:00:00') <= grid.store.data.items[i].data.PersonRegisterEco_ResultDate)
                    {
                        resp = true;
                    }
                }
            }
        } 
        return resp;
    },
	checkResultDateSluch: function(dateadd, dateresult, eco_id){
		
        var grid = Ext.getCmp('ecoNumber').getGrid();
        var nRecord = grid.store.getCount();
        var resp = false;
		
		if (eco_id == 0){ 
			dateresult = '01.01.2040';
		}else{
			var prevResultDate = grid.store.getById(eco_id).data.PersonRegisterEco_ResultDate;
			
			//Анализ дерева
			var tree = this.specificsTree;
			
			
			
			if (prevResultDate != '' && dateresult == ''){
				if (tree.getLoader().baseParams.PersonRegisterEco_id  != 0){
					if (Ext.getCmp('swECORegistryEditWindow_SpecificsTree').root.firstChild.text.indexOf('Создать') == -1){
						return true;
					}
				}
			}
		}
		
		//интервал [дата добавления и результата одного случая] не должен пересекаться с другими случаями
		for (var i =0; i < nRecord; i++){
			if (grid.getStore().data.items[i].data.Eco_id != eco_id){
				item_DateAdd = grid.store.data.items[i].data.DateAdd;
				item_ResultDate = grid.store.data.items[i].data.PersonRegisterEco_ResultDate;
				if (item_ResultDate == "") item_ResultDate = grid.store.data.items[i].data.DateAdd;

				if (!((item_DateAdd < new Date(dateadd+'T00:00:00') && item_ResultDate < new Date(dateadd+'T00:00:00')) || (item_DateAdd > new Date(dateresult+'T00:00:00') && item_ResultDate > new Date(dateresult+'T00:00:00'))))
				{
					resp = true;
				}
			}
		}

        return resp;
    },
    
    //Проверка на пересечение случаев
    checkCrossing: function(){
		
        var form = this;
        var data_form = form.svedPanel.getForm();
        
        if (!data_form.isValid()) {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    fn: function () {
                        //
                    }.createDelegate(this),
                    icon: Ext.Msg.WARNING,
                    msg: 'Заполните все обязательные поля',
                    title: ERR_INVFIELDS_TIT
            });
            
            return false;
        }
        
        
        var eco_id = Ext.getCmp('Eco_id').getValue();
        
        if (eco_id==0 & Ext.getCmp('ecoNumber').getGrid().store.data.items[0].data.Eco_id!=null) {            
            Ext.Ajax.request({ 
                url: '/?c=Eco&m=checkCrossingSluch', 
                params: { 
                Person_id: Ext.getCmp('Pers_id').getValue()
                },

                success: function(result){
                    var resp_obj = Ext.util.JSON.decode(result.responseText);
					if (!resp_obj.success){
						this.addEco();
						return true;
					}
						
                    var strDateMax = resp_obj.ResDate;
                    var strDate =  Ext.util.Format.date(Ext.getCmp('Date_add').getValue(), 'd.m.Y');
                    var dateMax = new Date(strDateMax.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1')).format('d.m.Y');
                    var date  = new Date(strDate.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1')).format('d.m.Y');


                    if (dateMax>=date){
                        sw.swMsg.show(
                        {
                            buttons: Ext.Msg.OK,
                            icon: Ext.Msg.WARNING, //icon: Ext.Msg.INFO,//
                            msg: 'Сохранение невозможно, имеется пересечение случаев с применением ВРТ.',
                            title: 'Пересечение случаев с применением ВРТ'
                        });
                        return false;
                    }
                    this.addEco();
                }.createDelegate(this)
            });
            
        } else{        
            this.addEco();
        }
        
    },

    //добавление/обновление случая эко
    addEco: function () {
				
        var form = this;
        var data_form = form.svedPanel.getForm();					
		var usl_store = form.uslFrameGrid.getGrid().store;
		var setResDate = data_form.findField('res_date').getValue();
		var novalidResDate = false;
		
		if (setResDate != ''){
			usl_store.each(function(record,index){
				var separDateUsl = record.get('DateUslStr').split('.');						
				if (setResDate <  new Date(separDateUsl[2],separDateUsl[1]-1, separDateUsl[0])){
					sw.swMsg.show({
						buttons: Ext.Msg.OK,	
						msg: 'Дата результата случая с применением ВРТ не должна быть ранее даты выполнения услуги',
						title: 'Ошибка'
					});
					
					novalidResDate = true;
					return false;
				}
			});		
		}
		
		if (novalidResDate){
			return false;
		}		

        var nRecord = Ext.getCmp('oslGrid').getGrid().store.getCount();

        if (nRecord>0){
            if (!Ext.getCmp('oslGrid').getGrid().getStore().data.items[0].data.Ds_int){    
                Ext.getCmp('oslGrid').getGrid().store.removeAt(0);
                nRecord = nRecord - 1;
            }
        }  
        
        var pers_id = data_form.findField('pers_id').getValue();
        var dateAdd = Ext.util.Format.date(data_form.findField('date_add').getValue(), 'Y-m-d');		
        var vid_oplod = data_form.findField('vid_oplod').getValue();
        var vid_oplat = data_form.findField('PayType_id').getValue();
        var gen_diag = data_form.findField('gen_diag').getValue();
        var count_embrion = data_form.findField('EmbrionCount_id').getValue();
        var res_eco = data_form.findField('EcoResultType_id').getValue();
        var ds_eco = Ext.getCmp('Ds_eco').getValue();  
        var res_date = Ext.util.Format.date(data_form.findField('res_date').getValue(), 'Y-m-d');
        var user_id = getGlobalOptions().pmuser_id;
        var eco_id = data_form.findField('eco_id').getValue();
        var ds1 = Ext.getCmp('ds1').getValue(); 
        var vidBer = data_form.findField('EcoPregnancyType_id').getValue(); 
        var countPlod = data_form.findField('EcoChildCountType_id').getValue(); 
        var lpu = getGlobalOptions().lpu_id;
		var dateResult = Ext.util.Format.date(data_form.findField('res_date').getValue(), 'Y-m-d');
		var MedPersonal_sid = data_form.findField('MedPersonal_sid').getValue();

        var grid = Ext.getCmp('oslGrid').getGrid()
        var arr = new Array();

        for (var i =0; i < nRecord; i++){
            var obj = new Object();

            obj.osl = grid.getStore().data.items[i].data.Osl;
            obj.oslId =  parseInt(grid.getStore().data.items[i].data.Osl_id);
            obj.date = grid.getStore().data.items[i].data.Date_osl;
            obj.dsInt =  parseInt(grid.getStore().data.items[i].data.Ds_int);
            obj.ds = grid.getStore().data.items[i].data.Ds;

            arr.push(obj);
        }

        var diag_combo = Ext.getCmp('Ds_eco');
        var indexStore = diag_combo.getStore().findBy(function(rec) { return rec.get('Diag_id') == ds_eco; });
        var Store = diag_combo.getStore();
        var rec = Store.getAt(indexStore);

        if (typeof rec == 'object')
        {
            var EventCode = rec.get('Diag_Code');
            var EventName = rec.get('Diag_Name');
        }

        if (this.checkEqualSluch(dateAdd,Ext.getCmp('Eco_id').getValue())){
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                msg: 'У данного пациента уже существуют запись в регистре ВРТ в указанный период',
                title: lang['podtverjdenie']
            });
		}else if (this.checkResultDateSluch(dateAdd,dateResult,Ext.getCmp('Eco_id').getValue())){
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
				msg: 'У данного пациента уже существуют запись в регистре ВРТ в указанный период.',
                title: lang['podtverjdenie']
            });
        } else{
			
						
			var tree = this.specificsTree;
			if (tree.getLoader().baseParams.Status != 4){
				//Сохранение исхода

				if (form.WizardPanel && !form.WizardPanel.hidden) {
					var cancel = false;																		
					form.WizardPanel.categories.each(function(category){
						var categoryData = category.getCategoryData(category);
						//categoryData.BirthSpecStac_id = "";
						if (category.idField == "BirthSpecStac_id"){
							var base_form = category.getForm();						
							var categoryData = category.getCategoryFormData(category, true);
							categoryData.BirthSpecStac_id = "";
							base_form.setValues(categoryData);
						}

						if (categoryData && categoryData.status != 3 && category.saveCategory(category) === false){
							cancel = true;
							return false;
						}
					});
					if (cancel) {
						this.formStatus = 'edit';
						return false;
					}
				}

				if (form.WizardPanel) {
					form.WizardPanel.setReadOnly(false);
				}
			}

			//сохранение случая ЭКО			

			Ext.Ajax.request({
				url: '/?c=Eco&m=ecoChange',
				params: {
					s_eco_id: eco_id,
					s_pers_id: pers_id,
					s_dateAdd :dateAdd,
					s_vid_oplod: vid_oplod,
					s_vid_oplat: vid_oplat,
					s_gen_diag :gen_diag,
					s_count_embrion: count_embrion,
					s_res_eco: res_eco,
					s_res_date: res_date,
					s_ds_eco: ds_eco,
					dsOsn: ds1,
					vidBer: vidBer,
					countPlod: countPlod,
					s_oslognen: Ext.util.JSON.encode(arr),
					s_pmUser: user_id,
					lpu:lpu,
					MedPersonal_sid:MedPersonal_sid
				},
				success: function(result){
					var form = Ext.getCmp('swECORegistryEditWindow');
					Ext.getCmp('Date_add').setDisabled(true);
					form.loadSluchList(true);					
				} 
			});  			              
        };
    },
    
    //событие на выбор элемента комборя результат эко
    onSelectRezCombo: function(combo, record, index){
        //var id = record.data.id;
        var id = typeof record.data.EcoResultType_Code == 'undefined' ? record.data.id : record.data.EcoResultType_Code;		
		
         if (id){
                Ext.getCmp('Ds_eco').allowBlank = false;
                Ext.getCmp('Res_date').allowBlank = false;
                Ext.getCmp('Res_date').validate();                      
                Ext.getCmp('Ds_eco').validate();
                
                if (id==1){
                    Ext.getCmp('vid_ber').getEl().up('.x-form-item').setDisplayed(true);
                    Ext.getCmp('vid_ber').allowBlank = false;                    
                    Ext.getCmp('vid_ber').validate();
                }
                else if (id==7){
                    Ext.getCmp('Ds_eco').allowBlank = true;
                    Ext.getCmp('Ds_eco').validate();
					Ext.getCmp('count_plod').allowBlank = true;
					Ext.getCmp('count_plod').validate();
					Ext.getCmp('count_plod').setValue();
					Ext.getCmp('vid_ber').allowBlank = true;
					Ext.getCmp('vid_ber').validate();
					Ext.getCmp('vid_ber').setValue();								
					
					Ext.getCmp('vid_ber').getEl().up('.x-form-item').setDisplayed(false);
					Ext.getCmp('count_plod').getEl().up('.x-form-item').setDisplayed(false);
                }
                else{
                    Ext.getCmp('vid_ber').getEl().up('.x-form-item').setDisplayed(false);
                    Ext.getCmp('vid_ber').allowBlank = true;
                    Ext.getCmp('vid_ber').validate();
                    Ext.getCmp('vid_ber').setValue();
                    
                    Ext.getCmp('count_plod').getEl().up('.x-form-item').setDisplayed(false);
                    Ext.getCmp('count_plod').allowBlank = true;
                    Ext.getCmp('count_plod').validate();
                    Ext.getCmp('count_plod').setValue();
                }
                
            }
            else{
                Ext.getCmp('Ds_eco').allowBlank = true;
                Ext.getCmp('Ds_eco').validate();
                Ext.getCmp('Ds_eco').setValue();

                Ext.getCmp('Res_date').allowBlank = true;
                Ext.getCmp('Res_date').validate();
                Ext.getCmp('Res_date').setValue();
                
                Ext.getCmp('vid_ber').getEl().up('.x-form-item').setDisplayed(false);
                Ext.getCmp('vid_ber').allowBlank = true;
                Ext.getCmp('vid_ber').validate();
                Ext.getCmp('vid_ber').setValue();
                
                Ext.getCmp('count_plod').getEl().up('.x-form-item').setDisplayed(false);
                Ext.getCmp('count_plod').allowBlank = true;
                Ext.getCmp('count_plod').validate();
                Ext.getCmp('count_plod').setValue();

            }
    },
    
    //событие на выбор элемента комборя вид беременности
    onSelectVidBerCombo: function (combo, record, index){
        var id = typeof record.data.EcoPregnancyType_Code == 'undefined' ? record.data.id : record.data.EcoPregnancyType_Code;
        if (id==1){
            Ext.getCmp('count_plod').getEl().up('.x-form-item').setDisplayed(true);
            Ext.getCmp('count_plod').allowBlank = false;
            Ext.getCmp('count_plod').validate();            
        }
        else{
            Ext.getCmp('count_plod').getEl().up('.x-form-item').setDisplayed(false);
            Ext.getCmp('count_plod').allowBlank = true;
            Ext.getCmp('count_plod').validate();
            Ext.getCmp('count_plod').setValue();
        }
    
    },
    
    //печать грида услуг
    schedulePrintUsl:function(action){
        var record = this.uslFrameGrid.getGrid().getSelectionModel().getSelected();

        if (!record) {
            sw.swMsg.alert(langs('Ошибка'), langs('Запись не выбрана'));
            return false;
        }

        if (action && action == 'row') {
            Ext.ux.GridPrinter.print(this.uslFrameGrid.getGrid(), {rowId: record.id});
        } else {
            Ext.ux.GridPrinter.print(this.uslFrameGrid.getGrid());
        }
    },
    
    //печать грида осложнений
    schedulePrintOsl:function(action){
        var record = this.oslFrameGrid.getGrid().getSelectionModel().getSelected();

        if (!record) {
            sw.swMsg.alert(langs('Ошибка'), langs('Запись не выбрана'));
            return false;
        }

        if (action && action == 'row') {
            Ext.ux.GridPrinter.print(this.oslFrameGrid.getGrid(), {rowId: record.id});
        } else {
            Ext.ux.GridPrinter.print(this.oslFrameGrid.getGrid());
        }
    },
    
    //печать грида уточненных диагнозов
    schedulePrintDs:function(action){
        var record = this.diagsPanel.getGrid().getSelectionModel().getSelected();

        if (!record) {
            sw.swMsg.alert(langs('Ошибка'), langs('Запись не выбрана'));
            return false;
        }

        if (action && action == 'row') {
            Ext.ux.GridPrinter.print(this.diagsPanel.getGrid(), {rowId: record.id});
        } else {
            Ext.ux.GridPrinter.print(this.diagsPanel.getGrid());
        }
    },
	
	//
	recalcBirthSpecStacDefaults: function() {
		
		
		if (this.WizardPanel) {
			var category = this.WizardPanel.getCategory('Result');
			var cat_form = category.getForm();
			//ТЕСТ
			//if (category.loaded) {
			if (true){
				cat_form.findField('MedPersonal_oid').reset();

				//ТЕСТ
				var values = Ext.apply(cat_form.getValues(), this.getBirthSpecStacDefaults());
				cat_form.setValues(values);
			}
		}
	},	
	
	getBirthSpecStacDefaults: function() {
		
		
		//var base_form = this.findById('swECORegistryEditWindow').getForm();
		var cat_form = this.WizardPanel.getCategory('Result').getForm();
		var oldValues = cat_form.getValues();
		//Параметры исхода, которые можно рассчитать на клиенте
		var values = {
			Lpu_oid: getGlobalOptions().lpu_id,
			//MedPersonal_oid: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'),
			MedPersonal_oid: getGlobalOptions().CurMedStaffFact_id,
			AbortLpuPlaceType_id: Ext.isEmpty(oldValues.AbortLpuPlaceType_id)?oldValues.AbortLpuPlaceType_id:2,
			BirthCharactType_id: Ext.isEmpty(oldValues.BirthCharactType_id)?oldValues.BirthCharactType_id:1,
			QuestionType_521: oldValues.QuestionType_521,
			QuestionType_522: oldValues.QuestionType_522,
			QuestionType_523: oldValues.QuestionType_523,
			QuestionType_532: oldValues.QuestionType_532,
			QuestionType_540: oldValues.QuestionType_540,
			QuestionType_541: oldValues.QuestionType_541
		};

		return values;
	},	
	
	
	/*treeart для дерева, функция опредения PersonRegister_id*/
	getPregnancyPersonRegister: function(callback) {
		
		callback = callback || Ext.emptyFn;
		
		this.PersonRegister_id = 1058;
		callback();
	},
	createPersonPregnancyCategory: function(categoryName) {
		
		
		if (!this.WizardPanel) {
			this.createPersonPregnancyWizardPanel();
		}
		this.WizardPanel.resetCurrentCategory(true);
		//this.specificsPanel.setHeight(996+5+26);
		Ext.getCmp("swECORegistryEditWindow").specificsPanel.setHeight(1027);
		this.WizardPanel.hide();
		this.WizardPanel.show();
		this.WizardPanel.createCategoryController(categoryName);
		
		
	},
	deletePersonPregnancyCategory: function(categoryName, id) {
		
		
		if (!this.WizardPanel) {
			this.createPersonPregnancyWizardPanel();
		}
		this.WizardPanel.deleteCategoryController(categoryName, id);
	},	
	resizeSpecificForWizardPanel: function() {
		
		
		if (!this.WizardPanel || !this.WizardPanel.isVisible() || this.WizardPanelResizing) {
			return;
		}

		this.WizardPanelResizing = true;
		var defaultHeight = 220;
		var page = this.WizardPanel.getCurrentPage();

		if (page) {
			this.WizardPanel.show();
			if (page instanceof sw.Promed.ViewFrame) {
				var height = defaultHeight;

				page.setHeight(height-36);
				this.WizardPanel.setHeight(height-36);
				this.specificsPanel.setHeight(height);
				page.doLayout();
			} else {
				var height = 0;
				page.items.each(function(item) {
					if (item.hidden) return;
					var el = item.getEl();
					var margins = el.getMargins();
					height += el.getHeight() + margins.top + margins.bottom;
				});
				height += 38;
				if (height <= defaultHeight) {
					height = defaultHeight;
				}
				if (this.WizardPanel.DataToolbar.isVisible()) {
					this.specificsPanel.setHeight(height+5+26);					
				} else {
					this.specificsPanel.setHeight(height+5);
				}
				//this.WizardPanel.setHeight(height);
				this.WizardPanel.setHeight(1584);
				page.doLayout();
			}
		} else {
			this.WizardPanel.hide();
			this.specificsPanel.setHeight(defaultHeight);
		}
		this.WizardPanelResizing = false;
	},	
	createPersonPregnancyWizardPanel: function() {
		
		var wnd = this;
		var tree = this.specificsTree;		
		var personInfoPanel = Ext.getCmp('ECO_PersonInfoFramea');		
		
		var inputData = new sw.Promed.PersonPregnancy.InputData({
			fn: function() {
				var wnd = Ext.getCmp('swECORegistryEditWindow');
				return {
					Person_id: wnd.Person_id,
					PersonRegister_id: wnd.PersonRegister_id,
					Evn_id: '',
					Server_id: 35,
					Lpu_id: getGlobalOptions().lpu_id,
					LpuSection_id: 4458,
					MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id,
					MedPersonal_id: getGlobalOptions().CurMedStaffFact_id,
					userMedStaffFact: wnd.userMedStaffFact
				};
			}
		});
		var afterPregnancyResultChange = function(options) {
			
			
			if (options && options.resize) {
				wnd.resizeSpecificForWizardPanel();
			}
			if (options && options.recalc) {
				wnd.recalcBirthSpecStacDefaults();
			}
		};
		var beforeChildAdd = function(objectToReturn, addFn) {
			
			var category = wnd.WizardPanel.getCategory('Result');
			var categoryData = category.getCategoryData(category);
			if (categoryData && (categoryData.status.inlist([-1, 0]) || Ext.isEmpty(categoryData.EvnSection_id))) {
				//Перед добавлением новорожденного происходит сохранение движения
				//с измененными данными по беременности, если исход беременности ещё не был сохранен
				category.saveCategory(category, function() {
					wnd.doSave({silent: addFn});
				});
				return false;
			}
			return true;
		};
		var wizardValidator = function() {
			
			var valid = true;
			wnd.WizardPanel.categories.each(function(category){
				if (category.loaded && category.validateCategory(category, true) === false) {
					valid = false;
					return false;
				}
			});
			return valid;
		};
		var afterPageChange = function() {
			
			wnd.resizeSpecificForWizardPanel();

			var category = wnd.WizardPanel.getCurrentCategory();

			if (category) {
				var values = category.getForm().getValues();
				wnd.PersonRegister_id = values.PersonRegister_id;			
			}
		};

		var updateScreenNode = function(categoryData) {
			
			var nodeId = 'PregnancyScreen_'+categoryData.PregnancyScreen_id;
			var text = new Ext.Template('{date}, {period} нед., Пер. риск {risk}').apply({
				date: categoryData.PregnancyScreen_setDate,
				period: categoryData.amenordate || categoryData.embriondate || categoryData.uzidate || categoryData.fmovedate || '*',
				risk: '*'
			});

			switch(categoryData.status) {
				case 0: text += ' <span class="status created">Новый</span>';break;
				case 2: text += ' <span class="status updated">Изменен</span>';break;
				case 3: text += ' <span class="status deleted">Удален</span>';break;
			}

			var tplDelete = new Ext.Template('<span class="link delete" onclick="{method}(\'{categoryName}\', {id})">Удалить</span>');
			if (categoryData.status.inlist([0,1,2])) {
				text += tplDelete.apply({
					id: categoryData.PregnancyScreen_id,
					categoryName: 'Screen',
					method: "Ext.getCmp('"+wnd.getId()+"').deletePersonPregnancyCategory"
				});
			}

			var screenListNode = tree.nodeHash.ScreenList;
			var screenNode = screenListNode.findChild('id', nodeId);

			if (screenNode) {
				screenNode.attributes.date = categoryData.PregnancyScreen_setDate;
				screenNode.setText(text);
			} else {
				screenListNode.leaf = false;
				screenNode = screenListNode.appendChild({
					id: nodeId,
					object: 'Screen',
					value: 'PersonPregnancy',
					key: categoryData.PregnancyScreen_id,
					date: categoryData.PregnancyScreen_setDate,
					text: text,
					leaf: true
				});
				screenListNode.expand();
			}

			screenListNode.sort(function(node1, node2) {
				return Date.parseDate(node1.attributes.date, 'd.m.Y') > Date.parseDate(node2.attributes.date, 'd.m.Y');
			});

			tree.getSelectionModel().select(screenNode);
		};

		var updateCategoryNode = function(category, id, action) {
			
			var categoryData = category.getCategoryData(category, id);

			if (category.name == 'Screen') {
				updateScreenNode(categoryData);
			} else {
				var node = tree.nodeHash[category.name];
				if (node) {
					if (action == 'delete') {
						node.attributes.key = null;
						node.attributes.readOnly = true;
						node.attributes.deleted = true;
					} else {
						node.attributes.key = id;
						if (id < 0) {
							node.attributes.readOnly = false;
						}
						delete node.attributes.deleted;
					}
					node.attributes.key = (action == 'delete')?null:id;

					var textEl = Ext.get(node.ui.elNode).child('.x-tree-node-anchor').child('span');

					if (textEl.child('.status')) {
						textEl.child('.status').remove();
					}
					if (textEl.child('.link')) {
						textEl.child('.link').remove();
					}

					switch(categoryData && categoryData.status) {
						case 0: textEl.createChild('<span class="status created">Новый</span>');break;
						case 2: textEl.createChild('<span class="status updated">Изменен</span>');break;
						case 3: textEl.createChild('<span class="status deleted">Удален</span>');break;
					}

					var tplCreate = new Ext.Template('<span class="link create" onclick="{method}(\'{categoryName}\')">Создать</span>');
					if (!categoryData) {
						textEl.createChild(tplCreate.apply({
							categoryName: category.name,
							method: "Ext.getCmp('"+wnd.getId()+"').createPersonPregnancyCategory"
						}));
					}

					var tplDelete = new Ext.Template('<span class="link delete" onclick="{method}(\'{categoryName}\', {id})">Удалить</span>');
					if (categoryData && categoryData.status.inlist([0,1,2])) {
						textEl.createChild(tplDelete.apply({
							id: node.attributes.key,
							categoryName: category.name,
							method: "Ext.getCmp('"+wnd.getId()+"').deletePersonPregnancyCategory"
						}));
					}
				}
			}
		};

		var saveCategory = function(category, callback) {			
			
			var params = category.getCategoryFormData(category, true);
			
			var tree = Ext.getCmp("swECORegistryEditWindow_SpecificsTree");
			params['Status'] = tree.getLoader().baseParams.Status;
			params['Eco_id'] = Ext.getCmp("swECORegistryEditWindow").sluchFrameGrid.getGrid().getStore().data.items[0].data.Eco_id;
			
			
			Ext.Ajax.request({
				params: params,
				url: '/?c=Eco&m=saveBirthSpecStac',
				success: function(response) {
					
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.Error_Msg == 'YesNo') {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (response_obj.Error_Code == 201) {
										category.ignoreCheckBirthSpecStacDate = 1;
									}
									if (response_obj.Error_Code == 202) {
										category.ignoreCheckChildrenCount = 1;
									}

									category.saveCategory(category);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: response_obj.Alert_Msg,
							title: 'Продолжить сохранение?'
						});
					} else if (response_obj.success) {
						
						category.BirthSpecStac_id = response_obj.BirthSpecStac_id;						
					}
				},
				failure: function(response) {
					
					//loadMask.hide();
				}
			});			
			
		};

		var afterSaveCategory = function(category) {
			
		};

		var beforeDeleteCategory = function(category, id) {
			
			if (category.name == 'Result') {
				if (sw.Promed.PersonPregnancy.ResultCategory.prototype.beforeDeleteCategory.apply(category, arguments) === false) {
					return false;
				}

				if (!category.allowDelete && id > 0) {
					var loadMask = wnd.WizardPanel.getLoadMask({msg: "Проверка возможности удаляения исхода..."});
					loadMask.show();
					Ext.Ajax.request({
						url: '/?c=PersonPregnancy&m=beforeDeleteBirthSpecStac',
						params: {BirthSpecStac_id: id},
						success: function(response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success) {
								category.allowDelete = true;
								category.deleteCategory(category, id);
							}
						},
						failure: function() {
							loadMask.hide();
						}
					});
					return false;
				}
			}
		};

		var deleteCategory = function(category, id) {
			
			var deleteCategory = function() {
				if (category.beforeDeleteCategory(category, id) === false) {
					return false;
				}

				var categoryData = category.getCategoryData(category, id);
				if (categoryData) {
					if (categoryData.status == 0) {
						category.removeCategoryData(category, id);
					} else {
						category.setCategoryDataValue(category, 'status', 3);
					}
				} else if(id > 0) {
					var conf = {status: 3, loaded: false};
					conf[category.idField] = id;

					category.data.add(id, conf);
				}

				delete category.wantDelete;
				delete category.allowDelete;
				category.afterDeleteCategory(category, id);
			};

			if (category.wantDelete) {
				deleteCategory();
			} else {
				sw.swMsg.show({
					buttons:Ext.Msg.YESNO,
					fn:function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							category.wantDelete = true;
							deleteCategory();
						}
					}.createDelegate(this),
					icon:Ext.MessageBox.QUESTION,
					msg:langs('Вы хотите удалить запись?'),
					title:langs('Подтверждение')
				});
			}
		};

		var afterDeleteCategory = function(category, id) {
			
			switch(category.name) {
				case 'Screen':
					var parentNode = tree.nodeHash.ScreenList;
					var node = parentNode.findChild('key', id);

					parentNode.removeChild(node);
					break;
				case 'Anketa':
					updateCategoryNode(category, id, 'delete');

					var anketaNode = tree.nodeHash.Anketa;
					while(anketaNode.childNodes.length != 0) {
						anketaNode.removeChild(anketaNode.childNodes[anketaNode.childNodes.length-1]);
					}
					anketaNode.leaf = true;
					anketaNode.ui.updateExpandIcon();
					break;
				default:
					updateCategoryNode(category, id, 'delete');
					break;
			}

			if (wnd.WizardPanel.getCurrentCategory() == category) {
				wnd.WizardPanel.resetCurrentCategory();
				wnd.WizardPanel.hide();
				wnd.specificsPanel.setHeight(220);
			}
		};

		var cancelCategory = function(category, onCancel) {
			onCancel();
		};

		
		wnd.WizardPanel = new sw.Promed.PersonPregnancy.WizardFrame({
			
			//id: 'ESEW_PersonPregnancyWizard',
			id: 'ECO_PersonPregnancyWizard',
			maskEl: wnd.specificsPanel.getEl(),
			readOnly: wnd.action == 'view',
			inputData: inputData,
			isValid: wizardValidator,
			afterPageChange: afterPageChange,
			//saveCategory: saveCategory,
			//afterSaveCategory: afterSaveCategory,
			beforeDeleteCategory: beforeDeleteCategory,
			//deleteCategory: deleteCategory,
			afterDeleteCategory: afterDeleteCategory,
			cancelCategory: cancelCategory,
			allowCollectData: true,
			categories: [
				new sw.Promed.PersonPregnancy.ResultCategory({
					saveCategory: saveCategory,
					allowSaveButton: false
				}),
				new sw.Promed.PersonPregnancy.DeathMotherCategory({
					readOnly: true
				})
			]
		});

		wnd.specificsFormsPanel.add(wnd.WizardPanel);
		wnd.specificsFormsPanel.doLayout();
		wnd.WizardPanel.init();
		wnd.WizardPanel.PrintResultButton = Ext.getCmp('ESEW_PrintPregnancyResultButton');
	},	
	
	

    initComponent: function() {
        var wnd = this;

        //Панель с перс данными
        this.PersonInfoPanelEco  =new sw.Promed.PersonInfoPanel({
            floatable: false,
            collapsed: true,
            region: 'north',
            title: lang['zagruzka'],
            plugins: [ Ext.ux.PanelCollapsedTitle ],
            titleCollapse: true,
            collapsible: true,
            id: 'ECO_PersonInfoFramea'
        });   

        //Панель с уточненными диагнозами
        this.diagsPanel = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            dataUrl: '/?c=Eco&m=getDiagList',
            id: 'diagsFrame',
            //height: 700,
            //layout: 'fit',
            bodyBorder: true,
            pageSize: 100,
            region: 'center',
            contextmenu: false,
            paging: false,
            toolbar: false,
            object: 'diagsFrame',
            //root: 'data',
            stringfields: [
                {name: 'id', header: 'ИД', width: 100, hidden: true},
                {name: 'Diag_setDate', header: 'Дата установки', width: 95, type:'date'},
                {name: 'Diag_Code', header: 'Шифр МКБ', width: 70},
                {name: 'Diag_Name', header: 'Диагноз', autoexpand:true},
                {name: 'Lpu_Nick', header: 'ЛПУ', width: 120},
                {name: 'LpuSectionProfile_Name', header: 'Профиль', width: 325},
                {name: 'MedPersonal_Fio', header: 'Врач', width: 270}
            ]
        });
       

        //Грид с датами случаев ЭКО
        this.sluchFrameGrid = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 110,
            autoLoadData: false,
            dataUrl: '/?c=Eco&m=loadEcoSluch',
            id: 'ecoNumber',
            //height: 400,   
            region: 'center',
            pageSize: 100,
            contextmenu: false,
            //useEmptyRecord: false,
            paging: false,
            toolbar: false,
            border: false, 
            stringfields: [
                {name: 'Eco_id', header: 'ИД', width: 100},
                {name: 'DateAdd', header: 'Дата включения', width: 95, type:'date'},
				{name: 'lpu_nick', header: 'МО', width: 190, type:'string'},
				{name: 'PersonRegisterEco_ResultDate', header: 'Дата результата', width: 95, type:'date', hidden:true},
				{name: 'EcoResultType_Name', header: 'Результат ВРТ', width: 190, type:'string', hidden:true},
				{name: 'lpu_id', header: 'Идентификатор МО', width: 190, type:'string', hidden:true}				
            ],
            onRowSelect: function() {
				
                Ext.getCmp('swECORegistryEditWindow').onClickSl();
            },
            //Если в гриде один случай, нажали кнопку +, то слушателем выше заново открыть случай не получается
            onCellDblClick: function(){
				
                Ext.getCmp('swECORegistryEditWindow').onClickSl();
            }
        });       

        //Грид услуг
        this.uslFrameGrid = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 110,
            dataUrl: '/?c=Eco&m=loadEcoUsl',
            autoLoadData: false,            
            id: 'uslGrid',
            style : 'border: 1px solid #666',
            height: 150,          
            layout: 'fit',
            object: 'ECO_Number',
            pageSize: 100,
            contextmenu: false,
            paging: false,
            toolbar: false,
            stringfields: [
                {name: 'Eco_usl_id', header: 'id_usl',hidden: true, width: 100},
                {name: 'usl_id', hidden: true},
                {name: 'DateUslStr', header: 'Дата выполнения', width: 70},
                {name: 'CodeUsl', header: 'Код услуги',width: 150},
                {name: 'NameUsl', header: 'Наименование',width: 400},//, autoexpand:true},
                {name: 'MO', header: 'МО',width: 150},
                {name: 'DS', header: 'Диагноз', autoexpand:true},
				{name: 'del', header: 'Удаление',hidden: true}
            ]
        });

        //Грид осложнений 
        this.oslFrameGrid = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 110,
            dataUrl: '/?c=Eco&m=loadEcoOsl',            
            autoLoadData: false,
            id: 'oslGrid',
            height: 150, 
            style : 'border: 1px solid #666',
            autoWidth: true,
            pageSize: 100,
            contextmenu: false,
            paging: false,
            toolbar: false, 
            border: true, 
            stringfields: [ 
				{name: 'EcoOsl_id', header: 'id_osl',hidden: true, width: 100},
                {name: 'Ds_int', header: 'ds',hidden: true,type: 'int', width: 100},
                {name: 'Date_osl', header: 'Дата', width: 70},
                {name: 'Osl', header: 'Осложнение', width: 455},
                {name: 'Osl_id', header: 'Осложнение', hidden: true,type: 'int',},
                {name: 'Ds', header: 'Диагноз осложнения',autoexpand:true}
            ]
        });
        
        //панель с Гридом с датами случаев ЭКО
        this.EcoSl = new Ext.Panel({
            collapsible: true,     
            title: 'Случаи с применением ВРТ',
            width : 300,                             
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
            tbar: [{
                xtype: 'button',
                id: 'addObjectButton',         
                text : BTN_GRIDADD,
                iconCls : 'add16',
                hidden: false,
                handler: function()
                {
                    Ext.getCmp('swECORegistryEditWindow').onAddPress();   
                }
                }],
            items : [this.sluchFrameGrid]
        });   
        
        //панель услуги
        this.UslPanel = new Ext.Panel({            
            collapsible: true, 
            title: 'Услуги',
            //layout: 'form',
            layout: 'fit',
            //autoWidth: true,
            style : 'margin: 3px 0px 5px 0px;',
            //сворачивание по клику на заголовок
            listeners: {
                'render': function(panel) {
                    if (panel.header)
                    {
                        panel.header.on({
                            'click': {
                                fn: this.toggleCollapse,
                                scope: panel
                            }
                        });
                    }
                }
            },
            
            border: true,
            tbar: [
                {
                    xtype: 'button',
                    id: 'addObjectButtonUsl',         
                    text : BTN_GRIDADD,
                    iconCls : 'add16',
                    hidden: false,
                    menu: {
                        xtype: 'menu',
                        plain: true,
                        items: [{
                            handler: function() {
                                var params = {};
                                var dat = new Date();
                                var formated_date = dat.format('d.m.Y');
                                
                                params.oper = 1;                                
                                params.pers_id = Ext.getCmp('Pers_id').getValue();
                                params.date = formated_date;
								params.Date_add = Ext.getCmp('Date_add').getValue();
								params.Res_date = Ext.getCmp('Res_date').getValue();								
                                getWnd('swECORegistryAddUsl').show(params);
                            }.createDelegate(this),
                            text: 'Добавить операцию'
                        }, {
                            handler: function() {
                                var params = {};
                                var dat = new Date();
                                var formated_date = dat.format('d.m.Y');
                                params.oper = 0;
                                params.pers_id = Ext.getCmp('Pers_id').getValue();
                                params.date =formated_date;
								params.Date_add = Ext.getCmp('Date_add').getValue();
								params.Res_date = Ext.getCmp('Res_date').getValue();
								params.eco_usl_id = '';
								
								
                                getWnd('swECORegistryAddUsl').show(params);
                            }.createDelegate(this),
                            text: 'Добавить общую услугу'
                        }]
                }
                },
                {
                    xtype: 'button',
                    text : BTN_GRIDEDIT,
                    id: 'viewObjectButtonUsl',
                    iconCls : 'edit16',
                    style : 'margin: 0px 2px 0px 3px;',
                    handler: function(){                        
						if (Ext.getCmp('uslGrid').getGrid().getSelectionModel().getSelections()[0].data.del == 'yes'){													
							var Eco_usl_id =Ext.getCmp('uslGrid').getGrid().getSelectionModel().getSelections()[0].data.Eco_usl_id;
							//Ext.getCmp('swECORegistryAddUsl').onDelUsl(id);         
							var params = {};
							params.eco_usl_id = Eco_usl_id;
							params.pers_id = Ext.getCmp('Pers_id').getValue();
							params.DateUslStr = Ext.getCmp('uslGrid').getGrid().getSelectionModel().getSelections()[0].data.DateUslStr;						
							params.usl_id = Ext.getCmp('uslGrid').getGrid().getSelectionModel().getSelections()[0].data.usl_id;
							params.NameUsl = Ext.getCmp('uslGrid').getGrid().getSelectionModel().getSelections()[0].data.NameUsl;
							params.Date_add = Ext.getCmp('Date_add').getValue();
							params.Res_date = Ext.getCmp('Res_date').getValue();								

							getWnd('swECORegistryAddUsl').show(params);
						}else{
							sw.swMsg.alert(langs('Сообщение'), 'Возможно редактирование только тех услуг, которые добавлены в случае с применением ВРТ.');
							
						}
                    }
                },				
                {
                    xtype: 'button',
                    text : BTN_GRIDDEL,
                    id: 'deleteObjectButtonUsl',
                    iconCls : 'delete16',
                    style : 'margin: 0px 2px 0px 3px;',
                    handler: function(){
						
						if (Ext.getCmp('uslGrid').getGrid().getSelectionModel().getSelections()[0].data.del == 'yes'){
							 sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									if ( buttonId == 'yes' )
									{
										var id =Ext.getCmp('uslGrid').getGrid().getSelectionModel().getSelections()[0].data.Eco_usl_id;
										Ext.getCmp('swECORegistryEditWindow').onDelUsl(id); 
									}
								},
								msg: 'Удалить услугу?',
								title: lang['podtverjdenie']
							});                                     
						}else{			
							sw.swMsg.alert(langs('Сообщение'), 'Возможно удаление только тех услуг, которые добавлены в случае с применением ВРТ.');
						}
                    }
                },
                {
                    xtype: 'button',
                    id: 'printUslBtn',         
                    text : BTN_GRIDPRINT,
                    iconCls : 'print16',
                    hidden: false,
                    /*handler: function() {
                            Ext.ux.GridPrinter.print(this.uslFrameGrid.getGrid());    
                    }.createDelegate(this)*/
                    menu: new Ext.menu.Menu([
						{text: langs('Печать'), handler: function () {this.schedulePrintUsl('row');}.createDelegate(this)},
						{text: langs('Печать всего списка'), handler: function () {this.schedulePrintUsl()}.createDelegate(this)}
					])
                }
            ],
            items: [                              
                this.uslFrameGrid
            ]
        });

        //панель сведений
        this.svedPanel = new Ext.FormPanel({
            title: null,
            //bodySyle:'background:#ffffff;',
            style: 'margin: 5px 10px 0px 0px;',            
            layout: 'form',    
            autoWidth: true,
            id: 'svedPanel',
            labelWidth: 240,
            labelAlign: 'right',
            items: [
                {
                  xtype: 'textfield',
                  id : 'Pers_id',
                  name: 'pers_id',
                  labelSeparator : ':',
                  hidden: true,
                  hideLabel: true,
                  fieldLabel: 'перс ид'
                },
                {
                  xtype: 'textfield',
                  id : 'Eco_id',
                  name: 'eco_id',
                  labelSeparator : ':',
                  hidden: true,
                  hideLabel: true,
                  fieldLabel: 'ВРТ ид'
                },
                {
                    xtype: 'swdatefield',
                    id : 'Date_add',                                          
                    name: 'date_add',
                    width: this.m_width_date,
                    labelSeparator : ':',
                    fieldLabel: 'Дата включения',
                    allowBlank: false
                },
				{
						allowBlank: true,
						xtype: 'swmedpersonalcombo',
						hiddenName: 'MedPersonal_sid',
						fieldLabel: 'Врач',
						width: this.m_width_big
				},				
                {
                    fieldLabel: 'Основной диагноз',
                    name: 'DS1',
                    id : 'ds1',                    
                    allowBlank : false,
                    width: this.m_width_big,
                    xtype: 'swdiagcombo'
                },
                {
                    fieldLabel: 'Вид ВРТ',
                    xtype: 'swbaselocalcombo',
                    id : 'Vid_oplod',
                    name: 'vid_oplod',
                    width: this.m_width_big,
                    editable: false,
                    mode: 'local',
                    displayField: 'name',
                    valueField: 'id',
                    codeField:'selection_code',
                    triggerAction: 'all',
                    allowBlank: false,
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '<font color="red">{selection_code}</font>&nbsp;{name}',
                        '</div></tpl>'
                    ),
                    store: new Ext.data.SimpleStore({
                        autoLoad: true,
                        fields: [{name: 'id', type: 'int'}, 
                                {name:'selection_code', type: 'int'},
                                {name: 'name', type: 'string'}],
                        data: [
                            [1, 1, 'ЭКО'], 
                            [2, 2,'ЭКО/ICSI'],
							[3, 3,'Перенос крио размороженных эмбрионов']
							],
                        key: 'id',
                    })
                },
                {
                    id : 'Vid_oplat',
                    name: 'vid_oplat',
					xtype: 'swcommonsprcombo',
					comboSubject: 'PayType',
					hiddenName: 'PayType_id',
					fieldLabel: 'Вид оплаты',
					width: this.m_width_min
                },
                {
                    fieldLabel: 'Преимплантационная генетическая диагностика',
                    xtype: 'swbaselocalcombo',
                    width: this.m_width_min,
                    id : 'Gen_diag',
                    name: 'gen_diag',
                    editable: false,
                    matchFieldWidth: false,
                    allowBlank: true,
                    mode: 'local',
                    displayField: 'name',
                    valueField: 'id',
                    codeField:'selection_code',
                    triggerAction: 'all',
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '<font color="red">{selection_code}</font>&nbsp;{name}',
                        '</div></tpl>'
                    ),
                    store: new Ext.data.SimpleStore({
                        autoLoad: true,
                        fields: [{name: 'id', type: 'int'}, 
                                {name:'selection_code', type: 'int'},
                                {name: 'name', type: 'string'}],
                        key: 'id',
                        data: [
                            [1, 1, 'Да'], 
                            [2, 2, 'Нет']							
							]
                    })
                },
                {

					id : 'Count_embrion',
					xtype: 'swcommonsprcombo',
					comboSubject: 'EmbrionCount',
					hiddenName: 'EmbrionCount_id',
					fieldLabel: 'Количество перенесенных эмбрионов',
					width: this.m_width_min

                },                              
                {
                    xtype: 'panel',
                    title: 'Осложнения',                    
                    //style : 'margin: 3px 0px 5px 0px; border: 1px solid #666',
                    style : 'margin: 3px 0px 5px 0px;',
                    //сворачивание по клику на заголовок
                    listeners: {
                        'render': function(panel) {
                            if (panel.header)
                            {
                                panel.header.on({
                                    'click': {
                                        fn: this.toggleCollapse,
                                        scope: panel
                                    }
                                });
                            }
                        }
                    },
                    collapsible: true,
                    tbar: [
                    {
                        xtype: 'button',
                        id: 'addObjectButtonOsl',         
                        text : BTN_GRIDADD,
                        iconCls : 'add16',
                        hidden: false,
                        handler:  function(){
                                var params = {};
                                var dat = new Date();
                                var formated_date = dat.format('d.m.Y');

                                params.date = formated_date;
                                getWnd('swECORegistryAddOsl').show(params);
                        }           
                    },
					{
						xtype: 'button',
						text : BTN_GRIDEDIT,
						id: 'viewObjectButtonOsl',
						iconCls : 'edit16',
						style : 'margin: 0px 2px 0px 3px;',
						handler: function(){                        
							var EcoOsl_id =Ext.getCmp('oslGrid').getGrid().getSelectionModel().getSelections()[0].data.EcoOsl_id;
							//Ext.getCmp('swECORegistryAddUsl').onDelUsl(id);         
							var params = {};
							params.EcoOsl_id = EcoOsl_id;
							params.pers_id = Ext.getCmp('oslGrid').getGrid().getSelectionModel().getSelections()[0].data.pers_id;
							params.DateOslStr = Ext.getCmp('oslGrid').getGrid().getSelectionModel().getSelections()[0].data.Date_osl;						
							params.osl_id = Ext.getCmp('oslGrid').getGrid().getSelectionModel().getSelections()[0].data.Osl_id;
							params.ds_int = Ext.getCmp('oslGrid').getGrid().getSelectionModel().getSelections()[0].data.Ds_int;
							params.Action = 'Edit';

							
							getWnd('swECORegistryAddOsl').show(params);
						}
					},					
                    {
                        xtype: 'button',
                        text : BTN_GRIDDEL,
                        id: 'deleteObjectButtonOsl',   
                        iconCls : 'delete16',
                        style : 'margin: 0px 2px 0px 3px;',
                        handler: function(){
                             sw.swMsg.show({
                                buttons: Ext.Msg.YESNO,
                                fn: function ( buttonId ) {
                                    if ( buttonId == 'yes' )
                                    {
                                        Ext.getCmp('swECORegistryEditWindow').onDelOsl();
                                    }
                                },
                                msg: 'Удалить осложнение?',
                                title: lang['podtverjdenie']
                            });                                     
                        }
                    },
                    {
                        xtype: 'button',
                        id: 'printOslBtn',         
                        text : BTN_GRIDPRINT,
                        iconCls : 'print16',
                        hidden: false,
                        menu: new Ext.menu.Menu([
                                               {text: langs('Печать'), handler: function () {this.schedulePrintOsl('row');}.createDelegate(this)},
                                               {text: langs('Печать всего списка'), handler: function () {this.schedulePrintOsl()}.createDelegate(this)}
                                       ])
                    }],
                    items:[this.oslFrameGrid]
                    
                },
                
                this.UslPanel,
                {
                    xtype: 'fieldset',
                    autoHeight: true,
                    title: 'Результат ВРТ',
                    items:[                        
                        {
							
							id : 'Res_eco',
							xtype: 'swcommonsprcombo',
							comboSubject: 'EcoResultType',
							hiddenName: 'EcoResultType_id',
							fieldLabel: 'Результат ВРТ',
							width: this.m_width_big,
                            listeners: {
                                'select': function(combo, record, index) {
                                    Ext.getCmp('swECORegistryEditWindow').onSelectRezCombo(combo, record, index);

                                }
                            }
                        },
                        {
                            fieldLabel: 'Дата результата ВРТ',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            width: this.m_width_date,
                            xtype: 'swdatefield',
                            name: 'res_date',
                            allowBlank: true,
                            id : 'Res_date'
                        },
                        {
                            xtype: 'swdiagcombo',
                            fieldLabel: 'Диагноз',
                            name: 'ds_eco',
                            id : 'Ds_eco',
                            //Diag_level3_code: 'Z32ANDZ33ANDZ34ANDZ35ANDZ36ANDO',
                            width: this.m_width_big,
                            allowBlank: true,                            
							registryType: 'EcoRegistry'
                        },
                        {
						
							id : 'vid_ber',
							xtype: 'swcommonsprcombo',
							comboSubject: 'EcoPregnancyType',
							hiddenName: 'EcoPregnancyType_id',
							fieldLabel: 'Вид беременности',
							width: this.m_width_min,
                            listeners: {
                                'select': function(combo, record, index) {
									
                                    Ext.getCmp('swECORegistryEditWindow').onSelectVidBerCombo(combo, record, index);
                                    
                                }
                            },							
                        },
                        {
							
							id : 'count_plod',
							xtype: 'swcommonsprcombo',
							comboSubject: 'EcoChildCountType',
							hiddenName: 'EcoChildCountType_id',
							fieldLabel: 'Количество плодов',
							width: this.m_width_min					
                        }
                    ]
                },
			
			
						new sw.Promed.Panel({
							border:true,
							collapsible:true,
							id:this.id + '_SpecificsPanel',
							isExpanded:false,
							layout:'border',
							split:true,
							style:'margin-bottom: 0.5em;background: #dfe8f6;border: 1px solid #99bbe8;',
							title: 'Исход беременности',
							items:[
								{
									autoScroll:true,
									border:false,
									collapsible:false,
									wantToFocus:false,
									id:this.id + '_SpecificsTree',
									listeners:{
										'bodyresize': function(tree) {																						
											setTimeout(function() {
												
												var tree = Ext.getCmp("swECORegistryEditWindow_SpecificsTree");
												if (tree.getLoader().baseParams.Status != 1){
													Ext.getCmp("swECORegistryEditWindow").specificsPanel.setHeight(1650);
												}
											}, 1);
										}.createDelegate(this),
										'beforeload': function(node) {
											
											
											var tree = this.findById(this.id + '_SpecificsTree');
											if (this.PersonRegister_id) {
												tree.getLoader().baseParams.PersonRegister_id = this.PersonRegister_id;
											}
											
											//Входные параметтры для дерева
											var form = this;
											var data_form = form.svedPanel.getForm();												
		
											tree.getLoader().baseParams.object = "PersonPregnancy";
											tree.getLoader().baseParams.EvnSection_id = "";//base_form.findField('EvnSection_id').getValue();											
											tree.getLoader().baseParams.EvnSection_setDate = "";//Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y');
											tree.getLoader().baseParams.EvnSection_disDate = "";//Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y');
											tree.getLoader().baseParams.createCategoryMethod = "Ext.getCmp('"+this.getId()+"').createPersonPregnancyCategory";
											tree.getLoader().baseParams.deleteCategoryMethod = "Ext.getCmp('"+this.getId()+"').deletePersonPregnancyCategory";
											tree.getLoader().baseParams.allowCreateButton = true;//(this.action != 'view');
											tree.getLoader().baseParams.allowDeleteButton = true;//(this.action != 'view');
										}.createDelegate(this),
										'click':function (node, e) {
											
											
											if (e && e.getTarget('.link', this.specificsTree.body)) {
												e.stopEvent();
												return false;
											}
											if (e && node && Ext.get(node.ui.getEl())) {
												var linkEl = Ext.get(node.ui.getEl()).child('.link');
												if (linkEl && linkEl.isVisible() && linkEl.dom.innerText == 'Создать') {
													e.stopEvent();
													return false;
												}
											}

											if (this.WizardPanel) {
												this.WizardPanel.hide();
											}
											
											if (typeof node.attributes.key != 'undefined' && node.attributes.key != ""){
												node.attributes.value = 'PersonPregnancy';
											}
											
											switch (node.attributes.value) {
												//Открытие исхода	
												case 'PersonPregnancy':
													if (!this.WizardPanel) {
														this.createPersonPregnancyWizardPanel();
													}
													if (this.WizardPanel.isLoading()) {
														this.WizardPanel.show();
														if (e) e.stopEvent();
														return false;
													}

													this.WizardPanel.resetCurrentCategory();

													if (!Ext.isEmpty(node.attributes.key) || node.attributes.grid) {
														var params = {};
														switch(node.attributes.object) {
															case 'Anketa':
																if (this.PersonRegister_id) {
																	params.PersonPregnancy_id = node.attributes.key;
																	this.WizardPanel.getCategory('Anketa').loadParams = params;
																	this.WizardPanel.getCategory('Anketa').selectPage(0);
																}
																break;
															case 'AnketaCommonData':
																params.PersonPregnancy_id = node.attributes.key;
																this.WizardPanel.getCategory('Anketa').loadParams = params;
																this.WizardPanel.getCategory('Anketa').selectPage(0);
																break;
															case 'AnketaFatherData':
																params.PersonPregnancy_id = node.attributes.key;
																this.WizardPanel.getCategory('Anketa').loadParams = params;
																this.WizardPanel.getCategory('Anketa').selectPage(1);
																break;
															case 'AnketaAnamnesData':
																params.PersonPregnancy_id = node.attributes.key;
																this.WizardPanel.getCategory('Anketa').loadParams = params;
																this.WizardPanel.getCategory('Anketa').selectPage(2);
																break;
															case 'AnketaExtragenitalDisease':
																params.PersonPregnancy_id = node.attributes.key;
																this.WizardPanel.getCategory('Anketa').loadParams = params;
																this.WizardPanel.getCategory('Anketa').selectPage(3);
																break;
															case 'Screen':
																params.PregnancyScreen_id = node.attributes.key;
																this.WizardPanel.getCategory('Screen').loadParams = params;
																this.WizardPanel.getCategory('Screen').selectPage(0);
																break;
															case 'EvnList':
																this.WizardPanel.getCategory('EvnList').selectPage(0);
																break;
															case 'ConsultationList':
																this.WizardPanel.getCategory('ConsultationList').selectPage(0);
																break;
															case 'ResearchList':
																this.WizardPanel.getCategory('ResearchList').selectPage(0);
																break;
															case 'Certificate':
																params.BirthCertificate_id = node.attributes.key;
																this.WizardPanel.getCategory('Certificate').loadParams = params;
																this.WizardPanel.getCategory('Certificate').selectPage(0);
																break;
															case 'Result':
																params.BirthSpecStac_id = node.attributes.key;
																
																
																if (!Ext.isEmpty(params.BirthSpecStac_id) || !Ext.isEmpty(this.PersonRegister_id)) {
																	this.WizardPanel.getCategory('Result').loadParams = params;
																	this.WizardPanel.getCategory('Result').selectPage(0);
																}
																break;
															case 'DeathMother':
																params.DeathMother_id = node.attributes.key;
																this.WizardPanel.getCategory('DeathMother').loadParams = params;
																this.WizardPanel.getCategory('DeathMother').selectPage(0);
																break;
														}

														var status = 0;
														var category = this.WizardPanel.getCurrentCategory();
														if (category && node.attributes.key) {
															var categoryData = category.getCategoryData(category, node.attributes.key);
															status = categoryData?categoryData.status:0;
														}

														var page = this.WizardPanel.getCurrentPage();
														var readOnly = (node.attributes.readOnly || this.action == 'view');
														
														var statustree = node.attributes.loader.baseParams.Status;

														if (page && status != 3) {
															this.WizardPanel.show();
															category.setReadOnly(statustree == 4);
															category.moveToPage(page, this.WizardPanel.afterPageChange);
														} else {
															this.resizeSpecificForWizardPanel();
														}
													} else {
														if (node.attributes.object == 'Result' && !node.attributes.deleted) {
															this.WizardPanel.show();
															var category = this.WizardPanel.getCategory('Result');
															category.createCategory(category);
														}
													}
													
													break;
												default:
													this.specificsPanel.setHeight(220);
													this.specificsFormsPanel.doLayout();
													break;
											}
											this.prevNode = node;
										}.createDelegate(this)
									},
									loader:new Ext.tree.TreeLoader({
										dataUrl:'/?c=Eco&m=getResultPregnancyTree'
									}),
									region:'west',
									root:{
										draggable:false,
										id:'specifics_tree_root',
										nodeType:'async',
										text:'Специфика',
										value:'root'
									},
									rootVisible:false,
									style : 'background: white',
									split:true,
									useArrows:true,
									width:200,
									xtype:'treepanel',
								},
								{
									border:false,
									//style : 'border: 1px solid #666',
									layout:'border',
									region:'center',
									xtype:'panel',
									items:[
//										{
//											autoHeight:true,
//											border:true,
//											labelWidth:15,
//											split:true,
//											items:[
//											],
//											layout:'form',
//											region:'north',
//											//style : 'background: red',
//											xtype:'panel'
//										},
										{
											autoHeight:true,
											border:true,
											id:this.id + '_SpecificFormsPanel',
											items:[

											],
											layout:'fit',
											region:'center',
											style : 'background: white',
											xtype:'panel'
										}
									]
								}
							]
						})		
			
			
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
            listeners: {
                    tabchange : function (panel, tab) {
                        if (panel.getActiveTab().id=='tabDs'){
                            Ext.getCmp('swECORegistryEditWindow').onLoadDs();                             
                        }
                   }
            },
            items : [
                {
                    autoHeight: true,                                    
                    title: 'Сведения',
                    id: 'tabSved',
                    border: false,    
                    style: 'padding:10px;margin:0px;',
                    items : [
                        this.svedPanel                        
                    ] 
                },
                {                      
                    title: 'Список уточненных диагнозов',
                    id: 'tabDs',
                    border: false,
                    layout: 'border',
                    tbar:[{
                            xtype: 'button',
                            id: 'printDsBtn',         
                            text : BTN_GRIDPRINT,
                            iconCls : 'print16',
                            hidden: false,
                            menu: new Ext.menu.Menu([
                                               {text: langs('Печать'), handler: function () {this.schedulePrintDs('row');}.createDelegate(this)},
                                               {text: langs('Печать всего списка'), handler: function () {this.schedulePrintDs()}.createDelegate(this)}
                                           ])
                        }],
                    items : [
                        this.diagsPanel   
                    ] 
                }]
        }),

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
                this.PersonInfoPanelEco,
                this.EcoSl
            ]
        });

        Ext.apply(this, {
           layout: 'border',
            buttons:
                [
                {
                    handler: function() {
                        this.ownerCt.checkCrossing();					
                    },
                    iconCls: 'ok16',
                    text: 'Сохранить',
					id: 'ecosave'
                },                
                {
                    text: '-'
                },
                    HelpButton(this, 0),
                {
                    handler: function()  {
                        this.ownerCt.hide();
                    },
                    iconCls: 'cancel16',
                    text: BTN_FRMCANCEL
                }],
            items:[wnd.MainPanel]
        });
        
        sw.Promed.swECORegistryEditWindow.superclass.initComponent.apply(this, arguments);
		
		
		
		
		this.onSpecificsExpand = function (panel, forbidResetSpecific) {
			
			
			this.Morbus_id = null;
			panel.isExpanded = true;
			var than = this;
			var tree = this.specificsTree;
			//var tree = parentWin.specificsTree;
			tree.getRootNode().expand();


			var now;

			var func = function(node) {
			
				
				
				while (node) {
					switch (node.id) {
						case 'PersonPregnancy':
							if (!male && !parentWin.childPS && /*olderThanOneYear && */isPregnancyDiag) {
								node.enable();
								node.expand();
								node.leaf = false;
							} else {
								node.disable();
								node.collapse();
								node.leaf = true;
							}
							node.ui.updateExpandIcon();
							break;
					}
					node = node.nextSibling;
				}
			};
			var loadTree = function(forceLoad) {
				
				
				//if (!parentWin.treeLoaded || forceLoad) {
				//	parentWin.treeLoaded = true;
				if (!this.treeLoaded || forceLoad) {
					this.treeLoaded = true;
					tree.getLoader().load(tree.getRootNode(), function(){
						func(tree.getRootNode().firstChild);
					});
				} else {
					func(tree.getRootNode().firstChild);
				}
			};
			
			
			
			//if (isOnkoDiag && isDebug()) {
			//	panel.hide();
			//} else {
				if (!forbidResetSpecific) {
					
					
					panel.show();
					
					//загрузка дерева
					
					
					tree.fireEvent('click', tree.getRootNode());
					tree.setWidth(300);
					//panel.doLayout();
				}
				var isPregnancyDiag = false;

		}.createDelegate(this);
	
		this.specificsPanel = this.findById(this.id + '_SpecificsPanel');
		this.specificsTree = this.findById(this.id + '_SpecificsTree');
		this.specificsFormsPanel = this.findById(this.id + '_SpecificFormsPanel');
		
		
		if (!this.specificsPanel.collapsed) {
			this.specificsPanel.fireEvent('expand', this.specificsPanel);
		}		
		
		
    }
	

});