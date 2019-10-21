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
    <title>CrytoWeb - Homepage</title>
    <link rel="icon" type="image/png" href="favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="favicon3.png" sizes="120x120">
    <meta name="description" content="CryptoWeb official website">
    <meta name="keywords" content="sign in, login">
    <meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=0.9">
    <link type="text/css" href="./fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="./styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="./styles/homepage.css">
    <script type="text/javascript" src="./js/utils.js"></script>
    <script type="text/javascript" src="./js/slider.js"></script>
    <script type="text/javascript" src="./js/homepage.js"></script>
</head>

<body>
    <div class="page-top-menu-cont absolute">
        <!--Header menu container-->
        <div class="page-cont-max-width">
            <nav>
                <div class="site-logo-cont">
                    <a href="./index.html">
                        <img class="site" src="./images/logo/img1.png" alt="Site Logo" />
                    </a>
                </div>
                <!--for desktop view-->
                <div class="main-menu-cont">
                    <ul class="ux-hr-menu fmt-link-med ux-txt-align-rt">
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">Home</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">Investment Packages</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">Testimoney</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">About Us</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">Contact Us</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">FAQ</a></li>
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
            <li><a class="link active" href="#">Investment Packages</a></li>
            <li><a class="link" href="#">Testimony</a></li>
            <li><a class="link" href="#">About US</a></li>
            <li><a class="link" href="#">Contact US</a></li>
            <li><a class="link" href="#">FAQ</a></li>
        </ul>
    </div>

    <!--page upper section-->
    <div class="page-upper-section">
        <div id="imageslider-cont"></div>
        <div class="headline-left-bg-img"></div>
        <!--headline container-->
        <div id="headline-cont" class="page-cont-max-width">
            <div class="headline">
                <h1>
                    <span class="ux-txt-white">The Crypto Currency </span>
                    </br>
                    <span class="ux-txt-chocolate ux-fs-px-19">You Ever Wanted</span>
                </h1>
            </div>
            <div class="sub-headline">
                <h2 style="line-height: 28px;">
                    <span class="ux-txt-white ux-fs-px-15 sub-headline-txt">
                        CRYENGINE makes the learning curve less steep with Full Source Code.
                        Clear tutorials, detailed documentation, and a strong development community.
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
            <p class="descr-txt ux-fs-px-20">
                You have come to the right place because we have the services you always want. It is our
                obligation to provide unparalleled services for your success. We are working 24 hours to
                improve our service to suit your preference at best.
            </p>
        </div>
        <div class="package-panel-cont">
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="fas fa-shield-alt ux-txt-grayblue"></i>
                </div>
                <div class="content">
                    <h3>Secure & Reliable</h3>
                    <p class="ux-fs-px-16">
                        Once you begin altering your site, you can easily check conversions by running Google
                        Analytics reports. More conversions must mean something’s working.
                    </p>
                </div>
            </div>
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="fas fa-box-open ux-txt-grayblue"></i>
                </div>
                <div class="content">
                    <h3>10% bonus on referal</h3>
                    <p class="ux-fs-px-16">
                        Marketers seeking to dominate their respective niches should be focused on the best website
                        layouts for maximum UX and conversions.
                    </p>
                </div>
            </div>
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="far fa-question-circle ux-txt-grayblue"></i>
                </div>
                <div class="content">
                    <h3>You already know</h3>
                    <p class="ux-fs-px-16">
                        If you want to infuse these best practices into your own website layout, here are a few steps to
                        follow.
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
            <div class="service-panel-cont">
                <div class="service-panel">
                    <div class="cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-chart-area ux-txt-grayblue"></i>
                        </div>
                        <h3 class="title ux-txt-grayblue">Cryptotrading</h3>
                        <p class="txt ux-fs-px-16">
                            It’s said that 94% of first-impressions are design driven. Not only that, but it takes less
                            than half a second for a visitor to form an opinion of your site.
                        </p>
                    </div>
                </div>
                <div class="service-panel">
                    <div class="cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-chart-pie ux-txt-grayblue"></i>
                        </div>
                        <h3 class="title ux-txt-grayblue">Equities and Hedge Funds management</h3>
                        <p class="txt ux-fs-px-16">
                            It’s said that 94% of first-impressions are design driven. Not only that, but it takes less
                            than half a second for a visitor to form an opinion of your site.
                        </p>
                    </div>
                </div>
                <div class="service-panel">
                    <div class="cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-cannabis ux-txt-grayblue"></i>
                        </div>
                        <h3 class="title ux-txt-grayblue">Cannabis Stocks Trading</h3>
                        <p class="txt ux-fs-px-16">
                            It’s said that 94% of first-impressions are design driven. Not only that, but it takes less
                            than half a second for a visitor to form an opinion of your site.
                        </p>
                    </div>
                </div>
                <div class="service-panel">
                    <div class="cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-hands-helping ux-txt-grayblue"></i>
                        </div>
                        <h3 class="title ux-txt-grayblue">OTC brokerage</h3>
                        <p class="txt">
                            It’s said that 94% of first-impressions are design driven. Not only that, but it takes less
                            than half a second for a visitor to form an opinion of your site.
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
                                            <li>Monthly ROI - ' . intval($row['monthlyROI']) . '%</li>
                                            <li>Monthly Bonuses - ' . ($row['bonuses'] == 1 ? 'Yes' : 'No') . '</li>
                                        </ul>
                                        <button onclick="investmentPkgsSelected(' . $counter . ')">Invest</button>
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
                <h1 class="ux-txt-white">About WebsiteName</h1>
                <!--<div class="about-crypto-headline-underline ux-bg-grayblue"></div>-->
            </div>
            <div class="about-crypto-img-cont">
                <img src="./images/background/about-us.jpg" alt="About cryptocurrency image" />
            </div>
            <div class="about-crypto-cont">
                <p>
                    Cryptopro investment is a financial group that help it client invest their cryptocurrency
                    to make profitable ROI in accordance with the investment package the client subscribe to.
                    We ... to ratify our client and help them on their journey to financial freedom while
                    cryptocurrency are digital asset ... (digital money). They are bit of valuable information
                    which can be transfered among users on a blockchain network. There are thousands of
                    cryptocurrency with different features and bitcoin is the oldest, which was introduced by
                    Satophi Nakemoto in 2008 and it is the most widely accepted crytocurrency.
                </p>
            </div>
        </div>
    </div>

    <!--Cryptocurrencies' price and trade statistics-->
    <div class="crypto-statistics-section-cont">
        <div class="crypto-statistics-section page-cont-max-width remove-elem">
            <div class="title-cont">
                <h1 class="ux-txt-grayblue">Cryptocurrency Live Price</h1>
            </div>
            <div id="crypto-st-table-cont">
                <div class="tab-btn-cont">
                    <div class="tab-btn active" onclick="changeCryptoPriceTo('usd', 0, this)">DOLLAR</div>
                    <div class="tab-btn" onclick="changeCryptoPriceTo('eur', 1, this)">EURO</div>
                </div>
                <div class="table-wrapper">
                    <table id="crypto-st-tbl"></table>
                </div>
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
            <div class="records-cont">
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
            <div class="sponsor-cont">
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
    <div class="ux-bg-grayblue">
        <div class="footer-header-cont">
            <a href="#" class="ux-txt-smokewhite txt-hover">
                <i class="fas fa-user-cog"></i><span>Admin Sign In</span>
            </a>
        </div>
        <div class="footer-cont">
            <div class="footer-col_1">
                <h4>CONTACT US</h4>
                <ul class="ux-vt-menu fmt-link-med">
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">
                            <i class="fas fa-map-marker-alt"></i><span>10A Enugu, Nigeria</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">
                            <!--<i class="fas fa-envelope"></i>-->officialwebsite@gmail.com
                        </a>
                    </li>
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">
                            <!--<i class="fas fa-phone-square-alt"></i>-->+234 8156654434
                        </a>
                    </li>
                </ul>
            </div>
            <div class="footer-col_2">
                <h4>TERMS</h4>
                <ul class="ux-vt-menu fmt-link-med">
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">Terms & Condition</a>
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
            <div class="footer-col_3">
                <h4>CONNECT WITH US</h4>
                <ul class="ux-hr-menu">
                    <li>
                        <a href="#" class="ux-txt-smokewhite txt-hover">
                            <i class="fab fa-facebook-square"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="ux-txt-smokewhite txt-hover">
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
            Copyright &copy; WebsiteName <?php echo date("Y");?>. All Rights Reserved
        </div>
    </div>
</body>

</html>