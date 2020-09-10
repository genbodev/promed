Ext.define('SMP.HeadDoctorWorkPlace.swWialonTrackPlayerTab_controller', {
    extend: 'Ext.app.Controller',

    refs: [
        {
            ref: 'callDetailParentHD',
            selector: 'swHeadDoctorWorkPlace tabpanel[refId=callDetailParentHD]'
        },
        {
            ref: 'callTrackTab',
            selector: 'swHeadDoctorWorkPlace panel[refId=callTrackTab]'
        },
        {
            ref: 'wialonTrackMapPanel',
            selector: 'swHeadDoctorWorkPlace [refId=wialonTrackMapPanel]'
        }
    ],

    init: function () {

        var cntr = this;

        this.control({
            'swHeadDoctorWorkPlace panel[refId=wialonTrackMapPanel]': {
                render: function (panel) {
                    panel.initMarkup();
                    this.getCallDetailParentHD().setActiveTab(0);
                },
                show: function () {
                    panel.initMarkup();
                }
            },
            'swHeadDoctorWorkPlace panel[refId=callTrackTab] button[refId=closeBtn]': {
                click: function () {
                    cntr.getWialonTrackMapPanel().initMarkup();
					sw.Promed.WialonTrackPlayer.logout();
                    cntr.getCallDetailParentHD().setActiveTab(0);
                }
            }
        });
    },
    /**
     *
     * @param params
     * @params {int} params.startTime
     * @params {int} params.endTime
     * @params {int} params.wialonId
     */
    initTrackPlayer: function (params) {
        // sw.Promed.WialonTrackPlayer.initTrackPlayer(180, 1482944320, 1482947720);
        sw.Promed.WialonTrackPlayer.initTrackPlayer(params.wialonId, params.startTime, params.endTime);
    },
	logout: function(){
		sw.Promed.WialonTrackPlayer.logout();
	}
});
