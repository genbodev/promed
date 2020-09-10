/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2018 gilmiyarov
* @version      09.2018
*/

/**
 * swSelectEco - окно для взаимосвязи регистра эко и регистра беременных
 *
 */
sw.Promed.swSelectEco = Ext.extend(sw.Promed.BaseForm, {
	closable: true,
	width : 700,
	height : 340,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	id: 'swSelectEco',
	border : false,
	plain : false,
	title: langs('Выбор случая ЭКО'),
    mode: 'all',
    onSelect: Ext.emptyFn,
    onHide: Ext.emptyFn,
    listeners: {
        hide: function(win) {
            win.onHide();
        }
    },
	show: function() {
		this.loadSluchList();
		sw.Promed.swSelectEco.superclass.show.apply(this, arguments);
    }, 
    linkEco: function(isLink) {		
		
		if (Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[0].PersonPregnancy_id == null){
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						msg: 'Сохраните анкету до установления взаимосвязи регистров.',
						title: 'Ошибка'
					});				
					return false;
		}
		
		var selectrec = Ext.getCmp('sluchECOGrid').getGrid().getSelectionModel().selections.keys[0];		
		console.log(selectrec);
        if (typeof selectrec == 'undefined' || Ext.getCmp('sluchECOGrid').getGrid().getSelectionModel().selections.items[0].data['lpu_id'] == null) {
            Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись.'));
            return false;
        }		
		
		Ext.getCmp('swPersonPregnancyEditWindow').formPanels[0].find("hiddenName","QuestionType_228")[0].setValue(Ext.getCmp('sluchECOGrid').getGrid().getSelectionModel().selections.items[0].data['lpu_id']);
		
		
		
		Ext.Ajax.request({ 
			url: '/?c=PersonPregnancy&m=savelinkEco', 
			params: { 
				PersonPregnancy_id: Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.categories.items[0].PersonPregnancy_id,
				PersonRegisterEco_id: selectrec,
				IsLink: isLink
			},
			success: function(result){				
				if (result.statusText == 'OK'){																				
					sw.swMsg.show(
					{						
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						msg: 'Взаимосвязь со случаем из регистра ЭКО '+(isLink ? 'установлена.' : 'удалена.'),
						title: 'Подтверждение',
						fn: function()
						{
							Ext.getCmp('swSelectEco').hide()
						}					
					});									
				}else{
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						msg: 'При сохранении возникла ошибка.',
						title: 'Ошибка'
					});
				}
			}.createDelegate(this)
		});
			
        return true;
	}, 
	loadSluchList: function() {
		var form = this;
        var parametrs = {}; 
		parametrs.PersID = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.inputData.defaults.Person_id;
		form.sluchECOGrid.loadData({globalFilters: parametrs});				
	},
	initComponent: function() {
		var form = this;		
		
		this.sluchECOGrid = new sw.Promed.ViewFrame({
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 110,
            autoLoadData: false,
            dataUrl: '/?c=Eco&m=loadEcoSluch',
            id: 'sluchECOGrid',
            height: 222,   
            region: 'center',
            contextmenu: false,
            paging: false,
            toolbar: false,
            border: false, 
            stringfields: [
                {name: 'Eco_id', header: 'ИД', width: 100},
                {name: 'DateAdd', header: 'Дата включения', width: 95, type:'date'},
				{name: 'lpu_nick', header: 'МО', width: 250, type:'string'},
				{name: 'PersonRegisterEco_ResultDate', header: 'Дата результата', width: 95, type:'date', hidden:false},
				{name: 'EcoResultType_Name', header: 'Результат ЭКО', width: 190, type:'string'},
				{name: 'lpu_id', header: 'Идентификатор МО', width: 190, type:'string', hidden:true},
				{name: 'PersonPregnancy_id', header: 'Идентификатор регистра беременных', width: 190, type:'string', hidden:true},
            ]
        });       
				
		Ext.getCmp('sluchECOGrid').getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {			 
			 if (!Ext.isEmpty(row.get('PersonPregnancy_id'))) {			 
			  return ' x-grid-rowblue x-grid-rowbold';
			 }
			 return '';
			}
		});		
		
		
    	Ext.apply(this, {
			items : [new Ext.form.FormPanel({
				id : 'SelectUslugaComplexForm',
				height : 300,
				layout : 'form',
				border : false,
				frame : true,
				labelWidth : 120,
				items : [{
					xtype: 'fieldset',
					style : 'padding: 10px;',
					autoHeight: true,
					items : [this.sluchECOGrid]
			    }]
			})],
			buttons : [
				{
					text : langs('Связать'),
					iconCls : 'ok16',
					handler : function() {
						form.linkEco(true);
					}
				},
				{
					text : langs('Удалить связь'),
					iconCls : 'ok16',
					handler : function() {
						form.linkEco(false);
					}
				},
				{
					text: '-'
				},				
				{
					text : langs('Отмена'),
					iconCls : 'ok16',
					handler : function() {
						this.hide();
					}.createDelegate(this)
				},				
			],
			buttonAlign : "right"
		});
		sw.Promed.swSelectEco.superclass.initComponent.apply(this, arguments);
	} 
});