<?php use function KreeLabs\WSR\translate; ?>

<a href="<?= $options['link'] ?>" class="card gradient-45deg-<?= $options['color'] ?> min-height-100 white-text block
        <?= isset($options['box_classes']) ? $options['box_classes'] : '' ?>">
    <div class="padding-4">
        <div class="col s7 m7">
            <i class="material-icons background-round mt-5"><?= $options['icon'] ?></i>
            <p><?= translate($options['icon_title']) ?></p>
        </div>
        <div class="col s5 m5 right-align">
            <h5 class="mb-0 white-text no-margin"><?= $options['count'] ?></h5>
            <p class="no-margin"><?= translate($options['count_title']) ?></p>
            <p>
                <?= $options['total'] ?> <br/>
                <small><?= translate('Total') ?></small>
            </p>
        </div>
    </div>
</a>
