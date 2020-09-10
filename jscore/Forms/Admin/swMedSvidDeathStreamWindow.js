/**
* swMedSvidDeathStreamWindow - окно поточного ввода медсвидетельств о смерти.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Salakhov Rustam
* @version      21.04.2010
*/

sw.Promed.swMedSvidDeathStreamWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 880,
	id: 'MedSvidDeathStreamWindow',
	title: lang['medsvidetelstva_o_smerti'], 
	layout: 'border',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	listeners:
	{
		beforeshow: function()
		{
			//
		},
		resize: function()
		{
			if(this.layout.layout) {
				this.doLayout();
			}
		}
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
		}
		return this.loadMask;
	},
	setBegDateTime: function(first) 
	{
		form = this;
		Ext.Ajax.request(
		{
			callback: function(opt, success, response) 
			{
				if (success && response.responseText != '') 
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);
					form.begDate = response_obj.begDate;
					form.begTime = response_obj.begTime;
					form.SearchGrid.setParam('begDate',response_obj.begDate);
					form.SearchGrid.setParam('begTime',response_obj.begTime);
					if (first)
					{
						form.loadGridWithFilter();
					}
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	openEmk: function() {
		var grid = this.SearchGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Person_id')) ) {
			return false;
		}

		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			ARMType: 'common',
			readOnly: true
		});

		return true;
	},
	/**
	 * Получение периода за последние 7 дней
	 */
	getPeriod7DaysLast: function ()
	{
		var date2 = (Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
		var date1 = date2.add(Date.DAY, -6).clearTime();
		return Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y');
	},
	show: function()
	{
		var win = this;

		sw.Promed.swMedSvidDeathStreamWindow.superclass.show.apply(this, arguments);
		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly){
			this.viewOnly = arguments[0].viewOnly;
		}
		if(this.viewOnly == false && !this.SearchGrid.getAction('action_isp'))
		{
			this.SearchGrid.addActions({name:'action_isp', id: 'msd_action_isp', text: lang['deystviya'], menu: this.ActionsMenu, iconCls : 'x-btn-text', icon: 'img/icons/digital-sign16.png'});
			this.SearchGrid.addActions({name:'action_newsvid', id: 'msd_action_newsvid', text: lang['svidetelstvo_na_osnove_dannogo'], menu: this.NewSvidMenu, iconCls: 'add16'}, 1);
		}
		this.SearchGrid.addActions({
			name:'action_openemk',
			text: lang['otkryit_emk'],
			hidden: (getRegionNick() == 'perm' && isUserGroup('ZagsUser')),
			handler: function() { win.openEmk(); },
			iconCls: 'open16'
		}, 4);

		var base_form = this.UserPanel.getForm();

		this.ARMType = null;
		if (arguments[0] && arguments[0].ARMType) {
			this.ARMType = arguments[0].ARMType;
		}
		this.fromPatMorphArm = false;
		if (arguments[0] && arguments[0].fromPatMorphArm) {
			this.fromPatMorphArm = arguments[0].fromPatMorphArm;
		}
		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly){
			this.viewOnly = arguments[0].viewOnly;
		}

		// по умолчанию заполняем дату выдачи
		base_form.findField('Give_Date').setValue(this.getPeriod7DaysLast());

		if (this.ARMType == 'zags') {
			this.findById('msdswStreamInfo').hide();
			this.SearchGrid.getAction('action_add').hide();
			this.SearchGrid.getAction('action_edit').hide();
			this.ActionsMenu.items.itemAt(0).hide();
			this.ActionsMenu.items.itemAt(1).hide();
			this.doLayout();
		} else {
			this.findById('msdswStreamInfo').show();
			this.SearchGrid.getAction('action_add').show();
			this.SearchGrid.getAction('action_edit').show();
			this.ActionsMenu.items.itemAt(0).show();
			this.ActionsMenu.items.itemAt(1).show();
			this.doLayout();
		}

		this.clearLpu();
		base_form.findField('Lpu_id').fireEvent('change', base_form.findField('Lpu_id'), base_form.findField('Lpu_id').getValue());

		if ( base_form.findField('LpuRegion_id').getStore().getCount() == 0 ) {
			base_form.findField('LpuRegion_id').getStore().load({
				callback: function(records, options, success) {
					if ( !success ) {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_uchastkov']);
						return false;
					}
				},
				params: {
					'add_without_region_line': true
				}
			});
		}

		this.getLoadMask().show();

		this.center();
		this.maximize();
		this.SearchGrid.removeAll();
		this.clearFilters();
		this.setBegDateTime(true);
		this.getLoadMask().hide();
		this.loadGridWithFilter();
	},
	onOpenForm: function(person_data)
	{
		this.SearchGrid.setParam('Person_id',  person_data.Person_id, false);
		this.SearchGrid.setParam('PersonEvn_id',  person_data.PersonEvn_id, false);
		this.SearchGrid.setParam('Server_id',  person_data.Server_id, false);
		getWnd('swPersonSearchWindow').hide();
		this.SearchGrid.run_function_add = false;
		this.SearchGrid.runAction('action_add');
		this.getLoadMask().hide();
	},
	addMedSvid: function()
	{
		var win = this;
		var params = new Object();
		var action = 'add';
		
		getWnd('swPersonSearchWindow').show(
		{
			onClose: function() 
			{
				if (win.SearchGrid.getGrid().getSelectionModel().getSelected()) 
				{
					win.SearchGrid.getGrid().getView().focusRow(win.SearchGrid.getGrid().getStore().indexOf(win.SearchGrid.getGrid().getSelectionModel().getSelected()));
				}
				else 
				{
					win.SearchGrid.focus();
				}
			}.createDelegate(this),
			onSelect: function(person_data) {	
				function showSwMedSvidDeathEditWindow() {
					getWnd('swMedSvidDeathEditWindow').show({
						action: action,
						formParams: params
					});
					swPersonSearchWindow.hide();
				};
				
				var swPersonSearchWindow = getWnd('swPersonSearchWindow');
				params.Person_id 	= person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id 	= person_data.Server_id;

				if (person_data.Person_IsDead === 'true' && !getRegionNick().inist(['kz', 'msk'])) {
					swPersonSearchWindow.getLoadMask('Проверка существования мед. свидетельства, выписанного на выбранного человека').show();
					Ext.Ajax.request({
						method: 'POST',					
						url: '/?c=MedSvid&m=getDeathSvidByAttr',
						params: {
							Person_id: params.Person_id,
							OrderByDeathSvid_id: 'desc'				
						},

						failure: function () {
							swPersonSearchWindow.getLoadMask().hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_svidetelstva']);
						},
						success: function (response) {
							swPersonSearchWindow.getLoadMask().hide();
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.length && result[0].DeathSvid_Ser && result[0].DeathSvid_Num && result[0].DeathSvidType_Name) {
									sw.swMsg.alert(lang['oshibka'], 'Актуальное свидетельство ' + result[0].DeathSvid_Ser + ' №' + result[0].DeathSvid_Num + ' (' + result[0].DeathSvidType_Name + ') о смерти данного человека уже существует. Новое свидетельство можно создать только на основе актуального');
							} else {
								showSwMedSvidDeathEditWindow();
							}
						},
					});
				} else {
					showSwMedSvidDeathEditWindow();
				}
			},
			searchMode: 'all',
			allowUnknownPerson: true
		});
	},
	newMedSvid: function(mode) {
		var win = this;
		var params = new Object();
		var grid = this.findById('MedSvidDeathStreamWindowSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('DeathSvid_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		params = record.data;

		// При нажатии на кнопку выполняется поиск записи мед свидетельства выписанного на основании текущего
		win.getLoadMask('Проверка существования мед. свидетельства, выписанного на основании текущего').show();
		Ext.Ajax.request({
			url: '/?c=MedSvid&m=checkDeathSvidExist',
			params: {
				DeathSvid_pid: params.DeathSvid_id
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						if (mode == 6) {
							// Взамен испорченного (на другого пациента)
							getWnd('swPersonSearchWindow').show({
								onClose: function()
								{
									if (win.SearchGrid.getGrid().getSelectionModel().getSelected())
									{
										win.SearchGrid.getGrid().getView().focusRow(win.SearchGrid.getGrid().getStore().indexOf(win.SearchGrid.getGrid().getSelectionModel().getSelected()));
									}
									else
									{
										win.SearchGrid.focus();
									}
								}.createDelegate(this),
								onSelect: function(person_data) {
									getWnd('swPersonSearchWindow').getLoadMask('Проверка существования мед. свидетельства, выписанного на выбранного человека').show();
									Ext.Ajax.request({
										url: '/?c=MedSvid&m=checkDeathSvidExist',
										params: {
											Person_id: person_data.Person_id
										},
										callback: function(options, success, response) {
											getWnd('swPersonSearchWindow').getLoadMask().hide();
											if (success && response.responseText != '') {
												var result = Ext.util.JSON.decode(response.responseText);
												if (result.success) {
													getWnd('swMedSvidDeathEditWindow').show({
														action: 'edit',
														modeNewSvid: mode,
														formParams: params,
														PersonData: {
															Person_id: person_data.Person_id,
															PersonEvn_id: person_data.PersonEvn_id,
															Server_id: person_data.Server_id
														}
													});
													getWnd('swPersonSearchWindow').hide();
												}
											}
										}
									});
								},
								searchMode: 'all',
								allowUnknownPerson: true
							});
						} else {
							getWnd('swMedSvidDeathEditWindow').show({
								action: 'edit',
								modeNewSvid: mode,
								formParams: params
							});
						}
					}
				}
			}
		});
	},
	editMedSvid: function(action) {
		var params = new Object();		
		var grid = this.findById('MedSvidDeathStreamWindowSearchGrid').getGrid();
	
		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('DeathSvid_id') ) {
			return false;
		}
		
		var record = grid.getSelectionModel().getSelected();
		params = record.data;

		getWnd('swMedSvidDeathEditWindow').show({
			action: action,
			formParams: params
		});
	},
	printMedSvidTipografBlank: function(type){
		var current_window = this;
		var record = current_window.SearchGrid.getGrid().getSelectionModel().getSelected();

		if(record && record.get('ReceptType_id') == 2){
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.printMedSvid(type);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Медицинское свидетельство о смерти выписано на листе, выполнить печать на типографском бланке?',
				title: lang['vopros']
			});
		}else{
			this.printMedSvid(type);
		}
	},
	printMedSvid: function(type) {
		var current_window = Ext.getCmp('MedSvidDeathStreamWindow');
		var record = current_window.SearchGrid.getGrid().getSelectionModel().getSelected();
		var isbad_id = record.get('DeathSvid_IsBad');
		if (isbad_id != 1) { sw.swMsg.alert(lang['oshibka'], lang['svidetelstvo_otmecheno_kak_isporchennoe']); return false; }
		if (!record) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrano_ni_odno_svidetelstvo']);
			return false;
		}
		var svid_id = record.get('DeathSvid_id');
		if ( !svid_id )
			return false;
		var id_salt = Math.random();
		var win_id = 'print_svid' + Math.floor(id_salt * 10000);

		// https://redmine.swan.perm.ru/issues/51402
		// Было для Уфы, стало для всех регионов
		var DeathSvidReport = 'DeathSvid';

		if (type && type === 'blank'){
			DeathSvidReport = 'DeathSvid_blank';
		} else if(type && type === 'blank_dbl_pnt') {
			DeathSvidReport = 'DeathSvid_blank_dbl_pnt';
		} else if (record.get('DeathSvid_IsDuplicate') == 2) {
			DeathSvidReport = 'DeathSvid_Dublikat';
			if (type && type === 'dbl_pnt') {
				DeathSvidReport += '_dbl_pnt';
			}
		} else if (type && type === 'dbl_pnt') {
			DeathSvidReport = 'DeathSvid_dbl_pnt';
		} else if (type && (type === 'blank_tipograf_dbl_pnt')) {
			DeathSvidReport = 'DeathSvid_blank_dbl_pnt_dopechat'; //двустороння печать
		}

		if( type && type.inlist(['dbl_pnt', 'blank_dbl_pnt', 'blank_tipograf_dbl_pnt']) ){
			printBirt({
				'Report_FileName': DeathSvidReport+'.rptdesign',
				'Report_Params': '&paramDeathSvid=' + svid_id,
				'Report_Format': 'pdf'
			});
		} else {
			printBirt({
				'Report_FileName': DeathSvidReport+'.rptdesign',
				'Report_Params': '&paramDeathSvid=' + svid_id,
				'Report_Format': 'pdf'
			});
			if(type && type === 'blank'){
				printBirt({
					'Report_FileName': 'DeathSvid_Oborot_blank.rptdesign',
					'Report_Params': '&paramDeathSvid=' + svid_id,
					'Report_Format': 'pdf'
				});
			} else {
				printBirt({
					'Report_FileName': 'DeathSvid_Oborot.rptdesign',
					'Report_Params': '&paramDeathSvid=' + svid_id,
					'Report_Format': 'pdf'
				});
			}
		}
		// var win = window.open('/?c=MedSvid&m=printMedSvid&svid_id=' + svid_id + '&svid_type=death', win_id);
	},
	keys: 
	[{
		fn: function(inp, e) 
		{
			var win = Ext.getCmp('MedSvidDeathStreamWindow');
			switch (e.getKey()) 
			{
				case Ext.EventObject.INSERT:
					win.addMedSvid();
					break;
			}
		},
		key: [Ext.EventObject.INSERT],
		stopEvent: true
	}],
	enableLpuRegionField: function() {
		var base_form = this.UserPanel.getForm();
		if ( base_form.findField('viewMode').getValue() == 2 && !Ext.isEmpty(base_form.findField('Lpu_id').getValue()) && base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id ) {
			base_form.findField('LpuRegion_id').enable();
		}
		else {
			base_form.findField('LpuRegion_id').clearValue();
			base_form.findField('LpuRegion_id').disable();
		}
	},
	initComponent: function() 
	{
		var form = this;
		var IsActualStore = new Ext.data.SimpleStore({
			fields: [
				'IsActual',
				'displayText'
			],
			data: [[0, lang['vse']], [2, lang['aktualnyie']], [1, lang['ne_aktualnyie']]]
		});
		
		this.UserPanel = new Ext.form.FormPanel(
		{
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'north',
			autoHeight: true,
			frame: true,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					this.loadGridWithFilter();
				}.createDelegate(this),
				stopEvent: true
			}, {
				ctrl: true,
				fn: function(inp, e) {
					this.clearFilters();
				},
				key: 188,
				scope: this,
				stopEvent: true
			}],
			items: 
			[{
				id: 'msdswStreamInfo',
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px; padding-bottom: 15px;',
				labelAlign: 'right',
				labelWidth: 120,
				items: [{
					layout: 'column',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					columnWidth: .36,
					labelAlign: 'right',
					labelWidth: 100,
					items: [{
						layout: 'form',
						width: 300,
						labelWidth: 100,
						border: false,
						items: [{
							fieldLabel: lang['rejim_prosmotra'],
							listeners: {
								'change': function(combo, newValue, oldValue) {
									form.enableLpuRegionField();
								}
							},
							xtype: 'combo',
							hiddenName: 'viewMode',
							triggerAction: 'all',
							forceSelection: true,
							editable: false,
							store: [
								[1, lang['vyipisannyie_v_mo']],
								[2, lang['po_prikreplennomu_naseleniyu']]
							],
							allowBlank: false,
							value: 1,
							//width: 175
							maxLength: 30,
							width: 175,
						}]
					}/*, {
						layout: 'form',
						border: false,
						width: 270,
						labelWidth: 80,
						items: [{
							disabled: true,
							fieldLabel: lang['polzovatel'],
							name: 'pmUser_Name',
							width: 175,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						border: false,
						items: [{
							disabled: true,
							fieldLabel: lang['data_nachala_vvoda'],
							name: 'Stream_begDateTime',
							width: 175,
							xtype: 'textfield'
						}]
					}*/]
				}]
			}, {
				layout: 'column',
				border: false,	
				labelAlign: 'right',
				labelWidth: 120,
				items: [{
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					//columnWidth: .45,
					width: 300,
					labelAlign: 'right',
					labelWidth: 100,
					items:
					[{
						xtype: 'textfield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.translit(true, true) ],
						fieldLabel: lang['familiya'],
						name: 'Person_Surname'
					}, {
						xtype: 'textfield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.translit(true, true) ],
						fieldLabel: lang['imya'],
						name: 'Person_Firname'
					}, {
						xtype: 'textfield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.translit(true, true) ],
						fieldLabel: lang['otchestvo'],
						name: 'Person_Secname'
					}, {
						fieldLabel: lang['data_rojdeniya'],
						name: 'Birth_Date',
						width: 175,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						xtype: 'daterangefield'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					//columnWidth: .36,
					width: 320,
					labelAlign: 'right',
					labelWidth: 130,
					items: 
					[new Ext.form.ComboBox({
						fieldLabel: lang['sostoyanie'],
						hiddenName: 'IsActual',
						width: 175,
						typeAhead: true,
						triggerAction: 'all',
						lazyRender:true,
						mode: 'local',
						store: IsActualStore,
						value: 0,
						valueField: 'IsActual',
						displayField: 'displayText'
					}), {
						fieldLabel: lang['nomer_svidetelstva'],
						name: 'Svid_Num',
						width: 175,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['data_smerti'],
						name: 'Death_Date',
						width: 175,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						xtype: 'daterangefield'
					}, {
						xtype: 'daterangefield',
						width: 175,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						fieldLabel: lang['data_vyidachi'],
						name: 'Give_Date'
					}]
				},{
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					// hidden: !(getRegionNick() === 'ufa'),
					width: 350,
					labelAlign: 'right',
					labelWidth: 100,
					items:
					[new sw.Promed.SwBaseLocalCombo({
						fieldLabel: lang['prichina_smerti'],
						hiddenName: 'DeathCause',
						//width: 223,
						anchor: '100%',
						typeAhead: true,
						triggerAction: 'all',
						lazyRender:true,
						mode: 'local',
						store: new Ext.data.SimpleStore({
							fields: [
								'DeathCause_id',
								'displayText'
							],
							data: [
								['', ''],
								['Diag_iid', lang['neposredstvennaya_prichina_smerti']],
								['Diag_tid', lang['patologicheskoe_sostoyanie']],
								['Diag_mid', lang['pervonachalnaya_prichina_smerti']],
								['Diag_eid', lang['vneshnie_prichinyi']],
								['Diag_oid', lang['prochie_vajnyie_sostoyaniya']]
							]
						}),
						valueField: 'DeathCause_id',
						displayField: 'displayText'
					}), {
						fieldLabel: lang['kod_diagnoza_s'],
						hiddenName: 'Diag_Code_From',
						listWidth: 620,
						anchor: '100%',
						valueField: 'Diag_Code',
						//width: 590,
						xtype: 'swdiagcombo'
					}, {
						fieldLabel: lang['po'],
						hiddenName: 'Diag_Code_To',
						listWidth: 620,
						anchor: '100%',
						valueField: 'Diag_Code',
						//width: 590,
						xtype: 'swdiagcombo'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					//columnWidth: .36,
					width: 250,
					labelAlign: 'right',
					labelWidth: 60,
					items:
					[{
						xtype: 'swlpucombo',
						width: 175,
						fieldLabel: lang['mo'],
						listeners: {
							'change': function(combo, newValue, oldValue) {
								form.enableLpuRegionField();
							}
						},
						hiddenName: 'Lpu_id',
						disabled: !(isSuperAdmin() || isUserGroup('ZagsUser') || getWnd('swWorkPlaceMZSpecWindow').isVisible() || isUserGroup('MIACSuperAdmin')),
						value: !(isSuperAdmin() || isUserGroup('ZagsUser') || isUserGroup('MIACSuperAdmin')) ? getGlobalOptions().lpu_id : ''
					}, {
						displayField: 'LpuRegion_Name',
						editable: true,
						fieldLabel: lang['uchastok'],
						forceSelection: true,
						typeAhead: true,
						hiddenName: 'LpuRegion_id',
						triggerAction: 'all',
						valueField: 'LpuRegion_id',
						width: 175,
						xtype: 'swlpuregioncombo'
					},
					// NGS - START
					new sw.Promed.SwReceptTypeCombo({
						allowBlank: true,
						fieldLabel:  'Тип св-ва',
						width: 175,
						validateOnBlur: true,
						hidden: !['vologda'].includes(getRegionNick()), // displayed on for Vologda only
						hideLabel: !['vologda'].includes(getRegionNick()), // displayed on for Vologda only
						listeners: {
							'expand': function () {
								this.setStoreFilter();
							}
						},
						setStoreFilter: function () {
							this.getStore().clearFilter();
							this.getStore().filterBy(function (rec) {
								return rec.get('ReceptType_Code') != 3;
							});
						}
					}),
					// NGS - END	
					{
						layout: 'column',
						border: false,
						labelAlign: 'right',
						style: 'float: right;',
						labelWidth: 100,
						items: [{
							layout: 'form',
							border: false,
							bodyStyle:'background:#DFE8F6;',
							//style: 'margin-left: 92px;',
							width: 80,
							items:
								[{
									xtype: 'button',
									text: lang['nayti'],
									iconCls: 'search16',
									disabled: false,
									topLevel: true,
									allowBlank:true,
									handler: function ()
									{
										form.loadGridWithFilter();
									}
								}]
						},  {
							layout: 'form',
							border: false,
							bodyStyle:'background:#DFE8F6;',
							width: 73,
							items:
								[{
									xtype: 'button',
									text: lang['sbros'],
									iconCls: 'resetsearch16',
									disabled: false,
									topLevel: true,
									allowBlank:true,
									handler: function ()
									{
										form.loadGridWithFilter(true);
									}
								}]
						}]
					}]
				}]
			}]
		});

		this.NewSvidMenu = new Ext.menu.Menu({
			items: [
				{name:'new_svid1', text:lang['dublikat'], handler: function() {this.newMedSvid(1);}.createDelegate(this)},
				{name:'new_svid2', text:lang['vzamen_predvaritelnogo'], handler: function() {this.newMedSvid(2);}.createDelegate(this)},
				{name:'new_svid3', text:lang['okonchatelnoe'], handler: function() {this.newMedSvid(3);}.createDelegate(this)},
				{name:'new_svid4', text:lang['vzamen_okonchatelnogo'], handler: function() {this.newMedSvid(4);}.createDelegate(this)},
				{name:'new_svid5', text:lang['vzamen_isporchennogo'], handler: function() {this.newMedSvid(5);}.createDelegate(this)},
				{name:'new_svid6', text:langs('Взамен испорченного (на другого пациента)'), hidden: getRegionNick() == 'kz', handler: function() {this.newMedSvid(6);}.createDelegate(this)}
			]
		});

		this.ActionsMenu = new Ext.menu.Menu({
			id: 'msd_actions_menu',
			items: [
				{name:'action_isbad', text:lang['pometit_kak_isporchennyiy'], iconCls:'x-group-by-icon', handler: function() { this.setBadSvid(2); }.createDelegate(this)},
				{name:'action_nobad', text:lang['snyat_otmetku_isporchennyiy'], iconCls:'x-group-by-icon', handler: function() { this.setBadSvid(1); }.createDelegate(this)},
				{
					name: 'action_signDeathSvid',
					text: langs('Подписать'),
					tooltip: langs('Подписать'),
					handler: function() {
						var me = this;
						var rec = form.SearchGrid.getGrid().getSelectionModel().getSelected();
						if((rec && rec.get('MissingDataList')=='') || getRegionNick().inlist(['kz']) ) {
							if (rec && rec.get('DeathSvid_id')) {
								getWnd('swEMDSignWindow').show({
									EMDRegistry_ObjectName: 'DeathSvid',
									EMDRegistry_ObjectID: rec.get('DeathSvid_id'),
									callback: function (data) {
										if (data.preloader) {
											rec.set('signAccess', 'view');
											rec.commit();
										}

										if (data.error) {
											if (rec) {
												rec.set('signAccess', 'edit');
												rec.commit();
											}
										}

										if (data.success) {
											form.SearchGrid.getGrid().getStore().reload();
										}
									}
								});
							}
						}else{
							sw.swMsg.show({
								buttons: sw.swMsg.OK,
								icon: Ext.MessageBox.WARNING,
								width: '400',
								msg: langs('Для регистрации медсвидетельства о смерти в РЭМД ЕГИСЗ обязательно наличие следующих данных получателя свидетельства:') + rec.get('MissingDataList').substring(0, rec.get('MissingDataList').length - 1) + '. ' + langs('Подписание свидетельства невозможно.'),
								title: langs('Ошибка')
							});
							return false;
						}
					}
				}, {
					name: 'action_showDeathSvidVersionList',
					text: langs('Список версий документа'),
					tooltip: langs('Список версий документа'),
					handler: function() {
						var rec = form.SearchGrid.getGrid().getSelectionModel().getSelected();
						if (rec && rec.get('DeathSvid_id')) {
							getWnd('swEMDVersionViewWindow').show({
								EMDRegistry_ObjectName: 'DeathSvid',
								EMDRegistry_ObjectID: rec.get('DeathSvid_id')
							});
						}
					}
				}
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'SearchGrid',
			region: 'center',
			height: 203,
			title:lang['svidetelstva_o_smerti_spisok'],
			dataUrl: '/?c=MedSvid&m=loadMedSvidDeathListGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			useEmptyRecord: false,
			stringfields:
			[
				{ name: 'DeathSvid_id', type: 'int', header: 'ID', key: true },
				{ name: 'DeathSvid_IsBad', type: 'int', hidden: true },
				{ name: 'DeathSvid_IsActual', type: 'int', hidden: true },
				{ name: 'DeathSvid_IsLose', type: 'int', hidden: true },
				{ name: 'DeathSvid_SignCount', type: 'int', hidden: true },
				{ name: 'DeathSvid_MinSignCount', type: 'int', hidden: true },
				{ name: 'DeathSvidType_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'LpuType_Code', type: 'string', hidden: true },
				{ name: 'Person_rid', type: 'int', hidden: true },
				{ name: 'DeathSvid_IsDuplicate', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true, isparams: true },
				{ name: 'ReceptType_id', type: 'int', hidden: true },
				{ name: 'DeathSvid_GiveDate', type: 'date', format: 'd.m.Y', header: langs('Дата выдачи') },
				{ name: 'DeathSvid_Ser', type: 'string', header: langs('Серия'), width:75 },
				{ name: 'DeathSvid_Num', type: 'string', header: langs('Номер'), width:75 },
				{ name: 'PersonInfo_IsSetDeath', sortable: false, type: 'checkcolumnedit', isparams: true, hidden: getRegionNick() != 'ufa', header: langs('Протокол установления смерти'), width: 150 },
				{ name: 'PersonInfo_IsParsDeath', sortable: false, type: 'checkcolumnedit', isparams: true, hidden: getRegionNick() != 'ufa', header: langs('Протокол разбора случая смерти'), width: 150 },
				{ name: 'DeathSvidType_Name', type: 'string', header: langs('Вид'), width:150 },
				{ name: 'Person_FIO', type: 'string', header: langs('ФИО'), width: 250, id: 'autoexpand' },
				{ name: 'Person_Birthday', type: 'string', format: 'd.m.Y', header: langs('Дата рождения') },
				{ name: 'DeathSvid_DeathDate', type: 'string', format: 'd.m.Y', header: langs('Дата смерти') },
				{ name: 'Lpu_Nick', type: 'string', header: langs('МО'), width: 150},
				{ name: 'MedPersonal_FIO', type: 'string', header: langs('ФИО врача'), width: 150},
				{ name: 'Diag_iidName', type: 'string', header: langs('Непосредственная причина смерти'), width: 150},
				{ name: 'Diag_tidName', type: 'string', header: langs('Патологическое состояние'), width: 150},
				{ name: 'Diag_midName', type: 'string', header: langs('Первоначальная причина смерти'), width: 150},
				{ name: 'Diag_eidName', type: 'string', header: langs('Внешние причины'), width: 150},
				{ name: 'DeathSvid_signDT', type: 'string', hidden: true },
				{ name: 'DeathSvid_IsSigned', renderer: function(v, p, r){
						if (!Ext.isEmpty(r.get('DeathSvid_IsSigned'))) {
							var s = '';

							if (r.get('DeathSvid_IsSigned') == 2) {
								if (r.get('DeathSvid_SignCount') == r.get('DeathSvid_MinSignCount')) {
									s += '<img src="/img/icons/emd/doc_signed.png">';
								} else {
									s += '<span class="sp_doc_signed" data-qtip="' + r.get('DeathSvid_SignCount') + ' из ' + r.get('DeathSvid_MinSignCount') + '">' + r.get('DeathSvid_SignCount') + '</span>';
								}
							} else {
								s += '<img src="/img/icons/emd/doc_notactual.png">';
							}

							s += r.get('DeathSvid_signDT');

							return s;
						} else if (r.get('DeathSvid_SignCount') > 0) {
							return '<span class="sp_doc_unsigned" data-qtip="' + r.get('DeathSvid_SignCount') + ' из ' + r.get('DeathSvid_MinSignCount') + '">' + r.get('DeathSvid_SignCount') + '</span>';
						} else {
							return '<img src="/img/icons/emd/doc_notsigned.png">';
						}
					}, header: langs('Статус документа'), width: 120 },
				{ name: 'MissingDataList', type: 'string', hidden: true }

			],
			actions:
			[
				{name:'action_add', func:  function() {this.addMedSvid();}.createDelegate(this)},
				{name:'action_edit', handler: function() { this.editMedSvid('edit'); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.editMedSvid('view'); }.createDelegate(this)},
				{name:'action_delete',  hidden: true, disabled: true},
				{name:'action_save',  url: '/?c=MedSvid&m=refreshPersonInfo'},
				{
					name:'action_print',
					menuConfig: {
						printObject: {text: lang['pechat_svidetelstva'], handler: function(){this.printMedSvid();}.createDelegate(this)},
						printDbl: {text: lang['pechat_svidetelstva_dvuhstoronnyaya'], name: 'printDbl', handler: function(){this.printMedSvid('dbl_pnt');}.createDelegate(this)},
						printOnBlank: {text: lang['pechat_svidetelstva_na_blanke'], name: 'printOnBlank', hidden: getRegionNick() == 'ufa', handler: function(){this.printMedSvid('blank');}.createDelegate(this)},
						printDblOnBlank: {text: lang['pechat_svidetelstva_na_blanke_dvuhstoronnyaya'], name: 'printDblOnBlank', hidden: getRegionNick() == 'ufa', handler: function(){this.printMedSvid('blank_dbl_pnt');}.createDelegate(this)},
						//printingCertificateOnLetterhead: {text: lang['pechat_svidetelstva_na_tipografskom_blanke'], name: 'printingCertificateOnLetterhead', hidden: getRegionNick() != 'khak', handler: function(){this.printMedSvidTipografBlank('printOnTipografBlank');}.createDelegate(this)},
						printingCertificateOnLetterheadDbl: {text: lang['pechat_svidetelstva_na_tipografskom_blanke_dvuhstoronnyaya'], name: 'printingCertificateOnLetterheadDbl', hidden: !getRegionNick().inlist(['vologda', 'khak']), handler: function(){this.printMedSvidTipografBlank('blank_tipograf_dbl_pnt');}.createDelegate(this)},
					}
				}
			],
			saveAllParams: true,
			saveAtOnce: true,
			afterSaveEditForm: function(RegistryQueue_id, records)
			{
				var form = Ext.getCmp('MedSvidDeathStreamWindow');
			},
			onLoadData: function()
			{
				var form = Ext.getCmp('MedSvidDeathStreamWindow');
				form.checkGrants();
			},
			onRowSelect: function(sm,index,record)
			{
				var msgrant = isMedSvidAccess();

				form.NewSvidMenu.items.itemAt(0).disable();
				form.NewSvidMenu.items.itemAt(1).disable();
				form.NewSvidMenu.items.itemAt(2).disable();
				form.NewSvidMenu.items.itemAt(3).disable();
				form.NewSvidMenu.items.itemAt(4).disable();
				form.NewSvidMenu.items.itemAt(5).disable();

				form.ActionsMenu.items.itemAt(0).disable();
				form.ActionsMenu.items.itemAt(1).disable();

				form.SearchGrid.setActionDisabled('action_edit', true);
				// Разрешить изменять свидетельство с незаполненным разделом "Получатель" в части сведений о получателе без выписки нового свидетельства.
				// Кнопка изменить не доступна если режим просмотра по прикрепленном населению
				var prikOnly = (form.SearchGrid.getGrid().getStore().baseParams.viewMode && form.SearchGrid.getGrid().getStore().baseParams.viewMode == 2);
				if (!Ext.isEmpty(record.get('DeathSvid_id')) && Ext.isEmpty(record.get('Person_rid'))&&(isUserGroup('MedSvidDeath')||(!prikOnly && msgrant && getGlobalOptions().lpu_id == record.get('Lpu_id')))) {
					form.SearchGrid.setActionDisabled('action_edit', false);
				}
				
				// Помечать как испорченные и выписывать на их основе новые медсвидетельства разрешено только для свидетельств, выписанных в текущей МО. В остальных случаях кнопки и пункты контекстного меню должны быть неактивны.
				if (msgrant && getGlobalOptions().lpu_id == record.get('Lpu_id')) {
					var isBad = (record.get('DeathSvid_IsBad') == 2);
					var isLose = (record.get('DeathSvid_IsLose') == 2);
					var isActual = (record.get('DeathSvid_IsActual') == 2);

					if (isLose) {
						// если утеряно (refs #67964)
						form.ActionsMenu.items.itemAt(0).hide();
						form.ActionsMenu.items.itemAt(1).hide();
					} else if (isBad) {
						// если испорчено то можно пометить как не испорченное
						form.ActionsMenu.items.itemAt(0).hide();
						form.ActionsMenu.items.itemAt(1).show();
						form.ActionsMenu.items.itemAt(1).enable();
						// Взамен испорченного - для испорченных
						form.NewSvidMenu.items.itemAt(4).enable();
						form.NewSvidMenu.items.itemAt(5).enable();
					} else {
						// если не испорчено то можно пометить как испорченное
						form.ActionsMenu.items.itemAt(0).show();
						form.ActionsMenu.items.itemAt(0).enable();
						form.ActionsMenu.items.itemAt(1).hide();
					}

					if (isActual) {
						// Дубликат - для любого актуального
						form.NewSvidMenu.items.itemAt(0).enable();
						// Взамен предварительного - для актуального (предварительных и взамен предварительных)
						if (record.get('DeathSvidType_id').inlist([2, 3])) {
							form.NewSvidMenu.items.itemAt(1).enable();
						}
						// Окончательное - для актуального (предварительных и взамен предварительных)
						if (record.get('DeathSvidType_id').inlist([2, 3])) {
							form.NewSvidMenu.items.itemAt(2).enable();
						}
						// Взамен окончательного - для актуального (окончательных и взамен окончательных)
						if (record.get('DeathSvidType_id').inlist([1, 4])) {
							form.NewSvidMenu.items.itemAt(3).enable();
						}
					}
				}
			},
			onEnter: function() {
				if (!this.ViewActions.action_edit.isDisabled()) {
					this.ViewActions.action_edit.execute();
				}
				else if (!this.ViewActions.action_view.isDisabled()) {
					this.ViewActions.action_view.execute();
				}
			},
			onDblClick: function() {
				if (!this.ViewActions.action_edit.isDisabled()) {
					this.ViewActions.action_edit.execute();
				}
				else if (!this.ViewActions.action_view.isDisabled()) {
					this.ViewActions.action_view.execute();
				}
			}
		});
		
		this.SearchGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';
				if (row.get('DeathSvid_IsActual') == 1)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		Ext.apply(this, 
		{
			layout:'border',
			defaults: {split: true},
			buttons: 
			[{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: 
			[form.UserPanel,
			{
				border: false,
				xtype: 'panel',
				region: 'center',
				layout:'border',
				items: [form.SearchGrid]
			}]
		});
		sw.Promed.swMedSvidDeathStreamWindow.superclass.initComponent.apply(this, arguments);
	},
	setBadSvid: function(bad_id) {
		var win = this;
		var grid = this.findById('MedSvidDeathStreamWindowSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('DeathSvid_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var svid_id = record.get('DeathSvid_id');
		var isbad_id = record.get('DeathSvid_IsBad');
		
		if (isbad_id == bad_id) { sw.swMsg.alert(lang['oshibka'], lang['svidetelstvo_uje_otmecheno_kak'] + (isbad_id == 1 ? lang['deystvuyuschee'] : lang['isporchennoe'])); return false; }
		
		/*if (bad_id == 1) {
			var fio_birth = record.get('Person_FIO') + '_' + record.get('Person_Birthday');
			var isbad_err = false;
			grid.getStore().each(function(r) {
				if (r.data['Person_FIO'] + "_" + r.data['Person_Birthday'] == fio_birth && r.data['DeathSvid_id'] != svid_id && r.data['DeathSvid_IsBad'] == 1)
					isbad_err = true;
			});
			if (isbad_err) { sw.swMsg.alert(lang['oshibka'], lang['uje_suschestvuet_neisporchennoe_svidetelstvo_na_dannogo_cheloveka']); return false; }
		}*/

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (response.responseText != '') {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj && response_obj.Alert_Msg && response_obj.Error_Msg == 'YesNo') {
									record.set('DeathSvid_isBad', 2);
									record.commit();
									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function(buttonId, text, obj) {
											if ( buttonId == 'yes' ) {
												win.newMedSvid(5);
											}
										},
										icon: Ext.MessageBox.QUESTION,
										msg: response_obj.Alert_Msg,
										title: 'Внимание'
									});
									return true;
								} else if (success) {
									Ext.getCmp('MedSvidDeathStreamWindowSearchGrid').ViewGridStore.reload();
								} else {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При смене статуса свидетельства произошла ошибка');
								}
							}
						},
						params: {
							svid_id: svid_id,
							svid_type: 'death',
							bad_id: bad_id
						},
						url: '/?c=MedSvid&m=setBadSvid'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: bad_id == 2 ? lang['pometit_dannoe_zayavlenie_kak_isporchennoe'] : lang['snyat_otmetku_isporchen'],
			title: lang['vopros']
		});
	},
	loadGridWithFilter: function(clear) {
		var form = this;				
		if (clear) {
			form.clearFilters();
			this.SearchGrid.removeAll();
		}

		var base_form = form.UserPanel.getForm(),
			params = form.UserPanel.getForm().getValues();

		if ((!Ext.isEmpty(base_form.findField('Diag_Code_To').getValue()) || !Ext.isEmpty(base_form.findField('Diag_Code_From').getValue()))
			&& Ext.isEmpty(base_form.findField('Give_Date').getValue1())
			&& Ext.isEmpty(base_form.findField('Give_Date').getValue2())
			&& Ext.isEmpty(base_form.findField('Death_Date').getValue1())
			&& Ext.isEmpty(base_form.findField('Death_Date').getValue2())

		){
			//sw.swMsg.alert(lang['oshibka'], lang['pojaluysta_zapolnite_datu_vyidachi']);
			sw.swMsg.alert(lang['oshibka'], 'Пожалуйста, заполните либо дату выдачи, либо дату смерти.');
			return false;
		}

		params.start = 0;
		params.limit = 100;
		if (!(isSuperAdmin() || isUserGroup('ZagsUser'))){
			if (haveArmType('spec_mz') || isUserGroup('MIACSuperAdmin')) {
				params.Lpu_id = form.UserPanel.getForm().findField('Lpu_id').getValue();
			} else {
				params.Lpu_id = form.UserPanel.getForm().findField('Lpu_id').getValue() || getGlobalOptions().lpu_id;
			}
		}

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
			globalFilters: params
		});
	},
	clearLpu: function(){
		var base_form = this.UserPanel.getForm();
		if (!(isSuperAdmin() || isUserGroup('ZagsUser') || getWnd('swWorkPlaceMZSpecWindow').isVisible() || isUserGroup('MIACSuperAdmin')) || this.fromPatMorphArm) {
			base_form.findField('Lpu_id').disable();
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
		} else {
			base_form.findField('Lpu_id').enable();
			base_form.findField('Lpu_id').clearValue();
		}
	},
	clearFilters: function () {
		var base_form = this.UserPanel.getForm();
		base_form.reset();

		// по умолчанию заполняем дату выдачи
		base_form.findField('Give_Date').setValue(this.getPeriod7DaysLast());

		this.clearLpu();
		base_form.findField('Lpu_id').fireEvent('change', base_form.findField('Lpu_id'), base_form.findField('Lpu_id').getValue());
	},
	checkGrants: function() {
		var msgrant = isMedSvidAccess();
		this.SearchGrid.ViewActions.action_add.setDisabled(!msgrant);
	}
});