/**
* swEvnCytologicProtoEditWindow - Протокол цитологического диагностического исследования
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
* 
*/

sw.Promed.swEvnCytologicProtoEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,	
	deleteEvnUslugaPar: function() {
		var grid = this.findById('ECPEF_EvnUslugaParGrid').getGrid();
		var view_frame = this.findById('ECPEF_EvnUslugaParGrid');

		if ( !view_frame || !grid ) {
			sw.swMsg.alert('Ошибка', 'Не найден список услуг');
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() ) {
			sw.swMsg.alert('Ошибка', 'Не выбрана услуга из списка');
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var evn_usluga_par_id = selected_record.get('EvnUslugaPar_id');

		if ( !evn_usluga_par_id ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert('Ошибка', 'При удалении усдуши возникли ошибки [Тип ошибки: 2]');
						},
						params: {
							EvnUslugaPar_id: evn_usluga_par_id
						},
						success: function(response, options) {
							grid.getStore().reload();
						},
						url: '/?c=EvnUslugaPar&m=deleteEvnUslugaPar'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить параклиническую услугу?',
			title: 'Вопрос'
		});
	},
    openEvnUslugaParEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( this.action == 'view' )

		if ( getWnd('swEvnUslugaParEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования услуги уже открыто');
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('ECPEF_EvnUslugaParGrid').getGrid();
		var params = new Object();
		var evn_direction_id = parseInt(base_form.findField('EvnDirectionCytologic_id').getValue());
		if(!evn_direction_id){
			sw.swMsg.alert('Сообщение', 'Не выбрано направление на цитологическое диагностическое исследование');
			base_form.findField('EvnDirectionCytologic_SerNum').focus(true);
			return false;
		}
		
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		
		if ( action == 'add' ) {		
			var form_params = {
				'Person_id': base_form.findField('Person_id').getValue(),
				'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
				'Server_id': base_form.findField('Server_id').getValue(),		
				'EvnDirection_id': evn_direction_id,
				'EvnUslugaPar_Kolvo': 1,
				'EvnUslugaPar_IsCytologic': 2
			}			
			Ext.apply(form_params, params);
			getWnd('swEvnUslugaParEditWindow').show(form_params);		
		} else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}
			var selected_record = grid.getSelectionModel().getSelected();
			var evn_usluga_par_id = selected_record.get('EvnUslugaPar_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');
			if ( evn_usluga_par_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.EvnUslugaPar_id = evn_usluga_par_id;
				params.Person_id = person_id;
				params.Server_id = server_id;
				getWnd('swEvnUslugaParEditWindow').show(params);
			}
		}
	
	},
	doSave: function(options){
		var formStatus = this.formStatus;
		try {
			this.doSaveContinue(options);
			this.saveMask.hide();
		} catch (err) {
			this.saveMask.hide();
			this.formStatus = formStatus;
			if(err instanceof SyntaxError) {
				console.log("Ошибка в синтаксисе данных: " + err.message);
			}else{
				throw err;
			}
		}
	},
	doSaveContinue: function(options) {
		// options @Object
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		// options.print @Boolean Вызывать печать протокола патологогистологического исследования, если true

		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';
		this.onCancelActionFlag = false;

		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.onCancelActionFlag = true;
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();
		this.saveMask.show();

		params.EvnCytologicProto_Ser = base_form.findField('EvnCytologicProto_Ser').getValue();
		params.EvnCytologicProto_Num = base_form.findField('EvnCytologicProto_Num').getValue();
		params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

        if ( this.PrescrReactionTypePanel.isVisible()) {
        	params.PrescrReactionType_ids = this.PrescrReactionTypePanel.getIds();
		}
		if ( this.MedPersonalPanel.isVisible()) {
			params.MedPersonal_ids = this.MedPersonalPanel.getMedPersonalIds();
			params.MedStaffFact_ids = this.MedPersonalPanel.getIds();
			if(!params.MedStaffFact_ids){
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						this.onCancelActionFlag = true;
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'поле "Исследование выполнили, ФИО" - обязательно для заполнения хотя бы одного рабочего места !!!',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		this.clearBlockCytogram('save');

		// var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение протокола..." });
		// this.saveMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				this.onCancelActionFlag = true;
				this.saveMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				this.saveMask.hide();

				if ( action.result ) {
					if ( action.result.EvnCytologicProto_id > 0 ) {
						base_form.findField('EvnCytologicProto_id').setValue(action.result.EvnCytologicProto_id);

						if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
							this.onCancelActionFlag = true;
							options.openChildWindow();
						}
						else {
							var data = new Object();

							var med_personall_fio = '';

							base_form.findField('LabMedPersonal_id').getStore().each(function(rec) {
								if ( rec.get('MedPersonal_id') == base_form.findField('LabMedPersonal_id').getValue() ) {
									med_personall_fio = rec.get('MedPersonal_Fio');
								}
							});

							data.evnCytologicProtoData = {
								'EvnCytologicProto_id': base_form.findField('EvnCytologicProto_id').getValue(),
								'accessType': 'edit',
								'EvnDirectionCytologic_IsBad': 0,
								'Person_id': base_form.findField('Person_id').getValue(),
								'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
								'Server_id': base_form.findField('Server_id').getValue(),
								'EvnCytologicProto_Ser': base_form.findField('EvnCytologicProto_Ser').getValue(),
								'EvnCytologicProto_Num': base_form.findField('EvnCytologicProto_Num').getValue(),
								'EvnCytologicProto_SurveyDT': base_form.findField('EvnCytologicProto_SurveyDT').getValue(),
								// 'Lpu_Name': '',
								// 'LpuSection_Name': '',
								// 'EvnDirectionCytologic_NumCard': '',
								'Person_Surname': this.PersonInfo.getFieldValue('Person_Surname'),
								'Person_Firname': this.PersonInfo.getFieldValue('Person_Firname'),
								'Person_Secname': this.PersonInfo.getFieldValue('Person_Secname'),
								'Person_Birthday': this.PersonInfo.getFieldValue('Person_Birthday'),
								'MedPersonal_Fio': med_personall_fio
							};

							this.callback(data);

							if ( options && options.print ) {
								this.buttons[1].focus();
								this.printProto();
							}
							else {
								this.hide();
							}
						}
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert('Ошибка', action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
						}
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'EvnDirectionCytologic_SerNum',
			'EvnCytologicProto_MaterialDT',
			'PayType_id',
			//'UslugaComplex_id',
			'EvnCytologicProto_CountUsluga',
			'EvnCytologicProto_CountGlass',
			'EvnCytologicProto_CountFlacon',
			'EvnCytologicProto_IssueDT',
			'OnkoDiag_id',
			'EvnCytologicProto_MicroDescr',
			'Mkb10Code_id',
			'EvnCytologicProto_Difficulty',
			'EvnCytologicProto_Conclusion',
			'EvnCytologicProto_SurveyDT',
			'LabMedPersonal_id',
			'DrugQualityCytologic_id',
			'ScreeningSmearType_id',
			'EvnCytologicProto_Cytogram',
			'EvnCytologicProto_Description',
			'CytologicMaterialPathology_id',
			'EvnCytologicProto_Degree',
			'EvnCytologicProto_Etiologic',
			'EvnCytologicProto_OtherConcl',
			'EvnCytologicProto_MoreClar'
		);
		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			var elem = base_form.findField(form_fields[i]);
			if(!elem) continue;
			if ( enable ) {
				elem.enable();
			}
			else {
				elem.disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
			this.findById('ECPEF_EvnUslugaParGrid').setReadOnly(false);
		}
		else {
			this.buttons[0].hide();
            this.findById('ECPEF_EvnUslugaParGrid').setReadOnly(true);
		}
	},
	formStatus: 'edit',
	height: 550,
	id: 'swEvnCytologicProtoEditWindow',
	setLabMedPersonal: function(){
		var base_form = this.FormPanel.getForm(),
			comboMedstaffact = base_form.findField('MedPersonalPanel_id_1'),
			comboLabMedPersonal = base_form.findField('LabMedPersonal_id');

		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		if(!lpu_section_id) lpu_section_id = comboMedstaffact.getFieldValue('LpuSection_id');
		if(!lpu_section_id) {
			comboLabMedPersonal.clearValue();
			comboLabMedPersonal.getStore().removeAll();
			return false;
		}
		
		setMedStaffFactGlobalStoreFilter({
			Lpu_id: getGlobalOptions().lpu_id,
			LpuSection_id: lpu_section_id,
			isMidMedPersonalOnly: true,
		});
		comboLabMedPersonal.getStore().loadData(getMedPersonalListFromGlobal());
		comboLabMedPersonal.setValue(comboLabMedPersonal.getValue());
		/*
		PostKind_id = "6";
		comboLabMedPersonal.getStore().load({
			params: {
				Lpu_id: getGlobalOptions().lpu_id,
				LpuSection_id: lpu_section_id,
				withPosts: PostKind_id,//только средний мед. персонал
				// fromRegistryViewForm: 2
			}
		});
		*/
	},
	initComponent: function() {
		var
			_this = this,
			formTabIndex = TABINDEX_EHPEF;


		_this.PrescrReactionTypeBodyPanel = new Ext.Panel({
			layout: 'form',
			autoHeight: true,
			border: false,
			items: []
		});

		_this.PrescrReactionTypePanel = new Ext.Panel({
			baseFilter: null,
			bodyStyle:'padding: 10px; margin-bottom: 5px;', 
			width: 800,
			count: 0,
			limit: -1,
			numArr: [],
			setBaseFilter: function(filterFn) {				
				var base_form = _this.FormPanel.getForm();
				var container = _this.PrescrReactionTypePanel;
				var arrNums = this.numArr.length;
				container.baseFilter = filterFn;
				
				for (var i = 0; i <= arrNums; i++) {
					if(!this.numArr[i]) continue;
					var num = this.numArr[i];

					var field = base_form.findField('PrescrReactionType_id_' + num);
					if (field) field.setBaseFilter(container.baseFilter);
				}
				
			},
			setAccess: function() {				
				var base_form = _this.FormPanel.getForm();
				var container = _this.PrescrReactionTypePanel;
				var arrNums = this.numArr.length;
				var last_number = this.getLastNumber();
				var firstNumber = null;
				for (var i = 0; i <= arrNums; i++) {
					if(!this.numArr[i]) continue;
					var num = this.numArr[i];
					if(firstNumber == null) firstNumber = i;

					if ( _this.getAction() == 'view' ) {
						base_form.findField('PrescrReactionType_id_' + num).disable();
						container.findById('PrescrReactionTypeAddButton_' + num).hide();
						container.findById('PrescrReactionTypeDelButton_' + num).hide();
					}
					else {
						base_form.findField('PrescrReactionType_id_' + num).enable();
						container.findById('PrescrReactionTypeAddButton_' + num).show();
						container.findById('PrescrReactionTypeDelButton_' + num).show();

						if ( num != last_number ) {
							base_form.findField('PrescrReactionType_id_' + num).disable();
						}

						// if ( num == 1 || num != last_number ) {
						// 	container.findById('PrescrReactionTypeDelButton_' + num).hide();
						// }

						if ( num < last_number || container.getLimit() == num ) {
							container.findById('PrescrReactionTypeAddButton_' + num).hide();
						}
					}
				}
				
				if(firstNumber !== null && base_form.findField('PrescrReactionType_id_' + this.numArr[firstNumber]) && !base_form.findField('PrescrReactionType_id_' + this.numArr[firstNumber]).fieldLabel){
					base_form.findField('PrescrReactionType_id_' + this.numArr[firstNumber]).setFieldLabel(langs('Назначенные окраски')+':');
				}
				if (this.getCount() == 1) {
					//скрываем кнопку удалить
					var last_number = this.getLastNumber();
					var elemDelButton = container.findById('PrescrReactionTypeDelButton_' + last_number);
					if(elemDelButton) elemDelButton.hide();
				}				
			},
			getCount: function() {
				// return this.count;
				this.numArr =  this.numArr.filter(Number);
				return this.numArr.length;
			},
			getLimit: function() {
				return this.limit;
			},
			getIds: function() {
				var base_form = _this.FormPanel.getForm();
				var container = _this.PrescrReactionTypePanel;
				var arrNums = this.numArr.length;
				var ids = [];

				for (var i = 0; i <= arrNums; i++) {
					if(!this.numArr[i]) continue;
					var num = this.numArr[i];

					var field = base_form.findField('PrescrReactionType_id_' + num);
					if (field && !Ext.isEmpty(field.getValue())) {
						ids.push(field.getValue());
					}
				}

				return ids.join(',');
			},
			setIds: function(ids) {
				var container = _this.PrescrReactionTypePanel;

				container.resetFieldSets();

				var ids_arr = ids.split(',');
				for (var i = 0; i < ids_arr.length; i++) {
					container.addFieldSet({value: ids_arr[i]});
				}
			},
			checkLimit: function(checkCount) {
				var container = _this.PrescrReactionTypePanel;
				return (container.getLimit() == -1 || container.getLimit() >= container.count);
			},
			resetFieldSets: function() {
				var container = _this.PrescrReactionTypePanel;
				var count = container.count;
				var arrNums = this.numArr.length;

				for (var i = 0; i <= arrNums; i++) {
					if(!this.numArr[i]) continue;
					var num = this.numArr[i];
					container.deleteFieldSet(num);
				}
				container.count = 0;
				this.numArr = [];
			},
			deleteFieldSet: function(num) {
				var base_form = _this.FormPanel.getForm();
				var container = _this.PrescrReactionTypePanel;
				var panel = _this.PrescrReactionTypeBodyPanel;

				if (panel.findById('PrescrReactionTypeFieldSet_' + num)) {
					var field = base_form.findField('PrescrReactionType_id_' + num);
					var rec = field.findRecord('PrescrReactionType_id', field.getValue());
					base_form.items.removeKey(field.id);

					panel.remove('PrescrReactionTypeFieldSet_'+num);
					_this.doLayout();
					_this.syncShadow();
					_this.FormPanel.initFields();

					container.count--;
					this.delElemNumArr(num);
					if(rec && rec.data) this.resetFieldsBaseFilter(num, rec);
				}
			},
			getLastNumber: function(){
				var array = this.numArr;
			    var index = array.length;			    
				while (index-- && !array[index]);
				return array[index];
			},
			resetFieldsBaseFilter: function(num, rec){
				if(!num) return false;
				var base_form = _this.FormPanel.getForm();
				var field_next = null;
				var container = _this.PrescrReactionTypePanel;
				var prescrReactionType_id = null;
				var arr = [];
				for (var i = 0; i <= this.numArr.length; i++) {
					if(!this.numArr[i] || this.numArr[i] <= num) continue;
					field_next = base_form.findField('PrescrReactionType_id_' + this.numArr[i]);
					if(field_next && rec && rec.data){
						var r = new Array(new Ext.data.Record(rec.data));
						field_next.getStore().add(r);
						field_next.getStore().sort('PrescrReactionType_Code', 'ASC');
						field_next.render();
					}
				}
			},
			delElemNumArr: function(num){
				var num = num || this.numArr.length-1;
				if(num){
					var idx = this.numArr.indexOf(num);
					if( idx >= 0 ) delete this.numArr[idx];
				}
			},
			setNumArr: function(){
				var current_number = (this.numArr.length == 0) ? 0 : this.numArr[this.numArr.length-1];
				current_number++;
				this.numArr.push(current_number);
				return this.numArr[this.numArr.length-1];
			},
			addFieldSet: function(options) {
				_this.loadMask.show();
				var base_form = _this.FormPanel.getForm();
				var container = _this.PrescrReactionTypePanel;
				var panel = _this.PrescrReactionTypeBodyPanel;

				var ids = container.getIds();
				var usedValues = (!Ext.isEmpty(ids) ? ids.split(',') : []);

				container.count++;
				var num = this.setNumArr(); // container.count;

				if (!container.checkLimit()) {
					container.count--;
					this.delElemNumArr();
					return false;
				}

				var addButton = new Ext.Button({
					iconCls:'add16',
					handler: function() {
						if(_this.PrescrReactionTypePanel.getCount() > 0 ){
							var last_field = base_form.findField('PrescrReactionType_id_' + _this.PrescrReactionTypePanel.getLastNumber());
							if(!Ext.isEmpty(last_field) && Ext.isEmpty(last_field.getValue())) return false;
						}
						_this.PrescrReactionTypePanel.addFieldSet();
					},
					id: 'PrescrReactionTypeAddButton_' + num
				});

				var delButton = new Ext.Button({
					iconCls: 'delete16',
					handler: function() {
						container.deleteFieldSet(num);
						container.setAccess();
					},
					id: 'PrescrReactionTypeDelButton_' + num
				});

				var config = {
					layout: 'column',
					id: 'PrescrReactionTypeFieldSet_' + num,
					border: false,
					cls: 'AccessRigthsFieldSet',
					items: [{
						layout: 'form',
						border: false,
						labelWidth: 200,
						items: [{
							comboSubject: 'PrescrReactionType',
							displayField: 'PrescrReactionType_Display',
							editable: true,
							fieldLabel: (container.count == 1 ? langs('Назначенные окраски') : ''),
							hiddenName: 'PrescrReactionType_id_' + num,
							ignoreCodeField: true,
							labelSeparator: (container.count == 1 ? ':' : ''),
							lastQuery: '',
							moreFields: [{
								name: 'PrescrReactionType_Display',
								convert: function(val,row) {
									return row.PrescrReactionType_Code + '. ' + row.PrescrReactionType_Name;
								}	
							}],
							width: 430,
							xtype: 'swcommonsprcombo'
						}]
					}, {
						layout: 'form',
						border: false,
						items: [
							delButton
						]
					}, {
						layout: 'form',
						border: false,
						items: [
							addButton
						]
					}]
				};

				panel.add(config);
				_this.doLayout();
				_this.syncSize();
				_this.FormPanel.initFields();

				var field = base_form.findField('PrescrReactionType_id_' + num);

				if (field) {
					field.setBaseFilter(container.baseFilter);
					field.getStore().load({
						callback: function() {
							if ( _this.PrescrReactionTypePanel.getLimit() == -1 ) {
								_this.PrescrReactionTypePanel.limit = field.getStore().getCount();
							}
							_this.loadMask.hide();
						},
						failure : function() { _this.loadMask.hide(); },
						params: {
							where: (usedValues.length > 0 ? " where PrescrReactionType_id not in (" + usedValues.join(',') + ")" : null)
						}
					});

					if (options && options.value) {
						field.setValue(options.value);
					}
				}

				container.setAccess();
			},
			items: [ _this.PrescrReactionTypeBodyPanel ]
		});
		
		_this.MedPersonalBodyPanel = new Ext.Panel({
			layout: 'form',
			autoHeight: true,
			border: false,
			items: []
		});

		_this.MedPersonalPanel = new Ext.Panel({
			baseFilter: null,
			border: true,
			bodyStyle:'padding: 10px; margin-bottom: 5px;', 
			width: 800,
			count: 0,
			limit: -1,
			numArr: [],
			setBaseFilter: function(filterFn) {
				//
			},
			setAccess: function() {				
				var base_form = _this.FormPanel.getForm();
				var container = _this.MedPersonalPanel;
				var arrNums = this.numArr.length;
				var last_number = this.getLastNumber();
				var firstNumber = null;
				for (var i = 0; i <= arrNums; i++) {
					if(!this.numArr[i]) continue;
					var num = this.numArr[i];
					if(firstNumber == null) firstNumber = i;
					if ( _this.getAction() == 'view' ) {
						base_form.findField('MedPersonalPanel_id_' + num).disable();
						container.findById('MedPersonalAddButton_' + num).hide();
						container.findById('MedPersonalDelButton_' + num).hide();
					}
					else {
						base_form.findField('MedPersonalPanel_id_' + num).enable();
						container.findById('MedPersonalAddButton_' + num).show();
						container.findById('MedPersonalDelButton_' + num).show();
						
						if ( num != last_number ) {
							base_form.findField('MedPersonalPanel_id_' + num).disable();
						}

						// if ( num == 1 || num != last_number ) {
						// 	container.findById('MedPersonalDelButton_' + num).hide();
						// }

						if ( num < last_number || container.getLimit() == num ) {
							container.findById('MedPersonalAddButton_' + num).hide();
						}
					}
				}
				
				if(firstNumber !== null && base_form.findField('MedPersonalPanel_id_' + this.numArr[firstNumber]) && !base_form.findField('MedPersonalPanel_id_' + this.numArr[firstNumber]).fieldLabel){
					base_form.findField('MedPersonalPanel_id_' + this.numArr[firstNumber]).setFieldLabel(langs('Исследование выполнили, ФИО')+':');
				}
				if (this.getCount() == 1) {
					//скрываем кнопку удалить
					var last_number = this.getLastNumber();
					var elemDelButton = container.findById('MedPersonalDelButton_' + last_number);
					if(elemDelButton) elemDelButton.hide();
					if(firstNumber !== null ) base_form.findField('MedPersonalPanel_id_' + this.numArr[firstNumber]).setAllowBlank(false);
				}
				
			},
			getCount: function() {
				// return this.count;
				this.numArr =  this.numArr.filter(Number);
				return this.numArr.length;
			},
			getLimit: function() {
				return this.limit;
			},
			getIds: function() {
				var base_form = _this.FormPanel.getForm();
				var container = _this.MedPersonalPanel;
				var arrNums = this.numArr.length;
				var ids = [];

				for (var i = 0; i <= arrNums; i++) {
					if(!this.numArr[i]) continue;
					var num = this.numArr[i];

					var field = base_form.findField('MedPersonalPanel_id_' + num);
					if (field && !Ext.isEmpty(field.getValue())) {
						ids.push(field.getValue());
					}
				}

				return ids.join(',');
			},
			getMedPersonalIds: function() {
				var base_form = _this.FormPanel.getForm();
				var container = _this.MedPersonalPanel;
				var arrNums = this.numArr.length;
				var ids = [];

				for (var i = 0; i <= arrNums; i++) {
					if(!this.numArr[i]) continue;
					var num = this.numArr[i];

					var field = base_form.findField('MedPersonalPanel_id_' + num);
					var med_personal_id = field.getFieldValue('MedPersonal_id');
					if (field && !Ext.isEmpty(med_personal_id)) {
						ids.push(med_personal_id);
					}
				}

				return ids.join(',');
			},
			getArrIds: function(ignoreNum) {
				var base_form = _this.FormPanel.getForm();
				var container = _this.MedPersonalPanel;
				var arrNums = this.numArr.length;
				var ids = [];
				var ignoreNum = ignoreNum || false;

				for (var i = 0; i <= arrNums; i++) {
					if(!this.numArr[i]) continue;
					var num = this.numArr[i];

					if(ignoreNum && ignoreNum == num) continue;
					var field = base_form.findField('MedPersonalPanel_id_' + num);
					if (field && !Ext.isEmpty(field.getValue())) {
						ids.push(field.getValue());
					}
				}

				return ids;
			},
			setIds: function(ids) {
				var container = _this.MedPersonalPanel;

				container.resetFieldSets();

				var ids_arr = ids.split(',');
				for (var i = 0; i < ids_arr.length; i++) {
					container.addFieldSet({value: ids_arr[i]});
				}
			},
			checkLimit: function(checkCount) {
				var container = _this.MedPersonalPanel;
				return (container.getLimit() == -1 || container.getLimit() >= container.count);
			},
			resetFieldSets: function() {
				var container = _this.MedPersonalPanel;
				var count = container.count;
				var arrNums = this.numArr.length;
				
				for (var i = 0; i <= arrNums; i++) {
					if(!this.numArr[i]) continue;
					var num = this.numArr[i];

					container.deleteFieldSet(num);
				}
				container.count = 0;
				this.numArr = [];
			},
			getLastNumber: function(){
				var array = this.numArr;
			    var index = array.length;			    
				while (index-- && !array[index]);
				return array[index];
			},
			resetFieldsBaseFilter: function(num){
				if(!num) return false;
				var base_form = _this.FormPanel.getForm();
				var field_next = null;
				var medStaffFact_id = null;
				var arr = [];
				for (var i = 0; i <= this.numArr.length; i++) {
					if(this.numArr[i]) {
						field_next = base_form.findField('MedPersonalPanel_id_' + this.numArr[i]);
						if(field_next) arr.push(field_next.getValue());
					}
					if(this.numArr[i] && this.numArr[i] <= num) continue;
					if(field_next){
						field_next.clearBaseFilter();
						medStaffFact_id = field_next.getValue();
						field_next.setBaseFilter(function(record) {
							return (medStaffFact_id == record.get('MedStaffFact_id') || arr.indexOf(record.get('MedStaffFact_id')) < 0);
						});
					}
				}
			},
			deleteFieldSet: function(num) {
				var base_form = _this.FormPanel.getForm();
				var container = _this.MedPersonalPanel;
				var panel = _this.MedPersonalBodyPanel;

				if (panel.findById('MedPersonalFieldSet_' + num)) {
					var field = base_form.findField('MedPersonalPanel_id_' + num);
					base_form.items.removeKey(field.id);

					panel.remove('MedPersonalFieldSet_'+num);
					_this.doLayout();
					_this.syncShadow();
					_this.FormPanel.initFields();

					container.count--;
					this.delElemNumArr(num);
					
					this.resetFieldsBaseFilter(num);
				}
			},
			delElemNumArr: function(num){
				var num = num || this.numArr.length-1;
				if(num){
					var idx = this.numArr.indexOf(num);
					if( idx >= 0 ) delete this.numArr[idx];
				}
			},
			setNumArr: function(){
				var current_number = (this.numArr.length == 0) ? 0 : this.numArr[this.numArr.length-1];
				current_number++;
				this.numArr.push(current_number);
				return this.numArr[this.numArr.length-1];
			},
			addFieldSet: function(options) {
				_this.loadMask.show();
				var base_form = _this.FormPanel.getForm();
				var container = _this.MedPersonalPanel;
				var panel = _this.MedPersonalBodyPanel;

				var ids = container.getIds();
				var usedValues = (!Ext.isEmpty(ids) ? ids.split(',') : []);

				container.count++;
				var num = this.setNumArr(); //container.count;

				if (!container.checkLimit()) {
					container.count--;
					this.delElemNumArr(num);
					return false;
				}

				var addButton = new Ext.Button({
					iconCls:'add16',
					handler: function() {
						if(_this.MedPersonalPanel.getCount() > 0){
							var last_field = base_form.findField('MedPersonalPanel_id_' + _this.MedPersonalPanel.getLastNumber());
							if(!Ext.isEmpty(last_field) && Ext.isEmpty(last_field.getValue())) return false;
						}
						_this.MedPersonalPanel.addFieldSet();
					},
					id: 'MedPersonalAddButton_' + num
				});

				var delButton = new Ext.Button({
					iconCls: 'delete16',
					handler: function() {
						container.deleteFieldSet(num);
						container.setAccess();
					},
					id: 'MedPersonalDelButton_' + num
				});

				var config = {
					layout: 'column',
					id: 'MedPersonalFieldSet_' + num,
					border: false,
					cls: 'AccessRigthsFieldSet',
					items: [{
						layout: 'form',
						border: false,
						labelWidth: 200,
						items: [{
							editable: true,
							fieldLabel: (container.count == 1 ? langs('Исследование выполнили, ФИО') : ''),
							hiddenName: 'MedPersonalPanel_id_' + num,
							num: num,
							ignoreCodeField: true,
							labelSeparator: (container.count == 1 ? ':' : ''),
							width: 430,
							listeners: {
								'change': function(field, newValue, oldValue) {
									if(field.num == 1) Ext.getCmp('swEvnCytologicProtoEditWindow').setLabMedPersonal();
								}.createDelegate(this)
							},
							xtype: 'swmedstafffactglobalcombo'
						}]
					}, {
						layout: 'form',
						border: false,
						items: [
							delButton
						]
					}, {
						layout: 'form',
						border: false,
						items: [
							addButton
						]
					}]
				};

				panel.add(config);
				_this.doLayout();
				_this.syncSize();
				_this.FormPanel.initFields();

				var field = base_form.findField('MedPersonalPanel_id_' + num);
				var lpu_section_id =  base_form.findField('LpuSection_id').getValue();
				
				if (field) {
					field.getStore().load({
						callback: function() {
							var arr = _this.MedPersonalPanel.getArrIds(num);
							field.setBaseFilter(function(record) {
								return (arr.indexOf(record.get('MedStaffFact_id')) < 0);
							}.bind(_this.MedPersonalPanel));
							if ( _this.MedPersonalPanel.getLimit() == -1 ) {
								_this.MedPersonalPanel.limit = field.getStore().getCount();
							}
							_this.loadMask.hide();
							field.setValue(field.getValue());
						},
						failure : function() { _this.loadMask.hide(); },
						params: {
							Lpu_id: getGlobalOptions().lpu_id,
							LpuSection_id: (lpu_section_id) ? lpu_section_id : getGlobalOptions().CurLpuSection_id,
							mode: 'combo'
						}
					});

					if (options && options.value) {
						field.setValue(options.value);
						if(num == 1) field.fireEvent('change', field, options.value);
					}

				}

				container.setAccess();
			},
			items: [ _this.MedPersonalBodyPanel ]
		});
		
		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnCytologicProtoEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'LpuSection_id' },
				{ name: 'MedStaffFact_ids'},
				{ name: 'LabMedPersonal_id'},
				{ name: 'EvnCytologicProto_Conclusion'},
				// { name: 'EvnCytologicProto_Count'},
				{ name: 'EvnCytologicProto_CountUsluga'},
				{ name: 'EvnCytologicProto_CountFlacon'},
				{ name: 'EvnCytologicProto_CountGlass'},
				{ name: 'EvnCytologicProto_Difficulty'},
				{ name: 'EvnCytologicProto_IssueDT'},
				{ name: 'EvnCytologicProto_MaterialDT'},
				{ name: 'EvnCytologicProto_MicroDescr'},
				{ name: 'EvnCytologicProto_Num'},
				{ name: 'EvnCytologicProto_Ser'},
				{ name: 'EvnCytologicProto_SurveyDT'},
				{ name: 'EvnCytologicProto_id'},
				{ name: 'EvnCytologicProto_setDate'},
				{ name: 'EvnCytologicProto_setTime'},
				{ name: 'EvnDirectionCytologic_SerNum'},
				{ name: 'EvnDirectionCytologic_id'},
				{ name: 'LabMedPersonal_id'},
				{ name: 'LpuSection_id'},
				{ name: 'Mkb10Code_id'},
				{ name: 'Numerator_id'},
				{ name: 'OnkoDiag_id'},
				{ name: 'PayType_id'},
				{ name: 'PersonEvn_id'},
				{ name: 'Person_id'},
				{ name: 'Server_id'},
				{ name: 'UslugaComplex_id'},
				{ name: 'DrugQualityCytologic_id'},
				{ name: 'ScreeningSmearType_id'},
				{ name: 'EvnCytologicProto_Cytogram'},
				{ name: 'EvnCytologicProto_Description'},
				{ name: 'CytologicMaterialPathology_id'},
				{ name: 'EvnCytologicProto_Degree'},
				{ name: 'EvnCytologicProto_Etiologic'},
				{ name: 'EvnCytologicProto_OtherConcl'},
				{ name: 'EvnCytologicProto_MoreClar'},
			]),
			region: 'center',
			url: '/?c=EvnCytologicProto&m=saveEvnCytologicProto',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnCytologicProto_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionCytologic_id',
				value: 0,
				xtype: 'hidden'
			}, {
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
				name: 'Numerator_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'LpuSection_id',
				value: 0,
				xtype: 'hidden'
			},{
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: 'Направление',
				listeners: {
					'keydown': function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.F4:
								if ( this.action == 'view' || inp.disabled ) {
									return false;
								}

								e.stopEvent();
								this.openEvnDirectionCytologicListWindow();
							break;

							case Ext.EventObject.TAB:
								if ( e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							break;
						}
					}.createDelegate(this)
				},
				name: 'EvnDirectionCytologic_SerNum',
				onTriggerClick: function() {
					this.openEvnDirectionCytologicListWindow();
				}.createDelegate(this),
				readOnly: true,
				tabIndex: formTabIndex++,
				triggerClass: 'x-form-search-trigger',
				width: 300,
				xtype: 'trigger'
			}, {
				border: false,
				allowBlank: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						disabled: false,
						fieldLabel: langs('Дата поступления материала'),
						format: 'd.m.Y',
						name: 'EvnCytologicProto_MaterialDT',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: formTabIndex++,
						width: 100,
						xtype: 'swdatefield'
					}]
				}]
			},
				{
					border: true,
					layout: 'column',
					width: 700,
					bodyStyle:'padding: 10px 0 10px 0; margin: 10px 0 10px 0',
					items: [
						{
							border: false,
							layout: 'form',
							items: [{
								allowDecimals: false,
								allowNegative: false,
								disabled: true,
								allowBlank: false,
								fieldLabel: 'Серия исследования',
								name: 'EvnCytologicProto_Ser',
								width: 100,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							labelWidth: 150,
							layout: 'form',
							items: [{
								allowBlank: false,
								allowDecimals: false,
								allowNegative: false,
								disabled: true,
								fieldLabel: 'Регистрационный номер',
								name: 'EvnCytologicProto_Num',
								width: 100,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							labelWidth: 100,
							layout: 'form',
							bodyStyle:'padding-left: 10px;',
							items: [{
								handler: function() {
									this.setEvnCytologicProtoNumber();
								}.createDelegate(this),
								text: langs(''),
								width: 100,
								icon: 'img/icons/add16.png',
								iconCls: 'x-btn-text',
								id: 'ECPEW_generate_SeriesNumber',
								name: 'generate_SeriesNumber',
								xtype: 'button'
							}]
						},
					]
			},  {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'PayType',
				hiddenName: 'PayType_id',
				fieldLabel: 'Вид оплаты',
				width: 150,
			},
			new sw.Promed.Panel({
				autoHeight: true,
				border: true,
				collapsible: true,
				id: 'ECPEF_EvnCytologicDescriptionPanel', 
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: '1. Описание',
				items: [{
					autoHeight: true,
					bodyStyle: 'padding: 5px;',
					border: false,
					layout: 'form',
					region: 'north',
					items: [{
						fieldLabel: langs('Исследование'),
						//UslugaComplexAttributeType = cytology
						// Ext.getCmp('usl_comb').setAllowedUslugaComplexAttributeList([ 'lab','func','endoscop','laser','ray','inject','xray','registry', 'cytology']);
						// Ext.getCmp('usl_comb').setUslugaCategoryList(['gost2011']);
						hiddenName: 'UslugaComplex_id',
						tabIndex: formTabIndex++,
						width: 430,
						listWidth: 500,
						disabled: true,
						xtype: 'swuslugacomplexnewcombo',
					}, {
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: langs('Количество'),
						name: 'EvnCytologicProto_CountUsluga',
						tabIndex: formTabIndex++,
						width: 100,
						minText: 'Введите целое число больше нуля',
						minValue: 1,
						xtype: 'numberfield'
					}, {
						allowBlank: false,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: langs('Количество стекол'),
						name: 'EvnCytologicProto_CountGlass',
						tabIndex: formTabIndex++,
						width: 100,
						minText: 'Введите целое число больше нуля',
						minValue: 1,
						xtype: 'numberfield'
					}, {
						allowBlank: false,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: langs('Количество флаконов'),
						name: 'EvnCytologicProto_CountFlacon',
						tabIndex: formTabIndex++,
						width: 100,
						minText: 'Введите целое число больше нуля',
						minValue: 1,
						xtype: 'numberfield'
					}, {
						allowBlank: false,
						disabled: false,
						fieldLabel: langs('Дата выдачи врачу'),
						format: 'd.m.Y',
						name: 'EvnCytologicProto_IssueDT',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: formTabIndex++,
						width: 100,
						xtype: 'swdatefield'
					}, 
					_this.PrescrReactionTypePanel,
					{
						allowBlank: false,
						fieldLabel: langs('Цитологический диагноз'), //Морфологическая классификация новообразований (МКБ-0)
						hiddenName: 'OnkoDiag_id',
						listWidth: 580,
						tabIndex: formTabIndex++,
						width: 430,
						xtype: 'swonkodiagcombo'
					}, {
						allowBlank: true,
						fieldLabel: langs('Микроскопическое описание'),
						height: 100,
						name: 'EvnCytologicProto_MicroDescr',
						tabIndex: formTabIndex++,
						width: 430,
						maxLength: 1000,
						xtype: 'textarea'
					}, {				
						fieldLabel: 'Диагноз по МКБ-10',
						hiddenName: 'Mkb10Code_id',              
						allowBlank : false,
						width: 430,
						xtype: 'swdiagcombo'				                    										
					}, {
						allowBlank: false,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: langs('Категория сложности'),
						name: 'EvnCytologicProto_Difficulty',
						tabIndex: formTabIndex++,
						width: 100,
						minText: 'Число от 1 до 5',
						maxText: 'Число от 1 до 5',
						minValue: 1,
						maxValue: 5,
						xtype: 'numberfield'
					}, {
						allowBlank: false,
						fieldLabel: langs('Заключение'),
						height: 100,
						name: 'EvnCytologicProto_Conclusion',
						tabIndex: formTabIndex++,
						width: 430,
						maxLength: 1000,
						xtype: 'textarea'
					}, {
						allowBlank: false,
						disabled: false,
						fieldLabel: langs('Дата проведения исследования'),
						format: 'd.m.Y',
						name: 'EvnCytologicProto_SurveyDT',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: formTabIndex++,
						width: 100,
						xtype: 'swdatefield'
					}, 
					_this.MedPersonalPanel,
					{
						width: 430,
						hiddenName: 'LabMedPersonal_id',
						fieldLabel: langs('Лаборант'),
						lastQuery: '',
						listWidth: 650,
						editable: true,
						tabIndex: formTabIndex++,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
					},
					{
						autoHeight: true,
						width: 800,
						style: 'padding: 0; padding-top: 5px; margin: 0',
						title: langs('Цитограмма'),
						id: 'ECPEW_BlockCytogram',
						xtype: 'fieldset',
						items: [{
							layout: 'form',
							border: false,
							items: [
								{
									xtype: 'swdrugqualitycytologiccombo',
									mode: 'local',
									allowBlank: true,
									hiddenName: 'DrugQualityCytologic_id',
								},
								{
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'swscreeningsmeartypecombo',
													mode: 'local',
													allowBlank: true,
													autoLoad: true,
													hiddenName: 'ScreeningSmearType_id',
													listeners: {
														select: function(combo, rec) {
															var base_form = _this.FormPanel.getForm();
															var evnCytologicProto_Cytogram = base_form.findField('EvnCytologicProto_Cytogram');
															var code = (rec) ? rec.get('ScreeningSmearType_Code') : null;
															if(evnCytologicProto_Cytogram && code == 1){
																evnCytologicProto_Cytogram.showContainer();
															}else{
																evnCytologicProto_Cytogram.hideContainer();
																evnCytologicProto_Cytogram.setValue();
															}
														}
													}
												}
											]
										},
										{
											layout: 'form',
											border: false,
											style: 'padding-left: 10px; margin: 0',
											items: [{
												// fieldLabel: '',
												hideLabel: true,
												name: 'EvnCytologicProto_Cytogram',
												width: 350,
												xtype: 'textfield'
											}]
										}
									]
								},
								{
									fieldLabel: langs('Описание'),
									name: 'EvnCytologicProto_Description',
									width: 550,
									xtype: 'textfield'
								},
								{
									xtype: 'swcytologicmaterialpathologycombo',
									width: 550,
									autoLoad: true,
									allowBlank: true,
									hiddenName: 'CytologicMaterialPathology_id',
									listeners: {
										select: function(combo, rec) {
											var base_form = _this.FormPanel.getForm();
											var evnCytologicProto_Degree = base_form.findField('EvnCytologicProto_Degree');
											var evnCytologicProto_Etiologic = base_form.findField('EvnCytologicProto_Etiologic');
											var hidden = (rec && rec.get('CytologicMaterialPathology_Code') == 3) ? true : false;
											evnCytologicProto_Degree.setContainerVisible(hidden);
											evnCytologicProto_Etiologic.setContainerVisible(hidden);
											if(!hidden){
												evnCytologicProto_Degree.setValue();
												evnCytologicProto_Etiologic.setValue();
											}
										}
									}
								},
								{
									fieldLabel: langs('Степень выраженности'),
									name: 'EvnCytologicProto_Degree',
									width: 550,
									xtype: 'textfield'
								},
								{
									fieldLabel: langs('Этиологический фактор'),
									name: 'EvnCytologicProto_Etiologic',
									width: 550,
									xtype: 'textfield'
								},
								{
									fieldLabel: langs('Другие типы цитологических заключений'),
									name: 'EvnCytologicProto_OtherConcl',
									width: 550,
									xtype: 'textfield'
								},
								{
									fieldLabel: langs('Дополнительные уточнения'),
									name: 'EvnCytologicProto_MoreClar',
									width: 550,
									xtype: 'textfield'
								}
							]
						}]
					}
					]
				},
				new sw.Promed.Panel({
					border: true,
					collapsible: true,
					height: 150,
					id: 'ECPEF_EvnUslugaParPanel',
					layout: 'border',
					listeners: {
						'expand': function(panel) {
							if ( panel.isLoaded === false ) {
								panel.isLoaded = true;
								panel.findById('ECPEF_EvnUslugaParGrid').getGrid().getStore().load({
									params: {EvnDirection_id: this.FormPanel.getForm().findField('EvnDirectionCytologic_id').getValue()}
								});
							}
							panel.doLayout();
						}.createDelegate(this)
					},
					style: 'margin-bottom: 0.5em;',
					title: '2. Услуга',
					items: [new sw.Promed.ViewFrame({
						tbar: false,
						border: false,
						id: 'ECPEF_EvnUslugaParGrid',
						autoLoadData: false,
						useEmptyRecord: false,
						stringfields: [
							{ name: 'EvnUslugaPar_id', type: 'int', key: true },
							{ name: 'Person_id', type: 'int', hidden: true },
							{ name: 'Server_id', type: 'int', hidden: true },
							{ name: 'EvnUslugaPar_setDate', type: 'date', header: 'Дата',  width: 100},
							{ name: 'UslugaComplex_Code', type: 'string', header: 'Код', width: 150 },
							{ name: 'UslugaComplex_Name', type: 'string', header: 'Наименование', width: 550, id: 'autoexpand'}
						],
						region: 'center',
						stripeRows: true,
						actions: [
							{ name: 'action_add', handler: function() { this.openEvnUslugaParEditWindow('add'); }.createDelegate(this) },
							{ name: 'action_edit', handler: function() { this.openEvnUslugaParEditWindow('edit'); }.createDelegate(this) },
							{ name: 'action_view', handler: function() { this.openEvnUslugaParEditWindow('view'); }.createDelegate(this) },
							{ name: 'action_delete', handler: function(){ this.deleteEvnUslugaPar();}.createDelegate(this) },
							{ name: 'action_print', hidden: true, disabled: true},
							{ name: 'action_refresh', hidden: true, disabled: true}
						],
						onLoadData: function() {
							//this.getAction('action_add').setDisabled(this.getGrid().getStore().getCount() > 0);
	                    },
						dataUrl: '/?c=EvnUslugaPar&m=loadEvnUslugaParListByDirection'
					})]
				})
                ]
			}), 
			]
		});

		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				if ( this.action == 'view' ) {
					this.buttons[this.buttons.length - 1].focus();
				}
				else {
					this.FormPanel.getForm().findField('EvnDirectionCytologic_SerNum').focus(true);
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
			title: '<div>Загрузка...</div>',
			titleCollapse: true
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else {
						base_form.findField('EvnCytologicProto_MacroDescr').focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( !this.buttons[1].hidden ) {
						this.buttons[1].focus(true);
					}
					else {
						this.buttons[this.buttons.length - 2].focus(true);
					}
				}.createDelegate(this),
				tabIndex: formTabIndex++,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnCytologicProto();
				}.createDelegate(this),
				hidden: getRegionNick() == 'kz',
				iconCls: 'print16',
				onShiftTabAction: function() {
					if ( this.action != 'view' ) {
						this.buttons[0].focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 2].focus(true);
				}.createDelegate(this),
				tabIndex: formTabIndex++,
				text: BTN_FRMPRINT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[this.buttons.length - 2].focus(true);
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.FormPanel.getForm().findField('EvnDirectionCytologic_SerNum').focus(true);
					}
					else {
						this.buttons[1].focus(true);
					}
				}.createDelegate(this),
				tabIndex: formTabIndex++,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swEvnCytologicProtoEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('swEvnCytologicProtoEditWindow');

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
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'beforehide': function(win) {
			win.onCancelAction();
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('ECPEF_EvnCytologicDescriptionPanel').doLayout();
		},
		'restore': function(win) {
			win.fireEvent('maximize', win);
		}
	},
	maximizable: true,
	maximized: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onCancelAction: function() {
		var base_form = this.FormPanel.getForm();
		var evn_cytologic_proto_id = base_form.findField('EvnCytologicProto_id').getValue();

		if ( this.onCancelActionFlag == true && evn_cytologic_proto_id > 0 && this.action == 'add') {
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление протокола..." });
			loadMask.show();

			Ext.Ajax.request({
				failure: function(response, options) {
					loadMask.hide();
					sw.swMsg.alert('Ошибка', 'При удалении протокола патологогистологического исследования возникли ошибки [Тип ошибки: 2]');
					return false;
				},
				params: {
					EvnCytologicProto_id: evn_cytologic_proto_id
				},
				success: function(response, options) {
					loadMask.hide();

					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'При удалении протокола патологогистологического исследования возникли ошибки [Тип ошибки: 3]');
						return false;
					}
				},
				url: '/?c=EvnCytologicProto&m=deleteEvnCytologicProto'
			});
		}
	},
	onCancelActionFlag: true,
	onHide: Ext.emptyFn,
	openEvnDirectionCytologicListWindow: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();

		if ( base_form.findField('EvnDirectionCytologic_SerNum').disabled ) {
			return false;
		}

		if ( getWnd('swEvnDirectionCytologicListWindow').isVisible() ) {
			sw.swMsg.alert('Ошибка', 'Окно просмотра списка направлений уже открыто');
			return false;
		}

		var params = new Object();

		params.callback = function(data) {
			if ( !data ) return false;

			base_form.findField('EvnDirectionCytologic_id').setValue(data.EvnDirectionCytologic_id);
			base_form.findField('EvnDirectionCytologic_SerNum').setValue(data.EvnDirectionCytologic_Ser + ' ' + data.EvnDirectionCytologic_Num + ', ' + Ext.util.Format.date(data.EvnDirectionCytologic_setDate, 'd.m.Y'));

			var comboUslugaComplex = base_form.findField('UslugaComplex_id');
			comboUslugaComplex.setValue(data.UslugaComplex_id);
			var comboPayType = base_form.findField('PayType_id');
			if(data.PayType_id) comboPayType.setValue(data.PayType_id);
			if(comboUslugaComplex.getStore().getCount() == 0) this.loadComboUslugaComplex();
			win.findById('ECPEF_EvnUslugaParGrid').loadData({globalFilters: {EvnDirection_id: data.EvnDirectionCytologic_id}});
			this.visibleBlockCytogram();
		}.createDelegate(this);
		params.onHide = function() {
			base_form.findField('EvnDirectionCytologic_SerNum').focus();
		}.createDelegate(this);
 
		params.formParams = {
			'PersonEvn_id': win.PersonEvn_id,
			'Person_id': win.Person_id,
			'Server_id': win.Server_id
		};
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');

		getWnd('swEvnDirectionCytologicListWindow').show(params);
	},
	plain: true,
	printEvnCytologicProto: function() {
		switch ( this.action ) {
			case 'add':
			case 'edit':
				this.doSave({
					print: true
				});
			break;

			case 'view':
				this.printProto();
			break;
		}
	},
	printProto: function(){
		var evn_cytologic_proto_id = this.FormPanel.getForm().findField('EvnCytologicProto_id').getValue();
		var report_file_name = 'f203u02_CytologicProtocol.rptdesign';
		printBirt({
			'Report_FileName': report_file_name,
			'Report_Params': '&paramEvnCytologicProto=' + evn_cytologic_proto_id,
			'Report_Format': 'pdf'
		});
	},
	resizable: true,
	setEvnCytologicProtoNumber: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		// base_form.findField('EvnCytologicProto_Num').setValue(3);
		// base_form.findField('EvnCytologicProto_Ser').setValue(570500);
		// return false;
		win.loadMask.show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				var err_flag = true;
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj.EvnCytologicProto_Num && response_obj.EvnCytologicProto_Ser){
						base_form.findField('EvnCytologicProto_Num').setValue(response_obj.EvnCytologicProto_Num);
						base_form.findField('EvnCytologicProto_Ser').setValue(response_obj.EvnCytologicProto_Ser);
						if(response_obj.Numerator_id) base_form.findField('Numerator_id').setValue(response_obj.Numerator_id);
						err_flag = false;
					}
				}
				if(err_flag) {
					sw.swMsg.alert('Ошибка', 'Ошибка при определении номера протокола', function() { base_form.findField('EvnCytologicProto_Num').focus(true); }.createDelegate(this) );
				}
				win.loadMask.hide();
			}.createDelegate(this),
			url: '/?c=EvnCytologicProto&m=getEvnCytologicProtoNumber'
		});
	},
	clearComboUslugaComplex: function(){
		//my
		var base_form = this.FormPanel.getForm();
		var comboUslugaComplex = base_form.findField('UslugaComplex_id');

		comboUslugaComplex.clearValue();
		comboUslugaComplex.getStore().removeAll();
		this.visibleBlockCytogram();
	},
	loadComboUslugaComplex: function() {
		var base_form = this.FormPanel.getForm();
		var comboUslugaComplex = base_form.findField('UslugaComplex_id');
		var usluga_complex_id = comboUslugaComplex.getValue();
		this.loadMask.show();
		comboUslugaComplex.getStore().load({
			callback: function(){
				if(usluga_complex_id) comboUslugaComplex.setValue(usluga_complex_id);
				this.loadMask.hide();
				this.visibleBlockCytogram();
			}.createDelegate(this)
		});
	},
	visibleBlockCytogram: function(){
		var show = false;
		var blockCytogram = Ext.getCmp('ECPEW_BlockCytogram');
		var base_form = this.FormPanel.getForm();
		var comboUslugaComplex = base_form.findField('UslugaComplex_id');
		var uslugaComplexValue = comboUslugaComplex.getValue();
		if(uslugaComplexValue){
			var record = comboUslugaComplex.findRecord('UslugaComplex_id', uslugaComplexValue);
			var attributeList = (record) ? record.get('UslugaComplex_AttributeList').split(',') : [];
			show = 'Cytogram'.inlist(attributeList);
		}
		
		if(show){
			blockCytogram.show();
		}else{
			blockCytogram.hide();
		}

		if(blockCytogram.isVisible()){
			var evnCytologicProto_Cytogram = base_form.findField('EvnCytologicProto_Cytogram');
			var screeningSmearTypeCombo = base_form.findField('ScreeningSmearType_id');
			if(screeningSmearTypeCombo.getFieldValue('ScreeningSmearType_Code') == 1){
				evnCytologicProto_Cytogram.showContainer();
			}else{
				evnCytologicProto_Cytogram.hideContainer();
			}
			
			var comboCytologicMaterialPathology = base_form.findField('CytologicMaterialPathology_id');
			var rec = comboCytologicMaterialPathology.findRecord('CytologicMaterialPathology_id', comboCytologicMaterialPathology.getValue());
			comboCytologicMaterialPathology.fireEvent('select', comboCytologicMaterialPathology, rec);
		}
	},
	clearBlockCytogram: function(action){
		var action = action || null;
		var blockCytogram = Ext.getCmp('ECPEW_BlockCytogram');
		var clear = (action == 'save' && blockCytogram.isVisible()) ? false : true;
		if(!clear) return true;
		var base_form = this.FormPanel.getForm();
		var arr = [
			'DrugQualityCytologic_id',
			'ScreeningSmearType_id',
			'EvnCytologicProto_Cytogram',
			'EvnCytologicProto_Description',
			'CytologicMaterialPathology_id',
			'EvnCytologicProto_Degree',
			'EvnCytologicProto_Etiologic',
			'EvnCytologicProto_OtherConcl',
			'EvnCytologicProto_MoreClar',
		];
		arr.forEach(function(elem){
			var f = base_form.findField(elem);
			if(f){
				if(f.xtype == 'textfield') {
					f.setValue();
				}else{
					f.clearValue();
				}
			}
		});
	},
	MaskConstr: function(elem, msg){
		this.msg = msg || LOAD_WAIT;
		this.count = 0;
		this.m = new Ext.LoadMask(elem.getEl(), { msg: this.msg });
		this.hide = function(){
			this.count--;
			if(this.count <= 0) {
				this.m.hide();
				var form_window = Ext.getCmp('swEvnCytologicProtoEditWindow');
				if(form_window.action == 'add'){
					var base_form = form_window.FormPanel.getForm();
					base_form.isValid()
				}
			}
		}
		this.killer = function(){
			this.m.hide();
		}
		this.show = function(){
			if(this.count == 0) this.m.show();
			this.count++;
		}
	},
	loadMask: {},
	saveMask: {},
	show: function() {
		sw.Promed.swEvnCytologicProtoEditWindow.superclass.show.apply(this, arguments);
		this.loadMask = new this.MaskConstr(this, LOAD_WAIT);
		this.saveMask = new this.MaskConstr(this, "Подождите, идет сохранение протокола...");

		this.findById('ECPEF_EvnCytologicDescriptionPanel').expand();

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.PrescrReactionTypePanel.resetFieldSets();
		this.MedPersonalPanel.resetFieldSets();
		base_form.findField('LabMedPersonal_id').getStore().removeAll();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.onCancelActionFlag = true;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.UserMedStaffFact_id = null;
		this.UserMedStaffFactList = new Array();

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if(arguments[0].formParams.PersonEvn_id) {
			this.PersonEvn_id = arguments[0].formParams.PersonEvn_id;
		}

		if(arguments[0].formParams.Person_id) {
			this.Person_id = arguments[0].formParams.Person_id;
		}

		if(arguments[0].formParams.Server_id) {
			this.Server_id = arguments[0].formParams.Server_id;
		}

		this.curentMedStaffFactByUser = (arguments[0].curentMedStaffFactByUser) ? arguments[0].curentMedStaffFactByUser : sw.Promed.MedStaffFactByUser.current;
		// определенный медстафффакт
		if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 ) {
			this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}
		// если в настройках есть medstafffact, то имеем список мест работы
		else if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 ) {
			this.UserMedStaffFactList = Ext.globalOptions.globals['medstafffact'];
		}

		base_form.setValues(arguments[0].formParams);

		//Иследование
		this.clearComboUslugaComplex();
		var comboUslugaComplex = base_form.findField('UslugaComplex_id');
		comboUslugaComplex.setAllowedUslugaComplexAttributeList(['cytology']);
		
		this.findById('ECPEF_EvnUslugaParPanel').doLayout();

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

		//var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		this.loadMask.show();
		var that = this;
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_CYTOLOGIC_EHPEFADD);
				this.enableEdit(true);

				LoadEmptyRow(this.findById('ECPEF_EvnUslugaParGrid').getGrid());  //Добавляем пустую строку в grid
				this.findById('ECPEF_EvnCytologicDescriptionPanel').isLoaded = true;
				Ext.getCmp('ECPEW_generate_SeriesNumber').onShow();

				this.loadMask.show();
				setCurrentDateTime({
					callback: function() {
						that.loadMask.hide();
						// base_form.clearInvalid();
						// base_form.findField('EvnDirectionCytologic_SerNum').focus(true, 250);
						base_form.findField('EvnCytologicProto_IssueDT').setValue(Ext.globalOptions.globals.date);
						base_form.findField('EvnCytologicProto_SurveyDT').setValue(Ext.globalOptions.globals.date);
					}.createDelegate(this),
					dateField: base_form.findField('EvnCytologicProto_MaterialDT'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					windowId: this.id
				});

				// Генерируем серию протокола
				// В Екб серия через нумератор
				/*
				if (getRegionNick() != 'ekb') {
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

							base_form.findField('EvnCytologicProto_Ser').setValue(serial);
						}
					});
				}
				*/
				// Получаем номер направления
				// this.setEvnCytologicProtoNumber();

				this.loadMask.hide();
				base_form.clearInvalid();
						
				this.findById('ECPEF_EvnUslugaParGrid').gFilters = {EvnDirection_id: this.FormPanel.getForm().findField('EvnDirectionCytologic_id').getValue()};
				this.findById('ECPEF_EvnUslugaParGrid').loadData();

				this.PrescrReactionTypePanel.addFieldSet();
				this.MedPersonalPanel.addFieldSet();

				base_form.findField('EvnDirectionCytologic_SerNum').focus(true, 250);
				break;

			case 'edit':
			case 'view':
				var evn_cytologic_proto_id = base_form.findField('EvnCytologicProto_id').getValue();

				if ( !evn_cytologic_proto_id ) {
					this.loadMask.hide();
					this.hide();
					return false;
				}
				Ext.getCmp('ECPEW_generate_SeriesNumber').onHide();
				base_form.load({
					failure: function() {
						this.loadMask.hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnCytologicProto_id': evn_cytologic_proto_id
					},
					success: function(form, act) {
						var response_obj = Ext.util.JSON.decode(act.response.responseText);

						if (response_obj[0].accessType == 'view') {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_CYTOLOGIC_EHPEFEDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_CYTOLOGIC_EHPEFVIEW);
							this.enableEdit(false);
							this.findById('ECPEF_EvnUslugaParGrid').getGrid().getTopToolbar().items.items[0].disable();
						}

						if ( this.action == 'edit' ) {
							setCurrentDateTime({
								dateField: base_form.findField('EvnCytologicProto_MaterialDT'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: this.id
							});
						}

						this.findById('ECPEF_EvnCytologicDescriptionPanel').isLoaded = false;
						this.findById('ECPEF_EvnCytologicDescriptionPanel').fireEvent('expand', this.findById('ECPEF_EvnCytologicDescriptionPanel'));

						var
							diag_id = response_obj[0].Mkb10Code_id,
							index,
							med_personal_id = response_obj[0].MedPersonal_id,
							record;

						if ( !Ext.isEmpty(response_obj[0].PrescrReactionType_ids) ) {
							this.PrescrReactionTypePanel.setIds(response_obj[0].PrescrReactionType_ids);
						}
						else {
							this.PrescrReactionTypePanel.addFieldSet();
						}

						if ( !Ext.isEmpty(response_obj[0].MedStaffFact_ids) ) {
							this.MedPersonalPanel.setIds(response_obj[0].MedStaffFact_ids);
						}
						else {
							this.MedPersonalPanel.addFieldSet();
						}
						
						this.setLabMedPersonal();
						this.loadComboUslugaComplex();

						if ( diag_id != null && Number(diag_id) > 0 ) {
							var comboMkb10Code = base_form.findField('Mkb10Code_id');
							comboMkb10Code.getStore().load({
								callback: function() {
									comboMkb10Code.getStore().each(function(rec) {
										if ( rec.get('Diag_id') == diag_id ) {
											comboMkb10Code.fireEvent('select', comboMkb10Code, rec);
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
							});
						}

						this.loadMask.hide();

						base_form.clearInvalid();
						
						this.findById('ECPEF_EvnUslugaParGrid').gFilters = {EvnDirection_id: this.FormPanel.getForm().findField('EvnDirectionCytologic_id').getValue()};
						this.findById('ECPEF_EvnUslugaParGrid').loadData();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnDirectionCytologic_SerNum').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnCytologicProto&m=loadEvnCytologicProtoEditForm'
				});
			break;

			default:
				this.loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 750
});