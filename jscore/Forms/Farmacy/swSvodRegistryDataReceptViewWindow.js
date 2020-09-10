/**
 * swSvodRegistryDataReceptViewWindow - окно просмотра сводных реестров рецептов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Salakhov R.
 * @version      06.2013
 * @comment
 */
sw.Promed.swSvodRegistryDataReceptViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['svodnyiy_registr_retseptov_spisok_retseptov'],
	layout: 'border',
	id: 'SvodRegistryDataReceptViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide({
				isChanged: this.isChanged
			});
		}
	},
	checkRecept: function(recept_id, checked) {
		var wnd = this;

		if (recept_id > 0) {
			wnd.isChanged = true;
			Ext.Ajax.request({
				params:{
					RegistryDataRecept_id: recept_id,
					IsReceived: checked
				},
				url:'/?c=SvodRegistry&m=setReceptIsReceived',
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && !result.success) {
						wnd.ReceptGrid.loadData();
					} else {
						var index = wnd.ReceptGrid.getGrid().getStore().findBy(function(rec) {
							return (rec.get('RegistryDataRecept_id') == recept_id);
						});
						var record = wnd.ReceptGrid.getGrid().getStore().getAt(index);
						record.set('IsReceived', checked);
						record.set('ReceptStatusFLKMEK_Name', result.ReceptStatusFLKMEK_Name);
						record.commit();
						wnd.checkCheckedRecepts();
					}
				},
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_sohranit_dannyie_na_servere']);
					wnd.ReceptGrid.loadData();
				}
			});
		}
	},
	checkAllRecepts: function(checked)
	{
		var wnd = this;
		var RegistryDataReceptList = [];

		wnd.ReceptGrid.getGrid().getStore().each(function(rec) {
			RegistryDataReceptList.push(rec.get('RegistryDataRecept_id'));
		});

		if (RegistryDataReceptList.length > 0) {
			wnd.isChanged = true;
			RegistryDataReceptList = Ext.util.JSON.encode(RegistryDataReceptList);
			Ext.Ajax.request({
				params:{
					RegistryDataReceptList: RegistryDataReceptList,
					IsReceived: checked
				},
				url:'/?c=SvodRegistry&m=setAllReceptsIsReceived',
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && !result.success) {
						wnd.ReceptGrid.loadData();
					} else {
						wnd.ReceptGrid.getGrid().getStore().each(function(rec) {
							rec.set('IsReceived', checked);
							rec.set('ReceptStatusFLKMEK_Name', result.ReceptStatusFLKMEK_Name);
							rec.commit();
						});
					}
				},
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_sohranit_dannyie_na_servere']);
					wnd.ReceptGrid.loadData();
				}
			});
		}
	},
	checkCheckedRecepts: function()
	{
		var grid = this.ReceptGrid.getGrid();
		if (grid.getStore().getCount() == 0 || !(grid.getStore().getAt(0).get('RegistryDataRecept_id') > 1)) {
			return false;
		}
		var checkbox = document.getElementById('srdrvw_checkAll_checkbox');
		var checkAll = true;
		checkbox.disabled = false;
		grid.getStore().each(function(rec) {
			if (rec.get('IsReceived') == false) {
				checkAll = false;
			}
		});
		checkbox.checked = checkAll;
	},
	show: function() {
		var wnd = this;
		sw.Promed.swSvodRegistryDataReceptViewWindow.superclass.show.apply(this, arguments);
		this.onHide = Ext.emptyFn;
		this.Registry_id = null;
		this.isChanged = false;

		if ( !arguments[0] || !arguments[0].Registry_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
			return false;
		} else {
			this.Registry_id = arguments[0].Registry_id;
		}
		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		this.ReceptGrid.loadData({
			globalFilters: {
				Registry_id: this.Registry_id,
				start: 0,
				limit: 100
			}
		});
	},
	initComponent: function() {
		var wnd = this;

		wnd.ReceptGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			schema: 'r64',
			obj_isEvn: false,
			border: true,
			dataUrl: '/?c=SvodRegistry&m=loadRegistryDataReceptList',
			height: 180,
			region: 'center',
			object: 'Registry',
			id: 'SvodRegistryDataReceptGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'RegistryDataRecept_id', type: 'int', header: 'ID', key: true},
				{name: 'ReceptStatusFLKMEK_Name', type: 'string', header: lang['status'], width: 250},
				{name: 'RegistryDataRecept_Ser', type: 'string', header: lang['seriya'], width: 100},
				{name: 'RegistryDataRecept_Num', type: 'string', header: lang['nomer'], width: 100},
				{name: 'RegistryStatus_Code', hidden: true},
				{
					name: 'IsReceived',
					header: '<input id="srdrvw_checkAll_checkbox" type="checkbox" disabled="disabled" onClick="getWnd(\'swSvodRegistryDataReceptViewWindow\').checkAllRecepts(this.checked);"> Получен экземпляр рецепта',
					width: 200,
					sortable: false,
					renderer: function(v, p, record) {
						var recept_id = record.get('RegistryDataRecept_id');
						return '<input type="checkbox" value="'+recept_id+'"'+(record.get('IsReceived') > 0 ? ' checked="checked"' : '')+''+(['1','2'].indexOf(record.get('RegistryStatus_Code')) == -1 ? ' disabled="disabled"' : '')+'" onClick="getWnd(\'swSvodRegistryDataReceptViewWindow\').checkRecept(this.value, this.checked);">';
					}
				}/*,
				{name: 'FinDocument_Date', type: 'string', header: lang['data_scheta'], width: 100},
				{name: 'FinDocument_Sum', type: 'money', header: lang['summa_scheta'], width: 100},
				{name: 'FinDocumentSpec_Sum', type: 'money', header: lang['summa_oplatyi'], width: 100}*/
			],
			toolbar: true,
			onLoadData: function()
			{
				wnd.checkCheckedRecepts();
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
				[{
					text: '-'
				},
				HelpButton(this, 0),
				{
					handler: function()
					{
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}],
			items:[
				wnd.ReceptGrid
			]
		});
		sw.Promed.swSvodRegistryDataReceptViewWindow.superclass.initComponent.apply(this, arguments);
	}
});