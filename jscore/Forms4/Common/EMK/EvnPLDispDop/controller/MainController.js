Ext6.define('common.EMK.EvnPLDispDop.controller.MainController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnPLDispDop13MainController',
	bindings: {
		onIsEndStage_Change: '{EvnPLDispDop13_IsEndStage}',
		onAction: '{action}',
		onEvnPLDispDop13_IsDisp: '{EvnPLDispDop13_IsDisp}',
	},
	onEvnPLDispDop13_IsDisp: function (value, none, binding) {
		var view = this.getView(),
			groupBtn = view.queryById('HealthKind_id'),
			button = groupBtn.items.findBy(function(btn) {
				return btn.value == groupBtn.getValue();
			});
		if(button && button.hidden) groupBtn.setValue(null);
	},
	onIsEndStage_Change: function(value, none, binding) {
		var vm = this.getViewModel();
		if(vm.get('action') != 'add')
			vm.set('action', value == 2 ? 'view':'edit');
	},
	onAction: function(value, none, binding) {
		var view = this.getView();
		view.AccordionPanel.items.getRange().forEach(function(panel) {
			if(panel.setReadOnly) panel.setReadOnly(value == 'view');
		});
	},
	setParams: function(params) {//Здесь мы можем получить только Person_id, Server_id и параметр типа object_id=object_value (это EvnPLDispDop13_id)
		var contr = this,
			vm = this.getViewModel(),
			view = this.getView();
		// @TODO Что это за херня я так и не разобрался, надо дождаться Игоря
		/*view.getViewModel().setParams(params);
		view.PrescrPanel.setParams(vm.getData());*/
		vm.setParams(params);
		vm.setParams();
		view.getViewModel().setParams(params);
		view.swEMDPanel.setParams({
			EMDRegistry_ObjectName: 'EvnPLDisp',
			EMDRegistry_ObjectID: vm.get('EvnPLDispDop13_id')
		});
	},
	getParams: function() {
		var contr = this,
			vm = this.getViewModel(),
			view = this.getView();
		return vm.get('params');
	},
	doCallback: function(options) {
		if (options && typeof options.callback == 'function') {
			options.callback();
		}
	},
	checkStages: function() {
		var contr = this,
			view = this.getView(),
			vm = this.getViewModel();
	},
	loadData: function(options) {//вызов из ЭМК. next: tabchange , loadForm
		var contr = this,
			vm = this.getViewModel(),
			view = this.getView(),
			base_form = view.MainForm.getForm();
			
		view.options = options;
		var components = view.query('combobox');
		if(view.formStatus == 'save'){
			view.mask('Дождитесь сохранения формы');
			setTimeout(function() {
				contr.loadData(options);
			}, 2000);
			return false;
		}
		loadDataLists(view, components, function() {// загружаем справочники
			Ext6.suspendLayouts();

			view.AccordionPanel.reset();
			view.MainForm.reset();
			
			if (options.dataToLoad && options.dataToLoad.evnPLDispDop13Data) {
				view.MainForm.getForm().setValues(options.dataToLoad.evnPLDispDop13Data);
			}
			
			let EvnPLDispDop13_id = vm.get('EvnPLDispDop13_id'); // base_form.findField('EvnPLDispDop13_id').getValue();
			let EvnPLDispDop13_fid = base_form.findField('EvnPLDispDop13_fid').getValue();
			let DispClass_id = base_form.findField('DispClass_id').getValue();
			
			view.tabPanel.removeAll();
			vm.reset();
			vm.setParams();//восстанавливаем утерянные от reset значения
			var panel = Ext6.create('Ext6.Panel', {
				title: 'Этап 1',
				border: false,
				html: '',
				EvnPLDisp_id: EvnPLDispDop13_fid ? EvnPLDispDop13_fid : EvnPLDispDop13_id
			});
			view.tabPanel.add(panel);
			
			var panel2 = Ext6.create('Ext6.Panel', {
				title: 'Этап 2',
				border: false,
				html: '',
				hidden: DispClass_id!=2 || Ext6.isEmpty(EvnPLDispDop13_fid),
				EvnPLDisp_id: EvnPLDispDop13_id //vm.get('DispClass_id')=="2" ? vm.get('EvnPLDispDop13_fid') : vm.get('EvnPLDispDop13_id')
			});
			view.tabPanel.add(panel2);
			
			view.tabPanel.setActiveTab(EvnPLDispDop13_fid ? 1 : 0);
			
			Ext6.resumeLayouts(true);
			
			if(vm.get('action') == 'add') {
				view.tabToolPanel.show();
				//view.AccordionPanel.expandPanel(view.ConsentPanel);
			} else {
				view.tabToolPanel.hide();//панель-заголовок для режима согласия. Перекрывает вкладки
				//view.AccordionPanel.expandPanel(view.AnketPanel);
			}
		});
		
	},
	loadForm: function(options) {//from tabchange. next: onLoadForm
		var contr = this,
			vm = this.getViewModel(),
			view = this.getView(),
			base_form = view.MainForm.getForm(),
			thisdate = new Date();
		view.mask(LOADING_MSG);
		contr.doCallback(options);
		if (!options) options = {};
		
		//~ view.MainForm.reset();
		//~ vm.setParams();//восстанавливаем утерянные от reset значения
		vm.set('action', (Number(vm.get('EvnPLDispDop13_id'))>0) ? 'edit' : 'add' );
		vm.set('inWowRegister', false);
		vm.set('blockSaveDopDispInfoConsent', false);
		vm.set('saveDopDispInfoConsentAfterLoad', false);
		//vm.set('PersonAgree', false);
		//~ if(Ext6.isEmpty(vm.get('DispClass_id'))) vm.set('DispClass_id',1);//не может быть неопределенного DispClass_id. По умолчанию дисп-я 1 этап.
		base_form.findField('EvnPLDispDop13_consDate').setMaxValue(thisdate);
		
		//vm.set('EvnPLDispDop13_consDate', thisdate.format('d.m.Y'));//убрал в default value = new date
		view.ConsentPanel.ConsentForm.getForm().findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
		
		//~ if(view.ownerWin) {
			//~ vm.set('PersonEvn_id', view.ownerWin.PersonInfoPanel.getFieldValue('PersonEvn_id'));
		//~ }

		//~ if (options.dataToLoad && options.dataToLoad.evnPLDispDop13Data) {
			//~ view.MainForm.getForm().setValues(options.dataToLoad.evnPLDispDop13Data);
		//~ }
		
		if( vm.get('action')!='add' ) {
			view.MainForm.getForm().load({
				url: '/?c=EvnPLDispDop13&m=loadEvnPLDispDop13EditForm', 
				params: {
					EvnPLDispDop13_id: vm.get('EvnPLDispDop13_id'),
					isExt6: 1
				},
				failure: function(form, action) {
					contr.doCallback(options);
				},
				success: function(form, action) {
					contr.doCallback(options);
					if (action.response && action.response.responseText) {
						var data = Ext6.JSON.decode(action.response.responseText);
						contr.onLoadForm(data);
					}
				}
			});
		} else contr.onLoadForm();//все равно надо грузить таблицу согласий
	},
	onLoadForm: function(data) {//после loadForm
		var contr = this,
			view = this.getView(),
			vm = this.getViewModel();

		if (data && data[0]) {
			//~ view.MainForm.getForm().setValues(data[0]);
			
			data[0].EvnPLDispDop13_IsSuspectZNO = data[0].EvnPLDispDop13_IsSuspectZNO == 2;
			vm.setData(data[0]);
			//vm.set('EvnPLDispDop13_IsSuspectZNO', data[0].EvnPLDispDop13_IsSuspectZNO == 2);
			
			if(data[0].TherapistViewData && data[0].TherapistViewData[0].OnkoDiag_Code) {
				vm.set('Terapevt_OnkoDiag_Code', data[0].TherapistViewData[0].OnkoDiag_Code);
			}

			if (view.ownerWin.PersonInfoPanel && view.ownerWin.PersonInfoPanel.checkIsDead()) {
				vm.set('accessType','view');
			} else {
				vm.set('accessType',data[0].accessType);
			}
		}
		
		if(view.ownerWin && view.ownerWin.PersonInfoPanel) {
			vm.set('PersonEvn_id', view.ownerWin.PersonInfoPanel.getFieldValue('PersonEvn_id'));
			vm.set('Person_Age', view.ownerWin.PersonInfoPanel.getFieldValue('Person_Age'));
			vm.set('Sex_id', view.ownerWin.PersonInfoPanel.getFieldValue('Sex_id'));
			vm.set('Sex_Name', view.ownerWin.PersonInfoPanel.getFieldValue('Sex_Name'));
		}

		view.bottomPanel.setParams({
			Evn_id: vm.get('EvnPLDispDop13_id'),
			EvnClass_id: vm.get('EvnClass_id'),
			Person_id: vm.get('Person_id'),
			Server_id: vm.get('Server_id'),
			PersonEvn_id: vm.get('PersonEvn_id'),
			userMedStaffFact: view.ownerWin.userMedStaffFact
		});
		
		view.ConsentPanel.getGrid().getStore().load();//win.unmask будет на onLoad
	},
	onLoadConsentGrid: function(){
		var view = this.getView(),
			vm = this.getViewModel(),
			codes = [],
			panel = null;
		view.unmask();
		
		//пост-обработка списка согласий-услуг:
		vm.getStore('ConsentStore').each(function(rec) {
			var code = rec.get('SurveyType_Code');
			if(	!Ext6.isEmpty(rec.get('OutUsluga_id')) 
				&& (Ext6.isEmpty(vm.get('EvnPLDispDop13_setDate')) || (parseDate(vm.get('EvnPLDispDop13_setDate')) > parseDate(rec.get('OutUsluga_Date'))))
			) {//если есть выполненная ранее услуга
				rec.set('DopDispInfoConsent_IsAgree', false);
				rec.set('DopDispInfoConsent_IsEarlier', true);
			} else {
				//rec.set('OutUsluga_id', null);
			}
			
			if(!Ext6.isEmpty(rec.get('EvnUslugaDispDop_id'))) {
				vm.set('MedPersonal_SurveyType_Code'+code, CaseLettersPersonFio(rec.get('EvnUslugaDispDop_MedPersonalFio')));
				vm.set('LpuNick_SurveyType_Code'+code, rec.get('EvnUslugaDispDop_Lpu_Nick'));
				vm.set('Date_SurveyType_Code'+code, rec.get('EvnUslugaDispDop_didDate'));
			}
			if(	!Ext6.isEmpty(code) && !codes.in_array(code) && 
				(rec.get('DopDispInfoConsent_IsAgree') || rec.get('DopDispInfoConsent_IsEarlier'))
				) {
				codes.push(code);//собираем коды чтобы отобразить только нужные разделы на форме
				panel = view.getController().getPanelByCode(code);
				if((panel && panel.getController() && panel.getController().setParams) 
					|| (panel && panel.setParams)) {
					if(panel.setParams)
						panel.setParams(rec);
					else
						panel.getController().setParams(rec);

					if(panel.autoLoad && !panel.isLoaded && panel.load && vm.get('action')!='add') panel.load();
				}
			}
			
		});
		
		view.AccordionPanel.expandPanel( vm.get('action') == 'add' ? view.ConsentPanel : view.AnketPanel);
		
		if( !(codes.in_array(96) || codes.in_array(97)) ) {
			codes.push(7); //ССР нужен в любом случае, как минимум суммарный ССР.
			panel = view.getController().getPanelByCode(7);
			if(panel && panel.getController() && panel.getController().setParams) {
				panel.getController().setParams();
			}
		}
		//панели без привязки к записи из ConsentStore, поэтому отдельный setParams:
		[
			view.FactorRiskPanel,
			view.DeseasePanel
		].forEach(function(pan) {
			if(pan.getController() && pan.getController().setParams)
				pan.getController().setParams();
		});
		
		if(panel.autoLoad && !panel.isLoaded && panel.load && vm.get('action')!='add') panel.load();
		
		vm.set('SurveyType_Codes',codes);
		if(!Ext6.isEmpty(vm.get('EvnPLDispDop13_id'))) {
			this.loadPrescribes();// Загрузка списка направлений (назначений)
		}
		this.checkSpecifics();
	},
	loadPrescribes: function(){
		var view = this.getView(),
			vm = this.getViewModel(),
			s = vm.getStore('ConsentStore'),
			recs = s.getRange();
		if(recs){
			view.PrescrPanel.setParams(vm.getData());
			view.PrescrPanel.loadPrescribes(recs);
		}
	},
	onLoadPrescrGrid: function () {
		this.getView().PrescribePanel.unmask();
	},
	printEvnPLDispDop13: function(print_blank) {
		var contr = this,
			view = this.getView(),
			wnd = view,
			vm = view.getViewModel();
		var print = function () {
			var person_id = vm.get('Person_id');
			var evnpldispdop_setdate = vm.get('EvnPLDispDop13_setDate');
			var evnpldispdop_year = new Date(evnpldispdop_setdate.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1')).getFullYear();
			var template = 'pan_DispCard_2015.rptdesign';
			if(vm.get('EPLDD13_EvnPLDispDop13_consDate') < new Date(2015,3,1))
			{
				template = 'pan_DispCard.rptdesign';
			}
			printBirt({
				'Report_FileName': template,
				'Report_Params': '&paramPerson=' + person_id + '&paramDispClass=1&paramYear=' + evnpldispdop_year,
				'Report_Format': 'pdf'
			});
		};

		/* if ( 'add' == vm.get('action') || 'edit' == vm.get('action') ) {
			 this.doSave({
				 print: true,
				 callback: print
			 });
		 }
		 else if ( 'view' == vm.get('action') ) {
			 print();
		 }*/
		print();
	},
	printEvnPLDispDop13Passport: function(print_blank) {
		var contr = this,
			view = this.getView(),
			wnd = view,
			vm = view.getViewModel();
		var evn_pl_id = vm.get('EvnPLDispDop13_id'),
			server_id = vm.get('Server_id');
		if( !getGlobalOptions().region.nick.inlist([ 'pskov', 'ufa', 'buryatiya' ]) ){
			var dialog_wnd = Ext6.Msg.show({
				msg: langs('Печатать данные в раздел 10. Установленные заболевания?'),
				title: langs('Печать заболеваний'),
				icon: Ext6.MessageBox.QUESTION,
				
				buttons: Ext6.MessageBox.YESNOCANCEL,
				//buttonText: { yes: "От имени пациента", no: 'От имени законного представителя', cancel: "Отмена" },
				fn: function (buttonId) {
					if(buttonId === 'cancel') {
						return;
					} else
					if (buttonId === 'yes') {
						window.open('/?c=EvnPLDispDop13&m=printEvnPLDispDop13Passport&EvnPLDispDop13_id=' + evn_pl_id + '&Server_id=' + server_id + '&printDiag=1', '_blank');
					}
					else {
						window.open('/?c=EvnPLDispDop13&m=printEvnPLDispDop13Passport&EvnPLDispDop13_id=' + evn_pl_id + '&Server_id=' + server_id + '&printDiag=0', '_blank');
					}
				}.createDelegate(this)
			});
		}
		else{ //Для Уфы - всегда выводим диагнозы (printDiag=1)
			window.open('/?c=EvnPLDispDop13&m=printEvnPLDispDop13Passport&EvnPLDispDop13_id=' + evn_pl_id + '&Server_id=' + server_id + '&printDiag=1', '_blank');
		}
	},
	tabchange: function(tabPanel, newCard) {
		var contr = this,
			view = this.getView(),
			vm = view.getViewModel();
		
		vm.set('EvnPLDispDop13_id', newCard.EvnPLDisp_id);
		this.loadForm(view.options);
	},
	getPanelByCode: function(SurveyType_Code) {
		let contr = this,
			view = this.getView(),
			panel = false;
		if(!Ext6.isEmpty(SurveyType_Code)) {
			let panels = view.query('[SurveyType_Code='+SurveyType_Code+']');
			if(!Ext6.isEmpty(panels)) panel = panels[0];
		}
		return panel;
	},
	doSave: function(options) {//Сохранение карты ДВН (по сути завершение диспансеризации, т.к. большинство параметров сохраняется автоматом)
		if ( typeof options != 'object' ) {
			options = {};
		}
		var contr = this,
			view = this.getView(),
			vm = view.getViewModel();//win
		
		var main_form = view.MainForm;

		var base_form = view.MainForm.getForm();

		var doRestore = vm.get('EvnPLDispDop13_IsEndStage') == 2,
			params = vm.getData();
		
		if(!doRestore) {//завершить диспансеризацию
			if ( !main_form.getForm().isValid() )
			{
				let invalid = main_form.getFirstInvalidEl(),
					invalidPanel = invalid.up('swPanel');
				if(!Ext6.isEmpty(invalid) && !Ext6.isEmpty(invalidPanel)) {
					view.AccordionPanel.expandPanel(invalidPanel);
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							invalid.focus(false);
						},
						icon: Ext.Msg.WARNING,
						msg: 'В разделе "'+invalidPanel.title+'" есть незаполненные обязательные поля.',
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
			//проверка заполнения группы здоровья
			this.onEvnPLDispDop13_IsDisp();
			
			if(Ext6.isEmpty(vm.get('HealthKind_id'))) {
				sw.swMsg.alert(ERR_INVFIELDS_TIT, 'Не указана группа здоровья.');
				return;
			}
			/*
			if(!options.ignoreCheckDesease) {
				var deseaseList = [];
				view.DeseasePanel.DeseaseGrid.getStore().getRange().forEach(function(rec) {
					if(rec.get('renderType')=='Подозрение') {
						deseaseList.push(rec.get('renderName').toLowerCase());
					}
				});
				if(deseaseList.length>0) {
					sw.swMsg.show({
						buttons: Ext6.Msg.OKCANCEL,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'ok' ) {
								options.ignoreCheckDesease = 1;
								contr.doSave(options);
							}
						}.createDelegate(this),
						icon: Ext6.MessageBox.QUESTION,
						msg: 'Внимание! У пациента есть:<br>'+deseaseList.join(',<br>')+'.<br>Вы действительно хотите завершить диспансеризацию?',
						title: 'Предупреждение'
					});
					return;
				}
			}*/
			if(view.queryById('EvnPLDispDop13_IsDisp').getValue() && view.ownerWin && view.ownerWin.PersonInfoPanel && view.ownerWin.PersonInfoPanel.action_New_PersonDisp) {
				if(getGlobalOptions().region.nick.inlist([ 'krasnoyarsk' ])){
					if(!Ext6.isEmpty(options.checkAttributeforLpuSection)){
						view.ownerWin.PersonInfoPanel.action_New_PersonDisp();
					}
				}else{
					view.ownerWin.PersonInfoPanel.action_New_PersonDisp();
				}
			}
			//vm.set('EvnPLDispDop13_IsTwoStage', 1);//не нужен второй этап
			
			//vm.set('EvnPLDispDop13_IsEndStage', 2);//было 1 - ставим 2 - завершение
			params.checkAttributeforLpuSection = (!Ext6.isEmpty(options.checkAttributeforLpuSection)) ? options.checkAttributeforLpuSection : 0;
			options.checkAttributeforLpuSection = 0;
		} else {//отменить завершение
			//vm.set('EvnPLDispDop13_IsEndStage', 1);//было 2 - ставим 1 - отмена (этап открыт)
			params.checkAttributeforLpuSection=1;
		}
		
		/// СОХРАНЕНИЕ КАРТЫ ДВН
		/*
		//настройки в случае кнопки "завершить диспансеризацию"
		vm.set('EvnPLDispDop13_IsTwoStage', 1);//не нужен второй этап
		//завершение
		if(vm.get('EvnPLDispDop13_IsEndStage') == 2) {
			vm.set('EvnPLDispDop13_IsEndStage', 1);
		} else {
			vm.set('EvnPLDispDop13_IsEndStage', 2);
		}*/
		
		//~ if (base_form.findField('PayType_id').disabled) {
			//~ params.PayType_id = base_form.findField('PayType_id').getValue();
		//~ }
		
		view.mask('Подождите, идет сохранение...');
		
		params.EvnPLDispDop13_IsDisp = view.queryById('EvnPLDispDop13_IsDisp').getValue() ? '2' : '1';
		params.EvnPLDispDop13_IsSuspectZNO = view.queryById('EvnPLDispDop13_IsSuspectZNO').getValue() ? '2' : '1';
		if(!doRestore) {
			params.EvnPLDispDop13_IsTwoStage = '1';
			params.EvnPLDispDop13_IsEndStage = '2';
		} else {
			params.EvnPLDispDop13_IsEndStage = '1';
		}
		if(options.ignoreCheckDesease) params.ignoreCheckDesease = 2;
		
		
		Ext6.Ajax.request({
			url: '/?c=EvnPLDispDop13&m=saveEvnPLDispDop13Ext6',
			params: params,
			failure: function (response, opts) {
				view.unmask();
			},
			success: function (response, opts) {
				view.unmask();
				
				if (response && response.responseText) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					
					if (!response_obj.success) {
						if (response_obj.Alert_Msg) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										switch (response_obj.Error_Code) {
											case 110:
												options.checkAttributeforLpuSection = 2;
												vm.set('EvnPLDispDop13_IsEndStage', 1);
												break;
										}

									contr.doSave(options);

									}else{
										switch (response_obj.Error_Code) {
											case 110:
												options.checkAttributeforLpuSection = 1;
												vm.set('EvnPLDispDop13_IsEndStage', 1);
												contr.doSave(options);
												break;
										}
									}
								}.createDelegate(this),
								icon: Ext6.Msg.QUESTION,
								msg: response_obj.Alert_Msg,
								title: langs('Продолжить сохранение?')
							});
						}else if(!Ext6.isEmpty(response_obj.Error_Msg)){
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
							return;
						}else {
							sw.swMsg.alert(langs('Ошибка'), 'При сохранении произошли ошибки.');
							return;
						}
					} else {
						if(!doRestore) {
							vm.set('EvnPLDispDop13_IsTwoStage', 1);//не нужен второй этап
							vm.set('EvnPLDispDop13_IsEndStage', 2);//было 1 - ставим 2 - завершение
						} else {
							vm.set('EvnPLDispDop13_IsEndStage', 1);//было 2 - ставим 1 - отмена (этап открыт)
						}
						view.ownerWin.loadTree();
						if(!Ext6.isEmpty(params.checkAttributeforLpuSection) && params.checkAttributeforLpuSection==2) {
							contr.loadForm();
						}
					}
				}
			}
		});
		/*
		view.ResultForm.getForm().submit({
			url: '/?c=EvnPLDispDop13&m=saveEvnPLDispDop13Ext6',
			
			failure: function(result_form, action) {
				view.unmask();
			},
			params: params,
			success: function(result_form, action) {
				view.unmask();
				if (action.result)
				{
					//view.ownerWin.loadEmkViewPanel('Person', vm.get('Person_id'), '');
					view.ownerWin.loadTree();
				}
				else
				{
					Ext6.Msg.alert('Ошибка', 'При сохранении произошли ошибки');
				}
			}
		});*/
	},
	openSpecificsWindow: function(sopdiag) {
		var contr = this,
			view = this.getView(),
			TerapevtForm = view.TerapevtPanel.DataForm.getForm(),
			vm = view.getViewModel();

		var params = {};
		var wnd = null;
		
		params.EvnDiag_id = sopdiag;
		params.MorbusOnko_pid = TerapevtForm.findField('EvnVizitDispDop_id').getValue();
		wnd = 'swMorbusOnkoEditWindow';
		
		if (wnd === null) {
			return false;
		}

		params.Person_id = vm.get('Person_id');
		params.PersonEvn_id = vm.get('PersonEvn_id');
		params.Server_id = vm.get('Server_id');
		params.allowSpecificEdit = true;
		params.action = (vm.get('action') !== 'view') ? 'edit' : 'view';
		getWnd(wnd).show(params);
	},
	checkSpecifics: function(force) {
		var view = this.getView(),
			vm = view.getViewModel(),
			Terapevt_OnkoDiag_Code = null;
		
		if(view.SpecificsOnkoPanel) {
			view.SpecificsOnkoPanel.destroy();
		}
		
		Terapevt_OnkoDiag_Code = vm.get('Terapevt_OnkoDiag_Code');
		if(Terapevt_OnkoDiag_Code) {
			view.SpecificsOnkoPanel = Ext6.create('swSpecificPanel', {
				cls: 'emk-morbus-onko-panel',
				userCls: 'emk-morbus-onko-panel',
				EvnDiag_id: null,
				specificTitle: 'ОНКОЛОГИЯ <span style="'+'font-weight: normal;'+'">' + Terapevt_OnkoDiag_Code + '</span>',
				handler: function() {
					view.getController().openSpecificsWindow();
				}
			});
			
			var ind = view.AccordionPanel.getPositionNumber('ProtocolTerapevtPanel');
			view.AccordionPanel.insert(ind+1, view.SpecificsOnkoPanel);
		}
	},
	reloadListMorbus: function() {
		var contr = this,
			view = this.getView(),
			vm = this.getViewModel();
		
		log('reloadListMorbus');
		
		Ext6.Ajax.request({
			url: '/?c=EvnPLDispDop13&m=checkSpecifics',
			params: {
				EvnPLDispDop13: vm.get('EvnPLDispDop13_id')
			},
			success: function(response) {
				var result = Ext6.JSON.decode(response.responseText);
				if (result[0].listMorbus) {
					view.listMorbus = result[0].listMorbus;
				} else {
					view.listMorbus = {};
				}
				contr.checkSpecifics();
			}
		});
	},
});