/**
* swHomeVisitBookPrintParamsWindow - Форма уточнения параметров печати книги вызовов врача на дом
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Alexander Kurakin
* @copyright    Copyright (c) 2016 Swan Ltd.
* @version      2016
*/

sw.Promed.swHomeVisitBookPrintParamsWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Параметры формирования',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 450,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	doSave: Ext.emptyFn,
	owner: null,
	shim: false,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swHomeVisitBookPrintParamsWindow',
	
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},
	
	show: function() {
		sw.Promed.swHomeVisitBookPrintParamsWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0]  ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		} else {
			this.ARMType = '';
		}
		
		var bf = this.Form.getForm();
		
		this.SelectLpuRegionCombo.getStore().removeAll();
		if(this.ARMType == 'reg' || this.ARMType == 'regpol' || this.ARMType == 'regpol6'){
			this.SelectLpuRegionCombo.getStore().load({params:{LpuRegionTypeList:Ext.util.JSON.encode(['ter','ped','vop','stom'])}});
		} else if (this.ARMType == 'common' || this.ARMType == 'polka') {
			this.SelectLpuRegionCombo.getStore().load({params:{MedPersonal_id:getGlobalOptions().CurMedPersonal_id}});
		}
		this.buttons[0].show();
		this.center();
	},

	doSave: function (params) {
		if( !params.homeVisit_begDate ) {
			sw.swMsg.alert(lang['oshibka'], 'Не указано начало периода');
			return false;
		}
		if( !params.homeVisit_endDate ) {
			sw.swMsg.alert(lang['oshibka'], 'Не указан конец периода');
			return false;
		}
		if( !getGlobalOptions().lpu_id ) {
			sw.swMsg.alert(lang['oshibka'], 'Для текущего пользователя не указана МО');
			return false;
		}
		var paramLpu = getGlobalOptions().lpu_id;
		var paramMP = (getGlobalOptions().CurMedPersonal_id ? getGlobalOptions().CurMedPersonal_id : false);
		if(this.ARMType == 'reg' || this.ARMType == 'regpol' || this.ARMType == 'regpol6'){
			paramMP = false;
		}
		var paramLpuRegion = (params.LpuRegion_cid ? params.LpuRegion_cid : false);
		var paramBegDate = params.homeVisit_begDate;
		var paramEndDate = params.homeVisit_endDate;
		var prmFntPnt = 1;//1 если печатается первая страница, 0 если не печатается
		var prmBckPnt = (this.ARMType == 'common' || this.ARMType == 'polka' ? 1 : 0);//1 если печатается вторая страница, 0 если не печатается
		var reportFormat = 'pdf';
		switch(getPrintOptions().home_vizit_journal_print_extension) {
			case '1':
				reportFormat = 'pdf';
				break;
			case '2':
				reportFormat = 'doc';
				break;
			case '3':
				reportFormat = 'html';
				break;
			default:
				reportFormat = 'pdf';
				break;
		}
		if(!paramMP){
			var isnullM = '__isnull=';
			paramMP = '';
		} else {
			var isnullM = '';
		}
		if(!paramLpuRegion){
			var isnullR = '__isnull=';
			paramLpuRegion = '';
		} else {
			var isnullR = '';
		}
		if(this.ARMType == 'reg' && getRegionNick() != 'kz') {
			printBirt({
				'Report_FileName': 'pan_HomeVizit_List_reg.rptdesign',
				'Report_Params': '&paramLpu='+paramLpu+'&'+isnullM+'paramMedStaffFact='+paramMP+'&'+isnullR+'paramLpuRegion='+paramLpuRegion+'&paramBegDate='+paramBegDate+'&paramEndDate='+paramEndDate,
				'Report_Format': reportFormat
			});
		} else {
			printBirt({
				'Report_FileName': 'pan_HomeVizit_List.rptdesign',
				'Report_Params': '&paramLpu='+paramLpu+'&'+isnullM+'paramMedStaffFact='+paramMP+'&'+isnullR+'paramLpuRegion='+paramLpuRegion+'&paramBegDate='+paramBegDate+'&paramEndDate='+paramEndDate,
				'Report_Format': reportFormat
			});
		}
	},

	initComponent: function() {
		var wnd = this;

		this.SelectLpuRegionCombo = new sw.Promed.SwBaseRemoteCombo(
		{
			displayField: 'LpuRegion_Name',
			allowBlank: true,
			editable: false,
			enableKeyEvents: true,
			forceSelection: true,
			fieldLabel: lang['uchastok'],
			labelAlign: 'right',
			hiddenName: 'LpuRegion_cid',
			queryDelay: 1,
			lastQuery: '',
			mode: 'remote',
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'LpuRegion_id'
				},
				[
					{name: 'LpuRegion_Name', mapping: 'LpuRegion_Name'},
					{name: 'LpuRegion_id', mapping: 'LpuRegion_id'},
					{name: 'LpuRegion_Descr', mapping: 'LpuRegion_Descr'},
					{name: 'LpuRegionType_id', mapping: 'LpuRegionType_id'},
					{name: 'LpuRegionType_SysNick', mapping: 'LpuRegionType_SysNick'},
					{name: 'LpuRegionType_Name', mapping: 'LpuRegionType_Name'}
				]),
				listeners: {
					'load': function(store) {
						
					}.createDelegate(this)
				},
				url: C_LPUREGION_LIST
			}),
		
			tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegionType_Name} {LpuRegion_Name}</div></tpl>',
			triggerAction: 'all',
			valueField: 'LpuRegion_id',
			width: 300,
			xtype: 'swbaseremotecombo',
			onTrigger2Click: function() {
				this.clearValue();
			},
			trigger2Class: 'x-form-clear-trigger'
		});

		this.Form = new Ext.FormPanel({
			frame: true,
			defaults: {
				labelAlign: 'right'
			},
			layout: 'form',
			labelWidth: 70,
			labelAlign: 'right',
			items: [{
				layout: 'form',
				items: [{
					layout: 'column',
					items:[{
						layout: 'form',
						labelWidth: 70,
						labelAlign: 'left',
						items: [{
							allowBlank: false,
							fieldLabel: 'Период',
							id: 'homeVisit_begDate',
							name: 'homeVisit_begDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 120,
							xtype: 'swdatefield',
							listeners: {
								'change': function (comp,newval) {
									if(this.ARMType == 'reg' || this.ARMType == 'regpol' || this.ARMType == 'regpol6'){
										var LpuRegion_id = this.SelectLpuRegionCombo.getValue();
										var LpuRegion_begDate = Ext.isEmpty(newval) ? '' : Ext.util.Format.date(newval,'Y-m-d');
										var LpuRegion_endDate = Ext.isEmpty(this.findById('homeVisit_endDate').getValue()) ? '' : Ext.util.Format.date(this.findById('homeVisit_endDate').getValue(),'Y-m-d');
										this.SelectLpuRegionCombo.getStore().removeAll();
										this.SelectLpuRegionCombo.getStore().load({
											params:{
												LpuRegionTypeList:Ext.util.JSON.encode(['ter','ped','vop','stom']),
												showCrossedLpuRegions:1,
												LpuRegion_begDate: LpuRegion_begDate,
												LpuRegion_endDate: LpuRegion_endDate
											}
										});
										var index = this.SelectLpuRegionCombo.getStore().findBy(function(rec){
											return (rec.get('LpuRegion_id')==LpuRegion_id);
										});
										if(index == -1){
											this.SelectLpuRegionCombo.clearValue();
										}
									}
								}.createDelegate(this)
							}
						}]
					}, 
					{
						layout: 'form',
						labelWidth: 30,
						items: [{
							allowBlank: false,
							labelSeparator: '',
							fieldLabel: '',
							id: 'homeVisit_endDate',
							name: 'homeVisit_endDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 120,
							xtype: 'swdatefield',
							listeners: {
								'change': function (comp,newval) {
									if(this.ARMType == 'reg' || this.ARMType == 'regpol' || this.ARMType == 'regpol6'){
										var LpuRegion_id = this.SelectLpuRegionCombo.getValue();
										var LpuRegion_endDate = Ext.isEmpty(newval) ? '' : Ext.util.Format.date(newval,'Y-m-d');
										var LpuRegion_begDate = Ext.isEmpty(this.findById('homeVisit_begDate').getValue()) ? '' : Ext.util.Format.date(this.findById('homeVisit_begDate').getValue(),'Y-m-d');
										this.SelectLpuRegionCombo.getStore().removeAll();
										this.SelectLpuRegionCombo.getStore().load({
											params:{
												LpuRegionTypeList:Ext.util.JSON.encode(['ter','ped','vop','op','stom']),
												showCrossedLpuRegions:1,
												LpuRegion_begDate: LpuRegion_begDate,
												LpuRegion_endDate: LpuRegion_endDate
											}
										});
										var index = this.SelectLpuRegionCombo.getStore().findBy(function(rec){
											return (rec.get('LpuRegion_id')==LpuRegion_id);
										});
										if(index == -1){
											this.SelectLpuRegionCombo.clearValue();
										}
									}
								}.createDelegate(this)
							}
						}]
					}]
				}]
			},
			this.SelectLpuRegionCombo]
		});
		
		Ext.apply(this, {
			items: [this.Form],
			buttons: [{
				handler: function(button, event) {
					var bf = this.Form.getForm();
					if( !bf.isValid() ) {
						sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_korrektno']);
						return false;
					}
					var params = bf.getValues();
					
					wnd.doSave(params);
					wnd.hide();
				}.createDelegate(this),
				scope: this,
				iconCls: 'ok16',
				text: 'Печать'
			},
			'-',
			{
				text: lang['otmena'],
				tabIndex: -1,
				tooltip: lang['otmena'],
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}]
		});
		sw.Promed.swHomeVisitBookPrintParamsWindow.superclass.initComponent.apply(this, arguments);
	}
});