<?php 

// import all the necessary liberaries
require_once '../includes/config.php';

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>Thecitadelcapital - About Us</title>
    <link rel="icon" type="image/png" href="./images/icons/favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="./images/icons/favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="./images/icons/favicon3.png" sizes="120x120">
    <meta name="description" content="About the thecitadelcapital, our values and people behind it.">
    <meta name="keywords" content="thecitadelcapital, about, cryptocurrency, investment">
    <meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=0.9">
    <link type="text/css" href="./fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="./styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="./styles/about_us.css">
    <script type="text/javascript" src="./js/utils.js"></script>
    <script type="text/javascript" src="./js/about_us.js"></script>
</head>

<body class="theme-bg-color">
    <!--page main menu container-->
    <div class="page-top-menu-cont theme-bg-color">
        <div class="page-top-menu-wrapper sec-max-width">
            <nav>
                <div class="site-logo-cont">
                    <div class="site-logo-wrapper">
                        <a href="./index.html">
                            <img src="./images/icons/w_citadel_capital_logo.png" alt="thecitadelcapital" />
                        </a>
                    </div>
                </div>

                <!--for desktop view-->
                <div class="menu-links-cont">
                    <ul class="ux-hr-menu fmt-link-med">
                        <li><a class="link ux-txt-smokewhite txt-hover" href="./index.html">Home</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="./help/faq.html">FAQ</a></li>
                        <li><a class="reg-btn ux-btn" href="./register.html">Get Started</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="./user/login.html">Sign In</a></li>
                    </ul>
                </div>

                <!--for mobile view-->
                <div class="menu-links-cont-mobi">
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
    <div class="drop-down-mobi-menu-cont hide shadow ux-bg-grayblue">
        <ul class="ux-vt-menu">
            <li><a class="link active" href="./index.html">Home</a></li>
            <li><a class="link" href="./help/faq.html">FAQ</a></li>
            <li><a class="link" href="./register.html">Get Started</a></li>
        </ul>
    </div>

    <!--page upper section-->
    <div class="page-upper-section">
        <!--add hue or mood effect to the background image-->
        <div class="bg-img-cover"></div>
        <div class="page-upper-section-wrapper sec-max-width">
            <div class="headline-cont">
                <h1 class="headline txt-shadow">Your success is our success.</h1>
                <h3 class="sub-headline txt-shadow">We are committed to creating long lasting and profitable partnerships thus aligning our goals with those of our partners.</h3>
            </div>
        </div>
    </div>

    <!--about us section-->
    <div class="page-about-us-section">
        <div class="about-us-cont">
           <h1 class="caption ux-txt-white">About Us</h1>
           <p class="content ux-txt-white">
                The Citadel Capital Partners is a leading investor in the global financial 
                and alternative markets. Founded with the aim of ensuring capital appreciation 
                as well as wealth protection, we have consistently delivered returns to our 
                partners for more than a decade, thus maintaining our promise of establishing 
                profitable and everlasting partnerships. Using the latest management strategies, 
                technology and trading techniques, our team consisting of individuals with 
                extensive experience in hedge funds,private equity,equity capital markets 
                advisory and alternatives investments has built on a platform of integrity, 
                discipline and teamwork to identify and utilize the latest trends and market 
                sentiments to deliver outstanding returns.
            </p>
        </div>
    </div>

    <!--our values section-->
    <div class="page-our-values-section">
        <div class="our-values-cont sec-max-width">
            <h1 class="caption">Our Values</h1>
            <div class="our-values-wrapper ux-layout-grid columns-3">
                <div class="grid-item">
                    <h3 class="sub-caption">Integrity</h3>
                    <p class="content">
                        We have built a reputation of integrity for over a decade of fulfilling our 
                        promise of outstanding returns to our partners and investors.
                    </p>
                </div>
                <div class="grid-item">
                    <h3 class="sub-caption">Diligence</h3>
                    <p class="content">
                        Our team is made up of the most disciplined and dedicated individuals who 
                        share the love for their occupation. They also invest capital alongside that 
                        of our partners and as such are bent on generating the best returns.
                    </p>
                </div>
                <div class="grid-item">
                    <h3 class="sub-caption">Team Work</h3>
                    <p class="content">
                        All members work as a unit with the single goal of generating profits for 
                        our partners and investors.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!--our team section-->
    <div class="page-our-team-section">
        <div class="our-team-cont sec-max-width">
            <h1 class="caption ux-txt-white">Our Team</h1>
            <div class="our-team-wrapper ux-layout-grid columns-3">
                <div class="grid-item">
                    <div class="profile-pic-cont">
                        <img src="<?php echo BASE_URL; ?>images/team/img_3.jpg" />
                    </div>
                    <div class="content-cont">
                        <h3 class="name">Matteo Koch</h3>
                        <h4 class="position">Chief Investment Officer</h4>
                        <p class="bio">
                            A seasoned venture capitalist and tech investor, Matteo started his career 
                            trading stocks and investing in treasury bonds before cementing his career 
                            in real estate and private finance. He is a graduate of the University of 
                            Zurich and actively donates to charity.
                        </p>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="profile-pic-cont">
                        <img src="<?php echo BASE_URL; ?>images/team/img_1.jpg" />
                    </div>
                    <div class="content-cont">
                        <h3 class="name">Ella Garcia</h3>
                        <h4 class="position">Chief Risk Officer</h4>
                        <p class="bio">
                            Ella is a Harvard graduate and she stands out as an analytical and results 
                            driven individual. She is one of the pioneer employees of the firm and is a 
                            community leader in her native state of Texas.
                        </p>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="profile-pic-cont">
                        <img src="<?php echo BASE_URL; ?>images/team/img_4.jpg" />
                    </div>
                    <div class="content-cont">
                        <h3 class="name">Matthew Spencer</h3>
                        <h4 class="position">Chief Operating Officer</h4>
                        <p class="bio">
                            He is the chief operating officer and functions to manage the firms 
                            operations all around the globe. A graduate of the University of Leeds, 
                            Matthew became an employee of Citadel in 1999. He has since created a 
                            reputation of being a great leader and businessman. He has been the 
                            recipient of several awards and has served on different committees 
                            around the world.
                        </p>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="profile-pic-cont">
                        <img src="<?php echo BASE_URL; ?>images/team/img_5.jpg" />
                    </div>
                    <div class="content-cont">
                        <h3 class="name">Jamie Hernandez</h3>
                        <h4 class="position">Chief Marketing Officer</h4>
                        <p class="bio">
                            A graduate of the New York State University, Jamie holds a DBA in 
                            business administration. He is a seasoned investor and has huge 
                            holdings in cryptocurrencies. He is a dedicated philanthropist and 
                            has donated millions to several honorable causes.
                        </p>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="profile-pic-cont">
                        <img src="<?php echo BASE_URL; ?>images/team/img_2.jpg" />
                    </div>
                    <div class="content-cont">
                        <h3 class="name">Riku Takashi</h3>
                        <h4 class="position">Chief Technical Officer</h4>
                        <p class="bio">
                            Riku is a graduate of the University of Osaka and has dedicated his 
                            life's work to research and discovery of new trends in technology and 
                            has merged that knowledge with our goals to generate outstanding ROIs.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--page footer section-->
    <div class="page-footer-section">
        <div class="upper-footer-cont">
            <div class="column-group-1">
                <div class="footer-column">
                    <img class="site-logo" src="./images/icons/w_citadel_capital_logo.png" alt="thecitadelcapital">
                    <p class="text-block">
                        <span class="text-cont">
                            The Citadel Capital Partners is a leading investor in the global 
                            financial and alternative markets. Founded with the aim of ensuring 
                            capital appreciation as well as wealth protection.
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
                        <div class="email-input-cont">
                            <input type="email" name="email" placeholder="Your Email" />
                        </div>
                        <div class="sub-button-cont">
                            <button type="button">Sign Up</button>
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