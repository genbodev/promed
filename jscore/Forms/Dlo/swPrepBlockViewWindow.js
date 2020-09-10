/**
 * swPrepBlockViewWindow - окно справиочника забракованных серий ЛС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Dlo
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.02.2015
 */
/*NO PARSE JSON*/

sw.Promed.swPrepBlockViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPrepBlockViewWindow',
	layout: 'border',
	title: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
	maximizable: false,
	maximized: true,

	filesRenderer: function(v,p,record) {
		var files = [];
		for(var i=1; i<=2; i++) {
			if (!Ext.isEmpty(record.get('DocNormative_File_'+i))) {
				var link = record.get('DocNormative_File_'+i);
				var arr = link.split('/');
				var filename = arr[arr.length-1];
				files.push('<a target="_blank" href="'+link+'">'+filename+'</a>');
			}
		}
		return files.join(', ');
	},

	doSearch: function(reset) {
		var base_form = this.FilterPanel.getForm();

		var grid = this.GridPanel.getGrid();

		if (reset) {
			base_form.reset();
		}

		var params = base_form.getValues();
		params.start = 0;
		params.limit = 50;

		grid.getStore().load({params: params});
	},

	openPrepBlockEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var grid = this.GridPanel.getGrid();

		var params = {};
		params.action = action;
		params.formParams = {};

		if (action != 'add') {
			params.formParams.PrepBlock_id = grid.getSelectionModel().getSelected().get('PrepBlock_id');
		}

		params.callback = function() {
			this.GridPanel.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swPrepBlockEditWindow').show(params);
		return true;
	},

	deletePrepBlock: function() {
		var grid = this.GridPanel.getGrid();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('PrepBlock_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {PrepBlock_id: record.get('PrepBlock_id')};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								this.GridPanel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=RlsDrug&m=deletePrepBlock'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	show: function() {
		sw.Promed.swPrepBlockViewWindow.superclass.show.apply(this, arguments);

		this.readOnly = !(isUserGroup('RosZdrNadzorView') || haveArmType('adminllo') || haveArmType('minzdravdlo') || (getRegionNick()=='saratov' && haveArmType('mekllo')));

		this.GridPanel.setReadOnly(this.readOnly);

		var grid = this.GridPanel.getGrid();
		var base_form = this.FilterPanel.getForm();

		base_form.reset();
		grid.getStore().removeAll();

		base_form.findField('PrepBlockCause_id').getStore().load();

		this.doSearch(false);
	},

	initComponent: function() {
		this.FilterPanel = new Ext.FormPanel({
			frame: true,
			id: 'PBVW_FilterPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 120,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					labelWidth: 180,
					items: [{
						xtype: 'swrlstradenamescombo',
						hiddenName: 'Tradenames_id',
						fieldLabel: lang['torgovoe_naimenovanie'],
						minChars: 3,
						width: 280
					}, {
						xtype: 'swrlsactmatterscombo',
						hiddenName: 'Actmatters_id',
						fieldLabel: lang['deystvuyuschee_veschestvo_mnn'],
						minChars: 3,
						width: 280
					}, {
						xtype: 'swrlsclsdrugformscombo',
						hiddenName: 'RlsClsdrugforms_id',
						fieldLabel: lang['lekarstvennaya_forma'],
						width: 280
					}, {
						xtype: 'swrlscountrycombo',
						hiddenName: 'RlsCountries_id',
						fieldLabel: lang['proizvoditel'],
						width: 280
					}]
				}, {
					layout: 'form',
					labelWidth: 100,
					items: [{
						xtype: 'textfield',
						name: 'Drug_Dose',
						fieldLabel: lang['dozirovka'],
						width: 140
					}, {
						xtype: 'textfield',
						name: 'Drug_Fas',
						fieldLabel: lang['fasovka'],
						width: 140
					}, {
						xtype: 'textfield',
						name: 'Prep_RegNum',
						fieldLabel: lang['№_ru'],
						width: 140
					}, {
						xtype: 'textfield',
						name: 'PrepSeries_Ser',
						fieldLabel: lang['nomer_serii'],
						width: 140
					}]
				}, {
					layout: 'form',
					labelWidth: 180,
					items: [{
						xtype: 'swprepblockcausecombo',
						name: 'PrepBlockCause_id',
						fieldLabel: lang['prichina_vklyucheniya_v_spisok'],
						width: 280
					}, {
						xtype: 'textfield',
						name: 'DocNormative_Num',
						fieldLabel: lang['№_dokumenta'],
						width: 140
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
			id: 'PBVW_PrepBlockGrid',
			dataUrl: '/?c=RlsDrug&m=loadPrepBlockGrid',
			autoLoadData: false,
			paging: true,
			totalProperty: 'totalCount',
			pageSize: 50,
			root: 'data',
			title: lang['spisok_seriy_blokirovannyih_ls'],
			stringfields: [
				{name: 'PrepBlock_id', type: 'int', header: 'ID', key: true},
				{name: 'DocNormative_File_1', type: 'string', hidden: true},
				{name: 'DocNormative_File_2', type: 'string', hidden: true},
				{name: 'Actmatters_Name', header: lang['mnn'], type: 'string', width: 120},
				{name: 'Prep_Name', header: lang['lp'], type: 'string', width: 120},
				{name: 'RlsClsdrugforms_Name', header: lang['lekarstvenna_forma'], type: 'string', width: 120},
				{name: 'Drug_Dose', header: lang['dozirovka'], type: 'string', width: 120},
				{name: 'Drug_Fas', header: lang['fasovka'], type: 'string', width: 120},
				{name: 'Prep_RegNum', header: lang['№_ru'], type: 'string', width: 120},
				{name: 'Firm_Name', header: lang['proizvoditel'], type: 'string', width: 120},
				{name: 'PrepSeries_Ser', header: lang['nomer_serii'], type: 'string', width: 120},
				{name: 'PrepBlockCause_Name', header: lang['osnovanie_blokirovki'], type: 'string', width: 120},
				{name: 'DocNormative_Name_1', header: lang['zapret_oborota'], type: 'string', width: 120},
				{name: 'PrepBlock_Comment', header: lang['primechanie'], type: 'string', width: 240},
				{name: 'Lpu_Name', header: langs('МО, внесшая сведения'), type: 'string', width: 240},
				{name: 'DocNormative_Files', header: lang['prikreplennyie_dokumentyi'], width: 240, renderer: this.filesRenderer},
				{name: 'DocNormative_Name_2', header: lang['oborot_vozobnovlen'], type: 'string', width: 120}
			],
			actions: [
				{name:'action_add', handler: function(){this.openPrepBlockEditWindow('add');}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openPrepBlockEditWindow('edit');}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openPrepBlockEditWindow('view');}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deletePrepBlock();}.createDelegate(this)}
			]
		});

		Ext.apply(this,
		{
			buttons:
			[{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'PBVW_SearchButton',
				text: BTN_FRMSEARCH
			},
			{
				handler: function() {
					this.doSearch(true);
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				id: 'PBVW_ResetButton',
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

		sw.Promed.swPrepBlockViewWindow.superclass.initComponent.apply(this, arguments);
	}
});