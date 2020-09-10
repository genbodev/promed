Ext6.define('swBaseCombobox', {
	extend: 'Ext6.form.ComboBox',
	alias: 'widget.baseCombobox',
	liquidLayout: false,
	matchFieldWidth: true,
	minMatchFieldWidth: true,
	submitIfDisabled: true,
	setFieldValue: function(fieldName, fieldValue) {
		var table = '';
		if (this.store && this.tableName) {
			table = this.tableName;
		}
		else {
			table = fieldName.substr(0, fieldName.indexOf('_'));
		}
		if (table.length > 0) {
			var idx = this.getStore().findBy(function (rec) {
				if (rec.get(fieldName) == fieldValue) {
					return true;
				}
				else {
					return false;
				}
			});
			var record = this.getStore().getAt(idx);
			if (record) {
				this.setValue(record.get(table + '_id'));
			}
			else {
				this.clearValue();
			}
		}
		else {
			if (IS_DEBUG) {
				console.warn('Наименование объекта (%o) не определено!', this);
				console.warn('Поле: %s', fieldName);
				console.warn('Значение: %s', fieldValue);
			}
		}
	},
	getFieldValue: function (fieldName) {
		if (!Ext6.isEmpty(this.getValue()) && this.getStore().getCount() > 0) {
			var record = this.getSelectedRecord();
			if (record) {
				return record.get(fieldName);
			}
			else {
				if (IS_DEBUG) {
					console.warn('Невозможно выбрать запись из Store комбобокса (%o) или поле %s отсутствует в Store!', this, fieldName);
					console.warn(record);
				}
			}
		}
		else {
			return null;
		}
	},
	onLoad: function(store, records, success) {
		var me = this;

		// добавляем пустую строку
		if (typeof me.insertAdditionalRecords == 'function') {
			me.insertAdditionalRecords();
		}

		me.callParent();
	},
	/**
	 * Получение первой не пустой записи из стора комбобокса
	 */
	getFirstRecord: function() {
		var me = this;
		if (me.store.getCount() > 0 && me.store.getAt(0).data[this.valueField] != "") {
			return me.store.getAt(0);
		} else if (me.store.getCount() > 1) {
			return me.store.getAt(1);
		} else {
			return null;
		}
	},
	insertAdditionalRecords: function() {
		this.insertEmptyRecord();
		this.insertAdditionalRecord();
	},
	insertAdditionalRecord: function() {
		if (this.additionalRecord && !this.store.getById(this.additionalRecord.value)) {
			var data = {};

			if (this.codeField && this.additionalRecord.code != undefined) {
				data[this.codeField] = this.additionalRecord.code;
			}
			data[this.valueField] = this.additionalRecord.value;
			data[this.displayField] = this.additionalRecord.text;
			data['additionalSortCode'] = -1;

			this.store.insert(0, data);
		}
	},
	insertEmptyRecord: function() {
		if (this.store.getCount() > 0 && this.store.getAt(0).data[this.valueField] != "" && this.allowBlank == true && this.hideEmptyRow != true) {
			var data = {};

			if (this.codeField) {
				data[this.codeField] = "";
			}
			data[this.valueField] = "";
			data[this.displayField] = "";
			data['additionalSortCode'] = -2;

			this.store.insert(0, data);
		}
	},
	listConfig: {
		getInnerTpl: function(displayField) {
			return '{' + displayField + '}\u00a0'; // чтобы пустая строка была корректной высоты
		}
	},
	minMatchFieldWidthFn: function() {
		var me = this;

		if (!me.matchFieldWidth && me.minMatchFieldWidth && Ext6.isEmpty(me.listConfig.minWidth)) {
			me.getPicker().setMinWidth(me.bodyEl.getWidth(true));
		}
	},
	initComponent: function() {
		var me = this;

		me.callParent(arguments);

		me.on({
			boxready: me.minMatchFieldWidthFn,
			resize: me.minMatchFieldWidthFn,
			scope: me
		});
	},
	// чуть изменяем стандартный метод, чтобы возвращать значения отключенных полей в form.getValues()
	// https://docs.sencha.com/extjs/6.5.3/classic/src/Field.js.html#Ext.form.field.Field-method-getSubmitData
	getSubmitData: function() {
		var me = this,
			data = null;
		if ( (me.submitIfDisabled || ! me.disabled) && me.submitValue) {
			data = {};
			data[me.getName()] = '' + me.getValue();
		}
		return data;
	},
	listeners: {
		change: function (c, v)
		{
			if (isNaN(Number(v))) // значние id должно быть числом
			{
				return;
			}
			// для корректной работы с viewModel
			try {
				// понятия не имею, почему s.petrov по #131172 добавил это сюда, но это взрывается
				if (this.up && typeof this.up === 'function' && this.up('form') && this.up('form').getViewModel())
				{
					this.up('form').getViewModel().set(this.name, v);
				}

				//typeof this.up === 'function' ? (this.up('form') ? this.up('form').isValid() : null) : null;
			} catch (e) {
				log(e);
			}


			return;
		}.createDelegate(this)
	}
});

Ext6.define('swBaseLocalCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.swBaseLocalCombo',
	allowTextInput: false,
	beforeBlur: function() {
		// медитируем
		return true;
	},
	triggerAction: 'all',
	minChars: 1,
	maxCount:null,
	forceSelection: true,
	mode: 'local',
	resizable: false,
	ctxSerach:false,
	selectOnFocus: true,
	enableKeyEvents: true,
	selectIndex: -1,
	codeAlthoughNotEditable: false,
	ignoreCodeField: false,
	setSelectIndex: function(idx)
	{
		this.selectIndex = idx;
	},
	getSelectedRecordData: function() {
		var combo = this;
		var value = combo.getValue();
		var data = {};
		if (value > 0) {
			var idx = this.getStore().findBy(function(record) {
				return (record.get(combo.valueField) == value);
			});
			if (idx > -1) {
				Ext6.apply(data, this.getStore().getAt(idx).data);
			}
		}
		return data;
	},
	setValue: function(v) {
		this.callParent(arguments);
		if ( (this.codeField) && (this.editable == false || this.codeAlthoughNotEditable) ) {
			var r = this.findRecord(this.valueField, v);
			var text;

			if ( r ) {
				if ( !this.ignoreCodeField && !Ext6.isEmpty(r.get(this.codeField)) && r.get(this.codeField).toString().length > 0 && r.get(this.codeField) != -1 ) {
					text = r.get(this.codeField).toString() + '. ' + r.get(this.displayField);
				}
				else {
					text = r.get(this.displayField);
				}

				if ( !Ext6.isEmpty(r.get(this.valueField)) && r.get(this.valueField).toString().length > 0 && this.el) {
					Ext6.form.ComboBox.superclass.setRawValue.call(this, text);
				}
			}
		}
	},
	getCode: function() {
		var me = this,
			value = me.getValue(),
			rec = me.getSelectedRecordData();
		return value ? rec[me.codeField] : '';
	},
	initComponent: function() {

		this.store = new Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'EvnPLDisp_id', type: 'int' },
				{ name: 'EvnPLDisp_setDate', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'EvnPLDisp_Name', type: 'string' }
			],
			autoLoad: false,
			sorters: {
				property: 'Resource_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=EvnPLDisp&m=loadEvnPLDispList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		this.clearBaseFilter = function() {
			this.baseFilterFn = null;
			this.baseFilterScope = null;
		};

		this.setBaseFilter = function(fn, scope) {
			this.baseFilterFn = fn;
			this.baseFilterScope = scope || this;
			this.store.filterBy(fn, scope);
		};

		// поиск по коду и контекстный поиск
		if ( this.editable === true ) {
			this.baseFilterFn = null;
			this.baseFilterScope = null;
			this.doQuery = function(q, forceAll) {
				if (q === undefined || q === null) q = '';

				var qe = {
					query: q,
					forceAll: forceAll,
					combo: this,
					cancel: false
				};

				if (this.fireEvent('beforequery', qe) === false || qe.cancel) return false;

				q = qe.query;
				forceAll = qe.forceAll;

				if (q.length >= this.minChars) {
					if (this.lastQuery != q) {
						this.lastQuery = q;
						this.selectedIndex = -1;
						var cnt = 0;
						this.getStore().filterBy(function(record, id) {
							var result = true;
							var patt;
							if (this.maxCount!=null&&cnt>this.maxCount) {
								return false;
							}
							if (typeof this.baseFilterFn == 'function') {
								result = this.baseFilterFn.call(this.baseFilterScope, record, id);
							}

							if (result) {
								if (this.ctxSerach) {
									patt = new RegExp(String(q).toLowerCase());
								} else {
									patt = new RegExp('^' + String(q).toLowerCase());
								}

								result = patt.test(String(record.get(this.displayField)).toLowerCase());

								if (!result && !Ext6.isEmpty(this.codeField)) {
									result = patt.test(String(record.get(this.codeField)).toLowerCase());
								}
							}
							if (result) cnt++;
							return result;
						}, this);

						this.onLoad();
					} else {
						this.selectedIndex = -1;
						this.onLoad();
					}
				}
			};

			if ( this.allowTextInput != true ) {
				this.addListener('blur', function(combo) {
					if (combo.getValue() == null || combo.getValue().toString().length == 0 || combo.getRawValue().toString().length == 0) {
						combo.setRawValue(null);
						combo.setValue('');
						combo.fireEvent('change', combo, 0, 1);
					}
				});
			}

			this.addListener('select', function(combo, record, index) {
				if ( typeof record == 'object' ) {
					if ( !combo.ignoreCodeField && record.get(combo.valueField).toString().length > 0 && record.get(combo.valueField) != -1 ) {
						if ( !Ext.isEmpty(combo.codeField) && record.get(combo.codeField) && record.get(combo.codeField).toString().length > 0 ) {
							combo.setRawValue(record.get(combo.codeField) + ". " + record.get(combo.displayField));
						} else {
							combo.setRawValue(record.get(combo.displayField));
						}
					}
				}
			});
		} else {
			this.addListener('blur', function(inp) {
				if ( inp.getRawValue() == '' ) {
					inp.setValue('');
					inp.setSelectIndex(-1);
					if (inp.onClearValue)
						this.onClearValue();
				}
				inp.setSelectIndex(-1);
				return false;
			});
			this.addListener('select', function(inp, record, index) {
				if ( (inp.codeField != undefined ) && ( inp.editable == false ) && (typeof record == 'object') ) {
					inp.setValue(record.data[inp.valueField]);
					if (record.data[inp.valueField] != "") {
						if (record.data[inp.valueField] != "") {
							if (!inp.ignoreCodeField && record.data[inp.codeField] != "" && record.data[inp.codeField] != -1) {
								inp.setRawValue(record.data[inp.codeField] + ". " + record.data[inp.displayField]);
							} else {
								inp.setRawValue(record.data[inp.displayField]);
							}
						}
					}
				}
			});
		}

		this.addListener('keydown', function(inp, e) {
			if ( e.getKey() == e.END) {
				this.inKeyMode = true;
				this.select(this.store.getCount() - 1);
			}
			if ( e.getKey() == e.HOME) {
				this.inKeyMode = true;
				this.select(0);
			}
			if ( e.getKey() == e.PAGE_UP) {
				this.inKeyMode = true;
				var ct = this.store.getCount();
				if (ct > 0) {
					if (this.selectedIndex == -1) {
						this.select(0);
					} else if (this.selectedIndex != 0) {
						if (this.selectedIndex-10>=0) {
							this.select(this.selectedIndex - 10);
						} else {
							this.select(0);
						}
					}
				}
			}
			if ( e.getKey() == e.PAGE_DOWN) {
				if (!this.isExpanded()) {
					this.onTriggerClick();
				} else {
					this.inKeyMode = true;
					var ct = this.store.getCount();
					if (ct > 0) {
						if (this.selectedIndex == -1) {
							this.select(0);
						} else if (this.selectedIndex != ct-1) {
							if (this.selectedIndex+10<ct-1) {
								this.select(this.selectedIndex + 10);
							} else {
								this.select(ct - 1);
							}
						}
					}
				}
			}
			if (e.editable ==false && e.getKey() == e.DELETE) {
				inp.setValue('');
				inp.setRawValue("");
				inp.setSelectIndex(-1);
				if (inp.onClearValue) this.onClearValue();
				e.stopEvent();
				return true;
			}
			if ( (inp.codeField == undefined ) || ( inp.editable !== false ) ) return true;
			if (e.altKey || e.ctrlKey || e.shiftKey) return true;
			if ( e.getKey() == e.BACKSPACE) {
				e.stopEvent();
				if ( inp.selectIndex == 0 ) {
					inp.setValue('');
					inp.setRawValue("");
					inp.setSelectIndex(-1);
					if (inp.onClearValue) this.onClearValue();
				}
				var reg = /^(\d+)[.]+/;
				var numbers = String(inp.getRawValue()).match(reg);

				if ( numbers != null) var first = numbers[1];
				else var first = -1;

				if ( first >= 10 ) {
					if ( String(first).length > inp.selectIndex && inp.selectIndex > 0 )
						first = String(first).substring(0, inp.selectIndex);

					var number = String(first).substring(0, String(first).length - 1);

					var idx = -1;
					var findIndex = 0;
					inp.getStore().findBy(function(r) {
						if ( r.data[inp.codeField] == number ) {
							idx = findIndex;
							return true;
						}
						findIndex++;
					});
					if ( idx == -1 ) {
						findIndex = 0;
						inp.getStore().findBy(function(r) {
							if ( String(number) == String(r.data[inp.codeField]).substring(0, String(number).length) ) {
								idx = findIndex;
								return true;
							}
							findIndex++;
						});
					}

					if (idx>=0) {
						inp.setSelectIndex(String(number).length);
						inp.setValue(inp.getStore().getAt(idx).data[inp.valueField]);
						inp.setRawValue(inp.getStore().getAt(idx).data[inp.codeField] + '. ' + inp.getStore().getAt(idx).data[inp.displayField]);
						inp.selectText(String(number).length, inp.getRawValue().length);
						inp.fireEvent('beforeselect', inp, inp.getStore().getAt(idx));
					}
				} else {
					if (first >= 0) {
						inp.setValue('');
						inp.setRawValue("");
						inp.setSelectIndex(-1);
						if (inp.onClearValue)
							this.onClearValue();
					}
				}
				return true;
			}

			if ( e.getKey() < 95 ) var number = e.getKey() - 48;
			else var number = e.getKey() - 96;

			if ( (number <= 9) && (number >= 0 ) ) {
				var reg = /^(\d+)[.]+/;
				var numbers = String(inp.getRawValue()).match(reg);

				if ( numbers != null) var first = numbers[1];
				else var first = -1;

				if ( first >= 0 && inp.selectIndex > 0 ) {
					first = String(first).substring(0, inp.selectIndex);
					number = String(first) + String(number);
				}

				var idx = -1;
				findIndex = 0;
				inp.getStore().findBy(function(r) {
					if ( String(number) == String(r.data[inp.codeField]).substring(0, String(number).length) ) {
						idx = findIndex;
						return true;
					}
					findIndex++;
				});
				if ( idx == -1 ) {
					var findIndex = 0;
					inp.getStore().findBy(function(r) {
						if ( r.data[inp.codeField] == number ) {
							idx = findIndex;
							return true;
						}
						findIndex++;
					});
				}

				if ( idx>=0 ) {
					if (inp.isExpanded)
						inp.collapse();
					inp.selectIndex = String(number).length;
					inp.setValue(inp.getStore().getAt(idx).data[inp.valueField]);
					if (inp.getStore().getAt(idx).data[inp.codeField] != "")
						inp.setRawValue(inp.getStore().getAt(idx).data[inp.codeField] + '. ' + inp.getStore().getAt(idx).data[inp.displayField]);
					else
						inp.setRawValue(inp.getStore().getAt(idx).data[inp.displayField]);
					inp.selectText(String(number).length, inp.getRawValue().length);
					inp.fireEvent('beforeselect', inp, inp.getStore().getAt(idx));
				}
			}
		});
		this.callParent(arguments);
	}
});

Ext6.define('swCommonSprCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.commonSprCombo',
	cls: '',
	queryMode: 'local',
	autoFilter: true,
	enableKeyEvents: true,
	store: null,
	fields: null,
	searchFn: Ext6.emptyFn(),
	comboSubject: '',
	prefix: '',
	suffix: '',
	displayField: '',
	valueField: '',
	sortField: '',
	codeField: '',
	sysNickField: '',
	displayCode: true,
	typeCode: 'string',
	moreFields: [],
	loadParams: {},
	listConfig: {
		cls: 'choose-bound-list-menu update-scroller'
	},
	setValueByCode: function(code) {
		var combo = this,
			rec = false;
		if(code && combo.codeField)
			rec = combo.getStore().findRecord(combo.codeField,code);
		if(rec)
			combo.setValue(rec.get(combo.valueField));
		else
			combo.reset()
	},
	filterFn: function(){return true;},
	initComponent: function() {
		var me = this;

		if (Ext6.isEmpty(me.displayField)) {
			me.displayField = me.comboSubject + '_Name';
		}

		if (Ext6.isEmpty(me.valueField)) {
			me.valueField = me.comboSubject + '_id';
		}

		if (Ext6.isEmpty(me.codeField)) {
			me.codeField = me.comboSubject + '_Code';
		}

		if (Ext6.isEmpty(me.sysNickField)) {
			me.sysNickField = me.comboSubject + '_SysNick';
		}

		if (Ext6.isEmpty(me.sortField)) {
			if (me.displayCode) {
				me.sortField = me.codeField;
			} else {
				me.sortField = me.displayField;
			}
		}

		if (Ext6.isEmpty(me.fields)) {
			if (me.displayCode) {
				var displayField = me.displayField;
				me.fields = [
					{name: me.valueField, type:'int'},
					{name: me.displayField, type:'string'},
					{name: 'displayField', type:'string', convert: function(val,row) {
						if (row.get(me.valueField)) {
							return row.get(me.codeField) + '. ' + row.get(displayField);
						} else {
							return '';
						}
					}},
					{name: me.codeField, type:me.typeCode},
					{name: me.sysNickField, type:'string'}
				];

				me.displayField = 'displayField';
			} else {
				me.fields = [
					{name: me.valueField, type:'int'},
					{name: me.displayField, type:'string'},
					{name: me.codeField, type:me.typeCode},
					{name: me.sysNickField, type:'string'}
				];
			}
		}

		if (me.fields)
		{
			me.fields = me.fields.concat(me.moreFields);
			me.fields = me.fields.concat([{name: 'additionalSortCode', type: 'int'}]);

			var tableName = this.prefix + this.comboSubject + this.suffix;
			var baseParams = Object.assign({}, me.loadParams);
			for (key in me.fields) {
				if (me.fields[key].name) {
					baseParams[me.fields[key].name] = '';
				}
			}
			baseParams['object'] = tableName;

			me.store = Ext6.create('Ext6.data.Store', {
				fields: me.fields,
				autoLoad: false,
				sorters: ['additionalSortCode', this.sortField],
				proxy: {
					type: 'ajax',
					url : '/?c=MongoDBWork&m=getData',
					reader: {
						type: 'json'
					},
					extraParams: baseParams
				},
				getById: function(id) {
					var indx = this.findBy(function(rec) {if(rec.data[me.valueField] == id) return rec;});
					if(indx>=0) return this.getAt(indx); else return false;
				},
				baseParams: baseParams,
				tableName: tableName,
				mode: me.queryMode
			});
			if(me.filterFn){
				me.store.clearFilter();
				me.store.filterBy(function(rec) {
					return me.filterFn(rec);
				});

			}
		}

		me.callParent(arguments);
	}
});

Ext6.define('swSurgTypeCombo', {
	extend: 'swCommonSprCombo',
	comboSubject: 'SurgType',
	alias: 'widget.swSurgTypeCombo',
	fieldLabel: 'Роль'
});

Ext6.define('swAnesthesiaClassCombo', {
	extend: 'swCommonSprCombo',
	comboSubject: 'AnesthesiaClass',
	alias: 'widget.swAnesthesiaClassCombo',
	fieldLabel: 'Вид анестезии'
});

Ext6.define('swResourceCombo', {
	forceSelection: true,
	triggerAction: 'all',
	codeField: 'Resource_id',
	displayField: 'Resource_Name',
	valueField: 'Resource_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swResourceCombo',
	queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'Ресурс',
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'Resource_id', mapping: 'Resource_id' },
				{ name: 'Resource_Name', mapping: 'Resource_Name', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'Resource_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=Resource&m=loadResourceList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});

Ext6.define('swStudyTargetCombo', {
	forceSelection: true,
	triggerAction: 'all',
	codeField: 'StudyTarget_id',
	displayField: 'StudyTarget_Name',
	valueField: 'StudyTarget_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swStudyTargetCombo',
	queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'Цель иссл.',
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item MedStaffFactCombo">',
		'<span>{[this.formatName(values)]}</span>',
		'</div></tpl>',
		{
			formatName: function(values) {
				if (values.StudyTarget_id != "") {
					return values.StudyTarget_id + '.' + values.StudyTarget_Name;
				}
			}
		}
	),
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			idProperty: 'StudyTarget_id',
			name: 'StudyTarget_Name',
			fields: [
				{ name: 'StudyTarget_id', mapping: 'StudyTarget_id' },
				{ name: 'StudyTarget_Name', mapping: 'StudyTarget_Name', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'StudyTarget_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=StudyTarget&m=loadStudyTargetList',
				reader: {
					type: 'json'
				}
			}
		});
		me.callParent(arguments);
	}
});

Ext6.define('swMedServiceCombo', {
	forceSelection: true,
	triggerAction: 'all',
	codeField: 'MedService_id',
	displayField: 'displayField',
	valueField: 'MedService_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swMedServiceCombo',
	queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'Служба',
	needDisplayLpu: function() {
		return false;
	},
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{
					name: 'displayField',
					type: 'string',
					convert: function(val, row) {
						var s = '';
						if (row.get('MedService_Nick')) {
							s = row.get('MedService_Nick');
							if (me.needDisplayLpu() && row.get('Lpu_Name')) {
								s = row.get('Lpu_Name') + ' / ' + s;
							}
						} else {
							return '';
						}

						return s;
					}
				},
				{ name: 'MedService_id', mapping: 'MedService_id' },
				{ name: 'Lpu_id', mapping: 'Lpu_id', type: 'int' },
				{ name: 'MedService_Nick', mapping: 'MedService_Nick', type: 'string'},
				{ name: 'Lpu_Name', mapping: 'Lpu_Name', type: 'string'},
				{ name: 'additionalSortCode', type: 'int'},
				{ name: 'RecordQueue_id', type: 'int'}
			],
			autoLoad: false,
			sorters: ['additionalSortCode', 'MedService_Nick'],
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=MedService&m=loadMedServiceList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});

Ext6.define('swEMDCertificateCombo', {
	forceSelection: true,
	triggerAction: 'all',
	codeField: 'EMDCertificate_id',
	displayField: 'EMDCertificate_Name',
	valueField: 'EMDCertificate_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swEMDCertificateCombo',
	queryMode: 'local',
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'Сертификат',
	triggers: {
		settings: {
			cls: 'x6-form-settings-trigger',
			handler: function(combo) {
				getWnd('swEMDCertificateViewWindow').show({
					pmUser_id: getGlobalOptions().pmuser_id,
					callback: function() {
						combo.getStore().reload();
					}
				});
			}
		}
	},
	listConfig:{
		minWidth: 600
	},
	tpl: new Ext6.XTemplate(
		'<table class="combo-table">',
		'<tr>',
		'<th style="width: 30%;">Наименование</th>',
		'<th style="width: 30%;">Кем выдан</th>',
		'<th style="width: 20%;">Дата выдачи</th>',
		'<th style="width: 20%;">Дата окончания</th>',
		'</tr>',
		'<tpl for=".">',
		'<tr class="x6-boundlist-item">',
		'<td>{EMDCertificate_Name}</td>',
		'<td>{EMDCertificate_Publisher}</td>',
		'<td>{[!Ext6.isEmpty(values.EMDCertificate_begDT) ? this.formatDate(values.EMDCertificate_begDT):""]}</td>',
		'<td>{[!Ext6.isEmpty(values.EMDCertificate_endDT) ? this.formatDate(values.EMDCertificate_endDT):""]}</td>',
		'</tr>',
		'</tpl>',
		'</table>',
		{
			formatDate: function(date) {
				return Ext6.util.Format.date(date, 'd.m.Y');
			}
		}
	),
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'EMDCertificate_id', mapping: 'EMDCertificate_id' },
				{ name: 'EMDCertificate_Name', mapping: 'EMDCertificate_Name' },
				{ name: 'EMDCertificate_Publisher', mapping: 'EMDCertificate_Publisher' },
				{ name: 'EMDCertificate_begDT', mapping: 'EMDCertificate_begDT', type: 'date', dateFormat: 'd.m.Y'},
				{ name: 'EMDCertificate_endDT', mapping: 'EMDCertificate_endDT', type: 'date', dateFormat: 'd.m.Y'},
				{ name: 'EMDCertificate_IsNotUse', mapping: 'EMDCertificate_IsNotUse'},
				{ name: 'EMDCertificate_SHA1', mapping: 'EMDCertificate_SHA1'},
				{ name: 'EMDCertificate_OpenKey', mapping: 'EMDCertificate_OpenKey'}
			],
			autoLoad: false,
			sorters: {
				property: 'EMDCertificate_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=EMD&m=loadEMDCertificateList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});

Ext6.define('swLpuBuildingCombo', {
	forceSelection: true,
	extend: 'swCommonSprCombo',
	comboSubject: 'LpuBuilding',
	displayField: 'LpuBuilding_Name',
	displayCode: false,
	alias: 'widget.swLpuBuildingCombo',
	fieldLabel: 'Подразделение',
	moreFields: [
		{name: 'LpuBuilding_id', type:'int'},
		{name: 'LpuBuilding_Code', type:'string'},
		{name: 'LpuBuilding_Name', type:'string'}
	]
});

Ext6.define('swLpuBuildingOfficeCombo', {
	forceSelection: true,
	extend: 'swBaseCombobox',
	displayField: 'LpuBuildingOffice_Number',
	valueField: 'LpuBuildingOffice_id',
	displayCode: false,
	alias: 'widget.swLpuBuildingOfficeCombo',
	fieldLabel: 'Кабинет приема',
	//listConfig: {resizable: true},
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'LpuBuildingOffice_id', type:'int'},
				{name: 'LpuBuildingOffice_Number', type:'string'},
				{name: 'LpuBuildingOffice_Comment', type:'string'},
				{name: 'LpuBuildingOffice_Name', type:'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'LpuBuildingOffice_Number',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=LpuBuildingOffice&m=loadLpuBuildingOfficeCombo',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	},
	setLoadParams: function(params){
		this.getStore().getProxy().extraParams = params;
	},
	tpl: new Ext6.XTemplate(
		'<tpl for=".">',
		'<div  class="x6-boundlist-item" style="width: 100%; font-size: 14px; font-weight:bold;">',
		'<div style="min-width: 20%; color: red; display: inline-block;">{LpuBuildingOffice_Number}</div>',
		'<div style="min-width: 80%; display: inline-block;">{LpuBuildingOffice_Name}</div>',
		'<div style="width: 100%; font-size: 12px; font-weight:normal;">',
		'<tpl if="LpuBuildingOffice_Comment == null || LpuBuildingOffice_Comment == \'\'">',
			'[ нет комментария ]',
		'<tpl else >',
			'[ {LpuBuildingOffice_Comment} ]',
		'</tpl>',
		'</div>',
		'</div></tpl>'
	),
});

Ext6.define('swEvnQueueStatusCombo', {
	extend: 'swCommonSprCombo',
	alias: 'widget.swEvnQueueStatusCombo',
	fieldLabel: langs('Статус листа ожидания'),
	comboSubject: 'EvnQueueStatus',
	displayField: 'EvnQueueStatus_Name',
	valueField: 'EvnQueueStatus_id',
	allowSysNick: true,
	useCommonFilter: false,
	loadParams: null,
	autoLoad: false,
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item">',
		'<table>',
		'<td>{EvnQueueStatus_Name}&nbsp;</td>',
		'</tr></table>',
		'</div></tpl>'
	),
});


Ext6.define('swLpuCombo', {
	forceSelection: true,
	extend: 'swCommonSprCombo',
	comboSubject: 'Lpu',
	displayField: 'Lpu_Nick',
	displayCode: false,
	alias: 'widget.swLpuCombo',
	fieldLabel: 'МО',
	moreFields: [
		{name: 'Lpu_id', mapping: 'Lpu_id'},
		{name: 'Lpu_IsOblast', mapping: 'Lpu_IsOblast'},
		{name: 'Lpu_Name', mapping: 'Lpu_Name'},
		{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
		{name: 'Lpu_Ouz', mapping: 'Lpu_Ouz'},
		{name: 'Lpu_RegNomC', mapping: 'Lpu_RegNomC'},
		{name: 'Lpu_RegNomC2', mapping: 'Lpu_RegNomC2'},
		{name: 'Lpu_RegNomN2', mapping: 'Lpu_RegNomN2'},
		{name: 'Lpu_isDMS', mapping: 'Lpu_isDMS'},
		{name: 'Lpu_DloBegDate', mapping: 'Lpu_DloBegDate', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'Lpu_DloEndDate', mapping: 'Lpu_DloEndDate', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'Lpu_IsAccess', mapping: 'Lpu_IsAccess'},
		{name: 'Lpu_IsNotForSystem', mapping: 'Lpu_IsNotForSystem'}
	]
});

Ext6.define('swOrgCombo', {
	extend: 'swBaseCombobox',
	forceSelection: true,
	displayField: 'Org_Nick',
	alias: 'widget.swOrgCombo',
	autoFilter: true,
	enableKeyEvents: true,
	cls: 'trigger-outside',
	fieldLabel: langs('Организация'),
	orgType: 'org',

	onlyFromDictionary: false,
	queryMode: 'local',
	triggers: {
		search: {
			cls: 'x6-form-search-trigger',
			extraCls: 'search-icon-out',
			handler: function ()
			{
				if (this.disabled) return false;

				var combo = this;

				getWnd('swOrgSearchWindowExt6').show({
					object: combo.orgType || 'lpu',
					onlyFromDictionary: combo.onlyFromDictionary ? true : false,

					onHide: function() {
						combo.focus(false);
					},
					onSelect: function(orgData) {
						combo.getStore().removeAll();
						combo.getStore().loadData([{
							Org_id: orgData.Org_id,
							Org_Name: orgData.Org_Name,
							Org_Nick: orgData.Org_Nick,
							Org_StickNick: orgData.Org_StickNick,
							OrgType_id: orgData.OrgType_id,
							Lpu_id: orgData.Lpu_id
						}]);

						combo.setValue(orgData[combo.idProperty()]);

						var index = combo.getStore().find('Org_id', orgData.Org_id);

						if (index == -1)
						{
							return false;
						}

						var record = combo.getStore().getAt(index);
						combo.fireEvent('select', combo, record, 0);
						combo.fireEvent('change', combo, combo.getValue());

						getWnd('swOrgSearchWindowExt6').hide();
					}
				});
			}
		},
		clear: {
			cls: 'x6-form-clear-trigger',
			handler: function ()
			{
				if (this.disabled) return false;

				this.clearValue();
			},
			hidden: true
		}
	},
	idProperty: function ()
	{
		return this.orgType === 'lpu' ? 'Lpu_id' : 'Org_id';
	},

	setValue: function(v) 	// переопределяем функцию, для того чтобы при загрзке формы услуга в комбике отображалась с именем
			{						// потому что при инициализации комбик без записей, а услуга не устанавливается
				var combo = this,
					args = arguments;

				if (v && this.getRawValue() != v && ! isNaN(v) && this.getStore().find( this.idProperty(), v) == -1)
				{
					this.getStore().load(
						{
							params:
								{
									Org_id: this.orgType !== 'org' ? v : null,
									Lpu_oid: this.orgType === 'lpu' ? v : null,
									OrgType: this.orgType || 'org'
								},
							callback: function (store, records, successful)
							{
								var rec = this.findRecord(combo.idProperty(), v);

								if (rec)
								{
									combo.setValue(rec);
								} else
								{
									combo.setValue(null);
								}
							}
						});
				}

				return this.callParent(args); // вызываем оригинальный setValue
	},

	initComponent: function ()
	{
		var me = this;

		this.valueField = this.idProperty();

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'Org_id', mapping: 'Org_id', type: 'int'},
				{name: 'OrgType_id', mapping: 'OrgType_id', type: 'int'},
				{name: 'Org_Name', mapping: 'Org_Name', type: 'string'},
				{name: 'Org_Nick', mapping: 'Org_Nick', type: 'string'},
				{name: 'Org_StickNick', mapping: 'Org_StickNick', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'Org_Nick',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=Org&m=getOrgList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});

Ext6.define('swOrgTypeCombo', {
	forceSelection: true,
	triggerAction: 'all',
	codeField: 'OrgType_id',
	displayField: 'OrgType_Name',
	valueField: 'OrgType_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swOrgTypeCombo',
	queryMode: 'local',
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'Тип организации',
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'OrgType_id', mapping: 'OrgType_id', type: 'int' },
				{ name: 'OrgType_Name', mapping: 'OrgType_Name', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'OrgType_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=Org&m=getOrgTypeList',
				reader: {
					type: 'json'
				}
			},
			mode: 'local'
		});

		me.callParent(arguments);
	}
});

Ext6.define('swARMTypeCombo', {
	forceSelection: true,
	triggerAction: 'all',
	codeField: 'ARMType_id',
	displayField: 'ARMType_Name',
	valueField: 'ARMType_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swARMTypeCombo',
	queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'Организация',
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'ARMType_id', mapping: 'ARMType_id' },
				{ name: 'ARMType_Code', mapping: 'ARMType_Code', type: 'string'},
				{ name: 'ARMType_Name', mapping: 'ARMType_Name', type: 'string'},
				{ name: 'ARMType_SysNick', mapping: 'ARMType_SysNick', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'ARMType_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=User&m=getPHPARMTypeList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});

Ext6.define('swUslugaComplexCombo', {
	forceSelection: true,
	triggerAction: 'all',
	codeField: 'UslugaComplex_Code',
	displayField: 'displayField',
	valueField: 'UslugaComplex_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swUslugaComplexCombo',
	queryMode: 'remote',
	minChars: 2,
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'Услуга',
	listConfig:{
		minWidth: 500
	},
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item MedStaffFactCombo">',
		'<table style="border: 0; width: 100%; border-spacing: 0px;">',
		'<tr>',
		'<td width="60%" style="padding-left: 15px; padding-right: 15px;"><div style="font: 13px/16px Roboto; font-weight: 400; color: #000;">{UslugaComplex_Name}</div></td>',
		'<td width="20%" style="padding-right: 15px; vertical-align: top;"><div style="font: 13px/16px Roboto; font-weight: 400;"><nobr style="color: #000;">{UslugaComplex_Code}</nobr></div></td>',
		'<td width="20%" style="padding-right: 15px; vertical-align: top;"><div style="font: 13px/16px Roboto; font-weight: 400;"><nobr style="color: #000;">{UslugaCategory_Name}</nobr></div></td>',
		'</tr>',
		/*'<tr>',
		//'<td width="70%" style="padding-left: 10px;"><p style="font: 11px Roboto; font-weight: 400; color: #000;">',
		// "{[values.UslugaComplex_Name.replace(new RegExp('(' + this.field.getRawValue().trim().replace(new RegExp(' ', 'g'), '|') + ')', 'ig'), '<span style=\"color:red;font-weight:900\">$1</span>')]}", // на случай если понадобится подсветка найденных частей названия услуги {[Ext.isEmpty(values.UslugaComplex_id)?"":"Категория услуги"]}
		//'{UslugaComplex_Name}',
		'</p></td>',
		'<td width="30%" style="padding-right: 10px;"><p style="font: 11px Roboto; font-weight: 400; color: #2196f3; padding-top: 5px;">&nbsp;</p></td>',
		'</tr>',*/'</table>',
		'</div></tpl>'
	),
	clearBaseParams: function() {
		this.lastQuery = 'This query sample that is not will never appear';

		this.getStore().getProxy().extraParams.allowedUslugaComplexAttributeList = null;
		this.getStore().getProxy().extraParams.allowedUslugaComplexAttributeMethod = 'or';
		this.getStore().getProxy().extraParams.allowMorbusVizitCodesGroup88 = 0;
		this.getStore().getProxy().extraParams.allowMorbusVizitOnly = 0;
		this.getStore().getProxy().extraParams.allowNonMorbusVizitOnly = 0;
		this.getStore().getProxy().extraParams.ignoreUslugaComplexDate = 0;
		this.getStore().getProxy().extraParams.disallowedUslugaComplexAttributeList = null;
		this.getStore().getProxy().extraParams.Mes_id = null;
		this.getStore().getProxy().extraParams.MesOldVizit_id = null;
		this.getStore().getProxy().extraParams.LpuLevel_Code = null;
		this.getStore().getProxy().extraParams.LpuSection_id = null;
		this.getStore().getProxy().extraParams.LpuSectionProfile_id = null;
		this.getStore().getProxy().extraParams.PayType_id = null;
		this.getStore().getProxy().extraParams.Person_id = null;
		this.getStore().getProxy().extraParams.uslugaCategoryList = null;
		this.getStore().getProxy().extraParams.uslugaComplexCodeList = null;
		this.getStore().getProxy().extraParams.UslugaComplex_Date = null;
		this.getStore().getProxy().extraParams.UslugaComplex_2011id = null;
		this.getStore().getProxy().extraParams.withoutLpuFilter = null;
		this.getStore().getProxy().extraParams.to = this.to;
	},
	setVizitCodeFilters: function(params) {
		if ( false == sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
			return true;
		}

		this.getStore().getProxy().extraParams.isVizitCode = 1;

		if (params.allowMorbusVizitOnly) {
			this.getStore().getProxy().extraParams.allowMorbusVizitOnly = 1;
			if (params.allowMorbusVizitCodesGroup88) {
				this.getStore().getProxy().extraParams.allowMorbusVizitCodesGroup88 = 1;
			}
		}
		if (params.allowNonMorbusVizitOnly) {
			this.getStore().getProxy().extraParams.allowNonMorbusVizitOnly = 1;
		}
		switch ( getRegionNick() ) {
			case 'perm':
				this.setUslugaCategoryList([ 'gost2011' ]);
				this.setAllowedUslugaComplexAttributeList([ 'vizit' ]);
				if (params.isStom) {
					this.setAllowedUslugaComplexAttributeList([ 'stom', 'vizit' ]);
					this.setAllowedUslugaComplexAttributeMethod('and');
					this.getStore().getProxy().extraParams.isStomVizitCode = 1;
				}
				break;

			case 'buryatiya':
				var addArray = [];
				/*if (this.getStore().getProxy().extraParams.isInoter) {
					var addArray = ['mur'];
				} else {
                    //this.setDisallowedUslugaComplexAttributeList([ 'mur' ]);
					var addArray = [];
				}*/
				if (params.isStom) {
					this.setAllowedUslugaComplexAttributeList([ 'vizit', 'stom' ].concat(addArray));
				} else if (params.isStac) {
					this.setAllowedUslugaComplexAttributeList([ 'stac_kd' ].concat(addArray));
				} else {
					this.setAllowedUslugaComplexAttributeList([ 'vizit' ].concat(addArray));
				}
				this.setAllowedUslugaComplexAttributeMethod('and');
				this.setUslugaCategoryList([ 'tfoms' ]);
				break;

			case 'kz':
				this.setUslugaCategoryList([ 'classmedus' ]);
				break;

			case 'pskov':
				if (params.isStom) {
					this.setAllowedUslugaComplexAttributeList([ 'vizit', 'stom' ]);
					this.setAllowedUslugaComplexAttributeMethod('and');
				} else if (params.isStac) {
					this.setAllowedUslugaComplexAttributeList([ 'stac_kd' ]);
				} else {
					this.setAllowedUslugaComplexAttributeList([ 'vizit', 'obr' ]);
				}
				this.setUslugaCategoryList([ 'pskov_foms' ]);
				break;

			case 'ufa':
				this.setUslugaCategoryList([ 'lpusection' ]);
				if (params.isStom) {
					this.setAllowedUslugaComplexAttributeList([ 'stom' ]);
					this.getStore().getProxy().extraParams.isStomVizitCode = 1;
					this.getStore().getProxy().extraParams.allowMorbusVizitCodesGroup88 = 0;
					this.getStore().getProxy().extraParams.allowMorbusVizitOnly = 0;
					this.getStore().getProxy().extraParams.allowNonMorbusVizitOnly = 0;
				}
				break;

			case 'ekb':
				this.getStore().getProxy().extraParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300,301]);
				this.getStore().getProxy().extraParams.filterByLpuSection = 1;
				break;
		}
		return true;
	},
	setAllowedUslugaComplexAttributeList: function(list) {
		if ( typeof list != 'object' ) {
			list = new Array();
		}

		this.getStore().getProxy().extraParams.allowedUslugaComplexAttributeList = Ext.util.JSON.encode(list);
		this.lastQuery = 'This query sample that is not will never appear';

		return true;
	},
	// Метод учета допустимых типов атрибутов комплексной услуги
	// Допустимые значения:
	// and - должны иметься все перечисленные атрибуты
	// or - должен иметься хотя бы один из перечисленных атрибутов (по умолчанию)
	setAllowedUslugaComplexAttributeMethod: function(method) {
		if ( typeof method != 'string' || !method.inlist([ 'and', 'or' ]) ) {
			method = 'or';
		}

		this.getStore().getProxy().extraParams.allowedUslugaComplexAttributeMethod = method;

		return true;
	},
	setDisallowedUslugaComplexAttributeList: function(list) {
		if ( typeof list != 'object' ) {
			list = new Array();
		}

		this.getStore().getProxy().extraParams.disallowedUslugaComplexAttributeList = Ext.util.JSON.encode(list);
		this.lastQuery = 'This query sample that is not will never appear';

		return true;
	},
	setLpuLevelCode: function(lpu_level_code) {
		this.getStore().getProxy().extraParams.LpuLevel_Code = lpu_level_code;
	},
	setMesOldVizit_id: function(MesOldVizit_id){
		this.getStore().getProxy().extraParams.MesOldVizit_id = MesOldVizit_id;
	},
	setMedSpecOms_id: function(MedSpecOms_id){
		this.getStore().getProxy().extraParams.MedSpecOms_id = MedSpecOms_id;
	},
	setFedMedSpec_id: function(FedMedSpec_id){
		this.getStore().getProxy().extraParams.FedMedSpec_id = FedMedSpec_id;
	},
	setLpuSectionProfile_id: function(LpuSectionProfile_id){
		this.getStore().getProxy().extraParams.LpuSectionProfile_id = LpuSectionProfile_id;
	},
	setLpuSectionProfileByLpuSection_id: function(LpuSection_id){
		this.getStore().getProxy().extraParams.LpuSectionProfileByLpuSection_id = LpuSection_id;
	},

	setPayType: function(PayType_id) {
		this.getStore().getProxy().extraParams.PayType_id = PayType_id;
	},
	setPersonId: function(Person_id) {
		this.getStore().getProxy().extraParams.Person_id = Person_id;
	},
	setUslugaComplexDate: function(date) {
		this.getStore().getProxy().extraParams.UslugaComplex_Date = date;
	},
	setUslugaCategoryList: function(list) {
		if ( typeof list != 'object' ) {
			list = new Array();
		}

		this.getStore().getProxy().extraParams.uslugaCategoryList = Ext.util.JSON.encode(list);
		this.lastQuery = 'This query sample that is not will never appear';

		return true;
	},
	setUslugaComplexCodeList: function(list) {
		if ( typeof list != 'object' ) {
			list = new Array();
		}

		this.getStore().getProxy().extraParams.uslugaComplexCodeList = Ext.util.JSON.encode(list);
		this.lastQuery = 'This query sample that is not will never appear';

		return true;
	},
	setUslugaComplex2011Id: function(id) {
		this.getStore().getProxy().extraParams.UslugaComplex_2011id = id;
	},
	/*
	 * @var int Тип назначения
	 */
	PrescriptionType_Code: null,
	setPrescriptionTypeCode: function(code) {
		this.PrescriptionType_Code = parseInt(code);
		switch(this.PrescriptionType_Code) {
			case 6: //Манипуляции и процедуры
				this.setAllowedUslugaComplexAttributeList([ 'manproc' ]);
				break;
			case 7: //Оперативное лечение
				this.setAllowedUslugaComplexAttributeList([ 'oper' ]);
				break;
			case 11: //Лабораторная диагностика
				this.setAllowedUslugaComplexAttributeList([ 'lab' ]);
				break;
			case 12: //Функциональная диагностика
				this.setAllowedUslugaComplexAttributeList([ 'func' ]);
				break;
			case 13: //Консультационная услуга
				this.setAllowedUslugaComplexAttributeList([ 'consult' ]);
				break;
			default:
				this.setAllowedUslugaComplexAttributeList();
				break;
		}
	},
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'UslugaComplex_id', mapping: 'UslugaComplex_id' },
				{name: 'displayField', type:'string', convert: function(val,row) {
					if (row.get('UslugaComplex_id')) {
						return row.get('UslugaComplex_Code') + '. ' + row.get('UslugaComplex_Name');
					} else {
						return '';
					}
				}},
				{ name: 'UslugaComplex_Name', mapping: 'UslugaComplex_Name', type: 'string'},
				{ name: 'UslugaComplex_Code', mapping: 'UslugaComplex_Code', type: 'string'},
				{ name: 'UslugaCategory_Name', mapping: 'UslugaCategory_Name', type: 'string'},
				{ name: 'UslugaComplexMedService_Time', mapping: 'UslugaComplexMedService_Time', type: 'int'}
			],
			autoLoad: false,
			sorters: {
				property: 'UslugaComplex_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=Usluga&m=loadNewUslugaComplexList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		if ( getGlobalOptions().region ) {
			switch ( getGlobalOptions().region.nick ) {
				case 'perm':
				case 'ufa':
					this.getStore().getProxy().extraParams.withoutLpuFilter = 2;
					break;
			}
		}
		if (this.to)
			this.getStore().getProxy().extraParams.to = this.to;

		if (this.registryType)
			this.getStore().getProxy().extraParams.registryType = this.registryType;

		if (this.DispClass_id)
			this.getStore().getProxy().extraParams.DispClass_id = this.DispClass_id;
		if (this.dispOnly) {
			this.getStore().getProxy().extraParams.dispOnly = 1;
		}
		else if (this.nonDispOnly) {
			this.getStore().getProxy().extraParams.nonDispOnly = 1;
		}

		me.callParent(arguments);
	}
});

/**
 * Выбор услуги с фильтрацией по службе/ресурсу для АРМ оперблока
 */
Ext6.define('swUslugaComplexOperBlockCombo', {
	forceSelection: true,
	triggerAction: 'all',
	codeField: 'UslugaComplex_Code',
	displayField: 'UslugaComplex_Name',
	valueField: 'UslugaComplex_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swUslugaComplexOperBlockCombo',
	queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'Услуга',
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'UslugaComplex_id', mapping: 'UslugaComplex_id' },
				{ name: 'UslugaComplex_Name', mapping: 'UslugaComplex_Name', type: 'string'},
				{ name: 'UslugaComplex_Code', mapping: 'UslugaComplex_Code', type: 'string'},
				{ name: 'UslugaComplexMedService_Time', mapping: 'UslugaComplexMedService_Time', type: 'int'}
			],
			autoLoad: false,
			sorters: {
				property: 'UslugaComplex_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=Usluga&m=loadUslugaComplexList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});

/**
 * Комбо услуги, отличается от swUslugaComplexCombo наличием сброса значения и триггером в виде поисковой лупы
 */
Ext6.define('swUslugaComplexSearchCombo', {
	extend: 'swUslugaComplexCombo',
	alias: 'widget.swUslugaComplexSearchCombo',
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item MedStaffFactCombo">',
		'<table style="border: 0; width: 100%; border-spacing: 0px;">',
		'<tr>',
		'<td width="100%" style="padding-left: 15px; padding-right: 15px;"><div style="font: 13px/16px Roboto; font-weight: 400; color: #000;">{UslugaComplex_Code} {UslugaComplex_Name}</div></td>',
		//'<td width="20%" style="padding-right: 15px; vertical-align: top;"><div style="font: 13px/16px Roboto; font-weight: 400;"><nobr style="color: #000;">{UslugaComplex_Code}</nobr></div></td>',
		//'<td width="20%" style="padding-right: 15px; vertical-align: top;"><div style="font: 13px/16px Roboto; font-weight: 400;"><nobr style="color: #000;">{UslugaCategory_Name}</nobr></div></td>',
		'</tr>',
		/*'<tr>',
		//'<td width="70%" style="padding-left: 10px;"><p style="font: 11px Roboto; font-weight: 400; color: #000;">',
		// "{[values.UslugaComplex_Name.replace(new RegExp('(' + this.field.getRawValue().trim().replace(new RegExp(' ', 'g'), '|') + ')', 'ig'), '<span style=\"color:red;font-weight:900\">$1</span>')]}", // на случай если понадобится подсветка найденных частей названия услуги {[Ext.isEmpty(values.UslugaComplex_id)?"":"Категория услуги"]}
		//'{UslugaComplex_Name}',
		'</p></td>',
		'<td width="30%" style="padding-right: 10px;"><p style="font: 11px Roboto; font-weight: 400; color: #2196f3; padding-top: 5px;">&nbsp;</p></td>',
		'</tr>',*/'</table>',
		'</div></tpl>'
	),
	onSearchButton: Ext6.emptyFn,
	refreshQueryFieldTrigger: function(value) {
		var isEmpty = Ext6.isEmpty(value || this.getRawValue());
		this.triggers.clear.setVisible(!isEmpty);
		this.triggers.search.setVisible(isEmpty);
	},
	triggers: {
		clear: {
			cls: 'sw-clear-trigger',
			hidden: true,
			handler: function() {
				if (this.disabled) return false;
				this.clearValue();
				this.refreshQueryFieldTrigger();
			}
		},
		search: {
			cls: 'x6-form-search-trigger',
			handler: function() {
				this.onSearchButton();
			}
		}
	},
	listeners: {
		keyup: function(field, e) {
			field.refreshQueryFieldTrigger(e.target.value);
		},
		change: function(combo, newValue, oldValue) {
			combo.refreshQueryFieldTrigger();
		}
	},
	initComponent: function() {
		var me = this;

		var triggers = this.getTriggers();
		triggers.picker.hidden = true;
		this.setTriggers(triggers);

		me.callParent(arguments);
	}
});

Ext6.define('MedStaffFactModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.MedStaffFactModel',
	idProperty: 'MedStaffFact_id',
	fields: [
		{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id', type: 'int' },
		{ name: 'MedStaffFactKey_id', mapping: 'MedStaffFactKey_id' },
		{ name: 'MedPersonal_DloCode', mapping: 'MedPersonal_DloCode' },
		{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio', type: 'string', multipleSortInfo: [
				{ field: 'sortID', direction: 'ASC' },
				{ field: 'Lpu_Name', direction: 'ASC' },
				{ field: 'MedPersonal_Fio', direction: 'ASC' }
			]},
		{ name: 'MedPersonal_Fin', mapping: 'MedPersonal_Fin' },
		{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
		{ name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
		{ name: 'Person_Snils', mapping: 'Person_Snils' },

		{ name: 'Lpu_id', mapping: 'Lpu_id', type: 'int' },
		{ name: 'Lpu_Name', mapping: 'Lpu_Name', type: 'string' },
		{ name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code' },
		{ name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick' },

		{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
		{ name: 'LpuBuildingType_id', mapping: 'LpuBuildingType_id' },

		{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
		{ name: 'LpuUnitSet_id', mapping: 'LpuUnitSet_id' },

		{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
		{ name: 'LpuSection_pid', mapping: 'LpuSection_pid' },
		{ name: 'LpuSectionAge_id', mapping: 'LpuSectionAge_id' },
		{ name: 'LpuSection_Name', mapping: 'LpuSection_Name' },
        { name: 'LpuSection_RawName', mapping: 'LpuSection_RawName' },
		{ name: 'LpuSection_disDate', mapping: 'LpuSection_disDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'LpuSection_setDate', mapping: 'LpuSection_setDate', type: 'date', dateFormat: 'd.m.Y' },

		{ name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code' },
		{ name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick' },
		{ name: 'LpuSectionProfile_Name', mapping: 'LpuSectionProfile_Name' },
		{ name: 'LpuSectionProfile_msfid', mapping: 'LpuSectionProfile_msfid'},


		{ name: 'WorkData_begDate', mapping: 'WorkData_begDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'WorkData_endDate', mapping: 'WorkData_endDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'WorkData_dloBegDate', mapping: 'WorkData_dloBegDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'WorkData_dloEndDate', mapping: 'WorkData_dloEndDate', type: 'date', dateFormat: 'd.m.Y' },

		{ name: 'PostKind_id', mapping: 'PostKind_id' },
		{ name: 'PostMed_Code', mapping: 'PostMed_Code' },
		{ name: 'PostMed_Name', mapping: 'PostMed_Name' },


		{ name: 'MedSpecOms_id', mapping: 'MedSpecOms_id' },
		{ name: 'MedSpecOms_Code', mapping: 'MedSpecOms_Code' },

		{ name: 'FedMedSpec_id', mapping: 'FedMedSpec_id' },
		{ name: 'FedMedSpec_Code', mapping: 'FedMedSpec_Code' },
		{ name: 'FedMedSpecParent_Code', mapping: 'FedMedSpecParent_Code' },

		{ name: 'Post_IsPrimaryHealthCare', mapping: 'Post_IsPrimaryHealthCare', type: 'int' },
		{ name: 'MedStaffFactCache_IsDisableInDoc', mapping: 'MedStaffFactCache_IsDisableInDoc' },
		{ name: 'MedStaffFact_Stavka', mapping: 'MedStaffFact_Stavka', type: 'string'},
		{ name: 'LpuRegion_List', mapping: 'LpuRegion_List'},
		{ name: 'LpuRegion_MainList', mapping: 'LpuRegion_MainList'},
		{ name: 'LpuRegion_DatesList', mapping: 'LpuRegion_DatesList'},
		{ name: 'MedStaffFactCache_IsHomeVisit', mapping: 'MedStaffFactCache_IsHomeVisit'},

		{ name: 'SortVal', mapping: 'SortVal' },
		{ name: 'sortID', mapping: 'sortID', type: 'int' },
		{ name: 'listType', mapping: 'listType', type: 'string' }
	]
});

Ext6.define('swMedStaffFactCombo', {
	codeField: 'MedPersonal_TabCode',
	displayField: 'MedPersonal_Fio',
	valueField: 'MedStaffFact_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swMedStaffFactCombo',
	minWidth: 565,
	listConfig:{
		minWidth: 565,
		userCls: 'swMedStaffFactSearch'
	},
	queryMode: 'local',
	autoFilter: true,
	enableKeyEvents: true,
	displayTpl: new Ext6.XTemplate(
		'<tpl for=".">' +
		'{[typeof values === "string" ? values : [values["MedPersonal_TabCode"] ? values["MedPersonal_TabCode"] + ". " + values["MedPersonal_Fio"] : values["MedPersonal_Fio"]]]}' +
		'<tpl if="xindex < xcount">' + ', ' + '</tpl>' +
		'</tpl>'
	),
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item MedStaffFactCombo">',
		'<table style="border: 0; width: 100%; table-layout: fixed; padding: 11px 0px 9px 15px;">',
		'<tr>',
		'<td width="75%"><div style="font: 13px Roboto; font-weight: 700; text-transform: capitalize !important;">{MedPersonal_Fio} </div></td>',
		'<td width="15%"><div style="font: 12px Roboto; font-weight: 400;"><nobr style="color: #999;">Таб. номер</nobr></div></td>',
		'<td width="15%" style=" "><div style="font: 12px Roboto; font-weight: 400;"><nobr style="color: #999;">Код ЛЛО</nobr></div></td>',
		'</tr>',
		'<tr>',
		'<td width="70%" style="padding-right: 15px"><p style="font: 12px Roboto; font-weight: 400; color: #000; text-overflow:ellipsis; white-space: nowrap; overflow: hidden;">',
		'<p class="postMedName" data-qtip="{PostMed_Name}" style="padding-top: 2px">{PostMed_Name} </p>',
		'<p class="lpuSectionName" data-qtip="{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}">{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</p>',
		//'<nobr>{[!Ext.isEmpty(values.MedStaffFact_Stavka) ? " ставка" : ""]} {MedStaffFact_Stavka}</nobr>',
		'</p>',
		'<p class="postMedName">',
		'<nobr>{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name+"/ &nbsp":""]}</nobr>',
		'<nobr><span style="color: red"> {[!Ext.isEmpty(values.WorkData_endDate) ?"Уволен с: " + this.formatDate(values.WorkData_endDate):"</span>"+[!Ext.isEmpty(values.WorkData_begDate) ? "Работает с: " + this.formatDate(values.WorkData_begDate):""]]}</nobr>&nbsp;',
		'</p></td>',
		'<td style="width: 15%; vertical-align: top;"><p style="font: 12px Roboto; font-weight: 400; color: #000; padding-top: 5px;">{MedPersonal_TabCode}&nbsp;</p></td>',
		'<td style="width: 15%; vertical-align: top;"><p style="font: 12px Roboto; font-weight: 400; color: #000; padding-top: 5px;">{MedPersonal_DloCode}&nbsp;</p></td>',
		/*'<td>',
		'<div style="font-weight: bold; line-height: 16px;">{MedPersonal_Fio}&nbsp;{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</div>',
		'<div style="font-size: 10px; line-height: 16px;">{PostMed_Name}{[!Ext.isEmpty(values.MedStaffFact_Stavka) ? ", ставка" : ""]} {MedStaffFact_Stavka}</div>',
		'<div style="font-size: 10px; line-height: 16px;">{[!Ext.isEmpty(values.WorkData_begDate) ? "Дата начала работы: " + values.WorkData_begDate:""]} {[!Ext.isEmpty(values.WorkData_endDate) ? "Дата увольнения: " + values.WorkData_endDate:""]}</div>',
		'<div style="font-size: 10px; line-height: 16px;">{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div>',
		'</td>',*/
		'</tr></table>',
		'</div></tpl>',
		{
			formatDate: function(date) {
				return Ext6.util.Format.date(date, 'd.m.Y');
			}
		}
		/*data-qtip="{[!Ext.isEmpty(values.WorkData_begDate) ? "Работает с: " + this.formatDate(values.WorkData_begDate):""]} {[!Ext.isEmpty(values.WorkData_endDate) ? "Уволен с: " + this.formatDate(values.WorkData_endDate):""]}"*/
	),
	fieldLabel: 'Врач',
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			model: 'MedStaffFactModel',
			autoLoad: false,
			sorters: {
				property: 'MedPersonal_Fio',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: C_MEDPERSONAL_LIST,
				reader: {
					type: 'json'
				}
			},
			mode: 'local'
		});

		me.callParent(arguments);
	}
});

// Комбобоксы для клинических рекомендаций
Ext6.define('swCureStandartSpr', {
	extend: 'swBaseCombobox',
	alias: 'widget.swCureStandartSpr',
	cls: '',
	queryMode: 'local',
	autoFilter: true,
	enableKeyEvents: true,
	store: null,
	fields: null,
	searchFn: Ext6.emptyFn(),
	comboSubject: '',
	prefix: '',
	suffix: '',
	displayField: '',
	valueField: '',
	codeField: '',
	initComponent: function() {
		var me = this;

		if (Ext6.isEmpty(me.displayField)) {
			me.displayField = me.comboSubject + '_Name';
		}

		if (Ext6.isEmpty(me.valueField)) {
			me.valueField = me.comboSubject + '_id';
		}

		if (Ext6.isEmpty(me.codeField)) {
			me.codeField = me.comboSubject + '_Code';
		}

		if (Ext6.isEmpty(me.fields)) {
			me.fields = [
				{name: me.valueField, type:'int', mapping: 'id'},
				{name: me.displayField, type:'string', mapping: 'Name'},
				{name: me.codeField, type:'int', mapping: 'Code'}
			];
		}

		if (me.fields)
		{
			me.store = Ext6.create('Ext6.data.Store', {
				fields: me.fields,
				autoLoad: true,
				sorters: {
					property: me.comboSubject+'_Code',
					direction: 'ASC'
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url : '/?c=CureStandart&m=loadCureStandartSpr',
					extraParams: {
						subj: me.comboSubject
					},
					reader: {
						type: 'json'
					}
				},
				mode: 'remote'
			});
		}

		me.callParent(arguments);
	}
});

//Список услуг, форматированный для клинических рекомендаций
Ext6.define('swUslugaComplexComboExt', {
	extend: 'swBaseCombobox',
	alias: 'widget.swUslugaComplexComboExt',
	forceSelection: true,
	triggerAction: 'all',
	codeField: 'id',
	displayField: 'Name',
	valueField: 'id',
	minChars: 0,
	queryParam: 'query',
	queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,

	fieldLabel: 'Услуга',
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item">',
		'<table style="border: 3px; bordercolor: black; width: 480px;">',
		'<tr><td style="width: 100px;"><font color="red">{Code}&nbsp;</font></td>',
		'<td style="width: 300px;">{Name}&nbsp;</td>',
		'</tr></table>',
		'</div></tpl>'
	),
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'id', mapping: 'id' },
				{ name: 'Code', mapping: 'Code', type: 'string'},
				{ name: 'Name', mapping: 'Name', type: 'string'}
			],
			autoLoad: true,
			sorters: {
				property: 'Code',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=CureStandart&m=loadUslugaComplexList',
				reader: {
					type: 'json'
				}
			},
			extraParams: {
				code: ''
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});

Ext6.define('swBaseRemoteCombo', {
	extend: 'Ext.form.ComboBox',
	alias: 'widget.swBaseRemoteCombo',
	forceSelection: true,
	mode: 'remote',
	minChars: 1,
	resizable: true,
	selectOnFocus: false,
	trigger2Class: 'x-form-clear-trigger',
	onTrigger2Click: function() {
		if ( !this.disabled ) this.clearValue()
	},
	onTrigger1Click: this.onTriggerClick,
	initComponent: function() {

		this.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'PersonDisp_id', type: 'int'},
				{name: 'PersonDisp_setDate', type: 'date', dateFormat: 'd.m.Y'},
				{name: 'PersonDisp_Name', type: 'string'}
			],
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=PersonDisp&m=loadPersonDispList',
				reader: {
					type: 'json'
				}
			},
			sorters: {
				property: 'PersonDisp_setDate',
				direction: 'ASC'
			}
		});

		if ( this.width < 500 || typeof(this.width)=='undefined' )
			this.listWidth = 300;
		this.callParent(arguments);
	}
});

// sw.Promed.SwBaseRemoteCombo.prototype.initComponent = Ext.form.TwinTriggerField.prototype.initComponent;
// sw.Promed.SwBaseRemoteCombo.prototype.getTrigger = Ext.form.TwinTriggerField.prototype.getTrigger;
// sw.Promed.SwBaseRemoteCombo.prototype.initTrigger = Ext.form.TwinTriggerField.prototype.initTrigger;

// Список лекарственных средств
Ext6.define('swDrugComplexMnnCombo', {
	forceSelection: true,
	triggerAction: 'all',
	displayField:'DrugComplexMnn_Name',
	codeField: 'DrugComplexMnn_id',
	valueField: 'DrugComplexMnn_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swDrugComplexMnnCombo',
	queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'МНН',
	trigger2Cls: Ext6.baseCSSPrefix + 'form-search-trigger',
	trigger3Cls: Ext6.baseCSSPrefix + 'form-clear-trigger',
	onTrigger2Click: function () {
		if (this.disabled) return false;

		var combo = this;
		/*getWnd('swOrgSearchWindow').show({
			onHide: function() {
				combo.focus(false);
			},
			onSelect: function(orgData) {
				combo.getStore().removeAll();
				combo.getStore().loadData([{
					Org_id: orgData.Org_id,
					Org_Name: orgData.Org_Name,
					Org_Nick: orgData.Org_Nick,
					OrgType_id: orgData.OrgType_id
				}]);
				combo.setValue(orgData.Org_id);

				var index = combo.getStore().find('Org_id', orgData.Org_id);

				if (index == -1)
				{
					return false;
				}

				var record = combo.getStore().getAt(index);
				combo.fireEvent('select', combo, record, 0);
				combo.fireEvent('change', combo, combo.getValue());

				getWnd('swOrgSearchWindow').hide();
			}
		});*/
	},
	onTrigger3Click: function () {
		if (this.disabled) return false;

		this.clearValue();
	},
	initComponent: function () {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'DrugComplexMnn_id', mapping: 'DrugComplexMnn_id', type:'int'},
				{name: 'RlsClsdrugforms_id', mapping: 'RlsClsdrugforms_id', type:'int'},
				{name: 'RlsClsdrugforms_Name', mapping: 'RlsClsdrugforms_Name', type: 'string'},
				{name: 'RlsClsdrugforms_RusName', mapping: 'RlsClsdrugforms_RusName', type: 'string'},
				{name: 'DrugComplexMnn_Dose', mapping: 'DrugComplexMnn_Dose', type: 'string'},
				{name: 'DrugComplexMnnDose_Mass', mapping: 'DrugComplexMnnDose_Mass', type: 'string'},
				{name: 'DrugComplexMnn_Name', mapping: 'DrugComplexMnn_Name', type: 'string'},
				{name: 'RlsActmatters_id', mapping: 'RlsActmatters_id', type: 'int'},
				{name: 'Drug_Fas', mapping: 'Drug_Fas', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'DrugComplexMnn_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=RlsDrug&m=loadDrugComplexMnnList',
				reader: {
					type: 'json'
				},
				extraParams: {needFas:true}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});

// Список лекарственных средств
Ext6.define('swSearchDrugComplexMnnCombo', {
	forceSelection: true,
	hideEmptyRow: true,
	triggerAction: 'all',
	displayField:'Drug_Name',
	codeField: 'DrugComplexMnn_id',
	valueField: 'DrugComplexMnn_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swSearchDrugComplexMnnCombo',
	queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,
	onBeforeLoad: Ext6.emptyFn(),
	triggers: {
		clear: {
			cls: 'sw-clear-trigger',
			hidden: true,
			handler: function () {
				if (this.disabled) return false;
				this.setValue('');
				this.triggers.clear.hide();
				this.triggers.search.show();
			}
		},
		search: {
			cls: 'x6-form-search-trigger',
			handler: function () {
				//а хз что тут делать, и так работает
			}
		}
	},
	/*trigger2Cls: Ext6.baseCSSPrefix + 'form-search-trigger',
	trigger3Cls: Ext6.baseCSSPrefix + 'form-clear-trigger',*/
	initComponent: function () {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'Drug_Name', type: 'string'},
				{name: 'Drug_id', type:'int'},
				{name: 'DrugComplexMnn_id', type:'int'},
				{name: 'LatName', type: 'string'},
				{name: 'ActMatters_id', type: 'int'}
			],
			autoLoad: false,
			sorters: {
				property: 'Drug_id',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=RlsDrug&m=loadDrugMNNNameList',
				reader: {
					type: 'json'
				}
			},
			listeners: {
				beforeload: me.onBeforeLoad
			},
			mode: 'remote'
		});

		var triggers = this.getTriggers();
		triggers.picker.hidden = true;
		this.setTriggers(triggers);
		me.callParent(arguments);
	}
});
// Список лекарственных средств
Ext6.define('swDrugCombo', {
	forceSelection: true,
	hideEmptyRow: true,
	triggerAction: 'all',
	displayField:'Drug_Name',
	codeField: 'Drug_Code',
	valueField: 'Drug_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swDrugCombo',
	listConfig: {
		cls: 'choose-bound-list-menu'
	},
	queryMode: 'remote',
	autoFilter: false,
	typeAhead: false,
	enableKeyEvents: false,
	fieldLabel: 'Торговое наименование',
	initComponent: function () {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'Drug_Name', type: 'string'},
				{name: 'Drug_id', type:'int'},
				{name: 'DrugComplexMnn_id', type:'int'},
				//{name: 'LatName', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'Drug_id',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=RlsDrug&m=loadDrugList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});
Ext6.define('swPrivilegeTypeCombo', {
	extend: 'swCommonSprCombo',
	comboSubject: 'PrivilegeType',
	displayField: 'PrivilegeType_Name',
	moreFields: [
		{name: 'PrivilegeType_Code', mapping: 'PrivilegeType_Code', type: 'int'},
		{name: 'ReceptFinance_id', mapping: 'ReceptFinance_id', type: 'int'},
		{name: 'PrivilegeType_begDate', mapping: 'PrivilegeType_begDate', type: 'date'},
		{name: 'PrivilegeType_endDate', mapping: 'PrivilegeType_endDate', type: 'date'}
	],
	alias: 'widget.swPrivilegeTypeCombo',
	fieldLabel: 'Категория'
});

Ext6.define('LpuSectionGlobalModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.LpuSectionGlobalModel',
	idProperty: 'LpuSection_id',
	fields: [
		{ name: 'LpuSection_Code', mapping: 'LpuSection_Code', type: 'string' },
		{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
		{ name: 'LpuSection_pid', mapping: 'LpuSection_pid', type: 'int' },
		{ name: 'LpuSectionAge_id', mapping: 'LpuSectionAge_id', type: 'int' },
		{ name: 'LpuSection_Class', mapping: 'LpuSection_Class', type: 'string' },
		{ name: 'LpuSection_Name', mapping: 'LpuSection_Name', type: 'string', multipleSortInfo: [
				{ field: 'sortID', direction: 'ASC' },
				{ field: 'Lpu_Name', direction: 'ASC' },
				{ field: 'LpuSection_Name', direction: 'ASC' }
			]},
		{ name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id', type: 'int' },
		{ name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code', type: 'string' },
		{ name: 'LpuSectionProfile_Name', mapping: 'LpuSectionProfile_Name', type: 'string' },
		{ name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick', type: 'string' },
		{ name: 'LpuSectionBedProfile_id', mapping: 'LpuSectionBedProfile_id', type: 'int' },
		{ name: 'LpuSectionBedProfile_Code', mapping: 'LpuSectionBedProfile_Code', type: 'string' },
		{ name: 'LpuSectionBedProfile_Name', mapping: 'LpuSectionBedProfile_Name', type: 'string' },
		{ name: 'Lpu_id', mapping: 'Lpu_id', type: 'int' },
		{ name: 'Lpu_Name', mapping: 'Lpu_Name', type: 'string' },
		{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id', type: 'int' },
		{ name: 'LpuUnit_id', mapping: 'LpuUnit_id', type: 'int' },
		{ name: 'LpuUnitSet_id', mapping: 'LpuUnitSet_id', type: 'int' },
		{ name: 'LpuUnitSet_Code', mapping: 'LpuUnitSet_Code', type: 'string' },
		{ name: 'LpuUnitType_id', mapping: 'LpuUnitType_id', type: 'int' },
		{ name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code', type: 'string' },
		{ name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick', type: 'string' },
		{ name: 'LpuSection_disDate', mapping: 'LpuSection_disDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'LpuSection_setDate', mapping: 'LpuSection_setDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'LpuSection_IsHTMedicalCare', mapping: 'LpuSection_IsHTMedicalCare', type: 'int' },
		{ name: 'LpuSectionServiceList', mapping: 'LpuSectionServiceList', type: 'string' },
		{ name: 'LpuSectionLpuSectionProfileList', mapping: 'LpuSectionLpuSectionProfileList', type: 'string' },
		{ name: 'MedicalCareKind_id', mapping: 'MedicalCareKind_id', type: 'int' },
		{ name: 'MedicalCareKind_Code', mapping: 'MedicalCareKind_Code', type: 'int' },
		{ name: 'listType', mapping: 'listType', type: 'string' },
		{ name: 'sortID', mapping: 'sortID', type: 'int' }
	]
});


Ext6.define('SwLpuSectionGlobalCombo', {
	alias: 'widget.SwLpuSectionGlobalCombo',
	extend: 'swBaseCombobox',
	codeField: 'LpuSection_Code',
	displayField: 'LpuSection_Name',
	listConfig: {
		userCls: 'lpu-section-global-combo-list'
	},
	fieldLabel: langs('Отделение'),
	hiddenName: 'LpuSection_id',
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item lpu-section-global-combo" style="padding:10px 30px 8px 16px;">',
		'<table style="border: 3px; bordercolor: black; width: 100%;">',
		'<tr>',
		'<td style="width: 80%; vertical-align: top;" class="lpu-name"><div style="padding-right: 20px"><p style="line-height: 16px">{LpuSection_Name}&nbsp;</p>',
		'<p style="font-size: 10px; line-height: 16px" class="lpu-section-info">{[!Ext6.isEmpty(values.LpuSection_setDate) ? "Действует с: " + Ext6.util.Format.date(values.LpuSection_setDate,"d.m.Y"):""]} {[!Ext6.isEmpty(values.LpuSection_disDate) ? "Дата закрытия: " + Ext.util.Format.date(values.LpuSection_disDate,"d.m.Y"):""]}</p>',
		'</div></td>',
		'<td style="width: 20%; vertical-align: top;"><div><p class="tab-number" style="line-height: 16px">Таб. Номер</p></div>',
		'<div><font class="lpu-section-info" style="line-height: 16px;">{LpuSection_Code}&nbsp;</font></div>',
		'</td>',
		'</tr>',
		/*'<tr>',
		'<td>',
		'<div style="font-size: 10px;" class="lpu-section-info">{[!Ext6.isEmpty(values.LpuSection_setDate) ? "Дата начала действия: " + Ext6.util.Format.date(values.LpuSection_setDate,"d.m.Y"):""]} {[!Ext6.isEmpty(values.LpuSection_disDate) ? "Дата закрытия: " + Ext.util.Format.date(values.LpuSection_disDate,"d.m.Y"):""]}</div>',
		'</td>',
		/*'<td style="width: 25%;"><font class="lpu-section-info">{LpuSection_Code}&nbsp;</font></td>',*/
		//'<td style="width: 300px;">{LpuSection_Name}&nbsp;',
		/*'<div style="font-size: 10px;">{[!Ext6.isEmpty(values.LpuSection_setDate) ? "Дата начала действия: " + Ext6.util.Format.date(values.LpuSection_setDate,"d.m.Y"):""]} {[!Ext6.isEmpty(values.LpuSection_disDate) ? "Дата закрытия: " + Ext.util.Format.date(values.LpuSection_disDate,"d.m.Y"):""]}</div>',
		'</tr>',*/
		'</table>',
		'</div></tpl>'
	),
	valueField: 'LpuSection_id',
	//valueFieldAdd: 'LpuSection_pid',
	initComponent: function() {
		this.store = new Ext6.data.Store({
			autoLoad: false,
			model: 'LpuSectionGlobalModel',

			proxy: {
				type: 'ajax',
				url: C_LPUSECTION_LIST,
				reader: {
					type: 'json'
				}
			},
			sorters: [
				'LpuSection_Name'
			],
			// listeners: {
			// 	'load': function(store) {
			// 		this.setValue(this.getValue());
			// 	}.createDelegate(this)
			// },
		});
		this.callParent(arguments);
	}
});

Ext6.define('SwMedStaffFactGlobalCombo', {
	alias: 'widget.SwMedStaffFactGlobalCombo',
	extend: 'swBaseCombobox',
	codeField: 'MedPersonal_TabCode',
	//~ dateFieldId: null,
	displayField: 'MedPersonal_Fio',
	valueField: 'MedStaffFact_id',

	enableOutOfDateValidation: false,
	listConfig:{
		userCls: 'swMedStaffFactSearch'
	},
	ignoreDisableInDoc: false,
	fieldLabel: langs('Врач'),
	displayTpl: new Ext6.XTemplate(
		'<tpl for=".">' +
		'{[typeof values === "string" ? values : values["MedPersonal_TabCode"] + ". " + values["MedPersonal_Fio"]]}' +
		'<tpl if="xindex < xcount">' + ', ' + '</tpl>' +
		'</tpl>'
	),

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
		'<td width="250px"><p style="font: 11px Roboto; font-weight: 400; color: #000; word-wrap: break-word; width: 250px;">',
		'<p class="postMedName" data-qtip="{PostMed_Name}" style="padding-top: 2px; width: 250px;">{PostMed_Name}</p>',

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
	),
	initComponent: function() {
		var combo = this;

		this.store = new Ext6.data.Store({
				autoLoad: false,
				model: 'MedStaffFactModel',
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: C_MEDPERSONAL_LIST,
					reader: {
						type: 'json'
					}
				},
				sorters: [
					'MedPersonal_Fio'
				]
			});

		this.disableInDocFilter = function() {
			var combo = this;
			if (!this.ignoreDisableInDoc) {
				combo.getStore().filterBy(function(rec) {
					return ((rec.id == combo.getValue() && rec.get('MedStaffFactCache_IsDisableInDoc') == 2) || rec.get('MedStaffFactCache_IsDisableInDoc') != 2);
				});
			}
		};

		this.globalFilterFn = function(rec) {
			if (!this.ignoreDisableInDoc) {
				return (rec.id == this.getValue() || rec.get('MedStaffFactCache_IsDisableInDoc') != 2);
			} else {
				return true;
			}
		};

		this.store.addListener('load', function(store) {
			this.disableInDocFilter();
		}.createDelegate(this));
 /*		//TODO: валидатор из ext2:
		this.validator = function() {
			var combo = this;

			combo.clearInvalid();

			if ( combo.enableOutOfDateValidation == false
				|| typeof combo.dateFieldId != 'string'
				|| combo.dateFieldId.length == 0
				|| typeof Ext.getCmp(combo.dateFieldId) != 'object'
			) {
				return true;
			}

			var dateField = Ext.getCmp(combo.dateFieldId);

			if ( !dateField.getValue() ) {
				return true;
			}

			var date = dateField.getValue();

			if ( typeof date != 'object' ) {
				date = getValidDT(date, '');
			}

			if ( typeof date != 'object' ) {
				return true;
			}

			var index = combo.getStore().findBy(function(rec) {
				if ( rec.get('MedStaffFact_id') == combo.getValue() ) {
					return true;
				}
				else {
					return false;
				}
			});
			var r = combo.getStore().getAt(index);

			if ( typeof r != 'object' ) {
				return true;
			}

			if (!combo.ignoreDisableInDoc && r.get('MedStaffFactCache_IsDisableInDoc') == 2) {
				return langs('Врач, работающий в указанном отделении, не может быть указан в документе');
			}

			if ( r.get('WorkData_endDate') ) {
				if ( typeof r.get('WorkData_endDate') != 'object' ) {
					r.set('WorkData_endDate', getValidDT(r.get('WorkData_endDate'), ''));
					r.commit();
				}

				if ( r.get('WorkData_endDate') < date ) {
					return langs('Врач уволен ранее даты случая или не работает в указанном отделении, выберите другие параметры документа');
				}
				else {
					return true;
				}
			}
			else {
				return true;
			}
		}
*/
		this.callParent(arguments);
	}
});

Ext6.define('SwDopDispDiagTypeCombo',
{
	alias: 'widget.SwDopDispDiagTypeCombo',
	extend: 'swBaseCombobox',
	codeField: 'DopDispDiagType_Code',
	displayField:'DopDispDiagType_Name',
	valueField: 'DopDispDiagType_id',
	hiddenName:'DopDispDiagType_id',
	fieldLabel: langs('Заболевание'),
	displayTpl: Ext6.create('Ext6.XTemplate',
		'<tpl for=".">',
		'{[Ext.isEmpty(values.DopDispDiagType_Name)?"":values.DopDispDiagType_Code]} {DopDispDiagType_Name}',
		'</tpl>'
	),
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item">',
		'<table style="border: 3px; bordercolor: black;">',
		'<tr><td>{[Ext.isEmpty(values.DopDispDiagType_Name)?"":values.DopDispDiagType_Code]}&nbsp;',
		'{DopDispDiagType_Name}&nbsp;</td>',
		'</tr></table>',
		'</div></tpl>'
	),
	initComponent: function()
	{
		var me = this;
		this.store = new Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'DopDispDiagType_id', type:'int'},
				{name: 'DopDispDiagType_Code', type:'int'},
				{name: 'DopDispDiagType_Name',  type:'string'}
			],
			autoLoad: false,
			sorters: {
				property: me.codeField,
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=MongoDBWork&m=getData',
				reader: {
					type: 'json'
				},
				extraParams: {object:'DopDispDiagType', DopDispDiagType_id:'', DopDispDiagType_Code:'', DopDispDiagType_Name:''}
			},
			baseParams: {object:'DopDispDiagType', DopDispDiagType_id:'', DopDispDiagType_Code:'', DopDispDiagType_Name:''},
			tableName: 'DopDispDiagType',
			mode: me.queryMode
		});
		this.callParent(arguments);
	}
});

/**
 * Блок model-store-combo для диагноза, а также отдельно вынесен поисковый модуль
 */

Ext6.define('swDiagModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.swDiagModel',
	idProperty: 'Diag_id',
	fields: [
		{name: 'Diag_id'},
		{name: 'Diag_pid', type: 'int'},
		{name: 'Diag_Name', type: 'string'},
		{name: 'Diag_Code', type: 'string'},
		{name: 'Diag_Display', calculate: function (data) {return data.Diag_Code + ' ' + data.Diag_Name;}},
		{name: 'DiagLevel_id', type: 'int'},
		{name: 'Diag_begDate', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'Diag_endDate', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'PersonAgeGroup_Code'},
		{name: 'Sex_Code'},
		{name: 'DiagFinance_IsOms', type: 'boolean'},
		{name: 'DiagFinance_IsAlien', type: 'boolean'},
		{name: 'DiagFinance_IsFacult', type: 'int' },
		{name: 'DiagFinance_IsHealthCenter', type: 'boolean'},
		{name: 'DiagFinance_IsRankin', type: 'boolean'},
		{name: 'PersonRegisterType_List'},
		{name: 'MorbusType_List'},
		{name: 'DeathDiag_IsLowChance', type: 'boolean'},
		{name: 'Diag_IsFavourite', type: 'boolean'}
	]
});

Ext6.define('swDiagStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.swDiagStore',
	model: 'swDiagModel',
	autoLoad: false,
	loadParams : {
		object: 'Diag',
		where: 'where DiagLevel_id = 4'
	},
	sorters: {
		property: 'Diag_Code',
		direction: 'ASC'
	},
	proxy: {
		type: 'ajax',
		url : '/?c=MongoDBWork&m=getData',
		extraParams: {
			object: 'Diag', // для ручной загрузки
			where: 'where DiagLevel_id = 4',
			Diag_id: '',
			Diag_pid: '',
			Diag_Name: '',
			Diag_Code: '',
			DiagLevel_id: '',
			Diag_Display: '',
			Diag_begDate: '',
			Diag_endDate: '',
			PersonAgeGroup_Code: '',
			Sex_Code: '',
			DiagFinance_IsOms: '',
			DiagFinance_IsAlien: '',
			DiagFinance_IsHealthCenter: '',
			DiagFinance_IsRankin: '',
			DiagFinance_IsFacult: '',
			PersonRegisterType_List: '',
			MorbusType_List: '',
			DeathDiag_IsLowChance: '',
			IsFavourite: ''
		},
		reader: {
			type: 'json'
		}
	},
	mode: 'remote'
});

Ext6.define('DiagSearchModule', {
	MKB:null,
	Diag_level3_code:null,
	withGroups: false,
	PersonRegisterType_SysNick: '',
	MorbusType_SysNick: '', //Тип заболевания/нозологии
	HighlightSearhResults: true,
	onLoadStore: Ext6.emptyFn,
	onEmptyResults: Ext6.emptyFn,
	listConfig: { // конфиг для выпадающего списка с подкрашиванием результатов поиска
		loadingText: 'Загружаем диагнозы',
		cls: 'choose-bound-list-menu update-scroller',
		emptyText: '<div class="no-results-frame"><span>Диагнозов не найдено</span></div>',
		getInnerTpl: function() { // подкрашиваем результаты поиска

			return "{[values.Diag_Display.replace(new RegExp('(' + this.field.getRawValue().trim() + ')', 'ig'), '<span style=\"color:red;font-weight:900\">$1</span>')]}";
		}
	},
	getNameAndDiagFilters: function (q)
	{
		if (Ext6.isEmpty(q))
		{
			return '';
		}

		var reg = new RegExp('^([a-z]|[a-z][0-9.]{1,4})$', 'i'),
			filter;

		if (q.search(reg) !== -1)
		{
			filter = ' and (Diag_Code like \'' + q + '%\') ';
		} else
		{
			filter = ' and (Diag_Code like \'' + q + '%\' OR Diag_Name like \'%' + q + '%\') ';
		}

		return filter;
	},
	accessRightsFilter: function(record) {
		var code_list = getGlobalOptions().denied_diags.code_list,
			code_range_list = getGlobalOptions().denied_diags.code_range_list;

		var result = true;
		if (code_list.length > 0 && record.get('Diag_Code').inlist(code_list)) {
			return false;
		}
		code_range_list.forEach(function(range){
			if (record.get('Diag_Code') >= range[0] && record.get('Diag_Code') <= range[1]) {
				result = false; return false;
			}
		});
		return result;
	},
	getRegistryTypeFilter: function (registryType)
	{
		var filter = '';

		switch (registryType)
		{
			case 'Fmba':
				filter = " and Diag_Code like 'Z57%'";
				break;
			case 'palliat':
				filter = " and Diag_Code not like 'Z%'";
				break;
			case 'NarkoRegistry':
				filter = " and Diag_Code like 'F1%'";
				break;
			case 'CrazyRegistry':
				filter = " and (Diag_Code like 'F0%' or Diag_Code like 'F2%' or Diag_Code like 'F3%' or Diag_Code like 'F4%'" +
					" or Diag_Code like 'F5%' or Diag_Code like 'F6%' or Diag_Code like 'F7%' or Diag_Code like 'F8%' or Diag_Code like 'F9%')";
				break;
			case 'TubRegistry':
				filter = " and (Diag_Code like 'A15%' or Diag_Code like 'A16%' or Diag_Code like 'A17%' or Diag_Code like 'A18%' or Diag_Code like 'A19%'";
				break;
			case 'PersonPregnansy': //gaf 109848 27032018 добавлен фильтр по ufa
				filter = getRegionNick() == 'ufa' ? " and (Diag_Code like 'Z32%' or Diag_Code like 'Z33%' or Diag_Code like 'Z34%' or Diag_Code like 'Z35%' or Diag_Code like 'Z36%' or Diag_Code like 'O%') " : '';
				break;
			case 'ExternalCause':
				if(q.search(new RegExp("^[VWXY]", "i")) < 0){
					filter = " and Diag_id < 0";
				}
				break;
		}

		return filter;
	},
	getConcreteDiagFilter: function (DiagLevelFilter_id, DiagFilter_id) // фильтр диагнозов по конкретному diag_id на нокретном уровне
	{
		var join = "";
		var filters = "";
		switch (DiagLevelFilter_id)
		{
			case '1':
				join += "LEFT JOIN Diag dg1 ON dg.Diag_id=dg1.Diag_id ";
				join += "LEFT JOIN Diag dg2 ON dg1.Diag_pid=dg2.Diag_id ";
				join += "LEFT JOIN Diag dg3 ON dg2.Diag_pid=dg3.Diag_id ";
				join += "LEFT JOIN Diag dg4 ON dg3.Diag_pid=dg4.Diag_id ";
				filters += "dg4.Diag_id = " + DiagFilter_id;
				break;
			case '2':
				join += "LEFT JOIN Diag dg1 ON dg.Diag_id=dg1.Diag_id ";
				join += "LEFT JOIN Diag dg2 ON dg1.Diag_pid=dg2.Diag_id ";
				join += "LEFT JOIN Diag dg3 ON dg2.Diag_pid=dg3.Diag_id ";
				filters += "dg3.Diag_id = " + DiagFilter_id;
				break;
			case '3':
				join += "LEFT JOIN Diag dg1 ON dg.Diag_id=dg1.Diag_id ";
				join += "LEFT JOIN Diag dg2 ON dg1.Diag_pid=dg2.Diag_id ";
				filters += "dg2.Diag_id = " + DiagFilter_id;
				break;
			case '4':
				join += "LEFT JOIN Diag dg1 ON dg.Diag_id=dg1.Diag_id ";
				filters += "dg1.Diag_id = " + DiagFilter_id;
				break;
		}
		filters += this.getDateFilters();

		return ' and Diag_id in (select dg.Diag_id from Diag dg ' + join + ' where ' + filters + ') ';
	},
	getMKBFilters: function ()
	{
		var query = '';
		if(this.MKB.isMain){
			if(getRegionNick() =='kareliya'){
				query = " and (isMain != 1)";
			}else if (getRegionNick() != 'perm'){

				query = " and Diag_Code not like 'X%' and Diag_Code not like 'Y%' and Diag_Code not like 'W%' and Diag_Code not like 'V%'";
			}
		}
		if(this.MKB.query){

			query += " and ((Mkb10Cause_id is null) or (Mkb10Cause_id not in "+this.MKB.query+"))"
		}

		return query;
	},
	getDateFilters: function ()
	{
		var filterDate = this.getDate();
		return ' and (Diag_begDate is null or Diag_begDate <= \'' + filterDate + '\') and (Diag_endDate is null or Diag_endDate >= \'' + filterDate + '\') ';
	},
	getDate: function ()
	{
		return Ext6.util.Format.date(this.filterDate || new Date(), 'Y-m-d');
	},

	exportSearchModule: function () // конфиг того, что нужно обязательно добавить к комбику диагноза
	{
		return {
			doQuery: this.doQuery,
			accessRightsFilter: this.accessRightsFilter,
			getRegistryTypeFilter: this.getRegistryTypeFilter,
			getDateFilters: this.getDateFilters,
			getDate: this.getDate,
			getConcreteDiagFilter: this.getConcreteDiagFilter,
			getMKBFilters: this.getMKBFilters,
			getNameAndDiagFilters: this.getNameAndDiagFilters
		};
	},
	exportDefaultSearchParams: function () // конфиг дефолтных параметров, которые станут свойствами комбика, только если уже не определены в нем заранее
	{
		return {
			withGroups: this.withGroups,
			MKB: this.MKB,
			Diag_level3_code:this.Diag_level3_code,
			PersonRegisterType_SysNick: this.PersonRegisterType_SysNick,
			MorbusType_SysNick: this.MorbusType_SysNick,
			onLoadStore: this.onLoadStore,
			onEmptyResults: this.onEmptyResults,
			listConfig: this.listConfig
		};
	},

	// главная функция поиска. Она перезаписывает базовую функцию поиска комбика, запускается автоматически
	doQuery: function(q) {
		var cur = this;
		if (q === undefined || q === null)
		{
			q = '';
		}

		q = q.trim();

		var addGroupFilter = "",
			nameAndDiagFilters = this.getNameAndDiagFilters(q);
		if (this.withGroups) addGroupFilter = " or DiagLevel_id = 3";

		//gaf 109848 для Регистра беременных, Основной диагноз добавлено && 'PersonPregnansy' != this.registryType
		if(this.Diag_level3_code && 'PersonPregnansy' != this.registryType) q = this.Diag_level3_code;//для уточненного диагноза


		if (true)
		{
			if (this.lastQuery != q )
			{
				this.lastQuery = q;
				this.selectedIndex = -1;

				var additQueryFilter = '';
				if ( this.additQueryFilter && this.additQueryFilter != '' ) additQueryFilter = " and " + this.additQueryFilter;

				if ( this.PersonRegisterType_SysNick && this.PersonRegisterType_SysNick.length > 0 && !this.PersonRegisterType_SysNick.inlist(['crazyRegistry','narkoRegistry'])) additQueryFilter += " and PersonRegisterType_List like '%" + this.PersonRegisterType_SysNick + "%'";
				if ( this.MorbusType_SysNick && this.MorbusType_SysNick.length > 0 && this.MorbusType_SysNick!='vzn' ) additQueryFilter += " and MorbusType_List like '%" + this.MorbusType_SysNick + "%'";

				if (this.registryType) additQueryFilter += this.getRegistryTypeFilter(this.registryType);

				additQueryFilter += this.getDateFilters();

				var where = 'where (DiagLevel_id = 4 ' + addGroupFilter + ') ' + nameAndDiagFilters + ' ' + additQueryFilter + ' ';

				if(this.MKB!=null) where += this.getMKBFilters();

				// ограничиваем только заданными диагнозами
				if ( this.DiagFilter_id && this.DiagLevelFilter_id && this.DiagFilter_id > 0 && this.DiagLevelFilter_id > 0 ) where += this.getConcreteDiagFilter(this.DiagLevelFilter_id, this.DiagFilter_id);


				where += ' limit 50'; // для скорости

				var store = this.getStore();

				console.log(this)

				store.load({

					params: {where: where},
					callback: function() {
						this.onLoadStore();

						var filterDenied = this.checkAccessRights ? this.accessRightsFilter : (function () {return true;});


						if (typeof this.baseFilterFn == 'function')
						{
							// Apply the filter on top of the base filter
							this.getStore().filterBy(function(record, id) {
								var result = false;
								result = this.baseFilterFn.call(this.baseFilterScope, record, id);

								return result && filterDenied(record);
							}, this);
						} else if (this.checkAccessRights) {
							this.getStore().filterBy(filterDenied);
						}


						if (this.getStore().getCount() > 0)
						{
							this.expand();
						}
						else {
							this.onEmptyResults();
						}

					}.createDelegate(this)
				});

			}
			else
			{
				this.selectedIndex = -1;
			}
		}
		else
		{
			this.getStore().removeAll();
		}
		this.afterQuery = true;
	}
});

Ext6.define('swDiagCombo', {
	extend: 'swBaseCombobox',
	store: {type: 'swDiagStore'},
	checkAccessRights: true,
	alias: 'widget.swDiagCombo',
	fieldLabel: 'Диагноз',
	displayField: 'Diag_Display',
	typeAhead: true,
	onLoadStore: Ext6.emptyFn,
	minChars: 1,
	valueField: 'Diag_id',
	plugins: [new Ext6.ux.Translit(false, false)],
	//cls: 'trigger-outside',
	selectOnFocus: true,
	filterDate: '',
	enableKeyEvents: true,
	queryDelay: 300,
	queryMode: 'local',
	setValue: function(v) 	// переопределяем функцию, для того чтобы при загрзке формы диагноз в комбике отображался с именем
	{						// потому что при инициализации комбик без записей, а диагноз устанавливается
		var combo = this,
			args = arguments;

		if (v && ! isNaN(v) && this.getStore().find('Diag_id', v) == -1)
		{
			this.getStore().load(
				{
					params:
						{
							where: 'where Diag_id = ' + v
						},
					callback: function (store, records, successful) {
						if (successful)
						{
							combo.setValue(v);
						} else {
							args[0] = null; // зануляем параметр value, если запись не пришла
						}
					}
				});
		}

		return this.callParent(args); // вызываем оригинальный setValue
	},
	highlightSearchResults: true, // выделять вхождение поискового запроса в результатах
	// для текста внутри поля
	displayTpl: Ext6.create('Ext6.XTemplate',
		'<tpl for=".">',
		'{Diag_Display}',
		'</tpl>'
	),
	listConfig: { // для выпадающего списка. Будет перезаписан, если highlightSearchResults: true
		loadingText: 'Загружаем диагнозы',
		emptyText: '<span style="color:red;font: normal 12px/17px tahoma, arial, verdana, sans-serif;">Диагнозов не найдено</span>',
		getInnerTpl: function() {

			return '{[values.Diag_Display]}\u00a0';
		}
	},
	onLoad: function(store, records, success) {
		var me = this;

		if (me.ignoreSelection > 0) {
			--me.ignoreSelection;
		}

		if (success) {

			if (me.value == null) {
				// Highlight the first item in the list if autoSelect: true
				if (store.getCount()) {
					me.doAutoSelect();
				}
			}
		}
	},
	setFilterByDate: function(dateValue) {

		var value = this.getValue(),
			combo = this,
			index;

		this.filterDate = Ext6.util.Format.date(dateValue, 'd.m.Y');
		this.clearValue();
		this.getStore().clearFilter();
		this.lastQuery = '';

		if ( !Ext6.isEmpty(dateValue) ) {
			this.getStore().filterBy(function(rec) {
				return ((Ext6.isEmpty(rec.get('Diag_begDate')) || rec.get('Diag_begDate') <= dateValue || (rec.get('Diag_begDate') !== null && !Ext6.isEmpty(rec.get('Diag_begDate').date) && rec.get('Diag_begDate').date <= Ext6.util.Format.date(dateValue, 'Y-m-d')) || (rec.get('Diag_begDate').toString().split('.').reverse().join('-') <= Ext6.util.Format.date(dateValue, 'Y-m-d')))
					&& (Ext6.isEmpty(rec.get('Diag_endDate')) || rec.get('Diag_endDate') >= dateValue || (rec.get('Diag_endDate') !== null && !Ext6.isEmpty(rec.get('Diag_endDate').date) && rec.get('Diag_endDate').date >= Ext6.util.Format.date(dateValue, 'Y-m-d')) || (rec.get('Diag_endDate').toString().split('.').reverse().join('-') >= Ext6.util.Format.date(dateValue, 'Y-m-d'))));
			});
		}

		index = this.getStore().findBy(function(rec) {
			return (rec.get(combo.valueField) == value);
		});

		if ( index >= 0 ) {
			this.setValue(value);
			this.fireEvent('select', this, this.findRecord(combo.valueField,value));
		} else {
			this.clearValue();
		}
	},
	clearBaseFilter: function()
	{
		this.baseFilterFn = null;
		this.baseFilterScope = null;
		this.lastQuery = null;
	},
	setBaseFilter: function(fn, scope)
	{
		this.baseFilterFn = fn;
		this.baseFilterScope = scope || this;
		this.store.filterBy(fn, scope);
		this.lastQuery = null;
	},
	onTabAction: function(e){
		return false;
	},
	listeners: {
		keydown: function(inp, e) {


			if (e.getKey() == e.TAB)
			{
				this.onTabAction(e);
			}
			else
			{
				if ( e.getKey() == e.END) {
					this.inKeyMode = true;
					this.select(this.store.getCount() - 1);
				}

				if ( e.getKey() == e.HOME) {
					this.inKeyMode = true;
					this.select(0);
				}

				if ( e.getKey() == e.PAGE_UP) {
					this.inKeyMode = true;
					var ct = this.store.getCount();

					if ( ct > 0 ) {
						if ( this.selectedIndex == -1 ) {
							this.select(0);
						}
						else if ( this.selectedIndex != 0 ) {
							if ( this.selectedIndex - 10 >= 0 ) {
								this.select(this.selectedIndex - 10);
							}
							else {
								this.select(0);
							}
						}
					}
				}

				if ( e.getKey() == e.PAGE_DOWN)
				{
					if (!this.isExpanded)
					{
						this.onTriggerClick();
					}
					else
					{
						this.inKeyMode = true;
						var ct = this.store.getCount();
						if (ct > 0)
						{
							if (this.selectedIndex == -1)
							{
								this.select(0);
							}
							else if (this.selectedIndex != ct - 1)
							{
								if (this.selectedIndex + 10 < ct - 1)
									this.select(this.selectedIndex + 10);
								else
									this.select(ct - 1);
							}
						}
					}
				}

				if (e.shiftKey == false && e.getKey() == Ext6.EventObject.TAB && inp.focusOnTab != null && inp.focusOnTab.toString().length > 0)
				{
					e.stopEvent();
					if (Ext6.getCmp(this.focusOnTab))
					{
						Ext6.getCmp(this.focusOnTab).focus(true);
					}
				}

				if (e.shiftKey == true && e.getKey() == Ext6.EventObject.TAB && inp.focusOnShiftTab != null && inp.focusOnShiftTab.toString().length > 0)
				{
					e.stopEvent();
					if (Ext6.getCmp(this.focusOnShiftTab))
					{
						Ext6.getCmp(this.focusOnShiftTab).focus(true);
					}
				}

				if (e.altKey || e.ctrlKey || e.shiftKey)
					return true;

				if ( e.getKey() == e.DELETE)
				{
					inp.setValue('');
					inp.setRawValue("");
					inp.selectIndex = -1;
					if (inp.onClearValue)
						this.onClearValue();
					e.stopEvent();
					return true;
				}

				if (e.getKey() == e.F4)
				{
					this.onTriggerClick();
				}

				if (e.getKey() == Ext6.EventObject.TAB) {
					this.onTabKeyDown(e);
				}
			}
		},
		blur: function ()
		{
			if (this.getValue() == this.getRawValue()) // если это так, значит в значении комбика простой текст, а не id
			{
				this.setValue(null);
			}
		}
	},
	triggers: {
		search: {
			cls: 'x6-form-search-trigger',
			//extraCls: 'search-icon-out',
			handler: function() {
				var combo = this,
					formPanel = this.up('form'),
					form = formPanel.getForm();

				let setDate = form.findField('EvnVizitPL_setDate') ? form.findField('EvnVizitPL_setDate').getValue() : '';
				let filterDate = Ext6.util.Format.date(setDate, 'Y-m-d');

				if (!filterDate) {
					filterDate = Ext6.util.Format.date(Date.parseDate(getGlobalOptions().date, 'd.m.Y'), 'Y-m-d');
				}

				var searchWindow = getWnd('DiagSearchTreeWindow');

				var filterDenied = combo.checkAccessRights ? combo.accessRightsFilter : (function () {return true;});

				// TODO вынести параметры для поиска с комбика диагноза в отдельный объект и цеплять их к гриду и дереву при каждом открытии окна
				searchWindow.show({
					filterDate: filterDate,
					baseFilterFn: (typeof(combo.baseFilterFn) == 'function') ? combo.baseFilterFn : filterDenied,
					onSelect: function(data) {

						if (combo.getStore().find('Diag_id', data.Diag_id) !== -1)
						{
							combo.setValue(data.Diag_id);
						} else
						{
							combo.getStore().removeAll();
							combo.getStore().loadData([data], true);
							combo.setValue(data.Diag_id);
						}

						var record = combo.getStore().findRecord(combo.valueField, data[combo.valueField]);
						combo.fireEvent('select', combo, record);
						combo.fireEvent('change', combo, data[combo.valueField]);
						return true;
					}
				});
			}
		}
	},
	initComponent: function ()
	{
		var searchModule = Ext6.create('DiagSearchModule'),
			requiredParams = searchModule.exportSearchModule(),
			defaultParams = searchModule.exportDefaultSearchParams();

		if (this.highlightSearchResults === true)
		{
			requiredParams.listConfig = defaultParams.listConfig;
		}

		Ext6.apply(this, requiredParams); // все свойства добавятся и перезапишутся
		Ext6.applyIf(this, defaultParams); // существующие свойства не перезапишутся

		this.callParent(arguments);
	}
});

/**
 * Конец блока диагноз
 */

/**
 * Комбобокс диагноза для стоматки (выбирается среди диагнозов всех заболеваний, выводится вместе с характером заболевания).
 */
Ext6.define('swDiagDeseaseCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.swDiagDeseaseCombo',
	mode: 'local',
	minChars: 0,
	fieldLabel: langs('Диагноз'),
	forceSelection: true,
	triggerAction: 'all',
	tpl: new Ext6.XTemplate(
		'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: center;">',
		'<td style="padding: 2px; width: 10%;">Код диагноза</td>',
		'<td style="padding: 2px; width: 70%;">Диагноз</td>',
		'<td style="padding: 2px; width: 20%;">Характер заболевания</td></tr>',
		'<tpl for="."><tr class="x6-boundlist-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
		'<td style="padding: 2px;">{Diag_Code}&nbsp;</td>',
		'<td style="padding: 2px;">{Diag_Name}&nbsp;</td>',
		'<td style="padding: 2px;">{DeseaseType_Name}&nbsp;</td>',
		'</tr></tpl>',
		'</table>'
	),
	listWidth: 600,
	displayField: 'displayField',
	valueField: 'Diag_id',
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'displayField', type:'string', convert: function(val,row) {
					if (row.get('Diag_id')) {
						return row.get('Diag_Code') + '. ' + row.get('Diag_Name');
					} else {
						return '';
					}
				}},
				{name: 'Diag_id', type: 'int'},
				{name: 'Diag_Code', type: 'string'},
				{name: 'Diag_Name', type: 'string'},
				{name: 'DeseaseType_id', type: 'int'},
				{name: 'Diag_IsCurrent', type: 'int'},
				{name: 'DeseaseType_Name', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'Diag_Code',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=EvnVizit&m=loadDiagCombo',
				reader: {
					type: 'json'
				}
			},
			mode: 'local'
		});

		me.callParent();
	}
});

Ext6.define('swProfGoalCombo', {
	alias: 'widget.swProfGoalCombo',
	extend: 'swBaseCombobox',
	fieldLabel: langs('Цель профосмотра'),
	displayField: 'ProfGoal_Name',
	codeField: 'ProfGoal_Code',
	hiddenName: 'ProfGoal_id',
	valueField: 'ProfGoal_id',
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'ProfGoal_id', type: 'int'},
				{name: 'ProfGoal_Code', type: 'int'},
				{name: 'ProfGoal_Name', type: 'string'}
			],
			autoLoad: false,
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=ProfService&m=getProfGoalFormList',
				reader: {
					type: 'json'
				}
			},
			mode: 'local'
		});
		me.callParent();
	}
});

Ext6.define('swConsultingFormCombo', {
	alias: 'widget.swConsultingFormCombo',
	extend: 'swBaseCombobox',
	fieldLabel: 'Форма консультации',
	//~ forceSelection: true,
	//~ triggerAction: 'all',
	displayField: 'ConsultingForm_Name',
	valueField: 'ConsultingForm_id',
	//~ queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'ConsultingForm_id', type:'int'},
				{name: 'ConsultingForm_Name', type:'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'ConsultingForm_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=EvnDirection&m=getConsultingFormList',
				reader: {
					type: 'json'
				}
			},
			mode: 'local'
		});

		me.callParent(arguments);
	}
});

Ext6.define('swPayTypeCombo', {
	extend: 'swCommonSprCombo',
	alias: 'widget.swPayTypeCombo',
	fieldLabel: langs('Вид оплаты'),
	comboSubject: 'PayType',
	displayField: 'PayType_Name',
	valueField: 'PayType_id',
	allowSysNick: true,
	useCommonFilter: false,
	loadParams: null,
	autoLoad: false,

	initComponent: function()
	{
		this.callParent(arguments);

		if (this.useCommonFilter)
		{
			switch (getRegionNick()) {
				case 'penza':
					this.loadParams = {params: {where: ' where PayType_Code <= 7'}};
					break;
				case 'kz':
					this.loadParams = {params: {where: ' where PayType_Code != 3'}};
					break;
				default:
					this.loadParams = {params: {where: ' where PayType_Code < 7 or PayType_Code = 9 or PayType_Code = 10'}};
					break;
			}
		} else
		{
			if(getRegionNick() == 'kz'){
				this.loadParams = {params: {where: ' where PayType_Code != 3'}};
			}
		}

		this.autoLoad = ! (typeof this.loadParams == 'object' && ! Ext6.isEmpty(this.loadParams));

		this.addListener('render',function(combo) {
			if(combo.autoLoad == false)
			{
				if(combo.loadParams)
				{
					combo.loadParams = Object.assign({}, combo.loadParams, {object: this.comboSubject, PayType_id: '', PayType_Name: '', PayType_Code: '', PayType_SysNick: ''});
					combo.getStore().load(combo.loadParams);
				} else if(combo.getStore().getCount() == 0)
				{
					combo.getStore().load();
				}
			}
		});
	}
});

Ext6.define('swDirTypeBaseJournalCombo', {
	alias: 'widget.swDirTypeBaseJournalCombo',
	extend: 'swCommonSprCombo',
	fieldLabel: langs('Тип направления'),
	comboSubject: 'DirType',
	//~ useCommonFilter: false,
	displayCode: false,
	displayField: 'DirType_Name',
	codeField: 'DirType_Code',
	valueField: 'DirType_id',
	//~ loadParams: {params: {where: ' where DirType_id not in (7, 18, 19)', DirType_id: null, DirType_Name: null, DirType_Code: null}},
	//~ loadParams: { where: ' where DirType_id not in (7, 18, 19)' },
	//~ initComponent: function() {
		//~ var me = this;

		//~ this.addListener('render',function(combo) {
			//~ if(combo.autoLoad == false) {
				//~ if(combo.loadParams) {
					//~ combo.getStore().removeAll();
					//~ this.loadParams.params.object = this.comboSubject;
					//~ combo.getStore().load(combo.loadParams);
				//~ } else if(combo.getStore().getCount() == 0) {
					//~ combo.getStore().load();
				//~ }
			//~ }
		//~ });

		//~ me.callParent(arguments);
	//~ }
});

Ext6.define('swLpuUnitTypeCombo', {
	alias: 'widget.swLpuUnitTypeCombo',
	extend: 'swCommonSprCombo',
	comboSubject: 'LpuUnitType',
	editable: false,
	codeField: 'LpuUnitType_Code',
	displayField:'LpuUnitType_Name',
	valueField: 'LpuUnitType_id',

	onLoadStore: Ext6.emptyFn,
	//~ tpl:
			//~ '<tpl for="."><div class="x-combo-list-item">'+
			//~ '<font color="red">{LpuUnitType_Code}</font>&nbsp;{LpuUnitType_Name}'+
			//~ '</div></tpl>',
});

Ext6.define('swLpuSectionProfileCombo', {
	alias: 'widget.swLpuSectionProfileCombo',
	extend: 'swCommonSprCombo',
	editable: true,
	codeField: 'LpuSectionProfile_Code',
	displayField:'LpuSectionProfile_Name',
	valueField: 'LpuSectionProfile_id',
	sortField: (getRegionNick() == 'astra' ? 'LpuSectionProfile_Code' : 'LpuSectionProfile_Name'),
	fieldLabel: langs('Профиль'),
	comboSubject: 'LpuSectionProfile',
	moreFields: [
			{name: 'LpuSectionProfile_begDT', type: 'date', dateFormat: 'd.m.Y'},
			{name: 'LpuSectionProfile_endDT', type: 'date', dateFormat: 'd.m.Y'},
			{name: 'LpuSectionProfileGRAPP_Code', type: 'int'},
			{name: 'LpuSectionProfileGRKSS_Code', type: 'int'},
			{name: 'LpuSectionProfileGRSZP_Code', type: 'int'},
			{name: 'LpuSectionProfile_fedid', type: 'int'}
	],
	/*queryMode: 'remote',
	initComponent: function() {
		var me = this;

		//me.store.sorters.property = (getRegionNick() == 'astra' ? 'LpuSectionProfile_Code' : 'LpuSectionProfile_Name');

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'LpuSectionProfile_id',    type:'int'},
				{name: 'LpuSectionProfile_Code', type:(getRegionNick() == 'astra' ? 'int' : 'string')},
				{name: 'LpuSectionProfile_SysNick', type: 'string'},
				{name: 'LpuSectionProfile_Name',  type:'string'},
				{name: 'LpuSectionProfile_begDT', type: 'date', dateFormat: 'd.m.Y'},
				{name: 'LpuSectionProfile_endDT', type: 'date', dateFormat: 'd.m.Y'},
				{name: 'LpuSectionProfileGRAPP_Code', type: 'int'},
				{name: 'LpuSectionProfileGRKSS_Code', type: 'int'},
				{name: 'LpuSectionProfileGRSZP_Code', type: 'int'},
				{name: 'LpuSectionProfile_fedid', type: 'int'}
			],
			autoLoad: false,
			sorters: {
				property: (getRegionNick() == 'astra' ? 'LpuSectionProfile_Code' : 'LpuSectionProfile_Name'),
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=MongoDBWork&m=getData',
				reader: {
					type: 'json'
				},
				//~ extraParams: me.baseParams
			},
			baseParams: me.baseParams,
			tableName: me.tableName,
			mode: me.queryMode
		});
		this.callParent(arguments);
	},*/

/*	setValue: function(v) {
		var text = v;
		if(this.valueField){
			var r = this.findRecord(this.valueField, v);
			if(r){
				text = r.data[this.displayField];
				if ( r.data['LpuSectionProfile_endDT'] != '' && r.data['LpuSectionProfile_endDT'] < Date.parseDate(getGlobalOptions().date, 'd.m.Y') )
				{
					text = text + ' (закрыт '+ Ext.util.Format.date(r.data['LpuSectionProfile_endDT'], "d.m.Y")   + ')';
				}
			} else if(this.valueNotFoundText !== undefined){
				text = this.valueNotFoundText;
			}
		}
		this.lastSelectionText = text;
		if(this.hiddenField){
			this.hiddenField.value = v;
		}
		//~ Ext6.form.ComboBox.superclass.setValue.call(this, text);
		this.value = v;
	},*/
});

Ext6.define('swMedSpecFedCombo', {
	alias: 'widget.swMedSpecFedCombo',
	extend: 'swCommonSprCombo',
	codeField: 'MedSpec_Code',
	displayField: 'MedSpec_Name',
	valueField: 'MedSpec_id',
	editable: false,
	comboSubject: 'MedSpecFed',
	queryMode: 'remote',

/*	initComponent: function() {
		this.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'MedSpec_id',    type:'int'},
				{name: 'MedSpec_Code', type: 'string'},
				{name: 'MedSpec_Name', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'MedSpec_Code',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=MongoDBWork&m=getData',
				reader: {
					type: 'json'
				},
				extraParams: {object: 'MedSpecFed', MedSpec_id:'', MedSpec_Code:'', MedSpec_Name:''}
			},
			mode: 'remote'
		});

		this.callParent(arguments);
	}*/
});

Ext6.define('swUslugaCategoryCombo', {
	alias: 'widget.swUslugaCategoryCombo',
	extend: 'swCommonSprCombo',
	comboSubject: 'UslugaCategory',
	typeCode: 'int',
	isStom: false,
	allowSysNick: true,
	loadParams: null,
	autoLoad: false,
	queryMode: 'remote',
	initComponent: function()
	{
		this.autoLoad = (typeof this.loadParams != 'object');

		if ( getGlobalOptions().region && !this.loadParams  )
		{
			var categoryList = (typeof this.additionalCategoryList == 'object' ? this.additionalCategoryList : []);

			switch ( getRegionNick() )
			{
				case 'kareliya':
					categoryList = categoryList.concat([ 'gost2011', 'tfoms', 'stomoms', 'stomklass' ]);
				break;

				case 'kz':
					categoryList = categoryList.concat([ 'classmedus', 'lpu', 'MedOp' ]);
				break;

				case 'penza':
					categoryList = categoryList.concat([ 'gost2011', 'tfoms' ]);
				break;

				case 'perm':
					categoryList = categoryList.concat([ 'lpu', 'tfoms', 'gost2011' ]);
				break;

				case 'pskov':
					categoryList = categoryList.concat([ 'pskov_foms', 'gost2011', 'lpu' ]);
				break;

				case 'ufa':
					categoryList = categoryList.concat([ 'gost2011' ]);
				break;

				case 'ekb':
					categoryList = categoryList.concat([ 'gost2011', 'tfoms' ]);
				break;

				default:
					categoryList = categoryList.concat([ 'tfoms', 'promed', 'gost2011', 'lpu', 'syslabprofile', 'lpulabprofile' ]);
				break;
			}

			this.loadParams = {
				params: {
					where: "where UslugaCategory_SysNick in ('" + categoryList.join("', '") + "')"
				}
			};
		}
		this.callParent(arguments);
	},
/*	listeners: {
		'change': function (combo, newValue, oldValue) {
			var index = combo.getStore().findBy(function (rec) {
				if ( rec.get('UslugaCategory_id') == newValue ) {
					if(combo.isFromReports){
						combo.ownerCt.items.each(function(i){
							if (i.codeField == 'UslugaComplex_Code')
							{
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get('UslugaCategory_id') == newValue);
								});
								var record = combo.getStore().getAt(index);
								i.setUslugaCategoryList([record.get('UslugaCategory_SysNick')]);
							}
						});
					}
					return true;
				}
				else {
					return false;
				}
			});

			combo.fireEvent('select', combo, combo.getStore().getAt(index));
		}.createDelegate(this),

		'render' :function(combo) {
			if(combo.autoLoad == false) {
				combo.getStore().load();
			}
		}.createDelegate(this)
	}*/
});

Ext6.define('swMedService2Combo', {
	extend: 'swBaseCombobox',
	alias: 'widget.swMedService2Combo',
	//~ forceSelection: true,
	triggerAction: 'all',
	codeField: 'MedService_id',
	displayField: 'MedService_Nick',
	valueField: 'MedService_id',
	queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'Служба',
	getById: function(id) {
		return this.getStore().getAt(this.getStore().findExact('MedService_id',id));
	},
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'MedService_id', mapping: 'id', type: 'int'},
				{name: 'MedService_Nick', mapping: 'MedService_Nick'},
				{name: 'MedService_Name', mapping: 'MedService_Name'},
				{name: 'Lpu_id_Nick', mapping: 'Lpu_id_Nick'},
				{name: 'Address_Address', mapping: 'Address_Address'},
				{name: 'Org_id', mapping: 'Org_id'},
				{name: 'OrgStruct_id', mapping: 'OrgStruct_id'},
				{name: 'Lpu_id', mapping: 'Lpu_id'},
				{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
				{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
				{name: 'LpuSectionProfile_id_List', mapping: 'LpuSectionProfile_id_List'},
				{name: 'LpuSection_id', mapping: 'LpuSection_id'}
			],
			autoLoad: false,
			idProperty: 'MedService_id',
			sorters: {
				property: 'MedService_Nick',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url:'/?c=MedService&m=loadList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});


Ext6.define('MedSpecOmsModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.MedSpecOmsModel',
	idProperty: 'MedSpecOms_id',
	fields: [
		{name: 'MedSpecOms_id',    type:'int'},
		{name: 'MedSpec_fedid',    type:'int'},
		{name: 'MedSpecOms_Code',  type:'int'},
		{name: 'MedSpecOms_Name',  type:'string'},
		{name: 'MedSpecOms_FullName',  type:'string'}
	]
});

Ext6.define('MedSpecOmsStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.MedSpecOmsStore',
	model: 'MedSpecOmsModel',
	autoLoad: false,
	sorters: {
		property: 'MedSpecOms_Code',
		direction: 'ASC'
	},
	proxy: {
		type: 'ajax',
		url : '/?c=EvnUsluga&m=loadMedSpecOmsList',
		reader: {
			type: 'json'
		}
	}
});


// Комбобокс выбора специальности в зависимости от профиля
Ext6.define('MedSpecOmsWithFedCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.MedSpecOmsWithFedCombo',
	//queryMode: 'local',
	fieldLabel: langs('Специальность'),
	store: {type: 'MedSpecOmsStore'},

	triggerAction: 'all',
	displayField: 'MedSpecOms_FullName',
	valueField: 'MedSpecOms_id',
	name: 'MedSpecOms_id',
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item">',
		'<table><tr><td style="width: 40px;"><font color="red">{MedSpecOms_Code}</font>&nbsp;</td><td>{MedSpecOms_Name}&nbsp;</td></tr></table>',
		'</div></tpl>'
	),

	getBaseForm: function() {
		return this.findParentByType('form').getForm();// === this.up('form').getForm();
	},

	onChangePayTypeField: function(field, sys_nick) {
		this.setAllowBlank(this.hidden || 'oms' != sys_nick);
	},
	setAllowInput: function(isAllow) {
		var	cb = this;
		cb.setContainerVisible(isAllow);
		if (cb.hidden) {
			cb.getStore().removeAll();
			cb.clearValue();
		}
		cb.setAllowBlank(cb.hidden || 'oms' != cb.getBaseForm().findField('PayType_id').getFieldValue('PayType_SysNick'));
		if (false == cb.hidden && 0 == cb.getStore().getCount()) {
			cb.getStore().load({
				callback: function() {
					if (cb.getStore().getById(cb.getValue())) {
						cb.setValue(cb.getValue());
					} else {
						cb.clearValue();
					}
				}
			});
		}
	},
	onChangeUslugaPlaceField: function(field, code) {
		this.setAllowInput(getRegionNick() == 'perm' && code && 1 != code);
	},
	onShowWindow: function(win) {
		var	cb = this;
		cb.setAllowBlank(true);
		cb.setContainerVisible(false);
		cb.lastQuery = '';
		cb.getStore().baseParams = {};
		cb.getStore().removeAll();
		cb.clearValue();
	}
});

Ext6.define('PayTypeCombo', {
	extend: 'swCommonSprCombo',
	alias: 'widget.PayTypeCombo',
	fieldLabel: langs('Вид оплаты'),
	comboSubject: 'PayType',
	//typeCode: 'int',
	allowSysNick: true,
	useCommonFilter: false,
	loadParams: null,
	autoLoad: false,

	initComponent: function() {
		this.callParent(arguments);

		if (this.useCommonFilter)
		{
			switch (getRegionNick()) {
				case 'penza':
					this.loadParams = {params: {where: ' where PayType_Code <= 7'}};
					break;
				case 'kz':
					this.loadParams = {params: {where: ' where PayType_Code != 3'}};
					break;
				default:
					this.loadParams = {params: {where: ' where PayType_Code < 7 or PayType_Code = 9 or PayType_Code = 10'}};
					break;
			}
		} else
		{
			if(getRegionNick() == 'kz'){
				this.loadParams = {params: {where: ' where PayType_Code != 3'}};
			}
		}

		this.autoLoad = ! (typeof this.loadParams == 'object' && ! Ext6.isEmpty(this.loadParams));
		this.addListener('render',function(combo) {
			if(combo.autoLoad == false) {

				if(combo.loadParams) {
					combo.getStore().removeAll();
					combo.getStore().load(combo.loadParams);
				} else if(combo.getStore().getCount() == 0) {
					combo.getStore().load();
				}
			}
		});
	}
});

Ext6.define('EvnPrescrModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.EvnPrescrModel',
	idProperty: 'EvnPrescr_id',
	fields: [
		{ name: 'EvnPrescr_id'},
		{ name: 'PrescriptionType_id'},
		{ name: 'PrescriptionType_Name'},
		{ name: 'PrescriptionType_Code'},
		{ name: 'EvnPrescr_pid'},
		{ name: 'Lpu_id'},
		{ name: 'UslugaComplex_2011id'},
		{ name: 'EvnPrescr_setDate', type: 'date', dateFormat: 'd.m.Y'},
		{ name: 'EvnPrescr_Text'}
	]
});

Ext6.define('EvnPrescrStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.EvnPrescrStore',
	model: 'EvnPrescrModel',
	autoLoad: false,
	proxy: {
		type: 'ajax',
		url: '/?c=EvnPrescr&m=loadEvnPrescrCombo',
		reader: {
			type: 'json'
		}
	}
});


Ext6.define('EvnPrescrCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.EvnPrescrCombo',
	displayField: 'EvnPrescr_Text',
	//editable: true,
	emptyText: '',
	enableKeyEvents: true,
	fieldLabel: langs('Назначение'),
	forceSelection: true,
	hiddenName: 'EvnPrescr_id',
	minChars: 1,
	minLength: 0,
	listConfig:{
		userCls: 'evn-prescr-combo-menu'
	},
	queryMode: 'remote',
	store: {type: 'EvnPrescrStore'},
	selectOnFocus: true,
	triggerAction: 'all',
	valueField: 'EvnPrescr_id',
	uslugaCombo: null,
	uslugaCatCombo: null,
	hasLoaded: false,
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item evn-prescr-combo-item"><table style="width: 100%;">',
		'<tr>',
		//'<td style="padding: 2px; width: 70%;">Назначение</td>',
		//'<td style="padding: 2px; width: 30%;">Тип</td>',
		//'<tr class="x6-boundlist-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
		'<td><p class="evn-prescr-text">{EvnPrescr_Text}&nbsp;</p></td>',
		'</tr>',
		'<tr>',
		'<td><p class="evn-prescr-type-name">{PrescriptionType_Name}&nbsp;</p></td>',
		'</tr>',
		'</table></div></tpl>'
	),

	clearBaseParams: function() {
		this.lastQuery = 'This query sample that is not will never appear';
		this.getStore().baseParams.PrescriptionType_Code = null;
		this.getStore().baseParams.savedEvnPrescr_id = null;
		this.getStore().baseParams.newEvnPrescr_id = null;
		this.getStore().baseParams.EvnPrescr_pid = null;
		this.getStore().baseParams.withoutEvnDirection = null;
	},
	setPrescriptionTypeCode: function(code) {
		this.getStore().baseParams.PrescriptionType_Code = code;
		if (this.uslugaCombo) {
			this.getStore().baseParams.withoutEvnDirection = 1;
		}
	},
	filterAndAutoComplite: function(action, rec) {
		//log({filterAndAutoComplite: rec});
		var me = this;
		if (me.uslugaCombo) {
			var pr_rec = me.getStore().getById(me.getValue()),
				uc2011id = null;
			if (rec) {
				// услуга изменена, накладываем фильтр для назначений по ГОСТ-11
				uc2011id = rec.get('UslugaComplex_2011id');
				me.getStore().filter('UslugaComplex_2011id', uc2011id);
				// очищаем назначение, если оно не совпадает по ГОСТ-11
				if (!pr_rec || pr_rec.get('UslugaComplex_2011id') != uc2011id) {
					me.setValue(null);
					pr_rec = null;
				}
			} else {
				// выбранная услуга убрана
				uc2011id = -1;
			}
			if (pr_rec) {
				return false;
			}
			// При изменении услуги она должна связываться с назначением
			// при совпадении ИД услуги или совпадении по коду ГОСТ-11
			// автоматически, если подходящих назначений одно.
			if (me.getStore().getCount() == 1) {
				me.setValue(me.getStore().getAt(0).get('EvnPrescr_id'));
			}
		}
		return true;
	},
	onChangedUslugaCombo: function(action, rec) {
		//log({onChangedUslugaCombo: action});
		var me = this;
		if (me.uslugaCombo) {
			// восстанавливаем список назначений
			me.getStore().clearFilter();
			if (me.hasLoaded) {
				me.filterAndAutoComplite(action, rec);
			} else {
				me.getStore().load({
					callback: function(){
						me.hasLoaded = true;
						me.filterAndAutoComplite(action, rec);
					}
				});
			}
		}
	},
	applyChanges: function(newValue) {
		//log({applyChanges: newValue});
		var me = this,
			rec = me.getStore().getById(newValue);
		if (me.uslugaCombo && me.uslugaCatCombo) {
			var code = null,
				uc2011id = null,
				index = -1,
				uc_rec = null;
			if (rec && rec.get('PrescriptionType_Code')) {
				code = rec.get('PrescriptionType_Code');
			}
			me.uslugaCombo.setPrescriptionTypeCode(code);
			if (rec && rec.get('UslugaComplex_2011id')) {
				uc2011id = rec.get('UslugaComplex_2011id');
			}
			me.uslugaCombo.setUslugaComplex2011Id(uc2011id);
			if (uc2011id > 0) {
				me.uslugaCombo.getStore().load({
					callback: function() {
						if ( me.uslugaCombo.getStore().getCount() == 1 ) {
							index = 0;
						}
						if ( me.uslugaCombo.getStore().getCount() > 1 ) {
							index = me.uslugaCombo.getStore().find('UslugaComplex_2011id', uc2011id);
						}
						if (index >= 0) {
							uc_rec = me.uslugaCombo.getStore().getAt(index);
						}
						if ( uc_rec ) {
							me.uslugaCombo.setValue(uc_rec.get('UslugaComplex_id'));
							if ( Ext6.isEmpty(me.uslugaCatCombo.getValue()) ) {
								index = me.uslugaCatCombo.getStore().findBy(function(rec) {
									return (rec.get('UslugaCategory_id') == uc_rec.get('UslugaCategory_id'));
								})
								if ( index >= 0 ) {
									me.uslugaCatCombo.setValue(uc_rec.get('UslugaCategory_id'));
								}
							}
							//me.uslugaCombo.setUslugaCategoryList([uc_rec.get('UslugaCategory_SysNick')]);
						} else {
							me.uslugaCombo.clearValue();
						}
					}
				});
			}
		}
		return true;
	},
	initComponent: function() {
		var me = this;

		this.callParent(arguments);


		this.getStore().on('beforeload', function(store, options) {
			if (options.params && options.params.EvnPrescr_id) {
				return options;
			}
			if (store.getProxy().getExtraParams().EvnPrescr_pid) {
				return false;
			}
			if (me.hasLoaded && me.uslugaCombo) {
				if (typeof options.callback == 'function') {
					options.callback(me.getStore().getRange(), options, true);
				}
				return false;
			}
			return options;
		});
	}
});

Ext6.define('UslugaCategoryCombo', {
	extend: 'swCommonSprCombo',
	alias: 'widget.UslugaCategoryCombo',
	comboSubject: 'UslugaCategory',

	fieldLabel: langs('Категория услуги'),
	//typeCode: 'int',

	additionalCategoryList: [],
	isStom: false,
	allowSysNick: true,
	loadParams: null,
	autoLoad: false,
	initComponent: function()
	{
		this.callParent(arguments);

		if ( getGlobalOptions().region && ! this.loadParams  )
		{
			var categoryList = (typeof this.additionalCategoryList == 'object' ? this.additionalCategoryList : []);

			switch ( getRegionNick() ) {
				case 'kareliya':
					categoryList = categoryList.concat([ 'gost2011', 'tfoms', 'stomoms', 'stomklass' ]);
					break;

				case 'kz':
					categoryList = categoryList.concat([ 'classmedus', 'lpu', 'MedOp' ]);
					break;

				case 'penza':
					categoryList = categoryList.concat([ 'gost2011', 'tfoms' ]);
					break;

				case 'perm':
					categoryList = categoryList.concat([ 'lpu', 'tfoms', 'gost2011'/*, 'gost2011r'*/ ]);
					break;

				case 'pskov':
					categoryList = categoryList.concat([ 'pskov_foms', 'gost2011', 'lpu' ]);
					break;

				case 'ufa':
					categoryList = categoryList.concat([ 'gost2011' ]);
					break;

				case 'ekb':
					categoryList = categoryList.concat([ 'gost2011', 'tfoms' ]);
					break;

				default:
					categoryList = categoryList.concat([ 'tfoms', 'promed', 'gost2011', 'lpu', 'syslabprofile', 'lpulabprofile' ]);
					break;
			}

			this.loadParams = {params: {where: "where UslugaCategory_SysNick in ('" + categoryList.join("', '") + "')"}};

		}


		if(this.autoLoad == false)
		{
			if (this.loadParams) {
				this.getStore().load(this.loadParams);
			} else if(this.getStore().getCount() == 0) {
				this.getStore().load();
			}
		}

		return true;
	},
	// listeners: {
	// 	change: function (combo, newValue, oldValue)
	// 	{
	// 		var index = combo.getStore().findBy(function (rec)
	// 		{
	// 			if ( rec.get('UslugaCategory_id') == newValue )
	// 			{
	// 				if(combo.isFromReports)
	// 				{
	// 					combo.ownerCt.items.each(function(i)
	// 					{
	// 						if (i.codeField == 'UslugaComplex_Code')
	// 						{
	// 							var index = combo.getStore().findBy(function(rec)
	// 							{
	// 								return (rec.get('UslugaCategory_id') == newValue);
	// 							});
	//
	// 							var record = combo.getStore().getAt(index);
	// 							i.setUslugaCategoryList([record.get('UslugaCategory_SysNick')]);
	// 						}
	// 					});
	// 				}
	// 				return true;
	// 			}
	// 			else {
	// 				return false;
	// 			}
	// 		});
	//
	// 		combo.fireEvent('select', combo, combo.getStore().getAt(index));
	// 	}.createDelegate(this)
	// }
});

Ext6.define('UslugaComplexModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.UslugaComplexModel',
	idProperty: 'UslugaComplex_id',
	fields: [
		{ name: 'UslugaComplex_id'},
		{ name: 'UslugaComplex_2011id'},
		{ name: 'UslugaComplex_AttributeList'},
		{ name: 'UslugaCategory_id'},
		{ name: 'UslugaCategory_Name'},
		{ name: 'UslugaCategory_SysNick'},
		{ name: 'UslugaComplex_pid'},
		{ name: 'UslugaComplexLevel_id'},
		{ name: 'UslugaComplex_begDT', type: 'date', dateFormat: 'd.m.Y'},
		{ name: 'UslugaComplex_endDT', type: 'date', dateFormat: 'd.m.Y'},
		{ name: 'UslugaComplex_Code'},
		{ name: 'UslugaComplex_Name'},
		{ name: 'UslugaComplex_UET'},
		{ name: 'Fedswuslugacomplexnewcombo'},
		{ name: 'LpuSection_Name'},
		{ name: 'UslugaComplex_hasComposition', type: 'int' },
		{ name: 'LpuSectionProfile_id', type: 'int' },
		{ name: 'MedSpecOms_id', type: 'int' },
		{ name: 'SurveyTypeLink_IsPay', type: 'int' }
	]
});

Ext6.define('UslugaComplexStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.UslugaComplexStore',
	model: 'UslugaComplexModel',
	autoLoad: false,
	proxy: {
		type: 'ajax',
		url: '/?c=Usluga&m=loadNewUslugaComplexList',
		reader: {
			type: 'json'
		},
		mode: 'remote'
	}
});

Ext6.define('swVizitTypeCombo', {
	extend: 'swCommonSprCombo',
	alias: 'widget.swVizitTypeCombo',
	comboSubject: 'VizitType',
	fieldLabel: langs('Цель посещения'),
	typeCode: 'string',
	allowSysNick: true,
	moreFields: [
		{name: 'VizitType_begDT', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'VizitType_endDT', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'TreatmentClass_id', type: 'int'}
	],
	loadParams: null,
	autoLoad: false,
	EvnClass_id: 10,// параметр для фильтрации в зависимости от класса посещения
	filterDate: null,
	excCodeArray: [],
	filterTreatmentClassId: null,
	setTreatmentClass: function(TreatmentClass_id) {
		if (TreatmentClass_id) {
			this.filterTreatmentClassId = TreatmentClass_id;
		} else {
			this.filterTreatmentClassId = null;
		}
	},
	setFilterByDate: function(dateValue) {
		this.excCodeArray = [];
		this.filterDate = dateValue;
		this.applyFilter();
	},
	setFilterByDateAndCode: function(dateValue, codeArray) {
		if (typeof codeArray != 'object') {
			if (!Ext6.isEmpty(codeArray)) {
				this.excCodeArray = [codeArray];
			} else {
				this.excCodeArray = [];
			}
		} else {
			this.excCodeArray = codeArray;
		}

		this.filterDate = dateValue;
		this.applyFilter();
	},
	applyFilter: function() {
		var VizitType_id = this.getValue(),
			combo = this,
			index;

		this.clearValue();
		this.getStore().clearFilter();
		this.lastQuery = '';

		this.getStore().filterBy(function(rec) {
			var bool = true;

			if (!Ext6.isEmpty(combo.excCodeArray)) {
				if (rec.get('VizitType_Code').inlist(combo.excCodeArray)) {
					return false;
				}
			}

			if (!Ext6.isEmpty(combo.filterDate)) {
				if (!(
					(Ext6.isEmpty(rec.get('VizitType_begDT')) || rec.get('VizitType_begDT') <= combo.filterDate || (rec.get('VizitType_begDT') !== null && !Ext6.isEmpty(rec.get('VizitType_begDT').date) && rec.get('VizitType_begDT').date <= Ext6.util.Format.date(combo.filterDate, 'Y-m-d')))
					&& (Ext6.isEmpty(rec.get('VizitType_endDT')) || rec.get('VizitType_endDT') >= combo.filterDate || (rec.get('VizitType_endDT') !== null && !Ext6.isEmpty(rec.get('VizitType_endDT').date) && rec.get('VizitType_endDT').date >= Ext6.util.Format.date(combo.filterDate, 'Y-m-d')))
				)) {
					return false;
				}
			}

			if (getRegionNick() != 'kareliya' && !Ext6.isEmpty(combo.filterTreatmentClassId)) {
				var index = swTreatmentClassVizitTypeGlobalStore.findBy(function(r) {
					return (r.get('TreatmentClass_id') == combo.filterTreatmentClassId && r.get('VizitType_id') == rec.get('VizitType_id'));
				});

				if (index < 0) {
					return false;
				}
			}

			return true;
		});

		index = this.getStore().findBy(function(rec) {
			return (rec.get('VizitType_id') == VizitType_id);
		});

		if ( index >= 0 ) {
			this.setValue(VizitType_id);
			this.fireEvent('select', this, this.findRecord('VizitType_id',VizitType_id));
		} else {
			this.clearValue();
		}
	},
	initComponent: function() {
		this.callParent(arguments);

		switch (getRegionNick()) {
			case 'kareliya':
				this.loadParams = {params: {where: ' where VizitType_Code not in (41) '}};
				break;
		}
	}
});

Ext6.define('UslugaComplexCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.UslugaComplexCombo',
	showCodeField: true,
	to: null,
	DispClass_id: null,
	dispOnly: false,
	nonDispOnly: true,
	codeField: 'UslugaComplex_Code',
	displayField: 'UslugaComplex_Name',
	editable: true,
	cls: 'trigger-outside',
	emptyText: langs('Введите код или название услуги...'),
	enableKeyEvents: true,
	fieldLabel: langs('Услуга'),
	listConfig:{
		userCls: 'usluga-list-menu-combo'
	},
	name: 'UslugaComplex_id',
	store: {type: 'UslugaComplexStore'},
	minChars: 1,
	minLength: 0,
	//queryMode: 'remote',
	selectOnFocus: true,
	showUslugaComplexEndDate: false,
	showUslugaComplexLpuSection: true,
	valueField: 'UslugaComplex_id',
	triggerAction: 'all',
	//hideTrigger: false,
	disableBlurAction: false,
	onLoad: function ()
	{

	},
	setValue: function(v) 	// переопределяем функцию, для того чтобы при загрузке формы услуга в комбике отображалась с именем
	{						// потому что при инициализации комбик без записей, а услуга не устанавливается
		var combo = this,
			args = arguments;


		if (v && this.getRawValue() != v && ! isNaN(v) && this.getStore().find('UslugaComplex_id', v) == -1)
		{
			this.getStore().load(
				{
					params:
						{
							UslugaComplex_id: v
						},
					callback: function (store, records, successful)
					{
						var rec = this.findRecord('UslugaComplex_id', v);

						if (rec)
						{
							combo.setValue(rec);
						} else
						{
							combo.setValue(null);
						}
					}
				});
		}

		return this.callParent(args); // вызываем оригинальный setValue
	},
	triggers: {
		search: {
			cls: 'x6-form-search-trigger',
			extraCls: 'search-icon-out',
			handler: function ()
			{
				if (this.disabled) return false;
				var combo = this,
					win = this.getSearchWindow(),
					params = this.onBeforeShowSearchWindow(win, {
						onHide: function() {
							combo.onHideSearchWindow();
						},
						onSelect: function (obj) {
							combo.onSelectSearchWindow(win, obj);
						}
					});
				if (!win) return false;

				win.show(params);
				return true;
			}
		}
	},

	clearBaseParams: function() {
		this.lastQuery = '';

		this.getStore().getProxy().setExtraParams(
			{
				allowedUslugaComplexAttributeList: null,
				allowedUslugaComplexAttributeMethod: 'or',
				allowMorbusVizitCodesGroup88: 0,
				allowMorbusVizitOnly: 0,
				allowNonMorbusVizitOnly: 0,
				ignoreUslugaComplexDate: 0,
				disallowedUslugaComplexAttributeList: null,
				Mes_id: null,
				MesOldVizit_id: null,
				LpuLevel_Code: null,
				LpuSection_id: null,
				LpuSectionProfile_id: null,
				PayType_id: null,
				Person_id: null,
				uslugaCategoryList: null,
				uslugaComplexCodeList: null,
				UslugaComplex_Date: null,
				UslugaComplex_2011id: null,
				to: this.to
			});

	},
	setVizitCodeFilters: function(params) {
		// if ( false == sw.Promed.EvnVizitPL.isSupportVizitCode() ) {
		// 	return true;
		// }

		this.getStore().getProxy().setExtraParam('isVizitCode', 1);

		if (params.allowMorbusVizitOnly)
		{
			this.getStore().getProxy().setExtraParam('allowMorbusVizitOnly', 1);
			if (params.allowMorbusVizitCodesGroup88)
			{
				this.getStore().getProxy().setExtraParam('allowMorbusVizitCodesGroup88', 1);
			}
		}


		if (params.allowNonMorbusVizitOnly)
		{
			this.getStore().getProxy().setExtraParam('allowNonMorbusVizitOnly', 1);
		}

		switch ( getRegionNick() )
		{
			case 'perm':
				this.setUslugaCategoryList([ 'gost2011' ]);
				this.setAllowedUslugaComplexAttributeList([ 'vizit' ]);
				if (params.isStom)
				{
					this.setAllowedUslugaComplexAttributeList([ 'stom', 'vizit' ]);
					this.setAllowedUslugaComplexAttributeMethod('and');
					this.getStore().getProxy().setExtraParam('isStomVizitCode', 1);
				}
				break;

			case 'buryatiya':
				var addArray = [];

				if (params.isStom)
				{
					this.setAllowedUslugaComplexAttributeList([ 'vizit', 'stom' ].concat(addArray));
				} else if (params.isStac)
				{
					this.setAllowedUslugaComplexAttributeList([ 'stac_kd' ].concat(addArray));
				} else {
					this.setAllowedUslugaComplexAttributeList([ 'vizit' ].concat(addArray));
				}
				this.setAllowedUslugaComplexAttributeMethod('and');
				this.setUslugaCategoryList([ 'tfoms' ]);
				break;

			case 'kz':
				this.setUslugaCategoryList([ 'classmedus' ]);
				break;

			case 'pskov':
				if (params.isStom) {
					this.setAllowedUslugaComplexAttributeList([ 'vizit', 'stom' ]);
					this.setAllowedUslugaComplexAttributeMethod('and');
				} else if (params.isStac) {
					this.setAllowedUslugaComplexAttributeList([ 'stac_kd' ]);
				} else {
					this.setAllowedUslugaComplexAttributeList([ 'vizit', 'obr' ]);
				}
				this.setUslugaCategoryList([ 'pskov_foms' ]);
				break;

			case 'ufa':
				this.setUslugaCategoryList([ 'lpusection' ]);
				if (params.isStom) {
					this.setAllowedUslugaComplexAttributeList([ 'stom' ]);
					this.getStore().getProxy().setExtraParam('isStomVizitCode', 1);
					this.getStore().getProxy().setExtraParam('allowMorbusVizitCodesGroup88', 0);
					this.getStore().getProxy().setExtraParam('allowMorbusVizitOnly', 0);
					this.getStore().getProxy().setExtraParam('allowNonMorbusVizitOnly', 0);
				}
				break;

			case 'ekb':
				this.getStore().getProxy().setExtraParam('UslugaComplexPartition_CodeList', Ext6.util.JSON.encode([300,301]));
				this.getStore().getProxy().setExtraParam('filterByLpuSection', 1);
				break;
		}
		return true;
	},

	setAllowedUslugaComplexAttributeList: function(list) {
		if ( typeof list != 'object' ) {
			list = new Array();
		}

		this.getStore().getProxy().setExtraParam('allowedUslugaComplexAttributeList', Ext6.util.JSON.encode(list));
		this.lastQuery = '';

		return true;
	},
	// Метод учета допустимых типов атрибутов комплексной услуги
	// Допустимые значения:
	// and - должны иметься все перечисленные атрибуты
	// or - должен иметься хотя бы один из перечисленных атрибутов (по умолчанию)
	setAllowedUslugaComplexAttributeMethod: function(method) {
		if ( typeof method != 'string' || !method.inlist([ 'and', 'or' ]) ) {
			method = 'or';
		}

		this.getStore().getProxy().setExtraParam('allowedUslugaComplexAttributeMethod', method);

		return true;
	},
	setDisallowedUslugaComplexAttributeList: function(list) {
		if ( typeof list != 'object' ) {
			list = new Array();
		}

		this.getStore().getProxy().setExtraParam('disallowedUslugaComplexAttributeList', Ext6.util.JSON.encode(list));
		this.lastQuery = '';

		return true;
	},
	setLpuLevelCode: function(lpu_level_code) {
		this.getStore().getProxy().setExtraParam('LpuLevel_Code', lpu_level_code);
	},
	setMesOldVizit_id: function(MesOldVizit_id){
		this.getStore().getProxy().setExtraParam('MesOldVizit_id', MesOldVizit_id);
	},
	setMedSpecOms_id: function(MedSpecOms_id){
		this.getStore().getProxy().setExtraParam('MedSpecOms_id', MedSpecOms_id);
	},
	setFedMedSpec_id: function(FedMedSpec_id){
		this.getStore().getProxy().setExtraParam('FedMedSpec_id', FedMedSpec_id);
	},
	setLpuSectionProfile_id: function(LpuSectionProfile_id){
		this.getStore().getProxy().setExtraParam('LpuSectionProfile_id', LpuSectionProfile_id);
	},
	setLpuSectionProfileByLpuSection_id: function(LpuSection_id){
		this.getStore().getProxy().setExtraParam('LpuSectionProfileByLpuSection_id', LpuSection_id);
	},

	setPayType: function(PayType_id) {
		this.getStore().getProxy().setExtraParam('PayType_id', PayType_id);
	},
	setPersonId: function(Person_id) {
		this.getStore().getProxy().setExtraParam('Person_id', Person_id);
	},
	setUslugaComplexDate: function(date) {
		this.getStore().getProxy().setExtraParam('UslugaComplex_Date', date);
	},
	setUslugaCategoryList: function(list) {
		if ( typeof list != 'object' ) {
			list = new Array();
		}

		this.getStore().getProxy().setExtraParam('uslugaCategoryList', Ext6.util.JSON.encode(list));
		this.lastQuery = '';

		return true;
	},
	setUslugaComplexCodeList: function(list) {
		if ( typeof list != 'object' ) {
			list = new Array();
		}

		this.getStore().getProxy().setExtraParam('uslugaComplexCodeList', Ext6.util.JSON.encode(list));
		this.lastQuery = '';

		return true;
	},
	setUslugaComplex2011Id: function(id) {
		this.getStore().getProxy().setExtraParam('UslugaComplex_2011id', id);
	},
	/*
	 * @var int Тип назначения
	 */
	PrescriptionType_Code: null,
	setPrescriptionTypeCode: function(code) {
		this.PrescriptionType_Code = parseInt(code);
		switch(this.PrescriptionType_Code) {
			case 6: //Манипуляции и процедуры
				this.setAllowedUslugaComplexAttributeList([ 'manproc' ]);
				break;
			case 7: //Оперативное лечение
				this.setAllowedUslugaComplexAttributeList([ 'oper' ]);
				break;
			case 11: //Лабораторная диагностика
				this.setAllowedUslugaComplexAttributeList([ 'lab' ]);
				break;
			case 12: //Функциональная диагностика
				this.setAllowedUslugaComplexAttributeList([ 'func' ]);
				break;
			case 13: //Консультационная услуга
				this.setAllowedUslugaComplexAttributeList([ 'consult' ]);
				break;
			default:
				this.setAllowedUslugaComplexAttributeList();
				break;
		}
	},

	onBeforeShowSearchWindow: function(win, showParams)
	{
		this.disableBlurAction = true;
		if (!showParams) {
			showParams = {};
		}
		if (win && win.List && win.List.loadData) {
			win.List.loadData(null);
			showParams.params = {
				// query: this.getRawValue()
			};
		} else {
			showParams.query = this.getRawValue();
			showParams.store = this.getStore();
		}
		return showParams;
	},
	onHideSearchWindow: function()
	{
		this.disableBlurAction = false;
		this.focus(false);
	},
	onSelectSearchWindow: function(win, obj)
	{
		this.getStore().removeAll();
		this.getStore().loadData([obj], true);
		var index = this.getStore().find(this.valueField, obj[this.valueField]);
		if (index == -1) {
			this.getStore().removeAll();
			return false;
		}
		this.setValue(obj[this.valueField]);
		var record = this.getStore().getAt(index);
		if ( typeof record == 'object' ) {
			this.disableBlurAction = false;
			this.fireEvent('select', this, record, index);
			this.fireEvent('change', this, record.get(this.valueField));
		}
		win.hide();
	},
	getSearchWindow: function()
	{
		if (!this._searchWindow) {
			this._searchWindow = getWnd('swUslugaComplexSearchWindow');
		}
		return this._searchWindow;
	},
	getUslugaResultsTemplate: function () {

		var me = this,

			usluga_complex_name_tpl = '<div style="vertical-align: top; line-height: 20px; display: inline-block; overflow: hidden;width: 42%;' +
				'<tpl if="typeof UslugaComplexLevel_id !== \'undefined\' && UslugaComplexLevel_id == 9"> font-weight: bold;</tpl>' +
				'">{UslugaComplex_Name}&nbsp;</div>',

			usluga_complex_code_head_tpl = '<tpl if="this.enableShowCode()"><div style="display:inline-block;width: 20%;">Код</div></tpl>',

			usluga_complex_code_row_tpl = '<tpl if="this.enableShowCode()"><div style="vertical-align: top; line-height: 20px; display: inline-block; overflow: hidden;width: 20%;">{UslugaComplex_Code}&nbsp;</div></tpl>',

			functionsForTemplate = {
				enableShowCode: function() { return me.showCodeField; }
			};



		// Первый Template
		var temp1 = '<div style="width: 100%;font-family: Roboto; font-size: 10pt; font-weight: bold; text-align: center;">' +
			usluga_complex_code_head_tpl +
			'<div style="display:inline-block;width: 42%;">Наименование</div>' +
			'<div style="display:inline-block;width: 17%;">Категория</div>' +
			'<div style="display:inline-block;width: 21%;">Дата закрытия</div>' +
			'</div>' +
			'<tpl for="."><div class="x6-boundlist-item">' +
			usluga_complex_code_row_tpl +
			usluga_complex_name_tpl +
			'<div style="vertical-align: top; line-height: 20px; display: inline-block; overflow: hidden;width: 17%;">{UslugaCategory_Name}&nbsp;</div>' +
			'<div style="vertical-align: top; line-height: 20px; display: inline-block; overflow: hidden;width: 21%;">{[Ext6.util.Format.date(values.UslugaComplex_endDT, "d.m.Y")]}&nbsp;</div>' +
			'</div></tpl>',

			// Второй Template
			/*temp2 = '<div style="width: 100%;font-family: Roboto; font-size: 10pt; font-weight: bold; text-align: center;">' +
				usluga_complex_code_head_tpl +
				'<div style="display:inline-block;width: 42%;">Наименование</div>' +
				'<div style="display:inline-block;width: 17%;">Категория</div>' +
				'<div style="display:inline-block;width: 21%;">Отделение</div>' +
				'</div>' +
				'<tpl for="."><div class="x6-boundlist-item">' +
				usluga_complex_code_row_tpl +
				usluga_complex_name_tpl +
				'<div style="vertical-align: top; line-height: 20px; display: inline-block; overflow: hidden;width: 17%;">{UslugaCategory_Name}&nbsp;</div>' +
				'<div style="vertical-align: top; line-height: 20px; display: inline-block; overflow: hidden;width: 21%;">{LpuSection_Name}&nbsp;</div>' +
				'</div></tpl>',*/

			// Третий Template
			temp3 = '<div style="width: 100%;font-family: Roboto; font-size: 10pt; font-weight: bold; text-align: center;">' +
				usluga_complex_code_head_tpl +
				'<div style="display:inline-block;width: 57%;">Наименование</div>' +
				'<div style="display:inline-block;width: 23%;">Категория</div>' +
				'</div>' +
				'<tpl for="."><div class="x6-boundlist-item">' +
				usluga_complex_code_row_tpl +
				'<div style="vertical-align: top; line-height: 20px; display: inline-block; overflow: hidden;width: 57%;' +
				'<tpl if="typeof UslugaComplexLevel_id !== \'undefined\' && UslugaComplexLevel_id == 9"> font-weight: bold;</tpl>' +
				'">{UslugaComplex_Name}&nbsp;</div>' +
				'<div style="vertical-align: top; line-height: 20px; display: inline-block; overflow: hidden;width: 23%;">{UslugaCategory_Name}&nbsp;</div>' +
				'</div></tpl>',

			temp2 = '<tpl for="."><div class="x6-boundlist-item usluga-boundlist-item-combo" style="">' +
				//usluga_complex_code_row_tpl + usluga_complex_name_tpl +
				'<table style="width: 100%; padding: 8px 0px 8px 15px">' +
				'<tr>'+
				'<td style="width: 65%; padding-right: 15px;">'+
				'<div style="vertical-align: top; line-height:16px; color: black;"><p class="usluga-name">{UslugaComplex_Name}&nbsp;</p>' +
				/*'<p class="usluga-section-info">{UslugaCategory_Name}&nbsp;/ {LpuSection_Name}&nbsp;</p>'+*/
				'</div>'+
				'</td>'+
				'<td style="width: 25%; padding-right: 15px; vertical-align: top;">'+
				'<div style="vertical-align: top">'+
				//'<p style="color: #999; line-height: 16px">Код</p>'+
				'<p class="usluga-section-info" style="line-height: 16px">{UslugaComplex_Code}</p>'+
				'</div>'+
			   /* '<div style="vertical-align: top; line-height: 20px; display: inline-block; overflow: hidden;width: 21%;">{LpuSection_Name}&nbsp;</div>' +*/
				'</td>'+
				'<td style="width: 10%; vertical-align: top;">'+
				'<div>'+
				'<p class="usluga-section-info">{UslugaCategory_Name}</p>'+
				'</div>'+
				'</td>'+
				'</tr>'+
				'</table>' +
				'</div></tpl>',

			template = this.showUslugaComplexEndDate ? temp1 : ( this.showUslugaComplexLpuSection ? temp2 : temp3);


		return new Ext6.XTemplate(template, functionsForTemplate);
	},
	initComponent: function() {
		this.callParent(arguments);

		this.tpl = this.getUslugaResultsTemplate();

		this.getStore().getProxy().setExtraParams(
			{
				to: this.to,
				DispClass_id: this.DispClass_id,
				dispOnly: this.dispOnly ? 1 : null,
				nonDispOnly: (! this.dispOnly && this.nonDispOnly) ? 1 : null
			});

		if ( getGlobalOptions().region ) {
			switch ( getGlobalOptions().region.nick ) {
				case 'perm':
				case 'ufa':
					this.getStore().getProxy().setExtraParam('withoutLpuFilter', 2);
					break;
			}
		}

		return;
	},
	listeners: {
		blur: function ()
		{
			if (this.getValue() == this.getRawValue()) // если это так, значит в значении комбика простой текст, а не id
			{
				this.setValue(null);
			}
		}
	}
});

Ext6.define('UslugaComplexTariffModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.UslugaComplexTariffModel',
	idProperty: 'UslugaComplexTariff_id',
	fields: [
		{ name: 'UslugaComplexTariff_id' },
		{ name: 'UslugaComplexTariff_begDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'UslugaComplexTariff_endDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'UslugaComplexTariff_Code' },
		{ name: 'UslugaComplexTariff_Name' },
		{ name: 'UslugaComplexTariff_Tariff' },
		{ name: 'UslugaComplexTariff_UED' },
		{ name: 'UslugaComplexTariff_UEM' },
		{ name: 'UslugaComplexTariffType_Name' },
		{ name: 'LpuUnitType_Name' },
		{ name: 'Lpu_id' }
	]
});

Ext6.define('UslugaComplexTariffStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.UslugaComplexTariffStore',
	model: 'UslugaComplexTariffModel',
	proxy: {
		type: 'ajax',
		url: '/?c=Usluga&m=loadUslugaComplexTariffList',
		reader: {
			type: 'json'
		}
	}
});

Ext6.define('UslugaComplexTariffCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.UslugaComplexTariffCombo',

	allowLoadMask: false,
	store: {type: 'UslugaComplexTariffStore'},

	codeField: 'UslugaComplexTariff_Code',
	displayField: 'UslugaComplexTariff_Name',
	editable: true,
	enableKeyEvents: true,
	fieldLabel: langs('Тариф'),
	cls: 'trigger-outside',
	forceSelection: true,
	isStom: false,
	name: 'UslugaComplexTariff_id',
	isAllowSetFirstValue: true,
	isLpuFilter: false,
	queryMode: 'local',
	selectOnFocus: true,
	listConfig:{
		userCls: 'usluga-complex-tariff-menu',
	},
	triggerAction: 'all',
	valueField: 'UslugaComplexTariff_id',
	params: {
		LpuSection_id: null
		,PayType_id: null
		,Person_id: null
		,UslugaComplex_id: null
		,UEDAboveZero: null
		,UslugaComplexTariff_Date: null
	},

	triggers: {
		search: {

			cls: 'x6-form-search-trigger',
			extraCls: 'search-icon-out',
			handler: function ()
			{
				if ( this.disabled ) {
					return false;
				}
				else if ( Ext6.isEmpty(this.params.PayType_id) ) {
					sw.swMsg.alert(langs('Ошибка'), langs('Не указан вид оплаты'));
					return false;
				}
				else if ( Ext6.isEmpty(this.params.Person_id) ) {
					sw.swMsg.alert(langs('Ошибка'), langs('Не указан идентификатор пациента'));
					return false;
				}
				else if ( Ext6.isEmpty(this.params.UslugaComplex_id) ) {
					sw.swMsg.alert(langs('Ошибка'), langs('Не указана услуга'));
					return false;
				}
				else if ( Ext6.isEmpty(this.params.UslugaComplexTariff_Date) ) {
					sw.swMsg.alert(langs('Ошибка'), langs('Не указана дата выполнения услуги'));
					return false;
				}

				var combo = this;

				getWnd('swUslugaComplexTariffViewWindow').show({
					callback: function(data) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == data[combo.valueField]);
						});

						if ( index == -1 ) {
							combo.getStore().loadData([{
								UslugaComplexTariff_id: data.UslugaComplexTariff_id
								,UslugaComplexTariff_begDate: data.UslugaComplexTariff_begDate
								,UslugaComplexTariff_endDate: data.UslugaComplexTariff_endDate
								,UslugaComplexTariff_Code: data.UslugaComplexTariff_Code
								,UslugaComplexTariff_Name: data.UslugaComplexTariff_Name
								,UslugaComplexTariff_Tariff: data.UslugaComplexTariff_Tariff
								,UslugaComplexTariff_UED: data.UslugaComplexTariff_UED
								,UslugaComplexTariff_UEM: data.UslugaComplexTariff_UEM
								,UslugaComplexTariffType_Name: data.UslugaComplexTariffType_Name
							}], true);

							index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == data[combo.valueField]);
							});
						}

						combo.setValue(data[combo.valueField]);
						combo.fireEvent('select', combo, combo.getStore().getAt(index));

						getWnd('swUslugaComplexTariffViewWindow').hide();
					},
					formParams: combo.params,
					onHide: function() {
						combo.focus(false);
					}
				});
			}
		}
	},
	// setValue: function(v) 	// переопределяем функцию, для того чтобы при загрзке формы услуга в комбике отображалась с именем
	// {						// потому что при инициализации комбик без записей, а услуга не устанавливается
	// 	var combo = this,
	// 		args = arguments;
	//
	// 	if (v && this.getRawValue() != v && ! isNaN(v) && this.getStore().find('UslugaComplexTariff_id', v) == -1)
	// 	{
	// 		this.getStore().load(
	// 			{
	// 				params:
	// 					{
	// 						UslugaComplexTariff_id: v
	// 					},
	// 				callback: function (store, records, successful)
	// 				{
	// 					var rec = this.findRecord('UslugaComplexTariff_id', v);
	//
	// 					if (rec)
	// 					{
	// 						combo.setValue(rec);
	// 					} else
	// 					{
	// 						combo.setValue(null);
	// 					}
	// 				}
	// 			});
	// 	}
	//
	// 	return this.callParent(args); // вызываем оригинальный setValue
	// },

	clearParams: function() {
		this.params = {
			LpuSection_id: null
			,PayType_id: null
			,Person_id: null
			,UEDAboveZero: null
			,UslugaComplex_id: null
			,UslugaComplexTariff_Date: null
		};
	},
	loadUslugaComplexTariffList: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var UslugaComplexTariff_id = this.getValue();

		this.clearValue();
		this.fireEvent('change', this, null);

		this.getStore().removeAll();

		console.log({params: this.params});

		if ( Ext6.isEmpty(this.params.PayType_id) || Ext6.isEmpty(this.params.Person_id)
			|| Ext6.isEmpty(this.params.UslugaComplex_id) || Ext6.isEmpty(this.params.UslugaComplexTariff_Date)
		) {
			this.fireEvent('change', this, null);
			return false;
		}


		this.getStore().load({
			callback: function(records) {

				this.getStore().clearFilter();
				this.lastQuery = '';
				if (this.getStore().getCount() > 1 && this.isLpuFilter) {
					this.getStore().filterBy(function(rec) {
						return Ext6.isEmpty(rec.get('Lpu_id'));
					}, this);
				}
				if ( this.getStore().getCount() > 0 ) {
					var index;
					var record;

					if (this.isStom) {
						this.getStore().filterBy(function(rec) {
							return (!Ext6.isEmpty(rec.get('UslugaComplexTariff_UED')) && rec.get('UslugaComplexTariff_UED') != 0) || (!Ext6.isEmpty(rec.get('UslugaComplexTariff_UEM')) && rec.get('UslugaComplexTariff_UEM') != 0);
						}, this);
					} else {
						this.getStore().filterBy(function(rec) {
							return !Ext6.isEmpty(rec.get('UslugaComplexTariff_Tariff')) && rec.get('UslugaComplexTariff_Tariff') != 0;
						}, this);
					}

					if ( this.isAllowSetFirstValue
						&& this.getStore().getCount() == 1
					) {
						index = 0;
					} else if ( this.isAllowSetFirstValue
						&& this.getStore().getCount() == 2
						&& Ext6.isEmpty(this.getStore().getAt(0).get(this.valueField))
					) {
						index = 1;
					}
					else {
						index = this.getStore().findBy(function(rec) {
							return (rec.get(this.valueField) == UslugaComplexTariff_id);
						}.createDelegate(this));
					}

					record = this.getStore().getAt(index);

					if ( record && !Ext6.isEmpty(record.get(this.valueField)) ) {
						this.setValue(record.get(this.valueField));
						this.fireEvent('change', this, record.get(this.valueField));
					}

					if (options.callback && typeof options.callback == 'function') {
						options.callback();
					}

					this.collapse();
				}
			}.createDelegate(this),
			params: this.params
		});
	},

	setParams: function(params) {
		this.clearParams();

		if ( !Ext6.isEmpty(params.LpuSection_id) ) {
			this.params.LpuSection_id = params.LpuSection_id;
		}

		if ( !Ext6.isEmpty(params.PayType_id) ) {
			this.params.PayType_id = params.PayType_id;
		}

		if ( !Ext6.isEmpty(params.Person_id) ) {
			this.params.Person_id = params.Person_id;
		}

		if ( !Ext6.isEmpty(params.UEDAboveZero) ) {
			this.params.UEDAboveZero = params.UEDAboveZero;
		}

		if ( !Ext6.isEmpty(params.UslugaComplex_id) ) {
			this.params.UslugaComplex_id = params.UslugaComplex_id;
		}

		if ( !Ext6.isEmpty(params.UslugaComplexTariff_Date) ) {
			this.params.UslugaComplexTariff_Date = (typeof params.UslugaComplexTariff_Date == 'object' ? Ext6.util.Format.date(params.UslugaComplexTariff_Date, 'd.m.Y') : params.UslugaComplexTariff_Date);
		}
	},


	getUslugaComplexTariff: function ()
	{
		var temp_stom ='<tpl for="."> <div class="x6-boundlist-item usluga-complex-tariff-item" style="padding: 10px 30px 8px 15px;"> <table style="width: 100%">'
			+'<tr>'
			+'<td style="width: 73%; vertical-align: top;">'
			+'<div style="padding-right: 20px">'
			+'<p class="usluga-complex-tariff-name">{UslugaComplexTariff_Name}&nbsp;</p>'
			+'<p class="usluga-complex-tariff-info">{UslugaComplexTariffType_Name}&nbsp;/ {UslugaComplexTariff_UED}&nbsp;</p>'
			+'</div>'
			+'</td>'
			+'<td style="width: 27%; vertical-align: top;">'
			+'<div>'
			+'<p class="usluga-complex-tariff-tab-number">Код</p>'
			+'<p class="usluga-complex-tariff-info">{UslugaComplexTariff_Code}</p>'
			+'</div>'
			+'</td>'
			+'</tr>'
			+'</table></div></tpl>';

		var temp ='<tpl for="."> <div class="x6-boundlist-item usluga-complex-tariff-item" style="padding: 10px 30px 8px 15px;"> <table style="width: 100%">'
			+'<tr>'
			+'<td style="width: 73%; vertical-align: top;">'
			+'<div style="padding-right: 20px">'
			+'<p class="usluga-complex-tariff-name">{UslugaComplexTariff_Name}&nbsp;</p>'
			+'<p class="usluga-complex-tariff-info">{UslugaComplexTariffType_Name}&nbsp;/ {UslugaComplexTariff_Tariff}&nbsp;/ {LpuUnitType_Name}&nbsp;</p>'
			+'</div>'
			+'</td>'
			+'<td style="width: 27%; vertical-align: top;">'
			+'<div>'
			+'<p class="usluga-complex-tariff-tab-number">Код</p>'
			+'<p class="usluga-complex-tariff-info">{UslugaComplexTariff_Code}</p>'
			+'</div>'
			+'</td>'
			+'</tr>'
			+'</table></div></tpl>';

		return new Ext6.XTemplate(this.isStom ? temp_stom : temp);
	},

	initComponent: function() {

		this.callParent(arguments);

		this.tpl = this.getUslugaComplexTariff();

		if ( this.isStom == true ) {
			this.codeField = 'UslugaComplexTariff_UED';
			// для отображения нужного кода в поле
		}
	}
});

Ext6.define('UslugaEventModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.UslugaEventModel',
	idProperty: 'Evn_id',
	fields: [
		{name: 'Diag_id'},
		{name: 'Evn_Name'},
		{name: 'Evn_id'},
		{name: 'Evn_setDate', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'Evn_setTime'},
		{name: 'LpuSectionProfile_id'},
		{name: 'LpuSection_id'},
		{name: 'MedPersonal_id'},
		{name: 'MedStaffFact_id'},
		{name: 'ServiceType_SysNick'},
		{name: 'UslugaComplex_Code'},
		{name: 'VizitType_SysNick'},
		{name: 'IsPriem'}
	]
});


Ext6.define('UslugaEventStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.UslugaEventStore',
	model: 'UslugaEventModel',
	mode: 'local',
	proxy: {
		type: 'memory',
		reader: {
			type: 'json'
		}
	}
});

Ext6.define('EventCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.EventCombo',
	queryMode: 'local',
	displayField: 'Evn_Name',
	valueField: 'Evn_id',
	store: {type: 'UslugaEventStore'}
});


Ext6.define('LpuSectionProfileModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.LpuSectionProfileModel',
	idProperty: 'LpuSectionProfile_id',
	fields: [
		{name: 'LpuSectionProfile_id'},
		{name: 'LpuSectionProfile_fedid'},
		{name: 'LpuSectionProfile_Code'},
		{name: 'LpuSectionProfile_Name'},
		{name: 'LpuSectionProfile_FullName'}
	]
});

Ext6.define('LpuSectionProfileStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.LpuSectionProfileStore',
	model: 'LpuSectionProfileModel',
	autoLoad: false,

	sorters: {
		property: 'LpuSectionProfile_Code',
		direction: 'ASC'
	},
	proxy: {
		type: 'ajax',
		url: '/?c=EvnUsluga&m=loadLpuSectionProfileList',
		reader: {
			type: 'json'
		}
	}
});

Ext6.define('LpuSectionProfileWithFedCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.LpuSectionProfileWithFedCombo',
	store: {type: 'LpuSectionProfileStore'},
	valueField: 'LpuSectionProfile_id',
	displayField: 'LpuSectionProfile_FullName',
	queryMode: 'local',
	listConfig:{
		userCls: 'lpu-section-profile-menu',
	},
	fieldLabel: langs('Профиль'),
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item lpu-section-profile-item">',
		'<table><tr><td><p class=" lpu-section-profile-info">{LpuSectionProfile_Code}&nbsp; {LpuSectionProfile_Name}&nbsp;</p></td></tr></table>',
		'</div></tpl>'
	)

});

Ext6.define('swNephroRateTypeCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.swNephroRateTypeCombo',
	editable: false,
	isDinamic: 1,
	codeField: 'RateType_id',
	displayField:'RateType_Name',
	valueField: 'RateType_id',
	fieldLabel: langs('Показатель'),
	//~ tpl: '<tpl for="."><div class="x-combo-list-item">'+
		//~ '{RateType_Name}'+
		//~ '</div></tpl>',
	initComponent: function()
	{
		var me=this;
		var baseParams = {isDinamic: me.isDinamic};

		me.store = Ext6.create('Ext6.data.Store', {
				fields: [
					{name:'RateType_id', type:'int'},
					{name:'RateType_Name', type:'string'},
					{name:'RateType_SysNick', type:'string'}
				],
				autoLoad: false,
				sorters: {
					property: 'RateType_Name',
					direction: 'ASC'
				},
				proxy: {
					type: 'ajax',
					url: '/?c=MorbusNephro&m=doLoadRateTypeList',
					reader: {
						type: 'json'
					},
					extraParams: baseParams
				},
				baseParams: baseParams,
				tableName: 'RateType',
				mode: 'remote'
			});

		me.callParent(arguments);
	}
});

Ext6.define('swMedPersonalCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.swMedPersonalCombo',
	valueField: 'MedPersonal_id',
	displayField: 'MedPersonal_FIO',
	codeField: 'MedPersonal_Code',
	enableKeyEvents: true,
	fieldLabel: langs('Врач'),
	initComponent: function()
	{
		this.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'MedPersonal_id', mapping: 'MedPersonal_id', type:'int'},
				{name: 'MedPersonal_Code', mapping: 'MedPersonal_Code',type:'string'},
				{name: 'MedPersonal_FIO', mapping: 'MedPersonal_FIO', type:'string'},
				{name: 'WorkData_begDate', mapping: 'WorkData_endDate'},
				{name: 'WorkData_endDate', mapping: 'WorkData_endDate'}
			],
			autoLoad:false,
			sorters: {
				property: 'MedPersonal_FIO',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				url: C_MP_COMBO,
				reader: {
					type: 'json'
				},
			},
			mode: 'local'
		});

		this.callParent(arguments);
	}
});


/**
 * Блок model-store-combo для диагноза, а также отдельно вынесен поисковый модуль
 */

Ext6.define('swTagDiagModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.swTagDiagModel',
	idProperty: 'Diag_id',
	fields: [
		{name: 'Diag_id'},
		{name: 'Diag_pid', type: 'int'},
		{name: 'Diag_Name', type: 'string'},
		{name: 'Diag_Code', type: 'string'},
		{name: 'Diag_Display', calculate: function (data) {return data.Diag_Code + ' ' + data.Diag_Name;}},
		{name: 'DiagLevel_id', type: 'int'},
		{name: 'Diag_begDate', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'Diag_endDate', type: 'date', dateFormat: 'd.m.Y'},
		{name: 'PersonAgeGroup_Code', type: 'string'},
		{name: 'Sex_Code', type: 'int'},
		{name: 'DiagFinance_IsOms', type: 'boolean'},
		{name: 'DiagFinance_IsAlien', type: 'boolean'},
		{name: 'DiagFinance_IsHealthCenter', type: 'boolean'},
		{name: 'DiagFinance_IsRankin', type: 'boolean'},
		{name: 'PersonRegisterType_List'},
		{name: 'MorbusType_List'},
		{name: 'DeathDiag_IsLowChance', type: 'boolean'},
		{name: 'PersonAgeGroup_Code', type: 'string'},
		{name: 'IsFavourite', type: 'boolean'}
	]
});

Ext6.define('swTagDiagStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.swTagDiagStore',
	model: 'swTagDiagModel',
	autoLoad: false,
	loadParams : {
		object: 'Diag',
		where: 'where DiagLevel_id = 4'
	},
	sorters: {
		property: 'Diag_Code',
		direction: 'ASC'
	},
	proxy: {
		type: 'ajax',
		url : '/?c=MongoDBWork&m=getData',
		extraParams: {
			object: 'Diag', // для ручной загрузки
			where: 'where DiagLevel_id = 4',
			Diag_id: '',
			Diag_pid: '',
			Diag_Name: '',
			Diag_Code: '',
			DiagLevel_id: '',
			Diag_Display: '',
			Diag_begDate: '',
			Diag_endDate: '',
			PersonAgeGroup_Code: '',
			Sex_Code: '',
			DiagFinance_IsOms: '',
			DiagFinance_IsAlien: '',
			DiagFinance_IsHealthCenter: '',
			DiagFinance_IsRankin: '',
			PersonRegisterType_List: '',
			MorbusType_List: '',
			DeathDiag_IsLowChance: '',
			IsFavourite: ''
		},
		reader: {
			type: 'json'
		}
	},
	mode: 'remote'
});

Ext6.define('TagDiagSearchModule', {
	MKB:null,
	Diag_level3_code:null,
	withGroups: false,
	PersonRegisterType_SysNick: '',
	MorbusType_SysNick: '', //Тип заболевания/нозологии
	HighlightSearhResults: true,
	onLoadStore: Ext6.emptyFn,
	onEmptyResults: Ext6.emptyFn,
	listConfig: { // конфиг для выпадающего списка с подкрашиванием результатов поиска
		loadingText: 'Загружаем диагнозы',
		cls: 'choose-bound-list-menu update-scroller',
		emptyText: '<div class="no-results-frame"><span>Диагнозов не найдено</span></div>',
		getInnerTpl: function() { // подкрашиваем результаты поиска

			return "{[values.Diag_Display.replace(new RegExp('(' + this.field.getRawValue().trim() + ')', 'ig'), '<span style=\"color:red;font-weight:900\">$1</span>')]}";
		}
	},
	getNameAndDiagFilters: function (q)
	{
		if (Ext6.isEmpty(q))
		{
			return '';
		}

		var reg = new RegExp('^([a-z]|[a-z][0-9.]{1,4})$', 'i'),
			filter;

		if (q.search(reg) !== -1)
		{
			filter = ' and (Diag_Code like \'' + q + '%\') ';
		} else
		{
			filter = ' and (Diag_Code like \'' + q + '%\' OR Diag_Name like \'%' + q + '%\') ';
		}

		return filter;
	},
	accessRightsFilter: function(record) {
		var code_list = getGlobalOptions().denied_diags.code_list,
			code_range_list = getGlobalOptions().denied_diags.code_range_list;

		var result = true;
		if (code_list.length > 0 && record.get('Diag_Code').inlist(code_list)) {
			return false;
		}
		code_range_list.forEach(function(range){
			if (record.get('Diag_Code') >= range[0] && record.get('Diag_Code') <= range[1]) {
				result = false; return false;
			}
		});
		return result;
	},
	getRegistryTypeFilter: function (registryType)
	{
		var filter = '';

		switch (registryType)
		{
			case 'Fmba':
				filter = " and Diag_Code like 'Z57%'";
				break;
			case 'palliat':
				filter = " and Diag_Code not like 'Z%'";
				break;
			case 'NarkoRegistry':
				filter = " and Diag_Code like 'F1%'";
				break;
			case 'CrazyRegistry':
				filter = " and (Diag_Code like 'F0%' or Diag_Code like 'F2%' or Diag_Code like 'F3%' or Diag_Code like 'F4%'" +
					" or Diag_Code like 'F5%' or Diag_Code like 'F6%' or Diag_Code like 'F7%' or Diag_Code like 'F8%' or Diag_Code like 'F9%')";
				break;
			case 'TubRegistry':
				filter = " and (Diag_Code like 'A15%' or Diag_Code like 'A16%' or Diag_Code like 'A17%' or Diag_Code like 'A18%' or Diag_Code like 'A19%'";
				break;
			case 'PersonPregnansy': //gaf 109848 27032018 добавлен фильтр по ufa
				filter = getRegionNick() == 'ufa' ? " and (Diag_Code like 'Z32%' or Diag_Code like 'Z33%' or Diag_Code like 'Z34%' or Diag_Code like 'Z35%' or Diag_Code like 'Z36%' or Diag_Code like 'O%') " : '';
				break;
			case 'ExternalCause':
				if(q.search(new RegExp("^[VWXY]", "i")) < 0){
					filter = " and Diag_id < 0";
				}
				break;
		}

		return filter;
	},
	getConcreteDiagFilter: function (DiagLevelFilter_id, DiagFilter_id) // фильтр диагнозов по конкретному diag_id на нокретном уровне
	{
		var join = "";
		var filters = "";
		switch (DiagLevelFilter_id)
		{
			case '1':
				join += "LEFT JOIN Diag dg1 ON dg.Diag_id=dg1.Diag_id ";
				join += "LEFT JOIN Diag dg2 ON dg1.Diag_pid=dg2.Diag_id ";
				join += "LEFT JOIN Diag dg3 ON dg2.Diag_pid=dg3.Diag_id ";
				join += "LEFT JOIN Diag dg4 ON dg3.Diag_pid=dg4.Diag_id ";
				filters += "dg4.Diag_id = " + DiagFilter_id;
				break;
			case '2':
				join += "LEFT JOIN Diag dg1 ON dg.Diag_id=dg1.Diag_id ";
				join += "LEFT JOIN Diag dg2 ON dg1.Diag_pid=dg2.Diag_id ";
				join += "LEFT JOIN Diag dg3 ON dg2.Diag_pid=dg3.Diag_id ";
				filters += "dg3.Diag_id = " + DiagFilter_id;
				break;
			case '3':
				join += "LEFT JOIN Diag dg1 ON dg.Diag_id=dg1.Diag_id ";
				join += "LEFT JOIN Diag dg2 ON dg1.Diag_pid=dg2.Diag_id ";
				filters += "dg2.Diag_id = " + DiagFilter_id;
				break;
			case '4':
				join += "LEFT JOIN Diag dg1 ON dg.Diag_id=dg1.Diag_id ";
				filters += "dg1.Diag_id = " + DiagFilter_id;
				break;
		}
		filters += this.getDateFilters();

		return ' and Diag_id in (select dg.Diag_id from Diag dg ' + join + ' where ' + filters + ') ';
	},
	getMKBFilters: function ()
	{
		var query = '';
		if(this.MKB.isMain){
			if(getRegionNick() =='kareliya'){
				query = " and (isMain != 1)";
			}else if (getRegionNick() != 'perm'){

				query = " and Diag_Code not like 'X%' and Diag_Code not like 'Y%' and Diag_Code not like 'W%' and Diag_Code not like 'V%'";
			}
		}
		if(this.MKB.query){

			query += " and ((Mkb10Cause_id is null) or (Mkb10Cause_id not in "+this.MKB.query+"))"
		}

		return query;
	},
	getDateFilters: function ()
	{
		var filterDate = this.getDate();
		return ' and (Diag_begDate is null or Diag_begDate <= \'' + filterDate + '\') and (Diag_endDate is null or Diag_endDate >= \'' + filterDate + '\') ';
	},
	getDate: function ()
	{
		return Ext6.util.Format.date(this.filterDate || new Date(), 'Y-m-d');
	},

	exportSearchModule: function () // конфиг того, что нужно обязательно добавить к комбику диагноза
	{
		return {
			doQuery: this.doQuery,
			accessRightsFilter: this.accessRightsFilter,
			getRegistryTypeFilter: this.getRegistryTypeFilter,
			getDateFilters: this.getDateFilters,
			getDate: this.getDate,
			getConcreteDiagFilter: this.getConcreteDiagFilter,
			getMKBFilters: this.getMKBFilters,
			getNameAndDiagFilters: this.getNameAndDiagFilters
		};
	},
	exportDefaultSearchParams: function () // конфиг дефолтных параметров, которые станут свойствами комбика, только если уже не определены в нем заранее
	{
		return {
			withGroups: this.withGroups,
			MKB: this.MKB,
			Diag_level3_code:this.Diag_level3_code,
			PersonRegisterType_SysNick: this.PersonRegisterType_SysNick,
			MorbusType_SysNick: this.MorbusType_SysNick,
			onLoadStore: this.onLoadStore,
			onEmptyResults: this.onEmptyResults,
			listConfig: this.listConfig
		};
	},

	// главная функция поиска. Она перезаписывает базовую функцию поиска комбика, запускается автоматически
	doQuery: function(q) {
		var cur = this;
		if (q === undefined || q === null)
		{
			q = '';
		}

		q = q.trim();

		var addGroupFilter = "",
			nameAndDiagFilters = this.getNameAndDiagFilters(q);
		if (this.withGroups) addGroupFilter = " or DiagLevel_id = 3";

		//gaf 109848 для Регистра беременных, Основной диагноз добавлено && 'PersonPregnansy' != this.registryType
		if(this.Diag_level3_code && 'PersonPregnansy' != this.registryType) q = this.Diag_level3_code;//для уточненного диагноза


		if (true)
		{
			if (this.lastQuery != q )
			{
				this.lastQuery = q;
				this.selectedIndex = -1;

				var additQueryFilter = '';
				if ( this.additQueryFilter && this.additQueryFilter != '' ) additQueryFilter = " and " + this.additQueryFilter;

				if ( this.PersonRegisterType_SysNick && this.PersonRegisterType_SysNick.length > 0 && !this.PersonRegisterType_SysNick.inlist(['crazyRegistry','narkoRegistry'])) additQueryFilter += " and PersonRegisterType_List like '%" + this.PersonRegisterType_SysNick + "%'";
				if ( this.MorbusType_SysNick && this.MorbusType_SysNick.length > 0 && this.MorbusType_SysNick!='vzn' ) additQueryFilter += " and MorbusType_List like '%" + this.MorbusType_SysNick + "%'";

				if (this.registryType) additQueryFilter += this.getRegistryTypeFilter(this.registryType);

				additQueryFilter += this.getDateFilters();

				var where = 'where (DiagLevel_id = 4 ' + addGroupFilter + ') ' + nameAndDiagFilters + ' ' + additQueryFilter + ' ';

				//ограничиваем по умолчанию диагнозами из параметра idDefaultListFilter = строка-список Diag_id через запятую
				if(this.idDefaultListFilter && !nameAndDiagFilters) where+=' and Diag_id in ('+this.idDefaultListFilter+')';

				if(this.MKB!=null) where += this.getMKBFilters();

				// ограничиваем только заданными диагнозами
				if ( this.DiagFilter_id && this.DiagLevelFilter_id && this.DiagFilter_id > 0 && this.DiagLevelFilter_id > 0 ) where += this.getConcreteDiagFilter(this.DiagLevelFilter_id, this.DiagFilter_id);


				where += ' limit 50'; // для скорости

				var store = this.getStore();

				console.log(this)

				store.load({

					params: {where: where},
					callback: function() {
						this.onLoadStore();

						var filterDenied = this.checkAccessRights ? this.accessRightsFilter : (function () {return true;});


						if (typeof this.baseFilterFn == 'function')
						{
							// Apply the filter on top of the base filter
							this.getStore().filterBy(function(record, id) {
								var result = false;
								result = this.baseFilterFn.call(this.baseFilterScope, record, id);

								return result && filterDenied(record);
							}, this);
						} else if (this.checkAccessRights) {
							this.getStore().filterBy(filterDenied);
						}


						if (this.getStore().getCount() > 0)
						{
							this.expand();
						}
						else {
							this.onEmptyResults();
						}

					}.createDelegate(this)
				});

			}
			else
			{
				this.selectedIndex = -1;
				if(q=='' && !this.isExpanded) this.expand();//иначе комбик не раскрывается на второй раз
			}
		}
		else
		{
			this.getStore().removeAll();
		}
		this.afterQuery = true;
	}
});


Ext6.define('swBaseTagCombobox', {
	extend: 'Ext6.form.field.Tag',
	alias: 'widget.swBaseTagCombobox',
	liquidLayout: false,
	matchFieldWidth: true,
	minMatchFieldWidth: true,
	submitIfDisabled: true,
	setFieldValue: function(fieldName, fieldValue) {
		var table = '';
		if (this.store && this.tableName) {
			table = this.tableName;
		}
		else {
			table = fieldName.substr(0, fieldName.indexOf('_'));
		}
		if (table.length > 0) {
			var idx = this.getStore().findBy(function (rec) {
				if (rec.get(fieldName) == fieldValue) {
					return true;
				}
				else {
					return false;
				}
			});
			var record = this.getStore().getAt(idx);
			if (record) {
				this.setValue(record.get(table + '_id'));
			}
			else {
				this.clearValue();
			}
		}
		else {
			if (IS_DEBUG) {
				console.warn('Наименование объекта (%o) не определено!', this);
				console.warn('Поле: %s', fieldName);
				console.warn('Значение: %s', fieldValue);
			}
		}
	},
	getFieldValue: function (fieldName) {
		if (!Ext6.isEmpty(this.getValue()) && this.getStore().getCount() > 0) {
			var record = this.getSelectedRecord();
			if (record) {
				return record.get(fieldName);
			}
			else {
				if (IS_DEBUG) {
					console.warn('Невозможно выбрать запись из Store комбобокса (%o) или поле %s отсутствует в Store!', this, fieldName);
					console.warn(record);
				}
			}
		}
		else {
			return null;
		}
	},
	onLoad: function(store, records, success) {
		var me = this;

		// добавляем пустую строку
		if (typeof me.insertAdditionalRecords == 'function') {
			me.insertAdditionalRecords();
		}

		me.callParent();
	},
	/**
	 * Получение первой не пустой записи из стора комбобокса
	 */
	getFirstRecord: function() {
		var me = this;
		if (me.store.getCount() > 0 && me.store.getAt(0).data[this.valueField] != "") {
			return me.store.getAt(0);
		} else if (me.store.getCount() > 1) {
			return me.store.getAt(1);
		} else {
			return null;
		}
	},
	insertAdditionalRecords: function() {
		this.insertEmptyRecord();
		this.insertAdditionalRecord();
	},
	insertAdditionalRecord: function() {
		if (this.additionalRecord && !this.store.getById(this.additionalRecord.value)) {
			var data = {};

			if (this.codeField && this.additionalRecord.code != undefined) {
				data[this.codeField] = this.additionalRecord.code;
			}
			data[this.valueField] = this.additionalRecord.value;
			data[this.displayField] = this.additionalRecord.text;
			data['additionalSortCode'] = -1;

			this.store.insert(0, data);
		}
	},
	insertEmptyRecord: function() {
		if (this.store.getCount() > 0 && this.store.getAt(0).data[this.valueField] != "" && this.allowBlank == true && this.hideEmptyRow != true) {
			var data = {};

			if (this.codeField) {
				data[this.codeField] = "";
			}
			data[this.valueField] = "";
			data[this.displayField] = "";
			data['additionalSortCode'] = -2;

			this.store.insert(0, data);
		}
	},
	listConfig: {
		getInnerTpl: function(displayField) {
			return '{' + displayField + '}\u00a0'; // чтобы пустая строка была корректной высоты
		}
	},
	minMatchFieldWidthFn: function() {
		var me = this;

		if (!me.matchFieldWidth && me.minMatchFieldWidth && Ext6.isEmpty(me.listConfig.minWidth)) {
			me.getPicker().setMinWidth(me.bodyEl.getWidth(true));
		}
	},
	initComponent: function() {
		var me = this;

		me.callParent(arguments);

		me.on({
			boxready: me.minMatchFieldWidthFn,
			resize: me.minMatchFieldWidthFn,
			scope: me
		});
	},
	// чуть изменяем стандартный метод, чтобы возвращать значения отключенных полей в form.getValues()
	// https://docs.sencha.com/extjs/6.5.3/classic/src/Field.js.html#Ext.form.field.Field-method-getSubmitData
	getSubmitData: function() {
		var me = this,
			data = null;
		if ( (me.submitIfDisabled || ! me.disabled) && me.submitValue) {
			data = {};
			data[me.getName()] = '' + me.getValue();
		}
		return data;
	},
	listeners: {
		change: function (c, v)
		{
			// для корректной работы с viewModel
			try {
				// понятия не имею, почему s.petrov по #131172 добавил это сюда, но это взрывается
				if (this.up && typeof this.up === 'function' && this.up('form') && this.up('form').getViewModel())
				{
					this.up('form').getViewModel().set(this.name, v);
				}
				//typeof this.up === 'function' ? (this.up('form') ? this.up('form').isValid() : null) : null;
			} catch (e) {
				log(e);
			}


			return;
		}
	}
});

// Комбобокс тип узла дерева
Ext6.define('ambulanceDecigionTreeType',{
	extend: 'Ext.form.ComboBox',
	alias: 'widget.ambulanceDecigionTreeType',
	queryMode: 'local',
	valueField: 'AmbulanceDecigionTree_Type',
	displayField: 'AmbulanceDecigionTree_TypeName',
	typeAhead: true,
	forceSelection: true,
	store: Ext6.create('Ext.data.Store', {
		fields:
			[
				{name: 'AmbulanceDecigionTree_Type', type: 'int'},
				{name: 'AmbulanceDecigionTree_TypeName', type: 'string'}
			],
		data: [
			{
				'AmbulanceDecigionTree_Type': 1,
				'AmbulanceDecigionTree_TypeName': 'Вопрос'

			},
			{
				'AmbulanceDecigionTree_Type': 2,
				'AmbulanceDecigionTree_TypeName': 'Ответ'
			}
		]
	})
});


// Комбобокс уровня дерева
Ext6.define('ambulanceTreeLevel',{
	extend: 'Ext.form.ComboBox',
	alias: 'widget.ambulanceTreeLevel',
	queryMode: 'local',
	valueField: 'ambulanceTreeLevel_value',
	displayField: 'ambulanceTreeLevel_name',
	typeAhead: true,
	forceSelection: true,
	store: Ext6.create('Ext.data.Store', {
		data: [
			{
				'ambulanceTreeLevel_name': 'Регион',
				'ambulanceTreeLevel_value': 'Region'

			},{
				'ambulanceTreeLevel_name': 'МО',
				'ambulanceTreeLevel_value': 'Lpu'

			},{
				'ambulanceTreeLevel_name': 'Подразделение',
				'ambulanceTreeLevel_value': 'LpuBuilding'

			},
			{
				'ambulanceTreeLevel_name': 'Базовое дерево',
				'ambulanceTreeLevel_value': 'baseTree'
			}
		]
	})
});

// Получение значения в зависимости от выбранного уровня.
Ext6.define('getStructuresLevel',{
	extend: 'Ext.form.ComboBox',
	alias: 'widget.getStructuresLevel',
	queryMode: 'local',
	valueField: 'AmbulanceDecigionTreeRoot_id',
	displayField: 'text',
	typeAhead: true,
	forceSelection: true,
	store: Ext6.create('Ext.data.Store', {
		proxy: {
			type: 'ajax',
			url: '/?c=CmpCallCard&m=getStructuresIssetTree',
			reader: {
				type: 'json'
			}
		}
	})
});

Ext6.define('swDiagTagCombo', {
	extend: 'swBaseTagCombobox',
	store: {type: 'swTagDiagStore'},
	alias: 'widget.swDiagTagCombo',
	fieldLabel: 'Диагноз',
	//displayField: 'Diag_Display',
	displayField: 'Diag_Code',
	typeAhead: true,
	onLoadStore: Ext6.emptyFn,
	minChars: 1,
	valueField: 'Diag_id',
	plugins: [new Ext6.ux.Translit(false, false)],
	//cls: 'trigger-outside',
	selectOnFocus: true,
	enableKeyEvents: true,
	queryDelay: 300,
	queryMode: 'local',
	setValue: function(v) 	// переопределяем функцию, для того чтобы при загрзке формы диагноз в комбике отображался с именем
	{						// потому что при инициализации комбик без записей, а диагноз устанавливается
		var combo = this,
			args = arguments,
			where = '';
		var loadStore = function(val,where){
			combo.getStore().load(
				{
					params:
						{
							where: where
						},
					callback: function (store, records, successful) {
						if (successful)
						{
							combo.setValue(val);
						} else {
							args[0] = null; // зануляем параметр value, если запись не пришла
						}
					}
				});
		};
		if (!Ext6.isEmpty(v) && v && Ext6.isString(v)){
			var arr_str = v.split(',');
			var arr_val = [];
			arr_str.forEach(function(e){ var v = parseInt(e.trim()); arr_val.push(v);});
			where = 'where Diag_id in (' + v + ')';
			loadStore(arr_val,where);
		}

		if (!Ext6.isEmpty(v) && v && ! isNaN(v) && this.getStore().find('Diag_id', v) == -1)
		{
			where = 'where Diag_id = ' + v;
			loadStore(v,where);
		}

		return this.callParent(args); // вызываем оригинальный setValue
	},
	highlightSearchResults: true, // выделять вхождение поискового запроса в результатах
	// для текста внутри поля
	displayTpl: Ext6.create('Ext6.XTemplate',
		'<tpl for=".">',
		//'{Diag_Display}',
		'{[values.Diag_id > -1 ? values.Diag_Code : "sdfg"]}',
		'</tpl>'
	),
	listConfig: { // для выпадающего списка. Будет перезаписан, если highlightSearchResults: true

		loadingText: 'Загружаем диагнозы',
		emptyText: '<span style="color:red;font: normal 12px/17px Roboto, tahoma, arial, verdana, sans-serif;">Диагнозов не найдено</span>',
		getInnerTpl: function() {

			return '{[values.Diag_Display]}\u00a0';
		}
	},
	onLoad: function(store, records, success) {
		var me = this;

		if (me.ignoreSelection > 0) {
			--me.ignoreSelection;
		}

		if (success) {

			if (me.value == null) {
				// Highlight the first item in the list if autoSelect: true
				if (store.getCount()) {
					me.doAutoSelect();
				}
			}
		}
	},
	setFilterByDate: function(dateValue) {

		var value = this.getValue(),
			combo = this,
			index;

		this.filterDate = Ext6.util.Format.date(dateValue, 'd.m.Y');
		this.clearValue();
		this.getStore().clearFilter();
		this.lastQuery = '';

		if ( !Ext6.isEmpty(dateValue) ) {
			this.getStore().filterBy(function(rec) {
				return ((Ext6.isEmpty(rec.get('Diag_begDate')) || rec.get('Diag_begDate') <= dateValue || (rec.get('Diag_begDate') !== null && !Ext6.isEmpty(rec.get('Diag_begDate').date) && rec.get('Diag_begDate').date <= Ext6.util.Format.date(dateValue, 'Y-m-d')) || (rec.get('Diag_begDate').toString().split('.').reverse().join('-') <= Ext6.util.Format.date(dateValue, 'Y-m-d')))
					&& (Ext6.isEmpty(rec.get('Diag_endDate')) || rec.get('Diag_endDate') >= dateValue || (rec.get('Diag_endDate') !== null && !Ext6.isEmpty(rec.get('Diag_endDate').date) && rec.get('Diag_endDate').date >= Ext6.util.Format.date(dateValue, 'Y-m-d')) || (rec.get('Diag_endDate').toString().split('.').reverse().join('-') >= Ext6.util.Format.date(dateValue, 'Y-m-d'))));
			});
		}

		index = this.getStore().findBy(function(rec) {
			return (rec.get(combo.valueField) == value);
		});

		if ( index >= 0 ) {
			this.setValue(value);
			this.fireEvent('select', this, this.findRecord(combo.valueField,value));
		} else {
			this.clearValue();
		}
	},
	clearBaseFilter: function()
	{
		this.baseFilterFn = null;
		this.baseFilterScope = null;
		this.lastQuery = null;
	},
	setBaseFilter: function(fn, scope)
	{
		this.baseFilterFn = fn;
		this.baseFilterScope = scope || this;
		this.store.filterBy(fn, scope);
		this.lastQuery = null;
	},
	onTabAction: function(e){
		return false;
	},
	listeners: {
		keydown: function(inp, e) {


			if (e.getKey() == e.TAB)
			{
				this.onTabAction(e);
			}
			else
			{
				if ( e.getKey() == e.END) {
					this.inKeyMode = true;
					this.select(this.store.getCount() - 1);
				}

				if ( e.getKey() == e.HOME) {
					this.inKeyMode = true;
					this.select(0);
				}

				if ( e.getKey() == e.PAGE_UP) {
					this.inKeyMode = true;
					var ct = this.store.getCount();

					if ( ct > 0 ) {
						if ( this.selectedIndex == -1 ) {
							this.select(0);
						}
						else if ( this.selectedIndex != 0 ) {
							if ( this.selectedIndex - 10 >= 0 ) {
								this.select(this.selectedIndex - 10);
							}
							else {
								this.select(0);
							}
						}
					}
				}

				if ( e.getKey() == e.PAGE_DOWN)
				{
					if (!this.isExpanded)
					{
						this.onTriggerClick();
					}
					else
					{
						this.inKeyMode = true;
						var ct = this.store.getCount();
						if (ct > 0)
						{
							if (this.selectedIndex == -1)
							{
								this.select(0);
							}
							else if (this.selectedIndex != ct - 1)
							{
								if (this.selectedIndex + 10 < ct - 1)
									this.select(this.selectedIndex + 10);
								else
									this.select(ct - 1);
							}
						}
					}
				}

				if (e.shiftKey == false && e.getKey() == e.TAB && inp.focusOnTab != null && inp.focusOnTab.toString().length > 0)
				{
					e.stopEvent();
					if (Ext6.getCmp(this.focusOnTab))
					{
						Ext6.getCmp(this.focusOnTab).focus(true);
					}
				}

				if (e.shiftKey == true && e.getKey() == e.TAB && inp.focusOnShiftTab != null && inp.focusOnShiftTab.toString().length > 0)
				{
					e.stopEvent();
					if (Ext6.getCmp(this.focusOnShiftTab))
					{
						Ext6.getCmp(this.focusOnShiftTab).focus(true);
					}
				}

				if (e.altKey || e.ctrlKey || e.shiftKey)
					return true;

				if ( e.getKey() == e.DELETE)
				{
					inp.setValue('');
					inp.setRawValue("");
					inp.selectIndex = -1;
					if (inp.onClearValue)
						this.onClearValue();
					e.stopEvent();
					return true;
				}

				if (e.getKey() == e.F4)
				{
					this.onTriggerClick();
				}

				if (e.getKey() == e.TAB) {
					this.onTabKeyDown(e);
				}
			}
		},
		blur: function ()
		{
			if (this.getValue() == this.getRawValue()) // если это так, значит в значении комбика простой текст, а не id
			{
				this.setValue(null);
			}
		}
	},
	triggers: {
		search: {
			cls: 'x6-form-search-trigger',
			//extraCls: 'search-icon-out',
			handler: function() {
				var combo = this;

				var searchWindow = getWnd('DiagSearchTreeWindow');

				// TODO вынести параметры для поиска с комбика диагноза в отдельный объект и цеплять их к гриду и дереву при каждом открытии окна
				searchWindow.show({
					onSelect: function(data) {

						if (combo.getStore().find('Diag_id', data.Diag_id) !== -1)
						{
							combo.setValue(data.Diag_id);
						} else
						{
							combo.getStore().removeAll();
							combo.getStore().loadData([data], true);
							combo.setValue(data.Diag_id);
						}

						var record = combo.getStore().findRecord(combo.valueField, data[combo.valueField]);
						combo.fireEvent('select', combo, record);
						combo.fireEvent('change', combo, data[combo.valueField]);
						return true;
					}
				});
			}
		}
	},
	initComponent: function ()
	{
		var searchModule = Ext6.create('TagDiagSearchModule'),
			requiredParams = searchModule.exportSearchModule(),
			defaultParams = searchModule.exportDefaultSearchParams();

		if (this.highlightSearchResults === true)
		{
			requiredParams.listConfig = defaultParams.listConfig;
		}

		Ext6.apply(this, requiredParams); // все свойства добавятся и перезапишутся
		Ext6.applyIf(this, defaultParams); // существующие свойства не перезапишутся

		this.callParent(arguments);
	}
});

Ext6.define('swMedServicePrescrCombo', {
	forceSelection: true,
	triggerAction: 'all',
	codeField: 'MedService_id',
	displayField: 'displayField',
	valueField: 'UslugaComplexMedService_key',
	extend: 'swBaseCombobox',
	alias: 'widget.swMedServicePrescrCombo',
	queryMode: 'remote',
	autoFilter: true,
	enableKeyEvents: true,
	fieldLabel: 'Служба',
	listConfig:{
		minWidth: 500
	},
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item MedStaffFactCombo">',
		'<table style="border: 0; width: 100%">',
		'<tr>',
		'<td width="70%" style="padding-left: 10px;"><div style="font: 13px Roboto; font-weight: 700; color: #000;">{Lpu_Nick}</div></td>',
		'<td width="30%" style="padding-right: 10px;">',
			'<div style="font: 12px Roboto; font-weight: 400;">',
				'{[this.RecToString(values)]}',
			'</div>',
		'</td>',
		'</tr>',
		'<tr>',
		'<td width="70%" style="padding-left: 10px;"><p style="font: 11px Roboto; font-weight: 400; color: #000;">',
		'{[this.formatName(values)]}',
		//'{MedService_Nick}{[Ext.isEmpty(values.pzm_MedService_Nick)?"":" / " + values.pzm_MedService_Nick]}',
		'</p></td>',
		'<td width="30%" style="padding-right: 10px;"><p style="font: 11px Roboto; font-weight: 400; color: #2196f3; padding-top: 5px;">',
		'{[this.QueryString(values)]}&nbsp;',
		'</p></td>',
		'</tr></table>',
		'</div></tpl>',
		{
			formatName: function(values) {
				var str = '';
				if(values.MedService_Nick)
					str += values.MedService_Nick;
				if(!Ext.isEmpty(values.pzm_MedService_Nick))
					str = (str?(str+' / '):'')+ values.pzm_MedService_Nick;
				if(!Ext.isEmpty(values.Resource_Name))
					str = (str?(str+' / '):'')+ values.Resource_Name;
				return str;
			},
			RecToString: function(values){
				var str = '';
				if(values.UslugaComplexMedService_key && values.Timetable_begTime){
					str += '<nobr style="color: #999;">"Ближ. запись"</nobr>';
				}
				return str;
			},
			QueryString: function(values){
				var str = '';
				if(values.UslugaComplexMedService_key){
					if(values.Timetable_begTime){
						str += values.Timetable_begTime;
					} else {
						str += 'В очередь';
					}
				}
				return str;
			}
		}
	),
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			idProperty: 'UslugaComplexMedService_key',
			name: 'MedService_Nick',
			fields: [
				{name: 'UslugaComplexMedService_key', mapping: 'UslugaComplexMedService_key', type: 'string'},
				{name: 'displayField', type:'string', convert: function(val,row) {
						var text = '';
						if(row.get('MedService_Nick'))
							text += row.get('MedService_Nick');
						if(!Ext.isEmpty(row.get('pzm_MedService_Nick')))
							text = row.get('pzm_MedService_Nick') + (text?(' / '+text):'');
						if(!Ext.isEmpty(row.get('Resource_Name')))
							text = (text?(text+' / '):'')+ row.get('Resource_Name');
						if (me.loadedByAllLpu || me.onlyByContract) {
							text = row.get('Lpu_Nick') + ' / ' + text;
						}
						return text;
					}},
				{name: 'UslugaComplexMedService_id', mapping: 'UslugaComplexMedService_id', type: 'int'},
				{name: 'pzm_UslugaComplexMedService_id', mapping: 'pzm_UslugaComplexMedService_id', type: 'int'},
				{name: 'MedService_id', mapping: 'MedService_id', type: 'int'},
				{name: 'UslugaComplex_id', mapping: 'UslugaComplex_id', type: 'int'},
				{name: 'LpuUnit_id', mapping: 'LpuUnit_id', type: 'int'},
				{name: 'Lpu_id', mapping: 'Lpu_id', type: 'int'},
				{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id', type: 'int'},
				{name: 'LpuSection_id', mapping: 'LpuSection_id', type: 'int'},
				{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id', type: 'int'},
				{name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id', type: 'int'},
				{name: 'MedServiceType_id', mapping: 'MedServiceType_id', type: 'int'},
				{name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick', type: 'string'},
				{name: 'MedServiceType_SysNick', mapping: 'MedServiceType_SysNick', type: 'string'},
				{name: 'UslugaComplex_Code', mapping: 'UslugaComplex_Code', type: 'string'},
				{name: 'UslugaComplex_Name', mapping: 'UslugaComplex_Name', type: 'string'},
				{name: 'MedService_Nick', mapping: 'MedService_Nick', type: 'string'},
				{name: 'MedService_Name', mapping: 'MedService_Name', type: 'string'},
				{name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string'},
				{name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name', type: 'string'},
				{name: 'LpuUnit_Name', mapping: 'LpuUnit_Name', type: 'string'},
				{name: 'LpuSection_Name', mapping: 'LpuSection_Name', type: 'string'},
				{name: 'LpuUnit_Address', mapping: 'LpuUnit_Address', type: 'string'},
				{name: 'ttms_MedService_id', mapping: 'ttms_MedService_id', type: 'int'},
				{name: 'lab_MedService_id', mapping: 'lab_MedService_id', type: 'int'},
				{name: 'pzm_MedService_id', mapping: 'pzm_MedService_id', type: 'int'},
				{name: 'pzm_Lpu_id', mapping: 'pzm_Lpu_id', type: 'int'},
				{name: 'pzm_MedServiceType_id', mapping: 'pzm_MedServiceType_id', type: 'int'},
				{name: 'pzm_MedServiceType_SysNick', mapping: 'pzm_MedServiceType_SysNick', type: 'string'},
				{name: 'pzm_MedService_Nick', mapping: 'pzm_MedService_Nick', type: 'string'},
				{name: 'pzm_MedService_Name', mapping: 'pzm_MedService_Name', type: 'string'},
				{name: 'TimetableMedService_id', mapping: 'TimetableMedService_id', type: 'int'},
				{name: 'TimetableMedService_begTime', mapping: 'TimetableMedService_begTime', type: 'string'},
				{name: 'TimetableResource_begTime', mapping: 'TimetableResource_begTime', type: 'string'},
				{name: 'Timetable_begTime', type: 'string', convert: function(val,row) {
						var date = null;
						if (row.get('TimetableResource_begTime')) {
							date = row.get('TimetableResource_begTime');
						} else if (row.get('TimetableMedService_begTime')) {
							date = row.get('TimetableMedService_begTime');
						}
						var text = '';
						if (date) {
							var dt = Date.parseDate(date, 'd.m.Y H:i');
							if (dt) {
								text = dt.format('d.m.Y').toLowerCase() + ' ' + sw4.getRussianDayOfWeek(Ext6.util.Format.date(dt, 'w')) + ' ' + dt.format('H:i').toLowerCase();
							}
						}

						return text;
					}},
				{name: 'TimetableResource_id', mapping: 'TimetableResource_id', type: 'int'},
				{name: 'Resource_id', mapping: 'Resource_id', type: 'int'},
				{name: 'Resource_Name', mapping: 'Resource_Name', type: 'string'},
				{name: 'ttr_Resource_id', mapping: 'ttr_Resource_id', type: 'int'}
			],
			proxy: {
				extraParams: {
					start: 0,
					limit: 100,
					isOnlyPolka: 0,
					filterByLpu_str: ''
				},
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=MedService&m=getMedServiceSelectCombo',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			}
		});

		me.callParent(arguments);
	}
});

/**
 * Комбобокс выбора дополнительного профиля
 */
Ext6.define('swLpuSectionProfileDopRemoteCombo', {
	forceSelection: true,
	extend: 'swBaseCombobox',
	alias: 'widget.swLpuSectionProfileDopRemoteCombo',
	queryMode: 'local',
	triggerAction: 'all',
	displayField: 'displayField',
	valueField: 'LpuSectionProfile_id',
	fieldLabel: langs('Профиль'),
	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id'},
				{name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code', type: 'string'},
				{name: 'LpuSectionProfile_Name', mapping: 'LpuSectionProfile_Name', type: 'string'},
				{
					name: 'displayField',
					type: 'string',
					convert: function(val, row) {
						if (row.get('LpuSectionProfile_id')) {
							return row.get('LpuSectionProfile_Code') + '. ' + row.get('LpuSectionProfile_Name');
						} else {
							return '';
						}
					}
				}
			],
			autoLoad: false,
			sorters: {
				property: 'LpuSectionProfile_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=Common&m=loadLpuSectionProfileDopList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		me.callParent(arguments);
	}
});

/**
 * Комбобокс выбора МЭС для Екатеринбурга
 */
Ext6.define('swMesEkbCombo', {
	forceSelection: true,
	extend: 'swBaseCombobox',
	alias: 'widget.swMesEkbCombo',
	queryMode: 'local',
	triggerAction: 'all',
	displayField: 'displayField',
	valueField: 'Mes_id',
	fieldLabel: langs('МЭС'),
	initComponent: function() {
		let me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			autoLoad: false,
			fields: [
				{ name: 'Mes_id', mapping: 'Mes_id' },
				{ name: 'Mes_Code', mapping: 'Mes_Code' },
				{ name: 'Mes_Name', mapping: 'Mes_Name' },
				{
					name: 'displayField',
					type: 'string',
					convert: function(val, row) {
						if (row.get('Mes_id')) {
							return row.get('Mes_Code') + '. ' + row.get('Mes_Name');
						} else {
							return '';
						}
					}
				}
			],
			mode: 'remote',
			proxy: {
				actionMethods:  {
					create: "POST",
					read: "POST",
					update: "POST",
					destroy: "POST"
				},
				reader: {
					type: 'json'
				},
				type: 'ajax',
				url: '/?c=EvnVizit&m=loadMesEkbList'
			},
			sorters: {
				property: 'Mes_Code',
				direction: 'ASC'
			}
		});

		me.callParent(arguments);
	}
});

Ext6.define('StorageModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.StorageModel',
	idProperty: 'Storage_id',
	fields: [
		{name: 'Storage_id', type:'int'},
		{name: 'StorageType_id', type:'int'},
		{name: 'StorageType_Code', type:'int'},
		{name: 'Storage_Code', type:'int'},
		{name: 'Storage_Name', type:'string'},
		{name: 'Storage_begDate', type:'date', dateFormat: 'd.m.Y'},
		{name: 'Storage_endDate', type:'date', dateFormat: 'd.m.Y'},
		{name: 'StorageStructLevel', type:'string'},
		{name: 'LpuSection_id', type:'int'},
		{name: 'MedService_id', type:'int'},
		{name: 'Org_id', type:'int'}
	]
});

Ext6.define('StorageStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.StorageStore',
	model: 'StorageModel',
	autoLoad: true,
	proxy: {
		type: 'ajax',
		reader: {
			type: 'json'
		},
		url: '/?c=DocumentUc&m=loadStorageList'
	}
});

Ext6.define('StorageCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.StorageCombo',
	fieldLabel: langs('Склад'),
	queryMode: 'remote',
	minChars: 1,
	editable: true,
	name: 'Storage_id',
	displayField: 'Storage_Name',
	valueField: 'Storage_id',
	store: {type: 'StorageStore'}
});

Ext6.define('MolModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.MolModel',
	idProperty: 'Mol_id',
	fields: [
		{name: 'Mol_id', type:'int'},
		{name: 'Mol_Code', type:'int'},
		{name: 'LpuSection_id', type:'int'},
		{name: 'Contragent_id', type:'int'},
		{name: 'Storage_id', type:'int'},
		{name: 'MedPersonal_id', type:'int'},
		{name: 'Person_Fio', type:'string'},
		{name: 'Mol_begDT', type: 'date', dateFormat: 'd.m.Y'}
	]
});

Ext6.define('MolStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.MolStore',
	model: 'MolModel',
	proxy: {
		type: 'ajax',
		reader: {
			type: 'json'
		},
		url: '/?c=EvnDrug&m=getMolCombo'
	},
	sorters: {
		property: 'Person_Fio',
		direction: 'ASC'
	}
});

Ext6.define('MolCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.MolCombo',
	fieldLabel: langs('МОЛ'),
	queryMode: 'local',
	name: 'Mol_id',
	displayField: 'Person_Fio',
	valueField: 'Mol_id',
	store: {type: 'MolStore'}
});

Ext6.define('DrugPrepModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.DrugPrepModel',
	idProperty: 'DrugPrepFas_id',
	fields: [
		{name: 'DrugPrep_id'},
		{name: 'DrugPrep_Name'},
		{name: 'DrugPrepFas_id'},
		{name: 'Storage_id'},
		{name: 'hintPackagingData'},
		{name: 'hintRegistrationData'},
		{name: 'hintPRUP'},
		{name: 'FirmNames'}
	]
});

Ext6.define('DrugPrepStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.DrugPrepStore',
	model: 'DrugPrepModel',
	autoLoad: false,
	proxy: {
		type: 'ajax',
		reader: {
			type: 'json'
		},
		url: '/?c=EvnDrug&m=loadDrugPrepList'
	}
});

Ext6.define('DrugPrepCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.DrugPrepCombo',

	fieldLabel: langs('Медикамент'),

	drugField: 'Drug_id',
	drugPrepField: 'DrugPrepFas_id',
	valueField: 'DrugPrepFas_id',

	name: 'DrugPrepFas_id',
	displayField: 'DrugPrep_Name',

	forceSelection: true,
	queryDelay: 500,
	minChars: 3,
	minLength: 1,
	queryMode: 'remote',
	selectOnFocus: true,

	store: {type: 'DrugPrepStore'},

	triggers: {
		search: {
			cls: 'x6-form-search-trigger',
			extraCls: 'search-icon-out',
			handler: function ()
			{

			}
		}
	},

	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item" {[this.titleSet(values.DrugPrep_Name, values.hintPackagingData, values.hintRegistrationData, values.hintPRUP, values.FirmNames)]}>',
		'<h3>{DrugPrep_Name}&nbsp;</h3>',
		'</div></tpl>',
		{
			titleSet: function(str1,str2,str3,str4, firmNames){
				var mark = '&#8226;';
				var br = '&#013;';
				var bodyHint = '';
				var hintstr1 = (str1) ? str1.replace(/"/g, "&#8242;"): '';
				var hintstr2 = '';
				if(str2){
					if(firmNames){
						var pr = firmNames;
						pr = pr.replace('(', '\\(');
						pr = pr.replace(')', '\\)');
						var re = new RegExp("(, |^)"+pr+".*");
						hintstr2 = str2.replace(re, '');
						hintstr2 = hintstr2.replace(/"/g, "&#8242;");
					}else{
						hintstr2 = str2.replace(/"/g, "&#8242;");
					}
				}
				var hintstr3 = (str3) ? str3.replace(/"/g, "&#8242;"): '';
				var hintstr4 = (str4) ? str4.replace(/"/g, "&#8242;"): '';

				if(hintstr1){
					bodyHint += mark + ' Торговое наименование: ' + hintstr1 + br;
				}
				if(hintstr2){
					if(hintstr2.slice(-1) == ',') hintstr2=hintstr2.slice(0, -1);
					bodyHint += mark + ' Данные об упаковке: ' + hintstr2 + br;
				}
				if(hintstr3){
					if(hintstr3.slice(-1) == ',') hintstr3=hintstr3.slice(0, -1);
					bodyHint += mark + ' Данные о регистрации: ' + hintstr3 + br;
				}
				if(hintstr4){
					bodyHint += mark + ' Пр./Уп.: ' + hintstr4;
				}
				return 'title="'+bodyHint+'"';
			}
		}
	),
	onTrigger2Click: function()
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
				this.formList = new sw.Promed.swListSearchWindow({
					//params: {
					title: langs('Поиск медикамента'),
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
							{name: 'DrugTorg_Name',  headerName:langs('Торговое наименование/Мнн') ,header: langs('Торговое наименование'), isfilter:true, columnWidth: '.4'},
							{name: 'DrugMnn',  header: langs('МНН'), width: 200},
							{name: 'DrugForm_Name', header: langs('Форма выпуска'), width: 120,isfilter:true, columnWidth: '.14'},
							{name: 'Drug_Dose', header: langs('Дозировка'), width: 80, isfilter:true, columnWidth: '.14'},
							{name: 'Drug_Fas', header: langs('Фасовка'), width: 80},
							{name: 'Drug_PackName', header: langs('Упаковка'), width: 800},
							{name: 'Drug_Firm', header: langs('Производитель'), width: 180, isfilter:true, columnWidth: '.3'},
							{name: 'Drug_Ean', header: 'EAN', width: 80},
							{name: 'Drug_RegNum', header: langs('РУ'), width: 100}
						],
					useBaseParams: true
					//}
				});
			}
		}
		// выбираем компонент
		var combo = null;
		if (this.drugPrepField) {
			var form = this.findForm();
			if (form)
				combo = form.getForm().findField(this.drugPrepField);
		}
		else
			combo = this;
		var params = (combo.getStore().baseParams)?combo.getStore().baseParams:{};
		params.DrugPrepFas_id = null;
		params.mode = 'ostat';
		combo.collapse();
		this.collapse();
		this.formList.show({
			params:params,
			onSelect: function(data)
			{
				// на форме должно быть два компонента
				var cp = this.findForm().getForm().findField((this.drugField)?this.drugField:'Drug_id');
				cp.getStore().removeAll();
				cp.clearValue();
				combo.hasFocus = false;
				// Читаем DrugPrepFas
				cp.getStore().baseParams.DrugPrepFas_id=data['DrugPrepFas_id'];
				cp.getStore().baseParams.query=null;
				cp.getStore().load({
					callback: function() {
						this.setValue(data['DrugPrepFas_id']);
						cp.setValue(data['Drug_id']);

						this.startValue = this.getValue();
						cp.startValue = cp.getValue();

						cp.fireEvent('change', cp, data['Drug_id'], data['Drug_id']);
						combo.hasFocus = true;
						combo.getStore().baseParams.DrugPrepFas_id=null;
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
});

Ext6.define('DrugPackModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.DrugPackModel',
	idProperty: 'Drug_id',
	fields: [
		{name: 'Drug_Fas'},
		{name: 'DrugPrepFas_id'},
		{name: 'Drug_id'},
		{name: 'Drug_Code'},
		{name: 'Drug_Name'},
		{name: 'Drug_FullName'},
		{name: 'DrugForm_Name'},
		{name: 'DrugUnit_Name'},
		{name: 'GoodsUnit_bid'},
		{name: 'GoodsUnit_bName'},
		{name: 'GoodsPackCount_bCount'}
	]
});

Ext6.define('DrugPackStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.DrugPackStore',
	model: 'DrugPackModel',
	proxy: {
		type: 'ajax',
		reader: {
			type: 'json'
		},
		url: '/?c=EvnDrug&m=loadDrugList'
	}
});

Ext6.define('DrugPackCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.DrugPackCombo',

	fieldLabel: langs('Упаковка'),
	queryMode: 'remote',
	name: 'Drug_id',
	editable: false,
	displayField: 'Drug_Name',
	drugPrepField: 'DrugPrepFas_id',
	drugField: 'Drug_id',
	valueField: 'Drug_id',
	store: {type: 'DrugPackStore'},

	triggers: {
		search: {
			cls: 'x6-form-search-trigger',
			extraCls: 'search-icon-out',
			handler: function ()
			{

			}
		}
	},

	onTrigger2Click: function()
	{
		if (this.disabled)
			return false;
		var combo = this;
// Именно для этого комбо логика несколько иная
		if (!this.formList)
		{
			if (Ext.getCmp('DrugPackWinSearch')) {
				this.formList = Ext.getCmp('DrugPackWinSearch');
			} else {
				this.formList = new sw.Promed.swListSearchWindow(
					{
						//params: {
						title: langs('Поиск медикамента'),
						id: 'DrugPackWinSearch',
						object: 'Drug',
						modal: false,
						//maximizable: true,
						maximized: true,
						paging: true,
						columns: true,
						prefix: 'dpckws',
						dataUrl: '/?c=Farmacy&m=loadDrugMultiList',
						stringfields:
							[
								{name: 'Drug_id', key: true},
								{name: 'DrugPrepFas_id', hidden: true},
								{name: 'DrugTorg_Name', autoexpand: true, header: langs('Торговое наименование'), isfilter:true},
								{name: 'DrugForm_Name', header: langs('Форма выпуска'), width: 140, isfilter:true},
								{name: 'Drug_Dose', header: langs('Дозировка'), width: 100, isfilter:true},
								{name: 'Drug_Fas', header: langs('Фасовка'), width: 100},
								{name: 'Drug_PackName', header: langs('Упаковка'), width: 100},
								{name: 'Drug_Firm', header: langs('Производитель'), width: 200, isfilter:true},
								{name: 'Drug_Ean', header: 'EAN', width: 100},
								{name: 'Drug_RegNum', header: langs('РУ'), width: 120}
							],
						useBaseParams: true
						//}
					});
			}
		}
// выбираем компонент
		var combo = null;
		if (this.drugPrepField)
		{
			var form = this.findForm();
			if (form)
				combo = form.getForm().findField(this.drugPrepField);
		}
		else
			combo = this;

		var params = (combo.getStore().baseParams)?combo.getStore().baseParams:{};
		params.DrugPrepFas_id = null;
		params.mode = 'ostat';

		combo.collapse();
		this.collapse();

		this.formList.show(
			{
				params:params,
				onSelect: function(data)
				{
					// на форме должно быть два компонента
					var cp = this.findForm().getForm().findField((this.drugField)?this.drugField:'Drug_id');
					cp.getStore().removeAll();
					cp.clearValue();
					cp.hasFocus = false;
					// Читаем DrugPrepFas
					cp.getStore().baseParams.DrugPrepFas_id=data['DrugPrepFas_id'];
					cp.getStore().load({
						callback: function() {
							this.setValue(data['DrugPrepFas_id']);
							cp.setValue(data['Drug_id']);

							this.startValue = this.getValue();
							cp.startValue = cp.getValue();

							cp.fireEvent('change', cp, data['Drug_id'], data['Drug_id']);

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
});

Ext6.define('DocumentUcStrModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.DocumentUcStrModel',
	idProperty: 'DocumentUcStr_id',
	fields: [
		{ name: 'DocumentUcStr_id'},
		{ name: 'DocumentUc_id'},
		{ name: 'DocumentUcStr_Name'},
		{ name: 'DocumentUcStr_Count'},
		{ name: 'DocumentUcStr_Price'},
		{ name: 'DocumentUcStr_Sum'},
		{ name: 'PrepSeries_Ser'},
		{ name: 'PrepSeries_GodnDate'},
		{ name: 'DrugFinance_id'},
		{ name: 'DrugFinance_Name'},
		{ name: 'GoodsUnit_id'}
	]
});

Ext6.define('DocumentUcStrStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.DocumentUcStrStore',
	model: 'DocumentUcStrModel',
	autoLoad: false,
	proxy: {
		type: 'ajax',
		reader: {
			type: 'json'
		},
		url: '/?c=EvnDrug&m=loadDocumentUcStrList'
	}
});

Ext6.define('DocumentUcStrCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.DocumentUcStrCombo',
	fieldLabel: langs('Партия'),
	forceSelection: true,

	name: 'DocumentUcStr_oid',
	displayField: 'DocumentUcStr_Name',
	valueField: 'DocumentUcStr_id',
	codeField: 'PrepSeries_Ser',

	minChars: 3,
	minLength: 1,

	queryMode: 'local',
	selectOnFocus: true,

	// doQuery: function(q, forceAll) {
	// 	if ( q === undefined || q === null ) {
	// 		q = '';
	// 	}
	//
	// 	var qe = {
	// 		query: q,
	// 		forceAll: forceAll,
	// 		combo: this,
	// 		cancel: false
	// 	};
	//
	// 	if ( this.fireEvent('beforequery', qe) === false || qe.cancel ) {
	// 		return false;
	// 	}
	//
	// 	q = qe.query;
	// 	forceAll = qe.forceAll;
	//
	// 	if (forceAll) {
	// 		this.lastQuery = q;
	// 		this.getStore().clearFilter();
	// 		this.selectedIndex = -1;
	// 		this.onLoad();
	// 	} else if ( q.length >= this.minChars ) {
	// 		if ( this.lastQuery != q ) {
	// 			this.lastQuery = q;
	// 			this.selectedIndex = -1;
	//
	// 			this.getStore().filterBy(function(record, id) {
	// 				var result = true;
	// 				var patt_display = new RegExp(q.toLowerCase());
	// 				var patt_code = new RegExp('^' + q.toLowerCase());
	//
	// 				result = patt_display.test(record.get(this.displayField).toLowerCase());
	//
	// 				if ( !result ) {
	// 					result = patt_code.test(record.get(this.codeField).toLowerCase());
	// 				}
	//
	// 				return result;
	// 			}, this);
	//
	// 			this.onLoad();
	// 		}
	// 		else {
	// 			this.selectedIndex = -1;
	// 			this.onLoad();
	// 		}
	// 	}
	// },

	tpl: new Ext6.XTemplate(
		'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
		'<td style="padding: 2px; width: 20%;">Срок годности</td>',
		'<td style="padding: 2px; width: 15%;">Цена</td>',
		'<td style="padding: 2px; width: 15%;">Остаток</td>',
		'<td style="padding: 2px; width: 35%;">Источник финансирования</td>',
		'<td style="padding: 2px; width: 15%;">Серия</td></tr>',
		'<tpl for="."><tr class="x6-boundlist-item" style="font-family: tahoma; font-size: 8pt;">',
		'<td style="padding: 2px;">{PrepSeries_GodnDate}&nbsp;</td>',
		'<td style="padding: 2px;">{DocumentUcStr_Price}&nbsp;</td>',
		'<td style="padding: 2px;">{DocumentUcStr_Count}&nbsp;</td>',
		'<td style="padding: 2px;">{DrugFinance_Name}&nbsp;</td>',
		'<td style="padding: 2px;">{PrepSeries_Ser}&nbsp;</td>',
		'</tr></tpl>',
		'</table>'
	),

	store: {type: 'DocumentUcStrStore'}
});

Ext6.define('EvnPrescrTreatDrugModel', {
	extend: 'Ext6.data.Model',
	alias: 'model.EvnPrescrTreatDrugModel',
	idProperty: 'EvnPrescrTreatDrug_id',
	fields: [
		{ name: 'EvnPrescrTreatDrug_id'},
		{ name: 'EvnCourse_id'},
		{ name: 'EvnCourseTreatDrug_id'},
		{ name: 'EvnPrescrTreat_id'},
		{ name: 'EvnPrescrTreat_pid'},
		{ name: 'EvnPrescrTreat_setDate', type: 'date', dateFormat: 'd.m.Y' },
		{ name: 'EvnPrescrTreat_PrescrCount'},
		{ name: 'EvnPrescrTreatDrug_FactCount'},
		{ name: 'EvnPrescrTreatDrug_DoseDay'},
		{ name: 'PrescrFactCountDiff'},
		{ name: 'EvnPrescrTreat_Fact'},
		{ name: 'Drug_id'},
		{ name: 'Drug_Fas'},
		{ name: 'DocumentUcStr_Count'},
		{ name: 'EvnDrug_KolvoEd'},
		{ name: 'EvnDrug_Kolvo'},
		{ name: 'DrugPrepFas_id'},
		{ name: 'DocumentUcStr_oid'},
		{ name: 'DocumentUcStr_Name'},
		{ name: 'LpuSection_id'},
		{ name: 'Storage_id'},
		{ name: 'Mol_id'},
		{ name: 'Mol_Name'},
		{ name: 'DrugFinance_Name'},
		{ name: 'WhsDocumentCostItemType_Name'},
		{ name: 'DrugForm_Name'},
		{ name: 'Drug_Name'},
		{ name: 'GoodsUnit_id'},
		{ name: 'GoodsPackCount_Count'}
	]
});

Ext6.define('EvnPrescrTreatDrugStore', {
	extend: 'Ext6.data.Store',
	alias: 'store.EvnPrescrTreatDrugStore',
	autoLoad: false,
	proxy: {
		type: 'ajax',
		reader: {
			type: 'json'
		},
		url: '/?c=EvnPrescr&m=loadEvnPrescrTreatDrugCombo'
	}
});

Ext6.define('swDocumentPrivilegeType', {
	forceSelection: true,
	triggerAction: 'all',
	displayField: 'DocumentPrivilegeType_Name',
	valueField: 'DocumentPrivilegeType_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swDocumentPrivilegeType',
	queryMode: 'local',
	fieldLabel: langs('Вид документа'),
	initComponent: function() {
		this.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'DocumentPrivilegeType_id', mapping: 'DocumentPrivilegeType_id', type: 'int'},
				{name: 'DocumentPrivilegeType_Code', mapping: 'DocumentPrivilegeType_Code', type: 'int'},
				{name: 'DocumentPrivilegeType_Name', mapping: 'DocumentPrivilegeType_Name', type: 'string'}
			],
			autoLoad: true,
			proxy: {
				type: 'ajax',
				actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
				url: '/?c=Privilege&m=loadDocumentPrivilegeTypeCombo',
				reader: {
					type: 'json',
					id: 'DocumentPrivilegeType_id'
				}
			},
			mode: 'local'
		})
		this.callParent(arguments);
	}
});

Ext6.define('EvnPrescrTreatDrugCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.EvnPrescrTreatDrugCombo',
	displayField: 'Drug_Name',
	fieldLabel: langs('Назначение'),
	forceSelection: true,
	name: 'EvnPrescrTreatDrug_id',
	minChars: 1,
	minLength: 1,
	mode: 'remote',
	selectOnFocus: true,
	valueField: 'EvnPrescrTreatDrug_id',
	store: {type: 'EvnPrescrTreatDrugStore'},
	/*
	 Медикамент1, дневная доза, Количество выполненных приемов./Количество приемов в сутки
	 */
	tpl: new Ext6.XTemplate(
		'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 8pt; font-weight: bold;">',
		'<td style="padding: 2px; width: 60%;">Медикамент</td>',
		'<td style="padding: 2px; width: 20%;">Дневная доза</td>',
		'<td style="padding: 2px; width: 20%;">Приемов</td>',
		'<tpl for="."><tr class="x6-boundlist-item" style="font-family: tahoma; font-size: 8pt;">',
		'<td style="padding: 2px;">{Drug_Name}&nbsp;</td>',
		'<td style="padding: 2px;">{EvnPrescrTreatDrug_DoseDay}&nbsp;</td>',
		'<td style="padding: 2px;">{EvnPrescrTreatDrug_FactCount}/{EvnPrescrTreat_PrescrCount}&nbsp;</td>',
		'</tr></tpl>',
		'</table>'
	)
});

Ext6.define('swDispOutTypeCombo', {
	extend: 'swCommonSprCombo',
	comboSubject: 'DispOutType',
	alias: 'widget.swDispOutTypeCombo',
	fieldLabel: 'Причина исключения'
});

Ext6.define('swPersonModelCombo', {
	extend: 'swCommonSprCombo',
	comboSubject: 'PersonModel',
	alias: 'widget.swPersonModelCombo',
	fieldLabel: 'Группа',
	displayField: 'PersonModel_Name',
	displayCode: false,
	sortField: 'PersonModel_id',
	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item">',
		'{[this.modelName(values.PersonModel_id)]} {PersonModel_Name}&nbsp;',
		'</div></tpl>',
		{
			modelName: function(model_id){
				var s='';
				while(model_id>0) { model_id-=1; s+='I';}
				return s!='' ? 'Группа '+s : '';
			}
		}
	),
	displayTpl: new Ext6.XTemplate(
		'<tpl for=".">',
			'{[this.modelName(values.PersonModel_id)]} {PersonModel_Name}',
		'</tpl>',
		{
			modelName: function(model_id){
				var s='';
				while(model_id>0) { model_id-=1; s+='I';}
				return s!='' ? 'Группа '+s : '';
			}
		}
	),
});

Ext6.define('swLabelInviteStatusCombo', {
	extend: 'swCommonSprCombo',
	comboSubject: 'LabelInviteStatus',
	alias: 'widget.swLabelInviteStatusCombo',
	fieldLabel: 'Статус приглашения'
});

Ext6.define('swReceptGenFormCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.swReceptGenFormCombo',
	codeField: 'ReceptForm_Code',
	displayField: 'ReceptForm_Name',
	editable: false,
	forceSelection: true,
	hideEmptyRow: true,
	fieldLabel: 'Форма рецепта',
	valueField: 'ReceptForm_id',
	hiddenName: 'ReceptForm_id',
	lastQuery: '',
	initComponent: function()
	{
		this.store = new Ext6.create('Ext6.data.Store', {
			autoLoad: true,
			fields: [
				{ name: 'ReceptForm_id', mapping: 'ReceptForm_id', type: 'int', hidden: 'true'},
				{ name: 'ReceptForm_Code', mapping: 'ReceptForm_Code'},
				{ name: 'ReceptForm_Name', mapping: 'ReceptForm_Name' }
			],
			//autoLoad: false,
			sorters: {
				property: 'ReceptForm_id',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				//url : '/?c=EvnRecept&m=getReceptGenFormList',
				url : C_RECEPTGENFORM_GET_LIST,
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		this.callParent(arguments);
	}
});

Ext6.define('swEMDDocumentTypeLocal', {
	forceSelection: true,
	triggerAction: 'all',
	displayField: 'EMDDocumentTypeLocal_Name',
	valueField: 'EMDDocumentTypeLocal_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swEMDDocumentTypeLocal',
	queryMode: 'local',
	enableKeyEvents: true,
	fieldLabel: 'Вид документа',
	initComponent: function() {

		this.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'EMDDocumentTypeLocal_id', mapping: 'EMDDocumentTypeLocal_id', type: 'int' },
				{ name: 'EMDDocumentTypeLocal_Name', mapping: 'EMDDocumentTypeLocal_Name', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'EMDDocumentTypeLocal_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=EMD&m=getDocumentTypeLocalList',
				reader: {
					type: 'json'
				}
			},
			mode: 'local'
		});

		this.callParent(arguments);
	}
});

Ext6.define('swEMDQueryStatus', {
	displayField: 'EMDQueryStatus_Name',
	valueField: 'EMDQueryStatus_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swEMDQueryStatus',
	fieldLabel: 'Статус',
	queryMode: 'local',
	lastQuery: '',
	initComponent: function() {

		this.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'EMDQueryStatus_id', mapping: 'EMDQueryStatus_id', type: 'int' },
				{ name: 'EMDQueryStatus_Name', mapping: 'EMDQueryStatus_Name', type: 'string'}
			],
			sorters: {
				property: 'EMDQueryStatus_Name',
				direction: 'ASC'
			},
			autoLoad: true,
			lastQuery: '',
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=EMD&m=getEMDQueryStatus',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		this.callParent(arguments);
	},
	insertEmptyRecord: function() {
		if (this.store.getCount() > 0 && this.store.getAt(0).data[this.valueField] != "" && this.allowBlank == true && this.hideEmptyRow != true) {
			var data = {};

			if (this.codeField) {
				data[this.codeField] = "";
			}
			data[this.valueField] = -1;
			data[this.displayField] = "";
			data['additionalSortCode'] = -2;

			this.store.insert(0, data);
		}
	}
});

Ext6.define('swEMDDocumentTypeLocalRemote', {
	displayField: 'EMDDocumentTypeLocal_Name',
	valueField: 'EMDDocumentTypeLocal_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swEMDDocumentTypeLocalRemote',
	queryMode: 'local',
	lastQuery: '',
	fieldLabel: 'Тип документа',
	typeAhead: true,
	initComponent: function() {

		this.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'EMDDocumentTypeLocal_id', mapping: 'EMDDocumentTypeLocal_id', type: 'int' },
				{ name: 'EMDDocumentTypeLocal_Name', mapping: 'EMDDocumentTypeLocal_Name', type: 'string'}
			],
			autoLoad: true,
			lastQuery: '',
			sorters: {
				property: 'EMDDocumentTypeLocal_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=EMD&m=getDocumentTypeLocalList',
				reader: {
					type: 'json'
				}
			},
			mode: 'remote'
		});

		this.callParent(arguments);
	}
});

Ext6.define('swTagEMDDocumentTypeLocal', {
	extend: 'swBaseTagCombobox',
	store: Ext6.create('Ext6.data.Store', {
		fields: [
			{ name: 'EMDDocumentTypeLocal_id', mapping: 'EMDDocumentTypeLocal_id', type: 'int' },
			{ name: 'EMDDocumentTypeLocal_Name', mapping: 'EMDDocumentTypeLocal_Name', type: 'string'}
		],
		sorters: {
			property: 'EMDDocumentTypeLocal_Name',
			direction: 'ASC'
		},
		proxy: {
			type: 'ajax',
			actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
			url : '/?c=EMD&m=getDocumentTypeLocalList',
			reader: {
				type: 'json'
			}
		},
		mode: 'local'
	}),
	alias: 'widget.swTagEMDDocumentTypeLocal',
	fieldLabel: 'Вид документа',
	displayField: 'EMDDocumentTypeLocal_Name',
	typeAhead: true,
	valueField: 'EMDDocumentTypeLocal_id',
	selectOnFocus: true,
	enableKeyEvents: true,
	queryDelay: 300,
	queryMode: 'local',
	displayTpl: Ext6.create('Ext6.XTemplate',
		'<tpl for=".">',
		'{[values.EMDDocumentTypeLocal_id > -1 ? values.EMDDocumentTypeLocal_id : "empty"]}',
		'</tpl>'
	),
	initComponent: function ()
	{
		this.callParent(arguments);
	}
});

Ext6.define('swTagLpu', {
	extend: 'swBaseTagCombobox',
	store: Ext6.create('Ext6.data.Store', {
		fields: [
			{ name: 'Lpu_id', mapping: 'Lpu_id', type: 'int' },
			{ name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string' },
			{ name: 'Lpu_BegDate', mapping: 'Lpu_BegDate', type: 'date', dateFormat: 'd.m.Y' },
			{ name: 'Lpu_EndDate', mapping: 'Lpu_EndDate', type: 'date', dateFormat: 'd.m.Y' }
		],
		autoLoad: false,
		sorters: {
			property: 'Lpu_Nick',
			direction: 'ASC'
		},
		proxy: {
			type: 'ajax',
			url : '/?c=MongoDBWork&m=getData',
			reader: {
				type: 'json'
			}
		},
		baseParams: {
			Lpu_id: '',
			Lpu_Nick: '',
			Lpu_BegDate: '',
			Lpu_EndDate: '',
			object: 'Lpu'
		},
		tableName: 'Lpu',
		mode: 'local'
	}),
	alias: 'widget.swTagLpu',
	fieldLabel: 'Вид документа',
	displayField: 'Lpu_Nick',
	typeAhead: true,
	valueField: 'Lpu_id',
	selectOnFocus: true,
	enableKeyEvents: true,
	queryDelay: 300,
	queryMode: 'local',
	tpl: Ext6.create('Ext6.XTemplate',
		'<tpl for="."><div class="x6-boundlist-item lpu-section-global-combo" style="padding:10px 30px 8px 16px;">',
		'<div style="padding-right: 20px"><p style="line-height: 16px">{Lpu_Nick}&nbsp;</p>',
		'<p style="font-size: 10px; line-height: 16px" class="lpu-section-info">{[!Ext6.isEmpty(values.Lpu_BegDate) ? "Действует с: " + Ext6.util.Format.date(values.Lpu_BegDate,"d.m.Y"):""]} {[!Ext6.isEmpty(values.Lpu_EndDate) ? "Дата закрытия: " + Ext.util.Format.date(values.Lpu_EndDate,"d.m.Y"):""]}</p>',
		'</div>',
		'</div></tpl>'
	),
	displayTpl: Ext6.create('Ext6.XTemplate',
		'<tpl for=".">',
		'{[values.Lpu_id > -1 ? values.Lpu_id : "empty"]}',
		'</tpl>'
	),
	initComponent: function ()
	{
		this.callParent(arguments);
	}
});

Ext6.define('swTagLpuSection', {
	extend: 'swBaseTagCombobox',
	store: Ext6.create('Ext6.data.Store', {
		fields: [
			{ name: 'LpuSection_id', mapping: 'LpuSection_id', type: 'int' },
			{ name: 'LpuSection_Code', mapping: 'LpuSection_Code', type: 'string'},
			{ name: 'LpuSection_Name', mapping: 'LpuSection_Name', type: 'string'},
			{
				name: 'LpuSection_Display', calculate: function(data) {
					return data.LpuSection_Code + '. ' + data.LpuSection_Name;
				}
			}
		],
		autoLoad: false,
		sorters: {
			property: 'LpuSection_Name',
			direction: 'ASC'
		},
		mode: 'local'
	}),
	alias: 'widget.swTagLpuSection',
	fieldLabel: 'Вид документа',
	displayField: 'LpuSection_Display',
	typeAhead: true,
	valueField: 'LpuSection_id',
	selectOnFocus: true,
	enableKeyEvents: true,
	queryDelay: 300,
	queryMode: 'local',
	displayTpl: Ext6.create('Ext6.XTemplate',
		'<tpl for=".">',
		'{[values.LpuSection_id > -1 ? values.LpuSection_id : "empty"]}',
		'</tpl>'
	),
	initComponent: function ()
	{
		this.callParent(arguments);
	}
});

Ext6.define('swEMDPersonRole', {
	forceSelection: true,
	triggerAction: 'all',
	displayField: 'EMDPersonRole_Name',
	valueField: 'EMDPersonRole_id',
	extend: 'swBaseCombobox',
	alias: 'widget.swEMDPersonRole',
	queryMode: 'local',
	enableKeyEvents: true,
	fieldLabel: 'Роль при подписании',
	initComponent: function() {

		this.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'EMDPersonRole_id', mapping: 'EMDPersonRole_id', type: 'int' },
				{ name: 'EMDPersonRole_Name', mapping: 'EMDPersonRole_Name', type: 'string'}
			],
			autoLoad: false,
			sorters: {
				property: 'EMDPersonRole_Name',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=EMD&m=getPersonRoleList',
				reader: {
					type: 'json'
				}
			},
			mode: 'local'
		});

		this.callParent(arguments);
	}
});

Ext6.define('swHealthKindCombo', {
	extend: 'swCommonSprCombo',
	comboSubject: 'HealthKind',
	alias: 'widget.swHealthKindCombo',
	fieldLabel: 'Группа заболеваний',
	displayField: 'HealthKind_Name',
	valueField: 'HealthKind_id',
	displayCode: false,
	disabled: true,
	sortField: 'HealthKind_id'
});

Ext6.define('SwMesOldVizitCombo', {
	mode: 'remote',
	selectOnFocus: true,
	forceSelection: true,
	triggerAction: 'all',
	displayField: 'MesOldVizit_Name',
	valueField: 'MesOldVizit_id',
	extend: 'swBaseCombobox',
	alias: 'widget.SwMesOldVizitCombo',
	queryMode: 'local',
	enableKeyEvents: true,
	fieldLabel: langs('МЭС'),
	listeners : {
		'select'  : function(combo, record, index) {
			combo.setRawValue(record.get('MesOldVizit_Name'));
		},
		'keydown' : function(inp, e) {
			if ( e.getKey() == e.DELETE ||e.getKey() == e.BACKSPACE )
			{
				inp.setValue('');
				inp.setRawValue("");
				inp.selectIndex = -1;
				if (inp.onClearValue)
					this.onClearValue();
				e.stopEvent();
				return true;
			}

			if (e.getKey() == e.F4)
			{
				this.onTriggerClick();
			}
		}
	},

	beforeBlur: function() {
		// медитируем
		return true;
	},
	reload: function() {
		var combo = this;
		var MesOldVizit_id = combo.getValue();
		combo.clearValue();
		combo.lastQuery = '';
		combo.getStore().removeAll();
		combo.getStore().proxy.extraParams.query = '';

		combo.getStore().load({
			callback: function() {
				index = combo.getStore().findBy(function(rec) {
					return (rec.get('MesOldVizit_id') == MesOldVizit_id);
				});

				if ( index >= 0 ) {
					combo.setValue(MesOldVizit_id);
				}
			}
		});
	},
	setMesType_id:function(n){
		this.getStore().proxy.extraParams.MesType_id = n;
	},
	setUslugaComplex_id:function(n){
		this.getStore().proxy.extraParams.UslugaComplex_id = n;
	},
	setEvnDate: function(d){
		this.getStore().proxy.extraParams.EvnDate = d;
	},
	setMesCodeList:function(n){
		if (Ext6.isArray(n)) {
			this.getStore().proxy.extraParams.Mes_Codes = Ext.util.JSON.encode(n);
		} else {
			this.getStore().proxy.extraParams.Mes_Codes = null;
		}
	},
	setUslugaComplexPartitionCodeList:function(n){
		if (Ext6.isArray(n)) {
			this.getStore().proxy.extraParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode(n);
		} else {
			this.getStore().proxy.extraParams.UslugaComplexPartition_CodeList = null;
		}
	},
	clearBaseParams: function() {
		this.lastQuery = 'This query sample that is not will never appear';
		this.getStore().proxy.extraParams.UslugaComplex_id = null;
		this.getStore().proxy.extraParams.MesType_id = null;
		this.getStore().proxy.extraParams.Mes_Codes = null;
		this.getStore().proxy.extraParams.UslugaComplexPartition_CodeList = null;
	},
	initComponent: function() {
		var combo = this;
		this.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{ name: 'MesOldVizit_id', mapping: 'MesOldVizit_id', type: 'int'},
				{ name: 'MesOldVizit_Code', mapping: 'MesOldVizit_Code', type: 'int' },
				{ name: 'MesOldVizit_Name', mapping: 'MesOldVizit_Name', type: 'string' }
			],
			autoLoad: false,
			sorters: {
				property: 'MesOldVizit_Code',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=Mes&m=loadMesOldVizit',
				reader: {
					type: 'json'
				},
				extraParams: {
					UslugaComplex_id: null,
					MesType_id: null,
					Mes_Codes: null,
					UslugaComplexPartition_CodeList: null
				},
			},
			mode: 'remote',
			listeners: {
				load: function(store) {
					combo.setValue(combo.getValue());
				}
			}
		});

		this.callParent(arguments);
	}
});

Ext6.define('swPalliatPPSScaleCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.swPalliatPPSScaleCombo',
	triggerAction: 'all',
	mode: 'local',
	queryMode: 'local',
	fieldLabel: 'Шкала PPS',
	valueField: 'PalliatPPSScale_id',
	displayField: 'PalliatPPSScale_MoveAbility',
	codeField: 'PalliatPPSScale_Percent',

	displayTpl: new Ext6.XTemplate(
		'<tpl for="."><tpl if="PalliatPPSScale_id &gt; 0">',
		'{PalliatPPSScale_Percent}% {PalliatPPSScale_MoveAbility}',
		'</tpl></tpl>'
	),

	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item">',
		'<tpl if="PalliatPPSScale_id &gt; 0">',
		'<font color="red">{PalliatPPSScale_Percent}%</font>&nbsp;{PalliatPPSScale_MoveAbility}.',
		'&nbsp;{PalliatPPSScale_ActivityType}.',
		'&nbsp;{PalliatPPSScale_SelfCare}.',
		'&nbsp;{PalliatPPSScale_Diet}.',
		'&nbsp;{PalliatPPSScale_ConsiousLevel}.',
		'</tpl></div></tpl>'
	),

	triggers: {
		search: {
			cls: 'x6-form-search-trigger',
			handler: function(combo) {
				combo.openSearchWindow();
			}
		}
	},

	openSearchWindow: function() {
		var me = this;
		getWnd('swPalliatPPSScaleSelectWindow').show({
			callback: function(PalliatPPSScale_id) {
				if (PalliatPPSScale_id > 0) {
					me.setValue(PalliatPPSScale_id);
				}
			}
		});
	},

	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'PalliatPPSScale_id', type:'int'},
				{name: 'PalliatPPSScale_Percent', type: 'int'},
				{name: 'PalliatPPSScale_MoveAbility', type: 'string'},
				{name: 'PalliatPPSScale_ActivityType', type: 'string'},
				{name: 'PalliatPPSScale_SelfCase', type: 'string'},
				{name: 'PalliatPPSScale_Diet', type: 'string'},
				{name: 'PalliatPPSScale_ConsiousLevel', type: 'string'}
			],
			autoLoad: false,
			proxy: {
				type: 'ajax',
				url: '/?c=PalliatQuestion&m=loadPalliatPPSScale',
				reader: {type: 'json'}
			},
			mode: 'local'
		});

		me.callParent(arguments);
	}
});

Ext6.define('swPalliatPainScaleCombo', {
	extend: 'swBaseCombobox',
	alias: 'widget.swPalliatPainScaleCombo',
	triggerAction: 'all',
	mode: 'local',
	queryMode: 'local',
	fieldLabel: 'Болевой синдром по шкале боли',
	valueField: 'PalliatPainScale_id',
	displayField: 'PalliatPainScale_Characteristic',
	codeField: 'PalliatPainScale_PointCount',

	tpl: new Ext6.XTemplate(
		'<tpl for="."><div class="x6-boundlist-item">',
		'<tpl if="PalliatPainScale_id &gt; 0">',
		'<font color="red">{PalliatPainScale_PointCount}</font>&nbsp;{PalliatPainScale_Characteristic}',
		'</tpl></div></tpl>'
	),

	initComponent: function() {
		var me = this;

		me.store = Ext6.create('Ext6.data.Store', {
			fields: [
				{name: 'PalliatPainScale_id', type:'int'},
				{name: 'PalliatPainScale_PointCount', type: 'int'},
				{name: 'PalliatPainScale_Characteristic', type: 'string'}
			],
			autoLoad: false,
			proxy: {
				type: 'ajax',
				url: '/?c=PalliatQuestion&m=loadPalliatPainScale',
				reader: {type: 'json'}
			},
			mode: 'local'
		});

		me.callParent(arguments);
	}
});

Ext6.define('swExaminationPlaceCombo', {
	extend: 'swCommonSprCombo',
	comboSubject: 'ExaminationPlace',
	alias: 'widget.swExaminationPlaceCombo',
	fieldLabel: 'Место выполнения',
	displayField: 'ExaminationPlace_Name',
	valueField: 'ExaminationPlace_id',
	displayCode: false,
	sortField: 'ExaminationPlace_Code'
});

Ext6.define('swDopDispDiagTypeCombo', {
	extend: 'swCommonSprCombo',
	comboSubject: 'DopDispDiagType',
	alias: 'widget.swDopDispDiagTypeCombo',
	fieldLabel: 'Характер заболевания',
	displayField: 'DopDispDiagType_Name',
	valueField: 'DopDispDiagType_id',
	displayCode: false,
	sortField: 'DopDispDiagType_Code'
});

Ext6.define('swDeseaseStageCombo', {
	extend: 'swCommonSprCombo',
	comboSubject: 'DeseaseStage',
	alias: 'widget.swDeseaseStageCombo',
	fieldLabel: 'Стадия',
	displayField: 'DeseaseStage_Name',
	valueField: 'DeseaseStage_id',
	displayCode: false,
	sortField: 'DeseaseStage_Code'
});