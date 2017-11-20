<?php // S T Y L E ?>
<style media="screen">

  .kirby-tracking__list-item {
    padding: .25em 0 .25em 0;
  }
  .kirby-tracking__list-item ul {
    padding-left: 1em;
    list-style: none;
  }
  .kirby-tracking__list-item ul li:first-child {
    padding-top: .5em;
  }
  .kirby-tracking__list-item ul li + li {
    padding-top: .25em;
  }

  .kirby-tracking__dashboard-box {
      margin-top: 1em;
  }

</style>

<?php // C O N T R O L E R ?>
<?php
  $site = panel()->site();
  $kirbytracking_page = $site->page('kirby-tracking');
  $all_visitors = $kirbytracking_page->index()->filterBy('intendedTemplate','kirbytracking_visitor');

  $total_number_of_visitor = $all_visitors->count();

  $monthly_number_of_visitor = $all_visitors->filter(function($p){
                                                return $p->date('Ym') == date("Ym");
                                              })
                                            ->count();

  $daily_number_of_visitor = $all_visitors->filter(function($p){
                                              return $p->date('Ymd') == date("Ymd");
                                            })
                                          ->count();
?>

<?php // T E M P L A T E ?>
<ul class="nav nav-list sidebar-list">

  <li class="kirby-tracking__list-item">
    <span>Total number of visitors</span>
    <small class="marginalia shiv shiv-left shiv-white"><?php echo $total_number_of_visitor ?></small>
  </li>

  <li class="kirby-tracking__list-item">
    <span>Monthly number of visitors</span>
    <small class="marginalia shiv shiv-left shiv-white"><?php echo $monthly_number_of_visitor ?></small>
  </li>

  <li class="kirby-tracking__list-item">
    <span>Daily number of visitors</span>
    <small class="marginalia shiv shiv-left shiv-white"><?php echo $daily_number_of_visitor ?></small>
  </li>

</ul>

<div class="dashboard-box kirby-tracking__dashboard-box">
  <a class="dashboard-item" href="<?php echo purl($kirbytracking_page) ?>">
    <figure>
      <span class="dashboard-item-icon dashboard-item-icon-with-border"><i class="fa fa-chain"></i></span>
      <figcaption class="dashboard-item-text"><?php echo $kirbytracking_page->title() ?></figcaption>
    </figure>
  </a>
</div>
