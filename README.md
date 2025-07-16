# Solar Forecast Dashboard

A custom WordPress plugin to fetch, store, and display solar power forecast data using the [Solcast API](https://solcast.com). Includes charts, daily cron jobs, frontend shortcodes, and optional email reports.

---

## 🧩 Features

- Fetch 24-hour forecast data from Solcast API
- Store data daily in a custom database table
- Display reports using shortcodes
- Chart.js visualizations (respecting cookie consent)
- Optional daily email report
- Manual fetch and deletion tools in admin
- Lightweight and privacy-friendly

---

## 🔌 Shortcodes

- `[sfd_forecast]` – Displays a list of forecast reports with charts and CSV downloads  
- `[sfd_forecast_single]` – Shows a detailed chart for a specific date (via query string `?sfd_view=YYYY-MM-DD`)

---

## ⚙️ Installation

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Solar Forecast > Settings** to enter your Solcast API Key and configure options.
4. Use the shortcodes in posts or pages to display forecasts.
5. Visit **Solar Forecast > Reports** in the admin area to view stored data.

---

## 🙋 FAQ

### Where do I get a Solcast API key?
You can sign up for a free API key at [Solcast.com](https://solcast.com). Make sure to set your API region and site settings.

### Will this work without user consent for cookies?
If you enable the cookie-respect setting, charts will only be displayed if the user has given marketing consent via supported plugins (e.g., Cookie Consent, CookieHub, Complianz, Borlabs).

### Can I customize the charts?
Chart.js is loaded from a CDN. You can override styles using CSS or modify the rendering logic in the template if you're a developer.

### Does it work with caching plugins?
Yes. The data is rendered server-side and stored daily, so output is cache-friendly.

---

## 🖼️ Screenshots

1. Forecast display with chart and download button  
2. Admin settings page  
3. Daily email report example  

---

## 📝 Changelog

### 1.0.0
- Initial release
- Fetch and display Solcast forecast data
- Daily cron jobs
- Admin settings
- Frontend shortcodes and Chart.js graphs
- Email report feature

---

## 🛠 Upgrade Notice

### 1.0.0
First stable release of the Solar Forecast Display plugin.

---

## 📄 License

This plugin is licensed under the [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---

## 📦 Plugin Info

- **Contributors**: noumaan  
- **Tags**: solar forecast, renewable energy, weather data, solcast, chart.js, energy monitoring  
- **Requires at least**: WordPress 5.5  
- **Tested up to**: 6.8  
- **Requires PHP**: 7.4  
- **Stable tag**: 1.0.0  
# solar-forecast-dashboard
A WordPress plugin to display daily solar forecast data from Solcast.
