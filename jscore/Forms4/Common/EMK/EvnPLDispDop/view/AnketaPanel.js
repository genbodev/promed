Ext6.define('common.EMK.EvnPLDispDop.view.AnketaPanel', {
	extend: 'swPanel',
	requires: [
		//'common.EMK.EvnPLDispDop.controller.AnketaController',
	],
	//~ alias: 'widget.EvnPLDispDop_AnketaPanel',
	userCls: 'DVN-anketa panel-with-tree-dots accordion-panel-window',
	title: 'Опрос (анкетирование)',
	//~ controller: 'EvnPLDispDop13AnketaController',
	ownerPanel: {},
	bodyPadding: 10,
	isLoaded: false,
	listeners: {
		expand: function(panel, eOpts) { //прогрузить раздел
			this.onExpand();
		},
		collapse: function(panel, eOpts) { //сохранить раздел
			var vm = this.ownerPanel.getViewModel();
			vm.set('anketa_edit_disabled',true);
		},
		focusLeave: function(v, event, eOpts) {
			this.ownerPanel.formStatus = 'save';
			this.doSave();
		}
	},
	onExpand: function() {
		this.load(); // if(!this.store.isLoaded()) this.load();
		this.ownerPanel.AccordionPanel.collapseOtherPanels(this);
	},
	getField: function(name) {
		return this.AnketaForm.getForm().findField(name);
	},
	getStore: function() {
		return this.store;
	},
	setParams: function(record) {
		var view = this,
			vm = view.ownerPanel.getViewModel(),
			data_form = view.DataForm.getForm(),
			cd = new Date(),
			cdate = cd.dateFormat('d.m.Y'),
			ctime = cd.dateFormat('H:i'),
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		view.record = record;
		view.getStore().loadCount = 0;
		
		view.isLoaded = false;
		
		data_form.setValues({
			'UslugaComplex_id': record.get('UslugaComplex_id'),
			'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id'),
			'DopDispInfoConsent_id': record.get('DopDispInfoConsent_id'),
			'EvnVizitDispDop_pid': vm.get('EvnPLDispDop13_id'),
			'PersonEvn_id': vm.get('PersonEvn_id'),
			'Server_id': vm.get('Server_id'),
			
			'EvnUslugaDispDop_setDate': cdate,
			'EvnUslugaDispDop_setTime': ctime,
			'EvnUslugaDispDop_didDate': cdate,
			'EvnUslugaDispDop_didTime': ctime,
			'EvnUslugaDispDop_disDate': cdate,
			'EvnUslugaDispDop_disTime': ctime,
			
			'Diag_id': 10944,//Z10.8 Рутинная общая проверка здоровья
			'LpuSection_id': !Ext6.isEmpty(msf.LpuSection_id) ? msf.LpuSection_id : null,
			'MedStaffFact_id': !Ext6.isEmpty(msf.MedStaffFact_id) ? msf.MedStaffFact_id : null,
			'MedPersonal_id': !Ext6.isEmpty(msf.MedPersonal_id) ? msf.MedPersonal_id : null,
			'ExaminationPlace_id': 1
		});
	},
	openModalForm() {
		let view = this,
			DataForm = view.DataForm.getForm(),
			vm = view.ownerPanel.getViewModel();
		if(DataForm.findField('EvnUslugaDispDop_id').getValue()) {
			getWnd("swEvnUslugaDispDop13EditWindowExt6").show({
				needLoad: true,
				params: {
					title: "Анкетирование",
					blocktype: 'Anketa',
					EvnPLDispDop13_id: vm.get('EvnPLDispDop13_id'),
					EvnUslugaDispDop_id: DataForm.findField('EvnUslugaDispDop_id').getValue(),
					UslugaComplex_Date: vm.get('EvnPLDispDop13_consDate')
				},
				callback: function (data) {

				}
			});
		}
	},
	load: function() {
		var view = this,
			data_form = view.DataForm.getForm(),
			vm = view.ownerPanel.getViewModel(),
			EvnPLDisp_id = vm.get('EvnPLDispDop13_id');
		if(view.isLoaded) return;
		view.isLoaded = true;
		
		if(EvnPLDisp_id) {
			view.mask('Загрузка...');
			
			view.store.load({
				params: {
					EvnPLDisp_id: EvnPLDisp_id
				},
				callback: function() { view.makeAnketa(); }
			});
			
			let EvnUslugaDispDop_id = data_form.findField('EvnUslugaDispDop_id').getValue();
			if(!Ext6.isEmpty(EvnUslugaDispDop_id)) {
				data_form.load({
					url: '/?c=DopDispQuestion&m=loadDopDispQuestionEditWindow',
					failure: function() {
						view.unmask();
					}.createDelegate(this),
					params: {
						EvnUslugaDispDop_id: EvnUslugaDispDop_id,
						ExtVersion: 6
					},
					success: function(result_form, action) {
						view.unmask();
						
						/*var responseObj = new Object();
						
						if ( action && action.response && action.response.responseText ) {
							responseObj = Ext.util.JSON.decode(action.response.responseText);

							if ( responseObj.length > 0 ) {
								responseObj = responseObj[0];
								
							}
						}*/
					}
				});
			}
		}
	},
	updateStatus: function() {
		var view = this,
			win = view.ownerPanel,
			vm = win.getViewModel(),
			base_form = view.DataForm.getForm(),
			dt = base_form.findField('EvnUslugaDispDop_didDate').getValue(),
			code = view.SurveyType_Code,
			msf = null;
		if (!Ext6.isEmpty(view.ownerPanel.ownerWin)) {
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		}
		if(	Ext6.isEmpty(vm.get('MedPersonal_SurveyType_Code'+code)) &&
			Ext6.isEmpty(vm.get('LpuNick_SurveyType_Code'+code)) &&
			Ext6.isEmpty(vm.get('Date_SurveyType_Code'+code)) &&
			msf
		) {
			view.record.set('EvnUslugaDispDop_MedPersonalFio', msf.MedPersonal_FIO);
			view.record.set('EvnUslugaDispDop_Lpu_Nick', Ext6.isEmpty(msf.Lpu_Nick) ? '' : msf.Lpu_Nick);
			view.record.set('EvnUslugaDispDop_didDate', dt);
			
			vm.set('MedPersonal_SurveyType_Code'+code, CaseLettersPersonFio(view.record.get('EvnUslugaDispDop_MedPersonalFio')));
			vm.set('LpuNick_SurveyType_Code'+code, view.record.get('EvnUslugaDispDop_Lpu_Nick'));
			let ddt = view.record.get('EvnUslugaDispDop_didDate');
			vm.set('Date_SurveyType_Code'+code, Ext6.isDate(ddt) ? Ext6.Date.format(ddt,'d.m.Y') : ddt);
		}
	},
	recalcAllQuestion: function(needCalcSSRisk) {
		var view = this,
			vm = view.ownerPanel.getViewModel();
		
		var alcodepend = 0,
			alcosum = 0,
			isPohud = false,
			isPohudDepend = false,
			isPohudDependTwo = false,
			isIrrational = false,
			isIrrationalTwo = false,
			isOnko = false,
			isOnkoTwo = false,
			isOnkoThree = false,
			defaultValue = null,
			vmval = null;//только для присваивания внутри case
		
		if(!Ext6.isEmpty(vm.get('Date_SurveyType_Code2'))) {
			defaultValue = 1;
		}
		vm.setData({
			EvnPLDispDop13_IsSmoking: defaultValue,
			EvnPLDispDop13_IsStenocard: defaultValue,
			EvnPLDispDop13_IsDoubleScan: defaultValue,
			EvnPLDispDop13_IsTIA: defaultValue,
			EvnPLDispDop13_IsSpirometry: defaultValue,
			EvnPLDispDop13_IsLungs: defaultValue,
			EvnPLDispDop13_IsHeartFailure: defaultValue,
			EvnPLDispDop13_IsIrrational: defaultValue,
			EvnPLDispDop13_IsUseNarko: defaultValue,
			EvnPLDispDop13_IsBrain: defaultValue,
			EvnPLDispDop13_IsTub: defaultValue,
			EvnPLDispDop13_IsEsophag: defaultValue,
			EvnPLDispDop13_IsRiskAlco: defaultValue,
			EvnPLDispDop13_IsLowActiv: defaultValue,
			EvnPLDispDop13_IsAlcoDepend: defaultValue,
			EvnPLDispDop13_IsTopGastro: defaultValue,
			EvnPLDispDop13_IsBotGastro: defaultValue,
			EvnPLDispDop13_IsOncology: defaultValue
		});
		view.store.getRange().forEach(function(rec) {
			vmval = null;
			switch (Number(rec.get('QuestionType_id'))) {
				case 13:
				case 107:
				case 108:
				case 149:
				case 150:
				case 691:
				case 692:
				case 724:
					// Автоматически указывать значение «Имеется», если при анкетировании на вопрос №13 или №14 сохранен ответ «Да»
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsStenocard', 2);
					}
					break;

				case 14:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsStenocard', 2);
						vm.set('EvnPLDispDop13_IsDoubleScan', 2);
					}
					break;

				case 693:
				case 694:
				case 695:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsTIA', 2);
					}
					break;

				case 696:
				case 697:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsSpirometry', 2);
					}
					break;

				case 698:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsLungs', 2);
					}
					break;

				case 729:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsHeartFailure', 2);
					}
					break;

				case 707:
				case 708:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsIrrational', 2);
					}
					break;

				case 709:
					if( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsUseNarko', 2);
					}
					break;

				case 701:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						isPohud = true;
					}
					break;

				case 699:
				case 700:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						isPohudDepend = true;
					}
					break;

				case 702:
				case 703:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						isPohudDependTwo = true;
					}
					break;

				case 733:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 1) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 1) ) {
						isIrrational = true;
					}
					break;

				case 734:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 1) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 1) ) {
						isIrrationalTwo = true;
					}
					break;

				case 742:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						isOnko = true;
					}
					break;

				case 743:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 1) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 1) ) {
						isOnkoTwo = true;
					}
					break;

				case 744:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						isOnkoThree = true;
					}
					break;

				case 15:
				case 16:
				case 17:
				case 18:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsBrain', 2);
						vm.set('EvnPLDispDop13_IsDoubleScan', 2);
					}
					break;

				case 109:
				case 110:
				case 111:
				case 112:
				case 151:
				case 152:
				case 153:
				case 726:
				case 727:
				case 728:
					// Автоматически указывать значение «Имеется», если при анкетировании хотя бы на один из вопросов №14-18 сохранен ответ «Да»
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsDoubleScan', 2);
					}
					break;

				case 19:
				case 20:
				case 113:
				case 114:
					// Автоматически указывать значение «Имеется», если при анкетировании хотя бы на один из вопросов №19-20 сохранен ответ «Да»
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsTub', 2);
					}
					break;

				case 21:
				case 22:
				case 115:
				case 116:
				case 119:
					// Автоматически указывать значение «Имеется», если при анкетировании хотя бы на один из вопросов №21-22 сохранен ответ «Да»
					if ( age >= 50 && (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsEsophag', 2);
					}
					break;

				case 26:
				case 120:
				case 155:
				case 704:
				case 730:
				case 846:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsSmoking', 2);
					}
					break;

				case 27:
				case 28:
				case 29:
				case 30:
				case 123:
				case 124:
				case 125:
				case 126:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 2) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 2) ) {
						vm.set('EvnPLDispDop13_IsRiskAlco', 2);
						alcodepend++;
					}
					break;

				case 31:
				case 127:
					// на вопрос №31 сохранен ответ «до 30 минут»
					if ( rec.get('DopDispQuestion_ValuesStr') == 1 )  {
						vm.set('EvnPLDispDop13_IsLowActiv', 2);
					}
					break;

				case 172:
				case 706:
				case 735:
					if ( (rec.get('AnswerType_id') == 1 && rec.get('DopDispQuestion_IsTrue') == 1) || (rec.get('AnswerType_id') == 3 && rec.get('DopDispQuestion_ValuesStr') == 1) ) {
						vm.set('EvnPLDispDop13_IsLowActiv', 2);
					}
					break;

				case 710:
				case 711:
				case 712:
					if (parseInt(rec.get('DopDispQuestion_ValuesStr'))) {
						alcosum += parseInt(rec.get('DopDispQuestion_ValuesStr')) - 64;
					}
					break;
			}
			
			if(vm.get('alcodepend') > 3) {
				vm.set('EvnPLDispDop13_IsAlcoDepend', 2);
			}
			
			var sex_id = vm.get('Sex_id');
			if (alcosum >= 4 || (sex_id == 2 && alcosum >= 3)) {
				vm.set('EvnPLDispDop13_IsRiskAlco', 2);
			}

			if (isPohud && isPohudDepend) {
				vm.set('EvnPLDispDop13_IsTopGastro', 2);
			}

			if (isPohud && isPohudDependTwo) {
				vm.set('EvnPLDispDop13_IsBotGastro', 2);
			}

			if (isIrrational && isIrrationalTwo) {
				vm.set('EvnPLDispDop13_IsIrrational', 2);
			}

			if (isOnko && isOnkoTwo && isOnkoThree) {
				vm.set('EvnPLDispDop13_IsOncology', 2);
			}
		});
		
		if(needCalcSSRisk) {
			let ssriskPanel = view.ownerPanel.getSSRblock();
			if(ssriskPanel) {
				ssriskPanel.getController().loadScoreField();
			}
		}
	},
	onChangeAnswer: function(field, evnt) {
		var questionPanel = field.up('fieldset');
		if(questionPanel) {
			var visible = questionPanel.getValue()==2;
			
			var childPanel = questionPanel.query('[QuestionType_pid='+questionPanel.QuestionType_id+']');
			if(!Ext6.isEmpty(childPanel)) {
				
				childPanel.forEach(function(p) {p.setVisible(visible);});
			}
		}
	},
	setAnswer: function(field, evnt) {
		var questionPanel = field.up('fieldset');
		if(Ext6.isEmpty(questionPanel.QuestionType_id)) {
			return false;
		}
		var QuestionType_id = questionPanel.QuestionType_id,
			rec = questionPanel.record;
		
		switch(Number(rec.get('AnswerType_id'))) {
			case 1: //да/нет
				rec.set('DopDispQuestion_IsTrue', field.getValue());
				break;
			case 2: //текст
				//rec.set('DopDispQuestion_ValuesStr', field.getValue());
				rec.set('DopDispQuestion_Answer', field.getValue());
				break;
			case 3: //справочник
				if(questionPanel.getElement().xtype == 'segmentedbutton') {//такие вопросы, вроде и справочник, но ответ да/нет тоже имеется.
					rec.set('DopDispQuestion_IsTrue', field.getValue());
				} else {
					rec.set('DopDispQuestion_ValuesStr', field.getValue());
				}
				break;
		}
		
		//field.anketaPanel.recalcAllQuestion(field); //дублирует в doSave
	},
	doSave: function() {
		var view = this,
			vm = view.ownerPanel.getViewModel(),
			data_form = view.DataForm.getForm();
		var data = [];
		
		view.store.getRange().forEach(function(rec) {
			data.push({
				AnswerClass_id: rec.get('AnswerClass_id'),
				AnswerType_id: rec.get('AnswerType_id'),
				DopDispQuestion_Answer: rec.get('DopDispQuestion_Answer'),
				DopDispQuestion_IsTrue: rec.get('DopDispQuestion_IsTrue'),
				DopDispQuestion_ValuesStr: rec.get('DopDispQuestion_ValuesStr'),
				DopDispQuestion_id: rec.get('DopDispQuestion_id'),
				QuestionType_id: rec.get('QuestionType_id'),
				QuestionType_RowNum: rec.get('QuestionType_RowNum')
			});
		});
		
		var params = {};
		params.EvnPLDisp_id = vm.get('EvnPLDispDop13_id');
		params.DopDispQuestionData = Ext6.util.JSON.encode(data);
		//params.MedPersonal_id = getGlobalOptions().CurMedPersonal_id;
		params.Diag_id = 10944; //Z10.8 Рутинная общая проверка здоровья //TODO: проверить, возможно это и не обязательно здесь
		
		params.DopDispQuestion_setDate = //data_form.findField('EvnUslugaDispDop_didDate').getValue(); 
			Ext6.Date.format(data_form.findField('EvnUslugaDispDop_didDate').getValue(), 'd.m.Y');
		
		params.NeedCalculation = 1;
		
		view.mask('Сохранение анкеты');
		
		data_form.submit({
			url: '/?c=EvnPLDispDop13&m=saveDopDispQuestions',
			failure: function(result_form, action) {
				view.ownerPanel.formStatus = 'edit';
				view.unmask();
			},
			params: params,
			success: function(result_form, action) {
				view.ownerPanel.formStatus = 'edit';
				view.unmask();
				
				if ( action.result ) {
					if ( action.result.EvnUslugaDispDop_id ) {
						data_form.findField('EvnUslugaDispDop_id').setValue(action.result.EvnUslugaDispDop_id);

						view.updateStatus();
					
						view.recalcAllQuestion(true);
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
				}
			}
		});
	},
	reset: function() {
		var view = this;
		view.list = view.queryById('parentOfAllQuestions');
		if(view.list) view.list.destroy();
	},
	makeAnketa: function() {
		var view = this,
			win = view.ownerPanel,
			vm = win.getViewModel();
		view.mask('Загрузка анкеты');
		Ext6.suspendLayouts();
		view.reset();
		
		view.list = Ext6.create('Ext6.form.FieldSet', {
			border: false,
			itemId: 'parentOfAllQuestions',
			style: {
				paddingTop: '15px',
				paddingLeft: '10px',
				'border-width': '0px !important;' //все из-за того что в глобальных css для .fieldset стоит !important
			},
			defaults: {
				width: '100%',
				//~ labelWidth: 150
			},
			items: []
		});
		view.queryById('AnketaContainer').add(view.list);
		
		//строим список вопросов
		view.stopMake = false;//признак что есть хотя бы один rec.isMade = null
		var level = 0;//текущий уровень дерева
		var QuestionType_id;
		
		while(!view.stopMake && level<2) {
			view.stopMake = true;
			view.store.getRange().forEach(function(rec) {
				QuestionType_id = rec.get('QuestionType_id');
				
				if(Ext6.isEmpty(rec.get('isMade'))) {//уже построенные узлы не смотрим
					var needMake = false;
					var ownerPanel = null; //на какой элемент (родительский) помещать узел
					if(level==0) {
						if(Ext6.isEmpty(rec.get('QuestionType_pid'))) {//на нулевом(корневом) уровне рассматриваем только узлы без родителей ("сироты")
							ownerPanel = view.list;
						} else {
							view.stopMake = false;//обнаружили узел для постройки на следующем уровне
						}
						
					} else {
						if(!Ext6.isEmpty(rec.get('QuestionType_pid'))) {//на остальных уровнях рассматриваем только "детей"
							ownerPanel = view.list.queryById('questionPanel'+rec.get('QuestionType_pid'));
						} else {
							view.stopMake=false;
						}
					}
					
					if(ownerPanel) {
						rec.set('isMade',true);
						let el = null;
						switch(Number(rec.get('AnswerType_id'))) {
							case 1://да/нет
								el = {
									xtype: 'segmentedbutton',
									itemId: 'questionElement'+QuestionType_id,
									//~ QuestionType_id: rec.get('QuestionType_id'),
									bind: {
										disabled: '{anketa_edit_disabled}'
									},
									ownerPanel: ownerPanel,
									anketaPanel: view,
									width: 144,
									height: 30,
									userCls: 'segmentedButtonGroup',
									items: [{
										text: 'Да',
										value: 2,
										QuestionType_id: QuestionType_id,
										ownerPanel: ownerPanel,
										anketaPanel: view,
										handler: view.setAnswer
									}, {
										text: 'Нет',
										value: 1,
										QuestionType_id: QuestionType_id,
										pressed: true,
										ownerPanel: ownerPanel,
										anketaPanel: view,
										handler: view.setAnswer
									}],
									listeners: {
										change: view.onChangeAnswer
									}
								};
								break;
							case 2://текст
								el = {
									xtype: 'textfield',
									itemId: 'questionElement'+QuestionType_id,
									QuestionType_id: QuestionType_id,
									ownerPanel: ownerPanel,
									anketaPanel: view,
									width: 144,
									height: 30,
									value: '',
									bind: {
										disabled: '{anketa_edit_disabled}'
									},
									listeners: {
										blur: view.setAnswer,
										change: view.onChangeAnswer
									}
								};
								break;
							case 5://число
								el = {
									xtype: 'numberfield',
									itemId: 'questionElement'+QuestionType_id,
									QuestionType_id: QuestionType_id,
									ownerPanel: ownerPanel,
									anketaPanel: view,
									width: 144,
									height: 30,
									bind: {
										disabled: '{anketa_edit_disabled}'
									},
									listeners: {
										blur: view.setAnswer,
										change: view.onChangeAnswer
									}
								};
								break;
							default:
								let xtype = '',
									comboSubject = '',
									withDiag = false;
								switch(Number(rec.get('AnswerClass_id'))) {
									case 2: xtype = 'swAnswerOnkoTypeCombo'; comboSubject = 'AnswerOnkoType'; break;
									case 3: xtype = 'swAnswerSmokeTypeCombo'; comboSubject = 'AnswerSmokeType'; break;
									case 4: xtype = 'swAnswerWalkTypeCombo'; comboSubject = 'AnswerWalkType'; break;
									case 5: xtype = 'swAnswerPissTypeCombo'; comboSubject = 'AnswerPissType'; break;
									case 7: xtype = 'swAlcoholIngestTypeCombo'; comboSubject = 'AlcoholIngestType'; break;
									case 6: withDiag = true;
									case 1:
									default:
										el = {
											xtype: 'segmentedbutton',
											itemId: 'questionElement'+QuestionType_id,
											//~ QuestionType_id: rec.get('QuestionType_id'),
											ownerPanel: ownerPanel,
											anketaPanel: view,
											withDiag: withDiag,
											width: 144,
											height: 30,
											userCls: 'segmentedButtonGroup',
											items: [{
												text: 'Да',
												value: 2,
												QuestionType_id: QuestionType_id,
												ownerPanel: ownerPanel,
												anketaPanel: view,
												handler: view.setAnswer
											}, {
												text: 'Нет',
												value: 1,
												QuestionType_id: QuestionType_id,
												ownerPanel: ownerPanel,
												anketaPanel: view,
												pressed: true,
												ownerPanel: ownerPanel,
												anketaPanel: view,
												handler: view.setAnswer
											}],
											bind: {
												disabled: '{anketa_edit_disabled}'
											},
											listeners: {
												change: view.onChangeAnswer,
												//~ beforeBlur: view.beforeBlur
											}
										};
									break;
								}
								if(Ext6.isEmpty(el)) {
									el = Ext6.create('swCommonSprCombo', {
										comboSubject: comboSubject,
										itemId: 'questionElement'+QuestionType_id,
										QuestionType_id: QuestionType_id,
										ownerPanel: ownerPanel,
										anketaPanel: view,
										width: 144,
										height: 30,
										displayCode: false,
										//autoLoad: true,//не работает, поэтому:
										autoLoadOnValue: true,
										bind: {
											disabled: '{anketa_edit_disabled}'
										},
										listeners: {
											select: view.setAnswer,
											change: view.onChangeAnswer
										}
									});
								}
								break;
						}
						
						cfg = {
							xtype: 'fieldset',
							cls: 'DVN-anketa-box',
							itemId: 'questionPanel'+QuestionType_id,
							QuestionType_id: QuestionType_id,
							QuestionType_pid: rec.get('QuestionType_pid'),
							padding: '10 0 0 33',
							border: false,
							record: rec,
							level: level,
							ownerPanel: ownerPanel,
							anketaPanel: view,
							ownerWin: win, //не окно ЭМК, а форма ДВН
							getElement: function() {
								return this.queryById('questionElement'+this.QuestionType_id);
							},
							getValue: function() {
								var el = this.getElement();
								return el && el.getValue ? el.getValue() : null;
							},
							items: [{
								layout: 'hbox',
								border: false,
								items: [
									{
										xtype: 'label',
										flex: 1,
										padding: '0 20 0 0',
										html: //'id='+rec.get('QuestionType_id')+' №'+rec.get('QuestionType_Num')+') '+
											rec.get('QuestionType_Name')
									},
									el
								]
							}, {
								height: 10, border: false, cls: 'dvn-anketa-box-hr'
							}]
						};
						
						var pan = ownerPanel.add(cfg);
						if(pan && el.withDiag) {
							cfg.level = level+1;
							cfg.QuestionType_pid = QuestionType_id;
							
							//делаем доп.строку для диагноза
							cfg.items = [{
								layout: 'hbox',
								border: false,
								items: [
									{
										xtype: 'label',
										flex: 1,
										padding: '0 20 0 0',
										html: 'Диагноз'
									},
									{
										xtype: 'swDiagCombo',
										fieldLabel: '',
										userCls: 'diagnoz',
										allowBlank: false,
										itemId: 'questionElement'+QuestionType_id,
										//~ itemId: 'diagtest',
										QuestionType_id: rec.get('QuestionType_id'),
										ownerPanel: pan,
										anketaPanel: view,
										isDiag: true,
										width: 144,
										height: 30,
										bind: {
											disabled: '{anketa_edit_disabled}'
										},
										listeners: {
											change: view.onChangeAnswer,
											select: view.setAnswer
										}
									}
								]
							}, {
								height: 10, border: false, cls: 'dvn-anketa-box-hr'
							}]
							pan.add(cfg);
						}
					}
				}
			});
			level+=1;
		}
		
		Ext6.resumeLayouts(true);
		view.store.getRange().forEach(function(rec) {
			var panel = view.queryById('questionPanel'+rec.get('QuestionType_id'));
			if(panel) {
				var field = panel.getElement();
				if( field ) { // && panel.level==0
					switch(parseInt(rec.get('AnswerType_id'))) {
						case 1: //да/нет
							field.setValue(Number(panel.record.get('DopDispQuestion_IsTrue')));
							break;
						case 2: //текст
							field.setValue(panel.record.get('DopDispQuestion_Answer'));
							break;
						default:
							if(field.store) {//бывает и без комбика, "да/нет" с диагнозом
								field.getStore().addListener('load', function(){
									field.setValue(field.getValue());
								});
							}
							if(!Ext6.isEmpty(panel.record.get('DopDispQuestion_Response'))) {
								if(field.withDiag) {
									field.setValue(parseInt(panel.record.get('DopDispQuestion_IsTrue')));
									var diagfield = field.up('fieldset').query('[isDiag=true]');
									if(diagfield.length>0) {
										diagfield[0].setValue(parseInt(panel.record.get('DopDispQuestion_ValuesStr')));
									}
								} else {
									field.setValue(parseInt(panel.record.get('DopDispQuestion_ValuesStr')));
								}
							} else if(field.store) {
								field.store.load();
							}
					}
					
				}
			}
		});
		view.recalcAllQuestion(false);
		view.unmask();
	},
	initComponent: function() {
		var view = this;
		
		view.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'DopDispQuestion_id', mapping: 'Diag_id', type: 'int'},
				{ name: 'QuestionType_id', mapping: 'QuestionType_id', type: 'string'},
				{ name: 'QuestionType_Num', mapping: 'QuestionType_Num', type: 'string'},
				{ name: 'QuestionType_RowNum', mapping: 'QuestionType_RowNum', type: 'string'},
				{ name: 'QuestionType_Name', mapping: 'QuestionType_Name', type: 'string'},
				{ name: 'DopDispQuestion_Response', mapping: 'DopDispQuestion_Response', type: 'string'},
				{ name: 'DopDispQuestion_IsTrue', mapping: 'DopDispQuestion_IsTrue', type: 'string'},
				{ name: 'DopDispQuestion_Answer', mapping: 'DopDispQuestion_Answer', type: 'string'},
				{ name: 'DopDispQuestion_ValuesStr', mapping: 'DopDispQuestion_ValuesStr', type: 'string'},
				{ name: 'AnswerType_id', mapping: 'AnswerType_id', type: 'string'},
				{ name: 'AnswerClass_id', mapping: 'AnswerClass_id', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'QuestionType_RowNum',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=DopDispQuestion&m=loadDopDispQuestionGrid',
				reader: {
					type: 'json',
					rootProperty: 'data'
				},
				sorters: [{
					 property: 'QuestionType_RowNum',
					 direction: 'ASC'
				}],
			},
			extraParams: {
				//~ 'EvnPLDisp_id': '730023881328482'
			},
			mode: 'remote'
		});
		
		view.AnketaForm = Ext6.create('Ext6.form.Panel', {
			border: false,
			accessType: 'view',
			//~ padding: "18 0 30 27",
			layout: 'anchor',
			bind: {
				disabled: '{action == add}'
			},
			//~ bodyPadding: 10,
			border: false,
			defaults: {
				anchor: '100%'
			},
			items: [{
				xtype: 'container',
				itemId: 'AnketaContainer',
				border: false,
				items: [
					
				]
			}]
		});

		view.tools = [
			{
				xtype: 'displayfield',
				cls:'toolDisplayField',
				itemId: 'status'+view.SurveyType_Code,
				bind: {
					value: '{ MedPersonal_SurveyType_Code'+view.SurveyType_Code+' +" • "+ LpuNick_SurveyType_Code'+view.SurveyType_Code+' +" • "+ Date_SurveyType_Code'+view.SurveyType_Code+' }',
					hidden: '{!MedPersonal_SurveyType_Code'+view.SurveyType_Code+' && !LpuNick_SurveyType_Code'+view.SurveyType_Code+' && !Date_SurveyType_Code'+view.SurveyType_Code+'}'
				},
				fieldLabel: '',
				value: '',
			},
			{	xtype: 'tbspacer', width: 15},
			{
				type: 'dvn-panel-edit',
				bind: {
					disabled: '{action == "view"}'
				},
				handler: function() {
					var vm = view.ownerPanel.getViewModel();
					vm.set('anketa_edit_disabled', false);
				}
			},
			{	xtype: 'tbspacer',	width: 15 },
			{
				type: 'gear',
				bind: {
					hidden: '{!Date_SurveyType_Code'+view.SurveyType_Code+'}'
				},
				handler: function () {
					view.openModalForm();
				}
			}
		];
		
		view.DataForm = Ext6.create('Ext6.form.Panel', {
			hidden: true,
			items: [
				{
					name: 'UslugaComplex_id',
					xtype: 'hidden'
				},
				{
					name: 'LpuSection_uid',
					xtype: 'hidden'
				},
				{
					name: 'DopDispDiagType_id',
					xtype: 'hidden'
				},
				{
					name: 'DeseaseStage',
					xtype: 'hidden'
				},
				{
					name: 'EvnUslugaDispDop_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnVizitDispDop_id',
					xtype: 'hidden'
				}, {
					name: 'EvnVizitDispDop_pid',
					xtype: 'hidden'
				}, {
					name: 'DopDispInfoConsent_id',
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					bind: '{PersonEvn_id}',//?
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_id',
					xtype: 'hidden'
				}, {
					name: 'CytoMedPersonal_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					bind: {
						value: '{Server_id}' //?
					},
					xtype: 'hidden'
				}, {
					name: 'XmlTemplate_id',
					xtype: 'hidden'
				}, {
					name: 'EvnDirection_Type',
					xtype: 'hidden'
					//fieldLabel: 'Тип'
				}, {
					name: 'EvnDirection_insDate',
					xtype: 'hidden',
					//fieldLabel: 'Дата создания'
				}, {
					name: 'EvnDirection_Num',
					xtype: 'hidden',
					//fieldLabel: 'Номер направления'
				}, {
					name: 'EvnDirection_RecTo',
					xtype: 'hidden',
					//fieldLabel: 'Место оказания'
				}, {
					name: 'EvnDirection_RecDate',
					xtype: 'hidden',
					//fieldLabel: 'Запись'
				}, {
					name: 'EvnUslugaDispDop_setDate',
					xtype: 'datefield',
					format: 'd.m.Y',
					hidden: true
				}, {
					name: 'EvnUslugaDispDop_didDate',
					xtype: 'datefield',
					format: 'd.m.Y',
					hidden: true
				}, {
					name: 'EvnUslugaDispDop_disDate',
					xtype: 'datefield',
					format: 'd.m.Y',
					hidden: true
				}, {
					name: 'EvnUslugaDispDop_setTime',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaDispDop_didTime',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaDispDop_disTime',
					xtype: 'hidden'
				}, {
					name: 'Diag_id',
					value: 10944, //Z10.8 Рутинная общая проверка здоровья
					xtype: 'hidden'
				}, {
					name: 'LpuSection_id',
					xtype: 'hidden'
				},  {
					name: 'MedStaffFact_id',
					xtype: 'hidden'
				}, {
					name: 'ExaminationPlace_id',
					xtype: 'hidden'
				}
			],
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'EvnUslugaDispDop_id'},
						{name: 'EvnVizitDispDop_id'},
						{name: 'EvnVizitDispDop_pid'},
						{name: 'DopDispInfoConsent_id'},
						{name: 'EvnDirection_id'},
						{name: 'PersonEvn_id'},
						{name: 'MedPersonal_id'},
						{name: 'CytoMedPersonal_id'},
						{name: 'Server_id'},
						{name: 'XmlTemplate_id'},
						{name: 'EvnDirection_Type'},
						{name: 'EvnDirection_insDate'},
						{name: 'EvnDirection_Num'},
						{name: 'EvnDirection_RecTo'},
						{name: 'EvnDirection_RecDate'},
					]
				})
			})
		});
		
		Ext6.apply(view, {
			border: false,
			items: [
				view.AnketaForm,
				view.DataForm
			]
		});
			
		this.callParent(arguments);
	}
});