/**
* swDirectoryEditWindow - окно добавления/редактирования записи справочника
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2011-2012 Swan Ltd.
* @author       Storozhev Dmitry
* @version      12.10.2012
*/

sw.Promed.swDirectoryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 700,
	height: 500,
	modal: true,
	resizable: false,
	//plain: false,
	title: '',
	action: null,
	callback: Ext.emptyFn,
	id: 'DirectoryEditWindow',
	listeners: {
		hide: function() {
			this.deleteFormFields();
		}
	},

	show: function() {
		sw.Promed.swDirectoryEditWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] || !arguments[0].action || !arguments[0].owner || Ext.isEmpty(!arguments[0].owner.object) ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], this.hide.createDelegate(this, []));
			return;
		}
		
		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		this.action = arguments[0].action;
		this.buttons[0].setDisabled(this.action === 'view');
		
		var loadParams = {};
		
		loadParams['scheme'] = arguments[0].Directory_Schema;
		loadParams['table'] = arguments[0].Directory_Name;

		this.Directory_Schema = arguments[0].Directory_Schema;
		this.Directory_Name = arguments[0].Directory_Name;

		if( this.action !== 'add' ) {
			loadParams['keyName'] = arguments[0].keyName;
			loadParams['keyValue'] = arguments[0].id;
		}
		this.getFormData(loadParams);
		this.setTitle(lang['spravochnik'] + loadParams['scheme'] + '.' + loadParams['table'] + ': ' + this._getActionName(this.action) + ' ' + lang['zapisi']);
	},
	
	_getActionName: function(name) {
		return {
			add: lang['dobavlenie'],
			edit: lang['redaktirovanie'],
			view: lang['prosmotr']
		}[name];
	},
	
	getFormData: function(params) {
		var win = this;
		this.lists = [];
		this.editFields = [];
		
		this.getLoadMask(lang['zagruzka_parametrov']).show();
		Ext.Ajax.request({
			params: params,
			url: '/?c=MongoDBWork&m=getFormDataForDirectoryEditWindow',
			scope: this,
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if( s ) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if(obj.data) {
						win.editFields = obj.data;
						this.setFormFields(obj.data);
						// прогрузить данные комбо-справочников
						var panel = this.Form; // получаем панель, на которой находятся комбики
						this.getFieldsLists(panel, {
							needConstructComboLists: true,
							needConstructEditFields: true
						});
						this.loadDataLists({}, this.lists, true, function() {
							for (var k in obj.data) {
								if (!Ext.isEmpty(win.findById(win.id + '_' + obj.data[k].name))) {
									if (obj.data[k].table) {
										switch(obj.data[k].table) {
											case 'UslugaComplex':
												if (!Ext.isEmpty(obj.data[k].value)) {
													var combo = win.findById(win.id + '_' + obj.data[k].name);
													var combo_value = obj.data[k].value;
													combo.getStore().load({
														params: {
															'UslugaComplex_id': combo_value
														},
														callback: function() {
															combo.setValue(combo_value);
														}
													});
												}
											break;
											default:
												win.findById(win.id + '_' + obj.data[k].name).setValue(obj.data[k].value);
											break;
										}
									} else {
										win.findById(win.id + '_' + obj.data[k].name).setValue(obj.data[k].value);
									}
								}
							}
						}); // прогружаем все справочники (третий параметр noclose - без операций над формой)
					} else {
						this.hide();
					}
				}
			}
		});
	},
	
	setFormFields: function(fields) {
		var win = this;
		var contId = this.id + '_Container',
			items = [],
			maxWidthLabel = 0;

		var keyName;

		// Получаем название поля первичного ключа
		for(var i=0; i<fields.length; i++) {
			if ( fields[i].name == 'keyName' ) {
				keyName = fields[i].value;
			}
		}

		var hasDownloadLink = false;

		for(var i=0; i<fields.length; i++) {
			if( fields[i].fieldLabel ) {
				maxWidthLabel = maxWidthLabel < fields[i].fieldLabel.length ? fields[i].fieldLabel.length : maxWidthLabel;
			}
			//для поля "идентификатор" добавляю дизабленый контрол со значением, чисто для отображения
			if (keyName == fields[i].name) {
				var fieldLabel;
				if (fields[i].fieldLabel) {
					fieldLabel = fields[i].fieldLabel;
				} else {
					fieldLabel = lang['identifikator'];
				}
				maxWidthLabel = maxWidthLabel < fieldLabel ? fieldLabel : maxWidthLabel;
				items.push({
					readOnly: true,
					name: fields[i].name,
					id: win.id + '_' + fields[i].name,
					fieldLabel: fieldLabel,
					value: fields[i].value,
					xtype: "textfield"
				});
			} else {
				fields[i].disabled = this.action === 'view';

				if ( this.Directory_Schema == 'nsi' && this.Directory_Name != 'RefTableRegistry' && ( fields[i]['name'] == 'RefTableRegistry_Oid' || fields[i]['name'] == 'RefTableRegistry_Nick' ) ) {
					fields[i].disabled = true;
				}

				if (fields[i].name) {
					fields[i].id = win.id + '_' + fields[i].name;
				}

				items.push(fields[i]);

				if (fields[i].name == 'downloadLink' && fields[i].value) {
					hasDownloadLink = true;
				}
				if (fields[i].name == "Region_id") {
					fields[i].maskRe = /[0-9:]/;
				}
			}
		}

		if (this.Directory_Schema == 'nsi') {
			var that = this;
			items.push({
				text: 'Скачать файл со справочником',
				disabled: !hasDownloadLink,
				handler: function() {
					var idField = win.findById('DirectoryEditWindow_'+that.Directory_Name+'_id');
					if (idField && idField.getValue()) {
						window.open("/?c=ServiceNSI&m=downloadRefTableRegistry&RefTableName="+that.Directory_Name+"&RefTableId="+ idField.getValue(), '_blank');
					} else {
						sw.swMsg.alert('Внимание', 'Файл со справочником не найден');
					}
				},
				xtype: 'button'
			});
		}
		
		var container = new sw.Promed.Panel({
			layout: 'form',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: maxWidthLabel*7, // TO-DO: надо по-другому считать
			id: contId,
			defaults: {
				anchor: '-10'
			},
			items: items
		});
		
		this.Form.add(container);
		this.Form.doLayout();
		this.doLayout();
		
		this.center();
	},
	
	deleteFormFields: function() {
		this.Form.removeAll(true);
	},
	
	isFormValid: function() {
		var valid = true;
		this.Form.findBy(function(f) {
			if( f.isValid ) {
				//log(f.name + " => allowBlank: " + f.allowBlank + " => " + f.isValid());
				valid = valid ? f.isValid() : valid;
			}
		});
		return valid;
	},
	
	doSave: function() {
		var form = this.Form.getForm(),
			data = form.getValues(),
			saveData = {};
		
		// преобразовать даты в Y-m-d
		for(var index in this.editFields) {
			if (this.editFields[index] && this.editFields[index]['name']) {
				var field_name = this.editFields[index]['name'];
				if (this.editFields[index]['xtype'] && this.editFields[index]['xtype'] == 'swdatefield' && data.hasOwnProperty(field_name)) {
					data[field_name] = {
						type: 'date',
						format: 'd.m.Y',
						value: data[field_name]
					};
				}
			}
		}
		
		delete data['scheme'];
		delete data['table'];
		delete data['keyName'];
		saveData['fieldsData'] = Ext.util.JSON.encode(data);
		
		if (!this.isFormValid()) {
			sw.swMsg.alert(lang['oshibka'], lang['zapolnenyi_ne_vse_obyazatelnyie_polya']);
			return;
		}
		
		form.submit({
			params: saveData,
			scope: this,
			success: function(f, a) {
				this.hide();
				this.callback();
			}
			,failure: function(f, a) {
				//
			}
		});
	},

	initComponent: function() {
		
		this.Form = new Ext.form.FormPanel({
			frame: true,
			autoScroll: true,
			url: '/?c=MongoDBWork&m=saveDirectoryRecord',
			layout: 'form',
			items: []
		});
		
    	Ext.apply(this, {
			layout: 'fit',
			items: [this.Form],
			buttons: [{
				handler: this.doSave.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			}, 
			HelpButton(this), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: this.hide.createDelegate(this, [])
			}],
			buttonAlign: 'right'
		});
		sw.Promed.swDirectoryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});