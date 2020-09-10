/**
* swMedSvidDeathPrintWindow - окно печати свидетельства о смерти. (префикс MSDPW)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Alexander Kurakin (a.kurakin@swan.perm.ru)
* @version      0.001-08.12.2015
*/

sw.Promed.swMedSvidDeathPrintWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	draggable: true,
	width: 450,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['pechat'],
	//label: 'Напечатать свидетельство?',
	id: 'MedSvidDeathPrintWindow',
	
	initComponent: function() {

		var _this = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.onSelect();
				}.createDelegate(this),
				iconCls: 'ok16',
				id: 'MSDPW_SelectButton',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: lang['napechatat']
			}, {
				text: '-'
			},
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'MSDPW_CloseButton',
				onShiftTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.buttons[0].focus();
				}.createDelegate(this),
				text: BTN_FRMCLOSE
			}],
			items: [
				
				new Ext.form.FormPanel
				({
					id: 'MSDPW_MedSvidDeathPrintTypePanel',
					style : 'padding: 3px',
					autoheight: true,
					region: 'center',
					layout : 'form',
					border : false,
					frame : true,
						items: [
						new Ext.Panel({
					        title: '',
					        collapsible:false,
					        autowidth: true,
					        width:420,
					        style: 'text-align:center;font-size:18px;',
					        html: lang['napechatat_svidetelstvo']
				    	}),{
	                    	fieldLabel: '',
	                    	labelSeparator: '',
							boxLabel: lang['dvuhstoronnyaya_pechat'],
							checked: false,
							disabled: false,
							hideLabel: false,
							name: 'ParamDbl',
							vfield: 'checked',
							xtype: 'checkbox',
							height: 25
					}]
				})
			]
		});
		sw.Promed.swMedSvidDeathPrintWindow.superclass.initComponent.apply(this, arguments);
	},
	minWidth: 300,
	onSelect: function() {

		var form = this.findById("MSDPW_MedSvidDeathPrintTypePanel").getForm();
		var ParamDblPrint = form.findField("ParamDbl").getValue();

		var DeathSvidReport = 'DeathSvid';
		var svid_id = this.svid_id;

		if(this.pnt === true){
			DeathSvidReport = 'PntDeathSvid';
		}
		if (this.DeathSvid_IsDuplicate === true) {
			DeathSvidReport = 'DeathSvid_Dublikat';
			if(this.pnt === true){
				DeathSvidReport = 'PntDeathSvid_Dublikat';
			}
		}
		
		if(ParamDblPrint === true){
			DeathSvidReport += '_dbl_pnt';
			if(this.pnt === true){
				printBirt({
					'Report_FileName': DeathSvidReport+'.rptdesign',
					'Report_Params': '&paramPntDeathSvid=' + svid_id,
					'Report_Format': 'pdf'
				});
			} else {
				printBirt({
					'Report_FileName': DeathSvidReport+'.rptdesign',
					'Report_Params': '&paramDeathSvid=' + svid_id,
					'Report_Format': 'pdf'
				});
			}
			this.hide();
		} else {
			
			if(this.pnt === true){
				printBirt({
					'Report_FileName': DeathSvidReport+'.rptdesign',
					'Report_Params': '&paramPntDeathSvid=' + svid_id,
					'Report_Format': 'pdf'
				});
				printBirt({
					'Report_FileName': 'PntDeathSvid_Oborot.rptdesign',
					'Report_Params': '&paramPntDeathSvid=' + svid_id,
					'Report_Format': 'pdf'
				});
			} else {
				printBirt({
					'Report_FileName': DeathSvidReport+'.rptdesign',
					'Report_Params': '&paramDeathSvid=' + svid_id,
					'Report_Format': 'pdf'
				});
				printBirt({
					'Report_FileName': 'DeathSvid_Oborot.rptdesign',
					'Report_Params': '&paramDeathSvid=' + svid_id,
					'Report_Format': 'pdf'
				});
			}
			this.hide();
		}
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	show: function() {
		sw.Promed.swMedSvidDeathPrintWindow.superclass.show.apply(this, arguments);

		this.center();
		var form = this.findById("MSDPW_MedSvidDeathPrintTypePanel").getForm();
		form.reset();
		this.DeathSvid_IsDuplicate = false;
		this.svid_id = false;
		this.pnt = false;
		if (arguments[0].DeathSvid_IsDuplicate && arguments[0].DeathSvid_IsDuplicate == 2 ) {
			this.DeathSvid_IsDuplicate = true;
		}
		if (arguments[0].svid_id) {
			this.svid_id = arguments[0].svid_id;
		}
		if(arguments[0].pnt){
			this.pnt = arguments[0].pnt;
		}
	}
});