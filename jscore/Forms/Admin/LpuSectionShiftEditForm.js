/**
* swLpuSectionShiftEditForm - окно просмотра и редактирования участков
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      07.07.2009
*/

sw.Promed.swLpuSectionShiftEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['kolichestvo_smen_koyki'],
	id: 'LpuSectionShiftEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 500,
	height: 220,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lssOk',
		tabIndex: 1424,
		iconCls: 'save16',
		handler: function()
		{
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	}, 
	{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) {
			ShowHelp(this.ownerCt.title);
		}
	},
	{
		text: BTN_FRMCANCEL,
		id: 'lssCancel',
		tabIndex: 1425,
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
			this.ownerCt.returnFunc(this.ownerCt.owner, -1);
		}
	}
	],
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	returnFunc: function(owner, kid) {},
	show: function()
	{
		sw.Promed.swLpuSectionShiftEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionShiftEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].LpuSectionShift_id)
			this.LpuSectionShift_id = arguments[0].LpuSectionShift_id;
		else 
			this.LpuSectionShift_id = null;
		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;
		else 
			this.LpuSection_id = null;
		if (arguments[0].LpuSection_Name)
			this.LpuSection_Name = arguments[0].LpuSection_Name;
		else 
			this.LpuSection_Name = null;
		
		if (!arguments[0])
			{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
			}
		var form = this;
		form.findById('LpuSectionShiftEditFormPanel').getForm().reset();
		
		switch (this.action)
		{
			case 'add':
				form.setTitle(lang['kolichestvo_smen_koyki'] + ': ' + lang['dobavlenie']);
				break;
			case 'edit':
				form.setTitle(lang['kolichestvo_smen_koyki'] + ': ' + lang['redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['kolichestvo_smen_koyki'] + ': ' + lang['prosmotr']);
				break;
		}
		
		if (this.action=='view')
		{
			form.findById('lssLpuSectionShift_Count').disable();
			form.findById('lssLpuSectionShift_setDate').disable();
			form.findById('lssLpuSectionShift_disDate').disable();
			form.findById('lssLpuSection_id').disable();
			form.findById('lssLpuSectionShift_id').disable();
			form.buttons[0].disable();
		}
		else
		{
			form.findById('lssLpuSectionShift_Count').enable();
			form.findById('lssLpuSectionShift_setDate').enable();
			form.findById('lssLpuSectionShift_disDate').enable();
			form.findById('lssLpuSection_id').enable();
			form.findById('lssLpuSectionShift_id').enable();
			form.buttons[0].enable();
		}
		form.findById('lssLpuSection_id').setValue(this.LpuSection_id);
		form.findById('lssLpuSection_Name').setValue(this.LpuSection_Name);
		if (this.action!='add')
		{
			
			form.findById('LpuSectionShiftEditFormPanel').getForm().load(
			{
				url: C_LPUSECTIONSHIFT_GET,
				params:
				{
					object: 'LpuSectionShift',
					LpuSectionShift_id: this.LpuSectionShift_id,
					LpuSectionShift_Count: '',
					LpuSectionShift_setDate: '',
					LpuSectionShift_disDate: '',
					LpuSection_id: ''
				},
				success: function ()
				{
					if (form.action!='view')
						{
							//
						}
					form.findById('lssLpuSectionShift_Count').focus(true, 100);
					loadMask.hide();
				},
				failure: function ()
				{
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
				}
			});
			
		} else {

			loadMask.hide();
			form.findById('lssLpuSectionShift_Count').focus(true, 100);
		}
	},
	doSave: function() 
	{
		var form = this.findById('LpuSectionShiftEditFormPanel');

		if (!form.getForm().isValid()) {

			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var begDate = form.findById('lssLpuSectionShift_setDate').getValue(),
			endDate = form.findById('lssLpuSectionShift_disDate').getValue();

		// проверка: дата начала > даты окончания
		if ((begDate) && (endDate) && (begDate>endDate)) {

			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				fn: function() {
					form.findById('lssLpuSectionShift_setDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var sectionPanel = Ext.getCmp('lpusectiondescription-panel'),
			lpuSectionBegDate = sectionPanel.findById('LpuSection_setDateEdit').getValue(),
			lpuSectionEndDate = sectionPanel.findById('LpuSection_disDateEdit').getValue();

		// проверка: дата начала не может быть раньше даты начала создания отделения
		if (begDate < lpuSectionBegDate) {

			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				fn: function() {
					form.findById('lssLpuSectionShift_setDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_nachala_ne_mozhet_byt_ranshe_daty_sozdanija_otdelenija'],
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		// проверка: дата окончания не может быть позже даты закрытия отделения
		if (lpuSectionEndDate && (endDate > lpuSectionEndDate)) {

			sw.swMsg.show({

				buttons: Ext.Msg.OK,
				fn: function() {
					form.findById('lssLpuSectionShift_disDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchanija_ne_mozhet_byt_pozzhe_daty_zakrytija_otdelenija'],
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		// проверка на дубликат по дате начала теперь внутри save метода
		form.ownerCt.submit();
	},
	submit: function()
	{
		var form = this.findById('LpuSectionShiftEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionShiftEditForm'), { msg: "Подождите, идет сохранение..." });

		loadMask.show();

		form.getForm().submit({

				failure: function(result_form, action) {

					loadMask.hide();

					if (action.result) {
						if (action.result.Error_Code)
							Ext.Msg.alert(
								'Ошибка #'+action.result.Error_Code,
								action.result.Error_Message
							);
					}
				},
				success: function(result_form, action) {

					loadMask.hide();

					if (action.result) {
						if (action.result.LpuSectionShift_id) {

							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuSectionShift_id);

						} else
							Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
					} else
						Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
				}
			});
	},
	initComponent: function()
	{
		this.MainPanel = new sw.Promed.FormPanel(
		{
			id:'LpuSectionShiftEditFormPanel',
			height:this.height,
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			items:
			[
			{
				name: 'LpuSectionShift_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lssLpuSectionShift_id'
			},
			{
				name: 'LpuSection_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lssLpuSection_id'
			},
			{
				name: 'LpuSection_Name',
				disabled: true,
				fieldLabel: lang['otdelenie'],
				tabIndex: 1,
				xtype: 'descfield',
				id: 'lssLpuSection_Name'
			},{
				displayField: 'LpuSectionShift_Count',
				editable: false,
				allowBlank: false,
				fieldLabel: lang['kolichestvo_smen'],
				hiddenName: 'LpuSectionShift_Count',
				name: 'LpuSectionShift_Count',
				id: 'lssLpuSectionShift_Count',
				mode: 'local',
				store: new Ext.data.SimpleStore({
					key: 'LpuSectionShift_Count',
					autoLoad: false,
					fields: [
						{name:'LpuSectionShift_Count',type:'int'}
					],
					data: [
						['1'],
						['2']
					]
				}),
				tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px">{LpuSectionShift_Count}</td>'+
				'</tr></table>'+
				'</div></tpl>',
				valueField: 'LpuSectionShift_Count',
				width: 110,
				xtype: 'swbaselocalcombo'
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['period_deystviya'],
				style: 'padding: 2; padding-left: 5px',
				items: [
				{
					fieldLabel : lang['nachalo'],
					tabIndex: 1421,
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionShift_setDate',
					id: 'lssLpuSectionShift_setDate'
				},
				{
					fieldLabel : lang['okonchanie'],
					tabIndex: 1422,
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionShift_disDate',
					id: 'lssLpuSectionShift_disDate'
				}]
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
				{ name: 'LpuSectionShift_id' },
				{ name: 'LpuSection_id' },
				{ name: 'LpuSectionShift_Count' },
				{ name: 'LpuSectionShift_setDate' },
				{ name: 'LpuSectionShift_disDate' }
			]
			),
			url: C_LPUSECTIONSHIFT_SAVE
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swLpuSectionShiftEditForm.superclass.initComponent.apply(this, arguments);
	}
});