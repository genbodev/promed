/**
* sw.Promed.vac.utils - разные утилиты раздела Вакцинация
*
* PromedWeb - Вакцинация
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2012 Прогресс
* @author       
* @version      22.06.2012
*/

Ext.ns('sw.Promed.vac', 'sw.Promed.vac.cons');

//sw.Promed.vac.cons = {};
sw.Promed.vac.cons.formType = {
	VACCINE:      'VACCINE',
	MANTU:        'MANTU',
	VAC_REFUSE:   'VAC_REFUSE',
	VAC_AVAILABLE:'VAC_AVAILABLE'
};
sw.Promed.vac.cons.formName = {
	VACCINE: 'Прививка',
	MANTU:   'Манту',
//	VAC_REFUSE:    'Медотвод/отказ от прививки',
	VAC_REFUSE:    'Медицинские отводы, согласия и отказы',
	VAC_AVAILABLE: 'Наличие вакцин'
};
sw.Promed.vac.cons.actType = {
	IMPLEMENTING: 'IMPLEMENTING',
	PURPOSING: 'PURPOSING',
	PLANNING: 'PLANNING',
	DELETING: 'DELETING',
	ADDING: 'ADDING',
	EDITING: 'EDITING',
	VIEWING: 'VIEWING'
};
sw.Promed.vac.cons.actName = {
	IMPLEMENTING: 'Исполнение',
	PURPOSING: 'Назначение',
	PLANNING: 'Планирование',
	DELETING: 'Удаление',
	ADDING: 'Добавление',
	EDITING: 'Редактирование',
	VIEWING: 'Просмотр'
};

sw.Promed.vac.utils = function() {
  
  return {
    consoleLogEnable: true,
//    typeForms: function(){
//    },
//    
    /*
     * функция проверки наличия доступа (по ЛПУ)
     */
    lpuAccess: function(lpuId) {
			if (sw.Promed.vac.utils.settings.lpuAccessAll === '1') {return 1;} //Доступ для всех поликлиник
//      var lpuArr = [35, 27, 20, 3, 28, 16];
			//теперь берем из БД (таблица vac.settings) 
			var lpuArr = sw.Promed.vac.utils.settings.lpuAccess;
      for (var i = 0; i < lpuArr.length; i++) {
        if (lpuArr[i] == lpuId) {
//					alert('OK');
          return 1;
        }
      }
//			alert('NO');
      return 0;
    },
    
     /*
     * функция проверки наличия доступа в журнале анкетирования по онкоконтролю
     * по всем МО (по ЛПУ)
     */
    lpuAcceProfilesAllOnkoCtrl: function(lpuId) {
        //alert('lpuAcceProfilesAllOnkoCtrl');
    var lpuArr = sw.Promed.vac.utils.settings.lpuAcceProfilesAllOnkoCtrl;
      for (var i = 0; i < lpuArr.length; i++) {
        if (lpuArr[i] == lpuId) {
          return 1;
        }
      }
      return 0;
    },
        /*
         * функция проверки наличия полного доступа к справочникам вакцинации  (по юзеру)
         */
        vacSprAccesFull: function(pmuser_id) {
            var userArr = sw.Promed.vac.utils.settings.vacSprAccesFull;
//               alert('user = ' + userArr.length);
              for (var i = 0; i < userArr.length; i++) {
                sw.Promed.vac.utils.consoleLog('user = ' + userArr[i]);
                if (userArr[i] == pmuser_id) {
                  return 1;
                }
              }
          return 0;
        },

		//cmp - контекст накотором нах-ся кнопки
		resetButsDis: function(cmp){
//			alert('resetButsDis-utils');
			cmp.buttons.forEach(
				//активируем кнопки формы (могут остаться неактивными после нажатия кнопки)
				function(element, index){
//					alert(index);
					element.setDisabled(false);
				}
			);
		},
		
		/*
		 * Преобразование строчной даты в тип дата
		 * формат входной строки val: дд.мм.гг
		 * defaultVal - значение по умолчанию (если val не соотв-ет формату даты)
		 */
    strToDate: function(val, defaultVal) {
			var logMsg = [];
			logMsg.push('strToDate');
			if (val == undefined || val == '') {
				logMsg.push('Ошибка - пустое значение на входе!');
				sw.Promed.vac.utils.consoleLog(logMsg);
				return ((defaultVal != undefined) ? defaultVal : null);
			}
			//проверка на тип дата, если это "дата" - возвращать без изменения:
			var toClass = {}.toString;
			if (toClass.call(val).slice(8,-1) === 'Date') return val;
//			if (typeof(val) != 'string') return ((defaultVal != undefined) ? defaultVal : val);
      if (typeof(val) != 'string') {
				logMsg.push('Ошибка - Тип данных на входе должен быть "Строка" или "Дата"!');
				sw.Promed.vac.utils.consoleLog(logMsg);
				return ((defaultVal != undefined) ? defaultVal : null);
			}
			var arr = val.split('.');
			var dt = new Date(arr[2], arr[1]-1, arr[0]);
			return dt;
    },
		
		/*
		 * Функция добавления нужн кол-ва лет с преобразованием строки в дату
		 * val - строка формата: дд.мм.гг
		 * yearVal - кол-во лет (добавить)
		 */
    yearAdd: function(val, yearVal) {
//			alert(val);
			if (val == undefined || val == '') return null;
			var arr = val.split('.');
//			alert(arr[2]+'.'+arr[1]+'.'+arr[0]);
//			var dt = new Date(arr[2]+'.'+arr[1]+'.'+arr[0]);
			var dt = new Date(arr[2], arr[1]-1, arr[0]);
			dt.setFullYear(dt.getFullYear() + yearVal);
//			alert('dt: '+dt);
//			alert(dt.format('dd.mm.yyyy'));
//			return dt.format('dd.mm.yyyy');
			return dt;
    },
		
		/*
		 * Фабрика объектов валидации - данных и соотв-х правил
		 * o - объект описания типа запрашиваемого объекта
		 * o.fieldName - тип поля
		 * o.formId - ID формы
		 */
		getValidateObj: function (o) {
			
			switch(o.formId) {
				case 'vacImplEditForm': //форма исполнения прививки - дата исполнения
				case 'vacImplWithoutPurpEditForm': //форма исполнения прививки минуя назначение - дата исполнения
				case 'mantuImplWithoutPurpEditForm': //форма исполнения манту минуя назначение - дата исполнения
				case 'vacPurpEditForm': //форма назначения прививки - дата назначения
				case 'vacMantuEditForm': //форма назначения манту - дата назначения
				case 'vacQuikImplEditForm': //форма Исполнения прививки (ввод истории)
				case 'vacRefuseEditForm': //форма медотводов
					if (o.fieldName == 'vacImplementDate'
						|| o.fieldName == 'vacMantuDateImpl'
						|| o.fieldName == 'vacPurposeDate'
					  || o.fieldName == 'vacMantuDateAssign'
					  || o.fieldName == 'vacRefuseDate' // Дата решения о медотводе
				  ) {
						return {
							formId: '', //ID html объекта (формы)
							dataSrc: {}, //сборное хранилище данных, исп-мых для валидации
							rules: { //правила валидации (TODO - сделать из таблицы настроек)
								min: ['personBirthday', 'dateRangeBegin'],
//								minDt: [-1, -3], //поправки (точность) [дней]
                minDt: {//поправки (точность) [дней]
//									vacImplementDate: [0, 0],
									vacMantuDateImpl: [1, -3],
									vacMantuDateAssign: [20, 0]
//									,vacPurposeDate: [0, 0]
								},
								max: ['currentDate', 'dateRangeEnd'],
//								maxDt: [0, 0] //поправки (точность) [дней]
								maxDt: {//поправки (точность) [дней]
									vacPurposeDate: [10, 0],
									vacMantuDateAssign: [10, 0]
								}
							},

							/*
							 * инициализация объекта валидации
							 * входные параметры (необязательные):
							 * o.personBirthday - дата рождения пациента
							 * o.dateRangeBegin - дата начала разрешенного диапазона
							 * o.dateRangeEnd   - дата завершения разрешенного диапазона
							 */
							init: function(o){
								this.dataSrc.currentDate = new Date();
								if (o != undefined) {
									Ext.apply(this.dataSrc, o);
								}
							},
							
							/*
							 * возвращаем значение поправки Dt для соотв-го поля формы
							 * typeDt - тип поправок (minDt, maxDt)
							 */
							getDt: function(typeDt, i){
								if (this.rules[typeDt] == undefined) return 0;
								if (this.rules[typeDt][o.fieldName] == undefined) return 0;
								return this.rules[typeDt][o.fieldName][i];
							},
							
							/*
							 * вычисляем минимальную границу разрешенного диапазона
							 */
							getMinDate: function(){
//								debugger;
								var item;
//								var dataVal;
								var resultVal;
								for (var i=0; i<this.rules.min.length; i++) {
									item = this.rules.min[i];
									if (item == undefined) continue;
									item = this.dataSrc[item];
									if (item == undefined) continue;
//									if (typeof(item) == 'string')
									item = sw.Promed.vac.utils.strToDate(item);
//									item.setDate(item.getDate() + this.rules.minDt[i]);
									item.setDate(item.getDate() + this.getDt('minDt', i));
									if (resultVal == undefined) {
										resultVal = item;
									}	else {
										resultVal = (resultVal < item) ? item : resultVal;
									}
								}
								this.setMinDate(resultVal);
								return resultVal;
							},
							
							/*
							 * вычисляем максимальную границу разрешенного диапазона
							 */
							getMaxDate: function(){
								var item;
								var resultVal;
								for (var i=0; i<this.rules.max.length; i++) {
									item = this.rules.max[i];
									if (item == undefined) continue;
									item = this.dataSrc[item];
									if (item == undefined) continue;
									item = sw.Promed.vac.utils.strToDate(item);
//									item.setDate(item.getDate() + this.rules.maxDt[i]);
									item.setDate(item.getDate() + this.getDt('maxDt', i));
									if (resultVal == undefined) {
										resultVal = item;
									}	else {
										resultVal = (resultVal > item) ? item : resultVal;
									}
//									alert(resultVal);
								}
								this.setMaxDate(resultVal);
								return resultVal;
							},
							
							setMinDate: function(dt){
								var fl = Ext.getCmp(o.formId).getForm().findField(o.fieldName);
//								fl.setMinValue('05.04.2013');
								fl.setMinValue(dt);
							},
							
							setMaxDate: function(dt){
								var fl = Ext.getCmp(o.formId).getForm().findField(o.fieldName);
								fl.setMaxValue(dt);
							}
							
						}
					}
					break;
				default:
					break;
			}
			
		},
		
		/*
		 * Фабрика объектов - формирование заголовка форм с учетом режима ее открытия(действия)
		 */
		getFormTitleObj: function () {
			var titleMain = null;
			var titleAdd = null;
			var title = null;
			var formType = null;
			var modeType = null;
			return {
//				titleMain: null,
//				titleAdd: null,
//				title: null,
//				formType: null,
//				modeType: null,
				
				/*
				 * инициализация объекта именования формы
				 * входные параметры:
				 * obj.formType - тип формы (константа)
				 * obj.modeType - тип действия (константа)
				 */
				init: function(obj){
					formType = obj.formType || null;
					modeType = obj.modeType || null;
					this.setTitle();
					return this;
				},

				/*
				 * подготовка основного наименования формы
				 */
				setTitleMain: function(){
					switch(formType){
						case sw.Promed.vac.cons.formType.MANTU:
						case sw.Promed.vac.cons.formType.VACCINE:
						case sw.Promed.vac.cons.formType.VAC_AVAILABLE:
						case sw.Promed.vac.cons.formType.VAC_REFUSE:
							titleMain = sw.Promed.vac.cons.formName[formType];
							break;
						default:
							titleMain = '-';
							break;
					}
					return titleMain;
				},

				getTitleMain: function(){
					if (titleMain != undefined) return titleMain;
					return this.setTitleMain();
				},

				/*
				 * подготовка доп наименования формы
				 */
				setTitleAdd: function(){
					switch (modeType) {
						case sw.Promed.vac.cons.actType.EDITING:
						case sw.Promed.vac.cons.actType.IMPLEMENTING:
						case sw.Promed.vac.cons.actType.PURPOSING:
						case sw.Promed.vac.cons.actType.VIEWING:
						case sw.Promed.vac.cons.actType.ADDING:
							titleAdd = sw.Promed.vac.cons.actName[modeType];
							break;
						default:
//							titleAdd = sw.Promed.vac.cons.actName[modeType];
							titleAdd = '';
							break;
					}
					return titleAdd;
				},

				getTitleAdd: function(){
					if (titleAdd != undefined) return titleAdd;
					return this.setTitleAdd();
				},

				setTitle: function(){
					title = (this.setTitleAdd() != '') ? this.setTitleMain() + ' : ' + this.getTitleAdd() : this.setTitleMain();
					return title;
				},

				/*
				 * ф-ция "Получить наименование формы"
				 */
				getTitle: function(){
					if (title != undefined) return title;
					return this.setTitle();
				}
			}
		},
		
		/*
		 * Фабрика объектов - объект "маска загрузки"
		 */
		getLoadMaskObj: function () {
			return {
				/*
				 * Показываем маску
				 * o - объект для которого показываем маску
				 */
				loadMaskShow: function(o){
					if ( !o.loadMaskObj ) {
						o.loadMaskObj = new Ext.LoadMask(o.getEl(), { msg: LOAD_WAIT });
					}
					o.loadMaskObj.show();
				},
				
				/*
				 * Скрываем маску
				 * o - объект для которого показываем маску
				 */
				loadMaskHide: function(o){
					if ( o.loadMaskObj ) {
						o.loadMaskObj.hide();
					}
				}
			}
		},
		
		/*
		 * Фабрика объектов - параметры фильтров (основной+доп) для каждого журнала - свой
		 */
		getFiltrObj: function () {//TODO - Переименовать, отрефакторить (т.к. исп-ся также в группах риска)
			var isChanged = false; //признак "параметры поиска изменились"
			return {
				filtrFields: {}, //хранилище (в виде объекта) последних использованных параметров поиска
				newObj: {}, //хранилище значений нового объекта
				
				/* 
				 * setFiltr - сохранение новых параметров поиска (obj)
				 * o - новые параметры поиска
				 */
				setFiltr: function(o) {
					var obj = {};
//					obj = obj || this.newObj;
				  if (o != undefined) {
						Ext.apply(obj, o);
					} else {
						Ext.apply(obj, this.newObj);
					}
//					Ext.apply(obj, o, this.newObj);
					this.setChanged(obj);
					this.filtrFields = {};
					Ext.apply(this.filtrFields, obj);
					//this.resetIsChanged(); //Сброс флага "параметры поиска изменились"
				},
				
				/* 
				 * setChanged - проверка изменились ли параметры поиска, если да, то устанавливается isChanged = true
				 * obj - параметры поиска (сравниваются со старыми для проверки наличия изменений)
				 */
				setChanged: function(obj) {
					this.newObj = {};
//					this.newObj = obj;
					Ext.apply(this.newObj, obj);
					var changed = false;
					for (var name in obj) {
						if (obj.hasOwnProperty(name)) {
//							alert(this.filtrFields[name]);
//							alert(obj[name]);
							if (this.filtrFields[name] != obj[name]) changed = true;
						}
					}
					if ((this.filtrFields == undefined)||(Object.keys(obj).length != Object.keys(this.filtrFields).length)) {
						changed = true;
					}
//					if (changed == true) this.isChanged = changed;
					//if (changed == true)
						isChanged = changed;
//					return changed;
				},
				
				/* 
				 * получить значение признака "параметры поиска изменились"
				 */
				getIsChanged: function() {
					return isChanged;
				},
				
				//сброс признака "параметры поиска изменились"
				resetIsChanged: function() {
					isChanged = false;
				},
				
				//получить значение признака "параметры поиска изменились"
				getHistObj: function() {
					return this.filtrFields;
				},
				
				getNewObj: function() {
					return this.newObj;
				},
				
				//global - признак "Глобальный фильтр" - изменения параметров основного фильтра отражаются на всех
				// вкладках, в данный момент не используется
				global: true
//				,isChanged: false //признак "параметры поиска изменились"
			}
		},
		
	
    /*
     * ф-ция вывода окна сообщения об ошибке при работе с БД
     * responseObj - объект response-параметр результата выполнения запроса
     * возвращаемое значение - код ошибки
		 * TODO - сделать класс для обработки ответа, перенести это туда
		 * TODO - в модели сделать базовую модель, от которой и наследоваться
     */
		msgBoxErrorBd: function(responseObj) {
			var errMess = 'Ошибка при выполнении операции с БД!';
			var errCode = 0;
			var result = null;
			if ( typeof(responseObj) == "object" ) {
				if ( responseObj != null && responseObj.responseText.length > 0 ) {
					result = Ext.util.JSON.decode(responseObj.responseText);
//                consoleLog('-------result start-------');
//                consoleLog(result);
//                consoleLog('-------result end-------');
//                if ((!result.success)||(result.rows[0].Error_Msg.length > 0)) {
					if ((!result.success)||(result.rows[0].Error_Code != null && result.rows[0].Error_Code > 0)) {
						//сообщение об ошибке
						if (result.rows[0].Error_Msg.length > 0) {
							errMess = result.rows[0].Error_Msg;
							errCode = result.rows[0].Error_Code;
						}
//                  alert(errMess);
//                  return false;
					} else {
						return 0;
					}
				}
		
			} else {
				errMess = responseObj;
			}
			Ext.MessageBox.show({
				title: "Ошибка",
				msg: errMess,
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING
			});
			return errCode;
		},
    
    msgBoxNoValidForm: function(){
      Ext.MessageBox.show({
        title: "Проверка данных формы",
        msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
        buttons: Ext.Msg.OK,
        icon: Ext.Msg.WARNING
      //  ,fn: function() {
      //    base_form.findField('Person_SurName').focus(true, 100);
      //  }
      });
    },
    
    /*
     * ф-ция вывода сообщения на экран консоли
     * obj - объект для вывода (объект, массив или строка)
     */
		consoleLog: function (obj){
			if (sw.Promed.vac.utils.settings.consoleLogEnable == '1') {
        try {
          //log(title + ' | ' + obj[param]);
          //log(title + ' | ' + str);
          var logMsg = '';
          if (typeof(obj) == 'string' || typeof(obj) == 'boolean' || (obj instanceof window.Array)) { 
            logMsg += obj;
          } else {
            for(var key in obj) {
              if (!obj.hasOwnProperty(key)) continue
              if (logMsg) logMsg += ' | ';
              logMsg += key + ': ' + obj[key];
            }
          }
          //logMsg += ' | ' + (obj.msg || '-');
          log(logMsg);
        } catch(e) {
          log('log ERROR | ' + e.message);
        }
      }
    },
    
		/*
		 * ф-ция вызова окна
		 */
    callVacWindow: function (obj, scope, objConf){
      //debugger;
      var type1 = obj.type1;
    //  var gridType = obj.gridType;
      var type2 = obj.type2 || obj.gridType;
     
        sw.Promed.vac.utils.consoleLog('type2');
        sw.Promed.vac.utils.consoleLog(type2);
        
      //type2 = obj.gridType;
//              alert('params.parent_id='+params.parent_id);
      //switch(form.gridConfiDepend.active) {
      var record = obj.record;
//                        sw.Promed.vac.utils.consoleLog('record');
//                        sw.Promed.vac.utils.consoleLog(record);
                        
			sw.Promed.vac.utils.consoleLog('obj.processedRecords:');
			sw.Promed.vac.utils.consoleLog(obj.processedRecords);
//                        			sw.Promed.vac.utils.consoleLog('obj.processedRecords.Person_id:');
//			sw.Promed.vac.utils.consoleLog(obj.processedRecords.Person_id);
			
//        sw.Promed.vac.utils.consoleLog('record = ' + record.get('vacJournalAccount_id'));
        sw.Promed.vac.utils.consoleLog(record);
			var params = new Object();
      sw.Promed.vac.utils.consoleLog('type1');
            sw.Promed.vac.utils.consoleLog(type1)
      switch(type1) {
        /*
            case 'Other': // ДЛя прочих прививок
            sw.Promed.vac.utils.consoleLog('type1');
            sw.Promed.vac.utils.consoleLog(type1);
        break;
        */
        case 'btnForm': //формы по нажатию кнопки
    //      var recordId = record.person_id;
    //      sw.Promed.vac.utils.consoleLog('recordId='+recordId);
          Ext.apply(params, record);
    //      params.person_id = recordId;
		      if (record.parent_id != undefined) {
						params.parent_id = record.parent_id;
					} else {
						params.parent_id = scope.id;
					}
          break;
        default:
          var recordId = record.get('Person_id');
					if ((record != undefined)&&(record.get('Server_id') != undefined)) {
						params.Server_id = record.get('Server_id');
					}
    //      sw.Promed.vac.utils.consoleLog('recordId='+recordId);
          params.person_id = recordId;
          params.parent_id = scope.id;
          break;
      }
    //  alert(params.parent_id);

//      params.status_type_id = record.get('StatusType_id');
    switch(type2) {
        case 'VacMap':  //Список карт проф. прививок';
          params.age = record.get('Age');
          //params.parent_id = 
    //      sw.Promed.vac.utils.consoleLog('params.age='+params.age); 
          Ext.apply(params, objConf);          
          params.viewOnly = false;
          if(!Ext.isEmpty(obj.record.viewOnly))
          	params.viewOnly = obj.record.viewOnly;
          getWnd('amm_Kard063').show(params);
          break;

       case 'VacOther': //'Прочие прививки';
           //params.person_id = record.get('Person_id');
           // params.inoculation_id = record.get('Inoculation_id');
          sw.Promed.vac.utils.consoleLog('record');  
          Ext.apply(params, objConf);
          getWnd('amm_QuikImplVacOtherForm').show(params);
          break;
      
      case 'VacNoInfo': //'Информация отсутствует';
					params.status_type_id = record.get('StatusType_id');
          // params.row_plan_parent = 1; //пойдет в проц-ру исполнения
          // if (record.get('idInCurrentTable') != undefined) {
            params.plan_id = record.get('idInCurrentTable');
            // if (record.get('Vac_Scheme_id') != undefined && params.plan_id == -1) {
//              params.vac_scheme_id = record.get('Vac_Scheme_id');
							params.vac_scheme_id = record.get('Scheme_id');
              params.row_plan_parent = 0; //пойдет в проц-ру исполнения
            // }
          // } else {
          //   params.plan_id = record.get('planTmp_id');
          // }
					params.date_purpose = sw.Promed.vac.utils.nvlDate(record.get('Date_plan'));
					if (record.get('Scheme_num') != undefined) {
						params.scheme_num = record.get('Scheme_num');
					}
					sw.Promed.vac.utils.consoleLog(record.get('Date_plan'));
          sw.Promed.vac.utils.consoleLog('params:');
          sw.Promed.vac.utils.consoleLog(params);
          
          // if (sw.Promed.vac.utils.isValInArray(obj.processedRecords, params.plan_id)) {
          //   return sw.Promed.vac.utils.msgBoxProcessedRecords();
          // }
          Ext.apply(params, objConf);
          getWnd('amm_QuikImplVacForm').show(params);
          break;
          
        case 'VacEdit': //'Изменение вакцины при исполнении';
            sw.Promed.vac.utils.consoleLog('record');
            sw.Promed.vac.utils.consoleLog(record.data);
            params.status_type_id = -1; //record.get('StatusType_id');
            //alert(params.status_type_id);
            params.plan_id = -2;
            params.vac_scheme_id = record.get('Scheme_id');
            params.name = record.get('NAME_TYPE_VAC');
            params.row_plan_parent = -2; //пойдет в проц-ру исполнения
            params.date_purpose = sw.Promed.vac.utils.nvlDate(record.get('Date_plan'));
            params.vacJournalAccount_id = record.get('vacJournalAccount_id');
            if (record.get('Scheme_num') != undefined) {
                    params.scheme_num = record.get('Scheme_num');
            }
            sw.Promed.vac.utils.consoleLog(record.get('Date_plan'));
            sw.Promed.vac.utils.consoleLog('params:');
            sw.Promed.vac.utils.consoleLog(params);

              Ext.apply(params, objConf);
              getWnd('amm_QuikImplVacForm').show(params);
              break;  

        case 'VacPlan': //'План прививок';
          params.status_type_id = record.get('StatusType_id');
          params.row_plan_parent = 1; //пойдет в проц-ру исполнения
					if (record.get('idInCurrentTable') != undefined) {
						params.plan_id = record.get('idInCurrentTable');
						if (record.get('Vac_Scheme_id') != undefined && params.plan_id == -1) {
						  params.vac_scheme_id = record.get('Vac_Scheme_id');
              params.row_plan_parent = 0; //пойдет в проц-ру исполнения
					  }
					} else {
						params.plan_id = record.get('planTmp_id');
					}
					
					if (record.get('Scheme_num') != undefined) {
						params.scheme_num = record.get('Scheme_num');
					}
					
					sw.Promed.vac.utils.consoleLog('VacPlan params:');
					sw.Promed.vac.utils.consoleLog(params);
					
					if (sw.Promed.vac.utils.isValInArray(obj.processedRecords, params.plan_id)) {
						return sw.Promed.vac.utils.msgBoxProcessedRecords();
					}
    //              alert('params.plan_id='+params.plan_id);
//          params.date_purpose = sw.Promed.vac.utils.nvlDate(record.get('Date_Plan'));
//          params.vac_info = record.get('type_name');
//          params.vac_info += '\n' + record.get('Name').replace('<br />', '');
//          if (record.get('SequenceVac')) {//если 0, то не пишем (одиночная прививка)
//            params.vac_info += '\n' + 'Очередность: ' + record.get('SequenceVac');
//          }
//          params.vac_type_id = record.get('VaccineType_id');
//          params.birthday = sw.Promed.vac.utils.nvlDate(record.get('BirthDay'));
					
          Ext.apply(params, objConf);
          getWnd('amm_PurposeVacForm').show(params);
          break;
        case 'VacAssigned':
          params.source = type2;
					params.mode_type = 'IMPLEMENT';

                                         if (record.set_parent_id != undefined ) {;
                                            params.parent_id = record.parent_id;
                                            params.vac_jaccount_id = record.get('vacJournalAccount_id');
                                         }
                                        else {
                                            if (record.get('idInCurrentTable') != undefined) {
						params.vac_jaccount_id = record.get('idInCurrentTable');
					} else {
						params.vac_jaccount_id = record.get('JournalVacFixed_id');
					}
          
					if (sw.Promed.vac.utils.isValInArray(obj.processedRecords, params.vac_jaccount_id)) {
						return sw.Promed.vac.utils.msgBoxProcessedRecords();
					}
                                        }
    //              alert('params.vac_jaccount_id='+params.vac_jaccount_id);
    //              params.plan_id = record.get('planTmp_id');
//          params.date_purpose = sw.Promed.vac.utils.nvlDate(record.get('Date_Purpose'));
//          params.vac_info = record.get('NAME_TYPE_VAC').replace('<br />', '');
    //              params.vac_info += '\n' + record.get('vac_name');
    //              if (record.get('SequenceVac')) {//если 0, то не пишем (одиночная прививка)
    //                params.vac_info += '\n' + 'Очередность: ' + record.get('SequenceVac');
    //              }
    //              params.vac_type_id = record.get('VaccineType_id');
//          params.birthday = sw.Promed.vac.utils.nvlDate(record.get('BirthDay'));
//          params.vac_name = record.get('vac_name');
//          params.vaccine_id = record.get('Vaccine_id');
//          params.vac_doze = record.get('VACCINE_DOZA');
//          params.med_staff_fact_id = record.get('Purpose_MedPersonal_id'); //4543;
    //              alert('Purpose_MedPersonal_id='+record.get('Purpose_MedPersonal_id'));
    //      params.vac_way_place = record.get('VaccineWay_id');
          Ext.apply(params, objConf);
          getWnd('amm_ImplVacForm').show(params);
          break;
        case 'VacRegistr':
          sw.Promed.vac.utils.consoleLog('Все нормально!!!');
          params.source = type2;
          params.mode_type = 'EDIT';
//          params.Lpu_id = record.get('Lpu_id');
          
					
					if (record.get('idInCurrentTable') != undefined) {
						params.vac_jaccount_id = record.get('idInCurrentTable');
					} else {
						params.vac_jaccount_id = record.get('vacJournalAccount_id');
					}
					
//                alert ('params.parent_id = ' + params.parent_id);   
//                alert ('record.parent_id = ' + record.parent_id);
//                alert ('scope.id = ' + scope.id);
//                alert (record.set_parent_id);
                if (record.set_parent_id != undefined ) {
//                    alert ('record.parent_id = ' + record.parent_id);
                    params.parent_id = record.parent_id;
                }
//                else {
//                     alert ('params.parent_id = ' + params.parent_id); 
//                }
                if (record.parent_id != undefined) {
						params.parent_id = record.parent_id;
					} else {
						params.parent_id = scope.id;
					}
                                        
					//Перенесено на момент открытия формы
//          params.date_purpose = sw.Promed.vac.utils.nvlDate(record.get('Date_Purpose'));
//          params.vac_info = record.get('NAME_TYPE_VAC').replace('<br />', '');
//
//          params.birthday = sw.Promed.vac.utils.nvlDate(record.get('BirthDay'));
//          params.vac_name = record.get('vac_name');
//          params.vaccine_id = record.get('Vaccine_id');
//          params.vac_doze = record.get('VACCINE_DOZA');
    //      params.med_staff_fact_id = record.get('Purpose_MedPersonal_id'); //4543;

    //      params.vac_way_place = record.get('VaccineWay_id');
//          alert ('Lpu_id =  ' + params.Lpu_id);
          Ext.apply(params, objConf);
          getWnd('amm_ImplVacForm').show(params);
          break;
        case 'TubPlan':
          params.source = type2;
					if (record.get('idInCurrentTable') != undefined) {
						params.plan_tub_id = record.get('idInCurrentTable');
						params.parent_id = 'amm_Kard063';
//						if (record.get('Vac_Scheme_id') != undefined && params.plan_id == -1) {
//						  params.vac_scheme_id = record.get('Vac_Scheme_id');
//              params.row_plan_parent = 0; //пойдет в проц-ру исполнения
//					  }
					} else {
//						params.plan_id = record.get('planTmp_id');
						params.plan_tub_id = record.get('PlanTuberkulin_id');
						//params.parent_id = 'amm_Kard063';
					}
					
					if (sw.Promed.vac.utils.isValInArray(obj.processedRecords, params.plan_tub_id)) {
						return sw.Promed.vac.utils.msgBoxProcessedRecords();
					}
//          params.date_purpose = sw.Promed.vac.utils.nvlDate(record.get('Date_Plan'));
//          params.birthday = sw.Promed.vac.utils.nvlDate(record.get('BirthDay'));
          params.vaccine_id = 26; //манту
          Ext.apply(params, objConf);
          getWnd('amm_MantuPurposeForm').show(params);
          break;
        case 'TubAssigned':
          params.source = type2;
					
		if (record.set_parent_id != undefined ) {;
                            params.parent_id = record.parent_id;
                            params.fix_tub_id = record.get('fix_tub_id');
                         }
                 else {			
                    if (record.get('idInCurrentTable') != undefined) {
                                params.fix_tub_id = record.get('idInCurrentTable');
                        } else {
                                params.fix_tub_id = record.get('JournalMantuFixed_id');
                        }
                 }

            if (sw.Promed.vac.utils.isValInArray(obj.processedRecords, params.fix_tub_id)) {
                    return sw.Promed.vac.utils.msgBoxProcessedRecords();
            }

//          params.date_purpose = sw.Promed.vac.utils.nvlDate(record.get('Date_Purpose'));
//          params.birthday = sw.Promed.vac.utils.nvlDate(record.get('BirthDay'));
          params.vaccine_id = 26; //манту

          Ext.apply(params, objConf);
          sw.Promed.vac.utils.consoleLog('TubAssigned:');
          sw.Promed.vac.utils.consoleLog(params);
          getWnd('amm_mantuImplWithoutPurp').show(params);
          break;
        case 'TubReaction':
          params.source = type2;
            sw.Promed.vac.utils.consoleLog('type2');
            sw.Promed.vac.utils.consoleLog(type2);
          if (params.person_id == undefined) {
            //  Если в выбранной записи нет person_id
              params.person_id = record.person_id;
//              sw.Promed.vac.utils.consoleLog('record2');
//              sw.Promed.vac.utils.consoleLog(record);
//              alert (record.person_id);
          } 
            sw.Promed.vac.utils.consoleLog('params');
            sw.Promed.vac.utils.consoleLog(params);
					
		if (record.set_parent_id != undefined ) {;
                            params.parent_id = record.parent_id;
                            params.fix_tub_id = record.get('fix_tub_id');
                         }
                 else {			
                    if (record.get('idInCurrentTable') != undefined) {
                                params.fix_tub_id = record.get('idInCurrentTable');
                        } else {
                                params.fix_tub_id = record.get('JournalMantuFixed_id');
                        }
                 }
					
//          params.birthday = sw.Promed.vac.utils.nvlDate(record.get('BirthDay'));
//          params.date_purpose = sw.Promed.vac.utils.nvlDate(record.get('Date_Purpose'));
          params.vaccine_id = 26; //манту
					if (record.addNewMantu != undefined) {
						params.add_new_mantu = record.addNewMantu;
						if (record.get('BirthDay') != undefined) params.birthday = record.get('BirthDay');
					}
          Ext.apply(params, objConf);
          sw.Promed.vac.utils.consoleLog('TubReaction:');
          sw.Promed.vac.utils.consoleLog(params);
          getWnd('amm_mantuImplWithoutPurp').show(params);
          break;    
          
        case 'Diaskintest':
            params.source = type2;
            sw.Promed.vac.utils.consoleLog('type2');
            sw.Promed.vac.utils.consoleLog(type2);
            if (params.person_id == undefined) {
                params.person_id = record.person_id;
            }
            sw.Promed.vac.utils.consoleLog('params');
            sw.Promed.vac.utils.consoleLog(params);

            if (record.set_parent_id != undefined ) {;
                params.parent_id = record.parent_id;
                params.fix_tub_id = record.get('fix_tub_id');
            }
            else {
                if (record.get('idInCurrentTable') != undefined) {
                    params.fix_tub_id = record.get('idInCurrentTable');
                } else {
                    params.fix_tub_id = record.get('Diaskintest_id');
                }
            }
            params.vaccine_id = 26;
            if (record.addNewMantu != undefined) {
                params.add_new_mantu = record.addNewMantu;
                if (record.get('BirthDay') != undefined) params.birthday = record.get('BirthDay');
            }
            Ext.apply(params, objConf);
            sw.Promed.vac.utils.consoleLog('Diaskintest:');
            sw.Promed.vac.utils.consoleLog(params);
            getWnd('amm_DiaskinTestEditWindow').show(params);
            break;

        case 'VacRefuse':
					params.refuse_id = record.get('vacJournalMedTapRefusal_id');
					sw.Promed.vac.utils.consoleLog('params.refuse_id='+params.refuse_id);
          Ext.apply(params, objConf);
					getWnd('amm_RefuseVacForm').show(params);
          break;
        case 'btnFormRefuse':
          Ext.apply(params, objConf);
          getWnd('amm_RefuseVacForm').show(params);
          break;
        case 'btnFormPlanParams':
          Ext.apply(params, objConf);
          getWnd('amm_vacPlanParams').show(params);
          break;
        case 'btnFormEditParam':
          Ext.apply(params, objConf);
          getWnd('amm_vacGetComboVal').show(params);
          break;
					
	case 'btnSprVaccineEditForm':
          Ext.apply(params, objConf);
          getWnd('amm_SprVaccineEditWindow').show(params);
	  break;
          
        case 'btnSprNacCalEditWindow':
          Ext.apply(params, objConf);
          getWnd('amm_SprNacCalEditWindow').show(params);
	  break;  

        case 'btnGoToImpl':
          scope.hide();
          sw.Promed.vac.utils.consoleLog('btnGoToImpl...');
          sw.Promed.vac.utils.consoleLog('params:');
          sw.Promed.vac.utils.consoleLog(params);
          sw.Promed.vac.utils.consoleLog('record:');
          sw.Promed.vac.utils.consoleLog(record);
    //      params.vac_jaccount_id = record.get('JournalVacFixed_id');
    //      params.date_purpose = record.get('Date_Purpose');
    //      params.vac_info = record.get('NAME_TYPE_VAC').replace('<br />', '');
    //      params.birthday = record.get('BirthDay');
    //      params.vac_name = record.get('vac_name');
    //      params.vac_doze = record.get('VACCINE_DOZA');
    //      params.med_staff_fact_id = record.get('Purpose_MedPersonal_id'); //4543;
    //      params.vac_way_place = record.get('VaccineWay_id');
    //      getWnd('amm_ImplVacForm').show(params);
          getWnd('amm_ImplVacNoPurpForm').show(params);
          break;

        case 'btnGoToImplMantu':
          scope.hide();
          sw.Promed.vac.utils.consoleLog('btnGoToImplMantu...');
          sw.Promed.vac.utils.consoleLog('params:');
          sw.Promed.vac.utils.consoleLog(params);
          sw.Promed.vac.utils.consoleLog('record:');
          sw.Promed.vac.utils.consoleLog(record);
          getWnd('amm_mantuImplWithoutPurp').show(params);
          break;

        case 'btnAddPerson':
          Ext.apply(params, objConf);
    //      debugger;
          getWnd('amm_Kard063').show(params);
          break;
        default:
             sw.Promed.vac.utils.consoleLog('type1:'); 
             sw.Promed.vac.utils.consoleLog(type1);
             sw.Promed.vac.utils.consoleLog('callVacWindow - неописанное значение!');
					if (record.get('PersonPlan_id') != undefined) {
						params.person_plan_id = record.get('PersonPlan_id');
					}
					if (record.get('Vac_Scheme_id') != undefined) {
						params.vac_scheme_id = record.get('Vac_Scheme_id');
					}
					sw.Promed.vac.utils.consoleLog('params:');
					sw.Promed.vac.utils.consoleLog(params);
    //      Ext.apply(params, objConf);
    //      getWnd('amm_Kard063').show(params);
          break;
      }
    },
		
		/**
		* ф-ция проверки наличия даты
		*/
		nvlDate: function (param, formatd) {
			formatd = formatd || 'd.m.Y';
			sw.Promed.vac.utils.consoleLog('param:');
			sw.Promed.vac.utils.consoleLog(param);
			if ((param != undefined)&&(param != '')) {
				return param.format(formatd);
			} else {
				return null;
			}
		},
		
		/**
		* ф-ция проверки наличия значения val в массиве arr
		*/
		isValInArray: function (arr, val) {
//			alert(typeof(arr));
			if ((typeof(arr) != 'object')||(!arr.length)) return false;
			for (var i = 0; i < arr.length; i++) {
				if (val == arr[i]) return true;
			}
			return false;
		},
		
//		/**
//		* ф-ция проверки на попытку повторной обработки записи
//		*/
//		isRecordProcessed: function (arr, val) {
//			for (var i = 0; i < arr.length; i++) {
//				if (val == arr[i]) return true;
//			}
//			return false;
//		}

		/**
		* ф-ция вывода сообщения о попытке повторной обработки записи
		*/
    msgBoxProcessedRecords: function(){
      Ext.MessageBox.show({
        title: "Проверка на повторную обработку",
        msg: "Попытка повторной обработки! Данная запись уже была вами обработана.",
        buttons: Ext.Msg.OK,
        icon: Ext.Msg.WARNING
      //  ,fn: function() {
      //    base_form.findField('Person_SurName').focus(true, 100);
      //  }
      });
    }
		
  }
}();


/*
 * разные объекты используемые на формах (в целях унификации)
 */
sw.Promed.vac.objects = function() {
  return {
    vaccineListCombo: function(o) {
			
//			var def = new Object();
			// Дефолтовые значения:
//			def.idPrefix = 'vacObjects';
//			def.hiddenName = 'Vaccine_id';
//			def.fieldLabel = 'Вакцина';
//			def.width = 260;
		  var obj = new Object();
			obj.id = o.idPrefix + '_' + 'comboVaccineList';
			obj.idGridSimilarRecords = o.idPrefix + '_' + 'gridSimilarRecords';
			obj.idComboVaccineSeria = o.idPrefix + '_' + 'comboVaccineSeria';
			obj.idComboVaccineWay = o.idPrefix + '_' + 'comboVaccineWay';
      obj.idComboVaccineDoze = o.idPrefix + '_' + 'comboVaccineDoze';
			// Дефолтовые настройки объекта:
      var def = {
                allowBlank: false,
//								idPrefix: 'vacObjects',
//                id: obj.idPrefix + '_' + 'comboVaccineList',
                autoLoad: true,
//                fieldLabel: obj.fieldLabel,
                fieldLabel: 'Вакцина',
//                tabIndex: o.tabIndex,
                hiddenName: 'Vaccine_id',
                width: 260,
                xtype: 'amm_VaccineListCombo',
                listeners: {
                  //listeners.select = function( combo, record, index ) {
                  'select': function( combo, record, index ) {
                   
                      if ( combo.getValue() ) {
                      combo.generalParams.vaccine_id = combo.getValue();
                      //consoleLog('combo.generalParams.vaccine_id='+combo.generalParams.vaccine_id);
                      sw.Promed.vac.utils.consoleLog(combo.generalParams);
//											if (obj.idGridSimilarRecords != undefined) {
												Ext.getCmp(obj.idGridSimilarRecords).store.load({
													params: combo.generalParams
												});
//											}

                      var comboVacSeria = Ext.getCmp(obj.idComboVaccineSeria);
                      comboVacSeria.store.load({
                        params: combo.generalParams,
                        callback: function(){
                          if (comboVacSeria.getStore().getCount() > 0)
                            if (!!combo.generalParams.row_plan_parent) comboVacSeria.setValue(comboVacSeria.getStore().getAt(0).get('VacPresence_id'));
													else comboVacSeria.setValue('');
                        }
                      });
                    }

                    var comboVacWay = Ext.getCmp(obj.idComboVaccineWay);
                    comboVacWay.reset();
                    comboVacWay.store.load({
                      params: combo.generalParams,
                      callback: function(){
                        if (comboVacWay.getStore().getCount() > 0)
                          if (!!combo.generalParams.row_plan_parent) comboVacWay.setValue(comboVacWay.getStore().getAt(0).get('VaccineWayPlace_id'));
                      }
                    });

                    var comboVacDoze = Ext.getCmp(obj.idComboVaccineDoze);
                    comboVacDoze.reset();
                    comboVacDoze.store.load({
                      params: combo.generalParams,
                      callback: function(){
                        if (comboVacDoze.getStore().getCount() > 0)
                          if (!!combo.generalParams.row_plan_parent) comboVacDoze.setValue(comboVacDoze.getStore().getAt(0).get('VaccineDose_id'));
                      }
                    });
                  }
                }
      };
			Ext.apply(obj ,o ,def);
			
//			obj.id = o.id || 'vacObjects_comboVaccineList';
//      obj.idPrefix = o.idPrefix || 'vacObjects';
//			obj.id = obj.idPrefix + '_' + 'comboVaccineList';
//			obj.hiddenName = o.hiddenName || 'Vaccine_id';
//			obj.fieldLabel = o.fieldLabel || 'Вакцина';
//			obj.width = o.width || 260;
//			obj.idGridSimilarRecords = o.idGridSimilarRecords || obj.idPrefix + '_' + 'gridSimilarRecords';
//			obj.idComboVaccineSeria = o.idComboVaccineSeria || 'vacObjects_comboVaccineSeria';
//			obj.idComboVaccineWay = o.idComboVaccineWay || 'vacObjects_comboVaccineWay';
//      obj.idComboVaccineDoze = o.idComboVaccineDoze || 'vacObjects_comboVaccineDoze';
//			obj.idGridSimilarRecords = obj.idPrefix + '_' + 'gridSimilarRecords';
//			obj.idComboVaccineSeria = obj.idPrefix + '_' + 'comboVaccineSeria';
//			obj.idComboVaccineWay = obj.idPrefix + '_' + 'comboVaccineWay';
//      obj.idComboVaccineDoze = obj.idPrefix + '_' + 'comboVaccineDoze';
			
			return obj;
		},
		
		comboVaccineSeria: function(o) {
		  var obj = new Object();
			obj.id = o.idPrefix + '_' + 'comboVaccineSeria';
			// Дефолтовые настройки объекта:
      var def = {
//				id: 'vacObjects_comboVaccineSeria',
				autoLoad: true,
				allowTextInput: true,
				fieldLabel: 'Серия и срок годности',
				hiddenName: 'VaccineSeria_id',
				width: 260,
				xtype: 'amm_VaccineSeriaCombo'
//                                ,listeners: {
//					'blur': function(combo)  {
//                                            return;
//                                        }
//                                }        
			};
			Ext.apply(obj ,o ,def);
			return obj;
		},
		
		comboVaccineWay: function(o) {
		  var obj = new Object();
			obj.id = o.idPrefix + '_' + 'comboVaccineWay';
			// Дефолтовые настройки объекта:
      var def = {
				allowBlank: false,
//				id: 'vacObjects_comboVaccineWay',
				autoLoad: true,
				fieldLabel: 'Способ и место введения',
				hiddenName: 'VaccineWayPlace_id',
				width: 260,
				xtype: 'amm_VacWayPlaceCombo'
			};
			Ext.apply(obj ,o ,def);
			return obj;
		},
		
		comboVaccineDoze: function(o) {
		  var obj = new Object();
			obj.id = o.idPrefix + '_' + 'comboVaccineDoze';
			// Дефолтовые настройки объекта:
      var def = {
				allowBlank: false,
//				id: 'vacObjects_comboVaccineDoze',
				autoLoad: true,
				fieldLabel: 'Доза введения',
//				tabIndex: TABINDEX_VACPRPFRM + 15,
				hiddenName: 'VaccineDoze_id',
				width: 260,
				xtype: 'amm_VacDozeCombo'
			};
			Ext.apply(obj ,o ,def);
			return obj;
		},
		
		comboLpu: function(o) {
			var obj = new Object();
			obj.id = o.idPrefix + '_' + 'LpuCombo';
			// Дефолтовые настройки объекта:
      var def = {
//				id: 'impl_LpuCombo',
				listWidth: 600,
//				tabIndex: TABINDEX_VACIMPNPURPFRM + 11,
				width: 260,
				hiddenName: 'Lpu_id',
				xtype: 'amm_LpuListCombo',
				listeners: {
					'select': function(combo)  {
						var parentForm = combo.findForm().getForm();
						parentForm.findField('LpuBuilding_id').reset();
						parentForm.findField('LpuSection_id').reset();
						parentForm.findField('MedStaffFact_id').reset();
						parentForm.findField('LpuBuilding_id').getStore().load({
							params: {Lpu_id: combo.getValue()}
                                                      
						});                              
                                                
					}.createDelegate(this)
				}
			};
			Ext.apply(obj ,o ,def);
			return obj;
		},
		
		comboLpuBuilding: function(o) {
			var obj = new Object();
			obj.id = o.idPrefix + '_' + 'LpuBuildingCombo';
			obj.linkedElements = [o.idPrefix + '_' + 'LpuSectionCombo'];
			// Дефолтовые настройки объекта:
      var def = {
				hiddenName: 'LpuBuilding_id',
//				id: 'impl_LpuBuildingCombo',
				listWidth: 600,
//				linkedElements: [
//					'impl_LpuSectionCombo'
//				],
//				tabIndex: TABINDEX_VACIMPFRM + 11,
				width: 260,
				xtype: 'swlpubuildingglobalcombo'
			};
			Ext.apply(obj ,o ,def);
			return obj;
		},
		
		comboLpuSection: function(o) {
			var obj = new Object();
			obj.id = o.idPrefix + '_' + 'LpuSectionCombo';
			obj.parentElementId = o.idPrefix + '_' + 'LpuBuildingCombo';
			obj.linkedElements = [o.idPrefix + '_' + 'MedPersonalCombo'];
			// Дефолтовые настройки объекта:
      var def = {
				hiddenName: 'LpuSection_id',
//				id: 'impl_LpuSectionCombo',
//				linkedElements: [
//						'impl_MedPersonalCombo'
//				],
				listWidth: 600,
//				parentElementId: 'impl_LpuBuildingCombo',
//				tabIndex: TABINDEX_VACIMPFRM + 12,
				width: 260,
				xtype: 'swlpusectionglobalcombo'
			};
			Ext.apply(obj ,o ,def);
			return obj;
		},
		
		comboMedStaffFact: function(o) {
			var obj = new Object();
			obj.id = o.idPrefix + '_' + 'MedPersonalCombo';
			obj.parentElementId = o.idPrefix + '_' + 'LpuSectionCombo';
			// Дефолтовые настройки объекта:
      var def = {
				hiddenName: 'MedStaffFact_id',
				allowBlank: false,
				fieldLabel: 'Врач (исполнил)',
//				id: 'impl_MedPersonalCombo',
//				parentElementId: 'impl_LpuSectionCombo',
				listWidth: 600,
//				tabIndex: TABINDEX_VACIMPFRM + 13,
				width: 260,
				emptyText: VAC_EMPTY_TEXT,
				xtype: 'swmedstafffactglobalcombo'
			};
			Ext.apply(obj ,o ,def);
			return obj;
		}
		
		,fieldVacImplementDate: function(o) {
		  var obj = new Object();
			// Дефолтовые настройки объекта:
      var def = {
        name: 'vacImplementDate',
				fieldLabel: 'Дата исполнения',
//				tabIndex: TABINDEX_VACIMPFRM + 10,
				allowBlank: false,
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
                                
			};
			Ext.apply(obj ,o ,def);
			return obj;
		}
		
//		,comboXXX: function(o) {
//		  var obj = new Object();
//			// Дефолтовые настройки объекта:
//      var def = {
//        hiddenName: '',
//
//			};
//			Ext.apply(obj ,o ,def);
//			return obj;
//		}
	}
}();


/*
* Создаем синглтон из настроечных данных таблицы БД settings
*/

(function () {
	var instance;
	sw.Promed.vac.settings = function settings() {
		if (instance) {
//			alert('old settings');
			return instance;
		}
//		alert('new settings');
		instance = this;
		
		/*
		* читаем начальные установки из таблицы БД settings
		*/
		this.formStore = new Ext.data.JsonStore({
			fields: ['id', 'name', 'value', 'parent_id', 'description'],
			url: '/?c=VaccineCtrl&m=getVacSettings',
			key: 'id',
			root: 'rows'
		});

		this.formStore.load({
			params: {
			},
			callback: function(){
				var cntFormStore = this.formStore.getCount();
				var formStoreRecord;
				var j = 0;
				//default values:
				this.lpuAccess = [];
                                this.vacSprAccesFull = [];
				this.consoleLogEnable = 0;
				this.lpuAcceProfilesAllOnkoCtrl = [];
				//тянем из таблицы:
				for (var i = 0; i < cntFormStore; i++) {
					formStoreRecord = this.formStore.getAt(i);
					if (formStoreRecord.get('parent_id') != undefined) {
						//если не пустое значение:
						if (formStoreRecord.get('value') != undefined) {
							switch (formStoreRecord.get('name')) {
								case 'lpuAccess':
									this.lpuAccess[j++] = formStoreRecord.get('value');
									break;
                                                               case 'vacSprAccesFull':
                                                                        this.vacSprAccesFull[j++] = formStoreRecord.get('value');
									break;
                                                                case 'consoleLogEnable':
									this.consoleLogEnable = formStoreRecord.get('value');
									break;
								case 'lpuAccessAll':
									this.lpuAccessAll = formStoreRecord.get('value');
									break;
                                                                case 'lpuAcceProfilesAllOnkoCtrl':    
                                                                        this.lpuAcceProfilesAllOnkoCtrl[j++] = formStoreRecord.get('value');
									break;
							}
						}

					}

				}
			}.createDelegate(this)
		});
	};
})();

sw.Promed.vac.utils.settings = new sw.Promed.vac.settings;
