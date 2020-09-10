/**
* swMorbusHepatitisCureWindow - Лечение.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      24.05.2012
*/

sw.Promed.swMorbusHepatitisCureWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	modal: true,
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
		params.action = win.action;
		params.MorbusHepatitisCureEffMonitoring = this.collectGridData('MorbusHepatitisCureEffMonitoring');

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

		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				win.callback();
				win.hide();
			}
		});
		
	},
    collectGridData:function (gridName) {
        var result = '';
        var grid = this.findById('MHCW_' + gridName).getGrid();
        grid.getStore().clearFilter();
        if (grid.getStore().getCount() > 0) {
            if ((grid.getStore().getCount() == 1) && ((grid.getStore().getAt(0).data.RecordStatus_Code == undefined))) {
                return '';
            }
            var gridData = getStoreRecords(grid.getStore(), {convertDateFields:true});
            result = Ext.util.JSON.encode(gridData);
        }
        return result;
    },
	openWindow: function(gridName, action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('sw'+gridName+'Window').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_uje_otkryito']);
			return false;
		}

		var grid = this.findById('MHCW_'+gridName).getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			
			if (!data || !data.BaseData) {
				return false;
			}
			
			data.BaseData.RecordStatus_Code = 0;

			// Обновить запись в grid
			var record = grid.getStore().getById(data.BaseData[gridName+'_id']);

			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.BaseData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.BaseData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get(gridName+'_id')) {
					grid.getStore().removeAll();
				}

				data.BaseData[gridName+'_id'] = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.BaseData ], true);
			}
		}
		params.formMode = 'local';
		params.formParams = new Object();

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.formParams = selected_record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('sw'+gridName+'Window').show(params);
		
	},
	deleteGridSelectedRecord: function(gridId, idField) {
		var grid = this.findById(gridId).getGrid();
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					if (!grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField)) {
						return false;
					}
					switch (Number(record.get('RecordStatus_Code'))) {
						case 0:
							grid.getStore().remove(record);
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								if (Number(rec.get('RecordStatus_Code')) == 3) {
									return false;
								}
								else {
									return true;
								}
							});
							break;
					}
				}
				if (grid.getStore().getCount() > 0) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_etu_zapis'],
			title: lang['vopros']
		});
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.findById('MainForm').items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.findById('MHCW_MorbusHepatitisCureEffMonitoring').setReadOnly(d);
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swMorbusHepatitisCureWindow.superclass.show.apply(this, arguments);
		
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
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		
		var grid = this.findById('MHCW_MorbusHepatitisCureEffMonitoring').getGrid(); 
		grid.getStore().removeAll();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if (arguments[0].MorbusHepatitisCure_id) 
			this.MorbusHepatitisCure_id = arguments[0].MorbusHepatitisCure_id;
		else 
			this.MorbusHepatitisCure_id = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}	
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) 
		{
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.MorbusHepatitisCure_id ) && ( this.MorbusHepatitisCure_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['lechenie_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['lechenie_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['lechenie_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		

		if (this.action != 'add') {
			Ext.Ajax.request({
				failure:function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
				},
				params:{
					MorbusHepatitisCure_id: this.MorbusHepatitisCure_id
				},
				success:function (response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					//log(result[0]);
					base_form.setValues(result[0]);
					var combo = base_form.findField('Drug_id');
					var drug_id = combo.getValue();
					combo.getStore().baseParams.Drug_id=drug_id;
					combo.getStore().baseParams.query=null;
					combo.getStore().load({
						params: {Drug_id: drug_id},
						callback: function() {
							this.setValue(drug_id);
							combo.hasFocus = true;
							combo.getStore().baseParams.Drug_id=null;
						}.createDelegate(combo)
					});
					grid.getStore().load({
						params: {MorbusHepatitisCure_id: this.MorbusHepatitisCure_id},
						globalFilters: {MorbusHepatitisCure_id: this.MorbusHepatitisCure_id}
					});
					loadMask.hide();
				}.createDelegate(this),
				url:'/?c=MorbusHepatitisCure&m=load'
			});			
		} else {
			loadMask.hide();
		}
		current_window.syncShadow();
		current_window.doLayout();
		
	},	
	initComponent: function() 
	{
		
		this.FormPanel = new Ext.form.FormPanel(
		{	
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			url:'/?c=MorbusHepatitisCure&m=save',
			items: 
			[{
				name: 'MorbusHepatitisCure_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusHepatitis_id',
				xtype: 'hidden'
			}, {
				name: 'EvnSection_id',
				xtype: 'hidden'
			},
			new sw.Promed.Panel({
				autoHeight: true,
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				collapsible: true,
				id: 'MainForm',
				region: 'north',
				layout: 'form',
				labelAlign: 'right',
				labelWidth: 150,
				title: lang['1_lechenie'],
				listeners: {
					collapse: function(panel) {
						this.syncShadow();
					}.createDelegate(this),
					expand: function() {
						this.syncShadow();
					}.createDelegate(this)
				},
				items: [{
					fieldLabel: lang['data_nachala'],
					name: 'MorbusHepatitisCure_begDT',
					allowBlank: false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['data_okonchaniya'],
					name: 'MorbusHepatitisCure_endDT',
					allowBlank: true,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					hiddenName: 'Drug_id',
					displayField: 'Drug_Name',
					valueField: 'Drug_id',
					fieldLabel: lang['preparat'],
					xtype: 'swbaseremotecombo',
					width: 450,
					triggerAction: 'none',
					allowBlank: false,
					trigger1Class: 'x-form-search-trigger',
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'Drug_id'
						}, [
							{name: 'Drug_id', type:'int'},
							{name: 'Drug_Code', type:'int'},
							{name: 'DrugForm_Name', type: 'string'},
							{name: 'Drug_Name', type: 'string'}
						]),
						url: '/?c=RlsDrug&m=loadDrugSimpleList'
					}),
					onTrigger1Click: function() 
					{
						if (this.disabled)
							return false;
						var combo = this;
						// Именно для этого комбо логика несколько иная 
						if (!this.formList)
						{
							if (Ext.getCmp('DrugPrepWinSearch')) {
								this.formList = Ext.getCmp('DrugPrepWinSearch');
							} else {
								this.formList = new sw.Promed.swListSearchWindow(
								{
									//params: {
										title: lang['poisk_medikamenta'],
										id: 'DrugPrepWinSearch',
										object: 'Drug',
										modal: false,
										//maximizable: true,
										maximized: true,
										paging: true,
										prefix: 'dprws',
										dataUrl: '/?c=Farmacy&m=loadDrugMultiList',
										columns: true,
										stringfields:
										[
											{name: 'Drug_id', key: true},
											{name: 'DrugPrepFas_id', hidden: true},
											{name: 'DrugTorg_Name', autoexpand: true, header: lang['torgovoe_naimenovanie'], isfilter:true, columnWidth: '.4'},
											{name: 'DrugForm_Name', header: lang['forma_vyipuska'], width: 140, isfilter:true, columnWidth: '.15'},
											{name: 'Drug_Dose', header: lang['dozirovka'], width: 100, isfilter:true, columnWidth: '.15'},
											{name: 'Drug_Fas', header: lang['fasovka'], width: 100},
											{name: 'Drug_PackName', header: lang['upakovka'], width: 100},
											{name: 'Drug_Firm', header: lang['proizvoditel'], width: 200, isfilter:true, columnWidth: '.3'},
											{name: 'Drug_Ean', header: 'EAN', width: 100},
											{name: 'Drug_RegNum', header: lang['ru'], width: 120}
										],
										useBaseParams: true
									//}
								});
							}
						}
						var params = (combo.getStore().baseParams)?combo.getStore().baseParams:{};
						params.Drug_id = null;
						combo.collapse();
						this.collapse();
						this.formList.show(
						{
							params:params,
							onSelect: function(data) 
							{
								combo.hasFocus = false;
								combo.getStore().baseParams.Drug_id=data['Drug_id'];
								combo.getStore().baseParams.query=null;
								combo.getStore().load({
									params: {Drug_id: data['Drug_id']},
									callback: function() {
										this.setValue(data['Drug_id']);
										combo.hasFocus = true;
										combo.getStore().baseParams.Drug_id=null;
									}.createDelegate(combo)
								});
							}.createDelegate(this), 
							onHide: function() 
							{
								this.focus(false);
							}.createDelegate(this)
						});
						return false;
					}
				}, {
					allowBlank: false,
					name: 'HepatitisResultClass_id',
					comboSubject: 'HepatitisResultClass',
					fieldLabel: lang['rezultat'],
					xtype: 'swcommonsprcombo',
					width: 450
				}, {
					name: 'HepatitisSideEffectType_id',
					comboSubject: 'HepatitisSideEffectType',
					fieldLabel: lang['pobochnyiy_effekt'],
					xtype: 'swcommonsprcombo',
					width: 450
				}]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				style: 'margin-bottom: 0.5em;',
				border: true,
				collapsible: true,
				region: 'north',
				layout: 'form',
				title: lang['2_monitoring_effektivnosti_lecheniya'],
				listeners: {
					collapse: function(panel) {
						this.syncShadow();
					}.createDelegate(this),
					expand: function() {
						this.syncShadow();
					}.createDelegate(this)
				},
				items: [new sw.Promed.ViewFrame({
					actions: [
						{name: 'action_add', handler: function() {
							this.openWindow('MorbusHepatitisCureEffMonitoring', 'add');
						}.createDelegate(this)},
						{name: 'action_edit', handler: function() {
							this.openWindow('MorbusHepatitisCureEffMonitoring', 'edit');
						}.createDelegate(this)},
						{name: 'action_view', handler: function() {
							this.openWindow('MorbusHepatitisCureEffMonitoring', 'view');
						}.createDelegate(this)},
						{name: 'action_delete', handler: function() {
							this.deleteGridSelectedRecord('MHCW_MorbusHepatitisCureEffMonitoring', 'MorbusHepatitisCureEffMonitoring_id');
						}.createDelegate(this)},
						{name: 'action_print'}
					],
					autoExpandColumn: 'autoexpand',
					autoExpandMin: 150,
					autoLoadData: false,
					border: false,
					dataUrl: '/?c=MorbusHepatitisCureEffMonitoring&m=loadList',
					collapsible: true,
					id: 'MHCW_MorbusHepatitisCureEffMonitoring',
					paging: false,
					style: 'margin-bottom: 10px',
					stringfields: [
						{name: 'MorbusHepatitisCureEffMonitoring_id', type: 'int', header: 'ID', key: true},
						{name: 'RecordStatus_Code', type: 'int', hidden: true},
						{name: 'HepatitisCurePeriodType_id', type: 'string', hidden: true},
						{name: 'HepatitisCurePeriodType_Name', type: 'string', header: lang['srok_lecheniya'], width: 240},
						{name: 'HepatitisQualAnalysisType_id', type: 'string', hidden: true},
						{name: 'HepatitisQualAnalysisType_Name', type: 'string', header: lang['kachestvennyiy_analiz'], width: 240},
						{name: 'MorbusHepatitisCureEffMonitoring_VirusStress', type: 'string', header: lang['virusnaya_nagruzka'], width: 120}
					],
					toolbar: true
				})]
			})],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusHepatitisCure_id'},
				{name: 'MorbusHepatitisCure_Year'},
				{name: 'MorbusHepatitisCure_Drug'},
				{name: 'HepatitisResultClass_id'},
				{name: 'HepatitisSideEffectType_id'}
			])
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
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swMorbusHepatitisCureWindow.superclass.initComponent.apply(this, arguments);
	}
});