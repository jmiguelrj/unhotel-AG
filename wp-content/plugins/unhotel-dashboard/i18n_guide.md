# Unhotel Dashboard - Internal Internationalization (i18n) Guide

This guide explains how to manage translations for the Unhotel Dashboard React application.

## 1. Modifying Existing Translations

The text strings for the dashboard are stored in JavaScript files located in `src/locales/`.

1.  **Locate the file**:
    *   **Portuguese (Brazil)**: `src/locales/pt_BR.js`
    *   **English (US)**: `src/locales/en_US.js`

2.  **Edit the file**:
    Open the file in your code editor. It exports a simple object where the **Keys** are the English identifiers (or default text) and the **Values** are the translations.

    ```javascript
    export default {
        // "Key": "Translation"
        "FrontDesk": "Recepção",
        "Search guest...": "Buscar hóspede...",
        // ...
    };
    ```

3.  **Apply Changes**:
    After saving the file, you must rebuild the React application for changes to take effect:
    ```bash
    npm run build
    ```

---

## 2. Adding a New Language

To add a new language (e.g., Spanish - `es_ES`), follow these steps involving both the React frontend and the WordPress PHP backend.

### Step 1: Create the Locale File
1.  Create a new file: `src/locales/es_ES.js`.
2.  Copy the content from `src/locales/en_US.js` (or `pt_BR.js`) to use as a template.
3.  Translate the values.

    ```javascript
    // src/locales/es_ES.js
    export default {
        "FrontDesk": "Recepción",
        "Reception Dashboard": "Panel de Recepción",
        // ...
    };
    ```

### Step 2: Register the Language in React
1.  Open `src/index.js`.
2.  Import your new locale file at the top:
    ```javascript
    import en_US from './locales/en_US';
    import pt_BR from './locales/pt_BR';
    import es_ES from './locales/es_ES'; // <--- Add this
    ```

3.  Update the `DICTIONARIES` constant:
    ```javascript
    const DICTIONARIES = { en_US, pt_BR, es_ES }; // <--- Add this
    ```

### Step 3: Add Option to WordPress Settings
To allow users to select this language in the WP Admin, you need to update the settings form.

1.  Open `unhotel-dashboard.php`.
2.  Search for the function `unhotel_dashboard_render_config`.
3.  Find the `<select name="unhotel_system_language">` block (approx. line 356) and add the new option:

    ```php
    <select name="unhotel_system_language">
        <option value="en_US" <?php selected(get_option('unhotel_system_language', 'en_US'), 'en_US'); ?>>English (Default)</option>
        <option value="pt_BR" <?php selected(get_option('unhotel_system_language'), 'pt_BR'); ?>>Português (Brasil)</option>
        <!-- Add your new language here -->
        <option value="es_ES" <?php selected(get_option('unhotel_system_language'), 'es_ES'); ?>>Español</option>
    </select>
    ```

### Step 4: Rebuild and Configure
1.  Run the build command:
    ```bash
    npm run build
    ```
2.  Go to **WordPress Admin > Reception > Configuration**.
3.  Change **System Language** to "Español" and click **Save Settings**.
4.  Reload the dashboard to see the new language.
