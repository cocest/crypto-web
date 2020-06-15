<?php 

// import all the necessary liberaries
require_once '../../includes/config.php';

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>Thecitadelcapital - FAQS</title>
    <link rel="icon" type="image/png" href="../images/icons/favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="../images/icons/favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="../images/icons/favicon3.png" sizes="120x120">
    <meta name="description" content="Frequently ask question on thecitadelcapital.com">
    <meta name="keywords" content="thecitadelcapital, how, what, investment">
    <meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=0.9">
    <link type="text/css" href="../fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="../styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="../styles/faq.css">
    <script type="text/javascript" src="../js/utils.js"></script>
    <script type="text/javascript" src="../js/faq.js"></script>
</head>

<body>
    <!--page main menu container-->
    <div class="page-top-menu-cont">
        <div class="page-top-menu-wrapper sec-max-width">
            <nav>
                <div class="site-logo-cont">
                    <div class="site-logo-wrapper">
                        <a href="../index.html">
                            <img src="../images/icons/w_citadel_capital_logo.png" alt="thecitadelcapital" />
                        </a>
                    </div>
                </div>

                <!--for desktop view-->
                <div class="menu-links-cont">
                    <ul class="ux-hr-menu fmt-link-med">
                        <li><a class="link ux-txt-smokewhite txt-hover" href="../index.html">Home</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="../about_us.html">About Us</a></li>
                        <li><a class="ux-btn ux-bg-chocolate bg-hover ux-txt-white ux-rd-corner-1" href="../register.html">Get Started</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="../user/login.html">Sign In</a></li>
                    </ul>
                </div>

                <!--for mobile view-->
                <div class="menu-links-cont-mobi">
                    <ul class="ux-hr-menu fmt-link-med ux-txt-align-rt">
                        <li><a class="link ux-txt-smokewhite txt-hover" href="../user/login.html">Sign In</a></li>
                        <li>
                            <div class="drop-menu-icon-cont" toggle="0" onclick="dropMobileMenu(this)">
                                <img class="drop-menu-icon close" src="../images/icons/drop_menu_icon.png" />
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
            <li><a class="link active" href="../index.html">Home</a></li>
            <li><a class="link" href="../about_us.html">About Us</a></li>
            <li><a class="link" href="../register.html">Get Started</a></li>
        </ul>
    </div>

    <!--upper page section-->
    <div class="page-upper-section">
        <div class="page-upper-section-wrapper sec-max-width">
            <div class="bg-img"></div>
            <div class="container">
                <h1 class="headline">Frequently Asked Questions</h1>
                <h2 class="sub-headline">
                    <span class="txt">You can browse the questions below to find an answer. </span>
                    <span class="txt">If you can't find a related question, feel free to send us an email (contact@thecitadelcapital.com).</span>
                </h2>
            </div>
        </div>
    </div>

    <!--faq page section-->
    <div class="page-faq-section">
        <div class="page-faq-section-wrapper">
            <div class="q-header-cont expand" toggle="0" onclick="expandAnswer(this)">
                <div class="wrapper-1">
                    <h2>How do I purchase cryptocurrencies?</h2>
                </div>
                <div class="wrapper-2">
                    <div class="img-cont">
                        <img src="../images/icons/expand_and_collapse.png" alt="expand icon" />
                    </div>
                </div>
            </div>
            <div class="q-answer">
                <p>
                    You could purchase cryptocurrencies online via an exchange (<a href="https://coinbase.com" target="_blank">Coinbase.com</a> or 
                    <a href="https://coinmama.com" target="_blank">Coinmama.com</a>) or 
                    locally through a local vendor or ATM in your area. You could find local vendors in your country 
                    of residence on <a href="https://localbitcoins.com" target="_blank">localbitcoins.com</a>
                </p>
            </div>
            <div class="q-header-cont expand" toggle="0" onclick="expandAnswer(this)">
                <div class="wrapper-1">
                    <h2>How do I deposit/withdraw?</h2>
                </div>
                <div class="wrapper-2">
                    <div class="img-cont">
                        <img src="../images/icons/expand_and_collapse.png" alt="expand icon" />
                    </div>
                </div>
            </div>
            <div class="q-answer">
                <p>
                    Deposits and withdrawals are made according to the investment package of your choice. Select 
                    the cryptocurrency you would like to make deposits/withdrawals in and follow the prescribed steps.
                </p>
            </div>
            <div class="q-header-cont expand" toggle="0" onclick="expandAnswer(this)">
                <div class="wrapper-1">
                    <h2>Why can't I withdraw funds?</h2>
                </div>
                <div class="wrapper-2">
                    <div class="img-cont">
                        <img src="../images/icons/expand_and_collapse.png" alt="expand icon" />
                    </div>
                </div>
            </div>
            <div class="q-answer">
                <p>
                    You probably entered more funds than are available for withdrawal or your investments 
                    have not reached their maturity period. Please review your account.
                </p>
            </div>
            <div class="q-header-cont expand" toggle="0" onclick="expandAnswer(this)">
                <div class="wrapper-1">
                    <h2>How long does verification take?</h2>
                </div>
                <div class="wrapper-2">
                    <div class="img-cont">
                        <img src="../images/icons/expand_and_collapse.png" alt="expand icon" />
                    </div>
                </div>
            </div>
            <div class="q-answer">
                <p>
                    Verification takes up to but not more than 48 hrs. Ensure you uploaded the right details.
                </p>
            </div>
            <div class="q-header-cont expand" toggle="0" onclick="expandAnswer(this)">
                <div class="wrapper-1">
                    <h2>Why has my account not been verified?</h2>
                </div>
                <div class="wrapper-2">
                    <div class="img-cont">
                        <img src="../images/icons/expand_and_collapse.png" alt="expand icon" />
                    </div>
                </div>
            </div>
            <div class="q-answer">
                <p>
                    You might have entered wrong details or uploaded identification which could not be verified. 
                    Try registering again using the appropriate details. Ensure that your personal information 
                    you entered during registeration correspond with details in your uploaded identification.
                </p>
            </div>
            <div class="q-header-cont expand" toggle="0" onclick="expandAnswer(this)">
                <div class="wrapper-1">
                    <h2>Can I invest in more than two packages at once?</h2>
                </div>
                <div class="wrapper-2">
                    <div class="img-cont">
                        <img src="../images/icons/expand_and_collapse.png" alt="expand icon" />
                    </div>
                </div>
            </div>
            <div class="q-answer">
                <p>
                    You cannot invest in two packages simultaneously. You can invest on the same or different 
                    package when your current active investment has matured.
                </p>
            </div>
        </div>
    </div>

    <!--about us page footer-->
    <div class="page-footer-section ux-bg-grayblue">
        <div class="footer-cont">
            <div class="footer-col-1">
                <div class="site-logo-in-footer">
                    <img src="../images/icons/w_citadel_capital_logo.png" alt="thecitadelcapital">
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
            Copyright &copy; Thecitadelcapital <?php echo date("Y"); ?>. All Rights Reserved
        </div>
    </div>
</body>

</html>