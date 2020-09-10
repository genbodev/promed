
/********/
sw.Promed.swPersonBirthSpecific = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	//autoHeight: true,
	height: 700,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	EvnPS_id:null,
	closeAction: 'hide',
	collapsible: false,
	openPersonBirthTraumaEditWindow:function (action,type) {
		if (!type || !action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swPersonBirthTraumaEditWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_uje_otkryito']);
			return false;
		}
		var win = this;
		var grid = this.findById('PBS_PersonBirthTraumaGrid'+type).getGrid();
		var base_form = this.FormPanel.getForm();
		
		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('PersonBirthTrauma_id'))||record.get('RecordStatus_Code')==4) {
				return false;
			}
		}
		
		var params = new Object();
		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' ) {
				return false;
			}
			data.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) { return rec.get('PersonBirthTrauma_id') == data.PersonBirthTrauma_id; });
			var record = grid.getStore().getAt(index);

			if ( typeof record == 'object' ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for (var i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data[grid_fields[i]]);
				}

				record.commit();
			} else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PersonBirthTrauma_id') ) {
					grid.getStore().removeAll();
				}
				data.PersonBirthTrauma_id = -swGenTempId(grid.getStore());

				var newRecord = new Ext.data.Record(data);
				grid.getStore().loadRecords({records: [newRecord]}, {add: true}, true);
			}
		}.createDelegate(this);
		params.formParams = new Object();
		
		params.BirthTraumaType_id = type;
		
		params.Person_BirthDay = this.BirthDay;
		params.PersonNewBorn_id = base_form.findField('PersonNewBorn_id').getValue();
		if (action != 'add') {
			if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonBirthTrauma_id') ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();
			//params.PersonBirthTrauma_id=selected_record.get('PersonBirthTrauma_id');
			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}
		params.formParams.BirthTraumaType_id = type;
		params.formParams.Person_BirthDay = this.BirthDay;
		getWnd('swPersonBirthTraumaEditWindow').show(params);
	},
	deletePersonBirthTrauma:function(type){
		var grid = this.findById('PBS_PersonBirthTraumaGrid'+type).getGrid();
		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected()
		
		switch ( Number(record.get('RecordStatus_Code')) ) {
			case 0:
				grid.getStore().remove(record);
			break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
			break;
		}

		if ( grid.getStore().getCount() == 0 ) {
			//LoadEmptyRow(grid);
		} else {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	deleteApgarRate:function(){
		var win = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('PBS_NewbornApgarRateGrid').getGrid();
		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected()
		
		switch ( Number(record.get('RecordStatus_Code')) ) {
			case 0:
				grid.getStore().remove(record);
			break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
			break;
		}

		if ( grid.getStore().getCount() == 0 ) {
			//LoadEmptyRow(grid);
		} else {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
		
	},
	addNewbornApgarRate:function(){
		var win = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.findById('PBS_NewbornApgarRateGrid').getGrid();
		var data = {
			NewbornApgarRate_id:-swGenTempId(grid.getStore()),
			NewbornApgarRate_Time:0,
			RecordStatus_Code:0
		};
		grid.getStore().loadData([ data ], true);
		
	},
	doSave: function (callback) {
		//this.tabPanel.setActiveTab('tab_PBSCommon');
		var form = this.FormPanel;
		if (!form.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if (!this.ApgarRateGrid.isValid()) {
			sw.swMsg.alert(lang['oshibka'], 'Не верное значение в оценке состояния по шкале Апгар');
			return false;
		}
		this.submit(callback);
		return true;
	},
	submit: function (callback) {
		var form = this.FormPanel;
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		var params = {};
		
		
		var apgarGrid = this.findById('PBS_NewbornApgarRateGrid').getGrid();
		apgarGrid.getStore().clearFilter();
		if ( apgarGrid.getStore().getCount() > 0 ) {
			var ApgarData = getStoreRecords(apgarGrid.getStore());


			params.ApgarData = Ext.util.JSON.encode(ApgarData);

			apgarGrid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		} 
		var PersonBirthTraumaData =[]; 
		var tGrid;
		for(var x = 1;x<5;x++){
			tGrid = this.findById('PBS_PersonBirthTraumaGrid'+x).getGrid();
			tGrid.getStore().clearFilter();
			if ( tGrid.getStore().getCount() > 0 ) {
				[].push.apply(PersonBirthTraumaData,getStoreRecords(tGrid.getStore()))
				tGrid.getStore().filterBy(function(rec) {return (Number(rec.get('RecordStatus_Code')) != 3);});
			} 
		}
		params.PersonBirthTraumaData = Ext.util.JSON.encode(PersonBirthTraumaData);
		
		loadMask.show();
		form.getForm().submit({
			params: params,
			failure: function (result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#'] + action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function (result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.PersonNewBorn_id) {
						var apgarGrid = current_window.findById('PBS_NewbornApgarRateGrid').getGrid();
						apgarGrid.getStore().load({params:{PersonNewBorn_id:action.result.PersonNewBorn_id}});

						if (this.isTraumaTabGridLoaded) {
							var grid1 = current_window.findById('PBS_PersonBirthTraumaGrid1').getGrid();
							var grid2 = current_window.findById('PBS_PersonBirthTraumaGrid2').getGrid();
							var grid3 = current_window.findById('PBS_PersonBirthTraumaGrid3').getGrid();
							var grid4 = current_window.findById('PBS_PersonBirthTraumaGrid4').getGrid();

							grid1.getStore().baseParams.BirthTraumaType_id = 1;
							grid2.getStore().baseParams.BirthTraumaType_id = 2;
							grid3.getStore().baseParams.BirthTraumaType_id = 3;
							grid4.getStore().baseParams.BirthTraumaType_id = 4;

							grid1.getStore().load({params:{PersonNewBorn_id:action.result.PersonNewBorn_id}});
							grid2.getStore().load({params:{PersonNewBorn_id:action.result.PersonNewBorn_id}});
							grid3.getStore().load({params:{PersonNewBorn_id:action.result.PersonNewBorn_id}});
							grid4.getStore().load({params:{PersonNewBorn_id:action.result.PersonNewBorn_id}});
						}

					    if(callback){
							form.getForm().findField('PersonNewBorn_id').setValue(action.result.PersonNewBorn_id);
							callback();
					    }else{
							current_window.callback(action.result);
							current_window.hide();
					    }
					} else {
						sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function () {
									form.hide();
								},
								icon: Ext.Msg.ERROR,
								msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
								title: lang['oshibka']
							});
					}
				}
			}
		});
	},
	draggable: true,
	setRefuseType(typename){
		if (getRegionNick() == 'ufa') {
			var base_form = this.FormPanel.getForm();
			var parent = null;
			if (typename == 'RefuseType_pid') {
				parent = base_form.findField('PersonNewBorn_IsAudio');
			}
			if (typename == 'RefuseType_aid') {
				parent = base_form.findField('PersonNewBorn_IsNeonatal');
			}
			if (typename == 'RefuseType_bid') {
				parent = base_form.findField('PersonNewBorn_IsBCG');
			}
			if (typename == 'RefuseType_gid') {
				parent = base_form.findField('PersonNewBorn_IsHepatit');
			}

			if (base_form)
				var hidefield = base_form.findField(typename);
			if (hidefield)
				var hidepanel = hidefield.findParentByType('panel');
			if (hidepanel) {
				hidepanel.setVisible(parent.getValue() == 1);
			}
		}
	},
	openPersonWeightEditWindow:function (action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swPersonWeightEditWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_izmereniya_massyi_uje_otkryito']);
			return false;
		}

		var base_form = this.findById('PBS_PersonNewBornForm').getForm();
		var grid = this.findById('PBS_PersonWeightGrid').getGrid();
		var params = new Object();

		var measure_type_exceptions = new Array();

		grid.getStore().each(function (rec) {
			if (rec.get('WeightMeasureType_id') && rec.get('WeightMeasureType_Code').toString().inlist(['1', '2'])) {
				measure_type_exceptions.push(rec.get('WeightMeasureType_Code'));
			}
		});

		params.action = action;
		params.callback = function (data) {
			if (!data || !data.personWeightData) {
				return false;
			}

			data.personWeightData.RecordStatus_Code = 0;

			// Обновить запись в grid
			var record = grid.getStore().getById(data.personWeightData.PersonWeight_id);

			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.personWeightData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function (key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.personWeightData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PersonWeight_id')) {
					grid.getStore().removeAll();
				}

				

				grid.getStore().loadData([ data.personWeightData ], true);
			}
		}
		params.formParams = new Object();
		params.measureTypeExceptions = measure_type_exceptions;
		params.personMode = 'child';

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
			params.formParams.Person_id = base_form.findField('Person_id').getValue();
			params.formParams.Server_id = base_form.findField('Server_id').getValue();
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.formParams = selected_record.data;
			params.onHide = function () {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('swPersonWeightEditWindow').show(params);
	},
	enableEdit: function(enable) {
		/*if (!this.checkRole('edit')) { // если для формы не разрешено "редактирование", значит и поля,кнопки должны быть задисаблены
			enable = false;
		}*/
		
		// все редактируемые поля на форме
		for (var k in this.editFields) {
			if (typeof this.editFields[k] == 'object' && typeof this.editFields[k].enable == 'function') {
				if (enable) {
					if (!this.editFields[k].initialConfig.disabled) {
						this.editFields[k].enable();
					}
				} else {
					this.editFields[k].disable();
				}
			}
		}
		this.findById('PBS_NewbornApgarRateGrid').setReadOnly(!enable)
		this.hideEditButtons(enable);

		if (this.onEnableEdit && typeof this.onEnableEdit == 'function') {
			this.onEnableEdit(enable);
		}
	},	
	hideEditButtons: function(enable) {
		if (!this.checkRole('edit')) { // если для формы не разрешено "редактирование", значит и поля,кнопки должны быть задисаблены
			enable = false;
		}
		
		// все кнопки, кроме закрыть, помощь, найти, поиск, обновить, фильтр, сброс
		for (var k in this.buttons) {
			if (typeof this.buttons[k] == 'object' && typeof this.buttons[k].show == 'function') {
				if (enable) {
					if (!this.buttons[k].initialConfig.hidden) {
						this.buttons[k].show();
					}
				} else if (
					!(this.buttons[k].iconCls && this.buttons[k].iconCls.inlist(['print16','cancel16','close16','help16','search16','digital-sign16'])) // кнопки которые не надо скрывать для режима "Просмотр"
				) {
					this.buttons[k].hide();
				}
			}
		}
	},
	formStatus: 'edit',
	id: 'PersonBirthSpecificWindow',

	openEvnObservNewBornEditWindow: function(action) {
		if (!action || !action.inlist(['add','edit','view'])) {
			return false;
		}
		var wnd = this;
		var grid_panel = this.findById('PBS_EvnObservNewBornGrid');
		var grid = grid_panel.getGrid();
		var base_form = this.FormPanel.getForm();

		var PersonNewBorn_id = base_form.findField('PersonNewBorn_id').getValue();

		if (Ext.isEmpty(PersonNewBorn_id) || PersonNewBorn_id == 0) {
			this.doSave(function() {wnd.openEvnObservNewBornEditWindow(action)});
			return false;
		}

		var params = {
			action: action,
			disableChangeTime: false,
			callback: function() {
				grid_panel.getAction('action_refresh').execute();
			}
		};
		if (action == 'add') {
			params.formParams = {
				EvnObserv_pid: base_form.findField('EvnSection_mid').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				Person_Birthday: this.BirthDay,
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue(),
				PersonNewBorn_id: PersonNewBorn_id
			};
		} else {
			var record = grid.getSelectionModel().getSelected();

			if (!record || Ext.isEmpty(record.get('EvnObserv_id'))) {
				return false;
			}

			params.formParams = {
				EvnObserv_id: record.get('EvnObserv_id'),
				Person_Birthday: this.BirthDay
			};
		}

		getWnd('swEvnObservEditWindow').show(params);
		return true;
	},
	openEvnNeonatalSurveyEditWindow: function (action) {
		if (!action || !action.inlist(['view'])) {
			return false;
		}
		var wnd = this;

		var base_form = wnd.FormPanel.getForm();
		var grid = this.findById('PBS_EvnNeonatalSurveyGrid');

		if (grid)
			grid = grid.getGrid();

		if (grid)
			grid = grid.getSelectionModel().getSelected();

		var person_info = this.findById('ESecEF_PersonInformationFrame');

		if (getWnd('swEvnNeonatalSurveyEditWindow').isVisible() && getWnd('swEvnNeonatalSurveyEditWindow').changedDatas) {
			Ext.Msg.alert(langs('Сообщение'), langs('Окно Наблюдение состояния младенца уже открыто<br> и в нём имеются несохранённые изменния!'));
			return false;
		}

		var pers_data = {
			Person_id: base_form.findField('Person_id').getValue(),
			PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue(),
			Person_Surname: grid ? grid.get('Person_Surname') : '',
			Person_Firname: grid ? grid.get('Person_Firname') : '',
			Person_Secname: grid ? grid.get('Person_Secname') : '',
			Person_Birthday: grid ? grid.get('Person_Birthday') : '',
			Sex_id: grid ? grid.get('Sex_Code') : ''
		};

		var params = {
			ENSEW_title: langs('Наблюдение состояния младенца'),
			action: action,
			fromObject: this,
			pers_data: pers_data,
			EvnNeonatalSurvey_pid: grid ? grid.get('EvnSection_pid') : '',
			EvnNeonatalSurvey_rid: grid ? grid.get('EvnSection_pid') : '',
			EvnNeonatalSurvey_id: action == 'add' ? null : (grid ? grid.get('EvnNeonatalSurvey_id') : ''),
			ParentObject: 'EvnPersonNewBorn',
			userMedStaffFact: getGlobalOptions().CurMedStaffFact_id || null,
			ARMType: 'stas_pol',
			LpuSection_id: grid ? grid.get('LpuSection_id') : '',
			Lpu_id: getGlobalOptions().lpu_id,
			FirstConditionLoad: false
		};

		getWnd('swEvnNeonatalSurveyEditWindow').show(params);

		return true;
	},
	deleteEvnObservNewBorn: function() {
		var grid_panel = this.findById('PBS_EvnObservNewBornGrid');
		var grid = grid_panel.getGrid();
		var base_form = this.FormPanel.getForm();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('EvnObserv_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {
						EvnObserv_id: record.get('EvnObserv_id'),
						PersonNewBorn_id: base_form.findField('PersonNewBorn_id').getValue()
					};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=EvnObserv&m=deleteEvnObserv'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},
	setNewbornBlood(){
		if (getRegionNick() == 'ufa') {
			var base_form = this.FormPanel.getForm();
			var BloodBili = this.findById('PBS_BloodBili_Xml');
			var BloodHemoglo = this.findById('PBS_BloodHemoglo_Xml');
			var BloodEryth = this.findById('PBS_BloodEryth_Xml');
			var BloodHemato = this.findById('PBS_BloodHemato_Xml');

			Ext.Ajax.request({
				url: '/?c=PersonNewBorn&m=loadNewBornBlood',
				params: {
					Person_id: base_form.findField('Person_id').getValue()
				},
				callback: function(options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						response_obj.forEach(function(item) {
							var EvnXml_id = "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + item.EvnXml_id + " });'>" + "Просмотреть результат" + "</a>";
							switch (item.UslugaComplex_Code) {
								case 'A09.05.021':
									base_form.findField('PersonNewborn_BloodBili').setValue(item.UslugaTest_ResultValue);
									BloodBili.setText(EvnXml_id,false);
									BloodBili.show();
									BloodBili.enable();

									break;
								case 'A09.05.003':
									base_form.findField('PersonNewborn_BloodHemoglo').setValue(item.UslugaTest_ResultValue);
									BloodHemoglo.setText(EvnXml_id,false);
									BloodHemoglo.show();
									BloodHemoglo.enable();

									break;
								case 'A08.05.003':
									base_form.findField('PersonNewborn_BloodEryth').setValue(item.UslugaTest_ResultValue);
									BloodEryth.setText(EvnXml_id,false);
									BloodEryth.show();
									BloodEryth.enable();

									break;
								case 'A09.05.002':
									base_form.findField('PersonNewborn_BloodHemato').setValue(item.UslugaTest_ResultValue);
									BloodHemato.setText(EvnXml_id,false);
									BloodHemato.show();
									BloodHemato.enable();
									
									break;

								default:
									break;
							}
						});
					}
				}
			});
		}
	},

	initComponent: function() {
		var parentWin = this;

		var ApgarRateEditor = Ext.extend(Ext.form.NumberField, {
			maxValue: 2,
			allowDecimals: false,
			allowNegative: false,
			isValid: function(){
				ApgarRateEditor.superclass.isValid.apply(this, arguments);
				return true;
			}
		});

		var ApgarRateRenderer = function(value, meta) {
			var editor = parentWin.ApgarRateGrid.getColumnEditor(this.name);
			if (!editor.validateValue(value || '')) {
				meta.css = 'x-grid-cell-invalid';
			}
			return value;
		};

		var ApgarRateFields = [
			'NewbornApgarRate_Heartbeat',
			'NewbornApgarRate_Breath',
			'NewbornApgarRate_SkinColor',
			'NewbornApgarRate_ToneMuscle',
			'NewbornApgarRate_Reflex'
		];

		this.ApgarRateGrid = new sw.Promed.ViewFrame({
			//border:false,
			actions: [
				{name: 'action_add', handler: function(){this.addNewbornApgarRate()}.createDelegate(this)},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', handler: function(){parentWin.deleteApgarRate()}},
				{name: 'action_refresh', disabled: true},
				{name: 'action_print', hidden: true},
				{name:'action_save', hidden: true},
			],
			autoLoadData:false,
			focusOnFirstLoad:false,
			saveAtOnce: false,
			dataUrl:'/?c=PersonNewBorn&m=loadNewbornApgarRateGrid',
			height:140,
			id:'PBS_NewbornApgarRateGrid',
			getColumnEditor: function(name) {
				var cm = parentWin.ApgarRateGrid.getColumnModel();
				var index = cm.findColumnIndex(name);
				return cm.getCellEditor(index).field;
			},
			isValid: function() {
				var grid = parentWin.ApgarRateGrid.getGrid();
				var isValid = true;

				grid.getStore().each(function(rec) {
					isValid = ApgarRateFields.every(function(field) {
						var editor = parentWin.ApgarRateGrid.getColumnEditor(field);
						return editor.validateValue(rec.get(field)  || '');
					});
					if (!isValid) return false;
				});

				return isValid;
			},
			onAfterEdit: function(o) {
				o.grid.stopEditing(false);
				var rec = o.record;
				var fields = ApgarRateFields;

				var isEmpty = fields.every(function(field){
					var value = rec.get(field);
					var editor = parentWin.ApgarRateGrid.getColumnEditor(field);
					return (Ext.isEmpty(value) || !editor.validateValue(value  || ''));
				});
				var sum = fields.reduce(function(sum, field){
					var value = Number(rec.get(field));
					var editor = parentWin.ApgarRateGrid.getColumnEditor(field);
					return  editor.validateValue(value  || '') ? sum + value : sum;
				}, 0);

				if (!isEmpty) {
					rec.set('NewbornApgarRate_Values', sum);
				}
				if(rec.get('RecordStatus_Code') == 1){
					rec.set('RecordStatus_Code', 2);
				}
				o.record.commit();
			},
			onRowSelect:function (sm, index, record) {
				//
			},
			paging:false,
			region:'center',
			stringfields:[
				{name: 'NewbornApgarRate_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonNewBorn_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'NewbornApgarRate_Time', type: 'string', header: lang['vremya_posle_rojdeniya_min'],
					editor: new Ext.form.NumberField({maxValue: 60, allowDecimals: false})
				},
				{name: 'NewbornApgarRate_Heartbeat', header:lang['serdtsebienie'],
					editor: new ApgarRateEditor, renderer: ApgarRateRenderer
				},
				{name: 'NewbornApgarRate_Breath', header: lang['dyihanie'],
					editor: new ApgarRateEditor, renderer: ApgarRateRenderer
				},
				{name: 'NewbornApgarRate_SkinColor', header: lang['okraska_koji'],
					editor: new ApgarRateEditor, renderer: ApgarRateRenderer
				},
				{name: 'NewbornApgarRate_ToneMuscle', header: lang['tonus_myishts'],
					editor: new ApgarRateEditor, renderer: ApgarRateRenderer
				},
				{name: 'NewbornApgarRate_Reflex', header: lang['refleksyi'],
					editor: new ApgarRateEditor, renderer: ApgarRateRenderer
				},
				{name: 'NewbornApgarRate_Values', type: 'int', header:lang['otsenka_v_ballah'],
					editor: new Ext.form.NumberField({maxValue: 10, allowDecimals: false})
				}
			],
			title:lang['otsenka_sostoyaniya_po_shkale_apgar']
		});

		this.tabPanel = new Ext.TabPanel({
			id: 'PBS-tabs-panel',
			//autoScroll: true,

			border:false,
			activeTab: 0,
			//resizeTabs: true,
			//enableTabScroll: true,
			//autoWidth: true,
			//tabWidth: 'auto',
			layoutOnTabChange: true,
			listeners: {
				'tabchange': function(tab, panel) {
					var base_form = parentWin.FormPanel.getForm();
					var Person_id = base_form.findField('Person_id').getValue();
					var PersonNewBorn_id = base_form.findField('PersonNewBorn_id').getValue();

					if(!parentWin.isTraumaTabGridLoaded && panel.id == 'tab_PBSTrauma'){
						parentWin.isTraumaTabGridLoaded = true;

						parentWin.findById('PBS_PersonBirthTraumaGrid1').setReadOnly(parentWin.action=='view');
						parentWin.findById('PBS_PersonBirthTraumaGrid2').setReadOnly(parentWin.action=='view');
						parentWin.findById('PBS_PersonBirthTraumaGrid3').setReadOnly(parentWin.action=='view');
						parentWin.findById('PBS_PersonBirthTraumaGrid4').setReadOnly(parentWin.action=='view');

						var grid1 = parentWin.findById('PBS_PersonBirthTraumaGrid1').getGrid();
						var grid2 = parentWin.findById('PBS_PersonBirthTraumaGrid2').getGrid();
						var grid3 = parentWin.findById('PBS_PersonBirthTraumaGrid3').getGrid();
						var grid4 = parentWin.findById('PBS_PersonBirthTraumaGrid4').getGrid();

						if (!Ext.isEmpty(PersonNewBorn_id) && PersonNewBorn_id > 0) {
							grid1.getStore().baseParams.BirthTraumaType_id = 1;
							grid2.getStore().baseParams.BirthTraumaType_id = 2;
							grid3.getStore().baseParams.BirthTraumaType_id = 3;
							grid4.getStore().baseParams.BirthTraumaType_id = 4;

							grid1.getStore().load({params: {PersonNewBorn_id: PersonNewBorn_id}});
							grid2.getStore().load({params: {PersonNewBorn_id: PersonNewBorn_id}});
							grid3.getStore().load({params: {PersonNewBorn_id: PersonNewBorn_id}});
							grid4.getStore().load({params: {PersonNewBorn_id: PersonNewBorn_id}});
						}
					}

					if(!parentWin.isObservTabGridLoaded && panel.id == 'tab_PBSObserv'){
						parentWin.isObservTabGridLoaded = true;

						parentWin.findById('PBS_EvnObservNewBornGrid').removeAll();
						parentWin.findById('PBS_EvnObservNewBornGrid').setReadOnly(parentWin.action=='view');

						var grid_observ = parentWin.findById('PBS_EvnObservNewBornGrid').getGrid();

						grid_observ.getStore().load({
							params: {
								Person_id: Person_id,
								PersonNewBorn_id: (PersonNewBorn_id>0)?PersonNewBorn_id:null
							}
						});
					}
					if (!parentWin.isNeonatalTabGridLoaded && panel.id == 'tab_PBSEvnNeonatalSurvey') {
						parentWin.isNeonatalTabGridLoaded = true;

						var grid_neonatal = parentWin.findById('PBS_EvnNeonatalSurveyGrid').getGrid();

						grid_neonatal.getStore().load({
							params: {
								Person_id: Person_id
							}
						});
					}
				}
			},
			items:[
				{
				title: lang['obschaya_informatsiya'],
				id: 'tab_PBSCommon',
				iconCls: 'info16',
				border:false,
				height: 605,
				bodyStyle: 'overflow-y: scroll;',
				items: [{
						layout:'form',
						bodyStyle: 'padding: 5px 5px 0',
						labelAlign: 'right',
						border:false,
						labelWidth: 190,
						items:[
							{
								comboSubject:'ChildTermType',
								fieldLabel:lang['donoshennost'],
								hiddenName:'ChildTermType_id',
								width:300,
								xtype:'swcommonsprcombo'

							},{
								fieldLabel:lang['predlejanie'],
								comboSubject:'ChildPositionType',
								hiddenName:'ChildPositionType_id',
								name:'ChildPositionType_id',
								width:100,
								xtype:'swcommonsprcombo'
							},
							{
								comboSubject:'FeedingType',
								fieldLabel:lang['vid_vskarmlivaniya'],
								hiddenName:'FeedingType_id',
								width:300,
								xtype:'swcommonsprcombo',
								listeners:{
									keydown:function () {
										this.keyPressedOnThisControll = true;
									},
									keypress:function (inp, e) {
										if (!this.keyPressedOnThisControll) {
											return;
										}

										this.keyPressedOnThisControll = false;
									}
								}
							},{
								fieldLabel:lang['kotoryiy_po_schetu'],
								allowNegative:false,
								allowDecimals:false,
								hiddenName:'PersonNewBorn_CountChild',
								name:'PersonNewBorn_CountChild',
								width:100,
								xtype:'numberfield'
							},{
								comboSubject:'YesNo',
								fieldLabel:lang['vich-infektsiya_u_materi'],
								hiddenName:'PersonNewBorn_IsAidsMother',
								width:100,
								xtype:'swcommonsprcombo'
							},{
								comboSubject:'YesNo',
								fieldLabel:lang['otkaz_ot_rebenka'],
								hiddenName:'PersonNewBorn_IsRejection',
								width:100,
								xtype:'swcommonsprcombo',
								listeners:{
									keydown:function (inp, e) {
										if (e.getKey() == Ext.EventObject.TAB) {
											if (!e.shiftKey) {
												e.stopEvent();
												parentWin.buttons[0].focus();
											}
										}
									}
								}
							},{
								fieldLabel:lang['massa_ves_pri_rojdenii_g'],
								name:'PersonNewBorn_Weight',
								allowNegative:false,
								allowDecimals:false,
								maxLength:4,
								width:100,
								xtype:'numberfield'
							},{
								fieldLabel:lang['rost_dlina_pri_rojdenii_sm'],
								name:'PersonNewBorn_Height',
								allowNegative:false,
								allowDecimals:false,
								maxLength:2,
								width:100,
								xtype:'numberfield'
							},{
								fieldLabel:lang['okrujnost_golovyi_sm'],
								name:'PersonNewBorn_Head',
								allowNegative:false,
								allowDecimals:false,
								maxLength:2,
								width:100,
								xtype:'numberfield'
							},{
								fieldLabel:lang['okrujnost_grudi_sm'],
								name:'PersonNewBorn_Breast',
								allowNegative:false,
								allowDecimals:false,
								maxLength:2,
								width:100,
								xtype:'numberfield'
							},{
								comboSubject:'YesNo',
								fieldLabel:lang['nalichie_krovotecheniya'],
								hiddenName:'PersonNewBorn_IsBleeding',
								width:100,
								xtype:'swcommonsprcombo'
							},{
								autoHeight:true,
								layout:'form',
								style:'padding: 2px 10px;',
								title:'Критерии живорождения',
								xtype:'fieldset',
								hidden: getRegionNick()!='kz',

								items:[{
									layout: 'column',
									border: false,
									defaults: {
										border: false,
										style: 'margin-right: 20px;'
									},
									items: [{
										layout: 'form',
										items: [{
											xtype: 'checkbox',
											name: 'PersonNewBorn_IsBreath',
											hideLabel: true,
											boxLabel: 'Дыхание'
										}]
									}, {
										layout: 'form',
										items: [{
											xtype: 'checkbox',
											name: 'PersonNewBorn_IsHeart',
											hideLabel: true,
											boxLabel: 'Сердцебиение'
										}]
									}, {
										layout: 'form',
										items: [{
											xtype: 'checkbox',
											name: 'PersonNewBorn_IsPulsation',
											hideLabel: true,
											boxLabel: 'Пульсация пуповины'
										}]
									}, {
										layout: 'form',
										items: [{
											xtype: 'checkbox',
											name: 'PersonNewBorn_IsMuscle',
											hideLabel: true,
											boxLabel: 'Произвольное сокращение мускулатуры'
										}]
									}]
								}]
							},this.ApgarRateGrid,
							{
								xtype: 'fieldset',
								title: 'Анализ крови',
								autoHeight: true,
								style:'padding: 0px;',
								labelWidth: 170,
								items: [{
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 170,
											items: [
												{
													allowDecimals: false,
													xtype: 'numberfield',
													name: 'PersonNewborn_BloodBili',
													fieldLabel: 'Общий билирубин, Ммоль/л'
												}
											]
										}, {
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'label',
													id: 'PBS_BloodBili_Xml',
													hidden: true,
													style: 'margin-left: 10px;',
													html: ''
												}
											]
										}
									]
								}, {
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 170,
											items: [
												{
													allowDecimals: false,
													xtype: 'numberfield',
													name: 'PersonNewborn_BloodHemoglo',
													fieldLabel: 'Гемоглобин, г/л'
												}
											]
										}, {
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'label',
													id: 'PBS_BloodHemoglo_Xml',
													hidden: true,
													style: 'margin-left: 10px;',
													html: ''
												}
											]
										}
									]
								}, {
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 170,
											items: [
												{
													xtype: 'numberfield',
													name: 'PersonNewborn_BloodEryth',
													fieldLabel: 'Эритроциты, 10^12/л'
												}
											]
										}, {
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'label',
													id: 'PBS_BloodEryth_Xml',
													hidden: true,
													style: 'margin-left: 10px;',
													html: ''
												}
											]
										}
									]
								}, {
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 170,
											items: [
												{
													xtype: 'numberfield',
													name: 'PersonNewborn_BloodHemato',
													fieldLabel: 'Гематокрит, %'
												}
											]
										}, {
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'label',
													id: 'PBS_BloodHemato_Xml',
													hidden: true,
													style: 'margin-left: 10px;',
													html: ''
												}
											]
										}
									]
								}]
							},
							{
								fieldLabel:lang['pereveden_v'],
								comboSubject:'NewBornWardType',
								hiddenName:'NewBornWardType_id',
								name:'NewBornWardType_id',
								width:300,
								xtype:'swcommonsprcombo'
							},{
								layout: 'form',
								border: false,
								items: [
									{
										layout: 'column',
										border: false,
										items: [
											{
												layout: 'form',
												border: false,
												labelWidth: 190,
												width: 310,
												items: [
													{
														comboSubject:'YesNo',
														fieldLabel:lang['vzyata_proba_dlya_neonatalnogo_skrininga'],
														hiddenName:'PersonNewBorn_IsNeonatal',
														width:100,
														xtype:'swcommonsprcombo',
														listeners: {
															'change': function(combo, newValue, oldValue) {
																parentWin.setRefuseType('RefuseType_aid');
															}
														}
													}
												]
											}, {
												layout: 'form',
												border: false,
												labelWidth: 90,
												width: 310,
												hidden: getRegionNick() != 'ufa',
												items: [
													{
														comboSubject: 'RefuseType',
														fieldLabel: 'Уточнение',
														hiddenName: 'RefuseType_aid',
														width: 150,
														xtype: 'swcommonsprcombo'
													}
												]
											}
										]
									}
								]
							},{
								layout: 'form',
								border: false,
								items: [
									{
										layout: 'column',
										border: false,
										items: [
											{
												layout: 'form',
												border: false,
												labelWidth: 190,
												width: 310,
												items: [
													{
														comboSubject:'YesNo',
														fieldLabel:lang['audiologicheskiy_skrining'],
														hiddenName:'PersonNewBorn_IsAudio',
														width:100,
														xtype:'swcommonsprcombo',
														listeners: {
															'change': function(combo, record, index) {
																parentWin.setRefuseType('RefuseType_pid');
															}
														}
													}
												]
											}, {
												layout: 'form',
												border: false,
												labelWidth: 90,
												width: 310,
												hidden: getRegionNick() != 'ufa',
												items: [
													{
														comboSubject: 'RefuseType',
														fieldLabel: 'Уточнение',
														hiddenName: 'RefuseType_pid',
														width: 150,
														xtype: 'swcommonsprcombo'
													}
												]
											}
										]
									}
								]
							},
							{
								autoHeight:true,
								labelWidth:150,
								layout:'form',
								style:'padding: 0px;',
								title:lang['vaktsinatsiya'],
								xtype:'fieldset',

								items:[
									{
										layout:'form',
										border:false,
										items:[
											{
												layout:'column',
												border:false,
												items:[
													{
														layout:'form',
														border:false,
														labelWidth: 70,
														items:[
															{
																comboSubject:'YesNo',
																fieldLabel:lang['btsj'],
																hiddenName:'PersonNewBorn_IsBCG',
																width:100,
																xtype:'swcommonsprcombo',
																listeners: {
																	'change': function(combo, record, index) {
																		parentWin.setRefuseType('RefuseType_bid');
																	}
																}
															}
														]
													},{
														layout: 'form',
														border: false,
														labelWidth: 90,
														width: 270,
														hidden: getRegionNick() != 'ufa',
														items: [
															{
																comboSubject: 'RefuseType',
																fieldLabel: 'Уточнение',
																hiddenName: 'RefuseType_bid',
																width: 150,
																xtype: 'swcommonsprcombo'
															}
														]
													}, {
														layout:'form',
														border:false,
														labelWidth: 50,
														items:[
															{
																fieldLabel:lang['data'],
																format:'d.m.Y',
																name:'PersonNewBorn_BCGDate',
																plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
																selectOnFocus:true,
																width:100,
																xtype:'swdatefield'
															}
														]
													},{
														layout:'form',
														border:false,
														labelWidth: 50,
														items:[
															{
																fieldLabel:lang['seriya'],
																name:'PersonNewBorn_BCGSer',
																width:100,
																xtype:'textfield'
															},
														]
													},{
														layout:'form',
														border:false,
														labelWidth: 50,
														items:[
															{
																fieldLabel:lang['nomer'],
																name:'PersonNewBorn_BCGNum',
																width:100,
																xtype:'textfield'
															}
														]
													}
												]
											},{
												layout:'column',
												border:false,
												items:[
													{
														layout:'form',
														border:false,
														labelWidth: 70,
														items:[
															{
																comboSubject:'YesNo',
																fieldLabel:lang['gepatit_b'],
																hiddenName:'PersonNewBorn_IsHepatit',
																width:100,
																xtype:'swcommonsprcombo',
																listeners: {
																	'change': function(combo, record, index) {
																		parentWin.setRefuseType('RefuseType_gid');
																	}
																}
															}
														]
													},{
														layout: 'form',
														border: false,
														labelWidth: 90,
														width: 270,
														hidden: getRegionNick() != 'ufa',
														items: [
															{
																comboSubject: 'RefuseType',
																fieldLabel: 'Уточнение',
																hiddenName: 'RefuseType_gid',
																width: 150,
																xtype: 'swcommonsprcombo'
															}
														]
													}, {
														layout:'form',
														border:false,
														labelWidth: 50,
														items:[
															{
																fieldLabel:lang['data'],
																format:'d.m.Y',
																name:'PersonNewBorn_HepatitDate',
																plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
																selectOnFocus:true,
																width:100,
																xtype:'swdatefield'
															}
														]
													},{
														layout:'form',
														border:false,
														labelWidth: 50,
														items:[
															{
																fieldLabel:lang['seriya'],
																name:'PersonNewBorn_HepatitSer',
																width:100,
																xtype:'textfield'
															}
														]
													},{
														layout:'form',
														border:false,
														labelWidth: 50,
														items:[
															{
																fieldLabel:lang['nomer'],
																name:'PersonNewBorn_HepatitNum',
																width:100,
																xtype:'textfield'
															}
														]
													}
												]
											}
										]
									},

								]
							},

						]
					}]
				},{
				title: lang['rodovyie_travmyi_poroki_razvitiya'],
				id: 'tab_PBSTrauma',
				iconCls: 'info16',
				border:false,
				items: [{
						layout:'form',
						border:false,
						bodyStyle: 'padding: 5px 5px 0',
						labelAlign: 'right',
						labelWidth: 150,
						items:[
							new sw.Promed.ViewFrame({
								//border:false,
								actions:[
								{
									name:'action_add', 
									handler:function () {
										this.openPersonBirthTraumaEditWindow('add',1);
									}.createDelegate(this)
								},

								{
									name:'action_edit', 
									hidden:true
								},

								{
									name:'action_view', 
									handler:function () {
										this.openPersonBirthTraumaEditWindow('view',1);
									}.createDelegate(this)
								},

								{
									name:'action_delete', 
									handler:function(){parentWin.deletePersonBirthTrauma(1)}
								},

								{
									name:'action_refresh', 
									disabled:true
								},

								{
									name:'action_print', 
									disabled:true
								}
								],
								autoLoadData:false,
								focusOnFirstLoad:false,
								dataUrl:'/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height:130,
								id:'PBS_PersonBirthTraumaGrid1',
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
								onLoadData:function () {
								//
								},
								onRowSelect:function (sm, index, record) {
								//
								},
								paging:false,
								region:'center',
								stringfields:[
								{
									name:'PersonBirthTrauma_id', 
									type:'int', 
									header:'ID', 
									key:true
								},

								{
									name:'PersonBirthTrauma_setDate', 
									type:'date', 
									hidden:true
								},
								{
									name:'RecordStatus_Code', 
									type:'int', 
									hidden:true
								},
								{
									name:'Diag_id', 
									type:'int', 
									hidden:true
								},
								
								{
									name:'PersonNewBorn_id', 
									type:'int', 
									hidden:true
								},
								{
									name:'BirthTraumaType_id', 
									type:'int', 
									hidden:true
								},
								{
									name:'Diag_Code', 
									type:'string', 
									//hidden:true
									header:lang['kod']
								},

								{
									name:'Diag_Name', 
									type:'string', 
									//hidden:true
									header:lang['naimenovanie']
								},

								{
									name:'PersonBirthTrauma_Comment', 
									type:'string', 
									//hidden:true
									header:lang['rasshifrovka']
								}
								],
								title:lang['rodovyie_travmyi']
							}),
							new sw.Promed.ViewFrame({
								//border:false,
								actions:[
								{
									name:'action_add', 
									handler:function () {
										this.openPersonBirthTraumaEditWindow('add',2);
									}.createDelegate(this)
								},

								{
									name:'action_edit', 
									hidden:true
								},

								{
									name:'action_view', 
									handler:function () {
										this.openPersonBirthTraumaEditWindow('view',2);
									}.createDelegate(this)
								},

								{
									name:'action_delete', 
									handler:function(){parentWin.deletePersonBirthTrauma(2)}
								},

								{
									name:'action_refresh', 
									disabled:true
								},

								{
									name:'action_print', 
									disabled:true
								}
								],
								autoLoadData:false,
								focusOnFirstLoad:false,
								dataUrl:'/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height:130,
								id:'PBS_PersonBirthTraumaGrid2',
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
								onLoadData:function () {
								//
								},
								onRowSelect:function (sm, index, record) {
								//
								},
								paging:false,
								region:'center',
								stringfields:[
								{
									name:'PersonBirthTrauma_id', 
									type:'int', 
									header:'ID', 
									key:true
								},
								{
									name:'PersonBirthTrauma_setDate', 
									type:'date', 
									hidden:true
								},
								{
									name:'BirthTraumaType_id', 
									type:'int', 
									hidden:true
								},
								{
									name:'RecordStatus_Code', 
									type:'int', 
									hidden:true
								},
								{
									name:'Diag_id', 
									type:'int', 
									hidden:true
								},
								{
									name:'PersonNewBorn_id', 
									type:'int', 
									hidden:true
								},

								{
									name:'Diag_Code', 
									type:'string', 
									//hidden:true
									header:lang['kod']
								},

								{
									name:'Diag_Name', 
									type:'string', 
									//hidden:true
									header:lang['naimenovanie']
								},

								{
									name:'PersonBirthTrauma_Comment', 
									type:'string', 
									//hidden:true
									header:lang['rasshifrovka']
								}
								],
								title:lang['porajeniya_ploda']
							}),
							new sw.Promed.ViewFrame({
								//border:false,
								actions:[
								{
									name:'action_add', 
									handler:function () {
										this.openPersonBirthTraumaEditWindow('add',3);
									}.createDelegate(this)
								},

								{
									name:'action_edit', 
									hidden:true
								},

								{
									name:'action_view', 
									handler:function () {
										this.openPersonBirthTraumaEditWindow('view',3);
									}.createDelegate(this)
								},

								{
									name:'action_delete', 
									handler:function(){parentWin.deletePersonBirthTrauma(3)}
								},

								{
									name:'action_refresh', 
									disabled:true
								},

								{
									name:'action_print', 
									disabled:true
								}
								],
								autoLoadData:false,
								focusOnFirstLoad:false,
								dataUrl:'/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height:130,
								id:'PBS_PersonBirthTraumaGrid3',
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
								onLoadData:function () {
								//
								},
								onRowSelect:function (sm, index, record) {
								//
								},
								paging:false,
								region:'center',
								stringfields:[
								{
									name:'PersonBirthTrauma_id', 
									type:'int', 
									header:'ID', 
									key:true
								},
								{
									name:'PersonBirthTrauma_setDate', 
									type:'date', 
									hidden:true
								},
								{
									name:'BirthTraumaType_id', 
									type:'int', 
									hidden:true
								},
								{
									name:'RecordStatus_Code', 
									type:'int', 
									hidden:true
								},
								{
									name:'Diag_id', 
									type:'int', 
									hidden:true
								},
								{
									name:'PersonNewBorn_id', 
									type:'int', 
									hidden:true
								},

								{
									name:'Diag_Code', 
									type:'string', 
									//hidden:true
									header:lang['kod']
								},

								{
									name:'Diag_Name', 
									type:'string', 
									//hidden:true
									header:lang['naimenovanie']
								},

								{
									name:'PersonBirthTrauma_Comment', 
									type:'string', 
									//hidden:true
									header:lang['rasshifrovka']
								}
								],
								title:lang['vrojdennyie_poroki_razvitiya']
							}),
							new sw.Promed.ViewFrame({
								//border:false,
								actions:[
								{
									name:'action_add', 
									handler:function () {
										this.openPersonBirthTraumaEditWindow('add',4);
									}.createDelegate(this)
								},

								{
									name:'action_edit', 
									hidden:true
								},

								{
									name:'action_view', 
									handler:function () {
										this.openPersonBirthTraumaEditWindow('view',4);
									}.createDelegate(this)
								},

								{
									name:'action_delete', 
									handler:function(){parentWin.deletePersonBirthTrauma(4)}
								},

								{
									name:'action_refresh', 
									disabled:true
								},

								{
									name:'action_print', 
									disabled:true
								}
								],
								autoLoadData:false,
								focusOnFirstLoad:false,
								dataUrl:'/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height:130,
								id:'PBS_PersonBirthTraumaGrid4',
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
								onLoadData:function () {
								//
								},
								onRowSelect:function (sm, index, record) {
								//
								},
								paging:false,
								region:'center',
								stringfields:[
								{
									name:'PersonBirthTrauma_id', 
									type:'int', 
									header:'ID', 
									key:true
								},
								{
									name:'PersonBirthTrauma_setDate', 
									type:'date', 
									hidden:true
								},
								{
									name:'RecordStatus_Code', 
									type:'int', 
									hidden:true
								},
								{
									name:'BirthTraumaType_id', 
									type:'int', 
									hidden:true
								},
								{
									name:'Diag_id', 
									type:'int', 
									hidden:true
								},
								{
									name:'PersonNewBorn_id', 
									type:'int', 
									hidden:true
								},

								{
									name:'Diag_Code', 
									type:'string', 
									//hidden:true
									header:lang['kod']
								},

								{
									name:'Diag_Name', 
									type:'string', 
									//hidden:true
									header:lang['naimenovanie']
								},

								{
									name:'PersonBirthTrauma_Comment', 
									type:'string', 
									//hidden:true
									header:lang['rasshifrovka']
								}
								],
								title:lang['podozreniya_na_vrojdennyie_poroki']
							})
						]
					}]
				},{
					title: 'Наблюдения',
					id: 'tab_PBSObserv',
					iconCls: 'info16',
					border: false,
					items: [
						new sw.Promed.ViewFrame({
							id: 'PBS_EvnObservNewBornGrid',
							border: true,
							autoLoadData: false,
							focusOnFirstLoad: false,
							useEmptyRecord: false,
							dataUrl: '/?c=EvnObserv&m=loadEvnObservGrid',
							height: 605,
							actions: [
								{name: 'action_add', handler: function(){parentWin.openEvnObservNewBornEditWindow('add')}},
								{name: 'action_edit', handler: function(){parentWin.openEvnObservNewBornEditWindow('edit')}},
								{name: 'action_view', handler: function(){parentWin.openEvnObservNewBornEditWindow('view')}},
								{name: 'action_delete', handler: function(){parentWin.deleteEvnObservNewBorn()}},
								{name: 'action_refresh', hidden: true}
							],
							stringfields:[
								{name: 'EvnObserv_id', type: 'int', header: 'ID', key: true},
								{name: 'PersonNewBorn_id', type: 'int', hidden: true},
								{name: 'EvnObserv_pid', type: 'int', hidden: true},
								{name: 'EvnObserv_setDate', header: 'Дата', type: 'date', width: 80},
								{name: 'ObservTimeType_Name', header: 'Время', type: 'string', width: 120},
								{name: 'art_davlenie', header: lang['art_davlenie'], type: 'string', width: 80},
								{name: 'temperatura', header: lang['temperatura'], type: 'string', width: 80},
								{name: 'puls', header: lang['puls'], type: 'string', width: 80},
								{name: 'chastota_dyihaniya', header: lang['chastota_dyihaniya'], type: 'int', width: 80},
								{name: 'ves', header: lang['ves'], type: 'float', width: 80},
								{name: 'vyipito_jidkosti', header: lang['vyipito_jidkosti'], type: 'float', width: 80},
								{name: 'kol-vo_mochi', header: lang['kol-vo_mochi'], type: 'float', width: 80},
								{name: 'reaktsiya_na_osmotr', header: lang['reaktsiya_na_osmotr'], type: 'string', width: 80},
								{name: 'reaktsiya_zrachka', header: lang['reaktsiya_zrachka'], type: 'string', width: 80},
								{name: 'stul', header: lang['stul'], type: 'string', width: 80}
							]
						})
					]
				}, {
					title: 'Наблюдение состояния младенца',
					id: 'tab_PBSEvnNeonatalSurvey',
					iconCls: 'info16',
					border: false,
					items: [
						new sw.Promed.ViewFrame({
							id: 'PBS_EvnNeonatalSurveyGrid',
							border: true,
							autoLoadData: false,
							focusOnFirstLoad: false,
							useEmptyRecord: false,
							dataUrl: '/?c=EvnNeonatalSurvey&m=loadNeonatalSurveyGrid',
							height: 600,
							actions: [
								{
									name: 'action_add', hidden: true
								},
								{
									name: 'action_edit', hidden: true
								},
								{
									name: 'action_view', handler: function () {
										parentWin.openEvnNeonatalSurveyEditWindow('view')
									}
								},
								{
									name: 'action_delete', hidden: true
								}
							],
							stringfields: [
								{name: 'EvnNeonatalSurvey_id', type: 'int', header: 'ID', key: true},
								{name: 'Person_SurName', type: 'string', header: 'Фамилия', width: 100, hidden: true },
								{name: 'Person_FirName', type: 'string', header: 'Имя', width: 100, hidden: true },
								{name: 'Person_SecName', type: 'string', header: 'Отчество', width: 100, hidden: true },
								{name: 'Person_BirthDay', type: 'string', header: 'День рождения', width: 100, hidden: true },
								{name: 'Sex_Code', type: 'string', header: 'Пол', width: 100, hidden: true },
								{name: 'EvnSection_pid', type: 'string', header: 'ИД движения', width: 100, hidden: true },
								{name: 'LpuSection_id', type: 'string', header: 'ИД отделения', width: 100, hidden: true },
								{name: 'Evn_setD', type: 'string', header: 'Дата', width: 75 },
								{name: 'Evn_setT', type: 'string', header: 'Время', width: 50 },
								{name: 'PersonWeight_Weight', header: 'Масса (г)', type: 'string', width: 60},
								{name: 'PersonTemperature', header: langs('Температура'), type: 'string', width: 80},
								{name: 'BreathFrequency', header: 'Частота дыхания', type: 'string', width: 110},
								{name: 'HeartFrequency', header: 'Частота сердечных сокращений', type: 'string', width: 180},
								{name: 'ReanimConditionType_Name', header: 'Состояние', type: 'string', width: 140},
								{name: 'CheckReact', header: 'Реакция на осмотр', type: 'string', width: 130},
								{name: 'MuscleTone', header: 'Мышечный тонус', type: 'string', width: 120},
								{name: 'Oedemata', header: 'Отеки', type: 'string', width: 80},
								{name: 'HeartTones1', header: 'Ритм сердечных тонов', type: 'string', width: 140},
								{name: 'HeartTones2', header: 'Характер сердечных тонов', type: 'string', width: 160},
								{name: 'RemainUmbilCord', header: 'Пуповинный остаток', type: 'string', width: 130},
								{name: 'UmbilicWound', header: 'Пупочная ранка', type: 'string', width: 130}
							]
						})
					]
				}
			]
		});

		if (getRegionNick() != 'ufa') {
			if (this.tabPanel.getComponent('tab_PBSEvnNeonatalSurvey'))
				this.tabPanel.remove(this.tabPanel.getComponent('tab_PBSEvnNeonatalSurvey'));
			if (this.tabPanel.getComponent('tab_PBSObserv'))
				this.tabPanel.remove(this.tabPanel.getComponent('tab_PBSObserv'));
		}else {
			if (this.tabPanel.getComponent('tab_PBSObserv'))
				this.tabPanel.remove(this.tabPanel.getComponent('tab_PBSObserv'));
		}
		
		
		this.FormPanel = new Ext.form.FormPanel({
			region: 'center',
			border:false,
			frame:false,
			//height: 640,
			url: '/?c=PersonNewBorn&m=savePersonNewBorn',

			id:'PBS_PersonNewBornForm',
			isLoaded:false,
			labelWidth:180,
			
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name:'EvnSection_mid'},
				{ name: 'PersonNewBorn_id' },//
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'BirthSpecStac_id' },
				{ name: 'Server_id' },//
				{ name: 'ChildTermType_id' },//
				{ name: 'PersonNewBorn_IsRejection' },//
				{ name: 'PersonNewBorn_CountChild' },//
				{ name: 'PersonNewBorn_IsAidsMother' },//
				{ name: 'PersonNewBorn_BCGNum' },//
				{ name: 'PersonNewBorn_BCGSer' },//
				{ name: 'PersonNewBorn_IsBCG' },//
				{ name: 'FeedingType_id' },//
				{ name: 'ChildPositionType_id' },
				{ name: 'PersonNewborn_BloodBili' },
				{ name: 'PersonNewborn_BloodHemoglo' },
				{ name: 'PersonNewborn_BloodEryth' },
				{ name: 'PersonNewborn_BloodHemato' },
				{ name: 'NewBornWardType_id' },
				{ name: 'PersonNewBorn_BCGDate' },
				{ name: 'PersonNewBorn_HepatitNum' },
				{ name: 'PersonNewBorn_HepatitSer' },
				{ name: 'PersonNewBorn_HepatitDate' },
				{ name: 'PersonNewBorn_IsHepatit' },
				{ name: 'EvnPS_id' },
				{ name: 'PersonNewBorn_IsBleeding' },
				{ name: 'PersonNewBorn_IsNeonatal' },
				{ name: 'PersonNewBorn_IsAudio' },
				{ name: 'PersonNewBorn_Weight' },
				{ name: 'PersonNewBorn_Head' },
				{ name: 'PersonNewBorn_Height' },
				{ name: 'PersonNewBorn_Breast' },
				{ name: 'Person_BirthDay' },
				{ name: 'PersonNewBorn_IsBreath' },
				{ name: 'PersonNewBorn_IsHeart' },
				{ name: 'PersonNewBorn_IsPulsation' },
				{ name: 'PersonNewBorn_IsMuscle' },
				{ name: 'RefuseType_pid' },
				{ name: 'RefuseType_aid' },
				{ name: 'RefuseType_bid' },
				{ name: 'RefuseType_gid' },
			]),
			items:[
			{
				name:'PersonNewBorn_id',
				value:0,
				xtype:'hidden'
			},
			{
				name:'EvnSection_mid',
				value:0,
				xtype:'hidden'
			},
			{
				name:'Person_BirthDay',
				value:0,
				xtype:'hidden'
			},
			{
				name:'EvnPS_id',
				value:0,
				xtype:'hidden'
			},
			{
				name:'Person_id',
				value:0,
				xtype:'hidden'
			},
			{
				name:'PersonEvn_id',
				value:0,
				xtype:'hidden'
			},
			{
				name:'BirthSpecStac_id',
				value:0,
				xtype:'hidden'
			},
			{
				name:'Server_id',
				value:0,
				xtype:'hidden'
			},
			parentWin.tabPanel
			]
		});

		var current_window = this;
		Ext.apply(this, {
			buttons: [
			{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_PSEDEF + 3,
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.onCancelAction();
					this.hide()
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if (!this.buttons[0].hidden) {
						this.buttons[0].focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_PSEDEF + 4,
				text: BTN_FRMCANCEL
			}
			],
			items: [
			this.FormPanel
			],
			layout: 'border',
			keys: [
			{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey()) {
						case Ext.EventObject.C:
							current_window.doSave();
							break;
						case Ext.EventObject.J:
							current_window.hide();
							break;
					}
				},
				key: [
				Ext.EventObject.C,
				Ext.EventObject.J
				],
				scope: this,
				stopEvent: true
			}
			]
		});
		sw.Promed.swPersonBirthSpecific.superclass.initComponent.apply(this, arguments);
	},
	onCancelActionFlag: true,
	onHide: Ext.emptyFn,
	listeners: {
		'beforehide': function(w) {
			
		},
		'hide': function(w) {
			w.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPersonBirthSpecific.superclass.show.apply(this, arguments);
		this.center();
		this.tabPanel.setActiveTab('tab_PBSCommon');
		
		var base_form = this.FormPanel.getForm();
		log(base_form,4444)
		base_form.reset();
		this.isTraumaTabGridLoaded = false;
		this.isObservTabGridLoaded = false;
		this.isNeonatalTabGridLoaded = false;
		this.action = null;
		this.EvnPS_id = null;
		this.BirthSpecStac_id=null;
		this.EvnSection_mid = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.measureTypeExceptions = new Array();
		this.onHide = Ext.emptyFn;
		this.gridRecords = [];
		this.title = "Специфика новорожденного"; 
		var win = this;
		if (!arguments[0] ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {
				this.hide();
			}.createDelegate(this));
			return false;
		}
		if(arguments[0].EvnPS_id){
			this.EvnPS_id=arguments[0].EvnPS_id;
		}
		if(arguments[0].onCancelAction){
			this.onCancelAction=arguments[0].onCancelAction;
		}else{
			this.onCancelAction = Ext.emptyFn;
		}
		if(arguments[0].EvnSection_mid){
			this.EvnSection_mid=arguments[0].EvnSection_mid;
		}
		if(arguments[0].BirthSpecStac_id){
			this.BirthSpecStac_id=arguments[0].BirthSpecStac_id;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		this.BirthDay = null;
		/*var gridH = this.findById('PBS_PersonHeightGrid').getGrid();
		var gridW = this.findById('PBS_PersonWeightGrid').getGrid();
		gridH.getStore().removeAll();
		gridW.getStore().removeAll();*/
		
		var grid1 = this.findById('PBS_PersonBirthTraumaGrid1').getGrid();
		var grid2 = this.findById('PBS_PersonBirthTraumaGrid2').getGrid();
		var grid3 = this.findById('PBS_PersonBirthTraumaGrid3').getGrid();
		var grid4 = this.findById('PBS_PersonBirthTraumaGrid4').getGrid();
		var apgarGrid = this.findById('PBS_NewbornApgarRateGrid').getGrid();
		grid1.getStore().removeAll();
		grid2.getStore().removeAll();
		grid3.getStore().removeAll();
		grid4.getStore().removeAll();
		apgarGrid.getStore().removeAll();
		grid1.getStore().baseParams.BirthTraumaType_id = 1;
		grid2.getStore().baseParams.BirthTraumaType_id = 2;
		grid3.getStore().baseParams.BirthTraumaType_id = 3;
		grid4.getStore().baseParams.BirthTraumaType_id = 4;
		base_form.setValues(arguments[0]);
		this.findById('PBS_BloodBili_Xml').hide();
		this.findById('PBS_BloodHemoglo_Xml').hide();
		this.findById('PBS_BloodEryth_Xml').hide();
		this.findById('PBS_BloodHemato_Xml').hide();

		//this.AmbulatCardLocatGrid.removeAll();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) {
			case 'add':
				base_form.findField('PersonNewBorn_id').setValue('0');
				var values = [];
				var grid = this.findById('PBS_NewbornApgarRateGrid').getGrid();
				var values = [
					{NewbornApgarRate_id:-swGenTempId(grid.getStore()),NewbornApgarRate_Time:1,RecordStatus_Code:0},
					{NewbornApgarRate_id:-swGenTempId(grid.getStore()),NewbornApgarRate_Time:5,RecordStatus_Code:0},
					{NewbornApgarRate_id:-swGenTempId(grid.getStore()),NewbornApgarRate_Time:10,RecordStatus_Code:0},
					{NewbornApgarRate_id:-swGenTempId(grid.getStore()),NewbornApgarRate_Time:15,RecordStatus_Code:0}
				];
				grid.getStore().loadData(values, true);
				var Person_BirthDay = base_form.findField('Person_BirthDay').getValue();
				this.BirthDay = Person_BirthDay;
				base_form.findField('PersonNewBorn_BCGDate').setMinValue(Person_BirthDay);
				base_form.findField('PersonNewBorn_HepatitDate').setMinValue(Person_BirthDay);
				base_form.findField('EvnPS_id').setValue(this.EvnPS_id);
				this.setNewbornBlood();
				this.setTitle(win.title + lang['_dobavlenie']);
				loadMask.hide();
				base_form.clearInvalid();
				break;
			case 'edit':
			case 'view':
				
				var Person_id = base_form.findField('Person_id').getValue();
				
				if ( !Person_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'Person_id': Person_id
					},
					success: function() {
						if ( this.action == 'edit' ) {
							this.setTitle(win.title + lang['_redaktirovanie']);
							this.enableEdit(true);
						}
						else {
							this.setTitle(win.title + lang['_prosmotr']);
							this.enableEdit(false);
						}
						/*grid1.getStore().load({params:{PersonNewBorn_id:base_form.findField('PersonNewBorn_id').getValue()}});
						grid2.getStore().load({params:{PersonNewBorn_id:base_form.findField('PersonNewBorn_id').getValue()}});
						grid3.getStore().load({params:{PersonNewBorn_id:base_form.findField('PersonNewBorn_id').getValue()}});
						grid4.getStore().load({params:{PersonNewBorn_id:base_form.findField('PersonNewBorn_id').getValue()}});*/
						apgarGrid.getStore().load({params:{PersonNewBorn_id:base_form.findField('PersonNewBorn_id').getValue()}});
						/*
						gridW.getStore().load({
							params:{
								mode:'child',
								Person_id:base_form.findField('Person_id').getValue()
							}
						});*/
						loadMask.hide();
						//base_form.findField('EvnPS_id').setValue(this.EvnPS_id);
						this.EvnPS_id = base_form.findField('EvnPS_id').getValue()
						var Person_BirthDay = base_form.findField('Person_BirthDay').getValue();
						this.BirthDay = Person_BirthDay;
						base_form.findField('PersonNewBorn_BCGDate').setMinValue(Person_BirthDay);
						base_form.findField('PersonNewBorn_HepatitDate').setMinValue(Person_BirthDay);
						log(base_form.findField('PersonNewBorn_BCGDate'))
						if(this.BirthSpecStac_id>0){
							base_form.findField('BirthSpecStac_id').setValue(this.BirthSpecStac_id);
						}
						if(this.EvnSection_mid>0){
							base_form.findField('EvnSection_mid').setValue(this.EvnSection_mid);
						}
						this.setNewbornBlood();

						this.setRefuseType('RefuseType_pid');
						this.setRefuseType('RefuseType_aid');
						this.setRefuseType('RefuseType_bid');
						this.setRefuseType('RefuseType_gid');
						base_form.clearInvalid();
					}.createDelegate(this),
					url: '/?c=PersonNewBorn&m=loadPersonNewBornData'
				});
				break;
		}
		
	},
	width: 750
});