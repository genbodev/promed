<tr id="PersonSvidInfo_{PersonSvid_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonSvidInfo_{PersonSvid_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonSvidInfo_{PersonSvid_id}_toolbar').style.display='none'">
    <td>{PersonSvidType_Name}</td>
    <td>{PersonSvid_Ser}</td>
    <td>{PersonSvid_Num}</td>
    <td>{PersonSvid_GiveDate}</td>
    <td class="toolbar">
        <div id="PersonSvidInfo_{PersonSvid_id}_toolbar" class="toolbar">
            <?php if(!$isMseDepers) { ?><a id="PersonSvidInfo_{PersonSvid_id}_view" class="button icon icon-view16" title="Просмотреть"><span></span></a><?php } ?>
        </div>
    </td>
</tr>