<table class="table faild_table">
  <thead>
    <tr>
      <th scope="col">Suite</th>
      <th scope="col">Class</th>
      <th class="text-right" scope="col">Successful</th>
      <th class="text-right" scope="col">Failed</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($total_cases_failed as $total_case_failed):
      $names_failed = explode("\\", $total_case_failed['case']); ?>
      <tr id = "<?=$total_case_failed['case']?>">
        <td><?=$names_failed[1]?></td>
        <td><?=$names_failed[2]?></td>
        <td class="text-right"><?=$total_case_failed['case_successful']?></td>
        <td class="text-right"><?=$total_case_failed['case_failed']?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>