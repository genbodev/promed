/**
 * swAttributeViewWindow - окно просмотра атрибутов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.07.2014
 */

/*NO PARSE JSON*/

sw.Promed.swAttributeViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAttributeViewWindow',
	width: 800,
	height: 600,
	maximized: true,
	layout: 'border',
	title: lang['atributyi'],

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

	openAttributeEditWindow: function(action){
		if (!action.inlist(['add','edit','view'])) {return false;}

		var grid = this.GridPanel.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record.get('Attribute_id')) {return false;}
			params.formParams.Attribute_id = record.get('Attribute_id');
		}

		params.callback = function(){
			this.GridPanel.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swAttributeEditWindow').show(params);
	},

	show: function() {
		sw.Promed.swAttributeViewWindow.superclass.show.apply(this, arguments);

		var base_form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();

		this.doSearch(true);
	},

	initComponent: function(){
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
					labelWidth: 100,
					items: [{
						xtype: 'textfield',
						name: 'Attribute_Name',
						fieldLabel: lang['naimenovanie']
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 180,
					items: [{
						xtype: 'textfield',
						name: 'Attribute_SysNick',
						fieldLabel: lang['sistemnoe_naimenovanie']
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 60,
					items: [{
						xtype: 'swcommonsprcombo',
						comboSubject: 'AttributeValueType',
						hiddenName: 'AttributeValueType_id',
						fieldLabel: lang['tip']
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						id: 'AVW_SearchButton',
						style: 'margin-left: 30px',
						xtype: 'button',
						text: lang['ustanovit'],
						handler: function() {
							this.doSearch();
						}.createDelegate(this),
						minWidth: 100
					}]
				},  {
					layout: 'form',
					border: false,
					items: [{
						id: 'AVW_SearchButton',
						style: 'margin-left: 20px',
						xtype: 'button',
						text: lang['otmenit'],
						handler: function() {
							this.doSearch(true);
						}.createDelegate(this),
						minWidth: 100
					}]
				}]
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'AVW_AttributeGrid',
			region: 'center',
			object: 'Attribute',
			editformclassname: 'swAttributeEditWindow',
			dataUrl: '/?c=Attribute&m=loadAttributeGrid',
			paging: true,
			autoLoadData: false,
			root: 'data',
			stringfields:
				[
					{name: 'Attribute_id', type: 'int', header: 'ID', key: true},
					{name: 'AttributeValueType_id', type: 'int', hidden: true},
					{name: 'AttributeValueType_SysNick', type: 'string', hidden: true},
					{name: 'Attribute_Code', type: 'int', header: lang['kod'], width: 100},
					{name: 'Attribute_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand'},
					{name: 'Attribute_SysNick', type: 'string', header: lang['sistemnoe_naimenovanie'], width: 260},
					{name: 'AttributeValueType_Name', type: 'string', header: lang['tip'], width: 140}
				],
			actions: [
				{name: 'action_add', handler: function(){this.openAttributeEditWindow('add');}.createDelegate(this)},
				{name: 'action_edit', handler: function(){this.openAttributeEditWindow('edit');}.createDelegate(this)},
				{name: 'action_view', handler: function(){this.openAttributeEditWindow('view');}.createDelegate(this)}
			]
		});

		Ext.apply(this, {
			items: [
				this.FilterPanel,
				this.GridPanel
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
					id: 'AVW_CancelButton',
					text: '<cite>З</cite>акрыть'
				}]
		});

		sw.Promed.swAttributeViewWindow.superclass.initComponent.apply(this, arguments);
	}
});