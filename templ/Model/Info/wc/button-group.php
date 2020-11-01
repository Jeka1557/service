
<?php if ($info->hasData): ?>
    <button  type="submit" class="btn btn-outline-primary">Изменить</button>
<?php else: ?>
    <button  type="submit" class="btn btn-primary">Ввести</button>
    <?php if ($info->hasDefault): ?>
        <button  type="submit" class="btn btn-link">Пропустить</button>
    <?php endif; ?>
<?php endif; ?>