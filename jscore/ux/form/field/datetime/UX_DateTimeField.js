Ext4.define('Ext4.ux.form.DateTimeField', {
	  extend: 'Ext4.form.field.Date',
	  alias: 'widget.datetimefield',
	  requires: ['Ext4.ux.DateTimePicker'],
	  
	  timeFormat: 'H:i:s',

	  initComponent: function(){
		  this.format = this.format + ' ' + this.timeFormat;
		  console.log(this.timeFormat);
		  this.callParent();
	  },
	  
	  // overwrite
	  createPicker: function(){
		  var me = this,
			  format = Ext4.String.format;

		  return Ext4.create('Ext4.ux.DateTimePicker', {
			    ownerCt: me.ownerCt,
			    renderTo: document.body,
			    floating: true,
			    hidden: true,
			    focusOnShow: true,
			    minDate: me.minValue,
			    maxDate: me.maxValue,
			    disabledDatesRE: me.disabledDatesRE,
			    disabledDatesText: me.disabledDatesText,
			    disabledDays: me.disabledDays,
			    disabledDaysText: me.disabledDaysText,
			    format: me.format,
			    showToday: me.showToday,
			    startDay: me.startDay,
			    minText: format(me.minText, me.formatDate(me.minValue)),
			    maxText: format(me.maxText, me.formatDate(me.maxValue)),
				timeFormat: this.timeFormat,
			    listeners: {
				    scope: me,
				    select: me.onSelect
			    },
			    keyNavConfig: {
				    esc: function() {
					    me.collapse();
				    }
			    }
		    });
	  }
  });