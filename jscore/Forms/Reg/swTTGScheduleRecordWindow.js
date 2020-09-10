/**
* swTTGScheduleRecordWindow - окно для работы с записью в поликлинику
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

sw.Promed.swTTGScheduleRecordWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	id: 'TTGScheduleRecordWindow',
	title: '',
	
	/**
	 * Панель с расписанием для записи
	 */
	TTGRecordPanel: null,
	
    initComponent: function() {
		
		// Панель расписания для записи
		this.TTGRecordPanel = new sw.Promed.swTTGRecordPanel({
			id:'TTGRecordPanel',
			frame: false,
			border: false,
			region: 'center'
		});
		
	    Ext.apply(this, {
	    	border: false,
	    	layout: 'border',
			items: [
				this.TTGRecordPanel
			],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
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
						case Ext.EventObject.F5:
							this.TTGRecordPanel.loadSchedule();
						break;

						case Ext.EventObject.F9:
							this.TTGRecordPanel.printSchedule();
						break;
					}
				},
				scope: this,
				stopEvent: false
			}]
	    });
	    sw.Promed.swTTGScheduleRecordWindow.superclass.initComponent.apply(this, arguments);
    },
	
    show: function () {
    	sw.Promed.swTTGScheduleRecordWindow.superclass.show.apply(this, arguments);
		
		// Если в качестве параметра был передан MedStaffFact_id, то берём ФИО врача и отделение из переданных параметров
		if (arguments[0] && arguments[0]['MedStaffFact_id']) {
			this.TTGRecordPanel.MedStaffFact_id = arguments[0]['MedStaffFact_id'];
			this.setTitle(WND_TTGRW + ' (' + arguments[0]['LpuSection_Name'] + ' / ' + arguments[0]['MedPersonal_FIO'] + ')');
		} else { // иначе, мы открываем форму из рабочего места врача и ФИО врача и отделение берём из глобальных параметров
			this.TTGRecordPanel.MedStaffFact_id = null;
			this.setTitle(WND_TTGRW + ' (' + getGlobalOptions().CurLpuSection_Name + ' / ' + getGlobalOptions().CurMedPersonal_FIO + ')');
		}
		
    	//Сразу загружаем расписание на текущий день
    	this.TTGRecordPanel.loadSchedule(this.TTGRecordPanel.calendar.value);
    }
});
