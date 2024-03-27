
function checkNotifications() {
    // Make an AJAX request to fetch notifications for the current user
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                var notifications = JSON.parse(xhr.responseText);
                if (notifications.length > 0) {
                    // Display notifications to the user
                    alert("New notification: " + notifications[0].content);
                    // You can customize how you want to display notifications
                }
            }
        }
    };
    xhr.open("GET", "fetch_notifications.php", true);
    xhr.send();
}

// Check for notifications every 10 seconds
setInterval(checkNotifications, 10000); // Adjust the interval as needed

