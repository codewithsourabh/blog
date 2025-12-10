# BlogsHQ Admin Toolkit - Personal Edition

Personal WordPress admin toolkit featuring category logos, table of contents, and AI-powered share functionality.

## Features

- 🎨 **Category Logos** - Light/dark mode support with shortcodes
- 📑 **Table of Contents** - Auto-generated TOC with smooth scrolling
- 🤖 **AI Share** - Share content via ChatGPT, Claude, Gemini, and more
- ✨ **Modern Admin UI** - Beautiful, responsive admin interface

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher

## Installation

1. Upload plugin folder to `/wp-content/plugins/`
2. Activate through WordPress admin
3. Configure via BlogsHQ menu

## Quick Start

### Category Logos

Display category logos with light/dark mode support:

```
[blogshq_category_logo]
```

---

### Table of Contents

Automatically inserted on mobile, or use the shortcode below:

```
[blogshq_toc]
```

---

### AI Share

Enable AI-powered sharing functionality:

```
[ai_share]
```

---

## Development

```bash
# Install dependencies
npm install

# Build assets
npm run build

# Watch for changes
npm run watch
```

## License

GPL v2 or later