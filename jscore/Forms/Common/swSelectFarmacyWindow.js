/**
* swSelectFarmacyWindow - окно выбора аптеки, в случае если человек прикреплен к нескольким аптекам
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      19.05.2009
*/

/**
 * swSelectFarmacyWindow - окно выбора аптеки, в случае если человек прикреплен к нескольким аптекам
 *
 * @class sw.Promed.swSelectFarmacyWindow
 * @extends Ext.Window
 */
sw.Promed.swSelectFarmacyWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: false,
	width : 500,
	height : 145,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['vyibor_apteki_otdela'],

	/**
	 * Входящие параметры - список OrgFarmacy_id для отображения в списке выбора
	 * @type {Array}
	 */
	params: null,


	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swSelectFarmacyWindow.superclass.show.apply(this, arguments);
		
		if ( arguments[0].params ) {
			this.params = arguments[0].params;
		}

		var form = this.findById('SelectFarmacyForm');
		
		/*var params = [];
		for (i = 0; i < this.params.length; i++) 
		{
			if ( this.params[i] == record.get('OrgFarmacy_id') ) 
			{
				params[i]
			}
		}*/

		//form.findById('SFW_OrgFarmacy_id').getStore().clearFilter();
		form.findById('SFW_OrgFarmacy_id').setValue(this.params[0]);
		form.findById('SFW_OrgFarmacy_id').getStore().baseParams = '';
		form.findById('SFW_OrgFarmacy_id').getStore().load({
			params: {
				OrgFarmacys:this.params,
				add_without_orgfarmacy_line: 0
			},
			callback: function () 
			{
				form.findById('SFW_OrgFarmacy_id').setValue(form.findById('SFW_OrgFarmacy_id').getValue());
			}
		});
		
		// Выбираем первую в списке
		
		if ( !getGlobalOptions().superadmin ) {
			// Фильтруем ЛПУ, чтобы отображались только те, идентификаторы которых пришли как параметр
			form.findById('SFW_OrgFarmacy_id').getStore().filterBy(function(record, id) {
				var ret = false;
				for (i = 0; i < this.params.length; i++) {
					if ( this.params[i] == record.get('OrgFarmacy_id') ) {
						ret = true;
						break;
					}
				}
				return ret;
			}.createDelegate(this));
		}
		if (this.params.length<=1 && !(getGlobalOptions()['isFarmacyNetAdmin'] && getGlobalOptions()['isFarmacyNetAdmin'] === true) ) {
			form.findById('SFW_OrgFarmacy_id').disable();
			form.findById('SFW_FarmacyOtdel_id').focus(true, 100);
		}
		else {
			form.findById('SFW_OrgFarmacy_id').focus(true, 100);
		}
		
		
		this.buttons[0].enable();
	}, //end show()


	/**
	 * Запрос к серверу после выбора ЛПУ
	 */
	submit: function() {
		var form = this.findById('SelectFarmacyForm').getForm();
		
		if (!form.isValid()) {
			Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'],
					lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		this.buttons[0].disable();
		
		// Выбор 
		combo = form.findField('DrugFinance_id');
		var idx = combo.getStore().indexOfId(combo.getValue());
		if (idx<0)
			idx = combo.getStore().findBy(function(rec) { return rec.get('DrugFinance_id') == combo.getValue(); });
		var Otdel_Name = '';
		if (idx>=0)
		{
			var row = combo.getStore().getAt(idx);
			var Otdel_Name = row.data.DrugFinance_Name;
		}
		
		form.submit({
			params: {
				FarmacyOtdel_Name: Otdel_Name
			},
			success : function(form, action) {
				this.hide();
				getGlobalOptions().OrgFarmacy_Nick = action.result.OrgFarmacy_Nick;
				getGlobalOptions().OrgFamacy_id = action.result.OrgFamacy_id;
				getGlobalOptions().Org_pid = action.result.Org_pid;
				getGlobalOptions().Contragent_id = action.result.Contragent_id;
				getGlobalOptions().Contragent_Name = action.result.Contragent_Name;
				getGlobalOptions().FarmacyOtdel_id = action.result.FarmacyOtdel_id;
				getGlobalOptions().FarmacyOtdel_Name = action.result.FarmacyOtdel_Name;
				Ext.getCmp('menuFarmacyOtdel_Name').setText(getGlobalOptions().FarmacyOtdel_Name);
				//Обновляем данные пользователя в меню
				user_menu.items.items[0].setText('<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'Аптека : '+getGlobalOptions().OrgFarmacy_Nick+'<br/>'+'Отдел : '+getGlobalOptions().FarmacyOtdel_Name);
				
				//alert('ОБРАТИТЕ ВНИМАНИЕ!\n\r'+'Если ВЫ уже занесли все или часть остатков (документы ввода остатков), необходимо войти во все заведенные документы остатков, проставить у них отдел и сохранить.');
				
			}.createDelegate(this),
			failure : function(form, action) {
				if (action.result.Error_Code)
					Ext.Msg.alert("Ошибка", '<b>Ошибка '
									+ action.result.Error_Code
									+ ' :</b><br/> '
									+ action.result.Error_Msg);
			}.createDelegate(this)
		});
	}, //end submit()

	/**
	 * Конструктор
	 */
	initComponent: function() {
    	Ext.apply(this, {
			items : [new Ext.form.FormPanel({
						id : 'SelectFarmacyForm',
						height : 85,
						layout : 'form',
						border : false,
						bodyStyle:'width:100%;background-color:transparent;padding:10px;',
						frame : true,
						labelWidth : 60,
						items : [{
									xtype : 'sworgfarmacycombo',
									width:400,
									id : 'SFW_OrgFarmacy_id',
									tabIndex : 1712,
									hiddenName : 'OrgFarmacy_id',
									hideEmptyRow: true,
									/*listWidth : 500,*/
									enableKeyEvents : true,
									listeners: {
										'blur': function(combo) {
											if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 ) {
												//combo.clearValue();
											}
										},
										'keydown': function (inp, e) {
											if (e.shiftKey == false && e.getKey() == Ext.EventObject.ENTER)
											{
												e.stopEvent();
												this.submit();
											}
										}.createDelegate(this)
									}
								},
								{
									fieldLabel: lang['otdel'],
									width:400,
									id : 'SFW_FarmacyOtdel_id',
									allowBlank: false,
									hiddenName: 'DrugFinance_id',
									tabIndex : 1713,
									xtype: 'swdrugfinancecombo'
								}],
						url : C_USER_SETCURFARMACY
					})],
			buttons : [{
						text : lang['vyibrat'],
						iconCls : 'ok16',
						handler : function(button, event) {
							this.submit();
						}.createDelegate(this)
					}, {
						text: '-'
					},
					HelpButton(this)],
			buttonAlign : "right"
		});
		sw.Promed.swSelectFarmacyWindow.superclass.initComponent.apply(this, arguments);
	} //end initComponent()
});