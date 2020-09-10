/**
* swEditMedStaffFactCommentWindow - окно редактирования примечания на место работы врача
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      04.10.2011
*/

/*NO PARSE JSON*/
sw.Promed.swEditMedStaffFactCommentWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['redaktirovanie_primechaniya_vracha'],
	id: 'EditMedStaffFactCommentWindow',
	layout: 'border',
	maximizable: false,
	width: 350,
	height: 200,
	modal: true,
	codeRefresh: true,
	objectName: 'swEditMedStaffFactCommentWindow',
	objectSrc: '/jscore/Forms/Reg/swEditMedStaffFactCommentWindow.js',
	
	MedStaffFact_id: null,
	
	returnFunc: function(owner) {},
	show: function() 
	{
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['MedStaffFact_id']) {
			this.MedStaffFact_id = arguments[0]['MedStaffFact_id'];
		}
		
		sw.Promed.swEditMedStaffFactCommentWindow.superclass.show.apply(this, arguments);
		var current_window = this;
		
		this.findById('EditMedStaffFactCommentPanel').getForm().load({
			url: C_MSF_COMMENT_GET,
			params:
			{
				MedStaffFact_id: this.MedStaffFact_id
			},
			success: function (form, action)
			{
				this.findById('MedStaffFact_Descr').focus();
			},
			failure: function (form, action)
			{
				if (!action.result.success) {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
					this.hide();
					this.returnFunc(this, -1);
				}
				this.findById('MedStaffFact_Descr').focus();
			},
			scope: this
		}); 
	},
	doSave: function() 
	{
		var form = this.findById('EditMedStaffFactCommentPanel').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('EditMedStaffFactCommentPanel'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		
		//Чтобы не делать hidden поля со значениями, храним данные в объекте и при посылке запроса вручную их передаём
		var post = [];
		post['MedStaffFact_id'] = this.MedStaffFact_id;
		
		form.submit({
			params: post,
			failure: function(result_form, action) 
			{
				if (action.result)
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
					else
					{
						//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
					}
				}
				loadMask.hide();
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				this.hide();
				this.returnFunc();
				
			}.createDelegate(this)
		});
	},

	initComponent: function() 
	{
		var MainPanel = new sw.Promed.FormPanel({
			id:'EditMedStaffFactCommentPanel',
			height:this.height, 
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			layout: 'fit',
			items:
			[{
				anchor: '100%',
				fieldLabel : lang['primechanie'],
				hideLabel: true,
				height: 100,
				name: 'MedStaffFact_Descr',
				xtype: 'textarea',
				autoCreate: {tag: "textarea", autocomplete: "off"},
				id: 'MedStaffFact_Descr'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
				alert('success');
				}
			},
			[
				{ name: 'MedStaffFact_id' },
				{ name: 'MedStaffFact_Descr' }
			]
			),
			url: C_MSF_COMMENT_SAVE
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [MainPanel],
			buttons:
			[{
				text: lang['sohranit'],
				iconCls: 'save16',
				handler: function()
				{
					this.doSave();
				}.createDelegate(this)
			},
			{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			},
			{
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function()
				{
					this.hide();
				}.createDelegate(this)
			}],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.doSave();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swEditMedStaffFactCommentWindow.superclass.initComponent.apply(this, arguments);
	}
});