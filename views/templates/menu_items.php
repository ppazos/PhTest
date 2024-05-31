<li class="nav-item position-relative">
  <a id="<?=$item?>" class="nav-link collapsed" href="#" data-toggle="collapse"
     data-target="#collapseUtilities_<?=$item?>" aria-expanded="true" aria-controls="collapseUtilities">
    <?php $style_item = "fa-fw fa fa-check text-success"; ?>
    <?php $color = "green"; ?>
    <?php if ($is_failed || $fatal_error || $type_fail) : ?>
      <?php $style_item = "fa-fw fas fa-times text-warning"; ?>
    <?php endif; ?>
    <i class="<?=$style_item?>"></i>
    <span class="mr-1 d-inline-block text-truncate align-text-top" title="<?=$item?>"><?=$item?></span>
    <?php $style_badge = "top-0 badge badge-success" ?>
    <?php if ($badge['case_successfull'] < $badge['total_cases'] || $fatal_error || $type_fail) : ?>
      <?php $style_badge = "top-0 badge badge-danger"; ?>
    <?php endif; ?>
    <span class="<?=$style_badge?>"><?=$badge['case_successfull']?> / <?=$badge['total_cases']?></span>
  </a>
  <div id="collapseUtilities_<?=$item?>" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
    <div id="collapse_<?=$item?>" class="bg-white py-2 collapse-inner rounded">
    <?php foreach ($namesSuitessubmenu as $submenu): ?>
      <?php $suites = explode("\\", $submenu); ?>
      <?php if (in_array($item, $suites)): ?>
        <?php if ($fatal_error === $suites[2] || $type_fail === $suites[2]) : ?>
          <?php $color = "red"; ?>
        <?php else: ?>
          <?php $color = "green"; ?>
        <?php endif; ?>
        <a id="<?=$suites[2]?>" class="text-truncate collapse-item" style="max-width: 175px; color:<?=$color?>" href="#" title="<?=$suites[2]?>"><?=$suites[2]?></a>
      <?php endif; ?>
    <?php endforeach; ?>
    </div>
  </div>
</li>