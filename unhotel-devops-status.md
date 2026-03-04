# Unhotel Local Development: Architecture & Roadmap

## 1. Current System Architecture (Completed)

* **Infrastructure:** Dockerized WordPress environment running on Windows 11 via WSL2 (ARM64 native images for optimal Snapdragon performance).
* **Location:** C:\unhotel-local-dev (Evicted from OneDrive to eliminate severe cross-OS file I/O bottlenecks).
* **Database:** SiteGround live database successfully imported. URLs aggressively rewritten to http://localhost:8080 using WP-CLI.
* **File System:** 17.5 GB wp-content folder mounted as a local volume. SiteGround caching (sg-cachepress) deactivated.
* **Performance Optimizations:** OPcache enabled via custom .ini. Heavy local-breaking plugins (WPML, FluentSMTP, Site Kit) manually deactivated, reducing TTFB from 42+ seconds down to ~15 seconds.
* **Version Control:** Git initialized with a strict .gitignore to prevent tracking massive media uploads, cache, and SQL dumps. Baseline committed.
* **Custom Logic:** Implemented unhotel-symlink-guardian.php (MU-Plugin) to protect VikBooking upload paths during updates.

## 2. Standard Operating Procedure (SOP): Core Commands

* **Start Environment:** `docker compose up -d`
* **Stop Environment:** `docker compose down`
* **View Running Status:** `docker ps`

**Pull Fresh Live Database (Future):**

1.  Export .sql from SiteGround.
2.  Replace unhotel-live-db.sql in local root.
3.  Run: `docker compose exec db bash -c 'mysql -u wordpress -pwordpress wordpress < /tmp/unhotel-live-db.sql'`
4.  Run: `docker compose exec wordpress wp search-replace "https://unhotel.com.br" "http://localhost:8080" --skip-columns=guid --allow-root`

## 3. Pending Roadmap & Future Tasks

* **Regression Prevention Strategy:** Implement visual regression testing and automated PHP unit tests to ensure future development does not break existing property management agency booking flows.
* **Antigravity Workspace Setup:** Define dedicated AI Agent profiles, tool permissions, and workspace context boundaries for the Unhotel project.
* **Prompt Library & Instructions:** Document global AI instructions and specific system prompts for generating Unhotel components (e.g., custom JetEngine queries, VikBooking overrides).
* **GitHub & SiteGround Integration:** Connect the local Git repository to a remote GitHub origin, and establish a deployment pipeline to bridge with SiteGround's native Git repository tool.
* **Change Management Protocol:** Establish a standardized format for documenting all future code changes, plugin additions, and database schema updates.
* **Security & Performance Audits:** Schedule automated WP-CLI vulnerability scans and external speed profiling (Lighthouse/GTmetrix) for both the local staging and the live production environments.
