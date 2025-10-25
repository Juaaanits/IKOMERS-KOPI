<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | IKOMERS KOPI</title>
    <link rel="stylesheet" href="/IKOMERS-KOPI/assets/css/home-style.css?v=20251025">
</head>

<body>
    <nav class="navbar">
        <div class="container navbar__inner">
            <a href="#welcome-container" class="navbar__brand">IKOMERS KOPI</a>
            <div class="navbar__menu">
                <div class="nav-links">
                    <a href="#welcome-container">Home</a>
                    <a href="#features-container">Menu</a>
                    <a href="#third-container">Offers</a>
                </div>
                <div class="nav-cta">
                    <a href="../pages/login.php" id="order-online">Order Online</a>
                    <a href="../pages/admin-login.php" id="admin-login" aria-label="Administrator Login">
                        <span class="admin-icon" aria-hidden="true">&#9881;</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <!-- Hero Section -->
        <section id="welcome-container">
            <div class="container hero__inner">
                <div class="hero__content">
                    <span class="eyebrow">Freshly Brewed Daily</span>
                    <h1>Savor the Perfect Brew.</h1>
                    <p>From ethically sourced beans to artisan roasting, every cup at IKOMERS KOPI is crafted for rich aroma and lasting comfort.</p>
                    <div id="welcome-buttons">
                        <a href="#features-container" class="button-link button-link--primary">Explore Menu</a>
                        <a href="#third-container" class="button-link">Visit the Cafe</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Coffees -->
        <section id="features-container">
            <div class="container">
                <div class="section-header">
                    <span class="eyebrow">Barista Selections</span>
                    <h2 class="section-title">Signature Favorites</h2>
                </div>
                <div id="features-item">
                    <article class="coffee-item">
                        <img src="../assets/images/coffee-latte-icon.png" alt="Espresso cup icon">
                        <h3>Espresso</h3>
                        <p>A bold, velvety shot that balances caramel sweetness with a lingering cocoa finish.</p>
                    </article>
                    <article class="coffee-item">
                        <img src="../assets/images/coffee-latte-icon.png" alt="Latte cup icon">
                        <h3>Latte</h3>
                        <p>Silky steamed milk meets rich espresso for a smooth, comforting crowd favourite.</p>
                    </article>
                    <article class="coffee-item">
                        <img src="../assets/images/coffee-latte-icon.png" alt="Americano cup icon">
                        <h3>Americano</h3>
                        <p>Bright and balanced, brewed to showcase the nuances of our single-origin beans.</p>
                    </article>
                    <article class="coffee-item">
                        <img src="../assets/images/coffee-latte-icon.png" alt="Cold brew glass icon">
                        <h3>Cold Brew</h3>
                        <p>Slow-steeped for 18 hours to deliver a refreshing, naturally sweet finish.</p>
                    </article>
                </div>
            </div>
        </section>

        <div id="designbox" aria-hidden="true"></div>

        <!-- Offers & Highlights -->
        <section id="third-container">
            <div class="container">
                <div class="section-header">
                    <span class="eyebrow">Experience Matters</span>
                    <h2 class="section-title section-title--light">Why Choose IKOMERS KOPI</h2>
                </div>
                <div id="why-choose-us">
                    <article class="feature-panel feature-panel--dark">
                        <h3>Brewing Joy One Cup at a Time</h3>
                        <p>We roast in small batches and pour with intention so every sip is bold, balanced, and memorable.</p>
                        <p>Pair artisan pastries with single-origin beans, enjoy the cozy space, and let our baristas guide your next favourite drink.</p>
                        <a href="#features-container" class="button-link">Learn More</a>
                        <img src="../assets/images/person-serving.png" alt="Barista serving a cup of coffee">
                    </article>
                    <article class="feature-panel feature-panel--light">
                        <h3>Exclusive Morning Treat</h3>
                        <p>Drop by before noon and enjoy 10% off your first beverage. It is the perfect excuse to explore our brews.</p>
                        <p>Show this offer at the bar to redeem. Valid for dine-in or takeout every weekday morning.</p>
                        <a href="#welcome-container" class="button-link button-link--primary">Claim the Offer</a>
                    </article>
                </div>
            </div>
        </section>
    </main>
</body>

</html>
