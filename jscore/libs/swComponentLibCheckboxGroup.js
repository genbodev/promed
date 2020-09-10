/**
* Группа чекбоксов для выбора одного или нескольких значений
*/
sw.Promed.swCheckboxGroup = Ext.extend(Ext.form.CheckboxGroup,
{
	name: '',
	itemName: '',
	itemsData: [],
	value: '',// строка с id разделенными запятой
	fieldLabel: '',
	hideLabel: true,
	columns: 1,
	getRawValue: function() {
		var out = [];
		this.items.each(function(item){
			if(item.checked){
				out.push(item.boxLabel);
			}
		});
		return out.join(', ');
	},
	getValue: function() {
		var out = [];
		this.items.each(function(item){
			if(item.checked){
				out.push(item.value);
			}
		});
		this.value = out.join(',');
		return this.value;
	},
	setValue: function(value) {
		if(typeof value != 'string')
			value = '';
		else{
			var id_list = value.split(',') || [];
			this.items.each(function(item){
				item.setValue(item.value.toString().inlist(id_list));
			});
		}
		this.value = value;
	},
	initComponent: function(){
		this.itemsData = Ext.isArray(this.itemsData) ? this.itemsData : [];
		this.items = [];
		var toggleValue = function() {
			this.setValue(!this.checked);
			this.ownerCmp.value = this.ownerCmp.getValue();
		};
		var value_id_list = this.value.toString().split(',') || [];
		for(var i=0; i < this.itemsData.length; i++)
		{
			var value = this.itemsData[i][0];
			var item_config = {boxLabel: this.itemsData[i][1], value: value, name: this.itemName, checked: (value.toString().inlist(value_id_list)), id: this.id +'_'+ value};
			item_config.ownerCmp = this;
			item_config.toggleValue = (typeof this.toggleValue == 'function')?this.toggleValue:toggleValue;
			this.items.push(new Ext.form.Checkbox(item_config));
		}
		sw.Promed.swCheckboxGroup.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swcheckboxgroup', sw.Promed.swCheckboxGroup);

/**
 * Группа чекбоксов с формированием из стора
 */
sw.Promed.SwCustomObjectCheckBoxGroup = Ext.extend(Ext.form.CheckboxGroup, {
    store: null,
    tableSubject: '',
    idField: '',
    codeField: '',
    displayField: '',
    prefix: '',
    singleValue: false,
    vertical: true,
    columns: 1,
    width: '100%',
    initComponent: function(a,b,c) {
        var checkGroup = this;

        checkGroup.idField = checkGroup.tableSubject + '_id';
        checkGroup.codeField = checkGroup.tableSubject + '_Code';
        checkGroup.displayField = checkGroup.tableSubject + '_Name';

        checkGroup.sortField = checkGroup.codeField;

        checkGroup.store = new Ext.db.AdapterStore({
            autoLoad: true,
            dbFile: 'Promed.db',
            fields: [
                {name: checkGroup.idField, mapping: checkGroup.idField},
                {name: checkGroup.codeField, mapping: checkGroup.codeField},
                {name: checkGroup.displayField, mapping: checkGroup.displayField}
            ],
            key: checkGroup.idField,
            sortInfo: {field: checkGroup.sortField},
            tableName: checkGroup.prefix+checkGroup.tableSubject,
            listeners: {
                'load': function(store) {
					
					checkGroup.panel.items.items[0].removeAll();

                    store.each(function(rec, b, c){

						var check = new Ext.form.Checkbox({
                            boxLabel: rec.get(checkGroup.displayField),
							readOnly: (checkGroup.tableSubject == 'CmpReasonNew' && rec.get(checkGroup.idField) == 306 && getRegionNick().inlist(['astra'])), //Флаг «Другое» в группе флагов «Причина обращения» недоступен для выбора.
                            //name: checkGroup.idField,
                            value: checkGroup.tableSubject == 'TechnicInstrumRehab' ? rec.get(checkGroup.codeField) :rec.get(checkGroup.idField),
							xtype: 'checkbox',
							listeners: {
								change: function(e){
									if (checkGroup.tableSubject == 'TechnicInstrumRehab') checkGroup.fireEvent('change', check);
								}
							}
                        });

                        if(checkGroup.singleValue) {
                            check.on('check', checkGroup.fireSingleChecked, checkGroup);
                        }

                        checkGroup.items.add(check);
                        checkGroup.panel.items.items[0].add(check);

                    });

                    checkGroup.panel.doLayout();

                }
            }
        });

        this.items = [{}];

        sw.Promed.SwCustomObjectCheckBoxGroup.superclass.initComponent.apply(this, arguments);

    },

    //честно стырено
    getValue: function() {
        var out = [];
        this.items.each(function(item){
            if(item.checked){
                out.push(item.value);
            }
        });
        this.value = out.join(',');
        return this.value;
    },

    setValue: function(value) {
        if(!value) return;
        var id_list = value.split(',') || [];
        this.items.each(function(item){
            if(item.value)
                item.setValue(item.value.toString().inlist(id_list));
        });
    },
});
Ext.reg('swcustomobjectcheckboxgroup', sw.Promed.SwCustomObjectCheckBoxGroup);


/**
* Группа чекбоксов для выбора одного или нескольких значений кода контингента ВИЧ-инфицированных
*/
sw.Promed.swHIVContingentTypeCheckboxGroup = Ext.extend(sw.Promed.swCheckboxGroup,
{
	name: 'HIVContingentType_id_list',
	field_name: 'HIVContingentType_id_list',
	itemName: 'HIVContingentType_id',
	hideLabel: true,
	columns: 1,
	baseParams: {},
	url: '/?c=MorbusHIV&m=getHIVContingentType',
	defaultItems: [
		new Ext.form.Checkbox(
			{
				xtype: 'checkbox',
				boxLabel: 'No Items',
				ctCls: 'auto-height-checkbox',
				disabled: true
			})],
	reader: new Ext.data.JsonReader(
	{
		totalProperty: 'totalCount',
		root: 'data',
		fields: [{name: 'HIVContingentType_id'}, {name: 'HIVContingentType_Name'}, {checked: 'Checked'}]
	}),
	items:[{boxLabel:'Loading'},{boxLabel:'Loading'}],

	onRender: function ()
	{
		var comp = this;
		Ext.ux.RemoteCheckboxGroup.superclass.onRender.apply(this, arguments);
		if (this.showMask)
		{
			this.loadmask = new Ext.LoadMask(this.ownerCt.getEl(), {
				msg: "Loading..."
			});
		}
		this.reload(comp);
	},
	getGroupValue: function ()
	{
		var valuesArray = [];
		for (var j = 0; j < this.columns; j++)
		{
			if (this.panel.getComponent(j).items.length > 0)
			{
				this.panel.getComponent(j).items.each(
					function (i)
					{
						if (i.checked)
						{
							valuesArray.push(i.inputValue);
						}
					});
			}
		}
		return valuesArray;
	},
	getValues: function() {
		var valuesArray = [];
		for (var i = 0; i < this.items.length; i++) {
			if (this.items[i].checked) {
				valuesArray.push(this.items[i].inputValue);
			}
		}
		return valuesArray;
	},
	setValues: function(data) {
		for (var i = 0; i < this.items.length; i++) {
			if (typeof data[this.items[i].inputValue] !== 'undefined' ) {
				this.items[i].setValue(data[this.items[i].inputValue]);
			}
		}
	},
	clearValues: function() {
		for (var i = 0; i < this.items.length; i++) {
			this.items[i].setValue(false);
		}
	},
	reload: function (comp)
	{
		//var comp = this;
		if ((this.url != '') && (this.reader != null))
		{
			this.removeAll();
			if (this.showMask)
			{
				this.loadmask.show();
			}

			var handleCB = function (responseObj, options)
			{

				var toggleValue = function() {
					this.setValue(!this.checked);
					this.ownerCmp.value = this.ownerCmp.getValue();
				};
				var response = Ext.decode(responseObj.responseText);
				if (!(Ext.isEmpty(response)))
				{
					var record, item;
					for (var i=0;i<response.data.length;i++)
					{
						record = response.data[i];
						item = new Ext.form.Checkbox(
							{
								xtype: 'checkbox',
								boxLabel: record.HIVContingentType_Name.toString(),
								inputValue: record.HIVContingentType_id.toString(),
								value: record.HIVContingentType_id.toString(),
								checked: (record.CHECKED.toString() == 'true'),
								name: comp.itemName,
								ownerCmp: comp,
								toggleValue: ((typeof this.toggleValue == 'function')?this.toggleValue:toggleValue),
								id: comp.id +'_'+ record.HIVContingentType_id.toString(),
								ctCls: 'auto-height-checkbox'
							});
						if (this.fieldName != '')
							item.name = 'HIVContingentType_id';

						item.on('check', function(){
							var arr = [];
							this.items.each(function(item){
								if(item.checked){
									arr.push(item);
								}
							});
							this.fireEvent('change', this, arr);
						}, this);

						if (this.fireEvent('beforeadd', this, item) !== false)
						{
							var items = this.items;
							var columns = this.panel.items;
							var column = columns.itemAt(items.getCount() % columns.getCount());
							var chk = column.add(item);
							items.add(item);
							items[i] = chk;
							column.doLayout();
							this.fireEvent('add', this, item);
						}
					}
					this.fireEvent('load', this);
				}
				if (this.showMask)
				{
					this.loadmask.hide();
				}
			}
		}

		var fail = function ()
		{
			console.log("fail");
		};

		Ext.Ajax.request(
			{
				method: 'POST',
				url: '/?c=MorbusHIV&m=getHIVContingentType',
				params: this.baseParams,
				success: handleCB,
				failure: handleCB,
				callback: handleCB,
				scope: this
			});
	},
	removeAll: function ()
	{

		if(this.baseParams.End_value == 0)
		{
			this.value = 0;
		}
		for (var j = 0; j < this.columns.length; j++)
		{
			if (this.panel.items.length > 0)
			{
				var items = this.items;
				var columns = this.panel.items;
				var column = columns.itemAt(items.getCount() % columns.getCount());
				column.items.each(
					function (i)
					{
						if (this.fireEvent('beforeremove', this, i) !== false)
						{
							var items = this.items;
							var columns = this.panel.items;
							var column = columns.itemAt(items.getCount() % columns.getCount());
							var chk = column.remove(i);
							items.remove(i);
						}
					}, this);
			}
		}
	},


	initComponent: function() {
		this.itemsData = [[1,'Выделить всё']];

		this.addEvents(
			/**
			 * @event add
			 * Fires when a checkbox is added to the group
			 * @param {Ext.form.CheckboxGroup} this
			 * @param {object} chk The checkbox that was added.
			 */
			'add',
			/**
			 * @event beforeadd
			 * Fires before a checkbox is added to the group
			 * @param {Ext.form.CheckboxGroup} this
			 * @param {object} chk The checkbox to be added.
			 */
			'beforeadd',
			/**
			 * @event load
			 * Fires when a the group has finished loading (adding) new records
			 * @param {Ext.form.CheckboxGroup} this
			 */
			'load',
			/**
			 * @event beforeremove
			 * Fires before a checkbox is removed from the group
			 * @param {Ext.form.CheckboxGroup} this
			 * @param {object} chk The checkbox to be removed.
			 */
			'beforeremove');
		sw.Promed.swHIVContingentTypeCheckboxGroup.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swhivcontingenttypecheckboxgroup', sw.Promed.swHIVContingentTypeCheckboxGroup);


/**
 * Группа чекбоксов для выбора одного или нескольких значений Поверхность зуба
 */
sw.Promed.swToothSurfaceTypeCheckboxGroup = Ext.extend(sw.Promed.swCheckboxGroup,
{
	name: 'ToothSurfaceType_id_list',
	itemName: 'ToothSurfaceType_id',
	fieldLabel: langs('Поверхность зуба'),
	hideLabel: false,
    style: "white-space: nowrap;",
	columns: 1,
	initComponent: function() {
        this.itemsData = [
            [1,langs('Вестибулярная')]
            ,[2,langs('Медиальная')]
            ,[3,langs('Язычная')]
            ,[4,langs('Дистальная')]
            ,[5,langs('Окклюзионная')]
        ];
		sw.Promed.swToothSurfaceTypeCheckboxGroup.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('swtoothsurfacetypecheckboxgroup', sw.Promed.swToothSurfaceTypeCheckboxGroup);

/**
 * Чекбокс работающий со значениями справочника YesNo
 */
sw.Promed.swCheckbox = Ext.extend(Ext.form.Checkbox, {
	initComponent: function() {
		sw.Promed.swCheckbox.superclass.initComponent.apply(this, arguments);
	},
    setValue : function(v) {
        var checked = this.checked;
        this.checked = (v === true || v === 'true' || v == '2' || String(v).toLowerCase() == 'on');

        if(this.rendered){
            this.el.dom.checked = this.checked;
            this.el.dom.defaultChecked = this.checked;
            this.wrap[this.checked? 'addClass' : 'removeClass'](this.checkedCls);
        }

        if(checked != this.checked){
            this.fireEvent("check", this, this.checked);
            if(this.handler){
                this.handler.call(this.scope || this, this, this.checked);
            }
        }
    }
});
Ext.reg('swcheckbox', sw.Promed.swCheckbox);

/**
 * Чекбокс работающий со значениями справочника YesNo для первоначальной причины в свидетельстах о смерти
 */
sw.Promed.swDSCheckbox = Ext.extend(Ext.form.Checkbox, {
    initComponent: function() {
        sw.Promed.swDSCheckbox.superclass.initComponent.apply(this, arguments);
    },
    setValue : function(v) {
        var checked = this.checked;
        this.checked = (v === true || v === 'true' || v == '2' || String(v).toLowerCase() == 'on');

        if(this.rendered){
            this.el.dom.checked = this.checked;
            this.el.dom.defaultChecked = this.checked;
            this.wrap[this.checked? 'addClass' : 'removeClass'](this.checkedCls);
        }

        if(checked != this.checked){
            if(this.handler){
                this.handler.call(this.scope || this, this, this.checked);
            }
        }
    }
});
Ext.reg('swdscheckbox', sw.Promed.swDSCheckbox);

Ext.namespace("Ext.ux");
Ext.ux.RemoteCheckboxGroup = Ext.extend(Ext.form.CheckboxGroup, {
    baseParams: null,
    url: '',
    defaultItems: [
    new Ext.form.Checkbox(
    {
        xtype: 'checkbox',
        boxLabel: 'No Items',
        disabled: true
    })],
    fieldId: 'id',
    fieldName: 'name',
    fieldLabel: 'Checkbox group',
	boxLabel: 'boxLabel',
    fieldValue: 'inputValue',
    fieldChecked: 'checked',
    reader: null,
	singleValue: false,

    //private
    initComponent: function ()
    {

        this.addEvents(
        /**
         * @event add
         * Fires when a checkbox is added to the group
         * @param {Ext.form.CheckboxGroup} this
         * @param {object} chk The checkbox that was added.
         */
        'add',
        /**
         * @event beforeadd
         * Fires before a checkbox is added to the group
         * @param {Ext.form.CheckboxGroup} this
         * @param {object} chk The checkbox to be added.
         */
        'beforeadd',
        /**
         * @event load
         * Fires when a the group has finished loading (adding) new records
         * @param {Ext.form.CheckboxGroup} this
         */
        'load',
        /**
         * @event beforeremove
         * Fires before a checkbox is removed from the group
         * @param {Ext.form.CheckboxGroup} this
         * @param {object} chk The checkbox to be removed.
         */
        'beforeremove');

        Ext.ux.RemoteCheckboxGroup.superclass.initComponent.apply(this, arguments);
    },

    onRender: function ()
    {
        Ext.ux.RemoteCheckboxGroup.superclass.onRender.apply(this, arguments);
        if (this.showMask)
        {
            this.loadmask = new Ext.LoadMask(this.ownerCt.getEl(), {
                msg: "Loading..."
            });
        }
        this.reload();
    },

    reload: function ()
    {
        if ((this.url != '') && (this.reader != null))
        {
            this.removeAll(); 
            if (this.showMask)
            {
                this.loadmask.show();
            }
           
            var handleCB = function (responseObj, options)
            {
                var response = Ext.decode(responseObj.responseText);
                if (!(response.Error_Msg && response.Error_Msg != ''))
                {
                    var data = this.reader.readRecords(Ext.decode(responseObj.responseText));
                    for (var i = 0; i < data.records.length; i++)
                    {
                        var record = data.records[i];
                        var item = new Ext.form.Checkbox(
                        {
                            xtype: 'checkbox',
                            listeners: {
                                'render': this.cbRenderer
                            },
                            boxLabel: record.get(this.boxLabel),
                            inputValue: record.get(this.fieldValue)
                        });

                        if (this.fieldId != '')
                        {
                            item.id = record.get(this.fieldId);
                        }

                        if (this.fieldName != '')
                        {
                            item.name = record.get(this.fieldName);
                        }

                        if (this.fieldChecked != '')
                        {
                            item.checked = record.get(this.fieldChecked);
                        }

                        if (this.fieldCode != '')
                        {
                            item.code = record.get(this.fieldCode);
                        }

                        if (record.get('disabled'))
                        {
                            item.disabled = true;
                        }

						if (this.singleValue) {
							item.on('check', this.fireSingleChecked, this);
						}

                        item.on('check', this.cbHandler, this.cbHandlerScope ? this.cbHandlerScope : this, {buffer: 10});
                        if (this.fireEvent('beforeadd', this, item) !== false)
                        {
                            var items = this.items;
                            var columns = this.panel.items;
                            var column = columns.itemAt(items.getCount() % columns.getCount());
                            var chk = column.add(item);
                            items.add(item);
                            items[i] = chk;
                            column.doLayout();
                            
                            this.fireEvent('add', this, item);
                        }
                    }

                    this.fireEvent('load', this);
                }
                if (this.showMask)
                {
                    this.loadmask.hide();
                }
            }

        }
        
        var fail = function ()
        {
            console.log("fail");
        };

        Ext.Ajax.request(
        {
            method: 'POST',
            url: this.url,
            params: this.baseParams,
            success: handleCB,
            failure: fail,
            scope: this
        });
    },
    removeAll: function ()
    {
        for (var j = 0; j < this.columns.length; j++)
        {
           if (this.panel.items.length > 0)
            {
                var items = this.items;
                var columns = this.panel.items;
                var column = columns.itemAt(items.getCount() % columns.getCount());
                                
                column.items.each(
                
                function (i)
                {
                    if (this.fireEvent('beforeremove', this, i) !== false)
                    {
                        var items = this.items;
                        var columns = this.panel.items;
                        var column = columns.itemAt(items.getCount() % columns.getCount());
                        
                        var chk = column.remove(i);
                        items.remove(i);
                    }
                }, this);
            }
        }
    },
    getGroupValue: function ()
    {
        var valuesArray = [];
        for (var j = 0; j < this.columns; j++)
        {
            if (this.panel.getComponent(j).items.length > 0)
            {
                this.panel.getComponent(j).items.each(

                function (i)
                {
                    if (i.checked)
                    {
                        valuesArray.push(i.inputValue);
                    }
                });
            }
        }
        return valuesArray;
    },
	getValues: function() {
		var valuesArray = [];
		for (var i = 0; i < this.items.length; i++) {
			if (this.items[i].checked) {
				valuesArray.push(this.items[i].inputValue);
			}
		}
		return valuesArray;
	},
	getValue: function(){
		var values = this.getValues();

		if(values[0]) return values[0];
		else return null;
	},
	getCodeValues: function() {
		var codeArray = [];
		for (var i = 0; i < this.items.length; i++) {
			if (this.items[i].checked) {
				codeArray.push(this.items[i].code);
			}
		}
		return codeArray;
	},
	setValues: function(data) {
		for (var i = 0; i < this.items.length; i++) {
			if (typeof data[this.items[i].inputValue] !== 'undefined' ) {
				this.items[i].setValue(data[this.items[i].inputValue]);
			}
		}
	},
	clearValues: function() {
		for (var i = 0; i < this.items.length; i++) {
			this.items[i].setValue(false);
		}
	}

});
Ext.reg("remotecheckboxgroup", Ext.ux.RemoteCheckboxGroup);

Ext.ux.UslugaExecutionTypeRadioGroup = Ext.extend(Ext.form.RadioGroup, {

	name: 'UslugaExecutionType_id',
	url: '/?c=EvnUsluga&m=getUslugaExecutionTypeList',
	items: [{
		boxLabel: 'No Items',
		value: '',
		disabled: true
	}],
	isLoaded: false,
	value: '',
	onRender: function(){
		Ext.ux.UslugaExecutionTypeRadioGroup.superclass.onRender.apply(this, arguments);
		this.loadRadioItems();
	},
	loadRadioItems: function(){
		var me = this,
			items = [];

		if (me.url != '') {
			Ext.Ajax.request(
				{
					method: 'POST',
					url: me.url,
					callback: function(cmp,s,responseObj){
						var response = Ext.decode(responseObj.responseText);

						if(response && response.length > 0){
							me.removeAll();

							for(var i=0; i < response.length; i++){
								var radio = new Ext.form.Radio({
									boxLabel: response[i].UslugaExecutionType_Name,
									name: me.name + '_item',
									width: 200,
									value: response[i].UslugaExecutionType_id
								})
								radio.on('check', me.fireChecked, this);
								var items = me.items;
								var columns = me.panel.items;
								var column = columns.itemAt(items.getCount() % columns.getCount());
								var chk = column.add(radio);
								items.add(radio);
								items[i] = chk;
								column.doLayout();
							}
							me.isLoaded = true;
							if(me.value){
								me.setValue(me.value)
							}

						}
					},
					scope: me
				});
		}
	},
	removeAll: function ()
	{
		var  me = this;

		for (var j = 0; j < me.columns.length; j++)
		{
			if (me.panel.items.length > 0)
			{
				var items = me.items;

				items.items.forEach(function(i){
					var items = this.items;
					var columns = this.panel.items;
					var column = columns.itemAt(items.getCount() % columns.getCount());
					var chk = column.remove(i);
					items.remove(i);
					column.doLayout();
				}, me);
			}
		}
	},
	getValue: function(){
		var valuesArray = [];
		for (var i = 0; i < this.items.length; i++) {
			if (this.items[i].checked) {
				valuesArray.push(this.items[i].value);
			}
		}
		return valuesArray.length > 0 ? valuesArray[0] : null;

	},
	setValue: function(value) {
		if(this.isLoaded){
			for (var i = 0; i < this.items.length; i++) {
				if (this.items[i].value == value ) {
					this.items[i].setValue(true);
				}
			}
		}else{
			this.value = value;
		}

	}
});
Ext.reg("uslugaexecutiontyperadiogroup", Ext.ux.UslugaExecutionTypeRadioGroup);
