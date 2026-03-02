# VikBooking Uploads Symlink Logic (`unhotel-symlink-guardian.php`)

The `unhotel-symlink-guardian.php` Must-Use (MU) plugin runs during the WordPress `admin_init` hook and ensures that VikBooking uploads are safely stored centrally rather than inside the plugin directory itself.

## How It Works

1. **Path Definitions:**
   It defines a source directory (inside the VikBooking plugin tree) and a destination directory (inside the standard WordPress `uploads` folder).
   * **Source:** `wp-content/plugins/vikbooking/site/resources/uploads`
   * **Destination:** `wp-content/uploads/vikbooking`

2. **Destination Check:**
   It first checks if the destination directory (`wp-content/uploads/vikbooking`) actually exists. If it doesn't, the script simply aborts.

3. **Status Check:**
   It checks if the source path is already a valid symlink pointing to the correct destination directory. If it is, the script successfully finishes.

4. **Plugin Update Handling:**
   If a plugin update occurs, WordPress often removes the symlink and replaces it with a normal empty folder during the plugin extraction process.
   If the script detects that the source path is a real directory and *not* a symlink, it moves the directory aside by renaming it to something like `wp-content/plugins/vikbooking/site/resources/uploads.replaced_[timestamp]`.

5. **Symlink Recreation:**
   Finally, if the source path does not exist (or was just moved out of the way), the script attempts to create a fresh symlink linking the plugin's upload folder directly to the safe central `wp-content/uploads/vikbooking` directory.

## Summary

This script protects uploaded media from being lost during VikBooking plugin updates by maintaining a persistent symlink to the central WordPress `uploads` folder.
