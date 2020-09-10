/**
 *  Переопределения и добавления новых функций в базовые классы.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      lib
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */

Ext.override(Ext.Ajax, {
	localCmpMethods: [ // методы, которые необходимо дублировать в локальную БД
		'setStatusCmpCallCard',
		'saveCmpCallCard'
	],
    timeout: 600000,
	checkUrlToLocalCmp: function(url) {
		var me = this;

		if (new RegExp(me.localCmpMethods.join("|")).test(url)) {
			return true;
		}

		return false;
	},
	request: function(o) {
		var me = this;

		// если не пинг запрос
		if (!o.ignoreCheckConnection && !o.isPingRequest && !o.toLocalCMP) {
			if (sw.lostConnection) {
				// если соединение с основным сервером потеряно, то все запросы переадресуются на локальный веб
				if (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedService_IsLocalCMP && parseInt(sw.Promed.MedStaffFactByUser.last.MedService_IsLocalCMP) == 2) {
					var local_url = sw.Promed.MedStaffFactByUser.last.MedService_LocalCMPPath; // урл локального веба
					if (!o.url) {
						o.url = this.url;
					}
					o.url = local_url + o.url; // меняем на локальный УРЛ.
					o.toLocalCMPRedirect = true;
				}
			}

			o.ignoreCheckConnection = true;
			return me.request(o);
		} else {
			return me.callOverridden(arguments);
		}
	},
	onComplete: function(request, xdrResult) {
		var me = this;

		var options = request.options;
		if (!options.toLocalCMPRedirect) {
			// сохранилось. надо сохранить и в локальный промед #90822
			if (options.toLocalCMP) {
				// если уже сохраняем в локальный, то калбэк не нужен
				options.callback = Ext.emptyFn;
				options.failure = Ext.emptyFn;
				options.success = Ext.emptyFn;
			} else if (!options.toLocalCMP && sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedService_IsLocalCMP && parseInt(sw.Promed.MedStaffFactByUser.last.MedService_IsLocalCMP) == 2) {
				if (me.checkUrlToLocalCmp(options.url)) { // нас интересуют только определённые методы сохранения в локальную БД.
					log('toLocalCMP: ', options.url, options);
					var local_url = sw.Promed.MedStaffFactByUser.last.MedService_LocalCMPPath; // урл локального веба
					options.toLocalCMP = true; // признак, что отправляем на локальный веб
					options.url = local_url + options.url; // меняем на локальный УРЛ.

					var response = request.xhr;
					if (response.responseText && options.params) {
						var result = Ext.JSON.decode(response.responseText);
						if (result && result.CmpCallCard_id) {
							if (typeof options.params == 'object') {
								options.params.CmpCallCard_insID = result.CmpCallCard_id;
							} else {
								options.params = options.params + '&CmpCallCard_insID=' + result.CmpCallCard_id;
							}
						}
					}

					me.request(options); // запускаем запрос повторно
				}
			}
		}

		return me.callOverridden(arguments);
	}
});

Ext.override(Ext.view.Table, {
	focusRow: function(rowIdx) {
		var me = this,
			row,
			gridCollapsed = me.ownerCt && me.ownerCt.collapsed,
			record;


		if (me.isVisible(true) && !gridCollapsed && (row = me.getNode(rowIdx, true)) && me.el) {
			record = me.getRecord(row);
			rowIdx = me.indexInStore(row);


			me.selModel.setLastFocused(record);
			//row.focus(); // убрал, т.к. теряется фокус в формах, компонентах, после автоматического обновления сторе в гриде.
			me.focusedRow = row;
			me.fireEvent('rowfocus', record, row, rowIdx);
		}
	}
});

Ext.override(Ext.form.field.Checkbox, {
	initComponent: function() {
		var me = this,
			value = me.value;

		me.addEvents({
			'check' : true
		});

		if (value !== undefined) {
			me.checked = me.isChecked(value, me.inputValue);
		}

		me.callParent(arguments);
		me.getManager().add(me);
	},
	onBoxClick: function(e) {
		var me = this;

		//евент на пользовательский ввод
		me.fireEvent('check', this, !this.checked, this.checked);

		if (!me.disabled && !me.readOnly) {
			this.setValue(!this.checked);
		}
	},
})

Ext.override(Ext.grid.header.Container, {
	onMenuHide: function(menu) {
		var grid = this.up('grid');

		menu.activeHeader.setMenuActive(false);

		if(grid.keepState && getRegionNick().inlist(['astra'])){
			//@todo save state и зачем мне этот state
			var columns = grid.columns,
				gridState = grid.getState().columns,
				colsArray = [];

			columns.forEach(function(a,b){
				gridState[b].dataIndex = a.dataIndex;
				gridState[b].hidden = a.hidden;
			});

			Ext.Ajax.request({
				url: '/?c=Options&m=saveGridState',
				params: {
					gridState: Ext.JSON.encode(gridState),
					gridRefId: grid.refId
				},
				callback: function (opt, success, response) {
					if (success) {
					}
				}
			});
		};
	}
});

Ext.override(Ext.grid.Panel, {
	initComponent: function() {
		var me = this;

		if(me.keepState && getRegionNick().inlist(['astra'])){
			var columns = me.columns;

			Ext.Ajax.request({
				url: '/?c=Options&m=getGridState',
				params: {
					gridRefId: me.refId
				},
				callback: function (opt, success, response) {
					var resp = Ext.JSON.decode(response.responseText);
					if (resp.success && resp.gridState) {
						var gridState = Ext.JSON.decode(resp.gridState);

						columns.forEach(function(column,index){
							var stateColumn = gridState.find(function(a){
								return (a.dataIndex == column.dataIndex)
							});

							if(stateColumn && typeof stateColumn.hidden != 'undefined'){
								var headerCmp = me.down('gridcolumn[dataIndex='+ column.dataIndex +']');

								column.hidden = stateColumn.hidden;
							}
						});

						me.reconfigure(me.store, columns)

					}
				}
			});
		}

		me.callParent(arguments);
	}
});

/*
Ext.override(Ext.toolbar.Paging, {
	initComponent : function(){
		var me = this,
			pagingItems = me.getPagingItems(),
			userItems   = me.items || me.buttons || [];

		if (me.prependButtons) {
			me.items = userItems.concat(pagingItems);
		} else {
			me.items = pagingItems.concat(userItems);
		}
		delete me.buttons;

		if (me.displayInfo) {
			me.items.push('->');
			me.items.push({xtype: 'tbtext', itemId: 'displayItem'});
		}

		me.callParent();

		me.addEvents(
			'change',
			'beforechange'
		);
		me.on('beforerender', me.onLoad, me, {single: true});

		me.bindStore(me.store || 'ext-empty-store', true);

		me.store.on('filterchange', me.onLoad, me);
	},

	//@private
	getPageData: function () {
		var store = this.store,
			totalCount = store.isFiltered() ? store.getCount() : store.getTotalCount(); // условие для отображения отфильтрованых записей

		return {
			total: totalCount,
			currentPage: store.currentPage,
			pageCount: Math.ceil(totalCount / store.pageSize),
			fromRecord: ((store.currentPage - 1) * store.pageSize) + 1,
			toRecord: Math.min(store.currentPage * store.pageSize, totalCount)

		};
	}
});*/

Ext.override(Ext.form.Basic, {
	setValues: function(values) {
		var me = this,
			v, vLen, val, field;

		function setVal(fieldId, val) {
			var field = me.findField(fieldId);
			if (field) {
				if(field.store && field.store.getCount()==0){
					field.store.on('load', function(){
						field.setValue(val);
					});
				};

				if(field.xtype == 'DoubleValueTriggerField'){
					field.setValue(values[field.hiddenFieldName],val);
				}
				else{
					if(field.valueField)
					{
						val = parseInt(val);
						if (!val) {val=''}
					}
					field.setValue(val);
				}
				if (me.trackResetOnLoad) {
					field.resetOriginalValue();
				}
			}
		}

		Ext.suspendLayouts();
		if (Ext.isArray(values)) {
			vLen = values.length;
			for (v = 0; v < vLen; v++) {
				val = values[v];
				setVal(val.id, val.value);
			}
		} else {
			Ext.iterate(values, setVal);
		}
		Ext.resumeLayouts(true);
		return this;
	},
	getValues: function(asString, dirtyOnly, includeEmptyText, useDataValues, isSubmitting) {
		var values  = {},
			fields  = this.getFields().items,
			fLen    = fields.length,
			isArray = Ext.isArray,
			field, data, val, bucket, name, f;

		for (f = 0; f < fLen; f++) {

			field = fields[f];

			if ((!dirtyOnly || field.isDirty())) {
				data = field[useDataValues ? 'getModelData' : 'getSubmitData'](includeEmptyText, isSubmitting);

				if (Ext.isObject(data)) {
					for (name in data) {
						if (data.hasOwnProperty(name)) {
							val = data[name];

							if (includeEmptyText && val === '') {
								val = field.emptyText || '';
							}

							if (!field.isRadio) {
								if (values.hasOwnProperty(name)) {
									bucket = values[name];

									if (!isArray(bucket)) {
										bucket = values[name] = [bucket];
									}

									if (isArray(val)) {
										values[name] = bucket.concat(val);
									} else {
										bucket.push(val);
									}
								} else {
									values[name] = val;
									if(field.xtype == 'DoubleValueTriggerField'){
										values[name] = field.hiddenValue?field.hiddenValue:field.rawToValue(field.processRawValue(field.getRawValue()));
									}
								}
							} else {
								values[name] = values[name] || val;
							}
						}
					}
				}
			}
		}

		if (asString) {
			values = Ext.Object.toQueryString(values);
		}
		return values;
	},
	getAllFields: function(){

		var fieldsArr = Ext.ComponentQuery.query('field', this.owner),
			fieldsObj = {};
		
		for (i = 0; i < fieldsArr.length; i++) {
			fieldsObj[fieldsArr[i].getName()] = fieldsArr[i];
		}

		return fieldsObj;
	},
	getAllValues: function () {
		var fieldsArr = Ext.ComponentQuery.query('field', this.owner),
			values = {};

		for (i = 0; i < fieldsArr.length; i++) {
			if (fieldsArr[i].getValue() instanceof Date){
				values[fieldsArr[i].getName()] = Ext.Date.format(fieldsArr[i].getValue(), 'Y-m-d H:i:s');
			} else values[fieldsArr[i].getName()] = fieldsArr[i].getValue();
		}

		return values;
	}
});

Ext.override(Ext.form.Panel, {
	initComponent : function(){
		var me = this;

		me.on('show', function (form) {
			me.checkFormAutoMask(form);
		});

		me.on('render', function (form) {
			var	allCmps = Ext.ComponentQuery.query('{store}', form),
				countMongoCombo = 0,
				res = {};
			Ext.suspendLayouts();

			var loadAllStores = function(allCmps){

				for(var n in allCmps) {
					var o = allCmps[n];
					//здесь надо развести 2 типа комбиков - монго и обычные
					if (o.store) {

						switch(o.initialCls) {

							case 'localCombo': {
								//обычный комбик - загружайся
								o.getStore().load();
								break;
							};

							case 'localComboMongo': {
								Ext.data.Store.prototype.clearFilter.call(o.store);
								//не надо грузить localComboMongo store множество раз
								//console.log('store count - ', o.store.count())
								if(!o.store.count()) {
									countMongoCombo++;
									//собираем поля
									var ffields = {},
										cfields = o.store.getProxy().getReader().getFields();

									Ext.Object.each(cfields, function(key, value, myself) {
										if (value.type.type != 'auto') {
											var nn = value.name
											ffields[nn] = ""
										}
									})
									ffields.object = o.tableName;

									//собираем urls
									var curl = o.store.url,
										//собираем таблицы
										ctable = o.tableName;

									//собираем в параметры
									res[ctable] = {
										url: o.store.url,
										params : (o.params || null),
										baseparams : ffields
									}
								}
								break;
							};
							case 'transFieldDelbut': {
								//хитрый комбик - вроде как комбик но с классом инпутавтокомплита
								//зачем? затем, чтобы использовать загруженный сторе
								//здесь для того чтобы отслеживать загрузку прилинкованного стора
								o.store = Ext.data.StoreManager.lookup(o.storeName);
								break;
							};
						}

					}
					else {
						if (o.initialCls == 'transFieldDelbut') {
							//загружаем поля-автокомплиты
							if (!o.store && o.storeName) {
								o.store = Ext.data.StoreManager.lookup(o.storeName);
							}
						}
					}
				};

				if (countMongoCombo) {
					Ext.Ajax.request({
						url: '/?c=MongoDBWork&m=getDataAll',
						callback: function(opt, success, response) {
							if (success){
								var response_obj = Ext.JSON.decode(response.responseText);
								//заполняем комбики монго

								for(var n in allCmps){
									var o = allCmps[n];
									if (o.initialCls == 'localComboMongo') {
										o.store.loadData(response_obj[o.tableName], true);
										o.store.commitChanges();
										o.store.fireEvent('load');
									}
								};
							};
						},
						params: {
							'data': Ext.JSON.encode(res)
						}
					})
				}
			};

			//для загрузки компонентов внутри грида с фильтрами
			var grids = me.down('grid'),
				isGridWithFilters = (grids && grids.headerFilterPlugin);

			if(isGridWithFilters){
				grids.on('headerfiltersrender',
					function(grid, fields, parseFields){
						loadAllStores(fields);
					}
				);

			};

			loadAllStores(allCmps);

			Ext.resumeLayouts(true);

			form.isFormComponentsLoaded = false; // Свойство, которое показывает, все ли сторы формы загружены
			form.countOfLoadedStores = 0; // Количество загруженных сторов
			form.countFormStores = allCmps.length; // Количество сторов формы.

			var onStoreLoad = function (form) {
				form.countOfLoadedStores++;
				if (form.countOfLoadedStores == form.countFormStores) { // Если после загрузки стора, количество всех сторов, стало равно загруженным
					form.isFormComponentsLoaded = true;
					//console.log('FORM: ' + form.id + ' HAS ' + form.countFormStores + ' STORES | LOADED ' + form.countOfLoadedStores);
					form.fireEvent('formLoaded', form);
				}
			}

			for(var i in allCmps) {
				var o = allCmps[i];
				if(o.store) {
					// Считаем кол-во сторов.
					if (o.initialCls != 'localCombo' || o.autoLoad == false || o.hidden == true ) { //Считаем только загружаемые комбики. localCombo - обычний комбо, загружается при загрузке
						onStoreLoad(form);
						continue;
					}
					if(o.bigStore) {
						o.bigStore.on('load', function(store, records, success, operation){
							onStoreLoad(form);
						});
					}
					else {
						o.store.on('load', function(store, records, success, operation){
							onStoreLoad(form);
						});
					}
				}
			}
		});

		me.addEvents('formLoaded');

		me.on('formLoaded', function (form) {
			form.isFormComponentsLoaded = true;
            if(form.mask && form.autoMask == true) return form.mask.hide();
		});

		me.callParent(arguments);
	},

	checkFormAutoMask: function(form) {
        if(form.autoMask == true && form.countFormStores > 0 && !form.isFormComponentsLoaded) { // Если у формы включена автомаска
			form.mask = new Ext.LoadMask(form.up('window'), {msg: "Пожалуйста, подождите..."});
			if(form.mask) {
				form.mask.show();
				setTimeout(function(){ // Для форс-мажора
					form.mask.destroy();
				}, 10000)
			}
		}
	}
});