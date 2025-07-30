=== Solar Forecast Dashboard ===
Contributors: noumaan 
Tags: solar forecast, solar energy, solcast
Requires at least: 5.8  
Tested up to: 6.8.2  
Requires PHP: 7.4  
Stable tag: 1.0.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Fetch, store, and display solar power forecast data using the Solcast API. Includes charts, daily reports, frontend shortcodes, and email reports.

== Description ==

**Solar Forecast Dashboard** is a lightweight and privacy-conscious plugin that integrates with the [Solcast API](https://solcast.com) to show solar power forecast data for your location.

Use it to display detailed 24-hour solar predictions, visualized with charts, and store historical forecast data for visitors to explore. It’s designed for small solar projects, educators, or anyone interested in solar insights.

**Features:**

- Fetch 24-hour forecast data from Solcast API
- Store data daily in a custom database table
- Display reports using shortcodes
- Chart.js visualizations (respecting cookie consent)
- Optional daily email report
- Manual fetch and deletion tools in admin
- Lightweight and privacy-friendly

**Shortcodes:**

## Shortcodes

- `[sfd_forecast_tomorrow]` – Displays the total solar energy forecast (in kWh) for **tomorrow** based on stored forecast data.

- `[sfd_today_generation]` – Shows the total solar energy forecast (in kWh) for **today**.

- `[sfd_month_summary]` – Outputs the total solar energy forecast (in kWh) for the **current month**, summing up all available daily forecasts.

- `[sfd_monthly_impact]` – Displays the **environmental impact** of solar generation for the current month:  
  Approximate **CO₂ saved** (kg) and equivalent **number of trees planted**.

- `[sfd_year_summary]` – Outputs the total solar energy forecast (in kWh) for the **current year**, combining all available daily forecasts.

- `[sfd_yearly_impact]` – Displays the **environmental impact** of solar generation for the current year:  
  Approximate **CO₂ saved** (kg) and equivalent **number of trees planted**.

- `[sfd_public_reports]` – Generates a **public report listing** of all forecast data:  
  Users can click on a specific date to view a detailed forecast. The detailed view includes a chart and a table showing:  
  - Low (10%) estimate  
  - Expected (50%) estimate  
  - High (90%) estimate  
  for each time period of the day.


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Solar Forecast > Settings** to enter your Solcast API Key and configure options.
4. Use the shortcodes in posts or pages to display forecasts.
5. Keep the reports private and view forecast data in admin area by visiting **Solar Forecast > Reports** page.

== Frequently Asked Questions ==

= Where do I get a Solcast API key? =  
You can sign up for a free API key at [Solcast.com](https://solcast.com). Make sure to set your API region and site settings.

= Will this work without user consent for cookies? =  
If you enable the cookie-respect setting, charts will only be displayed if the user has given marketing consent via supported plugins (e.g., Cookie Consent, CookieHub, Complianz, Borlabs).

= Can I customize the charts? =  
Chart.js is loaded from a CDN. You can override styles using CSS or modify the rendering logic in the template if you're a developer.

= Does it work with caching plugins? =  
Yes. The data is rendered server-side and stored daily, so output is cache-friendly.

== Screenshots ==

1. Forecast display with chart and download button
2. Admin settings page
3. Daily email report example

== Changelog ==

= 1.0.0 =
* Initial release
* Fetch and display Solcast forecast data
* Daily cron jobs
* Admin settings
* Frontend shortcodes and Chart.js graphs
* Email report feature

== Upgrade Notice ==

= 1.0.0 =
First stable release of the Solar Forecast Display plugin.

== License ==

This plugin is licensed under the GPL v2 or later.
