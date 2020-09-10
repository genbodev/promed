Ext4.define('Ext4.ux.DateTimeMenu', {
	  extend: 'Ext4.menu.Menu',
	  
	  alias: 'widget.datetimemenu',
	  
	  requires: ['Ext4.ux.DateTimePicker'],
	  
	  hideOnClick: true,
	  pickerId: null,
	  
	  initComponent: function() {
		  var me = this;
		  
		  Ext4.apply(me, {
			    showSeparator: false,
			    plain: true,
			    border: false,
			    bodyPadding: 0,
			    items: Ext4.applyIf({
				      cls: Ext4.baseCSSPrefix + 'menu-date-item',
				      id: me.pickerId,
				      xtype: 'datetimepicker'
			      }, me.initialConfig)
		    });
		  
		  me.callParent(arguments);
		  
		  me.picker = me.down('datetimepicker');
		  me.relayEvents(me.picker, ['select']);
		  
		  if (me.hideOnClick) {
			  me.on('select', me.hidePickerOnSelect, me);
		  }
	  },
	  
	  hidePickerOnSelect: function() {
		  Ext4.menu.Manager.hideAll();
	  }
  });
