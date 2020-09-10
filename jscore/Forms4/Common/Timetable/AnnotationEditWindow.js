Ext6.define('common.Timetable.AnnotationEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swTimetableAnnotationEditWindow',
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new addDescritptionWindow',
	title: 'Примечание',
	width: 350,
	modal: true,
	
	save: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();
		var visonButton = me.down('[name=AnnotationVison_id]');
		var typeList = me.down('[name=annotationTypeSelect]');
		var typeSelection = typeList.selection;
		
		if (!baseForm.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.formPanel.getFirstInvalidEl().focus();
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return;
		}
		if (!typeSelection) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING,
				msg: 'Не выбрано примечание',
				title: ERR_INVFIELDS_TIT
			});
			return;
		}
		
		var params = {
			AnnotationType_id: typeSelection.get('id'),
			AnnotationVison_id: visonButton.getValue()
		};

		me.mask('Сохранение...');

		baseForm.submit({
			params: params,
			url: '/?c=Timetable6E&m=saveAnnotation',
			success: function(form, action) {
				me.unmask();
				me.callback();
				me.hide();
			},
			failure: function(form, action) {
				me.unmask();
			}
		});
	},
	
	addCustomType: function(name, callback) {
		callback = callback || Ext6.emptyFn;
		var me = this;
		var typeList = me.down('[name=annotationTypeSelect]');
		
		if (!name || !name.trim()) {
			return;
		}
		
		me.mask('Сохранение...');
		
		Ext6.Ajax.request({
			url: '/?c=Timetable6E&m=addAnnotationTypeCustom',
			params: {
				name: name
			},
			success: function(response) {
				me.unmask();
				var result = Ext6.decode(response.responseText);
				var id = result.AnnotationType_id;
				
				me.TypeStore.load({
					callback: function() {
						typeList.select(typeList.store.getById(id));
						callback();
					}
				});
			},
			failure: function(response) {
				me.unmask();
			}
		});
	},
	
	deleteCustomType: function(id) {
		var me = this;
		
		me.mask('Удалене...');
		
		Ext6.Ajax.request({
			url: '/?c=Timetable6E&m=deleteAnnotationTypeCustom',
			params: {
				id: id
			},
			success: function(response) {
				me.unmask();
				me.TypeStore.load();
			},
			failure: function(response) {
				me.unmask();
			}
		});
	},
	
	enableEdit: function(enable) {
		var me = this;
	
		me.down('[cls=buttonAccept]').setVisible(enable);
		
		me.down('[cls=addBoundlist]').setDisabled(!enable);
		me.down('[name=AnnotationVison_id]').setDisabled(!enable);
		me.down('[name=RangeType]').setDisabled(!enable);
		me.down('[name=annotationTypeSelect]').setDisabled(!enable);
		
		me.query('field').forEach(function(field) {
			field.setDisabled(!enable);
		});
	},
	
	onSprLoad: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();
		
	},
	
	show: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();
		
		me.action = 'view';
		me.callback = Ext6.emptyFn;
	
		me.callParent(arguments);
		
		if (arguments[0].action) {
			me.action = arguments[0].action;
		}
		if (Ext6.isFunction(arguments[0].callback)) {
			me.callback = arguments[0].callback;
		}
		
		var formParams = arguments[0].formParams;
		
		baseForm.reset();
		baseForm.setValues(formParams);
		
		baseForm.findField('Annotation_begDate').setMinValue(new Date());
		baseForm.findField('Annotation_endDate').setMinValue(new Date());
		
		me.TypeStore.load();
		
		switch(me.action) {
			case 'add':
				me.enableEdit(true);
			
				if (formParams.RangeType) {
					me.down('[name=RangeType]').setValue(formParams.RangeType);
				}
				
				break;
			case 'edit':
			case 'view':
				me.enableEdit(me.action == 'edit');
				
				baseForm.load({
					params: {
						Annotation_id: baseForm.findField('Annotation_id').getValue()
					},
					success: function(form, action) {
						var result = action.result.data;
					
						me.down('[name=AnnotationVison_id]').setValue(Number(result.AnnotationVison_id));
						
						var annotationType = me.down('[name=annotationTypeSelect]').store.getById(result.AnnotationType_id);
						me.down('[name=annotationTypeSelect]').select(annotationType);
					},
					failure: function() {
						
					}
				});
				break;
		}
	},
	
	initComponent: function() {
		var me = this;
		
		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '25 30 0 30',
			trackResetOnLoad: false,
			url: '/?c=Timetable6E&m=loadAnnotationEditForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'Annotation_id'},
						{name: 'MedStaffFact_id'},
						{name: 'RangeType'},
						{name: 'RangeType'},
					]
				})
			}),
			items: [{
				xtype: 'hidden',
				name: 'Annotation_id'
			}, {
				xtype: 'hidden',
				name: 'MedStaffFact_id'
			}, {
				layout: 'hbox',
				border: false,
				margin: '0 0 8 0',
				items: [{
					xtype: 'label',
					margin: '5 5 0 0',
					text: 'Примечание:',
					style: {color: '#666'},
					width: 85
				}, {
					xtype: 'segmentedbutton',
					cls: 'segmentedButtonGroup segmentedButtonGroupMini',
					flex: 1,
					name: 'RangeType',
					value: 'day',
					items: [{
						text: 'На день',
						value: 'day'
					}, {
						text: 'На бирки',
						value: 'timetable'
					}]
				}]
			}, {
				layout: 'hbox',
				border: false,
				margin: '0 0 8 0',
				items: [{
					allowBlank: false,
					xtype: 'datefield',
					name: 'Annotation_begDate',
					cls: 'date-field',
					fieldLabel: 'Начало',
					flex: 1,
					labelWidth: 85,
					margin: '0 5 0 0'
				}, {
					xtype: 'swTimeField',
					name: 'Annotation_begTime',
					cls: 'date-field',
					width: 76
				}]
			}, {
				layout: 'hbox',
				border: false,
				margin: '0 0 8 0',
				items: [{
					allowBlank: false,
					xtype: 'datefield',
					name: 'Annotation_endDate',
					cls: 'date-field',
					fieldLabel: 'Окончание',
					flex: 1,
					labelWidth: 85,
					margin: '0 5 0 0'
				}, {
					xtype: 'swTimeField',
					name: 'Annotation_endTime',
					cls: 'date-field',
					width: 76
				}]
			}, {
				layout: 'hbox',
				border: false,
				margin: '0 0 8 0',
				items: [{
					xtype: 'label',
					margin: '5 5 0 0',
					text: 'Видимость:',
					style: {color: '#666'},
					width: 85
				}, {
					xtype: 'segmentedbutton',
					cls: 'segmentedButtonGroup segmentedButtonGroupMini',
					flex: 1,
					name: 'AnnotationVison_id',
					value: 2,
					items: [{
						text: 'Все МО',
						value: 2,
						width: 75
					}, {
						text: 'Только своя МО',
						value: 3
					}]
				}]
			}]
		});
		
		me.TypeTpl = new Ext6.XTemplate(
			'<tpl>',
			'{[ this.checkTypeDescription(values, xindex) ]}',
			'</tpl>', 
			{
				checkTypeDescription(values, xindex) {
					var str = '';
					values.forEach((value, key) => {
						if (value.isCustom) {
							str += '<div class="x6-boundlist-item customDescr">' +
								'<p class="custom-description">' +
								'<span>' + value.name + '</span>' +
								'<i class="delete-custom-descr" onClick="Ext6.getCmp(\'' + me.id + '\').deleteCustomType(' + value.id +')"></i>' +
								'</p>' +
								'</div>';
						} else {
							str += '<div class="x6-boundlist-item">' + value.name + '</div>';
						}
					});
					return str;
				}
			}
		);
		
		me.TypeStore = Ext6.create('Ext6.data.Store', {
			autoLoad: false,
			proxy: {
				type: 'ajax',
				url: '/?c=Timetable6E&m=loadAnnotationTypeList',
				reader: {type: 'json'}
			},
			fields: [
				{name: 'id', type: 'int'},
				{name: 'name', type: 'string'},
				{name: 'isCustom', type: 'boolean'}
			]
		});
		
		me.TypePanel = Ext6.create('Ext6.panel.Panel', {
			layout: 'vbox',
			border: false,
			bodyPadding: '0 30 25 30',
			items: [{
				xtype: 'label',
				text: 'Выберите примечание:',
				style: {color: '#666'}
			}, {
				xtype: 'boundlist',
				width: '100%',
				height: 117,
				cls: 'custom-scroll descriptionComboStore',
				border: true,
				store: me.TypeStore,
				name: 'annotationTypeSelect',
				margin: '0 0 10 0',
				tpl: me.TypeTpl
			}, {
				xtype: 'textfield',
				width: '100%',
				cls: 'customDescriptionInput',
				fieldLabel: 'Новое примечание:',
				id: 'customDescription',
				margin: 0,
				emptyText: 'Добавить другое'
			}, {
				xtype: 'button',
				margin: '0 0 0 0',
				cls: 'addBoundlist',
				id: 'addCustomDescription',
				text: 'Сохранить в список примечаний',
				padding: '0 0 0 0',
				handler: function () {
					var field = me.down('#customDescription');
					if (field.getValue()) {
						me.addCustomType(field.getValue(), function() {
							field.setValue(null);
						});
					}
				}
			}]
		});
		
		Ext6.apply(me, {
			items: [
				me.formPanel,
				me.TypePanel
			],
			buttons: [
				'->',
				{
					text: 'Отмена',
					userCls: 'buttonCancel',
					margin: 0,
					handler: function() {
						me.hide();
					}
				}, {
					id: me.getId()+'-save-btn',
					cls: 'buttonAccept',
					margin: '0 19 0 0',
					text: 'Создать',
					handler: function() {
						me.save();
					}
				}
			]
		});
		
		me.callParent(arguments);
	}
});