/**
* АРМ врача функциональной диагностики 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      март.2012
*/
sw.Promed.swWorkPlaceFuncDiagWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,
{
	id: 'swWorkPlaceFuncDiagWindow',
	gridPanelAutoLoad: false,
	listeners: {
		'beforehide': function()
		{
			sw.Applets.commonReader.stopReaders();
		}
	},
    buttons: [
        '-',
        {
            text: BTN_FRMHELP,
            iconCls: 'help16',
            handler: function(button, event)
            {
                ShowHelp(this.ownerCt.ARMName);
            }
        }, {
            text      : 'Закрыть',
            tabIndex  : -1,
            tooltip   : 'Закрыть',
            iconCls   : 'cancel16',
            handler   : function()
            {
                this.ownerCt.hide();
            }
        }
    ],
	//Список регионов, для которых открыт обновленный просмотровщик Dicom
	dicomViewRegionList: [2,3,19,30,35,40,63,58,59,91,101,77,66,10,50],
	hasPermissionToDicomViewer: function(){
		opts = getGlobalOptions();
		return (jQuery.inArray(opts.region.number, this.dicomViewRegionList)!=(-1));
	},
	getDataFromUec: function(uec_data, person_data) {
		var form = this;
		var grid = form.GridPanel.getGrid();
		var f = false;
		grid.getStore().each(function(record) {
			if (record.get('Person_id') == person_data.Person_id) {
				log('Найден в гриде');
				var index = grid.getStore().indexOf(record);
				grid.getView().focusRow(index);
				grid.getSelectionModel().selectRow(index);
				form.openEvnFuncRequestEditWindow('edit', false);
				f = true;
				return;
			}
		});
		if (!f) { // Если не нашли в гриде
			// todo: Еще надо проверку в принципе на наличие такого человека в БД, и если нет - предлагать добавлять
			// Открываем на добавление
			var params = {};
			params.action = 'add';
			params.Person_id =  person_data.Person_id;
			params.PersonEvn_id = (person_data.PersonEvn_id)?person_data.PersonEvn_id:null;
			params.Server_id = (person_data.Server_id)?person_data.Server_id:null;
			params.swPersonSearchWindow = getWnd('swPersonSearchWindow');
			params.onHide = function() {
				sw.Applets.commonReader.startReaders();
			}
			getWnd('swEvnFuncRequestEditWindow').show(params);
		}
	},
	cancelEvnUslugaPar: function(params) {
		var win = this;
		Ext.Msg.show({
			title: 'Отмена выполнения услуги',
			msg: 'Вы действительно хотите отменить выполнение услуги?',
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					win.getLoadMask('Отмена выполнения услуги').show();
					// отмена выполнения услуги
					Ext.Ajax.request({
						url: '/?c=EvnFuncRequest&m=cancelEvnUslugaPar',
						params: params,
						callback: function(opt, success, response) {
							win.getLoadMask().hide();
							win.GridPanel.refreshRecords(null, 0);
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},
	acceptWithoutRecord: function() { // приём без записи
		this.openEvnFuncRequestEditWindow('add', false);
	},
	returnToQueue: function(options) { // убрать в очередь
		var win = this;
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		var grid = this.GridPanel.getGrid();
		if (record == false || !record.get('TimetableResource_id')) {
			return false;
		}
		if(typeof(options) != 'object') {
			options = {};
		}

		if(!options.ignoreLinkCheck) {
			Ext.Ajax.request({
				url: '/?c=MedService&m=checkEQMedServiceLink',
				success: function(responseText) {
					var response = Ext.util.JSON.decode(responseText.responseText);
					if(response.MedServiceLinked == true) {
						sw.swMsg.show({
							icon: Ext.MessageBox.QUESTION,
							msg: 'При подтверждении действия пациент будет исключён из электронной очереди и поставлен в очередь на приём. Приём данного пациента по электронной очереди будет недоступен.',
							title: langs('Внимание'),
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj){
								if ('yes' == buttonId){	
									options.ignoreLinkCheck = 1;
									win.returnToQueue(options);
								}
							}
						});
					} else {
						options.ignoreLinkCheck = 1;
						win.returnToQueue(options);
					}
					return false;
				}
			});	
			return false;
		}

		return sw.Promed.Direction.returnToQueue({
			loadMask: this.getLoadMask('Пожалуйста, подождите...'),
			EvnDirection_id: record.get('EvnDirection_id'),
			TimetableResource_id: record.get('TimetableResource_id'),
			EvnQueue_id: record.get('EvnQueue_id'),
			callback: function (data) {
				grid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = grid.getStore().findBy( function(rec) {
								if ( rec.get('TimetableResource_id') == record.get('TimetableResource_id') ) {
									return true;
								}
							});
							if (index > -1) {
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
						}
					}
				});
			}
		});
	},
	sendEcg: function(options) { //отправить на ЭКГ
		var win = this;
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		var grid = this.GridPanel.getGrid();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана ни одна заявка');
			return false;
		}
		if (record && record.get('EvnUslugaPar_id') && record.get('Person_id')) {
			win.getLoadMask('Отправка на ЭКГ').show();
			Ext.Ajax.request({
				url: '/?c=ECG&m=getXMLfoECG',
				params: {
					EvnUslugaPar_id: record.get('EvnUslugaPar_id'),
					Person_id: record.get('Person_id')
				},
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success) {
							var ecg_server = Ext.globalOptions.ecg.ecg_server;
							var ecg_port = Ext.globalOptions.ecg.ecg_port;
							var socket = new WebSocket("ws://"+ecg_server+":"+ecg_port+"");
							socket.onopen = function(event){
								socket.send("sendMessage " + result.send["length"]);
								socket.send(result.send["xmlbase64"]);
								socket.close();
							}
							socket.onclose = function(event) {
								if (event.wasClean) {
									sw.swMsg.alert('Сообщение', 'Заявка отправлена');
								} else {
									sw.swMsg.alert('Сообщение', 'Невозможно соединиться с сервисом AI_ServerService. Обратитесь к администратору. Код ошибки: ' + event.code);
								}
							}
							socket.onmessage = function(event) {
								event;
							}
							socket.onerror = function(error) {
								socket.close();
								sw.swMsg.alert('Сообщение', 'Невозможно соединиться с сервисом AI_ServerService. Обратитесь к администратору');
							};
						}
					}
				}
			});
			}
			
	},
	sendWLQ: function() { // отправить заявку в рабочий список
		var win = this;
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		
		if (!record) {
			Ext.Msg.alert('Ошибка', 'Не выбрана ни одна заявка');
			return false;
		}
		if (record && record.get('EvnUslugaPar_id')) {
			win.getLoadMask('Добавление в очередь РС').show();
			var params = new Object();
				params.Data = [];
				params.Data.push({EvnUslugaPar_id: record.get('EvnUslugaPar_id')});
			Ext.Ajax.request({
				url: '/?c=WorkList&m=addRecordToDB',
				params: {
					Data: Ext.util.JSON.encode(params)
				},
				callback: function (options, success, response) {
					var result = Ext.util.JSON.decode(response.responseText)[0];
					win.getLoadMask().hide();
		            if(success) {
		                if(result) {
		                    if(result.Error_Message) sw.swMsg.alert(langs('Ошибка'), result.Error_Message);
							else showSysMsg(result.message, 'Рабочие списки');
		                }
		            } else {
		                sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при добавлении'));
		            }
				}
			});
		}
	},
	recordPersonFromQueue: function() { // запись из очереди
		var win = this;
		var rec = this.GridPanel.getGrid().getSelectionModel().getSelected();
		if (rec && rec.get('EvnQueue_id') && rec.get('Person_id')) {
			// надо получить по заявке все её услуги.
			win.getLoadMask('Получение списка услуг заявки...').show();
			Ext.Ajax.request({
				url: '/?c=EvnFuncRequest&m=getEvnFuncRequestUslugaComplex',
				params: {
					EvnFuncRequest_id: rec.get('EvnFuncRequest_id')
				},
				callback: function(options, success, response) {
					win.getLoadMask().hide();

					if ( success ) {
						var UslugaComplex_ids = [];
						var response_obj = Ext.util.JSON.decode(response.responseText);
						response_obj.forEach(function(respone) {
							if (respone.UslugaComplex_id && !respone.UslugaComplex_id.inlist(UslugaComplex_ids)) {
								UslugaComplex_ids.push(respone.UslugaComplex_id);
							}
						});

						getWnd('swTTRScheduleRecordWindow').show({
							disableRecord: true,
							MedService_id: win.msData.MedService_id,
							UslugaComplex_ids: UslugaComplex_ids,
							MedServiceType_id: win.msData.MedServiceType_id,
							MedService_Nick: win.msData.MedService_Nick,
							MedService_Name: win.msData.MedService_Name,
							MedServiceType_SysNick: win.msData.MedServiceType_SysNick,
							Lpu_did: win.msData.Lpu_id,
							LpuUnit_did: win.msData.LpuUnit_id,
							LpuUnitType_SysNick: win.msData.LpuUnitType_SysNick,
							LpuSection_uid: win.msData.LpuSection_id,
							LpuSection_Name: win.msData.LpuSection_Name,
							LpuSectionProfile_id: win.msData.LpuSectionProfile_id,
							userMedStaffFact: win.userMedStaffFact,
							callback: function (ttms) {
								if (ttms.TimetableResource_id > 0) {
									getWnd('swTTRScheduleRecordWindow').hide();
									win.getLoadMask('Запись пациента...').show();
									// нужно записать человека на эту бирку
									Ext.Ajax.request({
										url: '/?c=Queue&m=ApplyFromQueue',
										params: {
											TimetableResource_id: ttms.TimetableResource_id,
											EvnDirection_id: rec.get('EvnDirection_id'),
											EvnQueue_id: rec.get('EvnQueue_id'),
											Person_id: rec.get('Person_id')
										},
										callback: function (options, success, response) {
											win.getLoadMask().hide();
											win.GridPanel.refreshRecords(null, 0);
										},
										failure: function () {
											win.getLoadMask().hide();
										}
									});
								}
							}
						});
					}
				},
				failure: function() {
					win.getLoadMask().hide();
				}
			});
		}
	},
	add20ToQueue: function() {
		var win = this;
		var params = win.FilterPanel.getForm().getValues();
		params.MedService_id = this.MedService_id;
		params.wnd_id = this.id;
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		var i = 0;
		this.GridPanel.getGrid().getStore().each(function(rec){
			if(Ext.isEmpty(rec.get('Resource_Name')))
				i++;
		});
		if(i%20 > 0){
			sw.swMsg.alert('Сообщение', 'Вся очередь отображена');
			Ext.select(".add20Btn").fadeOut();
			return false;
		}
		var fl = Math.floor(i/20);
		if(fl>0){
			params.start = 20*fl;
		}

		params.limit = 20;

		win.getLoadMask('Загрузка дополнительных записей в Очередь').show();
		Ext.Ajax.request({
			url: '/?c=EvnFuncRequest&m=loadEvnFuncQueueRequestList',
			params: params,
			callback: function (opt, success, response) {
				win.getLoadMask().hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj.length > 0){
						win.GridPanel.getGrid().getStore().loadData(response_obj, true);
						win.GridPanel.getGrid().getView().scroller.dom.scrollTop = win.GridPanel.getGrid().getView().scroller.dom.scrollTopMax;
					} else {
						Ext.select(".add20Btn").fadeOut();
					}
				}
			}
		});
	},
	/*showOnly20: function(only20) {
		var win = this;

		if (only20) {
			this.GridPanel.only20 = true;
			Ext.select('.showOnly20on').setStyle('color' , '#000');
			Ext.select('.showOnly20off').setStyle('color' , '');

			var i = 0;
			Ext.select('.x-grid-queue-group .x-grid3-row').each(function(row) {
				i++;
				if (i > 20) {
					row.setStyle('display', 'none');
				}
			});
		} else {
			this.GridPanel.only20 = false;
			Ext.select('.showOnly20on').setStyle('color' , '');
			Ext.select('.showOnly20off').setStyle('color' , '#000');

			Ext.select('.x-grid-queue-group .x-grid3-row').setStyle('display', 'block');
		}
	},*/
	show: function()
	{
		sw.Promed.swWorkPlaceFuncDiagWindow.superclass.show.apply(this, arguments);

		var win = this;
		// Свои функции при открытии
		
		if ( arguments[0].MedService_id ) {
			this.MedService_id = arguments[0].MedService_id;
		} else {
			// Не понятно, что за АРМ открывается 
			return false;
		}

		win.ElectronicQueuePanel.initElectronicQueue();

		win.GridPanel.setColumnHidden('sendToRCC_link', true);
		Ext.Ajax.request({
			url: '/?c=MedService&m=checkMedServiceHasLinked',
			params: {
				MedService_id: win.MedService_id,
				MedServiceLinkType_Code: 3
			},
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var result  = Ext.util.JSON.decode(response.responseText);
					if (result.cnt > 0) {
						win.GridPanel.setColumnHidden('sendToRCC_link', false);
					}
				}
			}
		});

		Ext.Ajax.request({
			url: '/?c=WorkList&m=getMedProductCardIsWL',
			params: {
				MedService_id: win.MedService_id,
				MedProductCard_IsWorkList: 2 // есть признак работы с рабочим списком (ref #95987)
			},
			callback: function(options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText)[0];
				if(success) {
					if(result) {
						Ext.getCmp('action_WorkList').setVisible(result.btn_isHidden);
						Ext.getCmp('action_wl').setVisible(result.btn_isHidden);
					}
				} 
			}
		});

		sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});

		this.Lpu_id = arguments[0].Lpu_id || null;
        this.Lpu_Nick = arguments[0].Lpu_Nick || '';
        this.LpuUnit_id = arguments[0].LpuUnit_id || null;
        this.LpuUnit_Name = arguments[0].LpuUnit_Name || '';
		this.LpuSection_id = arguments[0].LpuSection_id || null;
        this.LpuSection_Name = arguments[0].LpuSection_Name || '';
        this.LpuSection_Nick = arguments[0].LpuSection_Nick || '';
        this.LpuUnitType_id = arguments[0].LpuUnitType_id || null;
        this.LpuUnitType_SysNick = arguments[0].LpuUnitType_SysNick || '';
        this.MedPersonal_id = arguments[0].MedPersonal_id || null;
        this.MedPersonal_FIO = arguments[0].MedPersonal_FIO || '';
        this.MedService_Name = arguments[0].MedService_Name || '';
        this.MedService_Nick = arguments[0].MedService_Nick || '';
        this.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick || 'func';
		this.ARMName = arguments[0].ARMName || '';
		
		//отправка заявки на ЭКГ
		if(getRegionNick()=='ufa') {
			Ext.Ajax.request({
				url: '/?c=MedService&m=checkMedServiceUsluga',
				params: {
					MedService_id: win.MedService_id
				},
				callback: function(opt, success, response) {
					if (success && response.responseText != '') {
						var result  = Ext.util.JSON.decode(response.responseText);
						win.GridPanel.getAction('action_ecg').setHidden(!result.checkMedServiceUsluga);
					}
				}
			});
		}
		
        if(getRegionNick()=='ekb' && !this.ElectronicQueuePanel.electronicQueueEnable)
			this.GridPanel.getAction('action_extdir').show();
		else
			this.GridPanel.getAction('action_extdir').hide();
		
		//Подгружаем данные службы, нужные для записи
		var ms_combo = this.FilterPanel.getForm().findField('MedService_id');
		this.msData = {};
		ms_combo.getStore().load({
			params: { MedService_id: this.MedService_id },
			callback: function(r,o,s) {
				if(r.length > 0) {
					this.msData = r[0].data;
				}
			}.createDelegate(this)
		});
		
		var usluga_combo = this.FilterPanel.getForm().findField('UslugaComplex_id');
		usluga_combo.getStore().removeAll();
		usluga_combo.getStore().load({
			params: {
				MedService_id: this.MedService_id,
				level: 0
			}
		});
		usluga_combo.getStore().baseParams = {
			MedService_id: this.MedService_id,
			level: 0
		};		
		this.searchParams = {'MedService_id':this.MedService_id, 'wnd_id':this.id, 'start':0, 'limit':20}; // для фильтрации направлений по службе
		this.afterShowFlag = true;
		this.numQueue = 0;
		this.doSearch('day');

		log('userMedStaffFact', this.userMedStaffFact);
		log('gridStore', this.GridPanel.getGrid().getStore())

    },
    getIdUslugaWindow: function()
    {
        return (this.hasPermissionToDicomViewer())?'swEvnUslugaFuncRequestDicomViewerEditWindow':'swEvnUslugaFuncRequestEditWindow';
    },
    openUslugaWindow: function(params)
    {
		var form = this;
		var selModel = form.GridPanel.getGrid().getSelectionModel();
		var record = (selModel.getSelected()) ? selModel.getSelected() : form.ElectronicQueuePanel.getLastSelectedRecord() ;

		//form.GridPanel.getGrid().getSelectionModel().selectFirstRow();
		//form.GridPanel.getGrid().getView().focusRow(0);

		log('record', record);
		if ( typeof params != 'object' ) params = new Object();

		if (record) {

			params.Person_id = record.get('Person_id');
			params.EvnDirection_id = (record.get('EvnDirection_id') ? record.get('EvnDirection_id') : null);
			params.EvnFuncRequest_id = (record.get('EvnFuncRequest_id') ? record.get('EvnFuncRequest_id') : null);
			params.Resource_id = (record.get('Resource_id') ? record.get('Resource_id') : null);
			params.MedService_id = form.MedService_id;
			params.parentEvnClass_SysNick = record.get('parentEvnClass_SysNick');

			// если не передан параметр услуги, т.е. не нажали услугу в строке грида, а нажали "принять" на панели ЭО
			if (!params.EvnUslugaPar_id) {

				var servicesList = Ext.util.JSON.decode(record.get('EvnFuncRequest_UslugaCache'));

				if (servicesList) {
					for (var k in servicesList) {
						if (servicesList[k].UslugaComplex_Name) {
							params.EvnUslugaPar_id = servicesList[k].EvnUslugaPar_id;
							// только самый первый, ну а чо делать...
							break;
						}
					}
				}
			}
		}

		if (Ext.isEmpty(params.EvnUslugaPar_id)) {
			sw.swMsg.alert('Ошибка', 'В заявке не указана услуга.');
			form.ElectronicQueuePanel.cancelCall()
			return false;
		}

		var electronicQueueData = (form.ElectronicQueuePanel.electronicQueueData
				? form.ElectronicQueuePanel.electronicQueueData
				: form.ElectronicQueuePanel.getElectronicQueueData()
		);

		if (form.ElectronicQueuePanel.electronicQueueData) form.ElectronicQueuePanel.electronicQueueData = null;

		params.userMedStaffFact = this.userMedStaffFact;
		params.electronicQueueData = electronicQueueData;
		params.Lpu_id = this.Lpu_id;
		params.LpuSection_id = this.LpuSection_id;
		params.MedPersonal_id = (!params.MedPersonal_id)?this.MedPersonal_id:params.MedPersonal_id;
		params.MedService_id = (!params.MedService_id)?this.MedService_id:params.MedService_id;
	
		params.callback = function(retParams) {

			// выполняем кэллбэк
			if (retParams && retParams.callback && typeof retParams.callback === 'function') retParams.callback();

			// если нет ЭО
			if (!form.ElectronicQueuePanel.electronicQueueEnable) { this.GridPanel.refreshRecords(null, 0) }

		}.createDelegate(this);
		
		var opts = getGlobalOptions();
		var wnd = (this.hasPermissionToDicomViewer())?'swEvnUslugaFuncRequestDicomViewerEditWindow':'swEvnUslugaFuncRequestEditWindow';
		
		getWnd(wnd).show(params);
    },
    checkApp: function(params) {
		var win = this;
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();

				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.length) {
						getWnd('swEvnDirectionFuncDiagSelectWindow').show({
							params: params,
							dir_list: response_obj
						});
					} 
					else {
						getWnd('swEvnFuncRequestEditWindow').show(params);
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при получении направлений'));
				}
			}.createDelegate(this),
			params: {
				Person_id: params.Person_id
			},
			url: '/?c=ExchangeBL&m=getRefferalByPerson'
		});
	},
	openEvnFuncRequestEditWindow: function(action, is_time) { // is_time - признак записи на бирку.
		var form = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		sw.Applets.commonReader.stopReaders();

		var swPersonSearchWindow = getWnd('swPersonSearchWindow');
		if ( action == 'add' && swPersonSearchWindow.isVisible() ) {
			sw.swMsg.alert('Окно поиска человека уже открыто', 'Для продолжения необходимо закрыть окно поиска человека.');
			return false;
		}



		var params = new Object();

		params.MedService_id = this.MedService_id;

		params.action = action;
		params.callback = function(data) {};
        params.swWorkPlaceFuncDiagWindow = form;

		if ( action == 'add' ) {
			if ( record && record.get('TimetableResource_id') && is_time == true ) {
				params.TimetableResource_id = record.get('TimetableResource_id');
				params.Resource_id = record.get('Resource_id');
			}

            swPersonSearchWindow.show({
				onClose: function() {
					sw.Applets.commonReader.startReaders();
					if ( record ) {
						grid.getView().focusRow(grid.getStore().indexOf(record));
					}
					else {
						//grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onSelect: function(person_data) {
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
                    params.swPersonSearchWindow = swPersonSearchWindow;
					params.onHide = function() {
						if (swPersonSearchWindow.isVisible()) {
							//На форме поиска человека нет такого метода
							//swPersonSearchWindow.startUecReader();
						}

					};
					this.hide(); // закрываем форму поиска человека
					if (getRegionNick() != 'kz') {
						getWnd('swEvnFuncRequestEditWindow').show(params);
					} else {
						form.checkApp(params);
					}
				},
				searchMode: 'all',
				needUecIdentification: true
			});

		} else {

			if ( !record || !record.get('EvnDirection_id') ) {
				sw.swMsg.alert('Ошибка', 'Не выбрана заявка или направление!');
				return false;
			}

			params.EvnFuncRequest_id = record.get('EvnFuncRequest_id');
			params.EvnDirection_id = record.get('EvnDirection_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
			};
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');
			params.onHide = function() {
				sw.Applets.commonReader.startReaders();
			};

			getWnd('swEvnFuncRequestEditWindow').show(params);

		}
    },
	recordPerson: function() { // запись на бирку
		this.openEvnFuncRequestEditWindow('add', true);
	},
	/**
	 * Отправить в центр удалённой консультации
	 */
	sendToRCC: function(data) {

		if (!data||!data.EvnUslugaPar_id) {
			sw.swMsg.alert('Ошибка', 'Не выбрано исследование для передачи в ЦУК!');
			return false;
		}

		var grid = this.GridPanel,
			loadMask = this.getLoadMask('Пожалуйста, подождите. Идет передача заявки в ЦУК...');
        loadMask.show();
		Ext.Ajax.request({
			url: '/?c=EvnFuncRequest&m=sendUslugaParToRCC',
			params: {
				EvnUslugaPar_id:data.EvnUslugaPar_id,
				MedService_lid: (sw.Promed.MedStaffFactByUser.current.MedService_id)?sw.Promed.MedStaffFactByUser.current.MedService_id:null

			},
			callback: function(options, success, response) {
				loadMask.hide();
				grid.refreshRecords(null, 0);
			},
			failure: function() {
				loadMask.hide();
			}
		});
	},
    printCost: function() {
        var grid = this.GridPanel.getGrid();
        var selected_record = grid.getSelectionModel().getSelected();
        if (selected_record && selected_record.get('EvnFuncRequest_id')) {
            getWnd('swCostPrintWindow').show({
                Evn_id: selected_record.get('EvnFuncRequest_id'),
                type: 'EvnFuncRequest',
                callback: function() {
                    grid.getStore().reload();
                }
            });
        }
    },
	printPatientList: function() {
		var grid = this.GridPanel.getGrid();
		if (!grid) {
			Ext.Msg.alert(langs('Ошибка'), langs('Список расписаний не найден'));
			return false;
		}
		var MedService_id = this.MedService_id,
			begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y'),
			endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y'),
			id_salt = Math.random(),
			win_id = 'print_pac_list' + Math.floor(id_salt * 10000);

		window.open('/?c=TimetableGraf&m=printPacList&begDate=' + begDate + '&endDate=' + endDate + '&MedService_id=' + MedService_id + '&isPeriod=' + 2, win_id);
	},
    /**
     * Инициализация формы
     */
    initComponent: function() {

		var form = this;

		this.buttonPanelActions = {
			action_Timetable: {
				nn: 'action_Timetable',
					tooltip: 'Работа с расписанием службы',
					text: 'Расписание службы',
					iconCls : 'mp-timetable32',
					disabled: false,
					handler: function()
				{
					getWnd('swTTRScheduleEditWindow').show({
						MedService_id: form.MedService_id,
						MedService_Name: form.MedService_Name
					});
				}.createDelegate(this)
			},
			action_Spr:{
				nn: 'action_Spr',
					iconCls: 'book32',
					text: 'Справочники',
					hidden: true,
					tooltip: 'Справочники',
					menu: new Ext.menu.Menu({
					items: [
						{
							tooltip: 'Справочник ' + getMESAlias(),
							text: 'Справочник ' + getMESAlias(),
							hidden: !((getGlobalOptions().region.nick == 'ekb')||(getGlobalOptions().region.nick == 'perm')),
							iconCls: 'spr-mes16',
							handler: function() {
								if ( !getWnd('swMesOldSearchWindow').isVisible() )
									getWnd('swMesOldSearchWindow').show();
							}.createDelegate(this)
						},
						{
							text: 'Справочник услуг',
							tooltip: 'Справочник услуг',
							iconCls: 'services-complex16',
							handler: function() {
								getWnd('swUslugaTreeWindow').show({action: (isUserGroup('LpuUser') || isUserGroup('OrgUser'))?'view':''});
							},
							hidden: !isAdmin
						},
						sw.Promed.Actions.swDrugDocumentSprAction,
						{
							text: WND_DLO_DRUGMNNLATINEDIT,
							tooltip: 'Редактирование латинского наименования МНН',
							iconCls : 'drug-viewmnn16',
							handler: function()
							{
								getWnd('swDrugMnnViewWindow').show({
									privilegeType: 'all'
								});
							}
						},
						{
							text: WND_DLO_DRUGTORGLATINEDIT,
							tooltip: 'Редактирование латинского наименования медикамента',
							iconCls : 'drug-viewtorg16',
							handler: function()
							{
								getWnd('swDrugTorgViewWindow').show();
							}
						},
						{
							text: 'Справочник медикаментов в ' + getCountryName('predl'),
							tooltip: 'Справочник медикаментов в ' + getCountryName('predl'),
							iconCls: 'rls16',
							handler: function()
							{
								getWnd('swRlsViewForm').show({action: (isUserGroup('LpuUser') || isUserGroup('OrgUser'))?'view':''});
							},
							hidden: false
						}
					]
				})
			},
			GlossarySearchAction: {
				text: 'Глоссарий',
					tooltip: 'Глоссарий',
					iconCls : 'glossary16',
					hidden: true,
					nn: 'GlossarySearchAction',
					handler: function()
				{
					getWnd('swGlossarySearchWindow').show();
				}
			},
			actions_person_search:{
				handler: function()
				{
					getWnd('swPersonSearchWindow').show({
						onSelect: function(person_data) {
							getWnd('swPersonEditWindow').show({
								onHide: function () {
									if ( person_data.onHide && typeof person_data.onHide == 'function' ) {
										person_data.onHide();
									}
								},
								Person_id: person_data.Person_id,
								Server_id: person_data.Server_id
							});
						},
						searchMode: 'all'
					});
				},
				hidden: true,
					iconCls : 'patient-search32',
					nn: 'action_PersonSearch',
					text: 'Поиск пациента',
					tooltip: 'Поиск пациента'
			},
			action_histo_direction: {
				nn: 'action_histo_direction',
					iconCls: 'lab32',
					text: 'Направление на патологогистологическое исследование',
					tooltip: 'Направление на патологогистологическое исследование',
					handler: function(){
					getWnd('swEvnDirectionHistologicViewWindow').show();
				}
			},
			actions_settings: {
				nn: 'actions_settings',
					iconCls: 'settings32',
					text: 'Сервис',
					tooltip: 'Сервис',
					listeners: {
					'click': function(){
						var menu = Ext.menu.MenuMgr.get('wpfdw_menu_windows');
						menu.removeAll();
						var number = 1;
						Ext.WindowMgr.each(function(wnd){
							if ( wnd.isVisible() )
							{
								if ( Ext.WindowMgr.getActive().id == wnd.id )
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'checked16',
											checked: true,
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
								else
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'x-btn-text',
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
							}
						});
						if ( menu.items.getCount() == 0 )
							menu.add({
								text: 'Открытых окон нет',
								iconCls : 'x-btn-text',
								handler: function()
								{
								}
							});
						else
						{
							menu.add(new Ext.menu.Separator());
							menu.add(new Ext.menu.Item(
								{
									text: 'Закрыть все окна',
									iconCls : 'close16',
									handler: function()
									{
										Ext.WindowMgr.each(function(wnd){
											if ( wnd.isVisible() )
											{
												wnd.hide();
											}
										});
									}
								})
							);
						}
					}
				},
				menu: new Ext.menu.Menu({
					items: [
						{
							nn: 'action_UserProfile',
							text: 'Мой профиль',
							tooltip: 'Профиль пользователя',
							iconCls : 'user16',
							hidden: false,
							handler: function()
							{
								args = {};
								args.action = 'edit';
								getWnd('swUserProfileEditWindow').show(args);
							}
						},
						{
							text: 'Выбор АРМ по умолчанию',
							tooltip: 'Выбор АРМ по умолчанию',
							iconCls: 'lab-assist16',
							handler: function()
							{
								getWnd('swSelectWorkPlaceWindow').show();
							}
						},
						{
							nn: 'action_settings',
							text: 'Настройки',
							tooltip: 'Просмотр и редактирование настроек',
							iconCls : 'settings16',
							handler: function()
							{
								getWnd('swOptionsWindow').show();
							}
						},
						{
							nn: 'action_selectMO',
							text: 'Выбор МО',
							tooltip: 'Выбор МО',
							hidden: !isSuperAdmin(),
							iconCls: 'lpu-select16',
							handler: function()
							{
								Ext.WindowMgr.each(function(wnd){
									if ( wnd.isVisible() )
									{
										wnd.hide();
									}
								});
								getWnd('swSelectLpuWindow').show({});
							}
						},
						{
							text:'Помощь',
							nn: 'action_help',
							iconCls: 'help16',
							menu: new Ext.menu.Menu(
								{
									//plain: true,
									id: 'menu_help',
									items:
										[
											{
												text: 'Вызов справки',
												tooltip: 'Помощь по программе',
												iconCls : 'help16',
												handler: function()
												{
													ShowHelp('Содержание');
												}
											},
											{
												text: 'Форум поддержки',
												iconCls: 'support16',
												xtype: 'tbbutton',
												handler: function() {
													window.open(ForumLink);
												}
											},
											{
												text: 'О программе',
												tooltip: 'Информация о программе',
												iconCls : 'promed16',
												testId: 'mainmenu_help_about',
												handler: function()
												{
													getWnd('swAboutWindow').show();
												}
											}
										]
								}),
							tabIndex: -1
						},
						{
							text: 'Информация о пользователе',
							nn: 'action_user_about',
							iconCls: 'user16',
							menu: new Ext.menu.Menu(
								{
									//plain: true,
									id: 'user_menu',
									items:
										[
											{
												disabled: true,
												iconCls: 'user16',
												text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+Ext.globalOptions.globals.lpu_nick,
												xtype: 'tbtext'
											}
										]
								})
						},
						{
							text: 'Окна',
							nn: 'action_windows',
							iconCls: 'windows16',
							listeners: {
								'click': function(e) {
									var menu = Ext.menu.MenuMgr.get('wpfdw_menu_windows');
									menu.removeAll();
									var number = 1;
									Ext.WindowMgr.each(function(wnd){
										if ( wnd.isVisible() )
										{
											if ( Ext.WindowMgr.getActive().id == wnd.id )
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'checked16',
														checked: true,
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
											else
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'x-btn-text',
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
										}
									});
									if ( menu.items.getCount() == 0 )
										menu.add({
											text: 'Открытых окон нет',
											iconCls : 'x-btn-text',
											handler: function()
											{
											}
										});
									else
									{
										menu.add(new Ext.menu.Separator());
										menu.add(new Ext.menu.Item(
											{
												text: 'Закрыть все окна',
												iconCls : 'close16',
												handler: function()
												{
													Ext.WindowMgr.each(function(wnd){
														if ( wnd.isVisible() )
														{
															wnd.hide();
														}
													});
												}
											})
										);
									}
								},
								'mouseover': function() {
									var menu = Ext.menu.MenuMgr.get('wpfdw_menu_windows');
									menu.removeAll();
									var number = 1;
									Ext.WindowMgr.each(function(wnd){
										if ( wnd.isVisible() )
										{
											if ( Ext.WindowMgr.getActive().id == wnd.id )
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'checked16',
														checked: true,
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
											else
											{
												menu.add(new Ext.menu.Item(
													{
														text: number + ". " + wnd.title,
														iconCls : 'x-btn-text',
														handler: function()
														{
															Ext.getCmp(wnd.id).toFront();
														}
													})
												);
												number++;
											}
										}
									});
									if ( menu.items.getCount() == 0 )
										menu.add({
											text: 'Открытых окон нет',
											iconCls : 'x-btn-text',
											handler: function()
											{
											}
										});
									else
									{
										menu.add(new Ext.menu.Separator());
										menu.add(new Ext.menu.Item(
											{
												text: 'Закрыть все окна',
												iconCls : 'close16',
												handler: function()
												{
													Ext.WindowMgr.each(function(wnd){
														if ( wnd.isVisible() )
														{
															wnd.hide();
														}
													});
												}
											})
										);
									}
								}
							},
							menu: new Ext.menu.Menu(
								{
									//plain: true,
									id: 'wpfdw_menu_windows',
									items: [
										'-'
									]
								}),
							tabIndex: -1
						}
					]
				})
			},
			action_Templ: {
				handler: function() {
					var params = {
						LpuSection_id: form.userMedStaffFact.LpuSection_id,
						MedPersonal_id: form.userMedStaffFact.MedPersonal_id,
						MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id,
						XmlType_id: 4,
						allowSelectXmlType: true,
						EvnClass_id: 47
					};
					getWnd('swTemplSearchWindow').show(params);
				},
				iconCls : 'card-state32',
					nn: 'action_Templ',
					text: 'Шаблоны документов',
					tooltip: 'Шаблоны документов'
			},
			action_JourNotice: {
				nn: 'action_JourNotice',
					text: 'Журнал уведомлений',
					tooltip: 'Открыть журнал уведомлений',
					iconCls: 'notice32',
					handler: function () {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this)
			},
			action_EvnUslugaParSearch: {
				nn: 'action_EvnUslugaParSearch',
					text: 'Параклинические услуги: Поиск',
					tooltip: 'Параклинические услуги: Поиск',
					iconCls: 'para-service32',
					handler: function () {
					getWnd('swEvnUslugaParSearchWindow').show({
						LpuSection_id: form.userMedStaffFact.LpuSection_id
					});
				}.createDelegate(this)
			},
			action_DirectionJournal: {
				nn: 'action_DirectionJournal',
					text: WND_DIRECTION_JOURNAL,
					tooltip: WND_DIRECTION_JOURNAL,
					iconCls : 'mp-queue32',
					handler: function() {
					getWnd('swMPQueueWindow').show({
						ARMType: 'funcdiag',
						callback: function(data) {
							// this.createTtgAndOpenPersonEPHForm(data);
							// this.scheduleRefresh();
						}.createDelegate(this),
						mode: 'view',
						userMedStaffFact: this.userMedStaffFact,
						onSelect: function(data) { // на тот случай если из режима просмотра очереди будет сделана запись
							getWnd('swMPQueueWindow').hide();
							getWnd('swMPRecordWindow').hide();
							// Ext.getCmp('swMPWorkPlaceWindow').scheduleSave(data);
						}
					});
				}.createDelegate(this)
			},
			action_QueryEvn:
			{
				disabled: false, 
				handler: function() 
				{
					getWnd('swQueryEvnListWindow').show({ARMType: 'funcdiag'});
				},
				iconCls: 'mail32',
				nn: 'action_QueryEvn',
				text: 'Журнал запросов',
				tooltip: 'Журнал запросов',
			},
			action_WorkList:
			{ 
				handler: function() 
				{
					getWnd('swWorkListWindow').show(form.MedService_id);
				}.createDelegate(this),
				iconCls: 'card-state32',
				nn: 'action_WorkList',
				id: 'action_WorkList',
				text: 'Рабочие списки',
				tooltip: 'Рабочие списки'
			}
		};

		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			filter: {
				title: 'Фильтр',
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 65,
						items:
						[{
							xtype: 'swmedserviceglobalcombo',
							hidden: true,
							disabled: true,
                            hideLabel: true
						}/*, {
							xtype: 'textfieldpmw',
							width: 150,
							name: 'Search_SurName',
							fieldLabel: 'Фамилия',
							listeners: {
								'keydown': form.onKeyDown
							}
						}*/]
					    },
                        {
                         layout: 'form',
                         labelWidth: 65,
                         items:
                         [{
                             xtype: 'textfieldpmw',
                             width: 150,
                             name: 'Search_SurName',
                             fieldLabel: 'Фамилия',
                             listeners: {
                                 'keydown': form.onKeyDown
                             }
                          }]
                        },
                        {
						layout: 'form',
						labelWidth: 45,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 150,
							name: 'Search_FirName',
							fieldLabel: 'Имя',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 150,
							name: 'Search_SecName',
							fieldLabel: 'Отчество',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 35,
						items:
						[{
							xtype:'swdatefield',
							format:'d.m.Y',
							plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Search_BirthDay',
							fieldLabel: 'ДР',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 145,
						items:
						[{
							xtype: 'textfield',
							width: 100,
							name: 'EvnDirection_Num',
							fieldLabel: 'Номер направления',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 65,
						items:
						[{
							xtype: 'swuslugacomplexmedservicecomdo',
							width: 450,
							baseParams: {UslugaGost_Code: 'FU', level:0},
							name: 'Search_Usluga',
							fieldLabel: 'Услуга',
							allowBlank: true,
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 55,
						items:
						[{
							fieldLabel: 'Cito',
							comboSubject: 'YesNo',
							name: 'EvnDirection_IsCito',
							hiddenName: 'EvnDirection_IsCito',
							xtype: 'swcommonsprcombo'
						}]
					}, {
						layout: 'form',
						labelWidth: 45,
                        hidden: getRegionNick() != 'kz',
						items:
							[{
                                allowBlank: true,
                                autoCreate: {tag: "input", type: "text", size: "30", maxLength: "12", autocomplete: "off"},
                                fieldLabel: langs('ИИН'),
                                maskRe: /\d/,
                                maxLength: 12,
                                minLength: 0,
                                name: 'Search_PersonInn',
                                width: 100,
                                xtype: 'textfield',

							}]
					}, {
						layout: 'form',
						labelWidth: 100,
                        hidden: getRegionNick() != 'kz',
						items:
							[{
								fieldLabel: langs('Кем направлен'),
								hiddenName: 'Search_LpuId',
								xtype: 'swlpucombo',
								width: 300
					},{
							layout: 'form',
							labelWidth: 204,
						hidden: ['ufa', 'buryatiya'].indexOf(getRegionNick()) < 0,
							items: [{
								xtype: 'numberfield',
								allowDecimals: false,
								width: 67,
								name: 'Person_id',
								fieldLabel: langs('Идентификатор пациента'),
								listeners: {
									'keydown': form.onKeyDown
								}
							}]
						}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items:
						[{
							xtype: 'button',
							id: form.id+'BtnSearch',
							text: 'Найти',
							iconCls: 'search16',
							handler: function()
							{
								form.doSearch();
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items:
						[{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id+'BtnClear',
							text: 'Сброс',
							iconCls: 'reset16',
							handler: function()
							{
								form.doReset();
                                this.searchParams = {'MedService_id':this.MedService_id, 'wnd_id':this.id, 'start':0, 'limit':this.numQueue}; // для фильтрации направлений по службе
                                this.doSearch('day');
                                this.searchParams.limit = 20;
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								text: 'Считать с карты',
								iconCls: 'idcard16',
								handler: function()
								{
									form.readFromCard();
								}
							}]
					}]
				}]
			}
		]}
		});

		let renderEvnDirection_Num = function (value, cellEl, rec) {
			let EvnDirection_Num = rec.get('EvnDirection_Num');
			if (form.hasPermissionToDicomViewer()) {
				if (Ext.isEmpty(rec.get('EvnDirection_id'))) {
					EvnDirection_Num = '<a href="javascript://" onClick="Ext.getCmp(\'' + form.id + '\').openEvnFuncRequestEditWindow(\'add\', true)">' + rec.get('EvnDirection_Num') + '</a>';
				} else {
					EvnDirection_Num = '<a href="javascript://" onClick="Ext.getCmp(\'' + form.id + '\').openEvnFuncRequestEditWindow(\'edit\', false)">' + rec.get('EvnDirection_Num') + '</a>';
				}
			}
			return EvnDirection_Num;
		};

		let renderSendToRCC_link = function (value, cellEl, rec) {
			let sendToRCC_link = rec.get('sendToRCC_link');
			if (rec.get('RemoteConsultCenterResearch_id')) {
				sendToRCC_link = 'Отправлено в ЦУК'
			} else if (rec.get('EvnUslugaPar_id') && rec.get('RCC_MedService_id')) {
				sendToRCC_link = '<a href="javascript://" onClick="Ext.getCmp(\'' + form.id + '\').sendToRCC({EvnUslugaPar_id:' + rec.get('EvnUslugaPar_id') + '})"> Отправить в ЦУК </a>';
			} else if (rec.get('EvnUslugaPar_id') && !rec.get('RCC_MedService_id')) {
				sendToRCC_link = '<p style="color: gray;" > (невозможно) </p>';
			}
			return sendToRCC_link;
		};

		this.GridPanel = new sw.Promed.ViewFrame({
			uniqueId: true,
            useEmptyRecord: false,
            object: 'EvnFuncRequest',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			isScrollToTopOnLoad: false,
			//only20: true,
			interceptMouse : function(e){
				var hdon = e.getTarget('.showOnly20on', this.mainBody);
				var hdoff = e.getTarget('.showOnly20off', this.mainBody);
				var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
				var hdel = e.getTarget('.x-grid-group-hd', this.mainBody, true);
				if(hd && !hdon && !hdoff){
					e.stopEvent();
					if(hdel.hasClass('queuegrp')){
						var i = hd.nextSibling.childNodes.length;
						if(i%20 > 0){
							Ext.select(".add20Btn").fadeOut();
						} else if(i >= 20 && !Ext.get(hd.nextSibling).isVisible()) {
							Ext.select(".add20Btn").fadeIn();
						} else {
							Ext.select(".add20Btn").fadeOut();
						}
					}
					this.toggleGroup(hd.parentNode);
				}
			},
			doGroupStart : function(buf, g, cs, ds, colCount){
				var tpl = '';
				if (g.text == "(Пусто)") {
					if (false/*g.rs.length > 20*/) {
						// есть выбор
						var styleOnly20On = '';
						var styleOnly20Off = '';
						if (form.GridPanel.only20) {
							styleOnly20On = 'color: #000';
						} else {
							styleOnly20Off = 'color: #000';
						}
						tpl = new Ext.XTemplate(
							'<div id="{groupId}" class="x-grid-queue-group x-grid-group {cls}">',
							'<div id="{groupId}-hd" class="x-grid-group-hd" style="{style}"><div>', 'Очередь (<a class="showOnly20on" style="'+styleOnly20On+'" onClick="getWnd(\'swWorkPlaceFuncDiagWindow\').showOnly20(true);">20 заявок</a>/<a class="showOnly20off" style="'+styleOnly20Off+'" onClick="getWnd(\'swWorkPlaceFuncDiagWindow\').showOnly20(false);">Всего {[values.rs.length]})</a>', '</div></div>',
							'<div id="{groupId}-bd" class="x-grid-group-body">'
						);
					} else {
						// нет выбора
						tpl = new Ext.XTemplate(
							'<div id="{groupId}" class="x-grid-queue-group x-grid-group {cls}">',
							'<div id="{groupId}-hd" class="x-grid-group-hd queuegrp" style="{style}"><div>', 'Очередь ({[values.rs[0].data.total]} {[parseInt(values.rs[0].data.total.toString().charAt(values.rs[0].data.total.toString().length-1)).inlist([1]) ?"заявка" :(parseInt(values.rs[0].data.total.toString().charAt(values.rs[0].data.total.toString().length-1)).inlist([2,3,4]) ? "заявки" : "заявок")]})', '</div></div>',
							'<div id="{groupId}-bd" class="x-grid-group-body">'
						);
					}
				} else {
					tpl = new Ext.XTemplate(
						'<div id="{groupId}" class="x-grid-group {cls}">',
						'<div id="{groupId}-hd" class="x-grid-group-hd" style="{style}"><div>', '{values.text} ({[values.rs.filter(function(rec){ if (rec.data.EvnDirection_id) return true; }).length]}/{[values.rs.length]} {[parseInt(values.rs.length.toString().charAt(values.rs.length.toString().length-1)).inlist([1]) ?"заявка" :(parseInt(values.rs.length.toString().charAt(values.rs.length.toString().length-1)).inlist([2,3,4]) ? "заявки" : "заявок")]})', '</div></div>',
						'<div id="{groupId}-bd" class="x-grid-group-body">'
					);
				}

				buf[buf.length] = tpl.apply(g);
			},
			doGroupEnd : function(buf, g, cs, ds, colCount){
				var tpl = '';
				if (g.text == "(Пусто)") {
					tpl = new Ext.XTemplate(
						'</div><a href="#" class="add20Btn" style="display:block;width:100%;text-align:center;height:20px;" onClick="Ext.getCmp(\'swWorkPlaceFuncDiagWindow\').add20ToQueue();">Показать еще 20 заявок</a>',
						'</div>'
					);
				} else {
					tpl = new Ext.XTemplate(
						'</div></div>'
					);
				}
				
				buf[buf.length] = tpl.apply(g);
			},
			groupingView: {showGroupName: false, showGroupsText: true},
			actions:
			[
				{name:'action_add', hidden: true, disabled: true },
				{name:'action_edit', handler: function() { this.openEvnFuncRequestEditWindow('edit', false);}.createDelegate(this) },
				{name:'action_view', handler: function() { this.openEvnFuncRequestEditWindow('view', false);}.createDelegate(this) },
				{
					name:'action_delete',
					text: 'Отклонить',
					// disabled: true,
					handler: function (){
						var selected = form.GridPanel.getGrid().getSelectionModel().getSelected();
						if (!Ext.isEmpty(selected.data.EvnDirection_id) && selected.data.EvnDirection_id > 0) {
							// отмена направления
							getWnd('swSelectDirFailTypeWindow').show({formType: 'funcdiag', LpuUnitType_SysNick: 'parka', onSelectValue: function(responseData) {
								if (!Ext.isEmpty(responseData.DirFailType_id)) {
									var loadMask = new Ext.LoadMask(form.GridPanel.getEl(), {msg: "Отмена направления на функциональную диагностику..."});
									loadMask.show();
									Ext.Ajax.request({
										params: {
											EvnDirection_id: selected.data.EvnDirection_id,
											DirFailType_id: responseData.DirFailType_id,
											EvnComment_Comment: responseData.EvnComment_Comment
										},
										url: '/?c=EvnFuncRequest&m=cancelDirection',
										callback: function(options, success, response) {
											loadMask.hide();
											if(success) {
												form.GridPanel.loadData();
											}
										}
									});
								}
							}});
						} else if (!Ext.isEmpty(selected.data.EvnFuncRequest_id) && selected.data.EvnFuncRequest_id > 0) {
							Ext.Msg.show({
								title: 'Удаление заявки',
								msg: 'Вы действительно хотите удалить заявку?',
								buttons: Ext.Msg.YESNO,
								fn: function(btn) {
									if (btn === 'yes') {
										var loadMask = new Ext.LoadMask(form.GridPanel.getEl(), {msg: "Удаление заявки на функциональную диагностику..."});
										loadMask.show();
										Ext.Ajax.request({
											params: {
												EvnFuncRequest_id: selected.data.EvnFuncRequest_id
											},
											url: '/?c=EvnFuncRequest&m=delete',
											callback: function(options, success, response) {
												loadMask.hide();
												if(success) {
													form.GridPanel.loadData();
												}
											}
										});
									}
								},
								icon: Ext.MessageBox.QUESTION
							});
						}
					}
				},
				{name: 'action_refresh'},
                { name: 'action_print',
                    menuConfig: {
						printCost: {name: 'printCost', text: 'Справка о стоимости лечения', hidden: true /*пока скрыли для всех*/, handler: function () { form.printCost() }},
						printPatientList: {name: 'printPatientList', text: 'Печать списка пациентов', handler: function() { form.printPatientList() }}
                    }
                }
			],
			autoLoadData: false,
			groupSortInfo: {
				field: 'Resource_Name',
				direction:'DESC'
			},
			pageSize: 20,
			stringfields:
			[
				// Поля для отображение в гриде
				{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
				{name: 'parentEvnClass_SysNick', type: 'string', hidden: true},
				{name: 'EvnQueue_id', type: 'int', hidden:true},
				{name: 'EvnFuncRequest_id', type: 'int', hidden:true},
				{name: 'EvnUslugaPar_id', type: 'int', hidden:true},
				{name: 'Resource_id', type: 'int', hidden:true},
				{name: 'TimetableResource_id', type: 'int', hidden:true},
				{name: 'PersonQuarantine_IsOn', type: 'int', hidden:true},
				{name: 'Resource_Name', type: 'string', hidden: true, group: true, sort: true, direction: [
					{field: 'Resource_Name', direction:'DESC'},
					{field: 'TimetableResource_begDate', direction:'DESC'},
					{field: 'TimetableResource_begTime', direction:'ASC'}
				]},
				{name: 'TimetableResource_begDate', type: 'date', hidden: true},
				{name: 'TimetableResource_Date', type: 'date', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Lpu_Name', type: 'string', hidden: true}, // ЛПУ откуда направлен
				{name: 'LpuSection_Name', type: 'string', hidden: true}, // Отделение откуда направлен

				{
					name: 'Person_Firname',
					hidden: true,
					type: 'string',
				},

				{
					name: 'Person_Secname',
					hidden: true,
					type: 'string',
				},

				{
					name: 'Person_Surname',
					hidden: true,
					type: 'string',
				},

				{name: 'EvnDirection_IsCito', header: 'Cito!', type: 'checkbox', width: 40},
				{name: 'FuncRequestState', header: 'Приём', type: 'checkbox', width: 60 },
				{name: 'EvnDirection_setDT', dateFormat: 'd.m.Y', type: 'date', header: 'Дата направления', width: 120},
				{name: 'TimetableResource_begTime', type: 'string', header: 'Запись', width: 120/*, sort: true, direction: 'ASC'*/},
				{name: 'EvnUslugaPar_setDate', dateFormat: 'd.m.Y', type: 'date', header: 'Дата исследования', width: 120, hidden: (getGlobalOptions().region.nick != 'kz')},
				{name: 'EvnDirection_Num', header: 'Направление', type: 'string', width: 80, renderer: renderEvnDirection_Num},
				{ name: 'EvnDirection_Name', header: 'Кем направлен', width: 160, renderer: function(value, cellEl, rec){
					if (rec.get('EvnDirection_id')) {
						return '' + rec.get('Lpu_Name') +', '+ rec.get('LpuSection_Name') +'';
					}
					return '';}},
				{name: 'Person_FIO', header: 'ФИО пациента', type: 'string', width: 250},
				{name: 'Person_BirthDay', header: 'Дата рождения', type: 'date', width: 100},
                {name: 'Person_Phone', type: 'string', header: 'Телефон', width: 100},
				{name: 'Person_IsBDZ', header: 'БДЗ', type: 'checkcolumn', width: 40, hidden: (getGlobalOptions().region.nick != 'kz')},
				{name: 'sendToRCC_link', header: 'Отправить в ЦУК', type: 'string', width: 130, renderer: renderSendToRCC_link},
				{name: 'EvnFuncRequest_UslugaCache', header: 'Список услуг', renderer: function(value, cellEl, rec) {
					var result = '';
					if (!Ext.isEmpty(value)) {
						// разджейсониваем
						var uslugas = Ext.util.JSON.decode(value);
						for(var k in uslugas) {
							if (uslugas[k].UslugaComplex_Name) {
								if (!Ext.isEmpty(result)) {
									result += '<br />';
								}
								result += '<a href="javascript://" onClick="Ext.getCmp(\'swWorkPlaceFuncDiagWindow\').openUslugaWindow({' +
								'Person_id: ' + rec.get('Person_id') + ',' +
								'EvnUslugaPar_id: ' + uslugas[k].EvnUslugaPar_id + ',' +
								'EvnDirection_id: ' + (rec.get('EvnDirection_id')?rec.get('EvnDirection_id'):'null') + ',' +
								'EvnFuncRequest_id: ' + (rec.get('EvnFuncRequest_id')?rec.get('EvnFuncRequest_id'):'null') + ',' +
								'Resource_id: ' + (rec.get('Resource_id')?rec.get('Resource_id'):'null') + ',' +
								//'parentEvnClass_SysNick: ' + (rec.get('parentEvnClass_SysNick')? rec.get('parentEvnClass_SysNick') : 'null') + ',' +
								'MedService_id: ' + form.MedService_id +
								'});">' + uslugas[k].UslugaComplex_Name + '</a>';

								if (!Ext.isEmpty(uslugas[k].EvnUslugaPar_setDate)) {
									result += ' <a title="Отменить выполнение услуги" href="javascript://" onClick="Ext.getCmp(\'swWorkPlaceFuncDiagWindow\').cancelEvnUslugaPar({' +
									'EvnUslugaPar_id: ' + uslugas[k].EvnUslugaPar_id +
									'});"><img width="14" src="/img/icons/cancel_blue16.png" /></a>';

									if (!Ext.isEmpty(uslugas[k].EvnXml_IsSigned)) {
										if (uslugas[k].EvnXml_IsSigned == 2) {
											result += '<img src="/img/icons/emd/doc_signed.png">';
										} else {
											result += '<img src="/img/icons/emd/doc_notactual.png">';
										}
									} else {
										result += '<img src="/img/icons/emd/doc_notsigned.png">';
									}
								}
							}
						}
					}
					return result;
                }, width: 420, id: 'autoexpand'},
				{name: 'EvnCostPrint_PrintStatus', header: 'Справка о стоимости лечения',type: 'checkbox', width: 120, hidden: true /*функционал скрыт полностью*/ },
				{name: 'Operator', header: 'Оператор', type: 'string', width: 140},
				{name: 'WorkListStatus_Name', header: 'Статус в рабочем списке', type: 'string', width: 140},
				{name: 'RCC_MedService_id', type: 'int', hidden: true},
				{name: 'RemoteConsultCenterResearch_id', type: 'int', hidden: true},
				{name: 'RemoteConsultCenterResearch_status', type: 'int', hidden: true},
				{name: 'pmUser_insID', type: 'int', hidden: true},
				{name: 'total', type: 'int', hidden: true}
			],
			dataUrl: '/?c=EvnFuncRequest&m=loadEvnFuncRequestList',
			totalProperty: 'totalCount',
			title: 'Список заявок',
			onRowSelect: function(sm, index, record) {
				var current_date = Date.parseDate(form.curDate, 'd.m.Y');
				// Записать пациента для свободной бирки.
				this.getAction('action_dir').setDisabled(!record || Ext.isEmpty(record.get('TimetableResource_id')) || !Ext.isEmpty(record.get('EvnDirection_id')));

				// Записать из очереди для записей в очереди
				this.getAction('action_dirqueue').setDisabled(!record || Ext.isEmpty(record.get('EvnQueue_id')) || !Ext.isEmpty(record.get('TimetableResource_id')));

				// Отклонить для заявок, по которым нет выполненных услуг.
				var disableDel = false;
				if (!Ext.isEmpty(record.get('EvnFuncRequest_UslugaCache'))) {
					var uslugas = Ext.util.JSON.decode(record.get('EvnFuncRequest_UslugaCache'));
					for(var k in uslugas) {
						if (!Ext.isEmpty(uslugas[k].EvnUslugaPar_setDate)) {
							// нельзя отклонить если есть выполненная услуга
							disableDel = true;
						}
					}
				}
				this.getAction('action_delete').setDisabled(!record || (record.get('TimetableResource_begTime') != 'б/з' && record.get('TimetableResource_begTime') != 'б/н' && current_date > record.get('TimetableResource_begDate')) || Ext.isEmpty(record.get('EvnDirection_id')) || disableDel || !!record.get('RemoteConsultCenterResearch_id'));

				// Убрать в очередь для заявки на бирке, по которой нет выполненных услуг
				this.getAction('action_toqueue').setDisabled(!record || (record.get('TimetableResource_begTime') != 'б/з' && record.get('TimetableResource_begTime') != 'б/н' && current_date > record.get('TimetableResource_begDate')) || Ext.isEmpty(record.get('EvnDirection_id')) || Ext.isEmpty(record.get('TimetableResource_id')) || disableDel || !!record.get('RemoteConsultCenterResearch_id'));

				this.getAction('action_view').setDisabled(!record || Ext.isEmpty(record.get('EvnDirection_id')));
				this.getAction('action_edit').setDisabled(!record || Ext.isEmpty(record.get('EvnDirection_id')) || !!record.get('RemoteConsultCenterResearch_id'));

				this.getAction('action_ecg').setDisabled(!record || Ext.isEmpty(record.get('EvnUslugaPar_id')));
				//form.onRowSelectElectronicQueue();
			},
			onLoadData: function(sm, index, record)
			{	
				var grid = this.GridPanel.getGrid(),
					store = grid.getStore();
				if (!store.totalLength) {
					store.removeAll();
				}
				var i = 0;
				store.each(function(rec){
					if(Ext.isEmpty(rec.get('Resource_Name')))
						i++;
				});
				if(i>this.numQueue)
					this.numQueue = i;
				if( Ext.get(grid.getView().getGroupId('(Пусто)')) != null && this.afterShowFlag == true) {
					grid.getView().toggleGroup(grid.getView().getGroupId('(Пусто)'), false);
					Ext.select(".add20Btn").fadeOut();
					this.afterShowFlag = false;
				} else {
					if(i<=20){
						if (Ext.get(grid.getView().getGroupId('(Пусто)')) != null) {
							grid.getView().toggleGroup(grid.getView().getGroupId('(Пусто)'), false);
						}
						Ext.select(".add20Btn").fadeOut();
					}
				}

				var a = 0;
				store.each(function(rec,idx,count){
					if(Ext.isEmpty(rec.get('Resource_Name')))
						a++;
				}.createDelegate(this));

				if(a%20 > 0){
					Ext.select(".add20Btn").fadeOut();
				}

				//form.showOnly20(true);
			}.createDelegate(this),
			onDblClick: function(grid, number, object){
				this.onEnter();
			},
			onEnter: function() {

				var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
				if (!record) return false;

				this.openEvnFuncRequestEditWindow((!record.get('EvnFuncRequest_id'))?'add':'edit', true); // либо редактирвоание, либо запись на бирку.
			}.createDelegate(this)
		});
		
		this.ElectronicQueuePanel = new sw.Promed.ElectronicQueuePanel({
			ownerWindow: form,
			ownerGrid: form.GridPanel.getGrid(), // передаем грид для работы с ЭО
			gridPanel: form.GridPanel, // свяжем так же грид панель
			applyCallActionFn: function(){ form.openUslugaWindow() }, // передаем то что будет отрываться при на жатии на принять
			region: 'south',
			refreshTimer: 30000,
			checkRedirection: true // опция означает, провереяем ли мы при завершении талона что талон был перенаправлен
		});

		sw.Promed.swWorkPlaceFuncDiagWindow.superclass.initComponent.apply(this, arguments);
        this.GridPanel.ViewToolbar.on('render', function(vt){
            this.ViewActions['action_create'] = new Ext.Action({
                name:'action_create',
                handler: function() {
                    form.acceptWithoutRecord();
                },
                text:'Принять без записи',
                tooltip: 'Пациент без записи',
                iconCls : 'copy16'
            });
            vt.insertButton(1,this.ViewActions['action_create']);

			this.ViewActions['action_dir'] = new Ext.Action({
				name:'action_dir',
				handler: function() {
					form.recordPerson();
				},
				text:'Записать пациента',
				tooltip: 'Записать пациента',
				iconCls : 'add16'
			});
			vt.insertButton(2,this.ViewActions['action_dir']);

			this.ViewActions['action_dirqueue'] = new Ext.Action({
				name:'action_dirqueue',
				handler: function() {
					form.recordPersonFromQueue();
				},
				text:'Записать из очереди',
				tooltip: 'Записать из очереди',
				iconCls : 'add16'
			});
			vt.insertButton(3,this.ViewActions['action_dirqueue']);

			this.ViewActions['action_toqueue'] = new Ext.Action({
				name:'action_toqueue',
				handler: function() {
					form.returnToQueue();
				},
				text:'Убрать в очередь',
				tooltip: 'Убрать в очередь',
				iconCls : 'delete16'
			});
			vt.insertButton(4,this.ViewActions['action_toqueue']);

			this.ViewActions['action_extdir'] = new Ext.Action({
				name:'action_extdir',
				handler: function() {
					var win = getWnd('swWorkPlaceFuncDiagWindow');
					var swPersonSearchWindow = getWnd('swPersonSearchWindow');
					if ( swPersonSearchWindow.isVisible() ) {
						sw.swMsg.alert('Окно поиска человека уже открыто', 'Для продолжения необходимо закрыть окно поиска человека.');
						return false;
					}
		
					var params = {
						MedService_id: win.MedService_id,
						action: 'add',
						callback: function(data) {},
						swWorkPlaceFuncDiagWindow: win,
						onSelect: function(pdata)
						{
							getWnd('swPersonSearchWindow').hide();
							var personData = new Object();
							
							personData.Person_id = pdata.Person_id;
							personData.Person_IsDead = pdata.Person_IsDead;
							personData.Person_Firname = pdata.PersonFirName_FirName;
							personData.Person_Surname = pdata.PersonSurName_Surname;
							personData.Person_Secname = pdata.PersonSecName_Secname;
							personData.PersonEvn_id = pdata.PersonEvn_id;
							personData.Server_id = pdata.Server_id;
							personData.Person_Birthday = pdata.Person_Birthday;
						
							getWnd('swDirectionMasterWindow').show({
								type: 'ExtDirDiag',
								dirTypeData: {DirType_id: 10, DirType_Code: 9, DirType_Name: 'На исследование'}, 
								date: null,
								personData: personData,
								onClose: function() {
									this.buttons[0].show();
									this.buttons[1].show();
								},
								onDirection: function (dataEvnDirection_id) {
									var EvnDirId = false;
									if(dataEvnDirection_id.EvnDirection_id) {
										EvnDirId = dataEvnDirection_id.EvnDirection_id;
									} else {
										if(dataEvnDirection_id.evnDirectionData && dataEvnDirection_id.evnDirectionData.EvnDirection_id){
											EvnDirId = dataEvnDirection_id.evnDirectionData.EvnDirection_id;
										}
									}
									if(!EvnDirId) {
										sw.swMsg.alert(langs('Сообщение'), langs('Мастер выписки направлений не вернул идентификатор направления.'));
										return false;
									}
									
									var params2 = {
										EvnDirection_id: EvnDirId,
										EvnFuncRequest_id: null,
										MedService_id: win.MedService_id,
										action: 'edit',
										callback: function(data) {},
										swWorkPlaceFuncDiagWindow: win,
										Person_id: pdata.Person_id,
										PersonEvn_id: pdata.PersonEvn_id,
										Server_id: pdata.Server_id,
										swPersonSearchWindow: swPersonSearchWindow
									}
									Ext.param2 = params2;
									getWnd('swEvnFuncRequestEditWindow').show(params2);
								}
							});
						},//.createDelegate(this),
						searchMode: 'all'
					};
					getWnd('swPersonSearchWindow').show(params);
				},
				text:'Внешнее направление',
				tooltip: 'Внешнее направление',
				iconCls : 'add16'
			});
			this.ExtDirButton = vt.insertButton(5,this.ViewActions['action_extdir']);

			this.ViewActions['action_ecg'] = new Ext.Action({
				name:'action_ecg',
				handler: function() {
					form.sendEcg();
				},
				text:'Отправить на ЭКГ',
				tooltip: 'Отправить на ЭКГ',
				iconCls : 'actions16',
				hidden: true
			});

			vt.insertButton(6,this.ViewActions['action_ecg']);

			this.ViewActions['action_wl'] = new Ext.Action({
				name:'action_wl',
				id: 'action_wl',
				handler: function() {
					form.sendWLQ();
				},
				text:'Отправить в РС',
				tooltip: 'Отправить в РС',
				iconCls : 'actions16'
			});

			vt.insertButton(7,this.ViewActions['action_wl']);

            return true;
        }, this.GridPanel);
		this.GridPanel.getGrid().getView().getRowClass = function(record, index) {
			var cls = '';
			if (record.data['RemoteConsultCenterResearch_id']) {
				cls += 'grid-locked-row';
			}
			if (record.get('PersonQuarantine_IsOn') == 2) {
				cls += 'x-grid-rowbackred ';
			}
			return cls;
		}

	},

});

