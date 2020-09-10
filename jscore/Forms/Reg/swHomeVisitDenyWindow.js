/**
* swHomeVisitDenyWindow - окно отказа в вызове на дом
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      23.09.2013
*/

/*NO PARSE JSON*/
sw.Promed.swHomeVisitDenyWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['otkazat_v_vyizove_na_dom'],
	id: 'HomeVisitDenyWindow',
	layout: 'border',
	maximizable: false,
	width: 350,
	height: 250,
	modal: true,
	codeRefresh: true,
	objectName: 'HomeVisitDenyWindow',
	objectSrc: '/jscore/Forms/Reg/swHomeVisitDenyWindow.js',
	
	HomeVisit_id: null,
	LpuRegion_id: null,
	MedPersonalCombo: null,
	
	returnFunc: function(owner) {},

	show: function() 
	{
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['HomeVisit_id']) {
			this.HomeVisit_id = arguments[0]['HomeVisit_id'];
		}
		
		Ext.getCmp('HomeVisitDenyPanel').getForm().reset();
						
		sw.Promed.swHomeVisitDenyWindow.superclass.show.apply(this, arguments);
	},
	doSave: function() 
	{
		var form = this.findById('HomeVisitDenyPanel').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('HomeVisitDenyPanel'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		
		//Чтобы не делать hidden поля со значениями, храним данные в объекте и при посылке запроса вручную их передаём
		var post = [];
		post['HomeVisit_id'] = this.HomeVisit_id;
		
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
			id:'HomeVisitDenyPanel',
			height:this.height, 
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			layout: 'form',
			labelWidth: 75,
			items:
			[{
				allowBlank: false,
				anchor: '100%',
				fieldLabel : lang['prichina_otkaza'],
				height: 150,
				name: 'HomeVisit_LpuComment',
				xtype: 'textarea',
				autoCreate: {tag: "textarea", autocomplete: "off"}
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
				alert('success');
				}
			},
			[
				{ name: 'HomeVisit_id' },
				{ name: 'HomeVisit_LpuComment' }
			]
			),
			url: C_HOMEVISIT_DENY
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
		sw.Promed.swHomeVisitDenyWindow.superclass.initComponent.apply(this, arguments);
	}
});