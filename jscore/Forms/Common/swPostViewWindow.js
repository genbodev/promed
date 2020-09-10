/**
 * swPostViewWindow - окно просмотра списка должностей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.04.2016
 */
/*NO PARSE JSON*/

sw.Promed.swPostViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPostViewWindow',
	maximizable: true,
	maximized: false,
	layout: 'border',
	title: 'Должности',

	openPostEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var wnd = this;
		var grid = this.GridPanel.getGrid();
		var params = {action: action};

		params.callback = function() {
			wnd.GridPanel.getAction('action_refresh').execute();
		};
		if (action == 'add') {
			params.Org_id = this.Org_id;
		} else {
			var record = grid.getSelectionModel().getSelected();
			if (!record) {
				return false;
			}
			params.Post_id = record.get('Post_id');
		}

		getWnd('swPostEditWindow').show(params);
		return true;
	},

	doSelect: function() {
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (record && !Ext.isEmpty(record.get('Post_id'))) {
			this.onSelect(record.data);
			this.hide();
		}
	},

	doSearch: function(reset) {
		var base_form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();

		if (reset) {
			base_form.reset();
		}

		var params = base_form.getValues();
        params.searchMode = this.searchMode;
        params.Org_id = this.Org_id;

		grid.getStore().load({params: params});
	},

	show: function() {
		sw.Promed.swPostViewWindow.superclass.show.apply(this, arguments);

		this.onSelect = Ext.emptyFn;
		this.searchMode = null;
		this.Org_id = null;

		if (arguments[0] && arguments[0].onSelect) {
			this.onSelect = arguments[0].onSelect;
			Ext.getCmp('PVW_SelectButton').show();
		} else {
			Ext.getCmp('PVW_SelectButton').hide();
		}

        if (arguments[0] && arguments[0].searchMode) {
            this.searchMode = arguments[0].searchMode;
        }
        if (arguments[0] && arguments[0].Org_id) {
            this.Org_id = arguments[0].Org_id;
        }

		this.GridPanel.removeAll();
	},

	initComponent: function() {
		this.FilterPanel = new Ext.form.FormPanel({
			region: 'north',
			frame: true,
			autoHeight: true,
			items: [{
				xtype: 'textfield',
				name: 'Post_Name',
				fieldLabel: 'Наименование',
				anchor: '100%'
			}],
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				stopEvent: true
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			dataUrl: '/?c=Post&m=loadPostGrid',
			object: 'Post',
			editformclassname: 'swPostEditWindow',
			autoLoadData: false,
			region: 'center',
			stringfields: [
				{name: 'Post_id', type: 'int', header: 'ID', key: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Post_Name', header: 'Наименование', type: 'string', id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', handler: function(){this.openPostEditWindow('add')}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openPostEditWindow('edit')}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openPostEditWindow('view')}.createDelegate(this)},
				{name:'action_delete'},
				{name:'action_refresh'}
			],
			onRowSelect: function(sm, index, record) {
				if (record && !Ext.isEmpty(record.get('Post_id'))) {
					this.GridPanel.getAction('action_edit').setDisabled(record.get('Server_id') == 0);
				}
			}.createDelegate(this)
		});

		Ext.apply(this,{
			buttons: [
				{
					handler: function() {
						this.doSearch();
					}.createDelegate(this),
					iconCls: 'search16',
					text: BTN_FRMSEARCH
				},
				{
					id: 'PVW_SelectButton',
					handler: function() {
						this.doSelect();
					}.createDelegate(this),
					iconCls: 'ok16',
					text: BTN_FRMSELECT
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FilterPanel,	this.GridPanel]
		});

		sw.Promed.swPostViewWindow.superclass.initComponent.apply(this, arguments);
	}
});