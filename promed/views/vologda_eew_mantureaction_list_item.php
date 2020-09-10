<tr id="MantuReaction_{JournalMantu_id}"
  onmouseover=
    "if (isMouseLeaveOrEnter(event, this))
      document.getElementById('MantuReaction_{JournalMantu_id}_toolbar').style.display = 'block'"
  onmouseout=
    "if (isMouseLeaveOrEnter(event, this))
      document.getElementById('MantuReaction_{JournalMantu_id}_toolbar').style.display = 'none'">
  <td>{Status_Name}</td>
  <td>{dateVac}</td>
  <td>{TubDiagnosisType_Name}</td>
  <td>{MantuReactionType_name}</td>
  <td>{ReactDescription}</td>
  <td>{ReactionSize}</td>
  <td>{Lpu_Name}</td>
  <td class="toolbar">
    <div id="MantuReaction_{JournalMantu_id}_toolbar" class="toolbar">
      <a id="MantuReaction_{JournalMantu_id}_viewMantu" class="button icon icon-view16" title="Просмотреть"><span></span></a>
    </div>
  </td>
</tr>
