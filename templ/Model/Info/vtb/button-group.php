
<div class="bm-form-line bm-form-line--last">
    <?php if ($info->hasData): ?>
        <button  type="submit" class="bm-form-btn bm-form-btn--blue">Изменить</button>
    <?php else: ?>
        <button  type="submit" class="bm-form-btn bm-form-btn--red">Ответить</button>
        <?php if ($info->hasDefault): ?>
            <button  type="submit" class="bm-form-btn bm-form-btn--white">Пропустить</button>
        <?php endif; ?>
    <?php endif; ?>
</div>

<input type="hidden" name="info_id" value="<?=$info->extId?>">