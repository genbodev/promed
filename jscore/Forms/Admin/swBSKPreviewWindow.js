/**
 * swBSKPreviewWindow - окно предосмотра предметов наблюдения по пациенту в регистре БСК
 * 
 */

sw.Promed.swBSKPreviewWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'swBSKPreviewWindow',
    MorbusType_id: false,
    modal: true,
    title: 'Окно предпросмотра предметов наблюдения',
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
				dataUrl: '/?c=BSK_Register_User&m=getInfoForPacientOnBSKRegistry',
				toolbar: false,
				id:'BSKPreview',
				autoLoadData: false,
				contextmenu: false,
				width: '800',				
				stringfields:
				[
					{name: 'MorbusType_id', type: 'int', hidden: true},
					{name: 'MorbusType_Name',  type: 'string', header: 'Наименование', width: 230},
					{name: 'BSKRegistry_setDate', header: 'Дата', width: 80,
						renderer: function(v) {
								return (v == null ? '-' : v);
						}
					},
					{name: 'BSKRegistry_setDateNext', type: 'date', dateFormat: 'd.m.Y', header: 'Дата следующего осмотра',  width: 150},
					{name: 'BSKRegistry_riskGroup', header: 'Фактор риска', width: 90,
						renderer: function(v) {
								return (v == null ? '-' : v);
						}
					},
					{name: 'PersonRegister_setDate', type: 'date', dateFormat: 'd.m.Y', header: 'Дата включения в регистр',  width: 120},
					{name: 'PersonRegister_disDate', type: 'date', dateFormat: 'd.m.Y', header: 'Дата исключения из регистра',  width: 120}
				],
				focusOnFirstLoad: false
			});

			this.BSKPreview.ViewGridPanel.view = new Ext.grid.GridView(
				{
					getRowClass: function (row, index)
					{
						var cls = '';
						var currentdate = getValidDT(getGlobalOptions().date, '');
						if(row.get('MorbusType_id').inlist(['84', '50', '89', '88'])) {
							var date = row.get('BSKRegistry_setDateNext');
							date = new Date(date);
							if (currentdate>date) {
								cls = "x-grid-rowbackpalepink";
							}
						}
						if(row.get('MorbusType_id').inlist(['19'])) {
							var date = row.get('PersonRegister_disDate');
							if (!!date && currentdate > date) {
								cls = "x-grid-rowgray";
							}
						}
						return cls;
					}
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