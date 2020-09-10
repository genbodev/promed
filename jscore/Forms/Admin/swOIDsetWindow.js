/**
* swOIDsetWindow - окно простановки OID для организации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Samir Abakhri
* @version      14.04.2015
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swOIDsetWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: true,
	height: 500,
	width: 800,
	id: 'swOIDsetWindow',
	title: WND_SETOID,
	layout: 'border',
	resizable: true,

    toggleFRMO: function(Lpu_id) {
		var grid = this.RegistryDataGrid.getGrid();
		var record = grid.getStore().getById(Lpu_id);

		var Lpu_isFRMO = record.get('Lpu_isFRMO') == 2 ? 1 : 2;
		record.set('Lpu_isFRMO', Lpu_isFRMO);

		var loadMask = new Ext.LoadMask(this.RegistryDataGrid.getEl(), {msg: lang['sohranenie_izmeneniy']});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=Org&m=setLpuIsFRMO',
			params: {
				Lpu_id: Lpu_id,
				Lpu_isFRMO: Lpu_isFRMO
			},
			success: function() {
				loadMask.hide();
				record.commit();
			}.createDelegate(this),
			failure: function() {
				loadMask.hide();
				record.reject();
			}.createDelegate(this)
		});
	},

	setOID: function(record) {
		if (!record || Ext.isEmpty(record.get('Lpu_id'))) {
			return;
		}

		var loadMask = new Ext.LoadMask(this.RegistryDataGrid.getEl(), {msg: lang['sohranenie_izmeneniy']});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=Org&m=setLpuOID',
			params: {
				Lpu_id: record.get('Lpu_id'),
				PassportToken_tid: record.get('PassportToken_tid')
			},
			success: function() {
				loadMask.hide();
				record.commit();
			}.createDelegate(this),
			failure: function() {
				loadMask.hide();
				record.reject();
			}.createDelegate(this)
		});
	},

	initComponent: function()
	{
		var _this = this;

		this.checkRenderer = function(v, p, record) {
			var id = record.get('Lpu_id');
			var value = 'value="'+id+'"';
			var checked = record.get('Lpu_isFRMO') == 2 ? ' checked="checked"' : '';
			var onclick = 'onClick="Ext.getCmp(\''+_this.id+'\').toggleFRMO(this.value);"';
			var disabled = '';

			return '<input type="checkbox" '+value+' '+checked+' '+onclick+' '+disabled+'>';
		};

		this.OIDEditor = new Ext.form.TextField({maskRe: /[0-9.]/});
		
		this.RegistryDataGrid = new sw.Promed.ViewFrame(
		{
			id: _this.id+'RegistryDataGrid',
			title:'',
            enableKeyEvents: true,
			useEmptyRecord: false,
			noSelectFirstRowOnFocus: true,
			object: 'Registry',
			dataUrl: '/?c=Org&m=loadLpuSetOIDGrid',
			autoLoadData: false,
			selectionModel: 'cell',
			region: 'center',
			layout: 'fit',
            stateful: true,
			toolbar: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'Lpu_isFRMO', header: 'Выгрузка в ФРМО', width: 100, renderer: this.checkRenderer},
				{name: 'PassportToken_tid', type: 'string', header: 'OID', width: 150, editor: this.OIDEditor},
				{name: 'Lpu_id', type: 'int', header: 'Lpu_id', key: true, hidden:!isSuperAdmin()},
                {name: 'Lpu_Nick', type: 'string', header: lang['kratkoe_naimenovanie_mo'], width: 200},
                {name: 'Lpu_Name', type: 'string', header: lang['polnoe_naimenovanie_mo'], id: 'autoexpand'},
                {name: 'Lpu_begDate', type: 'date', header: lang['data_nachala_deyatelnosti'], width: 100},
                {name: 'Lpu_endDate', type: 'date', header: lang['data_zakryitiya'], width: 100}
			],
			onAfterEditSelf: function(o) {
				if (o.originalValue == o.value) {
					o.record.commit();
				} else if(o.field == 'PassportToken_tid') {
					_this.setOID(o.record);
				}
			},
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			]
		});

		this.formPanel = new Ext.Panel(
		{
			region: 'center',
			labelAlign: 'right',
			layout: 'fit',
			labelWidth: 50,
			border: false,
			tbar: this.mainToolBar,
			items:
			[
				this.RegistryDataGrid
			]
		});
		
		Ext.apply(this, 
		{
			items: 
			[
				_this.formPanel
			],
			buttons:
			[{
				text:'-'
			},
			HelpButton(this, -1),
			{
				text: BTN_FRMCANCEL,
				id: 'lrCancel',
				iconCls: 'cancel16',
				handler: function()
				{
					this.ownerCt.hide();
				}
			}]
		});
		sw.Promed.swOIDsetWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swOIDsetWindow.superclass.show.apply(this, arguments);
		this.RegistryDataGrid.loadData();

	}
});