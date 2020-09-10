Ext.define('common.HeadDoctorWP.swWialonTrackPlayerPanel', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.swWialonTrackPlayerPanel',
    flex: 1,
    refId: 'callTrackTab',
    layout: 'fit',
    overflowY: 'auto',
    items: [{
        xtype: 'panel',
        itemId: 'wialonTrackMapPanel',
        refId: 'wialonTrackMapPanel',
        initMarkup: function () {
            $('#wialonTrackMapPanel_wraper').html('<div id="layout">'+
            '<div id="map"></div>'+
            '<div id="control">'+
            '<div id="info_block">'+
            '<div id="photos"></div>'+
            '<div id="graph"></div>'+
            '</div>'+
            '<div id="track_data_block">'+
            '<div id="unit_track_main" class="unit_track">'+
            '<img class="unit_icon" src="https://wialonweb.promedweb.ru/avl_item_image/180/24/null.png">'+
            '<div class="info" id="info_main">'+
            '</div>'+
            '<div class="unit_buttons">'+
            '<a href="#" class="unit_follow_btn" id="toggle_follow_main">'+
            '<img src="img/wialonTrackPlayer/watch-on-map-dis.png" alt="Follow">'+
            '</a>'+
            '</div>'+
            '<div id="commons_main_speed" class="unit_speed" style="color:#679a01">'+
            '<span id="unit_speed_main">0</span>'+
            '<span class="km"> км/ч</span>'+
            '</div>'+
            '</div>'+
            '</div>'+
            '<div id="playline_block">'+
            '<div class="b1">'+
            '<div id="step_val" title="">10x</div>'+
            '<button disabled id="play_btn" type="button"></button>'+
            '<div id="t_curr"><span class="d"></span><span class="t"></span></div>'+
            '<button disabled id="tostart_btn" type="button"></button>'+
            '<button disabled id="stepleft_btn" type="button"></button>'+
            '</div>'+
            '<div class="timeline_wrapper">'+
            '<div id="range"></div>'+
            '<div id="slider"></div>'+
            '</div>'+
            '<div class="b2">'+
            '<button disabled id="stepright_btn" type="button"></button>'+
            '<button disabled id="toend_btn" type="button"></button>'+
            '<button disabled id="settings_btn" type="button"></button>'+
            '</div>'+
            '</div>'+
            '</div>'+
            '</div>'+
            '<div id="step_wrapper" tabindex="-1" class="dialog">'+
            '<div id="step" tabindex="-1"></div>'+
            '</div>'+
            '<div id="settings_dialog" class="dialog">'+
            '<div class="row">'+
            '<input id="skip_trips_ch" type="checkbox"/><label id="tr_skip_trips" for="skip_trips_ch"></label>'+
            '</div>'+
            '</div>')
        },
        html:
        '<div id="wialonTrackMapPanel_wraper">'+

            '</div>'
    }],
    dockedItems: [
        {
            xtype: 'toolbar',
            dock: 'bottom',
            items: [
                /*{
                 xtype: 'button',
                 refId: 'saveBtn',
                 iconCls: 'save16',
                 text: 'Сохранить'
                 },*/
                { xtype: 'tbfill' },
                {
                    xtype: 'button',
                    refId: 'closeBtn',
                    iconCls: 'cancel16',
                    text: 'Закрыть'
                },
            ]
        }
    ]
});