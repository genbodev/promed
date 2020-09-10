Ext.define('sw.PersonField', {
	extend: 'Ext.container.Container',
	alias: 'widget.PersonField',
	name:'PersonContainer',
	margin: '0 0 5 0',
	layout: {
		type: 'hbox',
		align: 'stretch'
	},
	//Поиск человека по базе
	//Параметры:
	//	dete.callback [function] - функция, вызываемая после успешного поиска человека
	searchPerson: function(data) {
		Ext.create('sw.tools.subtools.swPersonWinSearch',
		{
				callback: data.callback
		}).show()
	},
	searchCallback: Ext.emptyFn,
	onChange: Ext.emptyFn,
	fieldLabel:'Исследуемое лицо',
	idName: 'Person_id',
	FioName: 'Person_FIO',
	allowBlank: true,
	labelWidth: 250,
	extraItems: [],
	//Возможность редактирования текстового поля (свободный ввод)
	editable: false,
	initComponent: function() {
		var me = this;
		
		var items = [{
				flex: 1,
				labelAlign: 'left',
				labelWidth: me.labelWidth,
				xtype: 'textfield',
				name: me.FioName,
				allowBlank: me.allowBlank,
				readOnly: !me.editable,
				fieldLabel: me.fieldLabel,
				margin: '0 5 0 0',
				listeners: {
					focus: function(field,focusEvt,evtOpts){
						
						//Если поле редактируемое, то вызывать окно поиска будем только по нажатию кнопки поиска явно
						if (me.editable) {
							return false;
						}
						
						var Person_id =field.up('container').down('[name='+me.idName+']'),
							Person_FIO = field;
						me.searchPerson({callback: function(result){
							if (result)	{
								if (result.Person_id) {
									Person_id.setValue(result.Person_id);
								}
								Person_FIO.setValue(
									(result.PersonSurName_SurName ? result.PersonSurName_SurName : '')+' '+
									(result.PersonFirName_FirName ? result.PersonFirName_FirName : '')+' '+
									(result.PersonSecName_SecName ? result.PersonSecName_SecName : '')
								)
							}
							me.searchCallback(field,result);
						}});
					},
					change: function(){
						me.onChange(arguments)
					}
				}
			},{
				xtype: 'hidden',
				name: me.idName,
				value: 0
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				iconCls: 'search16',
				name: 'searchbutton',
				tooltip: 'Поиск человека',
				handler: function(btn,evnt) {
					var Person_id =btn.up('container').down('[name='+me.idName+']'),
						Person_FIO = btn.up('container').down('[name='+me.FioName+']');
					me.searchPerson({callback: function(result){
						if (result)	{
							if (result.Person_id) {
								Person_id.setValue(result.Person_id);
							}
							Person_FIO.setValue(
								(result.PersonSurName_SurName ? result.PersonSurName_SurName : '')+' '+
								(result.PersonFirName_FirName ? result.PersonFirName_FirName : '')+' '+
								(result.PersonSecName_SecName ? result.PersonSecName_SecName : '')
							);
							me.searchCallback(btn,result);
						}
					}});
				}
			}];
		
		for (var i=0; i<me.extraItems.length; i++) {
			items.push(me.extraItems[i]);
		}
		
		Ext.apply(me,{
			items: items
		})
		
		me.callParent(arguments);
	}
});


Ext.define('sw.BSMEPersonField', {
	extend: 'sw.PersonField',
	alias: 'widget.BSMEPersonField',
	name:'BSMEPersonContainer',
	searchPerson: function(data) {
		Ext.create('common.BSME.tools.swBSMEPersonWinSearch',
		{
			callback: data.callback
		}).show()
	}
});

//поле хитрожйе
//сделано дабы не плодить hiden fields
Ext.define('sw.DoubleValueTriggerField', {
	extend: 'Ext.form.field.Trigger',
	alias: 'widget.DoubleValueTriggerField',
	triggerCls: 'x-form-search-trigger',
	enableKeyEvents: true,
	hiddenValue: '',
	hiddenFieldName: null,
	setValue: function(displayValue, hiddenValue){
		var me = this;
		
        me.value = displayValue;
		me.hiddenValue = hiddenValue?hiddenValue:displayValue;
        me.checkChange();
		me.setRawValue(displayValue)
        return me;
	},
	getValue: function() {
		 var me = this,
            val = me.hiddenValue?me.hiddenValue:me.rawToValue(me.processRawValue(me.getRawValue()));
        me.value = val;
        return val;
    }
});


Ext.define('sw.DateField',{
	extend: 'Ext.form.field.Date',
	alias: 'widget.swdatefield',
	format: 'd.m.Y',
	enableKeyEvents: true,
	startDay: 1,
	invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
	//Обработчики событий придется написать в initComponente
	//Иначе если у внешнего объекта указаны "defaults: { listeners: ... "
	//Они целиком переезжают обработчики в описании расширения 
	listenersParam: {
		keydown: function(field, e, eOpts){
			if (e.getKey() == e.F4) {
				field.setValue(new Date());
			}
		}
	},
	initComponent: function() {
		var me = this;

		if(me.triggerClear){

			me.trigger2Cls =  'clearTextfieldButton';

			me.onTrigger2Click = function() {
				this.setValue('');
				this.selectedRecord = null;
				this.fireEvent('triggerClick');
				me.focus(false, 200);
			};

			me.on('focus', function(){
				me.checkTriggerButton(true);
			})
			me.on('blur', function(){
				me.checkTriggerButton(false);
			})
			me.on('render', function(cmp, opts){
				//this.triggerCell.elements[0].hide();
				me.checkTriggerButton(false);
				cmp.mon(cmp.el, 'mouseover', function (event, html, eOpts) {
					me.checkTriggerButton(true);
				});
				cmp.mon(cmp.el, 'mouseleave', function (event, html, eOpts) {
					me.checkTriggerButton(false);
				});
			});
		}
		
		me.validateValue = function(value) {
			var me = this,
				errors = me.getErrors(value),
				isValid = me.allowBlank?( (me.value=='__.__.____')||(me.value=='')||(typeof(me.value)=='undefined')||(Ext.isEmpty(errors))):Ext.isEmpty(errors);
			
			if (!me.preventMark) {
				if (isValid) {
					me.clearInvalid();
				} else {
					me.markInvalid(errors);
				}
			}
			return isValid;
		};

		me.validate = function() {
			var me = this,
				// поправленная валидация - если не указан allowBlank = true, значит строгая валидация
				// иначе допускаем пробелы
				isValid = me.allowBlank?(me.value=='__.__.____'||me.isValid()):me.isValid();
				
			if (isValid !== me.wasValid) {
				me.wasValid = isValid;
				me.fireEvent('validitychange', me, isValid);
			}
			
			if(me.allowBlank && (me.value=='__.__.____')){
				me.value = '';
			}

			return isValid;
		};
		
		//Если прописывать plugins в описании класса,
		//то содается единственный экземпляр плагина на все объекты расширяемого класса
		Ext.apply(me,{
			plugins: [new Ux.InputTextMask('99.99.9999')]
		})

		for (var key in me.listenersParam ) {
			if (me.listenersParam.hasOwnProperty(key)) {
				me.on(key,me.listenersParam[key]);
			}
		}
	
		me.callParent(arguments);
		
	},
	checkTriggerButton: function(display) {
		var triggerCell = this.triggerCell.elements[1];
		if (!this.readOnly && this.triggerClear) {
			if (this.getRawValue().length > 0) {
				if (display) {
					triggerCell.show();
					triggerCell.removeCls('hiddenTriggerWrap');
					triggerCell.addCls('visibleDateTriggerWrap');
				} else {
					triggerCell.hide();
					triggerCell.addCls('hiddenTriggerWrap');
					triggerCell.removeCls('visibleDateTriggerWrap');
				}
			} else {
				triggerCell.hide();
				triggerCell.addCls('hiddenTriggerWrap');
				triggerCell.removeCls('visibleDateTriggerWrap');
			}
		}
		else{
			triggerCell.hide();
			triggerCell.addCls('hiddenTriggerWrap');
			triggerCell.removeCls('visibleDateTriggerWrap');
		}
	},

})

Ext.define('sw.TimeField',{
	extend: 'Ext.form.field.Date',
	alias: 'widget.swtimefield',
	format: 'H:i',
	hideTrigger: false,
	triggerCls: 'x-form-time-trigger',
	enableKeyEvents: true,
	invalidText: 'Неправильный формат времени. Время должно быть указано в формате ЧЧ:ММ',
	//Обработчики событий придется написать в initComponente
	//Иначе если у внешнего объекта указаны "defaults: { listeners: ... "
	//Они целиком переезжают обработчики в описании расширения 
	listenersParam: {
		keydown: function(field, e, eOpts){
			if (e.getKey() == e.F4) {
				field.setValue(new Date());
			}
		}
	},
	onTriggerClick: function() {
		this.setValue(new Date());
    },
	initComponent: function() {
		var me = this;
		
		me.validateValue = function(value) {
			var me = this,
				errors = me.getErrors(value),
				isValid = me.allowBlank?(  (me.value=='__:__') || (me.value=='') || (typeof(me.value)=='undefined') || Ext.isEmpty(errors)  ):Ext.isEmpty(errors);
			
			if (!me.preventMark) {
				if (isValid) {
					me.clearInvalid();
				} else {
					me.markInvalid(errors);
				}
			}
			return isValid;
		};
		
		me.validate = function() {
			var me = this,
				// поправленная валидация - если не указан allowBlank = true, значит строгая валидация
				// иначе допускаем пробелы
				isValid = me.allowBlank?(me.value=='__:__'||me.value==''||me.isValid()||typeof(me.value)=='undefined'):me.isValid();
				
			if (isValid !== me.wasValid) {
				me.wasValid = isValid;
				me.fireEvent('validitychange', me, isValid);
			}
			
			if(me.allowBlank && (me.value=='__:__')){
				me.value = '';
			}
			
			isValid?me.clearInvalid():false;

			return isValid;
		};
		
		//Если прописывать plugins в описании класса,
		//то содается единственный экземпляр плагина на все объекты расширяемого класса
		Ext.apply(me,{
			plugins: [new Ux.InputTextMask('99:99')]
		})
		for (var key in me.listenersParam ) {
			if (me.listenersParam.hasOwnProperty(key)) {
				me.on(key,me.listenersParam[key]);
			}
		}
		
		me.callParent(arguments);
	}
})

Ext.define('sw.DateTimeField',{
	extend: 'Ext.form.field.Date',
	alias: 'widget.swdatetimefield',
	format: 'd.m.Y H:i:s',
	hideTrigger: false,
	maskMarker: '__.__._____ __:__:__',
	triggerCls: 'x-form-clock-trigger',
	cls: 'stateCombo',
	enableKeyEvents: true,
	invalidText: 'Неправильный формат даты / времени.',
	listenersParam: {
		keydown: function(field, e, eOpts){
			if (e.getKey() == e.F4) {
				field.setValue(new Date());
			}
		}
	},
	onTriggerClick: function() {
		this.setValue(new Date());
    },
	initComponent: function() {
		var me = this;
		
		me.validateValue = function(value) {
			var me = this,
				errors = me.getErrors(value),
				isValid = me.allowBlank?(  (me.value==this.maskMarker) || (me.value=='') || (typeof(me.value)=='undefined') || Ext.isEmpty(errors)  ):Ext.isEmpty(errors);
			
			if (!me.preventMark) {
				if (isValid) {
					me.clearInvalid();
				} else {
					me.markInvalid(errors);
				}
			}
			return isValid;
		};
		
		me.validate = function() {
			var me = this,
			isValid = me.allowBlank?(me.value==this.maskMarker||me.value==''||me.isValid()||typeof(me.value)=='undefined'):me.isValid();
				
			if (isValid !== me.wasValid) {
				me.wasValid = isValid;
				me.fireEvent('validitychange', me, isValid);
			}
			
			if(me.allowBlank && (me.value==this.maskMarker)){
				me.value = '';
			}
			
			isValid?me.clearInvalid():false;

			return isValid;
		};
		
		Ext.apply(me,{
			plugins: [new Ux.InputTextMask('99.99.9999 99:99:99')]
		})
		for (var key in me.listenersParam ) {
			if (me.listenersParam.hasOwnProperty(key)) {
				me.on(key,me.listenersParam[key]);
			}
		}
		
		me.callParent(arguments);
	}
});
	
Ext.define('sw.OrgField', {
	extend: 'Ext.container.Container',
	alias: 'widget.orgfield',
	name:'OrgContainer',
	margin: '0 0 5 0',
	layout: {
		type: 'hbox',
		align: 'stretch'
	},
	fieldLabel:'Организация',
	comboName: 'Org_id',
	allowBlank: true,
	labelWidth: 250,
	extraItems: [],
	onChange: Ext.emptyFn,
	initComponent: function() {
		var me = this;
		
		var items = [{
			flex: 1,
			labelWidth: me.labelWidth,
			xtype: 'dOrgCombo',
			allowBlank: me.allowBlank,
			fieldLabel: me.fieldLabel,
			name: me.comboName,
			listeners: {
				change: (me.onChange && (typeof me.onChange == 'function')) ? me.onChange : Ext.emptyFn
			}
		},{
			margin: '0 0 0 10',
			xtype: 'button',
			iconCls: 'add16',
			tooltip: 'Добавить ораганизацию',
			handler: function(btn,evnt){
				Ext.create('sw.tools.subtools.swOrgEditWindow',{action: 'add'}).show();
			}
		}];
		
		for (var i=0; i<me.extraItems.length; i++) {
			items.push(me.extraItems[i]);
		}
		
		Ext.apply(me,{
			items: items
		})
		
		me.callParent(arguments);
	}
});

Ext.define('sw.ExtendedStore', {
	alias: 'widget.extendedstore',
	extend:  Ext.data.Store,
	constructor: function(config) {
        var me = this;
        me.callParent([config]);
        me.on({
            'beforeload': function(store, operation) {
                store.lastOperation = operation;
            }
        });
    },
    abort: function() {
        var me = this;
        if (me.loading && me.lastOperation) {
            var requests = Ext.Ajax.requests;
            for (var id in requests) {
                if (requests.hasOwnProperty(id) && requests[id].options == me.lastOperation.request) {
                    Ext.Ajax.abort(requests[id]);
                    delete requests[id];
					return me;
                }
            }
			return me;
        }
		return me;
    }
});