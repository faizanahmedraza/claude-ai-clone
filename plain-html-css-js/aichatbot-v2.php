<?php

/**
 * Template Name: LinkHomeAI Chatbot v2
 * User: Faizan Ahmed Raza
 * Date: 15/01/25
 * Time: 3:33 PM
 */

$recent_chats = [
    "Single family homes near good schools and parks in Irvine.",
    "Is Irvine a good place to live for young families?",
    "What are the average home prices in Irvine?",
    "What amenities does Irvine offer?",
    "Are there any parks near Irvine's downtown?",
    "How is the commute from Irvine to Los Angeles?"
];
$nav_links = [
    'Home' => 'https://www.linkhomeai.com',
    'Cash Offer' => 'https://us.linkhomeai.com/cash-offer/',
    'Flash Sale' => 'https://us.linkhomeai.com/flash-sale/',
    'Mortgage' => 'https://us.linkhomeai.com/mortgage/',
    'Contact Us' => 'https://us.linkhomeai.com/contact-us/'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot V2</title>
    <link rel="shortcut icon" href="https://www.linkhomeai.com/wp-content/uploads/2025/01/微信图片_20250114232457.jpg">
    <!-- Apple iPhone Icon -->
    <link rel="apple-touch-icon-precomposed"
        href="https://www.linkhomeai.com/wp-content/uploads/2025/01/微信图片_20250114232457.jpg">
    <!-- Apple iPhone Retina Icon -->
    <link rel="apple-touch-icon-precomposed" sizes="114x114"
        href="https://www.linkhomeai.com/wp-content/uploads/2025/01/微信图片_20250114232457.jpg">
    <!-- Apple iPhone Icon -->
    <link rel="apple-touch-icon-precomposed" sizes="72x72"
        href="https://www.linkhomeai.com/wp-content/uploads/2025/01/微信图片_20250114232457.jpg">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="https://www.linkhomeai.com/wp-content/themes/houzez/css/aichatbot-v2.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet.AwesomeMarkers CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.awesome-markers@2.0.5/dist/leaflet.awesome-markers.min.css"></head>

<body>
    <div class="fluid-container">
        <!-- Hamburger Menu for Medium and Small Screens -->
        <button id="hamburgerButton">☰</button>

        <!-- Reserved Space for Sidebar on Hover it -->
        <div class="default-sidebar" id="defaultSidebar">
            <span class="logo">LinkHomeAI</span>
            <div class="profile-button">
                <img src="https://avatar.iran.liara.run/public/10" alt="Profile" class="profile-image" />
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sub-container sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="logo color-p">LinkHomeAI</span>
                <button class="sidebar-toggler" id="toggleButton">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                        viewBox="0 0 256 256">
                        <path
                            d="M232,128a8,8,0,0,1-8,8H91.31l58.35,58.34a8,8,0,0,1-11.32,11.32l-72-72a8,8,0,0,1,0-11.32l72-72a8,8,0,0,1,11.32,11.32L91.31,120H224A8,8,0,0,1,232,128ZM40,32a8,8,0,0,0-8,8V216a8,8,0,0,0,16,0V40A8,8,0,0,0,40,32Z">
                        </path>
                    </svg>
                </button>
                <button class="cross-button" id="crossButton">✖</button>
            </div>
            <div class="sidebar-body">
                <div class="new-chat-button">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.10999 27C8.92999 27 8.76001 26.96 8.60001 26.9C8.43001 26.83 8.29 26.74 8.16 26.61C8.03 26.49 7.94 26.3499 7.87 26.1899C7.79999 26.0299 7.76001 25.8599 7.76001 25.6899L7.73001 23.04C7.34001 22.98 6.95001 22.8799 6.57001 22.7599C6.19001 22.6299 5.83001 22.48 5.48001 22.29C5.13001 22.1 4.79999 21.88 4.48999 21.63C4.17999 21.39 3.89 21.1199 3.63 20.82C3.37 20.52 3.13999 20.21 2.92999 19.87C2.72999 19.53 2.56001 19.18 2.42001 18.82C2.28001 18.45 2.17001 18.07 2.10001 17.69C2.03001 17.3 2 16.92 2 16.53V9.46995C2 9.03995 2.04 8.61995 2.12 8.19995C2.21 7.77995 2.34 7.36995 2.5 6.96995C2.67 6.57995 2.88 6.19995 3.12 5.84995C3.36 5.48995 3.64001 5.15995 3.95001 4.85995C4.26001 4.55995 4.59999 4.28995 4.95999 4.04995C5.32999 3.80995 5.70999 3.60995 6.10999 3.44995C6.51999 3.27995 6.94 3.15995 7.37 3.07995C7.79999 2.98995 8.23001 2.94995 8.67001 2.94995H13.3C13.46 2.94995 13.61 2.97995 13.76 3.03995C13.9 3.09995 14.03 3.17995 14.14 3.28995C14.25 3.39995 14.33 3.51995 14.39 3.65995C14.45 3.79995 14.48 3.94995 14.48 4.09995C14.48 4.25995 14.45 4.39995 14.39 4.54995C14.33 4.68995 14.25 4.80995 14.14 4.91995C14.03 5.02995 13.9 5.10995 13.76 5.16995C13.61 5.22995 13.46 5.25995 13.3 5.25995H8.67001C8.38001 5.25995 8.09999 5.27995 7.82999 5.33995C7.54999 5.38995 7.27999 5.46995 7.01999 5.57995C6.75999 5.67995 6.50999 5.80995 6.26999 5.96995C6.03999 6.11995 5.82 6.29995 5.62 6.48995C5.42 6.68995 5.23999 6.89995 5.07999 7.12995C4.92999 7.35995 4.78999 7.59995 4.67999 7.85995C4.57999 8.10995 4.49 8.37995 4.44 8.64995C4.38 8.91995 4.35999 9.18995 4.35999 9.46995V16.53C4.35999 16.81 4.38 17.08 4.44 17.36C4.5 17.63 4.58 17.9 4.69 18.16C4.8 18.42 4.93 18.67 5.09 18.9C5.25 19.13 5.43001 19.3499 5.64001 19.5499C5.84001 19.75 6.05999 19.92 6.29999 20.08C6.53999 20.24 6.79 20.37 7.06 20.47C7.32 20.58 7.6 20.66 7.88 20.72C8.16001 20.77 8.44001 20.7999 8.73001 20.7999C8.91001 20.7999 9.08 20.83 9.25 20.9C9.41 20.97 9.55999 21.0599 9.67999 21.18C9.80999 21.3099 9.91001 21.45 9.98001 21.61C10.05 21.77 10.08 21.94 10.09 22.11L10.1 23.74L13.08 21.61C13.84 21.07 14.69 20.7999 15.63 20.7999H19.32C19.61 20.7999 19.89 20.77 20.16 20.72C20.44 20.67 20.71 20.59 20.97 20.4799C21.23 20.3699 21.48 20.24 21.72 20.09C21.95 19.94 22.17 19.76 22.37 19.57C22.57 19.3699 22.75 19.16 22.91 18.93C23.07 18.7 23.2 18.46 23.31 18.2C23.41 17.95 23.5 17.68 23.55 17.41C23.61 17.14 23.63 16.87 23.63 16.59V12.94C23.63 12.79 23.66 12.64 23.72 12.5C23.78 12.36 23.87 12.23 23.98 12.13C24.09 12.02 24.22 11.93 24.36 11.88C24.51 11.82 24.66 11.79 24.82 11.79C24.97 11.79 25.12 11.82 25.27 11.88C25.41 11.93 25.54 12.02 25.65 12.13C25.76 12.23 25.85 12.36 25.91 12.5C25.97 12.64 26 12.79 26 12.94V16.59C26 17.02 25.95 17.44 25.87 17.86C25.78 18.28 25.66 18.69 25.49 19.08C25.32 19.48 25.11 19.8499 24.87 20.2099C24.63 20.57 24.35 20.9 24.04 21.2C23.73 21.5 23.39 21.7699 23.03 22.0099C22.67 22.2499 22.28 22.45 21.88 22.61C21.47 22.77 21.06 22.9 20.63 22.9799C20.2 23.07 19.76 23.11 19.32 23.11H16.4C15.47 23.11 14.62 23.3799 13.86 23.9199L9.91 26.74C9.67 26.91 9.39999 27 9.10999 27Z" fill="currentColor"></path>
                        <path d="M24.6805 5.14453H18.1874C17.5505 5.14453 17.0342 5.66086 17.0342 6.29778C17.0342 6.9347 17.5505 7.45102 18.1874 7.45102H24.6805C25.3175 7.45102 25.8338 6.9347 25.8338 6.29778C25.8338 5.66086 25.3175 5.14453 24.6805 5.14453Z" fill="currentColor"></path>
                        <path d="M22.6137 3.1804C22.6137 2.52848 22.0852 2 21.4333 2C20.7814 2 20.2529 2.52848 20.2529 3.1804V9.4168C20.2529 10.0687 20.7814 10.5972 21.4333 10.5972C22.0852 10.5972 22.6137 10.0687 22.6137 9.4168V3.1804Z" fill="currentColor"></path>
                    </svg>
                    New Chat
                </div>
                <ul>
                    <?php foreach ($nav_links as $label => $url) : ?>
                        <li><a class="nav-link" href="<?php echo $url; ?>">
                                <?php echo $label; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="sidebar-footer">
                <div class="profile-container">
                    <div class="profile-button" id="profileButton">
                        <img src="https://avatar.iran.liara.run/public/10" alt="Profile" class="profile-image" />
                    </div>
                    <div id="profileDropdown" class="profile-dropdown">
                        <ul>
                            <li><a href="#">View Profile</a></li>
                            <li><a href="#">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="sub-container chat-wrapper" id="chatWrapper">
            <div class="chat-container">
                <div class="plan-info">Using limited free plan <a href="#">Upgrade</a></div>

                <div class="greeting">🌟 Hello there! </div>

                <div id="mainView" class="prompt-view">
                    <div class="prompt-area">
                        <textarea id="promptInput" placeholder="How can LinkHomeAI help you today?"></textarea>
                        <div class="attachments">
                            <button id="attachFile">📎</button>
                            <button id="startChatSend">➤</button>
                        </div>
                    </div>

                    <div class="recent-chats">
                        <div class="header">
                            <span>Your recent chats</span>
                            <a class="" href="#">View all →</a>
                        </div>
                        <div class="chat-grid">
                            <?php foreach ($recent_chats as $chat) : ?>
                                <div class="chat-item"><?php echo $chat; ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div id="chatWindow" class="chat-hidden">
                    <div class="chat-header">New Chat</div>
                    <div class="chat-content"></div>
                    <div class="chat-input">
                        <textarea id="chatMessage" placeholder="Type a message..."></textarea>
                        <button id="chatSend">➤</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extended View -->
        <div class="sub-container extended-view" id="extendedView">
            <div class="ext-header">
                <button class="left-arrow" onclick="handleLeftArrow()"></button> <!-- ← -->
                <div class="right-buttons">
                    <div class="toggle-switch">
                        <input type="checkbox" id="listMapToggle" class="toggle-input">
                        <label for="listMapToggle" class="toggle-label">
                            <span class="list-text">List</span>
                            <span class="map-text">Map</span>
                        </label>

                    </div>
                    <button class="ext-cross-button" id="extCrossBtn">×</button>
                </div>
            </div>
            <div class="ext-body"></div>
        </div>
    </div>

    <!-- Load Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
	<!-- Leaflet.AwesomeMarkers JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet.awesome-markers@2.0.5/dist/leaflet.awesome-markers.min.js"></script>
    <!-- Custom JS -->
    <script src="https://www.linkhomeai.com/wp-content/themes/houzez/js/aichatbot-v2.js"></script>
</body>

</html>