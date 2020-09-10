<div>
    <div style="clear:both;" class="NewStyleDoc" id="EvnXmlDirectionLink_{EvnXmlDirectionLink_id}" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnXmlDirectionLink_{EvnXmlDirectionLink_id}_printdoc').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnXmlDirectionLink_{EvnXmlDirectionLink_id}_printdoc').style.display='none'">
        <div class="vPrint vPrint-none"><input pid="{EvnXml_pid}" type="checkbox" onclick="Ext.getCmp('PersonEmkForm').changeCountPrint()" class="checkPrint chkEvnXml" print="EvnXml" value="{EvnXml_id}"/></div>
        <div class="WrapDoc" id="EvnXmlDirectionLink_{EvnXmlDirectionLink_id}_wrap" style="padding: 0px 5px 4px 0px">
            <div id="EvnXmlDirectionLink_{EvnXmlDirectionLink_id}_content" style="height:19px;">

                <span id="EvnXmlDirectionLink_{EvnXmlDirectionLink_id}_showActions" class="button" title="Выбор действия"></span>
                <a id="EvnXmlDirectionLink_{EvnXmlDirectionLink_id}_printdoc" style="display:none;float:right" class="button icon icon-print16" title="Печать документ"><span></span></a>
                <span style="display: none;">{EvnXml_pid}</span>
                <span id="EvnXmlDirectionLink_{EvnXmlDirectionLink_id}_showDoc" class="link" title="Показать документ">{EvnXml_Name}</span>
                <b>Дата</b>: {EvnXml_Date} <b>Автор</b>: {pmUser_Name}
            </div>
            <div id="EvnXmlDirectionLink_Data_{EvnXmlDirectionLink_id}" style='clear:both; margin-top:5px; width: inherit; display: none;'>
                {EvnXml_Data}
                <div style="text-align: center; width: 95%">
                    <span id="EvnXmlDirectionLink_{EvnXmlDirectionLink_id}_hideDoc" class="link">Свернуть документ</span>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>