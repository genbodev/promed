/**
* swPersonAllergicReactionEditWindow - вид аллергической реакции
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Person
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      01.03.2011
* @comment      Префикс для id компонентов PAREF (PersonAllergicReactionEditForm)
*/

sw.Promed.swPersonAllergicReactionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		var allergic_reaction_level_id = base_form.findField('AllergicReactionLevel_id').getValue();
		var allergic_reaction_level_name = '';
		var allergic_reaction_type_id = base_form.findField('AllergicReactionType_id').getValue();
		var allergic_reaction_type_name = '';

		var index;
		var params = new Object();

		index = base_form.findField('AllergicReactionLevel_id').getStore().findBy(function(rec) {
			if ( rec.get('AllergicReactionLevel_id') == allergic_reaction_level_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			allergic_reaction_level_name = base_form.findField('AllergicReactionLevel_id').getStore().getAt(index).get('AllergicReactionLevel_Name');
		}

		index = base_form.findField('AllergicReactionType_id').getStore().findBy(function(rec) {
			if ( rec.get('AllergicReactionType_id') == allergic_reaction_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			allergic_reaction_type_name = base_form.findField('AllergicReactionType_id').getStore().getAt(index).get('AllergicReactionType_Name');
		}

		var drugFieldsArray = [/*'DrugMnn_id',*/'RlsActmatters_id','TRADENAMES_ID'],
			drugFieldNamesArray = {
				/*DrugMnn_id: 'DrugMnn_Name,*/
				RlsActmatters_id: 'RlsActmatters_RusName',
				TRADENAMES_ID: 'NAME'
			};

		var DrugParams = {};
		drugFieldsArray.forEach(function(fieldName) {
			var drugField = base_form.findField(fieldName), // name комбобокса, чтобы найти на форме
				drugField_name = drugFieldNamesArray[fieldName], // название поля в store/таблице в котором хранится название
				drug_id = drugField.getValue(),
				drug_name = '';

			index = base_form.findField(fieldName).getStore().findBy(function(rec) {
				if ( rec.get(fieldName) == drug_id ) {
					return true;
				}
				else {
					return false;
				}
			});

			if ( index >= 0 ) {
				drug_name = drugField.getStore().getAt(index).get(drugField_name);
				DrugParams[fieldName] = drug_id;
				DrugParams[drugField_name] = drug_name;
			}
		});



		var data = new Object();

		switch ( this.formMode ) {
			case 'local':
				data.personAllergicReactionData = {
					'PersonAllergicReaction_id': base_form.findField('PersonAllergicReaction_id').getValue(),
					'Person_id': base_form.findField('Person_id').getValue(),
					'Server_id': base_form.findField('Server_id').getValue(),
					'AllergicReactionLevel_id': allergic_reaction_level_id,
					'AllergicReactionType_id': allergic_reaction_type_id,
					//'DrugMnn_id': drug_mnn_id,
					'PersonAllergicReaction_setDate': base_form.findField('PersonAllergicReaction_setDate').getValue(),
					'AllergicReactionLevel_Name': allergic_reaction_level_name,
					'AllergicReactionType_Name': allergic_reaction_type_name,
					//'DrugMnn_Name': drug_mnn_name,
					'PersonAllergicReaction_Kind': base_form.findField('PersonAllergicReaction_Kind').getValue()
				};
				Ext.apply(data.personAllergicReactionData, DrugParams);
				this.callback(data);

				this.formStatus = 'edit';
				loadMask.hide();

				this.hide();
			break;

			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.PersonAllergicReaction_id > 0 ) {
								base_form.findField('PersonAllergicReaction_id').setValue(action.result.PersonAllergicReaction_id);

								data.personAllergicReactionData = {
									'PersonAllergicReaction_id': base_form.findField('PersonAllergicReaction_id').getValue(),
									'Person_id': base_form.findField('Person_id').getValue(),
									'Server_id': base_form.findField('Server_id').getValue(),
									'AllergicReactionLevel_id': allergic_reaction_level_id,
									'AllergicReactionType_id': allergic_reaction_type_id,
									//'DrugMnn_id': drug_mnn_id,
									'PersonAllergicReaction_setDate': base_form.findField('PersonAllergicReaction_setDate').getValue(),
									'AllergicReactionLevel_Name': allergic_reaction_level_name,
									'AllergicReactionType_Name': allergic_reaction_type_name,
									//'DrugMnn_Name': drug_mnn_name,
									'PersonAllergicReaction_Kind': base_form.findField('PersonAllergicReaction_Kind').getValue()
								};
								Ext.apply(data.personAllergicReactionData, DrugParams);
								this.callback(data);
								this.hide();
							}
							else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
								}
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;

			default:
				loadMask.hide();
			break;
		}
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'AllergicReactionLevel_id',
			'AllergicReactionType_id',
			//'DrugMnn_id',
			'RlsActmatters_id',
			'TRADENAMES_ID',
			'PersonAllergicReaction_Kind',
			'PersonAllergicReaction_setDate'
		);
		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	formMode: 'remote',
	formStatus: 'edit',
	id: 'PersonAllergicReactionEditWindow',

	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonAllergicReactionEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPersonAllergicReactionEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var win = this,
			base_form = win.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) {
			this.formMode = arguments[0].formMode;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.PersonInfo.load({
			Person_id: base_form.findField('Person_id').getValue(),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				clearDateAfterPersonDeath('personpanelid', 'PAREF_PersonInformationFrame', base_form.findField('PersonAllergicReaction_setDate'));
			}
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var index;
		var record;

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PERSON_PAREFADD);
				this.enableEdit(true);

				base_form.findField('AllergicReactionType_id').fireEvent('change', base_form.findField('AllergicReactionType_id'), null);
				win.setRadioGroupValue(1);

				loadMask.hide();

				base_form.clearInvalid();

				base_form.findField('PersonAllergicReaction_setDate').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				if ( this.formMode == 'local' ) {
					if ( this.action == 'edit' ) {
						this.setTitle(WND_PERSON_PAREFEDIT);
						this.enableEdit(true);

						base_form.findField('AllergicReactionType_id').fireEvent('change', base_form.findField('AllergicReactionType_id'), base_form.findField('AllergicReactionType_id').getValue());
					}
					else {
						this.setTitle(WND_PERSON_PAREFVIEW);
						this.enableEdit(false);
					}

					loadMask.hide();

					base_form.clearInvalid();

					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						base_form.findField('PersonAllergicReaction_setDate').focus(true, 250);
					}
				}
				else {
					var person_allergic_reaction_id = base_form.findField('PersonAllergicReaction_id').getValue();

					if ( !person_allergic_reaction_id ) {
						loadMask.hide();
						this.hide();
						return false;
					}

					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						}.createDelegate(this),
						params: {
							'PersonAllergicReaction_id': person_allergic_reaction_id
						},
						success: function(form, action) {
							var Tradename_id = undefined;

							if (action && action.result && action.result.data)
								Tradename_id = action.result.data.TRADENAMES_ID;

							if ( base_form.findField('accessType').getValue() == 'view' ) {
								this.action = 'view';
							}

							if ( this.action == 'edit' ) {
								this.setTitle(WND_PERSON_PAREFEDIT);
								this.enableEdit(true);

								base_form.findField('AllergicReactionType_id').fireEvent('change', base_form.findField('AllergicReactionType_id'), base_form.findField('AllergicReactionType_id').getValue());
							}
							else {
								this.setTitle(WND_PERSON_PAREFVIEW);
								this.enableEdit(false);
							}

							var inputValue = base_form.findField('PersonAllergicReactionType_value').getValue();
							if(inputValue){
								win.setRadioGroupValue(inputValue);
							}

							var drugFieldsArray = [/*'DrugMnn_id',*/'RlsActmatters_id','TRADENAMES_ID'];
							drugFieldsArray.forEach(function(fieldName) {
								var id = base_form.findField(fieldName).getValue() || Tradename_id;
								if (id) {
									var params = {};
									params[fieldName] = id;
									params['where'] = Tradename_id ? ' and TRADENAMES_ID = '+Tradename_id : '';
									base_form.findField(fieldName).getStore().load({
										callback: function (records, options, success) {
											base_form.findField(fieldName).setValue(id);
										}.createDelegate(this),
										params: params
									});
								}
							});

							loadMask.hide();

							base_form.clearInvalid();

							if ( this.action == 'view' ) {
								this.buttons[this.buttons.length - 1].focus();
							}
							else {
								base_form.findField('PersonAllergicReaction_setDate').focus(true, 250);
							}
						}.createDelegate(this),
						url: '/?c=PersonAllergicReaction&m=loadPersonAllergicReactionEditForm'
					});
				}
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 700,
	toggleFormDrugFields: function(value) {
		// value = 1 - 'Действующее вещество'
		// value = 2 - 'Торговое наименование'
		var win = this,
			base_form = win.FormPanel.getForm(),
			RlsActmattersField = base_form.findField('RlsActmatters_id'),
			TRADENAMESField = base_form.findField('TRADENAMES_ID');

		//RlsActmattersField.setVisible(value === 1);
		RlsActmattersField.setContainerVisible(value === 1);
		RlsActmattersField.setAllowBlank(value !== 1);

		//TRADENAMESField.setVisible(value === 2);
		TRADENAMESField.setContainerVisible(value === 2);
		TRADENAMESField.setAllowBlank(value !== 2);

		switch(value){
			case 1:
				TRADENAMESField.clearValue();
				break;
			case 2:
				RlsActmattersField.clearValue();
				break;
		}

	},
	toggleFormDrugFieldsAvailable: function(available){
		var win = this,
			base_form = win.FormPanel.getForm(),
			RlsActmattersField = base_form.findField('RlsActmatters_id'),
			TRADENAMESField = base_form.findField('TRADENAMES_ID'),
			rg = base_form.findField('PersonAllergicReactionType_rg');

		//rg.setVisible(available);
		rg.setDisabled(!available);

		//TRADENAMESField.setVisible(available);
		TRADENAMESField.setDisabled(!available);
		TRADENAMESField.setAllowBlank(!available);

		//RlsActmattersField.setVisible(available);
		RlsActmattersField.setDisabled(!available);
		RlsActmattersField.setAllowBlank(!available);

		if(!available){
			TRADENAMESField.clearValue();
			RlsActmattersField.clearValue();
		} else {
			win.setRadioGroupValue(1);
		}
		win.syncShadow();
	},
	setRadioGroupValue: function(inputValue){
		var win = this,
			base_form = win.FormPanel.getForm(),
			radioGroup = base_form.findField('PersonAllergicReactionType_rg');
		radioGroup.items.each(function(radioBtn){
			if (radioBtn.inputValue == inputValue) {
				radioBtn.setValue(true);
				radioGroup.fireEvent('change', radioGroup, radioBtn);
			} else {
				radioBtn.setValue(false);
			}
		});
		win.syncShadow();
	},
	initComponent: function() {
		var win = this;
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'PersonAllergicReactionEditForm',
			labelAlign: 'right',
			labelWidth: 250,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'AllergicReactionType_id' },
				{ name: 'AllergicReactionLevel_id' },
				//{ name: 'DrugMnn_id' },
				{ name: 'RlsActmatters_id' },
				{ name: 'TRADENAMES_ID' },
				{ name: 'PersonAllergicReactionType_value' },
				{ name: 'PersonAllergicReaction_id' },
				{ name: 'PersonAllergicReaction_Kind' },
				{ name: 'PersonAllergicReaction_setDate' },
				{ name: 'Person_id' },
				{ name: 'Server_id' }
			]),
			url: '/?c=PersonAllergicReaction&m=savePersonAllergicReaction',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'PersonAllergicReaction_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: true,
				fieldLabel: lang['data_vozniknoveniya'],
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						if ( Ext.isEmpty(newValue) ) {
							base_form.findField('AllergicReactionType_id').getStore().clearFilter();
						}
						else {
							base_form.findField('AllergicReactionType_id').getStore().filterBy(function(rec) {
								return (
									(Ext.isEmpty(rec.get('AllergicReactionType_begDate')) || rec.get('AllergicReactionType_begDate') <= newValue)
									&& (Ext.isEmpty(rec.get('AllergicReactionType_endDate')) || rec.get('AllergicReactionType_endDate') >= newValue)
								);
							});
						}
					}.createDelegate(this),
					'keydown': function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.TAB:
								if ( e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
								break;
						}
					}.createDelegate(this)
				},
				name: 'PersonAllergicReaction_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_PAREF + 1,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				comboSubject: 'AllergicReactionType',
				fieldLabel: lang['tip_allergicheskoy_reaktsii'],
				hiddenName: 'AllergicReactionType_id',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						var record = combo.getStore().getById(newValue);

						if ( record && record.get('AllergicReactionType_Code')) {
							var AllergicReactionType_Code = Number(record.get('AllergicReactionType_Code'));
							switch(AllergicReactionType_Code){
								case 21:
								case 31:
									win.toggleFormDrugFieldsAvailable(true);
									/*base_form.findField('DrugMnn_id').enable();
									base_form.findField('DrugMnn_id').setAllowBlank(false);*/
									base_form.findField('PersonAllergicReaction_Kind').disable();
									base_form.findField('PersonAllergicReaction_Kind').setAllowBlank(true);
									base_form.findField('PersonAllergicReaction_Kind').setRawValue('');
									break;
								default:
									win.toggleFormDrugFieldsAvailable(false);
									/*base_form.findField('DrugMnn_id').clearValue();
									base_form.findField('DrugMnn_id').disable();
									base_form.findField('DrugMnn_id').setAllowBlank(true);*/
									base_form.findField('PersonAllergicReaction_Kind').enable();
									base_form.findField('PersonAllergicReaction_Kind').setAllowBlank(false);
							}

						}
						else {
							win.toggleFormDrugFieldsAvailable(false);
							/*base_form.findField('DrugMnn_id').clearValue();
							base_form.findField('DrugMnn_id').disable();
							base_form.findField('DrugMnn_id').setAllowBlank(true);*/
							base_form.findField('PersonAllergicReaction_Kind').enable();
							base_form.findField('PersonAllergicReaction_Kind').setAllowBlank(false);
						}
					}.createDelegate(this)
				},
				moreFields: [
					{ name: 'AllergicReactionType_begDate', mapping: 'AllergicReactionType_begDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'AllergicReactionType_endDate', mapping: 'AllergicReactionType_endDate', type: 'date', dateFormat: 'd.m.Y' }
				],
				tabIndex: TABINDEX_PAREF + 2,
				width: 400,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'AllergicReactionLevel',
				fieldLabel: lang['harakter_allergicheskoy_reaktsii'],
				hiddenName: 'AllergicReactionLevel_id',
				tabIndex: TABINDEX_PAREF + 3,
				width: 400,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: true,
				fieldLabel: lang['vid_allergena'],
				height: 100,
				name: 'PersonAllergicReaction_Kind',
				tabIndex: TABINDEX_PAREF + 4,
				width: 400,
				xtype: 'textarea'
			}, {
				name: 'PersonAllergicReactionType_value',
				xtype: 'hidden'
			}, {
				//hideLabel: true,
				labelSeparator: '',
				xtype: 'radiogroup',
				padding: '0 0 0 250',
				width: 400,
				columns: 2,
				name: 'PersonAllergicReactionType_rg',
				id: 'PersonAllergicReactionType_rg',
				items: [
					{
						name: 'PersonAllergicReactionType',
						boxLabel: 'Действующее вещество',
						inputValue: 1
					},
					{
						name: 'PersonAllergicReactionType',
						boxLabel: 'Торговое наименование',
						inputValue: 2
					}
				],
				listeners: {
					'change': function(radioGroup, radioBtn) {
						if (radioBtn) {
							win.toggleFormDrugFields(radioBtn.inputValue);
						}
					}
				}
			}, {
				fieldLabel: lang['lekarstvennyiy_preparat-allergen'],
				emptyText: langs('Введите действующее вещество ...'),
				//fieldLabel: 'Действующее вещество <font style="color: red;">(МНН)</font>',
				name: 'RlsActmatters_RusName',
				xtype: 'swrlsactmatterscombo'
			}, {
				xtype: 'combo',
				anchor: '80%',
				displayField: 'NAME',
				enableKeyEvents: true,
				mode: 'local',
				triggerAction: 'none',
				doQuery: function(q, forceAll)
				{
					var combo = this;
					if(q.length<2)
						return false;
					combo.fireEvent('beforequery', combo);
					var where = ' and LOWER(NAME) like LOWER(\''+q+'%\')';
					combo.getStore().load({
						params: {where: where}
					});
				},
				listeners: {
					change: function(c)
					{
						if(typeof c.getValue() == 'string')
						{
							c.reset();
							return false;
						}
					}
				},
				valueField: 'TRADENAMES_ID',
				hiddenName: 'TRADENAMES_ID',
				store: new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
						id: 'TRADENAMES_ID'
					}, [{
						mapping: 'TRADENAMES_ID',
						name: 'TRADENAMES_ID',
						type: 'int'
					},{
						mapping: 'NAME',
						name: 'NAME',
						type: 'string'
					}]),
					url: '/?c=Rls&m=getTorgNames'
				}),
				resizable: true,
				emptyText: langs('Введите торговое наименование ...'),
				//fieldLabel: langs('Торговое наименование ')
				fieldLabel: lang['lekarstvennyiy_preparat-allergen']
			}/*, {
				allowBlank: true,
				fieldLabel: lang['lekarstvennyiy_preparat-allergen'],
				hiddenName: 'DrugMnn_id',
				listeners: {
					'keydown': function(inp, e) {
						if ( e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4 ) {
							e.stopEvent();

							var base_form = this.FormPanel.getForm();

							if ( e.browserEvent.stopPropagation ) {
								e.browserEvent.stopPropagation();
							}
							else {
								e.browserEvent.cancelBubble = true;
							}

							if ( e.browserEvent.preventDefault ) {
								e.browserEvent.preventDefault();
							}
							else {
								e.browserEvent.returnValue = false;
							}

							e.returnValue = false;

							if ( Ext.isIE ) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							switch ( e.getKey() ) {
								case Ext.EventObject.DELETE:
									inp.clearValue();
									break;

								case Ext.EventObject.F4:
									inp.onTrigger2Click();
									break;
							}
						}

						return true;
					}.createDelegate(this),
					'render': function(combo) {
						combo.getStore().baseParams.searchFull = 'searchFull';
					}
				},
				listWidth: 800,
				loadingText: lang['idet_poisk'],
				minLengthText: lang['pole_doljno_byit_zapolneno'],
				onTrigger2Click: function() {
					var base_form = this.FormPanel.getForm();

					if ( base_form.findField('DrugMnn_id').disabled ) {
						return false;
					}

					var drug_mnn_combo = base_form.findField('DrugMnn_id');

					getWnd('swDrugMnnSearchWindow').show({
						onHide: function() {
							drug_mnn_combo.focus(false);
						},
						onSelect: function(drugMnnData) {
							drug_mnn_combo.getStore().removeAll();
							drug_mnn_combo.getStore().loadData([ drugMnnData ]);
							drug_mnn_combo.setValue(drugMnnData.DrugMnn_id);
							getWnd('swDrugMnnSearchWindow').hide();
						},
						searchFull: true
					});
				}.createDelegate(this),
				tabIndex: TABINDEX_PAREF + 5,
				width: 400,
				xtype: 'swdrugmnncombo'
			}*/]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'PAREF_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else if ( !this.FormPanel.getForm().findField('DrugMnn_id').disabled ) {
						this.FormPanel.getForm().findField('DrugMnn_id').focus(true);
					}
					else if ( !this.FormPanel.getForm().findField('PersonAllergicReaction_Kind').disabled ) {
						this.FormPanel.getForm().findField('PersonAllergicReaction_Kind').focus(true);
					}
					else {
						this.FormPanel.getForm().findField('AllergicReactionLevel_id').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_PAREF + 6,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						if ( this.action != 'view' ) {
							this.buttons[0].focus();
						}
					}.createDelegate(this),
					onTabAction: function () {
						if ( this.action != 'view' ) {
							this.FormPanel.getForm().findField('PersonAllergicReaction_setDate').focus(true);
						}
					}.createDelegate(this),
					tabIndex: TABINDEX_PAREF + 7,
					text: BTN_FRMCANCEL
				}],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPersonAllergicReactionEditWindow.superclass.initComponent.apply(this, arguments);
	}
});