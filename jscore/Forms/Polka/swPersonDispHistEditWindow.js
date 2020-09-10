/**
* swPersonDispHistEditWindow - окно редактирования Сопутствующих диагнозов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Aleksandr Chebukin 
* @version      20.02.2016
*/

/*NO PARSE JSON*/
sw.Promed.swPersonDispHistEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'PersonDispHistEditWindow',
	layout: 'border',
	maximizable: false,
	width: 600,
	height: 180,
	modal: true,
	codeRefresh: true,
	objectName: 'swPersonDispHistEditWindow',
	objectSrc: '/jscore/Forms/Polka/swPersonDispHistEditWindow.js',
	show: function() {		
		sw.Promed.swPersonDispHistEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('PersonDispHistEditForm').getForm();
		base_form.reset();

		this.action = arguments[0]['action'] || 'add';
		this.PersonDispHist_id = arguments[0]['PersonDispHist_id'] || null;
		this.PersonDisp_id = arguments[0]['PersonDisp_id'] || null;
		this.PersonDisp_begDate = arguments[0]['PersonDisp_begDate'] || null;
		this.PersonDisp_endDate = arguments[0]['PersonDisp_endDate'] || null;
		this.returnFunc = arguments[0]['callback'] || Ext.emptyFn;
		
		switch (this.action){
			case 'add':
				this.setTitle(lang['otvetstvenniy_vrach_dobavlenie']);
				break;
			case 'edit':
				this.setTitle(lang['otvetstvenniy_vrach_redaktirovanie']);
				break;
			case 'view':
				this.setTitle(lang['otvetstvenniy_vrach_prosmotr']);
				break;
		}

		var lpu_section_filter_params = {
			allowLowLevel: 'yes',
			isPolka: true,
			isDisp: true,
			regionCode: getGlobalOptions().region.number
		};
		var medstafffact_filter_params = {
			allowLowLevel: 'yes',
			isPolka: true,
			isDisp: true,
			regionCode: getGlobalOptions().region.number,
			isDoctor: true
		};

		base_form.findField('LpuSection_id').getStore().removeAll();
		base_form.findField('MedStaffFact_id').getStore().removeAll();
		// загружаем локальные списки отделений и мест работы
		setLpuSectionGlobalStoreFilter(lpu_section_filter_params);
		setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
		if (this.action != 'add') {
			var loadMask = new Ext.LoadMask(Ext.get('PersonDispHistEditForm'), { msg: "Подождите, идет сохранение..." });
			this.findById('PersonDispHistEditForm').getForm().load({
				url: '/?c=PersonDisp&m=loadPersonDispHist',
				params: { PersonDispHist_id: this.PersonDispHist_id },
				success: function (form, action) {
					loadMask.hide();	
					var resp_obj = Ext.util.JSON.decode(action.response.responseText)[0];
					if(resp_obj && resp_obj.MedPersonal_id && resp_obj.LpuSection_id){
						var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec){
							return (rec.get('MedPersonal_id') == resp_obj.MedPersonal_id && rec.get('LpuSection_id') == resp_obj.LpuSection_id);
						});
						if(index > -1){
							base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
						}
					}
				},
				failure: function (form, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
						this.hide();
					}
				},
				scope: this
			});		
		} else {
			base_form.findField('PersonDisp_id').setValue(this.PersonDisp_id);
		}		
		if (this.action=='view') {
			base_form.findField('LpuSection_id').disable();
			base_form.findField('MedStaffFact_id').disable();
			base_form.findField('PersonDispHist_begDate').disable();
			base_form.findField('PersonDispHist_endDate').disable();
			this.buttons[0].disable();
		} else {
			base_form.findField('LpuSection_id').enable();
			base_form.findField('MedStaffFact_id').enable();
			base_form.findField('PersonDispHist_begDate').enable();
			base_form.findField('PersonDispHist_endDate').enable();
			this.buttons[0].enable();
		}
		
	},
	doSave: function() 
	{
		var win = this;
		var form = this.findById('PersonDispHistEditForm').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('PersonDispHistEditForm'), { msg: "Подождите, идет сохранение..." });
		
		if (!form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var begDate = form.findField('PersonDispHist_begDate').getValue();
		var endDate = form.findField('PersonDispHist_endDate').getValue();
		var today = new Date();

		if(begDate < win.PersonDisp_begDate){
			sw.swMsg.alert('Сообщение', 'Дата начала не может быть раньше даты взятия под наблюдение');
			return false;
		}
		if(win.PersonDisp_endDate && begDate > win.PersonDisp_endDate){
			sw.swMsg.alert('Сообщение', 'Дата начала не может быть позже даты снятия с наблюдения');
			return false;
		}
		if(!Ext.isEmpty(endDate)){
			if(endDate < begDate){
				sw.swMsg.alert('Сообщение', 'Дата окончания не может быть раньше даты начала');
				return false;
			}
			if(endDate > today){
				sw.swMsg.alert('Сообщение', 'Дата окончания не может быть позже текущей даты');
				return false;
			}
		}

		var params = new Object;
		params.MedPersonal_id = form.findField('MedStaffFact_id').getStore().getById(form.findField('MedStaffFact_id').getValue()).get('MedPersonal_id');
		
		loadMask.show();		
		form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			},
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.success) {
						win.hide();
						win.returnFunc();
					}	
				}
				else {
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_primechaniya_proizoshla_oshibka']);
				}
							
			}.createDelegate(this)
		});
	},

	initComponent: function() {
	
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			id:'PersonDispHistEditForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 150,
			items:
			[{
				name: 'PersonDispHist_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonDisp_id',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: 'Отделение',
				hiddenName: 'LpuSection_id',
				id: 'PDHE_LpuSectionCombo',
				linkedElements: [
					'PDHE_MedStaffactCombo'
				],
				listWidth: 650,
				tabIndex: 2603,
				width: 350,
				xtype: 'swlpusectionglobalcombo'
			},
			{
				allowBlank: false,
				fieldLabel: lang['otvetstvenniy_vrach'],
				hiddenName: 'MedStaffFact_id',
				id: 'PDHE_MedStaffactCombo',
				listWidth: 650,
				parentElementId: 'PDHE_LpuSectionCombo',
				tabIndex: 2604,
				width: 350,
				xtype: 'swmedstafffactglobalcombo'
			},
			{
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					border: false,
					width: 150,
					items:[{
						anchor: '100%',
						style: 'text-align:right;display:block;width:100%;padding-top:3px;font-size:12px;',
						text: 'Период ответственности: ',
						xtype: 'label'
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 1,
					items:[{
						allowBlank: false,
						labelSeparator: '',
						name: 'PersonDispHist_begDate',
						width: 100,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						xtype: 'swdatefield'
					}]
				},
				{
					layout: 'form',
					border: false,
					labelWidth: 10,
					items:[{
						labelSeparator: '-',
						name: 'PersonDispHist_endDate',
						width: 100,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						xtype: 'swdatefield'
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({},
			[
				{ name: 'PersonDispHist_id' },
				{ name: 'PersonDisp_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'PersonDispHist_begDate' },
				{ name: 'PersonDispHist_endDate' }
			]
			),
			url: '/?c=PersonDisp&m=savePersonDispHist'
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel],
			buttons:
			[{
				text: lang['sohranit'],
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this)
			},
			{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			},
			{
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function()
				{
					this.hide();
				}.createDelegate(this)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.doSave();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swPersonDispHistEditWindow.superclass.initComponent.apply(this, arguments);
	}
});