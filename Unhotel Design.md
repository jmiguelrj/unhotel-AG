# Unhotel Design

# Unhotel Digital Identity & UI System 

**Primary brand colour:** `#FA4676`

**Identity name (internal):** *Unhotel Soft Console*

**Purpose:** A single, reusable digital identity that works across **any Unhotel page** (dashboard, listings, reservations, finances, owners portal, support, settings, marketing admin), while preserving the look-and-feel of the reference UI.

***

## 1) Brand personality (how it should feel)

Unhotel’s interface should feel:

* **Calm under pressure** (operations-friendly, never noisy)
* **Modern and premium** (clean lines, refined spacing)
* **Warm and human** (hospitality energy, not corporate cold)
* **Fast to scan** (information hierarchy is obvious)

Design keyword trio: **Soft · Structured · Trustworthy**

***

## 2) Visual language principles (non-negotiables)

1. **Whitespace is a feature**: avoid dense UI.
2. **Cards over boxes**: content lives inside rounded surfaces.
3. **Pills for state**: statuses, filters, actions use rounded chips/pills.
4. **Quiet borders, quieter shadows**: depth is subtle; never heavy.
5. **Hierarchy by typography + spacing**, not by colour flooding.
6. **One accent colour, used intentionally**: `#FA4676` signals “primary”.

***

## 3) Layout system (applies to any page)

### A) Page scaffold

Use a consistent scaffold on all app pages:

* **Left navigation** (persistent on desktop; collapsible on smaller screens)
* **Page header** (title + context + primary actions)
* **Content region** (cards, tables, lists, forms)
* Optional **right inspector panel** (for details/editing), when the page benefits from a split-view.

### B) Common layout patterns (choose per page)

1. **List → Detail (Split view)**

* Left/centre: list/table
* Right: detail inspector
* Best for: Reservations, Guests, Tasks, Owners, Messages

1. **Card Grid**

* Responsive grid of cards
* Best for: Properties, Reports, Integrations, Settings categories

1. **Form-centric**

* Single column (or two-column) form in a card
* Best for: Create/edit listing, pricing rules, check-in setup

1. **Dashboard overview**

* KPI cards + lists + quick actions
* Best for: Reception, Operations, Performance pages

### Responsive behaviour

* **Desktop:** sidebar + content (+ optional inspector)
* **Tablet:** sidebar collapses to icon rail/drawer; inspector becomes a slide-over
* **Mobile:** single column; navigate between list and detail; primary CTA becomes sticky bottom action when appropriate

***

## 4) Colour identity

### Primary

* **Unhotel Accent:** `#FA4676`

### Neutrals (recommended)

* **Background:** `#F7F8FA` (or very close)
* **Surface (cards/panels):** `#FFFFFF`
* **Border:** `#E8EBF0`
* **Text (primary):** `#111827`
* **Text (secondary):** `#6B7280`
* **Icon muted:** `#9CA3AF`

### Colour usage rules

* Use `#FA4676` for:
  * Primary CTA buttons
  * Selected states (subtle tint or left indicator)
  * Important highlights (sparingly)
  * Focus rings (soft tint)
* Never use `#FA4676` as a full-page background.
* Status colours must be **soft tints**, not saturated blocks.

***

## 5) Typography identity

### Typeface

* Modern sans-serif: **Inter** (or equivalent system font stack)

### Scale (guideline)

* **H1 page title:** 20–24 / semibold
* **H2 section title:** 14–16 / semibold
* **Body:** 13–14 / regular
* **Meta & captions:** 11–12 / regular
* **Labels:** 11–12 / medium (often uppercase optional)

### Text hierarchy rules

* Titles are short and scannable.
* Secondary info is grey and smaller.
* Use **numbers** (IDs, amounts, dates) with slightly stronger weight.

***

## 6) Shape, spacing, and motion

### Corner radius

* **Cards/panels:** 12–16px
* **Inputs:** 10–12px
* **Pills/chips/buttons:** 999px

### Spacing system

* Use an **8px grid** everywhere: 8 / 16 / 24 / 32 / 40

### Shadows & borders

* Default: **border first**, shadow second (very soft)
* Hover: slight shadow lift OR slight background tint

### Motion (subtle)

* 120–180ms transitions
* Easing: standard ease-out
* Use motion to confirm state changes, not to entertain

***

## 7) Component identity (reusable everywhere)

### A) Buttons

**Primary**

* Background: `#FA4676`
* Text: white
* Fully rounded
* Medium height (40–44px)
* Used for the single most important action on the screen

**Secondary**

* White surface + border
* Grey text
* Fully rounded

**Tertiary / Ghost**

* No background, subtle hover tint
* Used in toolbars and inside cards

### B) Status pills (system-wide)

* Fully rounded
* Soft tinted background + readable text
* Consistent height (24–28px)
* Used for statuses, platforms, tags

**Examples (semantic, not fixed colours)**

* Success: “Paid”, “Checked-in”, “Active”
* Info: “Configured”, “In progress”
* Warning: “Pending”, “Needs action”
* Neutral: “Draft”, “Archived”

### C) Cards

* White surface
* Thin border
* Optional icon/title row
* Body content uses clear spacing
* Cards can be:
  * **Info cards** (summary)
  * **Interactive cards** (clickable)
  * **Form cards** (inputs inside)

### D) Lists & tables

* List rows appear as card-like items or table rows with generous padding
* Left-to-right: primary label → meta → status/action
* Selected row state is always obvious (subtle accent indicator)

### E) Inputs

* Rounded
* Light border
* Focus ring: tinted `#FA4676` (subtle)
* Placeholder text is grey
* Error states: soft red tint + clear message

### F) Badges & counters

* Small pill counters for totals (e.g., 15)
* Always aligned to the right of label
* Use neutral styling unless it’s critical

### G) Avatars & thumbnails

* Circular avatars for people
* Rounded thumbnails for properties
* When combined: avatar can overlap a cover image for warmth

### H) Panels / Inspector

* Right-side panel for detail/edit context
* Contains:
  * hero (image or header)
  * identity block (name + meta)
  * primary CTA
  * summary card(s)
  * activity/history

***

## 8) Content tone (microcopy identity)

* Friendly, simple Portuguese first (and consistent English version).
* Use verbs that match hospitality ops:
  * “Check-in”, “Instruído”, “Pendente”, “Adicionar detalhes”, “Histórico”
* Avoid bureaucratic language. Prefer human clarity.

***

## 9) Accessibility baseline

* Minimum contrast for text on tinted backgrounds
* Focus states visible for keyboard navigation
* Do not rely only on colour for status: include text + optional icon
* Buttons and list rows must have large tap targets (44px height on mobile)

***

# 10) AI Prompt (Generalised — works for ANY page)

## Prompt (copy/paste)

Design a web application page for Unhotel using the **Unhotel Soft Console** digital identity. The UI must feel calm, modern, premium, and hospitality-friendly, with generous whitespace, rounded cards, subtle borders, and pill-style statuses.

### Brand

* Primary accent colour: **#FA4676**
* Background: very light grey/near-white
* Cards/panels: white surfaces, thin borders, minimal shadows
* Typography: Inter-like modern sans-serif, clear hierarchy

### Core style constraints (must follow)

* 8px spacing system
* Card radius 12–16px; pills/buttons fully rounded
* Statuses displayed as soft tinted pills
* Selected states use subtle accent tint or a slim accent indicator (do not flood areas with colour)
* Inputs: rounded, thin border, subtle #FA4676-tinted focus ring
* Hover states: gentle lift or faint tint only

### Universal page scaffold

* Persistent left sidebar on desktop (collapsible on tablet/mobile)
* Page header with title, context, and primary action button
* Content in cards/lists/tables/forms depending on page purpose
* Optional right inspector panel for detail/edit workflows

### Apply this identity to the following page type:

\[INSERT PAGE TYPE HERE: e.g., Reservations list, Property editor form, Owner portal overview, Pricing rules, Messages inbox, Settings, Reports dashboard]

### Output requirements

1. Desktop layout + responsive tablet/mobile behaviours
2. Component specs for buttons, pills, cards, inputs, lists/tables
3. State designs: loading, empty, error, success, selected row/item

Do not invent a different style; keep the same soft, structured, low-noise console look.

***

# 11) Developer Handoff Notes (implementation-ready)

* Use CSS variables/tokens for colour, spacing, radius, shadow, typography.
* Build a reusable component library:
  * `Sidebar`, `PageHeader`, `Card`, `Button`, `Pill`, `BadgeCount`
  * `ListRow`, `Table`, `InspectorPanel`, `FormField`, `EmptyState`, `Toast`
* Keep layouts composable: `Scaffold + ContentPattern + Inspector(optional)`.

***

## 12) Token suggestions (CSS variables)

```css
:root{
  --uh-accent:#FA4676;

  --bg:#F7F8FA;
  --surface:#FFFFFF;
  --border:#E8EBF0;

  --text:#111827;
  --text-2:#6B7280;
  --icon:#9CA3AF;

  --radius-card:16px;
  --radius-input:12px;
  --radius-pill:999px;

  --space-1:8px;
  --space-2:16px;
  --space-3:24px;
  --space-4:32px;

  /* Shadow should be very subtle */
  --shadow-1:0 1px 2px rgba(17,24,39,0.06);
  --shadow-2:0 6px 18px rgba(17,24,39,0.08);
}
```
