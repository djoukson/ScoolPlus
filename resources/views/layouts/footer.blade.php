<footer class="main-footer pro-footer">
    <div class="footer-left">
        <span>
            <strong>&copy; {{ date('Y') }} SchoolPlus.</strong>
            <span class="d-none d-md-inline">Tous droits réservés.</span>
        </span>
    </div>

    <div class="footer-right d-none d-sm-flex">
        <span class="footer-status" title="Système opérationnel">
            <i class="fas fa-circle"></i> En ligne
        </span>
        <span class="footer-version">v1.5.2</span>
    </div>
</footer>

<style>
    .pro-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, #0b1f33, #102c44);
        color: rgba(255, 255, 255, 0.8);
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 -6px 20px rgba(11, 31, 51, 0.25);
        padding: 8px 24px;
        font-size: 0.85rem;
        min-height: 46px;
    }

    .pro-footer strong {
        color: #fff;
        font-weight: 600;
    }

    .pro-footer .footer-left,
    .pro-footer .footer-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .pro-footer .footer-logo {
        height: 22px;
        width: 22px;
        object-fit: contain;
        border-radius: 4px;
    }

    .pro-footer .footer-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.75);
    }

    .pro-footer .footer-status i {
        font-size: 0.5rem;
        color: #2ecc71;
        animation: footerPulse 2s infinite;
    }

    .pro-footer .footer-version {
        background: rgba(109, 213, 250, 0.15);
        color: #6dd5fa;
        border: 1px solid rgba(109, 213, 250, 0.35);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    @keyframes footerPulse {
        0%   { opacity: 1; }
        50%  { opacity: 0.35; }
        100% { opacity: 1; }
    }
</style>
