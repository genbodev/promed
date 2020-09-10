/* 
 * Форма добавления заявки в АРМ Секретаря службы "Медико-криминалистическое отделение"
 */


Ext.define('common.BSME.ForenCrim.SecretaryWP.tools.swCreateRequestWindow', {
	extend: 'Ext.window.Window',
    autoShow: true,
	modal: true,
	width: '50%',
	height: '80%',
	refId: 'forencrimcreaterequestwnd',
	closable: true,
//    header: false,
	title: 'Новая заявка',
	id: 'ForenCrimCreateRequestWindow',
	border: false,
	layout: {
        align: 'stretch',
        type: 'vbox'
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

	/**
	 * Функция добавления строки ввода для человека с поиском удалением и добавлением
	 * Параметры: 
	 *	data.commonName [string] - Наименование типа поля, являющееся префиксом для имён контейнеров (напр. Person)
	 *	data.hasTypeFlag [boolean] - флаг установки типа лица (Обвиняемый/свидетель)
	 *	data.onChangeFn [function] - функция-обработчик события change
	 */
	addPerson: function(data) {
		if (!data.commonName) {
			return false;
		}
		
		var PersonsContainerSelectorArray = this.BaseForm.query('[name='+data.commonName+'AllContainer]'),
			PersonsContainer = (PersonsContainerSelectorArray.length)?PersonsContainerSelectorArray[0]:null,
			cnt = (PersonsContainer)?PersonsContainer.items.length:null,
			me = this;
			
		if (!PersonsContainer) 
			return false;
		
		if (cnt) {
			var prevField = PersonsContainer.items.getAt(cnt-1);
			if (prevField) {
				prevField.query('[name=addbutton]')[0].setVisible(false);
				prevField.query('[name=deletebutton]')[0].setVisible(true);
			}
		}
		
		var label = (data.hasTypeFlag)?'Потерпевший/обвиняемый':'Исследуемое лицо';
		
		PersonsContainer.add({
			xtype:'container',
			name: data.commonName+'Container',
			margin: '0 0 5 0',
			layout: {
				type: 'hbox',
				align: 'stretch'
			},
			items: [{
				flex: 1,
				labelAlign: 'left',
				labelWidth: 250,
				xtype: 'textfield',
				name: 'Person_FIO',
				readOnly: true,
				fieldLabel: label+' #'+(1*cnt+1),
				margin: '0 5 0 0',
				listeners: {
					focus: function(field,focusEvt,evtOpts){
						var Person_id =field.up('container').down('[name=Person_id]'),
							Person_FIO = field;
						me.searchPerson({callback: function(result){
							if (result)	{
								if (result.Person_id) {
									Person_id.setValue(result.Person_id);
								}
								if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
									Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
								}
							}
							me.defaultFocus = '[id='+field.next().next().id+']';
						}});
					},
					change: data.onChangeFn
				}
			},{
				xtype: 'hidden',
				name: 'Person_id',
				value: 0,
				listeners: {
					change: function(field,nV,oV) {
						if (nV) {
							this.up('container').query('[name=addbutton]')[0].setDisabled(false);
						} else {
							this.up('container').query('[name=addbutton]')[0].setDisabled(true);
						}
					}
				}
			},{
				xtype: 'splitbutton',
				name: 'EvnForensicCrimeEvidLink_IsVic',
				value: 1,
				hidden: !data.hasTypeFlag,
				disabled: !data.hasTypeFlag,
				setValue: function(val) {
					this.value = val;
				},
				getValue: function() {
					return this.value
				},
				text: 'Обвиняемый',
				menu: new Ext.menu.Menu({
					items: [
						{value: 1, text: 'Обвиняемый', handler: function(){
							var splitbutton = this.up('splitbutton');
							splitbutton.setText(this.text);
							splitbutton.setValue(this.value);
						}},
						{value: 2, text: 'Потерпевший', handler: function(){
							var splitbutton = this.up('splitbutton');
							splitbutton.setText(this.text);
							splitbutton.setValue(this.value);
						}}
					]
				})
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				iconCls: 'search16',
				name: 'searchbutton',
				tooltip: 'Поиск человека',
				handler: function(btn,evnt) {
					var Person_id =btn.up('container').down('[name=Person_id]'),
						Person_FIO = btn.up('container').down('[name=Person_FIO]');
					me.searchPerson({callback: function(result){
						if (result)	{
							if (result.Person_id) {
								Person_id.setValue(result.Person_id);
							}
							if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
								Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
							}
						}
						me.defaultFocus = '[id='+btn.next().id+']';
					}});
				}
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				name: 'addbutton',
				iconCls: 'add16',
				disabled: true,
				tooltip: 'Добавить человека',
				handler: function(btn,evnt){
					me.addPerson(data)
				}
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				hidden: true,
				name: 'deletebutton',
				iconCls: 'delete16',
				tooltip: 'Удалить человека',
				handler: function(btn,evnt){
					me.deleteSimpleField(btn,data)
				}
			}]
		})
	},
	/**
	 * Функция добавления строки ввода для произвольного поля с удалением и добавлением
	 * Параметры: 
	 *	data.commonName [string] - Наименование типа поля, которое будет являться префиксом для имён контейнеров и поля ввода (напр. Person)
	 *	data.textFieldLabel [string] - Подпись для текстового поля ввода
	 *	data.onChangeFn [function] - функция-обработчик события change
	 */
	addSimpleField: function(data) {
		
		if (!data.commonName || !data.textFieldLabel) {
			return false;
		}
		
		var containerAllSelectorArray = this.BaseForm.query('[name='+data.commonName+'AllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null,
			cnt = (containerAll)?containerAll.items.length:null,
			me = this;
				
		if (!containerAll) 
			return false;
		
		if (cnt) {
			var prevField = containerAll.items.getAt(cnt-1);
			if (prevField) {
				prevField.query('[name='+data.commonName+'_Name]')[0].setReadOnly(true);
				prevField.query('[name=addbutton]')[0].setVisible(false);
				prevField.query('[name=deletebutton]')[0].setVisible(true);
			}
		}
		
		containerAll.add({
			xtype:'container',
			margin: '0 0 5 0',
			name: data.commonName+'Container',
			layout: {
				type: 'hbox',
				align: 'stretch'
			},
			items: [{
				flex: 1,
				labelAlign: 'left',
				labelWidth: 250,
				xtype: 'textfield',
				name: data.commonName+'_Name',
				fieldLabel: data.textFieldLabel+' #'+(1*cnt+1),
				listeners: {
					change: function(field,nV,oV) {
						field.up('container').down('[name=addbutton]').setDisabled(!nV)
						data.onChangeFn.apply(this,[field,nV,oV]);
					}
				}
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				iconCls: 'add16',
				name: 'addbutton',
				disabled: true,
				tooltip: 'Добавить',
				handler: function(btn,evnt){
					me.addSimpleField(data)
				}
			},{
				margin: '0 0 0 5',
				xtype: 'button',
				hidden: true,
				name: 'deletebutton',
				iconCls: 'delete16',
				tooltip: 'Удалить',
				handler: function(btn,evnt){
					me.deleteSimpleField(btn,data)
					//Используем onChangeFn как реакцию на удаление поля => удаление значения
					data.onChangeFn();
				}
			}]
		});
	},
	/**
	 * Функция удаления строки ввода для произвольного поля с удалением и добавлением
	 * Параметры: 
	 *	btn [Ext.button.Button] - кнопка внутри строки, которую нужно удалить
	 *	data.commonName [string] - Наименование типа поля, которое будет являться префиксом для имён контейнеров и поля ввода (напр. Person)
	 */
	deleteSimpleField: function(btn,data) {
		var containerAllSelectorArray = this.BaseForm.query('[name='+data.commonName+'AllContainer]'),
			containerAll = (containerAllSelectorArray.length)?containerAllSelectorArray[0]:null,
			cnt = (containerAll)?containerAll.items.length:null,
			me = this;
			
		if (!containerAll || !(containerAll.queryById(btn.id)))
			return false;
		
		containerAll.remove(btn.up('container'));
		
		var containerItemsArray = containerAll.query('[name='+data.commonName+'Container]');
		var textField,label,label_postfix;
		label_postfix = (data.commonName == 'Person')? '_FIO' : '_Name';
		for (var i=0;i<containerItemsArray.length;i++) {
			textField = containerItemsArray[i].query('[name='+data.commonName+label_postfix+']')[0];
			label = textField.getFieldLabel();
			textField.setFieldLabel(label.replace(/#\d+/,'#'+(1*i+1)));
		}
	},
	/**
	 * Проверка на полноту заполнения журнала вещественных доказательств
	 * Папраметры:
	 *	setAllowBlankFlag [boolean] - необходимость изменнения флага allowBlank для указанных полей
	 */
	checkEvidenceJournalIsFilled: function(setAllowBlankFlag) {
		
		var fieldset = this.BaseForm.down('[name=EvidenceJournal_Fieldset]'),
			valueFields = [
				'EvnForensicCrimeEvid_AccDocNum',
				'EvnForensicCrimeEvid_AccDocDate',
				'EvnForensicCrimeEvid_ForDate',
				'EvnForensicCrimeEvid_AccDocNumSheets',
				'Org_id',
				'Person_FIO',
				'Evidence_Name',
				'EvnForensicCrimeEvid_Facts',
				'EvnForensicCrimeEvid_Goal',
			]
		return (fieldset) ? this._checkJournalIsFilled(fieldset,valueFields,setAllowBlankFlag) : false;
	},
	/**
	 * Проверка на полноту заполнения журнала регистрации фоторабот в медико-криминалистическом отделении
	 * Папраметры:
	 *	setAllowBlankFlag [boolean] - необходимость изменнения флага allowBlank для указанных полей
	 */
	checkPhotoJournalIsFilled: function(setAllowBlankFlag) {
		var fieldset = this.BaseForm.down('[name=PhotoJournal_Fieldset]'),
			valueFields = [
				'EvnForensicCrimePhot_ActNum',
				'EvnForensicCrimePhot_ShoDate',
				'EvnForensicCrimePhot_Person_FIO',
				'Diag_id',
				'EvnForensicCrimePhot_PosKol',
				'EvnForensicCrimePhot_NegKol',
				'EvnForensicCrimePhot_Micro',
				'EvnForensicCrimePhot_Macro',
				'EvnForensicCrimePhot_SighSho'
			]
		

		return (fieldset) ? this._checkJournalIsFilled(fieldset,valueFields,setAllowBlankFlag) : false;
	},
	/**
	 * Проверка на полноту заполнения журнала регистрации разрушения почки на планктон
	 * Папраметры:
	 *	setAllowBlankFlag [boolean] - необходимость изменнения флага allowBlank для указанных полей
	 */
	checkKidneyPlanktJournalIsFilled: function(setAllowBlankFlag) {
		var fieldset = this.BaseForm.down('[name=KidneyPlanktJournal_Fieldset]'),
			valueFields = [
				'EvnForensicCrimeDesPlan_ForDate',
				'Person_eid',
				//'Person_zid',
				'EvnForensicCrimeDesPlan_ActCorpNum',
				'EvnForensicCrimeDesPlan_ActCorpDate',
				'EvnForensicCrimeDesPlan_Facts',
			];

		return (fieldset) ? this._checkJournalIsFilled(fieldset,valueFields,setAllowBlankFlag) : false;
	},
	/**
	 * Проверка всех журналов на полноту
	 */
	allJournalsAreEmpty: function() {
		return !this.checkKidneyPlanktJournalIsFilled()&&!this.checkEvidenceJournalIsFilled()&&!this.checkPhotoJournalIsFilled()
	},
	/**
	 * Проверка на полноту заполнения произвольного журнала
	 * Параметры:
	 *		fieldset [Ext.form.Fieldset] - fieldset с необходимыми полями журнала
	 *		valueFields [array] - массив имен полей, значения которых должны быть непусты
	 *		setAllowBlankFlag [boolean] - необходимость изменнения флага allowBlank для указанных полей после проверки на заполненность
	 */
	_checkJournalIsFilled: function(fieldset, valueFields,setAllowBlankFlag) {
		if (!fieldset || !valueFields) {
			return false;
		}
		var field,
			hasEmptyValue = false,
			hasFilledValue = false,
			fieldHasValue = false,
			i;
			
		for (i=0;(i<valueFields.length);i++) {
			field = fieldset.down('[name='+valueFields[i]+']');
			fieldHasValue = (field.xtype == 'datefield') ? ((typeof field.getValue() == 'object') && (field.getValue()!==null)) : (!!field.getValue())
			hasEmptyValue = hasEmptyValue||!fieldHasValue;
			hasFilledValue = hasFilledValue||fieldHasValue;
		}
		if (setAllowBlankFlag) {
			this._setJournalAllowBlankFlag({
				fieldset:fieldset,
				valueFields:valueFields,
				allowBlank: (!hasFilledValue)
			})
		}
				//нет пустых и есть заполненные
		return !hasEmptyValue&&hasFilledValue;
	},
	/**
	 * Установка флагов allowBlank для указанных полей в журнале (fieldset-е)
	 * Параметры: 
	 *	data.fieldset [Ext.form.Fieldset] - fieldset с необходимыми полями журнала
	 *	data.valueFields [array] - массив имен полей, значение флагов allowBlank которых должны быть изменены
	 *	data.allowBlank [boolean] - значение устанавливаемого флаг аallowBlank
	 */
	_setJournalAllowBlankFlag: function(data) {
		if (!data || !data.fieldset || !data.valueFields) {
			return false;
		}
		var val,i,field;
		for (i=0;(i<data.valueFields.length);i++) {
			field = data.fieldset.down('[name='+data.valueFields[i]+']');
			field.allowBlank = !!data.allowBlank;
			field.validate();
		}
		return true;
	},
	initComponent: function() {
		var me = this;
		
		this.BaseForm = Ext.create('sw.BaseForm',{
			xtype:'BaseForm',
			cls: 'mainFormNeptune',
			autoScroll: true,
			id: this.id+'_BaseForm',
			flex: 1,
			width: '100%',
			height: '100%',
			layout: {
				padding: '0 0 0 0', // [top, right, bottom, left]
				align: 'stretch',
				type: 'vbox'
			},
			items: [{
				xtype: 'container',
				padding: '0 10 0 0',
				width: '20%',
				bodyPadding: 10,
				autoHeight: true,
				defaults: {
					labelAlign: 'left',
					labelWidth: 250
				},
				items: [{
					xtype: 'textfield',
					padding: '0 0 0 0', // [top, right, bottom, left]
					fieldLabel: 'Номер заявки',
					readOnly: true,
					name: 'EvnForensic_Num'
				},{
					xtype: 'datefield',
					fieldLabel: 'Дата заявки',
					readOnly: true,
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
					plugins: [new Ux.InputTextMask('99.99.9999')],
					name: 'EvnForensic_Date'
				},{
					xtype: 'datefield',
					fieldLabel: 'Дата постановления',
					format: 'd.m.Y',
					invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
					plugins: [new Ux.InputTextMask('99.99.9999')],
					name: 'EvnForensic_ResDate'
				},{
					xtype:'container',
					name:'PersContainer',
					margin: '0 0 5 0',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [{
						flex: 1,
						labelAlign: 'left',
						labelWidth: 250,
						xtype: 'textfield',
						name: 'Person_FIO',
						readOnly: true,
						fieldLabel: 'Назначившее лицо',
						margin: '0 5 0 0',
						listeners: {
							focus: function(field,focusEvt,evtOpts){
								var Person_id =field.up('container').down('[name=Person_сid]'),
									Person_FIO = field;
								me.searchPerson({callback: function(result){
									if (result)	{
										if (result.Person_id) {
											Person_id.setValue(result.Person_id);
										}
										if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
											Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
										}
									}
									me.defaultFocus = '[id='+field.next().next().id+']';
								}});
							}, 
							change: function(){
								me.checkKidneyPlanktJournalIsFilled(true)
							}
						}
					},{
						xtype: 'hidden',
						name: 'Person_сid',
						value: 0
					},{
						margin: '0 0 0 5',
						xtype: 'button',
						iconCls: 'search16',
						name: 'searchbutton',
						tooltip: 'Поиск человека',
						handler: function(btn,evnt) {
							var Person_id =btn.up('container').down('[name=Person_сid]'),
								Person_FIO = btn.up('container').down('[name=Person_FIO]');
							me.searchPerson({callback: function(result){
								if (result)	{
									if (result.Person_id) {
										Person_id.setValue(result.Person_id);
									}
									if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
										Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
									}
								}
								me.defaultFocus = '[id='+btn.next().id+']';
							}});
						}
					}]
				}]
			},
			{
				xtype: 'fieldset',
				collapsible: true,
				width: '60%',
				margin: '20 50 0 25',
				padding: '20 20 25 25',// [top, right, bottom, left]
				defaults: {
					labelAlign: 'left',
					labelWidth: 250
				},
				name: 'EvidenceJournal_Fieldset',
				title: 'Журнал регистрации вещественных доказательств и документов к ним в лаборатории',
				items: [{
					xtype: 'container',
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					defaults: {
						labelAlign: 'left',
						labelWidth: 250,
						listeners: {
							change: function(field, newVal, oldVal) {
								this.checkEvidenceJournalIsFilled(true)
							}.bind(this)
						}
					},
					items: [{
						xtype: 'datefield',
						fieldLabel: 'Дата поступления вещественных доказательств',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
						name: 'EvnForensicCrimeEvid_ForDate'
					},{
						xtype: 'textfield',
						fieldLabel: '№ основного сопроводительного документа',
						name: 'EvnForensicCrimeEvid_AccDocNum'
					},{
						xtype: 'datefield',
						fieldLabel: 'Дата основного сопроводительного документа',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
						name: 'EvnForensicCrimeEvid_AccDocDate'
					},{
						xtype: 'textfield',
						fieldLabel: 'Кол-во листов документов',
						name: 'EvnForensicCrimeEvid_AccDocNumSheets'
					},{
						xtype: 'dOrgCombo',
						fieldLabel: 'Учреждение направившего',
						name: 'Org_id'
					},{
						xtype: 'container',
						name: 'PersonAllContainer',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						items: []
					},{
						xtype: 'container',
						name:'EvidenceAllContainer',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						items: []
					},{
						xtype: 'textareafield',
						minHeight: 15,
						fieldLabel: 'Краткие обстоятельства дела',
						name:'EvnForensicCrimeEvid_Facts'
					},{
						xtype: 'textfield',
						fieldLabel: 'Цель экспертизы',
						name: 'EvnForensicCrimeEvid_Goal'
					}]
				}]
			}, {
				xtype: 'fieldset',
				collapsible: true,
				width: '60%',
				margin: '20 50 0 25',
				padding: '20 20 25 25',
				
				
				name: 'PhotoJournal_Fieldset',
				title: 'Журнал регистрации фоторабот в медико-криминалистическом отделении',
				items: [{	
					xtype: 'container',
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					defaults: {
						labelAlign: 'left',
						labelWidth: 250,
						listeners: {
							change: function(field, newVal, oldVal) {
								this.checkPhotoJournalIsFilled(true)
							}.bind(this)
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: '№ Акта',
						name: 'EvnForensicCrimePhot_ActNum'
					},{
						xtype: 'datefield',
						fieldLabel: 'Дата съёмки',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
						name: 'EvnForensicCrimePhot_ShoDate'

					},{
						xtype:'container',
						name:'PersContainer',
						margin: '0 0 5 0',
						layout: {
							type: 'hbox',
							align: 'stretch'
						},
						items: [{
							flex: 1,
							labelAlign: 'left',
							labelWidth: 250,
							xtype: 'textfield',
							name: 'EvnForensicCrimePhot_Person_FIO',
							readOnly: true,
							fieldLabel: 'Исследуемое лицо',
							margin: '0 5 0 0',
							listeners: {
								focus: function(field,focusEvt,evtOpts){
									var Person_id =field.up('container').down('[name=EvnForensicCrimePhot_Person_zid]'),
										Person_FIO = field;
									me.searchPerson({callback: function(result){
										if (result)	{
											if (result.Person_id) {
												Person_id.setValue(result.Person_id);
											}
											if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
												Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
											}
										}
										me.defaultFocus = '[id='+field.next().next().id+']';
									}});
								}, 
								change: function(){
									me.checkPhotoJournalIsFilled(true)
								}
							}
						},{
							xtype: 'hidden',
							name: 'EvnForensicCrimePhot_Person_zid',
							value: 0
						},{
							margin: '0 0 0 5',
							xtype: 'button',
							iconCls: 'search16',
							name: 'searchbutton',
							tooltip: 'Поиск человека',
							handler: function(btn,evnt) {
								var Person_id =btn.up('container').down('[name=EvnForensicCrimePhot_Person_zid]'),
									Person_FIO = btn.up('container').down('[name=EvnForensicCrimePhot_Person_FIO]');
								me.searchPerson({callback: function(result){
									if (result)	{
										if (result.Person_id) {
											Person_id.setValue(result.Person_id);
										}
										if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
											Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
										}
									}
									me.defaultFocus = '[id='+btn.next().id+']';
								}});
							}
						}]
					},{
						xtype: 'swDiag',
						fieldLabel: 'Судмед диагноз',
						labelAlign: 'left',
						name: 'Diag_id',
						labelWidth: 250
					}, {
						xtype: 'textfield',
						fieldLabel: 'Количество позитивов',
						name: 'EvnForensicCrimePhot_PosKol'
					},{
						xtype: 'textfield',
						fieldLabel: 'Количество негативов',
						name: 'EvnForensicCrimePhot_NegKol'
					},{
						xtype: 'textfield',
						fieldLabel: 'Обзорная съемка',
						name: 'EvnForensicCrimePhot_SighSho'
					},{
						xtype: 'textfield',
						fieldLabel: 'Макро съемка',
						name: 'EvnForensicCrimePhot_Macro'
					},{
						xtype: 'textfield',
						fieldLabel: 'Микро съемка',
						name: 'EvnForensicCrimePhot_Micro'
					}]
				}]
			}, {
				xtype: 'fieldset',
				collapsible: true,
				width: '60%',
				margin: '20 50 0 25',
				padding: '20 20 25 25',
				name: 'KidneyPlanktJournal_Fieldset',
				title: 'Журнал регистрации  разрушений почки на планктон',
				items: [{
					xtype: 'container',
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					defaults: {
						labelAlign: 'left',
						labelWidth: 250,
						listeners: {
							change: function(field, newVal, oldVal) {
								this.checkKidneyPlanktJournalIsFilled(true)
							}.bind(this)
						}
					},
					items: [{
						xtype: 'datefield',
						fieldLabel: 'Дата поступления',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
						name: 'EvnForensicCrimeDesPlan_ForDate',
						listeners: {
							change: function(){
								me.checkKidneyPlanktJournalIsFilled(true)
							}
						}
					},{
						xtype:'container',
						name:'PersContainer',
						margin: '0 0 5 0',
						layout: {
							type: 'hbox',
							align: 'stretch'
						},
						items: [{
							flex: 1,
							labelAlign: 'left',
							labelWidth: 250,
							xtype: 'textfield',
							name: 'Person_eFIO',
							readOnly: true,
							fieldLabel: 'Направившее лицо',
							margin: '0 5 0 0',
							listeners: {
								focus: function(field,focusEvt,evtOpts){
									var Person_id =field.up('container').down('[name=Person_eid]'),
										Person_FIO = field;
									me.searchPerson({callback: function(result){
										if (result)	{
											if (result.Person_id) {
												Person_id.setValue(result.Person_id);
											}
											if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
												Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
											}
										}
										me.defaultFocus = '[id='+field.next().next().id+']';
									}});
								}, 
								change: function(){
									me.checkKidneyPlanktJournalIsFilled(true)
								}
							}
						},{
							xtype: 'hidden',
							name: 'Person_eid',
							value: 0
						},{
							margin: '0 0 0 5',
							xtype: 'button',
							iconCls: 'search16',
							name: 'searchbutton',
							tooltip: 'Поиск человека',
							handler: function(btn,evnt) {
								var Person_id =btn.up('container').down('[name=Person_eid]'),
									Person_FIO = btn.up('container').down('[name=Person_eFIO]');
								me.searchPerson({callback: function(result){
									if (result)	{
										if (result.Person_id) {
											Person_id.setValue(result.Person_id);
										}
										if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
											Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
										}
									}
									me.defaultFocus = '[id='+btn.next().id+']';
								}});
							}
						}]
					},{
						xtype:'container',
						name:'PersContainer',
						margin: '0 0 5 0',
						layout: {
							type: 'hbox',
							align: 'stretch'
						},
						items: [{
							flex: 1,
							labelAlign: 'left',
							labelWidth: 250,
							xtype: 'textfield',
							name: 'Person_FIO',
							readOnly: true,
							fieldLabel: 'Исследуемое лицо',
							margin: '0 5 0 0',
							listeners: {
								focus: function(field,focusEvt,evtOpts){
									var Person_id =field.up('container').down('[name=Person_zid]'),
										Person_FIO = field;
									me.searchPerson({callback: function(result){
										if (result)	{
											if (result.Person_id) {
												Person_id.setValue(result.Person_id);
											}
											if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
												Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
											}
										}
										me.defaultFocus = '[id='+field.next().next().id+']';
									}});
								}, 
								change: function(){
									me.checkKidneyPlanktJournalIsFilled(true)
								}
							}
						},{
							xtype: 'hidden',
							name: 'Person_zid',
							value: 0
						},{
							margin: '0 0 0 5',
							xtype: 'button',
							iconCls: 'search16',
							name: 'searchbutton',
							tooltip: 'Поиск человека',
							handler: function(btn,evnt) {
								var Person_id =btn.up('container').down('[name=Person_zid]'),
									Person_FIO = btn.up('container').down('[name=Person_FIO]');
								me.searchPerson({callback: function(result){
									if (result)	{
										if (result.Person_id) {
											Person_id.setValue(result.Person_id);
										}
										if (result.PersonFirName_FirName && result.PersonSecName_SecName && result.PersonSurName_SurName) {
											Person_FIO.setValue(result.PersonSurName_SurName+' '+result.PersonFirName_FirName+' '+result.PersonSecName_SecName);
										}
									}
									me.defaultFocus = '[id='+btn.next().id+']';
								}});
							}
						}]
					},{
						xtype: 'textfield',
						fieldLabel: '№ акта вскрытия',
						name: 'EvnForensicCrimeDesPlan_ActCorpNum',
						listeners: {
							change: function(){
								me.checkKidneyPlanktJournalIsFilled(true)
							}
						}
					},{
						xtype: 'datefield',
						fieldLabel: 'Дата вскрытия',
						format: 'd.m.Y',
						invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГ',
						plugins: [new Ux.InputTextMask('99.99.9999')],
						name: 'EvnForensicCrimeDesPlan_ActCorpDate',
						listeners: {
							change: function(){
								me.checkKidneyPlanktJournalIsFilled(true)
							}
						}	
					},{
						xtype: 'textareafield',
						minHeight: 15,
						fieldLabel: 'Краткие обстоятельства дела',
						name:'EvnForensicCrimeDesPlan_Facts',
						listeners: {
							change: function(){
								me.checkKidneyPlanktJournalIsFilled(true)
							}
						}
					}]
				}]
			}]
		});
		
		Ext.apply(me,{
			items: [
				this.BaseForm
			], 
			buttons: [{
				xtype: 'button',	
				text: 'Готово',
				handler: function(btn,evnt) {
					var params = {},
						PersonContainerNames = [
							'Person'
						],
						simpleContainerNames = [
							'Evidence'
						],
						i,k,selectorContainerAll,containerAll,containerArray,obj,value,personTypeField;
					
					for (i=0;i<PersonContainerNames.length;i++) {
						selectorContainerAll = this.BaseForm.query('[name='+PersonContainerNames[i]+'AllContainer]');
						containerAll = (selectorContainerAll.length)?selectorContainerAll[0]:null;
						if (containerAll) {
							containerArray = containerAll.query('[name='+PersonContainerNames[i]+'Container]');
							params[PersonContainerNames[i]]= [];
							for (k=0;k<containerArray.length;k++) {
								value = containerArray[k].query('[name=Person_id]')[0].getValue();
								if (parseInt(value)) {
									obj = {'Person_id': value};
									personTypeField = containerArray[k].down('[name=EvnForensicCrimeEvidLink_IsVic]');
									if (personTypeField) {
										obj['EvnForensicCrimeEvidLink_IsVic']=personTypeField.getValue();
									}
									params[PersonContainerNames[i]].push(obj)
								}
							}
						}
					}
					
					for (i=0;i<simpleContainerNames.length;i++) {
						selectorContainerAll = this.BaseForm.query('[name='+simpleContainerNames[i]+'AllContainer]');
						containerAll = (selectorContainerAll.length)?selectorContainerAll[0]:null;
						if (containerAll) {
							containerArray = containerAll.query('[name='+simpleContainerNames[i]+'Container]');
							params[simpleContainerNames[i]]= [];
							for (k=0;k<containerArray.length;k++) {
								obj = {};
								value = containerArray[k].query('[name='+simpleContainerNames[i]+'_Name]')[0].getValue();
								if (value) {
									obj[simpleContainerNames[i]+'_Name'] =  containerArray[k].query('[name='+simpleContainerNames[i]+'_Name]')[0].getValue();
									params[simpleContainerNames[i]].push(obj)
								}
							}
						}
					}
					
					for (var key in params) {
						if (params.hasOwnProperty(key)) {
							params[key] = Ext.JSON.encode(params[key]);
						}
					}
					
					params['Person_zid'] = this.BaseForm.getForm().findField('Person_zid').getValue();
					params['Person_cid'] = this.BaseForm.getForm().findField('Person_cid').getValue();
					params['Person_eid'] = this.BaseForm.getForm().findField('Person_eid').getValue();
					params['EvnForensicCrimePhot_Person_zid'] = this.BaseForm.getForm().findField('EvnForensicCrimePhot_Person_zid').getValue();
					
					if (this.allJournalsAreEmpty()) {
						Ext.Msg.alert('Проверка данных формы', 'Ни один из журналов не заполнен.Пожалуйста, заполните хотя бы 1 журнал');
						return;
					}
					if (!this.BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}
					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."}); 
//						loadMask.show();
					this.BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveEvnForenCrimeOwnRequest',
						success: function(form, action) {
							loadMask.hide();
							Ext.Msg.alert('', "Заявка успешно сохранена");
						},
						failure: function(form, action) {
							loadMask.hide();
							switch (action.failureType) {
								case Ext.form.action.Action.CLIENT_INVALID:
									Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
									break;
								case Ext.form.action.Action.CONNECT_FAILURE:
									Ext.Msg.alert('Ошибка', 'Ошибка соединения с сервером');
									break;
								case Ext.form.action.Action.SERVER_INVALID:
									Ext.Msg.alert('Ошибка', action.result.Error_Msg);
						   }
						},
						callback: function() {
							loadMask.hide();
						}
					});
					
					
					
				}.bind(this)
			}]
		})
		
		me.addPerson({
			commonName: 'Person',
			hasTypeFlag: true,
			onChangeFn: function() {
				me.checkEvidenceJournalIsFilled(true)
			}
		});
		
		this.BaseForm.getForm().findField('Diag_id').bigStore.load();
		
		me.addSimpleField({
			commonName: 'Evidence',
			textFieldLabel: 'Вещественное доказательство',
			onChangeFn: function() {
				me.checkEvidenceJournalIsFilled(true)
			}
		});
		
		me.callParent(arguments);
	},
	listeners: {
		show: function(wnd,eOpts) {
			var BaseForm = wnd.BaseForm.getForm();
			Ext.Ajax.request({
				params: {},
				url: '/?c=BSME&m=getNextRequestNumber',
				success: function(response) {
					var response_obj = Ext.JSON.decode(response.responseText);																															
					if (response_obj.EvnForensic_Num) {
						BaseForm.findField('EvnForensic_Num').setValue(response_obj.EvnForensic_Num);
					} else {
						Ext.Msg.alert('Ошибка', 'При получении номера заявки произошла ошибка');
						
					}
				},
				failure: function() {
					Ext.Msg.alert('Ошибка', 'При получении номера заявки произошла ошибка');
				}
			});
			
			BaseForm.findField('EvnForensic_Date').setValue(new Date())
			
			
			
		}
	}
	
})