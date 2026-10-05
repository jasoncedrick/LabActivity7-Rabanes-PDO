<?php if (!empty($errors)): ?>
    <div class="errors" role="alert">
        <p>Please check the following:</p>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= escape($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
