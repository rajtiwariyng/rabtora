# Landing Page Design Spec
**Date:** 2026-05-26  
**Project:** Rabtora Madhyam Management — rabtora.ae  
**File target:** `landing_page.html`  
**Purpose:** Paid ads lead-generation landing page

---

## Goal

Replace the existing `landing_page.html` (coral/violet theme, no active form) with a new page that:
- Matches the main site's design system (navy `#0d1b3d`, gold `#c99a2a`, Sen font, real logo)
- Captures leads via a working consultation form
- Is optimised for Google/Meta ad traffic (fast, focused, single CTA)

---

## Design System

| Token | Value |
|---|---|
| Primary background | `#0d1b3d` (navy) |
| Secondary background | `#1a2d5a` (navy-2) |
| Accent / panels | `#c99a2a` (gold) |
| Accent hover | `#e2b94a` (gold-light) |
| Ink (text on gold) | `#0a1020` |
| Font | Sen (400, 700, 800) — Google Fonts |
| Logo | `assets/images/logo/logo-3d.webp` |
| Button style | Pill / rounded-rect, no sharp corners |

---

## Page Structure (top → bottom)

### 1. Navigation (fixed, sticky)
- Logo (`logo-3d.webp`) left
- Phone number `+971 55 471 1132` centre-right (hidden on mobile)
- Gold CTA button `Book Free Consultation` right — scrolls to `#consultation`
- Background: `rgba(13,27,61,0.95)` + `backdrop-filter: blur(14px)`
- Bottom border: `1px solid rgba(201,154,42,0.2)`
- No other nav links (landing page — no distractions)

### 2. Hero Section
- Full viewport height (`min-height: 100vh`)
- Background: navy with subtle gold radial gradient overlay + noise texture
- Content (centred):
  - Label: `UAE's Premier Brand Visibility Partner` (gold, uppercase, letter-spaced, `—` decorators)
  - H1: `Own the Attention` (large, white, Bebas-style weight via Sen 800)
  - Sub-copy: `From luxury events to city-wide outdoor campaigns…`
  - Trust line: `Trusted by brands that want visibility beyond digital.`
  - CTA button: `Book Free Consultation →` (gold pill, scrolls to form)
- Stats bar (below CTA, separated by gold border):
  - 200+ Brands Served · 10+ Years in UAE · 500+ Projects Done · UAE Wide Coverage

### 3. Split Panel Row 1 — Outdoor + Branding
Two equal-width gold (`#c99a2a`) panels side by side:

**Left — Outdoor Advertising**
- H2: `Turn Every Road Into Brand Space`
- Body: billboards, hoardings, transit branding, LED screens copy
- CTA: `Get Outdoor Quote →` (navy pill button, scrolls to form)

**Right — Branding & Printing**
- H2: `Branding That Leaves a Mark`
- 2-col dot list: Brand Identity, Print Collateral, Packaging Design, Visual Materials, Business Cards
- CTA: `Start Branding →` (navy pill button, scrolls to form)

### 4. Bridge Section
- Background: `#1a2d5a` + subtle gold radial glow
- H2: `Built for Brands That Want More Than Reach` (white, large)
- Body: Rabtora combines outdoor, electronic, signage, and brand design copy
- Two buttons: `Book Free Consultation →` (gold) + `📞 Call Us Now` (gold outline)

### 5. Split Panel Row 2 — Electronic + Sign Boards
Two equal-width gold panels:

**Left — Electronic Media**
- H2: `Reach Millions on Screen`
- Body: TV, radio, digital display, broadcast placements copy
- CTA: `Explore Media Ads →`

**Right — Sign Boards & Letters**
- H2: `Signage That Commands Attention`
- Body: acrylic letters, illuminated fascia, storefront branding copy
- CTA: `Get a Sign Quote →`

### 6. Consultation Form Section
- Background: navy
- Header: tag `Free — No Obligation`, H2 `Get Your Free Consultation`, sub-copy
- Form card: dark glass card with gold top border (`3px solid #c99a2a`)
- **Fields:**
  - Full Name * (text)
  - WhatsApp / Phone * (tel)
  - Email Address * (email)
  - Company Name (text, optional)
  - Service Interested In * (select: Outdoor / Branding / Electronic / Sign Boards / Digital / Events / Other)
  - Project description (textarea, optional)
- **Submit button:** `Get My Free Consultation →` (full-width gold)
- **Privacy note:** `🔒 Your details are private. We'll call or WhatsApp you within 2 hours.`
- **OR divider** + WhatsApp direct button (green, links to `wa.me/97155471132` with pre-filled message)
- Trust row: Dubai HQ · Free Consultation · Reply Within 2 Hours · 200+ Brands

### 7. Footer
- Logo left, nav links centre (Privacy Policy, Terms, Contact), copyright right
- Background: `#060e1e`
- Minimal — no full site footer

---

## Form Backend

**Handler:** `assets/images/consultation_form.php`  
**Method:** POST  
**Fields sent:** `fullName`, `phone`, `email`, `goal` (+ optional `company`, `message`)  
**On success:**
1. Email sent to `connecttocontink@gmail.com`
2. Submit button turns green: `✓ Sent! Redirecting to WhatsApp...`
3. After 1.2s: opens `wa.me/97155471132` with pre-filled message including name + service
**On error:** Button shows call-to-action with phone number

---

## Behaviour & Interactions

- **Scroll reveal:** `.reveal` elements fade up on scroll (IntersectionObserver, threshold 0.1)
- **Hero animations:** staggered CSS `@keyframes up` on label, h1, sub, trust, CTA, stats
- **Nav CTA + all panel buttons:** smooth-scroll anchor to `#consultation`
- **Panel hover:** subtle lift (`translateY(-4px)`) — panels themselves don't move, only cards
- **Input focus:** border changes to gold, background gets `rgba(201,154,42,0.05)`
- **Form submit:** async fetch, no page reload

---

## Responsive Breakpoints

| Breakpoint | Change |
|---|---|
| `≤ 900px` | Split panels stack to 1 column; form rows stack |
| `≤ 560px` | Panel padding reduced; stats bar gap reduced |
| Mobile nav | Phone number hidden; logo + CTA only |

---

## What Is NOT Included

- No full site navigation menu (intentional — landing pages convert better without exit links)
- No portfolio, about, or blog sections
- No Google/Meta pixel (noted as future addition — user to provide pixel IDs)
- No "thank you" redirect page (post-submit handled inline + WhatsApp redirect)

---

## Files Affected

| File | Action |
|---|---|
| `landing_page.html` | Full rewrite |
| `assets/images/consultation_form.php` | Used as-is (add `company` + `message` fields to email body) |

---

## Suggested Future Additions (post-launch)

1. **Google Tag / Meta Pixel** — add tracking script once pixel IDs are provided
2. **Thank You page** (`thank-you.html`) — for cleaner conversion tracking in ad platforms
3. **UTM parameter capture** — hidden form fields to track which ad/campaign sent the lead
4. **Real client stats** — replace placeholder numbers (200+, 10+, 500+) with verified figures
5. **Client logo strip** — social proof section using `assets/images/clients/` images
