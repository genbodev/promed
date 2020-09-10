/**
* swEvnNotifyHIVBornEditWindow - Извещение о новорожденном, рожденном ВИЧ-инфицированной матерью (форма N  309/у)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Alexander Permyakov 
* @version      2012/12
*
* @input data: action - действие (add, view)
*			   callback
*              EvnNotifyHIVBorn_id - ID для просмотра
*              formParams.Person_mid - Мать из формы N 313/у
*			   formParams.mother_fio - ФИО матери из формы N 313/у
*              formParams.EvnNotifyHIVBorn_HIVDT - Дата установления ВИЧ-инфицирования матери из формы N 313/у
*              formParams.HIVPregPathTransType_id - Путь ВИЧ-инфицирования матери из формы N 313/у
*
*
* Использует: окно редактирования (swMorbusHIVChemEditWindow)
*             окно редактирования (swMorbusHIVChemPregEditWindow)
*/

sw.Promed.swEvnNotifyHIVBornEditWindow = Ext.extend(sw.Promed.BaseForm, 
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

		params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		params.MorbusHIVChem_data = this.MorbusHIVChemFrame.getJsonData();
		params.MorbusHIVChemPreg_data = this.MorbusHIVChemPregFrame.getJsonData();
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
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
	//возвращает значения store
	getDataFromStore: function(store){ 
		var data = new Array();
		store.clearFilter();
		store.each(function(record) {
			data.push(record.data);
		});
		return data;
	},
	openMorbusHIVChemPregEditWindow: function(action) 
	{
		var viewFrame = this.MorbusHIVChemPregFrame,
			win_name = 'swMorbusHIVChemPregEditWindow',
			fields = ['Drug_id','Drug_Name','MorbusHIVChemPreg_Dose','HIVPregnancyTermType_id','HIVPregnancyTermType_Name'];
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
	show: function() 
	{
		sw.Promed.swEvnNotifyHIVBornEditWindow.superclass.show.apply(this, arguments);
		
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
		this.EvnNotifyHIVBorn_id = arguments[0].EvnNotifyHIVBorn_id || null;
		this.action = (( this.EvnNotifyHIVBorn_id ) && ( this.EvnNotifyHIVBorn_id > 0 ))?'view':'add';
		this.formMode = (arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]))?arguments[0].formMode:'remote';
		this.owner = arguments[0].owner || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		
		base_form.setValues(arguments[0].formParams);
		
		
		var lpub_combo = base_form.findField('Lpu_rid');
		var lpuf_combo = base_form.findField('Lpu_fid');
		var baby_combo = base_form.findField('baby_fio');
		var mother_combo = base_form.findField('mother_fio');
		//log([lpub_combo,lpuf_combo,baby_combo,mother_combo]); 
		if ( this.kinderPanel.collapsed ) this.kinderPanel.expand();
		if ( !this.mutterPanel.collapsed ) this.mutterPanel.collapse();
        this.MorbusHIVChemFrame.removeAll(true);
        this.MorbusHIVChemPregFrame.removeAll(true);
		
		if (this.action != 'add') {
			switch (this.action) 
			{
				case 'view':
					this.setTitle(lang['izveschenie_o_novorojdennom_rojdennom_vich-infitsirovannoy_materyu_forma_n_309_u_prosmotr']);
					this.setFieldsDisabled(true);
					break;
			}
			var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
			loadMask.show();
			Ext.Ajax.request({
				failure:function (response, options) {
					loadMask.hide();
					current_window.hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					EvnNotifyHIVBorn_id: this.EvnNotifyHIVBorn_id
				},
				success:function (response, options) {
					loadMask.hide();
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.setValues(result[0]);
					base_form.findField('MedPersonal_id').getStore().load({
						callback: function()
						{
							base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
							base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
						}.createDelegate(this)
					});
					var params = { EvnNotifyBase_id: base_form.findField('EvnNotifyHIVBorn_id').getValue() };
					params.start = 0;
					params.limit = 100;
					this.MorbusHIVChemFrame.loadData({globalFilters:params});
					this.MorbusHIVChemPregFrame.loadData({globalFilters:params});
					
					//mother_combo.setRawValue(result[0].mother_fio);
					//baby_combo.setRawValue(result[0].baby_fio);

					lpub_combo.getStore().load({
						callback: function () {
							if ( lpub_combo.getStore().getCount() > 0 ) {
								lpub_combo.setValue(lpub_combo.getValue());
							}
						}
					});
					lpuf_combo.getStore().load({
						callback: function () {
							if ( lpuf_combo.getStore().getCount() > 0 ) {
								lpuf_combo.setValue(lpuf_combo.getValue());
							}
						}
					});
				}.createDelegate(this),
				url:'/?c=EvnNotifyHIVBorn&m=load'
			});			
		} else {			
			this.setTitle(lang['izveschenie_o_novorojdennom_rojdennom_vich-infitsirovannoy_materyu_forma_n_309_u_dobavlenie']);
			this.setFieldsDisabled(false);
			base_form.findField('MedPersonal_id').getStore().load({
				callback: function()
				{
					base_form.findField('MedPersonal_id').setValue(base_form.findField('MedPersonal_id').getValue());
					base_form.findField('MedPersonal_id').fireEvent('change', base_form.findField('MedPersonal_id'), base_form.findField('MedPersonal_id').getValue());
				}
			});
			if(arguments[0].formParams && arguments[0].formParams.Person_mid && arguments[0].formParams.mother_fio)
			{
				//mother_combo.setValue(arguments[0].formParams.Person_mid);
				//mother_combo.setRawValue(arguments[0].formParams.mother_fio);
				mother_combo.setDisabled(true);
			}
			/*
			lpub_combo.getStore().load({
				callback: function () {
					if ( lpub_combo.getStore().getCount() > 0 ) {
						//lpub_combo.setValue(getGlobalOptions().lpu_id);
					}
				}
			});
			*/
		}		
	},	
	initComponent: function() 
	{
		this.MorbusHIVChemFrame = new sw.Promed.ViewFrame({
			title:lang['provedenie_himioprofilaktiki_vich-infektsii_rebenku'],
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

		this.kinderPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			autoHeight: true,
			layout: 'fit',
			listeners: {
				'expand': function(panel) {
					if ( !this.mutterPanel.collapsed ) this.mutterPanel.collapse();
					panel.doLayout();
				}.createDelegate(this),
				'collapse': function(panel) {
					if ( this.mutterPanel.collapsed ) this.mutterPanel.expand();
				}.createDelegate(this)
			},
			//style: 'margin-bottom: 0.5em;',
			title: lang['rebenok'],
			items: [{
				border: true,
				autoHeight: true,
				layout: 'form',
				items: [{
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
					fieldLabel: lang['massa_rebenka_pri_rojdenii_g'],
					name: 'EvnNotifyHIVBorn_ChildMass',
					xtype: 'numberfield',
					width: 70,
					allowDecimals: false,
					allowNegative: false
				}, {
					fieldLabel: lang['rost_rebenka_pri_rojdenii_sm'],
					name: 'EvnNotifyHIVBorn_ChildHeight',
					xtype: 'numberfield',
					width: 70,
					allowDecimals: false,
					allowNegative: false
				}, {
					fieldLabel: lang['otkaznoy_rebenok'],
					xtype: 'swyesnocombo',
					width: 80,
					hiddenName: 'EvnNotifyHIVBorn_IsRefuse'
				}, {
					fieldLabel: 'ЛПУ рождения',//По умолчанию ЛПУ, указанное в свидетельстве о рождении.
					width: 300,
					autoLoad: false,
					allowBlank: true,
					hiddenName: 'Lpu_rid',
					xtype: 'swlpulocalcombo'
				}, {
					fieldLabel: lang['grudnoe_vskarmlivanie_rebenka'],
					xtype: 'swyesnocombo',
					width: 80,
					hiddenName: 'EvnNotifyHIVBorn_IsBreastFeed'
				}, {
					fieldLabel: lang['klinicheskiy_diagnoz_rebenka'],
					name: 'EvnNotifyHIVBorn_Diag',
					width: 300,
					maxLength: 100,
					xtype: 'textfield'
				},this.MorbusHIVChemFrame]
			}]
		});
		
		this.MorbusHIVChemPregFrame = new sw.Promed.ViewFrame({
			title:lang['provedenie_perinatalnoy_profilaktiki_vich'],
			object: 'MorbusHIVChemPreg',
			editformclassname: 'swMorbusHIVChemPregEditWindow',
			dataUrl: '/?c=MorbusHIV&m=loadMorbusHIVChemPreg',
			height: 200,
			autoScroll: true,
			autoLoadData: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 200,
			stringfields:[
				{name: 'MorbusHIVChemPreg_id', type: 'int', hidden: true, key: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true, isparams: true},
				{name: 'Drug_id', type: 'int', hidden: true},
				{name: 'HIVPregnancyTermType_id', type: 'int', hidden: true},
				{name: 'Drug_Name',  type: 'string', header: lang['preparat'], id: 'autoexpand'},
				{name: 'MorbusHIVChemPreg_Dose',  type: 'string', header: lang['doza'], width: 150},
				{name: 'HIVPregnancyTermType_Name',  type: 'string', header: lang['period'], width: 250}
			],
			getJsonData: function(){
				var out_list = new Array()
					,store = this.getGrid().getStore()
					,item;
				store.clearFilter();
				store.each(function(record) {
					item = {
						MorbusHIVChemPreg_id: record.data.MorbusHIVChemPreg_id
						,Drug_id: record.data.Drug_id
						,HIVPregnancyTermType_id: record.data.HIVPregnancyTermType_id
					};
					out_list.push(item);
				});
				return out_list.length > 0 ? Ext.util.JSON.encode(out_list) : '';
			},
			actions: [
				{name:'action_add', handler: function() { this.openMorbusHIVChemPregEditWindow('add'); }.createDelegate(this)},
				{name:'action_edit', handler: function() { this.openMorbusHIVChemPregEditWindow('edit'); }.createDelegate(this)},
				{name:'action_view', hidden: true},
				{name:'action_delete'},
				{name:'action_refresh', hidden: true},
				{name:'action_print', hidden: true}
			],
			paging: false,
			focusOnFirstLoad: false,
			onDblClick: function() {
				this.openMorbusHIVChemPregEditWindow('edit');
			}.createDelegate(this),
			onEnter: function() {
				this.openMorbusHIVChemPregEditWindow('edit');
			}.createDelegate(this)
		});

		this.mutterPanel = new sw.Promed.Panel({
			border: true,
			collapsible: true,
			autoHeight: true,
			layout: 'fit',
			//height: 390,
			//layout: 'border',
			listeners: {
				'expand': function(panel) {
					if ( !this.kinderPanel.collapsed ) this.kinderPanel.collapse();
					panel.doLayout();
				}.createDelegate(this),
				'collapse': function(panel) {
					if ( this.kinderPanel.collapsed ) this.kinderPanel.expand();
				}.createDelegate(this)
			},
			style: 'margin-bottom: 0.5em;',
			title: lang['mat'],
			items: [{
				border: true,
				autoHeight: true,
				layout: 'form',
				items: [{
					width: 300,
					fieldLabel: lang['mat'],
					xtype: 'swpersoncomboex',
					onSelectPerson: function(data) {
						var base_form = this.FormPanel.getForm();
						base_form.findField('Person_mid').setValue(data.Person_id);
					}.createDelegate(this), 
					hiddenName: 'mother_fio'
				}, {
					fieldLabel: lang['pervoe_obraschenie_po_povodu_beremennosti'],
					name: 'EvnNotifyHIVBorn_FirstPregDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['lpu_obrascheniya'],
					width: 300,
					autoLoad: false,
					allowBlank: true,
					hiddenName: 'Lpu_fid',
					xtype: 'swlpulocalcombo'
				}, {
					fieldLabel: lang['sostoyala_na_uchete_po_beremennosti_v_jenskoy_konsultatsii'],
					xtype: 'swyesnocombo',
					width: 80,
					hiddenName: 'isRegPregnancy'
				}, {
					fieldLabel: lang['srok_postanovki_na_uchet_v_jenskoy_konsultatsii'],
					width: 300,
					comboSubject: 'HIVRegPregnancyType',
					hiddenName: 'HIVRegPregnancyType_id',
					autoLoad: true,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: lang['data_ustanovleniya_vich-infitsirovaniya'],
					name: 'EvnNotifyHIVBorn_HIVDT',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['put_vich-infitsirovaniya'],
					width: 300,
					comboSubject: 'HIVPregPathTransType',
					hiddenName: 'HIVPregPathTransType_id',
					autoLoad: true,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: lang['rodorazreshenie_v_srok_beremennosti_v_nedelyah'],
					name: 'EvnNotifyHIVBorn_Srok',
					xtype: 'numberfield',
					width: 70,
					allowDecimals: false,
					allowNegative: false
				}, {
					fieldLabel: lang['kesarevo_sechenie'],
					xtype: 'swyesnocombo',
					width: 80,
					hiddenName: 'EvnNotifyHIVBorn_IsCes'
				}
				,this.MorbusHIVChemPregFrame]
			}]
		});

		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			layout: 'form',
			region: 'center',
			//bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 220,
			autoScroll:true,
			url:'/?c=EvnNotifyHIVBorn&m=save',
			items: 
			[{
				region: 'center',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'EvnNotifyHIVBorn_id',
					xtype: 'hidden'
				}, {
					name: 'EvnNotifyHIVBorn_pid',
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
				},
				this.kinderPanel,
				this.mutterPanel,
				{
					fieldLabel: lang['data_zapolneniya_izvescheniya'],
					name: 'EvnNotifyHIVBorn_setDT',
					allowBlank: false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					changeDisabled: false,
					disabled: true,
					fieldLabel: lang['vrach_zapolnivshiy_izveschenie'],
					hiddenName: 'MedPersonal_id',
					listWidth: 750,
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
		sw.Promed.swEvnNotifyHIVBornEditWindow.superclass.initComponent.apply(this, arguments);
	}
});