/**
* swLpuComboboxes - классы ниспадающих списков выбора.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      libs
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      11.01.2010
*/

sw.Promed.SwLpuListsBaseCombo = Ext.extend(Ext.form.ComboBox, {
	beforeBlur: function() {
		// медитируем
		return true;
	},
	baseFilterFn: null,
	baseFilterScope: null,
	baseFilterFnAdvanced: null,
	baseFilterScopeAdvanced: null,
	clearBaseFilter: function() {
		this.baseFilterFn = null;
		this.baseFilterScope = null;
	},
	clearBaseFilterAdvanced: function() {
		this.baseFilterFnAdvanced = null;
		this.baseFilterScopeAdvanced = null;
	},
	doQuery: function(q, forceAll) {
		if ( q === undefined || q === null ) {
			q = '';
		}

		var qe = {
			query: q,
			forceAll: forceAll,
			combo: this,
			cancel: false
		};

		if ( this.fireEvent('beforequery', qe) === false || qe.cancel ) {
			return false;
		}

		q = qe.query;
		forceAll = qe.forceAll;

		if ( q.length >= this.minChars ) {
			if ( this.lastQuery != q ) {
				this.lastQuery = q;
				this.selectedIndex = -1;

				this.getStore().filterBy(function(record, id) {
					var result = true;

					if ( result && this.baseFilterFn != null ) {
						result = this.baseFilterFn.call(this.baseFilterScope, record, id);
					}

					if ( result && this.baseFilterFnAdvanced != null ) {
						result = this.baseFilterFnAdvanced.call(this.baseFilterScopeAdvanced, record, id);
					}

					if ( result ) {
						if ( !Ext.isEmpty(record.get(this.displayField)) ) {
							var patt = new RegExp('^' + q.toLowerCase());
							result = patt.test(record.get(this.displayField).toLowerCase());

							if ( !result && record.get(this.codeField) ) {
								result = patt.test(record.get(this.codeField).toLowerCase());
							}
						}
						else {
							result = false;
						}
					}

					return result;
				}, this);

				this.onLoad();
			}
			else {
				this.selectedIndex = -1;
				this.onLoad();
			}
		}
	},
	enableKeyEvents: true,
	focusOnShiftTab: null,
	focusOnTab: null,
	linkedElementsDisabled: false,
	disableLinkedElements: function() {
		this.linkedElementsDisabled = true;
	},
	enableLinkedElements: function() {
		this.linkedElementsDisabled = false;
	},
	parentElementDisabled: false,
	disableParentElement: function() {
		this.parentElementDisabled = true;
	},
	enableParentElement: function() {
		this.parentElementDisabled = false;
	},
	globalFilterFn: null,
	linkedElements: new Array(),
	minChars: 1,
	minLength: 1,
	mode: 'local',
	parentElementId: null,
	resizable: true,
	selectOnFocus: true,
	filterStoreWithBaseFilters: function() {
		this.getStore().filterBy(function(record, id) {
			var result = true;

			if (this.ignoreFilter)
				return true; //#134721 вывод всех врачей для пензы

			if ( result && this.baseFilterFn != null ) {
				result = this.baseFilterFn.call(this.baseFilterScope, record, id);
			}

			if ( result && this.baseFilterFnAdvanced != null ) {
				result = this.baseFilterFnAdvanced.call(this.baseFilterScopeAdvanced, record, id);
			}

			return result;
		}, this);
	},
	setBaseFilter: function(fn, scope) {
		this.baseFilterFn = fn;
		this.baseFilterScope = scope || this;
		this.filterStoreWithBaseFilters();
	},
	setBaseFilterAdvanced: function(fn, scope) {
		this.baseFilterFnAdvanced = fn;
		this.baseFilterScopeAdvanced = scope || this;
		this.filterStoreWithBaseFilters();
	},
	setParentElementValue: function() {
		if ( !this.parentElementId || this.parentElementDisabled == true ) {
			return false;
		}

		var parentElement = Ext.getCmp(this.parentElementId);

		if ( !parentElement ) {
			return false;
		}

		var
			index,
			masterRecord,
			parentElementValue = parentElement.getValue(),
			slaveRecord;

		index = this.getStore().findBy(function(rec) {
			return (rec.get(this.valueField) == this.getValue());
		}.createDelegate(this));
		slaveRecord = this.getStore().getAt(index);

		if ( typeof slaveRecord != 'object' ) {
			return false;
		}

		index = parentElement.getStore().findBy(function(rec) {
			return (rec.get(parentElement.valueField) == parentElementValue);
		}.createDelegate(this));
		masterRecord = parentElement.getStore().getAt(index);

		if (
			parentElementValue != slaveRecord.get(parentElement.valueField)
			&& (
				typeof parentElement.linkedElementParams != 'object'
				|| typeof parentElement.linkedElementParams.additionalFilterFn != 'function'
				|| (
					parentElement.linkedElementParams.additionalFilterFn(masterRecord, slaveRecord) == false
					&& typeof parentElement.linkedElementParams.ignoreFilter == false
				)
			)
		) {
			if ( typeof masterRecord != 'object' || masterRecord.get(parentElement.valueFieldAdd) != slaveRecord.get(parentElement.valueField) ) {
				if (slaveRecord.get(parentElement.valueField)) {
					parentElement.setValue(slaveRecord.get(parentElement.valueField));
				} else {
					parentElement.clearValue();
				}
				//parentElement - компонент Extjs, у него нет метода setParentElementValue
				//parentElement.setParentElementValue();
			}
		}
	},
	setValue: function(v) {
		sw.Promed.SwLpuListsBaseCombo.superclass.setValue.apply(this, arguments);

		var r = this.findRecord(this.valueField, v);

		if ( r ) {
			this.setParentElementValue();

			if ( r.get(this.codeField) && r.get(this.codeField) != "" ) {
				var text = r.get(this.codeField) + '. ' + r.get(this.displayField);
			}
			else {
				var text = r.get(this.displayField);
			}

			if ( r.get(this.valueField) != "" ) {
				Ext.form.ComboBox.superclass.setRawValue.call(this, text);
			}
		}
	},
	triggerAction: 'all',
	initComponent: function() {
		sw.Promed.SwLpuListsBaseCombo.superclass.initComponent.apply(this, arguments);

		this.addListener('blur', function(combo) {
			if ( Ext.isEmpty(combo.getValue()) || Ext.isEmpty(combo.getRawValue()) ) {
				combo.setRawValue('');
				combo.setValue('');
				combo.fireEvent('change', combo, 0, 1);
			}
		});

		this.addListener('change', function(combo, newValue, oldValue) {
			if ( !(typeof this.linkedElements == 'object') || this.linkedElements.length == 0 || this.linkedElementsDisabled == true ) {
				return true;
			}

			var altValue, index, masterRecord;

			if ( this.valueFieldAdd ) {
				var r = combo.getStore().getById(newValue);

				if ( r ) {
					altValue = r.get(this.valueFieldAdd);
				}
			}
			var parentElement = Ext.getCmp(this.parentElementId);

			for ( var i = 0; i < this.linkedElements.length; i++ ) {
				var linked_element = Ext.getCmp(this.linkedElements[i]);

				if ( typeof linked_element != 'object' ) {
					return true;
				}

				var linked_element_value = linked_element.getValue();

				if ( newValue > 0 ) {
					index = combo.getStore().findBy(function(rec) {
						return (rec.get(combo.valueField) == newValue);
					});
					masterRecord = combo.getStore().getAt(index);

					linked_element.clearValue();
					linked_element.clearBaseFilter();

					linked_element.setBaseFilter(function(record, id) {
						if (
							(
								record.get(this.valueField) == newValue
								|| (altValue && record.get(this.valueField) == altValue)
								|| (
									typeof this.linkedElementParams == 'object'
									&& (
										(typeof this.linkedElementParams.additionalFilterFn == 'function' && this.linkedElementParams.additionalFilterFn(masterRecord, record) == true)
										|| this.linkedElementParams.ignoreFilter == true
									)
								)
							)
							&& (
								typeof linked_element.globalFilterFn != 'function'
								|| linked_element.globalFilterFn(record)
							)
						) {
							return true;
						}
						else {
							return false;
						}
					}.createDelegate(this), this);

					if ( linked_element_value && linked_element.valueField ) {
						index = linked_element.getStore().findBy(function(record) {
							if ( record.get(linked_element.valueField) == linked_element_value ) {
								return true;
							}
							else {
								return false;
							}
						}.createDelegate(this));

						var record = linked_element.getStore().getAt(index);

						if ( record ) {
							linked_element.setValue(linked_element_value);
							linked_element.fireEvent('change', linked_element, linked_element_value, null);
						}
						else {
							linked_element.clearValue();
							linked_element.fireEvent('change', linked_element, null);
						}
					}
				}
				else if ( parentElement && parentElement.getValue() > 0 && typeof parentElement.linkedElements == 'object' && this.linkedElements[i].inlist(parentElement.linkedElements) ) {
					// если есть родительский комбо в котором тоже есть связь с подчиненным комбо, то нужно применить фильтр по нему.
					// применяется для последовательной связи 3 комбо (LpuUnit, LpuBuilding, LpuSection), где 3ее комбо зависит сразу от обоих предыдущих.
					newValue = parentElement.getValue();

					index = parentElement.getStore().findBy(function(rec) {
						return (rec.get(parentElement.valueField) == newValue);
					});
					masterRecord = parentElement.getStore().getAt(index);

					linked_element.clearValue();

					linked_element.setBaseFilter(function(record, id) {
						if (
							(
								record.get(parentElement.valueField) == newValue
								|| (altValue && record.get(parentElement.valueField) == altValue)
								|| (
									typeof parentElement.linkedElementParams == 'object'
									&& (
										(typeof parentElement.linkedElementParams.additionalFilterFn == 'function' && parentElement.linkedElementParams.additionalFilterFn(masterRecord, record) == true)
										|| parentElement.linkedElementParams.ignoreFilter == true
									)
								)
							)
							&& (
								typeof linked_element.globalFilterFn != 'function'
								|| linked_element.globalFilterFn(record)
							)
						) {
							return true;
						}
						else {
							return false;
						}
					}.createDelegate(this), this);

					if ( linked_element_value && linked_element.valueField ) {
						var index = linked_element.getStore().findBy(function(record) {
							if ( record.get(parentElement.valueField) == linked_element_value ) {
								return true;
							}
							else {
								return false;
							}
						}.createDelegate(this));

						var record = linked_element.getStore().getAt(index);

						if ( record ) {
							linked_element.setValue(linked_element_value);
							linked_element.fireEvent('change', linked_element, linked_element_value, null);
						}
						else {
							linked_element.clearValue();
							linked_element.fireEvent('change', linked_element, null);
						}
					}
				}
				else {
					linked_element.clearBaseFilter();
					linked_element.getStore().clearFilter();
					if ( typeof linked_element.globalFilterFn == 'function' ) {
						linked_element.setBaseFilter(function(record, id) {
							return (linked_element.globalFilterFn(record));
						}.createDelegate(this), this);
					}
					linked_element.fireEvent('change', linked_element, null);
				}
			}
		}.createDelegate(this));

		this.addListener('keydown', function(inp, e) {
			if ( e.getKey() == e.TAB && e.shiftKey == true && inp.focusOnShiftTab != null ) {
				e.stopEvent();
				Ext.getCmp(inp.focusOnShiftTab).focus(true);
			}
			else if ( e.getKey() == e.TAB && e.shiftKey == false && inp.focusOnTab != null ) {
				e.stopEvent();
				Ext.getCmp(inp.focusOnTab).focus(true);
			}
			else if ( e.getKey() == e.DELETE ) {
				e.stopEvent();
				inp.clearValue();
			}
		});
/*
		this.addListener('select', function(combo, record, index) {
			combo.setValue(record.get(combo.valueField));
			combo.fireEvent('change', combo, record.get(combo.valueField), 0);
		});
*/
	}
});

sw.Promed.SwLpuFilialGlobalCombo = Ext.extend(sw.Promed.SwLpuListsBaseCombo, {
	codeField: 'LpuFilial_Code',
	displayField: 'LpuFilial_Name',
	fieldLabel: langs('Филиал'),
	hiddenName: 'LpuFilial_id',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<table style="border: 0;"><td style="width: 70px"><font color="red">{LpuFilial_Code}</font></td><td><div><h3>{LpuFilial_Name}&nbsp;</h3></div><div style="font-size: 10px;">{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div></td></tr></table>',
		'</div></tpl>'
	),
	valueField: 'LpuFilial_id',
	initComponent: function() {
		this.addListener('keydown', function(inp, e) {
			if ( e.getKey() == e.DELETE||e.getKey() == e.BACKSPACE){
				inp.clearValue();
				inp.setRawValue(null);
			}
		});
		sw.Promed.SwLpuFilialGlobalCombo.superclass.initComponent.apply(this, arguments);

		this.store = new Ext.data.Store({
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				id: 'LpuFilial_id'
			}, [
				{ name: 'LpuFilial_id', mapping: 'LpuFilial_id' },
				{ name: 'Lpu_id', mapping: 'Lpu_id', type: 'int' },
				{ name: 'Lpu_Name', mapping: 'Lpu_Name', type: 'string' },
				{ name: 'LpuFilial_Code', mapping: 'LpuFilial_Code' },
				{ name: 'LpuFilial_Name', mapping: 'LpuFilial_Name', type: 'string', multipleSortInfo: [
					{ field: 'sortID', direction: 'ASC' },
					{ field: 'Lpu_Name', direction: 'ASC' },
					{ field: 'LpuFilial_Name', direction: 'ASC' }
				]},
				{ name: 'LpuFilial_begDate', mapping: 'LpuFilial_begDate', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'LpuFilial_endDate', mapping: 'LpuFilial_endDate', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'sortID', mapping: 'sortID', type: 'int' }
			]),
			sortInfo: {
				field: 'LpuFilial_Name'
			},
			listeners: {
				'load': function(store) {
					this.setValue(this.getValue());
				}.createDelegate(this)
			},
			url: '/?c=Common&m=loadLpuFilialList'
		});
	}
});
Ext.reg('swlpufilialglobalcombo', sw.Promed.SwLpuFilialGlobalCombo);

sw.Promed.SwLpuBuildingGlobalCombo = Ext.extend(sw.Promed.SwLpuListsBaseCombo, {
	codeField: 'LpuBuilding_Code',
	displayField: 'LpuBuilding_Name',
	fieldLabel: langs('Подразделение'),
	hiddenName: 'LpuBuilding_id',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<table style="border: 0;"><td style="width: 70px"><font color="red">{LpuBuilding_Code}</font></td><td><div><h3>{LpuBuilding_Name}&nbsp;</h3></div><div style="font-size: 10px;">{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div></td></tr></table>',
		'</div></tpl>'
	),
	valueField: 'LpuBuilding_id',
	initComponent: function() {
		this.addListener('keydown', function(inp, e) { //В рамках задачи https://redmine.swan.perm.ru/issues/85359
			if ( e.getKey() == e.DELETE||e.getKey() == e.BACKSPACE){
				inp.clearValue();
				inp.setRawValue(null);
			}
		});
		sw.Promed.SwLpuBuildingGlobalCombo.superclass.initComponent.apply(this, arguments);

		this.store = new Ext.data.Store({
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				id: 'LpuBuilding_id'
			}, [
				{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
				{ name: 'Lpu_id', mapping: 'Lpu_id', type: 'int' },
				{ name: 'LpuFilial_id', mapping: 'LpuFilial_id', type: 'int' },
				{ name: 'Lpu_Name', mapping: 'Lpu_Name', type: 'string' },
				{ name: 'LpuBuilding_Code', mapping: 'LpuBuilding_Code' },
				{ name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name', type: 'string', multipleSortInfo: [
					{ field: 'sortID', direction: 'ASC' },
					{ field: 'Lpu_Name', direction: 'ASC' },
					{ field: 'LpuBuilding_Name', direction: 'ASC' }
				]},
				{ name: 'sortID', mapping: 'sortID', type: 'int' },
				{ name: 'LpuBuilding_begDate', mapping: 'LpuBuilding_begDate', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'LpuBuilding_endDate', mapping: 'LpuBuilding_endDate', type: 'date', dateFormat: 'd.m.Y' }
			]),
			sortInfo: {
				field: 'LpuBuilding_Name'
			},
			listeners: {
				'load': function(store) {
					this.setValue(this.getValue());
				}.createDelegate(this)
			},
			url: '/?c=Common&m=loadLpuBuildingList'
		});
	}
});
Ext.reg('swlpubuildingglobalcombo', sw.Promed.SwLpuBuildingGlobalCombo);

sw.Promed.SwLpuSectionGlobalCombo = Ext.extend(sw.Promed.SwLpuListsBaseCombo, {
	codeField: 'LpuSection_Code',
	displayField: 'LpuSection_Name',
	fieldLabel: langs('Отделение'),
	hiddenName: 'LpuSection_id',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<table style="border: 0;"><tr>',
		'<td style="width: 70px"><font color="red">{LpuSection_Code}</font></td>',
		'<td class="{LpuSection_Class}">',
			'<div><h3>{LpuSection_Name}&nbsp;</h3></div>',
			'<div style="font-size: 10px;">{[!Ext.isEmpty(values.LpuSection_setDate) ? "Дата начала действия: " + Ext.util.Format.date(values.LpuSection_setDate,"d.m.Y"):""]} {[!Ext.isEmpty(values.LpuSection_disDate) ? "Дата закрытия: " + Ext.util.Format.date(values.LpuSection_disDate,"d.m.Y"):""]}</div>',
			'<div style="font-size: 10px;">{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div>',
		'</td>',
		'</tr></table>',
		'</div></tpl>'
	),
	valueField: 'LpuSection_id',
	//valueFieldAdd: 'LpuSection_pid',
	initComponent: function() {
		sw.Promed.SwLpuSectionGlobalCombo.superclass.initComponent.apply(this, arguments);

		this.store = new Ext.data.Store({
			autoLoad: false,
			key: 'LpuSection_id',
			reader: new Ext.data.JsonReader({
				id: 'LpuSection_id'
			}, [
				{ name: 'LpuSection_Code', mapping: 'LpuSection_Code', type: 'string' },
				{ name: 'LpuSectionCode_id', mapping: 'LpuSectionCode_id', type: 'int' },
				{ name: 'LpuSection_id', mapping: 'LpuSection_id', type: 'int' },
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
			]),
			listeners: {
				'load': function(store) {
					this.setValue(this.getValue());
				}.createDelegate(this)
			},
			sortInfo: {
				field: 'LpuSection_Name'
			},
			url: C_LPUSECTION_LIST
		});
	}
});
Ext.reg('swlpusectionglobalcombo', sw.Promed.SwLpuSectionGlobalCombo);

sw.Promed.SwLpuSectionWardGlobalCombo = Ext.extend(sw.Promed.SwLpuListsBaseCombo, {
	codeField: '',
	displayField: 'LpuSectionWard_Name',
	fieldLabel: langs('Палата'),
	hiddenName: 'LpuSectionWard_id',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<div><div><h3>{LpuSectionWard_Name}&nbsp;</h3></div><div style="font-size: 10px;">{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div></div>',
		'</div></tpl>'
	),
	valueField: 'LpuSectionWard_id',
	initComponent: function() {
		sw.Promed.SwLpuSectionWardGlobalCombo.superclass.initComponent.apply(this, arguments);

		this.store = new Ext.data.Store({
			autoLoad: false,
			key: 'LpuSectionWard_id',
			reader: new Ext.data.JsonReader({
				id: 'LpuSectionWard_id'
			}, [
				{ name: 'LpuSectionWard_id', mapping: 'LpuSectionWard_id' },
				{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
				{ name: 'Lpu_id', mapping: 'Lpu_id', type: 'int' },
				{ name: 'Lpu_Name', mapping: 'Lpu_Name', type: 'string' },
				{ name: 'Sex_id', mapping: 'Sex_id' },
				{ name: 'LpuSectionWard_Name', mapping: 'LpuSectionWard_Name', type: 'string', multipleSortInfo: [
					{ field: 'sortID', direction: 'ASC' },
					{ field: 'Lpu_Name', direction: 'ASC' },
					{ field: 'LpuSectionWard_Name', direction: 'ASC' }
				]},
				{ name: 'LpuSectionWard_disDate', mapping: 'LpuSectionWard_disDate' },
				{ name: 'LpuSectionWard_setDate', mapping: 'LpuSectionWard_setDate' },
				{ name: 'sortID', mapping: 'sortID', type: 'int' }
			]),
			url: C_LPUSECTIONWARD_LIST
		});
	}
});
Ext.reg('swlpusectionwardglobalcombo', sw.Promed.SwLpuSectionWardGlobalCombo);

sw.Promed.SwLpuSectionBedProfileGlobalCombo = Ext.extend(sw.Promed.SwLpuListsBaseCombo, {
	codeField: '',
	displayField:'LpuSectionBedProfile_Display',
	fieldLabel: langs('Профиль коек'),
	hiddenName: 'LpuSectionBedProfileLink_id',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item word-wrap">'+
		'<font color="red">{LpuSectionBedProfile_Code}</font>&nbsp;{LpuSectionBedProfile_Name}{[this.fieldName(values)]}'+
		'</div></tpl>',
		{
			fieldName: function(values){
				var begDT = (values.LpuSectionBedProfile_begDT) ? Ext.util.Format.date(values.LpuSectionBedProfile_begDT, 'd.m.Y') : '';
				var endDT = (values.LpuSectionBedProfile_endDT) ? Ext.util.Format.date(values.LpuSectionBedProfile_endDT, 'd.m.Y') : '';
				var strDT = ' <font style="font-size: 0.8em; color: #585555; font-style: italic;">' + begDT + ' - ' + endDT + '</font>';
				if(values.LpuSectionBedProfile_fedid){
					return '&nbsp;(V020: '+values.LpuSectionBedProfile_fedCode+' - '+values.LpuSectionBedProfile_fedName+')' + strDT;
				}else{
					return strDT;
				}
			}
		}
	),
	valueField: 'LpuSectionBedProfileLink_id',
	initComponent: function() {
		sw.Promed.SwLpuSectionBedProfileGlobalCombo.superclass.initComponent.apply(this, arguments);

		this.store = new Ext.data.Store({
			autoLoad: false,
			key: 'LpuSectionBedProfileLink_id',
			reader: new Ext.data.JsonReader({
				id: 'LpuSectionBedProfileLink_id'
			}, [
				{name: 'LpuSectionBedProfileLink_id', type:'int'},
				{name: 'LpuSectionBedProfile_id', type:'int'},
				{name: 'LpuSectionBedProfile_Code', type:'string'},
				{name: 'LpuSectionBedProfile_Name', type:'string'},
				{name: 'LpuSectionBedProfile_fedid', type:'int'},
				{name: 'LpuSectionBedProfile_fedCode', type:'string'},
				{name: 'LpuSectionBedProfile_fedName', type:'string'},
				{name: 'LpuSectionBedProfile_Display',
					convert: function(val,row) {
						if ( !Ext.isEmpty(row.LpuSectionBedProfile_fedid) ) {
							return row.LpuSectionBedProfile_Code + '. ' + row.LpuSectionBedProfile_Name + ' (' + row.LpuSectionBedProfile_fedCode + ' - ' + row.LpuSectionBedProfile_fedName + ')';
						}
						else {
							return row.LpuSectionBedProfile_Code + '. ' + row.LpuBuildingOffice_Name;
						}
					}
				},
				{name: 'LpuSectionBedProfile_begDT', type:'date', dateFormat: 'd.m.Y'},
				{name: 'LpuSectionBedProfile_endDT', type:'date', dateFormat: 'd.m.Y'}
			]),
			url: C_LPUSECTIONBEDPROFILE_LIST
		});
	}
});
Ext.reg('swlpusectionbedprofileglobalcombo', sw.Promed.SwLpuSectionBedProfileGlobalCombo);

sw.Promed.SwLpuUnitGlobalCombo = Ext.extend(sw.Promed.SwLpuListsBaseCombo, {
	codeField: 'LpuUnit_Code',
	displayField: 'LpuUnit_Name',
	fieldLabel: langs('Группа отделений'),
	hiddenName: 'LpuUnit_id',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<table style="border: 0;"><td style="width: 70px"><font color="red">{LpuUnit_Code}</font></td><td><div><h3>{LpuUnit_Name}&nbsp;</h3></div><div style="font-size: 10px;">{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div></td></tr></table>',
		'</div></tpl>'
	),
	valueField: 'LpuUnit_id',
	initComponent: function() {
		sw.Promed.SwLpuUnitGlobalCombo.superclass.initComponent.apply(this, arguments);

		this.store = new Ext.data.Store({
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				id: 'LpuUnit_id'
			}, [
				{ name: 'Lpu_id', mapping: 'Lpu_id', type: 'int' },
				{ name: 'Lpu_Name', mapping: 'Lpu_Name', type: 'string' },
				{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
				{ name: 'LpuUnitType_id', mapping: 'LpuUnitType_id' },
				{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
				{ name: 'LpuUnit_Code', mapping: 'LpuUnit_Code' },
				{ name: 'LpuUnit_Name', mapping: 'LpuUnit_Name', type: 'string', multipleSortInfo: [
					{ field: 'sortID', direction: 'ASC' },
					{ field: 'Lpu_Name', direction: 'ASC' },
					{ field: 'LpuUnit_Name', direction: 'ASC' }
				]},
				{ name: 'LpuUnit_IsEnabled', mapping: 'LpuUnit_IsEnabled' },
				{ name: 'sortID', mapping: 'sortID', type: 'int' }
			]),
			listeners: {
				'load': function(store) {
					this.setValue(this.getValue());
				}.createDelegate(this)
			},
			sortInfo: {
				field: 'LpuUnit_Name'
			},
			url: '/?c=Common&m=loadLpuUnitList'
		});
	}
});
Ext.reg('swlpuunitglobalcombo', sw.Promed.SwLpuUnitGlobalCombo);

sw.Promed.SwMedStaffFactGlobalCombo = Ext.extend(sw.Promed.SwLpuListsBaseCombo, {
	codeField: 'MedPersonal_TabCode',
	dateFieldId: null,
	displayField: 'MedPersonal_Fio',
	enableOutOfDateValidation: false,
	ignoreDisableInDoc: false,
	ignoreFilter: false,
	fieldLabel: langs('Врач'),
	hiddenName: 'MedStaffFact_id',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<table style="border: 0;">',
		'<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
		'<td style="width: 45px;">{MedPersonal_DloCode}&nbsp;</td>',
		'<td>',
			'<div style="font-weight: bold;">{MedPersonal_Fio}&nbsp;{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</div>',
			'<div style="font-size: 10px;">{PostMed_Name}{[!Ext.isEmpty(values.MedStaffFact_Stavka) ? ", ст." : ""]} {MedStaffFact_Stavka}</div>',
			'<div style="font-size: 10px;">{[!Ext.isEmpty(values.WorkData_begDate) ? "Дата начала работы: " + values.WorkData_begDate:""]} {[!Ext.isEmpty(values.WorkData_endDate) ? "Дата увольнения: " + this.formatWorkDataEndDate(values.WorkData_endDate) :""]}</div>',
			'<div style="font-size: 10px;">{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div>',
		'</td>',
		'</tr></table>',
		'</div></tpl>',
		{
			formatWorkDataEndDate: function(endDate) {
				var fixed = (typeof endDate == 'object' ? Ext.util.Format.date(endDate, 'd.m.Y') : endDate);
				return fixed;
			}
		}
	),
	valueField: 'MedStaffFact_id',
	initComponent: function() {
		sw.Promed.SwMedStaffFactGlobalCombo.superclass.initComponent.apply(this, arguments);

		var combo = this;

		this.store = new Ext.data.Store({
			autoLoad: false,
			getById: function(id) { // шняга шняжная, т.к. идешниками вдруг стали MedStaffFactKey_id, а много где юзается getById и считается что там MedStaffFact_id.
				var index = combo.getStore().findBy(function(rec) {
					if (rec.get('MedStaffFact_id') == id) {
						return true;
					} else {
						return false;
					}
				});
				if (index >= 0) {
					return combo.getStore().getAt(index);
				}

				return false;
			},
			reader: new Ext.data.JsonReader({
				id: 'MedStaffFactKey_id'
			}, [
				{ name: 'MedStaffFactKey_id', mapping: 'MedStaffFactKey_id' },
				{ name: 'MedPersonal_DloCode', mapping: 'MedPersonal_DloCode' },
				{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio', type: 'string', multipleSortInfo: [
					{ field: 'sortID', direction: 'ASC' },
					{ field: 'Lpu_Name', direction: 'ASC' },
					{ field: 'MedPersonal_Fio', direction: 'ASC' }
				]},
				{ name: 'MedPersonal_Fin', mapping: 'MedPersonal_Fin' },
				{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
				{ name: 'Person_id', mapping: 'Person_id' },
				{ name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
				{ name: 'Person_Snils', mapping: 'Person_Snils' },
				{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
				{ name: 'Lpu_id', mapping: 'Lpu_id', type: 'int' },
				{ name: 'Lpu_Name', mapping: 'Lpu_Name', type: 'string' },
				{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
				{ name: 'LpuBuildingType_id', mapping: 'LpuBuildingType_id' },
				{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
				{ name: 'LpuUnitSet_id', mapping: 'LpuUnitSet_id' },
				{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
				{ name: 'LpuSection_pid', mapping: 'LpuSection_pid' },
				{ name: 'LpuSectionAge_id', mapping: 'LpuSectionAge_id' },
				{ name: 'LpuSection_Name', mapping: 'LpuSection_Name' },
				{ name: 'LpuSectionProfile_Code', mapping: 'LpuSectionProfile_Code' },
				{ name: 'LpuSectionProfile_SysNick', mapping: 'LpuSectionProfile_SysNick' },
				{ name: 'LpuSectionProfile_Name', mapping: 'LpuSectionProfile_Name' },
				{ name: 'LpuUnitType_Code', mapping: 'LpuUnitType_Code' },
				{ name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick' },
				{ name: 'LpuSection_disDate', mapping: 'LpuSection_disDate' },
				{ name: 'LpuSection_setDate', mapping: 'LpuSection_setDate' },
				{ name: 'WorkData_begDate', mapping: 'WorkData_begDate' },
				{ name: 'WorkData_endDate', mapping: 'WorkData_endDate' },
				{ name: 'WorkData_dloBegDate', mapping: 'WorkData_dloBegDate' },
				{ name: 'WorkData_dloEndDate', mapping: 'WorkData_dloEndDate' },
				{ name: 'PostKind_id', mapping: 'PostKind_id' },
				{ name: 'PostMed_id', mapping: 'PostMed_id' },
				{ name: 'PostMed_Code', mapping: 'PostMed_Code' },
				{ name: 'PostMed_Name', mapping: 'PostMed_Name' },
				{ name: 'SortVal', mapping: 'SortVal' },
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
				{ name: 'LpuSectionProfile_msfid', mapping: 'LpuSectionProfile_msfid'},
				{ name: 'listType', mapping: 'listType', type: 'string' },
				{ name: 'sortID', mapping: 'sortID', type: 'int' },
				{ name: 'MedStaffFactCidDispaly', convert: function( val,row ) {
							return row.MedPersonal_Fio + ' ' + row.PostMed_Name;
					}
				},
				{ name: 'MedStaffFact_cid', mapping: 'MedStaffFact_cid' },
				{ name: 'MedPost_pid', mapping: 'MedPost_pid' },
			]),
			sortInfo: {
				field: 'MedPersonal_Fio'
			},
			url: C_MEDPERSONAL_LIST
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
	}
});
Ext.reg('swmedstafffactglobalcombo', sw.Promed.SwMedStaffFactGlobalCombo);

sw.Promed.SwMedServiceGlobalCombo = Ext.extend(sw.Promed.SwLpuListsBaseCombo, {
	codeField: '',
	displayField: 'MedService_Name',
	fieldLabel: langs('Служба'),
	hiddenName: 'MedService_id',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<div><h3>{MedService_Name}&nbsp;</h3></div><div style="font-size: 10px;">{Lpu_Name}</div>',
		'</div></tpl>'
	),
	valueField: 'MedService_id',
	linkedElementsDisabled: true,
	parentElementDisabled: true,
	initComponent: function() {
		sw.Promed.SwMedServiceGlobalCombo.superclass.initComponent.apply(this, arguments);

		this.store = new Ext.data.Store({
			autoLoad: false,
			key: 'MedService_id',
			reader: new Ext.data.JsonReader({
				id: 'MedService_id'
			}, [
				{ name: 'MedService_id', mapping: 'MedService_id' },
				{ name: 'Org_id', mapping: 'Org_id' },
				{ name: 'Lpu_id', mapping: 'Lpu_id', type: 'int' },
				{ name: 'Lpu_Name', mapping: 'Lpu_Name', type: 'string' },
				{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
				{ name: 'LpuBuildingType_id', mapping: 'LpuBuildingType_id' },
				{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
				{ name: 'LpuSection_Name', mapping: 'LpuSection_Name' },
				{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
				{ name: 'LpuUnitType_id', mapping: 'LpuUnitType_id' },
				{ name: 'MedService_begDT', mapping: 'MedService_begDT' },
				{ name: 'MedService_endDT', mapping: 'MedService_endDT' },
				{ name: 'MedService_Name', mapping: 'MedService_Name', type: 'string', multipleSortInfo: [
					{ field: 'sortID', direction: 'ASC' },
					{ field: 'Lpu_Name', direction: 'ASC' },
					{ field: 'MedService_Name', direction: 'ASC' }
				]},
				{ name: 'MedService_Nick', mapping: 'MedService_Nick' },
				{ name: 'MedServiceType_id', mapping: 'MedServiceType_id' },
				{ name: 'MedServiceType_SysNick', mapping: 'MedServiceType_SysNick' },
				{ name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick' },
				{ name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id' },
				{ name: 'MedService_IsClosed', mapping: 'MedService_IsClosed' },
				{ name: 'MedService_IsExternal', mapping: 'MedService_IsExternal', type: 'int' },
				{ name: 'MedService_IsCytologic', mapping: 'MedService_IsCytologic', type: 'int' },
				{ name: 'sortID', mapping: 'sortID', type: 'int' }
			]),
			sortInfo: {
				field: 'MedService_Name'
			},
			url: C_MEDSERVICE_LIST
		});
	}
});
Ext.reg('swmedserviceglobalcombo', sw.Promed.SwMedServiceGlobalCombo);

sw.Promed.SwMedServiceMedPersonalCombo = Ext.extend(sw.Promed.SwLpuListsBaseCombo, {
	codeField: '',
	displayField: 'MedPersonal_Fio',
	fieldLabel: langs('Врач службы'),
	hiddenName: 'MedServiceMedPersonal_id',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<div><p style="font-weight: bold"><font style="color: red">{MedServiceMedPersonal_id}</font>&nbsp;&nbsp;{MedPersonal_Fio}</p></div>',
		'</div></tpl>'
	),
	valueField: 'MedServiceMedPersonal_id',
	linkedElementsDisabled: true,
	parentElementDisabled: true,
	initComponent: function() {
		sw.Promed.SwMedServiceMedPersonalCombo.superclass.initComponent.apply(this, arguments);

		this.store = new Ext.data.Store({
			autoLoad: false,
			key: 'MedServiceMedPersonal_id',
			reader: new Ext.data.JsonReader({
				id: 'MedServiceMedPersonal_id'
			}, [
				{ name: 'MedServiceMedPersonal_id', mapping: 'MedServiceMedPersonal_id' },
				{ name: 'MedService_id', mapping: 'MedService_id' },
				{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
				{ name: 'MedServiceType_id', mapping: 'MedServiceType_id' },
				{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' }
			]),
			url: C_MEDSERVICE_MP_LIST
		});
	}
});
Ext.reg('swmedservicemedpersonalcombo', sw.Promed.SwMedServiceMedPersonalCombo);

sw.Promed.SwMedStaffFactByLpuStructureCombo = Ext.extend(sw.Promed.SwLpuListsBaseCombo, {
	codeField: 'MedPersonal_TabCode',
	displayField: 'MedPersonal_FIO',
	fieldLabel: langs('Врач'),
	hiddenName: 'MedStaffFact_id',
	tpl: new Ext.XTemplate(
		'<tpl for="."><div class="x-combo-list-item">',
		'<table style="border: 0;">',
		'<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
		'<td>',
			'<div style="font-weight: bold; white-space: normal;">{MedPersonal_FIO}&nbsp;{[Ext.isEmpty(values.LpuSection_Name)?"":"[ " + values.LpuSection_Name + " ]"]}</div>',
			'<div style="font-size: 10px;">{PostMed_Name}{[!Ext.isEmpty(values.MedStaffFact_Stavka) ? ", ст." : ""]} {MedStaffFact_Stavka}</div>',
			'<div style="font-size: 10px;">{[!Ext.isEmpty(values.MedStaffFact_setDate) ? "Дата начала работы: " + values.MedStaffFact_setDate:""]} {[!Ext.isEmpty(values.MedStaffFact_disDate) ? "Дата увольнения: " + this.formatWorkDataEndDate(values.MedStaffFact_disDate) :""]}</div>',
		'</td>',
		'</tr></table>',
		'</div></tpl>',
		{
			formatWorkDataEndDate: function(endDate) {
				var fixed = (typeof endDate == 'object' ? Ext.util.Format.date(endDate, 'd.m.Y') : endDate);
				return fixed;
			}
		}
	),
	valueField: 'MedStaffFact_id',
	linkedElementsDisabled: true,
	parentElementDisabled: true,
	initComponent: function() {
		sw.Promed.SwMedStaffFactByLpuStructureCombo.superclass.initComponent.apply(this, arguments);

		this.store = new Ext.data.Store({
			autoLoad: false,
			key: 'MedStaffFact_id',
			reader: new Ext.data.JsonReader({
				id: 'MedStaffFact_id'
			}, [
				{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
				{ name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode' },
				{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
				{ name: 'MedPersonal_FIO', mapping: 'MedPersonal_FIO' },
				{ name: 'LpuSection_Name', mapping: 'LpuSection_Name' },
				{ name: 'PostMed_Name', mapping: 'PostMed_Name' },
				{ name: 'MedStaffFact_Stavka', mapping: 'MedStaffFact_Stavka', type: 'string'},
				{ name: 'MedStaffFact_setDate', mapping: 'MedStaffFact_setDate' },
				{ name: 'MedStaffFact_disDate', mapping: 'MedStaffFact_disDate' }
			]),
			url: C_MPBYSTRUCTURE_LIST
		});
	}
});
Ext.reg('swmedstafffactbylpustructurecombo', sw.Promed.SwMedStaffFactByLpuStructureCombo);