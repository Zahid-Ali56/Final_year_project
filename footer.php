<footer class="site-footer">
    <div class="footer-container">
        <!-- Column 1: Brand Info & Logo -->
        <div class="footer-col footer-about">
           <a href="index.php" class="footer-logo-link">
                <!-- SVG Logo ya Image Logo dono use kar sakte hain -->
                 <!--<svg class="footer-logo-svg" viewBox="0 0 500 200" xmlns="http://www.w3.org/2000/svg">
                    <path d="M 90,140 Q 110,165 135,140 Q 160,115 180,140" fill="none" stroke="#f97c06" stroke-width="14" stroke-linecap="round"/>
                    <path d="M 120,60 L 200,60 L 170,120 L 110,120 Z" fill="#ffffff"/>
                    <circle cx="125" cy="135" r="8" fill="#ffffff"/>
                    <circle cx="160" cy="135" r="8" fill="#ffffff"/>
                    <text x="210" y="115" font-family="Arial, sans-serif" font-weight="900" font-size="68" fill="#ffffff">MyShop</text>
                </svg> -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="80%" height="80%">
                    <g id="logo-mark" fill="none" stroke="#f97c06" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M 230 180 C 230 150, 270 150, 270 180 L 270 200 L 230 200 Z" stroke-width="5" fill="none" />
                        <path d="M 215 200 L 285 200 L 280 250 C 265 258, 235 258, 220 250 Z" stroke-width="5" fill="none" />
                        <path d="M 290 190 C 315 220, 310 270, 260 300 C 230 315, 190 310, 175 300 C 210 308, 255 300, 280 270 C 298 248, 298 210, 290 190 Z" 
                            fill="#f6f6f6"  "#f97c06" 
                            stroke="none" 
                            transform="translate(15, -10)" />
                    </g>
                    <text x="250" y="345" 
                          font-family="system-ui, -apple-system, sans-serif" 
                          font-size="45" 
                          font-weight="700" 
                          fill="#f97c06" 
                          text-anchor="middle" 
                          letter-spacing="3">BAZAARLY</text>
                </svg>
            </a>
            <p class="footer-desc">
                Aap ki trusted online shopping destination. Behtareen quality products aur Fast Delivery ke saath.
            </p>
            <div class="footer-socials">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>

        <!-- Column 2: Quick Links -->
        <div class="footer-col">
            <h4 class="footer-title">Quick Links</h4>
            <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Shop All</a></li>
                <li><a href="#">Featured Products</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
        </div>

        <!-- Column 3: Customer Service -->
        <div class="footer-col">
            <h4 class="footer-title">Customer Care</h4>
            <ul class="footer-links">
                <li><a href="#">My Account</a></li>
                <li><a href="#">Track Order</a></li>
                <li><a href="#">Shipping Policy</a></li>
                <li><a href="#">Returns & Exchange</a></li>
                <li><a href="#">FAQs</a></li>
            </ul>
        </div>

        <!-- Column 4: Newsletter -->
        <div class="footer-col footer-newsletter">
            <h4 class="footer-title">Stay Connected</h4>
            <p>New arrivals aur special offers ke liye subscribe karein:</p>
            <form class="newsletter-form" onsubmit="event.preventDefault();">
                <input type="email" placeholder="Enter your email..." required>
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
        <div class="footer-bottom-container">
            <p>© 2026 Bazaarly. All Rights Reserved.</p>
            <div class="payment-methods">
                <span><i class="fab fa-cc-visa"></i></span>
                <span><i class="fab fa-cc-mastercard"></i></span>
                <span><i class="fab fa-cc-paypal"></i></span>
                <span><i class="fas fa-money-bill-wave"></i> COD</span>
            </div>
        </div>
    </div>
</footer>

<!-- FontAwesome CDN (Icons ke liye header me add karein agar pehle se nahi hai) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-family/6.4.0/css/all.min.css">