/**
* swPersonUnionHistoryWindow - История объединения людей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      27.07.2010
*/

sw.Promed.swPersonUnionHistoryBottomPanel = Ext.extend(Ext.Panel,
{
	layout: "form",
	bodyBorder: false,
	bodyStyle: 'padding: 1px 1px',
	border: true,
	frame: false,
	height:28,
	html: '<table><tr><td valign="middle"><img src="/img/info.png" alt="" /> </td><td valign="middle">&nbsp;&nbsp;&nbsp;&nbsp;В этом окне вы можете посмотреть результаты объединения двойников отправленных на модерацию.</td></tr></table>'
});

sw.Promed.swPersonUnionHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 500,
	id: 'PersonUnionHistoryWindow',
	initComponent: function() {
		var win = this;

		win.FilterPanel = new Ext.FormPanel({
			region: 'north',
			autoHeight: true,
			frame: true,
			keys: [{
				fn: function(inp, e) {
					var f = Ext.get(e.getTarget());
					this.doSearch(f.focus.createDelegate(f));
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			items: [
				{
					xtype: 'fieldset',
					title: lang['filtr'],
					autoHeight: true,
					labelAlign: 'right',
					collapsible: true,
					listeners: {
						collapse: function(p) {
							p.doLayout();
							this.doLayout();
						}.createDelegate(this),
						expand: function(p) {
							p.doLayout();
							this.doLayout();
						}.createDelegate(this)
					},
					layout: 'form',
					items: [
						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									defaults: {
										anchor: '100%'
									},
									width: 400,
									labelWidth: 180,
									items: [{
										fieldLabel: 'Дата отправки на модерацию',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
										name: 'PersonDoubles_insDT_Range',
										xtype: 'daterangefield'
									}, {
										fieldLabel: 'МО запроса модерации',
										hiddenName: 'zLpu_id',
										ctxSerach: true,
										xtype: 'swlpucombo'
									}, {
										fieldLabel: 'Дата модерации',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
										name: 'PersonDoubles_updDT_Range',
										xtype: 'daterangefield'
									}, {
										fieldLabel: 'МО прикрепления',
										hiddenName: 'pLpu_id',
										ctxSerach: true,
										xtype: 'swlpucombo'
									}]
								}, {
									layout: 'form',
									defaults: {
										anchor: '100%'
									},
									width: 300,
									labelWidth: 120,
									items: [{
										fieldLabel: 'Фамилия',
										name: 'Person_SurName',
										xtype: (getRegionNick() == 'kz')?'textfield':'swtranslatedtextfieldwithapostrophe'
									}, {
										fieldLabel: 'Имя',
										name: 'Person_FirName',
										xtype: (getRegionNick() == 'kz')?'textfield':'swtranslatedtextfieldwithapostrophe'
									}, {
										fieldLabel: 'Отчество',
										name: 'Person_SecName',
										xtype: (getRegionNick() == 'kz')?'textfield':'swtranslatedtextfieldwithapostrophe'
									}, {
										fieldLabel: 'Дата рождения',
										width: 100,
										anchor: '',
										name: 'Person_BirthDay',
										xtype: 'swdatefield'
									}]
								}, {
									layout: 'form',
									defaults: {
										anchor: '100%'
									},
									width: 280,
									labelWidth: 100,
									items: [{
										fieldLabel: 'Результат',
										triggerAction: 'all',
										forceSelection: true,
										hiddenName: 'PersonDoublesStatus',
										tpl: '<tpl for="."><div class="x-combo-list-item"><font color="red">{value}</font>&nbsp;{text}</div></tpl>',
										store: [
											[1, 'Объединён'],
											[2, 'Запланирован к объединению'],
											[3, 'Отказано']
										],
										xtype: 'swbaselocalcombo'
									}]
								}
							]
						}, {
							layout: 'column',
							bodyStyle: 'padding: 5px;',
							items: [
								{
									layout: 'form',
									items: [
										{
											xtype: 'button',
											iconCls: 'search16',
											text: 'Найти',
											handler: function() {
												win.doSearch();
											}
										}
									]
								}, {
									layout: 'form',
									style: 'margin-left: 10px;',
									items: [
										{
											xtype: 'button',
											iconCls: 'reset16',
											text: lang['sbros'],
											handler: function() {
												win.doReset();
												win.doSearch();
											}
										}
									]
								}
							]
						}
					]
				}
			]
		});

		win.PersonDoublesGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			uniqueId: true,
			tbar: false,
			border: false,
			autoLoadData: false,
			stringfields: [
				{ name: 'Person_id', type: 'string', header: 'Person_id', key: true },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'],  width: 150},
				{ name: 'Person_Firname', type: 'string', header: lang['imya'],  width: 150},
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'],  width: 150},
				{ name: 'Person_Birthdate', type: 'date', header: lang['data_rojdeniya'], width: 150 },
				{ name: 'Lpu_pNick', type: 'string', header: 'МО прикрепления', width: 150 },
				{ name: 'PersonDoubles_Status', type: 'string', header: lang['rezultat'],  id: 'autoexpand'},
				{ name: 'Lpu_Nick', type: 'string', header: 'МО запроса модерации', width: 150 },
				{ name: 'PersonDoubles_insDT', type: 'datetime', dateFormat: 'd.m.Y H:i', header: lang['otpravleno_na_moderatsiyu'], width: 150 },
				{ name: 'PersonDoubles_updDT', type: 'datetime', dateFormat: 'd.m.Y H:i', header: lang['data_moderatsii'], width: 150 }
			],
			region: 'center',
			stripeRows: true,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true }
			],
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			dataUrl: C_UNIONHISTORY
		});

		Ext.apply(this, {
			buttons: [
				HelpButton(this),
				{
					handler: function() {
						this.ownerCt.returnFunc();
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [
				win.FilterPanel,
				win.PersonDoublesGrid,
                new sw.Promed.swPersonUnionHistoryBottomPanel({
                    region: 'south'
			    })
            ]
		});
		sw.Promed.swPersonUnionHistoryWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonUnionHistoryWindow');
			current_window.hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	doSearch: function(cb) {
		var bf = this.FilterPanel.getForm(),
			store = this.PersonDoublesGrid.getGrid().getStore();

		store.baseParams = bf.getValues();
		store.load();
	},
	doReset: function() {
		var bf = this.FilterPanel.getForm();
		bf.reset();
		this.PersonDoublesGrid.removeAll({clearAll: true});

		if (!isSuperAdmin()) {
			bf.findField('zLpu_id').setValue(getGlobalOptions().lpu_id);
			bf.findField('zLpu_id').disable();
		}

		var curdate = Date.parseDate(getGlobalOptions().date, 'd.m.Y'); // текущая дата
		var firstdate = curdate.getFirstDateOfMonth().clearTime(); // первый день месяца

		bf.findField('PersonDoubles_insDT_Range').setValue(Ext.util.Format.date(firstdate, 'd.m.Y')+' - '+Ext.util.Format.date(curdate, 'd.m.Y'));
	},
	maximizable: true,
	minHeight: 500,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	returnFunc: Ext.emptyFn,
	show: function() {
		sw.Promed.swPersonUnionHistoryWindow.superclass.show.apply(this, arguments);

		this.onHide = Ext.emptyFn;

		this.restore();
		this.center();
		this.maximize()

		this.doReset();
		this.doSearch();

		this.FilterPanel.doLayout();
		this.doLayout();
	},
	title: lang['istoriya_moderatsii_dvoynikov'],
	width: 800
});
