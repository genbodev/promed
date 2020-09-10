/**
* swHomeVisitConfirmWindow - окно одобрения вызова на дом
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
sw.Promed.swHomeVisitConfirmWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['odobrit_vyizov_na_dom'],
	id: 'HomeVisitConfirmWindow',
	layout: 'border',
	maximizable: false,
	width: 350,
	height: 250,
	modal: true,
	codeRefresh: true,
	objectName: 'HomeVisitConfirmWindow',
	objectSrc: '/jscore/Forms/Reg/swHomeVisitConfirmWindow.js',
	HomeVisit_id: null,
	Lpu_id: null,
	LpuRegion_id: null,
	MedPersonalCombo: null,
	returnFunc: function(owner) {},
	filterMSF: function () {
		var win = this;
		var MedStaffFact = win.MainPanel.getForm().findField('MedStaffFact_id');
		var LpuRegion_id = this.LpuRegion_id;
		setMedStaffFactGlobalStoreFilter(win.medstafffact_filter_params);
		MedStaffFact.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		var records = MedStaffFact.getStore().data;
		var callType = this.HomeVisitCallType_id;
		if (callType != 6){
			if ( records.length > 0 && LpuRegion_id && LpuRegion_id != "" ) {
				for (var i = 0; i < records.length; i++) {
					if ( !Ext.isEmpty(records.items[i].get('LpuRegion_List')) && !Ext.isEmpty(records.items[i].get('LpuRegion_MainList')) && LpuRegion_id.toString().inlist(records.items[i].get('LpuRegion_List').toString().split(',')) && LpuRegion_id.toString().inlist(records.items[i].get('LpuRegion_MainList').toString().split(',')) ) {
						MedStaffFact.setValue(records.items[i].get(MedStaffFact.valueField));
						break;
					}
				}
				if (!(MedStaffFact.getValue() > 0)) {
					for (var i = 0; i < records.length; i++) {
						if ( !Ext.isEmpty(records.items[i].get('LpuRegion_List')) && LpuRegion_id.toString().inlist(records.items[i].get('LpuRegion_List').toString().split(',')) ) {
							MedStaffFact.setValue(records.items[i].get(MedStaffFact.valueField));
							break;
						}
					}
				}
				if (!(MedStaffFact.getValue() > 0)) {
					var index = MedStaffFact.getStore().findBy(function(rec){
						return (rec.get('MedPersonal_id') == getGlobalOptions()['CurMedPersonal_id']);
					});
					if(index > -1){
						MedStaffFact.setValue(MedStaffFact.getStore().getAt(index).get('MedStaffFact_id'));
					}
				}
			} else { // Или текущего врача если такого врача или участка нет
				var index = MedStaffFact.getStore().findBy(function(rec){
					return (rec.get('MedPersonal_id') == getGlobalOptions()['CurMedPersonal_id']);
				});
				if(index > -1){
					MedStaffFact.setValue(MedStaffFact.getStore().getAt(index).get('MedStaffFact_id'));
				}
			}
		}
	},
	show: function() 
	{
		if (arguments[0]['callback'])
			this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['HomeVisit_id']) {
			this.HomeVisit_id = arguments[0]['HomeVisit_id'];
		}
		if (arguments[0]['LpuRegion_id']) {
			this.LpuRegion_id = arguments[0]['LpuRegion_id'];
		}
		if (arguments[0]['Lpu_id']) {
			this.Lpu_id = arguments[0]['Lpu_id'];
		}
		if (arguments[0]['CallProfType_id']) {
			this.CallProfType_id = arguments[0]['CallProfType_id'];
		}
		if (arguments[0]['HomeVisit_setDate']) {
			this.HomeVisit_setDate = arguments[0]['HomeVisit_setDate'];
		}
		if (arguments[0]['HomeVisitCallType_id']) {
			this.HomeVisitCallType_id = arguments[0]['HomeVisitCallType_id'];
		}
		
		Ext.getCmp('HomeVisitConfirmPanel').getForm().reset();
		var MedStaffFact = this.MainPanel.getForm().findField('MedStaffFact_id');
		MedStaffFact.clearValue();
		var win = this;
		var lpu = (this.Lpu_id)?this.Lpu_id:getGlobalOptions().lpu_id;
		var HomeVisitDate = (typeof this.HomeVisit_setDate == 'object' ? Ext.util.Format.date(this.HomeVisit_setDate, 'd.m.Y') : this.HomeVisit_setDate);
		this.medstafffact_filter_params = { isDoctor:true, Lpu_id:lpu, withLpuRegionOnly:true, HomeVisitDate:HomeVisitDate };
		if(getRegionNick() == 'kareliya'){
			this.medstafffact_filter_params.isDoctor = false;
		}
		var callProf = this.CallProfType_id;
		var LpuRegionType_HomeVisit = '';
		switch(callProf){
			case 1:
			LpuRegionType_HomeVisit = 'terpedvop';
			break;
			case 2:
			LpuRegionType_HomeVisit = 'stom';
			break;
			default:
			LpuRegionType_HomeVisit = '';
			break;
		}
		if(LpuRegionType_HomeVisit.length > 0){
			this.medstafffact_filter_params.LpuRegionType_HomeVisit = LpuRegionType_HomeVisit;
			if (LpuRegionType_HomeVisit == 'terpedvop') {
				var callType = this.HomeVisitCallType_id;
				if (callType == 6){
					this.medstafffact_filter_params.HomeVisit_onlySpecs = true;
				}
			}
		}
		if(swMedStaffFactGlobalStore.data.length == 0){
			var params = {};
			if(lpu != getGlobalOptions().lpu_id){
				params = {Lpu_id:lpu,mode:'combo'};
				this.medstafffact_filter_params.isAliens = true;
			}
			MedStaffFact.getStore().load({
				params: params,
				callback:function(){
					win.filterMSF(false,MedStaffFact.getStore());
				}
			});
		} else {
			win.filterMSF();
		}
						
		sw.Promed.swHomeVisitConfirmWindow.superclass.show.apply(this, arguments);
	},
	doSave: function() 
	{
		var form = this.findById('HomeVisitConfirmPanel').getForm();
		if (!form.isValid()) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('HomeVisitConfirmPanel').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var loadMask = new Ext.LoadMask(Ext.get('HomeVisitConfirmPanel'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		
		//Чтобы не делать hidden поля со значениями, храним данные в объекте и при посылке запроса вручную их передаём
		var post = [];
		post['HomeVisit_id'] = this.HomeVisit_id;
		var MedStaffFact = this.MainPanel.getForm().findField('MedStaffFact_id');
		var MedStaffFact_id = MedStaffFact.getValue();
		post['MedPersonal_id'] = MedStaffFact.getStore().getById(MedStaffFact_id).get('MedPersonal_id');
		
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
		this.MainPanel = new sw.Promed.FormPanel({
			id:'HomeVisitConfirmPanel',
			height:this.height, 
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			layout: 'fit',
			items:
			[{
				layout: 'form',
				labelWidth: 75,
				items:
				[{
					fieldLabel:lang['vrach'],
					hiddenName:'MedStaffFact_id',
					xtype:'swmedstafffactglobalcombo',
					allowBlank: false,
					width: 220,
					listWidth:700,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;">',
						'<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
						'<td>',
							'<div style="font-weight: bold;">{MedPersonal_Fio}&nbsp;{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</div>',
							'<div style="font-size: 10px;">{PostMed_Name}</div>',
						'</td>',
						'</tr></table>',
						'</div></tpl>'
					),
					anchor:'auto'
				},
				{
					anchor: '100%',
					fieldLabel : lang['primechanie'],
					height: 100,
					name: 'HomeVisit_LpuComment',
					xtype: 'textarea',
					autoCreate: {tag: "textarea", autocomplete: "off"}
				}]
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
				{ name: 'MedPersonal_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'HomeVisit_LpuComment' }
			]
			),
			url: '/?c=HomeVisit&m=takeMP'
		});
		
		Ext.apply(this, 
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel],
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
		sw.Promed.swHomeVisitConfirmWindow.superclass.initComponent.apply(this, arguments);
	}
});