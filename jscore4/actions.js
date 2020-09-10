//Ext.require([
//    'Ext.window.MessageBox',
//    'Ext.tip.*'
//]);

	
	// Конфиги акшенов
	sw.Promed.Actions = {
		
		PromedExit: {
			text:'Выход',
			iconCls: 'exit16',
			refId: 'globalExit',
			xtype: 'button',
			listeners: {
				click: function() {
					Ext.MessageBox.show({
						refId: 'globalExitMsg',
						title: 'Подтвердите выход',
						msg: 'Вы действительно хотите выйти?',
						buttons: Ext.MessageBox.YESNO,
						buttonText:{ 
							yes: "Да", 
							no: "Нет" 
						},
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {

								Ext.Ajax.request({
									url: '/?c=CmpCallCard4E&m=updateSmpUnitHistoryData',
									params: {
										'closeAll': true
									},
									callback: function(){
										window.onbeforeunload = null;
										window.location=C_LOGOUT;
									}
								})

							}
						}
					});
				}
			}
		}
		
		
    }
	
