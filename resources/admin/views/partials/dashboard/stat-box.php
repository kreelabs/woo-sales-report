<div class="card border-radius-6 animate fadeLeft">
    <div class="card-content center-align">
        <i class="material-icons amber-text small-ico-bg mb-5"><?= $options['icon'] ?></i>
        <h4 class="m-0"><b><?= $options['count_today'] ?></b></h4>
        <p class="small"><?= translate($options['title_today']) ?></p>
        <p class="small" style="margin-left: 5px;">
            (<?= $options['count_yesterday'] . ' ' . $options['title_yesterday'] ?>)
        </p>
        <?php if ( ! empty($options['comparision'])): ?>
            <p class="<?= $options['comparision']['color'] ?> mt-3">
                <i class="material-icons vertical-align-middle"><?= $options['comparision']['icon'] ?></i>
                <?= $options['comparision']['value'] ?>
            </p>
        <?php endif ?>
    </div>
</div>
