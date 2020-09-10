/**
* swTimetableGrafSetConfirmWindow - Предупреждение.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      01.11.2012
*/

sw.Promed.swTimetableGrafSetConfirmWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	modal: true,
	width: 600,
	listeners:{
		'hide':function (win) {
			win.onHide();
		}
	},
	doSave: function() 
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}

		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		base_form.submit({
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
						this.formStatus = 'edit';
					}
				}
			},
			success: function(result_form, action) 
			{
				loadMask.hide();
				if (Ext.isEmpty(action.result.error_list) || action.result.error_list.length == 0) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						msg: lang['vyibrannyie_patsientyi_preduprejdenyi'],
						title: lang['patsientyi_preduprejdenyi']
					});
				} else {
					win.proceedErrorList(action.result.error_list);
				}

				win.callback();
				win.hide();
			}
		});
		
	},
	show: function() 
	{
		sw.Promed.swTimetableGrafSetConfirmWindow.superclass.show.apply(this, arguments);
		
		var current_window = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.proceedErrorList = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.onHide = function() {
			// При отмене всё равно отправляем форму, но с пустым комментарием
			// Будет установлена метка, но письмо отправлено не будет
			this.FormPanel.findById('CRTTG_Moderator_Comment').reset();
			this.doSave();
		}.createDelegate(this);
		
		if (arguments[0].TimetableGraf_ids)
			this.TimetableGraf_ids = arguments[0].TimetableGraf_ids;
		else 
			this.TimetableGraf_ids = null;
			
		if (arguments[0].callback) 
		{
			this.callback = arguments[0].callback;
		}
		if (arguments[0].proceedErrorList)
		{
			this.proceedErrorList = arguments[0].proceedErrorList;
		}
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) 
		{
			this.formMode = arguments[0].formMode;
		}
		if (arguments[0].owner) 
		{
			this.owner = arguments[0].owner;
		}

		base_form.findField('TimetableGraf_ids').setValue(Ext.util.JSON.encode(this.TimetableGraf_ids));
		base_form.findField('Moderator_Comment').focus(true, 100);
		
	},	
	initComponent: function() 
	{
		
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 150,
			url:'/?c=TimetableGraf&m=confirmMultiRecTTGModeration',
			items: 
			[{
				name: 'TimetableGraf_ids',
				xtype: 'hidden'
			}, {
				id: 'CRTTG_Moderator_Comment',
				fieldLabel: lang['vvedite_tekst_preduprejdeniya'],
				name: 'Moderator_Comment',
				allowBlank: true,
				xtype: 'textarea', 
				width: 400
			}],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'TimetableGraf_ids'},
				{name: 'Moderator_Comment'}
			])
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'ok16',
				text: lang['otpravit']
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swTimetableGrafSetConfirmWindow.superclass.initComponent.apply(this, arguments);
	},
	title: lang['preduprejdenie']
});