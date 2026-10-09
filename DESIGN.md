---
name: Charis Creations
description: A near-black documentary stage where real Ugandan ceremonies carry the page and one hot orange marks the next step.
colors:
  ink-black: "#0a0a0a"
  graphite: "#1a1a1a"
  bone: "#f4f4f2"
  bone-muted: "rgba(244, 244, 242, 0.72)"
  bone-faint: "rgba(244, 244, 242, 0.56)"
  hairline: "rgba(244, 244, 242, 0.12)"
  hairline-strong: "rgba(244, 244, 242, 0.28)"
  primary: "#f16623"
  primary-hover: "#ff7d3d"
  on-primary: "#0a0a0a"
  whatsapp-green: "#25d366"
typography:
  display:
    fontFamily: "Barlow Condensed, Arial Narrow, sans-serif"
    fontSize: "clamp(2.6rem, 6.4vw, 5rem)"
    fontWeight: 800
    lineHeight: 0.96
    letterSpacing: "0.004em"
  headline:
    fontFamily: "Barlow Condensed, Arial Narrow, sans-serif"
    fontSize: "clamp(2.2rem, 5vw, 4rem)"
    fontWeight: 800
    lineHeight: 0.98
    letterSpacing: "0.004em"
  title:
    fontFamily: "Barlow Condensed, Arial Narrow, sans-serif"
    fontSize: "clamp(1.35rem, 2.1vw, 1.65rem)"
    fontWeight: 700
    lineHeight: 1.08
    letterSpacing: "0.01em"
  body:
    fontFamily: "Barlow, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "1.0625rem"
    fontWeight: 400
    lineHeight: 1.65
  lede:
    fontFamily: "Barlow, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "clamp(1.08rem, 1.5vw, 1.25rem)"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "Barlow Condensed, Arial Narrow, sans-serif"
    fontSize: "0.82rem"
    fontWeight: 600
    lineHeight: 1.3
    letterSpacing: "0.22em"
  button:
    fontFamily: "Barlow Condensed, Arial Narrow, sans-serif"
    fontSize: "0.95rem"
    fontWeight: 700
    lineHeight: 1
    letterSpacing: "0.13em"
rounded:
  none: "0px"
  device: "30px"
  circle: "50%"
spacing:
  gutter: "clamp(20px, 5vw, 56px)"
  section: "clamp(88px, 12vw, 168px)"
  section-tight: "clamp(56px, 8vw, 96px)"
  wrap: "1280px"
  nav-height: "68px"
  card: "clamp(24px, 2.6vw, 34px)"
  photo-gap: "3px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.button}"
    rounded: "{rounded.none}"
    padding: "0 28px"
    height: "52px"
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
    textColor: "{colors.on-primary}"
  button-outline:
    backgroundColor: "rgba(10, 10, 10, 0.25)"
    textColor: "{colors.bone}"
    typography: "{typography.button}"
    rounded: "{rounded.none}"
    padding: "0 28px"
    height: "52px"
  button-outline-hover:
    backgroundColor: "rgba(244, 244, 242, 0.06)"
    textColor: "{colors.bone}"
  book-toggle:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    rounded: "{rounded.none}"
    padding: "0 18px"
    height: "42px"
  nav-link:
    textColor: "{colors.bone-muted}"
    padding: "10px 0"
  nav-link-active:
    textColor: "{colors.bone}"
  package-card:
    backgroundColor: "{colors.graphite}"
    textColor: "{colors.bone}"
    rounded: "{rounded.none}"
    padding: "{spacing.card}"
  service-tile:
    backgroundColor: "{colors.graphite}"
    textColor: "{colors.bone}"
    rounded: "{rounded.none}"
    padding: "clamp(22px, 2.4vw, 32px)"
  social-icon:
    textColor: "{colors.bone}"
    rounded: "{rounded.none}"
    size: "44px"
  whatsapp-float:
    backgroundColor: "{colors.whatsapp-green}"
    textColor: "{colors.ink-black}"
    rounded: "{rounded.circle}"
    size: "58px"
---

# Design System: Charis Creations

## Overview

**Creative North Star: "The Darkroom Wall"**

The site is a black wall in a darkroom with real prints pinned to it. Charis disappears into moments, so the interface does the same: near-black planes recede, photographs of real Ugandan weddings, introductions and portraits carry every page, and type speaks in short, condensed, uppercase lines like captions stencilled under the prints. Photos arrive in black and white and develop into colour as they scroll into view; that one motion is the system's signature.

Density is generous and editorial. Sections breathe on a tall vertical rhythm, photography is cropped edge to edge in sharp rectangles, and structure is drawn with hairline rules rather than boxes or shadows. Colour is almost absent by design: a single logo orange is spent only where the visitor acts (Check Your Date, Book Now, Enquire) and on a handful of small marks, so the next step is always the brightest thing on screen.

The system refuses the agency-template hero with a showreel button, soft rounded cards, coloured side stripes, invented kickers over every heading, and decorative motion. It is one dark theme, phone first, built to load fast inside WhatsApp's browser.

**Key Characteristics:**
- Two-tone black ground (#0A0A0A page, #1A1A1A raised) with warm bone text in three opacities.
- One accent, logo orange, reserved for primary actions and small marks, always with dark text on it.
- Barlow Condensed 800 uppercase headlines; Barlow body at 17px.
- Sharp rectangles everywhere; hairline rules for structure; tonal layering instead of shadows.
- "Develop" motion: grayscale to colour on scroll, quiet fade-up reveals, a slow drifting photo wall in the home hero; all of it off under reduced motion.

## Colors

A monochrome darkroom of two blacks and one warm bone, lit by a single hot orange.

### Primary
- **Logo Orange** (primary): the brand's own orange from the logo mark. Fills primary buttons (Check Your Date, Enquire Now on the featured package), the Book Now toggle, focus rings and text selection. As small accents it colours the step numerals in the client journey and process lists, the arrow links, the 7px dash bullets in package lists, the 2px top rule on a featured package, the active-page underline in the nav, the FAQ plus icon, and the brand wordmark's second line.
- **Ember Orange** (primary-hover): a lighter, hotter orange used only as the hover state of orange buttons on pointer devices.
- **Ink on Orange** (on-primary): the page black reused as text on orange. Dark text holds about 7:1 on the orange; white would fail.

### Neutral
- **Ink Black** (ink-black): the page ground, the hero scrim tint, the mobile menu sheet, and the browser theme colour.
- **Graphite** (graphite): the one raised surface. Alternating raised sections, package and quote cards, the seasonal banner, the Book Now dropdown, the footer, and the empty frame behind every photo while it loads.
- **Bone** (bone): headlines, active nav, primary text. Slightly warm so white never glares on black.
- **Bone Muted** (bone-muted): supporting copy, ledes, nav at rest, package descriptions (about 9.9:1 on ink black).
- **Bone Faint** (bone-faint): labels, captions, meta lines, footer column heads, legal line (about 6.3:1).
- **Hairline** (hairline): every structural rule: section dividers, list rows, journey and process column dividers, card outlines, header bottom edge.
- **Hairline Strong** (hairline-strong): outline button borders, the featured package outline, the active sub-nav tab, lightbox controls.

### Functional exception
- **WhatsApp Green** (whatsapp-green): only the floating WhatsApp button, because the visitor must recognise WhatsApp at a glance. It never appears anywhere else.

### Named Rules
**The Two Blacks Rule.** Backgrounds are #0A0A0A or #1A1A1A and nothing else. Depth comes from swapping between the two, never from a third grey, a gradient panel or a tinted surface. Photos and their scrims are the only other things that sit behind text.

**The One Fire Rule.** Orange appears only on primary actions and small accents. If a screen has more than one orange-filled element in view besides the Book Now toggle, one of them is wrong. Text on orange is always ink black.

## Typography

**Display Font:** Barlow Condensed (with Arial Narrow, sans-serif), self-hosted at 600, 700 and 800
**Body Font:** Barlow (with system-ui, -apple-system, Segoe UI, sans-serif), self-hosted at 400, 500 and 600

**Character:** A tall, tight industrial condensed set in heavy uppercase, like a film slate or a stencilled caption, over a plain, open grotesque that reads easily at length on a phone. Same family, two widths: one voice shouting, one voice explaining.

### Hierarchy
- **Display** (800, clamp 2.6rem to 5rem, line-height 0.96, uppercase, balanced): page and hero headlines. One per page.
- **Headline** (800, clamp 2.2rem to 4rem, line-height 0.98, uppercase): section headings such as Featured Work and Plan With Confidence. Smaller variants appear for the Trusted By band and policy prose.
- **Title** (700, clamp 1.35rem to 1.65rem, line-height 1.08, uppercase): card, tile, package, person and step names. The same 700 condensed uppercase is reused at row scale for FAQ questions, starting-point rows, add-on names and guide links.
- **Body** (400, 17px, line-height 1.65): all running copy, held to 64ch; supporting copy sits in Bone Muted. Policy prose runs to 72ch.
- **Lede** (400, clamp 1.08rem to 1.25rem, line-height 1.6): the first paragraph under a headline, held to 58ch (46ch in the home hero).
- **Label** (600 condensed, 0.78 to 0.9rem, tracking 0.15em to 0.24em, uppercase): nav links, sub-nav tabs, footer column heads, package group heads and tags, fact terms, the hero motto, the lightbox counter.
- **Button** (700 condensed, 0.95rem, tracking 0.13em, uppercase): button and arrow-link text.
- **Numerals** (800 condensed, 2.4rem to 3.2rem, tabular): journey and process step numbers in orange; prices at 1.9rem in bone.

### Named Rules
**The Shout and Explain Rule.** Everything that names something (headings, buttons, nav, labels) is Barlow Condensed uppercase; everything that explains is Barlow in sentence case. Never set a paragraph in condensed, and never set a heading in regular Barlow.

**The Phone Floor Rule.** Body text is never below 16px on phones (it ships at 17px). Captions and meta may drop to about 15px; only uppercase condensed labels and the legal line go smaller.

**The Three Eyebrows Rule.** Only the three eyebrows the handover pins exist: Home ("Charis Creations | Kampala | Since 2013"), Projects ("Selected Work") and Our Team ("The People Behind the Work"). Their style is Label in Bone Faint. An empty eyebrow field renders nothing. No other heading gets a label stacked above it.

## Layout

A single 1280px wrap with a fluid side gutter (20px on phones rising to 56px) holds every page. Sections run on a tall vertical rhythm (88px to 168px of padding, or 56px to 96px for tight bands) and alternate between Ink Black and Graphite to separate chapters without rules.

Inside sections the grammar is asymmetric two-column splits (photo against words, roughly 1:1 to 1.3:0.9) that collapse to one column between 860px and 900px, and photo grids with hairline-thin gutters. Featured Work is a 12-column editorial grid: one large 7-column lead image spanning two rows, two 5-column landscape images beside it, then three 4-column portraits; it falls to two columns at 900px and one at 560px. Service tiles and guide links sit on a 3px gap so the black ground reads as a seam between prints.

Lists of steps, rows and facts are drawn as ruled tables: a hairline across the top, hairline rows or hairline column dividers, no boxes. The client journey runs four columns, then two at 900px, then one at 560px where the numeral moves beside its text.

The header is sticky at 68px. Below 960px the nav becomes a full-height Ink Black sheet with large 1.55rem condensed links separated by hairlines and a full-width Book Now. The Services page adds a second sticky strip of horizontally scrolling tabs under the header. Main breakpoints observed: 560px, 700px, 860px to 900px, 960px, 1080px.

On phones every hero button stacks full width, and photos switch to 4:5 or 4:3 crops so faces stay large.

## Elevation & Depth

The system is flat. Depth is tonal: Ink Black is the floor, Graphite is one step up, and photographs under dark scrims are the deepest layer. Shadows exist only on things that genuinely float above the page, and they are soft and black, never coloured.

### Shadow Vocabulary
- **Float** (`box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55)`): the Book Now dropdown on desktop.
- **Device** (`box-shadow: 0 28px 60px rgba(0, 0, 0, 0.5)`): the phone frames showing CharisOS screens in the client journey.
- **Action** (`box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5)`): the floating WhatsApp button.
- **Headline lift** (`text-shadow: 0 2px 32px rgba(0, 0, 0, 0.5)`): the home hero headline over the photo wall.

Glass appears twice: the sticky header (Ink Black at 82% with a 16px blur) and the Services sub-nav (92% with a 12px blur). Both fall back to solid Ink Black when the visitor prefers reduced transparency.

### Named Rules
**The Scrim, Not Shadow Rule.** Text over photographs is made legible with dark scrims (a 55% Ink Black wash on the home wall, a bottom-up gradient to 95% on service tiles), never with boxes behind the words.

## Shapes

Every surface is a sharp rectangle: buttons, cards, photos, inputs, menu panels, social icons, lightbox controls, sub-nav tabs (0px). Photos are cropped to a small set of fixed ratios (4:5 portrait, 4:3 and 16:10 landscape, 3:2 for category cards, 16:9 for film) and fill their frame with object-fit cover. Structure is drawn with 1px hairlines; the only heavier line is the 2px orange underline on the current nav item and the 2px orange rule along the top edge of a featured package.

Two shapes are allowed to be round because they depict real objects: the phone frames in the client journey (30px outer, 22px screen) are drawings of a phone, and the WhatsApp button is a circle because that is WhatsApp's mark. Nothing else is rounded.

### Named Rules
**The Print Edge Rule.** Corners are square. A radius anywhere other than a depicted device or the WhatsApp circle is a defect.

## Components

### Buttons
Blunt, confident and loud in type, quiet in form.
- **Shape:** square corners (0px), 52px tall, 28px side padding, 1.5px border slot.
- **Primary:** Logo Orange fill, ink-black condensed uppercase label at 0.95rem with 0.13em tracking. Used for the one main action in a view.
- **Outline:** transparent-black fill with a Hairline Strong border and Bone text. The secondary action beside a primary (See Our Work) and the default Enquire Now on non-featured packages.
- **Hover / Focus:** on pointer devices only, primary lightens to Ember Orange and outline brightens its border to Bone with a faint bone wash (180ms). Press scales to 0.97 (140ms). Focus is a 2px orange outline offset 3px.
- **Arrow link:** orange condensed uppercase text with a 16px arrow that slides 4px right on hover. Used for "See how to enter", "View project" and similar tertiary steps.

### Book Now menu
The header's only orange element and the site's master call to action. A 42px orange toggle with a caret opens a Graphite panel (Float shadow, hairline border) listing three routes, each a condensed uppercase title over a Bone Faint one-liner: Studio Session, Wedding or Introduction, Corporate or Event. Arrow Down opens and focuses the first item; Escape closes and returns focus. On phones it becomes a full-width 56px bar whose menu expands inline.

### Cards / Containers
- **Corner Style:** square (0px).
- **Background:** Graphite on Ink Black sections; Ink Black when the section itself is raised.
- **Shadow Strategy:** none (see Elevation & Depth).
- **Border:** 1px Hairline; the featured package uses Hairline Strong plus a 2px orange inset rule along its top edge and an orange tag ("Most Booked", "Most Popular").
- **Internal Padding:** 24px to 34px.
- **Package anatomy:** tag label, title, muted description, price in 800 condensed with a small "From" label, then hairline-topped groups (Photography, Videography, Experience) whose items carry a 7px by 2px orange dash, then a button aligned left.

### Service tiles
Full-bleed photo tiles (340px to 460px tall) in a 3-up grid on a 3px seam. A bottom-up Ink Black gradient carries a condensed title and a short line of copy in bone at 80%. The photo eases up to 1.04 scale on hover.

### Ruled lists
The house pattern for steps, facts, starting prices, add-ons, FAQs and beauty services: a hairline on top, hairline rows or column dividers, condensed uppercase names on the left, Bone Muted detail or tabular figures on the right. FAQ rows use an orange plus that rotates 45 degrees to a cross when open; only one answer is open at a time.

### Navigation
- **Desktop:** condensed uppercase labels at 0.86rem with 0.17em tracking in Bone Muted; hover and current page go to Bone, and the current page gets a 2px orange underline.
- **Header:** sticky 68px glass bar with a hairline bottom edge, logo mark plus a two-line wordmark ("Charis" in bone, "Creations" in orange).
- **Mobile (below 960px):** a 48px menu button opens a full-height Ink Black sheet; links grow to 1.55rem with hairline separators and the current page turns orange.
- **Services sub-nav:** a sticky, horizontally scrolling strip of label-style tabs; the section in view gets a Hairline Strong outline and scrolls itself into the strip.

### Hero photo wall (signature)
The home first viewport: full-height wall of grayscale photos in three rows (two on phones) that drift sideways in opposite directions over 56s to 68s, under a 55% Ink Black scrim with a soft centre vignette and a fade to black at the foot. Centered on top: the pinned eyebrow, a two-line display headline, a lede, the primary and outline buttons, and the owned motto as a wide-tracked label. Copy rises in with 70ms staggers. With reduced motion the wall is still and copy simply appears.

### Develop (signature motion)
Photos marked to develop sit at grayscale and 90% brightness until they are 12% into the viewport, then resolve to full colour over 1400ms on the house ease-out. Content blocks marked to reveal fade up 16px over 900ms. Both are skipped entirely under reduced motion, and everything is visible without JavaScript.

### Floating WhatsApp
A 58px WhatsApp-green circle with an ink-black glyph, fixed bottom right inside the safe area, carrying the Action shadow. It is the only always-visible route to a conversation.

### Embeds
CharisOS enquiry and testimonial iframes sit borderless on the page. If one fails to load, its section hides itself or swaps to a Graphite fallback panel with a hairline border, a short line of explanation, and a primary link to the standalone form beside an outline WhatsApp button. Nothing is ever placeholdered.

## Do's and Don'ts

### Do:
- **Do** keep every background #0A0A0A or #1A1A1A, and alternate them to separate sections.
- **Do** reserve orange (#F16623) for primary buttons, the Book Now toggle and small accents (step numerals, arrow links, dash bullets, the active-nav underline, focus rings), with ink-black text on any orange fill.
- **Do** set headings, buttons, nav and labels in Barlow Condensed uppercase (800 for headlines, 700 for titles and buttons, 600 for labels) and running text in Barlow at 17px, never under 16px on phones.
- **Do** keep every corner square and draw structure with 1px hairlines at 12% or 28% bone.
- **Do** let photographs fill their frames edge to edge at fixed ratios, and put text over them only on a dark scrim.
- **Do** gate every animation behind prefers-reduced-motion: no-preference, and keep content visible without JavaScript.
- **Do** hide any block whose content is missing or unconfirmed (an empty eyebrow, a failed embed, an unconfirmed price) instead of showing a placeholder.
- **Do** write copy with commas, full stops and pipes; keep the owned lines verbatim.

### Don't:
- **Don't** introduce a third background grey, a gradient panel or a tinted surface.
- **Don't** use orange for headings, large fills, section backgrounds or decoration, and don't put white text on orange.
- **Don't** add eyebrows or kicker labels above headings beyond the three the handover pins.
- **Don't** use coloured border-left (or border-right) accent stripes on cards, quotes or list items. Neutral hairline column dividers in ruled lists are fine.
- **Don't** round corners, except the depicted phone frame and the WhatsApp circle.
- **Don't** use em dashes or long dashes in copy.
- **Don't** add pins, custom cursors, rotating clocks, flip cards, oversized scrolling text or any motion that ignores reduced-motion.
- **Don't** add coloured or hard offset shadows; the only shadows are the soft black Float, Device and Action shadows.
