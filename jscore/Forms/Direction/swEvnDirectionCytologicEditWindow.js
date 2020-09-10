/**
* swEvnDirectionCytologicEditWindow - Направление на цитологическое диагностическое исследование
* 
* copy swEvnDirectionHistologicEditWindow 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*/

sw.Promed.swEvnDirectionCytologicEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	formStatus: 'edit',
	height: 550,
	id: 'EvnDirectionCytologicEditWindow',
	userMedStaffFact: null,
	callFromEmk: false,
	external_direction: false,
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnDirectionCytologicEditWindow');

			switch ( e.getKey() ) {
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
	}],
	layout: 'border',
	listeners: {
		'beforehide': function(win) {
			// 
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: true,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	plain: true,
	resizable: true,
	width: 750,

	/* методы */
	callback: Ext.emptyFn,
	loadMask: {},
	deleteGridForm: function(gridID) {
		var win = this;

		if ( win.action == 'view' ) {
			return false;
		}
		
		var elem = win.findById(gridID);
		if(elem && elem.object == 'VolumeAndMacroscopicDescription'){
			var key = 'MacroMaterialCytologic_id';
		}else if(elem && elem.object == 'LocalizationNatureProcessAndMethod'){
			var key = 'LocalProcessCytologic_id';
		}else{
			return false;
		}
		var grid = elem.getGrid();
		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || Ext.isEmpty(grid.getSelectionModel().getSelected().get(key)) ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
						break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();
							win.filterGridForm(grid);
						break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить запись?'),
			title: langs('Вопрос')
		});

		return true;
	},
	getInvalidFields: function() {
		//вернет массив, содержащий только те поля, которые были оценены как не валидные
		var invalidFields = [];
		this.FormPanel.getForm().items.items.forEach(function(field) {
			if (field.validate()) return;
			invalidFields.push(field.hiddenName + '-' + field.fieldLabel);
		});
		return invalidFields;
	},
	doSave: function(options) {
		var win = this;
		// options @Object
		// options.print @Boolean Вызывать печать направления , если true

		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			var invalids = this.getInvalidFields();
			if(isDebug()) console.warn('Не валидные поля: ' + invalids.join('; '))
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var params = new Object();
		var gridVolumeAndMacroscopicDescription = this.findById(this.id + 'VolumeAndMacroscopicDescriptionGrid').getGrid();
		var gridLocalizationNatureProcessAndMethod = this.findById(this.id + 'LocalizationNatureProcessAndMethodGrid').getGrid();
		VolumeAndMacroscopicDescriptionData = [];
		LocalizationNatureProcessAndMethodData = [];
		if ( this.findById(this.id + 'VolumeAndMacroscopicDescriptionPanel').isLoaded == true ) {
			gridVolumeAndMacroscopicDescription.getStore().clearFilter();
			
			if ( gridVolumeAndMacroscopicDescription.getStore().getCount() > 0 && !Ext.isEmpty(gridVolumeAndMacroscopicDescription.getStore().getAt(0).get('MacroMaterialCytologic_id')) ) {
				VolumeAndMacroscopicDescriptionData = getStoreRecords(gridVolumeAndMacroscopicDescription.getStore());
				this.filterGridForm(gridVolumeAndMacroscopicDescription);
			}
		}
		if ( this.findById(this.id + 'LocalizationNatureProcessAndMethodPanel').isLoaded == true ) {
			gridLocalizationNatureProcessAndMethod.getStore().clearFilter();
			
			if ( gridLocalizationNatureProcessAndMethod.getStore().getCount() > 0 && !Ext.isEmpty(gridLocalizationNatureProcessAndMethod.getStore().getAt(0).get('LocalProcessCytologic_id')) ) {
				LocalizationNatureProcessAndMethodData = getStoreRecords(gridLocalizationNatureProcessAndMethod.getStore());
				this.filterGridForm(gridLocalizationNatureProcessAndMethod);
			}
		}
		params.VolumeAndMacroscopicDescriptionData = Ext.util.JSON.encode(VolumeAndMacroscopicDescriptionData);
		params.LocalizationNatureProcessAndMethodData = Ext.util.JSON.encode(LocalizationNatureProcessAndMethodData);	

		base_form.findField('MedPersonal_did').setValue(base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'));

		//params.outer = win.outer == true;
		var getArrFieldsDisabled = ['EvnDirectionCytologic_Ser', 'EvnDirectionCytologic_Num', 'LpuSection_did', 'EvnDirectionCytologic_NumKVS', 'EvnDirectionCytologic_NumCard', 'Lpu_did'];
		getArrFieldsDisabled.forEach(function(item, i, arr){
			var elem = base_form.findField(item);
			if(elem && elem.disabled) params[item] = elem.getValue();
		})

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение направления..." });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnDirectionCytologic_id ) {
						var evn_direction_Cytologic_id = action.result.EvnDirectionCytologic_id;

						base_form.findField('EvnDirectionCytologic_id').setValue(evn_direction_Cytologic_id);

						var data = new Object();
						var LpuSection_Name = (base_form.findField('LpuSection_did').isVisible()) ? base_form.findField('LpuSection_did').getFieldValue('LpuSection_Name') : base_form.findField('EvnDirectionCytologic_LpuSectionName').getValue();
						var MedPersonal_Fio = (base_form.findField('MedStaffFact_id').isVisible()) ? base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_Fio') : base_form.findField('EvnDirectionCytologic_MedPersonalFIO').getValue();
						data.evnDirectionCytologicData = {
							'accessType': 'edit',
							'EvnDirectionCytologic_id': base_form.findField('EvnDirectionCytologic_id').getValue(),
							'Person_id': base_form.findField('Person_id').getValue(),
							'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue(),
							'EvnDirectionCytologic_Ser': base_form.findField('EvnDirectionCytologic_Ser').getValue(),
							'EvnDirectionCytologic_Num': base_form.findField('EvnDirectionCytologic_Num').getValue(),
							'EvnDirectionCytologic_setDate': base_form.findField('EvnDirectionCytologic_setDate').getValue(),
							'LpuSection_Name': LpuSection_Name,
							'MedPersonal_Fio': MedPersonal_Fio,
							'EvnDirectionCytologic_NumCard': base_form.findField('EvnDirectionCytologic_NumCard').getValue(),
							'PayType_id': base_form.findField('PayType_id').getValue(),
							'Person_Surname': this.PersonInfo.getFieldValue('Person_Surname'),
							'Person_Firname': this.PersonInfo.getFieldValue('Person_Firname'),
							'Person_Secname': this.PersonInfo.getFieldValue('Person_Secname'),
							'Person_Birthday': this.PersonInfo.getFieldValue('Person_Birthday'),
							'Lpu_Name':  base_form.findField('Lpu_did').getFieldValue('Lpu_Nick'),
							'EvnDirectionCytologic_IsCito': (base_form.findField('EvnDirectionCytologic_IsCito').getValue()) ? 'Да' : 'Нет' 
						};
						this.callback(data);
						if ( options && options.print ) {
							this.buttons[1].focus();
							this.printDirection();
							this.setParamRecordStatusOne(function(){
								loadMask.hide();
							});
						}
						else {
							this.hide();
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}.createDelegate(this)
		});
	},
	filterLastResultsGrid: function() {
		return false;
	},
	filterGridForm: function(grid) {
		if(!grid) return false;
		var store = grid.getStore();	

		store.clearFilter();
		store.filterBy(function(rec) {
			return (Number(rec.get('RecordStatus_Code')) != 3);
		});

		return true;
	},
	setParamRecordStatusOne: function(cb){
		var cb = cb || false;
		var gridVolumeAndMacroscopicDescription = this.findById(this.id + 'VolumeAndMacroscopicDescriptionGrid').getGrid();
		var gridLocalizationNatureProcessAndMethod = this.findById(this.id + 'LocalizationNatureProcessAndMethodGrid').getGrid();
		var arrGrid = [];
		if ( gridVolumeAndMacroscopicDescription.getStore().getCount() > 0 && !Ext.isEmpty(gridVolumeAndMacroscopicDescription.getStore().getAt(0).get('MacroMaterialCytologic_id')) ) {
			arrGrid.push(gridVolumeAndMacroscopicDescription);
		}
		if ( gridLocalizationNatureProcessAndMethod.getStore().getCount() > 0 && !Ext.isEmpty(gridLocalizationNatureProcessAndMethod.getStore().getAt(0).get('LocalProcessCytologic_id')) ) {
			arrGrid.push(gridLocalizationNatureProcessAndMethod);
		}
		/*
		var arrGrid = [
			this.findById(this.id + 'VolumeAndMacroscopicDescriptionGrid').getGrid(), 
			this.findById(this.id + 'LocalizationNatureProcessAndMethodGrid').getGrid()
		];
		*/
		arrGrid.forEach(function(item, i, arr){
			if(item.getStore()) item.getStore().reload();
		}, this);
		if(cb && typeof cb == 'function') cb();
	},
	onHide: Ext.emptyFn,
	openEvnPSListWindow: function() {
		var base_form = this.FormPanel.getForm();

		if ( base_form.findField('EvnDirectionCytologic_NumCard').disabled ) {
			return false;
		}

		if ( getWnd('swEvnPSListWindow').isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Окно просмотра списка КВС уже открыто'));
			return false;
		}

		var params = new Object();

		params.callback = function(data) {
			if ( !data ) {
				return false;
			}
			/*
			base_form.findField('UslugaComplex_id').getStore().load({
				callback: function() {
					// if ( base_form.findField('UslugaComplex_id').getStore().getCount() == 1 ) {
					// 	base_form.findField('UslugaComplex_id').setValue(base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id'));
					// }
				},
				params: {
					EvnPS_id:data.EvnPS_id
				}
			});
			*/
			base_form.findField('EvnPS_id').setValue(data.EvnPS_id);
			// base_form.findField('EvnDirectionCytologic_NumCard').setValue(data.EvnPS_NumCard);
			base_form.findField('EvnDirectionCytologic_NumKVS').setValue(data.EvnPS_NumCard);
		}.createDelegate(this);
		params.onHide = function() {
			base_form.findField('EvnDirectionCytologic_NumCard').focus();
		}.createDelegate(this);
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');

		getWnd('swEvnPSListWindow').show(params);
	},
	openEditWindow: function(action, form){
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) || !form) {
			return false;
		}
		var base_form = this.FormPanel.getForm();

		if(form == 'swDescriptionOfDiologicalMaterialEditWindow'){
			var msgOpenForm = langs('Окно редактирования описания биологического материала уже открыто');
			var gridID = this.id + 'VolumeAndMacroscopicDescriptionGrid';
			var keyID = 'MacroMaterialCytologic_id';
		}else if(form == 'swLocalizationNatureProcessEditWindow'){
			var msgOpenForm = langs('Окно редактирования локализации, характера процесса уже открыто');
			var gridID = this.id + 'LocalizationNatureProcessAndMethodGrid';
			var keyID = 'LocalProcessCytologic_id';
		}else{
			return false;
		}

		if ( getWnd(form).isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), langs(msgOpenForm));
			return false;
		}

		var
			formParams = new Object(),
			grid = this.findById(gridID).getGrid(),
			params = new Object(),
			win = this;

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.Data != 'object' ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют необходимые данные'));
				return false;
			}

			var dodmData =  data.Data;
			dodmData.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) {
				return (rec.get(keyID) == dodmData[keyID]);
			});

			if ( index == -1 ) {
				dodmData[keyID] = -swGenTempId(grid.getStore());
			}

			if ( index >= 0 ) {
				var record = grid.getStore().getAt(index);

				if ( record.get('RecordStatus_Code') == 1 ) {
					dodmData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], dodmData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && Ext.isEmpty(grid.getStore().getAt(0).get(keyID)) ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ dodmData ], true);
			}

			return true;
		};

		params.formMode = 'local';

		if ( action == 'add' ) {
			params.formParams = formParams;
			params.formParams.EvnDirectionCytologic_id = base_form.findField('EvnDirectionCytologic_id').getValue();
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(keyID) ) {
				return false;
			}

			var selectedRecord = grid.getSelectionModel().getSelected();

			formParams = selectedRecord.data;	
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.formParams = formParams;
		getWnd(form).show(params);

		return true;
	},	
	printEvnDirectionCytologic: function() {
		switch ( this.action ) {
			case 'add':
			case 'edit':
				this.doSave({
					print: true
				});
			break;

			case 'view':
				this.printDirection();
			break;
		}
	},
	printDirection: function(){
		var evn_direction_Cytologic_id = this.FormPanel.getForm().findField('EvnDirectionCytologic_id').getValue();
		if(!evn_direction_Cytologic_id) return false;
		printBirt({
			'Report_FileName': 'f203u02_Directioncytologic.rptdesign',
			'Report_Params': '&paramEvnDirectioncytologic=' + evn_direction_Cytologic_id,
			'Report_Format': 'pdf'
		});
	},
	setEvnDirectionCytologicSer: function(){
		var win = this;
		var base_form = this.FormPanel.getForm();
		var lpu_id = Ext.globalOptions.globals.lpu_id;

		var lpu_store = new Ext.db.AdapterStore({
			autoLoad: false,
			dbFile: 'Promed.db',
			fields: [
				{ name: 'Lpu_id', type: 'int' },
				{ name: 'Lpu_Ouz', type: 'int' },
				{ name: 'Lpu_RegNomC2', type: 'int' },
				{ name: 'Lpu_RegNomN2', type: 'int' }
			], 
			key: 'Lpu_id',
			tableName: 'Lpu'
		});

		lpu_store.load({
			callback: function(records, options, success) {
				var serial = '';

				for ( var i = 0; i < records.length; i++ ) {
					if ( records[i].get('Lpu_id') == lpu_id ) {
						serial = records[i].get('Lpu_Ouz');
					}
				}

				base_form.findField('EvnDirectionCytologic_Ser').setValue(serial);
			}
		});
	},
	setEvnDirectionCytologicNumber: function() {
		var base_form = this.FormPanel.getForm();

		this.loadMask.show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				this.loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj.EvnDirectionCytologic_Num && response_obj.Numerator_id){
						base_form.findField('EvnDirectionCytologic_Num').setValue(response_obj.EvnDirectionCytologic_Num);
						base_form.findField('EvnDirectionCytologic_Ser').setValue(response_obj.EvnDirectionCytologic_Ser); // ??????????
						if(response_obj.Numerator_id) base_form.findField('Numerator_id').setValue(response_obj.Numerator_id);
					}else{
						var msgTxt = (response_obj.Error_Msg) ? response_obj.Error_Msg : 'Ошибка при определении номера направления';
						sw.swMsg.alert(langs('Ошибка'), msgTxt);
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении номера направления'), function() { base_form.findField('EvnDirectionCytologic_Num').focus(true); }.createDelegate(this) );
				}
			}.createDelegate(this),
			failure: function(response, opts){
				this.loadMask.hide();
				Ext.Msg.alert('Ошибка','Ошибка при получении номера направления');
			}.createDelegate(this),
			url: '/?c=EvnDirectionCytologic&m=getEvnDirectionCytologicNumber'
		});
	},
	collapsePanels: function(){
		var win = this;
		var arrPanels = ['ProcessingResults', 'LocalizationNatureProcessAndMethod', 'VolumeAndMacroscopicDescription'];
		arrPanels.forEach(function(item, i, arr){
			var elemPanel = win.findById(win.id + item + 'Panel');
			var elemGrid = win.findById(win.id + item + 'Grid');
			if(elemPanel){
				elemPanel.collapse();
				elemPanel.isLoaded = false;
			}
			if(elemGrid) elemGrid.removeAll();
		});
	},
	enableFields: function(){
		var base_form = this.FormPanel.getForm();
		var fieldNumKVS = base_form.findField('EvnDirectionCytologic_NumKVS');
		var fieldNumCard = base_form.findField('EvnDirectionCytologic_NumCard');
		var pid = base_form.findField('EvnDirectionCytologic_pid').getValue();
		var flag = (parseInt(pid)) ? false : true;

		fieldNumKVS.setContainerVisible(flag);
		fieldNumCard.setContainerVisible(flag);
		this.enable_LpuSectionName_MedPersonalFIO();
	},
	enable_LpuSectionName_MedPersonalFIO: function(){
		if(!this.external_direction) return false;
		var base_form = this.FormPanel.getForm();

		var comboLpuSID = base_form.findField('Lpu_sid');
		var LpuInSystem = comboLpuSID.getFieldValue('Lpu_IsNotForSystem') != '2';
		var lpuSectionCombo = base_form.findField('LpuSection_did');
		var medStaffFactCombo = base_form.findField('MedStaffFact_id');
		var lpuSectionNameText = base_form.findField('EvnDirectionCytologic_LpuSectionName');
		var medPersonalFioText = base_form.findField('EvnDirectionCytologic_MedPersonalFIO');

		lpuSectionCombo.setAllowBlank(!LpuInSystem);
		medStaffFactCombo.setAllowBlank(!LpuInSystem);
		lpuSectionNameText.setAllowBlank(LpuInSystem);
		medPersonalFioText.setAllowBlank(LpuInSystem);

		if (LpuInSystem) {
			lpuSectionCombo.showContainer();
			medStaffFactCombo.showContainer();
			lpuSectionNameText.hideContainer();
			medPersonalFioText.hideContainer();
			lpuSectionNameText.setValue('');
			medPersonalFioText.setValue('');
		} else {
			lpuSectionCombo.hideContainer();
			medStaffFactCombo.hideContainer();
			lpuSectionCombo.clearValue();
			medStaffFactCombo.clearValue();
			lpuSectionNameText.showContainer();
			medPersonalFioText.showContainer();
		}
	},
	clearComboUslugaComplex: function(){
		//my
		var base_form = this.FormPanel.getForm();
		var comboUslugaComplex = base_form.findField('UslugaComplex_id');

		comboUslugaComplex.clearValue();
		comboUslugaComplex.getStore().removeAll();
	},
	setComboUslugaComplex: function(){
		var base_form = this.FormPanel.getForm();
		var comboUslugaComplex = base_form.findField('UslugaComplex_id');
		var comboUslugaCategory = base_form.findField('UslugaCategory_id');

		this.clearComboUslugaComplex();
		if(comboUslugaCategory.getValue() && comboUslugaCategory.getFieldValue('UslugaCategory_SysNick')){
			comboUslugaComplex.setAllowedUslugaComplexAttributeList(['cytology']);
			comboUslugaComplex.setUslugaCategoryList([comboUslugaCategory.getFieldValue('UslugaCategory_SysNick')]);
		}
	},
	setDefaultCombo_LpuSectionMedStaffFact: function(){
		var win = this;
		var base_form = this.FormPanel.getForm();
		var comboLpuSection = base_form.findField('LpuSection_did');
		var comboMedStaffFact = base_form.findField('MedStaffFact_id');
		if ( !win.external_direction && win.action == 'add' && win.userMedStaffFact ) {
			if(win.userMedStaffFact.MedStaffFact_id /*&& comboMedStaffFact.getStore().getById(win.userMedStaffFact.MedStaffFact_id)*/){
				comboMedStaffFact.setValue(win.userMedStaffFact.MedStaffFact_id);
			}
			if(win.userMedStaffFact.LpuSection_id /*&& comboMedStaffFact.getStore().getById(win.userMedStaffFact.LpuSection_id)*/){
				comboLpuSection.setValue(win.userMedStaffFact.LpuSection_id);
			}
		}
	},
	setMedStaffFactID: function(){
		var base_form = this.FormPanel.getForm();
		var comboLpuSection = base_form.findField('LpuSection_did');
		var comboMedStaffFact = base_form.findField('MedStaffFact_id');
		var lpu_section_did = comboLpuSection.getValue();
		var med_personal_id = base_form.findField('MedPersonal_did').getValue();
		if(!lpu_section_did || !med_personal_id) return false;
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		//loadMask.show();
		this.loadMask.show();
		index = comboMedStaffFact.getStore().findBy(function(record, id) {
			if ( record.get('LpuSection_id') == lpu_section_did && record.get('MedPersonal_id') == med_personal_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			comboMedStaffFact.setValue(comboMedStaffFact.getStore().getAt(index).get('MedStaffFact_id'));
		}
		// loadMask.hide();
		this.loadMask.hide();
	},
	loadCombo_LpuSectionMedStaffFact: function(cb){
		var cb = cb || null;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var comboLpuSection = base_form.findField('LpuSection_did');
		var comboMedStaffFact = base_form.findField('MedStaffFact_id');
		var dateValue = base_form.findField('EvnDirectionCytologic_setDate').getValue();
		var lpu_section_id = comboLpuSection.getValue();
		var med_staff_fact_id = comboMedStaffFact.getValue();
		var Lpu_id = (win.external_direction && this.action == 'add') ? base_form.findField('Lpu_sid').getValue() : (base_form.findField('Lpu_did').getValue() ? base_form.findField('Lpu_did').getValue() : getGlobalOptions().lpu_id);
		if(!dateValue || !Lpu_id || this.action == 'view') {
			comboLpuSection.disable();
			comboMedStaffFact.disable();
			if(!dateValue || !Lpu_id) {
				comboLpuSection.clearValue();
				comboMedStaffFact.clearValue();
				return false;
			}
		}else{			
			comboLpuSection.enable();
			comboMedStaffFact.enable();
		}		
		
		comboLpuSection.getStore().removeAll();
		//this.loadMask.show();
		comboLpuSection.getStore().load({
			params: {
				Lpu_id: Lpu_id,
				date: Ext.util.Format.date(dateValue, 'd.m.Y'),
				mode: 'combo'
			},
			callback: function(){
				var id = lpu_section_id;
				if(Ext.isEmpty(id)) id = comboLpuSection.getValue();
				if (id && comboLpuSection.getStore().getById(id) ) {
					comboLpuSection.setValue(id);
				}else{
					comboLpuSection.clearValue();
				}
				//this.loadMask.hide();
			}.bind(this)
		});
		
		comboMedStaffFact.getStore().removeAll();
		this.loadMask.show();
		comboMedStaffFact.getStore().load({
			params: {
				Lpu_id: Lpu_id,
				onDate: Ext.util.Format.date(dateValue, 'd.m.Y'),
				mode: 'combo'
			},
			callback: function(){
				if(comboLpuSection.getValue()){
					comboLpuSection.fireEvent('change', comboLpuSection, comboLpuSection.getValue());
				}
				var id = med_staff_fact_id;
				if(Ext.isEmpty(id)) id = comboMedStaffFact.getValue();
				if (id && comboMedStaffFact.getStore().getById(id) ) {
					comboMedStaffFact.setValue(id);
				}else{
					comboMedStaffFact.clearValue();
				}
				this.loadMask.hide();
				if(cb && typeof cb == 'function') cb();
			}.bind(this)
		});
	},
	loadLpuSection: function(cb){
		var cb = cb || null;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var comboLpuSection = base_form.findField('LpuSection_did');
		var dateValue = base_form.findField('EvnDirectionCytologic_setDate').getValue();

		var lpu_section_id = comboLpuSection.getValue();
		comboLpuSection.clearValue();

		if ( !dateValue ) {
			comboLpuSection.disable();
			return false;
		}

		var lpu_section_filter_params = {
			onDate: Ext.util.Format.date(dateValue, 'd.m.Y'),
			regionCode: getGlobalOptions().region.number
		};

		if ( this.action == 'view' ) {
			comboLpuSection.disable();
		}else{
			comboLpuSection.enable();
		}
		comboLpuSection.getStore().removeAll();
		if ( win.action == 'add' && win.userMedStaffFact && win.userMedStaffFact.LpuSection_id ) {
			// фильтр или на конкретное место работы или на список мест работы
			lpu_section_filter_params.id = win.userMedStaffFact.LpuSection_id;
		}
		// загружаем локальные списки отделений
		setLpuSectionGlobalStoreFilter(lpu_section_filter_params);
		comboLpuSection.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		if ( comboLpuSection.getStore().getById(lpu_section_id) ) {
			comboLpuSection.setValue(lpu_section_id);
		}
		else if ( !Ext.isEmpty(lpu_section_filter_params.id) ) {
			comboLpuSection.setValue(lpu_section_filter_params.id);
		}

		if(cb && typeof cb == 'function') cb();
	},
	loadMedStaffFact: function(cb){
		var cb = cb || null;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var comboMedStaffFact = base_form.findField('MedStaffFact_id');
		var dateValue = base_form.findField('EvnDirectionCytologic_setDate').getValue();
		var med_staff_fact_id = comboMedStaffFact.getValue();
		comboMedStaffFact.clearValue();

		if ( !dateValue ) {
			comboMedStaffFact.disable();
			return false;
		}
		var medstafffact_filter_params = {
			// isStacAndPolka: true,
			onDate: Ext.util.Format.date(dateValue, 'd.m.Y'),
			regionCode: getGlobalOptions().region.number
		};
		comboMedStaffFact.getStore().removeAll();
		if ( this.action == 'view' ) {
			comboMedStaffFact.disable();
		}else{
			comboMedStaffFact.enable();
		}
		if ( win.action == 'add' && win.userMedStaffFact && win.userMedStaffFact.MedStaffFact_id ) {
			// фильтр или на конкретное место работы или на список мест работы
			medstafffact_filter_params.id = win.userMedStaffFact.MedStaffFact_id;
		}
		// загружаем локальные списки мест работы
		setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
		comboMedStaffFact.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		if ( comboMedStaffFact.getStore().getById(med_staff_fact_id) ) {
			comboMedStaffFact.setValue(med_staff_fact_id);
		}
		else if ( !Ext.isEmpty(medstafffact_filter_params.id) ) {
			comboMedStaffFact.setValue(medstafffact_filter_params.id);
		}

		if(cb && typeof cb == 'function') cb();
	},
	MaskConstr: function(elem){
		//constructor
		this.count = 0;
		this.callerName = [];
		this.m = new Ext.LoadMask(elem.getEl(), { msg: LOAD_WAIT });
		this.hide = function(){
			var callerName  = arguments.callee.caller.name;
			this.count--;
			if(this.count <= 0) {
				this.callerName = [];
				this.m.hide();
				var form_window = Ext.getCmp('EvnDirectionCytologicEditWindow');
				if(form_window.action == 'add'){
					var base_form = form_window.FormPanel.getForm();
					base_form.isValid()
				}
			}
		}
		this.show = function(){
			if(this.count == 0) this.m.show();
			if( this.callerName.indexOf(arguments.callee.caller.name) < 0 ) {
				this.callerName.push(arguments.callee.caller.name);
				this.count++;
			}
		}
	},
	setActionPanels: function(){
		var arrID = ['LocalizationNatureProcessAndMethodGrid', 'VolumeAndMacroscopicDescriptionGrid'];
		var win = this;
		arrID.forEach(function(item, i, arr){
			var panel = win.findById(win.id + item);
			
			panel.setActionDisabled('action_add', win.action == 'view');
			panel.setActionDisabled('action_edit', win.action == 'view');
			panel.setActionDisabled('action_delete', win.action == 'view');
		});
	},
	loadUslugaList: function(){
		var base_form = this.FormPanel.getForm();
		var EvnDirectionCytologic_pid = base_form.findField('EvnDirectionCytologic_pid').getValue();
		var MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		if(!EvnDirectionCytologic_pid || !MedPersonal_id) return false;
		this.loadMask.show();
		Ext.Ajax.request({
			url: '/?c=EvnDirectionCytologic&m=loadUslugaList',
			params: {
				EvnDirectionCytologic_pid: EvnDirectionCytologic_pid,
				MedPersonal_id: MedPersonal_id
			},
			success: function(response, opts){
				var response_obj = Ext.util.JSON.decode(response.responseText);
				this.loadMask.hide();
				var win = this;
				var oper = []; var ray = []; var onkochem = [];
				if(response_obj[0]){
					if(response_obj[0]['oper'] && response_obj[0]['oper']){
						//оперативное дечение
						response_obj[0]['oper'].forEach(function(item, i, arr){
							var strOper = '';
							if(item.EvnUslugaOper_setDate) strOper += ' '+item.EvnUslugaOper_setDate;
							if(item.Usluga_Name) strOper += ' || '+item.Usluga_Name;
							if(item.OperType_Name) strOper += ' || '+item.OperType_Name;
							oper.push(strOper);
						});
					}
					if(response_obj[0]['ray'] && response_obj[0]['ray']){
						//лучевое лечение
						response_obj[0]['ray'].forEach(function(item, i, arr){
							var strRay = '';
							if(item.EvnUsluga_setDate) strRay += ' '+item.EvnUsluga_setDate;
							if(item.OnkoUslugaBeamIrradiationType_Name) strRay += ' || '+item.OnkoUslugaBeamIrradiationType_Name;
							if(item.OnkoUslugaBeamFocusType_Name) strRay += ' || '+item.OnkoUslugaBeamFocusType_Name;
							if(item.Usluga_Name) strRay += ' || '+item.Usluga_Name;
							if(item.EvnUslugaOnkoBeam_TotalDoseTumor) strRay += ' || Суммарная доза облучения опухоли: '+item.EvnUslugaOnkoBeam_TotalDoseTumor;
							if(item.EvnUslugaOnkoBeam_TotalDoseRegZone) strRay += ' || Суммарная доза облучения зон регионального метастазирования: '+item.EvnUslugaOnkoBeam_TotalDoseRegZone;
							ray.push(strRay);
						});
					}
					if(response_obj[0]['onkochem'] && response_obj[0]['onkochem']){
						//химиотерапия
						response_obj[0]['onkochem'].forEach(function(item, i, arr){
							var strChem = '';
							if(item.EvnUsluga_setDate) strChem += ' '+item.EvnUsluga_setDate;
							if(item.OnkoUslugaChemKindType_Name) strChem += ' || '+item.OnkoUslugaChemKindType_Name;
							if(item.OnkoUslugaChemFocusType_Name) strChem += ' || '+item.OnkoUslugaChemFocusType_Name;
							if(item['prep']){
								var prepList = item['prep'];
								var prepText = '';
								for (var i = 0; i < prepList.length; i++) {
									if(prepList[i]['OnkoDrug_Name']) prepText += ' ' + prepList[i]['OnkoDrug_Name'];
								}
								if(prepText) strChem += ' || Препарат: ' + prepText;
							}
							onkochem.push(strChem);
						});
					}

					if(oper.length>0) base_form.findField('EvnDirectionCytologic_OperTherapy').setValue(oper.join(' ; '));
					if(ray.length>0) base_form.findField('EvnDirectionCytologic_RadiationTherapy').setValue(ray.join(' ; '));
					if(onkochem.length>0) base_form.findField('EvnDirectionCytologic_ChemoTherapy').setValue(onkochem.join(' ; '));
				}
			}.createDelegate(this),
			failure: function(response, opts){
				this.loadMask.hide();
				Ext.Msg.alert('Ошибка','Ошибка при получении данных по улугам случая лечения');
			}.createDelegate(this)
		});
	},
	setExternalDirection: function(){
		var base_form = this.FormPanel.getForm();
		if(this.external_direction){
			Ext.getCmp('generate_SeriesNumber').onHide();
			if(this.action != 'view') base_form.findField('EvnDirectionCytologic_Num').enable();
			if(this.action != 'view') base_form.findField('EvnDirectionCytologic_Ser').enable();
			base_form.findField('Lpu_did').disable();
			base_form.findField('Lpu_sid').showContainer();
			base_form.findField('Lpu_sid').setAllowBlank(false);
		}else{
			if(this.action == 'add') Ext.getCmp('generate_SeriesNumber').onShow();
			base_form.findField('EvnDirectionCytologic_Num').disable();
			base_form.findField('EvnDirectionCytologic_Ser').disable();
			if(this.action != 'view') base_form.findField('Lpu_did').enable();
			base_form.findField('Lpu_sid').hideContainer();
			base_form.findField('Lpu_sid').setAllowBlank(true);
		}
	},
	show: function() {
		var win = this;
		sw.Promed.swEvnDirectionCytologicEditWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.collapsePanels();
		base_form.findField('LpuSection_did').disable();
		base_form.findField('MedStaffFact_id').disable();
		base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList(['cytology']);
		
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		this.userMedStaffFact = (arguments[0].curentMedStaffFactByUser) ? arguments[0].curentMedStaffFactByUser : sw.Promed.MedStaffFactByUser.current;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.external_direction = ((arguments[0].external_direction)) ? true : false; // внешнее направление

		if( arguments[0].formParams.PersonEvn_id ){
			base_form.findField('PersonEvn_id').setValue(arguments[0].formParams.PersonEvn_id);
		}
		if( arguments[0].formParams.Person_id ){
			base_form.findField('Person_id').setValue(arguments[0].formParams.Person_id);			
		}
		if( arguments[0].formParams.Server_id ){
			base_form.findField('Server_id').setValue(arguments[0].formParams.Server_id);			
		}
		if( this.userMedStaffFact.LpuSection_id ){
			base_form.findField('LpuSection_id').setValue(this.userMedStaffFact.LpuSection_id);
		}
		if( this.userMedStaffFact.MedPersonal_id ){
			base_form.findField('MedPersonal_id').setValue(this.userMedStaffFact.MedPersonal_id);
		}

		if(arguments[0].callFromEmk){
			this.callFromEmk = true;
		}

		base_form.setValues(arguments[0].formParams);

		this.PersonInfo.setTitle('...');
		this.PersonInfo.load({
			callback: function() {
				this.PersonInfo.setPersonTitle();
			}.createDelegate(this),
			Person_id: base_form.findField('Person_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue()
		});

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		// var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		// loadMask.show();
		this.loadMask = new this.MaskConstr(this);
		this.loadMask.show();

		// по умолчанию показываем поля для направление из МО работающей в системе
		base_form.findField('LpuSection_did').showContainer();
		base_form.findField('LpuSection_did').setAllowBlank(false);
		base_form.findField('MedStaffFact_id').showContainer();
		base_form.findField('MedStaffFact_id').setAllowBlank(false);
		base_form.findField('Lpu_sid').hideContainer();
		base_form.findField('Lpu_sid').setAllowBlank(true);
		base_form.findField('EvnDirectionCytologic_LpuSectionName').hideContainer();
		base_form.findField('EvnDirectionCytologic_LpuSectionName').setAllowBlank(true);
		base_form.findField('EvnDirectionCytologic_MedPersonalFIO').hideContainer();
		base_form.findField('EvnDirectionCytologic_MedPersonalFIO').setAllowBlank(true);
		base_form.findField('EvnDirectionCytologic_LpuSectionName').setValue('');
		base_form.findField('EvnDirectionCytologic_MedPersonalFIO').setValue('');
		var ms_combo = base_form.findField('MedService_id');
		ms_combo.clearValue();
		ms_combo.getStore().removeAll();
		base_form.findField('MedServiceType_id').setAllowBlank(false);

		this.setActionPanels();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_CYTOLOGIC_EDCWFADD);
				this.enableEdit(true);
				this.buttons[2].hide();

				Ext.getCmp('generate_SeriesNumber').onShow();

				this.clearComboUslugaComplex();
				if(base_form.findField('UslugaCategory_id').getStore().getById(4)) base_form.findField('UslugaCategory_id').setValue(4);
				this.setComboUslugaComplex();
				
				// Генерируем серию направления 	
				//win.setEvnDirectionCytologicSer();

				// Получаем номер направления
				//if( !win.outer ) this.setEvnDirectionCytologicNumber();				

				base_form.findField('Lpu_did').setValue(getGlobalOptions().lpu_id);

				setCurrentDateTime({
					callback: function() {
						win.loadMask.hide();
						win.loadCombo_LpuSectionMedStaffFact(function(){
							this.win.setDefaultCombo_LpuSectionMedStaffFact();	
						}.bind({loadMask: win.loadMask, win: win}))

						base_form.clearInvalid();
						base_form.findField('EvnDirectionCytologic_Num').focus(true, 250);
						base_form.findField('EvnDirectionCytologic_MaterialDT').setValue(Ext.globalOptions.globals.date);
					}.createDelegate(this),
					dateField: base_form.findField('EvnDirectionCytologic_setDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					windowId: this.id
				});

				if ( !Ext.isEmpty(arguments[0].formParams.Diag_id) ) {
					var diag_id = arguments[0].formParams.Diag_id;

					base_form.findField('Diag_id').getStore().load({
						callback: function() {
							base_form.findField('Diag_id').getStore().each(function (rec) {
								if ( rec.get('Diag_id') == diag_id ) {
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
								}
							});
						},
						params: {
							where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
						}
					});
				}
				this.enableFields();
				this.loadUslugaList();

				var comboPayType = base_form.findField('PayType_id');
				var omsPayType = comboPayType.findRecord('PayType_Name', 'ОМС');
				if(omsPayType) comboPayType.setValue(omsPayType.get('PayType_id'));
				this.setExternalDirection();
				break;

			case 'edit':
			case 'view':
				var evn_direction_Cytologic_id = base_form.findField('EvnDirectionCytologic_id').getValue();

				if ( !evn_direction_Cytologic_id ) {
					win.loadMask.hide();
					this.hide();
					return false;
				}

				Ext.getCmp('generate_SeriesNumber').onHide();

				base_form.load({
					failure: function() {
						win.loadMask.hide();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnDirectionCytologic_id': evn_direction_Cytologic_id
					},
					success: function(form, act) {
						var response_obj = Ext.util.JSON.decode(act.response.responseText);

						if (response_obj[0].accessType == 'view') {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_CYTOLOGIC_EDHWFEDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_CYTOLOGIC_EDHWFVIEW);
							this.enableEdit(false);
						}

						if( !Ext.isEmpty(base_form.findField('Lpu_sid').getValue()) ) {
							win.external_direction = true;
							//base_form.findField('Lpu_sid').fireEvent('change', base_form.findField('Lpu_sid'));
						}
						this.setExternalDirection();
						/*
						if ( !base_form.findField('EvnCytologicProto_id').getValue() ) {
							this.buttons[2].hide();
						}
						else {
							this.buttons[2].show();
						}
						*/
						this.enableFields();
						if ( this.action == 'edit' ) {
							setCurrentDateTime({
								dateField: base_form.findField('EvnDirectionCytologic_setDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: this.id
							});
						}
						var
							diag_id = response_obj[0].Diag_id,
							index,
							lpu_aid = response_obj[0].Lpu_aid,
							lpu_section_did = response_obj[0].LpuSection_did,
							med_personal_id = response_obj[0].MedPersonal_did,
							record,
							cito =  (response_obj[0].EvnDirectionCytologic_IsCito == 2) ? true : false,
							uslugacomplex_id = response_obj[0].UslugaComplex_id;

						base_form.findField('EvnDirectionCytologic_IsCito').setValue(cito);

						var uslugacomplex_combo = base_form.findField('UslugaComplex_id');
						if (!Ext.isEmpty(uslugacomplex_id)) {
							uslugacomplex_combo.getStore().load({
								callback: function() {
									uslugacomplex_combo.getStore().each(function(record) {
										if (record.data.UslugaComplex_id == uslugacomplex_id)
										{
											uslugacomplex_combo.setValue(uslugacomplex_id);
											//uslugacomplex_combo.fireEvent('select', uslugacomplex_combo, record, 0);
											uslugacomplex_combo.collapse();
											//uslugacomplex_combo.focus(true);
										}
									});
								},
								params: { "UslugaComplex_id": uslugacomplex_id }
							});
						}

						win.loadCombo_LpuSectionMedStaffFact(function(){
							this.setMedStaffFactID();
						}.bind(win));

						if ( !Ext.isEmpty(diag_id) ) {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function (rec) {
										if ( rec.get('Diag_id') == diag_id ) {
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
										}
									});
								},
								params: {
									where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
								}
							});
						}

						var mst_combo = base_form.findField('MedServiceType_id');
						mst_combo.fireEvent('change', mst_combo, mst_combo.getValue());

						win.loadMask.hide();

						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnDirectionCytologic_Num').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnDirectionCytologic&m=loadEvnDirectionCytologicEditForm'
				});
			break;

			default:
				win.loadMask.hide();
				this.hide();
			break;
		}
	},

	/* конструктор */
	initComponent: function() {
		var
			panelID = 1,
			formTabIndex = TABINDEX_EDHEF,
			win = this;

		win.BiopsyStudyTypeBodyPanel = new Ext.Panel({
			layout: 'form',
			autoHeight: true,
			border: false,
			items: []
		});		
		win.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnDirectionCytologicEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			listeners: {
				'afterlayout': function(panel) {
					//...
				}
			},
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'Numerator_id'},
				{ name: 'EvnDirectionCytologic_id' },
				{ name: 'EvnDirectionCytologic_pid'},
				{ name: 'EvnDirectionCytologic_Ser' },
				{ name: 'EvnDirectionCytologic_Num' },
				{ name: 'EvnDirectionCytologic_setDate' },
				{ name: 'EvnDirectionCytologic_setTime' },
				{ name: 'EvnDirectionCytologic_didDate' },
				{ name: 'Lpu_sid' }, //направившее ЛПУ
				{ name: 'Lpu_did' }, //лпу куда направили
				{ name: 'LpuSection_did' }, //отделение куда направили
				{ name: 'LpuSection_id'}, 	//направившее отделение
				{ name: 'MedPersonal_id' }, //направивший врач
				{ name: 'EvnDirectionCytologic_MedPersonalFIO' }, // текстовое поле
				{ name: 'EvnDirectionCytologic_LpuSectionName' }, // текстовое поле
				{ name: 'MedPersonal_did' }, //врач кому направили
				{ name: 'EvnDirectionCytologic_IsFirstTime'}, //Тип направления
				{ name: 'EvnDirectionCytologic_IsCito'},
				{ name: 'PayType_id'}, //вид оплаты
				{ name: 'EvnPS_id' },
				{ name: 'EvnDirectionCytologic_NumKVS'}, //номер КВС
				{ name: 'EvnDirectionCytologic_NumCard'}, //номер амбулаторной карты ({ name: 'PersonCard_Code'},)
				{ name: 'BiopsyReceive_id'},
				{ name: 'EvnDirectionCytologic_MaterialDT'},
				{ name: 'UslugaCategory_id'}, // категория услуги
				{ name: 'UslugaComplex_id'},
				{ name: 'Diag_id' },
				{ name: 'EvnDirectionCytologic_ClinicalDiag'},
				{ name: 'EvnDirectionCytologic_Anamnes'},
				{ name: 'EvnDirectionCytologic_GynecologicAnamnes'},
				{ name: 'EvnDirectionCytologic_Data'}, //данные о проведенных исследованиях
				{ name: 'EvnDirectionCytologic_OperTherapy'},
				{ name: 'EvnDirectionCytologic_RadiationTherapy'},
				{ name: 'EvnDirectionCytologic_ChemoTherapy'},
				{ name: 'MedService_id'},
				{ name: 'MedServiceType_id'},
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' }
			]),
			region: 'center',
			url: '/?c=EvnDirectionCytologic&m=saveEvnDirectionCytologic',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionCytologic_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'EvnDirectionCytologic_pid',
				xtype: 'hidden'
			}, {
				name: 'EvnPS_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			},  {
				name: 'MedPersonal_did',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'LpuSection_id',
				value: 0,
				xtype: 'hidden'
			},
			{
				name: 'Numerator_id',
				value: 0,
				xtype: 'hidden'
			},
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: win.id + 'DirectionPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						
					}
				},
				style: 'margin-bottom: 0.5em;',
				title: (panelID++) + '. ' + langs('Направление'),
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							disabled: true,
							fieldLabel: langs('Серия, номер направления'),
							name: 'EvnDirectionCytologic_Ser',
							tabIndex: formTabIndex++,
							width: 100,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							allowBlank: false,
							disabled: true,
							enableKeyEvents: true,
							fieldLabel: '',
							labelSeparator: '',
							listeners: {
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
										e.stopEvent();
										win.buttons[win.buttons.length - 1].focus();
									}
								}
							},
							name: 'EvnDirectionCytologic_Num',
							tabIndex: formTabIndex++,
							width: 100,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						layout: 'column',
						width: 100,
						bodyStyle:'padding-left: 10px;',
						items: [{
							handler: function() {
								win.setEvnDirectionCytologicNumber();
							},
							icon: 'img/icons/add16.png',
							iconCls: 'x-btn-text',
							id: 'generate_SeriesNumber',
							name: 'generate_SeriesNumber',
							tabIndex: formTabIndex++,
							text: langs(''),
							tooltip: langs('сгенерировать серию/номер'),
							xtype: 'button'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							fieldLabel: langs('Дата направления'),
							format: 'd.m.Y',
							listeners: {
								'change': function(field, newValue, oldValue) {
									if (blockedDateAfterPersonDeath('personpanel', win.PersonInfo, field, newValue, oldValue)) return;
									win.loadCombo_LpuSectionMedStaffFact();									
								}
							},
							name: 'EvnDirectionCytologic_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: formTabIndex++,
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							allowBlank: false,
							fieldLabel: langs('Время'),
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							name: 'EvnDirectionCytologic_setTime',
							onTriggerClick: function() {
								var base_form = win.FormPanel.getForm();
								var time_field = base_form.findField('EvnDirectionCytologic_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										base_form.findField('EvnDirectionCytologic_setDate').fireEvent('change', base_form.findField('EvnDirectionCytologic_setDate'), base_form.findField('EvnDirectionCytologic_setDate').getValue());
									},
									dateField: base_form.findField('EvnDirectionCytologic_setDate'),
									loadMask: true,
									setDate: true,
									setDateMaxValue: true,
									setDateMinValue: false,
									setTime: true,
									timeField: time_field,
									windowId: win.id
								});
							},
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: formTabIndex++,
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}]
					}]
				}, {
					allowBlank: false,
					fieldLabel: langs('МО направления'),
					hiddenName: 'Lpu_did',
					tabIndex: formTabIndex++,
					width: 430,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							win.loadCombo_LpuSectionMedStaffFact();
						}
					},
					xtype: 'swlpulocalcombo'
				}, {
					allowBlank: true,
					fieldLabel: langs('Направившая МО'),
					hiddenName: 'Lpu_sid',
					tabIndex: formTabIndex++,
					width: 430,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							win.enable_LpuSectionName_MedPersonalFIO();

							var LpuInSystem = combo.getFieldValue('Lpu_IsNotForSystem') != '2';
							if (LpuInSystem) win.loadCombo_LpuSectionMedStaffFact();
						}
					},
					xtype: 'swlpulocalcombo'
				}, {
					fieldLabel: langs('Отделение'), //--текстовое поле, если направление пришло из МО не работающей в системе
					name: 'EvnDirectionCytologic_LpuSectionName',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textfield'
				}, {
					allowBlank: false,
					fieldLabel: langs('Отделение'),
					hiddenName: 'LpuSection_did',
					id: 'EDСEF_LpuSectionCombo',
					linkedElements: [
						'EDСEF_MedStaffactCombo'
					],
					listWidth: 650,
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swlpusectionglobalcombo'
				}, {
					fieldLabel: langs('Лечащий врач'), //--текстовое поле, если направление пришло из МО не работающей в системе
					name: 'EvnDirectionCytologic_MedPersonalFIO',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textfield'
				}, {
					allowBlank: false,
					fieldLabel: langs('Врач'),
					hiddenName: 'MedStaffFact_id',
					id: 'EDСEF_MedStaffactCombo',
					listWidth: 650,
					parentElementId: 'EDСEF_LpuSectionCombo',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					comboSubject: 'MedServiceType',
					enableKeyEvents: true,
					typeCode: 'int',
					width: 320,
					xtype: 'swcommonsprcombo',
					hiddenName: 'MedServiceType_id',
					allowBlank: true,
					tabIndex: formTabIndex++,
					fieldLabel: lang['tip_slujbyi'],
					loadParams: {params: {where: ' where MedServiceType_Code in (2, 9)'}},
					listeners: {
						'change': function (combo, newValue, oldValue) {
							var base_form = win.FormPanel.getForm();
							var type_code = combo.getFieldValue('MedServiceType_Code');
							var ms_combo = base_form.findField('MedService_id');
							var ms_combo_value = ms_combo.getValue();
							var lpu_did_combo = base_form.findField('Lpu_did');
							ms_combo.clearValue();
							ms_combo.getStore().removeAll();
							if(!newValue) return false;
							ms_combo.getStore().baseParams.Lpu_id = lpu_did_combo.getValue();
							ms_combo.getStore().baseParams.MedServiceType_id = newValue;
							ms_combo.getStore().baseParams.MedService_IsCytologic = (type_code == 2) ? 2 : null;
							ms_combo.getStore().load({
								callback: function(){
									if(ms_combo_value && ms_combo.findRecord('MedService_id', ms_combo_value)){
										ms_combo.setValue(ms_combo_value);
									}
								}
							});
						}
					}
				}, {
					xtype: 'swmedserviceglobalcombo',
					fieldLabel: langs('Служба'),
					hiddenName: 'MedService_id',
					tabIndex: formTabIndex++,
					listWidth: 320,
					width: 320,
				}, {
					fieldLabel: 'Тип направления',
					hiddenName: 'EvnDirectionCytologic_IsFirstTime',
					id: 'EDСEF_EvnDirectionCytologic_IsFirstTimeCombo',
					value: 1,
					allowBlank: false,
					triggerAction: 'all',
					forceSelection: true,
					store: [
						[1, 'Первично'],
						[2, 'Повторно']
					],
					xtype: 'combo'
				},  {
					id: 'EvnDirectionCytologic_IsCito',
					fieldLabel: 'Cito!',
					xtype: 'checkbox',
					checked: false
				}, {
					xtype: 'swcommonsprcombo',
					allowBlank: false,
					comboSubject: 'PayType',
					hiddenName: 'PayType_id',
					fieldLabel: 'Вид оплаты',
					width: this.m_width_min
				}, /*{
					//anchor: '100%',
					fieldLabel: 'Вид оплаты 2',
					hiddenName: 'PayType_id',
					// loadParams: {
					// 	params: {where: " where PayType_SysNick in ('bud', 'fbud')"}
					// },
					xtype: 'swpaytypecombo',
				}, */{
					autoCreate: { tag: "input", type: "text", maxLength: "50", autocomplete: "off" },
					enableKeyEvents: true,
					fieldLabel: langs('Номер КВС'),
					hidden: false,
					listeners: {
						'change': function(field, newValue, oldValue) {
							if ( newValue != oldValue ) {
								var base_form = win.FormPanel.getForm();
								base_form.findField('EvnPS_id').setValue(0);
								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').getStore().removeAll();
							}
						},
						'keydown': function(inp, e) {
							switch ( e.getKey() ) {
								case Ext.EventObject.F4:
									e.stopEvent();
									win.openEvnPSListWindow();
								break;
							}
						}
					},
					maxLength: 50,
					name: 'EvnDirectionCytologic_NumKVS',
					onTriggerClick: function() {
						win.openEvnPSListWindow();
					},
					tabIndex: formTabIndex++,
					triggerClass: 'x-form-search-trigger',
					width: 200,
					xtype: 'trigger'
				}, {
					fieldLabel: 'Номер амб. карты',
					hidden: false,
					name: 'EvnDirectionCytologic_NumCard',
					width: 180,
					maskRe: /[^%]/,
					maxLength: 50,
					xtype: 'textfield'
				}, {
					comboSubject: 'BiopsyReceive',
					fieldLabel: langs('Способ получения материала'),
					hiddenName: 'BiopsyReceive_id',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: langs('Дата забора материала'),
					format: 'd.m.Y',
					name: 'EvnDirectionCytologic_MaterialDT',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					selectOnFocus: true,
					tabIndex: formTabIndex++,
					width: 100,
					xtype: 'swdatefield'
				}, {
					xtype: 'swuslugacategorycombo',
					allowBlank: false,
					hiddenName: 'UslugaCategory_id',
					fieldLabel: 'Категория услуги',
					width: 430,
					listeners: {
						'select': function (combo, record, index) {
							win.setComboUslugaComplex();							
						}
					}
				},{
					allowBlank: false,
					fieldLabel: langs('Исследование'),
					//UslugaComplexAttributeType = cytology
					// Ext.getCmp('usl_comb').setAllowedUslugaComplexAttributeList([ 'lab','func','endoscop','laser','ray','inject','xray','registry', 'cytology']);
					// Ext.getCmp('usl_comb').setUslugaCategoryList(['gost2011']);
					hiddenName: 'UslugaComplex_id',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swuslugacomplexnewcombo'
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: win.id + 'DiagDataPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						
					}
				},
				style: 'margin-bottom: 0.5em;',
				title: (panelID++) + '. ' + langs('Диагноз'),
				items: [{
					fieldLabel: langs('Диагноз'),
					hiddenName: 'Diag_id',
					allowBlank: false,
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swdiagcombo'
				},{
					allowBlank: true,
					fieldLabel: langs('Клинический диагноз'),
					height: 100,
					name: 'EvnDirectionCytologic_ClinicalDiag',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}, {
					allowBlank: true,
					fieldLabel: langs('Краткий анамнез'),
					height: 100,
					name: 'EvnDirectionCytologic_Anamnes',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}, {
					allowBlank: true,
					fieldLabel: langs('Гинекологический анамнез'),
					height: 100,
					name: 'EvnDirectionCytologic_GynecologicAnamnes',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}]
			}),
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				autoHeight: true,
				//height: 300,
				id: win.id + 'ProcessingResultsPanel',
				isLoaded: false,
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						var EvnDirectionCytologic_pid = win.FormPanel.getForm().findField('EvnDirectionCytologic_pid').getValue();
						if ( panel.isLoaded === false && EvnDirectionCytologic_pid) {
							panel.isLoaded = true;
							panel.findById(win.id + 'ProcessingResultsGrid').loadData({
								globalFilters: {
									Person_id: win.FormPanel.getForm().findField('Person_id').getValue(),
									EvnDirectionCytologic_pid: EvnDirectionCytologic_pid
								},
								callback: function() {
									win.filterLastResultsGrid();
								}
							});
						}
						panel.doLayout();
					}
				},
				style: 'margin-bottom: 0.5em;',
				title: (panelID++) + '. ' + langs('Обследование'),
				collapsed: false,
				items: [
					{
						region:'center',
                        margins:'35 5 5 0',
                        layout:'column',
                        //height: 150,
						autoHeight: true,
                        autoScroll:true,
						items:[
							{
								columnWidth:.59,
								baseCls:'x-plain',
								bodyStyle:'padding:5px 0 5px 5px',
								autoScroll:false,
								items:[
									new sw.Promed.ViewFrame({
										actions: [
											{ name: 'action_add', disabled: true, hidden: true },
											{ name: 'action_edit', disabled: true, hidden: true },
											{ name: 'action_view', disabled: true, hidden: true },
											{ name: 'action_delete', disabled: true, hidden: true },
											{ name: 'action_refresh', disabled: true, hidden: true },
											{ name: 'action_print', disabled: true, hidden: true }
										],
										autoExpandColumn: 'autoexpand',
										autoExpandMin: 145,
										autoLoadData: false,
										height: 145,
										border: false,
										dataUrl: '/?c=EvnDirectionCytologic&m=loadProcessingResultsGrid',
										id: win.id + 'ProcessingResultsGrid',
										object: 'EvnCytologicProto',
										paging: false,
										region: 'center',
										stringfields: [
											{ name: 'EvnUsluga_id', type: 'int', header: 'ID', key: true },
											{ name: 'EvnUsluga_pid', type: 'int',  hidden: true },
											{ name: 'EvnUsluga_setDT', type: 'date', header: langs('Дата'), width: 150 },
											{ name: 'UslugaComplex_CodeName', type: 'string', header: langs('Вид исследования'), width: 300 },
											{ name: 'StudyResult_Name', type: 'string', header: langs('Результат'), width: 150, id: 'autoexpand' }
										],
										toolbar: false
									})
								]
							},{
								columnWidth:.40, 
								baseCls:'x-plain',
								bodyStyle:'padding:5px 2px 5px 5px',
								items:[{
									title: 'Данные о проведенных обследованиях:',
									items:[{
										allowBlank: true,
										height: 120,
										name: 'EvnDirectionCytologic_Data',
										tabIndex: formTabIndex++,
										xtype: 'textarea',
										style: {
											width: '99%'
										}
									}]
								}]
							}
						]
					}					
				]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: win.id + 'TreatmentCarriedOutDataPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						
					}
				},
				style: 'margin-bottom: 0.5em;',
				title: (panelID++) + '. ' + langs('Проведенное лечение'),
				items: [{
					allowBlank: true,
					fieldLabel: langs('Оперативное'),
					height: 100,
					name: 'EvnDirectionCytologic_OperTherapy',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}, {
					allowBlank: true,
					fieldLabel: langs('Лучевое'),
					height: 100,
					name: 'EvnDirectionCytologic_RadiationTherapy',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}, {
					allowBlank: true,
					fieldLabel: langs('Химиотерапия'),
					height: 100,
					name: 'EvnDirectionCytologic_ChemoTherapy',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}]
			}),
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				hidden: false,
				height: 170,
				id: win.id + 'VolumeAndMacroscopicDescriptionPanel',
				isLoaded: false,
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						panel.doLayout();
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById(win.id + 'VolumeAndMacroscopicDescriptionGrid').loadData({
								globalFilters: {
									EvnDirectionCytologic_id: win.FormPanel.getForm().findField('EvnDirectionCytologic_id').getValue()
								}
							});
						}
					}
				},
				style: 'margin-bottom: 0.5em;',
				title:(panelID++) + '. ' + langs('Объем и макроскопическое описание материала'),
				collapsed: true,
				autoScroll: true,
				items: [
					new sw.Promed.ViewFrame({
						actions: [
							{ name: 'action_add', handler: function() { win.openEditWindow('add', 'swDescriptionOfDiologicalMaterialEditWindow'); } },
							{ name: 'action_edit', handler: function() { win.openEditWindow('edit', 'swDescriptionOfDiologicalMaterialEditWindow'); } },
							{ name: 'action_view', handler: function() { win.openEditWindow('view', 'swDescriptionOfDiologicalMaterialEditWindow'); } },
							{ name: 'action_delete', handler: function() { win.deleteGridForm(win.id+'VolumeAndMacroscopicDescriptionGrid'); } },
							{ name: 'action_refresh', disabled: true, hidden: true },
							{ name: 'action_print', disabled: true, hidden: true }
						],
						autoExpandColumn: 'autoexpand',
						autoExpandMin: 350,
						autoLoadData: false,
						border: false,
						dataUrl: '/?c=EvnDirectionCytologic&m=loadVolumeAndMacroscopicDescriptionGrid',
						id: win.id + 'VolumeAndMacroscopicDescriptionGrid',
						object: 'VolumeAndMacroscopicDescription',
						paging: false,
						region: 'center',
						stringfields: [
							{ name: 'MacroMaterialCytologic_id', type: 'int', header: 'ID', key: true },
							{ name: 'RecordStatus_Code', type: 'int', hidden: true },
							{ name: 'BiologycalMaterialType_id', type: 'int', hidden: true},
							{ name: 'MacroMaterialCytologic_Mark', type: 'string', header: langs('Маркировка препарата'), width: 300 },
							{ name: 'MacroMaterialCytologic_Size', type: 'string', header: langs('Объем'), width: 200 /*, id: 'autoexpand'*/ },
							{ name: 'MacroMaterialCytologic_CountObject', type: 'string', header: langs('Кол-во объектов'), width: 200 },
							{ name: 'BiologycalMaterialType_Name', type: 'string', header: langs('Макро-описание'), width: 300 }
						],
						toolbar: true
					})
				]
			}),

			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				hidden: false,
				height: 170,
				id: win.id + 'LocalizationNatureProcessAndMethodPanel',
				isLoaded: false,
				layout: 'form',
				autoScroll: true,
				listeners: {
					'expand': function(panel) {
						panel.doLayout();
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById(win.id + 'LocalizationNatureProcessAndMethodGrid').loadData({
								globalFilters: {
									EvnDirectionCytologic_id: win.FormPanel.getForm().findField('EvnDirectionCytologic_id').getValue()
								}
							});
						}
					}
				},
				style: 'margin-bottom: 0.5em;',
				title: (panelID++) + '. ' + langs('Локализация, характер процесса и способ получения материала'),
				collapsed: true,
				items: [ new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', handler: function() { win.openEditWindow('add', 'swLocalizationNatureProcessEditWindow'); } },
						{ name: 'action_edit', handler: function() { win.openEditWindow('edit', 'swLocalizationNatureProcessEditWindow'); } },
						{ name: 'action_view', handler: function() { win.openEditWindow('view', 'swLocalizationNatureProcessEditWindow'); } },
						{ name: 'action_delete', handler: function() { win.deleteGridForm(win.id+'LocalizationNatureProcessAndMethodGrid'); } },
						{ name: 'action_refresh', disabled: true, hidden: true },
						{ name: 'action_print', disabled: true, hidden: true }
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 350,
					autoLoadData: false,
					border: false,
					dataUrl: '/?c=EvnDirectionCytologic&m=loadLocalizationNatureProcessAndMethodGrid',
					id: win.id + 'LocalizationNatureProcessAndMethodGrid',
					object: 'LocalizationNatureProcessAndMethod',
					paging: false,
					region: 'center',
					stringfields: [
						{ name: 'LocalProcessCytologic_id', type: 'int', header: 'ID', key: true },
						{ name: 'EvnDirectionCytologic_id', type: 'int', hidden: true },
						{ name: 'PathologicProcessType_id', type: 'int', hidden: true },
						{ name: 'BiopsyReceive_id', type: 'int', hidden: true },
						{ name: 'RecordStatus_Code', type: 'int', hidden: true },
						{ name: 'PathologicProcessType_Name', type: 'string', header: langs('Характер патологического процесса'), width: 150 },
						{ name: 'LocalProcessCytologic_FeatureForm', type: 'string', header: langs('Характеристики образования; прилежащие ткани'), width: 350, id: 'autoexpand' },
						{ name: 'LocalProcessCytologic_Localization', type: 'string', header: langs('Локализация патологического процесса'), width: 450 },
						{ name: 'BiopsyReceive_Name', type: 'string', header: langs('Способ получения материала'), width: 150 }
					],
					toolbar: true
				})]
			}),
			]
		});

		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				if ( this.action == 'view' ) {
					this.buttons[this.buttons.length - 1].focus();
				}
				else {
					this.FormPanel.getForm().findField('EvnDirectionCytologic_Num').focus(true);
				}
			}.createDelegate(this),
			button2Callback: function(callback_data) {
				this.FormPanel.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				this.FormPanel.getForm().findField('Server_id').setValue(callback_data.Server_id);

				this.PersonInfo.load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
			}.createDelegate(this),
			button2OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button3OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button4OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			button5OnHide: function() {
				this.PersonInfo.button1OnHide();
			}.createDelegate(this),
			collapsible: true,
			collapsed: true,
			floatable: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north',
			title: langs('Загрузка...'),
			titleCollapse: true
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',				
				tabIndex: formTabIndex++,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnDirectionCytologic();
				}.createDelegate(this),
				iconCls: 'print16',				
				tabIndex: formTabIndex++,
				text: BTN_FRMPRINT
			}, 
			{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',				
				tabIndex: formTabIndex++,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swEvnDirectionCytologicEditWindow.superclass.initComponent.apply(this, arguments);
	}
});