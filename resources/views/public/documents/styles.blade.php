<style>
    .sos-documents {
        background: #f5f8fc;
        color: #17213d;
    }

    .sos-documents,
    .sos-documents * {
        box-sizing: border-box;
    }

    .sos-documents .doc-shell {
        width: min(1120px, calc(100% - 40px));
        margin-inline: auto;
    }

    .sos-documents .doc-hero {
        padding: 48px 0;
        border-bottom: 1px solid #dce5f2;
        background: linear-gradient(135deg, #eaf8ff, #ffffff, #eafff4);
    }

    .sos-documents .doc-kicker {
        margin: 0 0 10px;
        color: #007634;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .sos-documents h1 {
        margin: 0;
        color: #0d1536;
        font-size: clamp(28px, 4vw, 44px);
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .sos-documents .doc-intro {
        margin: 16px 0 0;
        color: #526079;
        font-size: 17px;
        line-height: 1.85;
    }

    .sos-documents .doc-content {
        padding: 36px 0 48px;
    }

    .sos-documents .doc-list {
        display: grid;
        gap: 18px;
    }

    .sos-documents .doc-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 24px;
        border: 1px solid #dce5f2;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(7, 19, 51, .04);
    }

    .sos-documents .doc-copy {
        flex: 1;
        min-width: 0;
    }

    .sos-documents .doc-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        color: #526079;
        font-size: 13px;
    }

    .sos-documents .doc-badge {
        display: inline-flex;
        padding: 4px 10px;
        border-radius: 999px;
        background: #eaf8ff;
        color: #005c8c;
        font-size: 12px;
        font-weight: 800;
    }

    .sos-documents .doc-card h2 {
        margin: 12px 0 0;
        color: #0d1536;
        font-size: 22px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .sos-documents .doc-description {
        margin: 12px 0 0;
        color: #526079;
        font-size: 16px;
        line-height: 1.85;
        overflow-wrap: anywhere;
    }

    .sos-documents .doc-version {
        margin: 12px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .sos-documents .doc-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .sos-documents .doc-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 10px 16px;
        border: 1px solid #ccd8e6;
        border-radius: 10px;
        background: #ffffff;
        color: #17213d;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.5;
        text-align: center;
        text-decoration: none;
    }

    .sos-documents .doc-button:hover {
        border-color: #0076b3;
        background: #eaf8ff;
        color: #005c8c;
    }

    .sos-documents .doc-button-primary {
        border-color: #007634;
        background: #007634;
        color: #ffffff;
    }

    .sos-documents .doc-button-primary:hover {
        border-color: #005c29;
        background: #005c29;
        color: #ffffff;
    }

    .sos-documents a:focus-visible {
        outline: 3px solid #0076b3;
        outline-offset: 4px;
    }

    .sos-documents .doc-empty {
        padding: 56px 24px;
        border: 2px dashed #ccd8e6;
        border-radius: 18px;
        background: #ffffff;
        text-align: center;
    }

    .sos-documents .doc-empty h2 {
        margin: 16px 0 8px;
        font-size: 24px;
    }

    .sos-documents .doc-pagination {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 28px;
        color: #526079;
        font-size: 14px;
    }

    .sos-documents .doc-pagination-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .sos-documents .doc-disabled {
        opacity: .5;
        cursor: default;
    }

    .sos-documents .doc-back {
        display: inline-block;
        margin-bottom: 20px;
        color: #007634;
        font-size: 15px;
        font-weight: 700;
    }

    .sos-documents .doc-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        align-items: start;
        gap: 24px;
    }

    .sos-documents .doc-panel {
        min-width: 0;
        padding: 24px;
        border: 1px solid #dce5f2;
        border-radius: 18px;
        background: #ffffff;
    }

    .sos-documents .doc-panel + .doc-panel {
        margin-top: 20px;
    }

    .sos-documents .doc-panel h2 {
        margin: 0 0 18px;
        color: #0d1536;
        font-size: 23px;
        line-height: 1.5;
    }

    .sos-documents .doc-data-row {
        display: grid;
        grid-template-columns: 150px minmax(0, 1fr);
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid #e7edf4;
        font-size: 15px;
        line-height: 1.8;
    }

    .sos-documents .doc-data-row:last-child {
        border-bottom: 0;
    }

    .sos-documents dt {
        color: #526079;
    }

    .sos-documents dd {
        margin: 0;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .sos-documents .doc-text {
        color: #34405a;
        font-size: 16px;
        line-height: 1.9;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .sos-documents .doc-sidebar-actions {
        display: grid;
        gap: 10px;
        margin-top: 20px;
    }

    .sos-documents .doc-url {
        display: block;
        margin-top: 14px;
        color: #005c8c;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    @media (max-width: 900px) {
        .sos-documents .doc-card {
            align-items: flex-start;
            flex-direction: column;
        }

        .sos-documents .doc-detail-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .sos-documents .doc-shell {
            width: calc(100% - 32px);
        }

        .sos-documents .doc-hero {
            padding: 32px 0;
        }

        .sos-documents .doc-card,
        .sos-documents .doc-panel {
            padding: 20px;
        }

        .sos-documents .doc-card h2 {
            font-size: 20px;
        }

        .sos-documents .doc-actions {
            width: 100%;
        }

        .sos-documents .doc-actions .doc-button {
            flex: 1 1 auto;
        }

        .sos-documents .doc-data-row {
            grid-template-columns: 1fr;
            gap: 4px;
        }
    }
</style>