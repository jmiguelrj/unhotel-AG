Unhotel Antigravity Agent Instructions
Environment Awareness: This is a Dockerized WordPress environment running via WSL2. Never modify docker-compose.yml, .gitignore, or core infrastructure files unless explicitly requested.

Development Boundaries: All custom development (e.g., the Reception Dashboard) must occur strictly within wp-content/plugins/unhotel-dashboard/, property-owner-access or wp-content/mu-plugins/. Do not modify third-party plugins like VikBooking directly ; use hooks and overrides.

Database Operations: Any operations requiring database changes must be executed via WP-CLI inside the wordpress Docker container.

Version Control Protocol: Do not automatically commit changes. Always summarize file modifications and wait for the user to review.
