/**
 * swBSKPreviewWindow - окно предосмотра предметов наблюдения по пациенту в регистре БСК
 * 
 */

sw.Promed.swBSKPreviewWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'swBSKPreviewWindow',
    MorbusType_id: false,
    modal: true,
    title: 'Окно предосмотра предметов наблюдения',
    height: 'auto',
    width: 'auto',	
	closable      : false,	
    closeAction: 'hide',
	buttonAlign: 'right',
    bodyStyle: 'padding:0px;border:0px;',
	lastSelectedMorbusType_id: false,
    initComponent: function () {
		this.BSKPreview = new sw.Promed.ViewFrame(
			{
				object: 'BSKPreview',
				dataUrl: '/?c=ufa_BSK_Register_User&m=getInfoForPacientOnBSKRegistry',
				toolbar: false,
				id:'BSKPreview',
				autoLoadData: false,
				contextmenu: false,
				width: '410',				
				stringfields:
				[
					{name: 'MorbusType_id', type: 'int', hidden: true},
					{name: 'MorbusType_Name',  type: 'string', header: 'Наименование', id:'autoexpand'},
					{name: 'BSKRegistry_setDate', header: 'Дата', 
						renderer: function(v) {
								return (v == null ? '-' : v);
						}
					},
					{name: 'BSKRegistry_riskGroup', header: 'Показатель', 
						renderer: function(v) {
								return (v == null ? '-' : v);
						}
					}					
				],
				focusOnFirstLoad: false
			});
		
        Ext.apply(this,
                {
                    items: [this.BSKPreview
                    ], buttons:
                            [
                                {
                                    text: 'Закрыть',
									id: 'close',
                                    handler: function () {
										Ext.getCmp('swBSKPreviewWindow').refresh();
                                    }
                                }
                            ]
                }
        );
        sw.Promed.swBSKPreviewWindow.superclass.initComponent.apply(this, arguments);
  },
  refresh : function(){
		sw.codeInfo.lastObjectName = this.objectName;
		sw.codeInfo.lastObjectClass = this.objectClass;
		if (sw.Promed.Actions.loadLastObjectCode)
		{
			sw.Promed.Actions.loadLastObjectCode.setHidden(false);
			sw.Promed.Actions.loadLastObjectCode.setText('Обновить '+this.objectName+' ...');
		}
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.close();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];

  } ,     
  show: function (params) {	
		
		this.BSKPreview.getGrid().getStore().load({
			params: {
				Person_id : params.Person_id
			}
		});
	    sw.Promed.swBSKPreviewWindow.superclass.show.apply(this, arguments);
  },
  listeners : {
		'hide': function() {
			if (this.refresh)
				this.onHide();
		},
		'close': function() {
			if (this.refresh)
				this.onHide();
		} 		
  }
});