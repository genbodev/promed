/**
 * swPersonEncrypHIVViewWindow - окно просмотра шифрование ВИЧ-инфицированных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			04.06.2015
 */
/*NO PARSE JSON*/

sw.Promed.swPersonEncrypHIVViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonEncrypHIVViewWindow',
	layout: 'border',
	title: lang['shifrovanie_vich-infitsirovannyih'],
	maximizable: false,
	maximized: true,

	doSearch: function(reset) {
		var base_form = this.FilterPanel.getForm();

		var grid_panel = this.GridPanel;

		if (!grid_panel) {
			return;
		}

		var grid = grid_panel.getGrid();

		if (reset) {
			base_form.reset();
		}

		var params = base_form.getValues();
		params.start = 0;
		params.limit = 100;

		grid.getStore().load({params: params});
	},

	openPersonEvncrypHIVEditWindow: function(action, person_type) {
		if (!action.inlist(['add','edit','view']) || (action == 'add' && Ext.isEmpty(person_type))) {
			return false;
		}

		var base_form = this.FilterPanel.getForm();
		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();

		var params = {};
		params.action = action;

		params.callback = function(key) {
			// key значение ключевого поля в гриде для того, чтобы установить выделение этой записи
			grid_panel.loadData({
				callback: function() {
					var index = grid_panel.getIndexByValue(key);
					if (index >= 0) {
						grid.focus();
						grid.getView().focusRow(index);
						grid.getSelectionModel().selectRow(index);
					}
				}
			});
		};

		if (action == 'add' && person_type == 'person') {
			if ( getWnd('swPersonSearchWindow').isVisible() ) {
				sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
				return false;
			}

			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					Ext.Ajax.request({
						params: {Person_id: person_data.Person_id},
						url: '/?c=PersonEncrypHIV&m=checkPersonEncrypHIVExists',
						success: function(response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success) {
								getWnd('swPersonSearchWindow').hide();
								params.Person_id = person_data.Person_id;
								getWnd('swPersonEncrypHIVEditWindow').show(params);
							}
						},
						failure: function(response) {

						}
					});
				},
				personFirname: base_form.findField('Person_FirName').getValue(),
				personSecname: base_form.findField('Person_SecName').getValue(),
				personSurname: base_form.findField('Person_SurName').getValue(),
				PersonBirthDay_BirthDay: base_form.findField('Person_BirthDay').getValue(),
				searchMode: 'all'
			});
		} else {
			if (action.inlist(['edit','view'])) {
				var record = grid.getSelectionModel().getSelected();
				if (!record || Ext.isEmpty(record.get('PersonEncrypHIV_id'))) {
					return false;
				}
				params.PersonEncrypHIV_id = record.get('PersonEncrypHIV_id');
			}
			getWnd('swPersonEncrypHIVEditWindow').show(params);
		}

		return true;
	},

	deletePersonEvncrypHIV: function(options) {
		var wnd = this;
		var grid_panel = this.GridPanel;
		var grid = grid_panel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('PersonEncrypHIV_id')) {
			return false;
		}

		var doDelete = function(params) {
			Ext.Ajax.request({
				callback: function(opt, scs, response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.Alert_Msg && response_obj.Error_Code == 101) {
						var msg = response_obj.Alert_Msg;
						var buttons = buttons = {ok: 'Удалить',cancel: 'Отмена'};

						sw.swMsg.show({
							buttons: buttons,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'ok' ) {
									params.ignoreAnonymWarning = 1;
									doDelete(params);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: msg,
							title: 'Подтверждение'
						});
					} else if (!response_obj.Error_Msg) {
						grid_panel.getAction('action_refresh').execute();
					}
				},
				params: params,
				url: '/?c=PersonEncrypHIV&m=deletePersonEncrypHIV'
			});
		};

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					doDelete({
						PersonEncrypHIV_id: record.get('PersonEncrypHIV_id')
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	show: function() {
		sw.Promed.swPersonEncrypHIVViewWindow.superclass.show.apply(this, arguments);

		var base_form = this.FilterPanel.getForm();
		base_form.reset();

		if (!this.GridPanel.getAction('action_add_menu')) {
			this.GridPanel.addActions({
				name: 'action_add_menu',
				iconCls: 'add16',
				text: lang['dobavit'],
				handler: function() {},
				menu: new Ext.menu.Menu({
					items: [{
						text: lang['anonim'],
						handler: function() {this.openPersonEvncrypHIVEditWindow('add', 'anonym')}.createDelegate(this)
					}, {
						text: lang['realnyiy_patsient'],
						handler: function() {this.openPersonEvncrypHIVEditWindow('add', 'person')}.createDelegate(this)
					}]
				})
			}, 0);
		}

		this.doSearch(true);
	},

	initComponent: function() {
		this.FilterPanel = new Ext.FormPanel({
			frame: true,
			id: 'PEHVW_FilterPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					labelWidth: 160,
					items: [{
						xtype: 'textfieldpmw',
						maskRe: /[^_%]/,
						name: 'Person_SurName',
						fieldLabel: lang['familiya'],
						width: 220,
						tabIndex: TABINDEX_PEHVW + 1
					}, {
						xtype: 'swdatefield',
						name: 'Person_BirthDay',
						fieldLabel: lang['dr'],
						width: 100,
						tabIndex: TABINDEX_PEHVW + 4
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'EncrypHIVTerr',
						hiddenName: 'EncrypHIVTerr_id',
						typeCode: 'int',
						fieldLabel: lang['territoriya_projivaniya'],
						width: 220,
						tabIndex: TABINDEX_PEHVW + 6
					}]
				}, {
					layout: 'form',
					labelWidth: 80,
					items: [{
						xtype: 'textfieldpmw',
						maskRe: /[^_%]/,
						name: 'Person_FirName',
						fieldLabel: lang['imya'],
						width: 200,
						tabIndex: TABINDEX_PEHVW + 2
					}, {
						comboSubject: 'Sex',
						fieldLabel: lang['pol'],
						hiddenName: 'Sex_id',
						loadParams: {
							params: {
								where: 'where Sex_id in (1, 2)'
							}
						},
						tabIndex: TABINDEX_PEHVW + 5,
						width: 120,
						xtype: 'swcommonsprcombo'
					}, {
						allowBlank: false,
						editable: false,
						xtype: 'swbaselocalcombo',
						hiddenName: 'PersonType_id',
						valueField: 'PersonType_id',
						displayField: 'PersonType_Name',
						fieldLabel: lang['patsient'],
						value: 1,
						store: new Ext.data.SimpleStore({
							key: 'PersonType_id',
							autoLoad: false,
							fields: [
								{name: 'PersonType_id', type: 'int'},
								{name: 'PersonType_Name', type: 'string'}
							],
							data: [
								[1, lang['vse']],
								[2, lang['tolko_anonimnyie']],
								[3, lang['tolko_realnyie']]
							]
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{PersonType_Name}&nbsp;',
							'</div></tpl>'
						),
						width: 200,
						tabIndex: TABINDEX_PEHVW + 7
					}]
				}, {
					layout: 'form',
					labelWidth: 80,
					items: [{
						xtype: 'textfieldpmw',
						maskRe: /[^_%]/,
						name: 'Person_SecName',
						fieldLabel: lang['otchestvo'],
						width: 200,
						tabIndex: TABINDEX_PEHVW + 3
					}]
				}]
			}],
			keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'PEHVW_PersonEncrypHIVGrid',
			dataUrl: '/?c=PersonEncrypHIV&m=loadPersonEncrypHIVGrid',
			border: false,
			autoLoadData: false,
			pageSize: 100,
			paging: true,
			root: 'data',
			stringfields: [
				{name: 'PersonEncrypHIV_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'Sex_id', type: 'int', hidden: true},
				{name: 'AttachLpu_id', type: 'int', hidden: true},
				{name: 'PersonEncrypHIV_Encryp', header: lang['shifr'], type: 'string', width: 120},
				{name: 'PersonEncrypHIV_setDT', header: lang['data_sozdaniya'], type: 'date', width: 120},
				{name: 'Person_Fio', header: lang['fio'], type: 'string', width: 260},
				{name: 'Person_BirthDay', header: lang['dr'], type: 'date', width: 120},
				{name: 'Sex_Name', header: lang['pol'], type: 'string', width: 120},
				{name: 'AttachLpu_Name', header: lang['mo_prikrepleniya'], type: 'string', id: 'autoexpand'}
			],
			totalProperty: 'totalCount',
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', handler: function(){this.openPersonEvncrypHIVEditWindow('edit');}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openPersonEvncrypHIVEditWindow('view');}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deletePersonEvncrypHIV();}.createDelegate(this)}
			]
		});

		Ext.apply(this,{
			buttons:
			[{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'PEHVW_SearchButton',
				text: BTN_FRMSEARCH
			},
			{
				handler: function() {
					this.doSearch(true);
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				id: 'PEHVW_ResetButton',
				text: BTN_FRMRESET
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [this.FilterPanel, this.GridPanel]
		});

		sw.Promed.swPersonEncrypHIVViewWindow.superclass.initComponent.apply(this, arguments);
	}
});