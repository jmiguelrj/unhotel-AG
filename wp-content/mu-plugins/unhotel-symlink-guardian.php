<?php
/**
 * Plugin Name: Unhotel Symlink Guardian (MU)
 * Description: Keeps VikBooking media in wp-content/uploads/vikbooking and ensures the plugin path stays a symlink to it.
 * Version: 1.0.0
 */

add_action('admin_init', static function () {
    // wp-content/mu-plugins -> wp-content -> site root
    $root = dirname(__FILE__, 3);

    $srcRel = 'wp-content/plugins/vikbooking/site/resources/uploads';
    $dstRel = 'wp-content/uploads/vikbooking';

    $src = $root . '/' . $srcRel;
    $dst = $root . '/' . $dstRel;

    // Destination must exist; if not, do nothing.
    if (!is_dir($dst)) {
        error_log('[SymlinkGuardian] Destination missing: ' . $dstRel);
        return;
    }

    // If $src is a symlink resolving to $dst, all good.
    if (is_link($src) && realpath($src) === realpath($dst)) {
        return;
    }

    // If an update recreated $src as a real directory, move it aside first.
    if (is_dir($src) && !is_link($src)) {
        @rename($src, $src . '.replaced_' . time());
    }

    // If $src doesn't exist (or was moved), create the symlink.
    if (!file_exists($src)) {
        if (@symlink($dst, $src)) {
            error_log('[SymlinkGuardian] Ensured symlink ' . $srcRel . ' -> ' . $dstRel);
        } else {
            error_log('[SymlinkGuardian] Could not create symlink for ' . $srcRel . ' -> ' . $dstRel . '. Manual action required.');
        }
    }
});
