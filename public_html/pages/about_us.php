<?php 

// import all the necessary liberaries
require_once '../includes/config.php';

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>The Citadel Capital Partners - About Us</title>
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
    <script type="text/javascript" src="./js/subscribe_newsletter.js"></script>
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
        <div class="about-us-video-frame">
            <iframe width="500" height="315" src="https://www.youtube.com/embed/KY_tzaZMUII" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <div class="about-us-cont">
            <h1 class="caption ux-txt-white">About Us</h1>
            <p class="content ux-txt-white">
                The Citadel Capital Partners is a leading investor in the global alternative markets. 
                Founded with the aim of ensuring capital appreciation as well 
                as wealth protection, we have consistently delivered returns to our partners thus 
                maintaining our promise of establishing profitable and everlasting partnerships.
                Using the latest management strategies, trading techniques and technology, our team 
                consisting of highly educated and qualified personnel, with extensive experience in 
                hedge funds, private equity and alternative assets management and advisory, has 
                built on a platform of integrity, discipline and teamwork to identify and utilize 
                the latest trends and market sentiments to deliver outstanding returns to our 
                partners in both the long and short terms. We have mastered the alchemy of fusing AI 
                data analysis with human insight and also take pride in our superior research process, 
                thus achieving maximum levels of efficiency and risk management. With the advent of 
                the Blockchain technology, digital assets and Decentralized Finance (DeFi), The 
                Citadel Capital Partners has recognized the importance of these as the future of 
                finance and has capitalized on them,exploring different investment opportunities 
                around the globe and subverting the problems plaguing the contemporary financial 
                industry.
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
                        We work to maintain a reputation of integrity by fulfilling our promise 
                        of generating outstanding returns for our partners. We also believe in 
                        transparency and offer 24/7 client support.
                    </p>
                </div>
                <div class="grid-item">
                    <h3 class="sub-caption">Diligence</h3>
                    <p class="content">
                        Our team is made up of the most disciplined and dedicated individuals 
                        who share the love for their occupation. They also invest capital 
                        alongside that of our partners and as such are bent on generating 
                        the best returns.
                    </p>
                </div>
                <div class="grid-item">
                    <h3 class="sub-caption">Team Work</h3>
                    <p class="content">
                        All team members work as a unit with the sole aim of profit generation.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!--research process section-->
    <div class="research-process-section">
        <div class="research-process-cont sec-max-width">
            <h1 class="caption">Our Strategy</h1>
            <div class="research-process-wrapper ux-layout-grid columns-3">
                <div class="grid-item">
                    <h3 class="sub-caption">Qualitative Analysis</h3>
                    <p class="content">
                        Our research team scrutinizes the legacy of the funds and firms 
                        in our portfolio, reviewing history, team members, and other third parties.
                    </p>
                </div>
                <div class="grid-item">
                    <h3 class="sub-caption">Quantitative Analysis</h3>
                    <p class="content">
                        The performance of the assets and funds in our portfolio is constantly 
                        reviewed and compared using mathematical and statistical models, identifying 
                        the best performers before investment distribution is made.
                    </p>
                </div>
                <div class="grid-item">
                    <h3 class="sub-caption">Legal Compliance</h3>
                    <p class="content">
                        We make sure that all assets in our portfolio comply with the legalities 
                        set down by the governing bodies of the areas in which they are located. 
                        We constantly revise our paperwork, making sure they are up to date and 
                        in compliance with all such legalities.
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
                            Matteo is a graduate of the University of Zurich and an alumni of the Harvard 
                            Business School. He started his career in the equity markets before 
                            branching out into commodities, real estate and other alternatives. 
                            He is an established venture capitalist and has been the recipient of numerous 
                            awards around the globe.
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
                            Ella graduated from Harvard University with magna cum laude and stands out as a 
                            highly disciplined and analytically driven individual. She has distinguished 
                            herself in her rationality and has steered a lot of firms to greater heights. 
                            She is a philanthropist in her native state of Texas.
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
                            Matthew is the chief operating officer and functions to manage the operations 
                            of the firm around the globe.A graduate of Dartmouth College,he migrated to 
                            the United States and became an employee of the Citadel. He has since then created 
                            a reputation of being a great leader and an astute businessman. He has been the 
                            recipient of several global awards and has served on different committees around the world.
                        </p>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="profile-pic-cont">
                        <img src="<?php echo BASE_URL; ?>images/team/img_5.jpg" />
                    </div>
                    <div class="content-cont">
                        <h3 class="name">Jamie Hernandez</h3>
                        <h4 class="position">Chief Public Relations Officer</h4>
                        <p class="bio">
                            He is a graduate of the New York State University and holds a DBA in business 
                            administration. He is a seasoned investor and has served as an arbitrator in 
                            different corporate crises globally. A dedicated philanthropist, he has donated 
                            to several honorable causes.
                        </p>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="profile-pic-cont">
                        <img src="<?php echo BASE_URL; ?>images/team/img_2.jpg" />
                    </div>
                    <div class="content-cont">
                        <h3 class="name">J.R. Takashi</h3>
                        <h4 class="position">Chief Technology Officer</h4>
                        <p class="bio">
                            He is a graduate of the Massachusetts Institute of Technology and has dedicated 
                            his life work to research and discovery of new trends in technology and has 
                            merged that knowledge with our goal of generating outstanding returns for our partners.
                        </p>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="profile-pic-cont">
                        <img src="<?php echo BASE_URL; ?>images/team/img_6.jpg" />
                    </div>
                    <div class="content-cont">
                        <h3 class="name">Robert Kramer</h3>
                        <h4 class="position">Chief Legal Advisor</h4>
                        <p class="bio">
                            Rob is the Chief Legal Advisor and serves to manage all international 
                            legalities concerning The Citadel Capital Partners. Prior to joining 
                            the Citadel, Rob attained fame as a top insurance attorney. He is a 
                            fellow of the American Bar Association and graduated with honors from 
                            the Vermont Law School.
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
                            The Citadel Capital Partners is a leading investor in the global alternative markets. 
                            Founded with the aim of ensuring capital appreciation as well as wealth protection.
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
                        <li><a href="https://web.facebook.com/thecitadelcapitalpartners/"><i class="icon fab fa-facebook-square"></i></a></li>
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
</body>

</html>
