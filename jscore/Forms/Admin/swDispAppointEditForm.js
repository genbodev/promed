/**
* swDispAppointEditForm - окно просмотра и редактирования наследственности по заболеваниям
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009-2016 Swan Ltd.
* @author       
* @version      11.2016
* @comment      префикс DAEF
*/
/*NO PARSE JSON*/

sw.Promed.swDispAppointEditForm = Ext.extend(sw.Promed.BaseForm, {
	layout: 'form',
	title: 'Назначение',
	id: 'DispAppointEditForm',
	width: 600,
	autoHeight: true,
	modal: true,
	formStatus: 'edit',
	doSave: function()  {
		var win = this;
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}
		win.formStatus = 'save';
		var form = this.FormPanel;
		if (!form.getForm().isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (win.formMode == 'remote') {
			win.getLoadMask("Подождите, идет сохранение...").show();
			form.getForm().submit({
				url: '/?c=DispAppoint&m=saveDispAppoint',
				failure: function (result_form, action) {
					win.formStatus = 'edit';
					win.getLoadMask().hide();
				},
				success: function (result_form, action) {
					win.formStatus = 'edit';
					win.getLoadMask().hide();
					if (action.result) {
						if (action.result.DispAppoint_id) {
							if ( win.callback(win.owner, action.result.DispAppoint_id) === true ) {
								win.hide();
							}
						}
						else
							Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
					}
					else
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
				}
			});
		} else {
			this.formStatus = 'edit';

			var base_form = win.FormPanel.getForm();
			var data = new Object();

			var DispAppoint_Comment = '';
			switch (base_form.findField('DispAppointType_id').getValue().toString()) {
				case '1':
				case '2':
					DispAppoint_Comment = base_form.findField('MedSpecOms_id').getFieldValue('MedSpecOms_Name');
					break;
				case '3':
					DispAppoint_Comment = base_form.findField('ExaminationType_id').getFieldValue('ExaminationType_Name');
					break;
				case '4':
				case '5':
					DispAppoint_Comment = base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Name');
					break;
				case '6':
					DispAppoint_Comment = base_form.findField('LpuSectionBedProfile_id').getFieldValue('LpuSectionBedProfile_Name');
					break;
			}

			data.DispAppointData = {
				DispAppoint_id: base_form.findField('DispAppoint_id').getValue(),
				EvnPLDisp_id: base_form.findField('EvnPLDisp_id').getValue(),
				DispAppointType_id: base_form.findField('DispAppointType_id').getValue(),
				MedSpecOms_id: base_form.findField('MedSpecOms_id').getValue(),
				ExaminationType_id: base_form.findField('ExaminationType_id').getValue(),
				LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
				LpuSectionBedProfile_id: base_form.findField('LpuSectionBedProfile_id').getValue(),
				DispAppointType_Name: base_form.findField('DispAppointType_id').getFieldValue('DispAppointType_Name'),
				DispAppoint_Comment: DispAppoint_Comment
			};

			if ( win.callback(data) === true ) {
				win.hide();
			}
		}
	},
	callback: Ext.emptyFn,
	formMode: 'remote',
	show: function() {
		sw.Promed.swDispAppointEditForm.superclass.show.apply(this, arguments);
		
		this.formStatus = 'edit';
		var win = this;
		win.getLoadMask("Подождите, идет загрузка...").show();
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}

		win.object = 'EvnPLDispDop13';
        if (arguments[0].object)
        {
        	win.object = arguments[0].object;
        }

		win.formMode = 'remote';
        if (arguments[0].formMode)
        {
        	win.formMode = arguments[0].formMode;
        }

		win.EvnPLDisp_consDate = new Date();
        if (arguments[0].EvnPLDisp_consDate)
        {
        	win.EvnPLDisp_consDate = arguments[0].EvnPLDisp_consDate;
        }

		this.callback = Ext.EmptyFn;
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		
		if (arguments[0].DispAppoint_id) {
			this.DispAppoint_id = arguments[0].DispAppoint_id;
		} else {
			this.DispAppoint_id = null;
		}

		var base_form = win.FormPanel.getForm();
		base_form.reset();
		base_form.findField('DispAppointType_id').fireEvent('change', base_form.findField('DispAppointType_id'), base_form.findField('DispAppointType_id').getValue());
		
		if (arguments[0].EvnPLDisp_id) {
			base_form.findField('EvnPLDisp_id').setValue(arguments[0].EvnPLDisp_id);
		}

		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
			base_form.findField('DispAppointType_id').fireEvent('change', base_form.findField('DispAppointType_id'), base_form.findField('DispAppointType_id').getValue());
		}
		
		switch (this.action)
		{
			case 'add':
				this.enableEdit(true);
				win.setTitle('Назначение: Добавление');
				break;
			case 'edit':
				this.enableEdit(true);
				win.setTitle('Назначение: Редактирование');
				break;
			case 'view':
				this.enableEdit(false);
				win.setTitle('Назначение: Просмотр');
				break;
		}

		// фильтруем справочник профилей коек по дате согласия из карты диспансеризации
		base_form.findField('LpuSectionBedProfile_id').getStore().clearFilter();
		base_form.findField('LpuSectionBedProfile_id').lastQuery = '';
		base_form.findField('LpuSectionBedProfile_id').getStore().filterBy(function(rec) {
			return (
				(Ext.isEmpty(rec.get('LpuSectionBedProfile_begDate')) || rec.get('LpuSectionBedProfile_begDate') <= win.EvnPLDisp_consDate)
				&& (Ext.isEmpty(rec.get('LpuSectionBedProfile_endDate')) || rec.get('LpuSectionBedProfile_endDate') >= win.EvnPLDisp_consDate)
			);
		});
		
		if (this.action != 'add') 
		{
			if ( this.formMode == 'remote' ) {
				base_form.load({
					url: '/?c=DispAppoint&m=loadDispAppointGrid',
					params: {
						DispAppoint_id: win.DispAppoint_id
					},
					success: function () {
						win.getLoadMask().hide();
						base_form.findField('DispAppointType_id').fireEvent('change', base_form.findField('DispAppointType_id'), base_form.findField('DispAppointType_id').getValue());
						base_form.findField('DispAppointType_id').focus(true, 100);
					},
					failure: function () {
						win.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih'], function () {
							win.hide();
						});
					}
				});
			} else {
				// do nothing
				win.getLoadMask().hide();
				base_form.findField('DispAppointType_id').focus(true, 100);
			}
		}
		else 
		{
			win.getLoadMask().hide();
			base_form.findField('DispAppointType_id').focus(true, 100);
		}
	},
	initComponent: function() 
	{
		var win = this;

		this.FormPanel = new sw.Promed.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'background:#DFE8F6;padding:5px;',
			id: 'DispAppointEditFormPanel',
			layout: 'form',
			frame: true,
			autoWidth: false,
			region: 'center',
			labelWidth: 200,
			items:
			[
				{
					name: 'DispAppoint_id',
					xtype: 'hidden'
				},
				{
					name: 'EvnPLDisp_id',
					xtype: 'hidden'
				},
				{
					allowBlank: false,
					fieldLabel: 'Назначение',
					hiddenName: 'DispAppointType_id',
					listeners: {
						'select': function(combo, record) {
							var base_form = win.FormPanel.getForm();
							base_form.findField('MedSpecOms_id').setAllowBlank(true);
							base_form.findField('MedSpecOms_id').hideContainer();
							base_form.findField('ExaminationType_id').setAllowBlank(true);
							base_form.findField('ExaminationType_id').hideContainer();
							base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
							base_form.findField('LpuSectionProfile_id').hideContainer();
							base_form.findField('LpuSectionBedProfile_id').setAllowBlank(true);
							base_form.findField('LpuSectionBedProfile_id').hideContainer();

							if (record) {
								var DispAppointType_id = record.get('DispAppointType_id');
								if (DispAppointType_id) {
									switch (DispAppointType_id.toString()) {
										case '1':
										case '2':
											base_form.findField('MedSpecOms_id').showContainer();
											base_form.findField('MedSpecOms_id').setAllowBlank(false);
											base_form.findField('ExaminationType_id').clearValue();
											base_form.findField('LpuSectionProfile_id').clearValue();
											base_form.findField('LpuSectionBedProfile_id').clearValue();
											break;
										case '3':
											base_form.findField('ExaminationType_id').showContainer();
											base_form.findField('ExaminationType_id').setAllowBlank(false);
											base_form.findField('MedSpecOms_id').clearValue();
											base_form.findField('LpuSectionProfile_id').clearValue();
											base_form.findField('LpuSectionBedProfile_id').clearValue();
											break;
										case '4':
										case '5':
											base_form.findField('LpuSectionProfile_id').showContainer();
											base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
											base_form.findField('MedSpecOms_id').clearValue();
											base_form.findField('ExaminationType_id').clearValue();
											base_form.findField('LpuSectionBedProfile_id').clearValue();
											break;
										case '6':
											base_form.findField('LpuSectionBedProfile_id').showContainer();
											base_form.findField('LpuSectionBedProfile_id').setAllowBlank(false);
											base_form.findField('MedSpecOms_id').clearValue();
											base_form.findField('ExaminationType_id').clearValue();
											base_form.findField('LpuSectionProfile_id').clearValue();
											break;
									}
								}
							}
							win.syncShadow();
						},
						'change': function(combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});

							combo.fireEvent('select', combo, combo.getStore().getAt(index));

							return true;
						}
					},
					comboSubject: 'DispAppointType',
					anchor: '100%',
					tabIndex: TABINDEX_DAEF + 1,
					xtype: 'swcommonsprcombo'
				},
				{
					anchor: '100%',
					comboSubject: 'MedSpecOms',
					displayField: 'MedSpecOms_Display',
					fieldLabel: 'Специальность врача назначения',
					hiddenName: 'MedSpecOms_id',
					listWidth: 700,
					moreFields: [
						{ name: 'MedSpecOms_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'MedSpecOms_endDate', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'MedSpecClass_id', type: 'int' },
						{ name: 'MedSpecClass_Code', type: 'int' },
						{ name: 'MedSpecClass_Name', type: 'string' },
						{ name: 'MedSpecClass_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'MedSpecClass_endDate', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'MedSpecOms_Display',
							convert: function(val,row) {
								if ( !Ext.isEmpty(row.MedSpecClass_id) ) {
									return row.MedSpecOms_Name + ' (V021: ' + row.MedSpecClass_Code + ' - ' + row.MedSpecClass_Name + ')';
								}
								else {
									return row.MedSpecOms_Name;
								}
							}
						}
					],
					tabIndex: TABINDEX_DAEF + 2,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">' +
						'<span style="color: red">({MedSpecOms_Code})</span> {MedSpecOms_Name} {[(values.MedSpecClass_id) ? " (V021: " + values.MedSpecClass_Code + " - " + values.MedSpecClass_Name + ")" : "" ]}&nbsp;' +
						'</div></tpl>'
					),
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: 'Вид обследования',
					hiddenName: 'ExaminationType_id',
					comboSubject: 'ExaminationType',
					anchor: '100%',
					tabIndex: TABINDEX_DAEF + 3,
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: 'Профиль медицинской помощи',
					suffix: 'Fed',
					hiddenName: 'LpuSectionProfile_id',
					comboSubject: 'LpuSectionProfile',
					anchor: '100%',
					tabIndex: TABINDEX_DAEF + 4,
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: 'Профиль койки',
					prefix: 'fed_',
					hiddenName: 'LpuSectionBedProfile_id',
					moreFields: [
						{name: 'LpuSectionBedProfile_begDate', mapping: 'LpuSectionBedProfile_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{name: 'LpuSectionBedProfile_endDate', mapping: 'LpuSectionBedProfile_endDate', type: 'date', dateFormat: 'd.m.Y' }
					],
					comboSubject: 'LpuSectionBedProfile',
					anchor: '100%',
					tabIndex: TABINDEX_DAEF + 5,
					xtype: 'swcommonsprcombo'
				}
			],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//alert('success');
				}
			},
			[
				{ name: 'DispAppoint_id' },
				{ name: 'EvnPLDisp_id' },
				{ name: 'DispAppointType_id' },
				{ name: 'MedSpecOms_id' },
				{ name: 'ExaminationType_id' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'LpuSectionBedProfile_id' }
			]
			)
		});
		
		Ext.apply(this,
		{
			border: false,
			items: [this.FormPanel],
			buttons:
			[
				{
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_DAEF + 91,
					iconCls: 'save16',
					handler: function() {
						this.doSave();
					}.createDelegate(this)
				},
				{
					text:'-'
				},
				HelpButton(this, TABINDEX_DAEF + 92),
				{
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_DAEF + 93,
					iconCls: 'cancel16',
					handler: function()
					{
						this.hide();
					}.createDelegate(this)
				}
			]
		});
		
		sw.Promed.swDispAppointEditForm.superclass.initComponent.apply(this, arguments);
	}
});