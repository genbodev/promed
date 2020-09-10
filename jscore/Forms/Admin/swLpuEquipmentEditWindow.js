/**
* swLpuEquipmentEditWindow - окно редактирования/добавления оборудования.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @version      05.10.2011
*/

sw.Promed.swLpuEquipmentEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 400,
	layout: 'form',
	id: 'LpuEquipmentEditWindow',
	listeners:
	{
		hide: function() 
		{
			this.LpuEquipmentEditForm.getForm().findField('LpuEquipmentType_id').setValue(null);
			this.onHide();
		},
		beforehide: function() {
			var combo = this.LpuEquipmentEditForm.getForm().findField('LpuEquipmentType_id');
			combo.fireEvent('beforeselect',combo,combo.getStore().getAt(0));
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function() 
	{
		this.submit();
		return true;
	},
	submit: function() 
	{
        var _this = this;
		var form = this.findById('LpuEquipmentEditForm');
		
		var base_form = this.LpuEquipmentEditForm.getForm(),
			pacsRequiredFields = ['PACS_ip_local','PACS_ip_vip','PACS_aet','PACS_port','PACS_wado','PACS_Interval','PACS_Interval_TimeType_id','LpuPacsCompressionType_id','PACS_StudyAge','PACS_Age_TimeType_id','LpuPacsCompressionType_id'],
			equipmentRequiredFields = ['LpuEquipment_Name','LpuEquipment_InvNum','LpuEquipment_StartUpDT']
		if(base_form.findField('LpuEquipmentType_id').getValue() == '4') {
			for (var i=0;i<pacsRequiredFields.length;i++) {
				base_form.findField(pacsRequiredFields[i]).allowBlank = false;
			}
			for (var i=0;i<equipmentRequiredFields.length;i++) {
				base_form.findField(equipmentRequiredFields[i]).allowBlank = true;
			}
		} else {
			for (var i=0;i<pacsRequiredFields.length;i++) {
				base_form.findField(pacsRequiredFields[i]).allowBlank = true;
			}
			for (var i=0;i<equipmentRequiredFields.length;i++) {
				base_form.findField(equipmentRequiredFields[i]).allowBlank = false;
			}
		}	
		
		
		if ( !form.getForm().isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		
		var current_window = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		var params = {};
		
		params.action = current_window.action;
			
		var cron_requests = getStoreRecords( this.CronGrid.getGrid().getStore(), {} );
		params.PACS_CronRequests = Ext.util.JSON.encode( cron_requests );
		params.LpuEquipmentType_id = base_form.findField('LpuEquipmentType_id').getValue();
		form.getForm().submit(
		{
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
				if (action.result)
				{
					if (action.result.LpuEquipment_id || action.result.LpuEquipmentPacs_id)
					{
						current_window.hide();
						Ext.getCmp('LpuPassportEditWindow').findById('LPEW_EquipmentGrid').loadData();
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								form.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	enableEdit: function(enable) 
	{
		var form = this;
		if (enable) 
		{
			var form = this.findById('LpuEquipmentEditForm');
			form.getForm().findField('LpuEquipmentType_id').enable();
			form.getForm().findField('LpuEquipment_Name').enable();
			form.getForm().findField('LpuEquipment_Producer').enable();
			form.getForm().findField('LpuEquipment_ReleaseDT').enable();
			form.getForm().findField('LpuEquipment_PurchaseDT').enable();
			form.getForm().findField('LpuEquipment_Model').enable();
			form.getForm().findField('LpuEquipment_InvNum').enable();
			form.getForm().findField('LpuEquipment_SerNum').enable();
			form.getForm().findField('LpuEquipment_StartUpDT').enable();
			form.getForm().findField('LpuEquipment_WearPersent').enable();
			form.getForm().findField('LpuEquipment_ConclusionDT').enable();
			form.getForm().findField('LpuEquipment_PurchaseCost').enable();
			form.getForm().findField('LpuEquipment_ResidualCost').enable();
			form.getForm().findField('LpuEquipment_IsNationProj').enable();
			form.getForm().findField('LpuEquipment_AmortizationTerm').enable();
			this.buttons[0].enable();
		}
		else 
		{
			var form = this.findById('LpuEquipmentEditForm');
			form.getForm().findField('LpuEquipmentType_id').disable();
			form.getForm().findField('LpuEquipment_Name').disable();
			form.getForm().findField('LpuEquipment_Producer').disable();
			form.getForm().findField('LpuEquipment_ReleaseDT').disable();
			form.getForm().findField('LpuEquipment_PurchaseDT').disable();
			form.getForm().findField('LpuEquipment_Model').disable();
			form.getForm().findField('LpuEquipment_InvNum').disable();
			form.getForm().findField('LpuEquipment_SerNum').disable();
			form.getForm().findField('LpuEquipment_StartUpDT').disable();
			form.getForm().findField('LpuEquipment_WearPersent').disable();
			form.getForm().findField('LpuEquipment_ConclusionDT').disable();
			form.getForm().findField('LpuEquipment_PurchaseCost').disable();
			form.getForm().findField('LpuEquipment_ResidualCost').disable();
			form.getForm().findField('LpuEquipment_IsNationProj').disable();
			form.getForm().findField('LpuEquipment_AmortizationTerm').disable();
			this.buttons[0].disable();			
		}
	},
	show: function() 
	{
		sw.Promed.swLpuEquipmentEditWindow.superclass.show.apply(this, arguments);
		var current_window = this;
        var form = this.LpuEquipmentEditForm.getForm(); 
        var EquipmentPanel = this.LpuEquipmentEditForm.findById('EqPanel');
        var PACSPanel = this.LpuEquipmentEditForm.findById('PACSPanel');
        var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});

        this.focus();
        this.findById('LpuEquipmentEditForm').getForm().reset();
        this.CronGrid.removeAll({ addEmptyRecord: false });
        this.callback = Ext.emptyFn;
        this.onHide = Ext.emptyFn;

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

        if (arguments[0].LpuEquipment_id)
            this.LpuEquipment_id = arguments[0].LpuEquipment_id;
        else
            this.LpuEquipment_id = null;

        if (arguments[0].LpuEquipmentPacs_id)
            this.LpuEquipmentPacs_id = arguments[0].LpuEquipmentPacs_id;
        else
            this.LpuEquipmentPacs_id = null;

        if (arguments[0].Lpu_id)
            this.Lpu_id = arguments[0].Lpu_id;
        else
            this.Lpu_id = null;

        if (arguments[0].callback)
        {
            this.callback = arguments[0].callback;
        }
        if (arguments[0].owner)
        {
            this.owner = arguments[0].owner;
        }
        if (arguments[0].onHide)
        {
            this.onHide = arguments[0].onHide;
        }
        if (arguments[0].action)
        {
            this.action = arguments[0].action;
        }
        else
        {
            if ( ( this.LpuEquipment_id ) && ( this.LpuEquipment_id > 0 ) )
                this.action = "edit";
            else
                this.action = "add";
        }

		var pacsRequiredFields = ['PACS_ip_local','PACS_ip_vip','PACS_aet','PACS_port','PACS_wado','PACS_Interval','PACS_Interval_TimeType_id','LpuPacsCompressionType_id','PACS_StudyAge','PACS_Age_TimeType_id','LpuPacsCompressionType_id'];
		if (arguments[0].LpuEquipmentPacs_id > 0) {		
			PACSPanel.show();			
			EquipmentPanel.hide();
			for (var i=0;i<pacsRequiredFields.length;i++) {
				form.findField(pacsRequiredFields[i]).allowBlank = false;
			}
			current_window.doLayout();
			current_window.syncShadow();
		} else {
			EquipmentPanel.show();
			PACSPanel.hide();
            for (var i=0;i<pacsRequiredFields.length;i++) {
				form.findField(pacsRequiredFields[i]).allowBlank = true;
			}
			current_window.doLayout();
			current_window.syncShadow();
		}


		form.setValues(arguments[0]);
		loadMask.show();
		switch (this.action) 
		{
			case 'add':
				this.setTitle(lang['oborudovanie_dobavlenie']);
				this.enableEdit(true);
				loadMask.hide();
				form.clearInvalid();
				break;
			case 'edit':
				this.setTitle(lang['oborudovanie_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['oborudovanie_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		//Загрузка комбобоксов
		var timeTypesIntervalCombo = this.LpuEquipmentEditForm.findById('PACS_TimeTypeInterval_Combo'),
			timeTypesAgeCombo = this.LpuEquipmentEditForm.findById('PACS_TimeTypeAge_Combo'),
			pacsCompressionTypesCombo = this.LpuEquipmentEditForm.findById('PACS_CompressionType_Combo'),
			lpuEquipmentTypesCombo = this.LpuEquipmentEditForm.findById('LPEW_LpuEquipmentType_id');
			
		timeTypesIntervalCombo.clearValue();
		timeTypesIntervalCombo.getStore().removeAll();
		timeTypesIntervalCombo.getStore().filterBy(function(rec) {
			return (/s|m|h/.test(rec.get('TimeType_Code')));
		});
		
		pacsCompressionTypesCombo.clearValue();
		pacsCompressionTypesCombo.getStore().removeAll();
		timeTypesAgeCombo.clearValue();
		timeTypesAgeCombo.getStore().removeAll();		

		if (this.action != 'add')
		{
			form.load(
			{
				params: 
				{
					LpuEquipment_id: current_window.LpuEquipment_id,
					LpuEquipmentPacs_id: current_window.LpuEquipmentPacs_id,
					Lpu_id: current_window.Lpu_id
				},
				failure: function(f, o, a)
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function(form,b,c) 
				{
					loadMask.hide();
					current_window.findById('LPEW_Lpu_id').setValue(current_window.Lpu_id);
					//form.findField('PACS_Interval_TimeType_id').setValue(form.findField('PACS_Interval_TimeType_id').getValue());
					this.CronGrid.loadData({
						globalFilters: {LpuEquipmentPacs_id: current_window.LpuEquipmentPacs_id}
					});
					timeTypesIntervalCombo.getStore().load({callback:function(){
						timeTypesIntervalCombo.setValue(timeTypesIntervalCombo.getValue());
						timeTypesAgeCombo.getStore().load({callback:function(){
						timeTypesAgeCombo.setValue(timeTypesAgeCombo.getValue());	
					}});
					}});
					pacsCompressionTypesCombo.getStore().load({callback:function(){
						pacsCompressionTypesCombo.setValue(pacsCompressionTypesCombo.getValue());	
					}});
					
				}.createDelegate(this),
				url: '/?c=LpuPassport&m=loadLpuEquipment'
			});
		} else {
			timeTypesIntervalCombo.getStore().load();
			pacsCompressionTypesCombo.getStore().load();
			timeTypesAgeCombo.getStore().load();

            form.findField('LpuEquipmentType_id').setValue(4);
            form.findField('LpuEquipmentType_id').setDisabled(true);
            this.LpuEquipmentEditForm.findById('EqPanel').hide();
            PACSPanel = this.LpuEquipmentEditForm.findById('PACSPanel').show();
		}
//		if ( this.action != 'view' )
//			Ext.getCmp('LPEW_LpuEquipmentType_id').focus(true, 100);
//		else
//			this.buttons[3].focus();
		
		
	},	
	initComponent: function() 
	{
		// Форма с полями 
		var current_window = this;
		
		
		var gridFields = [
			{ name: 'LpuEquipmentPacsCron_id', type: 'int', header: 'ID', key: true },
			{ name: 'LpuEquipmentPacsCron_request', header: lang['stroka_zaprosa'], editor: new Ext.form.TextField(), id:'autoexpand' },
		];
		
		var gridActions = [
			{ name: 'action_add', handler: function(){ this.findById(this.id+'CronGrid').addEmptyRow(); }.createDelegate(this), hidden: false },
			{ name: 'action_edit', handler: function(){ this.findById(this.id+'CronGrid').editSelectedCell(); }.createDelegate(this), disabled: true },
			{ name: 'action_view', disabled: true, hidden: true },
			{ name: 'action_delete', handler: function(){ this.findById(this.id+'CronGrid').deleteRow(); }.createDelegate(this), disabled: true, listeners: {keydown: function(f,e){alert(1)}} },
			{ name: 'action_refresh', disabled: true, hidden: true },
			{ name: 'action_print', disabled: true, hidden: true },
			{ name: 'action_save', disabled: true, hidden: true }
		];

		this.CronGrid = new sw.Promed.ViewFrame({
			actions: gridActions,
			autoLoadData: false,
//			autoexpand: 'expand',
			useEmptyRecord: false,
			object: 'LpuEquipmentPacsCron',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			border: true,
			dataUrl: '/?c=LpuPassport&m=loadCronRequests',
			height: 130,
			id: this.id+'CronGrid',
			region: 'center',
			saveAtOnce: false,
			selectionModel: 'cell',
			stringfields: gridFields,
			addEmptyRow: function() {
				var grid = this.getGrid();
				
				// Генерируем значение идентификатора с отрицательным значением
				// чтобы оперировать несохраненными записями
				var id = - swGenTempId( grid.getStore() );

				grid.getStore().loadData([{ WaybillRoute_id: id }], true);
				
				var rowsCnt = grid.getStore().getCount() - 1;
				var rowSel = 1;
				grid.getSelectionModel().select( rowsCnt, rowSel );
				grid.getView().focusCell( rowsCnt, rowSel );

				var cell = grid.getSelectionModel().getSelectedCell();
				if ( !cell || cell.length == 0 || cell[1] != rowSel ) {
					return false;
				}

				var record = grid.getSelectionModel().getSelected();
				if ( !record ) {
					return false;
				}

				grid.getColumnModel().setEditable( rowSel, true );
				grid.startEditing( cell[0], cell[1] );
			},
			deleteRow: function() {
				var grid = this.getGrid();

				var record = grid.getSelectionModel().getSelected();
				if ( !record ) {
					alert(lang['ne_vyibrana_zapis']);
					return false;
				}

//				var id = record.get('LpuEquipmentPacsCron_id');
//				if ( !id ) {
//					sw.swMsg.alert( 'Ошибка', 'Не удалось получить идентификатор CRON запроса.' );
//					return false;
//				}
				// Запись еще не сохранена? Просто вычеркиваем
				grid.getStore().remove(record);
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			},
			onCellSelect: function(sm,rowIdx,colIdx){
				var grid = this.getGrid();
				var record = grid.getSelectionModel().getSelected();
				this.getAction('action_edit').setDisabled( record.get('LpuEquipmentPacsCron_id') === null );
				this.getAction('action_delete').setDisabled( record.get('LpuEquipmentPacsCron_id') === null );
			},
			editSelectedCell: function(){
				var grid = this.getGrid();
				
				var rowsCnt = grid.getStore().getCount() - 1;
				var rowSel = 1;
				var cell = grid.getSelectionModel().getSelectedCell();
				if ( !cell || cell.length == 0 ) {
					return false;
				}

				var record = grid.getSelectionModel().getSelected();
				if ( !record ) {
					return false;
				}

				grid.getColumnModel().setEditable( rowSel, true );
				grid.startEditing( cell[0], cell[1] );
			}
		});
		
		
		this.CronGrid.getGrid().on('afteredit',function(e){
			e.record.commit();
		})
		
		
		this.LpuEquipmentEditForm = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',			
			frame: true,
			id: 'LpuEquipmentEditForm',
			labelAlign: 'right',
			labelWidth: 180,
			items: 
			[{
				id: 'LPEW_Lpu_id',
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			},  
			{
				fieldLabel: lang['tip_oborudovaniya'],
				xtype: 'swcommonsprcombo',
				allowBlank: false,
				comboSubject: 'LpuEquipmentType',
				disabled: false,
				anchor: '100%',
                value: 4,
				id: 'LPEW_LpuEquipmentType_id',
				hiddenName: 'LpuEquipmentType_id',
				name: 'LpuEquipmentType_id',
				listeners: {
					'beforeselect': function(combo, record) {
						var base_form = this.LpuEquipmentEditForm.getForm(),
							EquipmentPanel = this.LpuEquipmentEditForm.findById('EqPanel'),
							PACSPanel = this.LpuEquipmentEditForm.findById('PACSPanel'),
							pacsRequiredFields = ['PACS_ip_local','PACS_ip_vip','PACS_aet','PACS_port','PACS_wado','PACS_Interval','PACS_Interval_TimeType_id','LpuPacsCompressionType_id','PACS_StudyAge','PACS_Age_TimeType_id','LpuPacsCompressionType_id'],
							equipmentRequiredFields = ['LpuEquipment_Name','LpuEquipment_InvNum','LpuEquipment_StartUpDT']
						if(record.get(combo.valueField) == '4') {
							EquipmentPanel.hide();
							PACSPanel.show();
							for (var i=0;i<pacsRequiredFields.length;i++) {
								base_form.findField(pacsRequiredFields[i]).allowBlank = false;
							}
							for (var i=0;i<equipmentRequiredFields.length;i++) {
								base_form.findField(equipmentRequiredFields[i]).allowBlank = true;
							}
						} else {
							PACSPanel.hide();
							EquipmentPanel.show();
							
							for (var i=0;i<pacsRequiredFields.length;i++) {
								base_form.findField(pacsRequiredFields[i]).allowBlank = true;
							}
							for (var i=0;i<equipmentRequiredFields.length;i++) {
								base_form.findField(equipmentRequiredFields[i]).allowBlank = false;
							}
						}						
						current_window.doLayout(); 
						current_window.syncShadow();
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_LPEEW + 1
			},
			{				
				xtype: 'fieldset',
				height: 440,
				id: 'EqPanel',
				title: '',
				collapsible: true,
				labelWidth: 160,
				items: [{
					name: 'LpuEquipment_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					fieldLabel: lang['naimenovanie'],
					xtype: 'textfield',
					disabled: true,
					autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
					anchor: '100%',
					name: 'LpuEquipment_Name',
					tabIndex: TABINDEX_LPEEW + 2
					//allowBlank : false
				},
				{
					fieldLabel: lang['proizvoditel'],
					xtype: 'textfield',
					disabled: true,
					autoCreate: {tag: "input", maxLength: "60", autocomplete: "off"},
					anchor: '100%',				
					name: 'LpuEquipment_Producer',
					tabIndex: TABINDEX_LPEEW + 3
				},
				{
					fieldLabel: lang['data_vyipuska'],
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					format: 'd.m.Y',
					disabled: true,
					name: 'LpuEquipment_ReleaseDT',				
					tabIndex: TABINDEX_LPEEW + 4
				},
				{
					fieldLabel: lang['data_priobreteniya'],
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					format: 'd.m.Y',
					disabled: true,
					name: 'LpuEquipment_PurchaseDT',					
					tabIndex: TABINDEX_LPEEW + 5
				},
				{
					fieldLabel: lang['model'],
					xtype: 'textfield',
					disabled: true,
					autoCreate: {tag: "input", maxLength: "60", autocomplete: "off"},
					anchor: '100%',
					name: 'LpuEquipment_Model',
					tabIndex: TABINDEX_LPEEW + 6
				},
				{
					fieldLabel: lang['inventarnyiy_nomer'],
					xtype: 'textfield',
					disabled: true,
					autoCreate: {tag: "input", maxLength: "50", autocomplete: "off"},
					anchor: '100%',
					name: 'LpuEquipment_InvNum',
					tabIndex: TABINDEX_LPEEW + 7
					//allowBlank : false
				},
				{
					fieldLabel: lang['seriynyiy_nomer'],
					xtype: 'textfield',
					disabled: true,
					autoCreate: {tag: "input", maxLength: "50", autocomplete: "off"},
					anchor: '100%',
					name: 'LpuEquipment_SerNum',
					tabIndex: TABINDEX_LPEEW + 8
				},
				{
					fieldLabel: lang['data_vvoda_v_ekspluatatsiyu'],
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					format: 'd.m.Y',
					disabled: true,
					name: 'LpuEquipment_StartUpDT',
					tabIndex: TABINDEX_LPEEW + 9
					//allowBlank : false
				},
				{
					fieldLabel: lang['%_iznosa'],
					xtype: 'textfield',
					disabled: true,
					autoCreate: {tag: "input", maxLength: "3", autocomplete: "off"},
					maskRe: /[0-9]/,
					anchor: '100%',
					name: 'LpuEquipment_WearPersent',
					tabIndex: TABINDEX_LPEEW + 10
				},
				{
					fieldLabel: lang['data_zaklyucheniya_o_prigodnosti_apparata'],
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					format: 'd.m.Y',
					disabled: true,
					name: 'LpuEquipment_ConclusionDT',
					tabIndex: TABINDEX_LPEEW + 11
				},
				{
					fieldLabel: lang['stoimost_priobreteniya'],
					xtype: 'textfield',
					disabled: true,
					autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
					maskRe: /[0-9]/,
					anchor: '100%',
					name: 'LpuEquipment_PurchaseCost',
					tabIndex: TABINDEX_LPEEW + 12
				},
				{
					fieldLabel: lang['ostatochnaya_stoimost'],
					xtype: 'textfield',
					disabled: true,
					autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
					maskRe: /[0-9]/,
					anchor: '100%',
					name: 'LpuEquipment_ResidualCost',
					tabIndex: TABINDEX_LPEEW + 13
				},
				{
					xtype: 'swyesnocombo',
					tabIndex: TABINDEX_LPEEW + 14,
					disabled: true,
					fieldLabel: lang['postavlen_po_nats_proektu'],
					hiddenName: 'LpuEquipment_IsNationProj'
				},
				{
					fieldLabel: lang['srok_amortizatsii'],
					xtype: 'textfield',
					disabled: true,
					autoCreate: {tag: "input", maxLength: "3", autocomplete: "off"},
					maskRe: /[0-9]/,
					anchor: '100%',
					name: 'LpuEquipment_AmortizationTerm',
					tabIndex: TABINDEX_LPEEW + 15
				}]
			},
			{
				//hidden: true,
//				xtype: 'fieldset',
//				height: 440,
				autoHeight: true,
				id: 'PACSPanel',				
				title: '',
				collapsible: true,
				labelWidth: 160,
				layout: 'form',
				items: [
				{
					xtype: 'fieldset',
					border: true,
					autoHeight: true,
					items:[
					{
						name: 'LpuEquipmentPacs_id',
						value: 0,
						xtype: 'hidden'
					},
					{
						fieldLabel: lang['naimenovanie'],
						xtype: 'textfield',
						autoCreate: {tag: "input", maxLength: "90", autocomplete: "off"},
						anchor: '100%',
						name: 'PACS_name',
						tabIndex: TABINDEX_LPEEW + 2
					},
					{
						fieldLabel: lang['ip-adres_lokalnyiy'],
						xtype: 'textfield',					
						//plugins: [new Ext.ux.InputTextMask('999.999.999.999',false)],
						//autoCreate: {tag: "input", maxLength: "60", autocomplete: "off"},
						anchor: '100%',	
						//regex: new RegExp(/^(1\d{2}\.|[1-9]\d?\.|2[0-4]\d\.|25[0-5]\.){3}(\d{1,2}|1\d{2}|2[0-4]\d|25[0-5])$/),
						name: 'PACS_ip_local',
						tabIndex: TABINDEX_LPEEW + 3
					},
					{
						fieldLabel: lang['ip-adres_vipnet'],
						xtype: 'textfield',
						//plugins: [new Ext.ux.InputTextMask('999.999.999.999',false)],
						anchor: '100%',	
						//xtype: 'masktextfield',					
						//mask: '999.999.999-99',
					//	autoCreate: {tag: "input", maxLength: "15", minLength: "15", autocomplete: "off"},

						//regex: new RegExp(/^(1\d{2}\.|[1-9]\d?\.|2[0-4]\d\.|25[0-5]\.){3}(\d{1,2}|1\d{2}|2[0-4]\d|25[0-5])$/),
						name: 'PACS_ip_vip',
						tabIndex: TABINDEX_LPEEW + 4
					},
					{
						fieldLabel: 'AETittle',
						xtype: 'textfield',					
						autoCreate: {tag: "input", maxLength: "50", autocomplete: "off"},
						//anchor: '100%',
						name: 'PACS_aet',
						tabIndex: TABINDEX_LPEEW + 5
					},
					{
						fieldLabel: 'TCP/IP port',
						xtype: 'textfield',					
						//anchor: '100%',
						name: 'PACS_port',
						autoCreate: {tag: "input",  maxLength: "5", autocomplete: "off"},
						maskRe: /[0-9]/,
						tabIndex: TABINDEX_LPEEW + 6
					},
					{
						fieldLabel: 'WADO-port',
						xtype: 'textfield',					
						//anchor: '100%',
						name: 'PACS_wado',
						autoCreate: {tag: "input",  maxLength: "5", autocomplete: "off"},
						maskRe: /[0-9]/,
						tabIndex: TABINDEX_LPEEW + 7
					}]
				},
				{
					xtype: 'fieldset',
					border: true,
					autoHeight: true,
					labelWidth: 100,
					items:[
					{
						layout: 'column',
						items: [
							{
								layout: 'form',
								columWidth: 0.75,
								width: '60%',
								labelWidth: 100,
								items:[
								{
									fieldLabel: lang['interval'],
									xtype: 'textfield',
									maskRe: /^\d\d?$/,
									maxLength:2,
//									maxLengthText: 'Введите значение от 1 до 99',
//									msgTarget : 'under',
									tabIndex: TABINDEX_LPEEW + 8,
									anchor: '100%',
									name: 'PACS_Interval',
									value:1
								}
								]
							},{
								layout: 'form',
								columWidth: 0.25,
								width: '35%',
								labelWidth: 30,
								items:[
								{
									fieldLabel: lang['ed'],
									xtype: 'swtimetypecombo',
									width: '40',
									anchor: '100%',
									value:6,
									id: 'PACS_TimeTypeInterval_Combo',
									tabIndex: TABINDEX_LPEEW + 9,
									hiddenName: 'PACS_Interval_TimeType_id'
								}
								]
							}
						]
					},{
						layout: 'column',
						items:[
						{
							columWidth: 0.75,
							layout: 'form',
							width: '60%',
							labelWidth: 100,
							items:[
							{
								fieldLabel: lang['isklyuchennoe_vremya_s'],
								xtype: 'textfield',
								maskRe: /[0-9]/,
								width: '30',
								anchor: '100%',
								name: 'PACS_ExcludeTimeFrom',
								tabIndex: TABINDEX_LPEEW + 10
							}
							]
						},{
							columWidth: 0.25,
							layout: 'form',
							width: '35%',
							labelWidth: 30,
							items:[
							{
								fieldLabel: lang['po'],
								xtype: 'textfield',
								maskRe: /[0-9]/,
								width: '30',
								anchor: '100%',
								name: 'PACS_ExcludeTimeTo',
								tabIndex: TABINDEX_LPEEW + 11
							}
							]
						}
						]
					},{
						autoHeight: true,
						style: 'padding: 5px;',
						title: 'CRON',
						xtype: 'fieldset',
						items: this.CronGrid
					},{
						width: '40',
						id: 'PACS_CompressionType_Combo',
						hiddenName: 'LpuPacsCompressionType_id',
						xtype: 'swpacscompressiontypecombo',
						tabIndex: TABINDEX_LPEEW + 12
						
					},{
						layout: 'column',
						items: [
						{
							layout: 'form',
							columWidth: 0.75,
							width: '60%',
							labelWidth: 100,
							items:[
							{
								fieldLabel: lang['vozrast'],
								xtype: 'textfield',
								maskRe: /^\d\d?$/,
								maxLength:2,
								anchor: '100%',
								name: 'PACS_StudyAge',
								tabIndex: TABINDEX_LPEEW + 13
							}
							]
						},{
							layout: 'form',
							columWidth: 0.25,
							width: '35%',
							labelWidth: 30,
							items:[
							{
								fieldLabel: lang['ed'],
								xtype: 'swtimetypecombo',
								width: '40',
								anchor: '100%',
								id: 'PACS_TimeTypeAge_Combo',
								hiddenName: 'PACS_Age_TimeType_id',
								tabIndex: TABINDEX_LPEEW + 14
							}
							]
						}
						]
	
					},{
						xtype:'checkbox',
						checked: true,
						fieldLabel:lang['udalyat_iz_bd'],
						name: 'PACS_DeleteFromDb',
						id: 'PACS_DeleteFromDb_checkbox',
						tabIndex: TABINDEX_LPEEW + 15
					},{
						xtype:'checkbox',
						checked: true,
						fieldLabel:lang['udalyat_patsientov_bez_issledovaniy'],
						labelWidth:250,
						name: 'PACS_DeletePatientsWithoutStudies',
						id: 'PACS_DeletePatientsWithoutStudies_checkbox',
						tabIndex: TABINDEX_LPEEW + 16
					}
					]
				}]
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{name: 'Lpu_id'},
				{name: 'LpuEquipment_id'},
				{name: 'LpuEquipmentType_id'},
				{name: 'LpuEquipment_Name'},
				{name: 'LpuEquipment_Producer'},
				{name: 'LpuEquipment_ReleaseDT'},
				{name: 'LpuEquipment_PurchaseDT'},
				{name: 'LpuEquipment_Model'},
				{name: 'LpuEquipment_InvNum'},
				{name: 'LpuEquipment_SerNum'},
				{name: 'LpuEquipment_StartUpDT'},
				{name: 'LpuEquipment_WearPersent'},
				{name: 'LpuEquipment_ConclusionDT'},
				{name: 'LpuEquipment_PurchaseCost'},
				{name: 'LpuEquipment_ResidualCost'},
				{name: 'LpuEquipment_IsNationProj'},
				{name: 'LpuEquipment_AmortizationTerm'},
				
				{name: 'LpuEquipmentPacs_id'},
				{name: 'PACS_name'},
				{name: 'PACS_ip_local'},
				{name: 'PACS_ip_vip'},
				{name: 'PACS_aet'},
				{name: 'PACS_port'},
				{name: 'PACS_wado'},
				{name: 'PACS_Interval'},
				{name: 'PACS_Interval_TimeType_id'},
				{name: 'PACS_ExcludeTimeFrom'},
				{name: 'PACS_ExcludeTimeTo'},
				{name: 'LpuPacsCompressionType_id'},
				{name: 'PACS_StudyAge'},
				{name: 'PACS_Age_TimeType_id'},
				{name: 'PACS_DeleteFromDb'},
				{name: 'PACS_DeletePatientsWithoutStudies'}
			]),
			url: '/?c=LpuPassport&m=saveLpuEquipment'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_LPEEW + 16,
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
				tabIndex: TABINDEX_LPEEW + 17,
				text: BTN_FRMCANCEL,
				onShiftTabAction: function() {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function() {
					this.LpuEquipmentEditForm.getForm().findField('LpuEquipmentType_id').focus(true);
				}.createDelegate(this)
			}],
			items: [this.LpuEquipmentEditForm]
		});
		sw.Promed.swLpuEquipmentEditWindow.superclass.initComponent.apply(this, arguments);
	}
});