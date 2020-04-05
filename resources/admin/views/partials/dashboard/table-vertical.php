<?php use function KreeLabs\WSR\translate; ?>

<div class="card recent-buyers-card animate fadeLeft">
    <div class="card-content">
        <h4 class="card-title mb-0"><?= translate($options['title']) ?></h4>
        <p class="small"><?= translate($options['subtitle']) ?></p>
        <ul class="collection mb-0">
            <?php foreach ($options['values'] as $value): ?>
                <li class="collection-item <?= ! empty($value['image']) ? 'avatar' : '' ?>">
                    <?php if ( ! empty($value['image'])): ?>
                        <img src="<?= $value['image'] ?>" alt="" class="circle">
                    <?php endif; ?>
                    <p class="font-weight-600"><?= $value['main_text'] ?></p>
                    <?php foreach ($value['other_info'] as $info): ?>
                        <p class="small"><?= $info ?></p>
                    <?php endforeach; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
