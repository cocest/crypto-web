<?php 

// import all the necessary liberaries
require_once '../includes/config.php';

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>The Citadel Capital Partners - Homepage</title>
    <link rel="icon" type="image/png" href="./images/icons/favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="./images/icons/favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="./images/icons/favicon3.png" sizes="120x120">
    <meta name="description" content="Thecitadelcapital official website">
    <meta name="keywords" content="thecitadelcapital, cryptocurrency, investment, funds, business">
    <meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=0.9">
    <link type="text/css" href="./fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="./styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="./styles/homepage.css">
    <link type="text/css" rel="stylesheet" href="./styles/c2chat_client.css">
    <script type="text/javascript" src="./js/utils.js"></script>
    <script type="text/javascript" src="./js/slider.js"></script>
    <script type="text/javascript" src="./js/homepage.js"></script>
    <script type="text/javascript" src="./js/smoothScroll.js"></script>
    <script type="text/javascript" src="./js/subscribe_newsletter.js"></script>
    <script type="text/javascript" src="./js/howler.js"></script>
    <script type="text/javascript" src="./js/c2chat/C2Chat.js"></script>
    <script type="text/javascript" src="./js/c2chat_client.js"></script>
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
        <div id="imageslider"></div>
        <div class="img-slider-cover"></div>
        <!--headline container-->
        <div id="headline-cont" class="page-cont-max-width">
            <div class="headline-wrapper-1 anim-in">
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
            <div class="headline-wrapper-2 anim-out">
                <div class="headline">
                    <h1>
                        <span class="ux-txt-white ux-fs-px-23 txt-shadow">Risk analysis, in-house research and team debates</span>
                    </h1>
                </div>
                <div class="sub-headline">
                    <h2>
                        <span class="ux-txt-white ux-fs-px-15 sub-headline-txt txt-shadow">
                            Enhancing AI data analysis with human driven insight to better manage 
                            complexity and evaluate hidden risks.
                        </span>
                    </h2>
                </div>
                <div class="action-btn-cont">
                    <a class="ux-btn custom-action-btn ux-bg-chocolate bg-hover ux-txt-white shadow"
                        href="./register.html">Create Account</a>
                </div>
            </div>
            <div class="headline-wrapper-3 anim-out">
                <div class="headline">
                    <h1>
                        <span class="ux-txt-white ux-fs-px-23 txt-shadow">Gain access to our team of professionals</span>
                    </h1>
                </div>
                <div class="sub-headline">
                    <h2>
                        <span class="ux-txt-white ux-fs-px-15 sub-headline-txt txt-shadow">
                            Our partnership provides a leeway to get in contact with some of the 
                            most experienced personnel, thus creating a platform for education in 
                            alternative investing.
                        </span>
                    </h2>
                </div>
                <div class="action-btn-cont">
                    <a class="ux-btn custom-action-btn ux-bg-chocolate bg-hover ux-txt-white shadow"
                        href="./register.html">Create Account</a>
                </div>
            </div>
            <div class="headline-wrapper-4 anim-out">
                <div class="headline">
                    <h1>
                        <span class="ux-txt-white ux-fs-px-23 txt-shadow">Explore investment opportunities around the globe</span>
                    </h1>
                </div>
                <div class="sub-headline">
                    <h2>
                        <span class="ux-txt-white ux-fs-px-15 sub-headline-txt txt-shadow">
                            Harness the power of the Blockchain technology to reach the 
                            most remote of promising assets.
                        </span>
                    </h2>
                </div>
                <div class="action-btn-cont">
                    <a class="ux-btn custom-action-btn ux-bg-chocolate bg-hover ux-txt-white shadow"
                        href="./register.html">Create Account</a>
                </div>
            </div>
        </div>
        <!--animated image indicator-->
        <div id="anim-img-indicator">
            <div id="img-ind-0" class="ux-bg-dimgray bg-hover ux-f-rd-corner" onclick="gotoImage(0)"></div>
            <div id="img-ind-1" class="ux-bg-white ux-f-rd-corner" onclick="gotoImage(1)"></div>
            <div id="img-ind-2" class="ux-bg-white ux-f-rd-corner" onclick="gotoImage(2)"></div>
            <div id="img-ind-3" class="ux-bg-white ux-f-rd-corner" onclick="gotoImage(3)"></div>
        </div>
    </div>

    <!--why choose us section-->
    <div class="package-section page-cont-max-width">
        <div class="package-headline ux-txt-align-ct">
            <h1 class="ux-txt-white">Why Choose Us ?</h1>
            <p class="descr-txt ux-fs-px-18 ux-txt-white">
                Our philosophy borders on a belief in the delivery of efficient services. 
                It is our obligation to provide unparalleled services and we are always 
                working to suit your preferences in the best way.
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
                        Our collaborative culture of debate, on-the-ground work and in-house 
                        research enable us to enhance AI data analysis with human-driven 
                        insight to better manage complexity and evaluate hidden risk. Our web 
                        technology also adheres to the best security practices.
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
                        charge only minimal fees upon withdrawal. Other management fees are based on our 
                        performance during the fiscal period.
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
                            <i class="fas fa-tractor ux-txt-lightgray"></i>
                        </div>
                        <div class="content ux-txt-lightgray">
                            <h3>Agriculture and Real Estate</h3>
                            <p class="ux-fs-px-18">
                                Agriculture has historically proven to be a stable investment. We invest 
                                in farm debts, agro startups, agro ETFs, farm land, REITs and mortgage 
                                debts.
                            </p>
                        </div>
                    </div>
                    <div class="service-cont">
                        <div class="featured-art-cont">
                            <i class="fab fa-bitcoin ux-txt-lightgray"></i>
                        </div>
                        <div class="content ux-txt-lightgray">
                            <h3>Digital Assets</h3>
                            <p class="ux-fs-px-18">
                                We explore the digital assets industry, investing in blockchain start 
                                ups and ICOs, digital currencies and digital currency derivatives 
                                trading to harness the ground breaking returns in the digital currency 
                                markets.
                            </p>
                        </div>
                    </div>
                    <div class="service-cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-gem ux-txt-lightgray"></i>
                        </div>
                        <div class="content ux-txt-lightgray">
                            <h3>Commodities and Mineral Rights</h3>
                            <p class="ux-fs-px-18">
                                We invest in hard and soft commodities futures, precious and base 
                                metal funds, and other related funds. Our portfolio also includes mining 
                                equities and startups.
                            </p>
                        </div>
                    </div>
                    <div class="service-cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-chart-pie ux-txt-lightgray"></i>
                        </div>
                        <div class="content ux-txt-lightgray">
                            <h3>Institutional Funds</h3>
                            <p class="ux-fs-px-18">
                                We expose our partners to hedge funds and private equity 
                                funds, providing K1s and regularly offering alphas.
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
                    To become a partner you must have completed the steps below.
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
                            <h3>Create an account</h3>
                            <p class="ux-fs-px-18">
                                Create an account for free after you have completed your online registration on this platform.
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
                            <h3>Develop a portfolio</h3>
                            <p class="ux-fs-px-18">
                                Liase with our agents and develop the perfect portfolio to match your financial goals.
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
                            <h3>Fund your account</h3>
                            <p class="ux-fs-px-18">
                                Deposit investment capital to fund your account and begin earning according to the package of your choice.
                            </p>
                        </div>
                    </div>
                    <div class="create-account-btn-cont">
                        <a class="ux-btn custom-action-btn ux-bg-chocolate bg-hover ux-txt-white shadow" href="./register.html">Create Account</a>
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
                    thus maintaining our promise of establishing profitable and everlasting partnerships.
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
                    At The Citadel Capital Partners, the interest of our partners is primary. We are delighted 
                    to help you through any difficulties you may encounter!
                </p>
                <ul class="contact-info-list">
                    <li>
                        <div class="list-icon">
                            <i class="fas fa-envelope ux-txt-white"></i>
                        </div>
                        <div class="list-data ux-txt-white">contact@thecitadelcapitalpartners.com</div>
                    </li>
                    <li>
                        <div class="list-icon">
                            <i class="fas fa-phone-square-alt ux-txt-white"></i>
                        </div>
                        <div class="list-data ux-txt-white">+44 7537 180465</div>
                    </li>
                    <li>
                        <div class="list-icon">
                            <i class="fas fa-map-marker-alt ux-txt-white"></i>
                        </div>
                        <div class="list-data ux-txt-white">Salesforce Tower, 110 Bishopsgate, London</div>
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
                            The Citadel Capital Partners is a leading investor in the global alternative markets. Founded with the aim of ensuring capital appreciation 
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
                        <li><a href="<?php echo BASE_URL . 'terms_and_condition.html'; ?>">Terms & Conditions</a></li>
                        <li><a href="<?php echo BASE_URL . 'privacy_policy.html'; ?>">Privacy Policy</a></li>
                        <li><a href="<?php echo BASE_URL . 'cookies_policy.html'; ?>">Cookies Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="column-group-2">
                <div class="footer-column">
                    <h2 class="header">CONTACT US</h2>
                    <ul class="link-list">
                        <li>Salesforce Tower, 110 Bishopsgate, London</li>
                        <li>
                            <div class="list-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="list-data">contact@thecitadelcapitalpartners.com</div>
                        </li>
                        <li>
                            <div class="list-icon">
                                <i class="fas fa-phone-square-alt"></i>
                            </div>
                            <div class="list-data">+44 7537 180465</div>
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
                        <li><a href="https://www.facebook.com/The-Citadel-Capital-Partners-103036931767611"><i class="icon fab fa-facebook-square"></i></a></li>
                        <li><a href="https://twitter.com/theccpartners"><i class="icon fab fa-twitter-square"></i></a></li>
                        <li><a href="https://www.linkedin.com/company/the-citadel-capital-partners"><i class="icon fab fa-linkedin"></i></a></li>
                        <li><a href="https://wa.me/447537180465?text=Welcome%20to%20Thecitadelcapitalpartners%20help%20desk"><i class="icon fab fa-whatsapp"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="page-footer-hl"></div>
        <div class="lower-footer-cont">
            <div class="footer-bottom">Copyright &copy; Thecitadelcapital <?php echo date("Y");?>. All Rights Reserved</div>
        </div>
    </div>

    <!--C2Chat window customer-->
    <div id="c2chat-client-chat-win" class="remove-elem">
        <div class="header-menu-bar">
            <div class="header-title-cont">
                <h2 class="header-title">Chat with an agent</h2>
            </div>
            <div class="more-btn-cont">
                <button class="more-btn">
                    <svg class="more-icon" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 100 100">
                        <path d="M 29.445675,48.906468 A 10.219845,10.219845 0 0 1 19.225831,59.126312 10.219845,10.219845 0 0 1 9.005987,48.906468 10.219845,10.219845 0 0 1 19.225831,38.686623 10.219845,10.219845 0 0 1 29.445675,48.906468 Z"></path>
                        <path d="M 60.493864,48.906468 A 10.219845,10.219845 0 0 1 50.27402,59.126312 10.219845,10.219845 0 0 1 40.054176,48.906468 10.219845,10.219845 0 0 1 50.27402,38.686623 10.219845,10.219845 0 0 1 60.493864,48.906468 Z"></path>
                        <path d="M 91.445675,48.906468 A 10.219845,10.219845 0 0 1 81.225831,59.126312 10.219845,10.219845 0 0 1 71.005987,48.906468 10.219845,10.219845 0 0 1 81.225831,38.686623 10.219845,10.219845 0 0 1 91.445675,48.906468 Z"></path>
                    </svg>
                </button>
            </div>
            <div class="close-btn-cont">
                <button class="close-btn" onclick="closeC2ChatWindow()">
                    <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 100 100">
                        <path d="m 83.3314 7.83637 c -0.8715 0.00077 -1.74258 0.335655 -2.40977 1.004 L 49.8851 39.9305 L 18.8486 8.84037 c -1.33442 -1.33671 -3.48428 -1.33812 -4.82097 -0.0039 L 8.66583 14.1887 c -1.33668 1.33442 -1.33886 3.48503 -0.0045 4.82173 L 39.715 50.1176 L 8.66141 81.2247 c -1.33441 1.33671 -1.33225 3.48734 0.0045 4.82175 l 5.36176 5.35222 c 1.33669 1.33442 3.48656 1.33301 4.82097 -0.004 L 49.8851 60.3048 L 80.9217 91.3949 c 1.33444 1.3367 3.48499 1.33811 4.82172 0.004 l 5.3618 -5.35222 c 1.3367 -1.33442 1.33812 -3.48503 0.005 -4.82174 L 60.0553 50.1177 L 91.1089 19.0106 c 1.3344 -1.3367 1.33298 -3.48732 -0.005 -4.82173 L 85.7421 8.83667 c -0.66832 -0.667205 -1.54047 -1.00105 -2.41195 -1.0003 Z" />
                    </svg>
                </button>
            </div>
            <div id="drop-down-more-menu" class="remove-elem">
                <ul class="item-list-cont">
                    <li class="close-menu" onclick="initNewC2Chat()">
                        Initiate new chat
                    </li>
                </ul>
            </div>
        </div>
        <div class="start-chat-form-cont">
            <div class="start-chat-form">
                <form name="c2chat-init-chat-form" onsubmit="return initChat(event)" autocomplete="off" spellcheck="false" novalidate>
                    <div class="input-cont">
                        <label class="input-label" for="name-input">Your Name</label>
                        <input id="name-input" type="text" name="name"/>
                        <div class="input-error-msg">Field is required</div>
                    </div>
                    <div class="input-cont">
                        <label class="input-label" for="email-input">Your Email</label>
                        <input id="email-input" type="text" name="email"/>
                        <div class="input-error-msg"></div>
                    </div>
                    <div class="input-cont">
                        <label class="input-label" for="message-input">Your Question</label>
                        <textarea id="message-input" type="text" name="message" placeholder="Your question here"></textarea>
                        <div class="input-error-msg"></div>
                    </div>
                    <div class="submit-btn-cont">
                        <button class="submit-btn" type="submit">Start chat</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="init-chat-wait-cont remove-elem">
            <h4 class="header-title">
                Please wait while we connect you to an available agent.
            </h4>
            <div class="wait-anim-cont">
                <div class="anim-dot dot-1"></div>
                <div class="anim-dot dot-2"></div>
                <div class="anim-dot dot-3"></div>
            </div>
        </div>
        <div class="notification-cont remove-elem">
            <h4 class="header-title"></h4>
            <button class="reconnect-btn" onclick="c2chatReconnect()">Connect again</button>
        </div>
        <div class="chat-frame-cont remove-elem">
            <div class="agent-profile-bar">
                <div class="profile-picture-indicator-cont">
                    <img class="profile-picture">
                    <div class="indicator online"></div>
                </div>
                <div class="profile-name-connect-status">
                    <h4 class="name"></h4>
                    <div class="status">online</div>
                </div>
            </div>
            <div class="chat-msg-cont"></div>
            <div class="send-msg-cont">
                <div class="chat-text-area-cont">
                    <div class="text-area" contenteditable="true" spellcheck="false"></div>
                    <div class="text-area-placeholder">Type a message here</div>
                </div>
                <div class="send-msg-menu-cont">
                    <div class="left-section">
                        <label for="attach-file-input">
                            <svg class="papar-clip-icon" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 100 100">
                                <path d="M 45.61431,45.618012 34.233036,64.282538 c -7.330842,12.022086 8.189263,21.385635 15.479778,9.42968 L 74.941979,32.338085 C 84.204228,17.065445 61.217969,3.1571553 51.845167,18.268404 L 25.008311,62.279042 C 13.75703,81.098806 44.430505,99.137858 55.871911,80.74314 L 68.711123,59.687695"></path>
                            </svg>
                        </label>
                        <input id="attach-file-input" type="file" name="file" accept="image/png, image/jpeg, image/gif">
                    </div>
                    <div class="right-section">
                        <button class="send-msg-btn">
                            <svg class="send-msg-icon" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 100 100">
                                <path d="m 15.171956,11.292872 71.80649,39.031767 -71.80649,38.746863 V 59.156644 L 51.360148,50.039735 15.171956,41.207729 Z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--C2Chat launch button-->
    <div id="c2chat-launch-btn">
        <div class="agent-online-indicator offline"></div>
        <svg class="roi-chart-up-icon" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 100 100">
            <defs>
                <style>
                    .sent-msg-icon {
                        fill: #ffffff;
                        fill-rule: evenodd;
                    }

                    .reply-msg-icon {
                        fill: #ffffff;
                        fill-opacity:0.60;
                    }
                </style>
            </defs>
            <path class="sent-msg-icon" d="m 59.154434,39.066594 a 4.4222396,4.4228773 0 0 1 -4.42224,4.422879 4.4222396,4.4228773 0 0 1 -4.42224,-4.422879 4.4222396,4.4228773 0 0 1 4.42224,-4.422877 4.4222396,4.4228773 0 0 1 4.42224,4.422877 M 45.528753,38.892319 a 4.4222396,4.4228773 0 0 1 -4.422239,4.422878 4.4222396,4.4228773 0 0 1 -4.422241,-4.422878 4.4222396,4.4228773 0 0 1 4.422241,-4.422877 4.4222396,4.4228773 0 0 1 4.422239,4.422877 m -13.451437,0 a 4.4222396,4.4228773 0 0 1 -4.42224,4.422878 4.4222396,4.4228773 0 0 1 -4.422241,-4.422878 4.4222396,4.4228773 0 0 1 4.422241,-4.422877 4.4222396,4.4228773 0 0 1 4.42224,4.422877 z M 13.885595,57.581012 V 19.831279 c 0.678237,-2.326233 2.082064,-3.201073 3.682371,-3.682902 l 47.180364,0.230182 c 1.297712,0.409478 2.606901,0.773045 3.452221,2.992357 l 0.230147,38.90064 c -0.460392,2.224895 -1.857985,2.575114 -3.222072,2.992359 H 36.670259 L 23.09152,75.535155 V 61.494096 h -6.444147 c -2.030179,-0.749488 -2.573584,-2.242471 -2.761778,-3.913084 z" />
            <path class="reply-msg-icon" d="M 86.900479,66.712523 V 28.962789 c -0.678238,-2.326233 -2.082064,-3.201073 -3.682371,-3.682902 l -47.180363,0.230182 c -1.297714,0.409478 -2.606902,0.773045 -3.452222,2.992358 l -0.230148,38.900639 c 0.460392,2.224895 1.857985,2.575114 3.222073,2.992359 H 64.115815 L 77.694554,84.666666 V 70.625607 H 84.1387 c 2.030181,-0.749488 2.573585,-2.242472 2.761779,-3.913084 z" />
        </svg>
    </div>
</body>

</html>
