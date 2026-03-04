# Unhotel AI Workflow & Prompt Library
### 1. The Backend Architect (@backend-architect)
**Use for:** PHP logic, database queries, and custom post types.
**Sample Prompt:** '@backend-architect, read your context rules. I need to create a new custom endpoint for the Reception Dashboard. Write the PHP logic in wp-content/plugins/unhotel-dashboard/, ensuring you use WP-CLI to test any DB queries first.'

### 2. The Frontend Developer (@frontend-developer)
**Use for:** UI/UX, Vue/React/JS, and CSS styling.
**Sample Prompt:** '@frontend-developer, read your context rules and the Unhotel Design.md file. Build the Vue component for the check-in modal. Use the exact hex codes and 8px spacing grid from the design file. Open a headless browser to test the render when finished.'

### 3. The Reversion Protocol
If an agent breaks the site, open unhotel-event-registry.md, find the Git Hash of the bad update, and run: git reset --hard [Hash]
