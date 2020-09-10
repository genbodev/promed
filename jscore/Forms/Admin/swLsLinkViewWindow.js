/**
 * swLsLinkViewWindow - окно списка взаимодействий
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Dmitry Vlasenko
 * @version			12.2019
 */

/*NO PARSE JSON*/

sw.Promed.swLsLinkViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swLsLinkViewWindow',
	width: 800,
	height: 600,
	maximized: true,
	layout: 'border',
	title: langs('Взаимодействие ЛС'),
	doSearch: function(reset) {
		var base_form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();

		if (reset) {
			base_form.reset();
		}
		var params = base_form.getValues();
		params.start = 0;
		params.limit = 100;

		grid.getStore().load({params: params});
	},
	openLsLinkEditWindow: function(action){
		if (!action.inlist(['add','edit','view','copy'])) {return false;}

		var grid = this.GridPanel.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record.get('LS_LINK_ID')) {return false;}
			params.formParams.LS_LINK_ID = record.get('LS_LINK_ID');
		}

		params.callback = function(){
			this.GridPanel.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swLsLinkEditWindow').show(params);
	},
	deleteLsLink: function(options) {
		if (!options) {
			options = {};
		}

		var win = this;
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record.get('LS_LINK_ID')) {return false;}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			msg: langs('Удалить взаимодействие ЛС?'),
			title: langs('Удаление записи'),
			fn: function (buttonId) {

				if (buttonId == 'yes') {
					win.doDeleteLsLink({
						LS_LINK_ID: record.get('LS_LINK_ID')
					});
				}
			}
		});
	},
	doDeleteLsLink: function(params) {
		var win = this;
		win.getLoadMask('Удаление взаимодействия').show();
		Ext.Ajax.request({
			url: '/?c=LsLink&m=deleteLsLink',
			params: params,
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success && response && response.responseText) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.Error_Msg && result.Error_Msg == 'YesNo') {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							msg: langs(result.Alert_Msg),
							title: langs('Удаление записи'),
							fn: function(buttonId) {
								if (buttonId == 'yes') {
									params.ignorePrepLs = 1;
									win.doDeleteLsLink(params);
								}
							}
						});
					} else {
						win.GridPanel.getAction('action_refresh').execute();
					}
				}
			}
		});
	},
	show: function() {
		sw.Promed.swLsLinkViewWindow.superclass.show.apply(this, arguments);

		this.DescrPanel.body.dom.innerHTML = '';

		this.GridPanel.addActions({
			name: 'action_copy',
			text: 'Копировать',
			tooltip: 'Копировать',
			iconCls: 'copy16',
			handler: function () {
				this.openLsLinkEditWindow('copy');
			}.createDelegate(this)
		}, 1);

		this.doSearch(true);
	},
	initComponent: function() {
		var win = this;
		this.FilterPanel = getBaseFiltersFrame({
			defaults: {
				frame: true,
				collapsed: false
			},
			ownerWindow: this,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 90,
					items: [{
						xtype: 'textfield',
						name: 'LS_GROUP1',
						fieldLabel: langs('Группа 1 ЛС')
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 90,
					items: [{
						xtype: 'textfield',
						name: 'LS_GROUP2',
						fieldLabel: langs('Группа 2 ЛС')
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 120,
					items: [{
						xtype: 'textfield',
						name: 'PREP_NAME',
						fieldLabel: langs('Наименование ЛП')
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 50,
					items: [{
						xtype: 'textfield',
						name: 'RlsRegnum',
						fieldLabel: langs('№ РУ')
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						style: 'margin-left: 30px',
						xtype: 'button',
						text: langs('Найти'),
						handler: function() {
							this.doSearch();
						}.createDelegate(this),
						minWidth: 100
					}]
				},  {
					layout: 'form',
					border: false,
					items: [{
						style: 'margin-left: 20px',
						xtype: 'button',
						text: langs('Сброс'),
						handler: function() {
							this.doSearch(true);
						}.createDelegate(this),
						minWidth: 100
					}]
				}]
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			uniqueId: true,
			region: 'center',
			dataUrl: '/?c=LsLink&m=loadLsLinkGrid',
			paging: true,
			autoLoadData: false,
			onRowSelect: function (sm, rowIdx, record) {
				if (!Ext.isEmpty(record.get('LS_LINK_ID'))) {
					win.DescrTpl.overwrite(win.DescrPanel.body, {
						DESCRIPTION: record.get('DESCRIPTION'),
						RECOMMENDATION: record.get('RECOMMENDATION'),
						BREAKTIME: record.get('BREAKTIME'),
						PREPLIST: '...'
					});

					Ext.Ajax.request({
						url: '/?c=LsLink&m=getLsLinkInfo',
						params: {
							LS_LINK_ID: record.get('LS_LINK_ID')
						},
						callback: function(options, success, response) {
							if (success && response && response.responseText) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (result.PREPLIST) {
									win.DescrTpl.overwrite(win.DescrPanel.body, {
										DESCRIPTION: record.get('DESCRIPTION'),
										RECOMMENDATION: record.get('RECOMMENDATION'),
										BREAKTIME: record.get('BREAKTIME'),
										PREPLIST: result.PREPLIST
									});
								}
							}
						}
					});
				} else {
					win.DescrPanel.body.dom.innerHTML = '';
				}
			},
			root: 'data',
			stringfields: [
				{name: 'LS_LINK_ID', type: 'int', header: 'ID', key: true},
				{name: 'LS_GROUP1', type: 'string', header: langs('Группа 1 ЛС'), width: 150},
				{name: 'LS_GROUP2', type: 'string', header: langs('Группа 2 ЛС'), width: 150},
				{name: 'LS_INTERACTION_CLASS_NAME', type: 'string', header: langs('Класс взаимодействия'), width: 150},
				{name: 'LS_FT_TYPE_NAME', type: 'string', header: langs('Фармакологический тип'), width: 150},
				{name: 'LS_INFLUENCE_TYPE_NAME', type: 'string', header: langs('Тип влияния'), id: 'autoexpand'},
				{name: 'LS_EFFECT_NAME', type: 'string', header: langs('Терапевтический эффект'), width: 150},
				{name: 'DESCRIPTION', type: 'string', hidden: true},
				{name: 'RECOMMENDATION', type: 'string', hidden: true},
				{name: 'BREAKTIME', type: 'string', hidden: true}
			],
			actions: [
				{name: 'action_add', handler: function(){this.openLsLinkEditWindow('add');}.createDelegate(this)},
				{name: 'action_edit', handler: function(){this.openLsLinkEditWindow('edit');}.createDelegate(this)},
				{name: 'action_view', handler: function(){this.openLsLinkEditWindow('view');}.createDelegate(this)},
				{name: 'action_delete', handler: function(){this.deleteLsLink();}.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			]
		});

		this.DescrTpl = new Ext.XTemplate([
			'<div style="padding:2px;font-size: 12px;"><b>Описание  проявления  взаимодействия:</b> {DESCRIPTION}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Рекомендации:</b> {RECOMMENDATION}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Временной перерыв между приемами:</b> {BREAKTIME}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Список ЛП:</b> {PREPLIST}</div>'
		]);

		this.DescrPanel = new Ext.Panel({
			autoScroll: true,
			bodyStyle: 'padding:2px',
			border: true,
			frame: false,
			height: 200,
			region: 'south',
			title: langs('Описание')
		});

		Ext.apply(this, {
			items: [
				this.FilterPanel,
				this.GridPanel,
				this.DescrPanel
			],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}]
		});

		sw.Promed.swLsLinkViewWindow.superclass.initComponent.apply(this, arguments);
	}
});