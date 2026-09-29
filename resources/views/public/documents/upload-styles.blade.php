<style>
    .document-public {
        padding: 48px 0 64px;
        background: #f5f8fc;
        color: #17213d;
    }

    .document-public,
    .document-public * {
        box-sizing: border-box;
    }

    .document-shell {
        width: min(1080px, calc(100% - 32px));
        margin-inline: auto;
    }

    .document-heading {
        margin-bottom: 30px;
    }

    .document-heading > span {
        color: #007634;
        font-size: 13px;
        font-weight: 800;
    }

    .document-heading h1,
    .document-detail h1 {
        margin: 12px 0;
        font-size: clamp(26px, 3vw, 40px);
        line-height: 1.35;
        color: #0d1536;
        overflow-wrap: anywhere;
    }

    .document-heading p,
    .document-card p {
        color: #526079;
        line-height: 1.8;
    }

    .document-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .document-card {
        min-width: 0;
        padding: 26px;
        border: 1px solid #dce5f2;
        border-radius: 18px;
        background: #fff;
    }

    .document-card h2 {
        margin: 0 0 12px;
        font-size: 23px;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .document-card h2 a {
        color: #0d1536;
        text-decoration: none;
    }

    .document-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .document-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 10px 18px;
        border: 1px solid #007634;
        border-radius: 10px;
        background: #007634;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        text-decoration: none;
    }

    .document-button-light {
        border-color: #dce5f2;
        background: #eef8ff;
        color: #005c8c;
    }

    .document-button:hover {
        filter: brightness(.94);
    }

    .document-back {
        display: inline-block;
        margin-bottom: 24px;
        color: #007634;
        font-weight: 700;
    }

    .document-type {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 8px;
        background: #eaf8ff;
        color: #005c8c;
        font-size: 13px;
        font-weight: 800;
    }

    .document-description {
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .document-info {
        margin: 24px 0 0;
    }

    .document-info > div {
        display: grid;
        grid-template-columns: 120px minmax(0, 1fr);
        gap: 16px;
        padding: 13px 0;
        border-bottom: 1px solid #edf1f7;
    }

    .document-info dt {
        color: #64748b;
    }

    .document-info dd {
        margin: 0;
        overflow-wrap: anywhere;
    }

    .document-note {
        margin-top: 20px;
        font-size: 14px;
    }

    .document-pagination {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 24px;
        margin-top: 32px;
    }

    .document-pagination a {
        color: #007634;
        font-weight: 700;
    }

    .document-public a:focus-visible {
        outline: 3px solid #0076b3;
        outline-offset: 4px;
    }

    @media (max-width: 700px) {
        .document-list {
            grid-template-columns: 1fr;
        }

        .document-card {
            padding: 20px;
        }

        .document-info > div {
            grid-template-columns: 1fr;
            gap: 4px;
        }
    }
</style>