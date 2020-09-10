/**
* amm_vacGetComboVal - окно просмотра формы редактирования параметра
*
* PromedWeb - The New Generation of Medical Statistic Software
*
* @copyright    Copyright (c) 2012 
* @author       
* @version      20.07.2012
* @comment      
*/

sw.Promed.amm_vacGetComboVal = Ext.extend(Ext.Window, {
	id: 'amm_vacGetComboVal',
	title: "test",
	border: false,
	width: 400,
	height: 200,
	maximizable: false,  
	closeAction: 'hide',
	layout: 'border',
	codeRefresh: true,
	modal:true,
	
	initComponent: function() {
		var params = new Object();
		var form = this;
		
//    /*
//    * хранилище для доп сведений
//    */
//    this.formStore = new Ext.data.JsonStore({
//      fields: ['vacJournalAccount_id', 'VaccineWay_name', 'Doza', 'StatusType_id'],
//      url: '/?c=VaccineCtrl&m=loadRefuseFormInfo',
//      key: 'vacJournalAccount_id',
//      root: 'data'
//    });
		
		Ext.apply(this, {
			formParams: null,
			buttons: [{
				text: '-'
			},
//      HelpButton(this),
			{text: 'OK',
				tabIndex: TABINDEX_VACIMPFRM + 20,
				handler: function() {
					alert('parent_id='+this.formParams.parent_id);
					Ext.getCmp(this.formParams.parent_id).fireEvent('success', 'btnFormEditParam', { type: '171' });
					form.hide();
//          var vacRefuseForm = Ext.getCmp('vacRefuseEditForm');
//          if (!vacRefuseForm.form.isValid()) {
//            msgBoxNoValidForm();
//            return false;
//          }

//          Ext.Ajax.request({
//            url: '/?c=VaccineCtrl&m=savePrivivRefuse',
//            method: 'POST',
//            params: {
//                'person_id': Ext.getCmp('amm_vacGetComboVal').formParams.person_id,
//                'med_staff_refuse_id': vacRefuseForm.form.findField('MedStaffRefuse_id').getValue(),
//                'refuse_date': refuseDate,
//                'vaccine_type_id': vacRefuseForm.form.findField('VaccineType_id').getValue(),
//                'date_refuse_range': vacRefuseForm.form.findField('Date_RefuseRange').value,
//                'vac_refuse_cause': vacRefuseForm.form.findField('vacRefuseCause').getValue(),
//                'refusal_type_id': vacRefuseForm.form.findField('RefusalType_id').getValue(),
//                'user_id': getGlobalOptions().pmuser_id
//            },
//            success: function(response, opts) {
//              //debugger;
//              //log('response='+response.responseText);
//              consoleLog(response);
//              
//              if ( response.responseText.length > 0 ) {
//                var result = Ext.util.JSON.decode(response.responseText);
//                consoleLog('-------result start-------');
//                consoleLog(result);
//                consoleLog('-------result end-------');
//                if (!result.success) {
//                  return false;
//                }
//              }
//              
//              Ext.getCmp(this.formParams.parent_id).fireEvent('success', 'amm_vacGetComboVal', {keys: [Ext.getCmp('amm_vacGetComboVal').formParams.person_id]});
//              form.hide();
////              Ext.getCmp('journalsGridRight').fireEvent('successPurpose', Ext.getCmp('gridSimilarRecords').store.keyList);
//            }.createDelegate(this),
//            failure: function(response, opts) {
//              consoleLog('server-side failure with status code: ' + response.status);
//            }
//          });
				}.createDelegate(this)
			}, {
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				tabIndex: TABINDEX_VACIMPFRM + 21,
				text: '<u>З</u>акрыть'
			}],     
			items: [
				this.vacEditForm = new Ext.form.FormPanel({
					autoScroll: true,
					region: 'center',
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
//          id: 'vacRefuseEditForm',
					name: 'vacEditForm',
					labelAlign: 'right',
					//autohight: true,
					//height: 300,
					labelWidth: 100,
					layout: 'form',
					items: [
						{
							height:5,
							border: false
						},
						{
							border: false,
							layout: 'form',
							items: [{

									allowBlank: false,
									fieldLabel: 'парам',
									autoLoad: true,
									hiddenName: 'param_value',
									tabIndex: TABINDEX_VACPRPFRM + 13,
									width: 260,
									xtype: 'amm_SprInoculationCombo'

							}]

						}]
				})
			]
			
		});
		sw.Promed.amm_vacGetComboVal.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(record) {
		sw.Promed.vac.utils.consoleLog(record);
		sw.Promed.amm_vacGetComboVal.superclass.show.apply(this, arguments);
		this.formParams = record;

		this.vacEditForm.form.findField('param_value').getStore().load({
			callback: function() {
				this.vacEditForm.form.findField('param_value').setValue(1);
			}.createDelegate(this)
		});
				
	}
});
