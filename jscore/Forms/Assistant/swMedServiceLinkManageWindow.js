/**
 * swMedServiceLinkManageWindow - Редактирование справочника "Связь между службами"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       gabdushev
 * @version      06.2012
 * @comment
 */
sw.Promed.swMedServiceLinkManageWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['svyazi_mejdu_slujbami'],
	layout: 'border',
	id: 'MedServiceLinkManageWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	getLabCols: function (){
		return ['pz_lpu_Lpu_Nick','pz_MedServiceType_Name','MedService_id_Name','pz_Address_Address'];
	},
	hideLabCols: function() {
		this.showHideCols(this.getLabCols(), false);
	},
	showLabCols: function() {
		this.showHideCols(this.getLabCols(), true);
	},
	hidePzCols: function() {
		this.showHideCols(this.getPzCols(), false);
	},
	showPzCols: function() {
		this.showHideCols(this.getPzCols(), true);
	},
	getPzCols: function (){
		return ['lab_lpu_Lpu_Nick', 'lab_MedServiceType_Name', 'MedService_lid_Name','lab_Address_Address'];
	},
	showHideCols: function (cols, show){
		var vf = this.findById('MedServiceLinkGrid');
		cols.forEach(function (el){
			vf.setColumnHidden(el, !show);
		});
	},
	show: function() {
		var that = this;
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() {that.hide();});
			return false;
		}
		if (arguments[0].parentARMType) {
			this.parentARMType = arguments[0].parentARMType;
		}
		if (arguments[0].MedService_id) {
			this.MedService_id = arguments[0].MedService_id;
			this.MedService_lid = null;
			this.hideLabCols();
			this.showPzCols();
		} else {
			if (arguments[0].MedService_lid) {
				this.MedService_id = null;
				this.MedService_lid = arguments[0].MedService_lid;
				this.hidePzCols();
				this.showLabCols();
			} else {
				sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() {that.hide();});
				return false;
			}
		}
		
		this.MedServiceLinkType_id = arguments[0].MedServiceLinkType_id || null;
		this.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick || 'lab';
		
		sw.Promed.swMedServiceLinkManageWindow.superclass.show.apply(this, arguments);
		var params = {MedService_id: null, MedService_lid: null};
		if (that.parentARMType) {
			params.ARMType = that.parentARMType;
		}
		params.MedServiceLinkType_id = this.MedServiceLinkType_id;
		params.MedServiceType_SysNick = this.MedServiceType_SysNick;
		if (that.MedService_id){
			params.MedService_id = that.MedService_id;
		} else if (that.MedService_lid){
			params.MedService_lid = that.MedService_lid;
		}
		//log(params);
		this.grid.getStore().load({params: params});
	},
	initComponent: function() {
		var that = this;
		var grid = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add',
					handler: function (){
						var params = {
							action: 'add'
						}
						if (that.MedService_id){
							params.MedService_id = that.MedService_id;
						} else {
							if (that.MedService_lid){
								params.MedService_lid = that.MedService_lid;
							} else {
								//не должно возникнуть никогда
								sw.swMsg.alert(lang['oshibka_obratites_k_razrabochikam'], lang['medservice_lid_i_medservice_id_ne_ukazanyi'], function() {that.hide();});
							}
						}
						params.callback = function (){
							that.grid.getStore().load();
						}
						params.MedServiceLinkType_id = that.MedServiceLinkType_id;
						params.MedServiceType_SysNick = that.MedServiceType_SysNick;
						
						getWnd('swMedServiceLinkEditWindow').show(params);
					}
				},
				{
					name: 'action_edit',
					handler: function (){
						var params = {
							MedServiceLink_id: that.grid.getSelectionModel().getSelected().id,
							action: 'edit'
						}
						if (that.MedService_id){
							params.MedService_id = that.MedService_id;
						} else {
							if (that.MedService_lid){
								params.MedService_lid = that.MedService_lid;
							} else {
								//не должно возникнуть никогда
								sw.swMsg.alert(lang['oshibka_obratites_k_razrabochikam'], lang['medservice_lid_i_medservice_id_ne_ukazanyi'], function() {that.hide();});
							}
						}
						params.callback = function (){
							that.grid.getStore().load();
						}
						params.MedServiceLinkType_id = that.MedServiceLinkType_id;
						params.MedServiceType_SysNick = that.MedServiceType_SysNick;
						
						getWnd('swMedServiceLinkEditWindow').show(params);
					}
				},
				{name: 'action_view', hidden: true},
				{name: 'action_delete'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MedServiceLink&m=loadList',
			height: 180,
			region: 'center',
			object: 'MedServiceLink',
			editformclassname: 'swMedServiceLinkEditWindow',
			id: 'MedServiceLinkGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'MedServiceLink_id', type: 'int', header: 'ID', key: true},

				{name: 'pz_lpu_Lpu_Nick', type: 'string', header:lang['mo'], width: 120},
				{name: 'pz_MedServiceType_Name', type: 'string', header:lang['tip_svyazannoy_slujbyi'], width: 220},
				{name: 'MedService_id_Name', type: 'string', header: lang['svyazannaya_slujba'], width: 320},
				{name: 'pz_Address_Address', type: 'string', header:lang['adres'], width: 320},

				{name: 'lab_lpu_Lpu_Nick', type: 'string', header:lang['mo'], width: 120},
				{name: 'lab_MedServiceType_Name', type: 'string', header:lang['tip_slujbyi'], width: 220},
				{name: 'MedService_lid_Name', type: 'string', header: lang['slujba'], width: 320},
				{name: 'lab_Address_Address', type: 'string', header:lang['adres'], width: 320}
			],
			title: '',
			toolbar: true
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
				[
					{
						text: '-'
					},
					HelpButton(this, 0),//todo проставить табиндексы
					{
						handler: function()
						{
							this.ownerCt.hide();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCLOSE
					}],
			items:[grid]
		});
		sw.Promed.swMedServiceLinkManageWindow.superclass.initComponent.apply(this, arguments);
		this.grid = this.findById('MedServiceLinkGrid').getGrid();
	}
});