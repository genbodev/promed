/**
* swHTMRegisterWindow - Регистр по наркологии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package	  Common
* @access	   public
* @copyright	Copyright (c) 2018 EMSIS.
* @author	   Салават Магафуров
* @version	  2018/11
*/
sw.Promed.swHTMRegisterWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	title: langs('Регистр по ВМП'),
	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	collapsible: true,
	maximizable: true,
	resizable: true,
	closable: true,
	modal: false,
	plain: true,
	minHeight: 550,
	minWidth: 800,
	height: 550,
	width: 800,
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.SearchFilters.doLayout();
		},
		'restore': function(win) {
			win.SearchFilters.doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.HTMRegisterSearchFrame.setWidth(nW - 5);
			win.SearchFilters.setWidth(nW - 5);
		}
	},

	getButtonSearch: function() {
		return Ext.getCmp(this.id + '_SearchButton');
	},

	doReset: function() {
		
		var base_form = this.SearchFilters.getForm();
		base_form.reset();
		this.HTMRegisterSearchFrame.ViewActions.open_emk.setDisabled(true);
		//this.HTMRegisterSearchFrame.ViewActions.person_register_dis.setDisabled(true);
		this.HTMRegisterSearchFrame.ViewActions.action_view.setDisabled(true);
		this.HTMRegisterSearchFrame.ViewActions.action_delete.setDisabled(true);
		this.HTMRegisterSearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.HTMRegisterSearchFrame.getGrid().getStore().removeAll();
		this.HTMRegisterSearchFrame.getGrid().getViewFrame().removeAll();

		base_form.findField('RegisterType_id').setValue(1); // Регистр - Тип записи регистра - Все
	},

	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}

		var base_form = this.SearchFilters.getForm();

		if(!base_form.isValid()) {
			this.ShowMessage(langs('Сообщение'),langs('Не корректно заполнены поля'));
			return;
		}

		var grid = this.HTMRegisterSearchFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Выбран тип поиска человека ') + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? langs('по состоянию на момент случая') : langs('По всем периодикам')) + langs('.<br />При выбранном варианте поиск работает <b>значительно</b> медленнее.<br />Хотите продолжить поиск?'),
				title: langs('Предупреждение')
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();


		var requestParams = new Object();
		requestParams.params = getAllFormFieldValues(this.SearchFilters);

		requestParams.params.limit = 100;
		requestParams.params.start = 0;
		requestParams.params.AttachLpu_id = base_form.findField('AttachLpu_id').getValue();
		requestParams.params.LpuAttachType_id=(isUserGroup("HTMRegister"))?base_form.findField('LpuAttachType_id').getValue():1;

		if(!isUserGroup('SuperAdmin')) {
			requestParams.params.HTMLpu_id = getGlobalOptions().lpu_id;
		}

		requestParams.callback = function(records, options, success) {
		loadMask.hide();
		}

		this.HTMRegisterSearchFrame.ViewActions.action_refresh.setDisabled(false);
		grid.getStore().removeAll();
		grid.getStore().load(requestParams);
	},

	getRecordsCount: function() {
		var st = this.HTMRegisterSearchFrame.getGrid().getStore();
		var noLines = false;
		var noLines = false;
		if(st.totalLength == 0){
			noLines = true;
		}else if(st.totalLength == 1){
			if(typeof(st.getAt(0)) == 'undefined'){// бывает после нажатия "Обновить"
				noLines = true;
			}else if(! st.getAt(0).get('Register_id')){// если запись пустая
				noLines = true;
			}
		}
		if(noLines){
			sw.swMsg.alert('Подсчет записей', 'Найдено записей: 0');
			return;
		}

		var base_form = this.getFilterForm().getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(langs('Поиск'), langs('Проверьте правильность заполнения полей на форме поиска'));
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());
		post.AttachLpu_id = base_form.findField('AttachLpu_id').getValue();
		post.LpuAttachType_id=(isUserGroup("HTMRegister"))?base_form.findField('LpuAttachType_id').getValue():1;

		if ( post.PersonPeriodicType_id == null ) {
			post.PersonPeriodicType_id = 1;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(langs('Подсчет записей'), langs('Найдено записей: ') + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(langs('Подсчет записей'), response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При подсчете количества записей произошли ошибки'));
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},

	openWindow: function(action) {
		if (!action || !action.toString().inlist(['view','edit'])) { //person_register_dis'add',
			return false;
		}
		var wnd = this;
		var form = this.getFilterForm().getForm();
		var grid = this.HTMRegisterSearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected() && action!='add') {
			return false;
		}
		var rec = grid.getSelectionModel().getSelected();

		switch(action) {
			case 'edit':
			case 'view':
				var params = new Object();
				params.onHide = function(isChange) {
					if(isChange) {
						grid.getStore().reload();
					} else {
						grid.getView().focusRow(grid.getStore().indexOf(rec));
					}
				};

				params.callback = Ext.emptyFn;
				params.PersonRegister_setDate = rec.data.PersonRegister_setDate
				params.MedPersonal_did = rec.data.MedPersonal_iid
				params.Lpu_did = rec.data.Lpu_iid
				params.PersonRegister_id = rec.data.PersonRegister_id;
				params.Person_id = rec.data.Person_id;
				params.editType = wnd.editType;
				params.Register_id = rec.get('Register_id');
				params.HTMRegister_id = rec.get('HTMRegister_id');
				params.Person_id = rec.get('Person_id');
				params.Lpu_sid = rec.get('Lpu_sid');
				params.Org_sid = rec.get('Org_sid');
				params.QueueNumber = rec.get('QueueNumber');
				if(rec.get('Person_isDead') == 2)
					params.action = 'view';
				else
					params.action = action;
				ShowWindow('swHTMRegisterEditWindow',params);
				break;

		}
		

		
	},

	initComponent: function() {
		
		this.HTMRegisterSearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true,handler: function() {ShowWindow('swHTMRegisterEditWindow');}.createDelegate(this)},
				{name: 'action_edit', handler: function() {this.openWindow('edit');}.createDelegate(this)},
				{name: 'action_view', handler: function() {this.openWindow('view');}.createDelegate(this)},
				{name: 'action_delete', handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: this.id + '_SearchFrame',
			object: 'HTMRegister',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'HTMRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'Register_id', type: 'int', hidden: true},
				{name: 'Lpu_iid', type: 'int', hidden: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_SurName', type: 'string', header: langs('Фамилия'), width: 150},
				{name: 'Person_FirName', type: 'string', header: langs('Имя'), width: 150},
				{name: 'Person_SecName', type: 'string', header: langs('Отчество'), width: 150},
				{name: 'Person_BirthDay', type: 'date', format: 'd.m.Y', header: langs('Дата рождения'), width: 90},
				{name: 'Lpu_Nick', type: 'string', header: langs('МО прикр.'), width: 150},
				{name: 'HTMProfie_id', type: 'int', hidden: true },
				{name: 'LpuSectionProfile_Name', type: 'string', header: langs('Профиль'), width: 150},
				{name: 'QueueNumber', type: 'string', header: langs('Номер в очереди'), width: 100},
				{name: 'Lpu_sid', type: 'int', hidden: true },
				{name: 'Org_sid', type: 'int', hidden: true },
				{name: 'Lpu_Nick2', type: 'string', header: langs('МО очереди'), width: 150},
				{name: 'Register_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата включения в регистр'), width: 150},
				{name: 'Register_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата исключения из регистра'), width: 170},
				{name: 'Person_isDead', type: 'int', hidden: true}
				//{name: 'RegisterDisCause_id', type: 'int', hidden: true},
				//{name: 'RegisterDisCause_Name', type: 'string', header: langs('Причина исключения из регистра'), width: 190}
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function(sm,index,record) {
				this.getAction('open_emk').setDisabled( false );
				//this.getAction('person_register_dis').setDisabled( true );
				this.getAction('action_delete').setDisabled( true );
				this.getAction('action_edit').setDisabled( false );
				this.getAction('action_view').setDisabled( false );
			},
			onDblClick: function(sm,index,record) {
				this.getAction('action_edit').execute();
			}
		});

		this.SearchFilters = getBaseSearchFiltersFrame({
			isDisplayPersonRegisterRecordTypeField: false,
			allowPersonPeriodicSelect: false,
			id: this.id + '_FilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'HTMRegister',
			tabIndexBase: TABINDEX_CRZRW,
			tabPanelHeight: 215,
			tabPanelId: this.id + '_SearchFilter',
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 220,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						var form = this.getFilterForm().getForm();
						form.findField('RegisterType_id').focus(250, true);
					}.createDelegate(this)
				},
				title: langs('<u>6</u>. Регистр'),
				items: [ new Ext.Panel({
					layout: 'column',
					border: false,
					defaults: {
						autoHeight: true,
						xtype: 'fieldset',
						border: false
					},
					items: [{
						defaults: { width: 180 },
						items: [{
							fieldLabel: langs('Тип записи регистра'),
							xtype: 'swpersonregistertypecombo',
							hiddenName: 'RegisterType_id'
						}, {
							fieldLabel: langs('Дата включения в регистр'),
							xtype: 'daterangefield',
							name: 'Register_setDate_Range',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}, {
							fieldLabel: langs('Дата исключения из регистра'),
							xtype: 'daterangefield',
							name: 'Register_disDate_Range',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}, {
							fieldLabel:langs('МО оказания ВМП'),
							xtype: 'swlpusearchcombo',
							hiddenName: 'HTMLpu_id'
						},{
							fieldLabel: langs('Подписание'),
							hiddenName: 'HTMRegister_IsSigned',
							xtype: 'swyesnocombo'
						}]
					}, {
						labelWidth: 100,
						defaults: { width: 180 },
						items: [{
							fieldLabel: langs('Профиль'),
							xtype: 'swlpusectionprofilecombo',
							//xtype: 'swcommonsprcombo',
							//comboSubject: 'LpuSectionProfile',
							hiddenName: 'LpuSectionProfile_id',
							width: 270
						}, {
							hiddenName: 'HTMedicalCareClass_id',
							xtype: 'swcommonsprcombo',
							comboSubject: 'HTMedicalCareClass',
							editable: true,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<table style="border: 0;"><tr>',
								'<td style="width: 45px;"><font color="red">{HTMedicalCareClass_Code}&nbsp;</font></td>',
								'<td style="width: 45px;"><b>{HTMedicalCareClass_Name}</b>&nbsp;</td></tr>',
								'</tr></table>',
								'</div></tpl>'
							),
							moreFields: [
								{ name: 'HTMedicalCareClass_begDate', type: 'date', dateFormat: 'd.m.Y' },
								{ name: 'HTMedicalCareClass_endDate', type: 'date', dateFormat: 'd.m.Y' },
								{ name: 'HTMedicalCareClass_fid', type: 'int' },
								{ name: 'HTMedicalCareType_id', type: 'int' }
							],
							fieldLabel: 'Метод ВМП',
							listWidth: 500,
							width: 270
						}, {
							fieldLabel: langs('Этап ВМП'),
							xtype: 'swbaselocalcombo',
							hiddenName: 'HTMRegister_Stage',
							allowBlank: true,
							valueField: 'id',
							displayField: 'name',
							store: new Ext.data.SimpleStore({
								fields: [
									{ name: 'id', type: 'int' },
									{ name: 'name', type: 'string' }
								],
								data: [
									[ 1, 'Этап 1' ],
									[ 2, 'Этап 2' ],
									[ 3, 'Этап 3' ],
									[ 4, 'Этап 4' ],
									[ 5, 'Этап 5' ],
									[ 6, 'Этап 6' ]
								]
							})
						}, {
							fieldLabel: langs('Диагноз'),
							xtype: 'swdiagcombo',
							hiddenName: 'Diag_id1'
						}, {
							fieldLabel: langs('Результат оказания ВМП'),
							hiddenName: 'HTMResult_id',
							xtype: 'swcommonsprcombo',
							comboSubject: 'HTMResult',
							listWidth: 180
						}]
					}, {
						defaults: { width: 180 },
						items: [{
							fieldLabel: langs('Очередь'),
							xtype: 'swbaselocalcombo',
							hiddenName: 'HTMQueueType_id',
							valueField: 'id',
							displayField: 'name',
							store: new Ext.data.SimpleStore({
								fields: [
									{ name: 'id', type: 'int' },
									{ name: 'name', type: 'string' }
								],
								data: [
									[ 1, 'Состоящие в очереди' ],
									[ 2, 'Ожидающие по причине' ],
									[ 3, 'Исключённые из очереди' ]
								]
							})
						}, {
							fieldLabel: langs('Дата обращения пациента в МО'),
							xtype: 'daterangefield',
							name: 'HTMRegister_ApplicationDate_Range',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}, {
							fieldLabel: langs('Дата выписки пациента из МО-ВМП'),
							xtype: 'daterangefield',
							name: 'HTMRegister_DisDate_Range',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}, {
							fieldLabel: langs('Планируемая дата госпитализации заполнено'),
							hiddenName: 'isSetPlannedHospDate',
							xtype: 'swyesnocombo'
						}, {
							fieldLabel: langs('Дата проведения оперативного вмешательства'),
							xtype: 'daterangefield',
							name: 'HTMRegister_OperDate_Range',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
						}]
					}]
				})]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_CRZRW + 120,
				id: this.id + '_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
					this.doSearch({firstLoad: true});
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_CRZRW + 121,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_CRZRW + 123,
				text: BTN_FRMCOUNT
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
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					var tabbar = this.findById(this.id + '_SearchFilter');
					tabbar.getActiveTab().fireEvent('activate', tabbar.getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_CRZRW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				return this.SearchFilters;
			},
			items: [ this.SearchFilters, this.HTMRegisterSearchFrame]
		});

		sw.Promed.swHTMRegisterWindow.superclass.initComponent.apply(this, arguments);
		
	},

	show: function() {

		if (!isUserGroup('HTMRegister') && !isUserGroup('SuperAdmin') && !isUserGroup('HTMRegister_Admin')) {
			sw.swMsg.alert('Сообщение', 'Форма доступна только для пользователей, с указанной группой "Регистр ВМП"');
			return false;
		}

		sw.Promed.swHTMRegisterWindow.superclass.show.apply(this, arguments);

		this.HTMRegisterSearchFrame.addActions({
			name:'open_emk', 
			text:langs('Открыть ЭМК'), 
			tooltip: langs('Открыть электронную медицинскую карту пациента'),
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});

		var base_form = this.SearchFilters.getForm();
		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		
		if (arguments[0].userMedStaffFact) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}


		this.doLayout();
		
		base_form.findField('RegisterType_id').setValue(1);
	},

	emkOpen: function() {
		var grid = this.HTMRegisterSearchFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			readOnly: false,
			ARMType: 'common'
		});
	},

	deletePersonRegister: function() {
		var grid = this.HTMRegisterSearchFrame.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') ) {
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		Ext.Msg.show({
			title: langs('Вопрос'),
			msg: langs('Удалить выбранную запись регистра?'),
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(langs('Удаление...')).show();
					Ext.Ajax.request({
						url: '/?c=Register&m=delete',
						params: {
							Register_id: record.get('Register_id')
						},
						callback: function(options, success, response) {
							this.getLoadMask().hide();
							if (success) {	
								var obj = Ext.util.JSON.decode(response.responseText);
								if( obj.success )
									grid.getStore().remove(record);
							} else {
								sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении записи регистра!'));
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	}
});
