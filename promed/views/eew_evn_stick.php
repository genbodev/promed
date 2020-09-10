<div id="EvnStickList_{pid}" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnStickList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnStickList_{pid}_toolbar').style.display='none'">
    <div class="evn_stick clear">

        <div class="data-table">
            <div class="caption">
                <h2>Нетрудоспособность</h2>
                <div id="EvnStickList_{pid}_toolbar" class="toolbar" style="display: none;">
                    <a id="EvnStickList_{pid}_add" class="button icon icon-add16" title="Добавить ЛВН"><span></span></a>
                </div>
            </div>

            <table>

                <col class="first last" />
                <col class="toolbar"/>

                {items}

            </table>

        </div>

    </div>
</div>
