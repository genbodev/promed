/**
* swMedSvidPntDeathStreamWindow - окно поточного ввода медсвидетельств о перинатальной смерти.
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

sw.Promed.swMedSvidPntDeathStreamWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 880,
	id: 'MedSvidPntDeathStreamWindow',
	title: lang['medsvidetelstva_o_perinatalnoy_smerti'], 
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

		sw.Promed.swMedSvidPntDeathStreamWindow.superclass.show.apply(this, arguments);
		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly){
			this.viewOnly = arguments[0].viewOnly;
		}
		if(this.viewOnly == false && !this.SearchGrid.getAction('action_isp'))
		{
			this.SearchGrid.addActions({name:'action_isp', id: 'mspd_action_isp', text: lang['deystviya'], menu: this.ActionsMenu});
			this.SearchGrid.addActions({name:'action_newsvid', id: 'mspd_action_newsvid', text: lang['svidetelstvo_na_osnove_dannogo'], menu: this.NewSvidMenu, iconCls: 'add16'}, 1);
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
		// по умолчанию заполняем дату выдачи 
		base_form.findField('Give_Date').setValue(this.getPeriod7DaysLast());

		if (this.ARMType == 'zags') {
			this.findById('mspdswStreamInfo').hide();
			this.SearchGrid.getAction('action_add').hide();
			this.SearchGrid.getAction('action_edit').hide();
			this.ActionsMenu.items.itemAt(0).hide();
			this.ActionsMenu.items.itemAt(1).hide();
			this.doLayout();
		} else {
			this.findById('mspdswStreamInfo').show();
			this.SearchGrid.getAction('action_add').show();
			this.SearchGrid.getAction('action_edit').show();
			this.ActionsMenu.items.itemAt(0).show();
			this.ActionsMenu.items.itemAt(1).show();
			this.doLayout();
		}

		this.clearLpu();
		this.getLoadMask().show();

		this.center();
		this.maximize();
		this.SearchGrid.removeAll();
		this.setBegDateTime(true);
		this.getLoadMask().hide();
		this.loadGridWithFilter();
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
				params.Person_id 	= person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id 	= person_data.Server_id;

				getWnd('swMedSvidPntDeathEditWindow').show({
					action: action,
					formParams: params
				});
				getWnd('swPersonSearchWindow').hide();
			},
			searchMode: 'all',
			allowUnknownPerson: true
		});
	},
	newMedSvid: function(mode) {
		var params = new Object();
		var grid = this.findById('MedSvidPntDeathStreamWindowSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PntDeathSvid_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		params = record.data;

		getWnd('swMedSvidPntDeathEditWindow').show({
			action: 'edit',
			modeNewSvid: mode,
			formParams: params
		});
	},
	editMedSvid: function(action) {
		var params = new Object();		
		var grid = this.findById('MedSvidPntDeathStreamWindowSearchGrid').getGrid();
	
		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PntDeathSvid_id') ) {
			return false;
		}
		
		var record = grid.getSelectionModel().getSelected();
		params = record.data;
		
		getWnd('swMedSvidPntDeathEditWindow').show({
			action: action,
			formParams: params
		});
	},
	printMedSvid: function(type) {
		var current_window = Ext.getCmp('MedSvidPntDeathStreamWindow');
		var record = current_window.SearchGrid.getGrid().getSelectionModel().getSelected();
		var isbad_id = record.get('PntDeathSvid_IsBad');
		if (isbad_id != 1) { sw.swMsg.alert(lang['oshibka'], lang['svidetelstvo_otmecheno_kak_isporchennoe']); return false; }
		if (!record) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrano_ni_odno_svidetelstvo']);
			return false;
		}
		var svid_id = record.get('PntDeathSvid_id');
		if ( !svid_id )
			return false;
		var id_salt = Math.random();
		var win_id = 'print_svid' + Math.floor(id_salt * 10000);
		/*if(getRegionNick().inlist([ 'ufa', 'khak' ]))
		{*/

		var PntDeathSvidReport = 'PntDeathSvid';
		
		if (record.get('PntDeathSvid_IsDuplicate') == 2) {
			PntDeathSvidReport = 'PntDeathSvid_Dublikat';
			if (type && type === 'dbl_pnt') {
				PntDeathSvidReport += '_dbl_pnt';
			}
		} else if (type && type === 'dbl_pnt') {
			PntDeathSvidReport = 'PntDeathSvid_dbl_pnt';
		}

		if( type && (type === 'dbl_pnt') ){
			printBirt({
			'Report_FileName': PntDeathSvidReport+'.rptdesign',
			'Report_Params': '&paramPntDeathSvid=' + svid_id,
			'Report_Format': 'pdf'
			});
		} else {
			printBirt({
			'Report_FileName': PntDeathSvidReport+'.rptdesign',
			'Report_Params': '&paramPntDeathSvid=' + svid_id,
			'Report_Format': 'pdf'
			});
			printBirt({
				'Report_FileName': 'PntDeathSvid_Oborot.rptdesign',
				'Report_Params': '&paramPntDeathSvid=' + svid_id,
				'Report_Format': 'pdf'
			});

		}

		/*}
		else{
			var win = window.open('/?c=MedSvid&m=printMedSvid&svid_id=' + svid_id + '&svid_type=pntdeath', win_id);
		}*/
	},
	keys: 
	[{
		fn: function(inp, e) 
		{
			var win = Ext.getCmp('MedSvidPntDeathStreamWindow');
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
				id: 'mspdswStreamInfo',
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px; padding-bottom: 15px;',
				labelAlign: 'right',
				labelWidth: 140,
				items: [{
					layout: 'column',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					columnWidth: .36,
					labelAlign: 'right',
					labelWidth: 140,
					items: [{
						layout: 'form',
						width: 340,
						labelWidth: 140,
						border: false,
						items: [{
							fieldLabel: lang['rejim_prosmotra'],
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
							width: 175
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
					width: 340,
					labelAlign: 'right',
					labelWidth: 140,
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
						xtype: 'textfield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.translit(true, true) ],
						fieldLabel: lang['familiya_materi'],
						name: 'Person_Surname'
					}, {
						xtype: 'textfield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.translit(true, true) ],
						fieldLabel: lang['fio_rebenka'],
						name: 'Child_Surname'
					}, {
						xtype: 'daterangefield',
						width: 175,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						fieldLabel: lang['data_rojdeniya'],
						name: 'Child_BirthDate'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					//columnWidth: .36,
					width: 270,
					labelAlign: 'right',
					labelWidth: 80,
					items: 
					[{
						xtype: 'daterangefield',
						width: 175,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						fieldLabel: lang['data_vyidachi'],
						name: 'Give_Date'
					}, {
						xtype: 'textfield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.translit(true, true) ],
						fieldLabel: lang['imya_materi'],
						name: 'Person_Firname'
					},
					{
						xtype: 'swpersonsexcombo',
						width: 175,
						codeField: 'Sex_id',
						fieldLabel: lang['pol_rebenka'],
						//name: 'Child_Sex'
						hiddenName: 'Sex_id'
					}, {
						fieldLabel: lang['data_smerti'],
						name: 'Death_Date',
						width: 175,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						xtype: 'daterangefield'
					}]
				}, {
                    layout: 'form',
                    border: false,
                    bodyStyle:'background:#DFE8F6;padding-right:5px;',
                    width: 350,
                    labelAlign: 'right',
                    labelWidth: 120,
                    items:
					[{
						xtype: 'swlpucombo',
						width: 175,
						fieldLabel: lang['mo'],
						name: 'Lpu_id',
						disabled: !(isSuperAdmin() || isUserGroup('ZagsUser') || getWnd('swWorkPlaceMZSpecWindow').isVisible() || isUserGroup('MIACSuperAdmin')),
						value: !(isSuperAdmin() || isUserGroup('ZagsUser') || isUserGroup('MIACSuperAdmin')) ? getGlobalOptions().lpu_id : ''
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
					]
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
					width: 340,
					labelAlign: 'right',
					labelWidth: 140,
					items:
					[{
						fieldLabel: lang['nomer_svidetelstva'],
						name: 'Svid_Num',
						width: 175,
						xtype: 'textfield'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
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
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					width: 80,
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
		});

		this.NewSvidMenu = new Ext.menu.Menu({
			items: [
				{name:'new_svid1', text:lang['dublikat'], handler: function() {this.newMedSvid(1);}.createDelegate(this)},
				{name:'new_svid2', text:lang['vzamen_predvaritelnogo'], handler: function() {this.newMedSvid(2);}.createDelegate(this)},
				{name:'new_svid3', text:lang['okonchatelnoe'], handler: function() {this.newMedSvid(3);}.createDelegate(this)},
				{name:'new_svid4', text:lang['vzamen_okonchatelnogo'], handler: function() {this.newMedSvid(4);}.createDelegate(this)},
				{name:'new_svid5', text:lang['vzamen_isporchennogo'], handler: function() {this.newMedSvid(5);}.createDelegate(this)}
			]
		});

		this.ActionsMenu = new Ext.menu.Menu({
			id: 'mspd_actions_menu',
			items: [
				{name:'action_isbad', text:lang['pometit_kak_isporchennyiy'], iconCls:'x-group-by-icon', handler: function() { this.setBadSvid(2); }.createDelegate(this)},
				{name:'action_nobad', text:lang['snyat_otmetku_isporchennyiy'], iconCls:'x-group-by-icon', handler: function() { this.setBadSvid(1); }.createDelegate(this)}
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'SearchGrid',
			region: 'center',
			height: 203,
			title:lang['svidetelstva_o_perinatalnoy_smerti_spisok'],
			dataUrl: '/?c=MedSvid&m=loadMedSvidPntDeathListGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,			
			stringfields:			
			[
				{ name: 'PntDeathSvid_id', type: 'int', header: 'ID', key: true },
				{ name: 'PntDeathSvid_IsBad', type: 'int', hidden: true },
				{ name: 'PntDeathSvid_IsActual', type: 'int', hidden: true },
				{ name: 'PntDeathSvid_IsLose', type: 'int', hidden: true },
				{ name: 'DeathSvidType_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'Person_rid', type: 'int', hidden: true },
				{ name: 'PntDeathSvid_IsDuplicate', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true, isparams: true },
				{ name: 'PntDeathSvid_GiveDate', type: 'date', format: 'd.m.Y', header: lang['data_vyidachi'] },
                { name: 'PntDeathSvid_BirthDate', type: 'string', format: 'd.m.Y', header: lang['data_rojdeniya_rebenka'],width:170 },
				{ name: 'PntDeathSvid_DeathDate', type: 'string', format: 'd.m.Y', header: lang['data_smerti_rebenka'],width:170 },
				{ name: 'PersonInfo_IsSetDeath', sortable: false, type: 'checkcolumnedit', isparams: true, hidden: getRegionNick() != 'ufa', header: lang['protokol_ustanovleniya_smerti'], width: 150 },
				{ name: 'PersonInfo_IsParsDeath', sortable: false, type: 'checkcolumnedit', isparams: true, hidden: getRegionNick() != 'ufa', header: lang['protokol_razbora_sluchaya_smerti'], width: 150 },
                { name: 'PntDeathSvid_ChildFio', type: 'string', header: lang['fio_rebenka'], width: 250 },
                { name: 'Child_Sex', type: 'string', header: lang['pol_rebenka'], width:75 },
				{ name: 'PntDeathSvid_Ser', type: 'string', header: lang['seriya'], width:75 },
				{ name: 'PntDeathSvid_Num', type: 'string', header: lang['nomer'], width:75 },
				{ name: 'DeathSvidType_Name', type: 'string', header: lang['vid'], width:150 },
				{ name: 'Person_FIO', type: 'string', header: lang['fio_materi'], width: 250, id: 'autoexpand' },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya_materi'], width: 140 },
				{ name: 'Lpu_Nick', type: 'string', header: lang['mo'], width: 150},
				{ name: 'MedPersonal_FIO', type: 'string', header: lang['fio_vracha'], width: 150}
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
						printDbl: {text: lang['pechat_svidetelstva_dvuhstoronnyaya'], name: 'printDbl', handler: function(){this.printMedSvid('dbl_pnt');}.createDelegate(this)}
					}
				}
			],
			saveAllParams: true,
			saveAtOnce: true,
			afterSaveEditForm: function(RegistryQueue_id, records)
			{
				var form = Ext.getCmp('MedSvidPntDeathStreamWindow');
			},
			onLoadData: function()
			{
				var form = Ext.getCmp('MedSvidPntDeathStreamWindow');
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

				form.ActionsMenu.items.itemAt(0).disable();
				form.ActionsMenu.items.itemAt(1).disable();

				form.SearchGrid.setActionDisabled('action_edit', true);
				// Разрешить изменять свидетельство с незаполненным разделом "Получатель" в части сведений о получателе без выписки нового свидетельства.
				// Кнопка изменить не доступна если режим просмотра по прикрепленном населению
				var prikOnly = (form.SearchGrid.getGrid().getStore().baseParams.viewMode && form.SearchGrid.getGrid().getStore().baseParams.viewMode == 2);
				if (!prikOnly && msgrant && !Ext.isEmpty(record.get('PntDeathSvid_id')) && Ext.isEmpty(record.get('Person_rid')) && getGlobalOptions().lpu_id == record.get('Lpu_id')) {
					form.SearchGrid.setActionDisabled('action_edit', false);
				}

				// Помечать как испорченные и выписывать на их основе новые медсвидетельства разрешено только для свидетельств, выписанных в текущей МО. В остальных случаях кнопки и пункты контекстного меню должны быть неактивны.
				if (msgrant && getGlobalOptions().lpu_id == record.get('Lpu_id')) {
					var isBad = (record.get('PntDeathSvid_IsBad') == 2);
					var isLose = (record.get('PntDeathSvid_IsLose') == 2);
					var isActual = (record.get('PntDeathSvid_IsActual') == 2);

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
				if (row.get('PntDeathSvid_IsActual') == 1)
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
		sw.Promed.swMedSvidPntDeathStreamWindow.superclass.initComponent.apply(this, arguments);
	},
	setBadSvid: function(bad_id) {
		var grid = this.findById('MedSvidPntDeathStreamWindowSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PntDeathSvid_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var svid_id = record.get('PntDeathSvid_id');
		var isbad_id = record.get('PntDeathSvid_IsBad');
		
		if (isbad_id == bad_id) { sw.swMsg.alert(lang['oshibka'], lang['svidetelstvo_uje_otmecheno_kak'] + (isbad_id == 1 ? lang['deystvuyuschee'] : lang['isporchennoe'])); return false; }
		
		if (bad_id == 1) {
			var ser_num = record.get('PntDeathSvid_Ser') + '_' + record.get('PntDeathSvid_Num');
			var isbad_err = false;
			grid.getStore().each(function(r) {
				if (r.data['PntDeathSvid_Ser'] + "_" + r.data['PntDeathSvid_Num'] == ser_num && r.data['PntDeathSvid_id'] != svid_id && r.data['PntDeathSvid_IsBad'] == 1)
					isbad_err = true;
			});
			if (isbad_err) { sw.swMsg.alert(lang['oshibka'], lang['uje_suschestvuet_neisporchennoe_svidetelstvo_s_dannyimi_nomerom_i_seriey']); return false; }
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (response.responseText != '') {
								var response_obj = Ext.util.JSON.decode(response.responseText);								
								if (success) {								
									Ext.getCmp('MedSvidPntDeathStreamWindowSearchGrid').ViewGridStore.reload();
								} else {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При смене статуса свидетельства произошла ошибка');
								}
							}
						},
						params: {
							svid_id: svid_id,
							svid_type: 'pntdeath',
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

		var params = form.UserPanel.getForm().getValues();
		params.start = 0;
		params.limit = 100;
		params.Lpu_id = form.UserPanel.getForm().findField('Lpu_id').getValue();

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
			globalFilters: params
		});
	},
	clearFilters: function () {
		var base_form = this.UserPanel.getForm();
		base_form.reset();
		this.clearLpu();
	},
	checkGrants: function() {
		var msgrant = isMedSvidAccess();
		this.SearchGrid.ViewActions.action_add.setDisabled(!msgrant);
	}
});