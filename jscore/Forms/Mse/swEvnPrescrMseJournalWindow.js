/**
* Форма Журнал направлений на МСЭ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

sw.Promed.swEvnPrescrMseJournalWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Журнал направлений на МСЭ',
	modal: true,
	resizable: false,
	maximized: true,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swEvnPrescrMseJournalWindow',
	closeAction: 'hide',
	id: 'swEvnPrescrMseJournalWindow',
	objectSrc: '/jscore/Forms/Mse/swEvnPrescrMseJournalWindow.js',
	
	show: function()
	{
		sw.Promed.swEvnPrescrMseJournalWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0] || !arguments[0].userMedStaffFact) return false;
		
		this.userMedStaffFact = arguments[0].userMedStaffFact;

		var win = this;

		this.signAction = new Ext.Action({
			name: 'action_signEvnPrescrMse',
			text: langs('Подписать направление на МСЭ'),
			tooltip: langs('Подписать направление на МСЭ'),
			handler: function() {
				var me = this;
				var rec = win.Grid.getGrid().getSelectionModel().getSelected();
				if (rec && rec.get('EvnPrescrMse_id')) {
					getWnd('swEMDSignWindow').show({
						EMDRegistry_ObjectName: 'EvnPrescrMse',
						EMDRegistry_ObjectID: rec.get('EvnPrescrMse_id'),
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
								win.Grid.getGrid().getStore().reload();
							}

							win.onRecordSelect();
						}
					});
				}
			}
		});

		if (!this.Grid.getAction('sign_actions')) {
			this.Grid.addActions({
				name: 'sign_actions',
				hidden: getRegionNick() == 'kz',
				text: langs('Подписать'),
				menu: [win.signAction, {
					name: 'action_showEvnPrescrMseVersionList',
					text: langs('Версии документа «Направление на МСЭ»'),
					tooltip: langs('Версии документа «Направление на МСЭ»'),
					handler: function() {
						var rec = win.Grid.getGrid().getSelectionModel().getSelected();
						if (rec && rec.get('EvnPrescrMse_id')) {
							getWnd('swEMDVersionViewWindow').show({
								EMDRegistry_ObjectName: 'EvnPrescrMse',
								EMDRegistry_ObjectID: rec.get('EvnPrescrMse_id')
							});
						}
					}
				}],
				iconCls : 'x-btn-text',
				icon: 'img/icons/digital-sign16.png'
			});
		}
		
		var b_f = this.FilterPanel.getForm();
		b_f.reset();
		b_f.findField('EvnStatus_id').lastQuery = '';
		b_f.findField('EvnStatus_id').getStore().filterBy(function(rec) {
			return rec.get('EvnStatus_id').inlist([27,32]);
		});
		this.doSearch();
	},
	
	doSearch: function()
	{
		var grid = this.Grid.ViewGridPanel,
			form = this.FilterPanel.getForm();
		if( !form.isValid() ) {
			return false;
		}
		grid.getStore().baseParams = form.getValues();
		grid.getStore().baseParams.limit = 100;
		grid.getStore().baseParams.start = 0;
		grid.getStore().load();
	},
	
	doReset: function()
	{
		this.FilterPanel.getForm().reset();
		this.Grid.ViewGridPanel.getStore().baseParams = {};
		this.doSearch();
	},
	
	addEvnPrescrMse: function() {
		var win = this;
		// Добавление пациента
		if (getWnd('swPersonSearchWindow').isVisible()) {
			sw.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			getWnd('swPersonSearchWindow').hide();
			return false;
		}
		getWnd('swPersonSearchWindow').show({
			onSelect: function(pdata) {
				if (pdata.Person_IsDead != 'true') {
					getWnd('swPersonSearchWindow').hide();
					checkEvnPrescrMseExists({
						Person_id: pdata.Person_id,
						callback: function() {
							createEvnPrescrMse({
								personData: pdata, 
								userMedStaffFact: win.userMedStaffFact, 
								directionData: {},
								callback: function() {
									win.doSearch();
								}
							})
						}.createDelegate(this)
					});
				} else if (pdata.Person_IsDead == 'true') {
					sw.swMsg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
				}
			}.createDelegate(this),
			searchMode: 'all'
		});
	},
	
	openEvnPrescrMse: function( action )
	{
		var rec = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if( !rec ) {
			return false;
		}
		
		if (action == 'edit' && getGlobalOptions().medpersonal_id != rec.get('MedPersonal_sid')) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' ) {
						getWnd('swDirectionOnMseEditForm').show({
							EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
							Person_id: rec.get('Person_id'),
							Server_id: rec.get('Server_id'),
							action: action
						});
					}
				},
				msg: 'Выбранное направление на МСЭ создано другим врачом. Продолжить?',
				title: 'Вопрос'
			});

			return false;
		}
		
		getWnd('swDirectionOnMseEditForm').show({
			EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
			Person_id: rec.get('Person_id'),
			Server_id: rec.get('Server_id'),
			action: action
		});
	},
	
	deleteEvnPrescrMse: function()
	{
		var win = this;
		var rec = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if( !rec ) {
			return false;
		}
		
		var msg = getGlobalOptions().medpersonal_id == rec.get('MedPersonal_sid')
			? 'Направление на МСЭ будет удалено без возможности восстановления. Продолжить?'
			: 'Выбранное направление на МСЭ создано другим врачом. Направление на МСЭ будет удалено без возможности восстановления. Продолжить?';
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var form = this;
					Ext.Ajax.request({
						url: '/?c=ClinExWork&m=deleteEvnPrescrMse',
						params: {EvnPrescrMse_id: rec.get('EvnPrescrMse_id')},
						method: 'post',
						callback: function(opt, success, response){
							if (success && response.responseText != '') {
								win.doSearch();
							}
						}
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: msg,
			title: lang['vopros']
		});
	},

	sendToVk: function(){
		if(getRegionNick() == 'kz'){
			this.sendToVkContinuation();
			return false;
		}
		var win = this;
		var rec = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if( !rec ) return false;
		Ext.Ajax.request({
			url: '/?c=Mse&m=completenessTestMSE',
			method: 'POST',
			callback: function(options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(Ext.isArray(response_obj) && response_obj.length>0){
					if (response_obj[0]['code'] == 101) {
						sw.swMsg.show({
							buttons: {no: 'Отмена', yes: 'Продолжить'},
							fn: function (buttonId, text, obj) {
								if (buttonId == 'yes') {
									// ! сейчас эта проверка последняя, поэтому при подтверждении можно сразу переходить в выполнению
									win.sendToVkContinuation();
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: response_obj[0]['msg'],
							title: 'Внимание'
						});
					} else {
						sw.Msg.alert(langs('Сообщение'), langs(response_obj[0]['msg']));
					}
				}else{
					this.sendToVkContinuation();
				}
			}.bind(this),
			params: {
				EvnPrescrMse_id: rec.get('EvnPrescrMse_id'),
			}
		});
	},
	
	sendToVkContinuation: function()
	{
		var win = this;
		var rec = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if( !rec ) {
			return false;
		}
		
		Ext.Ajax.request({
			url: '/?c=Evn&m=updateEvnStatus',
			method: 'POST',
			callback: function(options, success, response) {
				win.doSearch();
			},
			params: {
				Evn_id: rec.get('EvnPrescrMse_id'), 
				EvnStatus_id: 27,
				EvnClass_id: 71
			}
		});
	},
	
	printEvnPrescrMse: function()
	{
		var rec = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if( !rec ) {
			return false;
		}

		if ( getRegionNick() == 'kz' ) {
			printBirt({
				'Report_FileName': 'DirectionMSE_f088u.rptdesign',
				'Report_Params': '&paramEvnPrescrMse_id=' + rec.get('EvnPrescrMse_id'),
				'Report_Format': 'pdf'
			});
		}
		else {
			var lm = this.getLoadMask(lang['vyipolnyaetsya_pechat_napravleniya']);
			lm.show();
			Ext.Ajax.request({
				url: '/?c=Mse&m=printEvnPrescrMse',
				params: {
					EvnPrescrMse_id: rec.get('EvnPrescrMse_id')
				},
				callback: function(o, s, r){
					lm.hide();
					if(s){
						openNewWindow(r.responseText);
					}
				}.createDelegate(this)
			});
		}
	},
	onRecordSelect: function() {
		var win = this;
		var rec = win.Grid.getGrid().getSelectionModel().getSelected();
		this.Grid.setActionDisabled('action_refresh', rec && rec.get('EvnStatus_id') != 32);
		this.Grid.setActionDisabled('action_delete', rec && !Ext.isEmpty(rec.get('EvnPrescrVK_id')));
		if (rec && rec.get('signAccess') == 'edit') {
			win.signAction.enable();
		} else {
			win.signAction.disable();
		}
	},
	initComponent: function()
	{
		var win = this;
		
		this.FilterPanel = new Ext.FormPanel({
			collapsible: true,
			titleCollapse: true,
			region: 'north',
			title: lang['jurnal_mse_poisk'],
			autoHeight: true,
			defaults: {
				labelAlign: 'right',
				border: false
			},
			floatable: false,
			animCollapse: false,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			bodyStyle: 'padding: 3px;',
			layout: 'column',
			items: [
				{
					layout: 'form',
					defaults: {
						anchor: '100%'
					},
					width: 400,
					labelWidth: 180,
					items: [
						{
							xtype: 'textfield',
							name: 'EvnDirection_Num',
							fieldLabel: 'Номер направления на ВК'
						}, {
							allowBlank: false,
							fieldLabel: 'Направление на ВК',
							hiddenName: 'EvnPrescrVK_Status',
							xtype: 'swbaselocalcombo',
							store: new Ext.data.SimpleStore({
								key: 'id',
								autoLoad: false,
								fields:[
									{name: 'Status_id', type: 'int'},
									{name: 'Status_Name', type: 'string'}
								],
								data: [
									[1, 'Создано'],
									[2, 'Не создано'],
									[0, 'Все']
								]
							}),
							editable: false,
							displayField:'Status_Name',
							valueField: 'Status_id',
							value: 0
						}, {
							xtype: 'textfieldpmw',
							name: 'Person_SurName',
							fieldLabel: 'Фамилия пациента'
						}, {
							xtype: 'textfieldpmw',
							name: 'Person_FirName',
							fieldLabel: 'Имя пациента'
						}, {
							xtype: 'textfieldpmw',
							name: 'Person_SecName',
							fieldLabel: 'Отчество пациента'
						}, {
							xtype: 'swdatefield',
							anchor: '',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Person_BirthDay',
							fieldLabel: 'Дата рождения'
						}
					]
				}, {
					layout: 'form',
					defaults: {
						anchor: '100%'
					},
					width: 450,
					labelWidth: 230,
					items: [{
							xtype: 'swdiagcombo',
							fieldLabel: lang['diagnoz'],
							width: 200
						}, {
							xtype: 'daterangefield',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
							name: 'EvnPrescrMse_issueDT',
							width: 200,
							fieldLabel: 'Даты направлений на МСЭ'
						}, {
							xtype: 'swevnstatuscombo',
							hiddenName: 'EvnStatus_id',
							fieldLabel: 'Статус направления',
							width: 200
						}
					]
				}
			]
		});
		
		
		this.Grid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
			pageSize: 100,
			border: false,
			uniqueId: true,
			actions: [
				{ name: 'action_add', handler: this.addEvnPrescrMse.createDelegate(this) },
				{ name: 'action_edit', handler: this.openEvnPrescrMse.createDelegate(this, ['edit']) },
				{ name: 'action_view', handler: this.openEvnPrescrMse.createDelegate(this, ['view']) },
				{ name: 'action_delete', handler: this.deleteEvnPrescrMse.createDelegate(this) },
				{ name: 'action_refresh', text: 'Отправить на ВК', tooltip: 'Отправить на ВК', icon: '/img/icons/arrow-next16.png', handler: this.sendToVk.createDelegate(this)},
				{ name: 'action_print', handler: this.printEvnPrescrMse.createDelegate(this) }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'EvnPrescrMse_id', type: 'int', hidden: true, key: true },
				{ name: 'EvnPrescrVK_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'MedPersonal_sid', type: 'int', hidden: true },
				{ name: 'EvnStatus_id', type: 'int', hidden: true },
				{ name: 'signAccess', type: 'string', hidden: true },
				{ name: 'EvnPrescrMse_signDT', type: 'string', hidden: true },
				{ name: 'EvnPrescrVK_setDate', type: 'string', header: 'Запись на ВК', width: 120 },
				{ name: 'EvnDirection_Num', type: 'int', header: 'Направление на ВК', width: 60 },
				{ name: 'EvnPrescrMse_issueDT', type: 'string', header: 'Дата направления на МСЭ', width: 100 },
				{ name: 'EvnStatus_Name', type: 'string', header: 'Статус направления на МСЭ', width: 100 },
				{ name: 'EvnPrescrMse_IsFirstTime', type: 'string', header: 'Направляется', width: 120 },
				{ name: 'MseDirectionAimType_Name', type: 'string', header: 'Цель направления на МСЭ', width: 120 },
				{ name: 'Diag_Name', type: 'string', header: 'Диагноз основной', id: 'autoexpand' },
				{ name: 'Person_Fio', type: 'string', header: langs('ФИО Пациента'), width: 250 },
				{ name: 'Person_BirthDay', type: 'string', header: langs('Дата рождения'), width: 100 },
				{ name: 'EvnPrescrMse_IsSigned', renderer: function(v, p, r){
					if (!Ext.isEmpty(r.get('EvnPrescrMse_id'))) {
						if (!Ext.isEmpty(r.get('EvnPrescrMse_IsSigned'))) {
							var s = '';

							if (r.get('EvnPrescrMse_IsSigned') == 2) {
								s += '<img src="/img/icons/emd/doc_signed.png">';
							} else {
								s += '<img src="/img/icons/emd/doc_notactual.png">';
							}

							s += r.get('EvnPrescrMse_signDT');

							return s;
						} else {
							return '<img src="/img/icons/emd/doc_notsigned.png">';
						}
					} else {
						return '';
					}
				}, header: langs('Документ подписан'), width: 120 }
			],
			paging: true,
			dataUrl: '/?c=Mse&m=searchEvnPrescrMse',
			root: 'data',
			totalProperty: 'totalCount',
			onRowSelect: function(sm,index,record){
				win.onRecordSelect();
			}
		});
		
		Ext.apply(this,	{
			layout: 'border',
			buttons: [
				{
					handler: this.doSearch.createDelegate(this),
					iconCls: 'search16',
					text: BTN_FRMSEARCH
				},
				{
					handler: this.doReset.createDelegate(this),
					iconCls: 'resetsearch16',
					text: BTN_FRMRESET
				},
				'-',
				HelpButton(this),
				{
					text: lang['zakryit'],
					tabIndex: -1,
					tooltip: lang['zakryit'],
					iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			],
			keys: 
			[{
				fn: function(inp, e) {
					if ( e.getKey() == Ext.EventObject.ENTER ) {
						this.doSearch();
					}
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			items: [this.FilterPanel, this.Grid]
		});
		sw.Promed.swEvnPrescrMseJournalWindow.superclass.initComponent.apply(this, arguments);
	}
});