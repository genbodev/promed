/**
* swMedSvidBirthStreamWindow - окно поточного ввода медсвидетельств о рождении.
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
sw.Promed.swMedSvidBirthStreamWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	height: 500,
	width: 880,
	id: 'MedSvidBirthStreamWindow',
	title: lang['medsvidetelstva_o_rojdenii'],
	layout: 'border',
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	resizable: false,
	firstTabIndex: 17300,
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
					form.findById('msbswStreamInformationForm').findById('msbswpmUser_Name').setValue(response_obj.pmUser_Name);
					form.findById('msbswStreamInformationForm').findById('msbswStream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
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
		sw.Promed.swMedSvidBirthStreamWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();
		this.action = '';
		this.viewOnly = false;
		this.currentARMType = '';
		if(arguments[0] && arguments[0].action){
			this.action = arguments[0].action
		}
		if(arguments[0] && arguments[0].viewOnly){
			this.viewOnly = arguments[0].viewOnly;
		}
		if(arguments[0] && arguments[0].ARMType){
			this.currentARMType = arguments[0].ARMType;
		}
		this.center();
		this.maximize();
		this.SearchGrid.removeAll();
		// по умолчанию заполняем дату выдачи 
		this.UserPanel.getForm().findField('Give_Date').setValue(this.getPeriod7DaysLast());
		this.setBegDateTime(true);
		this.checkGrants();
		this.getLoadMask().hide();
		//{name:'action_newsvid', iconCls: 'edit16', hidden: false, text:lang['novoe_svidetelstvo_na_osnove_dannogo'], handler: function() { this.editMedSvid('edit'); }.createDelegate(this)},

		var win = this;

		if (!this.SearchGrid.getAction('action_newsvid')) {
			this.SearchGrid.addActions({
				name: 'action_newsvid',
				text: lang['novoe_svidetelstvo_na_osnove_dannogo'],
				handler: function() {
					this.editMedSvid('edit', 0);
				}.createDelegate(this),
				iconCls: 'open16',
				hidden: getRegionNick() == 'kz'
			}, 1);
		}

		if (!this.SearchGrid.getAction('sign_actions')) {
			this.SearchGrid.addActions({
				name: 'sign_actions',
				hidden: getRegionNick() == 'kz',
				text: langs('Действия'),
				menu: [{
					name: 'action_signBirthSvid',
					text: langs('Подписать'),
					tooltip: langs('Подписать'),
					handler: function() {
						var me = this;
						var rec = win.SearchGrid.getGrid().getSelectionModel().getSelected();
						if (rec && rec.get('BirthSvid_id')) {
							getWnd('swEMDSignWindow').show({
								EMDRegistry_ObjectName: 'BirthSvid',
								EMDRegistry_ObjectID: rec.get('BirthSvid_id'),
								callback: function(data) {
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
										win.SearchGrid.getGrid().getStore().reload();
									}
								}
							});
						}
					}
				}, {
					name: 'action_showBirthSvidVersionList',
					text: langs('Список версий документа'),
					tooltip: langs('Список версий документа'),
					handler: function() {
						var rec = win.SearchGrid.getGrid().getSelectionModel().getSelected();
						if (rec && rec.get('BirthSvid_id')) {
							getWnd('swEMDVersionViewWindow').show({
								EMDRegistry_ObjectName: 'BirthSvid',
								EMDRegistry_ObjectID: rec.get('BirthSvid_id')
							});
						}
					}
				}],
				iconCls : 'x-btn-text',
				icon: 'img/icons/digital-sign16.png'
			});
		}

		if(this.action == 'view'){
			var disabled = (this.currentARMType && this.currentARMType.inlist(['stac', 'mstat'])) ? false : true;
			this.SearchGrid.ViewActions.action_isbad.setDisabled(disabled);
			this.SearchGrid.ViewActions.action_nobad.setDisabled(disabled);
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
		if (getRegionNick() == 'kz') return;

		var win = this;
		var params = new Object();
		var action = 'add';
		
		getWnd('swPersonSearchWindow').show( {
			onClose: function()  {
				if (win.SearchGrid.getGrid().getSelectionModel().getSelected())  {
					win.SearchGrid.getGrid().getView().focusRow(win.SearchGrid.getGrid().getStore().indexOf(win.SearchGrid.getGrid().getSelectionModel().getSelected()));
				} else  {
					win.SearchGrid.focus();
				}
			}.createDelegate(this),
			onSelect: function(person_data) {				
				params.Person_id 	= person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id 	= person_data.Server_id;

				getWnd('swMedSvidBirthEditWindow').show({
					action: action,
					formParams: params
				});
				getWnd('swPersonSearchWindow').hide();
			},
			searchMode: 'all'
		});
	},
	editMedSvid: function(action, edit_poluchatel) {
		var params = new Object();
		var win = this;
		var grid = this.findById('MedSvidBirthStreamWindowSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('BirthSvid_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var isbad_id = record.get('BirthSvid_isBad');
		if (isbad_id == 1 && action == 'edit' && edit_poluchatel == 0) { sw.swMsg.alert(lang['oshibka'], lang['svidetelstvo_ne_otmecheno_kak_isporchennoe']); return false; }
		params = record.data;

		// При нажатии на кнопку выполняется поиск записи мед свидетельства выписанного на основании текущего
		win.getLoadMask('Проверка существования мед. свидетельства, выписанного на основании текущего').show();
		Ext.Ajax.request({
			url: '/?c=MedSvid&m=checkBirthSvidExist',
			params: {
				BirthSvid_pid: params.BirthSvid_id
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						getWnd('swMedSvidBirthEditWindow').show({
							action: action,
							formParams: params,
							edit_poluchatel: edit_poluchatel
						});
					}
				}
			}
		});
	},
	keys: 
	[{
		fn: function(inp, e) 
		{
			var win = Ext.getCmp('MedSvidBirthStreamWindow');
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
		var isBadStore = new Ext.data.SimpleStore({
			fields: [
				'IsBad',
				'displayText'
			],
			data: [[0, lang['vse']], [1, lang['tolko_deystvuyuschie']], [2, lang['tolko_isporchennyie']]]
		});
		
		this.UserPanel = new Ext.form.FormPanel(
		{
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'north',
			autoHeight: true,
			frame: true,
			id: 'msbswStreamInformationForm',
			items: 
			[{
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px;padding-bottom:15px;',
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
						border: false,
						items: [{
							disabled: true,
							fieldLabel: lang['polzovatel'],
							id: 'msbswpmUser_Name',
							width: 340,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						border: false,
						items: [{
							disabled: true,
							fieldLabel: lang['data_nachala_vvoda'],
							id: 'msbswStream_begDateTime',
							width: 135,
							xtype: 'textfield'
						}]
					}]
				}]
			}, {
				layout: 'column',
				border: false,	
				labelAlign: 'right',
				labelWidth: 140,
				items: [{
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					//columnWidth: .45,
					width: 340,
					labelAlign: 'right',
					labelWidth: 140,
					items: [
						new Ext.form.ComboBox({						
							fieldLabel: lang['tip_svidetelstv'],
							hiddenName: 'IsBad',
							width: 175,
							typeAhead: true,
							triggerAction: 'all',
							lazyRender:true,
							mode: 'local',
							store: isBadStore,
							value: 0,
							valueField: 'IsBad',
							displayField: 'displayText'
						}),{
							xtype: 'daterangefield',
							width: 175,
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
							fieldLabel: lang['period_datyi_vyidachi'],
							name: 'Give_Date'
						}, {
							xtype: 'swlpucombo',
							width: 175,
							fieldLabel: lang['mo'],
							name: 'Lpu_id',
							disabled: !(isSuperAdmin() || isUserGroup('ZagsUser') || getWnd('swWorkPlaceMZSpecWindow').isVisible() || isUserGroup('MIACSuperAdmin')),
							value: (!(isSuperAdmin() || isUserGroup('ZagsUser') || getWnd('swWorkPlaceMZSpecWindow').isVisible() || isUserGroup('MIACSuperAdmin'))) ? getGlobalOptions().lpu_id : ''
						}
					]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					//columnWidth: .36,
					width: 330,
					labelAlign: 'right',
					labelWidth: 120,
					items: 
					[{
						xtype: 'textfield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.translit(true, true) ],
						fieldLabel: lang['familiya_materi'],
						name: 'Person_Surname',
						listeners: {
							'keydown': function (f,e){							
								if (e.getKey() == e.ENTER) Ext.getCmp('MedSvidBirthStreamWindow').loadGridWithFilter();								
							}
						}
					}, {
						xtype: 'textfield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.translit(true, true) ],
						fieldLabel: lang['imya_materi'],
						name: 'Person_Firname',
						listeners: {
							'keydown': function (f,e){							
								if (e.getKey() == e.ENTER) Ext.getCmp('MedSvidBirthStreamWindow').loadGridWithFilter();								
							}
						}
					}, {
						xtype: 'textfield',
						maxLength: 30,
						width: 175,
						plugins: [ new Ext.ux.translit(true, true) ],
						fieldLabel: lang['otchestvo_materi'],
						name: 'Person_Secname',
						listeners: {
							'keydown': function (f,e){							
								if (e.getKey() == e.ENTER) Ext.getCmp('MedSvidBirthStreamWindow').loadGridWithFilter();								
							}
						}
					}]
				}, {
                    layout: 'form',
                    border: false,
                    bodyStyle:'background:#DFE8F6;padding-right:5px;',
                    //columnWidth: .36,
                    width: 350,
                    labelAlign: 'right',
                    labelWidth: 150,
                    items:
                        [{
                            xtype: 'textfield',
                            maxLength: 30,
                            width: 175,
                            plugins: [ new Ext.ux.translit(true, true) ],
                            fieldLabel: lang['familiya_rebenka'],
                            name: 'Child_Surname',
                            listeners: {
                                'keydown': function (f,e){
                                    if (e.getKey() == e.ENTER) Ext.getCmp('MedSvidBirthStreamWindow').loadGridWithFilter();
                                }
                            }
                        }, {
                            xtype: 'daterangefield',
                            width: 175,
                            plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
                            fieldLabel: lang['data_rojdeniya_rebenka'],
                            name: 'Child_BirthDate',
                            listeners: {
                                'keydown': function (f,e){
                                    if (e.getKey() == e.ENTER) Ext.getCmp('MedSvidBirthStreamWindow').loadGridWithFilter();
                                }
                            }
                        }, {
                            xtype: 'swpersonsexcombo',
                            width: 175,
                            codeField: 'Sex_id',
                            fieldLabel: lang['pol_rebenka'],
                            //name: 'Child_Sex'
                            hiddenName: 'Sex_id',
                            listeners: {
                                'keydown': function (f,e){
                                    if (e.getKey() == e.ENTER) Ext.getCmp('MedSvidBirthStreamWindow').loadGridWithFilter();
                                }
                            }
                        }]
                }, {
					layout: 'form',
					border: false,
					bodyStyle:'background:#DFE8F6;padding-right:5px;',
					//columnWidth: .30,
					labelAlign: 'right',
					labelWidth: 120,
					items: [{
						xtype: 'button',
						text: lang['ustanovit_filtr'],
						tabIndex: 4217,
						minWidth: 125,
						disabled: false,
						topLevel: true,
						allowBlank:true,
						handler: function ()
						{
							Ext.getCmp('MedSvidBirthStreamWindow').loadGridWithFilter();
						}
					}, {
						xtype: 'button',
						text: lang['snyat_filtr'],
						tabIndex: 4218,
						minWidth: 125,
						disabled: false,
						topLevel: true,
						allowBlank:true,
						handler: function ()
						{
							Ext.getCmp('MedSvidBirthStreamWindow').loadGridWithFilter(true);
						}
					}]
				}]
			}]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'SearchGrid',
			region: 'center',
			height: 203,
			title:lang['svidetelstva_o_rojdenii_spisok'],
			object: 'EvnPLWOW',
			editformclassname: 'EvnPLWOWEditWindow',
			dataUrl: '/?c=MedSvid&m=loadMedSvidBirthListGrid',
			paging: true,
			pageSize: 100,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,			
			stringfields:			
			[
				{ name: 'BirthSvid_id', type: 'int', header: 'ID', key: true },				
				{ name: 'BirthSvid_isBad', type: 'int', header: lang['isp'], hidden: true },
				{ name: 'BirthSvid_isInRpn', type: 'int', hidden: true },
				{ name: 'BirthSvid_SignCount', type: 'int', hidden: true },
				{ name: 'BirthSvid_MinSignCount', type: 'int', hidden: true },
				{ name: 'BirthSvid_RcpDate', type: 'date', format: 'd.m.Y', header: lang['data_vyidachi'] },
				{ name: 'BirthSvid_Ser', type: 'string', header: lang['seriya'], width:75 },
				{ name: 'BirthSvid_Num', type: 'string', header: lang['nomer'], width:75 },
				{ name: 'Person_FIO', type: 'string', header: lang['fio_materi'], width: 250 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya_materi'], width: 150 },
                { name: 'BirthSvid_BirthChildDate', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya_rebenka'], width: 150 },
                { name: 'BirthSvid_ChildFamil', type: 'string', header: lang['familiya_rebenka'], width: 250 },
                { name: 'Child_Sex', type: 'string', header: lang['pol_rebenka'], width:75 },
				{ name: 'Lpu_Nick', type: 'string', header: lang['mo'], width: 150},
				{ name: 'MedPersonal_FIO', type: 'string', header: lang['fio_vracha'], width: 150},
				{ name: 'BirthSvid_signDT', type: 'string', hidden: true },
				{ name: 'BirthSvid_IsSigned', renderer: function(v, p, r){
						if (!Ext.isEmpty(r.get('BirthSvid_IsSigned'))) {
							var s = '';

							if (r.get('BirthSvid_IsSigned') == 2) {
								if (r.get('BirthSvid_SignCount') == r.get('BirthSvid_MinSignCount')) {
									s += '<img src="/img/icons/emd/doc_signed.png">';
								} else {
									s += '<span class="sp_doc_signed" data-qtip="' + r.get('BirthSvid_SignCount') + ' из ' + r.get('BirthSvid_MinSignCount') + '">' + r.get('BirthSvid_SignCount') + '</span>';
								}
							} else {
								s += '<img src="/img/icons/emd/doc_notactual.png">';
							}

							s += r.get('BirthSvid_signDT');

							return s;
						} else if (r.get('BirthSvid_SignCount') > 0) {
							return '<span class="sp_doc_unsigned" data-qtip="' + r.get('BirthSvid_SignCount') + ' из ' + r.get('BirthSvid_MinSignCount') + '">' + r.get('BirthSvid_SignCount') + '</span>';
						} else {
							return '<img src="/img/icons/emd/doc_notsigned.png">';
						}
					}, header: langs('Статус документа'), width: 120 }
			],
			actions:
			[
				{name:'action_add', hidden: getRegionNick() == 'kz', func:  function() {this.addMedSvid();}.createDelegate(this)},
				{name:'action_edit', handler: function() { this.editMedSvid('view',1); }.createDelegate(this)},
				{name:'action_view', handler: function() { this.editMedSvid('view',0); }.createDelegate(this)},
				{name:'action_delete',  hidden: true, func:  function() {}.createDelegate(this)},
                { name: 'action_print',
                    menuConfig: {
                        printObjectSved: { text: lang['pechat_svidetelstva'], handler: function() {
                                var current_window = Ext.getCmp('MedSvidBirthStreamWindow');
                                var record = current_window.SearchGrid.getGrid().getSelectionModel().getSelected();
                                var isbad_id = record.get('BirthSvid_isBad');
                                if (isbad_id != 1) { sw.swMsg.alert(lang['oshibka'], lang['svidetelstvo_otmecheno_kak_isporchennoe']); return false; }
                                if (!record) {
                                    Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrano_ni_odno_svidetelstvo']);
                                    return false;
                                }
                                var svid_id = record.get('BirthSvid_id');
                                if ( !svid_id )
                                    return false;
                                if(getRegionNick() == 'kz'){
                                	printBirt({
										'Report_FileName': 'BirthSvid_Print.rptdesign',// #133782 BirthSvid.rptdesign - BirthSvid_Print.rptdesign
										'Report_Params': '&paramBirthSvid=' + svid_id,
										'Report_Format': 'pdf'
									});
									printBirt({
										'Report_FileName': 'BirthSvid_Print_check.rptdesign',// #133782 BirthSvid_check.rptdesign - BirthSvid_Print_check.rptdesign
										'Report_Params': '&paramBirthSvid=' + svid_id,
										'Report_Format': 'pdf'
									});
                                } else {
	                                /*var id_salt = Math.random();
	                                var win_id = 'print_svid' + Math.floor(id_salt * 10000);
	                                var win = window.open('/?c=MedSvid&m=printMedSvid&svid_id=' + svid_id + '&svid_type=birth', win_id);*/
	                                printBirt({
										'Report_FileName': 'BirthSvid_Print.rptdesign',
										'Report_Params': '&paramBirthSvid=' + svid_id,
										'Report_Format': 'pdf'
									});
	                            }
                            }
                        },
						printObjectSvidBlank: {// #143997 печать на бланке свидетельства о рождении
							text: lang['pechat_svidetelstva_na_blanke'],
							hidden: getRegionNick() == 'kz',
							handler: function() {
								var current_window = Ext.getCmp('MedSvidBirthStreamWindow');
								var record = current_window.SearchGrid.getGrid().getSelectionModel().getSelected();
								var isbad_id = record.get('BirthSvid_isBad');
								if (isbad_id != 1) { sw.swMsg.alert(lang['oshibka'], lang['svidetelstvo_otmecheno_kak_isporchennoe']); return false; }
								if (!record) {
									Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrano_ni_odno_svidetelstvo']);
									return false;
								}
								var svid_id = record.get('BirthSvid_id');
								if ( !svid_id )
									return false;
								printBirt({
									'Report_FileName': 'BirthSvid_Print_blank.rptdesign',
									'Report_Params': '&paramBirthSvid=' + svid_id,
									'Report_Format': 'pdf'
								});
							}
						}
                    }
                },
				{name:'action_isbad', text:lang['pometit_kak_isporchennyiy'], iconCls:'x-group-by-icon', handler: function() { this.setBadSvid(2); }.createDelegate(this)}, 
				{name:'action_nobad', text:lang['snyat_otmetku_isporchennyiy'], iconCls:'x-group-by-icon', handler: function() { this.setBadSvid(1); }.createDelegate(this)}//,
				//{name:'action_newsvid',  hidden: true}
			],
			afterSaveEditForm: function(RegistryQueue_id, records)
			{
				var form = Ext.getCmp('MedSvidBirthStreamWindow');
			},
			onLoadData: function()
			{
				var form = Ext.getCmp('MedSvidBirthStreamWindow');
				form.checkGrants();
			},
			onRowSelect: function(sm,index,record)
			{
				//log(this.id+'.onRowSelect');
				var form = Ext.getCmp('MedSvidBirthStreamWindow');
				if (record.get('BirthSIid_isInRpn') || (form.viewOnly == true)) {
					this.setActionDisabled('action_newsvid', true);
				} else {
					var msgrant = isMedSvidAccess();
					this.setActionDisabled('action_newsvid', !msgrant);
				}
			},
			onEnter: function() {
				if (!this.ViewActions.action_view.isDisabled()) {
					this.ViewActions.action_view.execute();
				}
			},
			onDblClick: function() {
				if (!this.ViewActions.action_view.isDisabled()) {
					this.ViewActions.action_view.execute();
				}
			}
		});
		
		this.SearchGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';
				if (row.get('BirthSvid_isBad') == 2)
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
				id: 'msbswRightPanel',
				items: [form.SearchGrid]
			}]
		});
		sw.Promed.swMedSvidBirthStreamWindow.superclass.initComponent.apply(this, arguments);
	},
	setBadSvid: function(bad_id) {
		var win = this;
		var grid = this.findById('MedSvidBirthStreamWindowSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('BirthSvid_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var svid_id = record.get('BirthSvid_id');
		var isbad_id = record.get('BirthSvid_isBad');
		
		if (isbad_id == bad_id) { sw.swMsg.alert(lang['oshibka'], lang['svidetelstvo_uje_otmecheno_kak'] + (isbad_id == 1 ? lang['deystvuyuschee'] : lang['isporchennoe'])); return false; }

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (response.responseText != '') {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj && response_obj.Alert_Msg && response_obj.Error_Msg == 'YesNo') {
									record.set('BirthSvid_isBad', 2);
									record.commit();
									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function(buttonId, text, obj) {
											if ( buttonId == 'yes' ) {
												win.editMedSvid('edit', 0);
											}
										},
										icon: Ext.MessageBox.QUESTION,
										msg: response_obj.Alert_Msg,
										title: 'Внимание'
									});
									return true;
								} else if (success) {
									Ext.getCmp('MedSvidBirthStreamWindowSearchGrid').ViewGridStore.reload();
								} else {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg && response_obj.Error_Msg != '' ? response_obj.Error_Msg : 'При смене статуса свидетельства произошла ошибка');
								}
							}
						},
						params: {
							svid_id: svid_id,
							svid_type: 'birth',
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
			this.SearchGrid.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					IsBad: '',
					Start_Date: '',
					End_Date: '',
					Give_Date: '',
					Lpu_id: (isSuperAdmin() || isUserGroup('ZagsUser') || getWnd('swWorkPlaceMZSpecWindow').isVisible() || isUserGroup('MIACSuperAdmin')) ? 0 : getGlobalOptions().lpu_id,
					Person_Surname: '',
					Person_Firname: '',
					Person_Secname: '',
                    Child_Surname: '',
                    Sex_id: '',
                    Child_BirthDate: ''
				}
			});
		} else {
			var base_form = this.UserPanel.getForm();
			var params = base_form.getValues();
			params.Lpu_id = base_form.findField('Lpu_id').getValue() || '0';
			params.limit = 100;
			params.start = 0;
			this.SearchGrid.removeAll();
			this.SearchGrid.loadData({
				globalFilters: params
			});
		}
	},
	clearFilters: function () {
		var base_form = this.UserPanel.getForm();
		var state_combo = base_form.findField('IsBad');
		if (state_combo) state_combo.setValue(0);

		base_form.findField('Give_Date').reset();
		base_form.findField('Person_Surname').reset();
		base_form.findField('Person_Firname').reset();
		base_form.findField('Person_Secname').reset();
        base_form.findField('Child_Surname').reset();
        base_form.findField('Child_BirthDate').reset();
        base_form.findField('Sex_id').reset();

		if (isSuperAdmin() || isUserGroup('ZagsUser') || getWnd('swWorkPlaceMZSpecWindow').isVisible() || isUserGroup('MIACSuperAdmin'))
			base_form.findField('Lpu_id').reset();
		else
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
	},
	checkGrants: function() {
		var msgrant = isMedSvidAccess();
		this.SearchGrid.ViewActions.action_add.setDisabled(!msgrant);
		this.SearchGrid.ViewActions.action_edit.setDisabled(!msgrant);
		//alert('ddfdfdfdf');
		//this.SearchGrid.ViewActions.action_newsvid.setDisabled(!msgrant);
		//this.SearchGrid.setActionDisabled('action_newsvid', !msgrant);
	}
});