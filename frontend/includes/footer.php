<?php
/**
 * footer.php — Compact Professional Organic Footer
 */
?>
<style>
    .main-footer {
        background: linear-gradient(135deg, #0a2e1f 0%, #111827 100%);
        color: #fff;
        padding: 50px 40px 20px;
        margin-top: auto;
        position: relative;
        overflow: hidden;
        border-top: 3px solid #2E7D32;
    }

    .main-footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('https://www.transparenttextures.com/patterns/leaf.png');
        opacity: 0.03;
        pointer-events: none;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.5fr;
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto 30px;
        position: relative;
        z-index: 1;
    }

    .footer-brand h2 {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: -0.02em;
    }

    .footer-brand p {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.9rem;
        line-height: 1.6;
        max-width: 300px;
        margin-bottom: 20px;
    }

    .footer-col h3 {
        color: #4ADE80;
        font-size: 0.75rem;
        font-weight: 800;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 0.12em;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 10px;
    }

    .footer-links a {
        color: rgba(255, 255, 255, 0.5);
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .footer-links a:hover {
        color: #fff;
        transform: translateX(4px);
    }

    .footer-links .contact-item {
        display: flex;
        gap: 10px;
        align-items: center;
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 12px;
        font-size: 0.88rem;
    }

    .footer-links .contact-item i {
        color: #4ADE80;
        font-size: 0.9rem;
        width: 16px;
    }

    .social-links {
        display: flex;
        gap: 10px;
    }

    .social-links a {
        background: rgba(255, 255, 255, 0.06);
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .social-links a:hover {
        background: #2E7D32;
        transform: translateY(-3px);
        border-color: #4ADE80;
    }

    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: 20px;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: rgba(255, 255, 255, 0.4);
        font-size: 0.825rem;
        position: relative;
        z-index: 1;
    }

    .footer-badges {
        display: flex;
        gap: 20px;
    }

    .footer-badges span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    @media (max-width: 900px) {
        .footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .footer-brand {
            grid-column: span 2;
            margin-bottom: 10px;
        }
    }

    @media (max-width: 600px) {
        .footer-grid {
            grid-template-columns: 1fr;
        }

        .footer-brand {
            grid-column: span 1;
        }

        .footer-bottom {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }
</style>

<footer class="main-footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <h2><i class="fa-solid fa-leaf" style="color:#4ADE80;"></i> FOOD LINK</h2>
            <p>Smart technology connecting surplus food to those in need, reducing waste across the nation.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h3>Explore</h3>
            <ul class="footer-links">
                <li><a href="/index.php">Home</a></li>
                <li><a href="/pages/footer/about.php">About</a></li>
                <li><a href="/pages/footer/impact.php">Impact</a></li>
                <li><a href="/pages/footer/contact.php">Contact</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h3>Legal</h3>
            <ul class="footer-links">
                <li><a href="/pages/footer/terms.php">Terms</a></li>
                <li><a href="/pages/footer/privacy.php">Privacy</a></li>
                <li><a href="/pages/footer/donors-guide.php">Manual</a></li>
                <li><a href="/pages/footer/volunteer-code.php">Conduct</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h3>Contact</h3>
            <div class="footer-links">
                <div class="contact-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>support@foodlink.org</span>
                </div>
                <div class="contact-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>+1 234 567 890</span>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div>&copy; <?= date('Y') ?> Food Link Platform. Connecting surplus to soul.</div>
        <div class="footer-badges">
            <span><i class="fa-solid fa-shield-check" style="color:#4ADE80;"></i> SSL Secured</span>
            <span><i class="fa-solid fa-leaf" style="color:#4ADE80;"></i> Zero Waste</span>
        </div>
    </div>
</footer>

</body>

</html>