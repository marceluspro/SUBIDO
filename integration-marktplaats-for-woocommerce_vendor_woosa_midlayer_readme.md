## Introduction

This module provides essential functionality for the Midlayer app, including:

* **Status Notifications:** Sends notifications when the plugin status changes (activated, deactivated, or uninstalled).
* **Cron Job Management:** Notifies Midlayer to add or remove cron jobs from the cron UI as needed.
* **Authorization Extension:** Expands the [Authorization](https://gitlab.com/woosa/wp-plugin-modules/authorization)n module by adding a view where users can register their shop and grant or revoke plugin access.
* **Automatic Disconnection:** Automatically disconnects the shop if WooCommerce REST API keys or the `woosa_midlayer` WP user are removed.
* **Secret Key Field:** Adds a field in WooCommerce’s general settings to store the `woosa_secret` key.
* **Re-registration Prompt:** Requires the shop owner to re-register if changes occur to the original shop instance.
* **Daily Midlayer Ping:** Pings the Midlayer daily at a specified time.

## Installation

* Run composer `require woosa/midlayer:<version>` in the plugin's root directory. Alternatively, you can add `"woosa/midlayer": "<version>"` directly in `composer.json` and then run `npm start`