<div class="wrap">
    <?php screen_icon(); ?>
    <h2><?php echo $plugin['Name']; ?></h2>

    <p>
        <?php echo $plugin['Description']; ?>
    </p>

    <?php if (WP_DEBUG): ?>

        <div class="updated">
            <p><?php echo $plugin['Name'] ?> is deactivated while <code><a href="http://codex.wordpress.org/WP_DEBUG" target="_blank">WP_DEBUG</a></code> is on.</p>
        </div>

    <?php endif ?>

    <?php if (isset($_POST['regenerate'])): ?>

        <?php Cached_L10n::export(); ?>
        <div class="updated">
            <p>L10n cache regenerated.</p>
        </div>

    <?php elseif (isset($_POST['delete'])): ?>

        <?php Cached_L10n::delete(); ?>
        <div class="updated">
            <p>L10n cache deleted.</p>
        </div>

    <?php endif ?>
    
    <?php
        $real = Cached_L10n::index();
        $cached = Cached_L10n::cached_index();
        $domains = array_unique(array_merge(array_keys($real), array_keys($cached)));
        $needs_regen = false;
    ?>
    <table>
        <caption>Index</caption>
        <thead>
            <tr>
                <th>Domain</th>
                <th>Checksum</th>
                <th>Cached</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($domains as $domain): ?>
                <tr>
                    <th><?php echo $domain ?></th>
                    <td><?php echo $r = empty($real[$domain]) ? '-' : $real[$domain]; ?></td>
                    <td><?php echo $c = empty($cached[$domain]) ? '-' : $cached[$domain]; ?></td>
                    <?php $needs_regen |= $r != $c; ?>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <?php update_option('Cached_L10n.updated', $needs_regen); ?>
    <?php if ($needs_regen): ?>
        <div class="error">
            <p>L10n cache is not up-to-date.</p>
        </div>
    <?php endif ?>

    <form method="post">
        <p>
            <input type="submit" class="button" name="regenerate" value="Regenerate">
            <input type="submit" class="button" name="delete" value="Delete">
        </p>
    </form>
</div>