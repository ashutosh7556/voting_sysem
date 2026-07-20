<?php $prefix = (strpos($_SERVER['PHP_SELF'], '/includes/') !== false) ? '../' : ''; ?>
<footer class="site-footer">
    <div class="footer-flag"></div>
    <div class="footer-inner">
        <div class="footer-brand">
            <div class="footer-logo"><i class="fa-solid fa-check-to-slot"></i> <span>Voting System</span></div>
            <p>Election Commission of India</p>
            <p class="footer-tag">Secure · Transparent · Accessible</p>
        </div>
        <div class="footer-col">
            <h5>Quick Links</h5>
            <a href="<?= $prefix ?>index.php">Home</a>
            <a href="<?= $prefix ?>candidates.php">Candidates</a>
            <a href="<?= $prefix ?>results.php">Results</a>
            <a href="<?= $prefix ?>register.php">Register</a>
        </div>
        <div class="footer-col">
            <h5>Contact</h5>
            <p><i class="fa-solid fa-phone"></i> Toll-Free: <strong>1950</strong></p>
            <p><i class="fa-solid fa-envelope"></i> info@eci.gov.in</p>
            <p><i class="fa-solid fa-location-dot"></i> New Delhi, India</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Election Commission of India. All rights reserved.</p>
    </div>
</footer>

<style>
.site-footer { background: var(--navy-900); color: var(--slate-400); margin-top: 48px; }
.footer-flag { height: 4px; background: linear-gradient(90deg,var(--saffron) 0 33%,#fff 33% 66%,var(--green) 66% 100%); }
.footer-inner { max-width: var(--maxw); margin: 0 auto; padding: 40px 24px; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px; }
.footer-logo { font-family: var(--font-serif); font-size: var(--fs-md); font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px; }
.footer-logo i { color: var(--saffron); }
.footer-brand p { font-size: var(--fs-sm); margin: 8px 0 0; }
.footer-tag { color: var(--slate-500) !important; font-size: var(--fs-xs) !important; }
.footer-col h5 { color: #fff; font-family: var(--font-sans); font-size: var(--fs-sm); text-transform: uppercase; letter-spacing: .04em; margin: 0 0 14px; }
.footer-col a { display: block; color: var(--slate-400); text-decoration: none; font-size: var(--fs-sm); margin-bottom: 8px; }
.footer-col a:hover { color: var(--saffron); }
.footer-col p { font-size: var(--fs-sm); margin: 0 0 8px; }
.footer-bottom { border-top: 1px solid rgba(255,255,255,.1); text-align: center; padding: 16px; font-size: var(--fs-xs); }
@media (max-width: 768px) {
    .footer-inner { grid-template-columns: 1fr; gap: 24px; }
    .footer-col, .footer-brand { text-align: center; }
    .footer-logo { justify-content: center; }
}
</style>
