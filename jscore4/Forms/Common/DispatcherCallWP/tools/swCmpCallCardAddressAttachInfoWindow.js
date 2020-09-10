/* 
	Справочная информация по адресу вызова
*/


Ext.define('common.DispatcherCallWP.tools.swCmpCallCardAddressAttachInfoWindow', {
	alias: 'widget.swCmpCallCardAddressAttachInfoWindow',
	extend: 'sw.standartToolsWindow',
	title: 'Справочная информация по адресу вызова',
	width: getRegionNick()=='ufa'?830:500,
	height: 300,
	autoShow: true,
	initComponent: function() {
		var win = this,
			conf = win.initialConfig;
			
		var centerComponents = {
			xtype: 'form',
			layout: 'auto',	
			padding: '5',
			border: false,
			items: [
				{
					xtype: 'fieldset',
					title: 'МО, обслуживающая взрослое население',
					layout: 'form',
					items: [
						{
							xtype: 'hidden',
							name: 'adultLpuBuilding_id',
						},
						{
							xtype: 'textfield',
							name: 'adultLpuBuilding_Name',
							flex: 1,
							labelWidth: 120,
							padding: '0 0 5 0',
							fieldLabel: 'Наименование',
						},{
							xtype: 'textfield',
							name: 'adultLpuBuildingPhone',
							flex: 1,
							labelWidth: 120,
							padding: '0 0 5 0',
							fieldLabel: 'Телефон',
						},{
							xtype: 'textfield',
							name: 'adultLpuBuilding_Address',
							flex: 1,
							labelWidth: 120,
							
							fieldLabel: 'Адрес',
						}
					]
				},
				{
					xtype: 'fieldset',
					title: 'МО, обслуживающая детское население',
					layout: 'vbox',
					layout: 'form',
					items: [
						{
							xtype: 'hidden',
							name: 'childLpuBuilding_id',
						},
						{
							xtype: 'textfield',
							name: 'childLpuBuilding_Name',
							flex: 1,
							labelWidth: 120,
							padding: '0 0 5 0',
							fieldLabel: 'Наименование',
						},{
							xtype: 'textfield',
							name: 'childLpuBuildingPhone',
							flex: 1,
							labelWidth: 120,
							padding: '0 0 5 0',
							fieldLabel: 'Телефон',
						},{
							xtype: 'textfield',
							name: 'childLpuBuilding_Address',
							flex: 1,
							labelWidth: 120,
							padding: '0 0 5 0',
							fieldLabel: 'Адрес',
						}
					]
				}				
			]
		};
		
		var rightButtons = {
			xtype: 'button',
			text: 'Закрыть',
			iconCls: 'cancel16',
			handler: function(){}
		};
									

		//отправляем сборку
		win.configComponents = {			
			center: centerComponents,
			//leftButtons: leftButtons,
			rightButtons: rightButtons
		}
		
		win.callParent(arguments);
	},
	listeners: {
		show: function(){
			var win = this,
				conf = win.initialConfig,
				bForm = win.down('BaseForm').getForm();
				
			bForm.reset();
			
			//взрослые
			win.getLpuByAddress(conf,'1,4', function(resp){
				if(resp){
					for(var item in resp){
						bForm.findField('adult'+item).setValue(resp[item]);
					}
				}
			});
			//дети
			win.getLpuByAddress(conf,'2,4', function(resp){
				if(resp){
					for(var item in resp){
						bForm.findField('child'+item).setValue(resp[item]);
					}
				}
			});
		}
	},
	getLpuByAddress: function(conf, lpuRegionType_Codes, clback){
		Ext.Ajax.request({
			url: '/?c=LpuRegion&m=getLpuRegionsByAddress',
			params: {
				KLCity_id: conf.KLCity_id,
				KLStreet_id: conf.KLStreet_id,
				domNum: conf.domNum,
				LpuRegionType_Codes: lpuRegionType_Codes
			},
			callback: function(opt, success, response) {
				if (success){
					var response_obj = Ext.JSON.decode(response.responseText);
					console.log(response_obj[1]);
					clback(response_obj[1]?response_obj[1]:false);
				}
				else clback(false);
			}
		})
	}
})

