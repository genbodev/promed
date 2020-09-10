/**
* swTTGScheduleEditWindow - окно редактирования расписания врача поликлиники
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      03.10.2011
*/

sw.Promed.swTTGScheduleEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	id: 'TTGScheduleEditWindow',
	title: WND_TTGSEW,

	/**
	 * Панель с расписанием для редактирования
	 */
	TTGScheduleEditPanel: null,
	
	
    initComponent: function() {
		
		// Панель редактирования расписания
		this.TTGScheduleEditPanel = new sw.Promed.swTTGScheduleEditPanel({
			id:'TTGScheduleEdit',
			frame: false,
			border: false,
			region: 'center'
		});
		
	    Ext.apply(this, {
			border: false,
			layout: 'border',
			items: [
				this.TTGScheduleEditPanel
			],
			buttons: [
				{
					text: '-'
				},
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event) {
						ShowHelp(lang['rabota_s_zapisyu']);
					}.createDelegate(this)
				},
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function() { this.hide() }.createDelegate(this)
				}
			],
			keys: [{
				key: [
					Ext.EventObject.F2,
					Ext.EventObject.F5,
					Ext.EventObject.F9
				],
				fn: function(inp, e) {
					e.stopEvent();
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					switch (e.getKey())
					{
						case Ext.EventObject.F2:
							this.TTGScheduleEditPanel.openFillWindow();
						break;
						
						case Ext.EventObject.F5:
							this.TTGScheduleEditPanel.loadSchedule();
						break;

						case Ext.EventObject.F9:
							this.TTGScheduleEditPanel.printSchedule();
						break;
					}
				},
				scope: this,
				stopEvent: false
			}]
	    });
	    sw.Promed.swTTGScheduleEditWindow.superclass.initComponent.apply(this, arguments);
    },
	
    show: function () {
    	sw.Promed.swTTGScheduleEditWindow.superclass.show.apply(this, arguments);

		if (arguments[0] && arguments[0]['readOnly']) {
			this.TTGScheduleEditPanel.setReadOnly(arguments[0]['readOnly']);
		}

		// Если в качестве параметра был передан MedStaffFact_id, то берём ФИО врача и отделение из переданных параметров
		if (arguments[0] && arguments[0]['MedStaffFact_id']) {
			this.TTGScheduleEditPanel.MedStaffFact_id = arguments[0]['MedStaffFact_id'];
			this.setTitle(WND_TTGSEW + ' (' + arguments[0]['LpuSection_Name'] + ' / ' + arguments[0]['MedPersonal_FIO'] + ')');
		} else { // иначе, мы открываем форму из рабочего места врача и ФИО врача и отделение берём из глобальных параметров
			this.TTGScheduleEditPanel.MedStaffFact_id = null;
			this.setTitle(WND_TTGSEW + ' (' + getGlobalOptions().CurLpuSection_Name + ' / ' + getGlobalOptions().CurMedPersonal_FIO + ')');
		}
		
    	//Сразу загружаем расписание на текущий день
		this.TTGScheduleEditPanel.doResetAnnotationDate(this.TTGScheduleEditPanel.calendar.value);
    	this.TTGScheduleEditPanel.loadSchedule(this.TTGScheduleEditPanel.calendar.value);
    }
});
