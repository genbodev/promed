sw.Promed.QuestionType = (function(){
	var c = {};

	c.setVision = function(vision, settings) {
		if (!Ext.isArray(settings.vision)) {
			settings.vision = [];
		}
		var index = -1;
		settings.vision.forEach(function(item, i){
			if (item.id == vision.id && item.region_id == vision.region_id) {
				index = i; return false;
			}
		});
		if (vision.settings) {
			if (index >= 0) {
				if (settings.vision[index].RecordStatus_Code != 0) {
					vision.RecordStatus_Code = 2;
				}
				settings.vision[index] = vision;//Обновление
			} else {
				vision.RecordStatus_Code = 0;
				settings.vision.push(vision);	//Добавление
			}
		} else {
			//Удаление
			if (vision.id > 0) {
				vision.RecordStatus_Code = 3;
			} else {
				settings.vision.splice(index, 1);
			}
		}
	};

	c.deleteVision = function(vision_id, settings) {
		if (Ext.isArray(settings.vision)) {
			settings.vision.forEach(function(item, index){
				if (item.id == vision_id) {
					if (item.id > 0) {
						item.RecordStatus_Code = 3;
					} else {
						settings.vision.splice(index, 1);
					}
					return false;
				}
			});
		}
	};

	c.setChildVision = function(vision, settings) {
		if (!Ext.isArray(settings.childrenVision)) {
			settings.childrenVision = [];
		}
		var index = -1;
		settings.childrenVision.forEach(function(item, i){
			if (item.id == vision.id) {index = i; return false;}
		});
		if (vision.settings) {
			if (index >= 0) {
				if (settings.childrenVision[index].RecordStatus_Code != 0) {
					vision.RecordStatus_Code = 2;
				}
				settings.childrenVision[index] = vision;//Обновление
			} else {
				vision.RecordStatus_Code = 0;
				settings.childrenVision.push(vision);	//Добавление
			}
		} else {
			//Удаление
			if (vision.id > 0) {
				vision.RecordStatus_Code = 3;
			} else {
				settings.childrenVision.splice(index, 1);
			}
		}
	};

	c.deleteChildVision = function(vision_id, settings) {
		if (Ext.isArray(settings.childrenVision)) {
			settings.childrenVision.forEach(function(item, index){
				if (item.id == vision_id) {
					if (item.id > 0) {
						item.RecordStatus_Code = 3;
					} else {
						settings.childrenVision.splice(index, 1);
					}
					return false;
				}
			});
		}
	};

	c.loadSettings = function(params, callback) {
		callback = callback||Ext.emptyFn;

		Ext.Ajax.request({
			url: '/?c=QuestionType&m=loadQuestionTypeSettings',
			params: {
				DispClass_id: params.DispClass_id,
				QuestionType_Code: params.QuestionType_Code
			},
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success && response_obj.settings) {
					callback(response_obj.settings);
				}
			}
		});
	};

	c.getVision = function(settings, region_id) {
		region_id = !Ext.isEmpty(region_id) ? region_id : null;
		var visionList = settings.vision || [];
		var vision = null;

		for (var i=0; i<visionList.length; i++) {
			var item = visionList[i];
			if (item.settings.region_id == region_id && item.RecordStatus_Code != 3) {
				vision = item;break;
			}
		}

		return vision;
	};

	c.getVisionSettings = function(settings, region_id) {
		var vision = sw.Promed.QuestionType.getVision(settings, region_id);
		return vision ? vision.settings : null;
	};

	c.Checkbox = Ext.extend(sw.Promed.swCheckbox, {
		QuestionType_Code: null,
		settings: null,
		boxLabel: 'CheckBoxName',
		hideLabel: true,
		//id: 
		setName: function (text) {
			this.id = text;
		},		
		setBoxLabel: function(text) {
			this.boxLabel = text;

			if (this.rendered) {
				if (!this.labelEl) {
					this.labelEl = this.innerWrap.createChild({
						tag: 'label',
						htmlFor: this.el.id,
						cls: 'x-form-cb-label',
						html: this.boxLabel
					});
				} else {
					this.labelEl.dom.innerHTML = this.boxLabel;
				}
			}
		},

		initComponent: function() {
			sw.Promed.QuestionType.Checkbox.superclass.initComponent.apply(this, arguments);

			if (this.settings) {
				this.initSettings(this.settings);
			}
		},

		initSettings: function(settings) {
			var element = this;

			element.QuestionType_Code = settings.QuestionType_Code;
			element.settings = settings;
			element.name = 'QuestionType_'+settings.QuestionType_id;

			
			//element.setName('QQ'+settings.QuestionType_id);


			element.name = settings.container.getNameFromMap(settings);

			var defaultSettings = {
				fieldLabel: settings.QuestionType_Name,
				hidden: false
			};

			var visionSettings = settings.container.getVisionSettings(settings) || {};
			var region_id = getRegionNumber();
			if (visionSettings['cls' + region_id] != null) {
				element.itemCls = visionSettings['cls' + region_id];
			}

			//для Анкеты gaf
			if (Ext.getCmp(element.id).settings.DispClass_id == 14){
				//gaf 09022018 в режиме fullanketa #106655
				this.addListener('check', function (checkbox, checked) {
					//checkbox.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0] - кнопка отсутствует для группы, в которой лежит данный элемент ввода
					if (typeof checkbox.ownerCt.ownerCt.ownerCt.items.items[1] != 'undefined' &&
							typeof checkbox.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1] != 'undefined' &&
							typeof checkbox.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0] != 'undefined') {
						if (checked) {
							setDisabledPregnancy(checkbox.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], false);
						} else {
							//проверка есть ли выбранные элементы
							if (!hasSelectedElementPregnancy(checkbox)) {
								setDisabledPregnancy(checkbox.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], true);
							}
						}
					}
					
					//Кнопка выбор случая
					if (typeof Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_754")[0] != 'undefined'){
						if (checkbox.QuestionType_Code == 226 || checkbox.QuestionType_Code == 227){
							if (checked || Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_226")[0].checked || Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_227")[0].checked){
								Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_754")[0].ownerCt.show(); //сделать show для выбора случая
							}else{
								Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("name","QuestionType_754")[0].ownerCt.hide();
							}
						}
					}					
				});
			}

			// Изменение стиля и добавление функционала главным чекбоксам в группе, Ufa, gaf #111648
			if (visionSettings['cls'] != null) {
				//Ufa gaf 01022018 #106655
				if (typeof element.itemCls == 'undefined') {
					element.itemCls = visionSettings['cls'];
				}
				//Ufa gaf 01022018 #106655 comment 22032018
//				if (visionSettings['cls'] == 'paddleft100') {
//					alert(1);
//					console.log('paddleft100-A');
//					console.log(this);
//					this.addListener('check', function (checkbox, checked) {
//						var ready = false;
//						var parent = this.el.parent('.x-fieldset-body');
//						var childfieldset = parent.dom.children;
//						for (var i = 2; i < childfieldset.length; i++) {
//							ready = true;
//							if (checked) {
//								var oobjnow = Ext.getCmp(childfieldset[1].id);
//								oobjnow.items.items[1].items.items[0].setValue(false);
//								console.log('checked-B');
//								console.log($("#" + childfieldset[i].id));
//								$("#" + childfieldset[i].id).show();
//								//скрываем при раскрытии панели текстовые поля: Смерть в неонатальном периоде: Другое, Рубец на матке 13112017
//								console.log($("textarea[name=QuestionType_185]", $("#" + childfieldset[i].id)));
//								console.log($("textarea[name=QuestionType_197]", $("#" + childfieldset[i].id)));
//								console.log($("input[name=QuestionType_246]", $("#" + childfieldset[i].id)));
//								$("textarea[name=QuestionType_185]", $("#" + childfieldset[i].id)).hide();
//								$("textarea[name=QuestionType_197]", $("#" + childfieldset[i].id)).hide();
//								$("input[name=QuestionType_246]", $("#" + childfieldset[i].id)).hide();
//							}
//						}
//					});
//				}
//				if (visionSettings['cls'] == 'paddleft120') {
//					alert(2);
//					this.addListener('check', function (checkbox, checked) {
//						var ready = false;
//						var parent = this.el.parent('.x-fieldset-body');
//						var childfieldset = parent.dom.children;
//
//						for (var i = 2; i < childfieldset.length; i++) {
//							ready = true;
//							if (checked) {
//								var oobjnow = Ext.getCmp(childfieldset[1].id);
//								oobjnow.items.items[0].items.items[0].setValue(false);
//								if (childfieldset[i].localName != "fieldset") {
//									$("#" + childfieldset[i].id).hide();
//									var oobj = Ext.getCmp(childfieldset[i].id);
//									for (var j = 0; j < oobj.items.items.length; j++) {
//										if (typeof oobj != 'undefined' &&
//												typeof oobj.items != 'undefined' &&
//												typeof oobj.items.items[j] != 'undefined' &&
//												typeof oobj.items.items[j].items != 'undefined' &&
//												typeof oobj.items.items[j].items.items[0] != 'undefined') {
//											if (typeof oobj.items.items[j].items.items[0].checked != 'undefined') {
//												oobj.items.items[j].items.items[0].setValue(false);
//											} else {
//												if (typeof oobj.items.items[j].items.items[0].clearValue == "function") {
//													oobj.items.items[j].items.items[0].clearValue();
//												}
//											}
//										}
//									}
//								}
//							}
//						}
//					});
//				}

				if (settings.QuestionType_id == '581') {
					//Смерть в неонатальном периоде: обработка чекбокса
					this.addListener('check', function (checkbox, checked) {
						var objinput = Ext.getCmp(checkbox.id);
						if (checked) {
							//отображение поля для ввода значения
							objinput.ownerCt.ownerCt.items.items[1].items.items[0].show();
						} else {
							//скрытие поля для ввода значения
							objinput.ownerCt.ownerCt.items.items[1].items.items[0].hide();
						}
					});
				}


				/* Comment 05022018
				 this.addListener('check', function(checkbox, checked) {
				 var ready = false;
				 var parent = this.el.parent('.x-fieldset-body');
				 var childfieldset = parent.dom.children;
				 
				 for (var i = 1; i < childfieldset.length; i++) {
				 ready = true;
				 if (checked) {
				 $("#"+childfieldset[i].id).show();
				 
				 //скрываем при раскрытии панели текстовые поля: Смерть в неонатальном периоде: Другое
				 $("textarea[name=QuestionType_185]", $("#"+childfieldset[i].id)).hide();
				 $("textarea[name=QuestionType_197]", $("#"+childfieldset[i].id)).hide();
				 $("input[name=QuestionType_246]", $("#"+childfieldset[i].id)).hide();
				 }else{
				 if (childfieldset[i].localName != "fieldset"){
				 
				 $("#"+childfieldset[i].id).hide();
				 var oobj = Ext.getCmp(childfieldset[i].id);
				 
				 for (var j = 0; j < oobj.items.items.length; j++) {
				 if (typeof oobj != 'undefined' &&
				 typeof oobj.items != 'undefined' &&
				 typeof oobj.items.items[j] != 'undefined' &&
				 typeof oobj.items.items[j].items != 'undefined' &&
				 typeof oobj.items.items[j].items.items[0] != 'undefined'
				 ){
				 if (typeof oobj.items.items[j].items.items[0].checked != 'undefined'){
				 oobj.items.items[j].items.items[0].setValue(false);
				 }else{
				 
				 if (typeof oobj.items.items[j].items.items[0].clearValue == "function"){
				 oobj.items.items[j].items.items[0].clearValue();
				 }
				 }
				 }
				 }
				 }
				 }
				 }
				 //gaf 119289 Заболевания внутренних половых 13112017
				 if (checkbox.name == 'QuestionType_560'){
				 //закрываем по умолчанию Вид рубца Тип рубца
				 Ext.getCmp(childfieldset[7].id).hide();
				 }
				 
				 //gaf 119289 Бесплодие 13112017
				 if (checkbox.name == 'QuestionType_561'){
				 //очищаем Продолжительность лет
				 if (!checked)
				 $("input[name=QuestionType_221]", $("#"+parent.id)).val(""); 
				 } 
				 
				 if (!ready){
				 var parent = this.el.parent('.x-column-inner');
				 var childfieldset = parent.dom.children;
				 for (var i = 1; i < childfieldset.length; i++) {
				 if (checked) {
				 $("#"+childfieldset[i].id).show();
				 }else{
				 
				 var oobj = Ext.getCmp(childfieldset[i].id);
				 //gaf 119289 21112017
				 if (typeof oobj != 'undefined' && 
				 typeof oobj.items != 'undefined' && 
				 typeof oobj.items.items[0] != 'undefined' && 
				 typeof oobj.items.items[0].checked != 'undefined'){
				 oobj.items.items[0].setValue(false);
				 }
				 
				 
				 $("#"+childfieldset[i].id).hide();   
				 }
				 }
				 }
				 }
				 );   */
				/* comment 05022018
				 }else if (settings.QuestionType_id == '581'){
				 //Смерть в неонатальном периоде: обработка чекбокса
				 
				 this.addListener('check', function(checkbox, checked) {
				 
				 var objinput = Ext.getCmp(checkbox.id);
				 
				 var parent = objinput.el.parent('.x-column-inner');
				 var childfieldset = parent.dom.children;
				 for (var i = 2; i < childfieldset.length; i++) {
				 if (checked) {
				 $("input[name=QuestionType_246]", $("#"+childfieldset[i].id)).show();
				 }else{
				 $("input[name=QuestionType_246]", $("#"+childfieldset[i].id)).hide();
				 }
				 }
				 
				 }
				 );*/
				// Скриннинг блокировка параметров, Ufa, gaf #104947
			} else if (settings.QuestionType_id == '395') {
				//10-14нед, Скриннинг
				//tempcomment 22032018
				if (region_id == 2){
				this.addListener('check', function (checkbox, checked) {
					var array_356 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_356");
					Ext.getCmp(array_356[0].id).disabledClass = "disabled_field";
					var array_357 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_357");
					Ext.getCmp(array_357[0].id).disabledClass = "disabled_field";					
					var array_378 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_378");
					
					var array_414 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_414");
					//var cmp378 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_378");
					//cmp378[0].ownerCt.ownerCt.items.items[1].items.items[0].setDisabled(true);
					//
					var array_381 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_381");
					var array_382 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_382");
					
					var array_383 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_383");					
					var array_388 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_388");
					//var array_389 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_389");
					var array_390 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_390");
					var array_391 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_391");
					var array_401 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_401");
					//var array_402 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_402");
					//var array_403 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_403");
					//var array_404 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_404");
 					//var array_405 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_405");
 					//var array_553 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_553");
					
					var array_662 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_662");
					var array_663 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_663");
					var array_664 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_664");
					var array_665 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_665");					
					
					var array_661 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_661");
					var array_667 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_667");
					
					
					if (checked) {
						//окружность живота
						Ext.getCmp(array_356[0].id).setDisabled(true);
						Ext.getCmp(array_356[0].id).setValue("");
						//высота стояния дна матки
						Ext.getCmp(array_357[0].id).setDisabled(true);
						Ext.getCmp(array_357[0].id).setValue("");
						//отеки беременных
						Ext.getCmp(array_378[0].id).setDisabled(true);
						Ext.getCmp(array_378[0].id).setValue(false);
						
						//Гестоз комбобокс
						Ext.getCmp(array_414[0].id).setDisabled(true);
						Ext.getCmp(array_414[0].id).setValue("");
						//положение плода
						Ext.getCmp(array_381[0].id).setDisabled(true);
						Ext.getCmp(array_381[0].id).setValue("");
						//предлежание
						Ext.getCmp(array_382[0].id).setDisabled(true);
						Ext.getCmp(array_382[0].id).setValue("");
						
						//крупный плод
						Ext.getCmp(array_383[0].id).setDisabled(true);
						Ext.getCmp(array_383[0].id).setValue(false);
						//обвитие пуповины
						Ext.getCmp(array_388[0].id).setDisabled(true);
						Ext.getCmp(array_388[0].id).setValue(false);
						//Фетоплацентарная недостаточность
						//Ext.getCmp(array_389[0].id).setDisabled(true);
						//Ext.getCmp(array_389[0].id).setValue(false);
						//Биологическая незрелость родовых путей в 40 недель беременности
						Ext.getCmp(array_390[0].id).setDisabled(true);
						Ext.getCmp(array_390[0].id).setValue(false);
						//Перенашивание беременности
						Ext.getCmp(array_391[0].id).setDisabled(true);
						Ext.getCmp(array_391[0].id).setValue(false);
						//Хроническая плацентарная недостаточность
						Ext.getCmp(array_401[0].id).setDisabled(true);
						Ext.getCmp(array_401[0].id).setValue(false);
						//Оценка КТГ по шкале Фишер:
						//Ext.getCmp(array_402[0].id).setDisabled(true);
						//Ext.getCmp(array_402[0].id).setValue("");
						//Содержание эстриола в суточной моче мг/сут
						//Ext.getCmp(array_403[0].id).setDisabled(true);
						//Ext.getCmp(array_403[0].id).setValue("");
						//Наличие мекония в околоплодных водах
						//Ext.getCmp(array_404[0].id).setDisabled(true);
						//Ext.getCmp(array_404[0].id).setValue(false);
						//Направление на родоразрешение:
						//Ext.getCmp(array_405[0].id).setDisabled(true);
						//Ext.getCmp(array_405[0].id).setValue("");
						//Состояние кровотока по ДПМ
						//Ext.getCmp(array_553[0].id).setDisabled(true);
						//Ext.getCmp(array_553[0].id).setValue("");	

						Ext.getCmp(array_662[0].id).setDisabled(true);
						Ext.getCmp(array_662[0].id).setValue("");
						Ext.getCmp(array_663[0].id).setDisabled(true);
						Ext.getCmp(array_663[0].id).setValue("");
						Ext.getCmp(array_664[0].id).setDisabled(true);
						Ext.getCmp(array_664[0].id).setValue("");
						Ext.getCmp(array_665[0].id).setDisabled(true);
						Ext.getCmp(array_665[0].id).setValue("");						
						
						//Ext.getCmp(array_661[0].id).setDisabled(true);
						//Ext.getCmp(array_661[0].id).setValue("");																
						Ext.getCmp(array_667[0].id).setDisabled(true);
						Ext.getCmp(array_667[0].id).setValue("");				
						$(".disabled_field").parent().prev().css("color", "#777");						
						
					} else {
						
						$(".disabled_field").parent().prev().css("color", "#222");
						Ext.getCmp(array_356[0].id).setDisabled(false);
						Ext.getCmp(array_357[0].id).setDisabled(false);
						Ext.getCmp(array_378[0].id).setDisabled(false);
						
						Ext.getCmp(array_414[0].id).setDisabled(false);
						Ext.getCmp(array_381[0].id).setDisabled(false);
						Ext.getCmp(array_382[0].id).setDisabled(false);
						
						//Ext.getCmp(array_405[0].id).setDisabled(false);
						//Ext.getCmp(array_553[0].id).setDisabled(false);
						
						Ext.getCmp(array_383[0].id).setDisabled(false);
						Ext.getCmp(array_388[0].id).setDisabled(false);
						//Ext.getCmp(array_389[0].id).setDisabled(false);
						Ext.getCmp(array_390[0].id).setDisabled(false);
						Ext.getCmp(array_391[0].id).setDisabled(false);
						Ext.getCmp(array_401[0].id).setDisabled(false);
						//Ext.getCmp(array_402[0].id).setDisabled(false);
						//Ext.getCmp(array_403[0].id).setDisabled(false);
						//Ext.getCmp(array_404[0].id).setDisabled(false);

						Ext.getCmp(array_662[0].id).setDisabled(false);
						Ext.getCmp(array_663[0].id).setDisabled(false);
						Ext.getCmp(array_664[0].id).setDisabled(false);
						Ext.getCmp(array_665[0].id).setDisabled(false);						
						
						//Ext.getCmp(array_661[0].id).setDisabled(false);
						Ext.getCmp(array_667[0].id).setDisabled(false);						
												
					}
				}
				);
				}
			} else if (settings.QuestionType_id == '396') {
				//18-21нед, Скриннинг
				//tempcomment 22032018 
				if (region_id == 2){
				this.addListener('check', function (checkbox, checked) {
					var array_390 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_390");
					var array_391 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_391");
					//var array_404 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_404");				
					//var array_403 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_403");									
					if (checked) {
						Ext.getCmp(array_390[0].id).setDisabled(true);
						Ext.getCmp(array_390[0].id).setValue(false);
						Ext.getCmp(array_391[0].id).setDisabled(true);
						Ext.getCmp(array_391[0].id).setValue(false);
						//Ext.getCmp(array_404[0].id).setDisabled(true);
						//Ext.getCmp(array_404[0].id).setValue(false);						
						
						//Ext.getCmp(array_403[0].id).setDisabled(true);
						//Ext.getCmp(array_403[0].id).setValue("");						
					
					} else {
						Ext.getCmp(array_390[0].id).setDisabled(false);
						Ext.getCmp(array_391[0].id).setDisabled(false);
						//Ext.getCmp(array_403[0].id).setDisabled(false);
						//Ext.getCmp(array_404[0].id).setDisabled(false);
					
					}
				}
				);
				}
			} else if (settings.QuestionType_id == '599') {
				//Рубец на матке открывает поля ид рубца Тип рубца gaf 13112017
				var region_id = getRegionNumber();
				if (region_id == 2){
					//Рубец на матке открывает поля ид рубца Тип рубца gaf 13112017
					this.addListener('check', function (checkbox, checked) {
						if (checked) {
							Ext.getCmp(this.el.parent('.x-fieldset').id).items.items[8].show();
						} else {
							Ext.getCmp(this.el.parent('.x-fieldset').id).items.items[8].hide();
							Ext.getCmp(this.el.parent('.x-fieldset').id).items.items[8].items.items[0].items.items[0].clearValue();
							Ext.getCmp(this.el.parent('.x-fieldset').id).items.items[8].items.items[1].items.items[0].clearValue();
						}
 					}
					);
				}else if (region_id == 30){
					this.addListener('check', function (checkbox, checked) {
						if (checked) {
							Ext.getCmp(this.el.parent('.x-fieldset').id).items.items[6].show();
						} else {
							Ext.getCmp(this.el.parent('.x-fieldset').id).items.items[6].hide();
							Ext.getCmp(this.el.parent('.x-fieldset').id).items.items[6].items.items[0].items.items[0].clearValue();
							Ext.getCmp(this.el.parent('.x-fieldset').id).items.items[6].items.items[1].items.items[0].clearValue();
						}
					}
					);					
 				}																					
			} else if (settings.QuestionType_id == '769') {
				this.addListener('check', function (checkbox, checked) {
					var hidefield = Ext.getCmp('swPersonPregnancyEditWindow').WizardPanel.getCategory('Anketa').getForm().findField('QuestionType_770');
					if (hidefield){
						var hidepanel = hidefield.findParentByType('panel');
						if (hidepanel){
							if (checked) {
								hidepanel.show();
								hidefield.allowBlank = false;
							}else{
								hidepanel.hide();
								hidefield.setValue('');
								hidefield.allowBlank = true;
							}
						}
					}

				 }
				);
			} else if (settings.QuestionType_id == '546') {
				this.addListener('check', function (checkbox, checked) {
						var wizardCategory = this.findParentBy(function (obj) { return (obj && obj.loadCategory); });
						if (wizardCategory) {
							var hidefield = wizardCategory.getForm().findField('QuestionType_771');
							if (hidefield) {
								hidefield.addListener('change', function (checkbox, checked) {
									if (checked) {
										hidefield.removeClass('invalid_field');
										if (hidefield.getEl())
											hidefield.getEl().setStyle('border', '1px solid white');
									}
								});

								var hidepanel = hidefield.findParentByType('panel');
								if (hidepanel) {
									if (checked) {
										hidefield.addClass('invalid_field');
										if (hidefield.items && hidefield.items.find(function(comp){return comp.checked;})){
											hidefield.removeClass('invalid_field');
										}
										hidepanel.show();
										hidefield.allowBlank = false;
									} else {
										hidefield.removeClass('invalid_field');
										hidepanel.hide();
										hidefield.setValue('');
										hidefield.allowBlank = true;
									}
								}
							}
						}
					}
				);
			}

			//gaf 09112017 формирование наименования по регионам
			var region_id = getRegionNumber();

			for (name in defaultSettings) {
				var settingValue = visionSettings[name] || defaultSettings[name];
				//gaf 119289 09112017 формирование наимиенования по регионам
				settingValue = visionSettings[name + region_id] || settingValue;
				switch (name) {
					case 'fieldLabel':
						element.setBoxLabel(settingValue);
						break;
					case 'hidden':
						element.setVisible(!settingValue);
						break;
				}
			}
		}
	});

	c.NumberField = Ext.extend(Ext.form.NumberField, {
		QuestionType_Code: null,
		settings: null,
		fieldLabel: 'NumberFieldName',
		allowNegative: false,
		allowDecimals: false,

		getLabelEl: function() {
			if (this.el && this.el.parent('.x-form-item')) {
				return this.el.parent('.x-form-item').child('.x-form-item-label');
			}
			return null;
		},

		setLabelWidth: function(labelWidth) {
			this.labelWidth = Number(labelWidth);
			if (this.getLabelEl()) {
				this.getLabelEl().setWidth(this.labelWidth+3);
			}
		},

		setFieldLabel: function(text) {
			if (this.rendered) {
				var labelSeparator = this.labelSeparator?this.labelSeparator:':';
				this.el.up('.x-form-item', 10, true).child('.x-form-item-label').update(text+labelSeparator);
			}
			this.fieldLabel = text;
		},

		initComponent: function() {
			sw.Promed.QuestionType.NumberField.superclass.initComponent.apply(this, arguments);

			this.addListener('render', function(field) {
				if (this.labelWidth) {
					this.setLabelWidth(this.labelWidth);
				}
				/*
				//Автоматическкое выставление ширины по подписи поля
				var m = Ext.util.TextMetrics.createInstance(field.getLabelEl());
				field.setLabelWidth(m.getWidth(field.getLabelEl().dom.innerText) + 3);
				*/
			});

			if (this.settings) {
				this.initSettings(this.settings);
			}
		},

		initSettings: function (settings) {
			var element = this;

			element.settings = settings;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.name = 'QuestionType_' + settings.QuestionType_id;

			element.name = settings.container.getNameFromMap(settings);

			//для Анкеты gaf
			if (Ext.getCmp(element.id).settings.DispClass_id == 14){			
				//gaf 09022018 в режиме fullanketa 
				this.addListener('change', function (field, newVal, oldVal) {
					if (newVal.toString() != "") {
						setDisabledPregnancy(field.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], false);
					} else {
						//проверка есть ли выбранные элементы
						if (!hasSelectedElementPregnancy(field)) {
							setDisabledPregnancy(field.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], true);
						}
					}
				});
			}
			//для контроля минимального значения в числовых полях на форме "Сведения о беременности"
			minValue = Number.NEGATIVE_INFINITY;
			if (settings && settings.DispClass_id && (settings.DispClass_id == 14 || settings.DispClass_id == 16 || settings.DispClass_id == 18)){
				minValue = 0;
			}

			var defaultSettings = {
				fieldLabel: settings.QuestionType_Name,
				width: 121,
				labelWidth: 100,
				maskRe: /[0-9]/,
				validator: null,
				minValue: minValue,
				maxValue: Number.POSITIVE_INFINITY,
				hidden: false
			};

			//gaf 119289
			var region_id = getRegionNumber();

			var visionSettings = settings.container.getVisionSettings(settings) || {};

			//Ufa gaf 29012018 #106655
			if (visionSettings['cls' + region_id] != null) {
				element.itemCls = visionSettings['cls' + region_id];
			}

			//Ufa gaf 26012018 #106655
			if (visionSettings['cls'] != null && (typeof element.itemCls == 'undefined')) {
				element.itemCls = visionSettings['cls'];
			}
			
			for(name in defaultSettings) {
				var settingValue = visionSettings[name] || defaultSettings[name];
				//gaf 119289 формирование наимиенования по регионам
				settingValue = visionSettings[name+region_id] || settingValue;
				switch(name) {
					case 'fieldLabel':
						element.setFieldLabel(settingValue);
						break;
					case 'width':
						element.anchor = undefined;
						element.setWidth(settingValue);
						break;
					case 'labelWidth':
						element.setLabelWidth(settingValue);
						break;
					case 'maskRe':
						element.maskRe = new RegExp(settingValue);
						break;
					case 'validator':
						if (!settingValue) {
							element.validator = null;
						} else if (typeof settingValue == 'function') {
							element.validator = settingValue;
						} else {
							element.validator = new Function('value', settingValue);
						}
						break;
					case 'minValue':
						element.minValue = settingValue;
						break;
					case 'maxValue':
						element.maxValue = settingValue;
						break;
					case 'hidden':
						element.setVisible(!settingValue);
						break;
				}
			}
			element.validate();
		}
	});

	c.DecNumberField = Ext.extend(Ext.form.NumberField, {
		QuestionType_Code: null,
		settings: null,
		fieldLabel: 'DecNumberFieldName',
		allowNegative: false,

		getLabelEl: function() {
			if (this.el && this.el.parent('.x-form-item')) {
				return this.el.parent('.x-form-item').child('.x-form-item-label');
			}
			return null;
		},

		setLabelWidth: function(labelWidth) {
			this.labelWidth = Number(labelWidth);
			if (this.getLabelEl()) {
				this.getLabelEl().setWidth(this.labelWidth+3);
			}
		},

		setFieldLabel: function(text) {
			if (this.rendered) {
				var labelSeparator = this.labelSeparator?this.labelSeparator:':';
				this.el.up('.x-form-item', 10, true).child('.x-form-item-label').update(text+labelSeparator);
			}
			this.fieldLabel = text;
		},

		initComponent: function() {
			sw.Promed.QuestionType.NumberField.superclass.initComponent.apply(this, arguments);

			this.addListener('render', function(field) {
				if (this.labelWidth) {
					this.setLabelWidth(this.labelWidth);
				}
				/*
				//Автоматическкое выставление ширины по подписи поля
				var m = Ext.util.TextMetrics.createInstance(field.getLabelEl());
				field.setLabelWidth(m.getWidth(field.getLabelEl().dom.innerText) + 3);
				*/
			});

			if (this.settings) {
				this.initSettings(this.settings);
			}
		},

		initSettings: function (settings) {
			var element = this;

			element.settings = settings;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.name = 'QuestionType_' + settings.QuestionType_id;

			element.name = settings.container.getNameFromMap(settings);

			//для Анкеты gaf
			if (Ext.getCmp(element.id).settings.DispClass_id == 14){
				//gaf 09022018 в режиме fullanketa 
				this.addListener('change', function (field, newVal, oldVal) {
					if (newVal.toString() != "") {
						setDisabledPregnancy(field.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], false);
					} else {
						//проверка есть ли выбранные элементы
						if (!hasSelectedElementPregnancy(field)) {
							setDisabledPregnancy(field.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], true);
						}
					}
				});
			}
			//для контроля минимального значения в числовых полях на форме "Сведения о беременности"
			minValue = Number.NEGATIVE_INFINITY;
			if (settings && settings.DispClass_id && (settings.DispClass_id == 14 || settings.DispClass_id == 16 || settings.DispClass_id == 18)){
				minValue = 0;
			}

			var defaultSettings = {
				fieldLabel: settings.QuestionType_Name,
				width: 121,
				labelWidth: 100,
				maskRe: /[0-9]/,
				validator: null,
				minValue: minValue,
				maxValue: Number.POSITIVE_INFINITY,
				hidden: false
			};

			//gaf 119289
			var region_id = getRegionNumber();

			var visionSettings = settings.container.getVisionSettings(settings) || {};

			//Ufa gaf 29012018 #106655
			if (visionSettings['cls' + region_id] != null) {
				element.itemCls = visionSettings['cls' + region_id];
			}

			//Ufa gaf 26012018 #106655
			if (visionSettings['cls'] != null && (typeof element.itemCls == 'undefined')) {
				element.itemCls = visionSettings['cls'];
			}

			for(name in defaultSettings) {
				var settingValue = visionSettings[name] || defaultSettings[name];
				//gaf 119289 формирование наимиенования по регионам
				settingValue = visionSettings[name+region_id] || settingValue;
				switch(name) {
					case 'fieldLabel':
						element.setFieldLabel(settingValue);
						break;
					case 'width':
						element.anchor = undefined;
						element.setWidth(settingValue);
						break;
					case 'labelWidth':
						element.setLabelWidth(settingValue);
						break;
					case 'maskRe':
						element.maskRe = new RegExp(settingValue);
						break;
					case 'validator':
						if (!settingValue) {
							element.validator = null;
						} else if (typeof settingValue == 'function') {
							element.validator = settingValue;
						} else {
							element.validator = new Function('value', settingValue);
						}
						break;
					case 'minValue':
						element.minValue = settingValue;
						break;
					case 'maxValue':
						element.maxValue = settingValue;
						break;
					case 'hidden':
						element.setVisible(!settingValue);
						break;
				}
			}
			element.validate();
		}
	});

	c.TextField = Ext.extend(Ext.form.TextField, {
		QuestionType_Code: null,
		settings: null,
		autoCreate: {tag: 'input', type: 'text', autocomplete: 'off', maxlength: 1024},
		fieldLabel: 'TextFieldName',

		getLabelEl: function() {
			if (this.el && this.el.parent('.x-form-item')) {
				return this.el.parent('.x-form-item').child('.x-form-item-label');
			}
			return null;
		},

		setLabelWidth: function(labelWidth) {
			this.labelWidth = Number(labelWidth);
			if (this.getLabelEl()) {
				this.getLabelEl().setWidth(this.labelWidth+3);
			}
		},

		setFieldLabel: function(text) {
			if (this.rendered) {
				var labelSeparator = this.labelSeparator?this.labelSeparator:':';
				this.el.up('.x-form-item', 10, true).child('.x-form-item-label').update(text+labelSeparator);
			}
			this.fieldLabel = text;
		},

		initComponent: function() {
			sw.Promed.QuestionType.TextField.superclass.initComponent.apply(this, arguments);

			this.addListener('render', function(field) {
				if (this.labelWidth) {
					this.setLabelWidth(this.labelWidth);
				}
			});

			if (this.settings) {
				this.initSettings(this.settings);
			}
		},

		initSettings: function(settings) {
			var element = this;

			element.settings = settings;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.name = 'QuestionType_'+settings.QuestionType_id;

			element.name = settings.container.getNameFromMap(settings);

			var defaultSettings = {
				fieldLabel: settings.QuestionType_Name,
				width: 121,
				labelWidth: 100,
				maskRe: /[\s\S]/,
				validator: null,
				MaxLength: 1024,
				hidden: false
			};
			
			//gaf 119289
			var region_id = getRegionNumber();

			var visionSettings = settings.container.getVisionSettings(settings) || {};
			for(name in defaultSettings) {
				var settingValue = visionSettings[name] || defaultSettings[name];
				//gaf 119289
				settingValue = visionSettings[name+region_id] || settingValue;
				switch(name) {
					case 'MaxLength':
						element.autoCreate.maxlength = element.maxLength = settingValue;
						if (element.rendered) {
							element.getEl().dom.maxLength = element.maxLength;
						}
						break;
					case 'fieldLabel':
						element.setFieldLabel(settingValue);
						break;
					case 'width':
						element.anchor = undefined;
						element.setWidth(settingValue);
						break;
					case 'labelWidth':
						element.setLabelWidth(settingValue);
						break;
					case 'maskRe':
						element.maskRe = new RegExp(settingValue);
						break;
					case 'validator':
						if (!settingValue) {
							element.validator = null;
						} else if (typeof settingValue == 'function') {
							element.validator = settingValue;
						} else {
							element.validator = new Function('value', settingValue);
						}
						break;
					case 'hidden':
						element.setVisible(!settingValue);
						break;
				}
			}
			element.validate();
		}
	});

	c.TextArea = Ext.extend(Ext.form.TextArea, {
		QuestionType_Code: null,
		autoCreate: {tag: "textarea", autocomplete: "off", maxlength: 1024},
		anchor: '100%',
		height: 80,
		fieldLabel: 'TextAreaName',

		getLabelEl: function() {
			if (this.el && this.el.parent('.x-form-item')) {
				return this.el.parent('.x-form-item').child('.x-form-item-label');
			}
			return null;
		},

		setFieldLabel: function(text) {
			if (this.rendered) {
				var labelSeparator = this.labelSeparator?this.labelSeparator:':';
				this.el.up('.x-form-item', 10, true).child('.x-form-item-label').update(text+labelSeparator);
			}
			this.fieldLabel = text;
		},

		initComponent: function() {
			sw.Promed.QuestionType.TextArea.superclass.initComponent.apply(this, arguments);
			if (this.settings) {
				this.initSettings(this.settings);
			}
		},

		initSettings: function(settings) {
			var element = this;

			element.QuestionType_Code = settings.QuestionType_Code;
			element.settings = settings;
			element.name = 'QuestionType_'+settings.QuestionType_id;

			element.name = settings.container.getNameFromMap(settings);

			var defaultSettings = {
				fieldLabel: settings.QuestionType_Name,
				maskRe: /[\s\S]/,
				MaxLength: 1024,
				hidden: false
			};

			var visionSettings = settings.container.getVisionSettings(settings) || {};
			for(name in defaultSettings) {
				var settingValue = visionSettings[name] || defaultSettings[name];
				switch(name) {
					case 'MaxLength':
						element.autoCreate.maxlength = element.maxLength = settingValue;
						if (element.rendered) {
							element.getEl().dom.maxLength = element.maxLength;
						}
						break;
					case 'fieldLabel':
						element.setFieldLabel(settingValue);
						break;
					case 'maskRe':
						element.maskRe = new RegExp(settingValue);
						break;
					case 'maskRe':
						element.setVisible(!settingValue);
						break;
				}
			}
		}
	});

	c.OtherTextArea = Ext.extend(Ext.Panel, {
		layout: 'form',
		QuestionType_Code: null,
		border: false,
		settings: null,

		setBoxLabel: function(text) {
			this.Checkbox.setBoxLabel(text);
		},

		initComponent: function() {
			var element = this;

			element.Checkbox = new sw.Promed.swCheckbox({
				boxLabel: 'CheckBoxName',
				hideLabel: true,
				setBoxLabel: function(text) {
					this.boxLabel = text;
					if (this.rendered) {
						if (!this.labelEl) {
							this.labelEl = this.innerWrap.createChild({
								tag: 'label',
								htmlFor: this.el.id,
								cls: 'x-form-cb-label',
								html: this.boxLabel
							});
						} else {
							this.labelEl.dom.innerHTML = this.boxLabel;
						}
					}
				},
				listeners: {
					'check': function (checkbox, checked) {

						// Отображение текстового поля в зависимости от чекбокса, Ufa, gaf #111648
						if (checkbox.el.dom.name == "QuestionType_185_check" || checkbox.el.dom.name == "QuestionType_197_check") {
							var parent = this.el.parent('.x-form-item');
							var numel = checkbox.el.dom.name.replace("QuestionType_", "").replace("_check", "");
							if (checked) {
								//$("textarea[name=QuestionType_" + numel + "]", $("#" + parent.dom.id).next()).show();
								//27032018
								Ext.getCmp(checkbox.id).ownerCt.items.items[1].show();
							} else {
								$("textarea[name=QuestionType_" + numel + "]", $("#" + parent.dom.id).next()).hide();
								//27032018
								Ext.getCmp(checkbox.id).ownerCt.items.items[1].hide();
							}
						}
						element.TextArea.setDisabled(!checked || checkbox.disabled);
						if (!checked) {
							element.TextArea.setValue(null);
						}
					}
				}
			});

			element.TextArea = new Ext.form.TextArea({
				autoCreate: {tag: "textarea", autocomplete: "off", maxlength: 1024},
				//27032018
				anchor: '80%',
				height: 80,
				hideLabel: true,
				disabled: true,
				//27032018
				listeners: {
					change: function(field, newVal, oldVal) {
						//для Анкеты gaf
						if (Ext.getCmp(element.id).settings.DispClass_id == 14){
							var checkbox1 = Ext.getCmp(field.id).ownerCt.ownerCt.items.items[1].items.items[1].items.items[0];
							if (typeof checkbox1.ownerCt.ownerCt.ownerCt.items.items[1] != 'undefined' &&
									typeof checkbox1.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1] != 'undefined' &&
									typeof checkbox1.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0] != 'undefined') {
								if (newVal != "") {
									setDisabledPregnancy(checkbox1.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], false);
								} else {
									//проверка есть ли выбранные элементы
									if (!hasSelectedElementPregnancy(Ext.getCmp(field.id).ownerCt.ownerCt.items.items[2].items.items[0].items.items[0])) {
										setDisabledPregnancy(checkbox1.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], true);
									}
								}
							}
						}						
					}
				}
			});

			Ext.apply(element, {items: [element.Checkbox, element.TextArea]});

			sw.Promed.QuestionType.OtherTextArea.superclass.initComponent.apply(this, arguments);

			if (element.settings) {
				element.initSettings(element.settings);
			}
		},

		initSettings: function(settings) {
			var element = this;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.settings = settings;

			element.TextArea.name = 'QuestionType_'+settings.QuestionType_id;

			element.TextArea.name = settings.container.getNameFromMap(settings);

			var visionSettings = settings.container.getVisionSettings(settings) || {};
			
			//Ufa gaf 25012018 #106655
			if (visionSettings['cls'] != null) {
				element.itemCls=visionSettings['cls'];
			}

			if (visionSettings.MaxLength) {
				element.TextArea.maxLength = visionSettings.MaxLength;
				element.TextArea.autoCreate.maxlength = element.TextArea.maxLength;
				if (element.TextArea.rendered) {
					element.TextArea.getEl().dom.maxLength = element.TextArea.maxLength;
				}
			}
			element.setVisible(visionSettings.hidden !== true);

			element.Checkbox.name = 'QuestionType_'+settings.QuestionType_id+'_check';
			element.Checkbox.setBoxLabel(settings.QuestionType_Name);
		}
	});

	c.LpuCombo = Ext.extend(sw.Promed.SwLpuCombo, {
		QuestionType_Code: null,
		settings: null,

		getLabelEl: function() {
			if (this.el && this.el.parent('.x-form-item')) {
				return this.el.parent('.x-form-item').child('.x-form-item-label');
			}
			return null;
		},

		setLabelWidth: function(labelWidth) {
			this.labelWidth = Number(labelWidth);
			if (this.getLabelEl()) {
				this.getLabelEl().setWidth(this.labelWidth+3);
				this.container.setStyle('padding-left', (this.labelWidth+5)+'px');
			}
		},

		setFieldLabel: function(text) {
			if (this.rendered) {
				var labelSeparator = this.labelSeparator?this.labelSeparator:':';
				this.el.up('.x-form-item', 10, true).child('.x-form-item-label').update(text+labelSeparator);
			}
			this.fieldLabel = text;
		},

		initComponent: function() {
			sw.Promed.QuestionType.LpuCombo.superclass.initComponent.apply(this, arguments);

			if (this.settings) {
				this.initSettings(this.settings);

				if (this.settings.container) {
					this.settings.container.comboForLoad.push(this);
				}
			}
		},

		initSettings: function(settings) {
			var element = this;

			element.settings = settings;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.hiddenName = 'QuestionType_'+settings.QuestionType_id;

			element.hiddenName = settings.container.getNameFromMap(settings);

			var defaultSettings = {
				fieldLabel: settings.QuestionType_Name,
				width: 121,
				labelWidth: 100,
				hidden: false
			};

			//для Анкеты gaf
			if (Ext.getCmp(element.id).settings.DispClass_id == 14){
				//gaf 09022018 в режиме fullanketa 
				this.addListener('change', function (field, newVal, oldVal) {
					if (newVal != "") {
						setDisabledPregnancy(field.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], false);
					} else {
						//проверка есть ли выбранные элементы
						if (!hasSelectedElementPregnancy(field)) {
							setDisabledPregnancy(field.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], true);
						}
					}
				});
			}

			var visionSettings = settings.container.getVisionSettings(settings) || {};
			for(name in defaultSettings) {
				var settingValue = visionSettings[name] || defaultSettings[name];
				switch(name) {
					case 'fieldLabel':
						element.setFieldLabel(settingValue);
						break;
					case 'width':
						element.anchor = undefined;
						element.width = Number(settingValue);
						element.setWidth(element.width);
						break;
					case 'labelWidth':
						element.setLabelWidth(settingValue);
						break;
					case 'hidden':
						element.setContainerVisible(!settingValue);
						break;
				}
			}
		}
	});

	c.DiagCombo = Ext.extend(sw.Promed.SwDiagCombo, {
		QuestionType_Code: null,
		settings: null,

		getLabelEl: function() {
			return this.el.parent('.x-form-item').child('.x-form-item-label');
		},

		initComponent: function() {
			sw.Promed.QuestionType.LpuCombo.superclass.initComponent.apply(this, arguments);

			if (this.settings) {
				this.initSettings(this.settings);
			}
		},

		initSettings: function(settings) {
			var element = this;

			element.settings = settings;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.hiddenName = 'QuestionType_'+settings.QuestionType_id;
			element.setFieldLabel(settings.QuestionType_Name);

			element.hiddenName = settings.container.getNameFromMap(settings);
			

			//для Анкеты gaf
			if (Ext.getCmp(element.id).settings.DispClass_id == 14){
				//gaf 09022018 в режиме fullanketa 
				this.addListener('change', function (field, newVal, oldVal) {
					if (newVal != "") {
						setDisabledPregnancy(field.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], false);
					} else {
						//проверка есть ли выбранные элементы
						if (!hasSelectedElementPregnancy(field)) {
							setDisabledPregnancy(field.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], true);
						}
					}	
				});
			}

			var visionSettings = settings.container.getVisionSettings(settings) || {};
			if (visionSettings.fieldLabel) {
				element.setFieldLabel(visionSettings.fieldLabel);
			}
			if (visionSettings.width) {
				element.anchor = undefined;
				element.setWidth(visionSettings.width);
			}
			element.setContainerVisible(visionSettings.hidden !== true);
		}
	});

    c.LpuDifferent = Ext.extend(sw.Promed.SwBaseRemoteCombo, {
        triggerAction: 'all',
        editable: true,
        displayField:'LpuDifferent_Name',
        valueField: 'LpuDifferent_id',
        fieldLabel: langs('Иное МО'),
        searchMode: null,
        settings: null,
        Org_id: null,
        store: new Ext.data.JsonStore({
            url: '/?c=PersonPregnancy&m=getDifferentLpu',
            key: 'LpuDifferent_id',
            autoLoad: true,
            fields: [
                {name: 'LpuDifferent_id', type: 'int'},
                {name: 'LpuDifferent_Code', type: 'string'},
                {name: 'LpuDifferent_Name', type: 'string'}
            ]
        }),
        tpl: '<tpl for="."><div class="x-combo-list-item">'+
            '{LpuDifferent_Name}&nbsp;'+
            '</div></tpl>',
        trigger2Class: 'x-form-search-trigger',
        onTrigger2Click: function() {
            var combo = this;
            getWnd('swPersonPregnancyOtherMO').show(combo);
        },
        getLabelEl: function() {
            if (this.el && this.el.parent('.x-form-item')) {
                return this.el.parent('.x-form-item').child('.x-form-item-label');
            }
            return null;
        },

        setLabelWidth: function(labelWidth) {
            this.labelWidth = Number(labelWidth);
            if (this.getLabelEl()) {
                this.getLabelEl().setWidth(this.labelWidth+3);
                this.container.setStyle('padding-left', (this.labelWidth+3)+'px');
            }
        },

        setFieldLabel: function(text) {
            if (this.rendered) {
                var labelSeparator = this.labelSeparator?this.labelSeparator:':';
                this.el.up('.x-form-item', 10, true).child('.x-form-item-label').update(text+labelSeparator);
            }
            this.fieldLabel = text;
        },
        setWidth: function(text) {
            this.width = text;
         },

        settEditable: function(val) {
            this.editable = val;
        },
        initComponent: function() {
            sw.Promed.QuestionType.LpuDifferent.superclass.initComponent.apply(this, arguments);

            if (this.settings) {
                this.initSettings(this.settings);
            }
        },
        initSettings: function(settings) {
            var element = this;

            element.settings = settings;
            element.QuestionType_Code = settings.QuestionType_Code;
            element.hiddenName = 'QuestionType_'+settings.QuestionType_id;

            element.hiddenName = settings.container.getNameFromMap(settings);

            var defaultSettings = {
                fieldLabel: settings.QuestionType_Name,
                width: 121,
                labelWidth: 100,
                hidden: false,
                editable: false
            };
            var region_id = getRegionNumber();

            var visionSettings = settings.container.getVisionSettings(settings) || {};
            if (visionSettings['cls' + region_id] != null) {
                element.itemCls = visionSettings['cls' + region_id];
            }
            if (visionSettings['cls'] != null && (typeof element.itemCls == 'undefined')) {
                element.itemCls = visionSettings['cls'];
            }
            for(name in defaultSettings) {
                var settingValue = visionSettings[name] || defaultSettings[name];
                settingValue = visionSettings[name+region_id] || settingValue;
                switch(name) {
                    case 'fieldLabel':
                        element.setFieldLabel(settingValue);
                        break;
                    case 'width':
                        element.anchor = undefined;
                        element.width = Number(settingValue);
                        element.setWidth(element.width);
                        break;
                    case 'labelWidth':
                        element.setLabelWidth(settingValue);
                        break;
                    case 'hidden':
                        element.hidden = settingValue;
                        element.setContainerVisible(!settingValue);
                        break;
                    case 'editable':
                        element.settEditable(settingValue);
                        break;
                }
            }
        }
    });

	c.CommonSprCombo = Ext.extend(sw.Promed.SwCommonSprCombo, {
		QuestionType_Code: null,
		editable: false,
		//gaf #106655 11042018
 		onLoadStore:function() {
 
			var region_id = getRegionNumber();
			//gaf #104945 04042018			
			if (region_id == '2'){
				//Из комбо Гестоз убираем значения Нет Водянка	
				if (this.QuestionType_Code == '414'){
					
				
					this.store.remove(this.store.getAt(0));
					this.store.remove(this.store.getAt(0));
					var item_5 = this.store.getAt(5);
					var item_4 = this.store.getAt(4);
					var item_3 = this.store.getAt(3);
					var item_2 = this.store.getAt(2);
					var item_1 = this.store.getAt(1);
					var item_0 = this.store.getAt(0);
					this.store.remove(item_5);
					this.store.remove(item_4);
					this.store.remove(item_3);
					this.store.remove(item_2);
					this.store.remove(item_1);
					this.store.remove(item_0);
					item_4.data.Preeclampsia_Code = 6;
					item_4.data.Preeclampsia_Name = "Гепатоз, холестаз беременных";
					item_2.data.Preeclampsia_Code = 4;
					item_3.data.Preeclampsia_Code = 5;
					item_5.data.Preeclampsia_Code = 2;
					item_1.data.Preeclampsia_Code = 3;
					item_0.data.Preeclampsia_Code = 1;
					this.store.add(item_0);					
					this.store.add(item_5);
					this.store.add(item_1);
					this.store.add(item_2);
					this.store.add(item_3);
					this.store.add(item_4);		
					
				}
				//Из комбо Многоплодие убираем значения
				if (this.QuestionType_Code == '417'){
					this.store.remove(this.store.getAt(0));
					this.store.remove(this.store.getAt(0));				
					
					var item_0 = this.store.getAt(0);
					var item_1 = this.store.getAt(1);
					var item_2 = this.store.getAt(2);
					var item_3 = this.store.getAt(3);
					var item_4 = this.store.getAt(4);
					var item_5 = this.store.getAt(5);
					
					this.store.remove(item_0);
					this.store.remove(item_1);
					this.store.remove(item_2);
					this.store.remove(item_3);
					this.store.remove(item_4);
					this.store.remove(item_5);
					
					item_0.data.Twins_Code = 4;
					item_1.data.Twins_Name = "Более трех плодов";					
					item_1.data.Twins_Code = 5;
					item_2.data.Twins_Code = 1;
					item_3.data.Twins_Code = 2;
					item_4.data.Twins_Code = 3;					
					
					this.store.add(item_2);
					this.store.add(item_3);
					this.store.add(item_4);
					this.store.add(item_0);
					this.store.add(item_1);
					
				}
				//Из комбо Наличие ВПР по УЗИ обновляем значение
				if (this.QuestionType_Code == '421'){					
					var item_0 = this.store.getAt(1);
					var item_1 = this.store.getAt(2);

					this.store.remove(item_0);
					this.store.remove(item_1);

					item_0.data.Twins_Name = "Подтвержденные (предполагаемые) ВПР плода";
					item_0.data.PresenceCDF_Name = "Подтвержденные (предполагаемые) ВПР плода";
					item_0.json.PresenceCDF_Name = "Подтвержденные (предполагаемые) ВПР плода";
					
					item_1.data.Twins_Name = "Подтвержденные хромосомные аномалии плода";
					item_1.data.PresenceCDF_Name = "Подтвержденные хромосомные аномалии плода";
					item_1.json.PresenceCDF_Name = "Подтвержденные хромосомные аномалии плода";
					
					this.store.add(item_0);
					this.store.add(item_1);					
					
				}
				//Из комбо Гипотрофия плода убираем значения
				if (this.QuestionType_Code == '422'){					
					this.store.remove(this.store.getAt(0));
					this.store.remove(this.store.getAt(0));		

					var item_0 = this.store.getAt(0);
					var item_1 = this.store.getAt(1);
					var item_2 = this.store.getAt(2);
					
					this.store.remove(item_0);
					this.store.remove(item_1);
					this.store.remove(item_2);
					
					item_0.data.GenConditFetus_Code = 1;
					item_1.data.GenConditFetus_Code = 2;	
					item_2.data.GenConditFetus_Code = 3;	
					
					this.store.add(item_0);
					this.store.add(item_1);
					this.store.add(item_2);
				}				
			}else{
			//Из комбо Многоплодие убираем значения						
 				if (this.QuestionType_Code == '417'){
					if (region_id != '30'){
						this.store.remove(this.store.getAt(4));
						this.store.remove(this.store.getAt(4));
						this.store.remove(this.store.getAt(4));
						this.store.remove(this.store.getAt(4));
					}else{
						this.store.remove(this.store.getAt(0));
						this.store.remove(this.store.getAt(0));				

						var item_0 = this.store.getAt(0);
						var item_1 = this.store.getAt(1);
						var item_2 = this.store.getAt(2);
						var item_3 = this.store.getAt(3);
						var item_4 = this.store.getAt(4);
						var item_5 = this.store.getAt(5);
						this.store.remove(item_0);
						this.store.remove(item_1);
						this.store.remove(item_2);
						this.store.remove(item_3);
						this.store.remove(item_4);
						this.store.remove(item_5);
						item_0.data.Twins_Code = 4;
						item_1.data.Twins_Name = "Более трех плодов";					
						item_1.data.Twins_Code = 5;
						item_2.data.Twins_Code = 1;
						item_3.data.Twins_Code = 2;
						item_4.data.Twins_Code = 3;					
						item_5.data.Twins_Code = 6;

						this.store.add(item_2);
						this.store.add(item_3);
						this.store.add(item_4);
						this.store.add(item_0);
						this.store.add(item_1);						
						this.store.add(item_5);	
					}
 				}
 				if (this.QuestionType_Code == '421'){
					if (region_id != '30'){
						var item_1 = this.store.getAt(2);
						this.store.remove(item_1);					
						item_1.data.Twins_Name = "МХА";
						item_1.data.PresenceCDF_Name = "МХА";
						item_1.json.PresenceCDF_Name = "МХА";					
						this.store.add(item_1);
					}else{
						var item_0 = this.store.getAt(1);
						var item_1 = this.store.getAt(2);

						this.store.remove(item_0);
						this.store.remove(item_1);

						item_0.data.Twins_Name = "Подтвержденные (предполагаемые) ВПР плода";
						item_0.data.PresenceCDF_Name = "Подтвержденные (предполагаемые) ВПР плода";
						item_0.json.PresenceCDF_Name = "Подтвержденные (предполагаемые) ВПР плода";

						item_1.data.Twins_Name = "Подтвержденные хромосомные аномалии плода";
						item_1.data.PresenceCDF_Name = "Подтвержденные хромосомные аномалии плода";
						item_1.json.PresenceCDF_Name = "Подтвержденные хромосомные аномалии плода";

						this.store.add(item_0);
						this.store.add(item_1);						
					}
 				}

				if (region_id != '30'){
					//Из комбо Предлежание убираем значения Смешанное ягодично-ножное				
					if (this.QuestionType_Code == '382'){	 				
						this.store.remove(this.store.getAt(3));				
					}
				}else{
					//для Астрахани
					if (this.QuestionType_Code == '553'){	 				

						var item_3 = this.store.getAt(3);
						var item_4 = this.store.getAt(4);
						this.store.remove(item_3);
						this.store.remove(item_4);

						item_3.data.DPM_Name = "III и наличие реверсного потока";					
						console.log(item_3);
						console.log(item_4);

						this.store.add(item_3);
						this.store.add(item_4);	
					}
					
				}				
				
				
 			}
 		},		
		settings: null,

		getLabelEl: function() {
			if (this.el && this.el.parent('.x-form-item')) {
				return this.el.parent('.x-form-item').child('.x-form-item-label');
			}
			return null;
		},

		setLabelWidth: function(labelWidth) {
			this.labelWidth = Number(labelWidth);
			if (this.getLabelEl()) {
				this.getLabelEl().setWidth(this.labelWidth+3);
				this.container.setStyle('padding-left', (this.labelWidth+3)+'px');
			}
		},

		setFieldLabel: function(text) {
			if (this.rendered) {
				var labelSeparator = this.labelSeparator?this.labelSeparator:':';
				this.el.up('.x-form-item', 10, true).child('.x-form-item-label').update(text+labelSeparator);
			}
			this.fieldLabel = text;
		},
		//gaf #106655 1042018
		setWidth: function(text) {			
			this.width = text;
		},
		settEditable: function(val) {
            this.editable = val;
        },
		initComponent: function() {
			if (this.settings) {
				this.comboSubject = this.settings.AnswerClass_SysNick;
			}

			sw.Promed.QuestionType.CommonSprCombo.superclass.initComponent.apply(this, arguments);

			this.addListener('render', function(field) {
				if (this.labelWidth) {
					this.setLabelWidth(this.labelWidth);
				}
				if (this.hidden) {
					this.hideContainer();
				}
				if (this.ownerCt && this.ownerCt.id == 'qt-inrow-element-owner-'+this.settings.QuestionType_id) {
					var width = this.getSize().width+(this.labelWidth?this.labelWidth:0);
					this.ownerCt.setWidth(width);
				}
			});

			//для Анкеты gaf
			if (Ext.getCmp(this.id).settings.DispClass_id == 14){			
				//gaf 09022018 в режиме fullanketa 
				this.addListener('change', function (field, newVal, oldVal) {
					if (newVal.toString() != "") {
						setDisabledPregnancy(field.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], false);
					} else {
						//проверка есть ли выбранные элементы
						if (!hasSelectedElementPregnancy(field)) {
							setDisabledPregnancy(field.ownerCt.ownerCt.ownerCt.items.items[1].items.items[1].items.items[0], true);
						}
					}
				});
			}


			//Ufa, gaf #104945
			if (this.settings.QuestionType_Code == 398) {
				this.addListener('select', function (combo, record, index) {
					var parent = combo.el.parent('.x-fieldset-body');
					var childfieldset = parent.dom.children;
					var oobj = Ext.getCmp(childfieldset[1].id);
					if (index > 0) {
						oobj.show();
					} else {
						oobj.hide();
					}
				});
			}
			if (this.settings.QuestionType_Code == 400) {
				this.addListener('select', function (combo, record, index) {
					var parent = combo.el.parent('.x-fieldset-body');
					var childfieldset = parent.dom.children;
					var oobj = Ext.getCmp(childfieldset[4].id);
					if (index > 0) {
						oobj.show();
					} else {
						oobj.hide();
					}
				});
			}
			
			//Ufa, gaf #106655 06042018
			if (this.settings.QuestionType_Code == 661) {
				this.addListener('select', function (combo, record, index) {								
					switch(index) {
						case 1:							
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[13].show();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[14].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[15].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[16].hide();
							
							var array_663 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_663");
							Ext.getCmp(array_663[0].id).setValue("");
							var array_664 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_664");
							Ext.getCmp(array_664[0].id).setValue("");
							var array_665 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_665");
							Ext.getCmp(array_665[0].id).setValue("");							
							
						break;
						case 2:							
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[13].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[14].show();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[15].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[16].hide();
							
							var array_662 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_662");
							Ext.getCmp(array_662[0].id).setValue("");
							var array_664 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_664");
							Ext.getCmp(array_664[0].id).setValue("");
							var array_665 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_665");
							Ext.getCmp(array_665[0].id).setValue("");																
						break;
						case 3:							
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[13].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[14].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[15].show();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[16].hide();
							var array_663 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_663");
							Ext.getCmp(array_663[0].id).setValue("");
							var array_662 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_662");
							Ext.getCmp(array_662[0].id).setValue("");
							var array_665 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_665");
							Ext.getCmp(array_665[0].id).setValue("");														
						break;
						case 4:							
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[13].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[14].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[15].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[16].show();
							var array_663 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_663");
							Ext.getCmp(array_663[0].id).setValue("");
							var array_664 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_664");
							Ext.getCmp(array_664[0].id).setValue("");
							var array_662 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_662");
							Ext.getCmp(array_662[0].id).setValue("");														
						break;					
						default:
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[13].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[14].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[15].hide();
							Ext.getCmp(combo.id).ownerCt.ownerCt.items.items[16].hide();		
							var array_662 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_662");
							Ext.getCmp(array_662[0].id).setValue("");																					
							var array_663 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_663");
							Ext.getCmp(array_663[0].id).setValue("");
							var array_664 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_664");
							Ext.getCmp(array_664[0].id).setValue("");
							var array_665 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_665");
							Ext.getCmp(array_665[0].id).setValue("");														
						break;
					}
				});
			}
			
			//Ufa, gaf #136376
			if (this.settings.QuestionType_Code == 416) {
				this.addListener('select', function (combo, record, index) {				
					var parent = combo.el.parent('.x-column-inner');
					var childfieldset = parent.dom.children;
					var oobj = Ext.getCmp(childfieldset[1].id);
					if (index > 0) {				
						Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_753').el.setStyle('background', '#cfc');
						oobj.show();
					} else {
						var array_753 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_753");
						Ext.getCmp(array_753[0].id).setValue("");
						oobj.hide();
					}
				});				
			}
			if (this.settings.QuestionType_Code == 753) {
				this.addListener('select', function (field, newVal, oldVal) {																			
					if (oldVal != "0") {
						Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_753').el.setStyle('background', '#fff');
					} else {
						Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[1].getForm().findField('QuestionType_753').el.setStyle('background', '#cfc');
					}
				});																	
			}			

			if (this.settings) {
				this.initSettings(this.settings);

				if (this.settings.container) {
					//this.settings.container.comboForLoad.push(this);
				}
			}
		},

		initSettings: function(settings) {
			var element = this;

			element.settings = settings;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.hiddenName = 'QuestionType_'+settings.QuestionType_id;

			element.hiddenName = settings.container.getNameFromMap(settings);

			var defaultSettings = {
				fieldLabel: settings.QuestionType_Name,
				width: 121,
				labelWidth: 100,
				hidden: false,
                editable: false
			};
			//gaf 27112017 для формирования наимиенования по регионам
			var region_id = getRegionNumber(); 
			
			var visionSettings = settings.container.getVisionSettings(settings) || {};
			//Ufa gaf 26012018 #106655
			if (visionSettings['cls' + region_id] != null) {
				element.itemCls = visionSettings['cls' + region_id];
			}
			if (visionSettings['cls'] != null && (typeof element.itemCls == 'undefined')) {
				element.itemCls = visionSettings['cls'];
			}
			for(name in defaultSettings) {
				var settingValue = visionSettings[name] || defaultSettings[name];
				//gaf 27112017
				settingValue = visionSettings[name+region_id] || settingValue;
				switch(name) {
					case 'fieldLabel':
						element.setFieldLabel(settingValue);
						break;
					case 'width':
						element.anchor = undefined;
						element.width = Number(settingValue);
						element.setWidth(element.width);
						break;
					case 'labelWidth':
						element.setLabelWidth(settingValue);
						break;
					case 'hidden':
						element.hidden = settingValue;
						element.setContainerVisible(!settingValue);
						break;
					case 'editable':
						element.settEditable(settingValue);
						break;
				}
			}
		}
	});

	c.Panel = Ext.extend(Ext.Panel, {
		QuestionType_Code: null,
		settings: null,
		border: false,
		labelAlign: 'right',

		initComponent: function() {
			sw.Promed.QuestionType.Panel.superclass.initComponent.apply(this, arguments);
			if (this.settings) {
				this.initSettings(this.settings);
			}
		},

		initSettings: function(settings) {
			var element = this;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.settings = settings;
			element.removeAll();

			if (Ext.isArray(settings.itemsBefore)) {
				settings.itemsBefore.forEach(function(item){
					element.add(item);
				});
			}

			sw.Promed.QuestionType.createChildren(element);

			if (Ext.isArray(settings.itemsAfter)) {
				settings.itemsAfter.forEach(function(item){
					element.add(item);
				});
			}
		}
	});

	c.FieldSet = Ext.extend(Ext.form.FieldSet, {
		autoHeight: true,
		style: 'padding: 5px 10px',
		labelAlign: 'right',
		title: 'FieldSetName',	//Заглушка, иначе не отображается после setTitle
		QuestionType_Code: null,
		settings: null,
		initComponent: function() {
			sw.Promed.QuestionType.FieldSet.superclass.initComponent.apply(this, arguments);
			if (this.settings) {
				this.initSettings(this.settings);
			}

			this.addListener('bodyresize', function(fieldset) {
				var autoSyncWidth = false;
				if (fieldset.settings && fieldset.settings.container) {
					autoSyncWidth = fieldset.settings.container.autoSyncWidth;
				}
				if (autoSyncWidth) {
					var width = fieldset.getSize().width - 22;
					fieldset.items.each(function(item) {
						if (item instanceof Ext.Panel) {
							item.setWidth(width);
							item.doLayout();
						}
					});
				}
			});
		},

		initSettings: function(settings) {
			var element = this;

			element.QuestionType_Code = settings.QuestionType_Code;
			element.settings = settings;
			element.removeAll();

			var defaultSettings = {
				title: settings.QuestionType_Name,
				hidden: false
			};
			var visionSettings = settings.container.getVisionSettings(settings) || {};
			for(name in defaultSettings) {
				var settingValue = visionSettings[name] || defaultSettings[name];
				switch(name) {
					case 'title':
						//Ufa gaf 16012018 #106655
						if (settingValue == '*') {
							element.setTitle('');
							element.style = 'padding: 5px 10px; border-width: 0px;';
						} else {
							element.setTitle(settingValue);
						}
						break;
					case 'hidden':
						element.setVisible(!settingValue);
						break;
				}
			}

			if (Ext.isArray(settings.itemsBefore)) {
				settings.itemsBefore.forEach(function(item){
					element.add(item);
				});
			}

			sw.Promed.QuestionType.createChildren(element);

			if (Ext.isArray(settings.itemsAfter)) {
				settings.itemsAfter.forEach(function(item){
					element.add(item);
				});
			}
		}
	});

	//Ufa gaf 17012018 #106655
	c.Label = Ext.extend(Ext.form.Label, {
		QuestionType_Code: null,
		settings: null,
		cls: 'x-checkbox-blue-16',
		setText: function (text) {
			this.text = text;
		},
		initComponent: function () {
			sw.Promed.QuestionType.NumberField.superclass.initComponent.apply(this, arguments);
			if (this.settings) {
				this.initSettings(this.settings);
			}
		},
		initSettings: function (settings) {
			var element = this;
			element.settings = settings;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.name = 'QuestionType_' + settings.QuestionType_id;
			var region_id = getRegionNumber();
			var visionSettings = settings.container.getVisionSettings(settings) || {};
			settingValue = visionSettings['fieldLabel' + region_id] || element.settings.QuestionType_Name;
			element.setText(settingValue);
		}
	});

	//19012018 gaf
	c.Button = Ext.extend(Ext.Button, {
		QuestionType_Code: null,
		settings: null,
		//nameable: true,
		cls: 'switchButton',
		iconCls: '',
		icon: '',
		handler: function ()
		{
			this.toggle(true);

			var oobj = Ext.getCmp(this.id);
			var parent = this.el.parent('.x-fieldset-body');
			var childfieldset = parent.dom.children;			
			if (this.QuestionType_Code == '623') {
				//присутствуют для группы сведения о беременности, включает подгруппы
				Ext.getCmp(childfieldset[5].id).show();
				//Ext.getCmp(childfieldset[6].id).items.items[1].items.items[0].items.items[0].setDisabled(false);
				//Ext.getCmp(childfieldset[6].id).items.items[1].items.items[1].items.items[0].setDisabled(false);

				Ext.getCmp(childfieldset[6].id).show();
				//Ext.getCmp(childfieldset[7].id).items.items[1].items.items[0].items.items[0].setDisabled(false);
				//Ext.getCmp(childfieldset[7].id).items.items[1].items.items[1].items.items[0].setDisabled(false);
				//Ext.getCmp(childfieldset[5].id).show();
				//Ext.getCmp(childfieldset[8].id).items.items[1].items.items[0].items.items[0].setDisabled(false);
				//Ext.getCmp(childfieldset[8].id).items.items[1].items.items[1].items.items[0].setDisabled(false);
				Ext.getCmp(childfieldset[7].id).ownerCt.ownerCt.ownerCt.doLayout();

				if (getGlobalOptions().check_fullpregnancyanketa_allow && getGlobalOptions().check_fullpregnancyanketa_allow == '1') {
					var oobjnow = Ext.getCmp(childfieldset[1].id);
					var mainparent = oobjnow.ownerCt.ownerCt.ownerCt;
					mainparent = oobjnow.ownerCt.ownerCt.ownerCt.items.items[0];
					mainparent.ownerCt.ownerCt.ownerCt.ownerCt.NextButton.setDisabled(true);
				}
			}

			if (this.QuestionType_Code == '643') {
				//Нажатиена кнопку Отсутствуют
				//Скрываем 3 Fieldset, предварительно нажимаем в них кнпоку Отсутствуют и убираем признак нажатия кнопок (Присутствуют,Отсутствуют)
				var anketacategory = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.getCategory("Anketa");

				var btnyes = anketacategory.findById("Button_625");
				var btnno = anketacategory.findById("Button_645");
				if (btnyes) {
					btnyes.toggle(false);
				}
				if (btnno){
					btnno.toggle(false);
				}
				if (btnno){
					btnno.fireEvent('click', btnno);
				}
				if (getGlobalOptions().check_fullpregnancyanketa_allow && getGlobalOptions().check_fullpregnancyanketa_allow == '1') {
					if (btnyes){
						btnyes.setDisabled(true);
					}
					if (btnno){
						btnno.setDisabled(true);
					}
				}

				var fieldset  = btnno.findParentBy(function(p){
					return p.bbarCls == 'x-fieldset-bbar';
				});
				if (fieldset){
					fieldset.setVisible(false);
				}

				btnyes = anketacategory.findById("Button_626");
				btnno = anketacategory.findById("Button_646");
				if (btnyes) {
					btnyes.toggle(false);
				}
				if (btnno){
					btnno.toggle(false);
				}
				if (btnno){
					btnno.fireEvent('click', btnno);
				}
				if (getGlobalOptions().check_fullpregnancyanketa_allow && getGlobalOptions().check_fullpregnancyanketa_allow == '1') {
					if (btnyes){
						btnyes.setDisabled(true);
					}
					if (btnno){
						btnno.setDisabled(true);
					}
				}
				var fieldset  = btnno.findParentBy(function(p){
					return p.bbarCls == 'x-fieldset-bbar';
				});
				if (fieldset){
					fieldset.setVisible(false);
				}

				btnyes = anketacategory.findById("Button_624");
				btnno = anketacategory.findById("Button_644");
				if (btnyes) {
					btnyes.toggle(false);
				}
				if (btnno){
					btnno.toggle(false);
				}
				if (btnno){
					btnno.fireEvent('click', btnno);
				}
				if (getGlobalOptions().check_fullpregnancyanketa_allow && getGlobalOptions().check_fullpregnancyanketa_allow == '1') {
					if (btnyes){
						btnyes.setDisabled(true);
					}
					if (btnno){
						btnno.setDisabled(true);
					}
				}
				var fieldset  = btnno.findParentBy(function(p){
					return p.bbarCls == 'x-fieldset-bbar';
				});
				if (fieldset){
					fieldset.setVisible(false);
				}
			}
		},
		setId: function (text) {
			this.id = text;
		},
		setText: function (text) {
			this.text = text;
		},
		setCls: function (text) {
			this.cls = text;
		},
		initComponent: function () {
			sw.Promed.QuestionType.Button.superclass.initComponent.apply(this, arguments);
			if (this.settings) {
				this.initSettings(this.settings);
			}
		},
		initSettings: function (settings) {
			var element = this;
			element.settings = settings;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.name = 'QuestionType_' + settings.QuestionType_id;
			//test
			element.setId('Button_' + settings.QuestionType_id);
			//element.setName(element.name);
			element.setText(element.settings.QuestionType_Name);
			switch (element.settings.QuestionType_Name) {
				case 'Присутствуют':
				case 'Присутствует&nbsp;':
				case '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Выявлены&nbsp;&nbsp;&nbsp;':
				case '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Выявлено&nbsp;&nbsp;&nbsp;':
					element.setCls('switchButton yes-btn-cls');
					break;
				case 'Отсутствуют':
				case 'Отсутствует':
				case 'Не выявлены':
				case 'Не выявлено':
					element.setCls('switchButton no-btn-cls');
					break;
				case 'Случаи ЭКО':
					element.setCls('btneco btn-cls');
					break;					
				default:
					element.setCls('');
					break;
			}
			
			if (element.settings.QuestionType_Name == 'Случаи ЭКО'){			
				this.addListener('click', function (event, target){
					getWnd('swSelectEco').show();
				});
			}else{			
				this.addListener('click', function (event, target) {
					var ready = false;
					//Находим родителя
					var parent = this.el.parent('.x-fieldset-body');
					var childfieldset = parent.dom.children;
					var oobjnow = Ext.getCmp(childfieldset[1].id);

					//пробегаем по детям, 1 - это сами кнопки
					for (var i = 2; i < childfieldset.length; i++) {
						ready = true;
						if (event.text == 'Присутствуют' || event.text == '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Выявлены&nbsp;&nbsp;&nbsp;' || event.text == 'Присутствует&nbsp;' || event.text == '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Выявлено&nbsp;&nbsp;&nbsp;') {

							//снимаем нажатие с кнопки Не присутствует
							oobjnow.items.items[1].items.items[0].toggle(false);						

							//testjquery
							//$("#" + childfieldset[i].id).show();
							Ext.getCmp(childfieldset[i].id).show();

							//скрываем при раскрытии панели текстовые поля: Смерть в неонатальном периоде: Другое, Рубец на матке 13112017
							////testjquery

							//27032018
							if (Ext.getCmp(childfieldset[i].id).QuestionType_Code == 185 || Ext.getCmp(childfieldset[i].id).QuestionType_Code == 197){
								Ext.getCmp(childfieldset[i].id).items.items[1].hide();
							}					

							if (getRegionNumber() == 2){
								if (oobjnow.ownerCt.QuestionType_Code == "207" && i == 8) {
									Ext.getCmp(childfieldset[i].id).ownerCt.items.items[8].hide();
									Ext.getCmp(childfieldset[i].id).ownerCt.items.items[8].items.items[0].items.items[0].clearValue();
									Ext.getCmp(childfieldset[i].id).ownerCt.items.items[8].items.items[1].items.items[0].clearValue();
								}
							}else if (getRegionNumber() == 30){						
								if (oobjnow.ownerCt.QuestionType_Code == "207" && i == 6) {
									Ext.getCmp(childfieldset[i].id).ownerCt.items.items[6].hide();
									Ext.getCmp(childfieldset[i].id).ownerCt.items.items[6].items.items[0].items.items[0].clearValue();
									Ext.getCmp(childfieldset[i].id).ownerCt.items.items[6].items.items[1].items.items[0].clearValue();
								}
							}					

						} else if (event.text == 'Отсутствуют' || event.text == 'Не выявлены' || event.text == 'Отсутствует' || event.text == 'Не выявлено') {
							if (childfieldset[i].localName != "fieldset") {

								//22032018 testjquery
								//$("#" + childfieldset[i].id).hide();
								var oobj = Ext.getCmp(childfieldset[i].id);
								oobj.hide();
								oobjnow.items.items[0].items.items[0].toggle(false);

								if (typeof oobj != 'undefined' &&
										typeof oobj.items != 'undefined' &&
										typeof oobj.items.items[0] != 'undefined') {
									if (typeof oobj.items.items[0].checked != 'undefined') {
										oobj.items.items[0].setValue(false);
									} else {

										if (typeof oobj.items.items[0].clearValue == "function") {
											oobj.items.items[0].clearValue();
										}
									}
								}

								for (var j = 0; j < oobj.items.items.length; j++) {
									if (typeof oobj != 'undefined' &&
											typeof oobj.items != 'undefined' &&
											typeof oobj.items.items[j] != 'undefined' &&
											typeof oobj.items.items[j].items != 'undefined' &&
											typeof oobj.items.items[j].items.items[0] != 'undefined'
											) {
										if (typeof oobj.items.items[j].items.items[0].checked != 'undefined') {
											oobj.items.items[j].items.items[0].setValue(false);
										} else if (typeof oobj.items.items[j].items.items[0].clearValue == "function") {
											oobj.items.items[j].items.items[0].clearValue();
										} else if (typeof oobj.items.items[j].items.items[0].value != "") {
											//для элемента кнпоки выбов ЭКО
											if (typeof oobj.items.items[j].items.items[0].value != 'undefined'){
												oobj.items.items[j].items.items[0].setValue("")
											}
										}
									}
								}
							}
						}
					}

					//gaf 08022018 в режиме fullanketa #106655
					if (event.text == 'Присутствуют' || event.text == '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Выявлены&nbsp;&nbsp;&nbsp;' || event.text == 'Присутствует&nbsp;' || event.text == '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Выявлено&nbsp;&nbsp;&nbsp;') {
						setDisabledPregnancy(this, true);
					}
					if (event.text == 'Отсутствуют' || event.text == 'Не выявлены' || event.text == 'Отсутствует' || event.text == 'Не выявлено') {
						setDisabledPregnancy(this, false);
					}
				});						
			}			
		}
	});

	c.RadioGroup = Ext.extend(Ext.form.RadioGroup, {
		QuestionType_Code: null,
		settings: null,
		// setText: function (text) {
		// 	this.text = text;
		// },
		initComponent: function () {
			sw.Promed.QuestionType.RadioGroup.superclass.initComponent.apply(this, arguments);
			if (this.settings) {
				this.initSettings(this.settings);
			}
		},
		initSettings: function (settings) {
			var element = this;
			element.settings = settings;
			element.QuestionType_Code = settings.QuestionType_Code;
			element.name = 'QuestionType_' + settings.QuestionType_id;
			// var region_id = getRegionNumber();
			// var visionSettings = settings.container.getVisionSettings(settings) || {};
			// settingValue = visionSettings['fieldLabel' + region_id] || element.settings.QuestionType_Name;

			// element.labelWidth =10;
			element.items= [{
				boxLabel: 'До начала родовой деятельности',
				name: 'QuestionType_' + settings.QuestionType_id,
				inputValue: '1',
				width: 220
				}, {
				boxLabel: 'После начала родовой деятельности',
				name: 'QuestionType_' + settings.QuestionType_id,
				inputValue: '2',
				width: 280
			}];
		}
	});

	c.createChildren = function(element) {
		var settings = element.settings;
		var tables = [];
		var inrows = [];
		if (Ext.isArray(settings.childrenVision)) {
			settings.childrenVision.forEach(function(vision){
				if (vision.RecordStatus_Code == 3) return;
				var visionSettings = vision.settings;
				var CodeList = [];
				if (Ext.isArray(visionSettings.ExceptCodeList)) {
					settings.children.forEach(function(childSettings){
						if (!childSettings.QuestionType_Code.inlist(visionSettings.ExceptCodeList)) {
							CodeList.push(childSettings.QuestionType_Code);
						}
					});
				} else if (Ext.isArray(visionSettings.CodeList)) {
					settings.children.forEach(function(childSettings){
						if (childSettings.QuestionType_Code.inlist(visionSettings.CodeList)) {
							CodeList.push(childSettings.QuestionType_Code);
						}
					});
				} else {
					settings.children.forEach(function(childSettings){
						CodeList.push(childSettings.QuestionType_Code);
					});
				}

				if (Ext.isArray(visionSettings.columns)) {
					var elementCount = CodeList.length;
					var columnCount = visionSettings.columns.length;
					var rowCount = Math.ceil(elementCount/columnCount);
					var width = [];
					//Ufa gaf 24012018 #106655
					var paddingtop = [];
					var paddingbottom = [];	
					
					visionSettings.columns.forEach(function(column) {
						width.push(Number(column.width));
						//Ufa gaf 24012018 #106655
						if (typeof column.paddingtop != 'undefined') {
							paddingtop.push(Number(column.paddingtop));
						} else {
							paddingtop.push(0);
						}
						if (typeof column.paddingbottom != 'undefined') {
							paddingbottom.push(Number(column.paddingbottom));
						} else {
							paddingbottom.push(0);
						}
					});
					tables.push({
						codeList: CodeList,
						currentIndex: 0,
						currentGIndex: 0,
						columnCount: columnCount,
						width: width,
						//Ufa gaf 24012018 #106655
						paddingtop: paddingtop,
						paddingbottom: paddingbottom
					});
				} else if (visionSettings.inRow) {
					inrows.push({
						codeList: CodeList,
						spaceWidth: visionSettings.inRow.spaceWidth
					});
				}
			});
		}
		if (Ext.isArray(settings.children)) {
			var children = [];
			settings.children.sort(function(a, b) {
				var avs = settings.container.getVisionSettings(a);
				var bvs = settings.container.getVisionSettings(b);
				if (avs && avs.index && bvs && bvs.index) return (Number(avs.index) < Number(bvs.index))?-1:1;
				if (avs && avs.index) return 1;
				if (bvs && bvs.index) return -1;
				return (Number(a.QuestionType_Code) < Number(b.QuestionType_Code))?-1:1;
			});
			settings.children.forEach(function(childSettings, index, arr) {
				var visionSettings = settings.container.getVisionSettings(childSettings);
				var position = (visionSettings && visionSettings.index)?visionSettings.index-1:children.length;
				children.splice(position, 0, childSettings);
			});
			settings.children = children;

			var column = null;
			settings.children.forEach(function(childSettings) {
				childSettings.container = settings.container;
				childSettings.parent = element;
				var childElement = sw.Promed.QuestionType.createElement(childSettings);
				if (childElement) {
					var visionSettings = childSettings.container.getVisionSettings(childSettings);
					var flag = true;
					if (tables.length > 0) {
						tables.forEach(function(table, tableIndex) {
							var index = table.codeList.indexOf(childSettings.QuestionType_Code);
							if (index < 0) return true;
							flag = false;
							if (!column) {
								column = new Ext.Panel({
									layout: 'column',
									labelAlign: 'right',
									border: false
								});
								element.add(column);
							}
							if (childElement instanceof sw.Promed.QuestionType.FieldSet) {
								if (table.currentIndex+1 < table.columnCount) {
									//Ufa gaf 16012018 #106655
									if (typeof childElement.settings.vision == 'object' && childElement.settings.vision[0].settings['title'] == '*'){
										childElement.style = "padding: 5px 10px; margin-right: 5px; border-width: 0px;";
									}else{
										childElement.style = "padding: 5px 10px; margin-right: 5px;";
									}
								}
								childElement.setWidth(table.width[table.currentIndex]);
								column.add(childElement);
							} else {
								var config = {
									layout: 'form',
									border: false,
									width: table.width[table.currentIndex],
									items: [childElement],
									//Ufa gaf 16012018 #106655
									bodyStyle: 'padding-top: '+table.paddingtop[table.currentIndex]+'px; padding-bottom: '+table.paddingbottom[table.currentIndex]+'px'
									
								};
								if (visionSettings && visionSettings.labelWidth) {
									config.labelWidth = visionSettings.labelWidth;
								}
								//gaf 06042018 ufa labelWidth + код региона
								if (visionSettings && visionSettings.labelWidth2) {
									config.labelWidth = visionSettings.labelWidth2;
								}
								column.add(new Ext.Panel(config));
							}
							table.currentIndex++;
							table.currentGIndex++;
							if (table.currentIndex >= table.columnCount) {
								column = null;
								table.currentIndex = 0;
							}
							if (table.currentGIndex >= table.codeList.length) {
								column = null;
								table.currentIndex = 0;
								table.currentGIndex = 0;
							}
							return false;
						});
					}
					if (inrows.length > 0) {
						inrows.forEach(function(inrow) {
							var index = inrow.codeList.indexOf(childSettings.QuestionType_Code);
							if (index < 0) return true;
							flag = false;

							if (!inrow.row) {
								inrow.row = new Ext.Panel({
									id: 'qt-inrow-element-owner-'+childSettings.QuestionType_id,
									layout: 'column',
									labelAlign: 'right',
									border: false
								});
								element.add(inrow.row);
							}

							var config = {
								layout: 'form',
								border: false,
								width: Number(childElement.width)+Number(childElement.labelWidth)+11,
								items: [childElement]
							};
							if (visionSettings && visionSettings.labelWidth) {
								config.labelWidth = visionSettings.labelWidth;
							}
							// Скриннинг блокировка параметров, учитываем сортировку, Ufa, gaf #104947
							//удалено с новой задачей
							//if (visionSettings && visionSettings.index) {
							//	index = visionSettings.index-1;
							//}							
							if (index < inrow.codeList.length-1) {
								var spaceWidth = inrow.spaceWidth || 15;
								config.style = 'margin-right: ' + spaceWidth + 'px;';
							}

							inrow.row.add(config);
							return false;
						});
					}
					if (flag) {
						if (
							childElement instanceof sw.Promed.QuestionType.Checkbox
							|| childElement instanceof sw.Promed.QuestionType.NumberField
							|| childElement instanceof sw.Promed.QuestionType.TextField
							|| childElement instanceof sw.Promed.QuestionType.LpuCombo
							|| childElement instanceof sw.Promed.QuestionType.DiagCombo
							|| childElement instanceof sw.Promed.QuestionType.CommonSprCombo
							|| childElement instanceof sw.Promed.QuestionType.TextArea
						) {
							var config = {
								layout: 'form',
								border: false,
								items: [childElement]
							};
							if (visionSettings && visionSettings.labelWidth) {
								config.labelWidth = visionSettings.labelWidth;
							}
							element.add(config);
						} else {
							element.add(childElement);
						}
					}
				}
			});
		}
	};

	c.createElement = function(settings) {
		var element = null;
		if (settings.root) {
			element = new ElementRoot({settings: settings});
			return element;
		}
		var visionSettings = settings.container.getVisionSettings(settings) || {};
		if (visionSettings.FieldType) {
			switch(visionSettings.FieldType) {
				case 'TextField':
					element = new sw.Promed.QuestionType.TextField({settings: settings});
					break;
				case 'TextArea':
					element = new sw.Promed.QuestionType.TextArea({settings: settings});
					break;
				case 'OtherTextArea':
					element = new sw.Promed.QuestionType.OtherTextArea({settings: settings});
					break;
			}
			return element;
		}
		switch(Number(settings.AnswerType_id)) {
			case 0:
				element = new sw.Promed.QuestionType.FieldSet({settings: settings});
				break;
			case 1:
				element = new sw.Promed.QuestionType.Checkbox({settings: settings});
				//test gaf
				//element.id = settings.QuestionType_id;
				//console.log('element-A');
				//console.log(element);
				break;
			case 2:
				element = new sw.Promed.QuestionType.TextField({settings: settings});
				break;
			case 3:
			case 4:
				switch(settings.AnswerClass_SysNick) {
					case 'Lpu':
						element = new sw.Promed.QuestionType.LpuCombo({settings: settings});
						break;
					case 'Diag':
						element = new sw.Promed.QuestionType.DiagCombo({settings: settings});
						break;
					case 'LpuDifferent':
						element = new sw.Promed.QuestionType.LpuDifferent({settings: settings});
						break;
					default:
						element = new sw.Promed.QuestionType.CommonSprCombo({settings: settings});
				}
				break;
			case 13:
				element = new sw.Promed.QuestionType.DecNumberField({settings: settings});
				break;
			case 5:
				element = new sw.Promed.QuestionType.NumberField({settings: settings});
				break;
			case 6:
				//Ufa gaf 16012018 #106655
				element = new sw.Promed.QuestionType.Label({settings: settings});
				break;
			case 7:
				//Ufa gaf 16012018 #106655
				element = new sw.Promed.QuestionType.Button({settings: settings});
				break;
			case 8:
				//Ufa gaf 24102019 #164476
				element = new sw.Promed.QuestionType.RadioGroup({settings: settings});
				break;
		}
		return element;
	};

	var ElementContainer = Ext.extend(Ext.Panel, {
		cls: 'qt-container',
		layout: 'fit',
		border: false,
		labelAlign: 'right',
		QuestionType_Code: null,
		factory: null,
		root: false,
		type: null,
		autoSyncWidth: true,
		itemsBefore: [],
		itemsAfter: [],
		nameMap: {},

		getVision: function(settings) {
			var commonVision = sw.Promed.QuestionType.getVision(settings, null);
			var regionVision = sw.Promed.QuestionType.getVision(settings, (this.factory||{}).Region_id);
			return regionVision || commonVision || null;
		},

		getVisionSettings: function(settings) {
			var commonVisionSettings = sw.Promed.QuestionType.getVisionSettings(settings, null);
			var regionVisionSettings = sw.Promed.QuestionType.getVisionSettings(settings, (this.factory||{}).Region_id);
			if (!commonVisionSettings && !regionVisionSettings) return null;
			return Ext.apply({}, regionVisionSettings, commonVisionSettings);
		},

		getNameFromMap: function(settings) {
			var name = 'QuestionType_'+settings.QuestionType_id;
			if (!Ext.isEmpty(settings.QuestionType_SysNick) && this.nameMap[settings.QuestionType_SysNick]) {
				name = this.nameMap[settings.QuestionType_SysNick];
			}
			return name;
		},

		getFields: function() {
			var fields = [];
			function getRecursiveFields(o) {
				if ((typeof o == 'object') && o.items && o.items.items) {
					o = o.items.items;
				}
				if (o && o.length && o.length>0) {
					for (var i = 0, len = o.length; i < len; i++) {
						if (o[i])
							if ((o[i].xtype && (o[i].xtype=='fieldset' || o[i].xtype=='panel' || o[i].xtype=='tabpanel')) || (o[i].layout)) {
								getRecursiveFields(o[i]);
							}
						if (typeof o[i].getTopToolbar != 'function') {
							fields.push(o[i]);
						}
					}
				}
			}
			getRecursiveFields(this);
			return fields;
		},

		enableEdit: function(enable) {
			this.getFields().forEach(function(field) {
				field.setDisabled(!enable);
			});
		},

		initComponent: function() {
			this.comboForLoad = [];

			ElementContainer.superclass.initComponent.apply(this, arguments);
		}
	});

	var ElementRoot = Ext.extend(Ext.Panel, {
		cls: 'qt-element-root',
		settings: null,
		labelAlign: 'right',
		border: false,

		resizeChildren: function() {
			var width = this.getSize().width;
			var height = 0;
			this.items.each(function(item) {
				if (item instanceof Ext.Panel) {
					item.setWidth(width);

					var el = item.getEl();
					var margins = el.getMargins();
					height += el.getHeight() + margins.top + margins.bottom;
				}
			});
			if (this.settings && this.settings.container) {
				this.settings.container.setHeight(height);
			}
		},

		initComponent: function() {
			ElementRoot.superclass.initComponent.apply(this, arguments);

			this.addListener('afterlayout', function(panel) {
				setTimeout(function(){panel.resizeChildren()}, 1);
			});

			if (this.settings) {
				this.initSettings(this.settings);
			}
		},

		initSettings: function(settings) {
			var element = this;

			element.settings = settings;
			element.removeAll();

			if (Ext.isArray(settings.itemsBefore)) {
				settings.itemsBefore.forEach(function(item){
					element.add(item);
				});
			}

			sw.Promed.QuestionType.createChildren(element);

			if (Ext.isArray(settings.itemsAfter)) {
				settings.itemsAfter.forEach(function(item){
					element.add(item);
				});
			}
		}
	});

	c.Factory = function(config) {
		var factory = this;

		factory.loaded = false;
		factory.Region_id = getRegionNumber();
		factory.DispClass_id = null;
		factory.settingsTree = null;
		factory.settingsByCode = null;
		factory.containers = [];
		factory.nameMap = {};
		factory.readOnly = false;
		factory.loadComboCallback = Ext.emptyFn;

		if (typeof config == 'object') {
			if (config.DispClass_id) {
				factory.DispClass_id = config.DispClass_id;
			}
			if (config.nameMap) {
				factory.nameMap = config.nameMap;
			}
			if (typeof config.loadComboCallback == 'function') {
				factory.loadComboCallback = config.loadComboCallback;
			}
		}

		factory.setRegionId = function(region_id) {
			factory.Region_id = region_id;
		};

		factory.setDispClassId = function(DispClass_id) {
			factory.DispClass_id = DispClass_id;
		};

		factory.setReadOnly = function(readOnly) {
			factory.readOnly = readOnly;
			factory.containers.forEach(function(container) {
				container.enableEdit(!readOnly);
			});
		};

		factory.createContainer = function (containerConfig) {
			var params = {factory: factory};
			params.nameMap = Ext.apply({}, factory.nameMap);
			if (typeof containerConfig == 'object') {
				if (typeof containerConfig.root == 'boolean') {
					params.root = containerConfig.root;
				}
				if (containerConfig.QuestionType_Code) {
					params.QuestionType_Code = containerConfig.QuestionType_Code;
				}
				if (containerConfig.labelWidth) {
					params.labelWidth = containerConfig.labelWidth;
				}
				if (Ext.isArray(containerConfig.itemsBefore)) {
					params.itemsBefore = containerConfig.itemsBefore;
				}
				if (Ext.isArray(containerConfig.itemsAfter)) {
					params.itemsAfter = containerConfig.itemsAfter;
				}
				if (!Ext.isEmpty(containerConfig.type)) {
					params.type = containerConfig.type;
				}
				if (typeof containerConfig.autoSyncWidth == 'boolean') {
					params.autoSyncWidth = containerConfig.autoSyncWidth;
				}
				if (containerConfig.nameMap) {
					params.nameMap = Ext.apply(params.nameMap, containerConfig.nameMap);
				}
			}

			var container = new ElementContainer(params);
			factory.containers.push(container);
			return container;
		};

		factory.loadDataLists = function() {
			var comboboxes = [];
			factory.containers.forEach(function(container){
				container.getFields().forEach(function(field) {
					if (field.comboSubject) comboboxes.push(field);
				});
				comboboxes = comboboxes.concat(container.comboForLoad);
				container.comboForLoad = [];
			});
			if (comboboxes.length > 0) {
				loadStores(comboboxes, factory.loadComboCallback);
			}
		};

		factory.initSettings = function(allSettings) {
			if (!factory.DispClass_id) return;
			var settingsTree = {children: []};
			var settingsByCode = {};
			var settingsBySysNick = {};

			var tmp = {};
			allSettings.forEach(function(settings) {
				if (settings.DispClass_id != factory.DispClass_id) return;
				var id = settings.QuestionType_id;
				var pid = settings.QuestionType_pid;
				var code = settings.QuestionType_Code;
				var sysNick = settings.QuestionType_SysNick;

				settingsByCode['QuestionType_'+code] = settings;
				if (!Ext.isEmpty(sysNick)) {
					settingsBySysNick[sysNick] = settings;
				}

				if (settings.Level == 1) {
					settingsTree.root = true;
					settingsTree.children.push(settings);
				}
				if (pid) {
					if (!tmp[pid]) tmp[pid] = [];
					tmp[pid].push(settings);
				}
			});
			allSettings.forEach(function(settings) {
				if (settings.DispClass_id != factory.DispClass_id) return;
				var id = settings.QuestionType_id;
				var pid = settings.QuestionType_pid;
				if (tmp[id]) {
					settings.children = tmp[id];
				}
			});

			factory.settingsTree = settingsTree;
			factory.settingsByCode = settingsByCode;
			factory.settingsBySysNick = settingsBySysNick;

			var rootCount = 0;
			factory.containers.forEach(function(container) {
				var settings = null;
				var code = container.QuestionType_Code;
				if (container.root) {
					rootCount++;
					if (rootCount > 1) {
						warn('Не возможно создать больше одного корневого элемента анкеты');
						return;
					}
					settings = settingsTree;
				} else if(code && settingsByCode['QuestionType_'+code]) {
					settings = settingsByCode['QuestionType_'+code];
				}
				if (settings) {
					settings.container = container;
					settings.itemsBefore = container.itemsBefore;
					settings.itemsAfter = container.itemsAfter;
					var element = sw.Promed.QuestionType.createElement(settings)
					if (element) container.add(element);
				}
			});
		};

		factory.loadSettings = function(callback) {
			if (!factory.DispClass_id) return;
			callback = callback || Ext.emptyFn;

			factory.clearContainers();
			sw.Promed.QuestionType.loadSettings({
				DispClass_id: factory.DispClass_id
			}, function(settings) {
				factory.initSettings(settings);
				factory.setReadOnly(factory.readOnly);
				factory.loadDataLists();
				factory.loaded = true;
				callback();
			});
		};

		factory.clearContainers = function() {
			factory.loaded = false;
			factory.containers.forEach(function(container){
				container.removeAll();
			});
		}
	};

	return c;
}());