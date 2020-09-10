/**
* swEvnDiagDopDispEditForm - окно просмотра и редактирования наследственности по заболеваниям
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
* @comment      префикс EDDDEF
*/
/*NO PARSE JSON*/

sw.Promed.swEvnDiagDopDispEditForm = Ext.extend(sw.Promed.BaseForm, {
	layout: 'form',
	title: lang['diagnoz'],
	id: 'EvnDiagDopDispEditForm',
	width: 550,
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
					EvnPLDispDop13_id: base_form.findField('EvnDiagDopDisp_pid').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue(),
					DeseaseDispType_id: base_form.findField('DeseaseDispType_id').getValue()
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
						if (data) {
							var msg = '';
							
							if(data == -1) {//совпадение с диагнозами в случаях лечения и картах дисп.учета
								sw.swMsg.alert(langs('Ошибка'), langs('У пациента уже установлен диагноз')+' <b>'+Diag_Code+'</b><br>'
									+langs('Проверьте правильность введенных данных.'),
									function() {
										win.formStatus = 'edit';
										base_form.findField('Diag_id').focus(true);
									}.createDelegate(this)
								);
							} else if(data == base_form.findField('Diag_id').getValue()) {
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

		if (base_form.findField('DeseaseDispType_id').getValue() == 2) {
			//проверяем наличие карты диспансеризации для определенных групп диагноза
			var loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Проверка наличия карты диспансеризации...' });
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=EvnDiagDopDisp&m=checkDiagDisp',
				params: {
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue()
				},
				callback: function (options, success, response) {
					loadMask.hide();
					if (response.responseText != '') {
						var data = Ext.util.JSON.decode(response.responseText);

						if (!data.result && data.success) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId) {
									if (buttonId == 'yes') {
										var formParams = new Object();
										var params_disp = new Object();

										formParams.Person_id = data.Person_id;
										formParams.Server_id = base_form.findField('Server_id').getValue();
										formParams.PersonDisp_begDate = getGlobalOptions().date;
										formParams.PersonDisp_DiagDate = getGlobalOptions().date;
										formParams.Diag_id = base_form.findField('Diag_id').getValue();

										params_disp.action = 'add';
										params_disp.callback = Ext.emptyFn;
										params_disp.formParams = formParams;
										params_disp.onHide = Ext.emptyFn;

										getWnd('swPersonDispEditWindow').show(params_disp);
									}
								},
								msg: langs('Пациент с диагнозом ' + base_form.findField('Diag_id').getFieldValue('Diag_Code') + ' нуждается в диспансерном наблюдении. Создать карту диспансерного наблюдения?'),
								title: langs('Подтверждение сохранения')
							});
						}
					}
				}
			});
		}

		win.getLoadMask("Подождите, идет сохранение...").show();
		form.getForm().submit(
		{
			url: '/?c=EvnDiagDopDisp&m=saveEvnDiagDopDisp',
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
					if (action.result.EvnDiagDopDisp_id) 
					{
						win.hide();
						win.callback(win.owner, action.result.EvnDiagDopDisp_id);
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
		sw.Promed.swEvnDiagDopDispEditForm.superclass.show.apply(this, arguments);
		
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

		this.callback = Ext.emptyFn;
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		
		if (arguments[0].EvnDiagDopDisp_id) {
			this.EvnDiagDopDisp_id = arguments[0].EvnDiagDopDisp_id;
		} else {
			this.EvnDiagDopDisp_id = null;
		}

		var base_form = win.FormPanel.getForm();
		base_form.reset();
		
		if (arguments[0].EvnPLDisp_id) {
			base_form.findField('EvnDiagDopDisp_pid').setValue(arguments[0].EvnPLDisp_id);
		}
		
		if (arguments[0].PersonEvn_id) {
			base_form.findField('PersonEvn_id').setValue(arguments[0].PersonEvn_id);
		}
		
		if (arguments[0].Server_id) {
			base_form.findField('Server_id').setValue(arguments[0].Server_id);
		}
		
		this.formName = lang['diagnoz'];
		
		if (arguments[0].DeseaseDispType_id) {
			base_form.findField('DeseaseDispType_id').setValue(arguments[0].DeseaseDispType_id);
			if (arguments[0].DeseaseDispType_id == 1) {
				// скрываем поле тип 
				base_form.findField('DiagSetClass_id').setValue(1);
				base_form.findField('DiagSetClass_id').hideContainer();
				base_form.findField('EvnDiagDopDisp_setDate').showContainer();
				this.formName = lang['ranee_izvestnoe_imeyuscheesya_zabolevanie'];
			} else {
				base_form.findField('DiagSetClass_id').showContainer();
				base_form.findField('EvnDiagDopDisp_setDate').hideContainer();
				this.formName = lang['vpervyie_vyiyavlennoe_zabolevanie'];
			}
			win.syncShadow();
		}
		
		switch (this.action)
		{
			case 'add':
				this.enableEdit(true);
				win.setTitle(this.formName + lang['_dobavlenie']);
				break;
			case 'edit':
				this.enableEdit(true);
				win.setTitle(this.formName + lang['_redaktirovanie']);
				break;
			case 'view':
				this.enableEdit(false);
				win.setTitle(this.formName + lang['_prosmotr']);
				break;
		}
		
		if (this.action != 'add') 
		{
			base_form.load(
			{
				url: '/?c=EvnDiagDopDisp&m=loadEvnDiagDopDispGrid',
				params: 
				{
					EvnDiagDopDisp_id: win.EvnDiagDopDisp_id
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
			id: 'EvnDiagDopDispEditFormPanel',
			layout: 'form',
			frame: true,
			autoWidth: false,
			region: 'center',
			labelWidth: 180,
			items:
			[
				{
					name: 'EvnDiagDopDisp_id',
					xtype: 'hidden'
				},
				{
					name: 'EvnDiagDopDisp_pid',
					xtype: 'hidden'
				},
				{
					name: 'DeseaseDispType_id',
					xtype: 'hidden'
				},
				{
					name: 'PersonEvn_id',
					xtype: 'hidden'
				},
				{
					name: 'Server_id',
					xtype: 'hidden'
				},
				{
					// В Mongo не сработает
					// additQueryFilter: "(Diag_Code in ('Z03.4', 'I20.9', 'I67.9', 'O24.3', 'K29.7', 'N28.8', 'A16.2', 'I64.') or substr(Diag_Code, 1, 1) = 'C')",
					allowBlank: false,
/*
					// Закомментировано, ибо https://redmine.swan.perm.ru/issues/20964
					baseFilterFn: function(rec) {
						return (
							typeof rec == 'object'
							&& !Ext.isEmpty(rec.get('Diag_Code'))
							&& (rec.get('Diag_Code').inlist([ 'Z03.4', 'I20.9', 'I67.9', 'O24.3', 'K29.7', 'N28.8', 'A16.2', 'I64.' ]) || rec.get('Diag_Code').substr(0, 1) == 'C')
						);
					},
*/
					// Добавил, ибо #168522
					baseFilterFn: function(rec) {
						var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
						if (Ext.isEmpty(Diag_Code)) return false;
						return (
							(Diag_Code.substr(0,1) < 'V' || Diag_Code.substr(0,1) > 'Y')
						);
					},

					fieldLabel: langs('Диагноз'),
					hiddenName: 'Diag_id',
					// isEvnDiagDopDispDiag: true,
					anchor: '100%',
					tabIndex: TABINDEX_EDDDEF + 1,
					xtype: 'swdiagcombo'
				},
				{
					fieldLabel: lang['data_postanovki_diagnoza'],
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'EvnDiagDopDisp_setDate',
					width: 100,
					xtype: 'swdatefield'
				}
				,
				{
					allowBlank: false,
					comboSubject: 'DiagSetClass',
					fieldLabel: lang['tip'],
					hiddenName: 'DiagSetClass_id',
					loadParams: {params: {where: ' where DiagSetClass_Code in (1,3)'}},
					tabIndex: TABINDEX_EDDDEF + 2,
					anchor: '100%',
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
				{ name: 'EvnDiagDopDisp_id' },
				{ name: 'EvnDiagDopDisp_setDate' },
				{ name: 'EvnDiagDopDisp_pid' },
				{ name: 'Diag_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'DiagSetClass_id' }
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
					tabIndex: TABINDEX_EDDDEF + 91,
					iconCls: 'save16',
					handler: function() {
						this.doSave();
					}.createDelegate(this)
				},
				{
					text:'-'
				},
				HelpButton(this, TABINDEX_EDDDEF + 92),
				{
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_EDDDEF + 93,
					iconCls: 'cancel16',
					handler: function()
					{
						this.hide();
					}.createDelegate(this)
				}
			]
		});
		
		sw.Promed.swEvnDiagDopDispEditForm.superclass.initComponent.apply(this, arguments);
	}
});