
Ext.define('globalApp.controller.swBSMEDefaultWorkPlace_controller', {
    extend: 'Ext.app.Controller', 
		
	models: [
//        'common.swBSMEDefaultWorkPlace.model.***'
    ],
	
    stores: [
//        'common.swBSMEDefaultWorkPlace.store.***'
    ],
	init: function() {
		
		var cntr = this;
		
		this.win_id = 'BSMEDefaultWorkPlace';
		
        this.control({
			'#BSMEDefaultWorkPlace': {
				render:function(cmp) {
					
				},
			}
		})
	}
})