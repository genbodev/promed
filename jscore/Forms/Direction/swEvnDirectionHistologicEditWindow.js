/**
* swEvnDirectionHistologicEditWindow - направление на патологогистологическое исследование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Direction
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      28.08.2010
* @comment      Префикс для id компонентов EDHEF (EvnDirectionHistologicEditForm)
*
*
* Использует: -
*/

sw.Promed.swEvnDirectionHistologicEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	formStatus: 'edit',
	height: 550,
	id: 'EvnDirectionHistologicEditWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnDirectionHistologicEditWindow');

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
	deleteMarkingBiopsy: function() {
		var win = this;

		if ( win.action == 'view' ) {
			return false;
		}
				
		var grid = win.findById(win.id + 'MarkingBiopsyGrid').getGrid();

		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || Ext.isEmpty(grid.getSelectionModel().getSelected().get('MarkingBiopsy_id')) ) {
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
							win.filterMarkingBiopsyGrid();
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
	refreshOuterFieldsAccess: function() {
		var base_form = this.FormPanel.getForm();
		if(this.outer) {
			if(this.action == 'add') {
				base_form.findField('EvnDirectionHistologic_Ser').setValue('');
				base_form.findField('EvnDirectionHistologic_Num').setValue('');

				base_form.findField('Lpu_aid').setValue(getGlobalOptions().lpu_id);
			}
			base_form.findField('EvnDirectionHistologic_Ser').enable(false);

			base_form.findField('Lpu_aid').setDisabled(true);
			base_form.findField('Lpu_sid').showContainer();
			base_form.findField('Lpu_sid').setAllowBlank(false);

			base_form.findField('PrehospDirect_id').showContainer();
			base_form.findField('PrehospDirect_id').getStore().filterBy(function(rec) {
				return rec.get('PrehospDirect_Code').inlist([2,3]);
			});
			base_form.findField('PrehospDirect_id').setValue(2);
			base_form.findField('PrehospDirect_id').fireEvent( 'change', base_form.findField('PrehospDirect_id'), 2 );

			base_form.findField('Org_sid').showContainer();

			base_form.findField('EvnDirectionHistologic_LawDocumentDate').showContainer();
			base_form.findField('EvnDirectionHistologic_LawDocumentDate').setAllowBlank(false);

			base_form.findField('EvnDirectionHistologic_Descr').showContainer();
		} else {
			base_form.findField('EvnDirectionHistologic_Ser').disable(true);
			base_form.findField('Lpu_aid').setDisabled(false);
			
			base_form.findField('Lpu_sid').hideContainer();
			base_form.findField('Lpu_sid').setAllowBlank(true);

			base_form.findField('PrehospDirect_id').hideContainer();

			base_form.findField('Org_sid').hideContainer();

			//Корректировака под ТЗ
			base_form.findField('EvnDirectionHistologic_LawDocumentDate').setAllowBlank(true);
			base_form.findField('EvnDirectionHistologic_LawDocumentDate').hideContainer();
			base_form.findField('EvnDirectionHistologic_LawDocumentDate').setAllowBlank(true);

			base_form.findField('EvnDirectionHistologic_Descr').hideContainer();
		}
	},
	getInvalidFields: function() {
		var invalidFields = [];
		var str = '';
		var form = this.FormPanel;
		var base_form = form.getForm();
		base_form.items.filterBy(function(field) {
			if (field.validate()) return;
			var str = '';
			if(field.fieldLabel) str += field.fieldLabel;
			if(field.name) str += ' ('+field.name+')';
			invalidFields.push(str);
		});

		console.warn('Form validation error: ' + invalidFields.join(' , '));
	},
	doSave: function(options) {
		var win = this;
		// options @Object
		// options.print @Boolean Вызывать печать направления на патологогистологическое исследование, если true

		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
					this.getInvalidFields();
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'));

		var
			grid = this.findById(this.id + 'MarkingBiopsyGrid').getGrid(),
			MarkingBiopsyData = [],
			params = new Object();

		if ( this.findById(this.id + 'MarkingBiopsyPanel').isLoaded == true ) {
			grid.getStore().clearFilter();
			
			if ( grid.getStore().getCount() > 0 && !Ext.isEmpty(grid.getStore().getAt(0).get('MarkingBiopsy_id')) ) {
				MarkingBiopsyData = getStoreRecords(grid.getStore());
				this.filterMarkingBiopsyGrid();
			}
		}


		params.outer = win.outer == true;
		
		if( base_form.findField('EvnDirectionHistologic_Ser').disabled ) {
			params.EvnDirectionHistologic_Ser = base_form.findField('EvnDirectionHistologic_Ser').getValue();
		}

		if( base_form.findField('Lpu_aid').disabled ) {
			params.Lpu_aid = base_form.findField('Lpu_aid').getValue();
		}
		
		params.MarkingBiopsyData = Ext.util.JSON.encode(MarkingBiopsyData);

		if ( base_form.findField('LpuSection_did').disabled ) {
			params.LpuSection_did = base_form.findField('LpuSection_did').getValue();
		}

        if ( this.BiopsyStudyTypePanel.isVisible()) {
        	params.BiopsyStudyType_ids = this.BiopsyStudyTypePanel.getIds();
		}

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
					if ( action.result.EvnDirectionHistologic_id ) {
						var evn_direction_histologic_id = action.result.EvnDirectionHistologic_id;

						base_form.findField('EvnDirectionHistologic_id').setValue(evn_direction_histologic_id);

						var data = new Object();

						data.evnDirectionHistologicData = {
							'accessType': 'edit',
							'EvnDirectionHistologic_id': base_form.findField('EvnDirectionHistologic_id').getValue(),
							'Person_id': base_form.findField('Person_id').getValue(),
							'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue(),
							'EvnDirectionHistologic_Ser': base_form.findField('EvnDirectionHistologic_Ser').getValue(),
							'EvnDirectionHistologic_Num': base_form.findField('EvnDirectionHistologic_Num').getValue(),
							'EvnDirectionHistologic_setDate': base_form.findField('EvnDirectionHistologic_setDate').getValue(),
							'LpuSection_Name': base_form.findField('LpuSection_did').getFieldValue('LpuSection_Name'),
							'MedPersonal_Fio': base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_Fio'),
							'EvnDirectionHistologic_NumCard': base_form.findField('EvnDirectionHistologic_NumCard').getValue(),
							'Person_Surname': this.PersonInfo.getFieldValue('Person_Surname'),
							'Person_Firname': this.PersonInfo.getFieldValue('Person_Firname'),
							'Person_Secname': this.PersonInfo.getFieldValue('Person_Secname'),
							'Person_Birthday': this.PersonInfo.getFieldValue('Person_Birthday'),
							'Lpu_Name': base_form.findField('Lpu_aid').getFieldValue('Lpu_Nick'),
							'EvnDirectionHistologic_IsUrgent': base_form.findField('EvnDirectionHistologic_IsUrgent').getFieldValue('YesNo_Name')
						};
						this.callback(data);

						if ( options && options.print ) {
							this.buttons[1].focus();
							printBirt({
								'Report_FileName': 'f014u_DirectionHistologic.rptdesign',
								'Report_Params': '&paramEvnDirectionHistologic=' + base_form.findField('EvnDirectionHistologic_id').getValue(),
								'Report_Format': 'pdf'
							});
						}
						else {
							if (base_form.findField('ZNOinfo').getValue() == '1') {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									msg: langs('Создать ещё одно направление?'),
									title: langs('Вопрос'),
									icon: Ext.MessageBox.QUESTION,
									fn: function (buttonId) {
										if (buttonId === 'no') {
											var EvnDirection_pid = base_form.findField('EvnDirectionHistologic_pid').getValue();
											sw.Promed.Direction.loadDirectionDataForZNO({
												typeofdirection: 'all',
												EvnDirection_pid: EvnDirection_pid,
												callback: function (data) {
													if (data) {
														getWnd('swZNOinfoWindow').hide();
														var win = getWnd('swPersonEmkWindow');
														if (win.isVisible()) {
															win.openEmkEditWindow(false , getWnd('swPersonEmkWindow').Tree.getSelectionModel().selNode);
														}
													} 
												}
											});
										} 
									}.createDelegate(this)
								});
							}
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
		console.log('filterLastResultsGrid is currently unavailable');
	},
	filterMarkingBiopsyGrid: function() {
		var store = this.findById(this.id + 'MarkingBiopsyGrid').getGrid().getStore();	

		store.clearFilter();
		store.filterBy(function(rec) {
			return (Number(rec.get('RecordStatus_Code')) != 3);
		});

		return true;
	},
	onHide: Ext.emptyFn,
	openEvnHistologicProtoEditWindow: function() {
		var base_form = this.FormPanel.getForm();

		var evn_histologic_proto_id = base_form.findField('EvnHistologicProto_id').getValue();

		if ( !evn_histologic_proto_id ) {
			return false;
		}

		if ( getWnd('swEvnHistologicProtoEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования направление на патологогистологическое исследование уже открыто'));
			return false;
		}

		var params = new Object();

		params.action = 'view';
		params.formParams = new Object();

		params.formParams.EvnHistologicProto_id = evn_histologic_proto_id;
		params.formParams.Person_id = base_form.findField('Person_id').getValue();
		params.formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.formParams.Server_id = base_form.findField('Server_id').getValue();

		getWnd('swEvnHistologicProtoEditWindow').show(params);
	},
	openEvnPSListWindow: function() {
		var base_form = this.FormPanel.getForm();

		if ( base_form.findField('EvnDirectionHistologic_NumCard').disabled ) {
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

			base_form.findField('EDHUslugaComplex_id').getStore().load({
				/*callback: function() {
					if ( base_form.findField('EDHUslugaComplex_id').getStore().getCount() == 1 ) {
						base_form.findField('EDHUslugaComplex_id').setValue(base_form.findField('EDHUslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id'));
					}
				},*/
				params: {
					EvnPS_id:data.EvnPS_id
				}
			});
			base_form.findField('EvnPS_id').setValue(data.EvnPS_id);
			base_form.findField('EvnDirectionHistologic_NumCard').setValue(data.EvnPS_NumCard);
		}.createDelegate(this);
		params.onHide = function() {
			base_form.findField('EvnDirectionHistologic_NumCard').focus();
		}.createDelegate(this);
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');

		getWnd('swEvnPSListWindow').show(params);
	},
	openMarkingBiopsyEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( getWnd('swMarkingBiopsyEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования маркировки материала уже открыто'));
			return false;
		}

		var
			formParams = new Object(),
			grid = this.findById(this.id + 'MarkingBiopsyGrid').getGrid(),
			params = new Object(),
			win = this;

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.MarkingBiopsyData != 'object' ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют необходимые данные'));
				return false;
			}

			data.MarkingBiopsyData.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) {
				return (rec.get('MarkingBiopsy_id') == data.MarkingBiopsyData.MarkingBiopsy_id);
			});

			if ( index == -1 ) {
				data.MarkingBiopsyData.MarkingBiopsy_id = -swGenTempId(grid.getStore());
			}

			if ( index >= 0 ) {
				var record = grid.getStore().getAt(index);

				if ( record.get('RecordStatus_Code') == 1 ) {
					data.MarkingBiopsyData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.MarkingBiopsyData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && Ext.isEmpty(grid.getStore().getAt(0).get('MarkingBiopsy_id')) ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data.MarkingBiopsyData ], true);
			}

			return true;
		};
		params.formMode = 'local';

		if ( action == 'add' ) {
			params.formParams = formParams;
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('MarkingBiopsy_id') ) {
				return false;
			}

			var selectedRecord = grid.getSelectionModel().getSelected();

			formParams = selectedRecord.data;			
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.formParams = formParams;
		getWnd('swMarkingBiopsyEditWindow').show(params);

		return true;
	},
	printEvnDirectionHistologic: function() {
		switch ( this.action ) {
			case 'add':
			case 'edit':
				this.doSave({
					print: true
				});
			break;

			case 'view':
				var evn_direction_histologic_id = this.FormPanel.getForm().findField('EvnDirectionHistologic_id').getValue();
				printBirt({
					'Report_FileName': 'f014u_DirectionHistologic.rptdesign',
					'Report_Params': '&paramEvnDirectionHistologic=' + evn_direction_histologic_id,
					'Report_Format': 'pdf'
				});
			break;
		}
	},
	setEvnDirectionHistologicNumber: function() {
		var base_form = this.FormPanel.getForm();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					base_form.findField('EvnDirectionHistologic_Num').setValue(response_obj.EvnDirectionHistologic_Num);
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении номера направления'), function() { base_form.findField('EvnDirectionHistologic_Num').focus(true); }.createDelegate(this) );
				}
			}.createDelegate(this),
			url: '/?c=EvnDirectionHistologic&m=getEvnDirectionHistologicNumber'
		});
	},
	show: function() {
		var win = this;
		var paramsUsluga={};

		sw.Promed.swEvnDirectionHistologicEditWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.findById(this.id + 'LastResultsPanel').collapse();
		this.findById(this.id + 'LastResultsPanel').isLoaded = false;
		this.findById(this.id + 'LastResultsGrid').removeAll();
		this.findById(this.id + 'MarkingBiopsyPanel').collapse();
		this.findById(this.id + 'MarkingBiopsyPanel').isLoaded = false;
		this.findById(this.id + 'MarkingBiopsyGrid').removeAll();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.userMedStaffFact = (arguments[0].userMedStaffFact ? arguments[0].userMedStaffFact : null);

		this.findById('EDHEF_Caption').hide();

		this.BiopsyStudyTypePanel.resetFieldSets();

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if( arguments[0].formParams.PersonEvn_id ){
			base_form.findField('PersonEvn_id').setValue(arguments[0].formParams.PersonEvn_id);
		}
		if( arguments[0].formParams.Person_id ){
			base_form.findField('Person_id').setValue(arguments[0].formParams.Person_id);			
		}
		if( arguments[0].formParams.Server_id ){
			base_form.findField('Server_id').setValue(arguments[0].formParams.Server_id);			
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
		
		if ( arguments[0].outer ) {
			this.outer = arguments[0].outer;
		} else {
			this.outer = false;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		// для #154675 ТАП. Формирование системного сообщения при сохранении ТАПа со случаем подозрения на ЗНО
		if (arguments[0].ZNOinfo && arguments[0].ZNOinfo == '1' ) {
			var ZNOinfo = arguments[0].ZNOinfo;
			base_form.findField('ZNOinfo').setValue(ZNOinfo);
		}

		var ucat_cmb = base_form.findField('UslugaCategory_id');

		// по умолчанию показываем поля для направление из МО работающей в системе
		base_form.findField('LpuSection_did').showContainer();
		base_form.findField('MedStaffFact_id').showContainer();
		base_form.findField('EvnDirectionHistologic_LpuSectionName').hideContainer();
		base_form.findField('EvnDirectionHistologic_MedPersonalFIO').hideContainer();
		base_form.findField('EvnDirectionHistologic_LpuSectionName').setValue('');
		base_form.findField('EvnDirectionHistologic_MedPersonalFIO').setValue('');

		if (getRegionNick().inlist(['kareliya', 'krym', 'khak'])) {
			ucat_cmb.showContainer();
		}else{
			ucat_cmb.hideContainer();
		}

		this.findById(this.id + 'MarkingBiopsyGrid').setActionDisabled('action_add', this.action == 'view');

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PATHOMORPH_EDHEFADD);
				this.enableEdit(true);
				this.buttons[2].hide();
				base_form.findField('EDHUslugaComplex_id').getStore().removeAll();
				base_form.findField('Lpu_aid').getStore();

				this.findById(this.id + 'MarkingBiopsyPanel').isLoaded = true;

				// Генерируем серию направления
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

						base_form.findField('EvnDirectionHistologic_Ser').setValue('Г' + serial);
						win.refreshOuterFieldsAccess();
					}
				});

				// Получаем номер направления
				if( !win.outer ) {
					this.setEvnDirectionHistologicNumber();
				}

				setCurrentDateTime({
					callback: function() {
						base_form.findField('BiopsyOrder_id').fireEvent('change', base_form.findField('BiopsyOrder_id'), null);
						base_form.findField('EvnDirectionHistologic_IsUrgent').setValue(1);
						base_form.findField('EvnDirectionHistologic_setDate').fireEvent('change', base_form.findField('EvnDirectionHistologic_setDate'), base_form.findField('EvnDirectionHistologic_setDate').getValue());

						loadMask.hide();
					
						base_form.clearInvalid();

						base_form.findField('EvnDirectionHistologic_Num').focus(true, 250);
					}.createDelegate(this),
					dateField: base_form.findField('EvnDirectionHistologic_setDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					windowId: this.id
				});

				this.BiopsyStudyTypePanel.addFieldSet();

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

				if(base_form.findField('EvnDirectionHistologic_NumCard').getValue()){
					base_form.findField('EvnDirectionHistologic_NumCard').setDisabled(true)
				}

				if (getRegionNick().inlist(['kareliya', 'krym', 'khak'])) {

					var indeCategoryGost = ucat_cmb.getStore().findBy(function (rec) {
						return (rec.get('UslugaCategory_SysNick') == 'gost2011');
					});
					var ucat_rec = ucat_cmb.getStore().getAt(indeCategoryGost);

					if (ucat_rec) {
						ucat_cmb.fireEvent('select', ucat_cmb, ucat_rec);
						ucat_cmb.fireEvent('select', ucat_cmb, ucat_cmb.getStore().getById(ucat_cmb.getValue()));
					}

					base_form.findField('EDHUslugaComplex_id').clearValue();
					base_form.findField('EDHUslugaComplex_id').clearFilter();

					base_form.findField('EDHUslugaComplex_id').getStore().load({
						callback: function(){
							base_form.findField('EDHUslugaComplex_id').getStore().filterBy( function(rec) {
								return (rec.get('UslugaCategory_id') == ucat_cmb.getValue());
							});
						}
					});
				} else {
					base_form.findField('EDHUslugaComplex_id').clearValue();
					base_form.findField('EDHUslugaComplex_id').clearFilter();

					base_form.findField('EDHUslugaComplex_id').getStore().load({
						params: {
							'EvnPS_id': arguments[0].formParams.EvnPS_id
						}
					});
				}


				break;

			case 'edit':
			case 'view':
				var evn_direction_histologic_id = base_form.findField('EvnDirectionHistologic_id').getValue();

				if ( !evn_direction_histologic_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnDirectionHistologic_id': evn_direction_histologic_id
					},
					success: function(form, act) {
						var response_obj = Ext.util.JSON.decode(act.response.responseText);

						if (response_obj[0].accessType == 'view') {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_PATHOMORPH_EDHEFEDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_PATHOMORPH_EDHEFVIEW);
							this.enableEdit(false);
						}

						if ( !base_form.findField('EvnHistologicProto_id').getValue() ) {
							this.buttons[2].hide();
						}
						else {
							this.buttons[2].show();
						}

						if ( base_form.findField('pmUser_Name').getValue().toString().length > 0 ) {
							this.findById('EDHEF_Caption').show();
						}

						if ( this.action == 'edit' ) {
							setCurrentDateTime({
								dateField: base_form.findField('EvnDirectionHistologic_setDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: this.id
							});
						}

						if( !Ext.isEmpty(base_form.findField('Lpu_sid').getValue()) ) {
							win.outer = true;
							base_form.findField('Lpu_sid').fireEvent('change', base_form.findField('Lpu_sid'));
							base_form.findField('PrehospDirect_id').setValue(2);
						}

						if( !Ext.isEmpty(base_form.findField('Org_sid').getValue()) ) {
							win.outer = true;

							var org_sid_combo = base_form.findField('Org_sid');

							base_form.findField('Org_sid').getStore().load({
								params: {
									Object:'Org',
									Org_id: org_sid_combo.getValue(),
									Org_Name:''
								},
								callback: function() {
									if ( org_sid_combo.getStore().getCount() == 1 ) {
										org_sid_combo.setValue( org_sid_combo.getStore().getAt(0).get('Org_id') );
									}
								}
							});
							base_form.findField('PrehospDirect_id').setValue(3);
						}

						var
							biopsy_order_id = response_obj[0].BiopsyOrder_id,
							diag_id = response_obj[0].Diag_id,
							index,
							indexCategory,
							lpu_aid = response_obj[0].Lpu_aid,
							lpu_section_did = response_obj[0].LpuSection_did,
							med_personal_id = response_obj[0].MedPersonal_id,
							record,
							EDHUslugaComplex_id = response_obj[0].EDHUslugaComplex_id;

						if ( !Ext.isEmpty(response_obj[0].BiopsyStudyType_ids) ) {
							this.BiopsyStudyTypePanel.setIds(response_obj[0].BiopsyStudyType_ids);
						}
						else {
							this.BiopsyStudyTypePanel.addFieldSet();
						}


						if(!getRegionNick().inlist([ 'kareliya','krym','khak' ])){
							paramsUsluga.EvnPS_id=base_form.findField('EvnPS_id').getValue();
						}

						if ( !Ext.isEmpty(base_form.findField('EvnPS_id').getValue()) || !Ext.isEmpty(base_form.findField('EDHUslugaComplex_id').getValue())) {
							base_form.findField('EDHUslugaComplex_id').getStore().load({
								params:paramsUsluga,
								callback: function(){

									index = base_form.findField('EDHUslugaComplex_id').getStore().findBy(function(record, id) {

										return record.get('UslugaComplex_id') == EDHUslugaComplex_id;

									});

									if(getRegionNick().inlist([ 'kareliya','krym','khak' ])) {

										var usluga_category_id = base_form.findField('EDHUslugaComplex_id').getStore().getAt(0).get('UslugaCategory_id');

										indexCategory = ucat_cmb.getStore().findBy(function (rec) {
											return (rec.get('UslugaCategory_id') == usluga_category_id);
										});
										if (indexCategory >= 0) {
											ucat_cmb.setValue(usluga_category_id);
											base_form.findField('EDHUslugaComplex_id').getStore().filterBy( function(rec) {
												return (rec.get('UslugaCategory_id') == usluga_category_id);
											});
										}
									}

									if ( index >= 0 ) {
										base_form.findField('EDHUslugaComplex_id').setValue(EDHUslugaComplex_id);
									} else {
										base_form.findField('EDHUslugaComplex_id').clearValue();
									}
								}
							});
						}

						base_form.findField('Lpu_aid').getStore().load({
							callback: function(records, options, success) {
								if ( success ) {
									base_form.findField('Lpu_aid').setValue(lpu_aid);

									if(win.action == 'edit') {
										win.refreshOuterFieldsAccess();
									} else if(win.outer) {
										base_form.findField('Lpu_sid').showContainer();
										base_form.findField('EvnDirectionHistologic_LawDocumentDate').showContainer();
										base_form.findField('EvnDirectionHistologic_LawDocumentDate').setAllowBlank(false);
										base_form.findField('Org_sid').showContainer();
										base_form.findField('PrehospDirect_id').showContainer();
										base_form.findField('EvnDirectionHistologic_Descr').showContainer();
									} else {
										base_form.findField('Lpu_sid').hideContainer();
										base_form.findField('EvnDirectionHistologic_LawDocumentDate').hideContainer();
										//Корректировака под ТЗ
										base_form.findField('EvnDirectionHistologic_LawDocumentDate').setAllowBlank(true);
										base_form.findField('Org_sid').hideContainer();
										base_form.findField('PrehospDirect_id').hideContainer();
										base_form.findField('EvnDirectionHistologic_Descr').hideContainer();
									}
								}
							}
						});

						if ( this.action == 'edit' ) {
							record = base_form.findField('BiopsyOrder_id').getStore().getById(biopsy_order_id);

							if ( record && record.get('BiopsyOrder_Code') == 2 ) {
								base_form.findField('EvnDirectionHistologic_BiopsyDate').enable();
								base_form.findField('EvnDirectionHistologic_BiopsyNum').enable();
							}
							else {
								base_form.findField('EvnDirectionHistologic_BiopsyDate').disable();
								base_form.findField('EvnDirectionHistologic_BiopsyDate').setRawValue('');
								base_form.findField('EvnDirectionHistologic_BiopsyNum').disable();
								base_form.findField('EvnDirectionHistologic_BiopsyNum').setRawValue('');
							}

							base_form.findField('EvnDirectionHistologic_setDate').fireEvent('change', base_form.findField('EvnDirectionHistologic_setDate'), base_form.findField('EvnDirectionHistologic_setDate').getValue());

							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(record, id) {
								if ( record.get('LpuSection_id') == lpu_section_did && record.get('MedPersonal_id') == med_personal_id ) {
									return true;
								}
								else {
									return false;
								}
							});

							if ( index >= 0 ) {
								base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}
						else {
							base_form.findField('LpuSection_did').getStore().load({
								callback: function() {
									index = base_form.findField('LpuSection_did').getStore().findBy(function(rec) {
										if ( rec.get('LpuSection_id') == lpu_section_did ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('LpuSection_did').setValue(base_form.findField('LpuSection_did').getStore().getAt(index).get('LpuSection_id'));
										base_form.findField('LpuSection_did').fireEvent('change', base_form.findField('LpuSection_did'), base_form.findField('LpuSection_did').getStore().getAt(index).get('LpuSection_id'));
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: lpu_section_did,
									mode: 'combo'
								}
							});

							base_form.findField('MedStaffFact_id').getStore().load({
								callback: function() {
									index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_did ) {
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
								params: {
									LpuSection_id: lpu_section_did,
									MedPersonal_id: med_personal_id
								}
							});
						}

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

						loadMask.hide();

						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnDirectionHistologic_Num').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnDirectionHistologic&m=loadEvnDirectionHistologicEditForm'
				});
			break;

			default:
				loadMask.hide();
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

		win.BiopsyStudyTypePanel = new Ext.Panel({
			baseFilter: null,
			border: false,
			count: 0,
			limit: -1,
			setBaseFilter: function(filterFn) {
				var base_form = win.FormPanel.getForm();
				var container = win.BiopsyStudyTypePanel;
				container.baseFilter = filterFn;

				for (var num = 1; num <= container.count; num++) {
					var field = base_form.findField('BiopsyStudyType_id_' + num);
					if (field) field.setBaseFilter(container.baseFilter);
				}
			},
			setAccess: function() {
				var base_form = win.FormPanel.getForm();
				var container = win.BiopsyStudyTypePanel;

				for (var num = 1; num <= container.count; num++) {
					if ( win.getAction() == 'view' ) {
						base_form.findField('BiopsyStudyType_id_' + num).disable();
						container.findById('BiopsyStudyTypeAddButton_' + num).hide();
						container.findById('BiopsyStudyTypeDelButton_' + num).hide();
					}
					else {
						base_form.findField('BiopsyStudyType_id_' + num).enable();
						container.findById('BiopsyStudyTypeAddButton_' + num).show();
						container.findById('BiopsyStudyTypeDelButton_' + num).show();

						if ( num != container.count ) {
							base_form.findField('BiopsyStudyType_id_' + num).disable();
						}

						if ( num == 1 || num != container.count ) {
							container.findById('BiopsyStudyTypeDelButton_' + num).hide();
						}

						if ( num < container.count || container.getLimit() == num ) {
							container.findById('BiopsyStudyTypeAddButton_' + num).hide();
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
				var base_form = win.FormPanel.getForm();
				var container = win.BiopsyStudyTypePanel;
				var ids = [];

				for (var num = 1; num <= container.count; num++) {
					var field = base_form.findField('BiopsyStudyType_id_' + num);
					if (field && !Ext.isEmpty(field.getValue())) {
						ids.push(field.getValue());
					}
				}

				return ids.join(',');
			},
			setIds: function(ids) {
				var container = win.BiopsyStudyTypePanel;

				container.resetFieldSets();

				var ids_arr = ids.split(',');
				for (var i = 0; i < ids_arr.length; i++) {
					container.addFieldSet({value: ids_arr[i]});
				}
			},
			checkLimit: function(checkCount) {
				var container = win.BiopsyStudyTypePanel;
				return (container.getLimit() == -1 || container.getLimit() >= container.count);
			},
			resetFieldSets: function() {
				var container = win.BiopsyStudyTypePanel;
				var count = container.count;
				for (var num = 1; num <= count; num++) {
					container.deleteFieldSet(num);
				}
				container.count = 0;
			},
			deleteFieldSet: function(num) {
				var base_form = win.FormPanel.getForm();
				var container = win.BiopsyStudyTypePanel;
				var panel = win.BiopsyStudyTypeBodyPanel;

				if (panel.findById('BiopsyStudyTypeFieldSet_' + num)) {
					var field = base_form.findField('BiopsyStudyType_id_' + num);
					base_form.items.removeKey(field.id);

					panel.remove('BiopsyStudyTypeFieldSet_'+num);
					win.doLayout();
					win.syncShadow();
					win.FormPanel.initFields();

					container.count--;
				}
			},
			addFieldSet: function(options) {
				var base_form = win.FormPanel.getForm();
				var container = win.BiopsyStudyTypePanel;
				var panel = win.BiopsyStudyTypeBodyPanel;

				var ids = container.getIds();
				var usedValues = (!Ext.isEmpty(ids) ? ids.split(',') : []);
				var additionalValues = [];

				if ( usedValues.length > 0 ) {
					for (var i = 0; i < usedValues.length; i++) {
						if ( usedValues[i] == 1 ) {
							additionalValues.push("4");
						}
						else if ( usedValues[i] == 4 ) {
							additionalValues.push("1");
						}
					}
				}

				if ( additionalValues.length > 0 ) {
					usedValues = usedValues.concat(additionalValues);
				}

				container.count++;
				var num = container.count;

				if (!container.checkLimit()) {
					container.count--;
					return false;
				}

				var addButton = new Ext.Button({
					iconCls:'add16',
					handler: function() {
						if ( win.BiopsyStudyTypePanel.getCount() > 0 && !Ext.isEmpty(base_form.findField('BiopsyStudyType_id_' + win.BiopsyStudyTypePanel.getCount())) && Ext.isEmpty(base_form.findField('BiopsyStudyType_id_' + win.BiopsyStudyTypePanel.getCount()).getValue()) ) {
							return false;
						}

						win.BiopsyStudyTypePanel.addFieldSet();
					},
					id: 'BiopsyStudyTypeAddButton_' + num
				});

				var delButton = new Ext.Button({
					iconCls: 'delete16',
					handler: function() {
						container.deleteFieldSet(num);
						container.setAccess();
					},
					id: 'BiopsyStudyTypeDelButton_' + num
				});

				var config = {
					layout: 'column',
					id: 'BiopsyStudyTypeFieldSet_' + num,
					border: false,
					cls: 'AccessRigthsFieldSet',
					items: [{
						layout: 'form',
						border: false,
						labelWidth: 200,
						items: [{
							comboSubject: 'BiopsyStudyType',
							displayField: 'BiopsyStudyType_Display',
							editable: true,
							fieldLabel: (container.count == 1 ? langs('Задача прижизненного патолого-анатомического исследования биопсийного (операционного) материала') : ''),
							hiddenName: 'BiopsyStudyType_id_' + num,
							ignoreCodeField: true,
							labelSeparator: (container.count == 1 ? ':' : ''),
							lastQuery: '',
							moreFields: [{
								name: 'BiopsyStudyType_Display',
								convert: function(val,row) {
									return row.BiopsyStudyType_Code + '. ' + row.BiopsyStudyType_Name;
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
				win.doLayout();
				win.syncSize();
				win.FormPanel.initFields();

				var field = base_form.findField('BiopsyStudyType_id_' + num);

				if (field) {
					field.setBaseFilter(container.baseFilter);
					field.getStore().load({
						callback: function() {
							if ( win.BiopsyStudyTypePanel.getLimit() == -1 ) {
								win.BiopsyStudyTypePanel.limit = field.getStore().getCount() - 1; // -1, т.к. есть взаимоисключающие записи
							}
						},
						params: {
							where: (usedValues.length > 0 ? " where BiopsyStudyType_id not in (" + usedValues.join(',') + ")" : null)
						}
					});

					if (options && options.value) {
						field.setValue(options.value);
					}
				}

				container.setAccess();
			},
			items: [ win.BiopsyStudyTypeBodyPanel ]
		});

		win.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnDirectionHistologicEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			listeners: {
				'afterlayout': function(panel) {
					var base_form = panel.getForm();

					base_form.findField('Diag_id').setAllowBlank(getRegionNick() == 'kz');
					base_form.findField('Diag_id').setContainerVisible(getRegionNick() != 'kz');

					base_form.findField('EvnDirectionHistologic_Operation').setContainerVisible(getRegionNick() == 'kz');
					base_form.findField('EvnDirectionHistologic_SpecimenSaint').setContainerVisible(getRegionNick() == 'kz');
					base_form.findField('EvnDirectionHistologic_ObjectCount').setContainerVisible(getRegionNick() == 'kz');
				}
			},
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'BiopsyOrder_id' },
				{ name: 'BiopsyReceive_id' },
				{ name: 'BiopsyStudyType_ids' },
				{ name: 'Diag_id' },
				{ name: 'EvnDirectionHistologic_BiopsyDate' },
				{ name: 'EvnDirectionHistologic_BiopsyNum' },
				{ name: 'EvnDirectionHistologic_ClinicalData' },
				{ name: 'EvnDirectionHistologic_ClinicalDiag' },
				{ name: 'EvnDirectionHistologic_didDate' },
				{ name: 'EvnDirectionHistologic_didTime' },
				{ name: 'EvnDirectionHistologic_id' },
				{ name: 'EvnDirectionHistologic_pid' },
				{ name: 'EvnDirectionHistologic_IsPlaceSolFormalin' },
				{ name: 'EvnDirectionHistologic_IsUrgent' },
				{ name: 'EvnDirectionHistologic_Num' },
				{ name: 'EvnDirectionHistologic_NumCard' },
				{ name: 'EvnDirectionHistologic_ObjectCount' },
				{ name: 'EvnDirectionHistologic_Operation' },
				{ name: 'EvnDirectionHistologic_PredOperTreat' },
				{ name: 'EvnDirectionHistologic_Ser' },
				{ name: 'EvnDirectionHistologic_setDate' },
				{ name: 'EvnDirectionHistologic_setTime' },
				{ name: 'EvnDirectionHistologic_SpecimenSaint' },
				{ name: 'EvnHistologicProto_id' },
				{ name: 'EDHUslugaComplex_id' },
				{ name: 'EvnPS_id' },
				{ name: 'HistologicMaterial_id' },
				{ name: 'Lpu_aid' },
				{ name: 'Lpu_sid' },
				{ name: 'LpuSection_did' },
				{ name: 'MedPersonal_id' },
				{ name: 'EvnDirectionHistologic_MedPersonalFIO' },
				{ name: 'EvnDirectionHistologic_LpuSectionName' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'pmUser_Name' },
				{ name: 'Server_id' },
				{ name: 'TimetableGraf_id' },
				{ name: 'TimetableStac_id' },
				{ name: 'EvnDirectionHistologic_LawDocumentDate' },
				{ name: 'Org_sid' },
				{ name: 'EvnDirectionHistologic_Descr' }
			]),
			region: 'center',
			url: '/?c=EvnDirectionHistologic&m=saveEvnDirectionHistologic',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnDirectionHistologic_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'EvnDirectionHistologic_pid',
				xtype: 'hidden'
			}, {
				name: 'EvnHistologicProto_id',
				xtype: 'hidden'
			}, {
				name: 'EvnPS_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
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
				name: 'TimetableGraf_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'TimetableStac_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'ZNOinfo',
				value: 0,
				xtype: 'hidden'
			}, {
				border: false,
				hidden: true,
				id: 'EDHEF_Caption',
				layout: 'form',
				xtype: 'panel',

				items: [{
					fieldLabel: langs('Аннулировано'),
					name: 'pmUser_Name',
					readOnly: true,
					style: 'color: #ff8870',
					width: 430,
					xtype: 'textfield'
				}]
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
							allowBlank: (win.action == 'add' ? true : false),
							disabled: true,
							fieldLabel: langs('Серия, номер направления'),
							name: 'EvnDirectionHistologic_Ser',
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
							name: 'EvnDirectionHistologic_Num',
							tabIndex: formTabIndex++,
							width: 100,
							xtype: 'textfield'
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
								
									var base_form = win.FormPanel.getForm();

									if ( win.outer && base_form.findField('PrehospDirect_id').getValue() == 3 ) {
										return false
									}

									var lpu_section_id = base_form.findField('LpuSection_did').getValue();
									var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

									win.filterLastResultsGrid();

									base_form.findField('LpuSection_did').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();

									if ( !newValue ) {
										base_form.findField('LpuSection_did').disable();
										base_form.findField('MedStaffFact_id').disable();
										return false;
									}

									var lpu_section_filter_params = {
										// isStacAndPolka: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
										regionCode: getGlobalOptions().region.number
									};

									var medstafffact_filter_params = {
										// isStacAndPolka: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
										regionCode: getGlobalOptions().region.number
									};

									if ( win.action != 'view' ) {
										base_form.findField('LpuSection_did').enable();
										base_form.findField('MedStaffFact_id').enable();
									}

									base_form.findField('LpuSection_did').getStore().removeAll();
									base_form.findField('MedStaffFact_id').getStore().removeAll();

									if ( win.action == 'add' && win.userMedStaffFact && win.userMedStaffFact.LpuSection_id && win.userMedStaffFact.MedStaffFact_id ) {
										// фильтр или на конкретное место работы или на список мест работы
										lpu_section_filter_params.id = win.userMedStaffFact.LpuSection_id;
										medstafffact_filter_params.id = win.userMedStaffFact.MedStaffFact_id;
									}

									// загружаем локальные списки отделений и мест работы
									setLpuSectionGlobalStoreFilter(lpu_section_filter_params);
									setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

									base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									if ( base_form.findField('LpuSection_did').getStore().getById(lpu_section_id) ) {
										base_form.findField('LpuSection_did').setValue(lpu_section_id);
									}
									else if ( !Ext.isEmpty(lpu_section_filter_params.id) ) {
										base_form.findField('LpuSection_did').setValue(lpu_section_filter_params.id);
									}

									if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
										base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
									}
									else if ( !Ext.isEmpty(medstafffact_filter_params.id) ) {
										base_form.findField('MedStaffFact_id').setValue(medstafffact_filter_params.id);
									}

									if ( win.action == 'edit' && win.userMedStaffFact ) {
										base_form.findField('LpuSection_did').disable();
										base_form.findField('MedStaffFact_id').disable();
									}
								}
							},
							name: 'EvnDirectionHistologic_setDate',
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
							fieldLabel: langs('Время'),
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							name: 'EvnDirectionHistologic_setTime',
							onTriggerClick: function() {
								var base_form = win.FormPanel.getForm();
								var time_field = base_form.findField('EvnDirectionHistologic_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										base_form.findField('EvnDirectionHistologic_setDate').fireEvent('change', base_form.findField('EvnDirectionHistologic_setDate'), base_form.findField('EvnDirectionHistologic_setDate').getValue());
									},
									dateField: base_form.findField('EvnDirectionHistologic_setDate'),
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
					comboSubject: 'YesNo',
					fieldLabel: langs('Срочность'),
					hiddenName: 'EvnDirectionHistologic_IsUrgent',
					tabIndex: formTabIndex++,
					width: 100,
					xtype: 'swcommonsprcombo'
				}, {
					hiddenName: 'PrehospDirect_id',
					codeField: null,
					lastQuery: '',
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">{PrehospDirect_Name}</div></tpl>'
					),
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();

							if ( newValue == 2 ) {
								//направившая МО
								base_form.findField('Lpu_sid').setAllowBlank(false);
								base_form.findField('Lpu_sid').enable();
								//Отделения
								base_form.findField('LpuSection_did').setAllowBlank(false);
								base_form.findField('LpuSection_did').enable();

								base_form.findField('EvnDirectionHistologic_LpuSectionName').setAllowBlank(false);
								base_form.findField('EvnDirectionHistologic_LpuSectionName').enable();

								//Мед. работник, направивший тело
								base_form.findField('MedStaffFact_id').setAllowBlank(false);
								base_form.findField('MedStaffFact_id').enable();

								base_form.findField('EvnDirectionHistologic_MedPersonalFIO').setAllowBlank(false);
								base_form.findField('EvnDirectionHistologic_MedPersonalFIO').enable();

								//организация
								base_form.findField('Org_sid').setAllowBlank(true);
								base_form.findField('Org_sid').clearValue();
								base_form.findField('Org_sid').disable();

								//обоснование направления
								base_form.findField('EvnDirectionHistologic_Descr').setAllowBlank(true);
								base_form.findField('EvnDirectionHistologic_Descr').setValue('');
								base_form.findField('EvnDirectionHistologic_Descr').disable();

								base_form.findField('EvnDirectionHistologic_LawDocumentDate').setAllowBlank(true);
								base_form.findField('EvnDirectionHistologic_LawDocumentDate').setValue( null );
								base_form.findField('EvnDirectionHistologic_LawDocumentDate').disable();

							} else {
								//направившая МО
								base_form.findField('Lpu_sid').setAllowBlank(true);
								base_form.findField('Lpu_sid').clearValue();
								base_form.findField('Lpu_sid').disable();

								//Отделения
								base_form.findField('LpuSection_did').setAllowBlank(true);
								base_form.findField('LpuSection_did').clearValue();
								base_form.findField('LpuSection_did').disable();

								base_form.findField('EvnDirectionHistologic_LpuSectionName').setAllowBlank(true);
								base_form.findField('EvnDirectionHistologic_LpuSectionName').setValue('');
								base_form.findField('EvnDirectionHistologic_LpuSectionName').disable();

								//Мед. работник, направивший тело
								base_form.findField('MedStaffFact_id').setAllowBlank(true);
								base_form.findField('MedStaffFact_id').clearValue();
								base_form.findField('MedStaffFact_id').disable();

								base_form.findField('EvnDirectionHistologic_MedPersonalFIO').setAllowBlank(true);
								base_form.findField('EvnDirectionHistologic_MedPersonalFIO').setValue('');
								base_form.findField('EvnDirectionHistologic_MedPersonalFIO').disable();

								base_form.findField('Org_sid').setAllowBlank(false);
								base_form.findField('Org_sid').enable();

								base_form.findField('EvnDirectionHistologic_Descr').setAllowBlank(false);
								base_form.findField('EvnDirectionHistologic_Descr').enable();

								base_form.findField('EvnDirectionHistologic_LawDocumentDate').setAllowBlank(false);
								base_form.findField('EvnDirectionHistologic_LawDocumentDate').enable();
							}

						}.createDelegate(this),
						'select': function(combo, record, index) {
							combo.fireEvent( 'change', combo, record.get( 'PrehospDirect_id') );
						}.createDelegate(this)
					},
					width: 300,
					xtype: 'swprehospdirectcombo'
				}, {
					allowBlank: false,
					fieldLabel: 'Дата документа правоохранительных органов',
					format: 'd.m.Y',
					name: 'EvnDirectionHistologic_LawDocumentDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					selectOnFocus: true,
					width: 100,
					xtype: 'swdatefield'
				}, {
					xtype: 'sworgcombo',
					hiddenName: 'Org_sid',
					editable: false,
					fieldLabel: 'Организация',
					triggerAction: 'none',
					width: 610,
					onTrigger1Click: function() {
						var combo = this;
						if (this.disabled) return false;
						getWnd('swOrgSearchWindow').show({
							enableOrgType: true,
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 ) {
									combo.getStore().load({
										params: {
											Object:'Org',
											Org_id: orgData.Org_id,
											Org_Name:''
										},
										callback: function() {
											combo.setValue(orgData.Org_id);
										}
									});
								}
								getWnd('swOrgSearchWindow').hide();
							},
						});
					},
					enableKeyEvents: true
				}, {
					allowBlank: true,
					fieldLabel: lang['obosnovanie_napravleniya'],
					name: 'EvnDirectionHistologic_Descr',
					tabIndex: TABINDEX_EDMHEF + 13,
					width: 430,
					xtype: 'textfield'
				}, {
					allowBlank: false,
					fieldLabel: langs('В пат.-анатом. лаб-ю ЛПУ'),
					hiddenName: 'Lpu_aid',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swlpulocalcombo'
				}, {
					allowBlank: false,
					fieldLabel: langs('Направившая МО'),
					hiddenName: 'Lpu_sid',
					tabIndex: formTabIndex++,
					width: 430,
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = win.FormPanel.getForm();

							var LpuInSystem = combo.getFieldValue('Lpu_IsNotForSystem') != '2';

							base_form.findField('LpuSection_did').setAllowBlank(!LpuInSystem);
							base_form.findField('MedStaffFact_id').setAllowBlank(!LpuInSystem);
							base_form.findField('EvnDirectionHistologic_LpuSectionName').setAllowBlank(LpuInSystem);
							base_form.findField('EvnDirectionHistologic_MedPersonalFIO').setAllowBlank(LpuInSystem);

							if (LpuInSystem) {
								base_form.findField('LpuSection_did').showContainer();
								base_form.findField('MedStaffFact_id').showContainer();
								base_form.findField('EvnDirectionHistologic_LpuSectionName').hideContainer();
								base_form.findField('EvnDirectionHistologic_MedPersonalFIO').hideContainer();
								base_form.findField('EvnDirectionHistologic_LpuSectionName').setValue('');
								base_form.findField('EvnDirectionHistologic_MedPersonalFIO').setValue('');

							} else {
								base_form.findField('LpuSection_did').hideContainer();
								base_form.findField('MedStaffFact_id').hideContainer();
								base_form.findField('LpuSection_did').clearValue();
								base_form.findField('MedStaffFact_id').clearValue();
								base_form.findField('EvnDirectionHistologic_LpuSectionName').showContainer();
								base_form.findField('EvnDirectionHistologic_MedPersonalFIO').showContainer();
							}

							if(LpuInSystem && newValue != oldValue) {
								base_form.findField('LpuSection_did').clearValue();
								base_form.findField('LpuSection_did').getStore().removeAll();
								base_form.findField('LpuSection_did').getStore().load({
									params: {
										Lpu_id: newValue,
										mode: 'combo'
									}
								});

								base_form.findField('MedStaffFact_id').clearValue();
								base_form.findField('MedStaffFact_id').getStore().removeAll();
								base_form.findField('MedStaffFact_id').getStore().load({
									params: {
										Lpu_id: newValue,
										mode: 'combo'
									}
								});
							}	
						}
					},
					xtype: 'swlpulocalcombo'
				}, {
					fieldLabel: langs('Отделение'), //--текстовое поле, если направление пришло из МО не работающей в системе
					name: 'EvnDirectionHistologic_LpuSectionName',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textfield'
				}, {
					allowBlank: false,
					fieldLabel: langs('Отделение'),
					hiddenName: 'LpuSection_did',
					id: 'EDHEF_LpuSectionCombo',
					linkedElements: [
						'EDHEF_MedStaffactCombo'
					],
					listWidth: 650,
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swlpusectionglobalcombo'
				}, {
					fieldLabel: langs('Врач'), //--текстовое поле, если направление пришло из МО не работающей в системе
					name: 'EvnDirectionHistologic_MedPersonalFIO',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textfield'
				}, {
					allowBlank: false,
					fieldLabel: langs('Врач'),
					hiddenName: 'MedStaffFact_id',
					id: 'EDHEF_MedStaffactCombo',
					listWidth: 650,
					parentElementId: 'EDHEF_LpuSectionCombo',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swmedstafffactglobalcombo'
				}, {
					autoCreate: { tag: "input", type: "text", maxLength: "50", autocomplete: "off" },
					enableKeyEvents: true,
					fieldLabel: langs('Карта стационарного больного'),
					listeners: {
						'change': function(field, newValue, oldValue) {
							if ( newValue != oldValue ) {
								var base_form = win.FormPanel.getForm();
								base_form.findField('EvnPS_id').setValue(0);
								base_form.findField('EDHUslugaComplex_id').clearValue();
								base_form.findField('EDHUslugaComplex_id').getStore().removeAll();
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
					name: 'EvnDirectionHistologic_NumCard',
					onTriggerClick: function() {
						win.openEvnPSListWindow();
					},
					tabIndex: formTabIndex++,
					triggerClass: 'x-form-search-trigger',
					width: 200,
					xtype: 'trigger'
				}, {
					allowBlank: true,
					fieldLabel: langs('Категория услуги'),
					hiddenName: 'UslugaCategory_id',
					listeners: {
							'select': function (combo, record) {

								var base_form = win.FormPanel.getForm();
								base_form.findField('EDHUslugaComplex_id').clearValue();

								base_form.findField('EDHUslugaComplex_id').clearFilter();

								base_form.findField('EDHUslugaComplex_id').getStore().filterBy( function(rec) {
									return (rec.get('UslugaCategory_id') == record.get('UslugaCategory_id'));
								});

							}
					},
					listWidth: 400,
					tabIndex: formTabIndex++,
					width: 250,
					xtype: 'swuslugacategorycombo'
				}, {
					allowBlank: !getRegionNick().inlist([ 'kareliya','krym','khak' ]),
					fieldLabel: langs('Услуга'),
					hiddenName: 'EDHUslugaComplex_id',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swevnsectionuslugas'
				},
				win.BiopsyStudyTypePanel, {
					fieldLabel: langs('Проведенное предоперационное лечение'),
					height: 100,
					name: 'EvnDirectionHistologic_PredOperTreat',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: win.id + 'ClinicalDataPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						
					}
				},
				style: 'margin-bottom: 0.5em;',
				title: (panelID++) + '. ' + langs('Клинические данные'),
				items: [{
					fieldLabel: langs('Диагноз'),
					hiddenName: 'Diag_id',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swdiagcombo'
				}, {
					allowBlank: true,
					fieldLabel: langs('Клинические данные'),
					height: 100,
					name: 'EvnDirectionHistologic_ClinicalData',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}, {
					allowBlank: true,
					fieldLabel: langs('Клинический диагноз'),
					height: 100,
					name: 'EvnDirectionHistologic_ClinicalDiag',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textarea'
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				bodyStyle: 'padding-top: 0.5em;',
				border: true,
				collapsible: true,
				id: win.id + 'MaterialPanel',
				layout: 'form',
				listeners: {
					'expand': function(panel) {
						
					}
				},
				style: 'margin-bottom: 0.5em;',
				title: (panelID++) + '. ' + langs('Материал'),
				items: [{
					allowBlank: false,
					comboSubject: 'HistologicMaterial',
					fieldLabel: langs('Вид материала'),
					hiddenName: 'HistologicMaterial_id',
					tabIndex: formTabIndex++,
					width: 200,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					comboSubject: 'BiopsyOrder',
					fieldLabel: langs('Биопсия'),
					hiddenName: 'BiopsyOrder_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = win.FormPanel.getForm();

							var record = combo.getStore().getById(newValue);

							if ( record && record.get('BiopsyOrder_Code') == 2 ) {
								base_form.findField('EvnDirectionHistologic_BiopsyDate').enable();
								base_form.findField('EvnDirectionHistologic_BiopsyNum').enable();
							}
							else {
								base_form.findField('EvnDirectionHistologic_BiopsyDate').setRawValue('');
								base_form.findField('EvnDirectionHistologic_BiopsyNum').setValue('');
								base_form.findField('EvnDirectionHistologic_BiopsyDate').disable();
								base_form.findField('EvnDirectionHistologic_BiopsyNum').disable();
							}

							return true;
						}
					},
					tabIndex: formTabIndex++,
					width: 200,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: true,
					disabled: true,
					fieldLabel: langs('Дата первичной биопсии'),
					format: 'd.m.Y',
					name: 'EvnDirectionHistologic_BiopsyDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					selectOnFocus: true,
					tabIndex: formTabIndex++,
					width: 100,
					xtype: 'swdatefield'
				}, {
					allowBlank: true,
					disabled: true,
					fieldLabel: langs('Номер первичной биопсии'),
					name: 'EvnDirectionHistologic_BiopsyNum',
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
							allowBlank: getRegionNick().inlist(['ufa']),
							fieldLabel: langs('Дата операции (забора материала)'),
							format: 'd.m.Y',
							name: 'EvnDirectionHistologic_didDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: formTabIndex++,
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelWidth: 155,
						layout: 'form',
						items: [{
							fieldLabel: langs('Время операции (забора материала)'),
							allowBlank: getRegionNick().inlist(['ufa']),
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							name: 'EvnDirectionHistologic_didTime',
							onTriggerClick: function() {
								var base_form = win.FormPanel.getForm();
								var time_field = base_form.findField('EvnDirectionHistologic_didTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									dateField: base_form.findField('EvnDirectionHistologic_didDate'),
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
					allowBlank: true,
					fieldLabel: langs('Вид операции'),
					name: 'EvnDirectionHistologic_Operation',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textfield'
				}, {
					allowBlank: true,
					fieldLabel: langs('Маркировка материала'),
					name: 'EvnDirectionHistologic_SpecimenSaint',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'textfield'
				}, {
					allowBlank: true,
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: langs('Число объектов'),
					name: 'EvnDirectionHistologic_ObjectCount',
					tabIndex: formTabIndex++,
					width: 100,
					xtype: 'numberfield'
				}, {
					comboSubject: 'BiopsyReceive',
					fieldLabel: langs('Способ получения материала'),
					hiddenName: 'BiopsyReceive_id',
					tabIndex: formTabIndex++,
					width: 430,
					xtype: 'swcommonsprcombo'
				}, {
					comboSubject: 'YesNo',
					allowBlank: getRegionNick().inlist(['ufa']),
					fieldLabel: langs('Материал помещен в 10%-ный раствор нейтрального формалина'),
					hiddenName: 'EvnDirectionHistologic_IsPlaceSolFormalin',
					tabIndex: formTabIndex++,
					width: 100,
					xtype: 'swcommonsprcombo'
				}]
			}),
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				hidden: getRegionNick() == 'kz',
				height: 170,
				id: win.id + 'MarkingBiopsyPanel',
				isLoaded: false,
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById(win.id + 'MarkingBiopsyGrid').loadData({
								globalFilters: {
									EvnDirectionHistologic_id: win.FormPanel.getForm().findField('EvnDirectionHistologic_id').getValue()
								}
							});
						}
						panel.doLayout();
					}
				},
				style: 'margin-bottom: 0.5em;',
				title: (getRegionNick() == 'kz' ? '0' : panelID++) + '. ' + langs('Маркировка материала'),
				items: [ new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', handler: function() { win.openMarkingBiopsyEditWindow('add'); } },
						{ name: 'action_edit', handler: function() { win.openMarkingBiopsyEditWindow('edit'); } },
						{ name: 'action_view', handler: function() { win.openMarkingBiopsyEditWindow('view'); } },
						{ name: 'action_delete', handler: function() { win.deleteMarkingBiopsy(); } },
						{ name: 'action_refresh', disabled: true, hidden: true },
						{ name: 'action_print' }
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 350,
					autoLoadData: false,
					border: false,
					dataUrl: '/?c=MarkingBiopsy&m=loadMarkingBiopsyGrid',
					id: win.id + 'MarkingBiopsyGrid',
					object: 'MarkingBiopsy',
					paging: false,
					region: 'center',
					stringfields: [
						{ name: 'MarkingBiopsy_id', type: 'int', header: 'ID', key: true },
						{ name: 'RecordStatus_Code', type: 'int', hidden: true },
						{ name: 'MarkingBiopsy_NumBot', type: 'int', header: langs('Номер флакона'), width: 100 },
						{ name: 'MarkingBiopsy_LocalProcess', type: 'string', hidden: true },
						{ name: 'AnatomicLocal_Text', type: 'string', header: langs('Локализация патологического процесса'), width: 350, id: 'autoexpand' },
						{ name: 'MarkingBiopsy_NatureProcess', type: 'string', header: langs('Характер патологического процесса'), width: 350 },
						{ name: 'MarkingBiopsy_ObjKolvo', type: 'int', header: langs('Количество объектов'), width: 100 },
						{ name: 'AnatomicLocal_id', type: 'int', hidden: true },
						{ name: 'MaterialChange_id', type: 'int', hidden: true },
						{ name: 'MarkingBiopsy_Size', type: 'string', hidden: true },
						{ name: 'MarkingBiopsy_Shape', type: 'string', hidden: true },
						{ name: 'MarkingBiopsy_Border', type: 'string', hidden: true },
						{ name: 'MarkingBiopsy_Consistence', type: 'string', hidden: true },
						{ name: 'MarkingBiopsy_ColorSkin', type: 'string', hidden: true },
					],
					toolbar: true
				})]
			}),
			new sw.Promed.Panel({
				border: true,
				collapsible: true,
				height: 170,
				id: win.id + 'LastResultsPanel',
				isLoaded: false,
				layout: 'border',
				listeners: {
					'expand': function(panel) {
						if ( panel.isLoaded === false ) {
							panel.isLoaded = true;
							panel.findById(win.id + 'LastResultsGrid').loadData({
								globalFilters: {
									Person_id: win.FormPanel.getForm().findField('Person_id').getValue()
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
				title: (panelID++) + '. ' + langs('Результаты исследований'),
				items: [ new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', disabled: true, hidden: true },
						{ name: 'action_edit', disabled: true, hidden: true },
						{ name: 'action_view', disabled: true, hidden: true },
						{ name: 'action_delete', disabled: true, hidden: true },
						{ name: 'action_refresh', disabled: true, hidden: true },
						{ name: 'action_print', disabled: true, hidden: true }
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '/?c=EvnHistologicProto&m=loadEvnHistologicProtoList',
					id: win.id + 'LastResultsGrid',
					object: 'EvnHistologicProto',
					paging: false,
					region: 'center',
					stringfields: [
						{ name: 'EvnHistologicProto_id', type: 'int', header: 'ID', key: true },
						{ name: 'Lpu_Name', type: 'string', header: langs('Наименование МО'), width: 300 },
						{ name: 'EvnHistologicProto_setDate', type: 'date', header: langs('Дата исследования'), width: 150 },
						{ name: 'EvnHistologicProto_Num', type: 'string', header: langs('Регистрационный номер'), width: 110 },
						{ name: 'EvnHistologicProto_HistologicConclusion', type: 'string', header: langs('Заключение'), width: 150, id: 'autoexpand' }
					],
					toolbar: false
				})]
			})]
		});

		this.PersonInfo = new sw.Promed.PersonInfoPanel({
			button1OnHide: function() {
				if ( this.action == 'view' ) {
					this.buttons[this.buttons.length - 1].focus();
				}
				else {
					this.FormPanel.getForm().findField('EvnDirectionHistologic_Num').focus(true);
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
				onShiftTabAction: function() {
					var base_form = this.FormPanel.getForm();

					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else {
						base_form.findField('EvnDirectionHistologic_IsPlaceSolFormalin').focus();
					}
				}.createDelegate(this),
				onTabAction: function() {
					if ( !this.buttons[1].hidden ) {
						this.buttons[1].focus(true);
					}
					else if ( !this.buttons[2].hidden ) {
						this.buttons[2].focus(true);
					}
					else {
						this.buttons[this.buttons.length - 2].focus(true);
					}
				}.createDelegate(this),
				tabIndex: formTabIndex++,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnDirectionHistologic();
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
				onTabAction: function() {
					if ( !this.buttons[2].hidden ) {
						this.buttons[2].focus(true);
					}
					else {
						this.buttons[this.buttons.length - 2].focus(true);
					}
				}.createDelegate(this),
				tabIndex: formTabIndex++,
				text: BTN_FRMPRINT
			}, {
				handler: function() {
					this.openEvnHistologicProtoEditWindow();
				}.createDelegate(this),
				iconCls: 'copy16',
				onShiftTabAction: function() {
					var base_form = this.FormPanel.getForm();

					if ( !this.buttons[1].hidden ) {
						this.buttons[1].focus(true);
					}
					else if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else {
						base_form.findField('EvnDirectionHistologic_IsPlaceSolFormalin').focus();
					}
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 2].focus(true);
				}.createDelegate(this),
				tabIndex: formTabIndex++,
				text: langs('Протокол')
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus(true);
				}.createDelegate(this),
				onTabAction: function() {
					if ( this.action != 'view' ) {
						this.FormPanel.getForm().findField('EvnDirectionHistologic_Num').focus(true);
					}
					else if ( !this.buttons[1].hidden ) {
						this.buttons[1].focus(true);
					}
					else if ( !this.buttons[2].hidden ) {
						this.buttons[2].focus(true);
					}
					else {
						this.buttons[this.buttons.length - 2].focus(true);
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

		sw.Promed.swEvnDirectionHistologicEditWindow.superclass.initComponent.apply(this, arguments);
	}
});