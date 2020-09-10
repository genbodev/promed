/**
* swReanimatRegistryWindow - окно регистра реанимации
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @package      MorbusReanimat
* @author       Мускат Борис
* @version      10.2017
* @comment      Префикс для id компонентов ORW (ReanimatRegistryWindow)
*
*/

//getButtonSearch: function() - ф-я возвращает ссылку на кнопку поиска	
//doReset: function()  - функция сброса (очситки) 
//doSearch: function(params) - функция поиска
//getRecordsCount: function() - функция вычисления общего числа записей
//openViewWindow: function(action) - просмотр реанимационного периода или ЭМК
//openWindow: function(action) - исключение из регистра  - начало
//ReanimatRegisterOut: function(pdata) - исключение из регистра  - окончание
//emkOpen: function()- открытие ЭМК
//printAliveDead: function() - Печать списка выжил/умер
//printReanimatUslugi: function() - Печать списка услуг	
//printReanimatUslugiCount: function() - Печать количеств услуг
								

sw.Promed.swReanimatRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Регистр пациентов в реанимации',
	width: 800,    
    codeRefresh: true,
	objectName: 'swReanimatRegistryWindow',
	id: 'swReanimatRegistryWindow',	
    buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	height: 550,
	layout: 'border',
	
	// ф-я возвращает ссылку на кнопку поиска
	getButtonSearch: function() {
		return Ext.getCmp('ORW_SearchButton');
	},
/*    inArray: function(needle, array){//++
        for(var k in array){
            if(array[k] == needle)
                return true;
        }
        
        return false;
    },*/
	// функция сброса (очситки) 
	doReset: function() {//++
		
		var base_form = this.findById('ReanimatRegistryFilterForm').getForm();
		base_form.reset();
		this.ReanimatRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.ReanimatRegistrySearchFrame.ViewActions.person_register_out.setDisabled(false);		
		this.ReanimatRegistrySearchFrame.ViewActions.action_edit.setDisabled(true);
		this.ReanimatRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.ReanimatRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.ReanimatRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		
		this.ReanimatRegistrySearchFrame.ViewActions.action_print.setDisabled(false);
		this.ReanimatRegistrySearchFrame.ViewActions.action_print.menu.printAliveDead.setDisabled(false);
		this.ReanimatRegistrySearchFrame.ViewActions.action_print.menu.printCountUslug.setDisabled(false);
		this.ReanimatRegistrySearchFrame.ViewActions.action_print.menu.printListUslug.setDisabled(false);

		this.ReanimatRegistrySearchFrame.getGrid().getStore().removeAll();
			
	},
	
	// функция поиска
	doSearch: function(params) {//++
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('ReanimatRegistryFilterForm').getForm();
		
		if ( !params.firstLoad && this.findById('ReanimatRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.ReanimatRegistrySearchFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (!params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyibran_tip_poiska_cheloveka'] + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? lang['po_sostoyaniyu_na_moment_sluchaya'] : lang['po_vsem_periodikam']) + lang['pri_vyibrannom_variante_poisk_rabotaet_znachitelno_medlennee_hotite_prodoljit_poisk'],
				title: lang['preduprejdenie']
			});
			return false;
		}


//		console.log('BOB_RRW_EvnScaleType=',this.findById('RRW_EvnScaleType').getValue());  //BOB - 13.10.2017
//		console.log('BOB_RRW_ReanimatActionType=',this.findById('RRW_ReanimatActionType').getValue());  //BOB - 13.10.2017
//		console.log('BOB_RRW_RA_DrugNames=',this.findById('RRW_RA_DrugNames').getValue());  //BOB - 13.10.2017


		var post = getAllFormFieldValues(this.findById('ReanimatRegistryFilterForm'));
		
		var ErrMessag = '';
		if ((post.RRW_BeginDate != '') && (post.RRW_EndDate == ''))
			ErrMessag = 'Интервал поиска нахождения в реанимации должен быть полным,  <br> отсутствует дата окончания!' + ErrMessag;
		if ((post.RRW_BeginDate == '') && (post.RRW_EndDate != ''))
			ErrMessag = 'Интервал поиска нахождения в реанимации должен быть полным,  <br> отсутствует дата начала!' + ErrMessag;
		
		if (ErrMessag != '') {
			Ext.MessageBox.alert('Внимание!', ErrMessag);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		post.limit = 100;
		post.start = 0;
		
		post.HardOnly = this.findById('RRW_HardOnly').getValue() ? 'on' : ''; //значение чекбокса "Только тяжёлые"
		post.ReanimatRegister_IsPeriodNow = this.findById('RRW_ReanimatPeriodNow').getValue() ? 'on' : ''; //значение чекбокса "В реанимации сейчас"
		
		console.log('BOB_post=',post);  //BOB - 13.10.2017
		//log(post);
		

		if ( base_form.isValid() ) {
			this.ReanimatRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	// функция вычисления общего числа записей
	getRecordsCount: function() {//++
		var base_form = this.getFilterForm().getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		//var post = getAllFormFieldValues(this.getFilterForm());

		var post = getAllFormFieldValues(this.findById('ReanimatRegistryFilterForm'));
		
		var ErrMessag = '';
		if ((post.RRW_BeginDate != '') && (post.RRW_EndDate == ''))
			ErrMessag = 'Интервал поиска нахождения в реанимации должен быть полным,  <br> отсутствует дата окончания!' + ErrMessag;
		if ((post.RRW_BeginDate == '') && (post.RRW_EndDate != ''))
			ErrMessag = 'Интервал поиска нахождения в реанимации должен быть полным,  <br> отсутствует дата начала!' + ErrMessag;
		
		if (ErrMessag != '') {
			Ext.MessageBox.alert('Внимание!', ErrMessag);
			return false;
		}
		post.HardOnly = this.findById('RRW_HardOnly').getValue() ? 'on' : ''; //значение чекбокса "Только тяжёлые"
		post.ReanimatRegister_IsPeriodNow = this.findById('RRW_ReanimatPeriodNow').getValue() ? 'on' : ''; //значение чекбокса "В реанимации сейчас"

		if ( post.PersonPeriodicType_id == null ) {
			post.PersonPeriodicType_id = 1;
		}
		console.log('BOB_post=',post);  //BOB - 13.10.2017

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	
	//просмотр реанимационного периода или ЭМК
	openViewWindow: function(action) {//++
		var win = this;
		
		if (getWnd('swEvnReanimatPeriodEditWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_prosmotra_uje_otkryito']);
			return false;
		}
		
		var grid = this.ReanimatRegistrySearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected()) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
			
		if ( Ext.isEmpty(selected_record.get('EvnReanimatPeriod_id')) ) {
			sw.swMsg.alert(lang['soobschenie'], 'Реанимационный период не открывался');
			
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						Ext.getCmp('swReanimatRegistryWindow').emkOpen();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Реанимационный период не открывался. <BR> Желаете открыть ЭМК?',
				title: lang['preduprejdenie']  //vopros   
			});
			return false;                
		}
		
		
		var params = {
			EvnReanimatPeriod_id: selected_record.data.EvnReanimatPeriod_id,
			ERPEW_title: lang['redaktirovanie_reanimationnogo_perioda'],  			
			action: 'view',
			UserMedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			userMedStaffFact: this.userMedStaffFact,
			from: '', 
			ARMType: 'ReanimatRegistry' 											
		};
		
		
		
		var RP_saved = false;
		params.Callback = function(pdata) {
			getWnd('swEvnReanimatPeriodEditWindow').hide();                            
			RP_saved = pdata; 
			console.log('BOB_RP_saved=',RP_saved); 
			if(RP_saved) { //вообще-то здесь всегда false т.к. окно открывалось только на просмотр
				grid.getStore().reload();
			} else {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			}
		};    
		
		
		
		console.log('BOB_params3=',params); 

		getWnd('swEvnReanimatPeriodEditWindow').show(params);
	},
	
	//исключение из регистра  - начало
	openWindow: function(action) {//--
			
		var form = this.findById('ReanimatRegistryFilterForm').getForm();
		var grid = this.ReanimatRegistrySearchFrame.getGrid();
		
		var win = this;
        
//		if (action == 'include') { // добавление
//            var params = {};
//            getWnd('swBSKSelectWindow').show(params);
//
//		} else 
		if (action == 'out') {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
			{
				Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
				return false;
			}
			var record = grid.getSelectionModel().getSelected();
			
			//BOB - 23.01.2018
			var RegisterParams = {
//				PersonRegister_id: record.get('PersonRegister_id'),    //BOB - 23.01.2018
				Person_id: record.get('Person_id'),
				RegisterType_SysNick: 'reanimat',						//BOB - 23.01.2018   "Person"  отрезал
				callback: function(pdata) {
					getWnd('ufa_RegisterOutCaseSelectWindow').hide();   
					win.ReanimatRegisterOut(pdata);
				}    

			};
			getWnd('ufa_RegisterOutCaseSelectWindow').show(RegisterParams);
			//BOB - 23.10.2017
			
		}
		
	},
	//исключение из регистра  - окончание
	ReanimatRegisterOut: function(pdata) {
		
		var win = this;
		var grid = this.ReanimatRegistrySearchFrame.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();

		//!!!!!!!!!!!!!!сравнение дат исключения из регистра с включением в регистр
		if (pdata.ReanimatRegister_disDate.dateFormat('Y-m-d') <= selected_record.get('ReanimatRegister_setDate').dateFormat('Y-m-d')) {   //BOB - 23.01.2017
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['data_isklyucheniya_iz_registra_ne_mojet_byit_menshe_ili_ravno_date_vklyucheniya_v_registr'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=ReanimatRegister&m=ReanimatRegisterOut',
			data: { 
//				PersonRegister_id: selected_record.data.PersonRegister_id,   //BOB - 23.01.2017
				ReanimatRegister_id: selected_record.data.ReanimatRegister_id,
				ReanimatRegister_disDate: pdata.ReanimatRegister_disDate.dateFormat('Y-m-d'),
				PersonRegisterOutCause_id: pdata.PersonRegisterOutCause_id,
				MedPersonal_did: win.userMedStaffFact.MedPersonal_id,
				Lpu_did: win.userMedStaffFact.Lpu_id
			},
			success: function(response) {
				var params = Ext.util.JSON.decode(response);
				
				console.log('BOB_params1=',params); 
				
				if (!(params.Error_Code) && !(params.Error_Message))
					win.doSearch();    //BOB - 23.01.2017
			}, 
			error: function() {
				alert("При обработке запроса на сервере произошла ошибка!");
			} 
		});	

		
	},
	//открытие ЭМК
	emkOpen: function()
	{ //++  открывает ЭМК, но почему-то сигнальную информацию, а из АРМа врача - сразу движение
		var grid = this.ReanimatRegistrySearchFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	//Печать списка выжил/умер
	printAliveDead: function(RepName) {
		
		var RegisterParams = {
			callback: function(pdata) {
				getWnd('ufa_GetReportInterval').hide();   
				//console.log('BOB_pdata=',pdata); 
				
		//		var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'') + '/run?__report=report/ReanimatAliveDead.rptdesign';
				var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'') + '/run?__report=report/'+ RepName + '.rptdesign';
		//		var url = 'http://192.168.200.16/birt-viewer//run?__report=report/cathetPrrint.rptdesign';
				url += '&BeginDate=' + pdata.BeginDate.dateFormat('d.m.Y');
				url += '&EndDate=' + pdata.EndDate.dateFormat('d.m.Y');
				url += '&BeginDateQuery=' + pdata.BeginDate.dateFormat('Y-m-d');
				url += '&EndDateQuery=' + pdata.EndDate.dateFormat('Y-m-d');
				url += '&__format=pdf';		
				console.log('BOB_url=',url);  
				window.open(url, '_blank'); 
			}    
		};
		getWnd('ufa_GetReportInterval').show(RegisterParams);
	},
	
	//Печать списка услуг
	printReanimatUslugi: function() {
		
		var RegisterParams = {
			callback: function(pdata) {
				getWnd('ufa_GetReportInterval').hide();   
				//console.log('BOB_pdata=',pdata); 
				
				var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'') + '/run?__report=report/ReanimatUslugi.rptdesign';
		//		var url = 'http://192.168.200.16/birt-viewer//run?__report=report/cathetPrrint.rptdesign';
				url += '&BeginDate=' + pdata.BeginDate.dateFormat('d.m.Y');
				url += '&EndDate=' + pdata.EndDate.dateFormat('d.m.Y');
				url += '&BeginDateQuery=' + pdata.BeginDate.dateFormat('Y-m-d');
				url += '&EndDateQuery=' + pdata.EndDate.dateFormat('Y-m-d');
				url += '&__format=pdf';		
				//console.log('BOB_url=',url);  
				window.open(url, '_blank'); 
			}    
		};
		getWnd('ufa_GetReportInterval').show(RegisterParams);
	},
	//Печать количеств услуг
	printReanimatUslugiCount: function() {
		
		var RegisterParams = {
			callback: function(pdata) {
				getWnd('ufa_GetReportInterval').hide();   
				//console.log('BOB_pdata=',pdata); 
				
				var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'') + '/run?__report=report/ReanimatUslugiCount.rptdesign';
		//		var url = 'http://192.168.200.16/birt-viewer//run?__report=report/cathetPrrint.rptdesign';
				url += '&BeginDate=' + pdata.BeginDate.dateFormat('d.m.Y');
				url += '&EndDate=' + pdata.EndDate.dateFormat('d.m.Y');
				url += '&BeginDateQuery=' + pdata.BeginDate.dateFormat('Y-m-d');
				url += '&EndDateQuery=' + pdata.EndDate.dateFormat('Y-m-d');
				url += '&__format=pdf';		
				//console.log('BOB_url=',url);  
				window.open(url, '_blank'); 
			}    
		};
		getWnd('ufa_GetReportInterval').show(RegisterParams);
	},
	
        doHelp: function() {
        
        		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=ReanimatRegister&m=doHelp',
//			data: { 
////				PersonRegister_id: selected_record.data.PersonRegister_id,   //BOB - 23.01.2017
//				ReanimatRegister_id: selected_record.data.ReanimatRegister_id,
//				ReanimatRegister_disDate: pdata.ReanimatRegister_disDate.dateFormat('Y-m-d'),
//				PersonRegisterOutCause_id: pdata.PersonRegisterOutCause_id,
//				MedPersonal_did: win.userMedStaffFact.MedPersonal_id,
//				Lpu_did: win.userMedStaffFact.Lpu_id
//			},
			success: function(response) {
//				var params = Ext.util.JSON.decode(response);
//				
//				console.log('BOB_params1=',params); 
				alert('Ok');
			}, 
			error: function() {
				alert("При обработке запроса на сервере произошла ошибка!");
			} 
		});	

        
        
        },
	
	
	
	initComponent: function() {
		var win = this;

		//ГРИД
		this.ReanimatRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true, handler: function() { this.openWindow('include'); }.createDelegate(this)},
                {name: 'action_edit', hidden: true, handler: function() { this.openViewWindow('edit'); }.createDelegate(this)},
                {name: 'action_view',  handler: function() { this.openViewWindow('view'); }.createDelegate(this)},  
				{name: 'action_delete', hidden: true  }, //  ,  handler: this.deletePersonRegister.createDelegate(this)
				{name: 'action_refresh'},
				{name: 'action_print',
					menuConfig: {
						printAliveDead: {
							text: 'Печать списка выжил/умер',
							iconCls: 'print16',
							//hidden: (getRegionNick() == 'kz' || getRegionNick() == 'kareliya'),
							handler: function () { this.printAliveDead('ReanimatAliveDead');  }.createDelegate(this)
						},
						printAliveDead_AllLpu: {
							text: 'Печать списка выжил/умер по МО',
							iconCls: 'print16',
							//hidden: (getRegionNick() == 'kz' || getRegionNick() == 'kareliya'),
							handler: function () { this.printAliveDead('ReanimatAliveDead_AllLpu');  }.createDelegate(this)
						},
						printCountUslug: {  //
							text: 'Печать количеств услуг',
							iconCls: 'print16',
							//hidden: (getRegionNick() == 'kz' || getRegionNick() == 'kareliya'),
							handler: function () { this.printReanimatUslugiCount();  }.createDelegate(this)
						},
						printListUslug: {  //
							text: 'Печать списка услуг',
							iconCls: 'print16',
							//hidden: (getRegionNick() == 'kz' || getRegionNick() == 'kareliya'),
							handler: function () { this.printReanimatUslugi();  }.createDelegate(this)
						}
						
				
					}				
				}   
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH, /* /?c=Search&m=searchData */
			id: 'ReanimatRegistry',
			object: 'ReanimatRegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
//				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},   //закомментарил BOB - 23.01.2018
				{name: 'Person_id', type: 'int', hidden: true},	
				{name: 'Server_id', type: 'int', hidden: true},			
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Lpu_iid', type: 'int', hidden: true},
				{name: 'MedPersonal_iid', type: 'int', hidden: true},
				{name: 'MorbusType_id', type: 'int', hidden: true},					//закомментарил BOB - 23.01.2018
//				{name: 'ReanimatDirectType_id', type: 'int', hidden: true},					//BOB - 23.01.2018
//				{name: 'EvnNotifyBase_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_Name', type: 'string', hidden: true, header: lang['prichina_isklyucheniya_iz_registra'], width: 190},
                
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 90},
			
//            	{name: 'Diag_id', type: 'int', hidden: true},
//				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, hidden: true},
//				{name: 'OnkoDiag_Name', type: 'string', header: lang['gistologiya_opuholi'], width: 250, hidden: true},
//				{name: 'MorbusOnko_IsMainTumor', type: 'string', header: lang['priznak_osnovnoy_opuholi'], width: 150, hidden: true},
//				{name: 'TumorStage_Name', type: 'string', header: lang['stadiya'], width: 60,hidden: true},
//                {name: 'MorbusOnko_setDiagDT', type: 'date', format: 'd.m.Y', header: lang['data_ustanovleniya_diagnoza'], width: 150, hidden: true},
            
//                {name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y',  header: langs('Дата включения в регистр'), width: 150},				//закомментарил BOB - 23.01.2018
//                {name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', hidden: true, header: langs('Дата исключения из регистра'), width: 150},	//закомментарил BOB - 23.01.2018
                {name: 'ReanimatRegister_setDate', type: 'date', format: 'd.m.Y',  header: langs('Дата включения в регистр'), width: 150},					//BOB - 23.01.2018
                {name: 'ReanimatRegister_disDate', type: 'date', format: 'd.m.Y', hidden: true, header: langs('Дата исключения из регистра'), width: 150},	//BOB - 23.01.2018

                {name: 'PMUser_Name', type: 'string', header: 'Кем создана анкета', hidden: true},
            	{name: 'pmUser_id', type: 'int', hidden: true},
				{name: 'Lpu_Nick', type: 'string', header: langs('МО прикр.'), width: 250 },   //width: 150
				{name: 'Diag', type: 'string',  header: langs('Диагноз'), id: 'autoexpand'},
				{name: 'Lpu_Nick_Curr', type: 'string', header: langs('МО госпитализации'), width: 250 },   //width: 150
				{name: 'ReanimatRegister_id', type: 'int', hidden: true},
				{name: 'EvnReanimatPeriod_id', type: 'int', hidden: true},
				{name: 'ReanimatRegister_IsPeriodNow', type: 'int', hidden: true},
				{name: 'selrow', type: 'int', hidden: true}
			],
			focusOnFirstLoad: false,
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function(sm,index,record) {
				//console.log('sm',sm.getSelected(),'index',index,'record',record);
				this.getAction('open_emk').setDisabled( false );
				//this.getAction('ReanimatObjectButton').setDisabled( record.get('PersonRegister_id') == null );
				//this.getAction('person_register_out').setDisabled( Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
                this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
                //this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('MorbusOnko_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
                //this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('MorbusOnko_id')) );
			},
			onDblClick: function(x,c,v) {
			 
                

			}           
		});

        this.ReanimatRegistrySearchFrame.getGrid().on(
           'rowdblclick',
           function(){
                 win.openViewWindow('view');
           }
        );

		//Для раскраски GRIDa
		this.ReanimatRegistrySearchFrame.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
//				alert('Раскраска');
				var cls = '';
				if (row.get('selrow') == 1) {//  Выбранное значение шкалы
					cls = cls+' x-grid-rowred '; 
//					cls = cls+' x-grid-rowbackred '; 
//					cls = cls+' x-grid-rowbackorange '; 
//					cls = cls+' x-grid-rowbackyellow '; 
					cls = cls+' x-grid-rowbold '; 
				 }
				if ((row.get('ReanimatRegister_IsPeriodNow') == 2) && (row.get('selrow') == 0)) {//BOB - 28.03.2018  изменил в логическом выражении "== 1" на "== 2" кто бы мог подумать, что 1 - это "нет"!!!!!!!!
					cls = cls+' x-grid-rowblue '; 
//					cls = cls+' x-grid-rowbold '; 
				 }
				  return cls;
			}
		}); 


/* //BOB - 18.10.2017 - закомментарил
 * Это была установка обработчика события загрузки в Store, выполняющая следующее:
 * отбор уникальных записей по Person_id,
 * при этом нарушалось формирование количеств, отображаемых в футере таблици.
 * Закомментарил т.к.это неправильный подход. Уникальность должна обеспечиваться алгоритмом формирования записей в регистре и запросом, формирующим выборку
 * 
        this.ReanimatRegistrySearchFrame.getGrid().getStore().on(
            'load',
            function (){

                var unicRecs = [];
                var Person_ids = [];
                
                var recs = this.data.items;
                
                //console.log('RECS', recs);
               
                for(var k in recs){
                    if(typeof recs[k] == 'object'){
                          
                          if(recs[k].data.Person_id){
                              if(Ext.getCmp('swReanimatRegistryWindow').inArray(recs[k].get('Person_id'), Person_ids) === false){
                                   Person_ids.push(recs[k].data.Person_id);
                                   unicRecs.push(recs[k]);
                              }    
                          }  
                    }
                }
                
                                
                this.removeAll();
                
                
                for(var k in unicRecs){
                   if(typeof unicRecs[k] == 'object'){ 
                      this.add(unicRecs[k]);    
                   }
                }
                
			alert('load');	

            }   
        );   
*/

		Ext.apply(this, {
			buttons: [
				{
					handler: function() {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					tabIndex: TABINDEX_ORW + 120,
					id: 'ORW_SearchButton',
					text: BTN_FRMSEARCH
				}, {
					handler: function() {
						this.doReset();
					}.createDelegate(this),
					iconCls: 'resetsearch16',
					tabIndex: TABINDEX_ORW + 121,
					text: BTN_FRMRESET
				},{
					handler: function() {
						this.getRecordsCount();
					}.createDelegate(this),
					// iconCls: 'resetsearch16',
					tabIndex: TABINDEX_ORW + 123,
					text: BTN_FRMCOUNT
				},
				/*{
					text: 'DO',
					id: 'TRFFPSW_ButtonDO',
					tooltip: langs('DO'),
					iconCls: 'save16',
					handler: function()
					{
                                            this.doHelp();  //BOB - 17.03.2018
					}.createDelegate(this)
				}, */
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function() {
						this.buttons[this.buttons.length - 2].focus();
					}.createDelegate(this),
					onTabAction: function() {
						this.findById('ReanimatORW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ReanimatORW_SearchFilterTabbar').getActiveTab());
					}.createDelegate(this),
					tabIndex: TABINDEX_ORW + 124,
					text: BTN_FRMCLOSE
				}
			],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('ReanimatRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ 
				getBaseSearchFiltersFrame({
					isDisplayPersonRegisterRecordTypeField: true,
					allowPersonPeriodicSelect: true,
					id: 'ReanimatRegistryFilterForm',
					labelWidth: 130,
					ownerWindow: this,
					searchFormType: 'ReanimatRegistry',
					tabIndexBase: TABINDEX_ORW,
					tabPanelHeight: 225,
					tabPanelId: 'ReanimatORW_SearchFilterTabbar',
					region: 'north',											//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

					tabs: [{
						autoHeight: true,
						bodyStyle: 'margin-top: 5px;',
						border: false,
						labelWidth: 220,
						layout: 'form',
						listeners: {
							'activate': function() {
								this.getFilterForm().getForm().findField('PersonRegisterType_id').focus(250, true);
							}.createDelegate(this)
						},
						title: lang['6_registr'],
						items: [
							{
								xtype: 'swpersonregistertypecombo',
								hiddenName: 'PersonRegisterType_id',
								width: 200
							},
							
							//Дата включения в регистр / исключения из регистра
							{
								xtype: 'panel',
								layout:'column',
								border: false,
								items:[
	
									{
										layout:'form',
										border: false,
										items:[
											{
								fieldLabel: lang['data_vklyucheniya_v_registr'],
								name: 'PersonRegister_setDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 170,
								xtype: 'daterangefield'
											}
										]
									},
									{
										layout:'form',
										border: false,
										items:[

											{
								fieldLabel: lang['data_isklyucheniya_iz_registra'],
								name: 'PersonRegister_disDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 170,
								xtype: 'daterangefield'
											}
										]
									}
								]
							},
							//МО госпитализации
							{	
								layout:'form',
								border: false,
								items:[	
									{
										xtype: 'combo',
										allowBlank: true,
										fieldLabel: 'МО госпитализации',
										//Name: 'ReanimatActionType',	
										hiddenName: 'ReanimatLpu',	
										disabled: false,
										id: 'RRW_ReanimatLpu',
										mode:'local',
										listWidth: 400,
										width: 400,
										triggerAction : 'all',
										editable: false,
										store:new Ext.data.SimpleStore(  {           
											fields: [{name:'Lpu_Nick', type:'string'},
													 { name:'Lpu_id',type:'int'}]
										}),
										displayField:'Lpu_Nick',
										valueField:'Lpu_id',
										tpl: '<tpl for="."><div class="x-combo-list-item">'+
											'{Lpu_Nick} '+ '&nbsp;' +
											'</div></tpl>', 
										listeners: {
											'select': function(combo, record, index) {
												console.log('BOB_record=',record); 
												var Lpu_id = record.data.Lpu_id;
												var CurrDate = getGlobalOptions().date;
												CurrDate = CurrDate.substr(6, 4) + '-'+  CurrDate.substr(3, 2) + '-'+  CurrDate.substr(0, 2); 
							
												$.ajax({
													mode: "abort",
													type: "post",
													async: false,
													url: '/?c=ReanimatRegister&m=getAliveDead',
													data: { BeginDate: CurrDate,
															EndDate:CurrDate,
															Lpu_id: Lpu_id},   //BOB - 28.12.2018
													success: function(response) {
														var params = Ext.util.JSON.decode(response);
										//				console.log('BOB_params1=',params); 
														win.findById('RRW_at_the_beginning').setText(params[0]['at_the_beginning']);
														win.findById('RRW_at_the_end').setText(params[0]['at_the_end']);
														win.findById('RRW_new').setText(params[0]['new']);
														win.findById('RRW_quit').setText(params[0]['quit']);
														win.findById('RRW_to_profile_section').setText(params[0]['to_profile_section']);
														win.findById('RRW_dead').setText(params[0]['dead']);
													}, 
													error: function() {
														alert("При обработке запроса на сервере произошла ошибка!");
													} 
												});	
											}
										}  
									}									
								]
							},

								
							//Специфика - морбус
							{
								layout: 'column',
								border: false,
								items:[
									{
										layout:'form',
										border: false,
										items:[
											
											
											//combo Направление (специфика) 
											{
												fieldLabel: 'Специфика',
												mode: 'local',
												store: new Ext.data.JsonStore({
													url: '/?c=ReanimatRegister&m=getMorbusType',
													autoLoad: true,								
													fields: [
														{name: 'MorbusType_id', type: 'int'},
														{name: 'MorbusType_name', type: 'string'}
													],
													key: 'MorbusType_id',
												}),
												editable: false,
												triggerAction: 'all',
												hiddenName: 'MorbusType_id',
												displayField: 'MorbusType_name',
												valueField: 'MorbusType_id',
												width: 400,
												xtype: 'combo',
												tpl: '<tpl for="."><div class="x-combo-list-item">'+
																   '{MorbusType_name} '+ '&nbsp;' +
																   '</div></tpl>',
														}
										]
													} 
/*									{
										layout:'form',
										border: false,
										items:[
											{
												xtype: 'combo',
												fieldLabel: 'Есть заполненные анкеты',
												hiddenName: 'quest_id',
												labelAlign: 'left',
												editable: false,
												disabled: true,
												id: 'quest_yn',
												mode:'local',
												width: 50,
												triggerAction : 'all',
												store:new Ext.data.SimpleStore(  {           
													fields: [{name:'quest', type:'string'},{ name:'quest_id',type:'int'}],
													data: [
														['Да', 1],
														['Нет', 2]
													]
												}),
												displayField:'quest',
												valueField:'quest_id',
												tpl: '<tpl for="."><div class="x-combo-list-item">'+
																   '{quest} '+ '&nbsp;' +
																   '</div></tpl>'         
											}								
										]								
									},
									{
										layout:'form',
										border: false,
										items:[
											{
												xtype: 'combo',
												displayField: 'pmUser_FioL',
												editable: true,
												enableKeyEvents: true,
												fieldLabel: 'Пользователь',
												hiddenName: 'pmUser_docupdID',
												id: 'pmUser_docupd',
												disabled: true,
												minChars: 1,
												width: 300,
												name : "pmUser_docupdID",
												minLength: 1,
												mode: 'local',
												resizable: true,
												selectOnFocus: true,
												store: new Ext.data.Store({
													autoLoad: false,
													reader: new Ext.data.JsonReader({
														id: 'pmUser_id'
													}, 
													[
														{name: 'pmUser_id', mapping: 'pmUser_id'},
														{name: 'pmUser_FioL', mapping: 'pmUser_FioL'},
														{name: 'pmUser_Fio', mapping: 'pmUser_Fio'},
														{name: 'pmUser_Login', mapping: 'pmUser_Login'},
														{name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode'}
													]),
													sortInfo: {
														direction: 'ASC',
														field: 'pmUser_Fio'
													},
													url: '/?c=BSK_Register_User&m=getCurrentOrgUsersList'
												}),
												triggerAction: 'all',
//												tpl: new Ext.XTemplate(
//														'<tpl for="."><div class="x-combo-list-item">',
//														'<div><b>{[values.pmUser_Fio ? values.pmUser_Fio : "&nbsp;"]}</b> {[values.pmUser_Login ? "(" + values.pmUser_Login + ")" : "&nbsp;"]}</div>',
//														'</div></tpl>'
//												),
												valueField: 'pmUser_id',  
												listeners: {
													change: function() {

													},
													keydown: function(inp, e) {
														if ( e.getKey() == e.END ) {
															this.inKeyMode = true;
															this.select(this.getStore().getCount() - 1);
														}

														if ( e.getKey() == e.HOME ) {
															this.inKeyMode = true;
															this.select(0);
														}

														if ( e.getKey() == e.PAGE_UP ) {
															this.inKeyMode = true;
															var ct = this.getStore().getCount();

															if ( ct > 0 ) {
																if ( this.selectedIndex == -1 ) {
																	this.select(0);
																}
																else if ( this.selectedIndex != 0 ) {
																	if ( this.selectedIndex - 10 >= 0 )
																		this.select(this.selectedIndex - 10);
																	else
																		this.select(0);
																}
															}
														}

														if ( e.getKey() == e.PAGE_DOWN ) {
															if ( !this.isExpanded() ) {
																this.onTriggerClick();
															}
															else {
																this.inKeyMode = true;
																var ct = this.getStore().getCount();

																if ( ct > 0 ) {
																	if ( this.selectedIndex == -1 ) {
																		this.select(0);
																	}
																	else if ( this.selectedIndex != ct - 1 ) {
																		if ( this.selectedIndex + 10 < ct - 1 )
																			this.select(this.selectedIndex + 10);
																		else
																			this.select(ct - 1);
																	}
																}
															}
														}

														if ( e.altKey || e.ctrlKey || e.shiftKey )
															return true;

														if ( e.getKey() == e.DELETE||e.getKey() == e.BACKSPACE) {
															inp.setValue('');
															inp.setRawValue("");
															inp.selectIndex = -1;
															if ( inp.onClearValue ) {
																this.onClearValue();
															}
															e.stopEvent();
															return true;
														}                                                                                
													},
													beforequery: function(q) {
														if ( q.combo.getStore().getCount() == 0 ) {
															q.combo.getStore().removeAll();
															q.combo.getStore().load();
														}                                                                                
													}
												}
											}
										]								

									}								
*/
								]
							},
							//чекбоксы: 'Только тяжёлые' и 'В реанимации сейчас'
							{
								xtype: 'panel',
								layout:'column',
								border: false,
								items:[
									{
										layout:'form',
										border: false,
										items:[
											{
												fieldLabel: 'Только тяжёлые',
												name: 'HardOnly',
												id: 'RRW_HardOnly',
												//tabIndex: form.firstTabIndex + 12,
												xtype: 'checkbox',
												listeners: {
													'check': function(chb, checked ) {														
														if (checked){
															win.findById('RRW_ReanimatPeriodNow').setValue(true);
														}														
													}.createDelegate(this)
												}

											}
										]
									},
									{
										layout:'form',
										border: false,
										items:[
											{
												fieldLabel: 'В реанимации сейчас',
												name: 'ReanimatPeriodNow',
												id: 'RRW_ReanimatPeriodNow',
												//tabIndex: form.firstTabIndex + 12,
												xtype: 'checkbox',
												listeners: {
													'check': function(chb, checked ) {														
														if (!checked){
															win.findById('RRW_HardOnly').setValue(false);
														}														
													}.createDelegate(this)
												}

											}
										]
									}

								]
							},
							//Период нахождения в реанимации
							{
								xtype: 'panel',
								layout:'column',
								border: false,
								items:[
									{
										layout:'form',
										border: false,
										items:[
											{
												allowBlank: true,
												fieldLabel: 'Нахождение в реанимации с',
												id: 'RRW_BeginDate',
												name: 'RRW_BeginDate',
												xtype: 'swdatefield',
												plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
												maxValue: getGlobalOptions().date
										   }
										]
									},
									{
										layout:'form',
										border: false,
										labelWidth: 30,
										items:[
											{
												allowBlank: true,
												fieldLabel: 'по',
												id: 'RRW_EndDate',
												name: 'RRW_EndDate',
												xtype: 'swdatefield',
												plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
												maxValue: getGlobalOptions().date
											} 
										]
									}
								]
							},
							//Результаты исследований по шкалам
							{
								xtype: 'panel',
								layout:'column',
								border: false,
								items:[
									//пнель - combo Тип Шкалы исследования состояния    
									{	
										layout:'form',
										border: false,
										items:[	
											//combo Тип Шкалы исследования состояния
											{
												xtype: 'combo',
												allowBlank: true,
												fieldLabel: 'По шкале',
												hiddenName: 'EvnScaleType',									
												disabled: false,
												id: 'RRW_EvnScaleType',
												//name: 'EvnScaleType',
												mode:'local',
												listWidth: 500,
												width: 500,
												triggerAction : 'all',
												editable: false,
													store:new Ext.data.SimpleStore(  {           
														fields: [{name:'ScaleType_Name', type:'string'},
																 { name:'ScaleType_SysNick',type:'string'},
																 { name:'ScaleType_id',type:'int'}]
													}),
												displayField:'ScaleType_Name',
												valueField:'ScaleType_id',
												tpl: '<tpl for="."><div class="x-combo-list-item">'+
													'{ScaleType_Name} '+ '&nbsp;' +
													'</div></tpl>' 
											}
										]
									},
									{
										xtype: 'label',													
										text: 'результат от',
										style:'margin-top: 4px; margin-left: 10px; margin-right: 5px;'
									},
									new Ext.form.NumberField({
										value: 0,
										id: 'RRW_EvnScaleFrom',
										name: 'EvnScaleFrom',
										width: 70
									}),
									{
										xtype: 'label',													
										text: 'до',
										style:'margin-top: 4px;  margin-left: 10px; margin-right: 5px; '
									},
									new Ext.form.NumberField({
										value: 0,
										id: 'RRW_EvnScaleTo',
										name: 'EvnScaleTo',
										width: 70											
									})
								]
							},
							//реанимационные мероприятия
							{	
								layout:'form',
								border: false,
								items:[	
									{
										xtype: 'combo',
										allowBlank: true,
										fieldLabel: 'Вид реанимационного мероприятия',
										//Name: 'ReanimatActionType',	
										hiddenName: 'ReanimatActionType',	
										disabled: false,
										id: 'RRW_ReanimatActionType',
										mode:'local',
										listWidth: 400,
										width: 400,
										triggerAction : 'all',
										editable: false,
										store:new Ext.data.SimpleStore(  {           
											fields: [{name:'ReanimatActionType_Name', type:'string'},
													 { name:'ReanimatActionType_SysNick',type:'string'},
													 { name:'ReanimatActionType_id',type:'int'}]
										}),
										displayField:'ReanimatActionType_Name',
										valueField:'ReanimatActionType_id',
										tpl: '<tpl for="."><div class="x-combo-list-item">'+
											'{ReanimatActionType_Name} '+ '&nbsp;' +
											'</div></tpl>' 
									}									
								]
							},
							//использованные медикаменты
							{	
								layout:'form',
								border: false,
								items:[	
									{
										xtype: 'combo',
//															allowBlank: false,
										disabled: false,
										id: 'RRW_RA_DrugNames',
										//name: 'RA_DrugNames',
										hiddenName: 'RA_DrugNames',	
										mode:'local',
										listWidth: 240,
										width: 240,
										triggerAction : 'all',
										editable: false,
										displayField:'ReanimDrugType_Name',
										valueField:'ReanimDrugType_id',
										fieldLabel:'Использованный медикамент',
										labelSeparator: '',
//															labelStyle: 'width: 60px;',
										tpl: '<tpl for="."><div class="x-combo-list-item">'+
											'{ReanimDrugType_Name} '+ '&nbsp;' +
											'</div></tpl>' ,
										store:new Ext.data.SimpleStore(  {           
											fields: [{name:'ReanimDrugType_Name', type:'string'},
													 { name:'ReanimDrugType_id',type:'int'} ]
										})
									}
									
								]
							}

							
							
						]
					}]
				}),
				this.ReanimatRegistrySearchFrame,
				{
					layout:'column',
					id:'RRW_AliveDead',
					border:false,
					region: 'south',
					frame: true,
				//	style: 'border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
				//	bodyStyle:'background-color: transparent',
					height: 60,
					items:[	
						{							
							layout:'column',
							border:true,
							style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
							width: 200,
							height: 30,
							items:[	
								{
									id: 'RRW_AliveDead_label',
									xtype: 'label',													
									text: '',
									style:'margin-left: 10px; margin-top: 5px; '
								}
							]
						},
						{							
							layout:'column',
							border:true,
							style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
							width: 150,
							height: 30,
							items:[	
								{
									xtype: 'label',													
									text: 'На начало',
									style:'margin-left: 10px; margin-top: 5px; '
								},
								{									
									layout:'form',
									style:'margin-left: 5px; margin-top: 5px; color: blue; font-weight: bold;border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
									width: 60,
									items:[
										{
											id: 'RRW_at_the_beginning',
											xtype: 'label',
											text: '0'
										}					
									]
								}
							]
						},
						{							
							layout:'column',
							border:true,
							style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
							width: 150,
							height: 30,
							items:[	
								{
									xtype: 'label',													
									text: 'На конец',
									style:'margin-left: 10px; margin-top: 5px; '
								},
								{									
									layout:'form',
									style:'margin-left: 5px; margin-top: 5px; color: blue; font-weight: bold;border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
									width: 60,
									items:[
										{
											id: 'RRW_at_the_end',
											xtype: 'label',
											text: '0'
										}					
									]
								}
							]
						},
						{							
							layout:'column',
							border:true,
							style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
							width: 150,
							height: 30,
							items:[	
								{
									xtype: 'label',													
									text: 'Поступило',
									style:'margin-left: 10px; margin-top: 5px; '
								},
								{									
									layout:'form',
									style:'margin-left: 5px; margin-top: 5px; color: blue; font-weight: bold;border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
									width: 60,
									items:[
										{
											id: 'RRW_new',
											xtype: 'label',
											text: '0'
										}					
									]
								}
							]
						},
						{							
							layout:'column',
							border:true,
							style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
							width: 150,
							height: 30,
							items:[	
								{
									xtype: 'label',													
									text: 'Выбыло',
									style:'margin-left: 10px; margin-top: 5px; '
								},
								{									
									layout:'form',
									style:'margin-left: 5px; margin-top: 5px; color: blue; font-weight: bold;border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
									width: 60,
									items:[
										{
											id: 'RRW_quit',
											xtype: 'label',
											text: '0'
										}					
									]
								}
							]
						},
						{							
							layout:'column',
							border:true,
							style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
							width: 150,
							height: 30,
							items:[	
								{
									xtype: 'label',													
									text: 'В отделение',
									style:'margin-left: 10px; margin-top: 5px; '
								},
								{									
									layout:'form',
									style:'margin-left: 5px; margin-top: 5px; color: blue; font-weight: bold;border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
									width: 60,
									items:[
										{
											id: 'RRW_to_profile_section',
											xtype: 'label',
											text: '0'
										}					
									]
								}
							]
						},
						{							
							layout:'column',
							border:true,
							style: 'border-top: 1px solid #ffffff; border-left: 1px solid  #ffffff; border-bottom: 1px solid #99bbe8;border-right: 1px solid #99bbe8; padding: 2px; ',
							width: 150,
							height: 30,
							items:[	
								{
									xtype: 'label',													
									text: 'Умерло',
									style:'margin-left: 10px; margin-top: 5px; '
								},
								{									
									layout:'form',
									style:'margin-left: 5px; margin-top: 5px; color: blue; font-weight: bold;border-top: 1px solid #99bbe8; border-left: 1px solid #99bbe8 ; border-bottom: 1px solid #ffffff;border-right: 1px solid #ffffff;',
									width: 60,
									items:[
										{
											id: 'RRW_dead',
											xtype: 'label',
											text: '0'
										}					
									]
								}
							]
						}
					]
				}

			]
		});
		
		sw.Promed.swReanimatRegistryWindow.superclass.initComponent.apply(this, arguments);
		
	},
	
	listeners: {
	   /*
		'beforeShow': function(win) {
			if (String(getGlobalOptions().groups).indexOf('ReanimatRegistry', 0) < 0 && getGlobalOptions().CurMedServiceType_SysNick != 'minzdravdlo')
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «'+ win.title +'»');
				return false;
			}
		},
        */
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('ReanimatRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('ReanimatRegistryFilterForm').doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('ReanimatORW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('ReanimatRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		
		sw.Promed.swReanimatRegistryWindow.superclass.show.apply(this, arguments);

		this.ReanimatRegistrySearchFrame.addActions({   //!!!!!!!!!!!!!!буду переделывать
			name:'person_register_out', 
			text:lang['isklyuchit_iz_registra'], 
			tooltip: lang['isklyuchit_iz_registra'],
			iconCls: 'delete16',
			handler: function() {
				this.openWindow('out');
				//Ext.Msg.alert('Исключение из регистра', 'Исключение из регистра временно отключено.');
			}.createDelegate(this)
		});
		//++ 
		this.ReanimatRegistrySearchFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});
		

		var base_form = this.findById('ReanimatRegistryFilterForm').getForm();
		var grid = this.ReanimatRegistrySearchFrame.getGrid();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		//this.findById('ReanimatORW_SearchFilterTabbar').setActiveTab(0);
		
		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		
		base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		/*if ( String(getGlobalOptions().groups).indexOf('ReanimatRegistry', 0) >= 0 ) {
			base_form.findField('AttachLpu_id').setDisabled(false);
		} else {
			base_form.findField('AttachLpu_id').setDisabled(true);
		}*/
		
		this.doLayout();
		
		base_form.findField('PersonRegisterType_id').setValue(1);  //установка значения комбо типов поиска: всех / неисключонных / исключонных, а вовсе не тип регистра
		
		base_form.findField('PersonRegisterRecordType_id').getEl().parent().parent().parent().setVisible(false); //BOB - 16.10.2017 прячу ненужноен поле с лейблом "Записи регистра"
		//this.doSearch({firstLoad: true});
		
		//Сводная информация по началам, окончаниям и исходам реанимационных периодов
		var CurrDate = getGlobalOptions().date;
		this.findById('RRW_AliveDead_label').setText('За сутки на ' + CurrDate + ' - 10:00');
		CurrDate = CurrDate.substr(6, 4) + '-'+  CurrDate.substr(3, 2) + '-'+  CurrDate.substr(0, 2); 
		
		var win = this;
		
		
		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=ReanimatRegister&m=RRW_NSI',
			data: { BeginDate: CurrDate,
					EndDate:CurrDate},
			success: function(response) {
        		var RRW_NSI = Ext.util.JSON.decode(response);
				console.log('BOB_RRW_NSI=',RRW_NSI); 

				//загрузка справочника видов шкал
				var Datas =  [];
				for (var i in RRW_NSI.EvnScaleType) {
					Datas[i]= [ RRW_NSI.EvnScaleType[i].ScaleType_Name,   RRW_NSI.EvnScaleType[i].ScaleType_SysNick,   RRW_NSI.EvnScaleType[i].ScaleType_id ];
				};
				win.findById('RRW_EvnScaleType').getStore().loadData(Datas);
				//загрузка справочника видов Реанимационных мероприятий
				Datas =  [];
				for (var i in RRW_NSI.ReanimatActionType) {
					Datas[i]= [ RRW_NSI.ReanimatActionType[i].ReanimatActionType_Name,   RRW_NSI.ReanimatActionType[i].ReanimatActionType_SysNick,   RRW_NSI.ReanimatActionType[i].ReanimatActionType_id ];
				};
				win.findById('RRW_ReanimatActionType').getStore().loadData(Datas);
				//загрузка справочника медикаментов
				Datas =  [];
				for (var i in RRW_NSI.ReanimatDrug) {
					Datas[i] = [RRW_NSI.ReanimatDrug[i].ReanimDrugType_Name, RRW_NSI.ReanimatDrug[i].ReanimDrugType_id];
				}
				win.findById('RRW_RA_DrugNames').getStore().loadData(Datas);
				//загрузка справочника ЛПУ нахлждения в реанимации
				Datas =  [];
				for (var i in RRW_NSI.ReanimatLpu) {
					Datas[i] = [RRW_NSI.ReanimatLpu[i].Lpu_Nick, RRW_NSI.ReanimatLpu[i].Lpu_id];
				}
				win.findById('RRW_ReanimatLpu').getStore().loadData(Datas);
				
				
				
				
			}, 
			error: function() {
				alert("При обработке запроса на сервере произошла ошибка!");
			} 
		});	
		
		
		
		$.ajax({
			mode: "abort",
			type: "post",
			async: false,
			url: '/?c=ReanimatRegister&m=getAliveDead',
			data: { BeginDate: CurrDate,
					EndDate:CurrDate,
					Lpu_id: null},   //BOB - 28.12.2018
			success: function(response) {
				var params = Ext.util.JSON.decode(response);
//				console.log('BOB_params1=',params); 
				win.findById('RRW_at_the_beginning').setText(params[0]['at_the_beginning']);
				win.findById('RRW_at_the_end').setText(params[0]['at_the_end']);
				win.findById('RRW_new').setText(params[0]['new']);
				win.findById('RRW_quit').setText(params[0]['quit']);
				win.findById('RRW_to_profile_section').setText(params[0]['to_profile_section']);
				win.findById('RRW_dead').setText(params[0]['dead']);
			}, 
			error: function() {
				alert("При обработке запроса на сервере произошла ошибка!");
			} 
		});	

		
	}
	
	
	

});