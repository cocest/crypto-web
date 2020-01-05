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

<body>
    <!--page main menu container-->
    <div class="page-top-menu-cont ux-bg-grayblue">
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
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">FAQ</a></li>
                        <li><a class="ux-btn ux-bg-chocolate bg-hover ux-txt-white ux-rd-corner-1" href="./register.html">Get Started</a></li>
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
            <li><a class="link" href="./about_us.html">About US</a></li>
            <li><a class="link" href="#">FAQ</a></li>
            <li><a class="link" href="./register.html">Get Started</a></li>
        </ul>
    </div>

    <!--page upper section-->
    <div class="page-upper-section">
        <!--add hue or mood effect to the background image-->
        <div class="bg-img-cover"></div>
        <div class="page-upper-section-wrapper sec-max-width">
            <div class="headline-cont">
                <h1 class="headline txt-shadow">Your success is our desire.</h1>
                <h3 class="sub-headline txt-shadow">We are one family ready to take risk because that is where we strive.</h3>
            </div>
        </div>
    </div>

    <!--about us section-->
    <div class="page-about-us-section">
        <div class="about-us-cont">
           <h1 class="caption ux-txt-grayblue">About Us</h1>
           <p class="content">
                Citadel Capital Partners is one of the leading investors in the global alternative 
                and financial markets. Founded in 1996 for the aim of ensuring capital appreciation 
                as well as wealth protection, we have delivered investment returns to our partners 
                for over a decade, aligning our interests for a profitable and lasting partnership. 
                With over $10 billion in AUM across different funds and using the latest management strategies, 
                technology, and trading techniques, we harness the brainpower of the smartest 
                individuals, building on a platform of discipline, integrity, and teamwork to 
                identify and utilize the latest opportunities to deliver groundbreaking returns. 
                With the establishment and boom of cryptocurrencies, Citadel Capital identifies 
                the promise and potential within the industry and has since 2014 served as an 
                alternative to just holding thus generating profits for our partners in both the 
                long and short terms.
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
                        share the love for their occupation and invest their energy as well as time 
                        to achieve great returns.
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
            <h1 class="caption ux-txt-grayblue">Our Team</h1>
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
                        <h4 class="position">Marketing Officer</h4>
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
                            life’s work to research and discovery of new trends in technology and 
                            has merged that knowledge with our goals to generate outstanding ROIs.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--about us page footer-->
    <div class="page-footer-section ux-bg-grayblue">
        <div class="footer-cont">
            <div class="footer-col-1">
                <div class="site-logo-in-footer">
                    <img src="./images/icons/w_citadel_capital_logo.png" alt="thecitadelcapital">
                </div>
            </div>
            <div class="footer-col-2">
                <h4>TERMS</h4>
                <ul class="ux-vt-menu fmt-link-med">
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">Terms &amp; Condition</a>
                    </li>
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">Privacy Policy</a>
                    </li>
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">Terms of Use</a>
                    </li>
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">Cookies Statement</a>
                    </li>
                </ul>
            </div>
            <div class="footer-col-3">
                <h4>CONTACT US</h4>
                <ul class="ux-vt-menu fmt-link-med">
                    <li>
                        <div class="address-in-footer">
                            392 Triq il Kanun, Santa Venera, Malta
                        </div>
                    </li>
                    <li>
                        <div class="contact-in-footer">
                            <i class="fas fa-envelope"></i><span>contact@thecitadelcapital.com</span>
                        </div>
                    </li>
                    <li>
                        <div class="phone-in-footer">
                            <i class="fas fa-phone-square-alt"></i><span>+356 21250666</span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="footer-col-4">
                <h4>CONNECT WITH US</h4>
                <ul class="ux-hr-menu">
                    <li>
                        <a href="https://web.facebook.com/thecitadelcapitalpartners/" class="ux-txt-smokewhite txt-hover">
                            <i class="fab fa-facebook-square"></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://twitter.com/JamieCitadel" class="ux-txt-smokewhite txt-hover">
                            <i class="fab fa-twitter-square"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="ux-txt-smokewhite txt-hover">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer-hr-line"></div>
        <div class="footer-base-cont ux-bg-grayblue ux-txt-white">
            Copyright © Thecitadelcapital 2020. All Rights Reserved
        </div>
    </div>
</body>

</html>