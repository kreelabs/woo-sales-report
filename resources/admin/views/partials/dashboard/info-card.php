<div class="card animate fadeRight">
    <div class="card-content">
        <?php if ( ! empty($options['header'])): ?>
            <h4 class="header mt-0 mb-0"><?= \KreeLabs\WSR\translate('Chart Analysis') ?></h4>
        <?php endif; ?>
        <p class="caption">
            <?php if ( ! empty($options['icon'])): ?>
                <i class="material-icons info-i"><?= $options['icon'] ?></i>
            <?php endif; ?>
            <?= \KreeLabs\WSR\translate($options['message']) ?>
        </p>
    </div>
</div>
