/**
* swSelectOtdelWindow - окно выбора аптеки, в случае если человек прикреплен к нескольким аптекам
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Swan Coders
* @version      27.01.2010
*/

/**
 * swSelectOtdelWindow - окно выбора аптеки, в случае если человек прикреплен к нескольким аптекам
 *
 * @class sw.Promed.swSelectOtdelWindow
 * @extends Ext.Window
 */
sw.Promed.swSelectOtdelWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: false,
	width : 500,
	height : 170,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	title: lang['vyibor_otdela'],
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		}
	},
	show: function() 
	{
		sw.Promed.swSelectOtdelWindow.superclass.show.apply(this, arguments);
		var form = this.findById('SelectOtdelForm');
		if ((!arguments[0]) || (!arguments[0].DocumentUc_id)/* || (!arguments[0].DrugFinance_id)*/) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}		
		this.DocumentUc_id = arguments[0].DocumentUc_id;
		if (arguments[0].DrugFinance_id)
			this.DrugFinance_id = arguments[0].DrugFinance_id;
		if (arguments[0].callback)
			this.callback = arguments[0].callback;
		if (arguments[0].onHide)
			this.onHide = arguments[0].onHide;		
		form.findById('sowFarmacyOtdel_id').setValue(this.DrugFinance_id || getGlobalOptions().FarmacyOtdel_id);
		form.findById('sowDocumentUc_id').setValue(this.DocumentUc_id);
		
		this.buttons[0].enable();
		this.buttons[0].focus();
	}, 


	/**
	 * Запрос к серверу после выбора ЛПУ
	 */
	submit: function() 
	{
		var form = this.findById('SelectOtdelForm').getForm();
		
		if (!form.isValid()) 
		{
			Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}
		this.buttons[0].disable();
		form.submit(
		{
			params: 
			{
				action: 'take'
				//FarmacyOtdel_id: form.findField('DrugFinance_id').getValue()
			},
			success : function(form, action) 
			{
				if (action.result) 
				{
					this.hide();
					this.callback();
				}
				// Так же 
			}.createDelegate(this),
			failure : function(form, action) 
			{
				this.buttons[0].enable();
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
	initComponent: function() 
	{
		this.SelectOtdelForm = new Ext.form.FormPanel(
		{
			id: 'SelectOtdelForm',
			autoHeight: true,
			layout: 'form',
			border: false,
			bodyStyle:'width:100%;background-color:transparent;padding:10px;',
			frame: true,
			labelWidth: 60,
			items: 
			[{
				height: 60,
				id : 'sowText',
				xtype: 'panel',
				html: '<div style="font-weight: bold; font-size:12px;">По выбранной приходной накладной будет создан документ прихода в указанный отдел. <br/>'+
					'<span style="color:red;">(Определение отдела для прихода автоматическое и отдел не может быть изменен вручную)</span></div>'
			},
			{
				id : 'sowDocumentUc_id',
				name: 'DocumentUc_id',
				value: null,
				xtype: 'hidden'
			},
			{
				fieldLabel: lang['otdel'],
				width: 400,
				disabled: true,
				id : 'sowFarmacyOtdel_id',
				allowBlank: false,
				hiddenName: 'DrugFinance_id',
				tabIndex : 1713,
				xtype: 'swdrugfinancecombo'
			}],
			url: '/?c=Farmacy&m=save&method=DokNak'
		});
    Ext.apply(this, 
		{
			items : [this.SelectOtdelForm],
			buttons : 
			[{
				text : lang['prinyat'],
				iconCls : 'ok16',
				handler : function(button, event) 
				{
					this.submit();
				}.createDelegate(this)
			}, 
			{
				text: '-'
			},
			{
				id: 'sowCancelButton',
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: lang['otmena']
			}, 
			HelpButton(this)
			],
			buttonAlign : "right"
		});
		sw.Promed.swSelectOtdelWindow.superclass.initComponent.apply(this, arguments);
	}
});