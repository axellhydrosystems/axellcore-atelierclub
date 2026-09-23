#!/usr/bin/env python3
"""Generates content/noclass-nav-hero.html — a proof-of-concept slice of the
/atelier-noclass experiment (nav + hero, the two hardest sections: fixed
positioning, scroll-driven state, fluid typography) rebuilt with ZERO
`aac-`-prefixed CSS classes. Every visual property is a native core-block
style attribute (color/typography/spacing/border/position) instead.

Two block-supports processing paths matter here (confirmed by reading
wp-includes/block-supports/*.php directly):
  - color/typography/spacing/border have NO render_block hook at all — they
    are purely editor-side (JS save()), meaning whatever inline style="..."
    we hand-author IS exactly what the frontend renders, unmodified. We
    don't need to byte-match WordPress's own internal formatting — just
    write valid CSS achieving the intended look, and keep the JSON attrs
    reasonably faithful so the editor's own recompute doesn't flag it.
  - position DOES hook render_block (wp_render_position_support()) — WP
    generates the actual CSS (a `.wp-container-N{position:sticky;...}`
    rule + auto class) from the JSON attrs at render time, regardless of
    what's in the stored HTML. Requires settings.position.sticky=true,
    enabled via Template_Loader::enable_position_sticky() (a
    wp_theme_json_data_theme filter — no theme file touched).

Known hard limits found building this (no native block-attribute
equivalent exists for any of these — confirmed by reading every relevant
wp-includes/block-supports/*.php file, not guessed):
  - CSS counters (chapter numbering, decimal-leading-zero benefit numbers)
  - ::before/::after generated content (decorative eyebrow line, arrow
    icons, notice-pill glyphs via CSS, mystery-tier diagonal stripe)
  - backdrop-filter (nav's frosted-glass scrolled state)
  - CSS transitions (nav's smooth scroll-state change, reveal-on-scroll's
    fade/slide) — no "motion" block support exists
  - Multi-layer/positioned background gradients (hero overlay stacks two
    gradients; core/group's single `style.color.gradient` only takes one)
  - Fluid typography via clamp() driven by the *block's own fontSize
    control* — the Custom Size field takes one fixed value, not a range.
    (We DO use raw clamp() strings directly in hand-authored inline
    style="" below — valid CSS the browser executes fine — but that's
    bypassing the editor UI entirely, not "the fontSize attribute doing
    it": a real editor user could never produce this through the panel.)
"""

import json
import os

OUT = []


def esc_attrs(d):
    return json.dumps(d, ensure_ascii=False, separators=(",", ":"))


def style_to_css(style):
    """Flatten a WP block `style` attribute object into an inline CSS
    string — the same shape (color/typography/spacing/border/position)
    useBlockProps.save() would serialize, hand-rolled since we're not
    running JS. Only the properties this file actually uses are handled."""
    decls = []
    color = style.get("color", {})
    if "text" in color:
        decls.append(f'color:{color["text"]}')
    if "background" in color:
        decls.append(f'background-color:{color["background"]}')
    if "gradient" in color:
        decls.append(f'background:{color["gradient"]}')

    typo = style.get("typography", {})
    if "fontSize" in typo:
        decls.append(f'font-size:{typo["fontSize"]}')
    if "fontFamily" in typo:
        decls.append(f'font-family:{typo["fontFamily"]}')
    if "fontWeight" in typo:
        decls.append(f'font-weight:{typo["fontWeight"]}')
    if "fontStyle" in typo:
        decls.append(f'font-style:{typo["fontStyle"]}')
    if "lineHeight" in typo:
        decls.append(f'line-height:{typo["lineHeight"]}')
    if "letterSpacing" in typo:
        decls.append(f'letter-spacing:{typo["letterSpacing"]}')
    if "textTransform" in typo:
        decls.append(f'text-transform:{typo["textTransform"]}')

    spacing = style.get("spacing", {})
    if "padding" in spacing:
        p = spacing["padding"]
        if isinstance(p, str):
            decls.append(f'padding:{p}')
        else:
            for side in ("top", "right", "bottom", "left"):
                if side in p:
                    decls.append(f'padding-{side}:{p[side]}')
    if "margin" in spacing:
        m = spacing["margin"]
        if isinstance(m, str):
            decls.append(f'margin:{m}')
        else:
            for side in ("top", "right", "bottom", "left"):
                if side in m:
                    decls.append(f'margin-{side}:{m[side]}')
    if "blockGap" in spacing:
        decls.append(f'gap:{spacing["blockGap"]}')

    border = style.get("border", {})
    if "radius" in border:
        decls.append(f'border-radius:{border["radius"]}')
    if "width" in border and "color" in border:
        decls.append(f'border:{border["width"]} solid {border["color"]}')

    dimensions = style.get("dimensions", {})
    if "minHeight" in dimensions:
        decls.append(f'min-height:{dimensions["minHeight"]}')

    elements = style.get("_extra", "")
    css = ";".join(decls)
    if elements:
        css = (css + ";" + elements) if css else elements
    return css


def layout_to_css(layout):
    """The `layout` block attribute's actual CSS (display:flex + friends)
    is generated by WordPress at render time from theme.json global styles
    (WP_Theme_JSON's layout definitions) — NOT from wp-block-library or the
    per-block block-supports stylesheet. Confirmed by testing: with global
    styles stripped (required to keep the active theme's own presets from
    bleeding in — a constraint from earlier in this project, unrelated to
    this experiment), `layout` JSON attrs were present and WP even added
    the right classes/override rules (justify-content, flex-wrap, gap), but
    the foundational `.is-layout-flex{display:flex}` rule was simply
    missing from every loaded stylesheet, and the container computed
    `display:block`. So the `layout` attribute is kept in the JSON (it's
    still what a real editor user would set via the panel), but the actual
    `display` value is additionally hand-supplied via inline style here —
    a real limit of native block styling in a page this isolated, not a
    bug in our markup."""
    if not layout:
        return ""
    t = layout.get("type")
    if t == "flex":
        decls = ["display:flex"]
        if layout.get("orientation") == "vertical":
            decls.append("flex-direction:column")
        if "justifyContent" in layout:
            decls.append(f'justify-content:{layout["justifyContent"]}')
        if "alignItems" in layout:
            decls.append(f'align-items:{layout["alignItems"]}')
        if layout.get("flexWrap") == "nowrap":
            decls.append("flex-wrap:nowrap")
        return ";".join(decls)
    if t == "grid":
        decls = ["display:grid"]
        if "columnCount" in layout:
            decls.append(f'grid-template-columns:repeat({layout["columnCount"]},1fr)')
        return ";".join(decls)
    return ""


def group(inner_html_fn, style=None, layout=None, tag_name=None):
    attrs = {}
    if style:
        attrs["style"] = style
    if layout:
        attrs["layout"] = layout
    if tag_name:
        attrs["tagName"] = tag_name
    tag = tag_name or "div"
    css = style_to_css(style or {})
    layout_css = layout_to_css(layout)
    if layout_css:
        css = (css + ";" + layout_css) if css else layout_css
    style_attr = f' style="{css}"' if css else ""
    OUT.append(f'<!-- wp:group {esc_attrs(attrs) if attrs else "{}"} -->')
    OUT.append(f'<{tag} class="wp-block-group"{style_attr}>')
    inner_html_fn()
    OUT.append(f'</{tag}>')
    OUT.append('<!-- /wp:group -->')


def columns(cols_fn, style=None):
    # core/columns' own base `display:flex` lives in its per-block split
    # stylesheet (wp-includes/blocks/columns/style.css), which — like
    # group's `layout`-generated CSS — isn't loaded on this isolated page;
    # supplied directly for the same reason as layout_to_css() above.
    attrs = {"style": style} if style else {}
    css = style_to_css(style or {})
    css = (css + ";display:flex") if css else "display:flex"
    style_attr = f' style="{css}"'
    OUT.append(f'<!-- wp:columns {esc_attrs(attrs) if attrs else "{}"} -->')
    OUT.append(f'<div class="wp-block-columns"{style_attr}>')
    cols_fn()
    OUT.append('</div>')
    OUT.append('<!-- /wp:columns -->')


def column(inner_fn, style=None):
    attrs = {"style": style} if style else {}
    style_attr = f' style="{style_to_css(style)}"' if style else ""
    OUT.append(f'<!-- wp:column {esc_attrs(attrs) if attrs else "{}"} -->')
    OUT.append(f'<div class="wp-block-column"{style_attr}>')
    inner_fn()
    OUT.append('</div>')
    OUT.append('<!-- /wp:column -->')


def heading(level, text, style=None):
    attrs = {"level": level}
    if style:
        attrs["style"] = style
    style_attr = f' style="{style_to_css(style)}"' if style else ""
    OUT.append(f'<!-- wp:heading {esc_attrs(attrs)} -->')
    OUT.append(f'<h{level} class="wp-block-heading"{style_attr}>{text}</h{level}>')
    OUT.append('<!-- /wp:heading -->')


def paragraph(html, style=None):
    attrs = {"style": style} if style else {}
    style_attr = f' style="{style_to_css(style)}"' if style else ""
    OUT.append(f'<!-- wp:paragraph {esc_attrs(attrs) if attrs else "{}"} -->')
    OUT.append(f'<p class="wp-block-paragraph"{style_attr}>{html}</p>')
    OUT.append('<!-- /wp:paragraph -->')


def buttons_block(inner_fn, layout=None):
    attrs = {"layout": layout} if layout else {}
    OUT.append(f'<!-- wp:buttons {esc_attrs(attrs) if attrs else "{}"} -->')
    OUT.append('<div class="wp-block-buttons">')
    inner_fn()
    OUT.append('</div>')
    OUT.append('<!-- /wp:buttons -->')


def button(text, style=None, url="#", tag_name="a"):
    attrs = {}
    if tag_name != "a":
        attrs["tagName"] = tag_name
    if style:
        attrs["style"] = style
    if url and tag_name == "a":
        attrs["url"] = url
    style_attr = f' style="{style_to_css(style)}"' if style else ""
    OUT.append(f'<!-- wp:button {esc_attrs(attrs) if attrs else "{}"} -->')
    if tag_name == "a":
        OUT.append(f'<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="{url}"{style_attr}>{text}</a></div>')
    else:
        OUT.append(f'<div class="wp-block-button"><{tag_name} class="wp-block-button__link wp-element-button"{style_attr}>{text}</{tag_name}></div>')
    OUT.append('<!-- /wp:button -->')


# ============================================================
# TOKENS (from atelier-axell-club.html :root — hand-carried, since this
# experiment has no tokens.css to reference custom properties from)
# ============================================================
INK = "#0B0E12"
IVORY = "#EFECE4"
BRONZE = "#B4996A"
BRONZE_2 = "#C6AC7E"
BRONZE_3 = "#D9C199"
SERIF = "'Cormorant Garamond', 'Times New Roman', Georgia, serif"
SANS = "'Inter', -apple-system, 'Segoe UI', system-ui, sans-serif"


# ============================================================
# NAV — core/group, native `position:sticky` (WP renders it as `fixed`-
# equivalent CSS since it's the first element on the page: sticky to a
# container spanning the full page height reads identically to fixed).
# HARD LIMIT: no backdrop-filter, no transition — no block attribute for
# either exists. The scrolled/frosted-glass state is simply not
# reproduced in this variant; nav stays in its resting look throughout.
# ============================================================
def nav_section():
    def inner():
        def logo():
            paragraph('ATELIER AXELL', style={
                "typography": {"fontFamily": SERIF, "fontSize": "15px", "letterSpacing": "0.4em", "textTransform": "uppercase"},
                "color": {"text": IVORY},
                "spacing": {"padding": {"top": "8px", "bottom": "6px"}},
                "_extra": f"border-top:1px solid rgba(239,236,228,.35);border-bottom:1px solid rgba(239,236,228,.35);text-align:center;margin:0",
            })
            paragraph('THE AXELL WORLD', style={
                "typography": {"fontFamily": SANS, "fontSize": "8.5px", "letterSpacing": "0.42em", "textTransform": "uppercase"},
                "color": {"text": "rgba(239,236,228,.55)"},
                "spacing": {"margin": {"top": "6px", "bottom": "0"}},
                "_extra": "text-align:center",
            })
        group(logo, layout={"type": "flex", "orientation": "vertical", "flexWrap": "nowrap"})

        def links():
            for href, label in [('#convite', 'Convite'), ('#placa', 'A Placa'), ('#jornada', 'Como Entrar'), ('#niveis', 'Níveis'), ('#beneficios', 'Benefícios')]:
                paragraph(f'<a href="{href}" style="color:inherit;text-decoration:none">{label}</a>', style={
                    "typography": {"fontFamily": SANS, "fontSize": "11px", "letterSpacing": "0.24em", "textTransform": "uppercase"},
                    "color": {"text": "rgba(239,236,228,.68)"},
                    "spacing": {"margin": {"top": "0", "bottom": "0"}},
                })

            def cta():
                button('Solicitar adesão', url='#adesao', style={
                    "typography": {"fontFamily": SANS, "fontSize": "11.5px", "fontWeight": "500", "letterSpacing": "0.26em", "textTransform": "uppercase"},
                    "color": {"text": INK, "background": BRONZE},
                    "spacing": {"padding": {"top": "14px", "bottom": "14px", "left": "22px", "right": "22px"}},
                    "border": {"radius": "999px"},
                    "_extra": "display:inline-block;text-decoration:none",
                })
            buttons_block(cta)
        group(links, style={"spacing": {"blockGap": "36px"}}, layout={"type": "flex", "alignItems": "center", "flexWrap": "nowrap"})
    group(
        inner,
        style={
            "color": {"text": IVORY},
            "spacing": {"padding": {"top": "24px", "bottom": "24px", "left": "clamp(20px, 4vw, 60px)", "right": "clamp(20px, 4vw, 60px)"}},
            "position": {"type": "sticky", "top": "0px"},
            "_extra": "background:#0B0E12",
        },
        layout={"type": "flex", "justifyContent": "space-between", "alignItems": "center"},
    )


# ============================================================
# HERO — core/group, min-height 100vh. HARD LIMIT: the source's layered
# radial+linear gradient overlay (two stacked gradients + a photo
# background-image) can't be expressed via style.color.gradient (single
# gradient only) — approximated here with one linear gradient standing in
# for the darkening vignette; the photo layer and second gradient are
# dropped, not faked.
# ============================================================
def hero_section():
    def inner():
        def cols():
            def left():
                paragraph('Edição Lumière · 2026', style={
                    "typography": {"fontFamily": SANS, "fontSize": "11px", "letterSpacing": "0.42em", "textTransform": "uppercase"},
                    "color": {"text": BRONZE_3},
                    "spacing": {"margin": {"bottom": "40px", "top": "0"}},
                })
                heading(1, 'O atelier é o lugar onde criar encontra <em style="font-style:italic;color:' + BRONZE_2 + '">morar</em>.', style={
                    "typography": {"fontFamily": SERIF, "fontWeight": "300", "fontSize": "clamp(48px, 8vw, 116px)", "lineHeight": "1.02", "letterSpacing": "-0.015em"},
                    "color": {"text": IVORY},
                    "spacing": {"margin": {"top": "0", "bottom": "0"}},
                })
                paragraph('Um clube <em style="font-style:italic">por convite</em>. Para arquitetos e designers que transformam o banho em obra, o spa em poesia e o projeto em memória.', style={
                    "typography": {"fontFamily": SERIF, "fontWeight": "300", "fontStyle": "italic", "fontSize": "clamp(20px, 1.9vw, 26px)", "lineHeight": "1.45"},
                    "color": {"text": "rgba(239,236,228,.86)"},
                    "spacing": {"margin": {"top": "36px", "bottom": "44px"}},
                })

                def ctas():
                    button('Solicitar adesão', url='#adesao', style={
                        "typography": {"fontFamily": SANS, "fontSize": "12px", "fontWeight": "500", "letterSpacing": "0.26em", "textTransform": "uppercase"},
                        "color": {"text": INK, "background": BRONZE},
                        "spacing": {"padding": {"top": "24px", "bottom": "24px", "left": "38px", "right": "38px"}},
                        "border": {"radius": "999px"},
                        "_extra": "display:inline-block;text-decoration:none",
                    })
                    button('Ler o convite', url='#convite', style={
                        "typography": {"fontFamily": SANS, "fontSize": "12px", "fontWeight": "500", "letterSpacing": "0.26em", "textTransform": "uppercase"},
                        "color": {"text": IVORY},
                        "spacing": {"padding": {"top": "24px", "bottom": "24px", "left": "38px", "right": "38px"}},
                        "border": {"radius": "999px", "width": "1px", "color": "rgba(239,236,228,.3)"},
                        "_extra": "display:inline-block;text-decoration:none",
                    })
                buttons_block(ctas, layout={"type": "flex", "flexWrap": "wrap"})
            column(left)

            def right():
                paragraph('"Você não entra.<br>Você é recebido."', style={
                    "typography": {"fontFamily": SERIF, "fontStyle": "italic", "fontWeight": "300", "fontSize": "20px", "lineHeight": "1.4"},
                    "color": {"text": BRONZE_3},
                    "spacing": {"margin": {"bottom": "32px", "top": "0"}},
                })

                def kv(k, v):
                    paragraph(k, style={
                        "typography": {"fontFamily": SANS, "fontSize": "10px", "letterSpacing": "0.32em", "textTransform": "uppercase"},
                        "color": {"text": BRONZE_2},
                        "spacing": {"margin": {"bottom": "10px", "top": "0"}},
                    })
                    paragraph(v, style={
                        "typography": {"fontFamily": SERIF, "fontWeight": "300", "fontSize": "20px", "lineHeight": "1.3"},
                        "color": {"text": IVORY},
                        "spacing": {"margin": {"top": "0", "bottom": "28px"}},
                    })
                kv('Programa', 'Atelier Axell Club')
                kv('Convite', 'Por seleção — apenas profissionais aprovados.')
                kv('Três níveis', 'Signature · Alliance · Ambassador')
            column(right, style={"spacing": {"padding": {"left": "40px"}}, "_extra": "border-left:1px solid rgba(239,236,228,.18)"})
        columns(cols, style={"spacing": {"blockGap": "60px"}, "_extra": "align-items:end;max-width:1280px;margin:0 auto;width:100%"})
    group(
        inner,
        style={
            "color": {"text": IVORY, "background": INK},
            "spacing": {"padding": {"top": "160px", "bottom": "clamp(80px, 10vw, 140px)", "left": "clamp(20px, 4vw, 60px)", "right": "clamp(20px, 4vw, 60px)"}},
            "dimensions": {"minHeight": "100vh"},
            "_extra": "position:relative;box-sizing:border-box;background-image:linear-gradient(180deg, rgba(11,14,18,.65) 0%, rgba(11,14,18,.3) 40%, rgba(11,14,18,.85) 100%), radial-gradient(ellipse at 70% 20%, rgba(230,178,122,.18), transparent 60%);background-color:#0B0E12;display:flex;align-items:flex-end",
        },
    )


# ============================================================
# ASSEMBLE
# ============================================================
PLUGIN_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

nav_section()
hero_section()

with open(f'{PLUGIN_DIR}/content/noclass-nav-hero.html', 'w', encoding='utf-8') as f:
    f.write('\n'.join(OUT) + '\n')

print(f"wrote noclass-nav-hero.html: {len(OUT)} lines")
