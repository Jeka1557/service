<?php if ($info->hasData): ?>
    <button  type="submit" class="btn btn-sm btn-default">Изменить</button>
<?php else: ?>
    <button  type="submit" class="btn btn-sm btn-primary">Ввести</button>
    <?php if ($info->hasDefault): ?>
        <button  type="submit" class="btn btn-sm btn-default">Пропустить</button>
    <?php endif; ?>
<?php endif; ?>