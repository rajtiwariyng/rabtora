# Landing Page Panel Images Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add background images to all 4 service panels on `landing_page.html` using a gold gradient overlay, with no content changes.

**Architecture:** Each panel gets a unique CSS class identifier. Background images are set via CSS. A `<div class="panel-overlay">` is inserted as the first child of each panel to render the gold gradient over the photo. Text and button colours are updated to white for readability on the image background.

**Tech Stack:** HTML, CSS (inline `<style>` block in `landing_page.html`)

---

## Files Modified

| File | Change |
|---|---|
| `landing_page.html` | Add panel-specific CSS classes to 4 panel divs; insert overlay divs; add new CSS rules; update text + button colour rules |

No new files created. All changes are in one file.

---

### Task 1: Add panel identifier classes to HTML + background-image CSS rules

**Files:**
- Modify: `landing_page.html` (HTML panel divs + CSS `<style>` block)

- [ ] **Step 1: Add CSS rules for panel background images**

In `landing_page.html`, find the block ending with:
```css
    .panel-action { position: relative; z-index: 1; }
```
(around line 168). Add the following CSS immediately after it:

```css
    /* ── PANEL BACKGROUND IMAGES ────────────────── */
    .panel-outdoor    { background-image: url('assets/images/services/out1.jpg');    background-size: cover; background-position: center; }
    .panel-branding   { background-image: url('assets/images/branding-2.jpg');       background-size: cover; background-position: center; }
    .panel-electronic { background-image: url('assets/images/electronic-media.png'); background-size: cover; background-position: center; }
    .panel-signage    { background-image: url('assets/images/new-signboard.jpeg');   background-size: cover; background-position: center; }
```

- [ ] **Step 2: Add `panel-outdoor` class to the first panel div**

Find (around line 370):
```html
    <div class="panel reveal">
      <div class="panel-inner">
        <h2>Turn Every Road Into Brand Space</h2>
```
Change to:
```html
    <div class="panel panel-outdoor reveal">
      <div class="panel-inner">
        <h2>Turn Every Road Into Brand Space</h2>
```

- [ ] **Step 3: Add `panel-branding` class to the second panel div**

Find (around line 381):
```html
    <div class="panel reveal" style="transition-delay:0.12s">
      <div class="panel-inner">
        <h2>Branding That Leaves a Mark</h2>
```
Change to:
```html
    <div class="panel panel-branding reveal" style="transition-delay:0.12s">
      <div class="panel-inner">
        <h2>Branding That Leaves a Mark</h2>
```

- [ ] **Step 4: Add `panel-electronic` class to the third panel div**

Find (around line 414):
```html
    <div class="panel reveal">
      <div class="panel-inner">
        <h2>Reach Millions on Screen</h2>
```
Change to:
```html
    <div class="panel panel-electronic reveal">
      <div class="panel-inner">
        <h2>Reach Millions on Screen</h2>
```

- [ ] **Step 5: Add `panel-signage` class to the fourth panel div**

Find (around line 425):
```html
    <div class="panel reveal" style="transition-delay:0.12s">
      <div class="panel-inner">
        <h2>Signage That Commands Attention</h2>
```
Change to:
```html
    <div class="panel panel-signage reveal" style="transition-delay:0.12s">
      <div class="panel-inner">
        <h2>Signage That Commands Attention</h2>
```

- [ ] **Step 6: Visual check**

Open `http://localhost/rabtora.ae/landing_page.html` in a browser.
Scroll to the service panels. Each of the 4 panels should now show its photo as a background. The gold background colour is still applied (from the base `.panel { background: var(--gold) }` rule) but the image will show through. Text may be hard to read — that is expected at this step.

- [ ] **Step 7: Commit**

```bash
git add landing_page.html
git commit -m "feat: add background image classes to service panels"
```

---

### Task 2: Add overlay div to each panel

**Files:**
- Modify: `landing_page.html` (HTML + CSS `<style>` block)

- [ ] **Step 1: Add `.panel-overlay` CSS rule**

In `landing_page.html`, directly after the 4 background-image rules added in Task 1, add:

```css
    .panel-overlay {
      position: absolute; inset: 0; z-index: 0;
      background: linear-gradient(
        135deg,
        rgba(201,154,42,0.82) 0%,
        rgba(166,126,30,0.88) 60%,
        rgba(13,27,61,0.6) 100%
      );
    }
```

- [ ] **Step 2: Insert overlay div into the outdoor panel**

Find:
```html
    <div class="panel panel-outdoor reveal">
      <div class="panel-inner">
```
Change to:
```html
    <div class="panel panel-outdoor reveal">
      <div class="panel-overlay"></div>
      <div class="panel-inner">
```

- [ ] **Step 3: Insert overlay div into the branding panel**

Find:
```html
    <div class="panel panel-branding reveal" style="transition-delay:0.12s">
      <div class="panel-inner">
```
Change to:
```html
    <div class="panel panel-branding reveal" style="transition-delay:0.12s">
      <div class="panel-overlay"></div>
      <div class="panel-inner">
```

- [ ] **Step 4: Insert overlay div into the electronic panel**

Find:
```html
    <div class="panel panel-electronic reveal">
      <div class="panel-inner">
```
Change to:
```html
    <div class="panel panel-electronic reveal">
      <div class="panel-overlay"></div>
      <div class="panel-inner">
```

- [ ] **Step 5: Insert overlay div into the signage panel**

Find:
```html
    <div class="panel panel-signage reveal" style="transition-delay:0.12s">
      <div class="panel-inner">
```
Change to:
```html
    <div class="panel panel-signage reveal" style="transition-delay:0.12s">
      <div class="panel-overlay"></div>
      <div class="panel-inner">
```

- [ ] **Step 6: Visual check**

Refresh `http://localhost/rabtora.ae/landing_page.html`.
Each panel should now show the gold gradient overlay on top of its photo. The panels should look similar to the original gold panels, but with a subtle photo texture visible underneath.

- [ ] **Step 7: Commit**

```bash
git add landing_page.html
git commit -m "feat: add gold overlay div to service panels"
```

---

### Task 3: Update text and button colours for readability

**Files:**
- Modify: `landing_page.html` (CSS `<style>` block only)

- [ ] **Step 1: Update `.panel h2` colour to white**

Find in the `<style>` block:
```css
    .panel h2 {
      font-size: clamp(1.8rem, 3.2vw, 2.8rem); font-weight: 800;
      letter-spacing: -0.01em; color: var(--ink); line-height: 1.08;
      margin-bottom: 1.1rem;
    }
```
Change to:
```css
    .panel h2 {
      font-size: clamp(1.8rem, 3.2vw, 2.8rem); font-weight: 800;
      letter-spacing: -0.01em; color: #fff; line-height: 1.08;
      margin-bottom: 1.1rem; text-shadow: 0 2px 12px rgba(0,0,0,0.3);
    }
```

- [ ] **Step 2: Update `.panel p` colour to white**

Find:
```css
    .panel p { font-size: 0.94rem; font-weight: 400; line-height: 1.75; color: rgba(10,16,32,0.72); }
```
Change to:
```css
    .panel p { font-size: 0.94rem; font-weight: 400; line-height: 1.75; color: rgba(255,255,255,0.88); }
```

- [ ] **Step 3: Update `.svc-item` and `.svc-dot` colours to white**

Find:
```css
    .svc-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.83rem; font-weight: 700; color: var(--ink); }
    .svc-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--navy); flex-shrink: 0; opacity: 0.6; }
```
Change to:
```css
    .svc-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.83rem; font-weight: 700; color: #fff; }
    .svc-dot { width: 8px; height: 8px; border-radius: 50%; background: #fff; flex-shrink: 0; opacity: 0.7; }
```

- [ ] **Step 4: Update `.btn-panel` to ghost style**

Find:
```css
    .btn-panel {
      display: inline-flex; align-items: center; gap: 0.4rem;
      padding: 0.8rem 1.8rem; background: var(--navy); color: #fff;
      font-family: 'Sen', sans-serif; font-size: 0.82rem; font-weight: 700;
      letter-spacing: 0.04em; border-radius: 50px; border: none; cursor: pointer;
      transition: background 0.22s, transform 0.22s; white-space: nowrap;
    }
    .btn-panel:hover { background: var(--navy-2); transform: translateY(-2px); }
```
Change to:
```css
    .btn-panel {
      display: inline-flex; align-items: center; gap: 0.4rem;
      padding: 0.8rem 1.8rem; background: rgba(255,255,255,0.15); color: #fff;
      font-family: 'Sen', sans-serif; font-size: 0.82rem; font-weight: 700;
      letter-spacing: 0.04em; border-radius: 50px; border: 1.5px solid rgba(255,255,255,0.6); cursor: pointer;
      transition: background 0.22s, transform 0.22s; white-space: nowrap;
      backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    }
    .btn-panel:hover { background: rgba(255,255,255,0.25); transform: translateY(-2px); }
```

- [ ] **Step 5: Final visual check**

Refresh `http://localhost/rabtora.ae/landing_page.html`.
Verify all 4 panels:
- Each panel shows its background photo with a gold overlay
- Headings are white and readable
- Body text is white/semi-transparent and readable
- Branding panel service list items are white
- CTA buttons show as ghost (translucent with white border)
- No existing text content has changed
- Scroll reveal animations still work on page load
- Mobile: resize to < 900px — panels stack vertically, each fills full width with its image

- [ ] **Step 6: Commit**

```bash
git add landing_page.html
git commit -m "feat: update panel text and button colours for image backgrounds"
```
