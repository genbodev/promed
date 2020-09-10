/**
 * swEvnStickSearchWindow - форма поиска ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version      05.05.2014
 */

sw.Promed.swEvnStickSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['podojdite'] });
		}

		return this.loadMask;
	},
	height: 500,
	id: 'EvnStickSearchWindow',
	initComponent: function() {
		this.PersonInfo = new sw.Promed.PersonInformationPanelShort();

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', disabled: true },
				{ name: 'action_view', disabled: true },
				{ name: 'action_delete', disabled: true },
				{ name: 'action_print', disabled: true }
			],
			autoLoadData: false,
			dataUrl: '/?c=Stick&m=searchEvnStick',
			id: this.id + 'SearchGrid',
			onDblClick: function() {
				this.selectRecord();
			}.createDelegate(this),
			onEnter: function() {
				this.selectRecord();
			}.createDelegate(this),
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm,index,record) {
				//
			},
			paging: false,
			layout:'fit',
			region: 'center',
			stringfields: [
				{ name: 'EvnStick_id', type: 'int', header: 'ID', key: true },
				{ name: 'Org_id', type: 'int', hidden: true },
				{ name: 'EvnStick_all', type: 'string', hidden: true },
				{ name: 'EvnStick_Ser', type: 'string', header: lang['seriya'], width: 80 },
				{ name: 'EvnStick_Num', type: 'string', header: lang['nomer'], width: 150 },
				{ name: 'EvnStick_setDate', type: 'date', header: lang['vyidan'], width: 100 },
				{ name: 'EvnStick_disDate', type: 'date', header: 'Закрыт', width: 100 },
				{ name: 'StickOrder_Name', type: 'string', header: lang['poryadok'], id: 'autoexpand' }
			],
			toolbar: false
		});

		Ext.apply(this, {
			buttons: [{
				handler: function()  {
					this.selectRecord();
				}.createDelegate(this),
				iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
				HelpButton(this),
				{
					handler: function()  {
						this.hide()
					}.createDelegate(this),
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}],
			defaults: {
				split: true
			},
			layout: 'border',
			items: [{
				autoHeight: true,
				border: false,
				region: 'north',
				xtype: 'panel',

				items: [
					this.PersonInfo
				]
			}, {
				border: false,
				layout: 'border',
				region: 'center',
				xtype: 'panel',

				items: [
					this.SearchGrid
				]
			}]
		});

		sw.Promed.swEvnStickSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'beforeshow': function() {
			//
		}
	},
	loadGridWithFilter: function(clear) {
		this.SearchGrid.removeAll({
			clearAll: true
		});

		this.SearchGrid.gFilters = null;

		if ( this.personId ) {
			this.SearchGrid.loadData({
				globalFilters: {
					Person_id: this.personId,
					Evn_id: this.Evn_id
				}
			});
		}
	},
	maximizable: true,
	maximized: false,
	modal: true,
	personId: null,
	plain: true,
	resizable: false,
	onSelect: function() {},
	selectRecord: function(callback_data) {
		var data = {};
		var grid = this.findById(this.id+'SearchGrid');
		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		if( selected_record )
		{
			Ext.apply(data, selected_record.data);
			this.onSelect(data);
		}
		else
		{
			this.hide();
		}
	},
	show: function() {
		sw.Promed.swEvnStickSearchWindow.superclass.show.apply(this, arguments);

		this.getLoadMask().show();

		this.restore();
		this.center();

		this.personId = null;

		if ( arguments[0] ) {
			if ( arguments[0].Person_id ) {
				this.personId = arguments[0].Person_id;
			}
			if ( arguments[0].Evn_id ) {
				this.Evn_id = arguments[0].Evn_id;
			}
			if ( arguments[0].onSelect )
			{
				this.onSelect = arguments[0].onSelect;
			}
		}

		this.PersonInfo.show();
		this.loadGridWithFilter();
		this.setTitle(lang['vyibor_lvn']);

		this.PersonInfo.load({
			Person_id: this.personId,
			Person_Birthday: (arguments[0] && arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0] && arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0] && arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0] && arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		this.doLayout();

		this.syncSize();

		this.getLoadMask().hide();
	},
	width: 800
});