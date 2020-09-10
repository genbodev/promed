/**
* swPersonCardHistoryWindow - окно просмотра истории прикрепления.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Ivan Pshenitcyn aka IVP (ipshon@rambler.ru)
* @version      02.07.2009
*/

sw.Promed.swPersonCardHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	printAttachBlank: function(){
		var current_window = this;
		this.getMedicalInterventPrintParams({callback: function(mi_params){
			var params = new Object();
			var grid = Ext.getCmp('PCardHF_PersonCardHistoryGrid').getGrid();
			params.mi_params = mi_params;

			//var record = Ext.getCmp('PCVAW_PersonSearchGrid').getGrid().getSelectionModel().getSelected();
			params.Person_id = current_window.personId;

			var record = Ext.getCmp('PCardHF_PersonCardHistoryGrid').getGrid().getSelectionModel().getSelected();
			params.Server_id = record.get('Server_id');
			params.Lpu_id = record.get('Lpu_id');
			params.printAgreementOnly = 1;
			getWnd('swPersonCardPrintDialogWindow').show({params: params}); //https://redmine.swan.perm.ru/issues/56487
		}});
	},
	getMedicalInterventPrintParams: function(options) {
		var callback = Ext.emptyFn;
		if (options && options.callback) {
			callback = options.callback;
		}

		var record = Ext.getCmp('PCardHF_PersonCardHistoryGrid').getGrid().getSelectionModel().getSelected();

		var processParams = function(data) {
			var params = new Object();

			params.person_card_id = record.get('PersonCard_id');
			params.med_personal_id = (getGlobalOptions().medpersonal_id) ? getGlobalOptions().medpersonal_id : 0;

			params.total_count = 0;
			params.refuse_count = 0;

			data.forEach(function(item) {
				params.total_count++;
				if (item.PersonMedicalIntervent_IsRefuse) {
					params.refuse_count++;
				}
			});

			params.is_refuse = (params.refuse_count > 0);

			return params;
		};

		Ext.Ajax.request({
			url: '/?c=PersonCard&m=loadPersonCardMedicalInterventGrid',
			params: {PersonCard_id: record.get('PersonCard_id')},
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					callback(processParams(response_obj));
				}
			}
		});
	},
	addPersonCard: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var current_window = this;
		var person_card_grid = current_window.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel;

		if (getWnd('swPersonCardEditWindow').isVisible())
		{
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты пациента уже открыто');
			return false;
		}
		
		// определяем, прикреплялся ли человек сегодня с этим типом прикрепления
		var options = getGlobalOptions();
		var date = options['date'];
		
		// так же проверяем наличие ДМС-прикрепления к другому ЛПУ для основного прикрепления
		if ( Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id == 'common_region' )
		{
			var any_row = person_card_grid.getStore().getAt(0);
			if ( any_row && any_row.get('Person_HasDmsOtherLpu') == 'true' )
			{
				Ext.Msg.alert('Ошибка', 'Человек имеет активное ДМС прикрепление. Изменение основного прикрепления невозможно.', function() {
					var grid = Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel;
					if ( grid.getStore().getCount() > 0 )
					{
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
				});
				return false;
			}
		}
		
		// так же проверяем наличие ДМС-прикрепления к другому ЛПУ для основного прикрепления
		if ( Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id == 'dms_region' )
		{
			var any_row = person_card_grid.getStore().getAt(0);
			if ( any_row && any_row.get('PersonCard_IsDmsForCheck') == 'true' )
			{
				Ext.Msg.alert('Ошибка', 'Человек уже имеет активное ДМС прикрепление.', function() {
					var grid = Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel;
					if ( grid.getStore().getCount() > 0 )
					{
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
				});
				return false;
			}
		}

		if ( person_card_grid.getStore().getCount() > 0 && !['service_region'].in_array(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id) )
		{			
			var index = person_card_grid.getStore().findBy(function(record, id) {
				if ( record.data.PersonCard_endDate == '' && Ext.util.Format.date(record.data.PersonCard_begDate, 'd.m.Y') == date )
					return true;
				else
					return false;
			});
			var last_row = person_card_grid.getStore().getAt( index );
			if ( last_row )
			{
				Ext.Msg.alert('Ошибка', 'Новое прикрепление пациента можно добавлять не чаще одного раза в день. Если пациент прикреплен к Вашему ЛПУ, то прикрепление может быть удалено или изменен участок только в течение даты прикрепления.', function() {
					var grid = Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel;
					if ( grid.getStore().getCount() > 0 )
					{
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
				});
				return false;
			}				
		}
		
		var lastAttachIsNotInOurLpu = false;
		var lastAttach_IsAttachCondit = false; //https://redmine.swan.perm.ru/issues/29930
		// определяем, прикреплена ли текущая открытая карта к другому ЛПУ
		if ( person_card_grid.getStore().getCount() > 0 )
		{
			var index = person_card_grid.getStore().findBy(function(record, id) {
				if ( record.data.PersonCard_endDate == '' && record.data.Is_OurLpu == 'false' )
					return true;
				else
					return false;
			});
			var last_row = person_card_grid.getStore().getAt( index );
			if ( last_row )
			{
				lastAttachIsNotInOurLpu = true;
				if(last_row.data.PersonCard_IsAttachCondit == 'true'){
					lastAttach_IsAttachCondit = true; //https://redmine.swan.perm.ru/issues/29930
				}
			}				
		}
        var othercardexists = 1;
        var personcard_record = person_card_grid.getSelectionModel().getSelected();
        if(personcard_record && personcard_record.data.PersonCard_id > 0)
            othercardexists = 1;
        else
            othercardexists = 0;
		var prev_beg_date = null;
		var prev_end_date = null;
		if(person_card_grid.getStore().getCount() > 0){ //https://redmine.swan.perm.ru/issues/84790
			person_card_grid.getSelectionModel().selectLastRow();
			var last_record = person_card_grid.getSelectionModel().getSelected();
			if(!Ext.isEmpty(last_record.get('PersonCard_begDate')))
				prev_beg_date = last_record.get('PersonCard_begDate');
			if(!Ext.isEmpty(last_record.get('PersonCard_endDate')))
				prev_end_date = last_record.get('PersonCard_endDate');
		}
		var form_params = {};
		form_params.attachType = Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id;
		form_params.action = 'add';
		form_params.lastAttachIsNotInOurLpu = lastAttachIsNotInOurLpu;
		form_params.lastAttach_IsAttachCondit = lastAttach_IsAttachCondit;
		form_params.Person_id = current_window.personId;
		form_params.otherCardExists = othercardexists;
		form_params.prev_beg_date = prev_beg_date;
		form_params.prev_end_date = prev_end_date;
		form_params.Server_id = current_window.serverId;
		form_params.callback = function() {
			Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
		};
		Ext.Ajax.request(
			{
				url: '/?c=PersonAmbulatCard&m=checkPersonAmbulatCard',
				params: {Person_id:current_window.personId},
				callback: function(options, success, response)
				{
					if (success)
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if ( typeof result != 'object' ) {
							sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к сервер (проверка наличия амбулаторных карт).');
							return false;
						}
						else if ( !Ext.isEmpty(result[0].Error_Msg) ) {
							sw.swMsg.alert('Предупреждение', 'Номер АК совпадает с существующим в базе', function(){
								getWnd('swPersonCardEditWindow').show(form_params);
							});
						}
						else if ( result.length > 0 && !Ext.isEmpty(result[0].PersonAmbulatCard_id) && !Ext.isEmpty(result[0].PersonCard_Code) ) {
							form_params.PersonAmbulatCard_id=result[0].PersonAmbulatCard_id;
							form_params.PersonCard_Code=result[0].PersonCard_Code;
							if(result[0].newPersonAmbulatCard_id){
								form_params.newPersonAmbulatCard_id=result[0].newPersonAmbulatCard_id;
							}
							if(result[0].PersonAmbulatCard_Count > 1)
							{
								sw.swMsg.alert('Предупреждение', 'Внимание! У данного пациента несколько амбулаторных карт.', function(){
									getWnd('swPersonCardEditWindow').show(form_params);
								});
							}
							else
							{
								getWnd('swPersonCardEditWindow').show(form_params);
							}
						} else if (getRegionNick().inlist(['ufa','pskov','hakasiya','kaluga'])) {
							getWnd('swPersonCardEditWindow').show(form_params);
						}
					}
				}
			});
	},
    checkAttachAllow: function(){
		if (Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id == 'common_region' && (
				(!Ext.isEmpty(getGlobalOptions().check_attach_allow) && getGlobalOptions().check_attach_allow == 1 && !isUserGroup('CardEditUser')) ||
				((Ext.isEmpty(getGlobalOptions().check_attach_allow) || getGlobalOptions().check_attach_allow != 1) && (!haveArmType('regpol') && !haveArmType('regpol6')) && !getRegionNick().inlist(['perm']))
			)){
				Ext.getCmp('PCardHF_PersonCardHistoryGrid').setReadOnly(true);
		} else {
			Ext.getCmp('PCardHF_PersonCardHistoryGrid').setReadOnly(false);
		}
		//Добавил отдельно (мало ли что, чтобы не громоздить большое условие) для https://redmine.swan.perm.ru/issues/64839
		if((isSuperAdmin() || isLpuAdmin()) && (Ext.isEmpty(getGlobalOptions().check_attach_allow) || getGlobalOptions().check_attach_allow != 1))
		{
			Ext.getCmp('PCardHF_PersonCardHistoryGrid').setReadOnly(false);
		}
		if(isUserGroup('CardCloseUser')){ //https://redmine.swan.perm.ru/issues/64998
			Ext.getCmp('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_edit', false);
		}
        //Отдельно для Пскова https://redmine.swan.perm.ru/issues/71434
        if(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id == 'common_region' && getRegionNick()== 'pskov' && isUserGroup('CardEditUser')){
            Ext.getCmp('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_add', false);
        }
	},
	editPersonCard: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		if (
			!this.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().getSelected()
			|| Ext.isEmpty(this.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().getSelected().get('PersonCard_id'))
		) {
			return false;
		}

		var current_window = this;
		var person_card_row = current_window.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().getSelected();
		var person_card_grid = current_window.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel;
		var oneCard = false;
		if(person_card_grid.getStore().getCount() == 1)
				oneCard = true;
		/*
		var action = 'edit';
		var options = getGlobalOptions();
		var date = options['date'];
		
		if ( Ext.util.Format.date(person_card_row.data.PersonCard_begDate, 'd.m.Y') != date && !['service_region', 'dms_region'].in_array(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id) && person_card_row.data.PersonCard_endDate == '' )
		{
			if(person_card_row.data.PersonCard_IsAttachCondit)
				action = 'edit_card_code_only';
			else
				action = 'view';
		}
			
		if ( person_card_row.data.Is_OurLpu == 'false' || (person_card_row.data.PersonCard_endDate != '' && !['service_region', 'dms_region'].in_array(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id)) )
		{
			//Ext.Msg.alert('Ошибка', 'Редактирование карты прикрепления, введенной в другом ЛПУ, запрещено.', function() {
				//Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().selectFirstRow();
				//Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getView().focusRow(0);
				action = 'view';
			//});
			//return false;
		}
		*/
		var person_card_id = person_card_row.data.PersonCard_id;

		if (getWnd('swPersonCardEditWindow').isVisible())
		{
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты пациента уже открыто');
			return false;
		}

        getWnd('swPersonCardEditWindow').show({
			action: 'edit',
			//real_action: 'edit',
			attachType: Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id,
        	callback: function() {
				Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
        	},
        	/*onHide: function() {
				Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
				var grid = current_window.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel;
				current_window.findById('PCardHF_PersonCardHistoryGrid').loadData();
				if ( grid.getStore().getCount() > 0 )
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
        	},*/
        	Person_id: current_window.personId,
//        	PersonEvn_id: person_data.PersonEvn_id,
        	Server_id: current_window.serverId,
        	PersonCard_id: person_card_id,
        	oneCard: oneCard
        });
		
		/*
		if ( action == 'edit_card_code_only' )
		{
			Ext.Msg.alert('Внимание', 'Смена участка не доступна в день, отличный от даты прикрепления. Для смены участка добавьте новую карту.', function() {
				return true;
			});
		}
		*/
	},
	viewPersonCard: function() {
		/*if ( this.action == 'view' ) {
			return false;
		}*/

		if (
			!this.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().getSelected()
			|| Ext.isEmpty(this.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().getSelected().get('PersonCard_id'))
		) {
			return false;
		}

		var current_window = this;

		if (getWnd('swPersonCardEditWindow').isVisible())
		{
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты пациента уже открыто');
			return false;
		}

        getWnd('swPersonCardEditWindow').show({
			action: 'view',
			attachType: Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id,
        	callback: function() {
				Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
			},
        	/*onHide: function() {
				Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());	
				var grid = current_window.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel;
				if ( grid.getStore().getCount() > 0 )
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
			},*/
        	Person_id: current_window.personId,
//        	PersonEvn_id: person_data.PersonEvn_id,
        	Server_id: current_window.serverId,
        	PersonCard_id: current_window.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().getSelected().data.PersonCard_id
        });
	},
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	deletePersonCard: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var current_window = this;
  		var grid = current_window.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();

		if ( typeof current_row != 'object' || Ext.isEmpty(current_row.get('PersonCard_id')) ) {
			return false;
		}

		var options = getGlobalOptions();
		var date = options['date'];
		/*
		 *	1. Если прикрепление не активное, то вывести сообщение: «Закрытое прикрепление не может быть удалено. (Ок)»:
		 *	Ок – форму закрыть, дальнейшие действия отменить.
		 */
		if ( Ext.util.Format.date(current_row.data.PersonCard_endDate, 'd.m.Y') != '' && Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id != 'dms_region' )
		{
			Ext.Msg.alert('Ошибка', 'Закрытое прикрепление не может быть удалено.', function() {
				if ( grid.getStore().getCount() > 0 )
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
			});
			return false;
		}
		/*
		 *	2. Если пользователь не Администратор ЦОД и МУ прикрепления не равно МУ
		 *	Пользователя, то вывести сообщение: «Прикрепление к другому МУ удалено быть не может (Ок)»:
		 *	Ок – форму закрыть, дальнейшие действия отменить.
		 */
		if ( current_row.data.Is_OurLpu == 'false' && !getGlobalOptions().superadmin )
		{
			Ext.Msg.alert('Ошибка', 'Прикрепление к другому МУ удалено быть не может.', function() {
				Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().selectFirstRow();
				Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getView().focusRow(0);
			});
			return false;
		}
		
		/*if ( Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id == 'common_region' && current_row.get('PersonCard_IsDmsForCheck') == 'true' )
		{
			Ext.Msg.alert('Ошибка', 'Нельзя удалять основное прикрепление при наличии действующего прикрепления по ДМС.', function() {
				var grid = Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel;
				if ( grid.getStore().getCount() > 0 )
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
			});
			return false;
		}*/

		if ( Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id == 'dms_region' )
		{
			if ( Ext.util.Format.date(current_row.data.PersonCard_insDate, 'd.m.Y') == date )
			{
				// остальные прикрепления
				sw.swMsg.show({
					title: 'Подтверждение удаления',
					msg: 'Вы действительно желаете удалить эту запись?',
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' )
						{
							Ext.Ajax.request({
								url: '/?c=PersonCard&m=deleteDmsPersonCard',
								params: {PersonCard_id: current_row.data.PersonCard_id},
								callback: function() {
									Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
								}
							});
						}
					}
				});
			}
			else
			{
				Ext.Msg.alert('Ошибка', 'ДМС прикрепление может быть удалено только в дату создания.', function() {
					Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().selectFirstRow();
					Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getView().focusRow(0);
				});
				return false;
			}
			return false;
		}

		/*
		 * 3. Если дата прикрепления не текущая и у человека нет действующего полиса и МУ прикрепления равно МУ Пользователя, вывести сообщение: «Открепить пациента? (Да/Нет):
		 *	- Да - установить дату открепления равной текущей дате, и
		 *	если у человека проставлена "дата смерти", то в поле "причина закрытия" подставить значение "2. смерть",
		 *	если у человека не проставлена "дата смерти", то в поле "причина закрытия" подставить значение "5. снялся с учета",
		 *	т.е. пациент останется не прикрепленным ни к одной МУ, форму закрыть, дальнейшие действия отменить.
		 *	- Нет – форму закрыть, дальнейшие действия отменить.
		 */
        var do_delete = false;
        if (
            (getGlobalOptions().region.nick == 'khak')
                &&
                Ext.util.Format.date(current_row.data.PersonCard_endDate, 'd.m.Y') == ''
                &&
                (
                    (
                        isPolkaRegistrator()
                            &&
                            Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') == date
                        )
                        ||
                        isLpuAdmin()
                        ||
                        isSuperAdmin()
                    )
            )
        {
            do_delete = true;
        }
        
        if(grid.getStore().getCount() == 1) //Если это последняя запись - https://redmine.swan.perm.ru/issues/108560
        	do_delete = true;
		
		var Person_HasPolis = current_row.data.Person_HasPolis;
		if ( Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') != date && Person_HasPolis == 'false' && current_row.data.Is_OurLpu == 'true' && !do_delete)
		{
			sw.swMsg.show({
				title: 'Подтверждение открепления',
				msg: 'Открепить пациента?',
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' )
					{
						Ext.Ajax.request({
							url: '?c=PersonCard&m=closePersonCard',
							params: {PersonCard_id: current_row.data.PersonCard_id},
							callback: function(options, success, response) {
								if (success)
								{
									if ( response.responseText.length > 0 )
									{
										var resp_obj = Ext.util.JSON.decode(response.responseText);
										if (resp_obj[0].success == false)
										{
											if ( resp_obj[0].Error_Code && resp_obj[0].Error_Code == 666 )
											{
												sw.swMsg.show({
													title: 'Подтверждение открепления',
													msg: resp_obj[0].Error_Msg,
													buttons: Ext.Msg.YESNO,
													fn: function ( buttonId ) {
														if ( buttonId == 'yes' )
														{
															Ext.Ajax.request({
																url: '?c=PersonCard&m=closePersonCard',
																params: {PersonCard_id: current_row.data.PersonCard_id, cancelDrugRequestCheck: 2},
																callback: function(options, success, response) {
																	Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
																}
															});
														}
													}
												});
											}
										}
									}
								}
								Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
							}
						});
					}
				}
			});
			return false;
		}
		
		var Person_IsBDZ = current_row.data.Person_IsBDZ;
		var Person_IsFedLgot = current_row.data.Person_IsFedLgot;
		
		/*
		 * 4. Если дата прикрепления не текущая и человек не из БДЗ и МУ прикрепления равно МУ Пользователя, вывести сообщение: «Открепить пациента? (Да/Нет):
		 *	- Да - установить дату открепления равной текущей дате и в поле "причина закрытия" подставить значение "5. снялся с учета", т.е. пациент останется не прикрепленным ни к одной МУ, форму закрыть, дальнейшие действия отменить.
		 *	- Нет – форму закрыть, дальнейшие действия отменить.
		 */		
		if ( Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') != date && Person_IsBDZ == 'false' && current_row.data.Is_OurLpu == 'true' &&!do_delete)
		{
			sw.swMsg.show({
				title: 'Подтверждение открепления',
				msg: 'Открепить пациента?',
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' )
					{
						Ext.Ajax.request({
							url: '?c=PersonCard&m=closePersonCard',
							params: {PersonCard_id: current_row.data.PersonCard_id},
							callback: function(options, success, response) {
								if (success)
								{
									if ( response.responseText.length > 0 )
									{
										var resp_obj = Ext.util.JSON.decode(response.responseText);
										if (resp_obj[0].success == false)
										{
											if ( resp_obj[0].Error_Code && resp_obj[0].Error_Code == 666 )
											{
												sw.swMsg.show({
													title: 'Подтверждение открепления',
													msg: resp_obj[0].Error_Msg,
													buttons: Ext.Msg.YESNO,
													fn: function ( buttonId ) {
														if ( buttonId == 'yes' )
														{
															Ext.Ajax.request({
																url: '?c=PersonCard&m=closePersonCard',
																params: {PersonCard_id: current_row.data.PersonCard_id, cancelDrugRequestCheck: 2},
																callback: function(options, success, response) {
																	Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
																}
															});
														}
													}
												});
											}
										}
									}
								}
								Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
							}
						});
					}
				}
			});
			return false;
		}
		
		/*
		* 5. Если дата прикрепления не текущая и человек из БДЗ и Пользователь – Пользователь МУ вывести сообщение 
		* «Прикрепление застрахованного человека может быть удалено только в дату прикрепления (Ок)»:
		* Ок – форму закрыть, дальнейшие действия отменить.
		*/			   
		if ( Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') != date && Person_IsBDZ == 'true' && !getGlobalOptions().superadmin )
		{
			Ext.Msg.alert('Ошибка', 'Прикрепление застрахованного человека может быть удалено только в дату прикрепления.', function() {
				Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().selectFirstRow();
				Ext.getCmp('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getView().focusRow(0);
			});
			return false;
		}
		

	   
	   // остальные прикрепления
		sw.swMsg.show({
			title: 'Подтверждение удаления',
			msg: 'Вы действительно желаете удалить эту запись?',
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						url: C_PERSONCARD_DEL,
						params: {PersonCard_id: current_row.data.PersonCard_id},
                        callback: function (options, success, response) {
                            if (success) {
                                if (response.responseText.length > 0) {
                                    var resp_obj = Ext.util.JSON.decode(response.responseText);
                                    if (resp_obj[0] && resp_obj[0].success == false) {
                                        if (resp_obj[0].Error_Code && resp_obj[0].Error_Code == 777) {
                                            sw.swMsg.show({
                                                title: 'Подтверждение удаления',
                                                msg: resp_obj[0].Error_Msg,
                                                buttons: Ext.Msg.YESNO,
                                                fn: function (buttonId) {
                                                    if (buttonId == 'yes') {
                                                        Ext.Ajax.request({
                                                            url: C_PERSONCARD_DEL,
                                                            params: {PersonCard_id: current_row.data.PersonCard_id, isLastAttach: 2},
                                                            callback: function (options, success, response) {
                                                            	var fieldPersonCard = Ext.getCmp('PersonCardEditForm').getForm().findField('PersonCard_id');
                                                            	if(fieldPersonCard && current_row.data.PersonCard_id == fieldPersonCard.getValue()){
                                                            		fieldPersonCard.setValue(-1);
                                                            	}
                                                                Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
                                                            }
                                                        });
                                                    }
                                                }
                                            });
                                        }
                                    } else {
                                    	var fieldPersonCard = Ext.getCmp('PersonCardEditForm').getForm().findField('PersonCard_id');
                                    	if(fieldPersonCard && current_row.data.PersonCard_id == fieldPersonCard.getValue()){
                                    		fieldPersonCard.setValue(-1);
                                    	}
                                        Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
                                    }
                                }
                            }
                        }
					});
				}
			}
		});

		return false;
			
		// !!!!!!!!! старые проверки !!!!!!!!!!

		if ( Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') == date )
		{
			sw.swMsg.show({
			title: 'Подтверждение удаления',
			msg: 'Вы действительно желаете удалить эту запись?',
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
					if ( buttonId == 'yes' )
					{
						Ext.Ajax.request({
							url: C_PERSONCARD_DEL,
							params: {PersonCard_id: current_row.data.PersonCard_id},
							callback: function() {
								Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
							}
						});
					}
				}
			});
			return;
		}

		// закрываем не БДЗшных и не федералов
		if ( Person_IsBDZ == "false" && Person_IsFedLgot == "false" )
		{
			sw.swMsg.show({
				title: 'Подтверждение открепления',
				msg: 'Открепить пациента?',
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' )
					{
						Ext.Ajax.request({
							url: '?c=PersonCard&m=closePersonCard',
							params: { PersonCard_id: current_row.data.PersonCard_id },
							callback: function(options, success, response) {
								if (success)
								{
									if ( response.responseText.length > 0 )
									{
										var resp_obj = Ext.util.JSON.decode(response.responseText);
										if (resp_obj[0].success == false)
										{
											if ( resp_obj[0].Error_Code && resp_obj[0].Error_Code == 666 )
											{
												sw.swMsg.show({
													title: 'Подтверждение открепления',
													msg: resp_obj[0].Error_Msg,
													buttons: Ext.Msg.YESNO,
													fn: function ( buttonId ) {
														if ( buttonId == 'yes' )
														{
															Ext.Ajax.request({
																url: '?c=PersonCard&m=closePersonCard',
																params: { PersonCard_id: current_row.data.PersonCard_id, cancelDrugRequestCheck: 2 },
																callback: function(options, success, response) {
																	Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
																}
															});
														}
													}
												});
											}
										}
									}
								}
								Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
							}
						});
					}
				}
			});
			return;
		}
		// закрываем карты не основного прикрепления
		if ( Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id == 'service_region' )
		{
			sw.swMsg.show({
				title: 'Подтверждение открепления',
				msg: 'Открепить пациента?',
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' )
					{
						Ext.Ajax.request({
							url: '?c=PersonCard&m=closePersonCard',
							params: { PersonCard_id: current_row.data.PersonCard_id },
							callback: function(options, success, response) {
								if (success)
								{
									if ( response.responseText.length > 0 )
									{
										var resp_obj = Ext.util.JSON.decode(response.responseText);
										if (resp_obj[0].success == false)
										{
											if ( resp_obj[0].Error_Code && resp_obj[0].Error_Code == 666 )
											{
												sw.swMsg.show({
													title: 'Подтверждение открепления',
													msg: resp_obj[0].Error_Msg,
													buttons: Ext.Msg.YESNO,
													fn: function ( buttonId ) {
														if ( buttonId == 'yes' )
														{
															Ext.Ajax.request({
																url: '?c=PersonCard&m=closePersonCard',
																params: { PersonCard_id: current_row.data.PersonCard_id, cancelDrugRequestCheck: 2 },
																callback: function(options, success, response) {
																	Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
																}
															});
														}
													}
												});
											}
										}
									}
								}
								Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
							}
						});
					}
				}
			});
			return;
		}
		
		if ( Ext.util.Format.date(current_row.data.PersonCard_begDate, 'd.m.Y') != date )
		{
			// суперадмину можно хоть когда
			if ( !getGlobalOptions().superadmin )
			{
				Ext.Msg.alert('Ошибка', 'Можно удалить карту только в дату создания.', function() {
					if ( grid.getStore().getCount() > 0 )
					{
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
				});
				return false;
			}
		}
		sw.swMsg.show({
			title: 'Подтверждение удаления',
			msg: 'Вы действительно желаете удалить эту запись?',
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						url: C_PERSONCARD_DEL,
						params: {PersonCard_id: current_row.data.PersonCard_id},
						callback: function() {
							Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
						}
					});
				}
			}
		});
	},
	draggable: true,
	height: 400,
	id: 'PersonCardHistoryWindow',
	initComponent: function() {
		var _this = this;

		Ext.apply(this, {
			buttons: [
			{
				disabled: true,
				hidden: true,
				handler: function() {
					Ext.Msg.alert(BTN_GRIDPRINT, 'Печать истории прикреплений');
				},
				iconCls: 'print16',
				text: BTN_GRIDPRINT
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.returnFunc();
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}
			],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'PCardHF_PersonInformationFrame',
				region: 'north'
			}),
			new Ext.Panel({
				region: 'center',
				layout: 'border',
				border: false,
				items: [
					new Ext.TabPanel({
						id: 'PCHW_regions_tab_panel',
						activeTab: 0,
						border: false,
						layoutOnTabChange: true,
						listeners: {
							'beforetabchange': function(panel, tab) {
                                if(tab.id == 'common_region')
                                {
                                    Ext.getCmp('PCardHF_PersonCardHistoryGrid').getGrid().getColumnModel().setColumnHeader(12,'Тип основного участка');
                                    Ext.getCmp('PCardHF_PersonCardHistoryGrid').getGrid().getColumnModel().setColumnHeader(13,'Основной участок');
                                    Ext.getCmp('PCardHF_PersonCardHistoryGrid').getGrid().getColumnModel().setHidden(14,false);

									if (getRegionNick() == 'kz') {
										Ext.getCmp('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_add', true);
									}
                                }
                                else
                                {
                                    Ext.getCmp('PCardHF_PersonCardHistoryGrid').getGrid().getColumnModel().setColumnHeader(12,'Тип участка');
                                    Ext.getCmp('PCardHF_PersonCardHistoryGrid').getGrid().getColumnModel().setColumnHeader(13,'Участок');
                                    Ext.getCmp('PCardHF_PersonCardHistoryGrid').getGrid().getColumnModel().setHidden(14,true);

									Ext.getCmp('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_add', false);
                                }
								var current_window = Ext.getCmp('PersonCardHistoryWindow');
								var grid = current_window.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel;
								/*if ( tab.id == 'dms_region' )
										current_window.findById('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_delete', true);
									else
										current_window.findById('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_delete', false);
								*/
								grid.getStore().removeAll();
								if (current_window.personId)
								{
									grid.getStore().load({
										params: {
											Person_id: current_window.personId,
											AttachType: tab.id
										},
										callback: function() {
											if ( grid.getStore().getCount() > 0 )
											{
												grid.getSelectionModel().selectFirstRow();
												grid.getView().focusRow(0);
											}
										}
									});
								}
							}
						},
						region: 'north',
						items: [{
							id: 'common_region',
							title: '1. Основное',
							height: 0,
							style: 'padding: 0px',
							layout:'form',
							items: []
						}, {
							title: '2. Гинекология',
							id: 'ginecol_region',
							height: 0,
							style: 'padding: 0px',
							layout:'form',
							items: []
						}, {
							title: '3. Стоматология',
							id: 'stomat_region',
							height: 0,
							style: 'padding: 0px',
							layout:'form',
							items: []
						}, {
							title: '4. Служебный',
							id: 'service_region',
							height: 0,
							style: 'padding: 0px',
							layout:'form',
							items: []
						}, {
							title: '5. ДМС',
							id: 'dms_region',
							hidden: !getGlobalOptions().lpu_is_dms,
							height: 0,
							style: 'padding: 0px',
							layout:'form',
							items: []
						}]
					}),
		            new sw.Promed.ViewFrame(
					{
						actions:
						[
							{name: 'action_add', tooltip: 'Новое прикрепление пациента можно создавать только один раз в день', handler: function() {Ext.getCmp('PersonCardHistoryWindow').addPersonCard(); }},
							{name: 'action_edit', handler: function() {Ext.getCmp('PersonCardHistoryWindow').editPersonCard(); }},
							{name: 'action_view', handler: function() {Ext.getCmp('PersonCardHistoryWindow').viewPersonCard(); }},
							{name: 'action_delete', handler: function() {Ext.getCmp('PersonCardHistoryWindow').deletePersonCard(); }},
							{name: 'action_refresh'},
							{name: 'action_print',
								menuConfig: {
									printMedCard: {
										name: 'printMedCard',
										text: 'Печать мед карты пациента',
										handler: function(){
											// нужно, чтобы по этой кнопке печаталась амбулаторная карта с выбранным прикреплением. #16564
											var grid = Ext.getCmp('PCardHF_PersonCardHistoryGrid').getGrid();
											if (!grid.getSelectionModel().getSelected()) {
												return false;
											}

											if (getRegionNick() =='ufa'){
												printMedCard4Ufa(grid.getSelectionModel().getSelected().get('PersonCard_id'));
												return;
											}
											if(getRegionNick().inlist([ 'buryatiya', 'astra', 'perm', 'ekb', 'pskov', 'krym', 'khak', 'kaluga' ])){
												var PersonCard = 0;
												if(!Ext.isEmpty(grid.getSelectionModel().getSelected().get('PersonCard_id'))){
													var PersonCard = grid.getSelectionModel().getSelected().get('PersonCard_id');
												}
												printBirt({
							                        'Report_FileName': 'pan_PersonCard_f025u.rptdesign',
							                        'Report_Params': '&paramPerson=' + grid.getSelectionModel().getSelected().get('Person_id') + '&paramPersonCard=' + PersonCard + '&paramLpu=' + getLpuIdForPrint(),
							                        'Report_Format': 'pdf'
							                    });
											} else {

												this.getLoadMask().show();
												Ext.Ajax.request({
													callback: function(options, success, response) {
														this.getLoadMask().hide();
														var responseObj = Ext.util.JSON.decode(response.responseText);
														if ( success && responseObj.success ) {
															if ( getRegionNick() == 'ekb' ) {
																openNewWindow(responseObj.result1);
																openNewWindow(responseObj.result2);
															}else{
																openNewWindow(responseObj.result);
															}

														}
														else {
															sw.swMsg.alert('Ошибка', 'При получении данных для печати мед. карты произошла ошибка');
														}
													}.createDelegate(this),
													params: {
														PersonCard_id: grid.getSelectionModel().getSelected().get('PersonCard_id'),
														Person_id: grid.getSelectionModel().getSelected().get('Person_id')
													},
													url : '/?c=PersonCard&m=printMedCard'
												});
											}
										}.createDelegate(this)
									},
									printAgreement: {
										hidden: !(getRegionNick() =='ufa'),
										name: 'printAgreement',
										text: 'Печать инф. согласия',
										handler: function(){this.printAttachBlank();}.createDelegate(this)
									}
								}
							}

						],
		//					autoExpandColumn: 'autoexpand',
						autoLoadData: false,
						border: false,
						dataUrl: C_PERSONCARD_HIST,
						id: 'PCardHF_PersonCardHistoryGrid',
		//				focusOn: {name:'PCSW_SearchButton', type:'field'},
		//					object: 'LpuUnit',
						region: 'center',
						//editformclassname: swLpuUnitEditForm,
						stringfields:
						[
							{name: 'PersonCard_id', type: 'int', header: 'ID', key: true},
							{name: 'Person_id', type: 'int', hidden: true},
							{name: 'PersonCard_Code',  type: 'string', header: '№ амб карты'},
							{name: 'PersonCard_insDate', type: 'date', hidden: true},
							{name: 'PersonCard_begDate',  type: 'date', header: 'Прикрепление', renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'PersonCard_endDate',  type: 'date', header: 'Открепление', renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'Lpu_Nick',  type: 'string', header: 'МО прикрепления'},
							{name: 'PersonCard_IsDms',  type: 'checkbox', header: 'Прикреплен по ДМС', width: 120},
							{name: 'Is_OurLpu',  type: 'string', hidden: true},
							{name: 'PersonCard_IsDmsForCheck',  type: 'string', hidden: true},
							{name: 'Person_IsBDZ',  type: 'string', hidden: true},
							{name: 'Person_IsFedLgot',  type: 'string', hidden: true},
							{name: 'Person_HasPolis',  type: 'string', hidden: true},
							{name: 'Person_HasDmsOtherLpu',  type: 'string', hidden: true},
							{name: 'LpuRegionType_Name',  type: 'string', header: 'Тип участка'},
							{name: 'LpuRegion_Name',  type: 'string', header: 'Участок'},
                            {name: 'LpuRegion_FapName', type: 'string', header: 'ФАП участок',width:100},
							{name: 'CardCloseCause_Name',  type: 'string', header: 'Причина закрытия', width: 150},
							{name: 'PersonCard_IsAttachCondit', header: 'Усл. прикрепл.', width: 150, type: 'checkbox'},
						 	{name: 'PersonCardAttach', header: 'Заявит. прикрепл..', width: 150, type: 'checkbox'},
							{name: 'PersonAmbulatCard_id',hidden:true,type:'int'}/*,
							{name: 'PersonCard_IsDisp',  type: 'string', header: 'Д-Учет'}*/
						],
						toolbar: true,
						onRowSelect: function(sm,rowIdx,record){
							var disable = true,
								options = getGlobalOptions();

							if ( typeof record == 'object' && !Ext.isEmpty(record.get('PersonCard_id')) ) {
								if(true) {
									disable = !( 
										record.get('PersonCard_endDate') == '' && 
										record.get('Is_OurLpu') == 'true' /* && (
											Ext.util.Format.date(record.get('PersonCard_begDate'), 'd.m.Y') == options.date ||
											record.get('Person_IsBDZ') == 'false' ||
											record.get('PersonCard_IsAttachCondit') == 'true'
										)*/
									);
									if ( Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id == 'dms_region' )
										disable = false;
									if ( isSuperAdmin() )
										disable = false;
									this.setActionDisabled('action_edit', disable);
								}
								if(true) {
									disable = !( record.get('PersonCard_endDate') == '' && Ext.util.Format.date(record.get('PersonCard_begDate'), 'd.m.Y') == options.date && record.get('Is_OurLpu') == 'true' && record.get('Person_IsBDZ') == 'true');
									if ( isSuperAdmin() )
										disable = false;
									this.setActionDisabled('action_delete', disable);
								}
                                if(getRegionNick() == 'astra' || getRegionNick() == 'perm')
                                {
                                    this.setActionDisabled('action_add_lpuregion', true);
                                    if(Ext.isEmpty(record.get('LpuRegion_Name')) && record.get('PersonCard_endDate') == '')
                                        this.setActionDisabled('action_add_lpuregion', false);
                                    else
                                        _this.allowEditLpuRegion = 0;
                                }
                                else
                                    _this.allowEditLpuRegion = 0;
                                if(getRegionNick().inlist([ 'ufa', 'khak' ]))
                                {
                                    if(
                                        record.get('Is_OurLpu') == 'true'
                                        && record.get('PersonCard_endDate') == ''
                                        &&
                                        (
                                            (
                                                isPolkaRegistrator()
                                                    &&
                                                    Ext.util.Format.date(record.get('PersonCard_begDate'), 'd.m.Y') == options.date
                                            )
                                            || isLpuAdmin()
                                            || isSuperAdmin()
                                            )
                                        )
                                    {
                                        this.setActionDisabled('action_delete', false);
                                    }
                                }
							}
							else {
								this.setActionDisabled('action_edit', true);
								this.setActionDisabled('action_view', true);
								this.setActionDisabled('action_delete', true);
							}
							_this.checkAttachAllow();
						},
						onLoadData: function(data_is_exists){
							var type_panel = this.findById('PCHW_regions_tab_panel'),
								pc_grid = this.findById('PCardHF_PersonCardHistoryGrid').getGrid(),
								disable = true,
								options = getGlobalOptions(),
								index;
							if(true) {
								if( !data_is_exists )
									disable = false;
								else {
									//ищем созданное сегодня актуальное (незакрытое) прикрепление
									index = pc_grid.getStore().findBy(function(record, id) {
										if ( record.get('PersonCard_endDate') == '' && Ext.util.Format.date(record.get('PersonCard_begDate'), 'd.m.Y') == options.date )
											return true;
										else
											return false;
									});
									disable = (index >= 0);
								}
								if ( type_panel.getActiveTab().id == 'service_region' ) {
									//ищем актуальное (незакрытое) прикрепление текущего ЛПУ
									index = pc_grid.getStore().findBy(function(record, id) {
										if ( record.get('PersonCard_endDate') == '' && record.get('Is_OurLpu') == 'true')
											return true;
										else
											return false;
									});
									disable = (index >= 0);
								}
								
								if ( isSuperAdmin() ) {
									disable = false;
								}

								if (getRegionNick() == 'kz' && type_panel.getActiveTab().id == 'common_region') {
									disable = true;
								}

								if ( disable == false && this.action == 'view' ) {
									disable = true;
								}

								this.findById('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_add', disable);
								//this.findById('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_view', this.action == 'view');
							}
							_this.checkAttachAllow();
						}.createDelegate(this)
					})
				]
			})]
		});
		sw.Promed.swPersonCardHistoryWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			if ( e.getKey() == Ext.EventObject.P )
			{
				var current_window = Ext.getCmp('PersonCardHistoryWindow');
				current_window.hide();
			}

			var tab_panel = Ext.getCmp('PCHW_regions_tab_panel');
			switch ( e.getKey() )
			{
            	case Ext.EventObject.NUM_ONE:
            	case Ext.EventObject.ONE:
            	    tab_panel.setActiveTab(0);
            	    break;

            	case Ext.EventObject.NUM_TWO:
            	case Ext.EventObject.TWO:
               	    tab_panel.setActiveTab(1);
            	    break;

            	case Ext.EventObject.NUM_THREE:
            	case Ext.EventObject.THREE:
               	    tab_panel.setActiveTab(2);
            	    break;

            	case Ext.EventObject.NUM_FOUR:
            	case Ext.EventObject.FOUR:
               	    tab_panel.setActiveTab(3);
            	    break;

            	case Ext.EventObject.NUM_FIVE:
            	case Ext.EventObject.FIVE:
               	    tab_panel.setActiveTab(4);
            	    break;
			}

		},
		key: [
			Ext.EventObject.P,
   		    Ext.EventObject.NUM_ONE,
            Ext.EventObject.NUM_TWO,
   		    Ext.EventObject.NUM_THREE,
            Ext.EventObject.NUM_FOUR,
   		    Ext.EventObject.NUM_FIVE,
   		    Ext.EventObject.ONE,
            Ext.EventObject.TWO,
   		    Ext.EventObject.THREE,
            Ext.EventObject.FOUR,
   		    Ext.EventObject.FIVE
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 600,
	modal: true,
	personId: null,
	plain: true,
	resizable: true,
	returnFunc: Ext.emptyFn,
	serverId: 0,
	show: function() {
		sw.Promed.swPersonCardHistoryWindow.superclass.show.apply(this, arguments);

		this.action = 'edit';
		this.onHide = Ext.emptyFn;
        this.allowEditLpuRegion = 0;
        var current_window = this;
       
		if (arguments[0])
		{
			if (arguments[0].action)
			{
				this.action = arguments[0].action;
			}

			if (arguments[0].callback)
			{
				this.returnFunc = arguments[0].callback;
			}

			if (arguments[0].onHide)
			{
				this.onHide = arguments[0].onHide;
			}

			if (arguments[0].Person_id)
			{
				this.personId = arguments[0].Person_id;
			}

			if (arguments[0].Server_id >= 0)
			{
				this.serverId = arguments[0].Server_id;
			}			
		}

		if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
			this.action = 'view';
		
		var _this = this;

		//this.findById('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_add', this.action == 'view');
		//this.findById('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_edit', this.action == 'view');
		this.findById('PCardHF_PersonCardHistoryGrid').setActionDisabled('action_print', this.action == 'view');
		this.findById('PCardHF_PersonCardHistoryGrid').getAction('action_add').setHidden(this.action == 'view');
		this.findById('PCardHF_PersonCardHistoryGrid').getAction('action_edit').setHidden(this.action == 'view');

        if(getRegionNick() == 'astra' || getRegionNick() == 'perm')
        {
            if(!this.findById('PCardHF_PersonCardHistoryGrid').getAction('action_add_lpuregion'))
            {
                this.findById('PCardHF_PersonCardHistoryGrid').addActions({
                    name: 'action_add_lpuregion',
                    text: 'Добавить участок',
                    hidden: (_this.action == 'view'),
                    handler: function()
                    {
                        getWnd('swPersonCardEditWindow').show({
                            action: 'edit',
                            attachType: Ext.getCmp('PCHW_regions_tab_panel').getActiveTab().id,
                            callback: function() {
                                Ext.getCmp('PCHW_regions_tab_panel').setActiveTab(Ext.getCmp('PCHW_regions_tab_panel').getActiveTab());
                            },
                            Person_id: current_window.personId,
                            Server_id: current_window.serverId,
                            allowEditLpuRegion: 1,
                            PersonCard_id: current_window.findById('PCardHF_PersonCardHistoryGrid').ViewGridPanel.getSelectionModel().getSelected().data.PersonCard_id
                        });
                        Ext.getCmp('PersonCardViewAllWindow').openPersonCardEditWindow({action: 'edit'});
                    }
                });
            }
        }

		this.findById('PCardHF_PersonInformationFrame').load({
			Person_id: this.personId,
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});
		
		var regions_tab_panel = Ext.getCmp('PCHW_regions_tab_panel');
		
		if ( getGlobalOptions().lpu_is_dms )
			regions_tab_panel.unhideTabStripItem('dms_region');
		else
			regions_tab_panel.hideTabStripItem('dms_region');

		regions_tab_panel.setActiveTab(0);

		this.restore();
		this.center();
	},
	title: 'История прикреплений пациента',
	width: 700
});
