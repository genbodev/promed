/**
 * swTTRScheduleEditDayCommentWindow - окно редактирования примечания на день на ресурс
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Reg
 * @access      public
 * @copyright   Copyright (c) 2011 Swan Ltd.
 * @author		Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version     09.12.2015
 */

/*NO PARSE JSON*/
sw.Promed.swTTRScheduleEditDayCommentWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['redaktirovanie_primechaniya'],
	id: 'TTRScheduleEditDayCommentWindow',
	layout: 'border',
	maximizable: false,
	width: 350,
	height: 200,
	modal: true,
	codeRefresh: true,
	objectName: 'swTTRScheduleEditDayCommentWindow',
	objectSrc: '/jscore/Forms/Reg/swTTRScheduleEditDayCommentWindow.js',

	day: null,
	/**
	 * Ресурс, с расписанием которой идет работа
	 */
	Resource_id: null,

	returnFunc: function(owner) {},
	show: function()
	{
		if ( !getGlobalOptions().groups || (getGlobalOptions().groups.split('|').length == 1 && isCallCenterOperator()) ) {
			sw.swMsg.alert(lang['oshibka'], lang['u_vas_net_prav_na_redaktirovanie']);
			return false;
		}

		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];

		if (arguments[0]['Resource_id']) {
			this.Resource_id = arguments[0]['Resource_id'];
		} else {
			this.Resource_id = null;
		}

		if (arguments[0]['day']) {
			this.day = arguments[0]['day'];
		}

		sw.Promed.swTTRScheduleEditDayCommentWindow.superclass.show.apply(this, arguments);

		this.findById('TTRSScheduleDayCommentEditFormPanel').getForm().findField('ResourceDay_Descr').setValue('');
		this.findById('TTRSScheduleDayCommentEditFormPanel').getForm().load({
			url: C_TTR_DAYCOMMENT_GET,
			params:
			{
				Resource_id: this.Resource_id,
				Day: this.day
			},
			success: function (form, action)
			{
				this.findById('ResourceDay_Descr').focus();
			}.createDelegate(this),
			failure: function (form, action)
			{
				if (!action.result.success) {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
					this.hide();
					this.returnFunc(this, -1);
				}
				this.findById('ResourceDay_Descr').focus();
			}.createDelegate(this),
		});
	},
	doSave: function()
	{
		var form = this.findById('TTRSScheduleDayCommentEditFormPanel').getForm();
		var loadMask = new Ext.LoadMask(Ext.get('TTRSScheduleDayCommentEditFormPanel'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		//Чтобы не делать hidden поля со значениями, храним данные в объекте и при посылке запроса вручную их передаём
		var post = [];
		post['Day'] = this.day;
		post['Resource_id'] = this.Resource_id;

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
		var MainPanel = new sw.Promed.FormPanel(
			{
				id:'TTRSScheduleDayCommentEditFormPanel',
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
						name: 'ResourceDay_Descr',
						xtype: 'textarea',
						autoCreate: {tag: "textarea", autocomplete: "off"},
						id: 'ResourceDay_Descr'
					}],
				reader: new Ext.data.JsonReader({},
					[
						{ name: 'Day_id' },
						{ name: 'Resource_id' },
						{ name: 'ResourceDay_Descr' }
					]
				),
				url: C_TTR_DAYCOMMENT_SAVE
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
		sw.Promed.swTTRScheduleEditDayCommentWindow.superclass.initComponent.apply(this, arguments);
	}
});