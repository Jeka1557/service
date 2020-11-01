<?php if ($info->hasValue): ?>
    <script>
        try {
            sessionStorage.setItem('info_' +<?=$info->extId?>, '<?=str_replace(["\\", "'"], ["\\\\", "\'"], $info->dataText)?>');
        } catch (e) {
            // sessionStorage not awailable;
        }
    </script>
<?php elseif ($info->hasData): ?>
    <script>
        try {
            sessionStorage.removeItem('info_'+<?=$info->extId?>);
        } catch (e) {
            // sessionStorage not awailable;
        }
    </script>
<?php else: ?>
    <script>
        (function () {
            try {
                var value = sessionStorage.getItem('info_' +<?=$info->extId?>);

                if (value!==null)
                    document.getElementById('bm-form-input-<?=$info->extId?>').value = value;
            } catch (e) {
                // sessionStorage not awailable;
            }
        })();
    </script>
<?php endif; ?>