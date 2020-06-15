<?php 

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

// set the handler
set_error_handler('customError');

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
</head>

<body>
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
                        <li><a class="link ux-txt-smokewhite txt-hover" onclick="scrollToSection(1, this)">Investment Packages</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="./about_us.html">About Us</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" onclick="scrollToSection(2, this)">Contact Us</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="./help/faq.html">FAQ</a></li>
                        <li><a class="ux-btn ux-bg-chocolate bg-hover ux-txt-white ux-rd-corner-1"
                                href="./register.html">Get
                                Started</a></li>
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
    <div class="drop-down-mobi-menu-cont hide shadow ux-bg-grayblue">
        <ul class="ux-vt-menu">
            <li><a class="link active" onclick="scrollToSection(0, this)">Home</a></li>
            <li><a class="link" onclick="scrollToSection(1, this)">Investment Packages</a></li>
            <li><a class="link" href="./about_us.html">About US</a></li>
            <li><a class="link" onclick="scrollToSection(2, this)">Contact US</a></li>
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
                    <span class="ux-txt-white ux-fs-px-18 txt-shadow">Cryptocurrencies Hedge Funds</span>
                    </br>
                    <span class="ux-txt-chocolate ux-fs-px-20 txt-shadow">Creating Profitable And Lasting Partnerships</span>
                </h1>
            </div>
            <div class="sub-headline">
                <h2 style="line-height: 28px;">
                    <span class="ux-txt-white ux-fs-px-15 sub-headline-txt">
                        We provide an alternative means of Holding, generating profits for our 
                        partners in both the short and long terms. You too can become one of 
                        our partners now!
                    </span>
                </h2>
            </div>
            <div class="action-btn-cont">
                <a class="ux-btn custom-action-btn ux-bg-chocolate bg-hover ux-txt-white ux-rd-corner-1 shadow"
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
            <h1 class="ux-txt-grayblue">Why Choose Us</h1>
            <p class="descr-txt ux-fs-px-18">
                You are at the right place. With our top notch service your success is guaranteed. 
                It is our obligation to provide unparalleled services and we are always working 
                to suit your preferences in the best way.
            </p>
        </div>
        <div class="package-panel-cont">
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="fas fa-shield-alt ux-txt-grayblue"></i>
                </div>
                <div class="content">
                    <h3>Secure & Reliable</h3>
                    <p class="ux-fs-px-18">
                        Our web technology adhered to the best security practices. All your sensitive 
                        data are encrypted to ward off hacks and other cyber attacks.
                    </p>
                </div>
            </div>
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="far fa-question-circle ux-txt-grayblue"></i>
                </div>
                <div class="content">
                    <h3>Great Client Relationship</h3>
                    <p class="ux-fs-px-18">
                        We offer 24/7 client support. We are always glad to aid you through 
                        any difficulties you may encounter. We have the interest of our partners at heart.
                    </p>
                </div>
            </div>
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="fas fa-box-open ux-txt-grayblue"></i>
                </div>
                <div class="content">
                    <h3>Referral Bonus</h3>
                    <p class="ux-fs-px-18">
                        We offer 2% referral bonuses for new partners that register through your referral id.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!--Our service section-->
    <div class="service-section-cont">
        <div class="service-section page-cont-max-width">
            <div class="service-headline ux-txt-align-ct">
                <h1 class="ux-txt-grayblue">Our Services</h1>
            </div>
            <div class="service-panel-cont ux-layout-grid columns-2">
                <div class="grid-item">
                    <div class="featured-art-cont">
                        <i class="fas fa-cloud ux-txt-grayblue"></i>
                    </div>
                    <div class="content">
                        <h3 class="ux-txt-grayblue">Cloud mining</h3>
                        <p class="ux-fs-px-18">
                            We offer private investors the opportunity to invest in cryptocurrency mining. We pool 
                            resources together with other global investors to make this achievable and profitable.
                        </p>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="featured-art-cont">
                        <i class="fas fa-hands-helping ux-txt-grayblue"></i>
                    </div>
                    <div class="content">
                        <h3 class="ux-txt-grayblue">Blockchain Startups and ICOs</h3>
                        <p class="ux-fs-px-18">
                            We identify new and budding companies with huge potential in the crypto space 
                            and invest in them to generate returns.
                        </p>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="featured-art-cont">
                        <i class="fas fa-chart-pie ux-txt-grayblue"></i>
                    </div>
                    <div class="content">
                        <h3 class="ux-txt-grayblue">Cryptocurrencies and Crypto derivatives Trading</h3>
                        <p class="ux-fs-px-18">
                            We use the latest strategies, techniques, and equipment to trade cryptocurrencies and 
                            crypto derivatives, generating outstanding returns with a 90% profitable return rate.
                        </p>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="featured-art-cont">
                        <i class="fas fa-cannabis ux-txt-grayblue"></i>
                    </div>
                    <div class="content">
                        <h3 class="ux-txt-grayblue">Cannabis stocks and REITS</h3>
                        <p class="ux-fs-px-18">
                            We invest in upcoming and profitable sectors like the cannabis industry and real 
                            estate trusts to generate outstanding ROIs.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Investment packages section-->
    <div class="inv-pkg-section-cont">
        <div class="inv-pkg-section page-cont-max-width">
            <div class="headline-cont ux-txt-align-ct">
                <h1 class="ux-txt-grayblue">Investment Packages</h1>
            </div>
            <div class="inv-pkg-list-cont ux-layout-grid columns-4">
                <?php 

                // generate investment packages from database

                // mysql configuration
                $db = $config['db']['mysql'];
        
                // enable mysql exception
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                try {
                    // connect to database
                    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

                    //check connection
                    if ($conn->connect_error) {
                        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
                    }

                    // fetch data from database
                    $query = 'SELECT * FROM crypto_investment_packages';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $counter = 0;

                    while ($row = $result->fetch_assoc()) {
                        $package = 
                            '<div class="grid-item">
                                <div class="package-cont">
                                    <div class="upper-sec">
                                        <div class="pkg-name-cont ' . strtolower($row['package']) . '">
                                            <h2>' . $row['package'] . '</h2>
                                        </div>
                                        <div class="header-img-cont ' . strtolower($row['package']) . '">
                                            <img src="./images/icons/package_icon_sprint.png" />
                                        </div>
                                    </div>
                                    <div class="lower-sec">
                                        <h2 class="price">$' . number_format($row['minAmount']) . ' - ' . ($row['maxAmount'] == 0 ? 'unlimited' : '$' . number_format($row['maxAmount'])) . '</h2>
                                        <ul class="benefit-list">
                                            <li>' . ($row['durationInMonth'] > 2 ? 'Quarterly ROI - ' : $row['durationInMonth'] . ' Month ROI - ') . intval($row['monthlyROI']) . '%</li>
                                            <li>Bonus - ' . (intval($row['bonus']) == 0 ? 'No' : intval($row['bonus']) . '%') . '</li>
                                        </ul>
                                        <button onclick="investmentPkgsSelected(' . $counter . ')">INVEST NOW</button>
                                    </div>
                                </div>
                             </div>';

                        echo $package;
                        $counter++; // increment by one
                    }

                } catch (mysqli_sql_exception $e) {
                    echo 'Mysql error: ' . $e->getMessage() . PHP_EOL;
                
                } catch (Exception $e) { // catch other exception
                    echo 'Caught exception: ' .  $e->getMessage() . PHP_EOL;
                }

                ?>
            </div>
            <div class="inv-pkg-exp-col-btn-cont">
                <button onclick="showMoreOrLessInvestmentPackages()">
                    <div class="img-cont expand">
                       <img src="./images/icons/expand_and_collapse.png" />
                    </div>
                    <div class="text-cont">See More</div>
                </button>
            </div>
        </div>
    </div>

    <!--About cryptocurrency section-->
    <div class="about-crypto-section-cont">
        <div class="about-crypto-section page-cont-max-width">
            <div class="about-crypto-headline-cont">
                <h1 class="ux-txt-white">About Us</h1>
                <!--<div class="about-crypto-headline-underline ux-bg-grayblue"></div>-->
            </div>
            <div class="about-crypto-img-cont">
                <img src="./images/background/about-us.jpg" alt="About cryptocurrency image" />
            </div>
            <div class="about-crypto-cont">
                <p>
                    The Citadel Capital Partners is a leading investor in the global alternative and 
                    financial markets. Founded in 1996 for the aim of ensuring capital appreciation 
                    as well as wealth protection, we have delivered investment returns to our partners 
                    for over a decade, aligning our interests for a profitable and lasting partnership. 
                    With over $10 billion in AUM across different funds and using the latest management strategies, 
                    technology, and trading techniques, we harness the brainpower of the smartest 
                    individuals, building on a platform of discipline, integrity, and teamwork to 
                    identify and utilize the latest opportunities to deliver groundbreaking returns.
                </p>
                <div class="lm-about-us-link-cont">
                    <a href="./about_us.html">
                        <span>Learn more</span><img src="./images/icons/more_icon.png" alt="more" />
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!--Cryptocurrencies' price and trade statistics-->
    <div class="crypto-statistics-section-cont">
        <div class="crypto-statistics-section page-cont-max-width remove-elem">
            <div class="title-cont">
                <h1 class="ux-txt-grayblue">Cryptocurrencies Live Prices</h1>
            </div>
            <div id="crypto-st-table-cont">
                <div class="tab-btn-cont">
                    <div class="tab-btn active" onclick="changeCryptoPriceTo('usd', 0, this)">USD</div>
                    <div class="tab-btn" onclick="changeCryptoPriceTo('eur', 1, this)">EURO</div>
                    <div class="tab-btn" onclick="changeCryptoPriceTo('gbp', 2, this)">GBP</div>
                </div>
                <div class="table-wrapper">
                    <table id="crypto-st-tbl"></table>
                </div>
            </div>
            <div class="powered-crypto-statistics">
                <img src="./images/icons/crypto_compare.png" alt="cryptocompare" />
            </div>
        </div>
        <div class="crypto-statistics-loading-cont">
            <div class="vt-bars-anim-cont">
                <div class="vt-bar-cont">
                    <div class="vt-bar-1"></div>
                </div>
                <div class="vt-bar-cont">
                    <div class="vt-bar-2"></div>
                </div>
                <div class="vt-bar-cont">
                    <div class="vt-bar-3"></div>
                </div>
            </div>
            <div class="loading-txt-cont ux-txt-grayblue ux-fs-px-24">
                Loading statistics
            </div>
        </div>
    </div>

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

    <!--Achievement section-->
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
            <?php } ?>
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
        </div>
    </div>

    <!--page footer section-->
    <div class="page-footer-section ux-bg-grayblue">
        <div class="footer-cont">
            <div class="footer-col-1">
                <div class="site-logo-in-footer">
                    <img src="./images/icons/w_citadel_capital_logo.png" alt="thecitadelcapital" />
                </div>
            </div>
            <div class="footer-col-2">
                <h4>TERMS</h4>
                <ul class="ux-vt-menu fmt-link-med">
                    <li>
                        <a href="<?php echo BASE_URL . 'terms_and_condition.html'; ?>" class="link ux-txt-smokewhite txt-hover">Terms &amp; Condition</a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL . 'privacy_policy.html'; ?>" class="link ux-txt-smokewhite txt-hover">Privacy Policy</a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL . 'cookies_policy.html'; ?>" class="link ux-txt-smokewhite txt-hover">Cookies Policy</a>
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
            Copyright &copy; Thecitadelcapital <?php echo date("Y");?>. All Rights Reserved
        </div>
    </div>
</body>

</html>