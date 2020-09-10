/**
* swEvnNotifyHIVDispEditWindow - Донесение о подтверждении диагноза у ребенка, рожденного ВИЧ-инфицированной матерью (форма N  311/у)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Alexander Permyakov 
* @version      2013/07
*/

sw.Promed.swEvnNotifyHIVDispEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	maximized : true,
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		params.MorbusHIVChem_data = this.MorbusHIVChemFrame.getJsonData();
		params.MorbusHIVSecDiag_data = this.MorbusHIVSecDiagFrame.getJsonData();
		params.MorbusHIVVac_data = this.MorbusHIVVacFrame.getJsonData();
		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Msg);
					}
				}
			},
			success: function(result_form, action) 
			{
				
				showSysMsg(lang['izveschenie_sozdano']);
				win.formStatus = 'edit';
				loadMask.hide();
				var data = {};
				if (typeof action.result == 'object') {
					data = action.result;
					win.hide();
					win.callback(data);
				}
			}
		});
		
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.FormPanel.getForm();
		
		base_form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	openMorbusHIVVacEditWindow: function(action) 
	{
		var viewFrame = this.MorbusHIVVacFrame,
			win_name = 'swMorbusHIVVacEditWindow',
			fields = ['Drug_id','Drug_Name','MorbusHIVVac_setDT'];
		this._openLocalEditWindow(action, viewFrame, win_name, fields);
	},
	openMorbusHIVSecDiagEditWindow: function(action) 
	{
		var viewFrame = this.MorbusHIVSecDiagFrame,
			win_name = 'swMorbusHIVSecDiagEditWindow',
			fields = ['Diag_id','Diag_Name','MorbusHIVSecDiag_setDT'];
		this._openLocalEditWindow(action, viewFrame, win_name, fields);
	},
	openMorbusHIVChemEditWindow: function(action) 
	{
		var viewFrame = this.MorbusHIVChemFrame,
			win_name = 'swMorbusHIVChemEditWindow',
			fields = ['Drug_id','Drug_Name','MorbusHIVChem_Dose','MorbusHIVChem_begDT','MorbusHIVChem_endDT'];
		this._openLocalEditWindow(action, viewFrame, win_name, fields);
	},
	_openLocalEditWindow: function(action, viewFrame, win_name, fields) 
	{
		if(!action || !action.toString().inlist(['add','edit']))
		{
			return false;
		}
		var grid = viewFrame.getGrid(),
			record = grid.getSelectionModel().getSelected(),
			callback,
			formParams = {};
		
		if(action == 'edit' && record && record.data) {
			formParams = record.data;
			callback = function(data){
				for (var i = 0; i < fields.length; i++ ) {
					record.set(fields[i], data[fields[i]]);
				}
				record.commit();
				i = grid.getStore().indexOf(record);
				grid.getSelectionModel().selectRow(i);
				grid.getView().focusRow(i);
				if(typeof viewFrame.onRowSelect == 'function') viewFrame.onRowSelect(grid.getSelectionModel(),i,record);
			};
		} else {
			action = 'add';
			callback = function(data){
				var params = {};
				for (var i = 0; i < fields.length; i++ ) {
					params[fields[i]] = data[fields[i]] || null;
				}
				record = new Ext.data.Record(params);
				grid.getStore().add([record]);
				grid.getStore().commitChanges();
				i = grid.getStore().indexOf(record);
				grid.getSelectionModel().selectRow(i);
				grid.getView().focusRow(i);
				if(typeof viewFrame.onRowSelect == 'function') viewFrame.onRowSelect(grid.getSelectionModel(),i,record);
			};
		}

		getWnd(win_name).show({
			action: action,
			formMode: 'local',
			callback: callback,
			formParams: formParams
		});
	},
	actPanelId: null,
	toogleExpandPanel: function(id) 
	{
		var list_panels = ['MorbusHIVLabPanel','MorbusHIVVacPanel','MorbusHIVSecDiagPanel','MorbusHIVChemPanel'];
		if(this.actPanelId != id && !this[id].collapsed)
		{
			this.actPanelId = id;
			for(var i=0,iid; i<list_panels.length; i++) {
				iid = list_panels[i];
				if(id == iid) continue;
				if(!this[iid].collapsed) this[iid].collapse();
			}
		}
	},
	show: function() 
	{
		sw.Promed.swEvnNotifyHIVDispEditWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
			return false;
		}
		this.focus();
		this.FormPanel.getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.formStatus = 'edit';
		this.EvnNotifyHIVDisp_id = arguments[0].EvnNotifyHIVDisp_id || null;
		this.action = (( this.EvnNotifyHIVDisp_id ) && ( this.EvnNotifyHIVDisp_id > 0 ))?'view':'add';
		this.formMode = (arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]))?arguments[0].formMode:'remote';
		this.owner = arguments[0].owner || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		
		base_form.setValues(arguments[0].formParams);
		this.MorbusHIVLabPanel.collapse();
		this.MorbusHIVLabPanel.expand();
        this.MorbusHIVChemFrame.removeAll(true);
        this.MorbusHIVSecDiagFrame.removeAll(true);
        this.MorbusHIVVacFrame.removeAll(true);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		
		//var lpu_combo = base_form.findField('Lpuifa_id');

		if (this.action != 'add') {
			switch (this.action) 
			{
				case 'view':
					this.setTitle(lang['donesenie_o_podtverjdenii_diagnoza_u_rebenka_rojdennogo_vich-infitsirovannoy_materyu_forma_n_311_u_prosmotr']);
					this.setFieldsDisabled(true);
					break;
			}
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					current_window.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					EvnNotifyHIVDisp_id: this.EvnNotifyHIVDisp_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(result[0]);
					base_form.findField('MedPersonal_id').getStore().load({
						callback: function()
						{
							base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
							base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
						}.createDelegate(this)
					});
					var params = { EvnNotifyBase_id: base_form.findField('EvnNotifyHIVDisp_id').getValue() };
					params.start = 0;
					params.limit = 100;
					this.MorbusHIVChemFrame.loadData({globalFilters:params});
					this.MorbusHIVSecDiagFrame.loadData({globalFilters:params});
					this.MorbusHIVVacFrame.loadData({globalFilters:params});

					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=EvnNotifyHIVDisp&m=load'
			});			
		} else {
			this.setTitle(lang['donesenie_o_podtverjdenii_diagnoza_u_rebenka_rojdennogo_vich-infitsirovannoy_materyu_forma_n_311_u_dobavlenie']);
			this.setFieldsDisabled(false);
			base_form.findField('MedPersonal_id').getStore().load({
				callback: function()
				{
					base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
					base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
				}
			});
			base_form.findField('EvnNotifyHIVDisp_setDT').setValue(getGlobalOptions().date);
			loadMask.hide();			
		}		
	},	
	initComponent: function() 
	{
		this.MorbusHIVLabPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			autoHeight: true,
			layout: 'fit',
			listeners: {
				'expand': function(panel) {
					this.toogleExpandPanel('MorbusHIVLabPanel');
					panel.doLayout();
				}.createDelegate(this)
			},
			//style: 'margin-bottom: 0.5em;',
			title:lang['laboratornaya_diagnostika_vich-infektsii'],
			items: [{
				border: true,
				autoHeight: true,
				layout: 'form',
				items: [{
					name: 'MorbusHIVLab_id',
					xtype: 'hidden'
				}, {
					autoHeight: true,
					title: lang['immunofermentnyiy_analiz'],
					xtype: 'fieldset',
					labelWidth: 80,
					items: [{
						name: 'Lpuifa_id',
						xtype: 'hidden'
						/*
						fieldLabel: lang['uchrejdenie_pervichno_vyiyavivshee_polojitelnyiy_rezultat_v_ifa'],
						allowBlank: true,
						width: 300,
						autoLoad: false,
						hiddenName: 'Lpuifa_id',
						xtype: 'swlpulocalcombo'
						*/
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['data'],
								name: 'MorbusHIVLab_IFADT',
								xtype: 'swdatefield',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['rezultat'],
								name: 'MorbusHIVLab_IFAResult',
								width: 200,
								maxLength: 30,
								xtype: 'textfield'
							}]
						}]
					}]
				}, {
					autoHeight: true,
					title: lang['immunnyiy_blotting'],
					xtype: 'fieldset',
					labelWidth: 120,
					items: [{
						name: 'MorbusHIVLab_BlotNum',
						xtype: 'hidden'
						/*
						fieldLabel: lang['№_serii'],
						name: 'MorbusHIVLab_BlotNum',
						allowBlank:true,
						width: 300,
						maxLength: 64,
						xtype: 'textfield'
						*/
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
                            labelWidth: 110,
							items: [{
								fieldLabel: lang['data'],
								name: 'MorbusHIVLab_BlotDT',
								xtype: 'swdatefield',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                            }, {
                                fieldLabel: lang['№_immunoblota'],
                                name: 'MorbusHIV_NumImmun',
                                allowBlank:true,
                                width: 85,
                                maxLength: 5,
                                xtype: 'numberfield',
                                allowDecimals: false,
                                allowNegative: false,
                                plugins: [new Ext.ux.InputTextMask('99999', false)]
							}]
						}, {
							border: false,
							layout: 'form',
                            labelWidth: 140,
							items: [{
								fieldLabel: lang['rezultat'],
								name: 'MorbusHIVLab_BlotResult',
								allowBlank:true,
								width: 200,
								maxLength: 100,
								xtype: 'textfield'
                            }, {
                                fieldLabel: lang['tip_test-sistemyi'],
                                name: 'MorbusHIVLab_TestSystem',
                                allowBlank:true,
                                width: 200,
                                maxLength: 64,
                                xtype: 'textfield'
							}]
						}]
					}]
				}, {
					autoHeight: true,
					title: lang['polimeraznaya_tsepnaya_reaktsiya'],
					xtype: 'fieldset',
					labelWidth: 80,
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['data'],
								name: 'MorbusHIVLab_PCRDT',
								xtype: 'swdatefield',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['rezultat'],
								name: 'MorbusHIVLab_PCRResult',
								width: 200,
								maxLength: 30,
								xtype: 'textfield'
							}]
						}]
					}]
				}]
			}]
		});

		this.MorbusHIVVacFrame = new sw.Promed.ViewFrame({
			object: 'MorbusHIVVac',
			editformclassname: 'swMorbusHIVVacEditWindow',
			dataUrl: '/?c=MorbusHIV&m=loadMorbusHIVVac',
			toolbar: true,
			border: true,
			height: 200,
			autoScroll: true,
			autoLoadData: false,
			stringfields:[
				{name: 'MorbusHIVVac_id', type: 'int', hidden: true, key: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true, isparams: true},
				{name: 'Drug_id', type: 'int', hidden: true},
				{name: 'MorbusHIVVac_setDT',  type: 'date', header: lang['data'], width: 100},
				{name: 'Drug_Name',  type: 'string', header: lang['nazvanie'], autoexpand: true, autoExpandMin: 200}
			],
			getJsonData: function(){
				var out_list = new Array()
					,store = this.getGrid().getStore()
					,item;
				store.clearFilter();
				store.each(function(record) {
					item = {
						MorbusHIVVac_id: record.data.MorbusHIVVac_id
						,Drug_id: record.data.Drug_id
						,MorbusHIVVac_setDT: (record.data.MorbusHIVVac_setDT)?record.data.MorbusHIVVac_setDT.format('Y-m-d'):null
					};
					out_list.push(item);
				});
				return out_list.length > 0 ? Ext.util.JSON.encode(out_list) : '';
			},
			actions: [
				{name:'action_add', handler: function() { this.openMorbusHIVVacEditWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusHIVVacEditWindow('edit'); }.createDelegate(this)},
				{name:'action_view', hidden: true},
				{name:'action_delete', handler: function() {
					var grid = this.MorbusHIVVacFrame.getGrid();
					var rec = grid.getSelectionModel().getSelected();
					if(rec) grid.getStore().remove(rec);
				}.createDelegate(this)},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],
			onRowSelect: function(sm,i,record){
				this.setActionDisabled('action_edit',!record);
				this.setActionDisabled('action_delete',!record);
			},
			paging: false,
			focusOnFirstLoad: false
		});
		
		this.MorbusHIVVacPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			autoHeight: true,
			layout: 'fit',
			listeners: {
				'expand': function(panel) {
					this.toogleExpandPanel('MorbusHIVVacPanel');
					panel.doLayout();
				}.createDelegate(this)
			},
			//style: 'margin-bottom: 0.5em;',
			title:lang['vaktsinatsiya'],
			items: [
				this.MorbusHIVVacFrame
			]
		});

		this.MorbusHIVSecDiagFrame = new sw.Promed.ViewFrame({
			object: 'MorbusHIVSecDiag',
			editformclassname: 'swMorbusHIVSecDiagEditWindow',
			dataUrl: '/?c=MorbusHIV&m=loadMorbusHIVSecDiag',
			toolbar: true,
			border: true,
			height: 200,
			autoScroll: true,
			autoLoadData: false,
			stringfields:[
				{name: 'MorbusHIVSecDiag_id', type: 'int', hidden: true, key: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true, isparams: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'MorbusHIVSecDiag_setDT',  type: 'date', header: lang['data'], width: 100},
				{name: 'Diag_Name',  type: 'string', header: lang['zabolevaniya'], autoexpand: true, autoExpandMin: 200}
			],
			getJsonData: function(){
				var out_list = new Array()
					,store = this.getGrid().getStore()
					,item;
				store.clearFilter();
				store.each(function(record) {
					item = {
						MorbusHIVSecDiag_id: record.data.MorbusHIVSecDiag_id
						,Diag_id: record.data.Diag_id
						,MorbusHIVSecDiag_setDT: (record.data.MorbusHIVSecDiag_setDT)?record.data.MorbusHIVSecDiag_setDT.format('Y-m-d'):null
					};
					out_list.push(item);
				});
				return out_list.length > 0 ? Ext.util.JSON.encode(out_list) : '';
			},
			actions: [
				{name:'action_add', handler: function() { this.openMorbusHIVSecDiagEditWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusHIVSecDiagEditWindow('edit'); }.createDelegate(this)},
				{name:'action_view', hidden: true},
				{name:'action_delete', handler: function() {
					var grid = this.MorbusHIVSecDiagFrame.getGrid();
					var rec = grid.getSelectionModel().getSelected();
					if(rec) grid.getStore().remove(rec);
				}.createDelegate(this)},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],
			onRowSelect: function(sm,i,record){
				this.setActionDisabled('action_edit',!record);
				this.setActionDisabled('action_delete',!record);
			},
			paging: false,
			focusOnFirstLoad: false
		});
		
		this.MorbusHIVSecDiagPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			autoHeight: true,
			layout: 'fit',
			listeners: {
				'expand': function(panel) {
					this.toogleExpandPanel('MorbusHIVSecDiagPanel');
					panel.doLayout();
				}.createDelegate(this)
			},
			//style: 'margin-bottom: 0.5em;',
			title:lang['vtorichnyie_zabolevaniya_i_opportunisticheskie_infektsii'],
			items: [
				this.MorbusHIVSecDiagFrame
			]
		});

		this.MorbusHIVChemFrame = new sw.Promed.ViewFrame({
			id: 'HIVD_MorbusHIVChemFrame',
			object: 'MorbusHIVChem',
			editformclassname: 'swMorbusHIVChemEditWindow',
			dataUrl: '/?c=MorbusHIV&m=loadMorbusHIVChem',
			toolbar: true,
			border: true,
			height: 200,
			autoScroll: true,
			autoLoadData: false,
			stringfields:[
				{name: 'MorbusHIVChem_id', type: 'int', hidden: true, key: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true, isparams: true},
				{name: 'Drug_id', type: 'int', hidden: true},
				{name: 'Drug_Name',  type: 'string', header: lang['preparat'], autoexpand: true, autoExpandMin: 200},
				{name: 'MorbusHIVChem_Dose',  type: 'string', header: lang['doza'], width: 200},
				{name: 'MorbusHIVChem_begDT',  type: 'date', header: lang['data_nachala'], width: 100},
				{name: 'MorbusHIVChem_endDT',  type: 'date', header: lang['data_okonchaniya'], width: 100}
			],
			getJsonData: function(){
				var out_list = new Array()
					,store = this.getGrid().getStore()
					,item;
				store.clearFilter();
				store.each(function(record) {
					item = {
						MorbusHIVChem_id: record.data.MorbusHIVChem_id
						,Drug_id: record.data.Drug_id
						,MorbusHIVChem_Dose: record.data.MorbusHIVChem_Dose
						,MorbusHIVChem_begDT: (record.data.MorbusHIVChem_begDT)?record.data.MorbusHIVChem_begDT.format('Y-m-d'):null
						,MorbusHIVChem_endDT: (record.data.MorbusHIVChem_endDT)?record.data.MorbusHIVChem_endDT.format('Y-m-d'):null
					};
					out_list.push(item);
				});
				return out_list.length > 0 ? Ext.util.JSON.encode(out_list) : '';
			},
			actions: [
				{name:'action_add', handler: function() { this.openMorbusHIVChemEditWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusHIVChemEditWindow('edit'); }.createDelegate(this)},
				{name:'action_view', hidden: true},
				{name:'action_delete', handler: function() {
					var grid = this.MorbusHIVChemFrame.getGrid();
					var rec = grid.getSelectionModel().getSelected();
					if(rec) grid.getStore().remove(rec);
				}.createDelegate(this)},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],
			onRowSelect: function(sm,i,record){
				this.setActionDisabled('action_edit',!record);
				this.setActionDisabled('action_delete',!record);
			},
			paging: false,
			focusOnFirstLoad: false
		});
		
		this.MorbusHIVChemPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			autoHeight: true,
			layout: 'fit',
			listeners: {
				'expand': function(panel) {
					this.toogleExpandPanel('MorbusHIVChemPanel');
					panel.doLayout();
				}.createDelegate(this)
			},
			style: 'margin-bottom: 0.5em;',
			title:lang['protivoretrovirusnaya_terapiya'],
			items: [
				this.MorbusHIVChemFrame
			]
		});
		
		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'center',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 220,
			autoScroll:true,
			url:'/?c=EvnNotifyHIVDisp&m=save',
			items: 
			[{
				region: 'center',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyHIVDisp_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyHIVDisp_pid',
					xtype: 'hidden'
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'Person_mid',
					xtype: 'hidden'
				}, {
					width: 300,
					fieldLabel: lang['mat'],
					allowBlank: false,
					xtype: 'swpersoncomboex',
					onSelectPerson: function(data) {
						var base_form = this.FormPanel.getForm();
						base_form.findField('Person_mid').setValue(data.Person_id);
					}.createDelegate(this), 
					hiddenName: 'mother_fio'
				}, {
					width: 300,
					fieldLabel: lang['rebenok'],
					allowBlank: false,
					xtype: 'swpersoncomboex',
					onSelectPerson: function(data) {
						var base_form = this.FormPanel.getForm();
						base_form.findField('Server_id').setValue(data.Server_id);
						base_form.findField('Person_id').setValue(data.Person_id);
						base_form.findField('PersonEvn_id').setValue(data.PersonEvn_id);
					}.createDelegate(this), 
					hiddenName: 'baby_fio'
				}, {
					fieldLabel: lang['otkaznoy_rebenok'],
					xtype: 'swyesnocombo',
					width: 80,
					hiddenName: 'EvnNotifyHIVDisp_IsRefuse'
				}, {
					fieldLabel: lang['rebenok'],
					width: 300,
					comboSubject: 'HIVChildType',
					hiddenName: 'HIVChildType_id',
					autoLoad: true,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: lang['mesto_prebyivaniya'],
					name: 'EvnNotifyHIVDisp_Place',
					width: 150,
					maxLength: 30,
					xtype: 'textfield'
				}, {
					fieldLabel: lang['data_ustanovleniya_diagnoza_vich-infektsii'],
					name: 'EvnNotifyHIVDisp_DiagDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['polnyiy_klinicheskiy_diagnoz'],
					name: 'EvnNotifyHIVDisp_Diag',
					width: 150,
					maxLength: 50,
					xtype: 'textfield'
				}, {
					autoHeight: true,
					title: lang['immunnyiy_status_cd4_t-limfotsityi'],
					xtype: 'fieldset',
					labelWidth: 120,
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['kolichestvo_mm'],
								name: 'EvnNotifyHIVDisp_CountCD4',
								xtype: 'numberfield',
								maxValue: 99,
								width: 70,
								allowDecimals: false,
								allowNegative: false
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['%_soderjaniya'],
								name: 'EvnNotifyHIVDisp_PartCD4',
								xtype: 'numberfield',
								maxValue: 99.99,
								width: 70,
								allowDecimals: true,
								allowNegative: false
							}]
						}]
					}]
				},
				this.MorbusHIVLabPanel,
				this.MorbusHIVVacPanel,
				this.MorbusHIVSecDiagPanel,
				this.MorbusHIVChemPanel
				, {
					fieldLabel: lang['data_zapolneniya_izvescheniya'],
					name: 'EvnNotifyHIVDisp_setDT',
					allowBlank: false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['vrach_zapolnivshiy_izveschenie'],
					hiddenName: 'MedPersonal_id',
					listWidth: 450,
					width: 350,
					xtype: 'swmedpersonalcombo',
					anchor: false
				}]
			}]
		});
		Ext.apply(this, 
		{	
			buttons: 
			[{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swEvnNotifyHIVDispEditWindow.superclass.initComponent.apply(this, arguments);
	}
});