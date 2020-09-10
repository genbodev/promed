/**
* swHeredityDiagEditForm - окно просмотра и редактирования наследственности по заболеваниям
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009-2013 Swan Ltd.
* @author       
* @version      22.05.2013
* @comment      префикс HDEF
*/
/*NO PARSE JSON*/

sw.Promed.swHeredityDiagEditForm = Ext.extend(sw.Promed.BaseForm, {
	layout: 'form',
	title: lang['nasledstvennost_po_zabolevaniyu'],
	id: 'HeredityDiagEditForm',
	width: 450,
	autoHeight: true,
	modal: true,
	formStatus: 'edit',
	doSave: function(options)  {
		if ( typeof options != 'object' ) {
			options = new Object();
		}
		var win = this;
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}
		win.formStatus = 'save';
		var form = this.FormPanel;
		var base_form = form.getForm();
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
		// #181668 пока убрали контроль кроме Вологды
		// NGS: AN ADDITIONAL CHECK IS NOT NEEDED ANYMORE FOR VOLOGDA - #194032
		if(!(getRegionNick().inlist([/*'vologda'*/]) && win.object && win.object.inlist(['EvnPLDispDop13','EvnPLDispProf']))){
			options.ignoreCheckDiag = true;
		}
		//Проверка диагноза на наличие в EvnDiagDopDisp
		var Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
		var GroupDiag_Code = Diag_Code.slice(0,3);
		if (
			options.ignoreCheckDiag != true
		) {
			win.formStatus = 'edit';
			win.getLoadMask(langs("Подождите, идет проверка диагноза...")).show();
			
			Ext.Ajax.request({
				url: '/?c=EvnPLDispDop13&m=CheckDiag',
				params: {
					EvnPLDispDop13_id: base_form.findField('EvnPLDisp_id').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue()
				},
				failure: function(result_form, action) {
					win.getLoadMask().hide();
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								options.ignoreCheckDiag = true;
								win.doSave(options);
							}
							else {
								win.formStatus = 'edit';
							}
						},
						msg: langs('Ошибка при проверке на дублирование диагноза. Продолжить сохранение?'),
						title: langs('Подтверждение сохранения')
					});
				},
				success: function(response, action) {
					win.getLoadMask().hide();

					if (response.responseText != '') {
						var data = Ext.util.JSON.decode(response.responseText);
						if (data) {//иначе - проверка успешна, пересечений диагнозов нет
							var msg = '';
							
							if(data == base_form.findField('Diag_id').getValue()) {
								sw.swMsg.alert(langs('Ошибка'), langs('У пациента уже указан диагноз')+' <b>'+Diag_Code+'</b><br>'
									+langs('Проверьте правильность введенных данных.'),
									function() {
										win.formStatus = 'edit';
										base_form.findField('Diag_id').focus(true);
									}.createDelegate(this)
								);
							} else {
								sw.swMsg.show({
									buttons: {yes: langs('Продолжить'), no: langs('Отмена')},
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											options.ignoreCheckDiag = true;
											win.doSave(options);
										} else {
											win.formStatus = 'edit';
											base_form.findField('Diag_id').focus(true);
										}
									},
									msg: langs('У пациента уже указан диагноз группы')+' <b>'+GroupDiag_Code+'</b>',
									title: langs('Подтверждение сохранения'),
									width: 300
								});
							}
						} else {
							options.ignoreCheckDiag = true;
							win.doSave(options);
						}
					}
				}
			});

			win.formStatus = 'edit';
			return false;
		}
		
		win.getLoadMask("Подождите, идет сохранение...").show();
		form.getForm().submit(
		{
			url: '/?c=HeredityDiag&m=saveHeredityDiag',
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				win.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				win.getLoadMask().hide();
				if (action.result) 
				{
					if (action.result.HeredityDiag_id) 
					{
						win.hide();
						win.callback(win.owner, action.result.HeredityDiag_id);
					}
					else
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
				}
				else
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
			}
		});
	},
	callback: Ext.emptyFn,
	show: function() {
		sw.Promed.swHeredityDiagEditForm.superclass.show.apply(this, arguments);
		
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
		
		if (arguments[0].HeredityDiag_id) {
			this.HeredityDiag_id = arguments[0].HeredityDiag_id;
		} else {
			this.HeredityDiag_id = null;
		}

		var base_form = win.FormPanel.getForm();
		base_form.reset();
		
		if (arguments[0].EvnPLDisp_id) {
			base_form.findField('EvnPLDisp_id').setValue(arguments[0].EvnPLDisp_id);
		}
		
		switch (this.action)
		{
			case 'add':
				this.enableEdit(true);
				win.setTitle(lang['nasledstvennost_po_zabolevaniyu_dobavlenie']);
				break;
			case 'edit':
				this.enableEdit(true);
				win.setTitle(lang['nasledstvennost_po_zabolevaniyu_redaktirovanie']);
				break;
			case 'view':
				this.enableEdit(false);
				win.setTitle(lang['nasledstvennost_po_zabolevaniyu_prosmotr']);
				break;
		}
		
		if (this.action != 'add') 
		{
			base_form.load(
			{
				url: '/?c=HeredityDiag&m=loadHeredityDiagGrid',
				params: 
				{
					HeredityDiag_id: win.HeredityDiag_id
				},
				success: function() 
				{
					win.getLoadMask().hide();

					var diag_combo = base_form.findField('Diag_id');
					if ( !Ext.isEmpty(diag_combo.getValue()) ) {
						diag_combo.getStore().load({
							callback: function() {
								diag_combo.getStore().each(function(record) {
									if ( record.get('Diag_id') == diag_combo.getValue() ) {
										diag_combo.fireEvent('select', diag_combo, record, 0);
									}
								});
							},
							params: { where: "where Diag_id = " + diag_combo.getValue() }
						});
					}
					base_form.findField('Diag_id').focus(true, 100);
				},
				failure: function() 
				{
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih'], function() { win.hide(); } );
				}
			});
		} 
		else 
		{
			win.getLoadMask().hide();
			base_form.findField('Diag_id').focus(true, 100);
		}
	},
	initComponent: function() 
	{
		this.FormPanel = new sw.Promed.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'background:#DFE8F6;padding:5px;',
			id: 'HeredityDiagEditFormPanel',
			layout: 'form',
			frame: true,
			autoWidth: false,
			region: 'center',
			labelWidth: 130,
			items:
			[
				{
					name: 'HeredityDiag_id',
					xtype: 'hidden'
				},
				{
					name: 'EvnPLDisp_id',
					xtype: 'hidden'
				},
				{
					allowBlank: false,
/*
					// Закомментировано, ибо https://redmine.swan.perm.ru/issues/20964
					baseFilterFn: function(rec) {
						return (typeof rec == 'object' && !Ext.isEmpty(rec.get('Diag_Code')) && rec.get('Diag_Code').inlist([ 'Z03.4', 'I64.', 'C16.9' ]));
					},
*/
					//Добавил, ибо #168522
					baseFilterFn: function(rec) {
						var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
						if (Ext.isEmpty(Diag_Code)) return false;
						return (
							(Diag_Code.substr(0,1) < 'V' || Diag_Code.substr(0,1) > 'Y')
						);
					},
					fieldLabel: langs('Диагноз'),
					hiddenName: 'Diag_id',
					// isHeredityDiag: true,
					width: 250,
					tabIndex: TABINDEX_HDEF + 1,
					xtype: 'swdiagcombo'
				},
				{
					allowBlank: false,
					comboSubject: 'HeredityType',
					fieldLabel: lang['nasledstvennost'],
					hiddenName: 'HeredityType_id',
					tabIndex: TABINDEX_HDEF + 2,
					width: 250,
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
				{ name: 'HeredityDiag_id' },
				{ name: 'EvnPLDisp_id' },
				{ name: 'Diag_id' },
				{ name: 'HeredityType_id' }
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
					tabIndex: TABINDEX_HDEF + 91,
					iconCls: 'save16',
					handler: function() {
						this.doSave();
					}.createDelegate(this)
				},
				{
					text:'-'
				},
				HelpButton(this, TABINDEX_HDEF + 92),
				{
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_HDEF + 93,
					iconCls: 'cancel16',
					handler: function()
					{
						this.hide();
					}.createDelegate(this)
				}
			]
		});
		
		sw.Promed.swHeredityDiagEditForm.superclass.initComponent.apply(this, arguments);
	}
});