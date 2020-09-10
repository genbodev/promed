/*
 * 
 * swSelectDeleteEco - окно выбора случая ЭКО для удаления
 *
 * @access		public
 * @copyright	Copyright (c) 2019
 * @author		gilmiyarov
 * @version		04.2019
 * 
 */

sw.Promed.swSelectDeleteEco = Ext.extend(sw.Promed.BaseForm, {
    title: 'Случаи ВРТ',
    modal: true,
    closable: true,
    closeAction: 'hide',
    width: 270,
    height: 220,
	parametrs: '',
    listeners: {
        'hide': function() {
            //Ext.getCmp('select_mo_win').setVisible(false);
        }
    },
    show: function($data){
		
		this.callback = Ext.emptyFn;
			
        sw.Promed.swSelectDeleteEco.superclass.show.apply(this, arguments);
		var parametrs = {}; 
		parametrs.PersonRegister_id=$data.record.id;
		
		this.parametrs = parametrs;
		this.callback = $data.callback;		
        this.grid.loadData({globalFilters: parametrs});
    },
    initComponent: function()
    {
        var me = this;

        me.grid = new sw.Promed.ViewFrame({
            //selectionModel: 'multiselect',
            dataUrl: '/?c=Eco&m=getPersonRegisterEco',
            layout: 'fit',
            region: 'center',
            paging: false,
            root: '',
            totalProperty: 'totalCount',
            toolbar: false,
            singleSelect: false,
            autoLoadData: false,
            useEmptyRecord: false,
            noSelectFirstRowOnFocus: true,
            height: 150,
            stringfields: [                
                {name: 'Eco_id', type: 'int', header: 'ID', key: true, hidden: true},				
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: 'Дата', width: 100, align: 'center'}, 
				{name: 'opl_name', header: 'Вид ВРТ', hidden: false, sortable: true, width: 150, align: 'center' }
            ]
        });

        Ext.apply(me, {
            buttons: [
                {
                    text: 'Удалить случай',
                    iconCls: 'delete16',
                    handler: function()
                    {
                        me.doDelete();
                    }
                },
                {
                    text:'-'
                },
                //HelpButton(this, TABINDEX_AF + 10),
                {
                    handler: function() {
						me.callback();
                        me.hide();
                    },
                    iconCls: 'cancel16',
                    tabIndex: TABINDEX_AF + 11,
                    text: BTN_FRMCLOSE
                }
            ],
            items: [
                me.grid
            ]
        });

        sw.Promed.swSelectDeleteEco.superclass.initComponent.apply(this, arguments);
    },
    doDelete: function(){

      var me = this,
          collectChecked = [];

        me.grid.getMultiSelections().forEach(function (el){
            collectChecked.push(el.get('Eco_id'))
        });
		
        if (collectChecked.length == 0)
        {
            Ext.Msg.alert('Ошибка', 'Не выбрана запись!');
            return false;
        }		
		
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {

					Ext.Ajax.request({
						url: '/?c=Eco&m=Delete',
						params: {
							'Eco_id': collectChecked[0]
						},
						callback: function (opt, success, response) {
							me.grid.loadData({globalFilters: this.parametrs});
							//me.grid.getAction('action_refresh').execute();
							me.callback();
							
							if (success) {
								me.hide();
								//Ext.Msg.alert('Подтверждение', 'Случай удален!');
								if (success && response.responseText != '') {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if (response_obj.success){								
										sw.swMsg.show(
										{
											buttons: Ext.Msg.OK,
											icon: Ext.Msg.INFO,
											msg: 'Случай удален!',
											title: 'Подтверждение'
										});
									}else{
										sw.swMsg.show(
										{
											buttons: Ext.Msg.OK,
											icon: Ext.Msg.INFO,
											msg: 'Удаление завершилось с ошибкой!<br>Случай не удален!',
											title: 'Ошибка'
										});																	
									}									
								}else{
									sw.swMsg.show(
									{
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.INFO,
										msg: 'Случай удален!',
										title: 'Подтверждение'
									});									
								}								
							}														
						}
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
    }
});