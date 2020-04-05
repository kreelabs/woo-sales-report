<?php

use function KreeLabs\WSR\translate;
use function \KreeLabs\WSR\format_number;

?>

<div class="card gradient-shadow gradient-45deg-<?= $options['color'] ?> border-radius-3 animate fadeLeft">
    <div class="card-content center">
        <i class="material-icons background-round mt-5"><?= $options['icon'] ?></i>
        <h5 class="white-text lighten-4"><?= $options['total'] ?></h5>
        <p class="white-text lighten-4"><?= translate($options['title']) ?></p>
        <p class="white-text small">
            (<?= format_number($options['count']) . ' ' . translate($options['count_title']) ?>)
        </p>
    </div>
</div>
