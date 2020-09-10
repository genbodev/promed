/**
* swPhpLogViewWindow - окно просмотра логов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Admin
* @access			public
* @copyright		Copyright (c) 2012 Swan Ltd.
* @origamiauthor	Dmitry Storozhev
* @version			03.07.2012
*/

sw.Promed.swPhpLogViewWindow = Ext.extend(sw.Promed.BaseForm, {
	modal: true,
	resizable: false,
	title: lang['jurnal_sobyitiy_sistemyi'],
	maximized: true,
	buttonAlign: 'right',
	//plain: true,
	id: 'swPhpLogViewWindow',
	
	listeners: {
		'beforehide': function () {
			this.cancelQueries();
		},
		resize: function () {
			if (this.layout.layout) this.doLayout();
		}
	},
	
	show: function() {
		sw.Promed.swPhpLogViewWindow.superclass.show.apply(this, arguments);
		
		
		this.doReset();
		this.doSearch();
	},
	
	doSearch: function(cb) {
		// отменяем выполнение запросов в цикле
		this.cancelQueries();
		
		var bf = this.FilterPanel.getForm(),
			store = this.GridPanel.ViewGridPanel.getStore();

		var value = bf.findField('limit').getValue();
		if (Ext.isEmpty(value)) {
			bf.findField('limit').setValue(50);
			value = 50;
		}
		
		this.GridPanel.ViewGridPanel.getStore().baseParams['limit'] = value;
		this.GridPanel.ViewGridPanel.getBottomToolbar().pageSize = value;
		this.GridPanel.pageSize = value;
		
		with(store) {
			baseParams = bf.getValues();
			baseParams.limit = this.GridPanel.pageSize;
			removeAll();
			load({ callback: cb || Ext.emptyFn });
		}
	},
	
	doReset: function() {
		this.FilterPanel.getForm().reset();
		this.GridPanel.ViewGridPanel.getStore().removeAll();
		this.GridPanel.ViewGridPanel.getStore().baseParams['limit'] = 50;
		this.GridPanel.ViewGridPanel.getBottomToolbar().pageSize = 50;
		this.GridPanel.pageSize = 50;
	},
	executeQueries: function() {
		if (this.currentQuery == this.maxQuery) {
			this.currentQuery = 0;
		}
		
		var r = this.GridPanel.ViewGridPanel.getStore().getAt(this.currentQuery);
		
		if (r && r.get('QueryString')) {
			var url = '/?' + r.get('QueryString');
			var params = {};
			var get = r.get('POST')
			this.setQueriesStatus(lang['vyipolnyaetsya_zapros']+url+'&'+get);
			if(get != '') {
				var tmp = new Array();			
				tmp = get.split('&');	
				for(var i = 0; i < tmp.length; i++) {
					var item = tmp[i].split('=');
					params[item[0]] = item[1];
				}
			}

			Ext.Ajax.request({
				failure: function(response, options) {
					// обработка ошибки
				},
				params: params,
				success: function(response, options) {
					// обработка успешного завершения
				},
				showErrors: false,
				url: url
			});
		}
		
		this.currentQuery++;
	},
	setQueriesStatus: function(text) {
		this.QueriesStatus.getEl().update('<b>'+text+'</b>');
	},
	startQueries: function() {
		var form = this.FilterPanel.getForm();
		var queriesInterval = form.findField('queriesInterval').getValue();
		if (queriesInterval && queriesInterval < 10) {
			Ext.Msg.alert(lang['soobschenie'], lang['ukazan_slishkom_malenkiy_interval_zaprosov_<_10_ms']);
			return false;
		}
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId === 'yes' ) {
					this.setQueriesStatus(lang['start_vyipolneniya_zaprosov']);
					this.currentQuery = 0;
					this.maxQuery = this.GridPanel.ViewGridPanel.getStore().getCount();
					this.startQueriesButton.hide();
					this.cancelQueriesButton.show();
					this.queriesIntervalObj = setInterval(this.executeQueries.createDelegate(this), queriesInterval);
				}
			}.createDelegate(this),
			msg: lang['obratite_vnimanie_pri_vyipolnenii_v_bd_budut_vnesenyi_neobratimyie_izmeneniya_ni_v_koem_sluchae_nelzya_zapuskat_dannyiy_funktsional_na_rabochem_servere_vyi_deystvitelno_hotite_zapustit_vyipolnenie_vyibrannyih_zaprosov'],
			title: lang['podtverjdenie']
		});
	},
	cancelQueries: function() {
		this.cancelQueriesButton.hide();
		this.startQueriesButton.show();
		if (getRegionNick() === 'saratov') {
			this.startQueriesButton.hide();
		}
		this.setQueriesStatus('');
		if (this.queriesIntervalObj) {
			clearInterval(this.queriesIntervalObj);
		}
	},
	
	initComponent: function() {
		var form = this;
		this.cancelQueriesButton = new Ext.Button({
			xtype: 'button',
			iconCls: 'support16',
			text: lang['otmenit_vyipolnenie_zaprosov'],
			handler: this.cancelQueries.createDelegate(this),
			hidden: true,
			tabIndex: TABINDEX_PHPLOG + 16
		});

		this.startQueriesButton = new Ext.Button({
			xtype: 'button',
			iconCls: 'support16',
			text: lang['vyipolnyat_zaprosyi_v_tsikle'],
			name: 'startQueriesButton',
			hidden: (getRegionNick() === 'saratov'),
			handler: this.startQueries.createDelegate(this),
			tabIndex: TABINDEX_PHPLOG + 15
		});

		this.QueriesStatus = new Ext.form.Label({
			html: "<b></b>"
		});

		this.FilterPanel = new Ext.FormPanel({
			region: 'north',
			autoHeight: true,
			frame: true,
			keys: [{
				fn: function (inp, e) {
					var f = Ext.get(e.getTarget());
					form.doSearch(f.focus.createDelegate(f));
				},
				key: [Ext.EventObject.ENTER],
				scope: this,
				stopEvent: true
			}],
			items: [{
				xtype: 'fieldset',
				title: lang['filtr'],
				autoHeight: true,
				labelAlign: 'right',
				collapsible: true,
				listeners: {
					collapse: function (p) {
						p.doLayout();
						this.doLayout();
					}.createDelegate(this),
					expand: function (p) {
						p.doLayout();
						this.doLayout();
					}.createDelegate(this)
				},
				layout: 'form',
				items: [
					{
						layout: 'column',
						items: [{
							layout: 'form',
							defaults: {
								anchor: '100%'
							},
							width: 250,
							labelWidth: 80,
							items: [{
								fieldLabel: lang['period'],
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								name: 'PHPLog_insDT',
								xtype: 'daterangefield',
								tabIndex: TABINDEX_PHPLOG + 0
							}, {
								fieldLabel: lang['kontroller'],
								name: 'Controller',
								xtype: 'textfield',
								tabIndex: TABINDEX_PHPLOG + 1
							}, {
								fieldLabel: lang['metod'],
								name: 'Method',
								xtype: 'textfield',
								tabIndex: TABINDEX_PHPLOG + 2
							}]
						}, {
							layout: 'form',
							width: 400,
							defaults: {
								anchor: '100%'
							},
							labelWidth: 200,
							items: [{
								layout: 'column',
								items: [
									{
										layout: 'form',
										defaults: {
											anchor: '100%'
										},
										labelWidth: 200,
										width: 285,
										items: [{
											fieldLabel: lang['vremya_vyipolneniya_metoda_ot'],
											name: 'ET_from',
											maskRe: /[\d]/,
											xtype: 'textfield',
											tabIndex: TABINDEX_PHPLOG + 3
										}, {
											fieldLabel: lang['vremya_vyipolneniya_zaprosa_ot'],
											name: 'ET_Query_from',
											maskRe: /[\d]/,
											xtype: 'textfield',
											tabIndex: TABINDEX_PHPLOG + 5
										}]
									}, {
										layout: 'form',
										defaults: {
											anchor: '100%'
										},
										labelWidth: 30,
										width: 115,
										items: [{
											fieldLabel: lang['do'],
											name: 'ET_to',
											maskRe: /[\d]/,
											xtype: 'textfield',
											tabIndex: TABINDEX_PHPLOG + 4
										}, {
											fieldLabel: lang['do'],
											name: 'ET_Query_to',
											maskRe: /[\d]/,
											xtype: 'textfield',
											tabIndex: TABINDEX_PHPLOG + 6
										}]
									}
								]
							}, {
								fieldLabel: lang['polzovatel'],
								name: 'PMUser_Login',
								xtype: 'textfield',
								tabIndex: TABINDEX_PHPLOG + 7
							}]
						}, {
							layout: 'form',
							defaults: {
								anchor: '100%'
							},
							labelWidth: 120,
							width: 290,
							items: [{
								fieldLabel: lang['ip_polzovatelya'],
								name: 'IP',
								maskRe: /[\d]/,
								xtype: 'textfield',
								tabIndex: TABINDEX_PHPLOG + 8
							}, {
								fieldLabel: lang['ip_servera'],
								maskRe: /[\d]/,
								name: 'Server_IP',
								xtype: 'textfield',
								tabIndex: TABINDEX_PHPLOG + 9
							}, {
								fieldLabel: lang['dannyie_zaprosa'],
								name: 'POST',
								xtype: 'textfield',
								tabIndex: TABINDEX_PHPLOG + 10
							}]
						}, {
							layout: 'form',
							defaults: {
								anchor: '100%'
							},
							labelWidth: 150,
							width: 290,
							items: [
								{
									fieldLabel: lang['limit_zapisey'],
									allowDecimals: false,
									allowNegative: false,
									name: 'limit',
									xtype: 'numberfield',
									value: 50,
									tabIndex: TABINDEX_PHPLOG + 11
								}, new sw.Promed.SwYesNoCombo({
									fieldLabel: langs('Ошибка'),
									hiddenName: 'AnswerError',
									lastQuery: ''
								}), {
									layout: 'form',
									labelWidth: 270,
									hidden: (getRegionNick() != 'msk'),
									items:[{
										fieldLabel: 'Доступ к персональным данным',
										name: 'isUsePersonData',
										xtype: 'checkbox'
									}]
								}
							]
						}]
					}, {
						layout: 'column',
						bodyStyle: 'padding: 5px;',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'button',
								iconCls: 'search16',
								text: lang['poisk'],
								handler: this.doSearch.createDelegate(this, []),
								tabIndex: TABINDEX_PHPLOG + 12
							}]
						}, {
							layout: 'form',
							style: 'margin-left: 10px;',
							items: [{
								xtype: 'button',
								iconCls: 'reset16',
								text: lang['sbros'],
								handler: this.doReset.createDelegate(this),
								tabIndex: TABINDEX_PHPLOG + 13
							}]
						}, {
							layout: 'form',
							style: 'margin-left: 10px;',
							items: [
								this.startQueriesButton
							]
						}, {
							layout: 'form',
							style: 'margin-left: 10px;',
							items: [
								this.cancelQueriesButton
							]
						}, {
							layout: 'form',
							style: 'margin-left: 10px;',
							labelWidth: 200,
							items: [{
								fieldLabel: lang['interval_zaprosov_ms'],
								allowDecimals: false,
								allowNegative: false,
								name: 'queriesInterval',
								xtype: 'numberfield',
								value: 3000,
								tabIndex: TABINDEX_PHPLOG + 14
							}]
						}]
					},
					this.QueriesStatus
				]
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			autoLoadData: false,
			root: 'data',
			region: 'center',
			id: this.id + '_Grid',
			autoScroll: true,
			paging: true,
			pageSize: 50,
			auditOptions: {
				key: false
			},
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			stringfields: [
				{name: 'PHPLog_id', type: 'int', hidden: true, key: true},
				{
					name: 'PHPLog_insDT',
					renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'),
					header: langs('Дата и время выполнения'),
					width: 160
				},
				{name: 'ARMType_Name', type: 'string', header: langs('АРМ'), width: 300, hidden: !(getRegionNick() === 'vologda')},
				{name: 'Controller', type: 'string', header: langs('Контроллер'), width: 300},
				{name: 'Method', type: 'string', header: langs('Метод'), width: 300},
				{name: 'Method_Name_Ru', type: 'string', header: langs('Расшифровка метода'), width: 300, hidden: !(getRegionNick() === 'vologda')},
				{name: 'QueryString', type: 'string', header: langs('Строка запроса'), width: 250, hidden: true},
				{name: 'ET', type: 'string', header: langs('Время выполнения метода'), width: 160},
				{name: 'ET_Query', type: 'string', header: langs('Время выполнения запроса'), width: 170},
				{name: 'PMUser_Login', type: 'string', header: langs('Пользователь'), id: 'autoexpand'},
				{name: 'PMUser_id', type: 'int', header: langs('ID пользователя'), width: 100},
				{name: 'IP', type: 'string', header: langs('IP пользователя'), width: 100},
				{name: 'Server_IP', type: 'string', header: langs('IP сервера'), width: 100},
				{name: 'Methods_IsUsePersonData', type:'checkbox', header: langs('Доступ к персональным данным'), width: 60, hidden: (getRegionNick() != 'msk')},
				{name: 'POST', hidden: true, type: 'string', header: langs('Данные запроса')},
				{name: 'AnswerError', hidden: true, type: 'string', header: langs('Ошибка')}
			],
			dataUrl: '/?c=PhpLog&m=loadPhpLogGrid',
			totalProperty: 'totalCount'
		});
		this.GridPanel.ViewGridPanel.getStore().baseParams['limit'] = this.GridPanel.pageSize;
		this.GridPanel.ViewGridPanel.getSelectionModel().on('rowselect', function (sm, rowIdx, rec) {
			form.bottomTpl.overwrite(form.Bottompanel.body, {
				QueryString: rec.get('QueryString') != null && rec.get('QueryString') !== '' ? rec.get('QueryString') : 'нет',
				POST: rec.get('POST') != null && rec.get('POST') !== '' ? rec.get('POST') : 'нет',
				AnswerError: rec.get('AnswerError') != null && rec.get('AnswerError') !== '' ? rec.get('AnswerError') : 'нет'
			});
		}.createDelegate(this));

		this.bottomTpl = new Ext.Template(
			'Строка запроса: <b>{QueryString}</b><br>',
			'POST-данные: <b>{POST}</b><br>',
			'Ошибка: <b>{AnswerError}</b>'
		);

		this.Bottompanel = new Ext.Panel({
			height: 90,
			bodyStyle: 'padding:2px',
			region: 'south',
			border: true,
			frame: true,
			html: ''
		});

		Ext.apply(this, {
			layout: 'border',
			items: [this.FilterPanel, this.GridPanel, this.Bottompanel],
			buttons: [{
				text: '-'
			}, HelpButton(this, 20016), {
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: TABINDEX_PHPLOG + 17,
				handler: this.hide.createDelegate(this, []),
				onTabAction: function () {
					if (this.action !== 'view') {
						form.FilterPanel.getForm().findField('PHPLog_insDT').focus(true);
					}
				}.createDelegate(this)
			}]
		});
		sw.Promed.swPhpLogViewWindow.superclass.initComponent.apply(this, arguments);
	}
});