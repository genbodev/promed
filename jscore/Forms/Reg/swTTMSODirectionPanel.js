/**
 * Панель выписки направления на службу/услугу
 */
sw.Promed.swTTMSODirectionPanel = Ext.extend(sw.Promed.swTTMSRecordPanel, {
	id: 'TTMSDirectionPanel',
	
	/**
	 * Функция вызываемая после успешной записи 
	 */
	onDirection: Ext.emptyFn,
	
	/**
	 * Печать расписания
	 */
	printSchedule: function() {
		var id_salt = Math.random();
		var win_id = 'print_ttms_edit' + Math.floor(id_salt * 10000);
		window.open(C_TTMSO_LISTFOREDITPRINT + '&StartDay=' + this.date + '&MedService_id=' + this.MedService_id, win_id);
	},

	/**
	 * Загрузка расписания
	 *
	 * @param date Дата, начиная с которой загружать расписание
	 */
	loadSchedule: function(date)
	{
		if (date) {
			this.date = date;
		}

		if (this.UslugaComplexMedService_id) {
			var url = C_TTUC_LISTFORREC;
		} else {
			var url = C_TTMSO_LISTFORREC;
		}
		this.load(
			{
				url: url,
				params: {
					IsForDirection: 1,
					StartDay: this.date,
					MedService_id: this.MedService_id,
					UslugaComplexMedService_id: this.UslugaComplexMedService_id,
					PanelID: this.id // отправляем идентификатор панели для правильной генерации HTML
				},
				scripts:true,
                timeout: 300,
				text: lang['podojdite_idet_zagruzka_raspisaniya'],
				callback: function () {
					//
				}.createDelegate(this),
				failure: function () {
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_polucheniya_raspisaniya_poprobuyte_esche_raz']);
				}
			}
		);
	},

	/**
	 * Освобождение времени
	 */
	clearTime: function(time_id, evndirection_id)
	{
		if (time_id) {
			this.TimetableMedServiceOrg_id = time_id;
		}

		sw.swMsg.show({
			title: lang['podtverjdenie'],
			msg: lang['vyi_deystvitelno_jelaete_osvobodit_vremya_priema'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
					loadMask.show();
					submitClearTime(
						{
							id: this.TimetableMedServiceOrg_id,
							type: 'medserviceorg',
							DirFailType_id: null,
							EvnComment_Comment: null
						},
						function(options, success, response) {
							loadMask.hide();
							this.loadSchedule();
						}.createDelegate(this),
						function() {
							loadMask.hide();
						}
					);
				}
			}.createDelegate(this)
		});
	},
	
	recordOrg: function(time_id, date, time)
	{
       var win =this;
		getWnd('swOrgSearchWindow').show({
		object: 'lpu',
        onClose: function() {
           
        },
        onSelect: function(orgData) {
			     log(orgData)
            if ( orgData.Org_id > 0 ) {
				Ext.Ajax.request({
					url: '/?c=TimetableMedServiceOrg&m=Apply',
					callback: function(options, success, response)  {
						win.onDirection();
					},
					params: {
						TimetableMedServiceOrg_id: time_id,
						Org_id:orgData.Org_id
					}
				});
            }
            getWnd('swOrgSearchWindow').hide();
        }
    });
	},
	initComponent: function() {
		sw.Promed.swTTMSODirectionPanel.superclass.initComponent.apply(this, arguments);
    }
});