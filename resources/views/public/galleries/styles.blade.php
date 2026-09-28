<style>
    .aw-gallery { background: #f5f8fc; color: #111936; }
    .aw-gallery *, .aw-gallery *::before, .aw-gallery *::after { box-sizing: border-box; }
    .aw-gallery .aw-gallery-container { width: min(1240px, calc(100% - 40px)); margin-inline: auto; }
    .aw-gallery-hero { padding: 48px 0; background: linear-gradient(120deg, #edf8ff, #fff, #effbf5); border-bottom: 1px solid #dce5f2; }
    .aw-gallery-kicker { margin: 0; color: #007634; font-size: 13px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .aw-gallery h1 { margin: 12px 0 16px; font-size: clamp(28px, 4vw, 48px); line-height: 1.25; font-weight: 800; overflow-wrap: anywhere; }
    .aw-gallery-description { max-width: 820px; margin: 0; color: #465570; font-size: 17px; line-height: 1.8; white-space: pre-line; overflow-wrap: anywhere; }
    .aw-gallery-main { padding-block: 36px 56px; }
    .aw-gallery-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 22px; align-items: stretch; }
    .aw-gallery-card { min-width: 0; overflow: hidden; display: flex; flex-direction: column; background: #fff; border: 1px solid #dce5f2; border-radius: 18px; box-shadow: 0 12px 28px rgba(17,25,54,.06); }
    .aw-gallery-cover { display: block; width: 100%; aspect-ratio: 4 / 3; overflow: hidden; background: #e8eff6; color: #465570; text-decoration: none; }
    .aw-gallery-cover img { display: block; width: 100%; height: 100%; max-width: 100%; object-fit: cover; transition: transform .2s ease; }
    .aw-gallery-placeholder { display: flex; align-items: center; justify-content: center; height: 100%; padding: 20px; text-align: center; line-height: 1.6; }
    .aw-gallery-card-body { padding: 20px; display: flex; flex: 1; flex-direction: column; gap: 12px; }
    .aw-gallery-card h2 { margin: 0; font-size: 19px; line-height: 1.5; font-weight: 700; overflow-wrap: anywhere; }
    .aw-gallery-card a { color: inherit; text-decoration: none; }
    .aw-gallery-card-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 14px; margin: 0; color: #465570; font-size: 14px; line-height: 1.6; overflow-wrap: anywhere; }
    .aw-gallery-card-meta time { white-space: nowrap; }
    .aw-gallery-card .aw-gallery-link, .aw-gallery-link { display: inline-flex; align-items: center; min-height: 44px; color: #006b39; font-weight: 700; line-height: 1.6; text-decoration: none; overflow-wrap: anywhere; }
    .aw-gallery-card .aw-gallery-link { margin-top: auto; }
    .aw-gallery-photo { min-width: 0; margin: 0; border: 1px solid #dce5f2; border-radius: 16px; overflow: hidden; background: #fff; }
    /* Album covers may crop; photographs inside an album show the complete image. */
    .aw-gallery-photo .aw-gallery-cover { background: #edf1f6; }
    .aw-gallery-photo .aw-gallery-cover img { object-fit: contain; }
    .aw-gallery-photo figcaption { padding: 14px 16px; line-height: 1.7; color: #465570; overflow-wrap: anywhere; }
    .aw-gallery-hint { margin: 0 0 22px; color: #465570; font-size: 14px; line-height: 1.7; }
    .aw-gallery-empty { grid-column: 1 / -1; padding: 44px 24px; text-align: center; border: 1px dashed #bdcddd; border-radius: 18px; background: #fff; }
    .aw-gallery-empty svg { display: block; width: 48px; height: 48px; margin: 0 auto 16px; color: #0877a5; }
    .aw-gallery-empty h2 { margin: 0 0 12px; color: #111936; font-size: 24px; line-height: 1.5; }
    .aw-gallery-empty p { max-width: 560px; margin: 0 auto 14px; color: #465570; line-height: 1.8; }
    .aw-gallery-empty .aw-gallery-link { padding: 8px 18px; border: 1px solid #bdd5ca; border-radius: 12px; }
    .aw-gallery-pagination { margin-top: 32px; }
    .aw-gallery-pager { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 10px; }
    .aw-gallery-page-button { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 44px; padding: 9px 13px; border: 1px solid #ccd9e6; border-radius: 10px; background: #fff; color: #26344f; font-size: 15px; font-weight: 700; line-height: 1.6; text-decoration: none; }
    .aw-gallery-page-button[aria-current="page"] { border-color: #006b39; background: #006b39; color: #fff; }
    .aw-gallery-page-button[aria-disabled="true"] { color: #5f6b7f; background: #edf1f6; }
    .aw-gallery-page-numbers { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 8px; }
    .aw-gallery-page-gap { padding: 8px 4px; }
    .aw-gallery-page-count { display: none; color: #465570; font-size: 14px; }
    .aw-gallery a:focus-visible { outline: 3px solid #0877a5; outline-offset: 3px; }
    .aw-gallery-cover:focus-visible { outline-offset: -4px; }
    .aw-gallery:lang(si) h1 { line-height: 1.5; }
    .aw-gallery:lang(si) .aw-gallery-kicker { letter-spacing: normal; }
    .aw-gallery:lang(si) .aw-gallery-card h2 { line-height: 1.8; }
    @media (hover: hover) and (pointer: fine) {
        .aw-gallery-card:hover .aw-gallery-cover img { transform: scale(1.025); }
        .aw-gallery-page-button[href]:hover { border-color: #007634; background: #effbf5; color: #006b39; }
        .aw-gallery-link:hover { text-decoration: underline; text-underline-offset: 4px; }
    }
    @media (max-width: 1100px) { .aw-gallery-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 860px) { .aw-gallery-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; } }
    @media (max-width: 600px) {
        .aw-gallery .aw-gallery-container { width: calc(100% - 32px); }
        .aw-gallery-grid { grid-template-columns: 1fr; gap: 18px; }
        .aw-gallery-hero { padding-block: 28px; }
        .aw-gallery-main { padding-block: 28px 40px; }
        .aw-gallery-description { font-size: 16px; }
        .aw-gallery-card-body { padding: 18px; }
        .aw-gallery-empty { padding: 30px 18px; }
        .aw-gallery-pager { gap: 8px; justify-content: space-between; }
        .aw-gallery-page-numbers { display: none; }
        .aw-gallery-page-count { display: inline; }
        .aw-gallery-page-button { padding-inline: 11px; font-size: 14px; }
    }
    @media (prefers-reduced-motion: reduce) { .aw-gallery-cover img { transition: none; transform: none !important; } }
</style>
