/**
* swEditLpuSectionCommentWindow - окно редактирования примечания на отделение
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
sw.Promed.swEditLpuSectionCommentWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['redaktirovanie_primechaniya_vracha'],
	id: 'EditLpuSectionCommentWindow',
	layout: 'border',
	maximizable: false,
	width: 350,
	height: 200,
	modal: true,
	codeRefresh: true,
	objectName: 'swEditLpuSectionCommentWindow',
	objectSrc: '/jscore/Forms/Reg/swEditLpuSectionCommentWindow.js',
	
	LpuSection_id: null,
	
	returnFunc: function(owner) {},
	show: function() 
	{	if (sw.Promed.MedStaffFactByUser.current.ARMType == 'common' ||
			['regpol','regpol6'].in_array(sw.Promed.MedStaffFactByUser.current.ARMType))
		{
			if (arguments[0]['callback'])
				this.returnFunc = arguments[0]['callback'];
			
			if (arguments[0]['LpuSection_id']) {
				this.LpuSection_id = arguments[0]['LpuSection_id'];
			}
			
			sw.Promed.swEditLpuSectionCommentWindow.superclass.show.apply(this, arguments);
			var current_window = this;
			
			this.findById('EditLpuSectionCommentPanel').getForm().load({
				url: C_LS_COMMENT_GET,
				params:
				{
					LpuSection_id: this.LpuSection_id
				},
				success: function (result_form, action)
				{
					this.findById('LpuSection_Descr').focus();
				},
				failure: function ()
				{
					if (!action.result.success) {
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
						this.hide();
						this.returnFunc(this, -1);
					}
					this.findById('LpuSection_Descr').focus();
				},
				scope: this
			}); 
		}	else {
			Ext.Msg.alert(lang['oshibka'], lang['u_vas_net_prav_na_redaktirovanie']);
		}
	},
	doSave: function() 
	{
		var form = this.findById('EditLpuSectionCommentPanel').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('EditLpuSectionCommentPanel'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		
		//Чтобы не делать hidden поля со значениями, храним данные в объекте и при посылке запроса вручную их передаём
		var post = [];
		post['LpuSection_id'] = this.LpuSection_id;
		
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
			id:'EditLpuSectionCommentPanel',
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
				name: 'LpuSection_Descr',
				xtype: 'textarea',
				autoCreate: {tag: "textarea", autocomplete: "off"},
				id: 'LpuSection_Descr'
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
				alert('success');
				}
			},
			[
				{ name: 'LpuSection_id' },
				{ name: 'LpuSection_Descr' }
			]
			),
			url: C_LS_COMMENT_SAVE
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
					return true;
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swEditLpuSectionCommentWindow.superclass.initComponent.apply(this, arguments);
	}
});