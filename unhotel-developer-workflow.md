# Unhotel Developer Workflow & IDE Cheat Sheet
To get the best results from Antigravity, use this 4-step framework when starting a new task:

### 1. The Kickoff Prompt
Always tell the AI which agent to act as, and point it to the relevant files. Example:
> '@frontend-developer, act according to your rules. Read Unhotel Design.md and build the Reception Dashboard layout in wp-content/plugins/unhotel-dashboard/...'

### 2. Autonomous Browser Testing
Do not test the UI manually at first. Command the AI to do it:
> 'Open a headless browser to http://localhost:8080, navigate to the new dashboard page, parse the DOM, and verify that the layout renders without errors.'

### 3. Terminal Error Monitoring
When running complex backend scripts or database queries, keep the AI accountable:
> '@backend-architect, before writing this PHP logic, tail the WordPress debug.log or Query Monitor logs in the terminal so you can instantly catch and fix any Fatal Errors you cause.'

### 4. The Change Management Commit
When the feature works perfectly, lock it in:
> 'Summarize all the files you just modified, generate a clean Git commit message, and execute the commit.'
