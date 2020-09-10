/**
* Окно редактирования ответственного врача
* вызывается из контр.карт дисп.наблюдения (PersonDispEditWindow)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/
Ext6.define('common.EMK.PersonDispHistEditWindow', {
	/* свойства */
	alias: 'widget.swPersonDispHistEditWindowExt6',
	height: 230,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	width: 550,
	cls: 'arm-window-new ',//emk-forms-window person-disp-diag-edit-window
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(), //main_center_panel.body.dom,
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext.emptyFn,
	modal: true,
	title: 'Врач',
	
	//~ bindings: {
		//~ filterLpuSection: {UserLpuSection_id: '{UserLpuSection_id}', UserLpuSections: '{UserLpuSections}', EvnUslugaCommon_setDate: '{EvnUslugaCommon_setDate}'},
		//~ filterMedStaffFact: {UserMedStaffFact_id: '{UserMedStaffFact_id}', UserMedStaffFacts: '{UserMedStaffFacts}', EvnUslugaCommon_setDate: '{EvnUslugaCommon_setDate}', LpuSection_id: '{LpuSection_uid}'},
	//~ },
		
	show: function() {
		var win = this;
		this.callParent(arguments);
				
		win.taskButton.hide();
		var base_form = win.MainPanel.getForm();
		base_form.reset();
				
		this.action = arguments[0]['action'] || 'add';
		this.PersonDispHist_id = arguments[0]['PersonDispHist_id'] || null;
		this.PersonDisp_id = arguments[0]['PersonDisp_id'] || null;
		this.PersonDisp_begDate = arguments[0]['PersonDisp_begDate'] || null;
		this.PersonDisp_endDate = arguments[0]['PersonDisp_endDate'] || null;
		this.returnFunc = arguments[0]['callback'] || Ext.emptyFn;

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
		base_form.findField('LpuSection_id').getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));
		base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
		
		if (this.action != 'add') {
			var loadMask = new Ext6.LoadMask(win.MainPanel, { msg: "Подождите, идет сохранение..." });
			win.MainPanel.getForm().load({
				url: '/?c=PersonDisp&m=loadPersonDispHist',
				params: { PersonDispHist_id: this.PersonDispHist_id },
				success: function (form, action) {
					loadMask.hide();	
					var resp_obj = Ext6.util.JSON.decode(action.response.responseText)[0];
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
						Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
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
			this.queryById('button_save').disable();
		} else {
			base_form.findField('LpuSection_id').enable();
			base_form.findField('MedStaffFact_id').enable();
			base_form.findField('PersonDispHist_begDate').enable();
			base_form.findField('PersonDispHist_endDate').enable();
			this.queryById('button_save').enable();
		}
	},
	doSave: function() {
		var win = this;
		var form = this.MainPanel.getForm();
		var loadMask = new Ext6.LoadMask(this.MainPanel, { msg: "Подождите, идет сохранение..." });
		
		if (!form.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					win.MainPanel.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var begDate = form.findField('PersonDispHist_begDate').getValue();
		var endDate = form.findField('PersonDispHist_endDate').getValue();
		var today = new Date();

		if(begDate < win.PersonDisp_begDate){
			Ext6.Msg.alert('Сообщение', 'Дата начала не может быть раньше даты взятия под наблюдение');
			return false;
		}
		if(win.PersonDisp_endDate && begDate > win.PersonDisp_endDate){
			Ext6.Msg.alert('Сообщение', 'Дата начала не может быть позже даты снятия с наблюдения');
			return false;
		}
		if(!Ext6.isEmpty(endDate)){
			if(endDate < begDate){
				Ext6.Msg.alert('Сообщение', 'Дата окончания не может быть раньше даты начала');
				return false;
			}
			if(endDate > today){
				Ext6.Msg.alert('Сообщение', 'Дата окончания не может быть позже текущей даты');
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
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении примечания произошла ошибка'));
				}
							
			}.createDelegate(this)
		});
	},
	initComponent: function() {
		var win = this;
		
		win.MainPanel =  new Ext6.form.FormPanel({
			bodyPadding: '25 25 25 30',
			//userCls: 'PersonDispSopDiag dispcard',
			region: 'center',
			border: false,
			defaults: {
				width: 85+350,
				labelWidth: 85
			},
			items:[{
				name: 'PersonDispHist_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonDisp_id',
				value: 0,
				xtype: 'hidden'
			}, 
			{
				allowBlank: false,
				fieldLabel: langs('Врач'),
				name: 'MedStaffFact_id',
				itemId: 'PDHE_MedStaffactCombo',
				queryMode: 'local',
				minWidth: 350,
				matchFieldWidth: false,
				xtype: 'swMedStaffFactCombo',
				listConfig:{
							userCls: 'swMedStaffFactSearch MedStaffFact_nephro'
						},
				tpl: new Ext6.XTemplate(
							'<tpl for="."><div class="x6-boundlist-item MedStaffFactCombo">',
							'<table style="border: 0; width: 400px;">',
							'<tr>',
							'<td width="250px"><div style="font: 13px Roboto; font-weight: 700; text-transform: capitalize !important;">{MedPersonal_Fio} </div></td>',
							'<td width="20px">&nbsp;</td>',
							'<td width="60px"><div style="font: 12px Roboto; font-weight: 400;"><nobr style="color: #999;">Таб. номер</nobr></div></td>',
							'<td width="60px" style="padding-left: 29px"><div style="font: 12px Roboto; font-weight: 400;"><nobr style="color: #999;">Код ЛЛО</nobr></div></td>',
							'</tr>',
							'<tr>',
							'<td width="250px"><p style="font: 11px Roboto; font-weight: 400; color: #000;">',
							'<p class="postMedName" data-qtip="{PostMed_Name}" style="padding-top: 2px">{PostMed_Name}</p>',
							'<p class="lpuSectionName" data-qtip="{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}">{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</p>',
							'<nobr>{[!Ext.isEmpty(values.MedStaffFact_Stavka) ? " ставка" : ""]} {MedStaffFact_Stavka}</nobr>',
							'</p>',
							'<p class="postMedName">',
							'<nobr>{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name+"/ &nbsp":""]}</nobr>',
							'<nobr data-qtip="{[!Ext.isEmpty(values.WorkData_begDate) ? "Работает с: " + this.formatDate(values.WorkData_begDate):""]} {[!Ext.isEmpty(values.WorkData_endDate) ? "Уволен с: " + this.formatDate(values.WorkData_endDate):""]}"><span style="color: red"> {[!Ext.isEmpty(values.WorkData_endDate) ?"Уволен с: " + this.formatDate(values.WorkData_endDate):"</span>"+[!Ext.isEmpty(values.WorkData_begDate) ? "Работает с: " + this.formatDate(values.WorkData_begDate):""]]}</nobr>&nbsp;',
							'</p></td>',
							'<td width="20px">&nbsp;</td>',
							'<td style="width: 60px; vertical-align: top;"><p style="font: 11px Roboto; font-weight: 400; color: #000; padding-top: 5px;">{MedPersonal_TabCode}&nbsp;</p></td>',
							'<td style="width: 60px; vertical-align: top; padding-left: 29px"><p style="font: 11px Roboto; font-weight: 400; color: #000; padding-top: 5px;">{MedPersonal_DloCode}&nbsp;</p></td>',
							'</tr></table>',
							'</div></tpl>',
							{
								formatDate: function(date) {
									return Ext6.util.Format.date(date, 'd.m.Y');
								}
							}
						)
			},
			{
				allowBlank: false,
				fieldLabel: 'Отделение',
				queryMode: 'local',
				name: 'LpuSection_id',
				itemId: 'PDHE_LpuSectionCombo',
				queryMode: 'local',
				tabIndex: 2603,
				xtype: 'SwLpuSectionGlobalCombo',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = win.MainPanel.getForm();
						var MedStaffFactFilterParams = {
							allowLowLevel: 'yes',
							//onDate: 
						};
						MedStaffFactFilterParams.LpuSection_id = newValue;
						setMedStaffFactGlobalStoreFilter(MedStaffFactFilterParams);
						base_form.findField('MedStaffFact_id').setValue('');
						base_form.findField('MedStaffFact_id').getStore().removeAll();
						base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
					}
				}
			},
			{
				layout: 'column',
				padding: 0,
				border: false,
				items: [{
					allowBlank: false,
					fieldLabel: 'Начало',
					labelSeparator: '',
					name: 'PersonDispHist_begDate',
					width: 85+123,
					labelWidth: 85,
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'datefield'
				},
				{
					fieldLabel: 'Окончание',
					name: 'PersonDispHist_endDate',
					labelAlign: 'right',
					labelWidth: 104,
					width: 104+123,
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'datefield'
				}]
			}
			],
			url: '/?c=PersonDisp&m=savePersonDispHist',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{ name: 'PersonDispHist_id' },
						{ name: 'PersonDisp_id' },
						{ name: 'LpuSection_id' },
						{ name: 'MedPersonal_id' },
						{ name: 'PersonDispHist_begDate' },
						{ name: 'PersonDispHist_endDate' }
					]
				})
			})
		});

		Ext6.apply(win, {
			items: [
				win.MainPanel
			],
			buttons: ['->',
			{
				text: langs('ОТМЕНА'),
				itemId: 'button_cancel',
				userCls:'buttonPoupup buttonCancel',
				handler:function () {
					win.hide();
				}
			},
			{
				text: langs('ПРИМЕНИТЬ'),
				itemId: 'button_save',
				userCls:'buttonPoupup buttonAccept',
				handler: function() {
					this.doSave();
				}.createDelegate(this)
			}
			]
		});

		this.callParent(arguments);
	}
});