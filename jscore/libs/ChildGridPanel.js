/**
 * Список новорожденных в исходе беременности
 */
sw.Promed.ChildGridPanel = Ext.extend(sw.Promed.ViewFrame, {
	title:'Дети',
	//style:'margin-bottom: 10px',
	height:130,
	autoLoadData:false,
	focusOnFirstLoad:false,
	dataUrl:'/?c=BirthSpecStac&m=loadChildGridData',
	useEmptyRecord: false,

	//методы для переопределения
	beforeChildAdd: function(objectToReturn, addFn) {
		return true;
	},
	afterChildAdd: function(objectToReturn) {

	},
	beforeChildDelete: function(objectToReturn, deleteFn) {
		return true;
	},
	afterChildDelete: function(objectToReturn) {

	},

	printChildEvnPS: function() {
		var grid = this.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('ChildEvnPS_id'))) {
			return false;
		}

		printBirt({
			'Report_FileName': 'EvnPS_Child.rptdesign',
			'Report_Params': '&paramEvnPS=' + record.get('ChildEvnPS_id'),
			'Report_Format': 'pdf'
		});
	},

	childAddOnBD: function() {
		var gridPanel = this;
		var grid = gridPanel.getGrid();

		var objectToReturn = gridPanel.getObjectToReturn();

		var check = gridPanel.beforeChildAdd(objectToReturn, function(){gridPanel.childAddOnBD()});
		if (!check) {
			return false;
		}

		var params = {
			action: 'add'
		};

		getWnd('swPersonSearchWindow').show({
			editOnly:true,
			searchMode: 'all',
			personSurname: objectToReturn.Person_SurName,
			PersonBirthDay_BirthDay: Ext.util.Format.date(objectToReturn.BirthSpecStac_OutcomDate, 'd.m.Y'),
			onSelect: function(person_data) {
				getWnd('swPersonSearchWindow').hide();

				Ext.Ajax.request({
					params:{
						DeputyPerson_id: objectToReturn.Person_id,
						Person_id: person_data.Person_id,
						Server_id: person_data.Server_id
					},
					success: function(response, options) {
						//сработает при сохранении человвека
						var OutcomeDate = Ext.util.Format.date(objectToReturn.BirthSpecStac_OutcomDate, 'd.m.Y');
						params.Person_id =  person_data.Person_id;
						params.PersonEvn_id = person_data.PersonEvn_id;
						params.Server_id = person_data.Server_id;
						params.BirthSpecStac_id = objectToReturn.BirthSpecStac_id;
						params.Person_BirthDay =  OutcomeDate;
						params.ChildTermType_id = objectToReturn.ChildTermType_id;
						params.PersonNewBorn_CountChild = objectToReturn.BirthSpecStac_CountChild + 1;
						params.PersonNewBorn_IsAidsMother = objectToReturn.BirthSpecStac_IsHIV;
						params.callback = function(result){
							objectToReturn.PersonNewBorn_id = (result&&result.PersonNewBorn_id)?result.PersonNewBorn_id:null;
							gridPanel.afterChildAdd(objectToReturn);

							gridPanel.loadData({
								globalFilters: {BirthSpecStac_id: objectToReturn.BirthSpecStac_id},
								noFocusOnLoad:true
							});
						}
						Ext.Ajax.request({
							params: {
								BirthSpecStac_OutcomeDate: OutcomeDate,
								motherEvnSection_id: objectToReturn.EvnSection_id,
								mother_Person_id: objectToReturn.Person_id,
								child_Person_id: person_data.Person_id
							},
							success: function(response, options) {
								var resp = Ext.util.JSON.decode(response.responseText);
								var ok = resp[0].Success;
								params.action = (resp[0].add==1)?'add':'edit';
								if (ok) {
									getWnd('swPersonBirthSpecific').show(params);
								} else {
									sw.swMsg.alert('Ошибка', resp[0].Error_Msg);
								}
							},
							url: '/?c=BirthSpecStac&m=checkChild'
						});
					},
					url:'/?c=PersonNewBorn&m=addDeputy'
				});

			}
		});

		return true;
	},

	childAdd: function() {
		var gridPanel = this;
		var grid = gridPanel.getGrid();

		var objectToReturn = gridPanel.getObjectToReturn();
		var params = {};

		var check = gridPanel.beforeChildAdd(objectToReturn, function(){gridPanel.childAdd()});
		if (!check) {
			return false;
		}

		if (Ext.isEmpty(objectToReturn.BirthSpecStac_id) || Ext.isEmpty(objectToReturn.Person_id)) {
			return false;
		}

		var loadMask = new Ext.LoadMask(gridPanel.getEl(), {msg:"Загрузка данных..."});
		loadMask.show();

		Ext.Ajax.request({
			params: {Person_id: objectToReturn.Person_id},
			url: '/?c=Person&m=getAddressByPersonId',
			success: function(response) {
				loadMask.hide();

				var resp = Ext.util.JSON.decode(response.responseText);
				var fields = resp[0];
				fields.Person_SurName = objectToReturn.Person_SurName;
				fields.Person_BirthDay = Ext.util.Format.date(objectToReturn.BirthSpecStac_OutcomDate, 'd.m.Y');
				fields.SocStatus = 'babyborn';
				fields.DeputyKind_id = 2;
				fields.DeputyPerson_id = objectToReturn.Person_id;
				fields.DeputyPerson_Fio = objectToReturn.Person_SurName+' '+objectToReturn.Person_FirName+' '+objectToReturn.Person_SecName;

				getWnd('swPersonEditWindow').show({
					action: 'add',
					fields: fields,
					callback: function (saved_person) {
						params.action = 'add';
						params.Person_id =  saved_person.Person_id;
						params.PersonEvn_id = saved_person.PersonEvn_id;
						params.Server_id = saved_person.Server_id;
						params.BirthSpecStac_id = objectToReturn.BirthSpecStac_id;
						params.Person_BirthDay =  Ext.util.Format.date(objectToReturn.BirthSpecStac_OutcomDate, 'd.m.Y');
						params.EvnSection_mid = objectToReturn.EvnSection_id;
						params.ChildTermType_id = objectToReturn.ChildTermType_id;
						params.PersonNewBorn_CountChild = objectToReturn.BirthSpecStac_CountChild + 1;
						params.onCancelAction = function(){
							Ext.Ajax.request({
								url: '/?c=BirthSpecStac&m=deleteChild',
								params: {
									Person_id: saved_person.Person_id,
									type: 'cancel'
								},
								callback: function(options, success, response) {
									if ( !success ) {
										sw.swMsg.alert('Ошибка', 'При удалении возникли ошибки');
										return false;
									}
								}.createDelegate(this)
							});
						};

						params.callback = function(result) {
							objectToReturn.PersonNewBorn_id = (result&&result.PersonNewBorn_id)?result.PersonNewBorn_id:null;
							gridPanel.afterChildAdd(objectToReturn);

							gridPanel.loadData({
								globalFilters:{BirthSpecStac_id: objectToReturn.BirthSpecStac_id},
								noFocusOnLoad:true
							});
						}
						var OutcomeDate = Ext.util.Format.date(objectToReturn.BirthSpecStac_OutcomDate, 'd.m.Y');

						//проверим, может ли выбранный человек быть ребенком этой женщины
						Ext.Ajax.request({
							params: {
								BirthSpecStac_OutcomeDate: OutcomeDate,
								motherEvnSection_id: objectToReturn.EvnSection_id,
								mother_Person_id: objectToReturn.Person_id,
								child_Person_id: saved_person.Person_id
							},
							success:function (response, options) {
								var resp = Ext.util.JSON.decode(response.responseText);
								var ok = resp[0].Success;
								if (ok) {
									getWnd('swPersonBirthSpecific').show(params);
								} else {
									sw.swMsg.alert('Ошибка', resp[0].Error_Msg);
								}
							},
							url:'/?c=BirthSpecStac&m=checkChild'
						});
					}
				});
			},
			failure: function() {
				loadMask.hide();
			}
		});

		return true;
	},

	childEdit: function(action) {
		var gridPanel = this;
		var grid = gridPanel.getGrid();

		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('Person_cid'))) {
			return false;
		}

		var params = {
			action: action || 'edit',
			Person_id: record.get('Person_cid')
		};
		params.callback = function(){
			gridPanel.loadData({
				globalFilters: {BirthSpecStac_id: record.get('BirthSpecStac_id')},
				noFocusOnLoad: true
			});
		};
		getWnd('swPersonBirthSpecific').show(params);

		return true;
	},

	childDelete: function(type) {
		var gridPanel = this;
		var grid = gridPanel.getGrid();
		var objectToReturn = gridPanel.getObjectToReturn();

		var record = grid.getSelectionModel().getSelected();
		if (record.get('BirthSvid_id')) {
			sw.swMsg.alert('Ошибка удаления', 'Нельзя удалить эту запись, т.к. выписано свидетельство о рождении');
			return false;
		}
		if (record.get('PntDeathSvid_id')) {
			sw.swMsg.alert('Ошибка удаления', 'Нельзя удалить эту запись, т.к. выписано свидетельство о смерти');
			return false;
		}

		var params = {
			ChildEvnPS_id: record.get('ChildEvnPS_id'),
			PersonNewBorn_id: record.get('PersonNewBorn_id'),
			Person_id: record.get('Person_cid'),
			EvnLink_id: record.get('EvnLink_id'),
			PntDeathSvid_id: record.get('PntDeathSvid_id')
		};
		if (type) {
			params.type = type;
		}
		if (type != 'kvs' && record.get("ChildEvnPS_id") > 0) {
			sw.swMsg.alert('Ошибка удаления', 'Нельзя удалить этого человека.');
			return false;
		}

		var check = gridPanel.beforeChildDelete(objectToReturn, function(){gridPanel.childDelete()});
		if (!check) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					if ( record.get("PersonNewBorn_id") > 0  && record.get('Person_cid') > 0) {
						// удалить КВС
						// закрыть окно после успешного удаления
						var loadMask = new Ext.LoadMask(this.getEl(), {
							msg: (type=='kvs')?"Удаление КВС...":"Удаление ребенка..."
						});
						loadMask.show();

						Ext.Ajax.request({
							callback: function(options, success, response) {
								loadMask.hide();

								if ( success ) {
									objectToReturn.DeletedPersonNewBorn_id = params.PersonNewBorn_id;
									gridPanel.afterChildDelete(objectToReturn);

									gridPanel.loadData({
										globalFilters:{BirthSpecStac_id: record.get('BirthSpecStac_id')},
										noFocusOnLoad:true
									});
								} else {
									sw.swMsg.alert('Ошибка', 'При удалении КВС возникли ошибки');
									return false;
								}
							}.createDelegate(this),
							params: params,
							url: '/?c=BirthSpecStac&m=deleteChild'
						});
					} else {
						sw.swMsg.alert('Ошибка', 'При удалении КВС возникли ошибки');
						return false;
					}
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:'Вы действительно хотите удалить эту запись?',
			title:'Вопрос'
		});
		return true;
	},

	childSearch: function() {
		//открывает окно поиска КВС, чтобы можно было выбрать КВС рожденного в этих родах человека
		//если этого человека еще нет в базе, его пользователь может его создать через окно поиска человека.
		var gridPanel = this;
		var objectToReturn = this.getObjectToReturn();
		var params = {};

		var check = gridPanel.beforeChildAdd(objectToReturn, function(){gridPanel.childSearch()});
		if (!check) {
			return false;
		}

		//params.opener = this;
		params.childPS = true;
		params.objectToReturn = objectToReturn;
		params.Person_Surname = objectToReturn.Person_SurName;
		params.Person_Birthday = Ext.util.Format.date(objectToReturn.BirthSpecStac_OutcomDate, 'd.m.Y');
		params.onHide = function() {
			gridPanel.loadData({
				globalFilters: {BirthSpecStac_id: objectToReturn.BirthSpecStac_id},
				noFocusOnLoad: true
			});
		};
		//afterChildAdd вызывается в objectToReturnCallback
		getWnd('swEvnPSSearchWindow').show(params);
	},

	childEditPS: function (action) {
		var gridPanel = this;
		var grid = gridPanel.getGrid();

		var objectToReturn = gridPanel.getObjectToReturn();

		var record = grid.getSelectionModel().getSelected();
		if (!record) {
			return false;
		}

		var params = {};
		params.EvnPS_id = record.get('ChildEvnPS_id');
		params.Person_id = record.get('Person_cid');
		params.Server_id = record.get('Server_id');
		if (action) {
			params.action = action;
		} else {
			params.action = 'edit';

		}
		params.childPS = true;
		//var panelSpec = this.findById(this.id + '_SpecificsPanel');
		//panelSpec.toggleCollapse();

		params.ChildTermType_id = objectToReturn.ChildTermType;
		params.PersonNewBorn_CountChild = objectToReturn.BirthSpecStac_CountChild + 1;
		params.callback = function () {
			if (record) {// record - выделенная запись в грид в окне Движения матери
				//проставим в выделенную запись данные из периодики ребенка - массу и рост при рождении
				//если в квс ребенка открывали форму редактирования движения и специфику по новорожденным
				//и в этой специфике есть масса/рост при рождении, то она будет передана сюда в arguments[0].evnPSData
				//иначе делаем запрос на сервер по аяксу
				if (arguments[0].evnPSData.BirthWeight) {
					record.set('Person_Weight', arguments[0].evnPSData.BirthWeight);
					record.set('PersonWeight_text', arguments[0].evnPSData.PersonWeight_text);
					record.set('Okei_id', arguments[0].evnPSData.Okei_id);
				} else {
					Ext.Ajax.request({
						method:'post',
						params:{
							Person_id:arguments[0].evnPSData.Person_id,
							WeightMeasureType_id:1
						},
						success:function (response, options) {
							var resp = Ext.util.JSON.decode(response.responseText);
							if (resp && resp[0]) {
								if (resp[0].PersonWeight_Weight) {
									record.set('Person_Weight', resp[0].PersonWeight_Weight);
									record.set('PersonWeight_text', resp[0].PersonWeight_text);
									record.set('Okei_id', Number(resp[0].Okei_id));
								} else {
									record.set('Person_Weight', null);
									record.set('PersonWeight_text', null);
									record.set('Okei_id', null);
								}
							} else {
								record.set('Person_Weight', null);
								record.set('PersonWeight_text', null);
								record.set('Okei_id', null);
							}
							record.commit();
							//возвращаем фокус в грид
							gridPanel.focus();
						},
						url:'/?c=PersonWeight&m=loadPersonWeightGrid'
					});
				}
				if (arguments[0].evnPSData.BirthHeight) {
					record.set('Person_Height', arguments[0].evnPSData.BirthHeight);
				} else {
					Ext.Ajax.request({
						method:'post',
						params:{
							Person_id:arguments[0].evnPSData.Person_id,
							HeightMeasureType_id:1
						},
						success:function (response, options) {
							var resp = Ext.util.JSON.decode(response.responseText);
							if (resp && resp[0]) {
								if (resp[0].PersonHeight_Height) {
									record.set('Person_Height', Number(resp[0].PersonHeight_Height));
								} else {
									record.set('Person_Height', null);
								}
							} else {
								record.set('Person_Height', null);
							}
							record.commit();
							//возвращаем фокус в грид
							gridPanel.focus();
						},
						url:'/?c=PersonHeight&m=loadPersonHeightGrid'
					});
				}
				if (arguments[0].evnPSData.LeaveType_Name) {
					record.set('BirthResult', arguments[0].evnPSData.LeaveType_Name);
				}
				if (arguments[0].evnPSData.countChild) {
					record.set('CountChild', arguments[0].evnPSData.countChild);
				}
				record.commit();
				//возвращаем фокус в грид
				gridPanel.focus();
			}
		};
		getWnd({objectName:'swEvnPSEditWindow2', objectClass:'swEvnPSEditWindow'}, {params:{id:'EvnPSEditWindow2'}}).show(params);
	},

	childHosp: function() {
		var gridPanel = this;
		var grid = gridPanel.getGrid();

		var objectToReturn = gridPanel.getObjectToReturn();
		var record = grid.getSelectionModel().getSelected();

		if (!record) {
			return false;
		}

		/*var check = gridPanel.beforeChildAdd(objectToReturn, function(){gridPanel.childHosp()});
		if (!check) {
			return false;
		}*/

		if(record.get('ChildEvnPS_id')>0){
			gridPanel.childEditPS();
			return false;
		}

		var openPSEditWindowForChild = function () {
			var params = {};
			params.action = 'add';
			params.PrehospType_id = 1
			params.Person_id =  record.get('Person_cid');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');
			params.childPS = true;
			params.ChildTermType_id = objectToReturn.ChildTermType_id;
			params.PersonNewBorn_CountChild = record.get('CountChild');
			params.PersonNewBorn_IsAidsMother = objectToReturn.BirthSpecStac_IsHIV;
			params.callback = function(){
				gridPanel.loadData({
					globalFilters: {BirthSpecStac_id: objectToReturn.BirthSpecStac_id},
					noFocusOnLoad: true
				});										//Этот каллбэк вызывается при удачном сохранении в окне КВС
				objectToReturn.result = arguments[0];//записываю результаты сохранения, некоторые данные о КВС ребенка в переданный объект
				objectToReturn.callback();//в этом же объекте указан каллбэк, который надо выполнить после сохранения КВС ребенка. Выполняю его
			}
			getWnd({objectName:'swEvnPSEditWindow2', objectClass:'swEvnPSEditWindow'},{params:{id:'EvnPSEditWindow2'}}).show(params);
		};

		var OutcomeDate = Ext.util.Format.date(objectToReturn.BirthSpecStac_OutcomDate, 'd.m.Y');

		//проверим, может ли выбранный человек быть ребенком этой женщины
		Ext.Ajax.request({
			params:{
				BirthSpecStac_OutcomeDate: OutcomeDate,
				motherEvnSection_id: objectToReturn.EvnSection_id,
				mother_Person_id: objectToReturn.Person_id,
				child_Person_id: record.get('Person_cid')
			},
			success:function (response, options) {
				var resp = Ext.util.JSON.decode(response.responseText);
				var ok = resp[0].Success;
				if (ok) {
					openPSEditWindowForChild();
				} else {
					sw.swMsg.alert('Ошибка', resp[0].Error_Msg);
				}
			},
			url:'/?c=BirthSpecStac&m=checkChild'
		});
		return true;
	},

	delLink: function() {
		var gridPanel = this;
		var grid = gridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('Person_cid'))) {
			return false;
		}

		Ext.Ajax.request({
			url: '/?c=PersonNewBorn&m=delLink',
			params: {Person_id: record.get('Person_cid')},
			success: function(response, options) {
				var resp = Ext.util.JSON.decode(response.responseText);
				var ok = resp.success;
				if (ok) {
					gridPanel.loadData({
						globalFilters:{BirthSpecStac_id: record.get('BirthSpecStac_id')},
						noFocusOnLoad:true
					});
				} else {
					sw.swMsg.alert('Ошибка', resp.Error_Msg);
				}
			}
		});
		return true;
	},

	openBirthSvid: function() {
		var gridPanel = this;
		var grid = gridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('Person_cid')) {
			return false;
		}

		var data = gridPanel.getObjectToReturn();

		var formParams = {};

		var action = 'view';
		if (Ext.isEmpty(record.get('BirthSvid_id'))) {
			action = 'add';
			var plodCount = data.BirthSpecStac_CountChild;
			formParams.Person_id = data.Person_id;
			formParams.Person_cid = record.get('Person_cid');
			formParams.Server_id = data.Server_id;
			formParams.LpuSection_id = data.LpuSection_id;
			formParams.MedStaffFact_id = data.MedStaffFact_id;
			formParams.BirthSvid_BirthDT_Date = data.BirthSpecStac_OutcomDate;
			formParams.BirthSvid_BirthDT_Time = data.BirthSpecStac_OutcomTime;
			formParams.BirthPlace_id = data.BirthPlace_id;
			formParams.BirthSvid_PlodCount = plodCount;
			formParams.BirthSvid_IsMnogoplod = (plodCount > 1)?2:1;
			formParams.BirthSvid_PlodIndex = record.get('CountChild');
			formParams.Sex_id = record.get('Sex_id');
			formParams.BirthSvid_Mass = record.get('Person_Weight');
			formParams.Okei_mid = record.get('Okei_id');//BirthSvid_Mass - масса
			formParams.BirthSvid_Height = Number(record.get('Person_Height'));//BirthSvid_Height - рост
			formParams.BirthChildResult_id = 1;//BirthChildResult_id - ребенок родился = 1 (Живой)
			formParams.BirthSvid_ChildFamil = record.get('Person_F');
			formParams.BirthSvid_Week = data.BirthSpecStac_OutcomPeriod;
		} else {
			formParams.BirthSvid_id = record.get('BirthSvid_id');
		}
		getWnd('swMedSvidBirthEditWindow').show({
			action: action,
			formParams: formParams,
			focusOnfield: 'BirthSvid_ChildCount',
			callbackAfterSave: function(svid_id, BirthSvid_Num) {
				record.set('BirthSvid_id', svid_id);
				record.set('BirthSvid_Num', BirthSvid_Num);
				record.commit();
				gridPanel.focus();
			},
			callbackOnHide: function () {
				gridPanel.focus();
			}
		});
		return true;
	},

	openPntDeathSvid: function() {
		var gridPanel = this;
		var grid = gridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('Person_cid')) {
			return false;
		}

		var data = gridPanel.getObjectToReturn();

		var formParams = {};

		var action = 'view';
		if (Ext.isEmpty(record.get('PntDeathSvid_id'))) {
			action = 'add';
			var plodCount = data.BirthSpecStac_CountChild;
			formParams.Person_id =  data.Person_id;
			formParams.Server_id = data.Server_id;
			formParams.LpuSection_id = data.LpuSection_id;
			formParams.MedStaffFact_id = data.MedStaffFact_id;
			formParams.PntDeathSvid_ChildBirthDT_Date = data.BirthSpecStac_OutcomDate;
			formParams.PntDeathSvid_ChildBirthDT_Time = data.BirthSpecStac_OutcomTime;
			formParams.PntDeathSvid_PlodIndex = record.get('CountChild');
			formParams.PntDeathSvid_PlodCount = plodCount;
			formParams.PntDeathSvid_IsMnogoplod = (plodCount > 1)?2:1;
			formParams.Person_cid = record.get('Person_cid');
			formParams.PntDeathSvid_ChildFio = record.get('Person_F')+' '+record.get('Person_I')+' '+record.get('Person_O');
			formParams.Sex_id = record.get('Sex_id');
			formParams.Diag_iid = record.get('Diag_id');
			//formParams.PntDeathTime_id = record.get('PntDeathTime_id');
			formParams.Person_rid = data.Person_id;
			formParams.Person_r_FIO = data.Person_SurName+' '+data.Person_FirName+' '+data.Person_SecName;
		} else {
			formParams.PntDeathSvid_id = record.get('PntDeathSvid_id');
		}

		getWnd('swMedSvidPntDeathEditWindow').show({
			action:action,
			formParams:formParams,
			callback:function (svid_id, PntDeathSvid_Num) {
				record.set('PntDeathSvid_id', svid_id);
				record.set('PntDeathSvid_Num', PntDeathSvid_Num);
				if (!record.get('RecordStatus_Code').inlist([0,3])) {
					record.set('RecordStatus_Code', 2);
				}
				record.commit();
			},
			onHide:function () {}
		});
	},

	onDblClick:function () {
		if (!this.ViewActions.action_edit.isDisabled()) {
			this.ViewActions.action_edit.execute();
		}
	},
	onEnter:function () {
		if (!this.ViewActions.action_edit.isDisabled()) {
			this.ViewActions.action_edit.execute();
		}
	},
	onRowSelect:function (sm, index, record) {
		if (!record || Ext.isEmpty(record.get('Person_cid')) || getWnd('swWorkPlaceMZSpecWindow').isVisible()) {
			this.getAction('childsearch').disable();
			this.getAction('hosp').disable();
			this.getAction('search').disable();
			this.getAction('dellink').disable();
			this.getAction('delchild').disable();
			this.getAction('action_medsvid_menu').disable();

			this.getAction('action_print').menu.printObject.disable();
			this.getAction('action_print').menu.printChildEvnPS.disable();
		} else {
			this.getAction('action_medsvid_menu').enable();

			this.getAction('childsearch').setDisabled(this.readOnly);
			this.getAction('hosp').setDisabled(this.readOnly);
			this.getAction('search').setDisabled(this.readOnly);
			this.getAction('delchild').setDisabled(this.readOnly);
			this.getAction('action_delete').setDisabled(!record.get('ChildEvnPS_id') || this.readOnly);
			this.getAction('dellink').setDisabled(record.get('EvnSection_mid') || this.readOnly);

			this.getAction('action_print').menu.printObject.setDisabled(false);
			this.getAction('action_print').menu.printChildEvnPS.setDisabled(!record.get('ChildEvnPS_id'));
		}
	},

	stringfields:[
		{name:'PersonNewBorn_id', type:'int', header: 'ID', key: true},
		{name:'ChildEvnPS_id', type:'int', hidden: true},
		{name:'EvnSection_mid', type:'int', hidden:true},
		{name:'BirthSpecStac_id', type:'int', hidden:true},
		{name:'Person_F', type:'string', hidden:false, header:'Фамилия'},
		{name:'Person_I', type:'string', hidden:false, header:'Имя'},
		{name:'Person_O', type:'string', hidden:false, header:'Отчество'},
		{name:'Person_Bday', type:'date', hidden:false, format:'d.m.Y', header:'Дата рождения', width:110},
		{name:'Sex_name', type:'string', hidden:false, header:'Пол', width:80},
		{name:'Person_Weight', type:'float', hidden:true, header:'Масса при рождении', width:120},
		{name:'Okei_id', type:'int', hidden:true},
		{name:'Sex_id', type:'int', hidden:true},
		{name:'PersonWeight_text', type:'string', hidden:false, header:'Масса при рождении', width:120},
		{name:'Person_Height', type:'float', hidden:false, header:'Рост при рождении (см)', width:110},
		{name:'BirthSvid_id', type:'int', hidden:true},
		{name:'BirthSvid_Num', type:'string', hidden:false, header:'Св-во о рождении', width:110},
		{name:'PntDeathSvid_id', type:'int', hidden:true},
		{name:'PntDeathSvid_Num', type:'string', header:'Св-во о смерти', width:110, hidden:false},
		{name:'BirthResult', type:'string', hidden:false, header:'Результат родов', width:100},
		{name:'CountChild', type:'float', hidden:false, header:'Который по счету', width:110},
		{name:'PersonEvn_id', type:'int', hidden:true},
		{name:'RecordStatus_Code', type:'int', hidden:true},	//проверить возможность отложенного сохранения
		{name:'EvnLink_id', type:'int', hidden:true},
		{name:'Person_id', type:'int', hidden:true},
		{name:'Person_cid', type:'int', hidden:true},
		{name:'Server_id', type:'int', hidden:true}
	],

	initActions: function() {
		var gridPanel = this;
		var medSvidMenu = new Ext.menu.Menu({
			items: [{
				name: 'action_medsved',
				text: 'Мед. св-во о рождении',
				handler: function () {this.openBirthSvid()}.createDelegate(this)
			}, {
				name: 'action_pntdethsvid',
				text: 'Мед. св-во о перинат. смерти',
				handler: function() {this.openPntDeathSvid()}.createDelegate(this)
			}]
		});
		gridPanel.addActions({name:'action_medsvid_menu', text:'Мед. свидетельства', menu: medSvidMenu, disabled: getWnd('swWorkPlaceMZSpecWindow').isVisible()});
		gridPanel.addActions({name:'search', text: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Найти КВС',  tooltip: 'Указать существующую КВС ребенка', icon: 'img/icons/search160.png', disabled: getWnd('swWorkPlaceMZSpecWindow').isVisible(), handler: function() {
			//вызвать окно поиска КВС, там при необходимости создать КВС ребенка, а возможно и самого ребенка
			gridPanel.childSearch();
		}},1);
		gridPanel.addActions({name:'hosp', text: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Госпитализировать',  tooltip: 'Госпитализировать ребенка', icon: 'img/icons/search160.png', disabled: getWnd('swWorkPlaceMZSpecWindow').isVisible(), handler: function() {
			//вызвать окно поиска КВС, там при необходимости создать КВС ребенка, а возможно и самого ребенка
			gridPanel.childHosp();
		}},1);
		gridPanel.addActions({name:'childsearch', text: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Найти в БД',  tooltip: 'Найти в БД', icon: 'img/icons/search160.png', disabled: getWnd('swWorkPlaceMZSpecWindow').isVisible(), handler: function() {
			//вызвать окно поиска КВС, там при необходимости создать КВС ребенка, а возможно и самого ребенка
			gridPanel.childAddOnBD();
		}},1);
		gridPanel.addActions({name:'dellink', text: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Удалить связь',  tooltip: 'Удалить связь', icon: 'img/icons/search160.png', disabled: getWnd('swWorkPlaceMZSpecWindow').isVisible(), handler: function() {
			gridPanel.delLink();
		}},1);
		gridPanel.addActions({name:'delchild', text: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Удалить',  tooltip: 'Удалить специфику', icon: 'img/icons/search160.png', disabled: getWnd('swWorkPlaceMZSpecWindow').isVisible(), handler: function() {
			gridPanel.childDelete();
		}},5);
	},

	initComponent: function() {
		var gridPanel = this;

		this.actions = [
			{name:'action_add', handler:function () {gridPanel.childAdd()}},
			{name:'action_edit', handler:function () {gridPanel.childEdit()}},
			{name:'action_view', handler:function () {gridPanel.childEdit('view')}},
			{name:'action_delete', text:"Удалить КВС", handler:function () {gridPanel.childDelete('kvs')}},
			{name:'action_print',
				menuConfig: {
					printChildEvnPS: {name: 'printChildEvnPS', text: langs('097/у - История развития новорожденного'), handler: function(){gridPanel.printChildEvnPS()}}
				}
			}
		];

		gridPanel.objectToReturnCallback = function() {
			//этот каллбэк вызовется когда пользователь нажмет сохранить в КВС ребенка
			//проверяю может ли человек, КВС которого выбрали, быть ребенком этой женщины
			var grid = gridPanel.getGrid();
			var objectToReturn = this;	//Именно такой контекст должен быть!!!

			var evnPSData = (objectToReturn.result)?objectToReturn.result.evnPSData:{};
			var evnSection_exists = !Ext.isEmpty(objectToReturn.EvnSection_id);

			var addThisChild = function (Person_id, add) {
				//var callbackObjectToReturn = parentWindow.getObjectToReturn();	//объект из движения ребенка
				var params = {};
				params.Person_id = Person_id;
				params.action = (add?'add':'edit');
				params.Server_id = objectToReturn.Server_id;
				params.BirthSpecStac_id = objectToReturn.BirthSpecStac_id;

				params.childPS = true;
				params.ChildTermType_id = objectToReturn.ChildTermType_id;
				params.PersonNewBorn_CountChild = objectToReturn.BirthSpecStac_CountChild + 1;
				params.PersonNewBorn_IsAidsMother = objectToReturn.BirthSpecStac_IsHIV;
				if(evnPSData.EvnPS_id){
					params.EvnPS_id = evnPSData.EvnPS_id
				}
				params.callback = function(result){
					if (add) {
						objectToReturn.PersonNewBorn_id = (result&&result.PersonNewBorn_id)?result.PersonNewBorn_id:null;
						gridPanel.afterChildAdd(objectToReturn);
					}

					gridPanel.loadData({
						globalFilters: {BirthSpecStac_id: objectToReturn.BirthSpecStac_id},
						noFocusOnLoad: true
					});
					//parentWindow.birthFormRecalc();
					//parentWindow.flbr=true;
				}
				getWnd('swPersonBirthSpecific').show(params);
			};

			var OutcomeDate = Ext.util.Format.date(objectToReturn.BirthSpecStac_OutcomDate, 'd.m.Y');

			if (/*evnSection_exists*/true) {
				//проверка на сервере аяксом
				Ext.Ajax.request({
					params:{
						BirthSpecStac_OutcomeDate: OutcomeDate,
						motherEvnSection_id: objectToReturn.EvnSection_id,
						mother_Person_id: objectToReturn.Person_id,
						childEvnPS_id: evnPSData.EvnPS_id
					},
					success:function (response, options) {
						var resp = Ext.util.JSON.decode(response.responseText);
						var ok = resp[0].Success;//resp[0]....
						if (ok) {
							//все хорошо, продолжаю добавление
							var add = (resp[0].add==1)?true:false;
							addThisChild(resp[0].person_id,add);
						} else {
							//показываю сообщение об ошибке
							sw.swMsg.alert('Ошибка', resp[0].Error_Msg, function () {
								//возвращаем фокус в грид
								gridPanel.focus();
							});
						}
					},
					url:'/?c=BirthSpecStac&m=checkChild'
				});
			} else {
				//проверка на клиенте
				var err = '';
				//сэймперсун
				if (objectToReturn.Person_id == evnPSData.Person_id) {
					err = 'Мать добавляется в список рожденных ею детей';
				}
				//алреди
				/*var maxChildIdx = grid.getStore().getCount() - 1;
				for (var i = 0; i <= maxChildIdx; i++) {
					if (grid.getStore().getAt(i).get('Person_cid') == evnPSData.Person_id) {
						err = 'Ребенок добавляется к матери повторно';
					}
				}*/

				var birthDate = objectToReturn.BirthSpecStac_OutcomDate;
				if (birthDate) {
					if (evnPSData.Person_Birthday < birthDate) {
						err = 'Дата рождения ребенка наступила раньше даты родов';
					} else {
						if (evnPSData.Person_Birthday > birthDate.add(Date.DAY, 2)) {
							err = 'Дата рождения ребенка наступила позже даты родов более чем на два дня';
						}
					}
				}
				//алреди анозер мазер
				//проверяется только на сервере
				if (err != '') {
					sw.swMsg.alert('Ошибка', err, function () {
						gridPanel.focus();
					});
				} else {
					addThisChild(evnPSData.Person_id);
				}
			}
			gridPanel.focus();
		};

		if (!gridPanel.getObjectToReturn) {
			gridPanel.getObjectToReturn = function() {
				var gridPanel = this;
				return {
					BirthSpecStac_id: null,
					BirthSpecStac_OutcomPeriod: null,
					BirthSpecStac_OutcomDate: null,
					BirthSpecStac_OutcomTime: null,
					BirthSpecStac_CountChild: null,
					BirthSpecStac_IsHIV: null,
					BirthPlace_id: null,
					ChildTermType_id: null,
					Server_id: null,
					LpuSection_id: null,
					MedStaffFact_id: null,
					Person_id: null,
					Person_SurName: null,
					Person_FirName: null,
					Person_SecName: null,
					EvnSection_id: null,
					addressInfo: {},
					callback: gridPanel.objectToReturnCallback
				};
			};
		}

		sw.Promed.ChildGridPanel.superclass.initComponent.apply(this, arguments);
	}
});