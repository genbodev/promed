/**
 * swRegistryReceptExpertiseTypeViewWindow - окно справочника критериев экспертизы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			RegistryRecept
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			05.11.2013
 */

sw.Promed.swRegistryReceptExpertiseTypeViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border : false,
	closeAction :'hide',
	height: 500,
	width: 800,
	id: 'RegistryReceptExpertiseTypeViewWindow',
	title: lang['spravochnik_kriteriev_ekspertizyi'],
	layout: 'border',
	maximized: true,

	doFilterExpertiseTypeGrid: function()
	{
		var base_form = this.FilterPanel.getForm();
		var grid = this.ExpertiseTypeGrid.getGrid();

		var is_flk = base_form.findField('RegistryReceptExpertiseType_IsFLK').getValue();

		grid.getStore().filterBy(function(rec) {
			return (rec.get('RegistryReceptExpertiseType_IsFLK') == is_flk);
		});
		grid.getSelectionModel().selectFirstRow();
	},

	doFilterErrorTypeGrid: function(params)
	{
		var grid = this.ErrorTypeGrid.getGrid();
		var error_list = '';
		var error_arr = [];

		if (!params || !params.ExpertiseTypeData) {
			grid.getStore().filterBy(function(rec) {return false;});
		} else {
			error_list = params.ExpertiseTypeData.RegistryReceptExpertiseType_ErrorList;
			error_arr = error_list.split(', ');
			grid.getStore().filterBy(function(rec) {
				return (rec.get('RegistryReceptErrorType_Type').inlist(error_arr));
			});
			grid.getSelectionModel().selectFirstRow();
		}
	},

	show: function()
	{
		sw.Promed.swRegistryReceptExpertiseTypeViewWindow.superclass.show.apply(this, arguments);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var wnd = this;
		var ExpertiseTypeGrid = wnd.ExpertiseTypeGrid.getGrid();
		var ErrorTypeGrid = wnd.ErrorTypeGrid.getGrid();

		ExpertiseTypeGrid.getStore().load();

		loadMask.hide();
	},

	initComponent: function()
	{
		var wnd = this;

		this.FilterPanel = new Ext.FormPanel({
			autoHeight: true,
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			bodyStyle:'padding-top: 5px;background:#DFE8F6;',
			border: true,
			keys:
				[{
					key: Ext.EventObject.ENTER,
					fn: function(e)
					{
						wnd.doFilter();
					},
					stopEvent: true
				}],
			items: [
				new Ext.form.ComboBox({
					allowBlank: false,
					fieldLabel: lang['tip_kriteriya'],
					hiddenName: 'RegistryReceptExpertiseType_IsFLK',
					width: 150,
					value: 2,
					triggerAction: 'all',
					store: [
						[2, lang['flk']],
						[1, lang['mek']]
					],
					listeners: {
						'select': function(rec) {
							this.doFilterExpertiseTypeGrid();
						}.createDelegate(this)
					}
				})
			]
		});

		this.ExpertiseTypeGrid  = new sw.Promed.ViewFrame({
			id: 'RegistryReceptExpertiseTypeGrid',
			title: lang['spisok_kriteriev'],
			dataUrl: '/?c=RegistryRecept&m=loadRegistryReceptExpertiseTypeGrid',
			autoLoadData: false,
			region: 'center',
			toolbar: false,
			paging: false,
			root: 'data',
			stringfields: [
				{name: 'RegistryReceptExpertiseType_id', type: 'int', header: 'ID', key: true},
				{name: 'RegistryReceptExpertiseType_SysNick', type: 'string', hidden: true},
				{name: 'RegistryReceptExpertiseType_IsFLK', type: 'int', hidden: true},
				{name: 'AllowEdit', type: 'int', hidden: true},
				{name: 'RegistryReceptExpertiseType_IsActive', type: 'checkcolumnedit', header: lang['aktivnost'], width:80},
				{name: 'RegistryReceptExpertiseType_Name', type:'string', header: lang['naimenovanie'], id: 'autoexpand'},
				{name: 'RegistryReceptExpertiseType_ErrorList', type:'string', header: lang['spisok_kodov_oshibok'], width: 350}
			],
			saveRecord: function(o) {
				var record = o.record,
					store = o.grid.getStore(),
					sm = o.grid.getSelectionModel();

				sm.selectRow(store.indexOf(record), true);
				if (record.get('AllowEdit') == 0) {
					record.reject();
				} else {
					var params = record.data;
					if (params.RegistryReceptExpertiseType_IsActive == true) {
						params.RegistryReceptExpertiseType_IsActive = 2;
					} else if (params.RegistryReceptExpertiseType_IsActive == false) {
						params.RegistryReceptExpertiseType_IsActive = 1;
					}
					Ext.Ajax.request({
						url: '/?c=RegistryRecept&m=saveRegistryReceptExpertiseTypeActive',
						params: params,
						failure: function(response, options) {
							record.reject();
						},
						success: function(response, options) { log(options);
							record.data = options.params;
							if (record.get('RegistryReceptExpertiseType_IsActive') == 2) {
								record.set('RegistryReceptExpertiseType_IsActive',true);
							} else if (record.get('RegistryReceptExpertiseType_IsActive') == 1) {
								record.set('RegistryReceptExpertiseType_IsActive',false);
							}
							record.commit();
						}
					});
				}
			},
			onRowSelect: function(sm,index,record) {
				wnd.doFilterErrorTypeGrid({ExpertiseTypeData: record.data});
			},
			onLoadData: function() {
				wnd.doFilterExpertiseTypeGrid();
				wnd.ErrorTypeGrid.getGrid().getStore().load();
			}
		});

		this.ErrorTypeGrid  = new sw.Promed.ViewFrame({
			id: 'RegistryReceptErrorTypeGrid',
			title: lang['spisok_oshibok'],
			dataUrl: '/?c=RegistryRecept&m=loadRegistryReceptErrorTypeGrid',
			autoLoadData: false,
			height: 350,
			region: 'south',
			toolbar: false,
			paging: false,
			root: 'data',
			stringfields: [
				{name: 'RegistryReceptErrorType_id', type: 'int', header: 'ID', key: true},
				{name: 'RegistryReceptErrorType_Type', type: 'string', header: lang['kod_oshibki'], width: 100},
				{name: 'RegistryReceptErrorType_Name', type: 'string', header: lang['naimenovanie'], width: 500},
				{name: 'RegistryReceptErrorType_Descr', type: 'string', header: lang['opisanie'], id: 'autoexpand'}
			],
			onLoadData: function() {
				var base_form = wnd.FilterPanel.getForm();
				var is_flk_combo = base_form.findField('RegistryReceptExpertiseType_IsFLK');
				is_flk_combo.fireEvent('select',is_flk_combo,is_flk_combo.getStore().getAt(0));
			}
		});

		Ext.apply(this, {
			items : [
				this.FilterPanel,
				this.ExpertiseTypeGrid,
				this.ErrorTypeGrid
			],
			buttons : [
				'-',
				HelpButton(this, -1),
				{
					handler: function()
					{
						this.ownerCt.hide();
					},
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}]
		});
		sw.Promed.swRegistryReceptExpertiseTypeViewWindow.superclass.initComponent.apply(this, arguments);
	}
});