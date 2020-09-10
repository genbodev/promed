/**
* swBskRegistryWindow - окно регистра по БСК
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @package      MorbusBsk
* @author       Пермяков Александр
* @version      06.2013
* @comment      Префикс для id компонентов ORW (BskRegistryWindow)
*
*/
sw.Promed.swBskRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Регистр болезней системы кровообращения'),
	width: 800,    
    codeRefresh: true,
	objectName: 'swBskRegistryWindow',
	id: 'swBskRegistryWindow',	
    buttonAlign: 'left',
	closable: true,

	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('ORW_SearchButton');
	},
    inArray: function(needle, array){
        for(var k in array){
            if(array[k] == needle)
                return true;
        }
        
        return false;
    },
	doReset: function() {
		
		var base_form = this.findById('BskRegistryFilterForm').getForm();
		base_form.reset();
		this.BskRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.BskRegistrySearchFrame.ViewActions.person_register_out.setDisabled(false);
		this.BskRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.BskRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.BskRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.BskRegistrySearchFrame.getGrid().getStore().removeAll();
				
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('BskRegistryFilterForm').getForm();
		
		if ( !params.firstLoad && this.findById('BskRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено ни одно поле'), function() {
			});
			return false;
		}
		
		var grid = this.BskRegistrySearchFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (!params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Выбран тип поиска человека ') + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? langs('по состоянию на момент случая') : langs('По всем периодикам')) + langs('.<br />При выбранном варианте поиск работает <b>значительно</b> медленнее.<br />Хотите продолжить поиск?'),
				title: langs('Предупреждение')
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('BskRegistryFilterForm'));

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.BskRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	getRecordsCount: function() {
		var base_form = this.getFilterForm().getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(langs('Поиск'), langs('Проверьте правильность заполнения полей на форме поиска'));
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());

		if ( post.PersonPeriodicType_id == null ) {
			post.PersonPeriodicType_id = 1;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(langs('Подсчет записей'), langs('Найдено записей: ') + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(langs('Подсчет записей'), response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При подсчете количества записей произошли ошибки'));
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	height: 550,
	openViewWindow: function(action) {
		if (getWnd('swMorbusOnkoWindow').isVisible()) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно просмотра уже открыто'));
			return false;
		}
		
		var grid = this.BskRegistrySearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected()) {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();
			
		if ( Ext.isEmpty(selected_record.get('MorbusOnko_id')) ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Заболевание на человека не заведено'));
			return false;
		}
		
		var params = new Object();
		params.onHide = function(isChange) {
			if(isChange) {
				grid.getStore().reload();
			} else {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			}
		};
		params.allowSpecificEdit = ('edit' == action);
		params.Person_id = selected_record.data.Person_id;
		params.PersonEvn_id = selected_record.data.PersonEvn_id;
		params.Server_id = selected_record.data.Server_id;
		params.PersonRegister_id = selected_record.data.PersonRegister_id;
		params.userMedStaffFact = this.userMedStaffFact;
		getWnd('swMorbusOnkoWindow').show(params);
	},
	openWindow: function(action) {
			
		var form = this.findById('BskRegistryFilterForm').getForm();
		var grid = this.BskRegistrySearchFrame.getGrid();
		
		var cur_win = this;
        
   
        
		if (action == 'include') {
            //Ext.getCmp('BSKObjectCombo').setValue('');
            var params = {};
            getWnd('swBSKSelectWindow').show(params);

		} else if (action == 'out') {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
			{
				Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
				return false;
			}
			var record = grid.getSelectionModel().getSelected();
			sw.Promed.personRegister.out({
				PersonRegister_id: record.get('PersonRegister_id')
				,Person_id: record.get('Person_id')
				,Diag_Name: record.get('Diag_Name')
				,PersonRegister_setDate: record.get('PersonRegister_setDate')
				,callback: function(data) {
					grid.getStore().reload();
				}
			});
		}
		
	},
	initComponent: function() {
		var win = this;
        
		this.BskRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { this.openWindow('include'); }.createDelegate(this)},
                {name: 'action_edit', handler: function() { this.openViewWindow('edit'); }.createDelegate(this)},
                {name: 'action_view',  handler: function() { this.openViewWindow('view'); }.createDelegate(this)},
				{name: 'action_delete',  hidden: true, handler: this.deletePersonRegister.createDelegate(this)  },
				{name: 'action_refresh'},
				{name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH, /* /?c=Search&m=searchData */
			id: 'BskRegistry',
			object: 'BskRegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},	
				{name: 'Server_id', type: 'int', hidden: true},			
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Lpu_iid', type: 'int', hidden: true},
				{name: 'MedPersonal_iid', type: 'int', hidden: true},
				{name: 'MorbusType_id', type: 'int', hidden: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_Name', type: 'string', hidden: true, header: langs('Причина исключения из регистра'), width: 190},
                {name: 'BSKRegistry_setDateNext', type: 'string', header: langs('!'), width: 25, renderer: function(v, p, record) {
					var currentdate = getValidDT(getGlobalOptions().date, '');
					var date = new Date(v);
					if (currentdate > date) {
						return '<span style="color: red; font-weight: 900; font-size: 14px;">!</span>';
					}
				}},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 250},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 250},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 250},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 90},
                                {name: 'Person_deadDT', type: 'date', format: 'd.m.Y', header: langs('Дата смерти'), width: 90},
				{name: 'Person_Age', type: 'int', header: langs('Возраст'), width: 90},
				{name: 'Diag_Name', type: 'string', format: 'string', header: langs('Диагноз'), width: 250},
				{name: 'Lpu_Nick', type: 'string', header: langs('МО прикр.'), width: 150},
				{name: 'Lpu_Gospital', type: 'string', header: langs('МО госпитализации'), width: 150},
			
            	{name: 'Diag_id', type: 'int', hidden: true},
                //{name: 'Diag_Name', type: 'string', header: langs('Диагноз МКБ-10'), width: 150, hidden: true},
				{name: 'OnkoDiag_Name', type: 'string', header: langs('Гистология опухоли'), width: 250, hidden: true},
				{name: 'MorbusOnko_IsMainTumor', type: 'string', header: langs('Признак основной опухоли'), width: 150, hidden: true},
				{name: 'TumorStage_Name', type: 'string', header: langs('Стадия'), width: 60,hidden: true},
                {name: 'MorbusOnko_setDiagDT', type: 'date', format: 'd.m.Y', header: langs('Дата установления диагноза'), width: 150, hidden: true},
            
                {name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y',  header: langs('Дата включения в регистр'), width: 150},
                {name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', hidden: true, header: langs('Дата исключения из регистра'), width: 150},

				{name: 'isTLT', type: 'checkbox', value: true, header: langs('ТЛТ'), width: 40},
				{name: 'TimeBeforeTlt', type: 'string', header: 'Оставшееся<br>время<br>для ТЛТ', width: 90},
				{name: 'isCKV', type: 'string', header: langs('ЧКВ'), width: 100},
				{name: 'CKVduringHour', type: 'string', header: langs('ЧКВ в течение<br>60 минут'), width: 110, renderer: function (v, p, r) {	
					if(r.get('CKVduringHour') == 'true') {
						return '<span style="color:#f00" align="center"><img src="/img/icons/tick16.png" width="12" height="12"/></span>';
					} else if(r.get('CKVduringHour') == 'false'){
						return '<span style="color: red; font-weight: 900; font-size: 14px;">!</span>';
					} else {
						return r.get('CKVduringHour');
					}
				}},
				{name: 'isKAG', type: 'string', header: langs('КАГ'), width: 100},
				{name: 'KAGduringHour', type: 'string', header: langs('КАГ в течение<br>60 минут'), width: 110, renderer: function (v, p, r) {	
					if(r.get('KAGduringHour') == 'true') {
						return '<span style="color:#f00" align="center"><img src="/img/icons/tick16.png" width="12" height="12"/></span>';
					} else if(r.get('KAGduringHour') == 'false'){
						return '<span style="color: red; font-weight: 900; font-size: 14px;">!</span>';
					} else {
						return r.get('KAGduringHour');
					}
				}},
				{name: 'prosmotr', header: 'Операции, случаи лечения<br>в анамнезе, ДУ', width: 150, renderer: function (value, cellEl, rec) {
					return "<a href='#' onClick='getWnd(\"swBSKOKSWindow\").show({ Person_id: " + rec.get('Person_id') + " });'>" + "Просмотреть" + "</a>";
				}},
                {name: 'PMUser_Name', type: 'string', header: 'Кем создана анкета', hidden: true},
				{name: 'Diag', type: 'int', id: 'autoexpand', header: ''},
				{name: 'BSKRegistry_isBrowsed', type: 'int', hidden: true }
			],
			focusOnFirstLoad: false,
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelect: function(sm,index,record) {
				//console.log('sm',sm.getSelected(),'index',index,'record',record);
				this.getAction('open_emk').setDisabled( false );
				this.getAction('BSKObjectButton').setDisabled( record.get('PersonRegister_id') == null );
				//this.getAction('person_register_out').setDisabled( Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
                this.getAction('action_delete').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
                //this.getAction('action_edit').setDisabled( Ext.isEmpty(record.get('MorbusOnko_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false );
                //this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('MorbusOnko_id')) );
			},
			onDblClick: function(x,c,v) {
			 
                

			}           
		});

		this.BskRegistrySearchFrame.ViewGridPanel.view = new Ext.grid.GridView(
			{
				getRowClass : function (row, index)
				{
					var cls = '';
					if(row.get('MorbusType_id') == 19 && row.get('BSKRegistry_isBrowsed')  == 1 )
						cls = "x-grid-rowbackred x-grid-rowred " ;
					//if(row.get('MorbusType_id') == 19 && row.get('BSKRegistry_isBrowsed') == 2)
						//cls = "x-grid-row ";
					if (cls.length == 0)
						cls = 'x-grid-panel';
					return cls;
				}
			});
	

        var BskStore = this.BskRegistrySearchFrame.getGrid().on(
           'rowdblclick',
           function(){
                 var rec = this.getSelectionModel().getSelected();
                 //console.log('REC', rec);
                 
                 var params = {
                    Person_id : rec.data.Person_id
                 }
                 rec.set('BSKRegistry_isBrowsed',2);
                 getWnd('personBskRegistryDataWindow').show(params); 
                
                 
           }
        );

        var BskStore = this.BskRegistrySearchFrame.getGrid().getStore().on(
            'load',
            function (){

                
                var recs = this.data.items;
                
                //console.log('RECS', recs);
               
				var currentdate = new Date.now(); 

                for(var k in recs){
                    if(typeof recs[k] == 'object'){
						// Формируем данные о возрасте
						if (recs[k].data.Person_Birthday) {
							recs[k].data.Person_Age = Math.floor(((recs[k].data.Person_deadDT? recs[k].data.Person_deadDT : currentdate) - recs[k].data.Person_Birthday) / (1000 * 60 * 60 * 24 * 365)); // Если есть дата сметри, то указываем возраст на момент смерти
							if (recs[k].data.Person_Age % 10 === 1)
								recs[k].data.Person_Age += ' год';
							else if (recs[k].data.Person_Age % 10 >= 2 && recs[k].data.Person_Age % 10 <= 4 )                                
								recs[k].data.Person_Age += ' года';
							else 
								recs[k].data.Person_Age += ' лет';
						}

						if (recs[k].data.isTLT)
							recs[k].data.isTLT = true;
						else 
							recs[k].data.isTLT = false;
						
					}
				}

                                
                
                
                
                 //Ext.getCmp('BskRegistry').getGrid().bbar.dom.innerText = '';

            }   
        );   

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_ORW + 120,
				id: 'ORW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ORW + 121,
				text: BTN_FRMRESET
			},{
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ORW + 123,
				text: BTN_FRMCOUNT
			}, 
                        {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('ORW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ORW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_ORW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('BskRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
                isDisplayPersonRegisterRecordTypeField: false,
				allowPersonPeriodicSelect: true,
				id: 'BskRegistryFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'BskRegistry',
				tabIndexBase: TABINDEX_ORW,
				tabPanelHeight: 225,
				tabPanelId: 'ORW_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 220,
					layout: 'form',
					listeners: {
						'activate': function() {
							this.getFilterForm().getForm().findField('PersonRegisterType_id').focus(250, true);
						}.createDelegate(this)
					},
					title: langs('<u>6</u>. Регистр'),
					items: [{
						xtype: 'swpersonregistertypecombo',
						hiddenName: 'PersonRegisterType_id',
						width: 200,
						store: new Ext.data.SimpleStore({
							data: [
								['1',langs('Все')],
								['2',langs('Исключенные из регистра')],
								['3',langs('Умершие')]
							],
							editable: false,
							key: 'PersonRegisterType_id',
							autoLoad: false,
							fields: [
								{name: 'PersonRegisterType_id', type:'int'},
								{name: 'PersonRegisterType_Name', type:'string'}
							]
						})
					}, {
						fieldLabel: langs('Дата включения в регистр'),
						name: 'PersonRegister_setDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}, {
						fieldLabel: langs('Дата исключения из регистра'),
						name: 'PersonRegister_disDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					},
					{
						layout: 'column',
						border: false,
						items:[
							{
								layout:'form',
								border: false,
								items:[
									{
										fieldLabel: 'Предмет наблюдения',
										mode: 'local',
										store: new Ext.data.JsonStore({
											url: '/?c=BSK_Register_User&m=getBSKObjects',
											autoLoad: true,
											fields: [
												{name: 'MorbusType_id', type: 'int'},
												{name: 'MorbusType_name', type: 'string'}
											],
											key: 'MorbusType_id',
										}),
										editable: false,
										triggerAction: 'all',
										hiddenName: 'MorbusType_id',
										displayField: 'MorbusType_name',
										valueField: 'MorbusType_id',
										width: 200,
										xtype: 'combo',
										tpl: '<tpl for="."><div class="x-combo-list-item">'+
														   '{MorbusType_name} '+ '&nbsp;' +
														   '</div></tpl>',
										listeners: {
											'change': function(cb,newValue, oldValue ) {
												if (newValue == '') {
													Ext.getCmp('quest_yn').setValue('');
													Ext.getCmp('quest_yn').setDisabled(true);
													Ext.getCmp('pmUser_docupd').setValue('');
													Ext.getCmp('pmUser_docupd').setDisabled(true);
													Ext.getCmp('BskRegistry').getGrid().getColumnModel().setHidden(23,true)// Предоставление доступа к полю "Кем создана анкета"
												} else {
													Ext.getCmp('quest_yn').setDisabled(false);
													Ext.getCmp('pmUser_docupd').setDisabled(false);
													Ext.getCmp('BskRegistry').getGrid().getColumnModel().setHidden(23,false)
												}
											} 
										}
									}	
								]
							},
							{
								layout:'form',
								border: false,
								items:[
									{
										xtype: 'combo',
										fieldLabel: 'Есть заполненные анкеты',
										hiddenName: 'quest_id',
										labelAlign: 'left',
										editable: false,
										disabled: true,
										id: 'quest_yn',
										mode:'local',
										width: 50,
										triggerAction : 'all',
										store:new Ext.data.SimpleStore(  {           
												  fields: [{name:'quest', type:'string'},{ name:'quest_id',type:'int'}],
												  data: [
														  ['Да', 1],
														  ['Нет', 2],
														  ]
										}),
										displayField:'quest',
										valueField:'quest_id',
										tpl: '<tpl for="."><div class="x-combo-list-item">'+
														   '{quest} '+ '&nbsp;' +
														   '</div></tpl>'
									}
								]
							},
							{
								layout:'form',
								border: false,
								items:[
                                                                    {
                                                                        xtype: 'combo',
                                                                        displayField: 'pmUser_FioL',
                                                                        editable: true,
                                                                        enableKeyEvents: true,
                                                                        fieldLabel: 'Пользователь',
                                                                        hiddenName: 'pmUser_docupdID',
                                                                        id: 'pmUser_docupd',
                                                                        disabled: true,
                                                                        minChars: 1,
                                                                        width: 300,
                                                                        name : "pmUser_docupdID",
                                                                        minLength: 1,
                                                                        mode: 'local',
                                                                        resizable: true,
                                                                        selectOnFocus: true,
                                                                        store: new Ext.data.Store({
                                                                                autoLoad: false,
                                                                                reader: new Ext.data.JsonReader({
                                                                                        id: 'pmUser_id'
                                                                                }, [
                                                                                        {name: 'pmUser_id', mapping: 'pmUser_id'},
                                                                                        {name: 'pmUser_FioL', mapping: 'pmUser_FioL'},
                                                                                        {name: 'pmUser_Fio', mapping: 'pmUser_Fio'},
                                                                                        {name: 'pmUser_Login', mapping: 'pmUser_Login'},
                                                                                        {name: 'MedPersonal_TabCode', mapping: 'MedPersonal_TabCode'}
                                                                                ]),
                                                                                sortInfo: {
                                                                                        direction: 'ASC',
                                                                                        field: 'pmUser_Fio'
                                                                                },
                                                                                url: '/?c=BSK_Register_User&m=getCurrentOrgUsersList'
                                                                        }),
                                                                        triggerAction: 'all',
                                                                        /*tpl: new Ext.XTemplate(
                                                                                '<tpl for="."><div class="x-combo-list-item">',
                                                                                '<div><b>{[values.pmUser_Fio ? values.pmUser_Fio : "&nbsp;"]}</b> {[values.pmUser_Login ? "(" + values.pmUser_Login + ")" : "&nbsp;"]}</div>',
                                                                                '</div></tpl>'
                                                                        ),*/
                                                                        valueField: 'pmUser_id',  
                                                                        listeners: {
                                                                            change: function() {
                                                                                
                                                                            },
                                                                            keydown: function(inp, e) {
                                                                                if ( e.getKey() == e.END ) {
                                                                                        this.inKeyMode = true;
                                                                                        this.select(this.getStore().getCount() - 1);
                                                                                }

                                                                                if ( e.getKey() == e.HOME ) {
                                                                                        this.inKeyMode = true;
                                                                                        this.select(0);
                                                                                }

                                                                                if ( e.getKey() == e.PAGE_UP ) {
                                                                                        this.inKeyMode = true;
                                                                                        var ct = this.getStore().getCount();

                                                                                        if ( ct > 0 ) {
                                                                                                if ( this.selectedIndex == -1 ) {
                                                                                                        this.select(0);
                                                                                                }
                                                                                                else if ( this.selectedIndex != 0 ) {
                                                                                                        if ( this.selectedIndex - 10 >= 0 )
                                                                                                                this.select(this.selectedIndex - 10);
                                                                                                        else
                                                                                                                this.select(0);
                                                                                                }
                                                                                        }
                                                                                }

                                                                                if ( e.getKey() == e.PAGE_DOWN ) {
                                                                                        if ( !this.isExpanded() ) {
                                                                                                this.onTriggerClick();
                                                                                        }
                                                                                        else {
                                                                                                this.inKeyMode = true;
                                                                                                var ct = this.getStore().getCount();

                                                                                                if ( ct > 0 ) {
                                                                                                        if ( this.selectedIndex == -1 ) {
                                                                                                                this.select(0);
                                                                                                        }
                                                                                                        else if ( this.selectedIndex != ct - 1 ) {
                                                                                                                if ( this.selectedIndex + 10 < ct - 1 )
                                                                                                                        this.select(this.selectedIndex + 10);
                                                                                                                else
                                                                                                                        this.select(ct - 1);
                                                                                                        }
                                                                                                }
                                                                                        }
                                                                                }

                                                                                if ( e.altKey || e.ctrlKey || e.shiftKey )
                                                                                        return true;

                                                                                if ( e.getKey() == e.DELETE||e.getKey() == e.BACKSPACE) {
                                                                                        inp.setValue('');
                                                                                        inp.setRawValue("");
                                                                                        inp.selectIndex = -1;
                                                                                        if ( inp.onClearValue ) {
                                                                                                this.onClearValue();
                                                                                        }
                                                                                        e.stopEvent();
                                                                                        return true;
                                                                                }                                                                                
                                                                            },
                                                                            beforequery: function(q) {
                                                                                if ( q.combo.getStore().getCount() == 0 ) {
                                                                                        q.combo.getStore().removeAll();
                                                                                        q.combo.getStore().load();
                                                                                }                                                                                
                                                                            }
                                                                        }
                                                                    }
								]								

							}								
							
						]
					}
					]
				}
 
                ]
			}),
			this.BskRegistrySearchFrame]
		});
		
		sw.Promed.swBskRegistryWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
	   /*
		'beforeShow': function(win) {
			if (String(getGlobalOptions().groups).indexOf('BskRegistry', 0) < 0 && getGlobalOptions().CurMedServiceType_SysNick != 'minzdravdlo')
			{
				sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «'+ win.title +'»');
				return false;
			}
		},
        */
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('BskRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('BskRegistryFilterForm').doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('ORW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('BskRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swBskRegistryWindow.superclass.show.apply(this, arguments);

		this.BskRegistrySearchFrame.addActions({
			name:'person_register_out', 
			text:langs('Исключить из регистра'), 
			tooltip: langs('Исключить из регистра'),
			iconCls: 'delete16',
			handler: function() {
				//this.openWindow('out');
                /*
    			sw.swMsg.show({
    				buttons: Ext.Msg.YESNO,
    				fn: function(buttonId, text, obj) {
    					if ( buttonId == 'yes' ) {
                            Ext.getCmp('swBskRegistryWindow').deletePersonRegister();
    					}
    				}.createDelegate(this),
    				icon: Ext.MessageBox.QUESTION,
    				msg: langs('Вы дейсвтительно хотите исключить пациента из регистра?'),
    				title: langs('Предупреждение')
    			});
    			return false;                
                */
                //Ext.getCmp('swBskRegistryWindow').deletePersonRegister();
				Ext.Msg.alert('Исключение из регистра', 'Исключение из регистра временно отключено.');
			}.createDelegate(this)
		});
		
		this.BskRegistrySearchFrame.addActions({
			name:'open_emk', 
			text:langs('Открыть ЭМК'), 
			tooltip: langs('Открыть электронную медицинскую карту пациента'),
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});
		
		this.BskRegistrySearchFrame.addActions({
			name:'BSKObjectButton', 
			text:'Предмет наблюдения', 
			disabled: true,
			tooltip: 'Предмет наблюдения',
			iconCls: 'address-book16',
			handler: function() {
				var sm = this.BskRegistrySearchFrame.getGrid().getSelectionModel().getSelected();	
				if (typeof sm != 'undefined') {
					var Person_id = sm.get('Person_id');
				}
				else {
					Ext.Msg.alert('Ошибка ввода','Для просмотра предметов наблюдения выделите строку с пациентом и нажмите "Предмет наблюдения"');
					return false;
				}
				
				//console.log('Person_id',Person_id);
				getWnd('swBSKPreviewWindow').show({
						Person_id: Person_id
				});
			}.createDelegate(this)
		},4);
		
		var base_form = this.findById('BskRegistryFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		//this.findById('ORW_SearchFilterTabbar').setActiveTab(0);
		
		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		
		base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		//base_form.findField('DiagDiscrep_id').setValue('all'); // По умолчанию
		/*if ( String(getGlobalOptions().groups).indexOf('BskRegistry', 0) >= 0 ) {
			base_form.findField('AttachLpu_id').setDisabled(false);
		} else {
			base_form.findField('AttachLpu_id').setDisabled(true);
		}*/
		
		this.doLayout();
		
		base_form.findField('PersonRegisterType_id').setValue(1);

		if(typeof arguments[0].person != 'undefined') {
			getWnd('swBSKSelectWindow').show({person:arguments[0].person});
			//this.BskRegistrySearchFrame.getAction('action_add').execute();
		}
		//this.doSearch({firstLoad: true});                
	},
	emkOpen: function()
	{
		var grid = this.BskRegistrySearchFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	deletePersonRegister: function() {
		var grid = this.BskRegistrySearchFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		Ext.Msg.show({
			title: langs('Вопрос'),
			msg: langs('Удалить выбранную запись регистра?'),
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(langs('Удаление...')).show();
					Ext.Ajax.request({
						url: '/?c=PersonRegister&m=delete',
						params: {
							PersonRegister_id: record.get('PersonRegister_id')
						},
						callback: function(options, success, response) {
							this.getLoadMask().hide();
							if (success) {	
								var obj = Ext.util.JSON.decode(response.responseText);
								if( obj.success )
									grid.getStore().remove(record);
							} else {
								sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении записи регистра!'));
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	}
});