/**
 * Электронная медицинская карта
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.swPersonEmkWindow', {
	requires: [
		'common.EMK.PersonInfoPanel'
	],
	extend: 'base.BaseForm',
	alias: 'widget.swPersonEmkWindow',
	autoShow: false,
	maximized: true,
	width: 1000,
	itemId: 'common',
	refId: 'common',
	findWindow: false,
	closable: true,
	cls: 'arm-window-new',
	title: 'Электронная медицинская карта',
	iconCls: 'emk16-2017',
	header: false,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	isLoading: false,
	historyGroupMode: null,
	//объект с параметрами рабочего места, с которыми была открыта форма АРМа
	userMedStaffFact: null,
	openEditWindow: function (data, action) {

		var wnd,
			params = {
				userMedStaffFact: this.userMedStaffFact,
				formParams: {
					Person_id:this.Person_id,
					PersonEvn_id:this.PersonEvn_id,
					Server_id:this.Server_id
				}
			};

		switch(data.object) {
			case 'EvnPL':
				wnd = 'swEmkEvnPLEditWindow';
				params.formParams.EvnPL_id = data.object_id;
				params.action = action;
				break;
			case 'EvnVizitPL':
				wnd = 'swEmkEvnPLEditWindow';
				params.formParams.EvnPL_id = data.object_data.Evn_pid;
				params.formParams.EvnVizitPL_id = data.object_id;
				params.action = action;
				break;
		}

		params.callback = Ext6.emptyFn;
		params.onHide = function () { // надо ли перезагружать, если ничего не изменилось?
			this.loadEmkViewForm(this.currentNode.object, this.currentNode.object_value);
		}.createDelegate(this);

		if (!wnd) return false;

		getWnd(wnd).show(params);
	},
	viewFormDataStore: new Ext6.data.SimpleStore({
		autoLoad: true,
		fields:[],
		updateFromMap: function(map, parent)
		{
			this.completeFromMap(map, parent, true);
		},
		completeFromMap: function(map, parent, remove_existing)
		{
			log({
				debug: 'completeFromMap',
				args: arguments,
				store: this
			});
			if (typeof(map) != 'object') return false;
			var object = {},list,subsection, record, item_arr, i,index;
			var value_list = [];
			for (object.code in map)
			{
				//log(object.code);
				if (typeof(map[object.code]) == 'object' && map[object.code].item && Ext6.isArray(map[object.code].item))
				{
					item_arr = map[object.code].item;
					object.key = map[object.code].object_key;
					list = map[object.code].list || null;
					subsection = map[object.code].subsection || null;
					//log(item_arr.length);
					for(i=0; i < item_arr.length; i++)
					{
						//log(item_arr[i].data);
						//item_arr[i].data._is_first = (i==0);
						//item_arr[i].data._is_last = (i==(item_arr.length-1));
						item_arr[i].data._item_count = item_arr.length;
						item_arr[i].data._item_index = i;
						record = new Ext6.data.Record(item_arr[i].data);
						object.value = item_arr[i].data[object.key];
						value_list.push(object.value);
						if(record)
						{
							record.object_code = object.code;
							record.object_key = object.key;
							record.object_value = object.value;
							record.parent_object_code = (parent && parent.code) || null;
							record.parent_object_key = (parent && parent.key) || null;
							record.parent_object_value = (parent && parent.value) || null;
							record.subsection = subsection;
							record.list = list;
							record.id = object.code +'_'+ object.value;
							//log(record.id);
							if (remove_existing)
							{
								index = this.indexOfId(record.id);
								if(index)
								{
									this.removeAt(index);
								}
							}
							this.add(record);
						}
						if(item_arr[i].xml_data) {
							record = new Ext6.data.Record(item_arr[i].xml_data);
							if(record)
							{
								record.object_code = 'EvnXml';
								record.object_key = 'EvnXml_id';
								record.object_value = item_arr[i].EvnXml_id;
								record.parent_object_code = object.code;
								record.parent_object_key = object.key;
								record.parent_object_value = object.value;
								record.XmlType_id = item_arr[i].XmlType_id;
								record.id = 'EvnXml_'+ object.value;
								if (remove_existing)
								{
									index = this.indexOfId(record.id);
									if(index)
									{
										this.removeAt(index);
									}
								}
								this.add(record);
							}
						}
						if (item_arr[i].children)
						{
							this.completeFromMap(item_arr[i].children, object,remove_existing);
						}
					}
					this.each(function(record) {
						if (
							record.object_code == object.code
							&& record.parent_object_key == parent.key && record.parent_object_value == parent.value
							&& !record.object_value.inlist(value_list)
						) {
							index = this.indexOfId(record.id);
							if(index)
							{
								this.removeAt(index);
							}
						}
					}.createDelegate(this));
				} else {
					this.each(function(record) {
						if (record.object_code == object.code && record.parent_object_key == parent.key && record.parent_object_value == parent.value) {
							index = this.indexOfId(record.id);
							if(index)
							{
								this.removeAt(index);
							}
						}
					}.createDelegate(this));
				}
			}
		},
		data : []
	}),
	getObjectData: function(object,object_id)
	{
		var record = this.viewFormDataStore.getById(object + '_' + object_id);
		if (record && record.data) {
			return record.data;
		}
		return false;
	},
	openEvnPLDispEditWindow: function(action, el_data) {
		if (!action.inlist(['edit','view'])) {
			return false;
		}
		var form = this;
		var data = {};
		var my_params = {};
		var wnd_name = '';

		data = form.getObjectData(el_data.object, el_data.object_id);

		if ( data == false ) {
			return false;
		}

		my_params = {
			Person_id: form.Person_id,
			Server_id: form.Server_id,
			DispClass_id: data.DispClass_id,
			action: action
		};
		my_params[data.Object+'_id'] = el_data.object_id;

		switch(data.Object) {
			case 'EvnPLDispDop':
				wnd_name = 'swEvnPLDispDopEditWindow';
				break;

			case 'EvnPLDispDop13':
				wnd_name = 'swEvnPLDispDop13EditWindow';
				break;

			case 'EvnPLDispOrp':
				if (data.DispClass_Code.inlist([3,7])) {
					wnd_name = 'swEvnPLDispOrp13EditWindow';
				} else
				if (data.DispClass_Code.inlist([4,8])) {
					wnd_name = 'swEvnPLDispOrp13SecEditWindow';
				}
				break;

			case 'EvnPLDispProf':
				wnd_name = 'swEvnPLDispProfEditWindow';
				break;

			case 'EvnPLDispTeen14':
				wnd_name = 'swEvnPLDispTeen14EditWindow';
				break;

			case 'EvnPLDispTeenInspection':
				switch(data.DispClass_Code) {
					case 6:
						wnd_name = 'swEvnPLDispTeenInspectionEditWindow';
						break;
					case 9:
						wnd_name = 'swEvnPLDispTeenInspectionPredEditWindow';
						break;
					case 10:
						wnd_name = 'swEvnPLDispTeenInspectionProfEditWindow';
						break;
					case 11:
						wnd_name = 'swEvnPLDispTeenInspectionPredSecEditWindow';
						break;
					case 12:
						wnd_name = 'swEvnPLDispTeenInspectionProfSecEditWindow';
						break;
				}
				break;

			case 'EvnPLDispMigrant':
				wnd_name = 'swEvnPLDispMigrantEditWindow';
				my_params.callback = function(data) {
					if ( !data ) {
						return false;
					}
					// обновить форму
					form.loadEmkViewForm(el_data.object, el_data.object_id, '');
				};
				break;

			case 'EvnPLDispDriver':
				wnd_name = 'swEvnPLDispDriverEditWindow';
				my_params.callback = function(data) {
					if ( !data ) {
						return false;
					}
					// обновить форму
					form.loadEmkViewForm(el_data.object, el_data.object_id, '');
				};
				break;
		}

		if (wnd_name == '') {
			return false;
		}

		getWnd(wnd_name).show(my_params);
	},
	checkAndOpenRepositoryObserv: function (evn_id) {
		var win = this;
		var params = {
			action: 'add',
			useCase: 'evnvizitpl',
			Evn_id: evn_id,
			MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
			Person_id: win.Person_id,
			parentWin: win
		};
		
		Ext6.Ajax.request({
			callback: function(cbOptions, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj[0] && response_obj[0].RepositoryObserv_id) {
						params.hasPrev = true;
						params.PlaceArrival_id = response_obj[0].PlaceArrival_id;
						params.KLCountry_id = response_obj[0].KLCountry_id;
						params.Region_id = response_obj[0].Region_id;
						params.RepositoryObserv_arrivalDate = response_obj[0].RepositoryObserv_arrivalDate;
						params.TransportMeans_id = response_obj[0].TransportMeans_id;
						params.RepositoryObserv_TransportDesc = response_obj[0].RepositoryObserv_TransportDesc;
						params.RepositoryObserv_TransportPlace = response_obj[0].RepositoryObserv_TransportPlace;
						params.RepositoryObserv_TransportRoute = response_obj[0].RepositoryObserv_TransportRoute;
						params.RepositoryObserv_FlightNumber = response_obj[0].RepositoryObserv_FlightNumber;
						params.RepositoryObserv_IsCVIContact = response_obj[0].RepositoryObserv_IsCVIContact;
						params.RepositoryObesrv_contactDate = response_obj[0].RepositoryObesrv_contactDate || null;
						params.RepositoryObserv_Height = response_obj[0].RepositoryObserv_Height;
						params.RepositoryObserv_Weight = response_obj[0].RepositoryObserv_Weight;
						getWnd('swRepositoryObservEditWindow').show(params);
					} else {
						getWnd('swRepositoryObservEditWindow').show(params);
					}
				}
			},
			params: {
				Person_id: win.Person_id
			},
			url: '/?c=RepositoryObserv&m=findByPerson'
		});
	},
	toggleDisplayDocument: function(id, itemSectionCode) {
		// сворачивание / разворачивание документа
		var el = Ext6.get(itemSectionCode + '_Data_' + id);
		var me = this;
		if (el.hasCls('freedoc_opened')) {
			if (el.isVisible()) {
				el.setStyle('display', 'none');
			} else {
				el.setStyle('display', 'block');
			}
		} else {
			el.addCls('freedoc_opened');
			el.setStyle('display', 'block');
			var s_id = itemSectionCode + '_' + id + '_content';
			if (Ext6.fly(s_id)) {
				document.getElementById(s_id).scrollIntoView();
				document.getElementById('main-center-panel').scrollIntoView();
			}
			// Грузим данные документа с сервера
			var params = {
				EvnXml_id: id,
				instance_id: null //instance_id
			};

			me.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				url: '/?c=Template&m=loadEvnXmlViewData',
				params: params,
				callback: function(options, success, response) {
					me.getLoadMask().hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.html) {
							el.insertHtml("afterBegin", response_obj.html);
						}
					}

					el.setStyle('display', 'block');

					var s_id = itemSectionCode +'_'+ params.EvnXml_id +'_content';
					if (Ext.fly(s_id)){
						document.getElementById(s_id).scrollIntoView();
						document.getElementById('main-center-panel').scrollIntoView();
					}

					var editorIds = el.query('.mce-tinymce iframe').map(function(dom) {
						return dom.id.replace('_ifr','');
					});
					for (var id in this.input_cmp_list) {
						if (id.inlist(editorIds)) {
							sw.Promed.EvnXml.refreshEditorSize(this.input_cmp_list[id]);
						}
					}
				}
			});
		}
	},
	setObjectHandler: function (type, item) {
		log('setObjectHandler', type, item);

		var me = this;
		var object_id = item[type+'_id'];
		if (!object_id) {
			object_id = item['EvnXml_id'];
		}

		if (me.config_actions[type] && object_id) {
			var actions = me.config_actions[type];
			for (var action in actions) {
				var el = Ext6.get(type + '_data_' + object_id + '_' + action);
				if (!el) {
					el = Ext6.get(type + '_' + object_id + '_' + action);
				}

				var params = {object: type, object_id: object_id, object_data: item};
				if (el) {
				    if (action.indexOf('print') != -1) {
				        el.setCls('x6-btn-icon-el x6-btn-icon-el-default-toolbar-small panicon-print');
                    }
					el.show();
					el.on('click', actions[action].handler, null, params);
				}
			}
		}
	},
	setObjectHandlers: function (map, isroot) {
		log('setObjectHandlers', map, isroot);

		var win = this;

		if (isroot) {
			win.currentNodeData = map;

			// скрываем неактивные кнопки
			this.htmlPanel.getEl().select("a.button").hide();
		}

		for (obj in map) {
			var items = map[obj].item;
			if (!items) continue;

			for (i in items) {
				var item = items[i];
				if (typeof(item) != 'object') continue;
				win.setObjectHandler(obj, item);
				if (!!item.children) {
					win.setObjectHandlers(item.children, false);
				}
			}
		}
	},
	timerActivate: function () {
		var win = this;
		var p = win.PersonInfoPanel.TimeElapsed;

		var timerTpl = new Ext6.Template(
			'<div class="timer-evn-vizit" data-qtip="Время приема пациента">',
				'<div class="timer-minutes"><span>{decM}</span><span style="margin-left: 1px">{unM}</span></div>',
				'<p class="timer-splitter">',
					'<span style="font: 400 20px/12px Roboto, Helvetica, Arial, Geneva, sans-serif">.</span><br>',
					'<span style="font: 400 20px/6px Roboto, Helvetica, Arial, Geneva, sans-serif">.</span>',
				'</p>',
				'<div class="timer-seconds"><span>{decS}</span><span style="margin-left: 1px">{unS}</span></div>',
			'</di>'
		);
		win.stopTask();
		win.runner = new Ext6.util.TaskRunner();
		var begin = new Date();

		var updateClock = function() {
			var end = new Date(), // время сейчас
				t = new Date(end - begin), // времени прошло с момента старта begin
				s = Ext6.Date.format(t, 'is'); //преобразовали в строку разницу в миллисекундах
			// применили в шаблон значения и подставляем в панельку
			p.setHtml(timerTpl.apply({decM: s[0],unM: s[1],decS: s[2],unS: s[3]}));
			if(t.getMinutes()>12 && !p.hasCls('orange-time'))
				p.addCls('orange-time');
			if(t.getMinutes()>15 && !p.hasCls('red-time'))
				p.addCls('red-time');
		};
		// Задаем первоначальный вид
		p.removeCls(['orange-time','red-time']);
		p.setHtml(timerTpl.apply({decM: 0,unM: 0,decS: 0,unS: 0}));
		p.setStyle('visibility','visible');

		win.task = win.runner.start({
			run: updateClock,
			interval: 1000
		});
	},
	// очистка формы просмотра
	clearNodeViewForm: function ()
	{
		var win = this;
		var tpl = new Ext6.XTemplate('');
		tpl.overwrite(win.htmlPanel.body, {});
		win.rightPanel.getLayout().setActiveItem(0);
	},
	ignoreParams:[],
	formStatus: 'edit',
	// добавление нового посещения в ТАП
	addEvnVizitPL: function(data) {
		var me = this;
		if (me.formStatus === 'save') {
			return false;
		}
		me.formStatus = 'save';
		
		if(getRegionNick() == 'ufa') {
			var evndata = me.EvnPLForm.getEvnData();
			if(!Ext6.isEmpty(evndata)) {
				if (!me.EvnPLForm.isFinish && evndata.UslugaComplex_Code && isMorbusOneVizitCode(evndata.UslugaComplex_Code)) {
					Ext6.Msg.alert(langs('Сообщение'), langs('Случай содержит однократное посещение по заболеванию'));
					me.formStatus = 'edit';
					return false;
				}

				if (!me.EvnPLForm.isFinish && evndata.UslugaComplex_Code && isOneVizitCode(evndata.UslugaComplex_Code)) {
					Ext6.Msg.alert(langs('Сообщение'), langs('Добавление посещения невозможно, т.к. в рамках текущего ТАП уже есть посещение с кодом профилактического/консультативного посещения'));
					me.formStatus = 'edit';
					return false;
				}
			}
		}
		if (getRegionNick() == "kareliya") {
			if (!Ext6.isEmpty(evndata = me.EvnPLForm.getEvnData())) {
				//yl:174565
				var check_date = Date.parseDate("01.01.2019", "d.m.Y");
				var env_date = Date.parseDate(evndata.Evn_setDate, "d.m.Y");
				var env_vizittype = evndata.VizitType_SysNick;
				if (env_date < check_date) {
					if (env_vizittype != "desease" && env_vizittype != "consulspec") {
						Ext6.Msg.alert(langs("Сообщение"), langs("Случай АПЛ с посещением, отличным от \"Обращение по поводу заболевания\" или \"Диспансерное наблюдение\", должен быть закрыт!"));
						me.formStatus = 'edit';
						return false;
					}
				} else {
					if (env_vizittype != "desease") {
						Ext6.Msg.alert(langs("Сообщение"), langs("Случай АПЛ с посещением, отличным от \"Обращение по поводу заболевания\", должен быть закрыт!"));
						me.formStatus = 'edit';
						return false;
					}
				}
			}
		}
		// создаём посещение, открываем на редактирование в правой части
		me.mask('Создание нового посещения...');
		Ext6.Ajax.request({
			url: '/?c=EvnPL&m=addEvnVizitPL',
			params: Object.assign({
				EvnPL_id: data.EvnPL_id,
				MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
				LpuSection_id: me.userMedStaffFact.LpuSection_id,
				LpuSectionProfile_id: me.userMedStaffFact.LpuSectionProfile_id,
				MedPersonal_id: me.userMedStaffFact.MedPersonal_id,
				TimetableGraf_id: me.TimetableGraf_id || null,
				EvnDirection_id: me.EvnDirection_id || null
			}, me.ignoreParams),
			callback: function(opt,success,response) {
				me.formStatus = 'edit';
				if (success) {
					var response_obj = Ext6.decode(response.responseText);

					if (response_obj) {
						var msg = response_obj.Alert_Msg,
							params = opt.params;

						if (response_obj.Error_Msg == 'YesNo') {
							Ext6.Msg.show({
								buttons: Ext6.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										if (Ext6.isEmpty(me.ignoreParams[response_obj.ignoreParam])) {
											me.ignoreParams[response_obj.ignoreParam] = 1;
										}
										me.addEvnVizitPL(params);
									} else {
										me.unmask();
									}
								},
								icon: Ext6.MessageBox.QUESTION,
								msg: msg,
								title: langs(' Продолжить сохранение?')
							});
						}
						else if(response_obj.success === false) {
							me.unmask();//сообщение об ошибке откроется
						} else {
							me.loadTree({
								callback: function() {
									me.unmask();
									me.loadEmkViewPanel('EvnPL', data.EvnPL_id, '');
									me.timerActivate();
								}
							});
						}
					}
					if (response_obj.EvnVizitPL_id) {
						me.checkAndOpenRepositoryObserv(response_obj.EvnVizitPL_id);
					}
				}
			}
		});
	},
	// добавление нового посещения в стомат. ТАП
	addEvnVizitPLStom: function(data) {
		var me = this;
		if (me.formStatus === 'save') {
			return false;
		}
		me.formStatus = 'save';
		
		// создаём посещение, открываем на редактирование в правой части
		me.mask('Создание нового посещения...');
		Ext6.Ajax.request({
			url: '/?c=EvnPLStom&m=addEvnVizitPLStom',
			params: Object.assign({
				EvnPLStom_id: data.EvnPLStom_id,
				MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
				LpuSection_id: me.userMedStaffFact.LpuSection_id,
				LpuSectionProfile_id: me.userMedStaffFact.LpuSectionProfile_id,
				MedPersonal_id: me.userMedStaffFact.MedPersonal_id,
				TimetableGraf_id: me.TimetableGraf_id || null,
				EvnDirection_id: me.EvnDirection_id || null
			}, me.ignoreParams),
			callback: function(opt,success,response) {
				me.formStatus = 'edit';
				if (success) {
					var response_obj = Ext6.decode(response.responseText);

					if (response_obj) {
						var msg = response_obj.Alert_Msg,
							params = opt.params;

						if (response_obj.Error_Msg == 'YesNo') {
							Ext6.Msg.show({
								buttons: Ext6.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										if (Ext6.isEmpty(me.ignoreParams[response_obj.ignoreParam])) {
											me.ignoreParams[response_obj.ignoreParam] = 1;
										}
										me.addEvnVizitPLStom(params);
									} else {
										me.unmask();
									}
								},
								icon: Ext6.MessageBox.QUESTION,
								msg: msg,
								title: langs(' Продолжить сохранение?')
							});
						} else if(response_obj.success === false) {
							me.unmask();//сообщение об ошибке откроется
						} else {
							me.loadTree({
								callback: function() {
									me.unmask();
									me.loadEmkViewPanel('EvnPLStom', data.EvnPLStom_id, '');
									me.timerActivate();
								}
							});
						}
					}
				}
			}
		});
	},
	//добавление/открытие карты ДВН
	openEvnPLDispDop13: function(opts, butt) {
		var me = this;
		if (!opts) {
			opts = {};
		}
		//~ butt.disable();
		//~ me.mask(langs('Пожалуйста подождите...'));
		
		if(Ext6.isEmpty(opts.object_value)) {//новая карта двн, открываем без создания узла в дереве
			me.loadEmkViewPanel(opts.object,
				opts.object_value,
				opts.object_id,
				opts.dataToLoad,
				null);
		} else {//загрузить форму
			me.loadTree({
				callback: function () {
					me.loadEmkViewPanel(
						opts.object,
						opts.object_value,
						opts.object_id,
						opts.dataToLoad,
						null);
				}
			});
		}
	},
	//добавление карты первичного онкоскрининга
	addNewEvnPLDispScreenOnko: function(opts, butt) {
		var me = this;
		if (!opts) {
			opts = {};
		}
		//butt.disable();
		me.mask(langs('Пожалуйста подождите...'));
		var onComplete = function (data) {
			//button.enable();
			//butt.enable();
			if (data.Alert_Msg) {
				Ext6.Msg.alert(langs('Уведомление'), data.Alert_Msg);
			}
			if (typeof me.onSaveEvnDocument == 'function') {
				me.onSaveEvnDocument(true, data, tapSysNick);
			}
			// т.к. было обслужено
			if (me.EvnDirectionData) {
				me.EvnDirectionData = null;
			}
			if (me.TimetableGraf_id) {
				me.TimetableGraf_id = null;
			}
			me.loadTree({
				callback: function () {
					me.treePanel.collapse();
					me.loadEmkViewPanel(tapSysNick, data[tapSysNick + '_id'], '');
				}
			});
		};

		Ext6.Ajax.request({
			url: '/?c=EvnPLDispScreenOnko&m=checkEvnPLDispScreenOnkoExists',
			params: {
				Person_id: me.Person_id
			},
			callback: function(options, success, response) {
				me.unmask();

				var response_obj = Ext6.decode(response.responseText);

				if (response_obj.length) {
					sw.swMsg.show({
						buttons: sw.swMsg.YESNO,
						fn: function(buttonId, text, obj) {
							//butt.enable();
							if (buttonId == 'yes') {
								me.loadEmkViewPanel('EvnPLDispScreenOnko', response_obj[0], '');
							}
						}.createDelegate(this),
						icon: Ext6.MessageBox.QUESTION,
						msg: langs('У пациента есть пройдённый осмотр по онкологии. Открыть?'),
						title: langs('Вопрос')
					});
				} else {
					me._addNewEvnPLDispScreenOnko(opts, butt);
				}
			}
		});

	},
	_addNewEvnPLDispScreenOnko: function(opts, butt) {
		var me = this;
		me.mask(langs('Создание нового первичного онкоскрининга...'));
		console.log('me11',me);
		console.log('me.EvnPLForm.EvnUslugaPanel.Evn_id11',me.EvnPLForm.EvnUslugaPanel.Evn_id);
		Ext6.Ajax.request({
			url: '/?c=EvnPLDispScreenOnko&m=addEvnPLDispScreenOnko',
			params: {
				PersonEvn_id: me.PersonEvn_id,
				Server_id: me.Server_id,
				Lpu_id: getGlobalOptions().lpu_id,
				Evn_pid: me.EvnPLForm.EvnUslugaPanel.Evn_id, //потом переделать, брать из нужной панели
			},
			callback: function(options, success, response) {
				me.unmask();
				//butt.enable();

				var response_obj = Ext6.JSON.decode(response.responseText);
				if (response_obj.success) {

					me.loadTree({
						callback: function () {
							//~ me.treePanel.collapse();
							me.loadEmkViewPanel('EvnPLDispScreenOnko', response_obj['EvnPLDispScreenOnko_id'], ''); // вызывает окно с окноскринингом
						}
					});
				}
			}
		});

	},
	addNewEvnPLAndEvnVizitPL: function (opts, butt) {

		var me = this;
		if (!opts) {
			opts = {};
		}
		if (me.userMedStaffFact.MedStaffFactCache_IsDisableInDoc == 2) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Текущее рабочее место запрещено для выбора в документах'));
			return false;
		}
		if(getRegionNick() == 'ufa') {
			var lpusection_oid = me.userMedStaffFact.LpuSection_id;
			var LpuSectionAge_id = me.userMedStaffFact.LpuSectionAge_id;
			var person_age = -1, 
				PersonInfo = me.PersonInfoPanel;
			if(PersonInfo) {
				person_age = PersonInfo.getFieldValue('Person_Age');
			}
			if(lpusection_oid&&person_age!=-1){
				if(!opts.ignoreLpuSectionoidAgeCheck&& ((LpuSectionAge_id == 1 && person_age <= 17) || (LpuSectionAge_id == 2 && person_age >= 18))) {
					sw.swMsg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								opts.ignoreLpuSectionoidAgeCheck = true;
								this.addNewEvnPLAndEvnVizitPL(opts);
							}
						}.createDelegate(this),
						icon: Ext6.MessageBox.QUESTION,
						msg: lang['vozrastnaya_gruppa_otdeleniya_ne_sootvetstvuyut_vozrastu_patsienta_prodoljit'],
						title: lang['vopros']
					});

					return false;
				}
			}
		}
		var button,
			isStom = opts.isStom || false,
			tapSysNick,
			//curNode = tree.getSelectionModel().getSelectedNode(),
			vizitSysNick,
			yes_handler = function () {
				me.addNewEvnPLAndEvnVizitPL(opts);
			};

		if (isStom) {
			//button = this.Actions.action_New_EvnPLStom;
			tapSysNick = 'EvnPLStom';
			vizitSysNick = 'EvnVizitPLStom';
		} else {
			//button = this.Actions.action_New_EvnPL;
			tapSysNick = 'EvnPL';
			vizitSysNick = 'EvnVizitPL';
		}
		//button.disable();

		// экшн создания случая может быть не только в ЭМК
		//if (butt) butt.disable();

		me.mask(langs('Пожалуйста подождите...'));
		var d = new Date();
		var op = getGlobalOptions();
		var formParams = {
			action: 'add' + tapSysNick,
			allowCreateEmptyEvnDoc: 2,
			MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: me.userMedStaffFact.LpuSection_id,
			MedPersonal_id: me.userMedStaffFact.MedPersonal_id,
			TimetableGraf_id: me.TimetableGraf_id,
			EvnDirection_id: opts.EvnDirection_id || null,
			EvnDirection_vid: opts.EvnDirection_id || null,
			EvnPrescr_id: opts.EvnPrescr_id || null,
			isMyOwnRecord: me.isMyOwnRecord, // true если запись создана врачём, который создаёт посещение.
			PersonEvn_id: me.PersonEvn_id,
			Person_id: me.Person_id,
			Server_id: me.Server_id
		};
		formParams[tapSysNick + '_id'] = 0;
		formParams[tapSysNick + '_IsFinish'] = 1;
		formParams[vizitSysNick + '_id'] = 0;
		formParams[vizitSysNick + '_setDate'] = op.date;
		formParams[vizitSysNick + '_setTime'] = d.format('H:i');
		if ((op.region && op.region.nick.inlist(['ufa', 'pskov', 'ekb']))) {
			formParams.EvnUslugaCommon_id = 0;
		}
		if (me.EvnDirectionData && me.EvnDirectionData.EvnDirection_id) {
			formParams.EvnDirection_id = me.EvnDirectionData.EvnDirection_id;
			formParams.EvnDirection_vid = me.EvnDirectionData.EvnDirection_id;
		}
		if (me.EvnDirectionData && me.EvnDirectionData.EvnPrescr_id) {
			formParams.EvnPrescr_id = me.EvnDirectionData.EvnPrescr_id;
		}
		var beforeComplete = function () {
			//if (butt) butt.enable();
		};
		var onComplete = function (data) {
			//button.enable();
			if (data.Alert_Msg) {
				Ext6.Msg.alert(langs('Уведомление'), data.Alert_Msg);
			}
			if (typeof me.onSaveEvnDocument == 'function') {
				me.onSaveEvnDocument(true, data, tapSysNick);
			}
			// т.к. было обслужено
			if (me.EvnDirectionData) {
				me.EvnDirectionData = null;
			}
			if (me.TimetableGraf_id) {
				me.TimetableGraf_id = null;
			}
			me.loadTree({
				callback: function () {
					// историю лечения надо свернуть refs #138532
					me.treePanel.collapse();
					me.loadEmkViewPanel(tapSysNick, data[tapSysNick + '_id'], '');
				}
			});
			if (data.EvnVizitPL_id) {
				me.checkAndOpenRepositoryObserv(data.EvnVizitPL_id);
			}
		};

		if (me.EvnDirectionData && me.EvnDirectionData.useCase) {
			opts.useCase = me.EvnDirectionData.useCase;
		}

		var getEvnDirectionData = function (data, callback) {
			formParams[tapSysNick + '_NumCard'] = data[tapSysNick + '_NumCard'] || null;
			if (formParams.TimetableGraf_id || formParams.EvnDirection_id) {
				me.mask(langs('Получение данных направления...'));
				Ext6.Ajax.request({
					params: {
						TimetableGraf_id: formParams.TimetableGraf_id || null,
						EvnDirection_id: formParams.EvnDirection_id || null
						, Person_id: me.Person_id
						, useCase: opts.useCase || 'load_data_for_auto_create_tap'
					},
					callback: function (options, success, response) {
						me.unmask();
						if (success) {
							var response_obj = Ext6.JSON.decode(response.responseText);
							if (me.HomeVisit_id && me.allowHomeVisit == true) {
								formParams.HomeVisit_id = me.HomeVisit_id;

								me.serviceTypeStore.clearFilter();
								var index = me.serviceTypeStore.findBy(function (rec) {
									return (rec.get('ServiceType_SysNick') == 'home');
								});
								if (index == -1) {
									Ext6.Msg.alert(langs('Сообщение'), 'Ошибка при получении идентификатора места обслуживания');
									return false;
								}
								formParams.ServiceType_id = me.serviceTypeStore.getAt(index).get('ServiceType_id');
								me.allowHomeVisit = false;
							}
							formParams[tapSysNick + '_IsWithoutDirection'] = 1;
							if (response_obj.length > 0) {
								formParams.EvnDirection_id = response_obj[0].EvnDirection_id;
								formParams.EvnDirection_vid = response_obj[0].EvnDirection_id;
								formParams.EvnDirection_IsAuto = response_obj[0].EvnDirection_IsAuto;
								formParams.EvnDirection_IsReceive = response_obj[0].EvnDirection_IsReceive;
								formParams.Lpu_fid = response_obj[0].Lpu_sid;
								formParams.Lpu_id = op.lpu_id;
								formParams.Diag_id = response_obj[0].Diag_id;
								if (2 != formParams.EvnDirection_IsAuto) {
									formParams.EvnDirection_Num = response_obj[0].EvnDirection_Num;
									formParams.EvnDirection_setDate = response_obj[0].EvnDirection_setDate;
									formParams.Diag_did = response_obj[0].Diag_id;
									formParams.Org_did = response_obj[0].Org_id;
									formParams.LpuSection_did = response_obj[0].LpuSection_id;
									formParams.MedStaffFact_did = response_obj[0].MedStaffFact_id;
								}
								formParams.PrehospDirect_id = sw.Promed.EvnDirectionAllPanel.calcPrehospDirectId(response_obj[0].Lpu_sid, response_obj[0].Org_id, response_obj[0].LpuSection_id, response_obj[0].EvnDirection_IsAuto);
								formParams[tapSysNick + '_IsWithoutDirection'] = sw.Promed.EvnDirectionAllPanel.isWithDirection(formParams.EvnDirection_id, formParams.EvnDirection_IsAuto, formParams.EvnDirection_IsReceive, formParams.EvnDirection_Num);
							}
							if (response_obj.length == 0 && formParams.EvnDirection_id) {
								Ext6.Msg.alert(langs('Ошибка'), langs('Данные направления не найдены'));
							} else {
								callback();
							}
						} else {
							Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при получении данных направления'));
						}
					},
					url: '/?c=EvnDirection&m=loadEvnDirectionList'
				});
			} else {
				formParams[tapSysNick + '_IsWithoutDirection'] = 1;
				callback();
			}
		};
		var VizitType_SysNick = opts.VizitType_SysNick || 'desease';
		if (me.EvnDirectionData && me.EvnDirectionData.VizitType_SysNick) {
			VizitType_SysNick = me.EvnDirectionData.VizitType_SysNick;
		}
		if (isStom && 'kareliya' == getRegionNick()) {
			VizitType_SysNick = 'desease';
		}
		var vizit_type_storage = new sw.Promed.LocalStorage({
			tableName: 'VizitType'
			, typeCode: 'int'
			, allowSysNick: true
			, loadParams: {params: {where: " where VizitType_SysNick = '" + VizitType_SysNick + "'"}}
			, onLoadStore: function () {
				me.unmask();
				formParams.VizitType_id = vizit_type_storage.getValueOfKeyByFirstRecord() || null;

				me.getEvnPLNumber(getEvnDirectionData, function () {
					formParams.isAutoCreate = 1;
					if (me.HomeVisit_id && me.allowHomeVisit == true) {
						formParams.HomeVisit_id = me.HomeVisit_id;
						formParams.ServiceType_id = 2; // Место: На дому
						if (getRegionNick() == 'ufa') {
							formParams.ServiceType_id = 12; // Место: На дому
						} else if (getRegionNick() == 'krym') {
							formParams.ServiceType_id = 18; // Место: На дому
						}
						me.allowHomeVisit = false;
					}
					// addNewEvnPLAndEvnVizitPL
					me.createNewEvnPLAndEvnVizitPL({formParams: formParams, onComplete: onComplete, beforeComplete: beforeComplete}, isStom);
				}, isStom);
			}
		});
		var service_type_storage = new sw.Promed.LocalStorage({
			tableName: 'ServiceType'
			, typeCode: 'int'
			, allowSysNick: true
			, loadParams: {params: {where: " where ServiceType_SysNick = 'polka'"}}
			, onLoadStore: function () {
				formParams.ServiceType_id = service_type_storage.getValueOfKeyByFirstRecord() || null;
				vizit_type_storage.load();
			}
		});
		var PayType_SysNick = getPayTypeSysNickOms();
		var pay_type_storage = new sw.Promed.LocalStorage({
			tableName: 'PayType'
			, typeCode: 'int'
			, allowSysNick: true
			, loadParams: {params: {where: " where PayType_SysNick = '" + PayType_SysNick + "'"}}
			, onLoadStore: function () {
				formParams.PayType_id = pay_type_storage.getValueOfKeyByFirstRecord() || null;
				service_type_storage.load();
			}
		});
		pay_type_storage.load();
	},
	getEvnPLNumber: function(callback, callback2, isStom) {
		var me = this;
		var url = isStom ? '/?c=EvnPLStom&m=getEvnPLStomNumber' : '/?c=EvnPL&m=getEvnPLNumber';
		me.mask(langs('Получение номера талона...'));
		Ext6.Ajax.request({
			callback: function(options, success, response) {
				me.unmask();
				if ( success ) {
					callback(Ext6.JSON.decode(response.responseText), callback2);
				}
				else {
					Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при получении номера талона'));
				}
			},
			url: url
		});
	},
	// Получение номера талона
	createNewEvnPLAndEvnVizitPL: function(conf, isStom) {
		var me = this,
			url = isStom ? '/?c=EvnPLStom&m=createEvnPLStom' : '/?c=EvnPL&m=saveEmkEvnPL';
		me.mask(langs('Создание нового посещения и нового случая лечения...'));
		Ext6.Ajax.request({
			params: conf.formParams,
			callback: function(options, success, response) {
				me.unmask();
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (response_obj.success) {
					if ( typeof conf.beforeComplete == 'function') {
						conf.beforeComplete();
					}
					conf.onComplete(response_obj, conf);
					me.timerActivate();
				} else if (response_obj.Error_Msg && !response_obj.Error_Msg.inlist(['YesNo', 'EvnVizitPLDouble'])) {
					var msg = response_obj.Error_Msg;

					if (response_obj.Error_Code == 112 && response_obj.addMsg) {
						var headMsg = 'Информация о пересечениях';
						var addMsg = escapeHtml(response_obj.addMsg);
						msg += '<br/> <a onclick="Ext6.Msg.alert(\' ' + headMsg +  ' \',\' ' + addMsg +  ' \');" href=\'#\' >Подробнее</a>';
					}

					//Ext6.Msg.alert('Ошибка', response_obj.Error_Msg);
					Ext6.Msg.show({
						buttons: Ext6.Msg.OK,
						icon: Ext6.Msg.WARNING,
						title: langs('Ошибка'),
						msg: msg
					});
					conf.beforeComplete();
				} else if (response_obj.Alert_Msg && response_obj.Error_Msg == 'YesNo') {
					var msg = response_obj.Alert_Msg;

					if (response_obj.Error_Code == 112 && response_obj.addMsg) {
						var headMsg = 'Информация о пересечениях';
						var addMsg = escapeHtml(response_obj.addMsg);
						msg += '<br/> <a onclick="Ext6.Msg.alert(\' ' + headMsg +  ' \',\' ' + addMsg +  ' \');" href=\'#\' >Подробнее</a>';
					}

					Ext6.Msg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								conf.formParams[response_obj.ignoreParam] = 1;
								me.createNewEvnPLAndEvnVizitPL(conf, isStom);
							} else {
								conf.beforeComplete();
							}
						},
						icon: Ext6.MessageBox.QUESTION,
						msg: msg,
						title: langs(' Продолжить сохранение?')
					});
				} else if ( response_obj.Alert_Msg && 'EvnVizitPLDouble' == response_obj.Error_Msg ) {
					getWnd('swEvnVizitPLDoublesWindow').show({
						EvnVizitPLDoublesData: response_obj.Alert_Msg,
						callback: function(data) {
							conf.formParams.EvnVizitPLDoublesData = data.EvnVizitPLDoublesData;
							me.createNewEvnPLAndEvnVizitPL(conf, isStom);
						}.createDelegate(this)
					});
				} else {
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					conf.beforeComplete();
				}
				var cmp = Ext6.ComponentQuery.query('[refId=create-new-apl]');
				if (cmp) {cmp[0].enable();}
			},
			url: url
		});
	},
	getEvnData: function(conf) {
		var me = this;
		me.mask('Получение данных...');
		Ext6.Ajax.request({
			params: conf.params,
			callback: function(options, success, response) {
				me.unmask();

				if ( success ) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if(response_obj.length > 0) {
						conf.callback(response_obj[0]);
						return true;
					}
				}

				Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при получении данных'));
			},
			url: conf.url
		});
	},
	copyEvnVizitPL: function(data) {
		var me = this;
		var onComplete = function(data) {
			if (data.Alert_Msg) {
				Ext6.Msg.alert(langs('Уведомление'), data.Alert_Msg);
			}
			if (typeof me.onSaveEvnDocument == 'function') {
				me.onSaveEvnDocument(true, data, 'EvnPL');
			}
			// т.к. было обслужено
			if (me.EvnDirectionData) {
				me.EvnDirectionData = null;
			}
			if (me.TimetableGraf_id) {
				me.TimetableGraf_id = null;
			}
			me.loadTree({
				callback: function () {
					me.loadEmkViewPanel('EvnPL', data['EvnPL_id'], '');
				}
			});
		};
		var op = getGlobalOptions();
		var plop = getPolkaOptions();
		var getEvnPLEvnVizitPLData = function(o, callback) {
			me.getEvnData({
				params: {
					EvnPL_id: data.EvnPL_id,
					EvnVizitPL_id: data.EvnVizitPL_id
				},
				callback: function(formParams) {
					var d = new Date();
					formParams.action = 'addEvnPL';
					formParams.EvnPL_id = 0;
					formParams.EvnPL_NumCard = o.EvnPL_NumCard;
					formParams.EvnPL_IsFinish = 1;
					formParams.DirectClass_id = null;
					formParams.DirectType_id = null;
					formParams.EvnPL_UKL = null;
					formParams.ResultClass_id = null;
					formParams.Lpu_oid = null;
					formParams.LpuSection_oid = null;
					formParams.EvnVizitPL_id = 0;
					formParams.EvnVizitPL_setDate = op.date;
					formParams.EvnVizitPL_setTime = d.format('H:i');
					formParams.MedStaffFact_id = me.userMedStaffFact.MedStaffFact_id;
					formParams.LpuSection_id = me.userMedStaffFact.LpuSection_id;
					formParams.MedPersonal_id = me.userMedStaffFact.MedPersonal_id;
					formParams.MedStaffFact_sid = null;
					formParams.VizitClass_id = null;
					formParams.TimetableGraf_id = me.TimetableGraf_id || null;
					formParams.EvnDirection_id = null;
					formParams.EvnDirection_vid = null;
					if (me.EvnDirectionData && me.EvnDirectionData.EvnDirection_id){
						formParams.EvnDirection_id = me.EvnDirectionData.EvnDirection_id;
						formParams.EvnDirection_vid = me.EvnDirectionData.EvnDirection_id;
					}
					if (me.EvnDirectionData && me.EvnDirectionData.EvnPrescr_id){
						formParams.EvnPrescr_id = me.EvnDirectionData.EvnPrescr_id;
					}
					if((op.region && op.region.nick.inlist(['ufa','pskov','ekb']))){
						formParams.EvnUslugaCommon_id = 0;
					}
					//копируем осмотр или создаем пустой документ
					if(formParams.EvnXml_id) {
						formParams.copyEvnXml_id = formParams.EvnXml_id;
						formParams.EvnXml_id = null;
					}
					formParams.allowCreateEmptyEvnDoc = 2;
					formParams.isAutoCreate = 1;

					// copyEvnVizitPL
					me.createNewEvnPLAndEvnVizitPL({formParams: formParams, onComplete: onComplete}, false);
				},
				url: '/?c=EvnPL&m=loadEmkEvnPLEditForm'
			});
		};

		me.getEvnPLNumber(getEvnPLEvnVizitPLData, Ext6.emptyFn, false);
	},
	copyEvnVizitPLStom: function(data) {
		var me = this;
		var onComplete = function(data, conf) {
			if (data.Alert_Msg) {
				Ext6.Msg.alert(langs('Уведомление'), data.Alert_Msg);
			}
			if (typeof me.onSaveEvnDocument == 'function') {
				me.onSaveEvnDocument(true, data, 'EvnPLStom');
			}
			if (conf.formParams.BitePersonType_id != null) {
				let params = {
					Person_id: me.Person_id,
					EvnVizitPLStom_id: data.EvnVizitPLStom_id,
					BitePersonType_id: conf.formParams.BitePersonType_id,
				};
				me.saveBitePersonType(params, me);
			}
			// т.к. было обслужено
			if (me.EvnDirectionData) {
				me.EvnDirectionData = null;
			}
			if (me.TimetableGraf_id) {
				me.TimetableGraf_id = null;
			}
			me.loadTree({
				callback: function () {
					me.loadEmkViewPanel('EvnPLStom', data['EvnPLStom_id'], '');
				}
			});
		};

		// заглушка
		var beforeComplete = Ext6.emptyFn;

		var op = getGlobalOptions();
		var getEvnPLEvnVizitPLData = function(o, callback) {
			me.mask("Копирование случая...");
			Ext6.Ajax.request({
				url: '/?c=EvnPLStom&m=getEvnDiagPLStom',
				params: {
					EvnPLStom_id: data.EvnPLStom_id,
					EvnVizitPLStom_id: data.EvnVizitPLStom_id
				},
				callback: function(options, success, response) {
					me.unmask();
					if (success) {
						if (response && response.responseText) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result && result[0] && result[0].Error_Msg) {
								Ext6.Msg.alert(langs('Ошибка'), result[0].Error_Msg);
								return false;
							}
							var EvnDiagPLStom_id = '';
							var getEvnData = function(data,o,op,EvnDiagPLStom_id,form) {
								me.getEvnData({
									params: {
										EvnPLStom_id: data.EvnPLStom_id,
										EvnVizitPLStom_id: data.EvnVizitPLStom_id
									},
									callback: function (formParams) {
										var d = new Date();
										formParams.EvnPLStom_id = 0;
										formParams.EvnPLStom_NumCard = o.EvnPLStom_NumCard;
										formParams.EvnPLStom_IsFinish = 1;
										formParams.DirectClass_id = null;
										formParams.DirectType_id = null;
										formParams.EvnPLStom_UKL = null;
										formParams.ResultClass_id = null;
										formParams.Lpu_oid = null;
										formParams.LpuSection_oid = null;
										formParams.EvnVizitPLStom_id = 0;
										formParams.EvnVizitPLStom_setDate = op.date;
										formParams.EvnVizitPLStom_setTime = d.format('H:i');
										formParams.MedStaffFact_id = form.userMedStaffFact.MedStaffFact_id;
										formParams.LpuSection_id = form.userMedStaffFact.LpuSection_id;
										formParams.LpuSectionProfile_id = form.userMedStaffFact.LpuSectionProfile_id;
										formParams.MedPersonal_id = form.userMedStaffFact.MedPersonal_id;
										formParams.MedStaffFact_sid = null;
										formParams.VizitClass_id = null;
										formParams.TimetableGraf_id = form.TimetableGraf_id || null;
										formParams.EvnDirection_id = null;
										formParams.EvnDirection_vid = null;
										formParams.EvnPLStom_IsUnlaw = null;
										formParams.EvnPLStom_IsSurveyRefuse = null;
										formParams.EvnPLStom_IsUnport = null;
										formParams.EvnPLStom_IsCons = null;
										formParams.PrehospTrauma_id = null;
										formParams.ResultDeseaseType_id = null;
										formParams.LeaveType_fedid = null;
										formParams.ResultDeseaseType_fedid = null;
										formParams.MedicalCareKind_id = null;
										formParams.PrehospDirect_id = null;
										formParams.EvnDirection_Num = null;
										formParams.Org_did = null;
										formParams.Lpu_did = null;
										formParams.LpuSection_did = null;
										formParams.MedStaffFact_did = null;
										formParams.Diag_did = null;
										formParams.Diag_preid = null;
										formParams.EvnDirection_IsAuto = null;
										formParams.EvnDirection_IsReceive = null;
										formParams.Lpu_fid = null;
										formParams.EvnPrescr_id = null;
										formParams.Diag_fid = null;
										formParams.Diag_lid = null;
										if ((op.region && op.region.nick.inlist(['ufa', 'pskov', 'ekb']))) {
											formParams.EvnUslugaCommon_id = 0;
										}
										formParams.Mes_id = null;
										//копируем осмотр или создаем пустой документ
										if (formParams.EvnXml_id) {
											formParams.copyEvnXml_id = formParams.EvnXml_id;
											formParams.EvnXml_id = null;
										}
										formParams.allowCreateEmptyEvnDoc = 2;
										formParams.isAutoCreate = 1;
										formParams.copyEvnDiagPLStom = 1;
										formParams.EvnDiagPLStom_ids = EvnDiagPLStom_id;
										// copyEvnVizitPL
										me.afterCopyEvnVizitPLStom = true;
										me.createNewEvnPLAndEvnVizitPL({
											formParams: formParams,
											onComplete: onComplete,
											beforeComplete: beforeComplete
										}, true);
									},
									url: '/?c=EvnPLStom&m=loadEmkEvnPLStomEditForm'
								});
							};
							if(result){
								if(result.length > 1) {
									var prms = {};
									prms.store = result;
									prms.callback = function(dt) {
										if((typeof dt == 'object') && dt.length > 0){
											for(var i=0;i<dt.length;i++){
												if(dt[i].get('EvnDiagPLStom_id') && dt[i].get('EvnDiagPLStom_id') > 0)
													EvnDiagPLStom_id += (dt[i].get('EvnDiagPLStom_id')+',');
											}
										}
										getEvnData(data,o,op,EvnDiagPLStom_id,me);
									};
									getWnd('swSelectEvnDiagPLStomWindow').show(prms);
								} else if(result[0] && result[0].EvnDiagPLStom_id) {
									EvnDiagPLStom_id = result[0].EvnDiagPLStom_id;
									getEvnData(data,o,op,EvnDiagPLStom_id,me);
								}
							}
						}
					}
				}
			});
		};
		me.getEvnPLNumber(getEvnPLEvnVizitPLData, Ext6.emptyFn, true);
	},
	saveBitePersonType: function(conf, me) {
		Ext6.Ajax.request({
			url: '/?c=EvnPLStom&m=saveBitePersonType',
			params: {
				Person_id: conf.Person_id,
				EvnVizitPLStom_id: conf.EvnVizitPLStom_id,
				BitePersonData_setDate: new Date().format('d.m.Y'),
				BitePersonType_id: conf.BitePersonType_id
			},
			success: function() {
				sw4.showInfoMsg({
					panel: me,
					type: 'success',
					text: 'Данные сохранены.'
				});
			},
			failure: function() {
				sw4.showInfoMsg({
					panel: me,
					type: 'error',
					text: 'Ошибка сохранения данных.'
				});
			}
		});
	},
	checkOnOnlyOneVizitExist: function(options) {
		var me = this;
		var isStom = me.userMedStaffFact.ARMType.inlist(['stom', 'stom6']);

		Ext6.Ajax.request({
			url: '/?c=Evn&m=checkOnOnlyOneExist',
			params: {
				EvnVizitPL_id: options.id,
				isStom: isStom
			},
			callback: function(opt, success, response) {
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (success && response_obj.count) {
					var question;
					if ( isStom ) {
						question = lang['udalit_poseschenie_stomatologii_patsientom_polikliniki'];
					} else {
						question = lang['udalit_poseschenie_patsientom_polikliniki'];
					}
					question += lang['budet_udalen_ves_sluchay_apl'];

					Ext6.Msg.show({
						title: 'Вопрос',
						msg: question,
						buttons: Ext6.Msg.YESNO,
						icon: Ext6.Msg.QUESTION,
						fn: function(btn) {
							if (btn === 'yes') {
								me.deleteEvnPL({EvnPL_id: response_obj.EvnVizitPL_pid}, true);
							}
						}
					});
				} else {
					options.callback(options.id);
				}
			}
		})
	},
	deleteEvnVizitPL: function(data, withoutMessage, param) {//yl:удаление одного посещения
		var me = this;
		var deleteEvnVizitPL = function () {
			me.mask('Удаление посещения...');
			var params = {
				Evn_id: data.EvnVizitPL_id,
				ignoreDoc: 1
			};
			Ext.apply(params, param);//yl: добавить дополнительные параметры
			Ext6.Ajax.request({
				url: '/?c=Evn&m=deleteEvn',
				params: params,
				callback: function (opt, success, response) {
					me.unmask();
					if (success && response && response.responseText && (response_obj = Ext6.JSON.decode(response.responseText))) {
						if (response_obj.success) {
							me.loadTree();
							if (me.EvnPLForm) {
								me.EvnPLForm.loadData();
							}
						} else if (response_obj.Alert_Code) {//yl:пришёл код ошибки
							switch (response_obj.Alert_Code) {
								case 808://yl:есть обслуженные вызовы на дом -> сменить статус
									me.resolveDeleteAlert({
										msg: response_obj.Alert_Msg,
										url: '/?c=HomeVisit&m=RevertHomeVizitStatus',
										params: {
											Evn_id: data.EvnVizitPL_id,
											MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id
										},
										callback: 'deleteEvnVizitPL',
										data: data //EvnVizitPL_id
									});
									break;

								default:
									sw.swMsg.alert(langs('Ошибка'), response_obj.Alert_Msg);
									break;
							}
						}
					}
				}
			})
		};

		if (withoutMessage) {
			deleteEvnVizitPL();
		} else {
			checkDeleteRecord({
				callback: deleteEvnVizitPL
			});
		}

	},
	deleteEvnVizitPLStom: function(data) {
		var me = this;
		checkDeleteRecord({
			callback: function () {
				me.mask('Удаление стомат. посещения...');
				Ext6.Ajax.request({
					url: '/?c=Evn&m=deleteEvn',
					params: {
						Evn_id: data.EvnVizitPLStom_id,
						ignoreDoc: 1
					},
					callback: function(opt, success, response) {
						me.unmask();
						if (success && response.responseText != '') {
							var response_obj = Ext6.JSON.decode(response.responseText);
							if (response_obj.success) {
								me.loadTree();
								if (me.EvnPLStomForm) {
									me.EvnPLStomForm.loadData();
								}
							}
						}
					}
				})
			}
		});
	},
	deleteEvnPL: function(data, withoutMessage, param) {//yl:удаление всего АПЛ-случая
		var me = this;
		var deleteEvnPL = function () {
			me.mask('Удаление ТАП...');
			var params={
				Evn_id: data.EvnPL_id,
				MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id
			};
			Ext.apply(params, param);//yl: добавить дополнительные параметры
			Ext6.Ajax.request({
				url: '/?c=Evn&m=deleteFromArm',
				params: params,
				callback: function(opt, success, response) {
					me.unmask();
					if (success && response && response.responseText && (response_obj = Ext6.JSON.decode(response.responseText))) {
						if (response_obj.success) {
							me.loadTree({
								callback: function () {
									me.unmask();
									if (me.currentNode && me.currentNode.object == 'EvnPL' && me.currentNode.object_value == data.EvnPL_id) {
										me.loadEmkViewPanel('Person', me.Person_id, '');
									}
								}
							});
						}else if(response_obj.Alert_Code){//yl:пришёл код ошибки
							switch (response_obj.Alert_Code) {
								case 809://yl:есть обслуженные вызовы на дом -> сменить статус
									me.resolveDeleteAlert({
										msg: response_obj.Alert_Msg,
										url: '/?c=HomeVisit&m=RevertHomeVizitStatusesTAP',
										params: {
											Evn_id: data.EvnPL_id,
											MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id
										},
										callback: 'deleteEvnPL',
										data: data //EvnPL_id
									});
									break;

								default:
									sw.swMsg.alert(langs('Ошибка'), response_obj.Alert_Msg);
									break;
							}
						}
					}else{
						sw.swMsg.alert(langs("Ошибка"), "При удалении случая возникли ошибки");
					}
				}
			});
		};

		if ( withoutMessage ) {
			deleteEvnPL();
		} else {
			checkDeleteRecord({
				callback: deleteEvnPL
			}, 'случай АПЛ');
		}
	},
	//yl:обработчик ошибок удаления посещения и АПЛ
	resolveDeleteAlert: function (param) {
		var me = this;
		sw.swMsg.show({
			title: langs("Вопрос"),
			msg: param.msg,
			icon: Ext.MessageBox.QUESTION,
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, scope) {
				if (buttonId == "yes") {
					Ext.Ajax.request({
						url: param.url,
						params: param.params,
						callback: function (options, success, response) {
							if (success && response && response.responseText && (resp = Ext.util.JSON.decode(response.responseText))) {
								if (Ext.isEmpty(resp.Error_Msg)) {
									//yl:повторный вызов после измененения статусов без вопроса с игнором домашних посещений
									me[param.callback](param.data, true, {ignoreHomeVizit: true});
								} else {
									sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
								}
							} else {
								sw.swMsg.alert(langs("Ошибка"), "При измененении статусов вызовов на дом возникли ошибки");
							}
						}
					});
				}
			}.createDelegate(this)
		});
	},
	deleteEvnPLStom: function(data, withoutMessage) {
		var me = this;
		var deleteEvnPLStom = function(){
			me.mask('Удаление ТАП...');
			Ext6.Ajax.request({
				url: '/?c=Evn&m=deleteFromArm',
				params: {
					Evn_id: data.EvnPLStom_id,
					MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id
				},
				callback: function(opt, success, response) {
					me.unmask();
					if (success && response.responseText != '') {
						var response_obj = Ext6.JSON.decode(response.responseText);
						if (response_obj.success) {
							me.loadTree({
								callback: function () {
									me.unmask();
									if (me.currentNode && me.currentNode.object == 'EvnPLStom' && me.currentNode.object_value == data.EvnPLStom_id) {
										me.loadEmkViewPanel('Person', me.Person_id, '');
									}
								}
							});
						}
					}
				}
			});
		};

		if ( withoutMessage ) {
			deleteEvnPLStom();
		} else {
			checkDeleteRecord({
				callback: deleteEvnPLStom
			}, 'случай АПЛ');
		}
	},
	deleteEvnPLDispDop13: function(options, withoutMessage) {
		var me = this;
		var doDeleteEvnPLDispDop13 = function() {
			me.mask('Удаление карты ДВН...');
			let params = {
				EvnPLDispDop13_id: options.EvnPLDispDop13_id
			};

			if (options.ignoreCheckRegistry) {
				params.ignoreCheckRegistry = 1;
			}

			Ext.Ajax.request({
				callback: function(opts, success, response) {
					me.unmask();
					if ( success && response.responseText != '' ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.success == false ) {
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при удалении карты ДВН'));
						}
						else if (response_obj.Alert_Msg) {
							sw.swMsg.show({
								icon: Ext6.MessageBox.QUESTION,
								msg: response_obj.Alert_Msg + ' Продолжить?',
								title: langs('Подтверждение'),
								buttons: Ext6.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ('yes' == buttonId) {
										options.ignoreCheckRegistry = true;
										win.deleteEvnPLDispDop13(options, true);
									}
								}
							});
						}
						else {
							me.loadTree({
								callback: function () {
									me.unmask();
									if (me.currentNode && me.currentNode.object == 'EvnPLDispDop13' && me.currentNode.object_value == options.EvnPLDispDop13_id) {
										me.loadEmkViewPanel('Person', me.Person_id, '');
									}
								}
							});
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При удалении карты ДВН возникли ошибки'));
					}
				},
				params: params,
				url: '/?c=EvnPLDispDop13&m=deleteEvnPLDispDop13'
			});
		};
		
		if ( withoutMessage ) {
			doDeleteEvnPLDispDop13();
		} else {
			checkDeleteRecord({
				callback: doDeleteEvnPLDispDop13
			}, 'карту ДВН');
		}
	},
	// менюшка для истории
	showHistoryMenu: function(t, object, object_value, accessType, canCreateVizit) {
		log('showHistoryMenu', {t: t, object: object, object_value: object_value});

		var me = this,
			isDead = me.PersonInfoPanel.checkIsDead();
		switch(object) {
			case 'EvnPL':
				var menu = Ext6.create('Ext6.menu.Menu', {
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'Добавить посещение',
						disabled: (!canCreateVizit || isDead),
						handler: function() {
							me.addEvnVizitPL({
								EvnPL_id: object_value
							});
						}
					}, {
						text: 'Удалить случай АПЛ',
						disabled: (accessType != 'edit' || isDead),
						handler: function() {
							me.deleteEvnPL({
								EvnPL_id: object_value
							});
						}
					}]
				});

				menu.showBy(t);
				break;
			case 'EvnPLStom':
				var menu = Ext6.create('Ext6.menu.Menu', {
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'Добавить посещение',
						disabled: (!canCreateVizit || isDead),
						handler: function() {
							me.addEvnVizitPLStom({
								EvnPLStom_id: object_value
							});
						}
					}, {
						text: 'Удалить случай АПЛ',
						disabled: (accessType != 'edit' || isDead),
						handler: function() {
							me.deleteEvnPLStom({
								EvnPLStom_id: object_value
							});
						}
					}]
				});

				menu.showBy(t);
				break;
			case 'EvnPLDispDop13':
				var menu = Ext6.create('Ext6.menu.Menu', {
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'Удалить карту диспансеризации',
						disabled: (accessType != 'edit' || isDead),
						handler: function() {
							me.deleteEvnPLDispDop13({
								EvnPLDispDop13_id: object_value
							});
						}
					}]
				});

				menu.showBy(t);
				break;
		}
	},
	ExpandHospitalItems: function(t) {
		var caseContainer = Ext6.get(t.offsetParent);

		caseContainer.child('.baloon').select('li').show();
		caseContainer.child('.date').select('li').show();
		caseContainer.child('.baloon').select('li').animate({
			duration: 400,
			to:{
				opacity: 1
			}
		});
		caseContainer.child('.date').select('li').animate({
			duration: 400,
			to:{
				opacity: 1
			}
		});
		caseContainer.select('.case-expand').hide();
	},

    // создание формы
    createEmkRightPanel: function(params, dataToLoad) {
		switch (params.object) {
			case 'EvnPLStom':
				var folder = params.object;
				break;
			default:
				var folder = 'EMK';
				break;
		}

	    var win = this,
			formName = params.object +"Form",
	    	className = "common."+folder+"."+ formName;

		sw4.addToPerfLog({
			window: win.objectClass,
			panel: className,
			type: 'beforefetch'
		});

		Ext6.require(className, function() {

			sw4.addToPerfLog({
				window: win.objectClass,
				panel: className,
				type: 'afterfetch'
			});

			win[formName] = Ext6.create(className, {
				ownerPanel: win.rightPanel,
				ownerWin: win
			});

			win.rightPanel.add(win[formName]);

			win.isLoading = false;
			win.loadEmkViewPanel(params.object, params.object_value, params.object_id, dataToLoad);
			log('form created:', className);
			win._setFormAllowed();
		});
    },

    // проверка ТАП на входящие в него документы на возможность подписания
    checkSignedEvnContent: function(params) {

        var win = this;
        var checkNeed = false;

        return new Promise(function(resolve, reject) {

            // только если эта проверка включена в настройках
            // только если мы переключаемся из открытых форм ТАП или стомат. ТАП
            if (
                getGlobalOptions().enableEmkEmdDocControls
                && win.displayedObject
                && (win.displayedObject === "EvnPL" || win.displayedObject === "EvnPLStom" )
            ) {

                var displayedForm = win.displayedObject +"Form";
                var curr_params = win[displayedForm].getParams();

                if (curr_params && curr_params[win.displayedObject + '_id']) {

                    // если происходит смена id объекта ТАП,
                    // выполняем валидацию контента ТАП с которого хотим переключится
                    if (!params || (params && curr_params[win.displayedObject + '_id'] != params.object_value)) {
                        checkNeed = true;
                    }
                }
            }

            if (checkNeed) {

                Ext6.Ajax.request({
                    params: {
                        object: win.displayedObject,
                        object_id: curr_params[win.displayedObject + '_id']
                    },
                    url: "/?c=EMD&m=checkSignedEvnContent",
                    success: function(resp) {

                        var response = JSON.parse(resp.responseText);
                        log('checkSignedEvnContent:', response);

                        if (response.length > 0 && response[0].text) {
                            Ext6.Msg.show({
                                buttons: Ext6.Msg.YESNO,
                                title: langs('Подписание документов РЭМД'),
                                msg: "Для случая лечения созданы документы, подлежащие регистрации в РЭМД:" + response[0].text,
                                icon: Ext6.MessageBox.WARNING,
                                fn: function (buttonId, text, obj) {
                                    if (buttonId == 'yes') {

                                        resolve({success: true});

                                    } else {
                                        resolve({success: false, response: response});
                                    }
                                },
                                buttonText: {
                                    yes: 'Пропустить',
                                    no: 'Подписать'
                                }
                            });
                        } else resolve({success: true});
                    },
                    failure: function(err) { resolve({success: true, error: err}); }
                })
            } else {
                resolve({success: true});
            }
        })
    },

    loadEmkRightPanelData: function(params){

	    var win = this,
            object = params.object,
            object_value = params.object_value,
            object_id = params.object_id,
            dataToLoad = params.dataToLoad;

        if (this.isLoading) return false;
        this.isLoading = true;

        win.clearNodeViewForm();
        win.rightPanel.mask(LOADING_MSG);

        this.setTreeState(object, object_value, object_id);

        if (['EvnPL', 'EvnPLStom', 'Person', 'EvnPLDispDop13', 'EvnPLDispScreenOnko'].in_array(object)) {

            var formName = object +"Form";

            // создаем форму, если не создана
            if (win[formName]) {
                log("form exist:", formName);
            } else {
                log("form not exist:", formName);
                win.createEmkRightPanel(params, dataToLoad);
                return;
            }

            win.rightPanel.unmask();
            win.rightPanel.setActiveItem(win[formName]);

            var loadParams = {
                callback: function() {
                	win.isLoading = false;
                	if (win.onViewPanelLoaded && typeof win.onViewPanelLoaded === 'function') {
                        win.onViewPanelLoaded(win);
                        // очищаем после выполнения
                        win.onViewPanelLoaded = null;
					}
                }
            };

            if (object !== "Person") {

                loadParams.dataToLoad = dataToLoad;
                loadParams.EvnVizitPL_id = params.EvnVizitPL_id;
                var setParams = {
                	Person_id: win.Person_id,
                	Server_id: win.Server_id,
					EvnVizitPL_id: params.EvnVizitPL_id
                };
                setParams[object + "_id"] = object_value;

                win[formName].setParams(setParams);

            } else {
                loadParams.Person_id = object_value;
            }

            win.displayedObject = object;
            win[formName].loadData(loadParams);

        } else {
            win.rightPanel.unmask();
            win.rightPanel.setActiveItem(0);
            win.loadEmkViewForm(object, object_value, object_id);
            win.displayedObject = object;
        }

    },

	// загрузка формы просмотра (новой)
	loadEmkViewPanel: function(object, object_value, object_id, dataToLoad, EvnVizitPL_id) {
        log('loadEmkViewPanel', {
            object: object,
            object_value: object_value,
            object_id: object_id
        });

        var win = this;
        var params = {};

        params.object = object;
        params.object_id = object_id ? object_id : object + '_id';
        params.object_value = object_value;
        params.user_MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id || null;
        params.Person_id = this.Person_id;
        params.dataToLoad = dataToLoad;
        params.EvnVizitPL_id = EvnVizitPL_id;

        // если у пользователя есть сертификаты для подписи
        // и у посещений входящих в ТАП есть неподписанные
        // осмотры или направления или документы или рецепты,
        // то предлагаем подписать их

        // 1. ожидание прогрузки аякс
        // 2. ожидание ответа на предупреждение (если есть)
        win.checkSignedEvnContent(params).then(function(promise){

            if (promise.success) {
                // продолжаем загрузку если проверка прошла упешно
                win.loadEmkRightPanelData(params);
            } else {
                // если проверка провалилась, не переключаем форму
                log('sign needeed', promise)
            }

        }).catch(function(err) {

            log('error', err);

            // если что то пошло не так в проверке, мы должны продолжить загрузку
            win.loadEmkRightPanelData(params);
        })
	},

	// загрузка формы просмотра (старой)
	loadEmkViewForm: function(object, object_value, object_id) {
		log('loadEmkViewForm', {
			object: object,
			object_value: object_value,
			object_id: object_id
		});

		var win = this;
		var params = {};
		switch(object) {
			case 'EvnReceptGeneral':
				params.object = 'EvnReceptGeneralView';
				break;
			case 'EvnRecept':
				params.object = 'EvnReceptView';
				break;
			case 'EvnDirection':
				getWnd('swEvnDirectionEditWindow').show({
					action: 'view',
					formParams: {},
					EvnDirection_id: object_value,
					Person_id: win.Person_id
				});
				win.clearTreeState();
				win.isLoading = false;
				return false; // нет для EvnDirection интерактивной формы
				break;
			case 'EvnPrescrMse':
				getWnd('swDirectionOnMseEditForm').show({
					formParams: {},
					EvnPrescrMse_id: object_value,
					Person_id: win.Person_id
				});
				win.clearTreeState();
				win.isLoading = false;
				return false;
				break;
			default:
				params.object = object;
				break;
		}
		switch(object) {
			case 'DispRefuse': return; break;
			case 'EvnPLDispDop13':
			case 'EvnPLDispProf':
			case 'EvnPLDispOrp':
			case 'EvnPLDispTeenInspection':
			case 'EvnPLDispDriver':
			case 'EvnPLDispMigrant':
				params.object_id = 'EvnPLDisp_id';
				break;
			default:
				if (object_id)
					params.object_id = object_id;
				else
				params.object_id = object + '_id';
				break;
		}
		params.object_value = object_value;
		params.user_MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id || null;
		params.Person_id = this.Person_id;

		if (params.object_id == 'Person_id') {
			params.object_value = this.Person_id;
			params.parent_object_value = params.object_value;
			params.parent_object_id = 'Person_id';
		}

		this.setTreeState(object, object_value, params.object_id);

		var loadMask = new Ext6.LoadMask({msg: LOADING_MSG, target: win.rightPanel});
		loadMask.show();
		Ext6.Ajax.request({
			url: '/?c=Template&m=getEvnForm',
			callback: function(opt, success, response) {
				win.isLoading = false;
				loadMask.hide();
				if (success && response.responseText != '') {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if ( response_obj.success ) {
						var tpl = new Ext6.XTemplate(response_obj.html);
						tpl.overwrite(win.htmlPanel.body, {});
						win.renderEMDButtons(win.htmlPanel.body);
						win.setObjectHandlers(response_obj.map, true);

						var parent = {};
						win.viewFormDataStore.completeFromMap(response_obj.map, parent);
					}
					else {
						// TODO: выдать ошибку
					}
				}
			},
			params: params
		});
	},
	renderEMDButtons: function(el) {
		if (getRegionNick() == 'kz') {
			return; // для Казахстана не нужна подпись refs #113642
		}

		if (this.emdButtons) {
			for (var k in this.emdButtons) {
				if (typeof this.emdButtons[k].destroy == 'function') {
					this.emdButtons[k].destroy();
				}
			}
		} else {
			this.emdButtons = [];
		}

		var me = this;
		// рендерим кнопку для подписи (компонент 6-го ExtJS).
		if (el && typeof el.query == 'function') {
			var els = el.query('div.emd-here');
			Ext6.each(els, function(domEl) {
				//#160801 если в элементе уже есть emd панель, дублировать не будем
				var emd = domEl.querySelector('.emd-panel');
				if(!emd){
					var el = Ext6.get(domEl);
					var swEMDPanel = Ext6.create('sw.frames.EMD.swEMDPanel', {
						renderTo: domEl,
						width: 40,
						height: 30
					});
					swEMDPanel.setParams({
						EMDRegistry_ObjectName: el.getAttribute('data-objectname'),
						EMDRegistry_ObjectID: el.getAttribute('data-objectid')
					});
					swEMDPanel.setIsSigned(el.getAttribute('data-issigned'));
					if (el.getAttribute('data-disabledsign') && el.getAttribute('data-disabledsign') == "1") {
						swEMDPanel.setReadOnly(true);
					}
					me.emdButtons.push(swEMDPanel);
				}
			});
		}
	},
	setTreeData: function(options, treeData) {
		var win = this;
		win.treeData = treeData;

		if (win.treeData.data) {
			for (var k in win.treeData.data) {
				var el = win.treeData.data[k];
				if (typeof el.HasOpenEvnStick == 'string') {
					win.treeData.data[k].HasOpenEvnStick = Number(el.HasOpenEvnStick);
				}
				if (el.objectSetDate) {
					win.treeData.data[k].objectSetDateFormatted = Date.parseDate(el.objectSetDate, 'd.m.Y').format('j.m.y');
					if (win.treeData.data[k].children) {
						for (var l in win.treeData.data[k].children) {
							var el_child = win.treeData.data[k].children[l];
							if (el_child.objectSetDate) {
								win.treeData.data[k].children[l].objectSetDateFormatted = Date.parseDate(el_child.objectSetDate, 'd.m.Y').format('j.m.y');
							}
						}
					}
				}

				if (el.objectDisDate) {
					win.treeData.data[k].objectDisDateFormatted = Date.parseDate(el.objectDisDate, 'd.m.Y').format('j.m.y');
				}
			}
		}

		win.groupHistory();
		win.renderLoadedTree();

		if (options && options.callback) {
			options.callback();
		}
	},
	onLoadPersonInfoPanel: function() {
		var win = this;
		var piPanel = win.PersonInfoPanel;
		var winTitle = 'Электронная медицинская карта';

		if (piPanel && piPanel.getFieldValue('Person_Surname')) {
			winTitle = piPanel.getFieldValue('Person_Surname');
			if (piPanel.getFieldValue('Person_Firname')) {
				winTitle = winTitle + ' ' + piPanel.getFieldValue('Person_Firname').substring(0, 1) + '.';
			}
			if (piPanel.getFieldValue('Person_Secname')) {
				winTitle = winTitle + ' ' + piPanel.getFieldValue('Person_Secname').substring(0, 1) + '.';
			}
		}
		win.setTitle(winTitle);

		setTimeout(function() {
			win.showPersonDispSignalViewData();
		}, 5000);
	},
	// загрузка всего одним запросом
	loadAll: function(options) {
		var win = this;
		var params = {};
		params.Person_id = this.Person_id;
		params.Server_id = this.Server_id;
		params.userMedStaffFact_id = this.userMedStaffFact.MedStaffFact_id || null;
		params.userLpuUnitType_SysNick = this.userMedStaffFact.LpuUnitType_SysNick || null;
		if (win.useArchive) {
			params.useArchive = 2;
		}

		if (win.openEvn) {
			params.object = win.openEvn.object;
			params.object_value = win.openEvn.object_value;
		}

		win.mask(LOADING_MSG);
		Ext6.Ajax.request({
			url: '/?c=EMK&m=getAll',
			callback: function(opt, success, response) {
				win.unmask();
				if (success && response.responseText != '') {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if (response_obj.personHistory) {
						win.setTreeData(options, response_obj.personHistory);
					}

					if (response_obj.personInfo) {
						win.PersonInfoPanel.load({
							Person_id: win.Person_id,
							Server_id: win.Server_id,
							userMedStaffFact: win.userMedStaffFact,
							PersonEvn_id: win.PersonEvn_id,
							dataToLoad: response_obj.personInfo,
							callback: function() {
								win.onLoadPersonInfoPanel();
							}
						});
					}

					if (win.openEvn) {
						win.loadEmkViewPanel(win.openEvn.object, win.openEvn.object_value, '', {
							evnPLData: response_obj.evnPLData,
							evnVizitPLData: response_obj.evnVizitPLData
						});

					} else if(options && options.callback && typeof options.callback=="function"){
						//yl:авто-создание случая с привязкой к вызову на дом
						if (!response_obj.personHistory) {//если setTreeData не вызывалась выше - вызову сам
							options.callback();
						}
					} else {
						// иначе открываем сигнальную информацию
						win.loadEmkViewPanel('Person', win.Person_id, '');
					}
				}
			},
			params: params
		});
	},
	reloadTree: function() {
		var win = this;
		var params = {};
		params.Person_id = this.Person_id;
		params.Server_id = this.Server_id;
		params.type = 0;
		if (win.useArchive) {
			params.useArchive = 2;
		}
		var expandedGroups = [];
		win.Tree.getEl().select('div.groupcase').each(function(el){
			if(el.dom && el.dom.innerHTML.includes('expanded')) {
				expandedGroups.push(el.dom);
			}
		});
		Ext6.Ajax.request({
			url: '/?c=EMK&m=getPersonHistory',
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if ( response_obj.data ) {
						var options = {};
						options.callback = function() {
							expandedGroups.forEach(function (el) {
								var content_node = win.Tree.getEl().query('[nodeid=' + el.getAttribute('nodeid') + '_content]');
								var arrow_node = win.Tree.getEl().query('[nodeid=' + el.getAttribute('nodeid') + ']');

								if (content_node && content_node[0] && arrow_node && arrow_node[0]) {
									var content = Ext6.get(content_node[0]);
									var arrow = Ext6.get(arrow_node[0]).child('b.groupcase_arrow');
									if (!content) return false;
									content.setVisibilityMode(Ext6.Element.DISPLAY);
									if (!content.isVisible()) {
										content.show();
										arrow.addCls('expanded');
										el.setAttribute('data-qtip','Свернуть');
									}
								}
							});
						};
						win.setTreeData(options, response_obj);
					}
				}
			},
			params: params
		});
		if(!Ext6.isEmpty(win.PersonInfoPanel)) {
			win.PersonInfoPanel.getDispClassListAvailable();//обновление статуса кнопки "Д"(диспансеризация)
		}
	},
	// загрузка дерева
	loadTree: function(options) {
		var win = this;
		var params = {};
		params.Person_id = this.Person_id;
		params.Server_id = this.Server_id;
		params.type = 1; // Для фильтрации направлений
		params.userMedStaffFact_id = this.userMedStaffFact.MedStaffFact_id || null;
		params.userLpuUnitType_SysNick = this.userMedStaffFact.LpuUnitType_SysNick || null;
		if (win.useArchive) {
			params.useArchive = 2;
		}

		var loadMask = new Ext6.LoadMask({msg: LOADING_MSG, target: win.Tree});
		loadMask.show();
		Ext6.Ajax.request({
			url: '/?c=EMK&m=getPersonHistory',
			callback: function(opt, success, response) {
				loadMask.hide();
				if (success && response.responseText != '') {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if ( response_obj.data ) {
						win.setTreeData(options, response_obj);
					}
				}
			},
			params: params
		});
		if(!Ext6.isEmpty(win.PersonInfoPanel)) {
			win.PersonInfoPanel.getDispClassListAvailable();//обновление статуса кнопки "Д"(диспансеризация)
		}
	},
	useArchive: false, // признак загрузки архивных случаев
	// отрисовка дерева после загрузки
	renderLoadedTree: function() {
		var win = this;
		if(!!this.currentNode) {
			// https://www.sencha.com/forum/showthread.php?286213-Ext-get()-throws-errors-if-the-element-has-the-same-id-as-a-recently-removed-element/page3
			if(Ext6.Element.cache.hasOwnProperty('case_' + this.currentNode.object + '_' +  this.currentNode.object_value)){
				Ext6.Element.cache['case_' + this.currentNode.object + '_' +  this.currentNode.object_value].destroy();
			}
		}

		var archiveButton = '<a href="#" class="archive-button">Показать архивные данные</a>';
		if (this.useArchive) {
			archiveButton = '<a href="#" class="archive-button">Скрыть архивные данные</a>';
		}

		var emdOuterButton = '';
		if (getGlobalOptions().enableEMD) {
			emdOuterButton = '<a href="#" class="emd-outer-button">Внешние ЭМД</a>';
		}

		var tpl = new Ext6.XTemplate(
			'<div class="history">',
			'<div class="case signalinfo" nodeid="case_Person_{Person_id}" object="Person" object_value="{Person_id}" object_id="">',
			'<b class="icon"></b>',
			'<span class=""><a href="#">Сигнальная информация</a></span>',
			'</div>',
			'<tpl for="Groups">',
			'<tpl if="Group_id != 0">',
			'<div class="groupcasemenu"><a href="#"></a></div>',
			'<div class="groupcase groupcase-{Group_SysNick}" nodeid="{Group_id}">',
			'<b class="groupcase_arrow"></b>',
			'<span class="groupcase_title" idgroup="{Group_id}">{Group_Name}</span>',
			'<span class="groupcase_count">{count}</span>',
			'</div>',
			'</tpl>',
			'<div class="groupcasecontent" nodeid="{Group_id}_content" {[values[\'Group_id\'] != 0 ? \"style=\\"display: none;\\"\" : ""]}>',
			'<tpl for="items">',
			'<tpl if="this.isDocAndVK(values)">',
			'<div class="doc case {cls}" nodeid="case_{object}_{object_id}" object="{object}" object_value="{object_id}" object_id="">',
			'<ul>',
			'{[this.renderDocObject(values)]}',
			'</ul>',
			'</div>',
			'<tpl else>',
			'<div class="case {cls}" nodeid="case_{object}_{object_id}" object="{object}" object_value="{object_id}" object_id="" accessType="{accessType}" canCreateVizit="{canCreateVizit}">',
			'<tpl if="this.isCaseCLs(cls) == true">',
			'<ul class="date">',
			'<tpl if="children.length">',
			'<tpl for="children">',
			'<li><a data-type="evnVizitPL" {[this.getNodeParams(parent,values,xindex,1)]} href="#">{objectSetDateFormatted}</a></li>',
			'</tpl>',
			'<tpl else>',
			'<li><a data-type="evnVizitPL" {[this.getNodeParams(parent,values,xindex,2)]} href="#">{objectSetDateFormatted}</a></li>',
			'<li><a data-type="evnVizitPL" {[this.getNodeParams(parent,values,xindex,2)]} href="#">{objectDisDateFormatted}</a></li>',
			'</tpl>',
			'</ul>',
			'<ul class="baloon long {balooncls}" >',
			'<tpl if="children.length == 1">',
			'<tpl for="children">',
			'<li class="{cls}" data-qtip="{[this.getQtipByEvnClass(parent,values,xindex,xcount)]}" style="height: 49px"></li>',
			'</tpl>',
			'<tpl else>',
			'<tpl for="children">',
			'<li class="{cls}" data-qtip="{[this.getQtipByEvnClass(parent,values,xindex,xcount)]}" ></li>',
			'</tpl>',
			'</tpl>',
			'</ul>',
			'<ul class="ICD10">',
			'<tpl if="EmkTitle">',
			'<li><a href="#">{EmkTitle}</a></li>',
			'<tpl else>',
			'<tpl if="Diag_Code != null"><li><a href="#"><strong>{Diag_Code}</strong>{Diag_Name}</a></li><tpl else><li><a href="#">Диагноз не установлен</a></li></tpl>',
			'</tpl>',
			'<li><a href="#">{Lpu_Nick}</a></li>',
			'</ul>',
			'<div class="casemenu"><a href="#" object="{object}" object_value="{object_id}"></a></div>',
			'<tpl else>',
			'<ul class="date">',
			'<tpl if="children.length">',
			'<tpl for="children">',
			'<li><a data-type="evnVizitPL" {[this.getNodeParams(parent,values,xindex,3)]} href="#">{objectSetDateFormatted}</a></li>',
			'</tpl>',
			'<tpl else>',
			'<li><a data-type="evnVizitPL" {[this.getNodeParams(parent,values,xindex,4)]} href="#">{objectSetDateFormatted}</a></li>',
			'</tpl>',
			'</ul>',
			'<ul class="baloon {balooncls}" >',
			'<tpl if="this.isLong(balooncls) == true && children.length &gt;= 2">',
			'<tpl for="children">',
			'<li class="{cls}" data-qtip="{[this.getQtipByEvnClass(parent,values,xindex,xcount)]}" style="height: 49px"></li>',
			'</tpl>',
			'<tpl else>',
			'<tpl for="children">',
			'<li class="{cls}" data-qtip="{[this.getQtipByEvnClass(parent,values,xindex,xcount)]}"></li>',
			'</tpl>',
			'</tpl>',
			'</ul>',
			'<ul class="ICD10">',
			'<tpl if="EmkTitle">',
			'<li><a href="#">{EmkTitle}</a></li>',
			'<tpl else>',
			'<li><a href="#"><tpl if="HasOpenEvnStick"><span class="evnstick-warn">ЛВН</span></tpl><tpl if="Diag_Code != null"><strong>{Diag_Code}</strong>{Diag_Name}<tpl else>Диагноз не установлен</tpl></a></li>',
			'</tpl>',
			'<li><a href="#">{Lpu_Nick}',//нижняя строчка ЛПУ / врач
				'<tpl if="this.hasMedPersonalName(values)"> / {[this.getMedPersonalFio(values.MedPersonal_Name)]}</tpl>',
			'</a></li>',
			//'<li><a href="#"></a></li>',
			'</ul>',
				'<tpl if="this.hasObjectId(values)">',
					'<div class="casemenu"><a href="#" object="{object}" object_value="{object_id}"></a></div>',
				'</tpl>',
			'</tpl>',
			'</div>',
			'</tpl>',
			'</tpl>',
			'</div>',
			'</tpl>',
			'</div>',
			archiveButton,
			emdOuterButton,
			{
				disableFormats: true,
				getMedPersonalFio: function(str) {
					return CaseLettersPersonFio(str);
				},
				hasMedPersonalName: function(values) {
					return !Ext6.isEmpty(values.MedPersonal_Name);
				},
				hasObjectId: function(values) {
					return !Ext6.isEmpty(values.object_id);
				},
				isLong: function (balooncls) {
					if (balooncls.indexOf('long') > -1) {
						return true;
					}else {
						return false;
					}
				},
				isCaseCLs: function (cls) {
					if (cls.indexOf('hospital') > -1 || cls.indexOf('disp') > -1) {
						return true;
					}else {
						return false;
					}
				},
				isDocAndVK: function (values) {
					return ((values.isDoc && values.isDoc == 2) || (values.isVK && values.isVK == 2 && win.historyGroupMode == 'doc'));
				},
				renderDocObject: function(values){
					var status = '';

					if(values.EvnStatus_Name && values.EvnDirection_statusDate) {
						status = "Статус " + values.EvnStatus_Name + " " + values.EvnDirection_statusDate;
					} else if (values.DrugComplexMnn_RusName && values.DrugComplexMnn_RusName.length >= 50) {
						status = values.DrugComplexMnn_RusName;

					}
					var ret_str = '<li><a href="#" title="'+status+'">';
					switch(values.EvnClass_id){
						case '27':
							ret_str += "Направление ";
							if(values.DirType_Name){
								if(values.DirType_id){
									switch(values.DirType_id){
										case '23':
											values.DirType_Name = 'на МСЭ';
											break;
										case '9':
											values.DirType_Name = 'на ВК';
											break;
									}
								}
								ret_str += values.DirType_Name + ' ';
							}
							break;
						case '4':
							ret_str += "Льготный рецепт ";
							break;
						case '20':
							ret_str += "ЛВН ";
							break;
						case '48':
							ret_str += "Протокол ВК ";
							break;
						case '180':
							ret_str += "Рецепт ";
							break;
						default:
							if(values.EmkTitle)
								ret_str += values.EmkTitle;
							else
								ret_str += (values.Diag_Code)?('<strong>'+values.Diag_Code+'</strong>'+values.Diag_Name):'Диагноз не установлен ';
					}
					ret_str += values.objectSetDateFormatted;
					ret_str += values.number?('/№' + values.number):'';
					if(values.EvnClass_id == 27 && values.LpuSectionProfile_Name)
						ret_str += '/'+values.LpuSectionProfile_Name;
					ret_str += values.Lpu_Nick?('/'+values.Lpu_Nick):'';
					if(values.DrugComplexMnn_RusName) {
						if(values.DrugComplexMnn_RusName.length < 50) {
							ret_str += '/МНН: ' + values.DrugComplexMnn_RusName;
						} else {
							ret_str += '/МНН: ' + values.DrugComplexMnn_RusName.substr(0, 47) + '...';
						}
					}
					ret_str += '</a></li>';
					return ret_str;
				},
				getQtipByEvnClass: function(parent,values,xindex,xcount){
					//Дата посещения/Код диагноза/Отделение/Профиль/ФИО врача(Иванов И.И.)
					var ret_str = '';
					ret_str += parent.EvnClass_Name;
					ret_str += (values.Diag_Code)?(' / <strong>'+values.Diag_Code+'</strong>'):' / Диагноз не установлен ';
					if(values.LpuSectionProfile_Name)
						ret_str += ' / '+values.LpuSectionProfile_Name;
					return ret_str;
				},
				getNodeParams: function(parent,values,xindex,xcount){
					var ret_str = '';
					if(values && values.Evn_id)
						ret_str = 'object="EvnVizitPL" object_value="'+values.Evn_id+'"';
					return ret_str;
				}
			}
		);
		tpl.overwrite(this.Tree.body, win.historyData);
		let treeBody = this.Tree.getEl(),
			caseHospital = treeBody.select('div.case.hospital');
		caseHospital.each(function (el, c, index) {
			if (el.child('.baloon').el.dom.childNodes.length > 2) {
				let lis = el.child('.baloon').select('li', true);
				let dt = el.child('.date').select('li', true);
				lis.setVisibilityMode(Ext6.Element.DISPLAY);
				dt.setVisibilityMode(Ext6.Element.DISPLAY);
				lis.hide();
				lis.setStyle({opacity: 0});
				dt.hide();
				dt.setStyle({opacity: 0});
				dt.elements[0].show();
				dt.elements[0].setStyle({opacity: 1});
				dt.elements[dt.elements.length - 1].show();
				dt.elements[dt.elements.length - 1].setStyle({opacity: 1});
				lis.elements[0].show();
				lis.elements[0].setStyle({opacity: 1});
				lis.elements[lis.elements.length - 1].show();
				lis.elements[lis.elements.length - 1].setStyle({opacity: 1});
				el.insertHtml('beforeEnd', '<div class="case-expand" data-qtip="Развернуть"><a href="#" class="expand-cluster-button"></a></div>')
			}
		});
		let caseAmbulant = treeBody.select('div.case.ambulant');
		caseAmbulant.each(function (el, c, index) {
			if (el.child('.baloon').el.dom.childNodes.length > 2) {
				let lis = el.child('.baloon').select('li', true);
				let dt = el.child('.date').select('li', true);
				lis.setVisibilityMode(Ext6.Element.DISPLAY);
				dt.setVisibilityMode(Ext6.Element.DISPLAY);
				lis.hide();
				lis.setStyle({opacity: 0});
				dt.hide();
				dt.setStyle({opacity: 0});
				dt.elements[0].show();
				dt.elements[0].setStyle({opacity: 1});
				dt.elements[dt.elements.length-1].show();
				dt.elements[dt.elements.length-1].setStyle({opacity: 1});
				lis.elements[0].show();
				lis.elements[0].setStyle({opacity: 1});
				lis.elements[lis.elements.length-1].show();
				lis.elements[lis.elements.length-1].setStyle({opacity: 1});
				el.insertHtml('beforeEnd', '<div class="case-expand" data-qtip="Развернуть"><a href="#" class="expand-cluster-button"></a></div>');
			}
		});
		treeBody.select('div.groupcase').each(function (el, c, index) {
			el.child('span.groupcase_title').dom.setAttribute('data-qtip','Развернуть');
		});
		this.Tree.getEl().clearListeners();
		this.Tree.getEl().on('click', function (e, t) {
			let expandCase = e.getTarget('.case-expand');
			e.stopEvent(true);
			if (expandCase) {
				win.ExpandHospitalItems(t);
				return false;
			}
		}, null, {delegate: 'div.case-expand', order: 'before'});
		this.Tree.getEl().on('click', function(e, t) {
			var EvnVizitPL_id = false;
			if(e.target && e.target.getAttribute('data-type') && e.target.getAttribute('data-type') == 'evnVizitPL')
				EvnVizitPL_id = e.target.getAttribute('object_value');
			if(!t.getAttribute('object_value')) return;//для неоткрывающихся, только информационных элементов
			var caseMenu = e.getTarget('.casemenu', 2, true);
			e.stopEvent();
			if (caseMenu) {
				var canCreateVizit = t.getAttribute('canCreateVizit') == "1" ? true : false;
				win.showHistoryMenu(caseMenu, t.getAttribute('object'), t.getAttribute('object_value'), t.getAttribute('accessType'), canCreateVizit);
			} else {
				win.loadEmkViewPanel(t.getAttribute('object'), t.getAttribute('object_value'), t.getAttribute('object_id'), false, EvnVizitPL_id);
			}
		}, null, {delegate: 'div.case'});
		this.Tree.getEl().on('click', function(e, t) {
			e.stopEvent();
			if (e.getTarget('span.groupcase_title')) {
				var contentId = e.getTarget('span.groupcase_title');
				var content_node = win.Tree.getEl().query('[nodeid=' + contentId.getAttribute('idgroup') + '_content]');
				var arrow_node = win.Tree.getEl().query('[nodeid=' + contentId.getAttribute('idgroup') + ']');
				if (content_node && content_node[0] && arrow_node && arrow_node[0]) {
					var content = Ext6.get(content_node[0]);
					var arrow = Ext6.get(arrow_node[0]).child('b.groupcase_arrow');
					if (!content) return false;
					content.setVisibilityMode(Ext6.Element.DISPLAY);
					if (!content.isVisible()) {
						content.show();
						arrow.addCls('expanded');
						contentId.setAttribute('data-qtip','Свернуть')
					} else {
						content.hide();
						arrow.removeCls('expanded');
						contentId.setAttribute('data-qtip','Развернуть');
					}
				}
			}
			if (e.getTarget('b.groupcase_arrow')) {
				var contentId = t.getElementsByClassName('groupcase_title')[0];
				var content_node = win.Tree.getEl().query('[nodeid=' + contentId.getAttribute('idgroup') + '_content]');
				var arrow_node = win.Tree.getEl().query('[nodeid=' + contentId.getAttribute('idgroup') + ']');
				if (content_node && content_node[0] && arrow_node && arrow_node[0]) {
					var content = Ext6.get(content_node[0]);
					var arrow = Ext6.get(arrow_node[0]).child('b.groupcase_arrow');
					if (!content) return false;
					content.setVisibilityMode(Ext6.Element.DISPLAY);
					if (!content.isVisible()) {
						content.show();
						arrow.addCls('expanded');
						contentId.setAttribute('data-qtip','Свернуть');
					} else {
						content.hide();
						arrow.removeCls('expanded');
						contentId.setAttribute('data-qtip','Развернуть');
					}
				}
			}
		}, null, {delegate: 'div.groupcase'});
		this.Tree.getEl().on('click', function(e, t) {
			e.stopEvent();
			if (win.useArchive) {
				win.useArchive = false;
			} else {
				win.useArchive = true;
			}
			win.loadTree();
		}, null, {delegate: 'a.archive-button'});
		this.Tree.getEl().on('click', function(e, t) {
			e.stopEvent();
			win.openEMDOuterRegistry();
		}, null, {delegate: 'a.emd-outer-button'});

		if (this.currentNode) {
			this.setTreeState(this.currentNode.object, this.currentNode.object_value, this.currentNode.object_id);
		}
	},
	openEMDOuterRegistry: function() {
		var me = this;
		getWnd('swEMDOuterRegistryWindow').show({
			parentform: 'EMK',
			Person_id: me.Person_id,
			Person_Surname: me.PersonInfoPanel.getFieldValue('Person_Surname'),
			Person_Firname: me.PersonInfoPanel.getFieldValue('Person_Firname'),
			Person_Secname: me.PersonInfoPanel.getFieldValue('Person_Secname'),
			Person_Birthday: me.PersonInfoPanel.getFieldValue('Person_Birthday'),
			Person_Snils: me.PersonInfoPanel.getFieldValue('Person_Snils')
		});
	},
	// выделяет активный элемент в дереве
	setTreeState: function(object, object_value, object_id) {
		this.Tree.getEl().select("div.case").removeCls('selected');
		var node = this.Tree.getEl().query('[nodeid=case_' + object + '_' + object_value + ']');
		if (node && node[0]) {
			Ext6.get(node[0]).addCls('selected');
		}
		this.currentNode = {object: object, object_value: object_value, object_id: object_id};
	},

	clearTreeState: function() {
		this.Tree.getEl().select("div.case").removeCls('selected');
		this.currentNode = null;
	},

	// фильтрация истории
	filterHistory: function() {
		var win = this;
		var text_filter = this.treePanel.down('#historyTextFilter').getValue();
		var date_filter = this.historyFilterDateRange;
		var type_filter = this.filterTreePanelTypeFilter;
		var type_filter_menu = this.typeFilterMenu;
		var is_my = type_filter_menu.items.items[type_filter_menu.items.items.length - 1].checked;
		if (!win.treeData) return false;
		win.treeData.data.forEach(function (el, index, array) {

			var hide = false;

			// фильтр по тексту
			if(text_filter.length >= 2) {
				var filterRe = new RegExp(text_filter.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"), 'ig');
				hide = hide || !(filterRe.test(el.Diag_Code) || filterRe.test(el.Diag_Name) || filterRe.test(el.Lpu_Nick) || filterRe.test(el.EmkTitle));
			}

			// фильтр по дате
			if(!Ext6.isEmpty(date_filter.getValue())) {
				var date_from = date_filter.getDateFrom(),
					date_to = date_filter.getDateTo() || date_filter.getDateFrom(),
					set_date = Date.parseDate(el.objectSetDate, 'd.m.Y'),
					dis_date = Date.parseDate(el.objectDisDate, 'd.m.Y');

				if (!dis_date) {
					dis_date = set_date; // если нет даты окончания, фильтруем по дате начала
				}
				hide = hide || !set_date || set_date > date_to || dis_date < date_from;
			}

			// фильтр по типу событий
			if (win.historyFilterClassChecked.length) {
				hide = hide || Ext6.isEmpty(el.EvnClass_id) || !el.EvnClass_id.inlist(win.historyFilterClassChecked);
			}

			// только мои
			if (is_my && win.userMedStaffFact.MedStaffFact_id) {
				var is_my_evn = false;
				Ext6.each(el.children, function(item) {
					if (item.MedStaffFact_id == win.userMedStaffFact.MedStaffFact_id) {
						is_my_evn = true;
					}
				});
				hide = hide || !is_my_evn;
			}

			el.hide = hide ? 2 : null;
		});
		// TODO: надо как-то запоминать состояние раскрытых веток и после фильтрации восстанавливать
		win.groupHistory(); // после фильтрации заново группируем
		win.renderLoadedTree(); // и рендерим
		if(!!this.currentNode) {
			win.setTreeState(this.currentNode.object, this.currentNode.object_value, this.currentNode.object_id);
		}
		if(Ext6.isEmpty(date_filter.getValue()) && !type_filter.getValue().length) {
			win.filterTreePanel.hide();
		} else {
			win.filterTreePanel.show();
		}

	},

	showPersonDispSignalViewData: function() { //Отображение данных по дисп. учету человека при открытии ЭМК
		var win = this,
			piPanel = win.PersonInfoPanel;
		if (win.userMedStaffFact.ARMType == 'mse' && getGlobalOptions().use_depersonalized_expertise) return false;

		Ext6.Ajax.request({
			failure: function(response, options) {
				sw4.showInfoMsg({
					panel: win,
					type: 'error',
					text: langs('При загрузке сигнальной информации о диспансерном учете возникли ошибки')
				});
			},
			params: {
				Person_id: win.Person_id,
				UserMedStaffFact_id: win.userMedStaffFact.MedStaffFact_id
			},
			success: function(response, options) {
				if ( response.responseText )
				{
					var result  = Ext6.util.JSON.decode(response.responseText),
						tmp1_str = '',
						tmp2_str = '',
						diag_str,
						date_str,
						msg = '';
					if ( Ext6.isArray(result) && result.length > 0 )
					{
						msg += langs('Пациент')+' '+piPanel.getFieldValue('Person_Surname') +' ';
						msg += piPanel.getFieldValue('Person_Firname') +' ';
						msg += piPanel.getFieldValue('Person_Secname') +' ';
						msg += langs('состоит на диспансерном учете по следующим диагнозам, ');
						for (var i=0; i < result.length; i++)
						{
							diag_str = '<b>'+ result[i].Diag_Code + ' ' + result[i].Diag_Name+'</b>';
							date_str = result[i].LastOsmotr_setDate || '';
							/*
								3	поставлен ли пациент на ДУ текущим врачом
								2	поставлен ли пациент на ДУ на участке текущего врача
								1	пациент поставлен на ДУ чужим врачом
							*/
							if(result[i].PersonDispSetType_id != 3) {
								tmp1_str += '<br>'+ diag_str +' '+ date_str;
							} else {
								tmp2_str += '<br>'+ diag_str +' '+ date_str;
							}
						}
						if(tmp2_str.length > 0)
						{
							msg += langs('установленным Вами:')+ tmp2_str;
						}
						if(tmp1_str.length > 0)
						{
							if(tmp2_str.length > 0) msg += '<br>... ';
							msg += langs('установленным другими врачами:')+ tmp1_str;
						}

						sw4.showInfoMsg({
							panel: win,
							type: 'info',
							text: msg
						});
					}
				}
			},
			url: '/?c=PersonDisp&m=getPersonDispSignalViewData'
		});
	},

	// групиировка истории
	groupHistory: function() {
		var win = this;
		var groups = [];
		win.historyData = {
			Person_id: win.Person_id,
			Groups: []
		};
		if (win.historyGroupMode == 'evn') { // группировка по событиям
			win.treeData.data.forEach(function (el, index, array) {
				if (el.hide == 2) return false;
				if (el.isDoc == 2) return false;
				if (!!el.EvnClass_id) {
					if (!groups['Evn_' + el.EvnClass_id]) {
						groups['Evn_' + el.EvnClass_id] = {
							Group_id: 'Evn_' + el.EvnClass_id,
							Group_Name: el.EvnClass_Name,
							Group_SysNick: el.object,
							items: []
						};
					}
					groups['Evn_' + el.EvnClass_id].items.push(el);
				} else {
					// Не-Evn надо писать в отдельную группу
				}
			});
		} else if (win.historyGroupMode == 'doc') { // группировка по документам
			win.treeData.data.forEach(function (el, index, array) {
				if (el.hide == 2) return false;
				if (el.isDoc != 2 && el.isVK != 2) return false;
				if (!!el.EvnClass_id) {
					if (!groups['Evn_' + el.EvnClass_id]) {
						groups['Evn_' + el.EvnClass_id] = {
							Group_id: 'Evn_' + el.EvnClass_id,
							Group_Name: el.EvnClass_Name,
							Group_SysNick: el.object,
							items: []
						};
					}
					groups['Evn_' + el.EvnClass_id].items.push(el);
				} else {
					// Не-Evn надо писать в отдельную группу
				}
			});
		} else { // иначе всё пихаем в одну безымянную группу
			groups[0] = {
				Group_id: 0,
				Group_Name: '',
				Group_SysNick: '',
				items: []
			};
			win.treeData.data.forEach(function (el, index, array) {
				if (el.hide == 2) return false;
				if (el.isDoc == 2) return false;
				groups[0].items.push(el);
			});
		}
		// XTemplate понимает только массивы, придётся пересобрать
		for (el in groups) {
			if (typeof(groups[el]) != 'object') continue;
			groups[el].count = groups[el].items.length;
			//groups[el].count = null;
			win.historyData.Groups.push(groups[el]);
		}
	},

	// загрузка сигнальной информации
	loadSignalInfo: function() {
		var me = this;

		var tpl = new Ext6.XTemplate(
			'<div class="signal_info">',
			'<p><a object="PersonPanel" href="#">Данные пациента</a></p>',
			'<p><a object="PersonLpuInfoPanel" href="#">Информированное добровольное согласие</a></p>',
			'<p><a object="RiskFactorPanel" href="#">Факторы риска</a></p>',
			'<p><a object="PersonPrivilegePanel" href="#">Льготы</a></p>',
			'<p><a object="PersonBloodGroupPanel" href="#">Группа крови и резус фактор</a></p>',
			'<p><a object="PersonCardioRiskCalcPanel" href="#">Суммарный сердечно-сосудистый риск</a></p>',
			'<p><a object="PersonMedHistoryPanel" href="#">Анамнез жизни</a></p>',
			'<p><a object="PersonAllergicReactionPanel" href="#">Аллергологический анамнез</a></p>',
			'<p><a object="PersonDispPanel" href="#">Диспансерный учёт</a></p>',
			'<p><a object="PersonDiagPanel" href="#">Список уточненных диагнозов</a></p>',
            '<p><a object="PersonAnthropometricPanel" href="#">Антропометрические данные</a></p>',
            '<p><a object="PersonHeightPanel" href="#">Рост</a></p>',
			'<p><a object="PersonWeightPanel" href="#">Масса тела</a></p>',
			getRegionNick() == 'ufa' ? '<p><a object="PersonRacePanel" href="#">Раса</a></p>' : null,
			'<p><a object="PersonFeedingTypePanel" href="#">Способ вскармливания</a></p>',
			'<p><a object="PersonSvidPanel" href="#">Свидетельства</a></p>',
			'<p><a object="PersonSurgicalPanel" href="#">Список оперативных вмешательств</a></p>',
			'<p><a object="PersonDirFailPanel" href="#">Список отменённых направлений</a></p>',
			'<p><a object="PersonEvnPLDispPanel" href="#">Диспансеризация и мед. осмотры</a></p>',
			'<p><a object="PersonProfilePanel" href="#">Список опросов</a></p>',
			'<p><a object="PersonDrugRequestPanel" href="#">Список ЛС, заявленных в рамках ЛЛО</a></p>',
			'</div>'
		);

		tpl.overwrite(me.SignalInfoPanel.body, {});

		me.SignalInfoPanel.getEl().on('click', function(e, t) {
			e.stopEvent();
			// надо спозиционироваться на нужной панели справа и развернуть её при необходимости
			var object = t.getAttribute('object');
			if (me.PersonForm && me.PersonForm[object]) {
				if (me.PersonForm[object].collapsed) {
					me.PersonForm[object].expand();
				}

				var y = me.PersonForm[object].getLocalY();
				me.PersonForm.scrollablePanel.scrollTo(0, y);
			}
		}, null, {delegate: 'a'});
	},
	listeners: {
		hide: function(win) {
			var store = this.PersonInfoPanel.DataView.getStore();
			if(store) {
				store.removeAll();
				if (this.PersonInfoPanel) {
					this.PersonInfoPanel.clearTitle();
				}
				this.setTitle('...');
				this.Tree.data = null;
			}

			var emk = null;
			var emks = Ext6.ComponentQuery.query('window[refId=common]');
			if (emks) {
				for (i=0; i<emks.length; i++) if(emks[i].isVisible()) emk=emks[i];
			}

			if(!emk) {
				document.getElementById('main-center-panel').classList.remove('increased-size');
				var cmp = Ext6.getCmp('version_for_visually_impaired');
				if(cmp){cmp.setHidden(true);}
			}
			this.updateLayout();
			win.stopTask();
			win.callback();
		},
        // проверка перед закрытием ЭМК
        beforehide: function(sender) {

            var win = this;

            if  (!win.bypassCheckOnHide) {

                // если у пользователя есть сертификаты для подписи
                // и у посещений входящих в ТАП есть неподписанные
                // осмотры или направления или документы или рецепты,
                // то предлагаем подписать их

                // 1. ожидание прогрузки аякс
                // 2. ожидание ответа на предупреждение (если есть)

                win.checkSignedEvnContent().then(function(promise){

                    if (promise.success) {
                        // продолжаем, если проверка прошла упешно
                        win.bypassCheckOnHide = true;
                        win.hide();
                    } else {
                        // если проверка провалилась и выбрано подписать
                        log('sign needeed', promise);
                    }

                }).catch(function(err) {
                    log('error', err);
                    // если что то пошло не так в проверке, мы должны продолжить...
                    win.bypassCheckOnHide = true;
                    win.hide();
                });

                // блокируем спуск ниже
                return false;
            }
        }
	},
	// выбор формата отображения формы
	// 0 - история, 1 - сигнальная
	selectViewMode: function(mode) {
		if(mode == 0) {
			Ext6.getCmp('selectViewModeButton').setValue(0);
			this.SignalInfoPanel.hide();
			this.treePanel.show();

		} else if (mode == 1) {
			Ext6.getCmp('selectViewModeButton').setValue(1);
			this.SignalInfoPanel.show();
			this.treePanel.hide();
			this.SignalInfoPanel.getEl().select('a').first().dom.click(); // странный костыль, но fireEvent тут не работает

		}
	},
	/**
	 * Установка диагноза для случая в дереве (вызывается например после редактирования случая в правой части ЭМК)
	 */
	setTreeDataDiag: function(object, object_id, values, rec) {
		var Diag_Name = rec.Diag_Name?rec.Diag_Name:null,
			Diag_Code = rec.Diag_Code?rec.Diag_Code:null;
		var me = this;
		if (this.treeData.data)
			this.treeData.data.forEach(function (el, index, array) {
				if (el.object && el.object_id && el.object == object && el.object_id == object_id) {
					if(values && (values.EvnVizitPL_id || values.EvnVizitPLStom_id) && el.children && typeof el.children == 'object'){
						el.children.forEach(function(vizit,index,arr){
							if(vizit.Evn_id && (vizit.Evn_id == values.EvnVizitPL_id || vizit.Evn_id == values.EvnVizitPLStom_id) && ((index+1) == 1)){
								if(values.EvnVizitPLStom_id)
									me.EvnPLStomForm.setMainPanelTitle(rec);
								else
									me.EvnPLForm.setMainPanelTitle(rec);
								el.Diag_Code = Diag_Code;
								el.Diag_Name = Diag_Name;
							}
						});
					} else {
						if(object !== 'EvnPL' && object !== 'EvnPLStom'){
							el.Diag_Code = Diag_Code;
							el.Diag_Name = Diag_Name;
						}
					}
				}
			});
		this.groupHistory();
		this.renderLoadedTree();
	},

	setHistoryGroupMode: function(mode) {
		this.historyGroupMode = mode;
		this.groupHistory();
		this.renderLoadedTree();
		if(!!this.currentNode) {
			this.setTreeState(this.currentNode.object, this.currentNode.object_value, this.currentNode.object_id);
		}
	},

	show: function() {
		this.callParent(arguments);
		var win = this;

		if (!arguments || !arguments[0]) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext6.emptyFn;
		}
		this.formStatus = 'edit';
		this.ignoreParams = [];
        this.bypassCheckOnHide = false;
        this.displayedObject = null;

		this.historyFilterDateRange.clear();
		this.historyGroupMode = null;
		this.currentNode = null;
		this.historyFilterClassChecked = [];

		this.Person_id = arguments[0].Person_id;
		this.PersonEvn_id = arguments[0].PersonEvn_id;
		this.Server_id = arguments[0].Server_id;
		this.userMedStaffFact = arguments[0].userMedStaffFact;
		this.openEvn = arguments[0].openEvn || null;
		this.useArchive = false;
		this.electronicQueueData = arguments[0].electronicQueueData || false;

		//yl:авто-создание случая из вызова на дом
		this.allowHomeVisit = arguments[0].allowHomeVisit || false;
		this.HomeVisit_id = arguments[0].HomeVisit_id;

		this.TimetableGraf_id = null;
		if (arguments[0].TimetableGraf_id) {
			this.TimetableGraf_id = arguments[0].TimetableGraf_id;
		}
		this.EvnDirection_id = null;
		if (arguments[0].EvnDirection_id) {
			this.EvnDirection_id = arguments[0].EvnDirection_id;
		}
		
		if (arguments[0].EvnDirectionData && arguments[0].EvnDirectionData.EvnDirection_id) {
			this.EvnDirectionData = arguments[0].EvnDirectionData;
			this.TimetableGraf_id = arguments[0].EvnDirectionData.TimetableGraf_id || null;
		} else {
			this.EvnDirectionData = null;
		}

		this.clearNodeViewForm();

		this.SignalInfoPanel.hide();
		this.treePanel.expand();

		this.setTitle('...');
		this.Tree.setHtml('');

		var options={};//параметры для loadAll(options)

		//yl:авто-создание случая с привязкой к вызову на дом
		if (arguments[0].allowHomeVisit && arguments[0].HomeVisit_id) {
			options.callback = function () {//yl:сработает в setTreeData или в loadAll
				this.addNewEvnPLAndEvnVizitPL()
			}.createDelegate(this)
		}

		if (true) {
			// грузим всё одним запросом
			this.loadAll(options);
		} else {
			// грузим всё отдельными запросами
			this.PersonInfoPanel.load({
				Person_id: this.Person_id,
				Server_id: this.Server_id,
				userMedStaffFact: this.userMedStaffFact,
				PersonEvn_id: this.PersonEvn_id,
				callback: function () {
					win.onLoadPersonInfoPanel();
				}
			});

			this.loadTree();

			if (win.openEvn) {
				win.loadEmkViewPanel(win.openEvn.object, win.openEvn.object_value, '');
			} else if(arguments[0].allowHomeVisit && arguments[0].HomeVisit_id){
				this.addNewEvnPLAndEvnVizitPL();//yl:авто-создание случая с привязкой к вызову на дом
			} else {
				// иначе открываем сигнальную информацию
				win.loadEmkViewPanel('Person', win.Person_id, '');
			}
		}
		this.loadSignalInfo();
		this.filterTreePanel.hide();

		this.removeCls('x6-unselectable');

		this.ElectronicQueuePanel.initElectronicQueue();

		//todo: прописать это внутри панели и вызывать при инициализации панели
		if (this.electronicQueueData
			&& this.electronicQueueData.electronicTalonStatus_id
			&& this.electronicQueueData.electronicTalonStatus_id < 4) {
			log('showEqPanel');
			this.ElectronicQueuePanel.show(); this.updateLayout();

			if (this.ElectronicQueuePanel.floating) this.ElectronicQueuePanel.alignTo(this, 'bl', [300, -70]);
			else this.ElectronicQueuePanel.alignTo(this, 'bl?');

            if (this.electronicQueueData.onLoadEmkWindow && typeof this.electronicQueueData.onLoadEmkWindow === 'function') {
                this.onViewPanelLoaded = this.electronicQueueData.onLoadEmkWindow;
            }

		} else {
			log('hideEqPanel');
			this.ElectronicQueuePanel.hide(); this.updateLayout();
		}

		//refs #179845
		Ext6.getCmp('version_for_visually_impaired').setHidden(false);

		if(Ext.globalOptions.emk.version_for_visually_impaired){
			document.getElementById('main-center-panel').classList.add('increased-size');
        } else {
			document.getElementById('main-center-panel').classList.remove('increased-size');
		}

		this._setFormAllowed();
		this.updateLayout();

		return false;
	},
	searchNodeInTreeAndLoadViewForm: function(params) {
		var node, parent_node;
		if (typeof params.parent_node == 'object')
		{
			parent_node = params.parent_node;
		}
		else
		{
			// нужно найти родительную ноду, если её нет в params.parent_node
		}
		if (!parent_node)
		{
			log('searchNodeInTreeAndLoadViewForm: parent_node NOT found!');
			return false;
		}
		var loadViewForm = function(form, params)
		{
			if(params.object && params.object_id) {
				for(i=0; i<form.treeData.data.length; i++) {
					if((form.treeData.data[i].object == params.object) && (form.treeData.data[i].object_id == params.object_id)) node = form.treeData.data[i];
				}
			}
			if(node) {
				if(node.object=='EvnPL')
					form.loadEmkViewPanel(node.object, node.object_id, "");
				else
					form.loadEmkViewForm(node.object, node.object_id, "");
			} else {
				log('searchNodeInTreeAndLoadViewForm: The node NOT found in Tree!');
				return false;
			}

			/*	if(params.node_attr_name && params.node_attr_value && !params.last_child)
				{
					node = parent_node.findChild(params.node_attr_name, params.node_attr_value);
				}
				if(params.last_child && parent_node.lastChild)
				{
					node = parent_node.lastChild;
				}
				if (node)
				{
					node.select();
					form.Tree.fireEvent('click', node);
				}
				else
				{
					log('searchNodeInTreeAndLoadViewForm: The node NOT found in Tree!');
					return false;
				}
				return true;*/
		};
		//~ if (parent_node.isExpanded())
		//~ {
		loadViewForm(this,params);
		//~ }
		//~ else
		//~ {
		//~ parent_node.expand(false,false,function(){
		//~ loadViewForm(this,params);
		//~ }.createDelegate(this));
		//~ }
	},
	/**
	 * Уничтожаем все задания, чтобы не тормозить систему
	 */
	stopTask: function(){
		if (this.PersonInfoPanel.TimeElapsed) {
			this.PersonInfoPanel.TimeElapsed.setStyle('visibility', 'hidden');
		}
		if(this.runner)
			this.runner.destroy();
	},
    printHtml: function(id)
    {
        var s = Ext.get(id),
			id_salt = Math.random();
        if (!s)
        {
            Ext.Msg.alert(langs('Сообщение'), langs('Секция ')+' '+ id+' ' +langs(' не найдена.'));
            return false;
        }
        var win_id = 'printEvent' + Math.floor(id_salt*10000);
        var win = window.open('', win_id);
        win.document.write('<html><head><title>Печатная форма</title><link href="/css/emk.css?'+ id_salt +'" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">'+ s.dom.innerHTML +'</body></html>');
        var i, el;
        // нужно показать скрытые области для печати
        var printonly_list = Ext.query("div[class=printonly]",win.document);
        for(i=0; i < printonly_list.length; i++)
        {
            el = new Ext.Element(printonly_list[i]);
            el.setStyle({display: 'block'});
        }
        // нужно скрыть элементы управления
        var tb_list = Ext.query("*[class*=section-toolbar]",win.document);
        tb_list = tb_list.concat(Ext.query("*[class*=sectionlist-toolbar]",win.document));
        tb_list = tb_list.concat(Ext.query("*[class*=item-toolbar]",win.document));
        //tb_list = tb_list.concat(Ext.query("*[class=section-button]",win.document));
        //log(tb_list);
        for(i=0; i < tb_list.length; i++)
        {
            el = new Ext.Element(tb_list[i]);
            el.setStyle({display: 'none'});
        }
        win.document.close();
        //win.print();
    },
	initCofigActions: function() {
		var me = this;

		this.config_actions = {
			EvnPL: {
				editEvnPL: {
					actionType: 'edit',
					sectionCode: 'EvnPL_data',
					handler: function(e, c, d) {
						this.openEditWindow(d, 'editEvnPL');
					}.createDelegate(this)
				}
			},
			EvnVizitPL: {
				editEvnVizitPL: {
					actionType: 'edit',
					sectionCode: 'EvnVizitPL_data',
					handler: function(e, c, d) {
						this.openEditWindow(d, 'editEvnVizitPL');
					}.createDelegate(this)
				}
			},
            EvnUslugaTelemed: {
                printEvnUslugaTelemed:{
                    sectionCode: 'EvnUslugaTelemed_data',
                    actionType: 'view',
                    handler: function(e, c, d) {
                    	if (d.object_id) {
							var id = 'EvnUslugaTelemed_'+d.object_id;
							me.printHtml(id);
						}
                    }
                }
            },
			EvnXmlDirectionLink: {
				printdoc:{
					sectionCode: 'EvnXmlDirectionLink',
					actionType: 'view',
					handler: function(e, c, d) {
						var EvnXml_id = d.object_id;
						sw.Promed.EvnXml.doPrintById(EvnXml_id);
					}
				},
				showDoc: {
					actionType: 'view',
					sectionCode: 'EvnXmlDirectionLink',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				},
				hideDoc: {
					actionType: 'view',
					sectionCode: 'EvnXmlDirectionLink',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				}
			},
			FreeDocument: {
				printdoc:{
					sectionCode: 'FreeDocument',
					actionType: 'view',
					handler: function(e, c, d) {
						var EvnXml_id = d.object_id;
						sw.Promed.EvnXml.doPrintById(EvnXml_id);
					}
				},
				showDoc: {
					actionType: 'view',
					sectionCode: 'FreeDocument',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				},
				hideDoc: {
					actionType: 'view',
					sectionCode: 'FreeDocument',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				}
			},
			EvnXmlOther: {
				printdoc:{
					sectionCode: 'EvnXmlOther',
					actionType: 'view',
					handler: function(e, c, d) {
						var EvnXml_id = d.object_id;
						sw.Promed.EvnXml.doPrintById(EvnXml_id);
					}
				},
				showDoc: {
					actionType: 'view',
					sectionCode: 'EvnXmlOther',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				},
				hideDoc: {
					actionType: 'view',
					sectionCode: 'EvnXmlOther',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				}
			},
			EvnXmlEpikriz: {
				printdoc:{
					sectionCode: 'EvnXmlEpikriz',
					actionType: 'view',
					handler: function(e, c, d) {
						var EvnXml_id = d.object_id;
						sw.Promed.EvnXml.doPrintById(EvnXml_id);
					}
				},
				showDoc: {
					actionType: 'view',
					sectionCode: 'EvnXmlEpikriz',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				},
				hideDoc: {
					actionType: 'view',
					sectionCode: 'EvnXmlEpikriz',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				}
			},
			EvnXmlProtokol: {
				printdoc:{
					sectionCode: 'EvnXmlProtokol',
					actionType: 'view',
					handler: function(e, c, d) {
						var EvnXml_id = d.object_id;
						sw.Promed.EvnXml.doPrintById(EvnXml_id);
					}
				},
				showDoc: {
					actionType: 'view',
					sectionCode: 'EvnXmlProtokol',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				},
				hideDoc: {
					actionType: 'view',
					sectionCode: 'EvnXmlProtokol',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				}
			},
			EvnXmlRecord: {
				printrecordtop: {
					sectionCode: 'EvnXmlRecord',
					actionType: 'view',
					handler: function(e, c, d) {
						var EvnXml_id = d.object_id;
						sw.Promed.EvnXml.doPrintByIdHalf(EvnXml_id, false);
					}
				},
				printrecordbot: {
					sectionCode: 'EvnXmlRecord',
					actionType: 'view',
					handler: function(e, c, d) {
						var EvnXml_id = d.object_id;
						sw.Promed.EvnXml.doPrintByIdHalf(EvnXml_id, true);
					}
				},
				printdoc:{
					sectionCode: 'EvnXmlRecord',
					actionType: 'view',
					handler: function(e, c, d) {
						var EvnXml_id = d.object_id;
						sw.Promed.EvnXml.doPrintById(EvnXml_id);
					}
				},
				showDoc: {
					actionType: 'view',
					sectionCode: 'EvnXmlRecord',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				},
				hideDoc: {
					actionType: 'view',
					sectionCode: 'EvnXmlRecord',
					handler: function(e, c, d) {
						// сворачивание / разворачивание документа
						me.toggleDisplayDocument(d.object_id, d.object);
					}
				}
			},
			EvnPLDispInfo: {
				view: {
					actionType: 'view',
					sectionCode: 'EvnPLDispInfo',
					handler: function(e, c, d) {
						me.openEvnPLDispEditWindow('view', d);
					}.createDelegate(this)
				}
			},
			EvnPLDispAdult: {
				edit: {
					actionType: 'edit',
					sectionCode: 'EvnPLDispAdult',
					handler: function(e, c, d) {
						me.openEvnPLDispEditWindow('edit', d);
					}.createDelegate(this)
				}
			},
			EvnPLDispChild: {
				edit: {
					actionType: 'edit',
					sectionCode: 'EvnPLDispChild',
					handler: function(e, c, d) {
						me.openEvnPLDispEditWindow('edit', d);
					}.createDelegate(this)
				}
			},
			EvnPLDispDop13: {
				edit: {
					actionType: 'edit',
					sectionCode: 'EvnPLDispDop13',
					handler: function(e, c, d) {
						me.openEvnPLDispEditWindow('edit', d);
					}.createDelegate(this)
				}
			},
			EvnPLDispProf: {
				edit: {
					actionType: 'edit',
					sectionCode: 'EvnPLDispProf',
					handler: function(e, c, d) {
						me.openEvnPLDispEditWindow('edit', d);
					}.createDelegate(this)
				}
			},
			EvnPLDispOrp: {
				edit: {
					actionType: 'edit',
					sectionCode: 'EvnPLDispOrp',
					handler: function(e, c, d) {
						me.openEvnPLDispEditWindow('edit', d);
					}.createDelegate(this)
				}
			},
			EvnPLDispTeenInspection: {
				edit: {
					actionType: 'edit',
					sectionCode: 'EvnPLDispTeenInspection',
					handler: function(e, c, d) {
						me.openEvnPLDispEditWindow('edit', d);
					}.createDelegate(this)
				}
			},
			EvnPLDispMigrant: {
				edit: {
					actionType: 'edit',
					sectionCode: 'EvnPLDispMigrant',
					handler: function(e, c, d) {
						me.openEvnPLDispEditWindow('edit', d);
					}.createDelegate(this)
				}
			},
			EvnPLDispDriver: {
				edit: {
					actionType: 'edit',
					sectionCode: 'EvnPLDispDriver',
					handler: function(e, c, d) {
						me.openEvnPLDispEditWindow('edit', d);
					}.createDelegate(this)
				}
			}
		};
	},
	initComponent: function() {
		var win = this;

		var conf = win.initialConfig;
		this.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanel', {
			region: 'north',
			border: false,
			userMedStaffFact: this.userMedStaffFact,
			ownerWin: this
		});

		this.initCofigActions();

		win.SignalInfoPanel =  new Ext6.Panel({
			//region: 'center',
			layout: 'border',
			autoScroll: true,
			border: false,
			headerPosition: 'left',
			bodyStyle: 'background-color: #eee;',
			animCollapse: false,
			floatable: false,
			flex: 110,
			region: 'west',
			//layout: 'border',
			collapsible: true,
			header: false
		});

		win.filterDatePicker = Ext6.create('Ext6.date.RangePicker', {
			maxDate: new Date(),
			handler: function(dp, date){
				if (date && date.length == 2) {
					var date_from = date[0];
					var date_to = date[1];
				} else {
					var date_from = date;
					var date_to = date;
				}

				if (date_from && date_from.toJSONString() === date_to.toJSONString()) {
					win.historyFilterDateRange.setDates(date_from);
				} else {
					win.historyFilterDateRange.setDates([date_from, date_to]);
				}
			}
		});

		win.typeFilterMenu = Ext6.create('Ext6.menu.Menu', {
			listeners: {
				'mouseleave': function(menu) {
					menu.autoHide(true);
				},
				'mouseenter': function(menu) {
					menu.autoHide(false);
				}
			},
			autoHide: function(needHide) {
				var menu = this;
				if (needHide) {
					menu.autoHideTimeout = setTimeout(function() {
						menu.hide();
					}, 2000);
				} else {
					if (menu.autoHideTimeout) {
						clearTimeout(menu.autoHideTimeout);
					}
				}
			},
			margin: '0 0 10 0',
			items: [{
				text: 'Все типы случаев',
				checked: true,
				value: 0,
				handler:function(){win.typeFilterMenu.setAllChecked(true)}
			}, {
				xtype: 'menuseparator',
				userCls: 'separator',
			}, {
				text: 'Амбулаторное лечение',
				checked: false,
				value: '3',
				handler:function(){win.typeFilterMenu.getChecked()}
			}, {
				text: 'Стационарное лечение',
				checked: false,
				value: '30',
				handler:function(){win.typeFilterMenu.getChecked();}
			}, {
				text: 'Вызовы СМП',
				checked: false,
				//value: '901', // было
				value: '990',
				handler:function(){win.typeFilterMenu.getChecked();}
			}, {
				text: 'Диспансеризация',
				checked: false,
				value: '7',
				handler:function(){win.typeFilterMenu.getChecked();}
			}, {
				text: 'Стоматологическое лечение',
				checked: false,
				value: '6',
				handler:function(){win.typeFilterMenu.getChecked();}
			}, {
				text: 'Санаторно-курортное лечение',
				checked: false,
				value: '902',
				handler:function(){win.typeFilterMenu.getChecked();}
			}, {
				xtype: 'menuseparator',
				userCls: 'separator'
			}, {
				text: 'Только мои случаи',
				checked: false,
				value: '999',
				handler:function(){win.typeFilterMenu.getChecked();}
			}],
			checkMenuActive: function() {
				var menuFilter = win.treePanel.down('#checkFilter');
				var menuActive = false;
				Ext6.each(win.typeFilterMenu.items.items, function(item) {
					if (item.xtype == 'menuseparator') return true;
					if (item.checked && !item.value.inlist(['0'])) {
						menuActive = true;
					}
				});

				if (menuActive) {
					menuFilter.addCls('filter-active');
				} else {
					menuFilter.removeCls('filter-active');
				}
			},
			getChecked: function() {
				win.historyFilterClassChecked = [];
				Ext6.each(win.typeFilterMenu.items.items, function(item) {
					if (item.xtype == 'menuseparator') return true;
					if (item.checked && !item.value.inlist(['0','999'])) {
						win.historyFilterClassChecked.push(item.value);
					}
				});
				this.setAllChecked();
				win.filterTreePanelTypeFilter.setValue(win.historyFilterClassChecked);
				win.filterHistory();
			},
			setChecked: function() {
				Ext6.each(win.typeFilterMenu.items.items, function(item) {
					if (item.xtype == 'menuseparator') return true;
					if (item.value.inlist(['0','999'])) return true;
					item.setChecked(item.value.inlist(win.historyFilterClassChecked));
				});
				this.setAllChecked();
				win.filterHistory();
			},
			setAllChecked: function(check) {
				if (!check) {
					var isAll = true;
					Ext6.each(win.typeFilterMenu.items.items, function(item) {
						if (item.xtype == 'menuseparator') return true;
						if (item.value.inlist(['0','999'])) return true;
						if (item.checked) isAll = false;
					});
					win.typeFilterMenu.items.items[0].setChecked(isAll);
				} else {
					Ext6.each(win.typeFilterMenu.items.items, function(item) {
						if (item.xtype == 'menuseparator') return true;
						if (item.value.inlist(['0','999'])) return true;
						item.setChecked(false);
						win.historyFilterClassChecked.remove(item.value);
					});
					win.typeFilterMenu.items.items[0].setChecked(true);
					win.filterTreePanelTypeFilter.setValue(win.historyFilterClassChecked);
					Ext6.each(win.filterTreePanelTypeFilter.items.items, function(item) {
						item.setPressed(false);
					});
					win.filterHistory();
				}

				this.checkMenuActive();
			}
		});

		win.Tree =  new Ext6.Panel({
			itemId: 'tree',
			region: 'center',
			layout: 'border',
			userCls: 'narrow-scroll',
			autoScroll: true,
			border: false,
			headerPosition: 'left',
			bodyStyle: 'background-color: #eee;',
			html: '',
			items: []
		});

		win.filterTreePanelTypeFilter = Ext6.create('Ext6.button.Segmented', {
			padding: 5,
			region: 'east',
			allowMultiple: true,
			cls: 'filters',
			defaults: {
				width: 21,
				height: 21
			},
			items: [{
				xtype: 'button',
				iconCls: 'ambulant',
				value: '3',
				tooltip: 'Амбулаторное лечение'
			}, {
				xtype: 'button',
				iconCls: 'dentist',
				value: '6',
				tooltip: 'Стоматологическое лечение'
			}, {
				xtype: 'button',
				iconCls: 'hospital',
				value: '30',
				tooltip: 'Стационарное лечение'
			}, {
				xtype: 'button',
				iconCls: 'emergency',
				//value: '901', // было
				value: '990',
				tooltip: 'Вызовы СМП'
			}, {
				xtype: 'button',
				iconCls: 'disp',
				value: '7',
				tooltip: 'Диспансеризация'
			}, {
				xtype: 'button',
				iconCls: 'sankur',
				value: '902',
				tooltip: 'Санаторно-курортное лечение'
			}],
			listeners: {
				toggle: function(container, button, pressed){
					if (pressed) {
						win.historyFilterClassChecked.push(button.getValue());
					} else {
						win.historyFilterClassChecked.remove(button.getValue());
					}
					win.typeFilterMenu.setChecked();
				}
			}
		});

		win.historyFilterDateRange = Ext6.create('Ext6.date.RangeField', {
			allowClearValue: true,
			emptyText: 'Выбрать период',
			hideLabel: true,
			value: null,
			width: 230,
			listeners: {
				'change': function (cm, ov, nv) {
					win.filterHistory();
				}
			}
		});

		win.filterTreePanel = new Ext6.form.FormPanel({
			layout: 'form',
			region: 'north',
			cls: 'filterspanel',
			border: false,
			bodyStyle: 'background-color: #2196f3;',
			items: [{
				height: 30,
				layout: 'border',
				bodyStyle: 'background-color: transparent; border-spacing: 0;',
				border: false,
				defaults: {
					bodyStyle: 'background-color: transparent;  border-spacing: 0;',
					border: false
				},
				items: [{
					region: 'center',
					items: [
						win.historyFilterDateRange
					]
				}, win.filterTreePanelTypeFilter
				]
			}]
		});

		win.treePanel =  new Ext6.Panel({
			//animCollapse: false,  // БЫЛО
			animCollapse: true,
			maxWidth: 550,
			minWidth: 390,
			floatable: false,
			split: true,
			flex: 120,
			region: 'west',
			layout: 'border',
			collapsible: true,
			header: false,
			title: {
				text: 'ЗАБОЛЕВАНИЯ И СЛУЧАИ ЛЕЧЕНИЯ',
				style:{'fontSize':'14px', 'fontWeight':'500'},
				rotation: 2,
				textAlign: 'right'
			},
			itemId: 'treePanel',
			tbar: {
				xtype: 'toolbar',
				cls: 'emkFilterPanel',
				items: [{
					text: 'Группа',
					margin: 0,
					xtype: 'button',
					menu: Ext6.create('Ext6.menu.Menu', {
						listeners: {
							'mouseleave': function(menu) {
								menu.autoHide(true);
							},
							'mouseenter': function(menu) {
								menu.autoHide(false);
							}
						},
						autoHide: function(needHide) {
							var menu = this;
							if (needHide) {
								menu.autoHideTimeout = setTimeout(function() {
									menu.hide();
								}, 2000);
							} else {
								if (menu.autoHideTimeout) {
									clearTimeout(menu.autoHideTimeout);
								}
							}
						},
						items: [{
							text: 'Без группировки',
							group: 'historyGroupMode',
							checked:true,
							handler: function(){win.setHistoryGroupMode(null)}
						}, {
							text: 'Типы случаев',
							group: 'historyGroupMode',
							checked: false,
							handler: function(){win.setHistoryGroupMode('evn')}
						}, {
							text: 'Документы',
							group: 'historyGroupMode',
							checked: false,
							handler: function(){win.setHistoryGroupMode('doc')}
						}]
					})
				}, {
					text: 'Период',
					margin: 0,
					xtype: 'button',
					menu: win.filterDatePicker
				}, {
					itemId: 'checkFilter',
					text: 'Фильтр',
					cls: 'filter-deactive',
					margin: 0,
					xtype: 'button',
					menu: win.typeFilterMenu
				}, '->', {
					emptyText: 'Быстрый поиск',
					cls: 'fastSearch',
					triggers: {
						search: {
							cls: 'x6-form-search-trigger',
							handler: function() {
								win.filterHistory();
							}
						}
					},
					itemId: 'historyTextFilter',
					xtype: 'textfield',
					listeners: {
						'change': function(field, oV, nV){
							this.filterHistory();
						}.createDelegate(this)
					}
				}, {
					cls: 'button-80',
					tooltip: 'Обновить',
					iconCls: 'action_refresh',
					handler: function() {
						win.loadTree();
					},
					xtype: 'button'
				}]
			},
			items: [ win.filterTreePanel, win.Tree ],
			listeners: {
				collapse: function(comp) {
					if(this.currentNode && this.currentNode.object == 'Person') {
						this.SignalInfoPanel.show();
					}
				}.createDelegate(this),
				expand: function(comp) {
					this.SignalInfoPanel.hide();
				}.createDelegate(this)
			}
		});

		win.htmlPanel = new Ext6.Panel({
			bodyStyle: 'background-color: #e3e3e3',
			scrollable: true,
			html: ""
		});

		win.rightPanel =  new Ext6.Panel({
			region: 'center',
			layout: 'card',
			cls: 'rightEmkPanel',
			bodyStyle: 'background-color: #e3e3e3',
			border: false,
			items: [
				win.htmlPanel
			]
		});

		win.ElectronicQueuePanel = new sw4.Promed.ElectronicQueuePanel({
			ownerWindow: win,
			panelType: 2,
			region: 'south',

			// функция выполняющаяся при нажатии на кнопку завершить прием
			completeServiceActionFn: function(params){

				var wnd = win,
					sender = wnd.ElectronicQueuePanel.senderData;

				// если ЭО связана с диспанcеризацией
				if (sender.DispClass_id && sender.DispClass_id > 0) {
					// если пункт в цепочке первый сохраняем согласия (если не сохранены)
					if (sender.electronicServiceNum && sender.electronicServiceNum == 1) {
						if (sender.EvnPLObjectId) wnd.saveDopDispInfoConsentList(sender.EvnPLObjectId)
					}
				}

                if (params.callback && typeof params.callback === 'function') {
                    params.callback({hideForm:wnd});
                }
			}
		});

		win.mainPanel = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			userCls: 'mainPanel',
			border: false,
			items: [
				win.treePanel,
				win.SignalInfoPanel,
				{
					flex: 300,
					animCollapse: false,
					floatable: false,
					collapsible: false,
					collapseDirection: 'right',
					region: 'center',
					layout: 'border',
					items: [ win.rightPanel ]
				}]
		});

		Ext6.apply(win, {
			items: [win.PersonInfoPanel, win.mainPanel, win.ElectronicQueuePanel]
		});

		this.callParent(arguments);
	},

	_setFormAllowed: function()
	{
		if (this.userMedStaffFact.ARMType == "polka")
		{
			if (this.EvnPLForm)
				this.EvnPLForm.setAllowed([11], {3: [11]},
					[11], {2: [11], 10: [11]});

			if (this.EvnPLStomForm)
				this.EvnPLStomForm.setAllowed();
		}
		else if (this.userMedStaffFact.ARMType == "stom6")
		{
			if (this.EvnPLForm)
				this.EvnPLForm.setAllowed();

			if (this.EvnPLStomForm)
				this.EvnPLStomForm.setAllowed([13], {3: [13]});
		}
		else
		{
			if (this.EvnPLForm)
				this.EvnPLForm.setAllowed();

			if (this.EvnPLStomForm)
				this.EvnPLStomForm.setAllowed();
		}
	}
});

