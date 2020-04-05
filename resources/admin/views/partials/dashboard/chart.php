<?php use function KreeLabs\WSR\translate;

$randId  = uniqid();
$chartId = 'wsr-' . $options['meta']['id'] . "-chart-${randId}";

$defaultChartOptions = [
    'responsive' => true,
    'legend' => [
        'display' => true,
        'position' => 'bottom',
    ],
];

if ( ! empty($options['chart']['options'])) {
    $chartOptions = $options['chart']['options'] + $defaultChartOptions;
} else {
    $chartOptions = $defaultChartOptions;
}
?>

<div id="wsr-<?= $options['meta']['id'] ?>-<?= $randId ?>"
     class="card <?= ! empty($options['meta']['animate']) ? $options['meta']['animate'] : '' ?>">
    <div class="card-content">
        <h4 class="header mt-0 mb-0">
            <?= translate($options['title']) ?>

            <?php if ( ! empty($options['fancy_meter'])): ?>
                <span class="<?= $options['fancy_meter']['color'] ?> small text-darken-1 ml-1">
                        <i class="material-icons">&nbsp;<?= $options['fancy_meter']['icon'] ?></i>
                    <?= $options['fancy_meter']['value'] ?>
                    </span>
            <?php endif; ?>
        </h4>
        <p class="small"><?= translate($options['subtitle']) ?></p>
        <div class="row">
            <div class="col s12">
                <br/>
                <canvas id="<?= $chartId ?>"></canvas>
            </div>
        </div>
        <?php if (isset($options['total']) && is_numeric($options['total'])): ?>
            <h5 class="center-align"><?= $options['total'] ?></h5>
        <?php endif; ?>

        <?php if ( ! empty($options['description'])): ?>
            <p class="medium-small center-align"><?= translate($options['description']) ?></p>
        <?php endif; ?>
    </div>
</div>
<script>
  new Chart(document.getElementById('<?= $chartId ?>').getContext('2d'), {
    type: '<?= $options['chart']['type'] ?>',
    data: {
      labels: <?= json_encode($options['chart']['labels']) ?>,
      datasets: <?= json_encode($options['chart']['data']) ?>
    },
    options: <?= json_encode($chartOptions) ?>
  });
</script>
