# SkillyChatbot Themes

This folder contains ready-to-use themes for the SkillyChatbot platform.

## Available Themes

### Skylight
A modern, clean theme with:
- Vibrant gradient hero section
- Professional Inter font
- Card hover animations
- Dark navbar & footer
- Smooth entrance animations
- Responsive mobile design

## How to Install a Theme

1. **Create the ZIP** — zip the theme folder contents (so `theme.json` is at the root of the ZIP, not inside a subfolder):
   ```bash
   cd themes/skylight
   zip -r ../skylight-theme.zip .
   ```

2. **Upload** — Go to **Admin Panel → Appearance → Themes → Install Theme**

3. **Activate** — After installation, click **Activate** on the theme card

4. **Configure** (optional) — Click **Configure** to tweak CSS/JS or add custom code

## Theme Structure

Each theme folder must contain `theme.json` at the root:

```
theme-name/
  theme.json        ← Required: theme metadata + config
  preview.png       ← Optional: 800×600 preview screenshot
```

### theme.json Format

```json
{
    "name": "Display Name",
    "slug": "url-friendly-slug",
    "description": "Short description",
    "version": "1.0.0",
    "author": "Your Name",
    "config": {
        "custom": {
            "head_html": "<!-- extra <link> or <meta> tags -->",
            "css": "/* your CSS overrides */",
            "js": "/* your JS micro-interactions */"
        }
    }
}
```

The `css` field supports full CSS including variables, media queries, and animations.
The `head_html` field is injected inside `<head>` — use it for Google Fonts, etc.
The `js` field runs after the page loads.
