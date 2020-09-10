/**
* swTTSScheduleEditWindow - окно редактирования расписания услуги стационара
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      16.03.2012
*/

sw.Promed.swTTSScheduleEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	id: 'TTSScheduleEditWindow',
	title: WND_TTSSEW,
	
    initComponent: function() {
		
		// Панель редактирования расписания
		this.TTSScheduleEditPanel = new sw.Promed.swTTSScheduleEditPanel({
			id:'TTSScheduleEdit',
			frame: false,
			border: false,
			region: 'center'
		});
	    
	    Ext.apply(this, {
	    	border: false,
	    	layout: 'border',
			items: [
				this.TTSScheduleEditPanel
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
							this.TTSScheduleEditPanel.openFillWindow();
						break;
						
						case Ext.EventObject.F5:
							this.TTSScheduleEditPanel.loadSchedule();
						break;

						case Ext.EventObject.F9:
							this.TTSScheduleEditPanel.printSchedule();
						break;
					}
				},
				scope: this,
				stopEvent: false
			}]
	    });
	    sw.Promed.swTTSScheduleEditWindow.superclass.initComponent.apply(this, arguments);
    },
	
    show: function () {
    	sw.Promed.swTTSScheduleEditWindow.superclass.show.apply(this, arguments);
		
		// Если в качестве параметра был передан LpuSection_id, то берём ее
		if (arguments[0] && arguments[0]['LpuSection_id']) {
			this.TTSScheduleEditPanel.LpuSection_id = arguments[0]['LpuSection_id'];
			this.setTitle(WND_TTSSEW + ' (' + arguments[0]['LpuSection_Name'] + ')');
		} else { // иначе, мы открываем форму из рабочего места врача параклиники то отделение берём из глобальных параметров
			this.TTSScheduleEditPanel.LpuSection_id = getGlobalOptions().CurLpuSection_id;
			this.setTitle(WND_TTSSEW + ' (' + getGlobalOptions().CurLpuSection_Name + ')');
		}
		
		this.TTSScheduleEditPanel.loadSchedule(this.TTSScheduleEditPanel.calendar.value);
		
    }
});
