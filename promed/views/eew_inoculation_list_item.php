<tr id="Inoculation_{Inoculation_id}">
  <td>{DateVac}</td>
  <td>{age}</td>
  <td>{Vaccine_Name}</td>

  <?php if (getRegionNick() == 'ufa') { ?>
      <td>{typeName}</td>
  <?php } ?>

  <td>{VaccineType_Name}</td>
  <td>{Seria}</td>
  <td>{Dose}</td>
  <td>{WayPlace}</td>
  <td>{ReactGeneralDescription}</td>

  <?php if (getRegionNick() == 'vologda') { ?>
    <td>{Lpu_Name}</td>
  <?php } ?>
</tr>
