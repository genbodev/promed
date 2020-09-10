/**
 * swAttributeVisionViewWindow - окно просмотра атрибутов
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

sw.Promed.swAttributeVisionViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAttributeVisionViewWindow',
	width: 800,
	height: 600,
	maximized: true,
	layout: 'border',
	title: lang['oblasti_vidimosti_atributov'],

	listeners: {
		resize: function() {
			if(this.layout.layout)
				this.doLayout();
		}
	},

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

	openAttributeVisionEditWindow: function(action){
		if (!action.inlist(['add','edit','view'])) {return false;}

		var grid = this.GridPanel.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record.get('AttributeVision_id')) {return false;}
			params.formParams.AttributeVision_id = record.get('AttributeVision_id');
		}

		params.callback = function(){
			this.GridPanel.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swAttributeVisionEditWindow').show(params);
	},

	show: function() {
		sw.Promed.swAttributeVisionViewWindow.superclass.show.apply(this, arguments);

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
					labelWidth: 80,
					items: [{
						xtype: 'textfield',
						name: 'Attribute_Name',
						fieldLabel: lang['atribut']
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 80,
					items: [{
						xtype: 'textfield',
						name: 'AttributeVision_TableName',
						fieldLabel: lang['obyekt_bd']
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 80,
					items: [{
						xtype  : 'combo',
						store  : new Ext.data.SimpleStore({
							fields : ['Region_id','Region_Name'],
							data   : [
								['10' , lang['kareliya']],
								['19' , lang['hakasiya']],
								['30' , lang['astrahan']],
								['60' , lang['pskov']],
								['63' , lang['samara']],
								['64' , lang['saratov']],
								['77' , lang['moskva']],
								['101' , lang['kazahstan']],
								['66' , lang['ekaterinburg']],
								['59' , lang['perm']],
								['2' , lang['ufa']]
							]
						}),
						tpl: '<tpl for="."><div class="x-combo-list-item">'+
							'{Region_Name}&nbsp;'+
							'</div></tpl>',
						name        : 'Region_id' ,
						hiddenName  : 'Region_id' ,
						fieldLabel  : lang['region'],
						displayField: 'Region_Name',
						valueField  : 'Region_id',
						triggerAction : 'all',
						mode        : 'local',
						editable    : false
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 80,
					items: [{
						xtype: 'sworgcomboex',
						hiddenName: 'org_id',
						fieldLabel: lang['organizatsiya'],
						width: 240
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						id: 'AVVW_SearchButton',
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
						id: 'AVVW_SearchButton',
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
			id: 'AVVW_AttributeVisionGrid',
			region: 'center',
			object: 'AttributeVision',
			editformclassname: 'swAttributeVisionEditWindow',
			dataUrl: '/?c=Attribute&m=loadAttributeVisionGrid',
			pageSize: 100,
			paging: true,
			autoLoadData: false,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields:
				[
					{name: 'AttributeVision_id', type: 'int', header: 'ID', key: true},
					{name: 'Attribute_id', type: 'int', hidden: true},
					{name: 'Region_id', type: 'int', hidden: true},
					{name: 'Org_id', type: 'int', hidden: true},
					{name: 'Attribute_Name', type: 'string', header: lang['atribut'], width: 200},
					{name: 'AttributeVision_TableName', type: 'string', header: lang['obyekt_bd'], width: 200},
					{name: 'AttributeVision_Sort', type: 'int', header: lang['sortirovka'], width: 100},
					{name: 'Region_Name', type: 'string', header: lang['region'], width: 200},
					{name: 'Org_Name', type: 'string', header: lang['organizatsiya'], id: 'autoexpand'}
				],
			actions: [
				{name: 'action_add', handler: function(){this.openAttributeVisionEditWindow('add');}.createDelegate(this)},
				{name: 'action_edit', handler: function(){this.openAttributeVisionEditWindow('edit');}.createDelegate(this)},
				{name: 'action_view', handler: function(){this.openAttributeVisionEditWindow('view');}.createDelegate(this)}
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
					id: 'AVVW_CancelButton',
					text: '<cite>З</cite>акрыть'
				}]
		});

		sw.Promed.swAttributeVisionViewWindow.superclass.initComponent.apply(this, arguments);
	}
});