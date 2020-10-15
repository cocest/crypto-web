<?php 

// import all the necessary liberaries
require_once '../includes/config.php';

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>Thecitadelcapital - Homepage</title>
    <link rel="icon" type="image/png" href="./images/icons/favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="./images/icons/favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="./images/icons/favicon3.png" sizes="120x120">
    <meta name="description" content="Thecitadelcapital official website">
    <meta name="keywords" content="thecitadelcapital, cryptocurrency, investment, funds, business">
    <meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=0.9">
    <link type="text/css" href="./fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="./styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="./styles/homepage.css">
    <script type="text/javascript" src="./js/utils.js"></script>
    <script type="text/javascript" src="./js/slider.js"></script>
    <script type="text/javascript" src="./js/homepage.js"></script>
    <script type="text/javascript" src="./js/smoothScroll.js"></script>
    <script type="text/javascript" src="./js/subscribe_newsletter.js"></script>
</head>

<body class="theme-bg-color">
    <div class="page-top-menu-cont absolute">
        <!--Header menu container-->
        <div class="page-cont-max-width">
            <nav>
                <div class="site-logo-cont">
                    <div class="site-logo-wrapper">
                        <a href="./index.html">
                            <img class="site" src="./images/icons/w_citadel_capital_logo.png" alt="thecitadelcapital" />
                        </a>
                    </div>
                </div>
                <!--for desktop view-->
                <div class="main-menu-cont">
                    <ul class="ux-hr-menu fmt-link-med ux-txt-align-rt">
                        <li><a class="link ux-txt-smokewhite txt-hover" onclick="scrollToSection(0, this)">Home</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="./about_us.html">About Us</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" onclick="scrollToSection(1, this)">Contact Us</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="./help/faq.html">FAQ</a></li>
                        <li><a class="ux-btn reg-btn" href="./register.html">Get Started</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="./user/login.html">Sign In</a></li>
                    </ul>
                </div>

                <!--for mobile view-->
                <div class="main-menu-cont-mobi">
                    <ul class="ux-hr-menu fmt-link-med ux-txt-align-rt">
                        <li><a class="link ux-txt-smokewhite txt-hover" href="./user/login.html">Sign In</a></li>
                        <li>
                            <div class="drop-menu-icon-cont" toggle="0" onclick="dropMobileMenu(this)">
                                <img class="drop-menu-icon close" src="./images/icons/drop_menu_icon.png" />
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>

    <!--drop down mobile menu container-->
    <div class="drop-down-mobi-menu-cont theme-bg-color hide shadow">
        <ul class="ux-vt-menu">
            <li><a class="link active" onclick="scrollToSection(0, this)">Home</a></li>
            <li><a class="link" href="./about_us.html">About US</a></li>
            <li><a class="link" onclick="scrollToSection(1, this)">Contact US</a></li>
            <li><a class="link" href="./help/faq.html">FAQ</a></li>
            <li><a class="link" href="./register.html">Get Started</a></li>
        </ul>
    </div>

    <!--page upper section-->
    <div class="page-upper-section">
        <div id="imageslider-cont"></div>
        <div class="headline-left-bg-img-cont">
            <div class="headline-left-bg-img"></div>
        </div>
        <!--headline container-->
        <div id="headline-cont" class="page-cont-max-width">
            <div class="headline">
                <h1>
                    <span class="ux-txt-white ux-fs-px-23 txt-shadow">Creating Profitable And Lasting Partnerships</span>
                </h1>
            </div>
            <div class="sub-headline">
                <h2>
                    <span class="ux-txt-white ux-fs-px-15 sub-headline-txt txt-shadow">
                        We provide a means to alternative investing, generating profit for 
                        our partners in the short and long terms. You too can become a partner now!
                    </span>
                </h2>
            </div>
            <div class="action-btn-cont">
                <a class="ux-btn custom-action-btn ux-bg-chocolate bg-hover ux-txt-white shadow"
                    href="./register.html">Create Account</a>
            </div>
        </div>
        <!--animated image indicator-->
        <div id="anim-img-indicator">
            <div id="img-ind-0" class="ux-bg-grayblue bg-hover ux-f-rd-corner" onclick="gotoImageSlider(0)">
            </div>
            <div id="img-ind-1" class="ux-bg-white ux-f-rd-corner" onclick="gotoImageSlider(1)"></div>
            <div id="img-ind-2" class="ux-bg-white ux-f-rd-corner" onclick="gotoImageSlider(2)"></div>
        </div>
    </div>

    <!--why choose us section-->
    <div class="package-section page-cont-max-width">
        <div class="package-headline ux-txt-align-ct">
            <h1 class="ux-txt-white">Why Choose Us ?</h1>
            <p class="descr-txt ux-fs-px-18 ux-txt-white">
                You are at the right place. With our top notch service your success is guaranteed. 
                It is our obligation to provide unparalleled services and we are always working 
                to suit your preferences in the best way.
            </p>
        </div>
        <div class="package-panel-cont">
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="fas fa-shield-alt ux-txt-white"></i>
                </div>
                <div class="content ux-txt-white">
                    <h3>Risk Management</h3>
                    <p class="ux-fs-px-18">
                        Our collaborative culture of debate, on-the-ground work and 
                        in-house research enable us to enhance data analysis with 
                        human-driven insight to better manage complexity and 
                        evaluate hidden risks.
                    </p>
                </div>
            </div>
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="fas fa-globe-africa ux-txt-white"></i>
                </div>
                <div class="content ux-txt-white">
                    <h3>Global Accessibility</h3>
                    <p class="ux-fs-px-18">
                        We are an international firm and partner with institutional 
                        and private investors from different ends of the globe. We also recruit 
                        team members irrespective of nationality.
                    </p>
                </div>
            </div>
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="fas fa-compress-arrows-alt ux-txt-white"></i>
                </div>
                <div class="content ux-txt-white">
                    <h3>Reduced Minimums</h3>
                    <p class="ux-fs-px-18">
                        We have lower minimums to accommodate different income classes and 
                        charge only 2% upon withdrawal. Other management fees are pinned to the AUM.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!--Our service section-->
    <div class="service-section-cont">
        <div class="service-section page-cont-max-width">
            <div class="service-headline ux-txt-align-ct">
                <h1 class="ux-txt-lightgray">How We Do It ?</h1>
                <p class="descr-txt ux-fs-px-18 ux-txt-lightgray">
                    We believe in diversity and as such spread our portfolios 
                    across different aspects of the alternative markets, generating 
                    profit for our partners in the short and long terms.
                </p>
            </div>
            <div class="service-panel-cont">
                <div class="featured-img-cont">
                    <img src="./images/background/how-we-do-it.jpg" />
                </div>
                <div class="service-list-cont">
                    <div class="service-cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-chart-line ux-txt-lightgray"></i>
                        </div>
                        <div class="content ux-txt-lightgray">
                            <h3>Derivatives</h3>
                            <p class="ux-fs-px-18">
                                We engage in options and derivatives trading with the aim of 
                                generating good returns while reducing risk.
                            </p>
                        </div>
                    </div>
                    <div class="service-cont">
                        <div class="featured-art-cont">
                            <i class="fab fa-bitcoin ux-txt-lightgray"></i>
                        </div>
                        <div class="content ux-txt-lightgray">
                            <h3>Digital Currencies</h3>
                            <p class="ux-fs-px-18">
                                We explore the digital currency industry, investing in Blockchain 
                                startups and ICOs to harness the groundbreaking returns in the 
                                digital currency markets.
                            </p>
                        </div>
                    </div>
                    <div class="service-cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-chart-pie ux-txt-lightgray"></i>
                        </div>
                        <div class="content ux-txt-lightgray">
                            <h3>Quantitative Hedge Funds</h3>
                            <p class="ux-fs-px-18">
                                We invest in and manage hedge funds, providing K1s for our 
                                partners and regularly offering alphas.
                            </p>
                        </div>
                    </div>
                    <div class="service-cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-gem ux-txt-lightgray"></i>
                        </div>
                        <div class="content ux-txt-lightgray">
                            <h3>Commodities</h3>
                            <p class="ux-fs-px-18">
                                Our portfolio includes commodity futures, precious metals 
                                and related funds thus providing a hedge for our partners.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Getting started section-->
    <div class="getting-started-section-cont">
        <div class="getting-started-section page-cont-max-width">
            <div class="headline-cont ux-txt-align-ct">
                <h1 class="ux-txt-white">Getting Started</h1>
                <p class="descr-txt ux-fs-px-18 ux-txt-white">
                    The Citadel is a firm believer in the blockchain technology. To invest 
                    using our platform you must have completed the steps below.
                </p>
            </div>
            <div class="getting-started-cont">
                <div class="featured-img-cont">
                    <img src="./images/background/getting-started.jpg" />
                </div>
                <div class="getting-started-steps">
                    <div class="instruction-cont">
                        <div class="featured-art-cont">
                            <div class="numbering">
                                <span>1</span>
                            </div>
                        </div>
                        <div class="content ux-txt-white">
                            <h3>Own a Wallet</h3>
                            <p class="ux-fs-px-18">
                                Download a wallet on your device for storage of your digital currencies. 
                                Always opt for offline wallets as they offer better security.
                            </p>
                        </div>
                    </div>
                    <div class="instruction-cont">
                        <div class="featured-art-cont">
                            <div class="numbering">
                                <span>2</span>
                            </div>
                        </div>
                        <div class="content ux-txt-white">
                            <h3>Purchase Digital Currencies</h3>
                            <p class="ux-fs-px-18">
                                Digital currencies can be bought on trusted exchanges. For more information see FAQs.
                            </p>
                        </div>
                    </div>
                    <div class="instruction-cont">
                        <div class="featured-art-cont">
                            <div class="numbering">
                                <span>3</span>
                            </div>
                        </div>
                        <div class="content ux-txt-white">
                            <h3>Create an Account</h3>
                            <p class="ux-fs-px-18">
                                Click on the Create Account button to register and create 
                                your account. Once created select package of your choice.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--About cryptocurrency section-->
    <div class="about-crypto-section-cont">
        <div class="about-crypto-section page-cont-max-width">
            <div class="about-crypto-headline-cont">
                <h1 class="ux-txt-lightgray">About Us</h1>
                <!--<div class="about-crypto-headline-underline ux-bg-grayblue"></div>-->
            </div>
            <div class="about-crypto-img-cont">
                <img src="./images/background/about-us-1.jpg" alt="About cryptocurrency image" />
            </div>
            <div class="about-crypto-cont">
                <p>
                    The Citadel Capital Partners is a leading investor in the global financial and 
                    alternative markets. Founded with the aim of ensuring capital appreciation as 
                    well as wealth protection, we have consistently delivered returns to our partners 
                    for more than a decade, thus maintaining our promise of establishing profitable 
                    and everlasting partnerships.
                </p>
                <div class="lm-about-us-link-cont">
                    <a href="./about_us.html">
                        <span>Learn more</span><img src="./images/icons/more_icon.png" alt="more" />
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (false) { ?>
    <!--User Testimonial section-->
    <div class="testimoney-section-cont remove-elem">
        <div class="testimoney-section page-cont-max-width">
            <div class="tm-scroll-left-btn ux-f-rd-corner shadow anim-btn" onclick="slideTestimonial('prev')"><i
                    class="fas fa-angle-left"></i></div>
            <div class="tm-scroll-right-btn ux-f-rd-corner shadow anim-btn" onclick="slideTestimonial('next')"><i
                    class="fas fa-angle-right"></i></div>
            <div class="testimonial-descr-cont">
                <h1 class="ux-txt-grayblue">Customers' Testimony</h1>
            </div>
            <div id="testimonial-touch-surface" class="testimonial-panel-cont"></div>
            <div id="testimonial-full-panel-cont" class="remove-elem">
                <div class="win-close-btn" onclick="closeWindowPanel('testimonial-full-panel-cont')">
                    <i class="fas fa-window-close"></i>
                </div>
                <div>
                    <p class="testimony ux-fs-px-18">
                        <img class="prof-pic ux-f-rd-corner shadow" alt="user's profile picture" />
                        <span class="user-name ux-txt-grayblue"></span>
                        <span class="txt"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <!--Contact us section-->
    <div id="pop-up-message-bg-cover" class="remove-elem"></div>
    <div id="contact-us-pop-up-message" class="remove-elem">
        <div class="message-cont">
            <h4 class="message-title">Success!</h4>
            <p class="message-body">
                You have successfully subcribed to our newsletter.
            </p>
        </div>
        <div class="button-cont">
            <button class="ok-button" onclick="closePopMessage('contact-us-pop-up-message', true)">Ok</button>
        </div>
    </div>
    <div class="contact-us-section-cont">
        <div class="contact-us-section page-cont-max-width">
            <div class="headline-cont ux-txt-align-ct">
                <h1 class="ux-txt-white">Contact Us</h1>
            </div>
            <div class="message-us-cont">
                <h2 class="header-title ux-txt-white">Let's get in touch</h2>
                <p class="header-message ux-txt-white">
                    The Citadel Capital Partners is a leading investor in the global alternative and 
                    financial markets. Founded in 1996 for the aim of ensuring capital appreciation.
                </p>
                <ul class="contact-info-list">
                    <li>
                        <div class="list-icon">
                            <i class="fas fa-envelope ux-txt-white"></i>
                        </div>
                        <div class="list-data ux-txt-white">contact@thecitadelcapital.com</div>
                    </li>
                    <li>
                        <div class="list-icon">
                            <i class="fas fa-phone-square-alt ux-txt-white"></i>
                        </div>
                        <div class="list-data ux-txt-white">+356 21250666</div>
                    </li>
                    <li>
                        <div class="list-icon">
                            <i class="fas fa-map-marker-alt ux-txt-white"></i>
                        </div>
                        <div class="list-data ux-txt-white">392 Triq il Kanun, Santa Venera, Malta</div>
                    </li>
                </ul>
            </div>
            <div class="contact-form-cont">
                <form name="contact-us-form" onsubmit="return processSendUsMessageForm(event)" autocomplete="off" spellcheck="false" novalidate>
                    <div class="form-input-cont">
                        <div class="input-cont">
                            <label class="ux-txt-white" for="name-input">Your Name</label>
                            <input id="name-input" type="text" name="name" placeholder="Enter your full name" />
                        </div>
                        <div class="input-cont">
                            <label class="ux-txt-white" for="email-input">Your Email</label>
                            <input id="email-input" type="text" name="email" placeholder="Enter your email" />
                        </div>
                    </div>
                    <div class="textarea-cont">
                        <div class="textarea-label ux-txt-white">Your Message</div>
                        <textarea class="textarea-input" name="message" data-gramm_editor="false"></textarea>
                    </div>
                    <div class="submit-btn-cont">
                        <button id="contact-us-submit-btn" class="form-submit-btn" type="submit">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--Achievement section-->
    <?php 
        // don't render achievement section
        if (false) {
    ?>
    <div class="achievement-section-cont">
        <div class="achievement-section page-cont-max-width">
            <?php if (false) { ?>
            <div class="records-cont ux-layout-grid columns-4">
                <div class="grid-item">
                    <div class="upper-sec">
                        <span class="icon">
                            <i class="fab fa-bitcoin"></i>
                        </span>
                        <span class="figure">627,403+</span>
                    </div>
                    <div class="lower-sec">
                        <h4>Cryptocurrency Traded</h4>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="upper-sec">
                        <span class="icon">
                            <i class="far fa-question-circle"></i>
                        </span>
                        <span class="figure">3,421+</span>
                    </div>
                    <div class="lower-sec">
                        <h4>Something Here</h4>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="upper-sec">
                        <span class="icon">
                            <i class="fas fa-users"></i>
                        </span>
                        <span class="figure">12,740+</span>
                    </div>
                    <div class="lower-sec">
                        <h4>Registered Users</h4>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="upper-sec">
                        <span class="icon">
                            <i class="far fa-smile"></i>
                        </span>
                        <span class="figure">8,204+</span>
                    </div>
                    <div class="lower-sec">
                        <h4>Happy Customers</h4>
                    </div>
                </div>
            </div>
            <?php } 
                  if (false) {
            ?>
            <div class="sponsor-cont ux-layout-grid columns-4">
                <div class="grid-item">
                    <img class="img-2" src="./images/organisation/fsca_logo.png" alt="fsca logo" />
                </div>
                <div class="grid-item">
                    <img class="img-1" src="./images/organisation/fca_logo.png" alt="fca logo" />
                </div>
                <div class="grid-item">
                    <img class="img-3" src="./images/organisation/mfsa_logo.png" alt="mfsa logo" />
                </div>
                <div class="grid-item">
                    <img class="img-4" src="./images/organisation/cfpb_logo.png" alt="cfpb logo" />
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!--page footer section-->
    <div class="page-footer-section">
        <div class="upper-footer-cont">
            <div class="column-group-1">
                <div class="footer-column">
                    <img class="site-logo" src="./images/icons/w_citadel_capital_logo.png" alt="thecitadelcapital">
                    <p class="text-block">
                        <span class="text-cont">
                            The Citadel Capital Partners is a leading investor in the global financial 
                            and alternative markets. Founded with the aim of ensuring capital appreciation 
                            as well as wealth protection.
                        </span>
                        <span class="more-link-cont">
                            <a href="<?php echo BASE_URL . 'about_us.html'; ?>">Learn More</a>
                        </span>
                    </p>
                </div>
                <div class="footer-column">
                    <h2 class="header">TERMS & POLICY</h2>
                    <ul class="link-list">
                        <li><a href="<?php echo BASE_URL . 'terms_and_condition.html'; ?>">Terms & Condition</a></li>
                        <li><a href="<?php echo BASE_URL . 'privacy_policy.html'; ?>">Privacy Policy</a></li>
                        <li><a href="<?php echo BASE_URL . 'cookies_policy.html'; ?>">Cookies Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="column-group-2">
                <div class="footer-column">
                    <h2 class="header">CONTACT US</h2>
                    <ul class="link-list">
                        <li>392 Triq il Kanun, Santa Venera, Malta</li>
                        <li>
                            <div class="list-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="list-data">contact@thecitadelcapital.com</div>
                        </li>
                        <li>
                            <div class="list-icon">
                                <i class="fas fa-phone-square-alt"></i>
                            </div>
                            <div class="list-data">+356 21250666</div>
                        </li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h2 class="header">SUBSCRIBE TO NEWSLETTER </h2>
                    <p class="text-block">
                        To get the latest updates on market trends subscribe to our newsletter. 
                        Be rest assured that your email address is private information and would 
                        not be published publicly.
                    </p>
                    <div class="newsletter-sub-cont">
                        <div id="footer-subscription-message" class="subscription-message remove-elem">
                            <div class="message"></div>
                            <div class="pointer"></div>
                        </div>
                        <div class="email-input-cont">
                            <input id="footer-sub-newsletter-input" type="email" name="email" placeholder="Your Email" spellcheck="false" />
                        </div>
                        <div class="sub-button-cont">
                            <button type="button" onclick="subscribeToNewsletter(this)">Sign Up</button>
                        </div>
                    </div>
                    <h2 class="header">CONNECT WITH US</h2>
                    <ul class="social-media-list-cont">
                        <li><a href="https://web.facebook.com/thecitadelcapitalpartners/"><i class="icon fab fa-facebook-square"></i></a></li>
                        <li><a href="https://twitter.com/JamieCitadel"><i class="icon fab fa-twitter-square"></i></a></li>
                        <li><a href="#"><i class="icon fab fa-linkedin"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="page-footer-hl"></div>
        <div class="lower-footer-cont">
            <div class="footer-bottom">Copyright &copy; Thecitadelcapital <?php echo date("Y");?>. All Rights Reserved</div>
        </div>
    </div>
</body>

</html>