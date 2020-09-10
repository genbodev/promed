/**
 * swIPRARegistryViewWindow - окно регистра ИПРА
 */
sw.Promed.swIPRARegistryViewWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Регистр ИПРА',
	width: 800,
	codeRefresh: true,
	objectName: 'swIPRARegistryViewWindow',
	id: 'swIPRARegistryViewWindow',
	objectSrc: '/jscore/Forms/Admin/swIPRARegistryViewWindow.js',
	buttonAlign: 'left',
	closable: true,
	Ministry_of_Health: function() {
		var ggo = getGlobalOptions();
		//рабочий и тестовый
		var listMZ = [150035,150031,13002457,9990000014,9990000015];
		//прогресс
		var listMZTest = [150035,150031,13026016,13026015];

		if(getRegionNick() == 'ufa'){
			if(location.hostname == '127.0.0.1'){
				return true;
			}
			else if(location.hostname == '192.168.200.175' || location.hostname == '192.168.200.16'){
				return ggo.lpu_id.inlist(listMZTest);
			}
			else{
				return ggo.lpu_id.inlist(listMZ);
			}
		} else {
			return isUserGroup('IPRARegistryEdit');
		}
	},
	IPRAdata_decode: {},
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function () {
		return Ext.getCmp('ORW_SearchButton');
	},
	inArray: function (needle, array) {
		for (var k in array) {
			if (array[k] == needle)
				return true;
		}

		return false;
	},
	doReset: function () {

		var base_form = this.findById('IPRARegistryFilterForm').getForm();
		base_form.reset();
		this.IPRARegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.IPRARegistrySearchFrame.ViewActions.person_register_out.setDisabled(true);
		this.IPRARegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.IPRARegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.IPRARegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.IPRARegistrySearchFrame.getGrid().getStore().removeAll();
		this.IPRARegistrySearchFrame.getGrid().getViewFrame().removeAll();
	},
	doSearch: function (params) {

		if (typeof params != 'object') {
			params = {};
		}

		var base_form = this.findById('IPRARegistryFilterForm').getForm();

		if (!params.firstLoad && this.findById('IPRARegistryFilterForm').isEmpty()) {
			sw.swMsg.alert('Ошибка', 'Не заполнено ни одно поле', function () {
			});
			return false;
		}

		var grid = this.IPRARegistrySearchFrame.getGrid();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {

				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (base_form.findField('PersonPeriodicType_id').getValue().toString().inlist(['2', '3']) && (!params.ignorePersonPeriodicType)) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Выбран тип поиска человека ' + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? 'по состоянию на момент случая' : 'по всем периодикам') + '.<br />При выбранном варианте поиск работает <b>значительно</b> медленнее.<br />Хотите продолжить поиск?',
				title: 'Предупреждение'
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('IPRARegistryFilterForm'));

		post.limit = 100;
		post.start = 0;
		post.IPRARegistryEdit = (isUserGroup('IPRARegistryEdit') && getRegionNick() != 'ufa')?1:null;

		if (base_form.isValid()) {
			this.IPRARegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function (records, options, success) {
					loadMask.hide();
				},
				params: post
			});

		}

	},
	getRecordsCount: function () {
		var st = this.IPRARegistrySearchFrame.getGrid().getStore();
		var noLines = false;
		if(st.totalLength == 0){
			noLines = true;
		}else if(st.totalLength == 1){
			if(typeof(st.getAt(0)) == 'undefined'){// бывает после нажатия "Обновить"
				noLines = true;
			}else if(! st.getAt(0).get('PersonRegister_id')){// если запись пустая
				noLines = true;
			}
		}
		if(noLines){
			sw.swMsg.alert('Подсчет записей', 'Найдено записей: 0');
			return;
		}

		var base_form = this.getFilterForm().getForm();

		if (!base_form.isValid()) {
			sw.swMsg.alert('Поиск', 'Проверьте правильность заполнения полей на форме поиска');
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());

		if (post.PersonPeriodicType_id == null) {
			post.PersonPeriodicType_id = 1;
		}
		post.IPRARegistryEdit = isUserGroup('IPRARegistryEdit')?1:0;// для показа общего количества записей #138061
		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.Records_Count != undefined) {
						sw.swMsg.alert('Подсчет записей', 'Найдено записей: ' + response_obj.Records_Count);
					} else {
						sw.swMsg.alert('Подсчет записей', response_obj.Error_Msg);
					}
				} else {
					sw.swMsg.alert('Ошибка', 'При подсчете количества записей произошли ошибки');
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	height: 550,
	openViewWindow: function (action) {
		if (getWnd('swIPRARegistryConfirmWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно просмотра уже открыто');
			return false;
		}

		var grid = this.IPRARegistrySearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected()) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
		var params = {
			action: action,
			editType: this.editType,
			Person_id: selected_record.data.Person_id,
			Person_SurName: selected_record.data.Person_Surname,
			Person_FirName: selected_record.data.Person_Firname,
			Person_SecName: selected_record.data.Person_Secname,
			Person_Birthday: selected_record.data.Person_Birthday,
			Person_Snils: selected_record.data.Person_Snils,
			IPRARegistry_id: selected_record.data.IPRARegistry_id
		};
		if(action == 'edit' && this.IPRARegistrySearchFrame.getAction('action_edit').isDisabled()){
			params.action = 'view';
		}
		getWnd('swIPRARegistryConfirmWindow').show(params);
	},
	openWindow: function (action) {

		var form = this.findById('IPRARegistryFilterForm').getForm();
		var grid = this.IPRARegistrySearchFrame.getGrid();

		var cur_win = this;



		if (action == 'include') {
			getWnd('swPersonSearchWindow').show({
				viewOnly: (cur_win.editType=='onlyRegister')?true:false,
				onClose: function ()
				{
				},
				onSelect: function (params)
				{
					getWnd('swPersonSearchWindow').hide();
					params.editErrors = false;
					getWnd('swIPRARegistryEditWindow').show(params);
				}
			});
		} else if (action == 'out') {
			if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id'))
			{
				Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
				return false;
			}
			var record = grid.getSelectionModel().getSelected();
			sw.Promed.personRegister.out({
				PersonRegister_id: record.get('PersonRegister_id')
				, Person_id: record.get('Person_id')
				, Diag_Name: 'IPRA'
				, PersonRegister_setDate: record.get('PersonRegister_setDate')
				, Lpu_did: getGlobalOptions().lpu_id
				, MedPersonal_did: getGlobalOptions().medpersonal_id
				, PersonRegister_disDate: getGlobalOptions().date
				, PersonRegisterType_SysNick: 'IPRA'
				, MorbusType_SysNick: 'IPRA'

				, callback: function (data) {
					grid.getStore().reload();
				}
			});
		}

	},
	showIpraCount: function() {
		var win = this;
		win.getLoadMask(langs('Подсчёт количества документов (ИПРА)')).show();

		Ext.Ajax.request({
			url: '/?c=IPRARegister&m=getIpraCount',
			callback: function(options, success, response) {
					win.getLoadMask().hide()
					var result = Ext.util.JSON.decode(response.responseText);

					if ( success && result && result.length > 0 ) {
						Ext.Msg.alert(langs('Коли-во документов ИПРА'), langs('Найдено документов ИПРА: ') + result[0].IpraCount);
					}
					else {
						Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
					}
			}
		});
	},
	initComponent: function () {
		var win = this;

		this.IPRARegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: !Ext.getCmp('swIPRARegistryViewWindow').Ministry_of_Health(), handler: function () {
						this.openWindow('include');
					}.createDelegate(this)},
				{name: 'action_edit', disabled: !Ext.getCmp('swIPRARegistryViewWindow').Ministry_of_Health(), handler: function () {
						this.openViewWindow('edit');
					}.createDelegate(this)},
				{name: 'action_view', handler: function () {
						this.openViewWindow('view');
					}.createDelegate(this)},
				{name: 'action_delete', hidden: true, handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH, /* /?c=Search&m=searchData */
			id: 'IPRARegistry',
			object: 'IPRARegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'IPRARegistry_id', type: 'int', hidden: true},
				{name: 'IPRARegistry_issueDate', type: 'date', format: 'd.m.Y', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Person_Snils', type: 'string', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Lpu_iid', type: 'int', hidden: true},
				{name: 'MedPersonal_iid', type: 'int', hidden: true},
				{name: 'MorbusType_id', type: 'int', hidden: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_Name', type: 'string', hidden: true, header: 'Причина исключения из регистра', width: 190},
				{name: 'IPRARegistry_Number', type: 'string', header: '№ ИПРА', width: 100},
				{name: 'Person_Surname', type: 'string', header: 'Фамилия', width: 250},
				{name: 'Person_Firname', type: 'string', header: 'Имя', width: 250},
				{name: 'Person_Secname', type: 'string', header: 'Отчество', width: 250},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: 'Д/р', width: 90},
				{name: 'Lpu_Nick', type: 'string', header: 'МО прикрепления', width: 150},
				{name: 'IPRARegistry_Confirm', header: 'Подтверждение МО', width: 150, renderer: function (v, p, r) {
					return (r.get('IPRARegistry_Confirm') == 2) ? 'подтвержден' : 'не подтвержден';
				}},
				{name: 'pmUser_name', header: 'Пользователь подтвердивший', type: 'string', width: 150},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: 'Дата включения в регистр', width: 150},
				{name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: (getRegionNick() == 'ufa' ? 'Дата снятия с учёта' : 'Дата исключения из регистра'), width: 170},
				{name: 'PersonRegisterOutCause_Name', type: 'string', header: (getRegionNick() == 'ufa' ? 'Причина снятия с учёта' : 'Причина исключения из регистра'), width: 200},
				{name: 'IsMeasuresComplete', header: 'Мероприятия выполнены', width: 120, hidden: getRegionNick() == 'ufa', renderer: function (v, p, r) {
					return (r.get('IsMeasuresComplete') == 2) ? 'Да' : 'Нет';
				}},
				{name: 'Diag', type: 'int', id: 'autoexpand', header: ''}
			],
			toolbar: true,
			totalProperty: 'totalCount',
			onBeforeLoadData: function () {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function () {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function (sm, index, record) {
				this.getAction('open_emk').setDisabled(false);
				if (win.Ministry_of_Health()) {
					this.getAction('action_delete').setDisabled(Ext.isEmpty(record.get('PersonRegister_id')));
				}

				var enableEdit = (!Ext.isEmpty(record.get('PersonRegister_id')) && record.get('IPRARegistry_Confirm') != 2);
				if (getRegionNick() == 'ufa') {
					enableEdit = (enableEdit && Ext.getCmp('swIPRARegistryViewWindow').Ministry_of_Health());
				}
				this.getAction('action_edit').setDisabled(!enableEdit);

				var enableRegisterOut = win.Ministry_of_Health() ;
				if (getRegionNick() == 'perm') {
					enableRegisterOut = isUserGroup('IPRARegistry','IPRARegistryEdit');
				}
				this.getAction('person_register_out').setDisabled(getRegionNick() == 'ufa' || !enableRegisterOut || Ext.isEmpty(record.get('PersonRegister_id')) || !Ext.isEmpty(record.get('PersonRegister_disDate')));

				this.getAction('action_view').setDisabled(Ext.isEmpty(record.get('PersonRegister_id')));
			},
			onDblClick: function (x, c, v) {
			}
		});


		var IPRAStore = this.IPRARegistrySearchFrame.getGrid().on(
				'rowdblclick',
				function () {
					if (getWnd('swIPRARegistryConfirmWindow').isVisible()) {
						sw.swMsg.alert('Сообщение', 'Окно просмотра уже открыто');
						return false;
					}

					if (!this.getSelectionModel().getSelected()) {
						return false;
					}
					var selected_record = this.getSelectionModel().getSelected();
					var params = {
						Person_id: selected_record.data.Person_id,
						editType: win.editType,
						Person_SurName: selected_record.data.Person_Surname,
						Person_FirName: selected_record.data.Person_Firname,
						Person_SecName: selected_record.data.Person_Secname,
						Person_Birthday: selected_record.data.Person_Birthday,
						Person_Snils: selected_record.data.Person_Snils,
						IPRARegistry_id: selected_record.data.IPRARegistry_id
					};
					if ( getRegionNick() != 'ufa' ) {
						if(win.IPRARegistrySearchFrame.getAction('action_edit').isDisabled())
							params.action = 'view';
						else
							params.action = 'edit';
					}
					getWnd('swIPRARegistryConfirmWindow').show(params);
				}
		);

		var IPRAStore = this.IPRARegistrySearchFrame.getGrid().getStore().on(
				'load',
				function () {

					var unicRecs = [];
					var Person_ids = [];

					var recs = this.data.items;

					for (var k in recs) {
						if (typeof recs[k] == 'object') {

							if (recs[k].data.Person_id) {
								if (Ext.getCmp('swIPRARegistryViewWindow').inArray(recs[k].get('Person_id'), Person_ids) === false) {
									Person_ids.push(recs[k].data.Person_id);
									unicRecs.push(recs[k]);
								}
							}
						}
					}


					this.removeAll();


					for (var k in unicRecs) {
						if (typeof unicRecs[k] == 'object') {
							this.add(unicRecs[k]);
						}
					}
				}
		);

		Ext.apply(this, {
			buttons: [{
					handler: function () {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					tabIndex: TABINDEX_ORW + 120,
					id: 'ORW_SearchButton',
					text: BTN_FRMSEARCH
				}, {
					handler: function () {
						this.doReset();
					}.createDelegate(this),
					iconCls: 'resetsearch16',
					tabIndex: TABINDEX_ORW + 121,
					text: BTN_FRMRESET
				}, {
					handler: function () {
						this.getRecordsCount();
					}.createDelegate(this),
					tabIndex: TABINDEX_ORW + 123,
					text: BTN_FRMCOUNT
				},
				{
					handler: function() {
						this.showIpraCount();
					}.createDelegate(this),
					hidden: getRegionNick() != 'ufa' || !this.Ministry_of_Health(),
					text: langs('Показать количество документов(ИПРА)')
				},
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						this.buttons[this.buttons.length - 2].focus();
					}.createDelegate(this),
					onTabAction: function () {
						this.findById('ORW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ORW_SearchFilterTabbar').getActiveTab());
					}.createDelegate(this),
					tabIndex: TABINDEX_ORW + 124,
					text: BTN_FRMCLOSE
				}],
			getFilterForm: function () {
				if (this.filterForm == undefined) {
					this.filterForm = this.findById('IPRARegistryFilterForm');

				}
				return this.filterForm;
			},
			items: [getBaseSearchFiltersFrame({
					isDisplayPersonRegisterRecordTypeField: false,
					allowPersonPeriodicSelect: false,
					id: 'IPRARegistryFilterForm',
					labelWidth: 130,
					ownerWindow: this,
					searchFormType: 'IPRARegistry',
					tabIndexBase: TABINDEX_ORW,
					tabPanelHeight: 225,
					tabPanelId: 'ORW_SearchFilterTabbar',
					tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 220,
							layout: 'form',
							listeners: {
								'activate': function () {
									this.getFilterForm().getForm().findField('PersonRegisterType_id').focus(250, true);
								}.createDelegate(this)
							},
							title: '<u>6</u>. Регистр',
							items: [
								{
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											items: [
												{
													hidden: (getRegionNick() == 'ufa'),
													hideLabel: (getRegionNick() == 'ufa'),
													valueField: 'header',
													comboData: [
														['Attachment','Прикреплению'],
														['DirectionToMse','Направлению на МСЭ']
													],
													comboFields: [
														{name: 'header', type:'string'},
														{name: 'header_Name', type:'string'}
													],
													value: 'Attachment',
													fieldLabel: langs('Фильтр по'),
													width: 200,
													xtype: 'swstoreinconfigcombo',
													hiddenName: 'PersonRegister_FilterBy',
													name: 'PersonRegister_FilterBy'
												},
												{
													xtype: 'swpersonregistertypecombo',
													hiddenName: 'PersonRegisterType_id',
													width: 200
												}, {
													fieldLabel: 'Дата включения в регистр',
													name: 'PersonRegister_setDate_Range',
													plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
													width: 170,
													xtype: 'daterangefield'
												}, {
													fieldLabel: 'Дата исключения из регистра',
													name: 'PersonRegister_disDate_Range',
													plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
													width: 170,
													xtype: 'daterangefield'
												}, {
													fieldLabel: '№ ИПРА',
													name: 'PersonRegister_number_IPRA',
													width: 180,
													/*maskRe:/[\d\/\.]/,
													 msgTarget:'text_number_IPRA',
													 emptyText: 'XXXXX.XX.XX/XXXX',
													 regex:/^\d{1,5}\.\d{1,2}\.\d{1,2}\/\d{4}$/,
													 invalidText: '<br/><br/><br/><br/><br/>Вводимое значение должно иметь формат "AAAAA.BB.CC/DDDD" , где:<br/>\n\
													 AAAAA - число, имеющее до 5 цифр;<br/>\n\
													 BB - число, имеющее до 2 цифр;<br/>\n\
													 CC - число, имеющее до 2 цифр, но не больше 12;<br/>\n\
													 DDDD - четырехзначное число, которое не больше текущего года.',
													 validator: function() {
													 var numipra = this.getValue();
													 var arr_numipra = numipra.split('/');
													 var arr_numipra0 = arr_numipra[0].split('.');
													 var cdate = new Date();
													 var cyear = cdate.getFullYear();
													 if (arr_numipra0[2] > 12 || arr_numipra[1] > cyear) {
													 return false;
													 }
													 else {
													 return true;
													 }
													 },  */
													xtype: 'textfield'
												}, {
													fieldLabel: 'Подтверждение МО',
													hiddenName: 'PersonRegister_confirm_IPRA',
													xtype: 'swyesnocombo',
													width: 70
												}, {
													fieldLabel: 'Наименование ФГУ МСЭ',
													hiddenName: 'PersonRegister_buro_MCE',
													xtype: 'combo',
													width: 170,
													editable: false,
													displayField: 'LpuBuilding_Name',
													valueField: 'LpuBuilding_Code',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'{values.LpuBuilding_Name}&nbsp;',
															'</div></tpl>'
													),
													store: new Ext.data.JsonStore({
														url: '/?c=IPRARegister&m=getAllBureau',
														fields: [{name: 'LpuBuilding_Code', type: 'int'}, {name: 'LpuBuilding_Name', type: 'string', height: 10}],
													})
												},
												{
													fieldLabel: 'МО направившая на МСЭ',
													xtype: 'swlpucombo',
													autoload: true,
													mode: 'local',
													width: 200,
													hiddenName: 'IPRARegistry_DirectionLPU_id',

												}
											]
										},
										{
											layout: 'form',
											border: false,
											items: [
												{
													fieldLabel: 'МО сопровождения',
													xtype: 'swlpucombo',
													// задача https://redmine.swan.perm.ru/issues/100089
													hidden: true,
													hideLabel: true,
													autoload: true,
													id: 'LPU_idid',
													mode: 'local',
													width: 200,
													hiddenName: 'LPU_id',
													listeners: {
														render: function () {

														}
													}
												}, {
													fieldLabel: 'Дата окончания срока ИПРА',
													name: 'IPRARegistry_EndDate_Range',
													plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
													width: 170,
													xtype: 'daterangefield'
												}, {
													fieldLabel: 'Дата выдачи ИПРА',
													name: 'IPRARegistry_issueDate_Range',
													plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
													width: 170,
													xtype: 'daterangefield'
												}, {
													fieldLabel: 'Медицинская реабилитация',
													hiddenName: 'IPRARegistryData_MedRehab_yn',
													xtype: 'combo',
													width: 170,
													editable: false,
													mode: 'local',
													displayField: 'yn',
													valueField: 'id',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'{values.yn}&nbsp;',
															'</div></tpl>'
													),
													store: new Ext.data.SimpleStore({
														fields: [{name: 'id', type: 'int'}, {name: 'yn', type: 'string'}],
														data: [[1, '0. Не нуждается'], [2, '1. Нуждается']]
													})
												}, {
													fieldLabel: 'Реконструктивная хирургия',
													hiddenName: 'IPRARegistryData_ReconstructSurg_yn',
													xtype: 'combo',
													width: 170,
													editable: false,
													mode: 'local',
													displayField: 'yn',
													valueField: 'id',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'{values.yn}&nbsp;',
															'</div></tpl>'
													),
													store: new Ext.data.SimpleStore({
														fields: [{name: 'id', type: 'int'}, {name: 'yn', type: 'string'}],
														data: [[1, '0. Не нуждается'], [2, '1. Нуждается']]
													})
												}, {
													fieldLabel: 'Протезирование и ортезирование',
													hiddenName: 'IPRARegistryData_Orthotics_yn',
													xtype: 'combo',
													width: 170,
													editable: false,
													mode: 'local',
													displayField: 'yn',
													valueField: 'id',
													triggerAction: 'all',
													tpl: new Ext.XTemplate(
															'<tpl for="."><div class="x-combo-list-item">',
															'{values.yn}&nbsp;',
															'</div></tpl>'
													),
													store: new Ext.data.SimpleStore({
														fields: [{name: 'id', type: 'int'}, {name: 'yn', type: 'string'}],
														data: [[1, '0. Не нуждается'], [2, '1. Нуждается']]
													})
												}, {
													fieldLabel: 'Мероприятия выполнены',
													hiddenName: 'IsMeasuresComplete',
													xtype: 'swyesnocombo',
													width: 170
												}, 
												new sw.Promed.SwProMedUserCombo({
													fieldLabel: 'Пользователь подтвердивший',
													hiddenName: 'pmUser_confirmID',
													width: 300
												}),
											]
										},
										{
											layout: 'column',
											id: 'text_number_IPRA',
											border: false,
										}
									]
								}
							]
						}

					]
				}),
				this.IPRARegistrySearchFrame]
		});

		sw.Promed.swIPRARegistryViewWindow.superclass.initComponent.apply(this, arguments);

	},
	layout: 'border',
	listeners: {
		/*
		 'beforeShow': function(win) {
		 if (String(getGlobalOptions().groups).indexOf('IPRARegistry', 0) < 0 && getGlobalOptions().CurMedServiceType_SysNick != 'minzdravdlo')
		 {
		 sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «'+ win.title +'»');
		 return false;
		 }
		 },
		 */
		'hide': function (win) {
			win.doReset();
		},
		'maximize': function (win) {
			win.findById('IPRARegistryFilterForm').doLayout();
		},
		'restore': function (win) {
			win.findById('IPRARegistryFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('ORW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('IPRARegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function () {
		sw.Promed.swIPRARegistryViewWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('IPRARegistryFilterForm').getForm();

		//Убираем лишние поисковые поля
		Ext.getCmp('swIPRARegistryViewWindow').findById('IPRARegistryFilterForm').items.items[0].items.items[0].hide();
		Ext.getCmp('swIPRARegistryViewWindow').findById('IPRARegistryFilterForm').getForm().findField('PersonCard_IsDms').hide();
		Ext.getCmp('swIPRARegistryViewWindow').findById('IPRARegistryFilterForm').getForm().findField('PersonCard_IsDms').hideLabel = true;

		base_form.findField('IsMeasuresComplete').setContainerVisible(getRegionNick() != 'ufa');

		this.IPRARegistrySearchFrame.addActions({
			name: 'person_register_import',
			text: 'Импорт',
			tooltip: 'Импорт',
			disabled: !Ext.getCmp('swIPRARegistryViewWindow').Ministry_of_Health(),
			iconCls: 'archive16',
			handler: function () {
				getWnd('swIPRARegistryImportWindow').show();
			}

		}, 1);

		this.IPRARegistrySearchFrame.addActions({
			name: 'person_register_dop_import',
			text: 'Импорт доп. полей',
			tooltip: 'Импорт доп. полей',
			hidden: true,
			disabled: true,
			iconCls: 'archive16',
			handler: function () {
				getWnd('swIPRARegistryImportDopWindow').show();
			}

		}, 2);

		this.IPRARegistrySearchFrame.addActions({
			name: 'person_register_export',
			text: 'Экспорт',
			tooltip: 'Экспорт',
			hidden: getRegionNick() == 'ufa',
			disabled: !isUserGroup(['IPRARegistryEdit']),
			iconCls: 'database-export16',
			handler: function () {
				getWnd('swMeasuresRehabExportWindow').show();
			}.createDelegate(this)
		}, 3);

		this.IPRARegistrySearchFrame.addActions({
			name: 'person_register_out',
			text: (getRegionNick() == 'ufa' ? 'Снять с учёта' : 'Исключить из регистра'),
			tooltip: (getRegionNick() == 'ufa' ? 'Снять с учёта' : 'Исключить из регистра'),
			iconCls: 'delete16',
			disabled: !Ext.getCmp('swIPRARegistryViewWindow').Ministry_of_Health() || getRegionNick() == 'ufa',
			handler: function () {
				this.openWindow('out');
			}.createDelegate(this)
		}, 11);

		var enableErrorIPRA = Ext.getCmp('swIPRARegistryViewWindow').Ministry_of_Health();
		if (getRegionNick() == 'perm') {
			enableErrorIPRA = isUserGroup(['IPRARegistry','IPRARegistryEdit']);
		}

		this.IPRARegistrySearchFrame.addActions({
			name: 'errorIPRA',
			text: 'Ошибки',
			tooltip: 'Ошибки',
			disabled: !enableErrorIPRA,
			iconCls: 'stac-accident-injured16',
			handler: function () {
				getWnd('swIPRARegistryErrors').show();
			}
		}, 10);

		this.IPRARegistrySearchFrame.addActions({
			name: 'open_emk',
			text: 'Открыть ЭМК',
			tooltip: 'Открыть электронную медицинскую карту пациента',
			iconCls: 'open16',
			handler: function () {
				this.emkOpen();
			}.createDelegate(this)
		});

		var base_form = this.findById('IPRARegistryFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();

		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			} else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function (data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		this.editType = 'all';
		if(arguments[0] && arguments[0].editType)
		{
			this.editType = arguments[0].editType;
		}
		if(isUserGroup('IPRARegistry') || isUserGroup('IPRARegistryEdit')){

		}
		if(!this.Ministry_of_Health()){
			base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		}

		if (!this.Ministry_of_Health() || getRegionNick() != 'ufa') {
			var lpu_id = getGlobalOptions().lpu_id;
			var moSoprov = base_form.findField('LPU_id');
			moSoprov.setValue(lpu_id);
			moSoprov.setDisabled(true);
		}

		if (String(getGlobalOptions().groups).indexOf('IPRARegistry', 0) >= 0) {
			base_form.findField('AttachLpu_id').setDisabled(false);
		} else {
			base_form.findField('AttachLpu_id').setDisabled(true);
		}

		if(getWnd('swWorkPlaceMZSpecWindow').isVisible()){
			base_form.findField('AttachLpu_id').setValue('');
			base_form.findField('AttachLpu_id').setDisabled(false);
		}

		this.doLayout();

		base_form.findField('PersonRegisterType_id').setValue(1);
		if(getRegionNick() != 'ufa'){
			base_form.findField('PersonRegister_FilterBy').setValue('Attachment');
		}

		var ipraTab = Ext.getCmp('ORW_SearchFilterTabbar');
		ipraTab.setActiveTab(5);
		ipraTab.setActiveTab(0);
	},
	emkOpen: function ()
	{
		var grid = this.IPRARegistrySearchFrame.getGrid();

		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id'))
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
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
			ARMType: 'common',
			readOnly: (this.editType == 'onlyRegister')?true:false,
			callback: function ()
			{
			}.createDelegate(this)
		});
	},
	deletePersonRegister: function () {
		var grid = this.IPRARegistrySearchFrame.getGrid();

		if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id'))
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
			return false;
		}
		var record = grid.getSelectionModel().getSelected();

		Ext.Msg.show({
			title: 'Вопрос',
			msg: 'Удалить выбранную запись регистра?',
			buttons: Ext.Msg.YESNO,
			fn: function (btn) {
				if (btn === 'yes') {
					this.getLoadMask('Удаление...').show();
					Ext.Ajax.request({
						url: '/?c=PersonRegister&m=delete',
						params: {
							PersonRegister_id: record.get('PersonRegister_id')
						},
						callback: function (options, success, response) {
							this.getLoadMask().hide();
							if (success) {
								var obj = Ext.util.JSON.decode(response.responseText);
								if (obj.success)
									grid.getStore().remove(record);
							} else {
								sw.swMsg.alert('Ошибка', 'Ошибка при удалении записи регистра!');
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	}
});