/**
 * Панель нетрудоспособности
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
Ext6.define('common.EMK.EvnStickPanel', {
	requires: [
		'sw.frames.EMD.swEMDPanel'
	],
	extend: 'swPanel',
	title: 'НЕТРУДОСПОСОБНОСТЬ',
	layout: 'border',
	userCls: 'evn-stick-panel mini-scroll',
	setParams: function(params) {
		var me = this;

		me.Evn_id = params.Evn_id;
		me.Person_id = params.Person_id;
		me.PersonEvn_id = params.PersonEvn_id;
		me.Server_id = params.Server_id;
		me.loaded = false;

		if (!me.ownerCt.collapsed && me.isVisible()) {
			me.load();
		}
	},
	setTitleCounter: function(count) {
		var me = this;
		me.callParent(arguments);
		me.up('window').query('evnxmleditor').forEach(function(editor) {
			editor.setParams({EvnStickCount: count});
			editor.refreshSpecMarkerBlocksContent();
		});
	},
	loaded: false,
	load: function() {
		var me = this;
		me.loaded = true;
		
		me.EvnStickGrid.params.EvnStick_pid = me.Evn_id;
		me.EvnStickGrid.params.Person_id = me.Person_id;
		me.EvnStickGrid.getStore().load({
			params: me.EvnStickGrid.params
		});
		me.EvnStickChangeStore.load({
			params: {
				Person_id : me.EvnStickGrid.params.Person_id,
				EvnStick_mid : me.EvnStickGrid.params.EvnStick_pid,
			}
		});
		me.ownerWin.loadTree();
		me.checkEnable();
	},
	syncStores: function() {
		var me = this;
		if(me.EvnStickChangeStore.getCount()>0)
			me.EvnStickGrid.store.each(function(rec) {
				var id = me.EvnStickChangeStore.findBy(function(record) {
					return record.get('EvnStick_id')==rec.get('EvnStick_id');
				});
				rec.set('mark', id>-1);
			});
	},
	checkEnable: function() {
		var me = this;
		me.toolPanel.setDisabled(!me.editAvailable);
		me.toolPanel.queryById('menuAddLvn').setVisible(!Ext.isEmpty(me.Evn_id));
	},
	getEvnStickSetdate: function(params, callback) {
		var me = this;
		me.mask('Получение даты для ЛВН...');
		Ext6.Ajax.request({
			failure: function(response, options) {
				me.unmask();
				Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить дату для ЛВН!'));
			},
			params: params,
			success: function(response, options) {
				me.unmask();
				var response_obj = Ext6.JSON.decode(response.responseText);

				if ( !response_obj[0] || !response_obj[0].EvnStick_setDate ) {
					Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить дату последнего посещения для ЛВН!'));
				}
				else {
					callback(response_obj);
				}
			}.createDelegate(this),
			url: '/?c=Stick&m=getEvnStickSetdate'
		});
	},
	openEvnStickEditWindow: function(action, record) { //открыть форму ЛВН
		if (!action.inlist(['add', 'edit', 'view'])) {
			return false;
		}
		var me = this;
		var formParams = {
			Person_id: me.Person_id,
			PersonEvn_id: me.PersonEvn_id,
			Server_id: me.Server_id
		};
		var my_params = {
			Person_id: me.Person_id,
			PersonEvn_id: me.PersonEvn_id,
			Server_id: me.Server_id
		};
		my_params.parentClass = 'EvnPL';
		my_params.onHide = Ext6.emptyFn;
		my_params.UserMedStaffFact_id = getGlobalOptions().CurMedStaffFact_id;
		my_params.UserLpuSection_id = getGlobalOptions().CurLpuSection_id;

		var onSaveEvnStick = function(data) {
			if (!data || !data.evnStickData) {
				return false;
			}
			me.load();
		};

		// данные о человеке можно достать из PersonInfoPanel из ЭМК
		var piPanel = me.ownerWin.PersonInfoPanel;
		if (piPanel && piPanel.getFieldValue('Person_Surname')) {
			my_params.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
			my_params.Person_Surname = piPanel.getFieldValue('Person_Surname');
			my_params.Person_Firname = piPanel.getFieldValue('Person_Firname');
			my_params.Person_Secname = piPanel.getFieldValue('Person_Secname');
		} else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
			return false;
		}

		if (action == 'add') {
			formParams.EvnStick_id = 0;
			formParams.EvnStick_mid = me.Evn_id;
			formParams.EvnStick_pid = me.Evn_id;

			this.getEvnStickSetdate({
					EvnStick_mid: me.Evn_id
				},
				function(data) {
					formParams.EvnStick_begDate = Date.parseDate(data[0].EvnStick_setDate, 'd.m.Y');
					formParams.EvnStick_setDate = Date.parseDate(data[0].EvnStick_setDate, 'd.m.Y');
					my_params.formParams = formParams;
					my_params.callback = onSaveEvnStick;

					//~ getWnd('swEvnStickChangeWindow').show(my_params);

					if(me.EvnStickGrid.itemMenu==1) {
						my_params.action = 'add';
						my_params.evnStickType = 1;
						my_params.formParams.EvnStick_id = 0;

						getWnd('swEvnStickEditWindow'+(me.ext6 ? 'Ext6':'')).show(my_params);
					} else if(me.EvnStickGrid.itemMenu==2) {
						my_params.action = 'add';
						my_params.formParams.EvnStickStudent_id = 0;
						getWnd('swEvnStickStudentEditWindow').show(my_params);
					} else if(me.EvnStickGrid.itemMenu==3) { //лвн из фсс
						me.openStickFSSDataEditWindow(my_params);
					} else if(me.EvnStickGrid.itemMenu==5) {//существующий лвн
						if ( !record || !record.get('EvnStick_id') ) {
							return false;
						}

						my_params.action = 'edit';
						my_params.evnStickType = record.get('evnStickType');
						my_params.formParams.EvnStick_id = record.get('EvnStick_id');
						my_params.formParams.EvnStick_pid = record.get('EvnStick_pid');

						if (formParams.EvnStick_mid != formParams.EvnStick_pid && record.get('EvnStickLinked') == 0) {
							my_params.link = true;
						}
						
						var indx = me.EvnStickChangeStore.findBy(function(rec) {
							return rec.get('EvnStick_id')==record.get('EvnStick_id');
						});
						var record2 = indx>-1 ? me.EvnStickChangeStore.getAt(indx) : null;
						my_params.parentNum = !Ext6.isEmpty(record2) ? record2.get('EvnStick_ParentNum') : '';
						
						getWnd('swEvnStickEditWindow').show(my_params);
						
					} else if(me.EvnStickGrid.itemMenu==7) {//Создать дубликат

						if ( !record || !record.get('EvnStick_id') ) {
							return false;
						}
						
						my_params.action = 'add';
						my_params.formParams.EvnStick_id = 0;
						my_params.formParams.EvnStickCopy_id = record.get('EvnStick_id');
						getWnd('swEvnStickEditWindow'+(me.ext6 ? 'Ext6':'')).show(my_params);
					}
				});
		}
		else {
			formParams.Person_id = record.get('Person_id');
			formParams.Server_id = record.get('Server_id');
			formParams.PersonEvn_id = record.get('PersonEvn_id');
			formParams.EvnStick_mid = Ext.isEmpty(me.Evn_id) ? record.get('EvnStick_mid') : me.Evn_id;
			formParams.EvnStick_pid = record.get('EvnStick_pid');
			formParams.EvnStick_id = record.get('EvnStick_id');
			my_params.evnStickType = record.get('evnStickType');
			my_params.action = action;
			my_params.callback = onSaveEvnStick;
			my_params.delAccessType = record.get('delAccessType');
			//~ if (record.get('accessType') != 'edit') {
			//~ my_params.action = 'view';
			//~ }

			if (formParams.EvnStick_mid != formParams.EvnStick_pid && record.get('EvnStickLinked') == 0) {
				my_params.link = true;
			}

			my_params.formParams = formParams;
			switch (record.get('evnStickType')) {
				case 1:
				case 2:
					getWnd('swEvnStickEditWindow'+(me.ext6 ? 'Ext6':'')).show(my_params);
					break;
				case 3:
					getWnd('swEvnStickStudentEditWindow').show(my_params);
					break;
			}
		}
	},
	openStickFSSDataEditWindow: function(my_params) { //получить лвн из фсс
		var me = this;
		if (my_params.action == 'addRequest') {
			var rec = my_params.rec;
			var params = {
				action: 'add',
				ignoreCheckExist: true,
				Person_id: rec.get('Person_id'),
				StickFSSData_StickNum: rec.get('EvnStick_Num'),
				callback: function() {
					me.load();
				}
			}
		} else {
			var params = Object.assign({}, my_params);
			params.action = 'add';
			var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			if (params.formParams && typeof params.formParams.EvnStick_begDate == 'object') {
				date = params.formParams.EvnStick_begDate;
			}
			var person_age = sw4.GetPersonAge(params.Person_Birthday, date);
			if (person_age < 18) {
				params.Person_id = null;
			}

			params.callback = function(data) {
				this.hide();
				if (data.warnExist) {
					return false;
				}
				params.action = 'add';
				params.evnStickType = 1;
				params.formParams.StickFSSData_id = data.StickFSSData_id;
				params.formParams.EvnStick_id = 0;
				params.EvnStick_Num = data.EvnStick_Num;
				if (data.Person_id != params.Person_id) {
					params.Person_Surname = data.Person_Surname;
					params.Person_Firname = data.Person_Firname;
					params.Person_Secname = data.Person_Secname;
					params.Person_id = data.Person_id;
					params.PersonEvn_id = data.PersonEvn_id;
					params.Server_id = data.Server_id;
					params.formParams.Person_id = data.Person_id;
					params.formParams.PersonEvn_id = data.PersonEvn_id;
					params.formParams.Server_id = data.Server_id;
				}

				getWnd('swEvnStickEditWindow'+(me.ext6 ? 'Ext6':'')).show(params);
			}.createDelegate(this);
		}

		getWnd('swStickFSSDataEditWindow'+(me.ext6 ? 'Ext6':'')).show(params);
	},
	deleteEvnStick: function(evnstick_node) {
		var form = this;
		
		if ( !evnstick_node || !evnstick_node.get('EvnStick_pid') || (evnstick_node.get('delAccessType') == 'view' && evnstick_node.get('cancelAccessType') == 'view') )
		{
			return false;
		}

		var evnstick_id = evnstick_node.get('EvnStick_id'),
			evnstick_mid = evnstick_node.get('EvnStick_mid'),
			error, question, url,
			params = new Object();
		if ( evnstick_node.get('evnStickType') == 3 )
		{
			if(evnstick_mid == evnstick_node.get('EvnStick_pid')) {
				error = langs('При удалении справки учащегося возникли ошибки');
				question = langs('Удалить справку учащегося?');
			} else {
				error = langs('При удалении связи справки учащегося с текущим документом возникли ошибки');
				question = langs('Удалить связь справки учащегося с текущим документом?');
			}
			url = '/?c=Stick&m=deleteEvnStickStudent';
			params['EvnStickStudent_id'] = evnstick_id;
			params['EvnStickStudent_mid'] = evnstick_mid;
		}
		else
		{
			if (evnstick_node.get('EvnStick_closed') == 1) {
				error = langs('При удалении ЛВН возникли ошибки');
				question = langs('Вы уверены, что хотите удалить закрытый ЛВН?');
			} else {
				error = langs('При удалении ЛВН возникли ошибки');
				question = langs('Удалить ЛВН?');
			}
			url = '/?c=Stick&m=deleteEvnStick';
			params['EvnStick_id'] = evnstick_id;
			params['EvnStick_mid'] = evnstick_mid;
		}

		if ( evnstick_node.get('cancelAccessType') == 'edit' ) {
			params['deleteType'] = 'cancel';
			form.doDeleteEvnStick({
				error: error,
				params: params,
				evnstick_node: evnstick_node,
				evnstick_pid: evnstick_mid,
				evnstick_id: evnstick_id,
				url: url
			});
		} else {
			Ext6.Msg.show({
				buttons: Ext6.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						form.doDeleteEvnStick({
							error: error,
							params: params,
							evnstick_node: evnstick_node,
							evnstick_pid: evnstick_mid,
							evnstick_id: evnstick_id,
							url: url
						});
					}
				}.createDelegate(this),
				icon: Ext6.MessageBox.QUESTION,
				msg: question,
				title: langs('Вопрос')
			});
		}

	},
	doDeleteEvnStick: function(options) {
		var form = this;
		var maskMsg = "Удаление записи...";
		if(options.params.deleteType == 'cancel') {
			maskMsg = "Аннулирование записи...";
		}
		var loadMask = new Ext6.LoadMask(form, {msg: maskMsg});
		loadMask.show();

		var alert = sw.Promed.EvnStick.getDeleteAlertCodes({
			callback: function(options) {
				form.doDeleteEvnStick(options);
			},
			options: options,
			Ext: Ext6
		});

		Ext6.Ajax.request({
			failure: function(response, options) {
				loadMask.hide();
				Ext6.Msg.alert(langs('Ошибка'), options.error);
			},
			params: options.params,
			success: function(response, opts) {
				loadMask.hide();

				var response_obj = Ext6.util.JSON.decode(response.responseText);

				if ( response_obj.success == false ) {
					if (response_obj.Alert_Msg) {
						if (response_obj.Alert_Code == 705) {
							getWnd('swStickCauseDelSelectWindow').show({
								countNotPaid: response_obj.countNotPaid,
								existDuplicate: response_obj.existDuplicate,
								callback: function(StickCauseDel_id) {
									if (StickCauseDel_id) {
										options.params.StickCauseDel_id = StickCauseDel_id;
										form.doDeleteEvnStick(options);
									}
								}.createDelegate(this)
							});
						} else {
							var a_params = alert[response_obj.Alert_Code];
							Ext6.Msg.show({
								buttons: a_params.buttons,
								fn: function(buttonId) {
									a_params.fn(buttonId, form);
								}.createDelegate(this),
								msg: response_obj.Alert_Msg,
								icon: Ext6.MessageBox.QUESTION,
								title: 'Вопрос'
							});
						}
					} else {
						Ext6.Msg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : options.error);
					}
				}
				else {
					if (response_obj.IsDelQueue) {
						Ext6.Msg.alert('Внимание', 'ЛВН добавлен в очередь на удаление');
					}
					form.EvnStickGrid.getStore().reload();
				}
			},
			url: options.url
		});
	},
	undoDeleteEvnStick: function(evnstick_node) {
		var form = this;

		if ( !evnstick_node )
		{
			return false;
		}

		var evnstick_id = evnstick_node.get('EvnStick_id'),
			evnstick_pid = evnstick_node.get('Evn_pid'),
			error = langs(''), 
			question, url,
			params = new Object();

		form.mask("Отмена удаления ЛВН...");

		Ext6.Ajax.request({
			failure: function(response, options) {
				form.unmask();
				sw.swMsg.alert(langs('Ошибка'), langs('Произошла ошибка'));
			},
			params: {
				EvnStick_id: evnstick_id
			},
			success: function(response, options) {
				form.unmask();

				var response_obj = Ext6.util.JSON.decode(response.responseText);

				if ( response_obj.success == false ) {
					sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : error);
				}
				else {
					form.EvnStickGrid.getStore().reload();
				}
			},
			url: '/?c=Stick&m=undoDeleteEvnStick'
		});
	},
	editEvnStickEditWindow: function() {
		var me = this,
			record = me.EvnStickGrid.getSelectionModel().getSelectedRecord();
		if (record) {
			var accessToEdit = (record.get('accessType') == 'edit');
			me.openEvnStickEditWindow(accessToEdit?'edit':'view', record);
		}
	},
	doCopy: function() {
		var me = this,
			record = me.EvnStickGrid.getSelectionModel().getSelectedRecord();
		if (record) {
			me.EvnStickGrid.itemMenu = 7;
			me.openEvnStickEditWindow('add', record);
		}
	},
	doDelete: function() {
		var me = this,
			record = me.EvnStickGrid.getSelectionModel().getSelectedRecord();
		if(record)
			me.deleteEvnStick(record);
	},
	undoDelete: function() {
		var me = this,
			record = me.EvnStickGrid.getSelectionModel().getSelectedRecord();
		if(record)
			me.undoDeleteEvnStick(record);
	},
// Печать ЛВН
	doPrintEvnStick: function() {
		var me = this,
			params = new Object(),
			record = me.EvnStickGrid.getSelectionModel().getSelectedRecord(),
			prId,   // Ид. предыдущего ЛВН
			curLtc; // Код исхода текущего ЛВН

 		if (record) {
// Ид. ЛВН:
			params.EvnStick_id = record.get('EvnStick_id');
// Код типа ЛВН (1 - основная работа, 2 - работа по совместительству):
			params.evnStickType = record.get('evnStickType');
// Ид. порядка выдачи ЛВН:
			params.StickOrder_id = record.get('StickOrder_id');
// Условное обозначение причины нетрудоспособности (new):
			params.StickCause_SysNick = record.get('StickCause_SysNick');
// Текущий номер ЭЛН в хранилище:
			params.RegistryESStorage_id = record.get('ISELN');
// Дата окончания первого периода нетрудоспособности:
			params.firstEndDate = null;
// Ид. исхода текущего ЛВН:
			params.StickLeaveType_id = record.get('StickLeaveType_id');
// Код исхода ЛВН:
			if ((curLtc = record.get('StickLeaveType_Code')) && (curLtc != '0'))
				params.PridStickLeaveType_Code = curLtc;
			else
				params.PridStickLeaveType_Code = '0';

			if (params.evnStickType == 3) {
				if (getRegionNick() == 'kz') {
					printBirt({
						'Report_FileName': 'f095u.rptdesign',
						'Report_Params': '&paramEvnStickStudent=' + params.EvnStick_id,
						'Report_Format': 'pdf'
					});
				} else {
					url = '/?c=Stick&m=printEvnStickStudent&EvnStickStudent_id=' + params.EvnStick_id;
					window.open(url, '_blank');
				}
			} else if (prId = record.get('EvnStick_prid')) {
				// Если есть предыдущий ЛВН, запрашиваем его из БД и печатаем с учетом указанного
				// в нем код исхода:
				me.mask('Получение предыдущего ЛВН...');

				Ext6.Ajax.request({
					url: '/?c=Stick&m=getEvnStickPridValues',

					params: {
						EvnStick_prid: prId
					},

					success: function(response, options) {
						var response_obj,
							prLtc;  // Код исхода предыдущего ЛВН

						me.unmask();

						response_obj = Ext6.JSON.decode(response.responseText);

						if ( !response_obj[0] || !response_obj[0].EvnStick_setDate ) {
							Ext6.Msg.alert(langs('Ошибка'),
										   langs('Не удалось получить предыдущий ЛВН.'));
						}
						else {
							if ((prLtc = response_obj[0].PridStickLeaveType_Code2) &&
								(prLtc != '0'))
								params.PridStickLeaveType_Code = prLtc;

							getWnd('swEvnStickPrintWindow').show(params);
						}
					},

					failure: function(response, options) {
						me.unmask();
						Ext6.Msg.alert(langs('Ошибка'),
									   langs('Не удалось получить предыдущий ЛВН.'));
					}
				});
			} else {
				// Если предыдущего ЛВН нет, печатаем с текущими параметрами:
				getWnd('swEvnStickPrintWindow').show(params);
			}
 		}
 	},
	checkToolPanelButtons: function() {
		var me = this;
		var selected = me.EvnStickGrid.getStore().getCount()>0 && !Ext6.isEmpty(me.EvnStickGrid.getSelectionModel().getSelectedRecord());
		me.queryById('toolPanelOpen').setDisabled(!selected);
		me.queryById('toolPanelCopy').setDisabled(!selected);
		me.queryById('toolPanelDelete').setDisabled(!selected);
		me.queryById('toolPanelPrint').setDisabled(!selected);
	},
	openEvnStickChange: function(rowIndex) {//добавить существующий ЛВН
		var me = this, 
			msg= '',
			record = this.EvnStickGrid.getStore().getAt(rowIndex);
		if(record && getGlobalOptions().lpu_nick != record.get('Lpu_Nick') ) {
			if (record.get('evnStickType') == 3) {
				msg ="Справка учащегося ";
			} else if (record.get('ISELN')) {
				msg ="ЭЛН ";
			} else {
				msg ="ЛВН ";
			}
			msg += record.get('EvnStick_Num');
			
			sw.swMsg.show({
				buttonText: { yes: langs('Перенести'), no: langs('Отмена') },
				msg: msg+' открыт в '+record.get('Lpu_Nick')+'.<br>Перенести документ в мою мед.организацию?',
				title: langs('Вопрос'),
				icon: Ext.MessageBox.QUESTION,
				fn: function(buttonId){
					if ( buttonId == 'yes' ) {
						me.EvnStickGrid.itemMenu = 5;
						me.openEvnStickEditWindow('add', record);
					}
				}.createDelegate(this)
			});
		}
	},
	initComponent: function() {
		var me = this;
		me.ext6 = 0;//использовать новую форму ЛВН (true) или старую (false)

		me.filterField = Ext6.create('Ext6.form.Text', {
			triggers: {
				search: {
					cls: 'x6-form-search-trigger',
					handler: function() {

					}
				}
			},
			listeners: {
				'change': function (combo, newValue, oldValue) {
					me.EvnStickGrid.getStore().clearFilter();
					var filters = [
						new Ext6.util.Filter({
							filterFn: function(rec) {
								var arrFilterFields = [
										'StickOrder_Name',
										'evnStickType',
										'EvnStick_setDate',
										'EvnStick_disDate',
										'CardType',
										'NumCard',
										'StickWorkType_Name'
									],
									BreakException = {},
									filter = false;
								// Как только найдем сходство по строке - сразу прекратим поиск по записи
								try {
									arrFilterFields.forEach(function (fname) {
										var val = '';
										switch (fname) {
											case 'StickOrder_Name':
												val = rec.get('StickOrder_Name').slice(0, 1).toUpperCase()
													+ rec.get('StickOrder_Name').slice(1);
												break;
											case 'evnStickType':
												if (rec.get('evnStickType') == 3) {
													val = "Справка учащегося " + rec.get('EvnStick_Num');
												} else if (rec.get('ISELN')) {
													val = "ЭЛН " + rec.get('EvnStick_Num');
												} else {
													val = "ЛВН " + rec.get('EvnStick_Num');
												}
												break;
											case 'EvnStick_setDate':
											case 'EvnStick_disDate':
												if (rec.get(fname))
													val = rec.get(fname).format('d.m.Y');
												break;
											default:
												val = rec.get(fname);
										}
										if (val && (val.indexOf(newValue) + 1)) {
											filter = true;
											// Если нашли совпадение по одному полю, зачем искать по остальным
											throw BreakException;
										}
									});
								} catch (e) {
									if (e !== BreakException) throw e;
								}
								return filter;
							}
						})
					];
					me.EvnStickGrid.getStore().filter(filters);
					me.checkToolPanelButtons();
				}
			},
			minWidth: 42 + 500,
			emptyText: 'Поиск'
		});

		me.toolPanel = Ext6.create('Ext6.Toolbar', {
			height: 50.5,
			width: 40,
			border: false,
			style: {
				background: '#f5f5f5'
			},
			margin: '0 3 0 0',
			noWrap: true,
			right: 0,
			items: [
				me.filterField,
				'->',
				{
					text: 'Добавить ЛВН',
					userCls: 'button-without-frame',
					iconCls: 'panicon-add',
					tooltip: langs('Добавить ЛВН'),
					itemId: 'menuAddLvn',
					menu: [{
						text: 'ЛВН',
						handler: function() {
							me.EvnStickGrid.itemMenu = 1;
							me.openEvnStickEditWindow('add');
						}
					}, {
						text: 'Справка учащегося',
						handler: function() {
							me.EvnStickGrid.itemMenu = 2;
							me.openEvnStickEditWindow('add');
						}
					}, {
						text: 'ЛВН из ФСС',
						handler: function() {
							me.EvnStickGrid.itemMenu = 3;
							me.openEvnStickEditWindow('add');
						}
					}]
				}, {
					text: 'Открыть',
					itemId: 'toolPanelOpen',
					disabled: true,
					userCls: 'button-without-frame',
					iconCls: 'panicon-edit',
					tooltip: langs('Открыть'),
					handler: function () {
						me.editEvnStickEditWindow();
					}
				}, {
					text: 'Создать дубликат',
					itemId: 'toolPanelCopy',
					disabled: true,
					userCls: 'button-without-frame',
					iconCls: 'panicon-create-duplicate',
					tooltip: langs('Создать дубликат'),
					handler: function () {
						me.doCopy()
					}
				}, {
					text: 'Удалить',
					itemId: 'toolPanelDelete',
					disabled: true,
					userCls: 'button-without-frame',
					iconCls: 'panicon-del-prescr-item',
					tooltip: langs('Удалить'),
					handler: function () {
						me.doDelete()
					}
				}, {
					text: 'Печать',
					itemId: 'toolPanelPrint',
					disabled: true,
					userCls: 'button-without-frame',
					iconCls: 'panicon-print',
					tooltip: langs('Печать'),
					handler: function () {
						me.doPrintEvnStick();
					}
				}
			]
		});

		me.btnAdd = Ext6.create('Ext6.button.Button',{
			margin: '0 0 0 50',
			xtype: 'button',
			text: 'Добавить ЛВН',
			cls: 'button-without-frame add-lvn-button',
			style:{
				'text-transform': 'none',
				padding: '0px'
			},
			menu: {},
			listeners: {
				click: function () {
					Ext6.getCmp(me.id).addEvnStick(this)
				}
			}
		});
		var contactTpl = new Ext6.XTemplate(
			'<div class="contact-cell">',
			'<div class="contact-text-panel">',
			'<p><span class="contact-text" style="font: 13px/17px Roboto; color: #000;">{StickWorkType_Name}</span></p>',
			'</div>',
			'<div class="contact-tools-panel">{tools}</div>',
			'</div>'
		);
		var actionIdTpl = new Ext6.Template([
			'{wndId}-{name}-{id}'
		]);
		var toolTpl = new Ext6.Template([
			'<span id="{actionId}" class="packet-btn packet-btn-{name} {cls}" data-qtip="{qtip}"></span>'
		]);
		var createTool = function(toolCfg) {
			if (toolCfg.hidden) return '';
			var obj = Ext6.apply({wndId: me.getId()}, toolCfg);
			obj.actionId = actionIdTpl.apply(obj);
			Ext6.defer(function() {
				var el = Ext.get(obj.actionId);
				if (el) el.on('click', function(e) {
					e.stopEvent();
					if (toolCfg.menu) {
						toolCfg.menu.showBy(e.target);
					}
					if (toolCfg.handler) {
						toolCfg.handler();
					}
				});
			}, 10);
			return toolTpl.apply(obj);
		};
		var toolsRenderer = function(value, meta, record) {
			if (!record.get('active')) return '';
			var selMod = me.EvnStickGrid.getSelectionModel();
			var id = record.get('EvnStick_id');
			var delAccessType = record.get('delAccessType');
			var cancelAccessType = record.get('cancelAccessType');
			var EvnStickBase_IsDelQueue = record.get('EvnStickBase_IsDelQueue');
			
			var addRequestHidden = true;
			if (
				getRegionNick() != 'kz'
				&& record.get('ISELN')
				&& !record.get('requestExist')
				&& record.get('EvnStick_pid') == me.Evn_id
			) {
				addRequestHidden = false;
			}

			var tools = [{
				id: id,
				name: 'edit',
				qtip: 'Открыть',
				handler: function() {
					me.EvnStickGrid.itemMenu = 7;
					selMod.select(record);
					me.editEvnStickEditWindow();
				}
			}, {
				id: id,
				name: 'delPacket',
				hidden: EvnStickBase_IsDelQueue == 2 || delAccessType != 'edit',
				qtip: 'Удалить',
				handler: function() {
					selMod.select(record);
					if(record)
						me.doDelete();
				}
			}, {
				id: id,
				name: 'cancel',
				hidden: EvnStickBase_IsDelQueue == 2 || cancelAccessType != 'edit',
				qtip: 'Аннулировать',
				handler: function() {
					selMod.select(record);
					if(record) {
						me.doDelete();
					}
				}
			}, {
				id: id,
				name: 'undodelete',
				hidden: EvnStickBase_IsDelQueue != 2,
				qtip: 'Восстановить',
				handler: function() {
					selMod.select(record);
					if(record) {
						me.undoDelete();
					}
				}
			}, {
				id: id,
				name: 'addRequest',
				hidden: addRequestHidden,
				qtip: 'Создать запрос в ФСС',
				handler: function() {
					var options = {
						action: 'addRequest',
						rec: record
					}
					me.openStickFSSDataEditWindow(options);
				}
			}];

			return tools.map(createTool).join('');
		};
		var contactRenderer = function(value, meta, record) {
			var obj = Ext6.apply(record.data, {
				tools: toolsRenderer.apply(me, arguments)
			});
			obj.StickWorkType_Name = value.slice(0,1).toUpperCase()+value.slice(1);
			return contactTpl.apply(obj);
		};
		me.EvnStickGrid = Ext6.create('swGridWithBtnAddRecords', {
			itemId: 'evnStickGrid',
			border: false,
			region: 'center',
			tbar: me.toolPanel,
			cls: 'EMKBottomPanelGrid',
			withBtnShowMore: false,
			viewConfig: {
				getRowClass: function(record, rowIndex, rowParams, store){
					var cls = '';
					if (record.get('EvnStick_rid') == me.Evn_id) {
						cls = cls + 'x-grid-rowbold ';
					}
					return cls;
				}
			},
			params: {
				EvnStick_pid: me.Evn_id,
				Person_id: me.Person_id,
				Lpu_id: getGlobalOptions().lpu_id,
				Org_id: getGlobalOptions().org_id
			},
			columns: [{
				flex: 4,
				height: 32,
				text: 'Документ',
				minWidth: 160,
				dataIndex: 'evnStickType',
				renderer: function(value, metaData, record){
					var prefix = '', suffix = '';
					
					if (record.get('evnStickType') == 3) {
						prefix ="Справка учащегося ";
					} else if (record.get('ISELN')) {
						prefix ="ЭЛН ";
					} else {
						prefix ="ЛВН ";
					}
					
					if(record.get('mark') && record.get('Lpu_Nick')!=getGlobalOptions().lpu_nick )
						suffix = ' <img style="cursor:pointer;" src="/img/icons/emk/panelicons/VarningAlertIcon.png" onClick="Ext6.getCmp(\''+me.id+'\').openEvnStickChange('+metaData.rowIndex+');" data-qtip="'+prefix+' открыт в '+record.get('Lpu_Nick').replace(/"/g,'&quot;')+'"/>';
					return prefix + record.get('EvnStick_Num') + suffix;
				}
			}, {
				flex: 2,
				text: 'Открыт',
				height: 32,
				minWidth: 100,
				formatter: 'date("d.m.Y")',
				dataIndex: 'EvnStick_setDate'
			}, {
				flex: 2,
				text: 'Закрыт',
				height: 32,
				minWidth: 100,
				formatter: 'date("d.m.Y")',
				dataIndex: 'EvnStick_disDate'
			}, {
				flex: 3,
				text: 'Порядок выписки',
				minWidth: 100,
				height: 32,
				dataIndex: 'StickOrder_Name',
				renderer: function (value, metaData, record) {
					return  value.slice(0,1).toUpperCase()+value.slice(1);
				}
			}, {
				flex: 3,
				text: 'ТАП/КВС',
				minWidth: 100,
				height: 32,
				dataIndex: 'CardType',
				renderer: function (value, metaData, record) {
					var ct = record.get('CardType'),
						nc = record.get('NumCard'),
						ctl = record.get('linkCardType'),
						ncl = record.get('linkNumCard'),
						s = (ct ? ct : '') + ' ' + (nc ? nc : '');
					if( ctl && ncl && !(ct==ctl && nc==ncl) )
						s += ' / '+ ctl + ' ' + ncl;
					return s;
				}
			}, {
				flex: 4,
				text: 'Тип занятости',
				minWidth: 100,
				height: 32,
				dataIndex: 'StickWorkType_Name',
				renderer: contactRenderer
			}, {
				flex: 4,
				text: 'Мед.организация',
				minWidth: 100,
				height: 32,
				dataIndex: 'Lpu_Name',
				renderer: function (value, metaData, record) {
					var lpu = record.get('Lpu_Nick'),
						lpu2 = record.get('linkLpu_Nick'),
						s = (lpu ? lpu : '');
					if( lpu2 && lpu!=lpu2)
						s += ' / '+lpu2;
					return s;
				}
			},{
				flex: 4,
				text: 'Состояние ЭЛН в ФСС',
				minWidth: 100,
				height: 32,
				dataIndex: 'StickFSSType_Name',
				renderer: function (value, metaData, record) {
					if (record.get('ISELN')) {
						if (record.get('EvnStickBase_IsDelQueue') == 2) {
							return 'Аннулирован';
						}
						return value;
					}
					else {
						return '';
					}
				}
			},{
				width: 60,
				text: 'ЭЦП',
				dataIndex: 'EvnStick_Sign',
				tdCls: 'vertical-middle',
				xtype: 'widgetcolumn',
				widget: {
					xtype: 'swEMDPanel',
					bind: {
						EMDRegistry_ObjectName: 'EvnStickStudent',
						EMDRegistry_ObjectID: '{record.EvnStick_id}',
						IsSigned: '{record.EvnStick_IsSigned}',
						Hidden: '{record.SignHidden}'
					}
				}
			}],
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnStick_id', type: 'int' },
					{
						name: 'SignHidden',
						type: 'boolean',
						convert: function(val, row) {
							if (row.get('evnStickType') && row.get('evnStickType') == 3 && row.get('accessType') == 'edit') {
								return false;
							} else {
								return true;
							}
						}
					},
					{ name: 'EvnStick_IsSigned', type: 'int' },
					{ name: 'Person_id', type: 'int' },
					{ name: 'Server_id', type: 'int' },
					{ name: 'PersonEvn_id', type: 'int' },
					{ name: 'EvnStick_mid', type: 'int' },
					{ name: 'EvnStick_pid', type: 'int' },
					{ name: 'evnStickType', type: 'int' },
					{ name: 'EvnStick_rid', type: 'int' },
					{ name: 'EvnStickBase_IsDelQueue', type: 'int' },
					{ name: 'EvnStick_setDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'EvnStick_disDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'StickWorkType_Name', type: 'string' },
					{ name: 'StickOrder_id', type: 'int' },
					{ name: 'StickOrder_Name', type: 'string' },
					{ name: 'EvnStick_Num', type: 'string' },
					{ name: 'StickLeaveType_id', type: 'int' },
					{ name: 'StickLeaveType_Name', type: 'string' },
					{ name: 'StickLeaveType_Code', type: 'string' },
					{ name: 'accessType', type: 'string' },
					{ name: 'delAccessType', type: 'string' },
					{ name: 'CardType', type: 'string' },
					{ name: 'NumCard', type: 'string' },
					{ name: 'linkNumCard', type: 'string' },
					{ name: 'linkCardType', type: 'string' },
					{ name: 'Evn_pid', type: 'int' },
					{ name: 'EvnStick_IsPaid', type: 'int' },
					{ name: 'EvnStick_IsInReg', type: 'int' },
					{ name: 'ISELN', type: 'int' },
					{ name: 'requestExist', type: 'int' },
					{ name: 'EvnStickLinked', type: 'int'},
					{ name: 'requestExist', type: 'int' },
					{ name: 'thisevn', type: 'int' },
					{ name: 'active', type: 'boolean', defaultValue: false},
					{ name: 'Lpu_Nick', type: 'string' },
					{ name: 'linkLpu_Nick', type: 'string' },
					{ name: 'mark', type: 'boolean', defaultValue: false},
					{ name: 'StickCause_SysNick', type: 'string' },
					{ name: 'EvnStick_prid', type: 'int' }
				],
				listeners: {
					'load': function(store, records) {
						var cnt = 0;
						store.each(function(record) {
							if (record.get('EvnStick_rid') == me.Evn_id) {
								cnt++;
								record.set('thisevn', 1);
							} else record.set('thisevn', 0);
							me.syncStores();
						});
						me.setTitleCounter(cnt);
						me.checkToolPanelButtons();
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Stick&m=loadEvnStickPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [{
					property: 'EvnStick_setDate',
					direction: 'DESC'
				}]
			}),
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record) {
						record.set('active', true);
						me.checkToolPanelButtons();
					},
					deselect: function(model, record) {
						record.set('active', false);
						me.checkToolPanelButtons();
					}
				}
			},
			listeners: {
				itemmouseenter: function(grid, record) {
					if (grid.selection != record) {
						record.set('active', true);
					}
				},
				itemmouseleave: function(grid, record) {
					if (grid.selection != record) {
						record.set('active', false);
					}
				}
			}
		});
		
		me.EvnStickChangeStore = Ext6.create('Ext6.data.Store', {//на боевом таких лвн должно быть не более 1-го, поэтому один раз получим его, чтобы в автоподгрузке общего списка больше не грузить.
			fields: [
				{name: 'EvnStick_id', type: 'int'},
				{ name: 'EvnStick_pid', type: 'int' },
				{ name: 'EvnStick_mid', type: 'int' },
				{ name: 'Lpu_id', type: 'int' },
				{ name: 'EvnStick_setDate', type: 'string' },
				{ name: 'EvnStick_disDate', type: 'string' },
				{ name: 'StickOrder_Name', type: 'string' },
				{ name: 'evnStickType', type: 'string' },
				{ name: 'StickWorkType_Name', type: 'string' },
				{ name: 'EvnStickDoc', type: 'int' },
				{ name: 'StickType_Name', type: 'string' },
				{ name: 'EvnStick_Ser', type: 'string' },
				{ name: 'EvnStick_Num', type: 'string' },
				{ name: 'EvnStick_ParentTypeName', type: 'string' },
				{ name: 'parentClass', type: 'int' },
				{ name: 'EvnStick_ParentNum', type: 'string' },
				{ name: 'EvnStatus_Name', type: 'string' }
			],
			proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Stick&m=getEvnStickChange',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
			sorters: [{
					property: 'EvnStick_id',
					direction: 'DESC'
				}],
			listeners: {
					'load': function(store, records) {
						me.syncStores();
					}
				}
			});


		Ext6.apply(this, {
			items: [
				this.EvnStickGrid
			]
		});

		this.callParent(arguments);
	}
});
