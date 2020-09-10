
sw.Promed.PersonPregnancy = (function(){
	var o = {};

	o.getChildTermTypeByOutcomPeriod = function(BirthSpecStac_OutcomPeriod) {
		//1	Доношенный (37-41 недель)
		var ChildTermType_id = 1;
		//2	Недоношенный (менее 37 недель)
		if (BirthSpecStac_OutcomPeriod < 37) {
			ChildTermType_id = 2;
		} else {
			//3	Переношенный (42 недели и более)
			if (BirthSpecStac_OutcomPeriod > 41) {
				ChildTermType_id = 3;
			}
		}
		return ChildTermType_id;
	};

	o.InputData = function(config) {
		var inputData = this;

		inputData.defaults = {
			Person_id: null,
			PersonRegister_id: null,
			Person_SurName: null,
			Person_FirName: null,
			Person_SecName: null,
			Evn_id: null,
			Server_id: null,
			Lpu_id: null,
			LpuSection_id: null,
			MedStaffFact_id: null,
			MedPersonal_id: null,
			userMedStaffFact: {}
		};

		inputData.fn = Ext.emptyFn;

		inputData.getValues = function() {
			return Ext.apply(inputData.defaults, inputData.fn() || {});
		};

		if (typeof config == 'object') {
			if (config.fn) {
				inputData.fn = config.fn;
			}
		}
	};

	var inputData = new o.InputData;

	var integerValidator = function(value) {
		return (value%1 == 0);
	};

	var getPersonBloodGroup = function(Person_id, callback) {
		Ext.Ajax.request({
			url: '/?c=PersonBloodGroup&m=getPersonBloodGroup',
			params: {Person_id: Person_id},
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success) callback(response_obj);
			}
		});
	};

	o.openEvnXmlViewWindow = function(EvnXml_id) {
		var win = getWnd('swEvnXmlViewWindow');
		if (win.isVisible()) {
			win.hide();
		}
		var params = {
			EvnXml_id: EvnXml_id,
			onBlur: function() {
				win.hide();
			},
			onHide: Ext.emptyFn
		};
		win.show(params);
	};

	o.AnketaCategory = Ext.extend(sw.Promed.WizardCategory, {
		name: 'Anketa',
		objectName: 'PersonPregnancy',
		inputData: inputData,
		loadPersonInfo: function(callback) {
			callback = callback||Ext.emptyFn;
			var category = this;
			var data = category.inputData.getValues();

			category.PersonInfoPanel.setTitle('...');
			category.PersonInfoPanel.clearPersonChangeParams();
			category.PersonInfoPanel.load({
				Person_id: data.Person_id,
				callback: function() {
					category.PersonInfoPanel.setPersonTitle();
					callback();
					//устанавливаем config minValue для поля "Дата постановки на учет"
					var PersonBirthday = category.PersonInfoPanel.getFieldValue('Person_Birthday');
					if(PersonBirthday) {
						var PersonRegisterSetDate = category.getForm().findField('PersonRegister_setDate');
						PersonRegisterSetDate.setMinValue(PersonBirthday);
					}
				}
			});
		},
		openPersonPregnancyResultEditWindow: function(action) {
			if ( !action.inlist(['add','edit','view']) ) {
				return false;
			}
			var category = this;
			var grid = category.PersonPregnancyResultGridPanel.getGrid();

			if (action != 'add') {
				var record = grid.getSelectionModel().getSelected();
				if (!record || Ext.isEmpty('PersonPregnancyResult_id')) {
					return false;
				}
			}

			var params = new Object();

			params.action = action;
			params.callback = function(data) {
				if ( typeof data != 'object' || typeof data.PersonPregnancyResultData != 'object' ) {
					return false;
				}
				data.PersonPregnancyResultData.RecordStatus_Code = 0;

				var record = grid.getStore().getById(data.PersonPregnancyResultData.PersonPregnancyResult_id);

				if ( typeof record == 'object' ) {
					if ( record.get('RecordStatus_Code') == 1 ) {
						data.PersonPregnancyResultData.RecordStatus_Code = 2;
					}

					var grid_fields = new Array();

					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});

					for ( i = 0; i < grid_fields.length; i++ ) {
						record.set(grid_fields[i], data.PersonPregnancyResultData[grid_fields[i]]);
					}

					record.commit();
				} else {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PersonPregnancyResult_id') ) {
						grid.getStore().removeAll();
					}
					data.PersonPregnancyResultData.PersonPregnancyResult_id = -swGenTempId(grid.getStore());

					grid.getStore().loadData([data.PersonPregnancyResultData], true);
				}
			};

			params.formParams = {};

			if ( action != 'add' ) {
				var record = grid.getSelectionModel().getSelected();
				if ( !record || !record.get('PersonPregnancyResult_id') ) {
					return false;
				}
				params.formParams = record.data;
			}

			var PersonBirthday = category.PersonInfoPanel.getFieldValue('Person_Birthday');
			if(PersonBirthday) {
				params.PersonBirthday = PersonBirthday;
			}
			getWnd('swPersonPregnancyResultEditWindow').show(params);
			return true;
		},
		deletePersonPregnancyResult: function() {
			var category = this;
			var grid = category.PersonPregnancyResultGridPanel.getGrid();
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('PersonPregnancyResult_id'))) {
				return false;
			}

			sw.swMsg.show({
				buttons:Ext.Msg.YESNO,
				fn:function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						switch ( Number(record.get('RecordStatus_Code')) ) {
							case 0:
								grid.getStore().remove(record);
								break;

							case 1:
							case 2:
								record.set('RecordStatus_Code', 3);
								record.commit();
								grid.getStore().filterBy(function(rec) {
									return (Number(rec.get('RecordStatus_Code')) != 3);
								});
								break;
						}
						if ( grid.getStore().getCount() > 0 ) {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					}
				},
				icon:Ext.MessageBox.QUESTION,
				msg:lang['vyi_hotite_udalit_zapis'],
				title:lang['podtverjdenie']
			});
			return true;
		},
		setReadOnly: function(readOnly) {
			sw.Promed.PersonPregnancy.AnketaCategory.superclass.setReadOnly.apply(this, arguments);

			this.AnketaFactory.setReadOnly(readOnly);
			this.PersonPregnancyResultGridPanel.setReadOnly(readOnly);
		},
		getCategoryFormData: function(category, objectsAsJSON) {
			var base_form = category.getForm();
			var ppr_grid = category.PersonPregnancyResultGridPanel.getGrid();

			var params = {};
			var Answers = {};
			var PersonPregnancyResultData = [];

			base_form.items.each(function(field){
				if (!field.getName) return;
				var name = field.getName();
				var value = field.getValue();

				//обработка компоненты radiobutton
				if (value === undefined){
					value = "";
					var radioitem = field.items.find(function(comp){return comp.checked});
					if (radioitem){
						value = radioitem.inputValue
					}
				}

				if (value instanceof Date) {
					value = value.format('d.m.Y');
				}
				if (result = name.match(/^QuestionType_(\d+)$/)) {
					Answers[result[1]] = value;
					if (typeof field.checked != 'undefined' && field.checked){
						Answers[result[1]] = true;
					}										
				}
				params[name] = value;
				if (typeof field.checked != 'undefined' && field.checked){
					params[name] = true;
				}
				//gaf 10022018 для работы с кнопками
				if (typeof field.name != 'undefined' && (field.name).indexOf('QuestionType') > -1) {
					var objinput = Ext.getCmp(field.id);
					//Получение компонента группы, в котором состоит поле field
					var parent = objinput.ownerCt.ownerCt.ownerCt;
					if (typeof parent.items != 'undefined' && typeof parent.items.items[1] != 'undefined'
							&& typeof parent.items.items[1].items != 'undefined' 
							&& typeof parent.items.items[1].items.items[0] != 'undefined'
							&& typeof parent.items.items[1].items.items[0].items != 'undefined'
							) {
						//кнопка Присутствует/Выявлено
						var btnyes = parent.items.items[1].items.items[0].items.items[0];
						//кнопка Отсутствует/Не выявлено
						var btnno = parent.items.items[1].items.items[1].items.items[0];
						//группа Предыдущие беременности содержит свои группы, обрабтываем отдельно
						if (btnyes.QuestionType_Code == "623") {
							btnyes_QuestionType_Code_623 = btnyes.pressed;
						}
						if (btnyes.pressed == btnno.pressed /*&& btnyes.QuestionType_Code != "616" && btnyes.QuestionType_Code != "618"*/) {
							if (btnyes.QuestionType_Code == "624" || btnyes.QuestionType_Code == "625" || btnyes.QuestionType_Code == "626") {
								if (btnyes_QuestionType_Code_623) {
									Answers[btnno.QuestionType_Code] = false;
									params['QuestionType_' + btnno.QuestionType_Code] = false;
								}
							} else {
								Answers[btnno.QuestionType_Code] = false;
								params['QuestionType_' + btnno.QuestionType_Code] = false;
							}
						} else if (btnyes.pressed /*&& btnyes.QuestionType_Code != "616" && btnyes.QuestionType_Code != "618"*/) {
							//проверяем элементы в группах
							var parent = btnyes.ownerCt.el.parent('.x-fieldset-body');
							var checkedcount = 0;
							var childfieldset = parent.dom.children;
							for (var i = 2; i < childfieldset.length; i++) {
								ready = true;
								var arr_obj = $("input[type=checkbox]", $("#" + childfieldset[i].id));
								for (var j = 0; j < arr_obj.length; j++) {
									if (arr_obj[j].checked) {
										checkedcount++;
									}
								}
								arr_obj = $("input[type=text]", $("#" + childfieldset[i].id));
								for (var j = 0; j < arr_obj.length; j++) {
									if (arr_obj[j].value != "") {
										checkedcount++;
									}
								}
							}
							if (checkedcount == 0) {
								Answers[btnno.QuestionType_Code] = false;
								params['QuestionType_' + btnno.QuestionType_Code] = false;
							}
						}
					}
				}
			});

			ppr_grid.getStore().clearFilter();
			if (ppr_grid.getStore().getCount() > 0) {
				PersonPregnancyResultData = getStoreRecords(ppr_grid.getStore());
				ppr_grid.getStore().filterBy(function(rec) {
					return (Number(rec.get('RecordStatus_Code')) != 3);
				});
			}

			var isNumeric = function(value){
				return !isNaN(parseFloat(value)) && isFinite(value);
			};
			if (!Ext.isEmpty(params.Person_did) && !isNumeric(params.Person_did)) {
				params.PersonPregnancy_dadFIO = params.Person_did;
				params.Person_did = null;
			}

			if (objectsAsJSON) {
				params.Answers = Ext.util.JSON.encode(Answers);
				params.PersonPregnancyResultData = Ext.util.JSON.encode(PersonPregnancyResultData);
			} else {
				params.Answers = Answers;
				params.PersonPregnancyResultData = PersonPregnancyResultData;
			}
			//добавляем кнопки gaf 10022018
			return params;
		},
		beforeSaveCategory: function (category) {
			// Валидация правильности заполнения группбоксов, Ufa, gaf #111648
			var base_form = category.getForm();
			//правильность заполнения группбокса
			//COMMENT 10022018
			var notvalidgroupbox = false;
			var btnyes_QuestionType_Code_623 = false;
			var QuestiontypeNotValid = '';
			base_form.items.each(function (field) {
				var checkedcount = 0;
				if (typeof field.name != 'undefined' && (field.name).indexOf('QuestionType') > -1) {
					var objinput = Ext.getCmp(field.id);
					//Получение компонента группы, в котором состоит поле field
					var parent = objinput.ownerCt.ownerCt.ownerCt;
					if (typeof parent.items != 'undefined' && typeof parent.items.items[1] != 'undefined'
							&& typeof parent.items.items[1].items != 'undefined' 
							&& typeof parent.items.items[1].items.items[0] != 'undefined'
							&& typeof parent.items.items[1].items.items[0].items != 'undefined'
							) {
						var btnyes = parent.items.items[1].items.items[0].items.items[0];
						var btnno = parent.items.items[1].items.items[1].items.items[0];

						var ccolor = '';

						//группа Предыдущие беременности содержит свои группы, обрабтываем отдельно
						if (btnyes.QuestionType_Code == "623") {
							btnyes_QuestionType_Code_623 = btnyes.pressed;
						}
						/*
						 if (btnyes.pressed == btnno.pressed && btnyes.QuestionType_Code != "616" && btnyes.QuestionType_Code != "618"){
						 if (btnyes.QuestionType_Code == "624" || btnyes.QuestionType_Code == "625" || btnyes.QuestionType_Code == "626"){
						 if (btnyes_QuestionType_Code_623){
						 ccolor = 'rgb(0,150,0)';
						 notvalidgroupbox = true;
						 }
						 }else{
						 ccolor = 'rgb(0,150,0)';
						 notvalidgroupbox = true;
						 }
						 }else*/ if (btnyes.pressed && btnyes.QuestionType_Code != "616" && btnyes.QuestionType_Code != "618") {
							//проверяем элементы в группах
							var parent = btnyes.ownerCt.el.parent('.x-fieldset-body');
							var checkedcount = 0;
							var childfieldset = parent.dom.children;
							for (var i = 2; i < childfieldset.length; i++) {
								ready = true;

								var arr_obj = $("input[type=checkbox]", $("#" + childfieldset[i].id));
								for (var j = 0; j < arr_obj.length; j++) {
									if (arr_obj[j].getAttribute("checked") == "") {
										checkedcount++;
									}
								}
								arr_obj = $("input[type=text]", $("#" + childfieldset[i].id));
								for (var j = 0; j < arr_obj.length; j++) {
									if (arr_obj[j].value != "") {
										checkedcount++;
									}
								}
							}
							if (checkedcount == 0) {
								QuestiontypeNotValid = childfieldset[1].id;
								ccolor = 'rgb(0,150,0)';
								notvalidgroupbox = true;
							}
						}
						if (typeof objinput.ownerCt.ownerCt.ownerCt.items != 'undefined' && typeof objinput.ownerCt.ownerCt.ownerCt.items.items[0] != 'undefined') {
							Ext.get(objinput.ownerCt.ownerCt.ownerCt.items.items[0].id).setStyle('color', ccolor);
						}
					}

				}
			});
			if (notvalidgroupbox) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: 'В открытых группах не выбраны значения.',
					title: 'Проверка данных формы'
				});

				category.moveToPage(category.getNumByPage(category.getPageByField(Ext.getCmp(QuestiontypeNotValid).ownerCt.items.items[2].items.items[1].items.items[0])), null);
				return false;
			}
						
			var begMens = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[0].getForm().findField('PersonPregnancy_begMensDate').getValue();
			var endMens = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[0].getForm().findField('PersonPregnancy_endMensDate').getValue();		
			
			if ( begMens != "" && endMens != "" && (endMens-begMens < 0 || (Math.ceil((endMens-begMens) / (1000 * 3600 * 24)) >0 &&  Math.ceil((endMens-begMens) / (1000 * 3600 * 24)) >31))) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,					
					msg: 'Период для параметра «Последние менструации с» «по»  не должен быть более 31 календарного дня.',					
					title: 'Проверка данных формы'
				});
//				category.moveToPage(category.getNumByPage(category.getPageByField(Ext.getCmp(QuestiontypeNotValid).ownerCt.items.items[2].items.items[1].items.items[0])), null);
				return false;
			}	

			if ( begMens != "" && endMens == ""){
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: 'В параметре «Последние менструации с» «по»  не заполнено значение «по».',
					title: 'Проверка данных формы'
				});
//				category.moveToPage(category.getNumByPage(category.getPageByField(Ext.getCmp(QuestiontypeNotValid).ownerCt.items.items[2].items.items[1].items.items[0])), null);
				return false;							
			}

            if (getRegionNick() == 'khak'){
                var base_form = category.getForm();
                var input225 = base_form.findField('QuestionType_225');
                var input226 = base_form.findField('QuestionType_226');
                var input227 = base_form.findField('QuestionType_227');
                var input228 = base_form.findField('QuestionType_228');
                var input774 = base_form.findField('QuestionType_774');
                if ((input225.getValue() || input226.getValue() || input227.getValue()) && Ext.isEmpty(input228.getValue()) && Ext.isEmpty(input774.getValue()) && Ext.isEmpty(input774.getRawValue())){
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.WARNING,
                        msg: " Не выбрано ни одно из значений для элементов либо МО либо Иное МО»",
                        title: ERR_INVFIELDS_TIT
                    });
                    category.moveToPage(category.getNumByPage(category.getPageByField(input228)), null);
                    return false;
                }
            }

			return true;
		},
		saveCategory: function(category) {
			var base_form = category.getForm();
			var wizard = category.wizard;

			if (category.validateCategory(category, true) === false){
				return false;
			}

			if (category.beforeSaveCategory(category) === false) {
				return false;
			}

			var params = category.getCategoryFormData(category, true);

            //добавляем параметр Иное МО
            var input774 = base_form.findField('QuestionType_774');
            params['DifferentLpu']="";
            if (input774){
                params['DifferentLpu'] = input774.getRawValue();
            }

			var loadMask = wizard.getLoadMask({msg: "Подождите, идет сохранение..."});
			loadMask.show();

			Ext.Ajax.request({
				params: params,
				url: '/?c=PersonPregnancy&m=savePersonPregnancy',
				success: function(response) {
					loadMask.hide();

					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success) {
						category.PersonPregnancy_id = response_obj.PersonPregnancy_id;
						base_form.findField('PersonPregnancy_id').setValue(response_obj.PersonPregnancy_id);
						base_form.findField('PersonRegister_id').setValue(response_obj.PersonRegister_id);

						category.PersonPregnancyResultGridPanel.loadData({
							globalFilters: {PersonPregnancy_id: response_obj.PersonPregnancy_id},
							noFocusOnLoad: true
						});

						//рекомендации по акушерским осложнениям
						var arraynotice = response_obj.ObstetricPathologyType_text;
						wizard.getnotice(arraynotice);

						category.afterSaveCategory(category);
					}
				},
				failure: function(response) {
					loadMask.hide();
				}
			});
			return true;
		},
		loadRButton: function (base_form, PersonPregnancy) {
			var category = this;

			//кнопка случай ЭКО
			if (typeof Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_754")[0] != 'undefined'){
				Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_754")[0].ownerCt.hide();
			}
		
			//Установка кнопки чекбокса Назначение
			base_form.findField('PersonPregnancy_unknownMens').setValue(false);
			if ((field = base_form.findField(id)) && typeof Ext.getCmp(field.id) != 'undefined'){
				if (Ext.getCmp(base_form.findField('PersonPregnancy_endMensDate').id).getValue() == "" && !Ext.isEmpty(PersonPregnancy.PersonRegister_id)){
					base_form.findField('PersonPregnancy_unknownMens').setValue(true);
				}			
			}

			//08112017 gaf  удаления двоеточия
			var oobj = base_form.findField('QuestionType_246');

			if (typeof oobj != 'undefined') {
				//gaf 08022018 Получение доступа к кнопке Далее 
				category.wizard.NextButton.setDisabled(true);
			}
			//gaf 30012018 
			if (typeof oobj != 'undefined') {
				var parent = oobj.el.parent('.x-form-item');
				$("#" + parent.id + " .x-form-item-label").html("");
			}

			//05022018 gaf
			setIMT(base_form);

            //устанавливаем видимость поля Количество КС
            var hidefield = Ext.getCmp('swPersonPregnancyEditWindow').WizardPanel.getCategory('Anketa').getForm().findField('QuestionType_770');
            if (hidefield){
                var hidepanel = hidefield.findParentByType('panel');
                if (hidepanel){
                    hidepanel.setVisible(hidefield.getValue());
                }
            }

			// Отображение группбокса в зависимости от значения чекбокса, Ufa, gaf #111648
			//var parentid = '';
			var yetshow = false;
			//массив групп 01022018
			var arr_group = new Array();
			//предыдущая обрабатываемая группа
			var old_group = '';
			for (id in PersonPregnancy) {
				var QuestionType_Code = 0;
				if (result = id.match(/^QuestionType_(\d+)$/)) {
					if (typeof result[1] != 'undefined') {
						QuestionType_Code = result[1];
					}
				}

				if (QuestionType_Code > 608 && QuestionType_Code < 660) {

				} else {

					if (typeof PersonPregnancy[id] != 'function' && (field = base_form.findField(id)) && typeof Ext.getCmp(field.id) != 'undefined') {
						var ready = false;
						var objinput = Ext.getCmp(field.id);
						//test
						//console.log('B-B');
						//console.log(id);
						//console.log(objinput);
						var parent = objinput.el.parent('.x-fieldset-body');

						if (typeof objinput.QuestionType_Code != 'undefined') {
							
							if (typeof Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_754")[0] != 'undefined'){
								if (objinput.QuestionType_Code == 226 || objinput.QuestionType_Code == 227){
									if (objinput.checked || !Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_754")[0].ownerCt.hidden){
										Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_754")[0].ownerCt.show();//Кнопка случай show при выкладке взаимосвязи регистров
									}else{
										Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_754")[0].ownerCt.hide();
									}
								}
							}							


							var childfieldset = parent.dom.children;
							//Определяем смену группы
							if (parent != null) {
								if (old_group != Ext.getCmp(childfieldset[0].id).QuestionType_Code) {

									//проверка наличия в массиве групп
									if (typeof arr_group[Ext.getCmp(childfieldset[0].id).QuestionType_Code] == 'undefined') {
										arr_group[Ext.getCmp(childfieldset[0].id).QuestionType_Code] = false;
										arr_group[old_group] = yetshow;
										yetshow = false;
									} else {
										yetshow = arr_group[Ext.getCmp(childfieldset[0].id).QuestionType_Code];
									}

									old_group = Ext.getCmp(childfieldset[0].id).QuestionType_Code;
								}

								for (var i = 2; i < childfieldset.length; i++) {
									//изменения 07112017
									if (childfieldset[i].localName != 'fieldset') {
										var extobj = Ext.getCmp(childfieldset[i].id);
										//08112017 gaf 13112017 изменения для Внутриматочные вмешательства Которая беременность Злокачественные новообразования Диффузные заболевания соединительной ткани
										if ((typeof extobj.items.items[0].items == 'undefined' &&
												extobj.items.items[0].QuestionType_Code != "229" &&
												extobj.items.items[0].QuestionType_Code != "340" &&
												extobj.items.items[0].QuestionType_Code != "272") ||
												(typeof extobj.items.items[0].items != 'undefined' &&
														typeof extobj.items.items[0].items.items[0] != 'undefined' &&
														extobj.items.items[0].items.items[0].QuestionType_Code != "230")
												) {
											ready = true;
											if (((PersonPregnancy[id] == null || (PersonPregnancy[id] == '1' && id != "QuestionType_221"  && id != "QuestionType_231" && id != "QuestionType_602" && id != "QuestionType_603" && id != "QuestionType_605" && id != "QuestionType_606" && id != "QuestionType_607")) && !yetshow) || PersonPregnancy[id] == false) {
												//17012018 gaf временно
												//22032018 testjquery
												//$("#" + childfieldset[i].id).hide();
												Ext.getCmp(childfieldset[i].id).hide();
												//gaf 23012018
												var oobjkey = Ext.getCmp(childfieldset[1].id);
												if (typeof oobjkey.items.items[1] != 'undefined' && typeof oobjkey.items.items[1].items != 'undefined' && typeof oobjkey.items.items[0].items.items[0] != 'undefined' && typeof oobjkey.items.items[1].items.items[0].toggle == 'function') {
													//tempgaf

													oobjkey.items.items[0].items.items[0].toggle(false);
													oobjkey.items.items[1].items.items[0].toggle(PersonPregnancy[id] != false);
													//gaf 10022018
													if (typeof PersonPregnancy[oobjkey.items.items[1].items.items[0].id] != 'undefined') {
														oobjkey.items.items[1].items.items[0].toggle(false);
													}
													//gaf 08022018 блокируем кнопки если режим fullanketa
													if (PersonPregnancy[id] == false && (getGlobalOptions().check_fullpregnancyanketa_allow && getGlobalOptions().check_fullpregnancyanketa_allow == '1') && oobjkey.items.items[0].items.items[0].QuestionType_Code != 608) {
														oobjkey.items.items[0].items.items[0].setDisabled(true);
														oobjkey.items.items[1].items.items[0].setDisabled(true);
													}
												}
											} else {
												//22032018 testjquery
												//$("#" + childfieldset[i].id).show();
												Ext.getCmp(childfieldset[i].id).show();
												yetshow = true;
												arr_group[old_group] = yetshow;
												//gaf 23012018
												var oobjkey = Ext.getCmp(childfieldset[1].id);
												if (typeof oobjkey.items.items[1] != 'undefined' && typeof oobjkey.items.items[1].items != 'undefined' && typeof oobjkey.items.items[1].items.items[0].toggle == 'function') {
													//tempgaf
													oobjkey.items.items[0].items.items[0].toggle(true);
													oobjkey.items.items[1].items.items[0].toggle(false);
												}
											}
										}
									}
								}
							} else {
								if (objinput.name == "QuestionType_197") {
									//22032018 testjquery
									//$("#" + objinput.id).show();
									Ext.getCmp(objinput.id).show();
								}
							}
						}
					}
				}
			}

			//отображение групп в группах 01022018
			//группы предыдущие беременности
			field = base_form.findField("QuestionType_231");
			if (typeof field != 'undefined'){
				objinput = Ext.getCmp(field.id);
				if (typeof field != 'undefined'){				
					parent = objinput.el.parent('.x-fieldset-body');
					if (typeof field != 'undefined'){
						childfieldset = parent.dom.children;	
						if (typeof childfieldset != 'undefined'){	
							if (arr_group[567] || arr_group[565] || arr_group[564] || arr_group[563]) {
								Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_623")[0].toggle(true);
								for ( i = 2; i < 9; i++ ) {
									if (typeof childfieldset[i] != 'undefined') {
										Ext.getCmp(childfieldset[i].id).show();
									}									
								}								
							} else {
								for ( i = 5; i < 9; i++ ) {
									if (typeof childfieldset[i] != 'undefined') {
										Ext.getCmp(childfieldset[i].id).hide();
									}									
								}								
							}
						}						
					}
				}				
			}

			field = base_form.findField("QuestionType_761");
			if (field){
				var panel = field.findParentBy(function(component){return component.hidden == true; });
				if (panel){
					panel.setVisible(true);
				}
			}
		},
		setCategoryFormData: function(category, categoryData) {

			var base_form = category.getForm();
			var PersonPregnancy = categoryData;
			var data = category.inputData.getValues();

			if (Ext.isEmpty(PersonPregnancy.Evn_id) && !Ext.isEmpty(data.Evn_id)) {
				PersonPregnancy.Evn_id = data.Evn_id;
			}

			if (Ext.isEmpty(PersonPregnancy.Person_did)) {
				PersonPregnancy.Person_did = PersonPregnancy.PersonPregnancy_dadFIO;
			}

			base_form.setValues(PersonPregnancy);
			
			//gaf 05082018 #106655
			category.loadRButton(base_form, PersonPregnancy);
			//После режима Просмотра кнопка Сохранить становилась недоступной 07052018 gaf
			Ext.getCmp('swPersonPregnancyEditWindow').WizardPanel.SaveButton.setDisabled(false);

			base_form.findField('Person_did').setRawValue(PersonPregnancy.PersonPregnancy_dadFIO);

			base_form.findField('MedPersonal_iid').getStore().load({
				params: {Lpu_id: base_form.findField('Lpu_iid').getValue()},
				callback: function() {
					base_form.findField('MedPersonal_iid').setValue(base_form.findField('MedPersonal_iid').getValue());
				}
			});

			var Post_id = base_form.findField('Post_id').getValue();
			if (!Ext.isEmpty(Post_id)) {
				base_form.findField('Post_id').getStore().load({
					params: {Post_id: Post_id},
					callback: function() {
						base_form.findField('Post_id').setValue(Post_id);
					}
				});
			}

			var Org_did = base_form.findField('Org_did').getValue();
			if (!Ext.isEmpty(Org_did)) {
				base_form.findField('Org_did').getStore().load({
					params: {Org_id: Org_did},
					callback: function() {
						base_form.findField('Org_did').setValue(Org_did);
					}
				});
			}

			var Post_aid = base_form.findField('Post_aid');
			if (Post_aid){
				var Post_aid = base_form.findField('Post_aid').getValue();
				if (!Ext.isEmpty(Post_aid)) {
					base_form.findField('Post_aid').getStore().load({
						params: {Post_aid: Post_id},
						callback: function() {
							base_form.findField('Post_aid').setValue(Post_aid);
						}
					});
				}
			}

			var input774 = base_form.findField('QuestionType_774');
			if (input774){
				var DifferentLpu_id = base_form.findField('QuestionType_774').getValue();
				if (!Ext.isEmpty(DifferentLpu_id)) {
					base_form.findField('QuestionType_774').getStore().load({
						params: {DifferentLpu_id: DifferentLpu_id},
						callback: function() {
							base_form.findField('QuestionType_774').setValue(DifferentLpu_id);
						}
					});
				}
			}

			category.loadPersonInfo();

			if (PersonPregnancy.PersonPregnancyResultData) {
				category.PersonPregnancyResultGridPanel.getGrid().getStore().loadData(PersonPregnancy.PersonPregnancyResultData);
			} else if(PersonPregnancy.PersonPregnancy_id > 0) {
				category.PersonPregnancyResultGridPanel.loadData({
					globalFilters: {PersonPregnancy_id: PersonPregnancy.PersonPregnancy_id},
					callback: function() {
						category.collectCategoryData(category)
					},
					noFocusOnLoad: true
				});
			}
			category.collectCategoryData(category);
		},
		loadCategory: function(category, showPage) {
			var base_form = category.getForm();
			var ppr_grid = category.PersonPregnancyResultGridPanel.getGrid();
			var data = category.inputData.getValues();
			var wizard = category.wizard;

			if (category.loadParams.PersonPregnancy_id > 0 &&
				category.loadParams.PersonPregnancy_id != category.PersonPregnancy_id
			) {
				category.loaded = false;
				category.PersonPregnancy_id = category.loadParams.PersonPregnancy_id;
			}

			if (category.loaded) {
				showPage();return;
			}

			var loadMask = wizard.getLoadMask({msg: "Подождите, идет формирование анкеты..."});
			loadMask.show();

			var onDataLoad = function() {
				base_form.reset();
				ppr_grid.removeAll();

				base_form.findField('PersonRegister_setDate').setMaxValue(new Date().format('d.m.Y'));

				base_form.items.each(function (field) {
					field.validate()
				});

				//gaf 22112017
				var pHeight = base_form.findField('PersonPregnancy_Height');
				var pWeight = base_form.findField('PersonPregnancy_Weight');

				//05022018 gaf
				funcIMT = function (e) {
					setIMT(base_form);
				}


				//gaf 11122017
				pWeight.on('change', funcIMT);
				pHeight.on('change', funcIMT);

				//gaf 16012018 Согласовано для регионов Башкирия, Крым, Астрахань, Казахстан, Бурятия, Карелия, Екатеринбург
				//gaf 08022018 по регионам ограничение убираем
				setbirthDate = function (e) {
					var pbirthDate = base_form.findField('PersonPregnancy_birthDate');
					var pbegMensDate = base_form.findField('PersonPregnancy_begMensDate');
					var ddate = pbegMensDate.getValue();
					var personBirth = category.PersonInfoPanel.getFieldValue("Person_Birthday");
					personBirth.setFullYear(personBirth.getFullYear() + 10);
					//gaf 08022018
					if (ddate != null && ddate != '') {
						if (ddate >= new Date()) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								msg: 'Значение в поле «Последние менструации с» не может быть больше текущей даты.',
								title: 'Проверка даты'
							});
							pbegMensDate.setValue("");
						} else if (ddate < personBirth) {
							pbegMensDate.setValue("");
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								msg: 'Значение в поле «Последние менструации с» не может быть меньше 10 лет с даты рождения пациента.',
								title: 'Проверка даты'
							});

						} else {
							ddate.setMonth(ddate.getMonth() + 9);
							ddate.setDate(ddate.getDate() + 7);
							pbirthDate.setValue(ddate.format("d.m.y"));
						}
					} else {
						pbirthDate.setValue("");
					}
					personBirth.setFullYear(personBirth.getFullYear() - 10);

					if (getRegionNick().inlist(['krym', 'astra', 'ufa', 'vologda', 'kz', 'penza', 'pskov', 'perm', 'ekb', 'khak']) && pbegMensDate.getValue() != ""){
						var pSetDate = base_form.findField('PersonRegister_setDate').getValue();
						var pMensDate = pbegMensDate.getValue();
						if (getRegionNick() != 'khak'){
							pMensDate.setDate(pMensDate.getDate() - 7);
						}
						//var pPeriodDate = base_form.findField('PersonPregnancy_Period').getValue();
						//if (pPeriodDate == ''){
							base_form.findField('PersonPregnancy_Period').setValue(Math.trunc((pSetDate-pMensDate)/1000/3600/24/7));
						//}
					}
				}
				var pbegMensDate = base_form.findField('PersonPregnancy_begMensDate');
				//gaf 11122017
				pbegMensDate.on('change', setbirthDate);

				var psetDate = base_form.findField('PersonRegister_setDate');
				if (psetDate){
					psetDate.on('change', setbirthDate);
				}

				if (getRegionNumber() == 19){
					var input228 = base_form.findField('QuestionType_228');

					if (input228){
						Ext.Ajax.request({
							url: '/?c=PersonPregnancy&m=getEcoLpuId',
							success: function(response) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (answer){
									input228.addListener('expand', function(combo) {
										input228.getStore().filterBy(function(rec, id) {return answer.indexOf(','+id+',') >0;}, input228);
									});
								}
							}
						});
					}

					var input774 = base_form.findField('QuestionType_774');
					if (input774){

						input774.addListener('blur', function(comp) {
							console.log(comp.lastQuery);
							if (comp.lastQuery != "")
								comp.setRawValue(comp.lastQuery);
						});

						var sstore = new Ext.data.JsonStore({
							url: '/?c=PersonPregnancy&m=getDifferentLpu',
							autoLoad: true,
							fields: [
								{name: 'LpuDifferent_id', type: 'int'},
								{name: 'LpuDifferent_Code', type: 'string'},
								{name: 'LpuDifferent_Name', type: 'string'}
							],
							key: 'LpuDifferent_id',
						});
						input774.bindStore(sstore);
					}
				}

				if (!category.PersonPregnancy_id) {
					category.loaded = true;
					base_form.findField('Person_id').setValue(data.Person_id);
					base_form.findField('Evn_id').setValue(data.Evn_id);
					base_form.findField('Lpu_iid').setValue(data.Lpu_id);

					base_form.findField('MedPersonal_iid').setValue(data.MedPersonal_id);
					base_form.findField('MedPersonal_iid').getStore().load({
						params: {Lpu_id: base_form.findField('Lpu_iid').getValue()},
						callback: function() {
							base_form.findField('MedPersonal_iid').setValue(base_form.findField('MedPersonal_iid').getValue());
						}
					});

					category.loadPersonInfo();
					category.collectCategoryData(category);

					getPersonBloodGroup(data.Person_id, function(bloodGroupData) {
						base_form.findField('BloodGroupType_id').setValue(bloodGroupData.BloodGroupType_id);
						base_form.findField('RhFactorType_id').setValue(bloodGroupData.RhFactorType_id);
					});
					if (!Ext.isEmpty(data.PersonRegister_id)) {
						Ext.Ajax.request({
							url: '/?c=PersonPregnancy&m=loadPersonPregnancy',
							params: {PersonRegister_id: data.PersonRegister_id},
							success: function (response) {
								category.loaded = true;
								var response_obj = Ext.util.JSON.decode(response.responseText);
								var PersonPregnancy = Ext.apply(base_form.getValues(), response_obj[0]);
								category.setCategoryFormData(category, PersonPregnancy);
								loadMask.hide();
								showPage();
							},
							failure: function() {
								loadMask.hide();
							}
						});
					} else {
						//Отображение/скрытие группбоксов, Ufa, gaf #111648
						base_form.items.each(function (field) {
							var ready = false;
							if (typeof field.name != 'undefined' && (field.name).indexOf('QuestionType') > -1) {
								var objinput = Ext.getCmp(field.id);
								if (objinput.itemCls == "x-checkbox-blue-11" || objinput.itemCls == "x-checkbox-blue-16") {

									var parent = objinput.el.parent('.x-fieldset-body');
									if (parent != null && typeof objinput.QuestionType_Code != 'undefined') {
										var childfieldset = parent.dom.children;
										for (var i = 1; i < childfieldset.length; i++) {
											ready = true;
											if (childfieldset[i].localName != "fieldset") {
												//22032018 testjquery
												//$("#" + childfieldset[i].id).hide();
												Ext.getCmp(childfieldset[i].id).hide();
											} else {
												//22032018 testjquery
												//$("#" + childfieldset[i].id).attr("disabled", "true");
												Ext.getCmp(childfieldset[i].id).setDisabled(true);
											}
										}
									}

									if (!ready) {
										var parent = this.el.parent('.x-column-inner');
										var childfieldset = parent.dom.children;
										for (var i = 1; i < childfieldset.length; i++) {
											//22032018 testjquery
											//$("#" + childfieldset[i].id).hide();
											Ext.getCmp(childfieldset[i].id).hide();
										}
									}
								}
							}

						});

						var response_obj = [];
						response_obj[0] = {
							PersonPregnancy_id: null,
							PersonRegister_Code: null,
							PersonRegister_id: null,
							PersonRegister_setDate: null
						};
						var PersonPregnancy = Ext.apply(base_form.getValues(), response_obj[0]);
						category.loadRButton(base_form, PersonPregnancy);

						loadMask.hide();
						showPage();
					}
				} else {
					var categoryData = category.getCategoryData(category);
					if (category.allowCollectData && categoryData && categoryData.loaded) {
						category.loaded = true;
						category.setCategoryFormData(category, categoryData);
						loadMask.hide();
						showPage();
					} else {
						Ext.Ajax.request({
							url: '/?c=PersonPregnancy&m=loadPersonPregnancy',
							params: {
								PersonPregnancy_id: category.PersonPregnancy_id,
								PersonRegister_id: data.PersonRegister_id
							},
							success: function (response) {
								category.loaded = true;
								var response_obj = Ext.util.JSON.decode(response.responseText);

								category.setCategoryFormData(category, response_obj[0]);

								loadMask.hide();
								showPage();
							},
							failure: function() {
								loadMask.hide();
							}
						});
					}
				}
			};

			if (category.AnketaFactory.loaded) {
				onDataLoad();
			} else {
				category.AnketaFactory.loadSettings(function() {
					category.initFields();
					category.showPages();
					category.doLayout();
					category.hidePages();
					onDataLoad();
				});
			}
		},
		initCategory: function(category) {
			sw.Promed.PersonPregnancy.AnketaCategory.superclass.initCategory.apply(category, arguments);

			category.AnketaFactory.loaded = false;

			category.PersonPregnancyResultGridPanel.removeAll();
		},
		initComponent: function() {
			var category = this;
			var id = category.getId();
			category.AnketaFactory = new sw.Promed.QuestionType.Factory({DispClass_id: 14});

			category.PersonInfoPanel = new sw.Promed.PersonInfoPanel({
				collapsible: false,
				collapsed: false,
				floatable: false,
				id: id+'_PersonInformationFrame',
				title: '<div>Загрузка...</div>'
			});

			category.PersonPregnancyResultGridPanel = new sw.Promed.ViewFrame({
				id: id+'_PersonPregnancyResultGridPanel',
				dataUrl: '/?c=PersonPregnancy&m=loadPersonPregnancyResultGrid',
				title: 'Исход беременностей',
				autoLoadData: false,
				useEmptyRecord: false,
				focusOnFirstLoad: false,
				noFocusOnLoadOneTime: true,
				stringfields: [
					{name: 'PersonPregnancyResult_id', type: 'int', header: 'ID', key: true},
					{name: 'RecordStatus_Code', type: 'int', hidden: true},
					{name: 'PregnancyResult_id', type: 'int', hidden: true},
					{name: 'BirthChildResult_id', type: 'int', hidden: true},
					{name: 'ChildStateResult_id', type: 'int', hidden: true},
					{name: 'PersonPregnancyResult_OutcomPeriod', type: 'int', hidden: true},
					{name: 'PersonPregnancyResult_Num', header: '№ п/п', type: 'int', width: 80},
					{name: 'PersonPregnancyResult_Year', header: 'Год', type: 'int', width: 80},
					{name: 'PregnancyResult_Name', header: 'Исход', type: 'string', width: 120},
					{name: 'BirthChildResult_Name', header: 'Ребенок родился', type: 'string', width: 120},
					{name: 'PersonPregnancyResult_WeigthChild', header: 'Вес (г.)', type: 'int', width: 120},
					{name: 'ChildStateResult_Name', header: 'Текущее состояние ребенка', type: 'string', width: 120},
					{name: 'PersonPregnancyResult_AgeChild', header: 'В каком возрасте', type: 'string', width: 120},
					{name: 'PersonPregnancyResult_Descr', header: 'Особенности течения беременности', type: 'string', id: 'autoexpand'}
				],
				actions: [
					{name:'action_add', handler: function(){category.openPersonPregnancyResultEditWindow('add')}},
					{name:'action_edit', handler: function(){category.openPersonPregnancyResultEditWindow('edit')}},
					{name:'action_view', handler: function(){category.openPersonPregnancyResultEditWindow('view')}},
					{name:'action_delete', handler: function(){category.deletePersonPregnancyResult()}},
					{name:'action_refresh', hidden: true}
				]
			});

			var CommonDataPanel = new Ext.Panel({
				id: id+'_CommonData',
				border: false,
				labelAlign: 'right',
				style: 'padding: 5px',
				layout: 'form',
				items: [
					category.PersonInfoPanel,
					{
						xtype: 'hidden',
						name: 'PersonPregnancy_id'
					}, {
						xtype: 'hidden',
						name: 'PersonRegister_id'
					}, {
						xtype: 'hidden',
						name: 'Person_id'
					}, {
						xtype: 'hidden',
						name: 'RiskType_id'
					}, {
						xtype: 'hidden',
						name: 'PersonPregnancy_RiskDPP'
					}, {
						xtype: 'hidden',
						name: 'Evn_id'
					}, {
						xtype: 'hidden',
						name: 'Diag_id'
					}, {
						xtype: 'hidden',
						name: 'PregnancyResult_id'
					}, {
						layout: 'column',
						border: false,
						items: [{
							layout: 'form',
							border: false,
							labelWidth: 160,
							items: [{
								allowBlank: false,
								xtype: 'swdatefield',
								name: 'PersonRegister_setDate',
								fieldLabel: 'Дата постановки на учет',
								width: 120,
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var mathdate = newValue;
										mathdate.setDate(mathdate.getDate()+294)
										if (mathdate < getValidDT(getGlobalOptions().date, '')){
											sw.swMsg.alert('Ошибка', 'Недопустимая дата постановки на учет.');
											combo.setValue("");
										}
									}
								}
							}]
						}, {
							layout: 'form',
							border: false,
							labelWidth: 360,
							items: [{
								allowBlank: getRegionNumber() != 2 && getRegionNumber() != 58,
								allowNegative: false,
								allowDecimals: false,
								minValue: 1,
								maxValue: 40,
								xtype: 'numberfield',
								name: 'PersonPregnancy_Period',
								fieldLabel: 'Срок беременности при постановке на учет (недель)',
								validator: integerValidator,
								width: 120
							}]
						}]
					}, {
						layout: 'column',
						border: false,
						items: [{
							layout: 'form',
							border: false,
							labelWidth: 160,
								items: [{
										//gaf 30112017 #112048
										//16012018 gaf
										//allowBlank: !(getRegionNumber() == 2 || getRegionNumber() == 91 || getRegionNumber() == 30 || getRegionNumber() == 101 || getRegionNumber() == 3 || getRegionNumber() == 10),
										//gaf 08022018
										allowBlank: !(getGlobalOptions().check_menstrdatepregnancyanketa_allow && getGlobalOptions().check_menstrdatepregnancyanketa_allow == '1'),
								xtype: 'swdatefield',
								name: 'PersonPregnancy_begMensDate',
								fieldLabel: 'Последние менструации с',
								width: 120
							}
							]	
						}, {
							layout: 'form',
							border: false,
							labelWidth: 30,
								items: [{
										//gaf 01122017 #112048
										//16012018 gaf
										//allowBlank: !(getRegionNumber() == 2 || getRegionNumber() == 91 || getRegionNumber() == 30 || getRegionNumber() == 101 || getRegionNumber() == 3 || getRegionNumber() == 10),
										//gaf 08022018
										allowBlank: !(getGlobalOptions().check_menstrdatepregnancyanketa_allow && getGlobalOptions().check_menstrdatepregnancyanketa_allow == '1'),
								xtype: 'swdatefield',
								name: 'PersonPregnancy_endMensDate',
								fieldLabel: 'по',
								width: 125
							}]
						}, {
							layout: 'form',
							border: false,
							//labelWidth: 10,
							style: 'padding-left: 20px;',
							items: [{
								xtype: 'swcheckbox',
								validator: integerValidator,
								name: 'PersonPregnancy_unknownMens',
								hideLabel: true,
								boxLabel: 'Неизвестно',
								width: 90,
								listeners:{
									'check': function(checkbox, checked) {
										Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","PersonPregnancy_begMensDate")[0].setAllowBlank(checked);
										Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","PersonPregnancy_endMensDate")[0].setAllowBlank(checked);
									}
								}								
							}]
						}, {							
							layout: 'form',
							border: false,
							labelWidth: 205,
							items: [{
								xtype: 'swdatefield',
								name: 'PersonPregnancy_birthDate',
								fieldLabel: 'Предполагаемый срок родов',
								width: 125
							}]
						}]
					}, {
						xtype: 'fieldset',
						title: 'Наблюдение',
						autoHeight: true,
						style: 'padding: 2px 10px;',
						listeners: {
							'afterlayout': function(panel) {
								panel.setWidth(panel.container.getWidth());
							}
						},
						items: [{
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									allowBlank: false,
									xtype: 'numberfield',
									name: 'PersonRegister_Code',
									fieldLabel: 'Номер карты',
									width: 120,
									minValue: 0
								}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									allowBlank: false,
									xtype: 'swlpucombo',
									hiddenName: 'Lpu_iid',
									fieldLabel: 'МО наблюдения',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = category.getForm();

											var med_personal_combo = base_form.findField('MedPersonal_iid');
											var med_personal_id = med_personal_combo.getValue();
											med_personal_combo.getStore().load({
												params: {Lpu_id: newValue},
												callback: function() {
													var record = med_personal_combo.getStore().getById(med_personal_id);
													if (record) {
														med_personal_combo.setValue(med_personal_id);
													} else {
														med_personal_combo.setValue(null);
													}
												}
											});
										}
									},
									width: 320
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 85,
								items: [{
									allowBlank: false,
									xtype: 'swmedpersonalallcombo',
									hiddenName: 'MedPersonal_iid',
									fieldLabel: 'Врач',
									width: 320
								}]
							}]
						}]
					},
					category.AnketaFactory.createContainer({
						QuestionType_Code: 173,
						labelWidth: 135,
						itemsBefore: [{
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									xtype: 'textfield',
									name: 'PersonPregnancy_Phone',
									fieldLabel: 'Телефон',
												width: 240,
												//gaf 11122017 #112055
												autoCreate: {tag: "input", size: 11, maxLength: 11, autocomplete: "off"},
												maskRe: /\d/
											}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 130,
								items: [{
									xtype: 'textfield',
									name: 'PersonPregnancy_PhoneWork',
									fieldLabel: 'Телефон раб.',
												width: 240,
												//gaf 11122017 #112055 del too
												autoCreate: {tag: "input", size: 11, maxLength: 11, autocomplete: "off"},
												maskRe: /\d/
											}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									xtype: 'swcommonsprcombo',
									comboSubject: 'PersonPregnancyEducation',
									hiddenName: 'PersonPregnancyEducation_id',
									fieldLabel: 'Образование',
									width: 240
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 130,
								items: [{
									xtype: 'swpostsearchcombo',
									hiddenName: 'Post_id',
									fieldLabel: 'Профессия',
									width: 320
								}]
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								xtype: 'swcommonsprcombo',
								comboSubject: 'PregnancyFamilyStatus',
								hiddenName: 'PregnancyFamilyStatus_id',
								fieldLabel: 'Семейное положение',
								width: 300
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									allowNegative: false,
									allowDecimals: false,
									allowBlank: false,
									xtype: 'numberfield',
									validator: integerValidator,
									name: 'PersonPregnancy_Height',
									fieldLabel: 'Рост (см)',
									width: 120,
									//gaf 11122017 #112144 del too 577
									autoCreate: {tag: "input", size: 3, maxLength: 3, autocomplete: "off"},
									maskRe: /\d/,
									minValue: 0,
									maxValue: 250
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 100,
								items: [{
									allowNegative: false,
									allowDecimals: true,
												xtype: 'numberfield',
												allowBlank: false,
												enableKeyEvents: true,
												//gaf22
												//validator: integerValidator,
												listeners: {
													//gaf 11122017 del too 590 635
													'keypress': function (field, e) {
														if (e.keyCode == 8 || e.keyCode == 9) {
															return true;
														}
														if (e.keyCode == 44) {
															e.browserEvent.returnValue = false;
														}
														if (e.target.selectionDirection == 'backward') {
															e.target.value = "";
															if (e.keyCode == 46) {
																e.browserEvent.returnValue = false;
															}
															return true;
														}
														var isPoint = false;
														var vval = String.fromCharCode(e.charCode)
														isPoint = vval.includes(".");
														if (isPoint) {
															if (e.target.value.includes(".")) {
																e.browserEvent.returnValue = false;
															}
															if (isPoint && e.target.value.length > 1 && e.target.value.length < 4) {
																return true;
															}
															e.browserEvent.returnValue = false;
														} else if (e.target.value.length == 3) {
															if (!e.target.value.includes(".")) {
																e.target.value = e.target.value + '.';
																return true;
															}
														} else if (e.target.value.length == 5) {
															if (e.target.value.indexOf('.') == 2) {
																e.browserEvent.returnValue = false;
															}
														}
														return true;
													},
												},
												name: 'PersonPregnancy_Weight',
												fieldLabel: 'Вес (кг)',
												width: 120,
												//gaf 11122017 #112144 del too 577
												autoCreate: {tag: "input", size: 6, maxLength: 6, autocomplete: "off"},
												maskRe: /[\d\.]/,
												minValue: 0,
												maxValue: 500
											}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 100,
								items: [{
									allowNegative: false,
									allowDecimals: false,
									xtype: 'numberfield',
									validator: integerValidator,
									name: 'PersonPregnancy_IMT',
									fieldLabel: 'ИМТ',
									width: 50,
									//gaf
									readOnly: true
										}]
									}, {
										layout: 'form',
										border: false,
								style: 'padding: 5px;',
								labelWidth: 100,
								items: [{
									xtype: 'swcheckbox',
									validator: integerValidator,
									name: 'PersonPregnancy_IsWeight25',
									hideLabel: true,
									boxLabel: 'Превышение нормы веса на 25% и более',
									width: 300,
									//gaf
									readOnly: true
								}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									xtype: 'swcommonsprcombo',
									comboSubject: 'BloodGroupType',
									hiddenName: 'BloodGroupType_id',
									fieldLabel: 'Группа крови',
									width: 120
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 100,
								items: [{
									xtype: 'swcommonsprcombo',
									comboSubject: 'RhFactorType',
									hiddenName: 'RhFactorType_id',
									fieldLabel: 'Резус-фактор',
									width: 210
								}]
							}]
						}]
					})
				]
			});

			var FatherDataPanel = new Ext.Panel({
				id: id+'_FatherData',
				style: 'padding: 5px;',
				border: false,
				labelAlign: 'right',
				layout: 'form',
				items: [
					category.AnketaFactory.createContainer({
						QuestionType_Code: 191,
						labelWidth: 120,
						itemsBefore: [{
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									editable: true,
									forceSelection: false,
									xtype: 'swpersoncombo',
									hiddenName: 'Person_did',
									fieldLabel: 'ФИО',
									onTrigger1Click: function () {
										var base_form = category.getForm();
										var combo = base_form.findField('Person_did');
										var wizard = category.wizard;

										if (combo.disabled || category.readOnly) return false;

										getWnd('swPersonSearchWindow').show({
											getPersonWorkFields: true,
											onSelect: function (personData) {
												if (personData.Person_id > 0) {
													combo.getStore().loadData([{
														Person_id: personData.Person_id,
														Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
													}]);
													combo.setValue(personData.Person_id);
													combo.collapse();
													combo.focus(true, 500);
													combo.fireEvent('change', combo);

													base_form.findField('PersonPregnancy_dadAddress').setValue(personData.PAddress_AddressText);
													base_form.findField('PersonPregnancy_dadAge').setValue(personData.Person_Age);
													base_form.findField('PersonPregnancy_dadPhone').setValue(formatPhone(personData.Person_Phone));

													if (!Ext.isEmpty(personData.Person_Work_id)) {
														base_form.findField('Org_did').getStore().load({
															params: {Org_id: personData.Person_Work_id},
															callback: function() {
																base_form.findField('Org_did').setValue(personData.Person_Work_id);
															}
														});
													}

													getPersonBloodGroup(personData.Person_id, function(bloodGroupData){
														base_form.findField('BloodGroupType_dadid').setValue(bloodGroupData.BloodGroupType_id);
														base_form.findField('RhFactorType_dadid').setValue(bloodGroupData.RhFactorType_id);
													});
												}
												getWnd('swPersonSearchWindow').hide();
											},
											onClose: function () {
												combo.focus(true, 500)
											}
										});
									},
									onTrigger2Click: function () {
										var base_form = category.getForm();
										var combo = base_form.findField('Person_did');
										var wizard = category.wizard;

										if (combo.disabled || wizard.readOnly) return false;

										combo.clearValue();
										combo.getStore().removeAll();

										//Чистим поля gaf 18112017 11122017
										region_id = getRegionNumber();
										if (region_id == 58) {region_id = 2}
										if (region_id == 2) {
											base_form.findField('PersonPregnancy_dadAge').setValue('');
											base_form.findField('PersonPregnancy_dadAddress').setValue('');
											base_form.findField('PersonPregnancy_dadPhone').setValue('');
											base_form.findField('BloodGroupType_dadid').clearValue();
											base_form.findField('RhFactorType_dadid').clearValue();
											base_form.findField('Org_did').clearValue();
											base_form.findField('Org_did').getStore().removeAll();
										}
												},
									width: 300
								}]
							}, {
								layout: 'form',
								border: false,
								items: [{
									allowNegative: false,
									allowDecimals: false,
									xtype: 'numberfield',
									validator: integerValidator,
									name: 'PersonPregnancy_dadAge',
									fieldLabel: 'Возраст',
									width: 210,
									minValue: 0
								}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									xtype: 'swcommonsprcombo',
									comboSubject: 'BloodGroupType',
									hiddenName: 'BloodGroupType_dadid',
									fieldLabel: 'Группа крови',
									width: 300
								}]
							}, {
								layout: 'form',
								border: false,
								items: [{
									xtype: 'swcommonsprcombo',
									comboSubject: 'RhFactorType',
									hiddenName: 'RhFactorType_dadid',
									fieldLabel: 'Резус-фактор',
									width: 210
								}]
							}]
						}, {
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [{
									xtype: 'textfield',
									name: 'PersonPregnancy_dadAddress',
									fieldLabel: 'Адрес проживания',
									width: 300
								}]
							}, {
								layout: 'form',
								border: false,
								items: [{
									xtype: 'textfield',
									name: 'PersonPregnancy_dadPhone',
									fieldLabel: 'Телефон',
									width: 210
								}]
							}]
						}, {
							layout: 'form',
							hidden: getRegionNick() == 'khak',
							border: false,
							items: [{
								xtype: 'sworgcomboex',
								hiddenName: 'Org_did',
								fieldLabel: 'Место работы',
								width: 420
							}]
						}, {
							layout: 'form',
							hidden: getRegionNick() != 'khak',
							border: false,
							items: [{
								xtype: 'swpostsearchcombo',
								hiddenName: 'Post_aid',
								fieldLabel: 'Профессия',
								width: 420
							}]							
						}]
					})
				]
			});

			var AnamnesDataPanel = new Ext.Panel({
				id: id+'_AnamnesData',
				style: 'padding: 5px;',
				border: false,
				labelAlign: 'right',
				layout: 'form',
				items: [
					category.AnketaFactory.createContainer({QuestionType_Code: 203}),
					category.PersonPregnancyResultGridPanel
				]
			});

			var ExtragenitalDiseasePanel = new Ext.Panel({
				id: id+'_ExtragenitalDisease',
				style: 'padding: 5px;',
				border: false,
				labelAlign: 'right',
				layout: 'form',
				items: [
					category.AnketaFactory.createContainer({QuestionType_Code: 261})
				]
			});



			category.pages = [
				CommonDataPanel,
				FatherDataPanel,
				AnamnesDataPanel,
				ExtragenitalDiseasePanel
			];

			sw.Promed.PersonPregnancy.AnketaCategory.superclass.initComponent.apply(category, arguments);
		}
	});

	o.ScreenCategory = Ext.extend(sw.Promed.WizardCategory, {
		name: 'Screen',
		objectName: 'PregnancyScreen',
		isList: true,
		inputData: inputData,
		openPregnancyScreenSopDiagEditWindow: function(action) {
			if ( !action.inlist(['add','edit','view']) ) {
				return false;
			}
			var category = this;
			var grid = category.PregnancyScreenSopDiagGridPanel.getGrid();

			if (action != 'add') {
				var record = grid.getSelectionModel().getSelected();
				if (!record || Ext.isEmpty('PregnancyScreenSopDiag_id')) {
					return false;
				}
			}

			var params = new Object();

			params.action = action;
			params.callback = function(data) {
				if ( typeof data != 'object' || typeof data.PregnancyScreenSopDiagData != 'object' ) {
					return false;
				}
				data.PregnancyScreenSopDiagData.RecordStatus_Code = 0;

				var record = grid.getStore().getById(data.PregnancyScreenSopDiagData.PregnancyScreenSopDiag_id);

				if ( typeof record == 'object' ) {
					if ( record.get('RecordStatus_Code') == 1 ) {
						data.PregnancyScreenSopDiagData.RecordStatus_Code = 2;
					}

					var grid_fields = new Array();

					grid.getStore().fields.eachKey(function(key, item) {
						grid_fields.push(key);
					});

					for ( i = 0; i < grid_fields.length; i++ ) {
						record.set(grid_fields[i], data.PregnancyScreenSopDiagData[grid_fields[i]]);
					}

					record.commit();
				} else {
					if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PregnancyScreenSopDiag_id') ) {
						grid.getStore().removeAll();
					}
					data.PregnancyScreenSopDiagData.PregnancyScreenSopDiag_id = -swGenTempId(grid.getStore());

					grid.getStore().loadData([data.PregnancyScreenSopDiagData], true);
				}
			};

			params.formParams = {};

			if ( action != 'add' ) {
				var record = grid.getSelectionModel().getSelected();
				if ( !record || !record.get('PregnancyScreenSopDiag_id') ) {
					return false;
				}
				params.formParams = record.data;
			}

			getWnd('swPregnancyScreenSopDiagEditWindow').show(params);
			return true;
		},
		deletePregnancyScreenSopDiag: function() {
			var category = this;
			var grid = category.PregnancyScreenSopDiagGridPanel.getGrid();
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('PregnancyScreenSopDiag_id'))) {
				return false;
			}

			sw.swMsg.show({
				buttons:Ext.Msg.YESNO,
				fn:function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						switch ( Number(record.get('RecordStatus_Code')) ) {
							case 0:
								grid.getStore().remove(record);
								break;

							case 1:
							case 2:
								record.set('RecordStatus_Code', 3);
								record.commit();
								grid.getStore().filterBy(function(rec) {
									return (Number(rec.get('RecordStatus_Code')) != 3);
								});
								break;
						}
						if ( grid.getStore().getCount() > 0 ) {
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}
					}
				},
				icon:Ext.MessageBox.QUESTION,
				msg:lang['vyi_hotite_udalit_zapis'],
				title:lang['podtverjdenie']
			});
			return true;
		},
		setReadOnly: function(readOnly) {
			sw.Promed.PersonPregnancy.ScreenCategory.superclass.setReadOnly.apply(this, arguments);

			this.ScreenFactory.setReadOnly(readOnly);
			this.PregnancyScreenSopDiagGridPanel.setReadOnly(readOnly);
		},
		getCategoryFormData: function(category, objectsAsJSON) {
			var base_form = category.getForm();
			var pssd_grid = category.PregnancyScreenSopDiagGridPanel.getGrid();

			var params = {};
			var Answers = {};
			var PregnancyScreenSopDiagData = [];

			base_form.items.each(function(field){
				if (!field.getName) return;
				var name = field.getName();
				var value = field.getValue();

				if (value instanceof Date) {
					value = value.format('d.m.Y');
				}
				if (result = name.match(/^QuestionType_(\d+)$/)) {
					Answers[result[1]] = value;
				}
				params[name] = value;
			});

			var sysNickList = ['amenordate', 'embriondate', 'uzidate', 'fmovedate'];
			sysNickList.forEach(function(sysNick){
				var id = category.ScreenFactory.settingsBySysNick[sysNick].QuestionType_id;
				if (Answers[id]) {
					params[sysNick] = Answers[id];
				}
			});

			pssd_grid.getStore().clearFilter();
			if (pssd_grid.getStore().getCount() > 0) {
				PregnancyScreenSopDiagData = getStoreRecords(pssd_grid.getStore());
				pssd_grid.getStore().filterBy(function(rec) {
					return (Number(rec.get('RecordStatus_Code')) != 3);
				});
			}

			if (objectsAsJSON) {
				params.Answers = Ext.util.JSON.encode(Answers);
				params.PregnancyScreenSopDiagData = Ext.util.JSON.encode(PregnancyScreenSopDiagData);
			} else {
				params.Answers = Answers;
				params.PregnancyScreenSopDiagData = PregnancyScreenSopDiagData;
			}

			return params;
		},
		beforeSaveCategory: function (category) {
			if (getRegionNick() == 'ufa' || getRegionNick() == 'penza'){
				Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_662').ownerCt.ownerCt.el.removeClass('invalidfieldset');
				//проверка обязательности заполнения одного из комбобоксов в блоке Плацентарная недостаточность и гипокция плода
				var cmb662_obbj = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_662');
				var cmb662 = cmb662_obbj.getValue();
				var cmb663 = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_663').getValue();
				var cmb664 = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_664').getValue();
				var cmb665 = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_665').getValue();
				var cmb416 = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_416').getValue();
				var cmb753 = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_753').getValue();
				var validPlacenta = true;
				var valid416753 = true; //проверка обяхательности Степень при значении Нарушения околоплодных вод: Многоводие
				var validAll = true;
					setbreakpoint3();
					
				/*15082018 убрали обязательнрсть одного из параметров в блоке «Плацентарная недостаточность и гипоксия плода»"*/	
//				if (cmb662_obbj.disabled == false){
//					if ((cmb662 == "" || cmb662 == null)  && (cmb663 == "" || cmb663 == null) && (cmb664 == "" || cmb664 == null) && (cmb665 == "" || cmb665 == null)) {
//						validPlacenta = false;
//						//Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_662').ownerCt.ownerCt.el.setStyle('color', 'rgb(0,150,0)')
//						Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_662').ownerCt.ownerCt.addClass('invalidfieldset');
//					}
//				}

				if (cmb416 != null && cmb416 != '' && cmb753 == ''){
					valid416753 = false;
					Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_753').el.setStyle('background', '#cfc')
				}else{
					Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_753').el.setStyle('background', '#fff url(images/form/text-bg.gif) repeat-x 0 0')
				}

				if (!category.getForm().isValid()) {
					validAll = false;
				}		
				if (!validPlacenta || !validAll || !valid416753) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							if (!validAll){
								var field = category.getFirstInvalidEl();
								var page = category.getPageByField(field);

								if (category.wizard.getCurrentPage() == page) {
									field.focus(true);
								} else {
									category.moveToPage(page, function() {
										category.wizard.afterPageChange();
										field.focus(true);
									});
								}
							}else{
								Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_753').focus(true);
							}
						},
						icon: Ext.Msg.WARNING,
						msg: (!validAll ? "Не все поля формы заполнены корректно, проверьте введенные вами данные. <br>Некорректно заполненные поля выделены особо.": "")+
								(!validPlacenta ? " Не выбрано ни одно из значений для элементов в блоке «Плацентарная недостаточность и гипоксия плода»" : "") +
								(!valid416753 ? " Не выбрано ни одно из значений для элемента «Степень»" : ""),
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}				

				
			}
			
			return true;
		},		
		saveCategory: function(category) {
			var base_form = category.getForm();
			var wizard = category.wizard;


			if (category.validateCategory(category, true) === false){
				return false;
			}

			if (category.beforeSaveCategory(category) === false) {
				return false;
			}

			var params = category.getCategoryFormData(category, true);

			var loadMask = wizard.getLoadMask({msg: "Подождите, идет сохранение..."});
			loadMask.show();
			
			if (getRegionNick() == 'ufa' || getRegionNick() == 'penza'){
				sw.swMsg.show({
					buttons: {cancel:true, save:true, saveopen:true},
					icon: Ext.MessageBox.QUESTION,
					msg: 'Появились ли дополнительные сведения для внесения в раздел «Анкета»?',
					title: lang['vopros'],
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'save' ) {
							category.finishsaveCategory(category, params, loadMask, false);
							return true;
						}

						if ( buttonId == 'cancel' ) {
							loadMask.hide();
							return false;
						}

						if ( buttonId == 'saveopen' ) {
							loadMask.hide();
							category.finishsaveCategory(category, params, loadMask, true);							
							return true;
						}

					}
				});
			}else{				
				category.finishsaveCategory(category, params, loadMask, false);
			}
			return true;
		},
		finishsaveCategory: function(category, params, loadMask, toAnketa) {			

			var base_form = category.getForm();		
			Ext.Ajax.request({
				params: params,
				url: '/?c=PersonPregnancy&m=savePregnancyScreen',
				success: function(response) {
					loadMask.hide();

					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success) {
						var node = Ext.getCmp('swPersonPregnancyEditWindow').TreePanel;
						category.PregnancyScreen_id = response_obj.PregnancyScreen_id;
						base_form.findField('PregnancyScreen_id').setValue(category.PregnancyScreen_id);						
						if (toAnketa){
							
							node.getNodeById('ScreenList').toggle();
							node.getNodeById('Anketa').toggle();
							var com = node.getNodeById('Anketa');
							node.fireEvent('click',com);														
						}else{

							category.PregnancyScreenSopDiagGridPanel.loadData({
								globalFilters: {PregnancyScreen_id: response_obj.PregnancyScreen_id},
								noFocusOnLoad: true
							});							
						}

						//рекомендации по акушерским осложнениям
						var wizard = category.wizard;
						var arraynotice = response_obj.ObstetricPathologyType_text;
						wizard.getnotice(arraynotice);

						category.afterSaveCategory(category);
					}
				},
				failure: function(response) {
					loadMask.hide();
				}
			});
			//return true;
		},
		setCategoryFormData: function(category, categoryData) {
			var base_form = category.getForm();
			var PregnancyScreen = categoryData;
			var data = category.inputData.getValues();

			if (Ext.isEmpty(PregnancyScreen.Evn_id) && !Ext.isEmpty(data.Evn_id)) {
				PregnancyScreen.Evn_id = data.Evn_id;
			}

			base_form.setValues(PregnancyScreen);
			var input415 = base_form.findField('QuestionType_415');
			var input1182 = base_form.findField('QuestionType_1182');
			if (input1182) {
				var hidepanel_1182 = input1182.findParentByType('panel');
			}
			if (input415 && hidepanel_1182) {
				if (input415.value == 4){
					hidepanel_1182.show();
				} else {
					hidepanel_1182.hide();
				}
			}

 			//Ufa, gaf #104945
			/*
 			var oobj = base_form.findField('QuestionType_398');
			if (typeof oobj != 'undefined'){
				if (oobj.value == "") {
					var parent = oobj.el.parent('.x-fieldset-body');
					var childfieldset = parent.dom.children;
					Ext.getCmp(childfieldset[1].id).hide();
				} else {
					var parent = oobj.el.parent('.x-fieldset-body');
					var childfieldset = parent.dom.children;
					Ext.getCmp(childfieldset[1].id).show();
				}
 			}
 			var oobj = base_form.findField('QuestionType_400');
			if (typeof oobj != 'undefined'){
				if (oobj.value == "") {
					var parent = oobj.el.parent('.x-fieldset-body');
					var childfieldset = parent.dom.children;
					Ext.getCmp(childfieldset[4].id).hide();
				} else {
					var parent = oobj.el.parent('.x-fieldset-body');
					var childfieldset = parent.dom.children;
					Ext.getCmp(childfieldset[4].id).show();
				}
 			}
			*/

			base_form.findField('MedPersonal_oid').getStore().load({
				params: {Lpu_id: base_form.findField('Lpu_oid').getValue()},
				callback: function() {
					base_form.findField('MedPersonal_oid').setValue(base_form.findField('MedPersonal_oid').getValue());
				}
			});
			
			var diag_combo = base_form.findField('Diag_id');
			var diag_id = diag_combo.getValue();
			if (diag_id) {
				diag_combo.getStore().load({
					callback: function() {
						var record = diag_combo.getStore().getById(diag_id);
						if (record) {
							diag_combo.setValue(diag_id);
						} else {
							diag_combo.setValue(null);
						}
					},
					params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
				});
			}

			if (PregnancyScreen.PregnancyScreenSopDiagData) {
				category.PregnancyScreenSopDiagGridPanel.getGrid().getStore().loadData(PregnancyScreen.PregnancyScreenSopDiagData);
			} else if(PregnancyScreen.PregnancyScreen_id > 0) {
				category.PregnancyScreenSopDiagGridPanel.loadData({
					globalFilters: {PregnancyScreen_id: PregnancyScreen.PregnancyScreen_id},
					callback: function() {
						category.collectCategoryData(category);
					},
					noFocusOnLoad: true
				});
			}
			category.collectCategoryData(category);
			
			//gaf #106655 11042018
			if (getRegionNick() == 'ufa' || getRegionNick() == 'penza'){
				var combo = base_form.findField('QuestionType_661');
				if (typeof combo != 'undefined'){
					base_form.findField('QuestionType_662').show();
					base_form.findField('QuestionType_663').show();
					base_form.findField('QuestionType_664').show();
					base_form.findField('QuestionType_665').show();			
				}
				var array_416 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_416");
				var obj_416 = Ext.getCmp(array_416[0].id);
				var childfieldset = obj_416.el.parent('.x-column-inner').dom.children;
				var oobj = Ext.getCmp(childfieldset[1].id);				
				if (obj_416.getValue() == "" || obj_416.getValue() == null){
					if (oobj)
						oobj.hide();
					var array_753 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_753");
					var oobj_753 = Ext.getCmp(array_753[0].id);
					oobj_753.setValue("");				
				}else{
					if (oobj)
						oobj.show();
				}
				
				//Назначаем видимость полей 17082018
				set395fields(Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_395")[0].checked);
				set396fields(Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_396")[0].checked);							
			}
		},
		loadCategory: function(category, showPage) {
			var base_form = category.getForm();
			var pssd_grid = category.PregnancyScreenSopDiagGridPanel.getGrid();
			var data = category.inputData.getValues();
			var wizard = category.wizard;

			if (category.loadParams.PregnancyScreen_id != category.PregnancyScreen_id) {
				category.loaded = false;
				category.PregnancyScreen_id = category.loadParams.PregnancyScreen_id;
			}

			if (category.loaded) {
				showPage();return;
			}

			var loadMask = wizard.getLoadMask({msg: "Подождите, идет формирование анкеты..."});
			loadMask.show();

			pssd_grid.getStore().removeAll();

			var onDataLoad = function() {
				base_form.reset();

				base_form.items.each(function(field){field.validate()});
				//убираем множественный выбор в скрининге
				var input395 = base_form.findField('QuestionType_395');
				var input396 = base_form.findField('QuestionType_396');
				var input397 = base_form.findField('QuestionType_397');
				if (input395 && input396 && input397){
					input395.addListener('check', function (checkbox, checked) {
						var input396 = base_form.findField('QuestionType_396');
						var input397 = base_form.findField('QuestionType_397');
						if (checked) {
							input396.setValue(false);
							input397.setValue(false);
						}
					});
					input396.addListener('check', function (checkbox, checked) {
						var input395 = base_form.findField('QuestionType_395');
						var input397 = base_form.findField('QuestionType_397');
						if (checked) {
							input395.setValue(false);
							input397.setValue(false);
						}
					});
					input397.addListener('check', function (checkbox, checked) {
						var input396 = base_form.findField('QuestionType_396');
						var input395 = base_form.findField('QuestionType_395');
						if (checked) {
							input396.setValue(false);
							input395.setValue(false);
						}
					});
				}

				var input415 = base_form.findField('QuestionType_415');
				var input1182 = base_form.findField('QuestionType_1182');
				if (input1182) {
					var hidepanel_1182 = input1182.findParentByType('panel');
				}				
				if (input415 && hidepanel_1182) {
					hidepanel_1182.hide();
					input415.addListener('change', function (combo) {
						if (combo.value == 4) {
							hidepanel_1182.show();
							category.doLayout();
						} else {
							hidepanel_1182.hide();
							category.doLayout();
						}				
					});
				}
				var input552 = base_form.findField('QuestionType_552');
				var input1183 = base_form.findField('QuestionType_1183');
				if (input1183) {
					var hidepanel_1183 = input1183.findParentByType('panel');
				}
				if (input552 && hidepanel_1183) {
					hidepanel_1183.hide();
					input552.addListener('check', function (checkbox, checked) {
						if (checked) {
							hidepanel_1183.show();
							category.doLayout();
						} else {
							hidepanel_1183.hide();
							category.doLayout();
						}
					});
				}

				//убираем множественный выбор в скрининге
				var input395 = base_form.findField('QuestionType_395');
				input395.addListener('check', function (checkbox, checked) {
					var input396 = base_form.findField('QuestionType_396');
					var input397 = base_form.findField('QuestionType_397');
					if (checked) {
						input396.setValue(false);
						input397.setValue(false);
					}
				});
				var input396 = base_form.findField('QuestionType_396');
				input396.addListener('check', function (checkbox, checked) {
					var input395 = base_form.findField('QuestionType_395');
					var input397 = base_form.findField('QuestionType_397');
					if (checked) {
						input395.setValue(false);
						input397.setValue(false);
					}
				});
				var input397 = base_form.findField('QuestionType_397');
				input397.addListener('check', function (checkbox, checked) {
					var input396 = base_form.findField('QuestionType_396');
					var input395 = base_form.findField('QuestionType_395');
					if (checked) {
						input396.setValue(false);
						input395.setValue(false);
					}
				});

				//Срок беременности по аменорее (в неделях)
				setPeriodDate = function (e) {
					var pScreenDate = base_form.findField('PregnancyScreen_setDate');
					var pScreenDateValue = pScreenDate.getValue();
					if (getRegionNick().inlist(['krym', 'astra', 'ufa', 'vologda', 'kz', 'kareliya', 'penza', 'pskov', 'perm', 'ekb', 'khak'])){
						var pPeriodAmenoree = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_358");
						if (pPeriodAmenoree.length == 1){
							Ext.Ajax.request({
								url: '/?c=PersonPregnancy&m=getAnketaForScreen',
								params: {PersonRegister_id: category.wizard.inputData.defaults.PersonRegister_id},
								callback: function(options, success, response) {
									if (success){
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if (response_obj != 'undefined' && response_obj.PersonPregnancy_Period != 'undefined' && response_obj.PersonPregnancy_setDT != 'undefined'){
											pPeriodAmenoree[0].setValue(response_obj.PersonPregnancy_Period + Math.trunc((pScreenDateValue-new Date(response_obj.PersonPregnancy_setDT).setHours(0))/1000/3600/24/7));
										}
									}
								},
								failure: function() {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.WARNING,
										msg: 'Расчет значения в поле «Срок беременности по аменорее» завершился с ошибкой',
										title: 'Предупреждение'
									});
								}
							});
						}
					}
				}
				var pScreenDate = base_form.findField('PregnancyScreen_setDate');
				if (pScreenDate){
					pScreenDate.on('change', setPeriodDate);
				}

				if (!category.PregnancyScreen_id) {
					category.loaded = true;
					base_form.findField('PersonRegister_id').setValue(data.PersonRegister_id);
					base_form.findField('Evn_id').setValue(data.Evn_id);
					base_form.findField('Lpu_oid').setValue(data.Lpu_id);

					base_form.findField('MedPersonal_oid').setValue(data.MedPersonal_id);
					base_form.findField('MedPersonal_oid').getStore().load({
						params: {Lpu_id: base_form.findField('Lpu_oid').getValue()},
						callback: function() {
							base_form.findField('MedPersonal_oid').setValue(base_form.findField('MedPersonal_oid').getValue());
						}
					});

					category.collectCategoryData(category);
					//Ufa, gaf #104945
					var oobj = base_form.findField('QuestionType_583');
					if (typeof oobj != 'undefined'){
						var parent = oobj.el.parent('.x-fieldset-body');
						var childfieldset = parent.dom.children;
						Ext.getCmp(childfieldset[1].id).hide();
						Ext.getCmp(childfieldset[4].id).hide();
					}
					
					if (getRegionNick() == 'ufa' || getRegionNick() == 'penza'){
						
						var array_356 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_356");
						var array_357 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_357");
						
						var array_416 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_416");
						var obj_416 = Ext.getCmp(array_416[0].id);
						var childfieldset = obj_416.el.parent('.x-column-inner').dom.children;
						var oobj = Ext.getCmp(childfieldset[1].id);
						if (oobj)
							oobj.hide();
					
						var array_753 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_753");
						var oobj_753 = Ext.getCmp(array_753[0].id);
						oobj_753.setValue("");
						
						Ext.getCmp(array_356[0].id).setDisabled(true);
						Ext.getCmp(array_357[0].id).setDisabled(true);									
						$(".disabled_field").parent().prev().css("color", "#222");
						Ext.getCmp(array_356[0].id).setDisabled(false);
						Ext.getCmp(array_357[0].id).setDisabled(false);							
						
					}

					loadMask.hide();
					showPage();
				} else {
					category.getCurrentPage().show();
					category.getCurrentPage().doLayout();

					var categoryData = category.getCategoryData(category);
					if (category.allowCollectData && categoryData && categoryData.loaded) {
						category.loaded = true;
						category.setCategoryFormData(category, categoryData);

						loadMask.hide();
						showPage();
					} else {
						Ext.Ajax.request({
							url: '/?c=PersonPregnancy&m=loadPregnancyScreen',
							params: {PregnancyScreen_id: category.PregnancyScreen_id},
							success: function (response) {
								category.loaded = true;
								var response_obj = Ext.util.JSON.decode(response.responseText);

								category.setCategoryFormData(category, response_obj[0]);

								loadMask.hide();
								showPage();
							},
							failure: function() {
								loadMask.hide();
							}
						});
					}
				}
			};

			if (category.ScreenFactory.loaded) {
				onDataLoad();
			} else {
				category.ScreenFactory.loadSettings(function() {
					category.initFields();
					category.showPages();
					category.doLayout();
					category.hidePages();

					onDataLoad();
				});
			}

		},
		initCategory: function(category) {
			sw.Promed.PersonPregnancy.ScreenCategory.superclass.initCategory.apply(category, arguments);

			category.ScreenFactory.loaded = false;

			category.PregnancyScreenSopDiagGridPanel.removeAll();
		},
		initComponent: function() {
			var category = this;
			var id = category.getId();

			category.ScreenFactory = new sw.Promed.QuestionType.Factory({
				DispClass_id: 16,
				loadComboCallback: function() {
					if (getRegionNick() == 'astra') {
						category.getForm().findField('QuestionType_414').lastQuery = '';
						category.getForm().findField('QuestionType_414').setBaseFilter(function(rec) {
							return rec.get('Preeclampsia_Code').inlist([4,6,8]);
						});
					}
				}
			});

			category.PregnancyScreenSopDiagGridPanel = new sw.Promed.ViewFrame({
				id: id+'_PregnancyScreenSopDiagGridPanel',
				dataUrl: '/?c=PersonPregnancy&m=loadPregnancyScreenSopDiagGrid',
				title: 'Сопутствующие диагнозы',
				autoLoadData: false,
				useEmptyRecord: false,
				focusOnFirstLoad: false,
				noFocusOnLoadOneTime: true,
				style: 'margin-top: 5px; margin-bottom: 5px;',
				stringfields: [
					{name: 'PregnancyScreenSopDiag_id', type: 'int', header: 'ID', key: true},
					{name: 'PregnancyScreen_id', type: 'int', hidden: true},
					{name: 'RecordStatus_Code', type: 'int', hidden: true},
					{name: 'Diag_id', type: 'int', hidden: true},
					{name: 'DiagSetClass_id', type: 'int', hidden: true},
					{name: 'DiagSetClass_Name', header: 'Вид', type: 'string', width: 160},
					{name: 'Diag_FullName', header: 'Диагноз', type: 'string', id: 'autoexpand'}
				],
				actions: [
					{name:'action_add', handler: function(){category.openPregnancyScreenSopDiagEditWindow('add')}},
					{name:'action_edit', handler: function(){category.openPregnancyScreenSopDiagEditWindow('edit')}},
					{name:'action_view', handler: function(){category.openPregnancyScreenSopDiagEditWindow('view')}},
					{name:'action_delete', handler: function(){category.deletePregnancyScreenSopDiag()}},
					{name:'action_refresh', hidden: true}
				]
			});

			var ScreenPanel = new Ext.Panel({
				id: id+'_Screen',
				style: 'padding: 5px;',
				border: false,
				labelAlign: 'right',
				layout: 'form',
				items: [
					{
						xtype: 'hidden',
						name: 'PregnancyScreen_id'
					}, {
						xtype: 'hidden',
						name: 'PersonRegister_id'
					}, {
						xtype: 'hidden',
						name: 'Evn_id'
					}, {
						xtype: 'hidden',
						name: 'PregnancyScreen_RiskPerPat'
					}, {
						layout: 'form',
						border: false,
						labelWidth: 60,
						items: [{
							allowBlank: false,
							xtype: 'swdatefield',
							name: 'PregnancyScreen_setDate',
							fieldLabel: 'Дата',
							width: 120
						}]
					}, {
						layout: 'column',
						border: false,
						items: [{
							layout: 'form',
							border: false,
							labelWidth: 60,
							items: [{
								allowBlank: false,
								xtype: 'swlpucombo',
								hiddenName: 'Lpu_oid',
								fieldLabel: 'МО',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = category.getForm();

										var med_personal_combo = base_form.findField('MedPersonal_oid');
										var med_personal_id = med_personal_combo.getValue();
										med_personal_combo.getStore().load({
											params: {Lpu_id: newValue},
											callback: function() {
												var record = med_personal_combo.getStore().getById(med_personal_id);
												if (record) {
													med_personal_combo.setValue(med_personal_id);
												} else {
													med_personal_combo.setValue(null);
												}
											}
										});
									}
								},
								width: 320
							}]
						}, {
							layout: 'form',
							border: false,
							labelWidth: 60,
							items: [{
								allowBlank: false,
								xtype: 'swmedpersonalallcombo',
								hiddenName: 'MedPersonal_oid',
								fieldLabel: 'Врач',
								width: 320
							}]
						}]
					},
					category.ScreenFactory.createContainer({root: true}),
					{
						xtype: 'fieldset',
						title: 'Заключение',
						autoHeight: true,
						style: 'padding: 2px 10px;',
						listeners: {
							'afterlayout': function(panel) {
								panel.setWidth(panel.container.getWidth());
							}
						},
						items: [
							{
								layout: 'form',
								border: false,
								labelWidth: 120,
								items: [{
									allowBlank: false,
									xtype: 'swdiagcombo',
									fieldLabel: 'Основной диагноз',
									hiddenName: 'Diag_id',
									width: 400,
									//gaf #109848
									Diag_level3_code: 'Z32ANDZ33ANDZ34ANDZ35ANDZ36ANDO',
									registryType: 'PersonPregnansy'
								}]
							},
							//Ufa, gaf #104945   
							 {
								layout: 'form',
								border: false,
								labelWidth: 120,
								hidden: getRegionNumber() != 2 && getRegionNumber() != 58,
								items: [{
									allowBlank: getRegionNumber() != 2/* && getRegionNumber() != 58*/,
									xtype: 'swcommonsprcombo',
									comboSubject: 'GestationalAge',
									hiddenName: 'GestationalAge_id',
									fieldLabel: 'Срок беременности',
									width: 400
								}]
							},
							category.PregnancyScreenSopDiagGridPanel,
							{
								layout: 'form',
								border: false,
								labelAlign: 'top',
								items: [{
									xtype: 'textarea',
									fieldLabel: 'Замечания',
									name: 'PregnancyScreen_Comment',
									autoCreate: {tag: "textarea", autocomplete: "off", maxlength: 500},
									anchor: '100%',
									height: 60
								}]
							}
						]
					}
				]
			});

			category.pages = [ScreenPanel];

			sw.Promed.PersonPregnancy.ScreenCategory.superclass.initComponent.apply(category, arguments);
		}
	});

	o.EvnListCategory = Ext.extend(sw.Promed.WizardCategory, {
		name: 'EvnList',
		toolbar: false,
		inputData: inputData,
		openPersonEmkWindow: function(EvnClass_SysNick, Evn_id) {
			var category = this;
			var data = category.inputData.getValues();
			var searchNodeObj = {
				parentNodeId: 'root',
				last_child: false,
				disableLoadViewForm: false,
				EvnClass_SysNick: EvnClass_SysNick,
				Evn_id: Evn_id
			};
			var userMedStaffFact = data.userMedStaffFact;
			var emk = getWnd('swPersonEmkWindow');
			if (emk.isVisible()) {
				if (this.Person_id == emk.Person_id) {
					var sparams = {
						parent_node: emk.Tree.getRootNode(),
						last_child: false,
						disableLoadViewForm: searchNodeObj.disableLoadViewForm,
						node_attr_name: 'id',
						node_attr_value: searchNodeObj.EvnClass_SysNick +'_'+ searchNodeObj.Evn_id
					};
					emk.searchNodeInTreeAndLoadViewForm(sparams);
					emk.toFront();
				} else {
					sw.swMsg.alert(lang['soobschenie'], lang['forma_elektronnoy_istorii_bolezni_emk_v_dannyiy_moment_otkryita']);
				}
			} else {
				var params = {
					Person_id: data.Person_id,
					//Server_id: p.Server_id,
					//PersonEvn_id: p.PersonEvn_id,
					searchNodeObj: searchNodeObj,
					userMedStaffFact: userMedStaffFact,
					ARMType: userMedStaffFact.ARMType
				};
				if (params.ARMType == 'stac') {
					params.ARMType = 'common';
					params.addStacActions = ['action_New_EvnPS', 'action_StacSvid', 'action_EvnPrescrVK'];

					Ext.Ajax.request({
						url: '/?c=EvnPS&m=beforeOpenEmk',
						params: {Person_id: params.Person_id},
						success: function(response) {
							var answer = Ext.util.JSON.decode(response.responseText);
							if(!Ext.isArray(answer) || !answer[0]) {
								showSysMsg(lang['pri_poluchenii_dannyih_dlya_proverok_proizoshla_oshibka_nepravilnyiy_otvet_servera']);
								return false;
							}
							if (answer[0].countOpenEvnPS > 0) {
								//showSysMsg('Создание новых КВС недоступно','У пациента имеются открытые КВС в даннном ЛПУ! Количество открытых КВС: '+ answer[0].countOpenEvnPS);
								//emk_params.addStacActions = ['action_StacSvid']; //лочить кнопку создания случая лечения, если есть незакрытые КВС в данном ЛПУ #13272
								params.disAddPS = answer[0].countOpenEvnPS;
							}
							emk.show(params);
						}
					});
				} else {
					emk.show(params);
				}
			}
		},
		evnNumCardRenderer: function(value, meta, record) {
			var category = this;
			var text = value;
			var Evn_id = record.get('Evn_id');
			var EvnClass_SysNick = record.get('EvnClass_SysNick');
			if (!Ext.isEmpty(Evn_id) && !Ext.isEmpty(EvnClass_SysNick)) {
				var style = 'color: #000079;text-decoration: underline;cursor: pointer;';
				var onclick = "Ext.getCmp('"+category.getId()+"').openPersonEmkWindow('"+EvnClass_SysNick+"','"+Evn_id+"');";
				var text = '<span style="'+style+'" onclick="'+onclick+'">'+value+'</span>';
			}
			return text;
		},
		loadCategory: function(category, showPage) {
			var grid = category.EvnGridPanel.getGrid();
			var data = category.inputData.getValues();
			var wizard = category.wizard;

			if (!data.PersonRegister_id || data.PersonRegister_id < 0) {
				showPage();
				return;
			}

			var loadMask = wizard.getLoadMask({msg: "Получение случаев лечения..."});
			loadMask.show();

			grid.getStore().load({
				params: {
					PersonRegister_id: data.PersonRegister_id,
					start: 0,
					limit: 100
				},
				callback: function() {
					loadMask.hide();
					showPage();
				}
			});
		},
		initComponent: function() {
			var category = this;
			var id = category.getId();

			category.EvnGridPanel = new sw.Promed.ViewFrame({
				id: id+'_EvnGridPanel',
				dataUrl: '/?c=PersonPregnancy&m=loadPersonPregnancyEvnGrid',
				autoLoadData: false,
				contextmenu: false,
				toolbar: false,
				border: false,
				pageSize: 100,
				paging: true,
				root: 'data',
				totalProperty: 'totalCount',
				stringfields: [
					{name: 'Evn_id', type: 'int', header: 'ID', key: true},
					{name: 'EvnClass_SysNick', type: 'string', hidden: true},
					{name: 'Evn_setDate', header: 'Дата начала', type: 'date', width: 80},
					{name: 'Evn_disDate', header: 'Дата окончания', type: 'date', width: 80},
					{name: 'EvnType', header: 'Тип случая', type: 'string', width: 100},
					{name: 'Lpu_Nick', header: 'МО', type: 'string', width: 280},
					{name: 'Evn_NumCard', header: 'Номер карты', renderer: category.evnNumCardRenderer.createDelegate(category), width: 100},
					{name: 'Diag_FullName', header: 'Диагноз', type: 'string', id: 'autoexpand'},
					{name: 'EvnResult', header: 'Результат', type: 'string', width: 120},
					{name: 'CreatedObjects', header: 'События', type: 'string', width: 210}
				],
				listeners: {
					'afterlayout': function(gridPanel) {
						var wizard = category.wizard;
						if (wizard && wizard.rendered) {
							var height = wizard.getSize().height;
							var toolbar = wizard.DataToolbar;
							if (!toolbar.hidden) {
								height -= toolbar.getSize().height;
							}
							gridPanel.setHeight(height-2);
						}
					}
				}
			});

			category.pages = [category.EvnGridPanel];

			sw.Promed.PersonPregnancy.EvnListCategory.superclass.initComponent.apply(category, arguments);
		}
	});

	var uslugaNameRenderer = function(value, meta, record) {
		var category = this;
		var text = value;
		if (!Ext.isEmpty(record.get('EvnXml_id'))) {
			var id = record.get('EvnXml_id');
			var style = 'color: #000079;text-decoration: underline;cursor: pointer;';
			text = '<span style="'+style+'" onclick="sw.Promed.PersonPregnancy.openEvnXmlViewWindow('+id+');">'+value+'</span>';
		}
		return text;
	};

	o.ConsultationListCategory = Ext.extend(sw.Promed.WizardCategory, {
		name: 'ConsultationList',
		toolbar: false,
		inputData: inputData,
		loadCategory: function(category, showPage) {
			var grid = category.ConsultationGridPanel.getGrid();
			var data = category.inputData.getValues();
			var wizard = category.wizard;

			if (!data.PersonRegister_id || data.PersonRegister_id < 0) {
				showPage();
				return;
			}

			var loadMask = wizard.getLoadMask({msg: "Получение списка консультаций..."});
			loadMask.show();

			grid.getStore().load({
				params: {
					PersonRegister_id: data.PersonRegister_id,
					start: 0,
					limit: 100
				},
				callback: function() {
					loadMask.hide();
					showPage();
				}
			});
		},
		//gaf 22122017 #105948 
		openConsultWindow: function (action, gridPanel) {
			if (!action || !action.inlist(['add', 'edit', 'view'])) {
				return false;
			}

			var base_form = this.getForm();
			var params = {
				action: action,
				userMedStaffFact: this.userMedStaffFact
			};
			params.callback = function () {
				gridPanel.getAction('action_refresh').execute();
			};
			var that = this;
			if (action == 'add') {
				getWnd('swPersonPregnancyAddResearchWindow').show({
					onClose: Ext.emptyFn,
					Person_id: this.inputData.defaults.Person_id,
					TypeUsluga: "consult"
				});
			} else {
				var record = gridPanel.getGrid().getSelectionModel().getSelected();
				params.Evn_id = record.json.EvnUsluga_id;
				params.Person_id = this.inputData.defaults.Person_id;
				params.UslugaComplex_id = record.json.UslugaComplex_id;				
				
				params.Research_Data = record.json.EvnUsluga_setDate;
				params.Lpu_id = record.json.Lpu_id;
				params.editType = that.editType;
				params.MedPersonal_iidd = record.json.MedPersonal_FIO;
				params.TypeUsluga = "consult";
				getWnd('swPersonPregnancyAddResearchWindow').show(params);
			}
			return true;
		},
		//gaf 09052018 #105948 
		deleteData: function(gridPanel)
		{
			var record = gridPanel.getGrid().getSelectionModel().getSelected();
			var params = {
				Evn_id: record.json.EvnUsluga_id
			};

			sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNO,
				scope : Ext.getCmp('ResearchList'),
				fn: function(buttonId) 
				{
					if ( buttonId == 'yes' )
					{
						Ext.Ajax.request(
						{
							url: '/?c=EvnUslugaPrivateClinic&m=deleteData',
							params: params,
							callback: function(options, success, response) 
							{
								if (success)
								{
									gridPanel.getAction('action_refresh').execute()
								}
							}
						});
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: "Удаление записи",
				title: 'Вопрос'
			});
		},			
		initComponent: function() {
			var category = this;
			var id = category.getId();

			category.ConsultationGridPanel = new sw.Promed.ViewFrame({
				//gaf 22122017 #105948
				actions: [
					{name: 'action_add', handler: function () { category.openConsultWindow('add', category.ConsultationGridPanel) }},
					{name: 'action_edit', handler: function () { category.openConsultWindow('edit', category.ConsultationGridPanel) }},
					{name:'action_view', hidden: true},
					{name:'action_delete', handler: function() { category.deleteData(category.ConsultationGridPanel); }}
				],				
				id: id+'_ConsultationGridPanel',
				dataUrl: '/?c=PersonPregnancy&m=loadConsultationGrid',
				autoLoadData: false,
				contextmenu: false,
				toolbar: getRegionNumber() == 2 || getRegionNumber() == 58,
				border: false,
				pageSize: 100,
				paging: true,
				root: 'data',
				totalProperty: 'totalCount',
				stringfields: [
					{name: 'EvnUsluga_id', type: 'int', header: 'ID', key: true},
					{name: 'UslugaComplex_id', type: 'int', hidden: true},
					{name: 'EvnXml_id', type: 'int', hidden: true},
					{name: 'EvnUsluga_setDate', header: 'Дата', type: 'date', width: 80},
					{name: 'Lpu_Nick', header: 'МО', type: 'string', width: 280},
					{name: 'MedPersonal_FIO', header: 'Врач', type: 'string', width: 200},
					{name: 'UslugaComplex_FullName', header: 'Услуга', renderer: uslugaNameRenderer.createDelegate(category), id: 'autoexpand'},
					{name: 'pmUser', type: 'int', hidden: true}
				],
				listeners: {
					'afterlayout': function(gridPanel) {
						var wizard = category.wizard;
						if (wizard && wizard.rendered) {
							var height = wizard.getSize().height;
							var toolbar = wizard.DataToolbar;
							if (!toolbar.hidden) {
								height -= toolbar.getSize().height;
							}
							gridPanel.setHeight(height-2);
						}
					}
				},
				onRowSelect: function (sm, rowIdx, record) {				
					this.setActionDisabled('action_delete', record.data.pmUser != getGlobalOptions().pmuser_id);

 				}
			});

			category.pages = [category.ConsultationGridPanel];

			sw.Promed.PersonPregnancy.ConsultationListCategory.superclass.initComponent.apply(category, arguments);
		}
	});

	o.ResearchListCategory = Ext.extend(sw.Promed.WizardCategory, {
		name: 'ResearchList',
		toolbar: false,
		inputData: inputData,
		loadCategory: function(category, showPage) {
			var grid = category.ResearchGridPanel.getGrid();
			var data = category.inputData.getValues();
			var wizard = category.wizard;

			if (!data.PersonRegister_id || data.PersonRegister_id < 0) {
				showPage();
				return;
			}

			var loadMask = new Ext.LoadMask(wizard.getMaskEl(), {msg: "Получение списка исследований..."});
			loadMask.show();

			grid.getStore().load({
				params: {
					PersonRegister_id: data.PersonRegister_id,
					start: 0,
					limit: 100
				},
				callback: function() {
					loadMask.hide();
					showPage();
				}
			});
		},
		//gaf 22122017 #105948		
		openResearchWindow: function (action, gridPanel) {

			if (!action || !action.inlist(['add', 'edit', 'view'])) {
				return false;
			}

			var base_form = this.getForm();
			var params = {
				action: action,
				userMedStaffFact: this.userMedStaffFact
			};
			params.callback = function () {
				gridPanel.getAction('action_refresh').execute();
			};
			var that = this;
 			if (action == 'add') {
 				getWnd('swPersonPregnancyAddResearchWindow').show({
 					onClose: Ext.emptyFn,
					Person_id: this.inputData.defaults.Person_id,
					TypeUsluga: "research"
 				});
 			} else {
 				var record = gridPanel.getGrid().getSelectionModel().getSelected();
				params.Evn_id = record.json.EvnUsluga_id;
				params.Person_id = this.inputData.defaults.Person_id;
				params.UslugaComplex_id = record.json.UslugaComplex_id;				
				
				params.Research_Data = record.json.EvnUsluga_setDate;
				params.Lpu_id = record.json.Lpu_id;
 				params.editType = that.editType;
				params.MedPersonal_iidd = record.json.MedPersonal_FIO;
				params.TypeUsluga = "research";
 				getWnd('swPersonPregnancyAddResearchWindow').show(params);
 			}
			return true;

		},
		//gaf 09052018 #105948 
		deleteData: function(gridPanel)
		{
			var record = gridPanel.getGrid().getSelectionModel().getSelected();
			var params = {
				Evn_id: record.json.EvnUsluga_id
			};

			sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNO,
				scope : Ext.getCmp('ResearchList'),
				fn: function(buttonId) 
				{
					if ( buttonId == 'yes' )
					{
						Ext.Ajax.request(
						{
							url: '/?c=EvnUslugaPrivateClinic&m=deleteData',
							params: params,
							callback: function(options, success, response) 
							{
								if (success)
								{
									gridPanel.getAction('action_refresh').execute()
								}
							}
						});
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: "Удаление записи",
				title: 'Вопрос'
			});
 		},		
		initComponent: function () {
			var category = this;
			var id = category.getId();

			category.ResearchGridPanel = new sw.Promed.ViewFrame({
				//gaf 22122017 #105948
				actions: [
					{name: 'action_add', handler: function () { category.openResearchWindow('add', category.ResearchGridPanel) }},
					{name: 'action_edit', handler: function () { category.openResearchWindow('edit', category.ResearchGridPanel) }},
					{name:'action_view', hidden: true},
					{name:'action_delete', handler: function() { category.deleteData(category.ResearchGridPanel); }}
				],
				id: id + '_ResearchGridPanel',
				dataUrl: '/?c=PersonPregnancy&m=loadResearchGrid',
				autoLoadData: false,
				contextmenu: false,
				//gaf open toolbar 22122017
				toolbar: getRegionNumber() == 2 || getRegionNumber() == 58,
				border: false,
				pageSize: 100,
				paging: true,
				root: 'data',
				totalProperty: 'totalCount',
				stringfields: [
					{name: 'EvnUsluga_id', type: 'int', header: 'ID', key: true},
					{name: 'UslugaComplex_id', type: 'int', hidden: true},
					{name: 'EvnXml_id', type: 'int', hidden: true},
					{name: 'EvnUsluga_setDate', header: 'Дата', type: 'date', width: 80},
					{name: 'Lpu_Nick', header: 'МО', type: 'string', width: 280},
					{name: 'MedPersonal_FIO', header: 'Врач', type: 'string', width: 200},
					{name: 'UslugaComplex_FullName', header: 'Услуга', renderer: uslugaNameRenderer.createDelegate(category), id: 'autoexpand'},
					{name: 'pmUser', type: 'int', hidden: true}
				],
				listeners: {
					'afterlayout': function(gridPanel) {
						var wizard = category.wizard;
						if (wizard && wizard.rendered) {
							var height = wizard.getSize().height;
							var toolbar = wizard.DataToolbar;
							if (!toolbar.hidden) {
								height -= toolbar.getSize().height;
							}
							gridPanel.setHeight(height-2);
						}
					}
				},
				onRowSelect: function (sm, rowIdx, record) {
					this.setActionDisabled('action_delete', record.data.pmUser != getGlobalOptions().pmuser_id);
 				}
			});

			category.pages = [category.ResearchGridPanel];

			sw.Promed.PersonPregnancy.ResearchListCategory.superclass.initComponent.apply(category, arguments);
		}
	});

	o.CertificateCategory = Ext.extend(sw.Promed.WizardCategory, {
		name: 'Certificate',
		objectName: 'BirthCertificate',
		inputData: inputData,
		getCategoryFormData: function(category, objectsAsJSON) {
			return category.getForm().getValues();
		},
		saveCategory: function(category) {
			var base_form = category.getForm();
			var wizard = category.wizard;

			if (category.validateCategory(category, true) === false){
				return false;
			}

			if (category.beforeSaveCategory(category) === false) {
				return false;
			}

			var params = category.getCategoryFormData(category);

			var loadMask = wizard.getLoadMask({msg: "Подождите, идет сохранение..."});
			loadMask.show();

			Ext.Ajax.request({
				url: '/?c=PersonPregnancy&m=saveBirthCertificate',
				params: params,
				success: function(response) {
					loadMask.hide();

					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success) {
						category.BirthCertificate_id = response_obj.BirthCertificate_id;
						base_form.findField('BirthCertificate_id').setValue(category.BirthCertificate_id);

						wizard.afterSaveCategory(category);
					}
				},
				failure: function(response) {
					loadMask.hide();
				}
			});
			return true;
		},
		setCategoryFormData: function(category, categoryData) {
			var data = category.inputData.getValues();

			if (Ext.isEmpty(categoryData.Evn_id) && !Ext.isEmpty(data.Evn_id)) {
				categoryData.Evn_id = data.Evn_id;
			}

			category.getForm().setValues(categoryData);
			category.collectCategoryData(category);
		},
		loadCategory: function(category, showPage) {
			var base_form = category.getForm();
			var wizard = category.wizard;
			var data = category.inputData.getValues();

			if (category.loadParams.BirthCertificate_id != category.BirthCertificate_id) {
				category.loaded = false;
				category.BirthCertificate_id = category.loadParams.BirthCertificate_id;
			}

			if (category.loaded) {
				showPage();return;
			}

			var loadMask = wizard.getLoadMask({msg: "Подождите, идет формирование анкеты..."});
			loadMask.show();

			var onDataLoad = function() {
				base_form.reset();

				base_form.items.each(function(field){field.validate()});

				if (Ext.isEmpty(category.BirthCertificate_id)) {
					category.loaded = true;

					base_form.findField('Evn_id').setValue(data.Evn_id);
					base_form.findField('Lpu_oid').setValue(data.Lpu_id);
					base_form.findField('PersonRegister_id').setValue(data.PersonRegister_id);
					base_form.findField('BirthCertificate_setDate').setValue(new Date());

					category.collectCategoryData(category);

					loadMask.hide();
					setTimeout(showPage, 5);
				} else {
					category.getCurrentPage().show();
					category.getCurrentPage().doLayout();

					var categoryData = category.getCategoryData(category);
					if (category.allowCollectData && categoryData && categoryData.loaded) {
						category.loaded = true;
						category.setCategoryFormData(category, categoryData);

						loadMask.hide();
						setTimeout(showPage, 5);
					} else {
						Ext.Ajax.request({
							url: '/?c=PersonPregnancy&m=loadBirthCertificate',
							params: {BirthCertificate_id: category.BirthCertificate_id},
							success: function (response) {
								category.loaded = true;
								var response_obj = Ext.util.JSON.decode(response.responseText);

								category.setCategoryFormData(category, response_obj[0]);

								loadMask.hide();
								showPage();
							},
							failure: function() {
								loadMask.hide();
							}
						});
					}
				}
			};

			onDataLoad();
		},
		initComponent: function() {
			var category = this;
			var id = category.getId();

			var BirthCertificatePanel = new Ext.Panel({
				id: id+'_BirthCertificate',
				style: 'padding: 5px;',
				border: false,
				labelAlign: 'right',
				labelWidth: 130,
				layout: 'form',
				items: [{
					xtype: 'hidden',
					name: 'BirthCertificate_id'
				}, {
					xtype: 'hidden',
					name: 'PersonRegister_id'
				}, {
					xtype: 'hidden',
					name: 'Evn_id'
				}, {
					allowBlank: false,
					xtype: 'textfield',
					name: 'BirthCertificate_Ser',
					fieldLabel: 'Серия',
					autoCreate: {tag: 'input', type: 'text', autocomplete: 'off', maxlength: 3},
					width: 120,
					minValue: 0
				}, {
					allowBlank: false,
					xtype: 'numberfield',
					name: 'BirthCertificate_Num',
					fieldLabel: 'Номер',
					maskRe: /[0-9]/,
					autoCreate: {tag: 'input', type: 'text', autocomplete: 'off', maxlength: 7},
					width: 120,
					minValue: 0
				}, {
					allowBlank: false,
					xtype: 'swdatefield',
					name: 'BirthCertificate_setDate',
					fieldLabel: 'Дата выдачи',
					width: 120
				}, {
					allowBlank: false,
					xtype: 'swlpucombo',
					hiddenName: 'Lpu_oid',
					fieldLabel: 'МО родоразрешения',
					width: 300
				}]
			});

			category.pages = [BirthCertificatePanel];

			sw.Promed.PersonPregnancy.CertificateCategory.superclass.initComponent.apply(category, arguments);
		}
	});

	o.ResultCategory = Ext.extend(sw.Promed.WizardCategory, {
		name: 'Result',
		id:'resultCategoryPanel',
		objectName: 'BirthSpecStac',
		inputData: inputData,
		afterPregnancyResultChange: Ext.emptyFn,
		onPregnancyResultChange: function(options) {
			var category = this;
			var base_form = category.getForm();

			var PregnancyResult_Code = Number(base_form.findField('PregnancyResult_id').getFieldValue('PregnancyResult_id'));
			var id = category.getId();
			Ext.getCmp(id+'_ResultBirthPanel').hide();
			Ext.getCmp(id+'_ResultAbortPanel').hide();
			Ext.getCmp(id+'_SurgeryVolumePanel').hide();
			//снимаем обязательность полей Особенность родов, Кесарево сечение
			var field546 = base_form.findField('QuestionType_546');
			var field546v = '';
			var field771 = base_form.findField('QuestionType_771');
			var fieldBirthSpec_id = base_form.findField('BirthSpec_id');
			var fieldCountBirth = base_form.findField('BirthSpecStac_CountBirth');
			var IsOperationCaesarian = base_form.findField('IsOperationCaesarian');
			if (field546) {
				field546v = field546.getValue();
				if (field546v) {
					if (field771) {
						field771.setAllowBlank(true);
					}
				}
			}
			if (fieldBirthSpec_id) {
				fieldBirthSpec_id.setAllowBlank(true);
			}
			if (fieldCountBirth){
				fieldCountBirth.setAllowBlank(true);
			}
			var laborActivity = base_form.findField('LaborActivity_id');
			if (laborActivity){
				laborActivity.findParentByType('panel').setVisible(false);
			}
			switch(PregnancyResult_Code) {
				case 1:	//Роды
					Ext.getCmp(id+'_ResultBirthPanel').show();
					Ext.getCmp(id+'_ResultBirthPanel').doLayout();
					if(IsOperationCaesarian && IsOperationCaesarian.getValue() == '1'){
						//устанаваливаем обязательность полей Особенность родов, Кесарево сечение
						if (field771 && field546) {
							field546.setValue(true);
							field771.setAllowBlank(false);
						}
						if (fieldCountBirth){
							fieldCountBirth.setAllowBlank(false);
						}
						if (fieldBirthSpec_id){
							fieldBirthSpec_id.setAllowBlank(false);
						}
					}

					if (laborActivity){
						laborActivity.findParentByType('panel').setVisible(true);
					}
					break;
				case 2:	//Самопроизвольный аборт
					//???
					//Ext.getCmp(id+'_ResultAbortPanel').show();
					break;
				case 3:	//Искусственный аборт
					//фильтр по дате http://redmine.swan.perm.ru/issues/141356
					base_form.findField('AbortMethod_id').getStore().filterBy(function(rec) {
						return Ext.isEmpty(rec.get('AbortMethod_endDate'));
					});
					Ext.getCmp(id+'_ResultAbortPanel').show();
					break;
				case 4:	//Внематочная беременность
					Ext.getCmp(id+'_SurgeryVolumePanel').show();
					break;
			}

			category.refreshPregnancyResultDisable();
			category.afterPregnancyResultChange(options);
		},
        validateCategory: function(category, showMsg) {
            if (category.getForm().isValid()) {
                return true;
            }
            if (showMsg) {
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    fn: function() {
                        var field = category.getFirstInvalidEl();
                        var page = category.getPageByField(field);

                        if (category.wizard.getCurrentPage() == page) {
                            if (field)
                                field.focus(true);
                        } else if (page){
                            category.moveToPage(page, function() {
                                category.wizard.afterPageChange();
                                if (field)
                                    field.focus(true);
                            });
                        }
                    },
                    icon: Ext.Msg.WARNING,
                    msg: (typeof showMsg == 'string')?showMsg:ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
            }
            return false;
        },
		setReadOnly: function(readOnly) {
			sw.Promed.PersonPregnancy.ResultCategory.superclass.setReadOnly.apply(this, arguments);

			this.ResultScreenFactory.setReadOnly(readOnly);
			this.IntrFactorsFactory.setReadOnly(readOnly);
			this.ChildGridPanel.setReadOnly(readOnly);
			this.ChildDeathGridPanel.setReadOnly(readOnly);
		},
		getCategoryFormData: function(category, objectsAsJSON) {
			var base_form = category.getForm();
			var childGrid = category.ChildGridPanel.getGrid();
			var childDeathGrid = category.ChildDeathGridPanel.getGrid();

			var params = {};
			var Answers = {};
			//var ChildData = [];
			var PersonNewBorn_ids = [];
			var ChildDeathData = [];

			base_form.items.each(function(field){
				if (!field.getName) return;
				var name = field.getName();
				var value = field.getValue();

                //обработка компоненты radiobutton
                if (value === undefined){
                    value = "";
                    var radioitem = field.items.find(function(comp){return comp.checked});
                    if (radioitem){
                        value = radioitem.inputValue
                    }
                }

				if (value instanceof Date) {
					value = value.format('d.m.Y');
				}
				if (result = name.match(/^QuestionType_(\d+)$/)) {
					Answers[result[1]] = value;
				}
				params[name] = value;
			});

			if (category.ignoreCheckBirthSpecStacDate) {
				params.ignoreCheckBirthSpecStacDate = 1;
				delete category.ignoreCheckBirthSpecStacDate;
			}
			if (category.ignoreCheckChildrenCount) {
				params.ignoreCheckChildrenCount = 1;
				delete category.ignoreCheckChildrenCount;
			}

			//Новорожденные сохраняются сразу
			//childGrid.getStore().clearFilter();
			//if (childGrid.getStore().getCount() > 0) {
			//	ChildData = getStoreRecords(childGrid.getStore(), {convertDateFields:true});
			//	childGrid.getStore().filterBy(function (rec) {
			//		return (Number(rec.get('RecordStatus_Code')) != 3);
			//	});
			//}
			PersonNewBorn_ids = childGrid.getStore().data.keys;

			childDeathGrid.getStore().clearFilter();
			if (childDeathGrid.getStore().getCount() > 0) {
				ChildDeathData = getStoreRecords(childDeathGrid.getStore(), {convertDateFields:true});
				childDeathGrid.getStore().filterBy(function (rec) {
					return (Number(rec.get('RecordStatus_Code')) != 3);
				});
			}
			ChildDeath_ids = childDeathGrid.getStore().data.keys;

			if (objectsAsJSON) {
				params.Answers = Ext.util.JSON.encode(Answers);
				//params.ChildData = Ext.util.JSON.encode(ChildData);
				params.ChildDeathData = Ext.util.JSON.encode(ChildDeathData);
			} else {
				params.Answers = Answers;
				//params.ChildData = ChildData;
				params.ChildDeathData = ChildDeathData;
			}

			return params;
		},
		beforeSaveCategory: function(category) {
			var base_form = category.getForm();
			var PregnancyResult_Code = base_form.findField('PregnancyResult_id').getFieldValue('PregnancyResult_Code');
			var BirthSpecStac_CountPregnancy = base_form.findField('BirthSpecStac_CountPregnancy').getValue();
			var BirthSpecStac_CountBirth = base_form.findField('BirthSpecStac_CountBirth').getValue();

			if (PregnancyResult_Code == 1 &&
				!Ext.isEmpty(BirthSpecStac_CountPregnancy) && !Ext.isEmpty(BirthSpecStac_CountBirth) &&
				BirthSpecStac_CountPregnancy < BirthSpecStac_CountBirth
			) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: 'Количество родов превышает количество беременностей.',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			sw.Promed.PersonPregnancy.ResultCategory.superclass.beforeSaveCategory.apply(this, arguments);
		},
		saveCategory: function(category, callback) {
			var base_form = category.getForm();
			var wizard = category.wizard;

			if (category.validateCategory(category, true) === false){
				return false;
			}

			if (category.beforeSaveCategory(category) === false) {
				return false;
			}

			var params = category.getCategoryFormData(category, true);

			var loadMask = wizard.getLoadMask({msg: "Подождите, идет сохранение..."});
			loadMask.show();

			Ext.Ajax.request({
				params: params,
				url: '/?c=PersonPregnancy&m=saveBirthSpecStac',
				success: function(response) {
					loadMask.hide();

					var response_obj = Ext.util.JSON.decode(response.responseText);
					var field772 = Ext.getCmp('swPersonPregnancyEditWindow').WizardPanel.getCategory('Result').getForm().findField('QuestionType_772');
					field772.setValue("");
					if (field772 && response_obj.RobsonValue){
						field772.setValue(response_obj.RobsonValue);
					}
					var pregnancy_form = Ext.getCmp("swPersonPregnancyEditWindow");
					if (pregnancy_form){
						Ext.getCmp("swPersonPregnancyEditWindow").refreshInfoPanel();
					}

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
						base_form.findField('BirthSpecStac_id').setValue(category.BirthSpecStac_id);

						category.afterSaveCategory(category);

						if (typeof callback == 'function') {
							callback();
						}
					}
				},
				failure: function(response) {
					loadMask.hide();
				}
			});
			return true;
		},
		afterSaveCategory: function(category) {
			category.needDeleteOnCancel = false;
			category.AddedPersonNewBorn_ids = [];
			category.AddedChildDeath_ids = [];
			category.ChildDeathGridPanel.reloadData();
		},
        setValues : function(base_form, values){
            var field, id;
            for(id in values){
                if(typeof values[id] != 'function' && (field = base_form.findField(id))){

                    if (field.getValue() === undefined){
                        radiofield = field.items.find(function(comp){return comp.inputValue==values[id];});
                        if (radiofield){
                            radiofield.setValue(true);
                        }
                    }

                    field.setValue(values[id]);
                    if(base_form.trackResetOnLoad){
                        field.originalValue = field.getValue();
                    }
                }
            }
        },
		setCategoryFormData: function(category, categoryData) {
			var base_form = category.getForm();
			var BirthSpecStac = categoryData;
			var data = category.inputData.getValues();

			if (Ext.isEmpty(BirthSpecStac.MedPersonal_oid)) {
				BirthSpecStac.MedPersonal_oid = data.MedPersonal_id;
			}
			if (Ext.isEmpty(BirthSpecStac.Lpu_oid)) {
				BirthSpecStac.Lpu_oid = data.Lpu_id;
			}

			BirthSpecStac.BirthSpecStac_CountChild2 = BirthSpecStac.BirthSpecStac_CountChild;

			//24102019 замена setValues
			//base_form.setValues(BirthSpecStac);
			category.setValues(base_form, BirthSpecStac);

			category.onPregnancyResultChange();

			if (base_form.findField('BirthSpecStac_id').getValue() > 0) {
				category.ChildGridPanel.loadData({
					globalFilters: {
						BirthSpecStac_id: base_form.findField('BirthSpecStac_id').getValue(),
						Person_id: data.Person_id
					},
					callback: function() {
						category.refreshPregnancyResultDisable();
						category.collectCategoryData(category);
					},
					noFocusOnLoad: true
				});

				category.ChildDeathGridPanel.loadData({
					globalFilters: {
						BirthSpecStac_id: base_form.findField('BirthSpecStac_id').getValue()
					},
					callback: function() {
						category.refreshPregnancyResultDisable();
						category.collectCategoryData(category);
					},
					noFocusOnLoad: true
				});
			}

			category.collectCategoryData(category);

			//устанавливаем видимость поля 771 и 772
			var field771 = base_form.findField('QuestionType_771');
			var field772 = base_form.findField('QuestionType_772');
			var field_546 = base_form.findField('QuestionType_546');
			var fieldCountBirth = base_form.findField('BirthSpecStac_CountBirth');
			var fieldBirthSpec_id = base_form.findField('BirthSpec_id');
			if (field771 && field772 && field_546) {
				var hidepanel_771 = field771.findParentByType('panel');
				var hidepanel_772 = field772.findParentByType('panel');
				if (hidepanel_771 && hidepanel_772) {
					if (field_546.checked) {
						hidepanel_771.show();
						hidepanel_772.show();
					}
					else {
						hidepanel_771.hide();
						hidepanel_772.hide();
					}
				}
			}			
			if (field_546) {
				field_546.addListener('check', function(combo) {
					if (field772 && fieldCountBirth && fieldBirthSpec_id) {
						var hidepanel = field772.findParentByType('panel');
						if (field_546.checked) {
							if (hidepanel) {
								hidepanel.show();
								fieldBirthSpec_id.allowBlank = false;
								fieldCountBirth.allowBlank = false;
								category.doLayout();
							}
						}
						else {
							if (hidepanel) {
								hidepanel.hide();
								fieldBirthSpec_id.allowBlank = true;
								fieldCountBirth.allowBlank = true;
								category.doLayout();
							}
						}
					}
				});
			}
		},
		beforeDeleteCategory: function(category) {
			var childGrid = category.ChildGridPanel.getGrid();
			var childDeathGrid = category.ChildDeathGridPanel.getGrid();

			var allowDeleteChildren = true;

			childGrid.getStore().each(function(record) {
				if (!Ext.isEmpty(record.get('BirthSvid_id')) || !Ext.isEmpty(record.get('PntDeathSvid_id'))) {
					allowDeleteChildren = false;
					return false;
				}
			});
			childDeathGrid.getStore().each(function(record) {
				if (!Ext.isEmpty(record.get('PntDeathSvid_id'))) {
					allowDeleteChildren = false;
					return false;
				}
			});

			if (!allowDeleteChildren) {
				sw.swMsg.alert(lang['soobschenie'], 'Для удаления исхода беременности у детей не должно быть случаев лечения, данных наблюдений и мед. свидетельств.');
				return false;
			}
			return true;
		},
		loadCategory: function(category, showPage) {
			var base_form = category.getForm();
			var data = category.inputData.getValues();
			var wizard = category.wizard;

			if (category.loadParams.BirthSpecStac_id != category.BirthSpecStac_id) {
				category.loaded = false;
				category.BirthSpecStac_id = category.loadParams.BirthSpecStac_id;
			}

			if (category.loaded) {
				showPage();return;
			}

			var loadMask = wizard.getLoadMask({msg: "Подождите, идет формирование анкеты..."});
			loadMask.show();

			var onDataLoad = function() {
				base_form.reset();
				category.onPregnancyResultChange();

				category.ChildGridPanel.removeAll();
				category.ChildDeathGridPanel.removeAll();

				base_form.items.each(function(field){field.validate()});

				var categoryData = category.getCategoryData(category);
				if (category.allowCollectData && categoryData && categoryData.loaded) {
					category.loaded = true;
					category.setCategoryFormData(category, categoryData);
					loadMask.hide();
					showPage();
				} else {
					var params = {
						PersonRegister_id: data.PersonRegister_id,
						Evn_id: data.Evn_id
					};
					if (category.loadParams.BirthSpecStac_id) {
						params.BirthSpecStac_id = category.loadParams.BirthSpecStac_id;
					}
					if (category.allowCollectData) {
						if (wizard.getCategory('Anketa')) {
							var anketaCategoryData = wizard.getCategory('Anketa').data.last();
							if (anketaCategoryData) {
								params.AnketaAnswers = Ext.util.JSON.encode(anketaCategoryData.Answers);
							}
						}
						if (wizard.getCategory('Screen')) {
							var screenCategoryData = wizard.getCategory('Screen').data.last();
							if (screenCategoryData) {
								var PregnancyScreenDates = {};
								wizard.getCategory('Screen').data.each(function(item){
									if (item.status.inlist([0,2])) {
										PregnancyScreenDates[item.PregnancyScreen_id] = item.PregnancyScreen_setDate;
									}
								});
								params.PregnancyScreenDates = Ext.util.JSON.encode(PregnancyScreenDates);
								params.LastScreenAnswers = Ext.util.JSON.encode(screenCategoryData.Answers);
							}
						}
					}
					Ext.Ajax.request({
						url: '/?c=PersonPregnancy&m=loadBirthSpecStac',
						params: params,
						success: function (response) {
							category.loaded = true;
							var response_obj = Ext.util.JSON.decode(response.responseText);
							var BirthSpecStac = response_obj[0];
							if (BirthSpecStac) {
								//устанавливаем признак наличия в КВС услуги Кесарево сечение
								var base_form = category.getForm();
								base_form.findField('IsOperationCaesarian').setValue(response_obj[0]['IsOperationCaesarian']);
								category.setCategoryFormData(category, BirthSpecStac);
							}

							loadMask.hide();
							showPage();
						},
						failure: function() {
							category.onPregnancyResultChange();
							loadMask.hide();
							showPage();
						}
					});
				}
			};

			Ext.getCmp(category.id+'_ResultBirthPanel').show();
			Ext.getCmp(category.id+'_ResultAbortPanel').show();
			Ext.getCmp(category.id+'_SurgeryVolumePanel').show();

			if (category.ResultScreenFactory.loaded && category.IntrFactorsFactory.loaded) {
				onDataLoad();
			} else {
				var doings = sw.Promed.Doings();

				doings.start('loadResultScreen');
				category.ResultScreenFactory.loadSettings(function() {
					doings.finish('loadResultScreen');
				});

				doings.start('loadIntrFactors');
				category.IntrFactorsFactory.loadSettings(function() {
					doings.finish('loadIntrFactors');
				});

				doings.doLater('fn', function() {
					category.initFields();
					category.showPages();
					category.doLayout();
					category.hidePages();

					onDataLoad();
				});
			}
		},
		initCategory: function(category) {
			sw.Promed.PersonPregnancy.ResultCategory.superclass.initCategory.apply(category, arguments);

			this.ResultScreenFactory.loaded = false;
			this.IntrFactorsFactory.loaded = false;

			this.ChildGridPanel.removeAll();
			this.ChildDeathGridPanel.removeAll();

			this.ChildGridPanel.initActions();
			this.ChildDeathGridPanel.initActions();
		},
		reset: function(resetValues) {
			sw.Promed.PersonPregnancy.ResultCategory.superclass.reset.apply(this, arguments);
			if (resetValues) {
				delete this.ignoreCheckBirthSpecStacDate;
				delete this.ignoreCheckChildrenCount;
				this.AddedPersonNewBorn_ids = [];
				this.AddedChildDeath_ids = [];
			}
		},
		initComponent: function() {
			var category = this;
			var id = category.getId();

			category.ResultScreenFactory = new sw.Promed.QuestionType.Factory({DispClass_id: 16, nameMap: {
				IsRWtest: 'BirthSpecStac_IsRWtest',
				IsRW: 'BirthSpecStac_IsRW',
				IsHIVtest: 'BirthSpecStac_IsHIVtest',
				IsHIV: 'BirthSpecStac_IsHIV',
				IsHBtest: 'BirthSpecStac_IsHBtest',
				IsHB: 'BirthSpecStac_IsHB',
				IsHCtest: 'BirthSpecStac_IsHCtest',
				IsHC: 'BirthSpecStac_IsHC'
			}});
			category.IntrFactorsFactory = new sw.Promed.QuestionType.Factory({DispClass_id: 18});

			category.getChildCount = function () {
				var childGrid = category.ChildGridPanel.getGrid();
				var childDeathGrid = category.ChildDeathGridPanel.getGrid();
				var childs = getStoreRecords(childGrid.getStore());
				var deaths = getStoreRecords(childDeathGrid.getStore());
				var result = 0;
				var maxCount = 0;
				for (var i = 0; i < childs.length; i++) {
					if (childs[i].CountChild > maxCount) {
						maxCount = childs[i].CountChild;
					}
				}
				for (var i = 0; i < deaths.length; i++) {
					if (deaths[i].ChildDeath_Count > maxCount) {
						maxCount = deaths[i].ChildDeath_Count;
					}
				}
				return maxCount;
			};

			category.refreshPregnancyResultDisable = function() {
				var base_form = category.getForm();
				var result_combo = base_form.findField('PregnancyResult_id');
				var childGrid = category.ChildGridPanel.getGrid();
				var childDeathGrid = category.ChildDeathGridPanel.getGrid();
				var disabled = false;

				if (category.readOnly) {
					disabled = true;
				} else if (result_combo.getFieldValue('PregnancyResult_Code') == 1) {
					disabled = (childGrid.getStore().getCount() > 0 || childDeathGrid.getStore().getCount() > 0);
				}

				result_combo.setDisabled(disabled);
			};

			category.AddedPersonNewBorn_ids = [];
			category.AddedChildDeath_ids = [];

			category.ChildGridPanel = new sw.Promed.ChildGridPanel({
				id: id+'_ChildGridPanel',
				style:'margin-bottom: 10px',
				onLoadData: function() {
					category.refreshPregnancyResultDisable();
				},
				beforeChildAdd: function(objectToReturn, addFn) {
					if (Ext.isEmpty(objectToReturn.BirthSpecStac_id)) {
						category.ignoreCheckChildrenCount = true;
						category.saveCategory(category, function(){
							category.needDeleteOnCancel = true;
							addFn();
						});
						return false;
					}
					return true;
				},
				afterChildAdd: function(data) {
					var base_form = category.getForm();
					if (data && data.PersonNewBorn_id &&
						category.AddedPersonNewBorn_ids.indexOf(data.PersonNewBorn_id) < 0
					) {
						category.AddedPersonNewBorn_ids.push(data.PersonNewBorn_id);
						base_form.findField('BirthSpecStac_CountChild').setValue(data.BirthSpecStac_CountChild + 1);
						base_form.findField('BirthSpecStac_CountChild2').setValue(data.BirthSpecStac_CountChild + 1);
					}
				},
				afterChildDelete: function(data) {
					var base_form = category.getForm();

					var count = data.BirthSpecStac_CountChild - 1;
					base_form.findField('BirthSpecStac_CountChild').setValue(count>0?count:0);
					base_form.findField('BirthSpecStac_CountChild2').setValue(count>0?count:0);

					if (data && data.DeletedPersonNewBorn_id) {
						var index = category.AddedPersonNewBorn_ids.indexOf(data.DeletedPersonNewBorn_id);
						if (index >= 0) {
							category.AddedPersonNewBorn_ids.splice(index, 1);
						}
					}
				},
				getObjectToReturn: function() {
					var base_form = category.getForm();
					var data = category.inputData.getValues();

					var period = base_form.findField('BirthSpecStac_OutcomPeriod').getValue();
					var ChildTermType_id = sw.Promed.PersonPregnancy.getChildTermTypeByOutcomPeriod(period);

					var IsHIVtest = base_form.findField('BirthSpecStac_IsHIVtest').getValue();
					var IsHIV = base_form.findField('BirthSpecStac_IsHIV').getValue();
					var BirthSpecStac_IsHIV = null;
					if (IsHIVtest || IsHIV) {
						BirthSpecStac_IsHIV = IsHIV?2:1;
					}

					return {
						BirthSpecStac_id: base_form.findField('BirthSpecStac_id').getValue(),
						BirthSpecStac_OutcomPeriod: base_form.findField('BirthSpecStac_OutcomPeriod').getValue(),
						BirthSpecStac_OutcomDate: base_form.findField('BirthSpecStac_OutcomDate').getValue(),
						BirthSpecStac_OutcomTime: base_form.findField('BirthSpecStac_OutcomTime').getValue(),
						BirthSpecStac_CountChild: category.getChildCount(),
						BirthSpecStac_IsHIV: BirthSpecStac_IsHIV,
						BirthPlace_id: base_form.findField('BirthPlace_id').getValue(),
						ChildTermType_id: ChildTermType_id,
						Server_id: data.Server_id,
						LpuSection_id: data.LpuSection_id,
						MedStaffFact_id: data.MedStaffFact_id,
						Person_id: data.Person_id,
						Person_SurName: data.Person_SurName,
						Person_FirName: data.Person_FirName,
						Person_SecName: data.Person_SecName,
						EvnSection_id: base_form.findField('EvnSection_id').getValue(),
						callback: category.ChildGridPanel.objectToReturnCallback
					};
				}
			});

			if (typeof category.beforeChildAdd == 'function') {
				category.ChildGridPanel.beforeChildAdd = category.beforeChildAdd;
			}
			if (typeof category.afterChildAdd == 'function') {
				category.ChildGridPanel.afterChildAdd = category.afterChildAdd;
			}
			if (typeof category.beforeChildDelete == 'function') {
				category.ChildGridPanel.beforeChildDelete = category.beforeChildDelete;
			}
			if (typeof category.afterChildDelete == 'function') {
				category.ChildGridPanel.afterChildAdd = category.afterChildDelete;
			}

			category.ChildDeathGridPanel = new sw.Promed.ChildDeathGridPanel({
				id: id+'_ChildDeathGridPanel',
				style :'margin-bottom: 10px',
				reloadData: function() {
					if (category.BirthSpecStac_id > 0) {
						category.ChildDeathGridPanel.loadData({
							globalFilters: {
								BirthSpecStac_id: category.BirthSpecStac_id
							},
							callback: function() {
								category.refreshPregnancyResultDisable();
								category.collectCategoryData(category);
							},
							noFocusOnLoad: true
						});
					}
				},
				onLoadData: function() {
					category.refreshPregnancyResultDisable();
				},
				afterChildDeathAdd: function(data) {
					var base_form = category.getForm();
					if (data && data.ChildDeath_id &&
						category.AddedChildDeath_ids.indexOf(data.ChildDeath_id) < 0
					) {
						category.AddedChildDeath_ids.push(data.ChildDeath_id);
						base_form.findField('BirthSpecStac_CountChild').setValue(data.BirthSpecStac_CountChild + 1);
						base_form.findField('BirthSpecStac_CountChild2').setValue(data.BirthSpecStac_CountChild + 1);
					}
					category.refreshPregnancyResultDisable();
				},
				afterChildDeathDelete: function(data) {
					var base_form = category.getForm();

					var count = data.BirthSpecStac_CountChild - 1;
					base_form.findField('BirthSpecStac_CountChild').setValue(count>0?count:0);
					base_form.findField('BirthSpecStac_CountChild2').setValue(count>0?count:0);

					if (data && data.DeletedChildDeath_id) {
						var index = category.AddedChildDeath_ids.indexOf(data.DeletedChildDeath_id);
						if (index >= 0) {
							category.AddedChildDeath_ids.splice(index, 1);
						}
					}
					category.refreshPregnancyResultDisable();
				},
				getObjectToReturn: function() {
					var base_form = category.getForm();
					var data = category.inputData.getValues();

					var period = base_form.findField('BirthSpecStac_OutcomPeriod').getValue();
					var ChildTermType_id = sw.Promed.PersonPregnancy.getChildTermTypeByOutcomPeriod(period);

					return {
						BirthSpecStac_OutcomDate: base_form.findField('BirthSpecStac_OutcomDate').getValue(),
						BirthSpecStac_OutcomTime: base_form.findField('BirthSpecStac_OutcomTime').getValue(),
						BirthSpecStac_CountChild: category.getChildCount(),
						Server_id: data.Server_id,
						LpuSection_id: data.LpuSection_id,
						MedStaffFact_id: data.MedStaffFact_id,
						Person_id: data.Person_id,
						Person_SurName: data.Person_SurName,
						Person_FirName: data.Person_FirName,
						Person_SecName: data.Person_SecName,
						ChildTermType_id: ChildTermType_id
					};
				}
			});

			if (typeof category.beforeChildDeathAdd == 'function') {
				category.ChildDeathGridPanel.beforeChildDeathAdd = category.beforeChildDeathAdd;
			}
			if (typeof category.afterChildDeathAdd == 'function') {
				category.ChildDeathGridPanel.afterChildDeathAdd = category.afterChildDeathAdd;
			}
			if (typeof category.beforeChildDeathDelete == 'function') {
				category.ChildDeathGridPanel.beforeChildDeathDelete = category.beforeChildDeathDelete;
			}
			if (typeof category.afterChildDeathDelete == 'function') {
				category.ChildDeathGridPanel.afterChildDeathDelete = category.afterChildDeathDelete;
			}

			var ResultPanel = new Ext.Panel({
				id: id+'_Result',
				style: 'padding: 5px;',
				border: false,
				labelAlign: 'right',
				labelWidth: 170,
				layout: 'form',
				items: [{
					xtype: 'hidden',
					name: 'BirthSpecStac_id'
				}, {
					xtype: 'hidden',
					name: 'PersonRegister_id'
				}, {
					xtype: 'hidden',
					name: 'PregnancySpec_id'
				}, {
					xtype: 'hidden',
					name: 'EvnSection_id'
				}, {
					xtype: 'hidden',
					name: 'Evn_id'
				}, {
					xtype: 'hidden',
					name: 'MedPersonal_oid'
				}, {
					xtype: 'hidden',
					name: 'PregnancySrokInDay'
                }, {
                    xtype: 'hidden',
                    name: 'AnketaParitet'
                }, {
                    xtype: 'hidden',
                    name: 'ScrinnPredleg'
                }, {
                    xtype: 'hidden',
					name: 'AnketaCaesarian'
                }, {
                    xtype: 'hidden',
                    name: 'ScrinnPolog'
				}, {
					xtype: 'hidden',
					name: 'IsOperationCaesarian'
				}, {//Общая часть
					layout: 'form',
					border: false,
					items: [{
						allowBlank: false,
						allowNegative: false,
						allowDecimals: false,
						xtype: 'numberfield',
						name: 'BirthSpecStac_CountPregnancy',
						fieldLabel: 'Которая беременность',
						width: 100,
						minValue: 0,
						maxValue: 99,
						maskRe: /[\d\.]/						
					}, {
						layout: 'column',
						border: false,
						items: [{
							layout: 'form',
							border: false,
							items: [{
								allowBlank: false,
								xtype: 'swdatefield',
								name: 'BirthSpecStac_OutcomDate',
								fieldLabel: 'Дата исхода беременности',
								width: 100
							}]
						}, {
							layout: 'form',
							border: false,
							labelWidth: 59,
							items: [{
								allowBlank: false,
								xtype: 'swtimefield',
								format: 'H:i',
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								name: 'BirthSpecStac_OutcomTime',
								fieldLabel: 'Время',
								listeners:{
									'keydown':function (inp, e) {
										if (e.getKey() == Ext.EventObject.F4) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								onTriggerClick:function () {
									var base_form = category.getForm();
									var date_field = base_form.findField('BirthSpecStac_OutcomDate');
									var time_field = base_form.findField('BirthSpecStac_OutcomTime');

									if (time_field.disabled) {
										return false;
									}

									setCurrentDateTime({
										callback:function () {
											date_field.fireEvent('change', date_field, date_field.getValue());
										},
										dateField: date_field,
										timeField: time_field,
										setDate: true,
										setTime: true,
										loadMask: true,
										windowId: category.wizard.getId()
									});
								}
							}]
						}]
					}, {
						allowBlank: false,
						xtype: 'swcommonsprcombo',
						comboSubject: 'PregnancyResult',
						hiddenName: 'PregnancyResult_id',
						fieldLabel: 'Исход беременности',
						listeners: {
							'change': function(combo, record, index) {
								category.onPregnancyResultChange({resize: true, recalc: true});
							}
						},
						width: 235
					}, {
						layout: 'form',
						border: false,
						labelWidth: 175,
						hidden: getRegionNick() != 'khak',
						items: [{
							allowBlank: getRegionNick() != 'khak',
							xtype: 'swcommonsprcombo',
							comboSubject: 'PregnancyType',
							hiddenName: 'PregnancyType_id',
							fieldLabel: 'Вид исхода',
							width: 235
						}]
					}, {
						layout: 'form',
						border: false,
						labelWidth: 30,
						items: [{
							allowBlank: false,
							xtype: 'swlpucombo',
							hiddenName: 'Lpu_oid',
							fieldLabel: 'МО',
							width: 375
						}]
					}, {
						allowBlank: false,
						allowNegative: false,
						allowDecimals: false,
						xtype: 'numberfield',
						name: 'BirthSpecStac_OutcomPeriod',
						fieldLabel: 'Срок, недель',
						width: 100,
						minValue: 0,
						maskRe: /[\d\.]/,
						maxValue: 50
					}, {
						allowBlank: false,
						allowNegative: false,
						allowDecimals: false,
						xtype: 'numberfield',
						name: 'BirthSpecStac_CountChild',
						fieldLabel: 'Количество плодов',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = category.getForm();
								base_form.findField('BirthSpecStac_CountChild2').setValue(newValue);
							}
						},
						width: 100,
						minValue: 0,
						maskRe: /[\d\.]/,
						maxValue: 10
					}, {

						id: id+'_Resul111tBirthPanel',
						layout: 'form',
						border: false,
						items: [{

						xtype: 'swcommonsprcombo',
						comboSubject: 'LaborActivity',
						hiddenName: 'LaborActivity_id',
						fieldLabel: 'Родовая деятельность',
						width: 235
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'FetalHeartbeat',
						hiddenName: 'FetalHeartbeat_id',
						fieldLabel: 'Сердцебиение плода',
						width: 235
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'FetalHead',
						hiddenName: 'FetalHead_id',
						fieldLabel: 'Головка плода',
						width: 235
					}]},
					category.ResultScreenFactory.createContainer({QuestionType_Code: 408}),
					category.ResultScreenFactory.createContainer({QuestionType_Code: 409}),
					category.ResultScreenFactory.createContainer({QuestionType_Code: 410}),
					category.ResultScreenFactory.createContainer({QuestionType_Code: 411}),
					{
						allowBlank: false,
						allowNegative: false,
						allowDecimals: true,
						xtype: 'numberfield',
						name: 'BirthSpecStac_BloodLoss',
						fieldLabel: 'Кровопотери (мл)',
						width: 100,
						minValue: 0,
						maskRe: /[\d\.]/,
						maxValue: 6000,
						listeners: {
							'keypress': function (field, e) {
								if (e.keyCode == 8 || e.keyCode == 9) {
									return true;
								}
								if (e.keyCode == 44) {
									e.browserEvent.returnValue = false;
								}
								if (e.target.selectionDirection == 'backward') {
									e.target.value = "";
									if (e.keyCode == 46) {
										e.browserEvent.returnValue = false;
									}
									return true;
								}
								var isPoint = false;
								var vval = String.fromCharCode(e.charCode)
								isPoint = vval.includes(".");
								if (isPoint) {
									if (e.target.value.includes(".")) {
										e.browserEvent.returnValue = false;
									}
									if (isPoint && e.target.value.length > 1 && e.target.value.length < 4) {
										return true;
									}
									e.browserEvent.returnValue = false;
								} else if (e.target.value.length == 3) {
									if (!e.target.value.includes(".")) {
										e.target.value = e.target.value + '.';
										return true;
									}
								} else if (e.target.value.length == 5) {
									if (e.target.value.indexOf('.') == 2) {
										e.browserEvent.returnValue = false;
									}
								}
								return true;
							},
						}
					}]
				}, {//Внематочная беременность
					id: id+'_SurgeryVolumePanel',
					layout: 'form',
					border: false,
					items: [{
						xtype: 'textarea',
						name: 'BirthSpecStac_SurgeryVolume',
						fieldLabel: 'Объем оперативного вмешательства при внематочной беременности',
						autoCreate: {tag: "textarea", autocomplete: "off", maxlength: 500},
						anchor: '100%'
					}]
				}, {//Аборт
					id: id+'_ResultAbortPanel',
					layout: 'form',
					border: false,
					items: [{
						xtype: 'swcommonsprcombo',
						comboSubject: 'AbortLpuPlaceType',
						hiddenName: 'AbortLpuPlaceType_id',
						fieldLabel: 'Место аборта',
						width: 235
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'AbortLawType',
						hiddenName: 'AbortLawType_id',
						fieldLabel: 'Вид аборта',
						width: 235
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'AbortMethod',
						hiddenName: 'AbortMethod_id',
						fieldLabel: 'Метод аборта',
						editable: true,
                        lastQuery: '',
						width: 235
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'AbortIndicat',
						hiddenName: 'AbortIndicat_id',
						fieldLabel: 'Показания',
						width: 235
					}, {
						layout: 'form',
						border: false,
						items: [{
							xtype: 'textarea',
							name: 'BirthSpecStac_InjectVMS',
							fieldLabel: 'Введено ВМС',
							autoCreate: {tag: "textarea", autocomplete: "off", maxlength: 500},
							anchor: '100%'
						}]
					}]
				}, {//Роды
					id: id+'_ResultBirthPanel',
					layout: 'form',
					border: false,
					items: [{
						allowNegative: false,
						allowDecimals: false,
						xtype: 'swcommonsprcombo',
						comboSubject: 'BirthPlace',
						hiddenName: 'BirthPlace_id',
						fieldLabel: 'Место родов',
						width: 235
					}, {
						allowNegative: false,
						allowDecimals: false,
						xtype: 'numberfield',
						name: 'BirthSpecStac_CountChild2',
						fieldLabel: 'Количество плодов',
						disabled: true,
						width: 100,
						minValue: 0,
						maskRe: /[\d\.]/,
						maxValue: 10
					}, {
						allowNegative: false,
						allowDecimals: false,
						xtype: 'numberfield',
						name: 'BirthSpecStac_CountChildAlive',
						fieldLabel: 'В т.ч. живорожденных',
						width: 100,
						minValue: 0,
						maskRe: /[\d\.]/,
						maxValue: 10
					}, {
						allowNegative: false,
						allowDecimals: false,
						xtype: 'numberfield',
						name: 'BirthSpecStac_CountBirth',
						fieldLabel: 'Роды которые',
						width: 100,
						minValue: 0,
						maxValue: 99,
						maskRe: /[\d\.]/						
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'BirthSpec',
						name: 'BirthSpec_id',
						fieldLabel: 'Особенности родов',
						width: 235
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'BirthCharactType',
						hiddenName: 'BirthCharactType_id',
						fieldLabel: 'Характер родов',
						width: 235
					},
					category.IntrFactorsFactory.createContainer({root: true}),
					category.ChildGridPanel,
					category.ChildDeathGridPanel,
					{
						xtype: 'swcheckbox',
						name: 'BirthSpecStac_IsContrac',
						hideLabel: true,
						boxLabel: 'Послеродовая контрацепция'
					}, {
						layout: 'form',
						border: false,
						labelAlign: 'top',
						items: [{
							xtype: 'textarea',
							name: 'BirthSpecStac_ContracDesc',
							fieldLabel: 'Сведения о послеродовой контрацепции',
							autoCreate: {tag: "textarea", autocomplete: "off", maxlength: 500},
							anchor: '100%'
						}]
					}
					]
				}]
			});

			category.pages = [ResultPanel];

			sw.Promed.PersonPregnancy.ResultCategory.superclass.initComponent.apply(category, arguments);
		}
	});

	o.DeathMotherCategory = Ext.extend(sw.Promed.WizardCategory, {
		name: 'DeathMother',
		objectName: 'DeathMother',
		inputData: inputData,
		getCategoryFormData: function(category, objectsAsJSON) {
			return category.getForm().getValues();
		},
		saveCategory: function(category) {
			var base_form = category.getForm();
			var wizard = category.wizard;

			if (category.validateCategory(category, true) === false){
				return false;
			}

			if (category.beforeSaveCategory(category) === false) {
				return false;
			}

			var params = category.getCategoryFormData(category);

			var loadMask = wizard.getLoadMask({msg: "Подождите, идет сохранение..."});
			loadMask.show();

			Ext.Ajax.request({
				url: '/?c=PersonPregnancy&m=saveDeathMother',
				params: params,
				success: function(response) {
					loadMask.hide();

					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success) {
						category.DeathMother_id = response_obj.DeathMother_id;
						base_form.findField('DeathMother_id').setValue(category.DeathMother_id);

						wizard.afterSaveCategory(category);
					}
				},
				failure: function(response) {
					loadMask.hide();
				}
			});
			return true;
		},
		loadCategory: function(category, showPage) {
			var base_form = category.getForm();
			var wizard = category.wizard;
			var data = category.inputData.getValues();

			if (category.loadParams.DeathMother_id != category.DeathMother_id) {
				category.loaded = false;
				category.DeathMother_id = category.loadParams.DeathMother_id;
			}

			if (category.loaded) {
				showPage();return;
			}

			var loadMask = wizard.getLoadMask({msg: "Подождите, идет формирование анкеты..."});
			loadMask.show();

			var onDataLoad = function() {
				base_form.reset();

				base_form.items.each(function(field){field.validate()});

				if (Ext.isEmpty(category.DeathMother_id)) {
					category.loaded = true;

					base_form.findField('Lpu_oid').setValue(data.Lpu_id);
					base_form.findField('MedPersonal_oid').setValue(data.MedPersonal_id);
					base_form.findField('PersonRegister_id').setValue(data.PersonRegister_id);
					base_form.findField('Evn_id').setValue(data.Evn_id);

					loadMask.hide();
					setTimeout(showPage, 5);
				} else {
					Ext.Ajax.request({
						url: '/?c=PersonPregnancy&m=loadDeathMother',
						params: {DeathMother_id: category.DeathMother_id},
						success: function(response) {
							category.loaded = true;

							var response_obj = Ext.util.JSON.decode(response.responseText);
							var DeathMother = response_obj[0];

							if (Ext.isEmpty(DeathMother.MedPersonal_oid)) {
								DeathMother.MedPersonal_oid = data.MedPersonal_id;
							}
							if (Ext.isEmpty(DeathMother.Lpu_oid)) {
								DeathMother.Lpu_oid = data.Lpu_id;
							}

							base_form.setValues(DeathMother);

							var diag_c_combo = base_form.findField('Diag_cid');
							var diag_cid = diag_c_combo.getValue();
							if (diag_cid) {
								diag_c_combo.getStore().load({
									callback: function() {
										var record = diag_c_combo.getStore().getById(diag_cid);
										if (record) {
											diag_c_combo.setValue(diag_cid);
										} else {
											diag_c_combo.setValue(null);
										}
									},
									params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_cid}
								});
							}

							var diag_a_combo = base_form.findField('Diag_aid');
							var diag_aid = diag_a_combo.getValue();
							if (diag_aid) {
								diag_a_combo.getStore().load({
									callback: function() {
										var record = diag_a_combo.getStore().getById(diag_aid);
										if (record) {
											diag_a_combo.setValue(diag_aid);
										} else {
											diag_a_combo.setValue(null);
										}
									},
									params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_aid}
								});
							}

							loadMask.hide();
							showPage();
						},
						failure: function(response) {
							loadMask.hide();
						}
					});
				}
			};

			onDataLoad();
		},
		initComponent: function() {
			var category = this;
			var id = category.getId();

			var DeathMotherPanel = new Ext.Panel({
				id: id+'_DeathMother',
				style: 'padding: 5px;',
				border: false,
				labelAlign: 'right',
				labelWidth: 195,
				layout: 'form',
				items: [{
					xtype: 'hidden',
					name: 'DeathMother_id'
				}, {
					xtype: 'hidden',
					name: 'PersonRegister_id'
				}, {
					xtype: 'hidden',
					name: 'Evn_id'
				}, {
					xtype: 'hidden',
					name: 'Lpu_oid'
				}, {
					xtype: 'hidden',
					name: 'MedPersonal_oid'
				}, {
					allowBlank: false,
					xtype: 'swdatefield',
					name: 'DeathMother_DeathDate',
					fieldLabel: 'Дата смерти',
					width: 100
				}, {
					allowBlank: false,
					xtype: 'swcommonsprcombo',
					comboSubject: 'DeathMotherType',
					hiddenName: 'DeathMotherType_id',
					fieldLabel: 'Смерть',
					listWidth: 470,
					width: 420
				}, {
					xtype: 'swdiagcombo',
					fieldLabel: 'Клинический диагноз',
					hiddenName: 'Diag_cid',
					width: 420
				}, {
					xtype: 'swdiagcombo',
					fieldLabel: 'Патологоанатомический диагноз',
					hiddenName: 'Diag_aid',
					width: 420
				}, {
					xtype: 'textfield',
					name: 'DeathMother_DeathPlace',
					fieldLabel: 'Место смерти',
					width: 420
				}]
			});

			category.pages = [DeathMotherPanel];

			sw.Promed.PersonPregnancy.DeathMotherCategory.superclass.initComponent.apply(category, arguments);
		}
	});

	o.WizardFrame = Ext.extend(sw.Promed.WizardFrame, {
		inputData: inputData,
		createCategoryController: function(category) {
			var wizard = this;
			if (typeof category == 'string') {
				category = wizard.getCategory(category);
			}
			if (typeof category == 'object' && typeof category.createCategory == 'function') {
				category.createCategory(category);
			}
		},
		deleteCategoryController: function(category, id) {
			var wizard = this;
			if (typeof category == 'string') {
				category = wizard.getCategory(category);
			}
			if (typeof category == 'object' && typeof category.deleteCategory == 'function') {
				category.deleteCategory(category, id);
			}
		},
		deleteCategory: function(category, id) {
			if (Ext.isEmpty(id) || Ext.isEmpty(category.objectName) || Ext.isEmpty(category.idField)) {
				return false;
			}

			var deleteCategory = function() {
				if (category.beforeDeleteCategory(category, id) === false) {
					return false;
				}

				var url = '/?c=PersonPregnancy&m=delete'+category.objectName;

				var params = {};
				params[category.idField] = id;

				var loadMask = category.wizard.getLoadMask({msg: "Подождите, идет удаление..."});
				loadMask.show();

				Ext.Ajax.request({
					params: params,
					url: url,
					success: function(response) {
						loadMask.hide();

						if (category.wizard.getCurrentCategory() == category && category[category.idField] == id) {
							category.wizard.resetCurrentCategory(true);
						} else if (category[category.idField] == id) {
							category.reset(true);
						}

						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.success) {
							category.afterDeleteCategory(category, id, response_obj);
						}
					},
					failure: function(response) {
						loadMask.hide();
					},
				});
				delete category.wantDelete;
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
					msg:lang['vyi_hotite_udalit_zapis'],
					title:lang['podtverjdenie']
				});
			}
		},
		getDataForSave: function(objectsAsJSON) {
			var wizard = this;
			var data = {};

			wizard.categories.each(function(category){
				if (Ext.isEmpty(category.objectName)) return;

				var objectName = category.objectName;
				var arr = [];

				category.data.each(function(data){
					if (!data.status.inlist([0,2,3])) return;
					var saveData = Ext.apply({}, data);

					for(key in saveData) {
						switch(true) {
							case (objectsAsJSON && typeof saveData[key] == 'object' && saveData[key] != null):
								saveData[key] = Ext.util.JSON.encode(saveData[key]);
								break;
							case (/^QuestionType_\d+/.test(key)):
								delete saveData[key];
								break;
						}
					}
					arr.push(saveData);
				});

				if (arr.length > 0) {
					if (category.isList) {
						data[objectName+'List'] = (objectsAsJSON?Ext.util.JSON.encode(arr):arr);
					} else {
						data[objectName] = (objectsAsJSON?Ext.util.JSON.encode(arr[0]):arr[0]);
					}
				}
			});
			return data;
		},
		getnotice: function(arraynotice){
			var wnd = this;
			if (arraynotice.length > 0){

				var v_msg = '<div style="text-align:justify;">' + arraynotice[arraynotice.length-1].ObstetricPathologyType_text.replace(/(\s)+(1|2|3|4|5|6|7|8|9|10|11|12)+(\.)/g, (match)=>`<br>${match}`.replace(/ /g,'')) + ' </div>';

				//Преэклампсия
				if (v_msg.indexOf('преэклампсии') !== -1) {
					v_msg = v_msg.replace('Дополнительное обследование во время беременности', '<br>Дополнительное обследование во время беременности');
					v_msg = v_msg.replace('(в соответствии', '</div><div style="text-align:center;">(в соответствии');
					v_msg = v_msg.replace('План дополнительных методов исследования', '<div style="text-align:center;font-weight:bold;">План дополнительных методов исследования');
					v_msg = v_msg.replace('№15-4/10/2-3483)', '№15-4/10/2-3483)</div><br>');
					v_msg = v_msg.replace('Показания для госпитализации', '<br>Показания для госпитализации');
					v_msg = v_msg.replace('Госпитализация для родоразрешения в сроке 38-39 недель', '<br>Госпитализация для родоразрешения в сроке 38-39 недель');
					v_msg = v_msg.replace('75- 162 мг в день', '75-162 мг в день');
					v_msg = v_msg.replace('в соответствии с Приказом № 572н', 'в соответствии с Приказом МЗ РФ от 01.11.12 г. № 572н');
				}

				//Преждевременные роды
				if (v_msg.indexOf('преждевременными родами') !== -1) {
					v_msg = v_msg.replace('Дополнения к индивидуальному', '<div style="text-align:center;font-weight:bold;">Дополнения к индивидуальному');
					v_msg = v_msg.replace('(в соответствии', '</div><div style="text-align:center;">(в соответствии');
					v_msg = v_msg.replace('9480)', '9480)</div>');
					v_msg = v_msg.replace('?-гемолитического стрептококка', 'бета-гемолитического стрептококка');
					v_msg = v_msg.replace('№572 н', '№572н');
				}

				//Самопроизвольный выкидыш
				if (v_msg.indexOf('потерь беременности') !== -1) {
					v_msg = v_msg.replace('Дополнения к индивидуальному плану ведения беременной из группы риска ИЦН', '<div style="text-align:center;font-weight:bold;">Дополнения к индивидуальному плану ведения беременной из группы риска ИЦН</div><div style="text-align:center;">');
					v_msg = v_msg.replace('3482)', '3482)</div>');
					v_msg = v_msg.replace('№572 н,', '№572н, ');
					//v_msg = v_msg.replace('Трансвагинальное', 'трансвагинальное');
					//v_msg = v_msg.replace('7 – 14', '7-14');
					//v_msg = v_msg.replace('15 - 16', '15-16');
				}

				//Гипоксия и ЗВРП
				if (v_msg.indexOf('ЗВУР') !== -1) {
					v_msg = v_msg.replace('(в соответствии', '</div><div style="text-align:center;">(в соответствии');
					v_msg = v_msg.replace('Дополнение к плану ведения', '<div style="text-align:center;font-weight:bold;">Дополнение к плану ведения');
					v_msg = v_msg.replace('01.11.12 г. №572н)', '01.11.12 г. №572н)</div>');
					//v_msg = v_msg.replace('Показания для госпитализации', '<br>Показания для госпитализации');
					v_msg = v_msg.replace(/(\s)+(-)/g, (match)=>`;<br>${match}`);
					v_msg = v_msg.replace(/(\s)+( v )/g, (match)=>`;<br>${match}`);
					v_msg = v_msg.replace('<br> - исследование состава микрофлоры', ' - исследование состава микрофлоры');
					//v_msg = v_msg.replace(/ v /g, '   - ');

					//v_msg = v_msg.replace('лечение осложнений беременности', 'лечение осложнений беременности.');
					v_msg = v_msg.replace('обследование:;', 'обследование:');
					v_msg = v_msg.replace(/;;/g, ';');
					v_msg = v_msg.replace('состояния;', 'состояния');
					v_msg = v_msg.replace('32-34 недели:;', '32-34 недели:');
				}

				//Риск ВТЭО
				if (v_msg.indexOf('ВТЭО') !== -1) {
					v_msg = v_msg.replace('печения', 'печени');
					v_msg = v_msg.replace(/(\s)+(-)/g, (match)=>`;<br>${match}`);
					v_msg = v_msg.replace('тромбопрофилактика', 'тромбопрофилактика.');
					v_msg = v_msg.replace(/NB!/g, '<br>NB!');
					//v_msg = v_msg.replace('(по показаниям)', '(по показаниям).');
					v_msg = v_msg.replace('легочной артерии', 'легочной артерии.<br>');
					//v_msg = v_msg.replace('(более 200/120 мм рт.ст)', '(более 200/120 мм рт.ст).');
					v_msg = v_msg.replace('послеродовом периоде:;', 'послеродовом периоде:');
					v_msg = v_msg.replace('во время беременности:;', 'во время беременности:');
					v_msg = v_msg.replace('Оценка риска ВТЭО врачом анестезиологом', '<br>Оценка риска ВТЭО врачом анестезиологом');
					v_msg = v_msg.replace('К венозным тромбоэмболическим', '<br>К венозным тромбоэмболическим');
				}

				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						setTimeout(wnd.getnotice(arraynotice), 500);
					},
					msg: v_msg,
					title: 'Клинические рекомендации',
					width: 800
				});
				arraynotice.splice(arraynotice.length-1, 1);
			}
		},

		initComponent: function() {
			if (!this.categories || this.categories.length == 0) {
				this.categories = [
					new sw.Promed.PersonPregnancy.AnketaCategory,
					new sw.Promed.PersonPregnancy.ScreenCategory,
					new sw.Promed.PersonPregnancy.EvnListCategory({toolbar: false}),
					new sw.Promed.PersonPregnancy.ConsultationListCategory({toolbar: false}),
					new sw.Promed.PersonPregnancy.ResearchListCategory({toolbar: false}),
					new sw.Promed.PersonPregnancy.CertificateCategory,
					new sw.Promed.PersonPregnancy.ResultCategory,
					new sw.Promed.PersonPregnancy.DeathMotherCategory
				];
			}

			sw.Promed.PersonPregnancy.WizardFrame.superclass.initComponent.apply(this, arguments);
		},
	});

	return o;
}());