/* 
	Прослушивание аудозаписи
*/

Ext.define('common.tools.swCmpCallRecordListenerWindow', {
	alias: 'widget.swCmpCallRecordListenerWindow',
	extend: 'sw.standartToolsWindow',
	title: 'Аудиозапись',
	width: 350,
	height: 150,
	onEsc: Ext.emptyFn,
	refId: 'swCmpCallRecordListenerWindow',
	initComponent: function() {
		var win = this,
			conf = win.initialConfig,
			globals = getGlobalOptions();
		
		win.mp3Player = Ext.create('Ext.container.Container', 
			{
				//flex: 1,
				padding: '10 5',
				layout: {
					type: 'form',
					align: 'center',
					pack: 'center'
				}
			});
			
		//отправляем сборку
		win.configComponents = {
			//top: win.topTbar,
			center: [win.mp3Player],
			//subBottomItems: [],
			//leftButtons: win.printButton
		}
		
		win.callParent(arguments);
	},
	
	listeners: {
		
		render: function(){
			var win = this,
				conf = win.initialConfig;
			
			win.getCallAudio(conf.record_id);
		}
		
	},
	
	getCallAudio: function(record_id){
		var win = this;
		
		Ext.Ajax.request({
			url: '/?c=CmpCallCard4E&m=getCallAudio',
			params: {							
				CmpCallRecord_id: record_id
			},
			callback: function(opt, success, response) {
				var response_obj = Ext.JSON.decode(response.responseText);
				
				if(response_obj.length && response_obj[0].CmpCallCard_Ngod && response_obj[0].CmpCallCard_prmDT){
					win.setTitle(response_obj[0].CmpCallCard_prmDT +' №' + response_obj[0].CmpCallCard_Numv + ' ' + response_obj[0].Person_FIO);
					win.setHeight(50 + (100 * response_obj.length))
				}
				else{
					win.setTitle('Аудиозапись');
				}
				
				for(var i in response_obj){

					var h = '<audio controls style="margin: 10px 0;"><source src="uploads/audioCalls/'+response_obj[i].CmpCallRecord_RecordPlace+'" type="audio/mpeg"></audio>',					
						t = Ext.create('Ext.form.FieldSet', {
						xtype: 'fieldset',						
						title: i > 0 ? 'Связанный вызов ' + response_obj[i].CmpCallCard_prmDT +' №' + response_obj[i].CmpCallCard_Numv + ' ' + response_obj[i].Person_FIO: false,
						html: h,
						renderTo: win.mp3Player.el
					});

					win.mp3Player.addChildEls(t);
				}

			}
		});
	}
})
