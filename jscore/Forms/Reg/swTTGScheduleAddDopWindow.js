/**
* swTTGScheduleAddDopWindow - окно добавления дополнительной бирки
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
sw.Promed.swTTGScheduleAddDopWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['dobavlenie_dopolnitelnoy_birki'],
	id: 'TTGScheduleAddDopWindow',
	layout: 'border',
	maximizable: false,
	width: 450,
	height: 270,
	modal: true,
	resizable: false,
	codeRefresh: true,
	objectName: 'swTTGScheduleAddDopWindow',
	objectSrc: '/jscore/Forms/Reg/swTTGScheduleAddDopWindow.js',
	
	/**
	 * Идентификатор места работы, с расписанием которого мы работаем
	 */
	MedStaffFact_id: null,
	
	/**
	 * Дата, на которую создаётся дополнительная бирка
	 */
	date: null,
	
	returnFunc: function(owner) {},
	show: function() 
	{
		sw.Promed.swTTGScheduleAddDopWindow.superclass.show.apply(this, arguments);
		
		var form = this.findById('TTGScheduleAddDopForm');
		base_form = form.getForm();
		this.findById('ttgsfaCreateAnnotationPanel').collapse();
		this.findById('ttgsfaCreateAnnotationPanel').checkbox.dom.checked = false;
		base_form.findField('AnnotationType_id').setValue(4);
		base_form.findField('AnnotationVison_id').setValue(null);
		base_form.findField('Annotation_Comment').setValue(null);
		base_form.findField('ignore_doubles').setValue(0);
		
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['MedStaffFact_id']) {
			this.MedStaffFact_id = arguments[0]['MedStaffFact_id'];
		}
		
		if (arguments[0]['date']) {
			this.date = arguments[0]['date'];
		}
	},
	doSave: function() 
	{
		var win = this;
		var form = this.findById('TTGScheduleAddDopForm');
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
		
		var loadMask = new Ext.LoadMask(Ext.get('TTGScheduleAddDopForm'), { msg: "Подождите, идет создание новых бирок..." });
		loadMask.show();
		
		var post = [];
		post['MedStaffFact_id'] = this.MedStaffFact_id;
		post['Day'] = this.date;
		post['AnnotationVison_id'] = form.getForm().findField('AnnotationVison_id').getValue();
		form.getForm().submit({
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
	var MainPanel = new sw.Promed.FormPanel(
		{
			id:'TTGScheduleAddDopForm',
			height:this.height, 
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			labelWidth: 80,
			items:
			[{
				fieldLabel: lang['vremya_birki'],
				name: 'StartTime',
				plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
				validateOnBlur: false,
				value: '08:00',
				width: 60,
				xtype: 'swtimefield'
			},
			{
				xtype: 'fieldset',
				height: 'auto',
				checkboxToggle: true,
				collapsed: true,
				title: 'Создать примечание',
				id: 'ttgsfaCreateAnnotationPanel',
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
							var base_form = MainPanel.getForm();
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
					autoCreate: {tag: "textarea", autocomplete: "off"}
				}],
				listeners: {
					'beforecollapse': function(e) {
						var base_form = MainPanel.getForm();
						base_form.findField('AnnotationType_id').disable();
						base_form.findField('AnnotationVison_id').disable();
						base_form.findField('Annotation_Comment').disable();
						base_form.findField('CreateAnnotation').setValue(0);
						return false;
					},
				},
				onCheckClick: function(s, c) {
					var base_form = MainPanel.getForm();
					base_form.findField('AnnotationType_id').setDisabled(!c.checked);
					base_form.findField('AnnotationVison_id').setDisabled(!c.checked);
					base_form.findField('Annotation_Comment').setDisabled(!c.checked);
					base_form.findField('CreateAnnotation').setValue(c.checked?1:0);
					if (c.checked) {
						base_form.findField('AnnotationType_id').fireEvent('change', base_form.findField('AnnotationType_id'), base_form.findField('AnnotationType_id').getValue(), null);
					}
				}
			}],
			url: C_TTG_ADDDOP
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [MainPanel],
			buttons:
			[{
				text: lang['dobavit'],
				id: 'ttgadCreate',
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
				id: 'ttgadCancel',
				iconCls: 'cancel16',
				onTabAction: function()
				{
					this.findById('ttgadScheduleCreationType').focus();
				}.createDelegate(this),
				onShiftTabAction: function()
				{
					this.findById('ttgadCreate').focus();
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
		sw.Promed.swTTGScheduleAddDopWindow.superclass.initComponent.apply(this, arguments);
	}
});