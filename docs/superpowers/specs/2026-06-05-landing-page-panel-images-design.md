# Landing Page Panel Images — Design Spec

**Date:** 2026-06-05  
**File:** `landing_page.html`  
**Status:** Approved

---

## Goal

Add relevant background images to all 4 service panels on `landing_page.html`. No content (text, headings, CTAs, service lists) will be changed — only CSS/styling additions.

---

## Scope

### In scope
- 4 gold service panels: Outdoor, Branding, Electronic Media, Signage
- CSS changes only: background-image, overlay, text colour, button colour

### Out of scope
- Hero section
- Bridge section
- Consultation form
- Any text or copy changes

---

## Design

### Approach: Image as panel background with gold overlay

Each panel receives a `background-image` (photo from the existing assets directory). A semi-transparent gold gradient overlay sits on top of the photo to:
1. Preserve Rabtora's gold brand colour
2. Ensure text remains legible

### Image mapping

| Panel | Heading | Image file |
|---|---|---|
| Outdoor | "Turn Every Road Into Brand Space" | `assets/images/services/out1.jpg` |
| Branding | "Branding That Leaves a Mark" | `assets/images/branding-2.jpg` |
| Electronic Media | "Reach Millions on Screen" | `assets/images/electronic-media.png` |
| Signage | "Signage That Commands Attention" | `assets/images/new-signboard.jpeg` |

---

## CSS Changes

### Per-panel background images
Each `.panel` element gets a unique class (`panel-outdoor`, `panel-branding`, `panel-electronic`, `panel-signage`) plus:
- `background-image: url(...)` pointing to its matched asset
- `background-size: cover`
- `background-position: center`

### Overlay element
A `<div class="panel-overlay">` is added as the first child of each `.panel`. It is positioned `absolute; inset: 0; z-index: 0` with:
```css
background: linear-gradient(
  135deg,
  rgba(201,154,42,0.82) 0%,
  rgba(166,126,30,0.88) 60%,
  rgba(13,27,61,0.6) 100%
);
```

### Text colour updates
- `.panel h2`: `color: var(--ink)` → `color: #fff` with `text-shadow: 0 2px 12px rgba(0,0,0,0.3)`
- `.panel p`: `color: rgba(10,16,32,0.72)` → `color: rgba(255,255,255,0.88)`
- `.svc-item` (branding panel list): `color: var(--ink)` → `color: #fff`
- `.svc-dot`: `background: var(--navy)` → `background: #fff`

### Button style update
`.btn-panel` changes from solid navy fill to ghost/translucent style:
```css
background: rgba(255,255,255,0.15);
border: 1.5px solid rgba(255,255,255,0.6);
color: #fff;
backdrop-filter: blur(8px);
```
Hover state: `background: rgba(255,255,255,0.25)`

### z-index stacking
- `.panel-overlay`: `z-index: 0`
- `.panel-inner`, `.panel-action`: `z-index: 1` (already `position: relative` via `.panel-inner`)
- `.svc-grid`: `z-index: 1` (already set)

---

## Constraints

- **No content changes** — all headings, body copy, service lists, and CTA button labels remain identical
- Use only images already present in `assets/images/`
- Must remain responsive — `background-size: cover` handles this automatically
- Accessibility: overlay contrast ratio must remain readable (white text on gold/dark = passes WCAG AA)
