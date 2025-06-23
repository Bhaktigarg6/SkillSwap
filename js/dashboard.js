console.log("✅ dashboard.js loaded");

let isEditing = false;

function toggleEdit() {
    const teachText = document.getElementById('teachSkillsText');
    const learnText = document.getElementById('learnSkillsText');
    const teachInput = document.getElementById('teachSkillsInput');
    const learnInput = document.getElementById('learnSkillsInput');
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');

    if (!isEditing) {
        teachText.style.display = 'none';
        learnText.style.display = 'none';
        teachInput.style.display = 'block';
        learnInput.style.display = 'block';
        saveBtn.style.display = 'inline-block';
        editBtn.innerText = '❌ Cancel';
    } else {
        teachText.style.display = 'block';
        learnText.style.display = 'block';
        teachInput.style.display = 'none';
        learnInput.style.display = 'none';
        saveBtn.style.display = 'none';
        editBtn.innerText = '✏️ Add/Edit Skills';
    }

    isEditing = !isEditing;
}

// 📩 SEND REQUEST BUTTON HANDLER (works across all pages)
document.querySelectorAll('.send-btn').forEach(button => {
    button.addEventListener('click', function () {
        const targetUserId = this.dataset.userId;
        const btn = this;
console.log("🟡 Send button clicked for User ID:", targetUserId);

        fetch('send_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'receiver_id=' + encodeURIComponent(targetUserId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' || data.status === 'already_sent') {
                updateAllRequestButtons(data.receiver_id);
                sessionStorage.setItem("requestUpdated", "true"); // 🔄 flag for sync on other pages
            } else {
                alert("❌ Failed to send request. Try again.");
            }
        })
        .catch(error => {
            console.error("AJAX Error:", error);
        });
    });
});

// 🔁 Globally update all matching buttons
function updateAllRequestButtons(userId) {
    document.querySelectorAll(`.send-btn[data-user-id="${userId}"]`).forEach(btn => {
        btn.disabled = true;
        btn.innerText = '⏳ Pending';
        btn.classList.add('pending-btn');
    });
}

function fetchUpdatedMatches() {
   fetch('auto_change_btn.php')
        .then(res => res.json())
        .then(data => {
            updateMatchButtons(data);
            refreshSuggestedMatches(); // 🧠 This is the new function
        });
}

function refreshSuggestedMatches() {
    fetch("dashboard.php?ajax=true")
        .then(res => res.json())
        .then(data => {
            const container = document.querySelector(".suggested-matches-section .cards");
            container.innerHTML = '';

            if (!data || data.length === 0) {
                container.innerHTML = "<p>No suggested matches found right now. Try updating your skills!</p>";
                return;
            }

            data.forEach(user => {
                const card = document.createElement("div");
                card.classList.add("card", "match-card");

                const statusClass = user.status === "Pending" ? "pending-btn" : "";
                const disabled = user.status === "Pending" ? "disabled" : "";

                card.innerHTML = `
                    <img src="${user.profile_pic}" class="match-pic" />
                    <h3>${user.name}</h3>
                    <p><strong>Can Teach:</strong> ${user.teach_skills}</p>
                    <p><strong>Wants to Learn:</strong> ${user.learn_skills}</p>
                    <button class="send-btn ${statusClass}" ${disabled} data-user-id="${user.id}">
                        ${user.status === "Pending" ? '⏳ Pending' : '📩 Send Request'}
                    </button>
                `;

                container.appendChild(card);
            });

            // Re-attach event listeners to new buttons
            attachSendRequestListeners();
        })
        .catch(error => {
            console.error("❌ Error fetching matches:", error);
            const container = document.querySelector(".suggested-matches-section .cards");
            container.innerHTML = "<p>⚠️ Failed to load suggested matches. Try again later.</p>";
        });
}

function toggleEdit() {
    const teachText = document.getElementById('teachSkillsText');
    const learnText = document.getElementById('learnSkillsText');
    const teachInput = document.getElementById('teachSkillsInput');
    const learnInput = document.getElementById('learnSkillsInput');
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');

    if (!isEditing) {
        teachText.style.display = 'none';
        learnText.style.display = 'none';
        teachInput.style.display = 'block';
        learnInput.style.display = 'block';
        saveBtn.style.display = 'inline-block';
        editBtn.innerText = '❌ Cancel';
    } else {
        teachText.style.display = 'block';
        learnText.style.display = 'block';
        teachInput.style.display = 'none';
        learnInput.style.display = 'none';
        saveBtn.style.display = 'none';
        editBtn.innerText = '✏️ Add/Edit Skills';
    }

    isEditing = !isEditing;
}

// ✅ ATTACH SEND REQUEST BUTTON LISTENERS
function attachSendRequestListeners() {
    document.querySelectorAll('.send-btn').forEach(button => {
        button.addEventListener('click', function () {
            const targetUserId = this.dataset.userId;
            const btn = this;
            console.log("🟡 Send button clicked for User ID:", targetUserId);

            fetch('send_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'receiver_id=' + encodeURIComponent(targetUserId)
            })
                .then(response => response.json())
                .then(data => {
                    console.log("🟢 Response from send_request.php:", data);
                    if (data.status === 'success' || data.status === 'already_sent') {
                        updateAllRequestButtons(data.receiver_id);
                        sessionStorage.setItem("requestUpdated", "true");
                        fetchUpdatedMatches(); // optional refresh
                    } else {
                        alert("❌ Failed to send request. Try again.");
                    }
                })
                .catch(error => {
                    console.error("AJAX Error:", error);
                });
        });
    });
}

function updateAllRequestButtons(userId) {
    document.querySelectorAll(`.send-btn[data-user-id="${userId}"]`).forEach(btn => {
        btn.disabled = true;
        btn.innerText = '⏳ Pending';
        btn.classList.add('pending-btn');
    });
}

function fetchUpdatedMatches() {
    fetch('auto_change_btn.php')
        .then(res => res.json())
        .then(data => {
            updateMatchButtons(data);
            refreshSuggestedMatches();
        });
}

function refreshSuggestedMatches() {
    fetch("dashboard.php?ajax=true")
        .then(res => res.json())
        .then(data => {
            const container = document.querySelector(".suggested-matches-section .cards");
            container.innerHTML = '';

            if (data.length === 0) {
                container.innerHTML = "<p>No suggested matches found right now. Try updating your skills!</p>";
                return;
            }

            data.forEach(user => {
                const card = document.createElement("div");
                card.classList.add("card", "match-card");

                const statusClass = user.status === "Pending" ? "pending-btn" : "";
                const disabled = user.status === "Pending" ? "disabled" : "";

                card.innerHTML = `
                    <img src="${user.profile_pic}" class="match-pic" />
                    <h3>${user.name}</h3>
                    <p><strong>Can Teach:</strong> ${user.teach_skills}</p>
                    <p><strong>Wants to Learn:</strong> ${user.learn_skills}</p>
                    <button class="send-btn ${statusClass}" ${disabled} data-user-id="${user.id}">
                        ${user.status === "Pending" ? '⏳ Pending' : '📩 Send Request'}
                    </button>
                `;

                container.appendChild(card);
            });

            attachSendRequestListeners(); // rebind buttons
        });
}

function updateMatchButtons(sentIds = []) {
    document.querySelectorAll('.send-btn').forEach(btn => {
        const userId = btn.dataset.userId + "";
        if (sentIds.includes(userId)) {
            btn.disabled = true;
            btn.innerText = '⏳ Pending';
            btn.classList.add('pending-btn');
        } else {
            btn.disabled = false;
            btn.innerText = '📩 Send Request';
            btn.classList.remove('pending-btn');
        }
    });
}

// ✅ FIRST LOAD + NAVIGATION
window.addEventListener('load', fetchUpdatedMatches);
window.addEventListener('pageshow', e => {
    if (e.persisted || performance.getEntriesByType("navigation")[0]?.type === "back_forward") {
        fetchUpdatedMatches();
    }
});
if (sessionStorage.getItem("requestUpdated") === "true") {
    console.log("🔁 Refreshing match buttons (session update).");
    fetchUpdatedMatches();
    sessionStorage.removeItem("requestUpdated");
}

document.addEventListener('DOMContentLoaded', () => {
    console.log("✅ dashboard.js loaded");

    // 🧠 Chatbot toggle logic
const chatbotBtn = document.getElementById('chatbot-button');
const chatHistoryPanel = document.getElementById('chat-history-panel');
const closeChatBtn = document.getElementById('close-chat-history');

console.log("🧪 Debug chatbot elements:", chatbotBtn, chatHistoryPanel, closeChatBtn);

if (!chatbotBtn) {
    console.warn("❌ chatbot-button not found in DOM");
}
if (!chatHistoryPanel) {
    console.warn("❌ chat-history-panel not found in DOM");
}
if (!closeChatBtn) {
    console.warn("❌ close-chat-history button not found in DOM");
}

if (chatbotBtn && chatHistoryPanel && closeChatBtn) {
    chatbotBtn.addEventListener('click', () => {
        const isHidden = chatHistoryPanel.style.display === 'none' || chatHistoryPanel.style.display === '';
        chatHistoryPanel.style.display = isHidden ? 'block' : 'none';
        if (isHidden) {
            console.log("✅ Opening chat history...");
            loadChatHistory();
        } else {
            console.log("❎ Closing chat history...");
        }
    });

    closeChatBtn.addEventListener('click', () => {
        chatHistoryPanel.style.display = 'none';
        console.log("❎ Chat panel closed by user");
    });
}

    // 🧠 Filter logic
    const searchInput = document.getElementById('searchInput');
    const teachLearnFilter = document.getElementById('teachLearnFilter');
    const skillLevelFilter = document.getElementById('skillLevelFilter');
    const locationFilter = document.getElementById('locationFilter');
    const resultsContainer = document.getElementById('userResults');

    function fetchFilteredUsers() {
        const search = searchInput?.value.trim() ?? '';
        const teachLearn = teachLearnFilter?.value ?? '';
        const level = skillLevelFilter?.value ?? '';
        const location = locationFilter?.value ?? '';

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "filter_users.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (this.status === 200 && resultsContainer) {
                resultsContainer.innerHTML = this.responseText;
                attachSendRequestListeners(); // important for dynamic results
            }
        };

        xhr.send(`search=${search}&teach_learn=${teachLearn}&level=${level}&location=${location}`);
    }

    [searchInput, teachLearnFilter, skillLevelFilter, locationFilter].forEach(input => {
        input?.addEventListener('input', fetchFilteredUsers);
    });

    attachSendRequestListeners(); // initial page load
});

// 💬 Chat open from history
function openChatWindow(requestId, userName) {
    alert(`Going to chat.php?request_id=${requestId}`);
    window.location.href = `chat.php?request_id=${requestId}`;
}

// 💬 Load chat history panel
function loadChatHistory() {
    const chatHistoryList = document.getElementById('chat-history-list');
    chatHistoryList.innerHTML = '<p>Loading chats...</p>';

    fetch('fetch_chat_history.php')
        .then(res => res.json())
        .then(data => {
            chatHistoryList.innerHTML = '';
            if (data.length === 0) {
                chatHistoryList.innerHTML = '<p>No chat history found.</p>';
                return;
            }

            data.forEach(chat => {
                const isSentByYou = chat.sender_id == sessionStorage.getItem("user_id");
                const label = isSentByYou ? "🟢 You: " : `🔵 ${chat.user_name}: `;

                const chatItem = document.createElement('div');
                chatItem.innerHTML = `
                    <strong>${label}</strong><br>
                    <small>${chat.last_message}</small><br>
                    <small style="color:#999">${chat.last_message_time}</small>
                `;
                chatItem.style.padding = '0.5rem';
                chatItem.style.borderBottom = '1px solid #eee';
                chatItem.style.cursor = 'pointer';

                chatItem.addEventListener('click', () => {
                    openChatWindow(chat.request_id, chat.user_name);
                });

                chatHistoryList.appendChild(chatItem);
            });
        })
        .catch(() => {
            chatHistoryList.innerHTML = '<p>Error loading chats.</p>';
        });
}
