---
name: PropTech Systematic Modern
colors:
  surface: '#f6fafe'
  surface-dim: '#d6dade'
  surface-bright: '#f6fafe'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f0f4f8'
  surface-container: '#eaeef2'
  surface-container-high: '#e4e9ed'
  surface-container-highest: '#dfe3e7'
  on-surface: '#171c1f'
  on-surface-variant: '#43474d'
  inverse-surface: '#2c3134'
  inverse-on-surface: '#edf1f5'
  outline: '#74777e'
  outline-variant: '#c3c6ce'
  surface-tint: '#49607c'
  primary: '#00152a'
  on-primary: '#ffffff'
  primary-container: '#102a43'
  on-primary-container: '#7a92b0'
  inverse-primary: '#b0c9e8'
  secondary: '#2d628e'
  on-secondary: '#ffffff'
  secondary-container: '#9dceff'
  on-secondary-container: '#205883'
  tertiary: '#001623'
  on-tertiary: '#ffffff'
  tertiary-container: '#002c41'
  on-tertiary-container: '#6296b8'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d1e4ff'
  primary-fixed-dim: '#b0c9e8'
  on-primary-fixed: '#011d35'
  on-primary-fixed-variant: '#314863'
  secondary-fixed: '#cee5ff'
  secondary-fixed-dim: '#9acbfc'
  on-secondary-fixed: '#001d33'
  on-secondary-fixed-variant: '#094a74'
  tertiary-fixed: '#c7e7ff'
  tertiary-fixed-dim: '#99cdf1'
  on-tertiary-fixed: '#001e2e'
  on-tertiary-fixed-variant: '#094c6b'
  background: '#f6fafe'
  on-background: '#171c1f'
  surface-variant: '#dfe3e7'
  surface-white: '#FFFFFF'
  status-success: '#198754'
  status-warning: '#D97706'
  status-danger: '#DC2626'
  border-subtle: '#E2E8F0'
  text-muted: '#627D98'
typography:
  display-lg:
    fontFamily: Manrope
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.02em
  display-lg-mobile:
    fontFamily: Manrope
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: Manrope
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.015em
  headline-lg-mobile:
    fontFamily: Manrope
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Manrope
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Manrope
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: 0em
  title-md:
    fontFamily: Manrope
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: 0em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
    letterSpacing: 0em
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
    letterSpacing: 0em
  body-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
    letterSpacing: 0.01em
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 14px
    letterSpacing: 0.04em
  numeric-data:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
    letterSpacing: -0.01em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-mobile: 1rem
  margin: 2rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style

This design system expresses rigorous organization, institutional trust, and friction-free operational control. Tailored for property, utility, and billing management, the UI abandons superficial decoration in favor of high-legibility typographic structure, calibrated data density, and clear visual hierarchies. 

The aesthetic is Modern Corporate with a minimalist architectural discipline:
- **Tone**: Systematic, authoritative, calm, and dependable.
- **Visual Weight**: Grounded dark structural anchoring against crisp light workspaces.
- **Restraint**: Functional chromatic distribution where color carries operational meaning rather than cosmetic styling.

## Colors

Color is applied systematically to reinforce hierarchy and operational state without causing cognitive fatigue:

- **Primary (`#102A43`)**: Reserved for core navigational scaffolding, sidebar panels, high-level headers, and high-impact structural anchors.
- **Secondary (`#245B86`)**: Directs user attention to primary interactive touchpoints, key operational CTAs, active filters, and interactive text links.
- **Tertiary (`#75A9CC`)**: Applied to non-critical highlights, subtle selection indicators, tertiary badges, and secondary informational accents.
- **Neutral Canvas (`#F1F5F9`)**: Forms the stable foundation for viewports and layout sections, establishing a cool backdrop that elevates content modules.
- **Surfaces (`#FFFFFF`)**: Strictly designated for interactive cards, data tables, sheets, and structured form containers.
- **Operational Statuses**: Saturated colors (`status-success`, `status-warning`, `status-danger`) are isolated exclusively to indicators, ledger alerts, validation feedback, and lifecycle tags.

## Typography

The typographic strategy pairs `Manrope` for brand presence and architecture with `Inter` for operational interfaces:

- **Manrope**: Provides structured, geometric balance for section headers, metric figures, and card titles. It conveys modern stability without decorative idiosyncrasies.
- **Inter**: Optimizes clarity across micro-data, inputs, table entries, and navigation labels. Tabular numeric variants should be enabled for financial sums, consumption stats, and dates.
- **Hierarchy Enforcement**: Maintain strict line-height spacing; compact body copy (`body-md`) governs tables and inputs, whereas spacious titles provide immediate visual scanning across property and tenant records.

## Layout & Spacing

The layout is built upon a balanced fluid responsive grid anchored by persistent navigational structural elements:

- **Desktop Layout**: 12-column grid system with a fixed/collapsible navigation sidebar (width: 260px expanded, 72px collapsed). Gutters are maintained at 1.5rem (`24px`) with a minimum canvas margin of 2rem (`32px`).
- **Tablet Layout (768px - 1024px)**: 8-column layout. The sidebar switches to compact icon mode or overlay drawer. Margin shrinks to 1.5rem.
- **Mobile Layout (<768px)**: 4-column layout with a single-tier top app bar and slide-out navigation sheet. Outer margins drop to 1rem (`16px`).
- **Spacing Rhythm**: Generous perimeter whitespace (`space-xl` between logical administrative blocks) contrasts deliberately with tight, disciplined interior groupings (`space-xs` to `space-sm`) inside forms and table rows. Infinite scrolling is strictly avoided; bounded pagination and defined data containers guarantee full layout control.

## Elevation & Depth

This system avoids heavy shadows, skew, or high-blur glassmorphic treatments. Depth is communicated almost exclusively through tonal layering, precise surfaces, and low-contrast borders:

- **Layer Tier 1 (Background Canvas)**: Hex `#F1F5F9` establishes a cool, glare-free baseline.
- **Layer Tier 2 (Content Modules & Cards)**: Hex `#FFFFFF` flat surfaces nested with a subtle boundary outline (`1px solid #E2E8F0`). Flat elevation by default.
- **Layer Tier 3 (Interactive Floating Panels & Flyouts)**: Dropdowns, date pickers, and tooltips utilize minimal, diffused ambient shadowing: `0 4px 12px -2px rgba(16, 42, 67, 0.08), 0 2px 4px -1px rgba(16, 42, 67, 0.04)`.
- **Layer Tier 4 (Action Dialogs & Modals)**: Constrained strictly to critical workflows and confirmation prompts. Uses `#102A43` with 40% alpha backdrop fill, with modals elevated via `0 20px 25px -5px rgba(16, 42, 67, 0.12), 0 8px 10px -6px rgba(16, 42, 67, 0.08)`.

## Shapes

The geometric signature uses moderately rounded contours to soften technical administrative forms while preserving a professional, clean edge:

- **Containers & Cards (`rounded-lg` / `rounded-xl`)**: Primary content cards, data tables, and modal frames use 1rem (`16px`) to 1.5rem (`24px`) radii, cleanly separating content blocks from the `#F1F5F9` canvas.
- **Inputs & Action Buttons (`rounded-md` / 0.5rem)**: Standardized to 8px radii, balancing usability with clean data-entry alignment.
- **Badges & Status Tags**: Use either 6px subtle radii or full pill curves (`9999px`) for contextual metric chips, ensuring visual distinction from clickable operational controls.

## Components

### Buttons
- **Primary Action**: Solid `#245B86` background, `#FFFFFF` text, 8px border radius, 0.5rem 1rem padding. Hover: `#102A43`. Focus: `#245B86` ring with 2px offset.
- **Secondary Action**: Transparent background, 1px `#E2E8F0` border, `#102A43` text. Hover: `#F1F5F9` background, `#102A43` border.
- **Destructive Action**: Transparent or `#FEE2E2` background with `#DC2626` text and border for explicit soft/hard deletions.

### Input Fields & Controls
- **Text Inputs & Selects**: Height 40px, `#FFFFFF` surface, 1px `#E2E8F0` border, 8px border radius. Placeholder in `#627D98`. Active focus triggers a 1px border and 2px halo in `#245B86`.
- **Checkboxes & Radios**: 18px bounding box, 1px `#CBD5E1` border. Checked state transitions instantly to `#245B86` with crisp white iconography.

### Status Tags & Chips
- **Semantic Badges**: Compact padding (2px 8px), `label-sm` typographic styling, soft tinted background with high-contrast text:
  - *Active / Paid*: Background `#DEF7EC`, Text `#03543F`.
  - *Pending / Review*: Background `#FEF3C7`, Text `#92400E`.
  - *Overdue / Terminated*: Background `#FDE8E8`, Text `#9B1C1C`.
  - *Informational*: Background `#E1EFFE`, Text `#1E429F`.

### Data Tables
- **Header**: Height 44px, text set in `label-md` with `#627D98` coloring, uppercase tracking, bottom border `1px solid #E2E8F0`.
- **Rows**: Alternating hover highlights (`#F8FAFC`), 52px row height for optimal data scanning, clear horizontal separators. Numeric values align to the right with fixed-width tabular formatting.

### Cards & Metrics
- **Metric KPI Widgets**: `#FFFFFF` background, `1px solid #E2E8F0` border, 16px corner radius, generous 24px inner padding. Display titles using `title-md` and core figures using `headline-lg` in `#102A43`.

### Icons & Imagery
- **Iconography**: 20px and 24px stroke-based linear icons (1.5px to 2px stroke width). Uniformly colored in `#627D98` for static indicators and `#245B86` or `#102A43` for active states.