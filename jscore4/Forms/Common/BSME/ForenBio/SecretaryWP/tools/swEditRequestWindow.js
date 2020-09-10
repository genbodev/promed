/**
 * Форма редактирования заявки в АРМах службы "Судебно-биологическое отделение с молекулярно-генетической лабораторией"
 */

Ext.define('common.BSME.ForenBio.SecretaryWP.tools.swEditRequestWindow', {
	extend: 'Ext.window.Window',
	autoShow: true,
	modal: true,
	width: '50%',
	height: '80%',
	refId: 'forenbioeditrequestwnd',
	closable: true,
//    header: false,
	title: 'Новая заявка',
	id: 'ForenBioEditRequestWindow',
	border: false,
	callback: Ext.emptyFn(),
	listeners: {
		close: function(wnd) {
			if (typeof wnd.callback == 'function') {
				wnd.callback();
			}
		}
	},
	layout: {
		align: 'stretch',
		type: 'vbox'
	},
	action: 'add',
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
			xtype: 'PersonField',
			name: data.commonName+'Container',
			margin: '0 0 5 0',
			searchCallback: function(comp,result) {
				var nextId = '';
				if (comp.next()) {
					if (comp.next().xtype == 'hidden' && comp.next().next()) {
						nextId = comp.next().next().id;
					} else {
						nextId = comp.next().id;
					}
				}
				log({id:comp.up('container').down('[name=addbutton]').id});
				
				comp.up('container').down('[name=addbutton]').setDisabled(false);
				
				me.defaultFocus = '[id='+comp.up('container').down('[name=addbutton]').id+']';
			},
			fieldLabel:label,
			idName: 'Person_id',
			FioName: 'Person_FIO',
			allowBlank: true,
			extraItems: [
			{
				xtype: 'splitbutton',
				height: '16',
				name: 'EvnForensicGeneticEvidLink_IsVic',
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
				margin: '0 0 5 0',
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
			},
			{
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
		});

	},
	/**
	 * Функция добавления строки ввода для произвольного поля с удалением и добавлением
	 * Параметры:
	 *	data.commonName [string] - Наименование типа поля, которое будет являться префиксом для имён контейнеров и поля ввода (напр. Person)
	 *	data.textFieldLabel [string] - Подпись для текстового поля ввода
	 *	data.onChangeFn [function] - функция-обработчик события change
	 */
	addSimpleField: function(data,callback) {

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
					},
					added: function(field, opts) {
						if (typeof callback == 'function') {
							callback(field,opts);
						}
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
					me.addSimpleField(data,function(field) {
						setTimeout(function(){
							field.focus();
						},1000);
					});
					
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
		var textField,label;
		for (var i=0;i<containerItemsArray.length;i++) {
			textField = containerItemsArray[i].query('[name='+data.commonName+'_Name]')[0];
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
				'EvnForensicGeneticEvid_AccDocNum',
				'EvnForensicGeneticEvid_AccDocDate',
				'EvnForensicGeneticEvid_AccDocNumSheets',
				'Org_id',
				'Person_FIO',
				'Evidence_Name',
				'EvnForensicGeneticEvid_Facts',
				'EvnForensicGeneticEvid_Goal',
			]
		return (fieldset) ? this._checkJournalIsFilled(fieldset,valueFields,setAllowBlankFlag) : false;
	},
	/**
	 * Проверка на полноту заполнения журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории
	 * Папраметры:
	 *	setAllowBlankFlag [boolean] - необходимость изменнения флага allowBlank для указанных полей
	 */
	checkBioSamplesJournalIsFilled: function(setAllowBlankFlag) {
		var fieldset = this.BaseForm.down('[name=BioSamplesJournal_Fieldset]'),
			valueFields = [
				'EvnForensicGeneticSampleLive_TakeDT',
				'EvnForensicGeneticSampleLive_TakeTime',
				'Person_FIO',
				'EvnForensicGeneticSampleLive_VerifyingDoc',
				'EvnForensicGeneticSampleLive_Basis',
				'BioSample_Name',
			]
			
		return (fieldset) ? this._checkJournalIsFilled(fieldset,valueFields,setAllowBlankFlag) : false;
	},
	/**
	 * Проверка на полноту заполнения журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования
	 * Папраметры:
	 *	setAllowBlankFlag [boolean] - необходимость изменнения флага allowBlank для указанных полей
	 */
	checkBioSamplesGenJournalIsFilled: function(setAllowBlankFlag) {
		var fieldset = this.BaseForm.down('[name=BioSamplesGenJournal_Fieldset]'),
			valueFields = [
				'EvnForensicGeneticGenLive_Facts',
				'BioSampleForMolGenRes_Name',
				'Person_FIO',
				'EvnForensicGeneticGenLive_TakeDate'
			]
		return (fieldset) ? this._checkJournalIsFilled(fieldset,valueFields,setAllowBlankFlag) : false;
	},
	/**
	 * Проверка на полноту заполнения журнала регистрации исследований мазков и тампонов в лаборатории
	 * Папраметры:
	 *	setAllowBlankFlag [boolean] - необходимость изменнения флага allowBlank для указанных полей
	 */
	checkSwabJournalIsFilled: function(setAllowBlankFlag) {
		var fieldset = this.BaseForm.down('[name=SwabJournal_Fieldset]'),
			valueFields = [
				'EvnForensicGeneticSmeSwab_DelivDT',
				'EvnForensicGeneticSmeSwab_DelivTime',
				'Person_FIO',
				'EvnForensicGeneticSmeSwab_Basis',
				'Sample_Name',
			]
		return (fieldset) ? this._checkJournalIsFilled(fieldset,valueFields,setAllowBlankFlag) : false;
	},
	allJournalsAreEmpty: function() {
		return !this.checkSwabJournalIsFilled()&&!this.checkBioSamplesGenJournalIsFilled()&&!this.checkEvidenceJournalIsFilled()&&!this.checkBioSamplesJournalIsFilled()

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
			fieldHasValue = (field.xtype == 'swdatefield') || (field.xtype == 'swtimefield') ? ((typeof field.getValue() == 'object') && (field.getValue()!==null)) : (!!field.getValue())
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

	_initCreateRequest: function() {
		var me = this;

		me.addPerson({
			commonName: 'Person',
			hasTypeFlag: true,
			onChangeFn: function() {
				me.checkEvidenceJournalIsFilled(true)
			}
		});
		me.addPerson({
			commonName: 'PersonBioSample',
			hasTypeFlag: false,
			onChangeFn: function() {
				me.checkBioSamplesGenJournalIsFilled(true)
			}
		});

		me.addSimpleField({
			commonName: 'Evidence',
			textFieldLabel: 'Вещественное доказательство',
			onChangeFn: function() {
				me.checkEvidenceJournalIsFilled(true)
			}
		});
		me.addSimpleField({
			commonName: 'BioSample',
			textFieldLabel: 'Био образец',
			onChangeFn: function() {
				me.checkBioSamplesJournalIsFilled(true)
			}
		});
		me.addSimpleField({
			commonName: 'BioSampleForMolGenRes',
			textFieldLabel: 'Био образец',
			onChangeFn: function() {
				me.checkBioSamplesGenJournalIsFilled(true)
			}
		});
		me.addSimpleField({
			commonName: 'Sample',
			textFieldLabel: 'Образец',
			onChangeFn: function() {
				me.checkSwabJournalIsFilled(true)
			}
		});

		var BaseForm = me.BaseForm.getForm();
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

		BaseForm.findField('EvnForensic_Date').setValue(new Date());
	},

	_initViewRequest: function(params) {
		if (!params.EvnForensic_id) {return false;}
		var me = this;

		var BaseForm = me.BaseForm.getForm();
		BaseForm.load({
			params: params,
			url: '/?c=BSME&m=loadForenBioOwnRequestForm',
			success: function(form, action) {
				log([form, action]);
			},
			failure: function() {
				Ext.Msg.alert('Ошибка', 'При получении данных заявки');
			}
		});
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
			items: [
				{
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
						xtype: 'swdatefield',
						fieldLabel: 'Дата постановления',
						name: 'EvnForensic_ResDate',
						allowBlank: false
					},
					{
						xtype: 'PersonField',
						searchCallback: function() {
							me.defaultFocus = '[name=EvnForensicGeneticEvid_AccDocNum]';
						},
						onChange: Ext.emptyFn,
						fieldLabel:'Направившее лицо',
						idName: 'Person_cid',
						FioName: 'Person_FIO',
						allowBlank: false,
						labelWidth: 250,
						width: '80%'
					}
				]
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
					items: [
						{	xtype: 'container',
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
							items: [
								{
									xtype: 'textfield',
									fieldLabel: '№ основного сопроводительного документа',
									name: 'EvnForensicGeneticEvid_AccDocNum'
								},{
									xtype: 'swdatefield',
									fieldLabel: 'Дата основного сопроводительного документа',
									name: 'EvnForensicGeneticEvid_AccDocDate'
								},{
									xtype: 'textfield',
									fieldLabel: 'Кол-во листов документов',
									name: 'EvnForensicGeneticEvid_AccDocNumSheets'
								},{
									xtype: 'orgfield',
									allowBlank: true,
									onChange: function(field, newVal, oldVal) {
										this.checkEvidenceJournalIsFilled(true)
									}.bind(this),
									fieldLabel: 'Учреждение направившего',
									comboName: 'Org_id'
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
									name:'EvnForensicGeneticEvid_Facts'
								},{
									xtype: 'textfield',
									fieldLabel: 'Цель экспертизы',
									name: 'EvnForensicGeneticEvid_Goal'
								}]
						}
					]
				}, {
					xtype: 'fieldset',
					collapsible: true,
					width: '60%',
					margin: '20 50 0 25',
					padding: '20 20 25 25',
					defaults: {
						labelAlign: 'left',
						labelWidth: 250
					},
					name: 'BioSamplesJournal_Fieldset',
					title: 'Журнал регистрации биологических образцов, изъятых у живых лиц в лаборатории',
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
									this.checkBioSamplesJournalIsFilled(true);
								}.bind(this)
							}
						},
						items: [{
							xtype: 'swdatefield',
							fieldLabel: 'Дата изъятия образцов',
							name: 'EvnForensicGeneticSampleLive_TakeDT'
						},{
							xtype: 'swtimefield',
							fieldLabel: 'Время изъятия образцов',
							name: 'EvnForensicGeneticSampleLive_TakeTime'
						},{
							xtype: 'PersonField',
							name:'PersContainer',
							searchCallback: function() {
								me.defaultFocus = '[name=EvnForensicGeneticSampleLive_VerifyingDoc]';
							},
							onChange: function(){
								me.checkBioSamplesJournalIsFilled(true)
							},
							fieldLabel:'Исследуемое лицо',
							idName: 'Person_zid',
							FioName: 'Person_FIO',
							allowBlank: true,
							labelWidth: 250,
							width: '80%'
						},{
							xtype: 'textfield',
							fieldLabel: 'Документ, удостоверяющий личность',
							name: 'EvnForensicGeneticSampleLive_VerifyingDoc'
						},{
							xtype: 'textfield',
							fieldLabel: 'Основания для получения образцов',
							name: 'EvnForensicGeneticSampleLive_Basis'
						},{
							xtype: 'container',
							name: 'BioSampleAllContainer',
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: []
						}]
					}]
				}, {
					xtype: 'fieldset',
					collapsible: true,
					width: '60%',
					margin: '20 50 0 25',
					padding: '20 20 25 25',
					defaults: {
						labelAlign: 'left',
						labelWidth: 250,
						listeners: {
							change: function(field, newVal, oldVal) {
								this.checkBioSamplesGenJournalIsFilled(true)
							}.bind(this)
						}
					},
					name: 'BioSamplesGenJournal_Fieldset',
					title: 'Журнал регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования',
					items: [{
						xtype: 'container',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						defaults: {
							labelAlign: 'left',
							labelWidth: 250
						},
						items: [{
							xtype: 'swdatefield',
							fieldLabel: 'Дата изъятия образцов',
							name: 'EvnForensicGeneticGenLive_TakeDate'
						},{
							xtype: 'container',
							name: 'PersonBioSampleAllContainer',
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: []
						},{
							xtype: 'container',
							name: 'BioSampleForMolGenResAllContainer',
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: []
						},{
							xtype: 'textareafield',
							minHeight: 15,
							fieldLabel: 'Краткие обстоятельства дела',
							name:'EvnForensicGeneticGenLive_Facts'
						}]
					}]
				}, {
					xtype: 'fieldset',
					collapsible: true,
					width: '60%',
					margin: '20 50 20 25',
					padding: '20 20 25 25',
					defaults: {
						labelAlign: 'left',
						labelWidth: 250
					},
					title: 'Журнал регистрации исследований мазков и тампонов в лаборатории',
					name: 'SwabJournal_Fieldset',
					items: [{
						xtype: 'container',
						layout: {
							type: 'vbox',
							align: 'stretch'
						},
						defaults: {
							labelAlign: 'left',
							labelWidth: 250
						},
						items: [{
							xtype: 'swdatefield',
							fieldLabel: 'Дата поступления образцов',
							name: 'EvnForensicGeneticSmeSwab_DelivDT'
						},{
							xtype: 'swdatefield',
							fieldLabel: 'Время поступления образцов',
							name: 'EvnForensicGeneticSmeSwab_DelivTime'
						},
						{
							xtype: 'PersonField',
							searchCallback: function() {
								me.defaultFocus = '[name=EvnForensicGeneticSmeSwab_Basis]';
							},
							onChange: function() {
								me.checkSwabJournalIsFilled(true)
							},
							fieldLabel:'Исследуемое лицо',
							idName: 'ReasearchedPerson_id',
							FioName: 'Person_FIO',
							allowBlank: true,
							labelWidth: 250,
							width: '80%'
						},{
							xtype: 'textfield',
							name: 'EvnForensicGeneticSmeSwab_Basis',
							fieldLabel: 'Основания для получения образцов'
						},{
							xtype: 'container',
							name: 'SampleAllContainer',
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: []
						}]

					}]
				}
			]
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
							'Person',
							'PersonBioSample'
						],
						simpleContainerNames = [
							'Evidence',
							'BioSample',
							'BioSampleForMolGenRes',
							'Sample'
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
									personTypeField = containerArray[k].down('[name=EvnForensicGeneticEvidLink_IsVic]');
									if (personTypeField) {
										obj['EvnForensicGeneticEvidLink_IsVic']=personTypeField.getValue();
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
					params['ReasearchedPerson_id'] = this.BaseForm.getForm().findField('ReasearchedPerson_id').getValue();

					if (this.allJournalsAreEmpty()) {
						Ext.Msg.alert('Проверка данных формы', 'Ни один из журналов не заполнен.Пожалуйста, заполните хотя бы 1 журнал');
						return;
					}
					if (!this.BaseForm.isValid()) {
						Ext.Msg.alert('Проверка данных формы', 'Не все поля формы заполнены.<br>Незаполненные поля выделены особо.');
						return;
					}
					var loadMask =  new Ext.LoadMask(me, {msg:"Пожалуйста, подождите, идёт сохранение формы..."});
					//loadMask.show();
					this.BaseForm.submit({
						params : params,
						url: '/?c=BSME&m=saveEvnForensicGeneticRequest',
						success: function(form, action) {
							loadMask.hide();
							Ext.Msg.alert('', "Заявка успешно сохранена");
							//me.close();
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
		});
		
		if (me.EvnForensicSub_id) {
			me.title = 'Редактирование заявки';
			me._initEditRequest();
		} else {
			me._initCreateRequest();
		}
		
		me.callParent(arguments);
	}
});