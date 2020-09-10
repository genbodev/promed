/**
* swEvnReceptNotificationEditWindow - окно Оповещение при отсрочкеии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Нигматуллин Тагир
* @version      апрель 2017
*/

sw.Promed.swEvnReceptNotificationEditWindow =  Ext.extend(sw.Promed.BaseForm, {
	title: "Оповещение при отсрочке",
	text: "Оповещение при отсрочке",
	id: 'swEvnReceptNotificationEditWindow',
	border: false,
	codeRefresh: true,
	width: 700,
	height: 400,
	maximizable: false,
	maximized: false,
	modal:true,
	callback:Ext.emptyFn,
	//layout:'fit',
	closeAction: 'hide',
	onHide: Ext.emptyFn,
   
    saveRecord: function ($action) {
		var $formParams = Ext.getCmp('swEvnReceptNotificationEditWindow').formParams;
		var params = {};
			
		params.EvnRecept_id = $formParams.EvnRecept_id;
		params.EvnRecept_obrDate = $formParams.EvnRecept_obrDate;
		params.Org_id = getGlobalOptions().org_id;
		params.receptNotification_phone = Ext.getCmp('receptNotification_phone').getValue();

		Ext.Ajax.request({
			url: '/?c=Farmacy&m=putEvnReceptOnDelay',
			method: 'POST',
			params: params,
			success: function(response, opts) {
				if (response.responseText.length > 0) {
					var result = Ext.util.JSON.decode(response.responseText);
				}
				if (this.formParams.parent_id == 'swWorkPlaceDistributionPointWindow') {
					var $params = {};
					$params.receptNotification_phone = params.receptNotification_phone;
					Ext.getCmp(this.formParams.parent_id).fireEvent('success', this.formParams.parent_id, $params);
				}
				this.callback();
				Ext.getCmp('swEvnReceptNotificationEditWindow').hide();
			}.createDelegate(this)
		});
    },
     initComponent: function() {
         
		Ext.apply(this, {
			frame: true,           
			labelWidth : 150,  
			bodyBorder : true,
			layout : "form",
			cls: 'tg-label',
			autoHeight: true,

			items : [
			
			new Ext.form.FormPanel({
				frame: true,
				id: 'ReceptNotification_FormPlanPanel', 
                                //layout: 'form',
                                labelWidth: 150,
				items : [
				{
		   
					height : 10,
					border : false,
					cls: 'tg-label'
				}, 
                                {
                                    autoLoad: false,
                                    fieldLabel: lang['naimenovanie_apteki'],
                                    
                                    id: 'ReceptNotification_Apteka',
                                    width: 300,
                                    //tabIndex: TABINDEX_MANTUPURPFRM + 22,
                                    xtype: 'textfield'
                                },
                                 {
                                    autoLoad: false,
                                    fieldLabel: 'Телефон для оповещения',
                                    id: 'receptNotification_phone',
                                    //allowBlank: false,
                                    width: 500,
                                    //tabIndex: TABINDEX_MANTUPURPFRM + 22,
                                    xtype: 'textfield'
                                },
				

				{
					height : 20,
					border : false
				//            style: 'padding: 0px;'
				}, 
			 
				{
					height : 20,
					border : false
				}
		 
				]
			})
			//             }
			],
			buttons: [
                            {
                            text : lang['sohranit'],
                            id: 'ReceptNotification_Save', 
                            conCls: 'save16',
                            handler: function(b) { 
                            this.saveRecord();
                        }.createDelegate(this)                     

			},
			{
				text: '-'
			},
			HelpButton(this, TABINDEX_STARTVACFORMPLAN + 7),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'EPLSIF_CancelButton',
				text: '<u>З</u>акрыть',
				tabIndex: TABINDEX_STARTVACFORMPLAN + 8
			} ]
		}
		);   
		          
         sw.Promed.swEvnReceptNotificationEditWindow.superclass.initComponent.apply(this, arguments);
     },

	show: function(record) {
		sw.Promed.swEvnReceptNotificationEditWindow.superclass.show.apply(this, arguments);

		this.formParams = record;
		this.formParams.readOnly = false;
		this.callback = Ext.emptyFn;
		if (record.callback && typeof record.callback == 'function') {
			this.callback = record.callback;
		}

		Ext.getCmp('receptNotification_phone').setValue('');
		//   Окно предназначено только на добавление
		if (record.action == 'view') {
			this.setTitle(this.text + lang['_prosmotr']);
			this.formParams.readOnly = true;
		} else if (record.action == 'add') {
			this.setTitle(this.text + lang['_dobavlenie']);
		} else if (record.action == 'edit') {
			this.setTitle(this.text + lang['_redaktirovanie']);
		}
		Ext.getCmp('ReceptNotification_Apteka').setValue(getGlobalOptions().OrgFarmacy_Nick)
		Ext.getCmp('ReceptNotification_Apteka').setDisabled(true);
		Ext.getCmp('ReceptNotification_Save').setDisabled( this.formParams.readOnly);
	}

});

