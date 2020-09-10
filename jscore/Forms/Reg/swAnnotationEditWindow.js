/**
* swAnnotationEditWindow - окно редактирования примечания на врача
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Aleksandr Chebukin 
* @version      06.11.2015
*/

/*NO PARSE JSON*/
sw.Promed.swAnnotationEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'AnnotationEditWindow',
	layout: 'border',
	maximizable: false,
	width: 470,
	height: 300,
	modal: true,
	codeRefresh: true,
	objectName: 'swAnnotationEditWindow',
	objectSrc: '/jscore/Forms/Reg/swAnnotationEditWindow.js',	
	returnFunc: function(owner) {},
	Annotation_id: null,
	MedStaffFact_id: null,
	MedService_id: null,
	Resource_id: null,
	action: 'add',
	show: function() {		
		sw.Promed.swAnnotationEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('AnnotationEditForm').getForm();
		base_form.reset();

		if (arguments[0]['action']) {
			this.action = arguments[0]['action'];
		}

		if (arguments[0]['callback']) {
			this.returnFunc = arguments[0]['callback'];
		}
		
		if (arguments[0]['Annotation_id']) {
			this.Annotation_id = arguments[0]['Annotation_id'];
		} else {
			this.Annotation_id = null;
		}
		
		if (arguments[0]['MedStaffFact_id']) {
			this.MedStaffFact_id = arguments[0]['MedStaffFact_id'];
		} else {
			this.MedStaffFact_id = null;
		}
		
		if (arguments[0]['MedService_id']) {
			this.MedService_id = arguments[0]['MedService_id'];
		} else {
			this.MedService_id = null;
		}
		
		if (arguments[0]['Resource_id']) {
			this.Resource_id = arguments[0]['Resource_id'];
		} else {
			this.Resource_id = null;
		}
		
		if (arguments[0]['Date']) {
			this.Date = arguments[0]['Date'];
		} else {
			this.Date = null;
		}
		
		if (arguments[0]['Annotation_begTime']) {
			this.Annotation_begTime = arguments[0]['Annotation_begTime'];
		} else {
			this.Annotation_begTime = null;
		}
		
		if (arguments[0]['Annotation_endTime']) {
			this.Annotation_endTime = arguments[0]['Annotation_endTime'];
		} else {
			this.Annotation_endTime = null;
		}
		
		if (arguments[0]['AnnotationType_id']) {
			this.AnnotationType_id = arguments[0]['AnnotationType_id'];
		} else {
			this.AnnotationType_id = null;
		}
		
		switch (this.action){
			case 'add':
				this.setTitle('Примечание: Добавление');
				break;
			case 'edit':
				this.setTitle('Примечание: Редактирование');
				break;
			case 'view':
				this.setTitle('Примечание: Просмотр');
				break;
		}
		
		if (this.action != 'add') {
			var loadMask = new Ext.LoadMask(Ext.get('AnnotationEditForm'), { msg: "Подождите, идет сохранение..." });
			this.findById('AnnotationEditForm').getForm().load({
				url: '/?c=Annotation&m=load',
				params: {
					Annotation_id: this.Annotation_id
				},
				success: function (form, action) {
					loadMask.hide();
					base_form.findField('AnnotationType_id').focus();
					base_form.findField('AnnotationType_id').fireEvent('change', base_form.findField('AnnotationType_id'), base_form.findField('AnnotationType_id').getValue());
				},
				failure: function (form, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
						this.hide();
					}
				},
				scope: this
			});		
		} else {
			base_form.findField('AnnotationType_id').focus();		
			base_form.findField('MedStaffFact_id').setValue(this.MedStaffFact_id);
			base_form.findField('MedService_id').setValue(this.MedService_id);
			base_form.findField('Resource_id').setValue(this.Resource_id);
			if (this.Date) {
				base_form.findField('Annotation_begDate').setValue(this.Date);
				base_form.findField('Annotation_endDate').setValue(this.Date);
			}
			if (this.AnnotationType_id) {
				base_form.findField('AnnotationType_id').setValue(this.AnnotationType_id);
			}
			if (this.Annotation_begTime) {
				base_form.findField('Annotation_begTime').setValue(this.Annotation_begTime);
			}
			if (this.Annotation_endTime) {
				base_form.findField('Annotation_endTime').setValue(this.Annotation_endTime);
			}
		}		
		
		if (this.action=='view') {
			base_form.findField('AnnotationType_id').disable();
			base_form.findField('Annotation_Comment').disable();
			base_form.findField('Annotation_begDate').disable();
			base_form.findField('Annotation_endDate').disable();
			base_form.findField('Annotation_begTime').disable();
			base_form.findField('Annotation_endTime').disable();
			base_form.findField('AnnotationVison_id').disable();
			this.buttons[0].disable();
		} else {
			base_form.findField('AnnotationType_id').enable();
			base_form.findField('Annotation_Comment').enable();
			base_form.findField('Annotation_begDate').enable();
			base_form.findField('Annotation_endDate').enable();
			base_form.findField('Annotation_begTime').enable();
			base_form.findField('Annotation_endTime').enable();
			base_form.findField('AnnotationVison_id').enable();
			this.buttons[0].enable();
		}
		
	},
	doSave: function() 
	{
		var win = this;
		var form = this.findById('AnnotationEditForm').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('AnnotationEditForm'), { msg: "Подождите, идет сохранение..." });
		var base_form = win.findById('AnnotationEditForm').getForm();
		var params = {};
		
		if (!form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('AnnotationEditForm').getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}		

		if (base_form.findField('AnnotationVison_id').disabled) {
			params.AnnotationVison_id = base_form.findField('AnnotationVison_id').getValue();
		}

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
						if ( action.result.Alert_Msg && 'YesNo' == action.result.Error_Msg ) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' && action.result.Error_Code == 112 ) {
										form.findField('ignore_doubles').setValue('1');
										win.doSave();
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: action.result.Alert_Msg,
								title: 'Продолжить сохранение?'
							});
						} else {
							win.hide();
							win.returnFunc();
						}
					}	
				}
				else {
					Ext.Msg.alert('Ошибка', 'При сохранении примечания произошла ошибка');
				}
							
			}.createDelegate(this)
		});
	},

	initComponent: function() {
	
		var win = this;
		
		this.MainPanel = new Ext.form.FormPanel({
			id:'AnnotationEditForm',
			border: false,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			bodyStyle: 'padding: 10px 5px 0',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 120,
			items:
			[{
				name: 'Annotation_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedStaffFact_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedService_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Resource_id',
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
				fieldLabel: 'Тип',
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
							base_form.findField('Annotation_begDate').setAllowBlank(record.get('AnnotationClass_id') != 1);
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
				fieldLabel : 'Текст',
				width: 300,
				height: 100,
				allowBlank: false,
				name: 'Annotation_Comment',
				xtype: 'textarea',
				autoCreate: {tag: "textarea", autocomplete: "off"}
			}, {
				xtype: 'panel',
				layout: 'column',
				border: false,
				items:
				[{
					xtype: 'panel',
					layout: 'form',
					labelWidth: 120,
					columnWidth: 0.55,
					border: false,
					items: 
					[{
						fieldLabel: 'Период действия',
						width: 100,
						name: 'Annotation_begDate',
						xtype: 'swdatefield'
					}]
				},
				{
					xtype: 'panel',
					border: false,
					columnWidth: 0.45,
					layout: 'form',
					labelWidth: 20,
					items: 
					[{
						fieldLabel: 'Период действия',
						width: 100,
						hideLabel: true,
						name: 'Annotation_endDate',
						xtype: 'swdatefield'
					}]
				}]
			}, {
				xtype: 'panel',
				layout: 'column',
				border: false,
				items:
				[{
					xtype: 'panel',
					layout: 'form',
					labelWidth: 120,
					columnWidth: 0.5,
					border: false,
					items: 
					[{
						fieldLabel: 'Время действия',
						width: 80,
						name: 'Annotation_begTime',
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						xtype: 'swtimefield',
						format: 'H:i'
					}]
				},
				{
					xtype: 'panel',
					border: false,
					columnWidth: 0.5,
					layout: 'form',
					labelWidth: 20,
					items: 
					[{
						fieldLabel: 'Время действия',
						width: 80,
						hideLabel: true,
						name: 'Annotation_endTime',
						plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
						xtype: 'swtimefield',
						format: 'H:i'
					}]
				}]
			}, {
				allowBlank: false,
				comboSubject: 'AnnotationVison',
				fieldLabel: 'Видимость',
				hiddenName: 'AnnotationVison_id',
				width: 180,
				xtype: 'swcommonsprcombo',
			}],
			reader: new Ext.data.JsonReader({},
			[
				{ name: 'Annotation_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedService_id' },
				{ name: 'Resource_id' },
				{ name: 'AnnotationType_id' },
				{ name: 'Annotation_Comment' },
				{ name: 'Annotation_begDate' },
				{ name: 'Annotation_endDate' },
				{ name: 'Annotation_begTime' },
				{ name: 'Annotation_endTime' },
				{ name: 'AnnotationVison_id' }
			]
			),
			url: '/?c=Annotation&m=save'
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel],
			buttons:
			[{
				text: '<u>С</u>охранить',
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
		sw.Promed.swAnnotationEditWindow.superclass.initComponent.apply(this, arguments);
	}
});