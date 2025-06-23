
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".schedule-form").forEach(form => {
        form.addEventListener("submit", function(e) {
            e.preventDefault();

            const requestId = this.dataset.requestId;
            const dateInput = form.querySelector("input[name='scheduled_at']");
            const scheduledDate = new Date(dateInput.value);

            if (!dateInput.value) {
                alert("Please select a date and time!");
                return;
            }

            const formattedDate = scheduledDate.toLocaleString("en-IN", {
                dateStyle: "medium",
                timeStyle: "short"
            });

            const formData = new FormData(form);

            // 🟨 ADD THIS LINE FOR DEBUGGING
            fetch("schedule_swap.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                console.log("📡 Server returned:", data); // ← 🟩 ADD THIS LINE

                const alertBox = document.getElementById("alert-" + requestId);
                if (data.status === "success") {
                    alertBox.style.display = "block";
                    alertBox.textContent = `✅ Scheduled for: ${formattedDate}`;
                    alertBox.style.backgroundColor = "#d0f0c0";
                    alertBox.style.color = "#2e7d32";
                    alertBox.style.padding = "10px";
                    alertBox.style.marginTop = "0.8rem";
                    alertBox.style.borderRadius = "8px";
                    alertBox.style.fontWeight = "bold";
                    form.reset();
                } else {
                    alertBox.style.display = "block";
                    alertBox.textContent = "❌ " + data.message;
                    alertBox.style.backgroundColor = "#ffe0e0";
                    alertBox.style.color = "#c62828";
                }
            })
            .catch(err => {
                alert("❌ Failed to schedule! Please try again.");
                console.error("Error:", err);
            });
        });
    });
});




document.querySelectorAll('.accept-btn, .reject-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const form = this.closest('form');
        const action = this.classList.contains('accept-btn') ? 'Accepted' : 'Rejected';
        const formData = new FormData(form);
        formData.set('action', action);

        console.log("Submitting:", Object.fromEntries(formData.entries())); // 👈 DEBUG

        fetch('update_request_status.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(response => {
            console.log("Response:", response); // 👈 DEBUG RESPONSE

            const card = form.closest('.request-card');
            if (card) {
                const statusEl = card.querySelector('.status');
                if (statusEl) {
                    statusEl.innerText = action;
                }
                form.parentElement.innerHTML = `<p><strong>Status:</strong> ${action}</p>`;
            }

            sessionStorage.setItem("requestUpdated", "true");
        })
        .catch(err => {
            console.error("Error while updating request:", err); // 👈 SHOW ERRORS
        });
    });
});

document.querySelectorAll('.chat-form').forEach(form => {
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const requestId = form.dataset.requestId;

        const res = await fetch('send_message.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.text(); // just log it for now
        if (res.ok) {
            // append message to chat
            const message = form.querySelector('input[name="message"]').value;
            const chatBox = document.getElementById(`chat-${requestId}`);
            const msgElement = document.createElement('div');
            msgElement.textContent = "You: " + message;
            msgElement.className = 'chat-msg right-msg';
            chatBox.appendChild(msgElement);
            chatBox.style.display = 'block';
            form.reset();
        } else {
            alert('Message failed to send');
        }
    });
});



// ✅ Review Submission
document.querySelectorAll(".review-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        document.getElementById("ratingModal").style.display = "block";
        document.getElementById("receiver_id").value = btn.dataset.receiverId;
        document.getElementById("request_id").value = btn.dataset.requestId;
    });
});

document.getElementById("reviewForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    fetch("submit_review.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        alert("Thanks for your review! 🌟");
        location.reload();
    });
});


document.querySelectorAll('.status-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const clickedBtn = e.submitter;
        const action = clickedBtn.value;

        const formData = new FormData(this);
        formData.append('action', action);

        fetch('update_request_status.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(response => {
            console.log("Response:", response);
            if (response.trim() === 'success') {
                // Replace entire form with status update
                const statusText = `<p><strong>Status:</strong> ${action}</p>`;
                form.outerHTML = statusText;
            } else {
                alert("Error updating status: " + response);
            }
        })
        .catch(err => {
            console.error("AJAX error:", err);
        });
    });
});

// ✅ Chat System
document.querySelectorAll(".chat-form").forEach(form => {
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        const requestId = form.dataset.requestId;

        fetch("send_message.php", {
            method: "POST",
            body: formData
        }).then(res => res.text()).then(() => {
            form.message.value = "";
            loadChat(requestId);
        });
    });
});

function loadChat(requestId) {
    fetch(`get_messages.php?request_id=${requestId}`)
        .then(res => res.json())
        .then(messages => {
            const chatBox = document.getElementById(`chat-${requestId}`);
            if (!chatBox) return;
            chatBox.innerHTML = "";
            messages.forEach(msg => {
                const msgDiv = document.createElement("div");
                msgDiv.textContent = msg.mine ? "You: " + msg.message : "Partner: " + msg.message;
                msgDiv.style.marginBottom = "0.5rem";
                chatBox.appendChild(msgDiv);
            });
        });
}

// Auto-refresh chat every 5 seconds
setInterval(() => {
    document.querySelectorAll(".chat-box").forEach(box => {
        const requestId = box.dataset.requestId;
        loadChat(requestId);
    });
}, 5000);

document.querySelectorAll(".status-form").forEach(form => {
    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const clickedBtn = e.submitter;
        const action = clickedBtn.value;
        const requestId = form.dataset.requestId;

        const formData = new FormData(form);
        formData.append("action", action);

        fetch("update_request_status.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(response => {
            console.log("Server says:", response);

            if (response.trim() === "success") {
                form.parentElement.innerHTML = `
                    <p><strong>Status:</strong> ${action}</p>
                `;
            } else {
                alert("❌ Error updating request: " + response);
            }
        })
        .catch(err => {
            console.error("AJAX error:", err);
            alert("❌ Failed to update. Try again.");
        });
    });
});

document.querySelectorAll(".withdraw-form").forEach(form => {
    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const requestId = form.dataset.requestId;
        const formData = new FormData(form);

        fetch("withdraw_request.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(response => {
            console.log("Withdraw response:", response);

            if (response.trim() === "success") {
                form.closest(".match-card").remove(); // remove the whole request card
            } else {
                alert("❌ Failed to withdraw: " + response);
            }
        })
        .catch(err => {
            console.error("AJAX error:", err);
            alert("❌ Something went wrong while withdrawing.");
        });
    });
});

document.querySelectorAll('.cancel-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const requestId = form.dataset.requestId;

        if (!confirm("Are you sure you want to cancel this accepted swap?")) return;

        const response = await fetch('cancel_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `request_id=${requestId}`
        });

        const result = await response.text();

        if (result.trim() === 'success') {
            alert("Request cancelled successfully.");
            document.getElementById('req-' + requestId).remove(); // remove the card
        } else {
            alert("Something went wrong. ❌");
        }
    });
});
