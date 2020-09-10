/**
* swTTGScheduleFillWindow - окно создания расписания в поликлинике
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      04.10.2011
*/

/*NO PARSE JSON*/
sw.Promed.swTTGScheduleFillWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['sozdanie_raspisaniya'],
	id: 'TTGScheduleFillWindow',
	layout: 'border',
	maximizable: false,
	width: 550,
	height: 450,
	modal: true,
	resizable: false,
	codeRefresh: true,
	objectName: 'swTTGScheduleFillWindow',
	objectSrc: '/jscore/Forms/Reg/swTTGScheduleFillWindow.js',
	
	/**
	 * Идентификатор места работы, с расписанием которого мы работаем
	 */
	MedStaffFact_id: null,
	checkTimetableType: function(){
		var win = this,
			base_form = win.MainPanel.getForm(),
			combo = base_form.findField('TimetableType_id'),
			isMultiRec = base_form.findField('TimeTableGraf_IsMultiRec'),
			recLim = base_form.findField('TimeTableGraf_PersRecLim');
		// Если бирка с типом "Групповой прием" показываем
		if (combo && combo.getValue() > 0 && combo.getValue() == 14){
			isMultiRec.setValue(true);
			isMultiRec.showContainer();
			recLim.showContainer();
			recLim.allowBlank = false;
		} else {
			isMultiRec.setValue(false);
			isMultiRec.hideContainer();
			recLim.hideContainer();
			recLim.allowBlank = true;
		}
	},
	returnFunc: function(owner) {},
	annotationListLoad: function() {
		var win = this,
			base_form = win.findById('TTGCreateScheduleForm').getForm(),
			grid = win.findById('ttgsfCopyAnnotationGrid').getGrid();
		if (base_form.findField('ScheduleCreationType').getValue() == 1 || Ext.isEmpty(base_form.findField('CreateDateRange').value)) {
			grid.getStore().removeAll();
			return false;
		}
		grid.getStore().load({
			params: {
				MedStaffFact_id: win.MedStaffFact_id || getGlobalOptions().CurMedStaffFact_id,
				AnnotationDateRange: base_form.findField('CreateDateRange').value
			}
		});
	},
	show: function() 
	{
		sw.Promed.swTTGScheduleFillWindow.superclass.show.apply(this, arguments);
		
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['MedStaffFact_id']) {
			this.MedStaffFact_id = arguments[0]['MedStaffFact_id'];
		}
		
		var form = this.findById('TTGCreateScheduleForm');
		base_form = form.getForm();
		this.findById('ttgsfCreateAnnotationPanel').collapse();
		this.findById('ttgsfCreateAnnotationPanel').checkbox.dom.checked = false;
		this.findById('ttgsfCopyAnnotationGrid').getGrid().getStore().removeAll();
		this.findById('ttgsfCopyToDate').setValue('');
		base_form.findField('AnnotationType_id').setValue(4);
		base_form.findField('AnnotationVison_id').setValue(null);
		base_form.findField('Annotation_Comment').setValue(null);
		base_form.findField('ignore_doubles').setValue(0);
		
		if (arguments[0]['date']) {
			form.findById('ttgsfCreateDate').setValue(arguments[0]['date'] + ' - ' + arguments[0]['date']);
		} else {
			form.findById('ttgsfCreateDate').setValue('');
		}

		if ( !(form.getForm().findField('ttgsfDuration').msf && form.getForm().findField('ttgsfDuration').msf == this.MedStaffFact_id) ) {
			form.findById('ttgsfDuration').msf = this.MedStaffFact_id;
			//Получаем длительность приёма врача
			Ext.Ajax.request(
			{
				url: C_MSF_DURATION_GET,
				params: {
					MedStaffFact_id: this.MedStaffFact_id
				},
				callback: function(options, success, response) 
				{
					if (success)
					{
						if ( response.responseText.length > 0 )
						{
							var result = Ext.util.JSON.decode(response.responseText);
							if (result[0].MedStaffFact_PriemTime) {
								form.findById('ttgsfDuration').setValue(result[0].MedStaffFact_PriemTime);
							}
						}
					}
				}
			});
		}

		if ( !(form.getForm().findField('ttgsfTimetableType').getStore().place && form.getForm().findField('ttgsfTimetableType').getStore().place == 1) ) {
			form.getForm().findField('ttgsfTimetableType').getStore().load({
				params: {
					Place_id: 1
				},
				callback: function () {
					form.getForm().findField('ttgsfTimetableType').setValue(1);
					form.getForm().findField('ttgsfTimetableType').getStore().place = 1;
				}
			});
		}
	},
	doSave: function() 
	{
		var win = this;
		var form = this.findById('TTGCreateScheduleForm');
		if (!form.getForm().isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var fillScheduleDate = Ext.getCmp('ttgsfCreateDate');
		var scheduleCreationType = Ext.getCmp('ttgsfScheduleCreationType').value;
		var copyScheduleDate = Ext.getCmp('ttgsfCopyToDate');
		var fillScheduleMaxDays = getGlobalOptions().fillScheduleMaxDays;
		var fillDays = Math.ceil(Math.abs(fillScheduleDate.getValue2() - fillScheduleDate.getValue1()) / (1000 * 3600 * 24)) + 1;
		var copyDays = Math.ceil(Math.abs(copyScheduleDate.getValue2() - copyScheduleDate.getValue1()) / (1000 * 3600 * 24)) + 1;
		if(
			(scheduleCreationType == 1 && fillDays > fillScheduleMaxDays ) || //для создания нового расписания
			(scheduleCreationType == 2 && copyDays > fillScheduleMaxDays)	  //для копирования расписания
		){
			Ext.Msg.alert('Ошибка','Нельзя создать расписание более, чем на ' + fillScheduleMaxDays + ' дней.');
			return false;
		}
		
		var loadMask = new Ext.LoadMask(Ext.get('TTGCreateScheduleForm'), { msg: "Подождите, идет создание новых бирок..." });
		loadMask.show();
		
		var post = [];
		post['MedStaffFact_id'] = this.MedStaffFact_id;
		post['AnnotationVison_id'] = form.getForm().findField('AnnotationVison_id').getValue();
		post['copyAnnotationGridData'] = this.findById('ttgsfCopyAnnotationGrid').getCheckedAnnotationList();
		form.getForm().submit({
			timeout: 1500, // 1500 секунд = 25 минут
			params: post,
			failure: function(result_form, action) 
			{
				if (action.result)
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
					else
					{
						//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
					}
				}
				loadMask.hide();
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if ( action.result.Alert_Msg && 'YesNo' == action.result.Error_Msg ) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' && action.result.Error_Code == 112 ) {
								form.getForm().findField('ignore_doubles').setValue('1');
								win.doSave();
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: action.result.Alert_Msg,
						title: lang['prodoljit_sohranenie']
					});
				} else {
					win.hide();
					win.returnFunc();
				}	
			}.createDelegate(this)
		});
		return true;
	},

	initComponent: function() 
	{
	var win = this;
	win.MainPanel = new sw.Promed.FormPanel(
		{
			id:'TTGCreateScheduleForm',
			height:this.height, 
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			labelWidth: 150,
			items:
			[
			{
				anchor: '100%',
				name: 'ScheduleCreationType',
				tabIndex: TABINDEX_TTGSF + 1,
				xtype: 'swschedulecreationtypecombo',
				id: 'ttgsfScheduleCreationType',
				allowBlank: false,
				fieldLabel: lang['variant_sozdaniya'],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var form = this.findById('TTGCreateScheduleForm');

						form.findById('ttgsfTimetableType').setAllowBlank(newValue != 1);
						form.findById('ttgsfCopyToDate').setAllowBlank(newValue != 2);

						if (newValue == 1) {
							form.findById('ttgsfCreateSchedulePanel').show();
							form.findById('ttgsfCopySchedulePanel').hide();
							form.findById('ttgsfCreateDate').setFieldLabel(lang['sozdat_na_datyi']);
							this.findById('ttgsfCreateAnnotationPanel').show();
							this.findById('ttgsfCopyAnnotationGrid').hide();
						}
							
						if (newValue == 2) {
							form.findById('ttgsfCreateSchedulePanel').hide();
							form.findById('ttgsfCopySchedulePanel').show();
							form.findById('ttgsfCreateDate').setFieldLabel(lang['kopirovat_iz_diapazona']);
							this.findById('ttgsfCreateAnnotationPanel').hide();
							this.findById('ttgsfCopyAnnotationGrid').show();
							this.annotationListLoad();
						}
							
					}.createDelegate(this),
					'select': function(combo, record, index) {
						combo.fireEvent('change', combo, record.data.ScheduleCreationType, null);
					}
				},
				value: 1
			},
			{
				allowBlank: false,
				fieldLabel: lang['sozdat_na_datyi'],
				tabIndex: TABINDEX_TTGSF + 2,
				id: 'ttgsfCreateDate',
				name: 'CreateDateRange',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 170,
				xtype: 'daterangefield',
				listeners: {
					'blur': function () {
						win.annotationListLoad();
					},
					'select': function() {
						win.annotationListLoad();
					}
				}
			},
			{
				hidden: true,
				layout: 'form',
				height: 40,
				id: 'ttgsfCopySchedulePanel',
				title: '',
				labelWidth: 150,
				items: [{
					fieldLabel: 'Вставить в диапазон',
					id: 'ttgsfCopyToDate',
					name: 'CopyToDateRange',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 153,
					xtype: 'daterangefield',
					tabIndex: TABINDEX_TTGSF + 3
				}]
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				id: 'ttgsfCreateSchedulePanel',
				title: '',
				items: [{
					id: 'ttgsfStartDay',
					fieldLabel: lang['nachalo_rabotyi'],
					name: 'StartTime',
					plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
					tabIndex: TABINDEX_TTGSF + 5,
					validateOnBlur: false,
					value: '08:00',
					width: 60,
					xtype: 'swtimefield'
				},
				{
					id: 'ttgsfEndDay',
					fieldLabel: lang['okonchanie_rabotyi'],
					labelWidth: 100,
					name: 'EndTime',
					plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
					tabIndex: TABINDEX_TTGSF + 6,
					validateOnBlur: false,
					value: '17:00',
					width: 60,
					xtype: 'swtimefield'
				},
				{
					id: 'ttgsfDuration',
					xtype: 'textfield',
					maskRe: /\d/,
					fieldLabel: lang['dlitelnost_priema_min'],
					minLength: 1,
					autoCreate: {tag: "input", type: "text", size: "3", maxLength: "3", autocomplete: "off"},
					width: 50,
					name: 'Duration',
					tabIndex: TABINDEX_TTGSF + 7,
					value: 15
				},
				{
					width: 245,
					xtype: 'swtimetabletypecombo',
					tabIndex: TABINDEX_TTGSF + 8,
					hiddenName: 'TimetableType_id',
					id: 'ttgsfTimetableType',
					allowBlank: false,
					listeners: {
						'change': function(combo, nv, ov) {
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == nv);
							});
							combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
						},
						'select': function (combo, record) {
							win.checkTimetableType();
						}
					},
				},
				{
					xtype: 'checkbox',
					readOnly: true,
					name: 'TimeTableGraf_IsMultiRec',
					fieldLabel: langs('Множественная запись'),
					listeners: {
						render: function() {
							win.checkTimetableType();
						}
					}
				},
				{
					//disabled: true,
					allowDecimals: false,
					allowNegative: false,
					disabledClass: 'field-disabled',
					fieldLabel: langs('Максимальное число записанных'),
					name: 'TimeTableGraf_PersRecLim',
					toUpperCase: true,
					width: 30,
					minValue: 2,
					maxValue: 100,
					xtype: 'numberfield',
					listeners: {
						render: function(nf) {
							win.checkTimetableType();
						}
					}
				}]
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				checkboxToggle: true,
				collapsed: true,
				title: 'Создать примечание',
				id: 'ttgsfCreateAnnotationPanel',
				labelWidth: 95,
				items: [{
					name: 'CreateAnnotation',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'ignore_doubles',
					value: 0,
					xtype: 'hidden'
				}, {
					codeField: 'AnnotationType_Code',
					displayField: 'AnnotationType_Name',
					allowBlank: false,
					editable: false,
					comboSubject: 'AnnotationType',
					fieldLabel: lang['tip'],
					hiddenName: 'AnnotationType_id',
					width: 300,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{AnnotationType_Code}</font>&nbsp;{AnnotationType_Name}',
						'</div></tpl>'
					),
					store: new Ext.db.AdapterStore({
						autoLoad: false,
						dbFile: 'Promed.db',
						fields: [
							{name: 'AnnotationType_id', mapping: 'AnnotationType_id'},
							{name: 'AnnotationType_Code', mapping: 'AnnotationType_Code'},
							{name: 'AnnotationClass_id', mapping: 'AnnotationClass_id'},
							{name: 'AnnotationType_Name', mapping: 'AnnotationType_Name'}
						],
						key: 'AnnotationType_id',
						sortInfo: {field: 'AnnotationType_Code'},
						tableName: 'AnnotationType'
					}),
					valueField: 'AnnotationType_id',
					listeners: {
						'change': function(combo, nv, ov) {
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == nv);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
						},
						'select': function (combo, record) {				
							var base_form = win.MainPanel.getForm();
							if (combo.getValue() > 0) {
								// Если примечание управляющее, то видимость только "Всем МО" #66569
								if (record.get('AnnotationClass_id') == 1) {
									base_form.findField('AnnotationVison_id').disable();
									base_form.findField('AnnotationVison_id').setValue(1);
								} else {
									base_form.findField('AnnotationVison_id').enable();
								}
							}
						}
					},
					xtype: 'swbaselocalcombo'
				}, {
					allowBlank: false,
					comboSubject: 'AnnotationVison',
					fieldLabel: lang['vidimost'],
					hiddenName: 'AnnotationVison_id',
					width: 180,
					xtype: 'swcommonsprcombo',
				}, {
					allowBlank: false,
					fieldLabel : lang['tekst'],
					width: 300,
					height: 70,
					name: 'Annotation_Comment',
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"},
					id: 'Annotation_Comment'
				}],
				listeners: {
					'beforecollapse': function(e) {
						var base_form = win.MainPanel.getForm();
						base_form.findField('AnnotationType_id').disable();
						base_form.findField('AnnotationVison_id').disable();
						base_form.findField('Annotation_Comment').disable();
						base_form.findField('CreateAnnotation').setValue(0);
						return false;
					},
				},
				onCheckClick: function(s, c) {
					var base_form = win.MainPanel.getForm();
					base_form.findField('AnnotationType_id').setDisabled(!c.checked);
					base_form.findField('AnnotationVison_id').setDisabled(!c.checked);
					base_form.findField('Annotation_Comment').setDisabled(!c.checked);
					base_form.findField('CreateAnnotation').setValue(c.checked?1:0);
					if (c.checked) {
						base_form.findField('AnnotationType_id').fireEvent('change', base_form.findField('AnnotationType_id'), base_form.findField('AnnotationType_id').getValue(), null);
					}
				}
			},
			new sw.Promed.ViewFrame({
				region: 'south',
				id: 'ttgsfCopyAnnotationGrid',
				object: 'Annotation',
				height: 150,
				dataUrl: '/?c=Annotation&m=loadList',
				editformclassname: '',
				selectionModel: 'multiselect',
				autoLoadData: false,
				saveAtOnce: false,
				focusOnFirstLoad: false,
				noSelectFirstRowOnFocus: true,
				useEmptyRecord: false,
				stringfields: [
					{ name: 'Annotation_id', key: true, type:'int', hidden: true },
					{ name: 'Annotation_Comment', header: lang['tekst'], width: 140},
					{ name: 'AnnotationType_Name', header: lang['tip'], width: 70},
					{ name: 'AnnotationVison_Name', header: lang['vidimost'], width: 70},
					{ name: 'Annotation_Date', header: lang['period_deystviya'], width: 130},
					{ name: 'Annotation_Time', header: lang['vremya_deystviya'], width: 80}
				],
				actions: [
					{name:'action_add', hidden: true},
					{name:'action_edit', hidden: true},
					{name:'action_view', hidden: true},
					{name: 'action_delete', hidden: true},
					{name: 'action_refresh'},
					{name: 'action_print'},
					{name: 'action_save', hidden: true}
				],
				getCheckedAnnotationList: function() {
					var selections = this.getGrid().getSelectionModel().getSelections(),
						checked = [];
					for (var key in selections) {
						if (selections[key].data) {
							checked.push(selections[key].data[this.jsonData['key_id']]);
						}
					}
					return Ext.util.JSON.encode(checked);
				},
				title: 'Копировать примечание',
				toolbar: false
			})
			],
			url: C_TTG_CREATESCHED
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [win.MainPanel],
			buttons:
			[{
				text: lang['sozdat_raspisanie'],
				id: 'ttgsfCreate',
				tabIndex: TABINDEX_TTGSF + 10,
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
				}.createDelegate(this),
				tabIndex: TABINDEX_TTGSF + 11
			},
			{
				text: BTN_FRMCANCEL,
				id: 'ttgsfCancel',
				tabIndex: TABINDEX_TTGSF + 12,
				iconCls: 'cancel16',
				onTabAction: function()
				{
					this.findById('ttgsfScheduleCreationType').focus();
				}.createDelegate(this),
				onShiftTabAction: function()
				{
					this.findById('ttgsfCreate').focus();
				}.createDelegate(this),
				handler: function()
				{
					this.hide();
					//this.returnFunc(this.owner, -1);
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
					return true;
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swTTGScheduleFillWindow.superclass.initComponent.apply(this, arguments);
	}
});