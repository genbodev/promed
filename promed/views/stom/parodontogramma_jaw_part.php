<table style="border-collapse: collapse; font-family: tahoma,arial,helvetica,sans-serif;"><tbody>
<tr style="height: 18px;">
    {ToothCodeList}
	<td style="border: 0; text-align: center; font-size: 10pt; width: 30px;" class="tooth-info">{Tooth_Code}</td>
    {/ToothCodeList}
    <td></td>
</tr>
<tr style="height: 21px;">
    {ToothStateTypeNickList}
	<td style="text-align: center; font-size: 10pt; padding: 0; margin: 0;" itemId="{Tooth_Code}" class="parodont tooth-info state-type-{ToothStateType_Code}">
        {ToothStateType_Nick}
	</td>
    {/ToothStateTypeNickList}
    <td style="border: 0; text-align: left; font-size: 10pt; width: 48px;" class="tooth-info"> = {SumStateValues}</td>
</tr>
<tr style="height: 18px;">
    {ToothStateValueList}
	<td style="border: 0; text-align: center; font-size: 10pt;" class="tooth-info tooth-state-value">{ToothState_Value}</td>
    {/ToothStateValueList}
    <td></td>
</tr>
</tbody></table>
