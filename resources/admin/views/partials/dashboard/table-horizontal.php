<?php use function KreeLabs\WSR\translate; ?>

<div class="card comfy-card animate fadeRight">
    <div class="card-content pb-1">
        <h4 class="card-title mb-0"><?= translate($options['title']) ?></h4>
        <p class="small"><?= translate($options['subtitle']) ?></p>
    </div>
    <table class="responsive-table highlight">
        <thead>
        <tr>
            <?php foreach ($options['headers'] as $header): ?>
                <th><?= translate($header) ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($options['values'] as $values): ?>
            <tr>
                <?php foreach ($values as $value): ?>
                    <td><?= $value ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
