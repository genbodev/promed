/**
 * swQuestionTypeVisionViewWindow - окно просмотра настроек отображения элементов анкет
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			07.06.2016
 */

/*NO PARSE JSON*/

sw.Promed.swQuestionTypeVisionViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swQuestionTypeVisionViewWindow',
	width: 800,
	height: 600,
	maximized: true,
	maximizable: true,
	layout: 'border',
	title: 'Настройка отображения анкет',

	doSearch: function(reset) {
		/*var base_form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();

		if (reset) {
			base_form.reset();
		}
		var params = base_form.getValues();
		params.start = 0;
		params.limit = 100;

		grid.getStore().load({params: params});*/
	},

	openQuestionTypeVisionEditWindow: function(action){
		if (!action.inlist(['add','edit','view'])) return false;

		var grid = this.GridPanel.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record.get('QuestionTypeVision_id')) return false;
			params.formParams.QuestionTypeVision_id = record.get('QuestionTypeVision_id');
		}

		params.callback = function(){
			this.GridPanel.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swQuestionTypeVisionEditWindow').show(params);
	},

	show: function() {
		sw.Promed.swQuestionTypeVisionViewWindow.superclass.show.apply(this, arguments);

		//var base_form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();

		this.GridPanel.removeAll();

		//this.doSearch(true);

		grid.getStore().load();
	},

	initComponent: function(){
		/*this.FilterPanel = getBaseFiltersFrame({
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
		});*/

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'QTVVW_QuestionTypeVisionGrid',
			region: 'center',
			dataUrl: '/?c=QuestionType&m=loadQuestionTypeVisionGrid',
			paging: true,
			autoLoadData: false,
			root: 'data',
			stringfields: [
				{name: 'QuestionTypeVision_id', type: 'int', header: 'ID', key: true},
				{name: 'QuestionType_id', type: 'int', hidden: true},
				{name: 'QuestionType_pid', type: 'int', hidden: true},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'QuestionTypeVision_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand'}
			],
			actions: [
				{name: 'action_add', handler: function(){this.openQuestionTypeVisionEditWindow('add');}.createDelegate(this)},
				{name: 'action_edit', handler: function(){this.openQuestionTypeVisionEditWindow('edit');}.createDelegate(this)},
				{name: 'action_view', handler: function(){this.openQuestionTypeVisionEditWindow('view');}.createDelegate(this)}
			]
		});

		Ext.apply(this, {
			items: [
				//this.FilterPanel,
				this.GridPanel
			],
			buttons: [{
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

		sw.Promed.swQuestionTypeVisionViewWindow.superclass.initComponent.apply(this, arguments);
	}
});