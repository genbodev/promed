/**
 * swONMKRegistryViewWindow - окно регистра ОНМК
 */
sw.Promed.swONMKRegistryViewWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Регистр ОНМК',
	width: 800,
	codeRefresh: true,
	objectName: 'swONMKRegistryViewWindow',
	id: 'swONMKRegistryViewWindow',
	objectSrc: '/jscore/Forms/Admin/swONMKRegistryViewWindow.js',
	buttonAlign: 'left',
	closable: true,
	ONMKdata_decode: {},
	closeAction: 'hide',
	collapsible: true,
        playNotification: function()
        {
            Ext.get('swONMKRegistryViewWindowNotification').dom.play();
        },
        registryList: [],
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
		var base_form = this.findById('ONMKRegistryFilterForm').getForm();
		base_form.reset();
		this.ONMKRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.ONMKRegistrySearchFrame.getGrid().getStore().removeAll();
	},
	doSearch: function (params) {
		

		if (typeof params != 'object') {
			params = {};
		}

		var base_form = this.findById('ONMKRegistryFilterForm').getForm();

		var grid = this.ONMKRegistrySearchFrame.getGrid();

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

		var post = getAllFormFieldValues(this.findById('ONMKRegistryFilterForm'));

		post.limit = 100;
		post.start = 0;
		post.ONMKRegistryEdit = ((isUserGroup('ONMKRegistryEdit') && getRegionNick() != 'ufa')?1:null);

		if (base_form.isValid()) {
			this.ONMKRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
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
		/*
		if (getWnd('swONMKRegistryConfirmWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно просмотра уже открыто');
			return false;
		}
		*/
		var grid = this.ONMKRegistrySearchFrame.getGrid();
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
			ONMKRegistry_id: selected_record.data.ONMKRegistry_id
		};
		if(action == 'edit' && this.ONMKRegistrySearchFrame.getAction('action_edit').isDisabled()){
			params.action = 'view';
		}
		getWnd('swONMKRegistryConfirmWindow').show(params);
	},
	openWindow: function (action) {

		var form = this.findById('ONMKRegistryFilterForm').getForm();
		var grid = this.ONMKRegistrySearchFrame.getGrid();

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
					getWnd('swONMKRegistryEditWindow').show(params);
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
				, Diag_Name: 'ONMK'
				, PersonRegister_setDate: record.get('PersonRegister_setDate')
				, Lpu_did: getGlobalOptions().lpu_id
				, MedPersonal_did: getGlobalOptions().medpersonal_id
				, PersonRegister_disDate: getGlobalOptions().date
				, PersonRegisterType_SysNick: 'ONMK'
				, MorbusType_SysNick: 'ONMK'

				, callback: function (data) {
					grid.getStore().reload();
				}
			});
		}
	},
	showIpraCount: function() {
		var win = this;
		win.getLoadMask(langs('Подсчёт количества документов (ОНМК)')).show();

		Ext.Ajax.request({
			url: '/?c=ONMKRegister&m=getIpraCount',
			callback: function(options, success, response) {
					win.getLoadMask().hide()
					var result = Ext.util.JSON.decode(response.responseText);

					if ( success && result && result.length > 0 ) {
						Ext.Msg.alert(langs('Коли-во документов ОНМК'), langs('Найдено документов ОНМК: ') + result[0].IpraCount);
					}
					else {
						Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
					}
			}
		});
	},
	initComponent: function () {
		var win = this;		

		this.ONMKRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', handler: function () { this.openViewWindow('view');}.createDelegate(this)},
				{name: 'action_delete', hidden: true},
				{name: 'action_refresh'},
				{name: 'action_print', menuConfig: {printONMK: {name:'printONMK', text: langs('Печать ОНМК'), handler: function(){ alert('не реализовано'); /*win.doPrint();*/ } }
				}}
			],
		    getRowClass: function (row, index) {
				 //alert('Раскраска');
				 var cls = '';
				 if (row.get('selrow') == 1) {//  Выбранное значение шкалы
				  // alert('Раскраска');
				  cls = cls + ' x-grid-rowblue ';
				  cls = cls + ' x-grid-rowbold ';
				 }
				 return cls;
			},			
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH, /* /?c=Search&m=searchData */
			id: 'ONMKRegistry',
			object: 'ONMKRegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'vID', type: 'int', key: true, header: 'ID' },
				{name: 'PersonRegister_id', type: 'int', hidden: true},
				{name: 'ONMKRegistry_id', type: 'int', hidden: true},
				{name: 'ONMKRegistry_issueDate', type: 'date', format: 'd.m.Y', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', header: 'lpu_id', hidden: true},
				{name: 'MO_OK', type: 'int', header: 'MO_OK', hidden: true},				
				{name: 'ONMKRegistry_IsNew', type: 'int', hidden: true},

				{name: 'Person_Surname', type: 'string', header: '<center>Фамилия</center>', width: 130, align:'left'},
				{name: 'Person_Firname', type: 'string', header: '<center>Имя</center>', width: 80, align:'left'},
				{name: 'Person_Secname', type: 'string', header: '<center>Отчество</center>', width: 90, align:'left'},
				{name: 'Person_Age', type: 'string', header: 'Возраст', width: 70, align:'center'},
				//{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: 'Возраст', width: 90},
				{name: 'ONMKRegistry_Evn_DTDesease', type: 'date', format: 'd.m.Y', header: 'Дата<br>госпит-ии', width: 100, align:'center'},
				{name: 'ONMKRegistry_Evn_DTDesease_Time', type: 'string', header: 'Время<br>госпит-ии', width: 100, align:'center'}, 	
				{name: 'TimeBeforeStac', type: 'string', header: 'Время<br> от нач. заб.<br>до госпит-ии', width: 100, align:'center'},
				{name: 'Diag_Name', type: 'string', header: '<center>Диагноз</center>', width: 250, align:'left'},
				{name: 'Renkin', type: 'string', header: 'Рэнкин<br>при пост-ии', width: 70, align:'center'},
				{name: 'TLTDT', type: 'string', header: 'ТЛТ', width: 100, align:'center'},
				{name: 'TimeBeforeTlt', type: 'string', header: 'Оставшееся<br>время<br>для ТЛТ', width: 90, align:'center'},
				{name: 'Nihss', type: 'string', header: 'NIHSS<br>при пост-ии', width: 70, align:'center'},
				{name: 'ONMKRegistry_NIHSSAfterTLT', type: 'string', header: 'NIHSS<br>после ТЛТ', width: 70, align:'center'},
				{name: 'KTDT', type: 'string', header: 'КТ', width: 100, align:'center'},
				{name: 'MRTDT', type: 'string', header: 'МРТ', width: 100, align:'center'},
				{name: 'ConsciousType_Name', type: 'string', header: 'Сознание', width: 80},
				{name: 'BreathingType_Name', header: 'ИВЛ', width: 40, align:'center', renderer: function (v, p, r) {
					console.log('BreathingType_Name');
					console.log(r.get('BreathingType_Name'));
					return r.get('BreathingType_Name') != null && r.get('BreathingType_Name') != '' ? '<span style="color:#f00" align="center"><img src="/img/icons/tick16.png" width="12" height="12"/></span>' : '';
				}},
				{name: 'HasDiag', header: 'Повтор<br>ОНМК', width: 60, align:'center', renderer: function (v, p, r) {						
					return r.get('HasDiag') == 1 ? '<span style="color:#f00" align="center"><img src="/img/icons/tick16.png" width="12" height="12"/></span>' : '';
				}},
				{name: 'Lpu_Nick', header: 'МО госпит-ии', width: 180, align:'center', renderer: function (v, p, r) {						
					return r.get('MO_OK') == 1 ? r.get('Lpu_Nick') : '<span style="color:#f00">'+r.get('Lpu_Nick')+'</span>';
				}},
				{name: 'ONMKRegistry_SetDate', type: 'string', header: 'Дата<br>включения<br>в регистр', width: 110, align:'center'},
				{name: 'LeaveType_Name', type: 'string', header: 'Исход<br>госпит-ии', width: 80, align:'center'}
				
			],
			toolbar: true,
			totalProperty: 'totalCount',
			onBeforeLoadData: function () {
				//this.getButtonSearch().disable();
			}.createDelegate(this),
                        lastLoadGridDate: null,
                        auto_refresh: null,
                        html: '<audio id="swONMKRegistryViewWindowNotification"><source src="/audio/web/WavLibraryNet_Sound5825.mp3" type="audio/mpeg"></audio>',
			onLoadData: function (sm, index, records) {
				//this.getButtonSearch().enable();
                                
                                // #157110 Звуковое оповещение пользователя о событии в системе
                                if (getRegionNick() == 'ufa')
                                {
                                    var form = win;
                                    var grid = this.getGrid();
                                    grid.getStore().each(function(rec)
                                    {
                                        
                                            var vID = rec.get('vID');
                                            if (vID && !form.registryList.includes(vID))
                                            {
                                                form.playNotification();
                                                form.registryList.push(vID);
                                            }
                                        
                                    });
                                    grid.lastLoadGridDate = new Date();
                                    if(grid.auto_refresh)
                                    {
                                            clearInterval(grid.auto_refresh);
                                    }
                                    grid.auto_refresh = setInterval(
                                        function()
                                        {
                                            var cur_date = new Date();
                                            // если прошло более 2 минут с момента последнего обновления
                                            if(grid.lastLoadGridDate.getTime() < (cur_date.getTime()-120))
                                            {
                                                form.doSearch();
                                            }
                                        }.createDelegate(grid),
                                        120000
                                    );
                                }
			},
			onRowDeSelect: function (sm, index, record) {
				Ext.getCmp('ONMKRegistry').getGrid().getView().removeRowClass(index, 'x-grid-rowblue');
			},			
			onRowSelect: function (sm, index, record) {
				Ext.getCmp('ONMKRegistry').getGrid().getView().addRowClass(index, 'x-grid-rowblue');
				this.getAction('open_emk').setDisabled(false);

				this.getAction('action_view').setDisabled(Ext.isEmpty(record.get('PersonRegister_id')));
			},
			onDblClick: function (x, c, v) {
				/*
				var grid = Ext.getCmp('swONMKRegistryViewWindow').ONMKRegistrySearchFrame.getGrid()
								
				if (!grid.getSelectionModel().getSelected()) {
					return false;
				}
				var selected_record = grid.getSelectionModel().getSelected();
				var params = {
					editType: this.editType,
					Person_id: selected_record.data.Person_id,
					Person_SurName: selected_record.data.Person_Surname,
					Person_FirName: selected_record.data.Person_Firname,
					Person_SecName: selected_record.data.Person_Secname,
					Person_Birthday: selected_record.data.Person_Birthday,
					Person_Snils: selected_record.data.Person_Snils,
					ONMKRegistry_id: selected_record.data.ONMKRegistry_id
				};				
				getWnd('swONMKRegistryEditWindow').show(params);
				*/
			}
		});
		
		
		Ext.getCmp('ONMKRegistry').getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {			 
			 if (row.get('ONMKRegistry_IsNew') == 1) {
			  return ' x-grid-rowlightpink x-grid-rowbold';
			 }
			 return '';
			}
		});		


		var ONMKStore = this.ONMKRegistrySearchFrame.getGrid().on(
				'rowdblclick',
				function () {
					console.log(332);
					
					/*
					if (getWnd('swONMKRegistryConfirmWindow').isVisible()) {
						sw.swMsg.alert('Сообщение', 'Окно просмотра уже открыто');
						return false;
					}
					*/
					if (!this.getSelectionModel().getSelected()) {
						return false;
					}
					var selected_record = this.getSelectionModel().getSelected();
					console.log(selected_record);
					var params = {
						Person_id: selected_record.data.Person_id,
						Server_id: selected_record.data.Server_id,
						editType: win.editType,
						Person_SurName: selected_record.data.Person_Surname,
						Person_FirName: selected_record.data.Person_Firname,
						Person_SecName: selected_record.data.Person_Secname,
						Person_Birthday: selected_record.data.Person_Birthday,
						Person_Snils: selected_record.data.Person_Snils,
						ONMKRegistry_id: selected_record.data.ONMKRegistry_id
					};
					/*
					if ( getRegionNick() != 'ufa' ) {
						if(win.ONMKRegistrySearchFrame.getAction('action_edit').isDisabled())
							params.action = 'view';
						else
							params.action = 'edit';
					}
					*/
					//getWnd('swONMKRegistryConfirmWindow').show(params);
					
					
					getWnd('swONMKRegistryEditWindow').show(params);
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
					this.filterForm = this.findById('ONMKRegistryFilterForm');

				}
				return this.filterForm;
			},
			items: [getBaseSearchFiltersFrame({
					isDisplayPersonRegisterRecordTypeField: false,
					allowPersonPeriodicSelect: false,
					id: 'ONMKRegistryFilterForm',
					labelWidth: 130,
					ownerWindow: this,
					searchFormType: 'ONMKRegistry',
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
													xtype: 'swpersonregistertypecombo',
													hiddenName: 'PersonRegisterType_id',
													width: 200,
													listeners: {
														render: function(combo) {
															combo.setValue(1);
														}
													}
												}, {
													fieldLabel: 'Статус записи',
													hiddenName: 'ONMKRegistry_Status',
													xtype: 'combo',
													width: 200,
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
														data: [[1, 'Все активные случаи'], [2, 'Все'], [3, 'Непросмотренные'], [4, 'Просмотренные'], [5, 'Диагноз не подтвержден']]
													}),
													listeners: {
														render: function(combo) {
															combo.setValue(1);
														}
													}
												}, {													
													editable : true,
													ctxSerach: true,
													forceSelection: true,
													hiddenName : 'LPU_sid',
													fieldLabel: 'МО госпитализации',
													allowblank: true,
													id : 'SLW_Lpu_id',
													lastQuery : '',													
													listeners: {
														'blur': function(combo) {
															combo.allowBlank = true;															
														}
													},
													listWidth : 500,
													tpl: new Ext.XTemplate(
														'<tpl for="."><div class="x-combo-list-item">',
														'{[(values.Lpu_EndDate && values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыто "+ values.Lpu_EndDate  + ")" : values.Lpu_Nick ]}&nbsp;',
														'</div></tpl>'
													),
													width : 200,
													xtype : 'swlpucombo'																										
													
												}, {
													fieldLabel: 'Тип МО госпитализации',
													hiddenName: 'ONMKRegistry_TypeMO',
													xtype: 'combo',
													width: 200,
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
														data: [[1, 'Все'], [2, 'ПСО и РСЦ'], [3, 'ПСО'], [4, 'РСЦ'], [5, 'Прочие МО']]
													}),
													listeners: {
														render: function(combo) {
															combo.setValue(1);
														}
													}
												}, {
													fieldLabel: 'Дата госпитализации',
													name: 'ONMKRegistry_Evn_DTDesease',
													plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
													width: 200,
													xtype: 'daterangefield'
												}
											]
										},
										{
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'swdiagcombo',
													hiddenName: 'Diag_Code_From',
													valueField: 'Diag_Code',
													fieldLabel: 'Диагноз с',
													width: 200
												}, {
													fieldLabel: 'ТЛТ',
													hiddenName: 'ONMKRegistry_ISTLT',
													xtype: 'combo',
													width: 200,
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
														data: [[1, 'Проведен'], [2, 'Не проведен']]
													})
												}, {
													xtype: 'swlpuonmkcombo',
													hiddenName: 'LPU_id',
													valueField: 'Lpu_id',
													fieldLabel: 'РСЦ/ПСО/МО госп-ии',
													width: 200
												}, {
													fieldLabel: 'Исход заболевания',
													hiddenName: 'ONMKRegistry_ResultDesease',
													xtype: 'combo',
													width: 200,
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
														data: [[1, 'Выписка'], [2, 'Смерть']]
													})
												}, {
													fieldLabel: 'Дата включения в регистр',
													name: 'PersonRegister_setDate_Range',
													plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
													width: 200,
													xtype: 'daterangefield'
												}												
											]
										},{
											layout: 'form',
											border: false,
											align: 'right',
											labelWidth: 100,
											width: 400,
											items: [
												{
													xtype: 'swdiagcombo',
													hiddenName: 'Diag_Code_To',
													valueField: 'Diag_Code',
													fieldLabel: 'по',
													width: 200
												}
											]											
										}
									]
								}
							]
						}

					]
				}),
				this.ONMKRegistrySearchFrame]
		});

		sw.Promed.swONMKRegistryViewWindow.superclass.initComponent.apply(this, arguments);

	},
	doPrint: function() {
		var record = this.ONMKRegistrySearchFrame.getGrid().getSelectionModel().getSelected();

		if(!record) return;

		var params = '&ONMKRegistry_id=' + record.get('ONMKRegistry_id');

		printBirt({
			'Report_FileName': 'ONMKRegistry_PrintMedRehab.rptdesign',
			'Report_Params': params,
			'Report_Format': 'doc'
		});

		return true;
	},
	layout: 'border',
	listeners: {
		'hide': function (win) {
			win.doReset();
		},
		'maximize': function (win) {
			win.findById('ONMKRegistryFilterForm').doLayout();
		},
		'restore': function (win) {
			win.findById('ONMKRegistryFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('ORW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('ONMKRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function () {
		sw.Promed.swONMKRegistryViewWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('ONMKRegistryFilterForm').getForm();

		//Убираем лишние поисковые поля
		Ext.getCmp('swONMKRegistryViewWindow').findById('ONMKRegistryFilterForm').items.items[0].items.items[0].hide();
		Ext.getCmp('swONMKRegistryViewWindow').findById('ONMKRegistryFilterForm').getForm().findField('PersonCard_IsDms').hide();
		Ext.getCmp('swONMKRegistryViewWindow').findById('ONMKRegistryFilterForm').getForm().findField('PersonCard_IsDms').hideLabel = true;

		this.ONMKRegistrySearchFrame.addActions({
			name: 'open_emk',
			text: 'Открыть ЭМК',
			tooltip: 'Открыть электронную медицинскую карту пациента',
			iconCls: 'open16',
			handler: function () {
				this.emkOpen();
			}.createDelegate(this)
		});

		var base_form = this.findById('ONMKRegistryFilterForm').getForm();

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
		if(isUserGroup('ONMKRegistry') || isUserGroup('ONMKRegistryEdit')){

		}

		var lpu_id = getGlobalOptions().lpu_id;
		var moSoprov = base_form.findField('LPU_id');
		//moSoprov.setValue(lpu_id);
		//moSoprov.setDisabled(true);

		this.doLayout();
		
		//обновление признаков Подтвержден и Мониторинг		
		Ext.Ajax.request({ 
			url: '/?c=ONMKRegister&m=updateSluchData', 
			callback: function (options, success, response) {							
				if (!success) {
					sw.swMsg.alert('Ошибка', 'Ошибка при обновлении данных регистра!');
				}
			}.createDelegate(this)
		});    		
		
		
		
	},
	emkOpen: function ()
	{
		var grid = this.ONMKRegistrySearchFrame.getGrid();

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
		var grid = this.ONMKRegistrySearchFrame.getGrid();

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