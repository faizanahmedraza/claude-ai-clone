document.addEventListener("DOMContentLoaded", function () {
    const defaultSidebar = document.getElementById('defaultSidebar');
    const sidebar = document.getElementById('sidebar');
    const hamburgerButton = document.getElementById('hamburgerButton');
    const toggleButton = document.getElementById('toggleButton');
    const crossButton = document.getElementById('crossButton');
    const profileDropdown = document.getElementById('profileDropdown');
    const promptInput = document.getElementById("promptInput");
    const chatMessageInput = document.getElementById("chatMessage");
    const startChatSend = document.getElementById("startChatSend");
    const mainView = document.getElementById("mainView");
    const chatWrapper = document.getElementById('chatWrapper');
    const chatWindow = document.getElementById("chatWindow");
    const chatSendBtn = document.getElementById("chatSend");
    const listMapSwitch = document.getElementById("listMapToggle");
    const extViewCrossBtn = document.getElementById("extCrossBtn");
    const mapLoader = document.getElementById("mapLoader");
    const chatContainer = document.querySelector(".chat-container");
    const activeExtendedView = document.querySelector(".extended-view.active");
    const planInfo = document.querySelector(".plan-info");
    const greeting = document.querySelector(".greeting");
    const chatContent = document.querySelector(".chat-content");
    const chatHeader = document.querySelector(".chat-header");
    const extendedView = document.getElementById("extendedView");
    const chatItems = document.querySelectorAll('.chat-item');
    const newChatButton = document.querySelector(".new-chat-button");
    const mediumSizeWindows = window.matchMedia('(max-width: 991.98px)');
    let messages = [];
    let isSidebarStatic = false;
    let messageText = "";
    let isExtendedViewOpened = false;
    let generatingResponse = false;
    let map;

    // Function to reset sidebar state on screen size change
    function resetScreenState() {
        if (mediumSizeWindows.matches) {
            isSidebarStatic = false;
            sidebar.style.visibility = 'hidden';
            sidebar.style.width = '0';
            sidebar.style.borderRadius = '';
            sidebar.style.margin = '';
            defaultSidebar.style.display = 'none';

            if (isExtendedViewOpened) {
                chatWrapper.style.setProperty("max-width", "0%", "important");
                chatContainer.style.setProperty("max-width", "0%", "important");
                chatWindow.style.setProperty("display", "none", "important");
                extendedView.style.setProperty("display", "flex");
                extendedView.style.setProperty("width", "100%");
                if (activeExtendedView) {
                    activeExtendedView.style.setProperty("width", "100%", "important");
                }
            } else {
                extendedView.style.setProperty("display", "none");
            }
        } else {
            sidebar.classList.remove('active');
            defaultSidebar.style.display = 'flex';
            chatWrapper.style.marginLeft = '0%';

            if (isExtendedViewOpened) {
                chatWrapper.style.setProperty("max-width", "45%", "important")
                chatContainer.style.setProperty("max-width", "100%", "important");
                chatWindow.style.setProperty("display", "flex", "important");
                extendedView.style.setProperty("display", "flex");
                extendedView.style.setProperty("width", "50%");
                if (activeExtendedView) {
                    activeExtendedView.style.setProperty("width", "50%", "important");
                }
            } else {
                extendedView.style.setProperty("display", "none");
            }
        }
    }

    // Reset sidebar, chat windows state on screen size change
    window.addEventListener('resize', resetScreenState);

    // Toggle sidebar on hamburger button click
    hamburgerButton.addEventListener('click', () => {
        sidebar.style.visibility = '';
        sidebar.style.width = '';
        sidebar.classList.add('active');
        if (sidebar.classList.contains('active')) {
            defaultSidebar.style.display = 'none';
        }
    });

    // Sidebar hover effect (for large screens)
    defaultSidebar.addEventListener('mouseenter', () => {
        if (!isSidebarStatic) {
            sidebar.style.visibility = 'visible';
            sidebar.style.width = '14%';
        }
    });

    if (!mediumSizeWindows.matches) {
        sidebar.addEventListener('mouseleave', () => {
            if (!isSidebarStatic && !sidebar.classList.contains('active')) {
                sidebar.style.visibility = 'hidden';
                sidebar.style.width = '0';
            }
        });
    }

    newChatButton.addEventListener('click', () => window.location.reload());

    // Hide sidebar on close (for medium and small screens)
    crossButton.addEventListener('click', () => {
        sidebar.classList.remove('active');
        sidebar.style.visibility = 'hidden';
        sidebar.style.width = '0';
    });

    // Toggle sidebar static state (for large screens)
    toggleButton.addEventListener('click', () => {
        if (!mediumSizeWindows.matches) {
            isSidebarStatic = !isSidebarStatic;

            if (isSidebarStatic) {
                sidebar.style.visibility = 'visible';
                sidebar.style.width = '14%';
                chatWrapper.style.marginLeft = '14%';
                defaultSidebar.style.display = 'none';
                sidebar.style.borderRadius = '0';
                sidebar.style.margin = '0';
                sidebar.style.height = '100%';
            } else {
                sidebar.style.visibility = 'hidden';
                sidebar.style.width = '';
                sidebar.style.borderTopRightRadius = '';
                sidebar.style.borderBottomRightRadius = '';
                sidebar.style.margin = '';
                defaultSidebar.style.display = 'flex';
                chatWrapper.style.marginLeft = '0%';
            }
        }
    });

    document.getElementById('profileButton').addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', () => profileDropdown.style.display = 'none');

    promptInput.addEventListener("input", function () {
        startChatSend.style.display = promptInput.value.trim() ? "inline-block" : "none";
    });

    startChatSend.addEventListener("click", promptInputHandle);

    chatSendBtn.addEventListener("click", handleChat);

    chatMessageInput.addEventListener("keypress", (event) => event.key === "Enter" && handleChat());

    chatItems.forEach((item) => item.addEventListener('click', function (event) {
        const clickedItem = event.target;
        const chatTitle = clickedItem.textContent;
        promptInput.value = chatTitle;
        promptInputHandle();
    }));

    function promptInputHandle() {
        if (promptInput.value.trim()) {
            mainView.style.display = "none";
            planInfo.style.display = "none";
            greeting.style.display = "none";
            chatWindow.style.display = "flex";
            startChatSend.style.display = "none";
            handleChat();
        }
    }

    function handleChat() {
        messageText = chatMessageInput.value.trim() || promptInput.value.trim();
        if (!messageText) return;

        chatSendBtn.disabled = true;
        chatSendBtn.classList.add("disabled");
        chatMessageInput.value = "";
        promptInput.value = "";

        addUserMessage(messageText);
        showThinkingEffect();
        // Simulate bot response after a delay
        setTimeout(() => {
            addBotMessage(messageText);
        }, 600);
    }

    function addUserMessage(text) {
        const userMessage = {
            type: 'user',
            text: text,
            timestamp: Date.now(),
        };
        messages.push(userMessage);
        renderMessages(); // Render only the latest user message
    }

    async function addBotMessage(text, regenerate = false, messageIndex = null) {
        if (regenerate) {
            chatSendBtn.disabled = true;
            chatSendBtn.classList.add("disabled");
            messages.splice(messageIndex + 1);
        }

        const response = await sendAPI(text);
        if (!response?.data) return;

        const chat_title = response.data.chat_title;
        const properties = response.data.properties;
        const map_properties_data = response.data.map_properties_data;
        const total_properties = response.data.total_properties;
        const description = response.data.description || `<p>As, I am an AI agent specializing in answering questions related to properties and homes and don’t have an answer for that yet. Could you please rephrase your question or provide more details so I can assist you better?</p>`;
        const irrelevant = response.data.irrelevant;
        const response_overview = response.data.response_overview;

        if ((chatHeader.textContent == "" || chatHeader.textContent == "New Chat") && chat_title != "") {
            chatHeader.textContent = chat_title;
        }

        const botMessage = {
            type: 'bot',
            text: description,
            timestamp: Date.now(),
            properties: properties,
            total_properties: total_properties,
            irrelevant: irrelevant,
            response_overview: response_overview,
            map_properties_data: map_properties_data
        };

        messages.push(botMessage);
        renderMessages(); // Render only the latest bot message

        chatSendBtn.disabled = false;
        chatSendBtn.classList.remove("disabled");
    }

    function renderMessages() {
        chatContent.innerHTML = '';
        messages.forEach((msg, index) => {
            if (msg.type === 'user') {
                const userMessage = document.createElement('div');
                userMessage.classList.add('message', 'user-message');
                userMessage.innerHTML = `
                    <img src="https://avatar.iran.liara.run/public/10" alt="User" class="message-image" />
                    <div class="message-text">${msg.text}</div>`;
                chatContent.appendChild(userMessage);
            } else if (msg.type === 'bot') {
                const botMessage = document.createElement('div');
                botMessage.classList.add('message', 'bot-message', 'bot-image-below');

                const messageText = document.createElement('div');
                messageText.classList.add('message-text');
                botMessage.appendChild(messageText);

                const botImage = document.createElement('img');
                botImage.src = 'https://www.linkhomeai.com/wp-content/uploads/2025/01/微信图片_20250114232457.jpg';
                botImage.alt = 'Bot';
                botImage.classList.add('message-image');
                botMessage.appendChild(botImage);

                chatContent.appendChild(botMessage);

                // Apply typing effect only to the latest bot message
                if (index === messages.length - 1) {
                    typeTextEffect(messageText, msg.text, () => {
                        addButtonsToMessage(messageText, msg, index);
                        if (msg.properties && msg.properties.length > 0) {
                            const extendedViewButton = document.createElement('button');
                            extendedViewButton.classList.add('reveal-btn');
                            extendedViewButton.textContent = '📄 Home listing';
                            messageText.appendChild(extendedViewButton);
                            extendedViewButton.addEventListener('click', openExtendedView);
                            openExtendedView();
                        }
                    });
                } else {
                    messageText.innerHTML = msg.text;
                    addButtonsToMessage(messageText, msg, index);
                }

                if (msg.properties && msg.properties.length > 0) {
                    const extendedViewBody = document.querySelector(".ext-body");
                    extendedViewBody.innerHTML = "";
                    extendedViewBody.innerHTML += `<p class='ext-body-res'>${msg.total_properties ? msg.total_properties + " " : 'no '} <strong> results </strong></p>`;

                    if (msg.response_overview) {
                        extendedViewBody.innerHTML += `<p class='ext-body-desc'>${msg.response_overview}</p></br>`;
                    }

                    const propertiesList = document.createElement('div');
                    propertiesList.classList.add('properties-list', 'grid-view');

                    msg.properties.forEach(property => {
                        const propertyLink = document.createElement('a');
                        propertyLink.classList.add('property-link');
                        propertyLink.href = property.url || '#';
                        propertyLink.target = '_blank';
                        const propertyItem = document.createElement('div');
                        propertyItem.classList.add('property-item');
                        const imageElement = document.createElement('img');
                        imageElement.src = property.imageUrl || '';
                        imageElement.alt = property.title || 'Property Image';
                        imageElement.classList.add('property-image');

                        propertyItem.innerHTML = `
                            <div class="property-image-container">
                                ${imageElement.outerHTML}
                            </div>
                            <div class="property-price">${property.pricePin || ''}</div>
                            <div class="property-details">
                                <div>${property.bedrooms > 1
                                ? property.bedrooms + " beds"
                                : property.bedrooms + " bed"
                            }</div>
                                <div class="separator"></div>
                                <div>${property.bathrooms > 1
                                ? property.bathrooms + " baths"
                                : property.bathrooms + " bath"
                            }</div>
                                <div class="separator"></div>
                                <div>${property.size} sqft</div>
                            </div>
                            <div class="property-address">${property.address || ''}</div>
                        `;

                        propertyLink.appendChild(propertyItem);
                        propertiesList.appendChild(propertyLink);
                    });

                    extendedViewBody.appendChild(propertiesList);

                    const viewMore = document.createElement("a");
                    viewMore.classList.add('view-more');
                    viewMore.href = "https://www.linkhomeai.com/browse-homes/";
                    viewMore.target = '_blank';
                    viewMore.textContent = "View More";
                    propertiesList.after(viewMore);
                }
            }
        });

        // Scroll to the bottom after rendering messages
        chatContent.scrollTop = chatContent.scrollHeight;
    }

    function addButtonsToMessage(messageText, msg, index) {
        const messageIcons = document.createElement('div');
        messageIcons.classList.add('message-icons');

        const regenerateBtn = document.createElement('button');
        regenerateBtn.classList.add('icon-btn', 'regenerate-btn');
        regenerateBtn.textContent = '🔄';

        const copyBtn = document.createElement('button');
        copyBtn.classList.add('icon-btn', 'copy-btn');
        copyBtn.textContent = '📄';

        messageIcons.appendChild(regenerateBtn);
        messageIcons.appendChild(copyBtn);
        messageText.appendChild(messageIcons);

        regenerateBtn.addEventListener('click', () => {
            showThinkingEffect();
            addBotMessage(msg.text, true, index);
        });
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(messageText.textContent)
                .then(() => {
                    alert('Copied to clipboard!');
                })
                .catch((err) => {
                    console.error('Failed to copy text: ', err);
                });
        });
    }

    function typeTextEffect(element, html, callback, speed = 100) {
        let i = 0;
        const textQueue = []; // Queue to store text and HTML tags

        // Parse the HTML and separate text and tags
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const nodes = doc.body.childNodes;

        // Traverse the nodes and build the queue
        nodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE) {
                // Split text into individual characters
                node.textContent.split('').forEach(char => {
                    textQueue.push({ type: 'text', value: char });
                });
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                // Add HTML tags as-is
                textQueue.push({ type: 'html', value: node.outerHTML });
            }
        });

        // Function to process the queue
        const typingInterval = setInterval(() => {
            if (i < textQueue.length) {
                const item = textQueue[i];
                if (item.type === 'text') {
                    // Append text characters one by one
                    element.innerHTML += item.value;
                } else if (item.type === 'html') {
                    // Append HTML tags as-is
                    element.innerHTML += item.value;
                }
                i++;
                chatContent.scrollTop = chatContent.scrollHeight;
            } else {
                clearInterval(typingInterval);
                // Call the callbacks after the typing effect is complete
                if (callback) callback();
            }
        }, speed);
    }

    function handleChat() {
        messageText = chatMessageInput.value.trim() || promptInput.value.trim();
        if (!messageText) return;

        chatSendBtn.disabled = true;
        chatSendBtn.classList.add("disabled");

        chatMessageInput.value = "";
        promptInput.value = "";

        addUserMessage(messageText);
        showThinkingEffect(); // Show thinking effect
        setTimeout(() => addBotMessage(messageText), 600); // Simulate bot response delay
    }

    function showThinkingEffect() {
        const thinkingEffect = document.createElement('div');
        thinkingEffect.classList.add('thinking-effect');
        thinkingEffect.innerHTML = `
            <div class="thinking-dots">
                <span>.</span>
                <span>.</span>
                <span>.</span>
            </div>
        `;
        chatContent.appendChild(thinkingEffect);
        chatContent.scrollTop = chatContent.scrollHeight;
    }

    function removeThinkingEffect() {
        const thinkingEffect = document.querySelector('.thinking-effect');
        if (thinkingEffect) {
            thinkingEffect.remove();
        }
    }

    async function sendAPI(text) {
        generatingResponse = true;
        try {
            var response = await fetch(
                "https://www.linkhomeai.com/wp-admin/admin-ajax.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                    },
                    body: new URLSearchParams({
                        action: "chatbot_ajax_request",
                        search_query: text,
                    }),
                }
            );

            if (!generatingResponse) return;

            if (!response.ok) {
                throw new Error("Network response was not ok " + response.statusText);
            }

            return await response.json();
        } catch (error) {
            alert("Server is busy!")
            removeThinkingEffect();
        } finally {
            generatingResponse = false;
            chatSendBtn.disabled = false;
            chatSendBtn.classList.remove("disabled");
        }
    }

    function openExtendedView() {
        isExtendedViewOpened = true;
        extendedView.style.setProperty("display", "flex");
        if (mediumSizeWindows.matches) {
            chatWrapper.style.setProperty("max-width", "0%", "important");
            chatContainer.style.setProperty("max-width", "0%", "important");
            chatWindow.style.setProperty("display", "none", "important");
            extendedView.style.setProperty("width", "100%");
            if (activeExtendedView) {
                activeExtendedView.style.setProperty("width", "100%", "important")
            }
        } else {
            extendedView.style.setProperty("width", "50%");
            extendedView.classList.add('active');
            chatWrapper.style.setProperty("max-width", "45%", "important")
            chatContainer.style.setProperty("max-width", "100%", "important");
            chatWindow.style.setProperty("display", "flex", "important");
        }
    }

    listMapSwitch.addEventListener("change", listMapToggle);

    function initMap(properties) {
        const defaultLocation = [33.6846, -117.8265];
        map = L.map('map-container').setView(defaultLocation, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Add markers for each property
        if (properties && properties.length > 0) {
            properties.forEach(property => {
                const { lat, lng, pricePin, url, title } = property;
                if (lat && lng) {
                    const propertyMarker = L.divIcon({
                        className: 'custom-marker',
                        html: `<div class="marker-label">${pricePin}</div>`,
                        iconSize: [40, 40],
                        iconAnchor: [20, 40]
                    });

                    const popupContent = `
                    <div>
                        <strong>Price:</strong> ${pricePin}<br>
                        <a href="${url}" target="_blank">${title || 'View Property'}</a>
                    </div>
                `;

                    const marker = L.marker([lat, lng], { icon: propertyMarker })
                        .addTo(map)
                        .bindPopup(popupContent);

                    // Optionally, open the popup by default
                    marker.openPopup();
                }
            });
        }
    }

    function listMapToggle(event) {
        const propertiesList = document.querySelector(".properties-list");
        const extendedViewBody = document.querySelector(".ext-body");
        const mapContainer = document.getElementById("map-container");
        const extendedViewBodyRes = document.querySelector(".ext-body-res");
        const extendedViewBodyDesc = document.querySelector(".ext-body-desc");
        const viewMoreBtn = document.querySelector(".view-more");
        const toggle = event.target;

        if (toggle.checked) {
            // Show loader
            showMapLoader();

            // Hide list and other elements
            propertiesList.style.display = "none";
            if (extendedViewBodyDesc) {
                extendedViewBodyDesc.style.display = 'none';
            }
            if (extendedViewBodyRes) {
                extendedViewBodyRes.style.display = 'none';
            }
            if (viewMoreBtn) {
                viewMoreBtn.style.display = 'none';
            }

            // Initialize map after a short delay (simulate loading)
            setTimeout(() => {
                if (!mapContainer) {
                    const mapDiv = document.createElement("div");
                    mapDiv.id = "map-container";
                    mapDiv.style.height = "85%";
                    mapDiv.style.width = "100%";
                    mapDiv.style.display = "block";
                    extendedViewBody.appendChild(mapDiv);

                    // Pass the properties to the initMap function
                    const properties = messages[messages.length - 1]?.map_properties_data || [];
                    initMap(properties);
                } else {
                    mapContainer.style.display = "block";
                }

                // Hide loader after map is loaded
                hideMapLoader();
            }, 1000); // Simulate a 1-second loading delay
        } else {
            // Hide map and show list
            if (mapContainer) {
                mapContainer.style.display = 'none';
            }
            propertiesList.style.display = 'grid';

            if (extendedViewBodyDesc) {
                extendedViewBodyDesc.style.display = 'flex';
            }
            if (extendedViewBodyRes) {
                extendedViewBodyRes.style.display = 'flex';
            }
            if (viewMoreBtn) {
                viewMoreBtn.style.display = 'flex';
            }
        }
    }

    extViewCrossBtn.addEventListener("click", extHandleCross);

    function extHandleCross() {
        isExtendedViewOpened = false;
        extendedView.classList.remove('active');
        extendedView.style.setProperty("display", "none");
        if (mediumSizeWindows.matches) {
            chatWrapper.style.setProperty("max-width", "100%", "important");
            chatContainer.style.setProperty("max-width", "100%", "important");
            chatWindow.style.setProperty("display", "flex", "important");
        } else {
            chatWrapper.style.setProperty("max-width", "100%", "important")
            chatContainer.style.setProperty("max-width", "100%", "important");
            chatWindow.style.setProperty("display", "flex", "important");
        }
    }

    function showMapLoader() {
        let mapLoader = document.getElementById("map-loader");

        // Create the mapLoader if it doesn't exist
        if (!mapLoader) {
            mapLoader = document.createElement("div");
            mapLoader.id = "map-loader";
            mapLoader.style.display = "none"; // Initially hidden
            mapLoader.style.position = "absolute";
            mapLoader.style.top = "50%";
            mapLoader.style.left = "50%";
            mapLoader.style.transform = "translate(-50%, -50%)";
            mapLoader.style.zIndex = "1000";

            // Create the spinning loader inside the mapLoader
            const loaderInner = document.createElement("div");
            loaderInner.style.border = "4px solid #fff";
            loaderInner.style.borderTop = "4px solid #fb6109";
            loaderInner.style.borderRadius = "50%";
            loaderInner.style.width = "40px";
            loaderInner.style.height = "40px";
            loaderInner.style.animation = "spin 1s linear infinite";

            // Append the loaderInner to the mapLoader
            mapLoader.appendChild(loaderInner);

            // Append the mapLoader to the extendedViewBody
            const extendedViewBody = document.querySelector(".ext-body");
            extendedViewBody.appendChild(mapLoader);

            // Add the spin animation to the CSS if it doesn't exist
            if (!document.getElementById("spin-animation")) {
                const style = document.createElement("style");
                style.id = "spin-animation";
                style.textContent = `
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                `;
                document.head.appendChild(style);
            }
        }

        // Show the loader
        mapLoader.style.display = "block";
    }

    function hideMapLoader() {
        const mapLoader = document.getElementById("map-loader");
        if (mapLoader) {
            mapLoader.style.display = "none";
        }
    }
});
