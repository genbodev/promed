/**
* swEvnHistologicProtoEditWindow - протокол патологогистологического исследования
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      PathoMorphology
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      28.08.2010
* @comment      Префикс для id компонентов EHPEF (EvnHistologicProtoEditForm)
*
*
* Использует: микроскопическое описание (swEvnHistologicMicroEditWindow)
*/

sw.Promed.swEvnHistologicProtoEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,	
	deleteEvnUslugaPar: function() {
		var grid = this.findById('EHPEF_EvnUslugaParGrid').getGrid();
		var view_frame = this.findById('EHPEF_EvnUslugaParGrid');

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
		var grid = this.findById('EHPEF_EvnUslugaParGrid').getGrid();
		var params = new Object();
		
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		
		if ( action == 'add' ) {		
			var form_params = {
				'Person_id': base_form.findField('Person_id').getValue(),
				'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
				'Server_id': base_form.findField('Server_id').getValue(),		
				'EvnDirection_id': base_form.findField('EvnDirectionHistologic_id').getValue(),
				'EvnUslugaPar_Kolvo': 1,
				'EvnUslugaPar_IsHistologic': 2
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
	deleteEvnHistologicMicro: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var grid = this.findById('EHPEF_EvnHistologicMicroGrid').getGrid();

		if ( !grid ) {
			sw.swMsg.alert('Ошибка', 'При удалении микроскопического описания препарата возникли ошибки [Тип ошибки: 1]');
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() ) {
			sw.swMsg.alert('Ошибка', 'Не выбрано микроскопическое описание препарата  из списка');
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var evn_histologic_micro_id = selected_record.get('EvnHistologicMicro_id');

		if ( evn_histologic_micro_id == null ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert('Ошибка', 'При удалении микроскопического описания препарата возникли ошибки [Тип ошибки: 2]');
						},
						params: {
							EvnHistologicMicro_id: evn_histologic_micro_id
						},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'При удалении микроскопического описания препарата возникли ошибки [Тип ошибки: 3]');
							}
							else {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						},
						url: '/?c=EvnHistologicMicro&m=deleteEvnHistologicMicro'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить микроскопическое описание препарата?',
			title: 'Вопрос'
		});
	},
	doSave: function(options) {
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

		params.EvnHistologicProto_Ser = base_form.findField('EvnHistologicProto_Ser').getValue();

		if ( base_form.findField('MedPersonal_id').disabled ) {
			params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		}

        if ( this.PrescrReactionTypePanel.isVisible()) {
        	params.PrescrReactionType_ids = this.PrescrReactionTypePanel.getIds();
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение протокола..." });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				this.onCancelActionFlag = true;
				loadMask.hide();

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
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnHistologicProto_id > 0 ) {
						base_form.findField('EvnHistologicProto_id').setValue(action.result.EvnHistologicProto_id);

						if ( options && typeof options.openChildWindow == 'function' && this.action == 'add' ) {
							this.onCancelActionFlag = true;
							options.openChildWindow();
						}
						else {
							var data = new Object();

							var med_personall_fio = '';

							base_form.findField('MedPersonal_id').getStore().each(function(rec) {
								if ( rec.get('MedPersonal_id') == base_form.findField('MedPersonal_id').getValue() ) {
									med_personall_fio = rec.get('MedPersonal_Fio');
								}
							});

							data.evnHistologicProtoData = {
								'EvnHistologicProto_id': base_form.findField('EvnHistologicProto_id').getValue(),
								'accessType': 'edit',
								'EvnDirectionHistologic_IsBad': 0,
								'Person_id': base_form.findField('Person_id').getValue(),
								'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
								'Server_id': base_form.findField('Server_id').getValue(),
								'EvnHistologicProto_Ser': base_form.findField('EvnHistologicProto_Ser').getValue(),
								'EvnHistologicProto_Num': base_form.findField('EvnHistologicProto_Num').getValue(),
								'EvnHistologicProto_didDate': base_form.findField('EvnHistologicProto_didDate').getValue(),
								// 'Lpu_Name': '',
								// 'LpuSection_Name': '',
								// 'EvnDirectionHistologic_NumCard': '',
								'Person_Surname': this.PersonInfo.getFieldValue('Person_Surname'),
								'Person_Firname': this.PersonInfo.getFieldValue('Person_Firname'),
								'Person_Secname': this.PersonInfo.getFieldValue('Person_Secname'),
								'Person_Birthday': this.PersonInfo.getFieldValue('Person_Birthday'),
								'MedPersonal_Fio': med_personall_fio
							};

							this.callback(data);

							if ( options && options.print ) {
								this.buttons[1].focus();
								if(getRegionNick() == 'kz'){
									printBirt({
										'Report_FileName': 'f014u.rptdesign',
										'Report_Params': '&ParamEvnHistologicProto_id=' + base_form.findField('EvnHistologicProto_id').getValue(),
										'Report_Format': 'pdf'
									});
								} else {
									printBirt({
										'Report_FileName': 'f014u_HistologicProtocol.rptdesign',
										'Report_Params': '&paramEvnHistologicProto=' + base_form.findField('EvnHistologicProto_id').getValue(),
										'Report_Format': 'pdf'
									});
								}
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
			'Diag_id',
			'EvnDirectionHistologic_SerNum',
			'EvnHistologicProto_BitCount',
			'EvnHistologicProto_BlockCount',
			'EvnHistologicProto_CutDate',
			'EvnHistologicProto_CutTime',
			'EvnHistologicProto_didDate',
			'EvnHistologicProto_HistologicConclusion',
			'EvnHistologicProto_IsDiag',
			'EvnHistologicProto_IsOper',
			'EvnHistologicProto_MacroDescr',
			'EvnHistologicProto_Num',
			'EvnHistologicProto_setDate',
			'EvnHistologicProto_setTime',
			'EvnHistologicProto_CategoryDiff',
			'MedPersonal_id',
			'MedPersonal_sid',
			'EvnHistologicProto_IsDelivSolFormalin',
			'MarkSavePack_id',
			'OnkoDiag_id',
			'EvnHistologicProto_IsPolluted',
			'EvnHistologicProtoBiopsy_setDate',
			'EvnHistologicProtoBiopsy_setTime'
		);
		var i = 0;

		if ( getRegionNick() == 'kz' ) {
			form_fields.push('EvnHistologicProto_Ser');
		}

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
			this.findById('EHPEF_EvnHistologicMicroGrid').setReadOnly(false);
			this.findById('EHPEF_EvnUslugaParGrid').setReadOnly(false);
		}
		else {
			this.buttons[0].hide();
            this.findById('EHPEF_EvnHistologicMicroGrid').setReadOnly(true);
            this.findById('EHPEF_EvnUslugaParGrid').setReadOnly(true);
		}
	},
	formStatus: 'edit',
	height: 550,
	id: 'EvnHistologicProtoEditWindow',
	setMedPersonal: function(onDate){
		var base_form = this.FormPanel.getForm(),
			med_personal_id = base_form.findField('MedPersonal_id').getValue(),
			med_personal_sid = base_form.findField('MedPersonal_sid').getValue(),
			med_stafffact_id = base_form.findField('MedStaffFact_id').getValue();

		var medstafffact_filter_params = {
			onDate: !Ext.isEmpty(onDate)?Ext.util.Format.date(onDate, 'd.m.Y'):getGlobalOptions().date,
			regionCode: getGlobalOptions().region.number
		};

		if ( this.action != 'view' ) {
			base_form.findField('MedPersonal_id').enable();
			base_form.findField('MedPersonal_sid').enable();
			base_form.findField('MedStaffFact_id').enable();
		}

		base_form.findField('MedPersonal_id').getStore().removeAll();
		base_form.findField('MedPersonal_sid').getStore().removeAll();
		base_form.findField('MedStaffFact_id').getStore().removeAll();

		setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

		base_form.findField('MedPersonal_sid').getStore().loadData(getMedPersonalListFromGlobal());
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		if ( this.action == 'add' ) {
			// фильтр или на конкретное место работы или на список мест работы
			if ( this.UserMedStaffFact_id ) {
				medstafffact_filter_params.id = this.UserMedStaffFact_id;
			}
			else if ( typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0 ) {
				medstafffact_filter_params.ids = this.UserMedStaffFactList;
			}
		}

		// загружаем локальный список мест работы
		setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

		base_form.findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());

		if ( base_form.findField('MedPersonal_id').getStore().getById(med_personal_id) ) {
			base_form.findField('MedPersonal_id').setValue(med_personal_id);
		}

		if ( base_form.findField('MedPersonal_sid').getStore().getById(med_personal_sid) ) {
			base_form.findField('MedPersonal_sid').setValue(med_personal_sid);
		}

		if ( base_form.findField('MedStaffFact_id').getStore().getById(med_stafffact_id) ) {
			base_form.findField('MedStaffFact_id').setValue(med_stafffact_id);
		}

		// Если задано место работы или список мест работы, то не даем редактировать поле "Врач"
		if ( this.action.toString().inlist([ 'add', 'edit' ]) && (this.UserMedStaffFact_id || (typeof this.UserMedStaffFactList == 'object' && this.UserMedStaffFactList.length > 0)) ) {
			//base_form.findField('MedPersonal_id').disable();

			if ( this.action == 'add' ) {
				base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getStore().getAt(0).get('MedPersonal_id'));
			}
		}

		//base_form.findField('Diag_id').setFilterByDate(onDate);
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
			border: false,
			count: 0,
			limit: -1,
			setBaseFilter: function(filterFn) {
				var base_form = _this.FormPanel.getForm();
				var container = _this.PrescrReactionTypePanel;
				container.baseFilter = filterFn;

				for (var num = 1; num <= container.count; num++) {
					var field = base_form.findField('PrescrReactionType_id_' + num);
					if (field) field.setBaseFilter(container.baseFilter);
				}
			},
			setAccess: function() {
				var base_form = _this.FormPanel.getForm();
				var container = _this.PrescrReactionTypePanel;

				for (var num = 1; num <= container.count; num++) {
					if ( _this.getAction() == 'view' ) {
						base_form.findField('PrescrReactionType_id_' + num).disable();
						container.findById('PrescrReactionTypeAddButton_' + num).hide();
						container.findById('PrescrReactionTypeDelButton_' + num).hide();
					}
					else {
						base_form.findField('PrescrReactionType_id_' + num).enable();
						container.findById('PrescrReactionTypeAddButton_' + num).show();
						container.findById('PrescrReactionTypeDelButton_' + num).show();

						if ( num != container.count ) {
							base_form.findField('PrescrReactionType_id_' + num).disable();
						}

						if ( num == 1 || num != container.count ) {
							container.findById('PrescrReactionTypeDelButton_' + num).hide();
						}

						if ( num < container.count || container.getLimit() == num ) {
							container.findById('PrescrReactionTypeAddButton_' + num).hide();
						}
					}
				}
			},
			getCount: function() {
				return this.count;
			},
			getLimit: function() {
				return this.limit;
			},
			getIds: function() {
				var base_form = _this.FormPanel.getForm();
				var container = _this.PrescrReactionTypePanel;
				var ids = [];

				for (var num = 1; num <= container.count; num++) {
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
				for (var num = 1; num <= count; num++) {
					container.deleteFieldSet(num);
				}
				container.count = 0;
			},
			deleteFieldSet: function(num) {
				var base_form = _this.FormPanel.getForm();
				var container = _this.PrescrReactionTypePanel;
				var panel = _this.PrescrReactionTypeBodyPanel;

				if (panel.findById('PrescrReactionTypeFieldSet_' + num)) {
					var field = base_form.findField('PrescrReactionType_id_' + num);
					base_form.items.removeKey(field.id);

					panel.remove('PrescrReactionTypeFieldSet_'+num);
					_this.doLayout();
					_this.syncShadow();
					_this.FormPanel.initFields();

					container.count--;
				}
			},
			addFieldSet: function(options) {
				var base_form = _this.FormPanel.getForm();
				var container = _this.PrescrReactionTypePanel;
				var panel = _this.PrescrReactionTypeBodyPanel;

				var ids = container.getIds();
				var usedValues = (!Ext.isEmpty(ids) ? ids.split(',') : []);

				container.count++;
				var num = container.count;

				if (!container.checkLimit()) {
					container.count--;
					return false;
				}

				var addButton = new Ext.Button({
					iconCls:'add16',
					handler: function() {
						if ( _this.PrescrReactionTypePanel.getCount() > 0 && !Ext.isEmpty(base_form.findField('PrescrReactionType_id_' + _this.PrescrReactionTypePanel.getCount())) && Ext.isEmpty(base_form.findField('PrescrReactionType_id_' + _this.PrescrReactionTypePanel.getCount()).getValue()) ) {
							return false;
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
							fieldLabel: (container.count == 1 ? langs('Назначенные окраски (реакции, определения)') : ''),
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
							addButton
						]
					}, {
						layout: 'form',
						border: false,
						items: [
							delButton
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
						},
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

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnHistologicProtoEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'Diag_id' },
				{ name: 'EvnDirectionHistologic_id' },
				{ name: 'EvnDirectionHistologic_SerNum' },
				{ name: 'EvnHistologicProto_BitCount' },
				{ name: 'EvnHistologicProto_BlockCount' },
				{ name: 'EvnHistologicProto_CutDate'},
				{ name: 'EvnHistologicProto_CutTime'},
				{ name: 'EvnHistologicProto_Comments' },
				{ name: 'EvnHistologicProto_didDate' },
				{ name: 'EvnHistologicProto_HistologicConclusion' },
				{ name: 'EvnHistologicProto_id' },
				{ name: 'EvnHistologicProto_IsDelivSolFormalin' },
				{ name: 'EvnHistologicProto_IsPolluted' },
				{ name: 'EvnHistologicProto_IsDiag' },
				{ name: 'EvnHistologicProto_IsOper' },
				{ name: 'EvnHistologicProto_MacroDescr' },
				{ name: 'EvnHistologicProto_Num' },
				{ name: 'EvnHistologicProto_Ser' },
				{ name: 'EvnHistologicProto_setDate' },
				{ name: 'EvnHistologicProto_setTime' },
				{ name: 'EvnHistologicProtoBiopsy_setDate' },
				{ name: 'EvnHistologicProtoBiopsy_setTime' },
				{ name: 'EvnHistologicProto_CategoryDiff' },
				{ name: 'MarkSavePack_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'MedPersonal_sid' },
				{ name: 'MedStaffFact_id' },
				{ name: 'OnkoDiag_id' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'PrescrReactionType_ids' },
				{ name: 'pmUser_Name' },
				{ name: 'Server_id' }
			]),
			region: 'center',
			url: '/?c=EvnHistologicProto&m=saveEvnHistologicProto',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnHistologicProto_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionHistologic_id',
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
				border: false,
				hidden: true,
				id: 'EHPEF_Caption',
				layout: 'form',
				xtype: 'panel',

				items: [{
					fieldLabel: 'Аннулировано',
					name: 'pmUser_Name',
					readOnly: true,
					style: 'color: #ff8870',
					width: 430,
					xtype: 'textfield'
				}]
			}, {
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
								this.openEvnDirectionHistologicListWindow();
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
				name: 'EvnDirectionHistologic_SerNum',
				onTriggerClick: function() {
					this.openEvnDirectionHistologicListWindow();
				}.createDelegate(this),
				readOnly: true,
				tabIndex: formTabIndex++,
				triggerClass: 'x-form-search-trigger',
				width: 300,
				xtype: 'trigger'
			}, {
				allowBlank: (getRegionNick() == 'kz'),
				allowDecimals: false,
				allowNegative: false,
				disabled: true,
				fieldLabel: 'Серия исследования',
				name: 'EvnHistologicProto_Ser',
				width: 100,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				enableKeyEvents: true,
				fieldLabel: 'Номер исследования',
				name: 'EvnHistologicProto_Num',
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'textfield'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: 'Дата поступления материала',
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								blockedDateAfterPersonDeath('personpanel', this.PersonInfo, field, newValue, oldValue);
							}.createDelegate(this)
						},
						name: 'EvnHistologicProto_setDate',
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
						allowBlank: !(getRegionNick().inlist(['ufa','vologda', 'kz', 'penza', 'perm'])),
						fieldLabel: 'Время',
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnHistologicProto_setTime',
						onTriggerClick: function() {
							var base_form = this.FormPanel.getForm();
							var time_field = base_form.findField('EvnHistologicProto_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								dateField: base_form.findField('EvnHistologicProto_setDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: this.id
							});
						}.createDelegate(this),
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: formTabIndex++,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: !(getRegionNick().inlist(['ufa','vologda', 'kz', 'penza', 'perm'])),
				comboSubject: 'YesNo',
				fieldLabel: langs('Материал доставлен в 10%-ном растворе нейтрального формалина'),
				hiddenName: 'EvnHistologicProto_IsDelivSolFormalin',
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: !(getRegionNick().inlist(['ufa','vologda', 'kz', 'penza', 'perm'])),
				comboSubject: 'YesNo',
				fieldLabel: langs('Загрязнен'),
				hiddenName: 'EvnHistologicProto_IsPolluted',
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: !(getRegionNick().inlist(['ufa','vologda', 'kz', 'penza', 'perm'])),
				comboSubject: 'MarkSavePack',
				fieldLabel: langs('Отметка о сохранности упаковки'),
				hiddenName: 'MarkSavePack_id',
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'swcommonsprcombo'
			}, {
				border: false,
				layout: 'column',
				hidden: !(getRegionNick().inlist(['ufa','vologda', 'kz', 'penza', 'perm'])),
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: !(getRegionNick().inlist(['vologda', 'kz', 'penza', 'perm'])),
						fieldLabel: 'Дата регистрации биопсийного (операционного материала)',
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								blockedDateAfterPersonDeath('personpanel', this.PersonInfo, field, newValue, oldValue);
							}.createDelegate(this)
						},
						name: 'EvnHistologicProtoBiopsy_setDate',
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
						allowBlank: !(getRegionNick().inlist(['vologda', 'kz', 'penza', 'perm'])),
						fieldLabel: 'Время',
						listeners: {
							'keydown': function (inp, e) {
								if ( e.getKey() == Ext.EventObject.F4 ) {
									e.stopEvent();
									inp.onTriggerClick();
								}
							}
						},
						name: 'EvnHistologicProtoBiopsy_setTime',
						onTriggerClick: function() {
							var base_form = this.FormPanel.getForm();
							var time_field = base_form.findField('EvnHistologicProtoBiopsy_setTime');

							if ( time_field.disabled ) {
								return false;
							}

							setCurrentDateTime({
								dateField: base_form.findField('EvnHistologicProtoBiopsy_setDate'),
								loadMask: true,
								setDate: true,
								setDateMaxValue: true,
								setDateMinValue: false,
								setTime: true,
								timeField: time_field,
								windowId: this.id
							});
						}.createDelegate(this),
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						tabIndex: formTabIndex++,
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}]
				}]
			}, {
				allowBlank: false,
				comboSubject: 'YesNo',
				fieldLabel: 'Биопсия диагностическая',
				hiddenName: 'EvnHistologicProto_IsDiag',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var record = combo.getStore().getById(newValue);
						var yes_no_code = -1;

						if ( record ) {
							switch ( record.get('YesNo_Code') ) {
								case 0:
									yes_no_code = 1;
								break;

								case 1:
									yes_no_code = 0;
								break;
							}
						}

						var index = base_form.findField('EvnHistologicProto_IsOper').getStore().findBy(function(rec) {
							if ( rec.get('YesNo_Code') == yes_no_code ) {
								return true;
							}
							else {
								return false;
							}
						});

						record = base_form.findField('EvnHistologicProto_IsOper').getStore().getAt(index);

						if ( record ) {
							base_form.findField('EvnHistologicProto_IsOper').setValue(record.get('YesNo_id'));
						}
					}.createDelegate(this)
				},
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'YesNo',
				fieldLabel: 'Операционный материал',
				hiddenName: 'EvnHistologicProto_IsOper',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var record = combo.getStore().getById(newValue);
						var yes_no_code = -1;

						if ( record ) {
							switch ( record.get('YesNo_Code') ) {
								case 0:
									yes_no_code = 1;
								break;

								case 1:
									yes_no_code = 0;
								break;
							}
						}

						var index = base_form.findField('EvnHistologicProto_IsDiag').getStore().findBy(function(rec) {
							if ( rec.get('YesNo_Code') == yes_no_code ) {
								return true;
							}
							else {
								return false;
							}
						});

						record = base_form.findField('EvnHistologicProto_IsDiag').getStore().getAt(index);

						if ( record ) {
							base_form.findField('EvnHistologicProto_IsDiag').setValue(record.get('YesNo_id'));
						}
					}.createDelegate(this)
				},
				tabIndex: formTabIndex++,
				width: 100,
				xtype: 'swcommonsprcombo'
			}, {
				xtype: 'numberfield',
				name: 'EvnHistologicProto_CategoryDiff',
				fieldLabel: 'Сложность',
				allowDecimals: false,
				tabIndex: formTabIndex++,
				value: 1,
				width: 100,
				maxValue: 5,
				minValue: 1
			},
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: 'EHPEF_EvnHistologicProtoConclusionPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						// this.findById('EvnPLEditForm').getForm().findField('PrehospDirect_id').focus(true);
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: '1. Диагноз',
				items: [{
					allowBlank: !(getRegionNick().inlist(['ufa','vologda', 'kz', 'penza', 'perm'])),
					fieldLabel: 'Патологогистологическое заключение (диагноз)',
					height: 100,
					name: 'EvnHistologicProto_HistologicConclusion',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}, {
					allowBlank: !(getRegionNick().inlist(['ufa','vologda', 'kz', 'penza', 'perm'])),
					hiddenName: 'Diag_id',
					listWidth: 580,
					tabIndex: formTabIndex++,
					width: 430,
					validateOnBlur: true,
					checkAccessRights: true,
					xtype: 'swdiagcombo'
				}, {
					fieldLabel: langs('Морфологический код МКБ-О'),
					hiddenName: 'OnkoDiag_id',
					listWidth: 580,
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swonkodiagcombo'
				}, {
					fieldLabel: 'Комментарии к заключению и рекомендации',
					height: 100,
					name: 'EvnHistologicProto_Comments',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}, {
					fieldLabel: 'Дата исследования',
					format: 'd.m.Y',
					listeners: {
						'change': function(field, newValue, oldValue) {

							var base_form = this.FormPanel.getForm();

							var med_personal_id = base_form.findField('MedPersonal_id').getValue();
							var med_personal_sid = base_form.findField('MedPersonal_sid').getValue();

							base_form.findField('MedPersonal_id').clearValue();
							base_form.findField('MedPersonal_sid').clearValue();
							base_form.findField('MedStaffFact_id').clearValue();

							if ( !newValue ) {
								base_form.findField('MedPersonal_id').disable();
								base_form.findField('MedPersonal_sid').disable();
								base_form.findField('MedStaffFact_id').disable();
								return false;
							}

							_this.setMedPersonal(newValue);
						}.createDelegate(this)
					},
					name: 'EvnHistologicProto_didDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					selectOnFocus: true,
					tabIndex: formTabIndex++,
					width: 100,
					xtype: 'swdatefield'
				}, {
					allowBlank: false,
					codeField: 'MedPersonal_TabCode',
					displayField: 'MedPersonal_Fio',
					enableKeyEvents: true,
					fieldLabel: 'Патологоанатом',
					hiddenName: 'MedPersonal_id',
					listWidth: 650,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'MedPersonal_id'
						}, [
							{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
							{ name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
							{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' }
						]),
						sortInfo: {
							field: 'MedPersonal_Fio'
						},
						url: C_MP_LOADLIST
					}),
					listeners: {
						'change': function (field, newValue, oldValue) {
							_this.setMedPersonal(_this.FormPanel.getForm().findField('EvnHistologicProto_didDate').getValue());
						}
					},
					tabIndex: formTabIndex++,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><td style="width: 50px"><font color="red">{MedPersonal_TabCode}</font></td><td><h3>{MedPersonal_Fio}&nbsp;</h3></td></tr></table>',
						'</div></tpl>'
					),
					valueField: 'MedPersonal_id',
					width: 430,
					xtype: 'swbaselocalcombo'
				}, {
					codeField: 'MedPersonal_TabCode',
					displayField: 'MedPersonal_Fio',
					enableKeyEvents: true,
					fieldLabel: 'Лаборант',
					hiddenName: 'MedPersonal_sid',
					listWidth: 650,
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'MedPersonal_id'
						}, [
							{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
							{ name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
							{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' }
						]),
						sortInfo: {
							field: 'MedPersonal_Fio'
						},
						url: C_MP_LOADLIST
					}),
					tabIndex: formTabIndex++,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><td style="width: 50px"><font color="red">{MedPersonal_TabCode}</font></td><td><h3>{MedPersonal_Fio}&nbsp;</h3></td></tr></table>',
						'</div></tpl>'
					),
					valueField: 'MedPersonal_id',
					width: 430,
					xtype: 'swbaselocalcombo'
				}, {
					fieldLabel:langs('Врач-специалист, осуществляющий консультирование'),
					hiddenName: 'MedStaffFact_id',
					listWidth: 650,
					width: 430,
					xtype: 'swmedstafffactglobalcombo',
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;">',
						'<td>',
						'<div style="font-weight: bold;">{MedPersonal_Fio}</div>',
						'<div style="font-size: 10px;">{PostMed_Name}</div>',
						'</td>',
						'</tr></table>',
						'</div></tpl>'
					),
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				border: true,
				collapsible: true,
				id: 'EHPEF_EvnHistologicMicroPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById('EHPEF_EvnHistologicMicroGrid').getGrid().getStore().load({
								params: {
									EvnHistologicProto_id: this.FormPanel.getForm().findField('EvnHistologicProto_id').getValue()
								}
							});
						}
						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: '2. Описание',
				items: [{
					autoHeight: true,
					bodyStyle: 'padding-top: 5px;',
					border: false,
					layout: 'form',
					region: 'north',
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							hidden: getRegionNick() == 'kz',
							layout: 'form',
							items: [{
								allowBlank: !(getRegionNick().inlist(['ufa','vologda', 'kz', 'penza', 'perm'])),
								fieldLabel: 'Дата вырезки',
								format: 'd.m.Y',
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = this.FormPanel.getForm();

										if (newValue) {
											base_form.findField('EvnHistologicProto_BitCount').enable();
											base_form.findField('EvnHistologicProto_BlockCount').enable();
											if (!Ext.isEmpty(base_form.findField('PrescrReactionType_id_1'))) {
												base_form.findField('PrescrReactionType_id_1').enable();
											}
										} else {
											this.PrescrReactionTypePanel.resetFieldSets();
											this.PrescrReactionTypePanel.addFieldSet();
											base_form.findField('EvnHistologicProto_BitCount').setValue('');
											base_form.findField('EvnHistologicProto_BlockCount').setValue('');


											base_form.findField('EvnHistologicProto_BitCount').disable();
											base_form.findField('EvnHistologicProto_BlockCount').disable();
											if (!Ext.isEmpty(base_form.findField('PrescrReactionType_id_1'))) {
												base_form.findField('PrescrReactionType_id_1').disable();
											}
										}
									}.createDelegate(this)
								},
								name: 'EvnHistologicProto_CutDate',
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
							hidden: getRegionNick() == 'kz',
							items: [{
								allowBlank: !(getRegionNick().inlist(['ufa','vologda', 'kz', 'penza', 'perm'])),
								fieldLabel: 'Время',
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								name: 'EvnHistologicProto_CutTime',
								onTriggerClick: function() {
									var base_form = this.FormPanel.getForm();
									var time_field = base_form.findField('EvnHistologicProto_CutTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnHistologicProto_CutDate'),
										loadMask: true,
										setDate: true,
										setDateMaxValue: true,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										callback: function(nowDate) {
											base_form.findField('EvnHistologicProto_CutDate').fireEvent('change', base_form.findField('EvnHistologicProto_CutDate'), nowDate, null);
										},
										windowId: this.id
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: formTabIndex++,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					}, {
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'Количество кусочков',
						name: 'EvnHistologicProto_BitCount',
						tabIndex: formTabIndex++,
						width: 100,
						xtype: 'numberfield'
					}, {
						allowBlank: !(getRegionNick().inlist(['ufa','vologda', 'kz', 'penza', 'perm'])),
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'Количество блоков',
						name: 'EvnHistologicProto_BlockCount',
						tabIndex: formTabIndex++,
						width: 100,
						xtype: 'numberfield'
					},
					_this.PrescrReactionTypePanel,
					{
						allowBlank: true,
						fieldLabel: 'Макроскопическое описание',
						height: 100,
						name: 'EvnHistologicProto_MacroDescr',
						tabIndex: formTabIndex++,
						width: 430,
						xtype: 'textarea'
					}]
				},
                new sw.Promed.ViewFrame({
                    autoExpandColumn: 'autoexpand_usluga',
                    autoExpandMin: 100,
                    tbar: false,
                    title: 'Микроскопическое описание',
                    border: false,
                    autoLoadData: false,
                    stringfields: [
                        { name: 'EvnHistologicMicro_id', type: 'int', header: 'EvnHistologicMicro_id',  key: true },
                        { name: 'EvnHistologicProto_id', type: 'string', header: 'EvnHistologicProto_id',  hidden: true },
                        { name: 'HistologicSpecimenPlace_id', type: 'string', header: 'HistologicSpecimenPlace_id',  hidden: true },
                        { name: 'PrescrReactionType_id', type: 'string', header: 'PrescrReactionType_id',  hidden: true },
                        { name: 'PrescrReactionType_did', type: 'string', header: 'PrescrReactionType_did',  hidden: true },
                        { name: 'HistologicSpecimenPlace_Name', type: 'string', id: 'autoexpand_usluga', header: 'Откуда взят',  width: 100},
                        { name: 'EvnHistologicMicro_Count', type: 'string', header: 'Количество кусочков', width: 150 },
                        { name: 'EvnHistologicMicro_Descr', type: 'string', header: 'Микроскопическая картина', width: 550}
                    ],
                    id: 'EHPEF_EvnHistologicMicroGrid',
                    onDblClick: function() {
                        _this.openEvnHistologicMicroEditWindow('edit');
                    },
                    region: 'center',
                    stripeRows: true,
                    actions: [
                        { name: 'action_add', handler: function() { this.openEvnHistologicMicroEditWindow('add'); }.createDelegate(this) },
                        { name: 'action_edit', handler: function() { this.openEvnHistologicMicroEditWindow('edit'); }.createDelegate(this) },
                        { name: 'action_view', handler: function() { this.openEvnHistologicMicroEditWindow('view'); }.createDelegate(this) },
                        { name: 'action_delete', handler: function(){ this.deleteEvnHistologicMicro();}.createDelegate(this) },
                        { name: 'action_print'},
                        { name: 'action_refresh', hidden: true, disabled: true}
                    ],
                    //root: 'data',
                    //totalProperty: 'totalCount',
                    dataUrl: '/?c=EvnHistologicMicro&m=loadEvnHistologicMicroGrid'
                })]
			}), new sw.Promed.Panel({
				border: true,
				collapsible: true,
				height: 150,
				id: 'EHPEF_EvnUslugaParPanel',
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById('EHPEF_EvnUslugaParGrid').getGrid().getStore().load({
								params: {EvnDirection_id: this.FormPanel.getForm().findField('EvnDirectionHistologic_id').getValue()}
							});
						}
						panel.doLayout();
					}.createDelegate(this)
				},
				style: 'margin-bottom: 0.5em;',
				title: '3. Услуга',
				items: [new sw.Promed.ViewFrame({
					tbar: false,
					border: false,
					id: 'EHPEF_EvnUslugaParGrid',
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
			})]
		});

		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				if ( this.action == 'view' ) {
					this.buttons[this.buttons.length - 1].focus();
				}
				else {
					this.FormPanel.getForm().findField('EvnDirectionHistologic_SerNum').focus(true);
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
						base_form.findField('EvnHistologicProto_MacroDescr').focus();
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
					this.printEvnHistologicProto();
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
						this.FormPanel.getForm().findField('EvnDirectionHistologic_SerNum').focus(true);
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

		sw.Promed.swEvnHistologicProtoEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnHistologicProtoEditWindow');

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
			win.findById('EHPEF_EvnHistologicMicroPanel').doLayout();
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
		var evn_histologic_proto_id = base_form.findField('EvnHistologicProto_id').getValue();

		if ( this.onCancelActionFlag == true && evn_histologic_proto_id > 0 && this.action == 'add') {
			// удалить протокол патологогистологического исследования
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
					EvnHistologicProto_id: evn_histologic_proto_id
				},
				success: function(response, options) {
					loadMask.hide();

					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'При удалении протокола патологогистологического исследования возникли ошибки [Тип ошибки: 3]');
						return false;
					}
				},
				url: '/?c=EvnHistologicProto&m=deleteEvnHistologicProto'
			});
		}
	},
	onCancelActionFlag: true,
	onHide: Ext.emptyFn,
	openEvnDirectionHistologicListWindow: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();

		if ( base_form.findField('EvnDirectionHistologic_SerNum').disabled ) {
			return false;
		}

		if ( getWnd('swEvnDirectionHistologicListWindow').isVisible() ) {
			sw.swMsg.alert('Ошибка', 'Окно просмотра списка направлений уже открыто');
			return false;
		}

		var params = new Object();

		params.callback = function(data) {
			if ( !data ) {
				return false;
			}

			base_form.findField('EvnDirectionHistologic_id').setValue(data.EvnDirectionHistologic_id);
			base_form.findField('EvnDirectionHistologic_SerNum').setValue(data.EvnDirectionHistologic_Ser + ' ' + data.EvnDirectionHistologic_Num + ', ' + Ext.util.Format.date(data.EvnDirectionHistologic_setDate, 'd.m.Y'));
			win.findById('EHPEF_EvnUslugaParGrid').loadData({globalFilters: {EvnDirection_id: data.EvnDirectionHistologic_id}});
		}.createDelegate(this);
		params.onHide = function() {
			base_form.findField('EvnDirectionHistologic_SerNum').focus();
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

		getWnd('swEvnDirectionHistologicListWindow').show(params);
	},
	openEvnHistologicMicroEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( getWnd('swEvnHistologicMicroEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования микроскопического описания препарата уже открыто');
			return false;
		}

		if ( this.action == 'view') {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById('EHPEF_EvnHistologicMicroGrid').getGrid();
		var params = new Object();

		if ( action == 'add' && base_form.findField('EvnHistologicProto_id').getValue() == 0 ) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnHistologicMicroEditWindow(action);
				}.createDelegate(this)
			});

			return false;
		}

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnHistologicMicroData ) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.evnHistologicMicroData.EvnHistologicMicro_id);

			if ( record ) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnHistologicMicroData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnHistologicMicro_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data.evnHistologicMicroData ], true);
			}
		}
		params.formParams = new Object();
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);

		if ( action == 'add' ) {
			params.formParams.EvnHistologicMicro_id = 0;
			params.formParams.EvnHistologicProto_id = base_form.findField('EvnHistologicProto_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record || !selected_record.get('EvnHistologicMicro_id') ) {
				return false;
			}

			params.formParams = selected_record.data;
		}

		getWnd('swEvnHistologicMicroEditWindow').show(params);
	},
	plain: true,
	printEvnHistologicProto: function() {
		switch ( this.action ) {
			case 'add':
			case 'edit':
				this.doSave({
					print: true
				});
			break;

			case 'view':
				var evn_histologic_proto_id = this.FormPanel.getForm().findField('EvnHistologicProto_id').getValue();
				if(getRegionNick() == 'kz'){
					printBirt({
						'Report_FileName': 'f014u.rptdesign',
						'Report_Params': '&ParamEvnHistologicProto_id=' + evn_histologic_proto_id,
						'Report_Format': 'pdf'
					});
				} else {
					printBirt({
						'Report_FileName': 'f014u_HistologicProtocol.rptdesign',
						'Report_Params': '&paramEvnHistologicProto=' + evn_histologic_proto_id,
						'Report_Format': 'pdf'
					});
				}
			break;
		}
	},
	resizable: true,
	setEvnHistologicProtoNumber: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		if(getRegionNick() == 'ekb'){
			var params = {
				generateNew: 1
			};

			win.getLoadMask('Получение серии/номера протокола').show();
			Ext.Ajax.request({ //заполнение номера и серии
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					if (success && response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.Error_Code && response_obj.Error_Code == 'numerator404') {
							sw.swMsg.alert('Ошибка', 'Не задан активный нумератор для "Протокол патогистологического исследования". Обратитесь к администратору системы.');
						} else {
							base_form.findField('EvnHistologicProto_Ser').setValue(response_obj.ser);
							base_form.findField('EvnHistologicProto_Num').setValue(response_obj.num);
						}
					} else {
						sw.swMsg.alert('Ошибка', 'При генерации серии и номера протокола произошла ошибка');
					}
				},
				params: params,
				url: '/?c=EvnHistologicProto&m=getEvnHistologicProtoSerNum'
			});
		} else {
			Ext.Ajax.request({
				callback: function(options, success, response) {
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						base_form.findField('EvnHistologicProto_Num').setValue(response_obj[0].EvnHistologicProto_Num);
					}
					else {
						sw.swMsg.alert('Ошибка', 'Ошибка при определении номера протокола', function() { base_form.findField('EvnHistologicProto_Num').focus(true); }.createDelegate(this) );
					}
				}.createDelegate(this),
				url: '/?c=EvnHistologicProto&m=getEvnHistologicProtoNumber'
			});
		}
	},
	show: function() {
		sw.Promed.swEvnHistologicProtoEditWindow.superclass.show.apply(this, arguments);

		this.findById('EHPEF_EvnHistologicMicroPanel').expand();
		this.findById('EHPEF_EvnHistologicProtoConclusionPanel').expand();

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.findById('EHPEF_Caption').hide();

		this.PrescrReactionTypePanel.resetFieldSets();

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


		// определенный медстафффакт
		if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 ) {
			this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}
		// если в настройках есть medstafffact, то имеем список мест работы
		else if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 ) {
			this.UserMedStaffFactList = Ext.globalOptions.globals['medstafffact'];
		}

		base_form.setValues(arguments[0].formParams);

		this.findById('EHPEF_EvnHistologicMicroGrid').getGrid().getStore().removeAll();
		this.findById('EHPEF_EvnHistologicMicroGrid').getGrid().getTopToolbar().items.items[0].enable();
		this.findById('EHPEF_EvnHistologicMicroGrid').getGrid().getTopToolbar().items.items[1].disable();
		this.findById('EHPEF_EvnHistologicMicroGrid').getGrid().getTopToolbar().items.items[2].disable();
		this.findById('EHPEF_EvnHistologicMicroGrid').getGrid().getTopToolbar().items.items[3].disable();
		
		this.findById('EHPEF_EvnUslugaParGrid').getGrid().getStore().removeAll();
		this.findById('EHPEF_EvnUslugaParGrid').getGrid().getTopToolbar().items.items[0].enable();
		this.findById('EHPEF_EvnUslugaParGrid').getGrid().getTopToolbar().items.items[1].disable();
		this.findById('EHPEF_EvnUslugaParGrid').getGrid().getTopToolbar().items.items[2].disable();
		this.findById('EHPEF_EvnUslugaParGrid').getGrid().getTopToolbar().items.items[3].disable();
		
		this.findById('EHPEF_EvnUslugaParPanel').doLayout();

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

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		var that = this;
		base_form.findField('Diag_id').addListener('change',
			function (field, newValue, oldValue) {
				that.setMedPersonal(base_form.findField('EvnHistologicProto_didDate').getValue());
			}
		);
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PATHOMORPH_EHPEFADD);
				this.enableEdit(true);

				LoadEmptyRow(this.findById('EHPEF_EvnHistologicMicroGrid').getGrid());
				LoadEmptyRow(this.findById('EHPEF_EvnUslugaParGrid').getGrid());

				this.findById('EHPEF_EvnHistologicMicroPanel').isLoaded = true;

				base_form.findField('EvnHistologicProto_didDate').fireEvent('change', base_form.findField('EvnHistologicProto_didDate'), base_form.findField('EvnHistologicProto_didDate').getValue());

				// Генерируем серию протокола
				// В Екб серия через нумератор
				if ( getRegionNick() == 'krym' ) {
					base_form.findField('EvnHistologicProto_Ser').setValue(2016);
				}
				else if (getRegionNick() != 'ekb') {
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

							base_form.findField('EvnHistologicProto_Ser').setValue(serial);
						}
					});
				}

				// Получаем номер направления
				this.setEvnHistologicProtoNumber();

				loadMask.hide();

				base_form.clearInvalid();
						
				this.findById('EHPEF_EvnUslugaParGrid').gFilters = {EvnDirection_id: this.FormPanel.getForm().findField('EvnDirectionHistologic_id').getValue()};
				this.findById('EHPEF_EvnUslugaParGrid').loadData();

				this.PrescrReactionTypePanel.addFieldSet();

				if(getRegionNick() == 'kz') {
					base_form.findField('EvnHistologicProto_BitCount').enable();
					base_form.findField('EvnHistologicProto_BlockCount').enable();
					if (!Ext.isEmpty(base_form.findField('PrescrReactionType_id_1'))) {
						base_form.findField('PrescrReactionType_id_1').enable();
					}
				} else {
					base_form.findField('EvnHistologicProto_BitCount').disable();
					base_form.findField('EvnHistologicProto_BlockCount').disable();
					if (!Ext.isEmpty(base_form.findField('PrescrReactionType_id_1'))) {
						base_form.findField('PrescrReactionType_id_1').disable();
					}
				}
				

				base_form.findField('EvnDirectionHistologic_SerNum').focus(true, 250);
				break;

			case 'edit':
			case 'view':
				var evn_histologic_proto_id = base_form.findField('EvnHistologicProto_id').getValue();

				if ( !evn_histologic_proto_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnHistologicProto_id': evn_histologic_proto_id
					},
					success: function(form, act) {
						var response_obj = Ext.util.JSON.decode(act.response.responseText);

						if (response_obj[0].accessType == 'view') {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_PATHOMORPH_EHPEFEDIT);
							this.enableEdit(true);

							base_form.findField('EvnHistologicProto_didDate').fireEvent('change', base_form.findField('EvnHistologicProto_didDate'), base_form.findField('EvnHistologicProto_didDate').getValue());
						}
						else {
							this.setTitle(WND_PATHOMORPH_EHPEFVIEW);
							this.enableEdit(false);

							this.findById('EHPEF_EvnHistologicMicroGrid').getGrid().getTopToolbar().items.items[0].disable();
							this.findById('EHPEF_EvnUslugaParGrid').getGrid().getTopToolbar().items.items[0].disable();
						}

						if ( base_form.findField('pmUser_Name').getValue().toString().length > 0 ) {
							this.findById('EHPEF_Caption').show();
						}

						if ( this.action == 'edit' ) {
							setCurrentDateTime({
								dateField: base_form.findField('EvnHistologicProto_didDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: this.id
							});
						}

						this.findById('EHPEF_EvnHistologicMicroPanel').isLoaded = false;
						this.findById('EHPEF_EvnHistologicMicroPanel').fireEvent('expand', this.findById('EHPEF_EvnHistologicMicroPanel'));

						var
							diag_id = response_obj[0].Diag_id,
							index,
							med_personal_id = response_obj[0].MedPersonal_id,
							med_personal_sid = response_obj[0].MedPersonal_sid,
							med_stafffact_id = response_obj[0].MedStaffFact_id,
							record;

						if ( !Ext.isEmpty(response_obj[0].PrescrReactionType_ids) ) {
							this.PrescrReactionTypePanel.setIds(response_obj[0].PrescrReactionType_ids);
						}
						else {
							this.PrescrReactionTypePanel.addFieldSet();
						}

						if ( 
							this.action != 'view' 
							&& (getRegionNick() == 'kz' || base_form.findField('EvnHistologicProto_CutDate').getValue()) 
						) {
							base_form.findField('EvnHistologicProto_BitCount').enable();
							base_form.findField('EvnHistologicProto_BlockCount').enable();
							if (!Ext.isEmpty(base_form.findField('PrescrReactionType_id_1'))) {
								base_form.findField('PrescrReactionType_id_1').enable();
							}
						} else {
							base_form.findField('EvnHistologicProto_BitCount').disable();
							base_form.findField('EvnHistologicProto_BlockCount').disable();
							if (!Ext.isEmpty(base_form.findField('PrescrReactionType_id_1'))) {
								base_form.findField('PrescrReactionType_id_1').disable();
							}
						}

						/*if ( this.action == 'edit' ) {
							base_form.findField('EvnHistologicProto_didDate').fireEvent('change', base_form.findField('EvnHistologicProto_didDate'), base_form.findField('EvnHistologicProto_didDate').getValue());
						}
						else {*/
							base_form.findField('MedPersonal_id').getStore().load({
								callback: function() {
									index = base_form.findField('MedPersonal_id').getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_id ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getStore().getAt(index).get('MedPersonal_id'));
									}
								}.createDelegate(this),
								params: {
									MedPersonal_id: med_personal_id
								}
							});

							base_form.findField('MedPersonal_sid').getStore().load({
								callback: function() {
									index = base_form.findField('MedPersonal_sid').getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_sid ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedPersonal_sid').setValue(base_form.findField('MedPersonal_sid').getStore().getAt(index).get('MedPersonal_id'));
									}
								}.createDelegate(this),
								params: {
									MedPersonal_id: med_personal_sid
								}
							});

						base_form.findField('MedStaffFact_id').getStore().load({
							callback: function() {
								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
									//debugger;
									if ( rec.get('MedStaffFact_id') == med_stafffact_id ) {
										return true;
									}
									else {
										return false;
									}
								});

								if ( index >= 0 ) {
									base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
								}
							}.createDelegate(this),
						});
						//}

						if ( diag_id != null && Number(diag_id) > 0 ) {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function(rec) {
										if ( rec.get('Diag_id') == diag_id ) {
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec);
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
							});
						}

						loadMask.hide();

						base_form.clearInvalid();
						
						this.findById('EHPEF_EvnUslugaParGrid').gFilters = {EvnDirection_id: this.FormPanel.getForm().findField('EvnDirectionHistologic_id').getValue()};
						this.findById('EHPEF_EvnUslugaParGrid').loadData();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnDirectionHistologic_SerNum').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnHistologicProto&m=loadEvnHistologicProtoEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 750
});