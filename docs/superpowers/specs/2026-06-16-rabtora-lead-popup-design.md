# Rabtora Lead Capture Popup — Design Spec
Date: 2026-06-16

## Overview

Add a popup lead-capture form to the Rabtora landing page (`landing-page/index.html`). All CTA buttons open the modal. Leads are stored in a `rabtora` MySQL database. The admin panel is updated to display the new fields and rebranded to Rabtora.

---

## 1. Database & Schema

**Database:** `rabtora`
- `config/db.php` changes `dbname=hudayriyatluxuryvillas` → `dbname=rabtora`
- `admin/setup.php` updated to create `rabtora` DB and the updated `leads` table

**Table:** `leads`

| Column         | Type         | Nullable | Notes                                      |
|----------------|--------------|----------|--------------------------------------------|
| id             | INT UNSIGNED | NO       | Auto-increment PK                          |
| form_source    | VARCHAR(20)  | NO       | e.g. `landing_popup`                       |
| full_name      | VARCHAR(255) | NO       | Required                                   |
| phone          | VARCHAR(50)  | NO       | Required                                   |
| email          | VARCHAR(255) | YES      | Optional, validated format                 |
| company_name   | VARCHAR(255) | YES      | Optional — NEW                             |
| budget         | VARCHAR(100) | YES      | One of 5 allowed range strings — NEW       |
| services       | VARCHAR(500) | YES      | Comma-separated selected services — NEW    |
| ip_address     | VARCHAR(45)  | YES      | Auto-captured                              |
| created_at     | DATETIME     | NO       | DEFAULT CURRENT_TIMESTAMP                  |

Old columns `interest` and `community` are **dropped** from the new schema (not used by any active form going forward).

**Budget allowed values:**
- `Under AED 10,000`
- `AED 10,000 – 50,000`
- `AED 50,000 – 100,000`
- `AED 100,000 – 500,000`
- `Above AED 500,000`

**Services allowed values:**
- `Hoardings`
- `UniPols`
- `Digital Hoardings`
- `Transit Marketing`

---

## 2. Popup Form — Landing Page

**File:** `landing-page/index.html`

**Triggers:** The following 3 elements intercept their default anchor navigation and open the modal instead:
- `.btn-quote` (hero — "Request A Quote")
- `.btn-book` (header — "Book Now")
- `.btn-cta-quote` (CTA section — "Request A Quote")

**Modal structure:**
- Full-screen dark overlay (`z-index` above all content)
- Centred card panel
- × close button (top-right)
- Click outside modal card closes it

**Form fields (in order):**
1. Full Name — text input, required
2. Mobile — tel input, required
3. Email — email input, optional
4. Company Name — text input, optional
5. Budget — `<select>` dropdown, required (5 options + placeholder "Select Budget")
6. Services — 4 checkboxes (at least 1 required client-side): Hoardings, UniPols, Digital Hoardings, Transit Marketing

**Form behaviour:**
- `form_source` hidden field = `landing_popup`
- On submit: disable button, show "Sending…"
- On success (`ok: true`): replace form with "Thank you! We'll be in touch." message
- On error: show inline error message below the form, re-enable button
- POSTs via `fetch()` to `../submit_lead.php`

**Styling:**
- Dark background matching the landing page palette
- Gold accent colour (`#c9a84c`) for focus rings, checkbox highlights, submit button
- DM Sans font (already loaded on page)
- Fully responsive — modal card max-width 520px, scrollable on small screens

---

## 3. Backend — submit_lead.php

**File:** `submit_lead.php` (root)

**New fields handled:**
- `company_name` — trimmed, stored as-is, nullable
- `budget` — trimmed, validated against the 5 allowed strings (reject if not in list and not empty)
- `services` — received as `services[]` array POST param, each value validated against the 4 allowed service strings, joined to comma string for storage

**Updated INSERT columns:**
`form_source, full_name, phone, email, company_name, budget, services, ip_address`

**Validation rules:**
- `full_name` and `phone` are required (400 if missing)
- `email` optional but validated if present (400 if invalid format)
- `budget` optional but must be one of the 5 allowed values if provided (400 if invalid)
- `services` values each validated against allowed list; unknown values silently dropped

---

## 4. Admin Panel — dashboard.php

**File:** `admin/dashboard.php`

**Branding:** "Hudayriyat Island Admin" → "Rabtora Admin"

**Stats row (5 cards → 3 cards):**
1. Total Leads
2. Today
3. Landing Popup (count where `form_source = 'landing_popup'`)

**Table columns** (replaces old "Interest / Community"):
`# | Source | Full Name | Phone | Email | Company | Budget | Services | Submitted | (delete)`

**Filter dropdown:** Updated from `hero/mid/footer` to `landing_popup` (+ "All Sources")

**CSV export:** Updated headers and row data to include `company_name`, `budget`, `services`; removes old `interest`/`community` columns.

---

## 5. Files Changed

| File | Change |
|------|--------|
| `config/db.php` | DB name `hudayriyatluxuryvillas` → `rabtora` |
| `admin/setup.php` | New DB name, updated `leads` table schema |
| `submit_lead.php` | Handle `company_name`, `budget`, `services[]` |
| `landing-page/index.html` | Add modal HTML + inline CSS + JS |
| `admin/dashboard.php` | New columns, rebranded, updated stats/filter/CSV |

---

## 6. Out of Scope

- Email notifications on form submission
- CAPTCHA / spam protection
- Main site forms (index.html, contact.html etc.) — untouched
- WhatsApp button integration
